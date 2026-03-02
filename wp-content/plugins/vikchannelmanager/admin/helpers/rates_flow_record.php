<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2019 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Class handler for a rates flow record object.
 * 
 * @since 	1.8.3
 */
class VCMRatesFlowRecord extends JObject
{
	/**
	 * Class constructor.
	 * 
	 * @param 	mixed 	$data 	Either an associative array or an object.
	 */
	public function __construct($data = [])
	{
		// construct parent JObject to set initial properties, if any
		parent::__construct($data);
	}

	/**
	 * Tells whether we are parsing a new record object.
	 * 
	 * @return 	bool 	true if record has not been stored, or false.
	 */
	public function isNewRecord()
	{
		$id = $this->get('id', null);

		return empty($id);
	}

	/**
	 * Sets the from and to dates for the rates flow record.
	 * 
	 * @param 	string 	$from 	the from-date in Y-m-d format.
	 * @param 	string 	$to 	the to-date in Y-m-d format.
	 * 
	 * @return 	self
	 */
	public function setDates($from, $to = null)
	{
		if (empty($from)) {
			// from date cannot be empty
			return $this;
		}

		if (empty($to)) {
			// single date
			$to = $from;
		}

		// set properties
		$this->set('day_from', $from);
		$this->set('day_to', $to);

		return $this;
	}

	/**
	 * Gets the from and to dates for the record.
	 * 
	 * @return 	array 	[0] = from date, [1] = to date.
	 */
	public function getDates()
	{
		return array(
			$this->get('day_from', null),
			$this->get('day_to', null),
		);
	}

	/**
	 * Sets the channel unique key for the record.
	 * 
	 * @param 	int 	$unique_key 	the unique key of the channel involved (0 or -1 = VBO)
	 * 
	 * @return 	self
	 */
	public function setChannelID($unique_key)
	{
		// set property channel_id
		$this->set('channel_id', (int)$unique_key);

		return $this;
	}

	/**
	 * Gets the channel unique for the record.
	 * 
	 * @param 	mixed 	$def 	default value if not set.
	 * 
	 * @return 	int
	 */
	public function getChannelID($def = 0)
	{
		// get property channel_id
		return (int)$this->get('channel_id', $def);
	}

	/**
	 * Sets the OTA room ID for the record.
	 * 
	 * @param 	string 	$ota_room_id 	the ID of the OTA room.
	 * 
	 * @return 	self
	 */
	public function setOTARoomID($ota_room_id)
	{
		// set property ota_room_id
		$this->set('ota_room_id', (string)$ota_room_id);

		return $this;
	}

	/**
	 * Gets the OTA room ID for the record.
	 * 
	 * @param 	mixed 	$def 	default value if not set.
	 * 
	 * @return 	string
	 */
	public function getOTARoomID($def = null)
	{
		// get property ota_room_id
		return $this->get('ota_room_id', $def);
	}

	/**
	 * Sets the VBO room ID for the record.
	 * 
	 * @param 	int 	$vbo_room_id 	the ID of the room in VBO.
	 * 
	 * @return 	self
	 */
	public function setVBORoomID($vbo_room_id)
	{
		// set property vbo_room_id
		$this->set('vbo_room_id', (int)$vbo_room_id);

		return $this;
	}

	/**
	 * Gets the VBO room ID for the record.
	 * 
	 * @param 	mixed 	$def 	default value if not set.
	 * 
	 * @return 	int
	 */
	public function getVBORoomID($def = null)
	{
		// get property vbo_room_id
		return $this->get('vbo_room_id', $def);
	}

	/**
	 * Sets the VBO rate plan ID for the record.
	 * 
	 * @param 	int 	$vbo_price_id 	the ID of the type of price in VBO.
	 * 
	 * @return 	self
	 */
	public function setVBORatePlanID($vbo_price_id)
	{
		// set property vbo_price_id
		$this->set('vbo_price_id', (int)$vbo_price_id);

		return $this;
	}

	/**
	 * Gets the VBO rate plan ID for the record.
	 * 
	 * @param 	mixed 	$def 	default value if not set.
	 * 
	 * @return 	int
	 */
	public function getVBORatePlanID($def = null)
	{
		// get property vbo_price_id
		return $this->get('vbo_price_id', $def);
	}

	/**
	 * Sets the nightly fee of the record. This should be the exact rate
	 * transmitted to the channel, if applicable and according to the
	 * pricing model involved. It's inclusive of any channel alteration.
	 * 
	 * @param 	float 	$fee 	the (base) cost for the nights involved.
	 * 
	 * @return 	self
	 */
	public function setNightlyFee($fee)
	{
		// set property nightly_fee
		$this->set('nightly_fee', (float)$fee);

		return $this;
	}

	/**
	 * Gets the nightly fee for the record.
	 * 
	 * @param 	mixed 	$def 	default value if not set.
	 * 
	 * @return 	string
	 */
	public function getNightlyFee($def = null)
	{
		// get property nightly_fee
		return $this->get('nightly_fee', $def);
	}

	/**
	 * Sets the base fee of the record. It's the base cost of the website
	 * rate plan before any pricing modification, taken from the Rates Table.
	 * 
	 * @param 	float 	$fee 	the base cost for the website rate plan.
	 * 
	 * @return 	self
	 * 
	 * @since 	1.8.4
	 */
	public function setBaseFee($fee)
	{
		// set property base_fee
		$this->set('base_fee', (float)$fee);

		return $this;
	}

	/**
	 * Gets the base fee for the record.
	 * 
	 * @param 	mixed 	$def 	default value if not set.
	 * 
	 * @return 	string
	 * 
	 * @since 	1.8.4
	 */
	public function getBaseFee($def = null)
	{
		// get property base_fee
		return $this->get('base_fee', $def);
	}

	/**
	 * Sets the OTA price alteration string for the record.
	 * 
	 * @param 	string 	$alteration 	the alteration string, like +18%.
	 * 
	 * @return 	self
	 */
	public function setChannelAlteration($alteration)
	{
		// set property channel_alter
		$this->set('channel_alter', (string)$alteration);

		return $this;
	}

	/**
	 * Gets the OTA price alteration string for the record.
	 * 
	 * @param 	mixed 	$def 	default value if not set.
	 * 
	 * @return 	string
	 */
	public function getChannelAlteration($def = null)
	{
		// get property channel_alter
		return $this->get('channel_alter', $def);
	}

	/**
	 * Sets the data object/array for the record, which usually
	 * indicates information about restrictions or extra data.
	 * 
	 * @param 	mixed 	$data 	extra data associative array or object.
	 * @param 	bool 	$encode whether to immediately encode the array/object.
	 * @param 	bool 	$merge 	whether to merge or replace the previous extra data.
	 * 
	 * @return 	self
	 */
	public function setExtraData($data, $encode = true, $merge = true)
	{
		if (is_scalar($data)) {
			$data = null;
		} elseif ($merge) {
			// check if the current extra data should be merged with the previous data
			$prev_data = $this->getExtraData(true, true);
			if (!empty($prev_data) && is_array($prev_data)) {
				// we have previous data, cast current data to array for merging
				$data = (array)$data;
				// merge data values (new data will replace old existing data)
				$data = array_merge($prev_data, $data);
			}
		}

		if ($data !== null && $encode) {
			$data = json_encode($data);
		}

		// set property data
		$this->set('data', $data);

		return $this;
	}

	/**
	 * Returns the (decoded) data object/array for the record.
	 * 
	 * @param 	bool 	$decode 	whether to return the data JSON decoded.
	 * @param 	bool 	$assoc 		whether to decode as associative arrays.
	 * 
	 * @return 	mixed 				array/object or string if !$decode.
	 */
	public function getExtraData($decode = true, $assoc = false)
	{
		// get property data
		$data = $this->get('data', null);

		if ($data !== null && is_string($data) && $decode) {
			return json_decode($data, $assoc);
		}

		return $data;
	}

	/**
	 * Given a list of LOS records for any occupancy and number of nights of stay,
	 * this method smartly builds a nested list of occupancy - up-to-nights list
	 * of rates without needing to store any rates when they are proportional.
	 * Once the list has been build, this is set as extra data for the rates flow record.
	 * Such sensitive extra data must use a specific property name in the object.
	 * 
	 * @param 	array 	$los_occ_rates 	associative array of occupancy-night rates.
	 * 
	 * @return 	self
	 */
	public function setLOSRecords($los_occ_rates)
	{
		if (!is_array($los_occ_rates) || !count($los_occ_rates)) {
			return $this;
		}

		// clean up the LOS associative array for any occupancy, if possible
		foreach ($los_occ_rates as $occupancy => $los_rates) {
			// sort by num nights ascending
			ksort($los_rates, SORT_NUMERIC);
			// loop through all number of nights to see which prices are not proportional
			$compare_nightly_price = -1;
			foreach ($los_rates as $num_nights => $price) {
				if ($num_nights < 1) {
					continue;
				}
				$curr_nightly_price = ($price / $num_nights);
				/**
				 * LOS rates could be integers, and so (half-up) rounding may be applied.
				 * We allow a margin of 1 as the rounding difference that may occur.
				 * I.E. 1 night = 136.36 (136), 2 nights = 272.73 (273) - difference = 1.
				 */
				if ($compare_nightly_price == $curr_nightly_price || abs($compare_nightly_price - $curr_nightly_price) < 2) {
					// we have a proportional price for this number of nights so we unset it
					unset($los_rates[$num_nights]);
				} else {
					// we need to take this LOS row-record
					$compare_nightly_price = $curr_nightly_price;
				}
			}
			// update LOS rates for this occupancy with just the non-proportional number of nights
			$los_occ_rates[$occupancy] = $los_rates;
		}

		/**
		 * Clean up (unset) the occupancy LOS rates that have the same pricing.
		 * For example, if 2 and 3 guests have the same pricing, we omit the records
		 * for 2 adults by keeping the row for the highest occupancy of 3 adults.
		 */
		$los_occupancies = array_keys($los_occ_rates);
		sort($los_occupancies, SORT_NUMERIC);
		foreach ($los_occupancies as $los_occ_k => $occ) {
			$next_occ_key = ($los_occ_k + 1);
			if ($occ < 1 || !isset($los_occupancies[$next_occ_key])) {
				continue;
			}
			$next_occupancy = $los_occupancies[$next_occ_key];
			if (!isset($los_occ_rates[$next_occupancy])) {
				break;
			}
			if ($los_occ_rates[$occ] == $los_occ_rates[$next_occupancy]) {
				// next occupancy has the same pricing rules so we unset the current one by keeping the next
				unset($los_occ_rates[$occ]);
			}
		}

		if (count($los_occ_rates)) {
			// merge extra data for RatesLOS
			$rflow_los_data = array(
				'RatesLOS' => $los_occ_rates
			);
			return $this->setExtraData($rflow_los_data);
		}

		return $this;
	}

	/**
	 * Gets the LOS records from record data.
	 * 
	 * @return 	array 	associative or empty array.
	 */
	public function getLOSRecords()
	{
		$data = $this->getExtraData(true, true);

		if (is_array($data) && isset($data['RatesLOS'])) {
			return $data['RatesLOS'];
		}

		return array();
	}

	/**
	 * Sets the rate flow record extra data for the OTA rate plan.
	 * We expect to receive at least the keys "id" and "name".
	 * Such sensitive extra data must use a specific property name in the object.
	 * 
	 * @param 	array 	$rate_plan 	associative array of OTA rate plan info.
	 * 
	 * @return 	self
	 */
	public function setOTARatePlan($rate_plan)
	{
		if (!is_array($rate_plan) || !isset($rate_plan['id'])) {
			// we expect at least the property id to be available
			return $this;
		}

		// merge extra data for the OTA rate plan
		$rplan_data = array(
			'RatePlan' => $rate_plan
		);

		return $this->setExtraData($rplan_data);
	}

	/**
	 * Gets the OTA rate plan info from record data.
	 * 
	 * @return 	array 	associative or empty array.
	 */
	public function getOTARatePlan()
	{
		$data = $this->getExtraData(true, true);

		if (is_array($data) && isset($data['RatePlan'])) {
			return $data['RatePlan'];
		}

		return array();
	}

	/**
	 * Sets the rate flow record extra data for the restrictions.
	 * We expect to receive at least the key "minLOS".
	 * Such sensitive extra data must use a specific property name in the object.
	 * 
	 * @param 	array 	$restr 	associative array of the restrictions.
	 * 
	 * @return 	self
	 */
	public function setRestrictions($restr)
	{
		if (!is_array($restr) || !isset($restr['minLOS'])) {
			// we expect at least the property minLOS to be available
			return $this;
		}

		// merge extra data for the restrictions
		$restr_data = array(
			'Restrictions' => $restr
		);

		return $this->setExtraData($restr_data);
	}

	/**
	 * Gets the restrictions info from record data.
	 * 
	 * @return 	array 	associative or empty array.
	 */
	public function getRestrictions()
	{
		$data = $this->getExtraData(true, true);

		if (is_array($data) && isset($data['Restrictions'])) {
			return $data['Restrictions'];
		}

		return array();
	}

	/**
	 * Sets the record created by string.
	 * 
	 * @param 	string 	$by 		who is creating this record.
	 * @param 	string 	$api_user 	optional authenticated API (App) user email.
	 * 
	 * @return 	self
	 * 
	 * @since 	1.8.24 	added support for the 2nd argument $api_user.
	 */
	public function setCreatedBy($by, $api_user = '')
	{
		// set property created_by
		$this->set('created_by', (string)$by);

		/**
		 * We force the creation of an extra-data value for the currently logged in
		 * user or API (App) account so that the field "created_by" will always refer
		 * to the framework section only, and not to who actually made the request.
		 * 
		 * @since 	1.8.24
		 */
		$user_requestor = '';
		$user = JFactory::getUser();

		if ($api_user) {
			$user_requestor = $api_user;
		} elseif (!$user->guest) {
			$user_requestor = $user->name;
		}

		if ($user_requestor) {
			$this->setExtraData([
				'User' => $user_requestor,
			]);
		}

		return $this;
	}

	/**
	 * Gets the created by string for the record.
	 * 
	 * @param 	mixed 	$def 	default value if not set.
	 * 
	 * @return 	string
	 */
	public function getCreatedBy($def = null)
	{
		// get property created_by
		return $this->get('created_by', $def);
	}

	/**
	 * Sets the record created on date-time string.
	 * Must be an SQL timestamp compatible string in Y-m-d H:i:s format.
	 * 
	 * @param 	string 	$con 	when this record was created.
	 * 
	 * @return 	self
	 */
	public function setCreatedOn($con = null)
	{
		// set property created_on
		$this->set('created_on', $con);

		return $this;
	}

	/**
	 * Gets the created on date-time string for the record.
	 * Must be an SQL timestamp compatible string in Y-m-d H:i:s format.
	 * 
	 * @return 	string 	date-time string in Y-m-d H:i:s format.
	 */
	public function getCreatedOn()
	{
		// get property created_on
		$con = $this->get('created_on', null);

		if (empty($con)) {
			// get current timestamp
			$con = JDate::getInstance(date('Y-m-d H:i:s'))->toSql();
		}

		return $con;
	}
}
