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

// active rate plans in the promotion
$active_rplans_pool = array();
if ($is_editing && isset($this->promotion->eligibleRatePlans) && is_array($this->promotion->eligibleRatePlans)) {
	foreach ($this->promotion->eligibleRatePlans as $eligrplan) {
		if (!is_object($eligrplan) || !isset($eligrplan->id)) {
			continue;
		}
		array_push($active_rplans_pool, $eligrplan->id);
	}
}

// blackout (excluded) dates (Y-m-d)
$excluded_dates = array();
if ($is_editing && isset($this->promotion->blackoutDates) && is_array($this->promotion->blackoutDates) && count($this->promotion->blackoutDates)) {
	foreach ($this->promotion->blackoutDates as $blackoutd) {
		if (!is_object($blackoutd) || !isset($blackoutd->travelDateFrom) || !isset($blackoutd->travelDateTo)) {
			// missing data for the blackout date object
			continue;
		}
		if ($blackoutd->travelDateFrom == $blackoutd->travelDateTo) {
			// we suppose just one day is blackedout
			array_push($excluded_dates, $blackoutd->travelDateFrom);
			continue;
		}
		// find all dates in between
		$blk_from_info = getdate(strtotime($blackoutd->travelDateFrom));
		$blk_to_info = getdate(strtotime($blackoutd->travelDateTo));
		if (!$blk_from_info || !$blk_to_info || $blk_from_info[0] > $blk_to_info[0]) {
			// invalid blackout dates
			continue;
		}
		while ($blk_from_info[0] <= $blk_to_info[0]) {
			array_push($excluded_dates, date('Y-m-d', $blk_from_info[0]));
			$blk_from_info = getdate(mktime(0, 0, 0, $blk_from_info['mon'], ($blk_from_info['mday'] + 1), $blk_from_info['year']));
		}
	}
}

// take all the rate plans from the mapped room types
$otarplans = array();
foreach ($this->otarooms as $otar) {
	// make sure the hotel ID matches with the active one
	if (!empty($otar['prop_params'])) {
		$prop_params = json_decode($otar['prop_params'], true);
		if (isset($prop_params['hotelid']) && $prop_params['hotelid'] != $this->channel['params']['hotelid']) {
			// skip this room mapping as it's for a different hotel ID
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
			'dist_model' => (isset($rplan['distributionModel']) ? $rplan['distributionModel'] : null),
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
				
				<div class="vcm-mngpromo-typebox<?php echo ($is_editing && $this->promotion->name == 'BASIC_PROMOTION') || !$is_editing ? ' vcm-mngpromo-typebox-active' : ''; ?>" data-type="BASIC_PROMOTION">
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

				<div class="vcm-mngpromo-typebox<?php echo $is_editing && $this->promotion->name == 'SAME_DAY_PROMOTION' ? ' vcm-mngpromo-typebox-active' : ''; ?>" data-type="SAME_DAY_PROMOTION">
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

				<div class="vcm-mngpromo-typebox<?php echo $is_editing && $this->promotion->name == 'EARLY_BOOKING_PROMOTION' ? ' vcm-mngpromo-typebox-active' : ''; ?>" data-type="EARLY_BOOKING_PROMOTION">
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

				<div class="vcm-mngpromo-typebox<?php echo $is_editing && $this->promotion->name == 'MULTI_NIGHT_PROMOTION' ? ' vcm-mngpromo-typebox-active' : ''; ?>" data-type="MULTI_NIGHT_PROMOTION">
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

				<input type="hidden" name="promo_type" id="promotype" value="<?php echo $is_editing ? $this->promotion->name : 'BASIC_PROMOTION'; ?>" />
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
					<input type="radio" name="promo_status_active" id="promo_status_active" value="1"<?php echo $is_editing && isset($this->promotion->status) && $this->promotion->status == 'ACTIVE' ? ' checked="checked"' : ''; ?>>
					<label for="promo_status_active"><?php echo JText::_('VCMPROMSTATUSACTIVE'); ?></label>
				</div>

				<div class="vcm-mngpromo-status-toggle vcm-mngpromo-fullblock">
					<input type="radio" name="promo_status_active" id="promo_status_inactive" value="0"<?php echo $is_editing && isset($this->promotion->status) && $this->promotion->status == 'INACTIVE' ? ' checked="checked"' : ''; ?>>
					<label for="promo_status_inactive"><?php echo JText::_('VCMPROMSTATUSINACTIVE'); ?></label>
				</div>

			</div>

		</div>
	<?php
	}
	?>

		<div class="vcm-mngpromo-step vcm-mngpromo-step-conditional" style="display: none;" data-typecond="BASIC_PROMOTION SAME_DAY_PROMOTION EARLY_BOOKING_PROMOTION MULTI_NIGHT_PROMOTION">

			<div class="vcm-mngpromo-step-intro">
				<h3><?php echo JText::_('VCMBPROMQHOWEARLY'); ?></h3>
			</div>

			<div class="vcm-mngpromo-step-cont">

				<div class="vcm-mngpromo-earlybooker-types">

					<div class="vcm-mngpromo-earlybooker-type vcm-mngpromo-fullblock">
						<?php $inputString = "<input type=\"number\" name=\"adv_book_days_min\" value=\"".($is_editing && isset($this->promotion->restrictions) && isset($this->promotion->restrictions->minAdvanceBookingDays) ? (int)$this->promotion->restrictions->minAdvanceBookingDays : '0')."\" min=\"0\" />";?>
						<span><?php echo JText::sprintf("VCMBPROMBEFORED", $inputString . ' (min) ');?></span>
					</div>

					<div class="vcm-mngpromo-earlybooker-type vcm-mngpromo-fullblock">
						<?php $inputString = "<input type=\"number\" name=\"adv_book_days_max\" value=\"".($is_editing && isset($this->promotion->restrictions) && isset($this->promotion->restrictions->maxAdvanceBookingDays) ? (int)$this->promotion->restrictions->maxAdvanceBookingDays : '0')."\" min=\"0\" />";?>
						<span><?php echo JText::sprintf("VCMBPROMBEFORED", $inputString . ' (max) ');?></span>
					</div>

				</div>
				
			</div>

		</div>

		<div class="vcm-mngpromo-step vcm-mngpromo-step-conditional" style="display: none;" data-typecond="BASIC_PROMOTION SAME_DAY_PROMOTION EARLY_BOOKING_PROMOTION MULTI_NIGHT_PROMOTION">

			<div class="vcm-mngpromo-step-intro">
				<h3><?php echo JText::_('VCMBPROMQWHO'); ?></h3>
			</div>

			<div class="vcm-mngpromo-step-cont">

				<div class="vcm-mngpromo-targetbox<?php echo ($is_editing && isset($this->promotion->restrictions) && !$this->promotion->restrictions->isMemberOnly) || !$is_editing ? ' vcm-mngpromo-targetbox-active' : ''; ?>" data-target="public">
					<div class="vcm-mngpromo-targetbox-inner">
						<div class="vcm-mngpromo-targetimg">
							<img src="<?php echo VCM_ADMIN_URI . 'assets/css/images/bcom_promo_everyone.png'; ?>">
						</div>
						<div class="vcm-mngpromo-targettitle">
							<h4><?php echo JText::_('VCMBPROMAEVERYONE'); ?></h4>
						</div>
					</div>
				</div>

				<div class="vcm-mngpromo-targetbox<?php echo $is_editing && isset($this->promotion->restrictions) && $this->promotion->restrictions->isMemberOnly ? ' vcm-mngpromo-targetbox-active' : ''; ?>" data-target="members">
					<div class="vcm-mngpromo-targetbox-inner">
						<div class="vcm-mngpromo-targetimg">
							<img src="<?php echo VCM_ADMIN_URI . 'assets/css/images/bcom_promo_secret.png'; ?>">
						</div>
						<div class="vcm-mngpromo-targettitle">
							<h4><?php echo JText::_('VCMBPROMAMEMBERSSUB'); ?></h4>
						</div>
						<div class="vcm-mngpromo-targetsubtitle">
							<h5><?php echo JText::_('VCMBPROMASECRETD'); ?></h5>
						</div>
					</div>
				</div>

				<div class="vcm-mngpromo-targetbox<?php echo $is_editing && isset($this->promotion->restrictions) && $this->promotion->restrictions->isMobileUserOnly ? ' vcm-mngpromo-targetbox-active' : ''; ?>" data-target="mobile">
					<div class="vcm-mngpromo-targetbox-inner">
						<div class="vcm-mngpromo-targetimg">
							<img src="<?php echo VCM_ADMIN_URI . 'assets/css/images/bcom_promo_mobile_rate.png'; ?>">
						</div>
						<div class="vcm-mngpromo-targettitle">
							<h4><?php echo JText::_('VCMBPROMMOBILEDEALTIT'); ?></h4>
						</div>
						<div class="vcm-mngpromo-targetsubtitle">
							<h5><?php echo JText::_('VCMBPROMMOBILEDEALSUB'); ?></h5>
						</div>
					</div>
				</div>

				<?php
				$active_promo_audience = 'public';
				if ($is_editing && isset($this->promotion->restrictions)) {
					if ($this->promotion->restrictions->isMemberOnly) {
						$active_promo_audience = 'members';
					} elseif ($this->promotion->restrictions->isMobileUserOnly) {
						$active_promo_audience = 'mobile';
					}
				}
				?>

				<input type="hidden" name="members_only" id="members_only" value="<?php echo $active_promo_audience; ?>" />
			</div>

		</div>

		<div class="vcm-mngpromo-step">

			<div class="vcm-mngpromo-step-intro">
				<h3><?php echo JText::_('VCMBPROMQSTAY'); ?></h3>
			</div>

			<div class="vcm-mngpromo-step-cont">

				<div class="vcm-mngpromo-minstay vcm-mngpromo-fullblock">
					<span><?php echo JText::_('VCMBPROMAMINNIGHTS');?></span>
					<input type="number" name="min_stay" value="<?php echo $is_editing && isset($this->promotion->restrictions) ? (int)$this->promotion->restrictions->minLengthOfStay : '1'; ?>" min="1" max="28" />
				</div>

				<div class="vcm-mngpromo-maxstay vcm-mngpromo-fullblock">
					<span><?php echo JText::_('VCMMAXNIGHTS');?></span>
					<input type="number" name="max_stay" value="<?php echo $is_editing && isset($this->promotion->restrictions) ? (int)$this->promotion->restrictions->maxLengthOfStay : '28'; ?>" min="1" max="28" />
				</div>

			</div>

		</div>

		<div class="vcm-mngpromo-step">

			<div class="vcm-mngpromo-step-intro">
				<h3><?php echo JText::_('VCMBPROMQMUCH'); ?></h3>
			</div>

			<div class="vcm-mngpromo-step-cont">

				<div class="vcm-mngpromo-discount">
					<input type="number" name="discount" value="<?php echo ($is_editing && isset($this->promotion->discount) && isset($this->promotion->discount->value) ? $this->promotion->discount->value : (!$is_editing ? '10' : '')); ?>" step="any" min="1" max="100" />
					<span>%</span>
				</div>

			</div>

		</div>

		<div class="vcm-mngpromo-step vcm-mngpromo-step-conditional" style="display: none;" data-typecond="MULTI_NIGHT_PROMOTION">

			<div class="vcm-mngpromo-step-intro">
				<h3><?php echo JText::_('VCMPROMQHMANYMULTIN'); ?></h3>
				<p><?php echo JText::_('VCMPROMQHMANYMULTINDESC'); ?></p>
			</div>

			<div class="vcm-mngpromo-step-cont">

				<div class="vcm-mngpromo-discount vcm-mngpromo-discount-multinight">
					<input type="number" name="appl_night" value="<?php echo ($is_editing && isset($this->promotion->discount) && isset($this->promotion->discount->applicableNight) ? $this->promotion->discount->applicableNight : ''); ?>" min="2" max="180" />
				</div>

				<div class="vcm-mngpromo-discount-multinight-recurring vcm-mngpromo-fullblock">
					<input type="checkbox" name="appl_night_recur" id="appl_night_recur" value="1"<?php echo $is_editing && isset($this->promotion->discount) && isset($this->promotion->discount->isRecurring) && $this->promotion->discount->isRecurring ? ' checked="checked"' : ''; ?>>
					<label for="appl_night_recur"><?php echo JText::_('VCMPROMMULTINRECUR'); ?></label>
				</div>

			</div>

		</div>

		<div class="vcm-mngpromo-step">

			<div class="vcm-mngpromo-step-intro">
				<h3><?php echo JText::_('VCMPROMQMEMBEXTRADISC'); ?></h3>
				<p><?php echo JText::_('VCMPROMQMEMBEXTRADISCDESC'); ?></p>
			</div>

			<div class="vcm-mngpromo-step-cont">

				<div class="vcm-mngpromo-discount">
					<input type="number" name="memb_extra_disc" value="<?php echo ($is_editing && isset($this->promotion->discount) && isset($this->promotion->discount->memberOnlyAdditionalValue) ? $this->promotion->discount->memberOnlyAdditionalValue : ''); ?>" step="any" min="0" max="100" />
					<span>%</span>
				</div>

			</div>

		</div>

		<?php
		// we display the "day of week" (DOW) discount if edit mode and one value per week-day exists
		$dow_enabled = false;
		$dow_discounts = array(
			'monday' => null,
			'tuesday' => null,
			'wednesday' => null,
			'thursday' => null,
			'friday' => null,
			'saturday' => null,
			'sunday' => null,
		);
		if ($is_editing && isset($this->promotion->discount)) {
			foreach ($dow_discounts as $dow_name => $dow_disc) {
				if (isset($this->promotion->discount->{$dow_name})) {
					$dow_discounts[$dow_name] = $this->promotion->discount->{$dow_name};
					$dow_enabled = true;
				}
			}
		}
		?>
		<div class="vcm-mngpromo-step vcm-mngpromo-step-conditional" style="display: <?php echo $dow_enabled ? '' : 'none'; ?>;" data-typecond="BASIC_PROMOTION EARLY_BOOKING_PROMOTION MULTI_NIGHT_PROMOTION">

			<div class="vcm-mngpromo-step-intro">
				<h3><?php echo JText::_('VCMPROMAPPLYDOW'); ?></h3>
			</div>

			<div class="vcm-mngpromo-step-cont">

				<div class="vcm-mngpromo-choice-entry vcm-mngpromo-book-time-top">
					<input type="checkbox" name="dow_enabled" id="dow_enabled" value="1" onchange="vcmPromoDowToggle(this.checked);"<?php echo $dow_enabled ? ' checked="checked"' : ''; ?>>
					<label for="dow_enabled"><?php echo JText::_('VCMPROMAPPLYDOW'); ?></label>
				</div>

				<div class="vcm-mngpromo-book-time-bottom" style="display: <?php echo $dow_enabled ? '' : 'none'; ?>;">

					<div class="vcm-mngpromo-discount-dow">
						<span class="vcm-mngpromo-discount-dow-lbl"><?php echo JText::_('VCMJQCALMON'); ?></span>
						<input type="number" name="discount_mon" value="<?php echo $is_editing && isset($dow_discounts['monday']) ? $dow_discounts['monday'] : '0'; ?>" step="any" min="0" max="100" />
						<span>%</span>
					</div>

					<div class="vcm-mngpromo-discount-dow">
						<span class="vcm-mngpromo-discount-dow-lbl"><?php echo JText::_('VCMJQCALTUE'); ?></span>
						<input type="number" name="discount_tue" value="<?php echo $is_editing && isset($dow_discounts['tuesday']) ? $dow_discounts['tuesday'] : '0'; ?>" step="any" min="0" max="100" />
						<span>%</span>
					</div>

					<div class="vcm-mngpromo-discount-dow">
						<span class="vcm-mngpromo-discount-dow-lbl"><?php echo JText::_('VCMJQCALWED'); ?></span>
						<input type="number" name="discount_wed" value="<?php echo $is_editing && isset($dow_discounts['wednesday']) ? $dow_discounts['wednesday'] : '0'; ?>" step="any" min="0" max="100" />
						<span>%</span>
					</div>

					<div class="vcm-mngpromo-discount-dow">
						<span class="vcm-mngpromo-discount-dow-lbl"><?php echo JText::_('VCMJQCALTHU'); ?></span>
						<input type="number" name="discount_thu" value="<?php echo $is_editing && isset($dow_discounts['thursday']) ? $dow_discounts['thursday'] : '0'; ?>" step="any" min="0" max="100" />
						<span>%</span>
					</div>

					<div class="vcm-mngpromo-discount-dow">
						<span class="vcm-mngpromo-discount-dow-lbl"><?php echo JText::_('VCMJQCALFRI'); ?></span>
						<input type="number" name="discount_fri" value="<?php echo $is_editing && isset($dow_discounts['friday']) ? $dow_discounts['friday'] : '0'; ?>" step="any" min="0" max="100" />
						<span>%</span>
					</div>

					<div class="vcm-mngpromo-discount-dow">
						<span class="vcm-mngpromo-discount-dow-lbl"><?php echo JText::_('VCMJQCALSAT'); ?></span>
						<input type="number" name="discount_sat" value="<?php echo $is_editing && isset($dow_discounts['saturday']) ? $dow_discounts['saturday'] : '0'; ?>" step="any" min="0" max="100" />
						<span>%</span>
					</div>

					<div class="vcm-mngpromo-discount-dow">
						<span class="vcm-mngpromo-discount-dow-lbl"><?php echo JText::_('VCMJQCALSUN'); ?></span>
						<input type="number" name="discount_sun" value="<?php echo $is_editing && isset($dow_discounts['sunday']) ? $dow_discounts['sunday'] : '0'; ?>" step="any" min="0" max="100" />
						<span>%</span>
					</div>

				</div>

			</div>

		</div>

		<div class="vcm-mngpromo-step">

			<div class="vcm-mngpromo-step-intro">
				<h3><?php echo JText::_('VCMBPROMQRATES'); ?></h3>
				<p><?php echo JText::_('VCMBPROMQROOMSDESC'); ?></p>
			</div>

			<div class="vcm-mngpromo-step-cont">

				<div class="vcm-mngpromo-selall vcm-mngpromo-fullblock">
					<button type="button" class="btn btn-secondary" onclick="vcmEgPromoSelectAllRplans();"><?php echo JText::_('VCMSMARTBALSELALL'); ?></button>
				</div>

				<div class="vcm-mngpromo-rplans">
				<?php
				foreach ($otarplans as $rplanid => $rplan_info) {
					$say_rplan_name = strtolower($rplan_info['rplan_name']) == 'standard' ? '<strong>' . $rplan_info['rplan_name'] . '</strong>' : $rplan_info['rplan_name'];
					?>
					<div class="vcm-mngpromo-choice-entry vcm-mngpromo-rplan-block">
						<input type="checkbox" name="rplans[]" value="<?php echo $rplanid; ?>" id="rplan<?php echo $rplanid; ?>" <?php echo in_array($rplanid, $active_rplans_pool) ? 'checked="checked"' : ''; ?>/>
						<label for="rplan<?php echo $rplanid; ?>"><?php echo $rplan_info['room_name'] . ' - ' . $say_rplan_name . ' (' . $rplanid . (isset($rplan_info['dist_model']) ? ' - ' . $rplan_info['dist_model'] : '') .  ')'; ?></label>
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

				<div class="vcm-mngpromo-dates-stay-bottom">
					<p><?php echo JText::_('VCMBPROMQWHENDESC2'); ?></p>
					<div class="vcm-mngpromo-excl-dates">
						<span>
							<i class="vboicn-calendar"></i>
							<input type="text" class="vcm-mngpromo-excldate" name="sel-excl-day" id="sel-excl-day" value="" />
						</span>
					</div>
					<div class="vcm-mngpromo-excl-dates-current">
					<?php
					foreach ($excluded_dates as $excld) {
						?>
						<button type="button" class="btn btn-small btn-danger" onclick="vcmRmExclDate(this, '<?php echo $excld; ?>');"><i class="vboicn-cross"></i> <?php echo $excld; ?></button>
						<?php
					}
					?>
					</div>
					<input type="hidden" name="excluded_dates" id="excluded_dates" value="<?php echo count($excluded_dates) ? implode(';', $excluded_dates).';' : ''; ?>" />
				</div>

			</div>

		</div>

		<div class="vcm-mngpromo-step vcm-mngpromo-step-conditional" style="display: none;" data-typecond="BASIC_PROMOTION EARLY_BOOKING_PROMOTION MULTI_NIGHT_PROMOTION">

			<div class="vcm-mngpromo-step-intro">
				<h3><?php echo JText::sprintf('VCMBPROMQVISCH', 'Expedia'); ?></h3>
				<p><?php echo JText::_('VCMBPROMQVISDESC'); ?></p>
			</div>

			<div class="vcm-mngpromo-step-cont">

				<div class="vcm-mngpromo-dates-book-top">

					<div class="vcm-mngpromo-dates vcm-mngpromo-dates-book">
						<div class="vcm-mngpromo-date-from vcm-mngpromo-datetime-block">
							<span>
								<i class="vboicn-calendar"></i>
								<input type="text" class="vcm-mngpromo-bookdate" name="bookfromdate" id="bookfromdate" value="" />
							</span>
							<div class="vcm-mngpromo-booktime-bottom">
								<span class="vcm-mngpromo-booktime vcm-mngpromo-booktime-hours">
									<select name="book_time_h_start" id="book_time_h_start">
									<?php
									$cur_booktime_start = -1;
									$booktime_from_info = null;
									if ($is_editing && isset($this->promotion->restrictions) && isset($this->promotion->restrictions->bookingLocalDateTimeFrom)) {
										$booktime_from_info = getdate(strtotime($this->promotion->restrictions->bookingLocalDateTimeFrom));
										$cur_booktime_start = $booktime_from_info['hours'];
									}
									for ($i = 0; $i < 24; $i++) {
										$timelbl = ($i > 12 ? ($i - 12) : $i) . ' ' . ($i < 12 ? 'AM' : 'PM');
										?>
										<option value="<?php echo $i; ?>"<?php echo $cur_booktime_start == $i ? ' selected="selected"' : '' ; ?>><?php echo $timelbl; ?></option>
										<?php
									}
									?>
									</select>
								</span>
								<span class="vcm-mngpromo-book-time-txt">:</span>
								<span class="vcm-mngpromo-booktime vcm-mngpromo-booktime-minutes">
									<select name="book_time_m_start" id="book_time_m_start">
									<?php
									$cur_booktime_start = -1;
									if ($is_editing && isset($booktime_from_info)) {
										$cur_booktime_start = $booktime_from_info['minutes'];
									}
									for ($i = 0; $i < 60; $i++) {
										$timelbl = ($i < 10 ? ('0' . $i) : $i);
										?>
										<option value="<?php echo $i; ?>"<?php echo $cur_booktime_start == $i ? ' selected="selected"' : '' ; ?>><?php echo $timelbl; ?></option>
										<?php
									}
									?>
									</select>
								</span>
							</div>
						</div>
						<div class="vcm-mngpromo-date-to vcm-mngpromo-datetime-block">
							<span>
								<i class="vboicn-calendar"></i>
								<input type="text" class="vcm-mngpromo-bookdate" name="booktodate" id="booktodate" value="" />
							</span>
							<div class="vcm-mngpromo-booktime-bottom">
								<span class="vcm-mngpromo-booktime">
									<select name="book_time_h_end" id="book_time_h_end">
									<?php
									$cur_booktime_end = -1;
									$booktime_to_info = null;
									if ($is_editing && isset($this->promotion->restrictions) && isset($this->promotion->restrictions->bookingLocalDateTimeTo)) {
										$booktime_to_info = getdate(strtotime($this->promotion->restrictions->bookingLocalDateTimeTo));
										$cur_booktime_end = $booktime_to_info['hours'];
									}
									for ($i = 0; $i < 24; $i++) {
										$timelbl = ($i > 12 ? ($i - 12) : $i) . ' ' . ($i < 12 ? 'AM' : 'PM');
										?>
										<option value="<?php echo $i; ?>"<?php echo $cur_booktime_end == $i ? ' selected="selected"' : '' ; ?>><?php echo $timelbl; ?></option>
										<?php
									}
									?>
									</select>
								</span>
								<span class="vcm-mngpromo-book-time-txt">:</span>
								<span class="vcm-mngpromo-booktime vcm-mngpromo-booktime-minutes">
									<select name="book_time_m_end" id="book_time_m_end">
									<?php
									$cur_booktime_end = -1;
									if ($is_editing && isset($booktime_to_info)) {
										$cur_booktime_end = $booktime_to_info['minutes'];
									}
									for ($i = 0; $i < 60; $i++) {
										$timelbl = ($i < 10 ? ('0' . $i) : $i);
										?>
										<option value="<?php echo $i; ?>"<?php echo $cur_booktime_end == $i ? ' selected="selected"' : '' ; ?>><?php echo $timelbl; ?></option>
										<?php
									}
									?>
									</select>
								</span>
							</div>
						</div>
					</div>

				</div>

			</div>

		</div>

		<div class="vcm-mngpromo-step">

			<div class="vcm-mngpromo-step-intro">
				<h3><?php echo JText::_('VCMBPROMQNAME'); ?></h3>
				<p><?php echo JText::sprintf('VCMBPROMQNAMEDESC1CH', 'Expedia'); ?></p>
			</div>

			<div class="vcm-mngpromo-step-cont">

				<div class="vcm-mngpromo-name">
					<input type="text" name="name" id="promo-name" value="<?php echo $is_editing ? $this->promotion->code : ''; ?>" size="40" />
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
<a id="vcm-hidden-return" href="index.php?option=com_vikchannelmanager&task=egpromo" style="display: none;"></a>
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
function vcmCheckExclDate(selectedDate, inst) {
	// push the selected excluded date into the hidden input string
	jQuery('#excluded_dates').val(jQuery('#excluded_dates').val() + selectedDate + ';');
	// add a button to undo the action and to display the excluded date
	var undobtn = '<button type="button" class="btn btn-small btn-danger" onclick="vcmRmExclDate(this, \'' + selectedDate + '\');">';
	undobtn += '<i class="vboicn-cross"></i> ' + selectedDate;
	undobtn += '</button>';
	jQuery('.vcm-mngpromo-excl-dates-current').append(undobtn);
	// clean up the input field
	jQuery(this).val('');
}
function vcmRmExclDate(elem, day) {
	// removes the button from the screen and updates the current excluded dates value
	jQuery(elem).remove();
	jQuery('#excluded_dates').val(jQuery('#excluded_dates').val().replace(day + ';', ''));
}
function vcmCheckBookDates(selectedDate, inst) {
	var nowstart = jQuery(this).datepicker('getDate');
	var nowstartdate = new Date(nowstart.getTime());
	var nextyear = new Date(nowstart.getTime());
	nextyear.setFullYear((nextyear.getFullYear() + 1));
	var elid = jQuery(this).attr('id');
	if (elid == 'bookfromdate') {
		// from date selected
		jQuery('#booktodate').datepicker( 'option', { minDate: nowstartdate, maxDate: nextyear } );
	} else {
		// to date selected
		jQuery('#bookfromdate').datepicker( 'option', { maxDate: nowstartdate } );
	}
}
function vcmPromoDowToggle(checked) {
	if (checked) {
		jQuery('.vcm-mngpromo-book-time-bottom').show();
	} else {
		jQuery('.vcm-mngpromo-book-time-bottom').hide();
	}
}
function vcmEgPromoSelectAllRplans() {
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
	// excluded dates datepicker
	jQuery('.vcm-mngpromo-excldate:input').datepicker({
		dateFormat: "yy-mm-dd",
		minDate: "-1y",
		maxDate: "+2y",
		onSelect: vcmCheckExclDate
	});
	// stay dates datepicker
	jQuery('.vcm-mngpromo-bookdate:input').datepicker({
		dateFormat: "yy-mm-dd",
		minDate: "-1y",
		maxDate: "+2y",
		onSelect: vcmCheckBookDates
	});
<?php
// populate dates in datepicker calendars
if ($is_editing && isset($this->promotion->restrictions)) {
	if (isset($this->promotion->restrictions->travelDateFrom) && isset($this->promotion->restrictions->travelDateTo)) {
		?>
	jQuery('#stayfromdate').datepicker( 'setDate', '<?php echo $this->promotion->restrictions->travelDateFrom; ?>' );
	jQuery('#staytodate').datepicker( 'setDate', '<?php echo $this->promotion->restrictions->travelDateTo; ?>' );
		<?php
	}
	if (isset($this->promotion->restrictions->bookingLocalDateTimeFrom)) {
		$bookfromdate = date('Y-m-d', strtotime($this->promotion->restrictions->bookingLocalDateTimeFrom));
		?>
	jQuery('#bookfromdate').datepicker( 'setDate', '<?php echo $bookfromdate; ?>' );
		<?php
	}
	if (isset($this->promotion->restrictions->bookingLocalDateTimeTo)) {
		$booktodate = date('Y-m-d', strtotime($this->promotion->restrictions->bookingLocalDateTimeTo));
		?>
	jQuery('#booktodate').datepicker( 'setDate', '<?php echo $booktodate; ?>' );
		<?php
	}
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
	if (task == 'egpromo.savepromo' || task == 'egpromo.updatepromo') {
		// submit form to controller
		vcmSubmitPromotion(task);

		// exit
		return false;
	}
	// other buttons can submit the form normally
	Joomla.submitform(task, document.adminForm);
}
</script>
