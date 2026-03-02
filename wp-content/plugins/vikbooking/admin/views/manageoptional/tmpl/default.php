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

$row = $this->row;
$tot_rooms = $this->tot_rooms;
$tot_rooms_options = $this->tot_rooms_options;

// adjust option params
if ($row) {
	$row['oparams'] = !empty($row['oparams']) ? (array) json_decode($row['oparams'], true) : [];
}

$vbo_app = VikBooking::getVboApplication();
$vbo_app->loadSelect2();
$vbo_app->loadDatePicker();

$editor = JEditor::getInstance(JFactory::getApplication()->get('editor'));

$dbo = JFactory::getDbo();
$q = "SELECT * FROM `#__vikbooking_iva`;";
$dbo->setQuery($q);
$ivas = $dbo->loadAssocList();
if ($ivas) {
	$wiva = "<select name=\"optaliq\"><option value=\"\"> </option>\n";
	foreach ($ivas as $iv) {
		$wiva .= "<option value=\"".$iv['id']."\"".($row && $row['idiva'] == $iv['id'] ? " selected=\"selected\"" : "").">".(empty($iv['name']) ? $iv['aliq']."%" : $iv['name']."-".$iv['aliq']."%")."</option>\n";
	}
	$wiva .= "</select>\n";
} else {
	$wiva = "<a href=\"index.php?option=com_vikbooking&task=iva\">".JText::_('VBNOIVAFOUND')."</a>";
}

$currencysymb = VikBooking::getCurrencySymb();
if ($row && strlen((string)$row['forceval'])) {
	$forceparts = explode("-", $row['forceval']);
	$forcedq = $forceparts[0];
	$forcedqperday = intval($forceparts[1]) == 1 ? true : false;
	$forcedqperchild = intval($forceparts[2]) == 1 ? true : false;
	$forcesummary = intval($forceparts[3]) == 1 ? true : false;
} else {
	$forcedq = "1";
	$forcedqperday = false;
	$forcedqperchild = false;
	$forcesummary = false;
}
$useageintervals = false;
$oldageintervals = '';
if ($row && !empty($row['ageintervals'])) {
	$useageintervals = true;
	$ageparts = explode(';;', $row['ageintervals']);
	foreach ($ageparts as $kage => $age) {
		if (empty($age)) {
			continue;
		}
		$interval = explode('_', $age);
		$oldageintervals .= '<p id="old'.$kage.'intv">'.JText::_('VBNEWAGEINTERVALFROM').': <input type="number" min="0" max="30" name="agefrom[]" size="2" value="'.$interval[0].'"/> '.JText::_('VBNEWAGEINTERVALTO').': <input type="number" min="0" max="30" name="ageto[]" size="2" value="'.$interval[1].'"/> '.JText::_('VBNEWAGEINTERVALCOST').': <input type="number" step="any" name="agecost[]" size="4" value="'.$interval[2].'"/> <select name="agectype[]"><option value="">'.$currencysymb.'</option><option value="%"'.(array_key_exists(3, $interval) && strpos($interval[3], '%') !== false && strpos($interval[3], '%b') === false ? ' selected="selected"' : '').'>'.JText::_('VBNEWAGEINTERVALCOSTPCENT').'</option><option value="%b"'.(array_key_exists(3, $interval) && strpos($interval[3], '%b') !== false ? ' selected="selected"' : '').'>'.JText::_('VBOAGEINTVALBCOSTPCENT').'</option></select> <i class="'.VikBookingIcons::i('minus-circle').'" onclick="removeAgeInterval(\'old'.$kage.'intv\');" style="font-size: 25px; cursor: pointer;"></i></p>'."\n";
	}
}
$vbo_app->prepareModalBox();

$vbo_df = VikBooking::getDateFormat();
$datesep = VikBooking::getDateSeparator();
$df = $vbo_df == "%d/%m/%Y" ? 'd/m/Y' : ($vbo_df == "%m/%d/%Y" ? 'm/d/Y' : 'Y/m/d');
$juidf = $vbo_df == "%d/%m/%Y" ? 'dd/mm/yy' : ($vbo_df == "%m/%d/%Y" ? 'mm/dd/yy' : 'yy/mm/dd');

?>
<script type="text/javascript">
function showResizeSel() {
	if (document.adminForm.autoresize.checked == true) {
		jQuery('#resizesel').show();
	} else {
		jQuery('#resizesel').hide();
	}
	return true;
}
function showForceSel() {
	if (document.adminForm.forcesel.checked == true) {
		jQuery('.vbo-forceval-param').fadeIn();
	} else {
		jQuery('.vbo-forceval-param').hide();
	}
	return true;
}
function showDatesValidity() {
	if (document.adminForm.alwaysav.checked !== true) {
		jQuery('.vbo-opt-alwaysavail').show();
	} else {
		jQuery('.vbo-opt-alwaysavail').hide();
	}
	return true;
}
function showMinGuestsFilt() {
	if (document.adminForm.minguests.checked === true) {
		jQuery('.vbo-opt-minguestsfiltering').show();
	} else {
		jQuery('.vbo-opt-minguestsfiltering').hide();
	}
	return true;
}
function showMaxQuant() {
	if (document.adminForm.opthmany.checked == true) {
		jQuery('#maxquantblock').show();
	} else {
		jQuery('#maxquantblock').hide();
	}
	return true;
}
function showAgeIntervals() {
	if (document.adminForm.ifchildren.checked == true) {
		jQuery('#ifchildrenextra').show();
		document.adminForm.optperperson.checked = false;
		if (jQuery('#myDiv').find('input').length > 0) {
			jQuery('#optperpersontr').hide();
			jQuery('#opthmanytr').hide();
			jQuery('#forceseltr').hide();
			jQuery('.vbo-forceval-param').hide();
		}
	} else {
		jQuery('#ifchildrenextra').hide();
		if (!jQuery('#optperpersontr').is(':visible')) {
			jQuery('#optperpersontr').show();
			jQuery('#opthmanytr').show();
			jQuery('#forceseltr').show();
			jQuery('.vbo-forceval-param').show();
		}
	}
	return true;
}
function addAgeInterval() {
	var ni = document.getElementById('myDiv');
	var numi = document.getElementById('moreagaintervals');
	var num = (document.getElementById('moreagaintervals').value -1)+ 2;
	numi.value = num;
	var newdiv = document.createElement('div');
	var divIdName = 'my'+num+'Div';
	newdiv.setAttribute('id',divIdName);
	newdiv.innerHTML = '<p><?php echo addslashes(JText::_('VBNEWAGEINTERVALFROM')); ?>: <input type=\'number\' min=\'0\' max=\'30\' name=\'agefrom[]\' size=\'2\'/> <?php echo addslashes(JText::_('VBNEWAGEINTERVALTO')); ?>: <input type=\'number\' min=\'0\' max=\'30\' name=\'ageto[]\' size=\'2\'/> <?php echo addslashes(JText::_('VBNEWAGEINTERVALCOST')); ?>: <input type=\'number\' step=\'any\' name=\'agecost[]\' size=\'4\'/> <select name=\'agectype[]\'><option value=\'\'><?php echo addslashes($currencysymb); ?></option><option value=\'%\'><?php echo addslashes(JText::_('VBNEWAGEINTERVALCOSTPCENT')); ?></option><option value=\'%b\'><?php echo addslashes(JText::_('VBOAGEINTVALBCOSTPCENT')); ?></option></select> <i class=\'<?php echo VikBookingIcons::i('minus-circle'); ?>\' onclick=\'removeAgeInterval("my'+num+'Div");\' style=\'font-size: 25px; cursor: pointer;\'></i></p>';
	ni.appendChild(newdiv);
	if (document.getElementById('optperpersontr').style.display != 'none') {
		jQuery('#optperpersontr').hide();
		jQuery('#opthmanytr').hide();
		jQuery('#forceseltr').hide();
		jQuery('.vbo-forceval-param').hide();
	}
}
function removeAgeInterval(el) {
	return (elem=document.getElementById(el)).parentNode.removeChild(elem);
}
jQuery(function() {
	jQuery(".vbo-manageoptional-datepicker:input").datepicker({
		dateFormat: "<?php echo $juidf; ?>",
		onSelect: vboCheckDates
	});
	jQuery('#idrooms').select2();
	jQuery('.vbo-select-all').click(function() {
		var nextsel = jQuery(this).next("select");
		nextsel.find("option").prop('selected', true);
		nextsel.trigger('change');
	});
<?php
if ($row && !empty($row['alwaysav'])) {
	$dateparts = explode(';', $row['alwaysav']);
	?>
	jQuery(".vbo-manageoptional-datepicker-from").datepicker("setDate", "<?php echo date($df, $dateparts[0]); ?>");
	jQuery(".vbo-manageoptional-datepicker-to").datepicker("setDate", "<?php echo date($df, $dateparts[1]); ?>");
	<?php
}
?>
});
function vboCheckDates(selectedDate, inst) {
	if (selectedDate === null || inst === null) {
		return;
	}
	var cur_from_date = jQuery(this).val();
	if (jQuery(this).hasClass("vbo-manageoptional-datepicker-from") && cur_from_date.length) {
		var nowstart = jQuery(this).datepicker("getDate");
		var nowstartdate = new Date(nowstart.getTime());
		jQuery(".vbo-manageoptional-datepicker-to").datepicker("option", {minDate: nowstartdate});
	}
}
</script>
<input type="hidden" value="0" id="moreagaintervals" />

<?php
if ($row) {
?>
<div class="vbo-outer-info-message" id="vbo-outer-info-message-opt" style="display: block;" onclick="removeAgeInterval('vbo-outer-info-message-opt');">
	<div class="vbo-info-message-cont">
		<i class="vboicn-info"></i><span><?php echo JText::sprintf('VBOOPTASSTOXROOMS', $tot_rooms_options, $tot_rooms); ?></span>
	</div>
</div>
<?php
}
?>

<form name="adminForm" id="adminForm" action="index.php" method="post" enctype="multipart/form-data">
	<div class="vbo-admin-container">
		<div class="vbo-config-maintab-left">
			<fieldset class="adminform">
				<div class="vbo-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VBOADMINLEGENDDETAILS'); ?></legend>
					<div class="vbo-params-container">
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWOPTONE'); ?></div>
							<div class="vbo-param-setting"><input type="text" name="optname" value="<?php echo $row ? htmlspecialchars($row['name']) : ''; ?>" size="40"/></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWOPTSEVEN'); ?></div>
							<div class="vbo-param-setting">
								<div class="vbo-param-setting-block">
									<?php echo ($row && !empty($row['img']) && file_exists(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.$row['img']) ? '<a href="'.VBO_SITE_URI.'resources/uploads/'.$row['img'].'" class="vbomodal vbo-room-img-modal" target="_blank"><i class="' . VikBookingIcons::i('image') . '"></i> '.$row['img'].'</a>' : ""); ?>
									<input type="file" name="optimg" size="35"/>
								</div>
								<div class="vbo-param-setting-block">
									<span class="vbo-resize-lb-cont">
										<label style="display: inline;" for="autoresize"><?php echo JText::_('VBNEWOPTNINE'); ?></label> 
										<input type="checkbox" id="autoresize" name="autoresize" value="1" onclick="showResizeSel();"/> 
									</span>
									<span id="resizesel" style="display: none;"><span><?php echo JText::_('VBNEWOPTTEN'); ?></span><input type="number" name="resizeto" value="250" min="0" class="vbo-medium-input"/> px</span>
								</div>
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWOPTTHREE'); ?></div>
							<div class="vbo-param-setting">
								<input type="number" step="any" name="optcost" value="<?php echo $row ? $row['cost'] : ''; ?>" size="10"/>
								<select name="pcentroom">
									<option value="0"<?php echo !$row || ($row && !(int)$row['pcentroom']) ? ' selected="selected"' : ''; ?>><?php echo $currencysymb; ?></option>
									<option value="1"<?php echo $row && (int)$row['pcentroom'] ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBOPTPCENTROOMFEE'); ?></option>
								</select>
								<span class="vbo-opt-pcentroom-help"><?php echo $vbo_app->createPopover(array('title' => JText::_('VBNEWOPTTHREE'), 'content' => JText::_('VBOPTPCENTROOMFEEHELP'))); ?></span>
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWOPTFOUR'); ?></div>
							<div class="vbo-param-setting"><?php echo $wiva; ?></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWOPTFIVE'); ?></div>
							<div class="vbo-param-setting">
								<?php echo $vbo_app->printYesNoButtons('optperday', JText::_('VBYES'), JText::_('VBNO'), ($row && intval($row['perday']) == 1 ? 'each' : 0), 'each', 0); ?>
							</div>
						</div>
						<div class="vbo-param-container" id="optperpersontr">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWOPTPERPERSON'); ?></div>
							<div class="vbo-param-setting">
								<?php echo $vbo_app->printYesNoButtons('optperperson', JText::_('VBYES'), JText::_('VBNO'), ($row && intval($row['perperson']) == 1 ? 'each' : 0), 'each', 0); ?>
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWOPTEIGHT'); ?></div>
							<div class="vbo-param-setting"><?php echo $currencysymb; ?> <input type="number" step="any" name="maxprice" value="<?php echo $row ? $row['maxprice'] : ''; ?>" size="4"/></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWOPTIFCHILDREN'); ?></div>
							<div class="vbo-param-setting">
								<?php echo $vbo_app->printYesNoButtons('ifchildren', JText::_('VBYES'), JText::_('VBNO'), ($row && intval($row['ifchildren']) == 1 ? 1 : 0), 1, 0, 'showAgeIntervals();'); ?>
							</div>
						</div>
						<div class="vbo-param-container vbo-param-container-full vbo-param-nested" id="ifchildrenextra" style="<?php echo ($useageintervals === true && strlen($oldageintervals) ? '' : 'display: none;'); ?>">
							<div class="vbo-param-setting">
								<span class="vbo-param-setting-comment"><?php echo JText::_('VBNEWOPTIFAGEINTERVAL'); ?></span>
								<div id="myDiv" class="vbo-dyninpnum-cont"><?php echo $oldageintervals; ?></div>
								<span class="vbo-param-setting-comment"><?php echo JText::_('VBO_CHILDFEES_AGEBUCKETS_HELP'); ?></span>
								<button type="button" class="btn vbo-config-btn" onclick="addAgeInterval();"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::_('VBADDAGEINTERVAL'); ?></button>
							</div>
						</div>
						<div class="vbo-param-container" id="opthmanytr">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWOPTSIX'); ?></div>
							<div class="vbo-param-setting">
								<?php echo $vbo_app->printYesNoButtons('opthmany', JText::_('VBYES'), JText::_('VBNO'), ($row && intval($row['hmany']) == 1 ? 'yes' : 0), 'yes', 0, 'showMaxQuant();'); ?>
								<span id="maxquantblock" style="display: <?php echo ($row && intval($row['hmany']) == 1 ? "block" : "none"); ?>;">
									<?php echo JText::_('VBNEWOPTMAXQUANTSEL'); ?> 
									<input type="number" min="0" name="maxquant" value="<?php echo $row ? $row['maxquant'] : '0'; ?>" size="4"/>
								</span>
							</div>
						</div>
						<div class="vbo-param-container" id="mingueststr">
							<div class="vbo-param-label"><?php echo JText::_('VBOMINGUESTSFILT'); ?></div>
							<div class="vbo-param-setting">
								<?php echo $vbo_app->printYesNoButtons('minguests', JText::_('VBYES'), JText::_('VBNO'), ($row && (!empty($row['oparams']['minguestsnum']) || !empty($row['oparams']['maxguestsnum'])) ? 1 : 0), 1, 0, 'showMinGuestsFilt();'); ?>
							</div>
						</div>
						<div class="vbo-param-container vbo-param-nested vbo-opt-minguestsfiltering" style="<?php echo ($row && !empty($row['oparams']['minguestsnum']) ? '' : 'display: none;'); ?>">
							<div class="vbo-param-label"><?php echo JText::_('VBOIFGUESTSMORETHAN'); ?></div>
							<div class="vbo-param-setting">
								<input type="number" name="minguestsnum" id="minguestsnum" value="<?php echo ($row && !empty($row['oparams']['minguestsnum']) ? (int)$row['oparams']['minguestsnum'] : '0'); ?>"/>
								&nbsp;
								<select name="mingueststype">
									<option value="guests"<?php echo ($row && !empty($row['oparams']['mingueststype']) && $row['oparams']['mingueststype'] == 'guests' ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBPVIEWORDERSPEOPLE'); ?></option>
									<option value="adults"<?php echo ($row && !empty($row['oparams']['mingueststype']) && $row['oparams']['mingueststype'] == 'adults' ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBEDITORDERADULTS'); ?></option>
								</select>
								<span class="vbo-opt-minguestsnum-help"><?php echo $vbo_app->createPopover(array('title' => JText::_('VBOMINGUESTSFILT'), 'content' => JText::_('VBOMINGUESTSFILTHELP'))); ?></span>
							</div>
						</div>
						<div class="vbo-param-container vbo-param-nested vbo-opt-minguestsfiltering" style="<?php echo ($row && !empty($row['oparams']['minguestsnum']) ? '' : 'display: none;'); ?>">
							<div class="vbo-param-label"><?php echo JText::_('VBOIFGUESTSLESSTHAN'); ?></div>
							<div class="vbo-param-setting">
								<input type="number" name="maxguestsnum" id="maxguestsnum" value="<?php echo ($row && !empty($row['oparams']['maxguestsnum']) ? (int)$row['oparams']['maxguestsnum'] : '0'); ?>"/>
								&nbsp;
								<select name="maxgueststype">
									<option value="guests"<?php echo ($row && !empty($row['oparams']['maxgueststype']) && $row['oparams']['maxgueststype'] == 'guests' ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBPVIEWORDERSPEOPLE'); ?></option>
									<option value="adults"<?php echo ($row && !empty($row['oparams']['maxgueststype']) && $row['oparams']['maxgueststype'] == 'adults' ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBEDITORDERADULTS'); ?></option>
								</select>
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWOPTALWAYSAV'); ?></div>
							<div class="vbo-param-setting">
								<?php echo $vbo_app->printYesNoButtons('alwaysav', JText::_('VBYES'), JText::_('VBNO'), (!$row || ($row && empty($row['alwaysav'])) ? 1 : 0), 1, 0, 'showDatesValidity();'); ?>
							</div>
						</div>
						<div class="vbo-param-container vbo-param-nested vbo-opt-alwaysavail" style="<?php echo ($row && !empty($row['alwaysav']) ? '' : 'display: none;'); ?>">
							<div class="vbo-param-label"><?php echo JText::_('VBPACKAGESDROM'); ?></div>
							<div class="vbo-param-setting">
								<input type="text" class="vbo-manageoptional-datepicker vbo-manageoptional-datepicker-from" name="avfrom" id="avfrom" value="" size="12"/>
							</div>
						</div>
						<div class="vbo-param-container vbo-param-nested vbo-opt-alwaysavail" style="<?php echo ($row && !empty($row['alwaysav']) ? '' : 'display: none;'); ?>">
							<div class="vbo-param-label"><?php echo JText::_('VBPACKAGESDTO'); ?></div>
							<div class="vbo-param-setting">
								<input type="text" class="vbo-manageoptional-datepicker vbo-manageoptional-datepicker-to" name="avto" id="avto" value="" size="12"/>
							</div>
						</div>
						<div class="vbo-param-container" id="forceseltr">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWOPTFORCESEL'); ?></div>
							<div class="vbo-param-setting">
								<?php echo $vbo_app->printYesNoButtons('forcesel', JText::_('VBYES'), JText::_('VBNO'), ($row && intval($row['forcesel']) == 1 ? 1 : 0), 1, 0, 'showForceSel();'); ?>
								<span class="vbo-param-setting-comment"><?php echo JText::_('VBO_MANDATORY_FEE'); ?></span>
							</div>
						</div>
						<div class="vbo-param-container vbo-param-nested vbo-forceval-param" style="display: <?php echo ($row && intval($row['forcesel']) == 1 ? "flex" : "none"); ?>;">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWOPTFORCEVALT'); ?></div>
							<div class="vbo-param-setting">
								<input type="number" min="0" step="any" name="forceval" value="<?php echo $forcedq; ?>" />
							</div>
						</div>
						<div class="vbo-param-container vbo-param-nested vbo-forceval-param" style="display: <?php echo ($row && intval($row['forcesel']) == 1 ? "flex" : "none"); ?>;">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWOPTFORCEVALTPDAY'); ?></div>
							<div class="vbo-param-setting">
								<?php echo $vbo_app->printYesNoButtons('forcevalperday', JText::_('VBYES'), JText::_('VBNO'), ($forcedqperday ? 1 : 0), 1, 0); ?>
							</div>
						</div>
						<div class="vbo-param-container vbo-param-nested vbo-forceval-param" style="display: <?php echo ($row && intval($row['forcesel']) == 1 ? "flex" : "none"); ?>;">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWOPTFORCEVALPERCHILD'); ?></div>
							<div class="vbo-param-setting">
								<?php echo $vbo_app->printYesNoButtons('forcevalperchild', JText::_('VBYES'), JText::_('VBNO'), ($forcedqperchild ? 1 : 0), 1, 0); ?>
							</div>
						</div>
						<div class="vbo-param-container vbo-param-nested vbo-forceval-param" style="display: <?php echo ($row && intval($row['forcesel']) == 1 ? "flex" : "none"); ?>;">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWOPTFORCESUMMARY'); ?></div>
							<div class="vbo-param-setting">
								<?php echo $vbo_app->printYesNoButtons('forcesummary', JText::_('VBYES'), JText::_('VBNO'), ($forcesummary ? 1 : 0), 1, 0); ?>
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
							<div class="vbo-param-setting">
								<span class="vbo-param-setting-comment"><?php echo JText::_('VBOPTHELPCITYTAXFEE'); ?></span>
							</div>
						</div>
						<div class="vbo-params-block">
							<div class="vbo-param-container">
								<div class="vbo-param-label"><?php echo JText::_('VBNEWOPTISCITYTAX'); ?></div>
								<div class="vbo-param-setting">
									<?php echo $vbo_app->printYesNoButtons('is_citytax', JText::_('VBYES'), JText::_('VBNO'), ($row && $row['is_citytax'] == 1 ? 1 : 0), 1, 0); ?>
								</div>
							</div>
						</div>
						<div class="vbo-params-block">
							<div class="vbo-param-container">
								<div class="vbo-param-label"><?php echo JText::_('VBNEWOPTISFEE'); ?></div>
								<div class="vbo-param-setting">
									<?php echo $vbo_app->printYesNoButtons('is_fee', JText::_('VBYES'), JText::_('VBNO'), ($row && $row['is_fee'] == 1 ? 1 : 0), 1, 0); ?>
								</div>
							</div>
						</div>
						<div class="vbo-params-block">
							<div class="vbo-param-container">
								<div class="vbo-param-label"><?php echo JText::_('VBO_IS_PET_FEE'); ?></div>
								<div class="vbo-param-setting">
									<?php echo $vbo_app->printYesNoButtons('pet_fee', JText::_('VBYES'), JText::_('VBNO'), ($row && !empty($row['oparams']['pet_fee']) ? 1 : 0), 1, 0); ?>
								</div>
							</div>
						</div>
						<div class="vbo-params-block">
							<div class="vbo-param-container">
								<div class="vbo-param-label"><?php echo JText::_('VBONEWOPTISDMGDEP'); ?></div>
								<div class="vbo-param-setting">
									<?php echo $vbo_app->printYesNoButtons('damagedep', JText::_('VBYES'), JText::_('VBNO'), ($row && !empty($row['oparams']['damagedep']) ? 1 : 0), 1, 0, 'vboToggleDamageDepSettings();'); ?>
								</div>
							</div>
							<div class="vbo-param-container vbo-param-nested" data-type="damagedep_settings" style="<?php echo $row && !empty($row['oparams']['damagedep']) ? '' : 'display: none;'; ?>">
								<div class="vbo-param-label"><?php echo JText::_('VBO_PAYMENT'); ?></div>
								<div class="vbo-param-setting">
									<select name="damagedep_settings[paywhen]" onchange="vboToggleDamageDepPayment();">
										<option value="0"<?php echo $row && ($row['oparams']['damagedep_settings']['paywhen'] ?? 0) == 0 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBO_PAY_WITH_ROOM'); ?></option>
										<option value="1"<?php echo $row && ($row['oparams']['damagedep_settings']['paywhen'] ?? 0) == 1 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBO_PAY_SEPARATELY'); ?></option>
									</select>
								</div>
							</div>
							<div class="vbo-param-container vbo-param-nested" data-type="damagedep_payment" style="<?php echo $row && !empty($row['oparams']['damagedep']) && !empty($row['oparams']['damagedep_settings']['paywhen']) ? '' : 'display: none;'; ?>">
								<div class="vbo-param-label"><?php echo JText::_('VBO_DD_RES_MAX_LOS'); ?></div>
								<div class="vbo-param-setting">
									<input type="number" name="damagedep_settings[bmaxlos]" value="<?php echo $row && isset($row['oparams']['damagedep_settings']['bmaxlos']) ? (int) $row['oparams']['damagedep_settings']['bmaxlos'] : 7; ?>" min="0" max="365" />
									<span class="vbo-param-setting-comment"><?php echo JText::_('VBO_DD_RES_MAX_LOS_HELP'); ?></span>
								</div>
							</div>
							<div class="vbo-param-container vbo-param-nested" data-type="damagedep_payment" style="<?php echo $row && !empty($row['oparams']['damagedep']) && !empty($row['oparams']['damagedep_settings']['paywhen']) ? '' : 'display: none;'; ?>">
								<div class="vbo-param-label"><?php echo JText::_('VBO_PAYMENT_WINDOW'); ?></div>
								<div class="vbo-param-setting">
									<input type="number" name="damagedep_settings[paywind]" value="<?php echo $row && isset($row['oparams']['damagedep_settings']['paywind']) ? (string) $row['oparams']['damagedep_settings']['paywind'] : 1; ?>" min="-365" max="365" />
									<span class="vbo-param-setting-comment"><?php echo JText::_('VBO_DAYS_BEFORE_CHECKIN'); ?></span>
								</div>
							</div>
							<div class="vbo-param-container vbo-param-nested" data-type="damagedep_payment" style="<?php echo $row && !empty($row['oparams']['damagedep']) && !empty($row['oparams']['damagedep_settings']['paywhen']) ? '' : 'display: none;'; ?>">
								<div class="vbo-param-label"><?php echo JText::_('VBLIBPAYNAME'); ?></div>
								<div class="vbo-param-setting">
									<select name="damagedep_settings[payid]">
										<option value="0">- <?php echo JText::_('VBO_USE_DEFAULT'); ?> -</option>
									<?php
									foreach ($this->payment_methods as $payment) {
										?>
										<option value="<?php echo $payment['id']; ?>"<?php echo $row && ($row['oparams']['damagedep_settings']['payid'] ?? 0) == $payment['id'] ? ' selected="selected"' : ''; ?>><?php echo sprintf('%s (#%d)', $payment['name'], $payment['id']); ?></option>
										<?php
									}
									?>
									</select>
								</div>
							</div>
						</div>
						<div class="vbo-params-block">
							<div class="vbo-param-container">
								<div class="vbo-param-label"><?php echo JText::_('VBO_LATE_CHECKOUT') . ' / ' . JText::_('VBO_EARLY_CHECKIN'); ?></div>
								<div class="vbo-param-setting">
									<?php echo $vbo_app->printYesNoButtons('custom_checkinout', JText::_('VBYES'), JText::_('VBNO'), ($row && !empty($row['oparams']['custom_checkinout']) ? 1 : 0), 1, 0, 'vboToggleCustomCheckinout();'); ?>
								</div>
							</div>
							<?php
							// get the default check-in/out times
							$opening_times = VikBooking::getTimeOpenStore();
							if (!$opening_times || !($opening_times[0] ?? 0) || !($opening_times[1] ?? 0)) {
								// default values
								$opening_times = [(12 * 3600), (10 * 3600)];
							}

							// calculate the default readable times
							$def_read_checkin_hours = floor($opening_times[0] / 3600);
							$def_read_checkin_minutes = floor(($opening_times[0] - ($def_read_checkin_hours * 3600)) / 60);
							$def_read_checkin = ($def_read_checkin_hours < 10 ? '0' : '') . $def_read_checkin_hours . ':' . ($def_read_checkin_minutes < 10 ? '0' : '') . $def_read_checkin_minutes;
							$def_read_checkout_hours = floor($opening_times[1] / 3600);
							$def_read_checkout_minutes = floor(($opening_times[1] - ($def_read_checkout_hours * 3600)) / 60);
							$def_read_checkout = ($def_read_checkout_hours < 10 ? '0' : '') . $def_read_checkout_hours . ':' . ($def_read_checkout_minutes < 10 ? '0' : '') . $def_read_checkout_minutes;

							// round the default times to HH:00 in seconds
							$opening_times = array_map(function($seconds) {
								if ($seconds < 3600) {
									return 0;
								}
								if (($seconds % 3600) > 0) {
									$hours = floor($seconds / 3600);
									return $hours * 3600;
								}
								return $seconds;
							}, $opening_times);

							// get the check-in/out times to set
							$set_times = [0, 0];
							if ($row && !empty($row['oparams']['custom_checkinout'])) {
								if (!empty($row['oparams']['set_checkin'])) {
									$set_times[0] = (int) $row['oparams']['set_checkin'];
								}
								if (!empty($row['oparams']['set_checkout'])) {
									$set_times[1] = (int) $row['oparams']['set_checkout'];
								}
							}
							?>
							<div class="vbo-param-container vbo-param-nested" data-type="custom_checkinout" style="<?php echo $row && !empty($row['oparams']['custom_checkinout']) ? '' : 'display: none;'; ?>">
								<div class="vbo-param-label"><?php echo JText::_('VBCONFIGONESEVEN'); ?></div>
								<div class="vbo-param-setting">
									<select name="set_checkin">
										<option value="">-----</option>
									<?php
									$set_checkin = $set_times[0] ?: $opening_times[0];
									for ($h = 0; $h <= 23; $h++) {
										for ($m = 0; $m < 60; $m+=15) {
											$read_time = ($h < 10 ? '0' : '') . $h . ':' . ($m < 10 ? '0' : '') . $m;
											$time_seconds = ($h * 3600) + ($m * 60);
											?>
										<option value="<?php echo $time_seconds; ?>"<?php echo $time_seconds == $set_checkin ? ' selected="selected"' : ''; ?>><?php echo $read_time; ?></option>
											<?php
										}
									}
									?>
									</select>
								</div>
							</div>
							<div class="vbo-param-container vbo-param-nested" data-type="custom_checkinout" style="<?php echo $row && !empty($row['oparams']['custom_checkinout']) ? '' : 'display: none;'; ?>">
								<div class="vbo-param-label"><?php echo JText::_('VBCONFIGONETHREE'); ?></div>
								<div class="vbo-param-setting">
									<select name="set_checkout">
										<option value="">-----</option>
									<?php
									$set_checkout = $set_times[1] ?: $opening_times[1];
									for ($h = 0; $h <= 23; $h++) {
										for ($m = 0; $m < 60; $m+=15) {
											$read_time = ($h < 10 ? '0' : '') . $h . ':' . ($m < 10 ? '0' : '') . $m;
											$time_seconds = ($h * 3600) + ($m * 60);
											?>
										<option value="<?php echo $time_seconds; ?>"<?php echo $time_seconds == $set_checkout ? ' selected="selected"' : ''; ?>><?php echo $read_time; ?></option>
											<?php
										}
									}
									?>
									</select>
									<span class="vbo-param-setting-comment"><?php echo JText::sprintf('VBO_CUSTOM_CHECKINOUT_WARN', $def_read_checkin, $def_read_checkout); ?></span>
								</div>
							</div>
						</div>

						<div class="vbo-param-container vbo-param-container-full">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWOPTTWO'); ?></div>
							<div class="vbo-param-setting">
								<?php
								if (interface_exists('Throwable')) {
									/**
									 * With PHP >= 7 supporting throwable exceptions for Fatal Errors
									 * we try to avoid issues with third party plugins that make use
									 * of the WP native function get_current_screen().
									 * 
									 * @wponly
									 */
									try {
										echo $editor->display( "optdescr", ($row ? $row['descr'] : ""), '100%', 300, 70, 20 );
									} catch (Throwable $t) {
										echo $t->getMessage() . ' in ' . $t->getFile() . ':' . $t->getLine() . '<br/>';
									}
								} else {
									// we cannot catch Fatal Errors in PHP 5.x
									echo $editor->display( "optdescr", ($row ? $row['descr'] : ""), '100%', 300, 70, 20 );
								}
								?>
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBOROOMSASSIGNED'); ?></div>
							<div class="vbo-param-setting">
								<span class="vbo-select-all"><?php echo JText::_('VBOSELECTALL'); ?></span>
								<select name="idrooms[]" multiple="multiple" id="idrooms">
								<?php
								foreach ($this->allrooms as $rid => $room) {
									$is_room_assigned = ($row && is_array($room['idopt']) && in_array((string)$row['id'], $room['idopt']));
									?>
									<option value="<?php echo $rid; ?>"<?php echo $is_room_assigned ? ' selected="selected"' : ''; ?>><?php echo $room['name']; ?></option>
									<?php
								}
								?>
								</select>
							</div>
						</div>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
	<input type="hidden" name="task" value="">
<?php
if ($row) {
	?>
	<input type="hidden" name="whereup" value="<?php echo $row['id']; ?>">
	<?php
}
?>
	<input type="hidden" name="option" value="com_vikbooking">
	<?php echo JHtml::_('form.token'); ?>
</form>

<script type="text/javascript">
if (document.adminForm.ifchildren.checked == true && jQuery('#myDiv').find('input').length > 0) {
	jQuery('#optperpersontr').hide();
	jQuery('#opthmanytr').hide();
	jQuery('#forceseltr').hide();
	jQuery('.vbo-forceval-param').hide();
}

function vboToggleCustomCheckinout() {
	let enabled = document.querySelector('input[name="custom_checkinout"]').checked;
	document.querySelectorAll('[data-type="custom_checkinout"]').forEach((elem) => {
		elem.style.display = enabled ? '' : 'none';
	});
}

function vboToggleDamageDepSettings() {
	let enabled = document.querySelector('input[name="damagedep"]').checked;
	document.querySelectorAll('[data-type="damagedep_settings"]').forEach((elem) => {
		elem.style.display = enabled ? '' : 'none';
	});
	if (!enabled) {
		document.querySelector('select[name="damagedep_settings[paywhen]"]').value = 0;
	}
	vboToggleDamageDepPayment();
}

function vboToggleDamageDepPayment() {
	let paysep = document.querySelector('select[name="damagedep_settings[paywhen]"]').value;
	document.querySelectorAll('[data-type="damagedep_payment"]').forEach((elem) => {
		elem.style.display = paysep == 1 ? '' : 'none';
	});
}
</script>
