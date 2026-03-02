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
 * Expedia handler for the Promotions.
 */
class VikChannelManagerPromoExpedia extends VikChannelManagerPromo
{
	/**
	 * Class constructor defines the name, the key and the logo.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->handler_name = 'Expedia';
		$this->handler_key = basename(__FILE__, '.php');
		$this->handler_logo = VCM_ADMIN_URI . 'assets/css/channels/expedia.png';
	}

	/**
	 * Whether this channel is active in VCM
	 * 
	 * @return 	boolean 	true if the channel has been configured, false otherwise.
	 */
	public function isActive()
	{
		$channel_info = VikChannelManager::getChannel(VikChannelManagerConfig::EXPEDIA);
		if (!is_array($channel_info) || !count($channel_info) || empty($channel_info['params'])) {
			return false;
		}
		
		$params = json_decode($channel_info['params'], true);

		if (!$params || empty($params['hotelid'])) {
			return false;
		}

		return true;
	}

	/**
	 * Gets the name of this promotion handler
	 * 
	 * @return 	string 	the name of this promotion handler.
	 */
	public function getName()
	{
		return $this->handler_name;
	}

	/**
	 * Gets the key of this promotion handler
	 * 
	 * @return 	string 	the key of this promotion handler.
	 */
	public function getKey()
	{
		return $this->handler_key;
	}

	/**
	 * Gets the URI to the logo of this promotion handler
	 * 
	 * @return 	string 	the logo URI of this promotion handler.
	 */
	public function getLogoUri()
	{
		return $this->handler_logo;
	}

	/**
	 * Prepares the method that will save the promotion.
	 * Useful when VBO is creating a new promo also for this channel.
	 * 
	 * @param 	mixed 	$params 	instructions for preparing the request vars.
	 * 
	 * @return 	boolean 			if false, the promotion should not be created.
	 */
	protected function prepareSavePromotion($params)
	{
		if ($params['method'] != 'new') {
			// we do not need to do anything when updating a promotion
			return true;
		}
		$channels = VikRequest::getVar('channels', array(), 'request', 'array');
		if (empty($channels) || !is_array($channels) || !count($channels)) {
			// VCM will not set this vars, only VBO will do it, so we continue without injecting parameters
			return true;
		}

		// params to be injected
		$inject_params = array();

		// adjust the VBO request vars to the createPromotion structure
		$pfrom = VikRequest::getString('from', '', 'request');
		$pto = VikRequest::getString('to', '', 'request');
		$ptype = VikRequest::getInt('type', 2, 'request');
		$pdiffcost = VikRequest::getFloat('diffcost', '', 'request');
		$pidrooms = VikRequest::getVar('idrooms', array(), 'request', 'array');
		$pidprices = VikRequest::getVar('idprices', array(), 'request', 'array');
		$pwdays = VikRequest::getVar('wdays', array(), 'request', 'array');
		$pspname = VikRequest::getString('spname', '', 'request');
		$pval_pcent = VikRequest::getInt('val_pcent', 2, 'request');
		$ppromodaysadv = VikRequest::getInt('promodaysadv', 0, 'request');
		$ppromominlos = VikRequest::getInt('promominlos', 0, 'request');
		$ppromolastmind = VikRequest::getInt('promolastmind', 0, 'request');
		$ppromolastminh = VikRequest::getInt('promolastminh', 0, 'request');

		// validation and params injection
		if ($pval_pcent != 2 || $ptype != 2 || $pdiffcost <= 0) {
			// only percent values > 0 for the discount
			$this->setError('Promotions for Expedia must apply discounts in percent values.');
			return false;
		}
		$inject_params['discount'] = $pdiffcost;
		
		// minimum stay
		if ($ppromominlos < 2) {
			// 1 means no minimum stay
			$ppromominlos = 1;
		}
		$inject_params['min_stay'] = $ppromominlos;

		// stay dates
		if (empty($pfrom) || empty($pto)) {
			// dates are required
			$this->setError('Validity dates cannot be empty.');
			return false;
		}
		$tsfrom = VikBooking::getDateTimestamp($pfrom, 0, 0, 0);
		$tsto = VikBooking::getDateTimestamp($pto, 23, 59, 59);
		$inject_params['stayfromdate'] = date('Y-m-d', $tsfrom);
		$inject_params['staytodate'] = date('Y-m-d', $tsto);

		// rooms and rate plans are set from the VBO selection
		$inject_params['rooms'] = $pidrooms;
		$inject_params['rplans'] = $pidprices;

		// active week days
		$active_wdays = array(
			'Sun',
			'Mon',
			'Tue',
			'Wed',
			'Thu',
			'Fri',
			'Sat',
		);
		if (count($pwdays) && strlen($pwdays[0]) && count($pwdays) < 7) {
			// some week days have been selected in VBO, unset the non-selected ones
			for ($i = 0; $i < 7 ; $i++) { 
				if (!in_array($i, $pwdays) && isset($active_wdays[$i])) {
					unset($active_wdays[$i]);
				}
			}
		}
		$inject_params['wdays'] = $active_wdays;

		// guess the promotion type (BASIC_PROMOTION by default)
		$promo_type = 'BASIC_PROMOTION';
		if ($ppromodaysadv > 0) {
			// this will be an early bird promotion
			$promo_type = 'EARLY_BOOKING_PROMOTION';
			$inject_params['adv_book_days_min'] = $ppromodaysadv;
			if (empty($pspname)) {
				// we use a generic name
				$pspname = 'Early Booking Promotion';
			}
		} elseif ($ppromolastmind > 0 || $ppromolastminh > 0) {
			/**
			 * This is a last minute promotion (same-day promotion).
			 * According to the docs, minAdvanceBookingDays and maxAdvanceBookingDays
			 * should be set to 0, bookingLocalDateTimeXXX and travelDateXXX fields
			 * should be set to the same value to allow the promotion on the same day.
			 * sameDayBookingStartTime field is mandatory when creating a SAME_DAY_PROMOTION.
			 */
			$promo_type = 'SAME_DAY_PROMOTION';
			// set advance days to 0
			$inject_params['adv_book_days_min'] = 0;
			$inject_params['adv_book_days_max'] = 0;
			// set booking local date time to the same as the travel dates
			$inject_params['bookfromdate'] = date('Y-m-d', $tsfrom);
			$inject_params['booktodate'] = date('Y-m-d', $tsto);
			$inject_params['book_time_h_start'] = date('G', $tsfrom);
			$inject_params['book_time_m_start'] = (int)date('i', $tsfrom);
			$inject_params['book_time_h_end'] = date('G', $tsto);
			$inject_params['book_time_m_end'] = (int)date('i', $tsto);
			// set sameDayBookingStartTime
			$inject_params['sameday_booktime'] = '00:00:00';
			if (empty($pspname)) {
				// we use a generic name
				$pspname = 'Last Minute Promotion';
			}
		}
		$inject_params['promo_type'] = $promo_type;

		// promotion name
		if (empty($pspname)) {
			// we use a generic name
			$pspname = 'Promotion';
		}
		$inject_params['name'] = $pspname;

		// inject the preparred params and return true
		$this->injectParams($inject_params);

		return true;
	}

	/**
	 * Creates or updates a promotion. This method can be used by VBO
	 * when creating a promotion also on this channel, or by the VCM
	 * controller dedicated to the promotions for this channel.
	 * VCM can create or update, while VBO only creates.
	 * VCM may call this method during an AJAX request. If this channel
	 * was not selected in VBO, then this method will not be called.
	 * 
	 * @param 	mixed 	$data 		array of information or null.
	 * @param 	mixed 	$method 	either edit or new.
	 * 
	 * @return 	boolean 			true on success, false otherwise.
	 */
	public function createPromotion($data, $method)
	{
		$method = $method == 'edit' ? 'edit' : 'new';

		$proceed = $this->prepareSavePromotion(array(
			'data' 	 => $data,
			'method' => $method,
		));
		if (!$proceed) {
			// we expect some errors to be set, but the promotion cannot be created
			return false;
		}

		// configuration fields validation
		if (!function_exists('curl_init')) {
			$this->setError(VikChannelManager::getErrorFromMap('e4j.error.Curl'));
			return false;
		}
		$apikey = VikChannelManager::getApiKey(true);
		if (empty($apikey)) {
			$this->setError(VikChannelManager::getErrorFromMap('e4j.error.Settings'));
			return false;
		}

		// promotion fields
		$promoid = VikRequest::getString('promoid', '', 'request');
		$name = VikRequest::getString('name', '', 'request');
		$type = VikRequest::getString('promo_type', 'BASIC_PROMOTION', 'request');

		// promotion status
		$promo_status = VikRequest::getInt('promo_status_active', 1, 'request');

		// min/max LOS + members promo
		$members_only = VikRequest::getString('members_only', 'public', 'request');
		$min_stay = VikRequest::getInt('min_stay', 0, 'request');
		$max_stay = VikRequest::getInt('max_stay', 0, 'request');

		// booking dates allowed
		$bookfromdate = VikRequest::getString('bookfromdate', '', 'request');
		$booktodate = VikRequest::getString('booktodate', '', 'request');
		$book_time_h_start = VikRequest::getInt('book_time_h_start', 0, 'request');
		$book_time_m_start = VikRequest::getInt('book_time_m_start', 0, 'request');
		$book_time_h_end = VikRequest::getInt('book_time_h_end', 23, 'request');
		$book_time_m_end = VikRequest::getInt('book_time_m_end', 59, 'request');

		// min/max booking days in advance
		$adv_book_days_min = VikRequest::getInt('adv_book_days_min', 0, 'request');
		$adv_book_days_max = VikRequest::getInt('adv_book_days_max', 0, 'request');
		
		// travel dates
		$stayfromdate = VikRequest::getString('stayfromdate', '', 'request');
		$staytodate = VikRequest::getString('staytodate', '', 'request');
		
		// week-days enabled and excluded dates
		$wdays = VikRequest::getVar('wdays', array(), 'request', 'array');
		$wdays = !count($wdays) || empty($wdays[0]) ? array() : $wdays;
		$pexcluded_dates = explode(';', VikRequest::getString('excluded_dates', '', 'request'));
		$excluded_dates = array();
		foreach ($pexcluded_dates as $v) {
			if (!empty($v) && strlen($v) == 10) {
				array_push($excluded_dates, $v);
			}
		}
		if (count($wdays) > 0 && count($wdays) < 7) {
			// only some weekdays enabled, so add the excluded dates in the range
			$from_info = getdate(strtotime($stayfromdate));
			$to_info = getdate(strtotime($staytodate));
			if (is_array($from_info) && !empty($from_info[0]) && !empty($to_info[0]) && $from_info[0] < $to_info[0]) {
				while ($from_info[0] <= $to_info[0]) {
					if (!isset($wdays[$from_info['wday']])) {
						// this week day is not enabled, so push this as an excluded date
						$add_excl_date = date('Y-m-d', $from_info[0]);
						if (!in_array($add_excl_date, $excluded_dates)) {
							array_push($excluded_dates, $add_excl_date);
						}
					}
					$from_info = getdate(mktime(0, 0, 0, $from_info['mon'], ($from_info['mday'] + 1), $from_info['year']));
				}
			}
		}

		// rooms and rate plans
		$rooms = VikRequest::getVar('rooms', array(), 'request', 'array');
		$rplans = VikRequest::getVar('rplans', array(), 'request', 'array');
		$rplans = !is_array($rplans) ? array() : $rplans;

		// some rooms must be set, while it is not necessary to set some rate plans for VBO
		if ((!count($rooms) || empty($rooms[0])) && (!count($rplans) || empty($rplans[0]))) {
			// VCM would have passed directly some rate plan IDs
			$this->setError('Please select at least one room/rate plan.');
			return false;
		}

		/**
		 * Obtain the Expedia Hotel ID, only one per request.
		 * We also build the list of rooms and rate plans, if necessary
		 * because VBO may only pass the IDs of the VBO rooms.
		 * VCM instead will pass the rate plan IDs of Expedia.
		 */
		$egroup_hotel_id = null;
		$egroup_hotel_uname = null;
		$egroup_hotel_pwd = null;
		$involved_rooms = array();
		$involved_rplans = array();
		if (is_array($data) && !empty($data['hotelid'])) {
			// VCM will pass the Hotel ID to use, and rate plans, no rooms are needed for Expedia
			$egroup_hotel_id = $data['hotelid'];
			if (isset($data['username'])) {
				// test properties may have this parameter
				$egroup_hotel_uname = $data['username'];
			}
			if (isset($data['password'])) {
				// test properties may have this parameter
				$egroup_hotel_pwd = $data['password'];
			}
			$involved_rplans = $rplans;
		} else {
			// VBO will not pass the Hotel ID, just an array of rooms
			if (!count($rooms)) {
				$this->setError('Please select at least one room.');
				return false;
			}
			$q = "SELECT * FROM `#__vikchannelmanager_roomsxref` WHERE `idroomvb` IN (" . implode(', ', $rooms) . ") AND `idchannel`=" . VikChannelManagerConfig::EXPEDIA . ";";
			$this->dbo->setQuery($q);
			$this->dbo->execute();
			if (!$this->dbo->getNumRows()) {
				$this->setError('No rooms mapped for ' . $this->getName());
				return false;
			}
			$roomsxref = $this->dbo->loadAssocList();
			$bulk_rates_cache = VikChannelManager::getBulkRatesCache();
			foreach ($roomsxref as $xref) {
				$prop_params = json_decode($xref['prop_params'], true);
				if (!$prop_params || empty($prop_params['hotelid'])) {
					continue;
				}
				if (is_null($egroup_hotel_id)) {
					// the first room requested and mapped will be used for the Hotel ID
					$egroup_hotel_id = $prop_params['hotelid'];
					$egroup_hotel_uname = isset($prop_params['username']) ? $prop_params['username'] : $egroup_hotel_uname;
					$egroup_hotel_pwd = isset($prop_params['password']) ? $prop_params['password'] : $egroup_hotel_pwd;
				}
				if ($prop_params['hotelid'] != $egroup_hotel_id) {
					// only one Hotel ID per promotion, skip this room
					continue;
				}
				if (in_array($xref['idroomota'], $involved_rooms)) {
					// skip this room already pushed
					continue;
				}
				// push this room
				array_push($involved_rooms, $xref['idroomota']);
				// find the information for the mapped rate plan for this room
				$xref['idroomvb'] = (string)$xref['idroomvb'];
				$roomrplans = json_decode($xref['otapricing'], true);
				$rplans_found = array();
				if (isset($bulk_rates_cache[$xref['idroomvb']]) && count($rplans)) {
					// check the bulk rates cache for this room to find the rate plan
					// collect the Expedia rate plan ID(s) to update
					foreach ($rplans as $vborplan) {
						if (!isset($bulk_rates_cache[$xref['idroomvb']][$vborplan]) || !isset($bulk_rates_cache[$xref['idroomvb']][$vborplan]['rplans']) || !isset($bulk_rates_cache[$xref['idroomvb']][$vborplan]['rplans'][VikChannelManagerConfig::EXPEDIA])) {
							continue;
						}
						if (!in_array($bulk_rates_cache[$xref['idroomvb']][$vborplan]['rplans'][VikChannelManagerConfig::EXPEDIA], $rplans_found)) {
							array_push($rplans_found, $bulk_rates_cache[$xref['idroomvb']][$vborplan]['rplans'][VikChannelManagerConfig::EXPEDIA]);
						}
					}
				}
				if (!count($rplans_found)) {
					// grab the first rate plan from the mapping information
					if (isset($roomrplans['RatePlan'])) {
						foreach ($roomrplans['RatePlan'] as $rplanid => $rplan_val) {
							array_push($involved_rplans, $rplanid);
							break;
						}
					}
				} else {
					$involved_rplans = array_merge($involved_rplans, $rplans_found);
				}
			}
		}

		// validation for the creation and modification of the promotions
		if (!count($involved_rplans)) {
			$this->setError('No rate plans for the promotion.');
			return false;
		}

		// discount(s), applicable nights and recurring, extra discount for members
		$discount = VikRequest::getFloat('discount', 0, 'request');
		$appl_night = VikRequest::getInt('appl_night', 0, 'request');
		$appl_night_recur = VikRequest::getInt('appl_night_recur', 0, 'request');
		$memb_extra_disc = VikRequest::getFloat('memb_extra_disc', 0, 'request');
		// day of week discounts
		$dow_discount = false;
		$dow_enabled = VikRequest::getInt('dow_enabled', 0, 'request');
		$discount_mon = VikRequest::getFloat('discount_mon', 0, 'request');
		$discount_tue = VikRequest::getFloat('discount_tue', 0, 'request');
		$discount_wed = VikRequest::getFloat('discount_wed', 0, 'request');
		$discount_thu = VikRequest::getFloat('discount_thu', 0, 'request');
		$discount_fri = VikRequest::getFloat('discount_fri', 0, 'request');
		$discount_sat = VikRequest::getFloat('discount_sat', 0, 'request');
		$discount_sun = VikRequest::getFloat('discount_sun', 0, 'request');
		if ($dow_enabled) {
			// dow_enabled must be set to 1 to enable the day-of-week discounts
			if ($discount_mon > 0 || $discount_tue > 0 || $discount_wed > 0 || $discount_thu > 0 || $discount_fri > 0 || $discount_sat > 0 || $discount_sun > 0) {
				$dow_discount = true;
			}
		}

		if ((empty($discount) || $discount <= 0) && !$dow_discount) {
			$this->setError('Please enter a discount amount.');
			return false;
		}
		if (empty($name)) {
			$name = 'Promotion ' . date('Ymd', strtotime($stayfromdate));
		}
		/**
		 * Sanitize promotion name to avoid errors with invalid characters
		 */
		$name = preg_replace("/[^a-zA-Z0-9 ]+/", '', $name);
		//
		$name = htmlspecialchars((strlen($name) > 20 ? substr($name, 0, 20) : $name));
		if (!empty($bookfromdate) && !empty($booktodate)) {
			$from = strtotime($bookfromdate);
			$to = strtotime($booktodate);
			if ($from > $to || empty($from)) {
				$this->setError('Invalid bookable dates.');
				return false;
			}
		}
		if (empty($stayfromdate) || empty($staytodate)) {
			$this->setError('Dates of stay cannot be empty.');
			return false;
		}
		$from = strtotime($stayfromdate);
		$to = strtotime($staytodate);
		if ($from > $to || empty($from)) {
			$this->setError('Invalid stay dates.');
			return false;
		}
		if ($method != 'new' && empty($promoid)) {
			// update (edit) must provide the Promotion ID
			$this->setError('Missing Promotion ID for update.');
			return false;
		}

		// make the request to e4jConnect to write the Promotion
			
		// required filter by hotel ID
		$filters = array('hotelid="'.$egroup_hotel_id.'"');
		// other filters
		if (!empty($promoid)) {
			$filters[] = 'action="'.$method.'"';
			$filters[] = 'promoid="'.$promoid.'"';
		} else {
			$filters[] = 'action="new"';
		}
		// additional filters for the authentication of test properties
		if (!empty($egroup_hotel_uname)) {
			$filters[] = 'name="'.$egroup_hotel_uname.'"';
		}
		if (!empty($egroup_hotel_pwd)) {
			$filters[] = 'auth="'.$egroup_hotel_pwd.'"';
		}

		// promotion attributes
		$promo_attr = array();
		if (!empty($promoid)) {
			$promo_attr[] = 'id="'.$promoid.'"';
		}
		$promo_attr[] = 'name="'.$name.'"';
		$promo_attr[] = 'type="'.$type.'"';
		$promo_attr[] = 'target_channel="'.$members_only.'"';
		if (!empty($min_stay) && $min_stay > 0) {
			$min_stay = $min_stay > 28 ? 28 : $min_stay;
			$promo_attr[] = 'min_stay_through="'.$min_stay.'"';
		}
		if (!empty($max_stay) && $max_stay > 0 && $max_stay >= $min_stay) {
			$max_stay = $max_stay > 28 ? 28 : $max_stay;
			$promo_attr[] = 'max_stay_through="'.$max_stay.'"';
		}

		// conditional nodes
		$condit_nodes = array();
		if (!empty($bookfromdate) && !empty($booktodate)) {
			// book start and end date + time (in seconds)
			array_push($condit_nodes, '<book_date start="'.$bookfromdate.'" end="'.$booktodate.'" />');
			$booktime_start_total = ($book_time_h_start * 3600) + ($book_time_m_start * 60);
			$booktime_end_total = ($book_time_h_end * 3600) + ($book_time_m_end * 60);
			$booktime_end_total += $book_time_h_end == 23 && $book_time_m_end == 59 ? 59 : 0;
			array_push($condit_nodes, '<book_time start="'.$booktime_start_total.'" end="'.$booktime_end_total.'" />');
		}

		// extra nodes
		$extra_nodes = array();
		if ($adv_book_days_min > 0 || $adv_book_days_max > 0 || $type == 'SAME_DAY_PROMOTION') {
			// booking days in advance
			$adv_book_days_min = $adv_book_days_min < 0 ? 0 : $adv_book_days_min;
			$adv_book_days_max = $adv_book_days_max < 0 ? 0 : $adv_book_days_max;
			$adv_book_days_min = $adv_book_days_min > 500 ? 500 : $adv_book_days_min;
			$adv_book_days_max = $adv_book_days_max > 500 ? 500 : $adv_book_days_max;
			array_push($extra_nodes, '<extras key="minAdvanceBookingDays" value="'.$adv_book_days_min.'" />');
			array_push($extra_nodes, '<extras key="maxAdvanceBookingDays" value="'.$adv_book_days_max.'" />');
		}
		if ($type == 'SAME_DAY_PROMOTION') {
			// field sameDayBookingStartTime is mandatory when SAME_DAY_PROMOTION
			$sameday_booktime = VikRequest::getString('sameday_booktime', '', 'request');
			$sameday_booktime = empty($sameday_booktime) ? '00:00:00' : $sameday_booktime;
			array_push($extra_nodes, '<extras key="sameDayBookingStartTime" value="'.$sameday_booktime.'" />');
		}
		// dow discounts are placed in the extras nodes
		if ($dow_discount === true) {
			if ($discount_mon > 0 ) {
				array_push($extra_nodes, '<extras key="dow_discount_mon" value="'.$discount_mon.'" />');
			}
			if ($discount_tue > 0) {
				array_push($extra_nodes, '<extras key="dow_discount_tue" value="'.$discount_tue.'" />');
			}
			if ($discount_wed > 0) {
				array_push($extra_nodes, '<extras key="dow_discount_wed" value="'.$discount_wed.'" />');
			}
			if ($discount_thu > 0) {
				array_push($extra_nodes, '<extras key="dow_discount_thu" value="'.$discount_thu.'" />');
			}
			if ($discount_fri > 0) {
				array_push($extra_nodes, '<extras key="dow_discount_fri" value="'.$discount_fri.'" />');
			}
			if ($discount_sat > 0) {
				array_push($extra_nodes, '<extras key="dow_discount_sat" value="'.$discount_sat.'" />');
			}
			if ($discount_sun > 0) {
				array_push($extra_nodes, '<extras key="dow_discount_sun" value="'.$discount_sun.'" />');
			}
		}
		// applicable nights and recurring for multi-night promotions
		if ($appl_night > 0) {
			array_push($extra_nodes, '<extras key="applicableNight" value="'.$appl_night.'" />');
			array_push($extra_nodes, '<extras key="applicableNightRecurring" value="'.$appl_night_recur.'" />');
		}
		// extra discount for members
		if ($memb_extra_disc > 0) {
			array_push($extra_nodes, '<extras key="memberOnlyAdditionalValue" value="'.$memb_extra_disc.'" />');
		}
		// promotion status
		array_push($extra_nodes, '<extras key="promoStatus" value="' . ($promo_status > 0 ? 'ACTIVE' : 'INACTIVE') . '" />');

		// stay dates can be a single node, or it contains children nodes for excluded_dates
		$stay_dates = '<stay_date start="'.$stayfromdate.'" end="'.$staytodate.'"';
		if (count($excluded_dates)) {
			// children nodes
			$stay_dates .= '>'."\n";
			$stay_dates .= '<excluded_dates>'."\n";
			foreach ($excluded_dates as $exd) {
				$stay_dates .= '<excluded_date>'.$exd.'</excluded_date>'."\n";
			}
			$stay_dates .= '</excluded_dates>'."\n";
			// close node
			$stay_dates .= '</stay_date>';
		} else {
			// single node
			$stay_dates .= ' />';
		}

		// eligible rate plans
		$rooms_rates = '<parent_rates>'."\n";
		foreach ($involved_rplans as $r) {
			$rooms_rates .= '<parent_rate id="'.$r.'"/>'."\n";
		}
		$rooms_rates .= '</parent_rates>';

		$e4jc_url = "https://slave.e4jconnect.com/channelmanager/?r=wprom&c=expedia";
		
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager WPROM Request e4jConnect.com - Expedia -->
<WritePromotionRQ xmlns="http://www.e4jconnect.com/channels/wpromrq">
	<Notify client="'.JUri::root().'"/>
	<Api key="'.$apikey.'"/>
	<Fetch '.implode(' ', $filters).'/>
	<WritePromotion '.implode(' ', $promo_attr).'>
		'.implode("\n", $condit_nodes).'
		'.$stay_dates.'
		'.$rooms_rates.'
		<discount value="'.$discount.'" />
		'.implode("\n", $extra_nodes).'
	</WritePromotion>
</WritePromotionRQ>';

		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		$e4jC->slaveEnabled = true;
		$rs = $e4jC->exec();

		if ($e4jC->getErrorNo()) {
			$this->setError(@curl_error($e4jC->getCurlHeader()));
			return false;
		}
		if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			$this->setError(VikChannelManager::getErrorFromMap($rs));
			return false;
		}
		if (strpos($rs, 'e4j.ok') === false) {
			$this->setError('Invalid response received. ' . $rs);
			return false;
		}

		/**
		 * Store/update the record on the db for this promotion.
		 * 
		 * @since 	1.8.4
		 */
		$this->channelPromotionCompleted(str_replace('e4j.ok.', '', $rs), $method, $data);

		// set the response from e4jConnect and return true
		$this->setResponse($rs);

		return true;
	}
}
