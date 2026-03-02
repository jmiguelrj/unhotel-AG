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

JHtml::_('behavior.tooltip');
JHtml::_('behavior.calendar');

$params = $this->params;
$countries = $this->countries;

// Vik Booking Application for media field
$vbo_app = VikChannelManager::getVboApplication();

$user = JFactory::getUser();

if (!empty($params['amenities'])) {
	$params['amenities'] = explode(',', $params['amenities']);
} else {
	$params['amenities'] = array();
}

$all_empties = true;
foreach ($params as $k => $p) {
	$all_empties = empty($p) && $all_empties;
}

$ta_room_amenities = VikChannelManagerConfig::$TA_HOTEL_AMENITIES;
uasort($ta_room_amenities, array("VikChannelManagerConfig", "compareRoomAmenities"));

$select_amenities = VikChannelManager::composeSelectAmenities('amenities[]', $ta_room_amenities, $params['amenities'], 'vcmhotelamenities vcm-multi-select', true);

$countries_select = '<select name="country" id="vcmhcountry" class="'.((!$all_empties && empty($params['country'])) ? 'vcmrequired': '').'">';
$countries_select .= '<option value="">--</option>';
foreach( $countries as $c ) {
    $countries_select .= '<option value="'.$c['country_name'].'" '.($this->params['country'] == $c['country_name'] ? 'selected="selected"' : '').'>'.$c['country_name'].'</option>';
}
$countries_select .= '</select>';

// JS lang vars
JText::script('VCM_LATLNG_MUSTBENUMS');

/**
 * Property Class Types
 * 
 * @since 	1.7.2
 */
$active_pct = VikChannelManager::getActivePropertyClassTypes();

if (!VikChannelManager::checkIntegrityHotelDetails()) {
	?>
    <p class="vcmhdparwarning"><?php echo JText::_('VCMHOTELDETAILSFIRSTBUILDING'); ?></p>
	<?php
} elseif (VikChannelManager::hasGoogleHotelChannel() && !VikChannelManager::getHotelInventoryID()) {
	?>
	<p class="info"><?php echo JText::_('VCM_GOOGLE_NOHINV'); ?></p>
	<?php
}

$adding_new_hotel = false;
$support_multi_g_account = false;
$support_multi_ta_account = false;

if ($this->module['uniquekey'] == VikChannelManagerConfig::GOOGLEHOTEL && VikChannelManager::getHotelInventoryID() && VikChannelManager::channelHasRoomsMapped($this->module['uniquekey'])) {
	// turn flag on to support multiple hotel accounts with Google Hotel
	$support_multi_g_account = true;

	/**
	 * Display the possibility of adding another hotel account.
	 * 
	 * @since 	1.8.6
	 */
	if (!$all_empties) {
	?>
	<p class="info vcm-ghotel-add-multi-hotel" style="display: none;">
		<span><?php echo JText::_('VCM_GOOGLE_MULTI_HOTEL_ASK'); ?></span>
	</p>
	<p class="vcm-ghotel-add-multi-hotel" style="display: none;">
		<a class="btn btn-primary" href="index.php?option=com_vikchannelmanager&task=hoteldetails&add_multi_hotel=1"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::_('VCM_GOOGLE_MULTI_HOTEL_ADD'); ?></a>
		<a class="btn btn-danger" id="vcm-ghotel-hide-multi-hotel"><?php VikBookingIcons::e('times-circle'); ?> <?php echo JText::_('VCM_GOOGLE_MULTI_HOTEL_NO'); ?></a>
	</p>

	<script type="text/javascript">
		jQuery(function() {

			if (jQuery('.vcm-ghotel-add-multi-hotel').length && document.cookie.indexOf('vcmHideMultiGHotel=1') < 0) {
				jQuery('.vcm-ghotel-add-multi-hotel').fadeIn();
			}

			jQuery('#vcm-ghotel-hide-multi-hotel').click(function() {
				var nd = new Date();
				nd.setTime(nd.getTime() + (365*24*60*60*1000));
				document.cookie = "vcmHideMultiGHotel=1; expires=" + nd.toUTCString() + "; path=/; SameSite=Lax";
				jQuery('.vcm-ghotel-add-multi-hotel').hide();
			});

		});
	</script>
	<?php
	} elseif (VikRequest::getInt('add_multi_hotel', 0, 'request')) {
		// turn flag on and display info message
		$adding_new_hotel = true;
		?>
	<p class="info"><?php echo JText::_('VCM_GOOGLE_MULTI_HOTEL_ADD'); ?></p>
		<?php
	}
} elseif ($this->module['uniquekey'] == VikChannelManagerConfig::TRIP_CONNECT && !empty($this->module['params']['tripadvisorid'])) {
	// turn flag on to support multiple hotel accounts with TripAdvisor (TripConnect)
	$support_multi_ta_account = true;
	if (!$all_empties && $this->tac_rooms_mapped > 0 && $this->tac_vbo_tot_rooms > $this->tac_rooms_mapped) {
		// suggest to add a new hotel when one is configured with some rooms, but not all
		?>
	<p class="info vcm-tac-add-multi-hotel" style="display: none;">
		<span><?php echo preg_replace('/Google/i', 'TripAdvisor (TripConnect)', JText::_('VCM_GOOGLE_MULTI_HOTEL_ASK')); ?></span>
	</p>
	<p class="vcm-tac-add-multi-hotel" style="display: none;">
		<a class="btn btn-primary" href="index.php?option=com_vikchannelmanager&task=hoteldetails&add_multi_hotel=1"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::_('VCM_GOOGLE_MULTI_HOTEL_ADD'); ?></a>
		<a class="btn btn-danger" id="vcm-tac-hide-multi-hotel"><?php VikBookingIcons::e('times-circle'); ?> <?php echo JText::_('VCM_GOOGLE_MULTI_HOTEL_NO'); ?></a>
	</p>

	<script type="text/javascript">
		jQuery(function() {

			if (jQuery('.vcm-tac-add-multi-hotel').length && document.cookie.indexOf('vcmHideMultiTacHotel=1') < 0) {
				jQuery('.vcm-tac-add-multi-hotel').fadeIn();
			}

			jQuery('#vcm-tac-hide-multi-hotel').click(function() {
				var nd = new Date();
				nd.setTime(nd.getTime() + (365*24*60*60*1000));
				document.cookie = "vcmHideMultiTacHotel=1; expires=" + nd.toUTCString() + "; path=/; SameSite=Lax";
				jQuery('.vcm-tac-add-multi-hotel').hide();
			});

		});
	</script>
		<?php
	} elseif (VikRequest::getInt('add_multi_hotel', 0, 'request')) {
		// turn flag on and display info message
		$adding_new_hotel = true;
		?>
	<p class="info"><?php echo JText::_('VCM_GOOGLE_MULTI_HOTEL_ADD'); ?></p>
		<?php
	}
}
?>

<form name="adminForm" id="adminForm" action="index.php" method="post">
	<div class="vcm-admin-container">
		
		<div class="vcm-config-maintab-left">
		<?php
		if ($support_multi_g_account && $this->multi_hotels) {
			// Google Hotel multiple accounts
			?>
			<fieldset class="adminform">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VCMMANAGEACCOUNTS'); ?></legend>
					<div class="vcm-params-container">
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMSELECTACCOUNT'); ?></div>
							<div class="vcm-param-setting">
								<select id="multi-hotel-account-val" onchange="vcmSelectMultiHotelAccount(this.value);">
									<option value=""><?php echo VCMGhotelMultiaccounts::getMainHotelName() . ' (' . VikChannelManager::getHotelInventoryID() . ')'; ?></option>
								<?php
								foreach ($this->multi_hotels as $multi_hotel) {
									?>
									<option value="<?php echo $multi_hotel['id']; ?>"<?php echo $this->multi_haccount_id == $multi_hotel['id'] ? ' selected="selected"' : ''; ?>><?php echo $multi_hotel['hname'] . (!empty($multi_hotel['account_id']) ? (' (' . $multi_hotel['account_id'] . ')') : ''); ?></option>
									<?php
								}
								?>
								</select>
							</div>
						</div>
					</div>
				</div>
			</fieldset>

			<a href="index.php?option=com_vikchannelmanager&task=hoteldetails" id="vcm-hdetails-base-uri" style="display: none;"></a>

			<script type="text/javascript">
				function vcmSelectMultiHotelAccount(id) {
					var base_uri = document.getElementById('vcm-hdetails-base-uri').getAttribute('href');
					if (!id || !id.length) {
						// main account selected
						document.location.href = base_uri;
						return;
					}
					base_uri += '&multi_hotel_account=' + id;
					document.location.href = base_uri;
					return;
				}
			</script>
			<?php
		} elseif ($support_multi_ta_account && $this->multi_hotels) {
			// TripConnect multiple accounts
			?>
			<fieldset class="adminform">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VCMMANAGEACCOUNTS'); ?></legend>
					<div class="vcm-params-container">
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMSELECTACCOUNT'); ?></div>
							<div class="vcm-param-setting">
								<select id="multi-hotel-account-val" onchange="vcmSelectMultiHotelAccount(this.value);">
									<option value=""><?php echo VCMGhotelMultiaccounts::getMainHotelName() . ' (' . VCMFactory::getConfig()->get('tac_partner_ta_id', 'First Account') . ')'; ?></option>
								<?php
								foreach ($this->multi_hotels as $multi_hotel) {
									?>
									<option value="<?php echo $multi_hotel['account_id']; ?>"<?php echo $this->multi_haccount_id == $multi_hotel['id'] ? ' selected="selected"' : ''; ?>><?php echo $multi_hotel['hname'] . (!empty($multi_hotel['account_id']) ? (' (' . $multi_hotel['account_id'] . ')') : ''); ?></option>
									<?php
								}
								?>
								</select>
							</div>
						</div>
					</div>
				</div>
			</fieldset>

			<a href="index.php?option=com_vikchannelmanager&task=hoteldetails" id="vcm-hdetails-base-uri" style="display: none;"></a>

			<script type="text/javascript">
				function vcmSelectMultiHotelAccount(id) {
					var base_uri = document.getElementById('vcm-hdetails-base-uri').getAttribute('href');
					if (!id || !id.length) {
						// main account selected
						document.location.href = base_uri;
						return;
					}
					base_uri += '&multi_hotel_account=' + id;
					document.location.href = base_uri;
					return;
				}
			</script>
			<?php
		}
		?>
			<fieldset class="adminform">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VCMBPROMOHDET'); ?></legend>
					<div class="vcm-params-container">
						
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMTACHOTELNAME'); ?>*</div>
							<div class="vcm-param-setting"><input type="text" name="name" value="<?php echo $params['name']; ?>" size="30" id="vcmhname" onBlur="checkRequiredField('vcmhname');" class="<?php echo ((!$all_empties && empty($params['name'])) ? 'vcmrequired': ''); ?>"/></div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMPCTS'); ?>*</div>
							<div class="vcm-param-setting">
								<select name="pct[]" id="pct" multiple="multiple" class="vcm-multi-select" onchange="checkRequiredField('pct');">
								<?php
								foreach (VikChannelManager::getAllPropertyClassTypes() as $pct_cat => $pcts) {
									?>
									<optgroup label="<?php echo $pct_cat; ?>">
									<?php
									foreach ($pcts as $pct_key => $pct_val) {
										?>
										<option value="<?php echo $pct_key; ?>"<?php echo in_array($pct_key, $active_pct) ? ' selected="selected"' : ''; ?>><?php echo $pct_val; ?></option>
										<?php
									}
									?>
									</optgroup>
									<?php
								}
								?>
								</select>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMTACHOTELEMAIL'); ?>*</div>
							<div class="vcm-param-setting"><input type="text" name="email" value="<?php echo ((empty($params['email'])) ? $user->email : $params['email']); ?>" size="30"/></div>
						</div>
						
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMTACHOTELSTREET'); ?>*</div>
							<div class="vcm-param-setting"><input type="text" name="street" value="<?php echo $params['street']; ?>" size="30" id="vcmhstreet" onBlur="checkRequiredField('vcmhstreet');" class="<?php echo ((!$all_empties && empty($params['street'])) ? 'vcmrequired': ''); ?>"/></div>
						</div>
						
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMTACHOTELCITY'); ?>*</div>
							<div class="vcm-param-setting"><input type="text" name="city" value="<?php echo $params['city']; ?>" size="30" id="vcmhcity" onBlur="checkRequiredField('vcmhcity');" class="<?php echo ((!$all_empties && empty($params['city'])) ? 'vcmrequired': ''); ?>"/></div>
						</div>
						
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMTACHOTELZIP'); ?></div>
							<div class="vcm-param-setting"><input type="text" name="zip" value="<?php echo $params['zip']; ?>" size="5"/></div>
						</div>
						
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMTACHOTELSTATE'); ?></div>
							<div class="vcm-param-setting"><input type="text" name="state" value="<?php echo $params['state']; ?>" size="15"/></div>
						</div>
						
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMTACHOTELCOUNTRY'); ?>*</div>
							<div class="vcm-param-setting"><?php echo $countries_select; ?></div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMTACHOTELPHONE'); ?>*</div>
							<div class="vcm-param-setting">
								<input type="text" id="vcmhphone" name="phone" value="<?php echo $params['phone']; ?>" size="30" onBlur="checkRequiredField('vcmhphone');"/>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMTACHOTELFAX'); ?></div>
							<div class="vcm-param-setting"><input type="text" name="fax" value="<?php echo $params['fax']; ?>" size="30"/></div>
						</div>

					<?php
					if ($vbo_app !== false && method_exists($vbo_app, 'getMediaField')) {
						// Vik Booking must be updated in order to support the media field
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCM_HOTEL_MAIN_PIC'); ?></div>
							<div class="vcm-param-setting">
								<?php echo $vbo_app->getMediaField('main_pic', (!empty($params['main_pic']) ? $params['main_pic'] : null), array('multiple' => false, 'id' => "vcm-hotel-main-pic")); ?>
								<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_HOTEL_MAIN_PIC_DESCR'); ?></span>
							</div>
						</div>
						<?php
					}
					?>

					</div>
				</div>
			</fieldset>
		</div>

		<div class="vcm-config-maintab-right">
			<fieldset class="adminform">
				<div class="vcm-params-wrap">
					<div class="vcm-params-container">
						
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMTACHOTELDESCRIPTION'); ?></div>
							<div class="vcm-param-setting"><textarea name="description" rows="4" cols="30"><?php echo $params['description']; ?></textarea></div>
						</div>
						
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMTACHOTELAMENITIES'); ?></div>
							<div class="vcm-param-setting"><?php echo $select_amenities; ?></div>
						</div>
						
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMTACHOTELURL'); ?>*</div>
							<div class="vcm-param-setting">
								<input type="text" name="url" value="<?php echo ((empty($params['url'])) ? (JUri::root() . ($support_multi_ta_account && $adding_new_hotel ? 'hotel-' . uniqid() : '')) : $params['url']); ?>" size="30" id="vcmhurl" onBlur="checkRequiredField('vcmhurl');" class="<?php echo ((!$all_empties && empty($params['url'])) ? 'vcmrequired': ''); ?>"/>
							<?php
							if ($support_multi_ta_account && $adding_new_hotel) {
								?>
								<span class="vcm-param-setting-comment">Note: the URL for this new property must be different from the URL of all other properties already configured with TripConnect, or the information will NOT be accepted by TripConnect.</span>
								<?php
							}
							?>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMTACHOTELLATITUDE'); ?>*</div>
							<div class="vcm-param-setting">
								<div class="btn-wrapper input-append">
									<input type="text" id="vcmhlat" name="latitude" value="<?php echo $params['latitude']; ?>" data-ftype="latitude" onBlur="checkRequiredField('vcmhlat');" size="10"/>
									<button type="button" class="btn vcm-config-btn vcm-get-coords" title="<?php echo htmlspecialchars(JText::_('VCM_YOUR_CURR_LOCATION')); ?>"><?php VikBookingIcons::e('location-arrow'); ?></button>
								</div>
							</div>
						</div>
						
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMTACHOTELLONGITUDE'); ?>*</div>
							<div class="vcm-param-setting">
								<input type="text" id="vcmhlng" name="longitude" value="<?php echo $params['longitude']; ?>" data-ftype="longitude" onBlur="checkRequiredField('vcmhlng');" size="10"/>
							</div>
						</div>

					<?php
					if ($this->display_vress > 0 && is_object($this->vress_data)) {
						// display Vacation Rentals Essentials fields (which will be the only one in this block as the others will be moved onto the left side of the page via JS)
						$logo = VikChannelManager::getLogosInstance($this->module['name']);
						$ch_logo_url = $logo->getLogoURL();
						if ($ch_logo_url) {
							?>
						<div class="vcm-param-container vcm-param-vressentials">
							<div class="vcm-param-setting">
								<img src="<?php echo $ch_logo_url; ?>" alt="<?php echo $this->escape($this->module['name']); ?>" class="vcm-hdet-vress-chlogo" />
							<?php
							if (count($this->channels_mapping) && !empty($this->hotelid) && isset($this->channels_mapping[$this->hotelid])) {
								?>
								<span class="vcm-hdet-vress-propname"><?php echo $this->channels_mapping[$this->hotelid]; ?></span>
								<?php
							}
							?>
							</div>
						</div>
							<?php
						}

						// Key Collection API
						if (isset($this->vress_data->read) && isset($this->vress_data->read->KeyCollection) && isset($this->vress_data->read->KeyCollection->StreamVariations) && isset($this->vress_data->read->KeyCollection->CheckinMethods) 
							&& count($this->vress_data->read->KeyCollection->StreamVariations) && count($this->vress_data->read->KeyCollection->CheckinMethods)) 
						{
							// data available for building the form to collect the Property Check-in Methods for this property
							?>
						<legend class="adminlegend vcm-hdet-vress-title"><?php echo JText::_('VCMVRESSPCINMETS'); ?></legend>
							<?php
							// find the current/existing check-in methods defined for the various stream variations
							$stream_checkin_mapping = array();
							$stream_addinfo_mapping = array();
							if (isset($this->vress_data->read->KeyCollection->PropertyCheckinMethods) && is_array($this->vress_data->read->KeyCollection->PropertyCheckinMethods)) {
								foreach ($this->vress_data->read->KeyCollection->PropertyCheckinMethods as $prop_cin_met) {
									if (empty($prop_cin_met->stream_variation_name) || empty($prop_cin_met->checkin_method)) {
										continue;
									}
									// push relation between stream variation name and check-in method
									$stream_checkin_mapping[$prop_cin_met->stream_variation_name] = $prop_cin_met->checkin_method;
									// check additional info provided
									if (isset($prop_cin_met->additional_info) && is_object($prop_cin_met->additional_info) && count(get_object_vars($prop_cin_met->additional_info))) {
										// some additional info were provided for this stream variation name and check-in method, push the mapping
										$stream_addinfo_mapping[$prop_cin_met->stream_variation_name] = $prop_cin_met->additional_info;
									}
								}
							}

							// prepare helper HTML for defining the additional infos depending on the check-in method selected
							?>
						<div class="vcm-vress-hidden-helper" style="display: none;">
							<div class="vcm-param-container vcm-param-nested vcm-param-vressentials" data-checkin-methods="reception|someone_will_meet|secret_spot|door_code|lock_box|">
								<div class="vcm-param-label"><?php echo JText::_('VCMVRESSPCINMETADDINFOTXT'); ?></div>
								<div class="vcm-param-setting">
									<textarea data-setname="vress_other_text[%d]" data-infoname="other_text" name="" rows="4" cols="30"></textarea>
								</div>
							</div>
							<div class="vcm-param-container vcm-param-nested vcm-param-vressentials" data-checkin-methods="door_code|lock_box|">
								<div class="vcm-param-label"><?php echo JText::_('VCMVRESSPCINMETADDINFOBRANDNM'); ?></div>
								<div class="vcm-param-setting">
									<input type="text" data-setname="vress_brand_name[%d]" data-infoname="brand_name" name="" size="30"/>
								</div>
							</div>
							<div class="vcm-param-container vcm-param-nested vcm-param-vressentials" data-checkin-methods="reception|someone_will_meet|secret_spot|lock_box|">
								<div class="vcm-param-label"><?php echo JText::_('VCMVRESSPCINMETADDINFOOFFLOC'); ?></div>
								<div class="vcm-param-setting">
									<select data-setname="vress_off_location[%d]" data-infoname="off_location" name="" onchange="vcmVREssOffLocation(this.value, jQuery(this));">
										<option value="0"><?php echo JText::_('VCMNO'); ?></option>
										<option value="1"><?php echo JText::_('VCMYES'); ?></option>
									</select>
								</div>
							</div>
							<div class="vcm-param-container vcm-param-nested vcm-param-vressentials vcm-param-vressentials-hidden vcm-param-vressentials-cond-offlocation" style="display: none;" data-checkin-methods="reception|someone_will_meet|secret_spot|lock_box|">
								<div class="vcm-param-label"><?php echo JText::_('VCMBCAHADDRESS'); ?></div>
								<div class="vcm-param-setting">
									<input type="text" data-setname="vress_address[%d]" data-infoname="address" name="" size="30"/>
								</div>
							</div>
							<div class="vcm-param-container vcm-param-nested vcm-param-vressentials vcm-param-vressentials-hidden vcm-param-vressentials-cond-offlocation" style="display: none;" data-checkin-methods="reception|someone_will_meet|secret_spot|lock_box|">
								<div class="vcm-param-label"><?php echo JText::_('VCMTACHOTELCITY'); ?></div>
								<div class="vcm-param-setting">
									<input type="text" data-setname="vress_city[%d]" data-infoname="city" name="" size="30"/>
								</div>
							</div>
							<div class="vcm-param-container vcm-param-nested vcm-param-vressentials vcm-param-vressentials-hidden vcm-param-vressentials-cond-offlocation" style="display: none;" data-checkin-methods="reception|someone_will_meet|secret_spot|lock_box|">
								<div class="vcm-param-label"><?php echo JText::_('VCMTACHOTELZIP'); ?></div>
								<div class="vcm-param-setting">
									<input type="text" data-setname="vress_zip[%d]" data-infoname="zip" name="" size="30"/>
								</div>
							</div>
							<div class="vcm-param-container vcm-param-nested vcm-param-vressentials" data-checkin-methods="instruction_will_send|instruction_contact_us|">
								<div class="vcm-param-label"><?php echo JText::_('VCMVRESSPCINMETADDINFOHOW'); ?></div>
								<div class="vcm-param-setting">
									<select data-setname="vress_how[%d]" data-infoname="how" name="" onchange="vcmVREssInstructionsHow(this.value, jQuery(this));">
										<option value="phone"><?php echo JText::_('VCMBCAHPHONE'); ?></option>
										<option value="email">Email</option>
										<option value="sms">SMS</option>
										<option value="other"><?php echo JText::_('VCMBCAHOTHER'); ?></option>
									</select>
								</div>
							</div>
							<div class="vcm-param-container vcm-param-nested vcm-param-vressentials vcm-param-vressentials-cond-how-nonother" data-checkin-methods="instruction_will_send|">
								<div class="vcm-param-label"><?php echo JText::_('VCMVRESSPCINMETADDINFOWHEN'); ?></div>
								<div class="vcm-param-setting">
									<select data-setname="vress_when[%d]" data-infoname="when" name="">
										<option value="immediate">Immediate</option>
										<option value="month_before">Month Before</option>
										<option value="week_before">Week Before</option>
										<option value="day_of_arrival">Day of arrival</option>
									</select>
								</div>
							</div>
							<div class="vcm-param-container vcm-param-nested vcm-param-vressentials vcm-param-vressentials-cond-how-nonother" data-checkin-methods="instruction_contact_us|">
								<div class="vcm-param-label"><?php echo JText::_('VCMBCAHCINFO'); ?></div>
								<div class="vcm-param-setting">
									<input type="text" data-setname="vress_identifier[%d]" data-infoname="identifier" name="" size="30"/>
								</div>
							</div>
							<div class="vcm-param-container vcm-param-nested vcm-param-vressentials vcm-param-vressentials-hidden vcm-param-vressentials-cond-how-other" style="display: none;" data-checkin-methods="instruction_will_send|instruction_contact_us|">
								<div class="vcm-param-label"><?php echo JText::_('VCMVRESSPCINMETADDINFOEXPL'); ?></div>
								<div class="vcm-param-setting">
									<input type="text" data-setname="vress_other[%d]" data-infoname="other" name="" size="30"/>
								</div>
							</div>
						</div>
							<?php
							//

							// loop through all stream variations to define the check-in methods
							foreach ($this->vress_data->read->KeyCollection->StreamVariations as $pindex => $streamv) {
								?>
						<div class="vcm-param-container vcm-param-vressentials" data-streamvar-name="<?php echo $streamv->name; ?>">
							<div class="vcm-param-label"><?php echo $streamv->description; ?></div>
							<div class="vcm-param-setting">
								<select name="vress_<?php echo $streamv->name; ?>" class="vcm-vress-sel" onchange="vcmVREssSetCMAddInfos(this.value, '<?php echo $streamv->name; ?>', '<?php echo $pindex; ?>');">
									<option value=""></option>
								<?php
								foreach ($this->vress_data->read->KeyCollection->CheckinMethods as $checkinm) {
									?>
									<option value="<?php echo $checkinm->name; ?>"<?php echo isset($stream_checkin_mapping[$streamv->name]) && $stream_checkin_mapping[$streamv->name] == $checkinm->name ? ' selected="selected"' : ''; ?>><?php echo $checkinm->description; ?></option>
									<?php
								}
								?>
								</select>
							</div>
						</div>
						<div class="vcm-param-vressentials-addinfo" data-streamvar-name="<?php echo $streamv->name; ?>" style="display: none;"></div>
								<?php
							}
						}

						// Property Profile API (do it even if we do not have it in $this->vress_data->read->PropertyProfile as it may be empty, just do not populate any values)
						$current_property_profile = null;
						if (isset($this->vress_data->read) && isset($this->vress_data->read->PropertyProfile) && is_object($this->vress_data->read->PropertyProfile) && count(get_object_vars($this->vress_data->read->PropertyProfile))) {
							$current_property_profile = $this->vress_data->read->PropertyProfile;
						}
						?>
						<legend class="adminlegend vcm-hdet-vress-title"><?php echo JText::_('VCMVRESSPROPPROFILE'); ?></legend>
						
						<div class="vcm-param-container vcm-param-vressentials">
							<div class="vcm-param-label"><?php echo JText::_('VCMVRESSPROPBUILTDATE'); ?></div>
							<div class="vcm-param-setting"><?php echo JHTML::_('calendar', '', 'built_date', 'built_date', '%Y-%m-%d', array('class'=>'', 'size'=>'10',  'maxlength'=>'19', 'todayBtn' => 'true')); ?></div>
						</div>

						<div class="vcm-param-container vcm-param-vressentials">
							<div class="vcm-param-label"><?php echo JText::_('VCMVRESSPROPRENOVATINGDATE'); ?></div>
							<div class="vcm-param-setting"><?php echo JHTML::_('calendar', '', 'renovating_date', 'renovating_date', '%Y-%m-%d', array('class'=>'', 'size'=>'10',  'maxlength'=>'19', 'todayBtn' => 'true')); ?></div>
						</div>

						<div class="vcm-param-container vcm-param-vressentials">
							<div class="vcm-param-label"><?php echo JText::_('VCMTACHOTELNAME') . '/' . JText::_('VCMVRESSPROPCOMPANY'); ?></div>
							<div class="vcm-param-setting"><input type="text" name="name_or_company" value="<?php echo is_object($current_property_profile) && isset($current_property_profile->name_or_company) ? $this->escape($current_property_profile->name_or_company) : ''; ?>" size="30" /></div>
						</div>

						<div class="vcm-param-container vcm-param-vressentials">
							<div class="vcm-param-label"><?php echo JText::_('VCMVRESSPROPHOSTLOC'); ?></div>
							<div class="vcm-param-setting">
								<select name="host_location">
									<option value="offsite"<?php echo is_object($current_property_profile) && isset($current_property_profile->host_location) && $current_property_profile->host_location == 'offsite' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMVRESSPROPHOSTLOCOFF'); ?></option>
									<option value="onsite"<?php echo is_object($current_property_profile) && isset($current_property_profile->host_location) && $current_property_profile->host_location == 'onsite' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMVRESSPROPHOSTLOCON'); ?></option>
								</select>
							</div>
						</div>

						<div class="vcm-param-container vcm-param-vressentials">
							<div class="vcm-param-label"><?php echo JText::_('VCMVRESSPROPRENTINGDATE'); ?></div>
							<div class="vcm-param-setting"><?php echo JHTML::_('calendar', '', 'renting_date', 'renting_date', '%Y-%m-%d', array('class'=>'', 'size'=>'10',  'maxlength'=>'19', 'todayBtn' => 'true')); ?></div>
						</div>

						<div class="vcm-param-container vcm-param-vressentials">
							<div class="vcm-param-label"><?php echo JText::_('VCMVRESSISCOMPANYPROFILE'); ?></div>
							<div class="vcm-param-setting">
								<select name="is_company_profile">
									<option value="0"<?php echo is_object($current_property_profile) && isset($current_property_profile->is_company_profile) && (int)$current_property_profile->is_company_profile === 0 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMNO'); ?></option>
									<option value="1"<?php echo is_object($current_property_profile) && isset($current_property_profile->is_company_profile) && (int)$current_property_profile->is_company_profile === 1 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMYES'); ?></option>
								</select>
							</div>
						</div>

						<?php
						// populate calendar fields for Property Profile API (if any)
						if (is_object($current_property_profile)) {
							?>
						<script type="text/javascript">
						jQuery(function() {
						<?php
						if (isset($current_property_profile->built_date) && !empty($current_property_profile->built_date)) {
							?>
							jQuery('#built_date').val('<?php echo $current_property_profile->built_date; ?>').attr('data-alt-value', '<?php echo $current_property_profile->built_date; ?>');
							<?php
						}
						if (isset($current_property_profile->renovating_date) && !empty($current_property_profile->renovating_date)) {
							?>
							jQuery('#renovating_date').val('<?php echo $current_property_profile->renovating_date; ?>').attr('data-alt-value', '<?php echo $current_property_profile->renovating_date; ?>');
							<?php
						}
						if (isset($current_property_profile->renting_date) && !empty($current_property_profile->renting_date)) {
							?>
							jQuery('#renting_date').val('<?php echo $current_property_profile->renting_date; ?>').attr('data-alt-value', '<?php echo $current_property_profile->renting_date; ?>');
							<?php
						}
						?>
							// display any additional/conditional value for the key collections
							jQuery('select.vcm-vress-sel').trigger('change');
						});
						</script>
							<?php
						}
						//
						if (VikRequest::getInt('e4j_debug', 0, 'request')) {
							echo 'Debug<pre>' . print_r($this->vress_data, true) . '</pre>';
						}
					}
					?>

					</div>
				</div>
			</fieldset>
		<?php
		/**
		 * Attempt to render a Google Map to help locate the coordinates.
		 * 
		 * @since 	1.8.4
		 */
		$geocoding = VikChannelManager::getGeocodingInstance();
		if ($geocoding) {
			// load the necessary assets
			$geocoding->loadAssets();
			// check if latitude and longitude have been defined
			$valid_coords = (!empty($params['latitude']) && !empty($params['longitude']));
			// render the map
			?>
			<fieldset class="adminform vcm-map-fieldset" style="<?php echo !$valid_coords ? 'display: none;' : ''; ?>">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VCM_MAP'); ?></legend>
					<div class="vcm-params-container">
						
						<div class="vcm-param-container vcm-param-map">
							<div class="vcm-param-setting">
								<span class="vcm-param-setting-comment">
									<a id="geo_address_formatted" data-lat="" data-lng="" href="JavaScript: void(0);"></a>
								</span>
							</div>
						</div>

						<div class="vcm-param-container vcm-param-map">
							<div class="vcm-param-setting">
								<div id="vcm-geo-map" style="width: 100%; height: 300px;"></div>
							</div>
						</div>

					</div>
				</div>
			</fieldset>

			<script type="text/javascript">
				var vcm_geomap = null,
					vcm_geocoder = null,
					vcm_geomarker_hotel = null;

				/**
				 * Starts the Google Map at the given position.
				 * Initializes also the Geocoder utility.
				 */
				function vcmInitGeoMap(start_lat, start_lng) {
					if (isNaN(start_lat) || isNaN(start_lng)) {
						console.error('given latitude and longitude are not numbers', start_lat, start_lng);
						// overwrite values to a default location
						start_lat = '43.7734385';
						start_lng = '11.2565501';
					}

					// default map options
					var def_map_options = {
						center: new google.maps.LatLng(start_lat, start_lng),
						zoom: 18
					};

					// initialize Map
					vcm_geomap = new google.maps.Map(document.getElementById('vcm-geo-map'), def_map_options);

					// initialize Geocoder
					vcm_geocoder = new google.maps.Geocoder();

					// add map marker for hotel
					vcm_geomarker_hotel = new google.maps.Marker({
						draggable: true,
						map: vcm_geomap,
						position: {
							lat: parseFloat(start_lat),
							lng: parseFloat(start_lng)
						},
						title: jQuery('#vcmhname').val()
					});

					// add listener to marker
					vcm_geomarker_hotel.addListener('dragend', function() {
						// update lat and lng
						var current_lat = vcm_geomarker_hotel.getPosition().lat();
						var current_lng = vcm_geomarker_hotel.getPosition().lng();
						jQuery('input[data-ftype="latitude"]').val(current_lat);
						jQuery('input[data-ftype="longitude"]').val(current_lng);
					});
				}

				/**
				 * Gets the position of all markers to extend the bounds,
				 * and then sets the zoom and bounds to fit them all.
				 */
				function vcmGeoMapCenterBounds() {
					if (vcm_geomap === null || vcm_geomarker_hotel === null) {
						console.error('map is null');
						return false;
					}
					// set map center and zoom automatically
					var latlngbounds = new google.maps.LatLngBounds();
					// get main address marker position
					latlngbounds.extend(vcm_geomarker_hotel.getPosition());
					// apply calculated center and bounds
					vcm_geomap.setCenter(latlngbounds.getCenter());
					vcm_geomap.fitBounds(latlngbounds);
				}

				/**
				 * Define the necessary listeners and render the map.
				 */
				jQuery(function() {

					// init geo map
					var start_lat = '<?php echo !empty($params['latitude']) ? $params['latitude'] : '43.7734385'; ?>';
					var start_lng = '<?php echo !empty($params['longitude']) ? $params['longitude'] : '11.2565501'; ?>';
					vcmInitGeoMap(start_lat, start_lng);

					// change event listener for street, city and country to invoke the Geocoder
					jQuery('#vcmhstreet, #vcmhcity, #vcmhcountry').change(function() {
						var street 	= jQuery('#vcmhstreet').val();
						var city 	= jQuery('#vcmhcity').val();
						var country = jQuery('#vcmhcountry').val();
						if (!street.length || !city.length || !country.length) {
							return;
						}
						// query the geocoder to find the coordinates
						var geo_addr = street + ', ' + city + ', ' + country;
						if (!vcm_geocoder) {
							console.error('Geocoder not available');
							return;
						}
						// grab the current lat and lng
						var hotel_lat = jQuery('input[data-ftype="latitude"]').val();
						var hotel_lng = jQuery('input[data-ftype="longitude"]').val();
						// silently perform the request
						vcm_geocoder.geocode({'address': geo_addr}, function(results, status) {
							if (status == 'OK') {
								var multi_results = (results.length > 1);
								// get first result's coordinates
								var calc_lat = results[0].geometry.location.lat();
								var calc_lng = results[0].geometry.location.lng();
								// populate formatted address
								if (results[0].hasOwnProperty('formatted_address')) {
									jQuery('#geo_address_formatted').text(results[0].formatted_address).attr('data-lat', calc_lat).attr('data-lng', calc_lng);
								}
								if (!hotel_lat.length || !hotel_lng.length) {
									// set coordinates
									jQuery('input[data-ftype="latitude"]').val(calc_lat);
									jQuery('input[data-ftype="longitude"]').val(calc_lng);
								}
								// remove any previously added marker for hotel
								if (vcm_geomarker_hotel !== null) {
									// we always re-create the marker rather than using .setPosition()
									// as the map could have no marker yet, in case of no previous data.
									vcm_geomarker_hotel.setMap(null);
								}
								// add map marker for hotel at the new position
								vcm_geomarker_hotel = new google.maps.Marker({
									draggable: true,
									map: vcm_geomap,
									position: results[0].geometry.location,
									title: jQuery('#vcmhname').val()
								});
								// add listener to marker
								vcm_geomarker_hotel.addListener('dragend', function() {
									// update lat and lng
									var current_lat = vcm_geomarker_hotel.getPosition().lat();
									var current_lng = vcm_geomarker_hotel.getPosition().lng();
									jQuery('input[data-ftype="latitude"]').val(current_lat);
									jQuery('input[data-ftype="longitude"]').val(current_lng);
								});
								// set map center and zoom automatically
								vcmGeoMapCenterBounds();
								// display map fieldset
								if (!jQuery('.vcm-map-fieldset').is(':visible')) {
									jQuery('.vcm-map-fieldset').fadeIn();
								}
							} else {
								// log the error
								console.error('Geocoder failed', status);
							}
						});
					});

					// click event listener for Geocoder calculated location
					jQuery('#geo_address_formatted').click(function() {
						var elem = jQuery(this);
						var sugg_lat = elem.attr('data-lat');
						var sugg_lng = elem.attr('data-lng');
						var sugg_add = elem.text();
						if (!sugg_lat || !sugg_lng || !sugg_add.length) {
							return;
						}
						// apply clicked coordinates
						jQuery('input[data-ftype="latitude"]').val(sugg_lat);
						jQuery('input[data-ftype="longitude"]').val(sugg_lng);
						// empty suggestion
						elem.text('');
						elem.blur();
						// check if map is available
						if (vcm_geomap != null && vcm_geomarker_hotel != null) {
							// update marker position
							vcm_geomarker_hotel.setPosition(new google.maps.LatLng(sugg_lat, sugg_lng));
							// set map center and zoom automatically
							vcmGeoMapCenterBounds();
						}
					});

				});
			</script>
			<?php
		}
		?>
		</div>

	</div>
<?php
if ($adding_new_hotel) {
	// add flag to form
	?>
	<input type="hidden" name="add_multi_hotel" value="1"/>
	<?php
} elseif (!empty($this->multi_haccount_id)) {
	// add hidden input field for the multi-hotel-account ID currently being edited
	?>
	<input type="hidden" name="multi_hotel_account" value="<?php echo $this->multi_haccount_id; ?>"/>
	<?php
}
?>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="option" value="com_vikchannelmanager" />
</form>

<script>
	
	function checkRequiredField(id) {
		var elem = jQuery('#'+id);
		if (!elem.length) {
			return;
		}
		var lbl = elem.closest('.vcm-param-container').find('.vcm-param-label');
		if (!lbl.length) {
			return;
		}
		var cont = elem.val();
		if (cont.length) {
			lbl.removeClass('vcm-param-label-isrequired');
			var ftype = elem.attr('data-ftype');
			if (ftype && (ftype == 'latitude' || ftype == 'longitude') && isNaN(cont)) {
				alert(Joomla.JText._('VCM_LATLNG_MUSTBENUMS'));
			}
			return true;
		}
		lbl.addClass('vcm-param-label-isrequired');
		return false;
	}

	jQuery(function() {
		if (jQuery('.vcm-config-maintab-right').find('.vcm-param-container').length && <?php echo (int)$this->display_vress; ?>) {
			// move all hotel details onto the left side of the page wrapper, because the right side will contain the channel API information
			jQuery('.vcm-config-maintab-right').find('.vcm-param-container').not('.vcm-param-vressentials').not('.vcm-param-map').appendTo(jQuery('.vcm-config-maintab-left').find('.vcm-params-container'));
		}
		jQuery('.vcmhotelamenities').select2({
			allowClear: false,
			placeholder: "<?php echo addslashes(JText::_('VCMTACHOTELAMENITIES')); ?>",
			width: 300
		});
		jQuery('#pct').select2({
			allowClear: false,
			placeholder: "<?php echo addslashes(JText::_('VCMPCTSPLCHLD')); ?>",
			width: 300
		});
		jQuery('.vcm-vress-sel').select2();
		jQuery('#vcmhcountry').select2();

		jQuery('.vcm-get-coords').click(function() {
			if (!navigator.geolocation) {
				alert('Geolocation not supported');
				return false;
			}
			// request current position to browser
			navigator.geolocation.getCurrentPosition(function(pos) {
				var crd = pos.coords;
				jQuery('input[data-ftype="latitude"]').val(crd.latitude);
				jQuery('input[data-ftype="longitude"]').val(crd.longitude);
				// check if map is available
				if (typeof vcm_geomap !== 'undefined' && vcm_geomap != null && vcm_geomarker_hotel != null) {
					// update marker position
					vcm_geomarker_hotel.setPosition(new google.maps.LatLng(crd.latitude, crd.longitude));
					// set map center and zoom automatically
					vcmGeoMapCenterBounds();
					// display map fieldset
					if (!jQuery('.vcm-map-fieldset').is(':visible')) {
						jQuery('.vcm-map-fieldset').fadeIn();
					}
				}
			}, function(err) {
				alert(`Error (${err.code}): ${err.message}`);
			});
		});
	});

	function vcmVREssSetCMAddInfos(check_met, streamvar_name, index) {
		var stream_checkin_mapping = {};
		var stream_addinfo_mapping = {};
	<?php
	if (isset($stream_checkin_mapping) && isset($stream_addinfo_mapping)) {
		// we define these objects for JS to populate the current values set on the channel
		?>
		stream_checkin_mapping = <?php echo json_encode($stream_checkin_mapping); ?>;
		stream_addinfo_mapping = <?php echo json_encode($stream_addinfo_mapping); ?>;
		<?php
	}
	?>
		// always unset any additional info for the selected check-in method and current stream variation name
		jQuery('.vcm-param-vressentials-addinfo[data-streamvar-name="' + streamvar_name + '"]').html('');

		// find whether this checkin method requires additional information from the HTML helpers by looping over all of them
		jQuery('.vcm-vress-hidden-helper').find('.vcm-param-vressentials').each(function() {
			var info_methods = jQuery(this).attr('data-checkin-methods');
			if (info_methods.indexOf(check_met + '|') < 0) {
				// continue, this additional info field is not suited for this check-in method
				return;
			}

			// clone field to leave the original where it is, and then manipulate it
			var addinfo_block = jQuery(this).clone();

			// manipulate cloned field
			if (addinfo_block.hasClass('vcm-param-vressentials-hidden')) {
				// re-hide field that could have been shown before
				addinfo_block.hide();
			}
			// set proper name to input/select/textarea field
			if (addinfo_block.find('.vcm-param-setting').find('input').length) {
				// input field
				var setname = addinfo_block.find('.vcm-param-setting').find('input').attr('data-setname').replace('%d', index);
				addinfo_block.find('.vcm-param-setting').find('input').attr('name', setname);
			}
			if (addinfo_block.find('.vcm-param-setting').find('select').length) {
				// select field
				var setname = addinfo_block.find('.vcm-param-setting').find('select').attr('data-setname').replace('%d', index);
				addinfo_block.find('.vcm-param-setting').find('select').attr('name', setname);
			}
			if (addinfo_block.find('.vcm-param-setting').find('textarea').length) {
				// textarea field
				var setname = addinfo_block.find('.vcm-param-setting').find('textarea').attr('data-setname').replace('%d', index);
				addinfo_block.find('.vcm-param-setting').find('textarea').attr('name', setname);
			}

			// populate current value on channel (if any, and if same stream variation and check-in method)
			if (stream_checkin_mapping.hasOwnProperty(streamvar_name) && stream_checkin_mapping[streamvar_name] == check_met && stream_addinfo_mapping.hasOwnProperty(streamvar_name)) {
				// some default values are available, parse the entire additional info current object
				for (var info in stream_addinfo_mapping[streamvar_name]) {
					if (!stream_addinfo_mapping[streamvar_name].hasOwnProperty(info)) {
						continue;
					}
					var checkval = info;
					var setval = '';
					if (info == 'other_text') {
						// this is an object that also contains "lang" which we do not need, but only "text", it's useless to loop over it
						setval = stream_addinfo_mapping[streamvar_name][info]['text'];
						// try setting actual value
						if (addinfo_block.find('[data-infoname="' + checkval + '"]').length) {
							addinfo_block.find('[data-infoname="' + checkval + '"]').val(setval);
						}
					} else if (typeof stream_addinfo_mapping[streamvar_name][info] == 'string') {
						// this is a string like "brand_name"
						setval = stream_addinfo_mapping[streamvar_name][info];
						// try setting actual value
						if (addinfo_block.find('[data-infoname="' + checkval + '"]').length) {
							addinfo_block.find('[data-infoname="' + checkval + '"]').val(setval);
						}
					} else {
						// must be a nested object, loop over it
						if (typeof stream_addinfo_mapping[streamvar_name][info] != 'object') {
							// not an object, skip it
							continue;
						}
						// maximum nesting level for additional info is 2, so we are safe by looping here, we will find only strings
						for (var subinfo in stream_addinfo_mapping[streamvar_name][info]) {
							if (!stream_addinfo_mapping[streamvar_name][info].hasOwnProperty(subinfo) || typeof stream_addinfo_mapping[streamvar_name][info][subinfo] != 'string') {
								// invalid property or no string contained for populating the value
								continue;
							}
							checkval = subinfo;
							setval = stream_addinfo_mapping[streamvar_name][info][subinfo];
							// try setting actual value
							if (addinfo_block.find('[data-infoname="' + checkval + '"]').length) {
								addinfo_block.find('[data-infoname="' + checkval + '"]').val(setval);
							}
						}
					}
				}
			}

			// append additional info field for this stream variation
			addinfo_block.appendTo(jQuery('.vcm-param-vressentials-addinfo[data-streamvar-name="' + streamvar_name + '"]'));
		});

		// show additional info
		if (!jQuery('.vcm-param-vressentials-addinfo[data-streamvar-name="' + streamvar_name + '"]').is(':visible')) {
			jQuery('.vcm-param-vressentials-addinfo[data-streamvar-name="' + streamvar_name + '"]').show();
		}
	}

	function vcmVREssOffLocation(val, elem) {
		if (parseInt(val) > 0) {
			elem.closest('.vcm-param-vressentials-addinfo').find('.vcm-param-vressentials-cond-offlocation').show();
		} else {
			elem.closest('.vcm-param-vressentials-addinfo').find('.vcm-param-vressentials-cond-offlocation').hide();
		}
	}

	function vcmVREssInstructionsHow(val, elem) {
		if (val == 'other') {
			elem.closest('.vcm-param-vressentials-addinfo').find('.vcm-param-vressentials-cond-how-other').show();
			elem.closest('.vcm-param-vressentials-addinfo').find('.vcm-param-vressentials-cond-how-nonother').hide();
		} else {
			elem.closest('.vcm-param-vressentials-addinfo').find('.vcm-param-vressentials-cond-how-other').hide();
			elem.closest('.vcm-param-vressentials-addinfo').find('.vcm-param-vressentials-cond-how-nonother').show();
		}
	}
	
</script>
