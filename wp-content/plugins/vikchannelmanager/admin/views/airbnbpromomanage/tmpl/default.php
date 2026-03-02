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

JHTML::_('behavior.calendar');

// determine wether we are in edit or new mode
$is_editing = property_exists($this->promotion, 'id');

$wdays_enum_map = array(
	'MONDAY' => JText::_('VCMJQCALMON'),
	'TUESDAY' => JText::_('VCMJQCALTUE'),
	'WEDNESDAY' => JText::_('VCMJQCALWED'),
	'THURSDAY' => JText::_('VCMJQCALTHU'),
	'FRIDAY' => JText::_('VCMJQCALFRI'),
	'SATURDAY' => JText::_('VCMJQCALSAT'),
	'SUNDAY' => JText::_('VCMJQCALSUN'),
);

// listings assigned to the promotion
$active_listings_pool = array();
if ($is_editing && isset($this->promotion->listings) && is_array($this->promotion->listings)) {
	$active_listings_pool = $this->promotion->listings;
}

// take all the rate plans from the mapped room types (we may not need them)
$otarplans = array();
foreach ($this->otarooms as $otar) {
	// make sure the hotel ID matches with the active one
	if (!empty($otar['prop_params'])) {
		$prop_params = json_decode($otar['prop_params'], true);
		if (isset($prop_params['user_id']) && $prop_params['user_id'] != $this->channel['params']['user_id']) {
			// skip this room mapping as it's for a different Host User ID
			continue;
		}
	}
	$rplans = json_decode($otar['otapricing'], true);
	if (!isset($rplans['RatePlan'])) {
		continue;
	}
	foreach ($rplans['RatePlan'] as $rplan) {
		$otarplans[$rplan['id']] = array(
			'rplan_name' => $rplan['name'],
			'room_name'  => $otar['otaroomname'],
			'room_id' 	 => $otar['idroomota'],
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

<form action="index.php?option=com_vikchannelmanager" method="post" name="adminForm" id="adminForm">
	<div class="vcm-mngpromo-container">
		
		<div class="vcm-mngpromo-step">
			
			<div class="vcm-mngpromo-step-intro">
				<h3><?php echo JText::_('VCMBPROMPTYPE'); ?></h3>
				<p><?php echo JText::_('VCMBPROMPTYPEDESC'); ?></p>
			</div>
			
			<div class="vcm-mngpromo-step-cont">
				
				<div class="vcm-mngpromo-typebox<?php echo ($is_editing && $this->promotion->pricing_rule->rule_type == 'SEASONAL_ADJUSTMENT') || !$is_editing ? ' vcm-mngpromo-typebox-active' : ''; ?>" data-type="SEASONAL_ADJUSTMENT">
					<div class="vcm-mngpromo-typebox-inner">
						<div class="vcm-mngpromo-typeimg">
							<img src="<?php echo VCM_ADMIN_URI . 'assets/css/images/bcom_promo_basic.png'; ?>">
						</div>
						<div class="vcm-mngpromo-typetitle">
							<h4><?php echo JText::_('VCMBPROMBSDEALTIT'); ?></h4>
						</div>
						<div class="vcm-mngpromo-typesubtitle">
							<h5><?php echo JText::_('VCMBPROMBSDEALSUB'); ?></h5>
						</div>
					</div>
					<div class="vcm-mngpromo-typedescr">
						<p><?php echo JText::_('VCMBPROMBSDEALDSC1'); ?></p>
						<p><?php echo JText::_('VCMBPROMBSDEALDSC2'); ?></p>
					</div>
				</div>

				<div class="vcm-mngpromo-typebox<?php echo $is_editing && $this->promotion->pricing_rule->rule_type == 'BOOKED_WITHIN_AT_MOST_X_DAYS' ? ' vcm-mngpromo-typebox-active' : ''; ?>" data-type="BOOKED_WITHIN_AT_MOST_X_DAYS">
					<div class="vcm-mngpromo-typebox-inner">
						<div class="vcm-mngpromo-typeimg">
							<img src="<?php echo VCM_ADMIN_URI . 'assets/css/images/bcom_promo_lastminute.png'; ?>">
						</div>
						<div class="vcm-mngpromo-typetitle">
							<h4><?php echo JText::_('VCMBPROMLMDEALTIT'); ?></h4>
						</div>
						<div class="vcm-mngpromo-typesubtitle">
							<h5><?php echo JText::_('VCMBPROMLMDEALSUB'); ?></h5>
						</div>
					</div>
					<div class="vcm-mngpromo-typedescr">
						<p><?php echo JText::_('VCMBPROMLMDEALDSC1'); ?></p>
						<p><?php echo JText::_('VCMBPROMLMDEALDSC3'); ?></p>
					</div>
				</div>

				<div class="vcm-mngpromo-typebox<?php echo $is_editing && $this->promotion->pricing_rule->rule_type == 'BOOKED_BEYOND_AT_LEAST_X_DAYS' ? ' vcm-mngpromo-typebox-active' : ''; ?>" data-type="BOOKED_BEYOND_AT_LEAST_X_DAYS">
					<div class="vcm-mngpromo-typebox-inner">
						<div class="vcm-mngpromo-typeimg">
							<img src="<?php echo VCM_ADMIN_URI . 'assets/css/images/bcom_promo_earlybooker.png'; ?>">
						</div>
						<div class="vcm-mngpromo-typetitle">
							<h4><?php echo JText::_('VCMBPROMEBDEALTIT'); ?></h4>
						</div>
						<div class="vcm-mngpromo-typesubtitle">
							<h5><?php echo JText::_('VCMBPROMEBDEALSUB'); ?></h5>
						</div>
					</div>
					<div class="vcm-mngpromo-typedescr">
						<p><?php echo JText::_('VCMBPROMEBDEALDSC1'); ?></p>
						<p><?php echo JText::_('VCMBPROMEBDEALDSC2'); ?></p>
					</div>
				</div>

				<div class="vcm-mngpromo-typebox<?php echo $is_editing && $this->promotion->pricing_rule->rule_type == 'STAYED_AT_LEAST_X_DAYS' ? ' vcm-mngpromo-typebox-active' : ''; ?>" data-type="STAYED_AT_LEAST_X_DAYS">
					<div class="vcm-mngpromo-typebox-inner">
						<div class="vcm-mngpromo-typeimg">
							<img src="<?php echo VCM_ADMIN_URI . 'assets/css/images/promo_multinights.png'; ?>">
						</div>
						<div class="vcm-mngpromo-typetitle">
							<h4><?php echo JText::_('VCMPROMMULTINDEALTIT'); ?></h4>
						</div>
						<div class="vcm-mngpromo-typesubtitle">
							<h5><?php echo JText::_('VCMPROMMULTINDEALSUB'); ?></h5>
						</div>
					</div>
					<div class="vcm-mngpromo-typedescr">
						<p><?php echo JText::_('VCMPROMMULTINDSC1'); ?></p>
						<p><?php echo JText::_('VCMPROMMULTINDSC2'); ?></p>
					</div>
				</div>

				<input type="hidden" name="promo_type" id="promotype" value="<?php echo $is_editing ? $this->promotion->pricing_rule->rule_type : 'SEASONAL_ADJUSTMENT'; ?>" />
			</div>

		</div>

	<?php
	if ($is_editing) {
	?>
		<div class="vcm-mngpromo-step">

			<div class="vcm-mngpromo-step-intro">
				<h3><?php echo JText::_('VCMPROMSTATUS'); ?></h3>
			</div>

			<div class="vcm-mngpromo-step-cont">

				<div class="vcm-mngpromo-status-toggle vcm-mngpromo-fullblock">
					<input type="radio" name="promo_status_active" id="promo_status_active" value="1" checked="checked">
					<label for="promo_status_active"><?php echo JText::_('VCMPROMSTATUSACTIVE'); ?></label>
				</div>

				<div class="vcm-mngpromo-status-toggle vcm-mngpromo-fullblock">
					<input type="radio" name="promo_status_active" id="promo_status_inactive" value="0">
					<label for="promo_status_inactive"><?php echo JText::_('VCMPROMSTATUSINACTIVE') . ' (' . JText::_('VCMBCAHDELETE') . ')'; ?></label>
				</div>

			</div>

		</div>
	<?php
	}
	?>

		<div class="vcm-mngpromo-step vcm-mngpromo-step-conditional" style="display: none;" data-typecond="BOOKED_BEYOND_AT_LEAST_X_DAYS">

			<div class="vcm-mngpromo-step-intro">
				<h3><?php echo JText::_('VCMBPROMQHOWEARLY'); ?></h3>
			</div>

			<div class="vcm-mngpromo-step-cont">

				<div class="vcm-mngpromo-earlybooker-types">

					<div class="vcm-mngpromo-earlybooker-type vcm-mngpromo-fullblock">
						<?php $inputString = "<input type=\"number\" name=\"adv_book_days_min\" value=\"".($is_editing && isset($this->promotion->pricing_rule->threshold_one) ? (int)$this->promotion->pricing_rule->threshold_one : '0')."\" min=\"28\" step=\"30\" />";?>
						<span><?php echo JText::sprintf("VCMBPROMBEFORED", $inputString . ' (min) ');?></span>
					</div>

				</div>
				
			</div>

		</div>

		<div class="vcm-mngpromo-step vcm-mngpromo-step-conditional" style="display: none;" data-typecond="BOOKED_WITHIN_AT_MOST_X_DAYS">

			<div class="vcm-mngpromo-step-intro">
				<h3><?php echo JText::_('VCMBPROMQHOWEARLY'); ?></h3>
			</div>

			<div class="vcm-mngpromo-step-cont">

				<div class="vcm-mngpromo-earlybooker-types">

					<div class="vcm-mngpromo-earlybooker-type vcm-mngpromo-fullblock">
						<?php $inputString = "<input type=\"number\" name=\"adv_book_days_max\" value=\"".($is_editing && isset($this->promotion->pricing_rule->threshold_one) ? (int)$this->promotion->pricing_rule->threshold_one : '0')."\" min=\"0\" max=\"28\" />";?>
						<span><?php echo JText::sprintf("VCMBPROMBEFORED", $inputString . ' (max) ');?></span>
					</div>

				</div>
				
			</div>

		</div>

		<div class="vcm-mngpromo-step vcm-mngpromo-step-conditional" style="display: none;" data-typecond="STAYED_AT_LEAST_X_DAYS">

			<div class="vcm-mngpromo-step-intro">
				<h3><?php echo JText::_('VCMBPROMQSTAY'); ?></h3>
			</div>

			<div class="vcm-mngpromo-step-cont">

				<div class="vcm-mngpromo-minstay vcm-mngpromo-fullblock">
					<span><?php echo JText::_('VCMBPROMAMINNIGHTS');?></span>
					<input type="number" name="min_stay" value="<?php echo $is_editing && isset($this->promotion->pricing_rule->threshold_one) ? (int)$this->promotion->pricing_rule->threshold_one : (!$is_editing ? '3' : ''); ?>" min="1" max="100" />
				</div>

			</div>

		</div>

		<div class="vcm-mngpromo-step">

			<div class="vcm-mngpromo-step-intro">
				<h3><?php echo JText::_('VCMBPROMQMUCH'); ?></h3>
			</div>

			<div class="vcm-mngpromo-step-cont">

				<div class="vcm-mngpromo-discount">
					<input type="number" name="discount" value="<?php echo ($is_editing && isset($this->promotion->pricing_rule) && isset($this->promotion->pricing_rule->price_change) ? abs($this->promotion->pricing_rule->price_change) : (!$is_editing ? '10' : '')); ?>" step="1" min="1" max="100" />
					<span>%</span>
				</div>

			</div>

		</div>

		<?php
		$current_ctd_wdays = array();
		if ($is_editing && isset($this->promotion->availability_rules) && !empty($this->promotion->availability_rules->closed_for_checkout)) {
			// it is not clear if this is an array of enum strings, or if it's a string with comma-separated weekdays
			$current_ctd_wdays = $this->promotion->availability_rules->closed_for_checkout;
			if (!is_array($current_ctd_wdays)) {
				$current_ctd_wdays = explode(',', $current_ctd_wdays);
			}
		}
		?>
		<div class="vcm-mngpromo-step vcm-mngpromo-step-conditional" style="display: none;" data-typecond="SEASONAL_ADJUSTMENT BOOKED_WITHIN_AT_MOST_X_DAYS BOOKED_BEYOND_AT_LEAST_X_DAYS STAYED_AT_LEAST_X_DAYS">

			<div class="vcm-mngpromo-step-intro">
				<h3>Days in week that guests cannot check out</h3>
			</div>

			<div class="vcm-mngpromo-step-cont">

				<div class="vcm-mngpromo-choice-entry vcm-mngpromo-book-time-top">
					<input type="checkbox" name="ctd_enabled" id="ctd_enabled" value="1" onchange="vcmPromoDowToggle(this.checked, 'ctd');"<?php echo count($current_ctd_wdays) ? ' checked="checked"' : ''; ?>>
					<label for="ctd_enabled"><?php echo JText::_('VCMRARADDLOSAPPLY'); ?></label>
				</div>

				<div class="vcm-mngpromo-cta-ctd-wdays" data-restrtype="ctd" style="display: <?php echo count($current_ctd_wdays) ? '' : 'none'; ?>;">

					<div class="vcm-mngpromo-discount-dow">
						<span class="vcm-mngpromo-discount-dow-lbl"><?php echo JText::_('VCMJQCALMON'); ?></span>
						<input type="checkbox" name="ctd_wdays[]" value="MONDAY" <?php echo $is_editing && in_array('MONDAY', $current_ctd_wdays) ? 'checked="checked"' : ''; ?>/>
					</div>

					<div class="vcm-mngpromo-discount-dow">
						<span class="vcm-mngpromo-discount-dow-lbl"><?php echo JText::_('VCMJQCALTUE'); ?></span>
						<input type="checkbox" name="ctd_wdays[]" value="TUESDAY" <?php echo $is_editing && in_array('TUESDAY', $current_ctd_wdays) ? 'checked="checked"' : ''; ?>/>
					</div>

					<div class="vcm-mngpromo-discount-dow">
						<span class="vcm-mngpromo-discount-dow-lbl"><?php echo JText::_('VCMJQCALWED'); ?></span>
						<input type="checkbox" name="ctd_wdays[]" value="WEDNESDAY" <?php echo $is_editing && in_array('WEDNESDAY', $current_ctd_wdays) ? 'checked="checked"' : ''; ?>/>
					</div>

					<div class="vcm-mngpromo-discount-dow">
						<span class="vcm-mngpromo-discount-dow-lbl"><?php echo JText::_('VCMJQCALTHU'); ?></span>
						<input type="checkbox" name="ctd_wdays[]" value="THURSDAY" <?php echo $is_editing && in_array('THURSDAY', $current_ctd_wdays) ? 'checked="checked"' : ''; ?>/>
					</div>

					<div class="vcm-mngpromo-discount-dow">
						<span class="vcm-mngpromo-discount-dow-lbl"><?php echo JText::_('VCMJQCALFRI'); ?></span>
						<input type="checkbox" name="ctd_wdays[]" value="FRIDAY" <?php echo $is_editing && in_array('FRIDAY', $current_ctd_wdays) ? 'checked="checked"' : ''; ?>/>
					</div>

					<div class="vcm-mngpromo-discount-dow">
						<span class="vcm-mngpromo-discount-dow-lbl"><?php echo JText::_('VCMJQCALSAT'); ?></span>
						<input type="checkbox" name="ctd_wdays[]" value="SATURDAY" <?php echo $is_editing && in_array('SATURDAY', $current_ctd_wdays) ? 'checked="checked"' : ''; ?>/>
					</div>

					<div class="vcm-mngpromo-discount-dow">
						<span class="vcm-mngpromo-discount-dow-lbl"><?php echo JText::_('VCMJQCALSUN'); ?></span>
						<input type="checkbox" name="ctd_wdays[]" value="SUNDAY" <?php echo $is_editing && in_array('SUNDAY', $current_ctd_wdays) ? 'checked="checked"' : ''; ?>/>
					</div>

				</div>

			</div>

		</div>

		<?php
		$current_cta_wdays = array();
		if ($is_editing && isset($this->promotion->availability_rules) && !empty($this->promotion->availability_rules->closed_for_checkin)) {
			// it is not clear if this is an array of enum strings, or if it's a string with comma-separated weekdays
			$current_cta_wdays = $this->promotion->availability_rules->closed_for_checkin;
			if (!is_array($current_cta_wdays)) {
				$current_cta_wdays = explode(',', $current_cta_wdays);
			}
		}
		?>
		<div class="vcm-mngpromo-step vcm-mngpromo-step-conditional" style="display: none;" data-typecond="SEASONAL_ADJUSTMENT BOOKED_WITHIN_AT_MOST_X_DAYS BOOKED_BEYOND_AT_LEAST_X_DAYS STAYED_AT_LEAST_X_DAYS">

			<div class="vcm-mngpromo-step-intro">
				<h3>Days in week that guests cannot check in</h3>
			</div>

			<div class="vcm-mngpromo-step-cont">

				<div class="vcm-mngpromo-choice-entry vcm-mngpromo-book-time-top">
					<input type="checkbox" name="cta_enabled" id="cta_enabled" value="1" onchange="vcmPromoDowToggle(this.checked, 'cta');"<?php echo count($current_cta_wdays) ? ' checked="checked"' : ''; ?>>
					<label for="cta_enabled"><?php echo JText::_('VCMRARADDLOSAPPLY'); ?></label>
				</div>

				<div class="vcm-mngpromo-cta-ctd-wdays" data-restrtype="cta" style="display: <?php echo count($current_cta_wdays) ? '' : 'none'; ?>;">

					<div class="vcm-mngpromo-discount-dow">
						<span class="vcm-mngpromo-discount-dow-lbl"><?php echo JText::_('VCMJQCALMON'); ?></span>
						<input type="checkbox" name="cta_wdays[]" value="MONDAY" <?php echo $is_editing && in_array('MONDAY', $current_cta_wdays) ? 'checked="checked"' : ''; ?>/>
					</div>

					<div class="vcm-mngpromo-discount-dow">
						<span class="vcm-mngpromo-discount-dow-lbl"><?php echo JText::_('VCMJQCALTUE'); ?></span>
						<input type="checkbox" name="cta_wdays[]" value="TUESDAY" <?php echo $is_editing && in_array('TUESDAY', $current_cta_wdays) ? 'checked="checked"' : ''; ?>/>
					</div>

					<div class="vcm-mngpromo-discount-dow">
						<span class="vcm-mngpromo-discount-dow-lbl"><?php echo JText::_('VCMJQCALWED'); ?></span>
						<input type="checkbox" name="cta_wdays[]" value="WEDNESDAY" <?php echo $is_editing && in_array('WEDNESDAY', $current_cta_wdays) ? 'checked="checked"' : ''; ?>/>
					</div>

					<div class="vcm-mngpromo-discount-dow">
						<span class="vcm-mngpromo-discount-dow-lbl"><?php echo JText::_('VCMJQCALTHU'); ?></span>
						<input type="checkbox" name="cta_wdays[]" value="THURSDAY" <?php echo $is_editing && in_array('THURSDAY', $current_cta_wdays) ? 'checked="checked"' : ''; ?>/>
					</div>

					<div class="vcm-mngpromo-discount-dow">
						<span class="vcm-mngpromo-discount-dow-lbl"><?php echo JText::_('VCMJQCALFRI'); ?></span>
						<input type="checkbox" name="cta_wdays[]" value="FRIDAY" <?php echo $is_editing && in_array('FRIDAY', $current_cta_wdays) ? 'checked="checked"' : ''; ?>/>
					</div>

					<div class="vcm-mngpromo-discount-dow">
						<span class="vcm-mngpromo-discount-dow-lbl"><?php echo JText::_('VCMJQCALSAT'); ?></span>
						<input type="checkbox" name="cta_wdays[]" value="SATURDAY" <?php echo $is_editing && in_array('SATURDAY', $current_cta_wdays) ? 'checked="checked"' : ''; ?>/>
					</div>

					<div class="vcm-mngpromo-discount-dow">
						<span class="vcm-mngpromo-discount-dow-lbl"><?php echo JText::_('VCMJQCALSUN'); ?></span>
						<input type="checkbox" name="cta_wdays[]" value="SUNDAY" <?php echo $is_editing && in_array('SUNDAY', $current_cta_wdays) ? 'checked="checked"' : ''; ?>/>
					</div>

				</div>

			</div>

		</div>

		<?php
		$current_max_nights = array();
		if ($is_editing && isset($this->promotion->availability_rules) && !empty($this->promotion->availability_rules->max_nights)) {
			// this is an object with wday enum string as property and min/max nights as value: we cast it to an array
			$current_max_nights = (array)$this->promotion->availability_rules->max_nights;
		}
		?>
		<div class="vcm-mngpromo-step vcm-mngpromo-step-conditional" style="display: none;" data-typecond="SEASONAL_ADJUSTMENT BOOKED_WITHIN_AT_MOST_X_DAYS BOOKED_BEYOND_AT_LEAST_X_DAYS STAYED_AT_LEAST_X_DAYS">

			<div class="vcm-mngpromo-step-intro">
				<h3>Override maximum length of stay if the trip starts on a precise day?</h3>
			</div>

			<div class="vcm-mngpromo-step-cont">

				<div class="vcm-mngpromo-choice-entry vcm-mngpromo-book-time-top">
					<input type="checkbox" name="max_nights_enabled" id="max_nights_enabled" value="1" onchange="vcmPromoDowToggle(this.checked, 'maxnights');"<?php echo count($current_max_nights) ? ' checked="checked"' : ''; ?>>
					<label for="max_nights_enabled"><?php echo JText::_('VCMRARADDLOSAPPLY'); ?></label>
				</div>

				<div class="vcm-mngpromo-cta-ctd-wdays" data-restrtype="maxnights" style="display: <?php echo count($current_max_nights) ? '' : 'none'; ?>;">

					<div class="vcm-mngpromo-discount-dow">
						<span class="vcm-mngpromo-discount-dow-lbl"><?php echo JText::_('VCMJQCALMON'); ?></span>
						<input type="number" name="max_nights_mon" value="<?php echo $is_editing && isset($current_max_nights['MONDAY']) ? $current_max_nights['MONDAY'] : ''; ?>" min="0" max="1095"/>
					</div>

					<div class="vcm-mngpromo-discount-dow">
						<span class="vcm-mngpromo-discount-dow-lbl"><?php echo JText::_('VCMJQCALTUE'); ?></span>
						<input type="number" name="max_nights_tue" value="<?php echo $is_editing && isset($current_max_nights['TUESDAY']) ? $current_max_nights['TUESDAY'] : ''; ?>" min="0" max="1095"/>
					</div>

					<div class="vcm-mngpromo-discount-dow">
						<span class="vcm-mngpromo-discount-dow-lbl"><?php echo JText::_('VCMJQCALWED'); ?></span>
						<input type="number" name="max_nights_wed" value="<?php echo $is_editing && isset($current_max_nights['WEDNESDAY']) ? $current_max_nights['WEDNESDAY'] : ''; ?>" min="0" max="1095"/>
					</div>

					<div class="vcm-mngpromo-discount-dow">
						<span class="vcm-mngpromo-discount-dow-lbl"><?php echo JText::_('VCMJQCALTHU'); ?></span>
						<input type="number" name="max_nights_thu" value="<?php echo $is_editing && isset($current_max_nights['THURSDAY']) ? $current_max_nights['THURSDAY'] : ''; ?>" min="0" max="1095"/>
					</div>

					<div class="vcm-mngpromo-discount-dow">
						<span class="vcm-mngpromo-discount-dow-lbl"><?php echo JText::_('VCMJQCALFRI'); ?></span>
						<input type="number" name="max_nights_fri" value="<?php echo $is_editing && isset($current_max_nights['FRIDAY']) ? $current_max_nights['FRIDAY'] : ''; ?>" min="0" max="1095"/>
					</div>

					<div class="vcm-mngpromo-discount-dow">
						<span class="vcm-mngpromo-discount-dow-lbl"><?php echo JText::_('VCMJQCALSAT'); ?></span>
						<input type="number" name="max_nights_sat" value="<?php echo $is_editing && isset($current_max_nights['SATURDAY']) ? $current_max_nights['SATURDAY'] : ''; ?>" min="0" max="1095"/>
					</div>

					<div class="vcm-mngpromo-discount-dow">
						<span class="vcm-mngpromo-discount-dow-lbl"><?php echo JText::_('VCMJQCALSUN'); ?></span>
						<input type="number" name="max_nights_sun" value="<?php echo $is_editing && isset($current_max_nights['SUNDAY']) ? $current_max_nights['SUNDAY'] : ''; ?>" min="0" max="1095"/>
					</div>

				</div>

			</div>

		</div>

		<?php
		$current_min_nights = array();
		if ($is_editing && isset($this->promotion->availability_rules) && !empty($this->promotion->availability_rules->min_nights)) {
			// this is an object with wday enum string as property and min/max nights as value: we cast it to an array
			$current_min_nights = (array)$this->promotion->availability_rules->min_nights;
		}
		?>
		<div class="vcm-mngpromo-step vcm-mngpromo-step-conditional" style="display: none;" data-typecond="SEASONAL_ADJUSTMENT BOOKED_WITHIN_AT_MOST_X_DAYS BOOKED_BEYOND_AT_LEAST_X_DAYS STAYED_AT_LEAST_X_DAYS">

			<div class="vcm-mngpromo-step-intro">
				<h3>Override minimum length of stay if the trip starts on a precise day?</h3>
			</div>

			<div class="vcm-mngpromo-step-cont">

				<div class="vcm-mngpromo-choice-entry vcm-mngpromo-book-time-top">
					<input type="checkbox" name="min_nights_enabled" id="min_nights_enabled" value="1" onchange="vcmPromoDowToggle(this.checked, 'minnights');"<?php echo count($current_min_nights) ? ' checked="checked"' : ''; ?>>
					<label for="min_nights_enabled"><?php echo JText::_('VCMRARADDLOSAPPLY'); ?></label>
				</div>

				<div class="vcm-mngpromo-cta-ctd-wdays" data-restrtype="minnights" style="display: <?php echo count($current_min_nights) ? '' : 'none'; ?>;">

					<div class="vcm-mngpromo-discount-dow">
						<span class="vcm-mngpromo-discount-dow-lbl"><?php echo JText::_('VCMJQCALMON'); ?></span>
						<input type="number" name="min_nights_mon" value="<?php echo $is_editing && isset($current_min_nights['MONDAY']) ? $current_min_nights['MONDAY'] : ''; ?>" min="0" max="100"/>
					</div>

					<div class="vcm-mngpromo-discount-dow">
						<span class="vcm-mngpromo-discount-dow-lbl"><?php echo JText::_('VCMJQCALTUE'); ?></span>
						<input type="number" name="min_nights_tue" value="<?php echo $is_editing && isset($current_min_nights['TUESDAY']) ? $current_min_nights['TUESDAY'] : ''; ?>" min="0" max="100"/>
					</div>

					<div class="vcm-mngpromo-discount-dow">
						<span class="vcm-mngpromo-discount-dow-lbl"><?php echo JText::_('VCMJQCALWED'); ?></span>
						<input type="number" name="min_nights_wed" value="<?php echo $is_editing && isset($current_min_nights['WEDNESDAY']) ? $current_min_nights['WEDNESDAY'] : ''; ?>" min="0" max="100"/>
					</div>

					<div class="vcm-mngpromo-discount-dow">
						<span class="vcm-mngpromo-discount-dow-lbl"><?php echo JText::_('VCMJQCALTHU'); ?></span>
						<input type="number" name="min_nights_thu" value="<?php echo $is_editing && isset($current_min_nights['THURSDAY']) ? $current_min_nights['THURSDAY'] : ''; ?>" min="0" max="100"/>
					</div>

					<div class="vcm-mngpromo-discount-dow">
						<span class="vcm-mngpromo-discount-dow-lbl"><?php echo JText::_('VCMJQCALFRI'); ?></span>
						<input type="number" name="min_nights_fri" value="<?php echo $is_editing && isset($current_min_nights['FRIDAY']) ? $current_min_nights['FRIDAY'] : ''; ?>" min="0" max="100"/>
					</div>

					<div class="vcm-mngpromo-discount-dow">
						<span class="vcm-mngpromo-discount-dow-lbl"><?php echo JText::_('VCMJQCALSAT'); ?></span>
						<input type="number" name="min_nights_sat" value="<?php echo $is_editing && isset($current_min_nights['SATURDAY']) ? $current_min_nights['SATURDAY'] : ''; ?>" min="0" max="100"/>
					</div>

					<div class="vcm-mngpromo-discount-dow">
						<span class="vcm-mngpromo-discount-dow-lbl"><?php echo JText::_('VCMJQCALSUN'); ?></span>
						<input type="number" name="min_nights_sun" value="<?php echo $is_editing && isset($current_min_nights['SUNDAY']) ? $current_min_nights['SUNDAY'] : ''; ?>" min="0" max="100"/>
					</div>

				</div>

			</div>

		</div>

		<div class="vcm-mngpromo-step">

			<div class="vcm-mngpromo-step-intro">
				<h3><?php echo JText::_('VCMBPROMQROOMS'); ?></h3>
				<p><?php echo JText::_('VCMBPROMQROOMSDESC'); ?></p>
			</div>

			<div class="vcm-mngpromo-step-cont">

				<div class="vcm-mngpromo-selall vcm-mngpromo-fullblock">
					<button type="button" class="btn btn-secondary" onclick="vcmAirbnbPromoSelectAllRplans();"><?php echo JText::_('VCMSMARTBALSELALL'); ?></button>
				</div>

				<div class="vcm-mngpromo-rplans">
				<?php
				foreach ($this->otalistings as $listing_id => $listing_name) {
					?>
					<div class="vcm-mngpromo-choice-entry vcm-mngpromo-rplan-block">
						<input type="checkbox" name="listing_ids[]" value="<?php echo $listing_id; ?>" id="rplan<?php echo $listing_id; ?>" <?php echo in_array($listing_id, $active_listings_pool) ? 'checked="checked"' : ''; ?>/>
						<label for="rplan<?php echo $listing_id; ?>"><?php echo $listing_name . '(' . $listing_id . ')'; ?></label>
					</div>
					<?php
				}
				?>
				</div>

			</div>

		</div>

		<div class="vcm-mngpromo-step">

			<div class="vcm-mngpromo-step-intro">
				<h3><?php echo JText::_('VCMBPROMQWHEN'); ?></h3>
				<p><?php echo JText::_('VCMBPROMQWHENDESC1'); ?></p>
			</div>

			<div class="vcm-mngpromo-step-cont">

				<div class="vcm-mngpromo-dates-stay-top">

					<div class="vcm-mngpromo-dates vcm-mngpromo-dates-stay">
						<div class="vcm-mngpromo-date-from">
							<span>
								<i class="vboicn-calendar"></i>
								<input type="text" class="vcm-mngpromo-staydate" name="stayfromdate" id="stayfromdate" value="" />
							</span>
						</div>
						<div class="vcm-mngpromo-date-to">
							<span>
								<i class="vboicn-calendar"></i>
								<input type="text" class="vcm-mngpromo-staydate" name="staytodate" id="staytodate" value="" />
							</span>
						</div>
					</div>

				</div>

			</div>

		</div>

		<div class="vcm-mngpromo-step">

			<div class="vcm-mngpromo-step-intro">
				<h3><?php echo JText::_('VCMBPROMQNAME'); ?></h3>
				<p><?php echo JText::sprintf('VCMBPROMQNAMEDESC1CH', 'Airbnb'); ?></p>
			</div>

			<div class="vcm-mngpromo-step-cont">

				<div class="vcm-mngpromo-name">
					<input type="text" name="name" id="promo-name" value="<?php echo $is_editing ? $this->promotion->title : ''; ?>" size="40" />
				</div>

			</div>

		</div>

	</div>
<?php
if ($is_editing) {
	?>
	<input type="hidden" name="promoid" value="<?php echo $this->promotion->id; ?>" />
	<?php
}
?>
	<input type="hidden" name="task" value="" />
</form>

<!-- the link below must be hidden for compatibility with all CMSs -->
<a id="vcm-hidden-return" href="index.php?option=com_vikchannelmanager&task=airbnbpromo" style="display: none;"></a>
<!-- --- -->

<script type="text/javascript">
function vcmCheckStayDates(selectedDate, inst) {
	var nowstart = jQuery(this).datepicker('getDate');
	var nowstartdate = new Date(nowstart.getTime());
	var nextyear = new Date(nowstart.getTime());
	nextyear.setFullYear((nextyear.getFullYear() + 1));
	var elid = jQuery(this).attr('id');
	if (elid == 'stayfromdate') {
		// from date selected
		jQuery('#staytodate').datepicker( 'option', { minDate: nowstartdate, maxDate: nextyear } );
	} else {
		// to date selected
		jQuery('#stayfromdate').datepicker( 'option', { maxDate: nowstartdate } );
	}
}
function vcmPromoDowToggle(checked, type) {
	if (checked) {
		jQuery('.vcm-mngpromo-cta-ctd-wdays[data-restrtype="' + type + '"]').show();
	} else {
		jQuery('.vcm-mngpromo-cta-ctd-wdays[data-restrtype="' + type + '"]').hide();
	}
}
function vcmAirbnbPromoSelectAllRplans() {
	var all_checked = true;
	jQuery('.vcm-mngpromo-rplan-block').find('input').each(function() {
		if (jQuery(this).prop('checked') !== true) {
			all_checked = false;
			return false;
		}
	});
	jQuery('.vcm-mngpromo-rplan-block').find('input').prop('checked', (!all_checked));
}
jQuery(document).ready(function() {
	// change promotion type event listener for conditional fields
	jQuery('#promotype').change(function() {
		var ptype = jQuery(this).val();
		jQuery('.vcm-mngpromo-step-conditional').each(function() {
			var typecond = jQuery(this).attr('data-typecond');
			if (typecond.indexOf(ptype) < 0) {
				jQuery(this).hide();
			} else {
				jQuery(this).fadeIn();
			}
		});
	});
	// always trigger change promotion type event when page loads
	jQuery('#promotype').trigger('change');
	// promotion type
	jQuery('.vcm-mngpromo-typebox').click(function() {
		var ptype = jQuery(this).attr('data-type');
		jQuery('#promotype').val(ptype).trigger('change');
		jQuery('.vcm-mngpromo-typebox').removeClass('vcm-mngpromo-typebox-active');
		jQuery(this).addClass('vcm-mngpromo-typebox-active');
	});
	// target channel (members only promotion)
	jQuery('.vcm-mngpromo-targetbox').click(function() {
		var ptarget = jQuery(this).attr('data-target');
		jQuery('#members_only').val(ptarget);
		jQuery('.vcm-mngpromo-targetbox').removeClass('vcm-mngpromo-targetbox-active');
		jQuery(this).addClass('vcm-mngpromo-targetbox-active');
	});
	// stay dates datepicker
	jQuery('.vcm-mngpromo-staydate:input').datepicker({
		dateFormat: "yy-mm-dd",
		minDate: "-1y",
		maxDate: "+2y",
		onSelect: vcmCheckStayDates
	});
<?php
// populate dates in datepicker calendars
if ($is_editing && isset($this->promotion->since_date)) {
	?>
jQuery('#stayfromdate').datepicker( 'setDate', '<?php echo $this->promotion->since_date; ?>' );
jQuery('#staytodate').datepicker( 'setDate', '<?php echo $this->promotion->end_date; ?>' );
	<?php
}
?>
});
/* Loading Overlay */
function vcmShowLoading() {
	jQuery(".vcm-loading-overlay").show();
}
function vcmStopLoading() {
	jQuery(".vcm-loading-overlay").hide();
}
function vcmSubmitPromotion(task) {
	// display loading overlay
	vcmShowLoading();
	// get form values
	var qstring = jQuery('#adminForm').serialize();
	// make sure the task is not set again, or the good one will go lost.
	qstring = qstring.replace('&task=', '&');
	// make the ajax request to the controller
	var jqxhr = jQuery.ajax({
		type: "POST",
		url: "index.php?option=com_vikchannelmanager&task="+task,
		data: qstring
	}).done(function(res) {
		if (res.substr(0, 6) == 'e4j.ok') {
			// success: redirect
			document.location.href = jQuery('#vcm-hidden-return').attr('href') + '&success=' + res.replace('e4j.ok.', '');
		} else if (res.substr(0, 9) == 'e4j.error') {
			// error
			alert(res.replace('e4j.error.', ''));
			vcmStopLoading();
		} else {
			// unknown error
			console.log(res);
			alert('Unknown Error');
			vcmStopLoading();
		}
	}).fail(function() {
		alert("Error Performing Ajax Request");
		vcmStopLoading();
	});
}
Joomla.submitbutton = function(task) {
	if (task == 'airbnbpromo.savepromo' || task == 'airbnbpromo.updatepromo') {
		// submit form to controller
		vcmSubmitPromotion(task);

		// exit
		return false;
	}
	// other buttons can submit the form normally
	Joomla.submitform(task, document.adminForm);
}
</script>
