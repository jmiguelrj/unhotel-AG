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

JHtml::_('jquery.framework', true, true);
JHtml::_('script', VBO_SITE_URI.'resources/jquery-ui.sortable.min.js');

$config = VBOFactory::getConfig();

$vbo_app = VikBooking::getVboApplication();
$timeopst = VikBooking::getTimeOpenStore(true);
if (is_array($timeopst)) {
	$openat = VikBooking::getHoursMinutes($timeopst[0]);
	$closeat = VikBooking::getHoursMinutes($timeopst[1]);
} else {
	$openat = array(0, 0);
	$closeat = array(0, 0);
}
$wcheckintime = "<select name=\"timeopenstorefh\">\n";
for ($i = 0; $i <= 23; $i++) {
	if ($i < 10) {
		$in = "0".$i;
	} else {
		$in = $i;
	}
	$stat = $openat[0] == $i ? " selected=\"selected\"" : "";
	$wcheckintime .= "<option value=\"".$i."\"".$stat.">".$in."</option>\n";
}
$wcheckintime .= "</select> <select name=\"timeopenstorefm\">\n";
for ($i = 0; $i <= 59; $i++) {
	if ($i < 10) {
		$in = "0".$i;
	} else {
		$in = $i;
	}
	$stat = $openat[1] == $i ? " selected=\"selected\"" : "";
	$wcheckintime .= "<option value=\"".$i."\"".$stat.">".$in."</option>\n";
}
$wcheckintime .= "</select>\n";
$wcheckouttime = "<select name=\"timeopenstoreth\">\n";
for ($i = 0; $i <= 23; $i++) {
	if ($i < 10) {
		$in = "0".$i;
	} else {
		$in = $i;
	}
	$stat = $closeat[0]==$i ? " selected=\"selected\"" : "";
	$wcheckouttime .= "<option value=\"".$i."\"".$stat.">".$in."</option>\n";
}
$wcheckouttime .= "</select> <select name=\"timeopenstoretm\">\n";
for ($i = 0; $i <= 59; $i++) {
	if ($i < 10) {
		$in = "0".$i;
	} else {
		$in = $i;
	}
	$stat = $closeat[1] == $i ? " selected=\"selected\"" : "";
	$wcheckouttime .= "<option value=\"".$i."\"".$stat.">".$in."</option>\n";
}
$wcheckouttime .= "</select>\n";

$calendartype = VikBooking::calendarType(true);

$globnumadults = VikBooking::getSearchNumAdults(true);
$adultsparts = explode('-', $globnumadults);
$globnumchildren = VikBooking::getSearchNumChildren(true);
$childrenparts = explode('-', $globnumchildren);

$maxdatefuture = VikBooking::getMaxDateFuture();
$maxdate_val = intval(substr($maxdatefuture, 1, (strlen($maxdatefuture) - 1)));
$maxdate_interval = substr($maxdatefuture, -1, 1);

$smartseach_type = VikBooking::getSmartSearchType(true);

$vbosef = is_file(VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'router.php');

$vcm_autoupd  		= (int)VikBooking::vcmAutoUpdate();
$chat_enabled 		= (int)VikBooking::chatEnabled();
$precheckin_enabled = (int)VikBooking::precheckinEnabled();
$upselling_enabled  = (int)VikBooking::upsellingEnabled();

$nowdf = VikBooking::getDateFormat(true);
if ($nowdf == "%d/%m/%Y") {
	$usedf = 'd/m/Y';
} elseif ($nowdf == "%m/%d/%Y") {
	$usedf = 'm/d/Y';
} else {
	$usedf = 'Y/m/d';
}
$datesep = VikBooking::getDateSeparator(true);
?>
<script type="text/javascript">

function vboRemoveElement(el) {
	return (elem=document.getElementById(el)).parentNode.removeChild(elem);
}

function vboAddClosingDate() {
	var cdfrom = document.getElementById('cdfrom').value;
	var cdto = document.getElementById('cdto').value;
	if (cdfrom.length && cdto.length) {
		var cdcounter = document.getElementsByClassName('vbo-closed-date-entry').length + 1;
		var cdstring = "<div class=\"vbo-closed-date-entry\" id=\"vbo-closed-date-entry"+cdcounter+"\"><span>"+cdfrom+"</span> - <span>"+cdto+"</span> <span class=\"vbo-closed-date-rm\" onclick=\"vboRemoveElement('vbo-closed-date-entry"+cdcounter+"');\"><i class=\"vboicn-cross\"></i> </span><input type=\"hidden\" name=\"cdsfrom[]\" value=\""+cdfrom+"\" /><input type=\"hidden\" name=\"cdsto[]\" value=\""+cdto+"\" /></div>";
		document.getElementById('vbo-config-closed-dates').innerHTML += cdstring;
		document.getElementById('cdfrom').value = '';
		document.getElementById('cdto').value = '';
	}
}

function vboChangeMultiRoomSearch(type) {
	if (type == 'classic') {
		jQuery('.vbo-param-container[data-vbosearchtpl="classic"]').show();
	} else {
		jQuery('.vbo-param-container[data-vbosearchtpl="classic"]').hide();
	}
}

var chatenabled = <?php echo $chat_enabled; ?>;
var precheckinenabled = <?php echo $precheckin_enabled; ?>;

jQuery(function() {

	jQuery('input[name="chatenabled"]').change(function() {
		if (chatenabled < 0) {
			jQuery('#chat-params-tr').hide();
			return;
		}
		if (jQuery(this).is(':checked') && parseInt(jQuery(this).val()) > 0) {
			jQuery('#chat-params-tr').fadeIn();
		} else {
			jQuery('#chat-params-tr').fadeOut();
		}
	});

	jQuery('input[name="precheckinenabled"]').change(function() {
		if (precheckinenabled < 0) {
			jQuery('.precheckin-params-tr').hide();
			return;
		}
		if (jQuery(this).is(':checked') && parseInt(jQuery(this).val()) > 0) {
			jQuery('.precheckin-params-tr').fadeIn();
		} else {
			jQuery('.precheckin-params-tr').fadeOut();
		}
	});

});

</script>

<div class="vbo-config-maintab-left">
	<fieldset class="adminform">
		<div class="vbo-params-wrap">
			<legend class="adminlegend"><?php echo JText::_('VBOCPARAMBOOKING'); ?></legend>
			<div class="vbo-params-container">
				<div class="vbo-param-container">
					<div class="vbo-param-label">
						<?php echo $vbo_app->createPopover(array('title' => JText::_('VBCONFIGVCMAUTOUPD'), 'content' => JText::_('VBCONFIGVCMAUTOUPDHELP'), 'icon_class' => 'vboicn-lifebuoy')); ?>
						<?php echo JText::_('VBCONFIGVCMAUTOUPD'); ?>
					</div>
					<div class="vbo-param-setting"><?php echo $vbo_app->printYesNoButtons('vcmautoupd', JText::_('VBYES'), JText::_('VBNO'), ($vcm_autoupd < 0 ? 0 : $vcm_autoupd), 1, 0).($vcm_autoupd < 0 ? '<span class="vbo-config-warn">'.JText::_('VBCONFIGVCMAUTOUPDMISS').'</span>' : ''); ?></div>
				</div>
				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo JText::_('VBCONFIGONEFIVE'); ?></div>
					<div class="vbo-param-setting"><?php echo $vbo_app->printYesNoButtons('allowbooking', JText::_('VBYES'), JText::_('VBNO'), (int)VikBooking::allowBooking(), 1, 0); ?></div>
				</div>
				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo JText::_('VBCONFIGONESIX'); ?></div>
					<div class="vbo-param-setting"><textarea name="disabledbookingmsg" rows="5" cols="50"><?php echo JHtml::_('esc_textarea', VikBooking::getDisabledBookingMsg()); ?></textarea></div>
				</div>
				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo JText::_('VBCONFIGONETENSIX'); ?></div>
					<div class="vbo-param-setting"><input type="text" name="adminemail" value="<?php echo JHtml::_('esc_attr', VikBooking::getAdminMail()); ?>" size="35"/></div>
				</div>
				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo JText::_('VBSENDEREMAIL'); ?></div>
					<div class="vbo-param-setting"><input type="text" name="senderemail" value="<?php echo JHtml::_('esc_attr', VikBooking::getSenderMail()); ?>" size="35"/></div>
				</div>
				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo JText::_('VBCONFIGONESEVEN'); ?></div>
					<div class="vbo-param-setting"><?php echo $wcheckintime; ?></div>
				</div>
				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo JText::_('VBCONFIGONETHREE'); ?></div>
					<div class="vbo-param-setting"><?php echo $wcheckouttime; ?></div>
				</div>
				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo JText::_('VBCONFIGONEELEVEN'); ?></div>
					<div class="vbo-param-setting">
						<select name="dateformat">
							<option value="%d/%m/%Y"<?php echo ($nowdf=="%d/%m/%Y" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBCONFIGONETWELVE'); ?></option>
							<option value="%m/%d/%Y"<?php echo ($nowdf=="%m/%d/%Y" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBCONFIGONEMDY'); ?></option>
							<option value="%Y/%m/%d"<?php echo ($nowdf=="%Y/%m/%d" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBCONFIGONETENTHREE'); ?></option>
						</select>
					</div>
				</div>
				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo JText::_('VBCONFIGDATESEP'); ?></div>
					<div class="vbo-param-setting"><input type="text" name="datesep" value="<?php echo JHtml::_('esc_attr', $datesep); ?>" size="3"/></div>
				</div>
				<?php
				$resmodcanc = VikBooking::getReservationModCanc();
				$resmodcancmin = VikBooking::getReservationModCancMin();
				?>
				<div class="vbo-param-container">
					<div class="vbo-param-label">
						<?php echo $vbo_app->createPopover(array('title' => JText::_('VBOCONFIGALLOWMODCANC'), 'content' => JText::_('VBOCONFIGALLOWMODCANCHELP'))); ?>
						<?php echo JText::_('VBOCONFIGALLOWMODCANC'); ?>
					</div>
					<div class="vbo-param-setting">
						<script type="text/javascript">
						function vboChangeResModCanc(mode) {
							mode = parseInt(mode);
							document.getElementById('vbo-resmodcanc-lim').style.display = (mode > 0 ? 'flex' : 'none');
						}
						</script>
						<div class="vbo-resmodcanc-block">
							<select name="resmodcanc" onchange="vboChangeResModCanc(this.value);">
								<option value="0"<?php echo $resmodcanc == 0 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBOCONFIGMODCANC0'); ?></option>
								<option value="1"<?php echo $resmodcanc == 1 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBOCONFIGMODCANC1'); ?></option>
								<option value="2"<?php echo $resmodcanc == 2 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBOCONFIGMODCANC2'); ?></option>
								<option value="3"<?php echo $resmodcanc == 3 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBOCONFIGMODCANC3'); ?></option>
								<option value="4"<?php echo $resmodcanc == 4 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBOCONFIGMODCANC4'); ?></option>
							</select>
						</div>
					</div>
				</div>
				<div class="vbo-param-container vbo-resmodcanc-lim vbo-param-nested" id="vbo-resmodcanc-lim" style="display: <?php echo $resmodcanc > 0 ? 'flex' : 'none'; ?>;">
					<div class="vbo-param-label"><label for="resmodcancmin"><?php echo JText::_('VBOCONFIGMODCANCMINDAYS'); ?></label></div>
					<div class="vbo-param-setting"><input type="number" min="0" name="resmodcancmin" id="resmodcancmin" style="margin: 0;" value="<?php echo $resmodcancmin; ?>" /></div>
				</div>
				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo JText::_('VBCONFIGTODAYBOOKINGS'); ?></div>
					<div class="vbo-param-setting"><?php echo $vbo_app->printYesNoButtons('todaybookings', JText::_('VBYES'), JText::_('VBNO'), (int)VikBooking::todayBookings(), 1, 0); ?></div>
				</div>
				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo JText::_('VBCONFIGONECOUPONS'); ?></div>
					<div class="vbo-param-setting"><?php echo $vbo_app->printYesNoButtons('enablecoupons', JText::_('VBYES'), JText::_('VBNO'), (int)VikBooking::couponsEnabled(), 1, 0); ?></div>
				</div>
				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo JText::_('VBCONFIGENABLECUSTOMERPIN'); ?></div>
					<div class="vbo-param-setting"><?php echo $vbo_app->printYesNoButtons('enablepin', JText::_('VBYES'), JText::_('VBNO'), (int)VikBooking::customersPinEnabled(), 1, 0); ?></div>
				</div>
				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo JText::_('VBCONFIGONETENFIVE'); ?></div>
					<div class="vbo-param-setting"><?php echo $vbo_app->printYesNoButtons('tokenform', JText::_('VBYES'), JText::_('VBNO'), (VikBooking::tokenForm() ? 'yes' : 0), 'yes', 0); ?></div>
				</div>
				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo JText::_('VBCONFIGREQUIRELOGIN'); ?></div>
					<div class="vbo-param-setting"><?php echo $vbo_app->printYesNoButtons('requirelogin', JText::_('VBYES'), JText::_('VBNO'), (int)VikBooking::requireLogin(), 1, 0); ?></div>
				</div>
				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo $vbo_app->createPopover(array('title' => JText::_('VBCONFIGAUTODISTFEATURE'), 'content' => JText::_('VBCONFIGAUTODISTFEATUREHELP'))); ?> <?php echo JText::_('VBCONFIGAUTODISTFEATURE'); ?></div>
					<div class="vbo-param-setting"><?php echo $vbo_app->printYesNoButtons('autoroomunit', JText::_('VBYES'), JText::_('VBNO'), (VikBooking::autoRoomUnit() ? 1 : 0), 1, 0); ?></div>
				</div>
				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo $vbo_app->createPopover(array('title' => JText::_('VBCONFIGCHATENABLED'), 'content' => JText::_('VBCONFIGCHATENABLEDHELP'))); ?> <?php echo JText::_('VBCONFIGCHATENABLED'); ?></div>
					<div class="vbo-param-setting"><?php echo $vbo_app->printYesNoButtons('chatenabled', JText::_('VBYES'), JText::_('VBNO'), ($chat_enabled < 0 ? 0 : $chat_enabled), 1, 0).($chat_enabled < 0 ? '<span class="vbo-config-warn">'.JText::_('VBCONFIGVCMAUTOUPDMISS').'</span>' : ''); ?></div>
				</div>
				<?php
				$chat_params = VikBooking::getChatParams();
				?>
				<div class="vbo-param-container vbo-param-nested" id="chat-params-tr" style="display: <?php echo $chat_enabled > 0 ? 'flex' : 'none'; ?>;">
					<div class="vbo-param-label"><?php echo JText::_('VBCONFIGCHATPARAMS'); ?></div>
					<div class="vbo-param-setting">
						<select name="chat_res_status" style="max-width: 345px; margin: 0;">
							<option value="confirmed;standby;cancelled"<?php echo isset($chat_params->res_status) && count($chat_params->res_status) >= 3 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBCONFIGCHATRESSTATALL'); ?></option>
							<option value="confirmed"<?php echo isset($chat_params->res_status) && count($chat_params->res_status) === 1 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBCONFIGCHATRESSTATCONF'); ?></option>
						</select>
						<div style="margin-top: 5px;">
							<select name="chat_av_type" style="max-width: 345px; margin: 0;">
								<option value="checkin"<?php echo isset($chat_params->av_type) && $chat_params->av_type == 'checkin' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBCONFIGCHATAVCHECKIN'); ?></option>
								<option value="checkout"<?php echo isset($chat_params->av_type) && $chat_params->av_type == 'checkout' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBCONFIGCHATAVCHECKOUT'); ?></option>
							</select>
							<input type="number" name="chat_av_days" value="<?php echo isset($chat_params->av_days) ? (int)$chat_params->av_days : '0'; ?>" style="margin: 0;"/>
						</div>
					</div>
				</div>
				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo JText::_('VBCONFIGPRECHECKINENABLED'); ?></div>
					<div class="vbo-param-setting"><?php echo $vbo_app->printYesNoButtons('precheckinenabled', JText::_('VBYES'), JText::_('VBNO'), $precheckin_enabled, 1, 0); ?></div>
				</div>
				<div class="vbo-param-container vbo-param-nested precheckin-params-tr" style="display: <?php echo $precheckin_enabled > 0 ? 'flex' : 'none'; ?>;">
					<div class="vbo-param-label"><?php echo JText::_('VBCONFIGPRECHECKINMIND'); ?></div>
					<div class="vbo-param-setting"><input type="number" name="precheckinminoffset" value="<?php echo VikBooking::precheckinMinOffset(); ?>" min="-7" max="365" /></div>
				</div>
				<div class="vbo-param-container vbo-param-nested precheckin-params-tr" style="display: <?php echo $precheckin_enabled > 0 ? 'flex' : 'none'; ?>;">
					<div class="vbo-param-label"><?php echo JText::_('VBO_CONF_CHECKIN_DATA'); ?></div>
					<div class="vbo-param-setting">
						<?php
						$checkin_data = $config->getString('checkindata', 'basic');
						?>
						<select name="checkindata">
							<option value="basic"<?php echo $checkin_data == 'basic' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBO_CONF_CHECKIN_DATA_BASIC'); ?></option>
						<?php
						foreach (VBOCheckinPax::getDrivers() as $checkin_driver) {
							$cust_driver_obj  = VBOCheckinPax::getInstance($checkin_driver);
							$cust_driver_name = $cust_driver_obj !== null ? $cust_driver_obj->getName() : '???';
							?>
							<option value="<?php echo $checkin_driver; ?>" <?php echo $checkin_data == $checkin_driver ? ' selected="selected"' : ''; ?>><?php echo JText::sprintf('VBO_CONF_CHECKIN_DATA_CUSTOM', $cust_driver_name); ?></option>
							<?php
						}
						?>
						</select>
					</div>
				</div>
				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo $vbo_app->createPopover(array('title' => JText::_('VBCONFIGUPSELLINGENABLED'), 'content' => JText::_('VBCONFIGUPSELLINGENABLEDHELP'))); ?> <?php echo JText::_('VBCONFIGUPSELLINGENABLED'); ?></div>
					<div class="vbo-param-setting"><?php echo $vbo_app->printYesNoButtons('upsellingenabled', JText::_('VBYES'), JText::_('VBNO'), $upselling_enabled, 1, 0); ?></div>
				</div>
				<?php
				$orphans_calcm = VikBooking::orphansCalculation();
				?>
				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo $vbo_app->createPopover(array('title' => JText::_('VBOCONFIGORPHANSCALCM'), 'content' => JText::_('VBOCONFIGORPHANSCALCMHELP'))); ?> <?php echo JText::_('VBOCONFIGORPHANSCALCM'); ?></div>
					<div class="vbo-param-setting">
						<select name="orphanscal">
							<option value="next"<?php echo $orphans_calcm == 'next' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBOCONFIGORPHANSCALCMN'); ?></option>
							<option value="prevnext"<?php echo $orphans_calcm == 'prevnext' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBOCONFIGORPHANSCALCMPN'); ?></option>
						</select>
					</div>
				</div>
			</div>
		</div>
	</fieldset>
</div>

<div class="vbo-config-maintab-right">

	<fieldset class="adminform">
		<div class="vbo-params-wrap">
			<legend class="adminlegend"><?php echo JText::_('VBCONFIGSEARCHPARAMS'); ?></legend>
			<div class="vbo-params-container">
				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo JText::_('VBCONFIGMINDAYSADVANCE'); ?></div>
					<?php
					/**
					 * Do not ever change the argument "true" from VikBooking::getMinDaysAdvance(true); as it is
					 * now used to detect that the back-end is calling it, and no closing dates should be applied.
					 * 
					 * @since 	1.14 (J) - 1.4.0 (WP)
					 */
					?>
					<div class="vbo-param-setting"><input type="number" name="mindaysadvance" value="<?php echo VikBooking::getMinDaysAdvance(true); ?>" min="0"/></div>
				</div>
				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo JText::_('VBCONFIGSEARCHDEFNIGHTS'); ?></div>
					<div class="vbo-param-setting"><input type="number" name="autodefcalnights" value="<?php echo VikBooking::getDefaultNightsCalendar(); ?>" min="0"/></div>
				</div>
				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo JText::_('VBCONFIGSEARCHPNUMROOM'); ?></div>
					<div class="vbo-param-setting"><input type="number" name="numrooms" value="<?php echo VikBooking::getSearchNumRooms(true); ?>" min="0"/></div>
				</div>
				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo JText::_('VBCONFIGSEARCHPNUMADULTS'); ?></div>
					<div class="vbo-param-setting"><?php echo JText::_('VBCONFIGSEARCHPFROM'); ?> <input type="number" name="numadultsfrom" value="<?php echo $adultsparts[0]; ?>" min="0"/> &nbsp;&nbsp; <?php echo JText::_('VBCONFIGSEARCHPTO'); ?> <input type="number" name="numadultsto" value="<?php echo $adultsparts[1]; ?>" min="0"/></div>
				</div>
				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo JText::_('VBCONFIGSEARCHPNUMCHILDREN'); ?></div>
					<div class="vbo-param-setting"><?php echo JText::_('VBCONFIGSEARCHPFROM'); ?> <input type="number" name="numchildrenfrom" value="<?php echo $childrenparts[0]; ?>" min="0"/> &nbsp;&nbsp; <?php echo JText::_('VBCONFIGSEARCHPTO'); ?> <input type="number" name="numchildrento" value="<?php echo $childrenparts[1]; ?>" min="0"/></div>
				</div>
				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo JText::_('VBCONFIGSEARCHPMAXDATEFUT'); ?></div>
					<div class="vbo-param-setting"><input type="number" name="maxdate" value="<?php echo $maxdate_val; ?>" min="0"/> <select name="maxdateinterval"><option value="d"<?php echo $maxdate_interval == 'd' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBCONFIGSEARCHPMAXDATEDAYS'); ?></option><option value="w"<?php echo $maxdate_interval == 'w' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBCONFIGSEARCHPMAXDATEWEEKS'); ?></option><option value="m"<?php echo $maxdate_interval == 'm' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBCONFIGSEARCHPMAXDATEMONTHS'); ?></option><option value="y"<?php echo $maxdate_interval == 'y' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBCONFIGSEARCHPMAXDATEYEARS'); ?></option></select></div>
				</div>

				<div class="vbo-param-container">
					<div class="vbo-param-label">
						<?php echo $vbo_app->createPopover(array('title' => JText::_('VBCONFIGCLOSINGDATES'), 'content' => JText::_('VBCONFIGCLOSINGDATESHELP'))); ?>
						<?php echo JText::_('VBCONFIGCLOSINGDATES'); ?>
					</div>
					<div class="vbo-param-setting">
						<div style="width: 100%; display: inline-block;" class="btn-toolbar" id="filter-bar">
							<div class="btn-group pull-left">
								<?php echo $vbo_app->getCalendar('', 'cdfrom', 'cdfrom', $nowdf, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true', 'placeholder' => JText::_('VBCONFIGCLOSINGDATEFROM'))); ?>
							</div>
							<div class="btn-group pull-left">
								<?php echo $vbo_app->getCalendar('', 'cdto', 'cdto', $nowdf, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true', 'placeholder' => JText::_('VBCONFIGCLOSINGDATETO'))); ?>
							</div>
							<div class="btn-group pull-left">
								<button type="button" class="btn vbo-config-btn" onclick="vboAddClosingDate();"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::_('VBCONFIGCLOSINGDATEADD'); ?></button>
							</div>
						</div>
						<div id="vbo-config-closed-dates" style="display: block;">
					<?php
					$cur_closed_dates = VikBooking::getClosingDates();
					if (is_array($cur_closed_dates) && count($cur_closed_dates)) {
						foreach ($cur_closed_dates as $kcd => $vcd) {
							echo "<div class=\"vbo-closed-date-entry\" id=\"vbo-closed-date-entry".$kcd."\"><span>".date(str_replace("/", $datesep, $usedf), $vcd['from'])."</span> - <span>".date(str_replace("/", $datesep, $usedf), $vcd['to'])."</span> <span class=\"vbo-closed-date-rm\" onclick=\"vboRemoveElement('vbo-closed-date-entry".$kcd."');\"><i class=\"vboicn-cross\"></i> </span><input type=\"hidden\" name=\"cdsfrom[]\" value=\"".date($usedf, $vcd['from'])."\" /><input type=\"hidden\" name=\"cdsto[]\" value=\"".date($usedf, $vcd['to'])."\" /></div>"."\n";
						}
					}
					?>
						</div>
					</div>
				</div>

				<?php
				/**
				 * Choose the template file for the View "search". If compact, the "smartsearch" parameter
				 * will be ignored and set to hidden, because this new template does not use it.
				 * 
				 * @since 	1.13
				 */
				$search_tpl = VikBooking::searchResultsTmpl();
				?>
				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo JText::_('VBOCONFIGSEARCHRESTPL'); ?></div>
					<div class="vbo-param-setting">
						<select name="srcrtpl" onchange="vboChangeMultiRoomSearch(this.value);">
							<option value="compact"<?php echo $search_tpl == 'compact' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBOCONFIGSEARCHRESTPLCM'); ?></option>
							<option value="classic"<?php echo $search_tpl == 'classic' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBOCONFIGSEARCHRESTPLCL'); ?></option>
						</select>
					</div>
				</div>
				<div class="vbo-param-container vbo-param-nested" data-vbosearchtpl="classic" style="<?php echo $search_tpl != 'classic' ? 'display: none;' : ''; ?>">
					<div class="vbo-param-label"><?php echo JText::_('VBCONFIGSEARCHPSMARTSEARCH'); ?></div>
					<div class="vbo-param-setting">
						<select name="smartsearch">
							<option value="dynamic"<?php echo $smartseach_type == 'dynamic' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBCONFIGSEARCHPSMARTSEARCHDYN'); ?></option>
							<option value="automatic"<?php echo $smartseach_type == 'automatic' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBCONFIGSEARCHPSMARTSEARCHAUTO'); ?></option>
						</select>
					</div>
				</div>
				<div class="vbo-param-container vbo-param-nested" data-vbosearchtpl="classic" style="<?php echo $search_tpl != 'classic' ? 'display: none;' : ''; ?>">
					<div class="vbo-param-label">
						<?php echo $vbo_app->createPopover(array('title' => JText::_('VBO_INTERACTIVE_MAP_BOOK'), 'content' => JText::_('VBO_INTERACTIVE_MAP_BOOK_HELP'))); ?>
						<?php echo JText::_('VBO_INTERACTIVE_MAP_BOOK'); ?>
					</div>
					<div class="vbo-param-setting">
						<?php echo $vbo_app->printYesNoButtons('interactive_map', JText::_('VBYES'), JText::_('VBNO'), (int)VikBooking::interactiveMapEnabled(), 1, 0); ?>
					</div>
				</div>
				<div class="vbo-param-container vbo-param-nested" data-vbosearchtpl="classic" style="<?php echo $search_tpl != 'classic' ? 'display: none;' : ''; ?>">
					<div class="vbo-param-label">
						<?php echo JText::_('VBO_SEARCH_FILTERS'); ?>
					</div>
					<div class="vbo-param-setting">
						<?php echo $vbo_app->printYesNoButtons('search_filters', JText::_('VBYES'), JText::_('VBNO'), (int) $config->getBool('search_filters', false), 1, 0); ?>
					</div>
				</div>
				<?php
				$searchsugg = (int)VikBooking::showSearchSuggestions();
				?>
				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo JText::_('VBOCONFIGSHOWSEARCHSUGG'); ?></div>
					<div class="vbo-param-setting">
						<select name="searchsuggestions">
							<option value="1"<?php echo $searchsugg == 1 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBOYESWITHAVAILABILITY'); ?></option>
							<option value="2"<?php echo $searchsugg == 2 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBOYESNOAVAILABILITY'); ?></option>
							<option value="0"<?php echo $searchsugg == 0 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBNO'); ?></option>
						</select>
					</div>
				</div>
				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo JText::_('VBO_SEARCH_SHOW_BUSY_LISTINGS'); ?></div>
					<div class="vbo-param-setting"><?php echo $vbo_app->printYesNoButtons('search_show_busy_listings', JText::_('VBYES'), JText::_('VBNO'), ($config->getBool('search_show_busy_listings', false) ? 1 : 0), 1, 0); ?></div>
				</div>
				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo JText::_('VBO_SEARCH_LINK_ROOMDETAILS'); ?></div>
					<div class="vbo-param-setting"><?php echo $vbo_app->printYesNoButtons('search_link_roomdetails', JText::_('VBYES'), JText::_('VBNO'), ($config->getBool('search_link_roomdetails', false) ? 1 : 0), 1, 0); ?></div>
				</div>
				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo JText::_('VBCONFIGONETENFOUR'); ?></div>
					<div class="vbo-param-setting"><?php echo $vbo_app->printYesNoButtons('showcategories', JText::_('VBYES'), JText::_('VBNO'), (VikBooking::showCategoriesFront(true) ? 'yes' : 0), 'yes', 0); ?></div>
				</div>
				<?php
				$showchildren = VikBooking::showChildrenFront(true);
				$guests_label = $config->getString('guests_label', 'adults');
				?>
				<div class="vbo-param-container">
					<div class="vbo-param-label">
						<?php echo $vbo_app->createPopover(array('title' => JText::_('VBCONFIGSHOWCHILDREN'), 'content' => JText::_('VBO_CHOOSE_CHILDREN_HELP'))); ?>
						<?php echo JText::_('VBCONFIGSHOWCHILDREN'); ?>
					</div>
					<div class="vbo-param-setting">
						<?php echo $vbo_app->printYesNoButtons('showchildren', JText::_('VBYES'), JText::_('VBNO'), ($showchildren ? 'yes' : 0), 'yes', 0, 'vboToggleShowChildren(this.checked);'); ?>
					</div>
				</div>
				<div class="vbo-param-container vbo-param-nested" data-config-type="showchildren" style="<?php echo $showchildren ? 'display: none;' : ''; ?>">
					<div class="vbo-param-label"><?php echo JText::_('VBO_GUESTS_LABEL'); ?></div>
					<div class="vbo-param-setting">
						<select name="guests_label">
							<option value="adults"<?php echo $guests_label == 'adults' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBEDITORDERADULTS'); ?></option>
							<option value="guests"<?php echo $guests_label == 'guests' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBPVIEWORDERSPEOPLE'); ?></option>
						</select>
						<span class="vbo-param-setting-comment"><?php echo JText::_('VBO_GUESTS_LABEL_HELP'); ?></span>
					</div>
				</div>
				<div class="vbo-param-container vbo-param-nested" data-config-type="showchildren" style="<?php echo $showchildren ? 'display: none;' : ''; ?>">
					<div class="vbo-param-label"><?php echo JText::_('VBO_GUESTS_POLICY'); ?></div>
					<div class="vbo-param-setting">
						<textarea name="guests_allowed_policy" rows="5" cols="50"><?php echo JHtml::_('esc_textarea', VikBooking::getGuestsAllowedPolicy()); ?></textarea>
						<span class="vbo-param-setting-comment"><?php echo JText::_('VBO_GUESTS_POLICY_HELP'); ?></span>
					</div>
				</div>
				<div class="vbo-param-container">
					<div class="vbo-param-label">
						<?php echo $vbo_app->createPopover(array('title' => JText::_('VBO_BOOK_SPLIT_STAYS'), 'content' => JText::_('VBO_BOOK_SPLIT_STAYS_HELP'))); ?>
						<?php echo JText::_('VBO_BOOK_SPLIT_STAYS'); ?>
					</div>
					<div class="vbo-param-setting">
						<?php
						JText::script('VBO_SPLIT_STAY_RATIO_TEST');
						$split_stay_ratio = $config->getFloat('split_stay_ratio', 50);
						echo $vbo_app->printYesNoButtons('split_stay', JText::_('VBYES'), JText::_('VBNO'), (int)($split_stay_ratio > 0), 1, 0, 'vboToggleSplitStay(this.checked);');
						?>
					</div>
				</div>
				<div class="vbo-param-container vbo-param-nested" data-config-type="split-stay" style="<?php echo $split_stay_ratio < 1 ? 'display: none;' : ''; ?>">
					<div class="vbo-param-label">
						<?php echo $vbo_app->createPopover(array('title' => JText::_('VBO_SPLIT_STAYS_RATIO'), 'content' => JText::_('VBO_SPLIT_STAYS_RATIO_HELP'))); ?>
						<?php echo JText::_('VBO_SPLIT_STAYS_RATIO'); ?>
					</div>
					<div class="vbo-param-setting">
						<div class="input-append">
							<input type="number" min="0" max="100" step="5" name="split_stay_ratio" value="<?php echo $split_stay_ratio > 0 ? $split_stay_ratio : '50';?>" />
							<button type="button" class="btn">%</button>
						</div>
					</div>
				</div>
				<div class="vbo-param-container vbo-param-nested" data-config-type="split-stay" style="<?php echo $split_stay_ratio < 1 ? 'display: none;' : ''; ?>">
					<div class="vbo-param-label"><?php echo JText::_('VBOCRONSMSREMPARAMTEST'); ?></div>
					<div class="vbo-param-setting">
						<div class="input-append">
							<input type="number" id="vbo-test-splitstay-nights" min="2" max="365" value="<?php echo rand(4, 10); ?>" />
							<button type="button" class="btn"><?php echo JText::_('VBDAYS'); ?></button>
							<button type="button" class="btn vbo-config-btn vbo-btn-dont-append" onclick="vboTestSplitStay();"><?php echo JText::_('VBRATESOVWRATESCALCULATORCALC'); ?></button>
						</div>
						<div class="vbo-param-setting-comment" data-config-type="split-stay"></div>
					</div>
				</div>

				<script type="text/javascript">
					function vboToggleShowChildren(enabled) {
						if (enabled) {
							jQuery('.vbo-param-container[data-config-type="showchildren"]').hide();
						} else {
							jQuery('.vbo-param-container[data-config-type="showchildren"]').show();
						}
					}

					function vboToggleSplitStay(active) {
						if (active) {
							jQuery('.vbo-param-container[data-config-type="split-stay"]').show();
						} else {
							jQuery('.vbo-param-container[data-config-type="split-stay"]').hide();
							jQuery('.vbo-param-setting-comment[data-config-type="split-stay"]').text('');
						}
					}

					function vboTestSplitStay() {
						var nights = jQuery('#vbo-test-splitstay-nights').val() * 1;
						nights = nights < 2 ? 2 : nights;

						var ratio = jQuery('input[name="split_stay_ratio"]').val() * 1;
						ratio = ratio < 1 ? 1 : ratio;
						ratio = ratio > 100 ? 100 : ratio;

						var transfers = Math.round(nights * ratio / 100);

						// According to the current ratio (%s), a stay for %d night(s) will allow up to %d room transfer(s).
						var help_text = Joomla.JText._('VBO_SPLIT_STAY_RATIO_TEST');
						help_text = help_text.replace('%s', ratio + '%');
						help_text = help_text.replace('%d', nights);
						help_text = help_text.replace('%d', transfers);

						jQuery('.vbo-param-setting-comment[data-config-type="split-stay"]').text(help_text);
					}
				</script>

			</div>
		</div>
	</fieldset>

	<fieldset class="adminform">
		<div class="vbo-params-wrap">
			<legend class="adminlegend"><?php echo JText::_('VBOCPARAMSYSTEM'); ?></legend>
			<div class="vbo-params-container">

				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo JText::_('VBO_APPEARANCE_PREF'); ?></div>
					<div class="vbo-param-setting">
					<?php
					// prepare args for the multi-state toggle switch
					$appearance_vals = array(
						'light',
						'auto',
						'dark',
					);
					$appearance_lbls = array(
						array(
							'value' => '<i class="' . VikBookingIcons::i('sun') . '"></i>',
							'title' => JText::_('VBO_APPEARANCE_PREF_LIGHT'),
						),
						array(
							'value' => '<i class="' . VikBookingIcons::i('magic') . '"></i>',
							'title' => JText::_('VBO_APPEARANCE_PREF_AUTO'),
						),
						array(
							'value' => '<i class="' . VikBookingIcons::i('moon') . '"></i>',
							'title' => JText::_('VBO_APPEARANCE_PREF_DARK'),
						),
					);
					$appearance_attrs = array(
						array(
							'label_class' => 'vik-multiswitch-radiobtn-light',
							'input' 	  => array(
								'onchange' => 'vboPreviewAppeareance(this.value)',
							),
						),
						array(
							'label_class' => 'vik-multiswitch-radiobtn-auto',
							'input' 	  => array(
								'onchange' => 'vboPreviewAppeareance(this.value)',
							),
						),
						array(
							'label_class' => 'vik-multiswitch-radiobtn-dark',
							'input' 	  => array(
								'onchange' => 'vboPreviewAppeareance(this.value)',
							),
						),
					);
					echo $vbo_app->multiStateToggleSwitchField('appearance_pref', VikBooking::getAppearancePref(), $appearance_vals, $appearance_lbls, $appearance_attrs);
					?>
					</div>
				</div>

				<div class="vbo-param-container vbo-param-nested">
					<div class="vbo-param-label"><?php echo JText::_('VBO_APPEARANCE_PREF') . ' (front-end)'; ?></div>
					<div class="vbo-param-setting">
						<?php
						$front_appearance = $config->getInt('appearance_front', 0);
						?>
						<select name="appearance_front">
							<option value="0"<?php echo !$front_appearance ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBPARAMPRICECALENDARDISABLED'); ?></option>
							<option value="1"<?php echo $front_appearance ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBPARAMPRICECALENDARENABLED'); ?></option>
						</select>
					</div>
				</div>

				<script type="text/javascript">
					/**
					 * Apply on the fly the preview of the selected
					 * appearance mode: light, auto, dark.
					 */
					function vboPreviewAppeareance(mode) {
						var vbo_css_base_uri = '<?php echo VBO_ADMIN_URI . (VBOPlatformDetection::isWordPress() ? 'resources/' : '') . 'vbo-appearance-%s.css'; ?>';
						var vbo_css_base_id  = 'vbo-css-appearance-';
						var vbo_css_modes 	 = {
							auto: vbo_css_base_uri.replace('%s', 'auto'),
							dark: vbo_css_base_uri.replace('%s', 'dark'),
							light: null
						};
						if (!vbo_css_modes.hasOwnProperty(mode)) {
							return false;
						}
						// set/unset CSS files from DOM
						for (var app_mode in vbo_css_modes) {
							if (!vbo_css_modes.hasOwnProperty(app_mode) || !vbo_css_modes[app_mode]) {
								continue;
							}
							if (app_mode == mode) {
								// set this CSS file
								jQuery('head').append('<link rel="stylesheet" id="' + vbo_css_base_id + app_mode + '" href="' + vbo_css_modes[app_mode] + '" media="all">');
							} else {
								// unset this CSS file
								if (jQuery('link#' + vbo_css_base_id + app_mode).length) {
									jQuery('link#' + vbo_css_base_id + app_mode).remove();
								} else if (jQuery('link#' + vbo_css_base_id + app_mode + '-css').length) {
									// WP framework may add "-css" as suffix to the given ID
									jQuery('link#' + vbo_css_base_id + app_mode + '-css').remove();
								}
							}
						}
					}
				</script>

				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo JText::_('VBCONFIGCRONKEY'); ?></div>
					<div class="vbo-param-setting"><input type="text" name="cronkey" value="<?php echo JHtml::_('esc_attr', VikBooking::getCronKey()); ?>" size="6" /></div>
				</div>
				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo JText::_('VBCONFIGMULTILANG'); ?></div>
					<div class="vbo-param-setting"><?php echo $vbo_app->printYesNoButtons('multilang', JText::_('VBYES'), JText::_('VBNO'), (int)VikBooking::allowMultiLanguage(true), 1, 0); ?></div>
				</div>
				<?php
				if (VBOPlatformDetection::isWordPress()) {
					?>
				<!-- @wponly  we cannot display the setting for the SEF Router -->
					<?php
				} else {
					?>
				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo JText::_('VBCONFIGROUTER'); ?></div>
					<div class="vbo-param-setting"><?php echo $vbo_app->printYesNoButtons('vbosef', JText::_('VBYES'), JText::_('VBNO'), (int)$vbosef, 1, 0); ?></div>
				</div>
					<?php
				}
				?>
				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo JText::_('VBLOADBOOTSTRAP'); ?></div>
					<div class="vbo-param-setting"><?php echo $vbo_app->printYesNoButtons('loadbootstrap', JText::_('VBYES'), JText::_('VBNO'), (int)VikBooking::loadBootstrap(), 1, 0); ?></div>
				</div>
				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo JText::_('VBOLOADFA'); ?></div>
					<div class="vbo-param-setting"><?php echo $vbo_app->printYesNoButtons('usefa', JText::_('VBYES'), JText::_('VBNO'), (int)VikBooking::isFontAwesomeEnabled(), 1, 0); ?></div>
				</div>
				<?php
				if (VBOPlatformDetection::isWordPress()) {
					?>
				<!-- @wponly  jQuery main library should not be loaded as it's already included by WP -->
					<?php
				} else {
					?>
				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo JText::_('VBCONFIGONEJQUERY'); ?></div>
					<div class="vbo-param-setting"><?php echo $vbo_app->printYesNoButtons('loadjquery', JText::_('VBYES'), JText::_('VBNO'), (VikBooking::loadJquery() ? 'yes' : 0), 'yes', 0); ?></div>
				</div>
					<?php
				}
				?>
				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo JText::_('VBO_OPTIMIZE_DB_RECORDS'); ?></div>
					<div class="vbo-param-setting">
						<input type="time" name="dboptimizetime" value="<?php echo $config->get('dboptimizetime', ''); ?>" min="00:00" max="23:30" step="1800" />
						<span class="vbo-param-setting-comment"><?php echo JText::_('VBO_SEL_TIME_OP_RUN'); ?></span>
					</div>
				</div>
				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo JText::_('VBO_GMAPS_APIKEY'); ?></div>
					<div class="vbo-param-setting"><input type="text" name="gmapskey" value="<?php echo JHtml::_('esc_attr', VikBooking::getGoogleMapsKey()); ?>" size="30" /></div>
				</div>
				<div class="vbo-param-container">
					<div class="vbo-param-label">
						<?php echo JText::_('VBOPREFCOUNTRIESORD'); ?> 
						<?php echo $vbo_app->createPopover(array('title' => JText::_('VBOPREFCOUNTRIESORD'), 'content' => JText::_('VBOPREFCOUNTRIESORDHELP'))); ?>
						<div class="vbo-preferred-countries-edit-wrap">
							<span onclick="vboDisplayCustomPrefCountries();"><?php VikBookingIcons::e('edit'); ?></span>
						</div>
					</div>
					<div class="vbo-param-setting">
						<ul class="vbo-preferred-countries-sortlist">
						<?php
						$preferred_countries = VikBooking::preferredCountriesOrdering(true);
						foreach ($preferred_countries as $ccode => $langname) {
							?>
							<li class="vbo-preferred-countries-elem">
								<span><?php VikBookingIcons::e('ellipsis-v'); ?> <?php echo $langname; ?></span>
								<input type="hidden" name="pref_countries[]" value="<?php echo $ccode; ?>" />
							</li>
							<?php
						}
						?>
						</ul>
						<script type="text/javascript">
						function vboDisplayCustomPrefCountries() {
							var all_countries = new Array;
							jQuery('input[name="pref_countries[]"]').each(function() {
								all_countries.push(jQuery(this).val());
							});
							var current_countries = all_countries.join(', ');
							var custom_countries = prompt("<?php echo addslashes(JText::_('VBOPREFCOUNTRIESORD')); ?>", current_countries);
							if (custom_countries != null && custom_countries != current_countries) {
								jQuery('.vbo-preferred-countries-edit-wrap').append('<input type="hidden" name="cust_pref_countries" value="' + custom_countries + '"/>');
								jQuery('#adminForm').find('input[name="task"]').val('saveconfig');
								jQuery('#adminForm').submit();
							}
						}
						jQuery(function() {
							jQuery('.vbo-preferred-countries-sortlist').sortable();
							jQuery('.vbo-preferred-countries-sortlist').disableSelection();
						});
						</script>
					</div>
				</div>
			</div>
		</div>
	</fieldset>

	<!-- BACKUP -->

	<fieldset class="adminform">
		<div class="vbo-params-wrap">
			<legend class="adminlegend"><?php echo JText::_('VBO_CONFIG_BACKUP'); ?></legend>

			<div class="vbo-params-container">

				<!-- TYPE -->

				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo JText::_('VBO_CONFIG_BACKUP_TYPE'); ?></div>
					<div class="vbo-param-setting">
						<?php
						$options = [];

						foreach ($this->backupExportTypes as $type => $handler)
						{
							$options[] = JHtml::_('select.option', $type, $handler->getName());	
						}

						$backup_export_type = $config->get('backuptype', 'full');
						?>
						<select name="backuptype">
							<?php echo JHtml::_('select.options', $options, 'value', 'text', $backup_export_type); ?>
						</select>
						<?php
						// display a description for the export types
						foreach ($this->backupExportTypes as $type => $handler)
						{
							?>
							<div class="vbo-param-setting-comment" id="backup_export_type_<?php echo $type; ?>" style="<?php echo $type === $backup_export_type ? '' : 'display: none;'; ?>">
								<?php echo $handler->getDescription(); ?>
							</div>
							<?php
						}
						?>
					</div>
				</div>

				<!-- FOLDER -->

				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo JText::_('VBO_CONFIG_BACKUP_FOLDER'); ?></div>
					<div class="vbo-param-setting">
						<?php
						// get saved path
						$path = rtrim($config->get('backupfolder', ''), DIRECTORY_SEPARATOR);

						// get system temporary path
						$tmp_path = rtrim(JFactory::getApplication()->get('tmp_path', ''), DIRECTORY_SEPARATOR);

						if (!$path)
						{
							$path = $tmp_path;
						}
						?>
						<input type="text" name="backupfolder" value="<?php echo $this->escape($path); ?>" size="64" />
						<div class="vbo-param-setting-comment">
							<?php echo JText::sprintf('VBO_CONFIG_BACKUP_FOLDER_HELP', (VBOPlatformDetection::isWordPress() ? ABSPATH : JPATH_SITE)); ?>
						</div>
					</div>
				</div>

			</div>

			<!-- BACK-UP MANAGEMENT - Button -->

			<div class="vbo-param-container">
				<div class="vbo-param-label">&nbsp;</div>
				<div class="vbo-param-setting">
					<a href="index.php?option=com_vikbooking&amp;view=backups" class="btn vbo-config-btn" id="backup-btn" target="_blank">
						<?php echo JText::_('VBO_CONFIG_BACKUP_MANAGE_BTN'); ?>
					</a>
				</div>
			</div>

		</div>

		<script>
			(function($) {
				'use strict';

				$(function() {
					$('select[name="backuptype"]').on('change', function() {
						const type = $(this).val();

						$('#adminForm *[id^="backup_export_type_"]').hide();
						$('#backup_export_type_' + type).show();
					});

					$('a#backup-btn').on('click', function() {
						try {
							// gather action details
							let actionName = $(this).text();
							let actionHref = $(this).attr('href');

							// register clicked button on global scope
							VBOCore.registerAdminMenuAction({
								name: actionName,
								href: actionHref,
							}, 'global');

							// register clicked button on VBO's default scope (for VCM)
							VBOCore.registerAdminMenuAction({
								name: actionName,
								href: actionHref,
							}, '');
						} catch(e) {
							console.error(e);
						}
					});
				});
			})(jQuery);
		</script>

	</fieldset>

<?php
$extra_forms = VBOFactory::getPlatform()->getDispatcher()->filter('onDisplayViewConfigGlobal', [$this]);
foreach ($extra_forms as $extra_form) {
	foreach ($extra_form as $form_name => $form_html) {
		?>
	<fieldset class="adminform">
		<div class="vbo-params-wrap">
			<legend class="adminlegend"><?php echo JText::_($form_name); ?></legend>
			<?php echo $form_html; ?>
		</div>
	</fieldset>
		<?php
	}
}
?>

</div>
