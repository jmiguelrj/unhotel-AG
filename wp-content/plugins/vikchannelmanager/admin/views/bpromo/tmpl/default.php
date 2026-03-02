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

// take all the rate plans and rooms from the mapped room types
$otarplans = array();
$otaroomsinfo = array();
$hotel_name = '';
foreach ($this->otarooms as $otar) {
	// make sure the hotel ID matches with the active one
	if (!empty($otar['prop_params'])) {
		$prop_params = json_decode($otar['prop_params'], true);
		if (isset($prop_params['hotelid']) && $prop_params['hotelid'] != $this->channel['params']['hotelid']) {
			// skip this room mapping as it's for a different hotel ID
			continue;
		}
	}
	$hotel_name = !empty($otar['prop_name']) ? $otar['prop_name'] : $hotel_name;
	// get room info
	$otaroomsinfo[$otar['idroomota']] = $otar['otaroomname'];
	//
	$rplans = json_decode($otar['otapricing'], true);
	if (!isset($rplans['RatePlan'])) {
		continue;
	}
	foreach ($rplans['RatePlan'] as $rplan) {
		$otarplans[$rplan['id']] = $rplan['name'];
	}
}

?>

<div class="vcm-loading-overlay">
	<div class="vcm-loading-dot vcm-loading-dot1"></div>
	<div class="vcm-loading-dot vcm-loading-dot2"></div>
	<div class="vcm-loading-dot vcm-loading-dot3"></div>
	<div class="vcm-loading-dot vcm-loading-dot4"></div>
	<div class="vcm-loading-dot vcm-loading-dot5"></div>
</div>

<h3><?php echo JText::_('VCMHOTELID') . ' ' . $this->channel['params']['hotelid'] . (!empty($hotel_name) ? ' - ' . $hotel_name : ''); ?></h3>

<form action="index.php?option=com_vikchannelmanager" method="post" name="adminForm" id="adminForm">
<?php
if (is_string($this->promotions) && empty($this->promotions)) {
	// display the button to load the promotions by making the request
	?>
	<div class="vcm-download-container">
		<button type="button" id="vcm-load-promotions" class="btn vcm-config-btn" onclick="vcmLoadPromotions();"><i class="vboicn-cloud-download"></i> <span><?php echo JText::_('VCMLOADPROMO'); ?></span></button>
		<span class="vcm-download-inline-mess"><?php echo JText::_('VCMBPROMLOADALL'); ?></span>
		<input type="hidden" name="loadpromo" id="loadpromo" value="1" />
	</div>
	<?php
} elseif (is_array($this->promotions) && count($this->promotions)) {
	// display the list of promotions
	?>
	<div class="flat-table vcm-promotions-container">
		
		<div class="row flat-table-header">
			
			<div class="col-xs-3">
				<?php echo JText::_('VCMBPROMOHNAME'); ?>
			</div>
			<div class="col-xs-2">
				<?php echo JText::_('VCMBPROMOHDISC'); ?>
			</div>
			<div class="col-xs-3">
				<?php echo JText::_('VCMBPROMOHDET'); ?>
			</div>
			<div class="col-xs-2">
				<?php echo JText::_('VCMBPROMOHROOMRATES'); ?>
			</div>
			<div class="col-xs-2">
				<?php echo JText::_('VCMBPROMOHDATES'); ?>
			</div>

		</div>

		<ul class="vcm-promotions-list">
		<?php
		foreach ($this->promotions as $k => $promo) {
			$promo_img = '';
			if ($promo->type == 'basic') {
				$promo_img = VCM_ADMIN_URI . 'assets/css/images/bcom_promo_basic.png';
			} elseif ($promo->type == 'last_minute') {
				$promo_img = VCM_ADMIN_URI . 'assets/css/images/bcom_promo_lastminute.png';
			} elseif ($promo->type == 'early_booker') {
				$promo_img = VCM_ADMIN_URI . 'assets/css/images/bcom_promo_earlybooker.png';
			} elseif ($promo->type == 'geo_rate') {
				$promo_img = VCM_ADMIN_URI . 'assets/css/images/bcom_promo_geo_rate.png';
			} elseif ($promo->type == 'mobile_rate') {
				$promo_img = VCM_ADMIN_URI . 'assets/css/images/bcom_promo_mobile_rate.png';
			} elseif ($promo->type == 'business_booker') {
				$promo_img = VCM_ADMIN_URI . 'assets/css/images/bcom_promo_business_booker.png';
			}
			?>
			<li class="row flat-table-row vcm-promotion-row <?php echo property_exists($promo, 'active') && (int)$promo->active > 0 ? 'vcm-promotion-isactive' : 'vcm-promotion-isdeactive'; ?>">
				
				<div class="col-sm-3 vcm-promo-row-col-name">
					
					<div class="vcm-promo-row-left">
						
						<div class="vcm-promo-row-img">
							<img src="<?php echo $promo_img; ?>" title="<?php echo ucwords(str_replace('_', ' ', $promo->type)); ?>" />
						</div>

					</div>

					<div class="vcm-promo-row-right">

						<div class="col-invisible col-name-invisible">
							<?php echo JText::_('VCMBPROMOHNAME'); ?>
						</div>

						<div class="vcm-promo-row-name">
							<a href="index.php?option=com_vikchannelmanager&task=bpromoedit&cid[]=<?php echo $promo->id; ?>"><?php echo (string)$promo->name != '-1' ? $promo->name : $promo->id; ?></a>
						</div>

						<div class="vcm-promo-row-links">
							<span>
								<a href="index.php?option=com_vikchannelmanager&task=bpromoedit&cid[]=<?php echo $promo->id; ?>"><i class="vboicn-pencil2"></i> <?php echo JText::_('EDIT'); ?></a>
							</span>
						<?php
						if (property_exists($promo, 'active') && (int)$promo->active > 0) {
							// deactivate link
							?>
							<span>
								<a href="index.php?option=com_vikchannelmanager&task=bpromo.deactivate&promoid=<?php echo $promo->id; ?>" onclick="return confirm('<?php echo addslashes(JText::_('VCMBPROMDEACTIVCONF')); ?>');"><i class="vboicn-pause"></i> <?php echo JText::_('VCMBPROMDEACTIV'); ?></a>
							</span>
							<?php
						} else {
							// activate link
							?>
							<span>
								<a href="index.php?option=com_vikchannelmanager&task=bpromo.activate&promoid=<?php echo $promo->id; ?>" onclick="return confirm('<?php echo addslashes(JText::_('VCMBPROMACTIVCONF')); ?>');"><i class="vboicn-play2"></i> <?php echo JText::_('VCMBPROMACTIV'); ?></a>
							</span>
							<?php
						}
						?>
							
						</div>

					<?php
					if (property_exists($promo, 'stats')) {
						?>
						<div class="vcm-promo-row-stats">
						<?php
						foreach ($promo->stats as $stat_key => $stat_value) {
							$val = '';
							if (is_string($stat_value)) {
								$val = $stat_value;
							} elseif (is_object($stat_value)) {
								if (property_exists($stat_value, 'value') && property_exists($stat_value, 'currency')) {
									$val = $stat_value->currency . ' ' . $stat_value->value;
								}
							}
							?>
							<div class="vcm-promo-row-stat">
								<span class="vcm-promo-row-stat-lbl"><?php echo ucwords(str_replace('_', ' ', $stat_key)); ?></span>
								<span class="vcm-promo-row-stat-val"><?php echo $val; ?></span>
							</div>
							<?php
						}
						?>
						</div>
						<?php
					}
					?>

					</div>

				</div>
				
				<div class="col-sm-2 vcm-promo-row-col-disc">

					<div class="col-invisible col-disc-invisible">
						<?php echo JText::_('VCMBPROMOHDISC'); ?>
					</div>
					
					<div class="vcm-promo-row-discount-val">
						<span><i class="vboicn-price-tags"></i> <?php echo $promo->discount->value; ?> %</span>
					</div>
					<div class="vcm-promo-row-discount-rates">
					<?php
					if (property_exists($promo, 'parent_rates') && property_exists($promo->parent_rates, 'parent_rate')) {
						?>
						<ul class="vcm-promo-row-discount-rates-ul">
						<?php
						if (is_array($promo->parent_rates->parent_rate)) {
							// multiple rate plans
							foreach ($promo->parent_rates->parent_rate as $prate) {
								?>
							<li class="vcm-promo-row-discount-rate">
								<span title="<?php echo $prate->id; ?>"><?php echo isset($otarplans[$prate->id]) ? $otarplans[$prate->id] : $prate->id; ?></span>
							</li>
								<?php
							}
						} else {
							// just one rate plan
							?>
							<li class="vcm-promo-row-discount-rate">
								<span title="<?php echo $promo->parent_rates->parent_rate->id; ?>"><?php echo isset($otarplans[$promo->parent_rates->parent_rate->id]) ? $otarplans[$promo->parent_rates->parent_rate->id] : $promo->parent_rates->parent_rate->id; ?></span>
							</li>
							<?php
						}
						?>
						</ul>
						<?php
					}
					?>
					</div>

				</div>
				
				<div class="col-sm-3 vcm-promo-row-col-details">

					<div class="col-invisible col-details-invisible">
						<?php echo JText::_('VCMBPROMOHDET'); ?>
					</div>

					<?php
					$promo_type = '';
					$promo_subtype = '';
					if ($promo->type == 'basic' || $promo->type == 'geo_rate' || $promo->type == 'mobile_rate' || $promo->type == 'business_booker') {
						$promo_type = JText::_('VCMBPROMBSDEALTIT');
						if ($promo->type == 'geo_rate') {
							$promo_type = JText::_('VCMBPROMGEODEALTIT');
						}
						if ($promo->type == 'mobile_rate') {
							$promo_type = JText::_('VCMBPROMMOBILEDEALTIT');
						}
						if ($promo->type == 'business_booker') {
							$promo_type = JText::_('VCMBPROMBUSINESSDEALTIT');
						}
						if (property_exists($promo, 'book_date') && $promo->book_date->start != '-1') {
							$promo_subtype = JText::sprintf('VCMBPROMBOOKFROMTO', $promo->book_date->start, $promo->book_date->end);
						}
					} elseif ($promo->type == 'last_minute') {
						$promo_type = JText::_('VCMBPROMLMDEALTIT');
						if (property_exists($promo, 'last_minute') && $promo->last_minute->unit != '-1') {
							$promo_subtype = $promo->last_minute->unit == 'day' ? JText::sprintf('VCMBPROMWITHIND', $promo->last_minute->value) : JText::sprintf('VCMBPROMWITHINH', $promo->last_minute->value);
						}
					} elseif ($promo->type == 'early_booker') {
						$promo_type = JText::_('VCMBPROMEBDEALTIT');
						if (property_exists($promo, 'early_booker') && $promo->early_booker->value != '-1') {
							$promo_subtype = JText::sprintf('VCMBPROMBEFORED', $promo->early_booker->value);
						}
					}
					if ($promo->target_channel != 'public' && $promo->target_channel != 'app' && strpos($promo->target_channel, '_pos') === false) {
						// secret deal
						$promo_type .= ' - ' . JText::_('VCMBPROMASECRETD');
					} elseif (strpos($promo->target_channel, '_pos') !== false && $promo->type == 'geo_rate') {
						// geo rate
						$promo_type .= ' - ' . ucwords(str_replace('_', ' ', str_replace('_pos', '', $promo->target_channel)));
					} elseif ($promo->target_channel == 'app') {
						// mobile rate
						$promo_type .= ' - Mobile';
					}
					?>
					<div class="vcm-promo-row-details-det">
						<div class="vcm-promo-row-details-det-main"><?php echo $promo_type; ?></div>
						<div class="vcm-promo-row-details-det-sub"><?php echo $promo_subtype; ?></div>
					</div>

					<div class="vcm-promo-row-details-det">
						<div class="vcm-promo-row-details-det-main"><?php echo JText::_('VCMBPROMAMINNIGHTS'); ?></div>
						<div class="vcm-promo-row-details-det-sub"><?php echo property_exists($promo, 'min_stay_through') && (int)$promo->min_stay_through > 0 ? $promo->min_stay_through : JText::_('VCMBPROMAMINNIGHTSAUTO'); ?></div>
					</div>

				<?php
				if (property_exists($promo, 'non_refundable') && (int)$promo->non_refundable > 0) {
					?>
					<div class="vcm-promo-row-details-det">
						<div class="vcm-promo-row-details-det-main"><?php echo JText::_('VCMBPROMONONREF'); ?></div>
					</div>
					<?php
				}
				if (property_exists($promo, 'no_cc_promotion') && (int)$promo->no_cc_promotion > 0) {
					?>
					<div class="vcm-promo-row-details-det">
						<div class="vcm-promo-row-details-det-main"><?php echo JText::_('VCMBPROMONOCRED'); ?></div>
					</div>
					<?php
				}
				?>

				</div>
				
				<div class="col-sm-2 vcm-promo-row-col-rooms">

					<div class="col-invisible col-rooms-invisible">
						<?php echo JText::_('VCMBPROMOHROOMRATES'); ?>
					</div>

					<div class="vcm-promo-row-rooms">
				<?php
				if (property_exists($promo, 'rooms') && property_exists($promo->rooms, 'room')) {
					?>
						<ul class="vcm-promo-rooms-ul">
					<?php
					if (is_array($promo->rooms->room)) {
						// multiple rooms
						foreach ($promo->rooms->room as $proom) {
							?>
							<li class="vcm-promo-row-room">
								<span title="<?php echo $proom->id; ?>"><?php echo isset($otaroomsinfo[$proom->id]) ? $otaroomsinfo[$proom->id] : $proom->id; ?></span>
							</li>
							<?php
						}
					} else {
						// one room
						?>
							<li class="vcm-promo-row-room">
								<span title="<?php echo $promo->rooms->room->id; ?>"><?php echo isset($otaroomsinfo[$promo->rooms->room->id]) ? $otaroomsinfo[$promo->rooms->room->id] : $promo->rooms->room->id; ?></span>
							</li>
						<?php
					}
					?>
						</ul>
					<?php
				}
				?>
					</div>
					
				</div>
				
				<div class="col-sm-2 vcm-promo-row-col-dates">

					<div class="col-invisible col-dates-invisible">
						<?php echo JText::_('VCMBPROMOHDATES'); ?>
					</div>
					
					<div class="vcm-promo-row-dates">

					<?php
					if ($promo->stay_date->start != '-1') {
						?>
						<div class="vcm-promo-row-date">
							<span class="vcm-promo-row-date-fromto"><?php echo $promo->stay_date->start; ?> - <?php echo $promo->stay_date->end; ?></span>
						</div>
						<?php
					}
					// count active weekdays
					$only_some_wdays = false;
					if (property_exists($promo->stay_date, 'active_weekdays') && property_exists($promo->stay_date->active_weekdays, 'active_weekday')) {
						if ((is_string($promo->stay_date->active_weekdays->active_weekday) && strlen($promo->stay_date->active_weekdays->active_weekday)) || (is_array($promo->stay_date->active_weekdays->active_weekday) && count($promo->stay_date->active_weekdays->active_weekday) < 7)) {
							$only_some_wdays = true;
						}
					}
					if (!$only_some_wdays) {
						?>
						<div class="vcm-promo-row-date">
							<strong><?php echo JText::_('VCMBPROMEVERYWDAY'); ?></strong>
						</div>
						<?php
						// check if some dates are excluded
						if (property_exists($promo->stay_date, 'excluded_dates') && property_exists($promo->stay_date->excluded_dates, 'excluded_date')) {
							$some_dates_excl = false;
							if (is_string($promo->stay_date->excluded_dates->excluded_date) && strlen($promo->stay_date->excluded_dates->excluded_date)) {
								$some_dates_excl = true;
							} elseif (is_array($promo->stay_date->excluded_dates->excluded_date) && count($promo->stay_date->excluded_dates->excluded_date)) {
								$some_dates_excl = true;
							}
							if ($some_dates_excl) {
								?>
						<div class="vcm-promo-row-date">
							<strong><?php echo JText::_('VCMBPROMEXCLDATES'); ?></strong>
						</div>
								<?php
							}
						}
					} else {
						// only some weekdays can be considered as "some dates excluded"
						?>
						<div class="vcm-promo-row-date">
							<strong><?php echo JText::_('VCMBPROMEXCLDATES'); ?></strong>
						</div>
						<?php
					}
					?>

					</div>

				</div>
			</li>
			<?php
		}
		?>
		</ul>

	</div>
	<?php
	if (isset($_REQUEST['e4j_debug']) && (int)$_REQUEST['e4j_debug'] == 1) {
		echo '<pre>'.print_r($this->promotions, true).'</pre><br/>';
	}
} else {
	// no promotions found
	?>
	<p class="warn"><?php echo JText::_('VCMBPROMERRNOACTIVE'); ?></p>
	<?php
}
?>
	<input type="hidden" name="task" value="bpromo" />
</form>

<script type="text/javascript">
/* Loading Overlay */
function vcmShowLoading() {
	jQuery(".vcm-loading-overlay").show();
}
function vcmStopLoading() {
	jQuery(".vcm-loading-overlay").hide();
}
function vcmLoadPromotions() {
	vcmShowLoading();
	jQuery('#vcm-load-promotions').prop('disabled', true);
	jQuery('#adminForm').submit();
}
</script>
