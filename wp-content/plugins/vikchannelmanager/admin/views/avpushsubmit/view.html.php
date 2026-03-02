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

jimport('joomla.application.component.view');

class VikChannelManagerViewAvpushsubmit extends JViewUI
{
	public function display($tpl = null)
	{
		require_once(VBO_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "lib.vikbooking.php");
				
		$this->addToolBar();
		
		VCM::load_css_js();
		VCM::loadDatePicker();

		$dbo = JFactory::getDbo();
		$mainframe = JFactory::getApplication();
		$session = JFactory::getSession();

		$rooms = VikRequest::getVar('rooms', []);
		$from = VikRequest::getVar('from', []);
		$to = VikRequest::getVar('to', []);
		$channels = VikRequest::getVar('channels', []);
		$max_nodes = VikRequest::getInt('max_nodes', '', 'request');
		$max_nodes = empty($max_nodes) || $max_nodes <= 0 ? 50 : $max_nodes;
		$max_channels = 1;
		$closeall = VikRequest::getInt('closeall', 0, 'request');

		if (!$rooms || !$from || !$to || !$channels || count($rooms) != count($from) || count($rooms) != count($to)) {
			VikError::raiseWarning('', JText::_('VCMAVPUSHERRNODATA'));
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=avpush");
			exit;
		}
		
		$channels_mapped = false;
		$q = "SELECT `id`,`name`,`units` FROM `#__vikbooking_rooms` WHERE `id` IN (".implode(',', $rooms).") ORDER BY `#__vikbooking_rooms`.`name` ASC;";
		$dbo->setQuery($q);
		$rows = $dbo->loadAssocList();
		if ($rows) {
			foreach ($rows as $k => $r) {
				if (!array_key_exists($r['id'], $channels) || !(count($channels[$r['id']]) > 0)) {
					foreach ($rooms as $rk => $rv) {
						if ($rv == $r['id']) {
							unset($from[$rk]);
							unset($to[$rk]);
							break;
						}
					}
					unset($rows[$k]);
					continue;
				}
				$rows[$k]['channels'] = [];
				$q = "SELECT * FROM `#__vikchannelmanager_roomsxref` WHERE `idroomvb`='".$r['id']."' AND `idchannel` IN (".implode(',', $channels[$r['id']]).");";
				$dbo->setQuery($q);
				$channels_data = $dbo->loadAssocList();
				if ($channels_data) {
					$max_channels = count($channels_data) > $max_channels ? count($channels_data) : $max_channels;
					foreach ($channels_data as $ch_data) {
						$rows[$k]['channels'][$ch_data['idchannel']] = $ch_data;
					}
					$channels_mapped = true;
				} else {
					foreach ($rooms as $rk => $rv) {
						if ($rv == $r['id']) {
							unset($from[$rk]);
							unset($to[$rk]);
							break;
						}
					}
					unset($rows[$k]);
				}
			}
		} else {
			$rows = [];
		}

		foreach ($from as $kf => $vf) {
			$fromdate = strtotime($vf);
			$todate = strtotime($to[$kf]);
			if (empty($fromdate) || empty($todate) || $todate < $fromdate) {
				unset($from[$kf]);
				unset($to[$kf]);
				foreach ($rows as $k => $r) {
					if ($rooms[$kf] == $r['id']) {
						unset($rows[$k]);
						break;
					}
				}
			}
		}

		if ($channels_mapped !== true || !$rows) {
			VikError::raiseWarning('', JText::_('VCMAVPUSHERRNODATA').'.');
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=avpush");
			exit;
		}

		/**
		 * Update max date in the future for availability inventory.
		 * 
		 * @since 	1.7.1
		 */
		if (!$closeall && !VikRequest::getInt('e4j_debug', 0)) {
			$maxts = strtotime(max($to));
			$currentdates = VikChannelManager::getInventoryMaxFutureDates();
			$currentdates['av'] = $maxts;
			VikChannelManager::setInventoryMaxFutureDates($currentdates);
		}

		/**
		 * Global closing dates defined in Vik Booking are also considered. This is valid for the
		 * "Bulk Action - Copy Availability" (CustAvailUpdateRQ + AutoBulk AV) requests only.
		 * 
		 * @since 	1.8.13
		 */
		$glob_closing_dates = VikBooking::getClosingDates();
		$glob_closing_dates = !is_array($glob_closing_dates) ? [] : $glob_closing_dates;

		$availability = [];
		$from_to = [];
		foreach ($rooms as $rk => $roomid) {
			$tot_inv = 0;
			foreach ($rows as $k => $r) {
				if ($roomid == $r['id']) {
					$tot_inv = $r['units'];
					break;
				}
			}
			if ($tot_inv <= 0) {
				continue;
			}

			// VCM 1.6.8 - Close all rooms
			if ($closeall) {
				$tot_inv = 0;
			}

			$availability[$roomid] = [];
			$start_ts = strtotime($from[$rk]);
			$end_ts_base = strtotime($to[$rk]);
			$end_ts_info = getdate($end_ts_base);
			$end_ts = mktime(23, 59, 59, date('n', $end_ts_base), date('j', $end_ts_base), date('Y', $end_ts_base));
			$from_to[$roomid] = date('Y-m-d', $start_ts).'_'.date('Y-m-d', $end_ts);

			$arrbusy = [];
			if (!$closeall) {
				$q = "SELECT `b`.*,`ob`.`idorder` FROM `#__vikbooking_busy` AS `b`,`#__vikbooking_ordersbusy` AS `ob` WHERE `b`.`idroom`=" . (int)$roomid . " AND `b`.`id`=`ob`.`idbusy` AND (`b`.`checkin`>={$start_ts} OR `b`.`checkout`>={$start_ts}) AND (`b`.`checkin`<={$end_ts} OR `b`.`checkout`<={$start_ts});";
				$dbo->setQuery($q);
				$arrbusy = $dbo->loadAssocList();
			}

			$nowts = getdate($start_ts);
			$node_from = date('Y-m-d', $nowts[0]);
			$node_to = '';
			$last_av = '';
			while ($nowts[0] < $end_ts) {
				// count units booked
				$totfound = 0;
				foreach ($arrbusy as $b) {
					$tmpone = getdate($b['checkin']);
					$ritts = mktime(0, 0, 0, $tmpone['mon'], $tmpone['mday'], $tmpone['year']);
					$tmptwo = getdate($b['checkout']);
					$conts = mktime(0, 0, 0, $tmptwo['mon'], $tmptwo['mday'], $tmptwo['year']);
					if ($nowts[0] >= $ritts && $nowts[0] < $conts) {
						$totfound++;
					}
				}

				// check global closing dates
				foreach ($glob_closing_dates as $glob_closing_date) {
					if (!is_array($glob_closing_date) || empty($glob_closing_date['from']) || empty($glob_closing_date['to'])) {
						continue;
					}
					if ($nowts[0] >= $glob_closing_date['from'] && $nowts[0] <= $glob_closing_date['to']) {
						// this date is closed at property-level
						$totfound = $tot_inv;
						break;
					}
				}

				// set remaining units
				$remaining = $tot_inv - $totfound;
				$remaining = $remaining < 0 ? 0 : $remaining;
				$last_av = strlen($last_av) <= 0 ? $remaining : $last_av;
				$node_to = empty($node_to) ? $node_from : $node_to;
				$nextdayts = mktime(0, 0, 0, $nowts['mon'], ($nowts['mday'] + 1), $nowts['year']);
				if ($last_av != $remaining) {
					$availability[$roomid][] = $node_from.'_'.$node_to.'_'.$last_av;
					$last_av = $remaining;
					$node_from = date('Y-m-d', $nowts[0]);
				}
				$node_to = date('Y-m-d', $nowts[0]);
				$nowts = getdate($nextdayts);
			}
			$availability[$roomid][] = $node_from.'_'.$node_to.'_'.$remaining;
		}

		$avpushcusta_details = '';

		foreach ($rows as $k => $r) {
			$rows[$k]['availability'] = $availability[$r['id']];
			$rows[$k]['from_to'] = $from_to[$r['id']];
			$channel_names = [];
			foreach ($r['channels'] as $ck => $cv) {
				$channel_names[$cv['idchannel']] = ucwords($cv['channel']);
			}
			$avpushcusta_details .= $r['name'].': '.str_replace('_', ' - ', $from_to[$r['id']]).(count($channel_names) ? ' ('.implode(', ', $channel_names).')' : '').' ('.count($availability[$r['id']]).' Nodes)'."\n";
		}

		$avpushcusta_details = rtrim($avpushcusta_details, "\n");
		if (!empty($avpushcusta_details)) {
			$session->set('vcmAvpushDetails', $avpushcusta_details);
		}

		// check limit of max nodes based on max channels
		if ($max_nodes >= 50 && $max_channels > 2) {
			$max_nodes -= (5 * $max_channels);
			$max_nodes = $max_nodes < 10 ? 10 : $max_nodes;
		}

		$nkey = VikChannelManager::generateNKey('0');
		$session->set('vcmAvpushNkey', $nkey);

		$this->rows = $rows;
		$this->max_nodes = $max_nodes;

		parent::display($tpl);
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar()
	{
		//Add menu title and some buttons to the page
		JToolBarHelper::title(JText::_('VCMMAINTAVPUSH'), 'vikchannelmanager');

		JToolBarHelper::cancel( 'cancel', JText::_('BACK'));		
	}
}
