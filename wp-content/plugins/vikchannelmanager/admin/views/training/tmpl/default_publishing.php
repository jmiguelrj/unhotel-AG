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

$vbo = VikChannelManager::getVboApplication();

?>

<legend class="adminlegend"><?php echo JText::_('VCM_PUBLISHING'); ?></legend>

<div class="vcm-params-container">

	<div class="vcm-param-container">
		<div class="vcm-param-label"><label for="published-on"><?php echo JText::_('VCMPAYMENTSTATUS1'); ?></label></div>
		<div class="vcm-param-setting">
			<?php echo $vbo->printYesNoButtons('published', JText::_('JYES'), JText::_('JNO'), (int) ($this->training->published ?? 1), 1, 0); ?>
			<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_TRAINING_PUBLISHED_DESC'); ?></span>
		</div>
	</div>

	<div class="vcm-param-container">
		<div class="vcm-param-label"><?php echo JText::_('VCM_CREATED_DATE'); ?></div>
		<div class="vcm-param-setting">
			<input type="text" value="<?php echo $this->escape(!empty($this->training->created) ? JHtml::_('date', $this->training->created, JText::_('Y-m-d H:i:s')) : ''); ?>" readonly placeholder="<?php echo $this->escape(JText::_('VCM_FILLED_ON_SAVE')); ?>" />
		</div>
	</div>

	<div class="vcm-param-container">
		<div class="vcm-param-label"><?php echo JText::_('VCM_CREATED_BY'); ?></div>
		<div class="vcm-param-setting">
			<input type="text" value="<?php echo $this->escape($this->training->created_by->name ?? ''); ?>" readonly placeholder="<?php echo $this->escape(JText::_('VCM_FILLED_ON_SAVE')); ?>" />
		</div>
	</div>

	<div class="vcm-param-container">
		<div class="vcm-param-label"><?php echo JText::_('VCM_MODIFIED_DATE'); ?></div>
		<div class="vcm-param-setting">
			<input type="text" value="<?php echo $this->escape(!empty($this->training->modified) ? JHtml::_('date', $this->training->modified, JText::_('Y-m-d H:i:s')) : ''); ?>" readonly placeholder="<?php echo $this->escape(JText::_('VCM_FILLED_ON_SAVE')); ?>" />
		</div>
	</div>

	<div class="vcm-param-container">
		<div class="vcm-param-label"><?php echo JText::_('VCM_MODIFIED_BY'); ?></div>
		<div class="vcm-param-setting">
			<input type="text" value="<?php echo $this->escape($this->training->modified_by->name ?? ''); ?>" readonly placeholder="<?php echo $this->escape(JText::_('VCM_FILLED_ON_SAVE')); ?>" />
		</div>
	</div>

</div>