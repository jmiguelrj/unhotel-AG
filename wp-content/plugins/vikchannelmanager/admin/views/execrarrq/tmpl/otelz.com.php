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
$currency = '';

$inventory_loaded = array_key_exists('NoInventory', $rars) ? false : true;
if ($inventory_loaded === false) {
	$currency = VikChannelManager::getCurrencyName(true);
}

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
		<th class="title left" width="200"> </th>
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
	?>
	<tr class="row<?php echo $k; ?>">
		<td rowspan="<?php echo $day_rooms; ?>"><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $day; ?>" class="vcm-rar-ckb" onClick="<?php echo $vik->checkboxOnClick(); ?>"><span id="date<?php echo $day; ?>"></span></td>
		<td rowspan="<?php echo $day_rooms; ?>"><div class="vcmrardate-box"><span class="vcmrardate"><?php echo $day; ?></span></div></td>
		<td><div class="vcmrar-room-box"><span class="vcmshowtip vcmistip" title="ID <?php echo $rooms[0]['id']; ?>"><?php echo $ota_rooms[$rooms[0]['id']][0]['otaroomname']; ?></span></div></td>
		<td class="center" align="center"><img src="<?php echo VCM_ADMIN_URI; ?>assets/css/images/<?php echo $rooms[0]['closed'] == 'true' ? 'disabled' : 'enabled'; ?>.png" class="imgtoggle"/><div class="vcmrar-newroomstatus" id="divroomstatus<?php echo $day.$rooms[0]['id']; ?>"><input type="hidden" name="<?php echo 'roomstatus_'.$day.'_'.$rooms[0]['id']; ?>" value="" id="roomstatus<?php echo $day.$rooms[0]['id']; ?>"/></div></td>
		<td class="center" align="center">
			<span class="vcmrarinventorysp"><span><?php echo JText::_('VCMTOTINVAVAILABLE'); ?></span> <input type="number" min="0" name="<?php echo 'inv_'.$day.'_'.$rooms[0]['id']; ?>" value="" size="3"/></span>
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
						$rate_plan_tip .= ucwords($rpkey).': '.$rpval."&lt;br/&gt;";
					}
				}
				$rate_plans .= '<span class="'.(!empty($rate_plan_tip) ? 'vcmshowtip ' : '').'vcmrateplansp '.($rateplan['closed'] == 'true' ? 'vcmrateplanoff' : 'vcmrateplanon').'" id="rateplanstatus'.$day.$rateplan['id'].'" title="'.$rate_plan_tip.'">'.JText::sprintf('VCMRARRATEPLANTITLE', (array_key_exists($rateplan['id'], $ota_rate_plan['RatePlan']) ? $ota_rate_plan['RatePlan'][$rateplan['id']]['name'].' ' : ''), $rateplan['id']).'</span><span class="vcmrar-spacer"></span><input type="hidden" name="rateplanstatus'.$day.$rateplan['id'].'" value="" id="inprateplanstatus'.$day.$rateplan['id'].'"/>'."\n";
				if (array_key_exists('Rate', $rateplan) && count($rateplan['Rate']) > 0) {
					$rate_type = '';
					$rate_plans .= '<div class="vcmrar-rplan-leftblock">'."\n";
					$currency = $inventory_loaded === true ? $rateplan['Rate']['currency'] : $currency;
					//Room Price
					if ($ota_rate_plan['RatePlan'][$rateplan['id']]['pricing_type'] == 'SinglePrice' || $ota_rate_plan['RatePlan'][$rateplan['id']]['pricing_type'] == 'PriceByRate') {
						$rate_plans .= '<span class="vcmrarratesp">Room Price <span class="vcmrarcurrency">'.$currency.'</span> <input type="number" size="5" name="rateplan_'.$day.'_'.$rooms[0]['id'].'_'.$rateplan['id'].'_price" value="" placeholder="0.00"/></span>'."\n";
					}
					//Price by Adults
					if (array_key_exists('max_adults', $ota_rate_plan['RatePlan'][$rateplan['id']]) && $ota_rate_plan['RatePlan'][$rateplan['id']]['pricing_type'] == 'PriceByAdult') {
						for ($i = 1; $i <= (int)$ota_rate_plan['RatePlan'][$rateplan['id']]['max_adults']; $i++) {
							$lbl = $i < 2 ? JText::_('VCMDESPEGARRARRATEGUEST') : JText::sprintf('VCMDESPEGARRARRATEGUESTS', $i);
							$rate_plans .= '<span class="vcmrarratesp">'.$lbl.' <span class="vcmrarcurrency">'.$currency.'</span> <input type="number" size="5" name="rateplan_'.$day.'_'.$rooms[0]['id'].'_'.$rateplan['id'].'_adults'.$i.'" value="" placeholder="0.00"/></span>'."\n";
						}
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
								foreach ($rateplan['Rate'] as $kr => $rate) {
									$n = 1;
									$rate_type = $kr;
									$occ = 0;
									if (!in_array($rate_type, array('price'))) {
										continue;
									}
									foreach ($comparison[$day][$rooms[0]['id']] as $nights => $prices) {
										if (is_numeric($nights)) {
											foreach ($prices as $price) {
												if((int)$nights == (int)$n && $tpid == $price['idprice']) {
													$rate_plans .= '<div class="vcm-compare-rates-roomcost"><span class="vcm-compare-rates-copycost" id="'.$day.'_'.$rooms[0]['id'].'_'.$rateplan['id'].'_'.$kr.'" title="'.JText::_('VCMRARCOPYPRICE').'"></span><span class="vcm-compare-pricefornights">'.$rate_type.' - '.JText::sprintf('VCMRARCOMPNUMNIGHTS', $price['days']).'</span><span class="vcm-compare-pricecurrency">'.$currencysymb.'</span><span class="vcm-compare-pricebox">'.number_format($price['cost'], 2, '.', '').'</span></div>'."\n";
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
		//Restrictions are not supported
		?>
		</td>
	</tr>
	<?php
	$k = 1 - $k;
	$i++;
	if($day_rooms > 1) {
		for ($j = 1; $j < $day_rooms; $j++) {
			$ota_rate_plan = !empty($ota_rooms[$rooms[$j]['id']][key($ota_rooms[$rooms[$j]['id']])]['otapricing']) ? json_decode($ota_rooms[$rooms[$j]['id']][key($ota_rooms[$rooms[$j]['id']])]['otapricing'], true) : array();
	?>
	<tr class="row<?php echo $k; ?>">
		<td><div class="vcmrar-room-box"><span class="vcmshowtip vcmistip" title="ID <?php echo $rooms[$j]['id']; ?>"><?php echo $ota_rooms[$rooms[$j]['id']][0]['otaroomname']; ?></span></div></td>
		<td class="center" align="center"><img src="<?php echo VCM_ADMIN_URI; ?>assets/css/images/<?php echo $rooms[$j]['closed'] == 'true' ? 'disabled' : 'enabled'; ?>.png" class="imgtoggle"/><div class="vcmrar-newroomstatus" id="divroomstatus<?php echo $day.$rooms[$j]['id']; ?>"><input type="hidden" name="<?php echo 'roomstatus_'.$day.'_'.$rooms[$j]['id']; ?>" value="" id="roomstatus<?php echo $day.$rooms[$j]['id']; ?>"/></div></td>
		<td class="center" align="center">
			<span class="vcmrarinventorysp"><span><?php echo JText::_('VCMTOTINVAVAILABLE'); ?></span> <input type="number" min="0" name="<?php echo 'inv_'.$day.'_'.$rooms[$j]['id']; ?>" value="" size="3"/></span>
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
						$rate_plan_tip .= ucwords($rpkey).': '.$rpval."&lt;br/&gt;";
					}
				}
				$rate_plans .= '<span class="'.(!empty($rate_plan_tip) ? 'vcmshowtip ' : '').'vcmrateplansp '.($rateplan['closed'] == 'true' ? 'vcmrateplanoff' : 'vcmrateplanon').'" id="rateplanstatus'.$day.$rateplan['id'].'" title="'.$rate_plan_tip.'">'.JText::sprintf('VCMRARRATEPLANTITLE', (array_key_exists($rateplan['id'], $ota_rate_plan['RatePlan']) ? $ota_rate_plan['RatePlan'][$rateplan['id']]['name'].' ' : ''), $rateplan['id']).'</span><span class="vcmrar-spacer"></span><input type="hidden" name="rateplanstatus'.$day.$rateplan['id'].'" value="" id="inprateplanstatus'.$day.$rateplan['id'].'"/>'."\n";
				if (array_key_exists('Rate', $rateplan) && count($rateplan['Rate']) > 0) {
					$rate_type = '';
					$rate_plans .= '<div class="vcmrar-rplan-leftblock">'."\n";
					$currency = $inventory_loaded === true ? $rateplan['Rate']['currency'] : $currency;
					//Room Price
					if ($ota_rate_plan['RatePlan'][$rateplan['id']]['pricing_type'] == 'SinglePrice' || $ota_rate_plan['RatePlan'][$rateplan['id']]['pricing_type'] == 'PriceByRate') {
						$rate_plans .= '<span class="vcmrarratesp">Room Price <span class="vcmrarcurrency">'.$currency.'</span> <input type="number" size="5" name="rateplan_'.$day.'_'.$rooms[$j]['id'].'_'.$rateplan['id'].'_price" value="" placeholder="0.00"/></span>'."\n";
					}
					//Price by Adults
					if (array_key_exists('max_adults', $ota_rate_plan['RatePlan'][$rateplan['id']]) && $ota_rate_plan['RatePlan'][$rateplan['id']]['pricing_type'] == 'PriceByAdult') {
						for ($i = 1; $i <= (int)$ota_rate_plan['RatePlan'][$rateplan['id']]['max_adults']; $i++) {
							$lbl = $i < 2 ? JText::_('VCMDESPEGARRARRATEGUEST') : JText::sprintf('VCMDESPEGARRARRATEGUESTS', $i);
							$rate_plans .= '<span class="vcmrarratesp">'.$lbl.' <span class="vcmrarcurrency">'.$currency.'</span> <input type="number" size="5" name="rateplan_'.$day.'_'.$rooms[$j]['id'].'_'.$rateplan['id'].'_adults'.$i.'" value="" placeholder="0.00"/></span>'."\n";
						}
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
								foreach ($rateplan['Rate'] as $kr => $rate) {
									$n = 1;
									$rate_type = $kr;
									$occ = 0;
									if(!in_array($rate_type, array('price'))) {
										continue;
									}
									foreach ($comparison[$day][$rooms[$j]['id']] as $nights => $prices) {
										if (is_numeric($nights)) {
											foreach ($prices as $price) {
												if((int)$nights == (int)$n && $tpid == $price['idprice']) {
													$rate_plans .= '<div class="vcm-compare-rates-roomcost"><span class="vcm-compare-rates-copycost" id="'.$day.'_'.$rooms[$j]['id'].'_'.$rateplan['id'].'_'.$kr.'" title="'.JText::_('VCMRARCOPYPRICE').'"></span><span class="vcm-compare-pricefornights">'.$rate_type.' - '.JText::sprintf('VCMRARCOMPNUMNIGHTS', $price['days']).'</span><span class="vcm-compare-pricecurrency">'.$currencysymb.'</span><span class="vcm-compare-pricebox">'.number_format($price['cost'], 2, '.', '').'</span></div>'."\n";
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
		//Restrictions are not supported
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
if(rplansheight.length > 0) {
	jQuery(".vcmrarrestr-block").each(function(k){
		jQuery(this).height(rplansheight[k]);
	});
}
jQuery(".vcm-copy-ratesinv").fadeIn();
<?php
if(count($comparison) > 0) {
	?>
jQuery(".vcm-ibe-compare").fadeIn();
	<?php
}
?>

var fix_height = 0;
var fix_margin = 5;
var min_pos_check = 150;
function setFixHeight(limit) {
	jQuery('*').filter(function() {
		return jQuery(this).css("position") === 'fixed' && !jQuery(this).hasClass("vcm-info-overlay-block") && !jQuery(this).hasClass("vcm-info-overlay-content");
	}).each(function(){
		if(jQuery(this).offset().top < limit) {
			fix_height += jQuery(this).height();
		}
	});
}
var tot_rooms_found = <?php echo $max_rooms_found; ?>;
var tot_rateplans_found = <?php echo $max_rateplans_found; ?>;
if(tot_rooms_found > 1 || tot_rateplans_found > 1) {
	jQuery(window).scroll(function() {
		var scrollpos = jQuery(window).scrollTop();
		jQuery(".vcmrardate-box").each(function(kel) {
			var d_top = jQuery(this).offset().top + jQuery(this).outerHeight(true);
			var par_d_top = jQuery(this).parent("td").offset().top;
			var par_limit = (par_d_top + jQuery(this).parent("td").height());
			if(scrollpos > min_pos_check && fix_height == 0) {
				setFixHeight(par_d_top);
			}
			if((scrollpos + fix_height + fix_margin) > par_d_top && (scrollpos + fix_height + fix_margin) < par_limit) {
				jQuery(this).css({top: (scrollpos + fix_height + fix_margin), position: 'absolute'}).addClass("vcmrardate-scroll");
				return false;
			}
			if(scrollpos == 0 || (scrollpos - fix_height - fix_margin) < par_d_top) {
				jQuery(this).css({top: 'auto', position: 'inherit'}).removeClass("vcmrardate-scroll");
				return false;
			}
		});
		jQuery(".vcmrar-room-box").each(function(kel) {
			var r_top = jQuery(this).offset().top + jQuery(this).outerHeight(true);
			var par_r_top = jQuery(this).parent("td").offset().top;
			var par_limit = (par_r_top + jQuery(this).parent("td").height());
			if(scrollpos > min_pos_check && fix_height == 0) {
				setFixHeight(par_r_top);
			}
			if((scrollpos + fix_height + fix_margin) > par_r_top && (scrollpos + fix_height + fix_margin) < par_limit) {
				jQuery(this).css({top: (scrollpos + fix_height + fix_margin), position: 'absolute'}).addClass("vcmrar-room-scroll");
				return false;
			}
			if(scrollpos == 0 || (scrollpos - fix_height - fix_margin) < par_r_top) {
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
if($inventory_loaded === false) {
	?>
vcmAlertModal('warning', '<?php echo addslashes(JText::_('VCMWARNINGTEXT')); ?>', '<p><?php echo addslashes(JText::_('VCMPARNOINVLOADEDRESP')); ?></p><p><?php echo addslashes(JText::_('VCMPARNOINVPUSHSUGGEST')); ?></p>');
	<?php
}
?>
</script>
<?php
//Debug:
//echo '<br clear="all"/><br/><pre>'.print_r($rars, true).'</pre>';
if(isset($_REQUEST['e4j_debug']) && intval($_REQUEST['e4j_debug']) == 1) {
	echo '<br clear="all"/><br/><pre>'.print_r($rars, true).'</pre>';
}