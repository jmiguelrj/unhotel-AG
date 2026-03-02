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
 * Render Booking.com Opportunity
 * 
 * @since 	1.8.3 	the rendering was moved from default.php to default_bookingcom.php.
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
	<?php
	if (isset($opp_data->instructions)) {
		?>
		<div class="vcm-opp-instructions">
			<span><?php echo $opp_data->instructions; ?></span>
		</div>
		<?php
	}
	/**
	 * Opportunities with implementation type = PROVIDER do not need any buttons,
	 * like the high_demand_dates_inventory and they come with two extra properties
	 * in the object: "implementation_data" and "categories".
	 * 
	 * @since 	1.7.2
	 */
	$provider_implementation = (stripos($opp_data->implementation, 'provider') !== false);
	//
	$action_btns = array();
	if (!$provider_implementation && $this->opp->status == 0 && $this->opp->action == 0) {
		// new opportunity never considered
		$impl_txt = isset($opp_data->cta) ? $opp_data->cta : JText::_('VCMENABLEOPP');
		$dism_txt = JText::_('VCMDISMISS');
		$attribts = '';
		if (stripos($opp_data->implementation, 'redirect') !== false) {
			// implementation type REDIRECT
			$attribts = ' target="_blank" data-manmode="1"';
		}
		// push implement button
		array_push($action_btns, '<a href="index.php?option=com_vikchannelmanager&task=opportunity_action&opp_id=' . $this->opp->id . '&action=1"' . $attribts . ' class="btn btn-success vcm-opp-setdone">' . $impl_txt . '</a>');
		// push dismiss button
		array_push($action_btns, '<a href="index.php?option=com_vikchannelmanager&task=opportunity_action&opp_id=' . $this->opp->id . '&action=-1" class="btn btn-danger vcm-opp-confirmaction">' . $dism_txt . '</a>');
	} elseif (!$provider_implementation && $this->opp->action == 1 && stripos($opp_data->implementation, 'redirect') !== false) {
		// REDIRECT opportunity previously clicked as "implemented" can show "Done" button/action
		// push done button
		/**
		 * @todo 	for the moment we keep the link hidden as Booking.com returned HTTP_STATUS - UNKNOWN ERROR MESSAGE for action=DONE.
		 */
		array_push($action_btns, '<a href="index.php?option=com_vikchannelmanager&task=opportunity_action&opp_id=' . $this->opp->id . '&action=2" class="btn btn-primary" style="display: none;">' . JText::_('VCMOPPSETDONE') . '</a>');
	}

	// categories and implementation data info
	if (isset($opp_data->categories) && is_array($opp_data->categories) && count($opp_data->categories)) {
		// usually the categories are available for opportunities with implementation = PROVIDER like high_demand_dates_inventory
		?>
		<div class="vcm-opp-actions vcm-opp-categories">
		<?php
		foreach ($opp_data->categories as $opp_cat) {
			if (!is_object($opp_cat) || !isset($opp_cat->category_name)) {
				continue;
			}
			?>
			<span class="badge"><?php echo $opp_cat->category_name; ?></span>
			<?php
		}
		?>
		</div>
		<?php
	}
	if ($provider_implementation && isset($opp_data->implementation_data) && is_array($opp_data->implementation_data) && count($opp_data->implementation_data)) {
		?>
		<div class="vcm-opp-actions vcm-opp-implementation-data">
		<?php
		foreach ($opp_data->implementation_data as $implementation_data) {
			if (!is_object($implementation_data) || !isset($implementation_data->entity_id)) {
				continue;
			}
			$implementation_entity = explode('_', $implementation_data->entity_id);
			if (count($implementation_entity) != 2) {
				// we expect to get the room ID and the date separated with an underscore in "entity_id"
				continue;
			}
			$display_impl_info = JText::_('VCMROOMCHANNELID') . ' ' . $implementation_entity[0] . ' - ' . JText::_('VCMDASHNOTSDATE') . ' ' . $implementation_entity[1];
			if (isset($this->ch_acc_rooms[$this->opp->channel]) && isset($this->ch_acc_rooms[$this->opp->channel][$this->opp->prop_first_param])) {
				if (isset($this->ch_acc_rooms[$this->opp->channel][$this->opp->prop_first_param][$implementation_entity[0]])) {
					$ota_room_name = $this->ch_acc_rooms[$this->opp->channel][$this->opp->prop_first_param][$implementation_entity[0]];
					$display_impl_info = $ota_room_name . ' - ' . JText::_('VCMDASHNOTSDATE') . ' ' . $implementation_entity[1];
				}
			}
			?>
			<span class="badge badge-warning" title="<?php echo htmlentities($implementation_entity[0]); ?>"><?php echo $display_impl_info; ?></span>
			<?php
		}
		?>
		</div>
		<?php
	}
	?>
		<div class="vcm-opp-actions">
		<?php
		foreach ($action_btns as $btn) {
			?>
			<div class="vcm-opp-action">
				<?php echo $btn; ?>
			</div>
			<?php
		}
		?>
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
