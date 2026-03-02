<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.mvc.controllers.admin');

class VikChannelManagerControllerExpediaproduct extends JControllerAdmin
{
	/**
	 * Task expediaproduct.download to retrieve all room details
	 * from the current Expedia hotel account. This method can also
	 * be called by other methods of this class to update the contents.
	 * It is possible to filter the number of details to be retrieved in
	 * case only some data was updated. This is to save useless API calls.
	 * 
	 * @param 	string 		$listing_id 	the optional listing ID to read (all listings otherwise).
	 * @param 	bool 		$return 		whether to return the result.
	 * @param 	array 		$reload_values 	filter the API requests by only performing some.
	 * 
	 * @return 	void|bool 					if $return, a boolean value will be returned.
	 */
	public function download($listing_id = null, $return = false, $reload_values = null)
	{
		$dbo = JFactory::getDbo();
		$session = JFactory::getSession();
		$mainframe = JFactory::getApplication();
		
		if (!function_exists('curl_init')) {
			if ($return) {
				return false;
			}
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap('e4j.error.Curl'));
			$mainframe->redirect("index.php?option=com_vikchannelmanager&view=expediaproducts");
			exit;
		}
		
		$apikey = VikChannelManager::getApiKey(true);
		if (empty($apikey)) {
			if ($return) {
				return false;
			}
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap('e4j.error.Settings'));
			$mainframe->redirect("index.php?option=com_vikchannelmanager&view=expediaproducts");
			exit;
		}

		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);
		if (empty($channel['params']['hotelid']) || $channel['uniquekey'] != VikChannelManagerConfig::EXPEDIA) {
			if ($return) {
				return false;
			}
			VikError::raiseWarning('', 'Empty Hotel ID for Expedia.');
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=config");
			exit;
		}
		$account_key = $channel['params']['hotelid'];

		// increase listings retrieve counter, without validating it
		$retrieve_count = (int)$session->get('vcmExpediaLstRetCount', 0);
		$session->set('vcmExpediaLstRetCount', ++$retrieve_count);

		// build necessary download values
		$default_download_values = [
			'property',
			'roomtypes',
			'amenities',
			'rateplans',
			'ratethreshold',
		];
		if (!empty($reload_values) && is_array($reload_values)) {
			foreach ($reload_values as $k => $read_value) {
				if (!in_array($read_value, $default_download_values)) {
					// this value is unknown
					unset($reload_values[$k]);
				}
			}
			if (count($reload_values)) {
				// overwrite values to be downloaded
				$default_download_values = $reload_values;
			}
		}

		// build reading value nodes
		$read_nodes = [];
		foreach ($default_download_values as $read_value) {
			array_push($read_nodes, '<ReadListing type="' . $read_value . '"></ReadListing>');
		}

		// make the request to e4jConnect to load the Listings information

		try {
			// ignore any possible abort for this large request
			ignore_user_abort(true);
			ini_set('max_execution_time', 0);
		} catch (Exception $e) {
			// do nothing
		}
			
		// required filter by hotel ID (host user id)
		$filters = array('hotelid="' . $account_key . '"');

		/**
		 * Inject limit and offset per request to avoid gateway timeout errors for
		 * host accounts with a high number of listings connected.
		 */
		$_limit  = VikRequest::getInt('_limit', 30, 'request');
		$_offset = VikRequest::getInt('_offset', 0, 'request');
		$next_offset = null;
		
		// default on Slave, recurse on Master, if necessary
		$e4jc_url = "https://slave.e4jconnect.com/channelmanager/?r=getproduct&c=" . $channel['name'] . "&_limit={$_limit}&_offset={$_offset}";
		
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager GETPRODUCT Request e4jConnect.com - ' . ucwords($channel['name']) . ' -->
<ManageListingsRQ xmlns="http://www.e4jconnect.com/channels/mnglstrq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . $apikey . '"/>
	<Fetch ' . implode(' ', $filters) . '/>
	<ReadListings' . (!empty($listing_id) ? ' id="' . $listing_id . '"' : '') . '>
		' . implode("\n", $read_nodes) . '
	</ReadListings>
</ManageListingsRQ>';

		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		// increase the timeout for this large request
		$e4jC->setTimeout(600);
		//
		$e4jC->slaveEnabled = true;
		$rs = $e4jC->exec();
		if ($e4jC->getErrorNo()) {
			if ($return) {
				return false;
			}
			VikError::raiseWarning('', @curl_error($e4jC->getCurlHeader()));
			$mainframe->redirect("index.php?option=com_vikchannelmanager&view=expediaproducts");
			exit;
		}
		if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			if ($return) {
				return false;
			}
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
			$mainframe->redirect("index.php?option=com_vikchannelmanager&view=expediaproducts");
			exit;
		}

		// decode the response
		$response_obj = json_decode($rs);

		if (!is_object($response_obj) || (!isset($response_obj->property) && !isset($response_obj->roomtypes))) {
			// no useful data received from the response
			if ($return) {
				return false;
			}
			VikError::raiseWarning('', 'The JSON response could not be decoded. Invalid data.');
			VikError::raiseWarning('', htmlentities($rs));
			if (function_exists('json_last_error') && $json_last_error = json_last_error()) {
				VikError::raiseWarning('', 'Last JSON decode error: ' . $json_last_error);
			}
			$mainframe->redirect("index.php?option=com_vikchannelmanager&view=expediaproducts");
			exit;
		}

		if (isset($response_obj->property)) {
			// update or insert property (hotel) information
			$q = "SELECT `id` FROM `#__vikchannelmanager_otarooms_data` WHERE `idchannel`=" . (int)$channel['uniquekey'] . " AND `account_key`=" . $dbo->quote($account_key) . " AND `param`=" . $dbo->quote('hotel_content');
			$dbo->setQuery($q, 0, 1);
			$dbo->execute();
			if ($dbo->getNumRows()) {
				// update record
				$upd_record_id = $dbo->loadResult();

				$hotel_record = new stdClass;
				$hotel_record->id = $upd_record_id;
				$hotel_record->setting = json_encode($response_obj->property);

				$dbo->updateObject('#__vikchannelmanager_otarooms_data', $hotel_record, 'id');
			} else {
				// insert record
				$hotel_record = new stdClass;
				$hotel_record->idchannel = $channel['uniquekey'];
				$hotel_record->account_key = $account_key;
				$hotel_record->param = 'hotel_content';
				$hotel_record->setting = json_encode($response_obj->property);

				$dbo->insertObject('#__vikchannelmanager_otarooms_data', $hotel_record, 'id');
			}
		}

		// parse room types
		$listings_data = isset($response_obj->roomtypes) && is_array($response_obj->roomtypes) ? $response_obj->roomtypes : [];

		// store each listing onto the database
		foreach ($listings_data as $listing_data) {
			/**
			 * Extract the information for the cursor/pagination of the next request.
			 */
			if (property_exists($listing_data, 'e4jconnect_tot_listings')) {
				// set next offset
				$next_offset = $_offset + $_limit;
				// unset this useless property
				unset($listing_data->e4jconnect_nextcursor);
			}

			// make sure the ID property is set
			if (!isset($listing_data->id) && isset($listing_data->resourceId)) {
				$listing_data->id = $listing_data->resourceId;
			}

			// check if the record exists
			$q = "SELECT `setting` FROM `#__vikchannelmanager_otarooms_data` WHERE `idchannel`=" . (int)$channel['uniquekey'] . " AND `account_key`=" . $dbo->quote($account_key) . " AND `idroomota`=" . $dbo->quote($listing_data->id) . " AND `param`=" . $dbo->quote('room_content');
			$dbo->setQuery($q, 0, 1);
			$dbo->execute();
			if ($dbo->getNumRows()) {
				/**
				 * Update existing record by checking first if the response contains less reserved properties
				 * than what we actually have stored already. In case the re-download request was made for just
				 * some listing details/information that were updated, we may not get a full response of other information.
				 */
				$prev_listing_data = json_decode($dbo->loadResult());
				if (is_object($prev_listing_data)) {
					foreach ($prev_listing_data as $prop => $val) {
						if (substr($prop, 0, 1) == '_' && !property_exists($listing_data, $prop)) {
							// protected/reserved listing property should be re-added
							$listing_data->{$prop} = $val;
						}
					}
				}

				// update record with new JSON data downloaded
				$q = "UPDATE `#__vikchannelmanager_otarooms_data` SET `setting`=" . $dbo->quote(json_encode($listing_data)) . " WHERE `idchannel`=" . (int)$channel['uniquekey'] . " AND `account_key`=" . $dbo->quote($account_key) . " AND `idroomota`=" . $dbo->quote($listing_data->id) . " AND `param`=" . $dbo->quote('room_content');
				$dbo->setQuery($q);
				$dbo->execute();
			} else {
				// create new record
				$q = "INSERT INTO `#__vikchannelmanager_otarooms_data` (`idchannel`, `account_key`, `idroomota`, `param`, `setting`) VALUES (" . (int)$channel['uniquekey'] . ", " . $dbo->quote($account_key) . ", " . $dbo->quote($listing_data->id) . ", " . $dbo->quote('room_content') . ", " . $dbo->quote(json_encode($listing_data)) . ");";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}

		if ($return) {
			return true;
		}

		// check if a next offset is necessary or available
		if ($next_offset !== null) {
			$next_page_html = '<a class="btn vcm-config-btn" href="index.php?option=com_vikchannelmanager&task=expediaproduct.download&_limit=' . $_limit . '&_offset=' . $next_offset . '" onclick="return vcmLoadListingDetails();"><i class="vboicn-cloud-download"></i> ' . JText::_('VCM_LOAD_DETAILS') . ' - ' . JText::_('VCMJQCALNEXT') . '</a>';
			$mainframe->enqueueMessage($next_page_html);
		}

		// redirect to listings page by setting the just-loaded flag
		$mainframe->redirect("index.php?option=com_vikchannelmanager&view=expediaproducts&loaded=1");
		exit;
	}

	/**
	 * Task expediaproduct.cancel goes back to the products list page.
	 */
	public function cancel()
	{
		JFactory::getApplication()->redirect('index.php?option=com_vikchannelmanager&view=expediaproducts');
	}

	/**
	 * Task expediaproduct.new redirects to the product management page.
	 */
	public function new()
	{
		JFactory::getApplication()->redirect('index.php?option=com_vikchannelmanager&view=expediamngproduct');
	}

	/**
	 * Task expediaproduct.savelisting creates a new listing (only).
	 */
	public function savelisting()
	{
		$app = JFactory::getApplication();
		$is_ajax = VikRequest::getInt('aj', 0, 'request');
		
		// create a new listing
		$result = $this->_manageListingRequest('new');

		$error = null;
		if ($result === false) {
			$error = 'e4j.error.Generic error';
		} elseif (is_string($result) && strpos($result, 'e4j.error') !== false) {
			$error = $result;
		}

		if (!empty($error)) {
			if ($is_ajax) {
				VBOHttpDocument::getInstance()->json(['error' => VikChannelManager::getErrorFromMap($error)]);
			}
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($error));
			$app->redirect("index.php?option=com_vikchannelmanager&view=expediaproducts");
			$app->close();
		}

		// if no errors occurred, load details for the newly created listing
		if (is_string($result) && strpos($result, 'e4j.ok') !== false) {
			/**
			 * When creating a new listing, the new id obtained is appended to the
			 * success response. We download ALL the information for this new listing.
			 */
			if (VikChannelManager::sleepAllowed()) {
				sleep(1);
			}
			$this->download(str_replace('e4j.ok.', '', $result), true);

			if ($is_ajax) {
				VBOHttpDocument::getInstance()->json(['ok' => 1, 'id' => str_replace('e4j.ok.', '', $result)]);
			}
		}

		if ($is_ajax) {
			if (is_string($result) && strpos($result, 'e4j.warning') !== false) {
				VBOHttpDocument::getInstance()->json(['warning' => str_replace('e4j.warning.', '', $result)]);
			} elseif (is_string($result) && strpos($result, 'e4j.error') !== false) {
				VBOHttpDocument::getInstance()->json(['error' => str_replace('e4j.error.', '', $result)]);
			} else {
				VBOHttpDocument::getInstance()->json(['error' => 'Uncaught error']);
			}
		}

		$app->enqueueMessage(JText::_('MSG_BASE_SUCCESS'));
		$app->redirect("index.php?option=com_vikchannelmanager&view=expediaproducts&loaded=1");
		exit;
	}

	/**
	 * Task expediaproduct.updatelisting updates a listing.
	 */
	public function updatelisting()
	{
		$this->_doUpdateListing();
	}

	/**
	 * Task expediaproduct.updatelisting_stay updates a listing (no redirect).
	 */
	public function updatelisting_stay()
	{
		$this->_doUpdateListing(true);
	}

	/**
	 * Task expediaproduct.reload will download and update the contents of a given listing.
	 * Useful to immediately check what was changed after an update of some contents, and to
	 * reload data that could not be downloaded at first, maybe because of a large number of rooms.
	 */
	public function reload()
	{
		$app 		= JFactory::getApplication();
		$session 	= JFactory::getSession();
		$listing_id = VikRequest::getString('listing_id', '', 'request');

		if (empty($listing_id)) {
			VikError::raiseWarning('', 'Missing listing ID');
			$app->redirect("index.php?option=com_vikchannelmanager&view=expediaproducts");
			exit;
		}

		// check listing reload counter
		$reload_counter = (int)$session->get('vcmExpediaLst' . $listing_id . 'RelCount', 0);
		if ($reload_counter > 2) {
			// stop it, too many requests
			VikError::raiseWarning('', 'Too many reloading requests for this session.');
			$app->redirect("index.php?option=com_vikchannelmanager&view=expediamngproduct&idroomota={$listing_id}");
			exit;
		}

		// download listing information
		$res = $this->download($listing_id, true, ['roomtypes', 'amenities', 'rateplans', 'ratethreshold']);

		if (!$res) {
			VikError::raiseWarning('', 'Reloading the listing information failed');
		} else {
			// increase listing reload counter
			$session->set('vcmExpediaLst' . $listing_id . 'RelCount', ++$reload_counter);
		}

		$app->enqueueMessage(JText::_('MSG_BASE_SUCCESS'));
		$app->redirect("index.php?option=com_vikchannelmanager&view=expediamngproduct&idroomota={$listing_id}");
		$app->close();
	}

	/**
	 * Protected method to update a room-type as well as others of its details.
	 * 
	 * @param 	bool 	$stay 	whether to redirect to the same page.
	 * 
	 * @return 	void
	 */
	protected function _doUpdateListing($stay = false)
	{
		$app = JFactory::getApplication();
		$is_ajax = VikRequest::getInt('aj', 0, 'request');
		$idroomota = VikRequest::getString('idroomota', '', 'request');

		// update listing and collect which APIs will be updated and will have to be reloaded
		$reload_values = [];
		$result = $this->_manageListingRequest('update', $reload_values);

		$error = null;
		if ($result === false) {
			$error = 'e4j.error.Generic error';
		} elseif (is_string($result) && strpos($result, 'e4j.error') !== false) {
			$error = $result;
		}

		if (!empty($error)) {
			if ($is_ajax) {
				VBOHttpDocument::getInstance()->json(['error' => VikChannelManager::getErrorFromMap($error)]);
			}
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($error));
			if ($stay) {
				$app->redirect("index.php?option=com_vikchannelmanager&view=expediamngproduct&idroomota={$idroomota}");
			} else {
				$app->redirect("index.php?option=com_vikchannelmanager&view=expediaproducts");
			}
			$app->close();
		}

		if (is_string($result) && strpos($result, 'e4j.warning') !== false) {
			if ($is_ajax) {
				VBOHttpDocument::getInstance()->json(['warning' => VikChannelManager::getErrorFromMap($result)]);
			} else {
				VikError::raiseWarning('', VikChannelManager::getErrorFromMap($result));
			}
		}

		/**
		 * If no errors occurred, reload listing details with the updated information.
		 * We choose to re-download only the details that were updated, to save some
		 * useless API call that would return un-changed values. $reload_values is the
		 * variable set by reference by _manageListingRequest().
		 */
		if (VikChannelManager::sleepAllowed()) {
			sleep(1);
		}
		$this->download($idroomota, true, $reload_values);
		//

		if ($is_ajax) {
			VBOHttpDocument::getInstance()->json(['ok' => 1, 'id' => $idroomota]);
		}

		$app->enqueueMessage(JText::_('MSG_BASE_SUCCESS'));
		if ($stay) {
			$app->redirect("index.php?option=com_vikchannelmanager&view=expediamngproduct&idroomota={$idroomota}");
		} else {
			$app->redirect("index.php?option=com_vikchannelmanager&view=expediaproducts&loaded=1");
		}
		$app->close();
	}

	/**
	 * Protected method to create or update a listing via API. In case
	 * of update, also other listing information (APIs) can be updated.
	 * 
	 * @param 	string 	$type 			the type of action to perform.
	 * @param 	array 	$reload_values 	optional reference-array indicating the values that should be reloaded.
	 * 
	 * @return 	mixed 					false or string in case of error, response string otherwise.
	 */
	protected function _manageListingRequest($type = 'new', &$reload_values = null)
	{
		$dbo = JFactory::getDbo();

		$idroomota = VikRequest::getString('idroomota', '', 'request');
		$listing_values = VikRequest::getVar('listing', array(), 'request', 'array');

		$apikey = VikChannelManager::getApiKey(true);
		if (empty($apikey)) {
			return false;
		}

		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);
		if (empty($channel['params']['hotelid']) || $channel['uniquekey'] != VikChannelManagerConfig::EXPEDIA) {
			return false;
		}
		$account_key = $channel['params']['hotelid'];

		if (!is_array($listing_values) || !count($listing_values)) {
			return false;
		}

		if ($type == 'update' && empty($idroomota)) {
			return false;
		}

		// load current listing data as an associative array to compare array differences
		$listing_data = [];
		if (!empty($idroomota)) {
			$q = "SELECT `setting` FROM `#__vikchannelmanager_otarooms_data` WHERE `idchannel`=" . (int)$channel['uniquekey'] . " AND `account_key`=" . $dbo->quote($account_key) . " AND `idroomota`=" . $dbo->quote($idroomota) . " AND `param`=" . $dbo->quote('room_content');
			$dbo->setQuery($q, 0, 1);
			$dbo->execute();
			if ($dbo->getNumRows()) {
				$listing_data = (array)json_decode($dbo->loadResult(), true);
			}
		}

		// build values to be reloaded (downloaded) in case of success
		if (!is_array($reload_values)) {
			$reload_values = [];
		}

		// collect the fields related to the listing (room-type)
		$listing_fields = [];
		foreach ($listing_values as $key => $val) {
			if (substr($key, 0, 1) == '_') {
				// protected, or non listing-related property value
				continue;
			}
			if (is_string($val) && ($val === 'true' || $val === 'false')) {
				// convert boolean string to proper boolean value
				$val = (bool)($val === 'true');
			}
			if (is_string($val) && !strlen($val)) {
				// we keep empty string values without "continue"
				// continue;
			} elseif (is_array($val) && !count($val)) {
				// we keep empty array values without "continue"
				// continue;
			}
			// set room-type field to be written
			$listing_fields[$key] = $val;
		}

		// listing fields validation
		if ($type == 'new') {
			$mandatory_fieldds = array(
				'partnerCode',
				'name',
				'ageCategories',
				'bedrooms',
				'smokingPreferences',
			);
			foreach ($mandatory_fieldds as $manfield) {
				if (!isset($listing_fields[$manfield])) {
					return 'e4j.error.' . ucwords(str_replace('_', ' ', $manfield)) . ' is mandatory';
				}
				if (is_string($listing_fields[$manfield]) && !strlen($listing_fields[$manfield])) {
					return 'e4j.error.' . ucwords(str_replace('_', ' ', $manfield)) . ' cannot be empty';
				}
				if (is_array($listing_fields[$manfield]) && !count($listing_fields[$manfield])) {
					return 'e4j.error.' . ucwords(str_replace('_', ' ', $manfield)) . ' cannot be empty';
				}
			}
		}

		// room-type amenities
		$listing_amenities = [];
		if (!empty($listing_values['_amenities']) && is_array($listing_values['_amenities'])) {
			// parse all room amenities for create or update
			foreach ($listing_values['_amenities'] as $room_amenity) {
				if (!is_array($room_amenity) || empty($room_amenity['code'])) {
					// invalid structure
					continue;
				}
				// push room-type amenity to be created/updated
				$listing_amenities[] = $room_amenity;
			}
		}

		// room type rate plans
		$listing_rateplans = [];
		if (!empty($listing_values['_ratePlans']) && is_array($listing_values['_ratePlans'])) {
			foreach ($listing_values['_ratePlans'] as $room_rplan) {
				// rate plans can be managed only for existing room types (update)
				if (!is_array($room_rplan) || !count($room_rplan) || (!empty($room_rplan['resourceId']) && count($room_rplan) === 1)) {
					// invalid or empty structure (updating an existing rate plan requires at least two fields, one for resourceId and one for any other info)
					continue;
				}
				// push room-type rate plan to be created/updated
				$listing_rateplans[] = $room_rplan;
			}
		}

		// build and execute the XML request for e4jConnect
		$write_nodes = [];

		// create/update listing
		$data_pushed = 0;
		foreach ($listing_fields as $field => $val) {
			if (is_scalar($val) || is_null($val)) {
				// push node
				array_push($write_nodes, '<Data type="listing" extra="' . $field . '"><![CDATA[' . (is_bool($val) ? (int)$val : (string)$val) . ']]></Data>');
				$data_pushed++;
			} elseif (is_array($val) && $val && array_keys($val) === range(0, count($val) - 1)) {
				// numeric (sequential) array
				array_push($write_nodes, '<Data type="listing" extra="' . $field . '"><![CDATA[' . json_encode($val) . ']]></Data>');
				$data_pushed++;
			} else {
				// iterable
				foreach ($val as $sub_field => $sub_val) {
					if (is_array($sub_val) || is_object($sub_val)) {
						// we do not support more recursion levels to nest array values
						$sub_val = json_encode($sub_val);
					}
					// push node
					array_push($write_nodes, '<Data type="listing" extra="' . $field . '" extra2="' . $sub_field . '"><![CDATA[' . $sub_val . ']]></Data>');
					$data_pushed++;
				}
			}
		}
		if ($data_pushed) {
			// listing details are about to be updated
			array_push($reload_values, 'roomtypes');
		}

		// create/update room-type amenities
		foreach ($listing_amenities as $room_amenity) {
			// push node
			array_push($write_nodes, '<Data type="amenities" extra="' . (isset($room_amenity['detailCode']) ? (string)$room_amenity['detailCode'] : '') . '" extra2="' . (isset($room_amenity['value']) ? (string)$room_amenity['value'] : '') . '"><![CDATA[' . $room_amenity['code'] . ']]></Data>');
		}
		if ($listing_amenities) {
			// room-type amenities are about to be updated
			array_push($reload_values, 'amenities');
		}

		// create/update room-type rate plans
		foreach ($listing_rateplans as $room_rplan) {
			// push node (if resourceId empty, a new rate plan will be created)
			array_push($write_nodes, '<Data type="rateplans" extra="' . (!empty($room_rplan['resourceId']) ? (string)$room_rplan['resourceId'] : '') . '"><![CDATA[' . json_encode($room_rplan) . ']]></Data>');
		}
		if ($listing_rateplans) {
			// room-type rate plans are about to be updated
			array_push($reload_values, 'rateplans');
		}

		// required filter by hotel ID
		$filters = array('hotelid="' . $account_key . '"');

		// listing filters
		$list_filters = [];
		if ($type == 'update') {
			$list_filters[] = 'id="' . $idroomota . '"';
		}
		$list_filters[] = 'action="' . $type . '"';
		
		// default on Slave, recurse on Master, if necessary
		$e4jc_url = "https://slave.e4jconnect.com/channelmanager/?r=wrtproduct&c=" . $channel['name'];
		
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager WRTLST Request e4jConnect.com - ' . ucwords($channel['name']) . ' -->
<ManageListingsRQ xmlns="http://www.e4jconnect.com/channels/mnglstrq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . $apikey . '"/>
	<Fetch ' . implode(' ', $filters) . '/>
	<WriteListing ' . implode(' ', $list_filters) . '>
		' . implode("\n", $write_nodes) . '
	</WriteListing>
</ManageListingsRQ>';

		if (VikRequest::getInt('e4j_debug', 0, 'request')) {
			// debug the POST and XML requests
			VBOHttpDocument::getInstance()->close(500, print_r($listing_values, true) . "\n\n" . $xml . "\n\nreload_values:\n" . print_r($reload_values, true));
		}

		// take care of large requests
		try {
			// ignore any possible abort for this large request
			ignore_user_abort(true);
			ini_set('max_execution_time', 0);
		} catch (Exception $e) {
			// do nothing
		}

		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		// increase the timeout for this large request
		$e4jC->setTimeout(600);
		//
		$e4jC->slaveEnabled = true;
		$rs = $e4jC->exec();
		if ($e4jC->getErrorNo()) {
			return 'e4j.error.Communication error, please retry later';
		}

		// return the raw response
		return $rs;
	}

	/**
	 * Task expediaproduct.delete_rateplan deletes an existing room rate plan.
	 */
	public function delete_rateplan()
	{
		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();

		$rateplan_id = VikRequest::getString('rateplan_id', '', 'request');
		$listing_id  = VikRequest::getString('listing_id', '', 'request');

		if (empty($rateplan_id) || empty($listing_id)) {
			VikError::raiseWarning('', 'Missing rate plan or room ID');
			$app->redirect("index.php?option=com_vikchannelmanager&view=expediaproducts");
			$app->close();
		}

		$apikey = VikChannelManager::getApiKey(true);
		if (empty($apikey)) {
			VikError::raiseWarning('', 'Missing API Key');
			$app->redirect("index.php?option=com_vikchannelmanager");
			$app->close();
		}

		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);
		if (empty($channel['params']['hotelid']) || $channel['uniquekey'] != VikChannelManagerConfig::EXPEDIA) {
			VikError::raiseWarning('', 'Channel not yet configured');
			$app->redirect("index.php?option=com_vikchannelmanager");
			$app->close();
		}
		$account_key = $channel['params']['hotelid'];

		// load current listing data as an associative array to compare array differences
		$q = "SELECT `setting` FROM `#__vikchannelmanager_otarooms_data` WHERE `idchannel`=" . (int)$channel['uniquekey'] . " AND `account_key`=" . $dbo->quote($account_key) . " AND `idroomota`=" . $dbo->quote($listing_id) . " AND `param`=" . $dbo->quote('room_content');
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			VikError::raiseWarning('', 'Missing listing data');
			$app->redirect("index.php?option=com_vikchannelmanager&view=expediaproducts");
			$app->close();
		}
		$listing_data = json_decode($dbo->loadResult(), true);
		if (!is_array($listing_data) || empty($listing_data['_ratePlans'])) {
			VikError::raiseWarning('', 'Invalid listing data');
			$app->redirect("index.php?option=com_vikchannelmanager&view=expediaproducts");
			$app->close();
		}

		// find the array key to remove
		$remove_key = null;
		foreach ($listing_data['_ratePlans'] as $key => $val) {
			if (!empty($val['resourceId']) && $val['resourceId'] == $rateplan_id) {
				// rate plan found
				$remove_key = $key;
				break;
			}
		}
		if (is_null($remove_key)) {
			VikError::raiseWarning('', 'Rate plan to be removed not found in current listing data');
			$app->redirect("index.php?option=com_vikchannelmanager&view=expediaproducts");
			$app->close();
		}

		// make the request to e4jConnect

		// required filter by hotel ID (host user id)
		$filters = array('hotelid="' . $account_key . '"');

		// listing filters
		$list_filters = array(
			'id="' . $listing_id . '"',
			'action="delete"',
		);

		// compose node for deletion
		$write_nodes = array(
			'<Data type="rateplans" extra="' . $rateplan_id . '">' . $listing_id . '</Data>',
		);
		
		// default on Slave, recurse on Master, if necessary
		$e4jc_url = "https://slave.e4jconnect.com/channelmanager/?r=wrtproduct&c=" . $channel['name'];
		
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager WRTLST Request e4jConnect.com - ' . ucwords($channel['name']) . ' -->
<ManageListingsRQ xmlns="http://www.e4jconnect.com/channels/mnglstrq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . $apikey . '"/>
	<Fetch ' . implode(' ', $filters) . '/>
	<WriteListing ' . implode(' ', $list_filters) . '>
		' . implode("\n", $write_nodes) . '
	</WriteListing>
</ManageListingsRQ>';

		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		$e4jC->slaveEnabled = true;
		$rs = $e4jC->exec();
		if ($e4jC->getErrorNo()) {
			VikError::raiseWarning('', 'Communication error. Please try again later');
			$app->redirect("index.php?option=com_vikchannelmanager&view=expediamngproduct&idroomota={$listing_id}");
			$app->close();
		}

		if (strpos($rs, 'e4j.ok') === false) {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
			$app->redirect("index.php?option=com_vikchannelmanager&view=expediamngproduct&idroomota={$listing_id}");
			$app->close();
		}

		// delete the room rate plan locally to avoid making a new API request
		unset($listing_data['_ratePlans'][$remove_key]);
		// reset array keys or this will no longer be an array
		if (!count($listing_data['_ratePlans'])) {
			$listing_data['_ratePlans'] = [];
		} else {
			$listing_data['_ratePlans'] = array_values($listing_data['_ratePlans']);
		}
		//
		$q = "UPDATE `#__vikchannelmanager_otarooms_data` SET `setting`=" . $dbo->quote(json_encode($listing_data)) . " WHERE `idchannel`=" . (int)$channel['uniquekey'] . " AND `account_key`=" . $dbo->quote($account_key) . " AND `idroomota`=" . $dbo->quote($listing_id) . " AND `param`=" . $dbo->quote('room_content');
		$dbo->setQuery($q);
		$dbo->execute();

		// redirect with success
		$app->enqueueMessage(JText::_('MSG_BASE_SUCCESS'));
		$app->redirect("index.php?option=com_vikchannelmanager&view=expediamngproduct&idroomota={$listing_id}");
		$app->close();
	}
}
