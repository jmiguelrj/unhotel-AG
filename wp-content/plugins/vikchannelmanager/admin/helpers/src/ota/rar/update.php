<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2024 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * RAR update helper. Used to encapsulate and execute one
 * RAR update request towards the OTAs and/or VikBooking.
 * 
 * @since 	1.8.24
 */
final class VCMOtaRarUpdate extends JObject
{
	/**
	 * The singleton instance of the class.
	 *
	 * @var  VCMOtaRarUpdate
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
	 * Lets the RAR update request execute to modify or set room rates on
	 * VBO/OTAs with Min/Max LOS, CTA, CTD and room rates for a range of dates.
	 * 
	 * @return 	array 	to be used with list() returns the execution results.
	 * 
	 * @throws 	Exception
	 */
	public function execute()
	{
		$dbo = JFactory::getDbo();

		// gather the properties with which the object should be constructed

		$room_id		= (int) $this->get('room_id', 0);
		$rates_data 	= (array) $this->get('rates_data', []);
		$date_from 		= $this->get('date_from', '');
		$date_to 		= $this->get('date_to', '');
		$minlos			= (int) $this->get('minlos', 0);
		$maxlos			= (int) $this->get('maxlos', 0);
		$upd_vbo		= (int) $this->get('upd_vbo', 1);
		$upd_otas		= (int) $this->get('upd_otas', 0);
		$cta 			= $this->get('cta', null);
		$ctd 			= $this->get('ctd', null);
		$cta_wdays 		= (array) $this->get('cta_wdays', []);
		$ctd_wdays 		= (array) $this->get('ctd_wdays', []);

		// where rates should be updated
		if ($upd_vbo < 1 && $upd_otas < 1) {
			throw new Exception(JText::_('VCMAPPRQINVALID'), 400);
		}

		// if the date is not formatted correctly, an error is sent and false is returned
		if (count(explode('-', $date_from)) != 3) {
			throw new Exception(JText::_('VCMAPPINVALIDDATE'), 400);
		}

		$info_from = getdate(strtotime($date_from));

		if (empty($date_to)) {
			// if empty date_to, set it to the same day as date_from
			$date_to = $date_from;
		}
		$info_to = getdate(strtotime($date_to));

		// dates_to must be after or equal to date_from and cannot be in the past
		$today_midnight = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
		if ($info_from[0] > $info_to[0] || $info_to[0] < $today_midnight) {
			throw new Exception(JText::_('VCMAPPINVALIDDATE'), 400);
		}

		// load room details
		$dbo->setQuery(
			$dbo->getQuery(true)
				->select($dbo->qn(['id', 'name', 'units']))
				->from($dbo->qn('#__vikbooking_rooms'))
				->where($dbo->qn('id') . ' = ' . $room_id)
		);
		$room_data = $dbo->loadAssoc();

		if (!$room_data) {
			throw new Exception(JText::_('VCMAPPNOROOMFOUND'), 404);
		}

		// read the room rates for the lowest number of nights

		// build inner join sub-query
		$innerq = $dbo->getQuery(true)
			->select('MIN(' . $dbo->qn('days') . ') AS ' . $dbo->qn('min_days'))
			->from($dbo->qn('#__vikbooking_dispcost'))
			->where($dbo->qn('idroom') . ' = ' . $room_id)
			->group($dbo->qn('idroom'));

		// build main query
		$q = $dbo->getQuery(true)
			->select($dbo->qn([
				'r.id',
				'r.idroom',
				'r.days',
				'r.idprice',
				'r.cost',
				'p.name',
			]))
			->from($dbo->qn('#__vikbooking_dispcost', 'r'))
			->innerJoin('(' . $innerq . ') AS ' . $dbo->qn('r2') . ' ON ' . $dbo->qn('r.days') . ' = ' . $dbo->qn('r2.min_days'))
			->leftJoin($dbo->qn('#__vikbooking_prices', 'p') . ' ON ' . $dbo->qn('p.id') . ' = ' . $dbo->qn('r.idprice'))
			->where($dbo->qn('r.idroom') . ' = ' . $room_id)
			->group($dbo->qn('r.id'))
			->group($dbo->qn('r.idroom'))
			->group($dbo->qn('r.days'))
			->group($dbo->qn('r.idprice'))
			->group($dbo->qn('r.cost'))
			->group($dbo->qn('p.name'))
			->order($dbo->qn('r.days') . ' ASC')
			->order($dbo->qn('r.cost') . ' ASC');

		$dbo->setQuery($q);
		$lowest_nightly_rates = $dbo->loadAssocList();

		// build an associative list of room rates
		$roomrates = [];
		foreach ($lowest_nightly_rates as $rrv) {
			$roomrates[$rrv['idprice']] = $rrv;
			$roomrates[$rrv['idprice']]['cost'] = round(($rrv['cost'] / $rrv['days']), 2);
			$roomrates[$rrv['idprice']]['days'] = 1;
		}

		if (!$roomrates) {
			throw new Exception(JText::_('VCMAPPNORATESFOUND'), 404);
		}

		// check rates_data sent to make sure all rate plans exist
		foreach ($rates_data as $rk => $rd) {
			if (!isset($rd['rate_id']) || !isset($rd['cost']) || !array_key_exists((int)$rd['rate_id'], $roomrates) || (float)$rd['cost'] <= 0) {
				// this rate plan ID is invalid, terminate the whole request
				throw new Exception(JText::_('VCMAPPNORATESFOUND'), 404);
			}

			// typecast rate plan ID and amount
			$rates_data[$rk]['rate_id'] = (int)$rd['rate_id'];
			$rates_data[$rk]['cost'] 	= (float)$rd['cost'];
		}

		if (!$rates_data) {
			throw new Exception(JText::_('VCMAPPRQEMPTY'), 502);
		}

		// get the mapped channels for this room
		$dbo->setQuery(
			$dbo->getQuery(true)
				->select('*')
				->from($dbo->qn('#__vikchannelmanager_roomsxref'))
				->where($dbo->qn('idroomvb') . ' = ' . $room_id)
		);
		$channels_data = $dbo->loadAssocList();

		// set the available channels list
		$room_data['channels'] = [];
		foreach ($channels_data as $ch_data) {
			$room_data['channels'][$ch_data['idchannel']] = $ch_data;
		}

		if (!$room_data['channels'] && $upd_otas > 0 && $upd_vbo < 1) {
			// no channels found for this room and the request was made for updating only the channels
			throw new Exception(JText::_('VCMAPPNOROOMCHANNELS'), 500);
		}

		// load the 'Bulk Action - Rates Upload' cache
		$bulk_rates_cache = VikChannelManager::getBulkRatesCache();
		$update_otas = ($upd_otas > 0 && $room_data['channels']);

		// check for CTA/CTD rules
		$cta_string = '';
		$ctd_string = '';
		if (is_bool($cta) || is_bool($ctd)) {
			$cta_wdays = [];
			$ctd_wdays = [];
			$start_ts_info = $info_from;
			while ($start_ts_info[0] <= $info_to[0]) {
				if ($cta === true && !in_array($start_ts_info['wday'], $cta_wdays)) {
					$cta_wdays[] = $start_ts_info['wday'];
				}
				if ($ctd === true && !in_array($start_ts_info['wday'], $ctd_wdays)) {
					$ctd_wdays[] = $start_ts_info['wday'];
				}
				$start_ts_info = getdate(mktime(0, 0, 0, $start_ts_info['mon'], ($start_ts_info['mday'] + 1), $start_ts_info['year']));
				if (count($cta_wdays) >= 7 || count($ctd_wdays) >= 7) {
					break;
				}
			}
		}

		if ($cta_wdays || $ctd_wdays) {
			// ensure all week-day values are correct
			$cta_wdays = array_filter($cta_wdays, function($w) {
				return ((int)$w >= 0 && (int)$w <= 6);
			});

			$ctd_wdays = array_filter($ctd_wdays, function($w) {
				return ((int)$w >= 0 && (int)$w <= 6);
			});

			// sort week day values in ascending order
			sort($cta_wdays);
			sort($ctd_wdays);

			if ($cta_wdays) {
				$cta_string = 'CTA[' . implode(',', $cta_wdays) . ']';
			}
			if ($ctd_wdays) {
				$ctd_string = 'CTD[' . implode(',', $ctd_wdays) . ']';
			}
		}

		// build the array with the update details
		$update_rows = [];
		$channels_rates_ovr = [];
		foreach ($rates_data as $rk => $rd) {
			$node = $room_data;
			$setminlos = $minlos >= 1 ? $minlos : '';
			$setmaxlos = $maxlos > 0 ? $maxlos : '';

			// check bulk rates cache to see if the exact rate should be increased for the channels
			// we cannot alter the cost at this point because the new rate still has to be set in VBO. So we use the array $channels_rates_ovr as map.
			if (isset($bulk_rates_cache[$room_id]) && isset($bulk_rates_cache[$room_id][$rd['rate_id']])) {
				if ((int)$bulk_rates_cache[$room_id][$rd['rate_id']]['rmod'] > 0 && (float)$bulk_rates_cache[$room_id][$rd['rate_id']]['rmodamount'] > 0) {
					if ((int)$bulk_rates_cache[$room_id][$rd['rate_id']]['rmodop'] > 0) {
						// increase rates
						if ((int)$bulk_rates_cache[$room_id][$rd['rate_id']]['rmodval'] > 0) {
							// percentage charge
							$channels_rates_ovr[$rk] = $rd['cost'] * (100 + (float)$bulk_rates_cache[$room_id][$rd['rate_id']]['rmodamount']) / 100;
						} else {
							// fixed charge
							$channels_rates_ovr[$rk] = $rd['cost'] + (float)$bulk_rates_cache[$room_id][$rd['rate_id']]['rmodamount'];
						}
					} else {
						// lower rates
						if ((int)$bulk_rates_cache[$room_id][$rd['rate_id']]['rmodval'] > 0) {
							// percentage discount
							$disc_op = $rd['cost'] * (float)$bulk_rates_cache[$room_id][$rd['rate_id']]['rmodamount'] / 100;
							$channels_rates_ovr[$rk] = $rd['cost'] - $disc_op;
						} else {
							// fixed discount
							$channels_rates_ovr[$rk] = $rd['cost'] - (float)$bulk_rates_cache[$room_id][$rd['rate_id']]['rmodamount'];
						}
					}
				}
			}

			$node['ratesinventory'] = [
				date('Y-m-d', $info_from[0]).'_'.date('Y-m-d', $info_to[0]).'_'.$setminlos.$cta_string.$ctd_string.'_'.$setmaxlos.'_1_2_'.$rd['cost'].'_0'
			];
			$node['pushdata'] = [
				'pricetype'    => $rd['rate_id'],
				'defrate' 	   => $roomrates[$rd['rate_id']]['cost'],
				'rplans' 	   => [],
				'cur_rplans'   => [],
				'rplanarimode' => [],
			];

			if ($update_otas) {
				// build push data for each channel rate plan according to the Bulk Rates Cache or to the OTA Pricing
				if (isset($bulk_rates_cache[$room_id]) && isset($bulk_rates_cache[$room_id][$rd['rate_id']])) {
					// Bulk Rates Cache available for this room_id and rate_id
					$node['pushdata']['rplans'] = $bulk_rates_cache[$room_id][$rd['rate_id']]['rplans'];
					$node['pushdata']['cur_rplans'] = $bulk_rates_cache[$room_id][$rd['rate_id']]['cur_rplans'];
					$node['pushdata']['rplanarimode'] = $bulk_rates_cache[$room_id][$rd['rate_id']]['rplanarimode'];
				}
				// check the channels mapped for this room and add what was not found in the Bulk Rates Cache, if anything
				foreach ($node['channels'] as $idchannel => $ch_data) {
					if (!isset($node['pushdata']['rplans'][$idchannel])) {
						// this channel was not found in the Bulk Rates Cache. Read data from OTA Pricing
						$otapricing = json_decode($ch_data['otapricing'], true);
						$ch_rplan_id = '';
						if (is_array($otapricing) && isset($otapricing['RatePlan'])) {
							foreach ($otapricing['RatePlan'] as $rpkey => $rpv) {
								// get the first key (rate plan ID) of the RatePlan array from OTA Pricing
								$ch_rplan_id = $rpkey;
								break;
							}
						}
						if (empty($ch_rplan_id)) {
							unset($node['channels'][$idchannel]);
							continue;
						}
						//set channel rate plan data
						$node['pushdata']['rplans'][$idchannel] = $ch_rplan_id;
						if ($idchannel == (int)VikChannelManagerConfig::BOOKING) {
							//Default Pricing is used by default, when no data available
							$node['pushdata']['rplanarimode'][$idchannel] = 'person';
						}
					}
				}
			}

			// add update node
			array_push($update_rows, $node);
		}

		// invoke the connector for any update request
		$vboConnector = VikChannelManager::getVikBookingConnectorInstance();

		// check if the caller should be set
		if ($connector_caller = $this->getCaller()) {
			$vboConnector->caller = $connector_caller;
		}

		// check if the API user should be set
		if ($connector_user = $this->getApiUser()) {
			$vboConnector->apiUser = $connector_user;
		}

		// update rates on the website first
		if ($upd_vbo > 0) {
			foreach ($update_rows as $update_row) {
				foreach ($update_row['ratesinventory'] as $op_info) {
					list($fromd, $tod, $minlos, $maxlos, $rmod, $rmodop, $rmodamount, $rmodval) = explode('_', $op_info);
					$is_restr = ((strlen($minlos) && (strpos($minlos, 'CT') !== false || (int)$minlos >= 1)) || strlen($maxlos));
					$is_exactrate = (intval($rmodop) == 2 && intval($rmodamount) > 0);
					if ($is_restr) {
						$vboConnector->createRestriction($fromd, $tod, array($room_id), array($minlos, $maxlos));
					}
					if ($is_exactrate) {
						$vboConnector->setNewRate($fromd, $tod, $room_id, $update_row['pushdata']['pricetype'], $rmodamount);
					}
				}
			}
			if ($vc_error = $vboConnector->getError(true)) {
				// some errors occurred while updating the rates no the website. Terminate the request with an error
				throw new Exception(JText::sprintf('VCMAPPVBOMODRATESERR', $vc_error), 500);
			}
		}

		// update rates on the various channels
		$channels_map 	   = [];
		$channels_updated  = [];
		$channels_success  = [];
		$channels_warnings = [];
		$channels_errors   = [];
		if ($update_otas) {
			foreach ($update_rows as $kupd => $update_row) {
				if (!$update_row['channels']) {
					continue;
				}
				if (!$channels_updated) {
					foreach ($update_row['channels'] as $ch) {
						$channels_map[$ch['idchannel']] = ucfirst($ch['channel']);
						array_push($channels_updated, [
							'id'   => $ch['idchannel'],
							'name' => ucfirst($ch['channel']),
						]);
					}
				}

				// prepare request data
				$channels_ids = array_keys($update_row['channels']);
				$channels_rplans = [];
				foreach ($channels_ids as $ch_id) {
					$ch_rplan  = isset($update_row['pushdata']['rplans'][$ch_id]) ? $update_row['pushdata']['rplans'][$ch_id] : '';
					$ch_rplan .= isset($update_row['pushdata']['rplanarimode'][$ch_id]) ? '=' . $update_row['pushdata']['rplanarimode'][$ch_id] : '';
					$ch_rplan .= isset($update_row['pushdata']['cur_rplans'][$ch_id]) && !empty($update_row['pushdata']['cur_rplans'][$ch_id]) ? ':' . $update_row['pushdata']['cur_rplans'][$ch_id] : '';
					$channels_rplans[] = $ch_rplan;
				}
				$channels = [
					implode(',', $channels_ids)
				];
				$chrplans = [
					implode(',', $channels_rplans)
				];

				// check if the exact rate for the channels should be modified according to the Bulk Rates Cache rules
				if (isset($channels_rates_ovr[$kupd]) && $channels_rates_ovr[$kupd] > 0) {
					foreach ($update_row['ratesinventory'] as $rik => $riv) {
						//this array should always have one string only inside (count = 1). It's $update_rows that has a count equal to the number of rate plans to update.
						list($fromd, $tod, $minlos, $maxlos, $rmod, $rmodop, $rmodamount, $rmodval) = explode('_', trim($riv));
						$update_row['ratesinventory'][$rik] = implode('_', array(
							$fromd, $tod, $minlos, $maxlos, $rmod, $rmodop, $channels_rates_ovr[$kupd], $rmodval
						));
					}
				}

				// set nodes, push vars and rooms involved
				$nodes = [
					implode(';', $update_row['ratesinventory'])
				];
				$pushvars = [
					implode(';', [$update_row['pushdata']['pricetype'], $update_row['pushdata']['defrate']])
				];
				$rooms = [$room_id];

				// send the request
				$result = $vboConnector->channelsRatesPush($channels, $chrplans, $nodes, $rooms, $pushvars);
				if ($vc_error = $vboConnector->getError(true)) {
					$channels_errors[] = $vc_error;
					continue;
				}

				// parse the channels update result and compose success, warnings, errors
				$result_pool = json_decode($result, true);
				$result_pool = is_array($result_pool) ? $result_pool : [];
				foreach ($result_pool as $rid => $ch_responses) {
					foreach ($ch_responses as $ch_id => $ch_res) {
						if ($ch_id == 'breakdown' || !is_numeric($ch_id)) {
							// skip the rates/dates breakdown for the API requests
							continue;
						}
						$ch_id = (int)$ch_id;
						if (substr($ch_res, 0, 6) == 'e4j.OK') {
							// success
							if (!isset($channels_success[$ch_id])) {
								$channels_success[$ch_id] = $channels_map[$ch_id];
							}
						} elseif (substr($ch_res, 0, 11) == 'e4j.warning') {
							// warning
							if (!isset($channels_warnings[$ch_id])) {
								$channels_warnings[$ch_id] = $channels_map[$ch_id] . ': ' . str_replace('e4j.warning.', '', $ch_res);
							} else {
								$channels_warnings[$ch_id] .= "\n" . str_replace('e4j.warning.', '', $ch_res);
							}
							// add the channel also to the successful list in case of Warning
							if (!isset($channels_success[$ch_id])) {
								$channels_success[$ch_id] = $channels_map[$ch_id];
							}
						} elseif (substr($ch_res, 0, 9) == 'e4j.error') {
							// error
							if (!isset($channels_errors[$ch_id])) {
								$channels_errors[$ch_id] = $channels_map[$ch_id] . ': ' . str_replace('e4j.error.', '', $ch_res);
							} else {
								$channels_errors[$ch_id] .= "\n" . str_replace('e4j.error.', '', $ch_res);
							}
						}
					}
				}
			}
		}

		// return the list of array results
		return [
			$channels_updated,
			$channels_success,
			$channels_warnings,
			$channels_errors,
		];
	}

	/**
	 * Sets the caller of the Connector.
	 * 
	 * @param 	string 	$caller 	the caller of the Connector.
	 * 
	 * @return 	self
	 */
	public function setCaller($caller)
	{
		$this->set('_caller', $caller);

		return $this;
	}

	/**
	 * Gets the caller of the Connector.
	 * 
	 * @return 	string
	 */
	public function getCaller()
	{
		return $this->get('_caller', '');
	}

	/**
	 * Sets the API user for the Connector.
	 * 
	 * @param 	string 	$user 	the API user for the Connector.
	 * 
	 * @return 	self
	 */
	public function setApiUser($user)
	{
		$this->set('_apiUser', $user);

		return $this;
	}

	/**
	 * Gets the API user for the Connector.
	 * 
	 * @return 	string
	 */
	public function getApiUser()
	{
		return $this->get('_apiUser', '');
	}
}