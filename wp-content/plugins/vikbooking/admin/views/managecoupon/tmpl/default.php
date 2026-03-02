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

$coupon = $this->coupon;
$wselrooms = $this->wselrooms;

$vbo_app = VikBooking::getVboApplication();
$vbo_app->loadSelect2();

$currencysymb = VikBooking::getCurrencySymb();
$df = VikBooking::getDateFormat(true);
$fromdate = "";
$todate = "";
if (count($coupon) && strlen($coupon['datevalid']) > 0) {
	$dateparts = explode("-", $coupon['datevalid']);
	if ($df == "%d/%m/%Y") {
		$udf = 'd/m/Y';
	} elseif ($df == "%m/%d/%Y") {
		$udf = 'm/d/Y';
	} else {
		$udf = 'Y/m/d';
	}
	$fromdate = date($udf, $dateparts[0]);
	$todate = date($udf, $dateparts[1]);
}

JText::script('VBO_ERR_LOAD_RESULTS');
JText::script('NO_ROWS_FOUND');
JText::script('VBO_SEARCHING');

?>
<script type="text/javascript">
function vbToggleRooms() {
	if (document.adminForm.allvehicles.checked == true) {
		jQuery('#vbo-roomslist').hide();
	} else {
		jQuery('#vbo-roomslist').show();
	}
	return true;
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
							<div class="vbo-param-label"><?php echo JText::_('VBNEWCOUPONONE'); ?></div>
							<div class="vbo-param-setting"><input type="text" name="code" value="<?php echo count($coupon) ? $coupon['code'] : ''; ?>" size="30"/></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWCOUPONTWO'); ?></div>
							<div class="vbo-param-setting">
								<select name="type">
									<option value="1"<?php echo (count($coupon) && $coupon['type'] == 1 ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBCOUPONTYPEPERMANENT'); ?></option>
									<option value="2"<?php echo (count($coupon) && $coupon['type'] == 2 ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBCOUPONTYPEGIFT'); ?></option>
								</select>
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWCOUPONTHREE'); ?></div>
							<div class="vbo-param-setting">
								<select name="percentot">
									<option value="1"<?php echo (count($coupon) && $coupon['percentot'] == 1 ? " selected=\"selected\"" : ""); ?>>%</option>
									<option value="2"<?php echo (count($coupon) && $coupon['percentot'] == 2 ? " selected=\"selected\"" : ""); ?>><?php echo $currencysymb; ?></option>
								</select>
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWCOUPONFOUR'); ?></div>
							<div class="vbo-param-setting"><input type="number" step="any" min="0" name="value" value="<?php echo count($coupon) ? $coupon['value'] : ''; ?>" size="4"/></div>
						</div>
					</div>
				</div>
			</fieldset>
			<fieldset class="adminform">
				<div class="vbo-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VBMENUCUSTOMERS'); ?></legend>
					<div class="vbo-params-container">
					<?php
					if (count($coupon)) {
						?>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBO_NUMBER_USES'); ?></div>
							<div class="vbo-param-setting">
							<?php
							if ($this->use_count > 0) {
								?>
								<span class="vbo-coupon-use-count">
									<a href="index.php?option=com_vikbooking&task=orders&confirmnumber=coupon:<?php echo htmlspecialchars($coupon['code']); ?>" target="_blank"><?php echo $this->use_count; ?></a>
								</span>
								<?php
							} else {
								echo $this->use_count;
							}
							?>
							</div>
						</div>
						<?php
					}
					?>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBO_CUSTOMERS_ASSIGNED'); ?></div>
							<div class="vbo-param-setting">
								<select name="customers[]" multiple="multiple">
								<?php
								foreach ($this->coupon_customers as $coupon_customer) {
									?>
									<option value="<?php echo $coupon_customer['id']; ?>" selected="selected"><?php echo $coupon_customer['text']; ?></option>
									<?php
								}
								?>
								</select>
								<div class="vbo-param-setting-comment"><?php echo JText::_('VBO_CUSTOMERS_ASSIGNED_HELP') . ' *(' . JText::_('VBCUSTOMERTOTBOOKINGS') . ')'; ?></div>
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo $vbo_app->createPopover(array('title' => JText::_('VBO_APPLY_AUTOMATICALLY'), 'content' => JText::_('VBO_APPLY_AUTOMATICALLY_HELP'))); ?> <?php echo JText::_('VBO_APPLY_AUTOMATICALLY'); ?></div>
							<div class="vbo-param-setting">
								<?php echo $vbo_app->printYesNoButtons('automatic', JText::_('VBYES'), JText::_('VBNO'), (count($this->coupon_customers) && $this->coupon_customers[0]['automatic'] ? 1 : 0), 1, 0); ?>
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
							<div class="vbo-param-label"><?php echo JText::_('VBNEWCOUPONFIVE'); ?></div>
							<div class="vbo-param-setting">
								<?php echo $vbo_app->printYesNoButtons('allvehicles', JText::_('VBNEWCOUPONEIGHT'), JText::_('VBNEWCOUPONFIVE'), ((count($coupon) && $coupon['allvehicles'] == 1) || !count($coupon) ? 1 : 0), 1, 0, 'vbToggleRooms();'); ?>
							</div>
						</div>
						<div class="vbo-param-container vbo-param-nested" id="vbo-roomslist" style="display: <?php echo ((count($coupon) && $coupon['allvehicles'] == 1) || !count($coupon) ? 'none' : 'flex'); ?>;">
							<div class="vbo-param-label"></div>
							<div class="vbo-param-setting">
								<?php echo $wselrooms; ?>
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo $vbo_app->createPopover(array('title' => JText::_('VBNEWCOUPONSIX'), 'content' => JText::_('VBNEWCOUPONNINE'))); ?> <?php echo JText::_('VBNEWCOUPONSIX'); ?></div>
							<div class="vbo-param-setting">
								<div style="display: block; margin-bottom: 3px;">
									<?php echo '<span class="vbrestrdrangesp">'.JText::_('VBNEWRESTRICTIONDFROMRANGE').'</span>'.$vbo_app->getCalendar($fromdate, 'from', 'from', $df, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?>
								</div>
								<div style="display: block; margin-bottom: 3px;">
									<?php echo '<span class="vbrestrdrangesp">'.JText::_('VBNEWRESTRICTIONDTORANGE').'</span>'.$vbo_app->getCalendar($todate, 'to', 'to', $df, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?>
								</div>
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWCOUPONSEVEN'); ?></div>
							<div class="vbo-param-setting">
								<div class="input-append">
									<input type="number" step="any" min="0" name="mintotord" value="<?php echo count($coupon) ? $coupon['mintotord'] : '0'; ?>" size="4"/>
									<button type="button" class="btn"><?php echo $currencysymb; ?></button>
								</div>
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBO_MAX_BOOK_TOTAL'); ?></div>
							<div class="vbo-param-setting">
								<div class="input-append">
									<input type="number" step="any" min="0" name="maxtotord" value="<?php echo count($coupon) ? $coupon['maxtotord'] : '0'; ?>" size="4"/>
									<button type="button" class="btn"><?php echo $currencysymb; ?></button>
								</div>
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBOPRICETYPEMINLOS'); ?></div>
							<div class="vbo-param-setting"><input type="number" name="minlos" min="1" value="<?php echo count($coupon) ? $coupon['minlos'] : '1'; ?>" /></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo $vbo_app->createPopover(array('title' => JText::_('VBOCOUPONEXCLTAX'), 'content' => JText::_('VBOCOUPONEXCLTAXHELP'))); ?> <?php echo JText::_('VBOCOUPONEXCLTAX'); ?></div>
							<div class="vbo-param-setting">
								<?php echo $vbo_app->printYesNoButtons('excludetaxes', JText::_('VBYES'), JText::_('VBNO'), ((count($coupon) && $coupon['excludetaxes'] == 1) || !count($coupon) ? 1 : 0), 1, 0); ?>
							</div>
						</div>
					</div>
				</div>
			</fieldset>
		</div>

	</div>
	<input type="hidden" name="task" value="">
	<input type="hidden" name="option" value="com_vikbooking">
<?php
if (count($coupon)) {
	?>
	<input type="hidden" name="where" value="<?php echo $coupon['id']; ?>">
	<?php
}
?>
	<?php echo JHtml::_('form.token'); ?>
</form>

<script type="text/javascript">
jQuery(function() {
	jQuery('#from').val('<?php echo $fromdate; ?>').attr('data-alt-value', '<?php echo $fromdate; ?>');
	jQuery('#to').val('<?php echo $todate; ?>').attr('data-alt-value', '<?php echo $todate; ?>');
	jQuery('select[name="idrooms[]"]').select2();
	jQuery('select[name="customers[]"]').select2({
		language: {
			errorLoading: () => {
				return Joomla.JText._('VBO_ERR_LOAD_RESULTS');
			},
			noResults: () => {
				return Joomla.JText._('NO_ROWS_FOUND');
			},
			searching: () => {
				return Joomla.JText._('VBO_SEARCHING');
			},
		},
		ajax: {
			delay: 350,
			url: "<?php echo VikBooking::ajaxUrl('index.php?option=com_vikbooking&task=bookings.customers_search'); ?>",
			dataType: 'json',
		}
	});
});
</script>
