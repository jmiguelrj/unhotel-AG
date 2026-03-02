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

class SmartBalancer
{
	protected $dbo;
	protected $exec_logs;
	public $debug_output;

	public function __construct()
	{
		$this->dbo = JFactory::getDbo();
		$this->exec_logs = array();
		$this->debug_output = false;
	}

	/**
	 * Returns a list of dates blocked on the various channels by some rules.
	 * 
	 * @param 	mixed 	$rooms 	int for one room, array for multiple rooms, null for all.
	 * 
	 * @return 	array 	associative array of dates blocked and related rules.
	 * 
	 * @since 	1.8.3
	 */
	public function getFutureBlockDates($rooms = null)
	{
		$filter_rooms = array();
		if (is_array($rooms) && count($rooms)) {
			$filter_rooms = $rooms;
		} elseif (!empty($rooms) && is_scalar($rooms)) {
			$filter_rooms[] = (int)$rooms;
		}

		$dbo = JFactory::getDbo();

		$clauses = array(
			"`sbr`.`type`='av'",
			"`sbr`.`to_ts` > " . time(),
		);

		if (count($filter_rooms)) {
			$clauses[] = "`sbrr`.`room_id` IN (" . implode(', ', $filter_rooms) . ")";
		}

		$q = "SELECT `sbr`.`id`, `sbr`.`name`, `sbr`.`type`, `sbr`.`from_ts`, `sbr`.`to_ts`, `sbr`.`rule`, 
			(SELECT GROUP_CONCAT(`sbrr`.`room_id` SEPARATOR ';') FROM `#__vikchannelmanager_balancer_rooms` AS `sbrr` WHERE `sbrr`.`rule_id`=`sbr`.`id`) AS `idrooms` 
			FROM `#__vikchannelmanager_balancer_rules` AS `sbr` 
			LEFT JOIN `#__vikchannelmanager_balancer_rooms` AS `sbrr` ON `sbrr`.`rule_id`=`sbr`.`id` 
			WHERE " . implode(' AND ', $clauses) . " ORDER BY `sbr`.`from_ts` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			// no future availability rules found
			return array();
		}
		$rules = $dbo->loadAssocList();

		// make sure the type of the rule is "block dates" and prepare values
		foreach ($rules as $k => $r) {
			// decode rule instructions
			$r['rule'] = json_decode($r['rule']);
			if (!is_object($r['rule'])) {
				// invalid rule
				unset($rules[$k]);
				continue;
			}

			// must be a "block-date" rule
			if (!isset($r['rule']->type) || $r['rule']->type != 'block') {
				// not what we need
				unset($rules[$k]);
				continue;
			}

			// check if dates are valid
			if (empty($r['from_ts']) || empty($r['to_ts']) || $r['from_ts'] > $r['to_ts']) {
				// invalid dates
				unset($rules[$k]);
				continue;
			}

			// make the list of affected rooms an array
			if (!empty($r['idrooms'])) {
				$r['idrooms'] = array_filter(explode(';', $r['idrooms']));
			} else {
				$r['idrooms'] = array();
			}

			// make sure the list of week days is an array, even empty
			if (!isset($r['rule']->wdays) || !is_array($r['rule']->wdays)) {
				$r['rule']->wdays = array();
			}

			// make sure the list of excluded dates is an array
			if (!isset($r['rule']->excl_dates) || !is_array($r['rule']->excl_dates)) {
				$r['rule']->excl_dates = array();
			}

			// update record
			$rules[$k] = $r;
		}

		if (!count($rules)) {
			// no valid and active rules found of type availability block dates
			return array();
		}

		// now build the associative array of date-inforule list
		$block_dates = array();

		foreach ($rules as $k => $r) {
			$info_from = getdate($r['from_ts']);
			while ($info_from[0] <= $r['to_ts']) {
				// get the Y-m-d version of this day
				$day_ymd = date('Y-m-d', $info_from[0]);

				// check if this day is truly blocked
				$is_blocked = true;

				// make sure this week day is not excluded
				if (count($r['rule']->wdays) && count($r['rule']->wdays) < 7) {
					// rule is just for some week days, check if this date is affected
					if (!in_array((int)$info_from['wday'], $r['rule']->wdays)) {
						// skip this date
						$is_blocked = false;
					}
				}

				// make sure this day is not excluded
				if (in_array($day_ymd, $r['rule']->excl_dates)) {
					$is_blocked = false;
				}

				if ($is_blocked) {
					// set initial properties for this blocked day
					if (!isset($block_dates[$day_ymd])) {
						$block_dates[$day_ymd] = array(
							'rule_ids' 	 => array(),
							'rule_names' => array(),
							'room_ids' 	 => array(),
						);
					}

					// push/merge values
					if (!in_array($r['id'], $block_dates[$day_ymd]['rule_ids'])) {
						array_push($block_dates[$day_ymd]['rule_ids'], $r['id']);
						array_push($block_dates[$day_ymd]['rule_names'], $r['name']);
					}
					$block_dates[$day_ymd]['room_ids'] = array_unique(array_merge($block_dates[$day_ymd]['room_ids'], $r['idrooms']));
				}

				// go to next day
				$info_from = getdate(mktime(0, 0, 0, $info_from['mon'], ($info_from['mday'] + 1), $info_from['year']));
			}
		}

		// sort dates by key ascending
		ksort($block_dates);

		return $block_dates;
	}

	/**
	 * Adds one excluded date to all rules of type availability block dates.
	 * 
	 * @param 	string 	$dt 	the date to exclude in Y-m-d format.
	 * 
	 * @return 	bool 			true on success or false.
	 * 
	 * @since 	1.8.3
	 */
	public function excludeDateFromBlockDates($dt)
	{
		$dbo = JFactory::getDbo();

		$day_ts = strtotime($dt);

		if (empty($dt) || !$day_ts) {
			return false;
		}

		$clauses = array(
			"`sbr`.`type`='av'",
			"`sbr`.`from_ts` <= " . $day_ts,
			"`sbr`.`to_ts` >= " . $day_ts,
		);

		$q = "SELECT `sbr`.`id`, `sbr`.`name`, `sbr`.`type`, `sbr`.`from_ts`, `sbr`.`to_ts`, `sbr`.`rule`
			FROM `#__vikchannelmanager_balancer_rules` AS `sbr` 
			WHERE " . implode(' AND ', $clauses) . " ORDER BY `sbr`.`from_ts` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			// no rules including this date were found
			return false;
		}
		$rules = $dbo->loadAssocList();

		// parse all rules
		foreach ($rules as $k => $r) {
			// decode rule instructions
			$r['rule'] = json_decode($r['rule']);
			if (!is_object($r['rule'])) {
				// invalid rule
				unset($rules[$k]);
				continue;
			}

			// must be a "block-date" rule
			if (!isset($r['rule']->type) || $r['rule']->type != 'block') {
				// not what we need
				unset($rules[$k]);
				continue;
			}

			// make sure the list of excluded dates is an array
			if (!isset($r['rule']->excl_dates) || !is_array($r['rule']->excl_dates)) {
				$r['rule']->excl_dates = array();
			}

			// push this day to exclude, if not present already
			if (!in_array($dt, $r['rule']->excl_dates)) {
				array_push($r['rule']->excl_dates, $dt);
			}

			// update record
			$rules[$k] = $r;
		}

		if (!count($rules)) {
			// no suitable records found
			return false;
		}

		// update rules on DB
		$updated = false;
		foreach ($rules as $k => $r) {
			// encode back the rule instructions
			$updated_rule = json_encode($r['rule']);

			// build the object to update
			$record = new stdClass;
			$record->id = $r['id'];
			$record->rule = $updated_rule;

			$updated = $dbo->updateObject('#__vikchannelmanager_balancer_rules', $record, 'id') || $updated;
		}

		return $updated;
	}

	/**
	 * This (main) method is called by the SynchVikBooking class
	 * whenever the system needs to compose an XML for the A_RQ
	 * to update the availability on the various OTAs.
	 * Here the Smart Balancer checks if some rooms should be closed
	 * on the OTAs for these dates, depending on the rules configured.
	 * (Rule Type = av)
	 * 
	 * @param 		$rooms 		array 	([adates] => Array([2017-12-11] => Array([newavail] => 1),[2017-12-12] => Array([newavail] => 3)))
	 * @param 		$booking 	array 	([id] => 555, [days] => 2,[checkin] => 1512993600,[checkout] => 1513159200 .....)
	 *
	 * @return 		$rooms 		array
	 **/
	public function applyAvailabilityRulesOnSync($rooms, $booking)
	{
		$rules_applied 	= false;
		$all_rules 		= array();
		
		//get booked rooms
		$room_ids = array();
		foreach ($rooms as $room) {
			if (isset($room['idroom']) && !in_array($room['idroom'], $room_ids) && isset($room['adates'])) {
				array_push($room_ids, $room['idroom']);
			}
		}
		if (!count($room_ids)) {
			return $this->cleanAvailabilityExcludedRooms($rooms);
		}
		//adjust check-in/out times at midnight
		$info_in = getdate($booking['checkin']);
		$info_out = getdate($booking['checkout']);
		$booking['checkin'] = mktime(0, 0, 0, $info_in['mon'], $info_in['mday'], $info_in['year']);
		$booking['checkout'] = mktime(0, 0, 0, $info_out['mon'], $info_out['mday'], $info_out['year']);
		//

		$q = "SELECT `ru`.*, GROUP_CONCAT(`rr`.`room_id` SEPARATOR ',') AS `room_ids` 
			FROM `#__vikchannelmanager_balancer_rules` AS `ru` LEFT JOIN `#__vikchannelmanager_balancer_rooms` AS `rr` ON `ru`.`id`=`rr`.`rule_id` 
			WHERE `rr`.`room_id`".(count($room_ids) > 1 ? ' IN ('.implode(', ', $room_ids).')' : '='.(int)$room_ids[0])." AND `ru`.`type`='av' AND (
				(`ru`.`from_ts` <= ".$booking['checkin']." AND `ru`.`to_ts` >= ".$booking['checkin'].") OR 
				(`ru`.`from_ts` <= ".$booking['checkout']." AND `ru`.`to_ts` >= ".$booking['checkout'].") OR 
				(`ru`.`from_ts` >= ".$booking['checkin']." AND `ru`.`to_ts` <= ".$booking['checkout'].")
			);";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if ($this->dbo->getNumRows() > 0) {
			$all_rules = $this->dbo->loadAssocList();
			foreach ($all_rules as $k => $rule) {
				$rule['rule'] = json_decode((string) $rule['rule']);
				if (!is_object($rule['rule'])) {
					unset($all_rules[$k]);
				} else {
					$all_rules[$k]['rule'] = $rule['rule'];
					$rule_rids = explode(',', $rule['room_ids']);
					foreach ($rule_rids as $kk => $v) {
						$rule_rids[$kk] = intval($v);
					}
					$all_rules[$k]['room_ids'] = $rule_rids;
				}
			}
		}
		if (!count($all_rules)) {
			return $this->cleanAvailabilityExcludedRooms($rooms);
		}

		foreach ($rooms as $rk => $room) {
			if (!isset($room['idroom']) || !isset($room['adates']) || !is_array($room['adates'])) {
				continue;
			}
			foreach ($all_rules as $k => $rule) {
				$new_adates = $this->applyAvRuleDates($room['idroom'], $room['adates'], $booking['id'], $rule);
				if ($new_adates != $room['adates']) {
					$rooms[$rk]['adates'] = $new_adates;
					$rules_applied = true;
				}
				$this->finalizeRuleExecution($rule);
			}
		}

		return $rules_applied !== false ? $rooms : $this->cleanAvailabilityExcludedRooms($rooms);
	}

	/**
	 * This method attempts to apply the rule over the passed
	 * array of available dates for the given room ID.
	 * If the rule does not need to be applied, then the method
	 * returns the plain array of available dates passed.
	 * Must be called by applyAvailabilityRulesOnSync after
	 * preparing all the variables.
	 *
	 * @param 		$idroom 			int
	 * @param 		$adates 			array
	 * @param 		$idbooking 			int
	 * @param 		$rule 				array
	 *
	 * @return 		$new_adates 		array
	 **/
	protected function applyAvRuleDates($idroom, $adates, $idbooking, $rule)
	{
		$new_adates = $adates;

		if (!in_array((int)$idroom, $rule['room_ids'])) {
			// this room is not affected by this rule
			return $new_adates;
		}

		if ($rule['rule']->type == 'limit') {
			// (type = 'limit') the units should be closed if the remaining units are <= the limit set in the rule
			
			foreach ($adates as $date => $value) {
				// check weekdays rule
				if (isset($rule['rule']->wdays) && count($rule['rule']->wdays) && count($rule['rule']->wdays) < 7) {
					// rule is just for some week days, check if this date is affected
					$info_day = getdate(strtotime($date));
					if (!in_array((int)$info_day['wday'], $rule['rule']->wdays)) {
						// skip this date
						continue;
					}
				}
				//
				if ((int)$value['newavail'] <= (int)$rule['rule']->number) {
					// rule has been applied
					$new_adates[$date]['newavail'] = 0;
					// set execution logs for the rule
					$this->setRuleExecLog($rule, $idroom, $idbooking, 'Closing '.$date.' when '.$value['newavail'].' unit(s) remaining (limit = '.$rule['rule']->number.')');
				}
			}
		} elseif ($rule['rule']->type == 'units') {
			// (type = 'units') the units should be closed if the OTAs have already sold n units on these dates

			$all_dates = array_keys($adates);
			$ota_bookings = $this->getOtaBookingsFromDates((int)$idroom, $all_dates);
			if (count($ota_bookings)) {
				foreach ($adates as $date => $value) {
					// check weekdays rule
					$info_day = getdate(strtotime($date));
					if (isset($rule['rule']->wdays) && count($rule['rule']->wdays) && count($rule['rule']->wdays) < 7) {
						// rule is just for some week days, check if this date is affected
						if (!in_array((int)$info_day['wday'], $rule['rule']->wdays)) {
							// skip this date
							continue;
						}
					}
					//
					$otas_count = 0;
					foreach ($ota_bookings as $bid => $ota_booking) {
						//check the dates of the bookings to see if they include this day and increase the counter
						if ($info_day[0] >= $ota_booking['checkin'] && $info_day[0] < $ota_booking['checkout']) {
							$otas_count += $ota_booking['roomsnum'];
						}
					}
					// check if number of sold rooms by the OTAs exceeds the units limit
					if ((int)$rule['rule']->number > 0 && $otas_count >= (int)$rule['rule']->number) {
						// rule has been applied
						$new_adates[$date]['newavail'] = 0;
						// set execution logs for the rule
						$this->setRuleExecLog($rule, $idroom, $idbooking, 'Closing '.$date.' when '.$value['newavail'].' unit(s) remaining and '.$otas_count.' unit(s) sold by OTAs (limit = '.$rule['rule']->number.')');
					}
				}
			}
		} elseif ($rule['rule']->type == 'block') {
			/**
			 * This new type of availability rule was introduced to always shut down
			 * the availability on the OTAs no matter what. Excluded dates are allowed.
			 * 
			 * @since 	1.8.3
			 */
			$excluded_dates = isset($rule['rule']->excl_dates) && is_array($rule['rule']->excl_dates) ? $rule['rule']->excl_dates : array();

			foreach ($adates as $date => $value) {
				// check weekdays rule
				if (isset($rule['rule']->wdays) && count($rule['rule']->wdays) && count($rule['rule']->wdays) < 7) {
					// rule is just for some week days, check if this date is affected
					$info_day = getdate(strtotime($date));
					if (!in_array((int)$info_day['wday'], $rule['rule']->wdays)) {
						// skip this date
						continue;
					}
				}

				// check if this date is excluded
				if (in_array($date, $excluded_dates)) {
					// skip this date
					continue;
				}

				// rule has been applied
				$new_adates[$date]['newavail'] = 0;

				// set execution logs for the rule
				$this->setRuleExecLog($rule, $idroom, $idbooking, "Closing {$date} when {$value['newavail']} unit(s) remaining (date is blocked)");
			}

		}

		return $new_adates;
	}

	/**
	 * Reads from the database all the bookings received from the OTAs
	 * for the given room and for the passed dates.
	 * Returns an array of bookings with the booked count of this room (roomsnum).
	 *
	 * @param 		$idroom 			int
	 * @param 		$all_dates 			array
	 *
	 * @return 		$ota_bookings 		array
	 **/
	protected function getOtaBookingsFromDates($idroom, $all_dates)
	{
		$ota_bookings = array();
		foreach ($all_dates as $k => $v) {
			$all_dates[$k] = strtotime($v);
		}
		sort($all_dates);
		$earliest_info = getdate($all_dates[0]);
		$latest_info = getdate($all_dates[(count($all_dates) -1)]);
		$earliest_ts = mktime(0, 0, 0, $earliest_info['mon'], $earliest_info['mday'], $earliest_info['year']);
		$latest_ts = mktime(23, 59, 59, $latest_info['mon'], $latest_info['mday'], $latest_info['year']);

		$q = "SELECT `b`.`id`,`b`.`status`,`b`.`days`,`b`.`checkin`,`b`.`checkout`,`b`.`roomsnum`,`b`.`idorderota`,`b`.`channel`,`br`.`idroom` 
			FROM `#__vikbooking_orders` AS `b` LEFT JOIN `#__vikbooking_ordersrooms` AS `br` ON `b`.`id`=`br`.`idorder` 
			WHERE `b`.`status`='confirmed' AND `b`.`idorderota` IS NOT NULL AND `br`.`idroom`=".(int)$idroom." AND (
				(`b`.`checkin` >= ".$earliest_ts." AND `b`.`checkout` <= ".$latest_ts.") OR
				(`b`.`checkin` <= ".$earliest_ts." AND `b`.`checkout` >= ".$earliest_ts.") OR 
				(`b`.`checkin` <= ".$latest_ts." AND `b`.`checkout` >= ".$latest_ts.")
			);";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if ($this->dbo->getNumRows() > 0) {
			$all_ota_bookings = $this->dbo->loadAssocList();
			foreach ($all_ota_bookings as $k => $v) {
				if (empty($v['channel'])) {
					continue;
				}
				if (!isset($ota_bookings[$v['id']])) {
					//set rooms count to 1
					$v['roomsnum'] = 1;
					//set checkout ts to midnight for excluding this day
					$info_checkout = getdate($v['checkout']);
					$v['checkout'] = mktime(0, 0, 0, $info_checkout['mon'], $info_checkout['mday'], $info_checkout['year']);
					//add booking in the array
					$ota_bookings[$v['id']] = $v;
				} else {
					if ($v['idroom'] == (int)$idroom) {
						//increase rooms count for this booking
						$ota_bookings[$v['id']]['roomsnum']++;
					}
				}
			}
		}

		return $ota_bookings;
	}

	/**
	 * This method cleans the rooms that should not be included
	 * in the A_RQ because they belong to the channel from which the booking
	 * was received. The excluded channel was maintained in the array
	 * only to see if there were some active rules for these dates and this room
	 * to close the availability on the OTAs. When we call this method it means
	 * that the originally excluded channel does not need to be updated and
	 * should become 'excluded' again for building the XML.
	 *
	 * @param 		$rooms 		array
	 *
	 * @return 		$rooms 		array
	 **/
	protected function cleanAvailabilityExcludedRooms($rooms)
	{
		foreach ($rooms as $kor => $room) {
			if ($kor == 'vikbooking_order') {
				//we must keep the key containing the information of the booking or the channel manager will not parse the requests.
				continue;
			}
			if (!isset($room['channels']) || !count($room['channels'])) {
				unset($rooms[$kor]);
				continue;
			}
			foreach ($room['channels'] as $k => $v) {
				if (array_key_exists('excluded', $v) && $v['excluded'] > 0) {
					unset($room['channels'][$k]);
				}
			}
			if (!count($room['channels'])) {
				unset($rooms[$kor]);
				continue;
			}
		}
		return count($rooms) ? $rooms : array();
	}

	/**
	 * This (main) method reads all the rules of type rates (Rule Type = rt)
	 * to parse the ones that need to be executed. Rules are then executed
	 * by adding Special Prices (if necessary) and by logging the results.
	 *
	 * First we make a query to fetch all rules (rt) with end date in the future, unless
	 * the ID is not empty. Then we compose an array for each rule, with all the dates that 
	 * should be checked, by unsetting the excluded week-days from the range.
	 * Second, if discount and days in advance > 0, we take only the date of the rule from 
	 * today to the days in advance, without considering all the other dates.
	 * Third, we calculate the remaining availability for every date to see which dates/rooms
	 * should have a modification of the rates, by passing the results to other methods.
	 *
	 * This method is called by the front-end task 'smartbalancer_ratesrules' of the controller, 
	 * which is regularly pinged by the e4jConnect servers, or manually executed for debug.
	 *
	 * @param 		[$id_rule] 		int
	 *
	 * @return 		boolean 		true if some rules were executed, false otherwise.
	 **/
	public function applyRatesAdjustmentsRules($id_rule = 0)
	{
		$q = "SELECT `ru`.*, GROUP_CONCAT(`rr`.`room_id` SEPARATOR ',') AS `room_ids`, GROUP_CONCAT(`ro`.`units` SEPARATOR ',') AS `room_units` 
			FROM `#__vikchannelmanager_balancer_rules` AS `ru` LEFT JOIN `#__vikchannelmanager_balancer_rooms` AS `rr` ON `ru`.`id`=`rr`.`rule_id` LEFT JOIN `#__vikbooking_rooms` AS `ro` ON `ro`.`id`=`rr`.`room_id` 
			WHERE `ru`.`type`='rt' AND `ru`.`to_ts` >= ".time().($id_rule > 0 ? ' AND `ru`.`id`='.(int)$id_rule : '')." GROUP BY `ru`.`id` ORDER BY `ru`.`id` DESC;";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if (!$this->dbo->getNumRows()) {
			return false;
		}
		$all_rules = $this->dbo->loadAssocList();
		
		$now_info = getdate(mktime(0, 0, 0, date('n'), date('j'), date('Y')));
		//compose rooms and dates affected array
		foreach ($all_rules as $k => $rule) {
			$rule['rule'] = json_decode((string) $rule['rule']);
			if (!is_object($rule['rule'])) {
				unset($all_rules[$k]);
				continue;
			}
			$all_rules[$k]['rule'] = $rule['rule'];
			//rooms ids and rooms units
			$rule_rids = explode(',', $rule['room_ids']);
			foreach ($rule_rids as $kk => $v) {
				$rule_rids[$kk] = intval($v);
			}
			$all_rules[$k]['room_ids'] = $rule_rids;
			if (!count($all_rules[$k]['room_ids'])) {
				unset($all_rules[$k]);
			}
			$rule_runits = explode(',', $rule['room_units']);
			foreach ($rule_runits as $kk => $v) {
				$rule_runits[$kk] = intval($v);
			}
			$all_rules[$k]['room_units'] = $rule_runits;
			if (!count($all_rules[$k]['room_units'])) {
				unset($all_rules[$k]);
			}
			//
			//compose dates
			$all_rules[$k]['checkdays'] = array();
			if ($rule['rule']->updown == 'down' && (int)$rule['rule']->daysadv > 0) {
				/**
				 * When setting a number of days in advace greater than zero, we are actually
				 * forcing one single date to be checked. Therefore, it is probably important
				 * to suggest customers to use 0 as minimum days in advance for this type of rule.
				 * This way the ELSE statement below will run and all dates in the range will be taken.
				 * 
				 * @see 	code below
				 */
				//discount rates only n days in advance: check if allowed
				$discdate = mktime(0, 0, 0, $now_info['mon'], ($now_info['mday'] + (int)$rule['rule']->daysadv), $now_info['year']);
				$discdate_info = getdate($discdate);
				if (isset($rule['rule']->wdays) && count($rule['rule']->wdays) && count($rule['rule']->wdays) < 7) {
					// rule is just for some week days, check if this date is affected
					if (!in_array((int)$discdate_info['wday'], $rule['rule']->wdays)) {
						// skip this date, so skip this rule
						unset($all_rules[$k]);
						continue;
					}
				}
				//push the calculated day in the dates affected array
				array_push($all_rules[$k]['checkdays'], date('Y-m-d', $discdate));
			} else {
				//discount (at any time) or increase rates: build the dates affected array
				$check_wdays = (isset($rule['rule']->wdays) && count($rule['rule']->wdays) && count($rule['rule']->wdays) < 7);
				$start_date = getdate($rule['from_ts']);
				while ($start_date[0] <= $rule['to_ts']) {
					if (($check_wdays && !in_array((int)$start_date['wday'], $rule['rule']->wdays)) || $start_date[0] < $now_info[0]) {
						//this day should be skipped, either not affected or in the past
						$start_date = getdate(mktime(0, 0, 0, $start_date['mon'], ($start_date['mday'] + 1), $start_date['year']));
						continue;
					}
					//push the calculated day in the dates affected array
					array_push($all_rules[$k]['checkdays'], date('Y-m-d', $start_date[0]));
					//
					$start_date = getdate(mktime(0, 0, 0, $start_date['mon'], ($start_date['mday'] + 1), $start_date['year']));
				}
			}
			if (!count($all_rules[$k]['checkdays'])) {
				unset($all_rules[$k]);
			}
			//
		}
		//
		
		if (!count($all_rules)) {
			return false;
		}

		//compose rooms remaining availability
		foreach ($all_rules as $k => $rule) {
			$earliest_info 	= getdate(strtotime($rule['checkdays'][0]));
			$latest_info 	= getdate(strtotime($rule['checkdays'][(count($rule['checkdays']) -1)]));
			$earliest_ts 	= mktime(0, 0, 0, $earliest_info['mon'], $earliest_info['mday'], $earliest_info['year']);
			$latest_ts 		= mktime(23, 59, 59, $latest_info['mon'], $latest_info['mday'], $latest_info['year']);
			$busy_records 	= array();
			$q = "SELECT `b`.`id`,`b`.`idroom`,`b`.`checkin`,`b`.`checkout` 
				FROM `#__vikbooking_busy` AS `b` 
				WHERE `b`.`idroom` IN (".implode(',', $rule['room_ids']).") AND (
					(`b`.`checkin` >= ".$earliest_ts." AND `b`.`checkout` <= ".$latest_ts.") OR
					(`b`.`checkin` <= ".$earliest_ts." AND `b`.`checkout` >= ".$earliest_ts.") OR 
					(`b`.`checkin` <= ".$latest_ts." AND `b`.`checkout` >= ".$latest_ts.")
				);";
			$this->dbo->setQuery($q);
			$this->dbo->execute();
			if ($this->dbo->getNumRows() > 0) {
				$busy_records = $this->dbo->loadAssocList();
			}
			$all_rules[$k]['remaining_av'] = array();
			foreach ($rule['room_ids'] as $rind => $rid) {
				$all_rules[$k]['remaining_av'][$rid] = array();
				foreach ($rule['checkdays'] as $cday) {
					$all_rules[$k]['remaining_av'][$rid][$cday] = $rule['room_units'][$rind];
					$midn_ts = strtotime($cday);
					foreach ($busy_records as $bu) {
						if ($bu['idroom'] != $rid) {
							continue;
						}
						$checkin_info = getdate($bu['checkin']);
						$checkout_info = getdate($bu['checkout']);
						$midn_checkin = mktime(0, 0, 0, $checkin_info['mon'], $checkin_info['mday'], $checkin_info['year']);
						$midn_checkout = mktime(0, 0, 0, $checkout_info['mon'], $checkout_info['mday'], $checkout_info['year']);
						if ($midn_ts >= $midn_checkin && $midn_ts < $midn_checkout) {
							$all_rules[$k]['remaining_av'][$rid][$cday]--;
						}
					}
				}
			}
		}
		//structure of the remaining availability array of each rule (idrooms -> dates => availability):
		/*
		[remaining_av] => Array (
            [9] => Array (
                    [2017-05-24] => 3
                    [2017-05-25] => 3
                    [2017-05-26] => 3
                    [2017-05-28] => 3
            )
            [2] => Array (
                    [2017-05-24] => 5
                    [2017-05-25] => 3
                    [2017-05-26] => 2
                    [2017-05-28] => 4
            )
        )
		*/
		//

		if ($this->debug_output) {
			echo 'Debug<br/><pre>'.print_r($all_rules, true).'</pre>'."\n";
		}
		
		//Pass the execution onto the method parseRatesAdjustmentsRules that will check if the remaining availability requires the rules to be applied
		return $this->parseRatesAdjustmentsRules($all_rules);
	}

	/**
	 * This protected method is called by the (main) method that reads all
	 * the rules of type rates (Rule Type = rt). Once the (rt) rules have been
	 * composed with all the information about rooms, dates and availability,
	 * this method is called to parse, apply and log them.
	 * An array of rule-data is composed for each rule, if there are some dates and rooms
	 * that require a rates adjustment. The rule-data array is then passed onto another method.
	 *
	 * @param 		$rules 			array 	rules array built by applyRatesAdjustmentsRules().
	 *
	 * @return 		boolean 		true if some rules were executed, false otherwise.
	 **/
	protected function parseRatesAdjustmentsRules($rules)
	{
		$rules_applied = 0;

		foreach ($rules as $k => $rule) {
			$apply_rule_data = array(
				'rule' 	=> $rule,
				'data' 	=> array()
			);
			if ($rule['rule']->units_count == 'group') {
				//count the units left as a group of all rooms
				foreach ($rule['checkdays'] as $day) {
					//get units left for each day
					$tot_units_left = 0;
					$rooms_modifiable = array();
					foreach ($rule['remaining_av'] as $rid => $dates) {
						$tot_units_left += $dates[$day];
						if ($dates[$day] > 0) {
							//this room has still some units left on this day, so the rates for this room can be modifed
							array_push($rooms_modifiable, $rid);
						}
					}
					if ($rule['rule']->gtlt == 'lt' && count($rooms_modifiable) && $tot_units_left <= $rule['rule']->units) {
						//Adjust the Rates when the number of units left is less than or equal to 'n' units
						$apply_rule_data['data'][$day] = $rooms_modifiable;
					} elseif ($rule['rule']->gtlt == 'gt' && count($rooms_modifiable) && $tot_units_left >= $rule['rule']->units) {
						//Adjust the Rates when the number of units left is greater than or equal to 'n' units
						$apply_rule_data['data'][$day] = $rooms_modifiable;
					}
				}
			} else {
				//count the units left for each room individually
				foreach ($rule['remaining_av'] as $rid => $dates) {
					foreach ($dates as $day => $units) {
						if ($rule['rule']->gtlt == 'lt' && $units > 0 && $units <= $rule['rule']->units) {
							//Adjust the Rates when the number of units left is less than or equal to 'n' units
							if (!isset($apply_rule_data['data'][$day])) {
								$apply_rule_data['data'][$day] = array();
							}
							array_push($apply_rule_data['data'][$day], $rid);
						} elseif ($rule['rule']->gtlt == 'gt' && $units > 0 && $units >= $rule['rule']->units) {
							//Adjust the Rates when the number of units left is greater than or equal to 'n' units
							if (!isset($apply_rule_data['data'][$day])) {
								$apply_rule_data['data'][$day] = array();
							}
							array_push($apply_rule_data['data'][$day], $rid);
						}
					}
				}
			}
			//if the rates can be modified on some dates, we increase the number of rules applied and then we apply the rule
			if (count($apply_rule_data['data'])) {
				if ($this->debug_output) {
					echo '<br/>$apply_rule_data<pre>'.print_r($apply_rule_data, true).'</pre><br/>'."\n";
				}
				//pass the array to applyParsedRatesRule to create the Special Price and add the logs for this rule.
				$rules_applied += $this->applyParsedRatesRule($apply_rule_data);
			}
		}

		return ($rules_applied > 0);
	}

	/**
	 * This protected method returns an array of dates (Y-m-d) by taking
	 * a minimum (from) date and a maximum (to) date in Y-m-d format.
	 * All the dates from the start to the end are returned into an array.
	 *
	 * @param 		$mindate 		string 		date from (Y-m-d)
	 * @param 		$maxdate 		string 		date to (Y-m-d)
	 *
	 * @return 		array 
	 **/
	protected function getDatesRange($mindate, $maxdate)
	{
		$from_ts 		= strtotime($mindate);
		$to_ts 			= strtotime($maxdate);
		$info_from 		= getdate($from_ts);
		$dates_range 	= array();
		
		if (empty($from_ts) || empty($to_ts) || $from_ts > $to_ts) {
			return $dates_range;
		}

		while ($info_from[0] <= $to_ts) {
			array_push($dates_range, date('Y-m-d', $info_from[0]));
			$info_from = getdate(mktime(0, 0, 0, $info_from['mon'], ($info_from['mday'] + 1), $info_from['year']));
		}

		return $dates_range;
	}

	/**
	 * This protected method returns all the dates and rooms that were
	 * modified for a specific rule ID to avoid duplicated actions.
	 * Returns an associative array with key-value pairs of day-room_ids.
	 *
	 * @param 		$rule_id 		integer 	the ID of the rule being parsed
	 * @param 		$mindate 		string 		minimum date (Y-m-d) to be modified
	 * @param 		$maxdate 		string 		maximum date (Y-m-d) to be modified
	 *
	 * @return 		array
	 **/
	protected function loadRuleDayRateLogs($rule_id, $mindate, $maxdate)
	{
		$q = "SELECT * FROM `#__vikchannelmanager_balancer_ratelogs` WHERE `rule_id`=".(int)$rule_id." AND `day` >= ".$this->dbo->quote($mindate)." AND `day` <= ".$this->dbo->quote($maxdate).";";
		$this->dbo->setQuery($q);
		$prev_logs = $this->dbo->loadAssocList();

		if (!$prev_logs) {
			return [];
		}

		$day_logs = [];
		foreach ($prev_logs as $k => $v) {
			$day_logs[$v['day']] = $v['room_ids'];
		}

		return $day_logs;
	}

	/**
	 * This protected method is called every time a rule_data array has been
	 * prepared by the parseRatesAdjustmentsRules method for creation.
	 * This method creates the special prices (if necessary), and adds execution logs.
	 * The Special Prices nodes are composed by parsing the consecutive dates and rooms that can be updated.
	 *
	 * @param 		$rule_data 		array 	rule_data array built by parseRatesAdjustmentsRules().
	 *
	 * @return 		integer 		1 if the rule was applied, 0 otherwise.
	 **/
	protected function applyParsedRatesRule($rule_data)
	{
		$rule_applied = 0;

		if (!$rule_data['data']) {
			return $rule_applied;
		}

		// sort dates and room IDs
		$dates_sorted = array_keys($rule_data['data']);
		sort($dates_sorted);
		foreach ($rule_data['data'] as $day => $rooms) {
			sort($rule_data['data'][$day]);
		}

		// build dates
		$ts_start 	= strtotime($dates_sorted[0]);
		$ts_end 	= strtotime($dates_sorted[(count($dates_sorted) - 1)]);
		$info_day 	= getdate($ts_start);

		// load and check the previous rate-logs to unset the already parsed dates-rooms
		$rule_prev_logs = $this->loadRuleDayRateLogs($rule_data['rule']['id'], $dates_sorted[0], $dates_sorted[(count($dates_sorted) - 1)]);
		foreach ($rule_prev_logs as $day => $rooms_str) {
			if (!isset($rule_data['data'][$day])) {
				continue;
			}
			// this day is about to be modified, check if some rooms were already updated
			foreach ($rule_data['data'][$day] as $ind => $room_id) {
				if (strpos($rooms_str, ';'.$room_id.';') !== false) {
					// this room has already had the rates updated so we unset it
					if ($this->debug_output) {
						echo '<p>Unsetting Room ID '.$room_id.' for '.$day.' (Rule ID '.$rule_data['rule']['id'].') because rates were already modified.</p>'."\n";
					}
					unset($rule_data['data'][$day][$ind]);
				}
			}
			if (!$rule_data['data'][$day]) {
				// no more rooms to update for this day so we unset it
				if ($this->debug_output) {
					echo '<p>No more rooms to update for '.$day.' because rates were already modified. We unset it.</p>'."\n";
				}
				unset($rule_data['data'][$day]);
			}
		}

		if (!$rule_data['data']) {
			return $rule_applied;
		}

		$from_date_node = '';
		$seasons_pool 	= array();

		while ($info_day[0] <= $ts_end) {
			$cur_day 	= date('Y-m-d', $info_day[0]);
			$next_ts 	= mktime(0, 0, 0, $info_day['mon'], ($info_day['mday'] + 1), $info_day['year']);
			$next_day 	= date('Y-m-d', $next_ts);
			if (!isset($rule_data['data'][$cur_day])) {
				$info_day = getdate($next_ts);
				continue;
			}
			if (empty($from_date_node)) {
				$from_date_node = $cur_day;
			}
			if (isset($rule_data['data'][$next_day])) {
				// consecutive day can be updated
				if ($rule_data['data'][$cur_day] != $rule_data['data'][$next_day]) {
					// consecutive dates have different rooms to modify. Break node
					array_push($seasons_pool, array(
						'from' 	=> $from_date_node,
						'to' 	=> $cur_day,
						'rooms'	=> $rule_data['data'][$cur_day]
					));
					$from_date_node = $next_day;
				}
			} else {
				// consecutive day cannot be updated. Break node
				array_push($seasons_pool, array(
					'from' 	=> $from_date_node,
					'to' 	=> $cur_day,
					'rooms'	=> $rule_data['data'][$cur_day]
				));
				$from_date_node = '';
			}
			$info_day = getdate($next_ts);
		}

		if ($rule_data['rule']['rule']->updown == 'up') {
			$base_log = 'Increase rates when '.($rule_data['rule']['rule']->units_count == 'single' ? '' : 'total ').'units left '.($rule_data['rule']['rule']->gtlt == 'lt' ? '&lt;' : '&gt;').'= '.$rule_data['rule']['rule']->units.'.';
		} else {
			$base_log = 'Discount rates when '.($rule_data['rule']['rule']->units_count == 'single' ? '' : 'total ').'units left '.($rule_data['rule']['rule']->gtlt == 'lt' ? '&lt;' : '&gt;').'= '.$rule_data['rule']['rule']->units.'.';
		}

		if ($seasons_pool) {
			// create one special price for each date-room node of the seasons_pool array
			if ($this->debug_output) {
				echo 'Seasons Pool:<pre>'.print_r($seasons_pool, true).'</pre><br/>'."\n";
			}
			$vboConnector = VikChannelManager::getVikBookingConnectorInstance();
			foreach ($seasons_pool as $ks => $season_data) {
				$sp_id = $vboConnector->addSmartBalancerSpecialPrice($season_data['from'], $season_data['to'], $season_data['rooms'], $rule_data['rule']);
				if ($sp_id !== false && $sp_id > 0) {
					// at least one special price has been created and so the rule has been applied
					$rule_applied = 1;
					if ($this->debug_output) {
						echo '<p>Special Price created with ID '.$sp_id.'</p>'."\n";
					}
					// set rule execution logs because the rule has been applied
					$this->setRuleExecLog($rule_data['rule'], $season_data['rooms'], $this->getDatesRange($season_data['from'], $season_data['to']), $base_log.' '.'New Special Price ID '.$sp_id, true);
				} else {
					// error with the VBO Connector class, unset season data and log it
					if ($this->debug_output) {
						echo '<p>Error creating Special Price rule: '.$vboConnector->getError().'</p>'."\n";
					}
					unset($seasons_pool[$ks]);
					$this->setRuleExecLog($rule_data['rule'], $season_data['rooms'], $this->getDatesRange($season_data['from'], $season_data['to']), 'Error creating Special Price rule: '.$vboConnector->getError());
				}
			}
			// modify the rates also on the OTAs for all the necessary dates at once (not once for each season update node)
			if ($rule_data['rule']['rule']->ibeotas == 'ibeota' && count($seasons_pool) && $rule_applied > 0) {
				$channels_res = $this->dispatchChannelsRatesRule($rule_data['rule'], $seasons_pool, $vboConnector);
				if (!$channels_res['status']) {
					// error returned from the method dispatchChannelsRatesRule
					$this->setRuleExecLog($rule_data['rule'], array('channels' => array()), array(), $channels_res['log']);
				} else {
					// set the channels update responses
					$this->setRuleExecLog($rule_data['rule'], array('channels' => array()), array(), $channels_res['log']);
				}
			}
			// store execution log
			$this->finalizeRuleExecution($rule_data['rule']);
		}

		return (int)$rule_applied;
	}

	/**
	 * This protected method is called by the method applyParsedRatesRule when a rule
	 * of type 'rt' has been prepared. This method is called to dispatch each node of dates/rooms
	 * that have been used to create the Special Prices in VBO to update the rates on the site.
	 * Here we prepare the necessary arrays of data for the Connector that will prepare and
	 * send to e4jConnect the XML update request for all channels.
	 * Returns an associative array with the result of the update request made by e4jConnect. The
	 * array result contains: Key 'status': a boolean integer. Key 'log': a string in case of errors,
	 * or an array of results with success, warning, errors.
	 *
	 * @param 		$rule 			array 	The array containing the parsed record of this Rule.
	 * @param 		$seasons_pool 	array 	An array of arrays containing: 'from' and 'to' dates, and an array of 'rooms' IDs.
	 * @param 		$vboConnector	object 	An instance of the VikBookingConnector Class.
	 *
	 * @return 		array 			associative array with the result of the update.
	 **/
	protected function dispatchChannelsRatesRule($rule, $seasons_pool, $vboConnector)
	{
		$result = array(
			'status' => 0,
			'log' => ''
		);

		//Get the mapped channels for all the rooms
		$all_rooms = array();
		$channels_map = array();
		foreach ($seasons_pool as $season_data) {
			foreach ($season_data['rooms'] as $idr) {
				if (!in_array($idr, $all_rooms)) {
					array_push($all_rooms, $idr);
				}
			}
		}
		if (!count($all_rooms)) {
			//no rooms found
			$result['log'] .= "\nNo rooms found.";
			return $result;
		}
		$rooms_channels = array();
		$q = "SELECT * FROM `#__vikchannelmanager_roomsxref` WHERE `idroomvb` IN (".implode(',', $all_rooms).");";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if ($this->dbo->getNumRows() > 0) {
			$channels_data = $this->dbo->loadAssocList();
			foreach ($channels_data as $ch_data) {
				$channels_map[$ch_data['idchannel']] = ucfirst($ch_data['channel']);
				if (!isset($rooms_channels[$ch_data['idroomvb']])) {
					$rooms_channels[$ch_data['idroomvb']] = array();
				}
				$rooms_channels[$ch_data['idroomvb']][$ch_data['idchannel']] = $ch_data;
			}
		}
		if (!count($rooms_channels)) {
			//no channels found for these rooms so add logs
			$result['log'] .= "\nNo valid channels found for these rooms.";
			return $result;
		}
		//

		//Build push data for each channel rate plan according to the Bulk Rates Cache (if empty for some rooms, the method will skip them)
		$rooms_pricing = array();
		//load the 'Bulk Action - Rates Upload' cache
		$bulk_rates_cache = VikChannelManager::getBulkRatesCache();
		foreach ($rooms_channels as $room_id => $chs) {
			if (!isset($bulk_rates_cache[$room_id])) {
				//Bulk Rates Cache empty for this room, unset it.
				unset($rooms_channels[$room_id]);
				continue;
			}
			foreach ($chs as $ch_id => $ch_data) {
				$ch_found = false;
				foreach ($bulk_rates_cache[$room_id] as $price_id => $bulk_data) {
					if (isset($bulk_data['channels']) && in_array($ch_id, $bulk_data['channels'])) {
						//Bulk Rates Cache data available for this Channel
						if (!isset($rooms_pricing[$room_id])) {
							$rooms_pricing[$room_id] = array();
						}
						$rooms_pricing[$room_id][$price_id] = $bulk_data;
						$ch_found = true;
					}
				}
				if (!$ch_found) {
					//Bulk Rates Cache empty for this Channel in this room, unset it.
					unset($rooms_channels[$room_id][$ch_id]);
				}
			}
			if (!count($rooms_channels[$room_id])) {
				//Bulk Rates Cache empty for all channels of this room, unset it.
				unset($rooms_channels[$room_id]);
				continue;
			}
		}
		if (!count($rooms_channels) || !count($rooms_pricing)) {
			//no channels found for these rooms so add logs
			$result['log'] .= "\nBulk Rates Cache empty for these rooms.";
			return $result;
		}
		//

		if ($this->debug_output) {
			echo '$rooms_channels:<pre>'.print_r($rooms_channels, true).'</pre><br/>'."\n";
			echo '$rooms_pricing:<pre>'.print_r($rooms_pricing, true).'</pre><br/>'."\n";
		}

		//prepare arrays for the VBOConnector Class
		$push_channels 	= array();
		$push_chrplans 	= array();
		$push_nodes 	= array();
		$push_rooms 	= array();
		$push_vars 		= array();
		foreach ($rooms_channels as $room_id => $chs) {
			if (!isset($rooms_pricing[$room_id])) {
				continue;
			}
			foreach ($rooms_pricing[$room_id] as $price_id => $bulk_data) {
				$price_channels = array();
				foreach ($bulk_data['channels'] as $ch_id) {
					if (isset($chs[$ch_id])) {
						array_push($price_channels, $ch_id);
					}
				}
				if (!count($price_channels)) {
					continue;
				}
				//one room ID to update for each type of price that has Bulk Rates Cache available for mapped channels
				array_push($push_rooms, $room_id);
				array_push($push_channels, implode(',', $price_channels));
				//compose channels rate plans data
				$price_chrplans = array();
				foreach ($price_channels as $ch_id) {
					$ch_rplan = isset($bulk_data['rplans'][$ch_id]) ? $bulk_data['rplans'][$ch_id] : '';
					$ch_rplan .= isset($bulk_data['rplanarimode'][$ch_id]) ? '='.$bulk_data['rplanarimode'][$ch_id] : '';
					$ch_rplan .= isset($bulk_data['cur_rplans'][$ch_id]) && !empty($bulk_data['cur_rplans'][$ch_id]) ? ':'.$bulk_data['cur_rplans'][$ch_id] : '';
					array_push($price_chrplans, $ch_rplan);
				}
				array_push($push_chrplans, implode(',', $price_chrplans));
				//compose update nodes
				$price_nodes = array();
				foreach ($seasons_pool as $ks => $season_data) {
					if (!in_array($room_id, $season_data['rooms'])) {
						continue;
					}
					//$from_date, $to_date, $minlos, $maxlos, $rmod, $rmodop, $rmodamount, $rmodval
					$rmod = $bulk_data['rmod'];
					$rmodop = $bulk_data['rmodop'];
					$rmodamount = $bulk_data['rmodamount'];
					$rmodval = $bulk_data['rmodval'];
					$node_data = $season_data['from'].'_'.$season_data['to'].'___'.$rmod.'_'.$rmodop.'_'.$rmodamount.'_'.$rmodval;
					array_push($price_nodes, $node_data);
				}
				array_push($push_nodes, implode(';', $price_nodes));
				//compose push vars with price_id and default cost per night
				array_push($push_vars, implode(';', array($bulk_data['pricetype'], $bulk_data['defrate'])));
				//
			}
		}
		//

		//launch update request through the VBOConnector
		//set the caller to SmartBalancer to reduce the sleep time between the requests
		$vboConnector->caller = 'SmartBalancer';
		//
		$response = $vboConnector->channelsRatesPush($push_channels, $push_chrplans, $push_nodes, $push_rooms, $push_vars);
		if ($vc_error = $vboConnector->getError(true)) {
			$result['log'] .= $vc_error;
		} else {
			//the request was sent so we change the status to 1 and the 'log' key to an array of results
			$result['status'] = 1;
			$result['log'] = array(
				'channels_success' 	=> array(),
				'channels_warnings' => array(),
				'channels_errors' 	=> array(),
				'breakdown' 		=> array()
				//every log-array may contain special tags like {Room ID 5} or {RatePlan ID 118352A} that will be parsed by a protected method of the View displaying the logs.
			);
			//parse the channels update result and compose the rates breakdown, success, warning and error messages
			$result_pool = json_decode($response, true);
			if ($this->debug_output) {
				echo 'Channels Response $result_pool:<pre>'.print_r($result_pool, true).'</pre><br/>'."\n";
			}
			foreach ($result_pool as $rid => $ch_responses) {
				foreach ($ch_responses as $ch_id => $ch_res) {
					if ($ch_id == 'breakdown' || !is_numeric($ch_id)) {
						if ($ch_id == 'breakdown') {
							$brkstr = "{Room ID ".$rid."}:\n";
							if (!is_array($ch_res)) {
								$brkstr .= '<span class="vcm-sb-lgsm">'.$ch_res.'</span>';
							} else {
								foreach ($ch_res as $dinv => $brk) {
									$brkstr .= '<span class="vcm-sb-lgsm">'.$dinv.' '.$brk.'</span>'."\n";
								}
							}
							array_push($result['log']['breakdown'], rtrim($brkstr, "\n"));
						}
						continue;
					}
					$ch_id = (int)$ch_id;
					if (substr($ch_res, 0, 6) == 'e4j.OK') {
						//success
						if (!isset($result['log']['channels_success'][$ch_id])) {
							$result['log']['channels_success'][$ch_id] = $channels_map[$ch_id].': <span class="vcm-sb-lgsm">{Room ID '.$rid.'}</span>';
						} else {
							$result['log']['channels_success'][$ch_id] .= '<span class="vcm-sb-lgsm">, {Room ID '.$rid.'}</span>';
						}
					} elseif (substr($ch_res, 0, 11) == 'e4j.warning') {
						//warning
						$ch_res = rtrim($ch_res, "\n");
						if (!isset($result['log']['channels_warnings'][$ch_id])) {
							$result['log']['channels_warnings'][$ch_id] = $channels_map[$ch_id].', <span class="vcm-sb-lgsm">{Room ID '.$rid.'}: '.str_replace('e4j.warning.', '', $ch_res)."</span>";
						} else {
							$result['log']['channels_warnings'][$ch_id] .= "\n<span class=\"vcm-sb-lgsm\">{Room ID ".$rid."}: ".str_replace('e4j.warning.', '', $ch_res)."</span>";
						}
						//add the channel also to the successful list in case of Warning
						if (!isset($result['log']['channels_success'][$ch_id])) {
							$result['log']['channels_success'][$ch_id] = $channels_map[$ch_id];
						}
					} elseif (substr($ch_res, 0, 9) == 'e4j.error') {
						//error
						$ch_res = rtrim($ch_res, "\n");
						if (!isset($result['log']['channels_errors'][$ch_id])) {
							$result['log']['channels_errors'][$ch_id] = $channels_map[$ch_id].', <span class="vcm-sb-lgsm">{Room ID '.$rid.'}: '.str_replace('e4j.error.', '', $ch_res)."</span>";
						} else {
							$result['log']['channels_errors'][$ch_id] .= "\n<span class=\"vcm-sb-lgsm\">{Room ID ".$rid."}: ".str_replace('e4j.error.', '', $ch_res)."</span>";
						}
					}
				}
			}
		}

		return $result;
	}

	/**
	 * This method counts all the bookings that were generated thanks 
	 * to a modification of the rates made by a Smart Balancer Rule of type 'rt'.
	 * New bookings can optionally be checked and added to the database.
	 * The method can be called for all rules or just for one rule and it returns
	 * an associative array with all the booking IDs and some information.
	 * It is triggered by the pings made by the e4jConnect servers to execute
	 * the rules, or manually, when clicking on the button to count the boookings
	 * received thanks to the Smart Balancer Rules of type 'rt'.
	 *
	 * @param 		$rule_id 		int
	 * @param 		$check_new		boolean
	 *
	 * @return 		array 			associative array with key-value pairs of Rule_ID-Array_of_Bookings
	 **/
	public function countRatesModBookings($rule_id = 0, $check_new = true)
	{
		$bookings = array();

		//Count current bookings
		$skip_bids = array();
		$q = "SELECT * FROM `#__vikchannelmanager_balancer_bookings`".($rule_id > 0 ? " WHERE `rule_id`=".(int)$rule_id : "")." ORDER BY `id` DESC;";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if ($this->dbo->getNumRows() > 0) {
			$rules_bookings = $this->dbo->loadAssocList();
			foreach ($rules_bookings as $v) {
				if (!in_array($v['bid'], $skip_bids)) {
					array_push($skip_bids, $v['bid']);
				}
				if (!isset($bookings[$v['rule_id']])) {
					$bookings[$v['rule_id']] = array();
				}
				$bookings[$v['rule_id']][$v['bid']] = $v;
			}
		}
		//

		//Check new bookings
		if ($check_new) {
			$q = "SELECT `rl`.`ts`,`rl`.`rule_id`,`rl`.`day`,`rl`.`room_ids`,`ru`.`id`,`ru`.`name`,`ru`.`mod_ts`,`ru`.`from_ts`,`ru`.`to_ts`,`ru`.`rule` 
				FROM `#__vikchannelmanager_balancer_ratelogs` AS `rl` LEFT JOIN `#__vikchannelmanager_balancer_rules` AS `ru` ON `rl`.`rule_id`=`ru`.`id` 
				WHERE `ru`.`type`='rt'".($rule_id > 0 ? ' AND `ru`.`id`='.(int)$rule_id : ' AND `ru`.`to_ts` >= '.time())." 
				ORDER BY `rl`.`day` ASC;";
			$this->dbo->setQuery($q);
			$this->dbo->execute();
			if ($this->dbo->getNumRows() > 0) {
				$ratelogs = $this->dbo->loadAssocList();
				foreach ($ratelogs as $k => $v) {
					//make every ratelog-date an array of getdate
					$ratelogs[$k]['day'] = getdate(strtotime($v['day']));
					//decode rule's rules
					$ratelogs[$k]['rule'] = json_decode($v['rule']);
				}
				//load all bookings from VBO between the min and max dates stored in ratelogs
				$min_date = $ratelogs[0]['day'][0];
				$max_date_info = getdate($ratelogs[(count($ratelogs) - 1)]['day'][0]);
				$max_date = mktime(23, 59, 59, $max_date_info['mon'], $max_date_info['mday'], $max_date_info['year']);
				$q = "SELECT `o`.`id`,`o`.`ts`,`o`.`days`,`o`.`checkin`,`o`.`checkout`, 
						(SELECT GROUP_CONCAT(`or`.`idroom` SEPARATOR ',') FROM `#__vikbooking_ordersrooms` AS `or` WHERE `or`.`idorder`=`o`.`id`) AS `room_ids`, 
						(SELECT GROUP_CONCAT(`or`.`room_cost` SEPARATOR ';') FROM `#__vikbooking_ordersrooms` AS `or` WHERE `or`.`idorder`=`o`.`id`) AS `room_costs`, 
						(SELECT GROUP_CONCAT(`or`.`cust_cost` SEPARATOR ';') FROM `#__vikbooking_ordersrooms` AS `or` WHERE `or`.`idorder`=`o`.`id`) AS `cust_costs`
					FROM `#__vikbooking_orders` AS `o`  
					WHERE `o`.`status`='confirmed' AND `o`.`closure` = 0 AND (
						(`o`.`checkin` >= ".$min_date." AND `o`.`checkin` <= ".$max_date.") OR 
						(`o`.`checkout` >= ".$min_date." AND `o`.`checkout` <= ".$max_date.") OR 
						(`o`.`checkin` < ".$min_date." AND `o`.`checkout` > ".$max_date.")
					);";
				$this->dbo->setQuery($q);
				$this->dbo->execute();
				//
				if ($this->dbo->getNumRows() > 0) {
					$all_bookings = $this->dbo->loadAssocList();
					if ($this->debug_output) {
						echo 'Check New bookings:<br/>$min_date: '.date('Y-m-d H:i:s', $min_date).' - $max_date: '.date('Y-m-d H:i:s', $max_date).'<br/><pre>'.print_r($all_bookings, true).'</pre><br/>';
						echo 'Current ratelogs:<pre>'.print_r($ratelogs, true).'</pre><br/>';
						echo 'Current bookinglogs:<pre>'.print_r($bookings, true).'</pre><br/>';
					}
					//parse all the bookings and check if their dates were modified by any rules of the Smart Balancer, by excluding the existing bids in the 'balancer_bookings' table
					foreach ($all_bookings as $booking) {
						if (in_array($booking['id'], $skip_bids)) {
							continue;
						}
						$rooms_booked = explode(',', $booking['room_ids']);
						$checkin_info = getdate($booking['checkin']);
						foreach ($ratelogs as $k => $v) {
							$ratelog_ts = mktime($checkin_info['hours'], $checkin_info['minutes'], $checkin_info['seconds'], $v['day']['mon'], $v['day']['mday'], $v['day']['year']);
							if ($v['mod_ts'] <= $booking['ts'] && $v['ts'] <= $booking['ts'] && $ratelog_ts >= $booking['checkin'] && $ratelog_ts < $booking['checkout']) {
								//rule modts and ratelog applied ts are before booking date, and ratelog modified date ($ratelog_ts) affects some nights of the booking (not the checkout day)
								foreach ($rooms_booked as $rid) {
									if (strpos($v['room_ids'], ';'.$rid.';') !== false) {
										//the room modified by the Smart Balancer was booked, it's a new booking found
										array_push($skip_bids, $booking['id']);
										//store the booking found
										$saveamount = '';
										if (is_object($v['rule'])) {
											$saveamount .= ($v['rule']->updown == 'up' ? '+' : '-').(float)$v['rule']->amount.((int)$v['rule']->pcent > 0 ? '%' : '');
										}
										$q = "INSERT INTO `#__vikchannelmanager_balancer_bookings` (`bid`,`ts`,`rule_id`,`rule_name`,`saveamount`) VALUES(".(int)$booking['id'].", ".(int)$booking['ts'].", ".(int)$v['rule_id'].", ".$this->dbo->quote($v['name']).", ".$this->dbo->quote($saveamount).");";
										$this->dbo->setQuery($q);
										$this->dbo->execute();
										$newid = $this->dbo->insertid();
										//add the new booking found to the array that will be returned
										if (!isset($bookings[$v['rule_id']])) {
											$bookings[$v['rule_id']] = array();
										}
										$bookings[$v['rule_id']][$booking['id']] = array(
											'id' => $newid,
											'ts' => $booking['ts'],
											'rule_id' => $v['rule_id'],
											'rule_name' => $v['name'],
											'saveamount' => $saveamount
										);
										//break the execution and go to the next booking
										break 2;
									}
								}
							}
						}
					}
				}
			}
		}
		//

		return $bookings;
	}

	/**
	 * This method is used to build up an array of execution logs
	 * for the given rule. The logs are then stored onto the db
	 * to check the execution and the way the rule was applied.
	 * In case of applied 'rt' rules, the modified dates/rooms are stored on the db.
	 *
	 * @param 		$rule 			array
	 * @param 		$id_rooms 		int|array 	integer for type av, array for type rt
	 * @param 		$extra_ref		int|array 	integer for type av (Booking ID), array for type rt (array of dates or channels responses)
	 * @param 		$log 			string
	 * @param 		$store_ratelogs	boolean 	if true and rule type 'rt', a query will be made onto the ratelogs table for later executions.
	 *
	 * @return 		void
	 **/
	protected function setRuleExecLog($rule, $id_rooms, $extra_ref, $log, $store_ratelogs = false)
	{
		if (!isset($rule['id']) || ($rule['type'] == 'av' && empty($extra_ref))) {
			return;
		}
		$id_rule = $rule['id'];
		if (!isset($this->exec_logs[$id_rule])) {
			$this->exec_logs[$id_rule] = array();
		}
		$logdata = array(
			'idroom' => $id_rooms,
			'log' => $log
		);
		if ($rule['type'] == 'av') {
			$logdata['idorder'] = $extra_ref;
		}
		if ($rule['type'] == 'rt') {
			if (is_array($id_rooms) && isset($id_rooms['channels'])) {
				//logs for the channels update, we do not need the IDs of the rooms for this type of log
				unset($logdata['idroom']);
			} else {
				$logdata['dates'] = $extra_ref;
			}
			if ($store_ratelogs && is_array($id_rooms) && count($id_rooms) && is_array($extra_ref) && count($extra_ref)) {
				//make query onto the ratelogs table for later executions checks and skipping of already updated dates/rooms.
				$now = time();
				$room_ids_str = '';
				foreach ($id_rooms as $room_id) {
					$room_ids_str .= ';'.$room_id.';';
				}
				foreach ($extra_ref as $day) {
					$q = "INSERT INTO `#__vikchannelmanager_balancer_ratelogs` (`ts`,`rule_id`,`day`,`room_ids`) VALUES (" . $now . ", " . (int)$rule['id'] . ", " . $this->dbo->quote(date('Y-m-d', strtotime($day))) . ", " . $this->dbo->quote($room_ids_str) . ");";
					$this->dbo->setQuery($q);
					$this->dbo->execute();
				}
			}
		}
		array_push($this->exec_logs[$id_rule], $logdata);
	}

	/**
	 * This method shall be called after using setRuleExecLog.
	 * Stores onto the database the current execution log by
	 * appending this log to any existing previous log.
	 * Max 20 last execution logs allowed.
	 * The execution log is reset for this rule ID.
	 * 
	 * @param 		$rule 		array
	 *
	 * @return 		boolean
	 **/
	protected function finalizeRuleExecution($rule)
	{
		if (empty($rule['id']) || !isset($this->exec_logs[$rule['id']]) || !count($this->exec_logs[$rule['id']])) {
			return false;
		}

		$exec_log = [
			'ts' => time(),
			'exec' => $this->exec_logs[$rule['id']],
		];

		if (empty($rule['logs'])) {
			$current_logs = [];
		} else {
			$current_logs = json_decode($rule['logs'], true);
			$current_logs = is_array($current_logs) ? $current_logs : [];
		}

		array_unshift($current_logs, $exec_log);

		if (count($current_logs) > 20) {
			$current_logs = array_slice($current_logs, 0, 20);
		}

		$q = "UPDATE `#__vikchannelmanager_balancer_rules` SET `logs`=".$this->dbo->quote(json_encode($current_logs))." WHERE `id`=" . (int)$rule['id'] . ";";
		$this->dbo->setQuery($q);
		$this->dbo->execute();

		unset($this->exec_logs[$rule['id']]);

		return true;
	}
}
