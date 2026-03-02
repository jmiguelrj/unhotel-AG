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

$config = $this->config;
$appParams = $this->appParams;
$accountsNumber = $this->accountsNumber;
$app_accounts = $this->app_accounts;
$app_acl = $this->app_acl;

$vik = new VikApplication(VersionListener::getID());

/**
 * @joomlaonly  we include the VBO main library to access the application class and normalize the back-end styles
 * (useful for the Yes/No buttons that have different styles depending on the Joomla series version)
 */
if (defined('_JEXEC')) {
	echo VikBooking::getVboApplication()->normalizeBackendStyles();
}

?>
<form action="index.php?option=com_vikchannelmanager" method="post" name="adminForm" id="adminForm">
	<div class="vcm-admin-container">

		<div class="vcm-config-maintab-left">

			<fieldset class="adminform">

				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('bell'); ?> <?php echo JText::_('VCMAPPNOTIF'); ?></legend>
					<div class="vcm-params-container">

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMAPPVBRESERV');?></div>
							<div class="vcm-param-setting">
								<?php
								if (VCMPlatformDetection::isWordPress()) {
									/**
									 * @wponly  Yes No buttons are displayed through the VikApplication class
									 */
									echo (new VikApplication)->printYesNoButtons('vbBookings', JText::_('VCMYES'), JText::_('VCMNO'), (int)(isset($appParams['vbBookings']['on']) && $appParams['vbBookings']['on']), 1, 0);
								} else {
									/**
									 * @joomlaonly
									 */
									echo VikBooking::getVboApplication()->printYesNoButtons('vbBookings', JText::_('VCMYES'), JText::_('VCMNO'), (int)(isset($appParams['vbBookings']['on']) && $appParams['vbBookings']['on']), 1, 0);
								}
								?>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMAPPTOTACC'); ?></div>
							<div class="vcm-param-setting">
								<span class="badge badge-info"><?php echo $accountsNumber; ?></span>
								<a href="index.php?option=com_vikchannelmanager&task=config#appaccounts" target="_blank" class="btn"><i class="vboicn-eye"></i></a>
							</div>
						</div>

					</div>
				</div>

			</fieldset>

		</div>

		<div class="vcm-config-maintab-right">
		<?php
		if ($app_accounts) {
			$groups = VikChannelManager::getJoomlaUserGroups();
			?>
			<fieldset class="adminform">

				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('user-shield'); ?> <?php echo JText::_('VCMAPPACCOUNTSACL'); ?></legend>
					<div class="vcm-params-container">

					<?php
					foreach ($app_accounts as $k => $v) {
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php VikBookingIcons::e('key'); ?> <?php echo $k; ?></div>
							<div class="vcm-param-setting">
								<input type="hidden" name="usersEmails[]" value="<?php echo htmlspecialchars($k); ?>"/>
								<select name="usersAcl[]">
								<?php
								foreach ($groups as $group) {
									$selected = ($group['id'] == $app_acl[$k] ? 'selected="selected"' : '');
									$level = VikChannelManager::getRecursiveUserLevel($group['parent_id'], $groups);
									for ($i = 0, $separator = ''; $i < $level; $i++) {
										$separator .= '- ';
									}
									?>
									<option value="<?php echo $group['id']; ?>"<?php echo isset($app_acl[$k]) && $group['id'] == $app_acl[$k] ? ' selected="selected"' : ''; ?>><?php echo $separator . $group['title']; ?></option>
									<?php
								}
								?>
								</select>
							</div>
						</div>
						<?php
					}
					?>

					</div>
				</div>

			</fieldset>
			<?php
		}
		?>
		</div>

	</div>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="option" value="com_vikchannelmanager"/>
	<input type="hidden" name="e4j_debug" value="<?php echo VikRequest::getInt('e4j_debug');?>"/>
</form>
