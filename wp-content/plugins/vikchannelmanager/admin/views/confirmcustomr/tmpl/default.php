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

JHTML::_('behavior.tooltip');

$cust_a = $this->cust_a;
$max_nodes = VikRequest::getInt('max_nodes', '', 'request');
$max_nodes = empty($max_nodes) || $max_nodes <= 0 ? 10 : $max_nodes;
$cookie = JFactory::getApplication()->input->cookie;

$df = VikChannelManager::getClearDateFormat(true);

$curr_symb = VikChannelManager::getCurrencySymb(true);

?>

<form action="index.php" name="adminForm" id="adminForm" method="post">
	
	<?php foreach( $cust_a as $idroom => $ca ) { ?>
		<div class="vcm-custa-roomname">
			<h2><?php echo $ca['rname']; ?></h2>
			<?php if ( !empty($ca['rdesc']) ) { ?>
				<div class="vcm-custa-roomdesc">
					<small><?php echo $ca['rdesc']; ?></small>
				</div>
			<?php } ?>
		</div>
		
		<div class="vcm-custa-roomcont">
			<div class="vcm-custa-roomcont-inner">
			
				<div class="vcm-custrates-roomdetails">
					<?php
					$vbo_update = false;
					foreach( $ca['details'] as $index => $details ) {
						list($minlos, $maxlos, $cta, $ctd) = explode('-', $details['restrictions']);
						$restrictions_sent = (!empty($minlos) || !empty($maxlos) || !empty($cta) || !empty($ctd));
						$vbo_update = $restrictions_sent || ($details['rate'] > 0 && $details['exactcost'] > 0) ? true : $vbo_update;
						$format_alter = $details['rate'] > 0 && $details['exactcost'] < 1 ? '+ ' : '';
						$format_alter .= $details['percentot'] == 1 ? '' : ($curr_symb . ' ');
						$format_alter .= $details['rate'];
						$format_alter .= $details['percentot'] == 1 ? ' %' : '';
						?>
						<div class="vcm-custa-roomblock">
							<?php if ( $details['endts'] != 0 ) { ?>
								<span class="vcm-custa-from-label"><i class="vboicn-calendar"></i><?php echo JText::_('VCMOSFROMDATE'); ?></span>
								<span class="vcm-custa-from-value"><?php echo date( $df, $details['fromts'] ); ?></span>
								<span class="vcm-custa-to-label"><i class="vboicn-calendar"></i><?php echo JText::_('VCMOSTODATE'); ?></span>
								<span class="vcm-custa-to-value"><?php echo date( $df, $details['endts'] ); ?></span>
							<?php } else { ?>
								<span class="vcm-custa-from-label"><i class="vboicn-calendar"></i><?php echo JText::_('VCMOSSINGDATE'); ?></span>
								<span class="vcm-custa-from-value"><?php echo date( $df, $details['fromts'] ); ?></span>
								<span class="vcm-custa-to-label">&nbsp;</span>
								<span class="vcm-custa-to-value">&nbsp;</span>
							<?php } ?>
							<span class="vcm-custa-units-label"><i class="vboicn-calculator"></i><?php echo JText::_('VCMOSRATEONDATE'); ?></span>
							<span class="vcm-custa-units-value"><?php echo $format_alter; ?></span>
							<?php
							if ($restrictions_sent) {
								?>
								<span class="vcm-custa-restr-label"><i class="vboicn-shield"></i><?php echo JText::_('VCMRARRESTRICTIONS'); ?>:</span>
								<span class="vcm-custa-restr-value">
								<?php
								if (!empty($minlos)) {
									?>
									<span><?php echo JText::_('VCMRARRESTRMINLOS').' '.$minlos; ?></span>
									<?php
								}
								if (!empty($maxlos)) {
									?>
									<span><?php echo JText::_('VCMRARRESTRMAXLOS').' '.$maxlos; ?></span>
									<?php
								}
								if (!empty($cta)) {
									?>
									<span><?php echo JText::_('VCMRARRESTRCLOSEDARRIVAL'); ?></span>
									<?php
								}
								if (!empty($ctd)) {
									?>
									<span><?php echo JText::_('VCMRARRESTRCLOSEDDEPARTURE'); ?></span>
									<?php
								}
								?>
								</span>
								<?php
							}
							?>
						</div>
						
						<?php if (empty($ca['rdesc'])) {
							$val = $idroom.";;".$details['fromts'].";;".(empty($details['endts']) ? $details['fromts'] : $details['endts']).";;".$details['rate'].";;".$details['percentot'];
							?>
							
							<input type="hidden" name="rooms[<?php echo $idroom; ?>][details][<?php echo $index; ?>][from]" value="<?php echo $details['fromts']; ?>"/>
							<input type="hidden" name="rooms[<?php echo $idroom; ?>][details][<?php echo $index; ?>][to]" value="<?php echo $details['endts']; ?>"/>

							<input type="hidden" name="rooms[<?php echo $idroom; ?>][details][<?php echo $index; ?>][rate]" value="<?php echo $details['rate']; ?>"/>
							<input type="hidden" name="rooms[<?php echo $idroom; ?>][details][<?php echo $index; ?>][percentot]" value="<?php echo $details['percentot']; ?>"/>
							<input type="hidden" name="rooms[<?php echo $idroom; ?>][details][<?php echo $index; ?>][exactcost]" value="<?php echo $details['exactcost']; ?>"/>
							<?php
							if ($restrictions_sent) {
								//Submit the restrictions rules only if not all empty
								?>
							<input type="hidden" name="rooms[<?php echo $idroom; ?>][details][<?php echo $index; ?>][restrictions]" value="<?php echo $details['restrictions']; ?>"/>
								<?php
							}
							?>
						<?php } ?>
						
					<?php
					}
					?>  
				</div>

				<div class="vcm-custrates-roomrplans">
					<div class="vcmratespush-rplans-wrap">
						<label for="pricetypes_<?php echo $idroom; ?>"><?php echo JText::_('VCMRATESPUSHOVERVBORPLAN'); ?></label>
						<select name="rooms[<?php echo $idroom; ?>][pricetype]" id="pricetypes_<?php echo $idroom; ?>" onchange="JavaScript: vcmSetDefaultRates('<?php echo $idroom; ?>', this);">
						<?php
						foreach ($ca['pricetypes'] as $krp => $pricetype) {
							echo '<option value="'.$pricetype['idprice'].'" data-defcost="'.(array_key_exists('defaultrates', $ca) && count($ca['defaultrates']) > 0 && array_key_exists($krp, $ca['defaultrates']) ? $ca['defaultrates'][$krp] : '0.00').'"'.($krp <= 0 ? ' selected="selected"' : '').'>'.$pricetype['name'].'</option>'."\n";
						}
						?>
						</select>
					</div>
					<div class="vcmratespush-rplanscosts-wrap">
						<span class="vcmratespush-rplanscosts-lbl" onclick="vcmToggleDefRateInp('<?php echo $idroom; ?>');"><?php echo JText::_('VCMRATESPUSHPERNIGHT'); ?></span>
						<span class="vcmratespush-rplanscosts-currency"><?php echo VikChannelManager::getCurrencySymb(); ?></span>
						<span class="vcmratespush-rplanscosts-amount" id="defrateslbl_<?php echo $idroom; ?>"><?php echo array_key_exists('defaultrates', $ca) && count($ca['defaultrates']) > 0 ? $ca['defaultrates'][0] : '0.00'; ?></span>
						<input type="number" step="any" name="rooms[<?php echo $idroom; ?>][defrates]" id="defrates_<?php echo $idroom; ?>" value="<?php echo array_key_exists('defaultrates', $ca) && count($ca['defaultrates']) > 0 ? $ca['defaultrates'][0] : '0.00'; ?>" style="display: none;" />
					</div>
				</div>
				
				<div class="vcm-custa-roomchannels">
					
				<?php if (count($ca['channels']) > 0) { ?>
					<div class="vcm-custa-channelhead">
						<input type="button" class="btn" value="<?php echo JText::_('VCMOSCHECKALL'); ?>" onClick="jQuery('.check-<?php echo $idroom; ?>').prop('checked', true).trigger('change');"/>
						<input type="button" class="btn" value="<?php echo JText::_('VCMOSUNCHECKALL'); ?>" onClick="jQuery('.check-<?php echo $idroom; ?>').prop('checked', false).trigger('change');"/>
					</div>
				<?php } ?>
					
					<div class="vcmavpush-channels-wrap">
					<?php
					foreach ($ca['channels'] as $ch) {
						$channel_pricing = !empty($ch['otapricing']) ? json_decode($ch['otapricing'], true) : array();
						$orig_ch_name = $ch['name'];
						if ($ch['idchannel'] == VikChannelManagerConfig::AIRBNBAPI) {
							$ch['name'] = 'Airbnb';
						} elseif ($ch['idchannel'] == VikChannelManagerConfig::GOOGLEHOTEL) {
							$ch['name'] = 'Google Hotel';
						} elseif ($ch['idchannel'] == VikChannelManagerConfig::GOOGLEVR) {
							$ch['name'] = 'Google VR';
						}
						?>
						<div class="vcmratespush-channel-wrap">
							<span class="vbotasp <?php echo $orig_ch_name; ?> vcmavpush-channel-cont">
								<label for="ch<?php echo $idroom.$ch['idchannel']; ?>"><?php echo ucwords($ch['name']); ?></label> 
								<input type="checkbox" class="vcm-avpush-checkbox check-<?php echo $idroom; ?>" name="rooms[<?php echo $idroom; ?>][channels][]" id="ch<?php echo $idroom.$ch['idchannel']; ?>" value="<?php echo $ch['idchannel']; ?>" />
							</span>
							<div class="vcmratespush-channel-rateplans chrplans<?php echo $idroom; ?>" id="pricingch<?php echo $idroom.$ch['idchannel']; ?>">
						<?php
						/**
						 * We need to reorder the rate plans for Expedia
						 * to have the Derived ones at the bottom of the list.
						 * 
						 * @since 	1.6.13
						 */
						if ($ch['idchannel'] == VikChannelManagerConfig::EXPEDIA) {
							$channel_pricing = VikChannelManager::sortExpediaChannelPricing($channel_pricing);
						} else {
							$channel_pricing = VikChannelManager::sortGenericChannelPricing($channel_pricing);
						}
						foreach ($channel_pricing as $chpk => $rateplans) {
							if ($chpk != 'RatePlan') {
								continue;
							}
							?>
								<select name="rooms[<?php echo $idroom; ?>][rplans][<?php echo $ch['idchannel']; ?>]">
							<?php
							foreach ($rateplans as $rpid => $rateplan) {
								if ($ch['idchannel'] == VikChannelManagerConfig::AGODA || $ch['idchannel'] == VikChannelManagerConfig::YCS50) {
									echo '<option value="'.$rateplan['id'].'"'.(array_key_exists('vcm_default', $rateplan) ? ' selected="selected"' : '').'>'.$rateplan['name'].' '.(array_key_exists('rate_type', $rateplan) ? '('.$rateplan['rate_type'].')' : '').'</option>'."\n";
								} elseif ($ch['idchannel'] == VikChannelManagerConfig::EXPEDIA) {
									if (!isset($expedia_derived_group) && stripos($rateplan['rateAcquisitionType'], 'Derived') !== false) {
										$expedia_derived_group = 1;
										echo '<optgroup label="'.JText::_('VCMDERIVEDRATEPLANS').'">';
									}
									echo '<option value="'.$rateplan['id'].'"'.(array_key_exists('pricingModel', $rateplan) ? ' title="'.$rateplan['pricingModel'].'"' : '').(array_key_exists('vcm_default', $rateplan) ? ' selected="selected"' : '').'>'.$rateplan['name'].' '.(array_key_exists('distributionModel', $rateplan) ? '('.$rateplan['distributionModel'].')' : '').'</option>'."\n";
								} elseif ($ch['idchannel'] == VikChannelManagerConfig::BOOKING) {
									echo '<option value="'.$rateplan['id'].'" title="'.(array_key_exists('policy', $rateplan) ? 'Policy: '.$rateplan['policy'].', ' : '').(array_key_exists('max_persons', $rateplan) ? 'Max Persons: '.$rateplan['max_persons'].(array_key_exists('is_child_rate', $rateplan) && intval($rateplan['is_child_rate']) == 1 ? ' (Derived Rate)' : '') : '').'"'.(strtolower($rateplan['name']) == 'standard rate' || array_key_exists('vcm_default', $rateplan) ? ' selected="selected"' : '').'>'.$rateplan['name'].'</option>'."\n";
								} elseif ($ch['idchannel'] == VikChannelManagerConfig::DESPEGAR) {
									echo '<option value="'.$rateplan['id'].'"'.(array_key_exists('pricingModel', $rateplan) ? ' title="'.$rateplan['pricingModel'].'"' : '').(array_key_exists('vcm_default', $rateplan) ? ' selected="selected"' : '').'>'.$rateplan['name'].(isset($rateplan['ChargeTypeCode']) && in_array((int)$rateplan['ChargeTypeCode'], array(19, 21)) ? ' '.((int)$rateplan['ChargeTypeCode'] == 19 ? '(PerRoomPerNight)' : '(PerPersonPerNight)') : '').'</option>'."\n";
								} elseif ($ch['idchannel'] == VikChannelManagerConfig::OTELZ) {
									echo '<option value="'.$rateplan['id'].'" title="ID '.$rateplan['id'].'"'.(array_key_exists('vcm_default', $rateplan) ? ' selected="selected"' : '').'>'.$rateplan['name'].'</option>'."\n";
								} elseif ($ch['idchannel'] == VikChannelManagerConfig::GARDAPASS) {
									echo '<option value="'.$rateplan['id'].'" title="ID '.$rateplan['id'].'"'.(array_key_exists('vcm_default', $rateplan) ? ' selected="selected"' : '').'>'.$rateplan['name'].'</option>'."\n";
								} elseif ($ch['idchannel'] == VikChannelManagerConfig::BEDANDBREAKFASTIT) {
									echo '<option value="-1">Standard</option>'."\n";
								} elseif ($ch['idchannel'] == VikChannelManagerConfig::BEDANDBREAKFASTEU) {
									echo '<option value="-1">Standard</option>'."\n";
								} elseif ($ch['idchannel'] == VikChannelManagerConfig::BEDANDBREAKFASTNL) {
									echo '<option value="-1">Standard</option>'."\n";
								} elseif ($ch['idchannel'] == VikChannelManagerConfig::FERATEL) {
									echo '<option value="'.$rateplan['id'].'"'.(array_key_exists('price_rule', $rateplan) ? ' title="'.$rateplan['price_rule'].'"' : '').(array_key_exists('vcm_default', $rateplan) ? ' selected="selected"' : '').'>'.$rateplan['name'].'</option>'."\n";
								} elseif ($ch['idchannel'] == VikChannelManagerConfig::HOSTELWORLD) {
									$title = '';
									if (!empty($rateplan['default'])) {
										$title .= 'Default: ' . $rateplan['default'];
									}
									if (!empty($rateplan['active'])) {
										$title .= ' Active: ' . $rateplan['active'];
									}
									if (!empty($rateplan['currency'])) {
										$title .= ' Currency: ' . $rateplan['currency'];
									}
									echo '<option value="'.$rateplan['id'].'"'.(!empty($title) ? ' title="' . $title . '"' : '').(array_key_exists('vcm_default', $rateplan) ? ' selected="selected"' : '').'>'.$rateplan['name'].'</option>'."\n";
								} elseif (isset($rateplan['id']) && isset($rateplan['name'])) {
									/**
									 * Default statement for new channels or for those that
									 * do not need to display any particular information.
									 * 
									 * @since 	1.6.22
									 */
									echo '<option value="'.$rateplan['id'].'"'.(array_key_exists('vcm_default', $rateplan) ? ' selected="selected"' : '').'>'.$rateplan['name'].'</option>'."\n";
								}
							}
							if (isset($expedia_derived_group)) {
								unset($expedia_derived_group);
								echo '</optgroup>';
							}
							?>
								</select>
							<?php
						}
						if ($ch['idchannel'] == VikChannelManagerConfig::BOOKING) {
							$cookie_ariprmodel = $cookie->get('vcmAriPrModel'.$ch['idchannel'], '', 'string');
							?>
								<select name="rooms[<?php echo $idroom; ?>][rplanarimode][<?php echo $ch['idchannel']; ?>]" class="vcmratespush-channel-subsel">
									<option value="person" title="<?php echo addslashes(JText::_('VCMRARBCOMPERSONPMODELTIP')); ?>"<?php echo $cookie_ariprmodel == 'person' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMRARBCOMPERSONPMODEL'); ?></option>
									<option value="los" title="<?php echo addslashes(JText::_('VCMRARBCOMLOSPMODELTIP')); ?>"<?php echo $cookie_ariprmodel == 'los' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMRARBCOMLOSPMODEL'); ?></option>
									<option value="any"><?php echo JText::_('VCMRARBCOMANYPMODEL'); ?></option>
								</select>
							<?php
						}
						?>
							</div>
						</div>
					<?php
					}
					if ($vbo_update === true) {
						?>
						<div class="vcmratespush-channel-wrap vcmratespush-channel-wrap-vbo">
							<span class="vbotasp vbo vcmavpush-channel-cont">
								<i class="vboicn-home"></i>
								<label for="ch<?php echo $idroom; ?>vbo"><?php echo JText::_('VCMUPDATEVBOBOOKINGS'); ?></label> 
								<input type="checkbox" class="vcm-avpush-checkbox check-<?php echo $idroom; ?>" name="rooms[<?php echo $idroom; ?>][channels][]" id="ch<?php echo $idroom; ?>vbo" value="vbo" />
							</span>
							<div class="vcmratespush-channel-rateplans chrplans<?php echo $idroom; ?>" id="pricingch<?php echo $idroom; ?>vbo">
								<i class="vboicn-info vcmjtooltip" title="<?php echo addslashes(JText::_('VCMCUSTOMRIBEWARNMESS')); ?>"></i>
								<select name="rooms[<?php echo $idroom; ?>][rplans][vbo]">
									<option value=""><?php echo JText::_('VCMCUSTOMRIBEWARN'); ?></option>
								</select>
							</div>
						</div>
						<?php
					}
					?>
					</div>
				</div>
			</div>
		</div>
		
	<?php } ?>
	<br clear="all" />
	<div class="vcm-ratespush-advanced">
		<button type="button" id="vcm-ratespush-advancedopt" class="btn btn-primary"><i class="vboicn-cog"></i><?php echo JText::_('VCMRATESPUSHADVOPT'); ?></button>
	</div>
	<?php
	if (isset($_REQUEST['e4j_debug']) && (int)$_REQUEST['e4j_debug'] == 1) {
		echo '<input type="hidden" name="e4j_debug" value="1" />'."\n";
	}
	?>
	<input type="hidden" name="max_nodes" value="<?php echo $max_nodes; ?>" />
	<input type="hidden" name="multi" value="1" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikchannelmanager" />
</form>

<div style="clear: both;"></div>

<div class="vcm-loading-overlay">
	<div class="vcm-loading-dot vcm-loading-dot1"></div>
	<div class="vcm-loading-dot vcm-loading-dot2"></div>
	<div class="vcm-loading-dot vcm-loading-dot3"></div>
	<div class="vcm-loading-dot vcm-loading-dot4"></div>
	<div class="vcm-loading-dot vcm-loading-dot5"></div>
</div>

<script type="text/javascript">
/* Loading Overlay */
function vcmShowLoading() {
	jQuery(".vcm-loading-overlay").show();
}

/* Show loading when sending CUSTA_RQ to prevent double submit */
Joomla.submitbutton = function(task) {
	if ( task == 'ratespushsubmit' ) {
		vcmShowLoading();
	}
	Joomla.submitform(task, document.adminForm);
}

function vcmSetDefaultRates(rid, sel) {
	var opt_val = jQuery('option:selected', sel).attr('data-defcost');
	opt_val = opt_val.length ? opt_val : '0.00';
	jQuery('#defrateslbl_'+rid).text(opt_val);
	jQuery('#defrates_'+rid).val(opt_val);
}

function vcmToggleDefRateInp(rid) {
	jQuery('#defrateslbl_' + rid).hide();
	jQuery('#defrates_' + rid).show();
}

jQuery(document).ready(function() {
	jQuery('.vcm-avpush-checkbox').change(function() {
		jQuery('.vcm-avpush-checkbox').each(function(k, v) {
			if (jQuery(this).prop('disabled') !== true && jQuery(this).prop('checked') === true) {
				jQuery('#pricing'+jQuery(this).attr('id')).fadeIn();
			} else {
				jQuery('#pricing'+jQuery(this).attr('id')).fadeOut();
			}
		});
	});
	jQuery('#vcm-ratespush-advancedopt').click(function() {
		jQuery('.vcmratespush-channel-rateplans').each(function(k, v) {
			var rplan_name = jQuery(v).find('select').first().attr('name');
			if (rplan_name.length) {
				jQuery(v).append('<input type="text" class="vcm-ratespush-smallinp" value="<?php echo VikChannelManager::getCurrencyName(); ?>" name="'+rplan_name.replace('rplans', 'cur_rplans')+'" />');
			}
		});
		jQuery(this).fadeOut();
	});
	if (jQuery.isFunction(jQuery.fn.tooltip)) {
		jQuery(".vcmjtooltip").tooltip();
	}
});
</script>
