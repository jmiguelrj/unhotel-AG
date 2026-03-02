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
if ($is_editing) {
	$parent_rates = isset($this->promotion->parent_rates->parent_rate) ? $this->promotion->parent_rates->parent_rate : null;
	if (is_array($parent_rates)) {
		foreach ($parent_rates as $prate) {
			array_push($active_rplans_pool, $prate->id);
		}
	} elseif (is_object($parent_rates)) {
		array_push($active_rplans_pool, $parent_rates->id);
	}
}

// active rooms in the promotion
$active_rooms_pool = array();
if ($is_editing) {
	$active_rooms = isset($this->promotion->rooms->room) ? $this->promotion->rooms->room : null;
	if (is_array($active_rooms)) {
		foreach ($active_rooms as $proom) {
			array_push($active_rooms_pool, $proom->id);
		}
	} elseif (is_object($active_rooms)) {
		array_push($active_rooms_pool, $active_rooms->id);
	}
}

// active weekdays
$active_weekdays = array();
if ($is_editing && isset($this->promotion->stay_date->active_weekdays) && isset($this->promotion->stay_date->active_weekdays->active_weekday)) {
	if (is_array($this->promotion->stay_date->active_weekdays->active_weekday)) {
		// multiple week days is an array
		$active_weekdays = $this->promotion->stay_date->active_weekdays->active_weekday;
	} elseif (is_string($this->promotion->stay_date->active_weekdays->active_weekday) && !empty($this->promotion->stay_date->active_weekdays->active_weekday)) {
		// one active week day is a string, but it could be an empty node so !empty above is necessary
		$active_weekdays = array($this->promotion->stay_date->active_weekdays->active_weekday);
	}
}

// active excluded dates (Y-m-d)
$excluded_dates = array();
if ($is_editing && isset($this->promotion->stay_date->excluded_dates) && isset($this->promotion->stay_date->excluded_dates->excluded_date)) {
	if (is_array($this->promotion->stay_date->excluded_dates->excluded_date)) {
		// multiple excluded dates is an array
		$excluded_dates = $this->promotion->stay_date->excluded_dates->excluded_date;
	} elseif (is_string($this->promotion->stay_date->excluded_dates->excluded_date) && !empty($this->promotion->stay_date->excluded_dates->excluded_date)) {
		// one exclued date is a string, but it could be an empty node so !empty above is necessary
		$excluded_dates = array($this->promotion->stay_date->excluded_dates->excluded_date);
	}
}

// book time for basic deal
$is_booktime_set = ($is_editing && isset($this->promotion->book_time) && (int)$this->promotion->book_time->start >= 0);

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

<form action="index.php?option=com_vikchannelmanager" method="post" name="adminForm" id="adminForm">
	<div class="vcm-mngpromo-container">
		
		<div class="vcm-mngpromo-step">
			
			<div class="vcm-mngpromo-step-intro">
				<h3><?php echo JText::_('VCMBPROMPTYPE'); ?></h3>
				<p><?php echo JText::_('VCMBPROMPTYPEDESC'); ?></p>
			</div>
			
			<div class="vcm-mngpromo-step-cont">
				
				<div class="vcm-mngpromo-typebox<?php echo ($is_editing && $this->promotion->type == 'basic') || !$is_editing ? ' vcm-mngpromo-typebox-active' : ''; ?>" data-type="basic">
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

				<div class="vcm-mngpromo-typebox<?php echo $is_editing && $this->promotion->type == 'last_minute' ? ' vcm-mngpromo-typebox-active' : ''; ?>" data-type="last_minute">
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
						<p><?php echo JText::_('VCMBPROMLMDEALDSC2'); ?></p>
						<p><?php echo JText::_('VCMBPROMLMDEALDSC3'); ?></p>
					</div>
				</div>

				<div class="vcm-mngpromo-typebox<?php echo $is_editing && $this->promotion->type == 'early_booker' ? ' vcm-mngpromo-typebox-active' : ''; ?>" data-type="early_booker">
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

				<div class="vcm-mngpromo-typebox<?php echo $is_editing && $this->promotion->type == 'geo_rate' ? ' vcm-mngpromo-typebox-active' : ''; ?>" data-type="geo_rate">
					<div class="vcm-mngpromo-typebox-inner">
						<div class="vcm-mngpromo-typeimg">
							<img src="<?php echo VCM_ADMIN_URI . 'assets/css/images/bcom_promo_geo_rate.png'; ?>">
						</div>
						<div class="vcm-mngpromo-typetitle">
							<h4><?php echo JText::_('VCMBPROMGEODEALTIT'); ?></h4>
						</div>
						<div class="vcm-mngpromo-typesubtitle">
							<h5><?php echo JText::_('VCMBPROMGEODEALSUB'); ?></h5>
						</div>
					</div>
					<div class="vcm-mngpromo-typedescr">
						<p><?php echo JText::_('VCMBPROMBSDEALDSC1'); ?></p>
						<p><?php echo JText::_('VCMBPROMGEODEALDSC1'); ?></p>
					</div>
				</div>

				<div class="vcm-mngpromo-typebox<?php echo $is_editing && $this->promotion->type == 'mobile_rate' ? ' vcm-mngpromo-typebox-active' : ''; ?>" data-type="mobile_rate">
					<div class="vcm-mngpromo-typebox-inner">
						<div class="vcm-mngpromo-typeimg">
							<img src="<?php echo VCM_ADMIN_URI . 'assets/css/images/bcom_promo_mobile_rate.png'; ?>">
						</div>
						<div class="vcm-mngpromo-typetitle">
							<h4><?php echo JText::_('VCMBPROMMOBILEDEALTIT'); ?></h4>
						</div>
						<div class="vcm-mngpromo-typesubtitle">
							<h5><?php echo JText::_('VCMBPROMMOBILEDEALSUB'); ?></h5>
						</div>
					</div>
					<div class="vcm-mngpromo-typedescr">
						<p><?php echo JText::_('VCMBPROMBSDEALDSC1'); ?></p>
						<p><?php echo JText::_('VCMBPROMMOBILEDEALDSC1'); ?></p>
					</div>
				</div>

				<div class="vcm-mngpromo-typebox<?php echo $is_editing && $this->promotion->type == 'business_booker' ? ' vcm-mngpromo-typebox-active' : ''; ?>" data-type="business_booker">
					<div class="vcm-mngpromo-typebox-inner">
						<div class="vcm-mngpromo-typeimg">
							<img src="<?php echo VCM_ADMIN_URI . 'assets/css/images/bcom_promo_business_booker.png'; ?>">
						</div>
						<div class="vcm-mngpromo-typetitle">
							<h4><?php echo JText::_('VCMBPROMBUSINESSDEALTIT'); ?></h4>
						</div>
						<div class="vcm-mngpromo-typesubtitle">
							<h5><?php echo JText::_('VCMBPROMBUSINESSDEALSUB'); ?></h5>
						</div>
					</div>
					<div class="vcm-mngpromo-typedescr">
						<p><?php echo JText::_('VCMBPROMBSDEALDSC1'); ?></p>
						<p><?php echo JText::_('VCMBPROMBUSINESSDEALDSC1'); ?></p>
					</div>
				</div>

				<input type="hidden" name="promo_type" id="promotype" value="<?php echo $is_editing ? $this->promotion->type : 'basic'; ?>" />
			</div>

		</div>

		<div class="vcm-mngpromo-step vcm-mngpromo-step-conditional" style="display: none;" data-typecond="last_minute">

			<div class="vcm-mngpromo-step-intro">
				<h3><?php echo JText::_('VCMBPROMQWHERE'); ?></h3>
			</div>

			<div class="vcm-mngpromo-step-cont">

				<div class="vcm-mngpromo-lastmin-types">

					<div class="vcm-mngpromo-lastmin-type">
						<input type="radio" name="last_minute_unit" value="day" <?php echo !$is_editing || ($is_editing && isset($this->promotion->last_minute) && $this->promotion->last_minute->unit == 'day') ? 'checked="checked"' : ''; ?>/>
						<?php $inputString = "<input type=\"number\" name=\"last_minute_days\" value=\"".($is_editing && isset($this->promotion->last_minute) && $this->promotion->last_minute->unit == 'day' ? $this->promotion->last_minute->value : '0')."\" min=\"0\" />";?>
						<span><?php echo JText::sprintf("VCMBPROMWITHIND", $inputString);?></span>
					</div>

					<div class="vcm-mngpromo-lastmin-type">
						<input type="radio" name="last_minute_unit" value="hour" <?php echo $is_editing && isset($this->promotion->last_minute) && $this->promotion->last_minute->unit == 'hour' ? 'checked="checked"' : ''; ?>/>
						<?php $inputString = "<input type=\"number\" name=\"last_minute_hours\" value=\"".($is_editing && isset($this->promotion->last_minute) && $this->promotion->last_minute->unit == 'hour' ? $this->promotion->last_minute->value : '0')."\" min=\"0\" />";?>
						<span><?php echo JText::sprintf("VCMBPROMWITHINH", $inputString);?></span>
					</div>

				</div>
				
			</div>

		</div>

		<div class="vcm-mngpromo-step vcm-mngpromo-step-conditional" style="display: none;" data-typecond="early_booker">

			<div class="vcm-mngpromo-step-intro">
				<h3><?php echo JText::_('VCMBPROMQHOWEARLY'); ?></h3>
			</div>

			<div class="vcm-mngpromo-step-cont">

				<div class="vcm-mngpromo-earlybooker-types">

					<div class="vcm-mngpromo-earlybooker-type">
						<?php $inputString = "<input type=\"number\" name=\"early_booker_days\" value=\"".($is_editing && isset($this->promotion->early_booker) && $this->promotion->early_booker->value != '-1' ? $this->promotion->early_booker->value : '0')."\" min=\"0\" />";?>
						<span><?php echo JText::sprintf("VCMBPROMBEFORED", $inputString);?></span>
					</div>

				</div>
				
			</div>

		</div>

		<div class="vcm-mngpromo-step vcm-mngpromo-step-conditional" style="display: none;" data-typecond="geo_rate">

			<div class="vcm-mngpromo-step-intro">
				<h3><?php echo JText::_('VCMBPROMGEOWHICHAREA'); ?></h3>
			</div>

			<div class="vcm-mngpromo-step-cont">

				<div class="vcm-mngpromo-lastmin-types">

					<div class="vcm-mngpromo-lastmin-type">
						<select name="geo_target_channel">
							<option value="public"<?php echo $is_editing && $this->promotion->target_channel == 'public' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMBPROMAEVERYONE'); ?></option>
							<option value="algeria_pos"<?php echo $is_editing && $this->promotion->target_channel == 'algeria_pos' ? ' selected="selected"' : ''; ?>>Algeria</option>
							<option value="argentina_pos"<?php echo $is_editing && $this->promotion->target_channel == 'argentina_pos' ? ' selected="selected"' : ''; ?>>Argentina</option>
							<option value="australia_pos"<?php echo $is_editing && $this->promotion->target_channel == 'australia_pos' ? ' selected="selected"' : ''; ?>>Australia</option>
							<option value="belarus_pos"<?php echo $is_editing && $this->promotion->target_channel == 'belarus_pos' ? ' selected="selected"' : ''; ?>>Belarus</option>
							<option value="brazil_pos"<?php echo $is_editing && $this->promotion->target_channel == 'brazil_pos' ? ' selected="selected"' : ''; ?>>Brazil</option>
							<option value="canada_pos"<?php echo $is_editing && $this->promotion->target_channel == 'canada_pos' ? ' selected="selected"' : ''; ?>>Canada</option>
							<option value="chile_pos"<?php echo $is_editing && $this->promotion->target_channel == 'chile_pos' ? ' selected="selected"' : ''; ?>>Chile</option>
							<option value="colombia_pos"<?php echo $is_editing && $this->promotion->target_channel == 'colombia_pos' ? ' selected="selected"' : ''; ?>>Colombia</option>
							<option value="domestic_pos"<?php echo $is_editing && $this->promotion->target_channel == 'domestic_pos' ? ' selected="selected"' : ''; ?>>Domestic</option>
							<option value="eu_pos"<?php echo $is_editing && $this->promotion->target_channel == 'eu_pos' ? ' selected="selected"' : ''; ?>>Europe</option>
							<option value="hong_kong_pos"<?php echo $is_editing && $this->promotion->target_channel == 'hong_kong_pos' ? ' selected="selected"' : ''; ?>>Hong_kong</option>
							<option value="india_pos"<?php echo $is_editing && $this->promotion->target_channel == 'india_pos' ? ' selected="selected"' : ''; ?>>India</option>
							<option value="indonesia_pos"<?php echo $is_editing && $this->promotion->target_channel == 'indonesia_pos' ? ' selected="selected"' : ''; ?>>Indonesia</option>
							<option value="international_pos"<?php echo $is_editing && $this->promotion->target_channel == 'international_pos' ? ' selected="selected"' : ''; ?>>International</option>
							<option value="iran_pos"<?php echo $is_editing && $this->promotion->target_channel == 'iran_pos' ? ' selected="selected"' : ''; ?>>Iran</option>
							<option value="israel_pos"<?php echo $is_editing && $this->promotion->target_channel == 'israel_pos' ? ' selected="selected"' : ''; ?>>Israel</option>
							<option value="japan_pos"<?php echo $is_editing && $this->promotion->target_channel == 'japan_pos' ? ' selected="selected"' : ''; ?>>Japan</option>
							<option value="kazakhstan_pos"<?php echo $is_editing && $this->promotion->target_channel == 'kazakhstan_pos' ? ' selected="selected"' : ''; ?>>Kazakhstan</option>
							<option value="kuwait_pos"<?php echo $is_editing && $this->promotion->target_channel == 'kuwait_pos' ? ' selected="selected"' : ''; ?>>Kuwait</option>
							<option value="malaysia_pos"<?php echo $is_editing && $this->promotion->target_channel == 'malaysia_pos' ? ' selected="selected"' : ''; ?>>Malaysia</option>
							<option value="mexico_pos"<?php echo $is_editing && $this->promotion->target_channel == 'mexico_pos' ? ' selected="selected"' : ''; ?>>Mexico</option>
							<option value="argentina_pos"<?php echo $is_editing && $this->promotion->target_channel == 'argentina_pos' ? ' selected="selected"' : ''; ?>>Argentina</option>
							<option value="new_zealand_pos"<?php echo $is_editing && $this->promotion->target_channel == 'new_zealand_pos' ? ' selected="selected"' : ''; ?>>New Zealand</option>
							<option value="oman_pos"<?php echo $is_editing && $this->promotion->target_channel == 'oman_pos' ? ' selected="selected"' : ''; ?>>Oman</option>
							<option value="pakistan_pos"<?php echo $is_editing && $this->promotion->target_channel == 'pakistan_pos' ? ' selected="selected"' : ''; ?>>Pakistan</option>
							<option value="peru_pos"<?php echo $is_editing && $this->promotion->target_channel == 'peru_pos' ? ' selected="selected"' : ''; ?>>Peru</option>
							<option value="philippines_pos"<?php echo $is_editing && $this->promotion->target_channel == 'philippines_pos' ? ' selected="selected"' : ''; ?>>Philippines</option>
							<option value="qatar_pos"<?php echo $is_editing && $this->promotion->target_channel == 'qatar_pos' ? ' selected="selected"' : ''; ?>>Qatar</option>
							<option value="russia_pos"<?php echo $is_editing && $this->promotion->target_channel == 'russia_pos' ? ' selected="selected"' : ''; ?>>Russia</option>
							<option value="saudi_arabia_pos"<?php echo $is_editing && $this->promotion->target_channel == 'saudi_arabia_pos' ? ' selected="selected"' : ''; ?>>Saudi Arabia</option>
							<option value="singapore_pos"<?php echo $is_editing && $this->promotion->target_channel == 'singapore_pos' ? ' selected="selected"' : ''; ?>>Singapore</option>
							<option value="south_africa_pos"<?php echo $is_editing && $this->promotion->target_channel == 'south_africa_pos' ? ' selected="selected"' : ''; ?>>South Africa</option>
							<option value="south_korea_pos"<?php echo $is_editing && $this->promotion->target_channel == 'south_korea_pos' ? ' selected="selected"' : ''; ?>>South Korea</option>
							<option value="switzerland_pos"<?php echo $is_editing && $this->promotion->target_channel == 'switzerland_pos' ? ' selected="selected"' : ''; ?>>Switzerland</option>
							<option value="taiwan_pos"<?php echo $is_editing && $this->promotion->target_channel == 'taiwan_pos' ? ' selected="selected"' : ''; ?>>Taiwan</option>
							<option value="thailand_pos"<?php echo $is_editing && $this->promotion->target_channel == 'thailand_pos' ? ' selected="selected"' : ''; ?>>Thailand</option>
							<option value="trinidad_&_trinidad_tobago_pos"<?php echo $is_editing && $this->promotion->target_channel == 'trinidad_&_trinidad_tobago_pos' ? ' selected="selected"' : ''; ?>>Trinidad and Tobago</option>
							<option value="turkey_pos"<?php echo $is_editing && $this->promotion->target_channel == 'turkey_pos' ? ' selected="selected"' : ''; ?>>Turkey</option>
							<option value="ukraine_pos"<?php echo $is_editing && $this->promotion->target_channel == 'ukraine_pos' ? ' selected="selected"' : ''; ?>>Ukraine</option>
							<option value="united_arab_emirates_pos"<?php echo $is_editing && $this->promotion->target_channel == 'united_arab_emirates_pos' ? ' selected="selected"' : ''; ?>>United Arab Emirates</option>
							<option value="united_states_pos"<?php echo $is_editing && $this->promotion->target_channel == 'united_states_pos' ? ' selected="selected"' : ''; ?>>United States</option>
							<option value="vietnam_pos"<?php echo $is_editing && $this->promotion->target_channel == 'vietnam_pos' ? ' selected="selected"' : ''; ?>>Vietnam</option>
						</select>
					</div>

				</div>
				
			</div>

		</div>

		<div class="vcm-mngpromo-step vcm-mngpromo-step-conditional" style="display: none;" data-typecond="basic last_minute early_booker mobile_rate business_booker">

			<div class="vcm-mngpromo-step-intro">
				<h3><?php echo JText::_('VCMBPROMQWHO'); ?></h3>
			</div>

			<div class="vcm-mngpromo-step-cont">

				<div class="vcm-mngpromo-targetbox<?php echo ($is_editing && $this->promotion->target_channel == 'public') || !$is_editing ? ' vcm-mngpromo-targetbox-active' : ''; ?>" data-target="public">
					<div class="vcm-mngpromo-targetbox-inner">
						<div class="vcm-mngpromo-targetimg">
							<img src="<?php echo VCM_ADMIN_URI . 'assets/css/images/bcom_promo_everyone.png'; ?>">
						</div>
						<div class="vcm-mngpromo-targettitle">
							<h4><?php echo JText::_('VCMBPROMAEVERYONE'); ?></h4>
						</div>
					</div>
				</div>

				<div class="vcm-mngpromo-targetbox<?php echo $is_editing && $this->promotion->target_channel == 'subscribers' ? ' vcm-mngpromo-targetbox-active' : ''; ?>" data-target="subscribers">
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

				<input type="hidden" name="target_channel" id="target_channel" value="<?php echo $is_editing ? $this->promotion->target_channel : 'public'; ?>" />
			</div>

		</div>

		<div class="vcm-mngpromo-step">

			<div class="vcm-mngpromo-step-intro">
				<h3><?php echo JText::_('VCMBPROMQSTAY'); ?></h3>
			</div>

			<div class="vcm-mngpromo-step-cont">

				<div class="vcm-mngpromo-minstay">
					<span><?php echo JText::_('VCMBPROMAMINNIGHTS');?></span>
					<input type="number" name="min_stay_through" value="<?php echo $is_editing ? $this->promotion->min_stay_through : '1'; ?>" min="1" />
				</div>

			</div>

		</div>

		<div class="vcm-mngpromo-step">

			<div class="vcm-mngpromo-step-intro">
				<h3><?php echo JText::_('VCMBPROMQMUCH'); ?></h3>
			</div>

			<div class="vcm-mngpromo-step-cont">

				<div class="vcm-mngpromo-discount">
					<input type="number" name="discount" value="<?php echo $is_editing ? $this->promotion->discount->value : '10'; ?>" min="1" max="99" />
					<span>%</span>
				</div>

			</div>

		</div>

		<div class="vcm-mngpromo-step">

			<div class="vcm-mngpromo-step-intro">
				<h3><?php echo JText::_('VCMBPROMQRATES'); ?></h3>
				<p><?php echo JText::_('VCMBPROMQRATESDESC'); ?></p>
			</div>

			<div class="vcm-mngpromo-step-cont">

				<div class="vcm-mngpromo-rplans">
				<?php
				foreach ($otarplans as $rplanid => $rplanname) {
					?>
					<div class="vcm-mngpromo-choice-entry vcm-mngpromo-rplan-block">
						<input type="checkbox" name="rplans[]" value="<?php echo $rplanid; ?>" id="rplan<?php echo $rplanid; ?>" <?php echo in_array($rplanid, $active_rplans_pool) ? 'checked="checked"' : ''; ?>/>
						<label for="rplan<?php echo $rplanid; ?>"><?php echo $rplanname . ' (' . $rplanid . ')'; ?></label>
					</div>
					<?php
				}
				?>
				</div>

			</div>

		</div>

		<div class="vcm-mngpromo-step">

			<div class="vcm-mngpromo-step-intro">
				<h3><?php echo JText::_('VCMBPROMQROOMS'); ?></h3>
				<p><?php echo JText::_('VCMBPROMQROOMSDESC'); ?></p>
			</div>

			<div class="vcm-mngpromo-step-cont">

				<div class="vcm-mngpromo-rooms">
				<?php
				foreach ($this->otarooms as $otar) {
					// make sure the hotel ID matches with the active one
					if (!empty($otar['prop_params'])) {
						$prop_params = json_decode($otar['prop_params'], true);
						if (isset($prop_params['hotelid']) && $prop_params['hotelid'] != $this->channel['params']['hotelid']) {
							// skip this room mapping as it's for a different hotel ID
							continue;
						}
					}
					?>
					<div class="vcm-mngpromo-choice-entry vcm-mngpromo-room-block">
						<input type="checkbox" name="rooms[]" value="<?php echo $otar['idroomota']; ?>" id="room<?php echo $otar['idroomota']; ?>" <?php echo in_array($otar['idroomota'], $active_rooms_pool) ? 'checked="checked"' : ''; ?>/>
						<label for="room<?php echo $otar['idroomota']; ?>"><?php echo $otar['otaroomname']; ?></label>
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

					<div class="vcm-mngpromo-dates-stay-wdays">
						<div class="vcm-mngpromo-wday-block">
							<input type="checkbox" name="wdays[]" value="Mon" id="wdaymon" <?php echo !$is_editing || ($is_editing && in_array('Mon', $active_weekdays)) ? 'checked="checked"' : ''; ?>/>
							<label for="wdaymon"><?php echo JText::_('VCMJQCALMON'); ?></label>
						</div>
						<div class="vcm-mngpromo-wday-block">
							<input type="checkbox" name="wdays[]" value="Tue" id="wdaytue" <?php echo !$is_editing || ($is_editing && in_array('Tue', $active_weekdays)) ? 'checked="checked"' : ''; ?>/>
							<label for="wdaytue"><?php echo JText::_('VCMJQCALTUE'); ?></label>
						</div>
						<div class="vcm-mngpromo-wday-block">
							<input type="checkbox" name="wdays[]" value="Wed" id="wdaywed" <?php echo !$is_editing || ($is_editing && in_array('Wed', $active_weekdays)) ? 'checked="checked"' : ''; ?>/>
							<label for="wdaywed"><?php echo JText::_('VCMJQCALWED'); ?></label>
						</div>
						<div class="vcm-mngpromo-wday-block">
							<input type="checkbox" name="wdays[]" value="Thu" id="wdaythu" <?php echo !$is_editing || ($is_editing && in_array('Thu', $active_weekdays)) ? 'checked="checked"' : ''; ?>/>
							<label for="wdaythu"><?php echo JText::_('VCMJQCALTHU'); ?></label>
						</div>
						<div class="vcm-mngpromo-wday-block">
							<input type="checkbox" name="wdays[]" value="Fri" id="wdayfri" <?php echo !$is_editing || ($is_editing && in_array('Fri', $active_weekdays)) ? 'checked="checked"' : ''; ?>/>
							<label for="wdayfri"><?php echo JText::_('VCMJQCALFRI'); ?></label>
						</div>
						<div class="vcm-mngpromo-wday-block">
							<input type="checkbox" name="wdays[]" value="Sat" id="wdaysat" <?php echo !$is_editing || ($is_editing && in_array('Sat', $active_weekdays)) ? 'checked="checked"' : ''; ?>/>
							<label for="wdaysat"><?php echo JText::_('VCMJQCALSAT'); ?></label>
						</div>
						<div class="vcm-mngpromo-wday-block">
							<input type="checkbox" name="wdays[]" value="Sun" id="wdaysun" <?php echo !$is_editing || ($is_editing && in_array('Sun', $active_weekdays)) ? 'checked="checked"' : ''; ?>/>
							<label for="wdaysun"><?php echo JText::_('VCMJQCALSUN'); ?></label>
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

		<div class="vcm-mngpromo-step">

			<div class="vcm-mngpromo-step-intro">
				<h3><?php echo JText::_('VCMBPROMQNAME'); ?></h3>
				<p><?php echo JText::_('VCMBPROMQNAMEDESC1'); ?></p>
			</div>

			<div class="vcm-mngpromo-step-cont">

				<div class="vcm-mngpromo-name">
					<input type="text" name="name" id="promo-name" value="<?php echo $is_editing ? $this->promotion->name : ''; ?>" size="40" />
				</div>

			</div>

		</div>

		<div class="vcm-mngpromo-step">

			<div class="vcm-mngpromo-step-intro">
				<h3><?php echo JText::_('VCMBPROMONONREF'); ?></h3>
			</div>

			<div class="vcm-mngpromo-step-cont">

				<div class="vcm-mngpromo-choice-entry vcm-mngpromo-ckbox">
					<input type="checkbox" name="non_ref" value="1" id="non_ref" <?php echo $is_editing && (int)$this->promotion->non_refundable > 0 ? 'checked="checked"' : ''; ?>/>
					<label for="non_ref"><?php echo JText::_('VCMBPROMONONREFDESC');?></label>
				</div>

			</div>

		</div>

		<div class="vcm-mngpromo-step">

			<div class="vcm-mngpromo-step-intro">
				<h3><?php echo JText::_('VCMBPROMONOCRED'); ?></h3>
			</div>

			<div class="vcm-mngpromo-step-cont">

				<div class="vcm-mngpromo-choice-entry vcm-mngpromo-ckbox">
					<input type="checkbox" name="no_cc" value="1" id="no_cc" <?php echo $is_editing && (int)$this->promotion->no_cc_promotion > 0 ? 'checked="checked"' : ''; ?>/>
					<label for="no_cc"><?php echo JText::_('VCMBPROMONOCREDDESC');?></label>
				</div>

			</div>

		</div>

		<div class="vcm-mngpromo-step vcm-mngpromo-step-conditional" style="display: none;" data-typecond="basic geo_rate mobile_rate business_booker">

			<div class="vcm-mngpromo-step-intro">
				<h3><?php echo JText::_('VCMBPROMQVIS'); ?></h3>
				<p><?php echo JText::_('VCMBPROMQVISDESC'); ?></p>
			</div>

			<div class="vcm-mngpromo-step-cont">

				<div class="vcm-mngpromo-dates-book-top">

					<div class="vcm-mngpromo-dates vcm-mngpromo-dates-book">
						<div class="vcm-mngpromo-date-from">
							<span>
								<i class="vboicn-calendar"></i>
								<input type="text" class="vcm-mngpromo-bookdate" name="bookfromdate" id="bookfromdate" value="" />
							</span>
						</div>
						<div class="vcm-mngpromo-date-to">
							<span>
								<i class="vboicn-calendar"></i>
								<input type="text" class="vcm-mngpromo-bookdate" name="booktodate" id="booktodate" value="" />
							</span>
						</div>
					</div>

				</div>

			</div>

		</div>

		<div class="vcm-mngpromo-step vcm-mngpromo-step-conditional" style="display: none;" data-typecond="basic geo_rate mobile_rate business_booker">

			<div class="vcm-mngpromo-step-intro">
				<h3><?php echo JText::_('VCMBPROMOTIME'); ?></h3>
			</div>

			<div class="vcm-mngpromo-step-cont">

				<div class="vcm-mngpromo-choice-entry vcm-mngpromo-book-time-top">
					<input type="checkbox" name="book_time" id="book_time" value="1" onchange="vcmPromoBookTimeToggle(this.checked);" <?php echo $is_booktime_set ? 'checked="checked"' : ''; ?>/>
					<label for="book_time"><?php echo JText::_('VCMBPROMOTIMEDESC');?></label>
				</div>

				<div class="vcm-mngpromo-book-time-bottom">
					<span class="vcm-mngpromo-book-time-from">
						<select name="book_time_start" id="book_time_start"<?php echo !$is_booktime_set ? ' disabled="disabled"' : ''; ?>>
						<?php
						$cur_booktime_start = $is_booktime_set ? (int)$this->promotion->book_time->start : -1;
						for ($i = 0; $i < 24; $i++) {
							$timelbl = ($i > 12 ? ($i - 12) : $i) . ' ' . ($i < 12 ? 'AM' : 'PM');
							?>
							<option value="<?php echo $i; ?>"<?php echo $cur_booktime_start == $i ? ' selected="selected"' : '' ; ?>><?php echo $timelbl; ?></option>
							<?php
						}
						?>
						</select>
					</span>
					<span class="vcm-mngpromo-book-time-txt"><?php echo JText::_('VCMBCAHTO'); ?></span>
					<span class="vcm-mngpromo-book-time-to">
						<select name="book_time_end" id="book_time_end"<?php echo !$is_booktime_set ? ' disabled="disabled"' : ''; ?>>
						<?php
						$cur_booktime_end = $is_booktime_set ? (int)$this->promotion->book_time->end : -1;
						for ($i = 0; $i < 24; $i++) {
							$timelbl = ($i > 12 ? ($i - 12) : $i) . ' ' . ($i < 12 ? 'AM' : 'PM');
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
<a id="vcm-hidden-return" href="index.php?option=com_vikchannelmanager&task=bpromo" style="display: none;"></a>
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
function vcmPromoBookTimeToggle(checked) {
	if (checked) {
		jQuery('#book_time_start, #book_time_end').prop('disabled', false);
	} else {
		jQuery('#book_time_start, #book_time_end').prop('disabled', true);
	}
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
	// target channel
	jQuery('.vcm-mngpromo-targetbox').click(function() {
		var ptarget = jQuery(this).attr('data-target');
		jQuery('#target_channel').val(ptarget);
		jQuery('.vcm-mngpromo-targetbox').removeClass('vcm-mngpromo-targetbox-active');
		jQuery(this).addClass('vcm-mngpromo-targetbox-active');
	});
	// stay dates datepicker
	jQuery('.vcm-mngpromo-staydate:input').datepicker({
		dateFormat: "yy-mm-dd",
		minDate: 0,
		maxDate: "+2y",
		onSelect: vcmCheckStayDates
	});
	// excluded dates datepicker
	jQuery('.vcm-mngpromo-excldate:input').datepicker({
		dateFormat: "yy-mm-dd",
		minDate: 0,
		maxDate: "+2y",
		onSelect: vcmCheckExclDate
	});
	// stay dates datepicker
	jQuery('.vcm-mngpromo-bookdate:input').datepicker({
		dateFormat: "yy-mm-dd",
		minDate: 0,
		maxDate: "+2y",
		onSelect: vcmCheckBookDates
	});
<?php
// populate dates in datepicker calendars
if ($is_editing) {
	?>
	jQuery('#stayfromdate').datepicker( 'setDate', '<?php echo $this->promotion->stay_date->start; ?>' );
	jQuery('#staytodate').datepicker( 'setDate', '<?php echo $this->promotion->stay_date->end; ?>' );
	<?php
	if (isset($this->promotion->book_date)) {
		if (isset($this->promotion->book_date->start) && (string)$this->promotion->book_date->start != '-1') {
			?>
	jQuery('#bookfromdate').datepicker( 'setDate', '<?php echo $this->promotion->book_date->start; ?>' );
			<?php
		}
		if (isset($this->promotion->book_date->end) && (string)$this->promotion->book_date->end != '-1') {
			?>
	jQuery('#booktodate').datepicker( 'setDate', '<?php echo $this->promotion->book_date->end; ?>' );
			<?php
		}
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
	if (task == 'bpromo.savepromo' || task == 'bpromo.updatepromo') {
		// submit form to controller
		vcmSubmitPromotion(task);

		// exit
		return false;
	}
	// other buttons can submit the form normally
	Joomla.submitform(task, document.adminForm);
}
</script>
