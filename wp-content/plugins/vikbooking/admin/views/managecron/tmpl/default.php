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

$vbo_app = VikBooking::getVboApplication();
$vbo_app->loadSelect2();
$vbo_app->loadVisualEditorAssets();

$forms = $this->onDisplayView();

?>
<script type="text/javascript">
function vikLoadCronParameters(pfile) {
	if (pfile.length > 0) {
		jQuery("#vbo-cron-params").html('<?php echo addslashes(JText::_('VIKLOADING')); ?>');
		VBOCore.doAjax(
			"<?php echo VikBooking::ajaxUrl('index.php?option=com_vikbooking&task=loadcronparams'); ?>",
			{
				phpfile: pfile,
				tmpl: 'component',
			},
			(res) => {
				jQuery("#vbo-cron-params").html(res);
			},
			(error) => {
				console.error(error);
				alert(error.responseText || 'Generic error');
				jQuery("#vbo-cron-params").html('');
			}
		);
	} else {
		jQuery("#vbo-cron-params").html('<p>--------</p>');
	}
}
</script>
<form name="adminForm" id="adminForm" action="index.php" method="post">
	<div class="vbo-admin-container">
		<div class="vbo-config-maintab-left vbo-config-maintab-smaller">
			<fieldset class="adminform">
				<div class="vbo-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VBOADMINLEGENDDETAILS'); ?></legend>
					<div class="vbo-params-container">
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBCRONNAME'); ?></div>
							<div class="vbo-param-setting"><input type="text" name="cron_name" value="<?php echo count($row) ? $row['cron_name'] : ''; ?>" size="50"/></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBCRONCLASS'); ?></div>
							<div class="vbo-param-setting">
								<select name="class_file" id="cronfile" onchange="vikLoadCronParameters(this.value);">
									<?php
									$classfiles = [
										JHtml::_('select.option', '', ''),
									];

									foreach ($this->supportedDrivers as $driverId => $driver)
									{
										$classfiles[] = JHtml::_('select.option', $driverId, $driver->getTitle());
									}
									
									echo JHtml::_('select.options', $classfiles, 'value', 'text', $row ? basename($row['class_file'], '.php') : '');
									?>
								</select>
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBCRONPUBLISHED'); ?></div>
							<div class="vbo-param-setting"><?php echo $vbo_app->printYesNoButtons('published', JText::_('VBYES'), JText::_('VBNO'), (count($row) ? (int)$row['published'] : 1), 1, 0); ?></div>
						</div>
						<?php
						// look for any additional fields to be pushed within the "Details" fieldset (left-side)
						if (isset($forms['cronjob']))
						{
							echo $forms['cronjob'];
						}
						?>
					</div>
				</div>
			</fieldset>
			<?php
			foreach ($forms as $legend => $form)
			{
				if (in_array($legend, ['cronjob', 'params']))
				{
					// skip default forms
					continue;
				}
				?>
				<fieldset class="adminform">
					<div class="vbo-params-wrap">
						<legend class="adminlegend"><?php echo JText::_($legend); ?></legend>
						<div class="vbo-params-container">
							<?php echo $form; ?>
						</div>
					</div>
				</fieldset>
				<?php
			}
			?>
		</div>
		<div class="vbo-config-maintab-right vbo-config-maintab-larger">
			<fieldset class="adminform">
				<div class="vbo-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VBCRONPARAMS'); ?></legend>
					<div class="vbo-params-container">
						<div id="vbo-cron-params">
							<?php echo count($row) ? VikBooking::displayCronParameters($row['class_file'], $row['params']) : ''; ?>
						</div>
						<?php
						// look for any additional fields to be pushed within the "Parameters" fieldset (right-side)
						if (isset($forms['params']))
						{
							echo $forms['params'];
						}
						?>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
	<input type="hidden" name="task" value="">
	<input type="hidden" name="option" value="com_vikbooking">
<?php
if (count($row)) :
?>
	<input type="hidden" name="id" value="<?php echo $row['id']; ?>">
<?php
endif;
?>
	<?php echo JHtml::_('form.token'); ?>
</form>
