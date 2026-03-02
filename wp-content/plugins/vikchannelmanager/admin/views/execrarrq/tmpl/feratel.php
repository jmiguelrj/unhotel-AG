<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

// No direct access to this file
defined('ABSPATH') or die('No script kiddies please!');

$config = $this->config;
$vbrooms = $this->vbrooms;
$comparison = $this->comparison;
$currencysymb = $this->currencysymb;
$rars = $this->rars;
$currency = VikChannelManager::getCurrencyName(true);

$inventory_loaded = array_key_exists('NoInventory', $rars) ? false : true;

$vik = new VikApplication(VersionListener::getID());

$ota_rooms = array();
foreach ($vbrooms as $rxref) {
	$ota_rooms[$rxref['idroomota']][] = $rxref;
}
?>
<table cellpadding="4" cellspacing="0" border="0" width="100%" class="<?php echo $vik->getAdminTableClass(); ?> vcm-list-table">
<?php echo $vik->openTableHead(); ?>
	<tr>
		<th width="20">
			<?php echo $vik->getAdminToggle((isset($rars['AvailRate']) ? count($rars['AvailRate']) : 1)); ?>
		</th>
		<th class="title" width="100"><?php echo JText::_('VCMRARDATE'); ?></th>
		<th class="title" width="100"><?php echo JText::_('VCMOTAROOMTYPE'); ?></th>
		<th class="title center" width="40" align="center"><?php echo JText::_('VCMRAROPEN'); ?></th>
		<th class="title center" width="75" align="center"><?php echo JText::_('VCMRARINVENTORY'); ?></th>
		<th class="title left" width="250"><?php echo JText::_('VCMRARRATEPLAN'); ?></th>
		<th class="title left" width="200"><?php echo JText::_('VCMRARRESTRICTIONS'); ?></th>
	</tr>
<?php echo $vik->closeTableHead(); ?>
<?php
$k = 0;
$i = 0;
$max_rooms_found = 0;
$max_rateplans_found = 0;
foreach ($rars['AvailRate'] as $day => $rooms) {
	$day_rooms = count($rooms);
	$max_rooms_found = $day_rooms > $max_rooms_found ? $day_rooms : $max_rooms_found;
	$ota_rate_plan = !empty($ota_rooms[$rooms[0]['id']][key($ota_rooms[$rooms[0]['id']])]['otapricing']) ? json_decode($ota_rooms[$rooms[0]['id']][key($ota_rooms[$rooms[0]['id']])]['otapricing'], true) : array();
	$inv_details = '';
	foreach ($rooms[0]['Inventory'] as $inv_type => $inv_val) {
		$inv_details .= '- '.$inv_type.': '.$inv_val.'&lt;br/&gt;';
	}
	?>
	<tr class="row<?php echo $k; ?>">
		<td rowspan="<?php echo $day_rooms; ?>"><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $day; ?>" class="vcm-rar-ckb" onClick="<?php echo $vik->checkboxOnClick(); ?>"><span id="date<?php echo $day; ?>"></span></td>
		<td rowspan="<?php echo $day_rooms; ?>"><div class="vcmrardate-box"><span class="vcmrardate"><?php echo $day; ?></span></div></td>
		<td><div class="vcmrar-room-box"><span class="vcmshowtip vcmistip" title="ID <?php echo $rooms[0]['id']; ?>"><?php echo $ota_rooms[$rooms[0]['id']][0]['otaroomname']; ?></span></div></td>
		<td class="center" align="center"><img src="<?php echo VCM_ADMIN_URI; ?>assets/css/images/<?php echo $rooms[0]['closed'] == 'true' ? 'disabled' : 'enabled'; ?>.png" class="imgtoggle" style="cursor: default;" /><div class="vcmrar-newroomstatus" id="divroomstatus<?php echo $day.$rooms[0]['id']; ?>"><input type="hidden" name="<?php echo 'roomstatus_'.$day.'_'.$rooms[0]['id']; ?>" value="" id="roomstatus<?php echo $day.$rooms[0]['id']; ?>"/></div></td>
		<td class="center" align="center">
			<span class="vcmrarinventorysp"><span class="vcmshowtip" title="<?php echo $inv_details; ?>"><?php echo JText::_('VCMTOTINVAVAILABLE'); ?></span> <input type="number" min="0" name="<?php echo 'inv_'.$day.'_'.$rooms[0]['id']; ?>" value="<?php echo $rooms[0]['Inventory']['units_remaining']; ?>" size="3"/></span>
	<?php
	if (@count($comparison[$day][$rooms[0]['id']]) > 0) {
		?>
			<div class="vcm-comparison vcm-compare-units">
				<span class="vcm-compare-ibelab"><?php echo JText::_('VCMCOMPONIBE'); ?></span>
				<span class="vcm-compare-ibecircle vcm-compare-ibecircleavail"><?php echo $comparison[$day][$rooms[0]['id']]['unitsavail']; ?></span>
				<span class="vcm-compare-ibeoflab"><?php echo JText::_('VCMCOMPONIBEOF'); ?></span>
				<span class="vcm-compare-ibecircle"><?php echo $comparison[$day][$rooms[0]['id']]['units']; ?></span>
			</div>
		<?php
	}
	?>
		</td>
		<?php
		$rate_plans = '';
		$restrictions = array();
		$tot_rate_plans = count($rooms[0]['RatePlan']);
		$max_rateplans_found = $tot_rate_plans > $max_rateplans_found ? $tot_rate_plans : $max_rateplans_found;
		if ($tot_rate_plans > 0) {
			foreach ($rooms[0]['RatePlan'] as $rateplan) {
				$rate_plans .= '<div class="vcmrar-rateplan">'."\n";
				$rate_plan_tip = '';
				if (array_key_exists($rateplan['id'], $ota_rate_plan['RatePlan'])) {
					foreach ($ota_rate_plan['RatePlan'][$rateplan['id']] as $rpkey => $rpval) {
						if ($rpkey == 'id') {
							continue;
						}
						$rate_plan_tip .= ucwords($rpkey).': '.$rpval."&lt;br/&gt;";
					}
				}
				$rate_plans .= '<span class="'.(!empty($rate_plan_tip) ? 'vcmshowtip ' : '').'vcmrateplansp vcmrateplanon" id="rateplanstatus'.$day.$rateplan['id'].'" title="'.$rate_plan_tip.'">'.(array_key_exists($rateplan['id'], $ota_rate_plan['RatePlan']) ? $ota_rate_plan['RatePlan'][$rateplan['id']]['name'] : 'Rates').'</span><span class="vcmrar-spacer"></span><input type="hidden" name="rateplanstatus'.$day.$rateplan['id'].'" value="" id="inprateplanstatus'.$day.$rateplan['id'].'"/>'."\n";
				if (array_key_exists('Restrictions', $rateplan) && count($rateplan['Restrictions']) > 0) {
					$restrictions[$rateplan['id']] = $rateplan['Restrictions'];
					if (array_key_exists('@attributes', $rateplan['Restrictions']) && count($rateplan['Restrictions']) == 1) {
						$restrictions[$rateplan['id']] = $rateplan['Restrictions']['@attributes'];
					}
				}
				if (array_key_exists('Rate', $rateplan) && isset($ota_rate_plan['RatePlan'][$rateplan['id']])) {
					// since we never obtain information about rates from the PAR_RQ, we build the prices for this rate plan by reading the mix and max occupancy of the room
					$occ_range = range((int)$ota_rate_plan['RatePlan'][$rateplan['id']]['min_occupancy'], (int)$ota_rate_plan['RatePlan'][$rateplan['id']]['max_occupancy']);
					$rate_plans .= '<div class="vcmrar-rplan-leftblock">'."\n";
					$currency = $inventory_loaded === true && array_key_exists('currency', $rateplan['Rate']) ? $rateplan['Rate']['currency'] : $currency;
					foreach ($occ_range as $occ) {
						$rate_plans .= '<span class="vcmrarratesp">'.JText::sprintf('VCMRARRATEPEROCCUPANCY', $occ).' <span class="vcmrarcurrency">'.$currency.'</span> <input type="text" size="5" name="rateplan_'.$day.'_'.$rooms[0]['id'].'_'.$rateplan['id'].'_'.$occ.'" value=""/></span>'."\n";
					}
					$rate_plans .= '</div>'."\n";
					//Comparison with IBE
					if (@count($comparison[$day][$rooms[0]['id']]) > 0) {
						//build tabs for each type of price
						$tp_tabs = array();
						foreach ($comparison[$day][$rooms[0]['id']] as $nights => $prices) {
							if (is_numeric($nights)) {
								foreach ($prices as $fare) {
									$tp_tabs[$fare['idprice']] = $fare;
								}
							}
						}
						//
						if (count($tp_tabs) > 0) {
							$rate_plans .= '<div class="vcmrar-rplan-rightblock vcm-comparison">'."\n";
							$rate_plans .= '<div class="vcm-compare-rates">'."\n";
							$tk = 0;
							foreach ($tp_tabs as $tpid => $fare) {
								$tk++;
								$tp_tab_class = $tk == 1 ? ' vcm-compare-ratetab-active' : '';
								$rate_plans .= '<div class="vcm-compare-ratetab'.$tp_tab_class.'" id="'.$day.'-'.$rooms[0]['id'].'-'.$tpid.'-'.$rateplan['id'].'"><a href="javascript: void(0);">'.(strlen($fare['name']) > 9 ? substr($fare['name'], 0, 9).'.' : $fare['name']).'</a></div>'."\n";
							}
							$tk = 0;
							foreach ($tp_tabs as $tpid => $fare) {
								$tk++;
								$tp_col_class = $tk == 1 ? ' vcm-compare-pricecols-active' : '';
								$n = 0;
								$rate_plans .= '<div class="vcm-compare-pricecols'.$tp_col_class.' '.$day.'-'.$rooms[0]['id'].'-'.$tpid.'-'.$rateplan['id'].'">'."\n";
								foreach ($rateplan['Rate'] as $occkey => $rate) {
									$occ_parts = explode('_', $occkey);
									$occ = $occ_parts[1];
									$n = 1;
									foreach ($comparison[$day][$rooms[0]['id']] as $nights => $prices) {
										if (is_numeric($nights)) {
											foreach ($prices as $price) {
												if ((int)$nights == (int)$n && $tpid == $price['idprice']) {
													if ($occ > 0) {
														$diffusageprice = VikBooking::loadAdultsDiff($price['idroom'], $occ);
														if (is_array($diffusageprice)) {
															if ($diffusageprice['chdisc'] == 1) {
																//Charge
																if ($diffusageprice['valpcent'] == 1) {
																	//fixed value
																	$aduseval = $diffusageprice['pernight'] == 1 ? $diffusageprice['value'] * $price['days'] : $diffusageprice['value'];
																	$price['cost'] += $aduseval;
																} else {
																	//percentage value
																	$aduseval = $diffusageprice['pernight'] == 1 ? round(($price['cost'] * $diffusageprice['value'] / 100) * $price['days'], 2) : round(($price['cost'] * $diffusageprice['value'] / 100), 2);
																	$price['cost'] += $aduseval;
																}
															} else {
																//Discount
																if ($diffusageprice['valpcent'] == 1) {
																	//fixed value
																	$aduseval = $diffusageprice['pernight'] == 1 ? $diffusageprice['value'] * $price['days'] : $diffusageprice['value'];
																	$price['cost'] -= $aduseval;
																} else {
																	//percentage value
																	$aduseval = $diffusageprice['pernight'] == 1 ? round(($price['cost'] * $diffusageprice['value'] / 100) * $price['days'], 2) : round(($price['cost'] * $diffusageprice['value'] / 100), 2);
																	$price['cost'] -= $aduseval;
																}
															}
														}
													}
													$rate_plans .= '<div class="vcm-compare-rates-roomcost"><span class="vcm-compare-rates-copycost" id="'.$day.'_'.$rooms[0]['id'].'_'.$rateplan['id'].'_'.$kr.'" title="'.JText::_('VCMRARCOPYPRICE').'"></span><span class="vcm-compare-pricefornights">'.$occ.' - '.JText::sprintf('VCMRARCOMPNUMNIGHTS', $price['days']).'</span><span class="vcm-compare-pricecurrency">'.$currencysymb.'</span><span class="vcm-compare-pricebox">'.number_format($price['cost'], 2, '.', '').'</span></div>'."\n";
												}
											}
										}
									}
								}
								$rate_plans .= '</div>'."\n";
							}
							$rate_plans .= '</div>'."\n";
							$rate_plans .= '</div>'."\n";
						}
					}
					//End Comparison with IBE
				}
				$rate_plans .= '</div>'."\n";
			}
		}
		?>
		<td class="left"><?php echo $rate_plans; ?></td>
		<td class="left">
		<?php
		if (count($restrictions) > 0) {
			?>
			<div class="vcmrar-restr-leftblock">
			<?php
			foreach ($restrictions as $rpid => $restriction) {
				?>
				<div class="vcmrarrestr-block">
					<span class="vcmrarrestr-minlos"><span class="vcmshowtip" title="<?php echo JText::sprintf('VCMRARRATEPLANID', $rpid); ?>"><?php echo JText::_('VCMRARRESTRMINLOS'); ?></span> <input type="number" min="0" name="<?php echo 'restrmin_'.$rooms[0]['id'].'_'.$day.'_'.$rpid; ?>" size="3" value="<?php echo $restriction['minlos']; ?>"/></span>
					<span class="vcmrarrestr-maxlos"><span class="vcmshowtip" title="<?php echo JText::sprintf('VCMRARRATEPLANID', $rpid); ?>"><?php echo JText::_('VCMRARRESTRMAXLOS'); ?></span> <input type="number" min="0" name="<?php echo 'restrmax_'.$rooms[0]['id'].'_'.$day.'_'.$rpid; ?>" size="3" value="<?php echo $restriction['maxlos']; ?>"/></span>
					<div class="vcmrarrestr-arrivdep">
						<span class="vcmrarrestr-tag <?php echo $restriction['closedToArrival'] == 'true' ? 'vcmtagenabled' : 'vcmtagdisabled'; ?>" onclick="toggleRestrArrivalStatus(<?php echo $restriction['closedToArrival'] == 'true' ? '1' : '0'; ?>, '<?php echo $day; ?>', '<?php echo $rpid; ?>');" id="restrplanarrival<?php echo $day.$rpid; ?>"><?php echo JText::_('VCMRARRESTRCLOSEDARRIVAL'); ?></span><input type="hidden" name="restrplanarrival<?php echo $day.$rpid; ?>" value="" id="inprestrplanarrival<?php echo $day.$rpid; ?>"/>
						<span class="vcmrarrestr-tag <?php echo $restriction['closedToDeparture'] == 'true' ? 'vcmtagenabled' : 'vcmtagdisabled'; ?>" onclick="toggleRestrDepartureStatus(<?php echo $restriction['closedToDeparture'] == 'true' ? '1' : '0'; ?>, '<?php echo $day; ?>', '<?php echo $rpid; ?>');" id="restrplandeparture<?php echo $day.$rpid; ?>"><?php echo JText::_('VCMRARRESTRCLOSEDDEPARTURE'); ?></span><input type="hidden" name="restrplandeparture<?php echo $day.$rpid; ?>" value="" id="inprestrplandeparture<?php echo $day.$rpid; ?>"/>
					</div>
				</div>
				<?php
			}
			?>
			</div>
			<?php
			if (@count($comparison[$day][$rooms[0]['id']]) > 0) {
				?>
			<div class="vcm-comparison vcm-compare-restrictions">
				<span class="vcm-compare-ibelab vcm-compare-ibecenter"><?php echo JText::_('VCMCOMPONIBE'); ?></span>
				<span class="vcm-compare-ibelab"><?php echo JText::_('VCMRARRESTRMINLOS'); ?></span>
				<span class="vcm-compare-ibecircle"><?php echo $comparison[$day][$rooms[0]['id']]['minlos']; ?></span>
			</div>
				<?php
			}
		}
		?>
		</td>
	</tr>
	<?php
	$k = 1 - $k;
	$i++;
	if ($day_rooms > 1) {
		for ($j = 1; $j < $day_rooms; $j++) {
			$ota_rate_plan = !empty($ota_rooms[$rooms[$j]['id']][key($ota_rooms[$rooms[$j]['id']])]['otapricing']) ? json_decode($ota_rooms[$rooms[$j]['id']][key($ota_rooms[$rooms[$j]['id']])]['otapricing'], true) : array();
			$inv_details = '';
			foreach ($rooms[$j]['Inventory'] as $inv_type => $inv_val) {
				$inv_details .= '- '.$inv_type.': '.$inv_val.'&lt;br/&gt;';
			}
	?>
	<tr class="row<?php echo $k; ?>">
		<td><div class="vcmrar-room-box"><span class="vcmshowtip vcmistip" title="ID <?php echo $rooms[$j]['id']; ?>"><?php echo $ota_rooms[$rooms[$j]['id']][0]['otaroomname']; ?></span></div></td>
		<td class="center" align="center"><img src="<?php echo VCM_ADMIN_URI; ?>assets/css/images/<?php echo $rooms[$j]['closed'] == 'true' ? 'disabled' : 'enabled'; ?>.png" class="imgtoggle" style="cursor: default;" /><div class="vcmrar-newroomstatus" id="divroomstatus<?php echo $day.$rooms[$j]['id']; ?>"><input type="hidden" name="<?php echo 'roomstatus_'.$day.'_'.$rooms[$j]['id']; ?>" value="" id="roomstatus<?php echo $day.$rooms[$j]['id']; ?>"/></div></td>
		<td class="center" align="center">
			<span class="vcmrarinventorysp"><span class="vcmshowtip" title="<?php echo $inv_details; ?>"><?php echo JText::_('VCMTOTINVAVAILABLE'); ?></span> <input type="number" min="0" name="<?php echo 'inv_'.$day.'_'.$rooms[$j]['id']; ?>" value="<?php echo $rooms[$j]['Inventory']['units_remaining']; ?>" size="3"/></span>
			<?php
			if (@count($comparison[$day][$rooms[$j]['id']]) > 0) {
			?>
			<div class="vcm-comparison vcm-compare-units">
				<span class="vcm-compare-ibelab"><?php echo JText::_('VCMCOMPONIBE'); ?></span>
				<span class="vcm-compare-ibecircle vcm-compare-ibecircleavail"><?php echo $comparison[$day][$rooms[$j]['id']]['unitsavail']; ?></span>
				<span class="vcm-compare-ibeoflab"><?php echo JText::_('VCMCOMPONIBEOF'); ?></span>
				<span class="vcm-compare-ibecircle"><?php echo $comparison[$day][$rooms[$j]['id']]['units']; ?></span>
			</div>
			<?php
			}
			?>
		</td>
		<?php
		$rate_plans = '';
		$restrictions = array();
		$tot_rate_plans = count($rooms[$j]['RatePlan']);
		if ($tot_rate_plans > 0) {
			foreach ($rooms[$j]['RatePlan'] as $rateplan) {
				$rate_plans .= '<div class="vcmrar-rateplan">'."\n";
				$rate_plan_tip = '';
				if (array_key_exists($rateplan['id'], $ota_rate_plan['RatePlan'])) {
					foreach ($ota_rate_plan['RatePlan'][$rateplan['id']] as $rpkey => $rpval) {
						if ($rpkey == 'id') {
							continue;
						}
						$rate_plan_tip .= ucwords($rpkey).': '.$rpval."&lt;br/&gt;";
					}
				}
				$rate_plans .= '<span class="'.(!empty($rate_plan_tip) ? 'vcmshowtip ' : '').'vcmrateplansp vcmrateplanon" id="rateplanstatus'.$day.$rateplan['id'].'" title="'.$rate_plan_tip.'">'.(array_key_exists($rateplan['id'], $ota_rate_plan['RatePlan']) ? $ota_rate_plan['RatePlan'][$rateplan['id']]['name'] : 'Rates').'</span><span class="vcmrar-spacer"></span><input type="hidden" name="rateplanstatus'.$day.$rateplan['id'].'" value="" id="inprateplanstatus'.$day.$rateplan['id'].'"/>'."\n";
				if (array_key_exists('Restrictions', $rateplan) && count($rateplan['Restrictions']) > 0) {
					$restrictions[$rateplan['id']] = $rateplan['Restrictions'];
					if (array_key_exists('@attributes', $rateplan['Restrictions']) && count($rateplan['Restrictions']) == 1) {
						$restrictions[$rateplan['id']] = $rateplan['Restrictions']['@attributes'];
					}
				}
				if (array_key_exists('Rate', $rateplan) && isset($ota_rate_plan['RatePlan'][$rateplan['id']])) {
					// since we never obtain information about rates from the PAR_RQ, we build the prices for this rate plan by reading the mix and max occupancy of the room
					$occ_range = range((int)$ota_rate_plan['RatePlan'][$rateplan['id']]['min_occupancy'], (int)$ota_rate_plan['RatePlan'][$rateplan['id']]['max_occupancy']);
					$rate_plans .= '<div class="vcmrar-rplan-leftblock">'."\n";
					$currency = $inventory_loaded === true && array_key_exists('currency', $rateplan['Rate']) ? $rateplan['Rate']['currency'] : $currency;
					foreach ($occ_range as $occ) {
						$rate_plans .= '<span class="vcmrarratesp">'.JText::sprintf('VCMRARRATEPEROCCUPANCY', $occ).' <span class="vcmrarcurrency">'.$currency.'</span> <input type="text" size="5" name="rateplan_'.$day.'_'.$rooms[$j]['id'].'_'.$rateplan['id'].'_'.$occ.'" value=""/></span>'."\n";
					}
					$rate_plans .= '</div>'."\n";
					//Comparison with IBE
					if (@count($comparison[$day][$rooms[$j]['id']]) > 0) {
						//build tabs for each type of price
						$tp_tabs = array();
						foreach ($comparison[$day][$rooms[$j]['id']] as $nights => $prices) {
							if (is_numeric($nights)) {
								foreach ($prices as $fare) {
									$tp_tabs[$fare['idprice']] = $fare;
								}
							}
						}
						//
						if (count($tp_tabs) > 0) {
							$rate_plans .= '<div class="vcmrar-rplan-rightblock vcm-comparison">'."\n";
							$rate_plans .= '<div class="vcm-compare-rates">'."\n";
							$tk = 0;
							foreach ($tp_tabs as $tpid => $fare) {
								$tk++;
								$tp_tab_class = $tk == 1 ? ' vcm-compare-ratetab-active' : '';
								$rate_plans .= '<div class="vcm-compare-ratetab'.$tp_tab_class.'" id="'.$day.'-'.$rooms[$j]['id'].'-'.$tpid.'-'.$rateplan['id'].'"><a href="javascript: void(0);">'.(strlen($fare['name']) > 9 ? substr($fare['name'], 0, 9).'.' : $fare['name']).'</a></div>'."\n";
							}
							$tk = 0;
							foreach ($tp_tabs as $tpid => $fare) {
								$tk++;
								$tp_col_class = $tk == 1 ? ' vcm-compare-pricecols-active' : '';
								$n = 0;
								$rate_plans .= '<div class="vcm-compare-pricecols'.$tp_col_class.' '.$day.'-'.$rooms[$j]['id'].'-'.$tpid.'-'.$rateplan['id'].'">'."\n";

								foreach ($rateplan['Rate'] as $occkey => $rate) {
									$occ_parts = explode('_', $occkey);
									$occ = $occ_parts[1];
									$n = 1;
									foreach ($comparison[$day][$rooms[$j]['id']] as $nights => $prices) {
										if (is_numeric($nights)) {
											foreach ($prices as $price) {
												if ((int)$nights == (int)$n && $tpid == $price['idprice']) {
													if ($occ > 0) {
														$diffusageprice = VikBooking::loadAdultsDiff($price['idroom'], $occ);
														if (is_array($diffusageprice)) {
															if ($diffusageprice['chdisc'] == 1) {
																//Charge
																if ($diffusageprice['valpcent'] == 1) {
																	//fixed value
																	$aduseval = $diffusageprice['pernight'] == 1 ? $diffusageprice['value'] * $price['days'] : $diffusageprice['value'];
																	$price['cost'] += $aduseval;
																} else {
																	//percentage value
																	$aduseval = $diffusageprice['pernight'] == 1 ? round(($price['cost'] * $diffusageprice['value'] / 100) * $price['days'], 2) : round(($price['cost'] * $diffusageprice['value'] / 100), 2);
																	$price['cost'] += $aduseval;
																}
															} else {
																//Discount
																if ($diffusageprice['valpcent'] == 1) {
																	//fixed value
																	$aduseval = $diffusageprice['pernight'] == 1 ? $diffusageprice['value'] * $price['days'] : $diffusageprice['value'];
																	$price['cost'] -= $aduseval;
																} else {
																	//percentage value
																	$aduseval = $diffusageprice['pernight'] == 1 ? round(($price['cost'] * $diffusageprice['value'] / 100) * $price['days'], 2) : round(($price['cost'] * $diffusageprice['value'] / 100), 2);
																	$price['cost'] -= $aduseval;
																}
															}
														}
													}
													$rate_plans .= '<div class="vcm-compare-rates-roomcost"><span class="vcm-compare-rates-copycost" id="'.$day.'_'.$rooms[$j]['id'].'_'.$rateplan['id'].'_'.$kr.'" title="'.JText::_('VCMRARCOPYPRICE').'"></span><span class="vcm-compare-pricefornights">'.$occ.' - '.JText::sprintf('VCMRARCOMPNUMNIGHTS', $price['days']).'</span><span class="vcm-compare-pricecurrency">'.$currencysymb.'</span><span class="vcm-compare-pricebox">'.number_format($price['cost'], 2, '.', '').'</span></div>'."\n";
												}
											}
										}
									}
								}
								$rate_plans .= '</div>'."\n";
							}
							$rate_plans .= '</div>'."\n";
							$rate_plans .= '</div>'."\n";
						}
					}
					//End Comparison with IBE
				}
				$rate_plans .= '</div>'."\n";
			}
		}
		?>
		<td class="left"><?php echo $rate_plans; ?></td>
		<td class="left">
		<?php
		if (count($restrictions) > 0) {
			?>
			<div class="vcmrar-restr-leftblock">
			<?php
			foreach ($restrictions as $rpid => $restriction) {
				?>
				<div class="vcmrarrestr-block">
					<span class="vcmrarrestr-minlos"><span class="vcmshowtip" title="<?php echo JText::sprintf('VCMRARRATEPLANID', $rpid); ?>"><?php echo JText::_('VCMRARRESTRMINLOS'); ?></span> <input type="number" min="0" name="<?php echo 'restrmin_'.$rooms[$j]['id'].'_'.$day.'_'.$rpid; ?>" size="3" value="<?php echo $restriction['minlos']; ?>"/></span>
					<span class="vcmrarrestr-maxlos"><span class="vcmshowtip" title="<?php echo JText::sprintf('VCMRARRATEPLANID', $rpid); ?>"><?php echo JText::_('VCMRARRESTRMAXLOS'); ?></span> <input type="number" min="0" name="<?php echo 'restrmax_'.$rooms[$j]['id'].'_'.$day.'_'.$rpid; ?>" size="3" value="<?php echo $restriction['maxlos']; ?>"/></span>
					<div class="vcmrarrestr-arrivdep">
						<span class="vcmrarrestr-tag <?php echo $restriction['closedToArrival'] == 'true' ? 'vcmtagenabled' : 'vcmtagdisabled'; ?>" onclick="toggleRestrArrivalStatus(<?php echo $restriction['closedToArrival'] == 'true' ? '1' : '0'; ?>, '<?php echo $day; ?>', '<?php echo $rpid; ?>');" id="restrplanarrival<?php echo $day.$rpid; ?>"><?php echo JText::_('VCMRARRESTRCLOSEDARRIVAL'); ?></span><input type="hidden" name="restrplanarrival<?php echo $day.$rpid; ?>" value="" id="inprestrplanarrival<?php echo $day.$rpid; ?>"/>
						<span class="vcmrarrestr-tag <?php echo $restriction['closedToDeparture'] == 'true' ? 'vcmtagenabled' : 'vcmtagdisabled'; ?>" onclick="toggleRestrDepartureStatus(<?php echo $restriction['closedToDeparture'] == 'true' ? '1' : '0'; ?>, '<?php echo $day; ?>', '<?php echo $rpid; ?>');" id="restrplandeparture<?php echo $day.$rpid; ?>"><?php echo JText::_('VCMRARRESTRCLOSEDDEPARTURE'); ?></span><input type="hidden" name="restrplandeparture<?php echo $day.$rpid; ?>" value="" id="inprestrplandeparture<?php echo $day.$rpid; ?>"/>
					</div>
				</div>
				<?php
			}
			?>
			</div>
			<?php
			if (@count($comparison[$day][$rooms[$j]['id']]) > 0) {
				?>
			<div class="vcm-comparison vcm-compare-restrictions">
				<span class="vcm-compare-ibelab vcm-compare-ibecenter"><?php echo JText::_('VCMCOMPONIBE'); ?></span>
				<span class="vcm-compare-ibelab"><?php echo JText::_('VCMRARRESTRMINLOS'); ?></span>
				<span class="vcm-compare-ibecircle"><?php echo $comparison[$day][$rooms[$j]['id']]['minlos']; ?></span>
			</div>
				<?php
			}
		}
		?>
		</td>
	</tr>
	<?php
		}
	}
}
?>
</table>

<input type="hidden" name="currency" value="<?php echo $currency; ?>"/>
<input type="hidden" name="e4j_debug" value="<?php echo isset($_REQUEST['e4j_debug']) && intval($_REQUEST['e4j_debug']) == 1 ? '1' : ''; ?>"/>

<script type="text/javascript">
jQuery(".vcmshowtip").tooltip();
vcm_rar_days = <?php echo json_encode(array_keys($rars['AvailRate'])); ?>;
var rplansheight = new Array();
jQuery(".vcmrar-rateplan").each(function(k){
	rplansheight.push(jQuery(this).height());
});
if (rplansheight.length > 0) {
	jQuery(".vcmrarrestr-block").each(function(k){
		jQuery(this).height(rplansheight[k]);
	});
}
jQuery(".vcm-copy-ratesinv").fadeIn();
<?php
if (count($comparison) > 0) {
	?>
jQuery(".vcm-ibe-compare").fadeIn();
	<?php
}
?>
function toggleRoomStatus(status, date, roomid) {
	/* Feratel does not support closures at room level, only at room-rateplan level. */
	var setclass = status == 1 ? "vcmrar-newroomstatus-todisabled" : "vcmrar-newroomstatus-toenabled";
	var settitle = status == 1 ? "<?php echo addslashes(JText::_('VCMRARSETTOCLOSED')); ?>" : "<?php echo addslashes(JText::_('VCMRARSETTOOPEN')); ?>";
	var cur_status = jQuery("#roomstatus"+date+roomid).val();
	var opposite_status = status == 1 ? 0 : 1;
	var setstatus = cur_status.length == 0 ? opposite_status : '';
	if (cur_status.length == 0) {
		jQuery("#roomstatus"+date+roomid).val(setstatus);
		jQuery("#divroomstatus"+date+roomid).attr("title", settitle);
		jQuery("#divroomstatus"+date+roomid).removeClass("vcmrar-newroomstatus-toenabled").removeClass("vcmrar-newroomstatus-todisabled").addClass(setclass);
	} else {
		jQuery("#roomstatus"+date+roomid).val("");
		jQuery("#divroomstatus"+date+roomid).attr("title", "");
		jQuery("#divroomstatus"+date+roomid).removeClass("vcmrar-newroomstatus-toenabled").removeClass("vcmrar-newroomstatus-todisabled");
	}
	jQuery("#roomstatus"+date+roomid).trigger("change");
}
function toggleRestrArrivalStatus(status, date, rpid) {
	var setclass = status == 1 ? "vcmrarrestr-tag-todisabled" : "vcmrarrestr-tag-toenabled";
	var cur_status = jQuery("#inprestrplanarrival"+date+rpid).val();
	var opposite_status = status == 1 ? 0 : 1;
	var setstatus = cur_status.length == 0 ? opposite_status : '';
	if (cur_status.length == 0) {
		jQuery("#inprestrplanarrival"+date+rpid).val(setstatus);
		jQuery("#restrplanarrival"+date+rpid).removeClass("vcmrarrestr-tag-toenabled").removeClass("vcmrarrestr-tag-todisabled").addClass(setclass);
	} else {
		jQuery("#inprestrplanarrival"+date+rpid).val("");
		jQuery("#restrplanarrival"+date+rpid).removeClass("vcmrarrestr-tag-toenabled").removeClass("vcmrarrestr-tag-todisabled");
	}
	jQuery("#inprestrplanarrival"+date+rpid).trigger("change");
}
function toggleRestrDepartureStatus(status, date, rpid) {
	var setclass = status == 1 ? "vcmrarrestr-tag-todisabled" : "vcmrarrestr-tag-toenabled";
	var cur_status = jQuery("#inprestrplandeparture"+date+rpid).val();
	var opposite_status = status == 1 ? 0 : 1;
	var setstatus = cur_status.length == 0 ? opposite_status : '';
	if (cur_status.length == 0) {
		jQuery("#inprestrplandeparture"+date+rpid).val(setstatus);
		jQuery("#restrplandeparture"+date+rpid).removeClass("vcmrarrestr-tag-toenabled").removeClass("vcmrarrestr-tag-todisabled").addClass(setclass);
	} else {
		jQuery("#inprestrplandeparture"+date+rpid).val("");
		jQuery("#restrplandeparture"+date+rpid).removeClass("vcmrarrestr-tag-toenabled").removeClass("vcmrarrestr-tag-todisabled");
	}
	jQuery("#inprestrplandeparture"+date+rpid).trigger("change");
}
function toggleRatePlanStatus(status, date, rpid) {
	var setclass = status == 1 ? "vcmrateplansp-todisabled" : "vcmrateplansp-toenabled";
	var cur_status = jQuery("#inprateplanstatus"+date+rpid).val();
	var opposite_status = status == 1 ? 0 : 1;
	var setstatus = cur_status.length == 0 ? opposite_status : '';
	if (cur_status.length == 0) {
		jQuery("#inprateplanstatus"+date+rpid).val(setstatus);
		jQuery("#rateplanstatus"+date+rpid).removeClass("vcmrateplansp-toenabled").removeClass("vcmrateplansp-todisabled").addClass(setclass);
	} else {
		jQuery("#inprateplanstatus"+date+rpid).val("");
		jQuery("#rateplanstatus"+date+rpid).removeClass("vcmrateplansp-toenabled").removeClass("vcmrateplansp-todisabled");
	}
	jQuery("#inprateplanstatus"+date+rpid).trigger("change");
}

var fix_height = 0;
var fix_margin = 5;
var min_pos_check = 150;
function setFixHeight(limit) {
	jQuery('*').filter(function() {
		return jQuery(this).css("position") === 'fixed' && !jQuery(this).hasClass("vcm-info-overlay-block") && !jQuery(this).hasClass("vcm-info-overlay-content");
	}).each(function(){
		if (jQuery(this).offset().top < limit) {
			fix_height += jQuery(this).height();
		}
	});
}
var tot_rooms_found = <?php echo $max_rooms_found; ?>;
var tot_rateplans_found = <?php echo $max_rateplans_found; ?>;
if (tot_rooms_found > 1 || tot_rateplans_found > 1) {
	jQuery(window).scroll(function() {
		var scrollpos = jQuery(window).scrollTop();
		jQuery(".vcmrardate-box").each(function(kel) {
			var d_top = jQuery(this).offset().top + jQuery(this).outerHeight(true);
			var par_d_top = jQuery(this).parent("td").offset().top;
			var par_limit = (par_d_top + jQuery(this).parent("td").height());
			if (scrollpos > min_pos_check && fix_height == 0) {
				setFixHeight(par_d_top);
			}
			if ((scrollpos + fix_height + fix_margin) > par_d_top && (scrollpos + fix_height + fix_margin) < par_limit) {
				jQuery(this).css({top: (scrollpos + fix_height + fix_margin), position: 'absolute'}).addClass("vcmrardate-scroll");
				return false;
			}
			if (scrollpos == 0 || (scrollpos - fix_height - fix_margin) < par_d_top) {
				jQuery(this).css({top: 'auto', position: 'inherit'}).removeClass("vcmrardate-scroll");
				return false;
			}
		});
		jQuery(".vcmrar-room-box").each(function(kel) {
			var r_top = jQuery(this).offset().top + jQuery(this).outerHeight(true);
			var par_r_top = jQuery(this).parent("td").offset().top;
			var par_limit = (par_r_top + jQuery(this).parent("td").height());
			if (scrollpos > min_pos_check && fix_height == 0) {
				setFixHeight(par_r_top);
			}
			if ((scrollpos + fix_height + fix_margin) > par_r_top && (scrollpos + fix_height + fix_margin) < par_limit) {
				jQuery(this).css({top: (scrollpos + fix_height + fix_margin), position: 'absolute'}).addClass("vcmrar-room-scroll");
				return false;
			}
			if (scrollpos == 0 || (scrollpos - fix_height - fix_margin) < par_r_top) {
				jQuery(this).css({top: 'auto', position: 'inherit'}).removeClass("vcmrar-room-scroll");
				return false;
			}
		});
	});
}
jQuery("body").on("click", "div.vcmrardate-scroll", function() {
	var gotopos = jQuery(this).find("span").text();
	jQuery('html,body').animate({ scrollTop: (jQuery("#date"+gotopos).offset().top - fix_height - fix_margin) }, { duration: 'slow' });
});
<?php
if ($inventory_loaded === false) {
	?>
vcmAlertModal('warning', '<?php echo addslashes(JText::_('VCMWARNINGTEXT')); ?>', '<p><?php echo addslashes(JText::_('VCMPARNOINVLOADEDRESP')); ?></p><p><?php echo addslashes(JText::_('VCMPARNOINVPUSHSUGGEST')); ?></p>');
	<?php
}
?>
</script>
<?php
//Debug:
//echo '<br clear="all"/><br/><pre>'.print_r($rars, true).'</pre>';
if (isset($_REQUEST['e4j_debug']) && intval($_REQUEST['e4j_debug']) == 1) {
	echo '<br clear="all"/><br/><pre>'.print_r($rars, true).'</pre>';
}
