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

$payment = $this->payment;

$vbo_app = VikBooking::getVboApplication();
$vbo_app->loadSelect2();

$editor = JEditor::getInstance(JFactory::getApplication()->get('editor'));

$all_rooms = VikBooking::getAvailabilityInstance()->loadRooms();

/**
 * @wponly 	Use the dispatcher to retrieve the list of the installed gateways.
 *
 * @since 	1.0.5
 */
JLoader::import('adapter.payment.dispatcher');
$allf = JPaymentDispatcher::getSupportedDrivers('vikbooking');
//

$force_file = VikRequest::getString('file', '', 'request');

$psel = "";
if (@count($allf) > 0) {
	$classfiles = array();
	foreach ($allf as $af) {
		$classfiles[] = basename($af, '.php');
	}
	sort($classfiles);
	$psel = "<select name=\"payment\" onchange=\"vikLoadPaymentParameters(this.value);\">\n<option value=\"\"></option>\n";
	foreach ($classfiles as $cf) {
		$selected = ((count($payment) && $cf == basename($payment['file'], '.php')) || (!count($payment) && $cf == $force_file));
		$psel .= "<option value=\"".$cf."\"".($selected ? " selected=\"selected\"" : "").">".$cf."</option>\n";
	}
	$psel .= "</select>";
}

$currencysymb = VikBooking::getCurrencySymb();
$payparams = count($payment) ? VikBooking::displayPaymentParameters($payment['file'], $payment['params']) : '';
if (!count($payment) && !empty($force_file)) {
	// trigger the loading of the pre-selected driver params
	$payparams = VikBooking::displayPaymentParameters($force_file, null);
}
?>
<script type="text/javascript">
function vikLoadPaymentParameters(pfile) {
	if (pfile.length > 0) {
		jQuery("#vikparameters").html('<p><?php echo addslashes(JText::_('VIKLOADING')); ?></p>');
		jQuery.ajax({
			type: "POST",
			url: "index.php?option=com_vikbooking&task=loadpaymentparams&tmpl=component",
			data: { phpfile: pfile }
		}).done(function(res) {
			jQuery("#vikparameters").html(res);
		});
	} else {
		jQuery("#vikparameters").html('<p>--------</p>');
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
							<div class="vbo-param-label"><?php echo JText::_('VBNEWPAYMENTONE'); ?></div>
							<div class="vbo-param-setting"><input type="text" name="name" value="<?php echo count($payment) ? $payment['name'] : ''; ?>" size="30"/></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWPAYMENTTWO'); ?></div>
							<div class="vbo-param-setting"><?php echo $psel; ?></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWPAYMENTTHREE'); ?></div>
							<div class="vbo-param-setting"><?php echo $vbo_app->printYesNoButtons('published', JText::_('VBYES'), JText::_('VBNO'), (count($payment) ? (int)$payment['published'] : 1), 1, 0); ?></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWPAYMENTCHARGEORDISC'); ?></div>
							<div class="vbo-param-setting">
								<select name="ch_disc">
									<option value="1"<?php echo (count($payment) && $payment['ch_disc'] == 1 ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBNEWPAYMENTCHARGEPLUS'); ?></option>
									<option value="2"<?php echo (count($payment) && $payment['ch_disc'] == 2 ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBNEWPAYMENTDISCMINUS'); ?></option>
								</select> 
								<input type="number" step="any" name="charge" value="<?php echo count($payment) ? $payment['charge'] : ''; ?>" size="5"/> 
								<select name="val_pcent">
									<option value="1"<?php echo (count($payment) && $payment['val_pcent'] == 1 ? " selected=\"selected\"" : ""); ?>><?php echo $currencysymb; ?></option>
									<option value="2"<?php echo (count($payment) && $payment['val_pcent'] == 2 ? " selected=\"selected\"" : ""); ?>>%</option>
								</select>
							</div>
						</div>
					</div>
				</div>
			</fieldset>
			<fieldset class="adminform">
				<div class="vbo-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VBOADMINLEGENDSETTINGS'); ?></legend>
					<div class="vbo-params-container">
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo $vbo_app->createPopover(array('title' => JText::_('VBPAYMENTSHELPCONFIRMTXT'), 'content' => JText::_('VBPAYMENTSHELPCONFIRM'))); ?> <?php echo JText::_('VBNEWPAYMENTEIGHT'); ?></div>
							<div class="vbo-param-setting"><?php echo $vbo_app->printYesNoButtons('setconfirmed', JText::_('VBYES'), JText::_('VBNO'), (count($payment) ? (int)$payment['setconfirmed'] : 0), 1, 0); ?></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo $vbo_app->createPopover(array('title' => JText::_('VBOPAYMENTHIDENONREF'), 'content' => JText::_('VBOPAYMENTHIDENONREFHELP'))); ?> <?php echo JText::_('VBOPAYMENTHIDENONREF'); ?></div>
							<div class="vbo-param-setting"><?php echo $vbo_app->printYesNoButtons('hidenonrefund', JText::_('VBYES'), JText::_('VBNO'), (count($payment) ? (int)$payment['hidenonrefund'] : 0), 1, 0); ?></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo $vbo_app->createPopover(array('title' => JText::_('VBOPAYMENTONLYNONREF'), 'content' => JText::_('VBOPAYMENTONLYNONREFHELP'))); ?> <?php echo JText::_('VBOPAYMENTONLYNONREF'); ?></div>
							<div class="vbo-param-setting"><?php echo $vbo_app->printYesNoButtons('onlynonrefund', JText::_('VBYES'), JText::_('VBNO'), (count($payment) ? (int)$payment['onlynonrefund'] : 0), 1, 0); ?></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWPAYMENTNINE'); ?></div>
							<div class="vbo-param-setting"><?php echo $vbo_app->printYesNoButtons('shownotealw', JText::_('VBYES'), JText::_('VBNO'), (count($payment) ? (int)$payment['shownotealw'] : 0), 1, 0); ?></div>
						</div>
						<?php
						// build list of room IDs assigned to this payment option
						$current_rooms = [];
						if (count($payment) && !empty($payment['idrooms'])) {
							$current_rooms = json_decode($payment['idrooms']);
							$current_rooms = !is_array($current_rooms) ? [] : $current_rooms;
						}
						$all_rooms_selected = (count($current_rooms) && count($current_rooms) == count($all_rooms));
						?>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWRESTRICTIONALLROOMS'); ?></div>
							<div class="vbo-param-setting"><?php echo $vbo_app->printYesNoButtons('all_rooms', JText::_('VBYES'), JText::_('VBNO'), (!count($payment) || empty($payment['idrooms']) || $all_rooms_selected ? 1 : 0), 1, 0, 'vboToggleRooms(this.checked);'); ?></div>
						</div>
						<div class="vbo-param-container vbo-param-nested" id="vbo-paym-idrooms" style="<?php echo !count($payment) || empty($payment['idrooms']) || $all_rooms_selected ? 'display: none;' : ''; ?>;">
							<div class="vbo-param-label"><?php echo JText::_('VBPVIEWORDERSTHREE'); ?></div>
							<div class="vbo-param-setting">
								<select name="idrooms[]" multiple="multiple" id="vbo-idrooms-sel">
								<?php
								foreach ($all_rooms as $room) {
									?>
									<option value="<?php echo $room['id']; ?>"<?php echo in_array($room['id'], $current_rooms) ? ' selected="selected"' : ''; ?>><?php echo $room['name']; ?></option>
									<?php
								}
								?>
								</select>
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBO_PAYBUT_POS'); ?></div>
							<div class="vbo-param-setting">
								<select id="vbo-outposition" name="outposition" onchange="vboUpdateOutposSkeleton();">
									<option value="top"<?php echo !count($payment) || (count($payment) && $payment['outposition'] == 'top') ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBO_PAYBUT_POS_TOP'); ?></option>
									<option value="middle"<?php echo count($payment) && $payment['outposition'] == 'middle' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBO_PAYBUT_POS_MIDDLE'); ?></option>
									<option value="bottom"<?php echo count($payment) && $payment['outposition'] == 'bottom' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBO_PAYBUT_POS_BOTTOM'); ?></option>
								</select>
								<?php
								$current_paybut_pos = 'top';
								if (count($payment) && !empty($payment['outposition'])) {
									$current_paybut_pos = $payment['outposition'];
								}
								?>
								<div class="vbo-paybutpos-wrap">
									<div class="vbo-paybutpos-skeleton-card">
										<div class="vbo-paybutpos-skeleton-card-inner">
											<div class="vbo-paybutpos-skeleton-el-status">&nbsp;</div>
											<div class="vbo-paybutpos-skeleton-el-pos vbo-paybutpos-skeleton-el-pos-top" style="<?php echo $current_paybut_pos != 'top' ? 'display: none;' : ''; ?>">
												<span class="vbo-paybutpos-skeleton-el-paybut"><?php VikBookingIcons::e('credit-card'); ?></span>
											</div>
											<div class="vbo-paybutpos-skeleton-el-customer-infos">
												<div class="vbo-paybutpos-skeleton-el-customer">
													<span><?php VikBookingIcons::e('users'); ?></span>
												</div>
												<div class="vbo-paybutpos-skeleton-el-customer">
													<span><?php VikBookingIcons::e('passport'); ?></span>
												</div>
											</div>
											<div class="vbo-paybutpos-skeleton-el-pos vbo-paybutpos-skeleton-el-pos-middle" style="<?php echo $current_paybut_pos != 'middle' ? 'display: none;' : ''; ?>">
												<span class="vbo-paybutpos-skeleton-el-paybut"><?php VikBookingIcons::e('credit-card'); ?></span>
											</div>
											<div class="vbo-paybutpos-skeleton-el-room-infos">
												<div class="vbo-paybutpos-skeleton-el-room">
													<span><?php VikBookingIcons::e('bed'); ?></span>
												</div>
												<div class="vbo-paybutpos-skeleton-el-room">
													<span><?php VikBookingIcons::e('bed'); ?></span>
												</div>
											</div>
											<div class="vbo-paybutpos-skeleton-el-pos vbo-paybutpos-skeleton-el-pos-bottom" style="<?php echo $current_paybut_pos != 'bottom' ? 'display: none;' : ''; ?>">
												<span class="vbo-paybutpos-skeleton-el-paybut"><?php VikBookingIcons::e('credit-card'); ?></span>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBO_PAYMET_LOGO'); ?></div>
							<div class="vbo-param-setting"><?php echo $vbo_app->getMediaField('logo', (count($payment) && !empty($payment['logo']) ? $payment['logo'] : null)); ?></div>
						</div>
						<div class="vbo-param-container vbo-param-container-full">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWPAYMENTFIVE'); ?></div>
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
										echo $editor->display( "note", (count($payment) ? $payment['note'] : ""), '100%', 300, 70, 20 );
									} catch (Throwable $t) {
										echo $t->getMessage() . ' in ' . $t->getFile() . ':' . $t->getLine() . '<br/>';
									}
								} else {
									// we cannot catch Fatal Errors in PHP 5.x
									echo $editor->display( "note", (count($payment) ? $payment['note'] : ""), '100%', 300, 70, 20 );
								}
								?>
							</div>
						</div>
					</div>
				</div>
			</fieldset>
		</div>
		<div class="vbo-config-maintab-right">
			<fieldset class="adminform">
				<div class="vbo-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VBPAYMENTPARAMETERS'); ?></legend>
					<div class="vbo-params-container vbo-payment-params-container" id="vikparameters">
						<?php echo $payparams; ?>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
	<input type="hidden" name="task" value="">
	<input type="hidden" name="option" value="com_vikbooking">
<?php
if (count($payment)) :
?>
	<input type="hidden" name="where" value="<?php echo $payment['id']; ?>">
<?php
endif;
?>
	<?php echo JHtml::_('form.token'); ?>
</form>

<script type="text/javascript">
	
	function vboUpdateOutposSkeleton() {
		var pos = jQuery('#vbo-outposition').val();
		pos = !pos || !pos.length ? 'top' : pos;
		jQuery('.vbo-paybutpos-skeleton-el-pos').hide();
		jQuery('.vbo-paybutpos-skeleton-el-pos-' + pos).show();
	}

	function vboToggleRooms(enabled) {
		if (enabled) {
			jQuery('#vbo-paym-idrooms').hide();
		} else {
			jQuery('#vbo-paym-idrooms').show();
		}
	}

	jQuery(function() {

		// adjust payment button position interface
		vboUpdateOutposSkeleton();

		// render select2
		jQuery('#vbo-idrooms-sel').select2();

	});

</script>
