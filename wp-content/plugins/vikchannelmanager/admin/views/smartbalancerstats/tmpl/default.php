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

$vik = new VikApplication(VersionListener::getID());
$df = VikChannelManager::getClearDateFormat();
$currency_symb = VikChannelManager::getCurrencySymb();

$debug_val = VikRequest::getInt('e4j_debug');
$debug_mode = ($debug_val == -1);
if ($debug_mode) {
	echo '<pre>'.print_r($this->bookings, true).'</pre><br/>';
	echo '<pre>'.print_r($this->rule, true).'</pre><br/>';
}

if (count($this->rule)) {
	?>
<h3 style="text-align: center;"><?php echo $this->rule['name']; ?></h3>
	<?php
}
?>
<form action="index.php?option=com_vikchannelmanager&task=smartbalancerstats" method="post" name="adminForm" id="adminForm" class="vcm-list-form">
	<div style="width: 100%; display: inline-block;" class="btn-toolbar" id="filter-bar">
		<div class="btn-group pull-left">
			<h3><?php echo JText::sprintf('VCMSMARTBALSTATSTOTB', $this->tot_smbal_bookings); ?></h3>
		</div>
<?php
if (!empty($this->min_ts) && !empty($this->max_ts)) {
	?>
		<div class="btn-group pull-right">
			<button type="button" class="btn" onclick="vcmResetFilters();"><i class="icon-remove"></i></button>
		</div>
		<div class="btn-group pull-right">
			<input name="to_date" size="13" value="" placeholder="<?php echo addslashes(JText::_('VCMTODATE')); ?>" class="vcmdatepickerav" id="to_date" autocomplete="off" type="text" />
		</div>
		<div class="btn-group pull-right">
			<input name="from_date" size="13" value="" placeholder="<?php echo addslashes(JText::_('VCMFROMDATE')); ?>" class="vcmdatepickerav" id="from_date" autocomplete="off" type="text" />
		</div>
	<?php
}
?>
	</div>
	<div class="table-responsive">
		<table cellpadding="4" cellspacing="0" border="0" width="100%" class="<?php echo $vik->getAdminTableClass(); ?> vcm-list-table">
		<?php echo $vik->openTableHead(); ?>
			<tr>
				<th class="title" width="50"><?php echo JHtml::_('grid.sort', 'VCMSMARTBALBID', 'bid', $this->orderingDir, $this->ordering); ?></th>
				<th class="title" width="200"><?php echo JHtml::_('grid.sort', 'VCMSMARTBALRNAME', 'rule_name', $this->orderingDir, $this->ordering); ?></th>
				<th class="title" width="200"><?php echo JHtml::_('grid.sort', 'VCMSMARTBALBTS', 'ts', $this->orderingDir, $this->ordering); ?></th>
				<th class="title center" width="100" align="center"><?php echo JHtml::_('grid.sort', 'VCMSMARTBALSAVEAM', 'saveamount', $this->orderingDir, $this->ordering); ?></th>
				<th class="title center" width="100" align="center"><?php echo JText::_('VCMSMARTBALBSTATUS'); ?></th>
				<th class="title center" width="100" align="center"><?php echo JText::_('VCMSMARTBALBFROMC'); ?></th>
			</tr>
		<?php echo $vik->closeTableHead(); ?>
		<?php
		$k = 0;
		$i = 0;
		for ($i = 0, $n = count($this->rows); $i < $n; $i++) {
			$row = $this->rows[$i];
			$rule_logs = !empty($row['logs']) ? json_decode($row['logs'], true) : array();
			$status_tag = '';
			if ($row['status'] == 'confirmed') {
				$status_tag = '<span class="label label-success" style="padding: 3px 6px;">'.JText::_('VBCONFIRMED').'</span>';
			} elseif ($row['status'] == 'cancelled') {
				$status_tag = '<span class="label label-error" style="padding: 3px 6px; background-color: #d9534f;">'.JText::_('VBCANCELLED').'</span>';
			}
			$otachannel = '';
			if (!empty($row['channel'])) {
				$channelparts = explode('_', $row['channel']);
				$otachannel = array_key_exists(1, $channelparts) && strlen($channelparts[1]) > 0 ? $channelparts[1] : ucwords($channelparts[0]);
				$otachannelclass = $otachannel;
				if (strstr($otachannelclass, '.') !== false) {
					$otaccparts = explode('.', $otachannelclass);
					$otachannelclass = $otaccparts[0];
				}
			}
			if (substr($row['saveamount'], -1) != '%') {
				//fixed amount
				$samfirst = substr($row['saveamount'], 0, 1);
				if ($currency_symb == '$') {
					$row['saveamount'] = str_replace($samfirst, $samfirst.$currency_symb, $row['saveamount']);
				} else {
					$row['saveamount'] = str_replace($samfirst, $samfirst.$currency_symb.' ', $row['saveamount']);
				}
			}
			?>
			<tr class="row<?php echo $k; ?>">
				<td><a href="index.php?option=com_vikbooking&task=editorder&cid[]=<?php echo $row['bid']; ?>" target="_blank"><?php echo $row['bid']; ?></a></td>
				<td><a href="index.php?option=com_vikchannelmanager&task=editsmartbalancer&cid[]=<?php echo $row['rule_id']; ?>" target="_blank"><?php echo htmlentities($row['rule_name']); ?></a></td>
				<td><span title="<?php echo date($df.' H:i:s', $row['ts']); ?>"><?php echo VikChannelManager::formatDate(date('Y-m-d H:i:s', $row['ts'])); ?></span></td>
				<td class="center"><?php echo $row['saveamount']; ?></td>
				<td class="center"><?php echo $status_tag; ?></td>
				<td class="center"><?php echo (!empty($row['channel']) ? "<span class=\"vbotasp ".strtolower($otachannelclass)."\">".$otachannel."</span>" : "<span class=\"vbotasp\">".JText::_('VBORDFROMSITE')."</span>"); ?></td>
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
		</table>
	</div>
	<input type="hidden" name="filter_order" value="<?php echo $this->ordering; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->orderingDir; ?>" />
	<input type="hidden" name="resubm" value="1" />
	<input type="hidden" name="option" value="com_vikchannelmanager" />
	<input type="hidden" name="task" value="smartbalancerstats" />
	<input type="hidden" name="cid[]" value="<?php echo count($this->rule) ? $this->rule['id'] : ''; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<?php echo '<br/>'.$this->navbut; ?>
</form>
<script type="text/javascript">
function vcmResetFilters() {
	jQuery('#from_date').val("");
	jQuery('#to_date').val("");
	jQuery('#adminForm').submit();
}
<?php
if (!empty($this->min_ts) && !empty($this->max_ts)) {
	$min_ts_info = getdate($this->min_ts);
	$max_ts_info = getdate($this->max_ts);
	?>
function vcmCheckDatesFilter(selectedDate, inst) {
	if (jQuery('#from_date').val().length && jQuery('#to_date').val().length) {
		jQuery('#adminForm').submit();
	}
}
jQuery(document).ready(function() {
	jQuery('.vcmdatepickerav:input').datepicker({
		dateFormat: "yy-mm-dd",
		minDate: new Date(<?php echo $min_ts_info['year']; ?>, <?php echo ($min_ts_info['mon'] - 1); ?>, <?php echo $min_ts_info['mday']; ?>, 0, 0, 0, 0),
		maxDate: new Date(<?php echo $max_ts_info['year']; ?>, <?php echo ($max_ts_info['mon'] - 1); ?>, <?php echo $max_ts_info['mday']; ?>, 0, 0, 0, 0),
		onSelect: vcmCheckDatesFilter
	});
	<?php
	if (!empty($this->from_date_ts) && !empty($this->to_date_ts)) {
		?>
	jQuery('#from_date').datepicker('setDate', '<?php echo date('Y-m-d', $this->from_date_ts); ?>');
	jQuery('#to_date').datepicker('setDate', '<?php echo date('Y-m-d', $this->to_date_ts); ?>');
		<?php
	}
	?>
});
	<?php
}
?>
</script>
