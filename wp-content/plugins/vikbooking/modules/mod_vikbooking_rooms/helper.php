<?php
/**
 * @package     VikBooking
 * @subpackage  mod_vikbooking_rooms
 * @author      Alessio Gaggii - E4J s.r.l
 * @copyright   Copyright (C) 2019 E4J s.r.l. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

// no direct access
defined('ABSPATH') or die('No script kiddies please!');

class modvikbooking_roomsHelper {
	public static function getRooms($params) {
		self::importVboLib();
		$vbo_tn = VikBooking::getTranslator();
		$dbo = JFactory::getDbo();

		/**
		 * We calculate the default timestamps for check-in of today and check-out for tomorrow.
		 * Useful to apply the today's rate by default for one night of stay.
		 * 
		 * @since 	1.3.5
		 */
		$vbo_df = VikBooking::getDateFormat();
		$vbo_df = $vbo_df == "%d/%m/%Y" ? 'd/m/Y' : ($vbo_df == "%m/%d/%Y" ? 'm/d/Y' : 'Y/m/d');
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
		$checkin_today_ts = VikBooking::getDateTimestamp(date($vbo_df), $hcheckin, $mcheckin);
		$checkout_tomorrow_ts = VikBooking::getDateTimestamp(date($vbo_df, strtotime('tomorrow')), $hcheckout, $mcheckout);
		//

		$showcatname = intval($params->get('showcatname')) == 1 ? true : false;
		$rooms = array();
		$query = $params->get('query');
		if ($query == 'price') {
			//simple order by price asc
			$q = "SELECT * FROM `#__vikbooking_rooms` WHERE `avail`='1';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$rooms = $dbo->loadAssocList();
				$vbo_tn->translateContents($rooms, '#__vikbooking_rooms');
				foreach ($rooms as $k => $c) {
					if ($showcatname) $rooms[$k]['catname'] = self::getCategoryName($c['idcat']);
					$custprice = self::getRoomParam('custprice', $c['params']);
					if (strlen($custprice) > 0 && (float)$custprice > 0.00) {
						$rooms[$k]['cost'] = (float)$custprice;
						$custpricetxt = self::getRoomParam('custpricetxt', $c['params']);
						$custpricetxt = empty($custpricetxt) ? '' : JText::_($custpricetxt);
						$rooms[$k]['custpricetxt'] = $custpricetxt;
					} else {
						$rooms[$k]['cost'] = 0;
						$q = "SELECT * FROM `#__vikbooking_dispcost` WHERE `idroom`=" . (int)$c['id'] . " ORDER BY `#__vikbooking_dispcost`.`days` ASC, `#__vikbooking_dispcost`.`cost` ASC";
						$dbo->setQuery($q, 0, 1);
						$dbo->execute();
						if ($dbo->getNumRows()) {
							$tar = $dbo->loadAssocList();
							/**
							 * We apply the today's rate by default for one night of stay.
							 * 
							 * @since 	1.3.5
							 */
							$tar = VikBooking::applySeasonsRoom($tar, $checkin_today_ts, $checkout_tomorrow_ts);
							//
							$rooms[$k]['cost'] = $tar[0]['cost'] / ($tar[0]['days'] > 1 ? $tar[0]['days'] : 1);
						}
					}
				}
			}
			$rooms = self::sortRoomsByPrice($rooms, $params);
		} elseif ($query == 'name') {
			//order by name
			$q = "SELECT * FROM `#__vikbooking_rooms` WHERE `avail`='1' ORDER BY `#__vikbooking_rooms`.`name` ".strtoupper($params->get('order'))." LIMIT ".$params->get('numb').";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$rooms = $dbo->loadAssocList();
				$vbo_tn->translateContents($rooms, '#__vikbooking_rooms');
				foreach ($rooms as $k => $c) {
					if ($showcatname) $rooms[$k]['catname'] = self::getCategoryName($c['idcat']);
					$custprice = self::getRoomParam('custprice', $c['params']);
					if (strlen($custprice) > 0 && (float)$custprice > 0.00) {
						$rooms[$k]['cost'] = (float)$custprice;
						$custpricetxt = self::getRoomParam('custpricetxt', $c['params']);
						$custpricetxt = empty($custpricetxt) ? '' : JText::_($custpricetxt);
						$rooms[$k]['custpricetxt'] = $custpricetxt;
					} else {
						$rooms[$k]['cost'] = 0;
						$q = "SELECT * FROM `#__vikbooking_dispcost` WHERE `idroom`=" . (int)$c['id'] . " ORDER BY `#__vikbooking_dispcost`.`days` ASC, `#__vikbooking_dispcost`.`cost` ASC";
						$dbo->setQuery($q, 0, 1);
						$dbo->execute();
						if ($dbo->getNumRows()) {
							$tar = $dbo->loadAssocList();
							/**
							 * We apply the today's rate by default for one night of stay.
							 * 
							 * @since 	1.3.5
							 */
							$tar = VikBooking::applySeasonsRoom($tar, $checkin_today_ts, $checkout_tomorrow_ts);
							//
							$rooms[$k]['cost'] = $tar[0]['cost'] / ($tar[0]['days'] > 1 ? $tar[0]['days'] : 1);
						}
					}
				}
			}
		} else {
			//sort by category
			$q = "SELECT * FROM `#__vikbooking_rooms` WHERE `avail`='1' AND (`idcat`='".$params->get('catid').";' OR `idcat` LIKE '".$params->get('catid').";%' OR `idcat` LIKE '%;".$params->get('catid').";%' OR `idcat` LIKE '%;".$params->get('catid').";') ORDER BY `#__vikbooking_rooms`.`name` ".strtoupper($params->get('order'))." LIMIT ".$params->get('numb').";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$rooms = $dbo->loadAssocList();
				$vbo_tn->translateContents($rooms, '#__vikbooking_rooms');
				foreach ($rooms as $k => $c) {
					if ($showcatname) $rooms[$k]['catname'] = self::getCategoryName($c['idcat']);
					$custprice = self::getRoomParam('custprice', $c['params']);
					if (strlen($custprice) > 0 && (float)$custprice > 0.00) {
						$rooms[$k]['cost'] = (float)$custprice;
						$custpricetxt = self::getRoomParam('custpricetxt', $c['params']);
						$custpricetxt = empty($custpricetxt) ? '' : JText::_($custpricetxt);
						$rooms[$k]['custpricetxt'] = $custpricetxt;
					} else {
						$rooms[$k]['cost'] = 0;
						$q = "SELECT * FROM `#__vikbooking_dispcost` WHERE `idroom`=" . (int)$c['id'] . " ORDER BY `#__vikbooking_dispcost`.`days` ASC, `#__vikbooking_dispcost`.`cost` ASC";
						$dbo->setQuery($q, 0, 1);
						$dbo->execute();
						if ($dbo->getNumRows()) {
							$tar = $dbo->loadAssocList();
							/**
							 * We apply the today's rate by default for one night of stay.
							 * 
							 * @since 	1.3.5
							 */
							$tar = VikBooking::applySeasonsRoom($tar, $checkin_today_ts, $checkout_tomorrow_ts);
							//
							$rooms[$k]['cost'] = $tar[0]['cost'] / ($tar[0]['days'] > 1 ? $tar[0]['days'] : 1);
						}
					}
				}
			}
			if ($params->get('querycat') == 'price') {
				$rooms = self::sortRoomsByPrice($rooms, $params);
			}
		}
		return $rooms;
	}
	
	public static function getRoomParam ($paramname, $paramstr) {
		if (empty($paramstr)) return '';
		$paramarr = json_decode($paramstr, true);
		if (array_key_exists($paramname, $paramarr)) {
			return $paramarr[$paramname];
		}
		return '';
	}
	
	public static function sortRoomsByPrice($arr, $params) {
		$newarr = array ();
		foreach ($arr as $k => $v) {
			$newarr[$k] = $v['cost'];
		}
		asort($newarr);
		$sorted = array ();
		foreach ($newarr as $k => $v) {
			$sorted[$k] = $arr[$k];
		}
		return $params->get('order') == 'desc' ? array_reverse($sorted) : $sorted;
	}
	
	public static function getCategoryName($idcat) {
		self::importVboLib();
		$vbo_tn = VikBooking::getTranslator();
		$dbo = JFactory::getDBO();
		$q = "SELECT `id`,`name` FROM `#__vikbooking_categories` WHERE `id`='" . str_replace(";", "", $idcat) . "';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() < 1) {
			return '';
		}
		$p = $dbo->loadAssocList();
		$vbo_tn->translateContents($p, '#__vikbooking_categories');
		return $p[0]['name'];
	}
	
	public static function limitRes($rooms, $params) {
		return array_slice($rooms, 0, $params->get('numb'));
	}
	
	public static function numberFormat($numb) {
		self::importVboLib();
		return VikBooking::numberFormat($numb);
	}

	public static function importVboLib() {
		if (!class_exists('VikBooking') && is_file(VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'lib.vikbooking.php')) {
			require_once(VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'lib.vikbooking.php');
		}
	}
}
