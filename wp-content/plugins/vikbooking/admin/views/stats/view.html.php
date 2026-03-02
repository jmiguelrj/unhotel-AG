<?php
/**
 * @package     VikBooking
 * @subpackage  com_vikbooking
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

// import Joomla view library
jimport('joomla.application.component.view');

class VikBookingViewStats extends JViewVikBooking {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$dbo = JFactory::getDbo();
		$document = JFactory::getDocument();
		$session = JFactory::getSession();
		$cpin = VikBooking::getCPinIstance();
		$document->addStyleSheet(VBO_ADMIN_URI.'resources/Chart.min.css', array('version' => VIKBOOKING_SOFTWARE_VERSION));
		$document->addScript(VBO_ADMIN_URI . 'resources/Chart.min.js', array('version' => VIKBOOKING_SOFTWARE_VERSION));
		$pid_room = VikRequest::getInt('id_room', '', 'request');
		$pstatsmode = VikRequest::getString('statsmode', '', 'request');
		$sess_statsmode = $session->get('vbViewStatsMode', '');
		$pstatsmode = empty($pstatsmode) && !empty($sess_statsmode) ? $sess_statsmode : $pstatsmode;
		$pstatsmode = in_array($pstatsmode, array('ts', 'nights')) ? $pstatsmode : 'ts';
		$pdfrom = VikRequest::getString('dfrom', '', 'request');
		$sess_from = $session->get('vbViewStatsFrom', '');
		$pdfrom = empty($pdfrom) && !empty($sess_from) ? $sess_from : $pdfrom;
		$fromts = !empty($pdfrom) ? VikBooking::getDateTimestamp($pdfrom, '0', '0') : 0;
		$pdto = VikRequest::getString('dto', '', 'request');
		$sess_to = $session->get('vbViewStatsTo', '');
		$pdto = empty($pdto) && !empty($sess_to) ? $sess_to : $pdto;
		$tots = !empty($pdto) ? VikBooking::getDateTimestamp($pdto, '23', '59') : 0;
		$tots = $tots < $fromts ? 0 : $tots;
		//store last dates in session
		if (!empty($pdfrom)) {
			$session->set('vbViewStatsFrom', $pdfrom);
			$session->set('vbViewStatsTo', $pdto);
		}
		$session->set('vbViewStatsMode', $pstatsmode);
		//
		$arr_rooms = array();
		$q = "SELECT `id`,`name` FROM `#__vikbooking_rooms` ORDER BY `#__vikbooking_rooms`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$arr_rooms = $dbo->loadAssocList();
		}
		$bookings = array();
		$arr_months = array();
		$arr_channels = array();
		$arr_saleschannels = array();
		$arr_countries = array();
		$arr_totals = array('total_income' => 0, 'total_income_netcmms' => 0, 'total_income_nettax' => 0, 'nights_sold' => 0);
		$tot_rooms_units = 0;
		//Dates Clauses
		$from_clause = "`o`.`ts`>=".$fromts;
		$to_clause = "`o`.`ts`<=".$tots;
		$order_by = "`o`.`ts` ASC";
		if ($pstatsmode == 'nights') {
			$from_clause = "`o`.`checkout`>=".$fromts;
			$to_clause = "`o`.`checkin`<=".$tots;
			$order_by = "`o`.`checkin` ASC";
		}
		//
		if (!empty($pid_room)) {
			//filter by room
			//to make this query safe with the strict mode, we added the DISTINCT Optimization and we removed the GROUP BY `or`.`idorder`,`or`.`idroom`,`o`.`id` clause
			$q = "SELECT DISTINCT `o`.`id`,`o`.`custdata`,`o`.`ts`,`o`.`days`,`o`.`checkin`,`o`.`checkout`,`o`.`totpaid`,`o`.`roomsnum`,`o`.`total`,`o`.`idorderota`,`o`.`channel`,`o`.`country`,`o`.`tot_taxes`,`o`.`tot_city_taxes`,`o`.`tot_fees`,`o`.`cmms`,`o`.`closure`,`or`.`idorder`,`or`.`idroom` FROM `#__vikbooking_orders` AS `o` LEFT JOIN `#__vikbooking_ordersrooms` `or` ON `or`.`idorder`=`o`.`id` AND `or`.`idroom`=".$pid_room." WHERE `o`.`status`='confirmed' AND `or`.`idroom`=".$pid_room.(!empty($fromts) ? " AND ".$from_clause : "").(!empty($tots) ? " AND ".$to_clause : "")." ORDER BY ".$order_by.";";
		} else {
			$q = "SELECT `o`.`id`,`o`.`custdata`,`o`.`ts`,`o`.`days`,`o`.`checkin`,`o`.`checkout`,`o`.`totpaid`,`o`.`roomsnum`,`o`.`total`,`o`.`idorderota`,`o`.`channel`,`o`.`country`,`o`.`tot_taxes`,`o`.`tot_city_taxes`,`o`.`tot_fees`,`o`.`cmms`,`o`.`closure`".($pstatsmode == 'nights' ? ",(SELECT GROUP_CONCAT(`r`.`name` SEPARATOR ',') FROM `#__vikbooking_rooms` AS `r` LEFT JOIN `#__vikbooking_ordersrooms` `or` ON `or`.`idroom`=`r`.`id` WHERE `or`.`idorder`=`o`.`id`) AS `room_names`" : '')." FROM `#__vikbooking_orders` AS `o` WHERE `o`.`status`='confirmed'".(!empty($fromts) ? " AND ".$from_clause : "").(!empty($tots) ? " AND ".$to_clause : "")." ORDER BY ".$order_by.";";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$bookings = $dbo->loadAssocList();
			$first_ts = $bookings[0]['ts'];
			end($bookings);
			$last_ts = $bookings[(key($bookings))]['ts'];
			reset($bookings);
			$fromts = empty($fromts) ? $first_ts : $fromts;
			$tots = empty($tots) ? $last_ts : $tots;
			foreach ($bookings as $bk => $o) {
				$info_ts = getdate(($pstatsmode == 'nights' ? $o['checkin'] : $o['ts']));
				$monyear = $info_ts['mon'].'-'.$info_ts['year'];
				if ($pstatsmode == 'nights') {
					$is_closure = ($o['closure'] > 0 || JText::_('VBDBTEXTROOMCLOSED') == $o['custdata']);
					//Prepare the rooms booked array
					if (array_key_exists('room_names', $o) && strlen($o['room_names']) > 1) {
						if (!$is_closure) {
							$o['room_names'] = explode(',', $o['room_names']);
						} else {
							unset($o['room_names']);
						}
					}
					//Check and calculate average totals depending on the booked dates - set labels for months of check-in
					if ($o['total'] > 0) {
						if ($o['checkin'] < $fromts || $o['checkout'] > $tots) {
							$nights_in = 0;
							$oinfo_start = getdate($o['checkin']);
							$oinfo_end = getdate($o['checkout']);
							$ots_end = mktime(23, 59, 59, $oinfo_end['mon'], ($oinfo_end['mday'] - 1), $oinfo_end['year']);
							while ($oinfo_start[0] < $ots_end) {
								if ($oinfo_start[0] >= $fromts && $oinfo_start[0] <= $tots) {
									$nights_in++;
									if ($nights_in === 1) {
										//Reset variables for the month where the booking took place, it has to be the first night considered
										$monyear = $oinfo_start['mon'].'-'.$oinfo_start['year'];
									}
								}
								if ($oinfo_start[0] > $tots) {
									break;
								}
								$oinfo_start = getdate(mktime(0, 0, 0, $oinfo_start['mon'], ($oinfo_start['mday'] + 1), $oinfo_start['year']));
							}
							$fullo_total = $o['total'];
							$o['total'] = round(($o['total'] / $o['days'] * $nights_in), 2);
							$o['cmms'] = (float)$o['cmms'] > 0 ? round(($o['total'] * $o['cmms'] / $fullo_total), 2) : $o['cmms'];
							$o['tot_taxes'] = (float)$o['tot_taxes'] > 0 ? round(($o['total'] * $o['tot_taxes'] / $fullo_total), 2) : $o['tot_taxes'];
							$o['tot_city_taxes'] = (float)$o['tot_city_taxes'] > 0 ? round(($o['total'] * $o['tot_city_taxes'] / $fullo_total), 2) : $o['tot_city_taxes'];
							$o['tot_fees'] = (float)$o['tot_fees'] > 0 ? round(($o['total'] * $o['tot_fees'] / $fullo_total), 2) : $o['tot_fees'];
							//set new number of nights, percentage of the booked nights calculated and update booking
							$o['avg_stay_pcent'] = 100 * $nights_in / $o['days'];
							$o['days'] = $nights_in;
						}
					} elseif ($is_closure) {
						//room is closed, set the number of nights to 0 for statistics
						$o['avg_stay_pcent'] = 0;
						$o['days'] = 0;
					} else {
						//Total equal to 0 and not a closure
						$o['avg_stay_pcent'] = 0;
						$o['days'] = 0;
					}
					$bookings[$bk] = $o;
					//
					if (!($o['days'] > 0)) {
						//VBO 1.9 Nights-Mode should skip bookings with 0 nights
						continue;
					}
				}
				$arr_totals['total_income'] += $o['total'];
				$arr_totals['total_income_netcmms'] += (float)$o['cmms'];
				$arr_totals['total_income_nettax'] += ($o['total'] - $o['tot_taxes'] - $o['tot_city_taxes'] - $o['tot_fees']);
				$arr_totals['nights_sold'] += (empty($pid_room) ? ($o['days'] * $o['roomsnum']) : $o['days']);
				if (!empty($o['country'])) {
					if (!array_key_exists($o['country'], $arr_countries)) {
						$arr_countries[$o['country']] = 1;
					} else {
						$arr_countries[$o['country']]++;
					}
				}
				if (empty($o['channel']) || stripos($o['channel'], 'channel manager') !== false) {
					$channel = JText::_('VBOIBECHANNEL');
				} else {
					$ch_parts = explode('_', $o['channel']);
					$channel = array_key_exists(1, $ch_parts) && !empty($ch_parts[1]) ? $ch_parts[1] : $ch_parts[0];
					//Customer can be a sales channel with custom color for statistics
					if (strpos($ch_parts[0], 'customer') !== false && !array_key_exists($channel, $arr_saleschannels)) {
						$customer_id = (int)str_replace('customer', '', $ch_parts[0]);
						$customer_info = $cpin->getCustomerByID($customer_id);
						if (array_key_exists('ischannel', $customer_info) && (int)$customer_info['ischannel'] > 0) {
							$arr_saleschannels[$channel] = $customer_info;
						}
					}
					//
				}
				if (!in_array($channel, $arr_channels)) {
					$arr_channels[] = $channel;
				}
				if (!array_key_exists($monyear, $arr_months)) {
					$arr_months[$monyear] = array();
				}
				if (!array_key_exists($channel, $arr_months[$monyear])) {
					$arr_months[$monyear][$channel] = array($o);
				} else {
					$arr_months[$monyear][$channel][] = $o;
				}
			}
			$arr_totals['total_income_nettax'] -= $arr_totals['total_income_netcmms'];
			$arr_totals['total_income_netcmms'] = $arr_totals['total_income'] - $arr_totals['total_income_netcmms'];
			if (count($arr_countries)) {
				asort($arr_countries);
				$arr_countries = array_reverse($arr_countries, true);
				$all_countries = array_keys($arr_countries);
				foreach ($all_countries as $kc => $country) {
					$all_countries[$kc] = $dbo->quote($country);
				}
				$q = "SELECT `country_name`,`country_3_code` FROM `#__vikbooking_countries` WHERE `country_3_code` IN (".implode(',', $all_countries).");";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$countries_names = $dbo->loadAssocList();
					foreach ($countries_names as $kc => $vc) {
						$country_flag = '';
						if (file_exists(VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'countries'.DIRECTORY_SEPARATOR.$vc['country_3_code'].'.png')) {
							$country_flag = '<img src="'.VBO_ADMIN_URI.'resources/countries/'.$vc['country_3_code'].'.png'.'" title="'.$vc['country_name'].'" class="vbo-country-flag" />';
						}
						$arr_countries[$vc['country_3_code']] = array('country_name' => $vc['country_name'], 'tot_bookings' => $arr_countries[$vc['country_3_code']], 'img' => $country_flag);
					}
				} else {
					$arr_countries = array();
				}
			}
			$q = "SELECT SUM(`units`) AS `tot` FROM `#__vikbooking_rooms` WHERE ".(!empty($pid_room) ? "`id`=".$pid_room : "`avail`=1").";";
			$dbo->setQuery($q);
			$dbo->execute();
			$tot_rooms_units = (int)$dbo->loadResult();
		}

		$this->bookings = $bookings;
		$this->arr_rooms = $arr_rooms;
		$this->fromts = $fromts;
		$this->tots = $tots;
		$this->pstatsmode = $pstatsmode;
		$this->arr_months = $arr_months;
		$this->arr_channels = $arr_channels;
		$this->arr_saleschannels = $arr_saleschannels;
		$this->arr_countries = $arr_countries;
		$this->arr_totals = $arr_totals;
		$this->tot_rooms_units = $tot_rooms_units;
		
		// Display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar() {
		JToolBarHelper::title(JText::_('VBMAINSTATSTITLE'), 'vikbookingstats');
		JToolBarHelper::cancel( 'canceledorder', JText::_('VBBACK'));
		JToolBarHelper::spacer();
	}

}
