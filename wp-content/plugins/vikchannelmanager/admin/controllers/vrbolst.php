<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2023 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.mvc.controllers.admin');

class VikChannelManagerControllerVrbolst extends JControllerAdmin
{
	/**
	 * Task vrbolst.generate will generate new listings depending on
	 * the available rooms on the website created through Vik Booking.
	 * 
	 * @return 	void
	 */
	public function generate()
	{
		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();

		$q = "SELECT * FROM `#__vikbooking_rooms` WHERE `avail`=1;";
		$dbo->setQuery($q);
		$rooms = $dbo->loadAssocList();

		if (!$rooms) {
			$app->enqueueMessage('No active listings found on your website with Vik Booking.', 'error');
			$app->redirect('index.php?option=com_vikchannelmanager&view=vrbolistings');
			$app->close();
		}

		$account_key = VCMFactory::getConfig()->get('account_key_' . VikChannelManagerConfig::VRBOAPI);

		if (empty($account_key)) {
			$account_key = $this->getVrboUid();
		}

		if (!$account_key) {
			$app->redirect('index.php?option=com_vikchannelmanager&view=vrbolistings');
			$app->close();
		}

		// total listings generated
		$tot_generated = 0;

		foreach ($rooms as $room) {
			$listing_data = [
				'id' 	 	 => $room['id'],
				'name' 	 	 => $room['name'],
				'main_photo' => (!empty($room['img']) ? VBO_SITE_URI . 'resources/uploads/' . $room['img'] : ''),
				'active' 	 => false,
			];

			// make sure the record does not exist
			$q = "SELECT `id`, `setting` FROM `#__vikchannelmanager_otarooms_data` WHERE `idchannel`=" . (int)VikChannelManagerConfig::VRBOAPI . " AND `account_key`=" . $dbo->quote($account_key) . " AND `idroomota`=" . $dbo->quote($room['id']) . " AND `param`=" . $dbo->quote('listing_content');
			$dbo->setQuery($q, 0, 1);
			$prev_data = $dbo->loadObject();
			if ($prev_data) {
				// do nothing, skip to the next room because this one exists already
				continue;
			}

			// prepare record
			$listing = new stdClass;
			$listing->idchannel   = VikChannelManagerConfig::VRBOAPI;
			$listing->account_key = $account_key;
			$listing->idroomota   = $room['id'];
			$listing->param 	  = 'listing_content';
			$listing->setting 	  = json_encode($listing_data);

			$dbo->insertObject('#__vikchannelmanager_otarooms_data', $listing, 'id');
			if (!isset($listing->id)) {
				$app->enqueueMessage('Could not create the listing ' . $listing_data['name'] . ' from Vik Booking.', 'error');
				continue;
			}
			$tot_generated++;
		}

		if ($tot_generated) {
			$app->enqueueMessage(JText::_('VCM_VRBO_GEN_FROM_WEBSITE') . ": {$tot_generated}", 'success');
		}

		$app->redirect('index.php?option=com_vikchannelmanager&view=vrbolistings');
		$app->close();
	}

	/**
	 * Task vrbolst.cancel goes back to the products list page.
	 */
	public function cancel()
	{
		JFactory::getApplication()->redirect('index.php?option=com_vikchannelmanager&view=vrbolistings');
	}

	/**
	 * Task vrbolst.new redirects to the product management page.
	 */
	public function new()
	{
		JFactory::getApplication()->redirect('index.php?option=com_vikchannelmanager&view=vrbomnglisting');
	}

	/**
	 * Task vrbolst.delete_listing deletes an existing listing.
	 */
	public function delete_listing()
	{
		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();

		$listing_id = VikRequest::getString('listing_id', '', 'request');

		if (empty($listing_id)) {
			VikError::raiseWarning('', 'Missing listing ID');
			$app->redirect("index.php?option=com_vikchannelmanager&view=vrbolistings");
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
		if ($channel['uniquekey'] != VikChannelManagerConfig::VRBOAPI) {
			VikError::raiseWarning('', 'Invalid active channel');
			$app->redirect("index.php?option=com_vikchannelmanager");
			$app->close();
		}
		$account_key = !empty($channel['params']) && !empty($channel['params']['hotelid']) ? $channel['params']['hotelid'] : VCMFactory::getConfig()->get('account_key_' . VikChannelManagerConfig::VRBOAPI, '');

		if (empty($account_key)) {
			// make a request to get it
			$account_key = $this->getVrboUid();
		}

		if (empty($account_key)) {
			VikError::raiseWarning('', 'Missing account ID');
			$app->redirect("index.php?option=com_vikchannelmanager&view=vrbolistings");
			$app->close();
		}

		// load current listing data as an associative array
		$q = "SELECT `setting` FROM `#__vikchannelmanager_otarooms_data` WHERE `idchannel`=" . (int)$channel['uniquekey'] . " AND `account_key`=" . $dbo->quote($account_key) . " AND `idroomota`=" . $dbo->quote($listing_id) . " AND `param`=" . $dbo->quote('listing_content');
		$dbo->setQuery($q, 0, 1);
		$listing_setting = $dbo->loadResult();
		if (!$listing_setting) {
			VikError::raiseWarning('', 'Missing listing data');
			$app->redirect("index.php?option=com_vikchannelmanager&view=vrbolistings");
			$app->close();
		}
		$listing_data = json_decode($listing_setting, true);
		if (!is_array($listing_data)) {
			VikError::raiseWarning('', 'Invalid listing data');
			$app->redirect("index.php?option=com_vikchannelmanager&view=vrbolistings");
			$app->close();
		}

		// make the request to e4jConnect

		// required filter by hotel ID (PM id)
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
		
		// only on Master with no recursion, or data won't be synced
		$e4jc_url = "https://e4jconnect.com/channelmanager/?r=wrtlst&c=" . $channel['name'];
		
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
		$rs = $e4jC->exec();
		if ($e4jC->getErrorNo()) {
			VikError::raiseWarning('', 'Communication error. Please try again later');
			$app->redirect("index.php?option=com_vikchannelmanager&view=vrbomnglisting&idroomota={$listing_id}");
			exit;
		}

		if (strpos($rs, 'e4j.ok') === false) {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
			$app->redirect("index.php?option=com_vikchannelmanager&view=vrbomnglisting&idroomota={$listing_id}");
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
		$app->redirect("index.php?option=com_vikchannelmanager&task=vrbolistings");
		$app->close();
	}

	/**
	 * Task vrbolst.reset_listing empties data for an existing listing.
	 */
	public function reset_listing()
	{
		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();

		$listing_id = VikRequest::getString('listing_id', '', 'request');

		if (empty($listing_id)) {
			VikError::raiseWarning('', 'Missing listing ID');
			$app->redirect("index.php?option=com_vikchannelmanager&view=vrbolistings");
			$app->close();
		}

		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);
		if ($channel['uniquekey'] != VikChannelManagerConfig::VRBOAPI) {
			VikError::raiseWarning('', 'Invalid active channel');
			$app->redirect("index.php?option=com_vikchannelmanager");
			$app->close();
		}
		$account_key = !empty($channel['params']) && !empty($channel['params']['hotelid']) ? $channel['params']['hotelid'] : VCMFactory::getConfig()->get('account_key_' . VikChannelManagerConfig::VRBOAPI, '');

		if (empty($account_key)) {
			// make a request to get it
			$account_key = $this->getVrboUid();
		}

		if (empty($account_key)) {
			VikError::raiseWarning('', 'Missing account ID');
			$app->redirect("index.php?option=com_vikchannelmanager&view=vrbolistings");
			$app->close();
		}

		// load current listing data as an associative array
		$q = "SELECT `id`, `setting` FROM `#__vikchannelmanager_otarooms_data` WHERE `idchannel`=" . (int)$channel['uniquekey'] . " AND `account_key`=" . $dbo->quote($account_key) . " AND `idroomota`=" . $dbo->quote($listing_id) . " AND `param`=" . $dbo->quote('listing_content');
		$dbo->setQuery($q, 0, 1);
		$current_record = $dbo->loadObject();
		if (!$current_record) {
			VikError::raiseWarning('', 'Missing listing data');
			$app->redirect("index.php?option=com_vikchannelmanager&view=vrbolistings");
			$app->close();
		}
		$listing_data = json_decode($current_record->setting, true);
		if (!is_array($listing_data)) {
			VikError::raiseWarning('', 'Invalid listing data');
			$app->redirect("index.php?option=com_vikchannelmanager&view=vrbolistings");
			$app->close();
		}

		$reset_data = [
			'id' => $listing_data['id'],
			'name' => $listing_data['name'],
			'main_photo' => (!empty($listing_data['main_photo']) ? $listing_data['main_photo'] : ''),
			'active' => false, 
		];

		// update listing data with empty information
		$listing_record = new stdClass;
		$listing_record->id = $current_record->id;
		$listing_record->setting = json_encode($reset_data);

		$dbo->updateObject('#__vikchannelmanager_otarooms_data', $listing_record, 'id');

		// redirect with success
		$app->enqueueMessage(JText::_('MSG_BASE_SUCCESS'));
		$app->redirect("index.php?option=com_vikchannelmanager&view=vrbomnglisting&idroomota={$listing_id}");
		$app->close();
	}

	/**
	 * Task vrbolst.download_listing_regulations updates the regulation for the given listing.
	 */
	public function download_listing_regulations()
	{
		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();

		$listing_id = VikRequest::getString('listing_id', '', 'request');

		if (empty($listing_id)) {
			VikError::raiseWarning('', 'Missing listing ID');
			$app->redirect("index.php?option=com_vikchannelmanager&view=vrbolistings");
			$app->close();
		}

		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);
		if ($channel['uniquekey'] != VikChannelManagerConfig::VRBOAPI) {
			VikError::raiseWarning('', 'Invalid active channel');
			$app->redirect("index.php?option=com_vikchannelmanager");
			$app->close();
		}
		$account_key = !empty($channel['params']) && !empty($channel['params']['hotelid']) ? $channel['params']['hotelid'] : VCMFactory::getConfig()->get('account_key_' . VikChannelManagerConfig::VRBOAPI, '');

		if (empty($account_key)) {
			// make a request to get it
			$account_key = $this->getVrboUid();
		}

		if (empty($account_key)) {
			VikError::raiseWarning('', 'Missing account ID');
			$app->redirect("index.php?option=com_vikchannelmanager&view=vrbolistings");
			$app->close();
		}

		// load current listing data as an associative array
		$q = "SELECT `id`, `setting` FROM `#__vikchannelmanager_otarooms_data` WHERE `idchannel`=" . (int)$channel['uniquekey'] . " AND `account_key`=" . $dbo->quote($account_key) . " AND `idroomota`=" . $dbo->quote($listing_id) . " AND `param`=" . $dbo->quote('listing_content');
		$dbo->setQuery($q, 0, 1);
		$current_record = $dbo->loadObject();
		if (!$current_record) {
			VikError::raiseWarning('', 'Missing listing data');
			$app->redirect("index.php?option=com_vikchannelmanager&view=vrbolistings");
			$app->close();
		}
		$listing_data = json_decode($current_record->setting, true);
		if (!is_array($listing_data)) {
			VikError::raiseWarning('', 'Invalid listing data');
			$app->redirect("index.php?option=com_vikchannelmanager&view=vrbolistings");
			$app->close();
		}

		// download the information
		$regulation_requirements = VCMVrboListing::getListingComplianceRegulatoryInfo($listing_id);

		if (!is_object($regulation_requirements)) {
			$error_msg = is_string($regulation_requirements) ? $regulation_requirements : 'Could not fetch regulatory requirements';
			VikError::raiseWarning('', $error_msg);
			$app->redirect("index.php?option=com_vikchannelmanager&view=vrbomnglisting&idroomota={$listing_id}");
			$app->close();
		}

		if (!isset($regulation_requirements->data) || !isset($regulation_requirements->data->property) || !isset($regulation_requirements->data->property->district) || !isset($regulation_requirements->data->property->district->requirements)) {
			VikError::raiseWarning('', 'No relevant regulatory requirements found for or needed by this listing.');
			$app->redirect("index.php?option=com_vikchannelmanager&view=vrbomnglisting&idroomota={$listing_id}");
			$app->close();
		}

		// set requirements
		$listing_data['regulation_requirements'] = (array)$regulation_requirements->data->property->district->requirements;

		// set active status, listing URLs and Expedia IDs for listing and unit
		$lodging_supply_data = [];
		if (isset($regulation_requirements->data->property->activeStatus) && isset($regulation_requirements->data->property->activeStatus->active)) {
			$lodging_supply_data['active_status'] = $regulation_requirements->data->property->activeStatus->active;
		}
		if (isset($regulation_requirements->data->property->listings) && is_array($regulation_requirements->data->property->listings) && $regulation_requirements->data->property->listings) {
			$lodging_supply_data['urls'] = [];
			foreach ($regulation_requirements->data->property->listings as $supply_listing) {
				if (!is_object($supply_listing) || empty($supply_listing->url)) {
					continue;
				}
				// push listing URL on Vrbo and/or Expedia
				$lodging_supply_data['urls'][] = (string)$supply_listing->url;
			}
		}
		if (isset($regulation_requirements->data->property->id) && !empty($regulation_requirements->data->property->id)) {
			$lodging_supply_data['expedia_id'] = (string)$regulation_requirements->data->property->id;
		}
		if (isset($regulation_requirements->data->property->units) && is_array($regulation_requirements->data->property->units)) {
			$expedia_unit_ids = [];
			foreach ($regulation_requirements->data->property->units as $lodging_unit) {
				if (!is_object($lodging_unit) || !isset($lodging_unit->ids) || !is_array($lodging_unit->ids)) {
					continue;
				}
				foreach ($lodging_unit->ids as $lodging_unit_id) {
					if (!is_object($lodging_unit_id) || empty($lodging_unit_id->id) || empty($lodging_unit_id->idSource)) {
						continue;
					}
					if (stripos($lodging_unit_id->idSource, 'EXPEDIA') !== false) {
						// push Expedia Lodging Supply Unit ID
						$expedia_unit_ids[] = (string)$lodging_unit_id->id;
					}
				}
			}
			if ($expedia_unit_ids) {
				$lodging_supply_data['expedia_unit_ids'] = $expedia_unit_ids;
			}
		}

		if ($lodging_supply_data) {
			// set the additional lodging supply information that could only be fetched through the Expedia Lodging Supply GraphQL API
			$listing_data['supply'] = $lodging_supply_data;
		}

		// update listing data with regulatory information
		$listing_record = new stdClass;
		$listing_record->id = $current_record->id;
		$listing_record->setting = json_encode($listing_data);

		$dbo->updateObject('#__vikchannelmanager_otarooms_data', $listing_record, 'id');

		// redirect with success
		$app->enqueueMessage(JText::_('MSG_BASE_SUCCESS'));
		$app->redirect("index.php?option=com_vikchannelmanager&view=vrbomnglisting&idroomota={$listing_id}");
		$app->close();
	}

	/**
	 * Task vrbolst.savelisting creates a new listing (only).
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
			$app->redirect("index.php?option=com_vikchannelmanager&view=vrbolistings");
			$app->close();
		}

		// if no errors occurred, load details for the newly created listing
		if (is_string($result) && strpos($result, 'e4j.ok') !== false) {
			// no data should be re-downloaded from Vrbo as they do not support push, but only pull.
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
		$app->redirect("index.php?option=com_vikchannelmanager&view=vrbolistings&loaded=1");
		$app->close();
	}

	/**
	 * Task vrbolst.updatelisting updates a listing.
	 */
	public function updatelisting()
	{
		$this->_doUpdateListing();
	}

	/**
	 * Task vrbolst.updatelisting_stay updates a listing (no redirect).
	 */
	public function updatelisting_stay()
	{
		$this->_doUpdateListing(true);
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

		// update listing and collect which APIs will be updated and will have to be reloaded (none)
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
				$app->redirect("index.php?option=com_vikchannelmanager&view=vrbomnglisting&idroomota={$idroomota}");
			} else {
				$app->redirect("index.php?option=com_vikchannelmanager&view=vrbolistings");
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

		if ($is_ajax) {
			VBOHttpDocument::getInstance()->json(['ok' => 1, 'id' => $idroomota]);
		}

		$app->enqueueMessage(JText::_('MSG_BASE_SUCCESS'));
		if ($stay) {
			$app->redirect("index.php?option=com_vikchannelmanager&view=vrbomnglisting&idroomota={$idroomota}");
		} else {
			$app->redirect("index.php?option=com_vikchannelmanager&view=vrbolistings&loaded=1");
		}
		$app->close();
	}

	/**
	 * Protected method to create or update a listing via API.
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
		if ($channel['uniquekey'] != VikChannelManagerConfig::VRBOAPI) {
			return false;
		}
		$account_key = !empty($channel['params']) && !empty($channel['params']['hotelid']) ? $channel['params']['hotelid'] : VCMFactory::getConfig()->get('account_key_' . VikChannelManagerConfig::VRBOAPI, '');

		if (empty($account_key)) {
			// make a request to get it
			$account_key = $this->getVrboUid();
		}

		if (!$account_key) {
			return false;
		}

		if (!is_array($listing_values) || !$listing_values) {
			return false;
		}

		if ($type == 'update' && empty($idroomota)) {
			return false;
		}

		// load current listing data as an associative array to compare array differences
		$listing_data = [];
		$existing_record_id = null;
		$existing_record_payload = '';
		if (!empty($idroomota)) {
			$q = "SELECT `id`, `setting` FROM `#__vikchannelmanager_otarooms_data` WHERE `idchannel`=" . (int)$channel['uniquekey'] . " AND `account_key`=" . $dbo->quote($account_key) . " AND `idroomota`=" . $dbo->quote($idroomota) . " AND `param`=" . $dbo->quote('listing_content');
			$dbo->setQuery($q, 0, 1);
			$cur_listing_record = $dbo->loadObject();
			if ($cur_listing_record) {
				$listing_data = json_decode($cur_listing_record->setting, true);
				$listing_data = is_array($listing_data) ? $listing_data : [];
				$existing_record_id = $cur_listing_record->id;
				$existing_record_payload = $cur_listing_record->setting;
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
			if ($key == 'registration_details' && is_array($val)) {
				// adjust registration details to collect just one registration requirement record
				$registration_details = null;
				foreach ($val as $reg_key => $reg_data) {
					if (is_array($reg_data) && !empty($reg_data['key'])) {
						// active registration requirement found
						$registration_details = $reg_data;
						// overwrite the key of the registration record (could be 0 in case of the first array choice)
						$registration_details['key'] = $reg_key;
						break;
					}
				}
				// overwrite the value to store
				$val = $registration_details;
			}
			// set listing field to be written
			$listing_fields[$key] = $val;
		}

		// listing fields validation
		if ($type == 'new') {
			$mandatory_fieldds = array(
				'name',
				'propertyName',
				'active',
				'description',
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

		// adjust values
		if (isset($listing_fields['images']) && is_array($listing_fields['images']) && $listing_fields['images']) {
			// always reset array keys to make sure the array is numeric
			$listing_fields['images'] = array_values($listing_fields['images']);
			// always update the main photo URL
			$listing_fields['main_photo'] = ($listing_fields['images'][0]['url'] ?? '') ?: ($listing_fields['main_photo'] ?? '');
		}

		if (!empty($listing_values['_newphoto']) && is_array($listing_values['_newphoto']) && !empty($listing_values['_newphoto']['url'])) {
			// append new photo
			if (!isset($listing_fields['images']) || !is_array($listing_fields['images'])) {
				$listing_fields['images'] = [];
			}
			// calculate the ID for the new photo
			if (empty($listing_values['_newphoto']['id'])) {
				// photo file name (ID) from URL
				$fname = basename($listing_values['_newphoto']['url']);
				$fname = preg_replace("/[^a-z0-9\-\_]/i", '', strtolower($fname));
				$listing_values['_newphoto']['id'] = $fname;
			}
			// make sure to make the photo URL absolute
			if (strpos($listing_values['_newphoto']['url'], JUri::root()) !== 0) {
				$listing_values['_newphoto']['url'] = JUri::root() . $listing_values['_newphoto']['url'];
			}
			$listing_fields['images'][] = $listing_values['_newphoto'];
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
			array_push($reload_values, 'listing');
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

		if ($type == 'update' && $listing_data) {
			// merge existing array properties with new ones
			$listing_fields = VCMVrboListing::mergeAssocProperties($listing_data, $listing_fields);
		}

		if (VikRequest::getInt('e4j_debug', 0, 'request')) {
			// debug the POST and XML requests
			VBOHttpDocument::getInstance()->close(500, print_r($listing_values, true) . "\n\n" . print_r($listing_fields, true) . "\n\n" . $xml);
		}

		/**
		 * Even if we have built the XML for a possible request, for the moment we do not actually
		 * perform any request to the E4jConnect servers, as Vrbo will pull the information. They
		 * do not support any push endpoint at the moment for real-time updates of listing contents.
		 * Proceed with the local storage of the listing information.
		 */

		if ($type == 'new') {
			// store a room record in VBO with some basic details to obtain a room ID
			$vbo_room_record = new stdClass;
			$vbo_room_record->name = $listing_fields['name'];
			$vbo_room_record->info = $listing_fields['description'];
			$vbo_room_record->avail = 0;
			$vbo_room_record->units = 1;
			$vbo_room_record->fromadult = 1;
			$vbo_room_record->toadult = 2;
			$vbo_room_record->fromchild = 0;
			$vbo_room_record->tochild = 1;
			$vbo_room_record->smalldesc = $listing_fields['description'];
			$vbo_room_record->totpeople = 2;
			$vbo_room_record->mintotpeople = 1;

			$dbo->insertObject('#__vikbooking_rooms', $vbo_room_record, 'id');

			if (empty($vbo_room_record->id)) {
				VBOHttpDocument::getInstance()->close(500, 'Could not create the new room record in Vik Booking before creating the new listing');
			}

			// set the room ota id to the new value obtained
			$idroomota = $vbo_room_record->id;
		}

		// build record for insert or update
		$listing_record = new stdClass;
		if ($type == 'update') {
			$listing_record->id = $existing_record_id;
		} else {
			$listing_record->idchannel = (int)$channel['uniquekey'];
			$listing_record->account_key = $account_key;
			$listing_record->idroomota = $idroomota;
			$listing_record->param = 'listing_content';
		}
		$listing_record->setting = json_encode($listing_fields);

		if ($type == 'update') {
			// update record
			$dbo->updateObject('#__vikchannelmanager_otarooms_data', $listing_record, 'id');

			if (VCMVrboListing::comparePayloadSettings($existing_record_payload, $listing_record->setting) !== 0 && VCMVrboListing::getListingMapping($idroomota)) {
				/**
				 * Something was actually modified for this mapped listing.
				 * We perform a request to the E4jConnect servers to update
				 * the active status, and the last updated date. We will also
				 * check the compliance regulatory registration records.
				 */
				$upd_result = VCMVrboListing::notifyDataUpdated($idroomota, $listing_fields);
				if ($upd_result === false || is_string($upd_result)) {
					VCMHttpDocument::getInstance()->close(500, "Could not notify Vrbo with the listing updated information. Please try again later. " . (is_string($upd_result) ? $upd_result : ''));
				} elseif (is_array($upd_result)) {
					// update again the record with the compliance status information
					$listing_fields['compliance'] = $upd_result[0];
					$listing_record->setting = json_encode($listing_fields);
					$dbo->updateObject('#__vikchannelmanager_otarooms_data', $listing_record, 'id');
				}
			}
		} else {
			// insert record
			$dbo->insertObject('#__vikchannelmanager_otarooms_data', $listing_record, 'id');
		}

		return 'e4j.ok.' . $idroomota;
	}

	/**
	 * Migration tool from the old and deprecated verion of Vrbo iCal
	 * to the new API version of June 2023. Enables Vrbo API and/or
	 * removes the iCal version and its related calendars.
	 * 
	 * @since 	1.8.16
	 */
	public function vcm_vrbo_upgrade()
	{
		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();
		
		$api_key = VikChannelManager::getApiKey(true);

		// get current status
		$vrbo_status = VCMVrboHelper::hasDeprecatedCalendars();

		if ($vrbo_status === 0) {
			// nothing should be done
			VikError::raiseWarning('', 'No actions are needed to migrate to the API version of Vrbo');
			$app->redirect("index.php?option=com_vikchannelmanager");
			$app->close();
		}

		if ($vrbo_status === 1) {
			// activate Vrbo API
			
			// make the request to activate the new channel on e4jConnect
			$e4jc_url = "https://e4jconnect.com/channelmanager/?r=vrbomigr&c=generic";
			$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager VRBOMIGR Request e4jConnect.com - Vik Channel Manager -->
<ChannelsRQ xmlns="http://www.e4jconnect.com/schemas/charq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . $api_key . '"/>
	<Fetch question="activate" channel="' . VikChannelManagerConfig::VRBOAPI . '"/>
</ChannelsRQ>';

			$e4jC = new E4jConnectRequest($e4jc_url);
			$e4jC->setPostFields($xml);
			$rs = $e4jC->exec();
			if ($e4jC->getErrorNo()) {
				VikError::raiseWarning('', VikChannelManager::getErrorFromMap($e4jC->getErrorMsg()));
				$app->redirect("index.php?option=com_vikchannelmanager");
				$app->close();
			} elseif (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
				VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
				$app->redirect("index.php?option=com_vikchannelmanager");
				$app->close();
			}

			// attempt to decode the response with the channel information
			$channel = json_decode($rs, true);
			if (!is_array($channel) || !count($channel)) {
				VikError::raiseWarning('', 'Could not decode the JSON response');
				$app->redirect("index.php?option=com_vikchannelmanager");
				$app->close();
			}

			// activate the new channel locally
			$new_channel = new stdClass;
			$new_channel->name = $channel['channel'];
			$new_channel->params = json_encode($channel['params']);
			$new_channel->uniquekey = $channel['idchannel'];
			$new_channel->av_enabled = (int)$channel['av_enabled'];
			$new_channel->settings = json_encode($channel['settings']);
			
			if (!$dbo->insertObject('#__vikchannelmanager_channel', $new_channel, 'id')) {
				VikError::raiseWarning('', 'Could not store the new channel');
				$app->redirect("index.php?option=com_vikchannelmanager");
				$app->close();
			}

			// add success message and redirect to the page that will set the channel as active
			$app->enqueueMessage(JText::_('MSG_BASE_SUCCESS') . '!');
			$app->redirect("index.php?option=com_vikchannelmanager&task=setmodule&id=" . $new_channel->id);
			$app->close();
		}

		// status is -1: delete Vrbo iCal and related calendars

		$new_channel = VikChannelManager::getChannel(VikChannelManagerConfig::VRBOAPI);
		if (!is_array($new_channel) || !count($new_channel)) {
			VikError::raiseWarning('', 'Channel Vrbo API not found');
			$app->redirect("index.php?option=com_vikchannelmanager");
			$app->close();
		}

		// make the request to de-activate the old channel on e4jConnect
		$e4jc_url = "https://e4jconnect.com/channelmanager/?r=vrbomigr&c=generic";
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager VRBOMIGR Request e4jConnect.com - Vik Channel Manager -->
<ChannelsRQ xmlns="http://www.e4jconnect.com/schemas/charq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . $api_key . '"/>
	<Fetch question="deactivate" channel="' . VikChannelManagerConfig::VRBO . '"/>
</ChannelsRQ>';

		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		$e4jC->slaveEnabled = true;
		$rs = $e4jC->exec();
		if ($e4jC->getErrorNo()) {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($e4jC->getErrorMsg()));
			$app->redirect("index.php?option=com_vikchannelmanager");
			$app->close();
		} elseif (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
			$app->redirect("index.php?option=com_vikchannelmanager");
			$app->close();
		}

		// delete channels locally
		$q = "DELETE FROM `#__vikchannelmanager_channel` WHERE `uniquekey` IN (" . implode(', ', array_map([$dbo, 'q'], [VikChannelManagerConfig::HOMEAWAY, VikChannelManagerConfig::VRBO])) . ");";
		$dbo->setQuery($q);
		$dbo->execute();

		// delete any old iCal calendar associated
		$q = "DELETE FROM `#__vikchannelmanager_listings` WHERE `channel` IN (" . implode(', ', array_map([$dbo, 'q'], [VikChannelManagerConfig::HOMEAWAY, VikChannelManagerConfig::VRBO])) . ");";
		$dbo->setQuery($q);
		$dbo->execute();

		// clean up involved iCal calendar URLs that belong to the generic iCal channel
		VCMVrboHelper::deleteiCalCalendars(VikChannelManagerConfig::ICAL);

		// add success message and redirect to the page that will set the channel as active
		$app->enqueueMessage(JText::_('MSG_BASE_SUCCESS') . '!');
		$app->redirect("index.php?option=com_vikchannelmanager&task=setmodule&id=" . $new_channel['id']);
		$app->close();
	}

	/**
	 * Makes a request to get the UID for Vrbo. In case of
	 * success, the value obtained will be stored onto the db.
	 * 
	 * @return 	mixed 	false on failure, string otherwise.
	 */
	protected function getVrboUid()
	{
		$app = JFactory::getApplication();

		$rq = [
			'action' => 'get_uid',
			'base' 	 => JUri::root(),
			'apikey' => VikChannelManager::getApiKey(),
		];
		$transp = new E4jConnectRequest('https://e4jconnect.com/vrbo_api/xml/getter');
		$transp->slaveEnabled = true;
		$transp->setPostFields(json_encode($rq))->setHttpHeader(['Content-Type: application/json; charset=utf-8']);
		$resp = $transp->exec();

		if (!$resp) {
			$app->enqueueMessage('Could not receive the property manager ID for Vrbo', 'error');
			return false;
		}

		$json_resp = json_decode($resp);
		if (!is_object($json_resp) || empty($json_resp->uid)) {
			$app->enqueueMessage('Invalid response received for the property manager ID for Vrbo', 'error');
			return false;
		}

		$account_key = $json_resp->uid;

		VCMFactory::getConfig()->set('account_key_' . VikChannelManagerConfig::VRBOAPI, $account_key);

		return $account_key;
	}
}
