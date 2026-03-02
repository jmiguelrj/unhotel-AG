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

<legend class="adminlegend"><?php echo JText::_('VCMMENUTACDETAILS'); ?></legend>

<div class="vcm-params-container">

	<div class="vcm-param-container">
		<div class="vcm-param-label"><?php echo JText::_('VCM_TITLE'); ?></div>
		<div class="vcm-param-setting">
			<input type="text" name="title" value="<?php echo $this->escape($this->training->title ?? ''); ?>" />
			<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_TRAINING_TITLE_DESC'); ?></span>
		</div>
	</div>

	<div class="vcm-param-container">
		<div class="vcm-param-label"><?php echo JText::_('VCM_AIRBNB_LISTINGS'); ?></div>
		<div class="vcm-param-setting">
			<select name="listing_selection">
				<?php
				$options = [
					JHtml::_('select.option', '*', JText::_('VCM_ALL_LISTINGS')),
					JHtml::_('select.option', 0, JText::_('VCM_ALL_LISTINGS_SELECTED')),
					JHtml::_('select.option', 1, JText::_('VCM_ALL_LISTINGS_EXCEPT')),
				];

				echo JHtml::_('select.options', $options, 'value', 'text', $this->training->listing_selection ?? '*');
				?>
			</select>
			<div class="listing-selection-child" style="margin-top: 10px;<?php echo ($this->training->listing_selection ?? '*') !== '*' ? '' : 'display: none;'; ?>">
				<select name="id_listing[]" multiple>
					<?php
					$options = [];

					foreach ($this->rooms as $room) {
						$options[] = JHtml::_('select.option', $room['id'], $room['name']);
					}

					echo JHtml::_('select.options', $options, 'value', 'text', $this->training->id_listing ?? 0);
					?>
				</select>
			</div>
			<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_TRAINING_LISTING_DESC'); ?></span>
		</div>
	</div>

	<div class="vcm-param-container">
		<div class="vcm-param-label"><?php echo JText::_('VCMBCAHLANGUAGE'); ?></div>
		<div class="vcm-param-setting">
			<select name="language">
				<?php
				$options = [];

				foreach (VikBooking::getVboApplication()->getKnownLanguages() as $lang) {
					$options[] = JHtml::_('select.option', $lang['tag'], $lang['name']);
				}

				echo JHtml::_('select.options', $options, 'value', 'text', $this->training->language ?? null);
				?>
			</select>
			<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_TRAINING_LANGUAGE_DESC'); ?></span>
		</div>
	</div>

</div>

<script>
	(function($) {
		'use strict';

		$(function() {
			$('select[name="listing_selection"]').on('change', function() {
				if ($(this).val() === '*') {
					$('.listing-selection-child').hide();
				} else {
					$('.listing-selection-child').show();
				}
			});

			$('select[name="id_listing[]"]').select2({
				width: '100%',
			});
		})
	})(jQuery);
</script>