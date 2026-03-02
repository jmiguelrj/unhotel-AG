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

class VikChannelManagerControllerAirbnblst extends JControllerAdmin
{
	/**
	 * Task airbnblst.download to retrieve all listing details
	 * from the current Airbnb host account. This method can also
	 * be called by other methods of this class to update the contents.
	 * It is possible to filter the number of details to be retrieved in
	 * case only some data was updated. This is to save useless API calls.
	 * 
	 * @param 	string 		$listing_id 	the optional listing ID to read (all listings otherwise).
	 * @param 	bool 		$return 		whether to return the result.
	 * @param 	array 		$reload_values 	filter the API requests by only performing some.
	 * 
	 * @return 	void|bool 					if $return, a boolean will be returned.
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
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=airbnblistings");
			exit;
		}
		
		$apikey = VikChannelManager::getApiKey(true);
		if (empty($apikey)) {
			if ($return) {
				return false;
			}
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap('e4j.error.Settings'));
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=airbnblistings");
			exit;
		}

		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);
		if (empty($channel['params']['user_id']) || $channel['uniquekey'] != VikChannelManagerConfig::AIRBNBAPI) {
			if ($return) {
				return false;
			}
			VikError::raiseWarning('', 'Empty User ID for Airbnb.');
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=config");
			exit;
		}
		$account_key = $channel['params']['user_id'];

		// increase listings retrieve counter, without validating it
		$retrieve_count = (int)$session->get('vcmAirbnbLstRetCount', 0);
		$session->set('vcmAirbnbLstRetCount', ++$retrieve_count);

		// build necessary download values
		$default_download_values = array(
			'listings',
			'descriptions',
			'photos',
			'rooms',
			'bookingsettings',
			'availabilityrules',
			'pricingsettings',
		);
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
		$read_nodes = array();
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
		 * 
		 * @since 	1.8.1
		 */
		$_limit  = VikRequest::getInt('_limit', 30, 'request');
		$_offset = VikRequest::getInt('_offset', 0, 'request');
		$next_offset = null;
		
		// default on Slave, recurse on Master, if necessary
		$e4jc_url = "https://slave.e4jconnect.com/channelmanager/?r=getlst&c=" . $channel['name'] . "&_limit={$_limit}&_offset={$_offset}";
		
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager GETLST Request e4jConnect.com - ' . ucwords($channel['name']) . ' -->
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
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=airbnblistings");
			exit;
		}
		if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			if ($return) {
				return false;
			}
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=airbnblistings");
			exit;
		}

		// decode the response
		$listings_data = json_decode($rs);
		if (!is_array($listings_data) || !count($listings_data)) {
			if ($return) {
				return false;
			}
			VikError::raiseWarning('', 'The JSON response could not be decoded.');
			VikError::raiseWarning('', htmlentities($rs));
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=airbnblistings");
			exit;
		}

		// store each listing onto the database
		foreach ($listings_data as $listing_data) {
			/**
			 * Extract the information for the cursor/pagination of the next request.
			 * 
			 * @since 	1.8.1
			 */
			if (property_exists($listing_data, 'e4jconnect_tot_listings')) {
				// set next offset
				$next_offset = $_offset + $_limit;
				// unset this useless property
				unset($listing_data->e4jconnect_nextcursor);
			}

			// check if the record exists
			$q = "SELECT `setting` FROM `#__vikchannelmanager_otarooms_data` WHERE `idchannel`=" . (int)$channel['uniquekey'] . " AND `account_key`=" . $dbo->quote($account_key) . " AND `idroomota`=" . $dbo->quote($listing_data->id) . " AND `param`=" . $dbo->quote('listing_content');
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
				$q = "UPDATE `#__vikchannelmanager_otarooms_data` SET `setting`=" . $dbo->quote(json_encode($listing_data)) . " WHERE `idchannel`=" . (int)$channel['uniquekey'] . " AND `account_key`=" . $dbo->quote($account_key) . " AND `idroomota`=" . $dbo->quote($listing_data->id) . " AND `param`=" . $dbo->quote('listing_content');
				$dbo->setQuery($q);
				$dbo->execute();
			} else {
				// create new record
				$q = "INSERT INTO `#__vikchannelmanager_otarooms_data` (`idchannel`, `account_key`, `idroomota`, `param`, `setting`) VALUES (" . (int)$channel['uniquekey'] . ", " . $dbo->quote($account_key) . ", " . $dbo->quote($listing_data->id) . ", " . $dbo->quote('listing_content') . ", " . $dbo->quote(json_encode($listing_data)) . ");";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}

		if ($return) {
			return true;
		}

		// check if a next offset is necessary or available
		if ($next_offset !== null) {
			$next_page_html = '<a class="btn vcm-config-btn" href="index.php?option=com_vikchannelmanager&task=airbnblst.download&_limit=' . $_limit . '&_offset=' . $next_offset . '" onclick="return vcmLoadListingDetails();"><i class="vboicn-cloud-download"></i> ' . JText::_('VCM_LOAD_DETAILS') . ' - ' . JText::_('VCMJQCALNEXT') . '</a>';
			$mainframe->enqueueMessage($next_page_html);
		}

		// redirect to listings page by setting the just-loaded flag
		$mainframe->redirect("index.php?option=com_vikchannelmanager&task=airbnblistings&loaded=1");
		exit;
	}

	/**
	 * Task airbnblst.savelisting creates a new listing (only).
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
				echo 'e4j.error.' . VikChannelManager::getErrorFromMap($error);
				exit;
			}
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($error));
			$app->redirect("index.php?option=com_vikchannelmanager&task=airbnblistings");
			exit;
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
			//
		}

		if ($is_ajax) {
			echo is_string($result) ? $result : 'e4j.ok';
			exit;
		}

		$app->enqueueMessage(JText::_('MSG_BASE_SUCCESS'));
		$app->redirect("index.php?option=com_vikchannelmanager&task=airbnblistings&loaded=1");
		exit;
	}

	/**
	 * Task airbnblst.updatelisting updates a listing.
	 */
	public function updatelisting()
	{
		$this->_doUpdateListing();
	}

	/**
	 * Task airbnblst.updatelisting_stay updates a listing (no redirect).
	 */
	public function updatelisting_stay()
	{
		$this->_doUpdateListing(true);
	}

	/**
	 * Task airbnblst.delete_listing deletes an existing listing completely.
	 */
	public function delete_listing()
	{
		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();

		$listing_id = VikRequest::getString('listing_id', '', 'request');

		if (empty($listing_id)) {
			VikError::raiseWarning('', 'Missing listing ID');
			$app->redirect("index.php?option=com_vikchannelmanager&task=airbnblistings");
			exit;
		}

		$apikey = VikChannelManager::getApiKey(true);
		if (empty($apikey)) {
			VikError::raiseWarning('', 'Missing API Key');
			$app->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}

		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);
		if (empty($channel['params']['user_id']) || $channel['uniquekey'] != VikChannelManagerConfig::AIRBNBAPI) {
			VikError::raiseWarning('', 'Channel not yet configured');
			$app->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}
		$account_key = $channel['params']['user_id'];

		// load current listing data as an associative array to compare array differences
		$q = "SELECT `setting` FROM `#__vikchannelmanager_otarooms_data` WHERE `idchannel`=" . (int)$channel['uniquekey'] . " AND `account_key`=" . $dbo->quote($account_key) . " AND `idroomota`=" . $dbo->quote($listing_id) . " AND `param`=" . $dbo->quote('listing_content');
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			VikError::raiseWarning('', 'Missing listing data');
			$app->redirect("index.php?option=com_vikchannelmanager&task=airbnblistings");
			exit;
		}
		$listing_data = json_decode($dbo->loadResult(), true);
		if (!is_array($listing_data)) {
			VikError::raiseWarning('', 'Invalid listing data');
			$app->redirect("index.php?option=com_vikchannelmanager&task=airbnblistings");
			exit;
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
			'<Data type="listing" extra="listing_id">' . $listing_id . '</Data>',
		);
		
		// default on Slave, recurse on Master, if necessary
		$e4jc_url = "https://slave.e4jconnect.com/channelmanager/?r=wrtlst&c=" . $channel['name'];
		
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
			$app->redirect("index.php?option=com_vikchannelmanager&task=airbnbmnglisting&idroomota={$listing_id}");
			exit;
		}

		if (strpos($rs, 'e4j.ok') === false) {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
			$app->redirect("index.php?option=com_vikchannelmanager&task=airbnbmnglisting&idroomota={$listing_id}");
			exit;
		}

		// delete the listing locally
		$q = "DELETE FROM `#__vikchannelmanager_otarooms_data` WHERE `idchannel`=" . (int)$channel['uniquekey'] . " AND `account_key`=" . $dbo->quote($account_key) . " AND `idroomota`=" . $dbo->quote($listing_id) . " AND `param`=" . $dbo->quote('listing_content');
		$dbo->setQuery($q);
		$dbo->execute();
		// delete any mapping information
		$q = "DELETE FROM `#__vikchannelmanager_roomsxref` WHERE `idroomota`=" . $dbo->quote($listing_id) . " AND `idchannel`=" . (int)$channel['uniquekey'];
		$dbo->setQuery($q);
		$dbo->execute();

		// redirect with success
		$app->enqueueMessage(JText::_('MSG_BASE_SUCCESS'));
		$app->redirect("index.php?option=com_vikchannelmanager&task=airbnblistings");
		exit;
	}

	/**
	 * Task airbnblst.delete_photo deletes an existing listing photo id.
	 */
	public function delete_photo()
	{
		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();

		$photo_id = VikRequest::getString('photo_id', '', 'request');
		$listing_id = VikRequest::getString('listing_id', '', 'request');

		if (empty($photo_id) || empty($listing_id)) {
			VikError::raiseWarning('', 'Missing photo or listing ID');
			$app->redirect("index.php?option=com_vikchannelmanager&task=airbnblistings");
			exit;
		}

		$apikey = VikChannelManager::getApiKey(true);
		if (empty($apikey)) {
			VikError::raiseWarning('', 'Missing API Key');
			$app->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}

		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);
		if (empty($channel['params']['user_id']) || $channel['uniquekey'] != VikChannelManagerConfig::AIRBNBAPI) {
			VikError::raiseWarning('', 'Channel not yet configured');
			$app->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}
		$account_key = $channel['params']['user_id'];

		// load current listing data as an associative array to compare array differences
		$q = "SELECT `setting` FROM `#__vikchannelmanager_otarooms_data` WHERE `idchannel`=" . (int)$channel['uniquekey'] . " AND `account_key`=" . $dbo->quote($account_key) . " AND `idroomota`=" . $dbo->quote($listing_id) . " AND `param`=" . $dbo->quote('listing_content');
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			VikError::raiseWarning('', 'Missing listing data');
			$app->redirect("index.php?option=com_vikchannelmanager&task=airbnblistings");
			exit;
		}
		$listing_data = json_decode($dbo->loadResult(), true);
		if (!is_array($listing_data) || empty($listing_data['_photos'])) {
			VikError::raiseWarning('', 'Invalid listing data');
			$app->redirect("index.php?option=com_vikchannelmanager&task=airbnblistings");
			exit;
		}

		// find the array key to remove
		$remove_key = null;
		foreach ($listing_data['_photos'] as $key => $val) {
			if (!empty($val['id']) && $val['id'] == $photo_id) {
				// photo id found
				$remove_key = $key;
				break;
			}
			if (!empty($val['id_str']) && $val['id_str'] == $photo_id) {
				// photo id found
				$remove_key = $key;
				break;
			}
		}
		if (is_null($remove_key)) {
			VikError::raiseWarning('', 'Photo to be removed not found in current listing data');
			$app->redirect("index.php?option=com_vikchannelmanager&task=airbnblistings");
			exit;
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
			'<Data type="photos" extra="listing_id" extra2="' . $photo_id . '">' . $listing_id . '</Data>',
		);
		
		// default on Slave, recurse on Master, if necessary
		$e4jc_url = "https://slave.e4jconnect.com/channelmanager/?r=wrtlst&c=" . $channel['name'];
		
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
			$app->redirect("index.php?option=com_vikchannelmanager&task=airbnbmnglisting&idroomota={$listing_id}");
			exit;
		}

		if (strpos($rs, 'e4j.ok') === false) {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
			$app->redirect("index.php?option=com_vikchannelmanager&task=airbnbmnglisting&idroomota={$listing_id}");
			exit;
		}

		// delete the photo locally to avoid making a new API request
		unset($listing_data['_photos'][$remove_key]);
		// reset array keys or this will no longer be an array
		if (!count($listing_data['_photos'])) {
			$listing_data['_photos'] = array();
		} else {
			$listing_data['_photos'] = array_values($listing_data['_photos']);
		}
		//
		$q = "UPDATE `#__vikchannelmanager_otarooms_data` SET `setting`=" . $dbo->quote(json_encode($listing_data)) . " WHERE `idchannel`=" . (int)$channel['uniquekey'] . " AND `account_key`=" . $dbo->quote($account_key) . " AND `idroomota`=" . $dbo->quote($listing_id) . " AND `param`=" . $dbo->quote('listing_content');
		$dbo->setQuery($q);
		$dbo->execute();

		// redirect with success
		$app->enqueueMessage(JText::_('MSG_BASE_SUCCESS'));
		$app->redirect("index.php?option=com_vikchannelmanager&task=airbnbmnglisting&idroomota={$listing_id}");
		exit;
	}

	/**
	 * Task airbnblst.delete_description deletes an existing listing description locale.
	 */
	public function delete_description()
	{
		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();

		$descr_locale = VikRequest::getString('descr_locale', '', 'request');
		$listing_id = VikRequest::getString('listing_id', '', 'request');

		if (empty($descr_locale) || empty($listing_id)) {
			VikError::raiseWarning('', 'Missing locale or listing ID');
			$app->redirect("index.php?option=com_vikchannelmanager&task=airbnblistings");
			exit;
		}

		$apikey = VikChannelManager::getApiKey(true);
		if (empty($apikey)) {
			VikError::raiseWarning('', 'Missing API Key');
			$app->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}

		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);
		if (empty($channel['params']['user_id']) || $channel['uniquekey'] != VikChannelManagerConfig::AIRBNBAPI) {
			VikError::raiseWarning('', 'Channel not yet configured');
			$app->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}
		$account_key = $channel['params']['user_id'];

		// load current listing data as an associative array to compare array differences
		$q = "SELECT `setting` FROM `#__vikchannelmanager_otarooms_data` WHERE `idchannel`=" . (int)$channel['uniquekey'] . " AND `account_key`=" . $dbo->quote($account_key) . " AND `idroomota`=" . $dbo->quote($listing_id) . " AND `param`=" . $dbo->quote('listing_content');
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			VikError::raiseWarning('', 'Missing listing data');
			$app->redirect("index.php?option=com_vikchannelmanager&task=airbnblistings");
			exit;
		}
		$listing_data = json_decode($dbo->loadResult(), true);
		if (!is_array($listing_data) || empty($listing_data['_descriptions'])) {
			VikError::raiseWarning('', 'Invalid listing data');
			$app->redirect("index.php?option=com_vikchannelmanager&task=airbnblistings");
			exit;
		}

		// find the array key to remove
		$remove_key = null;
		foreach ($listing_data['_descriptions'] as $key => $val) {
			if (!empty($val['locale']) && $val['locale'] == $descr_locale) {
				// locale found
				$remove_key = $key;
				break;
			}
		}
		if (is_null($remove_key)) {
			VikError::raiseWarning('', 'Locale to be removed not found in current listing data');
			$app->redirect("index.php?option=com_vikchannelmanager&task=airbnblistings");
			exit;
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
			'<Data type="descriptions" extra="listing_id" extra2="' . $descr_locale . '">' . $listing_id . '</Data>',
		);
		
		// default on Slave, recurse on Master, if necessary
		$e4jc_url = "https://slave.e4jconnect.com/channelmanager/?r=wrtlst&c=" . $channel['name'];
		
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
			$app->redirect("index.php?option=com_vikchannelmanager&task=airbnbmnglisting&idroomota={$listing_id}");
			exit;
		}

		if (strpos($rs, 'e4j.ok') === false) {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
			$app->redirect("index.php?option=com_vikchannelmanager&task=airbnbmnglisting&idroomota={$listing_id}");
			exit;
		}

		// delete the description locally to avoid making a new API request
		unset($listing_data['_descriptions'][$remove_key]);
		// reset array keys or this will no longer be an array
		if (!count($listing_data['_descriptions'])) {
			$listing_data['_descriptions'] = array();
		} else {
			$listing_data['_descriptions'] = array_values($listing_data['_descriptions']);
		}
		//
		$q = "UPDATE `#__vikchannelmanager_otarooms_data` SET `setting`=" . $dbo->quote(json_encode($listing_data)) . " WHERE `idchannel`=" . (int)$channel['uniquekey'] . " AND `account_key`=" . $dbo->quote($account_key) . " AND `idroomota`=" . $dbo->quote($listing_id) . " AND `param`=" . $dbo->quote('listing_content');
		$dbo->setQuery($q);
		$dbo->execute();

		// redirect with success
		$app->enqueueMessage(JText::_('MSG_BASE_SUCCESS'));
		$app->redirect("index.php?option=com_vikchannelmanager&task=airbnbmnglisting&idroomota={$listing_id}");
		exit;
	}

	/**
	 * Task airbnblst.delete_room deletes an existing listing room id.
	 */
	public function delete_room()
	{
		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();

		$room_id = VikRequest::getString('room_id', '', 'request');
		$listing_id = VikRequest::getString('listing_id', '', 'request');

		if (empty($room_id) || empty($listing_id)) {
			VikError::raiseWarning('', 'Missing room or listing ID');
			$app->redirect("index.php?option=com_vikchannelmanager&task=airbnblistings");
			exit;
		}

		$apikey = VikChannelManager::getApiKey(true);
		if (empty($apikey)) {
			VikError::raiseWarning('', 'Missing API Key');
			$app->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}

		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);
		if (empty($channel['params']['user_id']) || $channel['uniquekey'] != VikChannelManagerConfig::AIRBNBAPI) {
			VikError::raiseWarning('', 'Channel not yet configured');
			$app->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}
		$account_key = $channel['params']['user_id'];

		// load current listing data as an associative array to compare array differences
		$q = "SELECT `setting` FROM `#__vikchannelmanager_otarooms_data` WHERE `idchannel`=" . (int)$channel['uniquekey'] . " AND `account_key`=" . $dbo->quote($account_key) . " AND `idroomota`=" . $dbo->quote($listing_id) . " AND `param`=" . $dbo->quote('listing_content');
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			VikError::raiseWarning('', 'Missing listing data');
			$app->redirect("index.php?option=com_vikchannelmanager&task=airbnblistings");
			exit;
		}
		$listing_data = json_decode($dbo->loadResult(), true);
		if (!is_array($listing_data) || empty($listing_data['_rooms'])) {
			VikError::raiseWarning('', 'Invalid listing data');
			$app->redirect("index.php?option=com_vikchannelmanager&task=airbnblistings");
			exit;
		}

		// find the array key to remove
		$remove_key = null;
		foreach ($listing_data['_rooms'] as $key => $val) {
			if (!empty($val['id']) && $val['id'] == $room_id) {
				// room id found
				$remove_key = $key;
				break;
			}
			if (!empty($val['id_str']) && $val['id_str'] == $room_id) {
				// room id found
				$remove_key = $key;
				break;
			}
		}
		if (is_null($remove_key)) {
			VikError::raiseWarning('', 'Room to be removed not found in current listing data');
			$app->redirect("index.php?option=com_vikchannelmanager&task=airbnblistings");
			exit;
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
			'<Data type="rooms" extra="listing_id" extra2="' . $room_id . '">' . $listing_id . '</Data>',
		);
		
		// default on Slave, recurse on Master, if necessary
		$e4jc_url = "https://slave.e4jconnect.com/channelmanager/?r=wrtlst&c=" . $channel['name'];
		
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
			$app->redirect("index.php?option=com_vikchannelmanager&task=airbnbmnglisting&idroomota={$listing_id}");
			exit;
		}

		if (strpos($rs, 'e4j.ok') === false) {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
			$app->redirect("index.php?option=com_vikchannelmanager&task=airbnbmnglisting&idroomota={$listing_id}");
			exit;
		}

		// delete the room locally to avoid making a new API request
		unset($listing_data['_rooms'][$remove_key]);
		// reset array keys or this will no longer be an array
		if (!count($listing_data['_rooms'])) {
			$listing_data['_rooms'] = array();
		} else {
			$listing_data['_rooms'] = array_values($listing_data['_rooms']);
		}
		//
		$q = "UPDATE `#__vikchannelmanager_otarooms_data` SET `setting`=" . $dbo->quote(json_encode($listing_data)) . " WHERE `idchannel`=" . (int)$channel['uniquekey'] . " AND `account_key`=" . $dbo->quote($account_key) . " AND `idroomota`=" . $dbo->quote($listing_id) . " AND `param`=" . $dbo->quote('listing_content');
		$dbo->setQuery($q);
		$dbo->execute();

		// redirect with success
		$app->enqueueMessage(JText::_('MSG_BASE_SUCCESS'));
		$app->redirect("index.php?option=com_vikchannelmanager&task=airbnbmnglisting&idroomota={$listing_id}");
		exit;
	}

	/**
	 * Task airbnblst.listingcalendars loads the Calendars of the given listing ID.
	 * This is an AJAX request.
	 * 
	 * @throws 	Exception
	 * 
	 * @since 	1.8.3
	 */
	public function listingcalendars()
	{
		$dbo = JFactory::getDbo();

		$listing_id = VikRequest::getString('listing_id', '', 'request');
		$from_date  = VikRequest::getString('from_date', '', 'request');
		$to_date 	= VikRequest::getString('to_date', '', 'request');

		if (empty($listing_id)) {
			throw new Exception('Empty listing ID', 400);
		}

		$apikey = VikChannelManager::getApiKey(true);
		if (empty($apikey)) {
			throw new Exception('Missing API Key', 400);
		}

		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);
		if (empty($channel['params']['user_id']) || $channel['uniquekey'] != VikChannelManagerConfig::AIRBNBAPI) {
			throw new Exception('Channel not yet configured', 400);
		}
		$account_key = $channel['params']['user_id'];

		// dates validation
		if (empty($from_date)) {
			$from_date = date('Y-m-d');
			$to_date   = date('Y-m-') . date('t');
		}
		if (empty($to_date)) {
			$from_ts = strtotime($from_date);
			$from_ts = $from_ts || time();
			$to_date   = date('Y-m-', $from_ts) . date('t', $from_ts);
		}
		$from_ts = strtotime($from_date);
		$to_ts   = strtotime($to_date);
		if (empty($from_ts) || empty($to_ts)) {
			throw new Exception('Invalid dates', 400);
		}
		if ($from_ts < strtotime(date('Y-m-d'))) {
			// do not accept dates in the past
			$from_ts   = time();
			$from_date = date('Y-m-d');
		}
		if ($from_ts > $to_ts) {
			$to_ts   = $from_ts;
			$to_date = $from_date;
		}

		// validate maximum days span
		$max_days     = 90;
		$dto_from 	  = new DateTime($from_date);
		$dto_to 	  = new DateTime($to_date);
		$dto_interval = $dto_from->diff($dto_to);
		$days_span 	  = (int)$dto_interval->format('%r%a');
		if ($days_span > $max_days) {
			// limit the request to 90 days ahead from start date
			$to_date = date('Y-m-d', strtotime("+ {$max_days} days", $from_ts));
		}

		// required filter by hotel ID (host user id)
		$filters = array('hotelid="' . $account_key . '"');

		// default on Slave, recurse on Master, if necessary
		$e4jc_url = "https://slave.e4jconnect.com/channelmanager/?r=getlst&c=" . $channel['name'];
		
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager GETLST Request e4jConnect.com - ' . ucwords($channel['name']) . ' -->
<ManageListingsRQ xmlns="http://www.e4jconnect.com/channels/mnglstrq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . $apikey . '"/>
	<Fetch ' . implode(' ', $filters) . '/>
	<ReadListings id="' . $listing_id . '">
		<ReadListing type="calendars">' . $from_date . '::' . $to_date . '</ReadListing>
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
			throw new Exception("Request error " . @curl_error($e4jC->getCurlHeader()), 500);
		}
		if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			throw new Exception(VikChannelManager::getErrorFromMap($rs), 500);
		}

		// decode the response
		$listings_data = json_decode($rs);
		if (!is_array($listings_data) || !count($listings_data)) {
			throw new Exception('Invalid response received.', 500);
		}
		if (!isset($listings_data[0]) || !isset($listings_data[0]->_calendars)) {
			throw new Exception('Unexpected, partially valid, response received.', 500);
		}

		// output the response
		echo json_encode($listings_data[0]->_calendars);
		exit;
	}

	/**
	 * Task airbnblst.reload will download and update the contents of a given listing.
	 * Useful to immediately check what was changed after a Bulk Action in some APIs,
	 * like the Pricing Settings, or to load photos that could not be downloaded at
	 * the time of listings import, maybe because of a large number of listings.
	 * 
	 * @since 	1.8.3
	 */
	public function reload()
	{
		$app 		= JFactory::getApplication();
		$session 	= JFactory::getSession();
		$listing_id = VikRequest::getString('listing_id', '', 'request');

		if (empty($listing_id)) {
			VikError::raiseWarning('', 'Missing listing ID');
			$app->redirect("index.php?option=com_vikchannelmanager&task=airbnblistings");
			exit;
		}

		// check listing reload counter
		$reload_counter = (int)$session->get('vcmAirbnbLst' . $listing_id . 'RelCount', 0);
		if ($reload_counter > 2) {
			// stop it, too many requests
			VikError::raiseWarning('', 'Too many reloading requests for this session.');
			$app->redirect("index.php?option=com_vikchannelmanager&task=airbnbmnglisting&idroomota={$listing_id}");
			exit;
		}

		// download listing information
		$res = $this->download($listing_id, true);

		if (!$res) {
			VikError::raiseWarning('', 'Reloading the listing information failed');
		} else {
			// increase listing reload counter
			$session->set('vcmAirbnbLst' . $listing_id . 'RelCount', ++$reload_counter);
		}

		$app->enqueueMessage(JText::_('MSG_BASE_SUCCESS'));
		$app->redirect("index.php?option=com_vikchannelmanager&task=airbnbmnglisting&idroomota={$listing_id}");
		exit;
	}

	/**
	 * Endpoint to push to Airbnb the unpublished status. Useful to unpublish again
	 * all listings that may have been re-published after a re-connection of the account.
	 * No data should be updated on the database, as this information is taken from VCM.
	 * 
	 * @return 	void
	 * 
	 * @since 	1.8.11
	 */
	public function retransmit_unpublished_status()
	{
		$app = JFactory::getApplication();

		$listing_ids = VikRequest::getVar('listing_id', array(), 'request', 'array');

		if (!$listing_ids) {
			VikError::raiseWarning('', 'No listing ids given');
			$app->redirect("index.php?option=com_vikchannelmanager&task=airbnblistings");
			$app->close();
		}

		// all listing ids must belong to the same account ID
		$apikey = VikChannelManager::getApiKey(true);
		if (empty($apikey)) {
			VikError::raiseWarning('', 'Missing API key');
			$app->redirect("index.php?option=com_vikchannelmanager&task=airbnblistings");
			$app->close();
		}

		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);
		if (empty($channel['params']['user_id']) || $channel['uniquekey'] != VikChannelManagerConfig::AIRBNBAPI) {
			VikError::raiseWarning('', 'Invalid active channel');
			$app->redirect("index.php?option=com_vikchannelmanager&task=airbnblistings");
			$app->close();
		}
		$account_key = $channel['params']['user_id'];

		try {
			// ignore any possible abort for this long request (one per listing ID)
			ignore_user_abort(true);
			ini_set('max_execution_time', 0);
		} catch (Exception $e) {
			// do nothing
		}

		// default on Slave, recurse on Master, if necessary
		$e4jc_url = "https://slave.e4jconnect.com/channelmanager/?r=wrtlst&c=" . $channel['name'];

		$tot_unpublished_ok = 0;
		foreach ($listing_ids as $listing_id) {
			if (empty($listing_id)) {
				continue;
			}

			// required filter by hotel ID (host user id)
			$filters = ['hotelid="' . $account_key . '"'];

			// listing filters
			$list_filters = [
				'id="' . $idroomota . '"',
				'action="update"',
			];

			array_push($write_nodes, '<Data type="listing" extra="' . $field . '"><![CDATA[' . $val . ']]></Data>');

			$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager WRTLST Request e4jConnect.com - ' . ucwords($channel['name']) . ' -->
<ManageListingsRQ xmlns="http://www.e4jconnect.com/channels/mnglstrq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . $apikey . '"/>
	<Fetch ' . implode(' ', $filters) . '/>
	<WriteListing ' . implode(' ', $list_filters) . '>
		<Data type="listing" extra="has_availability"><![CDATA[false]]></Data>
	</WriteListing>
</ManageListingsRQ>';

			$e4jC = new E4jConnectRequest($e4jc_url);
			$e4jC->setPostFields($xml);
			$e4jC->slaveEnabled = true;
			$result = $e4jC->exec();
			if ($e4jC->getErrorNo()) {
				VikError::raiseWarning('', 'An error occurred setting to unpublished the listing ID ' . $listing_id);
				continue;
			}

			if ($result === false) {
				VikError::raiseWarning('', 'A communication error occurred setting to unpublished the listing ID ' . $listing_id);
				continue;
			}

			if (is_string($result) && strpos($result, 'e4j.error') !== false) {
				VikError::raiseWarning('', 'Error setting to unpublished the listing ID ' . $listing_id . ': ' . VikChannelManager::getErrorFromMap($result));
				continue;
			}

			$tot_unpublished_ok++;
		}

		$app->enqueueMessage(JText::sprintf('VCM_TOT_LISTINGS_UNPUBLISHED', $tot_unpublished_ok));
		$app->redirect("index.php?option=com_vikchannelmanager&task=airbnblistings");
		$app->close();
	}

	/**
	 * AJAX endpoint to apply, retrieve, or unenroll the New Listing promotion from a listing.
	 * 
	 * @return 	void
	 * 
	 * @since 	1.8.26
	 */
	public function new_listing_promotion()
	{
		$app = JFactory::getApplication();

		$apikey = VikChannelManager::getApiKey(true);
		if (empty($apikey)) {
			VCMHttpDocument::getInstance($app)->close(500, 'Missing API Key');
		}

		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);
		if (empty($channel['params']['user_id']) || $channel['uniquekey'] != VikChannelManagerConfig::AIRBNBAPI) {
			VCMHttpDocument::getInstance($app)->close(500, 'Make sure Airbnb is the currently active channel');
		}
		$account_key = $channel['params']['user_id'];

		$action_type = $app->input->getString('action_type', 'check');
		$listing_id  = $app->input->getString('listing_id', '');

		if (!$listing_id || !in_array($action_type, ['check', 'enable', 'disable'])) {
			VCMHttpDocument::getInstance($app)->close(500, 'Invalid request action or data');
		}

		// start the transporter with slaves support on REST /v2 endpoint
		$transporter = new E4jConnectRequest("https://e4jconnect.com/channelmanager/v2/airbnb/promotions/new-listing/{$account_key}", true);
		$transporter->setBearerAuth($apikey, 'application/json')
			->setPostFields([
				'listing_ids' => (array) $listing_id,
			]);

		if (!strcasecmp($action_type, 'check')) {
			// get the current status and determine if it's eligible
			try {
				// fetch the new listing promotion status in JSON format
				$new_list_promos = $transporter->fetch('GET', 'json');

				foreach ($new_list_promos as $new_list_promo) {
					if (($new_list_promo->type ?? 'NEW_LISTING_PROMOTION') != 'NEW_LISTING_PROMOTION') {
						// unsupported promotion type
						continue;
					}

					if (($new_list_promo->listing_id ?? '') == $listing_id) {
						// proper listing was found, return the status
						VCMHttpDocument::getInstance($app)->json([
							'promoStatusCode' => ($new_list_promo->status ?? 'UNKNOWN'),
						]);
					}
				}

				// if this point is reached, it means the listing is not eligible
				throw new Exception('The listing is not eligible for this kind of promotion', 406);
			} catch (Exception $e) {
				// terminate the request
				VCMHttpDocument::getInstance($app)->close($e->getCode(), $e->getMessage());
			}
		} else {
			// perform either a POST or a DELETE request
			$rq_type = !strcasecmp($action_type, 'enable') ? 'POST' : 'DELETE';

			try {
				// execute the request (expected response in case of success is the same list of listing_ids given)
				$transporter->fetch($rq_type, 'json');

				// send the response to output
				VCMHttpDocument::getInstance($app)->json([
					'promoStatusCode' => ($action_type === 'enable' ? 'ONGOING' : 'AVAILABLE'),
				]);
			} catch (Exception $e) {
				// terminate the request
				VCMHttpDocument::getInstance($app)->close($e->getCode(), $e->getMessage());
			}
		}
	}

	/**
	 * Protected method to update a listing as well as others of its details.
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
		$reload_values = array();
		$result = $this->_manageListingRequest('update', $reload_values);

		$error = null;
		if ($result === false) {
			$error = 'e4j.error.Generic error';
		} elseif (is_string($result) && strpos($result, 'e4j.error') !== false) {
			$error = $result;
		}

		if (!empty($error)) {
			if ($is_ajax) {
				echo 'e4j.error.' . VikChannelManager::getErrorFromMap($error);
				exit;
			}
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($error));
			if ($stay) {
				$app->redirect("index.php?option=com_vikchannelmanager&task=airbnbmnglisting&idroomota={$idroomota}");
			} else {
				$app->redirect("index.php?option=com_vikchannelmanager&task=airbnblistings");
			}
			exit;
		}

		if (is_string($result) && strpos($result, 'e4j.warning') !== false) {
			if ($is_ajax) {
				$result = 'e4j.warning.' . VikChannelManager::getErrorFromMap($result);
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
			echo $result;
			exit;
		}

		$app->enqueueMessage(JText::_('MSG_BASE_SUCCESS'));
		if ($stay) {
			$app->redirect("index.php?option=com_vikchannelmanager&task=airbnbmnglisting&idroomota={$idroomota}");
		} else {
			$app->redirect("index.php?option=com_vikchannelmanager&task=airbnblistings&loaded=1");
		}
		exit;
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
		if (empty($channel['params']['user_id']) || $channel['uniquekey'] != VikChannelManagerConfig::AIRBNBAPI) {
			return false;
		}
		$account_key = $channel['params']['user_id'];

		if (!is_array($listing_values) || !count($listing_values)) {
			return false;
		}

		if ($type == 'update' && empty($idroomota)) {
			return false;
		}

		// load current listing data as an associative array to compare array differences
		$listing_data = array();
		if (!empty($idroomota)) {
			$q = "SELECT `setting` FROM `#__vikchannelmanager_otarooms_data` WHERE `idchannel`=" . (int)$channel['uniquekey'] . " AND `account_key`=" . $dbo->quote($account_key) . " AND `idroomota`=" . $dbo->quote($idroomota) . " AND `param`=" . $dbo->quote('listing_content');
			$dbo->setQuery($q, 0, 1);
			$dbo->execute();
			if ($dbo->getNumRows()) {
				$listing_data = json_decode($dbo->loadResult(), true);
			}
		}

		// build values to be reloaded (downloaded) in case of success
		if (!is_array($reload_values)) {
			$reload_values = array();
		}

		// collect the fields related to the listing
		$listing_fields = array();
		foreach ($listing_values as $key => $val) {
			if (substr($key, 0, 1) == '_') {
				// protected, or non listing-related property value
				continue;
			}
			if (is_string($val) && !strlen($val)) {
				// we skip empty string values
				continue;
			} elseif (is_array($val) && !count($val)) {
				// we skip empty array values
				continue;
			}
			$listing_fields[$key] = $val;
		}

		// listing fields validation
		if ($type == 'new') {
			$mandatory_fields = array(
				'name',
				'city',
				'country_code',
				'lat',
				'lng',
				'listing_price',
			);
			foreach ($mandatory_fields as $manfield) {
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

		/**
		 * Support for listing's check out tasks require an adaptation due to 3 nested levels array.
		 * 
		 * @since 	1.8.22
		 */
		if (isset($listing_fields['check_out_tasks']) && is_array($listing_fields['check_out_tasks']) && $listing_fields['check_out_tasks']) {
			// encode in JSON format the sub values (each check_out_task)
			$listing_fields['check_out_tasks'] = json_encode($listing_fields['check_out_tasks']);
			if (json_last_error()) {
				// we need to unset the whole property
				unset($listing_fields['check_out_tasks']);
			}
		}

		// photo upload
		$photo_upload = array();
		if (!empty($listing_values['_newphoto']) && !empty($listing_values['_newphoto']['url'])) {
			$photo_upload['url'] = $listing_values['_newphoto']['url'];
			$root_uri = JUri::root();
			if (strpos($photo_upload['url'], $root_uri) === false) {
				// prepend root URI
				$photo_upload['url'] = $root_uri . $photo_upload['url'];
			}
			if (!empty($listing_values['_newphoto']['caption'])) {
				$photo_upload['caption'] = $listing_values['_newphoto']['caption'];
			}
			if (!empty($listing_values['_newphoto']['category'])) {
				$photo_upload['category'] = $listing_values['_newphoto']['category'];
			}
			if (!empty($listing_values['_newphoto']['room_id'])) {
				$photo_upload['room_id'] = $listing_values['_newphoto']['room_id'];
			}
			if (!empty($listing_values['_newphoto']['amenity'])) {
				$photo_upload['amenity'] = $listing_values['_newphoto']['amenity'];
			}
		}

		// photo captions and sort order
		$photo_updates = array();
		if (!empty($listing_values['_photos']) && (empty($listing_data['_photos']) || $this->detectPhotoChanges($listing_data['_photos'], $listing_values['_photos']))) {
			// something has changed about photos, collect the update information
			$new_photo_pos = [];
			foreach ($listing_values['_photos']['id'] as $key => $val) {
				if (!isset($listing_values['_photos']['caption'][$key])) {
					// missing request variables
					continue;
				}

				// memorize the new sort order position of this photo
				$new_photo_pos[$val] = $listing_values['_photos']['sort_order'][$key];

				// prepare photo update container
				$photo_updates[$val] = [];
				if ($this->detectPhotoChanges($listing_data['_photos'], $listing_values['_photos'], $val)) {
					// caption or sort order has changed for this photo, so we update the caption no matter what
					$photo_updates[$val]['caption'] = $listing_values['_photos']['caption'][$key];
				}

				// default sort direction for current photo
				$curr_bool_direction = null;

				// check if the photo sort order has changed
				if ($listing_values['_photos']['prevpos'][$key] != $listing_values['_photos']['sort_order'][$key]) {
					/**
					 * Check how this photo sort order was mofified. If false, this photo was moved to the left
					 * meaning that it now has a lower sort position (will come first). If true, this photo was
					 * moved to the right, and so it has a higher sort position (will come after). In case the
					 * vars X_bool_direction is null it means we have no comparison data, so it's the first move.
					 * 
					 * @since 	1.8.6
					 */
					$curr_bool_direction = ($listing_values['_photos']['sort_order'][$key] > $listing_values['_photos']['prevpos'][$key]);
				}
				if ($curr_bool_direction !== null) {
					// update sort order for this photo because it has changed
					$photo_updates[$val]['sort_order'] = $listing_values['_photos']['sort_order'][$key];
				}

				if (!count($photo_updates[$val])) {
					// this photo does not require any update
					unset($photo_updates[$val], $new_photo_pos[$val]);
				}
			}
			/**
			 * In order to avoid unpredictable sorting results due to the single photo
			 * update API call, we sort the photos with the new highest position so that
			 * the API requests will be processed in a better ordering.
			 * 
			 * @since 	1.8.6
			 */
			if (count($new_photo_pos)) {
				// some photos have been sorted, sort new positions in a DESC order
				arsort($new_photo_pos);
				$sorted_photo_updates = [];
				foreach ($new_photo_pos as $photo_id => $new_pos) {
					$sorted_photo_updates[$photo_id] = $photo_updates[$photo_id];
				}
				// append photos that need only an update of the caption
				foreach ($photo_updates as $photo_id => $photo_upd_data) {
					if (isset($sorted_photo_updates[$photo_id])) {
						continue;
					}
					$sorted_photo_updates[$photo_id] = $photo_upd_data;
				}
				// replace photos to be updated
				$photo_updates = $sorted_photo_updates;
			}
		}

		// listing descriptions
		$listing_descriptions = array();
		if (!empty($listing_values['_descriptions']) && (empty($listing_data['_descriptions']) || $this->detectDescriptionChanges($listing_data['_descriptions'], $listing_values['_descriptions']))) {
			$locales_map = array();
			foreach ($listing_values['_descriptions']['locale'] as $key => $val) {
				if (empty($val)) {
					continue;
				}
				$locales_map[$val] = $key;
			}
			foreach ($locales_map as $locale => $key) {
				// build description values for this locale
				$listing_descriptions[$locale] = array();
				foreach ($listing_values['_descriptions'] as $descr_key => $descr_vals) {
					if ($descr_key == 'locale' || empty($descr_vals[$key])) {
						continue;
					}
					$listing_descriptions[$locale][$descr_key] = $descr_vals[$key];
				}
				if (!count($listing_descriptions[$locale])) {
					unset($listing_descriptions[$locale]);
				}
			}
		}

		// listing rooms
		$listing_rooms = array();
		if (!empty($listing_values['_rooms']) && !empty($listing_values['_rooms']['type'])) {
			// it would be hard to detect differences with the saved listing rooms, so we always proceed
			foreach ($listing_values['_rooms']['type'] as $key => $val) {
				if (empty($val)) {
					continue;
				}

				$update_room = array(
					// empty ID means that we need to create a new room, full ID means update
					'id' => (isset($listing_values['_rooms']['id']) && isset($listing_values['_rooms']['id'][$key]) ? $listing_values['_rooms']['id'][$key] : ''),
					'type' => $val,
				);

				if (isset($listing_values['_rooms']['beds']) && !empty($listing_values['_rooms']['beds'][$key]) && !empty($listing_values['_rooms']['beds'][$key]['type'])) {
					// collect room-beds information
					$room_beds = array();
					foreach ($listing_values['_rooms']['beds'][$key]['type'] as $bed_key => $bed_val) {
						if (empty($bed_val) || empty($listing_values['_rooms']['beds'][$key]['quantity'][$bed_key])) {
							continue;
						}
						array_push($room_beds, array(
							'type' => $bed_val,
							'quantity' => $listing_values['_rooms']['beds'][$key]['quantity'][$bed_key],
						));
					}
					if ($room_beds) {
						$update_room['beds'] = $room_beds;
					}
				}

				/**
				 * Room accessibility features.
				 * 
				 * @since 	1.9.10
				 */
				if (isset($listing_values['_rooms']['accessibility_features']) && !empty($listing_values['_rooms']['accessibility_features'][$key]) && !empty($listing_values['_rooms']['accessibility_features'][$key]['type'])) {
					// collect room accessibility_features information (in place of the old "amenities")
					$room_accessibility_features = array();
					foreach ($listing_values['_rooms']['accessibility_features'][$key]['type'] as $af_key => $af_val) {
						if (empty($af_val) || empty($listing_values['_rooms']['accessibility_features'][$key]['quantity'][$af_key])) {
							continue;
						}
						array_push($room_accessibility_features, array(
							'type' => $af_val,
							'quantity' => $listing_values['_rooms']['accessibility_features'][$key]['quantity'][$af_key],
						));
					}
					if ($room_accessibility_features) {
						$update_room['accessibility_features'] = $room_accessibility_features;
					}
				}

				// push room to be updated/created
				array_push($listing_rooms, $update_room);
			}
		}

		// listing booking settings
		$listing_booksettings = array();
		if (!empty($listing_values['_bookingsettings'])) {
			foreach ($listing_values['_bookingsettings'] as $key => $val) {
				if (is_string($val) && !strlen($val)) {
					// we skip empty string values
					continue;
				} elseif (is_array($val) && !count($val)) {
					// we skip empty array values
					continue;
				}
				$listing_booksettings[$key] = $val;
			}
		}

		// listing availability rules
		$listing_avrules = array();
		if (!empty($listing_values['_availabilityrules'])) {
			foreach ($listing_values['_availabilityrules'] as $key => $val) {
				if (is_string($val) && !strlen($val)) {
					// we skip empty string values
					continue;
				} elseif (is_array($val) && !count($val)) {
					// we skip empty array values
					continue;
				}
				$listing_avrules[$key] = $val;
			}
		}

		// listing pricing settings
		$listing_pricesettings = array();
		if (!empty($listing_values['_pricingsettings'])) {
			/**
			 * Normalize the default_pricing_rules for the basic discounts (early bird and last minute).
			 * We also normalize the values for the weekly and monthly discount (price) factors.
			 * 
			 * @since 	1.8.23
			 * @since 	1.8.28  added support for the weekly/monthly discount (price) factors (API version 2023-06-30)
			 */
			$default_pricing_rules = null;

			// early bird basic discount
			if (isset($listing_values['_pricingsettings']['base_earlybird_discount_amount']) || isset($listing_values['_pricingsettings']['base_earlybird_discount_days'])) {
				// turn the container into an array
				$default_pricing_rules = [];
				// build early bird discount
				$early_bird_amount = isset($listing_values['_pricingsettings']['base_earlybird_discount_amount']) ? (int)$listing_values['_pricingsettings']['base_earlybird_discount_amount'] : 0;
				$early_bird_days   = isset($listing_values['_pricingsettings']['base_earlybird_discount_days']) ? (int)$listing_values['_pricingsettings']['base_earlybird_discount_days'] : 0;
				if ($early_bird_amount && $early_bird_days) {
					// set the early bird basic discount
					$default_pricing_rules[] = [
						'rule_type' 		=> 'BOOKED_BEYOND_AT_LEAST_X_DAYS',
						'price_change' 		=> $early_bird_amount,
						'price_change_type' => 'PERCENT',
						'threshold_one' 	=> $early_bird_days,
					];
				}

				// always unset both values to keep the needed array-objects structure
				unset($listing_values['_pricingsettings']['base_earlybird_discount_amount'], $listing_values['_pricingsettings']['base_earlybird_discount_days']);
			}

			// last minute basic discount
			if (isset($listing_values['_pricingsettings']['base_lastminute_discount_amount']) || isset($listing_values['_pricingsettings']['base_lastminute_discount_days'])) {
				// turn the container into an array, if not set already
				$default_pricing_rules = $default_pricing_rules ? $default_pricing_rules : [];
				// build last minute basic discount
				$last_minute_amount = isset($listing_values['_pricingsettings']['base_lastminute_discount_amount']) ? (int)$listing_values['_pricingsettings']['base_lastminute_discount_amount'] : 0;
				$last_minute_days   = isset($listing_values['_pricingsettings']['base_lastminute_discount_days']) ? (int)$listing_values['_pricingsettings']['base_lastminute_discount_days'] : 0;
				if ($last_minute_amount && $last_minute_days) {
					// set the last minute basic discount
					$default_pricing_rules[] = [
						'rule_type' 		=> 'BOOKED_WITHIN_AT_MOST_X_DAYS',
						'price_change' 		=> $last_minute_amount,
						'price_change_type' => 'PERCENT',
						'threshold_one' 	=> $last_minute_days,
					];
				}

				// always unset both values to keep the needed array-objects structure
				unset($listing_values['_pricingsettings']['base_lastminute_discount_amount'], $listing_values['_pricingsettings']['base_lastminute_discount_days']);
			}

			// weekly discount (price) factor as a "default pricing rule"
			if (isset($listing_values['_pricingsettings']['weekly_price_factor'])) {
				// turn the container into an array, if not set already
				$default_pricing_rules = $default_pricing_rules ? $default_pricing_rules : [];

				// must be a negative float amount to define the percent discount
				$def_pr_rule_amount = (float) $listing_values['_pricingsettings']['weekly_price_factor'];
				if ($def_pr_rule_amount < 0) {
					// set the weekly discount (price) factor as a "default pricing rule"
					$default_pricing_rules[] = [
						'rule_type' 		=> 'STAYED_AT_LEAST_X_DAYS',
						'price_change' 		=> $def_pr_rule_amount,
						'price_change_type' => 'PERCENT',
						'threshold_one' 	=> 7,
					];
				}

				// unset the value so that it will be read from the "default pricing rules"
				unset($listing_values['_pricingsettings']['weekly_price_factor']);
			}

			// monthly discount (price) factor as a "default pricing rule"
			if (isset($listing_values['_pricingsettings']['monthly_price_factor'])) {
				// turn the container into an array, if not set already
				$default_pricing_rules = $default_pricing_rules ? $default_pricing_rules : [];

				// must be a negative float amount to define the percent discount
				$def_pr_rule_amount = (float) $listing_values['_pricingsettings']['monthly_price_factor'];
				if ($def_pr_rule_amount < 0) {
					// set the monthly discount (price) factor as a "default pricing rule"
					$default_pricing_rules[] = [
						'rule_type' 		=> 'STAYED_AT_LEAST_X_DAYS',
						'price_change' 		=> $def_pr_rule_amount,
						'price_change_type' => 'PERCENT',
						'threshold_one' 	=> 28,
					];
				}

				// unset the value so that it will be read from the "default pricing rules"
				unset($listing_values['_pricingsettings']['monthly_price_factor']);
			}

			// set the default pricing rules as a JSON encoded value, even if empty, but set
			if (isset($default_pricing_rules)) {
				$listing_values['_pricingsettings']['default_pricing_rules'] = json_encode($default_pricing_rules);
			}

			// parse regular fields
			foreach ($listing_values['_pricingsettings'] as $key => $val) {
				if (is_string($val) && !strlen($val)) {
					// we skip empty string values
					continue;
				} elseif (is_array($val) && !count($val)) {
					// we skip empty array values
					continue;
				}
				$listing_pricesettings[$key] = $val;
			}
		}

		// build and execute the XML request for e4jConnect
		$write_nodes = array();

		// create/update listing
		$data_pushed = 0;
		foreach ($listing_fields as $field => $val) {
			if (is_scalar($val)) {
				// push node
				array_push($write_nodes, '<Data type="listing" extra="' . $field . '"><![CDATA[' . $val . ']]></Data>');
				$data_pushed++;
			} else {
				foreach ($val as $sub_field => $sub_val) {
					if (!is_scalar($sub_val)) {
						// we do not support more levels of recursion to nest array values
						continue;
					}
					// push node
					array_push($write_nodes, '<Data type="listing" extra="' . $field . '" extra2="' . $sub_field . '"><![CDATA[' . $sub_val . ']]></Data>');
					$data_pushed++;
				}
			}
		}
		if ($data_pushed) {
			// listing details are about to be updated
			array_push($reload_values, 'listings');
		}

		// upload new photo
		if (count($photo_upload)) {
			// push nodes
			array_push($write_nodes, '<Data type="photo_upload" extra="url"><![CDATA[' . $photo_upload['url'] . ']]></Data>');
			if (!empty($photo_upload['caption'])) {
				array_push($write_nodes, '<Data type="photo_upload" extra="caption"><![CDATA[' . $photo_upload['caption'] . ']]></Data>');
			}
			if (!empty($photo_upload['category'])) {
				array_push($write_nodes, '<Data type="photo_upload" extra="category"><![CDATA[' . $photo_upload['category'] . ']]></Data>');
			}
			if (!empty($photo_upload['room_id'])) {
				array_push($write_nodes, '<Data type="photo_upload" extra="room_id"><![CDATA[' . $photo_upload['room_id'] . ']]></Data>');
			}
			if (!empty($photo_upload['amenity'])) {
				array_push($write_nodes, '<Data type="photo_upload" extra="amenity"><![CDATA[' . $photo_upload['amenity'] . ']]></Data>');
			}
			// photos are about to be updated after the upload
			array_push($reload_values, 'photos');
		}

		// photo captions and sort order
		foreach ($photo_updates as $photo_id => $photo_vals) {
			// push nodes
			array_push($write_nodes, '<Data type="photos" extra="caption" extra2="' . $photo_id . '"><![CDATA[' . $photo_vals['caption'] . ']]></Data>');
			if (!empty($photo_vals['sort_order'])) {
				array_push($write_nodes, '<Data type="photos" extra="sort_order" extra2="' . $photo_id . '">' . $photo_vals['sort_order'] . '</Data>');
			}
		}
		if (count($photo_updates) && !in_array('photos', $reload_values)) {
			// photos are about to be updated
			array_push($reload_values, 'photos');
		}

		// listing descriptions
		foreach ($listing_descriptions as $locale => $vals) {
			foreach ($vals as $field => $val) {
				// push node
				array_push($write_nodes, '<Data type="descriptions" extra="' . $field . '" extra2="' . $locale . '"><![CDATA[' . $val . ']]></Data>');
			}
		}
		if (count($listing_descriptions)) {
			// listing descriptions are about to be updated
			array_push($reload_values, 'descriptions');
		}

		// listing rooms
		$data_pushed = 0;
		foreach ($listing_rooms as $k => $room) {
			// a negative room id means we need to create a new room, update an existing id otherwise
			$room_id = !empty($room['id']) ? $room['id'] : (($k + 1) - (($k + 1) * 2));
			foreach ($room as $field => $val) {
				if ($field == 'id') {
					continue;
				}
				if (!is_scalar($val)) {
					// pass the value as a JSON encoded string
					$val = json_encode($val);
				}
				// push node
				array_push($write_nodes, '<Data type="rooms" extra="' . $field . '" extra2="' . $room_id . '"><![CDATA[' . $val . ']]></Data>');
				$data_pushed++;
			}
		}
		if ($data_pushed) {
			// listing rooms are about to be updated
			array_push($reload_values, 'rooms');
		}

		// listing booking settings
		$data_pushed = 0;
		foreach ($listing_booksettings as $field => $val) {
			if (is_scalar($val)) {
				// push node
				array_push($write_nodes, '<Data type="bookingsettings" extra="' . $field . '"><![CDATA[' . $val . ']]></Data>');
				$data_pushed++;
			} else {
				foreach ($val as $sub_field => $sub_val) {
					if (!is_scalar($sub_val)) {
						// pass the value as a JSON encoded string ("listing_expectations_for_guests")
						$sub_val = json_encode($sub_val);
					}
					// push node
					array_push($write_nodes, '<Data type="bookingsettings" extra="' . $field . '" extra2="' . $sub_field . '"><![CDATA[' . $sub_val . ']]></Data>');
					$data_pushed++;
				}
			}
		}
		if ($data_pushed) {
			// listing booking settings are about to be updated
			array_push($reload_values, 'bookingsettings');
		}

		// listing availability rules
		$data_pushed = 0;
		foreach ($listing_avrules as $field => $val) {
			if (is_scalar($val)) {
				// push node
				array_push($write_nodes, '<Data type="availabilityrules" extra="' . $field . '"><![CDATA[' . $val . ']]></Data>');
				$data_pushed++;
			} else {
				foreach ($val as $sub_field => $sub_val) {
					if (!is_scalar($sub_val)) {
						// we do not support more levels of recursion to nest array values
						continue;
					}
					// push node
					array_push($write_nodes, '<Data type="availabilityrules" extra="' . $field . '" extra2="' . $sub_field . '"><![CDATA[' . $sub_val . ']]></Data>');
					$data_pushed++;
				}
			}
		}
		if ($data_pushed) {
			// listing availability rules are about to be updated
			array_push($reload_values, 'availabilityrules');
		}

		// listing pricing settings
		$data_pushed = 0;
		foreach ($listing_pricesettings as $field => $val) {
			if (is_scalar($val)) {
				// push node
				array_push($write_nodes, '<Data type="pricingsettings" extra="' . $field . '"><![CDATA[' . $val . ']]></Data>');
				$data_pushed++;
			} else {
				foreach ($val as $sub_field => $sub_val) {
					if (!is_scalar($sub_val)) {
						// we do not support more levels of recursion to nest array values
						continue;
					}
					// push node
					array_push($write_nodes, '<Data type="pricingsettings" extra="' . $field . '" extra2="' . $sub_field . '"><![CDATA[' . $sub_val . ']]></Data>');
					$data_pushed++;
				}
			}
		}
		if ($data_pushed) {
			// listing pricing settings are about to be updated
			array_push($reload_values, 'pricingsettings');
		}

		// required filter by hotel ID (host user id)
		$filters = array('hotelid="' . $account_key . '"');

		// listing filters
		$list_filters = array();
		if ($type == 'update') {
			$list_filters[] = 'id="' . $idroomota . '"';
		}
		$list_filters[] = 'action="' . $type . '"';
		
		// default on Slave, recurse on Master, if necessary
		$e4jc_url = "https://slave.e4jconnect.com/channelmanager/?r=wrtlst&c=" . $channel['name'];
		
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
			return 'e4j.error.' . str_replace(array(':', '.'), '', print_r($listing_values, true)) . "\n\n" . str_replace(array(':', '.'), '', $xml . "\n\nreload_values:\n" . print_r($reload_values, true));
		}

		/**
		 * In case of listings with a lot of photos, by changing the sorting order
		 * of a single photo, this will trigger an update request for every photo,
		 * hence many API calls. Therefore, we need to increase the execution time
		 * as well as the timeout timing in order to avoid Gateway Timeout (504) errors.
		 * 
		 * @since 	1.8.4
		 */
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
	 * Compares the current information of the listing's photos to the values
	 * submitted to perform an update. This is to detect if changes were made.
	 * 
	 * @param 	array 	$current_photos 	_photos property of listing's data.
	 * @param 	array 	$request_photos 	_photos property of the update request.
	 * @param 	string 	$force_photo 		optional photo ID to check.
	 * 
	 * @return 	bool 						true if changes are detected, or false.
	 * 
	 * @since 	1.8.6 						introduced argument $force_photo.
	 */
	protected function detectPhotoChanges($current_photos, $request_photos, $force_photo = null)
	{
		if (empty($current_photos) || !is_array($current_photos) || empty($request_photos) || !is_array($request_photos)) {
			return true;
		}

		foreach ($request_photos['id'] as $index => $photo_id) {
			if (!empty($force_photo) && $force_photo != $photo_id) {
				// forced photo ID does not match with this one
				continue;
			}
			// find the current photo id in the saved data (we ignore the sort_order as matching photo id + caption is sufficient)
			if (!isset($current_photos[$index]) || empty($current_photos[$index]['id']) || $current_photos[$index]['id'] != $photo_id) {
				// photo ids did not match, it has to be because of a sorting of the photos
				return true;
			}
			// check if the caption has been modified
			if (!isset($current_photos[$index]['caption']) || $current_photos[$index]['caption'] != $request_photos['caption'][$index]) {
				// photo captions did not match
				return true;
			}
		}

		return false;
	}

	/**
	 * Compares the current information of the listing's descriptions to the values
	 * submitted to perform an update. This is to detect if changes were made.
	 * 
	 * @param 	array 	$current_descr 	_descriptions property of listing's data.
	 * @param 	array 	$request_descr 	_descriptions property of the update request.
	 * 
	 * @return 	bool 					true if changes are detected, or false.
	 */
	protected function detectDescriptionChanges($current_descr, $request_descr)
	{
		if (empty($current_descr) || !is_array($current_descr) || empty($request_descr) || !is_array($request_descr)) {
			return true;
		}

		foreach ($request_descr['locale'] as $index => $locale) {
			if (empty($locale)) {
				continue;
			}
			// find this locale in current data
			$locale_found = false;
			foreach ($current_descr as $k => $v) {
				if ($v['locale'] == $locale) {
					$locale_found = $k;
					break;
				}
			}
			if ($locale_found === false) {
				// new description language added
				return true;
			}
			// check if some values have changed for this locale
			foreach ($current_descr[$locale_found] as $descr_type => $descr_val) {
				if (!isset($request_descr[$descr_type]) || !isset($request_descr[$descr_type][$index])) {
					// input could be disabled, or some properties could be reserved, hence not in the form
					continue;
				}
				if ($request_descr[$descr_type][$index] != $descr_val) {
					// different string found
					return true;
				}
			}
		}

		return false;
	}
}
