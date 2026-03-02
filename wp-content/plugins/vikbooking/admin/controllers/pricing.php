<?php
/** 
 * @package     VikBooking
 * @subpackage  core
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * VikBooking pricing controller.
 *
 * @since 	1.15.5 (J) - 1.5.11 (WP)
 */
class VikBookingControllerPricing extends JControllerAdmin
{
	/**
	 * AJAX endpoint for setting new room rates/min LOS.
	 */
	public function setnewrates()
	{
		$app = JFactory::getApplication();
		$dbo = JFactory::getDbo();
		$session = JFactory::getSession();

		if (!JSession::checkToken()) {
			// missing CSRF-proof token
			VBOHttpDocument::getInstance($app)->close(403, JText::_('JINVALID_TOKEN'));
		}

		$updforvcm = $session->get('vbVcmRatesUpd', '');
		$updforvcm = empty($updforvcm) || !is_array($updforvcm) ? [] : $updforvcm;

		// gather operation values
		$pid_room = $app->input->getInt('id_room', 0);
		$pid_price_main = $app->input->getInt('id_price', 0);
		$prate_main = $app->input->getFloat('rate', 0);
		$pvcm = $app->input->getInt('vcm', 0);
		$pminlos = $app->input->getInt('minlos', 0);
		$prateclosed = $app->input->getInt('rateclosed', 0);
		$pfromdate = $app->input->getString('fromdate', '');
		$ptodate = $app->input->getString('todate', '');
		$pota_pricing = $app->input->get('ota_pricing', [], 'array');
		$pskip_derived = $app->input->getBool('skip_derived', false);

		/**
		 * Added support to "increase/decrease" rates type.
		 * 
		 * @since 	1.18.6 (J) - 1.8.6 (WP)
		 */
		$prate_type = $app->input->getString('rate_type', 'fixed');
		$paddsub_op = $app->input->getUInt('addsub_op', 0);
		$paddsub_amount = $app->input->getFloat('addsub_amount', 0);
		$paddsub_value = $app->input->getUInt('addsub_value', 0);
		$pasync_rar = $app->input->getBool('async_rar', false);

		if (empty($pid_room) || empty($pid_price_main) || ($prate_type !== 'addsub' && (empty($prate_main) || !($prate_main > 0))) || empty($pfromdate) || empty($ptodate)) {
			VBOHttpDocument::getInstance()->close(200, 'e4j.error.' . addslashes(JText::_('VBRATESOVWERRNEWRATE')));
		}

		if (strtotime($pfromdate) < strtotime(date('Y-m-d'))) {
			// from date is in the past, convert it to today
			$pfromdate = date('Y-m-d');
		}

		if (strtotime($pfromdate) > strtotime($ptodate)) {
			// invalid dates not allowed
			VBOHttpDocument::getInstance()->close(200, 'e4j.error.Invalid dates');
		}

		try {
			// access the model pricing by binding data
			$model = VBOModelPricing::getInstance([
				'from_date'       => $pfromdate,
				'to_date'         => $ptodate,
				'id_room'         => $pid_room,
				'id_price'        => $pid_price_main,
				'rate'            => $prate_main,
				'min_los'         => $pminlos,
				'close_rate_plan' => (bool) $prateclosed,
				'update_otas'     => (bool) $pvcm,
				'ota_pricing'     => $pota_pricing,
				'skip_derived'    => $pskip_derived,
				// additional properties in case of increase/decrease rates
				'addsub_op'       => $paddsub_op,
				'addsub_amount'   => $paddsub_amount,
				'addsub_value'    => $paddsub_value,
				'async_rar'       => $pasync_rar,
			]);

			// check if new rates should be calculated (increase/decrease) or immediately applied (fixed)
			if ($prate_type === 'addsub') {
				// increase/decrease rates
				$new_rates = $model->increaseDecreaseRoomRates();
			} else {
				// apply the new (fixed) rate/restrictions
				$new_rates = $model->modifyRateRestrictions();
			}
		} catch (Throwable $e) {
			// propagate the error in the format accepted by the AJAX request
			VBOHttpDocument::getInstance()->close(200, 'e4j.error.' . addslashes($e->getMessage()));
		}

		// calculate date values
		$start_ts = strtotime($pfromdate);
		$end_ts = strtotime($ptodate);

		if (!$pvcm) {
			// update session values
			$updforvcm['count'] = !empty($updforvcm['count']) ? ($updforvcm['count'] + 1) : 1;
			if (!empty($updforvcm['dfrom'])) {
				$updforvcm['dfrom'] = $updforvcm['dfrom'] > $start_ts ? $start_ts : $updforvcm['dfrom'];
			} else {
				$updforvcm['dfrom'] = $start_ts;
			}
			if (!empty($updforvcm['dto'])) {
				$updforvcm['dto'] = $updforvcm['dto'] < $end_ts ? $end_ts : $updforvcm['dto'];
			} else {
				$updforvcm['dto'] = $end_ts;
			}
			if (is_array(($updforvcm['rooms'] ?? null))) {
				if (!in_array($pid_room, $updforvcm['rooms'])) {
					$updforvcm['rooms'][] = $pid_room;
				}
			} else {
				$updforvcm['rooms'] = [$pid_room];
			}
			if (is_array(($updforvcm['rplans'] ?? null))) {
				if (array_key_exists($pid_room, $updforvcm['rplans'])) {
					if (!in_array($pid_price_main, $updforvcm['rplans'][$pid_room])) {
						$updforvcm['rplans'][$pid_room][] = $pid_price_main;
					}
				} else {
					$updforvcm['rplans'][$pid_room] = [$pid_price_main];
				}
			} else {
				$updforvcm['rplans'] = [
					$pid_room => [$pid_price_main]
				];
			}
		}

		// update session no matter what
		$session->set('vbVcmRatesUpd', $updforvcm);

		// send the JSON response to output
		VBOHttpDocument::getInstance()->json($new_rates);
	}

	/**
	 * AJAX endpoint for for loading the channel alteration rules for a given room and rate.
	 * 
	 * @since 	1.17.2 (J) - 1.7.2 (WP)
	 */
	public function loadOtaAlterationRules()
	{
		$app = JFactory::getApplication();

		if (!JSession::checkToken()) {
			// missing CSRF-proof token
			VBOHttpDocument::getInstance($app)->close(403, JText::_('JINVALID_TOKEN'));
		}

		$room_id = $app->input->getUInt('room_id', 0);
		$rate_id = $app->input->getUInt('rate_id', 0);

		if (!$room_id || !$rate_id) {
			VBOHttpDocument::getInstance($app)->close(400, 'Missing required parameters.');
		}

		if (!class_exists('VikChannelManager')) {
			VBOHttpDocument::getInstance($app)->close(500, 'Channel Manager not available.');
		}

		// access the bulk rates cache data
		$bulk_rates_cache = VikChannelManager::getBulkRatesCache();

		// get the requested room-rate cache
		$room_rate_cache = $bulk_rates_cache[$room_id][$rate_id] ?? [];

		// build the PMS default currency data list (symbol and ISO name)
		$default_currency_data = [
			VikBooking::getCurrencySymb(),
			VikBooking::getCurrencyName(),
			VikBooking::getCurrencyCodePp(),
		];

		// check if some channels are using a different currency
		$ota_currencies = [];
		foreach (($room_rate_cache['cur_rplans'] ?? []) as $ota_currency) {
			if (!in_array($ota_currency, $ota_currencies)) {
				$ota_currencies[] = trim($ota_currency);
			}
		}

		// filter empty currencies
		$ota_currencies = array_filter($ota_currencies);

		// get OTAs different currencies than the default one, if any
		$ota_custom_currencies = array_diff($ota_currencies, $default_currency_data);

		if ($ota_custom_currencies) {
			// attempt to build the currency options for each custom currency
			$custom_currency_options = [];

			// import currency converter class
			VikBooking::import('currencyconverter');

			// get an instance of the currency converter object
			$converter = new VboCurrencyConverter('EUR', 'USD', [1], explode(':', VikBooking::getNumberFormatData()));

			foreach ($ota_custom_currencies as $custom_currency) {
				// check if the currency name is known
				$ota_currency_data = $converter->getCurrencyData($custom_currency);

				if ($ota_currency_data) {
					// push currency data options
					$custom_currency_options[$custom_currency] = $ota_currency_data;
				}
			}

			// inject the currency data options to the room rate cache
			$room_rate_cache['currency_data_options'] = $custom_currency_options;
		}

		VBOHttpDocument::getInstance($app)->json($room_rate_cache);
	}

	/**
	 * AJAX endpoint for for loading the rates for a given range of dates and room(s).
	 * 
	 * @since 	1.18.0 (J) - 1.8.0 (WP)
	 */
	public function loadRoomRates()
	{
		$app = JFactory::getApplication();

		if (!JSession::checkToken()) {
			// missing CSRF-proof token
			VBOHttpDocument::getInstance($app)->close(403, JText::_('JINVALID_TOKEN'));
		}

		$room_id = $app->input->getUInt('room_id', 0);
		$room_ids = (array) $app->input->getUInt('room_ids', []);
		$from_date = $app->input->getString('from_date', date('Y-m-01'));
		$to_date = $app->input->getString('to_date', date('Y-m-t'));
		$restrictions = $app->input->getBool('restrictions', true);

		if ((!$room_id && !$room_ids) || empty($from_date) || empty($to_date)) {
			VBOHttpDocument::getInstance($app)->close(400, 'Missing required parameters.');
		}

		if (!class_exists('VikChannelManager')) {
			VBOHttpDocument::getInstance($app)->close(500, 'Channel Manager not available.');
		}

		try {
			// fetch room rates and restrictions
			$room_rates = VBOModelPricing::getInstance()->getRoomRates([
				'id_room'      => $room_id,
				'id_rooms'     => $room_ids,
				'from_date'    => $from_date,
				'to_date'      => $to_date,
				'restrictions' => $restrictions,
			]);
		} catch (Exception $e) {
			// propagate the error
			VBOHttpDocument::getInstance($app)->close($e->getCode(), $e->getMessage());
		}

		// send response to output
		VBOHttpDocument::getInstance($app)->json($room_rates);
	}

	/**
	 * AJAX endpoint for for loading the OTA rate records for a list of rooms and dates.
	 * 
	 * @since 	1.18.6 (J) - 1.8.6 (WP)
	 */
	public function loadOtaRateRecords()
	{
		$app = JFactory::getApplication();

		if (!JSession::checkToken()) {
			// missing CSRF-proof token
			VBOHttpDocument::getInstance($app)->close(403, JText::_('JINVALID_TOKEN'));
		}

		$room_ids = (array) $app->input->getUInt('room_ids', []);
		$from_date = $app->input->getString('from_date', '');
		$to_date = $app->input->getString('to_date', '');
		$days = $app->input->getUInt('days', 30);

		if (!$room_ids || empty($from_date) || (empty($to_date) && empty($days))) {
			VBOHttpDocument::getInstance($app)->close(400, 'Missing required parameters.');
		}

		if (!class_exists('VikChannelManager')) {
			VBOHttpDocument::getInstance($app)->close(500, 'Channel Manager not available.');
		}

		try {
			// fetch OTA rate records and data
			$ota_rates_data = (new VBORmsRatesRegistry([
				'id_rooms'  => $room_ids,
				'from_date' => $from_date,
				'to_date'   => $to_date,
				'days'      => $days,
			]))->loadOtaFlowRecords();
		} catch (Exception $e) {
			// propagate the error
			VBOHttpDocument::getInstance($app)->close($e->getCode(), $e->getMessage());
		}

		// send response to output
		VBOHttpDocument::getInstance($app)->json($ota_rates_data);
	}
}
