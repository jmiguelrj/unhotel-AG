<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2022 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

// No direct access to this file
defined('ABSPATH') or die('No script kiddies please!');

// load assets
$document = JFactory::getDocument();
$document->addStyleSheet(VBO_SITE_URI . 'resources/vikfxgallery.css');
$document->addScript(VBO_SITE_URI . 'resources/vikfxgallery.js');
// we use JHtml to load the jQuery UI Sortable script for compatibility with WP
JHtml::script(VBO_SITE_URI . 'resources/jquery-ui.sortable.min.js');

// Vik Booking Application for special field types
$vbo_app = VikChannelManager::getVboApplication();

// translator object and default language
$tn_obj = VikBooking::getTranslator();
$lang_code = $tn_obj->getDefaultLang();
$lang_code = substr(strtolower($lang_code), 0, 2);
if (!isset(VCMVrboListing::$supported_locales[$lang_code])) {
	$lang_code = 'en';
}

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

// wrap listing object into a JObject object
$listing = new JObject($this->listing);
$vbo_listing = new JObject($this->vbo_listing);

// the listing unit property
$unit = $listing->get('unit');

// check if we are in editing or new mode
$is_editing = count(get_object_vars($this->listing));

// check if the listing is mapped
$listing_mapped = $is_editing && isset($this->otalistings[$listing->get('id', -1)]);

// the name of the PM
$pm_name = VCMFactory::getConfig()->get('account_name_' . VikChannelManagerConfig::VRBOAPI, '');

// city taxes assigned to the listing, if any
$listing_city_taxes = [];
if ($listing_mapped) {
	// get all mandatory fees for this room
	$mandatory_fees = VikChannelManager::getAllMandatoryFees([$listing->get('id', -1)]);

	// attempt to extract the mandatory city taxes
	$listing_city_taxes = VCMVrboXml::getInstance()->getListingTypedFees($mandatory_fees, 'city');
}

// Vrbo listing and unit amenities
$vrbo_listing_amenities = VCMVrboListing::getAmenityCodesData();
$vrbo_unit_amenities = VCMVrboListing::getUnitFeatureValues();
$vrbo_unit_safety_amenities = VCMVrboListing::getUnitSafetyFeatureValues();

// lang vars for JS
JText::script('VCMREMOVECONFIRM');
JText::script('VCM_PLEASE_SELECT');
JText::script('MSG_BASE_SUCCESS');
JText::script('MSG_BASE_WARNING_BOOKING_RAR');
JText::script('VCM_PHOTO_CAPTION');
JText::script('VCMBCAHQUANTITY');

?>

<div class="vcm-loading-overlay">
	<div class="vcm-loading-dot vcm-loading-dot1"></div>
	<div class="vcm-loading-dot vcm-loading-dot2"></div>
	<div class="vcm-loading-dot vcm-loading-dot3"></div>
	<div class="vcm-loading-dot vcm-loading-dot4"></div>
	<div class="vcm-loading-dot vcm-loading-dot5"></div>
</div>

<div class="vcm-listings-list-head">
	<h3><?php echo JText::_('VCMACCOUNTCHANNELID') . ' ' . $this->channel['params']['hotelid'] . (!empty($hotel_name) ? ' - ' . $hotel_name : ''); ?></h3>
<?php
if ($is_editing) {
	// print the toolbar when in edit mode to quickly jump to the desired section
	?>
	<div class="vcm-listing-toolbar-wrap">
		<div class="vcm-listing-toolbar-inner">
			<div class="vcm-listing-toolbar-block">
				<span class="vcm-listing-toolbar-btn" data-jumpto="main">
					<a href="JavaScript: void(0);"><?php VikBookingIcons::e('home'); ?> <span><?php echo JText::_('VCMROOMSRELDETAILS'); ?></span></a>
				</span>
			</div>
			<div class="vcm-listing-toolbar-block">
				<span class="vcm-listing-toolbar-btn" data-jumpto="unit">
					<a href="JavaScript: void(0);"><?php VikBookingIcons::e('bed'); ?> <span>Unit</span></a>
				</span>
			</div>
			<div class="vcm-listing-toolbar-block">
				<span class="vcm-listing-toolbar-btn" data-jumpto="photos">
					<a href="JavaScript: void(0);"><?php VikBookingIcons::e('camera'); ?> <span><?php echo JText::_('VCMMENUBPHOTOS'); ?></span></a>
				</span>
			</div>
			<div class="vcm-listing-toolbar-block">
				<span class="vcm-listing-toolbar-btn" data-jumpto="license">
					<a href="JavaScript: void(0);"><?php VikBookingIcons::e('id-badge'); ?> <span>License</span></a>
				</span>
			</div>
			<div class="vcm-listing-toolbar-block">
				<span class="vcm-listing-toolbar-btn" data-jumpto="amenities">
					<a href="JavaScript: void(0);"><?php VikBookingIcons::e('icons'); ?> <span><?php echo JText::_('VCMTACHOTELAMENITIES'); ?></span></a>
				</span>
			</div>
			<div class="vcm-listing-toolbar-block">
				<span class="vcm-listing-toolbar-btn" data-jumpto="location">
					<a href="JavaScript: void(0);"><?php VikBookingIcons::e('map'); ?> <span><?php echo JText::_('VCMBCAHIMGTAG123'); ?></span></a>
				</span>
			</div>
			<div class="vcm-listing-toolbar-block">
				<span class="vcm-listing-toolbar-btn" data-jumpto="lodging">
					<a href="JavaScript: void(0);"><?php VikBookingIcons::e('hand-paper'); ?> <span><?php echo JText::_('VCMBCAHPOLICIES'); ?></span></a>
				</span>
			</div>
		</div>
	</div>
	<div class="vcm-listing-content-validation-status">
	<?php
	$content_validated_info = VCMVrboListing::contentValidationPass($this->listing);
	if ($content_validated_info[0] !== true) {
		?>
		<p class="err"><?php VikBookingIcons::e('exclamation-circle'); ?> <?php echo JText::_('VCM_VRBO_LISTING_CONTVALIDATION_STATUS'); ?>: <?php echo $content_validated_info[1]; ?></p>
		<?php
	} else {
		?>
		<p class="info"><?php VikBookingIcons::e('check-circle'); ?> <?php echo JText::_('VCM_VRBO_LISTING_CONTVALIDATION_OK'); ?></p>
		<?php
	}
	?>
	</div>
	<?php
}
?>
</div>

<form action="index.php?option=com_vikchannelmanager" method="post" name="adminForm" id="adminForm">
	<div class="vcm-admin-container vcm-admin-container-hastables">

		<div class="vcm-config-maintab-left">

			<fieldset class="adminform vcm-listings-listing-wrap" data-landto="main">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('home'); ?> <?php echo $is_editing ? (JText::_('VCM_MNGLISTING_EDIT') . ' - ' . $listing->get('id')) : JText::_('VCM_MNGLISTING_NEW'); ?></legend>
					<div class="vcm-params-container">

						<div class="vcm-param-container">
							<div class="vcm-param-label">Listing Headline (Name)</div>
							<div class="vcm-param-setting">
								<input type="text" name="listing[name]" id="vcm-listing-name" value="<?php echo $this->escape($listing->get('name', '')); ?>" maxlength="100" />
								<span class="vcm-param-setting-comment">Headline for the advertisement. 20 characters are required for the listing to pass the minimum content check. Max 100 characters.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Property Name</div>
							<div class="vcm-param-setting">
								<input type="text" name="listing[propertyName]" value="<?php echo $this->escape($listing->get('propertyName', $pm_name)); ?>" maxlength="400" />
								<span class="vcm-param-setting-comment">Name of the property. This name is not displayed on the Property Details Page (PDP); it is displayed on the Vrbo owner dashboard only.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VBSTATUS'); ?></div>
							<div class="vcm-param-setting">
								<select name="listing[active]">
									<option value="true"<?php echo $listing->get('active', null) ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMPROMSTATUSACTIVE'); ?></option>
									<option value="false"<?php echo !$listing->get('active', null) ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMPROMSTATUSINACTIVE'); ?></option>
								</select>
								<span class="vcm-param-setting-comment">Inactive listings will be de-activated from Vrbo until the status will change again.</span>
							</div>
						</div>

						<?php
						$listing_description = $listing->get('description', '');
						$descr_suggested = false;
						if (!$listing_description) {
							$listing_description = strip_tags($vbo_listing->get('info', ''));
							$descr_suggested = true;
						}
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-label">Description</div>
							<div class="vcm-param-setting">
								<textarea name="listing[description]" minlength="400" maxlength="10000"<?php echo $descr_suggested ? ' class="vcm-listing-editable"' : ''; ?>><?php echo $this->escape($listing_description); ?></textarea>
								<span class="vcm-param-setting-comment">Description of the property. 400 characters are required for the listing to pass the minimum content check.</span>
							</div>
						</div>

						<?php
						$summary = $listing->get('accommodationsSummary', $vbo_listing->get('smalldesc', ''));
						if (!$summary && $listing_description) {
							if (function_exists('mb_substr')) {
								$summary = mb_substr($listing_description, 0, 80, 'UTF-8');
							} else {
								$summary = substr($listing_description, 0, 80);
							}
						}
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-label">Accommodation Summary</div>
							<div class="vcm-param-setting">
								<textarea name="listing[accommodationsSummary]" maxlength="400"<?php echo $descr_suggested ? ' class="vcm-listing-editable"' : ''; ?>><?php echo $this->escape($summary); ?></textarea>
								<span class="vcm-param-setting-comment">Summary of the accommodations that the property provides. Example: Condo, 3 Bedrooms, 3 Baths (Sleeps 10).</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMCONFCURNAME'); ?></div>
							<div class="vcm-param-setting">
								<input type="text" name="listing[currency]" value="<?php echo $this->escape($listing->get('currency', VikBooking::getCurrencyName())); ?>" maxlength="3" />
								<span class="vcm-param-setting-comment">3-char ISO 4217 Currency Code.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Default locale (language code)</div>
							<div class="vcm-param-setting">
								<input type="text" name="listing[forced_locale]" value="<?php echo $this->escape($listing->get('forced_locale', $lang_code)); ?>" maxlength="2" />
								<span class="vcm-param-setting-comment">2-char default language code for the listing contents. Leave it empty to apply the website default language code.</span>
							</div>
						</div>

					</div>
				</div>
			</fieldset>

			<fieldset class="adminform vcm-listings-listing-wrap" data-landto="unit">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('bed'); ?> Unit</legend>
					<div class="vcm-params-container">

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMROOMSRELATIONSNAME') . ' / Plot Number'; ?></div>
							<div class="vcm-param-setting">
								<input type="text" name="listing[unit][name]" value="<?php echo is_object($unit) && !empty($unit->name) ? $this->escape($unit->name) : $this->escape($vbo_listing->get('name')); ?>" />
								<span class="vcm-param-setting-comment">The name of the unit. Can be also a plot number or a reference number.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCM_BED_SIZE'); ?></div>
							<div class="vcm-param-setting">
								<input type="number" name="listing[unit][area]" min="0" value="<?php echo is_object($unit) && isset($unit->area) ? (int)$unit->area : ''; ?>" />
								<span class="vcm-param-setting-comment">Usable area of the unit.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCM_AIRBNB_STDFEE_UNIT_TYPE'); ?></div>
							<div class="vcm-param-setting">
								<select name="listing[unit][areaUnit]">
									<option value="METERS_SQUARED"<?php echo is_object($unit) && isset($unit->areaUnit) && stripos($unit->areaUnit, 'meters') !== false ? ' selected="selected"' : ''; ?>>Square Meters</option>
									<option value="SQUARE_FEET"<?php echo is_object($unit) && isset($unit->areaUnit) && stripos($unit->areaUnit, 'feet') !== false ? ' selected="selected"' : ''; ?>>Square Feet</option>
								</select>
								<span class="vcm-param-setting-comment">Unit of measure in which the unit’s area is expressed.</span>
							</div>
						</div>

					<?php
					if ($is_editing) {
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('BATHROOMS'); ?></div>
							<div class="vcm-param-setting">
								<button type="button" class="btn vcm-config-btn" onclick="vcmAddBathroom();"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::_('VCMBCAHADD'); ?></button>
								<span class="vcm-param-setting-comment">Structured Room data about the bathroom(s) in the unit. Required.</span>
							</div>
						</div>

						<div class="vcm-vrbo-bathrooms">

						<?php
						$bathrooms = is_object($unit) && isset($unit->bathrooms) && is_array($unit->bathrooms) ? $unit->bathrooms : [];
						$bathroom_amenity_values = VCMVrboListing::getBathroomFeatureValues();
						$bathroom_types = VCMVrboListing::getBathroomTypeValues();
						foreach ($bathrooms as $k => $bathroom) {
							if (!is_object($bathroom) || !isset($bathroom->roomSubType)) {
								// invalid bathroom object structure
								continue;
							}
							$bathroom_amenities = isset($bathroom->amenities) && is_array($bathroom->amenities) ? $bathroom->amenities : [];
							?>
							<div class="vcm-params-block vcm-vrbo-bathroom">

								<div class="vcm-param-container">
									<div class="vcm-param-label"><?php echo JText::_('VCMBCAHIMGTAG52'); ?></div>
									<div class="vcm-param-setting">
										<button type="button" class="btn btn-danger" onclick="vcmRemoveBathroom(this);"><?php VikBookingIcons::e('times-circle'); ?></button>
									</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label"><?php echo JText::_('VCMBCAHAMENTYPE8'); ?></div>
									<div class="vcm-param-setting">
										<select name="listing[unit][bathrooms][<?php echo $k; ?>][amenities][]" data-buildname="listing[unit][bathrooms][%d][amenities][]" class="vcm-multi-select" multiple="multiple">
										<?php
										foreach ($bathroom_amenity_values as $bathroom_amenity_code => $bathroom_amenity_name) {
											?>
											<option value="<?php echo $bathroom_amenity_code; ?>"<?php echo in_array($bathroom_amenity_code, $bathroom_amenities) ? ' selected="selected"' : ''; ?>><?php echo $bathroom_amenity_name; ?></option>
											<?php
										}
										?>
										</select>
										<span class="vcm-param-setting-comment">Amenities associated with the bathroom.</span>
									</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label"><?php echo JText::_('VCMRESLOGSTYPE'); ?></div>
									<div class="vcm-param-setting">
										<select name="listing[unit][bathrooms][<?php echo $k; ?>][roomSubType]" data-buildname="listing[unit][bathrooms][%d][roomSubType]">
										<?php
										foreach ($bathroom_types as $bathroom_type_code => $bathroom_type_name) {
											?>
											<option value="<?php echo $bathroom_type_code; ?>"<?php echo $bathroom_type_code == $bathroom->roomSubType ? ' selected="selected"' : ''; ?>><?php echo $bathroom_type_name; ?></option>
											<?php
										}
										?>
										</select>
										<span class="vcm-param-setting-comment">Sub-category for the bathroom.</span>
									</div>
								</div>

							</div>
							<?php
						}
						?>

						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMBCAHIMGTAG52') . ' - ' . JText::_('VCMROOMSRELDETAILS'); ?></div>
							<div class="vcm-param-setting">
								<textarea name="listing[unit][bathroomDetails]"><?php echo is_object($unit) && isset($unit->bathroomDetails) ? $this->escape($unit->bathroomDetails) : ''; ?></textarea>
								<span class="vcm-param-setting-comment">Optional details about the bathroom(s) in the unit.</span>
							</div>
						</div>

						<div class="vcm-vrbo-bathroom-clone-copy" style="display: none;">

							<div class="vcm-params-block vcm-vrbo-bathroom">

								<div class="vcm-param-container">
									<div class="vcm-param-label"><?php echo JText::_('VCMBCAHIMGTAG52'); ?></div>
									<div class="vcm-param-setting">
										<button type="button" class="btn btn-danger" onclick="vcmRemoveBathroom(this);"><?php VikBookingIcons::e('times-circle'); ?></button>
									</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label"><?php echo JText::_('VCMBCAHAMENTYPE8'); ?></div>
									<div class="vcm-param-setting">
										<select data-buildname="listing[unit][bathrooms][%d][amenities][]" class="vcm-render-multi-select vcm-listing-editable" multiple="multiple">
										<?php
										foreach ($bathroom_amenity_values as $bathroom_amenity_code => $bathroom_amenity_name) {
											?>
											<option value="<?php echo $bathroom_amenity_code; ?>"><?php echo $bathroom_amenity_name; ?></option>
											<?php
										}
										?>
										</select>
										<span class="vcm-param-setting-comment">Amenities associated with the bathroom.</span>
									</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label"><?php echo JText::_('VCMRESLOGSTYPE'); ?></div>
									<div class="vcm-param-setting">
										<select data-buildname="listing[unit][bathrooms][%d][roomSubType]" class="vcm-listing-editable">
										<?php
										foreach ($bathroom_types as $bathroom_type_code => $bathroom_type_name) {
											?>
											<option value="<?php echo $bathroom_type_code; ?>"><?php echo $bathroom_type_name; ?></option>
											<?php
										}
										?>
										</select>
										<span class="vcm-param-setting-comment">Sub-category for the bathroom.</span>
									</div>
								</div>

							</div>

						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCM_BEDROOMS'); ?></div>
							<div class="vcm-param-setting">
								<button type="button" class="btn vcm-config-btn" onclick="vcmAddBedroom();"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::_('VCMBCAHADD'); ?></button>
								<span class="vcm-param-setting-comment">Structured Room data about the bedroom(s) in the unit. Required.</span>
							</div>
						</div>

						<div class="vcm-vrbo-bedrooms">

						<?php
						$bedrooms = is_object($unit) && isset($unit->bedrooms) && is_array($unit->bedrooms) ? $unit->bedrooms : [];
						$bedroom_amenity_values = VCMVrboListing::getBedroomFeatureValues();
						$bedroom_types = VCMVrboListing::getBedroomTypeValues();
						foreach ($bedrooms as $k => $bedroom) {
							if (!is_object($bedroom) || !isset($bedroom->roomSubType)) {
								// invalid bedroom object structure
								continue;
							}
							$bedroom_amenities = isset($bedroom->amenities) && is_array($bedroom->amenities) ? $bedroom->amenities : [];
							?>
							<div class="vcm-params-block vcm-vrbo-bedroom">

								<div class="vcm-param-container">
									<div class="vcm-param-label"><?php echo JText::_('VCMBCAHIMGTAG84'); ?></div>
									<div class="vcm-param-setting">
										<button type="button" class="btn btn-danger" onclick="vcmRemoveBedroom(this);"><?php VikBookingIcons::e('times-circle'); ?></button>
									</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label"><?php echo JText::_('VCMTACHOTELAMENITIES'); ?></div>
									<div class="vcm-param-setting">
										<select name="listing[unit][bedrooms][<?php echo $k; ?>][amenities][]" data-buildname="listing[unit][bedrooms][%d][amenities][]" class="vcm-multi-select" multiple="multiple">
										<?php
										foreach ($bedroom_amenity_values as $bedroom_amenity_code => $bedroom_amenity_name) {
											?>
											<option value="<?php echo $bedroom_amenity_code; ?>"<?php echo in_array($bedroom_amenity_code, $bedroom_amenities) ? ' selected="selected"' : ''; ?>><?php echo $bedroom_amenity_name; ?></option>
											<?php
										}
										?>
										</select>
										<span class="vcm-param-setting-comment">Amenities associated with the bedroom.</span>
									</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label"><?php echo JText::_('VCMRESLOGSTYPE'); ?></div>
									<div class="vcm-param-setting">
										<select name="listing[unit][bedrooms][<?php echo $k; ?>][roomSubType]" data-buildname="listing[unit][bedrooms][%d][roomSubType]">
										<?php
										foreach ($bedroom_types as $bedroom_type_code => $bedroom_type_name) {
											?>
											<option value="<?php echo $bedroom_type_code; ?>"<?php echo $bedroom_type_code == $bedroom->roomSubType ? ' selected="selected"' : ''; ?>><?php echo $bedroom_type_name; ?></option>
											<?php
										}
										?>
										</select>
										<span class="vcm-param-setting-comment">Sub-category for the bedroom.</span>
									</div>
								</div>

							</div>
							<?php
						}
						?>

						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMBCAHIMGTAG84') . ' - ' . JText::_('VCMROOMSRELDETAILS'); ?></div>
							<div class="vcm-param-setting">
								<textarea name="listing[unit][bedroomDetails]"><?php echo is_object($unit) && isset($unit->bedroomDetails) ? $this->escape($unit->bedroomDetails) : ''; ?></textarea>
								<span class="vcm-param-setting-comment">Optional details about the bedroom(s) in the unit.</span>
							</div>
						</div>

						<div class="vcm-vrbo-bedroom-clone-copy" style="display: none;">

							<div class="vcm-params-block vcm-vrbo-bedroom">

								<div class="vcm-param-container">
									<div class="vcm-param-label"><?php echo JText::_('VCMBCAHIMGTAG84'); ?></div>
									<div class="vcm-param-setting">
										<button type="button" class="btn btn-danger" onclick="vcmRemoveBedroom(this);"><?php VikBookingIcons::e('times-circle'); ?></button>
									</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label"><?php echo JText::_('VCMTACHOTELAMENITIES'); ?></div>
									<div class="vcm-param-setting">
										<select data-buildname="listing[unit][bedrooms][%d][amenities][]" class="vcm-render-multi-select vcm-listing-editable" multiple="multiple">
										<?php
										foreach ($bedroom_amenity_values as $bedroom_amenity_code => $bedroom_amenity_name) {
											?>
											<option value="<?php echo $bedroom_amenity_code; ?>"><?php echo $bedroom_amenity_name; ?></option>
											<?php
										}
										?>
										</select>
										<span class="vcm-param-setting-comment">Amenities associated with the bedroom.</span>
									</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label"><?php echo JText::_('VCMRESLOGSTYPE'); ?></div>
									<div class="vcm-param-setting">
										<select data-buildname="listing[unit][bedrooms][%d][roomSubType]">
										<?php
										foreach ($bedroom_types as $bedroom_type_code => $bedroom_type_name) {
											?>
											<option value="<?php echo $bedroom_type_code; ?>"><?php echo $bedroom_type_name; ?></option>
											<?php
										}
										?>
										</select>
										<span class="vcm-param-setting-comment">Sub-category for the bedroom.</span>
									</div>
								</div>

							</div>

						</div>

						<?php
						$unit_amenities = is_object($unit) && isset($unit->featureValues) && is_array($unit->featureValues) ? $unit->featureValues : [];
						$active_unit_amenity_codes = [];
						foreach ($unit_amenities as $rak => $unit_amenity) {
							if (is_object($unit_amenity) && isset($unit_amenity->unitFeatureName)) {
								// push amenity code to be disabled, as no duplicate values are allowed
								$active_unit_amenity_codes[] = $unit_amenity->unitFeatureName;
							}
						}
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMTACHOTELAMENITIES'); ?></div>
							<div class="vcm-param-setting">
								<div class="btn-group-inline">
									<select id="vcm-unit-amenities-dropdown-list" class="vcm-listing-editable vcm-multi-select">
										<option></option>
									<?php
									$amenity_group = null;
									foreach ($vrbo_unit_amenities as $amenity_code => $amenity_data) {
										if ($amenity_group != $amenity_data['group']) {
											if (!is_null($amenity_group)) {
												// close previous node
												echo '</optgroup>' . "\n";
											}
											// open new node
											echo '<optgroup label="' . $this->escape($amenity_data['group']) . '">' . "\n";
											// update current group
											$amenity_group = $amenity_data['group'];
										}
										?>
										<option value="<?php echo $this->escape($amenity_code); ?>"<?php echo in_array($amenity_code, $active_unit_amenity_codes) ? ' disabled' : ''; ?>><?php echo $amenity_data['name']; ?></option>
										<?php
									}
									if (!is_null($amenity_group) && $amenity_group == $amenity_data['group']) {
										// close last node
										echo '</optgroup>' . "\n";
									}
									?>
									</select>
									<button type="button" class="btn vcm-config-btn" onclick="vcmAddUnitAmenity();"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::_('VCMBCAHADD'); ?></button>
								</div>
								<span class="vcm-param-setting-comment">Select the unit amenities to add.</span>
							</div>
						</div>

						<div class="vcm-vrbounit-amenities">

						<?php
						foreach ($unit_amenities as $rak => $unit_amenity) {
							if (!is_object($unit_amenity) || !isset($unit_amenity->unitFeatureName)) {
								// invalid room amenity object structure
								continue;
							}
							$feature_count = isset($unit_amenity->count) ? (int)$unit_amenity->count : 1;
							$feature_count = $feature_count < 1 ? 1 : $feature_count;
							?>
							<div class="vcm-params-block vcm-vrbounit-amenity">

								<div class="vcm-param-container">
									<div class="vcm-param-label">
										<strong><?php echo isset($vrbo_unit_amenities[$unit_amenity->unitFeatureName]) ? $vrbo_unit_amenities[$unit_amenity->unitFeatureName]['name'] : $unit_amenity->unitFeatureName; ?></strong>
									<?php
									if (isset($vrbo_unit_amenities[$unit_amenity->unitFeatureName]) && !empty($vrbo_unit_amenities[$unit_amenity->unitFeatureName]['group'])) {
										?>
										<span class="vcm-param-setting-comment"><?php echo $vrbo_unit_amenities[$unit_amenity->unitFeatureName]['group']; ?></span>
										<?php
									}
									?>
									</div>
									<div class="vcm-param-setting">
										<input type="hidden" name="listing[unit][featureValues][<?php echo $rak; ?>][unitFeatureName]" data-buildname="listing[unit][featureValues][%d][unitFeatureName]" class="vcm-hidden-disabled vcm-vrbo-amenity-code" value="<?php echo $this->escape($unit_amenity->unitFeatureName); ?>" onchange="vcmEnableUnitAmenities();" />
										<button type="button" class="btn btn-danger" onclick="vcmRemoveUnitAmenity(this);"><?php VikBookingIcons::e('times-circle'); ?></button>
									</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label"><?php echo JText::_('VCMBCAHQUANTITY'); ?></div>
									<div class="vcm-param-setting">
										<input type="number" name="listing[unit][featureValues][<?php echo $rak; ?>][count]" data-buildname="listing[unit][featureValues][%d][count]" min="1" max="99" value="<?php echo $feature_count; ?>" onchange="vcmEnableUnitAmenities();" />
									</div>
								</div>

							</div>
							<?php
						}
						?>

						</div>
						<?php
						// unit safety feature amenities
						$unit_safety_amenities = is_object($unit) && isset($unit->safetyFeatureValues) && is_array($unit->safetyFeatureValues) ? $unit->safetyFeatureValues : [];
						$active_unit_safety_amenity_codes = [];
						foreach ($unit_safety_amenities as $rak => $unit_safety_amenity) {
							if (is_object($unit_safety_amenity) && isset($unit_safety_amenity->safetyFeatureName)) {
								// push amenity code to be disabled, as no duplicate values are allowed
								$active_unit_safety_amenity_codes[] = $unit_safety_amenity->safetyFeatureName;
							}
						}
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-label">Safety Amenities</div>
							<div class="vcm-param-setting">
								<div class="btn-group-inline">
									<select id="vcm-unit-safety-amenities-dropdown-list" class="vcm-listing-editable vcm-multi-select">
										<option></option>
									<?php
									foreach ($vrbo_unit_safety_amenities as $amenity_code => $amenity_data) {
										?>
										<option value="<?php echo $this->escape($amenity_code); ?>"<?php echo in_array($amenity_code, $active_unit_safety_amenity_codes) ? ' disabled' : ''; ?>><?php echo $amenity_data['name']; ?></option>
										<?php
									}
									?>
									</select>
									<button type="button" class="btn vcm-config-btn" onclick="vcmAddUnitSafetyAmenity();"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::_('VCMBCAHADD'); ?></button>
								</div>
								<span class="vcm-param-setting-comment">Select the unit safety amenities to add.</span>
							</div>
						</div>

						<div class="vcm-vrbounit-safety-amenities">

						<?php
						foreach ($unit_safety_amenities as $rak => $unit_safety_amenity) {
							if (!is_object($unit_safety_amenity) || !isset($unit_safety_amenity->safetyFeatureName)) {
								// invalid room amenity object structure
								continue;
							}

							$feature_content_supported = false;
							$feature_content = '';
							$feature_content_type = '';
							if (isset($vrbo_unit_safety_amenities[$unit_safety_amenity->safetyFeatureName]) && !empty($vrbo_unit_safety_amenities[$unit_safety_amenity->safetyFeatureName]['ctype'])) {
								$feature_content_supported = true;
							}
							if ($feature_content_supported) {
								$def_feature_ctype = $vrbo_unit_safety_amenities[$unit_safety_amenity->safetyFeatureName]['ctype'][0];
								$feature_content = isset($unit_safety_amenity->content) ? $unit_safety_amenity->content : '';
								$feature_content_type = isset($unit_safety_amenity->content_type) ? $unit_safety_amenity->content_type : $def_feature_ctype;
							}
							?>
							<div class="vcm-params-block vcm-vrbounit-safety-amenity">

								<div class="vcm-param-container">
									<div class="vcm-param-label">
										<strong><?php echo isset($vrbo_unit_safety_amenities[$unit_safety_amenity->safetyFeatureName]) ? $vrbo_unit_safety_amenities[$unit_safety_amenity->safetyFeatureName]['name'] : $unit_safety_amenity->safetyFeatureName; ?></strong>
									</div>
									<div class="vcm-param-setting">
										<input type="hidden" name="listing[unit][safetyFeatureValues][<?php echo $rak; ?>][safetyFeatureName]" data-buildname="listing[unit][safetyFeatureValues][%d][safetyFeatureName]" class="vcm-hidden-disabled vcm-vrbo-amenity-code" value="<?php echo $this->escape($unit_safety_amenity->safetyFeatureName); ?>" onchange="vcmEnableUnitSafetyAmenities();" />
										<button type="button" class="btn btn-danger" onclick="vcmRemoveUnitSafetyAmenity(this);"><?php VikBookingIcons::e('times-circle'); ?></button>
									</div>
								</div>

							<?php
							if ($feature_content_supported) {
								?>
								<div class="vcm-param-container">
									<div class="vcm-param-label"><?php echo ucfirst($feature_content_type); ?></div>
									<div class="vcm-param-setting">
										<input type="hidden" name="listing[unit][safetyFeatureValues][<?php echo $rak; ?>][ctype]" data-buildname="listing[unit][safetyFeatureValues][%d][ctype]" class="vcm-hidden-disabled" value="<?php echo $feature_content_type; ?>" />
										<textarea name="listing[unit][safetyFeatureValues][<?php echo $rak; ?>][content]" data-buildname="listing[unit][safetyFeatureValues][%d][content]" onchange="vcmEnableUnitSafetyAmenities();"><?php echo $this->escape($feature_content); ?></textarea>
									</div>
								</div>
								<?php
							}
							?>

							</div>
							<?php
						}
						?>

						</div>
						<?php
					}
					?>

					</div>
				</div>
			</fieldset>

			<fieldset class="adminform vcm-listings-listing-wrap" data-landto="photos">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('camera'); ?> <?php echo JText::_('VCM_MNGLISTING_PHOTOS'); ?></legend>
					<div class="vcm-params-container">

					<?php
					if (!$is_editing) {
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-setting">
								<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_MNGLISTING_PHOTOS_ONLYEDIT'); ?></span>
							</div>
						</div>
						<?php
					} else {
						// display media field and caption text for uploading a new photo
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMUPLOADPHOTOS'); ?></div>
							<div class="vcm-param-setting">
							<?php
							if ($vbo_app !== false && method_exists($vbo_app, 'getMediaField')) {
								// media field is only supported by recent VBO versions
								echo $vbo_app->getMediaField('listing[_newphoto][url]', null, array('multiple' => false, 'id' => "vrbo-add-photo-file"));
							} else {
								echo 'Media field for file upload not supported. Update your plugins!';
							}
							?>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCM_PHOTO_CAPTION'); ?></div>
							<div class="vcm-param-setting">
								<input type="text" name="listing[_newphoto][caption]" value="" class="vcm-listing-editable" />
							</div>
						</div>
						<?php
						// display current photos, if any
						$vbo_photos = [];
						$current_photos = $listing->get('images', []);

						if (!$current_photos) {
							// attempt to read the photos from the VBO room
							$vbo_mainimg  = $vbo_listing->get('img', '');
							$vbo_moreimgs = explode(';;', $vbo_listing->get('moreimgs', ''));
							$vbo_captions = json_decode($vbo_listing->get('imgcaptions', '[]'), true);
							$vbo_captions = is_array($vbo_captions) ? $vbo_captions : [];
							if (!empty($vbo_mainimg)) {
								$vbo_photos[] = [
									'url' 	  => VBO_SITE_URI . 'resources/uploads/' . $vbo_mainimg,
									'caption' => '',
								];
							}
							foreach ($vbo_moreimgs as $k => $vbo_moreimg) {
								if (empty($vbo_moreimg)) {
									continue;
								}
								$vbo_photos[] = [
									'url' 	  => VBO_SITE_URI . 'resources/uploads/big_' . $vbo_moreimg,
									'thumb'	  => VBO_SITE_URI . 'resources/uploads/thumb_' . $vbo_moreimg,
									'caption' => (isset($vbo_captions[$k]) ? $vbo_captions[$k] : ''),
								];
							}

							// use the VBO photos by default
							$current_photos = $vbo_photos;
						}

						?>
						<div class="vcm-airbphotos-gallery-thumbs-inner vcm-vrbo-gallery-thumbs-inner">
						<?php
						$parsed_photo_ids = [];
						foreach ($current_photos as $k => $list_photo) {
							// wrap listing photo object into a JObject
							$photo = new JObject($list_photo);

							$large_url = $photo->get('url', '');
							$small_url = $photo->get('thumb', '');

							// photo file name from URL
							$fname = basename($large_url);
							$fname = preg_replace("/[^a-z0-9\-\_]/i", '', strtolower($fname));

							$photo_id = $photo->get('id', $fname);
							$caption = $photo->get('caption', '');

							// avoid duplicate photo IDs
							while (in_array($photo_id, $parsed_photo_ids)) {
								$photo_id = rand() . '-' . $photo_id;
							}

							// push photo ID used
							$parsed_photo_ids[] = $photo_id;
							?>
							<div class="vcm-airbphotos-gallery-thumb">
								<div class="vcm-airbphotos-gallery-thumb-inner">
									<div class="vcm-airbphotos-gallery-thumb-img">
										<img src="<?php echo !empty($small_url) ? $small_url : $large_url; ?>" class="vcm-airbphotos-img" data-large-url="<?php echo !empty($large_url) ? $large_url : $small_url; ?>" data-caption="<?php echo $this->escape($caption); ?>" data-propgallery="<?php echo $this->listing->id; ?>" data-index="<?php echo $k; ?>" />
										<input type="hidden" class="vcm-hidden-inp-photo-id" name="listing[images][<?php echo $k; ?>][id]" value="<?php echo $photo_id; ?>" />
										<input type="hidden" class="vcm-hidden-inp-photo-url" name="listing[images][<?php echo $k; ?>][url]" value="<?php echo $this->escape($large_url); ?>" />
										<input type="hidden" class="vcm-hidden-inp-photo-caption" name="listing[images][<?php echo $k; ?>][caption]" value="<?php echo $this->escape($caption); ?>" />
									</div>
									<div class="vcm-airbphotos-gallery-thumb-bottom">
										<div class="vcm-airbphotos-gallery-thumb-editimg">
											<button type="button" class="btn btn-primary" onclick="vcmEditPhotoCaption('<?php echo $k; ?>');"><?php VikBookingIcons::e('edit'); ?></button>
										</div>
										<div class="vcm-airbphotos-gallery-thumb-rmimg">
											<button type="button" class="btn btn-danger" onclick="vcmRemovePhoto(this);"><?php VikBookingIcons::e('trash'); ?></a>
										</div>
									</div>
								</div>
							</div>
							<?php
						}
						?>
						</div>
						<?php


					}
					?>

					</div>
				</div>

				<script type="text/javascript">
					// gallery params
					var vcmFxParams = {
						sourceAttr: 'data-large-url',
						captionSelector: 'self',
						captionType: 'data',
						captionsData: 'caption',
						captionClass: 'vcm-photo-caption-active',
					};

					jQuery(function() {

						// sortable photos
						jQuery('.vcm-airbphotos-gallery-thumbs-inner').sortable({
							items: '.vcm-airbphotos-gallery-thumb',
							helper: 'clone',
							update: function(event, ui) {
								// we need to enable all hidden fields, or the new sorting won't be applied
								jQuery(this).find('input[type="hidden"]:disabled').prop('disabled', false);
							},
						});
						jQuery('.vcm-airbphotos-gallery-thumbs-inner').disableSelection();

						// photo gallery
						window['vcmFxGallery'] = jQuery('.vcm-airbphotos-img').vikFxGallery(vcmFxParams);
					});

					function vcmEditPhotoCaption(index) {
						var current_caption = jQuery('.vcm-airbphotos-img[data-index="' + index + '"]').attr('data-caption');
						var new_caption = prompt(Joomla.JText._('VCM_PHOTO_CAPTION'), current_caption);
						if (new_caption != null) {
							jQuery('.vcm-airbphotos-img[data-index="' + index + '"]').attr('data-caption', new_caption).data('caption', new_caption);
							// update hidden input field
							var photo_cont_elem = jQuery('.vcm-airbphotos-img[data-index="' + index + '"]').parent();
							photo_cont_elem.find('.vcm-hidden-inp-photo-caption').val(new_caption);
							// make sure to enable all hidden input fields there
							photo_cont_elem.find('input[type="hidden"]:disabled').prop('disabled', false);
						}
					}

					function vcmRemovePhoto(elem) {
						if (confirm(Joomla.JText._('VCMREMOVECONFIRM'))) {
							jQuery(elem).closest('.vcm-airbphotos-gallery-thumb').remove();

							return true;
						}

						return false;
					}
				</script>

			</fieldset>

		</div>

		<div class="vcm-config-maintab-right">

			<fieldset class="adminform vcm-listings-listing-wrap" data-landto="license">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('id-badge'); ?> Registration and license details</legend>
					<div class="vcm-params-container">

						<div class="vcm-param-container">
							<div class="vcm-param-setting">
								<span class="vcm-param-setting-comment">Depending on the location of the property and the property type, this attribute <strong>may</strong> or <strong>may not</strong> be mandatory.</span>
							</div>
						</div>

						<div class="vcm-param-container vcm-param-container-tmp-disabled">
							<div class="vcm-param-label"><?php echo JText::_('VCM_CATEGORY'); ?></div>
							<div class="vcm-param-setting">
								<select name="listing[unit][propertyType]">
									<option value=""></option>
								<?php
								foreach (VCMVrboListing::getPropertyTypes() as $prop_type_code => $prop_type_name) {
									?>
									<option value="<?php echo $prop_type_code; ?>"<?php echo is_object($unit) && isset($unit->propertyType) && $unit->propertyType == $prop_type_code ? ' selected="selected"' : ''; ?>><?php echo $prop_type_name; ?></option>
									<?php
								}
								?>
								</select>
								<span class="vcm-param-setting-comment">Property Type. Used to specify the category of property as per jurisdiction requirements. Utilized for regulatory validation purposes.</span>
							</div>
						</div>

					<?php
					if ($listing_mapped) {
						// display button to download the requirements and status (Lodging Supply GraphQL API)
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-label">Regulatory Requirements and Status</div>
							<div class="vcm-param-setting">
								<a href="index.php?option=com_vikchannelmanager&task=vrbolst.download_listing_regulations&listing_id=<?php echo $this->listing->id; ?>" class="btn vcm-config-btn" onclick="return vcmShowLoading();"><?php VikBookingIcons::e('download'); ?> Download</a>
							</div>
						</div>
						<?php
						// get the regulatory requirements for this listing
						$regulation_requirements = $listing->get('regulation_requirements');
						// get the current registration details
						$registration_details = $listing->get('registration_details');
						// check if some regulation requirements were downloaded
						$has_regulation_requirements = (is_array($regulation_requirements) && $regulation_requirements);
						// check if we have a compliance status to display
						$compliance_status = $listing->get('compliance');
						if ($compliance_status && is_string($compliance_status)) {
							?>
						<div class="vcm-param-container">
							<div class="vcm-param-label">Compliance Status</div>
							<div class="vcm-param-setting">
								<?php echo $compliance_status; ?>
							</div>
						</div>
							<?php
						}
						// display the listing URL(s) and active status from the information returned by the Lodging Supply API
						$supply_information = (array)$listing->get('supply', []);
						if (isset($supply_information['active_status']) && isset($supply_information['urls'])) {
							?>
						<div class="vcm-param-container">
							<div class="vcm-param-label">Active Status</div>
							<div class="vcm-param-setting">
								<?php echo $supply_information['active_status'] ? JText::_('VCMPROMSTATUSACTIVE') : JText::_('VCMPROMSTATUSINACTIVE'); ?>
								<span class="vcm-param-setting-comment">Lodging Supply Active status.</span>
							</div>
						</div>
						<div class="vcm-param-container">
							<div class="vcm-param-label">Listing Platform URL(s)</div>
							<div class="vcm-param-setting">
							<?php
							foreach ($supply_information['urls'] as $supply_url) {
								?>
								<div>
									<a href="<?php echo $supply_url; ?>" target="_blank"><?php VikBookingIcons::e('external-link'); ?> <?php echo $supply_url; ?></a>
								</div>
								<?php
							}
							?>
							</div>
						</div>
							<?php
						}
						// display the regulation requirements for this listing, if any
						if ($has_regulation_requirements) {
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-setting">
								<span class="vcm-param-setting-comment">Select the appropriate category and property type, and eventually provide the required registration regulatory details.</span>
							</div>
						</div>
						<div class="vcm-vrbo-registration-numbers">
						<?php
							$has_active_regulation = false;
							foreach ($regulation_requirements as $kreg => $reg_req) {
								if (!is_object($reg_req) || empty($reg_req->isVacationRental)) {
									continue;
								}
								$is_regulation_active = false;
								if (!$has_active_regulation && is_object($registration_details)) {
									if (isset($registration_details->key) && $registration_details->key == $kreg) {
										// note that the key could be 0 for the first array index
										$is_regulation_active = true;
									} elseif (empty($registration_details->key) && isset($registration_details->category) && $registration_details->category == $reg_req->regulatoryCategory) {
										$is_regulation_active = true;
									}
								}
								$has_active_regulation = $has_active_regulation || $is_regulation_active;
								?>
							<div class="vcm-params-block" data-regkey="<?php echo $kreg; ?>">
								<div class="vcm-param-container">
									<div class="vcm-param-label">
										<?php echo $vbo_app->printYesNoButtons('listing[registration_details][' . $kreg . '][key]', JText::_('VCMYES'), JText::_('VCMNO'), (int)$is_regulation_active, 1, 0, 'vcmToggleRegistrationDetail(this.checked, \'' . $kreg . '\')'); ?>
									</div>
								</div>
								<div class="vcm-param-container">
									<div class="vcm-param-label">Category</div>
									<div class="vcm-param-setting">
										<?php echo $reg_req->regulatoryCategoryLabel; ?>
										<input type="hidden" name="listing[registration_details][<?php echo $kreg; ?>][category]" value="<?php echo JHtml::_('esc_attr', $reg_req->regulatoryCategory); ?>" />
									</div>
								</div>
								<?php
								if (isset($reg_req->qualifiedPropertyTypes) && is_array($reg_req->qualifiedPropertyTypes) && $reg_req->qualifiedPropertyTypes) {
								?>
								<div class="vcm-param-container">
									<div class="vcm-param-label">Property Type</div>
									<div class="vcm-param-setting">
										<?php echo ucfirst($reg_req->qualifiedPropertyTypes[0]->type); ?>
										<input type="hidden" name="listing[registration_details][<?php echo $kreg; ?>][type]" value="<?php echo JHtml::_('esc_attr', $reg_req->qualifiedPropertyTypes[0]->type); ?>" />
									</div>
								</div>
								<?php
									if (!empty($reg_req->qualifiedPropertyTypes[0]->subtype)) {
										?>
								<div class="vcm-param-container">
									<div class="vcm-param-label">Property Sub-Type</div>
									<div class="vcm-param-setting">
										<?php echo ucfirst($reg_req->qualifiedPropertyTypes[0]->subtype); ?>
										<input type="hidden" name="listing[registration_details][<?php echo $kreg; ?>][subtype]" value="<?php echo JHtml::_('esc_attr', $reg_req->qualifiedPropertyTypes[0]->subtype); ?>" />
									</div>
								</div>
										<?php
									}
								}
								if (isset($reg_req->registrationNumberRequirements) && is_array($reg_req->registrationNumberRequirements) && $reg_req->registrationNumberRequirements) {
									foreach ($reg_req->registrationNumberRequirements as $knumreq => $num_req) {
										if (!is_object($num_req) || empty($num_req->numberType)) {
											continue;
										}
										$regrecord_number = '';
										if ($is_regulation_active && isset($registration_details->regrecord) && is_array($registration_details->regrecord)) {
											if (isset($registration_details->regrecord[$knumreq]) && is_object($registration_details->regrecord[$knumreq])) {
												$regrecord_number = !empty($registration_details->regrecord[$knumreq]->number) ? $registration_details->regrecord[$knumreq]->number : '';
											}
										}
										?>
								<div class="vcm-param-container">
									<div class="vcm-param-label"><?php echo $num_req->numberTypeLabel; ?></div>
									<div class="vcm-param-setting">
										<input type="hidden" name="listing[registration_details][<?php echo $kreg; ?>][regrecord][<?php echo $knumreq; ?>][type]" value="<?php echo JHtml::_('esc_attr', $num_req->numberType); ?>" />
										<input type="text" name="listing[registration_details][<?php echo $kreg; ?>][regrecord][<?php echo $knumreq; ?>][number]" value="<?php echo JHtml::_('esc_attr', $regrecord_number); ?>" />
										<span class="vcm-param-setting-comment"><?php echo isset($num_req->isOptional) && !$num_req->isOptional ? 'This registration detail is required.' : 'This registration detail is optional.'; ?></span>
									</div>
								</div>
										<?php
									}
								}
								?>
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
			</fieldset>

		<?php
		// allow amenities management functions only when editing an existing listing
		if ($is_editing) {
			?>

			<fieldset class="adminform vcm-listings-listing-wrap" data-landto="amenities">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('icons'); ?> <?php echo JText::_('VCMBCARCROOMAMENITIES'); ?></legend>
					<div class="vcm-params-container">

						<?php
						$room_amenities = (array)$listing->get('featureValues', []);
						$active_amenity_codes = [];
						foreach ($room_amenities as $rak => $room_amenity) {
							if (is_object($room_amenity) && isset($room_amenity->listingFeatureName)) {
								// push amenity code to be disabled, as no duplicate values are allowed
								$active_amenity_codes[] = $room_amenity->listingFeatureName;
							}
						}
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-setting">
								<div class="btn-group-inline">
									<select id="vcm-room-amenities-dropdown-list" class="vcm-listing-editable vcm-multi-select">
										<option></option>
									<?php
									$amenity_group = null;
									foreach ($vrbo_listing_amenities as $amenity_code => $amenity_data) {
										if ($amenity_group != $amenity_data['group']) {
											if (!is_null($amenity_group)) {
												// close previous node
												echo '</optgroup>' . "\n";
											}
											// open new node
											echo '<optgroup label="' . $this->escape($amenity_data['group']) . '">' . "\n";
											// update current group
											$amenity_group = $amenity_data['group'];
										}
										?>
										<option value="<?php echo $this->escape($amenity_code); ?>"<?php echo in_array($amenity_code, $active_amenity_codes) ? ' disabled' : ''; ?>><?php echo $amenity_data['name']; ?></option>
										<?php
									}
									if (!is_null($amenity_group) && $amenity_group == $amenity_data['group']) {
										// close last node
										echo '</optgroup>' . "\n";
									}
									?>
									</select>
									<button type="button" class="btn vcm-config-btn" onclick="vcmAddRoomAmenity();"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::_('VCMBCAHADD'); ?></button>
								</div>
								<span class="vcm-param-setting-comment">Select the listing amenities to add.</span>
							</div>
						</div>

						<div class="vcm-vrbolisting-amenities">

						<?php
						foreach ($room_amenities as $rak => $room_amenity) {
							if (!is_object($room_amenity) || !isset($room_amenity->listingFeatureName)) {
								// invalid room amenity object structure
								continue;
							}
							?>
							<div class="vcm-params-block vcm-vrbolisting-amenity">

								<div class="vcm-param-container">
									<div class="vcm-param-label">
										<strong><?php echo isset($vrbo_listing_amenities[$room_amenity->listingFeatureName]) ? $vrbo_listing_amenities[$room_amenity->listingFeatureName]['name'] : $room_amenity->listingFeatureName; ?></strong>
									<?php
									if (isset($vrbo_listing_amenities[$room_amenity->listingFeatureName]) && !empty($vrbo_listing_amenities[$room_amenity->listingFeatureName]['group'])) {
										?>
										<span class="vcm-param-setting-comment"><?php echo $vrbo_listing_amenities[$room_amenity->listingFeatureName]['group']; ?></span>
										<?php
									}
									?>
									</div>
									<div class="vcm-param-setting">
										<input type="hidden" name="listing[featureValues][<?php echo $rak; ?>][listingFeatureName]" data-buildname="listing[featureValues][%d][listingFeatureName]" class="vcm-hidden-disabled vcm-vrbo-amenity-code" value="<?php echo $this->escape($room_amenity->listingFeatureName); ?>" onchange="vcmEnableRoomAmenities();" />
										<button type="button" class="btn btn-danger" onclick="vcmRemoveRoomAmenity(this);"><?php VikBookingIcons::e('times-circle'); ?></button>
									</div>
								</div>

							</div>
							<?php
						}
						?>

						</div>

					</div>
				</div>
			</fieldset>
			<?php
		}
		?>

			<fieldset class="adminform vcm-listings-listing-wrap" data-landto="location">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('map'); ?> <?php echo JText::_('VCMBCAHIMGTAG123'); ?></legend>
					<div class="vcm-params-container">

						<?php
						$vbo_room_params = $vbo_listing->get('params', '');
						$vbo_room_params = !empty($vbo_room_params) ? json_decode($vbo_room_params, true) : [];
						$vbo_room_params = is_array($vbo_room_params) ? $vbo_room_params : [];

						$vbo_lat  = '';
						$vbo_lng  = '';
						$vbo_addr = '';

						$geocoding = VikChannelManager::getGeocodingInstance();
						if ($geocoding) {
							// load the necessary assets
							$geocoding->loadAssets();
							// load default values
							$vbo_lat  = $geocoding->getRoomGeoParams($vbo_room_params, 'latitude', '');
							$vbo_lng  = $geocoding->getRoomGeoParams($vbo_room_params, 'longitude', '');
							$vbo_addr = $geocoding->getRoomGeoParams($vbo_room_params, 'address', '');
						}

						?>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMBCAHADDLINE'); ?>*</div>
							<div class="vcm-param-setting">
								<input type="text" id="vcm-listing-street" name="listing[addressLine1]" value="<?php echo $listing->get('addressLine1', $vbo_addr); ?>" class="vcm-listing-editable" size="40" maxlength="225" />
								<span class="vcm-param-setting-comment">Required line one of the physical street address.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMBCAHADDLINE'); ?> 2</div>
							<div class="vcm-param-setting">
								<input type="text" id="vcm-listing-street2" name="listing[addressLine2]" value="<?php echo $listing->get('addressLine2', ''); ?>" class="vcm-listing-editable" size="40" maxlength="225" />
								<span class="vcm-param-setting-comment">Optional line two of the physical street address.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMTACHOTELCOUNTRY'); ?>*</div>
							<div class="vcm-param-setting">
								<select name="listing[country]" id="vcm-listing-country" class="vcm-listing-editable">
									<option value="" data-c3code=""></option>
								<?php
								foreach ($this->countries as $country) {
									?>
									<option data-c3code="<?php echo $country['country_3_code']; ?>" value="<?php echo $country['country_2_code']; ?>"<?php echo $listing->get('country', '') == $country['country_2_code'] ? ' selected="selected"' : ''; ?>><?php echo $country['country_name']; ?></option>
									<?php
								}
								?>
								</select>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMTACHOTELCITY'); ?>*</div>
							<div class="vcm-param-setting">
								<input type="text" id="vcm-listing-city" name="listing[city]" value="<?php echo $listing->get('city', ''); ?>" class="vcm-listing-editable" size="40" maxlength="80" />
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMTACHOTELSTATE'); ?></div>
							<div class="vcm-param-setting">
								<select name="listing[stateOrProvince]" id="vcm-listing-state" data-stateset="<?php echo $listing->get('stateOrProvince', ''); ?>" class="vcm-listing-editable">
									<option value=""></option>
								</select>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMBCAHPOSCODE'); ?>*</div>
							<div class="vcm-param-setting">
								<input type="text" name="listing[postalCode]" value="<?php echo $listing->get('postalCode', ''); ?>" class="vcm-listing-editable" size="40" maxlength="50" />
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMTACHOTELLATITUDE'); ?>*</div>
							<div class="vcm-param-setting">
								<div class="btn-wrapper input-append">
									<input type="text" id="vcmhlat" name="listing[latitude]" value="<?php echo $listing->get('latitude', $vbo_lat); ?>" data-ftype="latitude" class="vcm-listing-editable" size="40" />
									<button type="button" class="btn vcm-config-btn vcm-get-coords" title="<?php echo htmlspecialchars(JText::_('VCM_YOUR_CURR_LOCATION')); ?>"><?php VikBookingIcons::e('location-arrow'); ?></button>
								</div>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMTACHOTELLONGITUDE'); ?>*</div>
							<div class="vcm-param-setting">
								<input type="text" id="vcmhlng" name="listing[longitude]" value="<?php echo $listing->get('longitude', $vbo_lng); ?>" data-ftype="longitude" class="vcm-listing-editable" size="40"/>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Show exact location</div>
							<div class="vcm-param-setting">
								<?php
								$show_exact_location = $listing->get('showExactLocation');
								$show_exact_location = $show_exact_location === true || $show_exact_location != 'false' ? true : false;
								?>
								<select name="listing[showExactLocation]" class="vcm-listing-editable">
									<option value="true"<?php echo $show_exact_location ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMYES'); ?></option>
									<option value="false"<?php echo !$show_exact_location ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMNO'); ?></option>
								</select>
								<span class="vcm-param-setting-comment">Whether the advertisement contains the exact location when travelers view a map of the listing location. It is recommended to enable this setting, and if disabled, an obfuscated map pin is shown with a different visual treatment to indicate that the pinned location is an approximation.</span>
							</div>
						</div>

						<?php
						if ($geocoding) {
							// check if latitude and longitude have been defined
							$list_lat = $listing->get('latitude', $vbo_lat);
							$list_lng = $listing->get('longitude', $vbo_lng);
							$valid_coords = (!empty($list_lat) && !empty($list_lng));
							?>
						<div class="vcm-map-wrapper" style="<?php echo !$valid_coords ? 'display: none;' : ''; ?>">
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
								title: jQuery('#vcm-listing-name').val()
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
							var start_lat = '<?php echo $listing->get('latitude', $vbo_lat); ?>';
							var start_lng = '<?php echo $listing->get('longitude', $vbo_lng); ?>';
							if (!start_lat || !start_lng) {
								start_lat = '43.7734385';
								start_lng = '11.2565501';
							}
							vcmInitGeoMap(start_lat, start_lng);

							// change event listener for street, city and country to invoke the Geocoder
							jQuery('#vcm-listing-street, #vcm-listing-city, #vcm-listing-country').change(function() {
								var street 	= jQuery('#vcm-listing-street').val();
								var city 	= jQuery('#vcm-listing-city').val();
								var country = jQuery('#vcm-listing-country').val();
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
								var listing_lat = jQuery('input[data-ftype="latitude"]').val();
								var listing_lng = jQuery('input[data-ftype="longitude"]').val();
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
										if (!listing_lat.length || !listing_lng.length) {
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
											title: jQuery('#vcm-listing-name').val()
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
										if (!jQuery('.vcm-map-wrapper').is(':visible')) {
											jQuery('.vcm-map-wrapper').fadeIn();
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
					<script type="text/javascript">
					/**
					 * Define the necessary listeners.
					 */
					jQuery(function() {

						/**
						 * We always allow the geolocation API to be used no matter if an interactive map is available.
						 */
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
									if (!jQuery('.vcm-map-wrapper').is(':visible')) {
										jQuery('.vcm-map-wrapper').fadeIn();
									}
								}
							}, function(err) {
								alert(`Error (${err.code}): ${err.message}`);
							});
						});

						// country selection
						jQuery('select#vcm-listing-country').on('change', function() {
							// reload state/province
							vcmReloadStates(jQuery(this).val());
						});

					<?php
					if ($is_editing && $listing->get('country')) {
						?>
						setTimeout(() => {
							// reload state/province
							vcmReloadStates('<?php echo $listing->get('country'); ?>');
						}, 200);
						<?php
					}
					?>

					});

					function vcmReloadStates(country_2_code) {
						var states_elem = jQuery('select#vcm-listing-state');

						// get the current state, if any
						var current_state = states_elem.attr('data-stateset');

						// unset the current states/provinces
						states_elem.html('');

						if (!country_2_code || !country_2_code.length) {
							return;
						}

						// make a request to load the states/provinces of the selected country
						VBOCore.doAjax(
							"<?php echo VikBooking::ajaxUrl('index.php?option=com_vikbooking&task=states.load_from_country'); ?>",
							{
								country_2_code: country_2_code,
								tmpl: "component"
							},
							(response) => {
								try {
									var obj_res = typeof response === 'string' ? JSON.parse(response) : response;
									if (!obj_res) {
										console.error('Unexpected JSON response', obj_res);
										return false;
									}

									// append empty value
									states_elem.append('<option value="">-----</option>');

									for (var i = 0; i < obj_res.length; i++) {
										// append state
										states_elem.append('<option value="' + obj_res[i]['state_2_code'] + '">' + obj_res[i]['state_name'] + '</option>');
									}

									if (current_state.length && states_elem.find('option[value="' + current_state + '"]').length) {
										// set the current value
										states_elem.val(current_state);
									}
								} catch(err) {
									console.error('could not parse JSON response', err, response);
								}
							},
							(error) => {
								console.error(error);
								alert(error.responseText);
							}
						);
					}
					</script>

					</div>
				</div>
			</fieldset>

			<fieldset class="adminform vcm-listings-listing-wrap" data-landto="lodging">
			<?php
			// get lodging object, if available
			$lodging_arr = (array)$listing->get('lodging', []);
			$lodging = new JObject($lodging_arr);
			?>
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('hand-paper'); ?> <?php echo JText::_('VCMBCAHPOLICIES') . ' (Lodging)'; ?></legend>
					<div class="vcm-params-container">

					<?php
					$def_rplan_id = $listing->get('def_rplan_id');
					if ($this->vbo_listing_rplans) {
						if (count($this->vbo_listing_rplans) > 1) {
							?>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMRARRATEPLAN') . ' (' . JText::_('VCMROOMSRELDEFRPLAN') . ')'; ?></div>
							<div class="vcm-param-setting">
								<select name="listing[def_rplan_id]" class="vcm-listing-editable">
								<?php
								foreach ($this->vbo_listing_rplans as $vbo_rplan) {
									$is_rplan_sel = ((empty($def_rplan_id) && stripos($vbo_rplan['name'], 'standard') !== false) || $vbo_rplan['idprice'] == $def_rplan_id);
									?>
									<option value="<?php echo $vbo_rplan['idprice']; ?>"<?php echo $is_rplan_sel ? ' selected="selected"' : ''; ?>><?php echo $vbo_rplan['name']; ?></option>
									<?php
								}
								?>
								</select>
								<span class="vcm-param-setting-comment">The default rate plan from which rates will be pulled by Vrbo.</span>
							</div>
						</div>
							<?php
						} else {
							?>
							<input type="hidden" name="listing[def_rplan_id]" value="<?php echo $this->vbo_listing_rplans[0]['idprice']; ?>" />
							<?php
						}
					}

					// let the PM choose if tourist taxes should be paid at the time of booking or at checkin
					$tourist_tax_pay_when = $listing->get('tourist_tax_pay_when', 'checkin');
					if ($listing_city_taxes) {
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMBCAHTAXTYPE1'); ?></div>
							<div class="vcm-param-setting">
								<select name="listing[tourist_tax_pay_when]" class="vcm-listing-editable">
									<option value="checkin"<?php echo $tourist_tax_pay_when == 'checkin' ? ' selected="selected"' : ''; ?>>At Check-in</option>
									<option value="booking"<?php echo $tourist_tax_pay_when == 'booking' ? ' selected="selected"' : ''; ?>>At time of booking (online)</option>
								</select>
								<span class="vcm-param-setting-comment">Choose if tourist taxes should be paid by the guests at the time of booking or at check-in.</span>
							</div>
						</div>
						<?php
					}
					?>

						<?php
						$acceptedPaymentForms = (array)$lodging->get('acceptedPaymentForms', []);
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCM_ACCEPTED_PAYM_FORMS'); ?></div>
							<div class="vcm-param-setting">
								<button type="button" class="btn vcm-config-btn" onclick="vcmAddAcceptedPayment();"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::_('VCMBCAHADD'); ?></button>
								<span class="vcm-param-setting-comment">The types of payments allowed for online bookings. At least one type is required, up to 100 combinations accepted.</span>
							</div>
						</div>

						<div class="vcm-vrbo-lodging-accepted-payments">
						<?php
						foreach ($acceptedPaymentForms as $apfk => $acc_payment) {
							if (is_object($acc_payment)) {
								$acc_payment = (array)$acc_payment;
							}
							if (!is_array($acc_payment) || empty($acc_payment['payment_type'])) {
								continue;
							}
							$say_accepted_payment_type = '';
							if (!strcasecmp($acc_payment['payment_type'], 'CARD')) {
								// card
								$say_payment_type = 'Card';
								$say_accepted_payment_type .= "<strong>{$say_payment_type}</strong> " . (!empty($acc_payment['card_code']) ? $acc_payment['card_code'] : '');
								$say_accepted_payment_type .= ' ' . (!empty($acc_payment['card_type']) ? $acc_payment['card_type'] : '');
							} else {
								// invoice
								$say_payment_type = 'Invoice';
								$say_accepted_payment_type .= "<strong>{$say_payment_type}</strong> " . (!empty($acc_payment['invoice_type']) ? $acc_payment['invoice_type'] : '');
								$say_accepted_payment_type .= '<br/>' . (!empty($acc_payment['payment_note']) ? $acc_payment['payment_note'] : '');
							}
							?>
							<div class="vcm-params-block vcm-vrbolisting-acceptedpayment">
								<div class="vcm-param-container">
									<div class="vcm-param-label">
										<?php echo $say_accepted_payment_type; ?>
									</div>
									<div class="vcm-param-setting">
										<button type="button" class="btn btn-danger" onclick="vcmRemoveAcceptedPayment(this);"><?php VikBookingIcons::e('times-circle'); ?></button>

										<input type="hidden" name="listing[lodging][acceptedPaymentForms][<?php echo $apfk; ?>][payment_type]" data-buildname="listing[lodging][acceptedPaymentForms][%d][payment_type]" value="<?php echo $this->escape($acc_payment['payment_type']); ?>" />
									<?php
									if (!strcasecmp($acc_payment['payment_type'], 'CARD')) {
										// card
										?>
										<input type="hidden" name="listing[lodging][acceptedPaymentForms][<?php echo $apfk; ?>][card_code]" data-buildname="listing[lodging][acceptedPaymentForms][%d][card_code]" value="<?php echo $this->escape($acc_payment['card_code']); ?>" />
										<input type="hidden" name="listing[lodging][acceptedPaymentForms][<?php echo $apfk; ?>][card_type]" data-buildname="listing[lodging][acceptedPaymentForms][%d][card_type]" value="<?php echo $this->escape($acc_payment['card_type']); ?>" />
										<?php
									} else {
										// invoice
										?>
										<input type="hidden" name="listing[lodging][acceptedPaymentForms][<?php echo $apfk; ?>][invoice_type]" data-buildname="listing[lodging][acceptedPaymentForms][%d][invoice_type]" value="<?php echo $this->escape($acc_payment['invoice_type']); ?>" />
										<input type="hidden" name="listing[lodging][acceptedPaymentForms][<?php echo $apfk; ?>][payment_note]" data-buildname="listing[lodging][acceptedPaymentForms][%d][payment_note]" value="<?php echo $this->escape($acc_payment['payment_note']); ?>" />
										<?php
									}
									?>
									</div>
								</div>
							</div>
							<?php
						}
						?>
						</div>

						<?php
						$bookingPolicy = $lodging->get('bookingPolicy', '');
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-label">Booking Policy</div>
							<div class="vcm-param-setting">
								<select name="listing[lodging][bookingPolicy]" class="vcm-listing-editable">
									<option value="INSTANT"<?php echo !$bookingPolicy || !strcasecmp($bookingPolicy, 'INSTANT') ? ' selected="selected"' : ''; ?>>Instant Booking</option>
									<option value="QUOTEHOLD"<?php echo !strcasecmp($bookingPolicy, 'QUOTEHOLD') ? ' selected="selected"' : ''; ?>>Quote and Hold</option>
								</select>
								<span class="vcm-param-setting-comment">With Instant Booking, the reservation is immediately confirmed and no action is required by the PM. With Quote and Hold, the reservation is not confirmed until the PM manually confirms the reservation.</span>
							</div>
						</div>

						<?php
						$cancellationPolicy  = (array)$lodging->get('cancellationPolicy', []);
						$canc_policy_policy  = !empty($cancellationPolicy['policy']) ? $cancellationPolicy['policy'] : '';
						$canc_policy_periods = !empty($cancellationPolicy['periods']) ? (array)$cancellationPolicy['periods'] : [];
						$canc_policy_descr   = '';
						$canc_policy_custom  = false;
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCM_TA_CANCELLATION_POLICY'); ?></div>
							<div class="vcm-param-setting">
								<select name="listing[lodging][cancellationPolicy][policy]" id="vcm-vrbolisting-cancpolicy-policy" class="vcm-listing-editable" onchange="vcmHandleCancPolicy(this.value);">
									<option value=""></option>
								<?php
								foreach (VCMVrboListing::getCancellationPolicyTypeValues() as $canc_policy_key => $canc_policy_data) {
									$policy_selected = ($canc_policy_policy == $canc_policy_key);
									if ($policy_selected) {
										$canc_policy_descr = $canc_policy_data['descr'];
										$canc_policy_custom = ($canc_policy_key == 'CUSTOM');
									}
									?>
									<option value="<?php echo $this->escape($canc_policy_key); ?>" data-descr="<?php echo $this->escape($canc_policy_data['descr']); ?>"<?php echo $policy_selected ? ' selected="selected"' : ''; ?>><?php echo $canc_policy_data['name']; ?></option>
									<?php
								}
								?>
								</select>
								<span class="vcm-param-setting-comment" id="vcm-vrbolisting-cancpolicy-descr"><?php echo $canc_policy_descr; ?></span>
							</div>
						</div>

						<div id="vcm-vrbolisting-cancpolicy-custom-periods" style="<?php echo !$canc_policy_custom ? 'display: none;' : ''; ?>">
						<?php
						$canc_fee = !empty($cancellationPolicy['cancellationFee']) ? (float)$cancellationPolicy['cancellationFee'] : '';
						?>
							<div class="vcm-param-container vcm-param-nested">
								<div class="vcm-param-label">Cancellation Fee</div>
								<div class="vcm-param-setting">
									<input type="number" name="listing[lodging][cancellationPolicy][cancellationFee]" value="<?php echo $canc_fee; ?>" min="0" step="any" class="vcm-listing-editable" />
									<span class="vcm-param-setting-comment">Optional fee that is deducted from refund if the traveler qualifies for a refund of any amount.</span>
								</div>
							</div>
						<?php
						for ($cpol_p = 0; $cpol_p < 3; $cpol_p++) {
							if (isset($canc_policy_periods[$cpol_p]) && !is_array($canc_policy_periods[$cpol_p])) {
								$canc_policy_periods[$cpol_p] = (array)$canc_policy_periods[$cpol_p];
							}
							$cur_nights_before = isset($canc_policy_periods[$cpol_p]) && isset($canc_policy_periods[$cpol_p]['nightsbefore']) ? (int)$canc_policy_periods[$cpol_p]['nightsbefore'] : '';
							$cur_refund_pcent  = isset($canc_policy_periods[$cpol_p]) && isset($canc_policy_periods[$cpol_p]['refundpcent']) ? (float)$canc_policy_periods[$cpol_p]['refundpcent'] : '';
							?>
							<div class="vcm-param-container vcm-param-nested">
								<div class="vcm-param-label">Cancellation Terms #<?php echo ($cpol_p + 1); ?></div>
								<div class="vcm-param-setting">
									<div>
										<span>Nights before check-in</span>
										<input type="number" name="listing[lodging][cancellationPolicy][periods][<?php echo $cpol_p; ?>][nightsbefore]" value="<?php echo $cur_nights_before; ?>" min="0" max="365" class="vcm-listing-editable" />
									</div>
									<div>
										<span>Refund %</span>
										<input type="number" name="listing[lodging][cancellationPolicy][periods][<?php echo $cpol_p; ?>][refundpcent]" value="<?php echo $cur_refund_pcent; ?>" min="0" max="100" step="any" class="vcm-listing-editable" />
									</div>
								</div>
							</div>
							<?php
						}
						?>
						</div>

						<div class="vcm-params-block">
							<?php
							// check-in/check-out times
							$checkin_hours  = 12;
							$checkin_mins   = 0;
							$checkout_hours = 10;
							$checkout_mins  = 0;
							$in_out_times   = VikBooking::getTimeOpenStore();
							if (is_array($in_out_times)) {
								$checkin_hours  = floor($in_out_times[0] / 3600);
								$checkin_secs   = $in_out_times[0] - ($checkin_hours * 3600);
								$checkin_mins   = floor($checkin_secs / 60);
								$checkin_mins   = $checkin_mins > 30 || $checkin_mins == 0 ? 0 : 30;
								$checkout_hours = floor($in_out_times[1] / 3600);
								$checkout_secs  = $in_out_times[1] - ($checkout_hours * 3600);
								$checkout_mins  = floor($checkout_secs / 60);
								$checkout_mins  = $checkout_mins > 30 || $checkout_mins == 0 ? 0 : 30;
							}

							// check if a custom check-in/check-out time was set for Vrbo
							$cust_checkin = $lodging->get('checkInTime', '');
							$cust_checkout = $lodging->get('checkOutTime', '');
							if (!empty($cust_checkin) && preg_match("/^[0-9]{2}:[0-9]{2}$/", $cust_checkin)) {
								$cust_checkin_parts = explode(':', $cust_checkin);
								$checkin_hours = (int)$cust_checkin_parts[0];
								$checkin_mins  = (int)$cust_checkin_parts[1];
							}
							if (!empty($cust_checkout) && preg_match("/^[0-9]{2}:[0-9]{2}$/", $cust_checkout)) {
								$cust_checkout_parts = explode(':', $cust_checkout);
								$checkout_hours = (int)$cust_checkout_parts[0];
								$checkout_mins  = (int)$cust_checkout_parts[1];
							}
							?>
							<div class="vcm-param-container">
								<div class="vcm-param-label"><?php echo JText::_('VCMPVIEWORDERSVBFOUR'); ?></div>
								<div class="vcm-param-setting">
									<select name="listing[lodging][checkInTime]" class="vcm-listing-editable">
									<?php
									for ($h = 0; $h < 24; $h++) {
										for ($m = 0; $m < 60; $m += 30) {
											$say_time = ($h < 10 ? "0{$h}" : $h) . ':' . ($m < 10 ? "0{$m}" : $m);
											?>
										<option value="<?php echo $say_time; ?>"<?php echo $h == $checkin_hours && $m == $checkin_mins ? ' selected="selected"' : ''; ?>><?php echo $say_time; ?></option>
											<?php
										}
									}
									?>
									</select>
								</div>
							</div>

							<div class="vcm-param-container">
								<div class="vcm-param-label"><?php echo JText::_('VCMPVIEWORDERSVBFIVE'); ?></div>
								<div class="vcm-param-setting">
									<select name="listing[lodging][checkOutTime]" class="vcm-listing-editable">
									<?php
									for ($h = 0; $h < 24; $h++) {
										for ($m = 0; $m < 60; $m += 30) {
											$say_time = ($h < 10 ? "0{$h}" : $h) . ':' . ($m < 10 ? "0{$m}" : $m);
											?>
										<option value="<?php echo $say_time; ?>"<?php echo $h == $checkout_hours && $m == $checkout_mins ? ' selected="selected"' : ''; ?>><?php echo $say_time; ?></option>
											<?php
										}
									}
									?>
									</select>
								</div>
							</div>
						</div>

						<div class="vcm-params-block">
							<div class="vcm-param-container">
								<div class="vcm-param-label"><?php echo JText::_('VCMADULTS') . ' (Max)'; ?></div>
								<div class="vcm-param-setting">
									<?php
									$max_adults = $lodging->get('maxAdults', $vbo_listing->get('toadult', 1));
									$max_adults = $max_adults < 1 ? 1 : $max_adults;
									?>
									<input type="number" class="vcm-listing-editable" name="listing[lodging][maxAdults]" value="<?php echo $max_adults; ?>" min="1" max="99" />
								</div>
							</div>

							<div class="vcm-param-container">
								<div class="vcm-param-label"><?php echo JText::_('VCMBCAHIMGTAG92') . ' (Max)'; ?></div>
								<div class="vcm-param-setting">
									<?php
									$max_guests = $lodging->get('maxGuests', $vbo_listing->get('totpeople', 1));
									$max_guests = $max_guests < 1 ? 1 : $max_guests;
									?>
									<input type="number" class="vcm-listing-editable" name="listing[lodging][maxGuests]" value="<?php echo $max_guests; ?>" min="1" max="99" />
								</div>
							</div>
						</div>

						<div class="vcm-params-block">
							<div class="vcm-param-container">
								<div class="vcm-param-label"><?php echo JText::_('VCMBCARCCRIBS'); ?></div>
								<div class="vcm-param-setting">
									<?php
									$children_allowed = $lodging->get('childrenAllowedRule');
									$children_allowed = $children_allowed === true || $children_allowed != 'false' ? true : false;
									?>
									<select name="listing[lodging][childrenAllowedRule]" class="vcm-listing-editable">
										<option value="true"<?php echo $children_allowed ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMYES'); ?></option>
										<option value="false"<?php echo !$children_allowed ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMNO'); ?></option>
									</select>
								</div>
							</div>

							<div class="vcm-param-container">
								<div class="vcm-param-label"><?php echo JText::_('PETS_ALLOWED'); ?></div>
								<div class="vcm-param-setting">
									<?php
									$pets_allowed = $lodging->get('petsAllowedRule');
									$pets_allowed = $pets_allowed === true || $pets_allowed != 'false' ? true : false;
									?>
									<select name="listing[lodging][petsAllowedRule]" class="vcm-listing-editable">
										<option value="true"<?php echo $pets_allowed ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMYES'); ?></option>
										<option value="false"<?php echo !$pets_allowed ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMNO'); ?></option>
									</select>
								</div>
							</div>

							<div class="vcm-param-container">
								<div class="vcm-param-label">Events allowed</div>
								<div class="vcm-param-setting">
									<?php
									$events_allowed = $lodging->get('eventsAllowedRule');
									$events_allowed = $events_allowed === true || $events_allowed != 'false' ? true : false;
									?>
									<select name="listing[lodging][eventsAllowedRule]" class="vcm-listing-editable">
										<option value="true"<?php echo $events_allowed ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMYES'); ?></option>
										<option value="false"<?php echo !$events_allowed ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMNO'); ?></option>
									</select>
								</div>
							</div>

							<div class="vcm-param-container">
								<div class="vcm-param-label">Smoking allowed</div>
								<div class="vcm-param-setting">
									<?php
									$smoking_allowed = $lodging->get('smokingAllowedRule');
									$smoking_allowed = $smoking_allowed === true || $smoking_allowed != 'false' ? true : false;
									?>
									<select name="listing[lodging][smokingAllowedRule]" class="vcm-listing-editable">
										<option value="true"<?php echo $smoking_allowed ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMYES'); ?></option>
										<option value="false"<?php echo !$smoking_allowed ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMNO'); ?></option>
									</select>
								</div>
							</div>
						</div>

						<div class="vcm-params-block">

							<div class="vcm-param-container">
								<div class="vcm-param-label"><?php echo JText::_('VCM_WEEKLY_DISCOUNTS') ?></div>
								<div class="vcm-param-setting">
									<?php
									$weekly_discount_amount = (int)$lodging->get('weekly_discount', 0);
									$weekly_discount_type 	= $lodging->get('weekly_discount_type', 'percent');
									?>
									<input type="number" name="listing[lodging][weekly_discount]" min="0" max="99999" value="<?php echo $weekly_discount_amount; ?>" />
									<select name="listing[lodging][weekly_discount_type]">
										<option value="percent"<?php echo $weekly_discount_type == 'percent' ? ' selected="selected"' : ''; ?>>%</option>
										<option value="amount"<?php echo $weekly_discount_type == 'amount' ? ' selected="selected"' : ''; ?>><?php echo $listing->get('currency', '$'); ?></option>
									</select>
									<span class="vcm-param-setting-comment">Defines a per-night discount to be applied for stays longer than or equal to 7 nights. Not supported when using the LOS Pricing Model.</span>
								</div>
							</div>

							<div class="vcm-param-container">
								<div class="vcm-param-label"><?php echo JText::_('VCM_MONTHLY_DISCOUNTS') ?></div>
								<div class="vcm-param-setting">
									<?php
									$monthly_discount_amount = (int)$lodging->get('monthly_discount', 0);
									$monthly_discount_type 	 = $lodging->get('monthly_discount_type', 'percent');
									?>
									<input type="number" name="listing[lodging][monthly_discount]" min="0" max="99999" value="<?php echo $monthly_discount_amount; ?>" />
									<select name="listing[lodging][monthly_discount_type]">
										<option value="percent"<?php echo $monthly_discount_type == 'percent' ? ' selected="selected"' : ''; ?>>%</option>
										<option value="amount"<?php echo $monthly_discount_type == 'amount' ? ' selected="selected"' : ''; ?>><?php echo $listing->get('currency', '$'); ?></option>
									</select>
									<span class="vcm-param-setting-comment">Defines a per-night discount to be applied for stays longer than or equal to 28 nights. Not supported when using the LOS Pricing Model.</span>
								</div>
							</div>

							<div class="vcm-param-container">
								<div class="vcm-param-label"><?php echo JText::_('VCM_MAXIMUM_LOS') ?></div>
								<div class="vcm-param-setting">
									<?php
									$listing_max_los = (int)$lodging->get('max_los', 0);
									?>
									<input type="number" name="listing[lodging][max_los]" min="0" max="1095" value="<?php echo $listing_max_los; ?>" />
									<span class="vcm-param-setting-comment">Overrides the default maximum number of nights of stay for this listing.</span>
								</div>
							</div>

						</div>

					</div>
				</div>
			</fieldset>

		<?php
		if ($is_editing) {
			?>
			<fieldset class="adminform vcm-listings-listing-wrap">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('exclamation-circle'); ?> <?php echo JText::_('VCM_LISTING'); ?></legend>
					<div class="vcm-params-container">

						<div class="vcm-param-container">
							<div class="vcm-param-setting">
								<a class="btn btn-danger" href="index.php?option=com_vikchannelmanager&task=vrbolst.delete_listing&listing_id=<?php echo $this->listing->id; ?>" onclick="return vcmConfirmDelete();"><?php VikBookingIcons::e('bomb'); ?> <?php echo JText::_('VCMBCAHDELETE'); ?></a>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-setting">
								<a class="btn btn-danger" href="index.php?option=com_vikchannelmanager&task=vrbolst.reset_listing&listing_id=<?php echo $this->listing->id; ?>" onclick="return vcmConfirmDelete();"><?php VikBookingIcons::e('broom'); ?> <?php echo JText::_('VCM_RESET_DATA'); ?></a>
							</div>
						</div>

					</div>
				</div>
			</fieldset>
			<?php
		}
		?>

		</div>

	</div>
<?php
if ($is_editing) {
	?>
	<input type="hidden" id="idroomota" name="idroomota" value="<?php echo $this->listing->id; ?>" />
	<?php
}
?>
	<input type="hidden" name="task" value="" />
</form>

<?php
if ($is_editing) {
	// display a floating button to scroll to the top of the page
?>
<div class="vcm-floating-scrolltop" style="display: none;">
	<div class="vcm-floating-scrolltop-inner">
		<button type="button" class="btn vcm-scrolltop-btn" id="vcm-scrolltop-trigger"><?php VikBookingIcons::e('arrow-up'); ?></button>
	</div>
</div>
<?php
}
?>

<a class="vcm-hidden-refresh-url" href="index.php?option=com_vikchannelmanager&view=vrbomnglisting&idroomota=%s" style="display: none;"></a>
<a class="vcm-hidden-list-url" href="index.php?option=com_vikchannelmanager&view=vrbolistings&loaded=1" style="display: none;"></a>

<div class="vcm-vrbolisting-html-helpers" style="display: none;">

	<div class="vcm-vrbolisting-addacceptedpayment-helper">

		<div class="vcm-params-container">

			<div class="vcm-param-container">
				<div class="vcm-param-label">Payment Type</div>
				<div class="vcm-param-setting">
					<select id="addacceptedpayment-paytype" onchange="vcmVrboPaymentTypeChanged(this.value);">
						<option value="CARD">Credit Card Payment</option>
						<option value="INVOICE">Invoice Payment</option>
					</select>
				</div>
			</div>

			<div class="vcm-param-container" data-paytype="CARD">
				<div class="vcm-param-label">Card Code</div>
				<div class="vcm-param-setting">
					<select id="addacceptedpayment-cardcode">
						<option value=""></option>
					<?php
					foreach (VCMVrboListing::getCardCodeTypeValues() as $card_key => $card_val) {
						?>
						<option value="<?php echo $this->escape($card_key); ?>"><?php echo $card_val; ?></option>
						<?php
					}
					?>
					</select>
				</div>
			</div>

			<div class="vcm-param-container" data-paytype="CARD">
				<div class="vcm-param-label">Card Type</div>
				<div class="vcm-param-setting">
					<select id="addacceptedpayment-cardtype">
						<option value="CREDIT">Credit</option>
						<option value="DEBIT">Debit</option>
						<option value="ALTERNATIVE">Alternative (Affirm)</option>
					</select>
				</div>
			</div>

			<div class="vcm-param-container" data-paytype="INVOICE" style="display: none;">
				<div class="vcm-param-label">Invoice Type</div>
				<div class="vcm-param-setting">
					<select id="addacceptedpayment-invoicetype">
						<option value=""></option>
					<?php
					foreach (VCMVrboListing::getPaymentInvoiceMethodTypeValues() as $card_key => $card_val) {
						?>
						<option value="<?php echo $this->escape($card_key); ?>"><?php echo $card_val; ?></option>
						<?php
					}
					?>
					</select>
				</div>
			</div>

			<div class="vcm-param-container" data-paytype="INVOICE" style="display: none;">
				<div class="vcm-param-label">Payment Note</div>
				<div class="vcm-param-setting">
					<textarea id="addacceptedpayment-invoicenote" maxlength="500"></textarea>
				</div>
			</div>

		</div>

	</div>

</div>

<script type="text/javascript">
var vcm_vrbo_listing_amenities = <?php echo json_encode($vrbo_listing_amenities); ?>;
var vcm_vrbo_unit_amenities = <?php echo json_encode($vrbo_unit_amenities); ?>;
var vcm_vrbo_unit_safety_amenities = <?php echo json_encode($vrbo_unit_safety_amenities); ?>;

/* Display Loading Overlay */
function vcmShowLoading() {
	jQuery(".vcm-loading-overlay").show();
	// use the return value in case it's used for links
	return true;
}

/* Hide Loading Overlay */
function vcmStopLoading() {
	jQuery(".vcm-loading-overlay").hide();
	// use the return value in case it's used for links
	return true;
}

/* Handle some requests through AJAX */
Joomla.submitbutton = function(task) {
	if (task == 'vrbolst.savelisting' || task == 'vrbolst.updatelisting' || task == 'vrbolst.updatelisting_stay') {
		// submit form to controller
		vcmDoSaving(task);

		// exit
		return false;
	}
	// other buttons can submit the form normally
	Joomla.submitform(task, document.adminForm);
}

/* Handle the request through AJAX */
function vcmDoSaving(task) {
	// display loading overlay
	vcmShowLoading();
	// get form values
	var qstring = jQuery('#adminForm').serialize();
	// make sure the task is not set again, or the good one will go lost.
	qstring = qstring.replace('&task=', '&');
	// ajax base URL
	var ajax_base_url = "<?php echo VCMFactory::getPlatform()->getUri()->ajax('index.php?option=com_vikchannelmanager&task=&aj=1&e4j_debug=' . VikRequest::getInt('e4j_debug', 0, 'request')); ?>";
	// make the ajax request to the controller
	var jqxhr = jQuery.ajax({
		type: "POST",
		url: ajax_base_url.replace('task=&', 'task=' + task + '&'),
		data: qstring
	}).done(function(res) {
		vcmStopLoading();
		try {
			var obj_res = typeof res === 'string' ? JSON.parse(res) : res;
			if (obj_res.hasOwnProperty('ok') && obj_res.hasOwnProperty('id')) {
				// success
				alert(Joomla.JText._('MSG_BASE_SUCCESS') + '!');
				// all tasks (new/update) will attach the listing id to the response
				var reload_url = jQuery('.vcm-hidden-refresh-url').attr('href');
				reload_url = reload_url.replace('%s', obj_res['id']);
				if (task == 'vrbolst.updatelisting') {
					// navigate to manage listings page (save & close)
					document.location.href = jQuery('.vcm-hidden-list-url').attr('href');
				} else {
					// navigate to the same page to show the reloaded/newly-created data
					document.location.href = reload_url;
				}
			} else if (obj_res.hasOwnProperty('warning')) {
				// warning
				var warning_mess = Joomla.JText._('MSG_BASE_WARNING_BOOKING_RAR');
				warning_mess = warning_mess.replace('%s', obj_res['warning']);
				alert(warning_mess);
			} else if (obj_res.hasOwnProperty('error')) {
				// error
				alert(obj_res['error']);
			} else {
				// generic error
				alert('Invalid response.');
			}			
		} catch(err) {
			console.error('Could not parse JSON response', err, res);
			alert('Could not parse JSON response.');
		}
	}).fail(function(err) {
		console.error(err);
		alert(err.responseText);
		vcmStopLoading();
	});

	return true;
}

/* Simple confirmation */
function vcmConfirmDelete() {
	if (confirm(Joomla.JText._('VCMREMOVECONFIRM'))) {
		return true;
	} else {
		return false;
	}
}

/**
 * Modularity over the bathrooms is impossible to be achieved, as that's a whole object for the listing.
 */
function vcmEnableBathroomFields() {
	jQuery('.vcm-vrbo-bathrooms').find('.vcm-param-container-tmp-disabled:visible').removeClass('vcm-param-container-tmp-disabled').find('input, select').prop('disabled', false);
}

/**
 * Every time a modification is made, the name attribute of any input element must be renamed.
 */
function vcmResetBathroomNaming() {
	jQuery('.vcm-vrbo-bathrooms').find('[data-buildname]').each(function(k, v) {
		var elem = jQuery(this);
		var buildname = elem.attr('data-buildname');
		var replacements = (buildname.match(/%d/g) || []).length;
		if (!replacements) {
			return;
		}
		// set proper bathroom index (first wildcard %d)
		var bathroom_elem = elem.closest('.vcm-vrbo-bathroom');
		var bathroom_index = jQuery('.vcm-vrbo-bathrooms').find('.vcm-vrbo-bathroom').index(bathroom_elem);
		buildname = buildname.replace('%d', bathroom_index);
		// set correct name attribute
		elem.attr('name', buildname);
	});
	// make sure to enable all input fields for the bathrooms
	vcmEnableBathroomFields();
}

/**
 * Adds a new bathroom.
 */
function vcmAddBathroom() {
	var wrapper = jQuery('.vcm-vrbo-bathrooms');
	var bathroom_elem = jQuery('.vcm-vrbo-bathroom-clone-copy').find('.vcm-vrbo-bathroom').clone(true, true);
	if (bathroom_elem.find('select.vcm-render-multi-select').length) {
		bathroom_elem.find('select.vcm-render-multi-select').select2();
	}
	wrapper.append(bathroom_elem);
	// update the name attributes of every element
	vcmResetBathroomNaming();
}

/**
 * Removes the clicked bathroom container.
 */
function vcmRemoveBathroom(elem) {
	jQuery(elem).closest('.vcm-vrbo-bathroom').remove();
	// update the name attributes of every element
	vcmResetBathroomNaming();
}

/**
 * Modularity over the bedrooms is impossible to be achieved, as that's a whole object for the listing.
 */
function vcmEnableBedroomFields() {
	jQuery('.vcm-vrbo-bedrooms').find('.vcm-param-container-tmp-disabled:visible').removeClass('vcm-param-container-tmp-disabled').find('input, select').prop('disabled', false);
}

/**
 * Every time a modification is made, the name attribute of any input element must be renamed.
 */
function vcmResetBedroomNaming() {
	jQuery('.vcm-vrbo-bedrooms').find('[data-buildname]').each(function(k, v) {
		var elem = jQuery(this);
		var buildname = elem.attr('data-buildname');
		var replacements = (buildname.match(/%d/g) || []).length;
		if (!replacements) {
			return;
		}
		// set proper bedroom index (first wildcard %d)
		var bedroom_elem = elem.closest('.vcm-vrbo-bedroom');
		var bedroom_index = jQuery('.vcm-vrbo-bedrooms').find('.vcm-vrbo-bedroom').index(bedroom_elem);
		buildname = buildname.replace('%d', bedroom_index);
		// set correct name attribute
		elem.attr('name', buildname);
	});
	// make sure to enable all input fields for the bedrooms
	vcmEnableBedroomFields();
}

/**
 * Adds a new bedroom.
 */
function vcmAddBedroom() {
	var wrapper = jQuery('.vcm-vrbo-bedrooms');
	var bedroom_elem = jQuery('.vcm-vrbo-bedroom-clone-copy').find('.vcm-vrbo-bedroom').clone(true, true);
	if (bedroom_elem.find('select.vcm-render-multi-select').length) {
		bedroom_elem.find('select.vcm-render-multi-select').select2();
	}
	wrapper.append(bedroom_elem);
	// update the name attributes of every element
	vcmResetBedroomNaming();
}

/**
 * Removes the clicked bedroom container.
 */
function vcmRemoveBedroom(elem) {
	jQuery(elem).closest('.vcm-vrbo-bedroom').remove();
	// update the name attributes of every element
	vcmResetBedroomNaming();
}

/**
 * Fires when the button to add a new room amenity is clicked.
 */
function vcmAddRoomAmenity() {
	var amenity_code = jQuery('#vcm-room-amenities-dropdown-list').val();
	if (!amenity_code) {
		alert(Joomla.JText._('VCM_PLEASE_SELECT'));
		return false;
	}
	// disable this newly added amenity from the select
	jQuery('#vcm-room-amenities-dropdown-list').find('option[value="' + amenity_code + '"]').prop('disabled', true);
	// unset the value from the select and trigger the change event
	jQuery('#vcm-room-amenities-dropdown-list').val('').trigger('change');
	// populate the amenity
	if (!vcm_vrbo_listing_amenities.hasOwnProperty(amenity_code)) {
		alert('Invalid amenity');
		return false;
	}
	var amenities_wrapper = jQuery('.vcm-vrbolisting-amenities');
	var amenity_html = '';
	amenity_html += '<div class="vcm-params-block vcm-vrbolisting-amenity">' + "\n";
	amenity_html += '<div class="vcm-param-container">' + "\n";
	amenity_html += '	<div class="vcm-param-label">' + "\n";
	amenity_html += '		<strong>' + vcm_vrbo_listing_amenities[amenity_code]['name'] + '</strong>' + "\n";
	if (vcm_vrbo_listing_amenities[amenity_code]['group']) {
		amenity_html += '	<span class="vcm-param-setting-comment">' + vcm_vrbo_listing_amenities[amenity_code]['group'] + '</span>' + "\n";
	}
	amenity_html += '	</div>' + "\n";
	amenity_html += '	<div class="vcm-param-setting">' + "\n";
	amenity_html += '		<input type="hidden" name="" data-buildname="listing[featureValues][%d][listingFeatureName]" value="' + amenity_code + '" class="vcm-vrbo-amenity-code" />' + "\n";
	amenity_html += '		<button type="button" class="btn btn-danger" onclick="vcmRemoveRoomAmenity(this);"><?php VikBookingIcons::e('times-circle'); ?></button>' + "\n";
	amenity_html += '	</div>' + "\n";
	amenity_html += '</div>' + "\n";
	amenity_html += '</div>' + "\n";
	// append amenity elements
	amenities_wrapper.append(amenity_html);
	// animate scroll to that position
	jQuery('html,body').animate({scrollTop: jQuery('.vcm-vrbolisting-amenity').last().offset().top - 40}, {duration: 400});
	// update the name attributes of every element
	vcmResetAmenitiesNaming();
}

/**
 * Removes a room amenity block.
 */
function vcmRemoveRoomAmenity(elem) {
	var amenity_block = jQuery(elem).closest('.vcm-vrbolisting-amenity');
	var amenity_code = amenity_block.find('input.vcm-vrbo-amenity-code').val();
	// enable in the select the amenity just removed
	jQuery('#vcm-room-amenities-dropdown-list').find('option[value="' + amenity_code + '"]').prop('disabled', false);
	// unset the value from the select and trigger the change event
	jQuery('#vcm-room-amenities-dropdown-list').val('').trigger('change');
	// remove block element
	amenity_block.remove();
	// update the name attributes of every element
	vcmResetAmenitiesNaming();
}

/**
 * Every time a modification is made, the name attribute of any input element must be renamed.
 */
function vcmResetAmenitiesNaming() {
	jQuery('.vcm-vrbolisting-amenities').find('[data-buildname]').each(function(k, v) {
		var elem = jQuery(this);
		var buildname = elem.attr('data-buildname');
		var replacements = (buildname.match(/%d/g) || []).length;
		if (!replacements) {
			return;
		}
		// set proper amenity index (first wildcard %d)
		var amenity_elem = elem.closest('.vcm-vrbolisting-amenity');
		var amenity_index = jQuery('.vcm-vrbolisting-amenities').find('.vcm-vrbolisting-amenity').index(amenity_elem);
		buildname = buildname.replace('%d', amenity_index);
		// set correct name attribute
		elem.attr('name', buildname);
	});
	// make sure to enable all input fields
	vcmEnableRoomAmenities();
}

/**
 * Fires when the button to add a new unit amenity is clicked.
 */
function vcmAddUnitAmenity() {
	var amenity_code = jQuery('#vcm-unit-amenities-dropdown-list').val();
	if (!amenity_code) {
		alert(Joomla.JText._('VCM_PLEASE_SELECT'));
		return false;
	}
	// disable this newly added amenity from the select
	jQuery('#vcm-unit-amenities-dropdown-list').find('option[value="' + amenity_code + '"]').prop('disabled', true);
	// unset the value from the select and trigger the change event
	jQuery('#vcm-unit-amenities-dropdown-list').val('').trigger('change');
	// populate the amenity
	if (!vcm_vrbo_unit_amenities.hasOwnProperty(amenity_code)) {
		alert('Invalid amenity');
		return false;
	}
	var amenities_wrapper = jQuery('.vcm-vrbounit-amenities');
	var amenity_html = '';
	amenity_html += '<div class="vcm-params-block vcm-vrbounit-amenity">' + "\n";
	amenity_html += '<div class="vcm-param-container">' + "\n";
	amenity_html += '	<div class="vcm-param-label">' + "\n";
	amenity_html += '		<strong>' + vcm_vrbo_unit_amenities[amenity_code]['name'] + '</strong>' + "\n";
	if (vcm_vrbo_unit_amenities[amenity_code]['group']) {
		amenity_html += '	<span class="vcm-param-setting-comment">' + vcm_vrbo_unit_amenities[amenity_code]['group'] + '</span>' + "\n";
	}
	amenity_html += '	</div>' + "\n";
	amenity_html += '	<div class="vcm-param-setting">' + "\n";
	amenity_html += '		<input type="hidden" name="" data-buildname="listing[unit][featureValues][%d][unitFeatureName]" value="' + amenity_code + '" class="vcm-vrbo-amenity-code" />' + "\n";
	amenity_html += '		<button type="button" class="btn btn-danger" onclick="vcmRemoveUnitAmenity(this);"><?php VikBookingIcons::e('times-circle'); ?></button>' + "\n";
	amenity_html += '	</div>' + "\n";
	amenity_html += '</div>' + "\n";
	amenity_html += '<div class="vcm-param-container">' + "\n";
	amenity_html += '	<div class="vcm-param-label">' + Joomla.JText._('VCMBCAHQUANTITY') + '</div>' + "\n";
	amenity_html += '	<div class="vcm-param-setting">' + "\n";
	amenity_html += '		<input type="number" name="" data-buildname="listing[unit][featureValues][%d][count]" min="1" max="99" value="1" onchange="vcmEnableUnitAmenities();" />' + "\n";
	amenity_html += '	</div>' + "\n";
	amenity_html += '</div>' + "\n";
	amenity_html += '</div>' + "\n";
	// append amenity elements
	amenities_wrapper.append(amenity_html);
	// animate scroll to that position
	jQuery('html,body').animate({scrollTop: jQuery('.vcm-vrbounit-amenity').last().offset().top - 40}, {duration: 400});
	// update the name attributes of every element
	vcmResetUnitAmenitiesNaming();
}

/**
 * Removes a unit amenity block.
 */
function vcmRemoveUnitAmenity(elem) {
	var amenity_block = jQuery(elem).closest('.vcm-vrbounit-amenity');
	var amenity_code = amenity_block.find('input.vcm-vrbo-amenity-code').val();
	// enable in the select the amenity just removed
	jQuery('#vcm-unit-amenities-dropdown-list').find('option[value="' + amenity_code + '"]').prop('disabled', false);
	// unset the value from the select and trigger the change event
	jQuery('#vcm-unit-amenities-dropdown-list').val('').trigger('change');
	// remove block element
	amenity_block.remove();
	// update the name attributes of every element
	vcmResetUnitAmenitiesNaming();
}

/**
 * Every time a modification is made, the name attribute of any input element must be renamed.
 */
function vcmResetUnitAmenitiesNaming() {
	jQuery('.vcm-vrbounit-amenities').find('[data-buildname]').each(function(k, v) {
		var elem = jQuery(this);
		var buildname = elem.attr('data-buildname');
		var replacements = (buildname.match(/%d/g) || []).length;
		if (!replacements) {
			return;
		}
		// set proper amenity index (first wildcard %d)
		var amenity_elem = elem.closest('.vcm-vrbounit-amenity');
		var amenity_index = jQuery('.vcm-vrbounit-amenities').find('.vcm-vrbounit-amenity').index(amenity_elem);
		buildname = buildname.replace('%d', amenity_index);
		// set correct name attribute
		elem.attr('name', buildname);
	});
	// make sure to enable all input fields
	vcmEnableUnitAmenities();
}

/**
 * Fires when the button to add a new unit safety amenity is clicked.
 */
function vcmAddUnitSafetyAmenity() {
	var amenity_code = jQuery('#vcm-unit-safety-amenities-dropdown-list').val();
	if (!amenity_code) {
		alert(Joomla.JText._('VCM_PLEASE_SELECT'));
		return false;
	}
	// disable this newly added amenity from the select
	jQuery('#vcm-unit-safety-amenities-dropdown-list').find('option[value="' + amenity_code + '"]').prop('disabled', true);
	// unset the value from the select and trigger the change event
	jQuery('#vcm-unit-safety-amenities-dropdown-list').val('').trigger('change');
	// populate the amenity
	if (!vcm_vrbo_unit_safety_amenities.hasOwnProperty(amenity_code)) {
		alert('Invalid amenity');
		return false;
	}
	var amenities_wrapper = jQuery('.vcm-vrbounit-safety-amenities');
	var amenity_html = '';
	amenity_html += '<div class="vcm-params-block vcm-vrbounit-safety-amenity">' + "\n";
	amenity_html += '<div class="vcm-param-container">' + "\n";
	amenity_html += '	<div class="vcm-param-label">' + "\n";
	amenity_html += '		<strong>' + vcm_vrbo_unit_safety_amenities[amenity_code]['name'] + '</strong>' + "\n";
	amenity_html += '	</div>' + "\n";
	amenity_html += '	<div class="vcm-param-setting">' + "\n";
	amenity_html += '		<input type="hidden" name="" data-buildname="listing[unit][safetyFeatureValues][%d][safetyFeatureName]" value="' + amenity_code + '" class="vcm-vrbo-amenity-code" />' + "\n";
	amenity_html += '		<button type="button" class="btn btn-danger" onclick="vcmRemoveUnitSafetyAmenity(this);"><?php VikBookingIcons::e('times-circle'); ?></button>' + "\n";
	amenity_html += '	</div>' + "\n";
	amenity_html += '</div>' + "\n";
	if (vcm_vrbo_unit_safety_amenities[amenity_code].hasOwnProperty('ctype')) {
		var def_feature_ctype = (vcm_vrbo_unit_safety_amenities[amenity_code]['ctype'][0] + '');
		var def_feature_ctype_nm = def_feature_ctype.charAt(0).toUpperCase() + def_feature_ctype.slice(1);
		amenity_html += '<div class="vcm-param-container">' + "\n";
		amenity_html += '	<div class="vcm-param-label">' + def_feature_ctype_nm + '</div>' + "\n";
		amenity_html += '	<div class="vcm-param-setting">' + "\n";
		amenity_html += '		<input type="hidden" name="" data-buildname="listing[unit][safetyFeatureValues][%d][ctype]" value="' + def_feature_ctype + '" />' + "\n";
		amenity_html += '		<textarea name="" data-buildname="listing[unit][safetyFeatureValues][%d][content]" onchange="vcmEnableUnitSafetyAmenities();"></textarea>' + "\n";
		amenity_html += '	</div>' + "\n";
		amenity_html += '</div>' + "\n";
	}
	amenity_html += '</div>' + "\n";
	// append amenity elements
	amenities_wrapper.append(amenity_html);
	// animate scroll to that position
	jQuery('html,body').animate({scrollTop: jQuery('.vcm-vrbounit-safety-amenity').last().offset().top - 40}, {duration: 400});
	// update the name attributes of every element
	vcmResetUnitSafetyAmenitiesNaming();
}

/**
 * Removes a unit safety amenity block.
 */
function vcmRemoveUnitSafetyAmenity(elem) {
	var amenity_block = jQuery(elem).closest('.vcm-vrbounit-safety-amenity');
	var amenity_code = amenity_block.find('input.vcm-vrbo-amenity-code').val();
	// enable in the select the amenity just removed
	jQuery('#vcm-unit-safety-amenities-dropdown-list').find('option[value="' + amenity_code + '"]').prop('disabled', false);
	// unset the value from the select and trigger the change event
	jQuery('#vcm-unit-safety-amenities-dropdown-list').val('').trigger('change');
	// remove block element
	amenity_block.remove();
	// update the name attributes of every element
	vcmResetUnitSafetyAmenitiesNaming();
}

/**
 * Every time a modification is made, the name attribute of any input element must be renamed.
 */
function vcmResetUnitSafetyAmenitiesNaming() {
	jQuery('.vcm-vrbounit-safety-amenities').find('[data-buildname]').each(function(k, v) {
		var elem = jQuery(this);
		var buildname = elem.attr('data-buildname');
		var replacements = (buildname.match(/%d/g) || []).length;
		if (!replacements) {
			return;
		}
		// set proper amenity index (first wildcard %d)
		var amenity_elem = elem.closest('.vcm-vrbounit-safety-amenity');
		var amenity_index = jQuery('.vcm-vrbounit-safety-amenities').find('.vcm-vrbounit-safety-amenity').index(amenity_elem);
		buildname = buildname.replace('%d', amenity_index);
		// set correct name attribute
		elem.attr('name', buildname);
	});
	// make sure to enable all input fields
	vcmEnableUnitSafetyAmenities();
}

/**
 * Enables all fields of the room amenities after a single change.
 */
function vcmEnableRoomAmenities() {
	jQuery('.vcm-vrbolisting-amenities').find('.vcm-param-container-tmp-disabled').removeClass('vcm-param-container-tmp-disabled').find('input, select, textarea').prop('disabled', false);
}

/**
 * Enables all fields of the unit amenities after a single change.
 */
function vcmEnableUnitAmenities() {
	jQuery('.vcm-vrbounit-amenities').find('.vcm-param-container-tmp-disabled').removeClass('vcm-param-container-tmp-disabled').find('input, select, textarea').prop('disabled', false);
}

/**
 * Enables all fields of the unit safety amenities after a single change.
 */
function vcmEnableUnitSafetyAmenities() {
	jQuery('.vcm-vrbounit-safety-amenities').find('.vcm-param-container-tmp-disabled').removeClass('vcm-param-container-tmp-disabled').find('input, select, textarea').prop('disabled', false);
}

/**
 * Enables all fields of the accepted payment types after a single change.
 */
function vcmEnableAcceptedPaymentFields() {
	jQuery('.vcm-vrbo-lodging-accepted-payments').find('.vcm-param-container-tmp-disabled').removeClass('vcm-param-container-tmp-disabled').find('input, select, textarea').prop('disabled', false);
}

/**
 * Enables a specific field from the given selector.
 */
function vcmEnableField(selector, set_val) {
	var elem = jQuery(selector);
	if (!elem || !elem.length) {
		return false;
	}
	// enable the field and never trigger the change event to avoid loops
	elem.prop('disabled', false).removeClass('vcm-hidden-disabled');
	// check if the parent element is graphically disabled
	if (elem.closest('.vcm-param-container-tmp-disabled').length) {
		elem.closest('.vcm-param-container-tmp-disabled').removeClass('vcm-param-container-tmp-disabled');
	}
	// check for callback function
	if (typeof set_val !== 'undefined') {
		// set calculated value from callback
		elem.val(set_val);
	}
}

/**
 * Helper function to convert from square meters to square feet or viceversa.
 */
function vcmSquareMetersFeet(val, type) {
	if (isNaN(val) || val == 0) {
		return '';
	}
	if (type === 'meters') {
		// convert square meters to square feet
		return Math.floor(val * 10.764);
	}
	// convert square feet to square meters
	return Math.floor(val / 10.764);
}

/**
 * Helper function to change the accepted payment form type.
 */
function vcmVrboPaymentTypeChanged(type) {
	var wrapper = jQuery('.vcm-vrbolisting-addacceptedpayment-helper');
	if (type == 'INVOICE') {
		wrapper.find('[data-paytype="CARD"]').hide();
		wrapper.find('[data-paytype="INVOICE"]').show();
	} else {
		wrapper.find('[data-paytype="INVOICE"]').hide();
		wrapper.find('[data-paytype="CARD"]').show();
	}
}

/**
 * Opens the modal to add a new accepted payment.
 */
function vcmAddAcceptedPayment() {
	// build the button to handle the adding of the values
	var btn_apply_accepted_payment_type = jQuery('<button></button>').attr('type', 'button').addClass('btn btn-success').html('<?php VikBookingIcons::e('plus-circle'); ?> Accepted Payment Type');
	btn_apply_accepted_payment_type.on('click', function() {
		// collect fields
		var paytype = jQuery('#addacceptedpayment-paytype').val();
		var cardcode = jQuery('#addacceptedpayment-cardcode').val();
		var cardtype = jQuery('#addacceptedpayment-cardtype').val();
		var invoicetype = jQuery('#addacceptedpayment-invoicetype').val();
		var invoicenote = jQuery('#addacceptedpayment-invoicenote').val();

		if (!paytype || (paytype != 'CARD' && paytype != 'INVOICE')) {
			alert('Invalid payment type. Please make a valid selection');
			return false;
		}

		if (paytype == 'CARD' && (!cardcode || !cardtype)) {
			alert('Please select the card code and type.');
			return false;
		}

		if (paytype == 'INVOICE' && !invoicenote) {
			alert('Please provide a note for the payment type.');
			return false;
		}

		// build label with values submitted
		var say_accepted_payment_type = '';
		if (paytype == 'CARD') {
			say_accepted_payment_type += '<strong>Card</strong> ' + cardcode + ' ' + cardtype;
		} else {
			say_accepted_payment_type += '<strong>Invoice</strong> ' + invoicetype + '<br/>' + invoicenote;
		}

		// build new HTML content
		var addacceptedpayment_html = '';
		addacceptedpayment_html += '<div class="vcm-params-block vcm-vrbolisting-acceptedpayment">' + "\n";
		addacceptedpayment_html += '	<div class="vcm-param-container">' + "\n";
		addacceptedpayment_html += '		<div class="vcm-param-label">' + say_accepted_payment_type + '</div>' + "\n";
		addacceptedpayment_html += '		<div class="vcm-param-setting">' + "\n";
		addacceptedpayment_html += '			<button type="button" class="btn btn-danger" onclick="vcmRemoveAcceptedPayment(this);"><?php VikBookingIcons::e('times-circle'); ?></button>' + "\n";
		addacceptedpayment_html += '			<input type="hidden" data-buildname="listing[lodging][acceptedPaymentForms][%d][payment_type]" value="' + paytype + '" />' + "\n";
		if (paytype == 'CARD') {
			addacceptedpayment_html += '			<input type="hidden" data-buildname="listing[lodging][acceptedPaymentForms][%d][card_code]" value="' + cardcode + '" />' + "\n";
			addacceptedpayment_html += '			<input type="hidden" data-buildname="listing[lodging][acceptedPaymentForms][%d][card_type]" value="' + cardtype + '" />' + "\n";
		} else {
			addacceptedpayment_html += '			<input type="hidden" data-buildname="listing[lodging][acceptedPaymentForms][%d][invoice_type]" value="' + invoicetype + '" />' + "\n";
			addacceptedpayment_html += '			<input type="hidden" data-buildname="listing[lodging][acceptedPaymentForms][%d][payment_note]" value="' + invoicenote + '" />' + "\n";
		}
		addacceptedpayment_html += '		</div>' + "\n";
		addacceptedpayment_html += '	</div>' + "\n";
		addacceptedpayment_html += '</div>' + "\n";

		// append values
		jQuery('.vcm-vrbo-lodging-accepted-payments').append(addacceptedpayment_html);
		// update the name attributes of every element
		vcmResetAcceptedPaymentsNaming();
		// dismiss the modal
		VBOCore.emitEvent('close-lodging-new-accepted-payment');
	});

	// render modal
	var modal_wrapper = VBOCore.displayModal({
		suffix: 'lodging-new-accepted-payment',
		title: 'New Accepted Payment Type',
		body_prepend: true,
		footer_right: btn_apply_accepted_payment_type,
		dismiss_event: 'close-lodging-new-accepted-payment',
		onDismiss: () => {
			// move HTML helper back to its location
			jQuery('.vcm-vrbolisting-addacceptedpayment-helper').appendTo(jQuery('.vcm-vrbolisting-html-helpers'));
			// trigger function to reset values for the next run
			vcmVrboPaymentTypeChanged('CARD');
		},
	});

	// append content to modal
	jQuery('.vcm-vrbolisting-addacceptedpayment-helper').appendTo(modal_wrapper);
}

/**
 * Removes an existing accepted payment type.
 */
function vcmRemoveAcceptedPayment(elem) {
	jQuery(elem).closest('.vcm-vrbolisting-acceptedpayment').remove();
	// update the name attributes of every element
	vcmResetAcceptedPaymentsNaming();
}

/**
 * Calculates the proper name attributes for a list of elements.
 */
function vcmResetAcceptedPaymentsNaming() {
	jQuery('.vcm-vrbo-lodging-accepted-payments').find('[data-buildname]').each(function(k, v) {
		var elem = jQuery(this);
		var buildname = elem.attr('data-buildname');
		var replacements = (buildname.match(/%d/g) || []).length;
		if (!replacements) {
			return;
		}
		// set proper accepted payment index (first wildcard %d)
		var accpayment_elem = elem.closest('.vcm-vrbolisting-acceptedpayment');
		var accpayment_index = jQuery('.vcm-vrbo-lodging-accepted-payments').find('.vcm-vrbolisting-acceptedpayment').index(accpayment_elem);
		buildname = buildname.replace('%d', accpayment_index);
		// set correct name attribute
		elem.attr('name', buildname);
	});
	// make sure to enable all input fields for the accepted payments
	vcmEnableAcceptedPaymentFields();
}

/**
 * Handles the selection of a specific cancellation policy.
 */
function vcmHandleCancPolicy(policy) {
	if (!policy) {
		// empty policy description
		jQuery('#vcm-vrbolisting-cancpolicy-descr').text('');
		// hide custom policy periods
		jQuery('#vcm-vrbolisting-cancpolicy-custom-periods').hide();
		return;
	}

	// set policy description
	jQuery('#vcm-vrbolisting-cancpolicy-descr').text(jQuery('#vcm-vrbolisting-cancpolicy-policy').find('option:selected').attr('data-descr'));

	if (policy == 'CUSTOM') {
		// show custom policy periods
		jQuery('#vcm-vrbolisting-cancpolicy-custom-periods').show();
	} else {
		// hide custom policy periods
		jQuery('#vcm-vrbolisting-cancpolicy-custom-periods').hide();
	}
}

/**
 * Toggles the scroll top floating button.
 */
function vcmHandleScroll() {
	if (jQuery(window).scrollTop() > 1000) {
		jQuery('.vcm-floating-scrolltop').fadeIn();
	} else {
		jQuery('.vcm-floating-scrolltop').hide();
	}
}

/**
 * Fires when a registration detail block is toggled.
 */
function vcmToggleRegistrationDetail(enabled, reg_key) {
	if (!enabled) {
		return;
	}

	jQuery('.vcm-vrbo-registration-numbers').find('.vcm-params-block[data-regkey]').each(function() {
		if (jQuery(this).attr('data-regkey') == reg_key) {
			// go to the next block
			return;
		}
		// empty any input of type text
		jQuery(this).find('input[type="text"]').val('');
		// empty any textarea
		jQuery(this).find('textarea').val('');
		// empty any select
		jQuery(this).find('select').val('');
		// untick any checkbox
		jQuery(this).find('input[type="checkbox"]').prop('checked', false);
	});
}

/**
 * Fires when the DOM is ready.
 */
jQuery(function() {
	// disable input fields when in edit mode and listing contents are sufficient
	if (jQuery('#idroomota').length && <?php echo isset($content_validated_info) && $content_validated_info[0] === true ? 'true' : 'false'; ?>) {
		jQuery('#adminForm').find('input:not([type="hidden"]):not(.vcm-listing-editable):not(.vbo-iostoggle-elem), input.vcm-hidden-disabled[type="hidden"], select:not(.vcm-listing-editable), textarea:not(.vcm-listing-editable)').prop('disabled', true).closest('.vcm-param-container').addClass('vcm-param-container-tmp-disabled').on('click', function() {
			if (!jQuery(this).hasClass('vcm-param-container-tmp-disabled') || jQuery(this).hasClass('vcm-listing-noedit')) {
				return;
			}
			jQuery(this).removeClass('vcm-param-container-tmp-disabled').find('input, select, textarea').prop('disabled', false).trigger('change');
		});
	} else {
		// even in creation mode we allow to set some information as optional
		jQuery('#adminForm').find('.vcm-param-container-tmp-disabled').on('click', function() {
			if (!jQuery(this).hasClass('vcm-param-container-tmp-disabled') || jQuery(this).hasClass('vcm-listing-noedit')) {
				return;
			}
			jQuery(this).removeClass('vcm-param-container-tmp-disabled').find('input, select, textarea').prop('disabled', false).trigger('change');
		}).find('input, select, textarea').prop('disabled', true);
	}

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
	document.addEventListener('scroll', VBOCore.debounceEvent(vcmHandleScroll, 500));

	// attempt to render tooltips
	if (jQuery.isFunction(jQuery.fn.tooltip)) {
		jQuery(".hasTooltip").tooltip();
	}
});
</script>

<?php
if (VikRequest::getInt('e4j_debug', 0, 'request')) {
	echo 'listing<pre>' . print_r($this->listing, true) . '</pre>';
}
