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

?>
<div class="vcm-revscore-container">
	<div class="vcm-revscore-info vcm-revscore-info-property">
		<span class="vcm-revscore-propname"><?php echo $this->glob_score['prop_name']; ?></span>
		<span class="vcm-revscore-lastdate"><?php echo $this->glob_score['last_updated']; ?></span>
	</div>
	<div class="vcm-revscore-info">
		<div class="vcm-revscore-logo">
		<?php
		if (!empty($this->channel_logo)) {
			?>
			<img src="<?php echo $this->channel_logo; ?>" style="max-width: 100px;"/>
			<?php
		} elseif (!empty($this->glob_score['channel'])) {
			echo '<span>' . $this->glob_score['channel'] . '</span>';
		} else {
			echo '<span>' . JText::_('VCMCOMPONIBE') . '</span>';
		}
		?>
		</div>
	</div>
	<div class="vcm-revscore-info">
		<div class="vcm-revscore-score-wrap">
			<div class="vcm-revscore-score-point vcm-revscore-score-point-<?php echo preg_replace("/[^a-z0-9]/", '', strtolower($this->glob_score['channel'])); ?>">
				<span><?php echo $this->glob_score['score']; ?></span>
			</div>
		</div>
	</div>
</div>
