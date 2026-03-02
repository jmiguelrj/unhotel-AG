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

class VikChannelManagerViewnotification extends JViewUI {
	
	function display($tpl = null) {
	
		VCM::load_css_js();
	
		$cid = VikRequest::getVar('cid', array(0));
		
		$not = "";
		$row = "";
		$rooms = "";
		$busy = "";
		$rows = "";
		$retransmit = array();
		
		$dbo = JFactory::getDbo();
		$q = "SELECT * FROM `#__vikchannelmanager_notifications` WHERE `id`=".(int)$cid[0]." LIMIT 1;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$not = $dbo->loadAssoc();
			$not['children'] = array();
			$q = "SELECT * FROM `#__vikchannelmanager_notification_child` WHERE `id_parent`=".(int)$not['id'].";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$not['children'] = $dbo->loadAssocList();
			}
			if (!empty($not['idordervb'])) {
				$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id`=".(int)$not['idordervb']." LIMIT 1;";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 1) {
					$row = $dbo->loadAssoc();
					$q = "SELECT `or`.*,`r`.`name`,`r`.`fromadult`,`r`.`toadult` FROM `#__vikbooking_ordersrooms` AS `or`,`#__vikbooking_rooms` AS `r` WHERE `or`.`idorder`=".(int)$row['id']." AND `or`.`idroom`=`r`.`id` ORDER BY `or`.`id` ASC;";
					$dbo->setQuery($q);
					$dbo->execute();
					$rooms = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : "";
					$q = "SELECT * FROM `#__vikbooking_ordersbusy` WHERE `idorder`=".(int)$not['idordervb'].";";
					$dbo->setQuery($q);
					$dbo->execute();
					$busy = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : "";
				}
			}
			// VCM 1.6.10 - booking re-transmit (only new bookings)
			$valid_senders = array('booking.com', 'expedia', 'airbnbapi');
			$cont_lines = explode("\n", $not['cont']);
			if (in_array(strtolower($not['from']), $valid_senders) && strpos($cont_lines[0], 'e4j.error.Channels.BookingDownload') !== false) {
				/**
				 * In order to find the OTA reservation ID as well as the OTA room ID from the notification content,
				 * we use a regex that is compatible with all eligible channels and their format/length of reservation
				 * IDs. Most channels id length is 10 digits, but in some cases is 10-char long with upper case letters
				 * mixed with numbers. The first two wildcards replaced in the notification are respectively the OTA
				 * reservation ID and the OTA room ID, so we shoold be safe.
				 * 
				 * @since 	1.8.0
				 */
				preg_match_all("/[A-Z0-9]{8,16}/", $not['cont'], $matches);
				//
				if (is_array($matches) && count($matches[0]) && !empty($matches[0][0])) {
					$otabid = $matches[0][0];
					$otafirst_room = isset($matches[0][1]) ? $matches[0][1] : 0;
					// make sure this OTA Reservation ID does not exist
					$q = "SELECT `id` FROM `#__vikbooking_orders` WHERE `idorderota`=".$dbo->quote($otabid).";";
					$dbo->setQuery($q);
					$dbo->execute();
					if ($dbo->getNumRows() < 1) {
						// check for the string "retransmit-attempts:N;" in the content of the notification
						$re_attempts = 0;
						if (strpos($not['cont'], 'retransmit-attempts:') !== false) {
							$trparts = explode('retransmit-attempts:', $not['cont']);
							$re_attempts = (int)substr($trparts[1], 0, 1);
						}
						if ($re_attempts < 3) {
							// maximum 3 re-transmit attempts allowed to avoid getting errors
							$retransmit = array(
								'notid' 	=> $not['id'],
								'otabid' 	=> $otabid,
								'firstroom' => $otafirst_room,
								'channel' 	=> strtolower($not['from']),
								'attempts' 	=> (string)$re_attempts
							);
						}
					}
				}
			}
			//
		}
		
		$this->notification = $not;
		$this->row = $row;
		$this->rooms = $rooms;
		$this->busy = $busy;
		$this->retransmit = $retransmit;
		
		// Display the template (default.php)
		parent::display($tpl);
		
	}
}
