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
	$rplans = json_decode($otar['otapricing'], true);
	if (!isset($rplans['RatePlan'])) {
		continue;
	}
	foreach ($rplans['RatePlan'] as $rplan) {
		$otarplans[$rplan['id']] = array(
			'name' => $rplan['name'] . (isset($rplan['distributionModel']) ? ' (' . $rplan['distributionModel'] . ')' : ''),
			'room_name' => $otar['otaroomname'],
			'room_id' => $otar['idroomota'],
		);
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
		<span class="vcm-download-inline-mess"><?php echo JText::sprintf('VCMCHPROMLOADALL', 'Expedia'); ?></span>
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
			$promo_img = VCM_ADMIN_URI . 'assets/css/images/bcom_promo_basic.png';
			if (!empty($promo->name)) {
				if ($promo->name == 'SAME_DAY_PROMOTION') {
					$promo_img = VCM_ADMIN_URI . 'assets/css/images/bcom_promo_lastminute.png';
				} elseif ($promo->name == 'EARLY_BOOKING_PROMOTION') {
					$promo_img = VCM_ADMIN_URI . 'assets/css/images/bcom_promo_earlybooker.png';
				} elseif ($promo->name == 'MULTI_NIGHT_PROMOTION') {
					$promo_img = VCM_ADMIN_URI . 'assets/css/images/promo_multinights.png';
				}
			}
			$promo_name_vals = array();
			if (!empty($promo->code)) {
				array_push($promo_name_vals, $promo->code);
			}
			if (!count($promo_name_vals)) {
				array_push($promo_name_vals, $promo->id);
			}
			$promo_name = implode(' - ', $promo_name_vals);
			?>
			<li class="row flat-table-row vcm-promotion-row <?php echo property_exists($promo, 'status') && $promo->status == 'ACTIVE' ? 'vcm-promotion-isactive' : 'vcm-promotion-isdeactive'; ?>">
				
				<div class="col-sm-3 vcm-promo-row-col-name">
					
					<div class="vcm-promo-row-left">
						
						<div class="vcm-promo-row-img">
							<img src="<?php echo $promo_img; ?>" />
						</div>

					</div>

					<div class="vcm-promo-row-right">

						<div class="col-invisible col-name-invisible">
							<?php echo JText::_('VCMBPROMOHNAME'); ?>
						</div>

						<div class="vcm-promo-row-name">
							<a href="index.php?option=com_vikchannelmanager&task=egpromoedit&cid[]=<?php echo $promo->id; ?>"><?php echo $promo_name; ?></a>
						</div>

						<div class="vcm-promo-row-links">
							<span>
								<a href="index.php?option=com_vikchannelmanager&task=egpromoedit&cid[]=<?php echo $promo->id; ?>"><i class="vboicn-pencil2"></i> <?php echo JText::_('EDIT'); ?></a>
							</span>
						<?php
						if (property_exists($promo, 'status') && $promo->status == 'ACTIVE') {
							// show that it's active
							?>
							<span>
								<i class="vboicn-play2"></i> <?php echo JText::_('VCMPROMSTATUSACTIVE'); ?>
							</span>
							<?php
						} else {
							// show as inactive
							?>
							<span>
								<i class="vboicn-pause"></i> <?php echo JText::_('VCMPROMSTATUSINACTIVE'); ?>
							</span>
							<?php
						}
						?>
							
						</div>

					</div>

				</div>
				
				<div class="col-sm-2 vcm-promo-row-col-disc">

					<div class="col-invisible col-disc-invisible">
						<?php echo JText::_('VCMBPROMOHDISC'); ?>
					</div>
					
					<div class="vcm-promo-row-discount-val">
						<span><i class="vboicn-price-tags"></i> <?php echo isset($promo->discount->value) ? $promo->discount->value : '?'; ?> %</span>
					</div>
					<div class="vcm-promo-row-discount-rates">
						<ul class="vcm-promo-row-discount-rates-ul">
							<li class="vcm-promo-row-discount-rate">
								<span><?php echo $promo->discount->__typename; ?></span>
							</li>
						<?php
						if (!empty($promo->discount->applicableNight) && (int)$promo->discount->applicableNight > 1) {
							?>
							<li class="vcm-promo-row-discount-rate">
								<span>Applicable Night: <?php echo $promo->discount->applicableNight; ?></span>
							</li>
							<?php
						}
						if (isset($promo->discount->isRecurring) && $promo->discount->isRecurring === true) {
							?>
							<li class="vcm-promo-row-discount-rate">
								<span><?php echo JText::_('VCMPROMMULTINRECUR'); ?></span>
							</li>
							<?php
						}
						// check discount per weekdays for promos of type day-of-the-week discount
						$dow_props = array(
							'monday',
							'tuesday',
							'wednesday',
							'thursday',
							'friday',
							'saturday',
							'sunday',
						);
						foreach ($dow_props as $dow_prop) {
							if (isset($promo->discount->{$dow_prop})) {
								?>
							<li class="vcm-promo-row-discount-rate">
								<span><?php echo ucwords($dow_prop) . ': ' . $promo->discount->{$dow_prop} . ' %'; ?></span>
							</li>
								<?php
							}
						}
						?>
						</ul>
					</div>

				</div>
				
				<div class="col-sm-3 vcm-promo-row-col-details">

					<div class="col-invisible col-details-invisible">
						<?php echo JText::_('VCMBPROMOHDET'); ?>
					</div>

			<?php
			if (isset($promo->restrictions)) {
				if (!empty($promo->restrictions->isMemberOnly)) {
					// property "isMemberOnly" can be null or false
					?>
					<div class="vcm-promo-row-details-det">
						<div class="vcm-promo-row-details-det-main">Members Only</div>
						<div class="vcm-promo-row-details-det-sub">Promotion is applicable for only members shopping on Expedia</div>
					</div>
					<?php
				}
				if (!empty($promo->restrictions->isMobileUserOnly)) {
					// property "isMobileUserOnly" can be null or false
					?>
					<div class="vcm-promo-row-details-det">
						<div class="vcm-promo-row-details-det-main">Mobile Only</div>
						<div class="vcm-promo-row-details-det-sub">Promotion is applicable only for travelers booking on the mobile device</div>
					</div>
					<?php
				}
				if (isset($promo->restrictions->minLengthOfStay)) {
					?>
					<div class="vcm-promo-row-details-det">
						<div class="vcm-promo-row-details-det-main"><?php echo JText::_('VCMRARRESTRMINLOS') . ' ' . $promo->restrictions->minLengthOfStay; ?></div>
					</div>
					<?php
				}
				if (isset($promo->restrictions->maxLengthOfStay)) {
					?>
					<div class="vcm-promo-row-details-det">
						<div class="vcm-promo-row-details-det-main"><?php echo JText::_('VCMRARRESTRMAXLOS') . ' ' . $promo->restrictions->maxLengthOfStay; ?></div>
					</div>
					<?php
				}
				if (!empty($promo->restrictions->minAdvanceBookingDays) && $promo->restrictions->minAdvanceBookingDays > 0) {
					?>
					<div class="vcm-promo-row-details-det">
						<div class="vcm-promo-row-details-det-main"><?php echo JText::_('VCMBCAPNMINOFF') . ' ' . $promo->restrictions->minAdvanceBookingDays; ?></div>
					</div>
					<?php
				}
				if (!empty($promo->restrictions->maxAdvanceBookingDays) && $promo->restrictions->maxAdvanceBookingDays > 0) {
					?>
					<div class="vcm-promo-row-details-det">
						<div class="vcm-promo-row-details-det-main"><?php echo JText::_('VCMBCAPNMAXOFF') . ' ' . $promo->restrictions->maxAdvanceBookingDays; ?></div>
					</div>
					<?php
				}
				if (isset($promo->restrictions->bookingLocalDateTimeFrom)) {
					?>
					<div class="vcm-promo-row-details-det">
						<div class="vcm-promo-row-details-det-main">Reservations Start Date: <?php echo $promo->restrictions->bookingLocalDateTimeFrom; ?></div>
						<div class="vcm-promo-row-details-det-sub">Beginning of the reservation date range for which this promotion is applicable.</div>
					</div>
					<?php
				}
				if (isset($promo->restrictions->bookingLocalDateTimeTo)) {
					?>
					<div class="vcm-promo-row-details-det">
						<div class="vcm-promo-row-details-det-main">Reservations End Date: <?php echo $promo->restrictions->bookingLocalDateTimeTo; ?></div>
						<div class="vcm-promo-row-details-det-sub">End of the reservation date range for which this promotion is applicable.</div>
					</div>
					<?php
				}
			}
			?>

				</div>
				
				<div class="col-sm-2 vcm-promo-row-col-rooms">

					<div class="col-invisible col-rooms-invisible">
						<?php echo JText::_('VCMBPROMOHROOMRATES'); ?>
					</div>

					<div class="vcm-promo-row-rooms">
					<?php
					if (isset($promo->eligibleRatePlans) && is_array($promo->eligibleRatePlans) && count($promo->eligibleRatePlans)) {
						?>
						<ul class="vcm-promo-rooms-ul">
						<?php
						foreach ($promo->eligibleRatePlans as $elig_rplan) {
							if (!is_object($elig_rplan) || !isset($elig_rplan->id)) {
								// invalid eligible rate plan object structure
								continue;
							}
							$elig_rplan_id = $elig_rplan->id;
							if (!isset($otarplans[$elig_rplan_id])) {
								// rate plan ID not found in mapping
								?>
							<li class="vcm-promo-row-room">
								<span>Rate Plan ID <?php echo $elig_rplan_id; ?></span>
							</li>
								<?php
							} else {
								// eligible rate plan found in mapping
								?>
							<li class="vcm-promo-row-room">
								<span><?php echo $otarplans[$elig_rplan_id]['room_name'] . ' (' . $otarplans[$elig_rplan_id]['room_name'] . ') - ' . $otarplans[$elig_rplan_id]['name'] . ' (' . $elig_rplan_id . ')'; ?></span>
							</li>
								<?php
							}
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
					if (isset($promo->restrictions) && isset($promo->restrictions->travelDateFrom) && isset($promo->restrictions->travelDateTo)) {
						?>
						<div class="vcm-promo-row-date">
							<span class="vcm-promo-row-date-fromto"><?php echo $promo->restrictions->travelDateFrom; ?> - <?php echo $promo->restrictions->travelDateTo; ?></span>
						</div>
						<?php
					}
					// check if there are any blackout dates
					if (isset($promo->blackoutDates) && is_array($promo->blackoutDates) && count($promo->blackoutDates)) {
						?>
						<div class="vcm-promo-row-date">
							<strong><?php echo JText::_('VCMBPROMEXCLDATES'); ?></strong>
						</div>
						<?php
						foreach ($promo->blackoutDates as $blackout) {
							if (!is_object($blackout) || !isset($blackout->travelDateFrom) || !isset($blackout->travelDateTo)) {
								continue;
							}
							?>
							<div class="vcm-promo-row-date vcm-promo-row-date-excluded">
								<span class="vcm-promo-row-date-fromto"><?php echo $blackout->travelDateFrom; ?> - <?php echo $blackout->travelDateTo; ?></span>
							</div>
							<?php
						}
					} else {
						// print "for every day"
						?>
						<div class="vcm-promo-row-date">
							<strong><?php echo JText::_('VCMBPROMEVERYWDAY'); ?></strong>
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
	<input type="hidden" name="task" value="egpromo" />
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
