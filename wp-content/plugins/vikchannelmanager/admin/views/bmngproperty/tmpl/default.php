<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2023 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

// No direct access to this file
defined('ABSPATH') or die('No script kiddies please!');

// Vik Booking Application for special field types
$vbo_app = VikChannelManager::getVboApplication();

// whether to hide or show inactive rate plans
$hide_inactive_rplans = JFactory::getApplication()->getUserStateFromRequest('com_vikchannelmanager.hide_inactive_rplans', 'hide_inactive_rplans', 0, 'int');

// find the host account name
$hotel_name = '';
foreach ($this->otarooms as $otar) {
	// make sure the hotel ID matches with the active one
	if (!empty($otar['prop_params'])) {
		$prop_params = json_decode($otar['prop_params'], true);
		if (isset($prop_params['hotelid']) && $prop_params['hotelid'] != $this->channel['params']['hotelid']) {
			// skip this room mapping as it's for a different hotel ID
			continue;
		}
	}
	$hotel_name = !empty($otar['prop_name']) && $otar['prop_name'] != $this->channel['params']['hotelid'] ? $otar['prop_name'] : $hotel_name;
}

// build a map of OTA rooms and VBO rooms
$rooms_map = [];
foreach ($this->otarooms as $otar) {
	if (empty($otar['idroomvb'])) {
		continue;
	}
	foreach ($this->vbrooms as $vbroom) {
		if ($vbroom['id'] != $otar['idroomvb']) {
			continue;
		}
		$rooms_map[$otar['idroomota']] = [
			'id'   => $vbroom['id'],
			'name' => $vbroom['name'],
		];
	}
}

// the controller will redirect to this View by setting loaded=1 if some contents were just returned
$loaded = VikRequest::getInt('loaded', 0, 'request');

// collect current information data
$hotel_info  	   = null;
$house_rules 	   = null;
$rateplans_info    = null;
$roomrates_info    = null;
$licenses 		   = null;
$contracts 		   = null;
$hotel_currency    = '';
$used_cancpolicies = [];
$guestrooms_data   = [];
if (is_object($this->property_data) && isset($this->property_data->property)) {
	// parse XML into an object
	$hotel_info = is_string($this->property_data->property) ? simplexml_load_string($this->property_data->property) : $this->property_data->property;

	// check for house rules only if property details are available
	if (isset($this->property_data->houserules) && is_object($this->property_data->houserules)) {
		$house_rules = $this->property_data->houserules;
	}

	// parse additional XML data into objects
	if (isset($this->property_data->rateplans)) {
		$rateplans_info = is_string($this->property_data->rateplans) ? simplexml_load_string($this->property_data->rateplans) : $this->property_data->rateplans;
	}
	if (isset($this->property_data->roomrates)) {
		$roomrates_info = is_string($this->property_data->roomrates) ? simplexml_load_string($this->property_data->roomrates) : $this->property_data->roomrates;
	}

	// check for existing cancellation policies at property-level
	if (is_object($hotel_info) && isset($hotel_info->HotelDescriptiveContents->HotelDescriptiveContent->Policies->Policy->CancelPolicy->CancelPenalty)) {
		foreach ($hotel_info->HotelDescriptiveContents->HotelDescriptiveContent->Policies->Policy->CancelPolicy->CancelPenalty as $cancpenalty) {
			$cancpenalty_attr = $cancpenalty->attributes();
			// push cancellation policy already installed
			$used_cancpolicies[(string)$cancpenalty_attr->PolicyCode] = (string)$cancpenalty_attr->Description;
		}
	}

	// check for licenses only if property details are available
	if (isset($this->property_data->licenses) && is_object($this->property_data->licenses)) {
		$licenses = $this->property_data->licenses;
	}

	// check for contracts only if property details are available
	if (isset($this->property_data->contracts) && is_object($this->property_data->contracts)) {
		$contracts = $this->property_data->contracts;
	}

	// gather an associative list of guest rooms returned by the OTA
	if (is_object($hotel_info) && isset($hotel_info->HotelDescriptiveContents->HotelDescriptiveContent->FacilityInfo->GuestRooms->GuestRoom)) {
		foreach ($hotel_info->HotelDescriptiveContents->HotelDescriptiveContent->FacilityInfo->GuestRooms->GuestRoom as $guest_room) {
			$guest_room_attr = $guest_room->attributes();
			$guest_room_id 	 = (string)$guest_room_attr->ID;
			$guest_room_type = (string)$guest_room_attr->RoomTypeName;
			$guestrooms_data[$guest_room_id] = isset($rooms_map[$guest_room_id]) ? $rooms_map[$guest_room_id]['name'] : "({$guest_room_id}) {$guest_room_type}";
		}
	}
}

// list of Booking.com Hotel Amenity Codes (HAC)
$hac_list = VCMBookingcomContent::getHotelAmenityCodes($no_group = true);
$hac_priv = VCMBookingcomContent::getReservedHotelAmenityCodes();

// list of Booking.com bundled value-adds service codes
$vads_codes = VCMBookingcomContent::getValueAddsServiceCodes();

// channel logo
$default_logo = VikChannelManager::getLogosInstance('booking')->getSmallLogoURL();

// lang vars for JS
JText::script('VCMREMOVECONFIRM');
JText::script('MSG_BASE_SUCCESS');
JText::script('MSG_BASE_WARNING_BOOKING_RAR');
JText::script('VCMYES');
JText::script('VCMNO');
JText::script('VCMBCAHJOB17');
JText::script('VCM_ASK_CONTINUE');
JText::script('VCM_PLEASE_SELECT');
JText::script('VCMOVERSIGHTLEGENDGREEN');
JText::script('VCMRARADDLOSAPPLY');
JText::script('VCMBCAHCINFO');
JText::script('NEW');
JText::script('EDIT');
JText::script('REMOVE');
JText::script('VCMBCAHTAXTYPE');
JText::script('VCMBCAHFEE');
JText::script('VCMCHILDREN');
JText::script('VCM_MIN_AGE');
JText::script('VCM_MAX_AGE');
JText::script('VCMROOMSRELATIONSNAME');
JText::script('VCMRARRATEPLAN');
JText::script('VCM_MODELEM_AFTER_SAVE');
JText::script('VCMENABLED');
JText::script('VCMDISABLED');
JText::script('VCM_CREATE_NEW');
JText::script('VCM_PARENT_RPLAN_DESCR');
JText::script('VCM_DERIVED_RPLAN_DESCR');
JText::script('VCM_ASK_RELOAD');
JText::script('VCM_ROOMRATE_RELATIONS');

?>

<div class="vcm-loading-overlay">
	<div class="vcm-loading-dot vcm-loading-dot1"></div>
	<div class="vcm-loading-dot vcm-loading-dot2"></div>
	<div class="vcm-loading-dot vcm-loading-dot3"></div>
	<div class="vcm-loading-dot vcm-loading-dot4"></div>
	<div class="vcm-loading-dot vcm-loading-dot5"></div>
</div>

<div class="vcm-listings-list-head">
	<h3><?php echo 'Hotel ID ' . $this->channel['params']['hotelid'] . (!empty($hotel_name) ? ' - ' . $hotel_name : ''); ?></h3>
<?php
if (!is_object($hotel_info) && $this->retrieve_count < 3 && !$loaded) {
	// display download button
	?>
	<div class="vcm-listings-list-download">
		<a href="index.php?option=com_vikchannelmanager&task=bproperty.download" class="btn vcm-config-btn" onclick="return vcmLoadPropertyDetails();"><i class="vboicn-cloud-download"></i> <?php echo JText::_('VCM_LOAD_DETAILS'); ?></a>
	</div>
	<?php
}

if (is_object($hotel_info)) {
	// display toolbar
	?>
	<div class="vcm-listing-toolbar-wrap">
		<div class="vcm-listing-toolbar-inner">
			<div class="vcm-listing-toolbar-block">
				<span class="vcm-listing-toolbar-btn" data-jumpto="details">
					<a href="JavaScript: void(0);" title="<?php echo $this->escape(JText::_('VCM_BCOM_PROP_DETAILS')); ?>"><?php VikBookingIcons::e('hotel'); ?> <span><?php echo JText::_('VCM_BCOM_PROP_DETAILS'); ?></span></a>
				</span>
			</div>
			<div class="vcm-listing-toolbar-block">
				<span class="vcm-listing-toolbar-btn" data-jumpto="houserules">
					<a href="JavaScript: void(0);" title="<?php echo $this->escape(JText::_('VCM_HOUSE_RULES')); ?>"><?php VikBookingIcons::e('hand-paper'); ?> <span><?php echo JText::_('VCM_HOUSE_RULES'); ?></span></a>
				</span>
			</div>
			<div class="vcm-listing-toolbar-block">
				<span class="vcm-listing-toolbar-btn" data-jumpto="licenses">
					<a href="JavaScript: void(0);" title="<?php echo $this->escape(JText::_('VCM_LICENSES')); ?>"><?php VikBookingIcons::e('certificate'); ?> <span><?php echo JText::_('VCM_LICENSES'); ?></span></a>
				</span>
			</div>
			<div class="vcm-listing-toolbar-block">
				<span class="vcm-listing-toolbar-btn" data-jumpto="rateplans">
					<a href="JavaScript: void(0);" title="<?php echo $this->escape(JText::_('VCMROOMSRELRPLANS')); ?>"><?php VikBookingIcons::e('tags'); ?> <span><?php echo JText::_('VCMROOMSRELRPLANS'); ?></span></a>
				</span>
			</div>
			<div class="vcm-listing-toolbar-block">
				<span class="vcm-listing-toolbar-btn" data-jumpto="roomrates">
					<a href="JavaScript: void(0);" title="<?php echo $this->escape(JText::_('VCM_ROOMRATE_RELATIONS')); ?>"><?php VikBookingIcons::e('briefcase'); ?> <span><?php echo JText::_('VCM_ROOMRATE_RELATIONS'); ?></span></a>
				</span>
			</div>
			<div class="vcm-listing-toolbar-block">
				<span class="vcm-listing-toolbar-btn" data-jumpto="contactinfos">
					<a href="JavaScript: void(0);" title="<?php echo $this->escape(JText::_('VCMBCAHCINFO')); ?>"><?php VikBookingIcons::e('users'); ?> <span><?php echo JText::_('VCMBCAHCINFO'); ?></span></a>
				</span>
			</div>
			<div class="vcm-listing-toolbar-block">
				<span class="vcm-listing-toolbar-btn" data-jumpto="taxpolicies">
					<a href="JavaScript: void(0);" title="<?php echo $this->escape(JText::_('VCMBCAHPOLICIES') . ' - ' . JText::_('VCMBCAHTAXES')); ?>"><?php VikBookingIcons::e('percent'); ?> <span><?php echo JText::_('VCMBCAHPOLICIES') . ' - ' . JText::_('VCMBCAHTAXES'); ?></span></a>
				</span>
			</div>
			<div class="vcm-listing-toolbar-block">
				<span class="vcm-listing-toolbar-btn" data-jumpto="feepolicies">
					<a href="JavaScript: void(0);" title="<?php echo $this->escape(JText::_('VCMBCAHPOLICIES') . ' - ' . JText::_('VCMBCAHFEES')); ?>"><?php VikBookingIcons::e('calculator'); ?> <span><?php echo JText::_('VCMBCAHPOLICIES') . ' - ' . JText::_('VCMBCAHFEES'); ?></span></a>
				</span>
			</div>
			<div class="vcm-listing-toolbar-block">
				<span class="vcm-listing-toolbar-btn" data-jumpto="policyinfo">
					<a href="JavaScript: void(0);" title="<?php echo $this->escape(JText::_('VCMBCAHPOLICIES') . ' - ' . JText::_('VCMMENUORDERS')); ?>"><?php VikBookingIcons::e('plane-arrival'); ?> <span><?php echo JText::_('VCMBCAHPOLICIES') . ' - ' . JText::_('VCMMENUORDERS'); ?></span></a>
				</span>
			</div>
			<div class="vcm-listing-toolbar-block">
				<span class="vcm-listing-toolbar-btn" data-jumpto="services">
					<a href="JavaScript: void(0);" title="<?php echo $this->escape(JText::_('VCMBCAHSERVICES')); ?>"><?php VikBookingIcons::e('icons'); ?> <span><?php echo JText::_('VCMBCAHSERVICES'); ?></span></a>
				</span>
			</div>
			<div class="vcm-listing-toolbar-block">
				<span class="vcm-listing-toolbar-btn" data-jumpto="contracts">
					<a href="JavaScript: void(0);" title="<?php echo $this->escape(JText::_('VCMBCAHCINFOTYPE2')); ?>"><?php VikBookingIcons::e('file-contract'); ?> <span><?php echo JText::_('VCMBCAHCINFOTYPE2'); ?></span></a>
				</span>
			</div>
		<?php
		if (!$loaded) {
			?>
			<div class="vcm-listing-toolbar-block vcm-listing-toolbar-block-link">
				<span class="vcm-listing-toolbar-btn">
					<a href="index.php?option=com_vikchannelmanager&task=bproperty.download" onclick="return vcmLoadPropertyDetails();"><?php VikBookingIcons::e('sync'); ?> <span><?php echo JText::_('VCM_RELOAD'); ?></span></a>
				</span>
			</div>
			<?php
		}
		?>
		</div>
	</div>
	<?php
}
?>
</div>

<form action="index.php?option=com_vikchannelmanager" method="post" name="adminForm" id="adminForm">
	<div class="vcm-admin-container vcm-admin-container-hastables">

		<div class="vcm-config-maintab-left">

			<fieldset class="adminform vcm-listings-listing-wrap" data-landto="details">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('home'); ?> <?php echo JText::_('VCM_BCOM_PROP_DETAILS'); ?></legend>
					<div class="vcm-params-container">

					<?php
					if (is_object($hotel_info) && !empty($default_logo)) {
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-setting">
								<img src="<?php echo $default_logo; ?>" class="vcm-nice-picture" />
							</div>
						</div>
						<?php
					}
					?>

					<?php
					if (is_object($hotel_info)) {
						// display property information (details) fields
						$hcontent_attr = $hotel_info->HotelDescriptiveContents->HotelDescriptiveContent->attributes();

						// set hotel currency
						$hotel_currency = (string)$hcontent_attr->CurrencyCode;
						?>
						<div class="vcm-params-block">
							<div class="vcm-param-container">
								<div class="vcm-param-label"><?php echo JText::_('VCMPROPNAME'); ?></div>
								<div class="vcm-param-setting">
									<input type="text" name="property[name]" value="<?php echo $this->escape((string)$hcontent_attr->HotelName); ?>" />
								</div>
							</div>

							<div class="vcm-param-container">
								<div class="vcm-param-label"><?php echo JText::_('VBSTATUS'); ?></div>
								<div class="vcm-param-setting">
								<?php
								$status_badge_cls = '-info';
								if (stripos((string)$hcontent_attr->Status, 'open') !== false) {
									$status_badge_cls = '-success';
								} elseif (stripos((string)$hcontent_attr->Status, 'close') !== false) {
									$status_badge_cls = '-warning';
								}
								?>
									<span class="badge badge<?php echo $status_badge_cls; ?>"><?php echo $hcontent_attr->Status; ?></span>
								</div>
							</div>

							<div class="vcm-param-container">
								<div class="vcm-param-label"><?php echo JText::_('VCMPCTS'); ?></div>
								<div class="vcm-param-setting">
								<?php
								$pct_name = '';
								if (isset($hotel_info->HotelDescriptiveContents->HotelDescriptiveContent->HotelInfo)) {
									if (isset($hotel_info->HotelDescriptiveContents->HotelDescriptiveContent->HotelInfo->CategoryCodes)) {
										if (isset($hotel_info->HotelDescriptiveContents->HotelDescriptiveContent->HotelInfo->CategoryCodes->HotelCategory)) {
											$pct_code = (string)$hotel_info->HotelDescriptiveContents->HotelDescriptiveContent->HotelInfo->CategoryCodes->HotelCategory->attributes()->Code;
											$pct_name = VCMBookingcomContent::getPropertyClassTypes($pct_code);
											if ($pct_name) {
												$pct_name .= ' - ' . VCMBookingcomContent::getPropertyClassTypes($pct_code, $get_type = true);
											}
										}
									}
								}
								echo $pct_name;
								?>
								</div>
							</div>

							<div class="vcm-param-container">
								<div class="vcm-param-label"><?php echo JText::_('VCMCONFCURNAME'); ?></div>
								<div class="vcm-param-setting">
									<?php echo $hotel_currency; ?>
								</div>
							</div>
						</div>

						<?php
						// check for NoCVC in PaymentPreferences
						$payment_preferences = null;
						if (isset($hotel_info->HotelDescriptiveContents->HotelDescriptiveContent->HotelInfo->TPA_Extensions)) {
							if (isset($hotel_info->HotelDescriptiveContents->HotelDescriptiveContent->HotelInfo->TPA_Extensions->PaymentPreferences)) {
								$payment_preferences = $hotel_info->HotelDescriptiveContents->HotelDescriptiveContent->HotelInfo->TPA_Extensions->PaymentPreferences->attributes();
							}
						}
						if ($payment_preferences) {
							$cc_det = isset($payment_preferences->ViewCCDetails) ? (int)$payment_preferences->ViewCCDetails : 0;
							$no_cvc = isset($payment_preferences->NoCVC) ? (int)$payment_preferences->NoCVC : 1;
							?>
						<div class="vcm-params-block">
							<div class="vcm-param-container" data-related-group="paymentpreferences">
								<div class="vcm-param-label">Credit Card Details</div>
								<div class="vcm-param-setting">
									<select name="paymentpreferences[viewccdetails]">
										<option value="1"<?php echo $cc_det ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMENABLED'); ?></option>
										<option value="0"<?php echo !$cc_det ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMDISABLED'); ?></option>
									</select>
								</div>
							</div>

							<div class="vcm-param-container" data-related-group="paymentpreferences">
								<div class="vcm-param-label">Credit Card CVC</div>
								<div class="vcm-param-setting">
									<select name="paymentpreferences[nocvc]">
										<option value="0"<?php echo !$no_cvc ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMENABLED'); ?></option>
										<option value="1"<?php echo $no_cvc ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMDISABLED'); ?></option>
									</select>
								</div>
							</div>
						</div>
							<?php
						}

						// check for long stay bookings (over 30 nights)
						$long_stay_info = null;
						if (isset($hotel_info->HotelDescriptiveContents->HotelDescriptiveContent->TPA_Extensions)) {
							if (isset($hotel_info->HotelDescriptiveContents->HotelDescriptiveContent->TPA_Extensions->LongStayInfo)) {
								$long_stay_info = $hotel_info->HotelDescriptiveContents->HotelDescriptiveContent->TPA_Extensions->LongStayInfo->attributes();
							}
						}
						if ($long_stay_info) {
							$accept_long_stay = isset($long_stay_info->AcceptLongStay) ? (int)$long_stay_info->AcceptLongStay : 0;
							$long_max_los 	  = isset($long_stay_info->MaxLengthOfStay) ? (int)$long_stay_info->MaxLengthOfStay : 30;
							?>
						<div class="vcm-params-block">
							<div class="vcm-param-container" data-related-group="longstayinfo">
								<div class="vcm-param-label"><?php echo JText::_('VCM_ACCEPT_LONG_STAYS'); ?></div>
								<div class="vcm-param-setting">
									<select name="longstayinfo[acceptlongstay]" onchange="vcmHandleLongStays(this.value);">
										<option value="1"<?php echo $accept_long_stay ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMYES'); ?></option>
										<option value="0"<?php echo !$accept_long_stay ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMNO'); ?></option>
									</select>
									<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_ACCEPT_LONG_STAYS_DESCR'); ?></span>
								</div>
							</div>

							<div class="vcm-param-container vcm-param-nested vcm-longstayinfo-maxlos" data-related-group="longstayinfo" style="<?php echo !$accept_long_stay ? 'display: none;' : ''; ?>">
								<div class="vcm-param-label"><?php echo JText::_('VCM_MAXIMUM_LOS'); ?></div>
								<div class="vcm-param-setting">
									<select name="longstayinfo[maxlengthofstay]">
										<option value=""></option>
										<option value="90"<?php echo $long_max_los == 90 ? ' selected="selected"' : ''; ?>>90 <?php echo JText::_('VCMPVIEWORDERSVBSIX'); ?></option>
										<option value="75"<?php echo $long_max_los == 75 ? ' selected="selected"' : ''; ?>>75 <?php echo JText::_('VCMPVIEWORDERSVBSIX'); ?></option>
										<option value="60"<?php echo $long_max_los == 60 ? ' selected="selected"' : ''; ?>>60 <?php echo JText::_('VCMPVIEWORDERSVBSIX'); ?></option>
										<option value="45"<?php echo $long_max_los == 45 ? ' selected="selected"' : ''; ?>>45 <?php echo JText::_('VCMPVIEWORDERSVBSIX'); ?></option>
									</select>
								</div>
							</div>
						</div>
							<?php
						}
					} else {
						// no details found
						?>
						<p class="warn"><?php echo JText::_('VCM_CH_NODETAILS_FOUND'); ?></p>
						<?php
					}
					?>

					</div>
				</div>
			</fieldset>

			<fieldset class="adminform vcm-listings-listing-wrap" data-landto="rateplans">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('tags'); ?> <?php echo JText::_('VCMROOMSRELRPLANS'); ?></legend>
					<div class="vcm-params-container">

						<div class="vcm-param-container">
							<div class="vcm-param-setting">
								<div class="pull-left vcm-bookingcom-undo-rateplans" style="display: none;">
									<button type="button" class="btn" onclick="vcmUndoChangesRatePlan();"><?php VikBookingIcons::e('times'); ?> <?php echo JText::_('VCM_UNDO_CHANGES'); ?></button>
								</div>
								<div class="pull-right">
									<button type="button" class="btn vcm-config-btn" onclick="vcmNewRatePlan();"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::_('VCM_CREATE_NEW'); ?></button>
								</div>
							</div>
						</div>

						<div class="vcm-bookingcom-rateplans">
					<?php
					$sorted_rateplans = [];
					$bcom_rateplans   = [];
					if (is_object($rateplans_info) && isset($rateplans_info->rate)) {
						$sorted_rateplans = VCMBookingcomContent::sortRatePlans($rateplans_info->rate);
						foreach ($sorted_rateplans as $bcom_rplan) {
							$rplan_name 	  = $bcom_rplan->name;
							$is_rplan_active  = isset($bcom_rplan->active) && $bcom_rplan->active == '1';
							$is_rplan_derived = isset($bcom_rplan->is_child_rate) && $bcom_rplan->is_child_rate == '1';

							// set rate plan data
							$bcom_rateplans[(string)$bcom_rplan->id] = $rplan_name;
							?>
							<div class="vcm-params-block vcm-bookingcom-rateplan" data-rplan-name="<?php echo $this->escape($rplan_name); ?>" data-rplan-id="<?php echo $this->escape($bcom_rplan->id); ?>" style="<?php echo !$is_rplan_active && $hide_inactive_rplans ? 'display: none;' : ''; ?>">
								<div class="vcm-param-container">
									<div class="vcm-param-label">
										<strong><?php echo $rplan_name; ?></strong>
										<div>ID <?php echo $bcom_rplan->id; ?></div>
									</div>
									<div class="vcm-param-setting vcm-bookingcom-rateplan-actionmsg">
									<?php
									if (!$is_rplan_derived) {
										// no edit for derived rate plans, or by updating the name this will be changed into a parent rate plan
										?>
										<button type="button" class="btn" title="<?php echo $this->escape(JText::_('EDIT')); ?>" onclick="vcmEditRatePlan(this);"><?php VikBookingIcons::e('edit'); ?></button>
										<?php
									}
									?>
										<button type="button" class="btn<?php echo $is_rplan_active ? ' btn-danger' : ''; ?>" title="<?php echo $this->escape(($is_rplan_active ? JText::_('VCMDISABLED') : JText::_('VCMENABLED'))); ?>" onclick="vcmToggleRatePlan(this, '<?php echo $is_rplan_active ? 'deactivate' : 'activate'; ?>');"><?php VikBookingIcons::e(($is_rplan_active ? 'times-circle' : 'play-circle')); ?></button>
									</div>
								</div>
								<div class="vcm-param-container">
									<div class="vcm-param-label">
										<span class="badge badge-<?php echo $is_rplan_active ? 'success' : 'error vcm-hide-inactive-rplans'; ?>"><?php echo $is_rplan_active ? JText::_('VCMPROMSTATUSACTIVE') : JText::_('VCMPROMSTATUSINACTIVE'); ?></span>
									</div>
									<div class="vcm-param-setting">
									<?php
									if ($is_rplan_derived) {
										// derived rate plan
										?>
										<span class="label"><?php echo JText::sprintf('VCM_DERIVED_RPLAN_DESCR', 'Booking.com'); ?></span>
										<?php
									} else {
										// parent rate
										?>
										<span class="label label-info"><?php echo JText::_('VCM_PARENT_RPLAN_DESCR'); ?></span>
										<?php
									}
									?>
									</div>
								</div>
							</div>
							<?php
						}
					}
					?>
						</div>
					</div>
				</div>
			</fieldset>

			<fieldset class="adminform vcm-listings-listing-wrap" data-landto="roomrates">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('briefcase'); ?> <?php echo JText::_('VCM_ROOMRATE_RELATIONS'); ?></legend>
					<div class="vcm-params-container">

				<?php
				if (is_object($roomrates_info) && isset($roomrates_info->rooms) && isset($roomrates_info->rooms->room)) {
					$bcom_rids_wplans = [];
					foreach ($roomrates_info->rooms->room as $roomrate) {
						$roomrate_attr = $roomrate->attributes();
						$bcom_rids_wplans[] = (string) $roomrate_attr->id;
						?>
						<div class="vcm-params-block vcm-bookingcom-roomrate" data-room-id="<?php echo $roomrate_attr->id; ?>" data-room-name="<?php echo $this->escape($roomrate_attr->room_name); ?>">

							<div class="vcm-param-container">
								<div class="vcm-param-setting vcm-bookingcom-roomrate-room">
									<strong><?php VikBookingIcons::e('bed'); ?> <?php echo $roomrate_attr->room_name; ?></strong>
									<div>
										<span>ID <?php echo $roomrate_attr->id; ?></span>
									<?php
									if (isset($rooms_map[(string)$roomrate_attr->id])) {
										?>
										<span><?php VikBookingIcons::e('exchange-alt'); ?> <?php echo JText::_('VCM_LISTING_SYNCED') . ' (' . $rooms_map[(string)$roomrate_attr->id]['name'] . ')'; ?></span>
										<?php
									} else {
										?>
										<span> - <?php echo JText::_('VCM_LISTING_NOT_SYNCED'); ?></span>
										<?php
									}
									?>
									</div>
								</div>
								<span class="vcm-param-setting-pull-right">
									<span class="vcm-bookingcom-roomrate-toggle vcm-bookingcom-roomrate-toggle-down"><?php VikBookingIcons::e('chevron-down'); ?></span>
									<span class="vcm-bookingcom-roomrate-toggle vcm-bookingcom-roomrate-toggle-up" style="display: none;"><?php VikBookingIcons::e('chevron-up'); ?></span>
								</span>
							</div>

						<?php
						$roomrate_rplans = [];
						$last_roomrate_cancpol_code = null;
						if (isset($roomrate->rates) && isset($roomrate->rates->rate)) {
							foreach ($roomrate->rates->rate as $rr_rate) {
								$rr_rate_attr = $rr_rate->attributes();
								$roomrate_rplans[] = (string)$rr_rate_attr->id;

								$rate_canc_pol_code  = '';
								$rate_canc_pol_descr = '';
								if (isset($rr_rate->policies) && isset($rr_rate->policies->cancel_policy) && isset($rr_rate->policies->cancel_policy->cancel_penalty)) {
									$rate_canc_pol_code = (string)$rr_rate->policies->cancel_policy->cancel_penalty->attributes()->policy_code;
									$rate_canc_pol_descr = VCMBookingcomContent::getCancellationPolicyCodes($rate_canc_pol_code);
								}

								// check if this is a derived rate plan
								$is_child_rate  = isset($rr_rate_attr->is_child_rate) && (string)$rr_rate_attr->is_child_rate == '1';
								$parent_rate_id = '';
								$derived_pcent  = 100;
								$derived_disc 	= '';
								if (isset($rr_rate->rate_relation)) {
									$relation_attr  = $rr_rate->rate_relation->attributes();
									$parent_rate_id = isset($relation_attr->parent_rate_id) ? (string)$relation_attr->parent_rate_id : '';
									$is_child_rate  = $is_child_rate && $parent_rate_id;
									$derived_pcent  = isset($relation_attr->percentage) ? (int)$relation_attr->percentage : $derived_pcent;
								}
								if ($is_child_rate && $derived_pcent != 100) {
									if ($derived_pcent < 100) {
										// discount
										$derived_disc = '-' . (100 - $derived_pcent) . '%';
									} else {
										// surplus
										$derived_disc = '+' . ($derived_pcent - 100) . '%';
									}
								}
								?>
							<div class="vcm-param-container vcm-bookingcom-roomrate-rplan" data-rateplan-id="<?php echo $rr_rate_attr->id; ?>" style="display: none;">
								<div class="vcm-param-label vcm-bookingcom-roomrate-actionmsg">
									<button type="button" class="btn" title="<?php echo $this->escape(JText::_('EDIT')); ?>" onclick="vcmEditRoomRate(this);"><?php VikBookingIcons::e('edit'); ?></button>
									<button type="button" class="btn btn-danger" title="<?php echo $this->escape(JText::_('REMOVE')); ?>" onclick="vcmRemoveRoomRate(this);"><?php VikBookingIcons::e('times-circle'); ?></button>
								</div>
								<div class="vcm-param-setting vcm-bookingcom-roomrate-updateinfo">
									<strong><?php VikBookingIcons::e('tag'); ?> <?php echo $rr_rate_attr->rate_name; ?></strong>
								<?php
								if ($is_child_rate) {
									?>
									<span><small><?php echo JText::sprintf('VCM_DERIVED_RP_FROM_RP', (isset($bcom_rateplans[$parent_rate_id]) ? '&quot;' . $bcom_rateplans[$parent_rate_id] . '&quot;' : $parent_rate_id)); ?></small></span>
									<?php
								}
								?>
									<div>ID <?php echo $rr_rate_attr->id . ($is_child_rate && $derived_pcent != 100 ? ' <span class="badge badge-info">' . $derived_disc . '</span>' : ''); ?></div>
								<?php
								if (isset($rr_rate->value_added_services) && isset($rr_rate->value_added_services->value_added_service)) {
									?>
									<div class="vcm-bookingcom-includes-value-adds"><span class="badge badge-success"><?php VikBookingIcons::e('certificate'); ?> Bundled Value-Adds</span></div>
									<?php
								}
								if ($rate_canc_pol_descr && ($rate_canc_pol_code != $last_roomrate_cancpol_code || true)) {
									?>
									<span class="vcm-param-setting-comment"><?php echo '(' . $rate_canc_pol_code . ') ' . $rate_canc_pol_descr; ?></span>
									<?php
								}
								// update last cancellation policy code used to save space and avoid duplicate information (we now display it all the times)
								$last_roomrate_cancpol_code = $rate_canc_pol_code;
								?>
									<input type="hidden" name="roomrates_update[]" class="vcm-hidden-disabled vcm-bookingcom-roomrate-json" value="<?php echo $this->escape(json_encode(VCMBookingcomContent::buildRoomRatePayload($rr_rate, (string)$roomrate_attr->id))); ?>" />
								</div>
							</div>
								<?php
							}
						}

						// get the list of active rate plans not yet assigned to this room-type
						$roomrate_eligible_rplans = VCMBookingcomContent::filterEligibleRoomRatePlans($sorted_rateplans, $roomrate_rplans);

						if ($roomrate_eligible_rplans) {
							// it would not be possible to create a new room-rate relation without any eligible rate plan
							?>
							<div class="vcm-param-container vcm-bookingcom-roomrate-addnew" style="display: none;">
								<div class="vcm-param-label">&nbsp;</div>
								<div class="vcm-param-setting">
									<input type="hidden" value="<?php echo $this->escape(json_encode($roomrate_eligible_rplans)); ?>" class="vcm-bookingcom-roomrate-eligplans" />
									<button type="button" class="btn vcm-config-btn" onclick="vcmNewRoomRate('<?php echo $roomrate_attr->id; ?>');"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::_('VCMBCAHADD'); ?></button>
								</div>
							</div>
							<?php
						}
						?>

						</div>
						<?php
					}

					// print also the mapped rooms with no rate plans
					$hid = $this->channel['params']['hotelid'];
					$hotel_mapped_rooms = array_filter(array_keys($rooms_map), function($rid) use ($hid) {
						return strpos($rid, $hid) === 0;
					});
					$bcrom_rids_noplans = array_diff($hotel_mapped_rooms, $bcom_rids_wplans);
					$roomrate_eligible_rplans = VCMBookingcomContent::filterEligibleRoomRatePlans($sorted_rateplans, []);
					if ($bcrom_rids_noplans && $roomrate_eligible_rplans) {
						foreach ($bcrom_rids_noplans as $rid) {
							?>
						<div class="vcm-params-block vcm-bookingcom-roomrate" data-room-id="<?php echo $rid; ?>" data-room-name="<?php echo $this->escape($rooms_map[$rid]['name']); ?>">

							<div class="vcm-param-container">
								<div class="vcm-param-setting vcm-bookingcom-roomrate-room">
									<strong><?php VikBookingIcons::e('bed'); ?> <?php echo $rooms_map[$rid]['name']; ?></strong>
									<div>
										<span>ID <?php echo $rid; ?></span>
										<span> - No rate plans</span>
									</div>
								</div>
								<span class="vcm-param-setting-pull-right">
									<span class="vcm-bookingcom-roomrate-toggle vcm-bookingcom-roomrate-toggle-down"><?php VikBookingIcons::e('chevron-down'); ?></span>
									<span class="vcm-bookingcom-roomrate-toggle vcm-bookingcom-roomrate-toggle-up" style="display: none;"><?php VikBookingIcons::e('chevron-up'); ?></span>
								</span>
							</div>

							<div class="vcm-param-container vcm-bookingcom-roomrate-addnew" style="display: none;">
								<div class="vcm-param-label">&nbsp;</div>
								<div class="vcm-param-setting">
									<input type="hidden" value="<?php echo $this->escape(json_encode($roomrate_eligible_rplans)); ?>" class="vcm-bookingcom-roomrate-eligplans" />
									<button type="button" class="btn vcm-config-btn" onclick="vcmNewRoomRate('<?php echo $rid; ?>');"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::_('VCMBCAHADD'); ?></button>
								</div>
							</div>

						</div>
							<?php
						}
					}
				}
				?>

					</div>
				</div>
			</fieldset>

			<fieldset class="adminform vcm-listings-listing-wrap" data-landto="services">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('icons'); ?> <?php echo JText::_('VCMBCAHSERVICES'); ?></legend>
					<div class="vcm-params-container">

				<?php
				$hinfo_services = null;
				$active_amenity_codes = [];
				if (is_object($hotel_info)) {
					$hinfo_services = $hotel_info->HotelDescriptiveContents->HotelDescriptiveContent->HotelInfo->Services;
				}

				if ($hinfo_services) {
					foreach ($hinfo_services->Service as $service) {
						$serv_attr = $service->attributes();
						$active_amenity_codes[] = (string)$serv_attr->Code;
					}
					?>
						<div class="vcm-param-container">
							<div class="vcm-param-setting">
								<div class="btn-group-inline">
									<select id="vcm-hotel-amenities-dropdown-list" class="vcm-listing-editable vcm-multi-select">
										<option></option>
									<?php
									$amenity_group = null;
									foreach (VCMBookingcomContent::getHotelAmenityCodes() as $hac_group) {
										if ($amenity_group != $hac_group['group']) {
											if (!is_null($amenity_group)) {
												// close previous node
												echo '</optgroup>' . "\n";
											}
											// open new node
											echo '<optgroup label="' . $this->escape($hac_group['group']) . '">' . "\n";
											// update current group
											$amenity_group = $hac_group['group'];
										}
										foreach ($hac_group['list'] as $hacode => $haname) {
											if (in_array($hacode, $hac_priv)) {
												// we omit this "reserved" amenity (breakfast, lunch or dinner)
												continue;
											}
											?>
											<option value="<?php echo $this->escape($hacode); ?>"<?php echo in_array($hacode, $active_amenity_codes) ? ' disabled' : ''; ?>><?php echo '(' . $hacode . ') ' . $haname; ?></option>
											<?php
										}
									}
									if (!is_null($amenity_group) && $amenity_group == $hac_group['group']) {
										// close last node
										echo '</optgroup>' . "\n";
									}
									?>
									</select>
									<button type="button" class="btn vcm-config-btn" onclick="vcmAddHotelAmenity();"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::_('VCMBCAHADD'); ?></button>
								</div>
								<span class="vcm-param-setting-comment">Select the hotel amenities to add.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">
								<span><?php echo JText::_('VCM_TOTAL_COUNT') . ': '; ?></span>
								<span class="badge badge-info vcm-bookingcom-totservices">0</span>
							</div>
							<div class="vcm-param-setting">
								<span class="vcm-bookingcom-expand-services" onclick="jQuery(this).parent().find('input').trigger('click');"><?php echo JText::_('VCM_EXPAND'); ?></span>
								<?php echo $vbo_app->printYesNoButtons('vcm_toggle_services', JText::_('VCMYES'), JText::_('VCMNO'), 0, 1, 0, 'vcmToggleHotelServices(this.checked)'); ?>
							</div>
						</div>

						<div class="vcm-bookingcom-hotel-amenities" style="display: none;">
						<?php
						// Property facilities (services)
						$counter = 0;
						foreach ($hinfo_services->Service as $service) {
							$serv_attr = $service->attributes();
							$serv_name = isset($hac_list[(string)$serv_attr->Code]) ? ('(' . $serv_attr->Code . ') ' . $hac_list[(string)$serv_attr->Code]) : $serv_attr->Code;
							$included  = isset($serv_attr->Included) ? (string)$serv_attr->Included : 'unknown';
							$exists    = isset($serv_attr->ExistsCode) ? (string)$serv_attr->ExistsCode : '1';
							$is_priv   = in_array((string)$serv_attr->Code, $hac_priv);
							?>
							<div class="vcm-params-block vcm-bookingcom-hotel-amenity">

								<div class="vcm-param-container" data-related-group="hotelamenities">
									<div class="vcm-param-label">
										<strong><?php echo $serv_name; ?></strong>
									</div>
									<div class="vcm-param-setting">
										<input type="hidden" name="services[<?php echo $counter; ?>][code]" data-buildname="services[%d][code]" class="vcm-hidden-disabled vcm-bookingcom-amenity-code" value="<?php echo $this->escape((string)$serv_attr->Code); ?>" onchange="vcmEnableHotelAmenities();" />
									<?php
									if (!$is_priv) {
										?>
										<button type="button" class="btn btn-danger" onclick="vcmRemoveHotelAmenity(this);"><?php VikBookingIcons::e('times-circle'); ?></button>
										<?php
									} else {
										?>
										<span style="color: green;"><?php VikBookingIcons::e('check-circle'); ?></span>
										<input type="hidden" name="services[<?php echo $counter; ?>][protected]" data-buildname="services[%d][protected]" class="vcm-hidden-disabled vcm-bookingcom-amenity-code" value="1" />
										<?php
									}
									?>
									</div>
								</div>

							<?php
							if (!$is_priv) {
							?>
								<div class="vcm-param-container vcm-param-nested" data-related-group="hotelamenities">
									<div class="vcm-param-label"><?php echo JText::_('VCMOVERSIGHTLEGENDGREEN'); ?></div>
									<div class="vcm-param-setting">
										<select name="services[<?php echo $counter; ?>][existscode]" data-buildname="services[%d][existscode]" class="vcm-bookingcom-amenity-exists">
											<option value="1"<?php echo $exists == '1' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMYES'); ?></option>
											<option value="2"<?php echo $exists == '2' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMNO'); ?></option>
										</select>
									</div>
								</div>

								<div class="vcm-param-container vcm-param-nested vcm-bookingcom-amenity-included" data-related-group="hotelamenities" style="<?php echo $exists == '2' ? 'display: none;' : ''; ?>">
									<div class="vcm-param-label">Included</div>
									<div class="vcm-param-setting">
										<select name="services[<?php echo $counter; ?>][included]" data-buildname="services[%d][included]">
											<option value="true"<?php echo $included == 'true' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMYES'); ?></option>
											<option value="false"<?php echo $included == 'false' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMNO'); ?></option>
											<option value="unknown"<?php echo $included == 'unknown' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMBCAHJOB17'); ?></option>
										</select>
										<span class="vcm-param-setting-comment">Specifies whether the service is included in the room price or comes at an extra charge.</span>
									</div>
								</div>
							<?php
							}
							?>

							</div>
							<?php
							// increase counter
							$counter++;
						}
						?>
						</div>

						<script type="text/javascript">
							jQuery(function() {
								jQuery('.vcm-bookingcom-totservices').text('<?php echo $counter; ?>');
							});
						</script>
						<?php
				}
				?>

					</div>
				</div>
			</fieldset>

		</div>

		<div class="vcm-config-maintab-right">

			<fieldset class="adminform vcm-listings-listing-wrap" data-landto="houserules">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('hand-paper'); ?> <?php echo JText::_('VCM_HOUSE_RULES'); ?></legend>
					<div class="vcm-params-container">
					<?php
					if ($house_rules && !isset($house_rules->error)) {
						// display house rules fields
						$house_rules = new JObject($house_rules);
						?>

						<div class="vcm-param-container" data-related-group="houserules">
							<div class="vcm-param-label"><?php echo JText::_('VCM_SMOKING_ALLOWED'); ?></div>
							<div class="vcm-param-setting">
								<select name="houserules[smoking_allowed]">
									<option value="1"<?php echo $house_rules->get('smoking_allowed', 0) ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMYES'); ?></option>
									<option value="0"<?php echo !$house_rules->get('smoking_allowed', 0) ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMNO'); ?></option>
								</select>
							</div>
						</div>

						<div class="vcm-param-container" data-related-group="houserules">
							<div class="vcm-param-label"><?php echo JText::_('VCM_PARTIES_ALLOWED'); ?></div>
							<div class="vcm-param-setting">
								<select name="houserules[parties_allowed]">
									<option value="1"<?php echo $house_rules->get('parties_allowed', 0) ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMYES'); ?></option>
									<option value="0"<?php echo !$house_rules->get('parties_allowed', 0) ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMNO'); ?></option>
								</select>
							</div>
						</div>

						<div class="vcm-param-container" data-related-group="houserules">
							<div class="vcm-param-label"><?php echo JText::_('PETS_ALLOWED'); ?></div>
							<div class="vcm-param-setting">
								<select name="houserules[pets_allowed]">
									<option value="on_request"<?php echo $house_rules->get('pets_allowed', '') == 'on_request' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCM_ON_REQUEST'); ?></option>
									<option value="yes"<?php echo $house_rules->get('pets_allowed', '') == 'yes' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMYES'); ?></option>
									<option value="no"<?php echo $house_rules->get('pets_allowed', '') == 'no' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMNO'); ?></option>
								</select>
							</div>
						</div>

						<div class="vcm-param-container" data-related-group="houserules">
							<div class="vcm-param-label"><?php echo JText::_('VCMBCAHFEETYPE34'); ?></div>
							<div class="vcm-param-setting">
								<select name="houserules[pets_price_type]">
									<option value=""></option>
									<option value="free"<?php echo $house_rules->get('pets_price_type', '') == 'free' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMBCARCAMENITYVAL2'); ?></option>
									<option value="charges_may_apply"<?php echo $house_rules->get('pets_price_type', '') == 'charges_may_apply' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCM_CHARGES_MAY_APPLY'); ?></option>
								</select>
								<span class="vcm-param-setting-comment">Required if &quot;<?php echo JText::_('PETS_ALLOWED'); ?>&quot; is <i><?php echo JText::_('VCMYES'); ?></i> or <i><?php echo JText::_('VCM_ON_REQUEST'); ?></i>.</span>
							</div>
						</div>

						<div class="vcm-param-container" data-related-group="houserules">
							<div class="vcm-param-label"><?php echo JText::_('VCM_QUIET_HOURS'); ?></div>
							<div class="vcm-param-setting">
								<select name="houserules[quiet_hours_set]">
									<option value="1"<?php echo $house_rules->get('quiet_hours_set') ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMYES'); ?></option>
									<option value="0"<?php echo !$house_rules->get('quiet_hours_set') ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMNO'); ?></option>
								</select>
							</div>
						</div>
						<?php
						$quiet_hours_start = $house_rules->get('quiet_hours_start_time', '');
						$quiet_hours_end = $house_rules->get('quiet_hours_end_time', '');
						?>
						<div class="vcm-param-container vcm-param-nested" data-related-group="houserules">
							<div class="vcm-param-label"><?php echo JText::_('VCM_QUIET_HOURS'); ?> - Start Time</div>
							<div class="vcm-param-setting">
								<select name="houserules[quiet_hours_start_time]">
									<option value=""></option>
								<?php
								for ($ht = 1; $ht < 24; $ht++) {
									$hours_time = "{$ht}:00";
									?>
									<option value="<?php echo $hours_time; ?>"<?php echo $quiet_hours_start == $hours_time ? ' selected="selected"' : ''; ?>><?php echo $ht < 10 ? "0{$ht}" : $ht; ?>:00</option>
									<?php
								}
								?>
								</select>
							</div>
						</div>
						<div class="vcm-param-container vcm-param-nested" data-related-group="houserules">
							<div class="vcm-param-label"><?php echo JText::_('VCM_QUIET_HOURS'); ?> - End Time</div>
							<div class="vcm-param-setting">
								<select name="houserules[quiet_hours_end_time]">
									<option value=""></option>
								<?php
								for ($ht = 1; $ht < 24; $ht++) {
									$hours_time = "{$ht}:00";
									?>
									<option value="<?php echo $hours_time; ?>"<?php echo $quiet_hours_end == $hours_time ? ' selected="selected"' : ''; ?>><?php echo $ht < 10 ? "0{$ht}" : $ht; ?>:00</option>
									<?php
								}
								?>
								</select>
							</div>
						</div>
						<?php
					} elseif ($house_rules && isset($house_rules->error) && isset($house_rules->error->message)) {
						// current property type does not support house rules
						?>
						<p class="info"><?php echo $house_rules->error->message; ?></p>
						<?php
					}
					?>
					</div>
				</div>
			</fieldset>

			<fieldset class="adminform vcm-listings-listing-wrap" data-landto="licenses">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('file-contract'); ?> <?php echo JText::_('VCM_LICENSES'); ?></legend>
					<div class="vcm-params-container">
				<?php
				if ($licenses && isset($licenses->variants) && $licenses->variants) {
					// display license requirement fields
					$licenses = new JObject($licenses);
					// check if the license(s) are at room-level or at property-level
					$license_level = $licenses->get('level', 'property');
					$license_level = $license_level == 'room' ? 'room' : 'property';
					// access current license data submitted on B.com
					$current_license_data = $licenses->get('data');
					if (!is_object($current_license_data)) {
						$current_license_data = new stdClass;
					}
					?>

						<div class="vcm-param-container">
							<div class="vcm-param-label">
								<strong title="<?php echo $this->escape('ID ' . $licenses->get('id', '---')); ?>"><?php echo ucwords(str_replace('_', ' ', $licenses->get('name', ''))); ?></strong>
							</div>
							<div class="vcm-param-setting">
								<span>License requirements on <span class="label label-info"><?php echo ucfirst($license_level); ?>-Level</span></span>
							</div>
						</div>

					<?php
					// build pool of elements for which the variants should be displayed depending on level requirements (room or property)
					$license_fields_pool = ['property'];
					$first_license_group = 'property';
					if ($license_level == 'room') {
						// present the license variants and related fields for each room
						$license_fields_pool = $guestrooms_data;
						reset($license_fields_pool);
						$first_license_group = key($license_fields_pool);
					}

					?>
						<div class="vcm-param-container" style="<?php echo $license_level == 'property' ? 'display: none;' : ''; ?>">
							<div class="vcm-param-setting">
								<select id="vcm-bookingcom-licenses-group" class="vcm-listing-editable" onchange="vcmHandleLicenseGroup(this.value);">
								<?php
								foreach ($license_fields_pool as $license_group_key => $license_group_val) {
									// get the fields related group name
									$fields_group_key = $license_group_val == 'property' ? 'property' : $license_group_key;
									$fields_group_val = $license_group_val == 'property' ? 'Property' : $license_group_val;
									?>
									<option value="<?php echo $fields_group_key; ?>"><?php echo $fields_group_val; ?></option>
									<?php
								}
								?>
								</select>
								<span class="vcm-param-setting-comment">Choose the room-type for which you would like to select a license variant, and eventually provide the required information.</span>
							</div>
						</div>
					<?php

					foreach ($license_fields_pool as $license_group_key => $license_group_val) {
						// get the fields related group name
						$fields_related_group = $license_group_val == 'property' ? 'property' : $license_group_key;

						// check if any current data is available
						$current_data 	   = isset($current_license_data->{$fields_related_group}) ? $current_license_data->{$fields_related_group} : (new stdClass);
						$content_data 	   = isset($current_data->contentData) ? $current_data->contentData : (new stdClass);
						$active_variant_id = isset($current_data->variantId) ? $current_data->variantId : null;

						// loop through all variants for this license
						foreach ($licenses->get('variants', []) as $variant) {
							if (!is_object($variant) || !isset($variant->content) || empty($variant->name)) {
								// the variant may have no content (fields), but the property must be set
								continue;
							}
							$variant_id = !empty($variant->id) ? $variant->id : 0;
							$related_group_name = "licenses-{$variant_id}-{$fields_related_group}";
							?>
						<div class="vcm-params-block vcm-bookingcom-license-variant" data-variant-id="<?php echo $variant_id; ?>" data-variant-group="<?php echo $fields_related_group; ?>" style="<?php echo $fields_related_group != $first_license_group ? 'display: none;' : ''; ?>">

							<div class="vcm-param-container vcm-bookingcom-license-variant-type vcm-param-container-tmp-disabled" data-related-group="licenses-<?php echo $fields_related_group; ?>" data-fields_group="<?php echo $fields_related_group; ?>">
								<div class="vcm-param-setting">
									<input type="radio" name="licenses[variant][<?php echo $fields_related_group; ?>]" value="<?php echo $variant_id; ?>" id="<?php echo $related_group_name; ?>" onchange="vcmHandleLicenseVariant('<?php echo $this->escape($variant_id); ?>', '<?php echo $this->escape($fields_related_group); ?>');" class="<?php echo $active_variant_id != $variant_id ? 'vcm-listing-editable' : ''; ?>" <?php echo $active_variant_id == $variant_id ? 'checked ' : ''; ?>/>
									<label for="<?php echo $related_group_name; ?>"><strong><?php echo ucwords(str_replace('_', ' ', $variant->name)); ?></strong></label>
								<?php
								if ($active_variant_id == $variant_id) {
									?>
									<span class="badge badge-success"><?php echo JText::_('VCMPROMSTATUSACTIVE'); ?></span>
									<?php
								}
								?>
								</div>
							</div>

							<?php
							// variants may have no fields ("content"), hence an empty array
							$variant->content = is_array($variant->content) ? $variant->content : [];
							foreach ($variant->content as $license_field) {
								if (!is_object($license_field) || empty($license_field->name)) {
									continue;
								}
								$variant_field_name = $license_field->name;
								$is_required 		= (isset($license_field->required) && $license_field->required);
								$current_field_data = '';
								if ($active_variant_id == $variant_id && isset($content_data->{$variant_field_name})) {
									$current_field_data = isset($content_data->{$variant_field_name}->value) ? (string)$content_data->{$variant_field_name}->value : $current_field_data;
								}
								$field_input_name = 'licenses[' . $fields_related_group . '][' . $variant_id . '][' . $variant_field_name . ']';
								?>
							<div class="vcm-param-container vcm-bookingcom-license-variant-field" data-related-group="licenses-<?php echo $fields_related_group; ?>" data-fields_group="<?php echo $fields_related_group; ?>" style="<?php echo !$active_variant_id || $active_variant_id != $variant_id ? 'display: none;' : ''; ?>">
								<div class="vcm-param-label"><?php echo ucwords(str_replace('_', ' ', $variant_field_name)) . ($is_required ? '<sup>*</sup>' : ''); ?></div>
								<div class="vcm-param-setting">
								<?php
								if (!empty($license_field->possibleValues)) {
									// drop down menu
									?>
									<select name="<?php echo $field_input_name; ?>">
										<option value=""></option>
									<?php
									foreach ($license_field->possibleValues as $possib_val) {
										?>
										<option value="<?php echo $possib_val; ?>"<?php echo $current_field_data && $current_field_data == $possib_val ? ' selected="selected"' : ''; ?>><?php echo ucwords(str_replace('_', ' ', $possib_val)); ?></option>
										<?php
									}
									?>
									</select>
								<?php
								} elseif (isset($license_field->dataType) && !strcasecmp((string)$license_field->dataType, 'date')) {
									// calendar (date) field
									echo $vbo_app->getCalendar($current_field_data, $field_input_name, $variant_id . '-' . $variant_field_name, '%Y-%m-%d');
								} else {
									// input field
									?>
									<input type="text" name="<?php echo $field_input_name; ?>" value="<?php echo $this->escape($current_field_data); ?>" class="vcm-input-validation" placeholder=" " <?php echo !empty($license_field->format) ? 'pattern="' . $this->escape($license_field->format) . '"' : ''; ?>/>
									<?php
								}
								?>
								</div>
							</div>
								<?php
							}
							?>
						</div>
						<?php
						}
					}
				} elseif ($licenses && isset($licenses->error) && isset($licenses->error->message)) {
					// current property type does not require licenses
					?>
						<p class="info"><?php echo $licenses->error->message; ?></p>
					<?php
				}
				?>
					</div>
				</div>
			</fieldset>

			<fieldset class="adminform vcm-listings-listing-wrap" data-landto="contactinfos">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('users'); ?> <?php echo JText::_('VCMBCAHCINFO'); ?></legend>
					<div class="vcm-params-container">

					<?php
					$contact_infos = null;
					$contact_types = VCMBookingcomContent::getContactProfileTypes();
					if (is_object($hotel_info)) {
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-setting">
								<div class="btn-group-inline">
									<select id="vcm-hotel-contacttypes-dropdown-list" class="vcm-listing-editable vcm-multi-select">
										<option></option>
									<?php
									foreach ($contact_types as $contact_type_code => $contact_type_descr) {
										?>
										<option value="<?php echo $this->escape($contact_type_code); ?>"><?php echo ucfirst($contact_type_code) . ' - ' . $contact_type_descr; ?></option>
										<?php
									}
									?>
									</select>
									<button type="button" class="btn vcm-config-btn" onclick="vcmNewContactInfo();"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::_('VCMBCAHADD'); ?></button>
								</div>
								<span class="vcm-param-setting-comment">Select the contact profile type to add.</span>
							</div>
						</div>
						<?php
					}
					?>
						<div class="vcm-bookingcom-hotel-contacts">
					<?php
					if ($hotel_info && isset($hotel_info->HotelDescriptiveContents->HotelDescriptiveContent->ContactInfos)) {
						if (isset($hotel_info->HotelDescriptiveContents->HotelDescriptiveContent->ContactInfos->ContactInfo)) {
							$contact_infos = $hotel_info->HotelDescriptiveContents->HotelDescriptiveContent->ContactInfos->ContactInfo;
						}
					}

					if ($contact_infos) {
						foreach ($contact_infos as $contact_info) {
							$contact_details = VCMBookingcomContent::parseContactInfoNodes($contact_info);
							$contact_phones  = [];
							foreach ($contact_details['phones'] as $phone) {
								$contact_phones[] = $phone['number'];
							}
							?>
							<div class="vcm-params-block vcm-bookingcom-hotel-contactinfo">

								<div class="vcm-param-container" data-related-group="contactinfos">
									<div class="vcm-param-label">
										<strong class="vcm-bookingcom-type"><?php echo ucfirst($contact_details['type']); ?></strong>
									<?php
									if (isset($contact_types[$contact_details['type']])) {
										?>
										<span class="vcm-param-setting-comment"><?php echo $contact_types[$contact_details['type']]; ?></span>
										<?php
									}
									?>
										<input type="hidden" name="contactinfos[]" class="vcm-hidden-disabled vcm-bookingcom-contactinfo-json" value="<?php echo $this->escape(json_encode($contact_details)); ?>" />
									</div>
									<div class="vcm-param-setting">
										<button type="button" class="btn" onclick="vcmEditContactInfo(this);"><?php VikBookingIcons::e('edit'); ?></button>
										<button type="button" class="btn btn-danger" onclick="vcmRemoveContactInfo(this);"><?php VikBookingIcons::e('times-circle'); ?></button>
									</div>
								</div>

							<?php
							if ($contact_details['name']['givenname'] || $contact_details['name']['jobtitle']) {
								?>
								<div class="vcm-param-container">
									<div class="vcm-param-label">
										<span><?php VikBookingIcons::e('user'); ?></span>
										<span class="vcm-bookingcom-name"><?php echo $contact_details['name']['givenname'] . (!empty($contact_details['name']['gender']) ? ' (' . $contact_details['name']['gender'] . ')' : ''); ?></span>
									</div>
									<div class="vcm-param-setting">
										<span class="vcm-bookingcom-job"><?php echo $contact_details['name']['jobtitle'] . (!empty($contact_details['name']['language']) ? ' (' . $contact_details['name']['language'] . ')' : ''); ?></span>
									</div>
								</div>
								<?php
							}

							if ($contact_details['email'] || $contact_phones) {
								?>
								<div class="vcm-param-container">
									<div class="vcm-param-label">
										<span><?php VikBookingIcons::e('envelope'); ?></span>
										<span class="vcm-bookingcom-mail"><?php echo $contact_details['email']; ?></span>
									</div>
									<div class="vcm-param-setting">
										<span><?php VikBookingIcons::e('phone'); ?></span>
										<span class="vcm-bookingcom-phone"><?php echo implode(', ', array_filter($contact_phones)); ?></span>
									</div>
								</div>
								<?php
							}

							if ($contact_details['address']['street'] || $contact_details['address']['city']) {
								?>
								<div class="vcm-param-container">
									<div class="vcm-param-setting">
										<span><?php VikBookingIcons::e('location-arrow'); ?></span>
										<span class="vcm-bookingcom-address"><?php
											echo implode(', ', array_filter([
												$contact_details['address']['street'],
												$contact_details['address']['city'],
												$contact_details['address']['postcode'],
												$contact_details['address']['country'],
												$contact_details['address']['stateprov']
											]));
										?></span>
									</div>
								</div>
								<?php
							}
							?>

							</div>
							<?php
						}
					}
					?>

						</div>

					</div>
				</div>
			</fieldset>

			<fieldset class="adminform vcm-listings-listing-wrap" data-landto="taxpolicies">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('percent'); ?> <?php echo JText::_('VCMBCAHPOLICIES') . ' - ' . JText::_('VCMBCAHTAXES'); ?></legend>
					<div class="vcm-params-container">

					<?php
					$taxpolicies 		= null;
					$def_decimal_places = 2;
					$tax_type_codes 	= VCMBookingcomContent::getFeeTaxTypeCodes('tax');
					$charge_type_codes 	= VCMBookingcomContent::getChargeTypeCodes();
					$used_tax_codes 	= [];
					if (is_object($hotel_info)) {
						if (isset($hotel_info->HotelDescriptiveContents->HotelDescriptiveContent->Policies->Policy->TaxPolicies->TaxPolicy)) {
							$taxpolicies = $hotel_info->HotelDescriptiveContents->HotelDescriptiveContent->Policies->Policy->TaxPolicies->TaxPolicy;
							foreach ($taxpolicies as $taxpolicy) {
								$used_tax_codes[] = (string)$taxpolicy->attributes()->Code;
							}
						}
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-setting">
								<div class="btn-group-inline">
									<select id="vcm-hotel-taxpolicies-dropdown-list" class="vcm-listing-editable vcm-multi-select">
										<option></option>
									<?php
									foreach ($tax_type_codes as $tax_code => $tax_type) {
										?>
										<option value="<?php echo $this->escape($tax_code); ?>"<?php echo in_array($tax_code, $used_tax_codes) ? ' disabled' : ''; ?>><?php echo $tax_type; ?></option>
										<?php
									}
									?>
									</select>
									<button type="button" class="btn vcm-config-btn" onclick="vcmNewTaxPolicy();"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::_('VCMBCAHADD'); ?></button>
								</div>
								<span class="vcm-param-setting-comment">Select the type of tax policy to add.</span>
							</div>
						</div>
						<?php
					}
					?>
						<div class="vcm-bookingcom-taxpolicies">
					<?php

					if ($taxpolicies) {
						foreach ($taxpolicies as $taxpolicy) {
							$taxpol_attr = $taxpolicy->attributes();
							// attempt to update the default decimals places
							if (isset($taxpol_attr->DecimalPlaces)) {
								$def_decimal_places = (int)$taxpol_attr->DecimalPlaces;
							}
							// build information
							$tax_code 	 = (string)$taxpol_attr->Code;
							$decimals 	 = isset($taxpol_attr->DecimalPlaces) ? (int)$taxpol_attr->DecimalPlaces : 0;
							$divider 	 = pow(10, $decimals);
							$is_percent  = isset($taxpol_attr->Percent);
							$amount 	 = $is_percent ? (int)$taxpol_attr->Percent : (int)$taxpol_attr->Amount;
							$real_amount = VikBooking::numberFormat($amount / $divider);
							$type_incl 	 = isset($taxpol_attr->Type) ? (string)$taxpol_attr->Type : '';
							$charge_freq = isset($taxpol_attr->ChargeFrequency) ? (string)$taxpol_attr->ChargeFrequency : '';
							$charge_freq = $charge_freq && isset($charge_type_codes[$charge_freq]) ? $charge_type_codes[$charge_freq] : '';
							$inv_code 	 = isset($taxpol_attr->InvCode) ? JText::_('VCM_ROOM') . ' ' . (string)$taxpol_attr->InvCode : '';
							?>
							<div class="vcm-params-block vcm-bookingcom-taxpolicy" data-tax-code="<?php echo $tax_code; ?>">
								<div class="vcm-param-container" data-related-group="taxpolicies">
									<div class="vcm-param-label">
										<strong><?php echo isset($tax_type_codes[$tax_code]) ? $tax_type_codes[$tax_code] : $tax_code; ?></strong>
										<input type="hidden" name="taxpolicies[]" class="vcm-hidden-disabled" value="<?php echo $this->escape(json_encode(VCMBookingcomContent::buildTaxPolicyPayload($taxpol_attr))); ?>" />
									</div>
									<div class="vcm-param-setting">
										<button type="button" class="btn btn-danger" onclick="vcmRemoveTaxPolicy(this);"><?php VikBookingIcons::e('times-circle'); ?></button>
									</div>
								</div>
								<div class="vcm-param-container">
									<div class="vcm-param-label"><?php echo (!$is_percent ? $hotel_currency . ' ' : '') . $real_amount . ($is_percent ? '%' : ''); ?></div>
									<div class="vcm-param-setting"><?php echo implode(', ', array_filter([$type_incl, $charge_freq, $inv_code])); ?></div>
								</div>
							</div>
							<?php
						}
					}
					?>

						</div>

					</div>
				</div>
			</fieldset>

			<fieldset class="adminform vcm-listings-listing-wrap" data-landto="feepolicies">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('calculator'); ?> <?php echo JText::_('VCMBCAHPOLICIES') . ' - ' . JText::_('VCMBCAHFEES'); ?></legend>
					<div class="vcm-params-container">

					<?php
					$feepolicies 	 = null;
					$fee_type_codes  = VCMBookingcomContent::getFeeTaxTypeCodes('fee');
					$child_fee_codes = VCMBookingcomContent::getFeeTaxTypeCodes('children');
					if (is_object($hotel_info)) {
						if (isset($hotel_info->HotelDescriptiveContents->HotelDescriptiveContent->Policies->Policy->FeePolicies->FeePolicy)) {
							$feepolicies = $hotel_info->HotelDescriptiveContents->HotelDescriptiveContent->Policies->Policy->FeePolicies->FeePolicy;
						}
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-setting">
								<div class="btn-group-inline">
									<select id="vcm-hotel-feepolicies-dropdown-list" class="vcm-listing-editable vcm-multi-select">
										<option></option>
									<?php
									foreach ($fee_type_codes as $fee_code => $fee_type) {
										$name_extra  = isset($child_fee_codes[$fee_code]) ? ' (' . JText::_('VCMCHILDREN') . ')' : '';
										?>
										<option value="<?php echo $this->escape($fee_code); ?>"><?php echo $fee_type . $name_extra; ?></option>
										<?php
									}
									?>
									</select>
									<button type="button" class="btn vcm-config-btn" onclick="vcmNewFeePolicy();"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::_('VCMBCAHADD'); ?></button>
								</div>
								<span class="vcm-param-setting-comment">Select the type of fee policy to add.</span>
							</div>
						</div>
						<?php
					}
					?>
						<div class="vcm-bookingcom-feepolicies">
					<?php

					if ($feepolicies) {
						foreach ($feepolicies as $feepolicy) {
							$feepol_attr = $feepolicy->attributes();
							// attempt to update the default decimals places
							if (isset($feepol_attr->DecimalPlaces)) {
								$def_decimal_places = (int)$feepol_attr->DecimalPlaces;
							}
							// build information
							$fee_code 	 = (string)$feepol_attr->Code;
							$decimals 	 = isset($feepol_attr->DecimalPlaces) ? (int)$feepol_attr->DecimalPlaces : 0;
							$divider 	 = pow(10, $decimals);
							$is_percent  = isset($feepol_attr->Percent);
							$amount 	 = $is_percent ? (int)$feepol_attr->Percent : (int)$feepol_attr->Amount;
							$real_amount = VikBooking::numberFormat($amount / $divider);
							$type_incl 	 = isset($feepol_attr->Type) ? (string)$feepol_attr->Type : '';
							$charge_freq = isset($feepol_attr->ChargeFrequency) ? (string)$feepol_attr->ChargeFrequency : '';
							$charge_freq = $charge_freq && isset($charge_type_codes[$charge_freq]) ? $charge_type_codes[$charge_freq] : '';
							$inv_code 	 = isset($feepol_attr->InvCode) ? JText::_('VCM_ROOM') . ' ' . (string)$feepol_attr->InvCode : '';
							$name_extra  = isset($child_fee_codes[$fee_code]) ? ' (' . JText::_('VCMCHILDREN') . ')' : '';
							$min_age 	 = isset($feepol_attr->MinAge) ? (int)$feepol_attr->MinAge : null;
							$max_age 	 = isset($feepol_attr->MaxAge) ? (int)$feepol_attr->MaxAge : null;
							$fee_info 	 = [$type_incl, $charge_freq, $inv_code];
							if (isset($min_age)) {
								$fee_info[] = JText::_('VCM_MIN_AGE') . ': ' . $min_age;
							}
							if (isset($max_age)) {
								$fee_info[] = JText::_('VCM_MAX_AGE') . ': ' . $max_age;
							}
							// check for TPA Extensions
							if (isset($feepolicy->TPA_Extensions)) {
								if (isset($feepolicy->TPA_Extensions->Conditions->Condition)) {
									// cleaning fees
									$fee_info[] = 'Condition: ' . (string)$feepolicy->TPA_Extensions->Conditions->Condition->attributes()->Type;
								}
								if (isset($feepolicy->TPA_Extensions->ParkingFeePolicy)) {
									// parking fees
									$parking_attr = $feepolicy->TPA_Extensions->ParkingFeePolicy->attributes();
									foreach ($parking_attr as $attr_name => $attr_val) {
										$fee_info[] = (string)$attr_name . ': ' . (string)$attr_val;
									}
								}
								if (isset($feepolicy->TPA_Extensions->InternetFeePolicy)) {
									// internet fees
									$internet_attr = $feepolicy->TPA_Extensions->InternetFeePolicy->attributes();
									foreach ($internet_attr as $attr_name => $attr_val) {
										$fee_info[] = (string)$attr_name . ': ' . (string)$attr_val;
									}
								}
							}
							?>
							<div class="vcm-params-block vcm-bookingcom-feepolicy">
								<div class="vcm-param-container" data-related-group="feepolicies">
									<div class="vcm-param-label">
										<strong><?php echo (isset($fee_type_codes[$fee_code]) ? $fee_type_codes[$fee_code] : $fee_code) . $name_extra; ?></strong>
										<input type="hidden" name="feepolicies[]" class="vcm-hidden-disabled" value="<?php echo $this->escape(json_encode(VCMBookingcomContent::buildFeePolicyPayload($feepolicy))); ?>" />
									</div>
									<div class="vcm-param-setting">
										<button type="button" class="btn btn-danger" onclick="vcmRemoveFeePolicy(this);"><?php VikBookingIcons::e('times-circle'); ?></button>
									</div>
								</div>
								<div class="vcm-param-container">
									<div class="vcm-param-label"><?php echo (!$is_percent ? $hotel_currency . ' ' : '') . $real_amount . ($is_percent ? '%' : ''); ?></div>
									<div class="vcm-param-setting"><?php echo implode(', ', array_filter($fee_info)); ?></div>
								</div>
							</div>
							<?php
						}
					}
					?>

						</div>

					</div>
				</div>
			</fieldset>

			<fieldset class="adminform vcm-listings-listing-wrap" data-landto="policyinfo">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('plane-arrival'); ?> <?php echo JText::_('VCMBCAHPOLICIES') . ' - ' . JText::_('VCMMENUORDERS'); ?></legend>
					<div class="vcm-params-container">

					<?php
					$policyinfo = null;
					$petspolicy = null;
					if (is_object($hotel_info)) {
						if (isset($hotel_info->HotelDescriptiveContents->HotelDescriptiveContent->Policies->Policy->PolicyInfo)) {
							$policyinfo = $hotel_info->HotelDescriptiveContents->HotelDescriptiveContent->Policies->Policy->PolicyInfo->attributes();
						}
						if (isset($hotel_info->HotelDescriptiveContents->HotelDescriptiveContent->Policies->Policy->PetsPolicies)) {
							$petspolicy = $hotel_info->HotelDescriptiveContents->HotelDescriptiveContent->Policies->Policy->PetsPolicies;
						}
					}

					if ($policyinfo) {
						?>
						<div class="vcm-params-block">

							<div class="vcm-param-container" data-related-group="policyinfo">
								<div class="vcm-param-label"><?php echo JText::_('VCMPVIEWORDERSVBFOUR'); ?></div>
								<div class="vcm-param-setting">
									<input type="text" name="policyinfo[checkintime]" value="<?php echo $this->escape((isset($policyinfo->CheckInTime) ? (string)$policyinfo->CheckInTime : '')); ?>" />
									<span class="vcm-param-setting-comment">Format: HH:MM (from) or HH:MM-HH:MM (from-to). Only &quot;from&quot; is required; &quot;to&quot; is optional. 24-hour check-in can be specified using 00:00-00:00. Another valid example is 14:00-22:30.</span>
								</div>
							</div>

							<div class="vcm-param-container" data-related-group="policyinfo">
								<div class="vcm-param-label"><?php echo JText::_('VCMPVIEWORDERSVBFIVE'); ?></div>
								<div class="vcm-param-setting">
									<input type="text" name="policyinfo[checkouttime]" value="<?php echo $this->escape((isset($policyinfo->CheckOutTime) ? (string)$policyinfo->CheckOutTime : '')); ?>" />
									<span class="vcm-param-setting-comment">Format: HH:MM (from) or HH:MM-HH:MM (from-to). Only &quot;from&quot; is required; &quot;to&quot; is optional. 24-hour check-out can be specified using 00:00-00:00. Another valid example is 10:00-11:30.</span>
								</div>
							</div>

							<div class="vcm-param-container" data-related-group="policyinfo">
								<div class="vcm-param-label"><?php echo JText::_('VCMBCARCMAXGUESTS'); ?></div>
								<div class="vcm-param-setting">
									<input type="number" name="policyinfo[totalguestcount]" value="<?php echo isset($policyinfo->TotalGuestCount) ? (int)$policyinfo->TotalGuestCount : ''; ?>" min="0" max="99999" />
									<span class="vcm-param-setting-comment">The total number of guests that can stay at the property at a given time.</span>
								</div>
							</div>

							<div class="vcm-param-container" data-related-group="policyinfo">
								<div class="vcm-param-label"><?php echo JText::_('VCM_ACCEPT_GUEST_TYPE'); ?></div>
								<div class="vcm-param-setting">
									<select name="policyinfo[acceptedguesttype]" onchange="vcmHandleAccGuestType(this.value);">
										<option value="AdultOnly"<?php echo !isset($policyinfo->AcceptedGuestType) || (string)$policyinfo->AcceptedGuestType == 'AdultOnly' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMADULTS'); ?></option>
										<option value="ChildrenAllowed"<?php echo isset($policyinfo->AcceptedGuestType) && !strcasecmp((string)$policyinfo->AcceptedGuestType, 'ChildrenAllowed') ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMBCARCCRIBS'); ?></option>
									</select>
									<span class="vcm-param-setting-comment">Specifies whether the property admits adults and children, or only adults.</span>
								</div>
							</div>

							<div class="vcm-param-container vcm-param-nested vcm-bookingcom-minguestage" data-related-group="policyinfo" style="<?php echo !isset($policyinfo->AcceptedGuestType) || (string)$policyinfo->AcceptedGuestType == 'AdultOnly' ? 'display: none;' : ''; ?>">
								<div class="vcm-param-label"><?php echo JText::_('VCM_MIN_GUEST_AGE'); ?></div>
								<div class="vcm-param-setting">
									<input type="number" name="policyinfo[minguestage]" value="<?php echo isset($policyinfo->MinGuestAge) ? (int)$policyinfo->MinGuestAge : ''; ?>" min="0" max="99" />
									<span class="vcm-param-setting-comment">Specifies the minimum age allowed for children.</span>
								</div>
							</div>

						</div>

						<?php
					}

					if ($petspolicy) {
						$pets_code = (string)$petspolicy->attributes()->PetsAllowedCode;
						$pets_fee  = '';
						if (isset($petspolicy->PetsPolicy)) {
							$pets_pol_attr = $petspolicy->PetsPolicy->attributes();
							$pets_fee = isset($pets_pol_attr->NonRefundableFee) ? (string)$pets_pol_attr->NonRefundableFee : $pets_fee;
						}
						?>
						<div class="vcm-params-block">

							<div class="vcm-param-container" data-related-group="petspolicies">
								<div class="vcm-param-label"><?php echo JText::_('PETS_ALLOWED'); ?></div>
								<div class="vcm-param-setting">
									<select name="petspolicy[petsallowedcode]" onchange="vcmHandlePetsAllowedCode(this.value);">
										<option value="Pets Allowed"<?php echo !strcasecmp($pets_code, 'Pets Allowed') ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMYES'); ?></option>
										<option value="Pets Not Allowed"<?php echo !strcasecmp($pets_code, 'Pets Not Allowed') ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMNO'); ?></option>
										<option value="Pets By Arrangements"<?php echo !strcasecmp($pets_code, 'Pets By Arrangements') ? ' selected="selected"' : ''; ?>>By Arrangements</option>
									</select>
									<span class="vcm-param-setting-comment">Specifies whether pets are allowed, or if they are allowed only by arrangements.</span>
								</div>
							</div>

							<div class="vcm-param-container vcm-param-nested vcm-bookingcom-petfeenonref" data-related-group="petspolicies" style="<?php echo !strcasecmp($pets_code, 'Pets Not Allowed') ? 'display: none;' : ''; ?>">
								<div class="vcm-param-label"><?php echo JText::_('VCMBCAHFEETYPE34'); ?></div>
								<div class="vcm-param-setting">
									<select name="petspolicy[nonrefundablefee]">
										<option value="free"<?php echo !strcasecmp($pets_fee, 'free') ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMBCARCAMENITYVAL2'); ?></option>
										<option value="charges_may_apply"<?php echo !strcasecmp($pets_fee, 'charges_may_apply') ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCM_CHARGES_MAY_APPLY'); ?></option>
									</select>
								</div>
							</div>
							
						</div>
						<?php
					}
					?>

					</div>
				</div>
			</fieldset>

			<fieldset class="adminform vcm-listings-listing-wrap" data-landto="contracts">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('file-contract'); ?> <?php echo JText::_('VCMBCAHCINFOTYPE2'); ?></legend>
					<div class="vcm-params-container">

					<?php
					$legal_contact_email = '';
					if (is_object($hotel_info)) {
						if (is_object($contracts) && isset($contracts->legal_email)) {
							$legal_contact_email = $contracts->legal_email;
						}
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-setting">
								<div class="btn-group-inline">
									<input type="email" id="vcm-bookingcom-contract-email" class="vcm-listing-editable" value="<?php echo $this->escape($legal_contact_email); ?>" placeholder="Legal Contact Email" />
									<button type="button" class="btn vcm-config-btn" onclick="vcmCheckContractStatus();"><?php VikBookingIcons::e('check-circle'); ?> <?php echo JText::_('VCM_CHECK_STATUS'); ?></button>
								</div>
								<span class="vcm-param-setting-comment">Enter your Booking.com's legal contact email to retrieve contract status and legal details.</span>
							</div>
						</div>
						<?php

						// default and expected contract field details
						$contract_dets_available = false;
						$default_contract_fields = [
							'legal_entity_id' 			 => '',
							'contract_signed' 			 => '',
							'legal_contact_name' 		 => '',
							'legal_contact_email' 		 => '',
							'legal_contact_phone_number' => '',
							'total_number_of_properties' => '',
							'company_name' 				 => '',
						];

						// cast to object for compliance with the actual response
						$default_contract_fields = (object)$default_contract_fields;

						if (is_object($contracts) && isset($contracts->data) && is_object($contracts->data) && count(get_object_vars($contracts->data))) {
							// display the stored information
							$default_contract_fields = $contracts->data;
							$contract_dets_available = true;
						}

						?>
						<div class="vcm-params-block vcm-bookingcom-contract-data" style="<?php echo !$contract_dets_available ? 'display: none;' : ''; ?>">

						<?php
						foreach ($default_contract_fields as $contract_prop => $contract_val) {
							$contract_field_name = ucwords(str_replace('_', ' ', $contract_prop));
							$contract_field_clss = 'vcm-bookingcom-contract-' . str_replace('_', '-', $contract_prop);
							?>
							<div class="vcm-param-container">
								<div class="vcm-param-label"><?php echo $contract_field_name; ?></div>
								<div class="vcm-param-setting <?php echo $contract_field_clss; ?>"><?php echo $contract_val; ?></div>
							</div>
							<?php
						}
						?>

						</div>
						<?php

						if ($contract_dets_available && isset($contracts->data->contract_signed) && empty($contracts->data->contract_signed) && !JFactory::getSession()->get('vcmBcomContractResendLink', 0)) {
							// contract has not been signed yet, and link to contracting tools has not been requested
							?>
						<div class="vcm-params-block vcm-bookingcom-contract-resendlink">

							<div class="vcm-param-container">
								<div class="vcm-param-setting">
									<button type="button" class="btn vcm-config-btn" onclick="vcmHandleContractResendLink();"><?php VikBookingIcons::e('envelope'); ?> Resend email with link to contracting tool</button>
									<span class="vcm-param-setting-comment">In case the contract has not been signed, you can request to Booking.com to resend an email with the link to their contracting tool.</span>
								</div>
							</div>

						</div>
							<?php
						}
					}
					?>

					</div>
				</div>
			</fieldset>

		</div>

	</div>
<?php
if (VikRequest::getInt('e4j_debug', 0, 'request')) {
	// echo 'property_data<pre>' . print_r($this->property_data, true) . '</pre>';
	if (is_object($hotel_info)) {
		// display property details fields
		?>
		Property Raw XML:<br/><pre><?php echo htmlentities($this->property_data->property); ?></pre>
		<?php
	}
	echo '$house_rules<br/><pre>' . print_r($house_rules, true) . '</pre><br/>';
	if (is_object($rateplans_info)) {
		echo 'Rate Plans Raw XML:<br/><pre>' . htmlentities($this->property_data->rateplans) . '</pre><br/>';
	}
	if (is_object($roomrates_info)) {
		echo 'Room Rates Raw XML:<br/><pre>' . htmlentities($this->property_data->roomrates) . '</pre><br/>';
	}
	echo '$licenses<br/><pre>' . print_r($licenses, true) . '</pre><br/>';
	echo '$contracts<br/><pre>' . print_r($contracts, true) . '</pre><br/>';
}
?>
	<input type="hidden" name="task" value="" />
</form>

<div class="vcm-floating-scrolltop" style="display: none;">
	<div class="vcm-floating-scrolltop-inner">
		<button type="button" class="btn vcm-scrolltop-btn" id="vcm-scrolltop-trigger"><?php VikBookingIcons::e('arrow-up'); ?></button>
	</div>
</div>

<div class="vcm-bookingcom-html-helpers" style="display: none;">

	<div class="vcm-bookingcom-contactinfo-html-helper">
		<div class="vcm-bookingcom-contactinfo-helper">
			<div class="vcm-params-container">

				<form method="post" id="vcm-bookingcom-contactinfo-form">
					<div class="vcm-params-block">
						<div class="vcm-param-container">
							<div class="vcm-param-label"><strong><?php echo JText::_('VCMRESLOGSTYPE'); ?></strong></div>
							<div class="vcm-param-setting">
								<select name="type">
									<option value=""></option>
								<?php
								foreach ($contact_types as $contact_type_code => $contact_type_descr) {
									?>
									<option value="<?php echo $this->escape($contact_type_code); ?>"><?php echo ucfirst($contact_type_code); ?></option>
									<?php
								}
								?>
								</select>
							</div>
						</div>

					</div>

					<div class="vcm-params-block">

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMBCAHFIRSTNAME') . ' + ' . JText::_('VCMBCAHSURNAME'); ?></div>
							<div class="vcm-param-setting">
								<input type="text" name="name[givenname]" value="" />
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMBCAHGENDER'); ?></div>
							<div class="vcm-param-setting">
								<select name="name[gender]">
									<option value=""></option>
									<option value="male"><?php echo JText::_('VCMBCAHMALE'); ?></option>
									<option value="female"><?php echo JText::_('VCMBCAHFEMALE'); ?></option>
								</select>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMBCAHJOBTITLE'); ?></div>
							<div class="vcm-param-setting">
								<input type="text" name="name[jobtitle]" value="" />
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMBCAHLANGUAGE'); ?></div>
							<div class="vcm-param-setting">
								<input type="text" name="name[language]" value="" maxlength="2" />
								<span class="vcm-param-setting-comment">Two-letter language code (i.e. &quot;en&quot;)</span>
							</div>
						</div>

					</div>

					<div class="vcm-params-block">

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMBCAHEMAIL'); ?></div>
							<div class="vcm-param-setting">
								<input type="text" name="email" value="" />
							</div>
						</div>

					</div>

					<div class="vcm-params-block">

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMTACHOTELPHONE') . ' (' . JText::_('VCMBCAHPHONETYPE1') . ')'; ?></div>
							<div class="vcm-param-setting">
								<input type="tel" name="phones[0][number]" data-contact-type-elem="phone-number" value="" />
								<input type="hidden" name="phones[0][type]" value="1" />
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMTACHOTELPHONE') . ' (' . JText::_('VCMBCAHPHONETYPE2') . ')'; ?></div>
							<div class="vcm-param-setting">
								<input type="tel" name="phones[1][number]" data-contact-type-elem="phone-number" value="" />
								<input type="hidden" name="phones[1][type]" value="3" />
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMTACHOTELPHONE') . ' (' . JText::_('VCMBCAHPHONETYPE3') . ')'; ?></div>
							<div class="vcm-param-setting">
								<input type="tel" name="phones[2][number]" data-contact-type-elem="phone-number" value="" />
								<input type="hidden" name="phones[2][type]" value="5" />
							</div>
						</div>

					</div>

					<div class="vcm-params-block">

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMBCAHADDLINE'); ?></div>
							<div class="vcm-param-setting">
								<input type="text" name="address[street]" value="" />
								<input type="hidden" name="address[tn]" value="" class="vcm-reset-hidden-field" data-origvalue="" />
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMTACHOTELCITY'); ?></div>
							<div class="vcm-param-setting">
								<input type="text" name="address[city]" value="" />
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMBCAHPOSCODE'); ?></div>
							<div class="vcm-param-setting">
								<input type="text" name="address[postcode]" value="" />
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMTACHOTELCOUNTRY'); ?></div>
							<div class="vcm-param-setting">
								<input type="text" name="address[country]" value="" maxlength="2" />
								<span class="vcm-param-setting-comment">Two-letter ISO standard country code.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMBCAHSTATEPROV'); ?></div>
							<div class="vcm-param-setting">
								<input type="text" name="address[stateprov]" value="" maxlength="2" />
								<span class="vcm-param-setting-comment">Two-letter ISO 3166-2 standard code to specify the state, province, or other subdivision.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Hidden</div>
							<div class="vcm-param-setting">
								<select name="address[hide]">
									<option value=""></option>
									<option value="0"><?php echo JText::_('VCMNO'); ?></option>
									<option value="1"><?php echo JText::_('VCMYES'); ?></option>
								</select>
								<span class="vcm-param-setting-comment">Whether the address should be hidden.</span>
							</div>
						</div>

					</div>
				</form>

			</div>
		</div>
	</div>

	<div class="vcm-bookingcom-newcontactinfo-html-helper">
		<div class="vcm-bookingcom-newcontactinfo-helper">
			<div class="vcm-params-block vcm-bookingcom-hotel-contactinfo">

				<div class="vcm-param-container" data-related-group="contactinfos">
					<div class="vcm-param-label">
						<strong class="vcm-bookingcom-type"></strong>
						<input type="hidden" name="contactinfos[]" class="vcm-bookingcom-contactinfo-json" value="" />
					</div>
					<div class="vcm-param-setting">
						<button type="button" class="btn" onclick="vcmEditContactInfo(this);"><?php VikBookingIcons::e('edit'); ?></button>
						<button type="button" class="btn btn-danger" onclick="vcmRemoveContactInfo(this);"><?php VikBookingIcons::e('times-circle'); ?></button>
					</div>
				</div>

				<div class="vcm-param-container" data-contactinfo="namejob">
					<div class="vcm-param-label">
						<span><?php VikBookingIcons::e('user'); ?></span>
						<span class="vcm-bookingcom-name"></span>
					</div>
					<div class="vcm-param-setting">
						<span class="vcm-bookingcom-job"></span>
					</div>
				</div>

				<div class="vcm-param-container" data-contactinfo="mailphone">
					<div class="vcm-param-label">
						<span><?php VikBookingIcons::e('envelope'); ?></span>
						<span class="vcm-bookingcom-mail"></span>
					</div>
					<div class="vcm-param-setting">
						<span><?php VikBookingIcons::e('phone'); ?></span>
						<span class="vcm-bookingcom-phone"></span>
					</div>
				</div>

				<div class="vcm-param-container" data-contactinfo="address">
					<div class="vcm-param-setting">
						<span><?php VikBookingIcons::e('location-arrow'); ?></span>
						<span class="vcm-bookingcom-address"></span>
					</div>
				</div>

			</div>
		</div>
	</div>

	<div class="vcm-bookingcom-taxpolicy-html-helper">
		<div class="vcm-bookingcom-taxpolicy-helper">
			<div class="vcm-params-container">

				<form method="post" id="vcm-bookingcom-taxpolicy-form">
					<div class="vcm-param-container">
						<div class="vcm-param-label"><strong><?php echo JText::_('VCMBCAHTAXTYPE'); ?></strong></div>
						<div class="vcm-param-setting">
							<select name="code">
								<option value=""></option>
							<?php
							foreach ($tax_type_codes as $tax_code => $tax_type) {
								?>
								<option value="<?php echo $this->escape($tax_code); ?>"><?php echo $tax_type; ?></option>
								<?php
							}
							?>
							</select>
						</div>
					</div>

					<div class="vcm-param-container">
						<div class="vcm-param-label"><?php echo JText::_('VCMBCAHAMOUNT'); ?></div>
						<div class="vcm-param-setting">
							<input type="hidden" name="decimalplaces" value="<?php echo $def_decimal_places; ?>" />
							<input type="number" name="amount" step="any" value="0" min="0" max="999999" />
							<select name="percent">
								<option value="percent">%</option>
								<option value="amount"><?php echo $hotel_currency; ?></option>
							</select>
						</div>
					</div>

					<div class="vcm-param-container">
						<div class="vcm-param-label"><?php echo JText::_('VCMBCAHINCLUS'); ?></div>
						<div class="vcm-param-setting">
							<select name="type">
								<option value="Inclusive"><?php echo JText::_('VCMBCAHINCLUS'); ?></option>
								<option value="Exclusive"><?php echo JText::_('VCMBCAHEXCLUS'); ?></option>
							</select>
							<span class="vcm-param-setting-comment">Specifies whether the tax is included in the room price or not.</span>
						</div>
					</div>

					<div class="vcm-param-container">
						<div class="vcm-param-label"><?php echo JText::_('VCMBCAHCHGFRQ'); ?></div>
						<div class="vcm-param-setting">
							<select name="chargefrequency" onchange="vcmCommentChargeFrequency(this.value);">
								<option value=""></option>
							<?php
							foreach ($charge_type_codes as $charge_code => $charge_name) {
								?>
								<option value="<?php echo $this->escape($charge_code); ?>"><?php echo $charge_name; ?></option>
								<?php
							}
							?>
							</select>
							<span class="vcm-param-setting-comment vcm-chargefreq-comment"></span>
						</div>
					</div>
				</form>

			</div>
		</div>
	</div>

	<div class="vcm-bookingcom-newtaxpolicy-html-helper">
		<div class="vcm-bookingcom-newtaxpolicy-helper">
			<div class="vcm-params-block vcm-bookingcom-taxpolicy" data-tax-code="">
				<div class="vcm-param-container">
					<div class="vcm-param-label">
						<strong class="vcm-bookingcom-newtaxpolicy-code"></strong>
						<input type="hidden" name="taxpolicies[]" class="vcm-bookingcom-taxpolicy-json" value="" />
					</div>
					<div class="vcm-param-setting">
						<button type="button" class="btn btn-danger" onclick="vcmRemoveTaxPolicy(this);"><?php VikBookingIcons::e('times-circle'); ?></button>
					</div>
				</div>
				<div class="vcm-param-container">
					<div class="vcm-param-label">
						<span class="vcm-bookingcom-newtaxpolicy-percent"></span>
					</div>
					<div class="vcm-param-setting">
						<span class="vcm-bookingcom-newtaxpolicy-type"></span>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="vcm-bookingcom-feepolicy-html-helper">
		<div class="vcm-bookingcom-feepolicy-helper">
			<div class="vcm-params-container">

				<form method="post" id="vcm-bookingcom-feepolicy-form">
					<div class="vcm-param-container">
						<div class="vcm-param-label"><strong><?php echo JText::_('VCMBCAHFEETYPE'); ?></strong></div>
						<div class="vcm-param-setting">
							<select name="code" disabled="disabled">
								<option value=""></option>
							<?php
							foreach ($fee_type_codes as $fee_code => $fee_type) {
								$name_extra  = isset($child_fee_codes[$fee_code]) ? ' (' . JText::_('VCMCHILDREN') . ')' : '';
								?>
								<option value="<?php echo $this->escape($fee_code); ?>"><?php echo $fee_type . $name_extra; ?></option>
								<?php
							}
							?>
							</select>
						</div>
					</div>

					<div class="vcm-param-container vcm-param-nested vcm-bookingcom-newfee-5035" style="display: none;">
						<div class="vcm-param-label"><?php echo JText::_('VCMBCAHFEETYPE35') . ' - ' . JText::_('VCMRESLOGSTYPE'); ?></div>
						<div class="vcm-param-setting">
							<select name="internettype">
								<option value="wifi">WiFi</option>
								<option value="wired">Wired</option>
								<option value="none">None</option>
							</select>
							<span class="vcm-param-setting-comment">Specifies the type of internet connection.</span>
						</div>
					</div>

					<div class="vcm-param-container vcm-param-nested vcm-bookingcom-newfee-5035" style="display: none;">
						<div class="vcm-param-label"><?php echo JText::_('VCMBCAHFEETYPE35') . ' - Coverage'; ?></div>
						<div class="vcm-param-setting">
							<select name="internetcoverage">
								<option value="entire_property">Entire property</option>
								<option value="public_areas">Public areas</option>
								<option value="all_rooms">All rooms</option>
								<option value="some_rooms">Some rooms</option>
								<option value="business_centre">Business centre</option>
							</select>
							<span class="vcm-param-setting-comment">Specifies the area covered by internet.</span>
						</div>
					</div>

					<div class="vcm-param-container vcm-param-nested vcm-bookingcom-newfee-5036" style="display: none;">
						<div class="vcm-param-label"><?php echo JText::_('VCMBCAHFEETYPE36') . ' - ' . JText::_('VCMRESLOGSTYPE'); ?></div>
						<div class="vcm-param-setting">
							<select name="parkingtype">
								<option value="on_site">On site</option>
								<option value="location_nearby">Location nearby</option>
								<option value="none">None</option>
							</select>
							<span class="vcm-param-setting-comment">Specifies the type of parking the property offers.</span>
						</div>
					</div>

					<div class="vcm-param-container vcm-param-nested vcm-bookingcom-newfee-5036" style="display: none;">
						<div class="vcm-param-label"><?php echo JText::_('VCMBCAHFEETYPE36') . ' - Reservation'; ?></div>
						<div class="vcm-param-setting">
							<select name="parkingreservation">
								<option value="not_available">Not available</option>
								<option value="needed">Needed</option>
								<option value="not_needed">Not needed</option>
							</select>
							<span class="vcm-param-setting-comment">Specifies whether guests can/must reserve a parking space in advance.</span>
						</div>
					</div>

					<div class="vcm-param-container vcm-param-nested vcm-bookingcom-newfee-5036" style="display: none;">
						<div class="vcm-param-label"><?php echo JText::_('VCMBCAHFEETYPE36') . ' - Property'; ?></div>
						<div class="vcm-param-setting">
							<select name="parkingproperty">
								<option value="public">Public</option>
								<option value="private">Private</option>
							</select>
							<span class="vcm-param-setting-comment">Specifies whether the parking area is public or private.</span>
						</div>
					</div>

					<div class="vcm-param-container vcm-bookingcom-newfee-forchildren">
						<div class="vcm-param-label"><?php echo JText::_('VCM_MIN_AGE'); ?></div>
						<div class="vcm-param-setting">
							<input type="number" name="minage" step="1" value="" min="0" max="199" />
							<span class="vcm-param-setting-comment">Specifies the minimum age for the children policy to apply.</span>
						</div>
					</div>

					<div class="vcm-param-container vcm-bookingcom-newfee-forchildren">
						<div class="vcm-param-label"><?php echo JText::_('VCM_MAX_AGE'); ?></div>
						<div class="vcm-param-setting">
							<input type="number" name="maxage" step="1" value="" min="0" max="199" />
							<span class="vcm-param-setting-comment">Specifies the maximum age for the children policy to apply.</span>
						</div>
					</div>

					<div class="vcm-param-container">
						<div class="vcm-param-label"><?php echo JText::_('VCMBCAHAMOUNT'); ?></div>
						<div class="vcm-param-setting">
							<input type="hidden" name="decimalplaces" value="<?php echo $def_decimal_places; ?>" />
							<input type="number" name="amount" step="any" value="0" min="0" max="999999" />
							<select name="percent">
								<option value="percent">%</option>
								<option value="amount"><?php echo $hotel_currency; ?></option>
							</select>
						</div>
					</div>

					<div class="vcm-param-container">
						<div class="vcm-param-label"><?php echo JText::_('VCMBCAHINCLUS'); ?></div>
						<div class="vcm-param-setting">
							<select name="type" class="vcm-bookingcom-newfee-inclexcl" onchange="vcmHandleFeeType(this.value);">
								<option value="Inclusive"><?php echo JText::_('VCMBCAHINCLUS'); ?></option>
								<option value="Exclusive"><?php echo JText::_('VCMBCAHEXCLUS'); ?></option>
							</select>
							<span class="vcm-param-setting-comment">Specifies whether the fee is included in the room price or not.</span>
						</div>
					</div>

					<div class="vcm-param-container vcm-param-nested vcm-bookingcom-newfee-5009" style="display: none;">
						<div class="vcm-param-label"><?php echo JText::_('VCMBCAHFEETYPE9') . ' - Condition'; ?></div>
						<div class="vcm-param-setting">
							<select name="condition">
								<option value="guest_brings_pet">Guest brings pet</option>
								<option value="guest_doesnt_clean_before_checkout">Guest doesn't clean before checkout</option>
								<option value="guest_smokes">Guest smokes</option>
							</select>
							<span class="vcm-param-setting-comment">Specifies when a guest must pay the extra cleaning fee.</span>
						</div>
					</div>

					<div class="vcm-param-container">
						<div class="vcm-param-label"><?php echo JText::_('VCMBCAHCHGFRQ'); ?></div>
						<div class="vcm-param-setting">
							<select name="chargefrequency" onchange="vcmCommentChargeFrequency(this.value);">
								<option value=""></option>
							<?php
							foreach ($charge_type_codes as $charge_code => $charge_name) {
								?>
								<option value="<?php echo $this->escape($charge_code); ?>"><?php echo $charge_name; ?></option>
								<?php
							}
							?>
							</select>
							<span class="vcm-param-setting-comment vcm-chargefreq-comment"></span>
						</div>
					</div>
				</form>

			</div>
		</div>
	</div>

	<div class="vcm-bookingcom-newfeepolicy-html-helper">
		<div class="vcm-bookingcom-newfeepolicy-helper">
			<div class="vcm-params-block vcm-bookingcom-feepolicy">
				<div class="vcm-param-container">
					<div class="vcm-param-label">
						<strong class="vcm-bookingcom-newfeepolicy-code"></strong>
						<input type="hidden" name="feepolicies[]" class="vcm-bookingcom-feepolicy-json" value="" />
					</div>
					<div class="vcm-param-setting">
						<button type="button" class="btn btn-danger" onclick="vcmRemoveFeePolicy(this);"><?php VikBookingIcons::e('times-circle'); ?></button>
					</div>
				</div>
				<div class="vcm-param-container">
					<div class="vcm-param-label">
						<span class="vcm-bookingcom-newfeepolicy-percent"></span>
					</div>
					<div class="vcm-param-setting">
						<span class="vcm-bookingcom-newfeepolicy-type"></span>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="vcm-bookingcom-rateplan-html-helper">
		<div class="vcm-bookingcom-rateplan-helper">
			<div class="vcm-params-container">

				<form method="post" id="vcm-bookingcom-rateplan-form">
					<div class="vcm-param-container">
						<div class="vcm-param-label"><strong><?php echo JText::_('VCMROOMSRELATIONSNAME'); ?></strong></div>
						<div class="vcm-param-setting">
							<input type="text" name="rplan_name" value="" placeholder="<?php echo $this->escape(JText::_('VCMRARRATEPLAN')); ?>" />
						</div>
					</div>

					<div class="vcm-param-container">
						<div class="vcm-param-label"><?php echo JText::_('VCMRESLOGSTYPE'); ?></div>
						<div class="vcm-param-setting">
							<select name="rplan_type" onchange="vcmHandleRateplanType(this.value);">
								<option value="parent"><?php echo JText::_('VCMRARPARENTSRATEPLANS'); ?></option>
								<option value="derived"><?php echo JText::_('VCMDERIVEDRATEPLANS'); ?></option>
							</select>
							<span class="vcm-param-setting-comment">Specifies whether the new rate plan will have to be managed through your Channel Manager.</span>
						</div>
					</div>

					<div class="vcm-param-container vcm-param-nested vcm-bookingcom-newrplan-derived" style="display: none;">
						<div class="vcm-param-label"><?php echo JText::_('VCMRARRATEPLAN') . ' - Parent'; ?></div>
						<div class="vcm-param-setting">
							<select name="rplan_parentid">
								<option></option>
							<?php
							foreach ($sorted_rateplans as $bcom_rplan) {
								// make sure to take only parent rate plans
								if (isset($bcom_rplan->is_child_rate) && $bcom_rplan->is_child_rate == '1') {
									continue;
								}
								?>
								<option value="<?php echo $bcom_rplan->id; ?>"><?php echo $bcom_rplan->name . ' (' . $bcom_rplan->id . ')'; ?></option>
								<?php
							}
							?>
							</select>
							<span class="vcm-param-setting-comment">Specifies the parent rate plan from which this rate should be derived.</span>
						</div>
					</div>

					<div class="vcm-param-container vcm-param-nested vcm-bookingcom-newrplan-derived" style="display: none;">
						<div class="vcm-param-label">Percentage</div>
						<div class="vcm-param-setting">
							<input type="number" name="rplan_percentage" value="" min="1" max="200" />
							<span class="vcm-param-setting-comment">Specifies the percentage in relation to the price connected to the parent rate plan. Minimum value: 1. Maximum value: 200. For example, 90 refers to a 10% discount, while 120 refers to a 20% surplus.</span>
						</div>
					</div>

					<div class="vcm-param-container vcm-param-nested vcm-bookingcom-newrplan-derived" style="display: none;">
						<div class="vcm-param-label">Follows Price</div>
						<div class="vcm-param-setting">
							<select name="rplan_followsprice">
								<option value="1"><?php echo JText::_('VCMYES'); ?></option>
								<option value="0"><?php echo JText::_('VCMNO'); ?></option>
							</select>
							<span class="vcm-param-setting-comment">Indicates whether the rate relation follows the price of the parent rate plan.</span>
						</div>
					</div>

					<div class="vcm-param-container vcm-param-nested vcm-bookingcom-newrplan-derived" style="display: none;">
						<div class="vcm-param-label">Follows Restrictions</div>
						<div class="vcm-param-setting">
							<select name="rplan_followsrestrictions">
								<option value="1"><?php echo JText::_('VCMYES'); ?></option>
								<option value="0"><?php echo JText::_('VCMNO'); ?></option>
							</select>
							<span class="vcm-param-setting-comment">Indicates whether the rate relation follows the restrictions of the parent rate plan.</span>
						</div>
					</div>

					<div class="vcm-param-container vcm-param-nested vcm-bookingcom-newrplan-derived" style="display: none;">
						<div class="vcm-param-label">Follows Policy Group</div>
						<div class="vcm-param-setting">
							<select name="rplan_followspolicygroup">
								<option value="1"><?php echo JText::_('VCMYES'); ?></option>
								<option value="0"><?php echo JText::_('VCMNO'); ?></option>
							</select>
							<span class="vcm-param-setting-comment">Indicates whether the rate relation follows the policies of the parent rate plan.</span>
						</div>
					</div>

					<div class="vcm-param-container vcm-param-nested vcm-bookingcom-newrplan-derived" style="display: none;">
						<div class="vcm-param-label">Follows Closed</div>
						<div class="vcm-param-setting">
							<select name="rplan_followsclosed">
								<option value="0">Never follows parent rate plan status</option>
								<option value="1">Follows parent status when parent rate plan is open</option>
								<option value="2">Follows parent status when parent rate plan is closed</option>
								<option value="3">Always follows parent rate plan status</option>
							</select>
							<span class="vcm-param-setting-comment">Indicates how the rate relation follows the status (active or deactivated) of the parent rate plan.</span>
						</div>
					</div>
				</form>

			</div>
		</div>
	</div>

	<div class="vcm-bookingcom-newrateplan-html-helper">
		<div class="vcm-bookingcom-newrateplan-helper">
			<div class="vcm-params-block vcm-bookingcom-rateplan">
				<div class="vcm-param-container">
					<div class="vcm-param-label">
						<strong class="vcm-bookingcom-newrateplan-name"></strong>
						<input type="hidden" name="rateplans_create[]" class="vcm-bookingcom-newrateplan-json" value="" />
					</div>
					<div class="vcm-param-setting">
						<?php echo JText::_('VCM_MODELEM_AFTER_SAVE') . ' (' . JText::_('VCM_CREATE_NEW') . ')'; ?>
					</div>
				</div>
				<div class="vcm-param-container">
					<div class="vcm-param-label">
						<span class="badge badge-success"><?php echo JText::_('VCM_CREATE_NEW'); ?></span>
					</div>
					<div class="vcm-param-setting">
						<span class="vcm-bookingcom-newrateplan-type"></span>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="vcm-bookingcom-roomrate-html-helper">
		<div class="vcm-bookingcom-roomrate-helper">
			<div class="vcm-params-container">

				<form method="post" id="vcm-bookingcom-roomrate-form">

					<div class="vcm-params-block">

						<div class="vcm-param-container">
							<div class="vcm-param-label">
								<span><?php echo JText::_('VCM_ROOM'); ?></span>
							</div>
							<div class="vcm-param-setting">
								<span class="vcm-bookingcom-roomrate-roomname" style="font-weight: bold;"></span>
								<input type="hidden" name="room_id" value="" />
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMRARRATEPLAN'); ?></div>
							<div class="vcm-param-setting">
								<select name="rateplan_id">
									<option value=""></option>
								</select>
								<span class="vcm-param-setting-comment">Specifies the rate plan to link to the selected room.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMBCAPNMEALPLANS'); ?></div>
							<div class="vcm-param-setting">
								<select name="meal_code">
									<option value=""></option>
								<?php
								foreach (VCMBookingcomContent::getMealPlanCodes() as $meal_code => $meal_name) {
									?>
									<option value="<?php echo $meal_code; ?>"><?php echo $meal_name; ?></option>
									<?php
								}
								?>
								</select>
								<span class="vcm-param-setting-comment">Specifies the included meal plan details (if any).</span>
							</div>
						</div>

					</div>

					<div class="vcm-params-block">

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCM_TA_CANCELLATION_POLICY'); ?></div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-setting">
								<select name="cancpolicy_code" class="vcm-bookingcom-roomrate-cancpolicycode vcm-multi-select">
									<option></option>
								<?php
								$cpolicy_group = null;
								foreach (VCMBookingcomContent::getCancellationPolicyCodes() as $cancp_group) {
									if ($cpolicy_group != $cancp_group['group']) {
										if (!is_null($cpolicy_group)) {
											// close previous node
											echo '</optgroup>' . "\n";
										}
										// open new node
										echo '<optgroup label="' . $this->escape($cancp_group['group']) . '">' . "\n";
										// update current group
										$cpolicy_group = $cancp_group['group'];
									}
									foreach ($cancp_group['list'] as $cancp_code => $cancp_name) {
										?>
										<option value="<?php echo $this->escape($cancp_code); ?>"><?php echo '(' . $cancp_code . ') ' . $cancp_name; ?></option>
										<?php
									}
								}
								if (!is_null($cpolicy_group) && $cpolicy_group == $cancp_group['group']) {
									// close last node
									echo '</optgroup>' . "\n";
								}
								?>
								</select>
								<span class="vcm-param-setting-comment">Specifies the cancellation policy for this room-rate relation.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCM_EXISTING_CANC_POLICIES'); ?></div>
							<div class="vcm-param-setting vcm-block-wseparators">
							<?php
							foreach ($used_cancpolicies as $used_cancpolicy_code => $used_cancpolicy_name) {
								?>
								<div><?php VikBookingIcons::e('hand-paper'); ?> <?php echo '(' . $used_cancpolicy_code . ') ' . $used_cancpolicy_name; ?></div>
								<?php
							}
							?>
							</div>
						</div>

					</div>

					<div class="vcm-params-block">

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::_('VCM_BUNDLED_VALUE_ADDS'); ?></div>
							<div class="vcm-param-setting">
								<select name="bundled_valueadds" class="vcm-bookingcom-roomrate-valueadds vcm-multi-select">
									<option></option>
								<?php
								$vads_group = null;
								foreach ($vads_codes as $vad_service) {
									$vad_group_name = implode(' - ', array_unique([$vad_service['macro'], $vad_service['group']]));
									if ($vads_group != $vad_group_name) {
										if (!is_null($vads_group)) {
											// close previous node
											echo '</optgroup>' . "\n";
										}
										// open new node
										echo '<optgroup label="' . $this->escape($vad_group_name) . '">' . "\n";
										// update current group
										$vads_group = $vad_group_name;
									}
									foreach ($vad_service['list'] as $vads_code => $vads_data) {
										?>
										<option value="<?php echo $this->escape($vads_code); ?>"><?php echo $vads_data['name']; ?></option>
										<?php
									}
								}
								$vad_group_name = implode(' - ', array_unique([$vad_service['macro'], $vad_service['group']]));
								if (!is_null($vads_group) && $vads_group == $vad_group_name) {
									// close last node
									echo '</optgroup>' . "\n";
								}
								?>
								</select>
								<span class="vcm-param-setting-comment">Choose up to 5 value-added services or products per room-rate. Value adds are extra services or products <u>included</u> in the room-rate.</span>
							</div>
						</div>

						<div class="vcm-bookingcom-roomrate-valueadds-included" style="display: none;"></div>

					</div>

				</form>

			</div>
		</div>
	</div>

	<div class="vcm-bookingcom-newroomrate-html-helper">
		<div class="vcm-bookingcom-newroomrate-helper">
			<div class="vcm-param-container vcm-bookingcom-roomrate-rplan" data-rateplan-id="">
				<div class="vcm-param-label"><?php echo JText::_('VCM_MODELEM_AFTER_SAVE') . ' (' . JText::_('VCM_CREATE_NEW') . ')'; ?></div>
				<div class="vcm-param-setting">
					<strong><?php VikBookingIcons::e('tag'); ?> <span class="vcm-bookingcom-newroomrate-rplan-name"></span></strong>
					<div class="vcm-bookingcom-newroomrate-rplan-id"></div>
					<input type="hidden" name="roomrates_add[]" class="vcm-bookingcom-roomrate-json" value="" />
				</div>
			</div>
		</div>
	</div>

</div>

<a class="vcm-hidden-refresh-url" href="index.php?option=com_vikchannelmanager&view=bmngproperty" style="display: none;"></a>
<a class="vcm-hidden-dash-url" href="index.php?option=com_vikchannelmanager" style="display: none;"></a>

<script type="text/javascript">
	/* JSON vars */
	var vcm_bookingcom_hotel_amenities = <?php echo json_encode($hac_list); ?>;
	var fee_tax_type_codes 			   = <?php echo json_encode(VCMBookingcomContent::getFeeTaxTypeCodes()); ?>;
	var child_fee_codes 			   = <?php echo json_encode(VCMBookingcomContent::getFeeTaxTypeCodes('children')); ?>;
	var charge_type_codes 			   = <?php echo json_encode(VCMBookingcomContent::getChargeTypeCodes()); ?>;
	var charge_frequency_comments 	   = <?php echo json_encode(VCMBookingcomContent::getChargeTypeCodeComments()); ?>;
	var used_cancpolicies 			   = <?php echo json_encode($used_cancpolicies); ?>;
	var vcm_vads_codes                 = <?php echo json_encode($vads_codes); ?>;

	/* Loading Overlay */
	function vcmShowLoading() {
		jQuery(".vcm-loading-overlay").show();
	}

	function vcmStopLoading() {
		jQuery(".vcm-loading-overlay").hide();
	}

	function vcmLoadPropertyDetails() {
		vcmShowLoading();
		return true;
	}

	function vcmDoSaving(task) {
		// display loading overlay
		vcmShowLoading();

		try {
			// prepare toast message container
			VBOToast.create(VBOToast.POSITION_TOP_CENTER);
			VBOToast.changePosition(VBOToast.POSITION_TOP_CENTER);
		} catch(err) {
			// do nothing
		}

		// get form values
		var qstring = jQuery('#adminForm').serialize();

		// make sure the task is not set again, or the good one will go lost
		qstring = qstring.replace('&task=', '&');

		// make sure the form is not totally empty
		qstring = qstring == 'task=' ? '' : qstring;

		// make the ajax request to the requested controller
		var ajax_base = "<?php echo VikChannelManager::ajaxUrl('index.php?option=com_vikchannelmanager&task=&aj=1&e4j_debug=' . VikRequest::getInt('e4j_debug', 0, 'request')); ?>";
		VBOCore.doAjax(
			ajax_base.replace('task=', 'task=' + task),
			qstring,
			(res) => {
				vcmStopLoading();
				// success
				var display_mess = Joomla.JText._('MSG_BASE_SUCCESS') + '!';
				if (res.hasOwnProperty('warning') && res['warning']) {
					display_mess += ' (' + res['warning'] + ')';
					alert(display_mess);
				} else {
					try {
						// dispatch toast success message
						VBOToast.enqueue(new VBOToastMessage({
							body:   display_mess,
							icon:   'fas fa-check-circle',
							status: VBOToast.SUCCESS_STATUS,
							action: () => {
								VBOToast.dispose(true);
							},
						}));
					} catch(err) {
						// fallback on regular alert
						alert(display_mess);
					}
				}
				// reload URL (no creation)
				var reload_url = jQuery('.vcm-hidden-refresh-url').attr('href');
				if (task == 'bproperty.saveclose') {
					// save & close
					document.location.href = jQuery('.vcm-hidden-dash-url').attr('href');
				} else {
					// reload the page
					document.location.href = reload_url;
				}
			},
			(err) => {
				// display error message
				alert(err.responseText);
				vcmStopLoading();
			}
		);

		return true;
	}

	Joomla.submitbutton = function(task) {
		if (task == 'bproperty.save' || task == 'bproperty.saveclose') {
			// submit form to controller
			vcmDoSaving(task);

			// exit
			return false;
		}

		// other buttons can submit the form normally
		Joomla.submitform(task, document.adminForm);
	}

	jQuery(function() {

		// disable input fields when the page loads
		jQuery('#adminForm')
			.find('input:not([type="hidden"]):not([type="checkbox"]):not(.vcm-listing-editable), input.vcm-hidden-disabled[type="hidden"], select:not(.vcm-listing-editable), textarea:not(.vcm-listing-editable)')
			.prop('disabled', true)
			.closest('.vcm-param-container')
			.addClass('vcm-param-container-tmp-disabled');

		// separate the click event listener on this class only, to allow to use it statically via HTML to keep the trigger
		jQuery('.vcm-param-container-tmp-disabled').on('click', function() {
			if (!jQuery(this).hasClass('vcm-param-container-tmp-disabled') || jQuery(this).hasClass('vcm-listing-noedit')) {
				return;
			}
			// enable the clicked field and trigger the change event
			jQuery(this).removeClass('vcm-param-container-tmp-disabled').find('input, select, textarea').prop('disabled', false).trigger('change');

			// check if other fields of the same group should be enabled on cascade
			var related_group = jQuery(this).attr('data-related-group');
			if (related_group) {
				// enable related fields WITHOUT triggering the change event to avoid loops
				jQuery('[data-related-group="' + related_group + '"]').removeClass('vcm-param-container-tmp-disabled').find('input, select, textarea').prop('disabled', false);
			}
		});

		// render select2
		jQuery('.vcm-multi-select').select2();

		// toolbar buttons
		jQuery('.vcm-listing-toolbar-btn').on('click', function() {
			var jumpto = jQuery(this).attr('data-jumpto');
			if (!jumpto || !jumpto.length) {
				return;
			}
			var landto = jQuery('[data-landto="' + jumpto + '"]');
			if (!landto || !landto.length) {
				return;
			}
			// animate scroll to the outer position
			jQuery('html,body').animate({scrollTop: landto.offset().top - 20}, {duration: 400});
		});

		// scrolltop button
		jQuery('#vcm-scrolltop-trigger').click(function() {
			jQuery('html,body').animate({scrollTop: 0}, {duration: 400});
		});

		// scrolltop button position listener
		window.addEventListener('scroll', VBOCore.debounceEvent(() => {
			if (jQuery(window).scrollTop() > 700) {
				jQuery('.vcm-floating-scrolltop').fadeIn();
			} else {
				jQuery('.vcm-floating-scrolltop').hide();
			}
		}, 500));

		// toggle room-rate relation blocks
		jQuery('.vcm-bookingcom-roomrate-toggle').click(function() {
			var mode = jQuery(this).hasClass('vcm-bookingcom-roomrate-toggle-down') ? 'show' : 'hide';
			var room_rate = jQuery(this).closest('.vcm-bookingcom-roomrate');

			// hide clicked toggle button
			jQuery(this).hide();

			// process show/hide action
			if (mode === 'show') {
				room_rate.find('.vcm-bookingcom-roomrate-rplan, .vcm-bookingcom-roomrate-addnew').show();
				room_rate.find('.vcm-bookingcom-roomrate-toggle-up').show();
			} else {
				room_rate.find('.vcm-bookingcom-roomrate-rplan, .vcm-bookingcom-roomrate-addnew').hide();
				room_rate.find('.vcm-bookingcom-roomrate-toggle-down').show();
			}
		});

		// room-rate relation blocks trigger
		jQuery('.vcm-bookingcom-roomrate-room').click(function() {
			var room_rate = jQuery(this).closest('.vcm-bookingcom-roomrate');
			if (!room_rate.find('.vcm-bookingcom-roomrate-rplan').first().is(':visible')) {
				room_rate.find('.vcm-bookingcom-roomrate-toggle-down').trigger('click');
			} else {
				room_rate.find('.vcm-bookingcom-roomrate-toggle-up').trigger('click');
			}
		});

		// hotel amenity codes (services) change "exists"
		jQuery(document.body).on('click', 'select.vcm-bookingcom-amenity-exists', function() {
			if (jQuery(this).val() == '2') {
				jQuery(this).closest('.vcm-bookingcom-hotel-amenity').find('.vcm-bookingcom-amenity-included').hide();
			} else {
				jQuery(this).closest('.vcm-bookingcom-hotel-amenity').find('.vcm-bookingcom-amenity-included').show();
			}
		});

		// bundled value-adds select service code
		jQuery('select.vcm-bookingcom-roomrate-valueadds').on('change', function() {
			let service_code = jQuery(this).val();
			if (!service_code) {
				// do nothing in case of empty selection
				return;
			}

			// make sure we do not have too many service codes added
			if (jQuery('.vcm-bookingcom-roomrate-valueadd-wrap').length >= 5) {
				// display alert message
				alert('You can choose up to 5 value-added services or products per room-rate');

				// reset drop down
				setTimeout(() => {
					jQuery(this).val('').trigger('change');
				}, 200);

				// abort
				return;
			}

			// scan all service codes to find the selected one
			vcm_vads_codes.forEach((vads_group) => {
				let service_found = false;
				for (const [vads_code, vads_data] of Object.entries(vads_group.list)) {
					if (vads_code == service_code) {
						// service found, append it to the form
						service_found = true;
						vcmAddValueAddService(vads_code, vads_data, vads_group?.macro, vads_group?.group);
						break;
					}
				}
				if (service_found) {
					// disable all services under this group, because only one service per group is allowed
					for (const [vads_code, vads_data] of Object.entries(vads_group.list)) {
						jQuery(this).find('option[value="' + vads_code + '"]').prop('disabled', true);
					}
				}
			});

			// reset drop down
			setTimeout(() => {
				jQuery(this).val('').trigger('change');
			}, 200);
		});

		// hide inactive rate plans
		jQuery('.vcm-hide-inactive-rplans').on('dblclick', () => {
			document.location.href = jQuery('.vcm-hidden-refresh-url').attr('href') + '&hide_inactive_rplans=1';
		});
	});

	/**
	 * Displays the selected value-add service and related information.
	 */
	function vcmAddValueAddService(vads_code, vads_data, vads_macro, vads_group, vads_attributes) {
		let vads_entry_html = '<div class="vcm-param-container vcm-param-nested vcm-bookingcom-roomrate-valueadd-wrap" data-scode="' + vads_code + '">' + "\n";
		vads_entry_html += '	<div class="vcm-param-label">' + "\n";
		vads_entry_html += '		<div class="vcm-bookingcom-roomrate-valueadd-name">' + vads_data['name'] + '</div>' + "\n";
		vads_entry_html += '		<div class="vcm-bookingcom-roomrate-valueadd-actions">' + "\n";
		vads_entry_html += '			<button class="btn btn-danger btn-small" onclick="vcmRemoveValueAddService(this);"><?php VikBookingIcons::e('times-circle'); ?></button>' + "\n";
		vads_entry_html += '		</div>' + "\n";
		vads_entry_html += '	</div>' + "\n";
		vads_entry_html += '	<div class="vcm-param-setting">' + "\n";
		vads_entry_html += '		<input type="hidden" class="value_adds_service_codes" value="' + vads_code + '" />' + "\n";

		// handle service attributes
		let has_currency = false;
		vads_data['attributes'].forEach((attr) => {
			if (attr['type'] == 'currency') {
				has_currency = true;
				return;
			}

			// check if this attribute has a value set
			let attr_value_set = '';
			if (vads_attributes && vads_attributes.hasOwnProperty(attr['name'])) {
				attr_value_set = vads_attributes[attr['name']];
			}

			vads_entry_html += '<div class="vcm-bookingcom-valueadd-attribute">' + "\n";
			vads_entry_html += '	<span class="vcm-bookingcom-valueadd-attribute-name">' + attr['name'] + '</span>' + "\n";
			if (has_currency) {
				vads_entry_html += '<span class="vcm-bookingcom-valueadd-attribute-currency"><?php echo VikBooking::getCurrencySymb(); ?></span> ' + "\n";
			}
			if (attr['type'] == 'number') {
				// get rid of empty decimals
				if (attr_value_set && !isNaN(attr_value_set) && (attr_value_set - parseInt(attr_value_set)) == 0) {
					attr_value_set = parseInt(attr_value_set);
				}
				// render field
				vads_entry_html += '<span class="vcm-bookingcom-valueadd-attribute-inp">' + "\n";
				vads_entry_html += '	<input type="number" class="value_adds_service_values" data-scode="' + vads_code + '" data-sname="' + attr['name'] + '" min="' + (attr['min'] || 0) + '" max="' + (attr['max'] || '') + '" value="' + attr_value_set + '" />' + "\n";
				vads_entry_html += '</span>' + "\n";
			} else {
				vads_entry_html += '<span class="vcm-bookingcom-valueadd-attribute-inp">' + "\n";
				vads_entry_html += '	<input type="text" class="value_adds_service_values" data-scode="' + vads_code + '" data-sname="' + attr['name'] + '" value="' + attr_value_set + '" />' + "\n";
				vads_entry_html += '</span>' + "\n";
			}

			let help_str = (attr['help'] || '');
			vads_entry_html += '<span class="vcm-param-setting-comment">' + vads_macro + (vads_macro != vads_group ? ' - ' + vads_group : '') + (help_str ? ' - ' + help_str : '') + '</span>' + "\n";

			vads_entry_html += '</div>' + "\n";
		});

		if (!vads_data['attributes'].length) {
			vads_entry_html += '<span class="vcm-param-setting-comment">' + vads_macro + (vads_macro != vads_group ? ' - ' + vads_group : '') + '</span>' + "\n";
		}

		vads_entry_html += '	</div>' + "\n";
		vads_entry_html += '</div>' + "\n";

		jQuery('.vcm-bookingcom-roomrate-valueadds-included').append(vads_entry_html).show();
	}

	/**
	 * Removes the room-rate value-add service element.
	 */
	function vcmRemoveValueAddService(elem) {
		// get service code to remove
		let service_code = jQuery(elem).closest('.vcm-bookingcom-roomrate-valueadd-wrap').attr('data-scode');
		let service_selt = jQuery('select.vcm-bookingcom-roomrate-valueadds');

		// scan all service codes to find the one to remove
		vcm_vads_codes.forEach((vads_group) => {
			let service_found = false;
			for (const [vads_code, vads_data] of Object.entries(vads_group.list)) {
				if (vads_code == service_code) {
					// service found
					service_found = true;
					break;
				}
			}
			if (service_found) {
				// enable all services under this group before removing it
				for (const [vads_code, vads_data] of Object.entries(vads_group.list)) {
					service_selt.find('option[value="' + vads_code + '"]').prop('disabled', false);
				}

				// register trigger change event
				setTimeout(() => {
					service_selt.val('').trigger('change');
				}, 200);
			}
		});

		// remove the element nodes
		jQuery(elem).closest('.vcm-bookingcom-roomrate-valueadd-wrap').remove();
	}

	/**
	 * Fires when the button to add a new hotel amenity is clicked.
	 */
	function vcmAddHotelAmenity() {
		var amenity_code = jQuery('#vcm-hotel-amenities-dropdown-list').val();
		if (!amenity_code) {
			alert(Joomla.JText._('VCM_PLEASE_SELECT'));
			return false;
		}
		// disable this newly added amenity from the select
		jQuery('#vcm-hotel-amenities-dropdown-list').find('option[value="' + amenity_code + '"]').prop('disabled', true);
		// unset the value from the select and trigger the change event
		jQuery('#vcm-hotel-amenities-dropdown-list').val('').trigger('change');
		// populate the amenity
		if (!vcm_bookingcom_hotel_amenities.hasOwnProperty(amenity_code)) {
			alert('Invalid amenity');
			return false;
		}
		var amenities_wrapper = jQuery('.vcm-bookingcom-hotel-amenities');
		var amenity_html = '';
		amenity_html += '<div class="vcm-params-block vcm-bookingcom-hotel-amenity">' + "\n";

		amenity_html += '<div class="vcm-param-container" data-related-group="hotelamenities">' + "\n";
		amenity_html += '	<div class="vcm-param-label">' + "\n";
		amenity_html += '		<strong>' + vcm_bookingcom_hotel_amenities[amenity_code] + '</strong>' + "\n";
		amenity_html += '	</div>' + "\n";
		amenity_html += '	<div class="vcm-param-setting">' + "\n";
		amenity_html += '		<input type="hidden" name="" data-buildname="services[%d][code]" value="' + amenity_code + '" class="vcm-bookingcom-amenity-code" />' + "\n";
		amenity_html += '		<button type="button" class="btn btn-danger" onclick="vcmRemoveHotelAmenity(this);"><?php VikBookingIcons::e('times-circle'); ?></button>' + "\n";
		amenity_html += '	</div>' + "\n";
		amenity_html += '</div>' + "\n";

		amenity_html += '<div class="vcm-param-container vcm-param-nested" data-related-group="hotelamenities">' + "\n";
		amenity_html += '	<div class="vcm-param-label">' + Joomla.JText._('VCMOVERSIGHTLEGENDGREEN') + '</div>' + "\n";
		amenity_html += '	<div class="vcm-param-setting">' + "\n";
		amenity_html += '		<select name="" data-buildname="services[%d][existscode]" class="vcm-listing-editable vcm-bookingcom-amenity-exists">' + "\n";
		amenity_html += '			<option value="1">' + Joomla.JText._('VCMYES') + '</option>' + "\n";
		amenity_html += '			<option value="2">' + Joomla.JText._('VCMNO') + '</option>' + "\n";
		amenity_html += '		</select>' + "\n";
		amenity_html += '	</div>' + "\n";
		amenity_html += '</div>' + "\n";

		amenity_html += '<div class="vcm-param-container vcm-param-nested vcm-bookingcom-amenity-included" data-related-group="hotelamenities">' + "\n";
		amenity_html += '	<div class="vcm-param-label">Included</div>' + "\n";
		amenity_html += '	<div class="vcm-param-setting">' + "\n";
		amenity_html += '		<select name="" data-buildname="services[%d][included]" class="vcm-listing-editable">' + "\n";
		amenity_html += '			<option value="true">' + Joomla.JText._('VCMYES') + '</option>' + "\n";
		amenity_html += '			<option value="false">' + Joomla.JText._('VCMNO') + '</option>' + "\n";
		amenity_html += '			<option value="unknown">' + Joomla.JText._('VCMBCAHJOB17') + '</option>' + "\n";
		amenity_html += '		</select>' + "\n";
		amenity_html += '		<span class="vcm-param-setting-comment">Specifies whether the service is included in the room price or comes at an extra charge.</span>' + "\n";
		amenity_html += '	</div>' + "\n";
		amenity_html += '</div>' + "\n";

		amenity_html += '</div>' + "\n";

		// append amenity elements
		amenities_wrapper.append(amenity_html);

		// update total count
		jQuery('.vcm-bookingcom-totservices').text(jQuery('.vcm-bookingcom-hotel-amenities').find('.vcm-bookingcom-hotel-amenity').length);

		// make sure the elements are not collapsed, hence hidden
		if (!jQuery('.vcm-bookingcom-hotel-amenities').is(':visible')) {
			jQuery('input[name="vcm_toggle_services"]').trigger('click');
		}

		// animate scroll to that position
		jQuery('html,body').animate({scrollTop: jQuery('.vcm-bookingcom-hotel-amenity').last().offset().top - 40}, {duration: 400});

		// update the name attributes of every element
		vcmResetAmenitiesNaming();
	}

	/**
	 * Removes a hotel amenity block.
	 */
	function vcmRemoveHotelAmenity(elem) {
		if (!confirm(Joomla.JText._('VCM_ASK_CONTINUE'))) {
			return false;
		}
		if (jQuery('.vcm-bookingcom-hotel-amenities').find('.vcm-bookingcom-hotel-amenity').length === 1) {
			// cannot remove all elements or the update request will not be performed
			alert('Cannot remove all elements from this section or no data will be actually updated.');
			return false;
		}
		var amenity_block = jQuery(elem).closest('.vcm-bookingcom-hotel-amenity');
		var amenity_code = amenity_block.find('input.vcm-bookingcom-amenity-code').val();
		// enable in the select the amenity just removed
		jQuery('#vcm-hotel-amenities-dropdown-list').find('option[value="' + amenity_code + '"]').prop('disabled', false);
		// unset the value from the select and trigger the change event
		jQuery('#vcm-hotel-amenities-dropdown-list').val('').trigger('change');
		// remove block element
		amenity_block.remove();
		// update the name attributes of every element
		vcmResetAmenitiesNaming();
		// update total count
		jQuery('.vcm-bookingcom-totservices').text(jQuery('.vcm-bookingcom-hotel-amenities').find('.vcm-bookingcom-hotel-amenity').length);
	}

	/**
	 * Every time a modification is made, the name attribute of any input element must be renamed.
	 */
	function vcmResetAmenitiesNaming() {
		jQuery('.vcm-bookingcom-hotel-amenities').find('[data-buildname]').each(function(k, v) {
			var elem = jQuery(this);
			var buildname = elem.attr('data-buildname');
			var replacements = (buildname.match(/%d/g) || []).length;
			if (!replacements) {
				return;
			}
			// set proper amenity index (first wildcard %d)
			var amenity_elem = elem.closest('.vcm-bookingcom-hotel-amenity');
			var amenity_index = jQuery('.vcm-bookingcom-hotel-amenities').find('.vcm-bookingcom-hotel-amenity').index(amenity_elem);
			buildname = buildname.replace('%d', amenity_index);
			// set correct name attribute
			elem.attr('name', buildname);
		});
		// make sure to enable all input fields
		vcmEnableHotelAmenities();
	}

	/**
	 * Enables all fields of the hotel amenities after a single change.
	 */
	function vcmEnableHotelAmenities() {
		jQuery('.vcm-bookingcom-hotel-amenities').find('.vcm-param-container-tmp-disabled').removeClass('vcm-param-container-tmp-disabled').find('input, select').prop('disabled', false);
	}

	/**
	 * Fires when the collapse/expand button is clicked to toggle the hotel services.
	 */
	function vcmToggleHotelServices(expand) {
		if (expand) {
			jQuery('.vcm-bookingcom-hotel-amenities').show();
		} else {
			jQuery('.vcm-bookingcom-hotel-amenities').hide();
		}
	}

	/**
	 * Removes a contact info block.
	 */
	function vcmRemoveContactInfo(elem) {
		if (!confirm(Joomla.JText._('VCM_ASK_CONTINUE'))) {
			return false;
		}
		if (jQuery('.vcm-bookingcom-hotel-contacts').find('.vcm-bookingcom-hotel-contactinfo').length === 1) {
			// cannot remove all elements or the update request will not be performed
			alert('Cannot remove all elements from this section or no data will be actually updated.');
			return false;
		}
		var contact_block = jQuery(elem).closest('.vcm-bookingcom-hotel-contactinfo');
		// remove block element
		contact_block.remove();
		// make sure to enable all input fields
		vcmEnableHotelContactInfos();
	}

	/**
	 * Enables all fields of the hotel contact infos after a single change.
	 */
	function vcmEnableHotelContactInfos() {
		jQuery('.vcm-bookingcom-hotel-contacts').find('.vcm-param-container-tmp-disabled').removeClass('vcm-param-container-tmp-disabled').find('input, select').prop('disabled', false);
	}

	/**
	 * Populates the data of the contact info helper form to add or edit profiles.
	 */
	function vcmContactInfoPopulateForm(data) {
		var form_def_data = {
			type: '',
			name: {
				language: '',
				gender: '',
				givenname: '',
				jobtitle: '',
			},
			email: '',
			phones: [],
			address: {
				street: '',
				city: '',
				postcode: '',
				country: '',
				stateprov: '',
				tn: {},
				hide: 0,
			}
		};

		// merge data with argument
		form_def_data = Object.assign(form_def_data, data);

		// get the form element
		var helper_form = document.getElementById('vcm-bookingcom-contactinfo-form');

		// reset the HTML helper form
		helper_form.reset();

		// check if some hidden input fields require a manual reset, because form.reset() won't work on hidden fields
		var hidden_fields = helper_form.querySelectorAll('.vcm-reset-hidden-field');
		if (hidden_fields.length) {
			hidden_fields.forEach((hidden_input) => {
				if (hidden_input.hasAttribute('data-origvalue')) {
					// reset value to its original state in the hidden input field
					hidden_input.value = hidden_input.getAttribute('data-origvalue');
				}
			});
		}

		// populate values
		helper_form.elements['type'].value  = form_def_data['type'];
		helper_form.elements['email'].value = form_def_data['email'];
		for (var naming in form_def_data['name']) {
			if (!form_def_data['name'].hasOwnProperty(naming)) {
				continue;
			}
			helper_form.elements['name[' + naming + ']'].value = form_def_data['name'][naming];
		}
		for (var addr in form_def_data['address']) {
			if (!form_def_data['address'].hasOwnProperty(addr)) {
				continue;
			}
			if (addr == 'tn') {
				// special key with non-editable information
				if (Object.keys(form_def_data['address'][addr]).length) {
					// we add a JSON representation of the non-empty object only
					helper_form.elements['address[' + addr + ']'].value = JSON.stringify(form_def_data['address'][addr]);
				}
			} else {
				// regular form field
				helper_form.elements['address[' + addr + ']'].value = form_def_data['address'][addr];
			}
		}

		// newly created contact profiles may build an object with numeric keys for "phones"
		if (typeof form_def_data['phones'] === 'object') {
			// convert the object into an array
			form_def_data['phones'] = Object.values(form_def_data['phones']);
		}

		// we only support 3 phone numbers, but old contact profiles may have even 4
		form_def_data['phones'].slice(0, 3).forEach((phone, index) => {
			helper_form.elements['phones[' + index + '][number]'].value = phone['number'];
			helper_form.elements['phones[' + index + '][type]'].value 	= phone['type'];
		});
	}

	/**
	 * Renders the HTML for a new or an existing contact profile type (info) according to the input data.
	 * Fires when the modal window to create or edit a contact profile is clicked to apply the changes.
	 */
	function vcmRenderContactInfo(replace_block) {
		// gather the whole form
		var contact_info_form = document.getElementById('vcm-bookingcom-contactinfo-form');

		// collect fields
		var type = contact_info_form.type.value;
		if (!type) {
			alert(Joomla.JText._('VCM_PLEASE_SELECT'));
			return false;
		}

		// clone HTML helper's children for a new contact info to be added or replaced
		var base_info = jQuery('.vcm-bookingcom-newcontactinfo-helper').children().clone();

		// populate data for the new block to add by starting to read the form data entries
		var contact_info_form_data = new FormData(contact_info_form);

		/**
		 * We need to loop over each FormData entry in order to represent the desired form object
		 * with nested properties, such as "name[givenname]" in order to avoid one-level
		 * properties like {"name[givenname]": "foo"}.
		 */

		// this will not build an object with nested object properties
		// var form_object = Object.fromEntries(contact_info_form_data);

		const inp_rgx 	   = new RegExp(/[^A-Za-z0-9\[\]]+/g);
		const alphanum_rgx = new RegExp(/[^A-Za-z0-9]+/g);
		var form_object    = {};

		contact_info_form_data.forEach((value, name) => {
			// get rid of quotes, if any
			name = name.replace(inp_rgx, '');
			// check if it's an array-value
			if (name.indexOf('[') >= 0) {
				// multi-level value
				var parts = name.split('[');
				var tot_props = parts.length;
				var obj_copy;
				parts.forEach((prop, index) => {
					prop = prop.replace(alphanum_rgx, '');
					if (!index) {
						// array main name (first-level)
						if (!form_object.hasOwnProperty(prop)) {
							// initiate object
							form_object[prop] = {};
						}
						// assign new object
						obj_copy = form_object[prop];
						// go to next loop
						return;
					}
					if (index == (tot_props - 1)) {
						// set final value

						if (typeof value === 'string' && value.length) {
							// handle JSON encoded values (for properties like "tn")
							var should_decode = false;
							if (value.charAt(0) == '{' && value.charAt(value.length - 1) == '}') {
								should_decode = true;
							} else if (value.charAt(0) == '[' && value.charAt(value.length - 1) == ']') {
								should_decode = true;
							}
							if (should_decode) {
								try {
									var decoded = JSON.parse(value);
									value = decoded;
								} catch(e) {
									// do nothing
								}
							}
						}

						// set proper final value
						obj_copy[prop] = value;
					} else {
						// start new level
						if (!obj_copy.hasOwnProperty(prop)) {
							obj_copy[prop] = {};
						}
						// assign new object
						obj_copy = obj_copy[prop];
					}
				});
			} else {
				// one-level value
				form_object[name] = value;
			}
		});

		// append contact profile info payload for saving or later editing
		base_info.find('.vcm-bookingcom-contactinfo-json').val(JSON.stringify(form_object));

		// render the block for the new contact info with the data provided
		base_info.find('.vcm-bookingcom-type').text(type.charAt(0).toUpperCase() + type.slice(1));
		base_info.find('.vcm-bookingcom-name').text(contact_info_form.elements['name[givenname]'].value);
		base_info.find('.vcm-bookingcom-job').text(contact_info_form.elements['name[jobtitle]'].value);
		base_info.find('.vcm-bookingcom-mail').text(contact_info_form.elements['email'].value);

		if (!contact_info_form.elements['name[givenname]'].value && !contact_info_form.elements['name[jobtitle]'].value) {
			base_info.find('[data-contactinfo="namejob"]').hide();
		}

		// phone numbers
		var phone_numbers = [];
		for (var i = 0; i < 3; i++) {
			if (contact_info_form.elements['phones[' + i + '][number]'].value) {
				phone_numbers.push(contact_info_form.elements['phones[' + i + '][number]'].value);
			}
		}
		if (phone_numbers.length) {
			base_info.find('.vcm-bookingcom-phone').text(phone_numbers.join(', '));
		} else if (!contact_info_form.elements['email'].value) {
			base_info.find('[data-contactinfo="mailphone"]').hide();
		}

		// address information
		var address_info = [];
		if (contact_info_form.elements['address[street]'].value) {
			address_info.push(contact_info_form.elements['address[street]'].value);
		}
		if (contact_info_form.elements['address[city]'].value) {
			address_info.push(contact_info_form.elements['address[city]'].value);
		}
		if (contact_info_form.elements['address[postcode]'].value) {
			address_info.push(contact_info_form.elements['address[postcode]'].value);
		}
		if (contact_info_form.elements['address[country]'].value) {
			address_info.push(contact_info_form.elements['address[country]'].value);
		}
		if (contact_info_form.elements['address[stateprov]'].value) {
			address_info.push(contact_info_form.elements['address[stateprov]'].value);
		}
		if (address_info.length) {
			base_info.find('.vcm-bookingcom-address').text(address_info.join(', '));
		} else {
			base_info.find('[data-contactinfo="address"]').hide();
		}

		// finalize the rendering operation
		if (typeof replace_block === 'undefined' || replace_block == null) {
			// append block to document as a new contact info
			base_info.appendTo(jQuery('.vcm-bookingcom-hotel-contacts'));
			// animate scroll to that position
			jQuery('html,body').animate({scrollTop: base_info.offset().top - 40}, {duration: 400});
		} else {
			// replace existing block from document as a modified contact info
			replace_block.replaceWith(base_info);
			// animate scroll to that position
			jQuery('html,body').animate({scrollTop: base_info.offset().top - 40}, {duration: 400});
		}

		// make sure to enable all input fields
		vcmEnableHotelContactInfos();
	}

	/**
	 * Opens the modal to create a new contact profile type (info).
	 */
	function vcmNewContactInfo() {
		var profile_type = jQuery('#vcm-hotel-contacttypes-dropdown-list').val();
		if (!profile_type) {
			alert(Joomla.JText._('VCM_PLEASE_SELECT'));
			return false;
		}

		// unset the value from the select and trigger the change event
		jQuery('#vcm-hotel-contacttypes-dropdown-list').val('').trigger('change');

		// build the button to handle the adding of the values
		var btn_apply_contactinfo_addnew = jQuery('<button></button>').attr('type', 'button').addClass('btn btn-success').html('<?php VikBookingIcons::e('check-circle'); ?> ' + Joomla.JText._('VCMRARADDLOSAPPLY'));
		btn_apply_contactinfo_addnew.on('click', function() {
			// render the new contact profile type
			vcmRenderContactInfo();
			// dismiss the modal
			VBOCore.emitEvent('close-contactinfo-addnew');
		});

		// render modal
		var modal_wrapper = VBOCore.displayModal({
			extra_class: 'vbo-modal-tall',
			suffix: 'contactinfo-addnew',
			title:  Joomla.JText._('VCMBCAHCINFO') + ' - ' + Joomla.JText._('NEW'),
			body_prepend: true,
			lock_scroll: true,
			footer_right: btn_apply_contactinfo_addnew,
			dismiss_event: 'close-contactinfo-addnew',
			onDismiss: () => {
				// move HTML helper back to its location
				jQuery('.vcm-bookingcom-contactinfo-helper').appendTo(jQuery('.vcm-bookingcom-contactinfo-html-helper'));
			},
		});

		// populate form data
		vcmContactInfoPopulateForm({type: profile_type});

		// append content to modal
		jQuery('.vcm-bookingcom-contactinfo-helper').appendTo(modal_wrapper);
	}

	/**
	 * Opens the modal to modify an existing contact profile type (info).
	 */
	function vcmEditContactInfo(btn) {
		var editing_block = jQuery(btn).closest('.vcm-bookingcom-hotel-contactinfo');
		try {
			var contact_payload = JSON.parse(editing_block.find('.vcm-bookingcom-contactinfo-json').val());
		} catch(e) {
			alert('Could not parse a valid payload for the selected contact information. Please delete it and re-create it.');
			return false;
		}

		// build the button to handle the adding of the values
		var btn_apply_contactinfo_edit = jQuery('<button></button>').attr('type', 'button').addClass('btn btn-success').html('<?php VikBookingIcons::e('check-circle'); ?> ' + Joomla.JText._('VCMRARADDLOSAPPLY'));
		btn_apply_contactinfo_edit.on('click', function() {
			// render the modified contact profile type
			vcmRenderContactInfo(editing_block);
			// dismiss the modal
			VBOCore.emitEvent('close-contactinfo-edit');
		});

		// render modal
		var modal_wrapper = VBOCore.displayModal({
			extra_class: 'vbo-modal-tall',
			suffix: 'contactinfo-edit',
			title: Joomla.JText._('VCMBCAHCINFO') + ' - ' + Joomla.JText._('EDIT'),
			body_prepend: true,
			lock_scroll: true,
			footer_right: btn_apply_contactinfo_edit,
			dismiss_event: 'close-contactinfo-edit',
			onDismiss: () => {
				// move HTML helper back to its location
				jQuery('.vcm-bookingcom-contactinfo-helper').appendTo(jQuery('.vcm-bookingcom-contactinfo-html-helper'));
			},
		});

		// populate form data
		vcmContactInfoPopulateForm(contact_payload);

		// append content to modal
		jQuery('.vcm-bookingcom-contactinfo-helper').appendTo(modal_wrapper);
	}

	/**
	 * Removes a tax policy block.
	 */
	function vcmRemoveTaxPolicy(elem) {
		if (!confirm(Joomla.JText._('VCM_ASK_CONTINUE'))) {
			return false;
		}
		if (jQuery('.vcm-bookingcom-taxpolicies').find('.vcm-bookingcom-taxpolicy').length === 1) {
			// cannot remove all elements or the update request will not be performed
			alert('Cannot remove all elements from this section or no data will be actually updated.');
			return false;
		}
		var tax_block = jQuery(elem).closest('.vcm-bookingcom-taxpolicy');
		var tax_code = tax_block.attr('data-tax-code');
		// enable in the select the amenity just removed
		jQuery('#vcm-hotel-taxpolicies-dropdown-list').find('option[value="' + tax_code + '"]').prop('disabled', false);
		// unset the value from the select and trigger the change event
		jQuery('#vcm-hotel-taxpolicies-dropdown-list').val('').trigger('change');
		// remove block element
		tax_block.remove();
		// make sure to enable all input fields
		vcmEnableTaxPolicies();
	}

	/**
	 * Enables all fields of the tax policies after a single change.
	 */
	function vcmEnableTaxPolicies() {
		jQuery('.vcm-bookingcom-taxpolicies').find('.vcm-param-container-tmp-disabled').removeClass('vcm-param-container-tmp-disabled').find('input, select').prop('disabled', false);
	}

	/**
	 * Renders the HTML for a new tax policy according to the input data.
	 * Fires when the modal window to create a tax policy is clicked to apply the changes.
	 */
	function vcmRenderTaxpolicy() {
		// gather the whole form
		var taxpolicy_form = document.getElementById('vcm-bookingcom-taxpolicy-form');

		// collect fields
		var code = taxpolicy_form.code.value;
		if (!code) {
			alert(Joomla.JText._('VCM_PLEASE_SELECT'));
			return false;
		}

		// clone HTML helper's children for a new tax policy to be added or replaced
		var base_info = jQuery('.vcm-bookingcom-newtaxpolicy-helper').children().clone();

		// represent the form object
		var form_object = {
			code: code,
			decimalplaces: parseInt(taxpolicy_form.elements['decimalplaces'].value),
			type: taxpolicy_form.elements['type'].value,
		};
		var use_amount 	 = parseFloat((taxpolicy_form.elements['amount'].value + '').replace(',', '.'));
		var plain_amount = taxpolicy_form.elements['amount'].value;
		if (isNaN(use_amount)) {
			alert('Please enter a valid number');
			return false;
		}
		if (form_object['decimalplaces'] > 0) {
			use_amount = parseInt(use_amount * Math.pow(10, form_object['decimalplaces']));
		}
		if (taxpolicy_form.elements['percent'].value == 'percent') {
			form_object['percent'] = use_amount;
		} else {
			form_object['amount'] = use_amount;
		}
		if (taxpolicy_form.elements['chargefrequency'].value) {
			form_object['chargefrequency'] = taxpolicy_form.elements['chargefrequency'].value;
		}

		// append tax policy payload for saving
		base_info.find('.vcm-bookingcom-taxpolicy-json').val(JSON.stringify(form_object));

		// set data attribute with tax code
		base_info.attr('data-tax-code', code);

		// render the block for the new tax policy with the data provided
		var tax_amount = form_object.hasOwnProperty('amount') ? '<?php echo $hotel_currency; ?> ' : '';
		tax_amount += form_object.hasOwnProperty('amount') ? plain_amount : plain_amount + '%';

		var type_freq = form_object['type'];
		if (form_object.hasOwnProperty('chargefrequency') && charge_type_codes.hasOwnProperty(form_object['chargefrequency'])) {
			type_freq += ', ' + charge_type_codes[form_object['chargefrequency']];
		}

		base_info.find('.vcm-bookingcom-newtaxpolicy-code').text(fee_tax_type_codes.hasOwnProperty(code) ? fee_tax_type_codes[code] : code);
		base_info.find('.vcm-bookingcom-newtaxpolicy-percent').text(tax_amount);
		base_info.find('.vcm-bookingcom-newtaxpolicy-type').text(type_freq);

		// disable this newly added policy from the select
		jQuery('#vcm-hotel-taxpolicies-dropdown-list').find('option[value="' + code + '"]').prop('disabled', true);
		// unset the value from the select and trigger the change event
		jQuery('#vcm-hotel-taxpolicies-dropdown-list').val('').trigger('change');

		// append block to document as a new tax policy
		base_info.appendTo(jQuery('.vcm-bookingcom-taxpolicies'));
		// animate scroll to that position
		jQuery('html,body').animate({scrollTop: base_info.offset().top - 40}, {duration: 400});

		// make sure to enable all input fields
		vcmEnableTaxPolicies();
	}

	/**
	 * Opens the modal to create a new tax policy.
	 */
	function vcmNewTaxPolicy() {
		var tax_code = jQuery('#vcm-hotel-taxpolicies-dropdown-list').val();
		if (!tax_code) {
			alert(Joomla.JText._('VCM_PLEASE_SELECT'));
			return false;
		}

		// unset the value from the select and trigger the change event
		jQuery('#vcm-hotel-taxpolicies-dropdown-list').val('').trigger('change');

		// build the button to handle the adding of the values
		var btn_apply_taxpolicy_addnew = jQuery('<button></button>').attr('type', 'button').addClass('btn btn-success').html('<?php VikBookingIcons::e('check-circle'); ?> ' + Joomla.JText._('VCMRARADDLOSAPPLY'));
		btn_apply_taxpolicy_addnew.on('click', function() {
			// render the new tax policy
			if (vcmRenderTaxpolicy() !== false) {
				// dismiss the modal
				VBOCore.emitEvent('close-taxpolicy-addnew');
			}
		});

		// render modal
		var modal_wrapper = VBOCore.displayModal({
			extra_class: 'vbo-modal-tall',
			suffix: 'taxpolicy-addnew',
			title: Joomla.JText._('VCMBCAHTAXTYPE') + ' - ' + Joomla.JText._('NEW'),
			body_prepend: true,
			lock_scroll: true,
			footer_right: btn_apply_taxpolicy_addnew,
			dismiss_event: 'close-taxpolicy-addnew',
			onDismiss: () => {
				// move HTML helper back to its location
				jQuery('.vcm-bookingcom-taxpolicy-helper').appendTo(jQuery('.vcm-bookingcom-taxpolicy-html-helper'));
			},
		});

		// populate form data
		var helper_form = document.getElementById('vcm-bookingcom-taxpolicy-form');

		// reset the HTML helper form
		helper_form.reset();
		jQuery('.vcm-chargefreq-comment').text('');

		// populate values
		helper_form.elements['code'].value = tax_code;

		// append content to modal
		jQuery('.vcm-bookingcom-taxpolicy-helper').appendTo(modal_wrapper);
	}

	/**
	 * Fires when the charge frequency changes during the creation of a tax policy.
	 */
	function vcmCommentChargeFrequency(charge_freq_code) {
		var comment = '';
		if (charge_frequency_comments.hasOwnProperty(charge_freq_code)) {
			comment = charge_frequency_comments[charge_freq_code];
		}
		jQuery('.vcm-chargefreq-comment').text(comment);
	}

	/**
	 * Fires when the drop down menu to accept long stays changes.
	 */
	function vcmHandleLongStays(val) {
		if (val > 0) {
			jQuery('.vcm-longstayinfo-maxlos').show();
		} else {
			jQuery('.vcm-longstayinfo-maxlos').hide();
		}
	}

	/**
	 * Fires when the policy info accepted guest type changes.
	 */
	function vcmHandleAccGuestType(type) {
		if (type == 'ChildrenAllowed') {
			jQuery('.vcm-bookingcom-minguestage').show();
		} else {
			jQuery('.vcm-bookingcom-minguestage').hide();
		}
	}

	/**
	 * Fires when the pet policy "pets allowed" changes.
	 */
	function vcmHandlePetsAllowedCode(code) {
		if (code == 'Pets Not Allowed') {
			jQuery('.vcm-bookingcom-petfeenonref').hide();
		} else {
			jQuery('.vcm-bookingcom-petfeenonref').show();
		}
	}

	/**
	 * Removes a fee policy block.
	 */
	function vcmRemoveFeePolicy(elem) {
		if (!confirm(Joomla.JText._('VCM_ASK_CONTINUE'))) {
			return false;
		}
		if (jQuery('.vcm-bookingcom-feepolicies').find('.vcm-bookingcom-feepolicy').length === 1) {
			// cannot remove all elements or the update request will not be performed
			alert('Cannot remove all elements from this section or no data will be actually updated.');
			return false;
		}
		var fee_block = jQuery(elem).closest('.vcm-bookingcom-feepolicy');
		// remove block element
		fee_block.remove();
		// make sure to enable all input fields
		vcmEnableFeePolicies();
	}

	/**
	 * Enables all fields of the fee policies after a single change.
	 */
	function vcmEnableFeePolicies() {
		jQuery('.vcm-bookingcom-feepolicies').find('.vcm-param-container-tmp-disabled').removeClass('vcm-param-container-tmp-disabled').find('input, select').prop('disabled', false);
	}

	/**
	 * Renders the HTML for a new fee policy according to the input data.
	 * Fires when the modal window to create a fee policy is clicked to apply the changes.
	 */
	function vcmRenderFeepolicy() {
		// gather the whole form
		var feepolicy_form = document.getElementById('vcm-bookingcom-feepolicy-form');

		// collect fields
		var code = feepolicy_form.code.value;
		if (!code) {
			alert(Joomla.JText._('VCM_PLEASE_SELECT'));
			return false;
		}

		// clone HTML helper's children for a new fee policy to be added or replaced
		var base_info = jQuery('.vcm-bookingcom-newfeepolicy-helper').children().clone();

		// represent the form object
		var form_object = {
			code: code,
			decimalplaces: parseInt(feepolicy_form.elements['decimalplaces'].value),
			type: feepolicy_form.elements['type'].value,
		};
		var use_amount 	 = parseFloat((feepolicy_form.elements['amount'].value + '').replace(',', '.'));
		var plain_amount = feepolicy_form.elements['amount'].value;
		if (isNaN(use_amount)) {
			alert('Please enter a valid number');
			return false;
		}
		if (form_object['decimalplaces'] > 0) {
			use_amount = parseInt(use_amount * Math.pow(10, form_object['decimalplaces']));
		}
		if (feepolicy_form.elements['percent'].value == 'percent') {
			form_object['percent'] = use_amount;
		} else {
			form_object['amount'] = use_amount;
		}
		if (feepolicy_form.elements['chargefrequency'].value) {
			form_object['chargefrequency'] = feepolicy_form.elements['chargefrequency'].value;
		}
		var fee_extra_info = [];
		if (child_fee_codes.hasOwnProperty(code)) {
			if (feepolicy_form.elements['minage'].value.length) {
				form_object['minage'] = feepolicy_form.elements['minage'].value;
				fee_extra_info.push(Joomla.JText._('VCM_MIN_AGE') + ': ' + form_object['minage']);
			}
			if (feepolicy_form.elements['maxage'].value.length) {
				form_object['maxage'] = feepolicy_form.elements['maxage'].value;
				fee_extra_info.push(Joomla.JText._('VCM_MAX_AGE') + ': ' + form_object['maxage']);
			}
		}

		// handle specific type of fees that require additional information
		if (code == '5035') {
			// handle internet fees extra values
			form_object['internetfeepolicy'] = {
				internettype: feepolicy_form.elements['internettype'].value,
				internetcoverage: feepolicy_form.elements['internetcoverage'].value,
			};
			fee_extra_info.push('Internet Type: ' + form_object['internetfeepolicy']['internettype']);
			fee_extra_info.push('Internet Coverage: ' + form_object['internetfeepolicy']['internetcoverage']);
		} else if (code == '5036') {
			// handle parking fees extra values
			form_object['parkingfeepolicy'] = {
				parkingtype: feepolicy_form.elements['parkingtype'].value,
				parkingreservation: feepolicy_form.elements['parkingreservation'].value,
				parkingproperty: feepolicy_form.elements['parkingproperty'].value,
			};
			fee_extra_info.push('Parking Type: ' + form_object['parkingfeepolicy']['parkingtype']);
			fee_extra_info.push('Parking Reservation: ' + form_object['parkingfeepolicy']['parkingreservation']);
			fee_extra_info.push('Parking Property: ' + form_object['parkingfeepolicy']['parkingproperty']);
		} else if (code == '5009' && feepolicy_form.elements['type'].value == 'Conditional') {
			// handle cleaning fees extra values when type = 'Conditional'
			form_object['condition'] = {
				type: feepolicy_form.elements['condition'].value,
			};
		}

		// append fee policy payload for saving
		base_info.find('.vcm-bookingcom-feepolicy-json').val(JSON.stringify(form_object));

		// render the block for the new fee policy with the data provided
		var fee_amount = form_object.hasOwnProperty('amount') ? '<?php echo $hotel_currency; ?> ' : '';
		fee_amount += form_object.hasOwnProperty('amount') ? plain_amount : plain_amount + '%';

		var type_freq = form_object['type'];
		if (form_object.hasOwnProperty('chargefrequency') && charge_type_codes.hasOwnProperty(form_object['chargefrequency'])) {
			type_freq += ', ' + charge_type_codes[form_object['chargefrequency']];
		}
		if (fee_extra_info.length) {
			type_freq += ', ' + fee_extra_info.join(', ');
		}

		base_info.find('.vcm-bookingcom-newfeepolicy-code').text((fee_tax_type_codes.hasOwnProperty(code) ? fee_tax_type_codes[code] : code) + (child_fee_codes.hasOwnProperty(code) ? ' (' + Joomla.JText._('VCMCHILDREN') + ')' : ''));
		base_info.find('.vcm-bookingcom-newfeepolicy-percent').text(fee_amount);
		base_info.find('.vcm-bookingcom-newfeepolicy-type').text(type_freq);

		// append block to document as a new fee policy
		base_info.appendTo(jQuery('.vcm-bookingcom-feepolicies'));
		// animate scroll to that position
		jQuery('html,body').animate({scrollTop: base_info.offset().top - 40}, {duration: 400});

		// make sure to enable all input fields
		vcmEnableFeePolicies();
	}

	/**
	 * Opens the modal to create a new fee policy.
	 */
	function vcmNewFeePolicy() {
		var fee_code = jQuery('#vcm-hotel-feepolicies-dropdown-list').val();
		if (!fee_code) {
			alert(Joomla.JText._('VCM_PLEASE_SELECT'));
			return false;
		}

		// unset the value from the select and trigger the change event
		jQuery('#vcm-hotel-feepolicies-dropdown-list').val('').trigger('change');

		// build the button to handle the adding of the values
		var btn_apply_feepolicy_addnew = jQuery('<button></button>').attr('type', 'button').addClass('btn btn-success').html('<?php VikBookingIcons::e('check-circle'); ?> ' + Joomla.JText._('VCMRARADDLOSAPPLY'));
		btn_apply_feepolicy_addnew.on('click', function() {
			// render the new fee policy
			if (vcmRenderFeepolicy() !== false) {
				// dismiss the modal
				VBOCore.emitEvent('close-feepolicy-addnew');
			}
		});

		// render modal
		var modal_wrapper = VBOCore.displayModal({
			extra_class: 'vbo-modal-tall',
			suffix: 'feepolicy-addnew',
			title: Joomla.JText._('VCMBCAHFEE') + ' - ' + Joomla.JText._('NEW'),
			body_prepend: true,
			lock_scroll: true,
			footer_right: btn_apply_feepolicy_addnew,
			dismiss_event: 'close-feepolicy-addnew',
			onDismiss: () => {
				// move HTML helper back to its location
				jQuery('.vcm-bookingcom-feepolicy-helper').appendTo(jQuery('.vcm-bookingcom-feepolicy-html-helper'));
			},
		});

		// populate form data
		var helper_form = document.getElementById('vcm-bookingcom-feepolicy-form');

		// reset the HTML helper form
		helper_form.reset();
		jQuery('.vcm-chargefreq-comment').text('');

		// populate values
		helper_form.elements['code'].value = fee_code;

		// hide or show children policy fields
		if (child_fee_codes.hasOwnProperty(fee_code)) {
			jQuery('.vcm-bookingcom-newfee-forchildren').show();
		} else {
			jQuery('.vcm-bookingcom-newfee-forchildren').hide();
		}

		// hide or show internet fees
		if (fee_code == '5035') {
			jQuery('.vcm-bookingcom-newfee-5035').show();
		} else {
			jQuery('.vcm-bookingcom-newfee-5035').hide();
		}

		// hide or show parking fees
		if (fee_code == '5036') {
			jQuery('.vcm-bookingcom-newfee-5036').show();
		} else {
			jQuery('.vcm-bookingcom-newfee-5036').hide();
		}

		// handle cleaning fees
		if (fee_code == '5009') {
			// add the additional fee type "Conditional"
			if (!jQuery('select.vcm-bookingcom-newfee-inclexcl').find('option[value="Conditional"]').length) {
				jQuery('select.vcm-bookingcom-newfee-inclexcl').append('<option value="Conditional">Conditional</option>');
			}
		} else {
			// delete the additional fee type "Conditional"
			if (jQuery('select.vcm-bookingcom-newfee-inclexcl').find('option[value="Conditional"]').length) {
				jQuery('select.vcm-bookingcom-newfee-inclexcl').find('option[value="Conditional"]').remove();
			}
			// always hide the field(s) for the cleaning fees
			jQuery('.vcm-bookingcom-newfee-5009').hide();
		}

		// append content to modal
		jQuery('.vcm-bookingcom-feepolicy-helper').appendTo(modal_wrapper);
	}

	/**
	 * Fires when the fee type changes to handle particular conditional fields.
	 */
	function vcmHandleFeeType(type) {
		if (type == 'Conditional') {
			jQuery('.vcm-bookingcom-newfee-5009').show();
		} else {
			jQuery('.vcm-bookingcom-newfee-5009').hide();
		}
	}

	/**
	 * Sets a rate plan block for activation/deactivation.
	 */
	function vcmToggleRatePlan(elem, mode) {
		if (!confirm(Joomla.JText._('VCM_ASK_CONTINUE'))) {
			return false;
		}
		var rplan_block = jQuery(elem).closest('.vcm-bookingcom-rateplan');
		var rplan_id = rplan_block.attr('data-rplan-id');
		// prepare block element for action
		rplan_block.addClass('vcm-params-block-opaque').append('<input type="hidden" class="vcm-bookingcom-rateplan-input" name="rateplans_' + (mode == 'activate' ? 'activate' : 'deactivate') + '[]" value="' + rplan_id + '" />');
		rplan_block.find('.vcm-bookingcom-rateplan-actionmsg').text(Joomla.JText._('VCM_MODELEM_AFTER_SAVE') + ' (' + (mode == 'activate' ? Joomla.JText._('VCMENABLED') : Joomla.JText._('VCMDISABLED')) + ')');
		// detect changes
		vcmDetectChangesRatePlan();
	}

	/**
	 * Sets a rate plan block for modification (change name).
	 */
	function vcmEditRatePlan(elem) {
		var rplan_block = jQuery(elem).closest('.vcm-bookingcom-rateplan');
		var rplan_name  = rplan_block.attr('data-rplan-name');
		var rplan_id 	= rplan_block.attr('data-rplan-id');
		var new_name 	= prompt(Joomla.JText._('VCMROOMSRELATIONSNAME'), rplan_name);

		if (!new_name || !new_name.length || new_name.toLowerCase() == rplan_name.toLowerCase()) {
			return false;
		}

		// prepare block element for name editing
		var hidden_inp = jQuery('<input/>')
			.attr('type', 'hidden')
			.attr('name', 'rateplans_edit[' + rplan_id + ']')
			.val(new_name)
			.addClass('vcm-bookingcom-rateplan-input');

		rplan_block.addClass('vcm-params-block-opaque').append(hidden_inp);
		rplan_block.find('.vcm-bookingcom-rateplan-actionmsg').text(Joomla.JText._('VCM_MODELEM_AFTER_SAVE') + ' (' + new_name + ')');

		// detect changes
		vcmDetectChangesRatePlan();
	}

	/**
	 * Renders the HTML for a new rate plan according to the input data.
	 * Fires when the modal window to create a rate plan is clicked to apply the changes.
	 */
	function vcmRenderRateplan() {
		// gather the whole form
		var rateplan_form = document.getElementById('vcm-bookingcom-rateplan-form');

		// collect fields
		var name = rateplan_form.rplan_name.value;
		var type = rateplan_form.rplan_type.value;
		if (!name || !type) {
			alert(Joomla.JText._('VCM_PLEASE_SELECT'));
			return false;
		}

		// clone HTML helper's children for a new rate plan to be added or replaced
		var base_info = jQuery('.vcm-bookingcom-newrateplan-helper').children().clone();
		base_info.addClass('vcm-params-block-opaque');

		// represent the form object
		var form_object = {
			name: name,
			type: type,
		};

		if (type == 'derived') {
			// validate and collect extra fields for derived rate plans
			var parent_rateplan_id = rateplan_form.elements['rplan_parentid'].value;
			var derived_percentage = rateplan_form.elements['rplan_percentage'].value;
			if (!parent_rateplan_id || !derived_percentage || isNaN(derived_percentage)) {
				alert(Joomla.JText._('VCM_PLEASE_SELECT'));
				return false;
			}

			// set additional values
			form_object['parentrateid'] = parent_rateplan_id;
			form_object['percentage'] 	= derived_percentage;
			form_object['followsprice'] = rateplan_form.elements['rplan_followsprice'].value;
			form_object['followsrestrictions'] = rateplan_form.elements['rplan_followsrestrictions'].value;
			form_object['followspolicygroup'] = rateplan_form.elements['rplan_followspolicygroup'].value;
			form_object['followsclosed'] = rateplan_form.elements['rplan_followsclosed'].value;
		}

		// append rate plan payload for saving
		base_info.find('.vcm-bookingcom-newrateplan-json').val(JSON.stringify(form_object)).addClass('vcm-bookingcom-rateplan-input');

		// render the block for the new rate plan with the data provided
		base_info.find('.vcm-bookingcom-newrateplan-name').text(name);
		base_info.find('.vcm-bookingcom-newrateplan-type').text((type == 'derived' ? Joomla.JText._('VCM_DERIVED_RPLAN_DESCR').replace('%s', 'Booking.com') : Joomla.JText._('VCM_PARENT_RPLAN_DESCR')));

		// append block to document as a new rate plan
		base_info.appendTo(jQuery('.vcm-bookingcom-rateplans'));

		// animate scroll to that position
		jQuery('html,body').animate({scrollTop: base_info.offset().top - 40}, {duration: 400});

		// detect changes
		vcmDetectChangesRatePlan();
	}

	/**
	 * Opens the modal to create a new rate plan.
	 */
	function vcmNewRatePlan() {
		// build the button to handle the adding of the values
		var btn_apply_rateplan_addnew = jQuery('<button></button>').attr('type', 'button').addClass('btn btn-success').html('<?php VikBookingIcons::e('check-circle'); ?> ' + Joomla.JText._('VCMRARADDLOSAPPLY'));
		btn_apply_rateplan_addnew.on('click', function() {
			// render the new rate plan
			if (vcmRenderRateplan() !== false) {
				// dismiss the modal
				VBOCore.emitEvent('close-rateplan-addnew');
			}
		});

		// render modal
		var modal_wrapper = VBOCore.displayModal({
			extra_class: 'vbo-modal-tall',
			suffix: 'rateplan-addnew',
			title: Joomla.JText._('VCMRARRATEPLAN') + ' - ' + Joomla.JText._('NEW'),
			body_prepend: true,
			lock_scroll: true,
			footer_right: btn_apply_rateplan_addnew,
			dismiss_event: 'close-rateplan-addnew',
			onDismiss: () => {
				// move HTML helper back to its location
				jQuery('.vcm-bookingcom-rateplan-helper').appendTo(jQuery('.vcm-bookingcom-rateplan-html-helper'));
			},
		});

		// access form data
		var helper_form = document.getElementById('vcm-bookingcom-rateplan-form');

		// reset the HTML helper form
		helper_form.reset();
		jQuery('.vcm-bookingcom-newrplan-derived').hide();

		// append content to modal
		jQuery('.vcm-bookingcom-rateplan-helper').appendTo(modal_wrapper);
	}

	/**
	 * Fires when the new rate plan type is changed.
	 */
	function vcmHandleRateplanType(type) {
		if (type == 'derived') {
			jQuery('.vcm-bookingcom-newrplan-derived').show();
		} else {
			jQuery('.vcm-bookingcom-newrplan-derived').hide();
		}
	}

	/**
	 * Detect changes related to rate plans.
	 */
	function vcmDetectChangesRatePlan() {
		var changes = jQuery('.vcm-bookingcom-rateplan-input').length;
		if (changes > 0) {
			jQuery('.vcm-bookingcom-undo-rateplans').show();
		} else {
			jQuery('.vcm-bookingcom-undo-rateplans').hide();
		}
	}

	/**
	 * Undo changes related to rate plans.
	 */
	function vcmUndoChangesRatePlan() {
		// remove all hidden input fields of this type
		jQuery('.vcm-bookingcom-rateplan-input').remove();

		// restore style
		jQuery('.vcm-bookingcom-rateplans')
			.find('.vcm-bookingcom-rateplan.vcm-params-block-opaque')
			.removeClass('vcm-params-block-opaque')
			.find('.vcm-bookingcom-rateplan-actionmsg')
			.text('-----');

		// detect changes
		vcmDetectChangesRatePlan();

		// ask to reload the page
		if (confirm(Joomla.JText._('VCM_ASK_RELOAD'))) {
			document.location.href = jQuery('.vcm-hidden-refresh-url').attr('href');
		}
	}

	/**
	 * Sets a room rate block for removal.
	 */
	function vcmRemoveRoomRate(elem) {
		if (!confirm(Joomla.JText._('VCM_ASK_CONTINUE'))) {
			return false;
		}
		var roomrate_block = jQuery(elem).closest('.vcm-bookingcom-roomrate');
		var room_rplan_block = jQuery(elem).closest('.vcm-bookingcom-roomrate-rplan');
		var room_id = roomrate_block.attr('data-room-id');
		var rplan_id = room_rplan_block.attr('data-rateplan-id');

		// prepare input field
		var payload = {
			room_id: 	 room_id,
			rateplan_id: rplan_id,
		};
		var inpfield = jQuery('<input/>')
			.attr('type', 'hidden')
			.attr('name', 'roomrates_remove[]')
			.val(JSON.stringify(payload))
			.addClass('vcm-bookingcom-roomrate-input');

		// prepare block element for removal
		roomrate_block.append(inpfield);
		room_rplan_block.addClass('vcm-params-block-opaque')
			.find('.vcm-bookingcom-roomrate-actionmsg')
			.text(Joomla.JText._('VCM_MODELEM_AFTER_SAVE') + ' (' + Joomla.JText._('REMOVE') + ')');

		// eventually remove any new cancellation policy appended to this block
		roomrate_block.find('.vcm-bookingcom-newcancpolicy-input').remove();

		// make sure to delete the hidden input field for update that might have become enabled
		setTimeout(() => {
			var update_field = room_rplan_block.find('input.vcm-bookingcom-roomrate-json[name="roomrates_update[]"]');
			if (update_field.length && !update_field.prop('disabled')) {
				// the remove action cannot be undone, and so updating is futile
				update_field.remove();
			}
		}, 200);
	}

	/**
	 * Renders the HTML for a new room-rate relation according to the input data.
	 * Fires when the modal window to create/update a room-rate is clicked to apply the changes.
	 */
	function vcmRenderRoomrate(editing_block, payload) {
		// gather the whole form
		var roomrate_form = document.getElementById('vcm-bookingcom-roomrate-form');

		// collect fields
		var room_id = roomrate_form.room_id.value;
		var rateplan_id = roomrate_form.rateplan_id.value;
		if (!room_id || !rateplan_id) {
			alert(Joomla.JText._('VCM_PLEASE_SELECT'));
			return false;
		}

		// represent the form object
		var is_editing = false;
		if (typeof editing_block === 'undefined' || editing_block == null || !payload) {
			// create a new room-rate
			var form_object = {
				room_id: room_id,
				rateplan_id: rateplan_id,
				meal_code: roomrate_form.elements['meal_code'].value,
				canc_code: roomrate_form.elements['cancpolicy_code'].value,
			};
		} else {
			// update existing room-rate (we start from the original payload and keep all properties except the ones below)
			var form_object = payload;
			form_object['meal_code'] = roomrate_form.elements['meal_code'].value;
			form_object['canc_code'] = roomrate_form.elements['cancpolicy_code'].value;

			// turn flag on
			is_editing = true;
		}

		// gather the value-adds service codes that were dynamically added to the form
		var vads_codes = [];
		var vads_attributes = {};

		// value-adds service codes
		roomrate_form.querySelectorAll('input.value_adds_service_codes').forEach((input) => {
			// push selected value-add service code
			vads_codes.push(input.value);
		});

		// value-adds service attributes
		roomrate_form.querySelectorAll('input.value_adds_service_values').forEach((input) => {
			// set attribute details for the selected value-add service
			let service_code = input.getAttribute('data-scode');
			let attribute_name = input.getAttribute('data-sname');

			if (!vads_attributes.hasOwnProperty(service_code)) {
				// start object container
				vads_attributes[service_code] = {};
			}

			// set attribute name and value
			vads_attributes[service_code][attribute_name] = input.value;

			// scan all service codes to find the selected one and to check if hidden attribute types (currency) are needed
			vcm_vads_codes.forEach((vads_group) => {
				for (const [vads_code, vads_data] of Object.entries(vads_group.list)) {
					if (vads_code == service_code && vads_data['attributes'].length) {
						// service found with attributes
						vads_data['attributes'].forEach((attr) => {
							if (attr['type'] == 'currency') {
								// set additional (hidden) attribute
								vads_attributes[service_code]['currency'] = '<?php echo $hotel_currency ?: VikBooking::getCurrencyName(); ?>';
							}
						});

						// stop checking
						break;
					}
				}
			});
		});

		// set value adds service codes and attributes, if any
		if (vads_codes.length) {
			form_object['value_adds_codes'] = vads_codes;
			form_object['value_adds_attributes'] = vads_attributes;
		} else {
			form_object['value_adds_codes'] = [];
			form_object['value_adds_attributes'] = null;
		}

		// check if a new/unused cancellation policy has been selected in order to create it
		var create_canc_policy = null;
		if (form_object['canc_code'] && !used_cancpolicies.hasOwnProperty(form_object['canc_code'])) {
			// register cancellation policy code to create
			create_canc_policy = form_object['canc_code'];
			// push the newly added cancellation policy code
			used_cancpolicies[form_object['canc_code']] = form_object['canc_code'];
		}

		// distinguish the behavior between new and edit room-rate
		if (!is_editing) {
			// clone HTML helper's children for a new room rate to be added or replaced
			var base_info = jQuery('.vcm-bookingcom-newroomrate-helper').children().clone();

			// append room-rate payload for saving
			base_info.find('.vcm-bookingcom-roomrate-json').val(JSON.stringify(form_object));

			// set data attribute with rate plan ID
			base_info.attr('data-rateplan-id', rateplan_id);
			// add "opaque" class
			base_info.addClass('vcm-params-block-opaque');

			// render the block for the new room-rate with the data provided
			base_info.find('.vcm-bookingcom-newroomrate-rplan-name').text(roomrate_form.elements['rateplan_id'].options[roomrate_form.elements['rateplan_id'].selectedIndex].text);
			base_info.find('.vcm-bookingcom-newroomrate-rplan-id').text('ID ' + rateplan_id);

			if (create_canc_policy) {
				// append a hidden input field with the instructions to add a new cancellation
				// policy code before creating this new room-rate to avoid errors
				base_info.append(
					jQuery('<input/>')
						.attr('type', 'hidden')
						.attr('name', 'cancpolicies[]')
						.val(create_canc_policy)
						.addClass('vcm-bookingcom-newcancpolicy-input')
				);
			}

			// append block to document as a new room-rate (before the button to add a new room-rate)
			base_info.insertBefore(jQuery('.vcm-bookingcom-roomrate[data-room-id="' + room_id + '"]').find('.vcm-bookingcom-roomrate-addnew'));

			// animate scroll to that position
			jQuery('html,body').animate({scrollTop: base_info.offset().top - 80}, {duration: 400});
		} else {
			// update payload string and enable field for update
			editing_block.find('.vcm-bookingcom-roomrate-json')
				.val(JSON.stringify(form_object))
				.prop('disabled', false)
				.closest('.vcm-param-container-tmp-disabled')
				.removeClass('vcm-param-container-tmp-disabled');

			// set edit message
			var edit_msg = Joomla.JText._('VCM_MODELEM_AFTER_SAVE') + ' (' + Joomla.JText._('EDIT') + ')';
			if (editing_block.find('.vcm-bookingcom-roomrate-actionmsg').find('.vcm-bookingcom-roomrate-editmsg').length) {
				editing_block.find('.vcm-bookingcom-roomrate-actionmsg').find('.vcm-bookingcom-roomrate-editmsg').text(edit_msg);
			} else {
				editing_block.find('.vcm-bookingcom-roomrate-actionmsg').append('<div class="vcm-bookingcom-roomrate-editmsg">' + edit_msg + '</div>');
			}

			// always update the cancellation policy info for this modified room-rate
			var canc_policy_msg = roomrate_form.elements['cancpolicy_code'].options[roomrate_form.elements['cancpolicy_code'].selectedIndex].text;
			if (editing_block.find('.vcm-bookingcom-roomrate-updateinfo').find('.vcm-param-setting-comment').length) {
				editing_block.find('.vcm-bookingcom-roomrate-updateinfo').find('.vcm-param-setting-comment').text(canc_policy_msg);
			} else {
				editing_block.find('.vcm-bookingcom-roomrate-updateinfo').append(jQuery('<span></span>').addClass('vcm-param-setting-comment').text(canc_policy_msg));
			}

			if (create_canc_policy) {
				// append a hidden input field with the instructions to add a new cancellation
				// policy code before creating this new room-rate to avoid errors
				editing_block.append(
					jQuery('<input/>')
						.attr('type', 'hidden')
						.attr('name', 'cancpolicies[]')
						.val(create_canc_policy)
						.addClass('vcm-bookingcom-newcancpolicy-input')
				);
			}
		}
	}

	/**
	 * Fires when the button to add a new room rate relation is clicked.
	 */
	function vcmNewRoomRate(room_id) {
		var rateplans_inp = jQuery('.vcm-bookingcom-roomrate[data-room-id="' + room_id + '"]').find('.vcm-bookingcom-roomrate-eligplans');
		if (!rateplans_inp || !rateplans_inp.length) {
			alert('No eligible rate plans found to add a new room-rate relation');
			return false;
		}

		var eligible = [];
		try {
			eligible = JSON.parse(rateplans_inp.val());
			if (!eligible.length) {
				throw new Error('Empty list');
			}
		} catch(error) {
			alert('No valid rate plans found to add a new room-rate relation');
			return false;
		}

		// build the button to handle the adding of the values
		var btn_apply_roomrate_addnew = jQuery('<button></button>').attr('type', 'button').addClass('btn btn-success').html('<?php VikBookingIcons::e('check-circle'); ?> ' + Joomla.JText._('VCMRARADDLOSAPPLY'));
		btn_apply_roomrate_addnew.on('click', function() {
			// render the new room rate relation
			if (vcmRenderRoomrate() !== false) {
				// dismiss the modal
				VBOCore.emitEvent('close-roomrate-addnew');
			}
		});

		// render modal
		var modal_wrapper = VBOCore.displayModal({
			extra_class: 'vbo-modal-tall',
			suffix: 'roomrate-addnew',
			title: Joomla.JText._('VCM_ROOMRATE_RELATIONS') + ' - ' + Joomla.JText._('NEW'),
			body_prepend: true,
			lock_scroll: true,
			footer_right: btn_apply_roomrate_addnew,
			dismiss_event: 'close-roomrate-addnew',
			onDismiss: () => {
				// move HTML helper back to its location
				jQuery('.vcm-bookingcom-roomrate-helper').appendTo(jQuery('.vcm-bookingcom-roomrate-html-helper'));
			},
		});

		// populate form data
		var helper_form = document.getElementById('vcm-bookingcom-roomrate-form');

		// reset the HTML helper form
		helper_form.reset();

		// populate values
		helper_form.querySelector('.vcm-bookingcom-roomrate-roomname').innerText = jQuery('.vcm-bookingcom-roomrate[data-room-id="' + room_id + '"]').attr('data-room-name');
		helper_form.elements['room_id'].value = room_id;
		helper_form.elements['cancpolicy_code'].value = '';
		helper_form.elements['cancpolicy_code'].dispatchEvent(new Event('change'));
		helper_form.elements['rateplan_id'].innerHTML = '<option value=""></option>';
		eligible.forEach((rplan) => {
			var rp_opt = document.createElement('option');
			rp_opt.text  = rplan['name'] + ' (ID ' + rplan['id'] + ')';
			rp_opt.value = rplan['id'];
			helper_form.elements['rateplan_id'].appendChild(rp_opt);
		});

		// make sure no value adds are set
		helper_form.querySelectorAll('.vcm-bookingcom-roomrate-valueadd-wrap').forEach((elem) => {
			elem.remove();
		});

		// re-enable any previously disabled service code option
		helper_form.querySelector('select.vcm-bookingcom-roomrate-valueadds').querySelectorAll('option').forEach((opt) => {
			opt.disabled = false;
		});
		helper_form.querySelector('select.vcm-bookingcom-roomrate-valueadds').dispatchEvent(new Event('change'));

		// append content to modal
		jQuery('.vcm-bookingcom-roomrate-helper').appendTo(modal_wrapper);

		// adjust select2 z-index so that it will be visible within the modal
		jQuery('.select2-drop').css('z-index', '2147483647');
	}

	/**
	 * Opens the modal to modify an existing room-rate relation.
	 */
	function vcmEditRoomRate(elem) {
		var editing_block = jQuery(elem).closest('.vcm-bookingcom-roomrate-rplan');
		try {
			var payload = JSON.parse(editing_block.find('.vcm-bookingcom-roomrate-json').val());
		} catch(e) {
			alert('Could not parse a valid payload for the selected room-rate relation. Please delete it and re-create it.');
			return false;
		}

		var room_id   = editing_block.closest('.vcm-bookingcom-roomrate').attr('data-room-id');
		var room_name = editing_block.closest('.vcm-bookingcom-roomrate').attr('data-room-name');

		// build the button to handle the adding of the values
		var btn_apply_roomrate_edit = jQuery('<button></button>').attr('type', 'button').addClass('btn btn-success').html('<?php VikBookingIcons::e('check-circle'); ?> ' + Joomla.JText._('VCMRARADDLOSAPPLY'));
		btn_apply_roomrate_edit.on('click', function() {
			// render the modified room-rate relation
			if (vcmRenderRoomrate(editing_block, payload) !== false) {
				// dismiss the modal
				VBOCore.emitEvent('close-roomrate-edit');
			}
		});

		// render modal
		var modal_wrapper = VBOCore.displayModal({
			extra_class: 'vbo-modal-tall',
			suffix: 'roomrate-edit',
			title: Joomla.JText._('VCM_ROOMRATE_RELATIONS') + ' - ' + Joomla.JText._('EDIT'),
			body_prepend: true,
			lock_scroll: true,
			footer_right: btn_apply_roomrate_edit,
			dismiss_event: 'close-roomrate-edit',
			onDismiss: () => {
				// move HTML helper back to its location
				jQuery('.vcm-bookingcom-roomrate-helper').appendTo(jQuery('.vcm-bookingcom-roomrate-html-helper'));
			},
		});

		// populate form data
		var helper_form = document.getElementById('vcm-bookingcom-roomrate-form');

		// reset the HTML helper form
		helper_form.reset();

		// populate values
		helper_form.querySelector('.vcm-bookingcom-roomrate-roomname').innerText = room_name;
		helper_form.elements['room_id'].value = room_id;
		helper_form.elements['meal_code'].value = payload['meal_code'] || '';
		helper_form.elements['cancpolicy_code'].value = payload['canc_code'] || '';
		helper_form.elements['cancpolicy_code'].dispatchEvent(new Event('change'));
		helper_form.elements['rateplan_id'].innerHTML = '<option value="' + payload['rateplan_id'] + '">' + payload['rateplan_name'] + '</option>';

		// clean up any previously set value adds
		helper_form.querySelectorAll('.vcm-bookingcom-roomrate-valueadd-wrap').forEach((elem) => {
			elem.remove();
		});

		// re-enable any previously disabled service code option
		helper_form.querySelector('select.vcm-bookingcom-roomrate-valueadds').querySelectorAll('option').forEach((opt) => {
			opt.disabled = false;
		});

		// check for current value-adds
		if (payload.hasOwnProperty('value_adds_codes') && payload['value_adds_codes'].length) {
			// populate existing value-adds in edit mode
			payload['value_adds_codes'].forEach((service_code) => {
				// scan all service codes to find the current one
				vcm_vads_codes.forEach((vads_group) => {
					let service_found = false;
					for (const [vads_code, vads_data] of Object.entries(vads_group.list)) {
						if (vads_code == service_code) {
							// service found
							service_found = true;
							// check for existing attributes set
							let vads_attributes = (payload['value_adds_attributes'] || {}).hasOwnProperty(service_code) ? payload['value_adds_attributes'][service_code] : null;
							// append service to the form
							vcmAddValueAddService(vads_code, vads_data, vads_group?.macro, vads_group?.group, vads_attributes);
							break;
						}
					}
					if (service_found) {
						// disable all services under this group, because only one service per group is allowed
						for (const [vads_code, vads_data] of Object.entries(vads_group.list)) {
							jQuery('select.vcm-bookingcom-roomrate-valueadds').find('option[value="' + vads_code + '"]').prop('disabled', true);
						}
					}
				});
			});
		}

		// refresh select2
		setTimeout(() => {
			helper_form.querySelector('select.vcm-bookingcom-roomrate-valueadds').dispatchEvent(new Event('change'));
		}, 200);

		// append content to modal
		jQuery('.vcm-bookingcom-roomrate-helper').appendTo(modal_wrapper);

		// adjust select2 z-index so that it will be visible within the modal
		jQuery('.select2-drop').css('z-index', '2147483647');
	}

	/**
	 * Fires when a different group of license is selected
	 */
	function vcmHandleLicenseGroup(group) {
		jQuery('.vcm-bookingcom-license-variant:not([data-variant-group="' + group + '"])').hide();
		jQuery('.vcm-bookingcom-license-variant[data-variant-group="' + group + '"]').show();
	}

	/**
	 * Fires when a license variant is selected (changed).
	 */
	function vcmHandleLicenseVariant(variant_id, group) {
		jQuery('.vcm-bookingcom-license-variant[data-variant-group="' + group + '"]').each(function() {
			if (jQuery(this).attr('data-variant-id') == variant_id) {
				jQuery(this).find('.vcm-bookingcom-license-variant-field').show();
			} else {
				jQuery(this).find('.vcm-bookingcom-license-variant-field').hide();
			}
		});
	}

	/**
	 * Renders the contract status and legal details.
	 */
	function vcmRenderContractDetails(contract) {
		if (!contract || !contract.hasOwnProperty('data')) {
			throw new Error('Unexpected contract response');
		}

		var rendered = 0;

		for (var prop in contract['data']) {
			if (!contract['data'].hasOwnProperty(prop) || (typeof contract['data'][prop] != 'string' && typeof contract['data'][prop] != 'number')) {
				continue;
			}
			// field values
			var field_name  = prop.replaceAll('_', ' ').toLowerCase();
			field_name 		= field_name.charAt(0).toUpperCase() + field_name.slice(1);
			var field_class = 'vcm-bookingcom-contract-' + prop.replaceAll('_', '-');

			if (!jQuery('.' + field_class).length) {
				// unexpected field from default values
				var contract_field_block = jQuery('<div/>')
					.addClass('vcm-param-container')
					.append(
						jQuery('<div/>')
							.addClass('vcm-param-label')
							.text(field_name)
					)
					.append(
						jQuery('<div/>')
							.addClass('vcm-param-setting')
							.addClass(field_class)
							.text(contract['data'][prop])
					);
				contract_field_block.appendTo(jQuery('.vcm-bookingcom-contract-data'));
			} else {
				// contract detail field is known
				jQuery('.' + field_class).text(contract['data'][prop]);
			}

			// increase counter
			rendered++;
		}

		if (rendered) {
			jQuery('.vcm-bookingcom-contract-data').show();
		}
	}

	/**
	 * Fires when the button to check the contract status is clicked to run a request.
	 */
	function vcmCheckContractStatus() {
		var legal_email = jQuery('#vcm-bookingcom-contract-email').val();
		if (!legal_email || legal_email.indexOf('@') < 1) {
			alert('Please enter your Booking.com Legal Contact Email');
			return false;
		}

		// display loading overlay
		vcmShowLoading();

		// make the ajax request to the requested controller
		VBOCore.doAjax(
			"<?php echo VikChannelManager::ajaxUrl('index.php?option=com_vikchannelmanager&task=bproperty.contract_status&e4j_debug=' . VikRequest::getInt('e4j_debug', 0, 'request')); ?>",
			{
				legal_email: legal_email
			},
			(res) => {
				// stop loading
				vcmStopLoading();
				try {
					// render the fields
					vcmRenderContractDetails(res);
				} catch(e) {
					alert(e);
				}
			},
			(err) => {
				// display error message
				alert(err.responseText);
				vcmStopLoading();
			}
		);
	}

	/**
	 * Fires when the button to resend the email with link to contracting tool is clicked.
	 */
	function vcmHandleContractResendLink() {
		// display loading overlay
		vcmShowLoading();

		// make the ajax request to the requested controller
		VBOCore.doAjax(
			"<?php echo VikChannelManager::ajaxUrl('index.php?option=com_vikchannelmanager&task=bproperty.contract_resend_link&e4j_debug=' . VikRequest::getInt('e4j_debug', 0, 'request')); ?>",
			{},
			(res) => {
				// stop loading
				vcmStopLoading();

				// disable the button
				jQuery('.vcm-bookingcom-contract-resendlink').find('button').prop('disabled', true);

				// display alert message
				alert(Joomla.JText._('MSG_BASE_SUCCESS') + '!');
			},
			(err) => {
				// display error message
				alert(err.responseText);
				vcmStopLoading();
			}
		);
	}
</script>
