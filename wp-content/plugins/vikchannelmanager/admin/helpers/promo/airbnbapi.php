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
 * Airbnb (API) handler for the Promotions.
 * 
 * @since 	1.8.0
 */
class VikChannelManagerPromoAirbnbapi extends VikChannelManagerPromo
{
	/**
	 * Class constructor defines the name, the key and the logo.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->handler_name = 'Airbnb';
		$this->handler_key = basename(__FILE__, '.php');
		$this->handler_logo = VCM_ADMIN_URI . 'assets/css/channels/airbnb.png';
	}

	/**
	 * Whether this channel is active in VCM
	 * 
	 * @return 	boolean 	true if the channel has been configured, false otherwise.
	 */
	public function isActive()
	{
		$channel_info = VikChannelManager::getChannel(VikChannelManagerConfig::AIRBNBAPI);
		if (!is_array($channel_info) || !count($channel_info) || empty($channel_info['params'])) {
			return false;
		}
		
		$params = json_decode($channel_info['params'], true);

		if (!$params || empty($params['user_id'])) {
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
			$this->setError('Promotions for Airbnb must apply discounts in percent values.');
			return false;
		}
		/**
		 * Discount (price_change) value could be also positive for type SEASONAL_ADJUSTMENT,
		 * but then it would actually apply a charge. Therefore, we only support negative values.
		 */
		$inject_params['discount'] = $pdiffcost - ($pdiffcost * 2);

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

		// inactive week days
		$inactive_wdays = array(
			'SUNDAY',
			'MONDAY',
			'TUESDAY',
			'WEDNESDAY',
			'THURSDAY',
			'FRIDAY',
			'SATURDAY',
		);
		if (count($pwdays) && strlen($pwdays[0]) && count($pwdays) < 7) {
			// some week days have been selected in VBO, unset the selected ones
			for ($i = 0; $i < 7 ; $i++) { 
				if (in_array($i, $pwdays) && isset($inactive_wdays[$i])) {
					// this day is active, so we unset it from the inactive list
					unset($inactive_wdays[$i]);
				}
			}
		}
		// inject CTA/CTD week days for VCM
		$cta_ctd_wdays = array_values($inactive_wdays);
		if (count($cta_ctd_wdays) > 0 && count($cta_ctd_wdays) < 7) {
			$inject_params['cta_wdays'] = $cta_ctd_wdays;
			$inject_params['ctd_wdays'] = $cta_ctd_wdays;
		}

		/**
		 * Guess the promotion type (SEASONAL_ADJUSTMENT by default).
		 * Seasonal Rule Groups of type SEASONAL_ADJUSTMENT do not
		 * support values for threshold_one.
		 */
		$promo_type = 'SEASONAL_ADJUSTMENT';
		if ($ppromodaysadv >= 28) {
			// this will be an early bird promotion
			$promo_type = 'BOOKED_BEYOND_AT_LEAST_X_DAYS';
			// threshold_one must be a multiple of 28 or 30 for early bird promos
			if ($ppromodaysadv <= 30) {
				$ppromodaysadv = $ppromodaysadv == 28 ? 28 : 30;
			} else {
				$oper = round($ppromodaysadv / 30, 0);
				$ppromodaysadv = 30 * $oper;
			}
			$inject_params['threshold_one'] = $ppromodaysadv;
			if (empty($pspname)) {
				// we use a generic name
				$pspname = 'Early Booking Promotion';
			}
		} elseif (($ppromolastmind > 0 && $ppromolastmind <= 28) || $ppromolastminh > 0) {
			/**
			 * This is a last minute promotion (BOOKED_WITHIN_AT_MOST_X_DAYS promotion).
			 */
			$promo_type = 'BOOKED_WITHIN_AT_MOST_X_DAYS';
			// set threshold_one to a value equal to or less than 28
			$inject_params['threshold_one'] = $ppromolastmind;
			if (empty($pspname)) {
				// we use a generic name
				$pspname = 'Last Minute Promotion';
			}
		} elseif ($ppromominlos > 9) {
			// this will be a long-term stay adjustment
			$promo_type = 'STAYED_AT_LEAST_X_DAYS';
			// set threshold_one to the minimum nights specified
			$inject_params['threshold_one'] = $ppromominlos;
			if (empty($pspname)) {
				// we use a generic name
				$pspname = 'Long-Term Stay Promotion';
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
		$type = VikRequest::getString('promo_type', 'SEASONAL_ADJUSTMENT', 'request');

		// promotion status (if 0 the promotion will be deleted as the status is not supported)
		$promo_status = VikRequest::getInt('promo_status_active', 1, 'request');

		// min booking days in advance (only for early bird BOOKED_BEYOND_AT_LEAST_X_DAYS)
		$adv_book_days_min = VikRequest::getInt('adv_book_days_min', 0, 'request');
		// max booking days in advance (only for last minute BOOKED_WITHIN_AT_MOST_X_DAYS)
		$adv_book_days_max = VikRequest::getInt('adv_book_days_max', 0, 'request');
		// min LOS (only for long-term stays so "multiple nights" with type STAYED_AT_LEAST_X_DAYS)
		$min_stay = VikRequest::getInt('min_stay', 0, 'request');
		
		// travel dates
		$stayfromdate = VikRequest::getString('stayfromdate', '', 'request');
		$staytodate = VikRequest::getString('staytodate', '', 'request');

		// discount amount
		$discount = VikRequest::getFloat('discount', 0, 'request');
		
		// ctd/cta week days
		$ctd_enabled = VikRequest::getInt('ctd_enabled', 0, 'request');
		$cta_enabled = VikRequest::getInt('cta_enabled', 0, 'request');
		$ctd_wdays = VikRequest::getVar('ctd_wdays', array(), 'request', 'array');
		$ctd_wdays = !count($ctd_wdays) || empty($ctd_wdays[0]) ? array() : $ctd_wdays;
		$cta_wdays = VikRequest::getVar('cta_wdays', array(), 'request', 'array');
		$cta_wdays = !count($cta_wdays) || empty($cta_wdays[0]) ? array() : $cta_wdays;
		if (!$ctd_enabled) {
			// checkbox must be flagged (input value should be set in case the "prepare" method is called)
			$ctd_wdays = array();
		}
		if (!$cta_enabled) {
			// checkbox must be flagged (input value should be set in case the "prepare" method is called)
			$cta_wdays = array();
		}

		// override max nights on some week days
		$max_nights_enabled = VikRequest::getInt('max_nights_enabled', 0, 'request');
		$max_nights_mon = $max_nights_enabled ? VikRequest::getInt('max_nights_mon', 0, 'request') : 0;
		$max_nights_tue = $max_nights_enabled ? VikRequest::getInt('max_nights_tue', 0, 'request') : 0;
		$max_nights_wed = $max_nights_enabled ? VikRequest::getInt('max_nights_wed', 0, 'request') : 0;
		$max_nights_thu = $max_nights_enabled ? VikRequest::getInt('max_nights_thu', 0, 'request') : 0;
		$max_nights_fri = $max_nights_enabled ? VikRequest::getInt('max_nights_fri', 0, 'request') : 0;
		$max_nights_sat = $max_nights_enabled ? VikRequest::getInt('max_nights_sat', 0, 'request') : 0;
		$max_nights_sun = $max_nights_enabled ? VikRequest::getInt('max_nights_sun', 0, 'request') : 0;

		// override min nights on some week days
		$min_nights_enabled = VikRequest::getInt('min_nights_enabled', 0, 'request');
		$min_nights_mon = $min_nights_enabled ? VikRequest::getInt('min_nights_mon', 0, 'request') : 0;
		$min_nights_tue = $min_nights_enabled ? VikRequest::getInt('min_nights_tue', 0, 'request') : 0;
		$min_nights_wed = $min_nights_enabled ? VikRequest::getInt('min_nights_wed', 0, 'request') : 0;
		$min_nights_thu = $min_nights_enabled ? VikRequest::getInt('min_nights_thu', 0, 'request') : 0;
		$min_nights_fri = $min_nights_enabled ? VikRequest::getInt('min_nights_fri', 0, 'request') : 0;
		$min_nights_sat = $min_nights_enabled ? VikRequest::getInt('min_nights_sat', 0, 'request') : 0;
		$min_nights_sun = $min_nights_enabled ? VikRequest::getInt('min_nights_sun', 0, 'request') : 0;

		// listings (VBO will inject rooms[] while VCM will post listing_ids[])
		$listing_ids = VikRequest::getVar('listing_ids', array(), 'request', 'array');
		$rooms = VikRequest::getVar('rooms', array(), 'request', 'array');
		$rplans = VikRequest::getVar('rplans', array(), 'request', 'array');
		$rplans = !is_array($rplans) ? array() : $rplans;

		// some rooms must be set
		if (!count($listing_ids) && !count($rooms)) {
			$this->setError('Please select at least one room/listing.');
			return false;
		}

		/**
		 * Obtain the Airbnb User ID, only one per request.
		 * We also build the list of rooms involved.
		 */
		$airbnb_user_id = null;
		$involved_rooms = array();
		$involved_rplans = array();
		if (is_array($data) && !empty($data['user_id'])) {
			// VCM will pass the User ID to use and rooms
			$airbnb_user_id = $data['user_id'];
			$involved_rooms = $listing_ids;
		} else {
			// VBO will not pass the User ID, just an array of rooms
			if (!count($rooms)) {
				$this->setError('Please select at least one room.');
				return false;
			}
			$q = "SELECT * FROM `#__vikchannelmanager_roomsxref` WHERE `idroomvb` IN (" . implode(', ', $rooms) . ") AND `idchannel`=" . VikChannelManagerConfig::AIRBNBAPI . ";";
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
				if (!$prop_params || empty($prop_params['user_id'])) {
					continue;
				}
				if (empty($airbnb_user_id)) {
					// the first room requested and mapped will be used as the User ID
					$airbnb_user_id = $prop_params['user_id'];
				}
				if ($prop_params['user_id'] != $airbnb_user_id) {
					// only one User ID per promotion, skip this room
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
					// collect the Airbnb rate plan ID(s) to update
					foreach ($rplans as $vborplan) {
						if (!isset($bulk_rates_cache[$xref['idroomvb']][$vborplan]) || !isset($bulk_rates_cache[$xref['idroomvb']][$vborplan]['rplans']) || !isset($bulk_rates_cache[$xref['idroomvb']][$vborplan]['rplans'][VikChannelManagerConfig::AIRBNBAPI])) {
							continue;
						}
						if (!in_array($bulk_rates_cache[$xref['idroomvb']][$vborplan]['rplans'][VikChannelManagerConfig::AIRBNBAPI], $rplans_found)) {
							array_push($rplans_found, $bulk_rates_cache[$xref['idroomvb']][$vborplan]['rplans'][VikChannelManagerConfig::AIRBNBAPI]);
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
		if (empty($airbnb_user_id) || !count($involved_rooms)) {
			$this->setError('No valid rooms for the promotion.');
			return false;
		}

		// discount validation
		if (empty($discount)) {
			$this->setError('Please enter a discount amount.');
			return false;
		}
		// discount amount must be negative, or it would mean a charge
		if ($discount > 0) {
			$discount = ($discount - ($discount * 2));
		}

		/**
		 * Sanitize promotion name to avoid errors with invalid characters
		 */
		if (empty($name)) {
			$name = 'Promotion ' . date('Ymd', strtotime($stayfromdate));
		}
		$name = preg_replace("/[^a-zA-Z0-9 ]+/", '', $name);
		$name = htmlspecialchars((strlen($name) > 32 ? substr($name, 0, 32) : $name));
		//
		
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
			
		// required filter by User ID
		$filters = array('hotelid="' . $airbnb_user_id . '"');
		// other filters
		if (!empty($promoid)) {
			// check if the action should be "delete"
			$method = $promo_status === 0 ? 'delete' : $method;
			//
			$filters[] = 'action="' . $method . '"';
			$filters[] = 'promoid="' . $promoid . '"';
		} else {
			$filters[] = 'action="new"';
		}

		// promotion attributes
		$promo_attr = array();
		if (!empty($promoid)) {
			$promo_attr[] = 'id="' . $promoid . '"';
		}
		$promo_attr[] = 'name="' . $name . '"';
		$promo_attr[] = 'type="' . $type . '"';

		// extra nodes
		$extra_nodes = array();

		// compose threshold_one value for the pricing rules object
		$threshold_one = VikRequest::getInt('threshold_one', 0, 'request');
		if (empty($threshold_one)) {
			// only VBO injects this request variable, for VCM we need to detect it depending on the promo type
			if ($type == 'BOOKED_BEYOND_AT_LEAST_X_DAYS') {
				// early bird
				$threshold_one = $adv_book_days_min;
			} elseif ($type == 'BOOKED_WITHIN_AT_MOST_X_DAYS') {
				// last minute
				$threshold_one = $adv_book_days_max;
			} elseif ($type == 'STAYED_AT_LEAST_X_DAYS') {
				// long-term stay (multiple nights)
				$threshold_one = $min_stay;
			}
			// this limit is not used for seasonal adjustments promos
		}
		if (!empty($threshold_one)) {
			array_push($extra_nodes, '<extras key="threshold_one" value="' . $threshold_one . '" />');
		}

		// CTD week days
		foreach ($ctd_wdays as $ctd_wday) {
			if (!empty($ctd_wday)) {
				// it's the enum string representation of the day, like MONDAY
				array_push($extra_nodes, '<extras key="ctd_wday" value="' . $ctd_wday . '" />');
			}
		}

		// CTA week days
		foreach ($cta_wdays as $cta_wday) {
			if (!empty($cta_wday)) {
				// it's the enum string representation of the day, like MONDAY
				array_push($extra_nodes, '<extras key="cta_wday" value="' . $cta_wday . '" />');
			}
		}

		// max nights week days overrides
		if (!empty($max_nights_mon)) {
			array_push($extra_nodes, '<extras key="max_nights_mon" value="' . $max_nights_mon . '" />');
		}
		if (!empty($max_nights_tue)) {
			array_push($extra_nodes, '<extras key="max_nights_tue" value="' . $max_nights_tue . '" />');
		}
		if (!empty($max_nights_wed)) {
			array_push($extra_nodes, '<extras key="max_nights_wed" value="' . $max_nights_wed . '" />');
		}
		if (!empty($max_nights_thu)) {
			array_push($extra_nodes, '<extras key="max_nights_thu" value="' . $max_nights_thu . '" />');
		}
		if (!empty($max_nights_fri)) {
			array_push($extra_nodes, '<extras key="max_nights_fri" value="' . $max_nights_fri . '" />');
		}
		if (!empty($max_nights_sat)) {
			array_push($extra_nodes, '<extras key="max_nights_sat" value="' . $max_nights_sat . '" />');
		}
		if (!empty($max_nights_sun)) {
			array_push($extra_nodes, '<extras key="max_nights_sun" value="' . $max_nights_sun . '" />');
		}

		// min nights week days overrides
		if (!empty($min_nights_mon)) {
			array_push($extra_nodes, '<extras key="min_nights_mon" value="' . $min_nights_mon . '" />');
		}
		if (!empty($min_nights_tue)) {
			array_push($extra_nodes, '<extras key="min_nights_tue" value="' . $min_nights_tue . '" />');
		}
		if (!empty($min_nights_wed)) {
			array_push($extra_nodes, '<extras key="min_nights_wed" value="' . $min_nights_wed . '" />');
		}
		if (!empty($min_nights_thu)) {
			array_push($extra_nodes, '<extras key="min_nights_thu" value="' . $min_nights_thu . '" />');
		}
		if (!empty($min_nights_fri)) {
			array_push($extra_nodes, '<extras key="min_nights_fri" value="' . $min_nights_fri . '" />');
		}
		if (!empty($min_nights_sat)) {
			array_push($extra_nodes, '<extras key="min_nights_sat" value="' . $min_nights_sat . '" />');
		}
		if (!empty($min_nights_sun)) {
			array_push($extra_nodes, '<extras key="min_nights_sun" value="' . $min_nights_sun . '" />');
		}

		// stay dates is a single node, because excluded_dates are not supported
		$stay_dates = '<stay_date start="' . $stayfromdate . '" end="' . $staytodate . '" />';

		// listing ids involved
		$rooms_rates = '<rooms>' . "\n";
		foreach ($involved_rooms as $r) {
			$rooms_rates .= '<room id="' . $r . '"/>' . "\n";
		}
		$rooms_rates .= '</rooms>' . "\n";

		$e4jc_url = "https://slave.e4jconnect.com/channelmanager/?r=wprom&c=airbnbapi";
		
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager WPROM Request e4jConnect.com - Airbnb -->
<WritePromotionRQ xmlns="http://www.e4jconnect.com/channels/wpromrq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . $apikey . '"/>
	<Fetch ' . implode(' ', $filters) . '/>
	<WritePromotion ' . implode(' ', $promo_attr) . '>
		' . $stay_dates . '
		' . $rooms_rates . '
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
		if (stripos($rs, 'e4j.ok') === false) {
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
