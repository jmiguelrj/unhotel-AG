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

// import Joomla controller library
jimport('joomla.application.component.controller');

class VikChannelManagerController extends JControllerUI {
	/**
	 * display task
	 *
	 * @return void
	 */
	public function display($cachable = false, $urlparams = false) {
		// set default view if not set
		
		$api_key = VikChannelManager::getApiKey(true);
		
		if ( !empty($api_key) ) {
			
			if (VikRequest::getVar('tmpl') !== 'component') {
				VCM::printMenu();
			}
			
			VikRequest::setVar('view', VikRequest::getCmd('view', 'dashboard'));
			
		} else {
			VikRequest::setVar('view', VikRequest::getCmd('view', 'wizard'));
		}

		// call parent behavior
		parent::display();

		VCM::printFooter();
	}
	
	// ITEMS
	
	public function config() {
		VCM::printMenu();
	
		VikRequest::setVar('view', VikRequest::getCmd('view', 'config'));
	
		parent::display();
		
		VCM::printFooter();
	}
	
	public function oversight() {
		VCM::printMenu();
	
		VikRequest::setVar('view', VikRequest::getCmd('view', 'oversight'));
	
		parent::display();
		
		VCM::printFooter();
	}

	public function avpush() {
		if (VikChannelManager::isAvailabilityRequest()) {
			VCM::printMenu();

			VikRequest::setVar('view', VikRequest::getCmd('view', 'avpush'));
		} else {
			VikRequest::setVar('view', VikRequest::getCmd('view', 'dashboard'));
		}

		parent::display();
		
		VCM::printFooter();
	}

	public function avpushsubmit() {
		if (VikChannelManager::isAvailabilityRequest()) {
			VCM::printMenu();

			VikRequest::setVar('view', VikRequest::getCmd('view', 'avpushsubmit'));
		} else {
			VikRequest::setVar('view', VikRequest::getCmd('view', 'dashboard'));
		}

		parent::display();
		
		VCM::printFooter();
	}

	public function ratespush() {
		if (VikChannelManager::isAvailabilityRequest()) {
			VCM::printMenu();

			VikRequest::setVar('view', VikRequest::getCmd('view', 'ratespush'));
		} else {
			VikRequest::setVar('view', VikRequest::getCmd('view', 'dashboard'));
		}

		parent::display();
		
		VCM::printFooter();
	}

	public function ratespushsubmit() {
		if (VikChannelManager::isAvailabilityRequest()) {
			VCM::printMenu();

			VikRequest::setVar('view', VikRequest::getCmd('view', 'ratespushsubmit'));
		} else {
			VikRequest::setVar('view', VikRequest::getCmd('view', 'dashboard'));
		}

		parent::display();
		
		VCM::printFooter();
	}

	public function smartbalancer() {
		if (VikChannelManager::isAvailabilityRequest()) {
			VCM::printMenu();

			VikRequest::setVar('view', VikRequest::getCmd('view', 'smartbalancer'));
		} else {
			VikRequest::setVar('view', VikRequest::getCmd('view', 'dashboard'));
		}

		parent::display();
		
		VCM::printFooter();
	}

	public function smartbalancerlogs() {
		if (VikChannelManager::isAvailabilityRequest()) {
			VikRequest::setVar('view', VikRequest::getCmd('view', 'smartbalancerlogs'));
		} else {
			VikRequest::setVar('view', VikRequest::getCmd('view', 'dashboard'));
		}

		parent::display();
	}

	public function smartbalancerstats() {
		if (VikChannelManager::isAvailabilityRequest()) {
			VCM::printMenu();

			VikRequest::setVar('view', VikRequest::getCmd('view', 'smartbalancerstats'));
		} else {
			VikRequest::setVar('view', VikRequest::getCmd('view', 'dashboard'));
		}

		parent::display();
		
		VCM::printFooter();
	}

	public function rmsmartbalancerlogs() {
		$dbo = JFactory::getDbo();
		$mainframe = JFactory::getApplication();

		$ids = VikRequest::getVar('cid', array(0));
		if (!empty($ids[0])) {
			//make the logs string empty
			$q = "UPDATE `#__vikchannelmanager_balancer_rules` SET `logs`='' WHERE `id`=".(int)$ids[0].";";
			$dbo->setQuery($q);
			$dbo->execute();
			//delete execution log records
			$q = "DELETE FROM `#__vikchannelmanager_balancer_ratelogs` WHERE `rule_id`=".(int)$ids[0].";";
			$dbo->setQuery($q);
			$dbo->execute();
		}

		$mainframe->redirect('index.php?option=com_vikchannelmanager&task=smartbalancer');
	}

	public function newsmartbalancer() {
		if (VikChannelManager::isAvailabilityRequest()) {
			VCM::printMenu();

			VikRequest::setVar('view', 'managesmartbalancer');
			VikRequest::setVar('type', 'new');
		} else {
			VikRequest::setVar('view', 'dashboard');
		}

		parent::display();
		
		VCM::printFooter();
	}

	public function editsmartbalancer() {
		if (VikChannelManager::isAvailabilityRequest()) {
			VCM::printMenu();

			VikRequest::setVar('view', 'managesmartbalancer');
			VikRequest::setVar('type', 'edit');
		} else {
			VikRequest::setVar('view', 'dashboard');
		}

		parent::display();
		
		VCM::printFooter();
	}

	public function saveSmartBalancer() {
		$mainframe = JFactory::getApplication();

		$whereup = VikRequest::getInt('whereup', 0, 'request');
		$rule_id = $this->storeSmartBalancerRule();

		if (empty($rule_id)) {
			//errors occurred
			if ($whereup > 0) {
				$mainframe->redirect('index.php?option=com_vikchannelmanager&task=editsmartbalancer&cid[]='.$whereup);
			} else {
				$mainframe->redirect('index.php?option=com_vikchannelmanager&task=newsmartbalancer');
			}
		} else {
			$mainframe->enqueueMessage(JText::_('VCMSMARTBALRSAVED'));
			$mainframe->redirect('index.php?option=com_vikchannelmanager&task=editsmartbalancer&cid[]='.$rule_id);
		}
	}

	public function saveCloseSmartBalancer() {
		$mainframe = JFactory::getApplication();

		$whereup = VikRequest::getInt('whereup', 0, 'request');
		$rule_id = $this->storeSmartBalancerRule();

		if (empty($rule_id)) {
			//errors occurred
			if ($whereup > 0) {
				$mainframe->redirect('index.php?option=com_vikchannelmanager&task=editsmartbalancer&cid[]='.$whereup);
			} else {
				$mainframe->redirect('index.php?option=com_vikchannelmanager&task=newsmartbalancer');
			}
		} else {
			$mainframe->enqueueMessage(JText::_('VCMSMARTBALRSAVED'));
			$mainframe->redirect('index.php?option=com_vikchannelmanager&task=smartbalancer');
		}
	}

	private function storeSmartBalancerRule() {
		$dbo = JFactory::getDbo();
		$mainframe = JFactory::getApplication();

		$whereup = VikRequest::getInt('whereup', 0, 'request');
		$type = VikRequest::getString('type', '', 'request');
		$from_date = VikRequest::getString('from_date', '', 'request');
		$to_date = VikRequest::getString('to_date', '', 'request');
		$wdays = VikRequest::getVar('wdays', array());
		$rule_name = VikRequest::getString('rule-name', '', 'request', VIKREQUEST_ALLOWHTML);
		//rt
		$rt_updown = VikRequest::getString('rt-updown', '', 'request');
		$rt_amount = VikRequest::getFloat('rt-amount', 0.00, 'request');
		$rt_pcent = VikRequest::getInt('rt-pcent', 0, 'request');
		$rt_gtlt = VikRequest::getString('rt-gtlt', '', 'request');
		$rt_daysadv = VikRequest::getInt('rt-daysadv', 0, 'request');
		$rt_number = VikRequest::getInt('rt-number', 0, 'request');
		$rt_where = VikRequest::getString('rt-where', '', 'request');
		$rt_rooms = VikRequest::getVar('rt-rooms', array());
		$rt_units = VikRequest::getString('rt-units', '', 'request');
		//av
		$av_type = VikRequest::getString('av-type', '', 'request');
		$av_number = VikRequest::getInt('av-number', 0, 'request');
		$av_rooms = VikRequest::getVar('av-rooms', array());
		$av_excl_dates = VikRequest::getString('av-excl-dates', '', 'request');
		if ($type == 'av' && $av_type == 'block' && !empty($av_excl_dates)) {
			$av_excl_dates = array_filter(explode(',', $av_excl_dates));
		} else {
			$av_excl_dates = null;
		}
		//
		//validate fields
		$validate_err = '';
		if (empty($type) || ($type != 'rt' && $type != 'av')) {
			$validate_err = JText::sprintf('VCMSMARTBALERRGENERIC', 'Type');
		}
		$from_ts = strtotime($from_date);
		$to_ts = strtotime($to_date);
		if (empty($from_ts) || empty($from_date) || empty($to_ts) || empty($to_date) || $to_ts < $from_ts) {
			$validate_err = JText::sprintf('VCMSMARTBALERRGENERIC', 'Dates');
		}
		$to_ts_info = getdate($to_ts);
		$to_ts = mktime(23, 59, 59, $to_ts_info['mon'], $to_ts_info['mday'], $to_ts_info['year']);
		if ($type == 'rt') {
			if (empty($rt_updown) || ($rt_updown != 'up' && $rt_updown != 'down')) {
				$validate_err = JText::sprintf('VCMSMARTBALERRGENERIC', 'Rates Type');
			}
			if (!($rt_amount > 0.00)) {
				$validate_err = JText::sprintf('VCMSMARTBALERRGENERIC', 'Rates Amount');
			}
			if ($rt_pcent > 1 || $rt_pcent < 0) {
				$validate_err = JText::sprintf('VCMSMARTBALERRGENERIC', 'Rates Operator');
			}
			if (empty($rt_gtlt) || ($rt_gtlt != 'lt' && $rt_gtlt != 'gt')) {
				$validate_err = JText::sprintf('VCMSMARTBALERRGENERIC', 'Rates Adjustment');
			}
			if ($rt_number < 1) {
				$validate_err = JText::sprintf('VCMSMARTBALERRGENERIC', 'Number of units');
			}
			if ($rt_updown == 'down' && $rt_daysadv < 0) {
				$validate_err = JText::sprintf('VCMSMARTBALERRGENERIC', 'Days in Advance');
			}
			if (empty($rt_where) || ($rt_where != 'ibeota' && $rt_where != 'ibe')) {
				$validate_err = JText::sprintf('VCMSMARTBALERRGENERIC', 'Modify on IBE/OTA');
			}
			if (count($rt_rooms) < 1 || (count($rt_rooms) && empty($rt_rooms[0]))) {
				$validate_err = JText::sprintf('VCMSMARTBALERRGENERIC', 'Rooms Selected');
			}
			if (empty($rt_units) || ($rt_units != 'single' && $rt_units != 'group')) {
				$validate_err = JText::sprintf('VCMSMARTBALERRGENERIC', 'Units Left Calculation');
			}
		}
		if ($type == 'av') {
			if (empty($av_type) || ($av_type != 'limit' && $av_type != 'units' && $av_type != 'block')) {
				$validate_err = JText::sprintf('VCMSMARTBALERRGENERIC', 'Availability Type');
			}
			if ($av_number < 1 && $av_type != 'block') {
				$validate_err = JText::sprintf('VCMSMARTBALERRGENERIC', 'Number of units');
			}
			if (count($av_rooms) < 1 || (count($av_rooms) && empty($av_rooms[0]))) {
				$validate_err = JText::sprintf('VCMSMARTBALERRGENERIC', 'Rooms Selected');
			}
		}
		if (!empty($validate_err)) {
			VikError::raiseWarning('', $validate_err);
			return 0;
		}
		//

		//TODO: check existing rules?

		//compose the data for the database.
		$rule = new stdClass;
		$rule->wdays = array();
		if (empty($wdays) || !(count($wdays) > 0)) {
			$rule->wdays = range(0, 6);
		} else {
			foreach ($wdays as $wday) {
				if (strlen($wday) && intval($wday) >= 0 && intval($wday) <= 6) {
					$wday = intval($wday);
					if (!in_array($wday, $rule->wdays)) {
						array_push($rule->wdays, $wday);
					}
				}
			}
		}
		if ($type == 'rt') {
			//adjust rates
			$rule->updown = $rt_updown;
			$rule->amount = $rt_amount;
			$rule->pcent = $rt_pcent;
			$rule->gtlt = $rt_gtlt;
			$rule->units = $rt_number;
			$rule->daysadv = $rt_updown == 'down' ? $rt_daysadv : 0;
			$rule->ibeotas = $rt_where;
			$rule->units_count = $rt_units;
		} else {
			//adjust availability
			$rule->type = $av_type;
			$rule->number = $av_number;
			/**
			 * Availability rules of type "block dates" support excluded dates array
			 * 
			 * @since 	1.8.3
			 */
			if (is_array($av_excl_dates) && count($av_excl_dates)) {
				$rule->excl_dates = $av_excl_dates;
			}
		}

		$rule_db = json_encode($rule);
		$result = 0;
		$current_record = array();

		if ($whereup > 0) {
			//Update Rule
			$q = "SELECT * FROM `#__vikchannelmanager_balancer_rules` WHERE `id`=".$whereup.";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$current_record = $dbo->loadAssoc();
			} else {
				VikError::raiseWarning('', 'Rule ID not found for update.');
				return 0;
			}
			$q = "UPDATE `#__vikchannelmanager_balancer_rules` SET `name`=".$dbo->quote($rule_name).", ".($to_ts != $current_record['to_ts'] || $rule_db != $current_record['rule'] ? '`mod_ts`='.time().', ' : '')."`type`=".$dbo->quote($type).", `from_ts`=".$from_ts.", `to_ts`=".$to_ts.", `rule`=".$dbo->quote($rule_db)." WHERE `id`=".$whereup.";";
			$dbo->setQuery($q);
			$dbo->execute();
			$result = $whereup;
			//delete current rooms relations
			$q = "DELETE FROM `#__vikchannelmanager_balancer_rooms` WHERE `rule_id`=".$whereup.";";
			$dbo->setQuery($q);
			$dbo->execute();
			//create new rooms relations
			$sel_rooms = $type == 'rt' ? $rt_rooms : $av_rooms;
			foreach ($sel_rooms as $room_id) {
				if (empty($room_id)) {
					continue;
				}
				$q = "INSERT INTO `#__vikchannelmanager_balancer_rooms` (`rule_id`, `room_id`) VALUES (".$whereup.", ".(int)$room_id.");";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		} else {
			//Create New Rule
			$q = "INSERT INTO `#__vikchannelmanager_balancer_rules` (`name`, `mod_ts`, `type`, `from_ts`, `to_ts`, `rule`) VALUES (".$dbo->quote($rule_name).", ".time().", ".$dbo->quote($type).", ".$from_ts.", ".$to_ts.", ".$dbo->quote($rule_db).");";
			$dbo->setQuery($q);
			$dbo->execute();
			$result = $dbo->insertId();
			if (!empty($result)) {
				//create new rooms relations
				$sel_rooms = $type == 'rt' ? $rt_rooms : $av_rooms;
				foreach ($sel_rooms as $room_id) {
					if (empty($room_id)) {
						continue;
					}
					$q = "INSERT INTO `#__vikchannelmanager_balancer_rooms` (`rule_id`, `room_id`) VALUES (".(int)$result.", ".(int)$room_id.");";
					$dbo->setQuery($q);
					$dbo->execute();
				}
			} else {
				VikError::raiseWarning('', 'Unable to save the rule.');
				return 0;
			}
		}

		if ($type == 'rt') {
			$this->sendRuleToServer($current_record, $result, $to_ts, $rule, $rule_db);
		}

		return $result;
	}

	private function sendRuleToServer($current_record, $result, $to_ts, $rule, $rule_db) {
		//send the XML request to e4jConnect to add/modify/remove the SmartBalancer rule(s)
		$tz_offset = date('Z');
		
		$e4jc_url = "https://e4jconnect.com/channelmanager/?r=smbal&c=generic";
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- SmartBalancer Request e4jConnect.com - VikChannelManager - VikBooking -->
<SmartBalancerRQ xmlns="http://www.e4jconnect.com/avail/smbalrq">
	<Notify client="'.JUri::root().'"/>
	<Api key="'.VikChannelManager::getApiKey(true).'"/>'."\n";

		$action_type = count($current_record) > 0 ? 'modify' : 'add';

		if ($result < 0 && count($current_record) > 0) {
			//$current_record in this case contains all the IDs to be removed
			$action_type = 'remove';
			//multiple riles per time in the XML when removing rules
			foreach ($current_record as $k => $v) {
				if (intval($v) > 0) {
					$xml .= '	<SmartBalancer action="'.$action_type.'" tzoffset="'.$tz_offset.'" >
		<Rule id="'.$v.'" type="X" until="'.date('Y-m-d', $to_ts).'" />
	</SmartBalancer>'."\n";
				}
			}
		} else {
			if (count($current_record) > 0) {
				//check if the update request should be sent to e4jConnect (if some changes were made)
				if ($to_ts == $current_record['to_ts'] && $rule_db == $current_record['rule']) {
					//nothing to update: same rule parameters and until date
					return true;
				}
			}
			//one rule per time in the XML when adding or updating a rule on the servers
			$rule_type = '';
			//Rule type is composed of 3 chars: 1. A (Days in Advance) or E (Everyday) - 2. U (Up, increase rates) or D (Down, decrease rates) - 3. L (Less than x units left) or G (Greater than x units left)
			$rule_type .= $rule->daysadv > 0 ? 'A' : 'E';
			$rule_type .= $rule->updown == 'up' ? 'U' : 'D';
			$rule_type .= $rule->gtlt == 'lt' ? 'L' : 'G';
			$xml .= '	<SmartBalancer action="'.$action_type.'" tzoffset="'.$tz_offset.'" >
		<Rule id="'.$result.'" type="'.$rule_type.'" until="'.date('Y-m-d', $to_ts).'" />
	</SmartBalancer>'."\n";
		}

		$xml .= '</SmartBalancerRQ>';

		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		$rs = $e4jC->exec();
		if ($e4jC->getErrorNo()) {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($e4jC->getErrorMsg()));
			return;
		}
		if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
			return;
		}

	}

	public function rmsmartbalancer() {
		$mainframe = JFactory::getApplication();
		$ids = VikRequest::getVar('cid', array(0));
	
		if (count($ids)) {
			$dbo = JFactory::getDbo();
			foreach ($ids as $id){
				$q="DELETE FROM `#__vikchannelmanager_balancer_rules` WHERE `id`=".intval($id)." LIMIT 1;";
				$dbo->setQuery($q);
				$dbo->execute();
				$q="DELETE FROM `#__vikchannelmanager_balancer_rooms` WHERE `rule_id`=".intval($id).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
			$this->sendRuleToServer($ids, -1, time(), '', '');
		}

		$mainframe->redirect("index.php?option=com_vikchannelmanager&task=smartbalancer");
	}

	public function cancelsmartbalancer() {
		JFactory::getApplication()->redirect("index.php?option=com_vikchannelmanager&task=smartbalancer");
	}

	public function load_fests_regions() {
		//called via Ajax by the Wizard
		$festObj = VikChannelManager::getFestivitiesInstance();
		
		echo json_encode($festObj->getTranslatedRegions());
		exit;
	}

	public function load_festivities() {
		//called via Ajax by the Wizard
		$region = VikRequest::getString('region', '', 'request');
		$wdays_map = array(
			JText::_('VCMJQCALSUN'),
			JText::_('VCMJQCALMON'),
			JText::_('VCMJQCALTUE'),
			JText::_('VCMJQCALWED'),
			JText::_('VCMJQCALTHU'),
			JText::_('VCMJQCALFRI'),
			JText::_('VCMJQCALSAT')
		);
		
		$festObj = VikChannelManager::getFestivitiesInstance();
		$all_fests = $festObj->loadFestivities($region);
		//format the timestamps into readable dates and add info for the Wizard
		foreach ($all_fests as $k => $v) {
			$all_fests[$k]['next_day'] = date('Y-m-d', $v['next_ts']);
			$all_fests[$k]['from_day'] = date('Y-m-d', $v['from_ts']);
			$all_fests[$k]['to_day'] = date('Y-m-d', $v['to_ts']);
			$from_info = getdate($v['from_ts']);
			$to_info = getdate($v['to_ts']);
			$all_fests[$k]['week_day'] = addslashes($wdays_map[(int)$v['wday']]);
			$all_fests[$k]['from_week_day'] = addslashes($wdays_map[(int)$from_info['wday']]);
			$all_fests[$k]['to_week_day'] = addslashes($wdays_map[(int)$to_info['wday']]);
			$all_fests[$k]['date_diff'] = VikChannelManager::formatDate($all_fests[$k]['next_day']);
		}

		echo json_encode($all_fests);
		exit;
	}

	public function load_fest_occupancy() {
		//called via Ajax by the Wizard
		$dbo = JFactory::getDbo();
		$occupancy = array();

		$now_info = getdate();
		$now = mktime(0, 0, 0, $now_info['mon'], $now_info['mday'], $now_info['year']);
		$from_date = VikRequest::getString('from_date', '', 'request');
		$to_date = VikRequest::getString('to_date', '', 'request');
		if (empty($from_date) || empty($to_date)) {
			echo 'e4j.error.Missing Dates';
			exit;
		}
		//get timestamps from and to, and number of days for the festivities
		$fest_num_days = 1;
		$from_ts = strtotime($from_date);
		$to_info = getdate(strtotime($to_date));
		$to_ts = mktime(23, 59, 59, $to_info['mon'], $to_info['mday'], $to_info['year']);
		if ($from_date != $to_date && $from_ts < $to_ts) {
			$ts_start_info = getdate($from_ts);
			$fest_num_days = 0;
			while ($ts_start_info[0] < $to_ts) {
				$fest_num_days++;
				$ts_start_info = getdate(mktime(0, 0, 0, $ts_start_info['mon'], ($ts_start_info['mday'] + 1), $ts_start_info['year']));
			}
		}
		//count in how many days the festivity will be
		$in_days = floor(abs($from_ts - $now) / 86400);
		//get total number of units
		$q = "SELECT SUM(`units`) AS `tot` FROM `#__vikbooking_rooms` WHERE `avail`=1;";
		$dbo->setQuery($q);
		$dbo->execute();
		$tot_rooms_units = (int)$dbo->loadResult();
		if (!($tot_rooms_units > 0)) {
			echo 'e4j.error.No active rooms';
			exit;
		}
		//count number of nights booked
		$nights_booked = 0;
		$all_bookings = array();
		$q = "SELECT `b`.`id`,`b`.`idroom`,`b`.`checkin`,`b`.`checkout`,`ob`.`idorder`,`ob`.`idbusy` 
			FROM `#__vikbooking_busy` AS `b` LEFT JOIN `#__vikbooking_ordersbusy` AS `ob` ON `b`.`id`=`ob`.`idbusy` 
			WHERE (
				(`b`.`checkin` >= ".$from_ts." AND `b`.`checkin` <= ".$to_ts.") OR 
				(`b`.`checkout` >= ".$from_ts." AND `b`.`checkout` <= ".$to_ts.") OR 
				(`b`.`checkin` < ".$from_ts." AND `b`.`checkout` > ".$to_ts.")
			);";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$bookings = $dbo->loadAssocList();
			foreach ($bookings as $b) {
				if (!in_array($b['idorder'], $all_bookings)) {
					$all_bookings[] = $b['idorder'];
				}
				$oinfo_start = getdate($b['checkin']);
				$oinfo_end = getdate($b['checkout']);
				$ots_end = mktime(23, 59, 59, $oinfo_end['mon'], ($oinfo_end['mday'] - 1), $oinfo_end['year']);
				while ($oinfo_start[0] < $ots_end) {
					if ($oinfo_start[0] >= $from_ts && $oinfo_start[0] <= $to_ts) {
						$nights_booked++;
					}
					if ($oinfo_start[0] > $to_ts) {
						break;
					}
					$oinfo_start = getdate(mktime(0, 0, 0, $oinfo_start['mon'], ($oinfo_start['mday'] + 1), $oinfo_start['year']));
				}
			}
		}
		$nights_booked = $nights_booked > ($tot_rooms_units * $fest_num_days) ? ($tot_rooms_units * $fest_num_days) : $nights_booked;
		//count percentage occupation
		$pcent_occupied = 100 * $nights_booked / ($tot_rooms_units * $fest_num_days);
		if (intval($pcent_occupied) != $pcent_occupied) {
			$pcent_occupied = number_format($pcent_occupied, 2, '.', '');
		}
		//calculate suggestions for rates adjustment
		$suggestion = '';
		$suggestionmsg = '';
		if ($in_days <= 60 && (float)$pcent_occupied > 60 && (float)$pcent_occupied < 80) {
			//less than 60 days to the festivity, occupancy greater than 60% & less than 80%: increase rates
			$suggestion = '+20%';
			$suggestionmsg = JText::_('VCMSMARTBALWIZARDSUGGINCRATES');
		} elseif ($in_days <= 60 && (float)$pcent_occupied >= 80) {
			//less than 60 days to the festivity, occupancy greater than 80%: increase rates
			$suggestion = '+30%';
			$suggestionmsg = JText::_('VCMSMARTBALWIZARDSUGGINCRATES');
		} elseif ($in_days <= 15 && (float)$pcent_occupied <= 60) {
			//less than 15 days to the festivity, occupancy less than 60%: discount rates
			$suggestion = '-20%';
			$suggestionmsg = JText::_('VCMSMARTBALWIZARDSUGGDISCRATES');
		} elseif ($in_days > 15 && $in_days <= 30 && (float)$pcent_occupied <= 35) {
			//16 to 30 days to festivity, occupancy less than 35%: discount rates
			$suggestion = '-20%';
			$suggestionmsg = JText::_('VCMSMARTBALWIZARDSUGGDISCRATES');
		}
		//calculates min. units left for 'gtlt'
		$min_gtlt = 1;
		if (strlen($suggestion) && substr($suggestion, 0, 1) == '-') {
			//discount suggested: min. units left should be greater than 10% of full availability
			$min_gtlt = ceil(($tot_rooms_units * 10 / 100));
		} elseif (strlen($suggestion) && substr($suggestion, 0, 1) == '+') {
			//increase suggested: min. units left should be less than 20% of full availability
			$min_gtlt = ceil(($tot_rooms_units * 20 / 100));
		}
		
		//compose array of return values
		$occupancy = array(
			'fest_num_days' => $fest_num_days,
			'tot_rooms_units' => $tot_rooms_units,
			'nights_booked' => $nights_booked,
			'all_bookings' => $all_bookings,
			'pcent_occupied' => $pcent_occupied,
			'in_days' => $in_days,
			'min_gtlt' => $min_gtlt,
			'suggestion' => $suggestion,
			'suggestionmsg' => $suggestionmsg
		);

		if (VikChannelManager::sleepAllowed()) {
			sleep(1);
		}

		echo json_encode($occupancy);
		exit;
	}

	public function reslogs() {
		VCM::printMenu();
	
		VikRequest::setVar('view', VikRequest::getCmd('view', 'reslogs'));
	
		parent::display();
		
		VCM::printFooter();
	}

	public function reviews() {
		VCM::printMenu();
	
		VikRequest::setVar('view', VikRequest::getCmd('view', 'reviews'));
	
		parent::display();
		
		VCM::printFooter();
	}

	public function reviews_download() {
		$dbo = JFactory::getDbo();
		$mainframe = JFactory::getApplication();
		$session = JFactory::getSession();

		/**
		 * Optional parameters when calling this task via AJAX (VBO).
		 * Downloading the reviews "everywhere" means only on Booking.com.
		 * 
		 * @since 	1.8.0
		 */
		$return = VikRequest::getInt('return', 0, 'request');
		$uniquekey = VikRequest::getInt('uniquekey', 0, 'request');
		$everywhere = VikRequest::getInt('everywhere', 0, 'request');
		if ($everywhere > 0 && empty($uniquekey)) {
			/**
			 * VBO will pass everywhere=1 via AJAX, but we keep using just Booking.com
			 * to download the latest reviews, as Airbnb API will deliver the new reviews
			 * to VCM in real time. If not, the page Reviews when Airbnb API is the active
			 * channel will allow to retrieve the reviews only for this channel, but it's
			 * a manual action, not a periodic and automatic download made by VBO.
			 * 
			 * @since 	1.8.0
			 */
			$uniquekey = VikChannelManagerConfig::BOOKING;
		}
		//
		
		if (!function_exists('curl_init')) {
			if ($return) {
				echo 'e4j.error.Curl';
				exit;
			}
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap('e4j.error.Curl'));
			$mainframe->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}

		$apikey = VikChannelManager::getApiKey(true);
		if (empty($apikey)) {
			if ($return) {
				echo 'e4j.error.Settings';
				exit;
			}
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap('e4j.error.Settings'));
			$mainframe->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}

		// Check execution limit
		$sess_lim = $session->get('vcmRevwDownload', '');
		$sess_lim = (int)$sess_lim;
		if (!empty($sess_lim)) {
			$exec_lim = floor(VikChannelManager::getProLevel() / 3);
			$exec_lim = $exec_lim > 2 ? $exec_lim : 2;
			if ($sess_lim >= $exec_lim) {
				if ($return) {
					echo 'e4j.error.' . JText::_('VCMREVDOWNLIMEXCEED');
					exit;
				}
				VikError::raiseWarning('', JText::_('VCMREVDOWNLIMEXCEED'));
				$mainframe->redirect("index.php?option=com_vikchannelmanager&task=reviews");
				exit;
			}
		}
		$sess_lim++;
		$session->set('vcmRevwDownload', $sess_lim);
		//

		// gather all the accounts information
		$accounts_info = array();

		// requested channel details or active channel details
		$channel = !empty($uniquekey) ? VikChannelManager::getChannel($uniquekey) : VikChannelManager::getActiveModule(true);
		
		if (is_array($channel) && $channel) {
			// decode channel parameters
			$channel['params'] = json_decode($channel['params'], true);

			// obtain the account(s) details
			$q = "SELECT DISTINCT `prop_params`,`prop_name` FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel`=" . (int)$channel['uniquekey'] . ";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$prop_data = $dbo->loadAssocList();
				foreach ($prop_data as $pdata) {
					if (empty($pdata['prop_params']) || empty($pdata['prop_name'])) {
						continue;
					}
					$pdata['prop_params'] = json_decode($pdata['prop_params'], true);
					$account_info = array(
						'prop_params' => $pdata['prop_params'],
						'prop_name'	  => $pdata['prop_name'],
					);
					foreach ($pdata['prop_params'] as $paramk => $paramv) {
						if (!isset($account_info['hotelid'])) {
							// first parameter is what we need
							$account_info['prop_first_param'] = $paramv;
							$account_info['hotelid'] = $paramv;
						}
						if (empty($uniquekey)) {
							// just for the active channel account, not for all accounts (manual download button)
							foreach ($channel['params'] as $chk => $chv) {
								// the very first channel param key must match with the mapping data (i.e. 'hotelid' or 'user_id')
								if ($paramk == $chk && $paramv == $chv) {
									// get the channel params for the active account (reviews downloaded for just one account ID)
									$account_info['prop_first_param'] = $paramv;
									$account_info['hotelid'] = $paramv;
									$account_info['prop_name'] = $pdata['prop_name'];
									array_push($accounts_info, $account_info);
									// we break all loops as we've found what we need
									break 3;
								}
							}
						} else {
							// push this account into the queue
							array_push($accounts_info, $account_info);
						}
						break;
					}
				}
			}
		}

		if (!$accounts_info) {
			if ($return) {
				echo 'e4j.error.No valid accounts';
				exit;
			}
			VikError::raiseWarning('', 'No accounts found');
			$mainframe->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}

		/**
		 * In case of more than 5 accounts, we will attempt to download the reviews
		 * for the currently active account data to avoid execution timeouts.
		 * 
		 * @since 	1.9.2
		 */
		if (count($accounts_info) > 5 && $uniquekey == VikChannelManagerConfig::BOOKING) {
			$active_channel = VikChannelManager::getActiveModule(true);
			if ($active_channel['uniquekey'] == ($channel['uniquekey'] ?? null)) {
				$active_params = (array) json_decode($active_channel['params'], true);
				$find_hotel_id = $active_params['hotelid'] ?? '';
				$prepend_account = [];
				foreach ($accounts_info as $k => $account_info) {
					if ($account_info['hotelid'] == $find_hotel_id) {
						// save the account to prepend to the list
						$prepend_account = $account_info;
						// unset it on this position
						unset($accounts_info[$k]);
						// reset key values
						$accounts_info = array_values($accounts_info);
						// stop the loop
						break;
					}
				}
				if ($prepend_account) {
					// ensure the active account goes first
					array_unshift($accounts_info, $prepend_account);
					// shorten the list of accounts to parse
					$accounts_info = array_slice($accounts_info, 0, 5);
				}
			}
		}

		// loop over all accounts to make the requests and obtain the reviews
		foreach ($accounts_info as $k => $account_info) {
			// required filter by hotel ID
			$filters = array('hotelid="'.$account_info['hotelid'].'"');

			// optional filter by date
			$fromd = VikRequest::getString('fromd', '', 'request');
			if (!empty($fromd)) {
				$fromts = strtotime($fromd);
				if (!empty($fromts) && $fromts < time()) {
					// prevent invalid or in the future dates as filter
					array_push($filters, 'fromdate="'.$fromd.'"');
				}
			}

			// optional filter by Review ID
			$revid = VikRequest::getString('revid', '', 'request');
			if (!empty($revid)) {
				array_push($filters, 'revid="'.$revid.'"');
			}

			// adjust the channel name for TripConnect on e4jConnect
			$usech = $channel['name'];
			if ($channel['uniquekey'] == VikChannelManagerConfig::TRIP_CONNECT) {
				$usech = 'tripadvisor';
			}

			// make the request to e4jConnect to read the reviews
			$e4jc_url = "https://e4jconnect.com/channelmanager/?r=rrevw&c=".$usech;
			$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager RREVW Request e4jConnect.com - '.ucwords($channel['name']).' -->
<ReadReviewsRQ xmlns="http://www.e4jconnect.com/channels/rrevwrq">
	<Notify client="'.JUri::root().'"/>
	<Api key="'.$apikey.'"/>
	<ReadReviews>
		<Fetch '.implode(' ', $filters).'/>
	</ReadReviews>
</ReadReviewsRQ>';
			
			$e4jC = new E4jConnectRequest($e4jc_url);
			$e4jC->setPostFields($xml);
			$e4jC->slaveEnabled = true;
			$rs = $e4jC->exec();
			if ($e4jC->getErrorNo()) {
				if ($return) {
					echo 'e4j.error.' . @curl_error($e4jC->getCurlHeader());
					exit;
				}
				VikError::raiseWarning('', @curl_error($e4jC->getCurlHeader()));
				$mainframe->redirect("index.php?option=com_vikchannelmanager&task=reviews");
				exit;
			}
			if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
				if ($return) {
					echo $rs;
					exit;
				}
				VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
				$mainframe->redirect("index.php?option=com_vikchannelmanager&task=reviews");
				exit;
			}

			/**
			 * The unserialized response must be an array of stdClass objects.
			 * A string would mean an invalid response.
			 */
			$reviews = unserialize($rs);
			if ($reviews === false || !is_array($reviews)) {
				if ($return) {
					echo 'e4j.error.invalid response: ' . $rs;
					exit;
				}
				VikError::raiseWarning('', 'Invalid response.<br/>'.$rs);
				$mainframe->redirect("index.php?option=com_vikchannelmanager&task=reviews");
				exit;
			}

			// push reviews for this account
			if (!isset($accounts_info[$k]['reviews'])) {
				$accounts_info[$k]['reviews'] = array();
			}

			$accounts_info[$k]['reviews'] = array_merge($accounts_info[$k]['reviews'], $reviews);
		}

		// total new reviews
		$tot_new = 0;

		// load all review IDs for this channel to avoid double records
		$current_reviews = array();
		$q = "SELECT `review_id` FROM `#__vikchannelmanager_otareviews` WHERE `uniquekey`=".($channel['uniquekey']).";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			$allrevs = $dbo->loadAssocList();
			foreach ($allrevs as $r) {
				array_push($current_reviews, $r['review_id']);
			}
		}

		// parse all reviews obtained
		$set_status = $channel['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI ? 0 : 1;
		foreach ($accounts_info as $k => $account_info) {
			if (!count($account_info['reviews'])) {
				continue;
			}

			// attempt to get and update the global score
			$global_score = null;

			// parse all reviews returned
			foreach ($account_info['reviews'] as $review) {
				if (!is_object($review) || (empty($review->review_id) && empty($review->global_score))) {
					// review object must have a valid review_id property or must be a global_score
					continue;
				}
				if (!empty($review->global_score)) {
					// this is the global score
					$global_score = $review;
					continue;
				}
				if (in_array($review->review_id, $current_reviews)) {
					// this review already exists, skip it
					continue;
				}
				if (empty($review->dt)) {
					$review->dt = JFactory::getDate()->toSql(true);
				}
				if (!empty($review->content) && !is_scalar($review->content)) {
					// raw content is automatically converted into a JSON string for saving
					if (isset($review->content->reply)) {
						// we make sure the reply property goes as the last element
						try {
							$now_reply = $review->content->reply;
							unset($review->content->reply);
							$review->content->reply = $now_reply;
						} catch (Exception $e) {
							// do nothing
						}
						//
					}
					$review->content = json_encode($review->content);
				}
				// check if the review belongs to a specific ID Order OTA
				if (!isset($review->idorder) && isset($review->idorderota) && !empty($review->idorderota)) {
					// check if this booking exists from this channel
					$q = "SELECT `id` FROM `#__vikbooking_orders` WHERE `idorderota`=".$dbo->quote($review->idorderota)." AND `channel` LIKE ".$dbo->quote('%'.$channel['name'].'%').";";
					$dbo->setQuery($q);
					$dbo->execute();
					if ($dbo->getNumRows()) {
						// set the property idorder
						$review->idorder = $dbo->loadResult();
					}
				}
				// immediately increase the counter for the new reviews
				$tot_new++;
				// insert row
				$q = "INSERT INTO `#__vikchannelmanager_otareviews` (`review_id`, `prop_first_param`, `prop_name`, `channel`, `uniquekey`, `idorder`, `dt`, `customer_name`, `lang`, `score`, `country`, `content`, `published`) " .
					"VALUES (".$dbo->quote($review->review_id).", ".(!empty($account_info['prop_first_param']) ? $dbo->quote($account_info['prop_first_param']) : 'NULL').", ".(!empty($account_info['prop_name']) ? $dbo->quote($account_info['prop_name']) : 'NULL').", ".$dbo->quote($channel['name']).", ".$dbo->quote($channel['uniquekey']).", ".(isset($review->idorder) ? $dbo->quote($review->idorder) : 'NULL').", ".$dbo->quote($review->dt).", ".(!empty($review->customer_name) ? $dbo->quote($review->customer_name) : 'NULL').", ".(!empty($review->lang) ? $dbo->quote($review->lang) : 'NULL').", ".(!empty($review->score) ? $dbo->quote($review->score) : '0').", ".(!empty($review->country) ? $dbo->quote($review->country) : 'NULL').", ".(!empty($review->content) ? $dbo->quote($review->content) : 'NULL').", {$set_status});";
				$dbo->setQuery($q);
				$dbo->execute();
				// push OTA review ID to current queue to avoid duplicate values
				array_push($current_reviews, $review->review_id);
			}

			if (is_object($global_score)) {
				// update or insert the global score for this property
				$prev_score_id = null;
				$q = "SELECT `id` FROM `#__vikchannelmanager_otascores` WHERE `prop_first_param`=".$dbo->quote($account_info['prop_first_param'])." AND `uniquekey`=".$dbo->quote($channel['uniquekey']).";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows()) {
					$prev_score_id = $dbo->loadResult();
				}
				$last_upd = JFactory::getDate()->toSql(true);
				// raw content is automatically converted into a JSON string for saving
				$score_content = json_encode($global_score->content);
				//
				if (!empty($prev_score_id)) {
					// update
					$q = "UPDATE `#__vikchannelmanager_otascores` SET `last_updated`=".$dbo->quote($last_upd).", `score`=".$dbo->quote($global_score->score).", `content`=".$dbo->quote($score_content)." WHERE `id`=".(int)$prev_score_id.";";
				} else {
					// insert
					$q = "INSERT INTO `#__vikchannelmanager_otascores` (`prop_first_param`, `prop_name`, `channel`, `uniquekey`, `last_updated`, `score`, `content`) " . 
						"VALUES (".(!empty($account_info['prop_first_param']) ? $dbo->quote($account_info['prop_first_param']) : 'NULL').", ".(!empty($account_info['prop_name']) ? $dbo->quote($account_info['prop_name']) : 'NULL').", ".$dbo->quote($channel['name']).", ".$dbo->quote($channel['uniquekey']).", ".$dbo->quote($last_upd).", ".$dbo->quote($global_score->score).", ".$dbo->quote($score_content).");";
				}
				$dbo->setQuery($q);
				$dbo->execute();
			}

		}

		if ($return) {
			echo 'e4j.ok.' . $tot_new;
			exit;
		}
		$mainframe->enqueueMessage(JText::sprintf('VCMNEWTOTREVIEWS', $tot_new));
		$mainframe->redirect("index.php?option=com_vikchannelmanager&task=reviews");
		exit;
	}

	public function toggle_review_status() {
		$dbo = JFactory::getDbo();
		$mainframe = JFactory::getApplication();

		$ids = VikRequest::getVar('cid', array(0));
		$valid_ids = array();
		foreach ($ids as $revid) {
			if (!empty($revid)) {
				array_push($valid_ids, (int)$revid);
			}
		}

		if (count($valid_ids)) {
			$q = "SELECT `id`,`published` FROM `#__vikchannelmanager_otareviews` WHERE `id` IN (".implode(', ', $valid_ids).");";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows()) {
				$revs = $dbo->loadAssocList();
				foreach ($revs as $rev) {
					$q = "UPDATE `#__vikchannelmanager_otareviews` SET `published`=".($rev['published'] > 0 ? '0' : '1')." WHERE `id`=".$rev['id'].";";
					$dbo->setQuery($q);
					$dbo->execute();
				}
			}
		}

		$mainframe->redirect("index.php?option=com_vikchannelmanager&task=reviews");
		exit;
	}

	public function removereviews() {
		$dbo = JFactory::getDbo();
		$mainframe = JFactory::getApplication();

		$ids = VikRequest::getVar('cid', array(0));
		foreach ($ids as $revid) {
			$q = "DELETE FROM `#__vikchannelmanager_otareviews` WHERE `id`=".(int)$revid.";";
			$dbo->setQuery($q);
			$dbo->execute();
		}

		$mainframe->redirect("index.php?option=com_vikchannelmanager&task=reviews");
		exit;
	}

	/**
	 * This method is no longer being used.
	 * 
	 * @deprecated 	1.9
	 * @see 		task=review.reply
	 */
	public function review_reply() {
		$dbo = JFactory::getDbo();
		$mainframe = JFactory::getApplication();
		
		if (!function_exists('curl_init')) {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap('e4j.error.Curl'));
			$mainframe->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}

		$apikey = VikChannelManager::getApiKey(true);
		if (empty($apikey)) {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap('e4j.error.Settings'));
			$mainframe->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}

		// VCM Review ID value is mandatory
		$vcm_revid = VikRequest::getInt('review_id', '', 'request');
		if (empty($vcm_revid)) {
			VikError::raiseWarning('', '1. Missing required data');
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=reviews");
			exit;
		}

		// Review reply text cannot be empty
		$reply_text = VikRequest::getString('reply_text', '', 'request');
		if (empty($reply_text)) {
			VikError::raiseWarning('', '2. Missing required data');
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=reviews");
			exit;
		}

		// load VCM review
		$q = "SELECT * FROM `#__vikchannelmanager_otareviews` WHERE `id`=" . $vcm_revid . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			VikError::raiseWarning('', '3. Review not found');
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=reviews");
			exit;
		}
		$current_review = $dbo->loadAssoc();

		if (!empty($current_review['channel'])) {
			// OTA review

			// load the channel for this review
			$channel = VikChannelManager::getChannel($current_review['uniquekey']);
			$channel['params'] = json_decode($channel['params'], true);
			$channel['params'] = is_array($channel['params']) ? $channel['params'] : array();

			// get the first parameter, which may not be 'hotelid'
			$usehid = '';
			foreach ($channel['params'] as $v) {
				$usehid = $v;
				break;
			}

			// make sure the params saved for this channel match the account ID of the review
			if ((string)$usehid != (string)$current_review['prop_first_param']) {
				// we need to find the proper params even though changing the hotel ID would be sufficient for most channels
				$q = "SELECT `prop_params` FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel`=" . $dbo->quote($current_review['uniquekey']) . " AND `prop_params` LIKE " . $dbo->quote('%' . $current_review['prop_first_param'] . '%');
				$dbo->setQuery($q, 0, 1);
				$dbo->execute();
				if (!$dbo->getNumRows()) {
					VikError::raiseWarning('', 'No rooms mapped for Account ID ' . $current_review['prop_first_param']);
					$mainframe->redirect("index.php?option=com_vikchannelmanager&task=reviews");
					exit;
				}
				// overwrite channel params with the account requested
				$channel['params'] = json_decode($dbo->loadResult(), true);
				$channel['params'] = is_array($channel['params']) ? $channel['params'] : array();
				// get the first parameter, which may not be 'hotelid'
				foreach ($channel['params'] as $v) {
					$usehid = $v;
					break;
				}
			}
			
			// required filter by hotel ID
			$filters = array('hotelid="' . trim($usehid) . '"');

			// OTA Review ID filter is mandatory
			$revid = VikRequest::getString('ota_review_id', '', 'request');
			if (empty($revid)) {
				VikError::raiseWarning('', '4. Missing required data');
				$mainframe->redirect("index.php?option=com_vikchannelmanager&task=reviews");
				exit;
			}
			array_push($filters, 'revid="'.$revid.'"');

			// adjust the channel name for TripConnect on e4jConnect
			$usech = $channel['name'];
			if ($channel['uniquekey'] == VikChannelManagerConfig::TRIP_CONNECT) {
				$usech = 'tripadvisor';
			}

			// make the request to e4jConnect to reply to the review
			$e4jc_url = "https://e4jconnect.com/channelmanager/?r=rprew&c=".$usech;
			$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager RREVW Request e4jConnect.com - '.ucwords($channel['name']).' -->
<ReadReviewsRQ xmlns="http://www.e4jconnect.com/channels/rrevwrq">
	<Notify client="'.JUri::root().'"/>
	<Api key="'.$apikey.'"/>
	<ReadReviews>
		<Fetch '.implode(' ', $filters).'/>
		<Reply><![CDATA['.$reply_text.']]></Reply>
	</ReadReviews>
</ReadReviewsRQ>';
			
			$e4jC = new E4jConnectRequest($e4jc_url);
			$e4jC->setPostFields($xml);
			$rs = $e4jC->exec();
			if ($e4jC->getErrorNo()) {
				VikError::raiseWarning('', @curl_error($e4jC->getCurlHeader()));
				$mainframe->redirect("index.php?option=com_vikchannelmanager&task=reviews");
				exit;
			}
			if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
				VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
				$mainframe->redirect("index.php?option=com_vikchannelmanager&task=reviews");
				exit;
			}

			/**
			 * The response should be a string.
			 */
			if (strpos($rs, 'e4j.ok') === false) {
				VikError::raiseWarning('', 'Invalid response.<br/>'.$rs);
				$mainframe->redirect("index.php?option=com_vikchannelmanager&task=reviews");
				exit;
			}
		}

		// update the content of this review with the reply message so that no other replies will be allowed
		$new_rev_content = !empty($current_review['content']) ? json_decode($current_review['content']) : new stdClass;
		$new_rev_content = !is_object($new_rev_content) ? new stdClass : $new_rev_content;
		$new_rev_content->reply = isset($new_rev_content->reply) && is_object($new_rev_content->reply) ? $new_rev_content->reply : new stdClass;
		if (empty($current_review['channel'])) {
			// website review we update the "reply" property
			$new_rev_content->reply = $reply_text;
		} elseif ($channel['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI) {
			// Airbnb API OTA review, we update the "reviewee_response" property
			$new_rev_content->reviewee_response = $reply_text;
			unset($new_rev_content->reply);
		} else {
			// OTA review, we update the "text" property in "reply"
			$new_rev_content->reply->text = $reply_text;
		}
		
		$current_review['content'] = json_encode($new_rev_content);
		$q = "UPDATE `#__vikchannelmanager_otareviews` SET `content`=" . $dbo->quote($current_review['content']) . " WHERE `id`=" . $current_review['id'] . ";";
		$dbo->setQuery($q);
		$dbo->execute();

		// redirect
		$mainframe->enqueueMessage(JText::_('VCMREVREPLYSUCCESS'));
		$mainframe->redirect("index.php?option=com_vikchannelmanager&task=reviews");
		exit;
	}

	public function opportunities() {
		VCM::printMenu();
	
		VikRequest::setVar('view', VikRequest::getCmd('view', 'opportunities'));
	
		parent::display();
		
		VCM::printFooter();
	}

	public function opportunity_action() {
		$dbo = JFactory::getDbo();
		$mainframe = JFactory::getApplication();
		$opp = VikChannelManager::getOpportunityInstance();
		
		if (!function_exists('curl_init')) {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap('e4j.error.Curl'));
			$mainframe->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}

		$apikey = VikChannelManager::getApiKey(true);
		if (empty($apikey)) {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap('e4j.error.Settings'));
			$mainframe->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}

		// VCM Opportunity ID value is mandatory
		$opp_id = VikRequest::getInt('opp_id', 0, 'request');
		$action = VikRequest::getInt('action', 0, 'request');
		if (empty($opp_id)) {
			VikError::raiseWarning('', '1. Missing required data');
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=opportunities");
			exit;
		}

		// load VCM opportunity
		$vcm_opp = $opp->loadOpportunities(array('id' => $opp_id), 0, 1);
		if (!count($vcm_opp)) {
			VikError::raiseWarning('', '2. Opportunity not found');
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=opportunities");
			exit;
		}
		$current_opp = $vcm_opp[0];
		$current_opp->data = json_decode($current_opp->data);
		$current_opp->data = !is_object($current_opp->data) ? (new stdClass) : $current_opp->data;

		// get channel involved for this opportunity
		$channel_info = VikChannelManager::getChannelFromName($current_opp->channel);
		if (!is_array($channel_info) || !count($channel_info)) {
			VikError::raiseWarning('', '3. Opportunity Channel not found');
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=opportunities");
			exit;
		}
		$uniquekey = $channel_info['uniquekey'];

		// some specific input fields may be received for the opportunity
		$opp_fields   = VikRequest::getVar('opp_fields', array(), 'request', 'array');
		$opp_listings = VikRequest::getVar('opp_listings', array(), 'request', 'array');

		// default to Slave endpoint
		$e4jc_url = "https://slave.e4jconnect.com/channelmanager/?r=oppa&c=".$current_opp->channel;

		// update status and action no matter what action was selected or what type of opportunity is this
		$q = "UPDATE `#__vikchannelmanager_opportunities` SET `status`=1, `action`={$action} WHERE `id`={$current_opp->id};";
		$dbo->setQuery($q);
		$dbo->execute();

		// check if the opportunity requires a redirect to a given URL
		$opp_needs_redirect = false;
		$opp_redirect_url = null;
		if ($uniquekey == VikChannelManagerConfig::BOOKING) {
			$opp_needs_redirect = (isset($current_opp->data->implementation) && stripos($current_opp->data->implementation, 'redirect') !== false);
			$opp_redirect_url = $opp_needs_redirect ? $current_opp->data->url : $opp_redirect_url;
		} elseif ($uniquekey == VikChannelManagerConfig::AIRBNBAPI) {
			$opp_needs_redirect = (!isset($current_opp->data->activation_modes) || !is_array($current_opp->data->activation_modes) || !in_array('API', $current_opp->data->activation_modes));
			$opp_needs_redirect = ($opp_needs_redirect && isset($current_opp->data->activation_url));
			$opp_redirect_url = $opp_needs_redirect ? $current_opp->data->activation_url : $opp_redirect_url;

			if ($action === -1) {
				// dismissing an Airbnb opportunity should simply update the action on the db (just done above), no calls needed
				$mainframe->enqueueMessage(rtrim(JText::_('MSG_BASE_SUCCESS'), '!') . '!');
				$mainframe->redirect("index.php?option=com_vikchannelmanager&task=opportunities");
				exit;
			}
		}

		if ($opp_needs_redirect) {
			// Booking.com implementation type REDIRECT - Airbnb activation mode "MANUAL"

			if ($uniquekey == VikChannelManagerConfig::BOOKING && ($action === -1 || $action === 2)) {
				// Booking.com: dismiss/done action for REDIRECT opportunities requires a call to e4jConnect

				if ($action === 2) {
					$act_str = 'DONE';
				} else {
					$act_str = 'DISMISS';
				}

				// make the request to e4jConnect to action the opportunity
				$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager OPPA Request e4jConnect.com - '.ucwords($current_opp->channel).' -->
<OpportunityActionRQ xmlns="http://www.e4jconnect.com/channels/opparq">
	<Notify client="'.JUri::root().'"/>
	<Api key="'.$apikey.'"/>
	<Action type="' . $act_str . '" hid="' . $current_opp->prop_first_param . '">
		<Opportunity>' . $current_opp->identifier . '</Opportunity>
	</Action>
</OpportunityActionRQ>';
				
				$e4jC = new E4jConnectRequest($e4jc_url);
				$e4jC->setPostFields($xml);
				$e4jC->slaveEnabled = true;
				$rs = $e4jC->exec();
				if ($e4jC->getErrorNo()) {
					VikError::raiseWarning('', @curl_error($e4jC->getCurlHeader()));
					$mainframe->redirect("index.php?option=com_vikchannelmanager&task=opportunities");
					exit;
				}
				if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
					VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
					$mainframe->redirect("index.php?option=com_vikchannelmanager&task=opportunities");
					exit;
				}
				/**
				 * The response should be a string.
				 */
				if (strpos($rs, 'e4j.ok') === false) {
					VikError::raiseWarning('', 'Invalid response.<br/>'.$rs);
					$mainframe->redirect("index.php?option=com_vikchannelmanager&task=opportunities");
					exit;
				}

				// reload page and print a generic success message for the Dismiss/Done action
				$mainframe->enqueueMessage(rtrim(JText::_('MSG_BASE_SUCCESS'), '!') . '!');
				$mainframe->redirect("index.php?option=com_vikchannelmanager&task=opportunities");
				exit;
			}

			if (empty($opp_redirect_url)) {
				VikError::raiseWarning('', 'Unable to redirect to an empty URL for the opportunity');
				$mainframe->redirect("index.php?option=com_vikchannelmanager&task=opportunities");
				exit;
			}

			// redirect to given URL when "implement" was clicked
			$mainframe->redirect($opp_redirect_url);
			exit;
		}

		// Booking.com: implementation type TOGGLE - Airbnb: API activation mode

		if ($action === 1) {
			$act_str = 'ENABLE';
		} elseif ($action === 2) {
			$act_str = 'DONE';
		} else {
			$act_str = 'DISMISS';
		}

		// set up main node depending on the opportunity channel
		$opp_node_attr = '';
		$opp_node_cont = '';
		if ($uniquekey == VikChannelManagerConfig::AIRBNBAPI) {
			if ($action !== 1) {
				// this point should not have been reached, as with Airbnb we can only implement
				VikError::raiseWarning('', 'Nothing to do with this opportunity');
				$mainframe->redirect("index.php?option=com_vikchannelmanager&task=opportunities");
				exit;
			}
			// attribute entity_ids contains the opportunity identifier
			$opp_node_attr = ' entity_ids="' . $current_opp->identifier . '"';
			// node content contains the input fields to apply the opportunity and all listings involved
			$node_cont_obj = new stdClass;
			$node_cont_obj->input_fields = $opp_fields;
			$node_cont_obj->listing_ids = $opp_listings;
			$opp_node_cont = '<![CDATA[' . json_encode($node_cont_obj) . ']]>';
		} elseif ($uniquekey == VikChannelManagerConfig::BOOKING) {
			// opportunity identifier is passed along with the node content (no entity_ids attr needed)
			$opp_node_cont = $current_opp->identifier;
		}
		
		// make the request to e4jConnect to action the opportunity
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager OPPA Request e4jConnect.com - '.ucwords($current_opp->channel).' -->
<OpportunityActionRQ xmlns="http://www.e4jconnect.com/channels/opparq">
	<Notify client="'.JUri::root().'"/>
	<Api key="'.$apikey.'"/>
	<Action type="' . $act_str . '" hid="' . $current_opp->prop_first_param . '">
		<Opportunity' . $opp_node_attr . '>' . $opp_node_cont . '</Opportunity>
	</Action>
</OpportunityActionRQ>';
		
		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		$e4jC->slaveEnabled = true;
		$rs = $e4jC->exec();
		if ($e4jC->getErrorNo()) {
			// restore original object record or the opportunity will be gone
			$dbo->updateObject('#__vikchannelmanager_opportunities', $vcm_opp[0], 'id');
			// set error and redirect
			VikError::raiseWarning('', @curl_error($e4jC->getCurlHeader()));
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=opportunities");
			exit;
		}
		if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			// restore original object record or the opportunity will be gone
			$dbo->updateObject('#__vikchannelmanager_opportunities', $vcm_opp[0], 'id');
			// set error and redirect
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=opportunities");
			exit;
		}
		/**
		 * The response should be a string.
		 */
		if (strpos($rs, 'e4j.ok') === false) {
			// restore original object record or the opportunity will be gone
			$dbo->updateObject('#__vikchannelmanager_opportunities', $vcm_opp[0], 'id');
			// set error and redirect
			VikError::raiseWarning('', 'Invalid response.<br/>'.$rs);
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=opportunities");
			exit;
		}

		// redirect
		$mainframe->enqueueMessage(rtrim(JText::_('MSG_BASE_SUCCESS'), '!') . '!');
		$mainframe->redirect("index.php?option=com_vikchannelmanager&task=opportunities");
		exit;
	}

	public function diagnostic() {
		VCM::printMenu();
	
		VikRequest::setVar('view', VikRequest::getCmd('view', 'diagnostic'));
	
		parent::display();
		
		VCM::printFooter();
	}
	
	public function ordervbfromsid() {
		$sid = VikRequest::getVar('sid', '');
		$id = 0;
		
		$dbo = JFactory::getDbo();
		$q = "SELECT `id` FROM `#__vikbooking_orders` WHERE `confirmnumber`=".$dbo->quote($sid)." LIMIT 1";
		$dbo->setQuery($q);
		$dbo->execute();
		if ( $dbo->getNumRows() > 0 ) {
			$id = $dbo->loadResult();
		}
		
		$mainframe = JFactory::getApplication();
		$mainframe->redirect('index.php?option=com_vikbooking&task=editorder&cid[]='.$id.'&tmpl=component');
	}
	
	public function notification() {
		VikRequest::setVar('view', VikRequest::getCmd('view', 'notification'));
	
		parent::display();
	}
	
	public function rooms_rel_rplan() {
		VikRequest::setVar('view', VikRequest::getCmd('view', 'roomsrelrplan'));
	
		parent::display();
		
		exit;
	}

	public function rooms_rel_rplan_setdef() {
		$dbo = JFactory::getDbo();

		$reldata = VikRequest::getString('reldata', '', 'request');
		$rel_parts = explode('_', $reldata);

		$q = "SELECT `vcmr`.*,`vbr`.`name`,`vbr`.`img`,`vbr`.`smalldesc` FROM `#__vikchannelmanager_roomsxref` AS `vcmr` LEFT JOIN `#__vikbooking_rooms` `vbr` ON `vcmr`.`idroomvb`=`vbr`.`id` WHERE `vcmr`.`id`=".(int)$rel_parts[0]." ORDER BY `vbr`.`name` ASC, `vcmr`.`channel` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		$roomsrel = $dbo->getNumRows() > 0 ? $dbo->loadAssoc() : array();

		if (!array_key_exists('otapricing', $roomsrel) || empty($roomsrel['otapricing'])) {
			echo 'e4j.error.Relation Not Found or Empty';
			exit;
		}
		$roomsrel['otapricing'] = json_decode($roomsrel['otapricing'], true);
		if (!is_array($roomsrel['otapricing']) || !is_array($roomsrel['otapricing']['RatePlan']) || !array_key_exists($rel_parts[1], $roomsrel['otapricing']['RatePlan'])) {
			echo 'e4j.error.Rate Plan Not Found';
			exit;
		}

		foreach ($roomsrel['otapricing']['RatePlan'] as $rplan_k => $rplan_v) {
			if (array_key_exists('vcm_default', $rplan_v) && $rel_parts[1] != $rplan_k) {
				unset($roomsrel['otapricing']['RatePlan'][$rplan_k]['vcm_default']);
			} elseif (!array_key_exists('vcm_default', $rplan_v) && $rel_parts[1] == $rplan_k) {
				$roomsrel['otapricing']['RatePlan'][$rplan_k]['vcm_default'] = 1;
			}
		}
		$roomsrel['otapricing'] = json_encode($roomsrel['otapricing']);
		$q = "UPDATE `#__vikchannelmanager_roomsxref` SET `otapricing`=".$dbo->quote($roomsrel['otapricing'])." WHERE `id`=".(int)$rel_parts[0].";";
		$dbo->setQuery($q);
		$dbo->execute();

		echo 'e4j.ok';
		
		exit;
	}
	
	public function exec_par_products() {
		VikRequest::setVar('view', VikRequest::getCmd('view', 'execparproducts'));
	
		parent::display();
		
		exit;
	}
	
	public function exec_rar_rq() {
		VikRequest::setVar('view', VikRequest::getCmd('view', 'execrarrq'));
	
		parent::display();
	
		exit;
	}

	public function execpcid() {
		VikRequest::setVar('view', VikRequest::getCmd('view', 'execpcid'));
	
		parent::display();
	}

	public function exec_avpush() {
		if (!function_exists('curl_init')) {
			echo 'e4j.error.'.VikChannelManager::getErrorFromMap('e4j.error.Curl');
			exit;
		}
		$apikey = VikChannelManager::getApiKey(true);
		$dbo = JFactory::getDbo();
		$session = JFactory::getSession();
		$cookie = JFactory::getApplication()->input->cookie;
		
		//Check executions limit
		$prev_avpush = array();
		$cookie_avpush = $cookie->get('vcmAvpushData', '', 'string');
		if (!empty($cookie_avpush)) {
			$prev_avpush = json_decode($cookie_avpush, true);
			if (is_array($prev_avpush) && array_key_exists('a', $prev_avpush) && array_key_exists('t', $prev_avpush)) {
				$elapsed = $prev_avpush['t'] - time();
				if ($elapsed >= 0 && $elapsed < (3600 * 12) && $prev_avpush['a'] >= (VikChannelManager::getProLevel() * 2)) {
					echo 'e4j.error.'.VikChannelManager::getErrorFromMap( 'e4j.error.Channels.AVPUSH_Busy:'.ceil(($elapsed / 3600)) );
					exit;
				}
			}
		}
		//

		$req = VikRequest::getString('r', '', 'request');
		$rooms = VikRequest::getVar('rooms', array());
		$channels = VikRequest::getVar('channels', array());
		$nodes = VikRequest::getVar('nodes', array());
		//pre-validation
		if (empty($req) || !$rooms || !$channels || !$nodes) {
			echo 'e4j.error.'.VikChannelManager::getErrorFromMap('e4j.error.Missing Data');
			exit;
		}
		if (count($rooms) != count($channels) || count($rooms) != count($nodes)) {
			echo 'e4j.error.'.VikChannelManager::getErrorFromMap('e4j.error.Request Values do no match');
			exit;
		}
		//
		list($req_count, $req_length) = explode('_', $req);
		$req_count = intval($req_count);
		$req_length = intval($req_length);
		if ($req_count <= 0 || $req_length <= 0) {
			echo 'e4j.error.'.VikChannelManager::getErrorFromMap('e4j.error.Invalid Request Data');
			exit;
		}

		$xmlNodes = '';
		foreach ($rooms as $ind => $idroom) {
			if (empty($idroom) || !isset($channels[$ind]) || empty($channels[$ind]) || !isset($nodes[$ind]) || empty($nodes[$ind])) {
				continue;
			}
			$q = "SELECT `c`.`uniquekey`,`r`.`idroomota`, `r`.`idchannel`, `r`.`otapricing`, `r`.`prop_params` FROM `#__vikchannelmanager_channel` AS `c`, `#__vikchannelmanager_roomsxref` AS `r` WHERE `c`.`uniquekey`=`r`.`idchannel` AND `c`.`av_enabled`=1 AND `r`.`idroomvb`=".(int)$idroom." AND `c`.`uniquekey` IN (".$channels[$ind].");";
			$dbo->setQuery($q);
			$rows = $dbo->loadAssocList();
			if ($rows) {
				foreach ($rows as $row) {
					$hotelid = '';
					if (!empty($row['prop_params'])) {
						$prop_info = json_decode($row['prop_params'], true);
						if (isset($prop_info['hotelid'])) {
							$hotelid = $prop_info['hotelid'];
						} elseif (isset($prop_info['id'])) {
							// useful for Pitchup.com to identify multiple accounts
							$hotelid = $prop_info['id'];
						} elseif (isset($prop_info['apikey'])) {
							// useful for Pitchup.com, but it may be a good backup field for future channels to identify multiple accounts
							$hotelid = $prop_info['apikey'];
						} elseif (isset($prop_info['property_id'])) {
							// useful for Hostelworld
							$hotelid = $prop_info['property_id'];
						} elseif (isset($prop_info['user_id'])) {
							// useful for Airbnb API
							$hotelid = $prop_info['user_id'];
						}
					}
					$cust = explode(';', $nodes[$ind]);
					foreach ($cust as $det) {
						$rateplanid = '0';
						if ((int)$row['idchannel'] == (int)VikChannelManagerConfig::AGODA && !empty($row['otapricing'])) {
							$ota_pricing = json_decode($row['otapricing'], true);
							if (count($ota_pricing) > 0 && array_key_exists('RatePlan', $ota_pricing)) {
								foreach ($ota_pricing['RatePlan'] as $rp_id => $rp_val) {
									$rateplanid = $rp_id;
									break;
								}
							}
						}
						list($from_date, $to_date, $tot_units) = explode('_', trim($det));
						$xmlNodes .= "\t\t".'<RoomType id="'.$row['idroomota'].'" rateplanid="'.$rateplanid.'" idchannel="'.$row['idchannel'].'" newavail="'.$tot_units.'"'.(!empty($hotelid) ? ' hotelid="'.$hotelid.'"' : '').'>'."\n";
						$xmlNodes .= "\t\t\t".'<Day from="'.$from_date.'" to="'.$to_date.'"/>'."\n";
						$xmlNodes .= "\t\t".'</RoomType>'."\n";
					} 
				}
			}
		}

		if (empty($xmlNodes)) {
			echo 'e4j.error.'.VikChannelManager::getErrorFromMap('e4j.error.Invalid Request Nodes');
			exit;
		}

		$nkey = $session->get('vcmAvpushNkey', '0000');
		
		$e4jc_url = "https://e4jconnect.com/channelmanager/?r=custa&c=channels";
		
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager AVPUSH CUSTA Request e4jConnect.com - Channels Module extensionsforjoomla.com -->
<CustAvailUpdateRQ xmlns="http://www.e4jconnect.com/channels/custarq">
	<Notify client="'.JUri::root().'" nkey="'.$nkey.'"/>
	<Api key="'.$apikey.'"/>
	<AvailUpdate>'."\n";
		$xml .= $xmlNodes;
		$xml .= "\t".'</AvailUpdate>
</CustAvailUpdateRQ>';
		
		$debug_val = VikRequest::getInt('e4j_debug', '', 'request');
		if ($debug_val == 1) {
			echo 'e4j.error.'.htmlentities($xml);
			exit;
		}

		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		$e4jC->slaveEnabled = true;
		$rs = $e4jC->exec();
		if ($e4jC->getErrorNo()) {
			echo 'e4j.error.'.VikChannelManager::getErrorFromMap('e4j.error.Curl:Error #'.$e4jC->getErrorNo().' '.$e4jC->getErrorMsg());
			exit;
		}
		if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			echo 'e4j.error.'.VikChannelManager::getErrorFromMap($rs);
			exit;
		}

		echo 'e4j.OK.'.JText::_('VCMAVPUSHRQNODESENTOK');
		
		exit;
	}

	public function exec_avpush_prepare() {
		$dbo = JFactory::getDbo();
		$session = JFactory::getSession();

		$nkey = $session->get('vcmAvpushNkey', '0000');
		//save notification
		$avpushcusta_details = $session->get('vcmAvpushDetails', '');
		$esitstr = 'e4j.OK.Channels.AVPUSHCUSTAR_RQ'."\n".$avpushcusta_details;
		$q = "INSERT INTO `#__vikchannelmanager_notifications` (`ts`,`type`,`from`,`cont`,`read`) VALUES('".time()."', '1', 'VCM', ".$dbo->quote($esitstr).", 0);";
		$dbo->setQuery($q);
		$dbo->execute();
		$id_notification = $dbo->insertId();
		VikChannelManager::updateNKey($nkey, $id_notification);
		$session->set('vcmAvpushNId', $id_notification);
		$session->set('vcmAvpushDetails', '');

		exit;
	}

	public function exec_avpush_finalize() {
		$dbo = JFactory::getDbo();
		$session = JFactory::getSession();
		$cookie = JFactory::getApplication()->input->cookie;

		$id_notification = $session->get('vcmAvpushNId', '');
		$result = VikRequest::getInt('res', '', 'request');

		if ($result > 0) {
			//Set executions limit cookie
			$prev_avpush = array();
			$cookie_avpush = $cookie->get('vcmAvpushData', '', 'string');
			if (!empty($cookie_avpush)) {
				$prev_avpush = json_decode($cookie_avpush, true);
			}
			if (is_array($prev_avpush) && array_key_exists('a', $prev_avpush) && array_key_exists('t', $prev_avpush)) {
				$prev_avpush['a'] += 1;
				if (method_exists('VikRequest', 'setCookie')) {
					/**
					 * @wponly 	if VBO is not updated, this method won't exist.
					 *
					 */
					VikRequest::setCookie('vcmAvpushData', json_encode($prev_avpush), $prev_avpush['t'], '/');
				} else {
					$cookie->set('vcmAvpushData', json_encode($prev_avpush), $prev_avpush['t'], '/');
				}
			} else {
				$cexp = (time() + (3600 * 12));
				if (method_exists('VikRequest', 'setCookie')) {
					/**
					 * @wponly 	if VBO is not updated, this method won't exist.
					 *
					 */
					VikRequest::setCookie('vcmAvpushData', json_encode(array('a' => 1, 't' => $cexp)), $cexp, '/');
				} else {
					$cookie->set('vcmAvpushData', json_encode(array('a' => 1, 't' => $cexp)), $cexp, '/');
				}
			}
			//
		} else {
			//remove notification because only errors occurred in the ajax requests
			if (!empty($id_notification)) {
				$q = "DELETE FROM `#__vikchannelmanager_notifications` WHERE `id`=".(int)$id_notification.";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$session->set('vcmAvpushNId', '');

		exit;
	}

	public function exec_ratespush() {
		if (!function_exists('curl_init')) {
			echo 'e4j.error.'.VikChannelManager::getErrorFromMap('e4j.error.Curl');
			exit;
		}
		
		//Check executions limit
		$cookie = JFactory::getApplication()->input->cookie;
		$prev_ratespush = array();
		$cookie_ratespush = $cookie->get('vcmRatespushData', '', 'string');
		if (!empty($cookie_ratespush)) {
			$prev_ratespush = json_decode($cookie_ratespush, true);
			if (is_array($prev_ratespush) && array_key_exists('a', $prev_ratespush) && array_key_exists('t', $prev_ratespush)) {
				$elapsed = $prev_ratespush['t'] - time();
				if ($elapsed >= 0 && $elapsed < (3600 * 12) && $prev_ratespush['a'] >= (VikChannelManager::getProLevel() * 2)) {
					echo 'e4j.error.'.VikChannelManager::getErrorFromMap( 'e4j.error.Channels.RATESPUSH_Busy:'.ceil(($elapsed / 3600)) );
					exit;
				}
			}
		}

		// make sure the script will run with no limitation
		@set_time_limit(0);

		$req = VikRequest::getString('r', '', 'request');
		$rooms = VikRequest::getVar('rooms', array());
		$channels = VikRequest::getVar('channels', array());
		$chrplans = VikRequest::getVar('chrplans', array());
		$nodes = VikRequest::getVar('nodes', array());
		$pushvars = VikRequest::getVar('v', array());
		//pre-validation
		if (empty($req) || !(count($rooms) > 0) || !(count($channels) > 0) || !(count($nodes) > 0) || !(count($chrplans) > 0) || !(count($pushvars) > 0)) {
			echo 'e4j.error.'.VikChannelManager::getErrorFromMap('e4j.error.Missing Data');
			exit;
		}
		$tot_rooms = count($rooms);
		if ($tot_rooms != count($channels) || $tot_rooms != count($nodes) || $tot_rooms != count($chrplans) || $tot_rooms != count($pushvars)) {
			echo 'e4j.error.'.VikChannelManager::getErrorFromMap('e4j.error.Request Values do no match');
			exit;
		}
		//
		list($req_count, $req_length) = explode('_', $req);
		$req_count = intval($req_count);
		$req_length = intval($req_length);
		if ($req_count <= 0 || $req_length <= 0) {
			echo 'e4j.error.'.VikChannelManager::getErrorFromMap('e4j.error.Invalid Request Data');
			exit;
		}

		//invoke the connector to compose and make the request to e4jConnect
		$vboConnector = VikChannelManager::getVikBookingConnectorInstance();
		$result = $vboConnector->channelsRatesPush($channels, $chrplans, $nodes, $rooms, $pushvars);

		if ($vc_error = $vboConnector->getError(true)) {
			echo $vc_error;
			exit;
		}

		//print the json-encoded array to terminate the Ajax request
		echo $result;
		exit;
	}

	public function exec_ratespush_finalize() {
		$session = JFactory::getSession();
		$cookie = JFactory::getApplication()->input->cookie;

		$result = VikRequest::getInt('res', '', 'request');
		$session->set('vcmRatespushDerpr', '');

		if ($result > 0) {
			//Set executions limit cookie
			$prev_ratespush = array();
			$cookie_ratespush = $cookie->get('vcmRatespushData', '', 'string');
			if (!empty($cookie_ratespush)) {
				$prev_ratespush = json_decode($cookie_ratespush, true);
			}
			if (is_array($prev_ratespush) && array_key_exists('a', $prev_ratespush) && array_key_exists('t', $prev_ratespush)) {
				$prev_ratespush['a'] += 1;
				if (method_exists('VikRequest', 'setCookie')) {
					/**
					 * @wponly 	if VBO is not updated, this method won't exist.
					 *
					 */
					VikRequest::setCookie('vcmRatespushData', json_encode($prev_ratespush), $prev_ratespush['t'], '/');
				} else {
					$cookie->set('vcmRatespushData', json_encode($prev_ratespush), $prev_ratespush['t'], '/');
				}
			} else {
				$cexp = (time() + (3600 * 12));
				if (method_exists('VikRequest', 'setCookie')) {
					/**
					 * @wponly 	if VBO is not updated, this method won't exist.
					 *
					 */
					VikRequest::setCookie('vcmRatespushData', json_encode(array('a' => 1, 't' => $cexp)), $cexp, '/');
				} else {
					$cookie->set('vcmRatespushData', json_encode(array('a' => 1, 't' => $cexp)), $cexp, '/');
				}
			}
			//
		}

		exit;
	}

	public function exec_acmp_rq() {
		$response = 'e4j.error.Generic';

		$session = JFactory::getSession();
		$cookie = JFactory::getApplication()->input->cookie;
		$dbo = JFactory::getDbo();

		$acmp_debug = false;
		$debug_val = VikRequest::getInt('e4j_debug', '', 'request');
		if ($debug_val == 1) {
			$acmp_debug = true;
		}

		$exclude_rids = array();
		$excludeids = VikRequest::getString('excludeids', '', 'request');
		if (!empty($excludeids)) {
			$exclude_rids = explode(',', rtrim($excludeids, ','));
			foreach ($exclude_rids as $k => $v) {
				$exid = intval(str_replace(';', '', $v));
				if (!($exid > 0)) {
					unset($exclude_rids[$k]);
				}
				$exclude_rids[$k] = $exid;
			}
		}
		
		if (!function_exists('curl_init')) {
			echo 'e4j.error.'.VikChannelManager::getErrorFromMap('e4j.error.Curl');
			exit;
		}
		$apikey = VikChannelManager::getApiKey(true);
		if (empty($apikey) ) {
			echo 'e4j.error.'.VikChannelManager::getErrorFromMap('e4j.error.Settings');
			exit;
		}

		$fromdate = VikRequest::getString('from', '', 'request');
		if (empty($fromdate)) {
			echo 'e4j.error.'.VikChannelManager::getErrorFromMap('e4j.error');
			exit;
		}
		$limitts = strtotime(date('Y-m-d'));
		$fromts = strtotime($fromdate);
		$fromdate = $fromts < $limitts ? date('Y-m-d') : $fromdate;

		$from_info = getdate(strtotime($fromdate));
		$tots = mktime(0, 0, 0, ($from_info['mon'] + 1), $from_info['mday'], $from_info['year']);
		$todate = date('Y-m-d', $tots);

		$rooms_xref = array();

		//to make this query compatible with the strict mode, we have added the DISTINCT Optimization, and we have replaced the clause GROUP BY `r`.`idroomvb`, `r`.`idchannel`
		$q = "SELECT DISTINCT `r`.*, `c`.`name` AS `chname`, `c`.`uniquekey`, `b`.`name` AS `roomname` 
			FROM `#__vikchannelmanager_roomsxref` AS `r`, `#__vikchannelmanager_channel` AS `c`, `#__vikbooking_rooms` AS `b` 
			WHERE `b`.`id`=`r`.`idroomvb` AND `r`.`idchannel`=`c`.`uniquekey` AND `c`.`av_enabled`=1 AND 
			`c`.`uniquekey`!=" . (int)VikChannelManagerConfig::GOOGLEHOTEL . (count($exclude_rids) ? ' AND `r`.`idroomvb` NOT IN ('.implode(',', $exclude_rids).')' : '')." ORDER BY `c`.`uniquekey` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			$rooms_xref = $dbo->loadAssocList();
		}
		if (!count($rooms_xref)) {
			if (VikChannelManager::hasGoogleHotelChannel()) {
				echo 'e4j.error.No Relations found with channels supporting this request type - Google Hotel does not support this feature';
			} else {
				echo 'e4j.error.No Relations found with channels supporting this request type - synchronize the rooms first, or ignore this tool if none of your channels support it';
			}
			exit;
		}

		//check old session value
		$skip_call = false;
		$sess_acmp = $session->get('vcmExecAcmpRs', '');
		if (!empty($sess_acmp) && is_array($sess_acmp)) {
			if ($fromdate == $sess_acmp['fromdate'] && is_array($sess_acmp['acmp']) && count($sess_acmp['acmp'])) {
				$skip_call = true;
			}
		}

		$channel_rooms = array();
		$channel_names_map = array();
		$ota_rooms_vbo_map = array();
		foreach ($rooms_xref as $xref) {
			$channel_names_map[$xref['uniquekey']] = $xref['channel'];
			$ota_rooms_vbo_map[$xref['idroomota']] = $xref['idroomvb'];
			$rateplanid = '0';
			if (((int)$xref['uniquekey'] == (int)VikChannelManagerConfig::AGODA || (int)$xref['uniquekey'] == (int)VikChannelManagerConfig::YCS50) && !empty($xref['otapricing'])) {
				$ota_pricing = json_decode($xref['otapricing'], true);
				if (count($ota_pricing) > 0 && array_key_exists('RatePlan', $ota_pricing)) {
					foreach ($ota_pricing['RatePlan'] as $rp_id => $rp_val) {
						$rateplanid = $rp_id;
						break;
					}
				}
			}
			$prop_params = array();
			if (!empty($xref['prop_params'])) {
				$prop_params = json_decode($xref['prop_params'], true);
			}
			$channel_rooms[$xref['uniquekey']][] = array('roomid' => $xref['idroomota'], 'rateplanid' => $rateplanid, 'vbroomid' => $xref['idroomvb'], 'prop_params' => $prop_params);
		}

		if (!$skip_call) {
			$prev_acmp = array();
			$cookie_acmp = $cookie->get('vcmAcmpData', '', 'string');
			if (!empty($cookie_acmp)) {
				$prev_acmp = json_decode($cookie_acmp, true);
				if (is_array($prev_acmp) && array_key_exists('a', $prev_acmp) && array_key_exists('t', $prev_acmp)) {
					$elapsed = $prev_acmp['t'] - time();
					if ($elapsed >= 0 && $elapsed < 3600 && $prev_acmp['a'] >= (VikChannelManager::getProLevel() * 4)) {
						echo 'e4j.error.'.VikChannelManager::getErrorFromMap('e4j.error.Channels.ACMP_Busy:'.ceil(($elapsed / 60)).';;'.(!empty($sess_acmp['fromdate']) ? $sess_acmp['fromdate'] : '-------'));
						exit;
					}
				}
			}

			$e4jc_url = "https://e4jconnect.com/channelmanager/?r=acmp&c=channels";

			$xmlRQ = '<?xml version="1.0" encoding="UTF-8"?>
<!-- ACMP Request e4jConnect.com - VikChannelManager - VikBooking -->
<AvailCompareRQ xmlns="http://www.e4jconnect.com/avail/acmprq">
	<Notify client="'.JUri::root().'"/>
	<Api key="'.$apikey.'"/>
	<AvailCompare from="'.$fromdate.'" to="'.$todate.'">'."\n";
			foreach ($channel_rooms as $idchannel => $rooms) {
				$xmlRQ .= '<Channel id="'.$idchannel.'">'."\n";
				foreach ($rooms as $ch_room) {
					$send_hotelid = '';
					if (isset($ch_room['prop_params']['hotelid'])) {
						$send_hotelid = $ch_room['prop_params']['hotelid'];
					} elseif (isset($ch_room['prop_params']['property_id'])) {
						$send_hotelid = $ch_room['prop_params']['property_id'];
					} elseif (isset($ch_room['prop_params']['user_id'])) {
						$send_hotelid = $ch_room['prop_params']['user_id'];
					}
					$xmlRQ .= '<RoomType roomid="'.$ch_room['roomid'].'" rateplanid="'.$ch_room['rateplanid'].'" vbroomid="'.$ch_room['vbroomid'].'"'.(!empty($send_hotelid) ? ' hotelid="'.$send_hotelid.'"' : '').'/>'."\n";
				}
				$xmlRQ .= '</Channel>'."\n";
			}
			$xmlRQ .= '</AvailCompare>
</AvailCompareRQ>';
			
			$e4jC = new E4jConnectRequest($e4jc_url);
			$e4jC->setPostFields($xmlRQ);
			$rs = $e4jC->exec();
			if ($e4jC->getErrorNo()) {
				echo 'e4j.error.'.VikChannelManager::getErrorFromMap('e4j.error.Curl:Error #'.$e4jC->getErrorNo().' '.@curl_error($e4jC->getCurlHeader()));
				exit;
			}
			if (substr($rs, 0, 9) == 'e4j.error') {
				echo 'e4j.error.'.VikChannelManager::getErrorFromMap($rs);
				exit;
			}
			
			$jsondata = json_decode($rs, true);
			$json_err = false;
			if (function_exists('json_last_error')) {
				$json_err = (bool)(json_last_error() !== JSON_ERROR_NONE);
			}
			if ($jsondata === null || $json_err || !(@count($jsondata) > 0)) {
				echo 'e4j.error.Bad Response, please report to e4jConnect ('.date('c').')';
				exit;
			}

			//Update session values and cookie
			$sess_acmp = array('fromdate' => $fromdate, 'ts' => time(), 'acmp' => $jsondata);
			$session->set('vcmExecAcmpRs', $sess_acmp);
			if (is_array($prev_acmp) && array_key_exists('a', $prev_acmp) && array_key_exists('t', $prev_acmp)) {
				$prev_acmp['a'] += 1;
				if (method_exists('VikRequest', 'setCookie')) {
					/**
					 * @wponly 	if VBO is not updated, this method won't exist.
					 *
					 */
					VikRequest::setCookie('vcmAcmpData', json_encode($prev_acmp), $prev_acmp['t'], '/');
				} else {
					$cookie->set('vcmAcmpData', json_encode($prev_acmp), $prev_acmp['t'], '/');
				}
			} else {
				$cexp = (time() + 3600);
				if (method_exists('VikRequest', 'setCookie')) {
					/**
					 * @wponly 	if VBO is not updated, this method won't exist.
					 *
					 */
					VikRequest::setCookie('vcmAcmpData', json_encode(array('a' => 1, 't' => $cexp)), $cexp, '/');
				} else {
					$cookie->set('vcmAcmpData', json_encode(array('a' => 1, 't' => $cexp)), $cexp, '/');
				}
			}
			//

		} else {
			$jsondata = $sess_acmp['acmp'];
		}
		
		$response = array();
		foreach ($jsondata as $e4jc_channel_id => $ota_rooms_avail) {
			$channel_name = $channel_names_map[$e4jc_channel_id];
			$channel_name = ucwords($channel_name);
			//check if channel returned an error
			if (!is_array($ota_rooms_avail)) {
				if (substr($ota_rooms_avail, 0, 9) == 'e4j.error') {
					if (!isset($response['errors'])) {
						$response['errors'] = '';
					} else {
						$response['errors'] .= "\n";
					}
					$response['errors'] .= $channel_name.': '.VikChannelManager::getErrorFromMap($ota_rooms_avail);
				}
				continue;
			}
			//
			foreach ($ota_rooms_avail as $ota_room_id => $avail) {
				$vbo_room_key = $ota_rooms_vbo_map[$ota_room_id];
				if (empty($vbo_room_key) || empty($channel_name)) {
					continue;
				}
				//check if channel returned an error
				if (!is_array($avail)) {
					if (substr($avail, 0, 9) == 'e4j.error') {
						if (!isset($response['errors'])) {
							$response['errors'] = '';
						} else {
							$response['errors'] .= "\n";
						}
						$response['errors'] .= $channel_name.': '.VikChannelManager::getErrorFromMap($avail);
						continue;
					}
				}
				//
				if (!array_key_exists($vbo_room_key, $response)) {
					$response[$vbo_room_key] = array();
				}
				if (!array_key_exists($channel_name, $response[$vbo_room_key])) {
					$response[$vbo_room_key][$channel_name] = array();
				}
				$response[$vbo_room_key][$channel_name] = $avail;
			}
		}

		if (array_key_exists('errors', $response) && !(count($response) > 1)) {
			//only errors from e4jConnect
			$response = 'e4j.error.'.$response['errors'];
		} else {
			//no errors or maybe just for some channels
			$response = json_encode($response);
		}

		if ($acmp_debug === true) {
			$response = '<pre>'."Plain Request:\n".htmlentities($xmlRQ)."\n\nArray Response:\n".print_r($jsondata, true)."\n\nWorked Array for JS:\n".print_r($response, true).'</pre>';
		}
		
		echo $response;	
		exit;
	}

	public function get_vbo_dayrates() {
		$dbo = JFactory::getDbo();
		if (!class_exists('VikBooking')) {
			require_once(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'lib.vikbooking.php');
		}
		$vbo_df = VikBooking::getDateFormat();
		$df = $vbo_df == "%d/%m/%Y" ? 'd/m/Y' : ($vbo_df == "%m/%d/%Y" ? 'm/d/Y' : 'Y/m/d');
		$fromdate = VikRequest::getString('from', '', 'request');
		$todate = VikRequest::getString('to', '', 'request');
		$roomid = VikRequest::getInt('room', '', 'request');
		$start_ts = strtotime($fromdate);
		$end_ts = strtotime($todate);
		if (empty($fromdate) || empty($start_ts) || empty($todate) || empty($end_ts) || empty($roomid)) {
			echo 'e4j.error.Missing Request Data';
			exit;
		}
		$roomrates = array();
		//read the rates for the lowest number of nights
		//the old query below used to cause an error #1055 when sql_mode=only_full_group_by
		//$q = "SELECT `r`.*,`p`.`name` FROM `#__vikbooking_dispcost` AS `r` INNER JOIN (SELECT MIN(`days`) AS `min_days` FROM `#__vikbooking_dispcost` WHERE `idroom`=".(int)$roomid." GROUP BY `idroom`) AS `r2` ON `r`.`days`=`r2`.`min_days` LEFT JOIN `#__vikbooking_prices` `p` ON `p`.`id`=`r`.`idprice` WHERE `r`.`idroom`=".(int)$roomid." GROUP BY `r`.`idprice` ORDER BY `r`.`days` ASC, `r`.`cost` ASC;";
		$q = "SELECT `r`.`id`,`r`.`idroom`,`r`.`days`,`r`.`idprice`,`r`.`cost`,`p`.`name` FROM `#__vikbooking_dispcost` AS `r` INNER JOIN (SELECT MIN(`days`) AS `min_days` FROM `#__vikbooking_dispcost` WHERE `idroom`=".(int)$roomid." GROUP BY `idroom`) AS `r2` ON `r`.`days`=`r2`.`min_days` LEFT JOIN `#__vikbooking_prices` `p` ON `p`.`id`=`r`.`idprice` WHERE `r`.`idroom`=".(int)$roomid." GROUP BY `r`.`id`,`r`.`idroom`,`r`.`days`,`r`.`idprice`,`r`.`cost`,`p`.`name` ORDER BY `r`.`days` ASC, `r`.`cost` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$roomrates = $dbo->loadAssocList();
			foreach ($roomrates as $rrk => $rrv) {
				$roomrates[$rrk]['cost'] = round(($rrv['cost'] / $rrv['days']), 2);
				$roomrates[$rrk]['days'] = 1;
			}
		}
		//
		if (!(count($roomrates) > 0)) {
			echo 'e4j.error.No Rates in VikBooking';
			exit;
		}

		$pcheckinh = 0;
		$pcheckinm = 0;
		$pcheckouth = 0;
		$pcheckoutm = 0;
		$timeopst = VikBooking::getTimeOpenStore();
		if (is_array($timeopst)) {
			$opent = VikBooking::getHoursMinutes($timeopst[0]);
			$closet = VikBooking::getHoursMinutes($timeopst[1]);
			$pcheckinh = $opent[0];
			$pcheckinm = $opent[1];
			$pcheckouth = $closet[0];
			$pcheckoutm = $closet[1];
		}

		$current_rates = array();
		$infostart = getdate($start_ts);
		while ($infostart[0] > 0 && $infostart[0] <= $end_ts) {
			$today_tsin = VikBooking::getDateTimestamp(date($df, $infostart[0]), $pcheckinh, $pcheckinm);
			$today_tsout = VikBooking::getDateTimestamp(date($df, mktime(0, 0, 0, $infostart['mon'], ($infostart['mday'] + 1), $infostart['year'])), $pcheckouth, $pcheckoutm);

			$tars = VikBooking::applySeasonsRoom($roomrates, $today_tsin, $today_tsout);
			$current_rates[(date('Y-m-d', $infostart[0]))] = $tars;

			$infostart = getdate(mktime(0, 0, 0, $infostart['mon'], ($infostart['mday'] + 1), $infostart['year']));
		}
		if (!(count($current_rates) > 0)) {
			echo 'e4j.error.No Rates in VikBooking.';
			exit;
		}
		echo json_encode($current_rates);
		exit;
	}

	/**
	 * The use of this task has been deprecated just like the View that triggers it.
	 * 
	 * @deprecated 	1.8.3
	 */
	public function sendrar() {
		$dbo = JFactory::getDbo();
		$mainframe = JFactory::getApplication();
		$session = JFactory::getSession();
		$cookie = JFactory::getApplication()->input->cookie;
		$sess_rar = $session->get('vcmExecRarRs', '');
		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);
		$apikey = VikChannelManager::getApiKey(true);
		if (empty($apikey)) {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap('e4j.error.Settings'));
			$mainframe->redirect('index.php?option=com_vikchannelmanager&task=roomsrar');
			exit;
		}

		$ota_rooms = array();
		$prop_map = array();
		$q = "SELECT `vbr`.`id`,`vbr`.`name`,`vbr`.`img`,`vbr`.`units`,`vbr`.`smalldesc`,`vcmr`.`idroomvb`,`vcmr`.`idroomota`,`vcmr`.`channel`,`vcmr`.`otaroomname`,`vcmr`.`otapricing`,`vcmr`.`prop_params` FROM `#__vikbooking_rooms` AS `vbr` LEFT JOIN `#__vikchannelmanager_roomsxref` `vcmr` ON `vbr`.`id`=`vcmr`.`idroomvb` WHERE `vcmr`.`idchannel`=".$channel['uniquekey']." ORDER BY `vbr`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$vbrooms = $dbo->loadAssocList();
			foreach ($vbrooms as $rxref) {
				$ota_rooms[$rxref['idroomota']][] = $rxref;
				$prop_map[$rxref['idroomota']] = !empty($rxref['prop_params']) ? json_decode($rxref['prop_params'], true) : array();
			}
		} else {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap('e4j.error.No Rooms Relations found, unable to update the rates on the OTA'));
			$mainframe->redirect('index.php?option=com_vikchannelmanager&task=roomsrar');
			exit;
		}

		$los_sent = false;

		$e4jc_url = "https://e4jconnect.com/channelmanager/?r=rar&c=".$channel['name'];
		$rar_updates = array();

		if (is_array($sess_rar) && count($sess_rar) > 0) {
			$ids = VikRequest::getVar('cid', array(0));
			$currency = VikRequest::getString('currency');
			$dates = array();
			foreach ($ids as $date) {
				if (!empty($date)) $dates[] = $date;
			}
			if (count($dates) > 0) {
				$avail_rates = array();
				foreach ($sess_rar['rars']['AvailRate'] as $day => $rooms) {
					if (in_array($day, $dates)) {
						$avail_rates[$day] = $rooms;
					}
				}
				//Copy Inventory on some other dates
				$copy_inventory = array();
				$copy_inventory_ibe = array();
				$copy_requests = VikRequest::getVar('copyinventory', array());
				$copy_requests_where = VikRequest::getVar('copyinventorywhere', array());
				if (count($copy_requests) > 0) {
					foreach ($copy_requests as $crk => $copy_request) {
						if (!empty($copy_request)) {
							$copy_parts = explode(",", $copy_request);
							if (count($copy_parts) == 2 && strlen($copy_parts[0]) == 10 && strlen($copy_parts[1]) == 10) {
								$copy_inventory[$copy_parts[0]] = $copy_parts[1];
								//DEPRECATED AND NOT USED: the function below should copy the Rates (hardest for the RatePlans/Types of Price), the Inventory (easiest) and the Restrictions from VikBooking but it has to be implemented.
								$copy_inventory_ibe[$copy_parts[0]] = empty($copy_requests_where[$crk]) || !in_array($copy_requests_where[$crk], array('ota', 'ibe')) ? 'ota' : $copy_requests_where[$crk];
							}
						}
					}
				}
				//
				if (count($avail_rates) > 0) {
					unset($sess_rar['rars']['AvailRate']);
					$xmlRQ = '<?xml version="1.0" encoding="UTF-8"?>
<!-- RAR Request e4jConnect.com - VikChannelManager - VikBooking -->
<RarUpdateRQ xmlns="http://www.e4jconnect.com/avail/rarrq">
	<Notify client="'.JUri::root().'"/>
	<Api key="'.$apikey.'"/>
	<Currency name="'.$currency.'"/>'."\n";
					$updated_days = array();
					foreach ($avail_rates as $day => $rooms) {
						if (in_array($day, $updated_days)) {
							continue;
						}
						$to_day = $day;
						$rar_updates[$day] = array();
						$rar_updates_copy = array();
						if (array_key_exists($day, $copy_inventory)) {
							//Copy Inventory on the consecutive dates
							$day_start = strtotime($day);
							$day_end = strtotime($copy_inventory[$day]);
							if ($day_start < $day_end) {
								$to_day = $copy_inventory[$day];
								$is_dst = (bool)date('I', $day_start);
								while ($day_start < $day_end) {
									$day_start += 86400;
									//check if dst has changed
									$now_dst = (bool)date('I', $day_start);
									if ($is_dst !== $now_dst) {
										if ($now_dst === true) {
											$day_start -= 3600;
										} else {
											$day_start += 3600;
										}
										$is_dst = $now_dst;
									}
									array_push($updated_days, date('Y-m-d', $day_start));
									$rar_updates_copy[] = date('Y-m-d', $day_start);
								}
							}
						}
						$xmlRQ .= '<RarUpdate from="'.$day.'" to="'.$to_day.'">'."\n";
						foreach ($rooms as $kro => $room) {
							$rar_updates[$day][$room['id']] = $room;
							$ota_rate_plan = !empty($ota_rooms[$room['id']][key($ota_rooms[$room['id']])]['otapricing']) ? json_decode($ota_rooms[$room['id']][key($ota_rooms[$room['id']])]['otapricing'], true) : array();
							//Expedia RatePlans: when flexible products, only the parent or the derived RatePlan can be updated, not both
							$parent_rate_plans = array();
							$derived_rate_plans = array();
							$derived_rate_parents = array();
							if (array_key_exists('RatePlan', $ota_rate_plan) && $channel['uniquekey'] == VikChannelManagerConfig::EXPEDIA) {
								foreach ($ota_rate_plan['RatePlan'] as $rpkey => $rpval) {
									if (stripos($rpval['distributionModel'], 'expediacollect') !== false && (stripos($rpval['rateAcquisitionType'], 'derived') !== false || stripos($rpval['rateAcquisitionType'], 'netrate') !== false)) {
										if (count($parent_rate_plans) > 0) {
											foreach ($parent_rate_plans as $parent_rate_plan) {
												if (strpos((string)$parent_rate_plan, (string)$rpkey) !== false) {
													$derived_rate_plans[$parent_rate_plan][] = (string)$rpkey;
													$derived_rate_parents[] = (string)$rpkey;
													break;
												}
											}
										}
									} else {
										$parent_rate_plans[] = (string)$rpkey;
									}
								}
							}
							//
							$room_set_status = VikRequest::getString('roomstatus_'.$day.'_'.$room['id'], '', 'request');
							$room_status = strlen($room_set_status) == 0 ? $room['closed'] : (intval($room_set_status) == 1 ? 'false' : 'true');
							/**
							 * We also consider the value 'property_id' or 'user_id' rather than
							 * just 'hotelid' for channels like Hostelworld or Airbnb API.
							 * 
							 * @since 	1.6.22 & 1.8.0
							 */
							$send_hotelid = '';
							if (isset($prop_map[$room['id']]) && isset($prop_map[$room['id']]['hotelid'])) {
								$send_hotelid = $prop_map[$room['id']]['hotelid'];
							} elseif (isset($prop_map[$room['id']]) && isset($prop_map[$room['id']]['property_id'])) {
								$send_hotelid = $prop_map[$room['id']]['property_id'];
							} elseif (isset($prop_map[$room['id']]) && isset($prop_map[$room['id']]['user_id'])) {
								$send_hotelid = $prop_map[$room['id']]['user_id'];
							}
							//
							$xmlRQ .= '<RoomType id="'.$room['id'].'" closed="'.$room_status.'"'.(array_key_exists($room['id'], $prop_map) && !empty($send_hotelid) ? ' hotelid="'.$send_hotelid.'"' : '').'>'."\n";
							//RatePlan
							if (array_key_exists('RatePlan', $room)) {
								$set_rates = array();
								$restrictions_data = array();
								$skip_rateplans = array();
								$skip_restrictions = array();
								//Start - Expedia: Prevent Derived Rate Plans to be updated when Parent Rate Plans are set for Update
								if ($channel['uniquekey'] == VikChannelManagerConfig::EXPEDIA) {
									foreach ($room['RatePlan'] as $rateplan) {
										$rateplan_type = VikRequest::getString('rateplantype_'.$day.'_'.$room['id'].'_'.$rateplan['id'], '', 'request');
										if (array_key_exists($rateplan_type, $rateplan['Rate'])) {
											foreach ($rateplan['Rate'][$rateplan_type] as $kr => $rate) {
												$kr = is_numeric($kr) ? $kr : '0';
												$rate_cost = VikRequest::getString('rateplan_'.$day.'_'.$room['id'].'_'.$rateplan['id'].'_'.$kr, '', 'request');
												if (strlen($rate_cost)) {
													$set_rates[] = (string)$rateplan['id'];
													break;
												}
											}
										} else {
											$rate_cost = VikRequest::getString('rateplan_'.$day.'_'.$room['id'].'_'.$rateplan['id'], '', 'request');
											if (strlen($rate_cost)) {
												$set_rates[] = (string)$rateplan['id'];
											}
										}
										if (count($rateplan['Restrictions']) > 0) {
											$r_minlos = VikRequest::getInt('restrmin_'.$room['id'].'_'.$day.'_'.$rateplan['id']);
											$r_maxlos = VikRequest::getInt('restrmax_'.$room['id'].'_'.$day.'_'.$rateplan['id']);
											if (!empty($r_minlos) || !empty($r_maxlos)) {
												$restrictions_data[] = (string)$rateplan['id'];
											}
										}
									}
									if (count($set_rates) > 0) {
										//Check if there are some derived rate plans that should not be updated
										foreach ($set_rates as $rpid => $set_rate) {
											if (in_array($set_rate, $parent_rate_plans)) {
												//Parent Rate Plan
												if (array_key_exists($set_rate, $derived_rate_plans)) {
													foreach ($derived_rate_plans[$set_rate] as $drpk => $derived_rp) {
														if (in_array($derived_rp, $set_rates)) {
															$skip_rateplans[] = $derived_rp;
														}
														if (in_array($derived_rp, $restrictions_data)) {
															$skip_restrictions[] = $derived_rp;
														}
													}
												}
											}
										}
									}
									reset($room['RatePlan']);
								}
								//End - Expedia: Prevent Derived Rate Plans to be updated when Parent Rate Plans are set for Update
								foreach ($room['RatePlan'] as $krp => $rateplan) {
									$rar_updates[$day][$room['id']]['RatePlan'][$rateplan['id']] = $rateplan;
									unset($rar_updates[$day][$room['id']]['RatePlan'][$krp]);
									if ($channel['uniquekey'] == VikChannelManagerConfig::EXPEDIA) {
										if (in_array((string)$rateplan['id'], $skip_rateplans) && in_array((string)$rateplan['id'], $skip_restrictions)) {
											continue;
										} elseif (in_array((string)$rateplan['id'], $derived_rate_parents)) {
											//Derived Rate Plan
											foreach ($derived_rate_plans as $parent_id => $deriveds) {
												if (in_array((string)$rateplan['id'], $deriveds)) {
													if (in_array((string)$parent_id, $set_rates)) {
														//Parent Rate was updated so this rate plan should not even be closed or opened
														continue 2;
													}
												}
											}
										}
									}
									$rateplan_set_status = VikRequest::getString(($channel['uniquekey'] == VikChannelManagerConfig::BOOKING ? 'rateplanstatus'.$day.$room['id'].$rateplan['id'] : 'rateplanstatus'.$day.$rateplan['id']), '', 'request');
									$rateplan['closed'] = empty($rateplan['closed']) ? 'false' : $rateplan['closed'];
									$rateplan_status = strlen($rateplan_set_status) == 0 ? $rateplan['closed'] : (intval($rateplan_set_status) == 1 ? 'false' : 'true');
									$rateplan_type = VikRequest::getString('rateplantype_'.$day.'_'.$room['id'].'_'.$rateplan['id'], '', 'request');
									$pricing_attr = '';
									$taxpolicy_attr = '';
									if ($channel['uniquekey'] == VikChannelManagerConfig::DESPEGAR) {
										//for this channel we set two extra attributes in the RatePlan element (default for PerRoomPerNight and AmountAfterTax)
										$pricing_attr = '19';
										$taxpolicy_attr = 'inclusive';
										//if not charge_type, probably because of no inventory, read it from the mapping data and set it
										if (!isset($rateplan['charge_type']) && isset($ota_rate_plan['RatePlan']) && isset($ota_rate_plan['RatePlan'][$rateplan['id']]) && isset($ota_rate_plan['RatePlan'][$rateplan['id']]['ChargeTypeCode'])) {
											$rateplan['charge_type'] = $ota_rate_plan['RatePlan'][$rateplan['id']]['ChargeTypeCode'];
										}
										if (isset($rateplan['charge_type'])) {
											if ($rateplan['charge_type'] == 'PerPersonPerNight' || (int)$rateplan['charge_type'] == 21) {
												$pricing_attr = '21';
											}
										}
										//if no price_tax, probably because of no inventory, read it from the mapping data and set it
										if (!isset($rateplan['price_tax']) && isset($ota_rate_plan['RoomInfo']) && isset($ota_rate_plan['RoomInfo']['TaxPolicy'])) {
											$rateplan['price_tax'] = $ota_rate_plan['RoomInfo']['TaxPolicy'];
										}
										if (isset($rateplan['price_tax'])) {
											if (stripos($rateplan['price_tax'], 'exclusive') !== false) {
												$taxpolicy_attr = 'exclusive';
											}
										}
									}
									$xmlRQ .= '<RatePlan id="'.$rateplan['id'].'" closed="'.$rateplan_status.'"'.(!empty($pricing_attr) ? ' pricing="'.$pricing_attr.'"' : '').(!empty($taxpolicy_attr) ? ' taxpolicy="'.$taxpolicy_attr.'"' : '').'>'."\n";
									//Rate
									if ($channel['uniquekey'] == VikChannelManagerConfig::EXPEDIA) {
										//Expedia
										if (array_key_exists($rateplan_type, $rateplan['Rate'])) {
											if (!in_array((string)$rateplan['id'], $skip_rateplans)) {
												$last_los = 0;
												foreach ($rateplan['Rate'][$rateplan_type] as $kr => $rate) {
													$kr = is_numeric($kr) ? $kr : '0';
													$rate_cost = VikRequest::getString('rateplan_'.$day.'_'.$room['id'].'_'.$rateplan['id'].'_'.$kr, '', 'request');
													if (strlen($rate_cost)) {
														$xmlRQ .= '<Rate'.(array_key_exists('lengthOfStay', $rate) ? ' lengthOfStay="'.$rate['lengthOfStay'].'"' : '').'>'."\n";
														$xmlRQ .= '<'.$rateplan_type.' rate="'.floatval($rate_cost).'"'.(array_key_exists('occupancy', $rate) ? ' occupancy="'.$rate['occupancy'].'"' : '').'/>'."\n";
														$xmlRQ .= '</Rate>'."\n";
													}
													$last_los = (int)$kr;
												}
												//Costs per night added manually
												$addrateplans = VikRequest::getInt('addrateplans_'.$day.'_'.$room['id'].'_'.$rateplan['id'], '', 'request');
												if ($addrateplans > 0 && $last_los < $addrateplans) {
													for($i = ++$last_los; $i < $addrateplans; $i++) {
														$rate_cost = VikRequest::getString('rateplan_'.$day.'_'.$room['id'].'_'.$rateplan['id'].'_'.$i, '', 'request');
														if (strlen($rate_cost)) {
															$xmlRQ .= '<Rate lengthOfStay="'.($i + 1).'">'."\n";
															$xmlRQ .= '<'.$rateplan_type.' rate="'.floatval($rate_cost).'"/>'."\n";
															$xmlRQ .= '</Rate>'."\n";
														}
													}
												}
												//
											}
										} else {
											$rate_cost = VikRequest::getString('rateplan_'.$day.'_'.$room['id'].'_'.$rateplan['id'], '', 'request');
											if (strlen($rate_cost) && !in_array((string)$rateplan['id'], $skip_rateplans)) {
												$xmlRQ .= '<Rate'.(array_key_exists('lengthOfStay', $rateplan['Rate']) ? ' lengthOfStay="'.$rateplan['Rate']['lengthOfStay'].'"' : '').'>'."\n";
												$xmlRQ .= '<'.$rateplan_type.' rate="'.floatval($rate_cost).'"'.(array_key_exists('occupancy', $rateplan['Rate']) ? ' occupancy="'.$rateplan['Rate']['occupancy'].'"' : '').'/>'."\n";
												$xmlRQ .= '</Rate>'."\n";
											}
										}
									} elseif ($channel['uniquekey'] == VikChannelManagerConfig::AGODA || $channel['uniquekey'] == VikChannelManagerConfig::YCS50) {
										//Agoda
										foreach ($rateplan['Rate'] as $rateplan_type => $rateplan_rate) {
											if (!in_array($rateplan_type, array('SingleRate', 'DoubleRate', 'FullRate', 'ExtraPerson', 'ExtraAdult', 'ExtraChild', 'ExtraBed'))) {
												continue;
											}
											$rate_cost = VikRequest::getString('rateplan_'.$day.'_'.$room['id'].'_'.$rateplan['id'].'_'.$rateplan_type, '', 'request');
											if (strlen($rate_cost) > 0) {
												$xmlRQ .= '<Rate>'."\n";
												$xmlRQ .= '<'.$rateplan_type.' rate="'.floatval($rate_cost).'"/>'."\n";
												$xmlRQ .= '</Rate>'."\n";
											}
										}
									} elseif ($channel['uniquekey'] == VikChannelManagerConfig::GARDAPASS) {
										//GardaPass
										$ratemap = array('adults1' => 'SingleRate', 'price' => 'FullRate');
										foreach ($rateplan['Rate'] as $rateplan_type => $rateplan_rate) {
											if (!array_key_exists($rateplan_type, $ratemap)) {
												continue;
											}
											$rate_cost = VikRequest::getString('rateplan_'.$day.'_'.$room['id'].'_'.$rateplan['id'].'_'.$ratemap[$rateplan_type], '', 'request');
											if (strlen($rate_cost) > 0) {
												$xmlRQ .= '<Rate>'."\n";
												$xmlRQ .= '<'.$ratemap[$rateplan_type].' rate="'.floatval($rate_cost).'"/>'."\n";
												$xmlRQ .= '</Rate>'."\n";
											}
										}
									} elseif ($channel['uniquekey'] == VikChannelManagerConfig::DESPEGAR) {
										//Despegar
										$guests_rates_count = array();
										foreach ($rateplan['Rate'] as $pricek => $pricev) {
											if (strpos($pricek, 'price_') === false) {
												continue;
											}
											$price_parts = explode('_', $pricek);
											if ((int)$price_parts[1] < 1) {
												continue;
											}
											array_push($guests_rates_count, (int)$price_parts[1]);
											$rate_cost = VikRequest::getString('rateplan_'.$day.'_'.$room['id'].'_'.$rateplan['id'].'_Price'.$price_parts[1], '', 'request');
											if (strlen($rate_cost) > 0) {
												$xmlRQ .= '<Rate>'."\n";
												$xmlRQ .= '<PerDay rate="'.floatval($rate_cost).'" usage="'.(int)$price_parts[1].'"/>'."\n";
												$xmlRQ .= '</Rate>'."\n";
											}
										}
										//if pricing is PerRoomPerNight and no rate is available for StandardOccupancy, print this field
										if (isset($rateplan['charge_type']) && $rateplan['charge_type'] == 'PerRoomPerNight' && isset($ota_rate_plan['RoomInfo'])) {
											if (!in_array((int)$ota_rate_plan['RoomInfo']['StandardOccupancy'], $guests_rates_count)) {
												$rate_cost = VikRequest::getString('rateplan_'.$day.'_'.$room['id'].'_'.$rateplan['id'].'_Price'.$ota_rate_plan['RoomInfo']['StandardOccupancy'], '', 'request');
												if (strlen($rate_cost) > 0) {
													$xmlRQ .= '<Rate>'."\n";
													$xmlRQ .= '<PerDay rate="'.floatval($rate_cost).'" usage="'.(int)$ota_rate_plan['RoomInfo']['StandardOccupancy'].'"/>'."\n";
													$xmlRQ .= '</Rate>'."\n";
												}
											}
										}
										//if pricing is PerPersonPerNight and no rates are defined up to the MaxAdultOccupancy, print these fields
										if (isset($rateplan['charge_type']) && $rateplan['charge_type'] == 'PerPersonPerNight' && isset($ota_rate_plan['RoomInfo'])) {
											$max_guests_inp = count($guests_rates_count) ? max($guests_rates_count) : 0;
											if ((int)$ota_rate_plan['RoomInfo']['MaxAdultOccupancy'] > $max_guests_inp) {
												$max_guests_inp++;
												for ($num = $max_guests_inp; $num <= (int)$ota_rate_plan['RoomInfo']['MaxAdultOccupancy']; $num++) {
													$rate_cost = VikRequest::getString('rateplan_'.$day.'_'.$room['id'].'_'.$rateplan['id'].'_Price'.$num, '', 'request');
													if (strlen($rate_cost) > 0) {
														$xmlRQ .= '<Rate>'."\n";
														$xmlRQ .= '<PerDay rate="'.floatval($rate_cost).'" usage="'.$num.'"/>'."\n";
														$xmlRQ .= '</Rate>'."\n";
													}
												}
											}
										}
										//check if the cost for an additional adult was set
										$rate_cost = VikRequest::getString('rateplan_'.$day.'_'.$room['id'].'_'.$rateplan['id'].'_ExtraAdult', '', 'request');
										if (strlen($rate_cost) > 0) {
											$xmlRQ .= '<Rate>'."\n";
											//usage attribute -1 means that it's the additional adult rate
											$xmlRQ .= '<PerDay rate="'.floatval($rate_cost).'" usage="-1"/>'."\n";
											$xmlRQ .= '</Rate>'."\n";
										}
									} elseif ($channel['uniquekey'] == VikChannelManagerConfig::OTELZ) {
										//Otelz.com
										$rate_cost = VikRequest::getString('rateplan_'.$day.'_'.$room['id'].'_'.$rateplan['id'].'_price', '', 'request');
										if (strlen($rate_cost) > 0) {
											//room price
											$xmlRQ .= '<Rate>'."\n";
											$xmlRQ .= '<PerDay rate="'.floatval($rate_cost).'"/>'."\n";
											$xmlRQ .= '</Rate>'."\n";
										}
										if (isset($ota_rate_plan['RatePlan'][$rateplan['id']]['max_adults'])) {
											//price by adults
											for ($i = 1; $i <= (int)$ota_rate_plan['RatePlan'][$rateplan['id']]['max_adults']; $i++) {
												$rate_cost = VikRequest::getString('rateplan_'.$day.'_'.$room['id'].'_'.$rateplan['id'].'_adults'.$i, '', 'request');
												if (strlen($rate_cost) > 0) {
													//price by specific adult usage
													$xmlRQ .= '<Rate>'."\n";
													$xmlRQ .= '<PerDay rate="'.floatval($rate_cost).'" usage="'.$i.'"/>'."\n";
													$xmlRQ .= '</Rate>'."\n";
												}
											}
										}
									} elseif ($channel['uniquekey'] == VikChannelManagerConfig::BEDANDBREAKFASTIT) {
										//Bed-and-breakfast.it
										foreach ($rateplan['Rate'] as $rateplan_type => $rateplan_rate) {
											if (!in_array($rateplan_type, array('singleRate', 'doubleRate', 'extraBedRate'))) {
												continue;
											}
											//modify $rateplan_type for schema compatibility with Agoda
											if ($rateplan_type == 'extraBedRate') {
												$rateplan_type = 'ExtraBed';
											} else {
												$rateplan_type = ucfirst($rateplan_type);
											}
											//
											$rate_cost = VikRequest::getString('rateplan_'.$day.'_'.$room['id'].'_'.$rateplan['id'].'_'.$rateplan_type, '', 'request');
											if (strlen($rate_cost) > 0) {
												$xmlRQ .= '<Rate>'."\n";
												$xmlRQ .= '<'.$rateplan_type.' rate="'.floatval($rate_cost).'"/>'."\n";
												$xmlRQ .= '</Rate>'."\n";
											}
										}
									} elseif ($channel['uniquekey'] == VikChannelManagerConfig::BEDANDBREAKFASTEU || $channel['uniquekey'] == VikChannelManagerConfig::BEDANDBREAKFASTNL) {
										//Bedandbreakfast.eu and Bedandbreakfast.nl
										foreach ($rateplan['Rate'] as $occkey => $rateplan_rate) {
											$occ_parts = explode('_', $occkey);
											$occ = $occ_parts[1];
											$rate_cost = VikRequest::getString('rateplan_'.$day.'_'.$room['id'].'_'.$rateplan['id'].'_'.$occ, '', 'request');
											$set_rate = strlen($rate_cost) ? floatval($rate_cost) : (float)$rateplan_rate;
											$xmlRQ .= '<Rate>'."\n";
											$xmlRQ .= '<PerDay rate="'.$set_rate.'" usage="'.$occ.'"/>'."\n";
											$xmlRQ .= '</Rate>'."\n";
										}
									} elseif ($channel['uniquekey'] == VikChannelManagerConfig::FERATEL) {
										// Feratel
										/**
										 * if $rateplan['Rate'] is an empty array because of no inventory,
										 * attempt to build it through the mapping settings in $ota_rate_plan.
										 */
										if (!count($rateplan['Rate']) && isset($ota_rate_plan['RatePlan'][$rateplan['id']])) {
											if (!isset($ota_rate_plan['RatePlan'][$rateplan['id']]['min_occupancy']) || !isset($ota_rate_plan['RatePlan'][$rateplan['id']]['max_occupancy'])) {
												// we default to a basic range
												$occ_range = range(1, 6);
											} else {
												$occ_range = range((int)$ota_rate_plan['RatePlan'][$rateplan['id']]['min_occupancy'], (int)$ota_rate_plan['RatePlan'][$rateplan['id']]['max_occupancy']);
											}
											foreach ($occ_range as $occk => $occnum) {
												$occkey = $occk . '_' . $occnum;
												// populate values for the next loop
												$rateplan['Rate'][$occkey] = 0;
											}
										}
										//

										foreach ($rateplan['Rate'] as $occkey => $rateplan_rate) {
											$occ_parts = explode('_', $occkey);
											$occ = $occ_parts[1];
											$rate_cost = VikRequest::getString('rateplan_'.$day.'_'.$room['id'].'_'.$rateplan['id'].'_'.$occ, '', 'request');
											$set_rate = strlen($rate_cost) ? floatval($rate_cost) : (float)$rateplan_rate;
											$xmlRQ .= '<Rate>'."\n";
											$xmlRQ .= '<PerDay rate="'.$set_rate.'" usage="'.$occ.'"/>'."\n";
											$xmlRQ .= '</Rate>'."\n";
										}
									} elseif ($channel['uniquekey'] == VikChannelManagerConfig::BOOKING) {
										//Booking.com
										foreach ($rateplan['Rate'] as $rateplan_type => $rateplan_rate) {
											if (!in_array($rateplan_type, array('price', 'price1'))) {
												continue;
											}
											$rate_cost = VikRequest::getString('rateplan_'.$day.'_'.$room['id'].'_'.$rateplan['id'].'_'.$rateplan_type, '', 'request');
											if (strlen($rate_cost) > 0) {
												//take 1 as usage if it is price1
												$usage_attr = '';
												if (is_numeric(substr($rateplan_type, -1))) {
													$usage_attr = substr($rateplan_type, -1);
													if (!(intval($usage_attr) > 0)) {
														$usage_attr = '';
													}
												}
												//
												$rar_updates[$day][$room['id']]['RatePlan'][$rateplan['id']]['Rate'][$rateplan_type] = $rate_cost;
												$xmlRQ .= '<Rate>'."\n";
												$xmlRQ .= '<PerDay rate="'.floatval($rate_cost).'"'.(!empty($usage_attr) ? ' usage="'.$usage_attr.'"' : '').'/>'."\n";
												$xmlRQ .= '</Rate>'."\n";
											}
										}
										//Rates based on LOS and Occupancy
										$addrateplans = VikRequest::getInt('addrateplans_'.$day.'_'.$room['id'].'_'.$rateplan['id'], '', 'request');
										$addrateplansocc = VikRequest::getInt('addrateplansocc_'.$day.'_'.$room['id'].'_'.$rateplan['id'], '', 'request');
										if ($addrateplans > 0 && $addrateplansocc > 0) {
											$rar_updates[$day][$room['id']]['RatePlan'][$rateplan['id']]['RatesLOS'] = array();
											for($x = 1; $x <= $addrateplansocc; $x++) {
												$upd_occ = array();
												for($i = 0; $i < $addrateplans; $i++) {
													$rate_cost = VikRequest::getString('rateplan_'.$day.'_'.$room['id'].'_'.$rateplan['id'].'_'.$x.'_'.$i, '', 'request');
													if (strlen($rate_cost)) {
														$los_sent = true;
														$xmlRQ .= '<Rate lengthOfStay="'.($i + 1).'">'."\n";
														$xmlRQ .= '<PerDay rate="'.floatval($rate_cost).'" usage="'.$x.'"/>'."\n";
														$xmlRQ .= '</Rate>'."\n";
														$upd_occ[($i + 1)] = floatval($rate_cost);
													}
												}
												$rar_updates[$day][$room['id']]['RatePlan'][$rateplan['id']]['RatesLOS'][$x] = $upd_occ;
											}
										}
										//
									}
									//
									//Restrictions
									if (count($rateplan['Restrictions']) > 0) {
										$r_minlos = VikRequest::getInt('restrmin_'.$room['id'].'_'.$day.'_'.$rateplan['id']);
										$r_maxlos = VikRequest::getInt('restrmax_'.$room['id'].'_'.$day.'_'.$rateplan['id']);
										if (!empty($r_minlos) || !empty($r_maxlos) || $channel['uniquekey'] == VikChannelManagerConfig::BOOKING) {
											$lim_min_los = 28;
											$lim_down_min_los = $channel['uniquekey'] == VikChannelManagerConfig::BOOKING || $channel['uniquekey'] == VikChannelManagerConfig::DESPEGAR ? 0 : 1;
											$lim_max_los = $channel['uniquekey'] == VikChannelManagerConfig::AGODA || $channel['uniquekey'] == VikChannelManagerConfig::YCS50 || $channel['uniquekey'] == VikChannelManagerConfig::DESPEGAR ? 99 : ($channel['uniquekey'] == VikChannelManagerConfig::BOOKING ? 31 : 28);
											$lim_max_los = $channel['uniquekey'] == VikChannelManagerConfig::FERATEL ? 999 : $lim_max_los;
											$lim_down_max_los = $channel['uniquekey'] == VikChannelManagerConfig::AGODA || $channel['uniquekey'] == VikChannelManagerConfig::YCS50 || $channel['uniquekey'] == VikChannelManagerConfig::GARDAPASS || $channel['uniquekey'] == VikChannelManagerConfig::BOOKING || $channel['uniquekey'] == VikChannelManagerConfig::DESPEGAR || $channel['uniquekey'] == VikChannelManagerConfig::FERATEL ? 0 : 1;
											$r_minlos = $r_minlos < $lim_down_min_los ? $lim_down_min_los : ($r_minlos > $lim_min_los ? $lim_min_los : $r_minlos);
											$r_maxlos = $r_maxlos < $lim_down_max_los ? $lim_down_max_los : ($r_maxlos > $lim_max_los ? $lim_max_los : $r_maxlos);
											$r_close_in = VikRequest::getString(($channel['uniquekey'] == VikChannelManagerConfig::BOOKING ? 'restrplanarrival'.$day.$room['id'].$rateplan['id'] : 'restrplanarrival'.$day.$rateplan['id']), '', 'request');
											$r_close_in = strlen($r_close_in) == 0 ? (array_key_exists('closedToArrival', $rateplan['Restrictions']) ? $rateplan['Restrictions']['closedToArrival'] : 'false') : (intval($r_close_in) == 1 ? 'true' : 'false');
											$r_close_out = VikRequest::getString(($channel['uniquekey'] == VikChannelManagerConfig::BOOKING ? 'restrplandeparture'.$day.$room['id'].$rateplan['id'] : 'restrplandeparture'.$day.$rateplan['id']), '', 'request');
											$r_close_out = strlen($r_close_out) == 0 ? (array_key_exists('closedToDeparture', $rateplan['Restrictions']) ? $rateplan['Restrictions']['closedToDeparture'] : 'false') : (intval($r_close_out) == 1 ? 'true' : 'false');
											if ($channel['uniquekey'] == VikChannelManagerConfig::EXPEDIA) {
												if (!in_array((string)$rateplan['id'], $skip_restrictions)) {
													$xmlRQ .= '<Restrictions minLOS="'.$r_minlos.'" maxLOS="'.$r_maxlos.'" closedToArrival="'.$r_close_in.'" closedToDeparture="'.$r_close_out.'"/>'."\n";
												}
											} elseif ($channel['uniquekey'] == VikChannelManagerConfig::AGODA) {
												$r_breakfast = VikRequest::getString('restrplanbreakfast'.$day.$rateplan['id'], '', 'request');
												$r_breakfast = strlen($r_breakfast) == 0 ? '' : (intval($r_breakfast) == 1 ? 'true' : 'false');
												$r_promoblackout = VikRequest::getString('restrplanpromoblackout'.$day.$rateplan['id'], '', 'request');
												$r_promoblackout = strlen($r_promoblackout) == 0 ? '' : (intval($r_promoblackout) == 1 ? 'true' : 'false');
												$xmlRQ .= '<Restrictions minLOS="'.$r_minlos.'" maxLOS="'.$r_maxlos.'" closedToArrival="'.$r_close_in.'" closedToDeparture="'.$r_close_out.'"'.(strlen($r_breakfast) ? ' breakfastIncluded="'.$r_breakfast.'"' : '').(strlen($r_promoblackout) ? ' promotionBlackout="'.$r_promoblackout.'"' : '').'/>'."\n";
											} elseif ($channel['uniquekey'] == VikChannelManagerConfig::YCS50 || $channel['uniquekey'] == VikChannelManagerConfig::GARDAPASS) {
												$xmlRQ .= '<Restrictions minLOS="'.$r_minlos.'" maxLOS="'.$r_maxlos.'" closedToArrival="'.$r_close_in.'" closedToDeparture="'.$r_close_out.'"/>'."\n";
											} elseif ($channel['uniquekey'] == VikChannelManagerConfig::BEDANDBREAKFASTIT) {
												$xmlRQ .= '<Restrictions minLOS="'.$r_minlos.'" maxLOS="'.$r_maxlos.'" closedToArrival="'.$r_close_in.'" closedToDeparture="'.$r_close_out.'"/>'."\n";
											} elseif ($channel['uniquekey'] == VikChannelManagerConfig::BEDANDBREAKFASTEU || $channel['uniquekey'] == VikChannelManagerConfig::BEDANDBREAKFASTNL) {
												$xmlRQ .= '<Restrictions minLOS="'.$r_minlos.'" maxLOS="'.$r_maxlos.'" closedToArrival="'.$r_close_in.'" closedToDeparture="'.$r_close_out.'"/>'."\n";
											} elseif ($channel['uniquekey'] == VikChannelManagerConfig::DESPEGAR) {
												$xmlRQ .= '<Restrictions minLOS="'.$r_minlos.'" maxLOS="'.$r_maxlos.'" closedToArrival="'.$r_close_in.'" closedToDeparture="'.$r_close_out.'"/>'."\n";
											} elseif ($channel['uniquekey'] == VikChannelManagerConfig::FERATEL) {
												$xmlRQ .= '<Restrictions minLOS="'.$r_minlos.'" maxLOS="'.$r_maxlos.'" closedToArrival="'.$r_close_in.'" closedToDeparture="'.$r_close_out.'"/>'."\n";
											} elseif ($channel['uniquekey'] == VikChannelManagerConfig::BOOKING) {
												$rar_updates[$day][$room['id']]['RatePlan'][$rateplan['id']]['Restrictions']['minimumstay'] = $r_minlos;
												$rar_updates[$day][$room['id']]['RatePlan'][$rateplan['id']]['Restrictions']['maximumstay'] = $r_maxlos;
												$rar_updates[$day][$room['id']]['RatePlan'][$rateplan['id']]['Restrictions']['closedonarrival'] = $r_close_in;
												$rar_updates[$day][$room['id']]['RatePlan'][$rateplan['id']]['Restrictions']['closedondeparture'] = $r_close_out;
												$xmlRQ .= '<Restrictions minLOS="'.$r_minlos.'" maxLOS="'.$r_maxlos.'" closedToArrival="'.$r_close_in.'" closedToDeparture="'.$r_close_out.'"/>'."\n";
											}
										}
									}
									//
									$xmlRQ .= '</RatePlan>'."\n";
								}
							}
							//
							//Inventory
							$units_av = VikRequest::getString('inv_'.$day.'_'.$room['id'], '', 'request');
							$units_type = VikRequest::getString('invtype_'.$day.'_'.$room['id'], 'totalInventoryAvailable', 'request');
							if (strlen($units_av) > 0) {
								$units_av = intval($units_av);
								$units_av = $units_av < 0 ? 0 : $units_av;
								$xmlRQ .= '<Inventory totalInventoryAvailable="'.($units_type == 'totalInventoryAvailable' ? $units_av : '').'" flexibleAllocation="'.($units_type == 'flexibleAllocation' ? $units_av : '').'"/>'."\n";
							}
							//
							$xmlRQ .= '</RoomType>'."\n";
						}
						$xmlRQ .= '</RarUpdate>'."\n";
						$updated_days[] = $day;
						if (count($rar_updates_copy)) {
							foreach ($rar_updates_copy as $copyday) {
								$rar_updates[$copyday] = $rar_updates[$day];
							}
						}
					}
					$xmlRQ .= '</RarUpdateRQ>';
					
					//Debug:
					$rar_debug = false;
					$debug_val = VikRequest::getInt('e4j_debug', '', 'request');
					if ($debug_val == 1) {
						$rar_debug = true;
					}
					if ($rar_debug === true) {
						echo '<pre>'.print_r($_POST, true).'</pre><br/><br/>';
						if (class_exists('DOMDocument')) {
							$dom = new DOMDocument;
							$dom->preserveWhiteSpace = FALSE;
							$dom->loadXML($xmlRQ);
							$dom->formatOutput = TRUE;
							$xmlRQ = $dom->saveXML();
						}
						echo '<pre>'.htmlentities($xmlRQ).'</pre><br/><br/>';
						echo '<pre>'.print_r($rar_updates, true).'</pre><br/><br/>';
						die;
					}
					//
					
					$continue = true;
					$e4jC = new E4jConnectRequest($e4jc_url);
					$e4jC->setPostFields($xmlRQ);
					$e4jC->slaveEnabled = true;
					$rs = $e4jC->exec();
					if ($e4jC->getErrorNo()) {
						VikError::raiseWarning('', VikChannelManager::getErrorFromMap('e4j.error.Curl:Error #'.$curlerr_no.' '.@curl_error($e4jC->getCurlHeader())));
						$continue = false;
					}
					if (substr($rs, 0, 9) == 'e4j.error') {
						VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
						$continue = false;
					}
					if (substr($rs, 0, 11) == 'e4j.warning') {
						VikError::raiseNotice('', nl2br(VikChannelManager::getErrorFromMap($rs)));
					}
					
					$response = unserialize($rs);

					$channel_prefix = ucwords(str_replace('.com', '', $channel['name']));
					$channel_prefix = str_replace('.', '', str_replace('-', '', $channel_prefix));

					if (($response === false || !is_array($response) || !array_key_exists('esit', $response) || !in_array($response['esit'], array('Error', 'Warning', 'Success'))) && $continue) {
						VikError::raiseWarning('', VikChannelManager::getErrorFromMap('e4j.error.'.$channel_prefix.'.RAR:InvalidSchema'));
						$continue = false;
					}
					
					if ($response['esit'] == 'Error') {
						VikError::raiseWarning('', nl2br(VikChannelManager::getErrorFromMap('e4j.error.'.$channel_prefix.'.RAR:'.$response['message'])));
						$continue = false;
					}
					
					if ($response['esit'] == 'Warning') {
						VikError::raiseNotice('', nl2br(VikChannelManager::getErrorFromMap('e4j.warning.'.$channel_prefix.'.RAR:'.$response['message'])));
					}
					
					if ($continue) {
						//unset old rar_rq data
						$sess_rar['rars'] = '';
						$session->set('vcmExecRarRs', $sess_rar);
						$session->set('vcmExecAcmpRs', '');

						$mainframe->enqueueMessage(JText::_('VCMRARRQSUCCESS').(!empty($response['message']) ? '<br/>'.$response['message'] : ''));

						if ($channel['uniquekey'] == VikChannelManagerConfig::BOOKING && count($rar_updates)) {
							//store in db the updates made to the inventory, rates and availability for this date and room
							foreach ($rar_updates as $day => $rar) {
								foreach ($rar as $idroom => $room) {
									$q = "SELECT `id` FROM `#__vikchannelmanager_rar_updates` WHERE `channel`=".$dbo->quote($channel['uniquekey'])." AND `date`=".$dbo->quote($day)." AND `room_type_id`=".$dbo->quote($idroom)." LIMIT 1;";
									$dbo->setQuery($q);
									$dbo->execute();
									if ($dbo->getNumRows() == 1) {
										$rar_record = $dbo->loadAssoc();
										$q = "UPDATE `#__vikchannelmanager_rar_updates` SET `data`=".$dbo->quote(json_encode($room)).",`last_update`=CURRENT_TIMESTAMP WHERE `id`=".$rar_record['id'].";";
										$dbo->setQuery($q);
										$dbo->execute();
									} else {
										$q = "INSERT INTO `#__vikchannelmanager_rar_updates` (`channel`,`date`,`room_type_id`,`data`) VALUES(".$dbo->quote($channel['uniquekey']).", ".$dbo->quote($day).", ".$dbo->quote($idroom).", ".$dbo->quote(json_encode($room)).");";
										$dbo->setQuery($q);
										$dbo->execute();
									}
								}
							}
							if ($los_sent === true && $response['esit'] != 'Warning') {
								//Booking.com: rates were accepted by LOS so force the cookie for that pricing model
								if (method_exists('VikRequest', 'setCookie')) {
									/**
									 * @wponly 	if VBO is not updated, this method won't exist.
									 *
									 */
									VikRequest::setCookie('vcmAriPrModel'.$channel['uniquekey'], 'los', (time() + (86400 * 365)), '/');
								} else {
									$cookie->set('vcmAriPrModel'.$channel['uniquekey'], 'los', (time() + (86400 * 365)), '/');
								}
							}
						}
					}
					
				} else {
					VikError::raiseWarning('', JText::_('VCMRARERRNODATES'));
				}
			} else {
				VikError::raiseWarning('', JText::_('VCMRARERRNODATES'));
			}
		} else {
			VikError::raiseWarning('', JText::_('VCMRARERRNOSESSION'));
		}
		
		$mainframe->redirect('index.php?option=com_vikchannelmanager&task=roomsrar');
		
	}

	public function loadlosibe() {
		$dbo = JFactory::getDbo();
		$room_id = VikRequest::getInt('room_id');
		$date = VikRequest::getString('date');
		$date_ts = strtotime($date);
		$occupancy = VikRequest::getVar('occupancy', array());
		$result = 'e4j.error.No Rates Available';
		$pricing = array();
		if (!class_exists('VikBooking')) {
			require_once(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'lib.vikbooking.php');
		}
		$channel = VikChannelManager::getActiveModule(true);
		$channel['settings'] = json_decode($channel['settings'], true);
		$taxincl_price_compare = false;
		$vbo_tax_included = VikBooking::ivaInclusa();
		if (is_array($channel['settings']) && array_key_exists('price_compare', $channel['settings'])) {
			if ($channel['settings']['price_compare']['value'] == 'VCM_PRICE_COMPARE_TAX_INCL') {
				$taxincl_price_compare = true;
			}
		}

		if (!empty($room_id) && !empty($date) && !empty($date_ts) && count($occupancy)) {
			$date_ts += 7200;
			$end_date_ts = $date_ts + 86400;
			$q = "SELECT `d`.*,`p`.`name` AS `rate_name` FROM `#__vikbooking_dispcost` AS `d` LEFT JOIN `#__vikbooking_prices` `p` ON `p`.`id`=`d`.`idprice` WHERE `d`.`idroom`=".$room_id." AND `d`.`days` < 31 ORDER BY `d`.`days` ASC, `d`.`cost` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ( $dbo->getNumRows() > 0 ) {
				$rates = $dbo->loadAssocList();
				//Debug
				//$result = print_r($rates, true)."\n\n\n".print_r($occupancy, true)."\n\n\n";
				//
				$all_rate_plans = array();
				foreach ($rates as $rk => $rv) {
					$all_rate_plans[$rv['idprice']] = $rv['rate_name'];
				}
				$pricing = array('rate_plans' => $all_rate_plans, 'los' => array());
				$arr_rates = array();
				foreach( $rates as $rate ) {
					$arr_rates[$rate['idroom']][] = $rate;
				}
				$arr_rates = VikBooking::applySeasonalPrices($arr_rates, $date_ts, $end_date_ts);
				$multi_rates = 1;
				foreach ($arr_rates as $idr => $tars) {
					$multi_rates = count($tars) > $multi_rates ? count($tars) : $multi_rates;
				}
				if ($multi_rates > 1) {
					for($r = 1; $r < $multi_rates; $r++) {
						$deeper_rates = array();
						$num_nights = 0;
						foreach ($arr_rates as $idr => $tars) {
							foreach ($tars as $tk => $tar) {
								if ($tk == $r) {
									$deeper_rates[$idr][0] = $tar;
									$num_nights = ($tar['days'] - 1);
									break;
								}
							}
						}
						if (!count($deeper_rates) > 0) {
							continue;
						}
						$deeper_rates = VikBooking::applySeasonalPrices($deeper_rates, $date_ts, ($end_date_ts + (86400 * $num_nights)));
						foreach ($deeper_rates as $idr => $dtars) {
							foreach ($dtars as $dtk => $dtar) {
								$arr_rates[$idr][$r] = $dtar;
							}
						}
					}
				}
				//Debug
				//$result = print_r($arr_rates[$room_id], true)."\n\n\n";
				//

				//Tax Rates
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
				//

				//charges/discounts per adults occupancy
				foreach ($occupancy as $occk => $num_adults) {
					$base_rates = $arr_rates;
					$roomnumb = $occk + 1;
					foreach ($base_rates as $r => $rates) {
						$diffusageprice = VikBooking::loadAdultsDiff($r, (int)$num_adults);
						//Occupancy Override - Special Price may be setting a charge/discount for this occupancy while default price had no occupancy pricing
						if (!is_array($diffusageprice)) {
							foreach($rates as $kpr => $vpr) {
								if (array_key_exists('occupancy_ovr', $vpr) && array_key_exists((int)$num_adults, $vpr['occupancy_ovr']) && strlen($vpr['occupancy_ovr'][(int)$num_adults]['value'])) {
									$diffusageprice = $vpr['occupancy_ovr'][(int)$num_adults];
									break;
								}
							}
							reset($rates);
						}
						//
						if (is_array($diffusageprice)) {
							foreach($rates as $kpr => $vpr) {
								if ($roomnumb == 1) {
									$base_rates[$r][$kpr]['costbeforeoccupancy'] = $base_rates[$r][$kpr]['cost'];
								}
								//Occupancy Override
								if (array_key_exists('occupancy_ovr', $vpr) && array_key_exists((int)$num_adults, $vpr['occupancy_ovr']) && strlen($vpr['occupancy_ovr'][(int)$num_adults]['value'])) {
									$diffusageprice = $vpr['occupancy_ovr'][(int)$num_adults];
								}
								//
								$base_rates[$r][$kpr]['diffusage'] = $num_adults;
								if ($diffusageprice['chdisc'] == 1) {
									//charge
									if ($diffusageprice['valpcent'] == 1) {
										//fixed value
										$aduseval = $diffusageprice['pernight'] == 1 ? $diffusageprice['value'] * $base_rates[$r][$kpr]['days'] : $diffusageprice['value'];
										$base_rates[$r][$kpr]['diffusagecost'][$roomnumb] = $aduseval;
										$base_rates[$r][$kpr]['cost'] += $aduseval;
									} else {
										//percentage value
										$aduseval = $diffusageprice['pernight'] == 1 ? round(($base_rates[$r][$kpr]['costbeforeoccupancy'] * $diffusageprice['value'] / 100) * $base_rates[$r][$kpr]['days'], 2) : round(($base_rates[$r][$kpr]['costbeforeoccupancy'] * $diffusageprice['value'] / 100), 2);
										$base_rates[$r][$kpr]['diffusagecost'][$roomnumb] = $aduseval;
										$base_rates[$r][$kpr]['cost'] += $aduseval;
									}
								} else {
									//discount
									if ($diffusageprice['valpcent'] == 1) {
										//fixed value
										$aduseval = $diffusageprice['pernight'] == 1 ? $diffusageprice['value'] * $base_rates[$r][$kpr]['days'] : $diffusageprice['value'];
										$base_rates[$r][$kpr]['diffusagediscount'][$roomnumb] = $aduseval;
										$base_rates[$r][$kpr]['cost'] -= $aduseval;
									} else {
										//percentage value
										$aduseval = $diffusageprice['pernight'] == 1 ? round(((($base_rates[$r][$kpr]['costbeforeoccupancy'] / $base_rates[$r][$kpr]['days']) * $diffusageprice['value'] / 100) * $base_rates[$r][$kpr]['days']), 2) : round(($base_rates[$r][$kpr]['costbeforeoccupancy'] * $diffusageprice['value'] / 100), 2);
										$base_rates[$r][$kpr]['diffusagediscount'][$roomnumb] = $aduseval;
										$base_rates[$r][$kpr]['cost'] -= $aduseval;
									}
								}
							}
						} elseif ($roomnumb == 1) {
							foreach($rates as $kpr => $vpr) {
								$base_rates[$r][$kpr]['costbeforeoccupancy'] = $base_rates[$r][$kpr]['cost'];
							}
						}
					}
					//Taxes included or Excluded
					if (count($tax_rates) > 0) {
						foreach ($base_rates as $r => $rates) {
							foreach ($rates as $k => $rate) {
								if (array_key_exists($rate['idprice'], $tax_rates)) {
									if ($taxincl_price_compare === true) {
										if (!$vbo_tax_included) {
											$base_rates[$r][$k]['cost'] = VikBooking::sayCostPlusIva($rate['cost'], $rate['idprice']);
										}
									} else {
										if ($vbo_tax_included) {
											$base_rates[$r][$k]['cost'] = VikBooking::sayCostMinusIva($rate['cost'], $rate['idprice']);
										}
									}
								}
							}
						}
					}
					//
					//Debug
					//$result .= "Occupancy $num_adults\n".print_r($base_rates[$room_id], true)."\n\n\n";
					//
					//build response array for LOS
					foreach ($all_rate_plans as $rp_id => $rp_name) {
						foreach ($base_rates[$room_id] as $rate_ind => $vpr) {
							if ($vpr['idprice'] != $rp_id) {
								continue;
							}
							if (!array_key_exists($rp_id, $pricing['los'])) {
								$pricing['los'][$rp_id] = array(
									$num_adults => array(
										$vpr['days'] => round($vpr['cost'], 2)
									)
								);
							} else {
								if (!array_key_exists($num_adults, $pricing['los'][$rp_id])) {
									$pricing['los'][$rp_id][$num_adults] = array(
										$vpr['days'] => round($vpr['cost'], 2)
									);
								} else {
									$pricing['los'][$rp_id][$num_adults][$vpr['days']] = round($vpr['cost'], 2);
								}
							}
							
						}
					}
					//
				}
				//end charges/discounts per adults occupancy
				//Debug
				//$result .= "Pricing:\n\n".print_r($pricing, true)."\n\n\n";
				//
			}
		}
		if (count($pricing) > 0) {
			$result = json_encode($pricing);
			//Debug
			//$result = "Pricing:\n\n".print_r($pricing, true)."\n\n\n";
			//
		}

		echo $result;
		exit;
	}
	
	// CHANNEL VIEW - Expedia
	
	public function rooms() {
		if (VikChannelManager::isAvailabilityRequest()) {
			VCM::printMenu();
		
			VikRequest::setVar('view', VikRequest::getCmd('view', 'rooms'));
		
			parent::display();
			
			VCM::printFooter();
		} else {
			VikError::raiseWarning('', 'Authorization Denied!');
			$this->display();
		}
	}
	
	/**
	 * There is no need to authorize any new channel, because this View is deprecated.
	 * 
	 * @deprecated 	1.8.0
	 */
	public function roomsrar() {
		$eligible_channels = array(
			VikChannelManagerConfig::BOOKING,
			VikChannelManagerConfig::EXPEDIA,
			VikChannelManagerConfig::AGODA,
			VikChannelManagerConfig::YCS50,
			VikChannelManagerConfig::OTELZ,
			VikChannelManagerConfig::GARDAPASS,
			VikChannelManagerConfig::BEDANDBREAKFASTIT,
			VikChannelManagerConfig::BEDANDBREAKFASTEU,
			VikChannelManagerConfig::BEDANDBREAKFASTNL,
			VikChannelManagerConfig::FERATEL,
			VikChannelManagerConfig::PITCHUP,
		);

		$allowed = false;
		foreach ($eligible_channels as $chkey) {
			if (VikChannelManager::authorizeAction($chkey)) {
				$allowed = true;
				break;
			}
		}

		if ($allowed) {
			VCM::printMenu();
			
			VikRequest::setVar('view', VikRequest::getCmd('view', 'roomsrar'));
			
			parent::display();
			
			VCM::printFooter();
		} else {
			VikError::raiseWarning('', 'Authorization Denied!');
			$this->display();
		}
	}
	
	public function roomsynch() {
		if (VikChannelManager::isAvailabilityRequest()) {
			VCM::printMenu();
		
			VikRequest::setVar('view', VikRequest::getCmd('view', 'roomsynch'));
		
			parent::display();
			
			VCM::printFooter();
		} else {
			VikError::raiseWarning('', 'Authorization Denied!');
			$this->display();
		}
	}
	
	public function confirmcustoma() {
		VCM::printMenu();
	
		VikRequest::setVar('view', VikRequest::getCmd('view', 'confirmcustoma'));
	
		parent::display();
		
		VCM::printFooter();
	}

	public function confirmcustomr() {
		VCM::printMenu();
	
		VikRequest::setVar('view', VikRequest::getCmd('view', 'confirmcustomr'));
	
		parent::display();
		
		VCM::printFooter();
	}

	// CHANNEL VIEW - Booking.com Contents and Promotions API

	public function bcahcont() {
		if ( VikChannelManager::authorizeAction(VikChannelManagerConfig::BOOKING) ) {
			VCM::printMenu();
		
			VikRequest::setVar('view', VikRequest::getCmd('view', 'bcahcont'));
		
			parent::display();
			
			VCM::printFooter();
		} else {
			VikError::raiseWarning('', 'Authorization Denied!');
			$this->display();
		}
	}

	public function bcarcont() {
		if ( VikChannelManager::authorizeAction(VikChannelManagerConfig::BOOKING) ) {
			VCM::printMenu();
		
			VikRequest::setVar('view', VikRequest::getCmd('view', 'bcarcont'));
		
			parent::display();
			
			VCM::printFooter();
		} else {
			VikError::raiseWarning('', 'Authorization Denied!');
			$this->display();
		}
	}

	public function bcarplans() {
		if ( VikChannelManager::authorizeAction(VikChannelManagerConfig::BOOKING) ) {
			VCM::printMenu();
		
			VikRequest::setVar('view', VikRequest::getCmd('view', 'bcarplans'));
		
			parent::display();
			
			VCM::printFooter();
		} else {
			VikError::raiseWarning('', 'Authorization Denied!');
			$this->display();
		}
	}

	public function bcapnotif () {
		if ( VikChannelManager::authorizeAction(VikChannelManagerConfig::BOOKING) ) {
			VCM::printMenu();
		
			VikRequest::setVar('view', VikRequest::getCmd('view', 'bcapnotif'));
		
			parent::display();
			
			VCM::printFooter();
		} else {
			VikError::raiseWarning('', 'Authorization Denied!');
			$this->display();
		}
	}

	public function bcahsummary() {
		if ( VikChannelManager::authorizeAction(VikChannelManagerConfig::BOOKING) ) {
			VCM::printMenu();
		
			VikRequest::setVar('view', VikRequest::getCmd('view', 'bcahsummary'));
		
			parent::display();
			
			VCM::printFooter();
		} else {
			VikError::raiseWarning('', 'Authorization Denied!');
			$this->display();
		}
	}

	public function bphotos() {
		if ( VikChannelManager::authorizeAction(VikChannelManagerConfig::BOOKING) ) {
			VCM::printMenu();
		
			VikRequest::setVar('view', VikRequest::getCmd('view', 'bphotos'));
		
			parent::display();
			
			VCM::printFooter();
		} else {
			VikError::raiseWarning('', 'Authorization Denied!');
			$this->display();
		}
	}

	public function upload_image_ajax() {

		jimport('joomla.filesystem.file');

		$img = JFactory::getApplication()->input->files->get('vcm-image-upload', null, 'array');

		$minwidth = VikRequest::getInt('minwidth');
			
		$args = array( 'esit' => 0, 'name' => '', 'filename' => '');

		/**
		 * @wponly  The Booking.com Contents API use the directory below, which is in a different path for WP
		 */
		$dest = VCM_ADMIN_PATH.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR;
		if ( file_exists($dest.'vcm') || mkdir($dest.'vcm') ) {
			$dest .= 'vcm'.DIRECTORY_SEPARATOR;
		}
		//
		
		if ( isset($img) && strlen( trim( $img['name'] ) ) > 0 ) {
			$filename = JFile::makeSafe(str_replace(" ", "_", strtolower($img['name'])));
			$args['filename'] = $filename;
			$src = $img['tmp_name'];
			$j = "";
			if ( file_exists($dest.$filename) ) {
				$j = '1';
				while( file_exists($dest . $j . $filename) ) {
					$j++;
				}
			}
			$finaldest = $dest . $j . $filename;
			
			$check = getimagesize( $img['tmp_name'] );
			if ( $check[2] & imagetypes() ) {
				$size_accepted = $minwidth > 0 && (int)$check[0] < $minwidth ? false : true;
				if ($size_accepted === true) {
					if ( JFile::upload( $src, $finaldest ) ) {
						$args["name"] = $j . $filename;
						$args["esit"] = 1;
					} else {
						$args["esit"] = -1;
					}
				} else {
					$args["esit"] = -3;
				}
			} else {
				$args["esit"] = -2;
			}
		}
		
		echo json_encode( array( $args['esit'], $args['name'], $args['filename'] ) );
		die;	
	}

	public function bpromo() {
		if (VikChannelManager::authorizeAction(VikChannelManagerConfig::BOOKING)) {
			VCM::printMenu();
		
			VikRequest::setVar('view', VikRequest::getCmd('view', 'bpromo'));
		
			parent::display();
			
			VCM::printFooter();
		} else {
			VikError::raiseWarning('', 'Authorization Denied!');
			$this->display();
		}
	}

	public function bpromonew() {
		if (VikChannelManager::authorizeAction(VikChannelManagerConfig::BOOKING)) {
			VCM::printMenu();
		
			VikRequest::setVar('view', VikRequest::getCmd('view', 'bpromomanage'));
		
			parent::display();
			
			VCM::printFooter();
		} else {
			VikError::raiseWarning('', 'Authorization Denied!');
			$this->display();
		}
	}

	public function bpromoedit() {
		if (VikChannelManager::authorizeAction(VikChannelManagerConfig::BOOKING)) {
			VCM::printMenu();
		
			VikRequest::setVar('view', VikRequest::getCmd('view', 'bpromomanage'));
		
			parent::display();
			
			VCM::printFooter();
		} else {
			VikError::raiseWarning('', 'Authorization Denied!');
			$this->display();
		}
	}

	public function cancelbpromo() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikchannelmanager&task=bpromo");
	}

	public function egpromo() {
		if (VikChannelManager::authorizeAction(VikChannelManagerConfig::EXPEDIA)) {
			VCM::printMenu();
		
			VikRequest::setVar('view', VikRequest::getCmd('view', 'egpromo'));
		
			parent::display();
			
			VCM::printFooter();
		} else {
			VikError::raiseWarning('', 'Authorization Denied!');
			$this->display();
		}
	}

	public function egpromonew() {
		if (VikChannelManager::authorizeAction(VikChannelManagerConfig::EXPEDIA)) {
			VCM::printMenu();
		
			VikRequest::setVar('view', VikRequest::getCmd('view', 'egpromomanage'));
		
			parent::display();
			
			VCM::printFooter();
		} else {
			VikError::raiseWarning('', 'Authorization Denied!');
			$this->display();
		}
	}

	public function egpromoedit() {
		if (VikChannelManager::authorizeAction(VikChannelManagerConfig::EXPEDIA)) {
			VCM::printMenu();
		
			VikRequest::setVar('view', VikRequest::getCmd('view', 'egpromomanage'));
		
			parent::display();
			
			VCM::printFooter();
		} else {
			VikError::raiseWarning('', 'Authorization Denied!');
			$this->display();
		}
	}

	public function cancelegpromo() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikchannelmanager&task=egpromo");
	}

	public function airbnbpromo() {
		if (VikChannelManager::authorizeAction(VikChannelManagerConfig::AIRBNBAPI)) {
			VCM::printMenu();
		
			VikRequest::setVar('view', VikRequest::getCmd('view', 'airbnbpromo'));
		
			parent::display();
			
			VCM::printFooter();
		} else {
			VikError::raiseWarning('', 'Authorization Denied!');
			$this->display();
		}
	}

	public function airbnbpromonew() {
		if (VikChannelManager::authorizeAction(VikChannelManagerConfig::AIRBNBAPI)) {
			VCM::printMenu();
		
			VikRequest::setVar('view', VikRequest::getCmd('view', 'airbnbpromomanage'));
		
			parent::display();
			
			VCM::printFooter();
		} else {
			VikError::raiseWarning('', 'Authorization Denied!');
			$this->display();
		}
	}

	public function airbnbpromoedit() {
		if (VikChannelManager::authorizeAction(VikChannelManagerConfig::AIRBNBAPI)) {
			VCM::printMenu();
		
			VikRequest::setVar('view', VikRequest::getCmd('view', 'airbnbpromomanage'));
		
			parent::display();
			
			VCM::printFooter();
		} else {
			VikError::raiseWarning('', 'Authorization Denied!');
			$this->display();
		}
	}

	public function cancelairbnbpromo() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikchannelmanager&task=airbnbpromo");
	}
	
	// CHANNEL VIEW - Trip Connect
	
	public function hoteldetails() {	
		VCM::printMenu();
		
		VikRequest::setVar('view', VikRequest::getCmd('view', 'hoteldetails'));

		parent::display();
	
		VCM::printFooter();
	}
	
	public function inventory() {
		if ( VikChannelManager::authorizeAction(VikChannelManagerConfig::TRIP_CONNECT) ) {
			
			if (VikChannelManager::checkIntegrityHotelDetails()) {
				VCM::printMenu();
				
				VikRequest::setVar('view', VikRequest::getCmd('view', 'inventory'));
		
				parent::display();
			
				VCM::printFooter();
			} else {
				VikError::raiseNotice('', JText::_('VCMHOTELDETAILSNOTCOMPERR'));
				$this->hoteldetails();
			}
		} else {
			VikError::raiseWarning('', 'Authorization Denied!');
			$this->display();
		}
	}
	
	public function tacstatus() {
		if ( VikChannelManager::authorizeAction(VikChannelManagerConfig::TRIP_CONNECT) ) {
			VCM::printMenu();
			
			VikRequest::setVar('view', VikRequest::getCmd('view', 'tacstatus'));
	
			parent::display();
		
			VCM::printFooter();
		} else {
			VikError::raiseWarning('', 'Authorization Denied!');
			$this->display();
		}
	}
	
	public function revexpress() {
		if ( VikChannelManager::authorizeAction(VikChannelManagerConfig::TRIP_CONNECT) ) {
			VCM::printMenu();
			
			VikRequest::setVar('view', VikRequest::getCmd('view', 'revexpress'));
	
			parent::display();
		
			VCM::printFooter();
		} else {
			VikError::raiseWarning('', 'Authorization Denied!');
			$this->display();
		}
	}
	
	// CHANNEL VIEW - Trivago
	
	public function trinventory() {
		if ( VikChannelManager::authorizeAction(VikChannelManagerConfig::TRIVAGO) ) {
			
			if (VikChannelManager::checkIntegrityHotelDetails()) {
				VCM::printMenu();
				
				VikRequest::setVar('view', VikRequest::getCmd('view', 'trinventory'));
		
				parent::display();
			
				VCM::printFooter();
			} else {
				VikError::raiseNotice('', JText::_('VCMHOTELDETAILSNOTCOMPERR'));
				$this->hoteldetails();
			}
		} else {
			VikError::raiseWarning('', 'Authorization Denied!');
			$this->display();
		}
	}
	
	// CHANNEL VIEW - Airbnb
	
	public function listings() {
		if ( VikChannelManager::authorizeAction(VikChannelManagerConfig::AIRBNB) || 
			VikChannelManager::authorizeAction(VikChannelManagerConfig::FLIPKEY) || 
			VikChannelManager::authorizeAction(VikChannelManagerConfig::HOLIDAYLETTINGS) || 
			VikChannelManager::authorizeAction(VikChannelManagerConfig::WIMDU) ||
			VikChannelManager::authorizeAction(VikChannelManagerConfig::HOMEAWAY) ||
			VikChannelManager::authorizeAction(VikChannelManagerConfig::CAMPSITESCOUK) ||
			VikChannelManager::authorizeAction(VikChannelManagerConfig::ICAL) ||
			VikChannelManager::authorizeAction(VikChannelManagerConfig::VRBO) ) {
				
			VCM::printMenu();
			
			VikRequest::setVar('view', VikRequest::getCmd('view', 'listings'));
	
			parent::display();
		
			VCM::printFooter();
		} else {
			VikError::raiseWarning('', 'Authorization Denied!');
			$this->display();
		}
	}

	/**
	 * Dynamic creation of custom iCal channels.
	 * 
	 * @since 	1.6.23
	 */
	public function icalchannels() {
		if (!VikChannelManager::authorizeAction(VikChannelManagerConfig::ICAL)) {
			VikError::raiseWarning('', 'Authorization Denied!');
			$this->display();
			return;
		}

		VCM::printMenu();
		
		VikRequest::setVar('view', VikRequest::getCmd('view', 'icalchannels'));

		parent::display();
	
		VCM::printFooter();
	}

	/**
	 * Modification of custom iCal channels.
	 * 
	 * @since 	1.6.23
	 */
	public function editicalchannel() {
		if (!VikChannelManager::authorizeAction(VikChannelManagerConfig::ICAL) || !JFactory::getUser()->authorise('core.edit', 'com_vikchannelmanager')) {
			VikError::raiseWarning('', 'Authorization Denied!');
			$this->display();
			return;
		}

		VCM::printMenu();
		
		VikRequest::setVar('view', VikRequest::getCmd('view', 'icalchannels'));

		parent::display();
	
		VCM::printFooter();
	}

	/**
	 * Save/Update custom iCal channels.
	 * 
	 * @since 	1.6.23
	 */
	public function saveIcalChannel() {
		if (!VikChannelManager::authorizeAction(VikChannelManagerConfig::ICAL) || !JFactory::getUser()->authorise('core.edit', 'com_vikchannelmanager')) {
			VikError::raiseWarning('', 'Authorization Denied!');
			$this->display();
			return;
		}

		$app = JFactory::getApplication();
		$dbo = JFactory::getDbo();

		$edit_id = VikRequest::getInt('ical_channel_id', 0);

		$record = new stdClass;
		if (!empty($edit_id)) {
			$record->id = $edit_id;
		}
		$record->name = VikRequest::getString('ical_channel_name', '');
		$record->logo = VikRequest::getString('ical_channel_logo', '');
		$record->rules = VikRequest::getString('ical_channel_rules', '');

		if (isset($record->id)) {
			$dbo->updateObject('#__vikchannelmanager_ical_channels', $record, 'id');
			// unset channel session values
			$session = JFactory::getSession();
			$session->set('vcmiCalChLID', '', 'vcm');
			//
		} else {
			$dbo->insertObject('#__vikchannelmanager_ical_channels', $record, 'id');
		}

		$app->redirect('index.php?option=com_vikchannelmanager&task=icalchannels');
	}

	/**
	 * Save/Update custom iCal channels.
	 * 
	 * @since 	1.6.23
	 */
	public function deleteIcalChannels() {
		if (!VikChannelManager::authorizeAction(VikChannelManagerConfig::ICAL) || !JFactory::getUser()->authorise('core.delete', 'com_vikchannelmanager')) {
			VikError::raiseWarning('', 'Authorization Denied!');
			$this->display();
			return;
		}

		$app = JFactory::getApplication();
		$dbo = JFactory::getDbo();

		$ids = VikRequest::getVar('cid', array());
	
		if (count($ids)) {
			$q = "DELETE FROM `#__vikchannelmanager_ical_channels` WHERE `id` IN (" . implode(', ', $ids) . ");";
			$dbo->setQuery($q);
			$dbo->execute();
		}

		$app->redirect('index.php?option=com_vikchannelmanager&task=icalchannels');
	}
	
	/**
	 * Save Hotel details for any channel needing the data to be added to the hotels inventory.
	 */
	public function saveHotelDetails()
	{
		/**
		 * We call this method to trigger the Vacation Rentals APIs of Booking.com (if any).
		 * 
		 * @since 	1.7.2
		 */
		$this->triggerBcomVressPropDetails();
		//
		
		$app = JFactory::getApplication();
		$dbo = JFactory::getDbo();
		$session = JFactory::getSession();

		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = (array) json_decode($channel['params'], true);

		$args = array();
		$args['name'] = VikRequest::getVar('name');
		$args['street'] = VikRequest::getVar('street');
		$args['city'] = VikRequest::getVar('city');
		$args['zip'] = VikRequest::getVar('zip');
		$args['state'] = VikRequest::getVar('state');
		$args['country'] = VikRequest::getVar('country');

		$args['countrycode'] = "";
		$q = "SELECT `country_2_code` FROM `#__vikbooking_countries` WHERE `country_name`=".$dbo->quote($args['country'])." LIMIT 1;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$args['countrycode'] = $dbo->loadResult();
		}

		$args['latitude'] = VikRequest::getVar('latitude');
		$args['longitude'] = VikRequest::getVar('longitude');
		$args['description'] = VikRequest::getVar('description');
		$args['amenities'] = VikRequest::getVar('amenities', array());
		$args['url'] = VikRequest::getVar('url');
		$args['email'] = VikRequest::getVar('email');
		$args['phone'] = VikRequest::getVar('phone');
		$args['fax'] = VikRequest::getVar('fax');

		$args['amenities'] = implode(',', $args['amenities']);

		$pcts = VikRequest::getVar('pct', array());
		$pcts = !is_array($pcts) ? array() : $pcts;
		$args['pct'] = '';
		if (count($pcts) && !empty($pcts[0])) {
			$args['pct'] = implode(';', $pcts);
		}

		// data changed
		$not_changed = true;

		/**
		 * Check support for hotel main picture.
		 * 
		 * @since 	1.8.7
		 */
		$store_main_pic = true;
		$args['main_pic'] = VikRequest::getString('main_pic', '');
		
		// check if something has changed
		$q = "SELECT `key`,`value` FROM `#__vikchannelmanager_hotel_details`;";
		$dbo->setQuery($q);
		$rows = $dbo->loadAssocList();
		if ($rows) {
			foreach ($rows as $r) {
				$not_changed = $not_changed && ($r['value'] == $args[$r['key']]);
				if ($r['key'] == 'main_pic') {
					// field found
					$store_main_pic = false;
				}
			}
		} else {
			$not_changed = false;
		}

		if ($store_main_pic) {
			$q = "INSERT INTO `#__vikchannelmanager_hotel_details` (`key`, `value`, `required`) VALUES ('main_pic', " . $dbo->quote($args['main_pic']) . ", 0);";
			$dbo->setQuery($q);
			$dbo->execute();
		}

		// check multiple accounts
		$send_data_opts  = [];
		if ($channel['uniquekey'] == VikChannelManagerConfig::GOOGLEHOTEL) {
			// Google Hotel multiple accounts
			$add_multi_hotel = VikRequest::getInt('add_multi_hotel', 0, 'request');
			$multi_haccount  = VikRequest::getInt('multi_hotel_account', 0, 'request');

			if ($add_multi_hotel) {
				// create a new multiple hotel account
				$multi_hotel_record = new stdClass;
				$multi_hotel_record->hname 	 = $args['name'];
				$multi_hotel_record->hdata 	 = json_encode($args);
				$multi_hotel_record->channel = (int)$channel['uniquekey'];
				$dbo->insertObject('#__vikchannelmanager_hotel_multi', $multi_hotel_record, 'id');
				// prepare data
				$send_data_opts = [
					'multi_id' => $multi_hotel_record->id,
					'type' 	   => 'new',
					'channel'  => (int)$channel['uniquekey'],
				];
			} elseif ($multi_haccount) {
				// load existing multiple hotel account record
				$multi_hotel_data = VCMGhotelMultiaccounts::loadFromId($multi_haccount);
				if ($multi_hotel_data) {
					// record found
					$multi_hotel_record = new stdClass;
					$multi_hotel_record->id 	 = $multi_hotel_data['id'];
					$multi_hotel_record->hname 	 = $args['name'];
					$multi_hotel_record->hdata 	 = json_encode($args);
					$multi_hotel_record->channel = (int)$channel['uniquekey'];
					$dbo->updateObject('#__vikchannelmanager_hotel_multi', $multi_hotel_record, 'id');
					// prepare data
					$send_data_opts = [
						'multi_id' => $multi_hotel_record->id,
						'type' 	   => 'update',
						'channel'  => (int)$channel['uniquekey'],
					];
				} else {
					// record not found, deny the update of a multi-account
					$multi_haccount = 0;
				}
			}
		} elseif ($channel['uniquekey'] == VikChannelManagerConfig::TRIP_CONNECT && !empty($channel['params']['tripadvisorid'])) {
			// TripAdvisor (TripConnect) multiple accounts
			$add_multi_hotel = VikRequest::getInt('add_multi_hotel', 0, 'request');
			$multi_haccount  = VikRequest::getInt('multi_hotel_account', 0, 'request');

			if ($add_multi_hotel) {
				// create a new multiple hotel account
				$multi_hotel_record = new stdClass;
				$multi_hotel_record->hname 	    = $args['name'];
				$multi_hotel_record->hdata 	    = json_encode($args);
				$multi_hotel_record->channel    = (int) $channel['uniquekey'];
				$multi_hotel_record->account_id = $channel['params']['tripadvisorid'];
				$dbo->insertObject('#__vikchannelmanager_hotel_multi', $multi_hotel_record, 'id');
				// prepare data
				$send_data_opts = [
					'multi_id'   => $multi_hotel_record->id,
					'type' 	     => 'new',
					'channel'    => (int) $channel['uniquekey'],
					'account_id' => $channel['params']['tripadvisorid'],
				];
			} elseif ($multi_haccount) {
				// load existing multiple hotel account record
				$dbo->setQuery(
					$dbo->getQuery(true)
						->select('*')
						->from($dbo->qn('#__vikchannelmanager_hotel_multi'))
						->where($dbo->qn('id') . ' = ' . (int) $multi_haccount)
						->where($dbo->qn('channel') . ' = ' . (int) $channel['uniquekey'])
				);
				$multi_hotel_data = $dbo->loadAssoc();
				if ($multi_hotel_data) {
					// record found
					$multi_hotel_record = new stdClass;
					$multi_hotel_record->id 	    = $multi_hotel_data['id'];
					$multi_hotel_record->hname 	    = $args['name'];
					$multi_hotel_record->hdata 	    = json_encode($args);
					$multi_hotel_record->channel    = (int) $channel['uniquekey'];
					$dbo->updateObject('#__vikchannelmanager_hotel_multi', $multi_hotel_record, 'id');
					// prepare data
					$send_data_opts = [
						'multi_id'   => $multi_hotel_record->id,
						'type' 	     => 'update',
						'channel'    => (int) $channel['uniquekey'],
						'account_id' => $multi_hotel_data['account_id'],
					];
				} else {
					// record not found, deny the update of a multi-account
					$multi_haccount = 0;
				}
			}
		} else {
			// do not allow multiple hotel accounts
			$add_multi_hotel = 0;
			$multi_haccount = 0;
		}

		if (!$not_changed && empty($add_multi_hotel) && empty($multi_haccount)) {
			// update first (main) hotel account on db
			foreach ($args as $k => $v) {
				if ($k == 'pct') {
					// the property class types is saved in the configuration settings
					continue;
				}
				$q = "UPDATE `#__vikchannelmanager_hotel_details` SET `value`=".$dbo->quote($v)." WHERE `key`=".$dbo->quote($k);
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}

		/**
		 * We now store the Property Class Types
		 * 
		 * @since 	1.7.2
		 */
		if (empty($add_multi_hotel) && empty($multi_haccount)) {
			$active_pct = VikChannelManager::getActivePropertyClassTypes();
			$q = "UPDATE `#__vikchannelmanager_config` SET `setting`=".$dbo->quote(json_encode($pcts))." WHERE `param`='active_pct';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($active_pct != $pcts) {
				$not_changed = false;
			}
		}

		// check if we should redirect to a specific multi-account ID
		$redirect_suffix = '';

		if (VikChannelManager::checkIntegrityHotelDetails()) {
			if (!$not_changed || $session->get('hd-force-next-request', 0) || $channel['uniquekey'] == VikChannelManagerConfig::GOOGLEHOTEL || ($channel['uniquekey'] == VikChannelManagerConfig::TRIP_CONNECT && $send_data_opts)) {
				// if you are sending the request for trivago -> unset($args['countrycode']);
				if ($this->sendHotelDetails($args, $send_data_opts)) {
					$app->enqueueMessage(JText::_('VCMHOTELDETAILSUPDATED1'));
					// check redirect to multi-account ID
					if (!empty($send_data_opts) && !empty($send_data_opts['multi_id'])) {
						$redirect_suffix = '&multi_hotel_account=' . (($send_data_opts['account_id'] ?? '') ?: $send_data_opts['multi_id']);
					}
				}
			} else {
				$app->enqueueMessage(JText::_('VCMHOTELDETAILSUPDATED2'));
			}
		} else {
			VikError::raiseWarning('', JText::_('VCMHOTELDETAILSUPDATED0'));
		}
		
		$app->redirect('index.php?option=com_vikchannelmanager&task=hoteldetails' . $redirect_suffix);
		$app->close();
	}

	// SAVE TRIP ADVISOR ROOMS INVENTORY
	
	public function saveRoomsInventory()
	{
		$app = JFactory::getApplication();
		$dbo = JFactory::getDbo();

		$module = VikChannelManager::getActiveModule(true);
		$module['params'] = (array) json_decode($module['params'], true);

		/**
		 * Add support to multiple TripAdvisor (TripConnect) accounts.
		 * 
		 * @since 	1.9.10
		 */
		$ta_account_id = $app->input->getString('ta_account_id') ?: $module['params']['tripadvisorid'] ?? null;

		if (empty($ta_account_id)) {
			$app->enqueueMessage('Empty TripAdvisor (TripConnect) Account ID.', 'error');
			$app->redirect('index.php?option=com_vikchannelmanager');
			$app->close();
		}

		$args = array();
		$args['names'] = VikRequest::getVar('name', array());
		$args['costs'] = VikRequest::getVar('cost', array());
		$args['images'] = VikRequest::getVar('image', array());
		$args['descriptions'] = VikRequest::getVar('desc', array());
		$args['urls'] = VikRequest::getVar('url', array());
		$args['amenities'] = VikRequest::getVar('amenities', array(array()));
		$args['codes'] = VikRequest::getVar('codes', array());
		$args['numb_codes'] = VikRequest::getVar('numb_codes', array());
		$args['ids'] = VikRequest::getVar('tac_room_id', array());
		$args['vbids'] = VikRequest::getVar('vb_room_id', array());
		$args['status'] = VikRequest::getVar('status', array());

		$remove_rooms = array();

		$count = 0;

		for ($i = 0; $i < count($args['ids']); $i++) {
			
			if ($args['status'][$i]) {
				$name = $args['names'][$i];
				$img = $args['images'][$i];
				if (!empty($img)) {
					$img = VBO_SITE_URI.'resources/uploads/'.$img;
				}
				$desc = $args['descriptions'][$i];
				$url = $args['urls'][$i];
				$cost = $args['costs'][$i];
				$amenities = $args['amenities'][$i];
				$amenities_str = "";
				if (!empty($amenities)) {
					$amenities_str = implode(',', $amenities);
				}
				$codes = $args['codes'][$i];
				if (array_key_exists($i, $args['numb_codes']) && intval($args['numb_codes'][$i]) > 0) {
					$codes .= '='.$args['numb_codes'][$i];
				}
				$id = $args['ids'][$i];
				$vb_id = $args['vbids'][$i];
				
				if (!empty($name) && !empty($url)) {
					$q = "";
					if ($id == 0) {
						$q = "INSERT INTO `#__vikchannelmanager_tac_rooms`(`name`,`desc`,`img`,`url`,`cost`,`amenities`,`codes`,`id_vb_room`,`account_id`) VALUES(".
						$dbo->q($name).",".$dbo->q($desc).",".$dbo->q($img).",".$dbo->q($url).",".$cost.",".
						$dbo->q($amenities_str).",".$dbo->q($codes).",".$vb_id."," . $dbo->q($ta_account_id) . ");";
					} else {
						$q = "UPDATE `#__vikchannelmanager_tac_rooms` SET 
						`name`=".$dbo->q($name).",
						`desc`=".$dbo->q($desc).",
						`img`=".$dbo->q($img).",
						`url`=".$dbo->q($url).",
						`cost`=".$dbo->q($cost).",
						`amenities`=".$dbo->q($amenities_str).",
						`codes`=".$dbo->q($codes).",
						`id_vb_room`=".$vb_id.",
						`account_id`=" . $dbo->q($ta_account_id) . " 
						WHERE `id`=".$id." LIMIT 1;";
					}

					$dbo->setQuery($q);
					$dbo->execute();

					$count++;
				}
			} else {
				$remove_rooms[count($remove_rooms)] = $args['ids'][$i];
			}
		}

		$r_count = 0;
		foreach ($remove_rooms as $r) {
			$q = "DELETE FROM `#__vikchannelmanager_tac_rooms` WHERE `id`=" . (int) $r . " AND (`account_id` IS NULL OR `account_id`=" . $dbo->q($ta_account_id) . ") LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
			$r_count++;
		}
		
		if ($count > 0) {
			$app->enqueueMessage(JText::sprintf('VCMTACROOMSCREATEDMSG', $count));
		}
		if ($r_count > 0) {
			$app->enqueueMessage(JText::sprintf('VCMTACROOMSREMOVEDMSG', $r_count));
		}
		
		if ($count > 0 || $r_count > 0) {
			$rs = $this->sendTripConnectRoomsInventory($ta_account_id);
			$app->enqueueMessage(JText::sprintf('VCMTACROOMSSYNCHMSG', $rs['rooms']));
		} else {
			VikError::raiseNotice('', JText::_('VCMTACROOMSNOACTIONMSG'));
		}
		
		$app->redirect('index.php?option=com_vikchannelmanager&task=inventory&active_account_id=' . $ta_account_id);
		$app->close();
	}

	// SAVE TRIVAGO ROOMS INVENTORY
	
	public function saveTrivagoRoomsInventory() {
		
		$mainframe = JFactory::getApplication();
		$dbo = JFactory::getDbo();
		
		$args = array();
		$args['names'] = VikRequest::getVar('name', array());
		$args['costs'] = VikRequest::getVar('cost', array());
		$args['images'] = VikRequest::getVar('image', array());
		$args['descriptions'] = VikRequest::getVar('desc', array());
		$args['urls'] = VikRequest::getVar('url', array());
		$args['codes'] = VikRequest::getVar('codes', array());
		$args['ids'] = VikRequest::getVar('tri_room_id', array());
		$args['vbids'] = VikRequest::getVar('vb_room_id', array());
		$args['status'] = VikRequest::getVar('status', array());
		
		$remove_rooms = array();
		
		$count = 0;
		
		for( $i = 0; $i < count($args['ids']); $i++ ) {
			
			if ( $args['status'][$i] ) {
			
				$name = $args['names'][$i];
				$img = $args['images'][$i];
				if ( !empty($img) ) {
					$img = VBO_SITE_URI.'resources/uploads/'.$img;
				}
				$desc = $args['descriptions'][$i];
				$url = $args['urls'][$i];
				$cost = $args['costs'][$i];
				$codes = $args['codes'][$i];
				$id = $args['ids'][$i];
				$vb_id = $args['vbids'][$i];
				
				if ( !empty($name) && !empty($url) ) {
					$q = "";
					if ( $id == 0 ) {
						$q = "INSERT INTO `#__vikchannelmanager_tri_rooms`(`name`,`desc`,`img`,`url`,`cost`,`codes`,`id_vb_room`) VALUES(".
						$dbo->quote($name).",".$dbo->quote($desc).",".$dbo->quote($img).",".$dbo->quote($url).",".$cost.",".
						$dbo->quote($codes).",".$vb_id.");";
					} else {
						$q = "UPDATE `#__vikchannelmanager_tri_rooms` SET 
						`name`=".$dbo->quote($name).",
						`desc`=".$dbo->quote($desc).",
						`img`=".$dbo->quote($img).",
						`url`=".$dbo->quote($url).",
						`cost`=".$dbo->quote($cost).",
						`codes`=".$dbo->quote($codes).",
						`id_vb_room`=".$vb_id." WHERE `id`=".$id." LIMIT 1;";
					}
					
					$dbo->setQuery($q);
					$dbo->execute();
					
					$count++;
				}
			
			} else {
				$remove_rooms[count($remove_rooms)] = $args['ids'][$i];
			}
		}

		$r_count = 0;
		foreach( $remove_rooms as $r ) {
			$q = 'DELETE FROM `#__vikchannelmanager_tri_rooms` WHERE `id`='.$r." LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
			$r_count++;
		}
		
		if ( $count > 0 ) {
			$mainframe->enqueueMessage(JText::sprintf('VCMTRIROOMSCREATEDMSG', $count));
		}
		if ( $r_count > 0 ) {
			$mainframe->enqueueMessage(JText::sprintf('VCMTRIROOMSREMOVEDMSG', $r_count));
		}
		
		if ( $count > 0 || $r_count > 0 ) {
			$rs = $this->sendTrivagoRoomsInventory();
			$mainframe->enqueueMessage(JText::sprintf('VCMTRIROOMSSYNCHMSG', $rs['rooms']));
		} else {
			VikError::raiseNotice('', JText::_('VCMTRIROOMSNOACTIONMSG'));
		}
		
		$mainframe->redirect('index.php?option=com_vikchannelmanager&task=trinventory');
		
	} 

	// for any iCal channel
	
	public function saveListings() {
		
		$mainframe = JFactory::getApplication();
		$dbo = JFactory::getDbo();
		
		$module = VikChannelManager::getActiveModule(true);
		
		$id_vb_rooms    = VikRequest::getVar('id_vb_rooms', array());
		$id_listings    = VikRequest::getVar('id_assoc', array());
		$urls           = VikRequest::getVar('urls', array());
		
		$count = 0;
		
		for( $i = 0; $i < count($id_listings); $i++ ) {
			if ( empty($id_listings[$i]) ) {
				$id_listings[$i] = -1;
			}
			
			$q = "";
			
			if ( !empty($urls[$i]) ) {
				if ( $id_listings[$i] == -1 ) {
					$q = "INSERT INTO `#__vikchannelmanager_listings` (`id_vb_room`, `retrieval_url`, `channel`) VALUES(".
					$id_vb_rooms[$i].",".
					$dbo->quote($urls[$i]).",".
					$dbo->quote($module['uniquekey'] . (isset($module['ical_channel']) ? '-' . $module['ical_channel']['id'] : '')).");";
				} else {
					$q = "UPDATE `#__vikchannelmanager_listings` SET `retrieval_url`=".$dbo->quote($urls[$i])." WHERE `id`=".$id_listings[$i]." LIMIT 1;";
				}
			} else if ( $id_listings[$i] != -1 ) {
				$q = "DELETE FROM `#__vikchannelmanager_listings` WHERE `id`=".$id_listings[$i]." LIMIT 1;";
			}
			
			if ( !empty($q) ) {
				$dbo->setQuery($q);
				$dbo->execute();
				$count++;
			}
		}
		
		if ( $count > 0 ) {
			$this->sendListingsRequest();

			$mainframe->enqueueMessage(JText::_('VCMLISTINGSUPDATED'));
		}
		$mainframe->redirect('index.php?option=com_vikchannelmanager&task=listings');
	}
	
	// SAVE CONFIGURATION

	public function saveconfig()
	{
		$session = JFactory::getSession();
		$app = JFactory::getApplication();
		$dbo = JFactory::getDbo();

		$config_obj = VCMFactory::getConfig();

		// sync for pending reservations
		$config_obj->set('sync_pending', VikRequest::getInt('sync_pending', 0));

		/**
		 * Flush the session when switching to another Hotel ID/Account.
		 * This is to avoid cached values, like Promotions, to be
		 * displayed for an invalid account.
		 * 
		 * @since 	1.7.1
		 */
		$flush_session = VikRequest::getInt('flush_session', 0);
		if ($flush_session > 0) {
			$session->set('vcmBPromo', '');
		}

		$args = [];
		$args['dateformat'] 	 = VikRequest::getString('dateformat');
		$args['currencysymb'] 	 = VikRequest::getString('currencysymb', '', 'request', VIKREQUEST_ALLOWHTML);
		$args['currencyname'] 	 = VikRequest::getString('currencyname');
		$args['defaultpayment']  = VikRequest::getInt('defaultpayment');
		$args['defaultlang'] 	 = VikRequest::getString('defaultlang');
		$args['apikey'] 		 = trim(VikRequest::getString('vcm_apikey', '', 'request'));
		$args['emailadmin'] 	 = VikRequest::getString('emailadmin');
		$args['vikbookingsynch'] = (VikRequest::getInt('vikbookingsynch') == 1 ? 1 : 0);
		$args['appearance_pref'] = VikRequest::getString('appearance_pref', '');
		$args['expiration_reminders'] = VikRequest::getInt('expiration_reminders', 0);
		
		$vb_params = [];
		if (is_file(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'lib.vikbooking.php')) {
			require_once (VBO_SITE_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'lib.vikbooking.php');
			$vb_params['currencysymb'] = VikBooking::getCurrencySymb(true);
			$vb_params['currencyname'] = VikBooking::getCurrencyName(true);
			$vb_params['emailadmin'] = VikBooking::getAdminMail(true);
			$vb_params['dateformat'] = VikBooking::getDateFormat(true);
		}

		foreach ($vb_params as $k => $v) {
			if (empty($args[$k])) {
				$args[$k] = $v;
			}
		}

		$module = VikChannelManager::getActiveModule(true);
		$critical_error = null;
		if (!empty($module['id'])) {
			$module['params'] = (array)json_decode($module['params'], true);
			$params = [];
			$changed = false;
			foreach ($module['params'] as $k => $v) {
				$params[$k] = VikRequest::getVar($k);
				if (empty($params[$k])) {
					$params[$k] = VikRequest::getVar('old_'.$k, '');
				}
				$changed = $changed || ( $params[$k] != $v );
			}

			$err = false;
			$module['settings'] = (array)json_decode($module['settings'], true);
			$settings = [];
			foreach ($module['settings'] as $k => $v) {
				$settings[$k] = VikRequest::getVar($k);
				$module['settings'][$k]['value'] = ( (is_array($settings[$k]) && $settings[$k]) || strlen((string) $settings[$k]) ) ? $settings[$k] : '';
				$changed = $changed || ( $settings[$k] != $v['value'] );
				
				if (is_array($settings[$k])) {
					$module['settings'][$k]['value'] = $settings[$k];
					$settings[$k] = json_encode($settings[$k]);
				}
				
				if (is_string($module['settings'][$k]['value']) && strlen($module['settings'][$k]['value']) == 0 && $module['settings'][$k]['required']) {
					$err = true;
				}
			}

			if ($changed && !$err) {
				// submit the credentials to e4jConnect first in case there are errors
				$critical_error = $this->sendCredentials($module['name'], $module['uniquekey'], $params, $settings);
			}

			if (!$critical_error) {
				// unset previously cached value
				$app->setUserState('vcm.moduleactive', null);

				// update database values
				$q = "UPDATE `#__vikchannelmanager_channel` SET `params`=".$dbo->quote(json_encode($params)).", `settings`=".$dbo->quote(json_encode($module['settings']))." WHERE `id`=".$module['id']." LIMIT 1;";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}

		/**
		 * Some channels like Airbnb API may need custom settings only for VCM.
		 * The channel needing custom settings should take care of creating the config record.
		 * 
		 * @since 	1.8.0
		 */
		$custom_ch_settings = VikRequest::getVar('cust_ch_settings', array());
		if (!empty($module['uniquekey']) && is_array($custom_ch_settings)) {
			// we allow to clean all custom settings for this channel
			$custom_ch_settings = !count($custom_ch_settings) ? (new stdClass) : $custom_ch_settings;
			$args['custom_ch_settings_' . $module['uniquekey']] = json_encode($custom_ch_settings);
		}

		/**
		 * Those who upgrade to the version 1.8.0 may be missing the configuration record for
		 * the channels availability window (auto bulk actions), while those who install this
		 * version from zero, will have it set to the default value of 3 months.
		 * 
		 * @since 	1.8.0
		 */
		$av_window = VikRequest::getString('av_window', '', 'request');
		$av_window = empty($av_window) ? 'manual' : $av_window;
		$av_window = is_numeric($av_window) ? (int)$av_window : 'manual';
		$config_obj->set('av_window', $av_window);

		// update configuration settings
		foreach ($args as $key => $val) {
			$config_obj->set($key, $val);
		}

		if ($args['appearance_pref'] ?: '' && class_exists('VBOFactory')) {
			// mirror setting in VBO
			VBOFactory::getConfig()->set('appearance_pref', $args['appearance_pref']);
		}

		/**
		 * We save the settings for the reports sending interval differently, as for
		 * those who update from an older version, the records should be created.
		 * 
		 * @since 	1.6.17
		 */
		$config_obj->set('reports_interval', VikRequest::getInt('reports_interval', 0));

		/**
		 * Allow iCal cancellations: the record should be created for those who update from an older version.
		 * 
		 * @since 	1.8.9
		 */
		$ical_cancellations = VikRequest::getInt('ical_cancellations', 0);
		$config_obj->set('ical_cancellations', VikRequest::getInt('ical_cancellations', 0));

		/**
		 * iCal privacy protected fields.
		 * 
		 * @since 	1.8.24
		 */
		$config_obj->set('ical_privacy_fields', (array) $app->input->getString('ical_privacy_fields', []));

		/**
		 * Handle the autoresponder message within the translatable texts of Vik Booking.
		 * 
		 * @since 	1.8.21
		 */
		if (VikChannelManager::getChannel(VikChannelManagerConfig::BOOKING) || VikChannelManager::getChannel(VikChannelManagerConfig::AIRBNBAPI)) {
			// update message
			$autoresponder_mess = VikRequest::getString('autoresponder_mess', '', 'request', VIKREQUEST_ALLOWHTML);

			$q = $dbo->getQuery(true)
				->update($dbo->qn('#__vikbooking_texts'))
				->set($dbo->qn('setting') . ' = ' . $dbo->q($autoresponder_mess))
				->where($dbo->qn('param') . ' = ' . $dbo->q('messaging_autoresponder_txt'));

			$dbo->setQuery($q);
			$dbo->execute();
		}

		// Booking.com Contents API for auto-insert of newly created Hotel ID
		$force_insert = VikRequest::getString('force_insert');
		if (!$err && !empty($force_insert) && $module['uniquekey'] == VikChannelManagerConfig::BOOKING) {
			$warnings = VikRequest::getString('warnings');
			if (!empty($warnings)) {
				$warnings = urldecode($warnings);
				$app->enqueueMessage(JText::sprintf("VCMBCAHNEWWARNING", $warnings), 'warning');
			}
			$app->enqueueMessage(JText::sprintf("VCMBCAHNEWSUCCESS", $force_insert));
			$app->redirect("index.php?option=com_vikchannelmanager&task=bcahcont");
			$app->close();
		}

		if ($critical_error) {
			// do not proceed
			$app->redirect("index.php?option=com_vikchannelmanager&task=config");
			$app->close();
		}

		if (!$err) {
			$app->enqueueMessage(JText::_("VCMSETTINGSUPDATED"));
		} else {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap('e4j.error.RequestIntegrity'));
		}

		$app->redirect("index.php?option=com_vikchannelmanager&task=config");
		$app->close();
	}

	//bookings first summary
	public function first_summary() {
		$mainframe = JFactory::getApplication();
		$dbo = JFactory::getDbo();
		
		$module = VikChannelManager::getActiveModule(true);
		$pimp = VikRequest::getInt('imp', 0, 'request');
		$suggest_ba = VikRequest::getInt('suggest_ba', 0, 'request');

		if ($suggest_ba) {
			// display a message to suggest to run the bulk actions
			$mainframe->enqueueMessage(JText::_('VCM_SUGGEST_BULKACTIONS') . '<br/><br/><a href="index.php?option=com_vikchannelmanager&task=avpush" class="btn btn-primary">' . JText::_('VCMMENUBULKACTIONS') . '</a>', 'notice');
		}

		$req_channel = VikChannelManagerConfig::BOOKING;
		if ($module['av_enabled'] == 1) {
			$req_channel = $module['uniquekey'];
		}

		if ($pimp < 1) {
			//update the configuration to not ask it again
			VikChannelManager::checkFirstBookingSummary($req_channel, 0);
			//
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=config");
			exit;
		}

		//send the request to e4jConnect
		$api_key = VikChannelManager::getApiKey(true);
		$e4jc_url = "https://e4jconnect.com/channelmanager/?r=fsum&c=generic";
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager FSUM Request e4jConnect.com - VikBooking -->
<FirstSummaryRQ xmlns="http://www.e4jconnect.com/schemas/fsumrq">
	<Notify client="'.JUri::root().'"/>
	<Api key="'.$api_key.'"/>
	<Fetch ukey="'.$req_channel.'"/>
</FirstSummaryRQ>';
		
		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		$e4jC->slaveEnabled = true;
		$rs = $e4jC->exec();
		if ($e4jC->getErrorNo()) {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($e4jC->getErrorMsg()));
		} elseif (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
		} elseif (substr($rs, 0, 6) == 'e4j.ok') {
			//update the configuration to not ask it again
			VikChannelManager::checkFirstBookingSummary($req_channel, 0);
			//
			$mainframe->enqueueMessage(JText::_("VCMFIRSTBSUMMREQSENT"));
		} else {
			VikError::raiseWarning('', 'Empty Response');
		}

		//redirect
		$mainframe->redirect("index.php?option=com_vikchannelmanager&task=config");
	}

	//Remove one of the more_accounts for this active channel
	public function rmchaccount() {
		$mainframe = JFactory::getApplication();
		$dbo = JFactory::getDbo();
		
		$module = VikChannelManager::getActiveModule(true);
		$pind = VikRequest::getInt('ind');
		$phid = VikRequest::getString('hid');
		
		$more_accounts = array();
		if (!empty($module['id']) && $module['av_enabled'] == 1) {
			//this query below is safe with the error #1055 when sql_mode=only_full_group_by for the aggregate functions
			$q = "SELECT `prop_name`,`prop_params`, COUNT(DISTINCT `idroomota`) AS `tot_rooms` FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel`=".(int)$module['uniquekey']." GROUP BY `prop_name`,`prop_params` ORDER BY `prop_name` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ( $dbo->getNumRows() > 1 ) {
				$other_accounts = $dbo->loadAssocList();
				foreach ($other_accounts as $oacc) {
					if (!empty($oacc['prop_params'])) {
						$oacc['active'] = $oacc['prop_params'] == $module['params'] ? 1 : 0;
						$more_accounts[] = $oacc;
					}
				}
				if (!(count($more_accounts) > 1)) {
					$more_accounts = array();
				}
			}
		}
		if (count($more_accounts) && array_key_exists($pind, $more_accounts)) {
			$acc_info = json_decode($more_accounts[$pind]['prop_params'], true);
			$hid_id = '';
			if (isset($acc_info['hotelid'])) {
				$hid_id = $acc_info['hotelid'];
			} elseif (isset($acc_info['apikey'])) {
				$hid_id = $acc_info['apikey'];
			} elseif (isset($acc_info['property_id'])) {
				$hid_id = $acc_info['property_id'];
			} elseif (isset($acc_info['user_id'])) {
				$hid_id = $acc_info['user_id'];
			}
			if (!empty($hid_id) && $hid_id == $phid) {
				//remove all the mapped room types for this channel and account
				$q = "DELETE FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel`=".(int)$module['uniquekey']." AND `prop_params`=".$dbo->quote($more_accounts[$pind]['prop_params']).";";
				$dbo->setQuery($q);
				$dbo->execute();
				//send PWD Request to e4jConnect with action="remove"
				$this->sendPwdRemoval($module['name'], $module['uniquekey'], $acc_info, json_decode($module['settings'], true));
				//
				$mainframe->enqueueMessage(JText::_("VCMSETTINGSUPDATED"));
				$mainframe->redirect("index.php?option=com_vikchannelmanager&task=config");
				exit;
			}
		}
		VikError::raiseWarning('', VikChannelManager::getErrorFromMap('e4j.error.RequestIntegrity'));
		$mainframe->redirect("index.php?option=com_vikchannelmanager&task=config");
		exit;
	}

	public function changeStatusColumn() {
		$mainframe = JFactory::getApplication();
		
		$table = VikRequest::getString('table_db','');
		$column = VikRequest::getString('column_db','');
		$val = (VikRequest::getInt('val',0)+1)%2;
		$id = VikRequest::getInt('id',0);
		$return_url = 'index.php?option=com_vikchannelmanager&task=' . VikRequest::getString('return_task');
		
		$dbo = JFactory::getDbo();
		
		$q = "UPDATE `#__vikchannelmanager_".$table."` SET `".$column."` = ".$val . " WHERE `id` = " . $id . ";";
		
		$dbo->setQuery($q);
		$dbo->execute();
		
		$mainframe->redirect($return_url);
		exit;
	}

	public function setmodule()
	{
		$app = JFactory::getApplication();
		$dbo = JFactory::getDbo();
		$session = JFactory::getSession();

		$id = VikRequest::getString('id', '');

		/**
		 * Sub-channel for iCal is identified as "24-n"
		 * 
		 * @since 	1.7.0
		 */
		$ical_id = '';
		if (strpos($id, '-') !== false) {
			$parts = explode('-', $id);
			$id = (int)$parts[0];
			// make sure the sub-channel exists
			$q = "SELECT `id` FROM `#__vikchannelmanager_ical_channels` WHERE `id`=" . (int)$parts[1] . ";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows()) {
				$ical_id = '-' . (int)$parts[1];
			}
		}

		// redirection page
		$goto = '';
		$encoded_goto = $app->input->getBase64('goto', '');
		if ($encoded_goto) {
			// decode redirection page
			$goto = base64_decode($encoded_goto);
		}

		// default to Settings page
		$goto = empty($goto) ? 'index.php?option=com_vikchannelmanager&task=config' : $goto;

		// set active module
		$q = "SELECT `id`,`uniquekey` FROM `#__vikchannelmanager_channel` WHERE `id`=" . $dbo->q($id);
		$dbo->setQuery($q, 0, 1);
		$row = $dbo->loadAssoc();

		if ($row) {
			if ($row['uniquekey'] == VikChannelManagerConfig::ICAL) {
				$goto = 'index.php?option=com_vikchannelmanager&task=icalchannels';
				if (!empty($ical_id)) {
					$goto = 'index.php?option=com_vikchannelmanager&task=listings';
				}
			}

			// update configuration value
			VCMFactory::getConfig()->set('moduleactive', $id . $ical_id);

			// unset previously cached value
			$app->setUserState('vcm.moduleactive', null);

			// unset channel session values
			$session->set('vcmExecRarRs', '');
			$session->set('vcmiCalChLID', '', 'vcm');
		}

		// redirect and close
		$app->redirect($goto);
		$app->close();
	}

	// REMOVE
	
	public function removeroomsxref()
	{
		$dbo = JFactory::getDbo();
		$ids = VikRequest::getVar('cid', array());
		
		foreach ($ids as $id) {
			$q = "DELETE FROM `#__vikchannelmanager_roomsxref` WHERE `id`=" . (int)$id . " LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
		}

		JFactory::getApplication()->redirect("index.php?option=com_vikchannelmanager&task=rooms");
	}
	
	// CANCEL
	
	public function cancel() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikchannelmanager");
	}
	
	public function cancelsynch() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikchannelmanager&task=rooms");
	}
	
	public function canceloversight() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikchannelmanager&task=oversight");
	}
	
	// TASK
	
	public function check_notifications() {
		$response = '0';
		$session = JFactory::getSession();
		$dbo = JFactory::getDbo();
		$now_info = getdate();
		$max_in_past = mktime(0, 0, 0, $now_info['mon'], ($now_info['mday'] - 7), $now_info['year']);
		$q = "SELECT COUNT(*) FROM `#__vikchannelmanager_notifications` WHERE `ts`>=".$max_in_past." AND `read`=0;";
		$dbo->setQuery($q);
		$dbo->execute();
		$totnew = $dbo->loadResult();
		if (intval($totnew) > 0) {
			$response = intval($totnew) > 99 ? '99+' : $totnew;
			//unset any availability compare data
			$session->set('vcmExecAcmpRs', '');
			//
			$session->set('vcmNotifications', intval($totnew), 'vcm');
		} else {
			$session->set('vcmNotifications', 0, 'vcm');
		}
		echo $response;
		exit;
	}

	public function wizard_store_api_key() {
		$api_key = VikRequest::getVar('apikey', '');

		if (!function_exists('curl_init')) {
			echo json_encode(array(0, VikChannelManager::getErrorFromMap('e4j.error.Curl')));
			exit;
		}

		$e4jc_url = "https://e4jconnect.com/channelmanager/?r=exp&c=generic";
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager EXP Request e4jConnect.com - VikBooking -->
<ExpiringRQ xmlns="http://www.e4jconnect.com/schemas/exprq">
	<Notify client="'.JUri::root().'"/>
	<Api key="'.$api_key.'"/>
	<Fetch question="subscription" channel="generic"/>
</ExpiringRQ>';
		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		$rs = $e4jC->exec();
		if ($e4jC->getErrorNo()) {
			echo json_encode(array(0, $e4jC->getErrorNo().' '.$e4jC->getErrorMsg()));
			exit;
		}
		if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			echo json_encode(array(0, VikChannelManager::getErrorFromMap($rs)));
			exit;
		}

		$dbo = JFactory::getDbo();

		// set current API key in DB
		$q = "UPDATE `#__vikchannelmanager_config` SET `setting`=".$dbo->quote($api_key)." WHERE `param`='apikey' LIMIT 1;";
		$dbo->setQuery($q);
		$dbo->execute();

		/**
		 * The request is supposed to return a JSON encoded object with the information
		 * about the API key expiration date. Decode the data and store it onto the db.
		 * 
		 * @since 	1.8.3
		 */
		$expiration_details = json_decode($rs);
		if (is_object($expiration_details) && isset($expiration_details->ymd)) {
			// update value on db
			VikChannelManager::updateExpirationDetails($expiration_details);
		}

		// return a successful array
		echo json_encode(array(1));
		exit;
	}

	public function sendCustomAvailabilityRequest() {
		if (!function_exists('curl_init')) {
			echo VikChannelManager::getErrorFromMap('e4j.error.Curl');
			exit;
		}
		
		$apikey = VikChannelManager::getApiKey(true);
		
		$mainframe = JFactory::getApplication();
		$session = JFactory::getSession();
		
		$cust_a_req = VikRequest::getVar('cust_av', array());
		$channels = VikRequest::getVar('channel', array());
		
		if (!(count($channels) > 0)) {
			VikError::raiseWarning('', JText::_('VCMNOCUSTOMAMODS'));
			$mainframe = JFactory::getApplication();
			$mainframe->redirect('index.php?option=com_vikchannelmanager&task=oversight');
			exit;
		}

		$cust_a = array();

		$rooms_id = array();
		$custa_details = '';
		
		foreach( $cust_a_req as $i => $v ) {
			list($idroom, $fromts, $endts, $units, $vbounits) = explode('-', $v);
			$details = array(
				'idroom' => $idroom,
				'fromts' => $fromts,
				'from' => date('Y-m-d', $fromts),
				'endts' => $endts,
				'end' => date('Y-m-d', $endts),
				'units' => $units,
				'vbounits' => $vbounits
			);
			$rooms_id[$idroom] = $idroom;
			if ( empty($cust_a[$idroom]) ) {
				$cust_a[$idroom] = array();
			}
			
			array_push($cust_a[$idroom], $details);
		}
		
		$dbo = JFactory::getDbo();
		
		$q = "SELECT `id`,`name` FROM `#__vikbooking_rooms` WHERE `id` IN(".implode(', ', $rooms_id).");";
		$dbo->setQuery($q);
		$dbo->execute();
		if ( $dbo->getNumRows() > 0 ) {
			$rooms_details = $dbo->loadAssocList();
			foreach ($rooms_details as $room_details) {
				$rooms_id[$room_details['id']] = $room_details['name'];
			}
		}
		foreach ($rooms_id as $idr => $rname) {
			if (array_key_exists($idr, $cust_a)) {
				foreach ($cust_a[$idr] as $cust_det) {
					$custa_details .= $rname.': '.$cust_det['from'].' - '.$cust_det['end'].' Units: '.$cust_det['units']."\n";
					break;
				}
			}
		}
		$custa_details = rtrim($custa_details, "\n");

		//Clean vbo from channel IDs
		$channels_av = $channels;
		foreach( $cust_a as $idroom => $cust ) {
			if ( !empty($channels_av[$idroom]) && count($channels_av[$idroom]) > 0 ) {
				foreach ($channels_av[$idroom] as $ch_av_k => $ch_av_v) {
					if ($ch_av_v == 'vbo') {
						unset($channels_av[$idroom][$ch_av_k]);
					}
				}
			}
		}
		//

		$nkey = VikChannelManager::generateNKey('0');
		
		$e4jc_url = "https://e4jconnect.com/channelmanager/?r=custa&c=channels";
		
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager CUSTA Request e4jConnect.com - Channels Module extensionsforjoomla.com -->
<CustAvailUpdateRQ xmlns="http://www.e4jconnect.com/channels/custarq">
	<Notify client="'.JUri::root().'" nkey="'.$nkey.'"/>
	<Api key="'.$apikey.'"/>
	<AvailUpdate>'."\n";
		
		$totcombos = 0;
		foreach ($cust_a as $idroom => $cust) {
			if (!empty($channels_av[$idroom]) && count($channels_av[$idroom]) > 0) {
				$q = "SELECT `r`.`idroomota`, `r`.`idchannel`, `r`.`otapricing`, `r`.`prop_params` FROM `#__vikchannelmanager_channel` AS `c`, `#__vikchannelmanager_roomsxref` AS `r`
				WHERE `c`.`uniquekey`=`r`.`idchannel` AND `c`.`av_enabled`=1 AND `r`.`idroomvb`=$idroom AND `c`.`uniquekey` IN (".implode(",", $channels_av[$idroom]).");";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$rows = $dbo->loadAssocList();
					
					foreach ($rows as $row) {
						$hotelid = '';
						if (!empty($row['prop_params'])) {
							$prop_info = json_decode($row['prop_params'], true);
							if (isset($prop_info['hotelid'])) {
								$hotelid = $prop_info['hotelid'];
							} elseif (isset($prop_info['id'])) {
								// useful for Pitchup.com to identify multiple accounts
								$hotelid = $prop_info['id'];
							} elseif (isset($prop_info['apikey'])) {
								// useful for Pitchup.com, but it may be a good backup field for future channels to identify multiple accounts
								$hotelid = $prop_info['apikey'];
							} elseif (isset($prop_info['property_id'])) {
								// useful for Hostelworld
								$hotelid = $prop_info['property_id'];
							} elseif (isset($prop_info['user_id'])) {
								// useful for Airbnb API
								$hotelid = $prop_info['user_id'];
							}
						}
						foreach ($cust as $det) {
							$rateplanid = '0';
							if ((int)$row['idchannel'] == (int)VikChannelManagerConfig::AGODA && !empty($row['otapricing'])) {
								$ota_pricing = json_decode($row['otapricing'], true);
								if (count($ota_pricing) > 0 && array_key_exists('RatePlan', $ota_pricing)) {
									foreach ($ota_pricing['RatePlan'] as $rp_id => $rp_val) {
										$rateplanid = $rp_id;
										break;
									}
								}
							}
							$xml .= "\t\t".'<RoomType id="'.$row['idroomota'].'" rateplanid="'.$rateplanid.'" idchannel="'.$row['idchannel'].'" newavail="'.$det['units'].'"'.(!empty($hotelid) ? ' hotelid="'.$hotelid.'"' : '').'>'."\n";
							$xml .= "\t\t\t".'<Day from="'.$det['from'].'" to="'.$det['end'].'"/>'."\n";
							$xml .= "\t\t".'</RoomType>'."\n";
				
							$totcombos++;
						} 
					}
					
				}
			}
		}
		
		$xml .= "\t".'</AvailUpdate>
</CustAvailUpdateRQ>';

		$extra_qstring = '';
		
		if ($totcombos > 0) {
			$e4jC = new E4jConnectRequest($e4jc_url);
			$e4jC->setPostFields($xml);
			$e4jC->slaveEnabled = true;
			$rs = $e4jC->exec();
			if ($e4jC->getErrorNo()) {
				VikError::raiseWarning('', @curl_error($e4jC->getCurlHeader()));
				$mainframe->redirect("index.php?option=com_vikchannelmanager&task=oversight");
				exit;
			}
			if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
				VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
				$mainframe->redirect("index.php?option=com_vikchannelmanager&task=oversight");
				exit;
			}
			//save notification
			$esitstr = 'e4j.OK.Channels.CUSTAR_RQ'."\n".$custa_details;
			$q = "INSERT INTO `#__vikchannelmanager_notifications` (`ts`,`type`,`from`,`cont`,`read`) VALUES('".time()."', '1', 'VCM', ".$dbo->quote($esitstr).", 0);";
			$dbo->setQuery($q);
			$dbo->execute();
			$id_notification = $dbo->insertId();
			VikChannelManager::updateNKey($nkey, $id_notification);
			//unset any availability compare data
			$session->set('vcmExecAcmpRs', '');
			//Speed up the notification downloading interval
			$extra_qstring = '&fastcheck=1';
			//

			$mainframe->enqueueMessage(JText::sprintf('VCMTOTCUSTARQRESENT', $totcombos));
		}
		
		//Update availability on VBO if necessary
		$vbo_updated = false;
		if (!class_exists('VikBooking')) {
			require_once(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'lib.vikbooking.php');
		}
		$vbo_df = VikBooking::getDateFormat();
		$vbo_df = $vbo_df == "%d/%m/%Y" ? 'd/m/Y' : ($vbo_df == "%m/%d/%Y" ? 'm/d/Y' : 'Y/m/d');
		$morerb = VikBooking::getHoursMoreRb();
		$addrealback = VikBooking::getHoursRoomAvail() * 3600;
		$hcheckin = 0;
		$mcheckin = 0;
		$hcheckout = 0;
		$mcheckout = 0;
		$timeopst = VikBooking::getTimeOpenStore();
		if (is_array($timeopst)) {
			$opent = VikBooking::getHoursMinutes($timeopst[0]);
			$closet = VikBooking::getHoursMinutes($timeopst[1]);
			$hcheckin = $opent[0];
			$mcheckin = $opent[1];
			$hcheckout = $closet[0];
			$mcheckout = $closet[1];
		}
		foreach( $cust_a as $idroom => $cust ) {
			if ( !empty($channels[$idroom]) && count($channels[$idroom]) > 0 ) {
				if (!in_array('vbo', $channels[$idroom])) {
					//update on VBO not requested
					continue;
				}
				foreach( $cust as $det ) {
					if ($det['vbounits'] > $det['units']) {
						//Update availability on VBO
						$block_units = $det['vbounits'] - $det['units'];
						if ((int)$det['endts'] === 0 || $det['endts'] == $det['fromts']) {
							//one day only
							$det['endts'] = $det['fromts'] + 86500; //avoid dst
						} else {
							//end of booking must be set to the day after than the portals, at the check-out time
							$det['endts'] += 86500;
						}
						$first = VikBooking::getDateTimestamp(date($vbo_df, $det['fromts']), $hcheckin, $mcheckin);
						$second = VikBooking::getDateTimestamp(date($vbo_df, $det['endts']), $hcheckout, $mcheckout);
						$secdiff = $second - $first;
						$daysdiff = $secdiff / 86400;
						if (is_int($daysdiff)) {
							if ($daysdiff < 1) {
								$daysdiff=1;
							}
						} else {
							if ($daysdiff < 1) {
								$daysdiff=1;
							} else {
								$sum = floor($daysdiff) * 86400;
								$newdiff = $secdiff - $sum;
								$maxhmore = $morerb * 3600;
								if ($maxhmore >= $newdiff) {
									$daysdiff = floor($daysdiff);
								} else {
									$daysdiff = ceil($daysdiff);
								}
							}
						}
						$insertedbusy = array();
						for($b = 1; $b <= $block_units; $b++) {
							$realback = $second + $addrealback;
							$q = "INSERT INTO `#__vikbooking_busy` (`idroom`,`checkin`,`checkout`,`realback`) VALUES(".$idroom.",".$first.",".$second.",".$realback.");";
							$dbo->setQuery($q);
							$dbo->execute();
							$lid = $dbo->insertid();
							$insertedbusy[] = $lid;
						}
						if (count($insertedbusy) > 0) {
							$sid = VikBooking::getSecretLink();
							$q = "INSERT INTO `#__vikbooking_orders` (`custdata`,`ts`,`status`,`days`,`checkin`,`checkout`,`sid`,`roomsnum`,`channel`) VALUES(".$dbo->quote(JText::_('VCMDESCRORDVBO')).",'".time()."','confirmed',".$daysdiff.",".$first.",".$second.",'".$sid."','1',".$dbo->quote(JText::_('VCMVBORDERFROMVCM')).");";
							$dbo->setQuery($q);
							$dbo->execute();
							$newoid = $dbo->insertid();
							//ConfirmationNumber
							$confirmnumber = VikBooking::generateConfirmNumber($newoid, true);
							//end ConfirmationNumber
							foreach($insertedbusy as $lid) {
								$q = "INSERT INTO `#__vikbooking_ordersbusy` (`idorder`,`idbusy`) VALUES('".$newoid."','".$lid."');";
								$dbo->setQuery($q);
								$dbo->execute();
							}
							$q = "INSERT INTO `#__vikbooking_ordersrooms` (`idorder`,`idroom`,`adults`,`children`) VALUES(".$newoid.",".$idroom.",1,0);";
							$dbo->setQuery($q);
							$dbo->execute();
						}
						$vbo_updated = true;
					}
				}
			}
		}
		//End Update availability on VBO

		if ($vbo_updated === true && $totcombos === 0) {
			$mainframe->enqueueMessage(JText::_('VCMCUSTARQOKVBO'));
		}
		
		$mainframe->redirect("index.php?option=com_vikchannelmanager&task=oversight".$extra_qstring);
		
	}
	
	public function exec_exp() {
		if (!function_exists('curl_init')) {
			echo 'e4j.error.'.VikChannelManager::getErrorFromMap('e4j.error.Curl');
			exit;
		}
		
		$session = JFactory::getSession();
		$req_cont = $session->get('exec_exp', 0, 'vcm');
		if ( $req_cont >= 5 ) {
			echo 'e4j.error.'.JText::_('VCMEXECMAXREQREACHEDERR');
			exit;
		}
		
		$vcmresponse = 'e4j.error';
		$apikey = VikChannelManager::getApiKey(true);
		if (empty($apikey)) {
			echo 'e4j.error.'.VikChannelManager::getErrorFromMap('e4j.error.Settings');
			exit;
		}
		$e4jc_url = "https://e4jconnect.com/channelmanager/?r=exp&c=generic";
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager EXP Request e4jConnect.com - VikBooking -->
<ExpiringRQ xmlns="http://www.e4jconnect.com/schemas/exprq">
	<Notify client="'.JUri::root().'"/>
	<Api key="'.$apikey.'"/>
	<Fetch question="subscription_channels" channel="all"/>
</ExpiringRQ>';
		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		$rs = $e4jC->exec();
		
		VikChannelManager::validateChannelResponse($rs);

		if ($e4jC->getErrorNo()) {
			echo 'e4j.error.'.@curl_error($e4jC->getCurlHeader());
			exit;
		}
		if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			echo 'e4j.error.'.VikChannelManager::getErrorFromMap($rs);
			exit;
		}

		/**
		 * Attempt to decode the JSON response, which includes 2 properties:
		 * "expiration_details" and "channel_expirations".
		 * 
		 * @since 	1.8.3
		 */
		$rs_obj = json_decode($rs);
		if (!is_object($rs_obj)) {
			echo 'e4j.error.'.VikChannelManager::getErrorFromMap('e4j.error.Could not decode information');
			exit;
		}
		$channel_expirations = $rs_obj->channel_expirations;

		// update expiration details
		VikChannelManager::updateExpirationDetails($rs_obj->expiration_details);

		// increase session value
		$session->set('exec_exp', ++$req_cont, 'vcm');

		// set response for VCM
		$vcmresponse = JText::sprintf('VCMAPIEXPRQRSMESS', $channel_expirations);

		echo $vcmresponse;
		exit;
	}

	public function exec_cha()
	{
		if (!function_exists('curl_init')) {
			VCMHttpDocument::getInstance()->close(200, 'e4j.error.' . VikChannelManager::getErrorFromMap('e4j.error.Curl'));
		}

		$app = JFactory::getApplication();
		$session = JFactory::getSession();
		$req_cont = $session->get('exec_cha', 0, 'vcm');
		if ($req_cont >= 5) {
			VCMHttpDocument::getInstance()->close(200, JText::_('VCMEXECMAXREQREACHEDERR'));
		}

		$vcmresponse = 'e4j.error';
		$apikey = VikChannelManager::getApiKey(true);
		if (empty($apikey)) {
			VCMHttpDocument::getInstance()->close(200, 'e4j.error.' . VikChannelManager::getErrorFromMap('e4j.error.Settings'));
		}

		$e4jc_url = "https://e4jconnect.com/channelmanager/?r=cha&c=generic";
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager CHA Request e4jConnect.com - VikBooking -->
<ChannelsRQ xmlns="http://www.e4jconnect.com/schemas/charq">
	<Notify client="'.JUri::root().'"/>
	<Api key="'.$apikey.'"/>
	<Fetch question="api" channel="all"/>
</ChannelsRQ>';
		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		$rs = $e4jC->exec();

		VikChannelManager::validateChannelResponse($rs);

		if ($e4jC->getErrorNo()) {
			VCMHttpDocument::getInstance()->close(200, 'e4j.error.'.@curl_error($e4jC->getCurlHeader()));
		}

		if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			VCMHttpDocument::getInstance()->close(200, 'e4j.error.'.VikChannelManager::getErrorFromMap($rs));
		}

		if (!$e4jC->successResponse()) {
			VCMHttpDocument::getInstance()->close($e4jC->getResultInfo('http_code', 500), 'Error performing the request');
		}

		// PARSE
		$rs = unserialize($rs);
		
		$dbo = JFactory::getDbo();
		
		$channel_keys = [];
		foreach ((array) $rs as $channel) {
			/**
			 * Check if the service requires some settings to be stored.
			 * 
			 * @since 1.9
			 */
			if ($channel['idchannel'] == VikChannelManagerConfig::AI) {
				/**
		         * @todo  use "master." subdomain once the master will be divided from the shop
		         */
				VCMFactory::getConfig()->set('ai_server', ($channel['ai_server'] ?? 'e4jconnect.com'));
			}

			// set channel identifier key
			$channel_keys[count($channel_keys)] = $dbo->quote($channel['idchannel']);
		}
		
		if ($channel_keys) {

			$q = "SELECT `id` FROM `#__vikchannelmanager_channel` WHERE `uniquekey` NOT IN (".implode(",", $channel_keys).");";
			$dbo->setQuery($q);
			$rows = $dbo->loadAssocList();

			foreach( $rows as $r ) {
				$q = "DELETE FROM `#__vikchannelmanager_channel` WHERE `id`=".$r['id']." LIMIT 1;";
				$dbo->setQuery($q);
				$dbo->execute();
			}

		} else {
			$q = "TRUNCATE TABLE `#__vikchannelmanager_channel`;";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		
		$response_text = "";
		
		$date_format = VikChannelManager::getClearDateFormat(true);
		
		foreach ($rs as $channel) {
			if (empty($channel['settings'])) {
				$channel['settings'] = array();
			} else {
				$channel['settings'] = VCM::parseChannelSettings($channel);
			}

			$q = "SELECT `id` FROM `#__vikchannelmanager_channel` WHERE `uniquekey`=".$dbo->quote($channel['idchannel'])." LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows()) {
				// update
				$q = "UPDATE `#__vikchannelmanager_channel` SET `name`=".$dbo->quote($channel['channel']).",`settings`=".$dbo->quote(json_encode($channel['settings']))." WHERE `id`=".$dbo->loadResult()." LIMIT 1;";
			} else {
				// insert
				$q = "INSERT INTO `#__vikchannelmanager_channel` (`name`, `params`, `uniquekey`, `av_enabled`, `settings`) VALUES(".
				$dbo->quote($channel['channel']).", ".$dbo->quote(json_encode(($channel['params'] ?: []))).", ".$dbo->quote($channel['idchannel']).",".intval($channel['av']).",".$dbo->quote(json_encode($channel['settings'])).");";
			}

			$dbo->setQuery($q);
			$dbo->execute();

			$response_text .= "<span class=\"vcmactivechsinglespan\">- <strong>".$channel['channel']."</strong> (".date($date_format, $channel['validthru']).").</span>";
		}
		// END PARSE

		// unset user state to eventually reload the new channel settings
		$app->setUserState('vcm.moduleactive', null);
		
		if ($rs) {
			$session->set('exec_cha', ++$req_cont, 'vcm');
			VCMHttpDocument::getInstance()->close(200, JText::sprintf('VCMGETCHANNELSRQRSMESS1', $response_text));
		}

		VCMHttpDocument::getInstance()->close(200, JText::_('VCMGETCHANNELSRQRSMESS0'));
	}

	public function savesynch()
	{
		$dbo = JFactory::getDbo();
		$mainframe = JFactory::getApplication();
		
		$prop_name = VikRequest::getString('prop_name', '', 'request');
		$ptototarooms = VikRequest::getInt('tototarooms', '', 'request');
		$potaroomsids = VikRequest::getVar('otaroomsids', array());
		$potaroomsnames = VikRequest::getVar('otaroomsnames', array());
		$pvbroomsids = VikRequest::getVar('vbroomsids', array());
		$potapricing = VikRequest::getVar('otapricing', array());
		$tototaids = count($potaroomsids);
		$tototanames = count($potaroomsnames);
		$totvbids = count($pvbroomsids);
		if ($ptototarooms == 0) {
			VikError::raiseWarning('', JText::_('VCMSAVERSYNCHERRNOROOMSOTA'));
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=roomsynch");
			exit;
		}
		if ($tototaids == 0 || $tototanames == 0 || $totvbids == 0) {
			VikError::raiseWarning('', JText::_('VCMSAVERSYNCHERREMPTYVALUES'));
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=roomsynch");
			exit;
		}
		if ($tototaids != $tototanames || $tototaids != $totvbids) {
			VikError::raiseWarning('', JText::_('VCMSAVERSYNCHERRDIFFVALUES'));
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=roomsynch");
			exit;
		}

		/**
		 * Activation of the subscription based on the number of room types.
		 * This is a mandatory operation to not face connection issues.
		 * 
		 * @since 	1.6.11
		 */
		$pmax_rooms = VikRequest::getInt('max_rooms', 0, 'request');
		if ($pmax_rooms > 0) {
			$mapping_units = 0;
			$q = "SELECT SUM(`units`) FROM `#__vikbooking_rooms` WHERE `id` IN (".implode(', ', array_unique($pvbroomsids)).");";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$mapping_units = (int)$dbo->loadResult();
			}
			if ($mapping_units > $pmax_rooms) {
				VikError::raiseWarning('', JText::sprintf('VCMSAVERSYNCHERRSUBSCRLIM', $pmax_rooms, $mapping_units));
				$mainframe->redirect("index.php?option=com_vikchannelmanager&task=roomsynch");
				exit;
			}
		}
		//

		$rel = array();
		$relnames = array();
		$relplans = array();
		foreach($potaroomsids as $k => $otaid) {
			if (empty($potaroomsnames[$k])) {
				/**
				 * Some channels, like Airbnb API, may not return a name for the newly
				 * created listings, and so we use the OTA room ID temporarily.
				 * 
				 * @since 	1.8.0
				 */
				$potaroomsnames[$k] = $otaid;
			}
			if (!empty($otaid) && !empty($pvbroomsids[$k])) {
				$rel[$k] = $otaid.'_'.$pvbroomsids[$k];
				$relnames[$k] = $potaroomsnames[$k];
				$relplans[$k] = $potapricing[$k];
			}
		}
		
		$module = VikChannelManager::getActiveModule(true);

		// make sure the channel allows mapping
		if (empty($module['av_enabled'])) {
			VikError::raiseWarning('', 'Please do not change the active channel when saving the room relations. The current channel does not support relations.');
			$mainframe->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}

		if (empty($rel)) {
			/**
			 * Do not proceed as some request variables could have been truncated.
			 * To remove the relations with an account it's necessary to use the page
			 * Hotel - Rooms Relations and remove the desired records from that page.
			 * 
			 * @since 	1.8.8
			 */
			VikError::raiseWarning('', 'Saving failed because no relations were actually selected. This may be caused by truncated POST values by your server. To remove the room relations with a precise channel account, please use the apposite page Hotel - Rooms Relations.');
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=rooms");
			exit;
		}

		// BC for VCM 1.4.0: rooms mapped for channels with av_enabled=1 must have the prop_params not empty
		// Those upgrading to 1.4.0 will have this value empty so it is necessary to remove those records
		$q = "SELECT * FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel`=".intval($module['uniquekey']).";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$prev_mapped = $dbo->loadAssocList();
			foreach ($prev_mapped as $prev_map) {
				if (empty($prev_map['prop_params'])) {
					$q = "DELETE FROM `#__vikchannelmanager_roomsxref` WHERE `id`=".$prev_map['id'].";";
					$dbo->setQuery($q);
					$dbo->execute();
				}
			}
		}

		// delete all previously mapped rooms with this same account so that we can store the newly mapped rooms
		$q = "DELETE FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel`=" . intval($module['uniquekey']) . " AND `prop_params`=" . $dbo->quote($module['params']) . ";";
		if ($module['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI) {
			// the refresh token may be different in case of disconnection and re-connection, so we only compare the user_id
			$ch_params = json_decode($module['params'], true);
			if (is_array($ch_params) && !empty($ch_params['user_id'])) {
				// use a more precise query
				$q = "DELETE FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel`=" . intval($module['uniquekey']) . " AND `prop_params` LIKE " . $dbo->quote('%"user_id":"' . $ch_params['user_id'] . '"%') . ";";
			}
		}
		$dbo->setQuery($q);
		$dbo->execute();

		$rel = array_unique($rel);
		$totrelcreated = 0;
		$vbo_rooms_involved = array();

		// grab VBO rooms involved first
		foreach ($rel as $k => $r) {
			$parts = explode('_', $r);
			$vbo_room_id = (int)trim($parts[1]);
			if (!in_array($vbo_room_id, $vbo_rooms_involved)) {
				array_push($vbo_rooms_involved, $vbo_room_id);
			}
		}

		/**
		 * For Google Hotel we make a Property Data request.
		 * If this fails, we do not store any relations on VCM.
		 * 
		 * @since 	1.8.4
		 */
		if ($module['uniquekey'] == VikChannelManagerConfig::GOOGLEHOTEL && count($vbo_rooms_involved)) {
			$tn_errors = VikChannelManager::transmitPropertyData($module, $vbo_rooms_involved);
			if (is_string($tn_errors)) {
				// display error and redirect without proceeding any further
				VikError::raiseWarning('', $tn_errors);
				// display another error to inform the user that Google must accept their rooms
				VikError::raiseWarning('', JText::_('VCM_GOOGLE_PROPDATA_ERR'));
				VikError::raiseWarning('', JText::_('VCM_GOOGLE_PROPDATA_3DAYS'));
				$mainframe->redirect("index.php?option=com_vikchannelmanager&task=roomsynch");
				exit;
			}
		}

		/**
		 * For Vrbo we make a request to transmit the eligible listings.
		 * If this fails, we do not store any relations on VCM.
		 * 
		 * @since 	1.8.12
		 */
		if ($module['uniquekey'] == VikChannelManagerConfig::VRBOAPI && count($vbo_rooms_involved)) {
			$tn_errors = VCMVrboListing::transmitEligibleListings($module, $vbo_rooms_involved);
			if (is_string($tn_errors)) {
				// display error and redirect without proceeding any further
				VikError::raiseWarning('', nl2br($tn_errors));
				$mainframe->redirect("index.php?option=com_vikchannelmanager&task=roomsynch");
				exit;
			}

			// decode channel params
			$ch_params = json_decode($module['params'], true);
			$ch_params = is_array($ch_params) ? $ch_params : [];

			// get account key
			$account_key = VCMFactory::getConfig()->get('account_key_' . VikChannelManagerConfig::VRBOAPI, '');
			if (!empty($ch_params['params']) && !empty($ch_params['params']['hotelid'])) {
				$account_key = $ch_params['params']['hotelid'];
			}

			// make sure to set the proper account params
			$ch_params['hotelid'] = $account_key;
			$module['params'] = json_encode($ch_params);
		}

		// store relations
		foreach ($rel as $k => $r) {
			$parts = explode('_', $r);
			// insert new relation record
			$relation = new stdClass;
			$relation->idroomvb = trim($parts[1]);
			$relation->idroomota = trim($parts[0]);
			$relation->idchannel = intval($module['uniquekey']);
			$relation->channel = $module['name'];
			$relation->otaroomname = $relnames[$k];
			$relation->otapricing = $relplans[$k];
			$relation->prop_name = $prop_name;
			$relation->prop_params = $module['params'];
			
			if ($dbo->insertObject('#__vikchannelmanager_roomsxref', $relation, 'id')) {
				$totrelcreated++;
			}
		}

		/**
		 * For Booking.com we store the pricing model into the Bulk Rates Advanced Parameters.
		 * 
		 * @since 	1.7.2
		 */
		if ($module['uniquekey'] == VikChannelManagerConfig::BOOKING) {
			// find the pricing model from the mapping information
			$pricing_model = null;
			$pricing_model_map = array();
			foreach ($potapricing as $k => $opricing) {
				$pricing_info = json_decode($opricing, true);
				$pricing_info = !is_array($pricing_info) ? array() : $pricing_info;
				if (!isset($pricing_info['RatePlan'])) {
					continue;
				}
				foreach ($pricing_info['RatePlan'] as $rplan_id => $rplan_info) {
					if (!isset($rplan_info['pmodel']) || empty($rplan_info['pmodel'])) {
						continue;
					}
					// update global pricing model
					$pricing_model = $rplan_info['pmodel'];
					// update relation between rate plan ID and pricing model
					$pricing_model_map[$rplan_id] = $rplan_info['pmodel'];
				}
			}
			// update values
			if (!empty($pricing_model)) {
				// we can update the values in the Bulk Rates Advanced Parameters
				$adv_params = VikChannelManager::getBulkRatesAdvParams();
				
				// global pricing model for this channel
				$adv_params['bcom_pricing_model'] = $pricing_model;
				// store pricing model also at rate level
				foreach ($pricing_model_map as $rplan_id => $pmodel) {
					$adv_params['bcom_pricing_model_' . $rplan_id] = $pmodel;
				}
				// we also try to store the pricing model at account level
				$hotelid = null;
				$account_params = is_array($module['params']) ? $module['params'] : json_decode($module['params'], true);
				$account_params = !is_array($account_params) ? array() : $account_params;
				foreach ($account_params as $paramv) {
					if (!empty($paramv)) {
						// we grab the first account param, which should be the Hotel ID
						$hotelid = $paramv;
						break;
					}
				}
				if (!empty($hotelid)) {
					$adv_params['bcom_pricing_model_' . $hotelid] = $pricing_model;
				}

				// update Bulk Rates Advanced Parameters
				VikChannelManager::updateBulkRatesAdvParams($adv_params);
			}
		}

		/**
		 * For Airbnb api we make a listing-mapping request.
		 * 
		 * @since 	1.8.0
		 */
		if ($module['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI) {
			// build listings mapped associative data
			$listings_mapped = [];
			foreach ($rel as $k => $r) {
				$parts = explode('_', $r);
				$listing_id = trim($parts[0]);
				// push listing
				$listings_mapped[$listing_id] = $relnames[$k];
			}

			// transmit the information
			$success = false;
			try {
				$success = VikChannelManager::transmitListingsMapping($module, $listings_mapped);
			} catch (Exception $e) {
				// raise error message
				VikError::raiseWarning('', $e->getMessage());
			}

			if (!$success) {
				// raise another warning because it is fundamental for the listings mapped to be transmitted to e4jConnect
				VikError::raiseWarning('', JText::_('VCM_AIRBNB_MAPPING_CRITICAL'));
			}
		}

		$mainframe->enqueueMessage(JText::sprintf('VCMRELATIONSSAVED', $totrelcreated));
		$mainframe->redirect("index.php?option=com_vikchannelmanager&task=rooms&checkfs=1");
		exit;
	}

	/**
	 * Submits the credentials to e4jConnect.
	 * 
	 * @param 	string 	$ch_name 		The channel name.
	 * @param 	int 	$ch_key 		The channel unique key.
	 * @param 	array 	$ch_params 		The channel credentials.
	 * @param 	array 	$ch_settings 	The channel settings.
	 * 
	 * @return 	void|string 			If a string is returned, a critical error occurred.
	 * 
	 * @since 	1.8.11 	the method returns a string in case of a critical error (i.e. forbidden credentials submitted).
	 */
	private function sendCredentials($ch_name, $ch_key, $ch_params, $ch_settings)
	{
		$is_metasearch = in_array($ch_key, array(VikChannelManagerConfig::TRIP_CONNECT, VikChannelManagerConfig::TRIVAGO));
		
		$api_key = VikChannelManager::getApiKey(true);
		
		$e4jc_url = "https://e4jconnect.com/channelmanager/?r=pwd&c=generic";
		
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager PWD Request e4jConnect.com - VikBooking -->
<PasswordRQ xmlns="http://www.e4jconnect.com/schemas/pwdrq">
	<Notify client="'.JUri::root().'"/>
	<Params>';
		foreach ($ch_params as $k => $v) {
			$safe_attr = defined('ENT_XML1') ? htmlspecialchars($v, ENT_XML1 | ENT_COMPAT, 'UTF-8') : htmlentities($v);
			$xml .= '<Param name="' . $k . '" value="' . $safe_attr . '"/>';
		}
		$xml .= '</Params>
	<Settings>';
		foreach ($ch_settings as $k => $v) {
			if (defined('ENT_XML1')) {
				// only available from PHP 5.4 and on
				$setting_ent = htmlspecialchars($v, ENT_XML1 | ENT_COMPAT, 'UTF-8');
			} else {
				// fallback to plain all html entities
				$setting_ent = htmlentities($v);
			}
			
			if ($is_metasearch) {
				/**
				 * We try to prevent some XML Schema errors caused by accented words.
				 * However, the previous use of htmlentities() has been replaced with
				 * the above htmlspecialchars() by converting ", &, < and >, and this
				 * should already prevent most errors, of course only if PHP >= 5.4.
				 * 
				 * @since 	1.7.2
				 */
				$setting_ent = str_replace(array(
					'&agrave;', 
					'&egrave;', 
					'&igrave;', 
					'&ograve;', 
					'&ugrave;',
				), array(
					'à', 
					'è', 
					'ì', 
					'ò', 
					'ù',
				), $setting_ent);
			}

			$xml .= '<Setting name="' . $k . '" value="' . $setting_ent . '"/>';
		}
		$xml .= '</Settings>
	<Api key="'.$api_key.'"/>
	<Fetch channel="'.$ch_name.'" ukey="'.$ch_key.'"/>
</PasswordRQ>';
		
		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		$rs = $e4jC->exec();
		if ($e4jC->getErrorNo()) {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($e4jC->getErrorMsg()));
			return;
		}
		if (substr($rs, 0, 11) == 'e4j.warning') {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
			return;
		}
		if (substr($rs, 0, 9) == 'e4j.error') {
			// critical error string will be returned
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
			return $rs;
		}

		$config = VCMFactory::getConfig();
		if ($ch_key == VikChannelManagerConfig::TRIP_CONNECT) {
			$args = explode('::', $rs);
			if (count($args) == 2) {
				$config->set('tac_account_id', $args[0]);
				$config->set('tac_api_key', $args[1]);
			}
		} elseif ($ch_key == VikChannelManagerConfig::TRIVAGO) {
			$args = explode('::', $rs);
			if (count($args) == 2) {
				$config->set('tri_account_id', $args[0]);
			}
		}

		/**
		 * When sending the credentials for any channels, update the last_endpoint URL in config
		 * only in case it's empty or if the current endpoint is equal to the last one (no update).
		 */
		$last_endpoint = VikChannelManager::getLastEndpoint();
		if (empty($last_endpoint)) {
			VikChannelManager::getLastEndpoint(true);
		}
	}

	/**
	 * This task was added in VCM 1.6.3 to update the Endpoint URL for all channel credentials
	 * in case the system detects a different protocol or a different base domain name.
	 * The action must be confirmed via back-end, in order to be triggered.
	 */
	public function update_endpoints()
	{
		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();
		$credentials_map = [];

		$q = "SELECT `c`.`id`,`c`.`name`,`c`.`params`,`c`.`uniquekey`,`c`.`av_enabled`,`c`.`settings`,`x`.`idchannel`,`x`.`prop_params` ".
			"FROM `#__vikchannelmanager_channel` AS `c` LEFT JOIN `#__vikchannelmanager_roomsxref` AS `x` ON `c`.`uniquekey`=`x`.`idchannel` ".
			"ORDER BY `c`.`uniquekey` ASC;";
		$dbo->setQuery($q);
		$rows = $dbo->loadAssocList();
		if ($rows) {
			foreach ($rows as $row) {
				if (empty($row['name']) || empty($row['params'])) {
					//always skip channels with no params
					continue;
				}
				if (empty($row['prop_params'])) {
					//no rooms mapped or no 'av_enabled = 1' for this channel
					if (isset($credentials_map[$row['uniquekey']])) {
						//just one credentials update for this kind of channels
						continue;
					}
					//store map and send credentials to e4jConnect
					$credentials_map[$row['uniquekey']] = [$row['params']];
					$ch_settings = json_decode($row['settings'], true);
					if (is_array($ch_settings) && $ch_settings) {
						foreach ($ch_settings as $k => $v) {
							if (isset($v['value'])) {
								$ch_settings[$k] = is_array($v['value']) ? json_encode($v['value']) : $v['value'];
							}
						}
					} else {
						$ch_settings = [];
					}
					$this->sendCredentials($row['name'], $row['uniquekey'], json_decode($row['params'], true), $ch_settings);
				} else {
					//channels with some rooms mapped
					if (!isset($credentials_map[$row['uniquekey']])) {
						$credentials_map[$row['uniquekey']] = [];
					}
					if (!in_array($row['params'], $credentials_map[$row['uniquekey']])) {
						//store map and send credentials to e4jConnect
						array_push($credentials_map[$row['uniquekey']], $row['params']);
						$ch_settings = json_decode($row['settings'], true);
						if (is_array($ch_settings) && $ch_settings) {
							foreach ($ch_settings as $k => $v) {
								if (isset($v['value'])) {
									$ch_settings[$k] = is_array($v['value']) ? json_encode($v['value']) : $v['value'];
								}
							}
						} else {
							$ch_settings = [];
						}
						$this->sendCredentials($row['name'], $row['uniquekey'], json_decode($row['params'], true), $ch_settings);
					}
					if ($row['params'] != $row['prop_params']) {
						if (!in_array($row['prop_params'], $credentials_map[$row['uniquekey']])) {
							//store map and send credentials to e4jConnect
							array_push($credentials_map[$row['uniquekey']], $row['prop_params']);
							$ch_settings = json_decode($row['settings'], true);
							if (is_array($ch_settings) && $ch_settings) {
								foreach ($ch_settings as $k => $v) {
									if (isset($v['value'])) {
										$ch_settings[$k] = is_array($v['value']) ? json_encode($v['value']) : $v['value'];
									}
								}
							} else {
								$ch_settings = [];
							}
							$this->sendCredentials($row['name'], $row['uniquekey'], json_decode($row['prop_params'], true), $ch_settings);
						}
					}
				}
			}
		}

		if ($credentials_map) {
			$app->enqueueMessage(JText::_('VCMUPDATEENDPSUCC'));
		}

		// always update the last endpoint used, because we have notified the servers
		VikChannelManager::getLastEndpoint(true);

		// redirect
		$app->redirect('index.php?option=com_vikchannelmanager&task=config');
		$app->close();
	}

	private function sendPwdRemoval($ch_name, $ch_key, $ch_params, $ch_settings) {
		$api_key = VikChannelManager::getApiKey(true);
		
		$e4jc_url = "https://e4jconnect.com/channelmanager/?r=pwd&c=generic";
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager PWD Request e4jConnect.com - VikBooking -->
<PasswordRQ xmlns="http://www.e4jconnect.com/schemas/pwdrq">
	<Notify client="'.JUri::root().'"/>
	<Params>';
		foreach ($ch_params as $k => $v) {
			$xml .= '<Param name="'.$k.'" value="'.$v.'"/>';
		}
		$xml .= '</Params>
	<Settings>';
		foreach ($ch_settings as $k => $v) {
			if (!is_null($v) && !is_scalar($v)) {
				$v = json_encode($v);
			}
			if (defined('ENT_XML1')) {
				// only available from PHP 5.4 and on
				$setting_ent = htmlspecialchars((string) $v, ENT_XML1 | ENT_COMPAT, 'UTF-8');
			} else {
				// fallback to plain all html entities
				$setting_ent = htmlentities($v);
			}
			$xml .= '<Setting name="' . $k . '" value="' . $setting_ent . '"/>';
		}
		$xml .= '</Settings>
	<Api key="'.$api_key.'"/>
	<Fetch channel="'.$ch_name.'" ukey="'.$ch_key.'" action="remove"/>
</PasswordRQ>';
		
		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		$rs = $e4jC->exec();
		if ($e4jC->getErrorNo()) {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($e4jC->getErrorMsg()));
			return;
		}
		if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
			return;
		}
	}

	/**
	 * Transmits the hotel details to the e4jConnect Hotels Inventory.
	 * 
	 * @param 	array 	$args 				associative array of hotel details.
	 * @param 	array 	$send_data_opts 	associative array of multiple account data.
	 * 
	 * @return 	int
	 * 
	 * @since 	1.8.6 	added second argument for multiple hotel accounts
	 * @since  	1.9.10  added support to TripConnect data options for multiple accounts.
	 */
	private function sendHotelDetails($args, $send_data_opts = [])
	{
		$channel = VikChannelManager::getActiveModule(true);
		if ($channel['uniquekey'] == VikChannelManagerConfig::TRIP_CONNECT) {
			return $this->sendTripConnectHotelDetails($args, $channel, $send_data_opts);
		} elseif ($channel['uniquekey'] == VikChannelManagerConfig::TRIVAGO) {
			// NOTICE: do not send the countrycode for trivago
			unset($args['countrycode']);
			//
			return $this->sendTrivagoHotelDetails($args, $channel);
		}

		return $this->sendGenericHotelDetails($args, $send_data_opts);
	}

	/**
	 * Stores on the remote servers the hotel details for TripAdvisor (TripConnect).
	 * 
	 * @param 	array 	$args 		Associative list of hotel details.
	 * @param 	array 	$channel 	The TripConnect channel-account data.
	 * @param 	array 	$data_opts 	Optional list of multiple account data.
	 * 
	 * @return 	int
	 * 
	 * @since 	1.9.10 	Added argument $data_opts.
	 */
	private function sendTripConnectHotelDetails(array $args, array $channel, array $data_opts = [])
	{
		$dbo = JFactory::getDbo();
		$session = JFactory::getSession();

		$api_key = VikChannelManager::getApiKey(true);
		$channel['params'] = (array) json_decode($channel['params'], true);

		$args['amenities'] = json_encode(explode(',', $args['amenities']));
		$args['currency'] = VikChannelManager::getCurrencyName(true);

		/**
		 * Load proper languages in the correct order through VBO.
		 * 
		 * @since 	1.8.6
		 */
		$langs_list = [];
		if (!class_exists('VikBooking')) {
			require_once (VBO_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "lib.vikbooking.php");
		}
		try {
			$vbo_tn = VikBooking::getTranslator();
			$all_langs = array_keys($vbo_tn->getLanguagesList());
			foreach ($all_langs as $lang) {
				$ltag = substr(strtolower($lang), 0, 2);
				if (!in_array($ltag, $langs_list)) {
					$langs_list[] = $ltag;
				}
			}
		} catch (Exception $e) {
			// do nothing
		}
		// set known languages with a valid order
		$args['languages'] = json_encode($langs_list);

		/**
		 * We count the number of total rooms for the inventory
		 * 
		 * @since 	1.6.12
		 */
		$q = "SELECT SUM(`units`) FROM `#__vikbooking_rooms` WHERE `avail`=1;";
		$dbo->setQuery($q);
		$totrooms = $dbo->loadResult();
		$args['totrooms'] = $totrooms;

		/**
		 * Identify the proper TripConnect account ID we are creating/updating, by supporting multiple accounts.
		 * 
		 * @since 	1.9.10
		 */
		$ta_account_main_ta_id = VCMFactory::getConfig()->get('tac_partner_ta_id');
		$ta_hotel_account_id = ($data_opts['account_id'] ?? 0) ?: $ta_account_main_ta_id;
		
		// build the request
		$e4jc_url = "https://e4jconnect.com/channelmanager/?r=tachd&c=tripadvisor";
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager TACHD Request e4jConnect.com - VikBooking - TripAdvisor Module extensionsforjoomla.com -->
<HotelDetailsRQ xmlns="http://www.e4jconnect.com/tripadvisor/tachdrq">
	<Notify client="' . JUri::root() . '"/>
	<Authentication username="' . ($channel['params']['username'] ?? '') . '" password="' . ($channel['params']['password'] ?? '') . '"/>
	<Hotel id="' . ($ta_hotel_account_id ?: $channel['params']['tripadvisorid']) . '" multi_id="' . ($data_opts['multi_id'] ?? 0) . '"/>
	<Api key="' . $api_key . '"/>
	<HotelDetails>';
	foreach ($args as $k => $v) {
		if ($k == 'main_pic') {
			// do not submit the main picture via XML
			continue;
		}
		$xml .= '<'.ucwords($k).'>'.htmlspecialchars($v).'</'.ucwords($k).'>';
	}
	$xml .= '</HotelDetails>
</HotelDetailsRQ>';

		//echo htmlentities($xml);die;
		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		$rs = $e4jC->exec();
		
		VikChannelManager::validateChannelResponse($rs);

		if ($e4jC->getErrorNo()) {
			$session->set('hd-force-next-request', 1);
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($e4jC->getErrorMsg()));
			return 0;
		}
		
		if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			$session->set('hd-force-next-request', 1);
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
			return 0;
		}

		$session->set('hd-force-next-request', 0);
		
		if (!($data_opts['multi_id'] ?? 0)) {
			// save the main partner (inventory) ID as well as the main partner TripAdvisor ID (from settings)
			VCMFactory::getConfig()->set('tac_partner_id', $rs);
			VCMFactory::getConfig()->set('tac_partner_ta_id', $channel['params']['tripadvisorid']);

			if (VikChannelManager::hasGoogleHotelChannel()) {
				// set hotel inventory ID
				VikChannelManager::getHotelInventoryID($rs);
			}
		} else {
			// store a list of multi-account partner ID relations for TripConnect
			$tac_multi_accounts = (array) VCMFactory::getConfig()->getArray('tac_partner_id_multi', []);
			// merge current data
			$tac_multi_accounts[$channel['params']['tripadvisorid']] = $rs;
			// update the information
			VCMFactory::getConfig()->set('tac_partner_id_multi', $tac_multi_accounts);
		}

		return 1;
	}

	private function sendTrivagoHotelDetails($args, $channel)
	{
		$dbo = JFactory::getDbo();
		$session = JFactory::getSession();

		$api_key = VikChannelManager::getApiKey(true);
		$channel['params'] = json_decode($channel['params'], true);

		$args['amenities'] = json_encode(explode(',', $args['amenities']));
		$args['currency'] = VikChannelManager::getCurrencyName(true);

		/**
		 * Load proper languages in the correct order through VBO.
		 * 
		 * @since 	1.8.6
		 */
		$langs_list = [];
		if (!class_exists('VikBooking')) {
			require_once (VBO_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "lib.vikbooking.php");
		}
		try {
			$vbo_tn = VikBooking::getTranslator();
			$all_langs = array_keys($vbo_tn->getLanguagesList());
			foreach ($all_langs as $lang) {
				$ltag = substr(strtolower($lang), 0, 2);
				if (!in_array($ltag, $langs_list)) {
					$langs_list[] = $ltag;
				}
			}
		} catch (Exception $e) {
			// do nothing
		}
		// set known languages with a valid order
		$args['languages'] = json_encode($langs_list);

		/**
		 * We count the number of total rooms for the inventory
		 * 
		 * @since 	1.6.12
		 */
		$q = "SELECT SUM(`units`) FROM `#__vikbooking_rooms` WHERE `avail`=1;";
		$dbo->setQuery($q);
		$dbo->execute();
		$totrooms = $dbo->loadResult();
		$args['totrooms'] = $totrooms;
		//
		
		$e4jc_url = "https://e4jconnect.com/channelmanager/?r=trihd&c=trivago";
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager TRIHD Request e4jConnect.com - VikBooking - Trivago Module extensionsforjoomla.com -->
<HotelDetailsRQ xmlns="http://www.e4jconnect.com/trivago/trihdrq">
	<Notify client="'.JUri::root().'"/>
	<Authentication hotelname="' . htmlspecialchars($channel['params']['hotelname']) . '"/>
	<Hotel id="'.intval(VikChannelManager::getTrivagoPartnerID()).'"/>
	<Api key="'.$api_key.'"/>
	<HotelDetails>';
	foreach ($args as $k => $v) {
		if ($k == 'main_pic') {
			// do not submit the main picture via XML
			continue;
		}
		$xml .= '<'.ucwords($k).'>'.htmlspecialchars($v).'</'.ucwords($k).'>';
	}
	$xml .= '</HotelDetails>
</HotelDetailsRQ>';

		//echo htmlentities($xml);die;
		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		$rs = $e4jC->exec();

		VikChannelManager::validateChannelResponse($rs);

		if ($e4jC->getErrorNo()) {
			$session->set('hd-force-next-request', 1);
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($e4jC->getErrorMsg()));
			return 0;
		}

		if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			$session->set('hd-force-next-request', 1);
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
			return 0;
		}

		$session->set('hd-force-next-request', 0);
		
		$q = "UPDATE `#__vikchannelmanager_config` SET `setting`=".$dbo->quote($rs)." WHERE `param`='tri_partner_id' LIMIT 1;";
		$dbo->setQuery($q);
		$dbo->execute();

		if (VikChannelManager::hasGoogleHotelChannel()) {
			// set hotel inventory ID
			VikChannelManager::getHotelInventoryID($rs);
		}

		return 1;
	}

	/**
	 * Transmits the hotel details to the e4jConnect Hotels Inventory (not for trivago or tripadvisor).
	 * 
	 * @param 	array 	$args 				associative array of hotel details.
	 * @param 	array 	$send_data_opts 	associative array of multiple account data.
	 * 
	 * @return 	int
	 * 
	 * @since 	1.8.6 	added second argument for multiple hotel accounts
	 */
	private function sendGenericHotelDetails($args, $send_data_opts = [])
	{
		$dbo = JFactory::getDbo();
		$session = JFactory::getSession();

		$api_key = VikChannelManager::getApiKey(true);

		$args['amenities'] = json_encode(explode(',', $args['amenities']));
		$args['currency'] = VikChannelManager::getCurrencyName(true);

		/**
		 * Load proper languages in the correct order through VBO.
		 * 
		 * @since 	1.8.6
		 */
		$langs_list = [];
		if (!class_exists('VikBooking')) {
			require_once (VBO_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "lib.vikbooking.php");
		}
		try {
			$vbo_tn = VikBooking::getTranslator();
			$all_langs = array_keys($vbo_tn->getLanguagesList());
			foreach ($all_langs as $lang) {
				$ltag = substr(strtolower($lang), 0, 2);
				if (!in_array($ltag, $langs_list)) {
					$langs_list[] = $ltag;
				}
			}
		} catch (Exception $e) {
			// do nothing
		}
		// set known languages with a valid order
		$args['languages'] = json_encode($langs_list);

		/**
		 * We count the number of total rooms for the inventory
		 * 
		 * @since 	1.6.12
		 */
		$q = "SELECT SUM(`units`) FROM `#__vikbooking_rooms` WHERE `avail`=1;";
		$dbo->setQuery($q);
		$dbo->execute();
		$totrooms = $dbo->loadResult();
		$args['totrooms'] = $totrooms;

		// prepare special node for multi-account, if needed
		$multi_acc_node = '';
		if (!empty($send_data_opts)) {
			$multi_acc_node = "\n\t<MultiAccount><![CDATA[" . json_encode($send_data_opts) . "]]></MultiAccount>\n";
		}

		$e4jc_url = "https://e4jconnect.com/channelmanager/?r=ehd&c=generic";
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager EHD Request e4jConnect.com - VikBooking -->
<e4jHotelDetailsRQ xmlns="http://www.e4jconnect.com/schemas/ehdrq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . $api_key . '"/>' . $multi_acc_node . '
	<HotelDetails>';
	foreach ($args as $k => $v) {
		if ($k == 'main_pic') {
			// do not submit the main picture via XML
			continue;
		}
		$xml .= '<'.ucwords($k).'>'.htmlspecialchars($v).'</'.ucwords($k).'>';
	}
	$xml .= '</HotelDetails>
</e4jHotelDetailsRQ>';

		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		$rs = $e4jC->exec();

		VikChannelManager::validateChannelResponse($rs);

		if ($e4jC->getErrorNo()) {
			$session->set('hd-force-next-request', 1);
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($e4jC->getErrorMsg()));
			return 0;
		}

		if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			$session->set('hd-force-next-request', 1);
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
			return 0;
		}

		$session->set('hd-force-next-request', 0);

		/**
		 * The response should contain the hotel inventory ID.
		 * Example responses: e4j.ok.new.5555 or e4j.ok.upd.5555
		 * 
		 * @since 	1.8.4
		 */
		$was_new_property = (strpos($rs, 'new') !== false);
		$hinv_id = preg_replace("/[^0-9]+/", '', str_replace('e4j', '', $rs));
		if (!empty($hinv_id)) {
			// set hotel inventory ID by passing the multi-account information, if any
			VikChannelManager::getHotelInventoryID($hinv_id, $send_data_opts);
			if (!empty($send_data_opts) && !empty($send_data_opts['multi_id'])) {
				// update account ID for this multiple hotel
				VCMGhotelMultiaccounts::updateInventoryAccountId($send_data_opts['multi_id'], $hinv_id);
			}
			if ($was_new_property) {
				// set the submission date of the hotels inventory
				VikChannelManager::setHotelInventoryDate(date('Y-m-d H:i:s'));
			}
			/**
			 * If we have received an image as the main property picture, we transfer it
			 * to the central servers at e4jConnect so that they will be ready to use it.
			 * 
			 * @since 	1.8.7
			 * @since 	1.8.11 	if Google Hotel is not enabled, we would get an authentication error, so we prevent that.
			 */
			if (VikChannelManager::hasGoogleHotelChannel()) {
				$picture_res = VCMGhotelMultiaccounts::transferMainPicture($args['main_pic'], $hinv_id, $was_new_property);
				if (is_string($picture_res) && strlen($picture_res)) {
					// it would be true in case of success, so we raise just the error message
					VikError::raiseWarning('', $picture_res);
				}
			}
		}

		return 1;
	}

	/**
	 * Submits to the remote servers the rooms inventory details for TripAdvisor (TripConnect).
	 * 
	 * @param 	string 	$ta_account_id 	Optional TA account ID to use.
	 * 
	 * @return 	array 					Operation decoded response.
	 * 
	 * @since 	1.9.10 added support to multiple accounts
	 */
	private function sendTripConnectRoomsInventory($ta_account_id = null)
	{
		$dbo = JFactory::getDbo();

		$api_key = VikChannelManager::getApiKey(true);

		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = (array) json_decode($channel['params'], true);

		$ta_account_id = $ta_account_id ?: $channel['params']['tripadvisorid'];

		$q = "SELECT * FROM `#__vikchannelmanager_tac_rooms` WHERE `account_id` IS NULL OR `account_id` = " . $dbo->q($ta_account_id) . ";";
		$dbo->setQuery($q);
		$tac_rooms = $dbo->loadAssocList();

		$e4jc_url = "https://e4jconnect.com/channelmanager/?r=tacrd&c=tripadvisor";
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager TACRD Request e4jConnect.com - VikBooking - TripAdvisor Module extensionsforjoomla.com -->
<RoomsDetailsRQ xmlns="http://www.e4jconnect.com/tripadvisor/tacrdrq">
	<Notify client="' . JUri::root() . '"/>
	<Authentication username="' . ($channel['params']['username'] ?? '') . '" password="' . ($channel['params']['password'] ?? '') . '"/>
	<Hotel id="' . $ta_account_id . '"/>
	<Api key="' . $api_key . '"/>
	<RoomsDetails>';
	foreach ($tac_rooms as $row) {
		if (!empty($row['amenities'])) {
			$row['amenities'] = json_encode(explode(',', $row['amenities']));
		} else {
			$row['amenities'] = json_encode(array());
		}
		$row['url'] = urlencode($row['url']);
		
		$xml .= '<Room>';
		$xml .= '<Idvb>'.$row['id_vb_room'].'</Idvb>';
		$xml .= '<Name>'.htmlspecialchars($row['name']).'</Name>';
		$xml .= '<Url>'.htmlspecialchars($row['url']).'</Url>';
		$xml .= '<Description>'.htmlspecialchars($row['desc']).'</Description>';
		$xml .= '<Amenities>'.$row['amenities'].'</Amenities>';
		$xml .= '<Code>'.$row['codes'].'</Code>';
		$xml .= '<Cost>'.number_format($row['cost'], 2, ".", "").'</Cost>';
		$xml .= '</Room>';
	}
	$xml .= '</RoomsDetails>
</RoomsDetailsRQ>';
		
		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		$rs = $e4jC->exec();
		
		VikChannelManager::validateChannelResponse($rs);
		
		if ($e4jC->getErrorNo()) {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($e4jC->getErrorMsg()));
			return;
		}
		if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
			return;
		}
		
		return (array) json_decode($rs, true);
	}

	private function sendTrivagoRoomsInventory() {
		
		$api_key = VikChannelManager::getApiKey(true);
		
		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);
		
		$dbo = JFactory::getDbo();
		$args = array();
		$q = "SELECT * FROM `#__vikchannelmanager_tri_rooms`;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ( $dbo->getNumRows() > 0 ) {
			$args = $dbo->loadAssocList();
		}
		
		$e4jc_url = "https://e4jconnect.com/channelmanager/?r=trird&c=trivago";
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager TRIRD Request e4jConnect.com - VikBooking - Trivago Module extensionsforjoomla.com -->
<RoomsDetailsRQ xmlns="http://www.e4jconnect.com/trivago/trirdrq">
	<Notify client="'.JUri::root().'"/>
	<Authentication hotelname="' . htmlspecialchars($channel['params']['hotelname']) . '"/>
	<Hotel id="'.VikChannelManager::getTrivagoPartnerID().'"/>
	<Api key="'.$api_key.'"/>
	<RoomsDetails>';
	foreach( $args as $row ) {
		$row['amenities'] = json_encode(explode(',', $row['amenities']));
		$row['url'] = urlencode($row['url']);
		
		$xml .= '<Room>';
		$xml .= '<Idvb>'.$row['id_vb_room'].'</Idvb>';
		$xml .= '<Name>'.htmlspecialchars($row['name']).'</Name>';
		$xml .= '<Url>'.htmlspecialchars($row['url']).'</Url>';
		$xml .= '<Description>'.htmlspecialchars($row['desc']).'</Description>';
		$xml .= '<Code>'.$row['codes'].'</Code>';
		$xml .= '<Cost>'.number_format($row['cost'], 2, ".", "").'</Cost>';
		$xml .= '</Room>';
	}
	$xml .= '</RoomsDetails>
</RoomsDetailsRQ>';
		
		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		$rs = $e4jC->exec();

		VikChannelManager::validateChannelResponse($rs);
		
		if ($e4jC->getErrorNo()) {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($e4jC->getErrorMsg()));
			return;
		}
		if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
			return;
		}
		
		return json_decode($rs, true);
		
	}

	public function sendListingsRequest() {
		
		$api_key = VikChannelManager::getApiKey(true);
		
		$channel = VikChannelManager::getActiveModule(true);
		//$channel['params'] = json_decode($channel['params'], true);
		
		$dbo = JFactory::getDbo();
		$args = array();
		$q = "SELECT * FROM `#__vikchannelmanager_listings` WHERE `channel`=".$dbo->quote($channel['uniquekey'] . (isset($channel['ical_channel']) ? '-' . $channel['ical_channel']['id'] : '')).";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ( $dbo->getNumRows() > 0 ) {
			$args = $dbo->loadAssocList();
		}
		
		$e4jc_url = "https://e4jconnect.com/channelmanager/?r=icalurl&c=".$channel['name'];
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager ICALURL Request e4jConnect.com - VikBooking - E4J srl -->
<IcalurlRQ xmlns="http://www.e4jconnect.com/schemas/icalurlrq">
	<Notify client="'.JUri::root().'"/>
	<Api key="'.$api_key.'"/>
	<Listings>';
	foreach ($args as $row) {
		$cust_ical_attr = '';
		$icalname = isset($channel['ical_channel']) ? urlencode($channel['ical_channel']['name']) : '';
		$icalid = isset($channel['ical_channel']) ? intval($channel['ical_channel']['id']) : '';
		if (!empty($icalname) && !empty($icalid)) {
			$cust_ical_attr = ' icalname="' . $icalname . '" icalid="' . $icalid . '"';
		}
		$xml .= '<Listing roomid="' . $row['id_vb_room'] . '" url="' . urlencode($row['retrieval_url']) . '"' . $cust_ical_attr . '/>';
	}
	$xml .= '</Listings>
</IcalurlRQ>';
		
		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		$rs = $e4jC->exec();
		
		VikChannelManager::validateChannelResponse($rs);
		
		if ($e4jC->getErrorNo()) {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($e4jC->getErrorMsg()));
			return;
		}
		if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
			return;
		}
		
		return json_decode($rs, true);
		
	}

	public function input_output_diagnostic() {
		
		if (!function_exists('curl_init')) {
			echo 'e4j.error.'.VikChannelManager::getErrorFromMap('e4j.error.Curl');
			exit;
		}

		$token = VikChannelManager::generateSerialCode(16);

		$filename = VCM_SITE_PATH.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.$token.".txt";

		$handle = fopen($filename, "w");
		if ( $handle === null ) {
			echo 'e4j.error.'.VikChannelManager::getErrorFromMap('e4j.error.File.Permissions.Write');
			exit;
		}
		fclose($handle);

		$api_key = VikChannelManager::getApiKey(true);
		
		$e4jc_url = "https://e4jconnect.com/channelmanager/?r=iod&c=generic";
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager IOD Request e4jConnect.com - VikBooking -->
<InputOutputDiagnosticRQ xmlns="http://www.e4jconnect.com/schemas/iodrq">
	<Notify client="'.JUri::root().'"/>
	<Api key="'.$api_key.'"/>
	<Session token="'.$token.'"/>
</InputOutputDiagnosticRQ>';
		
		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setTimeout(40);
		$e4jC->setConnectTimeout(40);
		$e4jC->slaveEnabled = true;
		$e4jC->setPostFields($xml);
		$rs = $e4jC->exec();
		
		if ($e4jC->getErrorNo()) {
			echo 'e4j.error.'.VikChannelManager::getErrorFromMap($e4jC->getErrorMsg())."<br />".$e4jC->getErrorMsg();
			exit;
		}
		if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			echo 'e4j.error.'.VikChannelManager::getErrorFromMap($rs, true);
			exit;
		}
		if (trim($rs) != "e4j.ok") {
			echo "e4j.error.$rs";
			exit;
		}

		$handle = fopen($filename, "r");
		$bytes = fread($handle, filesize($filename));
		fclose($handle);

		@unlink($filename);

		//add SSL/TLS Info
		if (function_exists('curl_version')) {
			$bytes .= "<br/><hr/><br/>\n";
			$bytes .= "<h3>Server</h3>";
			$curl_info = curl_version();
			$bytes .= "<p><strong>OpenSSL</strong> includes support for TLS v1.1 and TLS v1.2 in OpenSSL 1.0.1 - <strong>NSS</strong> included support for TLS v1.1 in 3.14 and for TLS v1.2 in 3.15</p>\n";
			$bytes .= "<strong>SSL Version: ".(array_key_exists('ssl_version', $curl_info) ? $curl_info['ssl_version'] : '----')."</strong><br/>\n";
			//Howsmyssl.com TLS Check - Start
			$ch = curl_init('https://www.howsmyssl.com/a/check');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$ssl_data = curl_exec($ch);
			curl_close($ch);
			if ($ssl_data !== false) {
				$ssl_extra = json_decode($ssl_data, true);
				if (is_array($ssl_extra) && count($ssl_extra) > 0) {
					$bytes .= "<strong>TLS Version: ".(array_key_exists('tls_version', $ssl_extra) ? $ssl_extra['tls_version'] : '----')."</strong><br/>\n";
				}
			}
			//Howsmyssl.com TLS Check - End
			foreach ($curl_info as $ck => $cv) {
				if (is_array($cv) || $ck == 'ssl_version') {
					continue;
				}
				$bytes .= $ck.': '.$cv."<br/>\n";
			}
		}
		//

		echo json_encode($bytes);
		exit;

	}

	// UPDATE FUNCTION

	public function update_program()
	{
		$app = JFactory::getApplication();

		if (!function_exists('curl_init')) {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap('e4j.error.Curl'));
			$app->redirect('index.php?option=com_vikchannelmanager');
			exit;
		}

		$forcecheck = VikRequest::getInt('forcecheck', 0);
		$force_check = VikRequest::getInt('force_check', 0);
		if (!$forcecheck && !$force_check && !VikChannelManager::isNewVersionAvailable(true)) {
			VikError::raiseWarning('', 'No update available.');
			$app->redirect('index.php?option=com_vikchannelmanager');
			exit;
		}

		$vcmresponse = 'e4j.error';
		$apikey = VikChannelManager::getApiKey();
		if (empty($apikey)) {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap('e4j.error.Settings'));
			$app->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}

		try {
			// obtain the update data information
			$update_data = VCMUpdateHandler::retrieve_update_data($validate = true, $app->input->getString('force_version', null));

			// process update data
			VCMUpdateHandler::process_update_data($update_data);

		} catch (Exception $e) {
			// raise an error
			VikError::raiseWarning('', $e->getMessage());
			$app->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}

		// success
		$app->enqueueMessage(JText::_('VCMDOUPDATECOMPLETED'));
		$app->redirect('index.php?option=com_vikchannelmanager');
		$app->close();
	}

	//APP ACCOUNT UPDATE FUNCTION

	public function app_account_update() 
	{
		$dbo = JFactory::getDbo();
		$email = VikRequest::getString('email', '');
		$action = VikRequest::getString('action', '');
		$pass = VikRequest::getString('pass', '');
		$mainframe = JFactory::getApplication();

		if (empty($pass) && $action == "Remove") {
			$pass = "-";
		}
		if (empty($email) || empty($action) || empty($pass)){
			if (empty($email)) {
				$mainframe->enqueueMessage(JText::_('VCMUPAFAILNOEM'), 'error');
			}
			if (empty($action)) {
				$mainframe->enqueueMessage(JText::_('VCMUPAFAILNOAC'), 'error');
			}
			if (empty($pass)) {
				$mainframe->enqueueMessage(JText::_('VCMUPAFAILNOPS'), 'error');
			}
		} else {
			$api_key = VikChannelManager::getApiKey(true);
			$xmlRQ = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
			<!-- APPUPA Request e4jConnect.com - VikChannelManager - VikBooking -->
			<AppUpdateAccountRQ xmlns=\"http://www.e4jconnect.com/avail/appupa\">
				<Notify client=\"".JUri::root()."\" />
				<Api key=\"".$api_key."\" />
				<AppUpdateAccount action=\"".$action."\">
			  		<Email>".$email."</Email>
					<Password>".$pass."</Password>
				</AppUpdateAccount>
			</AppUpdateAccountRQ>";

			$url = "https://e4jconnect.com/api/app/e4jConnect/upa";

			$e4jC = new E4jConnectRequest($url);
			$e4jC->setPostFields($xmlRQ);
			$rs = $e4jC->exec();
			$xmlResponse = json_decode($rs, true);

			//echo "<pre>".$xmlResponse."</pre>";die;

			if (is_array($xmlResponse)) {
				if (array_key_exists('res', $xmlResponse) && array_key_exists('body', $xmlResponse)) {
					if ($xmlResponse['res'] == 'e4j.ok') {
						$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param` = 'app_accounts';";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() > 0) {
							$appAccounts = $dbo->loadAssoc();
							$appAccounts = json_decode($appAccounts['setting'], true);
							if ($action == "Remove") {
								unset($appAccounts[$email]);
								$q = "SELECT `setting` 
									FROM `#__vikchannelmanager_config` 
									WHERE `param` = 'app_acl'";
								$dbo->setQuery($q);
								$dbo->execute();
								if ($dbo->getNumRows() > 0) {
									$aclData = $dbo->loadAssoc();
									$aclData = json_decode($aclData['setting'], true);
									unset($aclData[$email]);
									$aclData = json_encode($aclData);
									$q = "UPDATE `#__vikchannelmanager_config`
										SET `setting` = ".$dbo->quote($aclData)."
										WHERE `param` = 'app_acl';";
									$dbo->setQuery($q);
									$dbo->execute();
								}
							} else if ($action == "Update"){
								$appAccounts[$email] = $xmlResponse['body'];
							}
							$jsonAccounts = json_encode($appAccounts);
							$q = "UPDATE `#__vikchannelmanager_config` 
								SET `setting` = ".$dbo->quote($jsonAccounts)." 
								WHERE `param` = 'app_accounts';";
							$dbo->setQuery($q);
							$dbo->execute();
							$mainframe->enqueueMessage(JText::_('VCMUPASUCC'));
						}
						else
						{
							$mainframe->enqueueMessage(JText::_('VCMUPAFAILNODB'), 'error');
						}
					}
					else
					{
						VikError::raiseWarning('', VikChannelManager::getErrorFromMap($xmlResponse['body']));
					}
				}
				else
				{
					$mainframe->enqueueMessage(JText::_('VCMUPAFAILRESERR'), 'error');
				}
			}
			else{
				$mainframe->enqueueMessage(JText::_('VCMUPAFAILRESERR'), 'error');
			}
		}
		$mainframe->redirect('index.php?option=com_vikchannelmanager&task=config');
	}
	
	public function appconfig() {
		VCM::printMenu();
	
		VikRequest::setVar('view', VikRequest::getCmd('view', 'appconfig'));
	
		parent::display();
		
		VCM::printFooter();
	}

	public function saveAppConfig() 
	{
		$dbo = JFactory::getDbo();
		$mainframe = JFactory::getApplication();
		$api_key = VikChannelManager::getApiKey(true);

		/**
		 * The following 3 fields are ignored.
		 * 
		 * @deprecated 	1.8.11
		 */
		$dayOfTheWeek = VikRequest::getInt('weekday', 0);
		$getReports = VikRequest::getInt('reportsOn', 0);
		$getMainEmail = VikRequest::getString('mainEmail', '');

		// regular fields
		$getVBBookings = VikRequest::getInt('vbBookings');
		$getUsersAcl = VikRequest::getVar('usersAcl', array());
		$getUsersEmails = VikRequest::getVar('usersEmails', array());

		$module = VikChannelManager::getActiveModule(true);
		$module['params'] = json_decode($module['params'], true);
		$ch_name = $module['name'];
		$ch_key = $module['uniquekey'];

		$xmlArray = [
			'reportswday' => $dayOfTheWeek,
			'reportson'   => $getReports,
			'vbonotifs'   => $getVBBookings,
			'main_email'  => $getMainEmail,
		];

		$e4jc_url = "https://e4jconnect.com/channelmanager/?r=pwd&c=generic";
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager PWD Request e4jConnect.com - VikBooking -->
<PasswordRQ xmlns="http://www.e4jconnect.com/schemas/pwdrq">
	<Notify client="'.JUri::root().'"/>
	<Params>';
	foreach ($module['params'] as $k => $v) {
		if (defined('ENT_XML1')) {
			// only available from PHP 5.4 and on
			$setting_ent = htmlspecialchars($v, ENT_XML1 | ENT_COMPAT, 'UTF-8');
		} else {
			// fallback to plain all html entities
			$setting_ent = htmlentities($v);
		}
		$xml .= '
		<Param name="' . $k . '" value="' . $setting_ent . '"/>';
	}
	$xml .= '
	</Params>
	<Settings>';
		foreach ($xmlArray as $k => $v) {
			if (defined('ENT_XML1')) {
				// only available from PHP 5.4 and on
				$setting_ent = htmlspecialchars($v, ENT_XML1 | ENT_COMPAT, 'UTF-8');
			} else {
				// fallback to plain all html entities
				$setting_ent = htmlentities($v);
			}
			$xml .= '
		<Setting name="' . $k . '" value="' . $setting_ent . '"/>';
		}
		$xml .= '
	</Settings>
	<Api key="'.$api_key.'"/>
	<Fetch channel="'.$ch_name.'" ukey="'.$ch_key.'"/>
</PasswordRQ>';
		foreach ($getUsersEmails as $key => $value) {
			$newAcl[$value] = $getUsersAcl[$key];
		}

		$newAcl = json_encode($newAcl);

		$q = "UPDATE `#__vikchannelmanager_config`
			SET `setting` = ".$dbo->quote($newAcl)."
			WHERE `param` = 'app_acl';";
		$dbo->setQuery($q);
		$dbo->execute();
		$values = array(
			'reports' => array(
				'on' => $getReports,
				'weekday' => $dayOfTheWeek
			),
			'vbBookings' => array(
				'on' => $getVBBookings
			)
		);
		$values = json_encode($values);
		$q = "SELECT `setting` 
			FROM `#__vikchannelmanager_config` 
			WHERE `param` = 'app_settings';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$oldData = $dbo->loadAssoc();
			if (!strcmp($oldData['setting'], $values)) {
				$e4jC = new E4jConnectRequest($e4jc_url);
				$e4jC->setPostFields($xml);
				$rs = $e4jC->exec();
				if ($e4jC->getErrorNo()) {
					VikError::raiseWarning('', VikChannelManager::getErrorFromMap($e4jC->getErrorMsg()));
					$mainframe->redirect('index.php?option=com_vikchannelmanager&task=appconfig');
					die;
				}
				if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
					VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
					$mainframe->redirect('index.php?option=com_vikchannelmanager&task=appconfig');
					die;
				}
			}
			$q = "UPDATE `#__vikchannelmanager_config` 
				SET `setting` = ".$dbo->quote($values)." 
				WHERE `param` = 'app_settings';";
			$dbo->setQuery($q);
			$dbo->execute();
		} else {
			$e4jC = new E4jConnectRequest($e4jc_url);
			$e4jC->setPostFields($xml);
			$rs = $e4jC->exec();
			if ($e4jC->getErrorNo()) {
				VikError::raiseWarning('', VikChannelManager::getErrorFromMap($e4jC->getErrorMsg()));
				$mainframe->redirect('index.php?option=com_vikchannelmanager&task=appconfig');
				die;
			}
			if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
				VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
				$mainframe->redirect('index.php?option=com_vikchannelmanager&task=appconfig');
				die;
			}
			$q = "INSERT INTO `#__vikchannelmanager_config` (`param`, `setting`)
				VALUES ('app_settings',".$dbo->quote($values).");";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe->enqueueMessage(JText::_('VCMAPPSETTINGSUCCESSUPDATE'));
		$mainframe->redirect('index.php?option=com_vikchannelmanager&task=appconfig');
	}

	public function brtwo()
	{
		$dbo = JFactory::getDbo();
		$mainframe = JFactory::getApplication();

		$api_key = VikChannelManager::getApiKey(true);
		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);

		$notid = VikRequest::getInt('notid', 0, 'request');
		$attempts = VikRequest::getInt('attempts', 0, 'request');
		$otabid = VikRequest::getString('otabid', '', 'request');
		$firstroom = VikRequest::getString('firstroom', '', 'request');
		$chname = VikRequest::getString('channel', $channel['name'], 'request');

		if (empty($otabid) || empty($chname)) {
			// missing data
			VikError::raiseWarning('', 'Missing Data.');
			$mainframe->redirect('index.php?option=com_vikchannelmanager');
			exit;
		}

		$notification = array();
		$q = "SELECT * FROM `#__vikchannelmanager_notifications` WHERE `id`=".$notid." LIMIT 1;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$notification = $dbo->loadAssoc();
		}

		$hotelid = '0';
		if (!empty($firstroom)) {
			$q = "SELECT * FROM `#__vikchannelmanager_roomsxref` WHERE `idroomota`=".$dbo->quote($firstroom).";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$rel = $dbo->loadAssoc();
				if (!empty($rel['prop_params'])) {
					$hparams = json_decode($rel['prop_params'], true);
					if (is_array($hparams) && isset($hparams['hotelid'])) {
						$hotelid = $hparams['hotelid'];
					} elseif (is_array($hparams)) {
						/**
						 * Some channels may use different params, and so we use the first one.
						 * For example, for Airbnb API this will be the "user_id".
						 * 
						 * @since 	1.8.0
						 */
						foreach ($hparams as $pamval) {
							$hotelid = $pamval;
							break;
						}
					}
				}
			}
		}
		
		$e4jc_url = "https://e4jconnect.com/channelmanager/?r=brtwo&c=".$chname;
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager BRTWO Request e4jConnect.com - VikBooking -->
<BookingRetrievalTwoRQ xmlns="http://www.e4jconnect.com/schemas/brtworq">
	<Notify client="'.JUri::root().'"/>
	<Api key="'.$api_key.'"/>
	<Fetch bid="'.$otabid.'" rooms="'.$firstroom.'" hotel_id="'.$hotelid.'"/>
</BookingRetrievalTwoRQ>';
		
		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		$e4jC->slaveEnabled = true;
		$rs = $e4jC->exec();
		
		if ($e4jC->getErrorNo()) {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($e4jC->getErrorMsg()));
			$mainframe->redirect('index.php?option=com_vikchannelmanager');
			exit;
		}
		if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
			$mainframe->redirect('index.php?option=com_vikchannelmanager');
			exit;
		}

		$attempts++;
		if (count($notification)) {
			if (strpos($notification['cont'], 'retransmit-attempts:') !== false) {
				$trparts = explode('retransmit-attempts:', $notification['cont']);
				$notification['cont'] = str_replace('retransmit-attempts:'.substr($trparts[1], 0, 1).';', 'retransmit-attempts:'.$attempts.';', $notification['cont']);
			} else {
				$notification['cont'] .= "\n".'retransmit-attempts:'.$attempts.';';
			}
			$q = "UPDATE `#__vikchannelmanager_notifications` SET `cont`=".$dbo->quote($notification['cont'])." WHERE `id`=".$notification['id'].";";
			$dbo->setQuery($q);
			$dbo->execute();
		}

		// display message to wait for the server
		$mainframe->enqueueMessage(JText::_('VCMBRTWOWAITOK'));
		
		$mainframe->redirect('index.php?option=com_vikchannelmanager');
		exit;
	}

	/**
	 * Manual bookings pull from the currently active iCal channel.
	 * Limited to 3 times per day.
	 * 
	 * @since 	1.6.18 (November 2019)
	 */
	public function ical_manual_pull()
	{
		$dbo 		= JFactory::getDbo();
		$mainframe 	= JFactory::getApplication();
		$module 	= VikChannelManager::getActiveModule(true);
		$config 	= VCMFactory::getConfig();
		$api_key 	= VikChannelManager::getApiKey(true);

		$canpull = (!empty($module['params']) && (int)$module['av_enabled'] != 1);
		$last_pull = $config->get('last_ical_pull_' . $module['uniquekey'], null);
		if (isset($last_pull)) {
			$last_pull = json_decode($last_pull);
			if (date('Y-m-d', $last_pull->ts) == date('Y-m-d') && $last_pull->retry > 2) {
				// we allow to manually pull the iCal bookings up to 3 times per day
				$canpull = false;
				/**
				 * It is possible to force the pulling by manually injecting a var in query string.
				 * 
				 * @since 	1.7.2
				 */
				if (VikRequest::getInt('force', 0, 'request')) {
					$canpull = (!empty($module['params']) && (int)$module['av_enabled'] != 1);
				}
			}
		}

		if (!$canpull) {
			VikError::raiseWarning('', 'Manual download at this time is forbidden.');
			$mainframe->redirect('index.php?option=com_vikchannelmanager&task=listings');
			exit;
		}

		// update retries attempts object first
		if (!is_object($last_pull)) {
			$last_pull = new stdClass;
		}
		$last_pull->ts = time();
		$last_pull->retry = isset($last_pull->retry) ? ((int)$last_pull->retry + 1) : 1;
		// store data
		$config->set('last_ical_pull_' . $module['uniquekey'], json_encode($last_pull));

		// make the request
		$e4jc_url = "https://slave.e4jconnect.com/channelmanager/?r=fsum&c=generic";
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager FSUM Request e4jConnect.com - VikBooking -->
<FirstSummaryRQ xmlns="http://www.e4jconnect.com/schemas/fsumrq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . $api_key . '"/>
	<Fetch ukey="' . $module['uniquekey'] . '"/>
</FirstSummaryRQ>';
		
		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		$e4jC->slaveEnabled = true;
		$rs = $e4jC->exec();
		if ($e4jC->getErrorNo()) {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($e4jC->getErrorMsg()));
		} elseif (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
		} elseif (substr($rs, 0, 6) == 'e4j.ok') {
			$mainframe->enqueueMessage(JText::_("VCMFIRSTBSUMMREQSENT"));
			$mainframe->enqueueMessage(JText::_("VCMICALMANUALPULLINFO"), 'warning');
		} else {
			VikError::raiseWarning('', 'Empty Response');
		}

		// redirect
		$mainframe->redirect("index.php?option=com_vikchannelmanager&task=listings");
		exit;
	}

	public function config_import() {
		$dbo 		= JFactory::getDbo();
		$session 	= JFactory::getSession();
		$mainframe  = JFactory::getApplication();
		
		$module = VikChannelManager::getActiveModule(true);
		$module['params'] = json_decode($module['params'], true);
		$pimp = VikRequest::getInt('imp', 0);

		$req_channel = VikChannelManagerConfig::BOOKING;
		if ($module['av_enabled'] == 1) {
			$req_channel = $module['uniquekey'];
		}

		$hotelid = '';
		foreach ($module['params'] as $param_name => $param_value) {
			// grab the first channel parameter
			$hotelid = $param_value;
			break;
		}
		if (empty($hotelid)) {
			VikError::raiseWarning('', 'Please make sure to enter your channel account details before proceeding.');
			// redirect and exit
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=config");
			exit;
		}

		if ($pimp < 1) {
			// update the configuration to not ask it again
			VikChannelManager::checkImportChannelConfig($req_channel, 0);
			// redirect and exit
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=config");
			exit;
		}

		// make sure we do not have too many invalid import attempts
		$invalid_attempts = (int)$session->get('vcmConfigImport' . $req_channel, 0);
		if ($invalid_attempts >= 2) {
			// prevent too many errors from occurring
			VikError::raiseWarning('', 'Please contact us to proceed. Too many erroneous attempts occurred, maybe because you have not yet enabled the connection between your Account ID on the channel and this Channel Manager.');
			// redirect and exit
			$mainframe->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}

		// send the request to e4jConnect
		$api_key = VikChannelManager::getApiKey(true);
		$e4jc_url = "https://e4jconnect.com/channelmanager/?r=cfgimp&c={$module['name']}";
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager CFGIMP Request e4jConnect.com - Vik Channel Manager -->
<ConfigImportRQ xmlns="http://www.e4jconnect.com/schemas/cfgimprq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . $api_key . '"/>
	<Fetch ukey="' . $req_channel . '" hotelid="' . $hotelid . '"/>
</ConfigImportRQ>';
		
		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		$e4jC->slaveEnabled = true;
		$rs = $e4jC->exec();
		
		if ($e4jC->getErrorNo()) {
			// increase errors counter
			$session->set('vcmConfigImport' . $req_channel, ($invalid_attempts + 1));
			// do not continue
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($e4jC->getErrorMsg()));
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=config");
			exit;
		}

		if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			// increase errors counter
			$session->set('vcmConfigImport' . $req_channel, ($invalid_attempts + 1));
			// do not continue
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=config");
			exit;
		}

		// try to decode the response
		$data = json_decode($rs, true);
		if (!$data || !is_array($data)) {
			// increase errors counter
			$session->set('vcmConfigImport' . $req_channel, ($invalid_attempts + 1));
			// do not continue
			VikError::raiseWarning('', 'Invalid response, unable to import data.<br/>' . $rs);
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=config");
			exit;
		}

		// store the response on the database
		$cfg_param_name = "cfgimp_{$req_channel}_{$hotelid}";
		$q = "SELECT * FROM `#__vikchannelmanager_config` WHERE `param`=" . $dbo->quote($cfg_param_name) . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			$q = "UPDATE `#__vikchannelmanager_config` SET `setting`=" . $dbo->quote($rs) . " WHERE `param`=" . $dbo->quote($cfg_param_name) . ";";
		} else {
			$q = "INSERT INTO `#__vikchannelmanager_config` (`param`, `setting`) VALUES (" . $dbo->quote($cfg_param_name) . ", " . $dbo->quote($rs) . ");";
		}
		$dbo->setQuery($q);
		$dbo->execute();

		// redirect to the apposte view to start the wizard
		$mainframe->redirect("index.php?option=com_vikchannelmanager&task=cfgimpwizard");
		exit;
	}

	public function cfgimpwizard() {
		VCM::printMenu();
	
		VikRequest::setVar('view', VikRequest::getCmd('view', 'cfgimpwizard'));
	
		parent::display();
		
		VCM::printFooter();
	}

	/**
	 * AJAX request for importing the various configuration aspects from a channel.
	 * 
	 * @since 	1.7.2
	 */
	public function config_import_exec_step() {
		$dbo 		= JFactory::getDbo();
		$step 		= VikRequest::getInt('step', 0, 'request');
		$rooms 		= VikRequest::getVar('rooms', array());
		$rplans 	= VikRequest::getVar('rplans', array());
		$costs 		= VikRequest::getVar('costs', array());
		$downphotos = VikRequest::getInt('downphotos', 0, 'request');

		// we need the main VBO library for using some methods
		if (!class_exists('VikBooking') && file_exists(VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'lib.vikbooking.php')) {
			require_once (VBO_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "lib.vikbooking.php");
		}

		$module = VikChannelManager::getActiveModule(true);
		$module['params'] = json_decode($module['params'], true);
		$module['params'] = !is_array($module['params']) ? array() : $module['params'];

		$req_channel = VikChannelManagerConfig::BOOKING;
		if ($module['av_enabled'] == 1) {
			$req_channel = $module['uniquekey'];
		}

		$hotelid = '';
		foreach ($module['params'] as $param_name => $param_value) {
			// grab the first channel parameter
			$hotelid = $param_value;
			break;
		}
		if (empty($hotelid)) {
			throw new Exception("Make sure to enter your channel account details before proceeding.", 404);
		}
		if (!count($rooms)) {
			throw new Exception("Make sure to select at least one room type for the import.", 500);
		}

		// load configuration data
		$cfg_param_name = "cfgimp_{$req_channel}_{$hotelid}";
		$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param`=" . $dbo->quote($cfg_param_name);
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			throw new Exception("Data not found for the import.", 404);
		}
		$config_data = json_decode($dbo->loadResult(), true);
		if (!$config_data || !is_array($config_data)) {
			throw new Exception("Invalid or broken configuration data.", 500);
		}

		if (!isset($config_data['Rooms']) || !count($config_data['Rooms'])) {
			throw new Exception("No rooms found in the configuration data.", 404);
		}

		// current rooms mapping for this account (for later steps than 1st)
		$current_mapping = array();
		$q = "SELECT * FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel`=" . $dbo->quote($req_channel) . " AND `prop_params` LIKE " . $dbo->quote("%{$hotelid}%") . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			$records = $dbo->loadAssocList();
			foreach ($records as $xref) {
				$current_mapping[$xref['idroomota']] = $xref['idroomvb'];
			}
		}

		// upload path for the rooms photos in VBO
		$vbo_rmedia_path = VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;

		$response = new stdClass;
		$response->status = 0;
		$response->error = 'A generic error occurred';
		$response->msg = '';

		// start importing data depending on the current step
		if ($step === 0) {
			// first step, import the rooms onto Vik Booking and create the mapping on VCM
			$added_rooms = 0;
			foreach ($config_data['Rooms'] as $otarid => $otaroom) {
				if (!in_array($otarid, $rooms)) {
					// this room was not selected for import, skip it
					continue;
				}

				if (isset($current_mapping[$otarid])) {
					// this OTA room ID has already been mapped to a VBO room, skip it
					continue;
				}

				// download room main photo
				$room_img = null;
				if (isset($config_data['Photos'][$otarid]) && !empty($config_data['Photos'][$otarid][0]['url'])) {
					$room_img = VikChannelManager::downloadRemoteFile($config_data['Photos'][$otarid][0]['url'], $vbo_rmedia_path);
					$room_img = !empty($room_img) ? $room_img : null;
				}

				// build room object for creation
				$vbo_room = new stdClass;
				$vbo_room->name = $otaroom['name'];
				$vbo_room->img = $room_img;
				$vbo_room->avail = 1;
				$vbo_room->units = $otaroom['units'];
				$vbo_room->fromadult = $otaroom['min_adults'];
				$vbo_room->toadult = $otaroom['max_adults'];
				$vbo_room->fromchild = $otaroom['min_children'];
				$vbo_room->tochild = $otaroom['max_children'];
				$vbo_room->totpeople = ($vbo_room->toadult + $vbo_room->tochild);
				$vbo_room->mintotpeople = ($vbo_room->fromadult + $vbo_room->fromchild);
				$vbo_room->alias = JFilterOutput::stringURLSafe($vbo_room->name);

				// create room in VBO
				try {
					if (!$dbo->insertObject('#__vikbooking_rooms', $vbo_room, 'id')) {
						throw new Exception("Could not create room {$otaroom['name']}", 500);
					}
				} catch (Exception $e) {
					throw new Exception("Could not create room {$otaroom['name']}", 500);
				}

				if (empty($vbo_room->id)) {
					continue;
				}

				// newly created room ID in VBO
				$vbo_room_id = (int)$vbo_room->id;
				$added_rooms++;

				// build room-rateplans mapping object for VCM
				$otarpricing = new stdClass;
				$otarpricing->RatePlan = array();
				if (isset($otaroom['rplans']) && count($otaroom['rplans'])) {
					foreach ($otaroom['rplans'] as $rplanid) {
						if (!isset($config_data['Rplans'][$rplanid])) {
							continue;
						}
						// push rate plan info for mapping
						$otarpricing->RatePlan[$rplanid] = $config_data['Rplans'][$rplanid];
						if (!isset($otarpricing->RatePlan[$rplanid]['max_persons'])) {
							// for compatibility with the standard rooms mapping, we include also this information
							$otarpricing->RatePlan[$rplanid]['max_persons'] = $otaroom['max_adults'];
						}
					}
				}
				
				// build mapping record in VCM
				$vcm_room_rel = new stdClass;
				$vcm_room_rel->idroomvb = $vbo_room_id;
				$vcm_room_rel->idroomota = $otarid;
				$vcm_room_rel->idchannel = $req_channel;
				$vcm_room_rel->channel = $module['name'];
				$vcm_room_rel->otaroomname = $otaroom['name'];
				$vcm_room_rel->otapricing = json_encode($otarpricing);
				$vcm_room_rel->prop_name = !empty($config_data['Hotel']['name']) ? $config_data['Hotel']['name'] : $config_data['Hotel']['id'];
				$vcm_room_rel->prop_params = json_encode($module['params']);

				// store mapping record
				try {
					if (!$dbo->insertObject('#__vikchannelmanager_roomsxref', $vcm_room_rel, 'id')) {
						throw new Exception("Could not create relation for room {$otaroom['name']}", 500);
					}
					// update mapping information just for this step-loop and to avoid double mapping
					$current_mapping[$otarid] = $vbo_room_id;
				} catch (Exception $e) {
					throw new Exception("Could not create relation for room {$otaroom['name']}", 500);
				}
			}

			if ($added_rooms > 0) {
				// process completed successfully
				$response->status = 1;
				$response->mess = JText::sprintf('VCMCFGIMPWIZDTOTROOMSIMP', $added_rooms);
			} else {
				$response->err = 'No rooms were actually created';
			}
		} elseif ($step === 1) {
			// second step, import the rate plans onto Vik Booking
			$tot_rplans_imp = 0;
			if (is_array($rplans) && count($rplans)) {
				// it is not mandatory to create some rate plans
				foreach ($config_data['Rplans'] as $otarpid => $otarplan) {
					if (!in_array($otarpid, $rplans)) {
						// this rate plan was not selected for import, skip it
						continue;
					}

					// find the key for the corresponding cost submitted
					$rplan_key = array_search($otarpid, $rplans);

					// check whether it is refundable from name/policy
					$refundable = true;
					if (stripos($otarplan['name'], 'refundable') !== false) {
						if (stripos($otarplan['name'], 'not') !== false || stripos($otarplan['name'], 'non') !== false) {
							// non refundable rate
							$refundable = false;
						}
					} elseif (stripos($otarplan['policy'], 'refundable') !== false) {
						if (stripos($otarplan['policy'], 'not') !== false || stripos($otarplan['policy'], 'non') !== false) {
							// non refundable rate
							$refundable = false;
						}
					}

					// build rate plan object for creation
					$vbo_rplan = new stdClass;
					$vbo_rplan->name = ucwords($otarplan['name']);
					$vbo_rplan->free_cancellation = (int)$refundable;
					$vbo_rplan->canc_deadline = $refundable ? 7 : 0;
					$vbo_rplan->canc_policy = $otarplan['policy'];

					// store rate plan record
					try {
						if (!$dbo->insertObject('#__vikbooking_prices', $vbo_rplan, 'id')) {
							throw new Exception("Could not create rate plan {$otarplan['name']}", 500);
						}
					} catch (Exception $e) {
						throw new Exception("Could not create rate plan {$otarplan['name']}", 500);
					}

					if (empty($vbo_rplan->id)) {
						continue;
					}

					// newly created rate plan ID in VBO
					$vbo_rplan_id = (int)$vbo_rplan->id;
					$tot_rplans_imp++;

					// check the default costs per night
					$rplan_cost = (float)$otarplan['cost'];
					if (isset($costs[$rplan_key]) && floatval($costs[$rplan_key]) > 0) {
						$rplan_cost = (float)$costs[$rplan_key];
					}

					// insert base costs per night
					if ($rplan_cost > 0) {
						// populate rates table for this rate plan and all associated rooms
						foreach ($config_data['Rooms'] as $otarid => $otaroom) {
							if (!in_array($otarpid, $otaroom['rplans']) || !isset($current_mapping[$otarid])) {
								// current rate plan not for this room, or room not mapped
								continue;
							}
							// this room is related to this rate plan
							$xref_rid = $current_mapping[$otarid];

							// execute queries up to 30 nights of stay for the cost
							for ($nights = 1; $nights <= 30; $nights++) {
								$dispcost = new stdClass;
								$dispcost->idroom = (int)$xref_rid;
								$dispcost->days = $nights;
								$dispcost->idprice = $vbo_rplan_id;
								$dispcost->cost = $rplan_cost * $nights;
								// store record
								try {
									$dbo->insertObject('#__vikbooking_dispcost', $dispcost, 'id');
								} catch (Exception $e) {
									// do nothing
								}
							}
						}
					}
					//
				}
			}
			// process completed successfully
			$response->status = 1;
			$response->mess = JText::sprintf('VCMCFGIMPWIZDTOTRPLANSIMP', $tot_rplans_imp);
		} elseif ($step === 2) {
			// third step: download extra photos
			$tot_photos_dld = 0;
			if ($downphotos > 0 && isset($config_data['Photos']) && count($config_data['Photos']) > 1) {
				// photos present for at least one room type
				foreach ($config_data['Photos'] as $prop => $prop_photos) {
					if ($prop == 'property' || count($prop_photos) < 2) {
						// property gallery or just one photo, which has already been downloaded
						continue;
					}
					if (!isset($current_mapping[$prop])) {
						// this room was not imported, skip it
						continue;
					}
					// download photos for this room
					$downloaded_photos = array();
					foreach ($prop_photos as $photo) {
						$room_img = VikChannelManager::downloadRemoteFile($photo['url'], $vbo_rmedia_path);
						if (!$room_img) {
							continue;
						}
						$tot_photos_dld++;
						array_push($downloaded_photos, $room_img);

						/**
						 * Create the thumbnail and the big version of the room extra image for vbo
						 * by using the method that triggers the files mirroring.
						 */
						VikBooking::uploadFile($vbo_rmedia_path . $room_img, $vbo_rmedia_path . 'thumb_' . $room_img, true);
						VikBooking::uploadFile($vbo_rmedia_path . $room_img, $vbo_rmedia_path . 'big_' . $room_img, true);
						//
					}
					if (count($downloaded_photos)) {
						// update room object in VBO
						$new_data = new stdClass;
						$new_data->id = $current_mapping[$prop];
						$new_data->moreimgs = implode(';;', $downloaded_photos);

						// update record
						try {
							$dbo->updateObject('#__vikbooking_rooms', $new_data, 'id');
						} catch (Exception $e) {
							// do nothing
						}
					}
				}
			}
			// process completed successfully
			$response->status = 1;
			$response->mess = JText::sprintf('VCMCFGIMPWIZDTOTPHOTOSDLD', $tot_photos_dld);
		} elseif ($step === 3) {
			// fourth step: import active bookings now that the rooms have been mapped
			
			$api_key = VikChannelManager::getApiKey(true);
			$e4jc_url = "https://e4jconnect.com/channelmanager/?r=fsum&c=generic";
			$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager FSUM Request e4jConnect.com - VikBooking -->
<FirstSummaryRQ xmlns="http://www.e4jconnect.com/schemas/fsumrq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . $api_key . '"/>
	<Fetch ukey="' . $req_channel . '"/>
</FirstSummaryRQ>';

			$e4jC = new E4jConnectRequest($e4jc_url);
			$e4jC->setPostFields($xml);
			$e4jC->slaveEnabled = true;
			$rs = $e4jC->exec();
			if ($e4jC->getErrorNo()) {
				throw new Exception(VikChannelManager::getErrorFromMap($e4jC->getErrorMsg()), 500);
			} elseif (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
				throw new Exception(VikChannelManager::getErrorFromMap($rs), 500);
			} elseif (substr($rs, 0, 6) == 'e4j.ok') {
				// everything went well!
				$response->mess = ':)';
			} else {
				throw new Exception('Empty Response', 500);
			}

			// process completed successfully, status = 2 for redirection
			$response->status = 2;

			// update the configuration to not ask again to import the configuration
			VikChannelManager::checkImportChannelConfig($req_channel, 0);

			// update the configuration to not ask to import the active bookings
			VikChannelManager::checkFirstBookingSummary($req_channel, 0);
		} else {
			throw new Exception("Invalid configuration step.", 500);
		}

		// output the response for the AJAX request
		echo json_encode($response);
		exit;
	}

	/**
	 * AJAX request for updating the Booking.com, Airbnb API and other OTAs property score.
	 * 
	 * @since 	1.7.2
	 * @since 	1.8.0 	the support for the Airbnb API User (Host) Statistics was added.
	 * @since 	1.8.4 	the support for Google Hotel Travel Partner API was added.
	 * @since 	1.9.14 	introduced support for "hosting quality", especially for Airbnb API.
	 */
	public function get_property_score()
	{
		$dbo       = JFactory::getDbo();
		$session   = JFactory::getSession();
		$hotelid   = VikRequest::getString('hotelid', '', 'request');
		$forcedown = VikRequest::getInt('force', 0, 'request');

		// supported channels
		$eligible_channels = [
			VikChannelManagerConfig::BOOKING,
			VikChannelManagerConfig::AIRBNBAPI,
			VikChannelManagerConfig::GOOGLEHOTEL,
		];

		// get the channel unique key from the request
		$req_channel = VikRequest::getInt('uniquekey', 0, 'request');

		$module = null;
		if (empty($req_channel)) {
			$module = VikChannelManager::getActiveModule(true);
			$req_channel = $module['uniquekey'];
		}

		if (!in_array($req_channel, $eligible_channels)) {
			VCMHttpDocument::getInstance()->close(500, "Unsupported channel");
		}

		if (!$module) {
			// load channel from the request
			$module = VikChannelManager::getChannel($req_channel);
		}

		if (!is_array($module) || !count($module)) {
			VCMHttpDocument::getInstance()->close(404, "Channel not found");
		}

		// check errors counter
		$errors_count = (int)$session->get("err_scorecard_{$req_channel}", 0, 'vcm-scorecard');
		if ($errors_count > 1 && !$forcedown) {
			VCMHttpDocument::getInstance()->close(500, "Too many API errors for this session");
		}

		if (empty($hotelid) && $module['uniquekey'] == $req_channel) {
			$module['params'] = json_decode($module['params'], true);
			$module['params'] = !is_array($module['params']) ? array() : $module['params'];
			foreach ($module['params'] as $param_name => $param_value) {
				// grab the first channel parameter
				$hotelid = $param_value;
				break;
			}
		}

		if (empty($hotelid)) {
			VCMHttpDocument::getInstance()->close(500, "Empty Account ID");
		}

		// make sure some rooms have been mapped so that we know the channel connection is working with this Account ID
		$q = "SELECT * FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel`=" . $dbo->quote($req_channel) . " AND `prop_params` LIKE " . $dbo->quote("%{$hotelid}%") . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			VCMHttpDocument::getInstance()->close(404, "No rooms mapped for this channel or account.");
		}

		// configuration object
		$config = VCMFactory::getConfig();

		// download new score
		$download = false;

		/**
		 * The newly submitted credentials may not be yet available on the Slave,
		 * and so in order to avoid a generic "Authentication Error" on the Slave,
		 * we force the call on the Master in case this is the first time for this call.
		 * 
		 * @since 	1.7.5
		 */
		$usemaster = false;

		// define the main configuration parameter name
		$cfg_param_name = "propscore_{$req_channel}_{$hotelid}";

		// define the hosting quality param name
		$hq_param_name = str_replace('propscore', 'hosting_quality', $cfg_param_name);

		// load current configuration data
		$config_data = $config->get($cfg_param_name, null);

		if (!$config_data) {
			$download  = true;
			$usemaster = true;
		} else {
			$config_data = json_decode($config_data);
			if (!$config_data || !is_object($config_data)) {
				VCMHttpDocument::getInstance()->close(500, "Invalid or broken configuration data.");
			}

			// check whether we should re-download the updated information
			$download = (date('Y-m-d') != ($config_data->last_ymd ?? ''));

			// check if additional contents should be returned
			if ($module['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI) {
				// in case of Airbnb we try to get the "hosting quality" data
				$hosting_quality_data = (array) $config->getArray($hq_param_name, []);
				if ($hosting_quality_data) {
					// set current hosting quality data for the scorecard
					$config_data->data->hosting_quality = VCMAirbnbContent::normalizeHostingQualityData($hosting_quality_data, [
						'host_id' => $hotelid,
						'purpose' => 'scorecard',
					]);
				}
			}
		}

		if ($forcedown > 0) {
			// download request was forced
			$download = true;
		}

		if ($download) {
			// download property score from e4jConnect (slave by default)
			$base_url = $usemaster ? 'https://e4jconnect.com/' : 'https://slave.e4jconnect.com/';
			$e4jc_url = $base_url . "channelmanager/?r=pscore&c=" . $module['name'];

			// determine data type
			$dataType = 'full';
			if ($module['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI) {
				$dataType .= '-hosting_quality';
			}

			$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager PSCORE Request e4jConnect.com - Vik Channel Manager -->
<PropertyScoreRQ xmlns="http://www.e4jconnect.com/schemas/pscorerq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . VikChannelManager::getApiKey(true) . '"/>
	<Fetch data="' . $dataType . '" hotelid="' . $hotelid . '"/>
</PropertyScoreRQ>';

			$e4jC = new E4jConnectRequest($e4jc_url);
			$e4jC->setPostFields($xml);
			$e4jC->slaveEnabled = true;
			$rs = $e4jC->exec();
			if ($e4jC->getErrorNo()) {
				$session->set("err_scorecard_{$req_channel}", ($errors_count + 1), 'vcm-scorecard');
				VCMHttpDocument::getInstance()->close(500, VikChannelManager::getErrorFromMap($e4jC->getErrorMsg()));
			} elseif (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
				$session->set("err_scorecard_{$req_channel}", ($errors_count + 1), 'vcm-scorecard');
				VCMHttpDocument::getInstance()->close(500, VikChannelManager::getErrorFromMap($rs));
			} else {
				$config_data = new stdClass;
				$config_data->last_ymd = date('Y-m-d');
				$config_data->data = json_decode($rs);
				if (!is_object($config_data->data)) {
					VCMHttpDocument::getInstance()->close(500, 'Could not decode property score response');
				}

				/**
				 * If the response contains the "hosting_quality" property, this will be saved separately.
				 * 
				 * @since 	1.9.14
				 */
				if ($config_data->data->hosting_quality ?? null) {
					// save this property separately
					$hosting_quality_data = $config_data->data->hosting_quality;
					$config->set($hq_param_name, $hosting_quality_data);

					// unset this special property that was saved separately
					unset($config_data->data->hosting_quality);
				}

				// update response on DB
				$config->set($cfg_param_name, $config_data);
			}
		}

		// final check for the response integrity
		if (!is_object($config_data)) {
			VCMHttpDocument::getInstance()->close(500, 'Cannot decode response');
		}

		if (isset($hosting_quality_data) && $hosting_quality_data) {
			// restore the property that was just saved so that it will be returned to VCM
			$config_data->data->hosting_quality = $hosting_quality_data;
		}

		// update session value
		$session->set("scorecard_{$req_channel}_{$hotelid}", $config_data, 'vcm-scorecard');

		if ($module['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI && isset($hosting_quality_data) && ($config_data->data->hosting_quality ?? null)) {
			// make sure to normalize the hosting quality data for display after it was downloaded
			$config_data->data->hosting_quality = VCMAirbnbContent::normalizeHostingQualityData($config_data->data->hosting_quality, [
				'host_id' => $hotelid,
				'purpose' => 'scorecard',
			]);
		}

		// return the current or the new property score data
		VCMHttpDocument::getInstance()->json($config_data);
	}

	/**
	 * This is a private method called by the task that saves the hotel details.
	 * We need to check if the current submission is suited for the Booking.com
	 * Vacation Rentals Essentials APIs, more precisely: Key Collection and Property Profile.
	 * 
	 * @param 	mixed 	$extra 	un-used argument useful for passing some data from another method.
	 * 
	 * @return 	boolean 		true on success, false otherwise by triggering system messages when necessary.
	 * 
	 * @see 	https://connect.booking.com/user_guide/site/en-US/checkin-methods-api/methods-descriptions/
	 * 
	 * @since 	1.7.2
	 */
	private function triggerBcomVressPropDetails($extra = null)
	{
		$session = JFactory::getSession();
		$api_key = VikChannelManager::getApiKey(true);

		$module = VikChannelManager::getActiveModule(true);
		$module['params'] = json_decode($module['params'], true);
		$module['params'] = !is_array($module['params']) ? array() : $module['params'];
		$vress_enabled = 0;
		$hotelid = '';
		$ch_allowed = ($module['uniquekey'] == VikChannelManagerConfig::BOOKING);
		$channels_mapping = $ch_allowed ? VikChannelManager::getChannelAccountsMapped(VikChannelManagerConfig::BOOKING) : array();
		// current (reading) values in session
		$vress_data = $session->get('vress_data', '', 'vcm-vress');
		//
		if ($ch_allowed && count($channels_mapping)) {
			// some rooms have been mapped already, and Booking.com is the active channel
			foreach ($module['params'] as $param_name => $param_value) {
				// grab the first channel parameter
				$hotelid = $param_value;
				break;
			}
			if (!empty($hotelid)) {
				// turn on the flag for displaying the Vacation Rentals APIs (just some of them will be here in this View)
				$vress_enabled = 1;
			}
		}

		if (!$vress_enabled) {
			// do not set any errors in this case because this channel simply does not support the VR Essentials APIs
			return false;
		}

		if (!is_object($vress_data) || !isset($vress_data->read) || !is_object($vress_data->read) || !count(get_object_vars($vress_data->read))) {
			// unable to proceed, we need to know what data to expect by reading it first
			VikError::raiseWarning('', 'Empty data in session for Key Collection and Property Profile');
			return false;
		}

		// compose XML writing nodes
		$xml_writing_nodes = array();
		$vress_errors_count = (int)$session->get('vress_write_err', 0, 'vcm-vress');

		if ($vress_errors_count >= 3) {
			// too many errors occurred, prevent too many API errors from happening
			VikError::raiseWarning('', 'Too many errors occurred. Please fix the configuration settings before retrying, or maybe your account is not enabled for transmitting this kind of information.');
			return false;
		}

		// Key Collection API
		if (isset($vress_data->read->KeyCollection) && isset($vress_data->read->KeyCollection->StreamVariations) && isset($vress_data->read->KeyCollection->CheckinMethods) 
			&& is_array($vress_data->read->KeyCollection->StreamVariations) && count($vress_data->read->KeyCollection->StreamVariations) 
			&& is_array($vress_data->read->KeyCollection->CheckinMethods) && count($vress_data->read->KeyCollection->CheckinMethods) 
		) {
			// we can proceed with the Key Collection API
			$checkin_mets = array();
			// request variables passed as arrays
			$other_text = VikRequest::getVar('vress_other_text', array(), 'request', 'array');
			$off_location = VikRequest::getVar('vress_off_location', array(), 'request', 'array');
			$address = VikRequest::getVar('vress_address', array(), 'request', 'array');
			$city = VikRequest::getVar('vress_city', array(), 'request', 'array');
			$zip = VikRequest::getVar('vress_zip', array(), 'request', 'array');
			$brand_name = VikRequest::getVar('vress_brand_name', array(), 'request', 'array');
			$how = VikRequest::getVar('vress_how', array(), 'request', 'array');
			$when = VikRequest::getVar('vress_when', array(), 'request', 'array');
			$other = VikRequest::getVar('vress_other', array(), 'request', 'array');
			$identifier = VikRequest::getVar('vress_identifier', array(), 'request', 'array');

			foreach ($vress_data->read->KeyCollection->StreamVariations as $k => $streamvar) {
				$sel_checkin_met = VikRequest::getString("vress_{$streamvar->name}", '', 'request');
				if (empty($sel_checkin_met)) {
					continue;
				}
				$checkin_met = new stdClass;
				$checkin_met->stream_variation_name = $streamvar->name;
				$checkin_met->checkin_method = $sel_checkin_met;
				if ($sel_checkin_met != 'other') {
					// we need to collect the additional info object
					$checkin_met->additional_info = new stdClass;
					
					// other_text object
					if (!in_array($sel_checkin_met, array('instruction_will_send', 'instruction_contact_us'))) {
						// all the other check-in methods require the other_text object
						$checkin_met->additional_info->other_text = new stdClass;
						$checkin_met->additional_info->other_text->lang = substr(JFactory::getLanguage()->getTag(), 0, 2);
						// make sure the other_text is not empty as it's a mandatory field
						if (!isset($other_text[$k]) || empty($other_text[$k])) {
							// raise warning and skip check-in method completely
							VikError::raiseWarning('', 'Empty instructions for guests for check-in method ' . $sel_checkin_met);
							continue;
						}
						$checkin_met->additional_info->other_text->text = $other_text[$k];
					}

					// brand_name property
					if (in_array($sel_checkin_met, array('door_code', 'lock_box'))) {
						if (!isset($brand_name[$k]) || empty($brand_name[$k])) {
							// raise warning and skip check-in method completely
							VikError::raiseWarning('', 'Empty brand name for ' . $sel_checkin_met);
							continue;
						}
						$checkin_met->additional_info->brand_name = $brand_name[$k];
					}

					// location object
					if (in_array($sel_checkin_met, array('reception', 'someone_will_meet', 'secret_spot', 'lock_box'))) {
						$checkin_met->additional_info->location = new stdClass;
						$checkin_met->additional_info->location->off_location = isset($off_location[$k]) && intval($off_location[$k]) > 0 ? 1 : 0;
						if ($checkin_met->additional_info->location->off_location > 0) {
							// when "off_location": 1, other fields are mandatory
							if (!isset($address[$k]) || empty($address[$k])) {
								// raise warning and skip check-in method completely
								VikError::raiseWarning('', 'Empty address for location of ' . $sel_checkin_met);
								continue;
							}
							if (!isset($city[$k]) || empty($city[$k])) {
								// raise warning and skip check-in method completely
								VikError::raiseWarning('', 'Empty city for location of ' . $sel_checkin_met);
								continue;
							}
							if (!isset($zip[$k]) || empty($zip[$k])) {
								// raise warning and skip check-in method completely
								VikError::raiseWarning('', 'Empty zip for location of ' . $sel_checkin_met);
								continue;
							}
							$checkin_met->additional_info->location->address = $address[$k];
							$checkin_met->additional_info->location->city = $city[$k];
							$checkin_met->additional_info->location->zip = $zip[$k];
						}
					}

					// instruction object
					if (in_array($sel_checkin_met, array('instruction_will_send', 'instruction_contact_us'))) {
						$checkin_met->additional_info->instruction = new stdClass;
						// how property is mandatory for both methods
						if (!isset($how[$k]) || empty($how[$k])) {
							// raise warning and skip check-in method completely
							VikError::raiseWarning('', 'Empty how for instruction of ' . $sel_checkin_met);
							continue;
						}
						$checkin_met->additional_info->instruction->how = $how[$k];
						if ($sel_checkin_met == 'instruction_will_send' && $checkin_met->additional_info->instruction->how != 'other') {
							// when property is mandatory
							if (!isset($when[$k]) || empty($when[$k])) {
								// raise warning and skip check-in method completely
								VikError::raiseWarning('', 'Empty when instructions will be sent for ' . $sel_checkin_met);
								continue;
							}
							$checkin_met->additional_info->instruction->when = $when[$k];
						}
						if ($sel_checkin_met == 'instruction_contact_us' && $checkin_met->additional_info->instruction->how != 'other') {
							// identifier property is mandatory when how != other
							if (!isset($identifier[$k]) || empty($identifier[$k])) {
								// raise warning and skip check-in method completely
								VikError::raiseWarning('', 'Empty contact info identifier for ' . $sel_checkin_met);
								continue;
							}
							$checkin_met->additional_info->instruction->identifier = $identifier[$k];
						}
						if ($checkin_met->additional_info->instruction->how == 'other') {
							// free text instructions (other) are mandatory for both methods
							if (!isset($other[$k]) || empty($other[$k])) {
								// raise warning and skip check-in method completely
								VikError::raiseWarning('', 'Empty other explanation free text for ' . $sel_checkin_met);
								continue;
							}
							$checkin_met->additional_info->instruction->other = $other[$k];
						}
					}
				}

				// push composed check-in method object if we reach this point
				array_push($checkin_mets, $checkin_met);
			}

			// check if we have some check-in method objects
			if (count($checkin_mets)) {
				// push the write command to the XML request
				$write_command = new stdClass;
				$write_command->type = 'KeyCollection';
				$write_command->data = array();
				foreach ($checkin_mets as $checkin_met) {
					// push data nodes
					$command_data = new stdClass;
					$command_data->attributes = array(
						'type'  => 'checkin_methods',
						'extra' => 'json',
					);
					$command_data->content = json_encode($checkin_met);
					array_push($write_command->data, $command_data);
				}

				// push writing command object for Key Collection API
				array_push($xml_writing_nodes, $write_command);
			}
		}

		// Property Profile API
		$built_date = VikRequest::getString('built_date', '', 'request');
		$renovating_date = VikRequest::getString('renovating_date', '', 'request');
		$name_or_company = VikRequest::getString('name_or_company', '', 'request');
		$host_location = VikRequest::getString('host_location', '', 'request');
		$renting_date = VikRequest::getString('renting_date', '', 'request');
		$is_company_profile = VikRequest::getInt('is_company_profile', 0, 'request');
		if (!empty($built_date) && substr($built_date, 0, 4) != '0000') {
			// push the write command to the XML request
			$write_command = new stdClass;
			$write_command->type = 'PropertyProfile';
			$write_command->data = array();
			// push data nodes
			$command_data = new stdClass;
			$command_data->attributes = array(
				'type'  => 'built_date',
				'extra' => '',
			);
			$command_data->content = $built_date;
			array_push($write_command->data, $command_data);
			// renovating_date
			if (!empty($renovating_date) && substr($renovating_date, 0, 4) != '0000') {
				$command_data = new stdClass;
				$command_data->attributes = array(
					'type'  => 'renovating_date',
					'extra' => '',
				);
				$command_data->content = $renovating_date;
				array_push($write_command->data, $command_data);
			}
			// name_or_company
			if (!empty($name_or_company)) {
				$command_data = new stdClass;
				$command_data->attributes = array(
					'type'  => 'name_or_company',
					'extra' => '',
				);
				$command_data->content = $name_or_company;
				array_push($write_command->data, $command_data);
			}
			// host_location
			if (!empty($host_location)) {
				$command_data = new stdClass;
				$command_data->attributes = array(
					'type'  => 'host_location',
					'extra' => '',
				);
				$command_data->content = $host_location;
				array_push($write_command->data, $command_data);
			}
			// renting_date
			if (!empty($renting_date) && substr($renting_date, 0, 4) != '0000') {
				$command_data = new stdClass;
				$command_data->attributes = array(
					'type'  => 'renting_date',
					'extra' => '',
				);
				$command_data->content = $renting_date;
				array_push($write_command->data, $command_data);
			}
			// is_company_profile
			$command_data = new stdClass;
			$command_data->attributes = array(
				'type'  => 'is_company_profile',
				'extra' => '',
			);
			$command_data->content = $is_company_profile;
			array_push($write_command->data, $command_data);

			// push writing command object for Property Profile
			array_push($xml_writing_nodes, $write_command);
		}

		// check if an XML request is necessary
		if (!count($xml_writing_nodes)) {
			return false;
		}

		$e4jc_url = "https://e4jconnect.com/channelmanager/?r=vress&c=booking.com";
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager VRESS Request e4jConnect.com - Vik Channel Manager -->
<VacationRentalsEssentialsRQ xmlns="http://www.e4jconnect.com/schemas/vressrq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . $api_key . '"/>
	<Fetch hotelid="' . $hotelid . '"/>' . "\n";
		
		foreach ($xml_writing_nodes as $write_command) {
			$xml .= "\t" . '<Write type="' . $write_command->type . '">' . "\n";
			foreach ($write_command->data as $command_data) {
				$attr_list = array();
				$needs_cdata = false;
				foreach ($command_data->attributes as $attrk => $attrv) {
					$needs_cdata = $attrk == 'extra' && $attrv == 'json' ? true : $needs_cdata;
					array_push($attr_list, $attrk . '="' . $attrv . '"');
				}
				$xml .= "\t\t" . '<Data ' . implode(' ', $attr_list) . '>' . ($needs_cdata ? '<![CDATA[ ' : '');
				$xml .= htmlspecialchars($command_data->content);
				$xml .= ($needs_cdata ? ' ]]>' : '') . '</Data>' . "\n";
			}
			$xml .= "\t" . '</Write>' . "\n";
		}

		$xml .= '</VacationRentalsEssentialsRQ>';

		// make the request to e4jConnect
		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		$e4jC->slaveEnabled = true;
		$rs = $e4jC->exec();
		if ($e4jC->getErrorNo()) {
			$session->set('vress_write_err', ($vress_errors_count + 1), 'vcm-vress');
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($e4jC->getErrorMsg()));
			return false;
		} elseif (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			$session->set('vress_write_err', ($vress_errors_count + 1), 'vcm-vress');
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
			return false;
		}
		// we do not really need the response when only writing
		$response = substr($rs, 0, 1) == '{' ? json_decode($rs) : null;
		if (!is_object($response)) {
			VikError::raiseWarning('', 'Cannot decode channel response');
			return false;
		}

		// set a flag in the session for reading the values again
		$session->set('force_vress', 1, 'vcm-vress');

		// print success message
		JFactory::getApplication()->enqueueMessage(JText::_('VCMVRESSINFOUPDATED'));
		
		return true;
	}

	/**
	 * AJAX request for getting the Guest Misconduct Categories of Booking.com.
	 * 
	 * @since 	1.7.2
	 */
	public function get_bcom_misconduct_categories() {
		$dbo 		= JFactory::getDbo();
		$session 	= JFactory::getSession();
		$forcedown 	= VikRequest::getInt('force', 0, 'request');

		// this request is only for Booking.com
		$req_channel = VikChannelManagerConfig::BOOKING;
		$hotelid = null;

		$response = new stdClass;
		$response->status = 0;
		$response->data = null;
		$response->error = 'Invalid response';

		// check errors counter
		$errors_count = (int)$session->get("err_misconductcats_{$req_channel}", 0, 'vcm-misconductcats');
		if ($errors_count > 1 && empty($forcedown)) {
			echo json_encode($response);
			exit;
		}

		// make sure some rooms have been mapped so that we know the channel connection is working
		$q = "SELECT `prop_params` FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel`=" . $dbo->quote($req_channel) . " LIMIT 1;";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			$response->error = 'No rooms mapped for Booking.com';
			echo json_encode($response);
			exit;
		}
		$prop_params = json_decode($dbo->loadResult(), true);
		if (!is_array($prop_params)) {
			echo json_encode($response);
			exit;
		}
		foreach ($prop_params as $param) {
			if (!empty($param)) {
				$hotelid = $param;
				break;
			}
		}
		if (empty($hotelid)) {
			echo json_encode($response);
			exit;
		}

		// check current session value
		$categories = $session->get("misconductcats_{$req_channel}_{$hotelid}", '', 'vcm-misconductcats');

		$download = (empty($categories) || $forcedown);

		// download misconduct categories
		if ($download) {
			$api_key = VikChannelManager::getApiKey(true);
			$e4jc_url = "https://e4jconnect.com/channelmanager/?r=vress&c=booking.com";
			$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager VRESS Request e4jConnect.com - Vik Channel Manager -->
<VacationRentalsEssentialsRQ xmlns="http://www.e4jconnect.com/schemas/vressrq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . $api_key . '"/>
	<Fetch hotelid="' . $hotelid . '"/>
	<Read type="MisconductCategories"/>
</VacationRentalsEssentialsRQ>';
			
			$e4jC = new E4jConnectRequest($e4jc_url);
			$e4jC->setPostFields($xml);
			$e4jC->slaveEnabled = true;
			$rs = $e4jC->exec();
			if ($e4jC->getErrorNo()) {
				$session->set("err_misconductcats_{$req_channel}", ($errors_count + 1), 'vcm-misconductcats');
				$response->error = VikChannelManager::getErrorFromMap($e4jC->getErrorMsg());
				echo json_encode($response);
				exit;
			} elseif (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
				$session->set("err_misconductcats_{$req_channel}", ($errors_count + 1), 'vcm-misconductcats');
				$response->error = VikChannelManager::getErrorFromMap($rs);
				echo json_encode($response);
				exit;
			} else {
				$categories = json_decode($rs);
			}
		}

		if (!is_object($categories) || !isset($categories->read) || !isset($categories->read->MisconductCategories)) {
			$response->error = 'Cannot decode response, or empty response';
			echo json_encode($response);
			exit;
		}

		// update session value
		$session->set("misconductcats_{$req_channel}_{$hotelid}", $categories, 'vcm-misconductcats');

		// build the reporting fields object by adding the mandatory fields for all requests
		$reporting_fields = new stdClass;
		$reporting_fields->categories = $categories->read->MisconductCategories;
		$reporting_fields->fields = array();
		$reporting_fields->labels = new stdClass;

		// details_text
		$field = new stdClass;
		$field->name = 'details_text';
		$field->label = JText::_('VCMGUESTMISCONDUCT_DETAILSTEXT');
		$field->type = 'textarea';
		$field->maxlength = 240;
		array_push($reporting_fields->fields, $field);

		// escalate_report
		$field = new stdClass;
		$field->name = 'escalate_report';
		$field->label = JText::_('VCMGUESTMISCONDUCT_ESCALATEREPORT');
		$field->type = 'select';
		$field->choices = array(
			JText::_('VCMGUESTMISCONDUCT_ESCALATEREPORT_NO'),
			JText::_('VCMGUESTMISCONDUCT_ESCALATEREPORT_YES'),
		);
		array_push($reporting_fields->fields, $field);

		// rebooking_allowed
		$field = new stdClass;
		$field->name = 'rebooking_allowed';
		$field->label = JText::_('VCMGUESTMISCONDUCT_REBOOKINGALLOWED');
		$field->type = 'select';
		$field->choices = array(
			JText::_('VCMGUESTMISCONDUCT_REBOOKINGALLOWED_NO'),
			JText::_('VCMGUESTMISCONDUCT_REBOOKINGALLOWED_YES'),
		);
		array_push($reporting_fields->fields, $field);

		// push some other useful labels for vbo
		$reporting_fields->labels->category = JText::_('VCMGUESTMISCONDUCT_CATEGORY');
		$reporting_fields->labels->subcategory = JText::_('VCMGUESTMISCONDUCT_SUBCATEGORY');

		// set success response
		$response->status = 1;
		$response->data = $reporting_fields;

		// return the misconduct categories obtained as well as the other mandatory fields inside a response object
		echo json_encode($response);
		exit;
	}

	/**
	 * AJAX request for submitting the Guest Misconduct Categories to Booking.com.
	 * 
	 * @since 	1.7.2
	 */
	public function submit_bcom_guestmisconduct() {
		$dbo 	 = JFactory::getDbo();
		$session = JFactory::getSession();

		// this request is only for Booking.com
		$req_channel = VikChannelManagerConfig::BOOKING;
		$hotelid = null;

		$response = new stdClass;
		$response->status = 0;
		$response->data = null;
		$response->error = 'Invalid response';

		// check errors counter (we use the same session value as for reading the categories)
		$errors_count = (int)$session->get("err_misconductcats_{$req_channel}", 0, 'vcm-misconductcats');
		if ($errors_count > 2 && empty($forcedown)) {
			echo json_encode($response);
			exit;
		}

		// make sure some rooms have been mapped so that we know the channel connection is working
		$q = "SELECT `prop_params` FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel`=" . $dbo->quote($req_channel) . " LIMIT 1;";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			$response->error = 'No rooms mapped for Booking.com';
			echo json_encode($response);
			exit;
		}
		$prop_params = json_decode($dbo->loadResult(), true);
		if (!is_array($prop_params)) {
			echo json_encode($response);
			exit;
		}
		foreach ($prop_params as $param) {
			if (!empty($param)) {
				$hotelid = $param;
				break;
			}
		}
		if (empty($hotelid)) {
			echo json_encode($response);
			exit;
		}

		// request fields
		$bid = VikRequest::getInt('bid', 0, 'request');
		$otabid = VikRequest::getString('otabid', '', 'request');
		$bcom_keys = VikRequest::getVar('bcom_keys', array(), 'request', 'array');
		if (empty($bid) || empty($otabid)) {
			$response->error = 'Empty booking ID';
			echo json_encode($response);
			exit;
		}
		if (!count($bcom_keys)) {
			$response->error = 'Empty request keys';
			echo json_encode($response);
			exit;
		}

		// compose the request
		$data_nodes = array();
		array_push($data_nodes, '<Data type="reservation_id" extra="request">' . $otabid . '</Data>');
		foreach ($bcom_keys as $bcom_key) {
			$bcom_var = VikRequest::getString($bcom_key, '', 'request');
			if (defined('ENT_XML1')) {
				// only available from PHP 5.4 and on
				$bcom_var = htmlspecialchars($bcom_var, ENT_XML1 | ENT_COMPAT, 'UTF-8');
			}
			$usekey = str_replace('bcom_', '', $bcom_key);
			array_push($data_nodes, '<Data type="' . $usekey . '" extra="misconduct_details">' . $bcom_var . '</Data>');
		}

		// make the request to e4jConnect
		$api_key = VikChannelManager::getApiKey(true);
		$e4jc_url = "https://e4jconnect.com/channelmanager/?r=vress&c=booking.com";
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager VRESS Request e4jConnect.com - Vik Channel Manager -->
<VacationRentalsEssentialsRQ xmlns="http://www.e4jconnect.com/schemas/vressrq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . $api_key . '"/>
	<Fetch hotelid="' . $hotelid . '"/>
	<Write type="GuestMisconduct">
		' . implode("\n", $data_nodes) . '
	</Write>
</VacationRentalsEssentialsRQ>';
			
		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		$e4jC->slaveEnabled = true;
		$rs = $e4jC->exec();
		if ($e4jC->getErrorNo()) {
			$session->set("err_misconductcats_{$req_channel}", ($errors_count + 1), 'vcm-misconductcats');
			$response->error = VikChannelManager::getErrorFromMap($e4jC->getErrorMsg());
			echo json_encode($response);
			exit;
		} elseif (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			$session->set("err_misconductcats_{$req_channel}", ($errors_count + 1), 'vcm-misconductcats');
			$response->error = VikChannelManager::getErrorFromMap($rs);
			echo json_encode($response);
			exit;
		}

		// parse the response
		$result = json_decode($rs);
		if (!is_object($result) || !isset($result->write) || !isset($result->write->GuestMisconduct)) {
			$response->error = 'Cannot decode update esponse, or empty response';
			echo json_encode($response);
			exit;
		}

		if (!is_string($result->write->GuestMisconduct) || strpos($result->write->GuestMisconduct, 'e4j.ok') === false) {
			$response->error = 'Unexpected update response';
			echo json_encode($response);
			exit;
		}

		// return success response
		$response->status = 1;
		$response->data = $result->write->GuestMisconduct;
		echo json_encode($response);
		exit;
	}

	/**
	 * AJAX request for sending the information of the security (damage) deposit to Booking.com.
	 * 
	 * @since 	1.7.2
	 */
	public function get_bcom_security_deposit_fields() {
		$dbo 	 = JFactory::getDbo();
		$session = JFactory::getSession();

		// this request is only for Booking.com
		$req_channel = VikChannelManagerConfig::BOOKING;
		$hotelid = null;

		$response = new stdClass;
		$response->status = 0;
		$response->data = null;
		$response->error = 'Invalid response';

		// check errors counter (we use the same session value as for reading the categories)
		$errors_count = (int)$session->get("err_securitydeposit_{$req_channel}", 0, 'vcm-securitydeposit');
		if ($errors_count > 1 && empty($forcedown)) {
			$response->error = 'Too many API errors occurred. Unable to proceed.';
			echo json_encode($response);
			exit;
		}

		// make sure some rooms have been mapped so that we know the channel connection is working
		$q = "SELECT `prop_params` FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel`=" . $dbo->quote($req_channel) . " LIMIT 1;";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			$response->error = 'The channel Booking.com is not enabled or set up in the Channel Manager.';
			echo json_encode($response);
			exit;
		}
		$prop_params = json_decode($dbo->loadResult(), true);
		if (!is_array($prop_params)) {
			echo json_encode($response);
			exit;
		}
		foreach ($prop_params as $param) {
			if (!empty($param)) {
				$hotelid = $param;
				break;
			}
		}
		if (empty($hotelid)) {
			echo json_encode($response);
			exit;
		}

		// we return a list of fields for VBO to render a form to submit the information
		$fields = array();

		// account ID
		$all_accounts = VikChannelManager::getChannelAccountsMapped($req_channel);
		if (!count($all_accounts)) {
			$response->error = 'No active Booking.com accounts found.';
			echo json_encode($response);
			exit;
		}
		$field = new stdClass;
		$field->name = 'HotelCode';
		$field->label = JText::_('VCMACCOUNTCHANNELID');
		$field->type = 'select';
		$field->choices = $all_accounts;
		$field->value = $hotelid;
		// push field
		array_push($fields, $field);

		// amount
		$field = new stdClass;
		$field->name = 'SecurityDepositAmount';
		$field->label = JText::_('VCMBCAHAMOUNT');
		$field->type = 'number';
		$field->value = null;
		// push field
		array_push($fields, $field);

		// collect method
		$field = new stdClass;
		$field->name = 'SecurityDepositCollectMethod';
		$field->label = JText::_('VCMBCAHPAYMETH');
		$field->type = 'select';
		$field->choices = array(
			'bank_transfer' => 'Bank Transfer',
			'credit_card' => 'Credit Card',
			'paypal' => 'PayPal',
			'cash' => 'Cash',
		);
		$field->value = 'credit_card';
		// push field
		array_push($fields, $field);

		// collect when
		$field = new stdClass;
		$field->name = 'SecurityDepositCollectWhen';
		$field->label = JText::_('VCMVRESSPCINMETADDINFOWHEN');
		$field->type = 'select';
		$field->choices = array(
			'before_arrival' => 'Before arrival',
			'upon_arrival' => 'Upon arrival',
			'when_guest_books' => 'When guest books',
		);
		$field->value = 'when_guest_books';
		// push field
		array_push($fields, $field);

		// collect num days
		$field = new stdClass;
		$field->name = 'SecurityDepositCollectNumDays';
		$field->label = JText::_('VCMDMGDEPCOLLECTNUMDAYS');
		$field->type = 'select';
		$field->choices = array(
			'0' => '0',
			'3' => '3',
			'7' => '7',
			'30' => '30',
		);
		$field->value = '0';
		// push field
		array_push($fields, $field);

		// deposit return method
		$field = new stdClass;
		$field->name = 'SecurityDepositReturnMethod';
		$field->label = JText::_('VCMDMGDEPRETMETH');
		$field->type = 'select';
		$field->choices = array(
			'bank_transfer' => 'Bank Transfer',
			'credit_card' => 'Credit Card',
			'paypal' => 'PayPal',
			'cash' => 'Cash',
		);
		$field->value = 'credit_card';
		// push field
		array_push($fields, $field);

		// deposit return when
		$field = new stdClass;
		$field->name = 'SecurityDepositReturnWhen';
		$field->label = JText::_('VCMDMGDEPRETWHEN');
		$field->type = 'select';
		$field->choices = array(
			'on_checkout' => 'On checkout',
			'within_7_days' => 'Within 7 days',
			'within_14_days' => 'Within 14 days',
		);
		$field->value = 'on_checkout';
		// push field
		array_push($fields, $field);

		// prepare response
		$response->status = 1;
		$response->data = $fields;

		echo json_encode($response);
		exit;
	}

	/**
	 * AJAX request for submitting the Security Deposit to Booking.com.
	 * This is part of the Vacation Rentals Essentials API (Damage Deposit).
	 * 
	 * @since 	1.7.2
	 */
	public function submit_bcom_security_deposit() {
		$dbo 	 = JFactory::getDbo();
		$session = JFactory::getSession();

		// this request is only for Booking.com
		$req_channel = VikChannelManagerConfig::BOOKING;
		$hotelid = null;

		$response = new stdClass;
		$response->status = 0;
		$response->data = null;
		$response->error = 'Invalid response';

		// check errors counter (we use the same session value as for reading the security deposit fields)
		$errors_count = (int)$session->get("err_securitydeposit_{$req_channel}", 0, 'vcm-securitydeposit');
		if ($errors_count > 1 && empty($forcedown)) {
			echo json_encode($response);
			exit;
		}

		// make sure some rooms have been mapped so that we know the channel connection is working
		$q = "SELECT `prop_params` FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel`=" . $dbo->quote($req_channel) . " LIMIT 1;";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			$response->error = 'No rooms mapped for Booking.com';
			echo json_encode($response);
			exit;
		}
		$prop_params = json_decode($dbo->loadResult(), true);
		if (!is_array($prop_params)) {
			echo json_encode($response);
			exit;
		}
		foreach ($prop_params as $param) {
			if (!empty($param)) {
				$hotelid = $param;
				break;
			}
		}
		if (empty($hotelid)) {
			echo json_encode($response);
			exit;
		}

		// request fields
		$optid = VikRequest::getInt('optid', 0, 'request');
		$bcom_keys = VikRequest::getVar('bcom_keys', array(), 'request', 'array');
		if (empty($optid)) {
			$response->error = 'Empty option ID';
			echo json_encode($response);
			exit;
		}
		if (!count($bcom_keys)) {
			$response->error = 'Empty request keys';
			echo json_encode($response);
			exit;
		}

		// compose the request
		$data_nodes = array();
		foreach ($bcom_keys as $bcom_key) {
			$bcom_var = VikRequest::getString($bcom_key, '', 'request');
			$usekey = str_replace('bcom_', '', $bcom_key);
			$extra_attr = stripos($usekey, 'HotelCode') !== false ? 'HotelCode' : 'Option';
			array_push($data_nodes, '<Data type="' . $usekey . '" extra="' . $extra_attr . '">' . $bcom_var . '</Data>');
		}

		// make the request to e4jConnect
		$api_key = VikChannelManager::getApiKey(true);
		$e4jc_url = "https://e4jconnect.com/channelmanager/?r=vress&c=booking.com";
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager VRESS Request e4jConnect.com - Vik Channel Manager -->
<VacationRentalsEssentialsRQ xmlns="http://www.e4jconnect.com/schemas/vressrq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . $api_key . '"/>
	<Fetch hotelid="' . $hotelid . '"/>
	<Write type="SecurityDeposit">
		' . implode("\n", $data_nodes) . '
	</Write>
</VacationRentalsEssentialsRQ>';
			
		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		$e4jC->slaveEnabled = true;
		$rs = $e4jC->exec();
		if ($e4jC->getErrorNo()) {
			$session->set("err_securitydeposit_{$req_channel}", ($errors_count + 1), 'vcm-securitydeposit');
			$response->error = VikChannelManager::getErrorFromMap($e4jC->getErrorMsg());
			echo json_encode($response);
			exit;
		} elseif (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			$session->set("err_securitydeposit_{$req_channel}", ($errors_count + 1), 'vcm-securitydeposit');
			$response->error = VikChannelManager::getErrorFromMap($rs);
			echo json_encode($response);
			exit;
		}

		// parse the response
		$result = json_decode($rs);
		if (!is_object($result) || !isset($result->write) || !isset($result->write->SecurityDeposit)) {
			$response->error = 'Cannot decode update esponse, or empty response';
			echo json_encode($response);
			exit;
		}

		if (!is_string($result->write->SecurityDeposit) || strpos($result->write->SecurityDeposit, 'e4j.ok') === false) {
			$response->error = 'Unexpected update response';
			echo json_encode($response);
			exit;
		}

		// return success response
		$response->status = 1;
		$response->data = $result->write->SecurityDeposit;
		echo json_encode($response);
		exit;
	}

	/**
	 * VikBooking may redirect to or display this view to decline an OTA booking
	 * and to provide the necessary decline reasons to perform a request to e4jConnect.
	 * 
	 * @since 	1.8.0
	 */
	public function declinebooking() {
		$rq_tmpl = VikRequest::getString('tmpl', '', 'request');
		if (VikChannelManager::isAvailabilityRequest()) {
			if ($rq_tmpl != 'component') {
				VCM::printMenu();
			}
			VikRequest::setVar('view', VikRequest::getCmd('view', 'declinebooking'));
		} else {
			VikRequest::setVar('view', VikRequest::getCmd('view', 'dashboard'));
		}

		parent::display();
		
		if ($rq_tmpl != 'component') {
			VCM::printFooter();
		}
	}

	/**
	 * @since 	1.8.0
	 */
	public function cancelDeclineBooking() {
		$vbo_oid = VikRequest::getInt('vbo_oid', 0, 'request');
		JFactory::getApplication()->redirect("index.php?option=com_vikbooking&task=editorder&cid[]=" . $vbo_oid);
		exit;
	}

	/**
	 * @since 	1.8.0
	 */
	public function doDeclineBooking() {
		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();

		$vbo_oid = VikRequest::getInt('vbo_oid', 0, 'request');
		$rq_tmpl = VikRequest::getString('tmpl', '', 'request');
		$res_action = VikRequest::getString('res_action', 'deny', 'request');
		$res_action = empty($res_action) || !in_array($res_action, array('accept', 'deny')) ? 'deny' : $res_action;
		$decline_reason = VikRequest::getString('decline_reason', '', 'request');
		$decline_guest_mess = VikRequest::getString('decline_guest_mess', '', 'request');
		$decline_ota_mess = VikRequest::getString('decline_ota_mess', '', 'request');

		if (empty($vbo_oid)) {
			VikError::raiseWarning('', 'Missing booking ID for cancellation');
			$app->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}

		$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id`=" . (int)$vbo_oid . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			VikError::raiseWarning('', 'Booking ID not found for cancellation');
			$app->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}
		$reservation = $dbo->loadAssoc();

		if (!VikChannelManager::reservationNeedsDeclineReasons($reservation)) {
			// just let VBO remove the booking as decline reasons are not needed
			VikError::raiseWarning('', 'Booking does not need decline reasons and so it can just be cancelled');
			$app->redirect("index.php?option=com_vikbooking&task=editorder&cid[]=" . $reservation['id']);
			exit;
		}

		$channel = VikChannelManager::getChannel(VikChannelManagerConfig::AIRBNBAPI);
		if (!is_array($channel) || !count($channel)) {
			VikError::raiseWarning('', 'No valid channels available to provide a decline reason for the booking, so it can just be cancelled');
			$app->redirect("index.php?option=com_vikbooking&task=editorder&cid[]=" . $reservation['id']);
			exit;
		}

		// find the mapping information for the room(s) booked
		$account_key = null;
		$q = "SELECT `or`.`idroom`, `x`.`idroomota`, `x`.`prop_params` FROM `#__vikbooking_ordersrooms` AS `or` LEFT JOIN `#__vikchannelmanager_roomsxref` AS `x` ON `or`.`idroom`=`x`.`idroomvb` WHERE `or`.`idorder`=" . $reservation['id'] . " AND `x`.`idchannel`=" . (int)$channel['uniquekey'] . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			$rooms_assoc = $dbo->loadAssocList();
			foreach ($rooms_assoc as $rassoc) {
				if (empty($rassoc['prop_params'])) {
					continue;
				}
				$account_data = json_decode($rassoc['prop_params'], true);
				if (is_array($account_data) && count($account_data)) {
					foreach ($account_data as $acc_val) {
						// we grab the first param value
						if (!empty($acc_val)) {
							$account_key = $acc_val;
							break 2;
						}
					}
				}
			}
		}
		if (empty($account_key)) {
			// the account credentials must be present to perform the request
			VikError::raiseWarning('', 'Could not find the channel account params');
			$app->redirect("index.php?option=com_vikbooking&task=editorder&cid[]=" . $reservation['id']);
			exit;
		}

		// make sure some reasons were provided
		if (empty($decline_reason) && empty($decline_guest_mess)) {
			VikError::raiseWarning('', 'Please provide some reasons for the cancellation');
			$app->redirect("index.php?option=com_vikchannelmanager&task=declinebooking&cid[]=" . $reservation['id'] . (!empty($rq_tmpl) ? '&tmpl=' . $rq_tmpl : ''));
			exit;
		}

		// sanitize values for XML request
		if (defined('ENT_XML1')) {
			// only available from PHP 5.4 and on
			$decline_reason = htmlspecialchars($decline_reason, ENT_XML1 | ENT_COMPAT, 'UTF-8');
			$decline_guest_mess = htmlspecialchars($decline_guest_mess, ENT_XML1 | ENT_COMPAT, 'UTF-8');
			$decline_ota_mess = htmlspecialchars($decline_ota_mess, ENT_XML1 | ENT_COMPAT, 'UTF-8');
		} else {
			// fallback to plain all html entities
			$decline_reason = htmlentities($decline_reason);
			$decline_guest_mess = htmlentities($decline_guest_mess);
			$decline_ota_mess = htmlentities($decline_ota_mess);
		}

		// make the request to e4jConnect
		$api_key = VikChannelManager::getApiKey(true);
		
		$e4jc_url = "https://slave.e4jconnect.com/channelmanager/?r=actpr&c=" . $channel['name'];
		
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager ACTPR Request e4jConnect.com - Vik Channel Manager -->
<ActionPendingReservationRQ xmlns="http://www.e4jconnect.com/schemas/actprrq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . $api_key . '"/>
	<Fetch hotelid="' . $account_key . '"/>
	<Action type="' . $res_action . '" otaresid="' . $reservation['idorderota'] . '">
		<Reason><![CDATA[' . $decline_reason . ']]></Reason>
		<GuestMessage><![CDATA[' . $decline_guest_mess . ']]></GuestMessage>
		<OtaMessage><![CDATA[' . $decline_ota_mess . ']]></OtaMessage>
	</Action>
</ActionPendingReservationRQ>';
			
		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		$e4jC->slaveEnabled = true;
		$rs = $e4jC->exec();
		if ($e4jC->getErrorNo()) {
			VikError::raiseWarning('', 'Request error: ' . VikChannelManager::getErrorFromMap($e4jC->getErrorMsg()));
			$app->redirect("index.php?option=com_vikchannelmanager&task=declinebooking&cid[]=" . $reservation['id'] . (!empty($rq_tmpl) ? '&tmpl=' . $rq_tmpl : ''));
			exit;
		} elseif (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			VikError::raiseWarning('', 'Response error: ' . VikChannelManager::getErrorFromMap($rs));
			$app->redirect("index.php?option=com_vikchannelmanager&task=declinebooking&cid[]=" . $reservation['id'] . (!empty($rq_tmpl) ? '&tmpl=' . $rq_tmpl : ''));
			exit;
		}

		/**
		 * Response was successful, go back to the decline booking View that will try
		 * to dismiss the modal and/or redirect. Store a log in the history of VBO first.
		 */
		$say_channel_name = $channel['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI ? 'Airbnb' : ucfirst($channel['name']);
		$reasons_vals = array();
		if (!empty($decline_reason)) {
			array_push($reasons_vals, $decline_reason);
		}
		if (!empty($decline_guest_mess)) {
			array_push($reasons_vals, $decline_guest_mess);
		}
		if (!empty($decline_ota_mess)) {
			array_push($reasons_vals, $decline_ota_mess);
		}

		if ($res_action == 'deny') {
			// try to update the VBO Booking History
			try {
				if (!class_exists('VikBooking')) {
					require_once VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'lib.vikbooking.php';
				}
				if (method_exists('VikBooking', 'getBookingHistoryInstance')) {
					VikBooking::getBookingHistoryInstance()->setBid($reservation['id'])->store('CB', $say_channel_name . ' - ' . JText::_('VCM_DECLINE_BOOKING_TITLE') . ":\n" . implode("\n", $reasons_vals));
				}
			} catch (Exception $e) {
				// do nothing
			}

			// update booking status to cancelled, as this was a stand-by reservation
			$q = "UPDATE `#__vikbooking_orders` SET `status`='cancelled' WHERE `id`=" . (int)$reservation['id'] . ";";
			$dbo->setQuery($q);
			$dbo->execute();
			$q = "DELETE FROM `#__vikbooking_tmplock` WHERE `idorder`=" . (int)$reservation['id'] . ";";
			$dbo->setQuery($q);
			$dbo->execute();
		}

		// set success message and redirect
		$app->enqueueMessage(JText::_('MSG_BASE_SUCCESS'));
		$app->redirect("index.php?option=com_vikchannelmanager&task=declinebooking&decline_success=1&cid[]=" . $reservation['id'] . (!empty($rq_tmpl) ? '&tmpl=' . $rq_tmpl : ''));
		exit;
	}

	/**
	 * VikBooking may redirect to or display this view to send a Special Offer through
	 * an OTA (usually Airbnb) to a customer from a pending reservation.
	 * 
	 * @since 	1.8.0
	 */
	public function specialoffer() {
		$rq_tmpl = VikRequest::getString('tmpl', '', 'request');
		if (VikChannelManager::isAvailabilityRequest()) {
			if ($rq_tmpl != 'component') {
				VCM::printMenu();
			}
			VikRequest::setVar('view', VikRequest::getCmd('view', 'specialoffer'));
		} else {
			VikRequest::setVar('view', VikRequest::getCmd('view', 'dashboard'));
		}

		parent::display();
		
		if ($rq_tmpl != 'component') {
			VCM::printFooter();
		}
	}

	/**
	 * @since 	1.8.0
	 */
	public function cancelSpecialOffer() {
		$vbo_oid = VikRequest::getInt('vbo_oid', 0, 'request');
		JFactory::getApplication()->redirect("index.php?option=com_vikbooking&task=editorder&cid[]=" . $vbo_oid);
		exit;
	}

	/**
	 * This task can be used to submit Special Offers of type "special_offer" as well as "preapproval".
	 * Airbnb API is currently the only channel supporting these features. VCM will display a view,
	 * usually within a modal window through VBO for the special_offer, while the "preapproval" type
	 * will be just a link displayed within VBO which expects the system to redirect back to that page.
	 * 
	 * @since 	1.8.0
	 */
	public function sendSpecialOffer() {
		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();

		$vbo_oid = VikRequest::getInt('vbo_oid', 0, 'request');
		$rq_tmpl = VikRequest::getString('tmpl', '', 'request');
		$spo_type = VikRequest::getString('spo_type', 'special_offer', 'request');
		$spo_type = in_array($spo_type, array('special_offer', 'preapproval')) ? $spo_type : 'special_offer';
		$spo_action = VikRequest::getString('spo_action', 'send', 'request');
		$spo_action = in_array($spo_action, array('send', 'withdraw')) ? $spo_action : 'send';
		$spo_id = VikRequest::getString('spo_id', '', 'request');
		
		$ota_thread_id = VikRequest::getString('ota_thread_id', '', 'request');
		$listing_id = VikRequest::getString('listing_id', '', 'request');
		$start_date = VikRequest::getString('start_date', '', 'request');
		$nights = VikRequest::getInt('nights', 0, 'request');
		$adults = VikRequest::getInt('adults', 0, 'request');
		$children = VikRequest::getInt('children', 0, 'request');
		$total_price = VikRequest::getFloat('total_price', 0, 'request');
		$ch_uniquekey = VikRequest::getString('ch_uniquekey', '', 'request');

		if (empty($vbo_oid)) {
			VikError::raiseWarning('', 'Missing booking ID for special offer');
			$app->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}

		$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id`=" . (int)$vbo_oid . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			VikError::raiseWarning('', 'Booking ID not found for special offer');
			$app->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}
		$reservation = $dbo->loadAssoc();

		if ($spo_action == 'withdraw' && empty($spo_id)) {
			// withdrawing a special offer requires its own id
			VikError::raiseWarning('', 'Cannot withdraw special offer type without its ID');
			$app->redirect("index.php?option=com_vikbooking&task=editorder&cid[]=" . $reservation['id']);
			exit;
		}

		if ($spo_action != 'withdraw') {
			// validate offer status
			if ($spo_type == 'special_offer') {
				$special_offer_data = VikChannelManager::reservationSupportsSpecialOffer($reservation);
				if ($special_offer_data === false) {
					VikError::raiseWarning('', 'Booking does not support Special Offer');
					$app->redirect("index.php?option=com_vikbooking&task=editorder&cid[]=" . $reservation['id']);
					exit;
				}
			} else {
				// pre-approval
				$pre_approval_data = VikChannelManager::reservationSupportsPreApproval($reservation);
				if ($pre_approval_data === false) {
					VikError::raiseWarning('', 'Booking does not support Pre-Approval');
					$app->redirect("index.php?option=com_vikbooking&task=editorder&cid[]=" . $reservation['id']);
					exit;
				}
			}
		}

		// we statically inject the channel Airbnb API even though it's passed via POST as $ch_uniquekey (for "special_offer")
		$channel = VikChannelManager::getChannel(VikChannelManagerConfig::AIRBNBAPI);
		if (!is_array($channel) || !count($channel)) {
			VikError::raiseWarning('', 'No valid channels available to send a special offer');
			$app->redirect("index.php?option=com_vikbooking&task=editorder&cid[]=" . $reservation['id']);
			exit;
		}

		// find the mapping information for the room(s) booked
		$account_key = null;
		$vbo_id_room = null;
		if (!empty($listing_id)) {
			// special offer creation view submits the listing id
			$q = "SELECT `or`.`idroom`, `x`.`idroomota`, `x`.`prop_params` FROM `#__vikbooking_ordersrooms` AS `or` LEFT JOIN `#__vikchannelmanager_roomsxref` AS `x` ON `or`.`idroom`=`x`.`idroomvb` WHERE `or`.`idorder`=" . $reservation['id'] . " AND `x`.`idroomota`=" . $dbo->quote($listing_id) . " AND `x`.`idchannel`=" . (int)$channel['uniquekey'] . ";";
		} else {
			// withdraw links or send pre-approval do not submit the listing id
			$q = "SELECT `or`.`idroom`, `x`.`idroomota`, `x`.`prop_params` FROM `#__vikbooking_ordersrooms` AS `or` LEFT JOIN `#__vikchannelmanager_roomsxref` AS `x` ON `or`.`idroom`=`x`.`idroomvb` WHERE `or`.`idorder`=" . $reservation['id'] . " AND `x`.`idchannel`=" . (int)$channel['uniquekey'] . ";";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			$rooms_assoc = $dbo->loadAssocList();
			foreach ($rooms_assoc as $rassoc) {
				if (empty($rassoc['prop_params'])) {
					continue;
				}
				$account_data = json_decode($rassoc['prop_params'], true);
				if (is_array($account_data) && count($account_data)) {
					foreach ($account_data as $acc_val) {
						// we grab the first param value
						if (!empty($acc_val)) {
							$account_key = $acc_val;
							$vbo_id_room = $rassoc['idroom'];
							break 2;
						}
					}
				}
			}
		}
		if (empty($account_key)) {
			// the account credentials must be present to perform the request
			VikError::raiseWarning('', 'Could not find the channel account params');
			$app->redirect("index.php?option=com_vikbooking&task=editorder&cid[]=" . $reservation['id']);
			exit;
		}

		if ($spo_action != 'withdraw') {
			// validate request variables depending on the type of special offer
			if ($spo_type == 'special_offer') {
				if (empty($ota_thread_id) || empty($start_date) || $nights < 1 || ($adults < 1 && $children < 1)) {
					VikError::raiseWarning('', 'Invalid variables submitted');
					$app->redirect("index.php?option=com_vikchannelmanager&task=specialoffer&listing_id={$listing_id}&ota_thread_id={$ota_thread_id}&bid=" . $reservation['id']);
					exit;
				}
			} else {
				// pre-approval
				if (empty($ota_thread_id)) {
					VikError::raiseWarning('', 'Invalid variables submitted');
					$app->redirect("index.php?option=com_vikbooking&task=editorder&cid[]=" . $reservation['id']);
					exit;
				}
			}
		}

		// adjust channel name
		$say_channel_name = $channel['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI ? 'Airbnb' : ucfirst($channel['name']);

		// make the request to e4jConnect
		$api_key = VikChannelManager::getApiKey(true);
		
		// the endpoint is the same for both special offer types, even for withdrawing them
		$e4jc_url = "https://slave.e4jconnect.com/channelmanager/?r=sdspo&c=" . $channel['name'];

		if ($spo_action == 'withdraw') {
			// this is a withdraw action, only the special offer id is actually needed, and it has to be passed via request

			$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager SDSPO Request e4jConnect.com - Vik Channel Manager -->
<SendSpecialOfferRQ xmlns="http://www.e4jconnect.com/schemas/sdsporq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . $api_key . '"/>
	<Fetch hotelid="' . $account_key . '"/>
	<SpecialOffer type="' . $spo_type . '" vboresid="' . $reservation['id'] . '">
		<Offer name="special_offer_id">' . $spo_id . '</Offer>
		<Offer name="update_type">' . $spo_action . '</Offer>
	</SpecialOffer>
</SendSpecialOfferRQ>';
			
			$e4jC = new E4jConnectRequest($e4jc_url);
			$e4jC->setPostFields($xml);
			$e4jC->slaveEnabled = true;
			$rs = $e4jC->exec();
			if ($e4jC->getErrorNo()) {
				VikError::raiseWarning('', 'Request error: ' . VikChannelManager::getErrorFromMap($e4jC->getErrorMsg()));
				$app->redirect("index.php?option=com_vikbooking&task=editorder&cid[]=" . $reservation['id']);
				exit;
			} elseif (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
				VikError::raiseWarning('', 'Response error: ' . VikChannelManager::getErrorFromMap($rs));
				$app->redirect("index.php?option=com_vikbooking&task=editorder&cid[]=" . $reservation['id']);
				exit;
			}

			// try to update the VBO Booking History
			$ev_descr = $spo_type == 'special_offer' ? JText::_('VCM_SPOFFER_WITHDRAWN') : JText::_('VCM_PREAPPROVAL_WITHDRAWN');
			try {
				if (!class_exists('VikBooking')) {
					require_once VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'lib.vikbooking.php';
				}
				if (method_exists('VikBooking', 'getBookingHistoryInstance')) {
					VikBooking::getBookingHistoryInstance()->setBid($reservation['id'])->setExtraData($special_offer)->store('CM', $say_channel_name . ' - ' . $ev_descr);
				}
			} catch (Exception $e) {
				// do nothing
			}

			// update OTA_TYPE_DATA object
			$ota_type_data = json_decode($reservation['ota_type_data']);
			$ota_type_data = is_object($ota_type_data) ? $ota_type_data : (new stdClass);
			$ota_type_data->thread_id = $ota_thread_id;
			$ota_type_data->spo_type = $spo_type;
			$ota_type_data->special_offer_id = $spo_id;
			$ota_type_data->withdrawn = time();

			$record = new stdClass;
			$record->id = $reservation['id'];
			$record->ota_type_data = json_encode($ota_type_data);

			try {
				$dbo->updateObject('#__vikbooking_orders', $record, 'id');
			} catch (Exception $e) {
				// do nothing
			}

			// set success message and redirect
			$app->enqueueMessage(JText::_('MSG_BASE_SUCCESS'));
			$app->redirect("index.php?option=com_vikbooking&task=editorder&cid[]=" . $reservation['id']);
			exit;
		}

		if ($spo_type == 'special_offer') {
			// build, execute and validate request for special offer
		
			$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager SDSPO Request e4jConnect.com - Vik Channel Manager -->
<SendSpecialOfferRQ xmlns="http://www.e4jconnect.com/schemas/sdsporq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . $api_key . '"/>
	<Fetch hotelid="' . $account_key . '"/>
	<SpecialOffer type="' . $spo_type . '" vboresid="' . $reservation['id'] . '">
		<Offer name="thread_id">' . $ota_thread_id . '</Offer>
		<Offer name="listing_id">' . $listing_id . '</Offer>
		<Offer name="start_date">' . $start_date . '</Offer>
		<Offer name="nights">' . $nights . '</Offer>
		<Offer name="adults">' . $adults . '</Offer>
		<Offer name="children">' . $children . '</Offer>
		<Offer name="total_price">' . $total_price . '</Offer>
	</SpecialOffer>
</SendSpecialOfferRQ>';
			
			$e4jC = new E4jConnectRequest($e4jc_url);
			$e4jC->setPostFields($xml);
			$e4jC->slaveEnabled = true;
			$rs = $e4jC->exec();
			if ($e4jC->getErrorNo()) {
				VikError::raiseWarning('', 'Request error: ' . VikChannelManager::getErrorFromMap($e4jC->getErrorMsg()));
				$app->redirect("index.php?option=com_vikchannelmanager&task=specialoffer&listing_id={$listing_id}&ota_thread_id={$ota_thread_id}&bid=" . $reservation['id'] . (!empty($rq_tmpl) ? '&tmpl=' . $rq_tmpl : ''));
				exit;
			} elseif (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
				VikError::raiseWarning('', 'Response error: ' . VikChannelManager::getErrorFromMap($rs));
				$app->redirect("index.php?option=com_vikchannelmanager&task=specialoffer&listing_id={$listing_id}&ota_thread_id={$ota_thread_id}&bid=" . $reservation['id'] . (!empty($rq_tmpl) ? '&tmpl=' . $rq_tmpl : ''));
				exit;
			}

			/**
			 * Response was successful, go back to the View that will try to dismiss
			 * the modal and/or redirect. Store a log in the history of VBO first,
			 * then add a "transient" record onto VCM to memorize the special offer.
			 */
			$special_offer_id = str_replace(array('e4j.OK.', 'e4j.ok.'), '', $rs);

			// compose special offer object for VCM records
			$special_offer = new stdClass;
			$special_offer->type = $spo_type;
			$special_offer->uniquekey = $channel['uniquekey'];
			$special_offer->ota_special_offer_id = $special_offer_id;
			$special_offer->ota_thread_id = $ota_thread_id;
			$special_offer->ota_listing_id = $listing_id;
			$special_offer->vbo_id_room = $vbo_id_room;
			$special_offer->reservation_id = $reservation['id'];
			$special_offer->checkin = $start_date;
			$special_offer->total_price = $total_price;
			$special_offer->ts = time();

			// try to update the VBO Booking History
			try {
				if (!class_exists('VikBooking')) {
					require_once VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'lib.vikbooking.php';
				}
				if (method_exists('VikBooking', 'getBookingHistoryInstance')) {
					VikBooking::getBookingHistoryInstance()->setBid($reservation['id'])->setExtraData($special_offer)->store('CM', $say_channel_name . ' - ' . JText::_('VCM_SPOFFER_SENT_GUEST'));
				}
			} catch (Exception $e) {
				// do nothing
			}

			// update OTA_TYPE_DATA object
			$ota_type_data = json_decode($reservation['ota_type_data']);
			$ota_type_data = is_object($ota_type_data) ? $ota_type_data : (new stdClass);
			$ota_type_data->thread_id = $ota_thread_id;
			$ota_type_data->spo_type = $spo_type;
			$ota_type_data->special_offer_id = $special_offer_id;

			$record = new stdClass;
			$record->id = $reservation['id'];
			$record->ota_type_data = json_encode($ota_type_data);

			try {
				$dbo->updateObject('#__vikbooking_orders', $record, 'id');
			} catch (Exception $e) {
				// do nothing
			}

			// set success message and redirect
			$app->enqueueMessage(JText::_('MSG_BASE_SUCCESS'));
			$app->redirect("index.php?option=com_vikchannelmanager&task=specialoffer&listing_id={$listing_id}&ota_thread_id={$ota_thread_id}&success=1&bid=" . $reservation['id'] . (!empty($rq_tmpl) ? '&tmpl=' . $rq_tmpl : ''));
			exit;
		}

		/**
		 * Special offer type is "preapproval" if we reach this point.
		 */
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager SDSPO Request e4jConnect.com - Vik Channel Manager -->
<SendSpecialOfferRQ xmlns="http://www.e4jconnect.com/schemas/sdsporq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . $api_key . '"/>
	<Fetch hotelid="' . $account_key . '"/>
	<SpecialOffer type="' . $spo_type . '" vboresid="' . $reservation['id'] . '">
		<Offer name="thread_id">' . $ota_thread_id . '</Offer>
	</SpecialOffer>
</SendSpecialOfferRQ>';
		
		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		$e4jC->slaveEnabled = true;
		$rs = $e4jC->exec();
		if ($e4jC->getErrorNo()) {
			VikError::raiseWarning('', 'Request error: ' . VikChannelManager::getErrorFromMap($e4jC->getErrorMsg()));
			$app->redirect("index.php?option=com_vikbooking&task=editorder&cid[]=" . $reservation['id']);
			exit;
		} elseif (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			VikError::raiseWarning('', 'Response error: ' . VikChannelManager::getErrorFromMap($rs));
			$app->redirect("index.php?option=com_vikbooking&task=editorder&cid[]=" . $reservation['id']);
			exit;
		}

		/**
		 * Response was successful. Store a log in the history of VBO first, then
		 * update the OTA_TYPE_DATA object by setting the "special_offer_id" obtained.
		 */
		$special_offer_id = str_replace(array('e4j.OK.', 'e4j.ok.'), '', $rs);

		// compose special offer object for VCM records
		$special_offer = new stdClass;
		$special_offer->type = $spo_type;
		$special_offer->uniquekey = $channel['uniquekey'];
		$special_offer->ota_special_offer_id = $special_offer_id;
		$special_offer->ota_thread_id = $ota_thread_id;
		$special_offer->ts = time();

		// try to update the VBO Booking History
		try {
			if (!class_exists('VikBooking')) {
				require_once VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'lib.vikbooking.php';
			}
			if (method_exists('VikBooking', 'getBookingHistoryInstance')) {
				VikBooking::getBookingHistoryInstance()->setBid($reservation['id'])->setExtraData($special_offer)->store('CM', $say_channel_name . ' - ' . JText::_('VCM_PREAPPROVAL_SENT_GUEST'));
			}
		} catch (Exception $e) {
			// do nothing
		}

		// update OTA_TYPE_DATA object
		$ota_type_data = json_decode($reservation['ota_type_data']);
		$ota_type_data = is_object($ota_type_data) ? $ota_type_data : (new stdClass);
		$ota_type_data->thread_id = $ota_thread_id;
		$ota_type_data->spo_type = $spo_type;
		$ota_type_data->special_offer_id = $special_offer_id;

		$record = new stdClass;
		$record->id = $reservation['id'];
		$record->ota_type_data = json_encode($ota_type_data);

		try {
			$dbo->updateObject('#__vikbooking_orders', $record, 'id');
		} catch (Exception $e) {
			// do nothing
		}

		// set success message and redirect
		$app->enqueueMessage(JText::_('MSG_BASE_SUCCESS'));
		$app->redirect("index.php?option=com_vikbooking&task=editorder&cid[]=" . $reservation['id']);
		exit;
	}

	/**
	 * VikBooking may redirect to or display this view to decline an OTA booking
	 * and to provide the necessary decline reasons to perform a request to e4jConnect.
	 * 
	 * @since 	1.8.0
	 */
	public function hostguestreview() {
		$rq_tmpl = VikRequest::getString('tmpl', '', 'request');
		if (VikChannelManager::isAvailabilityRequest()) {
			if ($rq_tmpl != 'component') {
				VCM::printMenu();
			}
			VikRequest::setVar('view', VikRequest::getCmd('view', 'hostguestreview'));
		} else {
			VikRequest::setVar('view', VikRequest::getCmd('view', 'dashboard'));
		}

		parent::display();
		
		if ($rq_tmpl != 'component') {
			VCM::printFooter();
		}
	}

	/**
	 * @since 	1.8.0
	 */
	public function cancelHostGuestReview() {
		$vbo_oid = VikRequest::getInt('vbo_oid', 0, 'request');
		JFactory::getApplication()->redirect("index.php?option=com_vikbooking&task=editorder&cid[]=" . $vbo_oid);
		exit;
	}

	/**
	 * This method is no longer being used.
	 * 
	 * @since 		1.8.0
	 * 
	 * @deprecated 	1.8.27
	 * 
	 * @see 		review.host_to_guest
	 */
	public function doHostGuestReview()
	{
		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();

		$vbo_oid = VikRequest::getInt('vbo_oid', 0, 'request');
		$rq_tmpl = VikRequest::getString('tmpl', '', 'request');
		$public_review = VikRequest::getString('public_review', '', 'request');
		$private_review = VikRequest::getString('private_review', '', 'request');
		$review_cat_clean = VikRequest::getInt('review_cat_clean', 5, 'request');
		$review_cat_clean_comment = VikRequest::getString('review_cat_clean_comment', '', 'request');
		$review_cat_comm = VikRequest::getInt('review_cat_comm', 5, 'request');
		$review_cat_comm_comment = VikRequest::getString('review_cat_comm_comment', '', 'request');
		$review_cat_hrules = VikRequest::getInt('review_cat_hrules', 5, 'request');
		$review_cat_hrules_comment = VikRequest::getString('review_cat_hrules_comment', '', 'request');
		$review_host_again = VikRequest::getInt('review_host_again', -1, 'request');

		if (empty($vbo_oid)) {
			VikError::raiseWarning('', 'Missing booking ID');
			$app->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}

		$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id`=" . (int)$vbo_oid . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			VikError::raiseWarning('', 'Booking ID not found');
			$app->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}
		$reservation = $dbo->loadAssoc();

		if (!VikChannelManager::hostToGuestReviewSupported($reservation)) {
			VikError::raiseWarning('', 'Booking does not support host to guest review at this time');
			$app->redirect("index.php?option=com_vikbooking&task=editorder&cid[]=" . $reservation['id']);
			exit;
		}

		$channel = VikChannelManager::getChannel(VikChannelManagerConfig::AIRBNBAPI);
		if (!is_array($channel) || !count($channel)) {
			VikError::raiseWarning('', 'No valid channels available to review your guest');
			$app->redirect("index.php?option=com_vikbooking&task=editorder&cid[]=" . $reservation['id']);
			exit;
		}

		// find the mapping information for the room(s) booked
		$account_key = null;
		$q = "SELECT `or`.`idroom`, `x`.`idroomota`, `x`.`prop_params` FROM `#__vikbooking_ordersrooms` AS `or` LEFT JOIN `#__vikchannelmanager_roomsxref` AS `x` ON `or`.`idroom`=`x`.`idroomvb` WHERE `or`.`idorder`=" . $reservation['id'] . " AND `x`.`idchannel`=" . (int)$channel['uniquekey'] . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			$rooms_assoc = $dbo->loadAssocList();
			foreach ($rooms_assoc as $rassoc) {
				if (empty($rassoc['prop_params'])) {
					continue;
				}
				$account_data = json_decode($rassoc['prop_params'], true);
				if (is_array($account_data) && count($account_data)) {
					foreach ($account_data as $acc_val) {
						// we grab the first param value
						if (!empty($acc_val)) {
							$account_key = $acc_val;
							break 2;
						}
					}
				}
			}
		}
		if (empty($account_key)) {
			// the account credentials must be present to perform the request
			VikError::raiseWarning('', 'Could not find the channel account params');
			$app->redirect("index.php?option=com_vikbooking&task=editorder&cid[]=" . $reservation['id']);
			exit;
		}

		// make sure some feedback was provided
		if (empty($public_review)) {
			VikError::raiseWarning('', 'Please provide a feedback and the ratings for the review');
			$app->redirect("index.php?option=com_vikchannelmanager&task=hostguestreview&cid[]=" . $reservation['id'] . (!empty($rq_tmpl) ? '&tmpl=' . $rq_tmpl : ''));
			exit;
		}

		// sanitize values for XML request
		if (defined('ENT_XML1')) {
			// only available from PHP 5.4 and on
			$public_review = htmlspecialchars($public_review, ENT_XML1 | ENT_COMPAT, 'UTF-8');
			$private_review = htmlspecialchars($private_review, ENT_XML1 | ENT_COMPAT, 'UTF-8');
			$review_cat_clean_comment = htmlspecialchars($review_cat_clean_comment, ENT_XML1 | ENT_COMPAT, 'UTF-8');
			$review_cat_comm_comment = htmlspecialchars($review_cat_comm_comment, ENT_XML1 | ENT_COMPAT, 'UTF-8');
			$review_cat_hrules_comment = htmlspecialchars($review_cat_hrules_comment, ENT_XML1 | ENT_COMPAT, 'UTF-8');
			
		} else {
			// fallback to plain all html entities
			$public_review = htmlentities($public_review);
			$private_review = htmlentities($private_review);
			$review_cat_clean_comment = htmlentities($review_cat_clean_comment);
			$review_cat_comm_comment = htmlentities($review_cat_comm_comment);
			$review_cat_hrules_comment = htmlentities($review_cat_hrules_comment);
		}

		// make the request to e4jConnect
		$api_key = VikChannelManager::getApiKey(true);
		
		$e4jc_url = "https://slave.e4jconnect.com/channelmanager/?r=htgr&c=" . $channel['name'];
		
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager HTGR Request e4jConnect.com - Vik Channel Manager -->
<HostToGuestReviewRQ xmlns="http://www.e4jconnect.com/schemas/htgrrq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . $api_key . '"/>
	<Fetch hotelid="' . $account_key . '"/>
	<HostReview otaresid="' . $reservation['idorderota'] . '">
		<Public><![CDATA[' . $public_review . ']]></Public>
		<Private><![CDATA[' . $private_review . ']]></Private>
		<Ratings>
			<Rating category="cleanliness" score="' . $review_cat_clean . '"><![CDATA[' . $review_cat_clean_comment . ']]></Rating>
			<Rating category="communication" score="' . $review_cat_comm . '"><![CDATA[' . $review_cat_comm_comment . ']]></Rating>
			<Rating category="respect_house_rules" score="' . $review_cat_hrules . '"><![CDATA[' . $review_cat_hrules_comment . ']]></Rating>
			<Rating category="host_again" score="' . $review_host_again . '" />
		</Ratings>
	</HostReview>
</HostToGuestReviewRQ>';
		
		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		$e4jC->slaveEnabled = true;
		$rs = $e4jC->exec();
		if ($e4jC->getErrorNo()) {
			VikError::raiseWarning('', 'Request error: ' . VikChannelManager::getErrorFromMap($e4jC->getErrorMsg()));
			$app->redirect("index.php?option=com_vikchannelmanager&task=hostguestreview&cid[]=" . $reservation['id'] . (!empty($rq_tmpl) ? '&tmpl=' . $rq_tmpl : ''));
			exit;
		} elseif (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			VikError::raiseWarning('', 'Response error: ' . VikChannelManager::getErrorFromMap($rs));
			$app->redirect("index.php?option=com_vikchannelmanager&task=hostguestreview&cid[]=" . $reservation['id'] . (!empty($rq_tmpl) ? '&tmpl=' . $rq_tmpl : ''));
			exit;
		}

		/**
		 * Response was successful, go back to the View that will try to dismiss
		 * the modal and/or redirect. Store a log in the history of VBO first.
		 */
		$say_channel_name = $channel['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI ? 'Airbnb' : ucfirst($channel['name']);
		
		// try to update the VBO Booking History
		try {
			if (!class_exists('VikBooking')) {
				require_once VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'lib.vikbooking.php';
			}
			if (method_exists('VikBooking', 'getBookingHistoryInstance')) {
				VikBooking::getBookingHistoryInstance()->setBid($reservation['id'])->store('CM', $say_channel_name . ' - ' . JText::_('VCM_HOST_TO_GUEST_REVIEW'));
			}
		} catch (Exception $e) {
			// do nothing
		}

		// insert record in VCM so that the system will detect that a review was left already
		$transient_name = 'host_to_guest_review_' . $channel['uniquekey'] . '_' . $reservation['id'];
		// build host review object with some basic details
		$host_review_object = new stdClass;
		$host_review_object->public_review = $public_review;
		$host_review_object->private_review = $private_review;
		$host_review_object->review_cat_clean = $review_cat_clean;
		$host_review_object->review_cat_comm = $review_cat_comm;
		$host_review_object->review_cat_hrules = $review_cat_hrules;
		// store record
		$q = "INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES(" . $dbo->quote($transient_name) . ", " . $dbo->quote(json_encode($host_review_object)) . ");";
		$dbo->setQuery($q);
		$dbo->execute();

		// set success message and redirect
		$app->enqueueMessage(JText::_('MSG_BASE_SUCCESS'));
		$app->redirect("index.php?option=com_vikchannelmanager&task=hostguestreview&success=1&cid[]=" . $reservation['id'] . (!empty($rq_tmpl) ? '&tmpl=' . $rq_tmpl : ''));
		exit;
	}

	/**
	 * Airbnb Contents API.
	 * 
	 * @since 	1.8.0
	 */
	public function airbnblistings() {
		VCM::printMenu();

		if (VikChannelManager::isAvailabilityRequest()) {
			VikRequest::setVar('view', VikRequest::getCmd('view', 'airbnblistings'));
		} else {
			VikRequest::setVar('view', VikRequest::getCmd('view', 'dashboard'));
		}

		parent::display();

		VCM::printFooter();
	}

	/**
	 * Airbnb Contents API.
	 * 
	 * @since 	1.8.0
	 */
	public function airbnbmnglisting() {
		VCM::printMenu();

		if (VikChannelManager::isAvailabilityRequest()) {
			VikRequest::setVar('view', VikRequest::getCmd('view', 'airbnbmnglisting'));
		} else {
			VikRequest::setVar('view', VikRequest::getCmd('view', 'dashboard'));
		}

		parent::display();

		VCM::printFooter();
	}

	/**
	 * @since 	1.8.0
	 */
	public function cancel_airbnbmnglisting() {
		JFactory::getApplication()->redirect("index.php?option=com_vikchannelmanager&task=airbnblistings");
		exit;
	}

	/**
	 * VikBooking may redirect to or display this view to cancel an active OTA booking
	 * and to provide the necessary cancellation reasons to perform a request to e4jConnect.
	 * 
	 * @since 	1.8.0
	 */
	public function cancelreservation() {
		$rq_tmpl = VikRequest::getString('tmpl', '', 'request');
		if (VikChannelManager::isAvailabilityRequest()) {
			if ($rq_tmpl != 'component') {
				VCM::printMenu();
			}
			VikRequest::setVar('view', VikRequest::getCmd('view', 'cancelreservation'));
		} else {
			VikRequest::setVar('view', VikRequest::getCmd('view', 'dashboard'));
		}

		parent::display();
		
		if ($rq_tmpl != 'component') {
			VCM::printFooter();
		}
	}

	/**
	 * @since 	1.8.0
	 */
	public function doCancelReservation() {
		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();

		$vbo_oid = VikRequest::getInt('vbo_oid', 0, 'request');
		$rq_tmpl = VikRequest::getString('tmpl', '', 'request');
		$canc_reason = VikRequest::getString('canc_reason', '', 'request');
		$canc_guest_mess = VikRequest::getString('canc_guest_mess', '', 'request');
		$canc_ota_mess = VikRequest::getString('canc_ota_mess', '', 'request');

		if (empty($vbo_oid)) {
			VikError::raiseWarning('', 'Missing booking ID for cancellation');
			$app->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}

		$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id`=" . (int)$vbo_oid . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			VikError::raiseWarning('', 'Booking ID not found for cancellation');
			$app->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}
		$reservation = $dbo->loadAssoc();

		if (!VikChannelManager::cancelActiveOtaReservation($reservation)) {
			// just let VBO remove the booking as ota cancellation is not supported
			VikError::raiseWarning('', 'Booking does not support cancellation reasons and so it can just be cancelled');
			$app->redirect("index.php?option=com_vikbooking&task=editorder&cid[]=" . $reservation['id']);
			exit;
		}

		$channel = VikChannelManager::getChannel(VikChannelManagerConfig::AIRBNBAPI);
		if (!is_array($channel) || !count($channel)) {
			VikError::raiseWarning('', 'No valid channels available to communicate the cancellation of the reservation, so it can just be cancelled');
			$app->redirect("index.php?option=com_vikbooking&task=editorder&cid[]=" . $reservation['id']);
			exit;
		}

		// find the mapping information for the room(s) booked
		$account_key = null;
		$q = "SELECT `or`.`idroom`, `x`.`idroomota`, `x`.`prop_params` FROM `#__vikbooking_ordersrooms` AS `or` LEFT JOIN `#__vikchannelmanager_roomsxref` AS `x` ON `or`.`idroom`=`x`.`idroomvb` WHERE `or`.`idorder`=" . $reservation['id'] . " AND `x`.`idchannel`=" . (int)$channel['uniquekey'] . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			$rooms_assoc = $dbo->loadAssocList();
			foreach ($rooms_assoc as $rassoc) {
				if (empty($rassoc['prop_params'])) {
					continue;
				}
				$account_data = json_decode($rassoc['prop_params'], true);
				if (is_array($account_data) && count($account_data)) {
					foreach ($account_data as $acc_val) {
						// we grab the first param value
						if (!empty($acc_val)) {
							$account_key = $acc_val;
							break 2;
						}
					}
				}
			}
		}
		if (empty($account_key)) {
			// the account credentials must be present to perform the request
			VikError::raiseWarning('', 'Could not find the channel account params');
			$app->redirect("index.php?option=com_vikbooking&task=editorder&cid[]=" . $reservation['id']);
			exit;
		}

		// make sure some reasons were provided
		if (empty($canc_reason)) {
			VikError::raiseWarning('', 'Please provide some reasons for the cancellation');
			$app->redirect("index.php?option=com_vikchannelmanager&task=cancelreservation&vbo_oid=" . $reservation['id'] . (!empty($rq_tmpl) ? '&tmpl=' . $rq_tmpl : ''));
			exit;
		}

		// sanitize values for XML request
		if (defined('ENT_XML1')) {
			// only available from PHP 5.4 and on
			$canc_reason = htmlspecialchars($canc_reason, ENT_XML1 | ENT_COMPAT, 'UTF-8');
			$canc_guest_mess = htmlspecialchars($canc_guest_mess, ENT_XML1 | ENT_COMPAT, 'UTF-8');
			$canc_ota_mess = htmlspecialchars($canc_ota_mess, ENT_XML1 | ENT_COMPAT, 'UTF-8');
		} else {
			// fallback to plain all html entities
			$canc_reason = htmlentities($canc_reason);
			$canc_guest_mess = htmlentities($canc_guest_mess);
			$canc_ota_mess = htmlentities($canc_ota_mess);
		}

		// make the request to e4jConnect
		$api_key = VikChannelManager::getApiKey(true);
		
		$e4jc_url = "https://slave.e4jconnect.com/channelmanager/?r=cancactr&c=" . $channel['name'];
		
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager CANCACTR Request e4jConnect.com - Vik Channel Manager -->
<ActionPendingReservationRQ xmlns="http://www.e4jconnect.com/schemas/actprrq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . $api_key . '"/>
	<Fetch hotelid="' . $account_key . '"/>
	<Action type="cancel" otaresid="' . $reservation['idorderota'] . '">
		<Reason><![CDATA[' . $canc_reason . ']]></Reason>
		<GuestMessage><![CDATA[' . $canc_guest_mess . ']]></GuestMessage>
		<OtaMessage><![CDATA[' . $canc_ota_mess . ']]></OtaMessage>
	</Action>
</ActionPendingReservationRQ>';
			
		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		$e4jC->slaveEnabled = true;
		$rs = $e4jC->exec();
		if ($e4jC->getErrorNo()) {
			VikError::raiseWarning('', 'Request error: ' . VikChannelManager::getErrorFromMap($e4jC->getErrorMsg()));
			$app->redirect("index.php?option=com_vikchannelmanager&task=cancelreservation&vbo_oid=" . $reservation['id'] . (!empty($rq_tmpl) ? '&tmpl=' . $rq_tmpl : ''));
			exit;
		} elseif (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			VikError::raiseWarning('', 'Response error: ' . VikChannelManager::getErrorFromMap($rs));
			$app->redirect("index.php?option=com_vikchannelmanager&task=cancelreservation&vbo_oid=" . $reservation['id'] . (!empty($rq_tmpl) ? '&tmpl=' . $rq_tmpl : ''));
			exit;
		}

		/**
		 * Response was successful, go back to the cancel reservation View that will try
		 * to dismiss the modal and/or redirect. Store a log in the history of VBO first.
		 */
		$say_channel_name = $channel['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI ? 'Airbnb' : ucfirst($channel['name']);
		$reasons_vals = array();
		if (!empty($canc_reason)) {
			array_push($reasons_vals, $canc_reason);
		}
		if (!empty($canc_guest_mess)) {
			array_push($reasons_vals, $canc_guest_mess);
		}
		if (!empty($canc_ota_mess)) {
			array_push($reasons_vals, $canc_ota_mess);
		}

		// try to update the VBO Booking History
		try {
			if (!class_exists('VikBooking')) {
				require_once VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'lib.vikbooking.php';
			}
			if (method_exists('VikBooking', 'getBookingHistoryInstance')) {
				VikBooking::getBookingHistoryInstance()->setBid($reservation['id'])->store('CM', $say_channel_name . ' - ' . JText::_('VCM_CANCACTIVE_BOOKING_TITLE') . ":\n" . implode("\n", $reasons_vals));
			}
		} catch (Exception $e) {
			// do nothing
		}

		// set success message and redirect
		$app->enqueueMessage(JText::_('MSG_BASE_SUCCESS'));
		$app->redirect("index.php?option=com_vikchannelmanager&task=cancelreservation&cancel_success=1&vbo_oid=" . $reservation['id'] . (!empty($rq_tmpl) ? '&tmpl=' . $rq_tmpl : ''));
		exit;
	}

	/**
	 * Migration tool from the old and deprecated verion of Airbnb iCal
	 * to the new API version of May 2021. Enables Airbnb API and/or
	 * removes the iCal version and its related calendars.
	 * 
	 * @since 	1.8.0
	 */
	public function vcm_airbnb_upgrade()
	{
		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();
		
		$api_key = VikChannelManager::getApiKey(true);

		// get current status
		$airbnb_status = VikChannelManager::hasDeprecatedAirbnbVersion();

		if ($airbnb_status === false) {
			// nothing should be done
			VikError::raiseWarning('', 'No actions are needed to migrate to the API version of Airbnb');
			$app->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}

		if ($airbnb_status === true) {
			// activate Airbnb API
			
			// make the request to activate the new channel on e4jConnect
			$e4jc_url = "https://e4jconnect.com/channelmanager/?r=airmigr&c=generic";
			$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager AIRMIGR Request e4jConnect.com - Vik Channel Manager -->
<ChannelsRQ xmlns="http://www.e4jconnect.com/schemas/charq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . $api_key . '"/>
	<Fetch question="activate" channel="' . VikChannelManagerConfig::AIRBNBAPI . '"/>
</ChannelsRQ>';

			$e4jC = new E4jConnectRequest($e4jc_url);
			$e4jC->setPostFields($xml);
			$e4jC->slaveEnabled = true;
			$rs = $e4jC->exec();
			if ($e4jC->getErrorNo()) {
				VikError::raiseWarning('', VikChannelManager::getErrorFromMap($e4jC->getErrorMsg()));
				$app->redirect("index.php?option=com_vikchannelmanager");
				exit;
			} elseif (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
				VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
				$app->redirect("index.php?option=com_vikchannelmanager");
				exit;
			}

			// attempt to decode the response with the channel information
			$channel = json_decode($rs, true);
			if (!is_array($channel) || !count($channel)) {
				VikError::raiseWarning('', 'Could not decode the JSON response');
				$app->redirect("index.php?option=com_vikchannelmanager");
				exit;
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
				exit;
			}

			// add success message and redirect to the page that will set the channel as active
			$app->enqueueMessage(JText::_('MSG_BASE_SUCCESS') . '!');
			$app->redirect("index.php?option=com_vikchannelmanager&task=setmodule&id=" . $new_channel->id);
			exit;
		}

		// status is -1: delete Airbnb iCal and related calendars

		$new_channel = VikChannelManager::getChannel(VikChannelManagerConfig::AIRBNBAPI);
		if (!is_array($new_channel) || !count($new_channel)) {
			VikError::raiseWarning('', 'Channel Airbnb API not found');
			$app->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}

		// make the request to de-activate the old channel on e4jConnect
		$e4jc_url = "https://e4jconnect.com/channelmanager/?r=airmigr&c=generic";
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager AIRMIGR Request e4jConnect.com - Vik Channel Manager -->
<ChannelsRQ xmlns="http://www.e4jconnect.com/schemas/charq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . $api_key . '"/>
	<Fetch question="deactivate" channel="' . VikChannelManagerConfig::AIRBNB . '"/>
</ChannelsRQ>';

		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		$e4jC->slaveEnabled = true;
		$rs = $e4jC->exec();
		if ($e4jC->getErrorNo()) {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($e4jC->getErrorMsg()));
			$app->redirect("index.php?option=com_vikchannelmanager");
			exit;
		} elseif (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
			$app->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}

		// delete channel locally
		$q = "DELETE FROM `#__vikchannelmanager_channel` WHERE `uniquekey`=" . $dbo->quote(VikChannelManagerConfig::AIRBNB) . ";";
		$dbo->setQuery($q);
		$dbo->execute();

		// delete any old iCal calendar associated
		$q = "DELETE FROM `#__vikchannelmanager_listings` WHERE `channel`=" . $dbo->quote(VikChannelManagerConfig::AIRBNB) . ";";
		$dbo->setQuery($q);
		$dbo->execute();

		// add success message and redirect to the page that will set the channel as active
		$app->enqueueMessage(JText::_('MSG_BASE_SUCCESS') . '!');
		$app->redirect("index.php?option=com_vikchannelmanager&task=setmodule&id=" . $new_channel['id']);
		exit;
	}

	/**
	 * Recovery tools to re-import the active bookings or to re-download
	 * a specific OTA reservation ID from some channels.
	 * 
	 * @since 	1.8.0
	 */
	public function recovery_tools()
	{
		$app = JFactory::getApplication();
		$session = JFactory::getSession();

		$mode = VikRequest::getString('mode', '', 'request');
		$otaresid = VikRequest::getString('otaresid', '', 'request');
		$accountid = VikRequest::getString('accountid', '', 'request');

		// prevent abuses
		$max_count = 5;
		$recovery_count = (int)$session->get('vcmRecoveryCount', 0);
		$session->set('vcmRecoveryCount', ++$recovery_count);
		if ($recovery_count > $max_count) {
			VikError::raiseWarning('', 'The recovery tools should not be used more than ' . $max_count . ' times per session. You should not have the need to use them so many times.');
			$app->redirect('index.php?option=com_vikchannelmanager&task=config');
			exit;
		}

		if ($mode == 'first_summary') {
			// redirect to the first summary task that will redirect back to the page Settings
			$app->redirect('index.php?option=com_vikchannelmanager&task=first_summary&imp=1');
			exit;
		}

		if ($mode == 'retransmit_otabooking') {
			if (empty($otaresid)) {
				VikError::raiseWarning('', 'Missing OTA Reservation ID');
				$app->redirect('index.php?option=com_vikchannelmanager&task=config');
				exit;
			}

			// get currently active channel
			$channel = VikChannelManager::getActiveModule(true);
			$params = json_decode($channel['params'], true);
			$params = is_array($params) ? $params : array();
			if (empty($accountid)) {
				// we always pass the current account id along with the request
				if (isset($acc_info['hotelid'])) {
					$accountid = $acc_info['hotelid'];
				} elseif (isset($acc_info['id'])) {
					// useful for Pitchup.com to identify multiple accounts
					$accountid = $acc_info['id'];
				} elseif (isset($acc_info['property_id'])) {
					// useful for Hostelworld
					$accountid = $acc_info['property_id'];
				} elseif (isset($acc_info['user_id'])) {
					// useful for Airbnb API
					$accountid = $acc_info['user_id'];
				}
			}

			// make the request to e4jConnect
			$api_key = VikChannelManager::getApiKey(true);
			
			$e4jc_url = "https://e4jconnect.com/channelmanager/?r=fsum&c=generic";
			$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager FSUM Request e4jConnect.com - VikBooking -->
<FirstSummaryRQ xmlns="http://www.e4jconnect.com/schemas/fsumrq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . $api_key . '"/>
	<Fetch ukey="' . $channel['uniquekey'] . '" otaresid="' . htmlentities($otaresid) . '" accountid="' . htmlentities($accountid) . '" />
</FirstSummaryRQ>';

			$e4jC = new E4jConnectRequest($e4jc_url);
			$e4jC->setPostFields($xml);
			$e4jC->slaveEnabled = true;
			$rs = $e4jC->exec();
			if ($e4jC->getErrorNo()) {
				VikError::raiseWarning('', VikChannelManager::getErrorFromMap($e4jC->getErrorMsg()));
			} elseif (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
				VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
			} else {
				// success
				$app->enqueueMessage(JText::_('VCM_DOWNOTABOOK_SENT'));
			}

			// redirect to the page settings
			$app->redirect('index.php?option=com_vikchannelmanager&task=config');
			exit;
		}

		// nothing needs to be done if this point is reached
		$app->redirect('index.php?option=com_vikchannelmanager&task=config');
		exit;
	}

	/**
	 * AJAX endpoint to exchange rates between currencies.
	 * 
	 * @since 	1.8.3
	 */
	public function currency_conversion()
	{
		$from_currency = VikRequest::getString('from_currency', '', 'request');
		$to_currency   = VikRequest::getString('to_currency', '', 'request');
		$rates 		   = VikRequest::getVar('rates', array(), 'request', 'array');

		if (empty($from_currency) || empty($to_currency)) {
			throw new Exception("Missing currency data for conversion.", 400);
		}

		// invoke object
		$converter = VikChannelManager::getCurrencyConverterInstance()->setFromCurrency($from_currency)->setToCurrency($to_currency);

		// make the request to obtain the most important information first (exchange rate)
		$result = $converter->calcExchangeRate();
		if (!$result || $converter->hasError()) {
			throw new Exception(sprintf("Errors occurred while converting: %s", $converter->getError()), 400);
		}

		// grab an example of a conversion of rates
		$converted_rates = $converter->exchangeRates($rates);
		$converted_rates = !$converted_rates ? $converter->getError() : $converted_rates;

		// compose the response (we've got for sure the exchange rate)
		$response = new stdClass;
		$response->exchange_rate    = $result;
		$response->converted_rates  = $converted_rates;
		$response->pcent_alteration = $converter->calcPercentAlteration(3);

		// send JSON object to output
		echo json_encode($response);
		exit;
	}

	/**
	 * AJAX endpoint to exclude one date from the Smart Balancer
	 * rules of type availability - block dates.
	 * 
	 * @since 	1.8.3
	 */
	public function smart_balancer_exclude_day()
	{
		$exclude_dt = VikRequest::getString('exclude_dt', '', 'request');
		if (empty($exclude_dt)) {
			echo 'e4j.error.1';
			exit;
		}

		$smart_balancer = VikChannelManager::getSmartBalancerInstance();
		$res = $smart_balancer->excludeDateFromBlockDates($exclude_dt);

		if (!$res) {
			echo 'e4j.error.2';
			exit;
		}

		echo 'e4j.ok';
		exit;
	}

	/**
	 * Helper task to clean up invalid listing-rate-plan relations.
	 * Useful for example to ensure Airbnb listings are only assigned to the
	 * "Standard" rate plan rather than also to a "Non Refundable" rate plan.
	 * The provided request values will be used to remove the given rate plan
	 * ID from the given channel ID (unique key).
	 * 
	 * @since 	1.8.28
	 */
	public function normalize_bulk_rates_cache()
	{
		$input = JFactory::getApplication()->input;

		// mandatory request values for the normalization
		$rplan_id   = $input->getInt('rplan_id', 0);
		$channel_id = $input->getInt('channel_id', 0);

		// get current bulk rates cache
		$bulk_rates_cache = VCMFactory::getConfig()->getArray('bulkratescache', []);

		// log the bulk rates cache before any update operation
		echo 'Before<pre>'.print_r($bulk_rates_cache, true).'</pre><br/>';

		if (!$rplan_id || !$channel_id) {
			exit('Provide the information for rplan_id and channel_id to start the normalization');
		}

		$normalized = 0;

		foreach ($bulk_rates_cache as $room_id => $room_data) {
			foreach ($room_data as $price_id => $bulk_data) {
				if ($bulk_data['pricetype'] != $rplan_id) {
					continue;
				}
				if (empty($bulk_data['channels']) || !in_array($channel_id, $bulk_data['channels'])) {
					continue;
				}
				// this is the rate plan and channel we want to delete
				unset(
					// rate plans relations
					$bulk_rates_cache[$room_id][$price_id]['rplans'][$channel_id],
					$bulk_data['rplans'][$channel_id],
					// currency information
					$bulk_rates_cache[$room_id][$price_id]['cur_rplans'][$channel_id],
					$bulk_data['cur_rplans'][$channel_id],
					// ARI mode
					$bulk_rates_cache[$room_id][$price_id]['rplanarimode'][$channel_id],
					$bulk_data['rplanarimode'][$channel_id]
				);

				$channel_key = array_search($channel_id, $bulk_data['channels']);
				if ($channel_key !== false) {
					// unset channel relation
					unset(
						$bulk_rates_cache[$room_id][$price_id]['channels'][$channel_key],
						$bulk_data['channels'][$channel_key]
					);
				}

				// increase counter
				$normalized++;

				if (!$bulk_data['rplans']) {
					// we have deleted all OTA rate plans, hence all channels, so we delete the whole data
					unset($bulk_rates_cache[$room_id][$price_id]);
				}

				if (!$bulk_rates_cache[$room_id]) {
					// no more rate plan bulk data for this listing
					unset($bulk_rates_cache[$room_id]);
					continue 2;
				}
			}
		}

		echo 'Normalized: ' . $normalized . '<br/>';

		// update value on DB
		VCMFactory::getConfig()->set('bulkratescache', $bulk_rates_cache);

		// log the bulk rates cache after the update operation
		echo 'After<pre>'.print_r($bulk_rates_cache, true).'</pre><br/>';
	}
}
