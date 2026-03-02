<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2023 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * OTA Rate plan helper. Used to parse specific channel
 * related meals and other information.
 * 
 * @since 	1.8.12
 */
final class VCMOtaRateplan extends JObject
{
	/**
	 * The singleton instance of the class.
	 *
	 * @var  VCMOtaRateplan
	 */
	private static $instance = null;

	/**
	 * Proxy to construct the object.
	 * 
	 * @param 	array|object  $data  optional data to bind.
	 * @param 	boolean 	  $anew  true for forcing a new instance.
	 * 
	 * @return 	self
	 */
	public static function getInstance($data = [], $anew = false)
	{
		if (is_null(static::$instance) || $anew) {
			static::$instance = new static($data);
		}

		return static::$instance;
	}

	/**
	 * Returns an associative list of meal plans included in OTA room reservation record.
	 * VBO may call this method to rely on the VCM internal mapping data obtained through
	 * the channel respective Content/Product/Listing APIs.
	 * 
	 * @param 	array 	$res 		the OTA reservation record.
	 * @param 	array 	$room_res 	the optional room reservation record.
	 * 
	 * @return 	array 				list of included meal enum values calculated on the fly.
	 */
	public function findIncludedMeals(array $res, array $room_res = [])
	{
		$dbo = JFactory::getDbo();

		// meal plans enums are expected to be injected
		$meal_plans = $this->get('meal_plans', []);

		if (!is_array($meal_plans) || !$meal_plans) {
			// with no supported enum values we cannot proceed
			return [];
		}

		if (empty($res['channel']) || empty($room_res['idroom'])) {
			// channel identifier and room ID booked are mandatory
			return [];
		}

		$included_meals = [];

		if (stripos($res['channel'], 'expedia') !== false && !empty($room_res['otarplan'])) {
			/**
			 * Use the Expedia Product API data to check if this rate plan has got
			 * some value add inclusions defined for the booked rate plan name.
			 */
			$q = $dbo->getQuery(true);

			$q->select($dbo->qn([
				'x.idroomvb',
				'x.idroomota',
				'x.otapricing',
				'd.setting',
			]))->from($dbo->qn('#__vikchannelmanager_roomsxref', 'x'));

			$q->leftJoin($dbo->qn('#__vikchannelmanager_otarooms_data', 'd') . ' ON ' . $dbo->qn('x.idroomota') . ' = ' . $dbo->qn('d.idroomota'))
				->where($dbo->qn('x.idroomvb') . ' = ' . (int)$room_res['idroom'])
				->where($dbo->qn('x.idchannel') . ' = ' . (int)VikChannelManagerConfig::EXPEDIA)
				->where($dbo->qn('d.param') . ' = ' . $dbo->q('room_content'));

			$dbo->setQuery($q, 0, 1);

			$ota_room_data = $dbo->loadAssoc();
			if (!$ota_room_data || empty($ota_room_data['setting'])) {
				return [];
			}

			$ota_room_data = json_decode($ota_room_data['setting'], true);
			if (!is_array($ota_room_data) || !$ota_room_data || empty($ota_room_data['_ratePlans'])) {
				return [];
			}

			// check if the OTA rate plan IDs were stored
			$ota_rplan_ids = [];
			if (!empty($res['ota_type_data'])) {
				if (!is_array($res['ota_type_data'])) {
					$res['ota_type_data'] = json_decode($res['ota_type_data'], true);
				}
				if (is_array($res['ota_type_data']) && !empty($res['ota_type_data']['rateplan_ids'])) {
					// could be an string or an array of strings
					$ota_rplan_ids = (array)$res['ota_type_data']['rateplan_ids'];
					$ota_rplan_ids = array_map(function($ota_rpid) {
						// keep only numbers to avoid string values like "123A" for distribution models
						return preg_replace("/[^0-9]+/", '', $ota_rpid);
					}, $ota_rplan_ids);
				}
			}

			// seek for the value-add inclusions of the booked OTA rate plan
			$value_add_inclusions = [];
			$expedia_meal_value_adds = VCMExpediaProduct::getMealValueAddInclusions();

			foreach ($ota_room_data['_ratePlans'] as $ota_rplan) {
				if (!is_array($ota_rplan) || empty($ota_rplan['resourceId']) || empty($ota_rplan['name']) || empty($ota_rplan['valueAddInclusions'])) {
					continue;
				}
				if ($ota_rplan_ids) {
					// we must find the exact OTA rate plan by ID
					if (in_array($ota_rplan['resourceId'], $ota_rplan_ids)) {
						// reserved OTA rate plan was found
						$value_add_inclusions = (array)$ota_rplan['valueAddInclusions'];
					}
				} else {
					// use the OTA rate plan name to find the matching record
					if (!strcasecmp($ota_rplan['name'], $room_res['otarplan'])) {
						// reserved OTA rate plan was found
						$value_add_inclusions = (array)$ota_rplan['valueAddInclusions'];
					}
				}
				if ($value_add_inclusions) {
					// make all values lower-case
					$value_add_inclusions = array_map('strtolower', $value_add_inclusions);

					// stop the loop
					break;
				}
			}

			foreach ($value_add_inclusions as $value_add_inclusion) {
				if (!is_string($value_add_inclusion) || !isset($expedia_meal_value_adds[$value_add_inclusion])) {
					continue;
				}
				// this Expedia value add inclusion offers some meals
				foreach ($expedia_meal_value_adds[$value_add_inclusion] as $meal_enum) {
					if (isset($meal_plans[$meal_enum])) {
						// push meal plan included
						$included_meals[$meal_enum] = $meal_plans[$meal_enum];
					}
				}
			}
		}

		return $included_meals;
	}
}
