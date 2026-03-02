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

JText::script('VCM_GUIDELINES');

?>

<legend class="adminlegend"><?php echo JText::_('VCM_GUIDELINES'); ?></legend>

<div class="vcm-params-container">

	<div class="vcm-param-container">
		<div class="vcm-param-setting">
			<button type="button" class="btn btn-primary" id="guidelines-open-btn"><?php echo JText::_('VCM_TRAINING_GUIDELINES_BTN_OPEN'); ?></button>
			<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_TRAINING_GUIDELINES_DESC'); ?></span>
		</div>
	</div>

</div>

<?php echo $this->loadTemplate('guidelines_modal'); ?>

<script>
	(function($) {
		'use strict';

		$(function() {
			$('#guidelines-open-btn').on('click', () => {
				VBOCore.displayModal({
					suffix: 'guidelines',
					title: $('<strong></strong>').text(Joomla.JText._('VCM_GUIDELINES')).css('font-size', '18px'),
					body: $('#guidelines-modal'),
					body_prepend: true,
					lock_scroll: true,
					dismiss_event: 'guidelines.dismiss',
					onDismiss: () => {
						$('#guidelines-modal').appendTo('#guidelines-modal-wrapper');
					},
				});
			});
		});
	})(jQuery);
</script>