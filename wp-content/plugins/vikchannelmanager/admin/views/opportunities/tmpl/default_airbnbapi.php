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
 * Render Airbnb API Opportunity
 * 
 * @since 	1.8.3 	older versions did not support such opportunities.
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

$impl_txt = JText::_('VCMENABLEOPP');
$dism_txt = JText::_('VCMDISMISS');
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
	// check whether this opportunity supports API as activation mode (all should support the MANUAL mode)
	$api_activation = (isset($opp_data->activation_modes) && is_array($opp_data->activation_modes) && in_array('API', $opp_data->activation_modes));

	// build action buttons (links used only for opportunities with no input fields, so no API mode)
	$action_btns = array();
	if (!$api_activation && $this->opp->status == 0 && $this->opp->action == 0) {
		// new opportunity never considered
		$attribts = '';
		if (isset($opp_data->activation_url)) {
			// MANUAL activation mode
			$attribts = ' target="_blank" data-manmode="1"';
		}
		// push implement button
		array_push(
			$action_btns,
			'<a href="index.php?option=com_vikchannelmanager&task=opportunity_action&opp_id=' . $this->opp->id . '&action=1"' . $attribts . ' class="btn btn-success vcm-opp-setdone">' . $impl_txt . '</a>'
		);
		// push dismiss button
		array_push(
			$action_btns,
			'<a href="index.php?option=com_vikchannelmanager&task=opportunity_action&opp_id=' . $this->opp->id . '&action=-1" class="btn btn-danger vcm-opp-confirmaction">' . $dism_txt . '</a>'
		);
	} elseif (!$api_activation && isset($opp_data->activation_url) && $this->opp->action == 1) {
		// opportunity with just MANUAL activation mode previously clicked, push "Done" button
		array_push(
			$action_btns,
			'<a href="index.php?option=com_vikchannelmanager&task=opportunity_action&opp_id=' . $this->opp->id . '&action=2" class="btn btn-primary">' . JText::_('VCMOPPSETDONE') . '</a>'
		);
	}

	// host_completion_percentage info
	if (isset($opp_data->host_completion_percentage)) {
		// adoption percentage of this opportunity type among all listings of the calling host
		?>
		<div class="vcm-opp-actions vcm-opp-categories">
			<span class="badge<?php echo $opp_data->host_completion_percentage >= 50 ? ' badge-info' : ''; ?>"><?php echo JText::_('VCM_ADOPTION_PCENTAGE') . ': ' . $opp_data->host_completion_percentage; ?>%</span>
		</div>
		<?php
	}

	// applicable listings for this opportunity
	$listings_involved = array();
	if (isset($opp_data->applicable_listing_ids) && is_array($opp_data->applicable_listing_ids) && count($opp_data->applicable_listing_ids)) {
		// always set the full list of applicable listings
		$listings_involved = $opp_data->applicable_listing_ids;
		?>
		<div class="vcm-opp-actions vcm-opp-implementation-data">
		<?php
		if (count($opp_data->applicable_listing_ids) > 6) {
			// do not display too many badges for each listing, but rather just a generic counter message
			?>
			<span class="badge badge-warning"><?php echo JText::sprintf('VCM_APPLICABLE_ROOMS_TOT', count($opp_data->applicable_listing_ids)); ?></span>
			<?php
		} else {
			// list every applicable listing (room) ID
			foreach ($opp_data->applicable_listing_ids as $applicable_listing_id) {
				// check if we have a name for this listing rather than printing the ID
				$display_listing_name = JText::_('VCMROOMCHANNELID') . ' ' . $applicable_listing_id;
				if (isset($this->ch_acc_rooms[$this->opp->channel]) && isset($this->ch_acc_rooms[$this->opp->channel][$this->opp->prop_first_param])) {
					if (isset($this->ch_acc_rooms[$this->opp->channel][$this->opp->prop_first_param][$applicable_listing_id])) {
						$display_listing_name = $this->ch_acc_rooms[$this->opp->channel][$this->opp->prop_first_param][$applicable_listing_id];
					}
				}
				?>
			<span class="badge badge-warning" title="<?php echo htmlentities($applicable_listing_id); ?>"><?php echo $display_listing_name; ?></span>
				<?php
			}
		}
		?>
		</div>
		<?php
	}

	// action buttons are links to the apposite controller
	if (count($action_btns)) {
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
		<?php
	}

	// check if this opportunity supports an API activation mode
	if ($api_activation && $this->opp->status == 0 && $this->opp->action == 0) {
		// display form if this opportunity was never actioned before
		$input_fields = array();
		if (isset($opp_data->input_fields) && is_array($opp_data->input_fields)) {
			$input_fields = $opp_data->input_fields;
		}
		?>
		<div class="vcm-opp-api-activation">
			<form action="index.php?option=com_vikchannelmanager" method="post">
				<div class="vcm-opp-api-activation-fields">
			<?php
			foreach ($input_fields as $kif => $api_field) {
				if (!is_object($api_field) || !isset($api_field->key) || !isset($api_field->value_constraint)) {
					// invalid input field object
					continue;
				}
				if (in_array($api_field->value_constraint->value_type, array('PERCENTAGE', 'INTEGER', 'DOUBLE'))) {
					// input type number
					$extra_attr = array();
					if (isset($api_field->value_constraint->min_value)) {
						array_push($extra_attr, 'min="' . (int)$api_field->value_constraint->min_value . '"');
					}
					if (isset($api_field->value_constraint->max_value)) {
						array_push($extra_attr, 'max="' . (int)$api_field->value_constraint->max_value . '"');
					}
					array_push($extra_attr, 'required');
					?>
					<div class="vcm-opp-api-activation-field">
						<label for="vcm-api-input-field-<?php echo $this->opp->id . '-' . $kif; ?>"><?php echo isset($api_field->key_label) ? $api_field->key_label : $api_field->key; ?></label>
						<input type="number" name="opp_fields[<?php echo $api_field->key; ?>]" id="vcm-api-input-field-<?php echo $this->opp->id . '-' . $kif; ?>" value="" <?php echo implode(' ', $extra_attr); ?>/>
					<?php
					if ($api_field->value_constraint->value_type == 'PERCENTAGE') {
						?>
						<span>%</span>
						<?php
					}
					?>
					</div>
					<?php
				} elseif ($api_field->value_constraint->value_type == 'OPTION') {
					// select with a fixed set of possibilities
					$option_vals = array();
					if (isset($api_field->value_constraint->options) && is_array($api_field->value_constraint->options)) {
						$option_vals = $api_field->value_constraint->options;
					}
					?>
					<div class="vcm-opp-api-activation-field">
						<label for="vcm-api-input-field-<?php echo $this->opp->id . '-' . $kif; ?>"><?php echo isset($api_field->key_label) ? $api_field->key_label : $api_field->key; ?></label>
						<select name="opp_fields[<?php echo $api_field->key; ?>]" id="vcm-api-input-field-<?php echo $this->opp->id . '-' . $kif; ?>" required>
							<option value=""></option>
						<?php
						foreach ($option_vals as $option_val) {
							?>
							<option value="<?php echo $option_val; ?>"><?php echo $option_val; ?></option>
							<?php
						}
						?>
						</select>
					</div>
					<?php
				} else {
					// default to an input text field
					?>
					<div class="vcm-opp-api-activation-field">
						<label for="vcm-api-input-field-<?php echo $this->opp->id . '-' . $kif; ?>"><?php echo isset($api_field->key_label) ? $api_field->key_label : $api_field->key; ?></label>
						<input type="text" name="opp_fields[<?php echo $api_field->key; ?>]" id="vcm-api-input-field-<?php echo $this->opp->id . '-' . $kif; ?>" value="" />
					</div>
					<?php
				}
			}
			?>
				</div>
				<div class="vcm-opp-api-activation-btns">
					<div class="vcm-opp-api-activation-btn">
						<input type="hidden" name="option" value="com_vikchannelmanager" />
						<input type="hidden" name="task" value="opportunity_action" />
						<input type="hidden" name="opp_id" value="<?php echo $this->opp->id; ?>" />
						<input type="hidden" name="action" value="1" />
					<?php
					// always print a dedicated hidden input field for each listing involved to apply the opportunity
					foreach ($listings_involved as $listing_id) {
						if (empty($listing_id)) {
							continue;
						}
						?>
						<input type="hidden" name="opp_listings[]" value="<?php echo $listing_id; ?>" />
						<?php
					}
					?>
						<button type="submit" class="btn btn-success"><?php echo $impl_txt; ?></button>
					</div>
					<div class="vcm-opp-api-activation-btn">
						<a href="index.php?option=com_vikchannelmanager&task=opportunity_action&opp_id=<?php echo $this->opp->id; ?>&action=-1" class="btn btn-danger vcm-opp-confirmaction"><?php echo $dism_txt; ?></a>
					</div>
				</div>
			</form>
		</div>
		<?php
	}
	?>
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
