<?php  
/**
 * @package     VikBooking
 * @subpackage  mod_vikbooking_channelrates
 * @author      Alessio Gaggii - E4J s.r.l
 * @copyright   Copyright (C) 2018 E4J s.r.l. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

$session = JFactory::getSession();
$dbo = JFactory::getDbo();
$vbo_tn = VikBookingChannelRatesHelper::getTranslator();
$cookie = JFactory::getApplication()->input->cookie;
$cookie_hide = $cookie->get('vboVcmMcrh', '0', 'string');

$is_mobile = VikBooking::detectUserAgent(false, false);

// load brands icons
VikBookingIcons::loadRemoteAssets();

$document = JFactory::getDocument();
$document->addStyleSheet($baseurl.'modules/mod_vikbooking_channelrates/mod_vikbooking_channelrates.css');

// load jQuery UI
$document->addStyleSheet(VBO_SITE_URI.'resources/jquery-ui.min.css');
JHtml::_('script', VBO_SITE_URI.'resources/jquery-ui.min.js');

// load DRP assets
VikBooking::getVboApplication()->loadDatesRangePicker(['type' => 'dates_range']);

// restrictions
$restrictions = VikBookingChannelRatesHelper::loadRestrictions();

$vbdateformat = VikBookingChannelRatesHelper::getDateFormat();
if ($vbdateformat == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($vbdateformat == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}
$timeopst = VikBookingChannelRatesHelper::getTimeOpenStore();
if (is_array($timeopst)) {
	$opent = VikBookingChannelRatesHelper::getHoursMinutes($timeopst[0]);
	$closet = VikBookingChannelRatesHelper::getHoursMinutes($timeopst[1]);
	$hcheckin = $opent[0];
	$mcheckin = $opent[1];
	$hcheckout = $closet[0];
	$mcheckout = $closet[1];
} else {
	$hcheckin = 0;
	$mcheckin = 0;
	$hcheckout = 0;
	$mcheckout = 0;
}
$default_dates = VikBookingChannelRatesHelper::getDefaultDates($hcheckin, $mcheckin, $hcheckout, $mcheckout);
$def_adults = 2;
$def_rplan = (int)$params->get('def_idprice', 0);
$def_rplan = $def_rplan > 0 ? $def_rplan : 0;
$show_tax = ((int)$params->get('tax', 0) > 0);
$currency_symb = VikBookingChannelRatesHelper::getCurrencySymb();
$num_decimals = (int)VikBookingChannelRatesHelper::gerNumberFormatValue(0);

$aspect_style = 'vbomodchrates-'.$params->get('modstyle', 'flat-vertical');
$intro_txt = $params->get('introtxt', '');
$intro_txt = empty($intro_txt) ? JText::_('VBOMODCMRINTROTXTDEF') : $intro_txt;
$channels_sel = $params->get('channels', array());
foreach ($channels_sel as $kcs => $vcs) {
	if (empty($vcs) || (int)$vcs < 1) {
		unset($channels_sel[$kcs]);
	}
}
if (count($channels_sel)) {
	$channels_sel = array_values($channels_sel);
}
$channels_map = VikBookingChannelRatesHelper::getChannelsMap($channels_sel);

//calculate rates for the default dates and adults (result could not be an array, or it could be an array with the error code)
$website_rates = VikBookingChannelRatesHelper::fetchWebsiteRates(date('Y-m-d', $default_dates[0]), date('Y-m-d', $default_dates[1]), $default_dates[2], array($def_adults), array(0));
//get the array with the lowest and preferred room rate
$best_room_rate = VikBookingChannelRatesHelper::getBestRoomRate($website_rates, $def_rplan);
//get the charge/discount value for the OTAs rates from the Bulk Rates Cache of VCM
$otas_rates_val = VikBookingChannelRatesHelper::getOtasRatesVal($best_room_rate, $params, true);

$otas_rmod = '';
$otas_rmodpcent = 0;
$otas_rmodval = 0;
$otas_rmod_channels = array();
if (!empty($otas_rates_val)) {
	if (is_array($otas_rates_val)) {
		$otas_rmod_channels = $otas_rates_val;
		$use_rates_val = $otas_rates_val[0];
	} else {
		// string
		$use_rates_val = $otas_rates_val;
	}
	$otas_rmod = substr($use_rates_val, 0, 1); //+ or - (charge or discount)
	$otas_rmodpcent = substr($use_rates_val, -1) == '%' ? 1 : 0;
	$otas_rmodval = (float)($otas_rmodpcent > 0 ? substr($use_rates_val, 1, (strlen($use_rates_val) - 2)) : substr($use_rates_val, 1, (strlen($use_rates_val) - 1)));
}
?>

<div class="vbomodchrates-container <?php echo (empty($cookie_hide) || $cookie_hide < 1 ? 'vbomodchrates-container-active ' : '') . $aspect_style; ?>">
	<div class="vbomodchrates-maindiv">
		<div class="vbomodchrates-close-cont" id="vbomodchrates-close-cont<?php echo $randid; ?>"<?php echo (!empty($cookie_hide) && (int)$cookie_hide > 0 ? ' style="display: none;"' : ''); ?>><?php VikBookingIcons::e('times-circle'); ?></div>
		<div class="vbomodchrates-open-cont" id="vbomodchrates-open-cont<?php echo $randid; ?>"<?php echo (empty($cookie_hide) || (int)$cookie_hide < 1 ? ' style="display: none;"' : ''); ?>><?php VikBookingIcons::e('chevron-up'); ?></div>
	<?php
	if (strlen($intro_txt) > 1) {
		//to hide the intro-text, users should set a value of length 1, like an empty space.
		?>
		<div class="vbomodchrates-introtxt" id="vbomodchrates-introtxt<?php echo $randid; ?>">
			<?php echo $intro_txt; ?>
		</div>
		<?php
	}
	?>
		<div class="vbomodchrates-inner-cont" id="vbomodchrates-inner-cont<?php echo $randid; ?>"<?php echo (!empty($cookie_hide) && (int)$cookie_hide > 0 ? ' style="display: none;"' : ''); ?>>
			<form action="<?php echo JRoute::_('index.php?option=com_vikbooking'.(($force_menu_itemid = $params->get('itemid')) ? '&Itemid='.$force_menu_itemid : '')); ?>" method="get">
				<input type="hidden" name="option" value="com_vikbooking" />
				<input type="hidden" name="task" value="search" />
<?php
if ($vbdateformat == "%d/%m/%Y") {
	$juidf = 'dd/mm/yy';
} elseif ($vbdateformat == "%m/%d/%Y") {
	$juidf = 'mm/dd/yy';
} else {
	$juidf = 'yy/mm/dd';
}

// lang for jQuery UI Calendar
$is_rtl_str = 'false';
$now_lang = JFactory::getLanguage();
if (method_exists($now_lang, 'isRtl')) {
	$is_rtl_str = $now_lang->isRtl() ? 'true' : $is_rtl_str;
}

// months names list "full"
$month_names_full_list = [
	JText::_('JANUARY'),
	JText::_('FEBRUARY'),
	JText::_('MARCH'),
	JText::_('APRIL'),
	JText::_('MAY'),
	JText::_('JUNE'),
	JText::_('JULY'),
	JText::_('AUGUST'),
	JText::_('SEPTEMBER'),
	JText::_('OCTOBER'),
	JText::_('NOVEMBER'),
	JText::_('DECEMBER'),
];

// month names list "short"
$month_names_short_list = [
	JText::_('JANUARY_SHORT'),
	JText::_('FEBRUARY_SHORT'),
	JText::_('MARCH_SHORT'),
	JText::_('APRIL_SHORT'),
	JText::_('MAY_SHORT'),
	JText::_('JUNE_SHORT'),
	JText::_('JULY_SHORT'),
	JText::_('AUGUST_SHORT'),
	JText::_('SEPTEMBER_SHORT'),
	JText::_('OCTOBER_SHORT'),
	JText::_('NOVEMBER_SHORT'),
	JText::_('DECEMBER_SHORT'),
];

// week day names list "full"
$day_names_full_list = [
	JText::_('SUNDAY'),
	JText::_('MONDAY'),
	JText::_('TUESDAY'),
	JText::_('WEDNESDAY'),
	JText::_('THURSDAY'),
	JText::_('FRIDAY'),
	JText::_('SATURDAY'),
];

// list of week day names for "short" and "min" versions
$day_names_short_list = [
	JText::_('SUN'),
	JText::_('MON'),
	JText::_('TUE'),
	JText::_('WED'),
	JText::_('THU'),
	JText::_('FRI'),
	JText::_('SAT'),
];
$day_names_min_list = [];
foreach ($day_names_short_list as $wdn) {
	$day_names_min_list[] = mb_substr($wdn, 0, 2, 'UTF-8');
}

// ensure this language does not produce conflicting week-days "min"
$day_names_min_list = array_unique($day_names_min_list);
if (count($day_names_min_list) != count($day_names_short_list)) {
	// fallback onto the "short" week-days list to avoid conflicts with this language
	$day_names_min_list = $day_names_short_list;
}

$ldecl = '
jQuery.noConflict();
jQuery(function($) {'."\n".'
	$.datepicker.regional["vikbookingmod"] = {'."\n".'
		closeText: "'.JText::_('VBJQCALDONE').'",'."\n".'
		prevText: "'.JText::_('VBJQCALPREV').'",'."\n".'
		nextText: "'.JText::_('VBJQCALNEXT').'",'."\n".'
		currentText: "'.JText::_('VBJQCALTODAY').'",'."\n".'
		monthNames: ' . json_encode($month_names_full_list) . ','."\n".'
		monthNamesShort: ' . json_encode($month_names_short_list) . ','."\n".'
		dayNames: ' . json_encode($day_names_full_list) . ','."\n".'
		dayNamesShort: ' . json_encode($day_names_short_list) . ','."\n".'
		dayNamesMin: ' . json_encode($day_names_min_list) . ','."\n".'
		weekHeader: "'.JText::_('VBJQCALWKHEADER').'",'."\n".'
		dateFormat: "'.$juidf.'",'."\n".'
		firstDay: '.VikBooking::getFirstWeekDay().','."\n".'
		isRTL: ' . $is_rtl_str . ','."\n".'
		showMonthAfterYear: false,'."\n".'
		yearSuffix: ""'."\n".'
	};'."\n".'
	$.datepicker.setDefaults($.datepicker.regional["vikbookingmod"]);'."\n".'
});
function vbGetDateObject'.$randid.'(dstring) {
	var dparts = dstring.split("-");
	return new Date(dparts[0], (parseInt(dparts[1]) - 1), parseInt(dparts[2]), 0, 0, 0, 0);
}
function vbFullObject'.$randid.'(obj) {
	var jk;
	for(jk in obj) {
		return obj.hasOwnProperty(jk);
	}
}
var vbrestrctarange, vbrestrctdrange, vbrestrcta, vbrestrctd;';
$document->addScriptDeclaration($ldecl);

// global restrictions
$totrestrictions               = count($restrictions);
$wdaysrestrictions             = [];
$wdaystworestrictions          = [];
$wdaysrestrictionsrange        = [];
$wdaysrestrictionsmonths       = [];
$ctarestrictionsrange          = [];
$ctarestrictionsmonths         = [];
$ctdrestrictionsrange          = [];
$ctdrestrictionsmonths         = [];
$monthscomborestr              = [];
$minlosrestrictions            = [];
$minlosrestrictionsrange       = [];
$maxlosrestrictions            = [];
$maxlosrestrictionsrange       = [];
$notmultiplyminlosrestrictions = [];
if ($totrestrictions > 0) {
	foreach ($restrictions as $rmonth => $restr) {
		if ($rmonth != 'range') {
			if (strlen((string)$restr['wday'])) {
				$wdaysrestrictions[($rmonth - 1)] = $restr['wday'];
				$wdaysrestrictionsmonths[] = $rmonth;
				if (strlen((string)$restr['wdaytwo'])) {
					$wdaystworestrictions[($rmonth - 1)] = $restr['wdaytwo'];
					$monthscomborestr[($rmonth - 1)] = VikBookingChannelRatesHelper::parseJsDrangeWdayCombo($restr);
				}
			} elseif (!empty($restr['ctad']) || !empty($restr['ctdd'])) {
				if (!empty($restr['ctad'])) {
					$ctarestrictionsmonths[($rmonth - 1)] = explode(',', $restr['ctad']);
				}
				if (!empty($restr['ctdd'])) {
					$ctdrestrictionsmonths[($rmonth - 1)] = explode(',', $restr['ctdd']);
				}
			}
			if ($restr['multiplyminlos'] == 0) {
				$notmultiplyminlosrestrictions[] = $rmonth;
			}
			$minlosrestrictions[($rmonth - 1)] = $restr['minlos'];
			if (!empty($restr['maxlos']) && $restr['maxlos'] > 0 && $restr['maxlos'] >= $restr['minlos']) {
				$maxlosrestrictions[($rmonth - 1)] = $restr['maxlos'];
			}
		} else {
			foreach ($restr as $kr => $drestr) {
				if (strlen((string)$drestr['wday'])) {
					$wdaysrestrictionsrange[$kr][0] = date('Y-m-d', $drestr['dfrom']);
					$wdaysrestrictionsrange[$kr][1] = date('Y-m-d', $drestr['dto']);
					$wdaysrestrictionsrange[$kr][2] = $drestr['wday'];
					$wdaysrestrictionsrange[$kr][3] = $drestr['multiplyminlos'];
					$wdaysrestrictionsrange[$kr][4] = strlen((string)$drestr['wdaytwo']) ? $drestr['wdaytwo'] : -1;
					$wdaysrestrictionsrange[$kr][5] = VikBookingChannelRatesHelper::parseJsDrangeWdayCombo($drestr);
				} elseif (!empty($drestr['ctad']) || !empty($drestr['ctdd'])) {
					$ctfrom = date('Y-m-d', $drestr['dfrom']);
					$ctto = date('Y-m-d', $drestr['dto']);
					if (!empty($drestr['ctad'])) {
						$ctarestrictionsrange[$kr][0] = $ctfrom;
						$ctarestrictionsrange[$kr][1] = $ctto;
						$ctarestrictionsrange[$kr][2] = explode(',', $drestr['ctad']);
					}
					if (!empty($drestr['ctdd'])) {
						$ctdrestrictionsrange[$kr][0] = $ctfrom;
						$ctdrestrictionsrange[$kr][1] = $ctto;
						$ctdrestrictionsrange[$kr][2] = explode(',', $drestr['ctdd']);
					}
				}
				$minlosrestrictionsrange[$kr][0] = date('Y-m-d', $drestr['dfrom']);
				$minlosrestrictionsrange[$kr][1] = date('Y-m-d', $drestr['dto']);
				$minlosrestrictionsrange[$kr][2] = $drestr['minlos'];
				if (!empty($drestr['maxlos']) && $drestr['maxlos'] > 0 && $drestr['maxlos'] >= $drestr['minlos']) {
					$maxlosrestrictionsrange[$kr] = $drestr['maxlos'];
				}
			}
			unset($restrictions['range']);
		}
	}
		
	$resdecl = "
var vbrestrmonthswdays = " . json_encode($wdaysrestrictionsmonths) . ";
var vbrestrmonths = " . json_encode(array_keys($restrictions)) . ";
var vbrestrmonthscombojn = " . json_encode($monthscomborestr) . ";
var vbrestrminlos = " . json_encode((object) $minlosrestrictions) . ";
var vbrestrminlosrangejn = " . json_encode($minlosrestrictionsrange) . ";
var vbrestrmultiplyminlos = " . json_encode($notmultiplyminlosrestrictions) . ";
var vbrestrmaxlos = " . json_encode((object) $maxlosrestrictions) . ";
var vbrestrmaxlosrangejn = " . json_encode($maxlosrestrictionsrange) . ";
var vbrestrwdaysrangejn = " . json_encode($wdaysrestrictionsrange) . ";
var vbrestrcta = " . json_encode($ctarestrictionsmonths) . ";
var vbrestrctarange = " . json_encode($ctarestrictionsrange) . ";
var vbrestrctd = " . json_encode($ctdrestrictionsmonths) . ";
var vbrestrctdrange = " . json_encode($ctdrestrictionsrange) . ";
var vbcombowdays = {};
function vbRefreshCheckout".$randid."(darrive) {
	if (vbFullObject".$randid."(vbcombowdays)) {
		var vbtosort = new Array();
		for(var vbi in vbcombowdays) {
			if (vbcombowdays.hasOwnProperty(vbi)) {
				var vbusedate = darrive;
				vbtosort[vbi] = vbusedate.setDate(vbusedate.getDate() + (vbcombowdays[vbi] - 1 - vbusedate.getDay() + 7) % 7 + 1);
			}
		}
		vbtosort.sort(function(da, db) {
			return da > db ? 1 : -1;
		});
		for(var vbnext in vbtosort) {
			if (vbtosort.hasOwnProperty(vbnext)) {
				var vbfirstnextd = new Date(vbtosort[vbnext]);
				jQuery('#checkindatemod".$randid."').vboDatesRangePicker('checkout', 'minDate', vbfirstnextd);
				jQuery('#checkindatemod".$randid."').vboDatesRangePicker('checkout', 'setcheckoutdate', vbfirstnextd);
				break;
			}
		}
	}
}
function vbSetMinCheckoutDatemod".$randid."(selectedDate) {
	var minlos = ".VikBooking::getDefaultNightsCalendar().";
	var maxlosrange = 0;
	var nowcheckin = jQuery('#checkindatemod".$randid."').vboDatesRangePicker('getCheckinDate');
	var nowd = nowcheckin.getDay();
	var nowcheckindate = new Date(nowcheckin.getTime());
	vbcombowdays = {};
	if (vbFullObject".$randid."(vbrestrminlosrangejn)) {
		for (var rk in vbrestrminlosrangejn) {
			if (vbrestrminlosrangejn.hasOwnProperty(rk)) {
				var minldrangeinit = vbGetDateObject".$randid."(vbrestrminlosrangejn[rk][0]);
				if (nowcheckindate >= minldrangeinit) {
					var minldrangeend = vbGetDateObject".$randid."(vbrestrminlosrangejn[rk][1]);
					if (nowcheckindate <= minldrangeend) {
						minlos = parseInt(vbrestrminlosrangejn[rk][2]);
						if (vbFullObject".$randid."(vbrestrmaxlosrangejn)) {
							if (rk in vbrestrmaxlosrangejn) {
								maxlosrange = parseInt(vbrestrmaxlosrangejn[rk]);
							}
						}
						if (rk in vbrestrwdaysrangejn && nowd in vbrestrwdaysrangejn[rk][5]) {
							vbcombowdays = vbrestrwdaysrangejn[rk][5][nowd];
						}
					}
				}
			}
		}
	}
	var nowm = nowcheckin.getMonth();
	if (vbFullObject".$randid."(vbrestrmonthscombojn) && vbrestrmonthscombojn.hasOwnProperty(nowm)) {
		if (nowd in vbrestrmonthscombojn[nowm]) {
			vbcombowdays = vbrestrmonthscombojn[nowm][nowd];
		}
	}
	if (jQuery.inArray((nowm + 1), vbrestrmonths) != -1) {
		minlos = parseInt(vbrestrminlos[nowm]);
	}
	nowcheckindate.setDate(nowcheckindate.getDate() + minlos);
	jQuery('#checkindatemod".$randid."').vboDatesRangePicker('checkout', 'minDate', nowcheckindate);
	jQuery('#checkindatemod".$randid."').vboDatesRangePicker('checkout', 'minStayNights', minlos);
	if (maxlosrange > 0) {
		var diffmaxminlos = maxlosrange - minlos;
		var maxcheckoutdate = new Date(nowcheckindate.getTime());
		maxcheckoutdate.setDate(maxcheckoutdate.getDate() + diffmaxminlos);
		jQuery('#checkindatemod".$randid."').vboDatesRangePicker('checkout', 'maxDate', maxcheckoutdate);
	}
	if (nowm in vbrestrmaxlos) {
		var diffmaxminlos = parseInt(vbrestrmaxlos[nowm]) - minlos;
		var maxcheckoutdate = new Date(nowcheckindate.getTime());
		maxcheckoutdate.setDate(maxcheckoutdate.getDate() + diffmaxminlos);
		jQuery('#checkindatemod".$randid."').vboDatesRangePicker('checkout', 'maxDate', maxcheckoutdate);
	}
	if (!vbFullObject".$randid."(vbcombowdays)) {
		var is_checkout_disabled = false;
		if (typeof selectedDate !== 'undefined' && typeof jQuery('#checkindatemod".$randid."').vboDatesRangePicker('drpoption', 'beforeShowDay.checkout') === 'function') {
			// let the datepicker validate if the min date to set for check-out is disabled due to CTD rules
			is_checkout_disabled = !jQuery('#checkindatemod".$randid."').vboDatesRangePicker('drpoption', 'beforeShowDay.checkout')(nowcheckindate)[0];
		}
		if (!is_checkout_disabled) {
			jQuery('#checkindatemod".$randid."').vboDatesRangePicker('checkout', 'setCheckoutDate', nowcheckindate);
		} else {
			setTimeout(() => {
				// make sure the minimum date just set for the checkout has not populated a CTD date that we do not want
				var current_out_dt = jQuery('#checkindatemod".$randid."').vboDatesRangePicker('getCheckoutDate');
				if (current_out_dt && current_out_dt.getTime() === nowcheckindate.getTime()) {
					jQuery('#checkindatemod".$randid."').vboDatesRangePicker('checkout', 'setCheckoutDate', null);
				}
			}, 100);
		}
	} else {
		vbRefreshCheckout".$randid."(nowcheckin);
	}
}";

	if ($wdaysrestrictions || $wdaysrestrictionsrange) {
		$resdecl .= "
var vbrestrwdays = " . json_encode((object) $wdaysrestrictions) . ";
var vbrestrwdaystwo = " . json_encode((object) $wdaystworestrictions) . ";
function vbIsDayDisabledmod".$randid."(date) {
	if (!vbIsDayOpenmod".$randid."(date) || !vboValidateCtamod".$randid."(date)) {
		return [false];
	}
	var m = date.getMonth(), wd = date.getDay();
	if (vbFullObject".$randid."(vbrestrwdaysrangejn)) {
		for (var rk in vbrestrwdaysrangejn) {
			if (vbrestrwdaysrangejn.hasOwnProperty(rk)) {
				var wdrangeinit = vbGetDateObject".$randid."(vbrestrwdaysrangejn[rk][0]);
				if (date >= wdrangeinit) {
					var wdrangeend = vbGetDateObject".$randid."(vbrestrwdaysrangejn[rk][1]);
					if (date <= wdrangeend) {
						if (wd != vbrestrwdaysrangejn[rk][2]) {
							if (vbrestrwdaysrangejn[rk][4] == -1 || wd != vbrestrwdaysrangejn[rk][4]) {
								return [false];
							}
						}
					}
				}
			}
		}
	}
	if (vbFullObject".$randid."(vbrestrwdays)) {
		if (jQuery.inArray((m+1), vbrestrmonthswdays) == -1) {
			return [true];
		}
		if (wd == vbrestrwdays[m]) {
			return [true];
		}
		if (vbFullObject".$randid."(vbrestrwdaystwo)) {
			if (wd == vbrestrwdaystwo[m]) {
				return [true];
			}
		}
		return [false];
	}
	return [true];
}
function vbIsDayDisabledCheckoutmod".$randid."(date) {
	if (!vbIsDayOpenmod".$randid."(date) || !vboValidateCtdmod".$randid."(date)) {
		return [false];
	}
	var m = date.getMonth(), wd = date.getDay();
	if (vbFullObject".$randid."(vbcombowdays)) {
		if (jQuery.inArray(wd, vbcombowdays) != -1) {
			return [true];
		} else {
			return [false];
		}
	}
	if (vbFullObject".$randid."(vbrestrwdaysrangejn)) {
		for (var rk in vbrestrwdaysrangejn) {
			if (vbrestrwdaysrangejn.hasOwnProperty(rk)) {
				var wdrangeinit = vbGetDateObject".$randid."(vbrestrwdaysrangejn[rk][0]);
				if (date >= wdrangeinit) {
					var wdrangeend = vbGetDateObject".$randid."(vbrestrwdaysrangejn[rk][1]);
					if (date <= wdrangeend) {
						if (wd != vbrestrwdaysrangejn[rk][2] && vbrestrwdaysrangejn[rk][3] == 1) {
							return [false];
						}
					}
				}
			}
		}
	}
	if (vbFullObject".$randid."(vbrestrwdays)) {
		if (jQuery.inArray((m+1), vbrestrmonthswdays) == -1 || jQuery.inArray((m+1), vbrestrmultiplyminlos) != -1) {
			return [true];
		}
		if (wd == vbrestrwdays[m]) {
			return [true];
		}
		return [false];
	}
	return [true];
}";
	}
	$document->addScriptDeclaration($resdecl);
}

// global closing dates
$closing_dates = VikBookingChannelRatesHelper::parseJsClosingDates();
$sdecl = "
var vbclosingdates = " . json_encode($closing_dates) . ";
function vbCheckClosingDatesInmod".$randid."(date) {
	if (!vbIsDayOpenmod".$randid."(date) || !vboValidateCtamod".$randid."(date)) {
		return [false];
	}
	return [true];
}
function vbCheckClosingDatesOutmod".$randid."(date) {
	if (!vbIsDayOpenmod".$randid."(date) || !vboValidateCtdmod".$randid."(date)) {
		return [false];
	}
	return [true];
}
function vbIsDayOpenmod".$randid."(date) {
	if (vbFullObject".$randid."(vbclosingdates)) {
		for (var cd in vbclosingdates) {
			if (vbclosingdates.hasOwnProperty(cd)) {
				var cdfrom = vbGetDateObject".$randid."(vbclosingdates[cd][0]);
				var cdto = vbGetDateObject".$randid."(vbclosingdates[cd][1]);
				if (date >= cdfrom && date <= cdto) {
					return false;
				}
			}
		}
	}
	return true;
}
function vboValidateCtamod".$randid."(date) {
	var m = date.getMonth(), wd = date.getDay();
	if (vbFullObject".$randid."(vbrestrctarange)) {
		for (var rk in vbrestrctarange) {
			if (vbrestrctarange.hasOwnProperty(rk)) {
				var wdrangeinit = vbGetDateObject".$randid."(vbrestrctarange[rk][0]);
				if (date >= wdrangeinit) {
					var wdrangeend = vbGetDateObject".$randid."(vbrestrctarange[rk][1]);
					if (date <= wdrangeend) {
						if (jQuery.inArray('-'+wd+'-', vbrestrctarange[rk][2]) >= 0) {
							return false;
						}
					}
				}
			}
		}
	}
	if (vbFullObject".$randid."(vbrestrcta)) {
		if (vbrestrcta.hasOwnProperty(m) && jQuery.inArray('-'+wd+'-', vbrestrcta[m]) >= 0) {
			return false;
		}
	}
	return true;
}
function vboValidateCtdmod".$randid."(date) {
	var m = date.getMonth(), wd = date.getDay();
	if (vbFullObject".$randid."(vbrestrctdrange)) {
		for (var rk in vbrestrctdrange) {
			if (vbrestrctdrange.hasOwnProperty(rk)) {
				var wdrangeinit = vbGetDateObject".$randid."(vbrestrctdrange[rk][0]);
				if (date >= wdrangeinit) {
					var wdrangeend = vbGetDateObject".$randid."(vbrestrctdrange[rk][1]);
					if (date <= wdrangeend) {
						if (jQuery.inArray('-'+wd+'-', vbrestrctdrange[rk][2]) >= 0) {
							return false;
						}
					}
				}
			}
		}
	}
	if (vbFullObject".$randid."(vbrestrctd)) {
		if (vbrestrctd.hasOwnProperty(m) && jQuery.inArray('-'+wd+'-', vbrestrctd[m]) >= 0) {
			return false;
		}
	}
	return true;
}
function vbSetGlobalMinCheckoutDatemod".$randid."() {
	var minlos = ".VikBooking::getDefaultNightsCalendar().";
	var nowcheckin = jQuery('#checkindatemod".$randid."').vboDatesRangePicker('getCheckinDate');
	var nowcheckindate = new Date(nowcheckin.getTime());
	nowcheckindate.setDate(nowcheckindate.getDate() + minlos);
	jQuery('#checkindatemod".$randid."').vboDatesRangePicker('checkout', 'minDate', nowcheckindate);
	jQuery('#checkindatemod".$randid."').vboDatesRangePicker('checkout', 'minStayNights', minlos);
	jQuery('#checkindatemod".$randid."').vboDatesRangePicker('checkout', 'setCheckoutDate', nowcheckindate);
}
function vbSetDatepickerPos".$randid."(input, inst) {
	if (!jQuery(window).scrollTop()) {
		return;
	}
	var rect = input.getBoundingClientRect();
	setTimeout(() => {
		var set_top = rect.top + input.offsetHeight + jQuery('body').scrollTop();
		var cal_height = inst.dpDiv.outerHeight();
		if ((set_top + cal_height) > jQuery(window).height()) {
			set_top = rect.top - cal_height;
		}
		inst.dpDiv.css({ top: set_top });
	}, 50);
}

jQuery(function() {
	// reset regional
	jQuery.datepicker.setDefaults(jQuery.datepicker.regional['']);

	// start DRP
	jQuery('#checkindatemod".$randid."').vboDatesRangePicker({
		checkout: '#checkoutdatemod".$randid."',
		dateFormat: '{$juidf}',
		showOn: 'focus',
		numberOfMonths: " . ($is_mobile ? '1' : '2') . ",
		minDate: '" . VikBooking::getMinDaysAdvance() . "d',
		maxDate: '" . VikBooking::getMaxDateFuture() . "',
		beforeShowDay: {
			checkin: " . ($wdaysrestrictions || $wdaysrestrictionsrange ? "vbIsDayDisabledmod{$randid}" : "vbCheckClosingDatesInmod{$randid}") . ",
			checkout: " . ($wdaysrestrictions || $wdaysrestrictionsrange ? "vbIsDayDisabledCheckoutmod{$randid}" : "vbCheckClosingDatesOutmod{$randid}") . ",
		},
		onSelect: {
			checkin: (selectedDate) => {
				" . ($totrestrictions ? "vbSetMinCheckoutDatemod".$randid."(selectedDate);" : "vbSetGlobalMinCheckoutDatemod".$randid."();") . "
				vbCalcNightsMod".$randid."();
			},
			checkout: (selectedDate) => {
				vbCalcNightsMod".$randid."();
				setTimeout(() => {
					// hide DRP with a delay to see the newly calculated rates
					jQuery('#checkindatemod".$randid."').vboDatesRangePicker('hide');
					// trigger blur event to allow again a click to focus the input field
					jQuery('#checkindatemod".$randid."').trigger('blur');
				}, 250);
			},
		},
		beforeShow: (input, inst) => {
			vbSetDatepickerPos".$randid."(input, inst);
		},
		labels: {
			checkin: Joomla.JText._('VBPICKUPROOM'),
			checkout: Joomla.JText._('VBRETURNROOM'),
			minStayNights: (nights) => {
				return (Joomla.JText._('VBO_MIN_STAY_NIGHTS') + '').replace('%d', nights);
			},
		},
		bottomCommands: {
			clear: Joomla.JText._('VBO_CLEAR_DATES'),
			close: Joomla.JText._('VBO_CLOSE'),
			onClear: () => {
				vbCalcNightsMod".$randid."();
			},
		},
	});

	// set proper regional
	jQuery('#checkindatemod".$randid."').datepicker('option', jQuery.datepicker.regional['vikbookingmod']);

	// register additional triggers (classic layout)
	jQuery('.vb-cal-img, .vbo-caltrigger').click(function() {
		let dp = jQuery(this).prev('input');
		if (!dp.length) {
			return;
		}
		if (dp.hasClass('hasDatepicker')) {
			dp.focus();
		} else if (dp.attr('id') == 'checkoutdatemod".$randid."') {
			jQuery('#checkindatemod".$randid."').focus();
		}
	});
});";
$document->addScriptDeclaration($sdecl);
?>
				<div class="vbomodchrates-dates-wrapper">
					<div class="vbomodchrates-checkindiv">
						<label for="checkindatemod<?php echo $randid; ?>"><?php echo JText::_('VBMCHECKIN'); ?></label>
						<div class="input-group">
							<input type="text" name="checkindate" id="checkindatemod<?php echo $randid; ?>" size="10" readonly />
							<?php VikBookingIcons::e('calendar', 'vbo-caltrigger'); ?>
							<input type="hidden" name="checkinh" value="<?php echo $hcheckin; ?>"/>
							<input type="hidden" name="checkinm" value="<?php echo $mcheckin; ?>"/>
						</div>
					</div>
					<div class="vbomodchrates-checkoutdiv">
						<label for="checkoutdatemod<?php echo $randid; ?>"><?php echo JText::_('VBMCHECKOUT'); ?></label>
						<div class="input-group">
							<input type="text" name="checkoutdate" id="checkoutdatemod<?php echo $randid; ?>" size="10" readonly />
							<?php VikBookingIcons::e('calendar', 'vbo-caltrigger'); ?>
							<input type="hidden" name="checkouth" value="<?php echo $hcheckout; ?>"/>
							<input type="hidden" name="checkoutm" value="<?php echo $mcheckout; ?>"/>
						</div>
					</div>
				</div>
<?php
//
//rooms, adults, children
$showchildren = VikBooking::showChildrenFront();
$guests_label = VBOFactory::getConfig()->get('guests_label', 'adults');
$use_guests_label = 'VBMFORMADULTS';
if (!$showchildren && !strcasecmp($guests_label, 'guests')) {
	$use_guests_label = 'VBMHORSGUESTS';
}
//max number of rooms
$maxsearchnumrooms = VikBookingChannelRatesHelper::getSearchNumRooms();
if (intval($maxsearchnumrooms) > 1) {
	$roomsel = "<span class=\"vbhsrnselsp\"><select name=\"roomsnum\" id=\"vbmodformroomsn".$randid."\" onchange=\"vbSetRoomsAdultsMod".$randid."(this.value);\">\n";
	for ($r = 1; $r <= intval($maxsearchnumrooms); $r++) {
		$roomsel .= "<option value=\"".$r."\">".$r."</option>\n";
	}
	$roomsel .= "</select></span>\n";
} else {
	$roomsel = "<input type=\"hidden\" name=\"roomsnum\" value=\"1\" id=\"vbmodformroomsn".$randid."\">\n";
}
//
//max number of adults per room
$globnumadults = VikBookingChannelRatesHelper::getSearchNumAdults();
$adultsparts = explode('-', $globnumadults);
$adultsel = "<select class=\"vbomodchrates-adults-sel" . $randid . "\" name=\"adults[]\" id=\"vbo-mhs-adults-" . $randid . "\" onchange=\"vbUpdateAdultsTrig" . $randid . "();\">";
for ($a = $adultsparts[0]; $a <= $adultsparts[1]; $a++) {
	$adultsel .= "<option value=\"".$a."\"".(($def_adults > 1 && $a == $def_adults) || (intval($adultsparts[0]) < 1 && $a == 1) ? " selected=\"selected\"" : "").">".$a."</option>";
}
$adultsel .= "</select>";
//
//max number of children per room
$globnumchildren = VikBookingChannelRatesHelper::getSearchNumChildren();
$childrenparts = explode('-', $globnumchildren);
$childrensel = "<select class=\"vbomodchrates-children-sel" . $randid . "\" name=\"children[]\" id=\"vbo-mhs-children-" . $randid . "\" onchange=\"vbUpdateChildrenTrig" . $randid . "();\">";
for ($c = $childrenparts[0]; $c <= $childrenparts[1]; $c++) {
	$childrensel .= "<option value=\"".$c."\">".$c."</option>";
}
$childrensel .= "</select>";
//
?>
				<div class="vbomodchrates-search-container">

					<div class="vbomodchrates-searchinfo" id="vbomodchrates-searchinfo<?php echo $randid; ?>">
						<div class="vbomodchrates-totnights">
							<span class="vbomodchrates-searchinfo-lbl"><?php echo JText::_('VBMJSTOTNIGHTS'); ?></span>
							<span class="vbomodchrates-searchinfo-val" id="vbjstotnightsmod<?php echo $randid; ?>"><?php echo $default_dates[2]; ?></span>
						</div>
					<?php
					if (intval($maxsearchnumrooms) > 1) {
						?>
						<div class="vbomodchrates-totrooms">
							<span class="vbomodchrates-searchinfo-lbl"><?php echo JText::_('VBMFORMROOMSN'); ?></span>
							<span class="vbomodchrates-searchinfo-val" id="vbjstotroomsmod<?php echo $randid; ?>">1</span>
						</div>
						<?php
					}
					?>
						<div class="vbomodchrates-totadults">
							<span class="vbomodchrates-searchinfo-lbl"><?php echo JText::_($use_guests_label); ?></span>
							<span class="vbomodchrates-searchinfo-val" id="vbjstotadultsmod<?php echo $randid; ?>"><?php echo $def_adults; ?></span>
						</div>
					<?php
					if ($showchildren) {
						?>
						<div class="vbomodchrates-totchildren">
							<span class="vbomodchrates-searchinfo-lbl"><?php echo JText::_('VBFORMCHILDREN'); ?></span>
							<span class="vbomodchrates-searchinfo-val" id="vbjstotchildrenmod<?php echo $randid; ?>">0</span>
						</div>
						<?php
					}
					?>
					</div>

					<div class="vbomodchrates-searchtoggle" id="vbomodchrates-searchtoggle<?php echo $randid; ?>">
						<?php VikBookingIcons::e('chevron-down'); ?>
					</div>

					<div class="vbomodchrates-rac" id="vbomodchrates-rac<?php echo $randid; ?>" style="display: none;">
					<?php
					if (intval($maxsearchnumrooms) > 1) {
						?>
						<div class="vbomodchrates-roomsel">
							<label for="vbmodformroomsn<?php echo $randid; ?>"><?php echo JText::_('VBMFORMROOMSN'); ?></label>
							<?php echo $roomsel; ?>
						</div>
						<?php
					} else {
						//hidden input
						echo $roomsel;
					}
					?>
						<div class="vbomodchrates-roomdentr">
							<div class="vbomodchrates-roomdentrfirst">
								<?php
								if (intval($maxsearchnumrooms) > 1): ?>
								<span class="horsrnum"><?php VikBookingIcons::e('bed'); ?> #1</span>
								<?php endif; ?>
								<div class="horsanumdiv">
									<label class="horsanumlb" for="vbo-mhs-adults-<?php echo $randid; ?>"><?php echo JText::_($use_guests_label); ?></label>
									<span class="horsanumsel"><?php echo $adultsel; ?></span>
								</div>
								<?php
								if ($showchildren):
								?>
								<div class="horscnumdiv">
									<label class="horscnumlb" for="vbo-mhs-children-<?php echo $randid; ?>"><?php echo JText::_('VBFORMCHILDREN'); ?></label>
									<span class="horscnumsel"><?php echo $childrensel; ?></span>
								</div>
								<?php
								endif;
								?>
							</div>
							<div class="vbmoreroomscontmod" id="vbmoreroomscontmod<?php echo $randid; ?>"></div>
						</div>
						<div class="vbomodchrates-refresh-cont">
							<span onclick="vbChRatesRefresh<?php echo $randid; ?>();"><?php VikBookingIcons::e('refresh'); ?> <?php echo JText::_('VBOMODCMRREFRESHRATES'); ?></span>
						</div>
					</div>

				</div>

				<div class="vbomodchrates-bookdiv">
					<input type="submit" class="btn vbo-pref-color-btn" name="search" value="<?php echo JText::_('SEARCHD'); ?>"/>
				</div>
			</form>
		<?php
		//display the website's best rate and the OTAs rates (if any)
		$skipcosts = !count($best_room_rate) && is_array($website_rates) && isset($website_rates['e4j.error']);
		if (count($best_room_rate) || $skipcosts) {
			if ($skipcosts) {
				//there is no availability for the default/current dates, but we need to print the HTML structure anyway, or Ajax functions will not show the prices.
				$final_cost = 0;
			} else {
				$sum_taxes  = isset($best_room_rate['taxes']) ? $best_room_rate['taxes'] : 0;
				$final_cost = $show_tax ? ($best_room_rate['cost'] + $sum_taxes) : $best_room_rate['cost'];
			}
			?>
			<div class="vbomodchrates-rates-wrap">
			<?php
			if ($skipcosts) {
				?>
				<p class="vbomodchrates-noav-par"><?php echo str_replace('e4j.error', '', $website_rates['e4j.error']); ?></p>
				<?php
			}
			?>
				<div class="vbomodchrates-rates-inner"<?php echo $skipcosts ? ' style="display: none;"' : ''; ?>>
					<div class="vbomodchrates-rates-website">
						<div class="vbomodchrates-rate-website" data-chidentifier="<?php echo $randid.'-website'; ?>">
							<span class="vbomodchrates-rate-lbl">
								<span><?php echo JText::_('VBOMODCMRWEBSITERATE'); ?></span>
							</span>
							<span class="vbomodchrates-rate-val">
								<?php echo VikBooking::formatCurrencyNumber(VikBookingChannelRatesHelper::numberFormat($final_cost), $currency_symb, ['<span class="vbo_currency">%s</span>', '<span class="vbo_price">%s</span>']); ?>
							</span>
						</div>
					</div>
				<?php
				if (count($channels_map)) {
					?>
					<div class="vbomodchrates-rates-otas">
					<?php
					foreach ($channels_map as $ch) {
						$ch_final_cost = $final_cost;

						/**
						 * Check if an alteration for this channel has been specified.
						 * 
						 * @since 	1.15.0 (J) - 1.5.0 (WP)
						 */
						$use_otas_rmod = $otas_rmod;
						$use_otas_rmodpcent = $otas_rmodpcent;
						$use_otas_rmodval = $otas_rmodval;
						if (is_array($otas_rmod_channels) && isset($otas_rmod_channels[$ch['id']])) {
							$use_rates_val = $otas_rmod_channels[$ch['id']];
							$use_otas_rmod = substr($use_rates_val, 0, 1); // + or - (charge or discount)
							$use_otas_rmodpcent = substr($use_rates_val, -1) == '%' ? 1 : 0;
							$use_otas_rmodval = (float)($use_otas_rmodpcent > 0 ? substr($use_rates_val, 1, (strlen($use_rates_val) - 2)) : substr($use_rates_val, 1, (strlen($use_rates_val) - 1)));
						}

						if (!$skipcosts && !empty($use_otas_rmod)) {
							if ($use_otas_rmod == '+') {
								// charge
								if ($use_otas_rmodpcent > 0) {
									// percentage
									$ch_final_cost = $ch_final_cost * (100 + $use_otas_rmodval) / 100;
								} else {
									// absolute
									$ch_final_cost += $use_otas_rmodval * (count($best_room_rate) && !empty($best_room_rate['days']) ? $best_room_rate['days'] : 1);
								}
							} else {
								// discount (must be a fool)
								if ($use_otas_rmodpcent > 0) {
									// percentage
									$ch_final_cost = $ch_final_cost / (($use_otas_rmodval / 100) + 1);
								} else {
									// absolute
									$ch_final_cost -= $use_otas_rmodval * (count($best_room_rate) && !empty($best_room_rate['days']) ? $best_room_rate['days'] : 1);
								}
							}
						}
						?>
						<div class="vbomodchrates-rate-ota" data-chidentifier="<?php echo $randid.'-'.$ch['id']; ?>">
							<span class="vbomodchrates-rate-lbl">
							<?php
							if (isset($ch['logo']) && !empty($ch['logo'])) {
								?>
								<img src="<?php echo $ch['logo']; ?>" class="vbomodchrates-rate-ota-logo" />
								<?php
							} else {
								?>
								<span><?php echo $ch['name']; ?></span>
								<?php
							}
							?>
							</span>
							<span class="vbomodchrates-rate-val">
								<?php echo VikBooking::formatCurrencyNumber(VikBookingChannelRatesHelper::numberFormat($ch_final_cost), $currency_symb, ['<span class="vbo_currency">%s</span>', '<span class="vbo_price">%s</span>']); ?>
							</span>
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
			<?php
		}
		//contact buttons
		$contact_email = $params->get('cemail', '');
		$contact_messenger = $params->get('cmessenger', '');
		$contact_whatsapp = $params->get('cwhatsapp', '');
		$contact_phone = $params->get('cphone', '');
		if (!empty($contact_email) || !empty($contact_messenger) || !empty($contact_whatsapp) || !empty($contact_phone)) {
			?>
			<div class="vbomodchrates-contacts-wrap">
			<?php
			if (!empty($contact_email)) {
				/**
				 * @wponly  we cannot echo the returned mail tag or the style declaration will be echoed inside the link attribute
				 */
				$mailtag = VikBookingChannelRatesHelper::safeMailTag($contact_email, true, true);
				?>
				<div class="vbomodchrates-contact-btn vbomodchrates-contact-btn-email">
					<a href="mailto:<?php echo $mailtag; ?>"><?php VikBookingIcons::e('envelope-o'); ?></a>
				</div>
				<?php
			}
			if (!empty($contact_messenger)) {
				?>
				<div class="vbomodchrates-contact-btn vbomodchrates-contact-btn-messenger">
					<a href="https://m.me/<?php echo $contact_messenger; ?>" target="_blank">
						<?php VikBookingIcons::e('fab fa-facebook'); ?>
					</a>
				</div>
				<?php
			}
			if (!empty($contact_whatsapp)) {
				?>
				<div class="vbomodchrates-contact-btn vbomodchrates-contact-btn-whatsapp">
					<a href="https://api.whatsapp.com/send?phone=<?php echo $contact_whatsapp; ?>" target="_blank">
						<?php VikBookingIcons::e('fab fa-whatsapp'); ?>
					</a>
				</div>
				<?php
			}
			if (!empty($contact_phone)) {
				?>
				<div class="vbomodchrates-contact-btn vbomodchrates-contact-btn-phone">
					<a href="tel:<?php echo $contact_phone; ?>"><?php VikBookingIcons::e('phone'); ?></a>
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
</div>

<script type="text/javascript">
jQuery(function() {
	jQuery("#vbomodchrates-searchinfo<?php echo $randid; ?> div span").click(function() {
		jQuery("#vbomodchrates-searchtoggle<?php echo $randid; ?>").trigger("click");
	});
	jQuery("#vbomodchrates-searchtoggle<?php echo $randid; ?>").click(function() {
		var elem = jQuery(this);
		if (elem.find("i").hasClass("fa-chevron-down")) {
			jQuery("#vbomodchrates-rac<?php echo $randid; ?>").slideDown(400, function() {
				elem.find("i").addClass("fa-chevron-up").removeClass("fa-chevron-down");
			});
		} else {
			jQuery("#vbomodchrates-rac<?php echo $randid; ?>").slideUp(400, function() {
				elem.find("i").addClass("fa-chevron-down").removeClass("fa-chevron-up");
			});
		}
	});
	jQuery("#vbomodchrates-close-cont<?php echo $randid; ?>").click(function() {
		var wrapper = jQuery(this);
		jQuery("#vbomodchrates-inner-cont<?php echo $randid; ?>").fadeOut();
		wrapper.hide();
		wrapper.closest('.vbomodchrates-container').removeClass('vbomodchrates-container-active');
		jQuery("#vbomodchrates-open-cont<?php echo $randid; ?>").show();
		var nd = new Date();
		nd.setTime(nd.getTime() + (7*24*60*60*1000));
		document.cookie = "vboVcmMcrh=1; expires=" + nd.toUTCString() + "; path=/; SameSite=Lax";
	});
	jQuery("#vbomodchrates-open-cont<?php echo $randid; ?>").click(function() {
		jQuery("#vbomodchrates-inner-cont<?php echo $randid; ?>").fadeIn();
		jQuery("#vbomodchrates-open-cont<?php echo $randid; ?>").hide();
		jQuery("#vbomodchrates-close-cont<?php echo $randid; ?>").show();
		jQuery(this).closest('.vbomodchrates-container').addClass('vbomodchrates-container-active');
		var nd = new Date();
		nd.setTime(nd.getTime() + (7*24*60*60*1000));
		document.cookie = "vboVcmMcrh=0; expires=" + nd.toUTCString() + "; path=/; SameSite=Lax";
	});
	jQuery("#vbomodchrates-introtxt<?php echo $randid; ?>").click(function() {
		if (jQuery("#vbomodchrates-open-cont<?php echo $randid; ?>").is(":visible")) {
			jQuery("#vbomodchrates-open-cont<?php echo $randid; ?>").trigger('click');
		}
	});
});
function vbAddElementMod<?php echo $randid; ?>() {
	var ni = document.getElementById('vbmoreroomscontmod<?php echo $randid; ?>');
	var numi = document.getElementById('vbroomhelpermod<?php echo $randid; ?>');
	var num = (document.getElementById('vbroomhelpermod<?php echo $randid; ?>').value -1)+ 2;
	numi.value = num;
	var newdiv = document.createElement('div');
	var divIdName = 'vb'+num+'racont';
	newdiv.setAttribute('id',divIdName);
	newdiv.innerHTML = '<div class=\'vbomodchrates-roomdentr\'><span class=\'horsrnum\'><i class=\'<?php echo VikBookingIcons::i('bed'); ?>\'></i> #'+num+'</span><div class=\'horsanumdiv\'><span class=\'horsanumsel\'><?php echo addslashes(str_replace('"', "'", preg_replace("/\sid=\"[a-z0-9\-]+\"/i", '', $adultsel))); ?></span><?php echo ($showchildren ? "<div class=\'horscnumdiv\'><span class=\'horscnumsel\'>".addslashes(str_replace('"', "'", preg_replace("/\sid=\"[a-z0-9\-]+\"/i", '', $childrensel)))."</span></div" : ""); ?></div>';
	ni.appendChild(newdiv);
}
function vbSetRoomsAdultsMod<?php echo $randid; ?>(totrooms) {
	var actrooms = parseInt(document.getElementById('vbroomhelpermod<?php echo $randid; ?>').value);
	var torooms = parseInt(totrooms);
	var difrooms;
	if (torooms > actrooms) {
		difrooms = torooms - actrooms;
		for(var ir=1; ir<=difrooms; ir++) {
			vbAddElementMod<?php echo $randid; ?>();
		}
	}
	if (torooms < actrooms) {
		for(var ir=actrooms; ir>torooms; ir--) {
			if (ir > 1) {
				var rmra = document.getElementById('vb' + ir + 'racont');
				rmra.parentNode.removeChild(rmra);
			}
		}
		document.getElementById('vbroomhelpermod<?php echo $randid; ?>').value = torooms;
	}
	document.getElementById('vbjstotroomsmod<?php echo $randid; ?>').innerText = torooms;
	vbUpdateAdultsTrig<?php echo $randid; ?>();
	vbUpdateChildrenTrig<?php echo $randid; ?>();
}
function vbUpdateAdultsTrig<?php echo $randid; ?>() {
	var adu_count = 0;
	jQuery(".vbomodchrates-adults-sel<?php echo $randid; ?>").each(function(k, v) {
		adu_count += parseInt(jQuery(this).val());
	});
	document.getElementById('vbjstotadultsmod<?php echo $randid; ?>').innerText = adu_count;
}
function vbUpdateChildrenTrig<?php echo $randid; ?>() {
	var child_count = 0;
	jQuery(".vbomodchrates-children-sel<?php echo $randid; ?>").each(function(k, v) {
		child_count += parseInt(jQuery(this).val());
	});
	document.getElementById('vbjstotchildrenmod<?php echo $randid; ?>').innerText = child_count;
}
function vbCalcNightsMod<?php echo $randid; ?>() {
	var vbcheckin = document.getElementById('checkindatemod<?php echo $randid; ?>').value;
	var vbcheckout = document.getElementById('checkoutdatemod<?php echo $randid; ?>').value;
	if (vbcheckin.length > 0 && vbcheckout.length > 0) {
		var vbcheckinp = vbcheckin.split("/");
		var vbcheckoutp = vbcheckout.split("/");
	<?php
	if ($vbdateformat == "%d/%m/%Y") {
		?>
		var vbinmonth = parseInt(vbcheckinp[1]);
		vbinmonth = vbinmonth - 1;
		var vbinday = parseInt(vbcheckinp[0], 10);
		var vbcheckind = new Date(vbcheckinp[2], vbinmonth, vbinday);
		var vboutmonth = parseInt(vbcheckoutp[1]);
		vboutmonth = vboutmonth - 1;
		var vboutday = parseInt(vbcheckoutp[0], 10);
		var vbcheckoutd = new Date(vbcheckoutp[2], vboutmonth, vboutday);
		<?php
	} elseif ($vbdateformat == "%m/%d/%Y") {
		?>
		var vbinmonth = parseInt(vbcheckinp[0]);
		vbinmonth = vbinmonth - 1;
		var vbinday = parseInt(vbcheckinp[1], 10);
		var vbcheckind = new Date(vbcheckinp[2], vbinmonth, vbinday);
		var vboutmonth = parseInt(vbcheckoutp[0]);
		vboutmonth = vboutmonth - 1;
		var vboutday = parseInt(vbcheckoutp[1], 10);
		var vbcheckoutd = new Date(vbcheckoutp[2], vboutmonth, vboutday);
		<?php
	} else {
		?>
		var vbinmonth = parseInt(vbcheckinp[1]);
		vbinmonth = vbinmonth - 1;
		var vbinday = parseInt(vbcheckinp[2], 10);
		var vbcheckind = new Date(vbcheckinp[0], vbinmonth, vbinday);
		var vboutmonth = parseInt(vbcheckoutp[1]);
		vboutmonth = vboutmonth - 1;
		var vboutday = parseInt(vbcheckoutp[2], 10);
		var vbcheckoutd = new Date(vbcheckoutp[0], vboutmonth, vboutday);
		<?php
	}
	?>
		var vbdivider = 1000 * 60 * 60 * 24;
		var vbints = vbcheckind.getTime();
		var vboutts = vbcheckoutd.getTime();
		if (vboutts > vbints) {
			//var vbnights = Math.ceil((vboutts - vbints) / (vbdivider));
			var utc1 = Date.UTC(vbcheckind.getFullYear(), vbcheckind.getMonth(), vbcheckind.getDate());
			var utc2 = Date.UTC(vbcheckoutd.getFullYear(), vbcheckoutd.getMonth(), vbcheckoutd.getDate());
			var vbnights = Math.ceil((utc2 - utc1) / vbdivider);
			if (vbnights > 0) {
				document.getElementById('vbjstotnightsmod<?php echo $randid; ?>').innerText = vbnights;
			} else {
				document.getElementById('vbjstotnightsmod<?php echo $randid; ?>').innerHTML = '';
			}
		} else {
			document.getElementById('vbjstotnightsmod<?php echo $randid; ?>').innerHTML = '';
		}
		//we can trigger the Ajax request to calculate the best rates
		vboLoadRates<?php echo $randid; ?>(vbcheckin, vbcheckout, vbnights);
	} else {
		document.getElementById('vbjstotnightsmod<?php echo $randid; ?>').innerHTML = '';
	}
}
function vbChRatesRefresh<?php echo $randid; ?>() {
	var checkin = document.getElementById('checkindatemod<?php echo $randid; ?>').value;
	var checkout = document.getElementById('checkoutdatemod<?php echo $randid; ?>').value;
	var nights = parseInt(document.getElementById('vbjstotnightsmod<?php echo $randid; ?>').innerText);
	vboLoadRates<?php echo $randid; ?>(checkin, checkout, nights);
}
function vboLoadRates<?php echo $randid; ?>(checkin, checkout, nights) {
	var num_rooms = document.getElementById('vbmodformroomsn<?php echo $randid; ?>').value;
	var adults = new Array;
	var children = new Array;
	jQuery('select.vbomodchrates-adults-sel<?php echo $randid; ?>').each(function(k, v) {
		var selval = parseInt(jQuery(this).val());
		selval = isNaN(selval) || selval < 0 ? 0 : selval;
		adults.push(selval);
	});
	if (jQuery('select.vbomodchrates-children-sel<?php echo $randid; ?>').length) {
		jQuery('select.vbomodchrates-children-sel<?php echo $randid; ?>').each(function(k, v) {
			var selval = parseInt(jQuery(this).val());
			selval = isNaN(selval) || selval < 0 ? 0 : selval;
			children.push(selval);
		});
	}

	// custom params
	var cust_rmodpcent = '<?php echo $params->get('cust_rmodpcent', 'pcent'); ?>';
	var cust_rmodval = parseFloat('<?php echo $params->get('cust_rmodval', 0); ?>');
	var channels_sel = JSON.parse('<?php echo json_encode($channels_sel); ?>');
	//
	
	//execute the request
	var jqxhr = jQuery.ajax({
		type: "POST",
		/**
		 * @wponly 	we need to route the AJAX endpoint URL for front-end requests
		 */
		url: "<?php echo VikBooking::ajaxUrl('index.php?option=com_vikbooking&task=tac_av_l&Itemid=' . $params->get('itemid', 0), false); ?>",
		data: {
			vbomodule: "1",
			tmpl: "component",
			show_tax: "<?php echo (int)$show_tax; ?>",
			def_rplan: "<?php echo $def_rplan; ?>",
			channels_sel: [<?php echo implode(',', $channels_sel); ?>],
			checkin: checkin,
			checkout: checkout,
			nights: nights,
			num_rooms: num_rooms,
			adults: adults,
			children: children
		}
	}).done(function(res) {
		try {
			var obj = JSON.parse(res);
		} catch(e) {
			var obj = res;
		}
		if (!obj.hasOwnProperty('website')) {
			//error: hide all prices
			console.log(res);
			jQuery(".vbomodchrates-rate-website[data-chidentifier='<?php echo $randid.'-website'; ?>']").find(".vbomodchrates-rate-val").find(".vbo_price").text("0");
			var chprices = jQuery(".vbomodchrates-rate-ota");
			if (chprices.length) {
				chprices.each(function(k, v) {
					jQuery(this).find(".vbomodchrates-rate-val").find(".vbo_price").text("0");
				});
			}
		} else {
			//change the website rate
			jQuery(".vbomodchrates-rate-website[data-chidentifier='<?php echo $randid.'-website'; ?>']").find(".vbomodchrates-rate-val").find(".vbo_price").text(obj['website']);
			//change the channels rates
			if (obj.hasOwnProperty('channels')) {
				for (var chid in obj['channels']) {
					var chel = jQuery(".vbomodchrates-rate-ota[data-chidentifier='<?php echo $randid.'-'; ?>"+chid+"']");
					if (chel.length) {
						chel.find(".vbomodchrates-rate-val").find(".vbo_price").text(obj['channels'][chid]);
					}
				}
			} else if (jQuery('.vbomodchrates-rate-ota').length && channels_sel.length && cust_rmodval > 0) {
				// apply custom modifiers
				var base_vbo_rate = parseFloat(obj['website']);
				var ch_final_cost = 0;
				if (!isNaN(base_vbo_rate) && base_vbo_rate > 0) {
					// apply charge
					if (cust_rmodpcent == 'pcent') {
						// percentage
						ch_final_cost = base_vbo_rate * (100 + cust_rmodval) / 100;
					} else {
						// absolute
						ch_final_cost = base_vbo_rate + cust_rmodval;
					}
				}
				ch_final_cost = ch_final_cost.toFixed(<?php echo $num_decimals; ?>);
				for (var ch_id in channels_sel) {
					if (!channels_sel.hasOwnProperty(ch_id)) {
						continue;
					}
					var chel = jQuery(".vbomodchrates-rate-ota[data-chidentifier='<?php echo $randid.'-'; ?>" + channels_sel[ch_id] + "']");
					if (chel.length) {
						chel.find(".vbomodchrates-rate-val").find(".vbo_price").text(ch_final_cost);
					}
				}
			}
			//make sure to display the container in case it was hidden by PHP for no availability
			if (!jQuery('.vbomodchrates-rates-inner').is(':visible')) {
				jQuery('.vbomodchrates-noav-par').remove();
				jQuery('.vbomodchrates-rates-inner').show();
			}
		}
	}).fail(function() { 
		console.log("Request Failed");
	});
}
</script>
<input type="hidden" id="vbroomhelpermod<?php echo $randid; ?>" value="1"/>
