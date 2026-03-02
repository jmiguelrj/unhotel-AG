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

VikBooking::getVboApplication()->loadContextMenuAssets();

JText::script('VCMRAROPEN');
JText::script('VCMBCAHDELETE');
JText::script('VBO_WANT_PROCEED');

?>

<legend class="adminlegend"><?php echo JText::_('VCM_ATTACHMENTS'); ?></legend>

<div class="vcm-params-container">

	<div class="vcm-param-container">
		<div class="vcm-param-setting">
			<input type="file" name="attachments[]" multiple />
			<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_TRAINING_ATTACHMENTS_DESC'); ?></span>
		</div>
	</div>

	<?php if ($this->training->attachments ?? null): ?>
		<div class="vcm-param-container">
			<div class="vcm-param-setting">
				<?php foreach ($this->training->attachments as $attachment): ?>
					<span>
						<a
							href="<?php echo $attachment->url . '?' . time(); ?>"
							class="btn btn-primary attachment-hndl"
							style="padding: 6px 12px; margin: 2px 0; height: auto;"
						><?php VikBookingIcons::e('paperclip'); ?>&nbsp;<?php echo $attachment->filename; ?></a>
						<input type="hidden" name="attachments[]" value="<?php echo $this->escape($attachment->filename); ?>" />
					</span>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

</div>

<script>
	(function($) {
		'use strict';

		const buttons = [
			{
				icon: '<?php echo VikBookingIcons::i('external-link'); ?>',
				text: Joomla.JText._('VCMRAROPEN'),
				separator: true,
				action: (root, config) => {
					window.open($(root).attr('href'), '_blank');
				},
			},
			{
				icon: '<?php echo VikBookingIcons::i('trash'); ?>',
				text: Joomla.JText._('VCMBCAHDELETE'),
				separator: true,
				action: (root, config) => {
					if (!confirm(Joomla.JText._('VBO_WANT_PROCEED'))) {
						return false;
					}

					if ($(root).closest('.vcm-param-setting').children().length <= 1) {
						// delete the whole setting
						$(root).closest('.vcm-param-container').remove();
					} else {
						// delete only this attachment
						$(root).parent().remove();
					}
				},
			},
		];

		$(function() {
			$('.attachment-hndl').vboContextMenu({
				buttons: buttons,
			});
		});
	})(jQuery);
</script>