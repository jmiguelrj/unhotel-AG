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

?>

<legend class="adminlegend"><?php echo JText::_('VCMGREVCONTENT'); ?></legend>

<div class="vcm-params-container">

	<div class="vcm-param-container">
		<div class="vcm-param-setting">
			<textarea name="content" style="min-height: 200px; width: 100% !important;" maxlength="1500"><?php echo $this->training->content ?? ''; ?></textarea>
			
			<?php if ($this->training->needsreview ?? false): ?>
				<p class="warn"><?php echo JText::plural('VCM_AI_TRAINING_NEEDS_REVIEW_WARNING', $this->trainingModel->getExpirationDays($this->training)); ?></p>
			<?php else: ?>
				<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_TRAINING_CONTENT_DESC'); ?></span>
			<?php endif; ?>
		</div>
	</div>

</div>