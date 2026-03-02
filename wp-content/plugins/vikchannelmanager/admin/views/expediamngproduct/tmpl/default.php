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

// check if we are in editing or new mode
$is_editing = count(get_object_vars($this->listing));

// wrap property data object into a JObject object
$property_data = new JObject($this->property_data);

// wrap listing object into a JObject object
$listing = new JObject($this->listing);

// expedia bed types
$expedia_bed_types = VCMExpediaProduct::getBedTypeSizes('types');
$expedia_bed_type_sizes = VCMExpediaProduct::getBedTypeSizes();
$expedia_bed_surcharges = VCMExpediaProduct::getBedTypeSizes('surcharges');

// expedia room amenities
$expedia_room_amenities = VCMExpediaProduct::getAmenityCodesData();

// lang vars for JS
JText::script('VCMREMOVECONFIRM');
JText::script('VCM_PLEASE_SELECT');
JText::script('MSG_BASE_SUCCESS');
JText::script('MSG_BASE_WARNING_BOOKING_RAR');
JText::script('VCMBCARCVALUE');
JText::script('VCMBCAHDELETE');
JText::script('VCM_ASK_CONTINUE');
JText::script('NEW');
JText::script('VCMYES');
JText::script('VCMNO');

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
				<span class="vcm-listing-toolbar-btn" data-jumpto="extras">
					<a href="JavaScript: void(0);"><?php VikBookingIcons::e('asterisk'); ?> <span>Extras</span></a>
				</span>
			</div>
			<div class="vcm-listing-toolbar-block">
				<span class="vcm-listing-toolbar-btn" data-jumpto="bedrooms">
					<a href="JavaScript: void(0);"><?php VikBookingIcons::e('bed'); ?> <span><?php echo JText::_('VCM_BEDROOMS'); ?></span></a>
				</span>
			</div>
			<div class="vcm-listing-toolbar-block">
				<span class="vcm-listing-toolbar-btn" data-jumpto="extrabeds">
					<a href="JavaScript: void(0);"><?php VikBookingIcons::e('bed'); ?> <span><?php echo JText::_('VCM_EXTRA_BEDS'); ?></span></a>
				</span>
			</div>
			<div class="vcm-listing-toolbar-block">
				<span class="vcm-listing-toolbar-btn" data-jumpto="thresholds">
					<a href="JavaScript: void(0);"><?php VikBookingIcons::e('money-bill'); ?> <span>Thresholds</span></a>
				</span>
			</div>
			<div class="vcm-listing-toolbar-block">
				<span class="vcm-listing-toolbar-btn" data-jumpto="license">
					<a href="JavaScript: void(0);"><?php VikBookingIcons::e('id-badge'); ?> <span>License</span></a>
				</span>
			</div>
			<div class="vcm-listing-toolbar-block">
				<span class="vcm-listing-toolbar-btn" data-jumpto="rateplans">
					<a href="JavaScript: void(0);"><?php VikBookingIcons::e('briefcase'); ?> <span><?php echo JText::_('VCMROOMSRELRPLANS'); ?></span></a>
				</span>
			</div>
			<div class="vcm-listing-toolbar-block">
				<span class="vcm-listing-toolbar-btn" data-jumpto="amenities">
					<a href="JavaScript: void(0);"><?php VikBookingIcons::e('icons'); ?> <span><?php echo JText::_('VCMTACHOTELAMENITIES'); ?></span></a>
				</span>
			</div>
			<div class="vcm-listing-toolbar-block vcm-listing-toolbar-block-link">
				<span class="vcm-listing-toolbar-btn">
					<a href="index.php?option=com_vikchannelmanager&task=expediaproduct.reload&listing_id=<?php echo $listing->get('id'); ?>" onclick="return confirm(Joomla.JText._('VCM_ASK_CONTINUE'));"><?php VikBookingIcons::e('sync'); ?> <span><?php echo JText::_('VCM_RELOAD'); ?></span></a>
				</span>
			</div>
		</div>
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
					<legend class="adminlegend"><?php VikBookingIcons::e('home'); ?> <?php echo $is_editing ? (JText::_('VCM_MNGPRODUCT_EDIT') . ' - ' . $listing->get('id')) : JText::_('VCM_MNGPRODUCT_NEW'); ?></legend>
					<div class="vcm-params-container">

						<div class="vcm-param-container">
							<div class="vcm-param-label">Partner Code</div>
							<div class="vcm-param-setting">
								<input type="text" name="listing[partnerCode]" value="<?php echo $this->escape($listing->get('partnerCode')); ?>" maxlength="40" />
								<span class="vcm-param-setting-comment">Partner room type custom code/identifier. 40 characters maximum.</span>
							</div>
						</div>

						<div class="vcm-params-block vcm-expediaroom-name-wrapper">

							<div class="vcm-param-container">
								<div class="vcm-param-label"><?php echo JText::_('VCMBCARCROOMNAME'); ?> (<?php echo JText::_('VCM_ATTRIBUTES'); ?>)</div>
								<div class="vcm-param-setting">
									<span class="vcm-param-setting-comment">The name of a room can be one of the Expedia predefined room names, or composed through room name attributes.</span>
								</div>
							</div>

							<?php
							$room_name_object = $listing->get('name');
							$room_name_attributes = is_object($room_name_object) && isset($room_name_object->attributes) && is_object($room_name_object->attributes) ? $room_name_object->attributes : null;
							?>
							<div class="vcm-param-container vcm-param-nested">
								<div class="vcm-param-label"><?php echo JText::_('VCM_TYPEOFROOM'); ?></div>
								<div class="vcm-param-setting">
									<select name="listing[name][attributes][typeOfRoom]" class="vcm-expedia-roomname-attribute" onchange="vcmRoomNameAttributesChosen(this);">
										<option value=""></option>
									<?php
									foreach (VCMExpediaProduct::getTypeOfRooms() as $type_of_room) {
										?>
										<option value="<?php echo $this->escape($type_of_room); ?>"<?php echo is_object($room_name_attributes) && isset($room_name_attributes->typeOfRoom) && !strcasecmp($room_name_attributes->typeOfRoom, $type_of_room) ? ' selected="selected"' : ''; ?>><?php echo $type_of_room; ?></option>
										<?php
									}
									?>
									</select>
								</div>
							</div>

							<div class="vcm-param-container vcm-param-nested">
								<div class="vcm-param-label">Class of room</div>
								<div class="vcm-param-setting">
									<select name="listing[name][attributes][roomClass]" class="vcm-expedia-roomname-attribute" onchange="vcmRoomNameAttributesChosen(this);">
										<option value=""></option>
									<?php
									foreach (VCMExpediaProduct::getRoomClasses() as $class_of_room) {
										?>
										<option value="<?php echo $this->escape($class_of_room); ?>"<?php echo is_object($room_name_attributes) && isset($room_name_attributes->roomClass) && !strcasecmp($room_name_attributes->roomClass, $class_of_room) ? ' selected="selected"' : ''; ?>><?php echo $class_of_room; ?></option>
										<?php
									}
									?>
									</select>
									<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_OPTIONAL'); ?>.</span>
								</div>
							</div>

							<div class="vcm-param-container vcm-param-nested">
								<div class="vcm-param-label">Include bed type</div>
								<div class="vcm-param-setting">
									<select name="listing[name][attributes][includeBedType]" class="vcm-expedia-roomname-attribute" onchange="vcmRoomNameAttributesChosen(this);">
										<option value=""></option>
										<option value="true"<?php echo is_object($room_name_attributes) && isset($room_name_attributes->includeBedType) && $room_name_attributes->includeBedType ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMYES'); ?></option>
										<option value="false"<?php echo is_object($room_name_attributes) && isset($room_name_attributes->includeBedType) && !$room_name_attributes->includeBedType ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMNO'); ?></option>
									</select>
									<span class="vcm-param-setting-comment">Whether or not to include bed type in the room name. <?php echo JText::_('VCM_OPTIONAL'); ?>.</span>
								</div>
							</div>

							<div class="vcm-param-container vcm-param-nested">
								<div class="vcm-param-label">Bedroom details</div>
								<div class="vcm-param-setting">
									<select name="listing[name][attributes][bedroomDetails]" class="vcm-expedia-roomname-attribute" onchange="vcmRoomNameAttributesChosen(this);">
										<option value=""></option>
									<?php
									foreach (VCMExpediaProduct::getBedroomDetails() as $bedroom_detail) {
										?>
										<option value="<?php echo $this->escape($bedroom_detail); ?>"<?php echo is_object($room_name_attributes) && isset($room_name_attributes->bedroomDetails) && !strcasecmp((string)$room_name_attributes->bedroomDetails, $bedroom_detail) ? ' selected="selected"' : ''; ?>><?php echo $bedroom_detail; ?></option>
										<?php
									}
									?>
									</select>
									<span class="vcm-param-setting-comment">Attribute that describes the details of the bedroom used to compose the name of the room. <?php echo JText::_('VCM_OPTIONAL'); ?>.</span>
								</div>
							</div>

							<div class="vcm-param-container vcm-param-nested">
								<div class="vcm-param-label">Include smoking preferences</div>
								<div class="vcm-param-setting">
									<select name="listing[name][attributes][includeSmokingPref]" class="vcm-expedia-roomname-attribute" onchange="vcmRoomNameAttributesChosen(this);">
										<option value=""></option>
										<option value="true"<?php echo is_object($room_name_attributes) && isset($room_name_attributes->includeSmokingPref) && $room_name_attributes->includeSmokingPref ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMYES'); ?></option>
										<option value="false"<?php echo is_object($room_name_attributes) && isset($room_name_attributes->includeSmokingPref) && !$room_name_attributes->includeSmokingPref ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMNO'); ?></option>
									</select>
									<span class="vcm-param-setting-comment">Attribute that determines if room has smoking preference. <?php echo JText::_('VCM_OPTIONAL'); ?>.</span>
								</div>
							</div>

							<div class="vcm-param-container vcm-param-nested">
								<div class="vcm-param-label"><?php echo JText::_('VCM_ACCESSIBILITY'); ?></div>
								<div class="vcm-param-setting">
									<select name="listing[name][attributes][accessibility]" class="vcm-expedia-roomname-attribute" onchange="vcmRoomNameAttributesChosen(this);">
										<option value=""></option>
										<option value="true"<?php echo is_object($room_name_attributes) && isset($room_name_attributes->accessibility) && $room_name_attributes->accessibility ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMYES'); ?></option>
										<option value="false"<?php echo is_object($room_name_attributes) && isset($room_name_attributes->accessibility) && !$room_name_attributes->accessibility ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMNO'); ?></option>
									</select>
									<span class="vcm-param-setting-comment">Attribute that determines if room is considered wheelchair accessible. <?php echo JText::_('VCM_OPTIONAL'); ?>.</span>
								</div>
							</div>

							<div class="vcm-param-container vcm-param-nested">
								<div class="vcm-param-label"><?php echo JText::_('VCMBCAHAMENTYPE128'); ?></div>
								<div class="vcm-param-setting">
									<select name="listing[name][attributes][view]" class="vcm-expedia-roomname-attribute" onchange="vcmRoomNameAttributesChosen(this);">
										<option value=""></option>
									<?php
									foreach (array_keys(VCMExpediaProduct::getRoomViews('room_name')) as $view_val) {
										?>
										<option value="<?php echo $this->escape($view_val); ?>"<?php echo is_object($room_name_attributes) && isset($room_name_attributes->view) && !strcasecmp((string)$room_name_attributes->view, $view_val) ? ' selected="selected"' : ''; ?>><?php echo $view_val; ?></option>
										<?php
									}
									?>
									</select>
									<span class="vcm-param-setting-comment">Attribute that describes the details of the bedroom used to compose the name of the room. <?php echo JText::_('VCM_OPTIONAL'); ?>.</span>
								</div>
							</div>

							<div class="vcm-param-container vcm-param-nested">
								<div class="vcm-param-label">Featured amenity</div>
								<div class="vcm-param-setting">
									<select name="listing[name][attributes][featuredAmenity]" class="vcm-expedia-roomname-attribute" onchange="vcmRoomNameAttributesChosen(this);">
										<option value=""></option>
									<?php
									foreach (VCMExpediaProduct::getFeaturedAmenities() as $feat_amenity_val) {
										?>
										<option value="<?php echo $this->escape($feat_amenity_val); ?>"<?php echo is_object($room_name_attributes) && isset($room_name_attributes->featuredAmenity) && !strcasecmp((string)$room_name_attributes->featuredAmenity, $feat_amenity_val) ? ' selected="selected"' : ''; ?>><?php echo $feat_amenity_val; ?></option>
										<?php
									}
									?>
									</select>
									<span class="vcm-param-setting-comment">Attribute used to highlight a feature of the room on its name. <?php echo JText::_('VCM_OPTIONAL'); ?>.</span>
								</div>
							</div>

							<div class="vcm-param-container vcm-param-nested">
								<div class="vcm-param-label"><?php echo JText::_('VCMGREVLOCATION'); ?></div>
								<div class="vcm-param-setting">
									<select name="listing[name][attributes][area]" class="vcm-expedia-roomname-attribute" onchange="vcmRoomNameAttributesChosen(this);">
										<option value=""></option>
									<?php
									foreach (VCMExpediaProduct::getAreaDescriptions() as $area_val) {
										?>
										<option value="<?php echo $this->escape($area_val); ?>"<?php echo is_object($room_name_attributes) && isset($room_name_attributes->area) && !strcasecmp((string)$room_name_attributes->area, $area_val) ? ' selected="selected"' : ''; ?>><?php echo $area_val; ?></option>
										<?php
									}
									?>
									</select>
									<span class="vcm-param-setting-comment">Attributed used to highlight the location of the room. <?php echo JText::_('VCM_OPTIONAL'); ?>.</span>
								</div>
							</div>

							<div class="vcm-param-container">
								<div class="vcm-param-setting"><?php echo JText::_('VCMOR'); ?></div>
							</div>

							<div class="vcm-param-container vcm-param-nested">
								<div class="vcm-param-label">Room Predefined Name</div>
								<div class="vcm-param-setting">
								<?php
								$prefefined_room_names = VCMExpediaProduct::getPredefinedRoomNamesEnum();
								$current_name_value = '';
								if (is_object($room_name_object) && !empty($room_name_object->value)) {
									$current_name_value = $room_name_object->value;
								}
								?>
									<select name="listing[name][value]" class="vcm-expedia-roomname-value" data-defnamevalue="<?php echo $this->escape($current_name_value); ?>" onchange="vcmRoomNameValueChosen(this);">
										<option value=""></option>
									<?php
									foreach ($prefefined_room_names as $def_rname) {
										?>
										<option value="<?php echo $this->escape($def_rname); ?>"<?php echo !strcasecmp($def_rname, $current_name_value) ? ' selected="selected"' : ''; ?>><?php echo $def_rname; ?></option>
										<?php
									}
									?>
									</select>
									<span class="vcm-param-setting-comment">The name of a room can be one of the Expedia predefined room names, or composed through room name attributes.</span>
								</div>
							</div>

						</div>

						<div class="vcm-param-container vcm-listing-noedit">
							<div class="vcm-param-label"><?php echo JText::_('VBSTATUS'); ?></div>
							<div class="vcm-param-setting">
								<select name="listing[status]"<?php echo $is_editing ? ' readonly' : ''; ?>>
									<option value="Active"<?php echo !strcasecmp($listing->get('status', ''), 'Active') ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMPROMSTATUSACTIVE'); ?></option>
									<option value="Inactive"<?php echo !strcasecmp($listing->get('status', ''), 'Inactive') ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMPROMSTATUSINACTIVE'); ?></option>
								</select>
								<span class="vcm-param-setting-comment">Room type status is derived from the rate plans associated with the room type: if at least one rate plan is active, the room type status will be active. If all rate plans are inactive, then the room type becomes inactive as well.</span>
							</div>
						</div>

						<div class="vcm-params-block vcm-expediaroom-agecategories">

							<div class="vcm-param-container">
								<div class="vcm-param-label">Age Categories</div>
								<div class="vcm-param-setting">
								<?php
								$room_age_categories = (array)$listing->get('ageCategories', []);
								$curr_age_categories = [];
								if ($room_age_categories) {
									foreach ($room_age_categories as $room_age_category) {
										if (!is_object($room_age_category) || !isset($room_age_category->category) || !isset($room_age_category->minAge)) {
											continue;
										}
										$curr_age_categories[$room_age_category->category] = (int)$room_age_category->minAge;
									}
								}
								?>
									<span class="vcm-param-setting-comment">Defines the different age categories supported by the room type. At the very least, the &quot;Adult&quot; category must be defined.</span>
								</div>
							</div>
							<div class="vcm-param-container vcm-param-nested">
								<div class="vcm-param-label"><?php echo JText::_('VCM_CATEGORY'); ?>: Adult</div>
								<div class="vcm-param-setting">
									<span>Minimum Age</span>
									<input type="hidden" name="listing[ageCategories][0][category]" value="Adult" class="vcm-hidden-disabled" />
									<input type="number" name="listing[ageCategories][0][minAge]" min="0" max="150" value="<?php echo isset($curr_age_categories['Adult']) ? $curr_age_categories['Adult'] : ''; ?>" onchange="vcmEnableAgeCategories();" />
								</div>
							</div>
							<div class="vcm-param-container vcm-param-nested">
								<div class="vcm-param-label"><?php echo JText::_('VCM_CATEGORY'); ?>: ChildAgeA</div>
								<div class="vcm-param-setting">
									<span>Minimum Age</span>
									<input type="hidden" name="listing[ageCategories][1][category]" value="ChildAgeA" class="vcm-hidden-disabled" />
									<input type="number" name="listing[ageCategories][1][minAge]" min="0" max="150" value="<?php echo isset($curr_age_categories['ChildAgeA']) ? $curr_age_categories['ChildAgeA'] : ''; ?>" onchange="vcmEnableAgeCategories();" />
								</div>
							</div>
							<div class="vcm-param-container vcm-param-nested">
								<div class="vcm-param-label"><?php echo JText::_('VCM_CATEGORY'); ?>: ChildAgeB</div>
								<div class="vcm-param-setting">
									<span>Minimum Age</span>
									<input type="hidden" name="listing[ageCategories][2][category]" value="ChildAgeB" class="vcm-hidden-disabled" />
									<input type="number" name="listing[ageCategories][2][minAge]" min="0" max="150" value="<?php echo isset($curr_age_categories['ChildAgeB']) ? $curr_age_categories['ChildAgeB'] : ''; ?>" onchange="vcmEnableAgeCategories();" />
								</div>
							</div>
							<div class="vcm-param-container vcm-param-nested">
								<div class="vcm-param-label"><?php echo JText::_('VCM_CATEGORY'); ?>: ChildAgeC</div>
								<div class="vcm-param-setting">
									<span>Minimum Age</span>
									<input type="hidden" name="listing[ageCategories][3][category]" value="ChildAgeC" class="vcm-hidden-disabled" />
									<input type="number" name="listing[ageCategories][3][minAge]" min="0" max="150" value="<?php echo isset($curr_age_categories['ChildAgeC']) ? $curr_age_categories['ChildAgeC'] : ''; ?>" onchange="vcmEnableAgeCategories();" />
								</div>
							</div>
							<div class="vcm-param-container vcm-param-nested">
								<div class="vcm-param-label"><?php echo JText::_('VCM_CATEGORY'); ?>: ChildAgeD</div>
								<div class="vcm-param-setting">
									<span>Minimum Age</span>
									<input type="hidden" name="listing[ageCategories][4][category]" value="ChildAgeD" class="vcm-hidden-disabled" />
									<input type="number" name="listing[ageCategories][4][minAge]" min="0" max="150" value="<?php echo isset($curr_age_categories['ChildAgeD']) ? $curr_age_categories['ChildAgeD'] : ''; ?>" onchange="vcmEnableAgeCategories();" />
								</div>
							</div>
							<div class="vcm-param-container vcm-param-nested">
								<div class="vcm-param-label"><?php echo JText::_('VCM_CATEGORY'); ?>: Infant</div>
								<div class="vcm-param-setting">
									<span>Minimum Age</span>
									<input type="hidden" name="listing[ageCategories][5][category]" value="Infant" class="vcm-hidden-disabled" />
									<input type="number" name="listing[ageCategories][5][minAge]" min="0" max="150" value="<?php echo isset($curr_age_categories['Infant']) ? $curr_age_categories['Infant'] : ''; ?>" onchange="vcmEnableAgeCategories();" />
								</div>
							</div>

						</div>

						<div class="vcm-params-block">

							<?php
							$max_occ_obj = $listing->get('maxOccupancy');
							?>
							<div class="vcm-param-container">
								<div class="vcm-param-label"><?php echo JText::_('VCMBCARCMAXOCCUP'); ?></div>
								<div class="vcm-param-setting">
									<span class="vcm-param-setting-comment">Defines the maximum occupancy of the room in total and by age category.</span>
								</div>
							</div>

							<div class="vcm-expediaroom-maxoccupancy">

								<div class="vcm-param-container vcm-param-nested">
									<div class="vcm-param-label"><?php echo JText::_('VCMBCARCMAXOCCUP') . ' - ' . JText::_('VCMBCAHIMGTAG92'); ?></div>
									<div class="vcm-param-setting">
										<input type="number" name="listing[maxOccupancy][total]" min="0" max="99" onchange="vcmEnableMaxOccupancy();" value="<?php echo is_object($max_occ_obj) && isset($max_occ_obj->total) ? (int)$max_occ_obj->total : '0'; ?>" />
									</div>
								</div>
								<div class="vcm-param-container vcm-param-nested">
									<div class="vcm-param-label"><?php echo JText::_('VCMBCARCMAXOCCUP') . ' - ' . JText::_('VCMADULTS'); ?></div>
									<div class="vcm-param-setting">
										<input type="number" name="listing[maxOccupancy][adults]" min="0" max="99" onchange="vcmEnableMaxOccupancy();" value="<?php echo is_object($max_occ_obj) && isset($max_occ_obj->adults) ? (int)$max_occ_obj->adults : '0'; ?>" />
									</div>
								</div>
								<div class="vcm-param-container vcm-param-nested">
									<div class="vcm-param-label"><?php echo JText::_('VCMBCARCMAXOCCUP') . ' - ' . JText::_('VCMCHILDREN'); ?></div>
									<div class="vcm-param-setting">
										<input type="number" name="listing[maxOccupancy][children]" min="0" max="99" onchange="vcmEnableMaxOccupancy();" value="<?php echo is_object($max_occ_obj) && isset($max_occ_obj->children) ? (int)$max_occ_obj->children : '0'; ?>" />
									</div>
								</div>

							</div>

						</div>

					</div>
				</div>
			</fieldset>

			<fieldset class="adminform vcm-listings-listing-wrap" data-landto="bedrooms">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('bed'); ?> <?php echo JText::_('VCM_BEDROOMS'); ?></legend>
					<div class="vcm-params-container">

						<div class="vcm-param-container">
							<div class="vcm-param-setting">
								<button type="button" class="btn vcm-config-btn" onclick="vcmAddBedroom();"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::_('VCMBCAHADD'); ?></button>
							</div>
						</div>

						<div class="vcm-expediaroom-bedrooms">
					<?php
					$bedrooms = (array)$listing->get('bedrooms', []);
					if ($is_editing && $bedrooms) {
						foreach ($bedrooms as $bk => $bedroom) {
							if (!is_object($bedroom) || !isset($bedroom->count) || !isset($bedroom->bedding) || !is_array($bedroom->bedding)) {
								// invalid bedroom structure
								continue;
							}
							?>
							<div class="vcm-params-block vcm-expediaroom-bedroom">

								<div class="vcm-param-container">
									<div class="vcm-param-label"><strong><?php echo JText::_('VCMBCAHIMGTAG84'); ?></strong></div>
									<div class="vcm-param-setting">
										<button type="button" class="btn btn-danger" onclick="vcmRemoveBedroom(this);"><?php VikBookingIcons::e('times-circle'); ?></button>
									</div>
								</div>

								<div class="vcm-param-container vcm-param-nested">
									<div class="vcm-param-label">Count</div>
									<div class="vcm-param-setting">
										<input type="number" name="listing[bedrooms][<?php echo $bk; ?>][count]" data-buildname="listing[bedrooms][%d][count]" value="<?php echo (int)$bedroom->count; ?>" min="1" max="99" />
										<span class="vcm-param-setting-comment">A count of this bedroom can be specified.</span>
									</div>
								</div>

								<div class="vcm-param-container vcm-param-nested">
									<div class="vcm-param-label">Bedding Options</div>
									<div class="vcm-param-setting">
										<span class="vcm-param-setting-comment">Minimum 1, maximum 2 bedding options a bedroom may have. Each bedding option can be with a combination of beds (type, size and quantity). If two bedding options provided, these will be displayed as &quot;OR&quot; to guests. By providing multiple combination of beds inside a bedding option, these will be displayed as &quot;AND&quot; to guests.</span>
									</div>
								</div>

								<div class="vcm-param-container vcm-param-nested vcm-param-nested-nested">
									<div class="vcm-param-label">Bedding Option #1</div>
									<div class="vcm-param-setting">
										<button type="button" class="btn vcm-config-btn" onclick="vcmAddBedroomBed(this, 1);"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::_('VCMBCAHIMGTAG11'); ?></button>
									</div>
								</div>

								<div class="vcm-expediaroom-bedroom-option-beds" data-bedoption="1">
							<?php
							if (isset($bedroom->bedding[0]) && is_object($bedroom->bedding[0]) && isset($bedroom->bedding[0]->option) && is_array($bedroom->bedding[0]->option)) {
								// display beds type and size for the first bedding option
								foreach ($bedroom->bedding[0]->option as $bedding_opt_k => $bedding_opt_bed) {
									if (!is_object($bedding_opt_bed) || !isset($bedding_opt_bed->quantity) || !isset($bedding_opt_bed->type)) {
										// invalid bedroom option bed object structure
										continue;
									}
									if (!isset($bedding_opt_bed->size)) {
										$bedding_opt_bed->size = '';
									}
									?>
									<div class="vcm-params-block vcm-params-block-nested vcm-expediaroom-bedroom-option-bed">

										<div class="vcm-param-container">
											<div class="vcm-param-label"><?php echo JText::_('VCMBCAHIMGTAG11'); ?></div>
											<div class="vcm-param-setting">
												<button type="button" class="btn btn-danger" onclick="vcmRemoveBedroomBed(this);"><?php VikBookingIcons::e('times-circle'); ?></button>
											</div>
										</div>

										<div class="vcm-param-container">
											<div class="vcm-param-label"><?php echo JText::_('VCMRESLOGSTYPE'); ?></div>
											<div class="vcm-param-setting">
												<select name="listing[bedrooms][<?php echo $bk; ?>][bedding][0][option][<?php echo $bedding_opt_k; ?>][type]" data-buildname="listing[bedrooms][%d][bedding][0][option][%d][type]" data-currentbedtype="<?php echo $this->escape($bedding_opt_bed->type); ?>" data-bedinfo="type" onchange="vboSetBedSize(this);">
													<option value=""></option>
												<?php
												foreach ($expedia_bed_types as $expedia_bed_type_id) {
													?>
													<option value="<?php echo $this->escape($expedia_bed_type_id); ?>"<?php echo !strcasecmp($expedia_bed_type_id, $bedding_opt_bed->type) ? ' selected="selected"' : ''; ?>><?php echo $expedia_bed_type_id; ?></option>
													<?php
												}
												?>
												</select>
												<span class="vcm-param-setting-comment">Defines the bed type. Example: &quot;King Bed&quot;, &quot;Sofa Bed&quot;.</span>
											</div>
										</div>

										<div class="vcm-param-container">
											<div class="vcm-param-label"><?php echo JText::_('VCM_BED_SIZE'); ?></div>
											<div class="vcm-param-setting">
											<?php
											$proper_bed_sizes = isset($expedia_bed_type_sizes[$bedding_opt_bed->type]) ? $expedia_bed_type_sizes[$bedding_opt_bed->type] : VCMExpediaProduct::getBedTypeSizes('sizes');
											?>
												<select name="listing[bedrooms][<?php echo $bk; ?>][bedding][0][option][<?php echo $bedding_opt_k; ?>][size]" data-buildname="listing[bedrooms][%d][bedding][0][option][%d][size]" data-currentbedsize="<?php echo $this->escape($bedding_opt_bed->size); ?>" data-bedinfo="size">
													<option value=""></option>
												<?php
												foreach ($proper_bed_sizes as $expedia_bed_size_id) {
													?>
													<option value="<?php echo $this->escape($expedia_bed_size_id); ?>"<?php echo !strcasecmp($expedia_bed_size_id, $bedding_opt_bed->size) ? ' selected="selected"' : ''; ?>><?php echo $expedia_bed_size_id; ?></option>
													<?php
												}
												?>
												</select>
												<span class="vcm-param-setting-comment">Defines the size of the bed. Example: &quot;King&quot;, &quot;Queen&quot;.</span>
											</div>
										</div>

										<div class="vcm-param-container">
											<div class="vcm-param-label"><?php echo JText::_('VCMBCAHQUANTITY'); ?></div>
											<div class="vcm-param-setting">
												<input type="number" name="listing[bedrooms][<?php echo $bk; ?>][bedding][0][option][<?php echo $bedding_opt_k; ?>][quantity]" data-buildname="listing[bedrooms][%d][bedding][0][option][%d][quantity]" value="<?php echo (int)$bedding_opt_bed->quantity; ?>" min="1" max="99" />
												<span class="vcm-param-setting-comment">Number of beds of this type.</span>
											</div>
										</div>

									</div>
									<?php
								}
							}
							?>
								</div>

								<div class="vcm-param-container vcm-param-nested vcm-param-nested-nested">
									<div class="vcm-param-label">Bedding Option #2</div>
									<div class="vcm-param-setting">
										<button type="button" class="btn vcm-config-btn" onclick="vcmAddBedroomBed(this, 2);"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::_('VCMBCAHIMGTAG11'); ?></button>
									</div>
								</div>

								<div class="vcm-expediaroom-bedroom-option-beds" data-bedoption="2">
							<?php
							if (isset($bedroom->bedding[1]) && is_object($bedroom->bedding[1]) && isset($bedroom->bedding[1]->option) && is_array($bedroom->bedding[1]->option)) {
								// display beds type and size for the second bedding option (if any)
								foreach ($bedroom->bedding[1]->option as $bedding_opt_k => $bedding_opt_bed) {
									if (!is_object($bedding_opt_bed) || !isset($bedding_opt_bed->quantity) || !isset($bedding_opt_bed->type)) {
										// invalid bedroom option bed object structure
										continue;
									}
									if (!isset($bedding_opt_bed->size)) {
										$bedding_opt_bed->size = '';
									}
									?>
									<div class="vcm-params-block vcm-params-block-nested vcm-expediaroom-bedroom-option-bed">

										<div class="vcm-param-container vcm-param-nested">
											<div class="vcm-param-label"><?php echo JText::_('VCMBCAHIMGTAG11'); ?></div>
											<div class="vcm-param-setting">
												<button type="button" class="btn btn-danger" onclick="vcmRemoveBedroomBed(this);"><?php VikBookingIcons::e('times-circle'); ?></button>
											</div>
										</div>

										<div class="vcm-param-container vcm-param-nested">
											<div class="vcm-param-label"><?php echo JText::_('VCMRESLOGSTYPE'); ?></div>
											<div class="vcm-param-setting">
												<select name="listing[bedrooms][<?php echo $bk; ?>][bedding][1][option][<?php echo $bedding_opt_k; ?>][type]" data-buildname="listing[bedrooms][%d][bedding][1][option][%d][type]" data-currentbedtype="<?php echo $this->escape($bedding_opt_bed->type); ?>" data-bedinfo="type" onchange="vboSetBedSize(this);">
													<option value=""></option>
												<?php
												foreach ($expedia_bed_types as $expedia_bed_type_id) {
													?>
													<option value="<?php echo $this->escape($expedia_bed_type_id); ?>"<?php echo !strcasecmp($expedia_bed_type_id, $bedding_opt_bed->type) ? ' selected="selected"' : ''; ?>><?php echo $expedia_bed_type_id; ?></option>
													<?php
												}
												?>
												</select>
												<span class="vcm-param-setting-comment">Defines the bed type. Example: &quot;King Bed&quot;, &quot;Sofa Bed&quot;.</span>
											</div>
										</div>

										<div class="vcm-param-container vcm-param-nested">
											<div class="vcm-param-label"><?php echo JText::_('VCM_BED_SIZE'); ?></div>
											<div class="vcm-param-setting">
											<?php
											$proper_bed_sizes = isset($expedia_bed_type_sizes[$bedding_opt_bed->type]) ? $expedia_bed_type_sizes[$bedding_opt_bed->type] : VCMExpediaProduct::getBedTypeSizes('sizes');
											?>
												<select name="listing[bedrooms][<?php echo $bk; ?>][bedding][1][option][<?php echo $bedding_opt_k; ?>][size]" data-buildname="listing[bedrooms][%d][bedding][1][option][%d][size]" data-currentbedsize="<?php echo $this->escape($bedding_opt_bed->size); ?>" data-bedinfo="size">
													<option value=""></option>
												<?php
												foreach ($proper_bed_sizes as $expedia_bed_size_id) {
													?>
													<option value="<?php echo $this->escape($expedia_bed_size_id); ?>"<?php echo !strcasecmp($expedia_bed_size_id, $bedding_opt_bed->size) ? ' selected="selected"' : ''; ?>><?php echo $expedia_bed_size_id; ?></option>
													<?php
												}
												?>
												</select>
												<span class="vcm-param-setting-comment">Defines the size of the bed. Example: &quot;King&quot;, &quot;Queen&quot;.</span>
											</div>
										</div>

										<div class="vcm-param-container vcm-param-nested">
											<div class="vcm-param-label"><?php echo JText::_('VCMBCAHQUANTITY'); ?></div>
											<div class="vcm-param-setting">
												<input type="number" name="listing[bedrooms][<?php echo $bk; ?>][bedding][1][option][<?php echo $bedding_opt_k; ?>][quantity]" data-buildname="listing[bedrooms][%d][bedding][1][option][%d][quantity]" value="<?php echo (int)$bedding_opt_bed->quantity; ?>" min="1" max="99" />
												<span class="vcm-param-setting-comment">Number of beds of this type.</span>
											</div>
										</div>

									</div>
									<?php
								}
							}
							?>
								</div>

							</div>
							<?php
						}
					}
					?>
						</div>

						<div class="vcm-expedia-bedrooms-clone-copies" style="display: none;">

							<div class="vcm-params-block vcm-expediaroom-bedroom">

								<div class="vcm-param-container">
									<div class="vcm-param-label"><strong><?php echo JText::_('VCMBCAHIMGTAG84'); ?></strong></div>
									<div class="vcm-param-setting">
										<button type="button" class="btn btn-danger" onclick="vcmRemoveBedroom(this);"><?php VikBookingIcons::e('times-circle'); ?></button>
									</div>
								</div>

								<div class="vcm-param-container vcm-param-nested">
									<div class="vcm-param-label">Count</div>
									<div class="vcm-param-setting">
										<input type="number" name="" data-buildname="listing[bedrooms][%d][count]" value="1" min="1" max="99" />
										<span class="vcm-param-setting-comment">A count of this bedroom can be specified.</span>
									</div>
								</div>

								<div class="vcm-param-container vcm-param-nested">
									<div class="vcm-param-label">Bedding Options</div>
									<div class="vcm-param-setting">
										<span class="vcm-param-setting-comment">Minimum 1, maximum 2 bedding options a bedroom may have. Each bedding option can be with a combination of beds (type, size and quantity). If two bedding options provided, these will be displayed as &quot;OR&quot; to guests. By providing multiple combination of beds inside a bedding option, these will be displayed as &quot;AND&quot; to guests.</span>
									</div>
								</div>

								<div class="vcm-param-container vcm-param-nested vcm-param-nested-nested">
									<div class="vcm-param-label">Bedding Option #1</div>
									<div class="vcm-param-setting">
										<button type="button" class="btn vcm-config-btn" onclick="vcmAddBedroomBed(this, 1);"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::_('VCMBCAHIMGTAG11'); ?></button>
									</div>
								</div>

								<div class="vcm-expediaroom-bedroom-option-beds" data-bedoption="1">

									<div class="vcm-params-block vcm-params-block-nested vcm-expediaroom-bedroom-option-bed">

										<div class="vcm-param-container">
											<div class="vcm-param-label"><?php echo JText::_('VCMBCAHIMGTAG11'); ?></div>
											<div class="vcm-param-setting">
												<button type="button" class="btn btn-danger" onclick="vcmRemoveBedroomBed(this);"><?php VikBookingIcons::e('times-circle'); ?></button>
											</div>
										</div>

										<div class="vcm-param-container">
											<div class="vcm-param-label"><?php echo JText::_('VCMRESLOGSTYPE'); ?></div>
											<div class="vcm-param-setting">
												<select name="" data-buildname="listing[bedrooms][%d][bedding][0][option][%d][type]" data-bedinfo="type" onchange="vboSetBedSize(this);">
													<option value=""></option>
												<?php
												foreach ($expedia_bed_types as $expedia_bed_type_id) {
													?>
													<option value="<?php echo $this->escape($expedia_bed_type_id); ?>"><?php echo $expedia_bed_type_id; ?></option>
													<?php
												}
												?>
												</select>
												<span class="vcm-param-setting-comment">Defines the bed type. Example: &quot;King Bed&quot;, &quot;Sofa Bed&quot;.</span>
											</div>
										</div>

										<div class="vcm-param-container">
											<div class="vcm-param-label"><?php echo JText::_('VCM_BED_SIZE'); ?></div>
											<div class="vcm-param-setting">
												<select name="" data-buildname="listing[bedrooms][%d][bedding][0][option][%d][size]" data-bedinfo="size">
													<option value=""></option>
												</select>
												<span class="vcm-param-setting-comment">Defines the size of the bed. Example: &quot;King&quot;, &quot;Queen&quot;.</span>
											</div>
										</div>

										<div class="vcm-param-container">
											<div class="vcm-param-label"><?php echo JText::_('VCMBCAHQUANTITY'); ?></div>
											<div class="vcm-param-setting">
												<input type="number" name="" data-buildname="listing[bedrooms][%d][bedding][0][option][%d][quantity]" value="1" min="1" max="99" />
												<span class="vcm-param-setting-comment">Number of beds of this type.</span>
											</div>
										</div>

									</div>

								</div>

								<div class="vcm-param-container vcm-param-nested vcm-param-nested-nested">
									<div class="vcm-param-label">Bedding Option #2</div>
									<div class="vcm-param-setting">
										<button type="button" class="btn vcm-config-btn" onclick="vcmAddBedroomBed(this, 2);"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::_('VCMBCAHIMGTAG11'); ?></button>
									</div>
								</div>

								<div class="vcm-expediaroom-bedroom-option-beds" data-bedoption="2">

									<div class="vcm-params-block vcm-params-block-nested vcm-expediaroom-bedroom-option-bed">

										<div class="vcm-param-container">
											<div class="vcm-param-label"><?php echo JText::_('VCMBCAHIMGTAG11'); ?></div>
											<div class="vcm-param-setting">
												<button type="button" class="btn btn-danger" onclick="vcmRemoveBedroomBed(this);"><?php VikBookingIcons::e('times-circle'); ?></button>
											</div>
										</div>

										<div class="vcm-param-container">
											<div class="vcm-param-label"><?php echo JText::_('VCMRESLOGSTYPE'); ?></div>
											<div class="vcm-param-setting">
												<select name="" data-buildname="listing[bedrooms][%d][bedding][1][option][%d][type]" data-bedinfo="type" onchange="vboSetBedSize(this);">
													<option value=""></option>
												<?php
												foreach ($expedia_bed_types as $expedia_bed_type_id) {
													?>
													<option value="<?php echo $this->escape($expedia_bed_type_id); ?>"><?php echo $expedia_bed_type_id; ?></option>
													<?php
												}
												?>
												</select>
												<span class="vcm-param-setting-comment">Defines the bed type. Example: &quot;King Bed&quot;, &quot;Sofa Bed&quot;.</span>
											</div>
										</div>

										<div class="vcm-param-container">
											<div class="vcm-param-label"><?php echo JText::_('VCM_BED_SIZE'); ?></div>
											<div class="vcm-param-setting">
												<select name="" data-buildname="listing[bedrooms][%d][bedding][1][option][%d][size]" data-bedinfo="size">
													<option value=""></option>
												</select>
												<span class="vcm-param-setting-comment">Defines the size of the bed. Example: &quot;King&quot;, &quot;Queen&quot;.</span>
											</div>
										</div>

										<div class="vcm-param-container">
											<div class="vcm-param-label"><?php echo JText::_('VCMBCAHQUANTITY'); ?></div>
											<div class="vcm-param-setting">
												<input type="number" name="" data-buildname="listing[bedrooms][%d][bedding][1][option][%d][quantity]" value="1" min="1" max="99" />
												<span class="vcm-param-setting-comment">Number of beds of this type.</span>
											</div>
										</div>

									</div>

								</div>

						</div>

					</div>

				</div>
			</fieldset>

			<fieldset class="adminform vcm-listings-listing-wrap" data-landto="extrabeds">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('bed'); ?> <?php echo JText::_('VCM_EXTRA_BEDS'); ?></legend>
					<div class="vcm-params-container">

						<div class="vcm-param-container">
							<div class="vcm-param-setting">
								<button type="button" class="btn vcm-config-btn" onclick="vcmAddExtrabed();"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::_('VCMBCAHADD'); ?></button>
							</div>
						</div>

						<div class="vcm-expediaroom-extrabeds">
					<?php
					$extrabeds = (array)$listing->get('extraBedding', []);
					if ($is_editing && $extrabeds) {
						foreach ($extrabeds as $k => $extrabed) {
							if (!is_object($extrabed) || !isset($extrabed->quantity) || !isset($extrabed->type)) {
								// invalid extrabed object structure
								continue;
							}
							if (!isset($extrabed->size)) {
								$extrabed->size = '';
							}
							?>
							<div class="vcm-params-block vcm-expediaroom-extrabed">

								<div class="vcm-param-container">
									<div class="vcm-param-label"><?php echo JText::_('VCMAGODARARRATEEXTRABED'); ?></div>
									<div class="vcm-param-setting">
										<button type="button" class="btn btn-danger" onclick="vcmRemoveExtrabed(this);"><?php VikBookingIcons::e('times-circle'); ?></button>
									</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label"><?php echo JText::_('VCMBCAHQUANTITY'); ?></div>
									<div class="vcm-param-setting">
										<input type="number" name="listing[extraBedding][<?php echo $k; ?>][quantity]" data-buildname="listing[extraBedding][%d][quantity]" value="<?php echo (int)$extrabed->quantity; ?>" min="1" max="99" />
										<span class="vcm-param-setting-comment">Number of beds of this type.</span>
									</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label"><?php echo JText::_('VCMRESLOGSTYPE'); ?></div>
									<div class="vcm-param-setting">
										<select name="listing[extraBedding][<?php echo $k; ?>][type]" data-buildname="listing[extraBedding][%d][type]" data-currentbedtype="<?php echo $this->escape($extrabed->type); ?>" data-bedinfo="type" onchange="vboSetExtrabedSize(this);">
											<option value=""></option>
										<?php
										foreach ($expedia_bed_types as $expedia_bed_type_id) {
											?>
											<option value="<?php echo $this->escape($expedia_bed_type_id); ?>"<?php echo !strcasecmp($expedia_bed_type_id, $extrabed->type) ? ' selected="selected"' : ''; ?>><?php echo $expedia_bed_type_id; ?></option>
											<?php
										}
										?>
										</select>
										<span class="vcm-param-setting-comment">Defines the bed type. Example: &quot;King Bed&quot;, &quot;Sofa Bed&quot;.</span>
									</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label"><?php echo JText::_('VCM_BED_SIZE'); ?></div>
									<div class="vcm-param-setting">
									<?php
									$proper_bed_sizes = isset($expedia_bed_type_sizes[$extrabed->type]) ? $expedia_bed_type_sizes[$extrabed->type] : VCMExpediaProduct::getBedTypeSizes('sizes');
									?>
										<select name="listing[extraBedding][<?php echo $k; ?>][size]" data-buildname="listing[extraBedding][%d][size]" data-currentbedsize="<?php echo $this->escape($extrabed->size); ?>" data-bedinfo="size">
											<option value=""></option>
										<?php
										foreach ($proper_bed_sizes as $expedia_bed_size_id) {
											?>
											<option value="<?php echo $this->escape($expedia_bed_size_id); ?>"<?php echo !strcasecmp($expedia_bed_size_id, $extrabed->size) ? ' selected="selected"' : ''; ?>><?php echo $expedia_bed_size_id; ?></option>
											<?php
										}
										?>
										</select>
										<span class="vcm-param-setting-comment">Defines the size of the bed. Example: &quot;King&quot;, &quot;Queen&quot;.</span>
									</div>
								</div>

								<div class="vcm-param-container vcm-expedia-extrabed-surcharge-field" style="<?php !isset($expedia_bed_surcharges[$extrabed->type]) ? 'display: none;' : ''; ?>">
									<div class="vcm-param-label"><?php echo JText::_('VCMBCARCAMENITYVAL3'); ?> - <?php echo JText::_('VCMRESLOGSTYPE'); ?></div>
									<div class="vcm-param-setting">
									<?php
									$extrabed_surcharge = isset($extrabed->surcharge) && is_object($extrabed->surcharge) ? $extrabed->surcharge : (new stdClass);
									?>
										<select name="listing[extraBedding][<?php echo $k; ?>][surcharge][type]" data-buildname="listing[extraBedding][%d][surcharge][type]">
										<?php
										foreach (VCMExpediaProduct::getSurchargeTypes() as $surcharge_key => $surcharge_name) {
											?>
											<option value="<?php echo $this->escape($surcharge_key); ?>"<?php echo isset($extrabed_surcharge->type) && !strcasecmp($surcharge_key, $extrabed_surcharge->type) ? ' selected="selected"' : ''; ?>><?php echo $surcharge_name; ?></option>
											<?php
										}
										?>
										</select>
										<span class="vcm-param-setting-comment">The type of surcharge for this extra bed. Select &quot;Free&quot; if no surcharges apply.</span>
									</div>
								</div>

								<div class="vcm-param-container vcm-expedia-extrabed-surcharge-field" style="<?php !isset($expedia_bed_surcharges[$extrabed->type]) ? 'display: none;' : ''; ?>">
									<div class="vcm-param-label"><?php echo JText::_('VCMBCARCAMENITYVAL3'); ?> - <?php echo JText::_('VCMBCAHAMOUNT'); ?></div>
									<div class="vcm-param-setting">
										<input type="number" value="<?php echo isset($extrabed_surcharge->amount) ? (float)$extrabed_surcharge->amount : ''; ?>" step="any" name="listing[extraBedding][<?php echo $k; ?>][surcharge][amount]" data-buildname="listing[extraBedding][%d][surcharge][amount]" min="0" />
									</div>
								</div>

							</div>
							<?php
						}
					}
					?>

						</div>

						<div class="vcm-expedia-extrabed-clone-copy" style="display: none;">

							<div class="vcm-params-block vcm-expediaroom-extrabed">

								<div class="vcm-param-container">
									<div class="vcm-param-label"><?php echo JText::_('VCMAGODARARRATEEXTRABED'); ?></div>
									<div class="vcm-param-setting">
										<button type="button" class="btn btn-danger" onclick="vcmRemoveExtrabed(this);"><?php VikBookingIcons::e('times-circle'); ?></button>
									</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label"><?php echo JText::_('VCMBCAHQUANTITY'); ?></div>
									<div class="vcm-param-setting">
										<input type="number" data-buildname="listing[extraBedding][%d][quantity]" value="" min="1" max="99" />
										<span class="vcm-param-setting-comment">Number of beds of this type.</span>
									</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label"><?php echo JText::_('VCMRESLOGSTYPE'); ?></div>
									<div class="vcm-param-setting">
										<select data-buildname="listing[extraBedding][%d][type]" data-bedinfo="type" onchange="vboSetExtrabedSize(this);">
											<option value=""></option>
										<?php
										foreach ($expedia_bed_types as $expedia_bed_type_id) {
											?>
											<option value="<?php echo $this->escape($expedia_bed_type_id); ?>"><?php echo $expedia_bed_type_id; ?></option>
											<?php
										}
										?>
										</select>
										<span class="vcm-param-setting-comment">Defines the bed type. Example: &quot;King Bed&quot;, &quot;Sofa Bed&quot;.</span>
									</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label"><?php echo JText::_('VCM_BED_SIZE'); ?></div>
									<div class="vcm-param-setting">
										<select name="" data-buildname="listing[extraBedding][%d][size]" data-bedinfo="size">
											<option value=""></option>
										</select>
										<span class="vcm-param-setting-comment">Defines the size of the bed. Example: &quot;King&quot;, &quot;Queen&quot;.</span>
									</div>
								</div>

								<div class="vcm-param-container vcm-expedia-extrabed-surcharge-field" style="display: none;">
									<div class="vcm-param-label"><?php echo JText::_('VCMBCARCAMENITYVAL3'); ?> - <?php echo JText::_('VCMRESLOGSTYPE'); ?></div>
									<div class="vcm-param-setting">
										<select name="" data-buildname="listing[extraBedding][%d][surcharge][type]">
										<?php
										foreach (VCMExpediaProduct::getSurchargeTypes() as $surcharge_key => $surcharge_name) {
											?>
											<option value="<?php echo $this->escape($surcharge_key); ?>"><?php echo $surcharge_name; ?></option>
											<?php
										}
										?>
										</select>
										<span class="vcm-param-setting-comment">The type of surcharge for this extra bed. Select &quot;Free&quot; if no surcharges apply.</span>
									</div>
								</div>

								<div class="vcm-param-container vcm-expedia-extrabed-surcharge-field" style="display: none;">
									<div class="vcm-param-label"><?php echo JText::_('VCMBCARCAMENITYVAL3'); ?> - <?php echo JText::_('VCMBCAHAMOUNT'); ?></div>
									<div class="vcm-param-setting">
										<input type="number" value="" step="any" name="" data-buildname="listing[extraBedding][%d][surcharge][amount]" min="0" />
									</div>
								</div>

							</div>

						</div>

					</div>
				</div>
			</fieldset>

		</div>

		<div class="vcm-config-maintab-right">

			<fieldset class="adminform vcm-listings-listing-wrap" data-landto="extras">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('asterisk'); ?> Extras</legend>
					<div class="vcm-params-container">

						<div class="vcm-param-container">
							<div class="vcm-param-label">Smoking Preferences</div>
							<div class="vcm-param-setting">
								<?php
								$smoking_preferences = (array)$listing->get('smokingPreferences', []);
								?>
								<select name="listing[smokingPreferences][]" multiple="multiple" class="vcm-multi-select">
									<option value="Smoking"<?php echo in_array('Smoking', $smoking_preferences) || count($smoking_preferences) > 1 ? ' selected="selected"' : ''; ?>>Smoking</option>
									<option value="Non-Smoking"<?php echo in_array('Non-Smoking', $smoking_preferences) || count($smoking_preferences) > 1 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMBCARCNONSMOKING'); ?></option>
								</select>
								<span class="vcm-param-setting-comment">Choose smoking, non-smoking, or if both options are available on request. If a single smoking option is provided, then the room is only available in this configuration. If both options are provided, then a choice will be offered to the customer.</span>
							</div>
						</div>

						<?php
						$floor_size = $listing->get('floorSize');
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMBCARCSIZEMEASUREMENT'); ?></div>
							<div class="vcm-param-setting">
								<span class="vcm-param-setting-comment">Used to define room size. Both size in square feet and in square meters must be specified.</span>
							</div>
						</div>
						<div class="vcm-param-container vcm-param-nested">
							<div class="vcm-param-label"><?php echo JText::_('VCMBCAHDISTMSR4'); ?></div>
							<div class="vcm-param-setting">
								<input type="number" name="listing[floorSize][squareFeet]" value="<?php echo is_object($floor_size) && isset($floor_size->squareFeet) ? (int)$floor_size->squareFeet : ''; ?>" min="0" max="99999" step="1" onchange="vcmEnableField('[name=\'listing[floorSize][squareMeters]\']', vcmSquareMetersFeet(this.value, 'feet'));" />
								<span class="vcm-param-setting-comment">Room size in ft<sup>2</sup>.</span>
							</div>
						</div>
						<div class="vcm-param-container vcm-param-nested">
							<div class="vcm-param-label"><?php echo JText::_('VCMBCAHDISTMSR2'); ?></div>
							<div class="vcm-param-setting">
								<input type="number" name="listing[floorSize][squareMeters]" value="<?php echo is_object($floor_size) && isset($floor_size->squareMeters) ? (int)$floor_size->squareMeters : ''; ?>" min="0" max="9999" step="1" onchange="vcmEnableField('[name=\'listing[floorSize][squareFeet]\']', vcmSquareMetersFeet(this.value, 'meters'));" />
								<span class="vcm-param-setting-comment">Room size in m<sup>2</sup>.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Room Views</div>
							<div class="vcm-param-setting">
								<?php
								$room_views = (array)$listing->get('views', []);
								?>
								<select name="listing[views][]" multiple="multiple" class="vcm-multi-select">
								<?php
								foreach (VCMExpediaProduct::getRoomViews('room_level') as $room_view => $view_vals) {
									?>
									<option value="<?php echo $this->escape($room_view); ?>"<?php echo in_array($room_view, $room_views) ? ' selected="selected"' : ''; ?>><?php echo $room_view; ?></option>
									<?php
								}
								?>
								</select>
								<span class="vcm-param-setting-comment">Used to define view(s) from the room. There can be up to 2 different views defined per room type.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('WHEELCHAIR_ACCESS'); ?></div>
							<div class="vcm-param-setting">
								<?php
								$wheelchair_access = $listing->get('wheelchairAccessibility', null);
								?>
								<select name="listing[wheelchairAccessibility]">
									<option value=""></option>
									<option value="true"<?php echo $wheelchair_access === true ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMYES'); ?></option>
									<option value="false"<?php echo $wheelchair_access === false ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMNO'); ?></option>
								</select>
								<span class="vcm-param-setting-comment">Whether the room is configured to be wheelchair accessible or not.</span>
							</div>
						</div>

					</div>
				</div>
			</fieldset>

		<?php
		$rate_thresholds = $listing->get('_rateThresholds');
		if (is_object($rate_thresholds) && count(get_object_vars($rate_thresholds))) {
			?>
			<fieldset class="adminform vcm-listings-listing-wrap" data-landto="thresholds">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('money-bill'); ?> Rate Thresholds</legend>
					<div class="vcm-params-container">

						<div class="vcm-param-container">
							<div class="vcm-param-label">Minimum amount</div>
							<div class="vcm-param-setting">
								<span><?php echo isset($rate_thresholds->minAmount) ? $rate_thresholds->minAmount : '-----'; ?></span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Maximum amount</div>
							<div class="vcm-param-setting">
								<span><?php echo isset($rate_thresholds->maxAmount) ? $rate_thresholds->maxAmount : '-----'; ?></span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Source</div>
							<div class="vcm-param-setting">
								<span><?php echo isset($rate_thresholds->source) ? $rate_thresholds->source : '-----'; ?></span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-setting">
								<span class="vcm-param-setting-comment">Rate thresholds define the minimum and maximum acceptable amounts that can be pushed for a room night, under any rate plan of the room type.</span>
							</div>
						</div>

					</div>
				</div>
			</fieldset>
			<?php
		}
		?>

			<fieldset class="adminform vcm-listings-listing-wrap" data-landto="license">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('id-badge'); ?> Regulatory and license details</legend>
					<div class="vcm-params-container">
						<?php
						$regulatory_records = $listing->get('regulatoryRecords');
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-setting">
								<span class="vcm-param-setting-comment">Depending on the location of the property and the property type, this attribute <strong>may</strong> or <strong>may not</strong> be mandatory. Predominantly used for vacation rental listings.</span>
							</div>
						</div>

						<div class="vcm-param-container vcm-param-container-tmp-disabled">
							<div class="vcm-param-label"><?php echo JText::_('VCM_CATEGORY'); ?></div>
							<div class="vcm-param-setting">
								<select name="listing[regulatoryRecords][category]">
									<option value=""></option>
								<?php
								foreach (VCMExpediaProduct::getRegulatoryCategories() as $reg_cat_code => $reg_cat_name) {
									?>
									<option value="<?php echo $reg_cat_code; ?>"<?php echo is_object($regulatory_records) && isset($regulatory_records->category) && $regulatory_records->category == $reg_cat_code ? ' selected="selected"' : ''; ?>><?php echo $reg_cat_name; ?></option>
									<?php
								}
								?>
								</select>
								<span class="vcm-param-setting-comment">Used to specify the category of property as per jurisdiction requirements. Utilized for regulatory validation purposes.</span>
							</div>
						</div>

					<?php
					/**
					 * Regulatory records -> records can be an array of multiple records in edit mode,
					 * but in create mode we only allow to transmit one array of records for the moment.
					 */
					$tot_regulatory_records = is_object($regulatory_records) && isset($regulatory_records->records) && is_array($regulatory_records->records) ? count($regulatory_records->records) : 1;
					$tot_regulatory_records = $tot_regulatory_records > 0 ? $tot_regulatory_records : 1;
					for ($r = 0; $r < $tot_regulatory_records; $r++) {
						$regulatory_records_records = is_object($regulatory_records) && isset($regulatory_records->records) && is_array($regulatory_records->records) && isset($regulatory_records->records[$r]) && is_object($regulatory_records->records[$r]) ? $regulatory_records->records[$r] : null;
						?>
						<div class="vcm-param-container vcm-param-container-tmp-disabled">
							<div class="vcm-param-label">Registration number</div>
							<div class="vcm-param-setting">
								<input type="text" name="listing[regulatoryRecords][records][<?php echo $r; ?>][registrationNumber]" value="<?php echo is_object($regulatory_records_records) && isset($regulatory_records_records->registrationNumber) ? $this->escape($regulatory_records_records->registrationNumber) : ''; ?>" />
								<span class="vcm-param-setting-comment">Used to specify the license/registration number of the property/room type that is registered with the city's regulators. Could be required for certain jurisdictions depending on property type.</span>
							</div>
						</div>

						<div class="vcm-param-container vcm-param-container-tmp-disabled">
							<div class="vcm-param-label">Registration number type</div>
							<div class="vcm-param-setting">
								<select name="listing[regulatoryRecords][records][<?php echo $r; ?>][registrationNumberType]">
									<option value=""></option>
								<?php
								foreach (VCMExpediaProduct::getRegistrationNumberTypes() as $reg_type_code => $reg_type_name) {
									?>
									<option value="<?php echo $reg_type_code; ?>"<?php echo is_object($regulatory_records_records) && isset($regulatory_records_records->registrationNumberType) && $regulatory_records_records->registrationNumberType == $reg_type_code ? ' selected="selected"' : ''; ?>><?php echo $reg_type_name; ?></option>
									<?php
								}
								?>
								</select>
								<span class="vcm-param-setting-comment">Type of license/registration number that is registered with the city's regulator. Could be required for certain jurisdictions depending on property type.</span>
							</div>
						</div>

						<div class="vcm-param-container vcm-param-container-tmp-disabled">
							<div class="vcm-param-label">License expiration date</div>
							<div class="vcm-param-setting">
								<?php echo $vbo_app->getCalendar((is_object($regulatory_records_records) && isset($regulatory_records_records->expiry) ? $this->escape($regulatory_records_records->expiry) : ''), 'listing[regulatoryRecords][records][' . $r . '][expiry]', 'listing-regulatoryRecords-records-expiry-' . $r, '%Y-%m-%d'); ?>
								<span class="vcm-param-setting-comment">Date of expiration of the license/registration number in YYYY-MM-DD format, if available.</span>
							</div>
						</div>
					<?php
					}
					?>

					</div>
				</div>
			</fieldset>

		<?php
		// always declare the pools of rate plan names and distribution models
		$all_rate_plan_names = [];
		$all_rate_plan_ec_codes = [];
		$all_rate_plan_hc_codes = [];
		// allow rate plans management functions only when editing an existing room-type
		if ($is_editing) {
			// rate plans management is only available when editing an existing room-type
			$room_rate_plans = (array)$listing->get('_ratePlans', []);
			?>
			<fieldset class="adminform vcm-listings-listing-wrap" data-landto="rateplans">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('briefcase'); ?> <?php echo JText::_('VCMROOMSRELRPLANS'); ?></legend>
					<div class="vcm-params-container">

						<div class="vcm-param-container">
							<div class="vcm-param-setting">
								<div style="text-align: right;">
									<button type="button" class="btn vcm-config-btn" onclick="vcmNewRatePlan();"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::_('NEW'); ?></button>
								</div>
							</div>
						</div>

						<div class="vcm-expediaroom-rateplans">

						<?php
						foreach ($room_rate_plans as $k => $room_rplan) {
							if (!is_object($room_rplan) || !isset($room_rplan->resourceId)) {
								// invalid room rate plan object structure
								continue;
							}
							$is_rplan_active = !strcasecmp($room_rplan->status, 'Active');
							// push rate plan name to help with the creation of a new one
							$all_rate_plan_names[] = $room_rplan->name;
							?>
							<div class="vcm-params-block vcm-expediaroom-rateplan">

								<div class="vcm-param-container vcm-expediaroom-rateplan-first-param">
									<div class="vcm-param-label">
										<strong><?php echo $room_rplan->name; ?></strong>
										<div>
											<span class="hasTooltip label label-<?php echo $is_rplan_active ? 'success' : 'error'; ?>" title="<?php echo $this->escape(JText::_($is_rplan_active ? 'VCMPROMSTATUSACTIVE' : 'VCMPROMSTATUSINACTIVE')); ?>"><?php echo $room_rplan->resourceId; ?></span>
										</div>
									</div>
									<div class="vcm-param-setting">
										<input type="hidden" name="listing[_ratePlans][<?php echo $k; ?>][resourceId]" value="<?php echo $room_rplan->resourceId; ?>" />
										<button type="button" class="btn vcm-config-btn vcm-expediaroom-toggle-rateplans hasTooltip" title="<?php echo $this->escape(JText::_('EDIT')); ?>"><?php VikBookingIcons::e('edit'); ?></button>
									</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label"><?php echo JText::_('VCMROOMSRELATIONSNAME'); ?></div>
									<div class="vcm-param-setting">
										<input type="text" name="listing[_ratePlans][<?php echo $k; ?>][name]" value="<?php echo $this->escape($room_rplan->name); ?>" maxlength="40" />
										<span class="vcm-param-setting-comment">Minimum 1, maximum 40 characters. If not provided, defaults to the manageable rate plan partner code.</span>
									</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label"><?php echo JText::_('VCMMENUTACSTATUS'); ?></div>
									<div class="vcm-param-setting">
										<select name="listing[_ratePlans][<?php echo $k; ?>][status]">
											<option value="Active"<?php echo !strcasecmp($room_rplan->status, 'Active') ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMPROMSTATUSACTIVE'); ?></option>
											<option value="Inactive"<?php echo !strcasecmp($room_rplan->status, 'Inactive') ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMPROMSTATUSINACTIVE'); ?></option>
										</select>
										<span class="vcm-param-setting-comment">Created on <?php echo $room_rplan->creationDateTime; ?></span>
									</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label">Rate acquisition type</div>
									<div class="vcm-param-setting">
										<strong><?php echo $room_rplan->rateAcquisitionType; ?></strong>
										<span class="vcm-param-setting-comment"><u>NetRate</u>: rate without compensation. <u>SellLAR</u>: rate inclusive of compensation. Compensation = OTA Commission.</span>
									</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label">Distribution Rules</div>
									<div class="vcm-param-setting">
										<span class="vcm-param-setting-comment">Indicate how this rate plan can be sold (Expedia Collect, Hotel Collect or both). Also contain the IDs and Codes that need to be mapped to push avail/rates and identify the right rate plans in booking messages.</span>
									</div>
								</div>
							<?php
							if (isset($room_rplan->distributionRules) && is_array($room_rplan->distributionRules)) {
								foreach ($room_rplan->distributionRules as $drk => $distributionRule) {
									if (stripos($distributionRule->distributionModel, 'Expedia') !== false) {
										// expedia collect
										$all_rate_plan_ec_codes[] = $distributionRule->partnerCode;
									} else {
										// hotel collect
										$all_rate_plan_hc_codes[] = $distributionRule->partnerCode;
									}
									?>
								<div class="vcm-param-container vcm-param-nested">
									<div class="vcm-param-label">
										<span>Distribution Rule - <?php echo $distributionRule->partnerCode; ?></span>
										<div>
											<span class="label label-info"><?php echo $distributionRule->expediaId; ?></span>
										</div>
									</div>
									<div class="vcm-param-setting">
										<div class="vcm-param-subsetting">
											<span>Distribution model:</span>
											<span><?php echo $distributionRule->distributionModel; ?></span>
										</div>
										<div class="vcm-param-subsetting">
											<span>Manageable:</span>
											<span><?php VikBookingIcons::e($distributionRule->manageable ? 'check-circle' : 'times-circle'); ?></span>
										</div>
										<div class="vcm-param-subsetting">
											<span>Compensation:</span>
											<span><?php echo isset($distributionRule->compensation) && isset($distributionRule->compensation->percent) ? ($distributionRule->compensation->percent * 100) . '%' : ''; ?></span>
											<span><?php echo isset($distributionRule->compensation) && !empty($distributionRule->compensation->minAmount) ? $distributionRule->compensation->minAmount . ' (minimum amount)' : ''; ?></span>
										</div>
									</div>
								</div>
									<?php
								}
							}
							?>

								<div class="vcm-param-container">
									<div class="vcm-param-label">Rate Plan type</div>
									<div class="vcm-param-setting">
										<select name="listing[_ratePlans][<?php echo $k; ?>][type]">
											<option value=""></option>
											<option value="Standalone"<?php echo !strcasecmp($room_rplan->type, 'Standalone') ? ' selected="selected"' : ''; ?>>Standalone</option>
											<option value="Package"<?php echo !strcasecmp($room_rplan->type, 'Package') ? ' selected="selected"' : ''; ?>>Package</option>
											<option value="Corporate"<?php echo !strcasecmp($room_rplan->type, 'Corporate') ? ' selected="selected"' : ''; ?>>Corporate</option>
											<option value="Wholesale"<?php echo !strcasecmp($room_rplan->type, 'Wholesale') ? ' selected="selected"' : ''; ?>>Wholesale</option>
										</select>
										<span class="vcm-param-setting-comment">Defaults to Standalone if not provided.</span>
									</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label">Pricing Model</div>
									<div class="vcm-param-setting">
										<select name="listing[_ratePlans][<?php echo $k; ?>][pricingModel]">
											<option value=""></option>
											<option value="PerDayPricing"<?php echo !strcasecmp($room_rplan->pricingModel, 'PerDayPricing') ? ' selected="selected"' : ''; ?>>PerDayPricing</option>
											<option value="PerDayPricingByDayOfArrival"<?php echo !strcasecmp($room_rplan->pricingModel, 'PerDayPricingByDayOfArrival') ? ' selected="selected"' : ''; ?>>PerDayPricingByDayOfArrival</option>
											<option value="PerDayPricingByLengthOfStay"<?php echo !strcasecmp($room_rplan->pricingModel, 'PerDayPricingByLengthOfStay') ? ' selected="selected"' : ''; ?>>PerDayPricingByLengthOfStay</option>
											<option value="OccupancyBasedPricing"<?php echo !strcasecmp($room_rplan->pricingModel, 'OccupancyBasedPricing') ? ' selected="selected"' : ''; ?>>OccupancyBasedPricing</option>
											<option value="OccupancyBasedPricingByDayOfArrival"<?php echo !strcasecmp($room_rplan->pricingModel, 'OccupancyBasedPricingByDayOfArrival') ? ' selected="selected"' : ''; ?>>OccupancyBasedPricingByDayOfArrival</option>
											<option value="OccupancyBasedPricingByLengthOfStay"<?php echo !strcasecmp($room_rplan->pricingModel, 'OccupancyBasedPricingByLengthOfStay') ? ' selected="selected"' : ''; ?>>OccupancyBasedPricingByLengthOfStay</option>
										</select>
										<span class="vcm-param-setting-comment">Rate plan pricing model. Will default to the property's pricing model, and if provided, it has to match the property's pricing model (except in the case of Length of Stay-based pricing models which are only at the Rate plan level).</span>
									</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label">Occupants for base rate</div>
									<div class="vcm-param-setting">
										<input type="number" name="listing[_ratePlans][<?php echo $k; ?>][occupantsForBaseRate]" value="<?php echo isset($room_rplan->occupantsForBaseRate) ? (int)$room_rplan->occupantsForBaseRate : ''; ?>" min="1" max="20" />
										<span class="vcm-param-setting-comment">Maximum occupants allowed for the base rate. Minimum 1, Maximum 20. This is only applicable for per-day pricing properties, and it indicates how many occupants the per-day price applies to.</span>
									</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label">Tax Inclusive</div>
									<div class="vcm-param-setting">
										<select name="listing[_ratePlans][<?php echo $k; ?>][taxInclusive]">
											<option value="true"<?php echo $room_rplan->taxInclusive ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMYES'); ?></option>
											<option value="false"<?php echo !$room_rplan->taxInclusive ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMNO'); ?></option>
										</select>
										<span class="vcm-param-setting-comment">Indicate whether the rate being exchanged over other APIs is inclusive of taxes or not. For properties managing net rates, the default value is false. For sell rates, it is based on the property's configuration.</span>
									</div>
								</div>

							<?php
							if (isset($room_rplan->depositRequired)) {
								?>
								<div class="vcm-param-container">
									<div class="vcm-param-label">Deposit Required</div>
									<div class="vcm-param-setting">
										<select name="listing[_ratePlans][<?php echo $k; ?>][depositRequired]">
											<option value="true"<?php echo $room_rplan->depositRequired ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMYES'); ?></option>
											<option value="false"<?php echo !$room_rplan->depositRequired ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMNO'); ?></option>
										</select>
										<span class="vcm-param-setting-comment">Indicates if a deposit is required upon booking. This flag is only available for rate plans with the Hotel Collect or Expedia Traveler Preference business models.</span>
									</div>
								</div>
								<?php
							}
							?>

								<div class="vcm-param-container">
									<div class="vcm-param-label">Min LOS Default</div>
									<div class="vcm-param-setting">
										<input type="number" name="listing[_ratePlans][<?php echo $k; ?>][minLOSDefault]" value="<?php echo (int)$room_rplan->minLOSDefault; ?>" min="1" max="28" />
										<span class="vcm-param-setting-comment">Default minimum LengthOfStay restriction. Minimum 1, maximum 28. Will always be considered along the value defined for each stay date, hence the value can be modified on any date of the year.</span>
									</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label">Max LOS Default</div>
									<div class="vcm-param-setting">
										<input type="number" name="listing[_ratePlans][<?php echo $k; ?>][maxLOSDefault]" value="<?php echo (int)$room_rplan->maxLOSDefault; ?>" min="1" max="28" />
										<span class="vcm-param-setting-comment">Default maximum LengthOfStay restriction. Minimum 1, maximum 28. Will always be considered along the value defined for each stay date, hence the value can be modified on any date of the year.</span>
									</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label">Min Advance Book Days</div>
									<div class="vcm-param-setting">
										<input type="number" name="listing[_ratePlans][<?php echo $k; ?>][minAdvBookDays]" value="<?php echo (int)$room_rplan->minAdvBookDays; ?>" min="0" max="500" />
										<span class="vcm-param-setting-comment">The minimum days before a stay date that the rate plan can be sold. Minimum 0, maximum 500.</span>
									</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label">Max Advance Book Days</div>
									<div class="vcm-param-setting">
										<input type="number" name="listing[_ratePlans][<?php echo $k; ?>][maxAdvBookDays]" value="<?php echo (int)$room_rplan->maxAdvBookDays; ?>" min="0" max="500" />
										<span class="vcm-param-setting-comment">The maximum days before a stay date that the rate plan can be sold. Minimum 0, maximum 500.</span>
									</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label">Book Date Start</div>
									<div class="vcm-param-setting">
										<?php echo $vbo_app->getCalendar($room_rplan->bookDateStart, 'listing[_ratePlans][' . $k . '][bookDateStart]', 'listing-ratePlans-bookDateStart-' . $k, '%Y-%m-%d'); ?>
										<span class="vcm-param-setting-comment">Date at which this rate plan starts being available for searching on any Expedia POS. If in the past, indicates rate plan book date start is not restricted. Accepted format: YYYY-MM-DD. If not restricted, will be returned as 1900-01-01.</span>
									</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label">Book Date End</div>
									<div class="vcm-param-setting">
										<?php echo $vbo_app->getCalendar($room_rplan->bookDateEnd, 'listing[_ratePlans][' . $k . '][bookDateEnd]', 'listing-ratePlans-bookDateEnd-' . $k, '%Y-%m-%d'); ?>
										<span class="vcm-param-setting-comment">Date at which this rate plan stops being available for searching on any Expedia POS. Format YYYY-MM-DD. If not restricted, will be returned as 2079-06-06. If in 2079, indicates this rate plan book end date is unrestricted.</span>
									</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label">Travel Date Start</div>
									<div class="vcm-param-setting">
										<?php echo $vbo_app->getCalendar($room_rplan->travelDateStart, 'listing[_ratePlans][' . $k . '][travelDateStart]', 'listing-ratePlans-travelDateStart-' . $k, '%Y-%m-%d'); ?>
										<span class="vcm-param-setting-comment">Date at which customers can start checking in for a stay including this rate plan. Format YYYY-MM-DD. If not restricted, will be returned at 1900-01-01. If in the past, indicates rate plan travel start date is not restricted.</span>
									</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label">Travel Date End</div>
									<div class="vcm-param-setting">
										<?php echo $vbo_app->getCalendar($room_rplan->travelDateEnd, 'listing[_ratePlans][' . $k . '][travelDateEnd]', 'listing-ratePlans-travelDateEnd-' . $k, '%Y-%m-%d'); ?>
										<span class="vcm-param-setting-comment">Latest date at which customers can checkout for a stay including this rate plan. Format YYYY-MM-DD. If not restricted, will be returned as 2079-06-06. If in 2079, indicates rate plan travel end date is not restricted.</span>
									</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label">Mobile Only</div>
									<div class="vcm-param-setting">
										<select name="listing[_ratePlans][<?php echo $k; ?>][mobileOnly]">
											<option value="true"<?php echo $room_rplan->mobileOnly ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMYES'); ?></option>
											<option value="false"<?php echo !$room_rplan->mobileOnly ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMNO'); ?></option>
										</select>
										<span class="vcm-param-setting-comment">Indicates if this rate plan is only available through shopping done on mobile devices.</span>
									</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label">Value Add Inclusions</div>
									<div class="vcm-param-setting">
										<?php
										$value_add_inclusions = isset($room_rplan->valueAddInclusions) ? (array)$room_rplan->valueAddInclusions : [];
										// make sure to convert all strings to lower case
										$value_add_inclusions = array_map('strtolower', $value_add_inclusions);
										?>
										<select name="listing[_ratePlans][<?php echo $k; ?>][valueAddInclusions][]" multiple="multiple" class="vcm-multi-select">
										<?php
										$vai_group = null;
										foreach (VCMExpediaProduct::getValueAddInclusions() as $vai_code => $vai_data) {
											if ($vai_group != $vai_data['group']) {
												if (!is_null($vai_group)) {
													// close previous node
													echo '</optgroup>' . "\n";
												}
												// open new node
												echo '<optgroup label="' . $this->escape($vai_data['group']) . '">' . "\n";
												// update current group
												$vai_group = $vai_data['group'];
											}
											?>
											<option value="<?php echo $this->escape($vai_code); ?>"<?php echo in_array(strtolower($vai_code), $value_add_inclusions) ? ' selected="selected"' : ''; ?>><?php echo $vai_code; ?></option>
											<?php
										}
										if (!is_null($vai_group) && $vai_group == $vai_data['group']) {
											// close last node
											echo '</optgroup>' . "\n";
										}
										?>
										</select>
										<span class="vcm-param-setting-comment">Value add inclusions are special features included with this rate. Breakfast, Internet, or parking inclusions are the most frequently used ones.</span>
									</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label">Cancel Policy</div>
									<div class="vcm-param-setting">
										<span class="vcm-param-setting-comment">Default cancel policy with cancellation penalties.</span>
									</div>
								</div>

								<div class="vcm-param-container vcm-param-nested">
									<div class="vcm-param-label">Default Cancellation Penalties</div>
									<div class="vcm-param-setting">
										<span class="vcm-param-setting-comment">There can be up to 3 penalties provided. A penalty with a deadline of 0 is <u>always required</u>. A second and third deadline can optionally be provided.</span>
									</div>
								</div>

								<div class="vcm-expediaroom-rateplan-cancpenalties">

									<div class="vcm-param-container vcm-param-nested vcm-param-nested-nested">
										<div class="vcm-param-label">Cancellation Penalty #1</div>
									</div>

									<div class="vcm-params-block vcm-params-block-nested">

										<?php
										$penalty_one = isset($room_rplan->cancelPolicy) && isset($room_rplan->cancelPolicy->defaultPenalties) && is_array($room_rplan->cancelPolicy->defaultPenalties) && isset($room_rplan->cancelPolicy->defaultPenalties[0]) ? $room_rplan->cancelPolicy->defaultPenalties[0] : null;
										?>
										<div class="vcm-param-container">
											<div class="vcm-param-label">Deadline</div>
											<div class="vcm-param-setting">
												<input type="number" name="listing[_ratePlans][<?php echo $k; ?>][cancelPolicy][defaultPenalties][0][deadline]" onchange="vcmEnableRplanCancPenalties(this);" value="<?php echo is_object($penalty_one) && isset($penalty_one->deadline) ? (int)$penalty_one->deadline : ''; ?>" />
												<span class="vcm-param-setting-comment">Penalty window defined in <u>hours</u>. Hours are relative to check-in date and the property's cancellation time (property level configuration that is available in read-only mode under the property resource). Minimum 0, maximum 32767 hours.</span>
											</div>
										</div>

										<div class="vcm-param-container">
											<div class="vcm-param-label">Per-Stay Fee</div>
											<div class="vcm-param-setting">
												<select name="listing[_ratePlans][<?php echo $k; ?>][cancelPolicy][defaultPenalties][0][perStayFee]" onchange="vcmEnableRplanCancPenalties(this);">
													<option value=""></option>
												<?php
												foreach (VCMExpediaProduct::getPerStayFees() as $per_stay_fee_code => $per_stay_fee_name) {
													?>
													<option value="<?php echo $this->escape($per_stay_fee_code); ?>"<?php echo is_object($penalty_one) && isset($penalty_one->perStayFee) && !strcasecmp($per_stay_fee_code, $penalty_one->perStayFee) ? ' selected="selected"' : ''; ?>><?php echo $per_stay_fee_name; ?></option>
													<?php
												}
												?>
												</select>
												<span class="vcm-param-setting-comment">Fee that will be charged if the customer cancels within the specified deadline. A cancel penalty can either be a per-stay fee or a flat amount, but it cannot be both.</span>
											</div>
										</div>

										<div class="vcm-param-container">
											<div class="vcm-param-setting">
												<?php echo JText::_('VCMOR'); ?>
											</div>
										</div>

										<div class="vcm-param-container">
											<div class="vcm-param-label">Flat amount</div>
											<div class="vcm-param-setting">
												<input type="number" name="listing[_ratePlans][<?php echo $k; ?>][cancelPolicy][defaultPenalties][0][amount]" onchange="vcmEnableRplanCancPenalties(this);" value="<?php echo is_object($penalty_one) && isset($penalty_one->amount) ? (float)$penalty_one->amount : ''; ?>" step="any" />
												<span class="vcm-param-setting-comment">Used to define a flat amount that would be charged as a cancel or change penalty. A cancel penalty can either be a per-stay fee or a flat amount, but it cannot be both. The amount provided here should be based on the property rate acquisition type. If the property rate acquisition type is Net, the rate provided here should be net of Expedia compensation. If it is SellLAR, the rate should be what the customer will be charged (inclusive of Expedia Group compensation).</span>
											</div>
										</div>

									</div>

									<div class="vcm-param-container vcm-param-nested vcm-param-nested-nested">
										<div class="vcm-param-label">Cancellation Penalty #2</div>
									</div>

									<div class="vcm-params-block vcm-params-block-nested">

										<?php
										$penalty_two = isset($room_rplan->cancelPolicy) && isset($room_rplan->cancelPolicy->defaultPenalties) && is_array($room_rplan->cancelPolicy->defaultPenalties) && isset($room_rplan->cancelPolicy->defaultPenalties[1]) ? $room_rplan->cancelPolicy->defaultPenalties[1] : null;
										?>
										<div class="vcm-param-container">
											<div class="vcm-param-label">Deadline</div>
											<div class="vcm-param-setting">
												<input type="number" name="listing[_ratePlans][<?php echo $k; ?>][cancelPolicy][defaultPenalties][1][deadline]" onchange="vcmEnableRplanCancPenalties(this);" value="<?php echo is_object($penalty_two) && isset($penalty_two->deadline) ? (int)$penalty_two->deadline : ''; ?>" />
												<span class="vcm-param-setting-comment">Penalty window defined in <u>hours</u>. Hours are relative to check-in date and the property's cancellation time (property level configuration that is available in read-only mode under the property resource). Minimum 0, maximum 32767 hours.</span>
											</div>
										</div>

										<div class="vcm-param-container">
											<div class="vcm-param-label">Per-Stay Fee</div>
											<div class="vcm-param-setting">
												<select name="listing[_ratePlans][<?php echo $k; ?>][cancelPolicy][defaultPenalties][1][perStayFee]" onchange="vcmEnableRplanCancPenalties(this);">
													<option value=""></option>
												<?php
												foreach (VCMExpediaProduct::getPerStayFees() as $per_stay_fee_code => $per_stay_fee_name) {
													?>
													<option value="<?php echo $this->escape($per_stay_fee_code); ?>"<?php echo is_object($penalty_two) && isset($penalty_two->perStayFee) && !strcasecmp($per_stay_fee_code, $penalty_two->perStayFee) ? ' selected="selected"' : ''; ?>><?php echo $per_stay_fee_name; ?></option>
													<?php
												}
												?>
												</select>
												<span class="vcm-param-setting-comment">Fee that will be charged if the customer cancels within the specified deadline. A cancel penalty can either be a per-stay fee or a flat amount, but it cannot be both.</span>
											</div>
										</div>

										<div class="vcm-param-container">
											<div class="vcm-param-setting">
												<?php echo JText::_('VCMOR'); ?>
											</div>
										</div>

										<div class="vcm-param-container">
											<div class="vcm-param-label">Flat amount</div>
											<div class="vcm-param-setting">
												<input type="number" name="listing[_ratePlans][<?php echo $k; ?>][cancelPolicy][defaultPenalties][1][amount]" onchange="vcmEnableRplanCancPenalties(this);" value="<?php echo is_object($penalty_two) && isset($penalty_two->amount) ? (float)$penalty_two->amount : ''; ?>" step="any" />
												<span class="vcm-param-setting-comment">Used to define a flat amount that would be charged as a cancel or change penalty. A cancel penalty can either be a per-stay fee or a flat amount, but it cannot be both. The amount provided here should be based on the property rate acquisition type. If the property rate acquisition type is Net, the rate provided here should be net of Expedia compensation. If it is SellLAR, the rate should be what the customer will be charged (inclusive of Expedia Group compensation).</span>
											</div>
										</div>

									</div>

									<div class="vcm-param-container vcm-param-nested vcm-param-nested-nested">
										<div class="vcm-param-label">Cancellation Penalty #3</div>
									</div>

									<div class="vcm-params-block vcm-params-block-nested">

										<?php
										$penalty_three = isset($room_rplan->cancelPolicy) && isset($room_rplan->cancelPolicy->defaultPenalties) && is_array($room_rplan->cancelPolicy->defaultPenalties) && isset($room_rplan->cancelPolicy->defaultPenalties[2]) ? $room_rplan->cancelPolicy->defaultPenalties[2] : null;
										?>
										<div class="vcm-param-container">
											<div class="vcm-param-label">Deadline</div>
											<div class="vcm-param-setting">
												<input type="number" name="listing[_ratePlans][<?php echo $k; ?>][cancelPolicy][defaultPenalties][2][deadline]" onchange="vcmEnableRplanCancPenalties(this);" value="<?php echo is_object($penalty_three) && isset($penalty_three->deadline) ? (int)$penalty_three->deadline : ''; ?>" />
												<span class="vcm-param-setting-comment">Penalty window defined in <u>hours</u>. Hours are relative to check-in date and the property's cancellation time (property level configuration that is available in read-only mode under the property resource). Minimum 0, maximum 32767 hours.</span>
											</div>
										</div>

										<div class="vcm-param-container">
											<div class="vcm-param-label">Per-Stay Fee</div>
											<div class="vcm-param-setting">
												<select name="listing[_ratePlans][<?php echo $k; ?>][cancelPolicy][defaultPenalties][2][perStayFee]" onchange="vcmEnableRplanCancPenalties(this);">
													<option value=""></option>
												<?php
												foreach (VCMExpediaProduct::getPerStayFees() as $per_stay_fee_code => $per_stay_fee_name) {
													?>
													<option value="<?php echo $this->escape($per_stay_fee_code); ?>"<?php echo is_object($penalty_three) && isset($penalty_three->perStayFee) && !strcasecmp($per_stay_fee_code, $penalty_three->perStayFee) ? ' selected="selected"' : ''; ?>><?php echo $per_stay_fee_name; ?></option>
													<?php
												}
												?>
												</select>
												<span class="vcm-param-setting-comment">Fee that will be charged if the customer cancels within the specified deadline. A cancel penalty can either be a per-stay fee or a flat amount, but it cannot be both.</span>
											</div>
										</div>

										<div class="vcm-param-container">
											<div class="vcm-param-setting">
												<?php echo JText::_('VCMOR'); ?>
											</div>
										</div>

										<div class="vcm-param-container">
											<div class="vcm-param-label">Flat amount</div>
											<div class="vcm-param-setting">
												<input type="number" name="listing[_ratePlans][<?php echo $k; ?>][cancelPolicy][defaultPenalties][2][amount]" onchange="vcmEnableRplanCancPenalties(this);" value="<?php echo is_object($penalty_three) && isset($penalty_three->amount) ? (float)$penalty_three->amount : ''; ?>" step="any" />
												<span class="vcm-param-setting-comment">Used to define a flat amount that would be charged as a cancel or change penalty. A cancel penalty can either be a per-stay fee or a flat amount, but it cannot be both. The amount provided here should be based on the property rate acquisition type. If the property rate acquisition type is Net, the rate provided here should be net of Expedia compensation. If it is SellLAR, the rate should be what the customer will be charged (inclusive of Expedia Group compensation).</span>
											</div>
										</div>

									</div>

								</div>

							<?php
							if (isset($room_rplan->cancelPolicy) && isset($room_rplan->cancelPolicy->exceptions) && is_array($room_rplan->cancelPolicy->exceptions)) {
								/**
								 * This would be an array of CancelPolicyException objects, composed of startDate, endDate and penalties, which is an array of
								 * Penalty objects. There could be up to 500 exceptions for the cancel policy, and so it's absurd to display them. For the moment
								 * we do not display them, nor do we allow to add new exceptions. We only count them, or a modal window would be required in this case.
								 */
								?>
								<div class="vcm-param-container vcm-param-nested">
									<div class="vcm-param-label">Cancellation Penalty Exceptions</div>
									<div class="vcm-param-setting">
										<span>Total Exceptions: <?php echo count($room_rplan->cancelPolicy->exceptions); ?></span>
									</div>
								</div>
								<?php
							}
							?>

								<div class="vcm-param-container">
									<div class="vcm-param-label">Additional guest amounts</div>
									<div class="vcm-param-setting">
										<button type="button" class="btn vcm-config-btn" onclick="vcmMngRateplanAdditionalGuestAmount(this, '<?php echo $k; ?>');"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::_('VCMBCAHADD'); ?></button>
									</div>
								</div>

								<div class="vcm-expediaroom-rateplan-additionalguestamounts" data-rateplanindex="<?php echo $k; ?>">

								<?php
								$additional_guest_amounts = isset($room_rplan->additionalGuestAmounts) ? (array)$room_rplan->additionalGuestAmounts : [];
								foreach ($additional_guest_amounts as $aga_k => $additional_guest_amount) {
									if (!is_object($additional_guest_amount) || !isset($additional_guest_amount->ageCategory)) {
										// invalid additional guest amount object structure
										continue;
									}
									?>
									<div class="vcm-params-block vcm-expediaroom-rateplan-additionalguestamount">

										<div class="vcm-param-container">
											<div class="vcm-param-label">Start Date</div>
											<div class="vcm-param-setting">
												<span><?php echo isset($additional_guest_amount->dateStart) ? $additional_guest_amount->dateStart : '-----'; ?></span>
												<input type="hidden" class="vcm-hidden-disabled" name="listing[_ratePlans][<?php echo $k; ?>][additionalGuestAmounts][<?php echo $aga_k; ?>][dateStart]" data-buildname="listing[_ratePlans][<?php echo $k; ?>][additionalGuestAmounts][%d][dateStart]" value="<?php echo isset($additional_guest_amount->dateStart) ? $additional_guest_amount->dateStart : ''; ?>" />
											</div>
										</div>

										<div class="vcm-param-container">
											<div class="vcm-param-label">End Date</div>
											<div class="vcm-param-setting">
												<span><?php echo isset($additional_guest_amount->dateEnd) ? $additional_guest_amount->dateEnd : '-----'; ?></span>
												<input type="hidden" class="vcm-hidden-disabled" name="listing[_ratePlans][<?php echo $k; ?>][additionalGuestAmounts][<?php echo $aga_k; ?>][dateEnd]" data-buildname="listing[_ratePlans][<?php echo $k; ?>][additionalGuestAmounts][%d][dateEnd]" value="<?php echo isset($additional_guest_amount->dateEnd) ? $additional_guest_amount->dateEnd : ''; ?>" />
											</div>
										</div>

										<div class="vcm-param-container">
											<div class="vcm-param-label">Age Category</div>
											<div class="vcm-param-setting">
												<span><?php echo isset($additional_guest_amount->ageCategory) ? $additional_guest_amount->ageCategory : '-----'; ?></span>
												<input type="hidden" class="vcm-hidden-disabled" name="listing[_ratePlans][<?php echo $k; ?>][additionalGuestAmounts][<?php echo $aga_k; ?>][ageCategory]" data-buildname="listing[_ratePlans][<?php echo $k; ?>][additionalGuestAmounts][%d][ageCategory]" value="<?php echo isset($additional_guest_amount->ageCategory) ? $additional_guest_amount->ageCategory : ''; ?>" />
											</div>
										</div>

										<div class="vcm-param-container">
											<div class="vcm-param-label">Amount</div>
											<div class="vcm-param-setting">
											<?php
											if (isset($additional_guest_amount->amount) && $additional_guest_amount->amount > 0) {
												?>
												<span><?php echo $additional_guest_amount->amount; ?></span>
												<input type="hidden" class="vcm-hidden-disabled" name="listing[_ratePlans][<?php echo $k; ?>][additionalGuestAmounts][<?php echo $aga_k; ?>][amount]" data-buildname="listing[_ratePlans][<?php echo $k; ?>][additionalGuestAmounts][%d][amount]" value="<?php echo $additional_guest_amount->amount; ?>" />
												<?php
											} elseif (isset($additional_guest_amount->percent) && $additional_guest_amount->percent > 0) {
												?>
												<span><?php echo $additional_guest_amount->percent; ?>%</span>
												<input type="hidden" class="vcm-hidden-disabled" name="listing[_ratePlans][<?php echo $k; ?>][additionalGuestAmounts][<?php echo $aga_k; ?>][percent]" data-buildname="listing[_ratePlans][<?php echo $k; ?>][additionalGuestAmounts][%d][percent]" value="<?php echo $additional_guest_amount->percent; ?>" />
												<?php
											} else {
												// default to "amount"
												?>
												<span>0</span>
												<input type="hidden" class="vcm-hidden-disabled" name="listing[_ratePlans][<?php echo $k; ?>][additionalGuestAmounts][<?php echo $aga_k; ?>][amount]" data-buildname="listing[_ratePlans][<?php echo $k; ?>][additionalGuestAmounts][%d][amount]" value="0" />
												<?php
											}
											?>
											</div>
										</div>

										<div class="vcm-param-container">
											<div class="vcm-param-label"><?php echo JText::_('VCMBCAHDELETE'); ?></div>
											<div class="vcm-param-setting">
												<button type="button" class="btn btn-danger" onclick="vcmRemoveRateplanAdditionalGuestAmount(this, '<?php echo $k; ?>');"><?php VikBookingIcons::e('times-circle'); ?></button>
											</div>
										</div>

									</div>
									<?php
								}
								?>

								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label">Per Stay Service Fees</div>
									<div class="vcm-param-setting">
										<button type="button" class="btn vcm-config-btn" onclick="vcmMngRateplanPerStayServiceFees(this, '<?php echo $k; ?>');"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::_('VCMBCAHADD'); ?></button>
										<span class="vcm-param-setting-comment">List of Per Stay Service Fees that are collected at the time of Booking.</span>
									</div>
								</div>

								<div class="vcm-expediaroom-rateplan-perstayservicefees" data-rateplanindex="<?php echo $k; ?>">

								<?php
								$service_fees_per_stay = isset($room_rplan->serviceFeesPerStay) ? (array)$room_rplan->serviceFeesPerStay : [];
								foreach ($service_fees_per_stay as $sfps_k => $service_fee_per_stay) {
									if (!is_object($service_fee_per_stay) || !isset($service_fee_per_stay->isTaxable)) {
										// invalid service fee per stay object structure
										continue;
									}
									?>
									<div class="vcm-params-block vcm-expediaroom-rateplan-perstayservicefee">

										<div class="vcm-param-container">
											<div class="vcm-param-label">Per Stay Service Fee</div>
											<div class="vcm-param-setting">
												<div class="vcm-param-subsetting">
													<span>Taxable:</span>
													<span><?php echo $service_fee_per_stay->isTaxable ? JText::_('VCMYES') : JText::_('VCMNO'); ?></span>
													<input type="hidden" class="vcm-hidden-disabled" name="listing[_ratePlans][<?php echo $k; ?>][serviceFeesPerStay][<?php echo $sfps_k; ?>][isTaxable]" data-buildname="listing[_ratePlans][<?php echo $k; ?>][serviceFeesPerStay][%d][isTaxable]" value="<?php echo $service_fee_per_stay->isTaxable ? 'true' : 'false'; ?>" />
												</div>
												<div class="vcm-param-subsetting">
													<span>Amount:</span>
												<?php
												if (isset($service_fee_per_stay->amountPerNight) && $service_fee_per_stay->amountPerNight > 0) {
													?>
													<span><?php echo $service_fee_per_stay->amountPerNight; ?> (per night)</span>
													<input type="hidden" class="vcm-hidden-disabled" name="listing[_ratePlans][<?php echo $k; ?>][serviceFeesPerStay][<?php echo $sfps_k; ?>][amountPerNight]" data-buildname="listing[_ratePlans][<?php echo $k; ?>][serviceFeesPerStay][%d][amountPerNight]" value="<?php echo $service_fee_per_stay->amountPerNight; ?>" />
													<?php
												} elseif (isset($service_fee_per_stay->amountPerStay) && $service_fee_per_stay->amountPerStay > 0) {
													?>
													<span><?php echo $service_fee_per_stay->amountPerStay; ?> (per stay)</span>
													<input type="hidden" class="vcm-hidden-disabled" name="listing[_ratePlans][<?php echo $k; ?>][serviceFeesPerStay][<?php echo $sfps_k; ?>][amountPerStay]" data-buildname="listing[_ratePlans][<?php echo $k; ?>][serviceFeesPerStay][%d][amountPerStay]" value="<?php echo $service_fee_per_stay->amountPerStay; ?>" />
													<?php
												} elseif (isset($service_fee_per_stay->percent) && $service_fee_per_stay->percent > 0) {
													?>
													<span><?php echo $service_fee_per_stay->percent; ?>% (base rate)</span>
													<input type="hidden" class="vcm-hidden-disabled" name="listing[_ratePlans][<?php echo $k; ?>][serviceFeesPerStay][<?php echo $sfps_k; ?>][percent]" data-buildname="listing[_ratePlans][<?php echo $k; ?>][serviceFeesPerStay][%d][percent]" value="<?php echo $service_fee_per_stay->percent; ?>" />
													<?php
												} else {
													?>
													<span>-----</span>
													<?php
												}
												?>
												</div>
											</div>
										</div>

										<div class="vcm-param-container">
											<div class="vcm-param-label"><?php echo JText::_('VCMBCAHDELETE'); ?></div>
											<div class="vcm-param-setting">
												<button type="button" class="btn btn-danger" onclick="vcmRemoveRateplanPerStayServiceFee(this, '<?php echo $k; ?>');"><?php VikBookingIcons::e('times-circle'); ?></button>
											</div>
										</div>

									</div>
									<?php
								}
								?>

								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label">Per Person Service Fees</div>
									<div class="vcm-param-setting">
										<button type="button" class="btn vcm-config-btn" onclick="vcmMngRateplanPerPersonServiceFees(this, '<?php echo $k; ?>');"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::_('VCMBCAHADD'); ?></button>
										<span class="vcm-param-setting-comment">List of Per Person Service Fees that are collected at the time of Booking.</span>
									</div>
								</div>

								<div class="vcm-expediaroom-rateplan-perpersonservicefees" data-rateplanindex="<?php echo $k; ?>">

								<?php
								$service_fees_per_person = isset($room_rplan->serviceFeesPerPerson) ? (array)$room_rplan->serviceFeesPerPerson : [];
								foreach ($service_fees_per_person as $sfps_k => $service_fee_per_person) {
									if (!is_object($service_fee_per_person) || !isset($service_fee_per_person->isTaxable)) {
										// invalid service fee per person object structure
										continue;
									}
									?>
									<div class="vcm-params-block vcm-expediaroom-rateplan-perpersonservicefee">

										<div class="vcm-param-container">
											<div class="vcm-param-label">Per Person Service Fee</div>
											<div class="vcm-param-setting">
												<div class="vcm-param-subsetting">
													<span>Start Date:</span>
													<span><?php echo isset($service_fee_per_person->dateStart) ? $service_fee_per_person->dateStart : '-----'; ?></span>
													<input type="hidden" class="vcm-hidden-disabled" name="listing[_ratePlans][<?php echo $k; ?>][serviceFeesPerPerson][<?php echo $sfps_k; ?>][dateStart]" data-buildname="listing[_ratePlans][<?php echo $k; ?>][serviceFeesPerPerson][%d][dateStart]" value="<?php echo isset($service_fee_per_person->dateStart) ? $service_fee_per_person->dateStart : ''; ?>" />
												</div>
												<div class="vcm-param-subsetting">
													<span>End Date:</span>
													<span><?php echo isset($service_fee_per_person->dateEnd) ? $service_fee_per_person->dateEnd : '-----'; ?></span>
													<input type="hidden" class="vcm-hidden-disabled" name="listing[_ratePlans][<?php echo $k; ?>][serviceFeesPerPerson][<?php echo $sfps_k; ?>][dateEnd]" data-buildname="listing[_ratePlans][<?php echo $k; ?>][serviceFeesPerPerson][%d][dateEnd]" value="<?php echo isset($service_fee_per_person->dateEnd) ? $service_fee_per_person->dateEnd : ''; ?>" />
												</div>
												<div class="vcm-param-subsetting">
													<span>Age Category:</span>
													<span><?php echo isset($service_fee_per_person->ageCategory) ? $service_fee_per_person->ageCategory : '-----'; ?></span>
													<input type="hidden" class="vcm-hidden-disabled" name="listing[_ratePlans][<?php echo $k; ?>][serviceFeesPerPerson][<?php echo $sfps_k; ?>][ageCategory]" data-buildname="listing[_ratePlans][<?php echo $k; ?>][serviceFeesPerPerson][%d][ageCategory]" value="<?php echo isset($service_fee_per_person->ageCategory) ? $service_fee_per_person->ageCategory : ''; ?>" />
												</div>
												<div class="vcm-param-subsetting">
													<span>Taxable:</span>
													<span><?php echo $service_fee_per_person->isTaxable ? JText::_('VCMYES') : JText::_('VCMNO'); ?></span>
													<input type="hidden" class="vcm-hidden-disabled" name="listing[_ratePlans][<?php echo $k; ?>][serviceFeesPerPerson][<?php echo $sfps_k; ?>][isTaxable]" data-buildname="listing[_ratePlans][<?php echo $k; ?>][serviceFeesPerPerson][%d][isTaxable]" value="<?php echo $service_fee_per_person->isTaxable ? 'true' : 'false'; ?>" />
												</div>
												<div class="vcm-param-subsetting">
													<span>Amount:</span>
												<?php
												if (isset($service_fee_per_person->amountPerNight) && $service_fee_per_person->amountPerNight > 0) {
													?>
													<span><?php echo $service_fee_per_person->amountPerNight; ?> (per night)</span>
													<input type="hidden" class="vcm-hidden-disabled" name="listing[_ratePlans][<?php echo $k; ?>][serviceFeesPerPerson][<?php echo $sfps_k; ?>][amountPerNight]" data-buildname="listing[_ratePlans][<?php echo $k; ?>][serviceFeesPerPerson][%d][amountPerNight]" value="<?php echo $service_fee_per_person->amountPerNight; ?>" />
													<?php
												} elseif (isset($service_fee_per_person->amountPerStay) && $service_fee_per_person->amountPerStay > 0) {
													?>
													<span><?php echo $service_fee_per_person->amountPerStay; ?> (per stay)</span>
													<input type="hidden" class="vcm-hidden-disabled" name="listing[_ratePlans][<?php echo $k; ?>][serviceFeesPerPerson][<?php echo $sfps_k; ?>][amountPerStay]" data-buildname="listing[_ratePlans][<?php echo $k; ?>][serviceFeesPerPerson][%d][amountPerStay]" value="<?php echo $service_fee_per_person->amountPerStay; ?>" />
													<?php
												} else {
													?>
													<span>-----</span>
													<?php
												}
												?>
												</div>
											</div>
										</div>

										<div class="vcm-param-container">
											<div class="vcm-param-label"><?php echo JText::_('VCMBCAHDELETE'); ?></div>
											<div class="vcm-param-setting">
												<button type="button" class="btn btn-danger" onclick="vcmRemoveRateplanPerPersonServiceFee(this, '<?php echo $k; ?>');"><?php VikBookingIcons::e('times-circle'); ?></button>
											</div>
										</div>

									</div>
									<?php
								}
								?>

								</div>

								<div class="vcm-param-container vcm-param-separator">
									<div class="vcm-param-label">&nbsp;</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-setting">
										<div style="text-align: right;">
											<a class="btn btn-danger" href="index.php?option=com_vikchannelmanager&task=expediaproduct.delete_rateplan&listing_id=<?php echo $listing->get('resourceId'); ?>&rateplan_id=<?php echo $room_rplan->resourceId; ?>" onclick="return vcmConfirmDelete();"><?php VikBookingIcons::e('bomb'); ?> Delete Rate Plan</a>
										</div>
									</div>
								</div>

							</div>
							<?php
						}
						?>

						</div>

						<?php
						if ($is_editing && !$room_rate_plans) {
							?>
						<div class="vcm-param-container">
							<div class="vcm-param-setting">
								<span class="vcm-param-setting-comment">No Rate Plans found or ever downloaded. Make sure to <a href="index.php?option=com_vikchannelmanager&task=expediaproduct.reload&listing_id=<?php echo $listing->get('id'); ?>" target="_blank">reload</a> the room-type information to see what's available on Expedia for your room-type.</span>
							</div>
						</div>
							<?php
						}
						?>

						<div class="vcm-param-container">
							<div class="vcm-param-setting">
								<span class="vcm-param-setting-comment">Note: if you create new rate plans or if you delete some, do not forget to <a href="index.php?option=com_vikchannelmanager&view=roomsynch" target="_blank">synchronize your Expedia rooms</a> again, or the &quot;Bulk Action - Rates Upload&quot; will contain outdated information.</span>
							</div>
						</div>

					</div>
				</div>
			</fieldset>

			<fieldset class="adminform vcm-listings-listing-wrap" data-landto="amenities">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('icons'); ?> <?php echo JText::_('VCMBCARCROOMAMENITIES'); ?></legend>
					<div class="vcm-params-container">

						<?php
						$room_amenities = (array)$listing->get('_amenities', []);
						$active_amenity_codes = [];
						foreach ($room_amenities as $rak => $room_amenity) {
							if (is_object($room_amenity) && isset($room_amenity->code)) {
								// push amenity code to be disabled, as no duplicate values are allowed
								$active_amenity_codes[] = $room_amenity->code;
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
									foreach ($expedia_room_amenities as $amenity_code => $amenity_data) {
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
								<span class="vcm-param-setting-comment">Select the room amenities to add. For some amenity codes, a detail code or a value may be mandatory.</span>
							</div>
						</div>

						<div class="vcm-expediaroom-amenities">

						<?php
						foreach ($room_amenities as $rak => $room_amenity) {
							if (!is_object($room_amenity) || !isset($room_amenity->code)) {
								// invalid room amenity object structure
								continue;
							}
							?>
							<div class="vcm-params-block vcm-expediaroom-amenity">

								<div class="vcm-param-container">
									<div class="vcm-param-label">
										<strong><?php echo isset($expedia_room_amenities[$room_amenity->code]) ? $expedia_room_amenities[$room_amenity->code]['name'] : $room_amenity->code; ?></strong>
									<?php
									if (isset($expedia_room_amenities[$room_amenity->code]) && !empty($expedia_room_amenities[$room_amenity->code]['group'])) {
										?>
										<span class="vcm-param-setting-comment"><?php echo $expedia_room_amenities[$room_amenity->code]['group']; ?></span>
										<?php
									}
									?>
									</div>
									<div class="vcm-param-setting">
										<input type="hidden" name="listing[_amenities][<?php echo $rak; ?>][code]" data-buildname="listing[_amenities][%d][code]" class="vcm-hidden-disabled vcm-expedia-amenity-code" value="<?php echo $this->escape($room_amenity->code); ?>" onchange="vcmEnableRoomAmenities();" />
										<button type="button" class="btn btn-danger" onclick="vcmRemoveRoomAmenity(this);"><?php VikBookingIcons::e('times-circle'); ?></button>
									</div>
								</div>
							<?php
							if (isset($expedia_room_amenities[$room_amenity->code]) && $expedia_room_amenities[$room_amenity->code]['detailCodes']) {
								$current_amenity_detail = isset($room_amenity->detailCode) ? $room_amenity->detailCode : '';
								?>
								<div class="vcm-param-container vcm-param-nested">
									<div class="vcm-param-label">Detail code</div>
									<div class="vcm-param-setting">
										<select name="listing[_amenities][<?php echo $rak; ?>][detailCode]" data-buildname="listing[_amenities][%d][detailCode]" onchange="vcmEnableRoomAmenities();">
											<option value=""></option>
										<?php
										foreach ($expedia_room_amenities[$room_amenity->code]['detailCodes'] as $detail_code => $detail_name) {
											?>
											<option value="<?php echo $this->escape($detail_code); ?>"<?php echo !strcasecmp($detail_code, $current_amenity_detail) ? ' selected="selected"' : ''; ?>><?php echo $detail_name; ?></option>
											<?php
										}
										?>
										</select>
									</div>
								</div>
								<?php
							}
							if (isset($expedia_room_amenities[$room_amenity->code]) && $expedia_room_amenities[$room_amenity->code]['valueType']) {
								$current_amenity_value = isset($room_amenity->value) ? $room_amenity->value : '';
								$field_attrs = isset($expedia_room_amenities[$room_amenity->code]['valueType']['attributes']) ? $expedia_room_amenities[$room_amenity->code]['valueType']['attributes'] : [];
								$field_attrs_list = [];
								foreach ($field_attrs as $attr_k => $attr_v) {
									$field_attrs_list[] = $attr_k . '="' . htmlspecialchars($attr_v) . '"';
								}
								$field_type = isset($expedia_room_amenities[$room_amenity->code]['valueType']['type']) ? $expedia_room_amenities[$room_amenity->code]['valueType']['type'] : 'text';
								?>
								<div class="vcm-param-container vcm-param-nested">
									<div class="vcm-param-label"><?php echo JText::_('VCMBCARCVALUE'); ?></div>
									<div class="vcm-param-setting">
										<input type="<?php echo $field_type; ?>" value="<?php echo $this->escape($current_amenity_value); ?>" name="listing[_amenities][<?php echo $rak; ?>][value]" data-buildname="listing[_amenities][%d][value]" onchange="vcmEnableRoomAmenities();" <?php echo implode(' ', $field_attrs_list); ?>/>
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

<a class="vcm-hidden-refresh-url" href="index.php?option=com_vikchannelmanager&view=expediamngproduct&idroomota=%s" style="display: none;"></a>
<a class="vcm-hidden-list-url" href="index.php?option=com_vikchannelmanager&view=expediaproducts&loaded=1" style="display: none;"></a>

<div class="vcm-expediaroom-html-helpers" style="display: none;">

	<div class="vcm-expediaroom-rplan-addguestamount-helper">

		<div class="vcm-params-container">

			<div class="vcm-param-container">
				<div class="vcm-param-label">Start Date</div>
				<div class="vcm-param-setting">
					<?php echo $vbo_app->getCalendar('', 'addguestamount-startdate', 'addguestamount-startdate', '%Y-%m-%d'); ?>
					<span class="vcm-param-setting-comment">Date (Y-m-d) at which this amount started being applicable. Can be omitted.</span>
				</div>
			</div>

			<div class="vcm-param-container">
				<div class="vcm-param-label">End Date</div>
				<div class="vcm-param-setting">
					<?php echo $vbo_app->getCalendar('', 'addguestamount-enddate', 'addguestamount-enddate', '%Y-%m-%d'); ?>
					<span class="vcm-param-setting-comment">Date (Y-m-d) until which this amount will be applied. Can be omitted, and if no end date is defined, will be returned as 2079-06-06.</span>
				</div>
			</div>

			<div class="vcm-param-container">
				<div class="vcm-param-label">Age Category</div>
				<div class="vcm-param-setting">
					<select id="addguestamount-agecategory">
					<?php
					foreach (VCMExpediaProduct::getAgeCategories() as $age_category_code) {
						?>
						<option value="<?php echo $this->escape($age_category_code); ?>"><?php echo $age_category_code; ?></option>
						<?php
					}
					?>
					</select>
				</div>
			</div>

			<div class="vcm-param-container">
				<div class="vcm-param-label">Amount</div>
				<div class="vcm-param-setting">
					<input type="number" id="addguestamount-amount" value="" step="any" />
					<span class="vcm-param-setting-comment">If a fixed amount, up to 3 decimals are accepted. If percent, value should be between 0 and 1 (i.e. 15% = 0.15).</span>
				</div>
			</div>

			<div class="vcm-param-container">
				<div class="vcm-param-label">Fixed or Percent</div>
				<div class="vcm-param-setting">
					<select id="addguestamount-amount-type">
						<option value="fixed">Fixed</option>
						<option value="percent">Percent (%)</option>
					</select>
				</div>
			</div>

		</div>

	</div>

	<div class="vcm-expediaroom-rplan-perstayservicefee-helper">

		<div class="vcm-params-container">

			<div class="vcm-param-container">
				<div class="vcm-param-label">Taxable</div>
				<div class="vcm-param-setting">
					<select id="perstayservicefee-taxable">
						<option value="1"><?php echo JText::_('VCMYES'); ?></option>
						<option value="0"><?php echo JText::_('VCMNO'); ?></option>
					</select>
				</div>
			</div>

			<div class="vcm-param-container">
				<div class="vcm-param-label">Amount</div>
				<div class="vcm-param-setting">
					<input type="number" id="perstayservicefee-amount" value="" step="any" />
					<span class="vcm-param-setting-comment">In case of fee type &quot;Percent&quot;, value should be between 0 and 1 (i.e. 15% = 0.15).</span>
				</div>
			</div>

			<div class="vcm-param-container">
				<div class="vcm-param-label">Fee Type</div>
				<div class="vcm-param-setting">
					<select id="perstayservicefee-amount-type">
						<option value="amountPerNight">Per Night</option>
						<option value="amountPerStay">Per Stay</option>
						<option value="percent">Percent (% base rate)</option>
					</select>
				</div>
			</div>

		</div>

	</div>

	<div class="vcm-expediaroom-rplan-perpersonservicefee-helper">

		<div class="vcm-params-container">

			<div class="vcm-param-container">
				<div class="vcm-param-label">Start Date</div>
				<div class="vcm-param-setting">
					<?php echo $vbo_app->getCalendar('', 'perpersonservicefee-startdate', 'perpersonservicefee-startdate', '%Y-%m-%d'); ?>
					<span class="vcm-param-setting-comment">Date (Y-m-d) at which this amount started being applicable. Can be omitted.</span>
				</div>
			</div>

			<div class="vcm-param-container">
				<div class="vcm-param-label">End Date</div>
				<div class="vcm-param-setting">
					<?php echo $vbo_app->getCalendar('', 'perpersonservicefee-enddate', 'perpersonservicefee-enddate', '%Y-%m-%d'); ?>
					<span class="vcm-param-setting-comment">Date (Y-m-d) until which this amount will be applied. Can be omitted, and if no end date is defined, will be returned as 2079-06-06.</span>
				</div>
			</div>

			<div class="vcm-param-container">
				<div class="vcm-param-label">Age Category</div>
				<div class="vcm-param-setting">
					<select id="perpersonservicefee-agecategory">
					<?php
					foreach (VCMExpediaProduct::getAgeCategories() as $age_category_code) {
						?>
						<option value="<?php echo $this->escape($age_category_code); ?>"><?php echo $age_category_code; ?></option>
						<?php
					}
					?>
					</select>
				</div>
			</div>

			<div class="vcm-param-container">
				<div class="vcm-param-label">Taxable</div>
				<div class="vcm-param-setting">
					<select id="perpersonservicefee-taxable">
						<option value="1"><?php echo JText::_('VCMYES'); ?></option>
						<option value="0"><?php echo JText::_('VCMNO'); ?></option>
					</select>
				</div>
			</div>

			<div class="vcm-param-container">
				<div class="vcm-param-label">Amount</div>
				<div class="vcm-param-setting">
					<input type="number" id="perpersonservicefee-amount" value="" step="any" />
				</div>
			</div>

			<div class="vcm-param-container">
				<div class="vcm-param-label">Fee Type</div>
				<div class="vcm-param-setting">
					<select id="perpersonservicefee-amount-type">
						<option value="amountPerNight">Per Night</option>
						<option value="amountPerStay">Per Stay</option>
					</select>
				</div>
			</div>

		</div>

	</div>

	<div class="vcm-expediaroom-rplan-addnew-helper">

		<div class="vcm-params-container">

			<?php
			// suggest unique rate plan name
			$new_rplan_sugg_name = 'Standard Rate';
			if (in_array($new_rplan_sugg_name, $all_rate_plan_names)) {
				$new_rplan_sugg_name .= ' ' . (count($all_rate_plan_names) + 1);
			}
			// suggest unique partner code for the distribution rule ExpediaCollect
			$new_rplan_sugg_code_ec = preg_replace("/[^BCDFGHJKLMNPQRSTVWXYZ0-9]+/", '', strtoupper($new_rplan_sugg_name));
			$new_rplan_sugg_code_ec .= '_EC' . (count($all_rate_plan_ec_codes) + 1);
			$new_rplan_sugg_code_ec = strlen($new_rplan_sugg_code_ec) > 10 ? substr($new_rplan_sugg_code_ec, -10) : $new_rplan_sugg_code_ec;
			// suggest unique partner code for the distribution rule HotelCollect
			$new_rplan_sugg_code_hc = preg_replace("/[^BCDFGHJKLMNPQRSTVWXYZ0-9]+/", '', strtoupper($new_rplan_sugg_name));
			$new_rplan_sugg_code_hc .= '_HC' . (count($all_rate_plan_hc_codes) + 1);
			$new_rplan_sugg_code_hc = strlen($new_rplan_sugg_code_hc) > 10 ? substr($new_rplan_sugg_code_hc, -10) : $new_rplan_sugg_code_hc;
			?>
			<div class="vcm-param-container">
				<div class="vcm-param-label">Name</div>
				<div class="vcm-param-setting">
					<input type="text" id="addrateplan-name" value="<?php echo $this->escape($new_rplan_sugg_name); ?>" placeholder="<?php echo $this->escape($new_rplan_sugg_name); ?>" maxlength="40" />
					<span class="vcm-param-setting-comment">Name of the rate plan, for information/identification purposes. Minimum 1, maximum 40 characters.</span>
				</div>
			</div>

			<div class="vcm-param-container">
				<div class="vcm-param-label">Distribution Rule(s)</div>
				<div class="vcm-param-setting">
					<span class="vcm-param-setting-comment">Used to provide information about how this rate plan can be sold (Expedia Collect, Hotel Collect or both).</span>
				</div>
			</div>

			<div class="vcm-param-container">
				<div class="vcm-param-label">
					<span><strong>Expedia Collect</strong> Model</span>
					<div>Rate Plan Identifier</div>
				</div>
				<div class="vcm-param-setting">
					<?php echo $vbo_app->printYesNoButtons('addrateplan_ec_model', JText::_('VCMYES'), JText::_('VCMNO'), 0, 1, 0, 'vcmToggleDistrModel(this.checked, \'ec\')'); ?>
					<div class="addrateplan-ec-model" style="display: none;">
						<input type="text" id="addrateplan-ec-pcode" value="<?php echo $this->escape($new_rplan_sugg_code_ec); ?>" placeholder="<?php echo $this->escape($new_rplan_sugg_code_ec); ?>" pattern="[A-Za-z0-9_.\-]{1,10}" maxlength="10" />
						<span class="vcm-param-setting-comment">If provided, it indicates that this rate plan will be sold as Expedia Collect and the property will collect payments from Expedia.</span>
					</div>
				</div>
			</div>

			<div class="vcm-param-container">
				<div class="vcm-param-label">
					<span><strong>Hotel Collect</strong> Model</span>
					<div>Rate Plan Identifier</div>
				</div>
				<div class="vcm-param-setting">
					<?php echo $vbo_app->printYesNoButtons('addrateplan_hc_model', JText::_('VCMYES'), JText::_('VCMNO'), 0, 1, 0, 'vcmToggleDistrModel(this.checked, \'hc\')'); ?>
					<div class="addrateplan-hc-model" style="display: none;">
						<input type="text" id="addrateplan-hc-pcode" value="<?php echo $this->escape($new_rplan_sugg_code_hc); ?>" placeholder="<?php echo $this->escape($new_rplan_sugg_code_hc); ?>" pattern="[A-Za-z0-9_.\-]{1,10}" maxlength="10" />
						<span class="vcm-param-setting-comment">If provided, the rate plan will be sold as Hotel Collect and the property is expected to collect the payment at the time customers check in.</span>
					</div>
				</div>
			</div>

			<?php
			// properties on PDP (PerDayPricing) model must pass the "occupants for base rate" when creating a new rate plan
			$prop_pricing_model = $property_data->get('pricingModel', '');
			if (!strcasecmp($prop_pricing_model, 'PerDayPricing')) {
				?>
			<div class="vcm-param-container">
				<div class="vcm-param-label">Occupants for base rate</div>
				<div class="vcm-param-setting">
					<input type="number" id="addrateplan-occupantsForBaseRate" value="1" min="1" max="20" />
					<span class="vcm-param-setting-comment">Maximum occupants allowed for the base rate. Minimum 1, Maximum 20. This is only applicable for per-day pricing properties, and it indicates how many occupants the per-day price applies to.</span>
				</div>
			</div>
				<?php
			}
			?>

			<div class="vcm-param-container">
				<div class="vcm-param-setting">
					<span class="vcm-param-setting-comment">If two distinct Distribution Rules are set, one for Expedia Collect, one for Hotel Collect, it indicates that the rate plan can be sold as either Expedia Collect or Hotel Collect. In this case, only one of them can be used to manage availability and rates but both can be used in booking messages, to indicate which option (Expedia Collect or Hotel Collect) the customer selected.</span>
				</div>
			</div>

		</div>

	</div>

</div>

<script type="text/javascript">
var vcm_expedia_bed_types = <?php echo json_encode($expedia_bed_types); ?>;
var vcm_expedia_bed_type_sizes = <?php echo json_encode($expedia_bed_type_sizes); ?>;
var vcm_expedia_bed_surcharges = <?php echo json_encode($expedia_bed_surcharges); ?>;
var vcm_expedia_room_amenities = <?php echo json_encode($expedia_room_amenities); ?>;

/* Display Loading Overlay */
function vcmShowLoading() {
	jQuery(".vcm-loading-overlay").show();
}

/* Hide Loading Overlay */
function vcmStopLoading() {
	jQuery(".vcm-loading-overlay").hide();
}

/* Handle some requests through AJAX */
Joomla.submitbutton = function(task) {
	if (task == 'expediaproduct.savelisting' || task == 'expediaproduct.updatelisting' || task == 'expediaproduct.updatelisting_stay') {
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
				if (task == 'expediaproduct.updatelisting') {
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
 * Adds a new bedroom container.
 */
function vcmAddBedroom() {
	var bedroom_elem = jQuery('.vcm-expedia-bedrooms-clone-copies').find('.vcm-expediaroom-bedroom').clone(true, true);
	jQuery('.vcm-expediaroom-bedrooms').append(bedroom_elem);
	// update the name attributes of every element
	vcmResetBedroomNaming();
}

/**
 * Removes the clicked bedroom container.
 */
function vcmRemoveBedroom(elem) {
	jQuery(elem).closest('.vcm-expediaroom-bedroom').remove();
	// update the name attributes of every element
	vcmResetBedroomNaming();
}

/**
 * Adds a new bedding option container to a bedroom.
 */
function vcmAddBedroomBed(elem, option_num) {
	var wrapper = jQuery(elem).closest('.vcm-expediaroom-bedroom').find('.vcm-expediaroom-bedroom-option-beds[data-bedoption="' + option_num + '"]');
	if (!wrapper || !wrapper.length) {
		return false;
	}
	var bed_elem = jQuery('.vcm-expedia-bedrooms-clone-copies').find('.vcm-expediaroom-bedroom-option-beds[data-bedoption="' + option_num + '"]').find('.vcm-expediaroom-bedroom-option-bed').clone(true, true);
	wrapper.append(bed_elem);
	// update the name attributes of every element
	vcmResetBedroomNaming();
}

/**
 * Removes the clicked bedding option container from a bedroom.
 */
function vcmRemoveBedroomBed(elem) {
	jQuery(elem).closest('.vcm-expediaroom-bedroom-option-bed').remove();
	// update the name attributes of every element
	vcmResetBedroomNaming();
}

/**
 * Triggers when the bed type changes to populate the supported bed sizes of this type.
 */
function vboSetBedSize(elem) {
	var bed_type = elem.value;
	var bed_elem = jQuery(elem).closest('.vcm-expediaroom-bedroom-option-bed');
	var bed_size_sel = bed_elem.find('select[data-bedinfo="size"]');
	var prev_bed_size_val = bed_size_sel.attr('data-currentbedsize') || '';
	bed_size_sel.html('<option value=""></option>');
	if (bed_type.length && vcm_expedia_bed_type_sizes.hasOwnProperty(bed_type)) {
		for (let i = 0; i < vcm_expedia_bed_type_sizes[bed_type].length; i++) {
			bed_size_sel.append('<option value="' + vcm_expedia_bed_type_sizes[bed_type][i] + '"' + (prev_bed_size_val == vcm_expedia_bed_type_sizes[bed_type][i] ? ' selected="selected"' : '') + '>' + vcm_expedia_bed_type_sizes[bed_type][i] + '</option>');
		}
		// make sure to enable all input fields for the bedrooms
		vcmEnableBedroomFields();
	}
}

/**
 * Every time a modification is made, the name attribute of any input element must be renamed.
 */
function vcmResetBedroomNaming() {
	jQuery('.vcm-expediaroom-bedrooms').find('[data-buildname]').each(function(k, v) {
		var elem = jQuery(this);
		var buildname = elem.attr('data-buildname');
		var replacements = (buildname.match(/%d/g) || []).length;
		if (!replacements) {
			return;
		}
		// set proper bedroom index (first wildcard %d)
		var bedroom_elem = elem.closest('.vcm-expediaroom-bedroom');
		var bedroom_index = jQuery('.vcm-expediaroom-bedrooms').find('.vcm-expediaroom-bedroom').index(bedroom_elem);
		buildname = buildname.replace('%d', bedroom_index);
		if (replacements > 1) {
			// set proper bed index (second wildcard %d)
			var bed_elem = elem.closest('.vcm-expediaroom-bedroom-option-bed');
			var bed_index = elem.closest('.vcm-expediaroom-bedroom-option-beds').find('.vcm-expediaroom-bedroom-option-bed').index(bed_elem);
			buildname = buildname.replace('%d', bed_index);
		}
		// set correct name attribute
		elem.attr('name', buildname);
	});
	// make sure to enable all input fields for the bedrooms
	vcmEnableBedroomFields();
}

/**
 * Modularity over the bedrooms is impossible to be achieved, as that's a whole object for the room.
 */
function vcmEnableBedroomFields() {
	jQuery('.vcm-expediaroom-bedrooms').find('.vcm-param-container-tmp-disabled').removeClass('vcm-param-container-tmp-disabled').find('input, select').prop('disabled', false);
}

/**
 * Modularity over the max occupancy is impossible to be achieved, as that's a whole object for the room.
 */
function vcmEnableMaxOccupancy() {
	jQuery('.vcm-expediaroom-maxoccupancy').find('.vcm-param-container-tmp-disabled').removeClass('vcm-param-container-tmp-disabled').find('input, select').prop('disabled', false);
}

/**
 * Modularity over the extra beds is impossible to be achieved, as that's a whole object for the room.
 */
function vcmEnableExtrabedFields() {
	jQuery('.vcm-expediaroom-extrabeds').find('.vcm-param-container-tmp-disabled:visible').removeClass('vcm-param-container-tmp-disabled').find('input, select').prop('disabled', false);
}

/**
 * Triggers when the extra bed type changes to populate the supported bed sizes of this type.
 */
function vboSetExtrabedSize(elem) {
	// perform the regular population of proper sizes
	var extrabed_type = elem.value;
	var extrabed_elem = jQuery(elem).closest('.vcm-expediaroom-extrabed');
	var bed_size_sel = extrabed_elem.find('select[data-bedinfo="size"]');
	var prev_bed_size_val = bed_size_sel.attr('data-currentbedsize') || '';
	bed_size_sel.html('<option value=""></option>');
	if (extrabed_type.length && vcm_expedia_bed_type_sizes.hasOwnProperty(extrabed_type)) {
		for (let i = 0; i < vcm_expedia_bed_type_sizes[extrabed_type].length; i++) {
			bed_size_sel.append('<option value="' + vcm_expedia_bed_type_sizes[extrabed_type][i] + '"' + (prev_bed_size_val == vcm_expedia_bed_type_sizes[extrabed_type][i] ? ' selected="selected"' : '') + '>' + vcm_expedia_bed_type_sizes[extrabed_type][i] + '</option>');
		}
		// make sure to enable all input fields for the extra beds
		vcmEnableExtrabedFields();
	}
	// check for surcharges support, which depends on the bed type
	if (vcm_expedia_bed_surcharges.hasOwnProperty(extrabed_type)) {
		// this type of extra bed requires the surcharge definitions
		extrabed_elem.find('.vcm-expedia-extrabed-surcharge-field').show();
	} else {
		// no surcharge supported by this extra bed type
		extrabed_elem.find('.vcm-expedia-extrabed-surcharge-field').hide().find('input, select').prop('disabled', true).closest('.vcm-param-container').addClass('vcm-param-container-tmp-disabled');
	}
}

/**
 * Every time a modification is made, the name attribute of any input element must be renamed.
 */
function vcmResetExtrabedNaming() {
	jQuery('.vcm-expediaroom-extrabeds').find('[data-buildname]').each(function(k, v) {
		var elem = jQuery(this);
		var buildname = elem.attr('data-buildname');
		var replacements = (buildname.match(/%d/g) || []).length;
		if (!replacements) {
			return;
		}
		// set proper extrabed index (first wildcard %d)
		var extrabed_elem = elem.closest('.vcm-expediaroom-extrabed');
		var extrabed_index = jQuery('.vcm-expediaroom-extrabeds').find('.vcm-expediaroom-extrabed').index(extrabed_elem);
		buildname = buildname.replace('%d', extrabed_index);
		// set correct name attribute
		elem.attr('name', buildname);
	});
	// make sure to enable all input fields for the extra beds
	vcmEnableExtrabedFields();
}

/**
 * Adds a new extra bed.
 */
function vcmAddExtrabed() {
	var wrapper = jQuery('.vcm-expediaroom-extrabeds');
	var extrabed_elem = jQuery('.vcm-expedia-extrabed-clone-copy').find('.vcm-expediaroom-extrabed').clone(true, true);
	wrapper.append(extrabed_elem);
	// update the name attributes of every element
	vcmResetExtrabedNaming();
}

/**
 * Removes the clicked extra bed container.
 */
function vcmRemoveExtrabed(elem) {
	jQuery(elem).closest('.vcm-expediaroom-extrabed').remove();
	// update the name attributes of every element
	vcmResetExtrabedNaming();
}

/**
 * Fires when an element to compose the room name through attributes is selected.
 */
function vcmRoomNameAttributesChosen(elem) {
	// disable any element to compose the room name with the opposite method
	jQuery('select.vcm-expedia-roomname-value, input.vcm-expedia-roomname-value').prop('disabled', true).closest('.vcm-param-container').addClass('vcm-param-container-tmp-disabled');
	// room name attributes are a lot, and we need to enable them all (do not trigger any change event to avoid loops)
	jQuery('select.vcm-expedia-roomname-attribute, input.vcm-expedia-roomname-attribute').prop('disabled', false).closest('.vcm-param-container').removeClass('vcm-param-container-tmp-disabled');
}

/**
 * Fires when an element to compose the room name through predefined value is selected.
 */
function vcmRoomNameValueChosen(elem) {
	// disable any element to compose the room name with the opposite method
	jQuery('select.vcm-expedia-roomname-attribute, input.vcm-expedia-roomname-attribute').prop('disabled', true).closest('.vcm-param-container').addClass('vcm-param-container-tmp-disabled');
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
	if (!vcm_expedia_room_amenities.hasOwnProperty(amenity_code)) {
		alert('Invalid amenity');
		return false;
	}
	var amenities_wrapper = jQuery('.vcm-expediaroom-amenities');
	var amenity_html = '';
	amenity_html += '<div class="vcm-params-block vcm-expediaroom-amenity">' + "\n";
	amenity_html += '<div class="vcm-param-container">' + "\n";
	amenity_html += '	<div class="vcm-param-label">' + "\n";
	amenity_html += '		<strong>' + vcm_expedia_room_amenities[amenity_code]['name'] + '</strong>' + "\n";
	if (vcm_expedia_room_amenities[amenity_code]['group']) {
		amenity_html += '	<span class="vcm-param-setting-comment">' + vcm_expedia_room_amenities[amenity_code]['group'] + '</span>' + "\n";
	}
	amenity_html += '	</div>' + "\n";
	amenity_html += '	<div class="vcm-param-setting">' + "\n";
	amenity_html += '		<input type="hidden" name="" data-buildname="listing[_amenities][%d][code]" value="' + amenity_code + '" class="vcm-expedia-amenity-code" />' + "\n";
	amenity_html += '		<button type="button" class="btn btn-danger" onclick="vcmRemoveRoomAmenity(this);"><?php VikBookingIcons::e('times-circle'); ?></button>' + "\n";
	amenity_html += '	</div>' + "\n";
	amenity_html += '</div>' + "\n";
	if (vcm_expedia_room_amenities[amenity_code]['detailCodes']) {
		amenity_html += '<div class="vcm-param-container vcm-param-nested">' + "\n";
		amenity_html += '	<div class="vcm-param-label">Detail code</div>' + "\n";
		amenity_html += '	<div class="vcm-param-setting">' + "\n";
		amenity_html += '		<select name="" data-buildname="listing[_amenities][%d][detailCode]" class="vcm-listing-editable">' + "\n";
		amenity_html += '			<option value=""></option>' + "\n";
		for (var am_dt_code in vcm_expedia_room_amenities[amenity_code]['detailCodes']) {
			if (!vcm_expedia_room_amenities[amenity_code]['detailCodes'].hasOwnProperty(am_dt_code)) {
				continue;
			}
			amenity_html += '		<option value="' + am_dt_code + '">' + vcm_expedia_room_amenities[amenity_code]['detailCodes'][am_dt_code] + '</option>' + "\n";
		}
		amenity_html += '		</select>' + "\n";
		amenity_html += '	</div>' + "\n";
		amenity_html += '</div>' + "\n";
	}
	if (vcm_expedia_room_amenities[amenity_code]['valueType']) {
		var field_type = vcm_expedia_room_amenities[amenity_code]['valueType'].hasOwnProperty('type') ? vcm_expedia_room_amenities[amenity_code]['valueType']['type'] : 'text';
		var field_attrs_list = [];
		if (vcm_expedia_room_amenities[amenity_code]['valueType'].hasOwnProperty('attributes')) {
			for (var field_attr in vcm_expedia_room_amenities[amenity_code]['valueType']['attributes']) {
				if (!vcm_expedia_room_amenities[amenity_code]['valueType']['attributes'].hasOwnProperty(field_attr)) {
					continue;
				}
				field_attrs_list.push(field_attr + '="' + vcm_expedia_room_amenities[amenity_code]['valueType']['attributes'][field_attr] + '"');
			}
		}
		amenity_html += '<div class="vcm-param-container vcm-param-nested">' + "\n";
		amenity_html += '	<div class="vcm-param-label">' + Joomla.JText._('VCMBCARCVALUE') + '</div>' + "\n";
		amenity_html += '	<div class="vcm-param-setting">' + "\n";
		amenity_html += '		<input type="' + field_type + '" name="" data-buildname="listing[_amenities][%d][value]" class="vcm-listing-editable" ' + field_attrs_list.join(' ') + '/>' + "\n";
		amenity_html += '	</div>' + "\n";
		amenity_html += '</div>' + "\n";
	}
	amenity_html += '</div>' + "\n";
	// append amenity elements
	amenities_wrapper.append(amenity_html);
	// animate scroll to that position
	jQuery('html,body').animate({scrollTop: jQuery('.vcm-expediaroom-amenity').last().offset().top - 40}, {duration: 400});
	// update the name attributes of every element
	vcmResetAmenitiesNaming();
}

/**
 * Removes a room amenity block.
 */
function vcmRemoveRoomAmenity(elem) {
	var amenity_block = jQuery(elem).closest('.vcm-expediaroom-amenity');
	var amenity_code = amenity_block.find('input.vcm-expedia-amenity-code').val();
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
	jQuery('.vcm-expediaroom-amenities').find('[data-buildname]').each(function(k, v) {
		var elem = jQuery(this);
		var buildname = elem.attr('data-buildname');
		var replacements = (buildname.match(/%d/g) || []).length;
		if (!replacements) {
			return;
		}
		// set proper amenity index (first wildcard %d)
		var amenity_elem = elem.closest('.vcm-expediaroom-amenity');
		var amenity_index = jQuery('.vcm-expediaroom-amenities').find('.vcm-expediaroom-amenity').index(amenity_elem);
		buildname = buildname.replace('%d', amenity_index);
		// set correct name attribute
		elem.attr('name', buildname);
	});
	// make sure to enable all input fields
	vcmEnableRoomAmenities();
}

/**
 * Enables all fields of the room amenities after a single change.
 */
function vcmEnableRoomAmenities() {
	jQuery('.vcm-expediaroom-amenities').find('.vcm-param-container-tmp-disabled').removeClass('vcm-param-container-tmp-disabled').find('input, select').prop('disabled', false);
}

/**
 * Enables all fields of the age categories after a single change.
 */
function vcmEnableAgeCategories() {
	jQuery('.vcm-expediaroom-agecategories').find('.vcm-param-container-tmp-disabled').removeClass('vcm-param-container-tmp-disabled').find('input, select').prop('disabled', false);
}

/**
 * Enables all fields of a room rate plan cancellation penalties.
 */
function vcmEnableRplanCancPenalties(elem) {
	jQuery(elem).closest('.vcm-expediaroom-rateplan-cancpenalties').find('.vcm-param-container-tmp-disabled').removeClass('vcm-param-container-tmp-disabled').find('input, select').prop('disabled', false);
}

/**
 * Display modal window to add a new additional guest amount to a rate plan.
 */
function vcmMngRateplanAdditionalGuestAmount(elem, rplan_index) {
	// build the button to handle the adding of the values
	var btn_apply_additional_guest_amount = jQuery('<button></button>').attr('type', 'button').addClass('btn btn-success').html('<?php VikBookingIcons::e('plus-circle'); ?> Additional Guest Amount');
	btn_apply_additional_guest_amount.on('click', function() {
		// collect fields
		var start_date = jQuery('#addguestamount-startdate').val();
		var end_date = jQuery('#addguestamount-enddate').val();
		var age_category = jQuery('#addguestamount-agecategory').val();
		var amount = jQuery('#addguestamount-amount').val();
		var amount_type = jQuery('#addguestamount-amount-type').val();
		if (!amount || !amount.length) {
			alert('Invalid amount. Cannot be empty and decimals must be properly formatted with a dot.');
			return false;
		}
		// build new HTML content
		var addguestamount_html = '';
		addguestamount_html += '<div class="vcm-params-block vcm-expediaroom-rateplan-additionalguestamount">' + "\n";
		addguestamount_html += '	<div class="vcm-param-container">' + "\n";
		addguestamount_html += '		<div class="vcm-param-label">Start Date</div>' + "\n";
		addguestamount_html += '		<div class="vcm-param-setting">' + "\n";
		addguestamount_html += '			<span>' + start_date + '</span>' + "\n";
		addguestamount_html += '			<input type="hidden" data-buildname="listing[_ratePlans][' + rplan_index + '][additionalGuestAmounts][%d][dateStart]" value="' + start_date + '" />' + "\n";
		addguestamount_html += '		</div>' + "\n";
		addguestamount_html += '	</div>' + "\n";
		addguestamount_html += '	<div class="vcm-param-container">' + "\n";
		addguestamount_html += '		<div class="vcm-param-label">End Date</div>' + "\n";
		addguestamount_html += '		<div class="vcm-param-setting">' + "\n";
		addguestamount_html += '			<span>' + end_date + '</span>' + "\n";
		addguestamount_html += '			<input type="hidden" data-buildname="listing[_ratePlans][' + rplan_index + '][additionalGuestAmounts][%d][dateEnd]" value="' + end_date + '" />' + "\n";
		addguestamount_html += '		</div>' + "\n";
		addguestamount_html += '	</div>' + "\n";
		addguestamount_html += '	<div class="vcm-param-container">' + "\n";
		addguestamount_html += '		<div class="vcm-param-label">Age Category</div>' + "\n";
		addguestamount_html += '		<div class="vcm-param-setting">' + "\n";
		addguestamount_html += '			<span>' + age_category + '</span>' + "\n";
		addguestamount_html += '			<input type="hidden" data-buildname="listing[_ratePlans][' + rplan_index + '][additionalGuestAmounts][%d][ageCategory]" value="' + age_category + '" />' + "\n";
		addguestamount_html += '		</div>' + "\n";
		addguestamount_html += '	</div>' + "\n";
		addguestamount_html += '	<div class="vcm-param-container">' + "\n";
		addguestamount_html += '		<div class="vcm-param-label">Amount</div>' + "\n";
		addguestamount_html += '		<div class="vcm-param-setting">' + "\n";
		addguestamount_html += '			<span>' + amount + (amount_type == 'percent' ? '%' : '') + '</span>' + "\n";
		addguestamount_html += '			<input type="hidden" data-buildname="listing[_ratePlans][' + rplan_index + '][additionalGuestAmounts][%d][' + (amount_type == 'percent' ? 'percent' : 'amount') + ']" value="' + amount + '" />' + "\n";
		addguestamount_html += '		</div>' + "\n";
		addguestamount_html += '	</div>' + "\n";
		addguestamount_html += '	<div class="vcm-param-container">' + "\n";
		addguestamount_html += '		<div class="vcm-param-label">' + Joomla.JText._('VCMBCAHDELETE') + '</div>' + "\n";
		addguestamount_html += '		<div class="vcm-param-setting">' + "\n";
		addguestamount_html += '			<button type="button" class="btn btn-danger" onclick="vcmRemoveRateplanAdditionalGuestAmount(this, \'' + rplan_index + '\');"><?php VikBookingIcons::e('times-circle'); ?></button>' + "\n";
		addguestamount_html += '		</div>' + "\n";
		addguestamount_html += '	</div>' + "\n";
		addguestamount_html += '</div>' + "\n";
		// append values
		jQuery('.vcm-expediaroom-rateplan-additionalguestamounts[data-rateplanindex="' + rplan_index + '"]').append(addguestamount_html);
		// update the name attributes of every element
		vcmResetAdditionalGuestAmountsNaming(elem, rplan_index);
		// dismiss the modal
		VBOCore.emitEvent('close-rateplan-additional-guest-amount');
	});

	// render modal
	var modal_wrapper = VBOCore.displayModal({
		suffix: 'rateplan-additional-guest-amount',
		title: 'New Additional Guest Amount',
		body_prepend: true,
		footer_right: btn_apply_additional_guest_amount,
		dismiss_event: 'close-rateplan-additional-guest-amount',
		onDismiss: () => {
			// move HTML helper back to its location
			jQuery('.vcm-expediaroom-rplan-addguestamount-helper').appendTo(jQuery('.vcm-expediaroom-html-helpers'));
		},
	});

	// append content to modal
	jQuery('.vcm-expediaroom-rplan-addguestamount-helper').appendTo(modal_wrapper);
}

/**
 * Removes the clicked additional guest amount from a rate plan.
 */
function vcmRemoveRateplanAdditionalGuestAmount(elem, rplan_index) {
	jQuery(elem).closest('.vcm-expediaroom-rateplan-additionalguestamount').remove();
	// update the name attributes of every element
	vcmResetAdditionalGuestAmountsNaming(elem, rplan_index);
}

/**
 * Every time a modification is made, the name attribute of any input element must be renamed.
 */
function vcmResetAdditionalGuestAmountsNaming(btn, rplan_index) {
	var wrapper;
	if (typeof rplan_index !== 'undefined') {
		wrapper = jQuery('.vcm-expediaroom-rateplan-additionalguestamounts[data-rateplanindex="' + rplan_index + '"]');
	} else {
		wrapper = jQuery(btn).closest('.vcm-expediaroom-rateplan-additionalguestamounts');
	}
	wrapper.find('[data-buildname]').each(function(k, v) {
		var elem = jQuery(this);
		var buildname = elem.attr('data-buildname');
		var replacements = (buildname.match(/%d/g) || []).length;
		if (!replacements) {
			return;
		}
		// set proper index (first wildcard %d)
		var block_elem = elem.closest('.vcm-expediaroom-rateplan-additionalguestamount');
		var block_index = jQuery('.vcm-expediaroom-rateplan-additionalguestamounts').find('.vcm-expediaroom-rateplan-additionalguestamount').index(block_elem);
		buildname = buildname.replace('%d', block_index);
		// set correct name attribute
		elem.attr('name', buildname);
	});
	// make sure to enable all input fields
	vcmEnableAdditionalGuestAmounts(btn, rplan_index);
}

/**
 * Enables all fields of a rate plan additional guest amounts.
 */
function vcmEnableAdditionalGuestAmounts(elem, rplan_index) {
	var wrapper;
	if (typeof rplan_index !== 'undefined') {
		wrapper = jQuery('.vcm-expediaroom-rateplan-additionalguestamounts[data-rateplanindex="' + rplan_index + '"]');
	} else {
		wrapper = jQuery(elem).closest('.vcm-expediaroom-rateplan-additionalguestamounts');
	}
	wrapper.find('.vcm-param-container-tmp-disabled').removeClass('vcm-param-container-tmp-disabled').find('input, select').prop('disabled', false).removeClass('vcm-hidden-disabled');
}

/**
 * Display modal window to add a new per-stay service fee to a rate plan.
 */
function vcmMngRateplanPerStayServiceFees(elem, rplan_index) {
	// build the button to handle the adding of the values
	var btn_apply_perstay_service_fee = jQuery('<button></button>').attr('type', 'button').addClass('btn btn-success').html('<?php VikBookingIcons::e('plus-circle'); ?> Per Stay Service Fee');
	btn_apply_perstay_service_fee.on('click', function() {
		// collect fields
		var taxable = jQuery('#perstayservicefee-taxable').val();
		var amount = jQuery('#perstayservicefee-amount').val();
		var amount_type = jQuery('#perstayservicefee-amount-type').val();
		if (!amount || !amount.length) {
			alert('Invalid amount. Cannot be empty and decimals must be properly formatted with a dot.');
			return false;
		}
		var amount_type_str = '';
		if (amount_type == 'amountPerNight') {
			amount_type_str = ' (per night)';
		} else if (amount_type == 'amountPerStay') {
			amount_type_str = ' (per stay)';
		} else if (amount_type == 'percent') {
			amount_type_str = '% (base rate)';
		} else {
			alert('Invalid amount type.');
			return false;
		}
		// build new HTML content
		var perstayservicefee_html = '';
		perstayservicefee_html += '<div class="vcm-params-block vcm-expediaroom-rateplan-perstayservicefee">' + "\n";
		perstayservicefee_html += '	<div class="vcm-param-container">' + "\n";
		perstayservicefee_html += '		<div class="vcm-param-label">Per Stay Service Fee</div>' + "\n";
		perstayservicefee_html += '		<div class="vcm-param-setting">' + "\n";
		perstayservicefee_html += '			<div class="vcm-param-subsetting">' + "\n";
		perstayservicefee_html += '				<span>Taxable:</span>' + "\n";
		perstayservicefee_html += '				<span>' + (taxable > 0 ? Joomla.JText._('VCMYES') : Joomla.JText._('VCMNO')) + '</span>' + "\n";
		perstayservicefee_html += '				<input type="hidden" data-buildname="listing[_ratePlans][' + rplan_index + '][serviceFeesPerStay][%d][isTaxable]" value="' + (taxable > 0 ? 'true' : 'false') + '" />' + "\n";
		perstayservicefee_html += '			</div>' + "\n";
		perstayservicefee_html += '			<div class="vcm-param-subsetting">' + "\n";
		perstayservicefee_html += '				<span>Amount:</span>' + "\n";
		perstayservicefee_html += '				<span>' + amount + amount_type_str + '</span>' + "\n";
		perstayservicefee_html += '				<input type="hidden" data-buildname="listing[_ratePlans][' + rplan_index + '][serviceFeesPerStay][%d][' + amount_type + ']" value="' + amount + '" />' + "\n";
		perstayservicefee_html += '			</div>' + "\n";
		perstayservicefee_html += '		</div>' + "\n";
		perstayservicefee_html += '	</div>' + "\n";
		perstayservicefee_html += '	<div class="vcm-param-container">' + "\n";
		perstayservicefee_html += '		<div class="vcm-param-label">' + Joomla.JText._('VCMBCAHDELETE') + '</div>' + "\n";
		perstayservicefee_html += '		<div class="vcm-param-setting">' + "\n";
		perstayservicefee_html += '			<button type="button" class="btn btn-danger" onclick="vcmRemoveRateplanPerStayServiceFee(this, \'' + rplan_index + '\');"><?php VikBookingIcons::e('times-circle'); ?></button>' + "\n";
		perstayservicefee_html += '		</div>' + "\n";
		perstayservicefee_html += '	</div>' + "\n";
		perstayservicefee_html += '</div>' + "\n";
		// append values
		jQuery('.vcm-expediaroom-rateplan-perstayservicefees[data-rateplanindex="' + rplan_index + '"]').append(perstayservicefee_html);
		// update the name attributes of every element
		vcmResetPerStayServiceFeesNaming(elem, rplan_index);
		// dismiss the modal
		VBOCore.emitEvent('close-rateplan-perstay-service-fee');
	});

	// render modal
	var modal_wrapper = VBOCore.displayModal({
		suffix: 'rateplan-perstay-service-fee',
		title: 'New Per Stay Service Fee',
		body_prepend: true,
		footer_right: btn_apply_perstay_service_fee,
		dismiss_event: 'close-rateplan-perstay-service-fee',
		onDismiss: () => {
			// move HTML helper back to its location
			jQuery('.vcm-expediaroom-rplan-perstayservicefee-helper').appendTo(jQuery('.vcm-expediaroom-html-helpers'));
		},
	});

	// append content to modal
	jQuery('.vcm-expediaroom-rplan-perstayservicefee-helper').appendTo(modal_wrapper);
}

/**
 * Removes the clicked per stay service fee from a rate plan.
 */
function vcmRemoveRateplanPerStayServiceFee(elem, rplan_index) {
	jQuery(elem).closest('.vcm-expediaroom-rateplan-perstayservicefee').remove();
	// update the name attributes of every element
	vcmResetPerStayServiceFeesNaming(elem, rplan_index);
}

/**
 * Every time a modification is made, the name attribute of any input element must be renamed.
 */
function vcmResetPerStayServiceFeesNaming(btn, rplan_index) {
	var wrapper;
	if (typeof rplan_index !== 'undefined') {
		wrapper = jQuery('.vcm-expediaroom-rateplan-perstayservicefees[data-rateplanindex="' + rplan_index + '"]');
	} else {
		wrapper = jQuery(btn).closest('.vcm-expediaroom-rateplan-perstayservicefees');
	}
	wrapper.find('[data-buildname]').each(function(k, v) {
		var elem = jQuery(this);
		var buildname = elem.attr('data-buildname');
		var replacements = (buildname.match(/%d/g) || []).length;
		if (!replacements) {
			return;
		}
		// set proper index (first wildcard %d)
		var block_elem = elem.closest('.vcm-expediaroom-rateplan-perstayservicefee');
		var block_index = jQuery('.vcm-expediaroom-rateplan-perstayservicefees').find('.vcm-expediaroom-rateplan-perstayservicefee').index(block_elem);
		buildname = buildname.replace('%d', block_index);
		// set correct name attribute
		elem.attr('name', buildname);
	});
	// make sure to enable all input fields
	vcmEnablePerStayServiceFees(btn, rplan_index);
}

/**
 * Enables all fields of a rate plan per stay service fees.
 */
function vcmEnablePerStayServiceFees(elem, rplan_index) {
	var wrapper;
	if (typeof rplan_index !== 'undefined') {
		wrapper = jQuery('.vcm-expediaroom-rateplan-perstayservicefees[data-rateplanindex="' + rplan_index + '"]');
	} else {
		wrapper = jQuery(elem).closest('.vcm-expediaroom-rateplan-perstayservicefees');
	}
	wrapper.find('.vcm-param-container-tmp-disabled').removeClass('vcm-param-container-tmp-disabled').find('input, select').prop('disabled', false).removeClass('vcm-hidden-disabled');
}

/**
 * Display modal window to add a new per-person service fee to a rate plan.
 */
function vcmMngRateplanPerPersonServiceFees(elem, rplan_index) {
	// build the button to handle the adding of the values
	var btn_apply_perperson_service_fee = jQuery('<button></button>').attr('type', 'button').addClass('btn btn-success').html('<?php VikBookingIcons::e('plus-circle'); ?> Per Person Service Fee');
	btn_apply_perperson_service_fee.on('click', function() {
		// collect fields
		var startdate = jQuery('#perpersonservicefee-startdate').val();
		var enddate = jQuery('#perpersonservicefee-enddate').val();
		var agecategory = jQuery('#perpersonservicefee-agecategory').val();
		var taxable = jQuery('#perpersonservicefee-taxable').val();
		var amount = jQuery('#perpersonservicefee-amount').val();
		var amount_type = jQuery('#perpersonservicefee-amount-type').val();
		if (!amount || !amount.length) {
			alert('Invalid amount. Cannot be empty and decimals must be properly formatted with a dot.');
			return false;
		}
		var amount_type_str = '';
		if (amount_type == 'amountPerNight') {
			amount_type_str = ' (per night)';
		} else if (amount_type == 'amountPerStay') {
			amount_type_str = ' (per stay)';
		} else {
			alert('Invalid amount type.');
			return false;
		}
		// build new HTML content
		var perpersonservicefee_html = '';
		perpersonservicefee_html += '<div class="vcm-params-block vcm-expediaroom-rateplan-perpersonservicefee">' + "\n";
		perpersonservicefee_html += '	<div class="vcm-param-container">' + "\n";
		perpersonservicefee_html += '		<div class="vcm-param-label">Per Person Service Fee</div>' + "\n";
		perpersonservicefee_html += '		<div class="vcm-param-setting">' + "\n";
		perpersonservicefee_html += '			<div class="vcm-param-subsetting">' + "\n";
		perpersonservicefee_html += '				<span>Start Date:</span>' + "\n";
		perpersonservicefee_html += '				<span>' + startdate + '</span>' + "\n";
		perpersonservicefee_html += '				<input type="hidden" data-buildname="listing[_ratePlans][' + rplan_index + '][serviceFeesPerPerson][%d][dateStart]" value="' + startdate + '" />' + "\n";
		perpersonservicefee_html += '			</div>' + "\n";
		perpersonservicefee_html += '			<div class="vcm-param-subsetting">' + "\n";
		perpersonservicefee_html += '				<span>End Date:</span>' + "\n";
		perpersonservicefee_html += '				<span>' + enddate + '</span>' + "\n";
		perpersonservicefee_html += '				<input type="hidden" data-buildname="listing[_ratePlans][' + rplan_index + '][serviceFeesPerPerson][%d][dateEnd]" value="' + enddate + '" />' + "\n";
		perpersonservicefee_html += '			</div>' + "\n";
		perpersonservicefee_html += '			<div class="vcm-param-subsetting">' + "\n";
		perpersonservicefee_html += '				<span>Age Category:</span>' + "\n";
		perpersonservicefee_html += '				<span>' + agecategory + '</span>' + "\n";
		perpersonservicefee_html += '				<input type="hidden" data-buildname="listing[_ratePlans][' + rplan_index + '][serviceFeesPerPerson][%d][ageCategory]" value="' + agecategory + '" />' + "\n";
		perpersonservicefee_html += '			</div>' + "\n";
		perpersonservicefee_html += '			<div class="vcm-param-subsetting">' + "\n";
		perpersonservicefee_html += '				<span>Taxable:</span>' + "\n";
		perpersonservicefee_html += '				<span>' + (taxable > 0 ? Joomla.JText._('VCMYES') : Joomla.JText._('VCMNO')) + '</span>' + "\n";
		perpersonservicefee_html += '				<input type="hidden" data-buildname="listing[_ratePlans][' + rplan_index + '][serviceFeesPerPerson][%d][isTaxable]" value="' + (taxable > 0 ? 'true' : 'false') + '" />' + "\n";
		perpersonservicefee_html += '			</div>' + "\n";
		perpersonservicefee_html += '			<div class="vcm-param-subsetting">' + "\n";
		perpersonservicefee_html += '				<span>Amount:</span>' + "\n";
		perpersonservicefee_html += '				<span>' + amount + amount_type_str + '</span>' + "\n";
		perpersonservicefee_html += '				<input type="hidden" data-buildname="listing[_ratePlans][' + rplan_index + '][serviceFeesPerPerson][%d][' + amount_type + ']" value="' + amount + '" />' + "\n";
		perpersonservicefee_html += '			</div>' + "\n";
		perpersonservicefee_html += '		</div>' + "\n";
		perpersonservicefee_html += '	</div>' + "\n";
		perpersonservicefee_html += '	<div class="vcm-param-container">' + "\n";
		perpersonservicefee_html += '		<div class="vcm-param-label">' + Joomla.JText._('VCMBCAHDELETE') + '</div>' + "\n";
		perpersonservicefee_html += '		<div class="vcm-param-setting">' + "\n";
		perpersonservicefee_html += '			<button type="button" class="btn btn-danger" onclick="vcmRemoveRateplanPerpersonServiceFee(this, \'' + rplan_index + '\');"><?php VikBookingIcons::e('times-circle'); ?></button>' + "\n";
		perpersonservicefee_html += '		</div>' + "\n";
		perpersonservicefee_html += '	</div>' + "\n";
		perpersonservicefee_html += '</div>' + "\n";
		// append values
		jQuery('.vcm-expediaroom-rateplan-perpersonservicefees[data-rateplanindex="' + rplan_index + '"]').append(perpersonservicefee_html);
		// update the name attributes of every element
		vcmResetPerPersonServiceFeesNaming(elem, rplan_index);
		// dismiss the modal
		VBOCore.emitEvent('close-rateplan-perperson-service-fee');
	});

	// render modal
	var modal_wrapper = VBOCore.displayModal({
		suffix: 'rateplan-perperson-service-fee',
		title: 'New Per Person Service Fee',
		body_prepend: true,
		footer_right: btn_apply_perperson_service_fee,
		dismiss_event: 'close-rateplan-perperson-service-fee',
		onDismiss: () => {
			// move HTML helper back to its location
			jQuery('.vcm-expediaroom-rplan-perpersonservicefee-helper').appendTo(jQuery('.vcm-expediaroom-html-helpers'));
		},
	});

	// append content to modal
	jQuery('.vcm-expediaroom-rplan-perpersonservicefee-helper').appendTo(modal_wrapper);
}

/**
 * Removes the clicked per person service fee from a rate plan.
 */
function vcmRemoveRateplanPerpersonServiceFee(elem, rplan_index) {
	jQuery(elem).closest('.vcm-expediaroom-rateplan-perpersonservicefee').remove();
	// update the name attributes of every element
	vcmResetPerPersonServiceFeesNaming(elem, rplan_index);
}

/**
 * Every time a modification is made, the name attribute of any input element must be renamed.
 */
function vcmResetPerPersonServiceFeesNaming(btn, rplan_index) {
	var wrapper;
	if (typeof rplan_index !== 'undefined') {
		wrapper = jQuery('.vcm-expediaroom-rateplan-perpersonservicefees[data-rateplanindex="' + rplan_index + '"]');
	} else {
		wrapper = jQuery(btn).closest('.vcm-expediaroom-rateplan-perpersonservicefees');
	}
	wrapper.find('[data-buildname]').each(function(k, v) {
		var elem = jQuery(this);
		var buildname = elem.attr('data-buildname');
		var replacements = (buildname.match(/%d/g) || []).length;
		if (!replacements) {
			return;
		}
		// set proper index (first wildcard %d)
		var block_elem = elem.closest('.vcm-expediaroom-rateplan-perpersonservicefee');
		var block_index = jQuery('.vcm-expediaroom-rateplan-perpersonservicefees').find('.vcm-expediaroom-rateplan-perpersonservicefee').index(block_elem);
		buildname = buildname.replace('%d', block_index);
		// set correct name attribute
		elem.attr('name', buildname);
	});
	// make sure to enable all input fields
	vcmEnablePerPersonServiceFees(btn, rplan_index);
}

/**
 * Enables all fields of a rate plan per person service fees.
 */
function vcmEnablePerPersonServiceFees(elem, rplan_index) {
	var wrapper;
	if (typeof rplan_index !== 'undefined') {
		wrapper = jQuery('.vcm-expediaroom-rateplan-perpersonservicefees[data-rateplanindex="' + rplan_index + '"]');
	} else {
		wrapper = jQuery(elem).closest('.vcm-expediaroom-rateplan-perpersonservicefees');
	}
	wrapper.find('.vcm-param-container-tmp-disabled').removeClass('vcm-param-container-tmp-disabled').find('input, select').prop('disabled', false).removeClass('vcm-hidden-disabled');
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
 * Displays a modal window to create a new rate plan.
 */
function vcmNewRatePlan() {
	// make sure a new one is not already in the form
	if (jQuery('.vcm-expediaroom-newrateplan').length) {
		alert('You can only add one rate plan per request. Click the Save button to create a new rate plan. Then you will be able to add another one.');
		return false;
	}
	// build the button to handle the adding of the values
	var btn_apply_rateplan_addnew = jQuery('<button></button>').attr('type', 'button').addClass('btn btn-success').html('<?php VikBookingIcons::e('plus-circle'); ?> New Rate Plan');
	btn_apply_rateplan_addnew.on('click', function() {
		// collect fields
		var name = jQuery('#addrateplan-name').val();
		var is_ec_model = jQuery('input[name="addrateplan_ec_model"]').prop('checked');
		var ec_model_code = jQuery('#addrateplan-ec-pcode').val();
		var is_hc_model = jQuery('input[name="addrateplan_hc_model"]').prop('checked');
		var hc_model_code = jQuery('#addrateplan-hc-pcode').val();
		var occ_for_base_rate = null;
		if (jQuery('#addrateplan-occupantsForBaseRate').length) {
			// only in case of PDP model
			occ_for_base_rate = jQuery('#addrateplan-occupantsForBaseRate').val();
		}
		// validate fields
		if (!name || !name.length) {
			alert('Rate plan name cannot be empty.');
			return false;
		}
		if (!is_ec_model && !is_hc_model) {
			alert('You need to enable at least one distribution rule.');
			return false;
		}
		if ((!ec_model_code || !ec_model_code.length) && (!hc_model_code || !hc_model_code.length)) {
			alert('You need to enable at least one distribution rule and provide an identifier code for it.');
			return false;
		}
		// get index for this new rate plan fields
		var rplan_index = jQuery('.vcm-expediaroom-rateplans').find('.vcm-expediaroom-rateplan').length;
		// collect distribution rules
		var distribution_rules = ['<input type="hidden" class="vcm-expediaroom-newrateplan" name="listing[_ratePlans][' + rplan_index + '][name]" value="' + name + '" />'];
		if (occ_for_base_rate) {
			// we need to push this hidden field as well for PDP model properties
			distribution_rules.push('<input type="hidden" name="listing[_ratePlans][' + rplan_index + '][occupantsForBaseRate]" value="' + occ_for_base_rate + '" />');
		}
		var distr_rule_index = 0;
		if (is_ec_model && ec_model_code && ec_model_code.length) {
			distribution_rules.push('<input type="hidden" name="listing[_ratePlans][' + rplan_index + '][distributionRules][' + distr_rule_index + '][partnerCode]" value="' + ec_model_code + '" />');
			distribution_rules.push('<input type="hidden" name="listing[_ratePlans][' + rplan_index + '][distributionRules][' + distr_rule_index + '][distributionModel]" value="ExpediaCollect" />');
			// increase index in case also hotel collect is defined
			distr_rule_index++;
		}
		if (is_hc_model && hc_model_code && hc_model_code.length) {
			distribution_rules.push('<input type="hidden" name="listing[_ratePlans][' + rplan_index + '][distributionRules][' + distr_rule_index + '][partnerCode]" value="' + hc_model_code + '" />');
			distribution_rules.push('<input type="hidden" name="listing[_ratePlans][' + rplan_index + '][distributionRules][' + distr_rule_index + '][distributionModel]" value="HotelCollect" />');
		}

		// build new HTML content
		var addrateplan_html = '';
		addrateplan_html += '<div class="vcm-params-block vcm-expediaroom-rateplan">' + "\n";
		addrateplan_html += '	<div class="vcm-param-container vcm-expediaroom-rateplan-first-param">' + "\n";
		addrateplan_html += '		<div class="vcm-param-label">' + "\n";
		addrateplan_html += '			<strong>' + name + '</strong>' + "\n";
		addrateplan_html += '			<div>' + "\n";
		addrateplan_html += '				<span class="label label-info">' + Joomla.JText._('NEW') + '</span>' + "\n";
		addrateplan_html += '			</div>' + "\n";
		addrateplan_html += '		</div>' + "\n";
		addrateplan_html += '		<div class="vcm-param-setting">' + "\n";
		addrateplan_html += '			<button type="button" class="btn btn-danger" onclick="vcmRemoveNewRatePlan(this);"><?php VikBookingIcons::e('times-circle'); ?></button>' + "\n";
		addrateplan_html += '		</div>' + "\n";
		addrateplan_html += distribution_rules.join("\n");
		addrateplan_html += '	</div>' + "\n";
		addrateplan_html += '</div>' + "\n";
		// append values
		jQuery('.vcm-expediaroom-rateplans').append(addrateplan_html);
		// dismiss the modal
		VBOCore.emitEvent('close-rateplan-addnew');
	});

	// render modal
	var modal_wrapper = VBOCore.displayModal({
		suffix: 'rateplan-addnew',
		title: 'New Rate Plan',
		body_prepend: true,
		footer_right: btn_apply_rateplan_addnew,
		dismiss_event: 'close-rateplan-addnew',
		onDismiss: () => {
			// move HTML helper back to its location
			jQuery('.vcm-expediaroom-rplan-addnew-helper').appendTo(jQuery('.vcm-expediaroom-html-helpers'));
		},
	});

	// append content to modal
	jQuery('.vcm-expediaroom-rplan-addnew-helper').appendTo(modal_wrapper);
}

/**
 * Removes the newly rate plan added to allow to re-create a new one.
 */
function vcmRemoveNewRatePlan(elem) {
	jQuery(elem).closest('.vcm-expediaroom-rateplan').remove();
}

/**
 * Toggles a specific type of distribution model when creating a new rate plan.
 */
function vcmToggleDistrModel(enabled, type) {
	var elems = jQuery((type === 'ec' ? '.addrateplan-ec-model' : '.addrateplan-hc-model'));
	if (enabled) {
		elems.show();
	} else {
		elems.hide();
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
 * Fires when the DOM is ready.
 */
jQuery(function() {
	// disable input fields when in edit mode
	if (jQuery('#idroomota').length) {
		jQuery('#adminForm').find('input:not([type="hidden"]):not(.vcm-listing-editable), input.vcm-hidden-disabled[type="hidden"], select:not(.vcm-listing-editable), textarea:not(.vcm-listing-editable)').prop('disabled', true).closest('.vcm-param-container').addClass('vcm-param-container-tmp-disabled').on('click', function() {
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

	// hide rate plans fields when the page loads to make it shorter
	jQuery('.vcm-expediaroom-rateplan').find('.vcm-param-container:not(.vcm-expediaroom-rateplan-first-param), .vcm-params-block').hide();

	// register click event to toggle the rate plan fields
	jQuery('.vcm-expediaroom-toggle-rateplans').click(function() {
		jQuery(this).trigger('blur').closest('.vcm-expediaroom-rateplan').find('.vcm-param-container:not(.vcm-expediaroom-rateplan-first-param), .vcm-params-block').toggle();
	});

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
