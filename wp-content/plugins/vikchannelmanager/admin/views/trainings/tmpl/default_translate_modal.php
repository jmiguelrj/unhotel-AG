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

VikBooking::getVboApplication()->loadSelect2();

JText::script('VCM_SELECT_LANG_FILTER');

?>

<div style="display: none;" id="translate-modal-wrapper">
	<div id="translate-modal">
		<p><?php echo JText::_('VCM_TRANSLATE_TRAINING_HELP'); ?></p>
		<select name="languages[]" id="translate-languages-select" multiple>
			<?php
			$options = [];
			
			foreach ($this->languages as $lang) {
				$options[] = JHtml::_('select.option', $lang['tag'], $lang['name']);
			}

			echo JHtml::_('select.options', $options);
			?>
		</select>
	</div>
</div>

<script>
	(function($) {
		'use strict';

		$(function() {
			$('#translate-languages-select').select2({
				placeholder: Joomla.JText._('VCM_SELECT_LANG_FILTER'),
				width: '100%',
			});
		});
	})(jQuery);
</script>