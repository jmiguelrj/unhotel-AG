<?php
/**
 * @package     VikBooking
 * @subpackage  com_vikbooking
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

$data = $this->data;
$rooms = $this->rooms;

$vbo_app = VikBooking::getVboApplication();
$vbo_app->loadSelect2();
$df = VikBooking::getDateFormat(true);
if ($df == "%d/%m/%Y") {
	$cdf = 'd/m/Y';
} elseif ($df == "%m/%d/%Y") {
	$cdf = 'm/d/Y';
} else {
	$cdf = 'Y/m/d';
}
$roomsel = '';
if ($rooms) {
	$nowrooms = count($data) && !empty($data['idrooms']) && $data['allrooms'] == 0 ? explode(';', $data['idrooms']) : array();
	$roomsel = '<select id="restr-idrooms" name="idrooms[]" multiple="multiple">'."\n";
	foreach ($rooms as $r) {
		$roomsel .= '<option value="'.$r['id'].'"'.(in_array('-'.$r['id'].'-', $nowrooms) ? ' selected="selected"' : '').'>'.$r['name'].'</option>'."\n";
	}
	$roomsel .= '</select>';
}
//CTA and CTD
$cur_setcta = count($data) && !empty($data['ctad']) ? explode(',', $data['ctad']) : array();
$cur_setctd = count($data) && !empty($data['ctdd']) ? explode(',', $data['ctdd']) : array();
$wdaysmap = array('0' => JText::_('VBSUNDAY'), '1' => JText::_('VBMONDAY'), '2' => JText::_('VBTUESDAY'), '3' => JText::_('VBWEDNESDAY'), '4' => JText::_('VBTHURSDAY'), '5' => JText::_('VBFRIDAY'), '6' => JText::_('VBSATURDAY'));
$ctasel = '<select id="restr-ctad" name="ctad[]" multiple="multiple" size="7">'."\n";
foreach ($wdaysmap as $wdk => $wdv) {
	$ctasel .= '<option value="'.$wdk.'"'.(in_array('-'.$wdk.'-', $cur_setcta) ? ' selected="selected"' : '').'>'.$wdv.'</option>'."\n";
}
$ctasel .= '</select>';
$ctdsel = '<select id="restr-ctdd" name="ctdd[]" multiple="multiple" size="7">'."\n";
foreach ($wdaysmap as $wdk => $wdv) {
	$ctdsel .= '<option value="'.$wdk.'"'.(in_array('-'.$wdk.'-', $cur_setctd) ? ' selected="selected"' : '').'>'.$wdv.'</option>'."\n";
}
$ctdsel .= '</select>';
//
$dfromval = count($data) && !empty($data['dfrom']) ? date($cdf, $data['dfrom']) : '';
$dtoval = count($data) && !empty($data['dto']) ? date($cdf, $data['dto']) : '';
$vbra1 = '';
$vbra2 = '';
$vbrb1 = '';
$vbrb2 = '';
$vbrc1 = '';
$vbrc2 = '';
$vbrd1 = '';
$vbrd2 = '';
if (count($data) && strlen((string)$data['wdaycombo'])) {
	$vbcomboparts = explode(':', $data['wdaycombo']);
	foreach($vbcomboparts as $kc => $cb) {
		if (!empty($cb)) {
			$nowcombo = explode('-', $cb);
			if ($kc == 0) {
				$vbra1 = $nowcombo[0];
				$vbra2 = $nowcombo[1];
			} elseif ($kc == 1) {
				$vbrb1 = $nowcombo[0];
				$vbrb2 = $nowcombo[1];
			} elseif ($kc == 2) {
				$vbrc1 = $nowcombo[0];
				$vbrc2 = $nowcombo[1];
			} elseif ($kc == 3) {
				$vbrd1 = $nowcombo[0];
				$vbrd2 = $nowcombo[1];
			}
		}
	}
}
$arrwdays = array(1 => JText::_('VBMONDAY'),
		2 => JText::_('VBTUESDAY'),
		3 => JText::_('VBWEDNESDAY'),
		4 => JText::_('VBTHURSDAY'),
		5 => JText::_('VBFRIDAY'),
		6 => JText::_('VBSATURDAY'),
		0 => JText::_('VBSUNDAY')
);
?>
<script type="text/javascript">
function vbSecondArrWDay() {
	var wdayone = document.adminForm.wday.value;
	if (wdayone != "") {
		document.getElementById("vbwdaytwodivid").style.display = "inline-block";
		document.adminForm.cta.checked = false;
		document.adminForm.ctd.checked = false;
		vbToggleCta();
		vbToggleCtd();
	} else {
		document.getElementById("vbwdaytwodivid").style.display = "none";
	}
	vbComboArrWDay();
}
function vbComboArrWDay() {
	var wdayone = document.adminForm.wday;
	var wdaytwo = document.adminForm.wdaytwo;
	if (wdayone.value != "" && wdaytwo.value != "" && wdayone.value != wdaytwo.value) {
		var comboa = wdayone.options[wdayone.selectedIndex].text;
		var combob = wdaytwo.options[wdaytwo.selectedIndex].text;
		document.getElementById("vbrcomboa1").innerHTML = comboa;
		document.getElementById("vbrcomboa2").innerHTML = combob;
		document.getElementById("vbrcomboa").value = wdayone.value+"-"+wdaytwo.value;
		document.getElementById("vbrcombob1").innerHTML = combob;
		document.getElementById("vbrcombob2").innerHTML = comboa;
		document.getElementById("vbrcombob").value = wdaytwo.value+"-"+wdayone.value;
		document.getElementById("vbrcomboc1").innerHTML = comboa;
		document.getElementById("vbrcomboc2").innerHTML = comboa;
		document.getElementById("vbrcomboc").value = wdayone.value+"-"+wdayone.value;
		document.getElementById("vbrcombod1").innerHTML = combob;
		document.getElementById("vbrcombod2").innerHTML = combob;
		document.getElementById("vbrcombod").value = wdaytwo.value+"-"+wdaytwo.value;
		document.getElementById("vbwdaycombodivid").style.display = "block";
	} else {
		document.getElementById("vbwdaycombodivid").style.display = "none";
	}
}
function vbToggleRooms() {
	if (document.adminForm.allrooms.checked == true) {
		document.getElementById("vbrestrroomsdiv").style.display = "none";
	} else {
		document.getElementById("vbrestrroomsdiv").style.display = "flex";
	}
}
function vbToggleCta() {
	if (document.adminForm.cta.checked != true) {
		document.getElementById("vbrestrctadiv").style.display = "none";
	} else {
		document.getElementById("vbrestrctadiv").style.display = "flex";
		document.adminForm.wday.value = "";
		document.adminForm.wdaytwo.value = "";
		vbSecondArrWDay();

	}
}
function vbToggleCtd() {
	if (document.adminForm.ctd.checked != true) {
		document.getElementById("vbrestrctddiv").style.display = "none";
	} else {
		document.getElementById("vbrestrctddiv").style.display = "flex";
		document.adminForm.wday.value = "";
		document.adminForm.wdaytwo.value = "";
		vbSecondArrWDay();
	}
}
function vbToggleDuration(val) {
	if (val == 'datesrange') {
		document.getElementById("restr-duration-month").style.display = "none";
		document.getElementById("restr-duration-datesrange").style.display = "flex";
		document.getElementById('restr-month').value = 0;
	} else {
		document.getElementById("restr-duration-datesrange").style.display = "none";
		document.getElementById("restr-duration-month").style.display = "flex";
	}
}
var restr_end_date = null;
function vbToggleRepeatRestr() {
	var restrdur = jQuery('#restr-duration').val();
	var dfrom = jQuery('#dfrom').val();
	var dto = jQuery('#dto').val();
	if (<?php echo count($data) ? 'true || ' : ''; ?>restrdur != 'datesrange' || !dfrom.length || !dto.length) {
		// repeating restrictions is only allowed when creating a new one
		vbCancelRepeatRestr();
		return;
	}
	var dfrom_parts = dfrom.split('/');
	var dto_parts = dto.split('/');
	if ('<?php echo $cdf; ?>' == 'd/m/Y') {
		var fromd = new Date(dfrom_parts[2], (dfrom_parts[1] - 1), dfrom_parts[0], 0, 0, 0, 0);
		var tod = new Date(dto_parts[2], (dto_parts[1] - 1), dto_parts[0], 0, 0, 0, 0);
	} else if ('<?php echo $cdf; ?>' == 'm/d/Y') {
		var fromd = new Date(dfrom_parts[2], (dfrom_parts[0] - 1), dfrom_parts[1], 0, 0, 0, 0);
		var tod = new Date(dto_parts[2], (dto_parts[0] - 1), dto_parts[1], 0, 0, 0, 0);
	} else {
		var fromd = new Date(dfrom_parts[0], (dfrom_parts[1] - 1), dfrom_parts[2], 0, 0, 0, 0);
		var tod = new Date(dto_parts[0], (dto_parts[1] - 1), dto_parts[2], 0, 0, 0, 0);
	}
	if (!fromd || !tod) {
		vbCancelRepeatRestr();
		return;
	}
	restr_end_date = tod;
	var utc1 = Date.UTC(fromd.getFullYear(), fromd.getMonth(), fromd.getDate());
	var utc2 = Date.UTC(tod.getFullYear(), tod.getMonth(), tod.getDate());
	var totdays = (Math.ceil((utc2 - utc1) / (1000 * 60 * 60 * 24))) + 1;
	if (totdays < 1 || totdays >= 7) {
		vbCancelRepeatRestr();
		return;
	}
	var wdays_tn = ["<?php echo addslashes(JText::_('VBSUNDAY')); ?>", "<?php echo addslashes(JText::_('VBMONDAY')); ?>", "<?php echo addslashes(JText::_('VBTUESDAY')); ?>", "<?php echo addslashes(JText::_('VBWEDNESDAY')); ?>", "<?php echo addslashes(JText::_('VBTHURSDAY')); ?>", "<?php echo addslashes(JText::_('VBFRIDAY')); ?>", "<?php echo addslashes(JText::_('VBSATURDAY')); ?>"];
	var wdays_pool = new Array;
	while (fromd <= tod) {
		wdays_pool.push(fromd.getDay());
		// go to next day
		fromd.setDate(fromd.getDate() + 1);
	}
	var wdays_str = new Array;
	for (var i in wdays_pool) {
		if (!wdays_pool.hasOwnProperty(i)) {
			continue;
		}
		wdays_str.push(wdays_tn[wdays_pool[i]]);
	}
	var repeat_str = "<?php echo addslashes(JText::_('VBORESTRREPEATONWDAYS')); ?>";
	jQuery('#restr-repeat-wdays').text(repeat_str.replace("%s", wdays_str.join(', ')));
	jQuery('#restr-repeat').fadeIn();
}
function vbCancelRepeatRestr() {
	jQuery('input[name="repeat"]').attr('checked', false);
	jQuery('#restr-repeat, #restr-repeat-until').hide();
}
function vbToggleRepeatUntil() {
	if (jQuery('input[name="repeat"]').is(':checked')) {
		// by default, set the "repeat until date" to end of year to-date
		if (restr_end_date != null) {
			var end_day = 31;
			var end_mon = 12;
			var end_year = restr_end_date.getFullYear();
			if ('<?php echo $cdf; ?>' == 'd/m/Y') {
				var endyear = end_day + '/' + end_mon + '/' + end_year;
			} else if ('<?php echo $cdf; ?>' == 'm/d/Y') {
				var endyear = end_mon + '/' + end_day + '/' + end_year;
			} else {
				var endyear = end_year + '/' + end_mon + '/' + end_day;
			}
			jQuery('#repeatuntil').val(endyear).attr('data-alt-value', endyear);
		}
		//
		jQuery('#restr-repeat-until').fadeIn();
	} else {
		jQuery('#restr-repeat-until').fadeOut();
	}
}
</script>

<form name="adminForm" id="adminForm" action="index.php" method="post">
	<div class="vbo-admin-container">
		<div class="vbo-config-maintab-left">
			<fieldset class="adminform">
				<div class="vbo-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VBOADMINLEGENDDETAILS'); ?></legend>
					<div class="vbo-params-container">
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo $vbo_app->createPopover(array('title' => JText::_('VBRESTRICTIONSHELPTITLE'), 'content' => JText::_('VBRESTRICTIONSSHELP'))); ?><?php echo JText::_('VBNEWRESTRICTIONNAME'); ?>*</div>
							<div class="vbo-param-setting"><input type="text" name="name" value="<?php echo count($data) ? $data['name'] : ''; ?>" size="40"/></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWRESTRICTIONDATERANGE') . '/' . JText::_('VBNEWRESTRICTIONONE'); ?>*</div>
							<div class="vbo-param-setting">
								<select id="restr-duration" onchange="vbToggleDuration(this.value);">
									<option value="datesrange"<?php echo ((count($data) && !empty($data['dfrom'])) || !count($data) ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBNEWRESTRICTIONDATERANGE'); ?></option>
									<option value="month"<?php echo count($data) && (int)$data['month'] > 0 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBNEWRESTRICTIONONE'); ?></option>
								</select>
							</div>
						</div>
						<div class="vbo-param-container vbo-param-nested" id="restr-duration-month" style="display: <?php echo count($data) && (int)$data['month'] > 0 ? 'flex' : 'none'; ?>;">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWRESTRICTIONONE'); ?>*</div>
							<div class="vbo-param-setting">
								<select name="month" id="restr-month">
									<option value="0">----</option>
									<option value="1"<?php echo (count($data) && $data['month'] == 1 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBMONTHONE'); ?></option>
									<option value="2"<?php echo (count($data) && $data['month'] == 2 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBMONTHTWO'); ?></option>
									<option value="3"<?php echo (count($data) && $data['month'] == 3 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBMONTHTHREE'); ?></option>
									<option value="4"<?php echo (count($data) && $data['month'] == 4 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBMONTHFOUR'); ?></option>
									<option value="5"<?php echo (count($data) && $data['month'] == 5 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBMONTHFIVE'); ?></option>
									<option value="6"<?php echo (count($data) && $data['month'] == 6 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBMONTHSIX'); ?></option>
									<option value="7"<?php echo (count($data) && $data['month'] == 7 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBMONTHSEVEN'); ?></option>
									<option value="8"<?php echo (count($data) && $data['month'] == 8 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBMONTHEIGHT'); ?></option>
									<option value="9"<?php echo (count($data) && $data['month'] == 9 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBMONTHNINE'); ?></option>
									<option value="10"<?php echo (count($data) && $data['month'] == 10 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBMONTHTEN'); ?></option>
									<option value="11"<?php echo (count($data) && $data['month'] == 11 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBMONTHELEVEN'); ?></option>
									<option value="12"<?php echo (count($data) && $data['month'] == 12 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBMONTHTWELVE'); ?></option>
								</select>
							</div>
						</div>
						<div class="vbo-param-container vbo-param-nested" id="restr-duration-datesrange" style="display: <?php echo ((count($data) && !empty($data['dfrom'])) || !count($data) ? 'flex' : 'none'); ?>;">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWRESTRICTIONDATERANGE'); ?>*</div>
							<div class="vbo-param-setting">
								<div style="display: block; margin-bottom: 3px;">
									<?php echo '<span class="vbrestrdrangesp">'.JText::_('VBNEWRESTRICTIONDFROMRANGE').'</span>'.$vbo_app->getCalendar($dfromval, 'dfrom', 'dfrom', $df, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?>
								</div>
								<div style="display: block; margin-bottom: 3px;">
									<?php echo '<span class="vbrestrdrangesp">'.JText::_('VBNEWRESTRICTIONDTORANGE').'</span>'.$vbo_app->getCalendar($dtoval, 'dto', 'dto', $df, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?>
								</div>
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWRESTRICTIONALLROOMS'); ?></div>
							<div class="vbo-param-setting">
								<?php echo $vbo_app->printYesNoButtons('allrooms', JText::_('VBYES'), JText::_('VBNO'), ((count($data) && $data['allrooms'] == 1) || !count($data) ? 1 : 0), 1, 0, 'vbToggleRooms();'); ?>
							</div>
						</div>
						<div class="vbo-param-container vbo-param-nested" id="vbrestrroomsdiv" style="display: <?php echo ((count($data) && $data['allrooms'] == 1) || !count($data) ? 'none' : 'flex'); ?>;">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWRESTRICTIONROOMSAFF'); ?></div>
							<div class="vbo-param-setting">
								<span class="vbo-select-all"><?php echo JText::_('VBOSELECTALL'); ?></span>
								<?php echo $roomsel; ?>
							</div>
						</div>
						<div class="vbo-param-container" id="restr-repeat" style="display: none;">
							<div class="vbo-param-label" id="restr-repeat-wdays"></div>
							<div class="vbo-param-setting">
								<?php echo $vbo_app->printYesNoButtons('repeat', JText::_('VBYES'), JText::_('VBNO'), 0, 1, 0, 'vbToggleRepeatUntil();'); ?>
							</div>
						</div>
						<div class="vbo-param-container vbo-param-nested" id="restr-repeat-until" style="display: none;">
							<div class="vbo-param-label"><?php echo JText::_('VBORESTRREPEATUNTIL'); ?></div>
							<div class="vbo-param-setting">
								<?php echo $vbo_app->getCalendar('', 'repeatuntil', 'repeatuntil', $df, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?>
							</div>
						</div>
					</div>
				</div>
			</fieldset>
		</div>
		
		<div class="vbo-config-maintab-right">
			<fieldset class="adminform">
				<div class="vbo-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VBOADMINLEGENDSETTINGS'); ?></legend>
					<div class="vbo-params-container">
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWRESTRICTIONMINLOS'); ?>*</div>
							<div class="vbo-param-setting"><input type="number" name="minlos" id="minlosinp" value="<?php echo count($data) ? $data['minlos'] : '1'; ?>" min="1" /></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo $vbo_app->createPopover(array('title' => JText::_('VBNEWRESTRICTIONMULTIPLYMINLOS'), 'content' => JText::_('VBNEWRESTRICTIONMULTIPLYMINLOSHELP'))); ?><?php echo JText::_('VBNEWRESTRICTIONMULTIPLYMINLOS'); ?></div>
							<div class="vbo-param-setting"><?php echo $vbo_app->printYesNoButtons('multiplyminlos', JText::_('VBYES'), JText::_('VBNO'), (count($data) && $data['multiplyminlos'] == 1 ? 1 : 0), 1, 0); ?></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWRESTRICTIONMAXLOS'); ?></div>
							<div class="vbo-param-setting"><input type="number" name="maxlos" id="maxlosinp" value="<?php echo count($data) ? $data['maxlos'] : '0'; ?>" min="0" /></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWRESTRICTIONSETCTA'); ?></div>
							<div class="vbo-param-setting">
								<?php echo $vbo_app->printYesNoButtons('cta', JText::_('VBYES'), JText::_('VBNO'), (count($cur_setcta) > 0 ? 1 : 0), 1, 0, 'vbToggleCta();'); ?>
							</div>
						</div>
						<div class="vbo-param-container vbo-param-nested" id="vbrestrctadiv" style="display: <?php echo count($cur_setcta) > 0 ? 'flex' : 'none'; ?>;">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWRESTRICTIONWDAYSCTA'); ?></div>
							<div class="vbo-param-setting"><?php echo $ctasel; ?></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWRESTRICTIONSETCTD'); ?></div>
							<div class="vbo-param-setting">
								<?php echo $vbo_app->printYesNoButtons('ctd', JText::_('VBYES'), JText::_('VBNO'), (count($cur_setctd) > 0 ? 1 : 0), 1, 0, 'vbToggleCtd();'); ?>
							</div>
						</div>
						<div class="vbo-param-container vbo-param-nested" id="vbrestrctddiv" style="display: <?php echo count($cur_setctd) > 0 ? 'flex' : 'none'; ?>;">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWRESTRICTIONWDAYSCTD'); ?></div>
							<div class="vbo-param-setting"><?php echo $ctdsel; ?></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWRESTRICTIONWDAY'); ?></div>
							<div class="vbo-param-setting">
								<select name="wday" onchange="vbSecondArrWDay();"><option value=""></option><option value="0"<?php echo (count($data) && strlen((string)$data['wday']) && $data['wday'] == 0 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBSUNDAY'); ?></option><option value="1"<?php echo (count($data) && $data['wday'] == 1 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBMONDAY'); ?></option><option value="2"<?php echo (count($data) && $data['wday'] == 2 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBTUESDAY'); ?></option><option value="3"<?php echo (count($data) && $data['wday'] == 3 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBWEDNESDAY'); ?></option><option value="4"<?php echo (count($data) && $data['wday'] == 4 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBTHURSDAY'); ?></option><option value="5"<?php echo (count($data) && $data['wday'] == 5 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBFRIDAY'); ?></option><option value="6"<?php echo (count($data) && $data['wday'] == 6 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBSATURDAY'); ?></option></select>
								<div class="vbwdaytwodiv" id="vbwdaytwodivid" style="display: <?php echo (count($data) && strlen((string)$data['wday']) ? 'inline-block' : 'none'); ?>;"><span><?php echo JText::_('VBNEWRESTRICTIONOR'); ?></span> 
								<select name="wdaytwo" onchange="vbComboArrWDay();"><option value=""></option><option value="0"<?php echo (count($data) && strlen((string)$data['wdaytwo']) && $data['wdaytwo'] == 0 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBSUNDAY'); ?></option><option value="1"<?php echo (count($data) && $data['wdaytwo'] == 1 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBMONDAY'); ?></option><option value="2"<?php echo (count($data) && $data['wdaytwo'] == 2 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBTUESDAY'); ?></option><option value="3"<?php echo (count($data) && $data['wdaytwo'] == 3 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBWEDNESDAY'); ?></option><option value="4"<?php echo (count($data) && $data['wdaytwo'] == 4 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBTHURSDAY'); ?></option><option value="5"<?php echo (count($data) && $data['wdaytwo'] == 5 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBFRIDAY'); ?></option><option value="6"<?php echo (count($data) && $data['wdaytwo'] == 6 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBSATURDAY'); ?></option></select></div>
								<div class="vbwdaycombodiv" id="vbwdaycombodivid" style="display: <?php echo (count($data) && !empty($data['wdaycombo']) && strlen($data['wdaycombo']) > 3 ? 'block' : 'none'); ?>;"><span class="vbwdaycombosp"><?php echo JText::_('VBNEWRESTRICTIONALLCOMBO'); ?></span><span class="vbwdaycombohelp"><?php echo JText::_('VBNEWRESTRICTIONALLCOMBOHELP'); ?></span>
								<p class="vbwdaycombop"><label for="vbrcomboa" style="display: inline-block; vertical-align: top;"><span id="vbrcomboa1"><?php echo strlen($vbra1) ? $arrwdays[intval($vbra1)] : ''; ?></span> - <span id="vbrcomboa2"><?php echo strlen($vbra2) ? $arrwdays[intval($vbra2)] : ''; ?></span></label> <input type="checkbox" name="comboa" id="vbrcomboa" value="<?php echo strlen($vbra1) ? $vbra1.'-'.$vbra2 : ''; ?>"<?php echo (strlen($vbra1) && $vbcomboparts[0] == $vbra1.'-'.$vbra2 ? ' checked="checked"' : ''); ?> style="display: inline-block; vertical-align: top;"/></p>
								<p class="vbwdaycombop"><label for="vbrcombob" style="display: inline-block; vertical-align: top;"><span id="vbrcombob1"><?php echo strlen($vbrb1) ? $arrwdays[intval($vbrb1)] : ''; ?></span> - <span id="vbrcombob2"><?php echo strlen($vbrb2) ? $arrwdays[intval($vbrb2)] : ''; ?></span></label> <input type="checkbox" name="combob" id="vbrcombob" value="<?php echo strlen($vbrb1) ? $vbrb1.'-'.$vbrb2 : ''; ?>"<?php echo (strlen($vbrb1) && $vbcomboparts[1] == $vbrb1.'-'.$vbrb2 ? ' checked="checked"' : ''); ?> style="display: inline-block; vertical-align: top;"/></p>
								<p class="vbwdaycombop"><label for="vbrcomboc" style="display: inline-block; vertical-align: top;"><span id="vbrcomboc1"><?php echo strlen($vbrc1) ? $arrwdays[intval($vbrc1)] : ''; ?></span> - <span id="vbrcomboc2"><?php echo strlen($vbrc2) ? $arrwdays[intval($vbrc2)] : ''; ?></span></label> <input type="checkbox" name="comboc" id="vbrcomboc" value="<?php echo strlen($vbrc1) ? $vbrc1.'-'.$vbrc2 : ''; ?>"<?php echo (strlen($vbrc1) && $vbcomboparts[2] == $vbrc1.'-'.$vbrc2 ? ' checked="checked"' : ''); ?> style="display: inline-block; vertical-align: top;"/></p>
								<p class="vbwdaycombop"><label for="vbrcombod" style="display: inline-block; vertical-align: top;"><span id="vbrcombod1"><?php echo strlen($vbrd1) ? $arrwdays[intval($vbrd1)] : ''; ?></span> - <span id="vbrcombod2"><?php echo strlen($vbrd2) ? $arrwdays[intval($vbrd2)] : ''; ?></span></label> <input type="checkbox" name="combod" id="vbrcombod" value="<?php echo strlen($vbrd1) ? $vbrd1.'-'.$vbrd2 : ''; ?>"<?php echo (strlen($vbrd1) && $vbcomboparts[3] == $vbrd1.'-'.$vbrd2 ? ' checked="checked"' : ''); ?> style="display: inline-block; vertical-align: top;"/></p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
<?php
if (count($data)) :
?>
	<input type="hidden" name="where" value="<?php echo $data['id']; ?>">
<?php
endif;
?>
	<input type="hidden" name="task" value="">
	<input type="hidden" name="option" value="com_vikbooking">
	<?php echo JHtml::_('form.token'); ?>
</form>
<script type="text/javascript">
jQuery(function() {
	jQuery('#dfrom').val('<?php echo $dfromval; ?>').attr('data-alt-value', '<?php echo $dfromval; ?>');
	jQuery('#dto').val('<?php echo $dtoval; ?>').attr('data-alt-value', '<?php echo $dtoval; ?>');
	jQuery('#restr-idrooms, #restr-ctad, #restr-ctdd').select2();
	jQuery('.vbo-select-all').click(function() {
		var nextsel = jQuery(this).next("select");
		nextsel.find("option").prop('selected', true);
		nextsel.trigger('change');
	});
	jQuery('#dfrom, #dto, #minlosinp, #maxlosinp').change(function() {
		vbToggleRepeatRestr();
	});
	jQuery('#dfrom, #dto').blur(function() {
		vbToggleRepeatRestr();
	});
});
<?php
if (count($data) && strlen((string)$data['wday']) && strlen((string)$data['wdaytwo'])) {
	?>
vbComboArrWDay();
	<?php
}
?>
</script>
