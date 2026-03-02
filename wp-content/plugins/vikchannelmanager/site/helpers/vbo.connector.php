<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2025 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

// No direct access to this file
defined('ABSPATH') or die('No script kiddies please!');

/**
 * This class is used by VCM to execute specific "write" functions for VikBooking.
 * For example: setting new rates, creating a restriction and Channels Rates Push submit.
 * 
 * @author Alessio
 */
final class VikBookingConnector
{
	/**
	 * @var  string
	 */
	private $error = '';

	/**
	 * @var  bool
	 */
	private $multiReq = false;

	/**
	 * @var  object
	 */
	private $dbo;

	/**
	 * @var  	array
	 * 
	 * @since 	1.9.4
	 */
	private $ota_pricing_overrides = [];

	/**
	 * @var 	array
	 * 
	 * @since 	1.9.10
	 */
	private $xmlData = [];

	/**
	 * @var 	array
	 * 
	 * @since 	1.9.10
	 */
	private $pricingData = [];

	/**
	 * @var 	bool
	 * 
	 * @since 	1.9.10
	 */
	private $debug = false;

	/**
	 * This property can be set for the method channelsRatesPush to determine who is making the
	 * request: either the back-end Bulk Action (empty), the Rates Overview ('VBO'), the App or
	 * the SmartBalancer.
	 * 
	 * @var  string
	 */
	public $caller = '';

	/**
	 * An optional string determining the name of the authenticated API (App) user.
	 * 
	 * @var  	string
	 * 
	 * @since 	1.8.24
	 */
	public $apiUser = '';

	/**
	 * Class constructor
	 */
	public function __construct() 
	{
		$this->dbo = JFactory::getDbo();
	}

	/**
	 * Create a new restriction in VikBooking by using
	 * the given dates in Y-m-d format, an array of room IDs [5, 6...],
	 * and the array of restrictions [minlos.cta.ctd, maxlos].
	 * 
	 * @param 	string 		$from_date 		Y-m-d
	 * @param 	string 		$to_date 		Y-m-d
	 * @param 	array 		$room_ids
	 * @param 	array 		$restriction 	[0 = minlos.cta.ctd, 1 = maxlos]
	 *
	 * @return 	bool
	 */
	public function createRestriction($from_date, $to_date, $room_ids, $restriction)
	{
		$this->prepareMethod();

		if (empty($from_date) || empty($to_date) || !is_array($room_ids) || count($room_ids) < 1 || empty($room_ids[0]) || !is_array($restriction) || count($restriction) < 2) {
			$this->setError("1. createRestriction: Missing required data");
			return false;
		}

		$from_ts = strtotime($from_date);
		$to_ts 	 = strtotime($to_date);
		$now_ts  = time();
		if ($from_ts > $to_ts || ($to_ts < $now_ts && ($now_ts - $to_ts) > 86400)) {
			$this->setError("2. createRestriction: Invalid Dates or in the past");
			return false;
		}

		// get rooms info
		$safe_rids = [];
		foreach ($room_ids as $rid) {
			if (intval($rid) > 0 && !in_array((int)$rid, $safe_rids)) {
				$safe_rids[] = (int)$rid;
			}
		}
		if (!$safe_rids) {
			$this->setError("3. createRestriction: No valid Room IDs");
			return false;
		}

		$rooms_data = [];
		$q = "SELECT `id`,`name` FROM `#__vikbooking_rooms` WHERE `id` IN (".implode(',', $safe_rids).");";
		$this->dbo->setQuery($q);
		$all_rooms = $this->dbo->loadAssocList();

		foreach ($all_rooms as $room) {
			$rooms_data[$room['id']] = $room['name'];
		}

		if (!$rooms_data) {
			$this->setError("4. createRestriction: No rooms data");
			return false;
		}

		// get restriction data
		$minlos 	= $restriction[0];
		$maxlos 	= $restriction[1];
		$cta_wdays 	= [];
		$ctd_wdays 	= [];
		$aff_rooms	= '';
		if (strpos($restriction[0], 'CTA') !== false) {
			// if CTA and CTD, CTA comes first. Re-attach string in case of CTD
			$minlos_parts = explode('CTA[', $restriction[0]);
			$minlos = $minlos_parts[0];
			$minlos_parts_left = explode(']', $minlos_parts[1]);
			$cta_wdays = explode(',', $minlos_parts_left[0]);
			$restriction[0] = $minlos_parts[0].(array_key_exists(1, $minlos_parts_left) ? $minlos_parts_left[1] : '');
		}
		if (strpos($restriction[0], 'CTD') !== false) {
			$minlos_parts = explode('CTD[', $restriction[0]);
			$minlos = $minlos_parts[0];
			$ctd_wdays = explode(',', str_replace(']', '', $minlos_parts[1]));
			$restriction[0] = $minlos_parts[0];
		}
		if (strlen($minlos) > 0 && intval($minlos) == 1 && !(count($cta_wdays) > 0) && !(count($ctd_wdays) > 0)) {
			// November 2018 - we allow the creation of restrictions with just minlos=1 to override others
			// Restriction is useless for 1 night min and no CTA/CTD
			// $this->setError("5. createRestriction: Invalid restriction for 1 night min with no closing days");
			// return false;
		}

		// prepare data
		$minlos = strlen($minlos) < 1 || intval($minlos) < 1 ? 1 : (int)$minlos;
		$maxlos = strlen($maxlos) < 1 || intval($maxlos) < 1 ? 0 : (int)$maxlos;
		if ($cta_wdays) {
			foreach ($cta_wdays as $k => $v) {
				$wday = intval($v);
				if (strlen($v) < 1 || ($wday < 0 || $wday > 6)) {
					unset($cta_wdays[$k]);
					continue;
				}
				$cta_wdays[$k] = '-'.$wday.'-';
			}
			if (count($cta_wdays) > 7) {
				$cta_wdays = array_slice($cta_wdays, 0, 7);
			}
		}
		if ($ctd_wdays) {
			foreach ($ctd_wdays as $k => $v) {
				$wday = intval($v);
				if (strlen($v) < 1 || ($wday < 0 || $wday > 6)) {
					unset($ctd_wdays[$k]);
					continue;
				}
				$ctd_wdays[$k] = '-'.$wday.'-';
			}
			if (count($ctd_wdays) > 7) {
				$ctd_wdays = array_slice($ctd_wdays, 0, 7);
			}
		}
		foreach ($rooms_data as $idr => $rv) {
			$aff_rooms .= '-'.$idr.'-;';
		}

		// compose restriction name
		$restr_name = '';
		$info_from = getdate($from_ts);
		$info_to = getdate($to_ts);
		if (count($rooms_data) == 1) {
			$restr_name .= $rooms_data[$safe_rids[0]].' ';
		}
		if ($info_from['mon'] == $info_to['mon']) {
			$restr_name .= $info_from['month'].' '.$info_from['mday'].($info_from['mday'] != $info_to['mday'] ? ' - '.$info_to['mday'] : '');
		} else {
			$restr_name .= $info_from['month'].' '.$info_from['mday'].' - '.$info_to['month'].' '.$info_to['mday'];
		}

		// prepare the restriction record
		$restr_record = new stdClass;
		$restr_record->name     = $restr_name;
		$restr_record->month    = 0;
		$restr_record->minlos   = $minlos;
		$restr_record->maxlos   = $maxlos;
		$restr_record->dfrom    = $from_ts;
		$restr_record->dto      = $to_ts;
		$restr_record->allrooms = 0;
		$restr_record->idrooms  = $aff_rooms;
		$restr_record->ctad     = $cta_wdays ? implode(',', $cta_wdays) : null;
		$restr_record->ctdd     = $ctd_wdays ? implode(',', $ctd_wdays) : null;

		/**
		 * Make sure to remove duplicate restrictions.
		 * 
		 * @since 	1.9.0
		 */
		$q = $this->dbo->getQuery(true)
			->delete($this->dbo->qn('#__vikbooking_restrictions'))
			->where($this->dbo->qn('minlos') . ' >= ' . (int) $restr_record->minlos)
			->where($this->dbo->qn('maxlos') . ' = ' . (int) $restr_record->maxlos)
			->where($this->dbo->qn('dfrom') . ' = ' . (int) $restr_record->dfrom)
			->where($this->dbo->qn('dto') . ' = ' . (int) $restr_record->dto)
			->where($this->dbo->qn('allrooms') . ' = 0')
			->where($this->dbo->qn('idrooms') . ' = ' . $this->dbo->q($restr_record->idrooms))
			->where($this->dbo->qn('ctad') . (!$restr_record->ctad ? ' IS NULL' : ' = ' . $this->dbo->q($restr_record->ctad)))
			->where($this->dbo->qn('ctdd') . (!$restr_record->ctdd ? ' IS NULL' : ' = ' . $this->dbo->q($restr_record->ctdd)));
		$this->dbo->setQuery($q);
		$this->dbo->execute();

		// store the restriction record
		$this->dbo->insertObject('#__vikbooking_restrictions', $restr_record, 'id');

		return (bool) ($restr_record->id ?? null);
	}

	/**
	 * Set a new rate in VikBooking by using
	 * the given dates in Y-m-d format, the room ID,
	 * the price ID and the new exact amount in the default currency.
	 * This method is similar to the task called via ajax in VBO Calendar Rates Overview.
	 * Can return the JSON string with the rates of each day requested or boolean true/false.
	 * 
	 * @param 	string 		$from_date    Y-m-d
	 * @param 	string 		$to_date	  Y-m-d
	 * @param 	integer		$room_id
	 * @param 	integer 	$price_id
	 * @param 	float 		$amount
	 * @param 	boolean 	$return_json
	 * 
	 * @return 	boolean|string 	  JSON encoded string when $return_json is true
	 */
	public function setNewRate($from_date, $to_date, $room_id, $price_id, $amount, $return_json = false)
	{
		$this->prepareMethod();

		if (empty($from_date) || empty($to_date) || empty($room_id) || empty($price_id) || !((float)$amount > 0)) {
			$this->setError("1. setNewRate: Missing required data");
			return false;
		}

		$from_ts = strtotime($from_date);
		$to_ts = strtotime($to_date);
		$now_ts = time();
		if ($from_ts > $to_ts || ($to_ts < $now_ts && ($now_ts - $to_ts) > 86400)) {
			$this->setError("2. setNewRate: Invalid Dates or in the past");
			return false;
		}

		$amount = (float)$amount;
		$checkin_h = 0;
		$checkin_m = 0;
		$checkout_h = 0;
		$checkout_m = 0;
		$timeopst = VikBooking::getTimeOpenStore();
		if (is_array($timeopst)) {
			$opent = VikBooking::getHoursMinutes($timeopst[0]);
			$closet = VikBooking::getHoursMinutes($timeopst[1]);
			$checkin_h = $opent[0];
			$checkin_m = $opent[1];
			$checkout_h = $closet[0];
			$checkout_m = $closet[1];
		}

		// read the rates for the lowest number of nights
		$q = "SELECT `r`.`id`,`r`.`idroom`,`r`.`days`,`r`.`idprice`,`r`.`cost`,`p`.`name` 
			FROM `#__vikbooking_dispcost` AS `r` 
			INNER JOIN (
				SELECT MIN(`days`) AS `min_days` FROM `#__vikbooking_dispcost` WHERE `idroom`=" . (int)$room_id . " AND `idprice`=" . (int)$price_id . " GROUP BY `idroom`
			) AS `r2` ON `r`.`days`=`r2`.`min_days` 
			LEFT JOIN `#__vikbooking_prices` `p` ON `p`.`id`=`r`.`idprice` AND `p`.`id`=" . (int)$price_id . " 
			WHERE `r`.`idroom`=" . (int)$room_id . " AND `r`.`idprice`=" . (int)$price_id . " 
			GROUP BY `r`.`id`,`r`.`idroom`,`r`.`days`,`r`.`idprice`,`r`.`cost`,`p`.`name` ORDER BY `r`.`days` ASC, `r`.`cost` ASC;";
		$this->dbo->setQuery($q);
		$roomrates = $this->dbo->loadAssocList();
		if ($roomrates) {
			foreach ($roomrates as $rrk => $rrv) {
				$roomrates[$rrk]['cost'] = round(($rrv['cost'] / $rrv['days']), 2);
				$roomrates[$rrk]['days'] = 1;
			}
		}
		if (!$roomrates) {
			$this->setError("3. setNewRate: No rates defined for this room");
			return false;
		}

		/**
		 * Disable season records caching because new rates will have to be re-calculated
		 * for the response by checking the same exact dates.
		 * 
		 * @since 	1.8.20
		 * 
		 * @requires 	VBO >= 1.16.5 (J) - 1.6.5 (WP)
		 */
		if (method_exists('VikBooking', 'setSeasonsCache')) {
			VikBooking::setSeasonsCache(false);
		}

		// prepare environment
		$roomrates = $roomrates[0];
		$current_rates = [];
		$start_ts = strtotime($from_date);
		$end_ts = strtotime($to_date);
		$infostart = getdate($start_ts);

		/**
		 * Check if season records should be preloaded. Beware of the hundreds of MBs of server's
		 * memory that could be used for pre-loading and pre-caching records in favour of CPU.
		 * 
		 * @since 	1.9.4
		 */
		$cached_seasons = VikBooking::getDateSeasonRecords($start_ts, ($end_ts + ($checkout_h * 3600)), [$room_id]);
		$cached_wdayseasons = [];
		if (method_exists('VikBooking', 'getWdaySeasonRecords')) {
			$cached_wdayseasons = VikBooking::getWdaySeasonRecords();
		}

		// calculate the current rates for these dates
		while ($infostart[0] > 0 && $infostart[0] <= $end_ts) {
			$today_tsin = $infostart[0] + ($checkin_h * 3600) + ($checkin_m * 60);
			$today_tsout = mktime(0, 0, 0, $infostart['mon'], ($infostart['mday'] + 1), $infostart['year']) + ($checkout_h * 3600) + ($checkout_m * 60);

			$tars = VikBooking::applySeasonsRoom([$roomrates], $today_tsin, $today_tsout, [], $cached_seasons, $cached_wdayseasons);
			$current_rates[(date('Y-m-d', $infostart[0]))] = $tars[0];

			$infostart = getdate(mktime(0, 0, 0, $infostart['mon'], ($infostart['mday'] + 1), $infostart['year']));
		}
		if (!$current_rates) {
			$this->setError("4. setNewRate: No applicable rates for this room");
			return false;
		}

		// get seasons intervals and apply difference by creating new special prices
		$all_days = array_keys($current_rates);
		$season_intervals = [];
		$firstind = 0;
		$firstdaycost = $current_rates[$all_days[0]]['cost'];
		$nextdaycost = false;
		for ($i = 1; $i < count($all_days); $i++) {
			$ind = $all_days[$i];
			$nextdaycost = $current_rates[$ind]['cost'];
			if ($firstdaycost != $nextdaycost) {
				$interval = [
					'from' => $all_days[$firstind],
					'to' => $all_days[($i - 1)],
					'cost' => $firstdaycost
				];
				$season_intervals[] = $interval;
				$firstdaycost = $nextdaycost;
				$firstind = $i;
			}
		}
		if ($nextdaycost === false) {
			$interval = [
				'from' => $all_days[$firstind],
				'to' => $all_days[$firstind],
				'cost' => $firstdaycost
			];
			$season_intervals[] = $interval;
		} elseif ($firstdaycost == $nextdaycost) {
			$interval = [
				'from' => $all_days[$firstind],
				'to' => $all_days[($i - 1)],
				'cost' => $firstdaycost
			];
			$season_intervals[] = $interval;
		}
		foreach ($season_intervals as $sik => $siv) {
			if ((float)$siv['cost'] == $amount) {
				unset($season_intervals[$sik]);
			}
		}
		if (!$season_intervals) {
			// no changes of rates is needed for the current dates. Return true
			return true;
		}

		foreach ($season_intervals as $sik => $siv) {
			$first = strtotime($siv['from']);
			$second = strtotime($siv['to']);

			if ($second > 0 && $second == $first) {
				$second += 86399;
			}

			if (!($second > $first)) {
				unset($season_intervals[$sik]);
				continue;
			}

			$baseone = getdate($first);
			$basets = mktime(0, 0, 0, 1, 1, $baseone['year']);
			$sfrom = $baseone[0] - $basets;
			$basetwo = getdate($second);
			$basets = mktime(0, 0, 0, 1, 1, $basetwo['year']);
			$sto = $basetwo[0] - $basets;

			// check leap year
			if ($baseone['year'] % 4 == 0 && ($baseone['year'] % 100 != 0 || $baseone['year'] % 400 == 0)) {
				$leapts = mktime(0, 0, 0, 2, 29, $baseone['year']);
				if ($baseone[0] >= $leapts) {
					$sfrom -= 86400;
				}
			}
			if ($basetwo['year'] % 4 == 0 && ($basetwo['year'] % 100 != 0 || $basetwo['year'] % 400 == 0)) {
				$leapts = mktime(0, 0, 0, 2, 29, $basetwo['year']);
				if ($basetwo[0] >= $leapts) {
					$sto -= date('d-m', $baseone[0]) != '31-12' && date('d-m', $basetwo[0]) == '31-12' ? 1 : 86400;
				}
			}

			$tieyear = $baseone['year'];
			$ptype = (float)$siv['cost'] > $amount ? 2 : 1;
			$pdiffcost = $ptype == 1 ? ($amount - (float)$siv['cost']) : ((float)$siv['cost'] - $amount);
			$roomstr = "-" . $room_id . "-,";
			$pspname = date('Y-m-d H:i').' - '.substr($baseone['month'], 0, 3).' '.$baseone['mday'].($siv['from'] != $siv['to'] ? '/'.($baseone['month'] != $basetwo['month'] ? substr($basetwo['month'], 0, 3).' ' : '').$basetwo['mday'] : '');
			$pval_pcent = 1;
			$pricestr = "-" . $price_id . "-,";

			// store season record
			$season_record = new stdClass;
			$season_record->type 	  = $ptype;
			$season_record->from 	  = $sfrom;
			$season_record->to 		  = $sto;
			$season_record->diffcost  = $pdiffcost;
			$season_record->idrooms   = $roomstr;
			$season_record->spname 	  = $pspname;
			$season_record->val_pcent = $pval_pcent;
			$season_record->year 	  = $tieyear;
			$season_record->idprices  = $pricestr;

			$this->dbo->insertObject('#__vikbooking_seasons', $season_record, 'id');
		}

		// prepare output by re-calculating the rates in real-time (if return_json)
		if ($return_json) {
			// prepare environment
			$current_rates = [];
			$infostart = getdate($start_ts);

			/**
			 * Check if season records should be preloaded. Beware of the hundreds of MBs of server's
			 * memory that could be used for pre-loading and pre-caching records in favour of CPU.
			 * 
			 * @since 	1.9.4
			 */
			$cached_seasons = VikBooking::getDateSeasonRecords($start_ts, ($end_ts + ($checkout_h * 3600)), [$room_id]);
			$cached_wdayseasons = [];
			if (method_exists('VikBooking', 'getWdaySeasonRecords')) {
				$cached_wdayseasons = VikBooking::getWdaySeasonRecords();
			}

			while ($infostart[0] > 0 && $infostart[0] <= $end_ts) {
				$today_tsin = $infostart[0] + ($checkin_h * 3600) + ($checkin_m * 60);
				$today_tsout = mktime(0, 0, 0, $infostart['mon'], ($infostart['mday'] + 1), $infostart['year']) + ($checkout_h * 3600) + ($checkout_m * 60);

				$tars = VikBooking::applySeasonsRoom([$roomrates], $today_tsin, $today_tsout, [], $cached_seasons, $cached_wdayseasons);
				$indkey = $infostart['mday'].'-'.$infostart['mon'].'-'.$infostart['year'].'-'.$price_id;
				$current_rates[$indkey] = $tars[0];

				$infostart = getdate(mktime(0, 0, 0, $infostart['mon'], ($infostart['mday'] + 1), $infostart['year']));
			}
		}

		/**
		 * Store a record in the rates flow for this rate modification on VBO.
		 * 
		 * @since 	1.8.3
		 */
		$rflow_handler = VikChannelManager::getRatesFlowInstance();
		$rflow_record  = $rflow_handler->getRecord()
			->setCreatedBy(__FUNCTION__, $this->apiUser)
			->setDates($from_date, $to_date)
			->setVBORoomID($room_id)
			->setVBORatePlanID($price_id)
			->setBaseFee($roomrates['cost'])
			->setNightlyFee($amount);
		// push rates flow record
		$rflow_handler->pushRecord($rflow_record);
		// store rates flow record
		$rflow_handler->storeRecords();
		
		// return response
		return $return_json ? json_encode($current_rates) : true;
	}

	/**
	 * Adds new special prices in VikBooking for the given dates and rooms.
	 * The method is called by the Smart Balancer class whenever some rules
	 * must be applied to adjust the rates depending on the availability.
	 * This method simply checks the validity of the passed arguments and creates
	 * the special price by making a query.
	 * 
	 * @param 	string 		$from_date    	Y-m-d
	 * @param 	string 		$to_date	  	Y-m-d
	 * @param 	array		$room_ids 		numeric array containing the IDs of all the rooms involved
	 * @param 	array 		$rule 			associative array containing the parsed information of the Rule.
	 * 
	 * @return 	integer|boolean 			the ID of the generated special price on success, false on failure.
	 */
	public function addSmartBalancerSpecialPrice($from_date, $to_date, $room_ids, $rule)
	{
		$this->prepareMethod();

		if (empty($from_date) || empty($to_date) || empty($room_ids) || !is_array($room_ids) || !is_array($rule)) {
			$this->setError("1. addSmartBalancerSpecialPrice: Missing required data");
			return false;
		}

		$from_ts = strtotime($from_date);
		$to_ts = strtotime($to_date);
		$now_ts = time();
		if ($from_ts > $to_ts || ($to_ts < $now_ts && ($now_ts - $to_ts) > 86400)) {
			$this->setError("2. addSmartBalancerSpecialPrice: Invalid Dates or in the past");
			return false;
		}

		$first = $from_ts;
		$second = $to_ts;
		if ($second > 0 && $second == $first) {
			$second += 86399;
		}
		if (!($second > $first)) {
			$this->setError("3. addSmartBalancerSpecialPrice: Invalid Dates or in the past");
			return false;
		}
		$baseone = getdate($first);
		$basets = mktime(0, 0, 0, 1, 1, $baseone['year']);
		$sfrom = $baseone[0] - $basets;
		$basetwo = getdate($second);
		$basets = mktime(0, 0, 0, 1, 1, $basetwo['year']);
		$sto = $basetwo[0] - $basets;
		//check leap year
		if ($baseone['year'] % 4 == 0 && ($baseone['year'] % 100 != 0 || $baseone['year'] % 400 == 0)) {
			$leapts = mktime(0, 0, 0, 2, 29, $baseone['year']);
			if ($baseone[0] >= $leapts) {
				$sfrom -= 86400;
			}
		}
		if ($basetwo['year'] % 4 == 0 && ($basetwo['year'] % 100 != 0 || $basetwo['year'] % 400 == 0)) {
			$leapts = mktime(0, 0, 0, 2, 29, $basetwo['year']);
			if ($basetwo[0] >= $leapts) {
				$sto -= date('d-m', $baseone[0]) != '31-12' && date('d-m', $basetwo[0]) == '31-12' ? 1 : 86400;
			}
		}
		//end leap year
		$tieyear = $baseone['year'];
		$ptype = $rule['rule']->updown == 'down' ? 2 : 1;
		$pdiffcost = (float)$rule['rule']->amount;
		$roomstr = '';
		foreach ($room_ids as $rid) {
			$roomstr .= "-".$rid."-,";
		}
		$pspname = 'Smart Balancer: '.$rule['name'];
		$pval_pcent = (int)$rule['rule']->pcent > 0 ? 2 : 1;
		$pricestr = '';
		//get all types of price ID
		$q = "SELECT `id`,`name` FROM `#__vikbooking_prices` ORDER BY `name` ASC;";
		$this->dbo->setQuery($q);
		$all_price_ids = $this->dbo->loadAssocList();
		if ($all_price_ids) {
			foreach ($all_price_ids as $price) {
				$pricestr .= "-".$price['id']."-,";
			}
		}
		//
		$q = "INSERT INTO `#__vikbooking_seasons` 
			(`type`,`from`,`to`,`diffcost`,`idrooms`,`spname`,`wdays`,`checkinincl`,`val_pcent`,`losoverride`,`roundmode`,`year`,`idprices`,`promo`,`promodaysadv`,`promotxt`,`promominlos`,`occupancy_ovr`) 
			VALUES(".$ptype.", ".$this->dbo->quote($sfrom).", ".$this->dbo->quote($sto).", ".$this->dbo->quote($pdiffcost).", ".$this->dbo->quote($roomstr).", ".$this->dbo->quote($pspname).", '', '0', ".$pval_pcent.", '', NULL, ".$tieyear.", ".$this->dbo->quote($pricestr).", 0, NULL, '', 0, NULL);";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		$sp_id = (int)$this->dbo->insertid();

		return $sp_id > 0 ? $sp_id : false;
	}

	/**
	 * This is the rates push submit execution method.
	 * The code was originally called by the controller.php (exec_ratespush)
	 * but everything has been moved onto this method since VCM 1.6.0 for the App.
	 * The method is called by the API Framework or by the back-end controller.
	 * The back-end controller is executed via Ajax while the API via POST.
	 * Calculates the rates, builds the XML and sends the request to e4jConnect.
	 * 
	 * @param 	array 		$channels
	 * @param 	array 		$chrplans
	 * @param 	array 		$nodes
	 * @param 	array 		$rooms
	 * @param 	array 		$pushvars
	 *
	 * @return 	bool|string 	false on failure (by setting errors) or JSON encoded string.
	 */	
	public function channelsRatesPush($channels, $chrplans, $nodes, $rooms, $pushvars)
	{
		ignore_user_abort(true);
		$this->prepareMethod();

		$app = JFactory::getApplication();
		$session = JFactory::getSession();

		// Derived Prices (Booking.com Occupancy rules Default Pricing)
		$derived_pr_cache = $session->get('vcmRatespushDerpr', '');
		$derived_pr_cache = empty($derived_pr_cache) ? [] : $derived_pr_cache;

		// prevent further API errors due to outdated mapping information
		$prevent_errors = $session->get('vcmRatespushNewmapping', []);
		$prevent_errors = !is_array($prevent_errors) ? [] : $prevent_errors;
		$resolve_errors = [];

		// access previously cached settings
		$bulk_rates_cache 	   = VikChannelManager::getBulkRatesCache();
		$bulk_rates_adv_params = VikChannelManager::getBulkRatesAdvParams();

		// get rates flow handler
		$rflow_handler = VikChannelManager::getRatesFlowInstance();

		// debug mode
		$debug_str = '';
		$debug_mode = $app->input->getBool('e4j_debug', false) || $this->debug;

		/**
		 * Access any promotion previously submitted to the OTAs and register them onto VikBooking.
		 * 
		 * @requires 	VBO >= 1.16.4 (J) - 1.6.4 (WP)
		 * 
		 * @since 		1.8.19
		 */
		try {
			$promo_handlers = VikChannelManager::getPromotionHandlers();
			if ($promo_handlers) {
				// load all promotion IDs transmitted to the OTAs
				$promo_ids = VikChannelManagerPromo::getPromosOnChannels('vbo_promo_id');
				if (method_exists('VikBooking', 'registerPromotionIds')) {
					// register them onto VikBooking
					VikBooking::registerPromotionIds($promo_ids);
				}
			}
		} catch (Exception $e) {
			// do nothing
		}

		// the list of channel responses
		$responses = [];

		// grab settings
		$vbo_tax_included = VikBooking::ivaInclusa();
		$currency 		  = VikChannelManager::getCurrencyName();
		$sleep_allowed 	  = VikChannelManager::sleepAllowed();

		// the names of the pricing models that require the LOS pricing
		$los_keymodels = ['any', 'los', 'PerDayPricingByLengthOfStay'];

		foreach ($rooms as $ind => $idroom) {
			if (empty($idroom) || empty($channels[$ind]) || empty($nodes[$ind]) || empty($chrplans[$ind]) || empty($pushvars[$ind])) {
				continue;
			}
			if (!isset($responses[$idroom])) {
				$responses[$idroom] = [];
			}
			list($idprice, $default_rate) = explode(';', trim($pushvars[$ind]));
			$min_occ = 1;
			$max_occ = 1;
			$breakdown = [];
			$q = "SELECT `fromadult`,`toadult`,`totpeople`,`mintotpeople`,`params` FROM `#__vikbooking_rooms` WHERE `id`=".(int)$idroom.";";
			$this->dbo->setQuery($q);
			$room_details = $this->dbo->loadAssoc() ?: [];
			if ($room_details) {
				$min_occ = $room_details['fromadult'] > $room_details['mintotpeople'] ? $room_details['fromadult'] : $room_details['mintotpeople'];
				$min_occ = $min_occ < 1 ? 1 : $min_occ;
				$max_occ = $room_details['toadult'];
				$max_occ = $max_occ < $min_occ ? $min_occ : $max_occ;
			}
			$occupancy = range($min_occ, $max_occ);
			// tax rates and rate plan name
			$tax_rates  = [];
			$price_name = null;
			$q = "SELECT `p`.`id`, `p`.`name`, `t`.`aliq` FROM `#__vikbooking_prices` AS `p` LEFT JOIN `#__vikbooking_iva` `t` ON `p`.`idiva`=`t`.`id` WHERE `p`.`id`=".(int)$idprice.";";
			$this->dbo->setQuery($q);
			$alltaxrates = $this->dbo->loadAssoc();
			if ($alltaxrates) {
				if (!empty($alltaxrates['aliq']) && $alltaxrates['aliq'] > 0) {
					$tax_rates[$alltaxrates['id']] = $alltaxrates['aliq'];
				}
				$price_name = $alltaxrates['name'];
			}
			//
			$q = "SELECT `c`.`name`,`c`.`uniquekey`,`c`.`settings`,`r`.`idroomota`, `r`.`idchannel`, `r`.`otapricing`, `r`.`prop_params` FROM `#__vikchannelmanager_channel` AS `c`, `#__vikchannelmanager_roomsxref` AS `r` WHERE `c`.`uniquekey`=`r`.`idchannel` AND `c`.`av_enabled`=1 AND `r`.`idroomvb`=".(int)$idroom." AND `c`.`uniquekey` IN (".$channels[$ind].");";
			$this->dbo->setQuery($q);
			$rows = $this->dbo->loadAssocList();
			if (!$rows) {
				continue;
			}

			// room-channel alterations
			$room_channel_alterations = [];

			foreach ($rows as $chind => $row) {
				// channel settings
				$channel_settings = !empty($row['settings']) ? json_decode($row['settings'], true) : [];

				// default to what's configured in Vik Booking
				$taxincl_price_compare = $vbo_tax_included;
				if (is_array($channel_settings) && isset($channel_settings['price_compare'])) {
					if ($channel_settings['price_compare']['value'] == 'VCM_PRICE_COMPARE_TAX_INCL') {
						$taxincl_price_compare = true;
					}
				}

				/**
				 * We try to memorize the amount of tax aliquot for some channels, like
				 * Google Hotel, that may prefer both amounts before and after tax.
				 * 
				 * @since 	1.8.7
				 */
				$tax_aliquot = !empty($tax_rates[$idprice]) ? $tax_rates[$idprice] : null;

				$hotelid = '';
				if (!empty($row['prop_params'])) {
					$prop_info = json_decode($row['prop_params'], true);
					if (is_array($prop_info)) {
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
				}
				$ota_rate_plan = !empty($row['otapricing']) ? json_decode($row['otapricing'], true) : [];
				$ota_rate_plan = is_array($ota_rate_plan) ? $ota_rate_plan : [];
				$channel_pos = 0;
				$channels_list = explode(',', $channels[$ind]);
				foreach ($channels_list as $ch_pos => $ch_id) {
					if (intval($ch_id) == intval($row['idchannel'])) {
						$channel_pos = $ch_pos;
						break;
					}
				}
				$chrplans_list = explode(',', $chrplans[$ind]);
				$channel_currency = '';
				$arimode = '';
				$rateplanid = array_key_exists($channel_pos, $chrplans_list) ? $chrplans_list[$channel_pos] : '0';
				$currency_sep = strpos($rateplanid, ':');
				if ($currency_sep !== false) {
					$channel_currency = substr($rateplanid, ($currency_sep + 1));
					$rateplanid = substr($rateplanid, 0, $currency_sep);
				}
				$arisep = strpos($rateplanid, '=');
				if ($arisep !== false) {
					$arimode = substr($rateplanid, ($arisep + 1));
					$rateplanid = substr($rateplanid, 0, $arisep);
				}
				$ota_rplan_name = null;
				if (isset($ota_rate_plan['RatePlan']) && isset($ota_rate_plan['RatePlan'][$rateplanid]) && isset($ota_rate_plan['RatePlan'][$rateplanid]['name'])) {
					$ota_rplan_name = $ota_rate_plan['RatePlan'][$rateplanid]['name'];
				}
				$log_rateplanid = '';
				if ($this->caller == 'SmartBalancer') {
					$log_rateplanid = '{RatePlan ID '.$rateplanid.'} ';
				}
				$pricingmodel = '';
				$pricing_attr = '';
				$taxpolicy_attr = '';
				if ($row['uniquekey'] == VikChannelManagerConfig::EXPEDIA) {
					//Expedia
					if (array_key_exists('RatePlan', $ota_rate_plan) && array_key_exists($rateplanid, $ota_rate_plan['RatePlan'])) {
						$pricingmodel = trim($ota_rate_plan['RatePlan'][$rateplanid]['pricingModel']);
					}
				} elseif ($row['uniquekey'] == VikChannelManagerConfig::AGODA) {
					//Agoda
					$pricingmodel .= 'SingleRate;';
					if (array_key_exists('RoomInfo', $ota_rate_plan) && array_key_exists('MaxOccupancy', $ota_rate_plan['RoomInfo']) && $ota_rate_plan['RoomInfo']['MaxOccupancy'] >= 2) {
						$pricingmodel .= 'DoubleRate;';
					}
					if (array_key_exists('RoomInfo', $ota_rate_plan) && array_key_exists('MaxOccupancy', $ota_rate_plan['RoomInfo']) && $ota_rate_plan['RoomInfo']['MaxOccupancy'] >= 3) {
						$pricingmodel .= 'FullRate;';
					}
					$pricingmodel = rtrim($pricingmodel, ';');
				} elseif ($row['uniquekey'] == VikChannelManagerConfig::YCS50) {
					//Agoda YCS 5.0 (Max occ = 1 require single rate - Max occ = 2 require single and double rate - Max occ > 2 require single, double and full rate)
					$pricingmodel .= 'SingleRate;';
					if (array_key_exists('RoomInfo', $ota_rate_plan) && array_key_exists('num_persons', $ota_rate_plan['RoomInfo']) && $ota_rate_plan['RoomInfo']['num_persons'] >= 2) {
						$pricingmodel .= 'DoubleRate;';
					}
					if (array_key_exists('RoomInfo', $ota_rate_plan) && array_key_exists('num_persons', $ota_rate_plan['RoomInfo']) && $ota_rate_plan['RoomInfo']['num_persons'] >= 3) {
						$pricingmodel .= 'FullRate;';
					}
					$pricingmodel = rtrim($pricingmodel, ';');
					if (array_key_exists('RoomInfo', $ota_rate_plan) && array_key_exists('num_persons', $ota_rate_plan['RoomInfo'])) {
						if (intval($ota_rate_plan['RoomInfo']['num_persons']) >= $min_occ) {
							$occupancy = range($min_occ, (int)$ota_rate_plan['RoomInfo']['num_persons']);
						}
					}
				} elseif ($row['uniquekey'] == VikChannelManagerConfig::GARDAPASS) {
					//Gardapass - they only support Single rate and room (Full) rate.
					if ($min_occ < 2) {
						$pricingmodel .= 'SingleRate;';
						$occupancy = range(1, 2);
					}
					$pricingmodel .= 'FullRate;';
					$pricingmodel = rtrim($pricingmodel, ';');
				} elseif ($row['uniquekey'] == VikChannelManagerConfig::BEDANDBREAKFASTIT) {
					$pricingmodel .= 'SingleRate;';
					if (array_key_exists('RatePlan', $ota_rate_plan) && array_key_exists('max_persons', $ota_rate_plan['RatePlan'][-1])) {
						if (intval($ota_rate_plan['RatePlan'][-1]['max_persons']) > 1) {
							$pricingmodel .= 'DoubleRate;';
						}
						if (intval($ota_rate_plan['RatePlan'][-1]['max_persons']) > 2) {
							$pricingmodel .= 'ExtraBed;';
						}
						if (intval($ota_rate_plan['RatePlan'][-1]['max_persons']) > 0) {
							$occupancy = range($min_occ, (int)$ota_rate_plan['RatePlan'][-1]['max_persons']);
						}
					}
				} elseif ($row['uniquekey'] == VikChannelManagerConfig::BEDANDBREAKFASTEU || $row['uniquekey'] == VikChannelManagerConfig::BEDANDBREAKFASTNL) {
					if (array_key_exists('RatePlan', $ota_rate_plan) && array_key_exists('max_occupation', $ota_rate_plan['RatePlan'][-1])) {
						if (intval($ota_rate_plan['RatePlan'][-1]['max_occupation']) >= $min_occ) {
							$occupancy = range($min_occ, (int)$ota_rate_plan['RatePlan'][-1]['max_occupation']);
						}
					}
				} elseif ($row['uniquekey'] == VikChannelManagerConfig::BOOKING) {
					//Booking.com
					if (!empty($arimode)) {
						$pricingmodel = trim($arimode);
					} else {
						$pricingmodel = 'any';
					}
					if (array_key_exists('RatePlan', $ota_rate_plan) && array_key_exists($rateplanid, $ota_rate_plan['RatePlan']) && array_key_exists('max_persons', $ota_rate_plan['RatePlan'][$rateplanid])) {
						if (intval($ota_rate_plan['RatePlan'][$rateplanid]['max_persons']) >= $min_occ) {
							$occupancy = range($min_occ, (int)$ota_rate_plan['RatePlan'][$rateplanid]['max_persons']);
						}
					}
				} elseif ($row['uniquekey'] == VikChannelManagerConfig::DESPEGAR) {
					//for this channel we set two extra attributes in the RatePlan element (default for PerRoomPerNight and AmountAfterTax)
					$pricing_attr = '19';
					$taxpolicy_attr = 'inclusive';
					if (isset($ota_rate_plan['RatePlan']) && isset($ota_rate_plan['RatePlan'][$rateplanid]) && isset($ota_rate_plan['RatePlan'][$rateplanid]['ChargeTypeCode'])) {
						if ($ota_rate_plan['RatePlan'][$rateplanid]['ChargeTypeCode'] == 'PerPersonPerNight' || (int)$ota_rate_plan['RatePlan'][$rateplanid]['ChargeTypeCode'] == 21) {
							$pricing_attr = '21';
						}
					}
					if (isset($ota_rate_plan['RoomInfo']) && isset($ota_rate_plan['RoomInfo']['TaxPolicy'])) {
						if (stripos($ota_rate_plan['RoomInfo']['TaxPolicy'], 'exclusive') !== false) {
							$taxpolicy_attr = 'exclusive';
						}
					}
				} elseif ($row['uniquekey'] == VikChannelManagerConfig::GOOGLEHOTEL || $row['uniquekey'] == VikChannelManagerConfig::GOOGLEVR) {
					// we need to know if the prices should be transmitted before or after tax
					if ($taxincl_price_compare) {
						$taxpolicy_attr = 'inclusive';
					} else {
						$taxpolicy_attr = 'exclusive';
					}
					// try to pass the amount of tax, if available, useful to build the amount before and after tax
					if (!empty($tax_aliquot)) {
						$pricing_attr = $tax_aliquot;
					}
					// check if LOS Records have been defined for this combination of room/rate plan
					$has_los_records = VikChannelManager::roomHasLosRecords($idroom, $idprice, $get_nights = true);
					if ($has_los_records) {
						/**
						 * We need to submit the prices by using the LOS information. However, we consider
						 * this scenario a LOS Pricing Model for Google Hotel only if less than 7 nights.
						 * 
						 * @since 	1.8.16
						 */
						if ($has_los_records < 7) {
							$pricingmodel = 'los';
						}
					}
					// make sure to re-define the proper room occupancy
					$occupancy = range($min_occ, $max_occ);
				} elseif ($row['uniquekey'] == VikChannelManagerConfig::OTELZ) {
					//Otelz.com
					$occupancy = [1];
					if (array_key_exists('RatePlan', $ota_rate_plan) && array_key_exists($rateplanid, $ota_rate_plan['RatePlan']) && array_key_exists('max_adults', $ota_rate_plan['RatePlan'][$rateplanid])) {
						if (intval($ota_rate_plan['RatePlan'][$rateplanid]['max_adults']) > 1 && $ota_rate_plan['RatePlan'][$rateplanid]['pricing_type'] == 'PriceByAdult') {
							$occupancy = range(1, (int)$ota_rate_plan['RatePlan'][$rateplanid]['max_adults']);
						}
					}
				} elseif ($row['uniquekey'] == VikChannelManagerConfig::FERATEL) {
					// Feratel
					if (isset($ota_rate_plan['RatePlan']) && isset($ota_rate_plan['RatePlan'][-1]) && isset($ota_rate_plan['RatePlan'][-1]['min_occupancy']) && isset($ota_rate_plan['RatePlan'][-1]['max_occupancy'])) {
						if (intval($ota_rate_plan['RatePlan'][-1]['max_occupancy']) >= intval($ota_rate_plan['RatePlan'][-1]['min_occupancy'])) {
							$occupancy = range((int)$ota_rate_plan['RatePlan'][-1]['min_occupancy'], (int)$ota_rate_plan['RatePlan'][-1]['max_occupancy']);
						}
					} elseif (isset($ota_rate_plan['RatePlan']) && isset($ota_rate_plan['RatePlan'][$rateplanid]) && isset($ota_rate_plan['RatePlan'][$rateplanid]['min_occupancy']) && isset($ota_rate_plan['RatePlan'][$rateplanid]['max_occupancy'])) {
						if (intval($ota_rate_plan['RatePlan'][$rateplanid]['max_occupancy']) >= intval($ota_rate_plan['RatePlan'][$rateplanid]['min_occupancy'])) {
							$occupancy = range((int)$ota_rate_plan['RatePlan'][$rateplanid]['min_occupancy'], (int)$ota_rate_plan['RatePlan'][$rateplanid]['max_occupancy']);
						}
					}
				} elseif ($row['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI) {
					// Airbnb API
					if (array_key_exists('RatePlan', $ota_rate_plan) && array_key_exists($rateplanid, $ota_rate_plan['RatePlan'])) {
						if (isset($ota_rate_plan['RatePlan'][$rateplanid]['person_capacity']) && isset($ota_rate_plan['RatePlan'][$rateplanid]['guests_included'])) {
							if ($ota_rate_plan['RatePlan'][$rateplanid]['person_capacity'] > $ota_rate_plan['RatePlan'][$rateplanid]['guests_included']) {
								/**
								 * This is the most we can do here, if the capacity is greater than the guests included, we are
								 * supposed to calculate the OBP pricing. In any case, OBP rules if supported will be transmitted.
								 */
								$pricingmodel = 'OBP';
							}
						}
					}

					/**
					 * We no longer use the mapping value for the person capacity as this
					 * could be invalid or outdated. We need to use the values in Vik Booking
					 * to make sure the proper OBP rules are reflected, as the person_capacity
					 * could be changed at any time through the Listings API. We simply make
					 * sure to re-define the proper room occupancy.
					 * 
					 * @since 	1.8.4
					 */
					$occupancy = range($min_occ, $max_occ);

					/**
					 * Prices to be transmitted to Airbnb should always be exclusive of taxes,
					 * so long as the listings are eligible for pass through (occupancy) taxes (GST).
					 * Most listings in EU are not eligible for occupancy taxes and so we cannot
					 * transmit always net rates, or prices on Airbnb will be cheaper, without tax.
					 * 
					 * @since 	1.8.1
					 */
					$taxincl_price_compare = true;
					if (array_key_exists('RatePlan', $ota_rate_plan) && array_key_exists($rateplanid, $ota_rate_plan['RatePlan'])) {
						if (isset($ota_rate_plan['RatePlan'][$rateplanid]['eligible_for_taxes']) && $ota_rate_plan['RatePlan'][$rateplanid]['eligible_for_taxes'] > 0) {
							/**
							 * During the mapping of this listing, we have detected from the Pricing Settings that pass through taxes are supported,
							 * and so prices should be exclusive of tax now that we know pass through (occupancy) taxes will be transmitted for GST.
							 */
							$taxincl_price_compare = false;
						}
					}

					/**
					 * Check if LOS Records have been defined for this combination of room/rate plan. 
					 * Can be disabled through the advanced options in the Bulk Action - Rates Upload.
					 * 
					 * @since 	1.8.6 	LOS records can be ignored through the advanced options.
					 */
					$has_los_records = empty($bulk_rates_adv_params['airbnb_no_los']) ? VikChannelManager::roomHasLosRecords($idroom, $idprice) : false;
					if ($has_los_records) {
						// we need to submit the prices by using the LOS Records in Airbnb
						$pricingmodel = 'los';
					}
				} elseif ($row['uniquekey'] == VikChannelManagerConfig::VRBOAPI) {
					// rates are always before taxes
					$taxincl_price_compare = false;
					// check if LOS Records have been defined for this combination of room/rate plan
					$pricingmodel = 'OBP';
					$has_los_records = empty($bulk_rates_adv_params['vrbo_no_los']) ? VikChannelManager::roomHasLosRecords($idroom, $idprice, true) : false;
					if ($has_los_records === true || (is_int($has_los_records) && $has_los_records < 28)) {
						// we need to submit the prices by using the LOS information
						$pricingmodel = 'los';
					}
					// make sure to re-define the proper room occupancy
					$occupancy = range($min_occ, $max_occ);
				}

				/**
				 * Gather the very min and very max dates to update.
				 * 
				 * @since 	1.8.11
				 */
				$very_min_max_dates = [
					'min_date' => null,
					'max_date' => null,
				];

				// get the nodes information
				$cust = explode(';', $nodes[$ind]);

				// compose request
				$e4jc_url = "https://e4jconnect.com/channelmanager/?r=rar&c=".$row['name'];
				$xmlRQ = '<?xml version="1.0" encoding="UTF-8"?>
<!-- RAR Request e4jConnect.com - VikChannelManager - VikBooking -->
<RarUpdateRQ xmlns="http://www.e4jconnect.com/avail/rarrq">
	<Notify client="'.JUri::root().'"/>
	<Api key="'.VikChannelManager::getApiKey(true).'"/>
	<Currency name="'.(!empty($channel_currency) ? $channel_currency : $currency).'"/>'."\n";

				foreach ($cust as $det) {
					// obtain the instructions
					list($from_date, $to_date, $minlos, $maxlos, $rmod, $rmodop, $rmodamount, $rmodval) = explode('_', trim($det));

					if ($minlos && !$maxlos) {
						// ensure restrictions will be submitted
						$maxlos = '0';
					}

					// update very min/max dates
					if (empty($very_min_max_dates['min_date'])) {
						$very_min_max_dates['min_date'] = $from_date;
					}
					$very_min_max_dates['max_date'] = $to_date;

					/**
					 * Rate alterations can be specified at channel level. Alteration instructions are accepted
					 * through the Bulk Rates Cache or by setting some custom OTA pricing override values.
					 * 
					 * @since 	1.8.3
					 * @since 	1.9.4 	Added support to custom OTA pricing override values.
					 */
					if (($bulk_rates_cache[$idroom][$idprice]['rmod_channels'] ?? []) || ($this->ota_pricing_overrides[$row['uniquekey']] ?? [])) {
						/**
						 * If coming from the View oversight and an exact rate is requested, we should not overwrite the alterations
						 * at channel level, because the exact cost to be passed to the channels has already been defined by VCM.
						 * In this case, the caller will be empty as it's the bulk action that answers to the custom channel rates.
						 * The rates flow record in this case will be saved with no channel alteration string because of the exact rate.
						 * An exact rate instruction node will receive rmod = 1, rmodop = 2, rmodamount > 0 (exact rate), rmodval = 0.
						 */
						$skip_ch_alteration = (empty($this->caller) && (int) $rmodop > 1 && (float) $rmodamount > 0);
						if (!$skip_ch_alteration && (($bulk_rates_cache[$idroom][$idprice]['rmod_channels'][$row['uniquekey']] ?? null) || ($this->ota_pricing_overrides[$row['uniquekey']] ?? []))) {
							// alterations defined for this room, rate plan and channel
							if ($this->ota_pricing_overrides[$row['uniquekey']] ?? []) {
								// use custom OTA pricing override commands
								list($rmod, $rmodop, $rmodamount, $rmodval) = $this->ota_pricing_overrides[$row['uniquekey']];
							} else {
								// use Bulk Rates Cache commands
								$rmod 		= $bulk_rates_cache[$idroom][$idprice]['rmod_channels'][$row['uniquekey']]['rmod'];
								$rmodop 	= $bulk_rates_cache[$idroom][$idprice]['rmod_channels'][$row['uniquekey']]['rmodop'];
								$rmodamount = $bulk_rates_cache[$idroom][$idprice]['rmod_channels'][$row['uniquekey']]['rmodamount'];
								$rmodval 	= $bulk_rates_cache[$idroom][$idprice]['rmod_channels'][$row['uniquekey']]['rmodval'];
							}
						}
					}

					/**
					 * We just cannot stand users that keep altering the rates for Google Hotel. This is the main source
					 * of price accuracy mismatches, hence of accounts becoming unpublished. We always force no alterations.
					 * 
					 * @since 	1.8.13
					 */
					if ($row['uniquekey'] == VikChannelManagerConfig::GOOGLEHOTEL || $row['uniquekey'] == VikChannelManagerConfig::GOOGLEVR) {
						// force the upload of the same rates as IBE
						$rmod 		= '0';
						$rmodop 	= '1';
						$rmodamount = '0';
						$rmodval 	= '1';
					}

					// whether the rates for this channel will be modified
					$alter_rates = (intval($rmod) > 0 && floatval($rmodamount) > 0);

					// store room-channel alterations
					$room_channel_alterations[$row['uniquekey']] = [
						'alter_rates' => $alter_rates,
						'rmod' 		  => $rmod,
						'rmodop' 	  => $rmodop,
						'rmodamount'  => $rmodamount,
						'rmodval' 	  => $rmodval,
					];

					// Bulk Action Rates Upload allows to send information about the rateplan status (closed=true|false)
					$rplan_closed = 'false';
					if (strpos($maxlos, 'closed') !== false) {
						$maxlos = str_replace('closed', '', $maxlos);
						$rplan_closed = 'true';
					}

					/**
					 * Get an instance of a new rates flow record and populate initial data.
					 * 
					 * @since 	1.8.3
					 */
					$rflow_record_by = !empty($this->caller) ? $this->caller : __FUNCTION__;
					$rflow_rplan = [
						'id' => $rateplanid,
						'name' => $ota_rplan_name,
					];
					if ($rplan_closed == 'true') {
						$rflow_rplan['closed'] = $rplan_closed;
					}
					$rflow_record = $rflow_handler->getRecord()
						->setCreatedBy($rflow_record_by, $this->apiUser)
						->setDates($from_date, $to_date)
						->setChannelID($row['uniquekey'])
						->setOTARoomID($row['idroomota'])
						->setVBORoomID($idroom)
						->setVBORatePlanID($idprice)
						->setOTARatePlan($rflow_rplan)
						->setBaseFee($default_rate);

					// compose debug string
					$debug_str .= '$idroom:'.$idroom.' - channel:'.$row['name'].' - rateplanid:'.$rateplanid.(!empty($arimode) ? ' ('.$arimode.')' : '').($rplan_closed != 'false' ? ' (closed)' : '').' - from_date:'.$from_date.' - to_date:'.$to_date.(strlen($minlos) && strlen($maxlos) ? ' - '.$minlos.':'.$maxlos : '').' - alter_rates:'.($alter_rates ? 'true' : 'false');

					// keep building the XML request
					$xmlRQ .= "\t".'<RarUpdate from="'.$from_date.'" to="'.$to_date.'">'."\n";
					$xmlRQ .= "\t\t".'<RoomType id="'.$row['idroomota'].'" closed="false"'.(!empty($hotelid) ? ' hotelid="'.$hotelid.'"' : '').'>'."\n";
					$xmlRQ .= "\t\t\t".'<RatePlan id="'.$rateplanid.'" closed="'.$rplan_closed.'"'.(!empty($pricing_attr) ? ' pricing="'.$pricing_attr.'"' : '').(!empty($taxpolicy_attr) ? ' taxpolicy="'.$taxpolicy_attr.'"' : '').'>'."\n";

					if (in_array($pricingmodel, $los_keymodels)) {
						// LOS
						$date_ts = strtotime($from_date) + 7200;
						$end_date_ts = $date_ts + 86400;
						$q = "SELECT `d`.*,`p`.`name` AS `rate_name` FROM `#__vikbooking_dispcost` AS `d` LEFT JOIN `#__vikbooking_prices` `p` ON `p`.`id`=`d`.`idprice` AND `p`.`id`=".(int)$idprice." WHERE `d`.`idroom`=".(int)$idroom." AND `d`.`idprice`=".(int)$idprice." AND `d`.`days` < 31 ORDER BY `d`.`days` ASC, `d`.`cost` ASC;";
						$this->dbo->setQuery($q);
						$rates = $this->dbo->loadAssocList();
						if ($rates) {
							// load PMS rates
							$arr_rates = [];
							foreach ($rates as $rate) {
								$arr_rates[$rate['idroom']][] = $rate;
							}
							$arr_rates = VikBooking::applySeasonalPrices($arr_rates, $date_ts, $end_date_ts);
							$multi_rates = 1;
							foreach ($arr_rates as $idr => $tars) {
								$multi_rates = count($tars) > $multi_rates ? count($tars) : $multi_rates;
							}
							if ($multi_rates > 1) {
								for ($r = 1; $r < $multi_rates; $r++) {
									$deeper_rates = [];
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
									if (!$deeper_rates) {
										continue;
									}
									$deeper_rates = VikBooking::applySeasonalPrices($deeper_rates, $date_ts, ($end_date_ts + (86400 * $num_nights)) );
									foreach ($deeper_rates as $idr => $dtars) {
										foreach ($dtars as $dtk => $dtar) {
											$arr_rates[$idr][$r] = $dtar;
										}
									}
								}
							}
							//Exact rates - overwrite costs per nights
							if (intval($rmodop) > 1) {
								foreach ($arr_rates as $idr => $tars) {
									foreach ($tars as $tk => $tar) {
										$arr_rates[$idr][$tk]['cost'] = (float)$rmodamount * $tar['days'];
									}
								}
							}
							//
							$pricing = [];
							// charges/discounts per adults occupancy
							$los_plus_obp_pool = [
								VikChannelManagerConfig::BOOKING,
								VikChannelManagerConfig::AIRBNBAPI,
								VikChannelManagerConfig::GOOGLEHOTEL,
								VikChannelManagerConfig::GOOGLEVR,
								VikChannelManagerConfig::VRBOAPI,
							];
							if (in_array($row['uniquekey'], $los_plus_obp_pool)) {
								// Booking.com, Airbnb and Google Hotel LOS Pricing is based on occupancy. Expedia Pricing ByLengthOfStay is not based on the occupancy
								foreach ($occupancy as $occk => $num_adults) {
									$base_rates = $arr_rates;
									foreach ($base_rates as $r => $rates) {
										$diffusageprice = VikBooking::loadAdultsDiff($r, (int)$num_adults);
										/**
										 * Occupancy pricing rules may be altered by the same rule as for the base room costs.
										 * This is to increase the rooms costs for the channels as well as the occupancy pricing rules
										 * defined for the website. This applies only to charges/discounts with absolute values, not percent.
										 * 
										 * @since 	1.6.17
										 */
										if ($alter_rates === true && isset($bulk_rates_adv_params['alter_occrules']) && (int)$bulk_rates_adv_params['alter_occrules']) {
											$diffusageprice = VikChannelManager::alterRoomOccupancyPricingRules($diffusageprice, $rmodop, $rmodval, $rmodamount);
										}
										//Occupancy Override - Special Price may be setting a charge/discount for this occupancy while default price had no occupancy pricing
										if (!is_array($diffusageprice)) {
											foreach ($rates as $kpr => $vpr) {
												if (array_key_exists('occupancy_ovr', $vpr) && array_key_exists((int)$num_adults, $vpr['occupancy_ovr']) && strlen($vpr['occupancy_ovr'][(int)$num_adults]['value'])) {
													$diffusageprice = $vpr['occupancy_ovr'][(int)$num_adults];
													break;
												}
											}
											reset($rates);
										}
										//
										if (is_array($diffusageprice)) {
											foreach ($rates as $kpr => $vpr) {
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
														$base_rates[$r][$kpr]['diffusagecost'][$num_adults] = $aduseval;
														$base_rates[$r][$kpr]['cost'] += $aduseval;
													} else {
														//percentage value
														$aduseval = $diffusageprice['pernight'] == 1 ? round(($arr_rates[$r][$kpr]['cost'] * $diffusageprice['value'] / 100) * $base_rates[$r][$kpr]['days'], 2) : round(($arr_rates[$r][$kpr]['cost'] * $diffusageprice['value'] / 100), 2);
														$base_rates[$r][$kpr]['diffusagecost'][$num_adults] = $aduseval;
														$base_rates[$r][$kpr]['cost'] += $aduseval;
													}
												} else {
													//discount
													if ($diffusageprice['valpcent'] == 1) {
														//fixed value
														$aduseval = $diffusageprice['pernight'] == 1 ? $diffusageprice['value'] * $base_rates[$r][$kpr]['days'] : $diffusageprice['value'];
														$base_rates[$r][$kpr]['diffusagediscount'][$num_adults] = $aduseval;
														$base_rates[$r][$kpr]['cost'] -= $aduseval;
													} else {
														//percentage value
														$aduseval = $diffusageprice['pernight'] == 1 ? round(((($arr_rates[$r][$kpr]['cost'] / $base_rates[$r][$kpr]['days']) * $diffusageprice['value'] / 100) * $base_rates[$r][$kpr]['days']), 2) : round(($arr_rates[$r][$kpr]['cost'] * $diffusageprice['value'] / 100), 2);
														$base_rates[$r][$kpr]['diffusagediscount'][$num_adults] = $aduseval;
														$base_rates[$r][$kpr]['cost'] -= $aduseval;
													}
												}
											}
										}
									}
									//Taxes included or Excluded
									if ($tax_rates) {
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
									//build response array for LOS
									foreach ($base_rates[$idroom] as $rate_ind => $vpr) {
										if (!array_key_exists($num_adults, $pricing)) {
											$pricing[$num_adults] = [
												$vpr['days'] => round($vpr['cost'], 2)
											];
										} else {
											$pricing[$num_adults][$vpr['days']] = round($vpr['cost'], 2);
										}
									}
									//
								}
							} else {
								// occupany not needed for LOS
								foreach ($arr_rates[$idroom] as $rate_ind => $vpr) {
									$pricing[0][$vpr['days']] = round($vpr['cost'], 2);
								}
							}

							// Compose XML Nodes
							if (strpos($pricingmodel, 'any') !== false) {
								// LOS Pricing + Default Pricing
								$season_rates = [
									$idroom => [
										0 => [
											'id' => -1,
											'idroom' => $idroom,
											'days' => 1,
											'idprice' => $idprice,
											'cost' => (float)$default_rate,
										]
									]
								];
								$date_ts = strtotime($from_date) + 7200;
								$end_date_ts = $date_ts + 86400;
								$season_rates = VikBooking::applySeasonalPrices($season_rates, $date_ts, $end_date_ts);
								$setcost = $season_rates[$idroom][0]['cost'];

								// Taxes
								if ($tax_rates && array_key_exists($idprice, $tax_rates)) {
									if ($taxincl_price_compare === true) {
										if (!$vbo_tax_included) {
											$setcost = VikBooking::sayCostPlusIva($setcost, $idprice);
										}
									} else {
										if ($vbo_tax_included) {
											$setcost = VikBooking::sayCostMinusIva($setcost, $idprice);
										}
									}
								}

								// modified rates
								$ch_alter_str = '';
								if ($alter_rates === true) {
									if (intval($rmodop) > 0 && intval($rmodop) < 2) {
										// Increase rates
										if (intval($rmodval) > 0) {
											// Percentage charge
											$setcost = $setcost * (100 + (float)$rmodamount) / 100;
											$ch_alter_str = '+' . (float)$rmodamount . '%';
										} else {
											// Fixed charge
											$setcost += (float)$rmodamount;
											$ch_alter_str = '+' . (float)$rmodamount;
										}
									} elseif (intval($rmodop) > 1) {
										// Exact rates
										$setcost = (float)$rmodamount;

										/**
										 * VBO Rates Overview may calculate before the exact amount to transmit
										 * for each channel by using the bulk rates cache of VCM. In this case,
										 * we enter this statement of the "exact rate" when no alterations have
										 * been defined at channel level. However, for the rates flow records it
										 * may be useful to know how the VBO rate was increased for this channel.
										 * 
										 * @since 	1.8.3
										 */
										if ($this->caller == 'VBO' && isset($bulk_rates_cache[$idroom]) && isset($bulk_rates_cache[$idroom][$idprice])) {
											if ((int)$bulk_rates_cache[$idroom][$idprice]['rmod'] > 0 && (float)$bulk_rates_cache[$idroom][$idprice]['rmodamount'] > 0) {
												// VBO must have increased the rate already, so calculate how this was made
												if ((int)$bulk_rates_cache[$idroom][$idprice]['rmodop'] > 0) {
													//Increase rates
													if ((int)$bulk_rates_cache[$idroom][$idprice]['rmodval'] > 0) {
														//Percentage charge
														$ch_alter_str = '+' . (float)$bulk_rates_cache[$idroom][$idprice]['rmodamount'] . '%';
													} else {
														//Fixed charge
														$ch_alter_str = '+' . (float)$bulk_rates_cache[$idroom][$idprice]['rmodamount'];
													}
												} else {
													//Lower rates
													if ((int)$bulk_rates_cache[$idroom][$idprice]['rmodval'] > 0) {
														//Percentage discount
														$ch_alter_str = '-' . (float)$bulk_rates_cache[$idroom][$idprice]['rmodamount'] . '%';
													} else {
														//Fixed discount
														$ch_alter_str = '-' . (float)$bulk_rates_cache[$idroom][$idprice]['rmodamount'];
													}
												}
											}
										}
									} else {
										//Lower rates
										if (intval($rmodval) > 0) {
											//Percentage discount
											$disc_op = $setcost * (float)$rmodamount / 100;
											$setcost -= $disc_op;
											$ch_alter_str = '-' . (float)$rmodamount . '%';
										} else {
											//Fixed discount
											$setcost -= (float)$rmodamount;
											$ch_alter_str = '-' . (float)$rmodamount;
										}
									}
								}

								if ($setcost < 0) {
									/**
									 * Rather than skipping the request with "continue;" we ensure the cost is actually 0. This is
									 * better, or the XML would be broken due to unclosed XML nodes and schema errors would occur.
									 * 
									 * @since 	1.8.24
									 */
									$setcost = 0;
								}

								// set the nightly fee for the rates flow record
								$rflow_record->setNightlyFee($setcost);
								if (!empty($ch_alter_str)) {
									// set also the channel rates alteration string
									$rflow_record->setChannelAlteration($ch_alter_str);
								}

								// build necessary XML node
								$xmlRQ .= "\t\t\t\t".'<Rate>'."\n";
								$xmlRQ .= "\t\t\t\t\t".'<PerDay rate="'.round($setcost, 2).'"/>'."\n";
								$xmlRQ .= "\t\t\t\t".'</Rate>'."\n";

								// set breakdown info
								$breakdown[$from_date . '-' . $to_date] = ($breakdown[$from_date . '-' . $to_date] ?? '') . $log_rateplanid . 'Per Day Rate: ' . (!empty($channel_currency) ? $channel_currency : $currency) . ' ' . round($setcost, 2) . " \n";
							}

							/**
							 * In case of LOS pricing model, the nightly fee of the room is caluclated
							 * by taking the cost for 1 night for the lowest occupancy.
							 * We also collect a list of LOS prices for the rates flow record.
							 * The channel rates alteration will not change with LOS records or occupancy.
							 * 
							 * @since 	1.8.3
							 */
							$los_nightly_fee = null;
							$los_occ_rates 	 = [];
							$ch_alter_str 	 = '';

							// parse the LOS records for occupancy and length of stay (number of nights)
							foreach ($pricing as $occk => $calc_rates) {
								$los_occ_rates[$occk] = [];
								foreach ($calc_rates as $nights => $setcost) {
									//modified rates
									if ($alter_rates === true) {
										if (intval($rmodop) > 0 && intval($rmodop) < 2) {
											//Increase rates
											if (intval($rmodval) > 0) {
												//Percentage charge
												$setcost = $setcost * (100 + (float)$rmodamount) / 100;
												$ch_alter_str = '+' . (float)$rmodamount . '%';
											} else {
												//Fixed charge
												$setcost += (float)$rmodamount;
												$ch_alter_str = '+' . (float)$rmodamount;
											}
										} elseif (intval($rmodop) > 1) {
											//Exact rates (exact rate is already multiplied by number of nights in the check at overwrite costs per nights above)
											$setcost = $setcost;

											/**
											 * VBO Rates Overview may calculate before the exact amount to transmit
											 * for each channel by using the bulk rates cache of VCM. In this case,
											 * we enter this statement of the "exact rate" when no alterations have
											 * been defined at channel level. However, for the rates flow records it
											 * may be useful to know how the VBO rate was increased for this channel.
											 * 
											 * @since 	1.8.3
											 */
											if ($this->caller == 'VBO' && isset($bulk_rates_cache[$idroom]) && isset($bulk_rates_cache[$idroom][$idprice])) {
												if ((int)$bulk_rates_cache[$idroom][$idprice]['rmod'] > 0 && (float)$bulk_rates_cache[$idroom][$idprice]['rmodamount'] > 0) {
													// VBO must have increased the rate already, so calculate how this was made
													if ((int)$bulk_rates_cache[$idroom][$idprice]['rmodop'] > 0) {
														//Increase rates
														if ((int)$bulk_rates_cache[$idroom][$idprice]['rmodval'] > 0) {
															//Percentage charge
															$ch_alter_str = '+' . (float)$bulk_rates_cache[$idroom][$idprice]['rmodamount'] . '%';
														} else {
															//Fixed charge
															$ch_alter_str = '+' . (float)$bulk_rates_cache[$idroom][$idprice]['rmodamount'];
														}
													} else {
														//Lower rates
														if ((int)$bulk_rates_cache[$idroom][$idprice]['rmodval'] > 0) {
															//Percentage discount
															$ch_alter_str = '-' . (float)$bulk_rates_cache[$idroom][$idprice]['rmodamount'] . '%';
														} else {
															//Fixed discount
															$ch_alter_str = '-' . (float)$bulk_rates_cache[$idroom][$idprice]['rmodamount'];
														}
													}
												}
											}
										} else {
											//Lower rates
											if (intval($rmodval) > 0) {
												//Percentage discount
												$disc_op = $setcost * (float)$rmodamount / 100;
												$setcost -= $disc_op;
												$ch_alter_str = '-' . (float)$rmodamount . '%';
											} else {
												//Fixed discount
												$setcost -= (float)$rmodamount;
												$ch_alter_str = '-' . (float)$rmodamount;
											}
										}
									}

									if ($setcost < 0) {
										/**
										 * Rather than skipping the request with "continue;" we ensure the cost is actually 0. This is
										 * better, or the XML would be broken due to unclosed XML nodes and schema errors would occur.
										 * 
										 * @since 	1.8.24
										 */
										$setcost = 0;
									}

									// check LOS nightly fee
									$los_nightly_fee = $los_nightly_fee === null ? $setcost : $los_nightly_fee;

									if ($occk > 0) {
										// push LOS price to the current occupancy pool
										$los_occ_rates[$occk][$nights] = $setcost;
									}

									// build XML nodes for LOS records
									$xmlRQ .= "\t\t\t\t".'<Rate lengthOfStay="'.$nights.'">'."\n";
									$xmlRQ .= "\t\t\t\t\t".'<PerDay rate="'.round($setcost, 2).'"'.($occk > 0 ? ' usage="'.$occk.'"' : '').'/>'."\n";
									$xmlRQ .= "\t\t\t\t".'</Rate>'."\n";
								}
								// make sure some LOS rates have been set for this occupancy
								if (!$los_occ_rates[$occk]) {
									unset($los_occ_rates[$occk]);
								}
							}

							// push data for the rates flow record
							$rflow_record->setNightlyFee($los_nightly_fee);
							if (!empty($ch_alter_str)) {
								// set also the channel rates alteration string
								$rflow_record->setChannelAlteration($ch_alter_str);
							}
							$rflow_record->setLOSRecords($los_occ_rates);
						} else {
							//No rates defined in VBO. Skip update request for this room (3 foreach)
							$responses[$idroom][$row['idchannel']] = 'e4j.error.'.VikChannelManager::getErrorFromMap('e4j.error.Missing Rates in VikBooking');
							continue 3;
						}
					} else {
						// PerDay Pricing, not LOS: apply special prices and for Agoda, Bed-and-breakfast.it, Bedandbreakfast.eu etc., apply also occupancy pricing
						$arr_rates = [
							$idroom => [
								0 => [
									'id' => -1,
									'idroom' => $idroom,
									'days' => 1,
									'idprice' => $idprice,
									'cost' => (float)$default_rate,
								]
							]
						];

						/**
						 * Cached pricing data can be set to reduce the work load for calculating the room rates.
						 * 
						 * @since 	1.9.10
						 */
						if ($this->pricingData[$ind][$from_date] ?? 0) {
							// use the previously cached rate
							$arr_rates[$idroom][0]['cost'] = (float) $this->pricingData[$ind][$from_date];
						} else {
							$date_ts = strtotime($from_date) + 7200;
							$end_date_ts = $date_ts + 86400;
							$arr_rates = VikBooking::applySeasonalPrices($arr_rates, $date_ts, $end_date_ts);
						}

						// set the calculated room/listing nightly rate
						$setcost = $arr_rates[$idroom][0]['cost'];

						// Taxes
						if ($tax_rates && array_key_exists($idprice, $tax_rates)) {
							if ($taxincl_price_compare === true) {
								if (!$vbo_tax_included) {
									$setcost = VikBooking::sayCostPlusIva($setcost, $idprice);
								}
							} else {
								if ($vbo_tax_included) {
									$setcost = VikBooking::sayCostMinusIva($setcost, $idprice);
								}
							}
						}

						// modified rates
						$ch_alter_str = '';
						if ($alter_rates === true) {
							if (intval($rmodop) > 0 && intval($rmodop) < 2) {
								// Increase rates
								if (intval($rmodval) > 0) {
									// Percentage charge
									$setcost = $setcost * (100 + (float)$rmodamount) / 100;
									$ch_alter_str = '+' . (float)$rmodamount . '%';
								} else {
									// Fixed charge
									$setcost += (float)$rmodamount;
									$ch_alter_str = '+' . (float)$rmodamount;
								}
							} elseif (intval($rmodop) > 1) {
								// Exact rates
								$setcost = (float)$rmodamount;
								$arr_rates[$idroom][0]['cost'] = (float)$rmodamount;

								/**
								 * VBO Rates Overview may calculate before the exact amount to transmit
								 * for each channel by using the bulk rates cache of VCM. In this case,
								 * we enter this statement of the "exact rate" when no alterations have
								 * been defined at channel level. However, for the rates flow records it
								 * may be useful to know how the VBO rate was increased for this channel.
								 * 
								 * @since 	1.8.3
								 */
								if ($this->caller == 'VBO' && isset($bulk_rates_cache[$idroom]) && isset($bulk_rates_cache[$idroom][$idprice])) {
									if ((int)$bulk_rates_cache[$idroom][$idprice]['rmod'] > 0 && (float)$bulk_rates_cache[$idroom][$idprice]['rmodamount'] > 0) {
										// VBO must have increased the rate already, so calculate how this was made
										if ((int)$bulk_rates_cache[$idroom][$idprice]['rmodop'] > 0) {
											//Increase rates
											if ((int)$bulk_rates_cache[$idroom][$idprice]['rmodval'] > 0) {
												//Percentage charge
												$ch_alter_str = '+' . (float)$bulk_rates_cache[$idroom][$idprice]['rmodamount'] . '%';
											} else {
												//Fixed charge
												$ch_alter_str = '+' . (float)$bulk_rates_cache[$idroom][$idprice]['rmodamount'];
											}
										} else {
											//Lower rates
											if ((int)$bulk_rates_cache[$idroom][$idprice]['rmodval'] > 0) {
												//Percentage discount
												$ch_alter_str = '-' . (float)$bulk_rates_cache[$idroom][$idprice]['rmodamount'] . '%';
											} else {
												//Fixed discount
												$ch_alter_str = '-' . (float)$bulk_rates_cache[$idroom][$idprice]['rmodamount'];
											}
										}
									}
								}
							} else {
								//Lower rates
								if (intval($rmodval) > 0) {
									//Percentage discount
									$disc_op = $setcost * (float)$rmodamount / 100;
									$setcost -= $disc_op;
									$ch_alter_str = '-' . (float)$rmodamount . '%';
								} else {
									//Fixed discount
									$setcost -= (float)$rmodamount;
									$ch_alter_str = '-' . (float)$rmodamount;
								}
							}
						}

						if ($setcost < 0) {
							/**
							 * Rather than skipping the request with "continue;" we ensure the cost is actually 0. This is
							 * better, or the XML would be broken due to unclosed XML nodes and schema errors would occur.
							 * 
							 * @since 	1.8.24
							 */
							$setcost = 0;
						}

						// set the nightly fee for the rates flow record
						$rflow_record->setNightlyFee($setcost);
						if (!empty($ch_alter_str)) {
							// set also the channel rates alteration string
							$rflow_record->setChannelAlteration($ch_alter_str);
						}

						// enhance debug string
						$debug_str .= ' - setcost:'.$setcost;

						// list of channels supporting OBP rules
						$gen_obp_pool = [
							VikChannelManagerConfig::AGODA,
							VikChannelManagerConfig::YCS50,
							VikChannelManagerConfig::GARDAPASS,
							VikChannelManagerConfig::BEDANDBREAKFASTIT,
							VikChannelManagerConfig::BEDANDBREAKFASTEU,
							VikChannelManagerConfig::BEDANDBREAKFASTNL,
							VikChannelManagerConfig::FERATEL,
							VikChannelManagerConfig::PITCHUP,
							VikChannelManagerConfig::AIRBNBAPI,
							VikChannelManagerConfig::GOOGLEHOTEL,
							VikChannelManagerConfig::GOOGLEVR,
							VikChannelManagerConfig::VRBOAPI,
						];
						if (
							in_array($row['uniquekey'], $gen_obp_pool) 
							|| 
							($row['uniquekey'] == VikChannelManagerConfig::EXPEDIA && strpos($pricingmodel, 'OccupancyBasedPricing') !== false) 
							|| 
							($row['uniquekey'] == VikChannelManagerConfig::DESPEGAR && (int)$pricing_attr == 21)
							|| 
							($row['uniquekey'] == VikChannelManagerConfig::OTELZ && count($occupancy) > 1)
							|| 
							($row['uniquekey'] == VikChannelManagerConfig::BOOKING && isset($bulk_rates_adv_params['bcom_pricing_model_' . $rateplanid]) && $bulk_rates_adv_params['bcom_pricing_model_' . $rateplanid] == 'OBP')
						) {
							// calculate occupancy pricing for single, double and full rates or for the Expedia OccupancyBasedPricing or Booking.com OBP (July 2020)
							$pricing = [];
							foreach ($occupancy as $occk => $num_adults) {
								$base_rates = $arr_rates;
								foreach ($base_rates as $r => $rates) {
									$diffusageprice = VikBooking::loadAdultsDiff($r, (int)$num_adults);
									/**
									 * Occupancy pricing rules may be altered by the same rule as for the base room costs.
									 * This is to increase the rooms costs for the channels as well as the occupancy pricing rules
									 * defined for the website. This applies only to charges/discounts with absolute values, not percent.
									 * 
									 * @since 	1.6.17
									 */
									if ($alter_rates === true && isset($bulk_rates_adv_params['alter_occrules']) && (int)$bulk_rates_adv_params['alter_occrules']) {
										$diffusageprice = VikChannelManager::alterRoomOccupancyPricingRules($diffusageprice, $rmodop, $rmodval, $rmodamount);
									}
									if (!is_array($diffusageprice)) {
										foreach ($rates as $kpr => $vpr) {
											if (array_key_exists('occupancy_ovr', $vpr) && array_key_exists((int)$num_adults, $vpr['occupancy_ovr']) && strlen($vpr['occupancy_ovr'][(int)$num_adults]['value'])) {
												$diffusageprice = $vpr['occupancy_ovr'][(int)$num_adults];
												break;
											}
										}
										reset($rates);
									}
									if (is_array($diffusageprice)) {
										foreach ($rates as $kpr => $vpr) {
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
													$base_rates[$r][$kpr]['diffusagecost'][$num_adults] = $aduseval;
													$base_rates[$r][$kpr]['cost'] += $aduseval;
												} else {
													//percentage value
													$aduseval = $diffusageprice['pernight'] == 1 ? round(($arr_rates[$r][$kpr]['cost'] * $diffusageprice['value'] / 100) * $base_rates[$r][$kpr]['days'], 2) : round(($arr_rates[$r][$kpr]['cost'] * $diffusageprice['value'] / 100), 2);
													$base_rates[$r][$kpr]['diffusagecost'][$num_adults] = $aduseval;
													$base_rates[$r][$kpr]['cost'] += $aduseval;
												}
											} else {
												//discount
												if ($diffusageprice['valpcent'] == 1) {
													//fixed value
													$aduseval = $diffusageprice['pernight'] == 1 ? $diffusageprice['value'] * $base_rates[$r][$kpr]['days'] : $diffusageprice['value'];
													$base_rates[$r][$kpr]['diffusagediscount'][$num_adults] = $aduseval;
													$base_rates[$r][$kpr]['cost'] -= $aduseval;
												} else {
													//percentage value
													$aduseval = $diffusageprice['pernight'] == 1 ? round(((($arr_rates[$r][$kpr]['cost'] / $base_rates[$r][$kpr]['days']) * $diffusageprice['value'] / 100) * $base_rates[$r][$kpr]['days']), 2) : round(($arr_rates[$r][$kpr]['cost'] * $diffusageprice['value'] / 100), 2);
													$base_rates[$r][$kpr]['diffusagediscount'][$num_adults] = $aduseval;
													$base_rates[$r][$kpr]['cost'] -= $aduseval;
												}
											}
										}
									}
								}
								$pricing[$num_adults] = $base_rates[$idroom][0]['cost'];
							}
							/**
							 * No matter if rates should be altered for this channel, we need to make sure
							 * the prices are inclusive or exclusive of taxes. Earlier versions only checked
							 * if taxes should be applied or detucted if rates needed to be altered.
							 * 
							 * @since 	1.8.1
							 */
							foreach ($pricing as $adu => $tot_rate) {
								// taxes
								if ($tax_rates && array_key_exists($idprice, $tax_rates)) {
									if ($taxincl_price_compare === true) {
										if (!$vbo_tax_included) {
											$tot_rate = VikBooking::sayCostPlusIva($tot_rate, $idprice);
										}
									} else {
										if ($vbo_tax_included) {
											$tot_rate = VikBooking::sayCostMinusIva($tot_rate, $idprice);
										}
									}
								}
								$pricing[$adu] = $tot_rate;
							}
							if ($alter_rates === true) {
								// modified rates for occupancy are altered after the occupancy pricing are applied
								foreach ($pricing as $adu => $tot_rate) {
									if (intval($rmodop) > 0 && intval($rmodop) < 2) {
										//Increase rates
										if (intval($rmodval) > 0) {
											//Percentage charge
											$pricing[$adu] = $tot_rate * (100 + (float)$rmodamount) / 100;
										} else {
											//Fixed charge
											$pricing[$adu] = $tot_rate + (float)$rmodamount;
										}
									} elseif (intval($rmodop) > 1) {
										//Exact rates
										$pricing[$adu] = $tot_rate;
									} else {
										//Lower rates
										if (intval($rmodval) > 0) {
											//Percentage discount
											$disc_op = $tot_rate * (float)$rmodamount / 100;
											$pricing[$adu] = $tot_rate - $disc_op;
										} else {
											//Fixed discount
											$pricing[$adu] = $tot_rate - (float)$rmodamount;
										}
									}
								}
							}
							// Agoda single, double and full rates, Bed-and-breakfast.it single, double and extrabed rates, Gardapass Single and Full rates
							if (strpos($pricingmodel, 'SingleRate') !== false) {
								$use_cost = array_key_exists(1, $pricing) ? $pricing[1] : $setcost;
								$xmlRQ .= "\t\t\t\t".'<Rate>'."\n";
								$xmlRQ .= "\t\t\t\t\t".'<SingleRate rate="'.round($use_cost, 2).'"/>'."\n";
								$xmlRQ .= "\t\t\t\t".'</Rate>'."\n";
								$breakdown[$from_date . '-' . $to_date] = ($breakdown[$from_date . '-' . $to_date] ?? '') . 'Single Rate: ' . (!empty($channel_currency) ? $channel_currency : $currency) . ' ' . round($use_cost, 2) . " \n";
							}
							if (strpos($pricingmodel, 'DoubleRate') !== false) {
								$use_cost = array_key_exists(2, $pricing) ? $pricing[2] : $setcost;
								$xmlRQ .= "\t\t\t\t".'<Rate>'."\n";
								$xmlRQ .= "\t\t\t\t\t".'<DoubleRate rate="'.round($use_cost, 2).'"/>'."\n";
								$xmlRQ .= "\t\t\t\t".'</Rate>'."\n";
								$breakdown[$from_date . '-' . $to_date] = ($breakdown[$from_date . '-' . $to_date] ?? '') . 'Double Rate: ' . (!empty($channel_currency) ? $channel_currency : $currency) . ' ' . round($use_cost, 2) . " \n";
							}
							if (strpos($pricingmodel, 'ExtraBed') !== false && $occupancy) {
								/**
								 * We use the same technique of Airbnb to calculate the cost for the extra bed
								 * beyond an occupancy of 2. This usually applies to Bed-and-breakfast.it.
								 * 
								 * @since 	1.9.10
								 */
								$prices_pool = [];
								foreach ($occupancy as $num_adults) {
									array_push($prices_pool, (isset($pricing[$num_adults]) ? $pricing[$num_adults] : $setcost));
								}
								$avg_cost 	= array_sum($prices_pool) / count($prices_pool);
								$mincost 	= min($prices_pool);
								$maxcost 	= max($prices_pool);
								$minocc 	= min($occupancy);
								$maxocc 	= max($occupancy);
								if ($avg_cost != $setcost && $minocc != $maxocc) {
									// occupancy pricing defined, take the price difference to calculate the average extra cost per adult
									$price_diff 	 = $maxcost - $mincost;
									$avg_extra_adult = $price_diff / ($maxocc - $minocc);
									$guests_included = 1;
									foreach ($occupancy as $num_adults) {
										$occ_cost = (isset($pricing[$num_adults]) ? $pricing[$num_adults] : $setcost);
										if ($mincost == $occ_cost) {
											// this number of adults is charged the same as one adult less
											$guests_included = $num_adults;
										}
									}
									if ($guests_included > $minocc && $guests_included < $maxocc) {
										/**
										 * Example: in case 1 adult and 2 adults pay the same, we should re-calculate
										 * the average cost per extra adult by using the guests includes as min occupancy.
										 */
										$avg_extra_adult = $price_diff / ($maxocc - $guests_included);
									}
									// set the extra bed cost
									$xmlRQ .= "\t\t\t\t".'<Rate>'."\n";
									$xmlRQ .= "\t\t\t\t\t".'<ExtraBed rate="' . round($avg_extra_adult, 2) . '"/>'."\n";
									$xmlRQ .= "\t\t\t\t".'</Rate>'."\n";
									$breakdown[$from_date . '-' . $to_date] = ($breakdown[$from_date . '-' . $to_date] ?? '') . 'ExtraBed Rate: ' . (!empty($channel_currency) ? $channel_currency : $currency) . ' ' . round($avg_extra_adult, 2) . " \n";
								}
							}
							if (strpos($pricingmodel, 'FullRate') !== false) {
								$max_room_occ = $pricing ? max(array_keys($pricing)) : 0;
								$use_cost = array_key_exists($max_room_occ, $pricing) ? $pricing[$max_room_occ] : $setcost;
								$xmlRQ .= "\t\t\t\t".'<Rate>'."\n";
								$xmlRQ .= "\t\t\t\t\t".'<FullRate rate="'.round($use_cost, 2).'"/>'."\n";
								$xmlRQ .= "\t\t\t\t".'</Rate>'."\n";
								$breakdown[$from_date . '-' . $to_date] = ($breakdown[$from_date . '-' . $to_date] ?? '') . 'Full Rate: ' . (!empty($channel_currency) ? $channel_currency : $currency) . ' ' . round($use_cost, 2) . " \n";
							}
							// Expedia OccupancyBasedPricing
							if (strpos($pricingmodel, 'OccupancyBasedPricing') !== false) {
								foreach ($occupancy as $occk => $num_adults) {
									$use_cost = array_key_exists($num_adults, $pricing) ? $pricing[$num_adults] : $setcost;
									$xmlRQ .= "\t\t\t\t".'<Rate>'."\n";
									$xmlRQ .= "\t\t\t\t\t".'<PerOccupancy rate="'.round($use_cost, 2).'" occupancy="'.$num_adults.'"/>'."\n";
									$xmlRQ .= "\t\t\t\t".'</Rate>'."\n";
									$breakdown[$from_date . '-' . $to_date] = ($breakdown[$from_date . '-' . $to_date] ?? '') . 'Occupancy ' . $num_adults . ': ' . (!empty($channel_currency) ? $channel_currency : $currency) . ' ' . round($use_cost, 2) . " \n";
								}
							}
							// Despegar PerPersonPerNight
							if ($row['uniquekey'] == VikChannelManagerConfig::DESPEGAR && (int)$pricing_attr == 21) {
								$from_room_occ = 2;
								$to_room_occ = 2;
								if (isset($ota_rate_plan['RoomInfo'])) {
									if (isset($ota_rate_plan['RoomInfo']['MinOccupancy'])) {
										$from_room_occ = (int)$ota_rate_plan['RoomInfo']['MinOccupancy'];
									}
									if (isset($ota_rate_plan['RoomInfo']['MaxOccupancy'])) {
										$to_room_occ = (int)$ota_rate_plan['RoomInfo']['MaxOccupancy'];
									}
								}
								$from_room_occ = $from_room_occ < 1 ? 1 : $from_room_occ;
								$to_room_occ = $to_room_occ < $from_room_occ ? $from_room_occ : $to_room_occ;
								for ($x = $from_room_occ; $x <= $to_room_occ; $x++) {
									$use_cost = array_key_exists($x, $pricing) ? $pricing[$x] : $setcost;
									$xmlRQ .= "\t\t\t\t".'<Rate>'."\n";
									$xmlRQ .= "\t\t\t\t\t".'<PerDay rate="'.round($use_cost, 2).'" usage="'.$x.'"/>'."\n";
									$xmlRQ .= "\t\t\t\t".'</Rate>'."\n";
								}
								// the breakdown in this case is only displayed for the max occupancy (last for-loop)
								$breakdown[$from_date . '-' . $to_date] = ($breakdown[$from_date . '-' . $to_date] ?? '') . $log_rateplanid . 'Rate per person (' . $to_room_occ . '): ' . (!empty($channel_currency) ? $channel_currency : $currency) . ' ' . round($use_cost, 2) . " \n";
							}
							//Otelz.com Room Price by Adults
							if ($row['uniquekey'] == VikChannelManagerConfig::OTELZ && count($occupancy) > 1) {
								//always transmit the rate update with no usage, so Room Price
								$xmlRQ .= "\t\t\t\t".'<Rate>'."\n";
								$xmlRQ .= "\t\t\t\t\t".'<PerDay rate="'.round($setcost, 2).'"/>'."\n";
								$xmlRQ .= "\t\t\t\t".'</Rate>'."\n";
								$breakdown[$from_date . '-' . $to_date] = ($breakdown[$from_date . '-' . $to_date] ?? '') . $log_rateplanid . 'Rate per night: ' . (!empty($channel_currency) ? $channel_currency : $currency) . ' ' . round($setcost, 2) . " \n";
								foreach ($occupancy as $occk => $num_adults) {
									//Room Price by Adults
									if (!isset($pricing[$num_adults])) {
										continue;
									}
									$use_cost = array_key_exists($num_adults, $pricing) ? $pricing[$num_adults] : $setcost;
									$xmlRQ .= "\t\t\t\t".'<Rate>'."\n";
									$xmlRQ .= "\t\t\t\t\t".'<PerDay rate="'.round($use_cost, 2).'" usage="'.$num_adults.'"/>'."\n";
									$xmlRQ .= "\t\t\t\t".'</Rate>'."\n";
									$breakdown[$from_date . '-' . $to_date] = ($breakdown[$from_date . '-' . $to_date] ?? '') . 'Occupancy ' . $num_adults . ': ' . (!empty($channel_currency) ? $channel_currency : $currency) . ' ' . round($use_cost, 2) . " \n";
								}
							}
							// Bedandbreakfast.eu and Bedandbreakfast.nl PerDay + Occupancy
							if ($row['uniquekey'] == VikChannelManagerConfig::BEDANDBREAKFASTEU || $row['uniquekey'] == VikChannelManagerConfig::BEDANDBREAKFASTNL) {
								foreach ($occupancy as $occk => $num_adults) {
									$use_cost = array_key_exists($num_adults, $pricing) ? $pricing[$num_adults] : $setcost;
									$xmlRQ .= "\t\t\t\t".'<Rate>'."\n";
									$xmlRQ .= "\t\t\t\t\t".'<PerDay rate="'.round($use_cost, 2).'" usage="'.$num_adults.'"/>'."\n";
									$xmlRQ .= "\t\t\t\t".'</Rate>'."\n";
									$breakdown[$from_date . '-' . $to_date] = ($breakdown[$from_date . '-' . $to_date] ?? '') . 'Occupancy ' . $num_adults . ': ' . (!empty($channel_currency) ? $channel_currency : $currency) . ' ' . round($use_cost, 2) . " \n";
								}
							}
							// Feratel PerDay + Occupancy
							if ($row['uniquekey'] == VikChannelManagerConfig::FERATEL) {
								foreach ($occupancy as $occk => $num_adults) {
									$use_cost = array_key_exists($num_adults, $pricing) ? $pricing[$num_adults] : $setcost;
									$xmlRQ .= "\t\t\t\t".'<Rate>'."\n";
									$xmlRQ .= "\t\t\t\t\t".'<PerDay rate="'.round($use_cost, 2).'" usage="'.$num_adults.'"/>'."\n";
									$xmlRQ .= "\t\t\t\t".'</Rate>'."\n";
									$breakdown[$from_date . '-' . $to_date] = ($breakdown[$from_date . '-' . $to_date] ?? '') . 'Occupancy ' . $num_adults . ': ' . (!empty($channel_currency) ? $channel_currency : $currency) . ' ' . round($use_cost, 2) . " \n";
								}
							}
							/**
							 * Pitchup.com: we tell the clients to base their prices on the lowest/minimum adults occupancy.
							 * In order to calculate the "price per pitch" and the "price per extra adult", we take the costs
							 * for any occupancy, and we make an average calculation to obtain the price per extra adult.
							 * 
							 * @since 	1.6.18
							 */
							if ($row['uniquekey'] == VikChannelManagerConfig::PITCHUP) {
								$prices_pool = [];
								foreach ($occupancy as $occk => $num_adults) {
									array_push($prices_pool, (isset($pricing[$num_adults]) ? $pricing[$num_adults] : $setcost));
								}
								$avg_cost 	= array_sum($prices_pool) / count($prices_pool);
								$mincost 	= min($prices_pool);
								$maxcost 	= max($prices_pool);
								$minocc 	= min($occupancy);
								$maxocc 	= max($occupancy);
								if ($avg_cost == $setcost || $minocc == $maxocc) {
									// no charges or discounts per number of adults, we transmit just the price per pitch
									$xmlRQ .= "\t\t\t\t".'<Rate>'."\n";
									$xmlRQ .= "\t\t\t\t\t".'<PerDay rate="'.round($setcost, 2).'"/>'."\n";
									$xmlRQ .= "\t\t\t\t".'</Rate>'."\n";
									$breakdown[$from_date . '-' . $to_date] = ($breakdown[$from_date . '-' . $to_date] ?? '') . $log_rateplanid . 'Price per Pitch: ' . (!empty($channel_currency) ? $channel_currency : $currency) . ' ' . round($setcost, 2) . " \n";
								} else {
									// prices per occupancy were defined. We take the price difference to calculate the average extra cost per adult
									$price_diff 	 = $maxcost - $mincost;
									$avg_extra_adult = $price_diff / ($maxocc - $minocc);
									/**
									 * Example:
									 * 2 adults = 150
									 * 3 adults = 190
									 * 4 adults = 230
									 * price diff = 80
									 * price per pitch = min cost = 150
									 * price per extra adult = (max cost - min cost) / (max occupancy - min occupancy)
									 * price per extra adult = (230 - 150) / (4 - 2) = 40
									 * 
									 * We transmit two Rate nodes, the price per pitch will have an attribute usage="1"
									 * while the price per extra adult will have an attribute usage="2".
									 */
									$xmlRQ .= "\t\t\t\t".'<Rate>'."\n";
									$xmlRQ .= "\t\t\t\t\t".'<PerDay rate="'.round($mincost, 2).'" usage="1"/>'."\n";
									$xmlRQ .= "\t\t\t\t".'</Rate>'."\n";
									$xmlRQ .= "\t\t\t\t".'<Rate>'."\n";
									$xmlRQ .= "\t\t\t\t\t".'<PerDay rate="'.round($avg_extra_adult, 2).'" usage="2"/>'."\n";
									$xmlRQ .= "\t\t\t\t".'</Rate>'."\n";
									$breakdown[$from_date . '-' . $to_date] = ($breakdown[$from_date . '-' . $to_date] ?? '') . $log_rateplanid . 'Price per Pitch: ' . (!empty($channel_currency) ? $channel_currency : $currency) . ' ' . round($setcost, 2) . " \n";
									$breakdown[$from_date . '-' . $to_date] .= 'Extra Adult: ' . (!empty($channel_currency) ? $channel_currency : $currency) . ' ' . round($avg_extra_adult, 2) . " \n";
								}
							}
							//
							/**
							 * Airbnb API: we tell the clients to base their prices on the lowest/minimum adults occupancy.
							 * In order to calculate the "daily price" and the "price per extra person", we take the costs
							 * for any occupancy, and we make an average calculation to obtain the price per extra person.
							 * 
							 * @since 	1.8.0
							 * @since 	1.9.16 added support to hidden advanced option to disable Airbnb OBP calculation.
							 */
							if ($row['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI) {
								$prices_pool = [];
								foreach ($occupancy as $occk => $num_adults) {
									array_push($prices_pool, (isset($pricing[$num_adults]) ? $pricing[$num_adults] : $setcost));
								}
								$avg_cost 	= array_sum($prices_pool) / count($prices_pool);
								$mincost 	= min($prices_pool);
								$maxcost 	= max($prices_pool);
								$minocc 	= min($occupancy);
								$maxocc 	= max($occupancy);
								if ($avg_cost == $setcost || $minocc == $maxocc || !empty($bulk_rates_adv_params['airbnb_no_obp'])) {
									// no charges or discounts per number of adults, we transmit just the "daily price"
									$xmlRQ .= "\t\t\t\t" . '<Rate>' . "\n";
									$xmlRQ .= "\t\t\t\t\t" . '<PerDay rate="' . round($setcost, 2) . '"/>' . "\n";
									$xmlRQ .= "\t\t\t\t" . '</Rate>' . "\n";
									$breakdown[$from_date . '-' . $to_date] = ($breakdown[$from_date . '-' . $to_date] ?? '') . $log_rateplanid . 'Daily rate: ' . (!empty($channel_currency) ? $channel_currency : $currency) . ' ' . round($setcost, 2) . " \n";
								} else {
									// prices per occupancy were defined. We take the price difference to calculate the average extra cost per adult
									$price_diff 	 = $maxcost - $mincost;
									$avg_extra_adult = $price_diff / ($maxocc - $minocc);
									$guests_included = 1;
									foreach ($occupancy as $num_adults) {
										$occ_cost = (isset($pricing[$num_adults]) ? $pricing[$num_adults] : $setcost);
										if ($mincost == $occ_cost) {
											// this number of adults is charged the same as one adult less
											$guests_included = $num_adults;
										}
									}
									if ($guests_included > $minocc && $guests_included < $maxocc) {
										/**
										 * Example: in case 1 adult and 2 adults pay the same, we should re-calculate
										 * the average cost per extra adult by using the guests includes as min occupancy.
										 * @since 	1.8.1
										 */
										$avg_extra_adult = $price_diff / ($maxocc - $guests_included);
									}
									/**
									 * Example:
									 * 2 adults = 150
									 * 3 adults = 190
									 * 4 adults = 230
									 * price diff = 80
									 * daily price = min cost = 150
									 * price per extra person = (max cost - min cost) / (max occupancy - min occupancy)
									 * price per extra person = (230 - 150) / (4 - 2) = 40
									 * 
									 * We transmit three Rate nodes: the daily price will have an attribute usage="1"
									 * the price per extra person will have an attribute usage="2" while the "guests included"
									 * (Pricing Settings: number of guests permitted without any additional fees) will pass the attribute usage="3".
									 */
									$xmlRQ .= "\t\t\t\t" . '<Rate>' . "\n";
									$xmlRQ .= "\t\t\t\t\t" . '<PerDay rate="' . round($mincost, 2) . '" usage="1"/>' . "\n";
									$xmlRQ .= "\t\t\t\t" . '</Rate>' . "\n";
									$xmlRQ .= "\t\t\t\t" . '<Rate>' . "\n";
									$xmlRQ .= "\t\t\t\t\t" . '<PerDay rate="' . round($avg_extra_adult, 2) . '" usage="2"/>' . "\n";
									$xmlRQ .= "\t\t\t\t" . '</Rate>' . "\n";
									$xmlRQ .= "\t\t\t\t" . '<Rate>' . "\n";
									$xmlRQ .= "\t\t\t\t\t" . '<PerDay rate="' . (int)$guests_included . '" usage="3"/>' . "\n";
									$xmlRQ .= "\t\t\t\t" . '</Rate>' . "\n";
									$breakdown[$from_date . '-' . $to_date] = ($breakdown[$from_date . '-' . $to_date] ?? '') . $log_rateplanid . 'Daily rate: ' . (!empty($channel_currency) ? $channel_currency : $currency) . ' ' . round($setcost, 2);
									$breakdown[$from_date . '-' . $to_date] .= ' Price per extra person: ' . (!empty($channel_currency) ? $channel_currency : $currency) . ' ' . round($avg_extra_adult, 2);
									$breakdown[$from_date . '-' . $to_date] .= ' Guests included: ' . $guests_included . " \n";
								}
							}
							
							/**
							 * Google Hotel: OTA BaseByGuestAmt is the OBP model, and requires
							 * the exact cost for any number of guests allowed in the room.
							 * 
							 * @since 	1.8.4
							 * @since 	1.8.12 	added the same support for Vrbo API
							 */
							if (in_array($row['uniquekey'], [VikChannelManagerConfig::GOOGLEHOTEL, VikChannelManagerConfig::GOOGLEVR, VikChannelManagerConfig::VRBOAPI])) {
								$prices_pool = [];
								foreach ($occupancy as $occk => $num_adults) {
									array_push($prices_pool, (isset($pricing[$num_adults]) ? $pricing[$num_adults] : $setcost));
								}
								$avg_cost 	= array_sum($prices_pool) / count($prices_pool);
								$mincost 	= min($prices_pool);
								$maxcost 	= max($prices_pool);
								$minocc 	= min($occupancy);
								$maxocc 	= max($occupancy);
								/**
								 * We do not consider the average daily cost being equal to the nightly cost ($avg_cost == $setcost)
								 * because base cost may be based on 2 adults, and other occupancies may produce the same average value.
								 * For example, -20€ for 1 adult and +20€ for 3 adults. Average value would be the same price, but this
								 * is still an OBP model that needs to be explicitly transmitted to Google.
								 * 
								 * @since 	1.8.20
								 */
								if ($minocc == $maxocc) {
									// no charges or discounts per number of adults, we transmit just the "daily price", but also the max occupants
									$xmlRQ .= "\t\t\t\t" . '<Rate>' . "\n";
									$xmlRQ .= "\t\t\t\t\t" . '<PerDay rate="' . round($setcost, 2) . '" usage="' . $maxocc . '"/>' . "\n";
									$xmlRQ .= "\t\t\t\t" . '</Rate>' . "\n";
									$breakdown[$from_date . '-' . $to_date] = ($breakdown[$from_date . '-' . $to_date] ?? '') . $log_rateplanid . 'Daily rate: ' . (!empty($channel_currency) ? $channel_currency : $currency) . ' ' . round($setcost, 2) . " \n";
								} else {
									// prices per occupancy were defined
									$guests_included = 1;
									foreach ($occupancy as $num_adults) {
										$occ_cost = (isset($pricing[$num_adults]) ? $pricing[$num_adults] : $setcost);
										if ($mincost == $occ_cost) {
											// this number of adults is charged the same as one adult less
											$guests_included = $num_adults;
										}
									}
									if ($guests_included > $minocc && $guests_included < $maxocc) {
										/**
										 * Example: in case 1 adult and 2 adults pay the same, we should re-create the
										 * range of occupancy to skip at least the lowest occupancy with equal prices.
										 */
										$occupancy = range($guests_included, $maxocc);
									}
									// build nodes
									foreach ($occupancy as $occk => $num_adults) {
										$use_cost = (isset($pricing[$num_adults]) ? $pricing[$num_adults] : $setcost);
										$xmlRQ .= "\t\t\t\t" . '<Rate>' . "\n";
										$xmlRQ .= "\t\t\t\t\t" . '<PerDay rate="' . round($use_cost, 2) . '" usage="' . $num_adults . '"/>'."\n";
										$xmlRQ .= "\t\t\t\t" . '</Rate>' . "\n";
										// handle breakdown
										$breakdown[$from_date . '-' . $to_date] = ($breakdown[$from_date . '-' . $to_date] ?? '') . 'OBP x' . $num_adults . ': ' . (!empty($channel_currency) ? $channel_currency : $currency) . ' ' . round($use_cost, 2) . ' ';
									}
									$breakdown[$from_date . '-' . $to_date] = trim($breakdown[$from_date . '-' . $to_date]) . " \n";
								}
							}

							/**
							 * Booking.com OBP Pricing (July 2020). We merge it with the Default Pricing rates information
							 * in order to limit the API errors, and to have a fallback in case OBP is forbidden or disabled.
							 * Only if this rate plan ID was mapped in OBP, otherwise we use just the Default Pricing structure.
							 * 
							 * @since 	1.7.2
							 */
							if ($row['uniquekey'] == VikChannelManagerConfig::BOOKING && isset($bulk_rates_adv_params['bcom_pricing_model_' . $rateplanid]) && $bulk_rates_adv_params['bcom_pricing_model_' . $rateplanid] == 'OBP') {
								// Default Pricing fallback
								$xmlRQ .= "\t\t\t\t".'<Rate>'."\n";
								$xmlRQ .= "\t\t\t\t\t".'<PerDay rate="'.round($setcost, 2).'"/>'."\n";
								$xmlRQ .= "\t\t\t\t".'</Rate>'."\n";
								$breakdown[$from_date . '-' . $to_date] = ($breakdown[$from_date . '-' . $to_date] ?? '') . $log_rateplanid . 'Per Day Rate: ' . (!empty($channel_currency) ? $channel_currency : $currency) . ' ' . round($setcost, 2) . ' ';
								// OBP Pricing with final cost for each adults occupancy of the room
								foreach ($occupancy as $occk => $num_adults) {
									$use_cost = array_key_exists($num_adults, $pricing) ? $pricing[$num_adults] : $setcost;
									$xmlRQ .= "\t\t\t\t".'<Rate>'."\n";
									$xmlRQ .= "\t\t\t\t\t".'<PerOccupancy rate="'.round($use_cost, 2).'" occupancy="'.$num_adults.'"/>'."\n";
									$xmlRQ .= "\t\t\t\t".'</Rate>'."\n";
									$breakdown[$from_date . '-' . $to_date] = ($breakdown[$from_date . '-' . $to_date] ?? '') . 'OBP x' . $num_adults . ': ' . (!empty($channel_currency) ? $channel_currency : $currency) . ' ' . round($use_cost, 2) . ' ';
								}
								$breakdown[$from_date . '-' . $to_date] = trim($breakdown[$from_date . '-' . $to_date]) . " \n";
							}
							//
						} elseif ($row['uniquekey'] == VikChannelManagerConfig::DESPEGAR && (int)$pricing_attr == 19) {
							//Despegar PerRoomPerNight - still requires the room's default Number Of Guests (usage)
							$def_room_occ = 2;
							if (isset($ota_rate_plan['RoomInfo']) && isset($ota_rate_plan['RoomInfo']['StandardOccupancy'])) {
								$def_room_occ = (int)$ota_rate_plan['RoomInfo']['StandardOccupancy'];
							}
							$xmlRQ .= "\t\t\t\t".'<Rate>'."\n";
							$xmlRQ .= "\t\t\t\t\t".'<PerDay rate="'.round($setcost, 2).'" usage="'.$def_room_occ.'"/>'."\n";
							$xmlRQ .= "\t\t\t\t".'</Rate>'."\n";
							$breakdown[$from_date . '-' . $to_date] = ($breakdown[$from_date . '-' . $to_date] ?? '') . $log_rateplanid . 'Rate per night: ' . (!empty($channel_currency) ? $channel_currency : $currency) . ' ' . round($setcost, 2) . " \n";
						} elseif ($row['uniquekey'] == VikChannelManagerConfig::OTELZ && count($occupancy) <= 1) {
							//Otelz.com - Room Price
							$xmlRQ .= "\t\t\t\t".'<Rate>'."\n";
							$xmlRQ .= "\t\t\t\t\t".'<PerDay rate="'.round($setcost, 2).'"/>'."\n";
							$xmlRQ .= "\t\t\t\t".'</Rate>'."\n";
							$breakdown[$from_date . '-' . $to_date] = ($breakdown[$from_date . '-' . $to_date] ?? '') . $log_rateplanid . 'Rate per night: ' . (!empty($channel_currency) ? $channel_currency : $currency) . ' ' . round($setcost, 2) . " \n";
						} elseif ($row['uniquekey'] == VikChannelManagerConfig::HOSTELWORLD) {
							/**
							 * Hostelworld - for Private Rooms they multiply the price we pass by the number of beds that the room has.
							 * For dormitory/shared rooms instead, they keep the price we transmit with the Channel Manager.
							 * Therefore, we need to divide the price obtained by the number of beds that this rate plan has. We do this
							 * by checking the rateplan mapping information and the property "beds", as well as the channel parameter for
							 * dividing the price by number of beds.
							 * 
							 * @since 	1.7.1
							 */
							$hw_divide_cost_prrooms = 0;
							$bkextra = '';
							if (is_array($ota_rate_plan) && isset($ota_rate_plan['RatePlan']) && isset($ota_rate_plan['RatePlan'][$rateplanid])) {
								if (isset($ota_rate_plan['RatePlan'][$rateplanid]['beds']) && isset($ota_rate_plan['RatePlan'][$rateplanid]['basictype'])) {
									if (stripos($ota_rate_plan['RatePlan'][$rateplanid]['basictype'], 'private') !== false && stripos($ota_rate_plan['RatePlan'][$rateplanid]['basictype'], 'dorm') === false) {
										// it's a private room, not a dormitory - check the channel settings
										if (is_array($channel_settings) && isset($channel_settings['hw_divide_cost_prrooms']) && stripos($channel_settings['hw_divide_cost_prrooms']['value'], 'no') === false) {
											$hw_divide_cost_prrooms = (int)$ota_rate_plan['RatePlan'][$rateplanid]['beds'];
										}
									}
								}
							}
							if ($hw_divide_cost_prrooms > 1) {
								$bkextra = " ({$setcost} divided by {$hw_divide_cost_prrooms} beds)";
								$setcost /= $hw_divide_cost_prrooms;
							}
							//
							$xmlRQ .= "\t\t\t\t".'<Rate>'."\n";
							$xmlRQ .= "\t\t\t\t\t".'<PerDay rate="'.round($setcost, 2).'"/>'."\n";
							$xmlRQ .= "\t\t\t\t".'</Rate>'."\n";
							$breakdown[$from_date . '-' . $to_date] = ($breakdown[$from_date . '-' . $to_date] ?? '') . $log_rateplanid . "Per Day Rate{$bkextra}: " . (!empty($channel_currency) ? $channel_currency : $currency) . ' ' . round($setcost, 2) . " \n";
						} else {
							// Expedia PerDayPricing, Booking.com DefaultPricing or any other channel without particular needs
							$xmlRQ .= "\t\t\t\t".'<Rate>'."\n";
							$xmlRQ .= "\t\t\t\t\t".'<PerDay rate="'.round($setcost, 2).'"/>'."\n";
							$xmlRQ .= "\t\t\t\t".'</Rate>'."\n";
							$breakdown[$from_date . '-' . $to_date] = ($breakdown[$from_date . '-' . $to_date] ?? '') . $log_rateplanid . 'Per Day Rate: ' . (!empty($channel_currency) ? $channel_currency : $currency) . ' ' . round($setcost, 2) . " \n";
						}
					}

					//Booking.com Derived prices for occupancy rules
					if ($row['uniquekey'] == VikChannelManagerConfig::BOOKING && array_key_exists('bcom_derocc', $bulk_rates_adv_params) && intval($bulk_rates_adv_params['bcom_derocc']) > 0) {
						$leading_occupancy = 0;
						$der_prices_occ = [];
						foreach ($occupancy as $occk => $num_adults) {
							$diffusageprice = VikBooking::loadAdultsDiff($idroom, (int)$num_adults);
							/**
							 * Occupancy pricing rules may be altered by the same rule as for the base room costs.
							 * This is to increase the rooms costs for the channels as well as the occupancy pricing rules
							 * defined for the website. This applies only to charges/discounts with absolute values, not percent.
							 * 
							 * @since 	1.6.17
							 */
							if ($alter_rates === true && isset($bulk_rates_adv_params['alter_occrules']) && (int)$bulk_rates_adv_params['alter_occrules']) {
								$diffusageprice = VikChannelManager::alterRoomOccupancyPricingRules($diffusageprice, $rmodop, $rmodval, $rmodamount);
							}
							if (is_array($diffusageprice)) {
								if ($diffusageprice['pernight'] != 1) {
									//rates modifications per occupancy are allowed only per-night, not in total
									continue;
								}
								if ($diffusageprice['chdisc'] == 1) {
									//charge
									if ($diffusageprice['valpcent'] == 1) {
										//fixed value
										$der_prices_occ[(int)$num_adults] = ['type' => 'additional', 'val' => (float)$diffusageprice['value']];
									} else {
										//percentage value
										$der_prices_occ[(int)$num_adults] = ['type' => 'percentage', 'val' => (float)$diffusageprice['value']];
									}
								} else {
									//discount
									if ($diffusageprice['valpcent'] == 1) {
										//fixed value
										$der_prices_occ[(int)$num_adults] = ['type' => 'additional', 'val' => ((float)$diffusageprice['value'] * -1)];
									} else {
										//percentage value
										$der_prices_occ[(int)$num_adults] = ['type' => 'percentage', 'val' => ((float)$diffusageprice['value'] * -1)];
									}
								}
							} else {
								if (!($leading_occupancy > 0)) {
									$leading_occupancy = (int)$num_adults;
								}
							}
						}
						if (!($leading_occupancy > 0)) {
							$leading_occupancy = (int)$occupancy[0];
						}
						if ($der_prices_occ && $leading_occupancy > 0 && !array_key_exists($row['idroomota'].$rateplanid, $derived_pr_cache)) {
							//Build XML Node and update memory array
							$xmlRQ .= "\t\t\t\t".'<OccupancyRules leading_occupancy="'.$leading_occupancy.'">'."\n";
							foreach ($der_prices_occ as $num_adults => $occ_rule) {
								$xmlRQ .= "\t\t\t\t\t".'<OccupancyRule persons="'.$num_adults.'" '.$occ_rule['type'].'="'.$occ_rule['val'].'"/>'."\n";
							}
							$xmlRQ .= "\t\t\t\t".'</OccupancyRules>'."\n";
							$derived_pr_cache[$row['idroomota'].$rateplanid] = count($der_prices_occ);
						}
					}
					//end Booking.com Derived prices for occupancy rules

					//Despegar price for additional Adults (triggered only if the Booking.com Derived prices for occupancy are sent)
					if ($row['uniquekey'] == VikChannelManagerConfig::DESPEGAR && array_key_exists('bcom_derocc', $bulk_rates_adv_params) && intval($bulk_rates_adv_params['bcom_derocc']) > 0) {
						$der_prices_occ = [];
						foreach ($occupancy as $occk => $num_adults) {
							$diffusageprice = VikBooking::loadAdultsDiff($idroom, (int)$num_adults);
							/**
							 * Occupancy pricing rules may be altered by the same rule as for the base room costs.
							 * This is to increase the rooms costs for the channels as well as the occupancy pricing rules
							 * defined for the website. This applies only to charges/discounts with absolute values, not percent.
							 * 
							 * @since 	1.6.17
							 */
							if ($alter_rates === true && isset($bulk_rates_adv_params['alter_occrules']) && (int)$bulk_rates_adv_params['alter_occrules']) {
								$diffusageprice = VikChannelManager::alterRoomOccupancyPricingRules($diffusageprice, $rmodop, $rmodval, $rmodamount);
							}
							if (is_array($diffusageprice)) {
								if ($diffusageprice['pernight'] == 1 && $diffusageprice['chdisc'] == 1 && $diffusageprice['valpcent'] == 1) {
									//Must be a charge, per night (not in total) and an absolute value (not a percentage value)
									$der_prices_occ[(int)$num_adults] = (float)$diffusageprice['value'];
								}
							}
						}
						if ($der_prices_occ && !array_key_exists($row['idroomota'].$rateplanid, $derived_pr_cache)) {
							//we only take the lowest additional cost per extra person, assuming that this will be the cost for any additional guest
							$min_occupancy_rule = min(array_keys($der_prices_occ));
							$min_occupancy_charge = min($der_prices_occ);
							//Examples
							/*
							 * 		Base rate 	100 	+0
							 * 		2 Adults 	120 	+20
							 * 		3 Adults 	140 	+40
							 * 		4 Adults 	180 	+80
							 * 		---- we take +20 ----
							 * 		---- 	---- 	----
							 * 		1 Adult 	100 	-20
							 * 		Base rate 	120 	+0
							 * 		3 Adults 	140 	+40
							 * 		4 Adults 	180 	+80
							 * 		---- we take +40 ----
							*/
							//Build XML Node and update memory array
							$xmlRQ .= "\t\t\t\t".'<OccupancyRules leading_occupancy="'.$min_occupancy_rule.'">'."\n";
							$xmlRQ .= "\t\t\t\t\t".'<OccupancyRule persons="'.$min_occupancy_rule.'" additional="'.$min_occupancy_charge.'"/>'."\n";
							$xmlRQ .= "\t\t\t\t".'</OccupancyRules>'."\n";
							$derived_pr_cache[$row['idroomota'].$rateplanid] = count($der_prices_occ);
						}
					}
					//end Despegar price for additional Adults

					// Restrictions
					if (strlen($minlos) && strlen($maxlos)) {
						$set_cta = 'false';
						$set_ctd = 'false';

						// CTA is written before CTD in Min LOS so explode and re-attach the left part if CTD exists too
						$cta_wdays = []; // not transmitted for the moment
						$ctd_wdays = []; // not transmitted for the moment
						if (strpos($minlos, 'CTA') !== false) {
							$minlos_parts = explode('CTA[', $minlos);
							$minlos_parts_left = explode(']', $minlos_parts[1]);
							$cta_wdays = explode(',', $minlos_parts_left[0]);
							$minlos = $minlos_parts[0].(array_key_exists(1, $minlos_parts_left) ? $minlos_parts_left[1] : '');
						}
						if (strpos($minlos, 'CTD') !== false) {
							$minlos_parts = explode('CTD[', $minlos);
							$ctd_wdays = explode(',', str_replace(']', '', $minlos_parts[1]));
							$minlos = $minlos_parts[0];
						}
						if ($cta_wdays) {
							$set_cta = 'true';
						}
						if ($ctd_wdays) {
							$set_ctd = 'true';
						}

						// adjust min/max LOS
						$r_minlos = intval($minlos) > 1 ? intval($minlos) : 0;
						$r_maxlos = intval($maxlos);

						/**
						 * Calculate and apply the effective minimum and maximum stay for this room and rate plan.
						 * This will add support to room-rate level restrictions for "Weekly" or "Monthly" rate plans.
						 * 
						 * @since 	1.9.10
						 * @since 	1.9.12  added support to advanced option to ignore rates-table restrictions.
						 * @since 	1.9.16  added support to ignore rates-table restrictions for other channels.
						 */
						$effective_min_los = 0;
						$effective_max_los = 0;
						$ignore_effective_min_max_los = false;
						if ($row['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI && !empty($bulk_rates_adv_params['airbnb_no_ratestable_restr'])) {
							$ignore_effective_min_max_los = true;
						} elseif ($row['uniquekey'] == VikChannelManagerConfig::BOOKING && !empty($bulk_rates_adv_params['bdc_no_ratestable_restr'])) {
							$ignore_effective_min_max_los = true;
						} elseif ($row['uniquekey'] == VikChannelManagerConfig::EXPEDIA && !empty($bulk_rates_adv_params['expedia_no_ratestable_restr'])) {
							$ignore_effective_min_max_los = true;
						}
						if (!$ignore_effective_min_max_los) {
							// calculate the effective min/max LOS for the current room rate plan
							if (class_exists('VBORoomHelper') && method_exists('VBORoomHelper', 'calcEffectiveMinLOS')) {
								$effective_min_los = VBORoomHelper::calcEffectiveMinLOS($idroom, $idprice);
								$effective_max_los = VBORoomHelper::calcEffectiveMaxLOS($idroom, $idprice);
							}
						} elseif ($ignore_effective_min_max_los && $r_minlos > 1 && $r_minlos < 7 && $r_minlos == $r_maxlos) {
							// we are dealing with a regular rate plan that is probably receiving a forced exact stay due to
							// multiple rate plans configured, like one-night rate, two-night rate.. and the min stay is modified
							// at room level directly on a parent rate plan (like standard rate), so we calculate the effective
							// maximum stay per room in order to avoid applying an exact stay of like 2 or 3 nights
							if (class_exists('VBORoomHelper') && method_exists('VBORoomHelper', 'calcHighestMaxLOS')) {
								$highest_maxlos = VBORoomHelper::calcHighestMaxLOS($idroom, $needs_multi_rate = true);
								$r_maxlos = $highest_maxlos ?: $r_maxlos;
								$r_maxlos = $r_maxlos >= $r_minlos ? $r_maxlos : $r_minlos;
							}
						}

						// if we have a weekly rate plan, the minimum stay should always be forced regardless of room-level restrictions
						if ($effective_min_los > 1 && $r_minlos < $effective_min_los) {
							$r_minlos = $effective_min_los;
						}

						// if no Max LOS defined for these dates, make sure to apply it from the Rates Table
						$r_maxlos = $r_maxlos === 0 ? $effective_max_los : $r_maxlos;

						// if we have a one-night rate plan, the maximum stay should always be 1 regardless of room-level restrictions
						if ($effective_max_los === 1) {
							$r_maxlos = $effective_max_los;
						}

						/**
						 * Ensure the minimum stay is less than or equal to the maximum stay.
						 * Take also care of the one-night rate with maximum stay equal to 1.
						 * 
						 * @since 	1.9.10
						 * @since 	1.9.16 prevented fixed number of nights rate plans to prevail on higher room-level restrictions.
						 */
						if ($effective_min_los > 0 && $effective_min_los === $effective_max_los && $r_minlos > $effective_min_los) {
							// one-night, two-night, three-night etc.. rate plans should follow the higher room-level min LOS
							$r_minlos = $r_minlos;
							$r_maxlos = $r_minlos;
						}
						if ($r_maxlos > 0 && $r_minlos > $r_maxlos) {
							$r_minlos = $r_maxlos;
						}
						if ($r_maxlos === 1 && $r_minlos !== 1) {
							// one-night rates should always set a minimum stay of 1 night even if it was unset (0)
							$r_minlos = 1;
						}

						$lim_min_los = 28;
						$lim_down_min_los = in_array($row['uniquekey'], [VikChannelManagerConfig::BOOKING, VikChannelManagerConfig::DESPEGAR, VikChannelManagerConfig::GOOGLEHOTEL, VikChannelManagerConfig::GOOGLEVR]) ? 0 : 1;
						$lim_max_los = $row['uniquekey'] == VikChannelManagerConfig::AGODA ? 99 : ($row['uniquekey'] == VikChannelManagerConfig::BOOKING ? 31 : 28);
						$lim_max_los = $row['uniquekey'] == VikChannelManagerConfig::FERATEL ? 999 : $lim_max_los;
						$lim_max_los = $row['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI ? 365 : $lim_max_los;
						$lim_max_los = $row['uniquekey'] == VikChannelManagerConfig::GOOGLEHOTEL || $row['uniquekey'] == VikChannelManagerConfig::GOOGLEVR ? 60 : $lim_max_los;
						$lim_max_los = $row['uniquekey'] == VikChannelManagerConfig::VRBOAPI ? 180 : $lim_max_los;
						$lim_down_max_los = $row['uniquekey'] == VikChannelManagerConfig::AGODA || $row['uniquekey'] == VikChannelManagerConfig::BOOKING ? 0 : 0;
						$r_minlos = $r_minlos < $lim_down_min_los ? $lim_down_min_los : ($r_minlos > $lim_min_los ? $lim_min_los : $r_minlos);
						$r_maxlos = $r_maxlos < $lim_down_max_los ? $lim_down_max_los : ($r_maxlos > $lim_max_los ? $lim_max_los : $r_maxlos);
						$r_maxlos = $row['uniquekey'] == VikChannelManagerConfig::EXPEDIA && !($r_maxlos > 0) ? 28 : $r_maxlos;
						$r_maxlos = $row['uniquekey'] == VikChannelManagerConfig::BOOKING && $r_maxlos > 28 ? 0 : $r_maxlos;

						// set restrictions node
						$xmlRQ .= "\t\t\t\t".'<Restrictions minLOS="'.$r_minlos.'" maxLOS="'.$r_maxlos.'" closedToArrival="'.$set_cta.'" closedToDeparture="'.$set_ctd.'"/>'."\n";

						// build and set restrictions data in rates flow record
						$restr_data = [
							'minLOS' => $r_minlos,
							'maxLOS' => $r_maxlos,
							'cta' 	 => $set_cta,
							'ctd' 	 => $set_ctd,
						];
						$rflow_record->setRestrictions($restr_data);
					}

					// finalize RarUpdate node
					$xmlRQ .= "\t\t\t".'</RatePlan>'."\n";
					$xmlRQ .= "\t\t".'</RoomType>'."\n";
					$xmlRQ .= "\t".'</RarUpdate>'."\n";

					// push rates flow record
					$rflow_handler->pushRecord($rflow_record);

					// enhance debug string
					$debug_str .= "\n\n";
				}

				if (empty($this->caller) && $row['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI) {
					/**
					 * Prices for Airbnb are always exclusive of taxes, because taxes and fees
					 * should be transmitted through the Occupancy Taxes (Pricing Settings), if any.
					 * However, the listing should be eligible for "pass through taxes".
					 * 
					 * Here we also deliver to e4jConnect the information about the Standard Fees,
					 * the Security Deposit as well as the Cleaning Fee if defined in VBO or through
					 * the custom settings of the channel Airbnb API.
					 * 
					 * @see 	we only transmit this information along with the Bulk Action Rates Upload.
					 * 
					 * @since 	1.8.0
					 * @since 	1.8.1 prices are transmitted inclusive of taxes if not tax eligible like for most EU clients.
					 */
					$tax_eligible = false;	
					if (array_key_exists('RatePlan', $ota_rate_plan) && array_key_exists($rateplanid, $ota_rate_plan['RatePlan'])) {
						// mapping information has got details about the rate plan
						if (isset($ota_rate_plan['RatePlan'][$rateplanid]['eligible_for_taxes']) && $ota_rate_plan['RatePlan'][$rateplanid]['eligible_for_taxes'] > 0) {
							// during the mapping of this listing, we have detected from the Pricing Settings that pass through taxes are supported
							$tax_eligible = true;
						}
					}

					if ($tax_eligible) {
						// pass through taxes are supported
						
						// business_tax_id and tot_registration_id are mandatory settings requested for this channel
						$business_tax_id = '';
						if (is_array($channel_settings) && isset($channel_settings['business_tax_id']) && !empty($channel_settings['business_tax_id']['value'])) {
							$business_tax_id = $channel_settings['business_tax_id']['value'];
						}
						$registration_id = '';
						if (is_array($channel_settings) && isset($channel_settings['registration_id']) && !empty($channel_settings['registration_id']['value'])) {
							$registration_id = $channel_settings['registration_id']['value'];
						}

						// check if VAT/GST has been configured
						if ($tax_rates && isset($tax_rates[$idprice])) {
							// VAT/GST is supported as one tax rate is assigned to the selected website rate plan
							$pass_through_tax = new stdClass;
							$pass_through_tax->tax_type = 'pass_through_vat_gst';
							$pass_through_tax->amount = $tax_rates[$idprice];
							$pass_through_tax->amount_type = 'percent_per_reservation';
							$pass_through_tax->business_tax_id = $business_tax_id;
							$pass_through_tax->tot_registration_id = $registration_id;
							$pass_through_tax->attestation = true;

							// add tax to XML node
							$xmlRQ .= "\t" . '<RarPricing type="pass_through_tax" idroomota="' . $row['idroomota'] . '">' . "\n";
							$xmlRQ .= "\t\t" . '<Pricing><![CDATA[' . json_encode($pass_through_tax) . ']]></Pricing>' . "\n";
							$xmlRQ .= "\t" . '</RarPricing>' . "\n";
						}
						
						// check if an option of type tourist tax is assigned to this room
						$room_tourist_tax = VikChannelManager::getRoomTouristTax($idroom);
						if (is_array($room_tourist_tax) && $room_tourist_tax && $room_tourist_tax['cost'] > 0) {
							// tourist tax is supported

							// detect type of amount
							$amount_type = 'flat_per_guest';
							if (!empty($room_tourist_tax['pcentroom']) && (int)$room_tourist_tax['pcentroom'] > 0) {
								// this should be pretty rare, but some countries and cities may need the tourist tax to be percent
								$amount_type = 'percent_per_reservation';
							} elseif ($room_tourist_tax['perday'] && $room_tourist_tax['perperson']) {
								$amount_type = 'flat_per_guest_per_night';
							} elseif ($room_tourist_tax['perday'] && !$room_tourist_tax['perperson']) {
								$amount_type = 'flat_per_night';
							}

							// detect maximum nights exemption
							$max_nights_exemption = 0;
							if ($room_tourist_tax['maxprice'] > 0 && $room_tourist_tax['maxprice'] > $room_tourist_tax['cost']) {
								$max_nights_exemption = floor($room_tourist_tax['maxprice'] / $room_tourist_tax['cost']);
							}

							$pass_through_tax = new stdClass;
							$pass_through_tax->tax_type = 'pass_through_tourist_tax';
							$pass_through_tax->amount = $room_tourist_tax['cost'];
							$pass_through_tax->amount_type = $amount_type;
							$pass_through_tax->business_tax_id = $business_tax_id;
							$pass_through_tax->tot_registration_id = $registration_id;
							$pass_through_tax->attestation = true;
							// long term stay exemption in the docs indicates that if provided, tax will be skipped completely, but we pass the value anyway
							$pass_through_tax->long_term_stay_exemption = $max_nights_exemption;

							// add tax to XML node
							$xmlRQ .= "\t" . '<RarPricing type="pass_through_tax" idroomota="' . $row['idroomota'] . '">' . "\n";
							$xmlRQ .= "\t\t" . '<Pricing><![CDATA[' . json_encode($pass_through_tax) . ']]></Pricing>' . "\n";
							$xmlRQ .= "\t" . '</RarPricing>' . "\n";
						}

						/**
						 * Listings eligible for taxes may use a "climate resilience" fee (eco-fee, environmental fee), which should not
						 * be transmitted to Airbnb as a standard fee of type "Community Fee", or VAT/GST will be calculated over it.
						 * Those listings uneligible for taxes can still use the standard fee "Community Fee", but when the listing is
						 * eligible for taxes, the CM will automatically push to Airbnb an Occupancy Tax of type "Tourism Assessment/Fee"
						 * which is not affected by VAT/GST, and it can represents the Greek eco/environmental fee as it should.
						 * 
						 * @since 	1.9.16
						 */
						$listingEnvFeeData = ($tax_rates[$idprice] ?? null) ? VikChannelManager::getRoomEnvironmentalFee((int) $idroom) : [];
						if ($listingEnvFeeData) {
							// eco/environmental fee is supported

							// detect type of amount (percent not supported for eco/environmental fee)
							$amount_type = 'flat_per_guest';
							if ($listingEnvFeeData['perday'] && $listingEnvFeeData['perperson']) {
								$amount_type = 'flat_per_guest_per_night';
							} elseif ($listingEnvFeeData['perday'] && !$listingEnvFeeData['perperson']) {
								$amount_type = 'flat_per_night';
							}

							// detect maximum nights exemption
							$max_nights_exemption = 0;
							if ($listingEnvFeeData['maxprice'] > 0 && $listingEnvFeeData['maxprice'] > $listingEnvFeeData['cost']) {
								$max_nights_exemption = floor($listingEnvFeeData['maxprice'] / $listingEnvFeeData['cost']);
							}

							// build occupancy tax details for Airbnb
							$eco_fee_occupancy_tax = [
								'tax_type' => 'pass_through_tourism_assessment_fee',
								'amount' => (float) $listingEnvFeeData['cost'],
								'amount_type' => $amount_type,
								'business_tax_id' => $business_tax_id,
								'tot_registration_id' => $registration_id,
								'attestation' => true,
								'long_term_stay_exemption' => $max_nights_exemption,
							];

							// add tax to XML node
							$xmlRQ .= "\t" . '<RarPricing type="pass_through_tax" idroomota="' . $row['idroomota'] . '">' . "\n";
							$xmlRQ .= "\t\t" . '<Pricing><![CDATA[' . json_encode($eco_fee_occupancy_tax) . ']]></Pricing>' . "\n";
							$xmlRQ .= "\t" . '</RarPricing>' . "\n";
						}
					}
					
					// load channel settings to see if security deposit, cleaning fee or standard fees were created for this room (listing)
					$custom_ch_settings = VikChannelManager::getConfigurationRecord('custom_ch_settings_' . $row['uniquekey']);
					if (!empty($custom_ch_settings)) {
						$custom_ch_settings = json_decode($custom_ch_settings);
					}

					// security deposit
					if (is_object($custom_ch_settings) && isset($custom_ch_settings->secdep) && (is_array($custom_ch_settings->secdep) || is_object($custom_ch_settings->secdep))) {
						// find a security deposit rule for this listing (this can be an array of rules or an object in case of associative array with non-consequent keys)
						foreach ($custom_ch_settings->secdep as $secdep) {
							if (!is_object($secdep) || !isset($secdep->amount) || !isset($secdep->listings) || !is_array($secdep->listings)) {
								continue;
							}
							if (in_array($row['idroomota'], $secdep->listings)) {
								// security deposit rule found for this listing
								$price_setting = new stdClass;
								$price_setting->amount = $secdep->amount;

								// add it to XML node
								$xmlRQ .= "\t" . '<RarPricing type="security_deposit" idroomota="' . $row['idroomota'] . '">' . "\n";
								$xmlRQ .= "\t\t" . '<Pricing><![CDATA[' . json_encode($price_setting) . ']]></Pricing>' . "\n";
								$xmlRQ .= "\t" . '</RarPricing>' . "\n";

								// break the loop as we can only transmit one security deposit per listing
								break;
							}
						}
					}

					// cleaning fee
					if (is_object($custom_ch_settings) && isset($custom_ch_settings->cleanfee) && (is_array($custom_ch_settings->cleanfee) || is_object($custom_ch_settings->cleanfee))) {
						// find a cleaning fee rule for this listing (this can be an array of rules or an object in case of associative array with non-consequent keys)
						foreach ($custom_ch_settings->cleanfee as $cleanfee) {
							if (!is_object($cleanfee) || !isset($cleanfee->amount) || !isset($cleanfee->listings) || !is_array($cleanfee->listings)) {
								continue;
							}
							if (in_array($row['idroomota'], $cleanfee->listings)) {
								// cleaning fee rule found for this listing
								$price_setting = new stdClass;
								$price_setting->amount = $cleanfee->amount;

								// add it to XML node
								$xmlRQ .= "\t" . '<RarPricing type="cleaning_fee" idroomota="' . $row['idroomota'] . '">' . "\n";
								$xmlRQ .= "\t\t" . '<Pricing><![CDATA[' . json_encode($price_setting) . ']]></Pricing>' . "\n";
								$xmlRQ .= "\t" . '</RarPricing>' . "\n";

								// break the loop as we can only transmit one cleaning fee per listing
								break;
							}
						}
					}

					// standard fees (multiple fees per listing supported)
					if (is_object($custom_ch_settings) && isset($custom_ch_settings->stdfee) && (is_array($custom_ch_settings->stdfee) || is_object($custom_ch_settings->stdfee))) {
						// find all standard fee rules for this listing (this can be an array of rules or an object in case of associative array with non-consequent keys)
						foreach ($custom_ch_settings->stdfee as $stdfee) {
							if (!is_object($stdfee) || empty($stdfee->fee_type) || !isset($stdfee->amount) || !isset($stdfee->listings) || !is_array($stdfee->listings)) {
								continue;
							}
							if (in_array($row['idroomota'], $stdfee->listings)) {
								// standard fee rule found for this listing
								$standard_fee = new stdClass;
								foreach ($stdfee as $prop => $val) {
									// pass any property except for "listings"
									if ($prop == 'listings') {
										continue;
									}
									$standard_fee->{$prop} = $val;
								}

								// add it to XML node
								$xmlRQ .= "\t" . '<RarPricing type="standard_fee" idroomota="' . $row['idroomota'] . '">' . "\n";
								$xmlRQ .= "\t\t" . '<Pricing><![CDATA[' . json_encode($standard_fee) . ']]></Pricing>' . "\n";
								$xmlRQ .= "\t" . '</RarPricing>' . "\n";

								// DO NOT break the loop as we can transmit multiple standard fees per listing
							}
						}
					}
				}
				//

				if (empty($this->caller) && in_array($row['uniquekey'], [VikChannelManagerConfig::BOOKING, VikChannelManagerConfig::GOOGLEHOTEL, VikChannelManagerConfig::GOOGLEVR, VikChannelManagerConfig::VRBOAPI])) {
					/**
					 * We check if costs per children have been defined in Vik Booking for this room so
					 * that we pass along this information that will be transmitted to Booking.com and Google
					 * by using the relevant API. Age intervals fees, max guests, max adults and max children.
					 * 
					 * @see 	we only transmit this information along with the Bulk Action Rates Upload.
					 * 
					 * @since 	1.8.1
					 * @since 	1.8.7 	we pass the child rates also for Google Hotel.
					 * @since 	1.8.11 	we support the new Booking.com "Flexible Children Rates" (late 2022/early 2023)
					 * 					such rates can be defined at room-rate-level as well as room-rate-date-level.
					 */

					// set from and to dates, if the channel supports this filter
					$child_fees_fdate = $row['uniquekey'] == VikChannelManagerConfig::BOOKING ? $very_min_max_dates['min_date'] : null;
					$child_fees_tdate = $row['uniquekey'] == VikChannelManagerConfig::BOOKING ? $very_min_max_dates['max_date'] : null;

					// load children fees from VBO
					$room_child_fees = VikChannelManager::getRoomChildrenFees($idroom, $child_fees_fdate, $child_fees_tdate, $bulk_rates_adv_params);

					/**
					 * Allow third party plugins to manipulate the children rates being transmitted to the channel. This
					 * is useful for example if someone needs to prevent the children rates from being sent to Booking.com.
					 * 
					 * @since 	1.8.19
					 */
					VCMFactory::getPlatform()->getDispatcher()->trigger('onBeforeTransmitChildrenRates', [$row['uniquekey'], $idroom, &$room_child_fees, $room_channel_alterations]);

					// check if any updates are necessary
					if ($room_child_fees !== false) {
						// fees per child age have been defined
						list($max_guests, $max_adults, $max_children, $child_intvals) = $room_child_fees;
						if (is_array($child_intvals) && $child_intvals) {
							// add child rates ("general" room-rate-level) information to XML node
							$xmlRQ .= "\t" . '<RarPricing type="child_rates" idroomota="' . $row['idroomota'] . '">' . "\n";
							$xmlRQ .= "\t\t" . '<Pricing><![CDATA[' . json_encode($child_intvals) . ']]></Pricing>' . "\n";
							$xmlRQ .= "\t" . '</RarPricing>' . "\n";

							// check for an additional format of children fees defined at date-level
							if (isset($room_child_fees[4]) && is_array($room_child_fees[4])) {
								// children age buckets defined at room-rate-date-level
								foreach ($room_child_fees[4] as $date_age_buckets) {
									if (!is_array($date_age_buckets) || empty($date_age_buckets['from_date']) || empty($date_age_buckets['buckets'])) {
										// invalid structure
										continue;
									}
									// add child rates at date-level information to XML node
									$xmlRQ .= "\t" . '<RarPricing type="child_rates_date" idroomota="' . $row['idroomota'] . '" from="' . $date_age_buckets['from_date'] . '" to="' . $date_age_buckets['to_date'] . '">' . "\n";
									$xmlRQ .= "\t\t" . '<Pricing><![CDATA[' . json_encode($date_age_buckets['buckets']) . ']]></Pricing>' . "\n";
									$xmlRQ .= "\t" . '</RarPricing>' . "\n";
								}
							}

							// add information about max occupancies to XML node
							$xmlRQ .= "\t" . '<RarPricing type="max_guests" idroomota="' . $row['idroomota'] . '">' . "\n";
							$xmlRQ .= "\t\t" . '<Pricing>' . $max_guests . '</Pricing>' . "\n";
							$xmlRQ .= "\t" . '</RarPricing>' . "\n";
							$xmlRQ .= "\t" . '<RarPricing type="max_adults" idroomota="' . $row['idroomota'] . '">' . "\n";
							$xmlRQ .= "\t\t" . '<Pricing>' . $max_adults . '</Pricing>' . "\n";
							$xmlRQ .= "\t" . '</RarPricing>' . "\n";
							$xmlRQ .= "\t" . '<RarPricing type="max_children" idroomota="' . $row['idroomota'] . '">' . "\n";
							$xmlRQ .= "\t\t" . '<Pricing>' . $max_children . '</Pricing>' . "\n";
							$xmlRQ .= "\t" . '</RarPricing>' . "\n";
						}
					}

					/**
					 * Booking.com/Google Hotel minimum and maximum advance booking days (time)
					 * 
					 * @since 	1.8.3
					 */
					if ((isset($bulk_rates_adv_params['min_max_adv_res']) && (int)$bulk_rates_adv_params['min_max_adv_res']) || $row['uniquekey'] == VikChannelManagerConfig::GOOGLEHOTEL || $row['uniquekey'] == VikChannelManagerConfig::GOOGLEVR) {
						$min_adv_hours  = null;
						$max_adv_time 	= null;

						// grab the VBO global configuration setting for the maximum advance booking period
						$max_adv_time = VikBooking::getMaxDateFuture($idroom);
						$max_adv_time = empty($max_adv_time) ? '+2y' : $max_adv_time;
						$max_numlim   = (int)substr($max_adv_time, 1, (strlen($max_adv_time) - 2));
						$max_quantlim = strtoupper(substr($max_adv_time, -1, 1));

						// grab from VBO the minimum advance booking time for this rate plan (we ignore the global setting "mindaysadvance")
						$ibe_rplan_info = VikBooking::getPriceInfo($idprice);
						if (is_array($ibe_rplan_info) && $ibe_rplan_info && isset($ibe_rplan_info['minhadv'])) {
							// set the minimum hours in advance for this rate plan (could be also 0)
							$min_adv_hours = (int) $ibe_rplan_info['minhadv'];
						}

						// check minimum requirements
						if (!$min_adv_hours && ($row['uniquekey'] == VikChannelManagerConfig::GOOGLEHOTEL || $row['uniquekey'] == VikChannelManagerConfig::GOOGLEVR)) {
							if (!VikBooking::todayBookings()) {
								/**
								 * In order to reduce price accuracy mismatch errors with Google Hotel, we always
								 * set a static minimum hours in advance value to 12 hours in case nothing was set.
								 * 
								 * @since 	1.8.20
								 */
								$min_adv_hours = 12;
							}
						}

						/**
						 * Minimum advance booking notice can be defined at listing-level.
						 * 
						 * @requires 	VikBooking >= 1.18.3 (J) - 1.8.3 (WP)
						 * 
						 * @since 		1.9.13
						 */
						if (method_exists('VikBooking', 'getMinDateFutureListing')) {
							$listing_min_adv_notice = VikBooking::getMinDateFutureListing($idroom);
							if (!is_null($listing_min_adv_notice)) {
								// set the minimum hours in advance for this listing
								$min_adv_hours = (int) $listing_min_adv_notice;
							}
						}

						// make sure the value for minimum hours in advance is set (could also be 0 hours)
						if ($min_adv_hours !== null && $min_adv_hours >= 0) {
							// format minimum hours
							if ($min_adv_hours == 24) {
								// format as one day in advance
								$min_adv_time_str = '1D';
							} elseif ($min_adv_hours < 24) {
								// format as N hours in advance
								$min_adv_time_str = $min_adv_hours . 'H';
							} else {
								// must be more than one day
								$min_adv_days 	  = floor($min_adv_hours / 24);
								$min_adv_hours 	  = $min_adv_hours - ($min_adv_days * 24);
								$min_adv_time_str = $min_adv_days . 'D' . ($min_adv_hours > 0 ? ($min_adv_hours . 'H') : '');
							}

							// build object with final restriction details
							$ota_min_max_details = new stdClass;
							$ota_min_max_details->min_advance_res = $min_adv_time_str;
							$ota_min_max_details->max_advance_res = $max_numlim . $max_quantlim;

							/**
							 * In order to comply with the Google Hotel ISO 8601 Duration format, we need
							 * to pass also the information about the check-in and check-out times, as for
							 * Google the hours are relative, meaning that it's the offset from midnight.
							 * 
							 * @since 	1.8.7
							 * @since 	1.9.13 added support to listing-level check-in/check-out times.
							 */
							$timeopst = VikBooking::getTimeOpenStore();
							if (is_array($timeopst)) {
								$opent = VikBooking::getHoursMinutes($timeopst[0]);
								$closet = VikBooking::getHoursMinutes($timeopst[1]);
								$checkin_h = $opent[0];
								$checkin_m = $opent[1];
								$checkout_h = $closet[0];
								$checkout_m = $closet[1];

								// listing-level check-in/check-out times
								if ($room_details['params'] ?? null) {
									$listing_checkin = VikBooking::getRoomParam('checkin', $room_details['params']);
									$listing_checkout = VikBooking::getRoomParam('checkout', $room_details['params']);
									if ($listing_checkin) {
										// overwrite check-in time
										$listing_checkin_parts = explode(':', $listing_checkin);
										$checkin_h = $listing_checkin_parts[0] ?: $checkin_h;
										$checkin_m = $listing_checkin_parts[1] ?? $checkin_m;
									}
									if ($listing_checkout) {
										// overwrite check-out time
										$listing_checkout_parts = explode(':', $listing_checkout);
										$checkout_h = $listing_checkout_parts[0] ?: $checkout_h;
										$checkout_m = $listing_checkout_parts[1] ?? $checkout_m;
									}
								}

								// set the information about check-in and check-out times
								$ota_min_max_details->checkin  = $checkin_h . ':' . $checkin_m;
								$ota_min_max_details->checkout = $checkout_h . ':' . $checkout_m;
							}

							// we can add the RarPricing node with both min and max advance period information
							$roomrate_identifier = $row['idroomota'] . ':' . $rateplanid;
							$xmlRQ .= "\t" . '<RarPricing type="min_max_advance" idroomota="' . $roomrate_identifier . '">' . "\n";
							$xmlRQ .= "\t\t" . '<Pricing><![CDATA[' . json_encode($ota_min_max_details) . ']]></Pricing>' . "\n";
							$xmlRQ .= "\t" . '</RarPricing>' . "\n";
						}
					}
				}
				//

				$xmlRQ .= '</RarUpdateRQ>';
				$debug_str .= $debug_mode ? htmlentities($xmlRQ) . "\n\n" : '';

				/**
				 * Register the XML data built.
				 * 
				 * @since 	1.9.10
				 */
				$this->setXmlData($xmlRQ, (int) $row['idchannel'], (int) $idroom, (int) $idprice);

				// execute the request
				if (!$debug_mode) {
					/**
					 * Attempt to load the back-end language, if necessary.
					 * This may be useful for the auto-bulk actions.
					 * 
					 * @since 	1.8.3
					 */
					if ($app->isClient('site')) {
						$lang = JFactory::getLanguage();
						if (VCMPlatformDetection::isWordPress()) {
							$lang->attachHandler(VIKCHANNELMANAGER_LIBRARIES . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR . 'admin.php', 'vikchannelmanager');
						} else {
							$lang->load('com_vikchannelmanager', JPATH_ADMINISTRATOR);
						}
					}

					// make sure this channel did not generate errors before
					if (in_array($row['idchannel'], $prevent_errors)) {
						// skip the request and set an error
						$err_mess = JText::_('VCM_RAR_PREVERR_MAPPING');
						if (empty($this->caller)) {
							$responses[$idroom][$row['idchannel']] = 'e4j.error.'.VikChannelManager::getErrorFromMap($err_mess);
						} else {
							$responses[$idroom][$row['idchannel']] = $err_mess;
						}
						continue;
					}

					// make the request to the e4jConnect servers
					$e4jC = new E4jConnectRequest($e4jc_url);
					$e4jC->setPostFields($xmlRQ);
					$e4jC->slaveEnabled = true;
					$rs = $e4jC->exec();

					// build the response
					if ($e4jC->getErrorNo()) {
						$responses[$idroom][$row['idchannel']] = 'e4j.error.'.VikChannelManager::getErrorFromMap('e4j.error.Curl:Error #'.$e4jC->getErrorNo().' '.$e4jC->getErrorMsg());
					} else {
						if (substr($rs, 0, 9) == 'e4j.error') {
							if (empty($this->caller)) {
								$responses[$idroom][$row['idchannel']] = 'e4j.error.'.VikChannelManager::getErrorFromMap($rs);
							} else {
								$responses[$idroom][$row['idchannel']] = $rs;
							}
						} elseif (substr($rs, 0, 11) == 'e4j.warning') {
							if (empty($this->caller)) {
								$responses[$idroom][$row['idchannel']] = 'e4j.warning.'.nl2br(VikChannelManager::getErrorFromMap($rs));
							} else {
								$responses[$idroom][$row['idchannel']] = $rs;
							}
						} else {
							$response = unserialize($rs);
							$channel_prefix = ucwords(str_replace('.com', '', $row['name']));
							$channel_prefix = str_replace('.', '', str_replace('-', '', $channel_prefix));
							if ($response === false || !is_array($response) || !array_key_exists('esit', $response) || !in_array($response['esit'], ['Error', 'Warning', 'Success'])) {
								if (empty($this->caller)) {
									$responses[$idroom][$row['idchannel']] = 'e4j.error.'.VikChannelManager::getErrorFromMap('e4j.error.'.$channel_prefix.'.RAR:InvalidSchema');
								} else {
									$responses[$idroom][$row['idchannel']] = 'e4j.error.InvalidSchema';
								}
							} else {
								if ($response['esit'] == 'Error') {
									/**
									 * Check if the channel returned new mapping information
									 * to prevent further API errors.
									 * 
									 * @since 	1.8.3
									 */
									if (isset($response['new_mapping'])) {
										// push this channel to prevent further API errors
										array_push($prevent_errors, $row['idchannel']);
										// try to decode the JSON string with the new mapping information
										$new_mapping = json_decode($response['new_mapping'], true);
										if (is_array($new_mapping)) {
											// push the new instructions to be parsed later
											$new_mapping['Error'] = [
												'idroomota'  => $row['idroomota'],
												'rateplanid' => $rateplanid,
												'idroomvbo'  => $idroom,
												'idpricevbo' => $idprice,
												'price_name' => $price_name,
											];
											$resolve_errors[$row['idchannel']] = $new_mapping;
										}
									}

									if (empty($this->caller)) {
										$responses[$idroom][$row['idchannel']] = 'e4j.error.'.nl2br(VikChannelManager::getErrorFromMap('e4j.error.'.$channel_prefix.'.RAR:'.$response['message']));
									} else {
										//Always overwrite any possible previous response for this room-channel in case of errors, no matter who's the caller.
										$responses[$idroom][$row['idchannel']] = 'e4j.error.'.$response['message'];
									}
								} elseif ($response['esit'] == 'Warning') {
									/**
									 * Check if the channel returned new mapping information
									 * to prevent further API warnings.
									 * 
									 * @since 	1.8.3
									 */
									if (isset($response['new_mapping'])) {
										// push this channel to prevent further API warnings
										array_push($prevent_errors, $row['idchannel']);
										// try to decode the JSON string with the new mapping information
										$new_mapping = json_decode($response['new_mapping'], true);
										if (is_array($new_mapping)) {
											// push the new instructions to be parsed later
											$new_mapping['Error'] = [
												'idroomota'  => $row['idroomota'],
												'rateplanid' => $rateplanid,
												'idroomvbo'  => $idroom,
												'idpricevbo' => $idprice,
												'price_name' => $price_name,
											];
											$resolve_errors[$row['idchannel']] = $new_mapping;
										}
									}

									if (empty($this->caller)) {
										$warn_mess = nl2br(VikChannelManager::getErrorFromMap('e4j.warning.'.$channel_prefix.'.RAR:'.$response['message']));
									} else {
										$warn_mess = $response['message'];
									}
									$warn_mess_parts = explode(':', $warn_mess);
									if (count($warn_mess_parts) > 1 && empty($this->caller)) {
										$warn_mess = $warn_mess_parts[0].'<span class="vcm-result-readmore-btn">'.JText::_('VCMRATESPUSHREADMORE').'</span><span class="vcm-result-readmore-cont">';
										unset($warn_mess_parts[0]);
										$warn_mess .= implode(':', $warn_mess_parts);
										$warn_mess .= '</span>';
									}
									if ($this->caller == 'SmartBalancer' && isset($responses[$idroom][$row['idchannel']]) && !empty($responses[$idroom][$row['idchannel']])) {
										//When SmartBalancer and this channel already has a response for this room (maybe for a different rate plan), make sure the previous response was not an error
										if (strpos($responses[$idroom][$row['idchannel']], 'e4j.error') === false) {
											//this way we do not override previous errors, but just previous successes or warnings
											$responses[$idroom][$row['idchannel']] = 'e4j.warning.'.$warn_mess;
										}
									} else {
										$responses[$idroom][$row['idchannel']] = 'e4j.warning.'.$warn_mess;
									}
								} else {
									if ($this->caller == 'SmartBalancer' && isset($responses[$idroom][$row['idchannel']]) && !empty($responses[$idroom][$row['idchannel']])) {
										//When SmartBalancer and this channel already has a response for this room (maybe for a different rate plan), make sure the previous response was successful
										if (strpos($responses[$idroom][$row['idchannel']], 'e4j.OK') !== false) {
											//this way we do not override previous errors or warnings
											$responses[$idroom][$row['idchannel']] = 'e4j.OK.'.JText::_('VCMRATESPUSHOKCHRES');
										}
									} else {
										$responses[$idroom][$row['idchannel']] = 'e4j.OK.'.JText::_('VCMRATESPUSHOKCHRES');
									}
								}
							}
						}
					}

					// add breakdown information to responses
					if ($breakdown) {
						if (!isset($responses[$idroom]['breakdown'])) {
							$responses[$idroom]['breakdown'] = $breakdown;
						} else {
							if ($this->caller == 'SmartBalancer') {
								// if multiple rate plans were updated for a certain room, concatenate the previous breakdown
								foreach ($breakdown as $dtbk => $rtbk) {
									if (isset($responses[$idroom]['breakdown'][$dtbk])) {
										if (strpos($responses[$idroom]['breakdown'][$dtbk], $rtbk) !== false) {
											// the same rate has probably been sent to two channels for this room. Skip it.
											continue;
										}
										$responses[$idroom]['breakdown'][$dtbk] .= "\n" . $dtbk . ' ' . $rtbk;
									} else {
										$responses[$idroom]['breakdown'][$dtbk] = $rtbk;
									}
								}
							} else {
								$responses[$idroom]['breakdown'] = $breakdown;
							}
						}
					}
				}

				if ($sleep_allowed === true && !$debug_mode) {
					if (!empty($this->caller)) {
						// reduce the delay time when coming from the App, from the Rates Overview or from the SmartBalancer
						sleep(1);
					} else {
						// set a higher sleep time for the Bulk Action - Rates Upload (back-end)
						// sleep(2);
						/**
						 * We believe sleeping for one second is more than sufficient for the Bulk Action
						 * if we also consider that delays are also applied through JS between calls.
						 * 
						 * @since 	1.8.0
						 */
						sleep(1);
					}
				}
			}
		}

		// update derived pricing
		$session->set('vcmRatespushDerpr', $derived_pr_cache);
		
		if ($debug_mode) {
			// debug the whole XML request and exit
			$this->setError('e4j.error.' . print_r($_POST, true) . "\n\n" . $debug_str);
			return false;
		}

		/**
		 * Check if mapping errors should be resolved to prevent further API errors.
		 * 
		 * @since 	1.8.3
		 */
		$this->resolveMappingErrors($resolve_errors);

		/**
		 * Store the rates flow records once the request has terminated.
		 * 
		 * @since 	1.8.3
		 */
		$rflow_handler->storeRecords();

		// unset empty responses, if any, then trim breakdown logs
		foreach ($responses as $idroom => $r) {
			if (!is_array($r) || !$r) {
				unset($responses[$idroom]);
				continue;
			}
			if (is_array($r['breakdown'] ?? [])) {
				$responses[$idroom]['breakdown'] = array_map(function($bklog) {
					return is_string($bklog) ? trim($bklog) : $bklog;
				}, (array) $r['breakdown']);
			}
		}

		// return the encoded update results
		return json_encode($responses);
	}

	/**
	 * Sets the pricing alteration overrides for the various OTAs.
	 * 
	 * @param 	array 	$ota_pricing 	Associative list of pricing alteration commands.
	 * 
	 * @return 	self
	 * 
	 * @since 	1.9.4
	 */
	public function setOTAPricingOverrides(array $ota_pricing)
	{
		// ensure given commands
		$ota_pricing = array_filter($ota_pricing, function($command) {
			// we expect a list of linear arrays containing at least 4 instructions each
			return is_array($command) && count($command) >= 4;
		});

		// set associative list of pricing alteration instructions
		$this->ota_pricing_overrides = $ota_pricing;

		return $this;
	}

	/**
	 * Updates the mapping information from the given instructions
	 * returned by the e4jConnect servers. This is to prevent future
	 * API errors due to outdated or invalid rate plans.
	 * 
	 * @param 	array 	$resolve_errors 	associative array of channel mapping data.
	 * 
	 * @return 	bool 						true if operations were performed, or false.
	 * 
	 * @since 	1.8.3
	 * @since 	1.8.4 	additional improvements to prevent Expedia AR errors/warnings.
	 */
	private function resolveMappingErrors(array $resolve_errors)
	{
		if (!$resolve_errors) {
			return false;
		}

		// whether some records were updated
		$records_updated = false;

		// associative list of updated mapping
		$updated_mapping = array();

		foreach ($resolve_errors as $id_channel => $new_mapping) {
			if (!is_array($new_mapping) || !isset($new_mapping['Rooms']) || !is_array($new_mapping['Rooms'])) {
				continue;
			}
			foreach ($new_mapping['Rooms'] as $room) {
				if (!is_array($room) || !isset($room['RatePlan']) || !is_array($room['RatePlan']) || !count($room['RatePlan'])) {
					continue;
				}
				$channel_room_id   = !empty($room['id']) ? $room['id'] : null;
				$channel_room_name = !empty($room['name']) ? $room['name'] : null;
				if (empty($channel_room_id)) {
					continue;
				}
				// look up this room on the DB
				$q = "SELECT `id` FROM `#__vikchannelmanager_roomsxref` WHERE `idroomota`=" . $this->dbo->quote($channel_room_id) . " AND `idchannel`=" . $this->dbo->quote($id_channel);
				$this->dbo->setQuery($q, 0, 1);
				$record_id = $this->dbo->loadResult();
				if (!$record_id) {
					// this room has not been mapped yet
					continue;
				}

				// build updated associative array for room rate plans
				$room_rplans = array();
				foreach ($room['RatePlan'] as $rplan) {
					$room_rplans[$rplan['id']] = $rplan;
				}

				// get the updated mapping information for this room
				if (!isset($updated_mapping[$id_channel])) {
					$updated_mapping[$id_channel] = array();
				}
				$updated_mapping[$id_channel][$channel_room_id] = $room_rplans;

				// update the information on the DB
				$new_room_record = new stdClass;
				$new_room_record->id = $record_id;
				if (!empty($channel_room_name)) {
					$new_room_record->otaroomname = $channel_room_name;
				}
				$new_room_record->otapricing = json_encode(array('RatePlan' => $room_rplans));
				// update object record
				$records_updated = $this->dbo->updateObject('#__vikchannelmanager_roomsxref', $new_room_record, 'id') || $records_updated;
			}
		}

		if ($records_updated) {
			/**
			 * Take care of the bulk rates cache, to unset combinations
			 * that generated an error or a warning with the channel.
			 * 
			 * @since 	1.8.4
			 */
			$cache_updated 	  = false;
			$bulk_rates_cache = VikChannelManager::getBulkRatesCache();

			// parse the combinations that triggered the error/warning
			foreach ($resolve_errors as $id_channel => $new_mapping) {
				if (!isset($new_mapping['Error'])) {
					continue;
				}
				$idroomvbo  = $new_mapping['Error']['idroomvbo'];
				$idpricevbo = $new_mapping['Error']['idpricevbo'];
				$idroomota  = $new_mapping['Error']['idroomota'];
				$rateplanid = $new_mapping['Error']['rateplanid'];
				$price_name = $new_mapping['Error']['price_name'];
				if (!isset($bulk_rates_cache[$idroomvbo]) || !isset($bulk_rates_cache[$idroomvbo][$idpricevbo])) {
					// no rates cache for this combination of room rate plan
					continue;
				}
				// make sure the rates cache is available for this channel
				if (!isset($bulk_rates_cache[$idroomvbo][$idpricevbo]['channels']) || !in_array($id_channel, $bulk_rates_cache[$idroomvbo][$idpricevbo]['channels'])) {
					continue;
				}
				// check if the OTA rate plan ID that triggered the error is cached
				if (!isset($bulk_rates_cache[$idroomvbo][$idpricevbo]['rplans']) || !isset($bulk_rates_cache[$idroomvbo][$idpricevbo]['rplans'][$id_channel])) {
					continue;
				}
				if ($bulk_rates_cache[$idroomvbo][$idpricevbo]['rplans'][$id_channel] != $rateplanid) {
					// not the OTA rate plan ID that we want to look for
					continue;
				}
				// we need to find a good replacement for this invalid OTA rate plan ID that triggers errors
				if (!isset($updated_mapping[$id_channel]) || !isset($updated_mapping[$id_channel][$idroomota])) {
					// no information about the updated rate plans for this room-type
					continue;
				}
				// check for a valid replacement depending on the channel type
				$replacement = null;
				if ($id_channel == VikChannelManagerConfig::EXPEDIA) {
					// attempt to find the best replacement for this rate plan ID
					$sorted_pricing = VikChannelManager::sortExpediaChannelPricing([
						'RatePlan' => $updated_mapping[$id_channel][$idroomota],
					]);
					if (!is_array($sorted_pricing['RatePlan']) || !count($sorted_pricing['RatePlan'])) {
						continue;
					}
					// we usually have one rate plan per Distribution Model, so we divide the rate plans by 2
					$max_attempts = floor(count($sorted_pricing['RatePlan']) / 2);
					$curr_attempt = 1;
					foreach ($sorted_pricing['RatePlan'] as $rp_id => $rp_data) {
						if ($curr_attempt > $max_attempts) {
							break;
						}
						if (empty($price_name) || empty($rp_data['name'])) {
							// grab the very first replacement
							$replacement = $rp_id;
							break;
						}
						// try matching the website rate plan name to the OTA rate plan name
						$ibe_standard_match = (stripos($price_name, 'Standard') !== false || stripos($price_name, 'Base') !== false);
						$ota_standard_match = (stripos($rp_data['name'], 'Standard') !== false || stripos($rp_data['name'], 'Base') !== false);
						if ($ibe_standard_match && $ota_standard_match) {
							// hipothetical match for "Standard Rate" found
							$replacement = $rp_id;
							break;
						}
						$ibe_nonref_match = (stripos($price_name, 'Non') !== false && stripos($price_name, 'Not') !== false);
						$ota_nonref_match = (stripos($rp_data['name'], 'Non') !== false && stripos($rp_data['name'], 'Not') !== false);
						if ($ibe_nonref_match && $ota_nonref_match) {
							// hipothetical match for "Non-Refundable Rate" found
							$replacement = $rp_id;
							break;
						}
						// increate attempt counter
						$curr_attempt++;
					}
				}

				if (!empty($replacement)) {
					// overwrite cached rate plan ID for next usage
					$bulk_rates_cache[$idroomvbo][$idpricevbo]['rplans'][$id_channel] = $replacement;
					// turn flag on
					$cache_updated = true;
				}
			}

			if ($cache_updated) {
				// update the bulk rates cache for next usage
				$q = "UPDATE `#__vikchannelmanager_config` SET `setting`=" . $this->dbo->quote(json_encode($bulk_rates_cache)) . " WHERE `param`='bulkratescache';";
				$this->dbo->setQuery($q);
				$this->dbo->execute();
			}
		}

		return $records_updated;
	}

	/**
	 * If the class is run in multi-requests, every method will reset
	 * the error string before its execution.
	 * By default, the class does not run in multi-request and all the
	 * error messages are concatenated for the execution.
	 * 
	 * @param 	boolean 	$status
	 *
	 * @return 	object 		$this 	for chain-ability
	 */
	public function setMultiRequests($status = true)
	{
		$this->multiReq = (bool)$status;
		return $this;
	}

	/**
	 * If in multiple requests, the error string is reset.
	 * 
	 * @return 	void
	 */
	private function prepareMethod()
	{
		if ($this->multiReq) {
			$this->error = '';
		}
	}

	/**
	 * Load the main lib of VikBooking if not already loaded.
	 * 
	 * @return 	void
	 * 
	 * @deprecated
	 */
	private function importVboLib()
	{
		if (!class_exists('VikBooking')) {
			require_once(VBO_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "lib.vikbooking.php");
		}
	}

	/**
	 * Set an execution error message.
	 * 
	 * @param 	string 	$str
	 *
	 * @return 	void
	 */
	private function setError($str)
	{
		$this->error .= $str."\n";
	}

	/**
	 * Get the execution error message.
	 * If true is passed to the method, it will return
	 * the error string only if not empty, false otherwise.
	 * 
	 * @param 	boolean 	$need_bool
	 * 
	 * @return 	boolean|string
	 */
	public function getError($need_bool = false)
	{
		$error = rtrim($this->error, "\n");
		if ($need_bool) {
			return strlen($error) ? $error : false;
		}
		return $error;
	}

	/**
	 * Registers the XML information built before the transmission.
	 * 
	 * @param 	string 	$xml 		The XML string representation.
	 * @param 	int 	$idchannel 	The channel identifier.
	 * @param 	int 	$idroom 	The room identifier.
	 * @param 	int 	$idprice 	The rate plan identifier.
	 * 
	 * @return 	void
	 * 
	 * @since 	1.9.10
	 * @since 	1.9.16 added support to argument $idprice
	 */
	private function setXmlData(string $xml, int $idchannel = 0, int $idroom = 0, int $idprice = 0)
	{
		// push XML data
		$this->xmlData[] = [
			'xml'       => $xml,
			'idchannel' => $idchannel,
			'idroom'    => $idroom,
			'idprice'   => $idprice,
		];
	}

	/**
	 * Returns the XML information set before the transmission.
	 * 
	 * @param 	bool 	$onlyXml 	True for returning a list of XML files.
	 * 
	 * @return 	array 				List of XML data requested.
	 * 
	 * @since 	1.9.10
	 */
	public function getXmlData(bool $onlyXml = false)
	{
		if ($onlyXml) {
			return array_column($this->xmlData, 'xml');
		}

		return $this->xmlData;
	}

	/**
	 * Registers the cached pricing information.
	 * 
	 * @param 	array 	$pricing 	The pricing data to set.
	 * 
	 * @return 	VikBookingConnector
	 * 
	 * @since 	1.9.10
	 */
	public function setPricingData(array $pricing)
	{
		$this->pricingData = $pricing;

		return $this;
	}

	/**
	 * Toggles debug mode at runtime.
	 * 
	 * @param 	bool 	$mode 	The debug mode to set.
	 * 
	 * @return 	VikBookingConnector
	 * 
	 * @since 	1.9.10
	 */
	public function setDebug(bool $mode)
	{
		$this->debug = $mode;

		return $this;
	}
}
