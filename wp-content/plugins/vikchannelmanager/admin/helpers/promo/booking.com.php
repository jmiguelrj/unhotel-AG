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
 * Booking.com handler for the Promotions.
 */
class VikChannelManagerPromoBookingcom extends VikChannelManagerPromo
{
	/**
	 * Class constructor defines the name, the key and the logo.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->handler_name = 'Booking.com';
		$this->handler_key = basename(__FILE__, '.php');
		$this->handler_logo = VCM_ADMIN_URI . 'assets/css/channels/booking.png';
	}

	/**
	 * Whether this channel is active in VCM
	 * 
	 * @return 	boolean 	true if the channel has been configured, false otherwise.
	 */
	public function isActive()
	{
		$channel_info = VikChannelManager::getChannel(VikChannelManagerConfig::BOOKING);
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
			$this->setError('Promotions for Booking.com must apply discounts in percent values.');
			return false;
		}
		$inject_params['discount'] = $pdiffcost;
		
		// minimum stay
		if ($ppromominlos < 2) {
			// 1 means no minimum stay
			$ppromominlos = 1;
		}
		$inject_params['min_stay_through'] = $ppromominlos;

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

		// check the non-refundable option
		$set_non_ref = 0;
		$q = "SELECT `id` FROM `#__vikbooking_prices` WHERE " . (is_array($pidprices) && count($pidprices) ? '`id` IN (' . implode(', ', $pidprices) . ') AND ' : '') . " `free_cancellation`=1;";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if (!$this->dbo->getNumRows()) {
			// no refundable rate plans selected or available, set the promo to non-refundable
			$set_non_ref = 1;
		}
		$inject_params['non_ref'] = $set_non_ref;

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

		// guess the promotion type (basic by default)
		$promo_type = 'basic';
		if ($ppromodaysadv > 0) {
			// this will be an early bird promotion
			$promo_type = 'early_booker';
			$inject_params['early_booker_days'] = $ppromodaysadv;
			if (empty($pspname)) {
				// we use a generic name
				$pspname = 'Early Booker Promotion';
			}
		} elseif ($ppromolastmind > 0 || $ppromolastminh > 0) {
			// this is a last minute promotion
			$promo_type = 'last_minute';
			$inject_params['last_minute_days'] = $ppromolastmind;
			$inject_params['last_minute_hours'] = $ppromolastminh;
			if ($ppromolastmind > 0) {
				$inject_params['last_minute_unit'] = 'day';
			}
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
		$type = VikRequest::getString('promo_type', 'basic', 'request');
		$target_channel = VikRequest::getString('target_channel', 'public', 'request');
		$min_stay_through = VikRequest::getInt('min_stay_through', 0, 'request');
		$non_ref = VikRequest::getInt('non_ref', 0, 'request');
		$no_cc = VikRequest::getInt('no_cc', 0, 'request');

		// basic promo only
		$bookfromdate = VikRequest::getString('bookfromdate', '', 'request');
		$booktodate = VikRequest::getString('booktodate', '', 'request');
		$book_time = VikRequest::getInt('book_time', 0, 'request');
		$book_time_start = VikRequest::getInt('book_time_start', 0, 'request');
		$book_time_end = VikRequest::getInt('book_time_end', 0, 'request');
		// last minute promo only
		$last_minute_unit = VikRequest::getString('last_minute_unit', 'hour', 'request');
		$last_minute_days = VikRequest::getInt('last_minute_days', 0, 'request');
		$last_minute_hours = VikRequest::getInt('last_minute_hours', 0, 'request');
		// early booker promo only
		$early_booker_days = VikRequest::getInt('early_booker_days', 0, 'request');

		// geo rate promotion
		$geo_target_channel = VikRequest::getString('geo_target_channel', '', 'request');
		if ($type == 'geo_rate' && !empty($geo_target_channel)) {
			$target_channel = $geo_target_channel;
		}
		
		// stay dates are for all promos
		$stayfromdate = VikRequest::getString('stayfromdate', '', 'request');
		$staytodate = VikRequest::getString('staytodate', '', 'request');
		$wdays = VikRequest::getVar('wdays', array(), 'request', 'array');
		$wdays = !count($wdays) || empty($wdays[0]) ? array() : $wdays;
		//excluded dates
		$pexcluded_dates = explode(';', VikRequest::getString('excluded_dates', '', 'request'));
		$excluded_dates = array();
		foreach ($pexcluded_dates as $v) {
			if (!empty($v) && strlen($v) == 10) {
				array_push($excluded_dates, $v);
			}
		}
		// rooms and rate plans
		$rooms = VikRequest::getVar('rooms', array(), 'request', 'array');
		$rplans = VikRequest::getVar('rplans', array(), 'request', 'array');
		// discount
		$discount = VikRequest::getInt('discount', 0, 'request');

		// some rooms must be set, while it is not necessary to set some rate plans for VBO
		if (!count($rooms) || empty($rooms[0])) {
			$this->setError('Please select at least one room.');
			return false;
		}
		$rplans = !is_array($rplans) ? array() : $rplans;

		/**
		 * Obtain the Booking.com Hotel ID, only one per request.
		 * We also build the list of rooms and rate plans, if necessary
		 * because VBO may only pass the IDs of the VBO rooms.
		 * VCM instead will pass the IDs of Booking.com.
		 */
		$bcom_hotel_id = null;
		$involved_rooms = array();
		$involved_rplans = array();
		if (is_array($data) && !empty($data['hotelid'])) {
			// VCM will pass the Hotel ID to use, rooms and rate plans
			$bcom_hotel_id = $data['hotelid'];
			$involved_rooms = $rooms;
			$involved_rplans = $rplans;
		} else {
			// VBO will not pass the Hotel ID, just an array of rooms
			$q = "SELECT * FROM `#__vikchannelmanager_roomsxref` WHERE `idroomvb` IN (" . implode(', ', $rooms) . ") AND `idchannel`=" . VikChannelManagerConfig::BOOKING . ";";
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
				if (is_null($bcom_hotel_id)) {
					// the first room requested and mapped will be used for the Hotel ID
					$bcom_hotel_id = $prop_params['hotelid'];
				}
				if ($prop_params['hotelid'] != $bcom_hotel_id) {
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
					// collect the Booking.com rate plan ID(s) to update
					foreach ($rplans as $vborplan) {
						if (!isset($bulk_rates_cache[$xref['idroomvb']][$vborplan]) || !isset($bulk_rates_cache[$xref['idroomvb']][$vborplan]['rplans']) || !isset($bulk_rates_cache[$xref['idroomvb']][$vborplan]['rplans'][VikChannelManagerConfig::BOOKING])) {
							continue;
						}
						if (!in_array($bulk_rates_cache[$xref['idroomvb']][$vborplan]['rplans'][VikChannelManagerConfig::BOOKING], $rplans_found)) {
							array_push($rplans_found, $bulk_rates_cache[$xref['idroomvb']][$vborplan]['rplans'][VikChannelManagerConfig::BOOKING]);
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
		if (!count($involved_rooms)) {
			$this->setError('No rooms for the promotion.');
			return false;
		}
		if (!count($involved_rplans)) {
			$this->setError('No rate plans for the promotion.');
			return false;
		}
		if (empty($discount) || $discount <= 0) {
			$this->setError('Please enter a discount amount.');
			return false;
		}
		if (empty($name)) {
			$name = 'Promotion ' . date('Ymd', strtotime($stayfromdate));
		}
		/**
		 * Sanitize promotion name to avoid error 400 "deal name contains invalid characters"
		 */
		$name = preg_replace("/[^a-zA-Z0-9 ]+/", '', $name);
		//
		$name = htmlspecialchars((strlen($name) > 20 ? substr($name, 0, 20) : $name));
		if (($type == 'basic' || $type == 'geo_rate' || $type == 'mobile_rate' || $type == 'business_booker') && !empty($bookfromdate) && !empty($booktodate)) {
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
		if ($method == 'edit' && empty($promoid)) {
			// update (edit) must provide the Promotion ID
			$this->setError('Missing Promotion ID for update.');
			return false;
		}

		// make the request to e4jConnect to write the Promotion
			
		// required filter by hotel ID
		$filters = array('hotelid="'.$bcom_hotel_id.'"');
		// other filters
		if (!empty($promoid)) {
			$filters[] = 'action="'.$method.'"';
			$filters[] = 'promoid="'.$promoid.'"';
		} else {
			$filters[] = 'action="new"';
		}

		// promotion attributes
		$promo_attr = array();
		if (!empty($promoid)) {
			$promo_attr[] = 'id="'.$promoid.'"';
		}
		$promo_attr[] = 'name="'.$name.'"';
		$promo_attr[] = 'type="'.$type.'"';
		$promo_attr[] = 'target_channel="'.$target_channel.'"';
		if (!empty($min_stay_through) && $min_stay_through > 0) {
			$promo_attr[] = 'min_stay_through="'.$min_stay_through.'"';
		}
		$promo_attr[] = 'non_refundable="'.$non_ref.'"';
		$promo_attr[] = 'no_cc_promotion="'.$no_cc.'"';

		// conditional nodes
		$condit_nodes = array();
		if (($type == 'basic' || $type == 'geo_rate' || $type == 'mobile_rate' || $type == 'business_booker') && !empty($bookfromdate) && !empty($booktodate)) {
			array_push($condit_nodes, '<book_date start="'.$bookfromdate.'" end="'.$booktodate.'" />');
		}
		if (($type == 'basic' || $type == 'geo_rate' || $type == 'mobile_rate' || $type == 'business_booker') && $book_time > 0) {
			array_push($condit_nodes, '<book_time start="'.$book_time_start.'" end="'.$book_time_end.'" />');
		}
		if ($type == 'last_minute') {
			array_push($condit_nodes, '<last_minute unit="'.$last_minute_unit.'" value="'.($last_minute_unit == 'hour' ? $last_minute_hours : $last_minute_days).'" />');
		}
		if ($type == 'early_booker') {
			array_push($condit_nodes, '<early_booker value="'.$early_booker_days.'" />');
		}

		// stay dates can be a single node, or it contains children nodes for active_weekdays and excluded_dates
		$stay_dates = '<stay_date start="'.$stayfromdate.'" end="'.$staytodate.'"';
		if (count($wdays) || count($excluded_dates)) {
			// children nodes
			$stay_dates .= '>'."\n";
			if (count($wdays)) {
				$stay_dates .= '<active_weekdays>'."\n";
				foreach ($wdays as $wday) {
					$stay_dates .= '<active_weekday>'.$wday.'</active_weekday>'."\n";
				}
				$stay_dates .= '</active_weekdays>'."\n";
			}
			if (count($excluded_dates)) {
				$stay_dates .= '<excluded_dates>'."\n";
				foreach ($excluded_dates as $exd) {
					$stay_dates .= '<excluded_date>'.$exd.'</excluded_date>'."\n";
				}
				$stay_dates .= '</excluded_dates>'."\n";
			}
			// close node
			$stay_dates .= '</stay_date>';
		} else {
			// single node
			$stay_dates .= ' />';
		}

		// rooms and rate plans
		$rooms_rates = '<rooms>'."\n";
		foreach ($involved_rooms as $r) {
			$rooms_rates .= '<room id="'.$r.'"/>'."\n";
		}
		$rooms_rates .= '</rooms>'."\n";
		$rooms_rates .= '<parent_rates>'."\n";
		foreach ($involved_rplans as $r) {
			$rooms_rates .= '<parent_rate id="'.$r.'"/>'."\n";
		}
		$rooms_rates .= '</parent_rates>';

		$e4jc_url = "https://slave.e4jconnect.com/channelmanager/?r=wprom&c=booking.com";
		
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager WPROM Request e4jConnect.com - Booking.com -->
<WritePromotionRQ xmlns="http://www.e4jconnect.com/channels/wpromrq">
	<Notify client="'.JUri::root().'"/>
	<Api key="'.$apikey.'"/>
	<Fetch '.implode(' ', $filters).'/>
	<WritePromotion '.implode(' ', $promo_attr).'>
		'.implode("\n", $condit_nodes).'
		'.$stay_dates.'
		'.$rooms_rates.'
		<discount value="'.$discount.'" />
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
