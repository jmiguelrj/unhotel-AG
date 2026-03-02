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

$log = $this->loopLog;

?>

<div
	class="log-row"
	data-level="<?php echo $this->escape($log->level ?? ''); ?>"
	data-date="<?php echo $this->escape(JHtml::_('date', $log->date ?? 'now', 'Y-m-d')); ?>"
	data-hour="<?php echo (int) JHtml::_('date', $log->date ?? 'now', 'G'); ?>"
>

	<div class="log-head">

		<?php if (!empty($log->date)): ?>
			<div class="log-date">
				<strong><?php echo JHtml::_('date', $log->date, 'd M Y H:i:s'); ?></strong>

				<?php if ($this->syncing ?? false): ?>
					<span class="label label-warning is-new">NEW</span>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if (!empty($log->level)):
			if (in_array($log->level, [VCMLogLevel::EMERGENCY, VCMLogLevel::ALERT, VCMLogLevel::CRITICAL, VCMLogLevel::ERROR])) {
				$levelClass = 'error';
			} else if (in_array($log->level, [VCMLogLevel::WARNING])) {
				$levelClass = 'warning';
			} else if (in_array($log->level, [VCMLogLevel::NOTICE])) {
				$levelClass = 'success';
			} else {
				$levelClass = 'info';
			}
			?>
			<span class="label label-<?php echo $levelClass; ?>"><?php echo strtoupper($log->level); ?></span>
		<?php endif; ?>

	</div>

	<div class="log-body">
		<div class="log-body-scrollable">
			<?php echo (new VCMAiHelperMarkdown($log->message))->toHtml(); ?>
		</div>
	</div>
	
</div>