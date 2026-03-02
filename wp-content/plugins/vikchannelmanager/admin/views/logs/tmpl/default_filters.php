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
	.logs-filters {
		display: flex;
		align-items: center;
		gap: 10px;
		padding: 10px;
		background: var(--vcm-body-bg-color);
		border-bottom: 1px solid var(--vcm-middle-color-btn);
	}
	.vik-context-menu li a .button-icon {
		width: 16px;
	}

	#log-settings-handle {
		position: relative;
	}
	#log-settings-handle:not([data-count="0"]):before {
		content: attr(data-count);
		position: absolute;
		top: 0;
		right: 0;
		background: #b00;
		color: #fff;
		padding: 0 4px;
		font-size: 10px;
		border-radius: 4px;
		transform: translate(25%, -25%);
		display: flex;
		align-items: center;
		height: 16px;
	}
</style>

<div class="logs-filters">

	<a href="javascript:void(0)" id="log-settings-handle" data-count="0"><?php VikBookingIcons::e('cog', 'fa-2x'); ?></a>

	<input type="text" name="search" id="log-search-filter" style="flex: 1;" placeholder="Search logs here..." />

	<input type="date" name="date" id="log-date-filter" />

	<select name="hour" id="log-hour-filter">
		<?php
		$hours = array_merge(
			[
				JHtml::_('select.option', '', '- select hour -'),
			],
			array_map(fn($hour) => JHtml::_('select.option', $hour, str_pad($hour, 2, '0', STR_PAD_LEFT)), range(0, 23))
		);

		echo JHtml::_('select.options', $hours);
		?>
	</select>

	<button type="button" class="btn btn-secondary" id="log-clear-btn">
		<?php VikBookingIcons::e('broom'); ?>
	</button>

</div>

<script>
	(function($) {
		'use strict';

		const filterLogs = () => {
			const filters = {
				search: $('#log-search-filter').val(),
				date: $('#log-date-filter').val(),
				hour: $('#log-hour-filter').val(),
				levels: getSelectedLevels(),
			};

			let found = 0, missed = 0;

			const logsTable = $('.logs-table')[0];

			// auto-scroll to bottom only if the current scroll top is close to the end (within 100px of margin)
			let shouldScroll = Math.abs(logsTable.scrollTop + logsTable.clientHeight - logsTable.scrollHeight) <= 100;

			$('.logs-table .log-row').each(function() {
				const log = {
					text: ($(this).find('.log-body-scrollable').text() || '').trim().toLowerCase(),
					level: $(this).data('level'),
					date: $(this).data('date'),
					hour: $(this).data('hour'),
				};

				if (isMatchingLog(log, filters)) {
					$(this).show();
					found++;
				} else {
					$(this).hide();
					missed++;
				}
			});

			if (found) {
				$('#no-matching-logs').hide();

				if (shouldScroll) {
					logsTable.scrollTop = logsTable.scrollHeight;
				}
			} else {
				$('#no-matching-logs').show();
			}

			if (typeof sessionStorage !== 'undefined') {
				sessionStorage.setItem('vcmlogsfilters', JSON.stringify(filters));
			}

			if (missed) {
				$('.logs-file-status .status-count').text(found + ' logs found out of ' + (found + missed));	
			} else {
				$('.logs-file-status .status-count').text(found + ' logs');	
			}
		}

		const isMatchingLog = (log, filters) => {
			if (filters.date && log.date != filters.date) {
				return false;
			}

			if (filters.hour && log.hour != filters.hour) {
				return false;
			}

			if (filters.search && log.text.indexOf((filters.search + '').toLowerCase()) === -1) {
				return false;
			}

			if (filters?.levels?.length && filters.levels.indexOf(log.level) === -1) {
				return false;
			}

			return true;
		}

		const createContextMenuButtons = (filters) => {
			let buttons = [];

			buttons.push({
				text: 'Levels',
				class: 'btngroup',
				disabled: true,
			});

			const levels = [
				'emergency',
				'alert',
				'critical',
				'error',
				'warning',
				'notice',
				'info',
				'debug',
			];

			levels.forEach((level) => {
				buttons.push({
					text: level.substr(0, 1).toUpperCase() + level.substr(1),
					id: level,
					selected: filters.levels && filters.levels.indexOf(level) !== -1,
					class: 'level',
					icon: function() {
						return this.selected ? 'fas fa-check' : '';
					},
					action: function(root, config) {
						this.selected = !this.selected;

						let count = parseInt($(root).attr('data-count'));
						$(root).attr('data-count', count + (this.selected ? 1 : -1));

						filterLogs();
					},
					reset: function(root, config) {
						if (this.selected) {
							this.action(root, config);
						}
					},
				});
			});

			buttons[buttons.length - 1].separator = true;

			buttons.push({
				text: 'Quit',
				icon: '<?php echo VikBookingIcons::i('sign-out'); ?>',
				action: (root, config) => {
					document.location.href = '<?php echo VCMFactory::getPlatform()->getUri()->admin('index.php?option=com_vikchannelmanager', false); ?>';
				}
			});

			return buttons;
		}

		const getSelectedLevels = () => {
			const config = $('#log-settings-handle').vboContextMenu('config');
			
			let levels = [];

			config.buttons.forEach((btn) => {
				if (btn.class === 'level' && btn.selected) {
					levels.push(btn.id);
				}
			});

			return levels;
		}

		$(function() {
			$('#log-search-filter, #log-date-filter, #log-hour-filter').on('change', filterLogs);

			$('#log-clear-btn').on('click', () => {
				$('#log-search-filter').val('');
				$('#log-date-filter').val('');
				$('#log-hour-filter').val('');

				const config = $('#log-settings-handle').vboContextMenu('config');

				config.buttons.forEach((btn) => {
					if (btn.reset) {
						btn.reset($('#log-settings-handle'), config);
					}
				});

				filterLogs();
			});

			let filters = {};

			try {
				filters = JSON.parse(sessionStorage.getItem('vcmlogsfilters')) || {};
			} catch (err) {

			}

			$('#log-settings-handle').vboContextMenu({
				buttons: createContextMenuButtons(filters),
			});

			if (!$.isEmptyObject(filters)) {
				$('#log-search-filter').val(filters.search || '');
				$('#log-date-filter').val(filters.date);
				$('#log-hour-filter').val(filters.hour);
				$('#log-settings-handle').attr('data-count', filters?.levels?.length || 0);
			}

			// scroll logs down before to filter
			let logsTable = $('.logs-table')[0];
			logsTable.scrollTop = logsTable.scrollHeight;

			filterLogs();
		});
	})(jQuery)
</script>