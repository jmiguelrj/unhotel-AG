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
$vbo_app = VikChannelManager::getVboApplication();
?>

<form action="index.php?option=com_vikchannelmanager" method="post" name="adminForm" id="adminForm" class="vcm-list-form">
	<div class="vcm-icalchannels-admin-wrap">
		<div class="vcm-icalchannels-admin-left">
			<div class="vcm-icalchannels-admin-inner">
				<h3><?php echo JText::_((count($this->editchannel) ? 'VCMEDITICALCH' : 'VCMADDNEWICALCH')); ?></h3>
				<div class="vcm-icalchannel-param-wrap">
					<div class="vcm-icalchannel-param-label">
						<label for="ical_channel_name"><?php echo JText::_('VCMROOMSRELATIONSNAME'); ?></label>
					</div>
					<div class="vcm-icalchannel-param-setting">
						<input type="text" name="ical_channel_name" id="ical_channel_name" size="32" value="<?php echo count($this->editchannel) ? htmlspecialchars($this->editchannel['name']) : ''; ?>" />
					</div>
				</div>
				<div class="vcm-icalchannel-param-wrap">
					<div class="vcm-icalchannel-param-label">
						<label for="ical_channel_logo"><?php echo JText::_('VCMBCARCROOMIMAGETITLE'); ?></label>
					</div>
					<div class="vcm-icalchannel-param-setting">
					<?php
					if ($vbo_app !== false && method_exists($vbo_app, 'getMediaField')) {
						echo $vbo_app->getMediaField('ical_channel_logo', (count($this->editchannel) && !empty($this->editchannel['logo']) ? $this->editchannel['logo'] : null));
					} else {
						?>
						<input type="text" name="ical_channel_logo" id="ical_channel_logo" value="<?php echo count($this->editchannel) ? htmlspecialchars($this->editchannel['logo']) : ''; ?>" size="32" />
						<?php
					}
					if (count($this->editchannel)) {
						// print the ID of the record being edited
						?>
						<input type="hidden" name="ical_channel_id" value="<?php echo $this->editchannel['id']; ?>" />
						<?php
					}
					?>
					</div>
				</div>
			</div>
		</div>
		<div class="vcm-icalchannels-admin-right">
			<div class="vcm-icalchannels-admin-inner">
			<?php
			if (!count($this->channels)) {
				?>
				<p class="warn"><?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></p>
				<?php
			} else {
			?>
				<table cellpadding="4" cellspacing="0" border="0" width="100%" class="<?php echo $vik->getAdminTableClass(); ?> vcm-list-table">
				<?php echo $vik->openTableHead(); ?>
					<tr>
						<th width="1%">
							<?php echo $vik->getAdminToggle(count($this->channels)); ?>
						</th>
						<th class="title" width="1%"><?php echo JText::_('JGRID_HEADING_ID'); ?></th>
						<th class="title" width="40%"><?php echo JText::_('VCMROOMSRELATIONSNAME'); ?></th>
						<th class="title center" width="10%" align="center"><?php echo JText::_('VCMBCARCROOMIMAGETITLE'); ?></th>
					</tr>
				<?php echo $vik->closeTableHead(); ?>
				<?php
				$k = 0;
				$i = 0;
				for ($i = 0, $n = count($this->channels); $i < $n; $i++) {
					$row = $this->channels[$i];
					?>
					<tr class="row<?php echo $k; ?>">
						<td><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row['id']; ?>" onClick="<?php echo $vik->checkboxOnClick(); ?>"></td>
						<td><?php echo $row['id']; ?></td>
						<td><a href="index.php?option=com_vikchannelmanager&task=editicalchannel&cid[]=<?php echo $row['id']; ?>"><?php echo htmlentities($row['name']); ?></a></td>
						<td><?php echo !empty($row['logo']) ? '<img src="' . JUri::root() . $row['logo'] . '" class="vcm-customical-channel-logo" />' : '/'; ?></td>
					</tr>
					<?php
					$k = 1 - $k;
				}
				?>
				</table>
			<?php
			}
			?>
			</div>
		</div>
	</div>
	<input type="hidden" name="task" value="icalchannels" />
	<input type="hidden" name="option" value="com_vikchannelmanager" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<?php echo '<br/>'.$this->navbut; ?>
</form>
