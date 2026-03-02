<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

// No direct access to this file
defined('ABSPATH') or die('No script kiddies please!');

// import Joomla view library
jimport('joomla.application.component.view');

class VikChannelManagerViewexecparproducts extends JViewUI
{
	public function display($tpl = null)
	{
		/**
		 * Require just the Icons class of Vik Booking with no assets.
		 * 
		 * @since 	1.7.2
		 */
		VCM::requireFontAwesome();
		
		if (!function_exists('curl_init')) {
			echo 'e4j.error.'.VikChannelManager::getErrorFromMap('e4j.error.Curl');
			exit;
		}
		
		$config = VikChannelManager::loadConfiguration();
		$validate = array('apikey');
		foreach ($validate as $v) {
			if (empty($config[$v])) {
				echo 'e4j.error.'.VikChannelManager::getErrorFromMap('e4j.error.Settings');
				exit;
			}
		}
		
		$dbo = JFactory::getDbo();
		$q = "SELECT * FROM `#__vikbooking_rooms` ORDER BY `name` ASC;";
		$dbo->setQuery($q);
		$vbrooms = $dbo->loadAssocList();
		if (!$vbrooms) {
			echo 'e4j.error.There are no rooms in VikBooking, fetching the rooms from the OTA would be useless.';
			exit;
		}

		try {
			// ignore any possible abort for this large request
			ignore_user_abort(true);
			ini_set('max_execution_time', 0);
		} catch (Exception $e) {
			// do nothing
		}
		
		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);
		$channel['params'] = is_array($channel['params']) ? $channel['params'] : [];

		$mainparam = $channel['params'] && isset($channel['params']['hotelid']) ? $channel['params']['hotelid'] : '';
		if ($channel['uniquekey'] == VikChannelManagerConfig::PITCHUP) {
			$mainparam = $channel['params']['id'] ?? $mainparam;
		} elseif ($channel['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI) {
			$mainparam = is_array($channel['params']) && isset($channel['params']['user_id']) ? $channel['params']['user_id'] : $mainparam;
		} elseif ($channel['uniquekey'] == VikChannelManagerConfig::HOSTELWORLD) {
			$mainparam = $channel['params']['property_id'] ?? $mainparam;
		}

		// list of room types and rate plans fetched
		$channelrooms = array();

		if ($channel['uniquekey'] == VikChannelManagerConfig::GOOGLEHOTEL) {
			/**
			 * This is actually a reflection of the website rooms, not a call to the channel.
			 * It serves to transmit to the Google Hotel Ads servers the Transaction (Property Data)
			 * message (room types and rate plans) where the ARI messages will follow.
			 * 
			 * @since 	1.8.4
			 */
			$use_current_ghid = !empty($mainparam) ? $mainparam : VikChannelManager::getHotelInventoryID();
			$use_current_ghid = preg_replace("/[^0-9]+/", '', $use_current_ghid);
			$channelrooms = array(
				'Hotel' => array(
					'Id'   => $use_current_ghid,
					'Name' => (JText::_('VCMWEBSITE') . ' ' . $use_current_ghid),
				),
				'Rooms' => array(),
			);

			// grab all website rate plans
			$web_rplans = array();
			$q = "SELECT * FROM `#__vikbooking_prices` ORDER BY `name` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			if (!$dbo->getNumRows()) {
				echo 'e4j.error.There are no rate plans in VikBooking, unable to complete the mapping procedure.';
				exit;
			}
			$all_rplans = $dbo->loadAssocList();
			foreach ($all_rplans as $wrplan) {
				$web_rplans[$wrplan['id']] = array(
					'id' 				 => $wrplan['id'],
					'name' 				 => str_replace(array("'", '"'), '&#039;', $wrplan['name']),
					'breakfast_included' => $wrplan['breakfast_included'],
					'free_cancellation'  => $wrplan['free_cancellation'],
					'canc_deadline' 	 => $wrplan['canc_deadline'],
					'minlos' 			 => $wrplan['minlos'],
					'minhadv' 			 => $wrplan['minhadv'],
				);
			}

			// parse all eligible website rooms
			foreach ($vbrooms as $vbroom) {
				if (!$vbroom['avail']) {
					// rooms in VBO must be published
					continue;
				}
				$ch_room = array(
					'id' 		 => $vbroom['id'],
					'name' 		 => $vbroom['name'],
					'max_guests' => $vbroom['totpeople'],
					'min_guests' => $vbroom['mintotpeople'],
					'RatePlan' 	 => array(),
				);
				// find room rate plans (if base rates specified in Rates Table)
				$q = "SELECT DISTINCT `idprice` FROM `#__vikbooking_dispcost` WHERE `idroom`={$vbroom['id']};";
				$dbo->setQuery($q);
				$dbo->execute();
				if (!$dbo->getNumRows()) {
					// skip room because it has no rates defined
					continue;
				}
				$room_rplans = $dbo->loadAssocList();
				foreach ($room_rplans as $room_rplan) {
					// push rate plan information
					array_push($ch_room['RatePlan'], $web_rplans[$room_rplan['idprice']]);
				}

				// push website room for mapping
				array_push($channelrooms['Rooms'], $ch_room);
			}
		} elseif ($channel['uniquekey'] == VikChannelManagerConfig::VRBOAPI) {
			/**
			 * This is actually a mapping with the listings configured in VCM, not a call to the channel.
			 * Needed to transmit to the E4jConnect servers the listings that will be pulled by Vrbo.
			 * 
			 * @since 	1.8.12
			 */
			$channel['params'] = !empty($channel['params']) && !is_array($channel['params']) ? json_decode($channel['params'], true) : $channel['params'];
			$channel['params'] = is_array($channel['params']) ? $channel['params'] : [];
			$account_key = !empty($channel['params']) && !empty($channel['params']['hotelid']) ? $channel['params']['hotelid'] : VCMFactory::getConfig()->get('account_key_' . VikChannelManagerConfig::VRBOAPI, '');
			if (!$account_key) {
				echo 'e4j.error.Missing account information';
				exit;
			}

			$channelrooms = [
				'Hotel' => [
					'Id'   => $account_key,
					'Name' => "PM {$account_key}",
				],
				'Rooms' => [],
			];

			// get all the eligible listings
			$listings = VCMVrboListing::getRecords($account_key);
			if (!$listings) {
				echo 'e4j.error.No listings found, please provide the listings information for Vrbo.';
				exit;
			}

			// parse all eligible listings
			foreach ($listings as $listing) {
				$listing_data = json_decode($listing['setting']);
				$validation = VCMVrboListing::contentValidationPass($listing_data);
				if ($validation[0] !== true) {
					// listing is not eligible
					continue;
				}

				$listing_info = new JObject($listing_data);

				// build listing
				$ch_room = [
					'id' 		 => $listing['idroomota'],
					'name' 		 => $listing_info->get('name'),
					'RatePlan' 	 => [
						[
							'id'   => -1,
							'name' => 'Standard Pricing',
						],
					],
				];

				// push listing for mapping
				array_push($channelrooms['Rooms'], $ch_room);
			}

			if (!$channelrooms['Rooms']) {
				echo 'e4j.error.No eligible listings found, please provide the required listings information for Vrbo.';
				exit;
			}
		} else {
			// process a regular PAR request to the channel

			$e4jc_url = "https://e4jconnect.com/channelmanager/?r=par&c=".$channel['name'];
			$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager PAR Request e4jConnect.com - '.ucwords($channel['name']).' Module Extensionsforjoomla.com -->
<ProductsAvailabilityRatesRQ xmlns="http://www.e4jconnect.com/channels/parrq">
	<Notify client="'.JUri::root().'"/>
	<Api key="'.$config['apikey'].'"/>
	<ProductsAvailabilityRates>
		<Fetch element="products" hotelid="'.$mainparam.'"/>
		<Dates from="'.date('Y-m-d').'" to="'.date('Y-m-d').'"/>
	</ProductsAvailabilityRates>
</ProductsAvailabilityRatesRQ>';

			$e4jC = new E4jConnectRequest($e4jc_url);
			$e4jC->setPostFields($xml);
			// increase the timeout for this large request
			$e4jC->setTimeout(600);
			//
			$rs = $e4jC->exec();
			if ($e4jC->getErrorNo()) {
				echo 'e4j.error.'.@curl_error($e4jC->getCurlHeader());
				exit;
			}
			if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
				echo 'e4j.error.'.VikChannelManager::getErrorFromMap($rs);
				exit;
			}
			
			$channelrooms = unserialize($rs);
		}

		if (!is_array($channelrooms) || !isset($channelrooms['Rooms']) || !count($channelrooms['Rooms'])) {
			echo 'e4j.error.No Rooms Returned. Check your Settings.';
			exit;
		}

		$active_xref = array();
		if (is_array($channel) && !empty($channel['params'])) {
			// fetch currently active relations
			if ($channel['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI && !empty($channel['params']['user_id'])) {
				/**
				 * We use a different statement to fetch just the main "user_id" param
				 * for the previous room relations. This is to cope with authorizations revoked.
				 * 
				 * @since 	1.8.11
				 */
				$prop_params_base = json_encode(['user_id' => $channel['params']['user_id']]);
				$prop_params_base = str_replace(['{', '}'], '', $prop_params_base);
				$q = "SELECT * FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel`=" . $dbo->quote($channel['uniquekey']) . " AND `prop_params` LIKE " . $dbo->quote("%$prop_params_base%") . ";";
			} else {
				$q = "SELECT * FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel`=" . $dbo->quote($channel['uniquekey']) . " AND `prop_params`=" . $dbo->quote(json_encode($channel['params'])) . ";";
			}
			$dbo->setQuery($q);
			$active_xref = $dbo->loadAssocList();

			if (!$active_xref && in_array($channel['uniquekey'], [VikChannelManagerConfig::GOOGLEHOTEL, VikChannelManagerConfig::VRBOAPI])) {
				// by default, we populate the relations for all rooms
				foreach ($channelrooms['Rooms'] as $ch_room) {
					// build and push fake relation
					$fake_xref = array(
						'idroomvb'  => $ch_room['id'],
						'idroomota' => $ch_room['id'],
						'is_fake' 	=> 1,
					);
					array_push($active_xref, $fake_xref);
				}
			}
		}

		$this->config = $config;
		$this->vbrooms = $vbrooms;
		$this->channelrooms = $channelrooms;
		$this->active_xref = $active_xref;
		$this->channel = $channel;

		// Display the template (default.php)
		parent::display($tpl);
	}
}
