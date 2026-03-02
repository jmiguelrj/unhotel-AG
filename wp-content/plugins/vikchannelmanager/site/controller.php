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

/**
 * The front-end section of VikChannelManager does not have any output, it is only meant
 * to generate responses in JSON format. All the methods below should not return any
 * PHP Strict Standards, Notice, Warning or Error messages but if any plugin published on the website
 * will cause some, the JSON responses will be corrupted and therefore, impossible to be decoded.
 * For this reason it is safer to force the error_reporting to None to suppress any
 * PHP message and ensure the JSON responses to be valid.
 */
$er_l = isset($_REQUEST['error_reporting']) && intval($_REQUEST['error_reporting'] == '-1') ? -1 : 0;
error_reporting($er_l);
//

jimport('joomla.application.component.controller');

class VikchannelmanagerController extends JControllerUI
{
	public function display($cachable = false, $urlparams = false)
	{
		$view = VikRequest::getVar('view', '');
		if ($view == 'default') {
			VikRequest::setVar('view', 'default');
		} else {
			VikRequest::setVar('view', 'default');
		}
		parent::display();
	}
	
	/**
	 * A_RSL Availability Update Response Listener
	 * Retrieves the response from e4jConnect of a AR_RQ
	 * that was previously sent to save the Notification
	 */
	public function a_rsl()
	{
		$dbo = JFactory::getDbo();

		$response = 'e4j.error';

		$porderid = VikRequest::getInt('orderid', 0, 'request');
		$pnkey = VikRequest::getString('nkey', '', 'request');
		$pchannel = VikRequest::getInt('channel', '', 'request');
		$pecode = VikRequest::getString('ecode', '', 'request');
		$pemessage = VikRequest::getString('emessage', '', 'request');

		if (!empty($porderid) && !empty($pnkey)) {
			$ecode = '0';
			$valsecode = array('0', '1', '2');
			$ecode = in_array($pecode, $valsecode) ? $pecode : $ecode;
			
			$q = "SELECT * FROM `#__vikchannelmanager_keys` WHERE `idordervb`=" . (int)$porderid . " AND `key`=" . $dbo->quote($pnkey) . " ORDER BY `id` DESC;";
			$dbo->setQuery($q);
			$keys = $dbo->loadAssocList();
			if ($keys) {
				// check if notification should be saved as new or as a child
				$q = "SELECT `n`.* FROM `#__vikchannelmanager_notifications` AS `n` WHERE `n`.`from`='VCM' AND `n`.`idordervb`=" . (int)$keys[0]['idordervb'] . " ORDER BY `n`.`id` DESC";
				$dbo->setQuery($q, 0, 1);
				$notification = $dbo->loadAssoc();
				if ($notification) {
					$id_parent = $notification['id'];
					$set_channel = 0;
					$channel_info = VikChannelManager::getChannel($pchannel);
					if ($channel_info) {
						$set_channel = (int)$channel_info['uniquekey'];
					}
					$q = "INSERT INTO `#__vikchannelmanager_notification_child` (`id_parent`,`type`,`cont`,`channel`) VALUES(".(int)$id_parent.", ".(int)$ecode.", ".$dbo->quote($pemessage).", ".$set_channel.");";
					$dbo->setQuery($q);
					$dbo->execute();
					$child_id = $dbo->insertId();

					/**
					 * Notifications coming for a Booking Request Accepted should store
					 * a record in the booking history of VikBooking so that the event
					 * with success will be tracked also there. I.E. e4j.OK.Airbnb.ACCPRR_RS
					 * 
					 * @since 	1.8.0
					 */
					if ((int)$ecode === 1 && strpos($pemessage, 'ACCPRR_RS') !== false && count($channel_info)) {
						$say_channel_name = (string)$set_channel == VikChannelManagerConfig::AIRBNBAPI ? 'Airbnb' : ucfirst($channel_info['name']);
						// try to update the VBO Booking History
						try {
							if (!class_exists('VikBooking')) {
								require_once VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'lib.vikbooking.php';
							}
							if (method_exists('VikBooking', 'getBookingHistoryInstance')) {
								VikBooking::getBookingHistoryInstance()->setBid((int)$keys[0]['idordervb'])->store('TC', $say_channel_name . ': ' . JText::_('VCM_ACCPRR_RS_SUCCESS'));
							}
						} catch (Exception $e) {
							// do nothing
						}
					}

					// get new type for parent notification
					$set_type = (int)$notification['type'];
					$all_types = array(intval($ecode));

					$q = "SELECT * FROM `#__vikchannelmanager_notification_child` WHERE `id_parent`=".(int)$id_parent." AND `id`!=".(int)$child_id.";";
					$dbo->setQuery($q);
					$child_types = $dbo->loadAssocList();

					foreach ($child_types as $ctype) {
						$all_types[] = intval($ctype['type']);
					}

					foreach ($all_types as $newtype) {
						if ($newtype == 0) {
							$set_type = 0;
							break;
						}
						if ($newtype == 2) {
							$set_type = 2;
						}
					}

					// set parent Notification to be read and update time and type
					$q = "UPDATE `#__vikchannelmanager_notifications` SET `ts`=".time().", `type`=".$set_type.", `read`=0 WHERE `id`=".(int)$id_parent.";";
					$dbo->setQuery($q);
					$dbo->execute();
				} else {
					$q = "INSERT INTO `#__vikchannelmanager_notifications` (`ts`,`type`,`from`,`cont`,`idordervb`) VALUES('".time()."', ".(int)$ecode.", 'e4jConnect', ".$dbo->quote($pemessage).", '".$keys[0]['idordervb']."');";
					$dbo->setQuery($q);
					$dbo->execute();
				}

				// clean up the notification keys leaving the ones for the last 20 bookings
				$key_ord_lim = (int)$keys[0]['idordervb'] - 20;
				if ($key_ord_lim > 0) {
					$q = "DELETE FROM `#__vikchannelmanager_keys` WHERE `idordervb`<".$key_ord_lim." AND `idordervb`>0;";
					$dbo->setQuery($q);
					$dbo->execute();
				}

				$response = 'e4j.ok';
			} else {
				$response .= '.InvalidOrderKey';
			}
		} else {
			$response .= '.MissingOrderIdOrNkey';
		}

		echo $response;
		exit;
	}
	
	/**
	 * CUSTA_RSL Availability Update Response Listener
	 * Retrieves the response from e4jConnect of a AR_RQ
	 * that was previously sent to save the Notification
	 */
	public function custa_rsl()
	{
		$dbo = JFactory::getDbo();

		$response = 'e4j.error';

		$pnkey = VikRequest::getString('nkey', '', 'request');
		$pchannel = VikRequest::getInt('channel', '', 'request');
		$pecode = VikRequest::getString('ecode', '', 'request');
		$pemessage = VikRequest::getString('emessage', '', 'request');

		if (!empty($pnkey)) {
			$ecode = '0';
			$valsecode = array('0', '1', '2');
			$ecode = in_array($pecode, $valsecode) ? $pecode : $ecode;

			$q = "SELECT * FROM `#__vikchannelmanager_keys` WHERE `idordervb`=0 AND `key`=" . $dbo->q($pnkey) . " ORDER BY `id` DESC;";
			$dbo->setQuery($q);
			$keys = $dbo->loadAssocList();
			if ($keys) {
				if (!empty($keys[0]['id_notification'])) {
					$set_channel = 0;
					$channel_info = VikChannelManager::getChannel($pchannel);
					if ($channel_info) {
						$set_channel = (int)$channel_info['uniquekey'];
					}

					$q = "INSERT INTO `#__vikchannelmanager_notification_child` (`id_parent`,`type`,`cont`,`channel`) VALUES(".(int)$keys[0]['id_notification'].", ".(int)$ecode.", ".$dbo->quote($pemessage).", ".$set_channel.");";
					$dbo->setQuery($q);
					$dbo->execute();
					$child_id = $dbo->insertId();

					// get new type for parent notification
					$q = "SELECT * FROM `#__vikchannelmanager_notifications` WHERE `id`=".(int)$keys[0]['id_notification'].";";
					$dbo->setQuery($q);
					$notification = $dbo->loadAssoc();
					if ($notification) {
						$set_type = (int)$notification['type'];
						$all_types = array(intval($ecode));

						$q = "SELECT * FROM `#__vikchannelmanager_notification_child` WHERE `id_parent`=".(int)$keys[0]['id_notification']." AND `id`!=".(int)$child_id.";";
						$dbo->setQuery($q);
						$child_types = $dbo->loadAssocList();

						foreach ($child_types as $ctype) {
							$all_types[] = intval($ctype['type']);
						}

						foreach ($all_types as $newtype) {
							if ($newtype == 0) {
								$set_type = 0;
								break;
							}
							if ($newtype == 2) {
								$set_type = 2;
							}
						}

						// set parent Notification to be read and update time and type
						$q = "UPDATE `#__vikchannelmanager_notifications` SET `ts`=".time().", `type`=".$set_type.", `read`=0 WHERE `id`=".(int)$keys[0]['id_notification'].";";
						$dbo->setQuery($q);
						$dbo->execute();
					}
				} else {
					$q = "INSERT INTO `#__vikchannelmanager_notifications` (`ts`,`type`,`from`,`cont`) VALUES('".time()."', ".(int)$ecode.", 'e4jConnect', ".$dbo->quote($pemessage).");";
					$dbo->setQuery($q);
					$dbo->execute();
				}

				$q = "DELETE FROM `#__vikchannelmanager_keys` WHERE `id`<".$keys[0]['id']." AND `idordervb`=0;";
				$dbo->setQuery($q);
				$dbo->execute();

				$response = 'e4j.ok';
			} else {
				$response .= '.InvalidOrderKey';
			}
		} else {
			$response .= '.MissingNkey';
		}
		
		echo $response;
		exit;
	}

	/**
	 * BR_L Booking Retrieval Listener
	 * Retrieves the new bookings sent by e4jConnect
	 */
	public function br_l()
	{
		// require necessary dependencies
		require_once VCM_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "newbookings.vikbooking.php";

		$response = 'e4j.error';

		$pe4jauth = VikRequest::getString('e4jauth', '', 'request', VIKREQUEST_ALLOWRAW);
		$pchannel = VikRequest::getInt('channel', '', 'request');
		$pnewbookings = VikRequest::getString('newbookings', '', 'request');
		$parrbookings = VikRequest::getString('arrbookings', '', 'request', VIKREQUEST_ALLOWRAW);

		if (empty($pe4jauth) || empty($pnewbookings) || empty($parrbookings)) {
			VCMHttpDocument::getInstance()->close(500, $response);
		}

		$channel = VikChannelManager::getChannel($pchannel);
		if (!$channel) {
			VCMHttpDocument::getInstance()->close(404, 'e4j.error.NoChannel');
		}

		// build configuration settings array
		$vcm_config = VCMFactory::getConfig();
		$config = [
			'apikey' 		  => $vcm_config->get('apikey', ''),
			'vikbookingsynch' => $vcm_config->get('vikbookingsynch', 1),
			'emailadmin' 	  => $vcm_config->get('emailadmin', ''),
		];

		// merge channel information to configuration settings array
		$config['channel'] = array_merge($channel, (array)json_decode($channel['params'], true));

		$checkauth = md5($config['apikey']);
		if ($checkauth != $pe4jauth) {
			VCMHttpDocument::getInstance()->close(403, 'e4j.error.Authentication');
		}

		$response = 'e4j.error.1';
		$arrbookings = null;

		if (preg_match("/^a:\d+:/i", $parrbookings)) {
			// serialized string
			$arrbookings = @unserialize($parrbookings);
			if ($arrbookings === false) {
				/**
				 * VCM 1.6.9 - Regex for fixing urlencoding issues when unserializing strings.
				 */
				$safe_decoded = preg_replace_callback("/\"credit_card\";s:\d+:\"(.*?)\";/", function($match) {
					$encoded = urlencode($match[1]);
					return str_replace($match[1], $encoded, $match[0]);
				}, $parrbookings);
				$arrbookings = @unserialize($safe_decoded);
			}
		} else {
			// attempt to JSON decode string
			$arrbookings = json_decode($parrbookings, true);
		}

		if (is_array($arrbookings) && !empty($arrbookings['orders']) && count($arrbookings['orders']) == (int)$pnewbookings && $checkauth == $arrbookings['e4jauth']) {
			// process bookings
			$e4j = new NewBookingsVikBooking($config, $arrbookings);
			$response = $e4j->processNewBookings();
		}

		VCMHttpDocument::getInstance()->close(200, $response);
	}

	/**
	 * iCal cancellation listener endpoint to process empty calendars.
	 * 
	 * @since 	1.8.9
	 */
	public function ical_canc_l()
	{
		// require necessary dependencies
		require_once VCM_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "newbookings.vikbooking.php";

		$pe4jauth = VikRequest::getString('e4jauth', '', 'request', VIKREQUEST_ALLOWRAW);
		$pchannel = VikRequest::getInt('channel', 0, 'request');
		$cals_data = VikRequest::getVar('cals_data', [], 'request', 'array', VIKREQUEST_ALLOWRAW);

		if (empty($pe4jauth) || empty($pchannel)) {
			VCMHttpDocument::getInstance()->close(500, 'Missing required data');
		}

		$channel = VikChannelManager::getChannel($pchannel);
		if (!$channel) {
			VCMHttpDocument::getInstance()->close(404, 'Channel not found');
		}

		// build configuration settings array
		$vcm_config = VCMFactory::getConfig();
		$config = [
			'apikey' 		  => $vcm_config->get('apikey', ''),
			'vikbookingsynch' => $vcm_config->get('vikbookingsynch', 1),
			'emailadmin' 	  => $vcm_config->get('emailadmin', ''),
		];

		// merge channel information to configuration settings array
		$config['channel'] = array_merge($channel, (array)json_decode($channel['params'], true));

		$checkauth = md5($config['apikey']);
		if ($checkauth != $pe4jauth) {
			VCMHttpDocument::getInstance()->close(403, 'You are not authorised to perform this request');
		}

		// validate calendars data
		if (empty($cals_data)) {
			VCMHttpDocument::getInstance()->close(500, 'Missing calendars data');
		}

		foreach ($cals_data as $k => $cal_data) {
			if (!isset($cal_data['info']) || empty($cal_data['info']['ical_sign'])) {
				// calendar identifier is mandatory
				unset($cals_data[$k]);
			}
		}

		if (!$cals_data) {
			VCMHttpDocument::getInstance()->close(500, 'Invalid calendars data');
		}

		// invoke class for processing new bookings
		$e4j = new NewBookingsVikBooking($config, ['orders' => $cals_data]);
		$cancelled_bookings = $e4j->iCalCheckNewCancellations();

		// send response to output
		VCMHttpDocument::getInstance()->json(['cancelled_bookings' => $cancelled_bookings]);
	}

	/**
	 * BC_RSL Booking Confirmation Response Listener
	 * Retrieves the response from e4jConnect of a BC_RQ
	 * that was previously sent to save the Notification
	 */
	public function bc_rsl()
	{
		$dbo = JFactory::getDbo();

		$response = 'e4j.error';

		$porderid = VikRequest::getVar('orderid', array());
		$pnkey = VikRequest::getVar('nkey', array());
		$pecode = VikRequest::getVar('ecode', array());
		$pemessage = VikRequest::getVar('emessage', array(), 'request', 'array', VIKREQUEST_ALLOWRAW);
		$pchannel = VikRequest::getVar('channel', array());
		if (!empty($porderid) && !empty($pnkey)) {
			$valsecode = array('0', '1', '2');
			foreach($porderid as $k => $orderid) {
				if (!empty($orderid) && !empty($pnkey[$k])) {
					$ecode = '0';
					$ecode = isset($pecode[$k]) && in_array($pecode[$k], $valsecode) ? $pecode[$k] : $ecode;
					$q = sprintf("SELECT `k`.*,`vbo`.`id` AS `fetchvboid` FROM `#__vikchannelmanager_keys` AS `k` LEFT JOIN `#__vikbooking_orders` `vbo` ON `k`.`idordervb`=`vbo`.`id` WHERE `idordervb`='%d' AND `key`='%d';", (int)$orderid, (int)$pnkey[$k]);
					$dbo->setQuery($q);
					$dbo->execute();
					if ($dbo->getNumRows() > 0) {
						$keys = $dbo->loadAssocList();
						//Check if notification should be saved as new or as a child
						$set_channel = 0;
						$channel_info = VikChannelManager::getChannel($pchannel[$k]);
						if (count($channel_info) > 0) {
							$set_channel = (int)$channel_info['uniquekey'];
						}
						$q = "SELECT * FROM `#__vikchannelmanager_notifications` WHERE `from`=".$dbo->quote($channel_info['name'])." AND `idordervb`=".(int)$keys[0]['idordervb']." ORDER BY `#__vikchannelmanager_notifications`.`id` DESC LIMIT 1;";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() == 1) {
							$notification = $dbo->loadAssoc();
							$id_parent = $notification['id'];
							$q = "INSERT INTO `#__vikchannelmanager_notification_child` (`id_parent`,`type`,`cont`,`channel`) VALUES(".(int)$id_parent.", ".(int)$ecode.", ".$dbo->quote($pemessage[$k]).", ".$set_channel.");";
							$dbo->setQuery($q);
							$dbo->execute();
							$child_id = $dbo->insertId();
							//get new type for parent notification
							$set_type = (int)$notification['type'];
							$all_types = array(intval($ecode));
							$q = "SELECT * FROM `#__vikchannelmanager_notification_child` WHERE `id_parent`=".(int)$id_parent." AND `id`!=".(int)$child_id.";";
							$dbo->setQuery($q);
							$dbo->execute();
							if ($dbo->getNumRows() > 0) {
								$child_types = $dbo->loadAssocList();
								foreach ($child_types as $ctype) {
									$all_types[] = intval($ctype['type']);
								}
							}
							foreach ($all_types as $newtype) {
								if ($newtype == 0) {
									$set_type = 0;
									break;
								}
								if ($newtype == 2) {
									$set_type = 2;
								}
							}
							//
							//Set parent Notification to be read and update time and type
							$q = "UPDATE `#__vikchannelmanager_notifications` SET `ts`=".time().", `type`=".$set_type.", `read`=0 WHERE `id`=".(int)$id_parent.";";
							$dbo->setQuery($q);
							$dbo->execute();
							//
						} else {
							$q = "INSERT INTO `#__vikchannelmanager_notifications` (`ts`,`type`,`from`,`cont`,`idordervb`,`read`) VALUES('".time()."', ".(int)$ecode.", 'e4jConnect', ".$dbo->quote($pemessage[$k]).", ".(strlen($keys[0]['fetchvboid']) > 0 ? "'".$keys[0]['idordervb']."'" : "null").", 0);";
							$dbo->setQuery($q);
							$dbo->execute();
						}
						$q = "DELETE FROM `#__vikchannelmanager_keys` WHERE `id`='".$keys[0]['id']."';";
						$dbo->setQuery($q);
						$dbo->execute();
						$response = 'e4j.ok';
					}
				}
			}
		}
		echo $response;
		exit;
	}

	/**
	 * TripAdvisor (Instant Booking) Booking Sync listener
	 */
	public function tac_bsync_l()
	{
		$crono = new Crono();
		$crono->start();
		
		$response = 'e4j.error';
		$args = array();
		$args['hash'] = VikRequest::getString('e4jauth', '', 'request');
		$args['reservation_id'] = VikRequest::getString('reservation_id', '', 'request');
		
		$valid = true;
		foreach ($args as $k => $v) {
			$valid = $valid && !empty($v);
		}
		
		//request type
		$req_type = VikRequest::getString('req_type', '', 'request');
		
		if ($valid) {
			$channel = VikChannelManager::getChannelCredentials(2);
			$checkauth = md5($channel['tripadvisorid'].'e4j'.$channel['tripadvisorid']);
			
			if ($checkauth == $args['hash']) {
				require_once (VBO_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "lib.vikbooking.php");
				$dbo = JFactory::getDbo();
				$q="SELECT * FROM `#__vikbooking_orders` WHERE `id`='".intval($args['reservation_id'])."';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 0) {
					echo json_encode(array('e4j.error' => 'UnknownReference', 'error.code' => 1));
					exit;
				}
				$order = $dbo->loadAssocList();
				
				$nowstatus = $order[0]['status'];
				$nowts = time();
				$res_status = 'Booked';
				
				if ($nowstatus == 'cancelled') {
					$res_status = 'Cancelled';
				} else {
					if ($nowts >= $order[0]['checkin'] && $nowts < $order[0]['checkout']) {
						$res_status = 'CheckedIn';
					} elseif ($nowts > $order[0]['checkout']) {
						$res_status = 'CheckedOut';
					}
				}
								
				$esit = true;
				$cancellation_number = !empty($order[0]['confirmnumber']) ? $order[0]['confirmnumber'] : $order[0]['id'].'canc';
				
				echo json_encode(array(
					'response' => array(
						'esit' => $esit,
						'status' => $res_status,
						'cancellation_number' => $cancellation_number,
						'currency' => VikBooking::getCurrencyName(),
						'order' => $order[0]
					)
				));
				exit;
				
			} else {
				$response = 'e4j.error.auth';
			}
		}
		echo $response;
		exit;
	}
	
	/**
	 * TripAdvisor or Trivago (Instant/Express Booking) Booking Cancel listener
	 */
	public function tac_bcanc_l()
	{
		$crono = new Crono();
		$crono->start();
		
		$response = 'e4j.error';
		$args = array();
		$args['hash'] = VikRequest::getString('e4jauth', '', 'request');
		$args['channel'] = VikRequest::getString('channel', 'tac', 'request');
		$args['reservation_id'] = VikRequest::getString('reservation_id', '', 'request');
		
		$valid = true;
		foreach ($args as $k => $v) {
			$valid = $valid && !empty($v);
		}
		
		//request type
		$req_type = VikRequest::getString('req_type', '', 'request');
		
		if ($valid) {
			$channel = VikChannelManager::getChannelCredentials(($args['channel'] == 'trivago' ? VikChannelManagerConfig::TRIVAGO : VikChannelManagerConfig::TRIP_CONNECT));
			if ($args['channel'] == 'trivago') {
				$checkauth = md5($channel['trivagoid'].'e4j'.$channel['trivagoid']);
			} else {
				$checkauth = md5($channel['tripadvisorid'].'e4j'.$channel['tripadvisorid']);
			}
			
			if ($checkauth == $args['hash']) {
				$dbo = JFactory::getDbo();
				$q="SELECT * FROM `#__vikbooking_orders` WHERE `id`='".intval($args['reservation_id'])."';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 0) {
					echo json_encode(array('e4j.error' => 'UnknownReference', 'error.code' => 1));
					exit;
				}
				$order = $dbo->loadAssocList();
				
				$nowstatus = $order[0]['status'];
				$nowts = time();
				$gocancel = true;
				$res_status = 'Error';
				
				//check if the reservation can be cancelled
				if ($nowstatus != 'cancelled' && $nowts >= $order[0]['checkin']) {
					$res_status = 'CannotBeCancelled';
					$gocancel = false;
				}
				
				if ($gocancel && ($nowstatus == 'confirmed' || $nowstatus == 'standby')) {
					$q="UPDATE `#__vikbooking_orders` SET `status`='cancelled' WHERE `id`='".$order[0]['id']."';";
					$dbo->setQuery($q);
					$dbo->execute();
					$q = "DELETE FROM `#__vikbooking_tmplock` WHERE `idorder`=" . intval($order[0]['id']) . ";";
					$dbo->setQuery($q);
					$dbo->execute();
					$q="SELECT * FROM `#__vikbooking_ordersbusy` WHERE `idorder`='".$order[0]['id']."';";
					$dbo->setQuery($q);
					$dbo->execute();
					if ($dbo->getNumRows() > 0) {
						$ordbusy = $dbo->loadAssocList();
						foreach ($ordbusy as $ob) {
							$q="DELETE FROM `#__vikbooking_busy` WHERE `id`='".$ob['idbusy']."';";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
					$q="DELETE FROM `#__vikbooking_ordersbusy` WHERE `idorder`='".$order[0]['id']."';";
					$dbo->setQuery($q);
					$dbo->execute();
					$res_status = 'Success';
					if ($nowstatus == 'confirmed') {
						//trigger VCM A_RQ for other channels
						if (!class_exists('SynchVikBooking')) {
							require_once(VCM_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "synch.vikbooking.php");
						}
						// VCM 1.6.8 - The ReservationsLogger needs to know who triggered the update request
						$exclude_chid = $args['channel'] == 'trivago' ? VikChannelManagerConfig::TRIVAGO : VikChannelManagerConfig::TRIP_CONNECT;
						//
						$vcm = new SynchVikBooking($order[0]['id'], array($exclude_chid));
						$vcm->setSkipCheckAutoSync();
						$vcm->setFromCancellation(array('id' => $order[0]['id']));
						$vcm->sendRequest();
						//
					}
				} elseif ($nowstatus == 'cancelled') {
					$res_status = 'AlreadyCancelled';
				}
				
				$esit = true;
				$cancellation_number = !empty($order[0]['confirmnumber']) ? $order[0]['confirmnumber'] : $order[0]['id'].'canc';
				
				echo json_encode(array(
					'response' => array(
						'esit' => $esit,
						'status' => $res_status,
						'cancellation_number' => $cancellation_number
					)
				));
				exit;
				
			} else {
				$response = 'e4j.error.auth';
			}
		}
		echo $response;
		exit;
	}
	
	/**
	 * TripAdvisor or Trivago (Instant/Express Booking) Booking Verify listener
	 */
	public function tac_bv_l()
	{
		$crono = new Crono();
		$crono->start();
		
		$response = 'e4j.error';
		$args = array();
		$args['hash'] = VikRequest::getString('e4jauth', '', 'request');
		$args['channel'] = VikRequest::getString('channel', 'tac', 'request');
		$args['reference_id'] = VikRequest::getString('reference_id', '', 'request');
		$args['reservation_id'] = VikRequest::getString('reservation_id', '', 'request');
		
		$valid = true;
		foreach ($args as $k => $v) {
			$valid = $valid && !empty($v);
		}
		
		//request type
		$req_type = VikRequest::getString('req_type', '', 'request');
				
		if ($valid) {
			$channel = VikChannelManager::getChannelCredentials(($args['channel'] == 'trivago' ? VikChannelManagerConfig::TRIVAGO : VikChannelManagerConfig::TRIP_CONNECT));
			if ($args['channel'] == 'trivago') {
				$checkauth = md5($channel['trivagoid'].'e4j'.$channel['trivagoid']);
			} else {
				$checkauth = md5($channel['tripadvisorid'].'e4j'.$channel['tripadvisorid']);
			}
			
			if ($checkauth == $args['hash']) {
				
				$dbo = JFactory::getDbo();
				$q="SELECT * FROM `#__vikbooking_orders` WHERE `id`='".intval($args['reservation_id'])."';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 0) {
					echo json_encode(array('e4j.error' => 'UnknownUserProblem', 'error.code' => 1, 'explanation' => 'Unknown Reservation_ID', 'response' => 'UnknownReference'));
					exit;
				}
				$order = $dbo->loadAssocList();
				
				$args['nights'] = $order[0]['days'];
				
				$q="SELECT `or`.`idroom`,`or`.`adults`,`or`.`children`,`or`.`idtar`,`or`.`optionals`,`or`.`childrenage`,`or`.`t_first_name`,`or`.`t_last_name`,`r`.`id` AS `langidroom`,`r`.`name`,`r`.`img`,`r`.`idcarat`,`r`.`fromadult`,`r`.`toadult` FROM `#__vikbooking_ordersrooms` AS `or`,`#__vikbooking_rooms` AS `r` WHERE `or`.`idorder`='".$order[0]['id']."' AND `or`.`idroom`=`r`.`id` ORDER BY `or`.`id` ASC;";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 0) {
					echo json_encode(array('e4j.error' => 'UnknownUserProblem', 'error.code' => 2, 'explanation' => 'Missing order data with this Reservation_ID', 'response' => 'UnknownReference'));
					exit;
				}
				$orderrooms = $dbo->loadAssocList();
				
				$partner_rates = array();
				$avail_rooms = array();
				foreach($orderrooms as $or) {
					$partner_rates[$or['idtar']] = $or['idtar'];
					$avail_rooms[] = $or['idroom'];
				}
				
				require_once (VBO_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "lib.vikbooking.php");
				
				$args['start_ts'] = $order[0]['checkin'];
				$args['end_ts'] = $order[0]['checkout'];
				
				// GET RATES
				$rates = array();
				$q = "SELECT `p`.*, `r`.`img`, `r`.`units`, `r`.`moreimgs`, `r`.`imgcaptions`, `prices`.`name` AS `pricename`, `prices`.`breakfast_included`, `prices`.`free_cancellation`, `prices`.`canc_deadline` FROM `#__vikbooking_dispcost` AS `p`, `#__vikbooking_rooms` AS `r`, `#__vikbooking_prices` AS `prices` WHERE `r`.`id`=`p`.`idroom` AND `p`.`idprice`=`prices`.`id` AND `p`.`days`=".$order[0]['days']." AND `p`.`id` IN (".implode(',', array_keys($partner_rates)).") AND `r`.`id` IN (".implode(',', $avail_rooms).") ORDER BY `p`.`cost` ASC;";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 0) {
					echo json_encode(array('e4j.error' => 'PriceMismatch', 'error.code' => 1));
					exit;
				}
				
				$rates = $dbo->loadAssocList();
				$arr_rates = array();
				foreach ($rates as $rate) {
					$arr_rates[$rate['idroom']][] = $rate;
				}
				
				$arrpeople = array();
				foreach($orderrooms as $kor => $or) {
					$numr = ($kor + 1);
					$arrpeople[$numr]['adults'] = $or['adults'];
					$arrpeople[$numr]['children'] = $or['children'];
					$children_age = array();
					if (!empty($or['childrenage'])) {
						$json_dec = json_decode($or['childrenage'], true);
						if (is_array($json_dec['age']) && count($json_dec['age']) > 0) {
							$children_age = $json_dec['age'];
						}
					}
					$arrpeople[$numr]['children_age'] = $children_age;
				}
				
				//apply special prices
				$arr_rates = VikBooking::applySeasonalPrices($arr_rates, $args['start_ts'], $args['end_ts']);
				$multi_rates = 1;
				foreach ($arr_rates as $idr => $tars) {
					$multi_rates = count($tars) > $multi_rates ? count($tars) : $multi_rates;
				}
				if ($multi_rates > 1) {
					for($r = 1; $r < $multi_rates; $r++) {
						$deeper_rates = array();
						foreach ($arr_rates as $idr => $tars) {
							foreach ($tars as $tk => $tar) {
								if ($tk == $r) {
									$deeper_rates[$idr][0] = $tar;
									break;
								}
							}
						}
						if (!count($deeper_rates) > 0) {
							continue;
						}
						$deeper_rates = VikBooking::applySeasonalPrices($deeper_rates, $args['start_ts'], $args['end_ts']);
						foreach ($deeper_rates as $idr => $dtars) {
							foreach ($dtars as $dtk => $dtar) {
								$arr_rates[$idr][$r] = $dtar;
							}
						}
					}
				}
				//
				
				//children ages charge
				$children_sums = array();
				//end children ages charge
				
				//set $args['num_adults'] to the number of adults occupying the first room
				$args['num_adults'] = $arrpeople[key($arrpeople)]['adults'];
				//
				
				//sum charges/discounts per occupancy for each room party
				foreach($arrpeople as $roomnumb => $party) {
					//charges/discounts per adults occupancy
					foreach ($arr_rates as $r => $rates) {
						$children_charges = VikBooking::getChildrenCharges($r, $party['children'], $party['children_age'], $args['nights']);
						if (count($children_charges) > 0) {
							$children_sums[$r] += $children_charges['total'];
						}
						$diffusageprice = VikBooking::loadAdultsDiff($r, $party['adults']);
						//Occupancy Override - Special Price may be setting a charge/discount for this occupancy while default price had no occupancy pricing
						if (!is_array($diffusageprice)) {
							foreach($rates as $kpr => $vpr) {
								if (array_key_exists('occupancy_ovr', $vpr) && array_key_exists($party['adults'], $vpr['occupancy_ovr']) && strlen($vpr['occupancy_ovr'][$party['adults']]['value'])) {
									$diffusageprice = $vpr['occupancy_ovr'][$party['adults']];
									break;
								}
							}
							reset($rates);
						}
						//
						if (is_array($diffusageprice)) {
							foreach($rates as $kpr => $vpr) {
								if ($roomnumb == 1) {
									$arr_rates[$r][$kpr]['costbeforeoccupancy'] = $arr_rates[$r][$kpr]['cost'];
								}
								//Occupancy Override
								if (array_key_exists('occupancy_ovr', $vpr) && array_key_exists($party['adults'], $vpr['occupancy_ovr']) && strlen($vpr['occupancy_ovr'][$party['adults']]['value'])) {
									$diffusageprice = $vpr['occupancy_ovr'][$party['adults']];
								}
								//
								$arr_rates[$r][$kpr]['diffusage'] = $party['adults'];
								if ($diffusageprice['chdisc'] == 1) {
									//charge
									if ($diffusageprice['valpcent'] == 1) {
										//fixed value
										$aduseval = $diffusageprice['pernight'] == 1 ? $diffusageprice['value'] * $arr_rates[$r][$kpr]['days'] : $diffusageprice['value'];
										$arr_rates[$r][$kpr]['diffusagecost'][$roomnumb] = $aduseval;
										$arr_rates[$r][$kpr]['cost'] += $aduseval;
									} else {
										//percentage value
										$aduseval = $diffusageprice['pernight'] == 1 ? round(($arr_rates[$r][$kpr]['costbeforeoccupancy'] * $diffusageprice['value'] / 100) * $arr_rates[$r][$kpr]['days'], 2) : round(($arr_rates[$r][$kpr]['costbeforeoccupancy'] * $diffusageprice['value'] / 100), 2);
										$arr_rates[$r][$kpr]['diffusagecost'][$roomnumb] = $aduseval;
										$arr_rates[$r][$kpr]['cost'] += $aduseval;
									}
								} else {
									//discount
									if ($diffusageprice['valpcent'] == 1) {
										//fixed value
										$aduseval = $diffusageprice['pernight'] == 1 ? $diffusageprice['value'] * $arr_rates[$r][$kpr]['days'] : $diffusageprice['value'];
										$arr_rates[$r][$kpr]['diffusagediscount'][$roomnumb] = $aduseval;
										$arr_rates[$r][$kpr]['cost'] -= $aduseval;
									} else {
										//percentage value
										$aduseval = $diffusageprice['pernight'] == 1 ? round(((($arr_rates[$r][$kpr]['costbeforeoccupancy'] / $arr_rates[$r][$kpr]['days']) * $diffusageprice['value'] / 100) * $arr_rates[$r][$kpr]['days']), 2) : round(($arr_rates[$r][$kpr]['costbeforeoccupancy'] * $diffusageprice['value'] / 100), 2);
										$arr_rates[$r][$kpr]['diffusagediscount'][$roomnumb] = $aduseval;
										$arr_rates[$r][$kpr]['cost'] -= $aduseval;
									}
								}
							}
						} elseif ($roomnumb == 1) {
							foreach($rates as $kpr => $vpr) {
								$arr_rates[$r][$kpr]['costbeforeoccupancy'] = $arr_rates[$r][$kpr]['cost'];
							}
						}
					}
					//end charges/discounts per adults occupancy
				}
				//end sum charges/discounts per occupancy for each room party
				
				//if the rooms are given to a party of multiple rooms, multiply the basic rates per room per number of rooms
				for($i = 2; $i <= count($arrpeople); $i++) {
					foreach ($arr_rates as $r => $rates) {
						foreach($rates as $kpr => $vpr) {
							$arr_rates[$r][$kpr]['cost'] += $arr_rates[$r][$kpr]['costbeforeoccupancy'];
						}
					}
				}
				//end if the rooms are given to a party of multiple rooms, multiply the basic rates per room per number of rooms
				
				//children ages charge
				if (count($children_sums) > 0) {
					foreach ($arr_rates as $r => $rates) {
						if (array_key_exists($r, $children_sums)) {
							foreach($rates as $kpr => $vpr) {
								$arr_rates[$r][$kpr]['cost'] += $children_sums[$r];
							}
						}
					}
				}
				//end children ages charge
				
				//compose taxes information
				$ivainclusa = (int)VikBooking::ivaInclusa();
				$rates_ids = array();
				foreach ($arr_rates as $r => $rate) {
					foreach ($rate as $ids) {
						if (!in_array($ids['idprice'], $rates_ids)) {
							$rates_ids[] = $ids['idprice'];
						}
					}
				}
				$tax_rates = array();
				$q = "SELECT `p`.`id`,`t`.`aliq` FROM `#__vikbooking_prices` AS `p` LEFT JOIN `#__vikbooking_iva` `t` ON `p`.`idiva`=`t`.`id` WHERE `p`.`id` IN (".implode(',', $rates_ids).");";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$alltaxrates = $dbo->loadAssocList();
					foreach ($alltaxrates as $tx) {
						if (!empty($tx['aliq']) && $tx['aliq'] > 0) {
							$tax_rates[$tx['id']] = $tx['aliq'];
						}
					}
				}
				$city_tax_fees = array();
				if (count($tax_rates) > 0) {
					foreach ($arr_rates as $r => $rates) {
						//$city_tax_fees = VikBooking::getMandatoryTaxesFees(array($r), $args['num_adults'], $args['nights']);
						foreach ($rates as $k => $rate) {
							if (array_key_exists($rate['idprice'], $tax_rates)) {
								if (intval($ivainclusa) == 1) {
									//prices tax included
									$realcost = $rate['cost'] / ((100 + $tax_rates[$rate['idprice']]) / 100);
									$tax_oper = ($tax_rates[$rate['idprice']] + 100) / 100;
									$taxes = $rate['cost'] - ($rate['cost'] / $tax_oper);
								} else {
									//prices tax excluded
									$realcost = $rate['cost'] * (100 + $tax_rates[$rate['idprice']]) / 100;
									$taxes = $realcost - $rate['cost'];
									$realcost = $rate['cost'];
								}
								$arr_rates[$r][$k]['cost'] = round($realcost, 2);
								$arr_rates[$r][$k]['taxes'] = round($taxes, 2);
								//$arr_rates[$r][$k]['city_taxes'] = round($city_tax_fees['city_taxes'], 2);
								//$arr_rates[$r][$k]['fees'] = round($city_tax_fees['fees'], 2);
							}
						}
					}
					//sum taxes/fees for each room party
					foreach($arrpeople as $roomnumb => $party) {
						foreach ($arr_rates as $r => $rates) {
							$city_tax_fees = VikBooking::getMandatoryTaxesFees(array($r), $party['adults'], $args['nights']);
							foreach ($rates as $k => $rate) {
								if (!isset($arr_rates[$r][$k]['city_taxes'])) {
									$arr_rates[$r][$k]['city_taxes'] = 0;
								}
								if (!isset($arr_rates[$r][$k]['fees'])) {
									$arr_rates[$r][$k]['fees'] = 0;
								}
								$arr_rates[$r][$k]['city_taxes'] += round($city_tax_fees['city_taxes'], 2);
								$arr_rates[$r][$k]['fees'] += round($city_tax_fees['fees'], 2);
							}
						}
					}
					//end sum taxes/fees for each room party
				} else {
					/**
					 * Those without tax rates must still return information about city taxes and fees.
					 * Trivago requested this modification.
					 * 
					 * @since 	1.7.2
					 */
					foreach ($arr_rates as $r => $rates) {
						$city_tax_fees = VikBooking::getMandatoryTaxesFees(array($r), $party['adults'], $args['nights']);
						foreach ($rates as $k => $rate) {
							if (!isset($arr_rates[$r][$k]['taxes'])) {
								$arr_rates[$r][$k]['taxes'] = 0;
							}
							if (!isset($arr_rates[$r][$k]['city_taxes'])) {
								$arr_rates[$r][$k]['city_taxes'] = 0;
							}
							if (!isset($arr_rates[$r][$k]['fees'])) {
								$arr_rates[$r][$k]['fees'] = 0;
							}
							$arr_rates[$r][$k]['cost'] = round($rate['cost'], 2);
							$arr_rates[$r][$k]['taxes'] = round(0, 2);
							$arr_rates[$r][$k]['city_taxes'] += round($city_tax_fees['city_taxes'], 2);
							$arr_rates[$r][$k]['fees'] += round($city_tax_fees['fees'], 2);
						}
					}
				}
				//end compose taxes information
								
				//customer_data
				$custdata = $order[0]['custdata'];
				$customer_info = array();
				$cust_parts = explode("\n", $custdata);
				foreach($cust_parts as $custval) {
					if (empty($custval)) {
						continue;
					}
					$keyval = explode(':', trim($custval));
					$readablekv = strtolower(str_replace(' ', '_', trim($keyval[0])));
					$customer_info[$readablekv] = trim($keyval[1]);
				}
				//
				
				$esit = true;
				$nowts = time();
				$confirmnumber = $order[0]['confirmnumber'];
				$orderlink = JUri::root()."index.php?option=com_vikbooking&task=vieworder&sid=".$order[0]['sid']."&ts=".$order[0]['ts'];
				$neworder_status = $order[0]['status'];
				$reservation_status = 'Booked';
				if ($order[0]['status'] == 'cancelled') {
					$reservation_status = 'Cancelled';
				} else {
					if ($nowts > $order[0]['checkout']) {
						$reservation_status = 'CheckedOut';
					} elseif ($nowts >= $order[0]['checkin'] && $nowts < $order[0]['checkout']) {
						$reservation_status = 'CheckedIn';
					}
				}
				
				$arr_rates['response'] = array(
					'esit' => $esit,
					'status' => $neworder_status,
					'reservationstatus' => $reservation_status,
					'id' => $order[0]['id'],
					'confirmnumber' => $confirmnumber,
					'orderlink' => $orderlink,
					'currency' => VikBooking::getCurrencyName(),
					'order' => $order[0],
					'order_rooms' => $orderrooms,
					'customer_info' => $customer_info
				);
				
				$response = $arr_rates;
								
				// store elapsed time statistics
				$elapsed_time = $crono->stop();
				VikChannelManager::storeCallStats(($args['channel'] == 'trivago' ? VikChannelManagerConfig::TRIVAGO : VikChannelManagerConfig::TRIP_CONNECT), 'tac_bv_l', $elapsed_time);
				//
				
				$args['response'] = $response['response'];
				
				echo json_encode($response);
				exit;
				
			} else {
				$response = 'e4j.error.auth';
			}
		}
		echo $response;
		exit;
	}
	
	/**
	 * TripAdvisor or Trivago (Instant/Express Booking) Booking Submit listener
	 */
	public function tac_bs_l()
	{
		$crono = new Crono();
		$crono->start();
		
		$response = 'e4j.error';
		$args = array();
		$args['hash'] = VikRequest::getString('e4jauth', '', 'request');
		$args['channel'] = VikRequest::getString('channel', 'tac', 'request');
		$args['start_date'] = VikRequest::getString('start_date', '', 'request');
		$args['end_date'] = VikRequest::getString('end_date', '', 'request');
		$args['nights'] = VikRequest::getInt('nights', 1, 'request');
		$args['num_rooms'] = VikRequest::getInt('num_rooms', 1, 'request');
		$args['start_ts'] = strtotime($args['start_date']);
		$args['end_ts'] = strtotime($args['end_date']);
		$args['adults'] = VikRequest::getVar('adults', array());
		$args['customer_info'] = VikRequest::getVar('customer_info', array());
		$args['rooms_info'] = VikRequest::getVar('rooms_info', array());
		$args['partner_data'] = VikRequest::getVar('partner_data', array());
		$args['final_price_at_booking'] = VikRequest::getVar('final_price_at_booking', array());
		$args['payment_method'] = VikRequest::getString('payment_method', '', 'request', VIKREQUEST_ALLOWRAW);
		$teb_tracking = VikRequest::getString('tracking', '', 'request', VIKREQUEST_ALLOWRAW);
		$args['phone'] = VikRequest::getString('phone', '', 'request');
		
		$valid = true;
		foreach ($args as $k => $v) {
			$valid = $valid && !empty($v);
		}
		
		$partner_id = $args['channel'] == 'trivago' ? VikChannelManager::getTrivagoPartnerID() : VikChannelManager::getTripConnectPartnerID();

		$enc = VikChannelManager::loadCypherFramework($partner_id);
		
		$decoded_paym = $enc->decrypt($args['payment_method']);
		$args['payment_method'] = unserialize($decoded_paym);
		if ($args['payment_method'] === false || !is_array($args['payment_method'])) {
			$valid = false;
			$response = 'e4j.error.CreditCardTypeNotSupported';
		}
		
		//request type
		$req_type = VikRequest::getString('req_type', '', 'request');
		
		$args['children'] = VikRequest::getVar('children', array());
		$args['children_age'] = VikRequest::getVar('children_age', array());
		$args['final_price_at_checkout'] = VikRequest::getVar('final_price_at_checkout', array());
		$args['reference_id'] = VikRequest::getString('reference_id', '', 'request');
		
		if ($valid) {
			$channel = VikChannelManager::getChannelCredentials(($args['channel'] == 'trivago' ? VikChannelManagerConfig::TRIVAGO : VikChannelManagerConfig::TRIP_CONNECT));
			if ($args['channel'] == 'trivago') {
				$checkauth = md5($channel['trivagoid'].'e4j'.$channel['trivagoid']);
			} else {
				$checkauth = md5($channel['tripadvisorid'].'e4j'.$channel['tripadvisorid']);
			}
			
			if ($checkauth == $args['hash']) {
				$debug_mode = isset($_REQUEST['e4j_debug']) && intval($_REQUEST['e4j_debug']) == 1 ? true : false;

				$partner_rooms = array();
				$partner_rates = array();
				if (!is_int(key($args['partner_data']))){
					$partner_rooms[$args['partner_data']['id_room']] = $args['partner_data']['id_room'];
					$partner_rates[$args['partner_data']['id_cost']] = $args['partner_data']['id_cost'];
				} else {
					foreach ($args['partner_data'] as $vbroom) {
						$partner_rooms[$vbroom['id_room']] = $vbroom['id_room'];
						$partner_rates[$vbroom['id_cost']] = $vbroom['id_cost'];
					}
				}
				
				$tac_rooms = array();
				$dbo = JFactory::getDbo();

				//VCM 1.6.6 - table name must be taken depending on the service
				$tbrooms = '`#__vikchannelmanager_tac_rooms`';
				if ($args['channel'] == 'trivago') {
					$tbrooms = '`#__vikchannelmanager_tri_rooms`';
				}
				//
				$q = "SELECT `id_vb_room` AS `id` FROM ".$tbrooms." WHERE `id_vb_room` IN (".implode(',', array_keys($partner_rooms)).");";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 0) {
					echo json_encode(array());
					exit;
				}
				
				$rows = $dbo->loadAssocList();
				for ($i = 0; $i < count($rows); $i++) {
					$tac_rooms[$i] = $rows[$i]['id'];
				}
				
				$avail_rooms = array();
				
				//compose adults-children array and sql clause
				$arradultsrooms = array();
				$arradultsclause = array();
				$arrpeople = array();
				if (count($args['adults']) > 0) {
					foreach($args['adults'] as $kad => $adu) {
						$roomnumb = $kad + 1;
						if (strlen($adu)) {
							$numadults = intval($adu);
							if ($numadults >= 0) {
								$arradultsrooms[$roomnumb] = $numadults;
								$arrpeople[$roomnumb]['adults'] = $numadults;
								$strclause = "(`fromadult`<=".$numadults." AND `toadult`>=".$numadults."";
								if (!empty($args['children'][$kad]) && intval($args['children'][$kad]) > 0) {
									$numchildren = intval($args['children'][$kad]);
									$arrpeople[$roomnumb]['children'] = $numchildren;
									$arrpeople[$roomnumb]['children_age'] = isset($args['children_age'][$roomnumb]) && count($args['children_age'][$roomnumb]) ? $args['children_age'][$roomnumb] : array();
									$strclause .= " AND `fromchild`<=".$numchildren." AND `tochild`>=".$numchildren."";
								} else {
									$arrpeople[$roomnumb]['children'] = 0;
									$arrpeople[$roomnumb]['children_age'] = array();
									if (intval($args['children'][$kad]) == 0) {
										$strclause .= " AND `fromchild` = 0";
									}
								}
								$strclause .= " AND `totpeople` >= ".($arrpeople[$roomnumb]['adults'] + $arrpeople[$roomnumb]['children']);
								$strclause .= " AND `mintotpeople` <= ".($arrpeople[$roomnumb]['adults'] + $arrpeople[$roomnumb]['children']);
								$strclause .= ")";
								$arradultsclause[] = $strclause;
							}
						}
					}
				}
				//
				//Set $args['adults'] to the number of adults occupying the first room but it could be a party of multiple rooms
				$args['num_adults'] = $arrpeople[1]['adults'];
				//
				//This clause would return one room type for each party type: implode(" OR ", $arradultsclause) - the AND clause must be used rather than OR.
				$q = "SELECT `id`, `units` FROM `#__vikbooking_rooms` WHERE `avail`=1 AND (".implode(" AND ", $arradultsclause).") AND `id` IN (".implode(',', $tac_rooms).");";
				
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 0) {
					echo json_encode(array('e4j.error' => 'RoomNotAvailable', 'error.code' => 1));
					exit;
				}
		
				$avail_rooms = $dbo->loadAssocList();
				
				if (count($arrpeople) != $args['num_rooms']) {
					echo json_encode(array('e4j.error' => 'RoomNotAvailable', 'error.code' => 2));
					exit;
				}
				
				require_once (VBO_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "lib.vikbooking.php");
				
				// arr[0] = (sec) checkin, arr[1] = (sec) checkout
				$check_in_out = VikBooking::getTimeOpenStore();
				$args['start_ts'] += $check_in_out[0];
				$args['end_ts'] += $check_in_out[1];
				
				$room_ids = array();
				for ($i = 0; $i < count($avail_rooms); $i++) {
					$room_ids[$i] = $avail_rooms[$i]['id'];
				}
				
				$all_restrictions = VikBooking::loadRestrictions(true, $room_ids);
				$glob_restrictions = VikBooking::globalRestrictions($all_restrictions);
				
				if (count($glob_restrictions) > 0 && strlen(VikBooking::validateRoomRestriction($glob_restrictions, getdate($args['start_ts']), getdate($args['end_ts']), $args['nights'])) > 0) {
					echo json_encode(array('e4j.error' => 'RoomNotAvailable', 'error.code' => 3));
					exit;
				}
				
				//Get Rates
				$room_ids = array();
				foreach ($avail_rooms as $k => $room) {
					$room_ids[$room['id']] = $room;
				}
				$rates = array();
				$q = "SELECT `p`.*, `r`.`img`, `r`.`units`, `r`.`moreimgs`, `r`.`imgcaptions`, `prices`.`name` AS `pricename`, `prices`.`breakfast_included`, `prices`.`free_cancellation`, `prices`.`canc_deadline` FROM `#__vikbooking_dispcost` AS `p`, `#__vikbooking_rooms` AS `r`, `#__vikbooking_prices` AS `prices` WHERE `r`.`id`=`p`.`idroom` AND `p`.`idprice`=`prices`.`id` AND `p`.`days`=".$args['nights']." AND `p`.`id` IN (".implode(',', array_keys($partner_rates)).") AND `r`.`id` IN (".implode(',', array_keys($room_ids)).") ORDER BY `p`.`cost` ASC;";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 0) {
					echo json_encode(array());
					exit;
				}
				$rates = $dbo->loadAssocList();
				$arr_rates = array();
				foreach ($rates as $rate) {
					$arr_rates[$rate['idroom']][] = $rate;
				}
				
				//Check Availability for the rooms with a rate for this number of nights
				$minus_units = 0;
				if (count($arr_rates) < $args['num_rooms']) {
					$minus_units = $args['num_rooms'] - count($arr_rates);
				}
				foreach ($arr_rates as $k => $datarate) {
					$room = $room_ids[$k];
					$consider_units = $room['units'] - $minus_units;
					if (!VikBooking::roomBookable($room['id'], $consider_units, $args['start_ts'], $args['end_ts']) || $consider_units <= 0) {
						unset($arr_rates[$k]);
					} else {
						
						if (count($all_restrictions) > 0) {
							$room_restr = VikBooking::roomRestrictions($room['id'], $all_restrictions);
							if (count($room_restr) > 0) {
								if (strlen(VikBooking::validateRoomRestriction($room_restr, getdate($args['start_ts']), getdate($args['end_ts']), $args['nights'])) > 0) {
									unset($arr_rates[$k]);
								}
							}
						}	
						
					}
				}
				
				if (count($arr_rates) == 0) {
					echo json_encode(array('e4j.error' => 'RoomNotAvailable', 'error.code' => 4));
					exit;
				}
				
				//apply special prices
				$arr_rates = VikBooking::applySeasonalPrices($arr_rates, $args['start_ts'], $args['end_ts']);
				$multi_rates = 1;
				foreach ($arr_rates as $idr => $tars) {
					$multi_rates = count($tars) > $multi_rates ? count($tars) : $multi_rates;
				}
				if ($multi_rates > 1) {
					for($r = 1; $r < $multi_rates; $r++) {
						$deeper_rates = array();
						foreach ($arr_rates as $idr => $tars) {
							foreach ($tars as $tk => $tar) {
								if ($tk == $r) {
									$deeper_rates[$idr][0] = $tar;
									break;
								}
							}
						}
						if (!count($deeper_rates) > 0) {
							continue;
						}
						$deeper_rates = VikBooking::applySeasonalPrices($deeper_rates, $args['start_ts'], $args['end_ts']);
						foreach ($deeper_rates as $idr => $dtars) {
							foreach ($dtars as $dtk => $dtar) {
								$arr_rates[$idr][$r] = $dtar;
							}
						}
					}
				}
				//
				
				//children ages charge
				$children_sums = array();
				$children_options = array();
				//end children ages charge
				
				//sum charges/discounts per occupancy for each room party
				foreach($arrpeople as $roomnumb => $party) {
					//charges/discounts per adults occupancy
					foreach ($arr_rates as $r => $rates) {
						$children_charges = VikBooking::getChildrenCharges($r, $party['children'], $party['children_age'], $args['nights']);
						if (count($children_charges) > 0) {
							$children_sums[$r] += $children_charges['total'];
							$children_options[$roomnumb] = $children_charges['options'];
						}
						$diffusageprice = VikBooking::loadAdultsDiff($r, $party['adults']);
						//Occupancy Override - Special Price may be setting a charge/discount for this occupancy while default price had no occupancy pricing
						if (!is_array($diffusageprice)) {
							foreach($rates as $kpr => $vpr) {
								if (array_key_exists('occupancy_ovr', $vpr) && array_key_exists($party['adults'], $vpr['occupancy_ovr']) && strlen($vpr['occupancy_ovr'][$party['adults']]['value'])) {
									$diffusageprice = $vpr['occupancy_ovr'][$party['adults']];
									break;
								}
							}
							reset($rates);
						}
						//
						if (is_array($diffusageprice)) {
							foreach($rates as $kpr => $vpr) {
								if ($roomnumb == 1) {
									$arr_rates[$r][$kpr]['costbeforeoccupancy'] = $arr_rates[$r][$kpr]['cost'];
								}
								//Occupancy Override
								if (array_key_exists('occupancy_ovr', $vpr) && array_key_exists($party['adults'], $vpr['occupancy_ovr']) && strlen($vpr['occupancy_ovr'][$party['adults']]['value'])) {
									$diffusageprice = $vpr['occupancy_ovr'][$party['adults']];
								}
								//
								$arr_rates[$r][$kpr]['diffusage'] = $party['adults'];
								if ($diffusageprice['chdisc'] == 1) {
									//charge
									if ($diffusageprice['valpcent'] == 1) {
										//fixed value
										$aduseval = $diffusageprice['pernight'] == 1 ? $diffusageprice['value'] * $arr_rates[$r][$kpr]['days'] : $diffusageprice['value'];
										$arr_rates[$r][$kpr]['diffusagecost'][$roomnumb] = $aduseval;
										$arr_rates[$r][$kpr]['cost'] += $aduseval;
									} else {
										//percentage value
										$aduseval = $diffusageprice['pernight'] == 1 ? round(($arr_rates[$r][$kpr]['costbeforeoccupancy'] * $diffusageprice['value'] / 100) * $arr_rates[$r][$kpr]['days'], 2) : round(($arr_rates[$r][$kpr]['costbeforeoccupancy'] * $diffusageprice['value'] / 100), 2);
										$arr_rates[$r][$kpr]['diffusagecost'][$roomnumb] = $aduseval;
										$arr_rates[$r][$kpr]['cost'] += $aduseval;
									}
								} else {
									//discount
									if ($diffusageprice['valpcent'] == 1) {
										//fixed value
										$aduseval = $diffusageprice['pernight'] == 1 ? $diffusageprice['value'] * $arr_rates[$r][$kpr]['days'] : $diffusageprice['value'];
										$arr_rates[$r][$kpr]['diffusagediscount'][$roomnumb] = $aduseval;
										$arr_rates[$r][$kpr]['cost'] -= $aduseval;
									} else {
										//percentage value
										$aduseval = $diffusageprice['pernight'] == 1 ? round(((($arr_rates[$r][$kpr]['costbeforeoccupancy'] / $arr_rates[$r][$kpr]['days']) * $diffusageprice['value'] / 100) * $arr_rates[$r][$kpr]['days']), 2) : round(($arr_rates[$r][$kpr]['costbeforeoccupancy'] * $diffusageprice['value'] / 100), 2);
										$arr_rates[$r][$kpr]['diffusagediscount'][$roomnumb] = $aduseval;
										$arr_rates[$r][$kpr]['cost'] -= $aduseval;
									}
								}
							}
						} elseif ($roomnumb == 1) {
							foreach($rates as $kpr => $vpr) {
								$arr_rates[$r][$kpr]['costbeforeoccupancy'] = $arr_rates[$r][$kpr]['cost'];
							}
						}
					}
					//end charges/discounts per adults occupancy
				}
				//end sum charges/discounts per occupancy for each room party
				
				//if the rooms are given to a party of multiple rooms, multiply the basic rates per room per number of rooms
				for($i = 2; $i <= $args['num_rooms']; $i++) {
					foreach ($arr_rates as $r => $rates) {
						foreach($rates as $kpr => $vpr) {
							$arr_rates[$r][$kpr]['cost'] += $arr_rates[$r][$kpr]['costbeforeoccupancy'];
						}
					}
				}
				//end if the rooms are given to a party of multiple rooms, multiply the basic rates per room per number of rooms
				
				//children ages charge
				if (count($children_sums) > 0) {
					foreach ($arr_rates as $r => $rates) {
						if (array_key_exists($r, $children_sums)) {
							foreach($rates as $kpr => $vpr) {
								$arr_rates[$r][$kpr]['cost'] += $children_sums[$r];
							}
						}
					}
				}
				//end children ages charge
				
				//compose taxes information
				$ivainclusa = (int)VikBooking::ivaInclusa();
				$rates_ids = array();
				foreach ($arr_rates as $r => $rate) {
					foreach ($rate as $ids) {
						if (!in_array($ids['idprice'], $rates_ids)) {
							$rates_ids[] = $ids['idprice'];
						}
					}
				}
				$tax_rates = array();
				$q = "SELECT `p`.`id`,`t`.`aliq` FROM `#__vikbooking_prices` AS `p` LEFT JOIN `#__vikbooking_iva` `t` ON `p`.`idiva`=`t`.`id` WHERE `p`.`id` IN (".implode(',', $rates_ids).");";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$alltaxrates = $dbo->loadAssocList();
					foreach ($alltaxrates as $tx) {
						if (!empty($tx['aliq']) && $tx['aliq'] > 0) {
							$tax_rates[$tx['id']] = $tx['aliq'];
						}
					}
				}
				$city_tax_fees = array();
				if (count($tax_rates) > 0) {
					foreach ($arr_rates as $r => $rates) {
						//$city_tax_fees = VikBooking::getMandatoryTaxesFees(array($r), $args['num_adults'], $args['nights']);
						foreach ($rates as $k => $rate) {
							if (array_key_exists($rate['idprice'], $tax_rates)) {
								if (intval($ivainclusa) == 1) {
									//prices tax included
									$realcost = $rate['cost'];
									$tax_oper = ($tax_rates[$rate['idprice']] + 100) / 100;
									$taxes = $rate['cost'] - ($rate['cost'] / $tax_oper);
								} else {
									//prices tax excluded ($rate['cost'] must always be rounded or errors will occur when discounts from special prices apply and tax excluded)
									$realcost = round($rate['cost'], 2) * (100 + $tax_rates[$rate['idprice']]) / 100;
									$taxes = $realcost - $rate['cost'];
								}
								$arr_rates[$r][$k]['cost'] = round($realcost, 2);
								$arr_rates[$r][$k]['taxes'] = round($taxes, 2);
								//$arr_rates[$r][$k]['city_taxes'] = round($city_tax_fees['city_taxes'], 2);
								//$arr_rates[$r][$k]['fees'] = round($city_tax_fees['fees'], 2);
							}
						}
					}
					//sum taxes/fees for each room party
					foreach($arrpeople as $roomnumb => $party) {
						foreach ($arr_rates as $r => $rates) {
							$city_tax_fees = VikBooking::getMandatoryTaxesFees(array($r), $party['adults'], $args['nights']);
							foreach ($rates as $k => $rate) {
								if (!isset($arr_rates[$r][$k]['city_taxes'])) {
									$arr_rates[$r][$k]['city_taxes'] = 0;
								}
								if (!isset($arr_rates[$r][$k]['fees'])) {
									$arr_rates[$r][$k]['fees'] = 0;
								}
								$arr_rates[$r][$k]['city_taxes'] += round($city_tax_fees['city_taxes'], 2);
								$arr_rates[$r][$k]['fees'] += round($city_tax_fees['fees'], 2);
							}
						}
					}
					//end sum taxes/fees for each room party
				} else {
					/**
					 * Those without tax rates must still return information about city taxes and fees.
					 * Trivago requested this modification.
					 * 
					 * @since 	1.7.2
					 */
					foreach ($arr_rates as $r => $rates) {
						$city_tax_fees = VikBooking::getMandatoryTaxesFees(array($r), $party['adults'], $args['nights']);
						foreach ($rates as $k => $rate) {
							$arr_rates[$r][$k]['cost'] = round($rate['cost'], 2);
							$arr_rates[$r][$k]['taxes'] = round(0, 2);
							$arr_rates[$r][$k]['city_taxes'] += round($city_tax_fees['city_taxes'], 2);
							$arr_rates[$r][$k]['fees'] += round($city_tax_fees['fees'], 2);
						}
					}
				}
				//end compose taxes information
				$room_ind = key($arr_rates);
				$price_ind = key($arr_rates[$room_ind]);
				$final_price = $arr_rates[$room_ind][$price_ind]['cost'] + $arr_rates[$room_ind][$price_ind]['city_taxes'] + $arr_rates[$room_ind][$price_ind]['fees'];
				$final_price = round($final_price, 2);
				$args['final_price_at_booking']['amount'] = round($args['final_price_at_booking']['amount'], 2);
				if ($final_price < (float)$args['final_price_at_booking']['amount'] || $final_price > (float)$args['final_price_at_booking']['amount']) {
					echo json_encode(array('e4j.error' => 'PriceMismatch', 'error.code' => 2, 'explanation' => JText::sprintf('VCM_TAC_ERR_PRICE_MISMATCH', trim($args['final_price_at_booking']['currency'].' '.$final_price))));
					exit;
				}
				
				$channel_data = VikChannelManager::getChannel(($args['channel'] == 'trivago' ? VikChannelManagerConfig::TRIVAGO : VikChannelManagerConfig::TRIP_CONNECT));
				$channel_settings = json_decode($channel_data['settings'], true);
				$neworder_status = $args['channel'] == 'trivago' || ($args['channel'] != 'trivago' && $channel_settings['paystatus']['value'] == 'VCM_TA_PAYMENT_STATUS_CONFIRMED') ? 'confirmed' : 'standby';
				
				//customer_data and phone
				$custdata = '';
				$phone = '';
				foreach($args['customer_info'] as $custkey => $custval) {
					if ($custkey == 'phone_number') {
						$phone = $custval;
					}
					$readablekv = ucwords(str_replace('_', ' ', $custkey));
					$custdata .= $readablekv.': '.$custval."\n";
				}
				//
				//email
				$customer_email = '';
				if (!empty($args['customer_info']['email'])) {
					$customer_email = $args['customer_info']['email'];
				}
				//
				//country code
				$country_code = '';
				if (!empty($args['customer_info']['country'])) {
					$q = "SELECT * FROM `#__vikbooking_countries` WHERE `country_2_code`='".$args['customer_info']['country']."';";
					$dbo->setQuery($q);
					$dbo->execute();
					if ($dbo->getNumRows() > 0) {
						$vbcountry = $dbo->loadAssocList();
						$country_code = $vbcountry[0]['country_3_code'];
					}
				}
				//
				
				//save order in VikBooking
				$esit = false;
				$neworderid = -1;
				$confirmnumber = '';
				$realback = VikBooking::getHoursRoomAvail() * 3600;
				$realback += $args['end_ts'];
				$sid = VikBooking::getSecretLink();
				$nowts = time();
				$orderlink = JUri::root()."index.php?option=com_vikbooking&task=vieworder&sid=".$sid."&ts=".$nowts;
				$lang = JFactory::getLanguage();
				$langtag = $lang->getTag();
				$options_str = '';
				if (is_array($city_tax_fees['options']) && count($city_tax_fees['options']) > 0) {
					$options_str = implode(';', $city_tax_fees['options']).';';
				}
				
				$pay_str = 'NULL';
				$q = "SELECT `id`, `name` FROM `#__vikbooking_gpayments` WHERE `id`=".VikChannelManager::getDefaultPaymentID()." LIMIT 1;";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$app = $dbo->loadAssoc();
					$pay_str = $app['id'].'='.$app['name'];
				}
				if ($debug_mode === false) {
					//Customers Management (VikBooking 1.6 or higher, check if cpin.php exists - since v1.6)
					$do_customer_management = file_exists(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'cpin.php') ? true : false;
					$traveler_first_name = '';
					$traveler_last_name = '';
					//
					if ($neworder_status == 'confirmed') {
						//Confirmed Status
						$arrbusy = array();
						foreach($arrpeople as $rnum => $party) {
							foreach($arr_rates as $idr => $rate) {
								$q = "INSERT INTO `#__vikbooking_busy` (`idroom`,`checkin`,`checkout`,`realback`) VALUES('" . $idr . "', '" . $args['start_ts'] . "', '" . $args['end_ts'] . "','" . $realback . "');";
								$dbo->setQuery($q);
								$dbo->execute();
								$lid = $dbo->insertid();
								$arrbusy[] = $lid;
							}
						}
						$q = "INSERT INTO `#__vikbooking_orders` (`custdata`,`ts`,`status`,`days`,`checkin`,`checkout`,`custmail`,`sid`,`totpaid`,`ujid`,`coupon`,`roomsnum`,`total`,`idorderota`,`channel`,`lang`,`country`,`tot_taxes`,`tot_city_taxes`,`tot_fees`,`phone`,`idpayment`) VALUES(" . $dbo->quote($custdata) . "," . $nowts . ",'confirmed','" . $args['nights'] . "','" . $args['start_ts'] . "','" . $args['end_ts'] . "'," . $dbo->quote($customer_email) . ",'" . $sid . "',NULL,'',NULL,'".count($arrpeople)."','".$final_price."'," . $dbo->quote($args['reference_id']) . ",'".($args['channel'] == 'trivago' ? 'trivago' : 'tripconnect')."'," . $dbo->quote($langtag) . ",".(!empty($country_code) ? "".$dbo->quote($country_code)."" : 'NULL').",'".$arr_rates[$room_ind][$price_ind]['taxes']."','".$arr_rates[$room_ind][$price_ind]['city_taxes']."','".$arr_rates[$room_ind][$price_ind]['fees']."', " . (!empty($args['phone']) ? $dbo->quote($args['phone']) : 'NULL') . ",".$dbo->quote($pay_str).");";
						$dbo->setQuery($q);
						$dbo->execute();
						$neworderid = $dbo->insertid();
						if (empty($neworderid)) {
							echo json_encode(array('e4j.error' => 'UnknownPartnerProblem', 'error.code' => 1));
							exit;
						}
						//ConfirmationNumber
						$confirmnumber = VikBooking::generateConfirmNumber($neworderid, true);
						//end ConfirmationNumber
						foreach($arrpeople as $rnum => $party) {
							foreach($arr_rates as $idr => $rate) {
								$q = "INSERT INTO `#__vikbooking_ordersbusy` (`idorder`,`idbusy`) VALUES('".$neworderid."', '".$arrbusy[($rnum - 1)]."');";
								$dbo->setQuery($q);
								$dbo->execute();
								$json_ch_age = '';
								if (count($party['children_age']) > 0) {
									$json_ch_age = json_encode(array('age' => $party['children_age']));
								}
								$opt_children = '';
								if (array_key_exists($rnum, $children_options)) {
									$opt_children .= $children_options[$rnum];
								}
								$traveler_first_name = empty($traveler_first_name) ? $args['rooms_info'][$rnum]['traveler_first_name'] : $traveler_first_name;
								$traveler_last_name = empty($traveler_last_name) ? $args['rooms_info'][$rnum]['traveler_last_name'] : $traveler_last_name;
								$q = "INSERT INTO `#__vikbooking_ordersrooms` (`idorder`,`idroom`,`adults`,`children`,`idtar`,`optionals`,`childrenage`,`t_first_name`,`t_last_name`) VALUES('".$neworderid."', '".$idr."', '".$arrpeople[$rnum]['adults']."', '".$arrpeople[$rnum]['children']."', '".$rate[0]['id']."', '".$options_str.$opt_children."', ".(!empty($json_ch_age) ? $dbo->quote($json_ch_age) : 'NULL').", ".$dbo->quote($args['rooms_info'][$rnum]['traveler_first_name']).", ".$dbo->quote($args['rooms_info'][$rnum]['traveler_last_name']).");";
								$dbo->setQuery($q);
								$dbo->execute();
							}
						}
						$esit = true;
						//save customer (VikBooking 1.6 or higher)
						if ($do_customer_management === true && !empty($traveler_first_name) && !empty($traveler_last_name) && !empty($customer_email)) {
							if (!class_exists('VikBookingCustomersPin')) {
								require_once(VBO_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "cpin.php");
							}
							$cpin = new VikBookingCustomersPin();
							$cpin->saveCustomerDetails($traveler_first_name, $traveler_last_name, $customer_email, $phone, $country_code, array());
							$cpin->saveCustomerBooking($neworderid);
						}
						//
						if (file_exists(VCM_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "synch.vikbooking.php")) {
							require_once(VCM_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "synch.vikbooking.php");
							// VCM 1.6.8 - The ReservationsLogger needs to know who triggered the update request, so we pass the 'uniquekey'
							$vcm = new SynchVikBooking($neworderid, array($channel_data['uniquekey']));
							//
							$vcm->sendRequest();
						}
					} else {
						//Pending Status
						$q = "INSERT INTO `#__vikbooking_orders` (`custdata`,`ts`,`status`,`days`,`checkin`,`checkout`,`custmail`,`sid`,`totpaid`,`ujid`,`coupon`,`roomsnum`,`total`,`idorderota`,`channel`,`lang`,`country`,`tot_taxes`,`tot_city_taxes`,`tot_fees`,`phone`,`idpayment`) VALUES(" . $dbo->quote($custdata) . ",'" . $nowts . "','standby','" . $args['nights'] . "','" . $args['start_ts'] . "','" . $args['end_ts'] . "'," . $dbo->quote($customer_email) . ",'" . $sid . "',NULL,'',NULL,'".count($arrpeople)."','".$final_price."'," . $dbo->quote($args['reference_id']) . ",'".($args['channel'] == 'trivago' ? 'trivago' : 'tripconnect')."'," . $dbo->quote($langtag) . ",".(!empty($country_code) ? "".$dbo->quote($country_code)."" : 'NULL').",'".$arr_rates[$room_ind][$price_ind]['taxes']."','".$arr_rates[$room_ind][$price_ind]['city_taxes']."','".$arr_rates[$room_ind][$price_ind]['fees']."', " . (!empty($args['phone']) ? $dbo->quote($args['phone']) : 'NULL') . ",".$dbo->quote($pay_str).");";
						$dbo->setQuery($q);
						$dbo->execute();
						$neworderid = $dbo->insertid();
						if (empty($neworderid)) {
							echo json_encode(array('e4j.error' => 'UnknownPartnerProblem', 'error.code' => 2));
							exit;
						}
						foreach($arrpeople as $rnum => $party) {
							foreach($arr_rates as $idr => $rate) {
								$json_ch_age = '';
								if (count($party['children_age']) > 0) {
									$json_ch_age = json_encode(array('age' => $party['children_age']));
								}
								$opt_children = '';
								if (array_key_exists($rnum, $children_options)) {
									$opt_children .= $children_options[$rnum];
								}
								$traveler_first_name = empty($traveler_first_name) ? $args['rooms_info'][$rnum]['traveler_first_name'] : $traveler_first_name;
								$traveler_last_name = empty($traveler_last_name) ? $args['rooms_info'][$rnum]['traveler_last_name'] : $traveler_last_name;
								$q = "INSERT INTO `#__vikbooking_ordersrooms` (`idorder`,`idroom`,`adults`,`children`,`idtar`,`optionals`,`childrenage`,`t_first_name`,`t_last_name`) VALUES('".$neworderid."', '".$idr."', '".$arrpeople[$rnum]['adults']."', '".$arrpeople[$rnum]['children']."', '".$rate[0]['id']."', '".$options_str.$opt_children."', ".(!empty($json_ch_age) ? $dbo->quote($json_ch_age) : 'NULL').", ".$dbo->quote($args['rooms_info'][$rnum]['traveler_first_name']).", ".$dbo->quote($args['rooms_info'][$rnum]['traveler_last_name']).");";
								$dbo->setQuery($q);
								$dbo->execute();
							}
						}
						foreach($arrpeople as $rnum => $party) {
							foreach($arr_rates as $idr => $rate) {
								//$q = "INSERT INTO `#__vikbooking_tmplock` (`idroom`,`checkin`,`checkout`,`until`,`realback`) VALUES('" . $idr . "','" . $args['start_ts'] . "','" . $args['end_ts'] . "','" . VikBooking::getMinutesLock(true) . "','" . $realback . "');";
								//$dbo->setQuery($q);
								//$dbo->execute();
							}
						}
						$esit = true;
						//save customer (VikBooking 1.6 or higher)
						if ($do_customer_management === true && !empty($traveler_first_name) && !empty($traveler_last_name) && !empty($customer_email)) {
							if (!class_exists('VikBookingCustomersPin')) {
								require_once(VBO_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "cpin.php");
							}
							$cpin = new VikBookingCustomersPin();
							$cpin->saveCustomerDetails($traveler_first_name, $traveler_last_name, $customer_email, $phone, $country_code, array());
							$cpin->saveCustomerBooking($neworderid);
						}
						//
					}
				}
				
				$conf_mail_sent = false;
				if ($debug_mode === false) {
					// send confirmation email to customer
					$conf_mail_sent = VikBooking::sendBookingEmail($neworderid, ['guest']);
				}
				
				$arr_rates['response'] = array(
					'esit' => $esit,
					'status' => $neworder_status,
					'reservationstatus' => 'Booked',
					'id' => $neworderid,
					'confirmnumber' => $confirmnumber,
					'orderlink' => $orderlink,
					'conf_mail_sent' => $conf_mail_sent,
					'currency' => VikBooking::getCurrencyName()
				);
				
				$response = $arr_rates;
								
				// store elapsed time statistics
				$elapsed_time = $crono->stop();
				VikChannelManager::storeCallStats(($args['channel'] == 'trivago' ? VikChannelManagerConfig::TRIVAGO : VikChannelManagerConfig::TRIP_CONNECT), 'tac_bs_l', $elapsed_time);
				//
				
				$args['response'] = $response['response'];
				
				// STORE CC INFO ORDER
				if ($arr_rates['response']['esit'] && $debug_mode === false) {
					$q = "UPDATE `#__vikbooking_orders` SET `paymentlog`=".$dbo->quote(VikChannelManager::getBookingSubmitPaymentLog($args['payment_method']))." WHERE `id`=".$arr_rates['response']['id']." LIMIT 1;";
					$dbo->setQuery($q);
					$dbo->execute();
					
					$admail = VikChannelManager::getAdminMail();
					$adsendermail = VikChannelManager::getSenderMail();
					$subject = $args['channel'] == 'trivago' ? JText::_('VCMTRINEWORDERMAILSUBJECT') : JText::_('VCMTACNEWORDERMAILSUBJECT');
					
					$vik = new VikApplication(VersionListener::getID());
					$vik->sendMail(
						$adsendermail, 
						$adsendermail, 
						$admail, 
						$admail, 
						$subject,
						VikChannelManager::getBookingSubmitCCMailContent($args),
						false
					);
				}
				//

				/**
				 * For tEB bookings we still need to use the Conversion API of late 2019.
				 * 
				 * @since 	1.6.19
				 */
				if ($args['channel'] == 'trivago') {
					// re-fetch full order details
					$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id` = " . (int)$neworderid;
					$dbo->setQuery($q);
					$dbo->execute();
					// find the mandatory "trv_reference" from tEB "tracking_data" (tracking for VCM) object
					$teb_tracking_tmp = !empty($teb_tracking) ? json_decode($teb_tracking) : null;
					$trv_reference = is_object($teb_tracking_tmp) && isset($teb_tracking_tmp->trv_reference) ? $teb_tracking_tmp->trv_reference : '';
					//
					VikChannelManager::generateTrivagoPixel($dbo->loadAssoc(), 2, array('trv_reference' => $trv_reference, 'teb' => 1));
				}
				//
				
				echo json_encode($response);
				exit;
			} else {
				$response = 'e4j.error.auth';
			}
		}
		echo $response;
		exit;
	}

	/**
	 * TripAdvisor rooms availability listener
	 */
	public function tac_av_l()
	{
		$crono = new Crono();
		$crono->start();
		
		$response = 'e4j.error';
		$args = array();
		$args['hash'] = VikRequest::getString('e4jauth', '', 'request');
		$args['start_date'] = VikRequest::getString('start_date', '', 'request');
		$args['end_date'] = VikRequest::getString('end_date', '', 'request');
		$args['nights'] = VikRequest::getInt('nights', 1, 'request');
		$args['num_rooms'] = VikRequest::getInt('num_rooms', 1, 'request');
		$args['start_ts'] = strtotime($args['start_date']);
		$args['end_ts'] = strtotime($args['end_date']);
		
		$valid = true;
		foreach ($args as $k => $v) {
			$valid = $valid && !empty($v);
		}

		// optional TripAdvisor (TripConnect) account ID to filter
		$ta_account_id = VikRequest::getString('ta_account_id', '', 'request');
		
		//request type
		$req_type = VikRequest::getString('req_type', '', 'request');
		
		//API version
		$tac_apiv = 4;
		//API v4
		$args['num_adults'] = VikRequest::getInt('num_adults', 1, 'request');
		//API v5
		$args['adults'] = VikRequest::getVar('adults', array());
		$args['children'] = VikRequest::getVar('children', array());
		$args['children_age'] = VikRequest::getVar('children_age', array());
		if (!empty($args['adults']) && !empty($args['children']) && !isset($_REQUEST['num_adults'])) {
			$tac_apiv = 5;
		}
		if ($tac_apiv == 4) {
			$valid = !empty($args['num_adults']) ? $valid : false;
		} elseif ($tac_apiv == 5) {
			$valid = !empty($args['adults']) ? $valid : false;
		}
		//
		
		if ($valid) {
			$channel = VikChannelManager::getChannelCredentials(VikChannelManagerConfig::TRIP_CONNECT);
			$checkauth = md5($channel['tripadvisorid'].'e4j'.$channel['tripadvisorid']);
			
			if ($checkauth == $args['hash']) {

				$response = $this->retrieve_av_l(VikChannelManager::getChannel(VikChannelManagerConfig::TRIP_CONNECT), $ta_account_id);
				
				//echo '<pre>'.print_r($response, true).'</pre>';
				
				// store elapsed time statistics
				
				$elapsed_time = $crono->stop();
				
				VikChannelManager::storeCallStats(VikChannelManagerConfig::TRIP_CONNECT, 'tac_av_l', $elapsed_time);
				
				//
				
				echo json_encode($response);
				exit;
				
			} else {
				$response = 'e4j.error.auth';
			}
		}
		echo $response;
		exit;
	}
	
	/**
	 * Trivago rooms availability listener
	 */
	public function tri_av_l()
	{
		$crono = new Crono();
		$crono->start();
		
		$response = 'e4j.error';
		$args = array();
		$args['hash'] = VikRequest::getString('e4jauth', '', 'request');
		$args['start_date'] = VikRequest::getString('start_date', '', 'request');
		$args['end_date'] = VikRequest::getString('end_date', '', 'request');
		$args['nights'] = VikRequest::getInt('nights', 1, 'request');
		$args['num_rooms'] = VikRequest::getInt('num_rooms', 1, 'request');
		$args['start_ts'] = strtotime($args['start_date']);
		$args['end_ts'] = strtotime($args['end_date']);
		
		$valid = true;
		foreach ($args as $k => $v) {
			$valid = $valid && !empty($v);
		}
		
		if ($valid) {
			$channel = VikChannelManager::getChannelCredentials(VikChannelManagerConfig::TRIVAGO);
			$checkauth = md5($channel['trivagoid'].'e4j'.$channel['trivagoid']);
			
			if ($checkauth == $args['hash']) {

				$response = $this->retrieve_av_l(VikChannelManager::getChannel(VikChannelManagerConfig::TRIVAGO));

				// store elapsed time statistics
				
				$elapsed_time = $crono->stop();
				
				VikChannelManager::storeCallStats(VikChannelManagerConfig::TRIVAGO, 'tri_av_l', $elapsed_time);
				
				//
				
				echo json_encode($response);
				exit;
				
			} else {
				$response = 'e4j.error.auth';
			}
		}
		echo $response;
		exit;
	}

	/**
	 * Retrieves the availability information from the base meta-search endpoint.
	 * 
	 * @param 	array 	$channel 		The meta-search channel details.
	 * @param 	?string $account_id 	Optional meta-search account ID requested.
	 * 
	 * @return 	array|void
	 * 
	 * @since 	1.9.10  added argument $account_id.
	 */
	private function retrieve_av_l($channel = [], $account_id = null)
	{
		$response = 'e4j.error';
		$args = array();
		$args['hash'] = VikRequest::getString('e4jauth', '', 'request');
		$args['start_date'] = VikRequest::getString('start_date', '', 'request');
		$args['end_date'] = VikRequest::getString('end_date', '', 'request');
		$args['nights'] = VikRequest::getInt('nights', 1, 'request');
		$args['num_rooms'] = VikRequest::getInt('num_rooms', 1, 'request');
		$args['start_ts'] = strtotime($args['start_date']);
		$args['end_ts'] = strtotime($args['end_date']);
		
		//request type
		$req_type = VikRequest::getString('req_type', '', 'request');
		
		//API version
		$tac_apiv = 4;
		//API v4
		$args['num_adults'] = VikRequest::getInt('num_adults', 1, 'request');
		//API v5
		$args['adults'] = VikRequest::getVar('adults', array());
		$args['children'] = VikRequest::getVar('children', array());
		$args['children_age'] = VikRequest::getVar('children_age', array());
		if (!empty($args['adults']) && !empty($args['children']) && !isset($_REQUEST['num_adults'])) {
			$tac_apiv = 5;
		}
		//

		$dbo = JFactory::getDbo();
		
		if ((int)$channel['uniquekey'] == (int)VikChannelManagerConfig::TRIP_CONNECT) {
			$q = "SELECT `id_vb_room` AS `id` FROM `#__vikchannelmanager_tac_rooms` WHERE `account_id` IS NULL OR `account_id` = " . $dbo->q((string) $account_id) . ";";
			$dbo->setQuery($q);
			$rows = $dbo->loadAssocList();
			if (!$rows) {
				echo json_encode(array('e4j.error' => '`#__vikchannelmanager_tac_rooms` is empty'));
				exit;
			}
		} elseif ((int)$channel['uniquekey'] == (int)VikChannelManagerConfig::TRIVAGO) {
			$q = "SELECT `id_vb_room` AS `id` FROM `#__vikchannelmanager_tri_rooms`;";
			$dbo->setQuery($q);
			$rows = $dbo->loadAssocList();
			if (!$rows) {
				echo json_encode(array('e4j.error' => '`#__vikchannelmanager_tri_rooms` is empty'));
				exit;
			}
		} else {
			echo json_encode(array('e4j.error' => 'invalid channel id ('.(int)$channel['uniquekey'].')'));
			exit;
		}

		$tac_rooms = [];
		$avail_rooms = [];
		for ($i = 0; $i < count($rows); $i++) {
			$tac_rooms[$i] = $rows[$i]['id'];
		}

		if ($tac_apiv == 5) {
			//compose adults-children array and sql clause
			$arradultsrooms = array();
			$arradultsclause = array();
			$arrpeople = array();
			if (count($args['adults']) > 0) {
				foreach($args['adults'] as $kad => $adu) {
					$roomnumb = $kad + 1;
					if (strlen($adu)) {
						$numadults = intval($adu);
						if ($numadults >= 0) {
							$arradultsrooms[$roomnumb] = $numadults;
							$arrpeople[$roomnumb]['adults'] = $numadults;
							$strclause = "(`fromadult`<=".$numadults." AND `toadult`>=".$numadults."";
							if (!empty($args['children'][$kad]) && intval($args['children'][$kad]) > 0) {
								$numchildren = intval($args['children'][$kad]);
								$arrpeople[$roomnumb]['children'] = $numchildren;
								$arrpeople[$roomnumb]['children_age'] = isset($args['children_age'][$roomnumb]) && count($args['children_age'][$roomnumb]) ? $args['children_age'][$roomnumb] : array();
								$strclause .= " AND `fromchild`<=".$numchildren." AND `tochild`>=".$numchildren."";
							} else {
								$arrpeople[$roomnumb]['children'] = 0;
								$arrpeople[$roomnumb]['children_age'] = array();
								if (intval($args['children'][$kad]) == 0) {
									$strclause .= " AND `fromchild` = 0";
								}
							}
							$strclause .= " AND `totpeople` >= ".($arrpeople[$roomnumb]['adults'] + $arrpeople[$roomnumb]['children']);
							$strclause .= " AND `mintotpeople` <= ".($arrpeople[$roomnumb]['adults'] + $arrpeople[$roomnumb]['children']);
							$strclause .= ")";
							$arradultsclause[] = $strclause;
						}
					}
				}
			}
			//
			//Set $args['adults'] to the number of adults occupying the first room but it could be a party of multiple rooms
			$args['num_adults'] = $arrpeople[1]['adults'];
			//
			//This clause would return one room type for each party type: implode(" OR ", $arradultsclause) - the AND clause must be used rather than OR.
			$q = "SELECT `id`, `units` FROM `#__vikbooking_rooms` WHERE `avail`=1 AND (".implode(" AND ", $arradultsclause).") AND `id` IN (".implode(',', $tac_rooms).");";
		} else {
			//API v4
			$arrpeople = array();
			$arrpeople[1]['adults'] = $args['num_adults'];
			$arrpeople[1]['children'] = 0;
			$q = "SELECT `id`, `units` FROM `#__vikbooking_rooms` WHERE `avail`=1 AND `toadult`>=".$args['num_adults']." AND `id` IN (".implode(',', $tac_rooms).");";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 0) {
			echo json_encode(array('e4j.error' => 'The Query for fetching the rooms returned an empty result'));
			exit;
		}

		$avail_rooms = $dbo->loadAssocList();
		
		require_once (VBO_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "lib.vikbooking.php");
		
		// arr[0] = (sec) checkin, arr[1] = (sec) checkout
		$check_in_out = VikBooking::getTimeOpenStore();
		$args['start_ts'] += $check_in_out[0];
		$args['end_ts'] += $check_in_out[1];
		
		$room_ids = array();
		for ($i = 0; $i < count($avail_rooms); $i++) {
			$room_ids[$i] = $avail_rooms[$i]['id'];
		}
		
		$all_restrictions = VikBooking::loadRestrictions(true, $room_ids);
		$glob_restrictions = VikBooking::globalRestrictions($all_restrictions);
		
		if (count($glob_restrictions) > 0 && strlen(VikBooking::validateRoomRestriction($glob_restrictions, getdate($args['start_ts']), getdate($args['end_ts']), $args['nights'])) > 0) {
			echo json_encode(array('e4j.error' => 'Unable to proceed because of booking Restrictions in these dates'));
			exit;
		}

		//April 2017 - Check Property Closing Dates
		$err_closingdates = VikBooking::validateClosingDates($args['start_ts'], $args['end_ts'], '');
		if (!empty($err_closingdates)) {
			echo json_encode(array('e4j.error' => 'The property will be closed on the selected dates ('.$err_closingdates.')'));
			exit;
		}
		//
		
		//Get Rates
		$room_ids = array();
		foreach ($avail_rooms as $k => $room) {
			$room_ids[$room['id']] = $room;
		}

		//$q = "SELECT `p`.*, `r`.`id` AS `r_reference_id`, `r`.`img`, `r`.`units`, `r`.`moreimgs`, `r`.`imgcaptions`, `prices`.`name` AS `pricename`, `prices`.`breakfast_included`, `prices`.`free_cancellation`, `prices`.`canc_deadline` FROM `#__vikbooking_dispcost` AS `p`, `#__vikbooking_rooms` AS `r`, `#__vikbooking_prices` AS `prices` WHERE `r`.`id`=`p`.`idroom` AND `p`.`idprice`=`prices`.`id` AND `p`.`days`=".$args['nights']." AND `r`.`id` IN (".implode(',', array_keys($room_ids)).") ORDER BY `p`.`cost` ASC;";
		$q = "SELECT `p`.*, `r`.`id` AS `r_reference_id`, `r`.`name` AS `r_short_desc`, `r`.`img`, `r`.`units`, `r`.`moreimgs`, `r`.`imgcaptions`, `r`.`fromadult`, `r`.`toadult`, `r`.`fromchild`, `r`.`tochild`, `r`.`totpeople`, `r`.`mintotpeople`, `prices`.`id` AS `price_reference_id`, `prices`.`name` AS `pricename`, `prices`.`breakfast_included`, `prices`.`free_cancellation`, `prices`.`canc_deadline`, `prices`.`minlos`, `prices`.`minhadv` FROM `#__vikbooking_dispcost` AS `p`, `#__vikbooking_rooms` AS `r`, `#__vikbooking_prices` AS `prices` WHERE `r`.`id`=`p`.`idroom` AND `p`.`idprice`=`prices`.`id` AND `p`.`days`=".$args['nights']." AND `r`.`id` IN (".implode(',', array_keys($room_ids)).") ORDER BY `p`.`cost` ASC;";
		$dbo->setQuery($q);
		$rates = $dbo->loadAssocList();
		if (!$rates) {
			echo json_encode(array('e4j.error' => 'The Query for fetching the rates returned an empty result'));
			exit;
		}

		if (method_exists('VikBooking', 'getTranslator')) {
			$vbo_tn = VikBooking::getTranslator();
			$vbo_tn->translateContents($rates, '#__vikbooking_rooms', array('id' => 'r_reference_id', 'r_short_desc' => 'name'));
			$vbo_tn->translateContents($rates, '#__vikbooking_prices', array('id' => 'price_reference_id', 'pricename' => 'name'));
		}
		//May 2016 API 7 TripConnect - Check-in/Check-out times
		$opent = VikBooking::getHoursMinutes($check_in_out[0]);
		$closet = VikBooking::getHoursMinutes($check_in_out[1]);
		$hcheckin = intval($opent[0]) < 10 ? '0'.$opent[0] : $opent[0];
		$mcheckin = intval($opent[1]) < 10 ? '0'.$opent[1] : $opent[1];
		$hcheckout = intval($closet[0]) < 10 ? '0'.$closet[0] : $closet[0];
		$mcheckout = intval($closet[1]) < 10 ? '0'.$closet[1] : $closet[1];
		foreach ($rates as $keyr => $valuer) {
			$valuer['checkin_time'] = $hcheckin.':'.$mcheckin;
			$valuer['checkout_time'] = $hcheckout.':'.$mcheckout;
			$rates[$keyr] = $valuer;
		}
		//
		/**
		 * @wponly 	we need to pass the rewritten version of the URL that renders a page of VikBooking
		 * 			or the landing page will be index.php?option=com_vikbooking... with no results displayed.
		 */
		$sef_urls = array();
		$model 	  = JModel::getInstance('vikbooking', 'shortcodes', 'admin');
		$itemid   = $model->best(array('vikbooking', 'roomslist', 'roomdetails'));
		if ($itemid) {
			$all_rids = array();
			foreach ($rates as $rate) {
				if (!in_array($rate['idroom'], $all_rids)) {
					array_push($all_rids, $rate['idroom']);
				}
			}
			foreach ($all_rids as $rid) {
				$sef_urls[$rid] = JRoute::_("index.php?option=com_vikbooking&task=search&roomid={$rid}&Itemid={$itemid}", false);
			}
		}
		//
		$arr_rates = array();
		foreach ($rates as $rate) {
			/**
			 * @wponly 	pass the rewritten version of the URL that renders a page of VikBooking (if found above)
			 */
			if (isset($sef_urls[$rate['idroom']])) {
				$rate['sef_url'] = $sef_urls[$rate['idroom']];
			}
			//
			$arr_rates[$rate['idroom']][] = $rate;
		}

		// VCM 1.6.12 - Closed rate plans on these dates and rate plans with a minlos, or with a min hours in advance
		if (method_exists('VikBooking', 'getRoomRplansClosedInDates')) {
			$roomrpclosed = VikBooking::getRoomRplansClosedInDates(array_keys($arr_rates), $args['start_ts'], $args['nights']);
			if (count($roomrpclosed) > 0) {
				foreach ($arr_rates as $kk => $tt) {
					if (array_key_exists($kk, $roomrpclosed)) {
						foreach ($tt as $tk => $tv) {
							if (array_key_exists($tv['idprice'], $roomrpclosed[$kk])) {
								unset($arr_rates[$kk][$tk]);
							}
						}
						if (!(count($arr_rates[$kk]) > 0)) {
							unset($arr_rates[$kk]);
						} else {
							$arr_rates[$kk] = array_values($arr_rates[$kk]);
						}
					}
				}
			}
		}
		$hoursdiff = method_exists('VikBooking', 'countHoursToArrival') ? VikBooking::countHoursToArrival($args['start_ts']) : -1;
		foreach ($arr_rates as $kk => $tt) {
			foreach ($tt as $tk => $tv) {
				if (!empty($tv['minlos']) && $tv['minlos'] > $args['nights']) {
					unset($arr_rates[$kk][$tk]);
				} elseif ($hoursdiff >= 0 && $hoursdiff < $tv['minhadv']) {
					unset($arr_rates[$kk][$tk]);
				}
			}
			if (!(count($arr_rates[$kk]) > 0)) {
				unset($arr_rates[$kk]);
			} else {
				$arr_rates[$kk] = array_values($arr_rates[$kk]);
			}
		}
		//
		
		//Check Availability for the rooms with a rate for this number of nights
		$minus_units = 0;
		if (count($arr_rates) < $args['num_rooms']) {
			$minus_units = $args['num_rooms'] - count($arr_rates);
		}
		$groupdays = VikBooking::getGroupDays($args['start_ts'], $args['end_ts'], $args['nights']);
		$morehst = VikBooking::getHoursRoomAvail() * 3600;
		$allbusy = VikBooking::loadBusyRecords(array_keys($arr_rates), $args['start_ts'], $args['end_ts']);
		foreach ($arr_rates as $k => $datarate) {
			$room = $room_ids[$k];
			$consider_units = $room['units'] - $minus_units;
			//March 31st 2015: old availability check
			//if (!VikBooking::roomBookable($room['id'], $consider_units, $args['start_ts'], $args['end_ts']) || $consider_units <= 0) { = do unset, continue.
			//New Availability Check + Unitsleft
			if ($consider_units <= 0) {
				unset($arr_rates[$k]);
				continue;
			}
			$units_left = $room['units'];
			if (count($allbusy) > 0 && array_key_exists($k, $allbusy) && count($allbusy[$k]) > 0) {
				$units_booked = array();
				foreach ($groupdays as $gday) {
					$bfound = 0;
					foreach ($allbusy[$k] as $bu) {
						if ($gday >= $bu['checkin'] && $gday <= ($morehst + $bu['checkout'])) {
							$bfound++;
						}
					}
					if ($bfound >= $consider_units) {
						unset($arr_rates[$k]);
						continue 2;
					} else {
						$units_booked[] = $bfound;
					}
				}
				if (count($units_booked) > 0) {
					$tot_u_booked = max($units_booked);
					$tot_u_left = ($room['units'] - $tot_u_booked);
					$units_left = $tot_u_left > 0 ? $tot_u_left : 1;
				}
			}
			foreach ($arr_rates[$k] as $tpk => $tpv) {
				//Cancellation Deadline and Rooms Available
				if (array_key_exists('canc_deadline', $tpv) && !empty($tpv['canc_deadline']) && intval($tpv['canc_deadline']) > 0) {
					$is_dst = date('I', $args['start_ts']);
					$canc_date_ts = $args['start_ts'] - (86400 * intval($tpv['canc_deadline']));
					$is_now_dst = date('I', $canc_date_ts);
					if ($is_dst != $is_now_dst) {
						//Daylight Saving Time has changed, check how
						if ((int)$is_dst == 1) {
							$canc_date_ts += 3600;
						} else {
							$canc_date_ts -= 3600;
						}
						$is_dst = $is_now_dst;
					}
					$arr_rates[$k][$tpk]['canc_deadline_date'] = date('Y-m-dTH:i:s', $canc_date_ts);
				}
				$arr_rates[$k][$tpk]['unitsleft'] = (int)$units_left;
			}
			//
			if (count($all_restrictions) > 0) {
				$room_restr = VikBooking::roomRestrictions($room['id'], $all_restrictions);
				if (count($room_restr) > 0) {
					if (strlen(VikBooking::validateRoomRestriction($room_restr, getdate($args['start_ts']), getdate($args['end_ts']), $args['nights'])) > 0) {
						unset($arr_rates[$k]);
					}
				}
			}
		}
		
		if (count($arr_rates) == 0) {
			echo json_encode(array('e4j.error' => 'No availability for these dates'));
			exit;
		}
		
		//apply special prices
		$arr_rates = VikBooking::applySeasonalPrices($arr_rates, $args['start_ts'], $args['end_ts']);
		$multi_rates = 1;
		foreach ($arr_rates as $idr => $tars) {
			$multi_rates = count($tars) > $multi_rates ? count($tars) : $multi_rates;
		}
		if ($multi_rates > 1) {
			for($r = 1; $r < $multi_rates; $r++) {
				$deeper_rates = array();
				foreach ($arr_rates as $idr => $tars) {
					foreach ($tars as $tk => $tar) {
						if ($tk == $r) {
							$deeper_rates[$idr][0] = $tar;
							break;
						}
					}
				}
				if (!count($deeper_rates) > 0) {
					continue;
				}
				$deeper_rates = VikBooking::applySeasonalPrices($deeper_rates, $args['start_ts'], $args['end_ts']);
				foreach ($deeper_rates as $idr => $dtars) {
					foreach ($dtars as $dtk => $dtar) {
						$arr_rates[$idr][$r] = $dtar;
					}
				}
			}
		}
		//
		
		//children ages charge
		$children_sums = array();
		$children_sums_rooms = array();
		//end children ages charge
		
		//sum charges/discounts per occupancy for each room party
		foreach($arrpeople as $roomnumb => $party) {
			//charges/discounts per adults occupancy
			foreach ($arr_rates as $r => $rates) {
				$children_charges = VikBooking::getChildrenCharges($r, $party['children'], $party['children_age'], $args['nights']);
				if (count($children_charges) > 0) {
					$children_sums[$r] += $children_charges['total'];
					$children_sums_rooms[$roomnumb][$r] += $children_charges['total'];
				}
				$diffusageprice = VikBooking::loadAdultsDiff($r, $party['adults']);
				//Occupancy Override - Special Price may be setting a charge/discount for this occupancy while default price had no occupancy pricing
				if (!is_array($diffusageprice)) {
					foreach($rates as $kpr => $vpr) {
						if (array_key_exists('occupancy_ovr', $vpr) && array_key_exists($party['adults'], $vpr['occupancy_ovr']) && strlen($vpr['occupancy_ovr'][$party['adults']]['value'])) {
							$diffusageprice = $vpr['occupancy_ovr'][$party['adults']];
							break;
						}
					}
					reset($rates);
				}
				//
				if (is_array($diffusageprice)) {
					foreach($rates as $kpr => $vpr) {
						if ($roomnumb == 1) {
							$arr_rates[$r][$kpr]['costbeforeoccupancy'] = $arr_rates[$r][$kpr]['cost'];
						}
						//Occupancy Override
						if (array_key_exists('occupancy_ovr', $vpr) && array_key_exists($party['adults'], $vpr['occupancy_ovr']) && strlen($vpr['occupancy_ovr'][$party['adults']]['value'])) {
							$diffusageprice = $vpr['occupancy_ovr'][$party['adults']];
						}
						//
						$room_cost = $arr_rates[$r][$kpr]['costbeforeoccupancy'];
						$arr_rates[$r][$kpr]['diffusage'] = $party['adults'];
						if ($diffusageprice['chdisc'] == 1) {
							//charge
							if ($diffusageprice['valpcent'] == 1) {
								//fixed value
								$aduseval = $diffusageprice['pernight'] == 1 ? $diffusageprice['value'] * $arr_rates[$r][$kpr]['days'] : $diffusageprice['value'];
								$arr_rates[$r][$kpr]['diffusagecost'][$roomnumb] = $aduseval;
								$arr_rates[$r][$kpr]['cost'] += $aduseval;
								$room_cost += $aduseval;
							} else {
								//percentage value
								$aduseval = $diffusageprice['pernight'] == 1 ? round(($arr_rates[$r][$kpr]['costbeforeoccupancy'] * $diffusageprice['value'] / 100) * $arr_rates[$r][$kpr]['days'], 2) : round(($arr_rates[$r][$kpr]['costbeforeoccupancy'] * $diffusageprice['value'] / 100), 2);
								$arr_rates[$r][$kpr]['diffusagecost'][$roomnumb] = $aduseval;
								$arr_rates[$r][$kpr]['cost'] += $aduseval;
								$room_cost += $aduseval;
							}
						} else {
							//discount
							if ($diffusageprice['valpcent'] == 1) {
								//fixed value
								$aduseval = $diffusageprice['pernight'] == 1 ? $diffusageprice['value'] * $arr_rates[$r][$kpr]['days'] : $diffusageprice['value'];
								$arr_rates[$r][$kpr]['diffusagediscount'][$roomnumb] = $aduseval;
								$arr_rates[$r][$kpr]['cost'] -= $aduseval;
								$room_cost -= $aduseval;
							} else {
								//percentage value
								$aduseval = $diffusageprice['pernight'] == 1 ? round(((($arr_rates[$r][$kpr]['costbeforeoccupancy'] / $arr_rates[$r][$kpr]['days']) * $diffusageprice['value'] / 100) * $arr_rates[$r][$kpr]['days']), 2) : round(($arr_rates[$r][$kpr]['costbeforeoccupancy'] * $diffusageprice['value'] / 100), 2);
								$arr_rates[$r][$kpr]['diffusagediscount'][$roomnumb] = $aduseval;
								$arr_rates[$r][$kpr]['cost'] -= $aduseval;
								$room_cost -= $aduseval;
							}
						}
						//Trivago: save in array the cost for each Room Number when multiple rooms
						//Their system needs the rooms for any party returned separately, therefore the cost must be exact depending on the occupancy
						if ((int)$channel['uniquekey'] == (int)VikChannelManagerConfig::TRIVAGO && $args['num_rooms'] > 1) {
							$arr_rates[$r][$kpr]['cost_array'][(int)$roomnumb] = $room_cost;
						}
					}
				} elseif ($roomnumb == 1) {
					foreach($rates as $kpr => $vpr) {
						$arr_rates[$r][$kpr]['costbeforeoccupancy'] = $arr_rates[$r][$kpr]['cost'];
						//Trivago: save in array the cost for each Room Number when multiple rooms
						//Their system needs the rooms for any party returned separately, therefore the cost must be exact depending on the occupancy
						if ((int)$channel['uniquekey'] == (int)VikChannelManagerConfig::TRIVAGO && $args['num_rooms'] > 1) {
							$arr_rates[$r][$kpr]['cost_array'][(int)$roomnumb] = $arr_rates[$r][$kpr]['cost'];
						}
					}
				} elseif ((int)$channel['uniquekey'] == (int)VikChannelManagerConfig::TRIVAGO && $args['num_rooms'] > 1) {
					//Trivago: save in array the cost for each Room Number when multiple rooms
					//Their system needs the rooms for any party returned separately, therefore the cost must be exact depending on the occupancy
					foreach($rates as $kpr => $vpr) {
						$arr_rates[$r][$kpr]['cost_array'][(int)$roomnumb] = $arr_rates[$r][$kpr]['cost'];
					}
				}
			}
			//end charges/discounts per adults occupancy
		}
		//end sum charges/discounts per occupancy for each room party
		
		//if the rooms are given to a party of multiple rooms, multiply the basic rates per room per number of rooms
		for($i = 2; $i <= $args['num_rooms']; $i++) {
			foreach ($arr_rates as $r => $rates) {
				foreach($rates as $kpr => $vpr) {
					$arr_rates[$r][$kpr]['cost'] += $arr_rates[$r][$kpr]['costbeforeoccupancy'];
				}
			}
		}
		//end if the rooms are given to a party of multiple rooms, multiply the basic rates per room per number of rooms
		
		//children ages charge
		if (count($children_sums) > 0) {
			foreach ($arr_rates as $r => $rates) {
				if (array_key_exists($r, $children_sums)) {
					foreach($rates as $kpr => $vpr) {
						$arr_rates[$r][$kpr]['cost'] += $children_sums[$r];
					}
				}
			}
		}
		if ((int)$channel['uniquekey'] == (int)VikChannelManagerConfig::TRIVAGO) {
			foreach ($arrpeople as $roomnumb => $party) {
				if (isset($children_sums_rooms[$roomnumb]) && count($children_sums_rooms[$roomnumb])) {
					foreach ($arr_rates as $r => $rates) {
						if (array_key_exists($r, $children_sums_rooms[$roomnumb])) {
							foreach($rates as $kpr => $vpr) {
								$arr_rates[$r][$kpr]['cost_array'][(int)$roomnumb] += $children_sums_rooms[$roomnumb][$r];
							}
						}
					}
				}
			}
		}
		//end children ages charge
		
		//sort results by price ASC
		$arr_rates = VikBooking::sortResults($arr_rates);
		//
		
		//compose taxes information
		$ivainclusa = (int)VikBooking::ivaInclusa();
		$rates_ids = array();
		foreach ($arr_rates as $r => $rate) {
			foreach ($rate as $ids) {
				if (!in_array($ids['idprice'], $rates_ids)) {
					$rates_ids[] = $ids['idprice'];
				}
			}
		}
		$tax_rates = array();
		$q = "SELECT `p`.`id`,`t`.`aliq` FROM `#__vikbooking_prices` AS `p` LEFT JOIN `#__vikbooking_iva` `t` ON `p`.`idiva`=`t`.`id` WHERE `p`.`id` IN (".implode(',', $rates_ids).");";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$alltaxrates = $dbo->loadAssocList();
			foreach ($alltaxrates as $tx) {
				if (!empty($tx['aliq']) && $tx['aliq'] > 0) {
					$tax_rates[$tx['id']] = $tx['aliq'];
				}
			}
		}
		if (count($tax_rates) > 0) {
			foreach ($arr_rates as $r => $rates) {
				//$city_tax_fees = VikBooking::getMandatoryTaxesFees(array($r), $args['num_adults'], $args['nights']);
				foreach ($rates as $k => $rate) {
					if (array_key_exists($rate['idprice'], $tax_rates)) {
						if (intval($ivainclusa) == 1) {
							//prices tax included
							$realcost = $rate['cost'];
							$tax_oper = ($tax_rates[$rate['idprice']] + 100) / 100;
							$taxes = $rate['cost'] - ($rate['cost'] / $tax_oper);
						} else {
							//prices tax excluded
							$realcost = $rate['cost'] * (100 + $tax_rates[$rate['idprice']]) / 100;
							$taxes = $realcost - $rate['cost'];
						}
						if ($req_type == 'hotel_availability' || $req_type == 'booking_availability') {
							//always set 'cost' to the base rate tax excluded
							$realcost = $realcost - $taxes;
						}
						$arr_rates[$r][$k]['cost'] = round($realcost, 2);
						$arr_rates[$r][$k]['taxes'] = round($taxes, 2);
						//$arr_rates[$r][$k]['city_taxes'] = round($city_tax_fees['city_taxes'], 2);
						//$arr_rates[$r][$k]['fees'] = round($city_tax_fees['fees'], 2);
					}
				}
			}
			//sum taxes/fees for each room party
			foreach($arrpeople as $roomnumb => $party) {
				foreach ($arr_rates as $r => $rates) {
					$city_tax_fees = VikBooking::getMandatoryTaxesFees(array($r), $party['adults'], $args['nights']);
					foreach ($rates as $k => $rate) {
						//Trivago: save in array the city_taxes and fees for each Room Number when multiple rooms
						//Their system needs the rooms for any party returned separately, therefore the taxes and fees must be exact depending on the occupancy
						if ((int)$channel['uniquekey'] == (int)VikChannelManagerConfig::TRIVAGO && $args['num_rooms'] > 1) {
							$arr_rates[$r][$k]['city_taxes_array'][(int)$roomnumb] = round($city_tax_fees['city_taxes'], 2);
							$arr_rates[$r][$k]['fees_array'][(int)$roomnumb] = round($city_tax_fees['fees'], 2);
							//Trivago re-calculate taxes
							if (array_key_exists($rate['idprice'], $tax_rates)) {
								if (intval($ivainclusa) == 1) {
									//prices tax included
									$realcost = $arr_rates[$r][$k]['cost_array'][(int)$roomnumb];
									$tax_oper = ($tax_rates[$rate['idprice']] + 100) / 100;
									$taxes = $arr_rates[$r][$k]['cost_array'][(int)$roomnumb] - ($arr_rates[$r][$k]['cost_array'][(int)$roomnumb] / $tax_oper);
								} else {
									//prices tax excluded
									$realcost = $arr_rates[$r][$k]['cost_array'][(int)$roomnumb] * (100 + $tax_rates[$rate['idprice']]) / 100;
									$taxes = $realcost - $arr_rates[$r][$k]['cost_array'][(int)$roomnumb];
								}
								//always set 'cost' to the base rate tax excluded
								$realcost = $realcost - $taxes;
								$arr_rates[$r][$k]['cost_array'][(int)$roomnumb] = round($realcost, 2);
								$arr_rates[$r][$k]['taxes_array'][(int)$roomnumb] = round($taxes, 2);
							}
							//end Trivago re-calculate taxes
						}
						//TripConnect
						if (!isset($arr_rates[$r][$k]['city_taxes'])) {
							$arr_rates[$r][$k]['city_taxes'] = 0;
						}
						if (!isset($arr_rates[$r][$k]['fees'])) {
							$arr_rates[$r][$k]['fees'] = 0;
						}
						$arr_rates[$r][$k]['city_taxes'] += round($city_tax_fees['city_taxes'], 2);
						$arr_rates[$r][$k]['fees'] += round($city_tax_fees['fees'], 2);
					}
				}
			}
			//end sum taxes/fees for each room party
		} else {
			/**
			 * Those without tax rates must still return information about city taxes and fees.
			 * Trivago requested this modification.
			 * 
			 * @since 	1.6.16
			 */
			foreach ($arr_rates as $r => $rates) {
				$city_tax_fees = VikBooking::getMandatoryTaxesFees(array($r), $arrpeople[1]['adults'], $args['nights']);
				foreach ($rates as $k => $rate) {
					$arr_rates[$r][$k]['cost'] = round($rate['cost'], 2);
					$arr_rates[$r][$k]['taxes'] = round(0, 2);
					if (!isset($arr_rates[$r][$k]['city_taxes'])) {
						$arr_rates[$r][$k]['city_taxes'] = 0;
					}
					if (!isset($arr_rates[$r][$k]['fees'])) {
						$arr_rates[$r][$k]['fees'] = 0;
					}
					$arr_rates[$r][$k]['city_taxes'] += round($city_tax_fees['city_taxes'], 2);
					$arr_rates[$r][$k]['fees'] += round($city_tax_fees['fees'], 2);
				}
			}
		}
		//end compose taxes information

		return $arr_rates;
	}

	/**
	 * TripAdvisor rooms information listener
	 */
	public function tac_ri_l()
	{
		$crono = new Crono();
		$crono->start();
		
		$response = 'e4j.error';
		$args = array();
		$args['hash'] = VikRequest::getString('e4jauth', '', 'request');
		
		$valid = true;
		foreach ($args as $k => $v) {
			$valid = $valid && !empty($v);
		}
		
		//request type
		$req_type = VikRequest::getString('req_type', '', 'request');
		
		//API version
		$tac_apiv = 7;
				
		if ($valid) {
			$channel = VikChannelManager::getChannelCredentials(VikChannelManagerConfig::TRIP_CONNECT);
			$checkauth = md5($channel['tripadvisorid'].'e4j'.$channel['tripadvisorid']);
			
			if ($checkauth == $args['hash']) {

				$response = $this->retrieve_ri_l(VikChannelManager::getChannel(VikChannelManagerConfig::TRIP_CONNECT));
				
				// store elapsed time statistics
				
				$elapsed_time = $crono->stop();
				
				VikChannelManager::storeCallStats(VikChannelManagerConfig::TRIP_CONNECT, 'tac_ri_l', $elapsed_time);
				
				//
				
				echo json_encode($response);
				exit;
				
			} else {
				$response = 'e4j.error.auth';
			}
		}
		echo $response;
		exit;
	}

	/**
	 * TripAdvisor (API 7) retrieve Rooms Information (VCM >= 1.4.3)
	 */
	private static function retrieve_ri_l($channel = array())
	{
		$response = 'e4j.error';
		$args = array();
		$args['hash'] = VikRequest::getString('e4jauth', '', 'request');
		
		//request type
		$req_type = VikRequest::getString('req_type', '', 'request');
		
		//API version
		$tac_apiv = 7;
		
		$tac_rooms = array();
		$dbo = JFactory::getDbo();
		
		if ((int)$channel['uniquekey'] == (int)VikChannelManagerConfig::TRIP_CONNECT) {
			$q = "SELECT `id_vb_room` AS `id` FROM `#__vikchannelmanager_tac_rooms`;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 0) {
				echo json_encode(array('e4j.error' => '`#__vikchannelmanager_tac_rooms` is empty'));
				exit;
			}
		} else {
			echo json_encode(array('e4j.error' => 'invalid channel id ('.(int)$channel['uniquekey'].')'));
			exit;
		}
		
		$rows = $dbo->loadAssocList();
		for ($i = 0; $i < count($rows); $i++) {
			$tac_rooms[$i] = $rows[$i]['id'];
		}
		
		$avail_rooms = array();

		$q = "SELECT `id`, `units` FROM `#__vikbooking_rooms` WHERE `avail`=1 AND `id` IN (".implode(',', $tac_rooms).");";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 0) {
			echo json_encode(array('e4j.error' => 'The Query for fetching the rooms returned an empty result'));
			exit;
		}

		$avail_rooms = $dbo->loadAssocList();
		
		require_once (VBO_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "lib.vikbooking.php");
		
		$room_ids = array();
		for ($i = 0; $i < count($avail_rooms); $i++) {
			$room_ids[$i] = $avail_rooms[$i]['id'];
		}
		
		$all_restrictions = VikBooking::loadRestrictions(true, $room_ids);
		$glob_restrictions = VikBooking::globalRestrictions($all_restrictions);
		
		if (count($glob_restrictions) > 0 && strlen(VikBooking::validateRoomRestriction($glob_restrictions, getdate($args['start_ts']), getdate($args['end_ts']), $args['nights'])) > 0) {
			echo json_encode(array('e4j.error' => 'Unable to proceed because of booking Restrictions in these dates'));
			exit;
		}
		
		//Get Rates
		$room_ids = array();
		foreach ($avail_rooms as $k => $room) {
			$room_ids[$room['id']] = $room;
		}
		$rates = array();
		$base_nights = 0;
		$q = "SELECT `days` FROM `#__vikbooking_dispcost` ORDER BY `days` ASC LIMIT 1;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$base_nights = $dbo->loadResult();
		}
		$q = "SELECT `p`.*, `r`.`id` AS `r_reference_id`, `r`.`name` AS `r_short_desc`, `r`.`img`, `r`.`units`, `r`.`moreimgs`, `r`.`imgcaptions`, `r`.`toadult`, `r`.`tochild`, `prices`.`id` AS `price_reference_id`, `prices`.`name` AS `pricename`, `prices`.`breakfast_included`, `prices`.`free_cancellation`, `prices`.`canc_deadline` FROM `#__vikbooking_dispcost` AS `p`, `#__vikbooking_rooms` AS `r`, `#__vikbooking_prices` AS `prices` WHERE `r`.`id`=`p`.`idroom` AND `p`.`idprice`=`prices`.`id` AND `p`.`days`=".(int)$base_nights." AND `r`.`id` IN (".implode(',', array_keys($room_ids)).") ORDER BY `p`.`cost` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 0) {
			echo json_encode(array('e4j.error' => 'The Query for fetching the rates returned an empty result'));
			exit;
		}
		$rates = $dbo->loadAssocList();
		if (method_exists('VikBooking', 'getTranslator')) {
			$vbo_tn = VikBooking::getTranslator();
			$vbo_tn->translateContents($rates, '#__vikbooking_rooms', array('id' => 'r_reference_id', 'r_short_desc' => 'name'));
			$vbo_tn->translateContents($rates, '#__vikbooking_prices', array('id' => 'price_reference_id', 'pricename' => 'name'));
		}
		//May 2016 API 7 TripConnect - Check-in/Check-out times
		$check_in_out = VikBooking::getTimeOpenStore();
		$opent = VikBooking::getHoursMinutes($check_in_out[0]);
		$closet = VikBooking::getHoursMinutes($check_in_out[1]);
		$hcheckin = intval($opent[0]) < 10 ? '0'.$opent[0] : $opent[0];
		$mcheckin = intval($opent[1]) < 10 ? '0'.$opent[1] : $opent[1];
		$hcheckout = intval($closet[0]) < 10 ? '0'.$closet[0] : $closet[0];
		$mcheckout = intval($closet[1]) < 10 ? '0'.$closet[1] : $closet[1];
		foreach ($rates as $keyr => $valuer) {
			$valuer['checkin_time'] = $hcheckin.':'.$mcheckin;
			$valuer['checkout_time'] = $hcheckout.':'.$mcheckout;
			$rates[$keyr] = $valuer;
		}
		//
		$arr_rates = array();
		foreach ($rates as $rate) {
			$arr_rates[$rate['idroom']][] = $rate;
		}
		
		//sort results by price ASC
		$arr_rates = VikBooking::sortResults($arr_rates);
		//
		
		return $arr_rates;

	}
	
	/**
	 * Orders retrieve listener.
	 * This method can also be called internally by another task to
	 * return all bookings and build ICS files for export.
	 * 
	 * @param 	bool 	$return 	return or output the bookings.
	 * 
	 * @since 						1.8.1 introduced $return argument.
	 * 								shared calendars occupying rooms are taken into account.
	 */
	public function orders_rv_l($return = false)
	{
		$response = 'e4j.error';
		
		$args = array();
		$args['hash'] = VikRequest::getString('e4jauth', '', 'request');
		
		$api_key = VikChannelManager::getApiKey();
		
		if ($args['hash'] != md5($api_key)) {
			if ($return) {
				return false;
			}
			echo $response.'.HashMismatch';
			die;
		}
		
		$dbo = JFactory::getDbo();
		
		$args['ids'] = VikRequest::getVar('ids', array());
		$args['filter'] = VikRequest::getVar('channel_filter', '');
		$args['checkout'] = explode('-', VikRequest::getVar('checkout'));
		$args['id_room'] = VikRequest::getInt('id_room', 0);
		/**
		 * A command for some iCal channels to only include the nights
		 * where a specific room type is fully booked.
		 * 
		 * @since 	1.6.21
		 */
		$args['only_full_nights'] = VikRequest::getInt('only_full_nights', 0);
		/**
		 * Fetch just the bookings for the requested sub-unit
		 * 
		 * @since 	1.7.0
		 */
		$args['sub_unit'] = VikRequest::getInt('subunit', 0);
		
		$now00 = mktime(0, 0, 0, date("n"), date("j"), date("Y"));
		$where_claus = '';
		
		if (!empty($args['filter'])) {
			$where_claus .= '`o`.`channel`='.$dbo->quote($args['filter']);
		}
		
		if (!empty($args['ids'])) {
			if (!empty($where_claus)) {
				$where_claus .= ' AND ';
			}
			$where_claus .= '`o`.`id` IN ('.implode(',', $args['ids']).')';
		}
		
		if (count($args['checkout']) == 3) {
			$ts = mktime(0, 0, 0, $args['checkout'][1], $args['checkout'][2], $args['checkout'][0]);
			
			if (!empty($where_claus)) {
				$where_claus .= ' AND ';
			}
			$where_claus .= $ts.'<`o`.`checkout` AND `o`.`checkout`<'.($ts+86399);
		} else {
			//ical to return only the future bookings
			$now00 = mktime(0, 0, 0, date("n"), date("j"), date("Y"));
			if (!empty($where_claus)) {
				$where_claus .= ' AND ';
			}
			$where_claus .= '`o`.`checkout`>='.$now00;
		}

		if (!empty($args['id_room'])) {
			if (!empty($where_claus)) {
				$where_claus .= ' AND ';
			}
			$where_claus .= '`or`.`idroom`='.$args['id_room'];
		}

		if (!empty($where_claus)) {
			$where_claus = 'AND '.$where_claus;
		}
		
		$orders = array();

		if ($args['only_full_nights'] > 0 && $args['id_room'] > 0) {
			/**
			 * The calendar should include only the nights
			 * where a specific room is fully booked.
			 * 
			 * @since 	1.6.21
			 */
			$fully_booked_ts = array();
			$fully_booked_id = array();
			$q = "SELECT `b`.`id`,`b`.`idroom`,`b`.`checkin`,`b`.`checkout`,`r`.`units` FROM `#__vikbooking_busy` AS `b` LEFT JOIN `#__vikbooking_rooms` AS `r` ON `b`.`idroom`=`r`.`id` WHERE `b`.`idroom`=" . $args['id_room'] . " AND `b`.`checkout` >= " . $now00 . " ORDER BY `b`.`id` DESC;";
			$dbo->setQuery($q);
			$busy = $dbo->loadAssocList();
			if (!$busy) {
				if ($return) {
					return $orders;
				}
				echo json_encode($orders);
				exit;
			}
			$max_room_units = $busy[0]['units'];
			$q = "SELECT MIN(`checkin`) AS `mincheckin`, MAX(`checkout`) AS `maxcheckout` FROM `#__vikbooking_busy` WHERE `idroom`=" . $args['id_room'] . " AND `checkout` >= " . $now00 . ";";
			$dbo->setQuery($q);
			$minmax = $dbo->loadAssoc();
			$start = getdate($minmax['mincheckin']);
			$groupdays = array();
			while ($start[0] < $minmax['maxcheckout']) {
				array_push($groupdays, $start[0]);
				$start = getdate(mktime($start['hours'], $start['minutes'], $start['seconds'], $start['mon'], ($start['mday'] + 1), $start['year']));
			}
			// loop over the booked dates
			foreach ($groupdays as $gday) {
				$bfound  = 0;
				$lastbid = 0;
				foreach ($busy as $bu) {
					if ($gday >= $bu['checkin'] && $gday <= $bu['checkout']) {
						$bfound++;
						$lastbid = $bu['id'];
					}
				}
				if ($bfound >= $max_room_units) {
					// push fully booked timestamp
					array_push($fully_booked_ts, $gday);
					// look for the last booking ID just for compliance with the other format
					$q = "SELECT `idorder` FROM `#__vikbooking_ordersbusy` WHERE `idbusy`={$lastbid};";
					$dbo->setQuery($q);
					$last_res_id = $dbo->loadResult();
					if ($last_res_id) {
						$fully_booked_id[$gday] = $last_res_id;
					}
				}
			}
			if (!count($fully_booked_ts)) {
				// no fully booked nights found
				if ($return) {
					return $orders;
				}
				echo json_encode($orders);
				exit;
			}
			// we set one booking for every fully booked night, so one event will always last 1 night
			foreach ($fully_booked_ts as $full_ts) {
				$infoin = getdate($full_ts);
				$out_ts = mktime(($infoin['hours'] > 1 ? ($infoin['hours'] - 1) : $infoin['hours']), $infoin['minutes'], $infoin['seconds'], $infoin['mon'], ($infoin['mday'] + 1), $infoin['year']);
				$reserved = array(
					'id' => (isset($fully_booked_id[$full_ts]) ? $fully_booked_id[$full_ts] : 0),
					'checkin' => $full_ts,
					'checkout' => $out_ts,
					'checkin_date' => date('Y-m-d', $full_ts),
					'checkout_date' => date('Y-m-d', $out_ts),
				);
				array_push($orders, $reserved);
			}
			
			// output fully booked nights as an array of orders
			if ($return) {
				return $orders;
			}
			echo json_encode($orders);
			exit;
		}

		if ($args['id_room'] > 0 && $args['sub_unit'] > 0) {
			/**
			 * It is now possible to request the availability calendar
			 * for the individual sub-units of each room.
			 * 
			 * @since 	1.7.0
			 */
			$q = "SELECT `id`, `name`, `units`, `params` FROM `#__vikbooking_rooms` WHERE `id`={$args['id_room']};";
			$dbo->setQuery($q);
			$room_info = $dbo->loadAssoc();
			if (!$room_info) {
				// room not found, exit
				if ($return) {
					return false;
				}
				echo json_encode(array());
				die;
			}
			if ($args['sub_unit'] > $room_info['units']) {
				// not existing room index, exit
				if ($return) {
					return false;
				}
				echo json_encode(array());
				die;
			}
			
			// get all bookings for this room
			$q = "SELECT `o`.*, `or`.`idroom`, `or`.`adults`, `or`.`children`, `or`.`t_first_name`, `or`.`t_last_name`, `or`.`roomindex`, `r`.`name`, `r`.`units` 
			FROM `#__vikbooking_orders` AS `o` LEFT JOIN `#__vikbooking_ordersrooms` AS `or` ON `o`.`id`=`or`.`idorder` LEFT JOIN `#__vikbooking_rooms` AS `r` ON 
			`or`.`idroom`=`r`.`id` WHERE `o`.`status`='confirmed' ".$where_claus." ORDER BY `o`.`id` ASC;";
			$dbo->setQuery($q);
			$rows = $dbo->loadAssocList();
			if (!$rows) {
				// no nookings found, exit
				if ($return) {
					return array();
				}
				echo json_encode(array());
				die;
			}
			// group rooms booked by booking ID
			$grouped = array();
			foreach ($rows as $k => $r) {
				if (!isset($grouped[$r['id']])) {
					$grouped[$r['id']] = array();
				}
				$r['checkin_date'] = date('Y-m-d', $r['checkin']);
				$r['checkout_date'] = date('Y-m-d', $r['checkout']);
				array_push($grouped[$r['id']], $r);
			}
			// parse the grouped rooms booked
			foreach ($grouped as $bid => $bookings) {
				// seek for the exact room-index first
				foreach ($bookings as $booking) {
					// closures should always be included, even at sub-unit level
					if (!empty($booking['closure'])) {
						// push booking and break the first loop
						array_push($orders, $booking);
						break;
					}
					if (!empty($booking['roomindex']) && (int)$booking['roomindex'] == $args['sub_unit']) {
						// booking found for this exact sub-unit
						array_push($orders, $booking);
						// break only the first loop and go to the next booking
						break;
					}
				}
				// grab the reservation index for this sub-unit, if available
				if (count($bookings) >= $args['sub_unit']) {
					if (empty($bookings[($args['sub_unit'] - 1)]['roomindex'])) {
						// push the booking found for the index of this sub-unit since it's not assigned to any room-index
						array_push($orders, $bookings[($args['sub_unit'] - 1)]);
					} else {
						// this index is occupied, find the first non-occupied index
						$free_index_found = false;
						foreach ($bookings as $kk => $booking) {
							if (empty($booking['roomindex'])) {
								// free room-index found, push record and exit
								$free_index_found = $kk;
								array_push($orders, $booking);
								break;
							}
						}
						if ($free_index_found === false) {
							// all indexes are occupied, and so we do not push any bookings for this sub-unit because it's free
						}
					}
				}
			}

			// output all bookings found for this room
			if ($return) {
				return $orders;
			}
			echo json_encode($orders);
			die;
		}
		
		$q = "SELECT `o`.*, `or`.`idroom`, `or`.`adults`, `or`.`children`, `or`.`t_first_name`, `or`.`t_last_name`, `or`.`roomindex`, `r`.`name`, `r`.`units` 
		FROM `#__vikbooking_orders` AS `o`, `#__vikbooking_ordersrooms` AS `or`, `#__vikbooking_rooms` AS `r` 
		WHERE `o`.`id`=`or`.`idorder` AND `or`.`idroom`=`r`.`id` AND `o`.`status`='confirmed' ".$where_claus.";";
		
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$rows = $dbo->loadAssocList();
			foreach ($rows as $r) {
				$r['checkin_date'] = date('Y-m-d', $r['checkin']);
				$r['checkout_date'] = date('Y-m-d', $r['checkout']);
				$orders[$r['id']] = $r;
			}
		}

		/**
		 * Merge all bookings that are occupying this room due to a shared calendar.
		 * 
		 * @since 	1.8.1
		 */
		try {
			$q = "SELECT `b`.`id` AS `id_busy`, `b`.`sharedcal`, `ob`.`idorder`, `o`.*, `r`.`name`, `r`.`units` 
				FROM `#__vikbooking_busy` AS `b` 
				LEFT JOIN `#__vikbooking_ordersbusy` AS `ob` ON `b`.`id`=`ob`.`idbusy` 
				LEFT JOIN `#__vikbooking_orders` AS `o` ON `ob`.`idorder`=`o`.`id` 
				LEFT JOIN `#__vikbooking_rooms` AS `r` ON `b`.`idroom`=`r`.`id` 
				WHERE `b`.`idroom`={$args['id_room']} AND `b`.`sharedcal`=1 AND `b`.`checkout`>={$now00} AND `o`.`status`='confirmed';";

			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$shared_busy = $dbo->loadAssocList();
				foreach ($shared_busy as $r) {
					if (isset($orders[$r['id']])) {
						continue;
					}
					$r['checkin_date'] = date('Y-m-d', $r['checkin']);
					$r['checkout_date'] = date('Y-m-d', $r['checkout']);
					$orders[$r['id']] = $r;
				}
			}
		} catch (Exception $e) {
			// do nothing as VBO may be outdated
		}
		
		// output all bookings found
		if ($return) {
			return $orders;
		}

		if (!headers_sent()) {
			header('Content-Type: application/json; charset=utf-8');
		}

		echo json_encode($orders);
		die;
	}

	/**
	 * Stats listener
	 */
	public function retrieve_stats_l()
	{
		$response = 'e4j.error';
		
		$args = array();
		$args['hash'] = VikRequest::getString('e4jauth', '', 'request');
		
		$api_key = VikChannelManager::getApiKey();
		
		if ($args['hash'] != md5($api_key)) {
			echo $response.'.HashMismatch';
			die;
		}
		
		$args['channel'] = VikRequest::getVar('channel', -1);
		
		$dbo = JFactory::getDbo();
		
		$stats = array();
		$q = "SELECT * FROM `#__vikchannelmanager_call_stats` WHERE `channel`=".$dbo->quote($args['channel'])." OR ".$dbo->quote($args['channel'])."='-1' ORDER BY `channel` ASC, `call` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$stats = $dbo->loadAssocList();
		}
		
		echo json_encode($stats);
		
		die;
	}

	/**
	 * Input Output Diagnostic
	 */
	public function iod_rq()
	{
		$args = array();
		$args['hash'] = VikRequest::getString('e4jauth', '', 'request');
		$args['token'] = VikRequest::getString('token', '', 'request');
		$args['bookings'] = json_decode(VikRequest::getString('bookings', '', 'request', VIKREQUEST_ALLOWRAW), true);

		if (function_exists('json_last_error')) {
			$decode_err = json_last_error();
			switch ($decode_err) {
				case JSON_ERROR_NONE:
					break;
				case JSON_ERROR_DEPTH:
					echo 'e4j.error.Curl.Broken:Maximum stack depth exceeded '.strlen($_REQUEST['bookings']);
					exit;
				case JSON_ERROR_STATE_MISMATCH:
					echo 'e4j.error.Curl.Broken:Underflow or the modes mismatch '.str_replace(':', '-', str_replace('.', ' ', $_REQUEST['bookings']));
					exit;
				case JSON_ERROR_CTRL_CHAR:
					echo 'e4j.error.Curl.Broken:Unexpected control character found '.str_replace(':', '-', str_replace('.', ' ', $_REQUEST['bookings']));
					exit;
				case JSON_ERROR_SYNTAX:
					echo 'e4j.error.Curl.Broken:Syntax error or malformed JSON <br/>'.str_replace(':', '-', str_replace('.', ' ', $_REQUEST['bookings']));
					exit;
				case JSON_ERROR_UTF8:
					echo 'e4j.error.Curl.Broken:Malformed UTF-8 characters and possibly incorrectly encoded <br/>'.str_replace(':', '-', str_replace('.', ' ', $_REQUEST['bookings']));
					exit;
				default:
					echo 'e4j.error.Curl.Broken:Unknown Decoding Error <br/>'.str_replace(':', '-', str_replace('.', ' ', $_REQUEST['bookings']));
					exit;
			}
		} else {
			if (!is_array($args['bookings'])) {
				echo 'e4j.error.Curl.Broken:Cannot Detect Decoding Error <br/>'.str_replace(':', '-', str_replace('.', ' ', $_REQUEST['bookings']));
				exit;
			}
		}

		if (!$args['bookings']) {
			// not decoded, probably because of empty 'bookings' value
			echo 'e4j.error.Curl.Broken:Empty dummy booking';
			exit;
		}
		
		$api_key = VikChannelManager::getApiKey();
		
		if ($args['hash'] != md5($api_key)) {
			echo 'e4j.error.Authentication';
			exit;
		}

		$filename = VCM_SITE_PATH.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.$args['token'].".txt";

		if (!file_exists($filename)) {
			echo 'e4j.error.File.NotFound';
			exit;
		}

		// the salt is hashed twice
		$cipher = VikChannelManager::loadCypherFramework(md5($api_key));

		// PARSE ORDER
		$order_str = "";
		foreach ($args['bookings']['orders'] as $o) {
			if (!empty($order_str)) {
				$order_str .= "--------------------------------------------------<br />";
			}

			foreach ($o as $section => $arr) {

				$order_str .= "### ".ucwords(str_replace("_", " ", $section))." ###<br />";

				foreach ($arr as $k => $v) {
					if (is_array($v)) {
						$v = implode(", ", $v);
					}

					if ($k == "credit_card") {
						$v = $cipher->decrypt($v);
					}

					$order_str .= ucwords(str_replace("_", " ", $k)).": ".$v."<br />";
				}

			}
		}
		//////////////

		$handle = fopen($filename, "w");
		$bytes = fwrite($handle, $order_str);
		fclose($handle);

		if ($bytes == 0) {
			echo 'e4j.error.File.Permissions.Write';
			exit;
		}

		echo "e4j.ok";
		exit;
	}

	/**
	 * Update listener
	 */
	public function update_l()
	{
		$response = 'e4j.error';
		
		$args = array();
		$args['hash'] = VikRequest::getString('e4jauth', '', 'request');
		
		$api_key = VikChannelManager::getApiKey();
		
		if ($args['hash'] != md5($api_key)) {
			echo $response.'.HashMismatch';
			die;
		}
		
		$args['latest_version'] = VikRequest::getVar('latest_version');
		$args['required'] = VikRequest::getInt('required');
		$args['install'] = VikRequest::getInt('install');
		$args['message'] = VikRequest::getString('message', '', 'request', VIKREQUEST_ALLOWHTML);

		if ($args['install'] && version_compare(PHP_VERSION, '7.4', '<')) {
			unset($args['install']);
			$args['required'] = true;
			$args['message'] = ($args['message'] ? "\n\n" : '') . 'It was not possible to run the update because of an old an incompatible PHP version. Please upgrade at least to PHP 7.4 as soon as possible to continue using VikChannelManager.';
		}
		
		$dbo = JFactory::getDbo();
		
		if (version_compare($args['latest_version'], VIKCHANNELMANAGER_SOFTWARE_VERSION, '>')) {
			$config = VCMFactory::getConfig();

			$config->set('to_update', 1);
			$config->set('block_program', (int) $args['required']);
			
			if ($args['install']) {
				try {
					// obtain the update data information
					$update_data = VCMUpdateHandler::retrieve_update_data($validate = true);

					// process update data
					VCMUpdateHandler::process_update_data($update_data);

					$args['updated'] = true;
				} catch (Exception $e) {
					$args['updated'] = false;
					$args['error'] = $e->getMessage();
				}
			}

			$adminMail = VikChannelManager::getAdminMail();

			VikBooking::getVboApplication()->sendMail(
				$adminMail,
				'e4jConnect',
				$adminMail,
				'no-reply@e4jconnect.com',
				'VikChannelManager - New Version',
				VikChannelManager::getNewVersionMailContent($args)
			);

			echo 0; // NOT UP-TO-DATE
		} else {
			echo 1; // UP-TO-DATE
		}
		
		die;
	}

	/**
	 * This task returns a JSON object holding all the following information:
	 * 
	 * - vcm
	 * - vbo (1.9.12+)
	 * - php (1.9.12+)
	 * - cms
	 * 
	 * @since 1.8.24  Hash validation added.
	 * @since 1.9.12  The response now uses an application/json content-type.
	 */
	public function vcmv()
	{
		if (VikRequest::getString('e4jauth', '', 'request') != md5(VikChannelManager::getApiKey())) {
			VCMHttpDocument::getInstance()->close(403, 'Hash mismatch');
		}

		if (!class_exists('VikBooking')) {
			@include_once VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'lib.vikbooking.php';
		}

		$versions = [
			'vcm' => VIKCHANNELMANAGER_SOFTWARE_VERSION,
			'vbo' => (defined('VIKBOOKING_SOFTWARE_VERSION') ? VIKBOOKING_SOFTWARE_VERSION : '/'),
			'php' => PHP_VERSION,
			'cms' => '/',
		];

		jimport('joomla.version');
		if (class_exists('JVersion')) {
			$versions['cms'] = (new JVersion)->getShortVersion();
		}

		VCMHttpDocument::getInstance()->json($versions);
	}

	/**
	 * Smart Balancer listener task for
	 * executing rules of type 'rt' and
	 * to update the Bookings Statistics.
	 */
	public function smartbalancer_ratesrules()
	{
		$response = 'e4j.error';
		
		$id = VikRequest::getVar('cid', array(0));
		$args = array(
			'hash' 			=> VikRequest::getString('e4jauth', '', 'request'),
			'id_rule' 		=> (isset($id[0]) && intval($id[0]) > 0 ? (int)$id[0] : 0),
			'closeconn' 	=> VikRequest::getInt('closeconn'),
			'debug_mode' 	=> VikRequest::getInt('e4j_debug')
		);
		
		$api_key = VikChannelManager::getApiKey();
		
		if ($args['hash'] != md5($api_key)) {
			echo $response.'.HashMismatch';
			die;
		}

		if ($args['closeconn'] > 0) {
			ob_end_clean();
			header("Connection: close");
			ignore_user_abort(true);
			ob_start();
			echo 'e4j.ok.closeconn';
			$size = ob_get_length();
			header("Content-Length: $size");
			ob_end_flush();
			flush();
		}

		$smartbal = VikChannelManager::getSmartBalancerInstance();
		if ($args['debug_mode'] == -1) {
			$smartbal->debug_output = true;
		}
		$res = $smartbal->applyRatesAdjustmentsRules($args['id_rule']);
		//update bookings statistics for this rule or for all if none passed
		$smartbal->countRatesModBookings($args['id_rule']);

		echo 'e4j.ok.'.(int)$res;
		exit;
	}

	/**
	 * Pro_Level listener
	 */
	public function pro_level_l()
	{
		$response = 'e4j.error';
		
		$args = array();
		$args['hash'] = VikRequest::getString('e4jauth', '', 'request');
		
		$api_key = VikChannelManager::getApiKey();
		
		if ($args['hash'] != md5($api_key)) {
			echo $response.'.HashMismatch';
			die;
		}
		
		$args['level'] = VikRequest::getInt('level');
		
		$dbo = JFactory::getDbo();
		
		$q = "UPDATE `#__vikchannelmanager_config` SET `setting`='".$args['level']."' WHERE `param`='pro_level' LIMIT 1;";
		$dbo->setQuery($q);
		$dbo->execute();
		
		echo 'e4j.ok.'.VikChannelManager::getProLevel();
		die;
	}

	/**
	 * Task used to communicate the rooms mapping data to
	 * the central servers in case of necessary updates.
	 * 
	 * @since 	1.6.11
	 */
	public function rooms_mapping_data()
	{
		$response = 'e4j.error';
		
		$args = array();
		$args['hash'] = VikRequest::getString('e4jauth', '', 'request');
		
		$api_key = VikChannelManager::getApiKey();
		
		if ($args['hash'] != md5($api_key)) {
			echo $response.'.HashMismatch';
			die;
		}
		
		$dbo = JFactory::getDbo();
		$mapping = array();

		$q = "SELECT `rel`.`idroomvb`,`rel`.`idroomota`,`rel`.`idchannel`,`rel`.`channel`,`rel`.`otaroomname`,`rel`.`prop_name`,`vbr`.`name` AS `room_vb_name`,`vbr`.`units` AS `room_vb_units` FROM `#__vikchannelmanager_roomsxref` AS `rel` LEFT JOIN `#__vikbooking_rooms` AS `vbr` ON `rel`.`idroomvb`=`vbr`.`id` ORDER BY `rel`.`channel` ASC, `vbr`.`units` DESC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$mapping = $dbo->loadAssocList();
		}

		/**
		 * We merge the iCal URLs into the mapping array
		 * 
		 * @since 	1.6.12
		 */
		$q = "SELECT `l`.`retrieval_url`,`l`.`id_vb_room` AS `idroomvb`,`l`.`channel` AS `idchannel`,`c`.`name` AS `channel`,`r`.`name` AS `room_vb_name`,`r`.`units` AS `room_vb_units` 
			FROM `#__vikchannelmanager_listings` AS `l` 
			LEFT JOIN `#__vikbooking_rooms` AS `r` ON `r`.`id`=`l`.`id_vb_room` 
			LEFT JOIN `#__vikchannelmanager_channel` AS `c` ON `c`.`uniquekey`=`l`.`channel` 
			ORDER BY `l`.`id_vb_room` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$icals = $dbo->loadAssocList();
			$mapping = array_merge($mapping, $icals);
		}

		/**
		 * We append also any room that is eventually not mapped with any OTAs.
		 * 
		 * @since 	1.7.5
		 */
		$roomids = array();
		foreach ($mapping as $map) {
			if (!in_array($map['idroomvb'], $roomids) && !empty($map['room_vb_units'])) {
				array_push($roomids, $map['idroomvb']);
			}
		}
		if (count($roomids)) {
			// find any other room ID on VBO
			$q = "SELECT `r`.`id` AS `idroomvb`, `r`.`name` AS `room_vb_name`, `r`.`avail`, `r`.`units` AS `room_vb_units` FROM `#__vikbooking_rooms` AS `r` WHERE `id` NOT IN (" . implode(', ', $roomids) . ");";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows()) {
				$vboexclusive = $dbo->loadAssocList();
				$mapping = array_merge($mapping, $vboexclusive);
			}
		}

		if (!headers_sent()) {
			header('Content-Type: application/json; charset=utf-8');
		}

		// output the JSON response
		echo json_encode($mapping);
		
		die;
	}

	/**
	 * App Authentication Data listener
	 */
	public function app_auth_l()
	{
		$response = 'e4j.error';
		
		$args = array();
		$args['hash'] = VikRequest::getString('e4jauth', '', 'request');
		$args['email'] = VikRequest::getString('email', '', 'request');
		$args['pwd'] = VikRequest::getString('pwd', '', 'request');
		
		$api_key = VikChannelManager::getApiKey();
		
		if ($args['hash'] != md5($api_key)) {
			echo $response.'.HashMismatch';
			die;
		}
		if (empty($args['email'])) {
			echo $response.'.MissingData1';
			die;
		}
		if (empty($args['pwd'])) {
			echo $response.'.MissingData2';
			die;
		}
		
		$args['email'] = trim(strtolower($args['email']));
		
		$dbo = JFactory::getDbo();

		$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param`='app_accounts' LIMIT 1;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$app_accounts = $dbo->loadResult();
			$app_accounts = json_decode($app_accounts, true);
			$app_accounts[$args['email']] = $args['pwd'];
			$q = "UPDATE `#__vikchannelmanager_config` SET `setting`=".$dbo->quote(json_encode($app_accounts))." WHERE `param`='app_accounts';";
			$dbo->setQuery($q);
			$dbo->execute();
		} else {
			$app_accounts = array($args['email'] => $args['pwd']);
			$q = "INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES ('app_accounts', ".$dbo->quote(json_encode($app_accounts)).");";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		
		//INSERIMENTO IN ACL
		$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param` = 'app_acl';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$aclAccounts = $dbo->loadAssoc();
			$aclAccounts = json_decode($aclAccounts['setting'], true);
			if (empty($aclAccounts)) {
				$aclAccounts[$args['email']] = VikChannelManager::getDefaultJoomlaUserGroup(true);
			} else { 
				$aclAccounts[$args['email']] = VikChannelManager::getDefaultJoomlaUserGroup();
			}
			$q = "UPDATE `#__vikchannelmanager_config` SET `setting`=".$dbo->quote(json_encode($aclAccounts))." WHERE `param`='app_acl';";
			$dbo->setQuery($q);
			$dbo->execute();
		} else {
			$aclAccounts = array($args['email'] => VikChannelManager::getDefaultJoomlaUserGroup(true));
			$q = "INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES ('app_acl', ".$dbo->quote(json_encode($aclAccounts)).");";
			$dbo->setQuery($q);
			$dbo->execute();
		}

		echo 'e4j.ok.'.$args['email'];
		die;
	}

	/**
	 * App Rectify Channel
	 */
	public function app_rectify()
	{
		$response = 'e4j.error';
		
		$args = array();
		$args['hash'] = VikRequest::getString('e4jauth', '', 'request');
		
		$api_key = VikChannelManager::getApiKey();
		
		if ($args['hash'] != md5($api_key)) {
			echo $response.'.HashMismatch';
			die;
		}
		
		$dbo = JFactory::getDbo();
		$rectify = array(0, 0);

		$q = "SELECT `id` FROM `#__vikchannelmanager_channel` WHERE `uniquekey`=".(int)VikChannelManagerConfig::MOBILEAPP." LIMIT 1;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$rectify[0] = $dbo->loadResult();
			$q = "DELETE FROM `#__vikchannelmanager_channel` WHERE `uniquekey`=".(int)VikChannelManagerConfig::MOBILEAPP.";";
			$dbo->setQuery($q);
			$dbo->execute();
			$rectify[1] = 1;
		}
		
		echo 'e4j.ok.'.implode('.', $rectify);
		die;
	}

	/**
	 * App Restore
	 */
	public function app_restore()
	{
		$response = 'e4j.error';
		
		$args = array();
		$args['hash'] = VikRequest::getString('e4jauth', '', 'request');
		
		$api_key = VikChannelManager::getApiKey();
		
		if ($args['hash'] != md5($api_key)) {
			echo $response.'.HashMismatch';
			die;
		}
		
		$dbo = JFactory::getDbo();

		$q = "DELETE FROM `#__vikchannelmanager_config` WHERE `param` = 'app_accounts';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "DELETE FROM `#__vikchannelmanager_config` WHERE `param` = 'app_acl';";
		$dbo->setQuery($q);
		$dbo->execute();
		
		echo 'e4j.ok';
		die;
	}

	/**
	 * Notifications of type Information
	 */
	public function vcm_notify_info()
	{
		$response = 'e4j.error';
		
		$args = array();
		$args['hash'] = VikRequest::getString('e4jauth', '', 'request');
		$args['cont'] = VikRequest::getString('cont', '', 'request', VIKREQUEST_ALLOWHTML);
		$args['sendemail'] = VikRequest::getInt('sendemail', 0, 'request');
		$args['mailsubject'] = VikRequest::getString('mailsubject', '', 'request');
		
		$api_key = VikChannelManager::getApiKey();
		
		if ($args['hash'] != md5($api_key)) {
			echo $response.'.HashMismatch';
			die;
		}
		if (empty($args['cont'])) {
			echo $response.'.Empty';
			die;
		}
		
		$dbo = JFactory::getDbo();
		$stored = 0;
		$now_info = getdate();
		$limit_ts = mktime(0, 0, 0, $now_info['mon'], $now_info['mday'], $now_info['year']);

		$q = "SELECT `id` FROM `#__vikchannelmanager_notifications` WHERE `from`='e4j' AND `ts`>=".$limit_ts.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() < 2) {
			$q = "INSERT INTO `#__vikchannelmanager_notifications` (`ts`,`type`,`from`,`cont`,`idordervb`,`read`) VALUES (".time().", 1, 'e4j', ".$dbo->quote(str_replace(':', '', JText::_('VCMAPPNOTIFINFO')).":\n".$args['cont']).", 0, 0);";
			$dbo->setQuery($q);
			$dbo->execute();
			$stored = (int)$dbo->insertId();
		}
		if ($stored > 0) {
			$response = 'e4j.ok';
			/**
			 * Check whether the notification should be sent via email
			 *
			 * @since 	1.6.10
			 */
			if ($args['sendemail']) {
				if (empty($args['mailsubject'])) {
					$args['mailsubject'] = JText::_('VCMAPPNOTIFINFO');
				}
				$vik = new VikApplication(VersionListener::getID());
				$vik->sendMail(
					'info@e4jconnect.com', 
					'e4jConnect', 
					VikChannelManager::getAdminMail(), 
					'no-reply@e4jconnect.com', 
					$args['mailsubject'], 
					$args['cont'],
					(strpos($args['cont'], '<') !== false)
				);
			}
			//
		}
		
		echo $response.'.'.$stored;
		die;
	}

	/**
	 * MR_L Messaging Retrieval Listener.
	 * New threads messages for the chat are sent to this task.
	 * 
	 * @uses 	VCMChatHandler
	 * 
	 * @since 	1.6.13
	 */
	public function mr_l()
	{
		$dbo = JFactory::getDbo();

		// JSON request content
		$input = JFactory::getApplication()->input->json;

		// request variables
		$pe4jauth = $input->get('e4jauth', '', 'raw');
		$pchannel = $input->getInt('channel', 0);
		$raw 	  = $input->getRaw();

		// validation
		if (empty($pe4jauth) || empty($pchannel) || empty($raw)) {
			echo 'e4j.error.EmptyRequest';
			exit;
		}
		$apikey = VikChannelManager::getApiKey(true);
		$channel = VikChannelManager::getChannel($pchannel);
		if (!count($channel)) {
			echo 'e4j.error.NoChannel';
			exit;
		}
		$checkauth = md5($apikey);
		if ($checkauth != $pe4jauth) {
			echo 'e4j.error.Authentication';
			exit;
		}
		$req = json_decode($raw);
		if (!is_object($req) || !isset($req->threadmess)) {
			echo 'e4j.error.InvalidRequest';
			exit;
		}

		// require VCMChatHandler class file
		require_once VCM_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'chat' . DIRECTORY_SEPARATOR . 'handler.php';

		$response = new stdClass;
		$response->newThreads  = 0;
		$response->newMessages = 0;
		/**
		 * In order to comply with some OTAs that support Messaging APIs,
		 * we need to return the list of reservation IDs involved in VBO.
		 * This is because the PND needs the reservation ID of VBO, and
		 * with some channels, this endpoint is called for storing messages
		 * of just one thread and reservation ID.
		 */
		$response->vbo_res_ids = array();

		// loop through reservations threads messages pool
		foreach ($req->threadmess as $otaresid => $threadsmessages) {
			// find the VBO reservation ID from the given OTA reservation ID
			$q = "SELECT `id` FROM `#__vikbooking_orders` WHERE `idorderota`=".$dbo->quote($otaresid)." AND `channel` LIKE ".$dbo->quote('%'.$channel['name'].'%')." LIMIT 1;";
			$dbo->setQuery($q);
			$order = $dbo->loadAssoc();
			if (!$order) {
				continue;
			}

			// initialize chat handler for this reservation ID
			$chat = VCMChatHandler::getInstance($order['id'], $channel['name']);
			if (!$chat) {
				echo 'e4j.error.InvalidChatHandler: ' . $order['id'] . ' - ' . $channel['name'];
				exit;
			}

			// push VBO reservation ID
			if (!in_array($order['id'], $response->vbo_res_ids)) {
				array_push($response->vbo_res_ids, $order['id']);
			}

			foreach ($threadsmessages as $threadmess) {
				// compose thread object with the information to find it
				$check_thread = new stdClass;
				$check_thread->idorder = $order['id'];
				$check_thread->idorderota = $otaresid;
				$check_thread->channel = $channel['name'];
				$check_thread->ota_thread_id = $threadmess->thread->id;
				
				// find thread last_updated date from messages
				$most_recent_ts = 0;
				$most_recent_dt = '';
				foreach ($threadmess->messages as $message) {
					// update most recent date for the thread `last_updated`
					$mess_ts = strtotime($message->created_on);
					if ($most_recent_ts < $mess_ts) {
						$most_recent_ts = $mess_ts;
						$most_recent_dt = $message->created_on;
					}
				}
				
				// check whether this thread exists
				$cur_thread_id = $chat->threadExists($check_thread);
				if ($cur_thread_id !== false) {
					// set the ID property to later update the thread found
					$check_thread->id = $cur_thread_id;
				}

				// set last_updated value for thread and other properties returned
				$check_thread->subject = ucwords($threadmess->thread->topic);
				$check_thread->type = $threadmess->thread->type;
				$check_thread->last_updated = $most_recent_dt;

				// always attempt to create/update thread
				$vcm_thread_id = $chat->saveThread($check_thread);
				if ($vcm_thread_id === false) {
					// go to next thread
					$chat->setError('Could not store thread.');
					continue;
				}

				if ($cur_thread_id === false) {
					// a new thread was stored, increase counter
					$response->newThreads++;

					/**
					 * Whenever a new thread is created, in order to later reduce the timing to download the new
					 * messages from a thread, we ensure the reservation record contains the OTA thread ID.
					 * 
					 * @since 	1.8.27
					 */
					VCMChatMessaging::getInstance($check_thread)->storeBookingThread();
				}

				// parse all new messages from this thread
				foreach ($threadmess->messages as $message) {
					// compose message object to find it or save it
					$check_message = new stdClass;
					$check_message->idthread = $vcm_thread_id;
					$check_message->ota_message_id = $message->id;

					/**
					 * always save or update the message because when sending
					 * a new message or a reply to the guest, we may be missing
					 * information about the sender/recipient ID/type/name or payload.
					 */
					$cur_mess_id = $chat->messageExists($check_message);
					if ($cur_mess_id !== false) {
						// set the ID property for later updating the message
						$check_message->id = $cur_mess_id;
					}

					/**
					 * When it's a new message, make sure the sender details are set.
					 * 
					 * @since 	1.9.18
					 */
					if (!$cur_mess_id && empty($message->sender->type)) {
						// do not proceed or the message will be saved without a sender
						continue;
					}

					// set the rest of the properties to this message
					$check_message->in_reply_to = !empty($message->payload->in_reply_to) ? $message->payload->in_reply_to : null;
					$check_message->sender_id   = !empty($message->sender->id) ? $message->sender->id : null;
					$check_message->sender_name = !empty($message->sender->name) ? $message->sender->name : null;
					$check_message->sender_type = !empty($message->sender->type) ? $message->sender->type : null;
					$check_message->recip_id    = !empty($message->recipient->id) ? $message->recipient->id : null;
					$check_message->recip_name  = !empty($message->recipient->name) ? $message->recipient->name : null;
					$check_message->recip_type  = !empty($message->recipient->type) ? $message->recipient->type : null;
					$check_message->dt          = JDate::getInstance($message->created_on)->toSql();
					$check_message->content     = $message->text ?? null;
					if (!$cur_mess_id || $message->attachments) {
						// set attachments only in case of a new message, or if not empty
						$check_message->attachments = json_encode($message->attachments);
					}
					$check_message->payload = isset($message->payload) ? json_encode($message->payload) : null;

					/**
					 * Check if the OTA included the translated content for the message.
					 * 
					 * @since 	1.9.18
					 */
					if (empty($message->translation)) {
						$check_message->translation = $message->translation;
					}

					/**
					 * In case the message comes from a co-host, we ensure to store the
					 * co-host details and to get the ID to be assigned to the message.
					 * We do the same if the message comes from another chat user, like
					 * from a co-traveler or if it's an OTA system message.
					 * 
					 * @since 	1.8.22 added co-host support.
					 * @since 	1.9.18 added "other chat user" support and message avatar.
					 */
					$message_avatar = null;
					if (!empty($message->cohost)) {
						// store the co-host details (if new) and get the ID
						$cohost_id = $chat->parseCohostDetails($message->cohost);
						if ($cohost_id) {
							// assign the message to a co-host ID only if available, to prevent possible SQL errors
							$check_message->cohost_id = $cohost_id;
						}
					} elseif (!empty($message->other_chat_user)) {
						// store the chat user details (if new) and get the ID
						$chat_user_id = $chat->parseOtherChatUserDetails((object) $message->other_chat_user, (string) $threadmess->thread->id);
						if ($chat_user_id) {
							// assign the message to a chat-user ID only if available
							$check_message->user_id = $chat_user_id;
							if (!empty($message->other_chat_user->pic)) {
								// set the message "avatar" value for the notifications center
								$message_avatar = (string) $message->other_chat_user->pic;
							}
						}
					}

					/**
					 * Added support for message language (2-char lang code).
					 * 
					 * @since 	1.8.27
					 */
					if (!empty($message->lang)) {
						$check_message->lang = substr((string) $message->lang, 0, 2);
					}

					/**
					 * Detect if the guest message is actually a reaction.
					 * 
					 * @since 	1.9.0
					 * @since 	1.9.10 added support to new reaction structure.
					 */
					$is_reaction = null;
					if (($message->reaction ?? null)) {
						// find the quoted message with the reaction
						$seek_quoted = [
							'idthread' => $check_message->idthread,
						];
						if ($message->reaction->ota_message_id ?? null) {
							// we've got the quoted ota message ID
							$seek_quoted['ota_message_id'] = (string) $message->reaction->ota_message_id;
						} else {
							// fallback onto the quoted message content
							$seek_quoted['content'] = (string) $message->reaction->quoted;
						}
						$quoted_message = $chat->messageExists($seek_quoted);

						if ($quoted_message) {
							// this will be saved as a message reaction rather than as a guest message
							$is_reaction = $chat->saveReaction([
								'idthread'       => $check_message->idthread,
								'idmessage'      => $quoted_message,
								'ota_message_id' => $check_message->ota_message_id,
								'emoji'          => $message->reaction->emoji,
								'user'           => $check_message->sender_name,
								'iduser'         => $check_message->sender_id,
								'dt'             => $check_message->dt,
							]);
						}
					}

					if ($is_reaction) {
						// do not proceed with saving the guest message record when we got a reaction
						$response->newMessages++;

						/**
						 * Let VikBooking add an entry to the notifications center, if supported,
						 * and if the new message received was sent by the guest to the host/hotel.
						 */
						if (empty($check_message->cohost_id) && !strcasecmp($check_message->sender_type, 'guest') && method_exists('VBOFactory', 'getNotificationCenter')) {
							try {
								// clone message object
								$guest_message = clone $check_message;
								if ($message_avatar) {
									// set avatar property to message
									$guest_message->avatar = $message_avatar;
								}

								// parse the new guest message received
								VBOFactory::getNotificationCenter()
										->parseNewGuestMessage($check_thread, $guest_message);
							} catch (Throwable $e) {
								// silently catch the error and do nothing
							}
						}

						// do NOT proceed
						continue;
					}

					// store or update the message
					if ($chat->saveMessage($check_message) && $cur_mess_id === false) {
						// increase new messages counter
						$response->newMessages++;

						/**
						 * Schedule an auto-response in case of a new guest message in a conversation.
						 * There's no need to check whether the thread existed because the cron will
						 * ensure the thread has got no replies from the host.
						 * 
						 * @since 	1.8.21
						 */
						VCMChatAutoresponder::getInstance()->scheduleResponse($check_message->idthread, $message->created_on);

						/**
						 * Let VikBooking add an entry to the notifications center, if supported,
						 * and if the new message received was sent by the guest to the host/hotel.
						 * 
						 * @since 	1.8.24
						 */
						if (empty($check_message->cohost_id) && !strcasecmp($check_message->sender_type, 'guest') && method_exists('VBOFactory', 'getNotificationCenter')) {
							try {
								// clone message object
								$guest_message = clone $check_message;
								if ($message_avatar) {
									// set avatar property to message
									$guest_message->avatar = $message_avatar;
								}

								// parse the new guest message received
								VBOFactory::getNotificationCenter()
										->parseNewGuestMessage($check_thread, $guest_message);
							} catch (Throwable $e) {
								// silently catch the error and do nothing
							}
						}
					}
				}
			}
		}

		// send JSON response to output
		VCMHttpDocument::getInstance()->json($response);
	}

	/**
	 * e4jConnect App Execution
	 */
	public function app_exec()
	{
		require_once(VCM_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "app.php");

		(new AppE4jConnect())->processRequest();
	}

	/**
	 * Gathers a list of information according to the passed POST parameters
	 * to build financial reports and to either return or send them via email.
	 * Prints a JSON-encoded response with the list of bookings.
	 * 
	 * @return 	void
	 * 
	 * @since 	1.7.1
	 */
	public function get_reports_data()
	{
		$pe4jauth = VikRequest::getString('e4jauth', '', 'request', VIKREQUEST_ALLOWRAW);
		$apikey   = VikChannelManager::getApiKey(true);
		if (empty($pe4jauth) || $pe4jauth != md5($apikey)) {
			throw new Exception('Forbidden', 403);
		}

		// request filters
		$date_from  = VikRequest::getString('date_from', '', 'request');
		$date_to 	= VikRequest::getString('date_to', '', 'request');
		$date_type 	= VikRequest::getString('date_type', 'bookings', 'request');
		$psend 		= VikRequest::getBool('send', false, 'request');
		if (!in_array($date_type, array('arrivals', 'departures', 'stayovers', 'bookings'))) {
			throw new Exception('Invalid date type', 500);
		}

		$content = VikChannelManager::fetchReportsData($psend, $date_from, $date_to, $date_type);

		VCMHttpDocument::getInstance()->json($content);
	}

	/**
	 * Renders a JSON response with all the notifications and the related children.
	 * 
	 * @return 	void
	 * 
	 * @since 	1.6.18
	 */
	public function get_notifications_list()
	{
		$dbo 		= JFactory::getDbo();
		$pe4jauth 	= VikRequest::getString('auth', '', 'request', VIKREQUEST_ALLOWRAW);
		$psearch	= VikRequest::getString('search', '', 'request');
		$pdate		= VikRequest::getString('date', '', 'request');
		$poffset 	= VikRequest::getInt('offset', 0, 'request');
		$plimit 	= VikRequest::getInt('limit', 20, 'request');
		$apikey   	= VikChannelManager::getApiKey(true);
		if (empty($pe4jauth) || $pe4jauth != md5($apikey)) {
			throw new Exception('Forbidden', 403);
		}

		// load back-end language
		$lang = JFactory::getLanguage();
		if (VCMPlatformDetection::isWordPress()) {
			$lang->load('com_vikchannelmanager', VIKCHANNELMANAGER_ADMIN_LANG, $lang->getTag(), true);
		} else {
			$lang->load('com_vikchannelmanager', JPATH_ADMINISTRATOR, $lang->getTag(), true);
		}

		$notifications  = array();
		$clauses 		= array();
		$tot_records 	= 0;

		if (!empty($pdate)) {
			$dtinfo = getdate(strtotime($pdate));
			$mindt  = mktime(0, 0, 0, $dtinfo['mon'], $dtinfo['mday'], $dtinfo['year']);
			$maxdt  = mktime(23, 59, 59, $dtinfo['mon'], $dtinfo['mday'], $dtinfo['year']);
			array_push($clauses, "`n`.`ts` >= {$mindt}");
			array_push($clauses, "`n`.`ts` <= {$maxdt}");
		}

		if (!empty($psearch)) {
			if (is_numeric($psearch)) {
				array_push($clauses, "`n`.`idordervb` = " . (int)$psearch);
			} else {
				array_push($clauses, "`n`.`cont` LIKE " . $dbo->quote('%' . $psearch . '%'));
			}
		}

		// load parent notifications
		$q = "SELECT SQL_CALC_FOUND_ROWS `n`.* 
			FROM `#__vikchannelmanager_notifications` AS `n`" . (count($clauses) ? " WHERE " . implode(' AND ', $clauses) : "") . " ORDER BY `n`.`ts` DESC";
		$dbo->setQuery($q, $poffset, $plimit);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			$notifications = $dbo->loadAssocList();
			$dbo->setQuery('SELECT FOUND_ROWS();');
			$tot_records = $dbo->loadResult();
			$nparent_ids = array();
			foreach ($notifications as $nf) {
				array_push($nparent_ids, $nf['id']);
			}
			// nest children notifications
			$q = "SELECT * FROM `#__vikchannelmanager_notification_child` WHERE `id_parent` IN (" . implode(',', $nparent_ids) . ");";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows()) {
				$children = $dbo->loadAssocList();
				foreach ($notifications as $nk => $nf) {
					$notifications[$nk]['result'] = '';
					$notifications[$nk]['children'] = array();
					foreach ($children as $child) {
						if ($nf['id'] == $child['id_parent']) {
							array_push($notifications[$nk]['children'], $child);
						}
					}
				}
			}
		}

		// parse contents for the various notifications
		foreach ($notifications as $nk => $notify) {
			// decode content into a readable string
			$txt_parts = explode("\n", $notify['cont']);
			$render_mess = VikChannelManager::getErrorFromMap(trim($txt_parts[0]), true);
			unset($txt_parts[0]);
			$notifications[$nk]['cont'] = $render_mess . (count($txt_parts) ? "\n" . implode("\n", $txt_parts) : '');

			// add the result key
			switch (intval($notify['type'])) {
				case 1:
					$notifications[$nk]['result'] = 'success';
					break;
				case 2:
					$notifications[$nk]['result'] = 'warning';
					break;
				default:
					$notifications[$nk]['result'] = 'error';
					break;
			}

			// adjust children notifications
			if (!isset($notify['children'])) {
				$notifications[$nk]['children'] = array();
			}
			foreach ($notifications[$nk]['children'] as $nck => $child) {
				// decode content into a readable string
				$txt_parts = explode("\n", $child['cont']);
				$render_mess = VikChannelManager::getErrorFromMap(trim($txt_parts[0]), true);
				unset($txt_parts[0]);
				$child['cont'] = $render_mess . (count($txt_parts) ? "\n" . implode("\n", $txt_parts) : '');

				// add the result key
				switch (intval($child['type'])) {
					case 1:
						$notifications[$nk]['children'][$nck]['result'] = 'success';
						break;
					case 2:
						$notifications[$nk]['children'][$nck]['result'] = 'warning';
						break;
					default:
						$notifications[$nk]['children'][$nck]['result'] = 'error';
						break;
				}

				// add channel name key
				$chname = '';
				$channel_info = VikChannelManager::getChannel($child['channel']);
				if (count($channel_info)) {
					$chname = ucwords($channel_info['name']);
				}
				$notifications[$nk]['children'][$nck]['channel_name'] = $chname;
			}
		}

		$result = new stdClass;
		$result->notifications  = $notifications;
		$result->total 			= $tot_records;

		echo json_encode($result);
		exit;
	}
	
	/**
	 * Renders a JSON response with all the reservations logs.
	 * 
	 * @return 	void
	 * 
	 * @since 	1.6.18
	 */
	public function get_reservations_logs_list()
	{
		$dbo 		= JFactory::getDbo();
		$pe4jauth 	= VikRequest::getString('auth', '', 'request', VIKREQUEST_ALLOWRAW);
		$psearch	= VikRequest::getString('search', '', 'request');
		$pdate		= VikRequest::getString('date', '', 'request'); // Y-m-d only
		$pdatetype  = VikRequest::getInt('datetype', 1, 'request'); // 1 for checkin date, 2 for booking date
		$poffset 	= VikRequest::getInt('offset', 0, 'request');
		$plimit 	= VikRequest::getInt('limit', 20, 'request');
		$apikey   	= VikChannelManager::getApiKey(true);
		if (empty($pe4jauth) || $pe4jauth != md5($apikey)) {
			throw new Exception('Forbidden', 403);
		}

		// load rooms
		$rooms = array();
		$q = "SELECT `id`,`name` FROM `#__vikbooking_rooms` ORDER BY `name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			$all_rooms = $dbo->loadAssocList();
			foreach ($all_rooms as $r) {
				$rooms[$r['id']] = $r['name'];
			}
		}

		// load back-end language
		$lang = JFactory::getLanguage();
		if (VCMPlatformDetection::isWordPress()) {
			$lang->load('com_vikchannelmanager', VIKCHANNELMANAGER_ADMIN_LANG, $lang->getTag(), true);
		} else {
			$lang->load('com_vikchannelmanager', JPATH_ADMINISTRATOR, $lang->getTag(), true);
		}

		$reslogger = VikChannelManager::getResLoggerInstance();
		$typesmap  = $reslogger->getTypesMap();

		$filters = array(
			'fromdate' => $pdate,
			'todate' => $pdate,
			'whatdate' => ($pdatetype == 1 ? 'day' : 'dt'),
			'roomids' => VikRequest::getVar('roomids', array()),
			'reskey' => $psearch,
		);

		// set filters and clauses
		$reslogger->filterLim0($poffset)
			->filterLim($plimit)
			->filterOrdering('dt')
			->filterDirection('DESC');
		$daymethod = 'clause' . ucfirst($filters['whatdate']);

		if (!empty($filters['fromdate'])) {
			$reslogger->{$daymethod}($filters['fromdate'], '>=', array());
		}
		if (!empty($filters['todate'])) {
			// force the end date to be at the end
			$reslogger->{$daymethod}($filters['todate'] . ' 23:59:59', '<=', array());
		}
		if (count($filters['roomids']) && !empty($filters['roomids'][0])) {
			$reslogger->clauseIdRoomVb('('.implode(', ', $filters['roomids']).')', 'IN');
		}
		if (!empty($filters['reskey'])) {
			$reslogger->clauseCustom('(`idorder`='.(int)$filters['reskey'].' OR `idorderota`='.$dbo->quote($filters['reskey']).' OR `idroomota`='.(int)$filters['reskey'].')');
		}
		// load records
		$logsdata = $reslogger->load();
		list($rows, $tot_records) = $logsdata;

		// adjust some keys
		foreach ($rows as $k => $row) {
			$rows[$k]['type'] = isset($typesmap[$row['type']]) ? $typesmap[$row['type']] : $row['type'];
			$rows[$k]['room_name'] = !empty($row['idroomvb']) && isset($rooms[$row['idroomvb']]) ? $rooms[$row['idroomvb']] . ' (#'.$row['idroomvb'].')' : $row['idroomvb'];
			$rows[$k]['channel_name'] = JText::_('VCMCOMPONIBE');
			$rows[$k]['channel_logo'] = '';
			if (!empty($row['idchannel'])) {
				$channel_info = VikChannelManager::getChannel($row['idchannel']);
				if (count($channel_info)) {
					$rows[$k]['channel_name'] = $channel_info['name'];
					$rows[$k]['channel_logo'] = VikChannelManager::getLogosInstance($channel_info['name'])->getLogoURL();
				}
			}
		}

		$result = new stdClass;
		$result->reslogs 	= $rows;
		$result->total 		= $tot_records;

		echo json_encode($result);
		exit;
	}

	/**
	 * Renders a JSON response containing all the bookings that
	 * match the query arguments.
	 * 
	 * @return 	void
	 * 
	 * @since 	1.6.18
	 */
	public function get_bookings_list()
	{
		$dbo 		= JFactory::getDbo();
		$pe4jauth 	= VikRequest::getString('auth', '', 'request', VIKREQUEST_ALLOWRAW);
		$psearch	= VikRequest::getString('search', '', 'request');
		$pdate		= VikRequest::getString('date', '', 'request');
		$pstatus	= VikRequest::getString('status', 'confirmed', 'request');
		$pnumdays	= VikRequest::getInt('numdays', 7, 'request');
		$apikey   	= VikChannelManager::getApiKey(true);
		if (empty($pe4jauth) || $pe4jauth != md5($apikey)) {
			throw new Exception('Forbidden', 403);
		}

		// load rooms
		$rooms_data = array();
		$q = "SELECT `id`,`name`,`units` FROM `#__vikbooking_rooms` ORDER BY `name` ASC;";
		$dbo->setQuery($q);
		$all_rooms = $dbo->loadAssocList();
		foreach ($all_rooms as $r) {
			$room_obj = new stdClass;
			$room_obj->id = $r['id'];
			$room_obj->name = $r['name'];
			$room_obj->units = $r['units'];
			$rooms_data[$r['id']] = $room_obj;
		}

		// load back-end language
		$lang = JFactory::getLanguage();
		if (VCMPlatformDetection::isWordPress()) {
			$lang->load('com_vikchannelmanager', VIKCHANNELMANAGER_ADMIN_LANG, $lang->getTag(), true);
			$lang->load('com_vikbooking', VIKBOOKING_LANG, $lang->getTag(), true);
		} else {
			$lang->load('com_vikchannelmanager', JPATH_ADMINISTRATOR, $lang->getTag(), true);
			$lang->load('com_vikbooking', JPATH_ADMINISTRATOR, $lang->getTag(), true);
		}

		$bookings  = array();
		$clauses   = array();
		$all_rooms = array();
		$currency  = VikBooking::getCurrencySymb();

		if (empty($pdate)) {
			$pdate = date('Y-m-d');
		}

		$dtinfo = getdate(strtotime($pdate));
		$mindt  = mktime(0, 0, 0, $dtinfo['mon'], ($dtinfo['mday'] - $pnumdays), $dtinfo['year']);
		$maxdt  = mktime(23, 59, 59, $dtinfo['mon'], ($dtinfo['mday'] + $pnumdays), $dtinfo['year']);
		array_push($clauses, "`o`.`checkout` >= {$mindt}");
		array_push($clauses, "`o`.`checkin` <= {$maxdt}");

		if (!empty($pstatus) && in_array($pstatus, array('confirmed', 'standby', 'cancelled'))) {
			array_push($clauses, "`o`.`status` = " . $dbo->quote($pstatus));
		}

		if (!empty($psearch)) {
			if (is_numeric($psearch)) {
				array_push($clauses, "(`o`.`id` = " . (int)$psearch . " OR `o`.`idorderota` = " . $dbo->quote($psearch) . ")");
			} else {
				array_push($clauses, "`o`.`custdata` LIKE " . $dbo->quote('%' . $psearch . '%'));
			}
		}

		// load bookings
		$q = "SELECT `o`.`id`, `o`.`custdata`, `o`.`ts`, `o`.`status`, `o`.`days`, `o`.`checkin`, `o`.`checkout`, `o`.`roomsnum`, `o`.`idorderota`, `o`.`channel`, `o`.`closure`, 
			(SELECT GROUP_CONCAT(`or`.`idroom` SEPARATOR ';') FROM `#__vikbooking_ordersrooms` AS `or` WHERE `or`.`idorder`=`o`.`id`) AS `room_ids` 
			FROM `#__vikbooking_orders` AS `o`" . (count($clauses) ? " WHERE " . implode(' AND ', $clauses) : "") . " ORDER BY `o`.`checkin` ASC";
		$dbo->setQuery($q);
		$bookings = $dbo->loadAssocList();

		foreach ($bookings as $k => $booking) {
			// set room ids array
			$bookings[$k]['room_ids'] = explode(';', $booking['room_ids']);
			$all_rooms = array_merge($all_rooms, $bookings[$k]['room_ids']);
			// adjust "custdata"
			$newcustdata = $booking['custdata'];
			$parts = preg_split("/\R/", $booking['custdata']);
			if (count($parts) > 2) {
				// we probably have enough information, let's just grab the first two rows
				$newcustdata = '';
				for ($i = 0; $i < 2; $i++) {
					$subparts = explode(':', $parts[$i]);
					if (count($subparts) > 1) {
						unset($subparts[0]);
						$newcustdata .= implode(' ', $subparts) . ' ';
					} else {
						$newcustdata .= $parts[$i];
					}
				}
			}
			$bookings[$k]['custdata'] = $newcustdata;
			// adjust timestamps
			$bookings[$k]['ts'] = date('Y-m-d H:i:s', $booking['ts']);
			$bookings[$k]['checkin'] = date('Y-m-d H:i:s', $booking['checkin']);
			$bookings[$k]['checkout'] = date('Y-m-d H:i:s', $booking['checkout']);
			// load booking history records
			$bookings[$k]['history'] = array();
			if (!method_exists('VikBooking', 'getBookingHistoryInstance')) {
				continue;
			}
			$history_obj = VikBooking::getBookingHistoryInstance();
			$history_obj->setBid($booking['id']);
			$bookings[$k]['history'] = $history_obj->loadHistory();
			foreach ($bookings[$k]['history'] as $hk => $hist) {
				// parse the log type into a readable value
				$bookings[$k]['history'][$hk]['type'] = $history_obj->validType($hist['type'], true);
			}
		}

		// filter the involved rooms
		foreach ($rooms_data as $k => $v) {
			if (!in_array($v->id, $all_rooms)) {
				unset($rooms_data[$k]);
			}
		}

		$result = new stdClass;
		$result->start_date = date('Y-m-d', $mindt);
		$result->end_date 	= date('Y-m-d', $maxdt);
		$result->bookings 	= $bookings;
		$result->rooms 		= $rooms_data;
		$result->currency 	= $currency;

		VCMHttpDocument::getInstance()->json($result);
	}

	/**
	 * Updates the settings for a specific channel. Originally introduced
	 * for the AirBnB APIs in April 2021 to handle the new host connections.
	 * 
	 * @return 	void
	 * 
	 * @since 	1.8.0
	 */
	public function connection_settings_l()
	{
		$dbo 		 = JFactory::getDbo();
		$pe4jauth 	 = VikRequest::getString('e4jauth', '', 'request', VIKREQUEST_ALLOWRAW);
		$pchannel 	 = VikRequest::getInt('channel', 0, 'request');
		$chsettings  = VikRequest::getVar('ch_settings', array());
		$newsettings = VikRequest::getVar('new_settings', array());
		$apikey   	 = VikChannelManager::getApiKey(true);
		if (empty($pe4jauth) || $pe4jauth != md5($apikey) || empty($pchannel)) {
			throw new Exception('e4j.error.Forbidden', 403);
		}

		if (!is_array($chsettings) || !$chsettings) {
			throw new Exception('e4j.error.Settings not found', 404);
		}

		$channel_info = VikChannelManager::getChannel($pchannel);
		if (!$channel_info) {
			throw new Exception('e4j.error.Channel not found', 404);
		}

		/**
		 * It is also possible to overwrite some settings properties.
		 * Useful to update settings like "price_compare" for taxes.
		 * 
		 * @since 	1.8.11
		 */
		$set_settings = $channel_info['settings'];
		$set_settings = !is_array($set_settings) ? (array)json_decode($set_settings, true) : $set_settings;
		if (!empty($newsettings)) {
			foreach ($newsettings as $sett_name => $sett_val) {
				if (!isset($set_settings[$sett_name]) || !is_array($set_settings[$sett_name]) || !isset($set_settings[$sett_name]['value'])) {
					continue;
				}
				// update setting value
				$set_settings[$sett_name]['value'] = $sett_val;
			}
		}

		// update channel params on db as well as channel actual settings
		$ch_params = json_encode($chsettings);
		$q = "UPDATE `#__vikchannelmanager_channel` SET `params`=" . $dbo->q($ch_params) . ", `settings`=" . $dbo->q(json_encode($set_settings)) . " WHERE `id`=" . $channel_info['id'] . ";";
		$dbo->setQuery($q);
		$dbo->execute();

		/**
		 * In case of a re-connection with Airbnb for an existing host account,
		 * we need to update the mapping information with the new settings, or
		 * when switching account an invalid refresh token will reset the good one.
		 * 
		 * @since 	1.9
		 */
		if ($channel_info['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI && ($chsettings['user_id'] ?? null)) {
			// find previously mapped listings
			$dbo->setQuery(
				$dbo->getQuery(true)
					->select([
						$dbo->qn('id'),
						$dbo->qn('prop_params'),
					])
					->from($dbo->qn('#__vikchannelmanager_roomsxref'))
					->where($dbo->qn('idchannel') . ' = ' . (int) $channel_info['uniquekey'])
					->where($dbo->qn('prop_params') . ' LIKE ' . $dbo->q('%' . $chsettings['user_id'] . '%'))
			);

			$prev_listings = $dbo->loadAssocList();

			foreach ($prev_listings as $prev_xref) {
				// update property params at listing mapping level to avoid future issues
				$dbo->setQuery(
					$dbo->getQuery(true)
						->update($dbo->qn('#__vikchannelmanager_roomsxref'))
						->set($dbo->qn('prop_params') . ' = ' . $dbo->q($ch_params))
						->where($dbo->qn('id') . ' = ' . (int) $prev_xref['id'])
				, 0, 1);
				$dbo->execute();
			}
		}

		// set response string
		$response = 'e4j.ok.' . $ch_params . "\n" . json_encode(VikChannelManager::getChannel($pchannel));

		echo $response;
		exit;
	}

	/**
	 * Checks if a room OTA is available. Originally introduced to support
	 * real-time responses for the Webhook notifications of the Airbnb APIs.
	 * 
	 * @return 	void
	 * 
	 * @since 	1.8.0
	 */
	public function ota_check_availability()
	{
		$dbo 		 = JFactory::getDbo();
		$pe4jauth 	 = VikRequest::getString('e4jauth', '', 'request', VIKREQUEST_ALLOWRAW);
		$pota_roomid = VikRequest::getString('ota_room_id', '', 'request');
		$pchannel 	 = VikRequest::getInt('channel', 0, 'request');
		$pfrom_date  = VikRequest::getString('from_date', '', 'request');
		$pto_date 	 = VikRequest::getString('to_date', '', 'request');
		$pnights 	 = VikRequest::getInt('nights', 0, 'request');

		if (empty($pe4jauth) || $pe4jauth != md5(VikChannelManager::getApiKey(true))) {
			echo 'e4j.error.Forbidden';
			exit;
		}

		if (empty($pota_roomid) || empty($pchannel) || empty($pfrom_date) || (empty($pto_date) && empty($pnights))) {
			echo 'e4j.error.Bad Request';
			exit;
		}

		$channel_info = VikChannelManager::getChannel($pchannel);
		if (!count($channel_info)) {
			echo 'e4j.error.Channel not found';
			exit;
		}

		// find the corresponding VBO room ID from the given OTA room id
		$q = "SELECT `idroomvb` FROM `#__vikchannelmanager_roomsxref` WHERE `idroomota`=" . $dbo->quote($pota_roomid) . " AND `idchannel`=" . (int)$pchannel . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			echo 'e4j.error.Room not found';
			exit;
		}
		$vbo_room_id = $dbo->loadResult();

		// get the total number of units for this room
		$q = "SELECT `units` FROM `#__vikbooking_rooms` WHERE `id`=" . (int)$vbo_room_id . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			echo 'e4j.error.Room does not exist';
			exit;
		}
		$vbo_room_units = $dbo->loadResult();

		// get check-in date timestamp
		$checkin_ts = strtotime($pfrom_date);
		$info_from  = getdate($checkin_ts);
		if (!empty($pto_date)) {
			$checkout_ts = strtotime($pto_date);
		} else {
			$checkout_ts = mktime($info_from['hours'], $info_from['minutes'], $info_from['seconds'], $info_from['mon'], ($info_from['mday'] + $pnights), $info_from['year']);
		}

		// let VikBooking do the rest
		require_once VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'lib.vikbooking.php';

		// adjust time, if necessary
		if ($info_from['hours'] < 1) {
			$check_in_out = VikBooking::getTimeOpenStore();
			$checkin_ts += $check_in_out[0];
			$checkout_ts += $check_in_out[1];
		}

		// check if the room is available on the requested dates
		if (VikBooking::roomBookable($vbo_room_id, $vbo_room_units, $checkin_ts, $checkout_ts)) {
			echo 'e4j.ok';
			exit;
		}

		/**
		 * In order to reduce rejections with Airbnb, we check if the subscription is active.
		 * 
		 * @since 	1.8.16
		 */
		if ($channel_info['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI && VikChannelManager::isSubscriptionExpired()) {
			// the reservation shall be allowed
			VCMHttpDocument::getInstance()->close(200, 'e4j.ok.expired');
		}

		// room is not available, output error code
		echo 'e4j.error.Unavailable';

		/**
		 * If we reach this point for the channel Airbnb API, then this will be a check_availability rejection.
		 * In order to reduce the number of rejections, due to a discrepancy with the availability between the
		 * site and Airbnb, we need to trigger the Bulk Action (availability) by forcing the update request.
		 * 
		 * @since 	1.8.3
		 */
		if ($channel_info['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI) {
			// build a smart range of dates that should be updated (one month before check-in and one month after check-out)
			$safe_now_ts  = time();
			$infocheckout = getdate($checkout_ts);
			$safe_from_ts = mktime(0, 0, 0, ($info_from['mon'] - 1), $info_from['mday'], $info_from['year']);
			$safe_from_ts = $safe_from_ts < $safe_now_ts ? $safe_now_ts : $safe_from_ts;
			$safe_to_ts   = mktime(0, 0, 0, ($infocheckout['mon'] + 1), $infocheckout['mday'], $infocheckout['year']);

			// attempt to close the response prematurely
			try {
				$output_flushed = false;
				if (function_exists('fastcgi_finish_request')) {
					$output_flushed = fastcgi_finish_request();
				}
				if (!$output_flushed) {
					header("Content-Encoding: none");
					header("Connection: close");
					ob_end_flush();
					flush();
				}
			} catch (Exception $e) {
				// do nothing
			}

			// force the bulk action
			VikChannelManager::autoBulkActions(array(
				'from_date' => date('Y-m-d', $safe_from_ts),
				'to_date' 	=> date('Y-m-d', $safe_to_ts),
				'update' 	=> 'availability',
				'uniquekey' => VikChannelManagerConfig::AIRBNBAPI,
			));
		}

		// exit process
		exit;
	}

	/**
	 * GRR_L Guest Reviews Retrieval Listener.
	 * New guest reviews for certain channels can be sent to this task.
	 * 
	 * @since 	1.8.0
	 */
	public function grr_l()
	{
		$dbo = JFactory::getDbo();

		// load dependencies
		require_once VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'lib.vikbooking.php';

		// JSON request content
		$input = JFactory::getApplication()->input->json;

		// request variables
		$pe4jauth 	 = $input->get('e4jauth', '', 'raw');
		$pchannel 	 = $input->getInt('channel', 0);
		$account_key = $input->getString('account_key', '');
		$raw 	  	 = $input->getRaw();

		// validation
		if (empty($pe4jauth) || empty($pchannel) || empty($raw)) {
			echo 'e4j.error.EmptyRequest';
			exit;
		}
		$api_key = VikChannelManager::getApiKey();
		$channel = VikChannelManager::getChannel($pchannel);
		if (!count($channel)) {
			echo 'e4j.error.NoChannel';
			exit;
		}
		$checkauth = md5($api_key);
		if ($checkauth != $pe4jauth) {
			echo 'e4j.error.Authentication';
			exit;
		}
		$req = json_decode($raw);
		if (!is_object($req) || !isset($req->reviews)) {
			echo 'e4j.error.InvalidRequest';
			exit;
		}

		// load all review IDs for this channel to avoid double records
		$current_reviews = array();
		$q = "SELECT `review_id` FROM `#__vikchannelmanager_otareviews` WHERE `uniquekey`=" . (int)$channel['uniquekey'] . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			$allrevs = $dbo->loadAssocList();
			foreach ($allrevs as $r) {
				array_push($current_reviews, $r['review_id']);
			}
		}

		// load account main param and property name to support multiple accounts for the reviews
		$account_main_param = null;
		$property_name 		= null;
		$q = "SELECT `prop_name`, `prop_params` FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel`=" . (int)$channel['uniquekey'] . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			$xref = $dbo->loadAssocList();
			foreach ($xref as $x) {
				$prop_params = json_decode($x['prop_params'], true);
				$prop_params = is_array($prop_params) ? $prop_params : array();
				foreach ($prop_params as $param_name => $param_val) {
					if ($param_val == $account_key) {
						// account found
						$account_main_param = $account_key;
						$property_name = $x['prop_name'];
						break 2;
					}
				}
			}
		}

		// start response
		$response = new stdClass;
		$response->newReviews = 0;

		// loop through reviews pool
		$set_status = $channel['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI ? 0 : 1;
		foreach ($req->reviews as $review) {
			// make sure the ID is set and that it doesn't exist already
			if (!is_object($review) || empty($review->review_id) || in_array($review->review_id, $current_reviews)) {
				continue;
			}

			// review content
			$rev_content = null;
			if (!empty($review->content) && !is_scalar($review->content)) {
				$rev_content = json_encode($review->content);
			}

			// VBO reservation ID
			$vbo_res_id = isset($review->idorder) ? $review->idorder : null;
			if (empty($vbo_res_id) && !empty($review->idorderota)) {
				// check if this booking exists from this channel
				$q = "SELECT `id` FROM `#__vikbooking_orders` WHERE `idorderota`=" . $dbo->quote($review->idorderota) . " AND `channel` LIKE " . $dbo->quote('%' . $channel['name'] . '%') . ";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows()) {
					// set the proper idorder
					$vbo_res_id = $dbo->loadResult();
				}
			}
			
			// build record object
			$vcm_review = new stdClass;
			$vcm_review->review_id 		  = $review->review_id;
			$vcm_review->prop_first_param = $account_main_param;
			$vcm_review->prop_name 		  = $property_name;
			$vcm_review->channel 		  = $channel['name'];
			$vcm_review->uniquekey 		  = $channel['uniquekey'];
			$vcm_review->idorder 		  = $vbo_res_id;
			$vcm_review->dt 			  = (!empty($review->dt) ? $review->dt : JFactory::getDate()->toSql(true));
			$vcm_review->customer_name 	  = (!empty($review->customer_name) ? $review->customer_name : null);
			$vcm_review->lang 			  = (!empty($review->lang) ? $review->lang : null);
			$vcm_review->score 			  = (float)$review->score;
			$vcm_review->country 		  = (!empty($review->country) && strlen($review->country) <= 3 ? $review->country : null);
			$vcm_review->content 		  = $rev_content;
			$vcm_review->published 		  = $set_status;

			// store the review record
			try {
				if ($dbo->insertObject('#__vikchannelmanager_otareviews', $vcm_review, 'id')) {
					// increase reviews saved
					$response->newReviews++;
					// Booking History
					if (!empty($vbo_res_id)) {
						VikBooking::getBookingHistoryInstance($vbo_res_id)->setExtraData([
							'guest_name'    => $vcm_review->customer_name,
							'score'         => $vcm_review->score,
							'review_id'     => $vcm_review->id ?? null,
							'ota_review_id' => $vcm_review->review_id,
						])->store('GR');
					}
				}
			} catch (Exception $e) {
				// do nothing
			}
		}

		echo json_encode($response);
		exit;
	}

	/**
	 * Task invoked to execute the bulk actions automatically. Invoking is made
	 * only if the Availability Window and previous Bulk Actions criterias are met.
	 * VCM is supposed to output a result string which can indicate if paging is needed.
	 * This task should not check if the availability window is covered, because due to
	 * paging functionalities, it can update the bulk cache before other requests will come.
	 * 
	 * @since 	1.8.0
	 */
	public function autobulk_l()
	{
		$app = JFactory::getApplication();

		// gather main request options
		$pe4jauth = $app->input->get('e4jauth', '', 'raw') ?: $app->input->get('e4j_auth', '', 'raw');
		$from     = $app->input->getString('from', '');
		$to       = $app->input->getString('to', '');
		$paging   = $app->input->getInt('paging', 0);

		/**
		 * It is possible to force the update to either availability or rates.
		 * 
		 * @since 	1.8.3
		 */
		$update = $app->input->getString('update', '');

		/**
		 * Optionally force a specific channel identifier for the request
		 * and optionally include only certain Vik Booking room type IDs.
		 * 
		 * @since 	1.8.9
		 */
		$uniquekey 	  = $app->input->getInt('uniquekey', 0);
		$forced_rooms = (array) $app->input->get('forced_rooms', [], 'array');

		/**
		 * It is possible to force the rates update to a specific rate-plan ID.
		 * 
		 * @since 	1.9.6
		 */
		$rate_id = $app->input->getInt('rate_id', 0);

		// tell if debug mode should be enabled
		$debug = $app->input->getBool('e4j_debug', false);

		// authenticate the request
		$vcm_api_key = VikChannelManager::getApiKey(true);
		if ($pe4jauth != md5($vcm_api_key)) {
			echo 'e4j.error.Authentication';
			exit;
		}

		// load back-end language
		$lang = JFactory::getLanguage();
		if (VCMPlatformDetection::isWordPress()) {
			$lang->load('com_vikchannelmanager', VIKCHANNELMANAGER_ADMIN_LANG, $lang->getTag(), true);
		} else {
			$lang->load('com_vikchannelmanager', JPATH_ADMINISTRATOR, $lang->getTag(), true);
		}

		/**
		 * All is ready to start composing the request(s). Make sure to increase the
		 * script execution lifetime to avoid unwanted limitations to be applied.
		 */
		try {
			// unlimited execution time
			@set_time_limit(0);

			// ignore user termination of script execution
			@ignore_user_abort(true);
		} catch (Exception $e) {
			// do nothing
		}

		/**
		 * Invoke Bulk Action Processor.
		 * 
		 * @since 	1.9.16
		 */
		try {
			$processor = (new VCMBulkactionProcessor([
				'update'       => $update,
				'from'         => $from,
				'to'           => $to,
				'uniquekey'    => $uniquekey,
				'forced_rooms' => $forced_rooms,
				'rate_id'      => $rate_id,
				'paging'       => $paging,
			]))->setDebug((bool) $debug);
		} catch (Exception $e) {
			// terminate the process by sending the error to output
			echo 'e4j.error.' . $e->getMessage();
			exit;
		}

		/**
		 * Check if we should compose and dispatch a "CustAvailUpdate" request.
		 * The operation will simulate a Bulk Action - Copy Availability request.
		 */
		if ($update != 'rates') {
			try {
				// distribute rooms availability to channels
				$processor->distributeAvailability();

				// obtain the next paging offset, if any
				$next_paging_offset = $processor->getNextPagingOffset();
			} catch (Exception $e) {
				// terminate the process by sending the error to output
				echo 'e4j.error.' . $e->getMessage();
				exit;
			}

			/**
			 * The auto bulk action for the availability inventory succeeded!
			 * Child notifications will be sent by e4jConnect with the various
			 * responses. Set last execution time for both bulk actions.
			 */
			$currentdates = VikChannelManager::getInventoryMaxFutureDates();
			$currentdates['av'] = $processor->getOption('to_ts', time());
			if ($update != 'availability') {
				$currentdates['rates'] = $processor->getOption('to_ts', time());
			}
			// we update the last execution time for both bulk action types because one succeeded
			VikChannelManager::setInventoryMaxFutureDates($currentdates);

			if ($update == 'availability') {
				// output the response for e4jConnect to, eventually, trigger other paginated requests, by skipping the rates upload
				echo 'e4j.ok.vcm_next_paging:' . $next_paging_offset . ';[vcm_rates_result][/vcm_rates_result]';
				exit;
			}
		}

		/**
		 * We can proceed with the Rates (RAR request) for the e4jConnect servers like
		 * if this was a regular Bulk Action - Rates Upload Availability request.
		 */
		try {
			// distribute rates and restrictions to channels
			$rates_result = $processor->distributeRates();

			// obtain the next paging offset, if any
			$next_paging_offset = $processor->getNextPagingOffset();
		} catch (Exception $e) {
			// terminate the process by sending the error to output
			echo 'e4j.error.' . $e->getMessage();
			exit;
		}

		if ($debug) {
			// send the debug operation to output
			VCMHttpDocument::getInstance($app)->json($rates_result);
		}

		// output the response for e4jConnect to, eventually, trigger other paginated requests
		echo 'e4j.ok.vcm_next_paging:' . $next_paging_offset . ';[vcm_rates_result]' . $rates_result . '[/vcm_rates_result]';
		exit;
	}

	/**
	 * Those who only need to export reservations in iCal format without needing
	 * to import them from an external channel, can rely on this feature that doesn't
	 * generate any traffic to the Channel Manager flow with e4jConnect.
	 * 
	 * @uses 	orders_rv_l(true)
	 * 
	 * @since 	1.8.1
	 */
	public function get_ical()
	{
		// website root and base URI
		$root_uri = JUri::root();
		$base_uri = JUri::base();

		/**
		 * Inject authentication.
		 * For more safety across different platforms and versions (J3/J4 or WP)
		 * we inject values in the super global array as well as in the input object.
		 */
		VikRequest::setVar('e4jauth', VikRequest::getString('auth', '', 'request'));
		VikRequest::setVar('e4jauth', VikRequest::getString('auth', '', 'request'), 'request');

		// get (optional) room ID
		$room_id = VikRequest::getInt('id_room', 0);

		$start_ics = "BEGIN:VCALENDAR\r\n".
			"PRODID:-//" . $root_uri . "//e4jConnect//EN\r\n".
			"CALSCALE:GREGORIAN\r\n".
			"VERSION:2.0\r\n";

		$end_ics = "END:VCALENDAR";

		// retrieve bookings
		$bookings = $this->orders_rv_l(true);

		if ($bookings) {
			/**
			 * Dispatch the event to allow third-party plugins to manipulate the booking records found.
			 * 
			 * @since 	1.8.20
			 */
			VCMFactory::getPlatform()->getDispatcher()->trigger('onBeforeParseBookingsCalendar', [&$bookings]);
		}

		/**
		 * In case of no bookings, we no longer send a 404 error code, but
		 * we rather deliver an empty calendar, yet syntactically valid.
		 * 
		 * @since 	1.9.6
		 */
		$bookings = is_array($bookings) ? $bookings : [];

		/**
		 * Get the iCal privacy settings to filter the properties of the iCal calendar.
		 * We also allow third-party plugins to override such privacy fields at runtime.
		 * 
		 * @since 	1.8.24
		 */
		$ical_privacy_fields = VCMFactory::getConfig()->getArray('ical_privacy_fields', []);

		// trigger event
		VCMFactory::getPlatform()->getDispatcher()->trigger('onApplyCalendarPrivacyFields', [&$ical_privacy_fields]);
		$ical_privacy_fields = is_array($ical_privacy_fields) ? $ical_privacy_fields : [];

		// build ics content
		$content_ics = '';

		foreach ($bookings as $booking) {
			// parse reservation record
			$ts_start = strtotime($booking['checkin_date']);
			$ts_end = strtotime($booking['checkout_date']);
			$dt_start = date('Ymd', $ts_start);
			$dt_end = date('Ymd', $ts_end);
			$dt_now = !empty($booking['ts']) ? $booking['ts'] : time();
			$dt_now = date('Ymd', $dt_now) . 'T' . date('His', $dt_now) . 'Z';
			if (function_exists('sha1')) {
				$uid = sha1($base_uri . $booking['id']);
			} else {
				$uid = md5($base_uri . $booking['id']);
			}

			// build description parts
			$description_parts = [];
			if (!in_array('bid', $ical_privacy_fields)) {
				$description_parts[] = 'BOOKINGID: ' . $booking['id'];
			}
			$description_parts[] = 'CHECKIN: ' . date('m/d/Y', $ts_start);
			$description_parts[] = 'CHECKOUT: ' . date('m/d/Y', $ts_end);
			if (!in_array('email', $ical_privacy_fields)) {
				$description_parts[] = 'EMAIL: ' . (!empty($booking['custmail']) ? $booking['custmail'] : '');
			}

			// get the first three custom fields
			$cust_fields = '';
			if (!in_array('customer', $ical_privacy_fields) && !empty($booking['custdata'])) {
				$custdata_parts = explode("\n", $booking['custdata']);
				if (count($custdata_parts) > 1) {
					$fetched = 0;
					foreach ($custdata_parts as $custdata_entry) {
						$info_parts = explode(":", $custdata_entry);
						if (empty($info_parts[0]) || empty($info_parts[1]) || stristr($info_parts[0], 'mail') !== false) {
							continue;
						}
						$cust_fields .= strtoupper(trim($info_parts[0])) . ': ' . trim($info_parts[1]) . '\n';
						$fetched++;
						if ($fetched >= 3) {
							$cust_fields = rtrim($cust_fields, '\n');
							break;
						}
					}
				}
			}
			if ($cust_fields) {
				$description_parts[] = $cust_fields;
			}

			// implode safe new-line separator
			$description = implode('\n', $description_parts);

			// sanitize value
			$description = preg_replace('/([\,;])/','\\\$1', $description);

			$summary = 'Booking';
			if (!in_array('customer', $ical_privacy_fields) && !empty($booking['t_first_name']) && !empty($booking['t_last_name'])) {
				$summary = $booking['t_first_name'] . ' ' . $booking['t_last_name'] . ' (' . $summary . ')';
			}
			$summary = preg_replace('/([\,;])/','\\\$1', $summary);

			// location property is the room name
			$location = !empty($booking['name']) ? $booking['name'] : '';
			$location = preg_replace('/([\,;])/','\\\$1', $location);

			if (!empty($ts_start) && !empty($ts_end) && !empty($dt_start) && !empty($dt_end)) {
				// build booking event properties
				$booking_cal_event = [
					'BEGIN' 			 => 'VEVENT',
					'DTSTAMP' 			 => $dt_now,
					'DTEND;VALUE=DATE' 	 => $dt_end,
					'DTSTART;VALUE=DATE' => $dt_start,
					'UID' 				 => $uid,
					'DESCRIPTION' 		 => $description,
					'SUMMARY' 			 => $summary,
				];

				if (!in_array('room', $ical_privacy_fields)) {
					$booking_cal_event['LOCATION'] = $location;
				}
				if (!in_array('bid', $ical_privacy_fields)) {
					$booking_cal_event['X-Booking-Ref'] = $booking['id'];
				}
				if (!in_array('guests', $ical_privacy_fields) && isset($booking['adults'])) {
					$booking_cal_event['X-Adults'] = $booking['adults'];
				}
				if (!in_array('guests', $ical_privacy_fields) && isset($booking['children'])) {
					$booking_cal_event['X-Children'] = $booking['children'];
				}
				if (!in_array('customer', $ical_privacy_fields) && !empty($booking['t_first_name']) && !empty($booking['t_last_name'])) {
					$booking_cal_event['X-Name'] = $booking['t_first_name'] . ' ' . $booking['t_last_name'];
				}
				if (!in_array('email', $ical_privacy_fields) && !empty($booking['custmail'])) {
					$booking_cal_event['X-Email'] = $booking['custmail'];
				}
				if (!in_array('customer', $ical_privacy_fields) && !empty($booking['country'])) {
					$booking_cal_event['X-Country'] = $booking['country'];
				}
				if (!in_array('customer', $ical_privacy_fields) && !empty($booking['phone'])) {
					$booking_cal_event['X-Telephone'] = $booking['phone'];
				}
				if (!in_array('total', $ical_privacy_fields) && isset($booking['total'])) {
					$booking_cal_event['X-Total-Booking-Value'] = $booking['total'];
				}

				/**
				 * Dispatch the event to allow third-party plugins to manipulate the booking event information.
				 * 
				 * @since 	1.8.20
				 */
				VCMFactory::getPlatform()->getDispatcher()->trigger('onBeforeSetBookingCalendar', [&$booking_cal_event, $booking]);

				if (!$booking_cal_event) {
					continue;
				}

				// end the booking event
				$booking_cal_event['END'] = 'VEVENT';

				// add booking event to calendar list
				foreach ($booking_cal_event as $cal_prop => $cal_val) {
					$event_line = "{$cal_prop}:{$cal_val}";
					$content_ics .= implode("\r\n ", str_split($event_line, 75)) . "\r\n";
				}
			}
		}

		// output calendar
		header('Content-type: text/calendar; charset=utf-8');
		header('Content-Disposition: attachment; filename=' . parse_url($base_uri, PHP_URL_HOST) . (!empty($room_id) ? '-' . $room_id : '') . '.ics');
		
		echo $start_ics . $content_ics . $end_ics;
		exit;
	}

	/**
	 * This endpoint updates the internal expiration details for the subscription.
	 * Successful renewals could ping this task to immediately update the data.
	 * 
	 * @since 	1.8.3
	 */
	public function update_expiration_details()
	{
		$response = new stdClass;
		$response->status = 0;

		$pe4jauth  = VikRequest::getString('e4jauth', '', 'request', VIKREQUEST_ALLOWRAW);
		$pdetails  = VikRequest::getString('details', '', 'request', VIKREQUEST_ALLOWRAW);
		$pdownload = VikRequest::getInt('download', 0, 'request');

		if ($pe4jauth != md5(VikChannelManager::getApiKey(true))) {
			if (!headers_sent()) {
				// set error code
				header('HTTP/1.1 403 Forbidden');
				header('Status: 403 Forbidden');
				return false;
			}
			echo json_encode($response);
			exit;
		}

		if ($pdownload) {
			// it is possible to ping VCM for requesting a download of the expiration details
			$response->status = (int)VikChannelManager::downloadExpirationDetails();
		} else {
			// attempt to decode the expected JSON string
			$exp_details = json_decode($pdetails);

			if (!is_object($exp_details)) {
				if (!headers_sent()) {
					// set error code
					header('HTTP/1.1 400 Bad Request');
					header('Status: 400 Bad Request');
					return false;
				}
				echo json_encode($response);
				exit;
			}

			// update expiration details
			$response->status = (int)VikChannelManager::updateExpirationDetails($exp_details);
		}

		// exit the process by writing the status
		echo json_encode($response);
		exit;
	}

	/**
	 * This endpoint updates the amount paid for an OTA reservation and
	 * stores an event log in the VBO history about the payout received.
	 * 
	 * @since 	1.8.3
	 * @since 	1.8.4  we try to keep any previously paid amount (like for upsales).
	 */
	public function payout_l()
	{
		$response = 'e4j.error';
		$pe4jauth = VikRequest::getString('e4jauth', '', 'request', VIKREQUEST_ALLOWRAW);
		$pbooking = VikRequest::getString('booking', '', 'request', VIKREQUEST_ALLOWRAW);

		// validate data
		if (empty($pe4jauth) || empty($pbooking)) {
			echo $response . '.missing data';
			exit;
		}

		// validate authentication signature
		$checkauth = md5(VikChannelManager::getApiKey());
		if ($checkauth != $pe4jauth) {
			$response = 'e4j.error.Authentication';
			echo $response;
			exit;
		}

		// decode booking details
		$reservation = json_decode($pbooking);
		if (!is_object($reservation)) {
			$response = 'e4j.error.Invalid booking data';
			echo $response;
			exit;
		}

		// check the integration of the reservation object
		if (empty($reservation->info) || empty($reservation->info->idorderota) || empty($reservation->info->source) || empty($reservation->info->total_paid)) {
			$response = 'e4j.error.Missing booking data';
			echo $response;
			exit;
		}

		// look up this booking on the db
		$dbo = JFactory::getDbo();

		$q = "SELECT * FROM `#__vikbooking_orders` WHERE `idorderota`=" . $dbo->quote($reservation->info->idorderota) . " AND `channel` LIKE " . $dbo->quote('%' . $reservation->info->source . '%');
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			$response = 'e4j.error.Booking not found';
			echo $response;
			exit;
		}
		$vbo_record = $dbo->loadObject();

		// update booking record properties
		$store_record = new stdClass;
		$store_record->id = $vbo_record->id;

		/**
		 * The property "total_paid" should be the grand total paid up until now by the OTA, not an amount just
		 * paid to be summed up. However, we need to consider any previous upselling event, by keeping in mind
		 * that multiple payout notifications may occur by the OTA (split payments) in case of large amounts.
		 * 
		 * @since 	1.8.4
		 */
		$store_record->totpaid = (float)$reservation->info->total_paid;

		// update the VBO booking history for the payout event as well as the new amount paid for the booking
		try {
			// load dependencies
			require_once VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'lib.vikbooking.php';

			// get history object
			$history_obj = VikBooking::getBookingHistoryInstance()->setBid($vbo_record->id);

			// build event type
			$ev_type = 'PO';
			if (!$history_obj->validType($ev_type)) {
				// VBO is probably outdated, revert to 'CM'
				$ev_type = 'CM';
			}

			// load current history
			$history_rows = $history_obj->loadHistory();
			$history_rows = count($history_rows) ? array_reverse($history_rows) : $history_rows;
			// check if there is a valid event with an amount paid greater than zero
			$prev_paid = 0;
			foreach ($history_rows as $hevent) {
				if ($hevent['totpaid'] < 1 || $hevent['totpaid'] == $reservation->info->total_paid || $hevent['type'] == $ev_type) {
					// skip history records with no amount paid, amount paid equal to payout, or payout events
					continue;
				}
				// amount paid found before payout notification
				$prev_paid = $hevent['totpaid'];
				break;
			}

			// sum any previously paid amount
			$store_record->totpaid += $prev_paid;

			// update booking record in VBO
			$dbo->updateObject('#__vikbooking_orders', $store_record, 'id');

			// re-invoke history object for new amount paid
			$history_obj = VikBooking::getBookingHistoryInstance()->setBid($vbo_record->id);

			// build extra data for the log
			$ev_data = new stdClass;
			// this is the total amount paid up until now
			$ev_data->payout_total = (float)$reservation->info->total_paid;
			if (isset($reservation->info->expected_payout)) {
				// this should be the grand total payout that will be received at the end
				$ev_data->payout_expected = (float)$reservation->info->expected_payout;
			}

			// build event description
			$ev_descr = $reservation->info->source . ': ' . $ev_data->payout_total;
			if (isset($ev_data->payout_expected)) {
				// concatenate the expected and maximum payout (final paid amount)
				$ev_descr .= ' / ' . $ev_data->payout_expected;
			}

			// store event history log
			$history_obj->setExtraData($ev_data)->store($ev_type, $ev_descr);
		} catch (Exception $e) {
			// do nothing, but update the amount paid in case of errors
			$dbo->updateObject('#__vikbooking_orders', $store_record, 'id');
		}

		// output a successful message
		echo 'e4j.ok.' . $vbo_record->id;
		exit;
	}

	/**
	 * Endpoint to route a booking link by taking booking vars into account.
	 * Originally introduced to support the channel Google Hotel and Google VR.
	 * 
	 * @since 	1.8.4
	 * @since 	1.9.4  added support to Google Vacation Rentals.
	 */
	public function booking_link()
	{
		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();

		$api_key = VikChannelManager::getApiKey();
		$e4j_auth = VikRequest::getString('e4j_auth', '', 'request', VIKREQUEST_ALLOWRAW);

		// the behavior will depend on the authentication of the request (redirect or JSON output)
		$is_authenticated = (md5($api_key) == $e4j_auth);

		// build default response object
		$response = new stdClass;
		$response->routed_status = 0;
		$response->routed_url 	 = JUri::root();

		// make sure the request variables are sufficient
		$enough_data = true;
		$min_rq_vars = [
			'checkin',
			'checkout',
			'adults',
		];
		foreach ($min_rq_vars as $rq_key) {
			$rq_val = VikRequest::getString($rq_key, '', 'request');
			if (!strlen($rq_val)) {
				$enough_data = false;
				break;
			}
		}

		if (!$enough_data) {
			// redirect to home page
			if ($is_authenticated) {
				// output JSON object
				VCMHttpDocument::getInstance()->json($response);
			}

			// redirect the request
			$app->redirect($response->routed_url);
			$app->close();
		}

		// build URI query arguments
		$room_type_id = VikRequest::getInt('rtype_id', 0, 'request');
		$rate_plan_id = VikRequest::getInt('rplan_id', 0, 'request');
		$route_query_args = [
			'option' 		=> 'com_vikbooking',
			'task' 			=> 'search',
			'roomdetail' 	=> $room_type_id,
			'rate_plan_id' 	=> $rate_plan_id,
			'start_date' 	=> VikRequest::getString('checkin', '', 'request'),
			'end_date' 		=> VikRequest::getString('checkout', '', 'request'),
			'roomsnum' 		=> VikRequest::getInt('roomsnum', 1, 'request'),
			'adults' 		=> [VikRequest::getInt('adults', 1, 'request')],
			'children'		=> [VikRequest::getInt('children', 0, 'request')],
			'children_age'  => VikRequest::getVar('children_age', []),
			'user_currency' => VikRequest::getString('currency', '', 'request'),
		];

		// unset useless properties that may have empty values in the URL
		if (empty($route_query_args['children_age'])) {
			unset($route_query_args['children_age']);
		}
		if (empty($route_query_args['user_currency'])) {
			unset($route_query_args['user_currency']);
		}

		/**
		 * Determine the current Google platform (Hotel/VR).
		 * 
		 * @since 	1.9.4
		 */
		$gplatform = VikRequest::getString('gplatform', 'hotel', 'request');
		$channel_key = !strcasecmp($gplatform, 'vr') ? VikChannelManagerConfig::GOOGLEVR : VikChannelManagerConfig::GOOGLEHOTEL;

		// get channel data
		$channel = VikChannelManager::getChannel($channel_key);

		/**
		 * Check if a specific category ID should be included in the routed URL in case
		 * multiple hotel accounts have been set up, in order to keep the filter.
		 * 
		 * @since 	1.8.9
		 */
		$ota_hotel_id = VikRequest::getInt('hotel_id', 0, 'request');
		if ($channel_key == VikChannelManagerConfig::GOOGLEHOTEL && !empty($ota_hotel_id) && !empty($room_type_id) && VCMGhotelMultiaccounts::getAll($only_account = true)) {
			$hotel_room_category_id = VCMGhotelMultiaccounts::guessHotelRoomCategory($ota_hotel_id, $room_type_id);
			if ($hotel_room_category_id) {
				// inject category ID filter in routed URI
				$route_query_args['category_ids'] = [
					$hotel_room_category_id,
				];
			}
		}

		// get user country and locale (preferred language), if available, to find the best language
		$user_country = VikRequest::getString('country', '', 'request');
		$user_lang 	  = VikRequest::getString('ulang', '', 'request');
		$user_country = empty($user_country) && !empty($user_lang) ? $user_lang : $user_country;
		$best_lang 	  = VikChannelManager::guessBookingLangFromCountry($user_country, $user_lang);

		/**
		 * Adjust best language in case the default website lang is different than English and
		 * the visitor speaks a foreign language. This way we make English the best language.
		 * I.E. Website default lang is IT, visitor's lang is DE, we make it land on EN because DE isn't available.
		 */
		$current_lang = JFactory::getLanguage()->getTag();
		if (empty($best_lang) && !empty($user_lang) && substr(strtolower($user_lang), 0, 2) != 'en' && substr(strtolower($current_lang), 0, 2) != 'en') {
			// check if English is available
			$known_langs = VikChannelManager::getKnownLanguages();
			foreach ($known_langs as $ltag => $ldet) {
				if (substr(strtolower($ltag), 0, 2) == 'en') {
					// grab this English-esque language
					$best_lang = $ltag;
					break;
				}
			}
		}

		// route the best URI (if possible)
		try {
			// find the best page id for the URL according to the CMS
			$itemid = null;
			if (VCMPlatformDetection::isWordPress()) {
				/**
				 * @wponly  route the best Shortcode for the booking process ("room details" or "search form").
				 * 			the best booking language is passed over the model to find the best shortcode.
				 */
				$model 	= JModel::getInstance('vikbooking', 'shortcodes', 'admin');
				if (!empty($room_type_id)) {
					// grab all Shortcodes and parse them to find the best one for this room, if any
					$shortcodes = $model->all();
					foreach ($shortcodes as $shortcode) {
						if ($shortcode->type != 'roomdetails' || empty($shortcode->post_id)) {
							continue;
						}
						$page_params = json_decode($shortcode->json);
						if (!is_object($page_params) || !isset($page_params->roomid) || (int)$page_params->roomid != $room_type_id) {
							continue;
						}
						if (!empty($best_lang) && $shortcode->lang == $best_lang) {
							// always give higher priority to the exact lang
							$itemid = $shortcode->post_id;
						}
						if (empty($itemid)) {
							// in case no perfect lang found, use this post id for this room-type
							$itemid = $shortcode->post_id;
						}
					}
				}
				if (empty($itemid)) {
					// default to the best shortcode for this lang of type "search form"
					$itemid = $model->best(['vikbooking'], $best_lang);
					if (!$itemid) {
						// if we really get to this point, try to fetch the first non-empty shortcode, if any
						$shortcodes = $model->all($columns = 'post_id', $full = true);
						if ($shortcodes) {
							// grab the very first active shortcode, no matter what's the type of it
							$itemid = $shortcodes[0]->post_id;
						}
					}
				}
			} else {
				/**
				 * @joomlaonly  inject the language to the query arguments list before routing
				 */
				if (!empty($best_lang)) {
					$route_query_args['lang'] = $best_lang;
				}

				/**
				 * @joomlaonly  seek for the best menu item ID
				 * 
				 * @since 	1.8.12  added support for menu item arguments to match.
				 */
				if (!empty($room_type_id)) {
					$best_menuitem_id = VCMFactory::getPlatform()->getPagerouter()->findProperPageId(['roomdetails'], ['roomid' => $room_type_id, 'lang' => $best_lang]);
				} else {
					$best_menuitem_id = VCMFactory::getPlatform()->getPagerouter()->findProperPageId(['vikbooking'], $best_lang);
				}

				if ($best_menuitem_id) {
					$itemid = $best_menuitem_id;
				} else {
					$itemid = VCMFactory::getPlatform()->getPagerouter()->findProperPageId(['vikbooking'], $best_lang);
				}
			}

			/**
			 * Invoke VBO tracker class handler for storing a temporary fingerprint for
			 * the e4jConnect servers that contains the booking link extra variables.
			 * This is done only if the channel Google Hotel is enabled in VCM.
			 * 
			 * @requires 	VBO 1.15.0 (J) - 1.5.0 (WP)
			 */
			if (is_array($channel) && $channel) {
				// invoke VikBookingTracker handler
				$tracker = VikBooking::getTracker();
				// push requested dates and party
				$tracker->pushDates($route_query_args['start_date'], $route_query_args['end_date'])->pushParty([
					[
						'adults'   => $route_query_args['adults'][0],
						'children' => $route_query_args['children'][0],
					]
				]);
				if (!empty($room_type_id)) {
					// push room type and rate plan IDs
					$tracker->pushRooms($room_type_id, $rate_plan_id);
				}
				// prepare special request variables to be tracked
				$extra_tracking = [
					'referrer' 		=> (!empty($channel['name']) ? $channel['name'] : ''),
					'user_currency' => VikRequest::getString('currency', '', 'request'),
					'user_language' => VikRequest::getString('ulang', '', 'request'),
					'country' 		=> VikRequest::getString('country', '', 'request'),
					'device' 		=> VikRequest::getString('device', '', 'request'),
					'source' 		=> VikRequest::getString('source', '', 'request'),
					'ads_id' 		=> VikRequest::getString('ads_id', '', 'request'),
					'origin' 		=> VikRequest::getString('origin', '', 'request'),
					'click_type' 	=> VikRequest::getString('click_type', '', 'request'),
					'date_type' 	=> VikRequest::getString('date_type', '', 'request'),
					'promo_code' 	=> VikRequest::getString('promo_code', '', 'request'),
					'test' 			=> VikRequest::getString('test', '', 'request'),
				];
				foreach ($extra_tracking as $extra_prop => $extra_val) {
					$tracker->pushData($extra_prop, $extra_val);
				}
				// close tracking and get tracking fingerprint
				$vbo_tracking = $tracker->closeTrack() ? $tracker->getFingerprint() : null;
				if (!empty($vbo_tracking) && is_scalar($vbo_tracking)) {
					// inject tracking fingerprint to the query arguments list
					$route_query_args['vbo_tracking'] = (string) $vbo_tracking;
				}
			}

			// route final URL with all arguments
			$response->routed_url = VikBooking::externalroute($route_query_args, false, $itemid);

			// update status in response object
			$response->routed_status = 1;
		} catch (Exception $e) {
			// do nothing
		}

		// finalize response
		if ($is_authenticated) {
			// output JSON object
			VCMHttpDocument::getInstance()->json($response);
		}

		// redirect the request if not authenticated
		$app->redirect($response->routed_url);
		$app->close();
	}

	/**
	 * Endpoint to perform the Google Hotel/VR check status operations in case
	 * of price accuracy mismatches reported by Google in the Hotel Center.
	 * 
	 * @since 	1.8.9
	 * @since 	1.9.4 	added support for Google Vacation Rentals.
	 */
	public function ghotel_status()
	{
		$app = JFactory::getApplication();
		$dbo = JFactory::getDbo();

		$api_key = VikChannelManager::getApiKey();
		$e4j_auth = $app->input->getRaw('e4j_auth');
		$uniquekey = $app->input->getUInt('uniquekey', VikChannelManagerConfig::GOOGLEHOTEL);

		if (md5($api_key) != $e4j_auth) {
			VCMHttpDocument::getInstance()->close(401, 'Unauthorized');
		}

		if (!in_array($uniquekey, [VikChannelManagerConfig::GOOGLEHOTEL, VikChannelManagerConfig::GOOGLEVR])) {
			VCMHttpDocument::getInstance()->close(400, 'Unsupported channel identifier');
		}

		// get channel data
		$channel = VikChannelManager::getChannel($uniquekey);

		if (empty($channel)) {
			VCMHttpDocument::getInstance()->close(500, 'Requested Google channel not available');
		}

		// invoke the Google Hotel Status object
		$ghotel_status = VCMGhotelStatus::getInstance($channel);

		// bind posted values
		$ghotel_status->bindOptions((array) $app->input->post->getArray());

		// check the operation requested
		$op = $app->input->getString('operation', '');
		if (empty($op) || !is_callable([$ghotel_status, $op])) {
			VCMHttpDocument::getInstance()->close(400, 'Bad request');
		}

		// run the requested operation and send JSON response to output
		VCMHttpDocument::getInstance()->json($ghotel_status->{$op}(), (defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0));
	}

	/**
	 * Endpoint to update the pricing model for a specific channel.
	 * Originally introduced to quickly switch from RLO to OBP with Booking.com.
	 * 
	 * @since 	1.8.11
	 */
	public function update_pricing_model()
	{
		$dbo = JFactory::getDbo();

		$api_key = VikChannelManager::getApiKey();
		$e4j_auth = VikRequest::getString('e4j_auth', '', 'request', VIKREQUEST_ALLOWRAW);

		if (md5($api_key) != $e4j_auth) {
			VCMHttpDocument::getInstance()->close(401, 'Unauthorized');
		}

		$channel_id = VikRequest::getInt('channel_id', 0, 'request');
		$account_id = VikRequest::getString('account_id', '', 'request');
		$pmodel 	= VikRequest::getString('pmodel', '', 'request');
		$channel 	= VikChannelManager::getChannel($channel_id);

		if (empty($channel_id) || empty($account_id) || empty($pmodel) || empty($channel)) {
			VCMHttpDocument::getInstance()->close(400, 'Bad Request');
		}

		// get bulk rates cache
		$adv_params = VikChannelManager::getBulkRatesAdvParams();

		if ($channel['uniquekey'] == VikChannelManagerConfig::BOOKING) {
			// make sure the pricing model string is in upper-case
			$pmodel = strtoupper($pmodel);

			// global pricing model for this channel
			$adv_params['bcom_pricing_model'] = $pmodel;

			// set the pricing model at account level
			$adv_params['bcom_pricing_model_' . $account_id] = $pmodel;

			// set the pricing model at rate plan level
			$matching_param = str_replace(['{', '}'], '', json_encode(['hotelid' => $account_id]));
			$q = "SELECT * FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel`={$channel['uniquekey']} AND `prop_params` LIKE " . $dbo->q("%{$matching_param}%");
			$dbo->setQuery($q);
			$dbo->execute();
			if (!$dbo->getNumRows()) {
				// if this account ID has got no rooms mapped, we exit with an error
				VCMHttpDocument::getInstance()->close(400, 'The provided account ID has got no rooms mapped');
			}

			$account_rooms = $dbo->loadAssocList();
			foreach ($account_rooms as $k => $account_room) {
				$ota_pricing = json_decode($account_room['otapricing'], true);
				if (!is_array($ota_pricing) || !isset($ota_pricing['RatePlan']) || !is_array($ota_pricing['RatePlan'])) {
					continue;
				}

				foreach ($ota_pricing['RatePlan'] as $rp_id => $rp_data) {
					if (!is_array($rp_data) || empty($rp_data)) {
						continue;
					}
					// inject the "pmodel" property
					$ota_pricing['RatePlan'][$rp_id]['pmodel'] = $pmodel;

					// set the pricing model at rate plan level for this rate plan ID
					$adv_params['bcom_pricing_model_' . $rp_id] = $pmodel;
				}

				// update record by setting the new ota pricing information
				$account_room_record = new stdClass;
				$account_room_record->id = $account_room['id'];
				$account_room_record->otapricing = json_encode($ota_pricing);

				$dbo->updateObject('#__vikchannelmanager_roomsxref', $account_room_record, 'id');
			}
		}

		// update Bulk Rates Advanced Parameters
		VikChannelManager::updateBulkRatesAdvParams($adv_params);

		// send JSON response to output
		VCMHttpDocument::getInstance()->json($adv_params, (defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0));
	}

	/**
	 * XML endpoint listener to render the requested listing XML document.
	 * 
	 * @since 	1.8.12
	 */
	public function xml_l()
	{
		$api_key 	= VikChannelManager::getApiKey();
		$hash 		= VikRequest::getString('hash', '', 'request', VIKREQUEST_ALLOWRAW);
		$x_type		= VikRequest::getString('x_type', '', 'request');
		$x_cache	= VikRequest::getInt('x_cache', 0, 'request');
		$channel_id = VikRequest::getInt('channel_id', 0, 'request');
		$listing_id = VikRequest::getString('listing_id', '', 'request');
		$channel 	= VikChannelManager::getChannel($channel_id);

		if (md5($api_key) != $hash) {
			// trigger event to allow third party plugins to perform the authorization
			$authorized = VCMFactory::getPlatform()->getDispatcher()->filter('onAuthorizeXmlListingVikChannelManager', [$hash, $x_type]);
			if (!is_array($authorized) || !isset($authorized[0]) || $authorized[0] !== true) {
				VCMHttpDocument::getInstance()->close(401, 'Unauthorized');
			}
		}

		if (empty($x_type) || empty($channel_id) || empty($channel)) {
			VCMHttpDocument::getInstance()->close(400, 'Bad Request');
		}

		if (VCMPlatformDetection::isWordPress()) {
			// load VCM admin language
			$lang = JFactory::getLanguage();
			$lang->load('com_vikchannelmanager', VIKCHANNELMANAGER_ADMIN_LANG);
			// load language site handler too
			$lang->attachHandler(VIKCHANNELMANAGER_LIBRARIES . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR . 'admin.php', 'vikchannelmanager');
		}

		if ($channel['uniquekey'] == VikChannelManagerConfig::VRBOAPI) {
			// perform the request for Vrbo API

			try {
				// instantiate the object
				$vrboXml = VCMVrboXml::getInstance(null, (bool) $x_cache);

				/**
				 * Get cache preferences for the requested type of document to consume less server resources.
				 * 
				 * @since 	1.9.5
				 */
				if (VCMFactory::getConfig()->get('vrbo_xml_cache_fetch_' . $x_type)) {
					// force cache to be enabled on this type of document
					$vrboXml->setAllowedCachedFiles(true);
				}

				// attempt to generate an XML document and send it to output
				$vrboXml->processDocument($x_type, $channel, $listing_id);
			} catch (Exception $e) {
				// generate an erroneous XML document
				VCMVrboXml::getInstance()->errorDocument($e, $channel, $listing_id);
			}
		}

		// default to an error document
		VCMHttpDocument::getInstance()->close(406, 'Not Acceptable');
	}

	/**
	 * Makes an alignment of the framework assets. Secure endpoint required by specific OTAs.
	 * 
	 * @since 	1.8.19
	 */
	public function assets_alignment()
	{
		$api_key = VikChannelManager::getApiKey();
		$hash 	 = VikRequest::getString('hash', '', 'request', VIKREQUEST_ALLOWRAW);

		if (md5($api_key) != $hash) {
			VCMHttpDocument::getInstance()->close(401, 'Unauthorized');
		}

		try {
			// obtain the update data information
			$update_data = VCMUpdateHandler::retrieve_update_data($validate = false);

			// process update data
			VCMUpdateHandler::process_update_data($update_data);

		} catch (Exception $e) {
			// an error occurred
			VCMHttpDocument::getInstance()->close(500, $e->getMessage());
		}

		// output successful response
		VCMHttpDocument::getInstance()->json([
			'completed'    => 1,
			'from_version' => VIKCHANNELMANAGER_SOFTWARE_VERSION,
		]);
	}
}
