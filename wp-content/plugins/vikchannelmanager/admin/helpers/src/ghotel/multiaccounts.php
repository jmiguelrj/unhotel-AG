<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2022 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Google Hotel helper class to manage multiple accounts.
 * 
 * @since 	1.8.6
 */
class VCMGhotelMultiaccounts
{
	/**
	 * Loads a multi hotel account record.
	 * 
	 * @param 	int 	$id 			the record to fetch.
	 * @param 	bool 	$is_remote_id 	whether this is an account ID.
	 * 
	 * @return 	array|bool 				false or record array.
	 * 
	 * @since 	1.8.7 	added $is_remote_id argument to signature.
	 */
	public static function loadFromId($id, $is_remote_id = false)
	{
		if (empty($id)) {
			return false;
		}

		$dbo = JFactory::getDbo();

		$q = "SELECT * FROM `#__vikchannelmanager_hotel_multi` WHERE `" . ($is_remote_id ? 'account_id' : 'id') . "`=" . ($is_remote_id ? $dbo->quote($id) : (int)$id);
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			return false;
		}

		$multi_hotel = $dbo->loadAssoc();
		$multi_hotel['hdata'] = !empty($multi_hotel['hdata']) ? json_decode($multi_hotel['hdata'], true) : [];
		$multi_hotel['hdata'] = !is_array($multi_hotel['hdata']) ? [] : $multi_hotel['hdata'];

		return $multi_hotel;
	}

	/**
	 * Sets the hotel inventory ID for the local multi hotel account ID.
	 * 
	 * @param 	int 	$local_id 	the ID of the VCM multi hotel account.
	 * @param 	int 	$remote_id 	the e4jConnect hotel inventory ID.
	 * 
	 * @return 	bool
	 */
	public static function updateInventoryAccountId($local_id, $remote_id)
	{
		if (empty($local_id) || empty($remote_id)) {
			return false;
		}

		$local_record = self::loadFromId($local_id);
		if (!$local_record) {
			return false;
		}

		$upd_obj = new stdClass;
		$upd_obj->id = $local_record['id'];
		$upd_obj->account_id = (string)$remote_id;

		$dbo = JFactory::getDbo();
		$dbo->updateObject('#__vikchannelmanager_hotel_multi', $upd_obj, 'id');

		return true;
	}

	/**
	 * Reads the name of the first (main) hotel account.
	 * 
	 * @return 	string
	 */
	public static function getMainHotelName()
	{
		$dbo = JFactory::getDbo();
		$q = "SELECT `value` FROM `#__vikchannelmanager_hotel_details` WHERE `key`='name'";
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			return $dbo->loadResult();
		}

		return '';
	}

	/**
	 * Loads all multi hotel account records.
	 * 
	 * @param 	bool 	$only_account 	whether to load just the account_id column.
	 * 
	 * @return 	array 					list of multi hotel accounts or empty array.
	 * 
	 * @since 	1.8.7
	 */
	public static function getAll($only_account = false)
	{
		$dbo = JFactory::getDbo();

		$q = "SELECT * FROM `#__vikchannelmanager_hotel_multi`;";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			return [];
		}

		$multi_hotels 	= $dbo->loadAssocList();
		$multi_accounts = [];
		foreach ($multi_hotels as $k => $multi_hotel) {
			$multi_hotel['hdata'] = !empty($multi_hotel['hdata']) ? json_decode($multi_hotel['hdata'], true) : [];
			$multi_hotel['hdata'] = !is_array($multi_hotel['hdata']) ? [] : $multi_hotel['hdata'];
			$multi_hotels[$k] = $multi_hotel;
			if (!empty($multi_hotel['account_id'])) {
				$multi_accounts[] = preg_replace("/[^0-9]+/", '', $multi_hotel['account_id']);
			}
		}

		return $only_account ? $multi_accounts : $multi_hotels;
	}

	/**
	 * Hotels can choose a picture, a logo or a photo, to be used for the landing page of
	 * Google Hotel and their Free Booking Links. We also accept empty picture strings to
	 * actually remove a previously transmitted picture.
	 * 
	 * @param 	string 	$picture 	the probable local path to the image, or an empty string.
	 * @param 	int 	$remote_id 	the e4jConnect hotel ID assigned to the property.
	 * @param 	bool 	$was_new 	whether the property was just created.
	 * 
	 * @return 	mixed 				true on success, string with error message otherwise.
	 * 
	 * @since 	1.8.7
	 */
	public static function transferMainPicture($picture, $remote_id, $was_new = false)
	{
		if (empty($remote_id)) {
			return 'Invalid Hotel ID given';
		}

		// build full URI to picture (if not empty and if a local image)
		if (!empty($picture) && strpos($picture, 'http') !== 0) {
			// prepend base URI and make sure the path can be used as a URI
			$picture = JUri::root() . ltrim(str_replace(DIRECTORY_SEPARATOR, '/', $picture), '/');
		}

		// build the request body
		$rq_body = [
			'api_key'     => VikChannelManager::getApiKey(true),
			'property_id' => $remote_id,
			'notify_url'  => JUri::root(),
			'new_hotel'   => (int)$was_new,
			'picture'     => $picture,
		];

		// execute the JSON request
		$e4jc_url = "https://hotels.e4jconnect.com/google-hotel/picture";

		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields(json_encode($rq_body));
		$e4jC->setHttpHeader(['Content-Type: application/json']);
		$e4jC->setTimeout(600);
		$rs = $e4jC->exec();

		if ($e4jC->getErrorNo()) {
			return sprintf('cURL error: %s', @curl_error($e4jC->getCurlHeader()));
		}

		// decode the response
		$result = json_decode($rs);
		if (!is_object($result) || !$result->status) {
			return (is_object($result) && !empty($result->error) ? $result->error : 'Invalid response');
		}

		// result was successful
		return true;
	}

	/**
	 * In case of multi-hotel accounts, given a hotel account ID and a room-type ID,
	 * attempts to check if all rooms mapped under the same account share the same
	 * category ID defined through Vik Booking to keep this filter in the stepbar
	 * of the booking process or on any other available feature for filtering.
	 * Should be called after having checked that multiple hotel accounts were configured.
	 * 
	 * @param 	int 	$ota_hotel_id 	the Google Hotel (Partner) Account ID.
	 * @param 	int 	$room_type_id 	the requested room-type ID in Vik Booking.
	 * 
	 * @return 	mixed 	 				the first common category ID in Vik Booking or null.
	 * 
	 * @since 	1.8.9
	 */
	public static function guessHotelRoomCategory($ota_hotel_id, $room_type_id)
	{
		if (empty($ota_hotel_id) || empty($room_type_id)) {
			return null;
		}

		$dbo = JFactory::getDbo();

		$room_type_id = (int)$room_type_id;

		// first get the categories of this room
		$q = "SELECT `idcat` FROM `#__vikbooking_rooms` WHERE `id`=" . $room_type_id;
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			return null;
		}

		$room_categories = $dbo->loadResult();

		if (empty($room_categories)) {
			return null;
		}

		// split category IDs
		$cat_parts = explode(';', $room_categories);

		// use the first category of this room in case of fallback
		$category_info = self::getCategoryInfo($cat_parts[0]);

		if (!$category_info) {
			return null;
		}

		// find account ID for this room
		$q = "SELECT `prop_params` FROM `#__vikchannelmanager_roomsxref` WHERE `idroomvb`=" . $room_type_id . " AND `idchannel`=" . VikChannelManagerConfig::GOOGLEHOTEL;
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			// room is not mapped
			return null;
		}

		$room_hotel_account = $dbo->loadResult();

		// find all the other rooms mapped under this account
		$q = "SELECT `idroomvb` FROM `#__vikchannelmanager_roomsxref` WHERE `idroomvb` != " . $room_type_id . " AND `idchannel`=" . VikChannelManagerConfig::GOOGLEHOTEL . " AND `prop_params` = " . $dbo->quote($room_hotel_account) . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			// this is the only room under this hotel account, but we need to return the first category no matter what
			return $category_info['id'];
		}

		$additional_rooms = $dbo->loadAssocList();

		$sibling_room_ids = [];
		foreach ($additional_rooms as $additional_room) {
			if (!in_array($additional_room['idroomvb'], $sibling_room_ids)) {
				$sibling_room_ids[] = $additional_room['idroomvb'];
			}
		}

		// find all categories of the sibling rooms
		$q = "SELECT `id`, `idcat` FROM `#__vikbooking_rooms` WHERE `id` IN (" . implode(', ', $sibling_room_ids) . ");";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			// rooms mapping must be outdated
			return null;
		}

		$sibling_rooms = $dbo->loadAssocList();

		// find the categories in common
		$common_categories = [];

		foreach ($cat_parts as $category_id) {
			if (empty($category_id)) {
				continue;
			}
			$in_common = true;
			foreach ($sibling_rooms as $sibling_room) {
				if (empty($sibling_room['idcat'])) {
					// all rooms mapped under the same account must have at least one category in common
					return null;
				}
				$sibling_cat_parts = explode(';', $sibling_room['idcat']);
				if (!in_array($category_id, $sibling_cat_parts)) {
					$in_common = false;
					break;
				}
			}
			if ($in_common) {
				$common_categories[] = $category_id;
			}
		}

		if (!count($common_categories)) {
			// no categories in common were found
			return null;
		}

		foreach ($common_categories as $category_id) {
			$category_info = self::getCategoryInfo($cat_parts[0]);
			if (!$category_info) {
				continue;
			}

			// use the first common category among all rooms under the same account
			return $category_info['id'];
		}

		// nothing was found
		return null;
	}

	/**
	 * Returns the room category information from the given ID.
	 * 
	 * @param 	int 	$idcat 	the Vik Booking category ID.
	 * 
	 * @return 	array
	 * 
	 * @since 	1.8.9
	 */
	protected static function getCategoryInfo($idcat)
	{
		$dbo = JFactory::getDbo();

		$q = "SELECT * FROM `#__vikbooking_categories` WHERE `id`=" . (int)$idcat;
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if (!$dbo->getNumRows()) {
			return [];
		}

		return $dbo->loadAssoc();
	}
}
