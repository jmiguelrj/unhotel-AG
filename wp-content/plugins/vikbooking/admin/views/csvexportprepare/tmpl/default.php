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

$all_rooms = $this->all_rooms;
$all_channels = $this->all_channels;
$all_payments = $this->all_payments;

JHtml::_('behavior.tooltip');

$vbo_app = VikBooking::getVboApplication();

$nowdf = VikBooking::getDateFormat(true);
if ($nowdf == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($nowdf == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}
?>

<script type="text/javascript">
	function vboToggleDates(val) {
		if (!val || !val.length) {
			jQuery('.vbcsvexp-dates').hide();
		} else {
			jQuery('.vbcsvexp-dates').show();
		}
	}
</script>

<form action="index.php?option=com_vikbooking" method="post" name="adminForm" id="adminForm">
	<div class="vbo-admin-container">
		<div class="vbo-config-maintab-left">
			<fieldset class="adminform">
				<div class="vbo-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VBCSVEXPORT'); ?></legend>
					<div class="vbo-params-container">
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBOFILTERBYDATES'); ?></div>
							<div class="vbo-param-setting">
								<select name="datefilt" onchange="vboToggleDates(this.value);">
									<option value="">----------</option>
									<option value="ts"><?php echo JText::_('VBOFILTERDATEBOOK'); ?></option>
									<option value="checkin"><?php echo JText::_('VBOFILTERDATEIN'); ?></option>
									<option value="checkout"><?php echo JText::_('VBOFILTERDATEOUT'); ?></option>
								</select>
							</div>
						</div>
						<div class="vbo-param-container vbo-param-nested vbcsvexp-dates" style="display: none;">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWRESTRICTIONDFROMRANGE'); ?></div>
							<div class="vbo-param-setting">
								<?php echo $vbo_app->getCalendar('', 'checkindate', 'checkindate', $nowdf, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?>
							</div>
						</div>
						<div class="vbo-param-container vbo-param-nested vbcsvexp-dates" style="display: none;">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWRESTRICTIONDTORANGE'); ?></div>
							<div class="vbo-param-setting">
								<?php echo $vbo_app->getCalendar('', 'checkoutdate', 'checkoutdate', $nowdf, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?>
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBROOMFILTER'); ?></div>
							<div class="vbo-param-setting">
								<select name="roomfilt"><option value="">----------</option>
								<?php
								foreach ($all_rooms as $room) {
									?>
									<option value="<?php echo $room['id']; ?>"><?php echo $room['name']; ?></option>
									<?php
								}
								?>
								</select>
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBPSHOWSEASONSTHREE'); ?></div>
							<div class="vbo-param-setting">
								<select name="format">
									<option value="csv">CSV</option>
									<option value="excel">Excel</option>
								</select>
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-setting">
								<button type="button" class="btn" name="csvsubmit" onclick="document.getElementById('adminForm').submit();"><i class="vboicn-cloud-download"></i> <?php echo JText::_('VBCSVGENERATE'); ?></button>
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
						<?php
					if ($this->all_categories) {
						?>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBOCATEGORYFILTER'); ?></div>
							<div class="vbo-param-setting">
								<select name="catfilt"><option value="">----------</option>
								<?php
								foreach ($this->all_categories as $cat) {
									?>
									<option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
									<?php
								}
								?>
								</select>
							</div>
						</div>
						<?php
					}
					?>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBCHANNELFILTER'); ?></div>
							<div class="vbo-param-setting">
								<select name="chfilt"><option value="">----------</option>
								<?php
								foreach ($all_channels as $ch) {
									?>
									<option value="<?php echo $ch; ?>"><?php echo $ch; ?></option>
									<?php
								}
								?>
								</select>
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBOFILTERBYPAYMENT'); ?></div>
							<div class="vbo-param-setting">
								<select name="payfilt"><option value="">----------</option>
								<?php
								foreach ($all_payments as $pay) {
									?>
									<option value="<?php echo $pay['id']; ?>"><?php echo $pay['name']; ?></option>
									<?php
								}
								?>
								</select>
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBCSVEXPFILTBSTATUS'); ?></div>
							<div class="vbo-param-setting">
								<select name="status">
									<option value="">----------</option>
									<option value="confirmed"><?php echo JText::_('VBCSVSTATUSCONFIRMED'); ?></option>
									<option value="standby"><?php echo JText::_('VBCSVSTATUSSTANDBY'); ?></option>
									<option value="cancelled"><?php echo JText::_('VBCSVSTATUSCANCELLED'); ?></option>
								</select>
							</div>
						</div>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
	<input type="hidden" name="task" value="csvexportlaunch" />
	<input type="hidden" name="option" value="com_vikbooking" />
</form>
