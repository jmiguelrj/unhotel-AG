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

VCM::load_css_js();

?>

<form name="adminForm" action="index.php" method="post" id="adminForm" enctype="multipart/form-data">

	<div class="vcm-admin-container">
		<div class="vcm-config-maintab-left">
			<fieldset class="adminform">
				<div class="vcm-params-wrap">
					<?php echo $this->loadTemplate('details'); ?>
				</div>
			</fieldset>
			<fieldset class="adminform">
				<div class="vcm-params-wrap">
					<?php echo $this->loadTemplate('content'); ?>
				</div>
			</fieldset>
		</div>
		<div class="vcm-config-maintab-right">
			<?php if (empty($this->training->needsreview)): ?>
				<fieldset class="adminform">
					<div class="vcm-params-wrap">
						<?php echo $this->loadTemplate('guidelines'); ?>
					</div>
				</fieldset>
			<?php endif; ?>
			<fieldset class="adminform">
				<div class="vcm-params-wrap">
					<?php echo $this->loadTemplate('attachments'); ?>
				</div>
			</fieldset>
			<fieldset class="adminform">
				<div class="vcm-params-wrap">
					<?php echo $this->loadTemplate('publishing'); ?>
				</div>
			</fieldset>
		</div>
	</div>

	<?php echo JHtml::_('form.token'); ?>
	
	<input type="hidden" name="id" value="<?php echo (int) ($this->training->id ?? 0); ?>" />
	<input type="hidden" name="option" value="com_vikchannelmanager" />
	<input type="hidden" name="task" value="" />

</form>
