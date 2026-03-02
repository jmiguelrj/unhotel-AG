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

class VikChannelManagerViewSmartbalancerlogs extends JViewUI {
	function display($tpl = null) {
		VCM::load_css_js();
	
		$cid = VikRequest::getVar('cid', array(0));
		$row = array();

		$dbo = JFactory::getDbo();
		$mainframe = JFactory::getApplication();

		$q = "SELECT * FROM `#__vikchannelmanager_balancer_rules` WHERE `id`=".(int)$cid[0].";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$row = $dbo->loadAssoc();
		}

		$this->row = $row;
		
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

	protected function loadRatePlans() {
		$rateplans_map = array();
		$dbo = JFactory::getDbo();
		$q = "SELECT `id`,`name` FROM `#__vikbooking_prices`;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$all_rateplans = $dbo->loadAssocList();
			foreach ($all_rateplans as $rp) {
				$rateplans_map[$rp['id']] = $rp['name'];
			}
		}

		return $rateplans_map;
	}

	/**
	 * This method is used to parse the special tags in the
	 * rule execution logs up. It replaces strings like
	 * {Room ID 5} or {RatePlan ID 118352A} with readable values.
	 *
	 * @param 		$str 				string 	the log plain string to be parsed
	 * @param 		$rooms_map 			array 	associative array of key-value pairs of id-roomName
	 * @param 		$rateplans_map		array 	associative array of key-value pairs of id-rateplanName
	 * @param 		$bulk_rates_cache 	array 	associative array containing the Bulk Rates cache data
	 *
	 * @return 		string
	 **/
	protected function parseLogIds($str, $rooms_map, $rateplans_map, $bulk_rates_cache) {
		//Replace special tags like {Room ID 5} with the actual name of the room
		if (count($rooms_map)) {
			preg_match_all('/\{Room ID ([0-9]+)\}/U', $str, $matches);
			if (is_array($matches[1]) && @count($matches[1]) > 0) {
				$rids = array();
				foreach ($matches[1] as $rid ){
					if (!in_array($rid, $rids)) {
						$rids[] = $rid;
					}
				}
				foreach ($rids as $rid) {
					if (isset($rooms_map[(int)$rid])) {
						$str = str_replace('{Room ID '.$rid.'}', $rooms_map[(int)$rid], $str);
					} else {
						$str = str_replace('{Room ID '.$rid.'}', 'Room ID '.$rid, $str);
					}
				}
			}
		}
		//Replace special tags like {RatePlan ID 118352A} with the actual name of the rate plan in VBO
		$can_parse_rp = (count($bulk_rates_cache) && count($rateplans_map));
		preg_match_all('/\{RatePlan ID ([0-9A-z]+)\}/U', $str, $matches);
		if (is_array($matches[1]) && @count($matches[1]) > 0) {
			$rpids = array();
			foreach ($matches[1] as $rpid ){
				if (!in_array($rpid, $rpids)) {
					$rpids[] = $rpid;
				}
			}
			foreach ($rpids as $rpid) {
				$rplan_found = false;
				if ($can_parse_rp) {
					foreach ($bulk_rates_cache as $idr => $ratescache) {
						foreach ($ratescache as $idrp => $rplancache) {
							if (isset($rplancache['rplans'])) {
								foreach ($rplancache['rplans'] as $idch => $chrp) {
									if ($chrp == $rpid && isset($rplancache['pricetype']) && isset($rateplans_map[(int)$rplancache['pricetype']])) {
										//channel rate plan ID found in the bulk rates cache
										$str = str_replace('{RatePlan ID '.$rpid.'}', $rateplans_map[(int)$rplancache['pricetype']], $str);
										$rplan_found = true;
										break 3;
									}
								}
							}
						}
					}
				}
				if (!$rplan_found) {
					$str = str_replace('{RatePlan ID '.$rpid.'}', 'RatePlan ID '.$rpid, $str);
				}
			}
		}
		//
		return $str;
	}

}
