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

VCM::load_complex_select();

$dbo = JFactory::getDbo();
$vcm_app = new VikApplication(VersionListener::getID());
$listings_mapped = array();

if ($this->module['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI) {
	$q = "SELECT * FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel`=" . (int)$this->module['uniquekey'] . ";";
	$dbo->setQuery($q);
	$dbo->execute();
	if ($dbo->getNumRows()) {
		$listings_mapped = $dbo->loadAssocList();
	}
}

if (class_exists('VikBookingIcons') && count($listings_mapped)) {
	// we can proceed and display the custom settings for this channel
	$current_custom_settings = new stdClass;
	if (isset($this->config['custom_ch_settings_' . $this->module['uniquekey']])) {
		$current_custom_settings = json_decode($this->config['custom_ch_settings_' . $this->module['uniquekey']]);
		$current_custom_settings = !is_object($current_custom_settings) ? (new stdClass) : $current_custom_settings;
	} else {
		// configuration record is missing
		$q = "INSERT INTO `#__vikchannelmanager_config` (`param`, `setting`) VALUES ('custom_ch_settings_" . $this->module['uniquekey'] . "', " . $dbo->quote(json_encode($current_custom_settings)) . ");";
		$dbo->setQuery($q);
		$dbo->execute();
	}
	?>
	<div class="vcm-params-block">
		<div class="vcm-param-container">
			<div class="vcm-param-setting">
				<span class="vcm-param-setting-comment"><?php VikBookingIcons::e('exclamation-triangle'); ?> <?php echo JText::_('VCM_AIRBNB_PRCSETTINGS_HELP2'); ?></span>
				<span class="vcm-param-setting-comment"><?php VikBookingIcons::e('info-circle'); ?> <?php echo JText::_('VCM_AIRBNB_PRCSETTINGS_HELP3'); ?></span>
			</div>
		</div>
	</div>

	<div class="vcm-param-container vcm-param-container-wrap">
		<div class="vcm-param-label">
			<?php echo JText::_('VCM_AIRBNB_SECDEP'); ?> <?php echo $vcm_app->createPopover(array('title' => JText::_('VCM_AIRBNB_SECDEP'), 'content' => JText::_('VCM_AIRBNB_SECDEP_HELP'))); ?>
		</div>
		<div class="vcm-param-setting">
			<div id="vcm-custom-chsettings-secdep" style="<?php echo !isset($current_custom_settings->secdep) ? 'display: none;' : ''; ?>">
		<?php
		if (isset($current_custom_settings->secdep)) {
			$field_counter = 0;
			foreach ($current_custom_settings->secdep as $k => $fields) {
				?>
				<div class="vcm-custom-chsettings-fields">
					<div class="vcm-custom-chsettings-field-rm">
						<button type="button" class="btn btn-danger" onclick="vcmRemoveAirbnbCustomSetting(this);"><?php VikBookingIcons::e('times-circle'); ?></button>
					</div>
					<div class="vcm-custom-chsettings-field">
						<div class="vcm-custom-chsettings-field-lbl"><?php echo JText::_('VCMBCAHAMOUNT'); ?> <sup>*</sup></div>
						<div class="vcm-custom-chsettings-field-val">
							<input type="number" name="cust_ch_settings[secdep][<?php echo $field_counter; ?>][amount]" value="<?php echo isset($fields->amount) ? $fields->amount : '0'; ?>" min="0" step="any" />
						</div>
					</div>
					<div class="vcm-custom-chsettings-field">
						<div class="vcm-custom-chsettings-field-lbl"><?php echo JText::_('VCM_AIRBNB_LISTINGS'); ?> <sup>*</sup></div>
						<div class="vcm-custom-chsettings-field-val">
							<select name="cust_ch_settings[secdep][<?php echo $field_counter; ?>][listings][]" multiple="multiple" size="6" class="vcm-cust-ch-settings-nicesel vcm-cust-ch-settings-nicesel-saved">
							<?php
							foreach ($listings_mapped as $listing) {
								$selected = (isset($fields->listings) && is_array($fields->listings) && in_array($listing['idroomota'], $fields->listings));
								?>
								<option value="<?php echo $listing['idroomota']; ?>"<?php echo $selected ? ' selected="selected"' : ''; ?>><?php echo $listing['otaroomname'] . ' (' . $listing['idroomota'] . ')'; ?></option>
								<?php
							}
							?>
							</select>
						</div>
					</div>
				</div>
				<?php
				$field_counter++;
			}
		}
		?>
			</div>
			<div class="vcm-param-setting-btn">
				<button type="button" class="btn vcm-config-btn" onclick="vcmAddAirbnbCustomSetting(this);"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::_('VCM_AIRBNB_SECDEP'); ?></button>
				<div class="vcm-custom-chsettings-helper" data-vcmtarget="vcm-custom-chsettings-secdep" style="display: none;">
					<div class="vcm-custom-chsettings-fields">
						<div class="vcm-custom-chsettings-field-rm">
							<button type="button" class="btn btn-danger" onclick="vcmRemoveAirbnbCustomSetting(this);"><?php VikBookingIcons::e('times-circle'); ?></button>
						</div>
						<div class="vcm-custom-chsettings-field">
							<div class="vcm-custom-chsettings-field-lbl"><?php echo JText::_('VCMBCAHAMOUNT'); ?> <sup>*</sup></div>
							<div class="vcm-custom-chsettings-field-val">
								<input type="number" name="cust_ch_settings[secdep][%d][amount]" value="0" min="0" step="any" disabled />
							</div>
						</div>
						<div class="vcm-custom-chsettings-field">
							<div class="vcm-custom-chsettings-field-lbl"><?php echo JText::_('VCM_AIRBNB_LISTINGS'); ?> <sup>*</sup></div>
							<div class="vcm-custom-chsettings-field-val">
								<select name="cust_ch_settings[secdep][%d][listings][]" multiple="multiple" size="6" class="vcm-cust-ch-settings-nicesel" disabled>
								<?php
								foreach ($listings_mapped as $listing) {
									?>
									<option value="<?php echo $listing['idroomota']; ?>"><?php echo $listing['otaroomname'] . ' (' . $listing['idroomota'] . ')'; ?></option>
									<?php
								}
								?>
								</select>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<div class="vcm-param-container vcm-param-container-wrap">
		<div class="vcm-param-label">
			<?php echo JText::_('VCM_AIRBNB_CLEANFEE'); ?> <?php echo $vcm_app->createPopover(array('title' => JText::_('VCM_AIRBNB_CLEANFEE'), 'content' => JText::_('VCM_AIRBNB_CLEANFEE_HELP'))); ?>
		</div>
		<div class="vcm-param-setting">
			<div id="vcm-custom-chsettings-cleanfee" style="<?php echo !isset($current_custom_settings->cleanfee) ? 'display: none;' : ''; ?>">
		<?php
		if (isset($current_custom_settings->cleanfee)) {
			$field_counter = 0;
			foreach ($current_custom_settings->cleanfee as $k => $fields) {
				?>
				<div class="vcm-custom-chsettings-fields">
					<div class="vcm-custom-chsettings-field-rm">
						<button type="button" class="btn btn-danger" onclick="vcmRemoveAirbnbCustomSetting(this);"><?php VikBookingIcons::e('times-circle'); ?></button>
					</div>
					<div class="vcm-custom-chsettings-field">
						<div class="vcm-custom-chsettings-field-lbl"><?php echo JText::_('VCMBCAHAMOUNT'); ?> <sup>*</sup></div>
						<div class="vcm-custom-chsettings-field-val">
							<input type="number" name="cust_ch_settings[cleanfee][<?php echo $field_counter; ?>][amount]" value="<?php echo isset($fields->amount) ? $fields->amount : '0'; ?>" min="0" step="any" />
						</div>
					</div>
					<div class="vcm-custom-chsettings-field">
						<div class="vcm-custom-chsettings-field-lbl"><?php echo JText::_('VCM_AIRBNB_LISTINGS'); ?> <sup>*</sup></div>
						<div class="vcm-custom-chsettings-field-val">
							<select name="cust_ch_settings[cleanfee][<?php echo $field_counter; ?>][listings][]" multiple="multiple" size="6" class="vcm-cust-ch-settings-nicesel vcm-cust-ch-settings-nicesel-saved">
							<?php
							foreach ($listings_mapped as $listing) {
								$selected = (isset($fields->listings) && is_array($fields->listings) && in_array($listing['idroomota'], $fields->listings));
								?>
								<option value="<?php echo $listing['idroomota']; ?>"<?php echo $selected ? ' selected="selected"' : ''; ?>><?php echo $listing['otaroomname'] . ' (' . $listing['idroomota'] . ')'; ?></option>
								<?php
							}
							?>
							</select>
						</div>
					</div>
				</div>
				<?php
				$field_counter++;
			}
		}
		?>
			</div>
			<div class="vcm-param-setting-btn">
				<button type="button" class="btn vcm-config-btn" onclick="vcmAddAirbnbCustomSetting(this);"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::_('VCM_AIRBNB_CLEANFEE'); ?></button>
				<div class="vcm-custom-chsettings-helper" data-vcmtarget="vcm-custom-chsettings-cleanfee" style="display: none;">
					<div class="vcm-custom-chsettings-fields">
						<div class="vcm-custom-chsettings-field-rm">
							<button type="button" class="btn btn-danger" onclick="vcmRemoveAirbnbCustomSetting(this);"><?php VikBookingIcons::e('times-circle'); ?></button>
						</div>
						<div class="vcm-custom-chsettings-field">
							<div class="vcm-custom-chsettings-field-lbl"><?php echo JText::_('VCMBCAHAMOUNT'); ?> <sup>*</sup></div>
							<div class="vcm-custom-chsettings-field-val">
								<input type="number" name="cust_ch_settings[cleanfee][%d][amount]" value="0" min="0" step="any" disabled />
							</div>
						</div>
						<div class="vcm-custom-chsettings-field">
							<div class="vcm-custom-chsettings-field-lbl"><?php echo JText::_('VCM_AIRBNB_LISTINGS'); ?> <sup>*</sup></div>
							<div class="vcm-custom-chsettings-field-val">
								<select name="cust_ch_settings[cleanfee][%d][listings][]" multiple="multiple" size="6" class="vcm-cust-ch-settings-nicesel" disabled>
								<?php
								foreach ($listings_mapped as $listing) {
									?>
									<option value="<?php echo $listing['idroomota']; ?>"><?php echo $listing['otaroomname'] . ' (' . $listing['idroomota'] . ')'; ?></option>
									<?php
								}
								?>
								</select>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<div class="vcm-param-container vcm-param-container-wrap">
		<div class="vcm-param-label">
			<?php echo JText::_('VCM_AIRBNB_STDFEES'); ?> <?php echo $vcm_app->createPopover(array('title' => JText::_('VCM_AIRBNB_STDFEES'), 'content' => JText::_('VCM_AIRBNB_STDFEES_HELP'))); ?>
		</div>
		<div class="vcm-param-setting">
			<div id="vcm-custom-chsettings-stdfee" style="<?php echo !isset($current_custom_settings->stdfee) ? 'display: none;' : ''; ?>">
		<?php
		if (isset($current_custom_settings->stdfee)) {
			$field_counter = 0;
			foreach ($current_custom_settings->stdfee as $k => $fields) {
				?>
				<div class="vcm-custom-chsettings-fields">
					<div class="vcm-custom-chsettings-field-rm">
						<button type="button" class="btn btn-danger" onclick="vcmRemoveAirbnbCustomSetting(this);"><?php VikBookingIcons::e('times-circle'); ?></button>
					</div>
					<div class="vcm-custom-chsettings-field">
						<div class="vcm-custom-chsettings-field-lbl"><?php echo JText::_('VCM_AIRBNB_STDFEE_TYPE'); ?> <sup>*</sup></div>
						<div class="vcm-custom-chsettings-field-val">
							<select name="cust_ch_settings[stdfee][<?php echo $field_counter; ?>][fee_type]">
								<option value="PASS_THROUGH_RESORT_FEE"<?php echo isset($fields->fee_type) && $fields->fee_type == 'PASS_THROUGH_RESORT_FEE' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCM_AIRBNB_STDFEE_TYPE_RESORT'); ?></option>
								<option value="PASS_THROUGH_MANAGEMENT_FEE"<?php echo isset($fields->fee_type) && $fields->fee_type == 'PASS_THROUGH_MANAGEMENT_FEE' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCM_AIRBNB_STDFEE_TYPE_MNG'); ?></option>
								<option value="PASS_THROUGH_COMMUNITY_FEE"<?php echo isset($fields->fee_type) && $fields->fee_type == 'PASS_THROUGH_COMMUNITY_FEE' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCM_AIRBNB_STDFEE_TYPE_COMM'); ?></option>
								<option value="PASS_THROUGH_LINEN_FEE"<?php echo isset($fields->fee_type) && $fields->fee_type == 'PASS_THROUGH_LINEN_FEE' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCM_AIRBNB_STDFEE_TYPE_LINEN'); ?></option>
								<option value="PASS_THROUGH_PET_FEE"<?php echo isset($fields->fee_type) && $fields->fee_type == 'PASS_THROUGH_PET_FEE' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCM_AIRBNB_STDFEE_TYPE_PET'); ?></option>
								<option value="PASS_THROUGH_SHORT_TERM_CLEANING_FEE"<?php echo isset($fields->fee_type) && $fields->fee_type == 'PASS_THROUGH_SHORT_TERM_CLEANING_FEE' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCM_AIRBNB_STDFEE_TYPE_SHORTCLEANING'); ?></option>
								<option value="PASS_THROUGH_ELECTRICITY_FEE"<?php echo isset($fields->fee_type) && $fields->fee_type == 'PASS_THROUGH_ELECTRICITY_FEE' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCM_AIRBNB_STDFEE_TYPE_ELEC'); ?></option>
								<option value="PASS_THROUGH_WATER_FEE"<?php echo isset($fields->fee_type) && $fields->fee_type == 'PASS_THROUGH_WATER_FEE' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCM_AIRBNB_STDFEE_TYPE_WATER'); ?></option>
								<option value="PASS_THROUGH_HEATING_FEE"<?php echo isset($fields->fee_type) && $fields->fee_type == 'PASS_THROUGH_HEATING_FEE' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCM_AIRBNB_STDFEE_TYPE_HEATING'); ?></option>
								<option value="PASS_THROUGH_AIR_CONDITIONING_FEE"<?php echo isset($fields->fee_type) && $fields->fee_type == 'PASS_THROUGH_AIR_CONDITIONING_FEE' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCM_AIRBNB_STDFEE_TYPE_AIRCOND'); ?></option>
								<option value="PASS_THROUGH_UTILITY_FEE"<?php echo isset($fields->fee_type) && $fields->fee_type == 'PASS_THROUGH_UTILITY_FEE' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCM_AIRBNB_STDFEE_TYPE_UTILITY'); ?></option>
							</select>
						</div>
					</div>
					<div class="vcm-custom-chsettings-field">
						<div class="vcm-custom-chsettings-field-lbl">
							<?php echo JText::_('VCM_AIRBNB_STDFEE_OFFLINE'); ?> <?php echo $vcm_app->createPopover(array('title' => JText::_('VCM_AIRBNB_STDFEE_OFFLINE'), 'content' => JText::_('VCM_AIRBNB_STDFEE_OFFLINE_HELP'))); ?>
						</div>
						<div class="vcm-custom-chsettings-field-val">
							<select name="cust_ch_settings[stdfee][<?php echo $field_counter; ?>][offline]">
								<option value="1"<?php echo isset($fields->offline) && (int)$fields->offline > 0 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMYES'); ?></option>
								<option value="0"<?php echo isset($fields->offline) && (int)$fields->offline < 1 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMNO'); ?></option>
							</select>
						</div>
					</div>
					<div class="vcm-custom-chsettings-field">
						<div class="vcm-custom-chsettings-field-lbl"><?php echo JText::_('VCMBCAHAMOUNT'); ?> <sup>*</sup></div>
						<div class="vcm-custom-chsettings-field-val">
							<input type="number" name="cust_ch_settings[stdfee][<?php echo $field_counter; ?>][amount]" value="<?php echo isset($fields->amount) ? $fields->amount : '0'; ?>" min="0" step="any" />
							<select name="cust_ch_settings[stdfee][<?php echo $field_counter; ?>][amount_type]">
								<option value="FLAT"<?php echo isset($fields->amount_type) && $fields->amount_type == 'FLAT' ? ' selected="selected"' : ''; ?>><?php echo !empty($this->config['currencysymb']) ? $this->config['currencysymb'] : '$'; ?></option>
								<option value="PERCENT"<?php echo isset($fields->amount_type) && $fields->amount_type == 'PERCENT' ? ' selected="selected"' : ''; ?>>%</option>
							</select>
						</div>
					</div>
					<div class="vcm-custom-chsettings-field">
						<div class="vcm-custom-chsettings-field-lbl"><?php echo JText::_('VCM_AIRBNB_STDFEE_UNIT_TYPE'); ?></div>
						<div class="vcm-custom-chsettings-field-val">
							<select name="cust_ch_settings[stdfee][<?php echo $field_counter; ?>][unit_type]">
								<option value=""></option>
								<option value="PER_KILOWATT_HOUR"<?php echo isset($fields->unit_type) && $fields->unit_type == 'PER_KILOWATT_HOUR' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCM_AIRBNB_STDFEE_UNIT_TYPE_KWH'); ?></option>
								<option value="PER_LITER"<?php echo isset($fields->unit_type) && $fields->unit_type == 'PER_LITER' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCM_AIRBNB_STDFEE_UNIT_TYPE_LT'); ?></option>
								<option value="PER_CUBIC_METER"<?php echo isset($fields->unit_type) && $fields->unit_type == 'PER_CUBIC_METER' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCM_AIRBNB_STDFEE_UNIT_TYPE_CM'); ?></option>
							</select>
						</div>
					</div>
					<div class="vcm-custom-chsettings-field">
						<div class="vcm-custom-chsettings-field-lbl"><?php echo JText::_('VCM_AIRBNB_STDFEE_CHARGE_TYPE'); ?></div>
						<div class="vcm-custom-chsettings-field-val">
							<select name="cust_ch_settings[stdfee][<?php echo $field_counter; ?>][charge_type]">
								<option value=""></option>
								<option value="PER_GROUP"<?php echo isset($fields->charge_type) && $fields->charge_type == 'PER_GROUP' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCM_AIRBNB_STDFEE_CHARGE_TYPE_GROUP'); ?></option>
								<option value="PER_PERSON"<?php echo isset($fields->charge_type) && $fields->charge_type == 'PER_PERSON' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCM_AIRBNB_STDFEE_CHARGE_TYPE_PERSON'); ?></option>
							</select>
						</div>
					</div>
					<div class="vcm-custom-chsettings-field">
						<div class="vcm-custom-chsettings-field-lbl"><?php echo JText::_('VCM_AIRBNB_STDFEE_CHARGE_PERIOD'); ?></div>
						<div class="vcm-custom-chsettings-field-val">
							<select name="cust_ch_settings[stdfee][<?php echo $field_counter; ?>][charge_period]">
								<option value=""></option>
								<option value="PER_BOOKING"<?php echo ($fields->charge_period ?? '') == 'PER_BOOKING' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCM_AIRBNB_STDFEE_CHARGE_PERIOD_BOOKING'); ?></option>
								<option value="PER_NIGHT"<?php echo ($fields->charge_period ?? '') == 'PER_NIGHT' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCM_AIRBNB_STDFEE_CHARGE_PERIOD_NIGHT'); ?></option>
							</select>
						</div>
					</div>
					<div class="vcm-custom-chsettings-field">
						<div class="vcm-custom-chsettings-field-lbl"><?php echo JText::_('VCM_AIRBNB_LISTINGS'); ?> <sup>*</sup></div>
						<div class="vcm-custom-chsettings-field-val">
							<select name="cust_ch_settings[stdfee][<?php echo $field_counter; ?>][listings][]" multiple="multiple" size="6" class="vcm-cust-ch-settings-nicesel vcm-cust-ch-settings-nicesel-saved">
							<?php
							foreach ($listings_mapped as $listing) {
								$selected = (isset($fields->listings) && is_array($fields->listings) && in_array($listing['idroomota'], $fields->listings));
								?>
								<option value="<?php echo $listing['idroomota']; ?>"<?php echo $selected ? ' selected="selected"' : ''; ?>><?php echo $listing['otaroomname'] . ' (' . $listing['idroomota'] . ')'; ?></option>
								<?php
							}
							?>
							</select>
						</div>
					</div>
				</div>
				<?php
				$field_counter++;
			}
		}
		?>
			</div>
			<div class="vcm-param-setting-btn">
				<button type="button" class="btn vcm-config-btn" onclick="vcmAddAirbnbCustomSetting(this);"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::_('VCM_AIRBNB_STDFEE'); ?></button>
				<div class="vcm-custom-chsettings-helper" data-vcmtarget="vcm-custom-chsettings-stdfee" style="display: none;">
					<div class="vcm-custom-chsettings-fields">
						<div class="vcm-custom-chsettings-field-rm">
							<button type="button" class="btn btn-danger" onclick="vcmRemoveAirbnbCustomSetting(this);"><?php VikBookingIcons::e('times-circle'); ?></button>
						</div>
						<div class="vcm-custom-chsettings-field">
							<div class="vcm-custom-chsettings-field-lbl"><?php echo JText::_('VCM_AIRBNB_STDFEE_TYPE'); ?> <sup>*</sup></div>
							<div class="vcm-custom-chsettings-field-val">
								<select name="cust_ch_settings[stdfee][%d][fee_type]" disabled>
									<option value="PASS_THROUGH_RESORT_FEE"><?php echo JText::_('VCM_AIRBNB_STDFEE_TYPE_RESORT'); ?></option>
									<option value="PASS_THROUGH_MANAGEMENT_FEE"><?php echo JText::_('VCM_AIRBNB_STDFEE_TYPE_MNG'); ?></option>
									<option value="PASS_THROUGH_COMMUNITY_FEE"><?php echo JText::_('VCM_AIRBNB_STDFEE_TYPE_COMM'); ?></option>
									<option value="PASS_THROUGH_LINEN_FEE"><?php echo JText::_('VCM_AIRBNB_STDFEE_TYPE_LINEN'); ?></option>
									<option value="PASS_THROUGH_PET_FEE"><?php echo JText::_('VCM_AIRBNB_STDFEE_TYPE_PET'); ?></option>
									<option value="PASS_THROUGH_SHORT_TERM_CLEANING_FEE"><?php echo JText::_('VCM_AIRBNB_STDFEE_TYPE_SHORTCLEANING'); ?></option>
									<option value="PASS_THROUGH_ELECTRICITY_FEE"><?php echo JText::_('VCM_AIRBNB_STDFEE_TYPE_ELEC'); ?></option>
									<option value="PASS_THROUGH_WATER_FEE"><?php echo JText::_('VCM_AIRBNB_STDFEE_TYPE_WATER'); ?></option>
									<option value="PASS_THROUGH_HEATING_FEE"><?php echo JText::_('VCM_AIRBNB_STDFEE_TYPE_HEATING'); ?></option>
									<option value="PASS_THROUGH_AIR_CONDITIONING_FEE"><?php echo JText::_('VCM_AIRBNB_STDFEE_TYPE_AIRCOND'); ?></option>
									<option value="PASS_THROUGH_UTILITY_FEE"><?php echo JText::_('VCM_AIRBNB_STDFEE_TYPE_UTILITY'); ?></option>
								</select>
							</div>
						</div>
						<div class="vcm-custom-chsettings-field">
							<div class="vcm-custom-chsettings-field-lbl">
								<?php echo JText::_('VCM_AIRBNB_STDFEE_OFFLINE'); ?>
							</div>
							<div class="vcm-custom-chsettings-field-val">
								<select name="cust_ch_settings[stdfee][%d][offline]" disabled>
									<option value="1"><?php echo JText::_('VCMYES'); ?></option>
									<option value="0"><?php echo JText::_('VCMNO'); ?></option>
								</select>
							</div>
						</div>
						<div class="vcm-custom-chsettings-field">
							<div class="vcm-custom-chsettings-field-lbl"><?php echo JText::_('VCMBCAHAMOUNT'); ?> <sup>*</sup></div>
							<div class="vcm-custom-chsettings-field-val">
								<input type="number" name="cust_ch_settings[stdfee][%d][amount]" value="0" min="0" step="any" disabled />
								<select name="cust_ch_settings[stdfee][%d][amount_type]" disabled>
									<option value="FLAT"><?php echo !empty($this->config['currencysymb']) ? $this->config['currencysymb'] : '$'; ?></option>
									<option value="PERCENT">%</option>
								</select>
							</div>
						</div>
						<div class="vcm-custom-chsettings-field">
							<div class="vcm-custom-chsettings-field-lbl"><?php echo JText::_('VCM_AIRBNB_STDFEE_UNIT_TYPE'); ?></div>
							<div class="vcm-custom-chsettings-field-val">
								<select name="cust_ch_settings[stdfee][%d][unit_type]" disabled>
									<option value=""></option>
									<option value="PER_KILOWATT_HOUR"><?php echo JText::_('VCM_AIRBNB_STDFEE_UNIT_TYPE_KWH'); ?></option>
									<option value="PER_LITER"><?php echo JText::_('VCM_AIRBNB_STDFEE_UNIT_TYPE_LT'); ?></option>
									<option value="PER_CUBIC_METER"><?php echo JText::_('VCM_AIRBNB_STDFEE_UNIT_TYPE_CM'); ?></option>
								</select>
							</div>
						</div>
						<div class="vcm-custom-chsettings-field">
							<div class="vcm-custom-chsettings-field-lbl"><?php echo JText::_('VCM_AIRBNB_STDFEE_CHARGE_TYPE'); ?></div>
							<div class="vcm-custom-chsettings-field-val">
								<select name="cust_ch_settings[stdfee][%d][charge_type]" disabled>
									<option value=""></option>
									<option value="PER_GROUP"><?php echo JText::_('VCM_AIRBNB_STDFEE_CHARGE_TYPE_GROUP'); ?></option>
									<option value="PER_PERSON"><?php echo JText::_('VCM_AIRBNB_STDFEE_CHARGE_TYPE_PERSON'); ?></option>
								</select>
							</div>
						</div>
						<div class="vcm-custom-chsettings-field">
							<div class="vcm-custom-chsettings-field-lbl"><?php echo JText::_('VCM_AIRBNB_STDFEE_CHARGE_PERIOD'); ?></div>
							<div class="vcm-custom-chsettings-field-val">
								<select name="cust_ch_settings[stdfee][%d][charge_period]" disabled>
									<option value=""></option>
									<option value="PER_BOOKING"><?php echo JText::_('VCM_AIRBNB_STDFEE_CHARGE_PERIOD_BOOKING'); ?></option>
									<option value="PER_NIGHT"><?php echo JText::_('VCM_AIRBNB_STDFEE_CHARGE_PERIOD_NIGHT'); ?></option>
								</select>
							</div>
						</div>
						<div class="vcm-custom-chsettings-field">
							<div class="vcm-custom-chsettings-field-lbl"><?php echo JText::_('VCM_AIRBNB_LISTINGS'); ?> <sup>*</sup></div>
							<div class="vcm-custom-chsettings-field-val">
								<select name="cust_ch_settings[stdfee][%d][listings][]" multiple="multiple" size="6" class="vcm-cust-ch-settings-nicesel" disabled>
								<?php
								foreach ($listings_mapped as $listing) {
									?>
									<option value="<?php echo $listing['idroomota']; ?>"><?php echo $listing['otaroomname'] . ' (' . $listing['idroomota'] . ')'; ?></option>
									<?php
								}
								?>
								</select>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<script type="text/javascript">
		function vcmAddAirbnbCustomSetting(elem) {
			var helper = jQuery(elem).parent().find('.vcm-custom-chsettings-helper');
			if (!helper || !helper.length) {
				return false;
			}
			// clone input elements and append them to target
			var target = helper.attr('data-vcmtarget');
			helper = helper.children().clone();
			jQuery(helper).appendTo(jQuery('#' + target).show());
			
			// enable any input field
			var all_inputs = jQuery('#' + target).find('input, select, textarea');
			all_inputs.prop('disabled', false);

			// count the number of elements defined
			inputs_counter = parseInt(jQuery('#' + target).find('.vcm-custom-chsettings-fields').length);
			// we start from 0
			inputs_counter--;
			
			// make sure to set the proper name by replacing the wildcard
			all_inputs.each(function(k, v) {
				var input_name = jQuery(this).attr('name');
				if (!input_name || input_name.indexOf('cust_ch_settings') < 0) {
					return;
				}
				jQuery(this).attr('name', input_name.replace('%d', inputs_counter));
			});
			
			// render select2, if any select element is found
			if (jQuery('#' + target).find('select.vcm-cust-ch-settings-nicesel').length) {
				jQuery('#' + target).find('select.vcm-cust-ch-settings-nicesel').select2();
			}
		}

		function vcmRemoveAirbnbCustomSetting(elem) {
			jQuery(elem).closest('.vcm-custom-chsettings-fields').remove();
		}

		jQuery(document).ready(function() {
			if (jQuery('.vcm-cust-ch-settings-nicesel-saved').length) {
				// these select tags are displayed when the page loads
				jQuery('.vcm-cust-ch-settings-nicesel-saved').select2();
			}
		});
	</script>
	<?php
}
