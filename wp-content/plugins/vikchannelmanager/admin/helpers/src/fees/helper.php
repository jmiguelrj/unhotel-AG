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
 * VCM fees helper with Vik Booking.
 * 
 * @since 	1.8.12
 */
final class VCMFeesHelper extends JObject
{
	/**
	 * The singleton instance of the class.
	 *
	 * @var  VCMFeesHelper
	 */
	private static $instance = null;

	/**
	 * Proxy for immediately getting the object and bind data.
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
	 * Fetches all the available options marked as a refundable damage deposit.
	 * 
	 * @return 	array 	empty or associative array of damage-deposit options.
	 */
	public function getDamageDepositOptions()
	{
		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true);

		$q->select($dbo->qn('o') . '.*');
		$q->from($dbo->qn('#__vikbooking_optionals', 'o'));

		$dbo->setQuery($q);
		$options = $dbo->loadAssocList();

		if (!$options) {
			return [];
		}

		$damage_deposits = [];
		foreach ($options as &$option) {
			$option['oparams'] = (array)json_decode($option['oparams'], true);
			if (!$option['oparams'] || empty($option['oparams']['damagedep'])) {
				continue;
			}
			// set option record
			$damage_deposits[$option['id']] = $option;
		}

		// unset last reference
		unset($option);

		return $damage_deposits;
	}
}
