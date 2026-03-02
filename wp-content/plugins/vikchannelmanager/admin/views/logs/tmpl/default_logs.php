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

<style>
	.logs-table {
		padding: 10px 20px;
		height: calc(100% - 95px);
		overflow-y: scroll;
	}
	.logs-table .log-row {
		border: 1px solid var(--vcm-input-style-nested-deactive);
		border-radius: 8px;
		margin: 10px 0;
		box-shadow: 0 4px 8px rgba(48, 48, 48, 0.2);
		background: var(--vcm-config-bg-color);
	}
	.logs-table .log-row .log-head {
		padding: 10px;
		display: flex;
		justify-content: space-between;
		align-items: center;
		gap: 10px;
		border-bottom: 1px solid var(--vcm-input-style-nested-deactive);
	}
	.logs-table .log-row .log-body {
		padding: 10px;
	}
	.logs-table .log-row .log-body .log-body-scrollable {
		overflow-x: scroll;
	}

	.logs-table .log-body blockquote {
		color: #7f8fa4;
		border-left: 4px solid #eaeaea;
		background: var(--vcm-body-bg-color);
		margin: 10px 0;
		padding: 10px 0 10px 12px;
	}
</style>

<div id="no-matching-logs" class="empty-placeholder" style="display: <?php echo $this->logData ? 'none' : 'block'; ?>;">
	<?php VikBookingIcons::e('sad-cry'); ?>
	<p><?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></p>
</div>

<div class="logs-table">
	<?php
	foreach ($this->logData as $log) {
		$this->loopLog = $log;
		echo $this->loadTemplate('log');	
	}
	?>
</div>

<script>
	(function($) {
		'use strict';

		let totalLogsCount = <?php echo count($this->logData); ?>;

		const syncLogs = () => {
			setTimeout(() => {
				$('.logs-file-status .status-count').append('<i class="fas fa-spinner fa-spin" style="margin-left: 6px;"></i>');

				downloadNewLogs().then((response) => {
					if (response.logs.length == 0) {
						return;
					}

					const table = $('.vcm-logs-manager .logs-table');
					// auto-scroll to bottom only if the current scroll top is close to the end (within 100px of margin)
					let shouldScroll = Math.abs(table[0].scrollTop + table[0].clientHeight - table[0].scrollHeight) <= 100;

					table.find('.log-row .is-new').remove();

					response.logs.forEach((log) => {
						table.append(log);
					});

					totalLogsCount += response.logs.length;

					if (shouldScroll) {
						table[0].scrollTop = table[0].scrollHeight;
					}

					$('#log-search-filter').trigger('change');

					$('.logs-file-status .status-filesize').text(response.filesize);
				}).catch((err) => {
					console.error(err);
				}).finally(() => {
					syncLogs();

					$('.logs-file-status .status-count i.fa-spin').remove();
				});
			}, 15000);
		}

		const downloadNewLogs = () => {
			return new Promise((resolve, reject) => {
				$.ajax({
					type: 'POST',
					url: '<?php echo VCMFactory::getPlatform()->getUri()->ajax('index.php?option=com_vikchannelmanager&view=logs&tmpl=component'); ?>',
					data: {
						selectedfile: '<?php echo $this->filters['file']; ?>',
						sync: 1,
						index: totalLogsCount,
					},
				}).done((response) => {
					if (Array.isArray(response?.logs)) {
						resolve(response);
					} else {
						reject(['Invalid response', response]);
					}
				}).fail((err) => {
					reject(err)
				});
			});
		}

		$(function() {
			syncLogs();
		});
	})(jQuery);
</script>