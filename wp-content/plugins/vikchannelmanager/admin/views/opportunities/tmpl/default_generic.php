<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Render "Generic" Opportunity (not supported).
 * 
 * @since 	1.8.3
 */

$opp_data = json_decode($this->opp->data);
$opp_data = !is_object($opp_data) ? new stdClass : $opp_data;
$main_title = $this->opp->title;
if (empty($main_title)) {
	$main_title = ucwords(str_replace('_', ' ', $this->opp->identifier));
}
$action_class = '';
if ($this->opp->action == -1) {
	$action_class = ' vcm-opp-element-dismissed';
} elseif ($this->opp->action == 1) {
	$action_class = ' vcm-opp-element-implemented';
} elseif ($this->opp->action == 2) {
	$action_class = ' vcm-opp-element-done';
}
?>
<div class="vcm-opp-element<?php echo $action_class; ?>" data-opp-id="<?php echo $this->opp->id; ?>" data-opp-channel="<?php echo $this->opp->channel; ?>" data-opp-identifier="<?php echo $this->opp->identifier; ?>">
	<div class="vcm-opp-element-inner">
		<div class="vcm-opp-title">
			<span><?php echo $main_title; ?></span>
		</div>
		<div class="vcm-opp-descr">
			<span><?php echo isset($opp_data->description) ? $opp_data->description : ''; ?></span>
		</div>
		<div class="vcm-opp-prop-details">
			<div class="vcm-opp-prop-name">
				<span><?php echo $this->opp->prop_name; ?></span>
			</div>
		<?php
		if (!empty($this->channel_logo)) {
			?>
			<div class="vcm-opp-channel-logo">
				<img src="<?php echo $this->channel_logo; ?>" style="max-width: 100px;"/>
			</div>
			<?php
		}
		?>
		</div>
	</div>
</div>
