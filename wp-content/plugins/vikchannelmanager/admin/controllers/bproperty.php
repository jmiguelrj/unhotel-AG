<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2023 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.mvc.controllers.admin');

class VikChannelManagerControllerBproperty extends JControllerAdmin
{
	/**
	 * Task bproperty.download to retrieve the property details, house rules
	 * rate plans and other details from Booking.com for the currently active hotel account.
	 * 
	 * @param 	bool 		$return 		whether to return the result.
	 * @param 	array 		$reload_values 	filter the API requests by only performing some.
	 * 
	 * @return 	void|bool 					if $return, a boolean value will be returned.
	 */
	public function download($return = false, $reload_values = null)
	{
		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();
		$session = JFactory::getSession();

		if (!function_exists('curl_init')) {
			if ($return) {
				return false;
			}
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap('e4j.error.Curl'));
			$app->redirect("index.php?option=com_vikchannelmanager&view=bmngproperty");
			exit;
		}

		$apikey = VikChannelManager::getApiKey(true);
		if (empty($apikey)) {
			if ($return) {
				return false;
			}
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap('e4j.error.Settings'));
			$app->redirect("index.php?option=com_vikchannelmanager&view=bmngproperty");
			exit;
		}

		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);
		if (!$channel['params'] || empty($channel['params']['hotelid']) || $channel['uniquekey'] != VikChannelManagerConfig::BOOKING) {
			if ($return) {
				return false;
			}
			VikError::raiseWarning('', 'Empty Hotel ID for Booking.');
			$app->redirect("index.php?option=com_vikchannelmanager&task=config");
			exit;
		}
		$account_key = $channel['params']['hotelid'];

		// increase listings retrieve counter, without validating it
		$retrieve_count = (int)$session->get('vcmBookingPropdetRetCount', 0);
		$session->set('vcmBookingPropdetRetCount', ++$retrieve_count);

		// build necessary download values (must match with property keys in "hotel_content" record)
		$default_download_values = [
			'property',
			'houserules',
			'rateplans',
			'roomrates',
			'licenses',
		];

		if (is_array($reload_values)) {
			foreach ($reload_values as $k => $read_value) {
				if (!in_array($read_value, $default_download_values)) {
					// this value is unknown
					unset($reload_values[$k]);
				}
			}
			if ($reload_values) {
				// overwrite values to be downloaded
				$default_download_values = $reload_values;
			}
		}

		// make the request(s) to e4jConnect to download and store the requested information

		try {
			// ignore any possible abort for this large request
			ignore_user_abort(true);
			ini_set('max_execution_time', 0);
		} catch (Exception $e) {
			// do nothing
		}

		// property details
		$property_details = null;
		if (in_array('property', $default_download_values)) {
			// start the transporter with slaves support on REST /v2 endpoint
			$transporter = new E4jConnectRequest("https://hotels.e4jconnect.com/channelmanager/v2/bookingcom/hotel-info/{$account_key}", true);
			$transporter->setBearerAuth($apikey, 'text/xml');

			try {
				// fetch the property details in XML format
				$property_details = $transporter->fetch('GET', 'xml');
			} catch (Exception $e) {
				// an error occurred
				if ($return) {
					return false;
				}
				VikError::raiseWarning('', $e->getMessage());
				$app->redirect("index.php?option=com_vikchannelmanager&view=bmngproperty");
				$app->close();
			}
		}

		// house rules
		$house_rules = null;
		if (in_array('houserules', $default_download_values)) {
			// start the transporter with slaves support on REST /v2 endpoint
			$transporter = new E4jConnectRequest("https://hotels.e4jconnect.com/channelmanager/v2/bookingcom/house-rules/{$account_key}", true);
			$transporter->setBearerAuth($apikey, 'application/json');

			try {
				// fetch the house rules in JSON format
				$house_rules = $transporter->fetch('GET', 'json');
			} catch (Exception $e) {
				// this API may not be available for non-eligible properties
				if (stripos($e->getMessage(), 'eligible') !== false) {
					// wrap the response error into an object that will be displayed in the View
					$house_rules = new stdClass;
					$house_rules->error = new stdClass;
					$house_rules->error->message = $e->getMessage();
				} else {
					// an unexpected error occurred, do nothing for this secondary API, unless it was the only one requested
					if ($return && count($default_download_values) === 1) {
						return false;
					}
				}
			}
		}

		// rate plans
		$rate_plans = null;
		if (in_array('rateplans', $default_download_values)) {
			// start the transporter with slaves support on REST /v2 endpoint
			$transporter = new E4jConnectRequest("https://hotels.e4jconnect.com/channelmanager/v2/bookingcom/rate-plans/{$account_key}", true);
			$transporter->setBearerAuth($apikey, 'text/xml');

			try {
				// fetch the rate plan details in XML format
				$rate_plans = $transporter->fetch('GET', 'xml');
			} catch (Exception $e) {
				// an error occurred, do nothing but raise an error for this secondary endpoint
				VikError::raiseWarning('', $e->getMessage());
			}
		}

		// room rates (products)
		$room_rates = null;
		if (in_array('roomrates', $default_download_values)) {
			// start the transporter with slaves support on REST /v2 endpoint
			$transporter = new E4jConnectRequest("https://hotels.e4jconnect.com/channelmanager/v2/bookingcom/room-rates/{$account_key}", true);
			$transporter->setBearerAuth($apikey, 'text/xml');

			try {
				// fetch the room rate (products) details in XML format
				$room_rates = $transporter->fetch('GET', 'xml');
			} catch (Exception $e) {
				// an error occurred, do nothing but raise an error for this secondary endpoint
				VikError::raiseWarning('', $e->getMessage());
			}
		}

		// house rules
		$licenses = null;
		if (in_array('licenses', $default_download_values)) {
			// start the transporter with slaves support on REST /v2 endpoint
			$transporter = new E4jConnectRequest("https://hotels.e4jconnect.com/channelmanager/v2/bookingcom/licenses/{$account_key}", true);
			$transporter->setBearerAuth($apikey, 'application/json');

			try {
				// fetch the licenses in JSON format
				$licenses = $transporter->fetch('GET', 'json');
			} catch (Exception $e) {
				// this API may not be available for properties with no country license requirements
				// wrap the response error into an object that will be displayed in the View
				$licenses = new stdClass;
				$licenses->error = new stdClass;
				$licenses->error->message = JText::_('VCM_NO_LICENSE_REQUIREMENTS');
			}
		}

		// prepare data to store
		$hotel_content = new stdClass;
		$hotel_content->property   = $property_details ? $property_details->asXml() : $property_details;
		$hotel_content->houserules = $house_rules;
		$hotel_content->rateplans  = $rate_plans ? $rate_plans->asXml() : $rate_plans;
		$hotel_content->roomrates  = $room_rates ? $room_rates->asXml() : $room_rates;
		$hotel_content->licenses   = $licenses;

		// check if previous data exists
		$q = $dbo->getQuery(true)
			->select('*')
			->from($dbo->qn('#__vikchannelmanager_otarooms_data'))
			->where($dbo->qn('idchannel') . ' = ' . (int)$channel['uniquekey'])
			->where($dbo->qn('account_key') . ' = ' . $dbo->q($account_key))
			->where($dbo->qn('param') . ' = ' . $dbo->q('hotel_content'));

		$dbo->setQuery($q, 0, 1);
		$hotel_record = $dbo->loadObject();

		if (!$hotel_record) {
			// create new record
			$hotel_record = new stdClass;
			$hotel_record->idchannel   = $channel['uniquekey'];
			$hotel_record->account_key = $account_key;
			$hotel_record->param 	   = 'hotel_content';
			$hotel_record->setting 	   = json_encode($hotel_content);

			$dbo->insertObject('#__vikchannelmanager_otarooms_data', $hotel_record, 'id');
		} else {
			// update existing record
			$prev_record = json_decode($hotel_record->setting);
			if (is_object($prev_record)) {
				// overwrite only the properties (re-)downloaded
				foreach ($default_download_values as $api_type) {
					$prev_record->{$api_type} = $hotel_content->{$api_type};
				}
				// keep any previous property not (re-)downloaded
				$hotel_record->setting = json_encode($prev_record);
			} else {
				// replace the whole value
				$hotel_record->setting = json_encode($hotel_content);
			}

			$dbo->updateObject('#__vikchannelmanager_otarooms_data', $hotel_record, 'id');
		}

		if ($return) {
			return true;
		}

		// redirect to listings page by setting the just-loaded flag
		$app->redirect("index.php?option=com_vikchannelmanager&view=bmngproperty&loaded=1");
		$app->close();
	}

	/**
	 * Task bproperty.cancel goes back to the products list page.
	 */
	public function cancel()
	{
		JFactory::getApplication()->redirect('index.php?option=com_vikchannelmanager&view=bmngproperty');
	}

	/**
	 * Task bproperty.save will update the property details and/or the house rules.
	 */
	public function save()
	{
		$this->_updatePropertyDetailsHouseRules($stay = true);
	}

	/**
	 * Task bproperty.saveclose will update the property details and/or the house rules.
	 */
	public function saveclose()
	{
		$this->_updatePropertyDetailsHouseRules();
	}

	/**
	 * To be called via AJAX, updates the values provided by the View "bmngproperty"
	 */
	protected function _updatePropertyDetailsHouseRules($stay = false)
	{
		$app = JFactory::getApplication();
		$is_ajax = VikRequest::getInt('aj', 0, 'request');

		$apikey = VikChannelManager::getApiKey(true);
		if (empty($apikey)) {
			VCMHttpDocument::getInstance($app)->close(500, 'Missing API Key');
		}

		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);
		if (!$channel['params'] || empty($channel['params']['hotelid']) || $channel['uniquekey'] != VikChannelManagerConfig::BOOKING) {
			VCMHttpDocument::getInstance($app)->close(500, 'Empty Hotel ID for Booking.');
		}
		$account_key = $channel['params']['hotelid'];

		if (VikRequest::getInt('e4j_debug', 0, 'request')) {
			VCMHttpDocument::getInstance($app)->close(400, print_r($app->input->post->getArray(), true));
		}

		// values to be reloaded after an update
		$reload_values = [];

		// supported request values
		$property 			  = VikRequest::getVar('property', [], 'request', 'array');
		$paymentpreferences   = VikRequest::getVar('paymentpreferences', [], 'request', 'array');
		$longstayinfo 		  = VikRequest::getVar('longstayinfo', [], 'request', 'array');
		$services 			  = VikRequest::getVar('services', [], 'request', 'array');
		$contactinfos 		  = VikRequest::getVar('contactinfos', [], 'request', 'array');
		$taxpolicies 		  = VikRequest::getVar('taxpolicies', [], 'request', 'array');
		$feepolicies 		  = VikRequest::getVar('feepolicies', [], 'request', 'array');
		$cancpolicies 		  = VikRequest::getVar('cancpolicies', [], 'request', 'array');
		$policyinfo 		  = VikRequest::getVar('policyinfo', [], 'request', 'array');
		$petspolicy 		  = VikRequest::getVar('petspolicy', [], 'request', 'array');
		$house_rules 		  = VikRequest::getVar('houserules', [], 'request', 'array');
		$rateplans_create 	  = VikRequest::getVar('rateplans_create', [], 'request', 'array');
		$rateplans_edit 	  = VikRequest::getVar('rateplans_edit', [], 'request', 'array');
		$rateplans_activate   = VikRequest::getVar('rateplans_activate', [], 'request', 'array');
		$rateplans_deactivate = VikRequest::getVar('rateplans_deactivate', [], 'request', 'array');
		$roomrates_add 		  = VikRequest::getVar('roomrates_add', [], 'request', 'array');
		$roomrates_update 	  = VikRequest::getVar('roomrates_update', [], 'request', 'array');
		$roomrates_remove 	  = VikRequest::getVar('roomrates_remove', [], 'request', 'array');
		$licenses 	  		  = VikRequest::getVar('licenses', [], 'request', 'array');

		if ($house_rules) {
			// start the transporter with slaves support on REST /v2 endpoint
			$transporter = new E4jConnectRequest("https://hotels.e4jconnect.com/channelmanager/v2/bookingcom/house-rules/{$account_key}", true);
			$transporter->setBearerAuth($apikey, 'application/json')
				->setPostFields($house_rules);

			try {
				// update house rules in JSON format and get the response
				$house_rules_resp = $transporter->fetch('PUT', 'json');

				// push endpoint for reloading
				$reload_values[] = 'houserules';
			} catch (Exception $e) {
				// raise an error
				VCMHttpDocument::getInstance()->close($e->getCode(), $e->getMessage());
			}
		}

		if ($property || $paymentpreferences || $longstayinfo || $services || $contactinfos || $taxpolicies || $feepolicies || $cancpolicies || $policyinfo || $petspolicy) {
			// start the transporter with slaves support on REST /v2 endpoint
			$transporter = new E4jConnectRequest("https://hotels.e4jconnect.com/channelmanager/v2/bookingcom/hotel-info/{$account_key}", true);
			$transporter->setBearerAuth($apikey, 'application/json')
				->setPostFields([
					'property' 			 => $property,
					'paymentpreferences' => $paymentpreferences,
					'longstayinfo' 		 => $longstayinfo,
					'services' 			 => $services,
					'contactinfos' 		 => $contactinfos,
					'taxpolicies' 		 => $taxpolicies,
					'feepolicies' 		 => $feepolicies,
					'cancpolicies' 		 => $cancpolicies,
					'policyinfo' 		 => $policyinfo,
					'petspolicy' 		 => $petspolicy,
				]);

			try {
				// update hotel info (OTA_HotelDescriptiveContentNotif) and get the response
				$hotel_content_notif_resp = $transporter->fetch('PUT');

				// push endpoint for reloading
				$reload_values[] = 'property';
			} catch (Exception $e) {
				// raise an error
				VCMHttpDocument::getInstance()->close($e->getCode(), $e->getMessage());
			}
		}

		if ($rateplans_create || $rateplans_edit || $rateplans_activate || $rateplans_deactivate) {
			// rate plans management endpoint
			foreach ($rateplans_create as &$create_rplan) {
				$create_rplan = json_decode($create_rplan);
			}

			// unset last reference
			unset($create_rplan);

			// start the transporter with slaves support on REST /v2 endpoint
			$transporter = new E4jConnectRequest("https://hotels.e4jconnect.com/channelmanager/v2/bookingcom/rate-plans/{$account_key}", true);
			$transporter->setBearerAuth($apikey, 'application/json')
				->setPostFields([
					'create' 	 => $rateplans_create,
					'update'	 => $rateplans_edit,
					'activate' 	 => $rateplans_activate,
					'deactivate' => $rateplans_deactivate,
				]);

			try {
				// update rate plans and get the response
				$transporter->fetch('PUT');

				// push endpoint for reloading
				$reload_values[] = 'rateplans';
			} catch (Exception $e) {
				// raise an error
				VCMHttpDocument::getInstance()->close($e->getCode(), $e->getMessage());
			}
		}

		if ($roomrates_add || $roomrates_update || $roomrates_remove) {
			// room-rate relations endpoint

			// convert all JSON string representations into objects
			foreach ($roomrates_add as &$create_roomrate) {
				$create_roomrate = json_decode($create_roomrate);
			}
			// unset last reference
			unset($create_roomrate);

			foreach ($roomrates_update as &$update_roomrate) {
				$update_roomrate = json_decode($update_roomrate);
			}
			// unset last reference
			unset($update_roomrate);

			foreach ($roomrates_remove as &$remove_roomrate) {
				$remove_roomrate = json_decode($remove_roomrate);
			}
			// unset last reference
			unset($remove_roomrate);

			// start the transporter with slaves support on REST /v2 endpoint
			$transporter = new E4jConnectRequest("https://hotels.e4jconnect.com/channelmanager/v2/bookingcom/room-rates/{$account_key}", true);
			$transporter->setBearerAuth($apikey, 'application/json')
				->setPostFields([
					'create' => $roomrates_add,
					'update' => $roomrates_update,
					'remove' => $roomrates_remove,
				]);

			try {
				// update room rates and get the response
				$transporter->fetch('PUT');

				// push endpoint for reloading
				$reload_values[] = 'roomrates';
			} catch (Exception $e) {
				// raise an error
				VCMHttpDocument::getInstance()->close($e->getCode(), $e->getMessage());
			}
		}

		if ($licenses && !empty($licenses['variant'])) {
			// licenses endpoint

			// clean up the whole array by only taking the real values (variants) to be updated
			$license_variants = [];
			foreach ($licenses['variant'] as $level_code => $variant_id) {
				if (!$variant_id || !isset($licenses[$level_code]) || !isset($licenses[$level_code][$variant_id])) {
					// missing data submitted
					continue;
				}

				// push proper license variant ID and value(s)
				$license_variants[$level_code] = [
					$variant_id => $licenses[$level_code][$variant_id],
				];
			}

			if ($license_variants) {
				// start the transporter with slaves support on REST /v2 endpoint
				$transporter = new E4jConnectRequest("https://hotels.e4jconnect.com/channelmanager/v2/bookingcom/licenses/{$account_key}", true);
				$transporter->setBearerAuth($apikey, 'application/json')
					->setPostFields($license_variants);

				try {
					// update licenses and get the response
					$transporter->fetch('PUT');

					// push endpoint for reloading
					$reload_values[] = 'licenses';
				} catch (Exception $e) {
					// raise an error
					VCMHttpDocument::getInstance()->close($e->getCode(), $e->getMessage());
				}
			}
		}

		// store the updated information after re-downloading them
		$warn_mess = null;
		if ($reload_values) {
			$this->download($return = true, $reload_values);
		} else {
			$warn_mess = JText::_('VCM_NOTHING_TO_SAVE');
		}

		if ($is_ajax) {
			VBOHttpDocument::getInstance()->json(['ok' => 1, 'warning' => $warn_mess]);
		}

		$app->enqueueMessage(JText::_('MSG_BASE_SUCCESS'));
		if ($stay) {
			$app->redirect("index.php?option=com_vikchannelmanager&view=bmngproperty&loaded=");
		} else {
			$app->redirect("index.php?option=com_vikchannelmanager&view=bmngproperty&loaded=1");
		}
		$app->close();
	}

	/**
	 * Task bproperty.contract_status to be called via AJAX will query the contract details.
	 */
	public function contract_status()
	{
		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();

		$apikey = VikChannelManager::getApiKey(true);
		if (empty($apikey)) {
			VCMHttpDocument::getInstance()->close(500, 'Missing API Key');
		}

		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);
		if (!$channel['params'] || empty($channel['params']['hotelid']) || $channel['uniquekey'] != VikChannelManagerConfig::BOOKING) {
			VCMHttpDocument::getInstance()->close(500, 'Empty Hotel ID for Booking.');
		}

		$account_key = $channel['params']['hotelid'];

		$legal_email = $app->input->getString('legal_email');
		if (empty($legal_email)) {
			VCMHttpDocument::getInstance()->close(500, 'Missing legal email');
		}

		// start the transporter with slaves support on REST /v2 endpoint
		$transporter = new E4jConnectRequest("https://hotels.e4jconnect.com/channelmanager/v2/bookingcom/contracting/{$account_key}?email={$legal_email}", true);
		$transporter->setBearerAuth($apikey, 'application/x-www-form-urlencoded');

		try {
			// fetch contract status in JSON format
			$contract_data = $transporter->fetch('GET', 'json');

			if (!is_object($contract_data) || empty($contract_data->legal_entity_id)) {
				throw new Exception('Could not find the legal entity ID in the response', 500);
			}
		} catch (Exception $e) {
			// raise an error
			VCMHttpDocument::getInstance()->close($e->getCode(), $e->getMessage());
		}

		// check if previous data exists
		$q = $dbo->getQuery(true)
			->select('*')
			->from($dbo->qn('#__vikchannelmanager_otarooms_data'))
			->where($dbo->qn('idchannel') . ' = ' . (int)$channel['uniquekey'])
			->where($dbo->qn('account_key') . ' = ' . $dbo->q($account_key))
			->where($dbo->qn('param') . ' = ' . $dbo->q('hotel_content'));

		$dbo->setQuery($q, 0, 1);
		$hotel_record = $dbo->loadObject();

		if (!$hotel_record) {
			// raise an error
			VCMHttpDocument::getInstance()->close(500, 'Missing property details for fetching the contract status');
		}

		// update the information on the existing record
		$prev_record = json_decode($hotel_record->setting);

		if (!is_object($prev_record)) {
			$prev_record = new stdClass;
		}

		if (!isset($prev_record->contracts)) {
			$prev_record->contracts = new stdClass;
		}

		$prev_record->contracts->legal_email = $legal_email;
		$prev_record->contracts->data = $contract_data;

		// keep any other property
		$hotel_record->setting = json_encode($prev_record);

		$dbo->updateObject('#__vikchannelmanager_otarooms_data', $hotel_record, 'id');

		VBOHttpDocument::getInstance()->json($prev_record->contracts);
	}

	/**
	 * Task bproperty.contract_resend_link to be called via AJAX will request the link to be sent to the legal email.
	 */
	public function contract_resend_link()
	{
		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();

		$session = JFactory::getSession();

		$total_attempts = $session->get('vcmBcomContractResendLink', 0);
		if ($total_attempts > 0) {
			VCMHttpDocument::getInstance()->close(500, 'The email with link to contracting tools was already requested to be re-sent.');
		}

		$apikey = VikChannelManager::getApiKey(true);
		if (empty($apikey)) {
			VCMHttpDocument::getInstance()->close(500, 'Missing API Key');
		}

		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);
		if (!$channel['params'] || empty($channel['params']['hotelid']) || $channel['uniquekey'] != VikChannelManagerConfig::BOOKING) {
			VCMHttpDocument::getInstance()->close(500, 'Empty Hotel ID for Booking.');
		}

		$account_key = $channel['params']['hotelid'];

		// check if previous data exists
		$q = $dbo->getQuery(true)
			->select('*')
			->from($dbo->qn('#__vikchannelmanager_otarooms_data'))
			->where($dbo->qn('idchannel') . ' = ' . (int)$channel['uniquekey'])
			->where($dbo->qn('account_key') . ' = ' . $dbo->q($account_key))
			->where($dbo->qn('param') . ' = ' . $dbo->q('hotel_content'));

		$dbo->setQuery($q, 0, 1);
		$hotel_record = $dbo->loadObject();

		if (!$hotel_record) {
			// raise an error
			VCMHttpDocument::getInstance()->close(500, 'Missing property details for checking the contract status');
		}

		// decode the contract information
		$prev_record = json_decode($hotel_record->setting);

		if (!is_object($prev_record) || !isset($prev_record->contracts) || !is_object($prev_record->contracts)) {
			VCMHttpDocument::getInstance()->close(500, 'Missing contract status details');
		}

		// validate the current contract details
		if (empty($prev_record->contracts->data) || !isset($prev_record->contracts->data->legal_contact_email)) {
			VCMHttpDocument::getInstance()->close(500, 'Missing legal email, please fetch the contract details first');
		}

		if (!isset($prev_record->contracts->data->contract_signed) || !empty($prev_record->contracts->data->contract_signed)) {
			VCMHttpDocument::getInstance()->close(500, 'The contract may have been signed already, unable to request a new link to the contracting tool');
		}

		// start the transporter with slaves support on REST /v2 endpoint
		$transporter = new E4jConnectRequest("https://hotels.e4jconnect.com/channelmanager/v2/bookingcom/contracting/{$account_key}", true);
		$transporter->setBearerAuth($apikey, 'application/json')
			->setPostFields([
				'email' => $prev_record->contracts->data->legal_contact_email,
			]);

		try {
			// request the resending of the email with link to contracting tool
			$transporter->fetch('PUT');
		} catch (Exception $e) {
			// raise an error
			VCMHttpDocument::getInstance()->close($e->getCode(), $e->getMessage());
		}

		// update session value to prevent multiple attempts on this session
		$session->set('vcmBcomContractResendLink', 1);

		VBOHttpDocument::getInstance()->json(['ok' => 1]);
	}
}
