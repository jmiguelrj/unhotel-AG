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

$vik = new VikApplication(VersionListener::getID());
$typesmap = $this->reslogger->getTypesMap();

// filters
JHTML::_('behavior.calendar');
?>
<div class="vcm-list-form-filters vcm-btn-toolbar">
	<form action="index.php?option=com_vikchannelmanager&task=reslogs" method="post">
		<div id="filter-bar" class="btn-toolbar" style="width: 100%; display: inline-block;">
			<div class="btn-group pull-left">
				<?php echo JHTML::_('calendar', '', 'fromdate', 'fromdate', '%Y-%m-%d', array('class'=>'', 'size'=>'10',  'maxlength'=>'19', 'todayBtn' => 'true', 'placeholder' => JText::_('VCMFROMDATE'))); ?>
			</div>
			<div class="btn-group pull-left">
				<?php echo JHTML::_('calendar', '', 'todate', 'todate', '%Y-%m-%d', array('class'=>'', 'size'=>'10',  'maxlength'=>'19', 'todayBtn' => 'true', 'placeholder' => JText::_('VCMTODATE'))); ?>
			</div>
			<div class="btn-group pull-left">
				<select name="whatdate">
					<option value="day"<?php echo $this->filters['whatdate'] == 'day' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMRESLOGSDAYAFF'); ?></option>
					<option value="dt"<?php echo $this->filters['whatdate'] == 'dt' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMRESLOGSDT'); ?></option>
				</select>
			</div>
			<div class="btn-group pull-left">
				&nbsp;&nbsp;&nbsp;
			</div>
			<div class="btn-group pull-left vcm-multi-select-toolbar-wrap">
				<select name="roomids[]" multiple="multiple" id="filtroomids" class="vcm-multi-select" size="3">
					<option value=""></option>
				<?php
				foreach ($this->rooms as $rk => $rv) {
					?>
					<option value="<?php echo $rk; ?>"<?php echo in_array($rk, $this->filters['roomids']) ? ' selected="selected"' : ''; ?>><?php echo $rv; ?></option>
					<?php
				}
				?>
				</select>
			</div>
			<div class="btn-group pull-left">
				&nbsp;&nbsp;&nbsp;
			</div>
			<div class="btn-group pull-left">
				<input type="text" name="reskey" value="<?php echo htmlentities($this->filters['reskey']); ?>" placeholder="<?php echo addslashes(JText::_('VCMSEARCHNOTIF')); ?>" size="20" />
			</div>
			<div class="btn-group pull-left">
				&nbsp;&nbsp;&nbsp;
			</div>
			<div class="btn-group pull-left">
				<button type="submit" class="btn btn-secondary"><i class="vboicn-search"></i> <?php echo JText::_('VCMBCAHSUBMIT'); ?></button>
			</div>
		</div>
	</form>
</div>
<?php
//

if (count($this->rows)) {
	?>
<form action="index.php?option=com_vikchannelmanager&task=reslogs" method="post" name="adminForm" id="adminForm" class="vcm-list-form">
	<div class="table-responsive">
		<table cellpadding="4" cellspacing="0" border="0" width="100%" class="<?php echo $vik->getAdminTableClass(); ?> vcm-list-table">
		<?php echo $vik->openTableHead(); ?>
			<tr>
				<th class="title center" width="50"><?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'id', $this->reslogger->getDirection('DESC'), $this->reslogger->getOrdering('dt')); ?></th>
				<th class="title" width="150"><?php echo JHtml::_('grid.sort', 'VCMRESLOGSTYPE', 'type', $this->reslogger->getDirection('DESC'), $this->reslogger->getOrdering('dt')); ?></th>
				<th class="title" width="150"><?php echo JHtml::_('grid.sort', 'VCMRESLOGSDT', 'dt', $this->reslogger->getDirection('DESC'), $this->reslogger->getOrdering('dt')); ?></th>
				<th class="title" width="150"><?php echo JHtml::_('grid.sort', 'VCMCHANNEL', 'idchannel', $this->reslogger->getDirection('DESC'), $this->reslogger->getOrdering('dt')); ?></th>
				<th class="title center" width="50"><?php echo JHtml::_('grid.sort', 'VCMSMARTBALBID', 'idorder', $this->reslogger->getDirection('DESC'), $this->reslogger->getOrdering('dt')); ?></th>
				<th class="title center" width="150"><?php echo JHtml::_('grid.sort', 'VCMRESLOGSIDORDOTA', 'idorderota', $this->reslogger->getDirection('DESC'), $this->reslogger->getOrdering('dt')); ?></th>
				<th class="title center" width="150"><?php echo JHtml::_('grid.sort', 'VCMROOMVBID', 'idroomvb', $this->reslogger->getDirection('DESC'), $this->reslogger->getOrdering('dt')); ?></th>
				<th class="title center" width="150"><?php echo JHtml::_('grid.sort', 'VCMROOMCHANNELID', 'idroomota', $this->reslogger->getDirection('DESC'), $this->reslogger->getOrdering('dt')); ?></th>
				<th class="title center" width="150"><?php echo JHtml::_('grid.sort', 'VCMRESLOGSDAYAFF', 'day', $this->reslogger->getDirection('DESC'), $this->reslogger->getOrdering('dt')); ?></th>
				<th class="title" width="75"><?php echo JText::_('VCMRESLOGSDESCR'); ?></th>
			</tr>
		<?php echo $vik->closeTableHead(); ?>
		<?php
		$k = 0;
		$i = 0;
		for ($i = 0, $n = count($this->rows); $i < $n; $i++) {
			$row = $this->rows[$i];
			// channel logo
			$channel_logo = '';
			if (!empty($row['idchannel'])) {
				$channel_info = VikChannelManager::getChannel($row['idchannel']);
				if (count($channel_info)) {
					$channel_logo = VikChannelManager::getLogosInstance($channel_info['name'])->getLogoURL();
				}
			}
			//
			?>
			<tr class="row<?php echo $k; ?>">
				<td class="center"><?php echo $row['id']; ?></td>
				<td><?php echo isset($typesmap[$row['type']]) ? $typesmap[$row['type']] : $row['type']; ?></td>
				<td><?php echo $row['dt']; ?></td>
				<td>
				<?php
				if (!empty($channel_logo)) {
					?>
					<img src="<?php echo $channel_logo; ?>" style="max-width: 100px;"/>
					<?php
				} elseif (!empty($row['idchannel'])) {
					echo $row['idchannel'];
				} else {
					echo JText::_('VCMCOMPONIBE');
				}
				?>
				</td>
				<td class="center">
				<?php
				if (!empty($row['idorder'])) {
					?>
					<a href="index.php?option=com_vikbooking&task=editorder&cid[]=<?php echo $row['idorder']; ?>" target="_blank"><i class="vboicn-link"></i> <?php echo $row['idorder']; ?></a>
					<?php
				} else {
					?>
					---
					<?php
				}
				?>
				</td>
				<td class="center">
				<?php
				if (!empty($row['idorderota'])) {
					echo $row['idorderota'];
				} else {
					?>
					---
					<?php
				}
				?>
				</td>
				<td class="center">
				<?php
				if (!empty($row['idroomvb'])) {
					echo isset($this->rooms[$row['idroomvb']]) ? $this->rooms[$row['idroomvb']] . ' (#'.$row['idroomvb'].')' : $row['idroomvb'];
				} else {
					?>
					---
					<?php
				}
				?>
				</td>
				<td class="center">
				<?php
				if (!empty($row['idroomota'])) {
					echo $row['idroomota'];
				} else {
					?>
					---
					<?php
				}
				?>
				</td>
				<td class="center"><?php echo $row['day']; ?></td>
				<td><?php echo $row['descr']; ?></td>
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
		</table>
	</div>
	<input type="hidden" name="filter_order" value="<?php echo $this->reslogger->getOrdering('dt'); ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->reslogger->getDirection('DESC'); ?>" />
	<input type="hidden" name="option" value="com_vikchannelmanager" />
	<input type="hidden" name="task" value="reslogs" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php
	foreach ($this->filters as $kf => $vf) {
		if (is_scalar($vf)) {
			?>
	<input type="hidden" name="<?php echo $kf; ?>" value="<?php echo $vf; ?>" />
			<?php
		} else {
			foreach ($vf as $subvf) {
				?>
	<input type="hidden" name="<?php echo $kf; ?>[]" value="<?php echo $subvf; ?>" />
				<?php
			}
		}
	}
	?>
	<?php echo JHTML::_( 'form.token' ); ?>
	<?php echo '<br/>'.$this->navbut; ?>
</form>
<?php
} else {
	?>
<p class="warn">-----</p>
<form action="index.php?option=com_vikchannelmanager" method="post" name="adminForm" id="adminForm">
	<input type="hidden" name="option" value="com_vikchannelmanager" />
	<input type="hidden" name="task" value="" />
</form>
	<?php
}
?>

<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('#filtroomids').select2({
		placeholder: '<?php echo addslashes(JText::_('VCMPVIEWORDERSVBTHREE')); ?>',
		allowClear: false,
		width: 300
	});
	jQuery('#fromdate').val('<?php echo $this->filters['fromdate'] ?>').attr('data-alt-value', '<?php echo $this->filters['fromdate'] ?>');
	jQuery('#todate').val('<?php echo $this->filters['todate'] ?>').attr('data-alt-value', '<?php echo $this->filters['todate'] ?>');
});
</script>
