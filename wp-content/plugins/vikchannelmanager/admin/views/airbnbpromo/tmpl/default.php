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

// calendar color codes with Airbnb
$airbnb_calendar_color_codes = array(
	'red',
	'brown',
	'orange',
	'yellow',
	'green',
	'blue',
	'purple',
	'pink',
);

// take all the rate plans and rooms from the mapped room types
$otarplans = array();
$hotel_name = '';
foreach ($this->otarooms as $otar) {
	// make sure the hotel ID matches with the active one
	if (!empty($otar['prop_params'])) {
		$prop_params = json_decode($otar['prop_params'], true);
		if (isset($prop_params['user_id']) && $prop_params['user_id'] != $this->channel['params']['user_id']) {
			// skip this room mapping as it's for a different hotel ID
			continue;
		}
	}
	$hotel_name = !empty($otar['prop_name']) && $otar['prop_name'] != $this->channel['params']['user_id'] ? $otar['prop_name'] : $hotel_name;
	$rplans = json_decode($otar['otapricing'], true);
	if (!isset($rplans['RatePlan'])) {
		continue;
	}
	foreach ($rplans['RatePlan'] as $rplan) {
		$otarplans[$rplan['id']] = array(
			'name' => $rplan['name'],
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

<h3><?php echo 'Host ID ' . $this->channel['params']['user_id'] . (!empty($hotel_name) ? ' - ' . $hotel_name : ''); ?></h3>

<form action="index.php?option=com_vikchannelmanager" method="post" name="adminForm" id="adminForm">
<?php
if (is_string($this->promotions) && empty($this->promotions)) {
	// display the button to load the promotions by making the request
	?>
	<div class="vcm-download-container">
		<button type="button" id="vcm-load-promotions" class="btn vcm-config-btn" onclick="vcmLoadPromotions();"><i class="vboicn-cloud-download"></i> <span><?php echo JText::_('VCMLOADPROMO'); ?></span></button>
		<span class="vcm-download-inline-mess"><?php echo JText::sprintf('VCMCHPROMLOADALL', 'Airbnb'); ?></span>
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
			if (!empty($promo->pricing_rule) && !empty($promo->pricing_rule->rule_type)) {
				if ($promo->pricing_rule->rule_type == 'BOOKED_WITHIN_AT_MOST_X_DAYS') {
					$promo_img = VCM_ADMIN_URI . 'assets/css/images/bcom_promo_lastminute.png';
				} elseif ($promo->pricing_rule->rule_type == 'BOOKED_BEYOND_AT_LEAST_X_DAYS') {
					$promo_img = VCM_ADMIN_URI . 'assets/css/images/bcom_promo_earlybooker.png';
				} elseif ($promo->pricing_rule->rule_type == 'STAYED_AT_LEAST_X_DAYS') {
					$promo_img = VCM_ADMIN_URI . 'assets/css/images/promo_multinights.png';
				}
			}
			$promo_name_vals = array();
			if (!empty($promo->title)) {
				array_push($promo_name_vals, $promo->title);
			}
			if (!count($promo_name_vals)) {
				array_push($promo_name_vals, $promo->id);
			}
			$promo_name = implode(' - ', $promo_name_vals);
			?>
			<li class="row flat-table-row vcm-promotion-row vcm-promotion-isactive">
				
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
							<a href="index.php?option=com_vikchannelmanager&task=airbnbpromoedit&cid[]=<?php echo $promo->id; ?>&index=<?php echo $k; ?>"><?php echo $promo_name; ?></a>
						</div>

						<div class="vcm-promo-row-links">
							<span>
								<a href="index.php?option=com_vikchannelmanager&task=airbnbpromoedit&cid[]=<?php echo $promo->id; ?>&index=<?php echo $k; ?>"><i class="vboicn-pencil2"></i> <?php echo JText::_('EDIT'); ?></a>
							</span>	
						</div>

					</div>

				</div>
				
				<div class="col-sm-2 vcm-promo-row-col-disc">

					<div class="col-invisible col-disc-invisible">
						<?php echo JText::_('VCMBPROMOHDISC'); ?>
					</div>
					
					<div class="vcm-promo-row-discount-val">
						<span><i class="vboicn-price-tags"></i> <?php echo isset($promo->pricing_rule->price_change) ? $promo->pricing_rule->price_change : '?'; ?> %</span>
					</div>
					<div class="vcm-promo-row-discount-rates">
						<ul class="vcm-promo-row-discount-rates-ul">
							<li class="vcm-promo-row-discount-rate">
								<span><?php echo $promo->pricing_rule->rule_type; ?></span>
							</li>
						<?php
						if (!empty($promo->pricing_rule->threshold_one)) {
							?>
							<li class="vcm-promo-row-discount-rate">
								<span>Threshold: <?php echo $promo->pricing_rule->threshold_one; ?></span>
							</li>
							<?php
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
			if (!empty($promo->availability_rules)) {
				if (!empty($promo->availability_rules->closed_for_checkin)) {
					?>
					<div class="vcm-promo-row-details-det">
						<div class="vcm-promo-row-details-det-main">Closed for check-in</div>
						<div class="vcm-promo-row-details-det-sub"><?php echo is_array($promo->availability_rules->closed_for_checkin) ? implode(', ', $promo->availability_rules->closed_for_checkin) : $promo->availability_rules->closed_for_checkin; ?></div>
					</div>
					<?php
				}
				if (!empty($promo->availability_rules->closed_for_checkout)) {
					?>
					<div class="vcm-promo-row-details-det">
						<div class="vcm-promo-row-details-det-main">Closed for check-out</div>
						<div class="vcm-promo-row-details-det-sub"><?php echo is_array($promo->availability_rules->closed_for_checkout) ? implode(', ', $promo->availability_rules->closed_for_checkout) : $promo->availability_rules->closed_for_checkout; ?></div>
					</div>
					<?php
				}
			}

				if (isset($promo->color) && isset($airbnb_calendar_color_codes[(int)$promo->color])) {
					?>
					<div class="vcm-promo-row-details-det">
						<div class="vcm-promo-row-details-det-main">Calendar Color Code</div>
						<div class="vcm-promo-row-details-det-sub">
							<span style="display: inline-block; width: 20px; height: 20px; border-radius: 50%; border: 1px solid #eee; background-color: <?php echo $airbnb_calendar_color_codes[(int)$promo->color]; ?>;"></span>
						</div>
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
					if (!empty($promo->listings) && is_array($promo->listings)) {
						?>
						<ul class="vcm-promo-rooms-ul">
						<?php
						foreach ($promo->listings as $listing) {
							if (!isset($this->otalistings[$listing])) {
								// listing id not mapped in VCM
								continue;
							}
							?>
							<li class="vcm-promo-row-room">
								<span><?php echo $this->otalistings[$listing]; ?></span>
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

						<div class="vcm-promo-row-date">
							<span class="vcm-promo-row-date-fromto"><?php echo $promo->since_date; ?> - <?php echo $promo->end_date; ?></span>
						</div>

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
	<input type="hidden" name="task" value="airbnbpromo" />
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
