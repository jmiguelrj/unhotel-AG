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

<style>
	.vcm-logs-manager {
		display: flex;
		background: var(--vcm-body-bg-color);
		color: var(--vcm-body-text-color);
		border: 1px solid var(--vcm-middle-color-btn);
		overflow: hidden;
		position: fixed;
		top: 0;
		left: 0;
		width: 100vw;
		height: 100vh;
		z-index: 99999;
		box-sizing: border-box;
	}
	.vcm-logs-manager * {
		box-sizing: border-box;
	}
	.vcm-logs-manager .logs-navigator {
		min-width: 250px;
		max-width: 250px;
		overflow-x: scroll;
		background: var(--vcm-body-bg-color);
		border-right: 1px solid var(--vcm-middle-color-btn);
		padding: 10px;
	}
	.vcm-logs-manager .logs-body {
		width: calc(100% - 250px);
		position: relative;
	}
	.vcm-logs-manager .logs-body .logs-container {
		background: var(--vcm-main-bg-color);
		height: 100%;
		position: relative;
	}

	.vcm-logs-manager .logs-navigator ul {
		padding: 0 0 0 15px;
		margin: 0 0 0 5px;
		border-left: 1px solid rgba(0,0,0,.2);
	}
	.vcm-logs-manager .logs-navigator ul li {
		margin: 4px 0 0 0;
		position: relative;
	}
	.vcm-logs-manager .logs-navigator ul li:before {
		position: absolute;
		top: 9px;
		left: -15px;
		width: 10px;
		height: 1px;
		margin: auto;
		content: "";
		background-color: rgba(0,0,0,.2);
	}
	.vcm-logs-manager .logs-navigator li ul {
		margin-left: 5px;
	}
	.vcm-logs-manager .logs-navigator > a:not(:first-of-type) {
		margin-top: 4px;
	}
	.vcm-logs-manager .logs-navigator a {
		display: flex;
		align-items: center;
	}
	.vcm-logs-manager .logs-navigator a.folder i {
		width: 18px;
	}
	.vcm-logs-manager .logs-navigator a.file i {
		margin-right: 4px;
	}
	.vcm-logs-manager .logs-navigator a.file.selected i {
		color: #080;
	}

	.vcm-logs-manager .logs-body .empty-placeholder {
		position: absolute;
		top: 50%;
		left: 50%;
		transform: translate(-50%, -50%);
		text-align: center;
		opacity: 0.6;
	}
	.vcm-logs-manager .empty-placeholder i {
		font-size: 96px;
	}
	.vcm-logs-manager .empty-placeholder p {
		font-size: 24px;
		margin: 20px 0 0;
	}
</style>

<form action="index.php?option=com_vikchannelmanager&view=logs" method="post" name="adminForm" id="adminForm">

	<?php echo JHtml::_('form.token'); ?>

	<input type="hidden" name="selectedfile" value="<?php echo $this->escape($this->filters['file']); ?>" />

	<input type="hidden" name="option" value="com_vikchannelmanager" />
	<input type="hidden" name="view" value="logs" />
	<input type="hidden" name="task" value="" />

</form>

<!-- body -->

<div class="vcm-logs-manager">

	<!-- navigator -->

	<div class="logs-navigator">
		<?php
		if ($this->tree) {
			foreach ($this->tree['logs']['files'] as $node) {
				echo $this->buildNode($node);
			}
		} else {
			?><div style="text-align: center; margin-top: 6px;"><?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></div><?php
		}
		?>
	</div>

	<!-- editor -->

	<div class="logs-body">
		<?php if ($this->filters['file']): ?>
			<div class="logs-container">
				<?php
				echo $this->loadTemplate('filters');
				echo $this->loadTemplate('logs');
				echo $this->loadTemplate('status');
				?>
			</div>
		<?php else: ?>
			<div class="empty-placeholder">
				<?php VikBookingIcons::e('life-ring'); ?>
				<p>Select a file first.</p>
			</div>
		<?php endif; ?>
	</div>

</div>

<script>
	(function($) {
		'use strict';

		$(function() {
			// handle folders click
			$('.logs-navigator a.folder').on('click', function() {
				// get UL next to button
				const ul = $(this).next('ul');

				if (ul.is(':visible')) {
					// hide list
					ul.hide();
					// back to the closed folder icon
					$(this).find('i').attr('class', 'fas fa-folder');
				} else {
					// show list
					ul.show();
					// set open folder icon
					$(this).find('i').attr('class', 'fas fa-folder-open');
				}
			});

			// handle files click
			$('.logs-navigator a.file').on('click', function() {
				// register the paths of the selected file within the form
				document.adminForm.selectedfile.value = $(this).data('path');
				// submit the form
				document.adminForm.submit();
			});
		});
	})(jQuery);
</script>