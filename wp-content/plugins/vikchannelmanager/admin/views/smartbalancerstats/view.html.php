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

class VikChannelManagerViewSmartbalancerstats extends JViewUI {
	function display($tpl = null) {
		$this->addToolBar();

		VCM::load_css_js();
		VCM::loadDatePicker();
		
		$dbo = JFactory::getDbo();
		$mainframe = JFactory::getApplication();
		$lim = $mainframe->getUserStateFromRequest("com_vikchannelmanager.limit", 'limit', $mainframe->get('list_limit'), 'int');
		$ordering = $mainframe->getUserStateFromRequest("smartbalancerstats.ordering", 'filter_order', 'bid', 'string');
		$orderingDir = strtoupper($mainframe->getUserStateFromRequest("smartbalancerstats.direction", 'filter_order_Dir', 'DESC', 'string'));
		$from_date = $mainframe->getUserStateFromRequest("smartbalancerstats.fromdate", 'from_date', '', 'string');
		$from_date_ts = !empty($from_date) ? strtotime($from_date) : 0;
		$to_date = $mainframe->getUserStateFromRequest("smartbalancerstats.todate", 'to_date', '', 'string');
		if (!empty($to_date)) {
			$to_date_info = getdate(strtotime($to_date));
			$to_date_ts = mktime(23, 59, 59, $to_date_info['mon'], $to_date_info['mday'], $to_date_info['year']);
		} else {
			$to_date_ts = 0;
		}
		$lim0 = VikRequest::getVar('limitstart', 0, '', 'int');
		//value resubm is used to simply understand that the page was resubmitted and so we should not count the new bookings via Smart Balancer
		$resubm = VikRequest::getInt('resubm');
		//
		$navbut = '';

		//Load VBO language file
		$lang = JFactory::getLanguage();
		$lang->load('com_vikbooking', VIKBOOKING_ADMIN_LANG, $lang->getTag(), true);
		//

		$cid = VikRequest::getVar('cid', array(0));
		$rule = array();
		$rows = array();
		$all_ts = array();
		$min_ts = 0;
		$max_ts = 0;
		$tot_smbal_bookings = 0;

		//Invoke the Smart Balancer class to get and calculate the bookings generated thanks to the 'rt' rules
		$smartbal = VikChannelManager::getSmartBalancerInstance();
		$debug_mode = VikRequest::getInt('e4j_debug');
		if ($debug_mode == -1) {
			$smartbal->debug_output = true;
		}
		//If $cid[0] is empty, it will return the statistics for all rules of type 'rt'. Check for new bookings only if at page 0
		$bookings = $smartbal->countRatesModBookings($cid[0], ($lim0 <= 0 && $resubm < 1));
		//

		if (!empty($cid[0])) {
			$q = "SELECT * FROM `#__vikchannelmanager_balancer_rules` WHERE `id`=".(int)$cid[0].";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$rule = $dbo->loadAssoc();
			}
		}

		//parse bookings and do sorting
		if (count($bookings)) {
			//get all bookings without rule ID and BID as keys, then get all ts
			foreach ($bookings as $idr => $bids) {
				foreach ($bids as $bid => $booking) {
					$all_ts[] = $booking['ts'];
					if (!empty($from_date_ts) && !empty($to_date_ts)) {
						//filter bookings by ts
						if (!($booking['ts'] >= $from_date_ts && $booking['ts'] <= $to_date_ts)) {
							continue;
						}
					}
					$tot_smbal_bookings++;
					$rows[] = $booking;
				}
			}
			//get sorting map
			$bookings_map = array();
			if ($ordering == 'rule_name') {
				foreach ($rows as $k => $v) {
					$bookings_map[$k] = $v['rule_name'];
				}
			} elseif ($ordering == 'ts') {
				foreach ($rows as $k => $v) {
					$bookings_map[$k] = $v['ts'];
				}
			} elseif ($ordering == 'saveamount') {
				foreach ($rows as $k => $v) {
					$bookings_map[$k] = $v['saveamount'];
				}
			} else {
				//bid
				foreach ($rows as $k => $v) {
					$bookings_map[$k] = $v['bid'];
				}
			}
			if ($orderingDir == 'DESC') {
				arsort($bookings_map);
			} else {
				asort($bookings_map);
			}
			
			//apply sorting
			$clonerows = $rows;
			$rows = array();
			foreach ($bookings_map as $k => $v) {
				//add temporary and empty placeholders for the status and channel keys
				$clonerows[$k]['status'] = '----';
				$clonerows[$k]['channel'] = '----';
				//
				$rows[] = $clonerows[$k];
			}

			//get min and max ts
			$min_ts = min($all_ts);
			$max_ts = max($all_ts);
			//
			
			//apply pagination
			jimport('joomla.html.pagination');
			$pageNav = new JPagination(count($rows), $lim0, $lim);
			$navbut = $pageNav->getPagesLinks();
			$rows = array_slice($rows, $lim0, $lim, true);
			
			//get bookings status and channel for each booking from VBO with just one query on the paginated bookings
			$all_bids = array();
			foreach ($rows as $k => $v) {
				if (!in_array($v['bid'], $all_bids)) {
					array_push($all_bids, $v['bid']);
				}
			}
			$q = "SELECT `id`,`status`,`channel` FROM `#__vikbooking_orders` WHERE `id` IN (".implode(', ', $all_bids).");";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$res_info = $dbo->loadAssocList();
				foreach ($res_info as $res) {
					foreach ($rows as $k => $v) {
						if ($v['bid'] == $res['id']) {
							//add information about the status and channel
							$rows[$k]['status'] = $res['status'];
							$rows[$k]['channel'] = $res['channel'];
							//go to the next booking
							break;
						}
					}
				}
			}
			//
		}
		//

		$this->bookings = $bookings;
		$this->rule = $rule;
		$this->rows = $rows;
		$this->min_ts = $min_ts;
		$this->max_ts = $max_ts;
		$this->from_date_ts = $from_date_ts;
		$this->to_date_ts = $to_date_ts;
		$this->tot_smbal_bookings = $tot_smbal_bookings;
		$this->lim0 = $lim0;
		$this->navbut = $navbut;
		$this->ordering = $ordering;
		$this->orderingDir = $orderingDir;
		
		parent::display($tpl);
	}

	protected function getRoomName($id) {
		$dbo = JFactory::getDbo();
		$q = "SELECT `name` FROM `#__vikbooking_rooms` WHERE `id`=".(int)$id.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			return $dbo->loadResult();
		}
		return $id;
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() {
		//Add menu title and some buttons to the page
		JToolBarHelper::title(JText::_('VCMMAINTSMARTBALANCERSTATS'), 'vikchannelmanager');
		
		JToolBarHelper::cancel( 'cancelsmartbalancer', JText::_('CANCEL'));
		JToolBarHelper::spacer();
		
	}

}
