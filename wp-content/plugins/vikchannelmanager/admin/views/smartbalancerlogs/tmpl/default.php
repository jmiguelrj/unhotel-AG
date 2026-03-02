<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

$date_format = VikChannelManager::getClearDateFormat(true);

$debug_val = VikRequest::getInt('e4j_debug', '', 'request');
if ($debug_val == 1) {
	if ($this->row['type'] == 'rt') {
		//Show execute rule button only in debug mode and only for the rules of type 'rt'
		$api_key = md5(VikChannelManager::getApiKey());
		?>
		<a class="btn btn-primary" href="<?php echo JUri::root().'index.php?option=com_vikchannelmanager&task=smartbalancer_ratesrules&e4jauth='.$api_key.'&cid[]='.$this->row['id'].'&e4j_debug=-1'; ?>" target="_blank"><i class="vboicn-play2"></i> Execute Rule</a>
		<?php
	}
	//Show empty execution logs button only in debug mode
	?>
	<a class="btn btn-danger" href="<?php echo 'index.php?option=com_vikchannelmanager&task=rmsmartbalancerlogs&cid[]='.$this->row['id']; ?>" target="_blank"><i class="vboicn-bin"></i> Reset Execution Logs</a>
	<?php
}
if ($this->row['type'] == 'rt') {
	?>
	<a class="btn btn-primary" onclick="if(window !== window.parent){window.parent.location.href = this.href;return false;}else{return true;}" href="index.php?option=com_vikchannelmanager&task=smartbalancerstats&cid[]=<?php echo $this->row['id'].($debug_val == 1 ? '&e4j_debug=-1' : ''); ?>" target="_blank"><i class="vboicn-stats-dots"></i> <?php echo JText::_('VCMSMARTBALRSTATS'); ?></a>
	<?php
}

$logs_arr = json_decode($this->row['logs'], true);

$rooms_names_map = array();
$rateplans_map = $this->loadRatePlans();
$bulk_rates_cache = VikChannelManager::getBulkRatesCache();

?>

<div class="vcm-smartbal-logs-container">
	<h3><?php echo $this->row['name'].' - '.JText::_('VCMSMARTBALRLOGS'); ?></h3>
<?php
if (!is_array($logs_arr)) {
	?>
	<pre>
Raw Data could not be decoded.<br />
<?php echo $this->row['logs']; ?>
	</pre>
	<?php
} else {
	//logs are available
	?>
	<div class="vcm-smartbal-logs-inner">
	<?php
	if ($this->row['type'] == 'av') {
		//Rule for the automatic availability
		foreach ($logs_arr as $k => $execution) {
			$last_room_id = 0;
			$last_order_id = 0;
			?>
		<div class="vcm-smartbal-log-output">
			<h4><?php echo date($date_format.' H:i:s', $execution['ts']); ?></h4>
			<?php
			foreach ($execution['exec'] as $n => $exec) {
				if ((isset($exec['idroom']) && $exec['idroom'] != $last_room_id) || (isset($exec['idorder']) && $exec['idorder'] != $last_order_id)) {
					if (isset($exec['idroom'])) {
						$last_room_id = $exec['idroom'];
						?>
						<span class="vcm-smartbal-log-elem"><i class="vboicn-home"></i> <?php echo $this->getRoomName($exec['idroom']); ?></span>
						<?php
					}
					if (isset($exec['idorder'])) {
						$last_order_id = $exec['idorder'];
						?>
						<span class="vcm-smartbal-log-elem"><i class="vboicn-ticket"></i> Booking ID <?php echo $exec['idorder']; ?></span>
						<?php
					}
				}
				?>
			<div class="vcm-smartbal-log-txt"><?php echo nl2br($exec['log']); ?></div>
				<?php
			}
			?>
		</div>
			<?php
		}
	} elseif ($this->row['type'] == 'rt') {
		//Rule for the automatic rates adjustment
		foreach ($logs_arr as $k => $execution) {
			?>
		<div class="vcm-smartbal-log-output">
			<h4><?php echo date($date_format.' H:i:s', $execution['ts']); ?></h4>
			<?php
			foreach ($execution['exec'] as $n => $exec) {
				if (isset($exec['idroom'])) {
					//log for the rates updated on the site
					$room_names = array();
					foreach ($exec['idroom'] as $idr) {
						$rname = $this->getRoomName($idr);
						array_push($room_names, $rname);
						if ($rname != $idr) {
							$rooms_names_map[(int)$idr] = $rname;
						}
					}
					?>
			<span class="vcm-smartbal-log-elem"><i class="vboicn-home"></i> <?php echo implode(', ', $room_names); ?></span>
					<?php
				}
				if (isset($exec['dates'])) {
				?>
			<span class="vcm-smartbal-log-elem"><i class="vboicn-calendar"></i> <?php echo count($exec['dates']) == 2 && $exec['dates'][0] == $exec['dates'][1] ? $exec['dates'][0] : implode(', ', $exec['dates']); ?></span>
				<?php
				}
				if (!is_array($exec['log'])) {
					?>
			<div class="vcm-smartbal-log-txt"><?php echo nl2br($exec['log']); ?></div>
					<?php
				} else {
					//log for the rates updated on the channels (array)
					?>
			<span class="vcm-smartbal-log-elem"><i class="vboicn-tree"></i> <?php echo JText::_('VCMSMARTBALCHANNELSUPDLOG'); ?></span>
					<?php
					if (isset($exec['log']['channels_success']) && count($exec['log']['channels_success'])) {
						//Successful responses
						?>
			<div class="vcm-smartbal-log-txt vcm-smartbal-log-chs-succ"><?php echo $this->parseLogIds(implode(', ', $exec['log']['channels_success']), $rooms_names_map, $rateplans_map, $bulk_rates_cache); ?></div>
						<?php
					}
					if (isset($exec['log']['channels_warnings']) && count($exec['log']['channels_warnings'])) {
						//Responses with warning
						?>
			<div class="vcm-smartbal-log-txt vcm-smartbal-log-chs-warn"><?php echo $this->parseLogIds(nl2br(rtrim(implode("\n", $exec['log']['channels_warnings']), "\n")), $rooms_names_map, $rateplans_map, $bulk_rates_cache); ?></div>
						<?php
					}
					if (isset($exec['log']['channels_errors']) && count($exec['log']['channels_errors'])) {
						//Responses with errors
						?>
			<div class="vcm-smartbal-log-txt vcm-smartbal-log-chs-err"><?php echo $this->parseLogIds(nl2br(rtrim(implode("\n", $exec['log']['channels_errors']), "\n")), $rooms_names_map, $rateplans_map, $bulk_rates_cache); ?></div>
						<?php
					}
					if (isset($exec['log']['breakdown']) && count($exec['log']['breakdown'])) {
						//Rates Upload Breakdown
						?>
			<span class="vcm-smartbal-log-elem"><i class="vboicn-calculator"></i> <?php echo JText::_('VCMSMARTBALCHANNELSBRKDWN'); ?></span>
			<div class="vcm-smartbal-log-txt vcm-smartbal-log-chs-brkdwn"><?php echo $this->parseLogIds(nl2br(implode("\n", $exec['log']['breakdown'])), $rooms_names_map, $rateplans_map, $bulk_rates_cache); ?></div>
						<?php
					}
				}
			}
			?>
		</div>
			<?php
		}
	}
	?>
	</div>
	<?php
}
?>
</div>
<?php
if ($debug_val == 1) {
	echo '$logs_arr:<pre>'.print_r($logs_arr, true).'</pre><br/>';
	echo '$bulk_rates_cache:<pre>'.print_r($bulk_rates_cache, true).'</pre><br/>';
}
?>