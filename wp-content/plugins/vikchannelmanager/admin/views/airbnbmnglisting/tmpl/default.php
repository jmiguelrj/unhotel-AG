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

// load assets
$document = JFactory::getDocument();
$document->addStyleSheet(VBO_SITE_URI . 'resources/vikfxgallery.css');
$document->addScript(VBO_SITE_URI . 'resources/vikfxgallery.js');
// we use JHtml to load the jQuery UI Sortable script for compatibility with WP
JHtml::script(VBO_SITE_URI . 'resources/jquery-ui.sortable.min.js');

// application class for table class and more
$vik = new VikApplication(VersionListener::getID());

// Vik Booking Application for media field
$vbo_app = VikChannelManager::getVboApplication();

// find the host account name
$hotel_name = '';
foreach ($this->otarooms as $otar) {
	// make sure the hotel ID matches with the active one
	if (!empty($otar['prop_params'])) {
		$prop_params = json_decode($otar['prop_params'], true);
		if (isset($prop_params['user_id']) && $prop_params['user_id'] != $this->channel['params']['user_id']) {
			// skip this room mapping as it's for a different hotel ID
			continue;
		}
	}
	$hotel_name = !empty($otar['prop_name']) && $otar['prop_name'] != $this->channel['params']['user_id'] ? $otar['prop_name'] : $hotel_name;
}

// check if we are in editing or new mode
$is_editing = count(get_object_vars($this->listing));

// wrap listing object into a JObject object
$listing = new JObject($this->listing);

// listing currency
$listing_currency = VikBooking::getCurrencyName();
if ($is_editing) {
	$pricing_settings = $listing->get('_pricingsettings');
	if (!is_object($pricing_settings)) {
		// we instantiate an empty JObject
		$pricing_settings = new JObject;
	} else {
		$pricing_settings = new JObject($pricing_settings);
	}
	$listing_currency = $pricing_settings->get('listing_currency', $listing_currency);
}

/**
 * Render the Gen-AI content layout.
 * 
 * @since 	1.9.10
 */
$layout_data = [
	'context' => 'listing',
	'channel' => 'Airbnb',
	'prefix'  => 'vcm-content-genai',
	'data'    => $this->listing,
	'info'    => 'Write contents for an Airbnb listing.'
];
echo JLayoutHelper::render('ai.gencontent', $layout_data);

// lang vars for JS
JText::script('VCMREMOVECONFIRM');
JText::script('VCM_PHOTO_CAPTION');
JText::script('MSG_BASE_SUCCESS');
JText::script('MSG_BASE_WARNING_BOOKING_RAR');
JText::script('VCMRESLOGSDT');
JText::script('VCM_UNITS');
JText::script('VCMRARRESTRMINLOS');
JText::script('VCMRARRESTRMAXLOS');
JText::script('VCMRARRESTRCLOSEDARRIVAL');
JText::script('VCMRARRESTRCLOSEDDEPARTURE');
JText::script('VCM_PRICE');
JText::script('VCMBPROMAMINNIGHTS');
JText::script('VCMMAXNIGHTS');
JText::script('VCMYES');
JText::script('VCMNO');
JText::script('VCM_ASK_CONTINUE');
JText::script('VCM_NOAMENITIES_ACCESS');
JText::script('VCM_LOSPRICES');
JText::script('VCM_LISTING_HASNO_ROOMS');

?>

<div class="vcm-loading-overlay">
	<div class="vcm-loading-dot vcm-loading-dot1"></div>
	<div class="vcm-loading-dot vcm-loading-dot2"></div>
	<div class="vcm-loading-dot vcm-loading-dot3"></div>
	<div class="vcm-loading-dot vcm-loading-dot4"></div>
	<div class="vcm-loading-dot vcm-loading-dot5"></div>
</div>

<div class="vcm-listings-list-head">
	<h3><?php echo 'Host ID ' . $this->channel['params']['user_id'] . (!empty($hotel_name) ? ' - ' . $hotel_name : ''); ?></h3>
<?php
if ($is_editing) {
	// print the toolbar when in edit mode to quickly jump to the desired section
	?>
	<div class="vcm-listing-toolbar-wrap">
		<div class="vcm-listing-toolbar-inner">
			<div class="vcm-listing-toolbar-block">
				<span class="vcm-listing-toolbar-btn" data-jumpto="details">
					<a href="JavaScript: void(0);"><?php VikBookingIcons::e('home'); ?> <span><?php echo JText::_('VCMROOMSRELDETAILS'); ?></span></a>
				</span>
			</div>
			<div class="vcm-listing-toolbar-block">
				<span class="vcm-listing-toolbar-btn" data-jumpto="booksettings">
					<a href="JavaScript: void(0);"><?php VikBookingIcons::e('cogs'); ?> <span><?php echo JText::_('VCM_MNGLISTING_BOOKSETTINGS'); ?></span></a>
				</span>
			</div>
			<div class="vcm-listing-toolbar-block">
				<span class="vcm-listing-toolbar-btn" data-jumpto="checkouttasks">
					<a href="JavaScript: void(0);"><?php VikBookingIcons::e('plane-departure'); ?> <span>Checkout Tasks</span></a>
				</span>
			</div>
			<div class="vcm-listing-toolbar-block">
				<span class="vcm-listing-toolbar-btn" data-jumpto="avrules">
					<a href="JavaScript: void(0);"><?php VikBookingIcons::e('calendar-day'); ?> <span><?php echo JText::_('VCM_MNGLISTING_AVRULES'); ?></span></a>
				</span>
			</div>
			<div class="vcm-listing-toolbar-block">
				<span class="vcm-listing-toolbar-btn" data-jumpto="pricesettings">
					<a href="JavaScript: void(0);"><?php VikBookingIcons::e('money-bill'); ?> <span><?php echo JText::_('VCM_MNGLISTING_PRSETTINGS'); ?></span></a>
				</span>
			</div>
			<div class="vcm-listing-toolbar-block">
				<span class="vcm-listing-toolbar-btn" data-jumpto="photos">
					<a href="JavaScript: void(0);"><?php VikBookingIcons::e('camera'); ?> <span><?php echo JText::_('VCMMENUBPHOTOS'); ?></span></a>
				</span>
			</div>
			<div class="vcm-listing-toolbar-block">
				<span class="vcm-listing-toolbar-btn" data-jumpto="rooms">
					<a href="JavaScript: void(0);"><?php VikBookingIcons::e('bed'); ?> <span><?php echo JText::_('VCMPVIEWORDERSVBTHREE'); ?></span></a>
				</span>
			</div>
			<div class="vcm-listing-toolbar-block">
				<span class="vcm-listing-toolbar-btn" data-jumpto="locdescr">
					<a href="JavaScript: void(0);"><?php VikBookingIcons::e('language'); ?> <span><?php echo JText::_('VCM_MNGLISTING_LOCDESCRS'); ?></span></a>
				</span>
			</div>
			<div class="vcm-listing-toolbar-block">
				<span class="vcm-listing-toolbar-btn" data-jumpto="quality">
					<a href="JavaScript: void(0);"><?php VikBookingIcons::e('thumbs-up'); ?> <span><?php echo JText::_('VCM_MNGLISTING_QUALITY'); ?></span></a>
				</span>
			</div>
			<div class="vcm-listing-toolbar-block">
				<span class="vcm-listing-toolbar-btn" data-jumpto="calendars">
					<a href="JavaScript: void(0);"><?php VikBookingIcons::e('calendar-check'); ?> <span><?php echo JText::_('VCM_CALENDARS'); ?></span></a>
				</span>
			</div>
			<div class="vcm-listing-toolbar-block vcm-listing-toolbar-block-link">
				<span class="vcm-listing-toolbar-btn">
					<a href="index.php?option=com_vikchannelmanager&task=airbnblst.reload&listing_id=<?php echo $listing->get('id'); ?>" onclick="return confirm(Joomla.JText._('VCM_ASK_CONTINUE'));"><?php VikBookingIcons::e('sync'); ?> <span><?php echo JText::_('VCM_RELOAD'); ?></span></a>
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

			<fieldset class="adminform vcm-listings-listing-wrap" data-landto="details">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('home'); ?> <?php echo $is_editing ? (JText::_('VCM_MNGLISTING_EDIT') . ' - ' . $listing->get('id')) : JText::_('VCM_MNGLISTING_NEW'); ?></legend>
					<div class="vcm-params-container">

						<div class="vcm-param-container vcm-listing-noedit">
							<div class="vcm-param-label"><?php echo JText::_('VCMROOMSRELATIONSNAME'); ?></div>
							<div class="vcm-param-setting">
								<textarea maxlength="50" minlength="8" rows="4" cols="50" name="listing[name]"<?php echo $is_editing ? ' readonly' : ''; ?>><?php echo $this->escape($listing->get('name')); ?></textarea>
								<span class="vcm-param-setting-comment">Listing name. 50 character maximum; 8 characters minimum.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VBSTATUS'); ?></div>
							<div class="vcm-param-setting">
								<select name="listing[has_availability]">
									<option value="true"<?php echo $listing->get('has_availability') ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMTACROOMPUBLISHED'); ?></option>
									<option value="false"<?php echo !$listing->get('has_availability') ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMTACROOMUNPUBLISHED'); ?></option>
								</select>
								<span class="vcm-param-setting-comment">Whether the listing should be &quot;listed&quot; or &quot;unlisted&quot;.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Property type group</div>
							<div class="vcm-param-setting">
								<select name="listing[property_type_group]">
									<option value=""></option>
									<option value="apartments"<?php echo $listing->get('property_type_group') == 'apartments' ? ' selected="selected"' : ''; ?>>Apartments</option>
									<option value="bnb"<?php echo $listing->get('property_type_group') == 'bnb' ? ' selected="selected"' : ''; ?>>BnB</option>
									<option value="boutique_hotels_and_more"<?php echo $listing->get('property_type_group') == 'boutique_hotels_and_more' ? ' selected="selected"' : ''; ?>>Boutique Hotels and more</option>
									<option value="houses"<?php echo $listing->get('property_type_group') == 'houses' ? ' selected="selected"' : ''; ?>>Houses</option>
									<option value="secondary_units"<?php echo $listing->get('property_type_group') == 'secondary_units' ? ' selected="selected"' : ''; ?>>Secondary units</option>
									<option value="unique_homes"<?php echo $listing->get('property_type_group') == 'unique_homes' ? ' selected="selected"' : ''; ?>>Unique homes</option>
								</select>
								<span class="vcm-param-setting-comment">Generalized group of the property types.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Property type category</div>
							<div class="vcm-param-setting">
								<select name="listing[property_type_category]">
									<option value=""></option>
									<option value="aparthotel"<?php echo $listing->get('property_type_category') == 'aparthotel' ? ' selected="selected"' : ''; ?>>Aparthotel</option>
									<option value="apartment"<?php echo $listing->get('property_type_category') == 'apartment' ? ' selected="selected"' : ''; ?>>Apartment</option>
									<option value="barn"<?php echo $listing->get('property_type_category') == 'barn' ? ' selected="selected"' : ''; ?>>Barn</option>
									<option value="bnb"<?php echo $listing->get('property_type_category') == 'bnb' ? ' selected="selected"' : ''; ?>>Bnb</option>
									<option value="boat"<?php echo $listing->get('property_type_category') == 'boat' ? ' selected="selected"' : ''; ?>>Boat</option>
									<option value="boutique_hotel"<?php echo $listing->get('property_type_category') == 'boutique_hotel' ? ' selected="selected"' : ''; ?>>Boutique hotel</option>
									<option value="bungalow"<?php echo $listing->get('property_type_category') == 'bungalow' ? ' selected="selected"' : ''; ?>>Bungalow</option>
									<option value="cabin"<?php echo $listing->get('property_type_category') == 'cabin' ? ' selected="selected"' : ''; ?>>Cabin</option>
									<option value="campsite"<?php echo $listing->get('property_type_category') == 'campsite' ? ' selected="selected"' : ''; ?>>Campsite</option>
									<option value="casa_particular"<?php echo $listing->get('property_type_category') == 'casa_particular' ? ' selected="selected"' : ''; ?>>Casa particular</option>
									<option value="castle"<?php echo $listing->get('property_type_category') == 'castle' ? ' selected="selected"' : ''; ?>>Castle</option>
									<option value="cave"<?php echo $listing->get('property_type_category') == 'cave' ? ' selected="selected"' : ''; ?>>Cave</option>
									<option value="chalet"<?php echo $listing->get('property_type_category') == 'chalet' ? ' selected="selected"' : ''; ?>>Chalet</option>
									<option value="condominium"<?php echo $listing->get('property_type_category') == 'condominium' ? ' selected="selected"' : ''; ?>>Condominium</option>
									<option value="cottage"<?php echo $listing->get('property_type_category') == 'cottage' ? ' selected="selected"' : ''; ?>>Cottage</option>
									<option value="cycladic_house"<?php echo $listing->get('property_type_category') == 'cycladic_house' ? ' selected="selected"' : ''; ?>>Cycladic house</option>
									<option value="dammuso"<?php echo $listing->get('property_type_category') == 'dammuso' ? ' selected="selected"' : ''; ?>>Dammuso</option>
									<option value="dome_house"<?php echo $listing->get('property_type_category') == 'dome_house' ? ' selected="selected"' : ''; ?>>Dome house</option>
									<option value="earthhouse"<?php echo $listing->get('property_type_category') == 'earthhouse' ? ' selected="selected"' : ''; ?>>Earthhouse</option>
									<option value="farm_stay"<?php echo $listing->get('property_type_category') == 'farm_stay' ? ' selected="selected"' : ''; ?>>Farm stay</option>
									<option value="guest_suite"<?php echo $listing->get('property_type_category') == 'guest_suite' ? ' selected="selected"' : ''; ?>>Guest suite</option>
									<option value="guesthouse"<?php echo $listing->get('property_type_category') == 'guesthouse' ? ' selected="selected"' : ''; ?>>Guesthouse</option>
									<option value="heritage_hotel"<?php echo $listing->get('property_type_category') == 'heritage_hotel' ? ' selected="selected"' : ''; ?>>Heritage hotel</option>
									<option value="holiday_park"<?php echo $listing->get('property_type_category') == 'holiday_park' ? ' selected="selected"' : ''; ?>>Holiday park</option>
									<option value="hostel"<?php echo $listing->get('property_type_category') == 'hostel' ? ' selected="selected"' : ''; ?>>Hostel</option>
									<option value="hotel"<?php echo $listing->get('property_type_category') == 'hotel' ? ' selected="selected"' : ''; ?>>Hotel</option>
									<option value="house"<?php echo $listing->get('property_type_category') == 'house' ? ' selected="selected"' : ''; ?>>House</option>
									<option value="houseboat"<?php echo $listing->get('property_type_category') == 'houseboat' ? ' selected="selected"' : ''; ?>>Houseboat</option>
									<option value="hut"<?php echo $listing->get('property_type_category') == 'hut' ? ' selected="selected"' : ''; ?>>Hut</option>
									<option value="igloo"<?php echo $listing->get('property_type_category') == 'igloo' ? ' selected="selected"' : ''; ?>>Igloo</option>
									<option value="island"<?php echo $listing->get('property_type_category') == 'island' ? ' selected="selected"' : ''; ?>>Island</option>
									<option value="kezhan"<?php echo $listing->get('property_type_category') == 'kezhan' ? ' selected="selected"' : ''; ?>>Kezhan</option>
									<option value="lighthouse"<?php echo $listing->get('property_type_category') == 'lighthouse' ? ' selected="selected"' : ''; ?>>Lighthouse</option>
									<option value="lodge"<?php echo $listing->get('property_type_category') == 'lodge' ? ' selected="selected"' : ''; ?>>Lodge</option>
									<option value="loft"<?php echo $listing->get('property_type_category') == 'loft' ? ' selected="selected"' : ''; ?>>Loft</option>
									<option value="minsu"<?php echo $listing->get('property_type_category') == 'minsu' ? ' selected="selected"' : ''; ?>>Minsu</option>
									<option value="pension"<?php echo $listing->get('property_type_category') == 'pension' ? ' selected="selected"' : ''; ?>>Pension</option>
									<option value="plane"<?php echo $listing->get('property_type_category') == 'plane' ? ' selected="selected"' : ''; ?>>Plane</option>
									<option value="ranch"<?php echo $listing->get('property_type_category') == 'ranch' ? ' selected="selected"' : ''; ?>>Ranch</option>
									<option value="religious_building"<?php echo $listing->get('property_type_category') == 'religious_building' ? ' selected="selected"' : ''; ?>>Religious building</option>
									<option value="resort"<?php echo $listing->get('property_type_category') == 'resort' ? ' selected="selected"' : ''; ?>>Resort</option>
									<option value="riad"<?php echo $listing->get('property_type_category') == 'riad' ? ' selected="selected"' : ''; ?>>Riad</option>
									<option value="rv"<?php echo $listing->get('property_type_category') == 'rv' ? ' selected="selected"' : ''; ?>>Rv</option>
									<option value="ryokan"<?php echo $listing->get('property_type_category') == 'ryokan' ? ' selected="selected"' : ''; ?>>Ryokan</option>
									<option value="serviced_apartment"<?php echo $listing->get('property_type_category') == 'serviced_apartment' ? ' selected="selected"' : ''; ?>>Serviced apartment</option>
									<option value="shepherds_hut"<?php echo $listing->get('property_type_category') == 'shepherds_hut' ? ' selected="selected"' : ''; ?>>Shepherds hut</option>
									<option value="shipping_container"<?php echo $listing->get('property_type_category') == 'shipping_container' ? ' selected="selected"' : ''; ?>>Shipping container</option>
									<option value="tent"<?php echo $listing->get('property_type_category') == 'tent' ? ' selected="selected"' : ''; ?>>Tent</option>
									<option value="tiny_house"<?php echo $listing->get('property_type_category') == 'tiny_house' ? ' selected="selected"' : ''; ?>>Tiny house</option>
									<option value="tipi"<?php echo $listing->get('property_type_category') == 'tipi' ? ' selected="selected"' : ''; ?>>Tipi</option>
									<option value="tower"<?php echo $listing->get('property_type_category') == 'tower' ? ' selected="selected"' : ''; ?>>Tower</option>
									<option value="townhouse"<?php echo $listing->get('property_type_category') == 'townhouse' ? ' selected="selected"' : ''; ?>>Townhouse</option>
									<option value="train"<?php echo $listing->get('property_type_category') == 'train' ? ' selected="selected"' : ''; ?>>Train</option>
									<option value="treehouse"<?php echo $listing->get('property_type_category') == 'treehouse' ? ' selected="selected"' : ''; ?>>Treehouse</option>
									<option value="trullo"<?php echo $listing->get('property_type_category') == 'trullo' ? ' selected="selected"' : ''; ?>>Trullo</option>
									<option value="villa"<?php echo $listing->get('property_type_category') == 'villa' ? ' selected="selected"' : ''; ?>>Villa</option>
									<option value="windmill"<?php echo $listing->get('property_type_category') == 'windmill' ? ' selected="selected"' : ''; ?>>Windmill</option>
									<option value="yurt"<?php echo $listing->get('property_type_category') == 'yurt' ? ' selected="selected"' : ''; ?>>Yurt</option>
								</select>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Room type category</div>
							<div class="vcm-param-setting">
								<select name="listing[room_type_category]">
									<option value=""></option>
									<option value="private_room"<?php echo $listing->get('room_type_category') == 'private_room' ? ' selected="selected"' : ''; ?>>Private Room</option>
									<option value="shared_room"<?php echo $listing->get('room_type_category') == 'shared_room' ? ' selected="selected"' : ''; ?>>Shared Room</option>
									<option value="entire_home"<?php echo $listing->get('room_type_category') == 'entire_home' ? ' selected="selected"' : ''; ?>>Entire Home</option>
								</select>
								<span class="vcm-param-setting-comment">Is this listing a shared room, private room, or the entire home?</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Bedrooms</div>
							<div class="vcm-param-setting">
								<input type="number" name="listing[bedrooms]" min="0" max="50" value="<?php echo $listing->get('bedrooms'); ?>" step="1" />
								<span class="vcm-param-setting-comment">Number of bedrooms. Minimum: 0 Maximum: 50. Zero bedrooms indicates a studio.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Bathrooms</div>
							<div class="vcm-param-setting">
								<input type="number" name="listing[bathrooms]" min="0" max="50" value="<?php echo $listing->get('bathrooms'); ?>" step="0.5" />
								<span class="vcm-param-setting-comment">Number of bathrooms. Minimum: 0 Maximum: 50. Half values can be used (1.5). A three-quarters bath can be included as a half or full bath.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Beds</div>
							<div class="vcm-param-setting">
								<input type="number" name="listing[beds]" min="0" max="500" value="<?php echo $listing->get('beds'); ?>" step="1" />
								<span class="vcm-param-setting-comment">Total number of beds.</span>
							</div>
						</div>

						<div class="vcm-param-container" id="vcm-airblist-amenities-wrap">
							<div class="vcm-param-label">Amenities</div>
							<div class="vcm-param-setting">
							<?php
							$current_amenities = $listing->get('amenity_categories', []);
							?>
								<select name="listing[amenity_categories][]" multiple="multiple" size="5" class="vcm-multi-select vcm-airblist-amenities">
									<optgroup label="Common">
										<option value="essentials" title="Essentials. Towels, bed sheets, soap, and toilet paper" <?php echo in_array('essentials', $current_amenities) ? 'selected="selected"' : ''; ?>>Essentials</option>
										<option value="ac" title="Air conditioning." <?php echo in_array('ac', $current_amenities) ? 'selected="selected"' : ''; ?>>Ac</option>
										<option value="cleaning_products" title="Cleaning products" <?php echo in_array('cleaning_products', $current_amenities) ? 'selected="selected"' : ''; ?>>Cleaning products</option>
										<option value="cooking_basics" title="Cooking basics. Pots and pans, oil, salt and pepper" <?php echo in_array('cooking_basics', $current_amenities) ? 'selected="selected"' : ''; ?>>Cooking basics</option>
										<option value="laptop_friendly_workspace" title="Dedicated Workspace" <?php echo in_array('laptop_friendly_workspace', $current_amenities) ? 'selected="selected"' : ''; ?>>Laptop friendly workspace</option>
										<option value="dishes_and_silverware" title="Dishes and silverware" <?php echo in_array('dishes_and_silverware', $current_amenities) ? 'selected="selected"' : ''; ?>>Dishes and silverware</option>
										<option value="dryer" title="Dryer. In the building, free or for a fee" <?php echo in_array('dryer', $current_amenities) ? 'selected="selected"' : ''; ?>>Dryer</option>
										<option value="washer" title="Washer. In the building, free or for a fee" <?php echo in_array('washer', $current_amenities) ? 'selected="selected"' : ''; ?>>Washer</option>
										<option value="hair_dryer" title="Hair dryer." <?php echo in_array('hair_dryer', $current_amenities) ? 'selected="selected"' : ''; ?>>Hair dryer</option>
										<option value="heating" title="Heating. Central heating or a heater in the listing" <?php echo in_array('heating', $current_amenities) ? 'selected="selected"' : ''; ?>>Heating</option>
										<option value="jacuzzi" title="Hot tub" <?php echo in_array('jacuzzi', $current_amenities) ? 'selected="selected"' : ''; ?>>Jacuzzi</option>
										<option value="kitchen" title="Kitchen. Space where guests can cook their own meals" <?php echo in_array('kitchen', $current_amenities) ? 'selected="selected"' : ''; ?>>Kitchen</option>
										<option value="pool" title="Pool. Private or Shared" <?php echo in_array('pool', $current_amenities) ? 'selected="selected"' : ''; ?>>Pool</option>
										<option value="tv" title="tv" <?php echo in_array('tv', $current_amenities) ? 'selected="selected"' : ''; ?>>Tv</option>
										<option value="wireless_internet" title="Wifi" <?php echo in_array('wireless_internet', $current_amenities) ? 'selected="selected"' : ''; ?>>Wireless internet</option>
									</optgroup>
									<optgroup label="Bathroom">
										<option value="body_soap" title="Body soap" <?php echo in_array('body_soap', $current_amenities) ? 'selected="selected"' : ''; ?>>Body soap</option>
										<option value="conditioner" title="Conditioner" <?php echo in_array('conditioner', $current_amenities) ? 'selected="selected"' : ''; ?>>Conditioner</option>
										<option value="shampoo" title="Shampoo" <?php echo in_array('shampoo', $current_amenities) ? 'selected="selected"' : ''; ?>>Shampoo</option>
										<option value="bidet" title="Bidet" <?php echo in_array('bidet', $current_amenities) ? 'selected="selected"' : ''; ?>>Bidet</option>
										<option value="bathtub" title="Bathtub" <?php echo in_array('bathtub', $current_amenities) ? 'selected="selected"' : ''; ?>>Bathtub</option>
										<option value="hot_water" title="Hot water" <?php echo in_array('hot_water', $current_amenities) ? 'selected="selected"' : ''; ?>>Hot water</option>
										<option value="rain_shower" title="Rain Shower" <?php echo in_array('rain_shower', $current_amenities) ? 'selected="selected"' : ''; ?>>Rain shower</option>
										<option value="shower_gel" title="Shower gel" <?php echo in_array('shower_gel', $current_amenities) ? 'selected="selected"' : ''; ?>>Shower gel</option>
									</optgroup>
									<optgroup label="Bedroom and laundry">
										<option value="bed_linens" title="Bed linens." <?php echo in_array('bed_linens', $current_amenities) ? 'selected="selected"' : ''; ?>>Bed linens</option>
										<option value="hangers" title="Hangers." <?php echo in_array('hangers', $current_amenities) ? 'selected="selected"' : ''; ?>>Hangers</option>
										<option value="iron" title="Iron." <?php echo in_array('iron', $current_amenities) ? 'selected="selected"' : ''; ?>>Iron</option>
										<option value="wardrobe_or_closet" title="Clothing storage" <?php echo in_array('wardrobe_or_closet', $current_amenities) ? 'selected="selected"' : ''; ?>>Wardrobe or closet</option>
										<option value="clothes_drying_rack" title="Drying rack for clothing" <?php echo in_array('clothes_drying_rack', $current_amenities) ? 'selected="selected"' : ''; ?>>Clothes drying rack</option>
										<option value="extra_pillows_and_blankets" title="Extra pillows and blankets." <?php echo in_array('extra_pillows_and_blankets', $current_amenities) ? 'selected="selected"' : ''; ?>>Extra pillows and blankets</option>
										<option value="mosquito_net" title="Mosquito net." <?php echo in_array('mosquito_net', $current_amenities) ? 'selected="selected"' : ''; ?>>Mosquito net</option>
										<option value="room_darkening_shades" title="Room-darkening shades." <?php echo in_array('room_darkening_shades', $current_amenities) ? 'selected="selected"' : ''; ?>>Room darkening shades</option>
										<option value="safe" title="Safe." <?php echo in_array('safe', $current_amenities) ? 'selected="selected"' : ''; ?>>Safe</option>
									</optgroup>
									<optgroup label="Entertainment">
										<option value="ethernet_connection" title="Ethernet connection." <?php echo in_array('ethernet_connection', $current_amenities) ? 'selected="selected"' : ''; ?>>Ethernet connection</option>
										<option value="exercise_equipment" title="Exercise equipment" <?php echo in_array('exercise_equipment', $current_amenities) ? 'selected="selected"' : ''; ?>>Exercise equipment</option>
										<option value="game_console" title="Game console." <?php echo in_array('game_console', $current_amenities) ? 'selected="selected"' : ''; ?>>Game console</option>
										<option value="piano" title="Piano" <?php echo in_array('piano', $current_amenities) ? 'selected="selected"' : ''; ?>>Piano</option>
										<option value="ping_pong_table" title="Ping Pong Table" <?php echo in_array('ping_pong_table', $current_amenities) ? 'selected="selected"' : ''; ?>>Ping pong table</option>
										<option value="pool_table" title="Pool table" <?php echo in_array('pool_table', $current_amenities) ? 'selected="selected"' : ''; ?>>Pool table</option>
										<option value="record_player" title="Record player" <?php echo in_array('record_player', $current_amenities) ? 'selected="selected"' : ''; ?>>Record player</option>
										<option value="sound_system" title="Sound system" <?php echo in_array('sound_system', $current_amenities) ? 'selected="selected"' : ''; ?>>Sound system</option>
									</optgroup>
									<optgroup label="Family">
										<option value="baby_bath" title="Baby bath." <?php echo in_array('baby_bath', $current_amenities) ? 'selected="selected"' : ''; ?>>Baby bath</option>
										<option value="baby_monitor" title="Baby monitor." <?php echo in_array('baby_monitor', $current_amenities) ? 'selected="selected"' : ''; ?>>Baby monitor</option>
										<option value="baby_safety_gate" title="Baby safety gates" <?php echo in_array('baby_safety_gate', $current_amenities) ? 'selected="selected"' : ''; ?>>Baby safety gate</option>
										<option value="babysitter_recommendations" title="Babysitter recommendations." <?php echo in_array('babysitter_recommendations', $current_amenities) ? 'selected="selected"' : ''; ?>>Babysitter recommendations</option>
										<option value="board_games" title="Board games" <?php echo in_array('board_games', $current_amenities) ? 'selected="selected"' : ''; ?>>Board games</option>
										<option value="changing_table" title="Changing table." <?php echo in_array('changing_table', $current_amenities) ? 'selected="selected"' : ''; ?>>Changing table</option>
										<option value="childrens_books_and_toys" title="Children’s books and toys." <?php echo in_array('childrens_books_and_toys', $current_amenities) ? 'selected="selected"' : ''; ?>>Childrens books and toys</option>
										<option value="childrens_dinnerware" title="Children’s dinnerware." <?php echo in_array('childrens_dinnerware', $current_amenities) ? 'selected="selected"' : ''; ?>>Childrens dinnerware</option>
										<option value="crib" title="Crib." <?php echo in_array('crib', $current_amenities) ? 'selected="selected"' : ''; ?>>Crib</option>
										<option value="fireplace_guards" title="Fireplace guards." <?php echo in_array('fireplace_guards', $current_amenities) ? 'selected="selected"' : ''; ?>>Fireplace guards</option>
										<option value="high_chair" title="High chair." <?php echo in_array('high_chair', $current_amenities) ? 'selected="selected"' : ''; ?>>High chair</option>
										<option value="outlet_covers" title="Outlet covers." <?php echo in_array('outlet_covers', $current_amenities) ? 'selected="selected"' : ''; ?>>Outlet covers</option>
										<option value="pack_n_play_travel_crib" title="Pack ’n Play/travel crib." <?php echo in_array('pack_n_play_travel_crib', $current_amenities) ? 'selected="selected"' : ''; ?>>Pack n play travel crib</option>
										<option value="table_corner_guards" title="Table corner guards." <?php echo in_array('table_corner_guards', $current_amenities) ? 'selected="selected"' : ''; ?>>Table corner guards</option>
										<option value="window_guards" title="Window guards." <?php echo in_array('window_guards', $current_amenities) ? 'selected="selected"' : ''; ?>>Window guards</option>
									</optgroup>
									<optgroup label="Heating and cooling">
										<option value="ceiling_fan" title="Ceiling fan" <?php echo in_array('ceiling_fan', $current_amenities) ? 'selected="selected"' : ''; ?>>Ceiling fan</option>
										<option value="fireplace" title="Indoor fireplace." <?php echo in_array('fireplace', $current_amenities) ? 'selected="selected"' : ''; ?>>Fireplace</option>
										<option value="portable_fans" title="Portable fans" <?php echo in_array('portable_fans', $current_amenities) ? 'selected="selected"' : ''; ?>>Portable fans</option>
									</optgroup>
									<optgroup label="Home Safety">
										<option value="fire_extinguisher" title="Fire extinguisher." <?php echo in_array('fire_extinguisher', $current_amenities) ? 'selected="selected"' : ''; ?>>Fire extinguisher</option>
										<option value="carbon_monoxide_detector" title="Carbon monoxide detector." <?php echo in_array('carbon_monoxide_detector', $current_amenities) ? 'selected="selected"' : ''; ?>>Carbon monoxide detector</option>
										<option value="smoke_detector" title="Smoke detector." <?php echo in_array('smoke_detector', $current_amenities) ? 'selected="selected"' : ''; ?>>Smoke detector</option>
										<option value="first_aid_kit" title="First aid kit." <?php echo in_array('first_aid_kit', $current_amenities) ? 'selected="selected"' : ''; ?>>First aid kit</option>
									</optgroup>
									<optgroup label="Internet and office">
										<option value="pocket_wifi" title="Pocket wifi." <?php echo in_array('pocket_wifi', $current_amenities) ? 'selected="selected"' : ''; ?>>Pocket wifi</option>
									</optgroup>
									<optgroup label="Kitchen and dining">
										<option value="baking_sheet" title="Baking sheet" <?php echo in_array('baking_sheet', $current_amenities) ? 'selected="selected"' : ''; ?>>Baking sheet</option>
										<option value="barbeque_utensils" title="Barbeque utensils" <?php echo in_array('barbeque_utensils', $current_amenities) ? 'selected="selected"' : ''; ?>>Barbeque utensils</option>
										<option value="bread_maker" title="Bread maker" <?php echo in_array('bread_maker', $current_amenities) ? 'selected="selected"' : ''; ?>>Bread maker</option>
										<option value="blender" title="Blender" <?php echo in_array('blender', $current_amenities) ? 'selected="selected"' : ''; ?>>Blender</option>
										<option value="coffee" title="Coffee." <?php echo in_array('coffee', $current_amenities) ? 'selected="selected"' : ''; ?>>Coffee</option>
										<option value="coffee_maker" title="Coffee maker." <?php echo in_array('coffee_maker', $current_amenities) ? 'selected="selected"' : ''; ?>>Coffee maker</option>
										<option value="dining_table" title="Dining table" <?php echo in_array('dining_table', $current_amenities) ? 'selected="selected"' : ''; ?>>Dining table</option>
										<option value="dishwasher" title="Dishwasher." <?php echo in_array('dishwasher', $current_amenities) ? 'selected="selected"' : ''; ?>>Dishwasher</option>
										<option value="freezer" title="Freezer" <?php echo in_array('freezer', $current_amenities) ? 'selected="selected"' : ''; ?>>Freezer</option>
										<option value="hot_water_kettle" title="Hot water kettle" <?php echo in_array('hot_water_kettle', $current_amenities) ? 'selected="selected"' : ''; ?>>Hot water kettle</option>
										<option value="microwave" title="Microwave" <?php echo in_array('microwave', $current_amenities) ? 'selected="selected"' : ''; ?>>Microwave</option>
										<option value="mini_fridge" title="Mini fridge" <?php echo in_array('mini_fridge', $current_amenities) ? 'selected="selected"' : ''; ?>>Mini fridge</option>
										<option value="oven" title="Oven." <?php echo in_array('oven', $current_amenities) ? 'selected="selected"' : ''; ?>>Oven</option>
										<option value="refrigerator" title="Refrigerator." <?php echo in_array('refrigerator', $current_amenities) ? 'selected="selected"' : ''; ?>>Refrigerator</option>
										<option value="rice_maker" title="Rice maker" <?php echo in_array('rice_maker', $current_amenities) ? 'selected="selected"' : ''; ?>>Rice maker</option>
										<option value="stove" title="Stove." <?php echo in_array('stove', $current_amenities) ? 'selected="selected"' : ''; ?>>Stove</option>
										<option value="toaster" title="Toaster" <?php echo in_array('toaster', $current_amenities) ? 'selected="selected"' : ''; ?>>Toaster</option>
										<option value="trash_compacter" title="Trash Compactor" <?php echo in_array('trash_compacter', $current_amenities) ? 'selected="selected"' : ''; ?>>Trash compacter</option>
										<option value="wine_glasses" title="Wine glasses" <?php echo in_array('wine_glasses', $current_amenities) ? 'selected="selected"' : ''; ?>>Wine glasses</option>
									</optgroup>
									<optgroup label="Location">
										<option value="lake_access" title="Lake access." <?php echo in_array('lake_access', $current_amenities) ? 'selected="selected"' : ''; ?>>Lake access</option>
										<option value="laundromat_nearby" title="Laundromat nearby." <?php echo in_array('laundromat_nearby', $current_amenities) ? 'selected="selected"' : ''; ?>>Laundromat nearby</option>
										<option value="ski_in_ski_out" title="Ski in/Ski out." <?php echo in_array('ski_in_ski_out', $current_amenities) ? 'selected="selected"' : ''; ?>>Ski in ski out</option>
										<option value="waterfront" title="Waterfront." <?php echo in_array('waterfront', $current_amenities) ? 'selected="selected"' : ''; ?>>Waterfront</option>
										<option value="beach_access" title="Beach access" <?php echo in_array('beach_access', $current_amenities) ? 'selected="selected"' : ''; ?>>Beach access</option>
										<option value="resort_access" title="Resort access" <?php echo in_array('resort_access', $current_amenities) ? 'selected="selected"' : ''; ?>>Resort access</option>
										<option value="private_entrance" title="Private entrance. Separate street or building entrance" <?php echo in_array('private_entrance', $current_amenities) ? 'selected="selected"' : ''; ?>>Private entrance</option>
									</optgroup>
									<optgroup label="Outdoor">
										<option value="garden_or_backyard" title="Garden or backyard." <?php echo in_array('garden_or_backyard', $current_amenities) ? 'selected="selected"' : ''; ?>>Garden or backyard</option>
										<option value="bbq_area" title="BBQ grill." <?php echo in_array('bbq_area', $current_amenities) ? 'selected="selected"' : ''; ?>>Bbq area</option>
										<option value="beach_essentials" title="Beach essentials. Beach towels, umbrella, beach blanket, snorkeling gear" <?php echo in_array('beach_essentials', $current_amenities) ? 'selected="selected"' : ''; ?>>Beach essentials</option>
										<option value="bikes_for_rent" title="Bikes" <?php echo in_array('bikes_for_rent', $current_amenities) ? 'selected="selected"' : ''; ?>>Bikes for rent</option>
										<option value="boat_slip" title="Boat slip" <?php echo in_array('boat_slip', $current_amenities) ? 'selected="selected"' : ''; ?>>Boat slip</option>
										<option value="fire_pit" title="Fire pit" <?php echo in_array('fire_pit', $current_amenities) ? 'selected="selected"' : ''; ?>>Fire pit</option>
										<option value="Hammock" title="Hammock" <?php echo in_array('Hammock', $current_amenities) ? 'selected="selected"' : ''; ?>>Hammock</option>
										<option value="Kayak" title="Kayak" <?php echo in_array('Kayak', $current_amenities) ? 'selected="selected"' : ''; ?>>Kayak</option>
										<option value="outdoor_seating" title="Outdoor seating" <?php echo in_array('outdoor_seating', $current_amenities) ? 'selected="selected"' : ''; ?>>Outdoor seating</option>
										<option value="patio_or_belcony" title="Patio or balcony." <?php echo in_array('patio_or_belcony', $current_amenities) ? 'selected="selected"' : ''; ?>>Patio or balcony</option>
										<option value="outdoor_kitchen" title="Outdoor kitchen" <?php echo in_array('outdoor_kitchen', $current_amenities) ? 'selected="selected"' : ''; ?>>Outdoor kitchen</option>
									</optgroup>
									<optgroup label="Parking and facilities">
										<option value="Elevator" title="Elevator" <?php echo in_array('Elevator', $current_amenities) ? 'selected="selected"' : ''; ?>>Elevator</option>
										<option value="ev_charger" title="EV charger" <?php echo in_array('ev_charger', $current_amenities) ? 'selected="selected"' : ''; ?>>Ev charger</option>
										<option value="free_parking" title="Free parking on premises." <?php echo in_array('free_parking', $current_amenities) ? 'selected="selected"' : ''; ?>>Free parking</option>
										<option value="street_parking" title="Free street parking." <?php echo in_array('street_parking', $current_amenities) ? 'selected="selected"' : ''; ?>>Street parking</option>
										<option value="gym" title="Gym. Free, in the building or nearby" <?php echo in_array('gym', $current_amenities) ? 'selected="selected"' : ''; ?>>Gym</option>
										<option value="paid_parking" title="Paid parking off premises." <?php echo in_array('paid_parking', $current_amenities) ? 'selected="selected"' : ''; ?>>Paid parking</option>
										<option value="paid_parking_on_premises" title="Paid parking on premises." <?php echo in_array('paid_parking_on_premises', $current_amenities) ? 'selected="selected"' : ''; ?>>Paid parking on premises</option>
										<option value="sauna" title="Sauna" <?php echo in_array('sauna', $current_amenities) ? 'selected="selected"' : ''; ?>>Sauna</option>
										<option value="single_level_home" title="Single level home. No stairs in home" <?php echo in_array('single_level_home', $current_amenities) ? 'selected="selected"' : ''; ?>>Single level home</option>
									</optgroup>
									<optgroup label="Services">
										<option value="luggage_dropoff_allowed" title="Luggage dropoff allowed. For guests' convenience when they have early arrival or late departure" <?php echo in_array('luggage_dropoff_allowed', $current_amenities) ? 'selected="selected"' : ''; ?>>Luggage dropoff allowed</option>
										<option value="long_term_stays_allowed" title="Long term stays allowed. Allow stay for 28 days or more" <?php echo in_array('long_term_stays_allowed', $current_amenities) ? 'selected="selected"' : ''; ?>>Long term stays allowed</option>
										<option value="cleaning_before_checkout" title="Cleaning before checkout." <?php echo in_array('cleaning_before_checkout', $current_amenities) ? 'selected="selected"' : ''; ?>>Cleaning before checkout</option>
										<option value="breakfast" title="Breakfast. Breakfast is provided." <?php echo in_array('breakfast', $current_amenities) ? 'selected="selected"' : ''; ?>>Breakfast</option>
									</optgroup>
									<optgroup label="Accessibility Getting Home">
										<option value="home_step_free_access" title="Step-free access. There are no steps to get into the home, and the entryway is level." data-accessibility="1" <?php echo in_array('home_step_free_access', $current_amenities) ? 'selected="selected"' : ''; ?>>Home step free access (<?php echo JText::_('VCM_ACCESSIBILITY'); ?>)</option>
										<option value="path_to_entrance_lit_at_night" title="Well-lit path to entrance. Light can help guests move around unexpected barriers." data-accessibility="1" <?php echo in_array('path_to_entrance_lit_at_night', $current_amenities) ? 'selected="selected"' : ''; ?>>Path to entrance lit at night (<?php echo JText::_('VCM_ACCESSIBILITY'); ?>)</option>
										<option value="home_wide_doorway" title="Wide doorway. The entrance doorway is at least 32 inches wide." data-accessibility="1" <?php echo in_array('home_wide_doorway', $current_amenities) ? 'selected="selected"' : ''; ?>>Home wide doorway (<?php echo JText::_('VCM_ACCESSIBILITY'); ?>)</option>
										<option value="flat_smooth_pathway_to_front_door" title="Flat path to front door. The pathway to the front door is at least 32 inches wide and flat, with little or no slope." data-accessibility="1" <?php echo in_array('flat_smooth_pathway_to_front_door', $current_amenities) ? 'selected="selected"' : ''; ?>>Flat smooth pathway to front door (<?php echo JText::_('VCM_ACCESSIBILITY'); ?>)</option>
										<option value="disabled_parking_spot" title="Disabled parking spot. There is city-approved disabled parking spot or a parking space at least 8 feet wide." data-accessibility="1" <?php echo in_array('disabled_parking_spot', $current_amenities) ? 'selected="selected"' : ''; ?>>Disabled parking spot (<?php echo JText::_('VCM_ACCESSIBILITY'); ?>)</option>
									</optgroup>
									<optgroup label="Accessibility Bedroom">
										<option value="step_free_access" title="Step-free access. There are no steps to get into the bedroom, and the entryway is level." data-accessibility="1" <?php echo in_array('step_free_access', $current_amenities) ? 'selected="selected"' : ''; ?>>Step free access (<?php echo JText::_('VCM_ACCESSIBILITY'); ?>)</option>
										<option value="wide_doorway" title="Wide doorway. The bedroom door is at least 32 inches wide." data-accessibility="1" <?php echo in_array('wide_doorway', $current_amenities) ? 'selected="selected"' : ''; ?>>Wide doorway (<?php echo JText::_('VCM_ACCESSIBILITY'); ?>)</option>
									</optgroup>
									<optgroup label="Accessibility Bathroom">
										<option value="grab_rails_in_shower" title="Fixed grab bars for shower" data-accessibility="1" <?php echo in_array('grab_rails_in_shower', $current_amenities) ? 'selected="selected"' : ''; ?>>Grab rails in shower (<?php echo JText::_('VCM_ACCESSIBILITY'); ?>)</option>
										<option value="grab_rails_in_toilet" title="Fixed grab bars for toilet" data-accessibility="1" <?php echo in_array('grab_rails_in_toilet', $current_amenities) ? 'selected="selected"' : ''; ?>>Grab rails in toilet (<?php echo JText::_('VCM_ACCESSIBILITY'); ?>)</option>
										<option value="rollin_shower" title="There is no threshold or step between the shower and bathroom floor." data-accessibility="1" <?php echo in_array('rollin_shower', $current_amenities) ? 'selected="selected"' : ''; ?>>Rollin shower (<?php echo JText::_('VCM_ACCESSIBILITY'); ?>)</option>
										<option value="shower_chair" title="There is a chair that allows a guest to be seated in the shower. These can be wall-mounted, freestanding or wheeled." data-accessibility="1" <?php echo in_array('shower_chair', $current_amenities) ? 'selected="selected"' : ''; ?>>Shower chair (<?php echo JText::_('VCM_ACCESSIBILITY'); ?>)</option>
									</optgroup>
									<optgroup label="Accessibility Equipment">
										<option value="ceiling_hoist" title="Ceiling hoist. The home has a device fixed to the ceiling that can lift someone in and out of a wheelchair." data-accessibility="1" <?php echo in_array('ceiling_hoist', $current_amenities) ? 'selected="selected"' : ''; ?>>Ceiling hoist (<?php echo JText::_('VCM_ACCESSIBILITY'); ?>)</option>
										<option value="pool_hoist" title="Pool with pool hoist. The home has a pool with a device that can lift someone in and out of it." data-accessibility="1" <?php echo in_array('pool_hoist', $current_amenities) ? 'selected="selected"' : ''; ?>>Pool hoist (<?php echo JText::_('VCM_ACCESSIBILITY'); ?>)</option>
									</optgroup>
								</select>
							</div>
						</div>

						<?php
						$checkin_option = $listing->get('check_in_option');
						$checkin_option = !is_object($checkin_option) ? (new stdClass) : $checkin_option;
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-label">Check-in option - Category</div>
							<div class="vcm-param-setting">
								<select name="listing[check_in_option][category]">
									<option value=""></option>
									<option value="doorman_entry"<?php echo isset($checkin_option->category) && $checkin_option->category == 'doorman_entry' ? ' selected="selected"' : ''; ?>>Doorman entry</option>
									<option value="lockbox"<?php echo isset($checkin_option->category) && $checkin_option->category == 'lockbox' ? ' selected="selected"' : ''; ?>>Lockbox</option>
									<option value="smartlock"<?php echo isset($checkin_option->category) && $checkin_option->category == 'smartlock' ? ' selected="selected"' : ''; ?>>Smartlock</option>
									<option value="keypad"<?php echo isset($checkin_option->category) && $checkin_option->category == 'keypad' ? ' selected="selected"' : ''; ?>>Keypad</option>
									<option value="host_checkin"<?php echo isset($checkin_option->category) && $checkin_option->category == 'host_checkin' ? ' selected="selected"' : ''; ?>>Host Check-in</option>
									<option value="other_checkin"<?php echo isset($checkin_option->category) && $checkin_option->category == 'other_checkin' ? ' selected="selected"' : ''; ?>>Other Check-in</option>
								</select>
								<span class="vcm-param-setting-comment">How guests will check in.</span>
							</div>
						</div>
						<div class="vcm-param-container vcm-param-nested">
							<div class="vcm-param-label">Check-in option - Instruction</div>
							<div class="vcm-param-setting">
								<input type="text" name="listing[check_in_option][instruction]" value="<?php echo isset($checkin_option->instruction) ? $this->escape($checkin_option->instruction) : ''; ?>" />
								<span class="vcm-param-setting-comment">The instructions for guests on how to check in. Only shared with confirmed guests.</span>
							</div>
						</div>

						<!--
						<div class="vcm-param-container">
							<div class="vcm-param-label">Permit or Tax ID</div>
							<div class="vcm-param-setting">
								<input type="text" name="listing[permit_or_tax_id]" value="<?php echo $this->escape($listing->get('permit_or_tax_id')); ?>" />
								<span class="vcm-param-setting-comment">The local permit or tax ID.</span>
							</div>
						</div>
						-->

						<div class="vcm-param-container">
							<div class="vcm-param-label">Apartment/Unit</div>
							<div class="vcm-param-setting">
								<input type="text" name="listing[apt]" value="<?php echo $this->escape($listing->get('apt')); ?>" placeholder="i.e. Apt 32" />
								<span class="vcm-param-setting-comment">Apartment/Unit. Non-special characters only.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Street</div>
							<div class="vcm-param-setting">
								<input type="text" name="listing[street]" value="<?php echo $this->escape($listing->get('street')); ?>" />
								<span class="vcm-param-setting-comment">Street address. Should include number, street name, and street suffix.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">City</div>
							<div class="vcm-param-setting">
								<input type="text" name="listing[city]" value="<?php echo $this->escape($listing->get('city')); ?>" />
								<span class="vcm-param-setting-comment">The name of the city where the listing is located.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">State</div>
							<div class="vcm-param-setting">
								<input type="text" name="listing[state]" value="<?php echo $this->escape($listing->get('state')); ?>" />
								<span class="vcm-param-setting-comment">States, territories, districts, or province. For US states, use the official two-letter abbreviation.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Zip Code</div>
							<div class="vcm-param-setting">
								<input type="text" name="listing[zipcode]" value="<?php echo $this->escape($listing->get('zipcode')); ?>" />
								<span class="vcm-param-setting-comment">Zip or postal code.</span>
							</div>
						</div>

						<?php
						$country_2codes = VikChannelManager::getCountryCodes();
						$current_c2code = $listing->get('country_code');
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-label">Country</div>
							<div class="vcm-param-setting">
								<select name="listing[country_code]">
									<option value=""></option>
								<?php
								foreach ($country_2codes as $ctwoc => $cname) {
									?>
									<option value="<?php echo $ctwoc; ?>"<?php echo $current_c2code == $ctwoc ? ' selected="selected"' : ''; ?>><?php echo $cname; ?></option>
									<?php
								}
								?>
								</select>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Latitude</div>
							<div class="vcm-param-setting">
								<input type="text" name="listing[lat]" value="<?php echo $this->escape($listing->get('lat')); ?>" onkeyup="vcmFormatLatLng(this);" />
								<span class="vcm-param-setting-comment">Latitude. You can use Google Maps to find the listing's latitude and longitude.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Longitude</div>
							<div class="vcm-param-setting">
								<input type="text" name="listing[lng]" value="<?php echo $this->escape($listing->get('lng')); ?>" onkeyup="vcmFormatLatLng(this);" />
								<span class="vcm-param-setting-comment">Longitude. You can use Google Maps to find the listing's latitude and longitude.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Directions</div>
							<div class="vcm-param-setting">
								<textarea rows="4" cols="50" name="listing[directions]"><?php echo $this->escape($listing->get('directions')); ?></textarea>
								<span class="vcm-param-setting-comment">Directions are only provided to confirmed guests.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Person Capacity</div>
							<div class="vcm-param-setting">
								<input type="number" min="1" name="listing[person_capacity]" value="<?php echo $listing->get('person_capacity'); ?>" />
								<span class="vcm-param-setting-comment">Maximum number of guests that can be accommodated. Default is 1.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Shared Bathroom?</div>
							<div class="vcm-param-setting">
								<select name="listing[bathroom_shared]">
									<option value=""></option>
									<option value="true"<?php echo $listing->get('bathroom_shared') === true ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMYES'); ?></option>
									<option value="false"<?php echo $listing->get('bathroom_shared') === false ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMNO'); ?></option>
								</select>
								<span class="vcm-param-setting-comment">Is the bathroom shared? Ignore for &quot;entire home&quot; listings.</span>
							</div>
						</div>
						<div class="vcm-param-container vcm-param-nested">
							<div class="vcm-param-label">Bathroom is shared with</div>
							<div class="vcm-param-setting">
							<?php
							$current_bath_shared_with_cats = $listing->get('bathroom_shared_with_category', array());
							?>
								<select name="listing[bathroom_shared_with_category][]" multiple="multiple" class="vcm-multi-select">
									<option value="host"<?php echo in_array('host', $current_bath_shared_with_cats) ? ' selected="selected"' : ''; ?>>Host</option>
									<option value="family_friends_roommates"<?php echo in_array('family_friends_roommates', $current_bath_shared_with_cats) ? ' selected="selected"' : ''; ?>>Family/Friends/Roommates</option>
									<option value="other_guests"<?php echo in_array('other_guests', $current_bath_shared_with_cats) ? ' selected="selected"' : ''; ?>>Other guests</option>
								</select>
								<span class="vcm-param-setting-comment">Who is the bathroom shared with? Pass one or more values. Ignore for &quot;entire home&quot; listings.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Common spaces shared?</div>
							<div class="vcm-param-setting">
								<select name="listing[common_spaces_shared]">
									<option value=""></option>
									<option value="true"<?php echo $listing->get('common_spaces_shared') === true ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMYES'); ?></option>
									<option value="false"<?php echo $listing->get('common_spaces_shared') === false ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMNO'); ?></option>
								</select>
								<span class="vcm-param-setting-comment">Are the common spaces shared? Ignore for &quot;entire home&quot; listings.</span>
							</div>
						</div>
						<div class="vcm-param-container vcm-param-nested">
							<div class="vcm-param-label">Common spaces are shared with</div>
							<div class="vcm-param-setting">
							<?php
							$current_commsp_shared_with_cats = $listing->get('common_spaces_shared_with_category', array());
							?>
								<select name="listing[common_spaces_shared_with_category][]" multiple="multiple" class="vcm-multi-select">
									<option value="host"<?php echo in_array('host', $current_commsp_shared_with_cats) ? ' selected="selected"' : ''; ?>>Host</option>
									<option value="family_friends_roommates"<?php echo in_array('family_friends_roommates', $current_commsp_shared_with_cats) ? ' selected="selected"' : ''; ?>>Family/Friends/Roommates</option>
									<option value="other_guests"<?php echo in_array('other_guests', $current_commsp_shared_with_cats) ? ' selected="selected"' : ''; ?>>Other guests</option>
								</select>
								<span class="vcm-param-setting-comment">Who are the common spaces shared with? Pass one or more values. Ignore for &quot;entire home&quot; listings.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Total units available</div>
							<div class="vcm-param-setting">
								<input type="number" name="listing[total_inventory_count]" value="<?php echo $this->escape($listing->get('total_inventory_count')); ?>" min="1" />
								<span class="vcm-param-setting-comment">Only used with Room Type inventory. The total number of rooms of a certain room type.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">House Manual</div>
							<div class="vcm-param-setting">
								<textarea rows="4" cols="50" name="listing[house_manual]"><?php echo $this->escape($listing->get('house_manual')); ?></textarea>
								<span class="vcm-param-setting-comment">Notes for guest on enjoying their visit and the property.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Wifi Network</div>
							<div class="vcm-param-setting">
								<input type="text" name="listing[wifi_network]" value="<?php echo $this->escape($listing->get('wifi_network')); ?>" />
								<span class="vcm-param-setting-comment">Name of the wifi network at the listing.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Wifi Password</div>
							<div class="vcm-param-setting">
								<input type="text" name="listing[wifi_password]" value="<?php echo $this->escape($listing->get('wifi_password')); ?>" />
								<span class="vcm-param-setting-comment">Password for connecting to the wifi, cannot be set without wifi network.</span>
							</div>
						</div>

					<?php
					if ($is_editing) {
						?>
						<div class="vcm-params-block">
							<div class="vcm-param-container">
								<div class="vcm-param-label">API Synchronization Category</div>
								<div class="vcm-param-setting">
									<select name="listing[synchronization_category]">
										<option value="sync_all"<?php echo !strcasecmp((string)$listing->get('synchronization_category', ''), 'sync_all') ? ' selected="selected"' : ''; ?>>Sync All (Default)</option>
										<option value="sync_none"<?php echo $listing->get('synchronization_category', '') == 'none' ? ' selected="selected"' : ''; ?>>Sync None (Disconnect)</option>
									</select>
									<span class="vcm-param-setting-comment"><?php VikBookingIcons::e('exclamation-triangle'); ?> By selecting &quot;Sync None&quot; you will disconnect this listing from the Channel Manager. To re-connect it, you may have to go through the Airbnb authorization process again for the current Airbnb Host account.</span>
								</div>
							</div>
							<div class="vcm-param-container">
								<div class="vcm-param-label"><?php VikBookingIcons::e('exclamation-triangle'); ?></div>
								<div class="vcm-param-setting">
									<a class="btn btn-danger" href="index.php?option=com_vikchannelmanager&task=airbnblst.delete_listing&listing_id=<?php echo $this->listing->id; ?>" onclick="return vcmConfirmDelete();"><?php VikBookingIcons::e('bomb'); ?> <?php echo JText::_('VCMBCAHDELETE'); ?></a>
								</div>
							</div>
						</div>
						<?php
					}
					?>

					</div>
				</div>
			</fieldset>

			<fieldset class="adminform vcm-listings-listing-wrap" data-landto="checkouttasks">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('plane-departure'); ?> Checkout Tasks</legend>
					<div class="vcm-params-container">
						<?php
						$current_cout_tasks = $listing->get('check_out_tasks');
						$current_cout_tasks = is_array($current_cout_tasks) || is_object($current_cout_tasks) ? $current_cout_tasks : [];
						$current_cout_tasks = new JObject($current_cout_tasks);
						?>

						<div class="vcm-param-container">
							<div class="vcm-param-setting">
								<span class="vcm-param-setting-comment">Optional tasks to be completed by the guests on or before checking out.</span>
							</div>
						</div>

						<div class="vcm-params-block">
							<?php
							// check out task - return keys
							$task_return_keys = $current_cout_tasks->get('RETURN_KEYS', $current_cout_tasks->get('return_keys'));
							$task_is_present  = is_object($task_return_keys) && isset($task_return_keys->is_present) && (bool)$task_return_keys->is_present;
							$task_detail 	  = is_object($task_return_keys) && !empty($task_return_keys->task_detail) ? (string) $task_return_keys->task_detail : '';
							?>
							<div class="vcm-param-container" data-related-group="checkouttasks">
								<div class="vcm-param-label">Return keys</div>
								<div class="vcm-param-setting">
									<select name="listing[check_out_tasks][return_keys][is_present]">
										<option value="false"<?php echo !$task_is_present ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMNO'); ?></option>
										<option value="true"<?php echo $task_is_present ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMYES'); ?></option>
									</select>
								</div>
							</div>
							<div class="vcm-param-container vcm-param-nested" data-related-group="checkouttasks">
								<div class="vcm-param-label"><?php echo JText::_('VCMVRESSPCINMETADDINFOTXT'); ?></div>
								<div class="vcm-param-setting">
									<textarea name="listing[check_out_tasks][return_keys][task_detail]" maxlength="140"><?php echo $task_is_present ? $this->escape($task_detail) : ''; ?></textarea>
									<span class="vcm-param-setting-comment">Optional instructions for the guests to return keys to a specific place/person on checking out.</span>
								</div>
							</div>
						</div>

						<div class="vcm-params-block">
							<?php
							// check out task - turn_things_off
							$task_turn_things_off = $current_cout_tasks->get('TURN_THINGS_OFF', $current_cout_tasks->get('turn_things_off'));
							$task_is_present  	  = is_object($task_turn_things_off) && isset($task_turn_things_off->is_present) && (bool)$task_turn_things_off->is_present;
							$task_detail 	  	  = is_object($task_turn_things_off) && !empty($task_turn_things_off->task_detail) ? (string) $task_turn_things_off->task_detail : '';
							?>
							<div class="vcm-param-container" data-related-group="checkouttasks">
								<div class="vcm-param-label">Turn things off</div>
								<div class="vcm-param-setting">
									<select name="listing[check_out_tasks][turn_things_off][is_present]">
										<option value="false"<?php echo !$task_is_present ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMNO'); ?></option>
										<option value="true"<?php echo $task_is_present ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMYES'); ?></option>
									</select>
								</div>
							</div>
							<div class="vcm-param-container vcm-param-nested" data-related-group="checkouttasks">
								<div class="vcm-param-label"><?php echo JText::_('VCMVRESSPCINMETADDINFOTXT'); ?></div>
								<div class="vcm-param-setting">
									<textarea name="listing[check_out_tasks][turn_things_off][task_detail]" maxlength="140"><?php echo $task_is_present ? $this->escape($task_detail) : ''; ?></textarea>
									<span class="vcm-param-setting-comment">Optional instructions for the guests to turn lights/appliances etc off on leaving.</span>
								</div>
							</div>
						</div>

						<div class="vcm-params-block">
							<?php
							// check out task - throw_trash
							$task_throw_trash = $current_cout_tasks->get('THROW_TRASH', $current_cout_tasks->get('throw_trash'));
							$task_is_present  = is_object($task_throw_trash) && isset($task_throw_trash->is_present) && (bool)$task_throw_trash->is_present;
							$task_detail 	  = is_object($task_throw_trash) && !empty($task_throw_trash->task_detail) ? (string) $task_throw_trash->task_detail : '';
							?>
							<div class="vcm-param-container" data-related-group="checkouttasks">
								<div class="vcm-param-label">Take care of trash</div>
								<div class="vcm-param-setting">
									<select name="listing[check_out_tasks][throw_trash][is_present]">
										<option value="false"<?php echo !$task_is_present ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMNO'); ?></option>
										<option value="true"<?php echo $task_is_present ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMYES'); ?></option>
									</select>
								</div>
							</div>
							<div class="vcm-param-container vcm-param-nested" data-related-group="checkouttasks">
								<div class="vcm-param-label"><?php echo JText::_('VCMVRESSPCINMETADDINFOTXT'); ?></div>
								<div class="vcm-param-setting">
									<textarea name="listing[check_out_tasks][throw_trash][task_detail]" maxlength="140"><?php echo $task_is_present ? $this->escape($task_detail) : ''; ?></textarea>
									<span class="vcm-param-setting-comment">Optional instructions for the guests to take care of trash before leaving.</span>
								</div>
							</div>
						</div>

						<div class="vcm-params-block">
							<?php
							// check out task - lock_up
							$task_lock_up 	 = $current_cout_tasks->get('LOCK_UP', $current_cout_tasks->get('lock_up'));
							$task_is_present = is_object($task_lock_up) && isset($task_lock_up->is_present) && (bool)$task_lock_up->is_present;
							$task_detail 	 = is_object($task_lock_up) && !empty($task_lock_up->task_detail) ? (string) $task_lock_up->task_detail : '';
							?>
							<div class="vcm-param-container" data-related-group="checkouttasks">
								<div class="vcm-param-label">Lock the doors</div>
								<div class="vcm-param-setting">
									<select name="listing[check_out_tasks][lock_up][is_present]">
										<option value="false"<?php echo !$task_is_present ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMNO'); ?></option>
										<option value="true"<?php echo $task_is_present ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMYES'); ?></option>
									</select>
								</div>
							</div>
							<div class="vcm-param-container vcm-param-nested" data-related-group="checkouttasks">
								<div class="vcm-param-label"><?php echo JText::_('VCMVRESSPCINMETADDINFOTXT'); ?></div>
								<div class="vcm-param-setting">
									<textarea name="listing[check_out_tasks][lock_up][task_detail]" maxlength="140"><?php echo $task_is_present ? $this->escape($task_detail) : ''; ?></textarea>
									<span class="vcm-param-setting-comment">Optional instructions for the guests to lock the doors on leaving.</span>
								</div>
							</div>
						</div>

						<div class="vcm-params-block">
							<?php
							// check out task - gather_towels
							$task_gather_towels = $current_cout_tasks->get('GATHER_TOWELS', $current_cout_tasks->get('gather_towels'));
							$task_is_present  	= is_object($task_gather_towels) && isset($task_gather_towels->is_present) && (bool)$task_gather_towels->is_present;
							$task_detail 	  	= is_object($task_gather_towels) && !empty($task_gather_towels->task_detail) ? (string) $task_gather_towels->task_detail : '';
							?>
							<div class="vcm-param-container" data-related-group="checkouttasks">
								<div class="vcm-param-label">Gather towels</div>
								<div class="vcm-param-setting">
									<select name="listing[check_out_tasks][gather_towels][is_present]">
										<option value="false"<?php echo !$task_is_present ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMNO'); ?></option>
										<option value="true"<?php echo $task_is_present ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMYES'); ?></option>
									</select>
								</div>
							</div>
							<div class="vcm-param-container vcm-param-nested" data-related-group="checkouttasks">
								<div class="vcm-param-label"><?php echo JText::_('VCMVRESSPCINMETADDINFOTXT'); ?></div>
								<div class="vcm-param-setting">
									<textarea name="listing[check_out_tasks][gather_towels][task_detail]" maxlength="140"><?php echo $task_is_present ? $this->escape($task_detail) : ''; ?></textarea>
									<span class="vcm-param-setting-comment">Optional instructions for the guests to keep the towels in a specified place.</span>
								</div>
							</div>
						</div>

						<div class="vcm-params-block">
							<?php
							// check out task - additional_requests
							$task_additional_requests = $current_cout_tasks->get('ADDITIONAL_REQUESTS', $current_cout_tasks->get('additional_requests'));
							$task_is_present  		  = is_object($task_additional_requests) && isset($task_additional_requests->is_present) && (bool)$task_additional_requests->is_present;
							$task_detail 	  		  = is_object($task_additional_requests) && !empty($task_additional_requests->task_detail) ? (string) $task_additional_requests->task_detail : '';
							?>
							<div class="vcm-param-container" data-related-group="checkouttasks">
								<div class="vcm-param-label">Additional requests</div>
								<div class="vcm-param-setting">
									<select name="listing[check_out_tasks][additional_requests][is_present]">
										<option value="false"<?php echo !$task_is_present ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMNO'); ?></option>
										<option value="true"<?php echo $task_is_present ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMYES'); ?></option>
									</select>
								</div>
							</div>
							<div class="vcm-param-container vcm-param-nested" data-related-group="checkouttasks">
								<div class="vcm-param-label"><?php echo JText::_('VCMVRESSPCINMETADDINFOTXT'); ?></div>
								<div class="vcm-param-setting">
									<textarea name="listing[check_out_tasks][additional_requests][task_detail]" maxlength="400"><?php echo $task_is_present ? $this->escape($task_detail) : ''; ?></textarea>
									<span class="vcm-param-setting-comment">Optional additional instructions for the guests on/before checking out.</span>
								</div>
							</div>
						</div>

					</div>
				</div>
			</fieldset>

			<fieldset class="adminform vcm-listings-listing-wrap" data-landto="photos">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"<?php echo $is_editing ? ' ondblclick="vcmPhotoSecretCmdsToggle();"' : ''; ?>><?php VikBookingIcons::e('camera'); ?> <?php echo JText::_('VCM_MNGLISTING_PHOTOS'); ?></legend>
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
								echo $vbo_app->getMediaField('listing[_newphoto][url]', null, array('multiple' => false, 'id' => "airbnb-add-photo-file"));
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
						<div class="vcm-param-container vcm-param-nested">
							<div class="vcm-param-label">
								<?php echo JText::_('VCM_CATEGORY'); ?>
								<?php
								try {
									echo $vbo_app->createPopover(array('title' => JText::_('VCM_CATEGORY'), 'content' => JText::_('VCM_PHOTO_CATEGORY_HELP')));
								} catch (Exception $e) {
									// do nothing
								}
								?>
							</div>
							<div class="vcm-param-setting">
								<select name="listing[_newphoto][category]" class="vcm-listing-editable vcm-airbphoto-categorysel" onchange="vcmCheckPhotoCategory(this.value);">
									<option value=""></option>
									<option value="listing"><?php echo JText::_('VCM_LISTING'); ?></option>
									<option value="listing_amenity"><?php echo JText::_('VCM_LISTING') . ' - ' . JText::_('VCMBCAHAMENITY') . '/' . JText::_('VCM_ACCESSIBILITY'); ?></option>
									<option value="room"><?php echo JText::_('VCM_LISTING_ROOM'); ?></option>
									<option value="room_amenity"><?php echo JText::_('VCM_LISTING_ROOM') . ' - ' . JText::_('VCMBCAHAMENITY') . '/' . JText::_('VCM_ACCESSIBILITY'); ?></option>
								</select>
							</div>
						</div>
						<div class="vcm-param-container vcm-param-nested" id="vcm-airbphoto-roomid" style="display: none;">
							<div class="vcm-param-label"><?php echo JText::_('VCM_LISTING_ROOM'); ?></div>
							<div class="vcm-param-setting">
								<select name="listing[_newphoto][room_id]" class="vcm-listing-editable"></select>
							</div>
						</div>
						<div class="vcm-param-container vcm-param-nested" id="vcm-airbphoto-amenity" style="display: none;">
							<div class="vcm-param-label"><?php echo JText::_('VCMBCAHAMENITY'); ?></div>
							<div class="vcm-param-setting">
								<select name="listing[_newphoto][amenity]" class="vcm-listing-editable"></select>
							</div>
						</div>
						<?php
						// display current photos, if any
						$current_photos = $listing->get('_photos', array());
						$current_photos = !is_array($current_photos) ? array() : $current_photos;
						// we also build an associative array of room-id and room-type for the photos
						$listing_rooms_data = array();
						$current_rooms = $listing->get('_rooms', array());
						$current_rooms = !is_array($current_rooms) ? array() : $current_rooms;
						foreach ($current_rooms as $k => $list_room) {
							// wrap listing room object into a JObject
							$room = new JObject($list_room);
							// Airbnb listing room id
							$room_id = $room->get('id');
							$room_id = empty($room_id) ? $room->get('id_str') : $room_id;
							// listing room type
							$current_room_type = $room->get('room_type');
							// push relation
							$listing_rooms_data[$room_id] = $current_room_type;
						}

						?>
						<div class="vcm-airbphotos-gallery-thumbs-inner">
						<?php
						foreach ($current_photos as $k => $list_photo) {
							// wrap listing photo object into a JObject
							$photo = new JObject($list_photo);
							$photo_id = $photo->get('id');
							if (empty($photo_id)) {
								$photo_id = $photo->get('id_str');
							}
							if (empty($photo_id)) {
								// invalid photo structure
								continue;
							}
							$small_url = $photo->get('small_url');
							$large_url = $photo->get('extra_medium_url');
							$caption = $photo->get('caption');
							$sort_order = $photo->get('sort_order');
							$photo_room_id = $photo->get('room_id', $photo->get('room_id_str', ''));
							$photo_amenity = $photo->get('amenity');
							$photo_category = $photo->get('category', 'LISTING');
							if (empty($small_url) && empty($large_url)) {
								continue;
							}

							/**
							 * To reduce traffic and avoid gateway timeout (504) errors for long requests,
							 * we need to disable the hidden input fields involving a current photo, or by
							 * saving without actually making any changes to the photos, these will be updated.
							 * Therefore, such hidden input fields will take the "disabled" property by default.
							 * They will be "enabled" via JS upon making changes.
							 * 
							 * @since 	1.8.4
							 */

							?>
							<div class="vcm-airbphotos-gallery-thumb">
								<div class="vcm-airbphotos-gallery-thumb-inner">
									<div class="vcm-airbphotos-gallery-thumb-img">
										<img src="<?php echo !empty($small_url) ? $small_url : $large_url; ?>" class="vcm-airbphotos-img" data-large-url="<?php echo !empty($large_url) ? $large_url : $small_url; ?>" data-caption="<?php echo $this->escape($caption); ?>" data-propgallery="<?php echo $this->listing->id; ?>" data-index="<?php echo $k; ?>" />
										<input type="hidden" class="vcm-hidden-inp-photo-id" name="listing[_photos][id][]" value="<?php echo $photo_id; ?>" disabled />
										<input type="hidden" class="vcm-hidden-inp-photo-caption" name="listing[_photos][caption][]" value="<?php echo $this->escape($caption); ?>" disabled />
										<input type="hidden" class="vcm-hidden-inp-photo-order" name="listing[_photos][sort_order][]" value="<?php echo $sort_order; ?>" disabled />
										<input type="hidden" class="vcm-hidden-inp-photo-prevpos" name="listing[_photos][prevpos][]" value="<?php echo $sort_order; ?>" disabled />
									<?php
									if (stripos($photo_category, 'amenity') !== false) {
										// display an icon for the accessibility amenity photo
										if (!strcasecmp($photo_category, 'room_amenity')) {
											// photo category must be "ROOM_AMENITY"
											$room_tip = isset($listing_rooms_data[$photo_room_id]) ? $listing_rooms_data[$photo_room_id] : '';
											$tag_tip = JText::_('VCM_LISTING_ROOM') . (!empty($room_tip) ? ' (' . $room_tip . ')' : '') . ' - ' . JText::_('VCM_ACCESSIBILITY') . ' (' . $photo_amenity . ')';
										} else {
											// photo category must be "LISTING_AMENITY"
											$tag_tip = JText::_('VCM_LISTING') . ' - ' . JText::_('VCM_ACCESSIBILITY') . ' (' . $photo_amenity . ')';
										}
										?>
										<span class="vcm-airbphotos-img-typetag" title="<?php echo htmlentities($tag_tip); ?>"><?php VikBookingIcons::e('universal-access'); ?></span>
										<?php
									} elseif (!strcasecmp($photo_category, 'room')) {
										// display a different icon for a listing-room photo (category "ROOM")
										$room_tip = isset($listing_rooms_data[$photo_room_id]) ? $listing_rooms_data[$photo_room_id] : '';
										$tag_tip = JText::_('VCM_LISTING_ROOM') . (!empty($room_tip) ? ' (' . $room_tip . ')' : '');
										?>
										<span class="vcm-airbphotos-img-typetag vcm-airbphotos-img-typetag-room" title="<?php echo htmlentities($tag_tip); ?>"><?php VikBookingIcons::e('couch'); ?></span>
										<?php
									}
									?>
									</div>
									<div class="vcm-airbphotos-gallery-thumb-bottom">
										<div class="vcm-airbphotos-gallery-thumb-editimg">
											<button type="button" class="btn btn-primary" onclick="vcmEditPhotoCaption('<?php echo $k; ?>');"><?php VikBookingIcons::e('edit'); ?></button>
										</div>
										<div class="vcm-airbphotos-gallery-thumb-rmimg">
											<a class="btn btn-danger" href="index.php?option=com_vikchannelmanager&task=airbnblst.delete_photo&photo_id=<?php echo $photo_id; ?>&listing_id=<?php echo $this->listing->id; ?>" onclick="return vcmConfirmDelete();"><?php VikBookingIcons::e('trash'); ?></a>
										</div>
									</div>
								</div>
							</div>
							<?php
						}
						?>
						</div>

						<div class="vcm-airbphotos-secret-cmds" style="display: none;">
							<button type="button" class="btn btn-danger" onclick="vcmPhotoUpdateToggle(true, true);">Exclude all photos</button>
							<button type="button" class="btn btn-danger" onclick="vcmPhotoUpdateToggle(false, true);">Exclude photo index</button>
							<button type="button" class="btn btn-primary" onclick="vcmPhotoUpdateToggle(true, false);">Include all photos</button>
							<button type="button" class="btn btn-primary" onclick="vcmPhotoUpdateToggle(false, false);">Include photo index</button>
						</div>

						<script type="text/javascript">
							function vcmPhotoSecretCmdsToggle() {
								jQuery('.vcm-airbphotos-secret-cmds').toggle();
							}
							function vcmPhotoUpdateToggle(all, exclude) {
								if (all) {
									jQuery('.vcm-airbphotos-gallery-thumb').find('input[type="hidden"]').prop('disabled', exclude);
									return;
								}
								if (!all) {
									var photo_index = prompt('Insert the photo position number to ' + (exclude ? 'exclude' : 'include') + ' (starting from 1)', '');
									if (photo_index != null && !isNaN(photo_index)) {
										photo_index = photo_index - 1;
										var photo_wrap = jQuery('.vcm-airbphotos-gallery-thumb').eq(photo_index);
										if (!photo_wrap || !photo_wrap.length) {
											alert('Photo not found with the given position number. Retry.');
											return;
										}
										photo_wrap.find('input[type="hidden"]').prop('disabled', exclude);
									}
									return;
								}
							}
						</script>
						<?php
					}
					?>
					</div>
				</div>
			</fieldset>

			<fieldset class="adminform vcm-listings-listing-wrap" data-landto="rooms">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('bed'); ?> <?php echo JText::_('VCM_MNGLISTING_ROOMSDESCR'); ?></legend>
					<div class="vcm-params-container">
					<?php
					if (!$is_editing) {
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-setting">
								<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_MNGLISTING_ROOMSDESCR_ONLYEDIT'); ?></span>
							</div>
						</div>
						<?php
					} else {
						// display current rooms, if any
						$current_rooms = $listing->get('_rooms', array());
						$current_rooms = !is_array($current_rooms) ? array() : $current_rooms;
						// push fake room object to use it for cloning
						$fake_room = new stdClass;
						$fake_room->is_fake = 1;
						array_push($current_rooms, $fake_room);
						//
						?>
						<div class="vcm-listings-rooms-wrap">
						<?php
						foreach ($current_rooms as $k => $list_room) {
							// wrap listing room object into a JObject
							$room = new JObject($list_room);
							// check whether the room is "fake", hence a placeholder
							$is_fake = $room->get('is_fake', 0);
							// Airbnb listing room id
							$room_id = !$is_fake ? $room->get('id') : '';
							$room_id = empty($room_id) && !$is_fake ? $room->get('id_str') : $room_id;
							// set input key for array values
							$inp_key = $is_fake ? '%d' : $k;

							?>
							<div class="<?php echo $is_fake ? 'vcm-listing-room-new' : 'vcm-listing-room'; ?>" data-room-number="<?php echo ($k + 1); ?>" style="<?php echo $is_fake ? 'display: none;' : ''; ?>">
								<?php
								if (!empty($room_id)) {
									// hidden fields are not disabled, so we only display it for real rooms
									?>
									<input type="hidden" name="listing[_rooms][id][<?php echo $inp_key; ?>]" value="<?php echo $room_id; ?>" class="vcm-hid-listing-room-id" />
									<?php
								}
								$current_room_type = $room->get('room_type');
								?>
								<div class="vcm-param-container vcm-listing-noedit">
									<div class="vcm-param-label">Room type</div>
									<div class="vcm-param-setting">
										<select name="listing[_rooms][type][<?php echo $inp_key; ?>]" class="vcm-listing-room-type">
											<option value=""></option>
											<option value="bedroom"<?php echo $current_room_type == 'bedroom' ? ' selected="selected"' : ''; ?>>Bedroom</option>
											<option value="backyard"<?php echo $current_room_type == 'backyard' ? ' selected="selected"' : ''; ?>>Backyard</option>
											<option value="dining_room"<?php echo $current_room_type == 'dining_room' ? ' selected="selected"' : ''; ?>>Dining room</option>
											<option value="exterior"<?php echo $current_room_type == 'exterior' ? ' selected="selected"' : ''; ?>>Exterior</option>
											<option value="front_yard"<?php echo $current_room_type == 'front_yard' ? ' selected="selected"' : ''; ?>>Front yard</option>
											<option value="full_bathroom"<?php echo $current_room_type == 'full_bathroom' ? ' selected="selected"' : ''; ?>>Full bathroom</option>
											<option value="half_bathroom"<?php echo $current_room_type == 'half_bathroom' ? ' selected="selected"' : ''; ?>>Half bathroom</option>
											<option value="hot_tub"<?php echo $current_room_type == 'hot_tub' ? ' selected="selected"' : ''; ?>>Hot tub</option>
											<option value="garage"<?php echo $current_room_type == 'garage' ? ' selected="selected"' : ''; ?>>Garage</option>
											<option value="gym"<?php echo $current_room_type == 'gym' ? ' selected="selected"' : ''; ?>>Gym</option>
											<option value="kitchen"<?php echo $current_room_type == 'kitchen' ? ' selected="selected"' : ''; ?>>Kitchen</option>
											<option value="kitchenette"<?php echo $current_room_type == 'kitchenette' ? ' selected="selected"' : ''; ?>>Kitchenette</option>
											<option value="laundry_room"<?php echo $current_room_type == 'laundry_room' ? ' selected="selected"' : ''; ?>>Laundry room</option>
											<option value="living_room"<?php echo $current_room_type == 'living_room' ? ' selected="selected"' : ''; ?>>Living room</option>
											<option value="office"<?php echo $current_room_type == 'office' ? ' selected="selected"' : ''; ?>>Office</option>
											<option value="patio"<?php echo $current_room_type == 'patio' ? ' selected="selected"' : ''; ?>>Patio</option>
											<option value="pool"<?php echo $current_room_type == 'pool' ? ' selected="selected"' : ''; ?>>Pool</option>
											<option value="studio"<?php echo $current_room_type == 'studio' ? ' selected="selected"' : ''; ?>>Studio</option>
										</select>
									<?php
									if ($is_editing && $current_room_type) {
										// listing room type is not available in PUT (update) mode
										?>
										<input type="hidden" name="listing[_rooms][type][<?php echo $inp_key; ?>]" value="<?php echo $current_room_type; ?>" />
										<?php
									}
									?>
										<span class="vcm-param-setting-comment">Choose the type of room for the current listing.</span>
									</div>
								</div>

								<?php
								$room_beds = $room->get('beds', array());
								// push fake bed object to use it for cloning
								$fake_bed = new stdClass;
								$fake_bed->is_fake = 1;
								array_push($room_beds, $fake_bed);
								//
								?>
								<div class="vcm-param-container">
									<div class="vcm-param-label">Beds</div>
									<div class="vcm-param-setting">
										<button type="button" class="btn vcm-config-btn" onclick="vcmAddRoomBed(this);"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::_('VCMBCAHADD'); ?></button>
									</div>
								</div>

								<div class="vcm-listing-rooms-beds-wrap">
								<?php
								foreach ($room_beds as $bedk => $room_bed) {
									// wrap bed object into a JObject
									$bed = new JObject($room_bed);
									// check whether the bed is "fake", hence a placeholder
									$is_fake_bed = $bed->get('is_fake', 0);
									// current bed type
									$current_bed_type = $bed->get('type');
									?>
									<div class="<?php echo $is_fake_bed ? 'vcm-listing-rooms-bed-new' : 'vcm-listing-rooms-bed'; ?>" style="<?php echo $is_fake_bed ? 'display: none;' : ''; ?>">
										<div class="vcm-param-container vcm-param-nested">
											<div class="vcm-param-label">Type</div>
											<div class="vcm-param-setting">
												<select name="listing[_rooms][beds][<?php echo $inp_key; ?>][type][]" class="vcm-inp-beds">
													<option value=""></option>
													<option value="king_bed"<?php echo $current_bed_type == 'king_bed' ? ' selected="selected"' : ''; ?>>King bed</option>
													<option value="queen_bed"<?php echo $current_bed_type == 'queen_bed' ? ' selected="selected"' : ''; ?>>Queen bed</option>
													<option value="double_bed"<?php echo $current_bed_type == 'double_bed' ? ' selected="selected"' : ''; ?>>Double bed</option>
													<option value="single_bed"<?php echo $current_bed_type == 'single_bed' ? ' selected="selected"' : ''; ?>>Single bed</option>
													<option value="sofa_bed"<?php echo $current_bed_type == 'sofa_bed' ? ' selected="selected"' : ''; ?>>Sofa bed</option>
													<option value="couch"<?php echo $current_bed_type == 'couch' ? ' selected="selected"' : ''; ?>>Couch</option>
													<option value="air_mattress"<?php echo $current_bed_type == 'air_mattress' ? ' selected="selected"' : ''; ?>>Air mattress</option>
													<option value="bunk_bed"<?php echo $current_bed_type == 'bunk_bed' ? ' selected="selected"' : ''; ?>>Bunk bed</option>
													<option value="floor_mattress"<?php echo $current_bed_type == 'floor_mattress' ? ' selected="selected"' : ''; ?>>Floor mattress</option>
													<option value="toddler_bed"<?php echo $current_bed_type == 'toddler_bed' ? ' selected="selected"' : ''; ?>>Toddler bed</option>
													<option value="crib"<?php echo $current_bed_type == 'crib' ? ' selected="selected"' : ''; ?>>Crib</option>
													<option value="water_bed"<?php echo $current_bed_type == 'water_bed' ? ' selected="selected"' : ''; ?>>Water bed</option>
													<option value="hammock"<?php echo $current_bed_type == 'hammock' ? ' selected="selected"' : ''; ?>>Hammock</option>
												</select>
											</div>
										</div>
										<div class="vcm-param-container vcm-param-nested">
											<div class="vcm-param-label">Quantity</div>
											<div class="vcm-param-setting">
												<input type="number" name="listing[_rooms][beds][<?php echo $inp_key; ?>][quantity][]" min="1" value="<?php echo $bed->get('quantity'); ?>" class="vcm-inp-beds" />
											</div>
										</div>
									</div>
									<?php
								}
								?>
								</div>

								<?php
								$accessibility_features = (array) $room->get('accessibility_features', []);
								// push fake accessibility feature object to use it for cloning
								$fake_af = new stdClass;
								$fake_af->is_fake = 1;
								array_push($accessibility_features, $fake_af);
								?>
								<div class="vcm-param-container">
									<div class="vcm-param-label">Accessibility Features</div>
									<div class="vcm-param-setting">
										<button type="button" class="btn vcm-config-btn" onclick="vcmAddRoomAccessibilityFeature(this);"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::_('VCMBCAHADD'); ?></button>
										<span class="vcm-param-setting-comment">List of accessibility amenities in the room.</span>
									</div>
								</div>

								<div class="vcm-listing-rooms-af-wrap">
								<?php
								foreach ($accessibility_features as $afk => $room_af) {
									// wrap accessibility feature object into a JObject
									$af = new JObject($room_af);
									// check whether the AF is "fake", hence a placeholder
									$is_fake_af = $af->get('is_fake', 0);
									// current AF type
									$current_af_type = $af->get('type');
									?>
									<div class="<?php echo $is_fake_af ? 'vcm-listing-rooms-af-new' : 'vcm-listing-rooms-af'; ?>" style="<?php echo $is_fake_af ? 'display: none;' : ''; ?>">
										<div class="vcm-param-container vcm-param-nested">
											<div class="vcm-param-label">Type</div>
											<div class="vcm-param-setting">
												<select name="listing[_rooms][accessibility_features][<?php echo $inp_key; ?>][type][]" class="vcm-inp-afs">
													<option value=""></option>
													<optgroup label="Accessible guest entrance and parking">
														<option value="disabled_parking_spot"<?php echo $current_af_type == 'disabled_parking_spot' ? ' selected="selected"' : ''; ?>>City-approved disabled parking spot, or a parking space that is at least 8 feet wide.</option>
														<option value="path_to_entrance_lit_at_night"<?php echo $current_af_type == 'path_to_entrance_lit_at_night' ? ' selected="selected"' : ''; ?>>Lit path to the guest entrance.</option>
														<option value="flat_smooth_pathway_to_front_door"<?php echo $current_af_type == 'flat_smooth_pathway_to_front_door' ? ' selected="selected"' : ''; ?>>Step-free path to the guest entrance.</option>
														<option value="home_step_free_access"<?php echo $current_af_type == 'home_step_free_access' ? ' selected="selected"' : ''; ?>>Step-free guest entrance.</option>
														<option value="home_wide_doorway"<?php echo $current_af_type == 'home_wide_doorway' ? ' selected="selected"' : ''; ?>>Guest entrance wider than 32 inches.</option>
													</optgroup>
													<optgroup label="Accessible bedroom">
														<option value="step_free_access"<?php echo $current_af_type == 'step_free_access' ? ' selected="selected"' : ''; ?>>Step-free access to the bedroom.</option>
														<option value="wide_doorway"<?php echo $current_af_type == 'wide_doorway' ? ' selected="selected"' : ''; ?>>Bedroom entrance wider than 32 inches.</option>
													</optgroup>
													<optgroup label="Accessible bathroom">
														<option value="step_free_access"<?php echo $current_af_type == 'step_free_access' ? ' selected="selected"' : ''; ?>>Step-free access to the bathroom.</option>
														<option value="wide_doorway"<?php echo $current_af_type == 'wide_doorway' ? ' selected="selected"' : ''; ?>>Bathroom entrance wider than 32 inches.</option>
														<option value="grab_rails_in_toilet"<?php echo $current_af_type == 'grab_rails_in_toilet' ? ' selected="selected"' : ''; ?>>Fixed grab bars for toilet.</option>
														<option value="grab_rails_in_shower"<?php echo $current_af_type == 'grab_rails_in_shower' ? ' selected="selected"' : ''; ?>>Fixed grab bars for shower.</option>
														<option value="rollin_shower"<?php echo $current_af_type == 'rollin_shower' ? ' selected="selected"' : ''; ?>>Step-free shower (not valid for half bathrooms).</option>
														<option value="shower_chair"<?php echo $current_af_type == 'shower_chair' ? ' selected="selected"' : ''; ?>>Shower or bath chair (not valid for half bathrooms).</option>
													</optgroup>
													<optgroup label="Adaptive equipment">
														<option value="ceiling_hoist"<?php echo $current_af_type == 'ceiling_hoist' ? ' selected="selected"' : ''; ?>>Ceiling or mobile hoist.</option>
														<option value="pool_hoist"<?php echo $current_af_type == 'pool_hoist' ? ' selected="selected"' : ''; ?>>Swimming pool or hot tub hoist.</option>
													</optgroup>
													<optgroup label="Accessibility in other rooms">
														<option value="step_free_access"<?php echo $current_af_type == 'step_free_access' ? ' selected="selected"' : ''; ?>>Step-free access to the room.</option>
														<option value="wide_doorway"<?php echo $current_af_type == 'wide_doorway' ? ' selected="selected"' : ''; ?>>Room entrance wider than 32 inches.</option>
													</optgroup>
												</select>
											</div>
										</div>
										<div class="vcm-param-container vcm-param-nested">
											<div class="vcm-param-label">Quantity</div>
											<div class="vcm-param-setting">
												<input type="number" name="listing[_rooms][accessibility_features][<?php echo $inp_key; ?>][quantity][]" min="1" value="<?php echo $af->get('quantity'); ?>" class="vcm-inp-afs" />
											</div>
										</div>
									</div>
									<?php
								}
								?>
								</div>

							<?php
							if (!$is_fake) {
								?>
								<div class="vcm-param-container">
									<div class="vcm-param-label">&nbsp;</div>
									<div class="vcm-param-setting">
										<a class="btn btn-danger" href="index.php?option=com_vikchannelmanager&task=airbnblst.delete_room&room_id=<?php echo $room_id; ?>&listing_id=<?php echo $this->listing->id; ?>" onclick="return vcmConfirmDelete();"><?php VikBookingIcons::e('trash'); ?> <?php echo JText::_('VCMBCAHDELETE') . ' (' . $room_id . ')'; ?></a>
									</div>
								</div>
								<?php
							} else {
								?>
								<div class="vcm-param-container">
									<div class="vcm-param-label">&nbsp;</div>
									<div class="vcm-param-setting">
										<button type="button" class="btn btn-danger" onclick="vcmRemoveNewRoom(this);"><?php VikBookingIcons::e('times-circle'); ?></button>
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

						<div class="vcm-listing-room-add">
							<div class="vcm-param-container">
								<div class="vcm-param-label">
									<button type="button" class="btn vcm-config-btn" onclick="vcmAddRoom();"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::_('VCMBCAHADD'); ?></button>
								</div>
							</div>
						</div>
						<?php
					}
					?>
					</div>
				</div>
			</fieldset>

		<?php
		if ($is_editing) {
			?>
			<fieldset class="adminform vcm-listings-listing-wrap" data-landto="calendars">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('calendar-check'); ?> <?php echo JText::_('VCM_CALENDARS'); ?></legend>
					<div class="vcm-params-container">
						<div class="vcm-param-container">
							<div class="vcm-param-setting">
								<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_AIRBNB_CHECK_CALENDARS'); ?></span>
							</div>
						</div>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMFROMDATE'); ?></div>
							<div class="vcm-param-setting">
								<?php echo $vbo_app->getCalendar(date('Y-m-d'), 'cal_fdate', 'cal_fdate', '%Y-%m-%d', array('class' => 'vcm-listing-editable')); ?>
							</div>
						</div>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMTODATE'); ?></div>
							<div class="vcm-param-setting">
								<?php echo $vbo_app->getCalendar(date('Y-m-') . date('t'), 'cal_tdate', 'cal_tdate', '%Y-%m-%d', array('class' => 'vcm-listing-editable')); ?>
							</div>
						</div>
						<div class="vcm-param-container">
							<div class="vcm-param-label">
								<button type="button" class="btn vcm-config-btn" onclick="vcmLoadCalendars();"><?php VikBookingIcons::e('sync'); ?> <?php echo JText::_('VCM_DOWNLOAD'); ?></button>
							</div>
						</div>
					</div>
					<div class="vcm-calendars-container table-responsive" id="vcm-calendars-response"></div>
				</div>
			</fieldset>

			<script type="text/javascript">
				function vcmLoadCalendars() {
					// display loading overlay
					vcmShowLoading();

					// make the ajax request to the controller
					VBOCore.doAjax(
						"<?php echo VikChannelManager::ajaxUrl('index.php?option=com_vikchannelmanager&task=airbnblst.listingcalendars&e4j_debug=' . VikRequest::getInt('e4j_debug', 0, 'request')); ?>",
						{
							listing_id: "<?php echo $listing->get('id'); ?>",
							from_date:  jQuery('#cal_fdate').val(),
							to_date:    jQuery('#cal_tdate').val()
						},
						(resp) => {
							// hide loading overlay
							vcmStopLoading();

							// parse the response
							try {
								resp = JSON.parse(resp);
							} catch (e) {
								console.error('Could not decode response', e, resp);
								resp = null;
							}
							if (!resp || !resp.length) {
								// invalid response, it has to be an array of calendar objects
								alert("Invalid response.");
								console.error('Invalid response', resp);
								return;
							}

							// unset current content
							jQuery('#vcm-calendars-response').html('');

							// build new content
							var html_calendars = vcmRenderCalendarsResponse(resp);
							if (html_calendars !== false) {
								// set new content
								jQuery('#vcm-calendars-response').html(html_calendars);
							}
						},
						(err) => {
							alert("Error performing the request.");
							console.log(err.responseText);
							vcmStopLoading();
						}
					);
				}

				function vcmRenderCalendarsResponse(resp) {
					// define icons
					var person_icon = '<?php VikBookingIcons::e('male'); ?>';
					var adults_icon = '<?php VikBookingIcons::e('users'); ?>';
					var nights_icon = '<?php VikBookingIcons::e('moon'); ?>';
					var currency    = '<?php echo $this->escape($listing_currency); ?>';

					if (!resp || !resp.length || typeof resp != 'object') {
						// response argument must be an array
						return false;
					}
					// prepare vars
					var dates_parsed   = 0,
						listing_basect = '<?php echo isset($pricing_settings) ? $pricing_settings->get('default_daily_price', 0) : 0; ?>',
						html_calendars = '';

					// build table and head (do NOT add cells unsafely, because some may be using a COLSPAN with a precise count)
					html_calendars += '<table class="<?php echo $vik->getAdminTableClass(); ?> vcm-list-table vcm-calendars-table">' + "\n";
					html_calendars += '	<thead>' + "\n";
					html_calendars += '		<tr>' + "\n";
					html_calendars += '			<th class="left">' + Joomla.JText._('VCMRESLOGSDT') + '</th>' + "\n";
					html_calendars += '			<th class="center">' + Joomla.JText._('VCM_UNITS') + '</th>' + "\n";
					html_calendars += '			<th class="center" title="' + Joomla.JText._('VCMBPROMAMINNIGHTS') + '">' + Joomla.JText._('VCMRARRESTRMINLOS').replace(':', '') + '</th>' + "\n";
					html_calendars += '			<th class="center" title="' + Joomla.JText._('VCMMAXNIGHTS') + '">' + Joomla.JText._('VCMRARRESTRMAXLOS').replace(':', '') + '</th>' + "\n";
					html_calendars += '			<th class="center" title="' + Joomla.JText._('VCMRARRESTRCLOSEDARRIVAL') + '">CTA</th>' + "\n";
					html_calendars += '			<th class="center" title="' + Joomla.JText._('VCMRARRESTRCLOSEDDEPARTURE') + '">CTD</th>' + "\n";
					html_calendars += '			<th class="center">' + Joomla.JText._('VCM_PRICE') + '</th>' + "\n";
					html_calendars += '		</tr>' + "\n";
					html_calendars += '	</thead>' + "\n";

					// start body
					html_calendars += '	<tbody>' + "\n";

					// check listing default (base) cost per night
					listing_basect = isNaN(listing_basect) ? 0 : parseInt(listing_basect);
					
					// build day rows
					for (var i = 0; i < resp.length; i++) {
						if (!resp.hasOwnProperty(i)) {
							continue;
						}

						// count rooms to sell
						var rooms_to_sell = 0;
						if (resp[i]['availability'] == 'available') {
							rooms_to_sell = 1;
							if (resp[i].hasOwnProperty('available_count') && !isNaN(resp[i]['available_count'])) {
								rooms_to_sell = parseInt(resp[i]['available_count']);
								rooms_to_sell = rooms_to_sell > 0 ? rooms_to_sell : 1;
							}
						}

						// style badges
						var display_uleft  = '<span class="badge ' + (rooms_to_sell > 0 ? 'badge-success' : 'badge-error') + '">' + rooms_to_sell + '</span>';
						var display_minlos = '<span class="badge' + (resp[i]['min_nights'] > 1 ? ' badge-warning' : '') + '">' + resp[i]['min_nights'] + '</span>';
						var display_maxlos = '<span class="badge">' + resp[i]['max_nights'] + '</span>';
						var display_cta    = '<span class="badge' + (resp[i]['closed_to_arrival'] ? ' badge-error' : '') + '">' + (resp[i]['closed_to_arrival'] ? Joomla.JText._('VCMYES') : Joomla.JText._('VCMNO')) + '</span>';
						var display_ctd    = '<span class="badge' + (resp[i]['closed_to_departure'] ? ' badge-error' : '') + '">' + (resp[i]['closed_to_departure'] ? Joomla.JText._('VCMYES') : Joomla.JText._('VCMNO')) + '</span>';

						// check listing daily price
						var listing_daycost = isNaN(resp[i]['daily_price']) ? 0 : parseInt(resp[i]['daily_price']);
						// update listing base cost, if necessary
						listing_basect 		= listing_basect > 0 ? listing_basect : listing_daycost;
						// display a class indicating a higher, lower or equal price
						var cost_badge_cls  = (listing_basect > 0 && listing_basect != listing_daycost ? (listing_daycost > listing_basect ? 'badge-success' : 'badge-error') : 'badge-info');
						var display_price   = '<span class="badge ' + cost_badge_cls + '">' + currency + ' ' + resp[i]['daily_price'] + '</span>';

						// try to get the short weekday name from browser's locale
						var short_wday = '';
						try {
							// get date string parts to instantiate a full and precise Date object
							var dparts = resp[i]['date'].split('-');
							var dobj = new Date(dparts[0], (dparts[1] - 1), dparts[2], 12, 0, 0);
							short_wday = dobj.toLocaleString(window.navigator.language, {timeZone: 'UTC', weekday: 'short'});
						} catch (e) {
							// do nothing
						}
						short_wday = short_wday && short_wday.length ? (short_wday + ', ') : '';

						// build day-row
						html_calendars += '		<tr>' + "\n";
						html_calendars += '			<td class="left"><span class="vcm-txt-strongest">' + short_wday + resp[i]['date'] + '</span></td>' + "\n";
						html_calendars += '			<td class="center">' + display_uleft + '</td>' + "\n";
						html_calendars += '			<td class="center">' + display_minlos + '</td>' + "\n";
						html_calendars += '			<td class="center">' + display_maxlos + '</td>' + "\n";
						html_calendars += '			<td class="center">' + display_cta + '</td>' + "\n";
						html_calendars += '			<td class="center">' + display_ctd + '</td>' + "\n";
						html_calendars += '			<td class="center">' + display_price + '</td>' + "\n";
						html_calendars += '		</tr>' + "\n";

						// check los records
						if (resp[i].hasOwnProperty('los_records')) {
							for (var los_occ in resp[i]['los_records']) {
								if (!resp[i]['los_records'].hasOwnProperty(los_occ) || isNaN(los_occ) || !resp[i]['los_records'][los_occ] || !resp[i]['los_records'][los_occ].length) {
									continue;
								}

								// check which icon to use
								var use_people_icon = los_occ > 1 ? adults_icon : person_icon;

								// first LOS cell content
								var first_los_cell = use_people_icon + ' ' + los_occ + ' &nbsp; ' + Joomla.JText._('VCM_LOSPRICES');

								// build sub-day-row for LOS rates
								html_calendars += '		<tr class="vcm-nested-subrow">' + "\n";
								html_calendars += '			<td class="left vcm-nested-subrow-cell"><span class="badge badge-warning">' + first_los_cell + '</span></td>' + "\n";
								html_calendars += '			<td class="left vcm-subrow-cell-scroll-horiz" colspan="6">' + "\n";
								html_calendars += '				<div class="vcm-airbcals-losrows-container">' + "\n";
								for (var price_point in resp[i]['los_records'][los_occ]) {
									if (!resp[i]['los_records'][los_occ].hasOwnProperty(price_point)) {
										continue;
									}
									var num_nights = resp[i]['los_records'][los_occ][price_point]['nights'];
									var los_price  = resp[i]['los_records'][los_occ][price_point]['price'];
									var display_los_point = num_nights + ' ' + nights_icon + ' ' + currency + ' ' + los_price;
									html_calendars += '				<span class="badge badge-info vcm-airbcals-losrows-pricepoint">' + display_los_point + '</span>' + "\n";
								}
								html_calendars += '				</div>' + "\n";
								html_calendars += '			</td>' + "\n";
								html_calendars += '		</tr>' + "\n";
							}
						}

						// increase counter
						dates_parsed++;
					}

					// close body
					html_calendars += '	</tbody>' + "\n";

					// close table
					html_calendars += '</table>' + "\n";

					return dates_parsed > 0 ? html_calendars : false;
				}
			</script>
			<?php
		}
		?>

		</div>

		<div class="vcm-config-maintab-right">

			<fieldset class="adminform vcm-listings-listing-wrap" data-landto="booksettings">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('cogs'); ?> <?php echo JText::_('VCM_MNGLISTING_BOOKSETTINGS'); ?></legend>
					<div class="vcm-params-container">
					<?php
					if (!$is_editing) {
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-setting">
								<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_MNGLISTING_BOOKSETTINGS_ONLYEDIT'); ?></span>
							</div>
						</div>
						<?php
					} else {
						// display current booking settings
						$booking_settings = $listing->get('_bookingsettings');
						if (!is_object($booking_settings)) {
							// we instantiate an empty JObject
							$booking_settings = new JObject;
						} else {
							$booking_settings = new JObject($booking_settings);
						}
						?>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Check-in Time Start</div>
							<div class="vcm-param-setting">
								<?php
								$cur_checkin_start = $booking_settings->get('check_in_time_start');
								?>
								<select name="listing[_bookingsettings][check_in_time_start]">
									<option value=""></option>
									<option value="FLEXIBLE"<?php echo $cur_checkin_start == 'FLEXIBLE' ? ' selected="selected"' : ''; ?>>Flexible</option>
								<?php
								for ($i = 0; $i < 24; $i++) {
									?>
									<option value="<?php echo $i; ?>"<?php echo $cur_checkin_start == (string)$i ? ' selected="selected"' : ''; ?>><?php echo ($i < 10 ? "0{$i}" : $i) . ':00'; ?></option>
									<?php
								}
								?>
								</select>
								<span class="vcm-param-setting-comment">Earliest time the guest can check in.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Check-in Time End</div>
							<div class="vcm-param-setting">
								<?php
								$cur_checkin_end = $booking_settings->get('check_in_time_end');
								?>
								<select name="listing[_bookingsettings][check_in_time_end]">
									<option value=""></option>
									<option value="FLEXIBLE"<?php echo $cur_checkin_end == 'FLEXIBLE' ? ' selected="selected"' : ''; ?>>Flexible</option>
								<?php
								for ($i = 0; $i < 24; $i++) {
									?>
									<option value="<?php echo $i; ?>"<?php echo $cur_checkin_end == (string)$i ? ' selected="selected"' : ''; ?>><?php echo ($i < 10 ? "0{$i}" : $i) . ':00'; ?></option>
									<?php
								}
								?>
								</select>
								<span class="vcm-param-setting-comment">Latest time the guest can check in.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Check-out Time</div>
							<div class="vcm-param-setting">
								<?php
								$cur_checkout_time = $booking_settings->get('check_out_time');
								?>
								<select name="listing[_bookingsettings][check_out_time]">
									<option value=""></option>
									<option value="FLEXIBLE"<?php echo $cur_checkout_time == 'FLEXIBLE' ? ' selected="selected"' : ''; ?>>Flexible</option>
								<?php
								for ($i = 0; $i < 24; $i++) {
									?>
									<option value="<?php echo $i; ?>"<?php echo $cur_checkout_time == (string)$i ? ' selected="selected"' : ''; ?>><?php echo ($i < 10 ? "0{$i}" : $i) . ':00'; ?></option>
									<?php
								}
								?>
								</select>
								<span class="vcm-param-setting-comment">Latest time the guest can check out.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Instant Book Category</div>
							<div class="vcm-param-setting">
								<?php
								$cur_ib_allow_cat = $booking_settings->get('instant_booking_allowed_category');
								?>
								<select name="listing[_bookingsettings][instant_booking_allowed_category]">
									<option value="off"<?php echo $cur_ib_allow_cat == 'off' ? ' selected="selected"' : ''; ?>>Off</option>
									<option value="everyone"<?php echo $cur_ib_allow_cat == 'everyone' ? ' selected="selected"' : ''; ?>>Everyone</option>
									<option value="well_reviewed_guests"<?php echo $cur_ib_allow_cat == 'well_reviewed_guests' ? ' selected="selected"' : ''; ?>>Well Reviewed Guests</option>
									<!-- <option value="guests_with_verified_identity"<?php echo $cur_ib_allow_cat == 'guests_with_verified_identity' ? ' selected="selected"' : ''; ?>>Guests with Verified Identity</option> -->
									<!-- <option value="well_reviewed_guests_with_verified_identity"<?php echo $cur_ib_allow_cat == 'well_reviewed_guests_with_verified_identity' ? ' selected="selected"' : ''; ?>>Well Reviewed Guests with Verified Identity</option> -->
								</select>
								<span class="vcm-param-setting-comment">Defines the category of guests that can make Instant Book reservations. Anyone who doesn't meet these requirements can still send a reservation request.</span>
							</div>
						</div>

						<?php
						$cur_canc_pol_settings = $booking_settings->get('cancellation_policy_settings');
						$cur_canc_pol_cat = is_object($cur_canc_pol_settings) && isset($cur_canc_pol_settings->cancellation_policy_category) ? $cur_canc_pol_settings->cancellation_policy_category : null;
						$cur_nonref_prfactor = is_object($cur_canc_pol_settings) && isset($cur_canc_pol_settings->non_refundable_price_factor) ? $cur_canc_pol_settings->non_refundable_price_factor : null;
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-label">Cancellation Policy Category</div>
							<div class="vcm-param-setting">
								<select name="listing[_bookingsettings][cancellation_policy_settings][cancellation_policy_category]">
									<option value=""></option>
									<option value="flexible"<?php echo $cur_canc_pol_cat == 'flexible' || $cur_canc_pol_cat == 'flexible_new' ? ' selected="selected"' : ''; ?>>Flexible</option>
									<option value="moderate"<?php echo $cur_canc_pol_cat == 'moderate' ? ' selected="selected"' : ''; ?>>Moderate</option>
									<option value="firm"<?php echo $cur_canc_pol_cat == 'firm' ? ' selected="selected"' : ''; ?>>Firm</option>
									<option value="strict_14_with_grace_period"<?php echo $cur_canc_pol_cat == 'strict_14_with_grace_period' ? ' selected="selected"' : ''; ?>>Strict: 14 days grace period</option>
									<option value="super_strict_30"<?php echo $cur_canc_pol_cat == 'super_strict_30' ? ' selected="selected"' : ''; ?>>Super Strict: 30 days</option>
									<option value="super_strict_60"<?php echo $cur_canc_pol_cat == 'super_strict_60' ? ' selected="selected"' : ''; ?>>Super Strict: 60 days</option>
								</select>
								<span class="vcm-param-setting-comment">Cancellation policy for the listing. This API interface can only provide some of Airbnb's cancellation policies.</span>
							</div>
						</div>

						<div class="vcm-param-container vcm-param-nested">
							<div class="vcm-param-label">Non-refundable Price Factor</div>
							<div class="vcm-param-setting">
								<input type="number" name="listing[_bookingsettings][cancellation_policy_settings][non_refundable_price_factor]" min="0" max="1" step="0.1" value="<?php echo $cur_nonref_prfactor; ?>" />
								<span class="vcm-param-setting-comment">If empty, the guests cannot select a tiered option. If specified (from 0 to 1), it is the multiplier for the price if the guest accepts the non-refundable cancellation policy (i.e. 0.8 = 20% discount). Non-refundable discounts may not work for Lux listings and listings located in Italy.</span>
							</div>
						</div>

						<?php
						$cur_guest_controls = $booking_settings->get('guest_controls');
						if (!is_object($cur_guest_controls)) {
							// we instantiate an empty JObject
							$guest_controls = new JObject;
						} else {
							$guest_controls = new JObject($cur_guest_controls);
						}
						?>
						<div class="vcm-param-container" data-related-group="guestcontrols">
							<div class="vcm-param-label">Children allowed</div>
							<div class="vcm-param-setting">
								<select name="listing[_bookingsettings][guest_controls][allows_children_as_host]">
									<option value="true"<?php echo $guest_controls->get('allows_children_as_host') === true ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMYES'); ?></option>
									<option value="false"<?php echo !$guest_controls->get('allows_children_as_host') ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMNO'); ?></option>
								</select>
								<span class="vcm-param-setting-comment">Is the listing suitable for children (2-12 years)?</span>
							</div>
						</div>

						<div class="vcm-param-container" data-related-group="guestcontrols">
							<div class="vcm-param-label">Infants allowed</div>
							<div class="vcm-param-setting">
								<select name="listing[_bookingsettings][guest_controls][allows_infants_as_host]">
									<option value="true"<?php echo $guest_controls->get('allows_infants_as_host') === true ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMYES'); ?></option>
									<option value="false"<?php echo !$guest_controls->get('allows_infants_as_host') ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMNO'); ?></option>
								</select>
								<span class="vcm-param-setting-comment">Is the listing suitable for infants (under 2 years)?</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Children disallowed details</div>
							<div class="vcm-param-setting">
								<input type="text" name="listing[_bookingsettings][guest_controls][children_not_allowed_details]" value="<?php echo $this->escape($guest_controls->get('children_not_allowed_details')); ?>" />
								<span class="vcm-param-setting-comment">If children or infants are not allowed, this field is required to provide details about why the place is not suitable for children.</span>
							</div>
						</div>

						<div class="vcm-param-container" data-related-group="guestcontrols">
							<div class="vcm-param-label">Smoking allowed</div>
							<div class="vcm-param-setting">
								<select name="listing[_bookingsettings][guest_controls][allows_smoking_as_host]">
									<option value="true"<?php echo $guest_controls->get('allows_smoking_as_host') === true ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMYES'); ?></option>
									<option value="false"<?php echo !$guest_controls->get('allows_smoking_as_host') ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMNO'); ?></option>
								</select>
								<span class="vcm-param-setting-comment">Is smoking allowed?</span>
							</div>
						</div>

						<div class="vcm-param-container" data-related-group="guestcontrols">
							<div class="vcm-param-label">Pets allowed</div>
							<div class="vcm-param-setting">
								<select name="listing[_bookingsettings][guest_controls][allows_pets_as_host]">
									<option value="true"<?php echo $guest_controls->get('allows_pets_as_host') === true ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMYES'); ?></option>
									<option value="false"<?php echo !$guest_controls->get('allows_pets_as_host') ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMNO'); ?></option>
								</select>
								<span class="vcm-param-setting-comment">Are pets allowed?</span>
							</div>
						</div>

						<div class="vcm-param-container" data-related-group="guestcontrols">
							<div class="vcm-param-label">Events allowed</div>
							<div class="vcm-param-setting">
								<select name="listing[_bookingsettings][guest_controls][allows_events_as_host]">
									<option value="true"<?php echo $guest_controls->get('allows_events_as_host') === true ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMYES'); ?></option>
									<option value="false"<?php echo !$guest_controls->get('allows_events_as_host') ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMNO'); ?></option>
								</select>
								<span class="vcm-param-setting-comment">Are parties or events allowed?</span>
							</div>
						</div>

						<?php
						$expectations = $booking_settings->get('listing_expectations_for_guests', array());
						// push fake bed object to use it for cloning
						$fake_expect = new stdClass;
						$fake_expect->is_fake = 1;
						array_push($expectations, $fake_expect);
						//
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-label">Expectations for guests</div>
							<div class="vcm-param-setting">
								<button type="button" class="btn vcm-config-btn" onclick="vcmAddListingExpectation();"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::_('VCMBCAHADD'); ?></button>
								<span class="vcm-param-setting-comment">Details guests must know about the home. Set expectations that the guests must agree to before booking.</span>
							</div>
						</div>

						<div class="vcm-listing-expectations-wrap">
						<?php
						foreach ($expectations as $k => $list_expectation) {
							// wrap expectation object into a JObject
							$expectation = new JObject($list_expectation);
							// check whether the expectation is "fake", hence a placeholder
							$is_fake_expectation = $expectation->get('is_fake', 0);
							// current expectation type
							$current_expectation_type = $expectation->get('type');
							// set input key for array values
							$inp_key = $is_fake_expectation ? '%d' : $k;
							?>
							<div class="<?php echo $is_fake_expectation ? 'vcm-listing-expectation-new' : 'vcm-listing-expectation'; ?>" style="<?php echo $is_fake_expectation ? 'display: none;' : ''; ?>">
								<div class="vcm-param-container vcm-param-nested">
									<div class="vcm-param-label">Expectation type</div>
									<div class="vcm-param-setting">
										<select name="listing[_bookingsettings][listing_expectations_for_guests][type][<?php echo $inp_key; ?>]">
											<option value=""></option>
											<option value="requires_stairs"<?php echo $current_expectation_type == 'requires_stairs' ? ' selected="selected"' : ''; ?>>Requires stairs</option>
											<option value="potential_noise"<?php echo $current_expectation_type == 'potential_noise' ? ' selected="selected"' : ''; ?>>Potential noise</option>
											<option value="has_pets"<?php echo $current_expectation_type == 'has_pets' ? ' selected="selected"' : ''; ?>>There are pets</option>
											<option value="limited_parking"<?php echo $current_expectation_type == 'limited_parking' ? ' selected="selected"' : ''; ?>>Limited parking</option>
											<option value="shared_spaces"<?php echo $current_expectation_type == 'shared_spaces' ? ' selected="selected"' : ''; ?>>Shared spaces</option>
											<option value="limited_amenities"<?php echo $current_expectation_type == 'limited_amenities' ? ' selected="selected"' : ''; ?>>Limited amenities</option>
											<option value="surveillance"<?php echo $current_expectation_type == 'surveillance' ? ' selected="selected"' : ''; ?>>Surveillance</option>
											<option value="weapons"<?php echo $current_expectation_type == 'weapons' ? ' selected="selected"' : ''; ?>>Weapons</option>
											<option value="animals"<?php echo $current_expectation_type == 'animals' ? ' selected="selected"' : ''; ?>>Animals</option>
										</select>
									</div>
								</div>
								<div class="vcm-param-container vcm-param-nested">
									<div class="vcm-param-label">Expectation details</div>
									<div class="vcm-param-setting">
										<input type="text" name="listing[_bookingsettings][listing_expectations_for_guests][added_details][<?php echo $inp_key; ?>]" value="<?php echo $this->escape($expectation->get('added_details')); ?>" />
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
			</fieldset>

			<fieldset class="adminform vcm-listings-listing-wrap" data-landto="avrules">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('calendar-day'); ?> <?php echo JText::_('VCM_MNGLISTING_AVRULES'); ?></legend>
					<div class="vcm-params-container">
					<?php
					if (!$is_editing) {
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-setting">
								<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_MNGLISTING_AVRULES_ONLYEDIT'); ?></span>
							</div>
						</div>
						<?php
					} else {
						// display current availability rules
						$av_rules = $listing->get('_availabilityrules');
						if (!is_object($av_rules)) {
							// we instantiate an empty JObject
							$av_rules = new JObject;
						} else {
							$av_rules = new JObject($av_rules);
						}
						?>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Default Min Nights</div>
							<div class="vcm-param-setting">
								<input type="number" name="listing[_availabilityrules][default_min_nights]" min="0" value="<?php echo $av_rules->get('default_min_nights'); ?>" />
								<span class="vcm-param-setting-comment">The default minimum night requirement for reservations. Set 0 to reset to default value.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Default Max Nights</div>
							<div class="vcm-param-setting">
								<input type="number" name="listing[_availabilityrules][default_max_nights]" min="0" value="<?php echo $av_rules->get('default_max_nights'); ?>" />
								<span class="vcm-param-setting-comment">The default maximum night requirement for reservations. Set 0 to reset to default value.</span>
							</div>
						</div>

						<div class="vcm-param-container vcm-param-nested">
							<div class="vcm-param-label">Request to Book above max nights</div>
							<div class="vcm-param-setting">
								<select name="listing[_availabilityrules][allow_rtb_above_max_nights]">
									<option value="true"<?php echo $av_rules->get('allow_rtb_above_max_nights') === true ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMYES'); ?></option>
									<option value="false"<?php echo $av_rules->get('allow_rtb_above_max_nights') !== true ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMNO'); ?></option>
								</select>
								<span class="vcm-param-setting-comment">If enabled, an attempt to book a stay longer than allowed by the default max nights will be allowed, but will trigger the Request to Book (RTB) flow instead of Instant Book (IB).</span>
							</div>
						</div>

						<?php
						$lead_time = $av_rules->get('booking_lead_time');
						if (!is_object($lead_time)) {
							$lead_time = new JObject;
						} else {
							$lead_time = new JObject($lead_time);
						}
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-label">Minimum notice hours</div>
							<div class="vcm-param-setting">
								<input type="number" name="listing[_availabilityrules][booking_lead_time][hours]" min="0" max="168" value="<?php echo $lead_time->get('hours'); ?>" />
								<span class="vcm-param-setting-comment">The number of hours required for minimum notice before booking. Valid values are 0-24, 48, 72, and 168 (one week).</span>
							</div>
						</div>
						<div class="vcm-param-container vcm-param-nested">
							<div class="vcm-param-label">Allow Request to Book</div>
							<div class="vcm-param-setting">
								<select name="listing[_availabilityrules][booking_lead_time][allow_request_to_book]">
									<option value="1"<?php echo $lead_time->get('allow_request_to_book', 0) > 0 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMYES'); ?></option>
									<option value="0"<?php echo $lead_time->get('allow_request_to_book', 0) < 1 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMNO'); ?></option>
								</select>
								<span class="vcm-param-setting-comment">Bookings that do not meet the minimum notice requirement can become requests to book instead.</span>
							</div>
						</div>

						<?php
						$max_notice = $av_rules->get('max_days_notice');
						if (!is_object($max_notice)) {
							$max_notice = new JObject;
						} else {
							$max_notice = new JObject($max_notice);
						}
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-label">Maximum days in advance</div>
							<div class="vcm-param-setting">
								<input type="number" name="listing[_availabilityrules][max_days_notice][days]" min="-1" max="365" value="<?php echo $max_notice->get('days'); ?>" />
								<span class="vcm-param-setting-comment">The maximum number of days between the booking date and the check in date. Valid values are -1, 0, 30, 60, 90, 120, 150, 180, 210, 240, 270, 300, 330, 365. Pass 0 to disable ALL future days, or -1 to have no limits.</span>
							</div>
						</div>

						<?php
						$turnover_days = $av_rules->get('turnover_days');
						if (!is_object($turnover_days)) {
							$turnover_days = new JObject;
						} else {
							$turnover_days = new JObject($turnover_days);
						}
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-label">Turnover days</div>
							<div class="vcm-param-setting">
								<input type="number" name="listing[_availabilityrules][turnover_days][days]" min="0" max="14" value="<?php echo $turnover_days->get('days'); ?>" />
								<span class="vcm-param-setting-comment">Days to block before and after each reservation. For example, if you need one full day after a check-out before a new check-in, set this to 1.</span>
							</div>
						</div>

						<?php
					}
					?>
					</div>
				</div>
			</fieldset>

			<fieldset class="adminform vcm-listings-listing-wrap" data-landto="pricesettings">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('money-bill'); ?> <?php echo JText::_('VCM_MNGLISTING_PRSETTINGS'); ?></legend>
					<div class="vcm-params-container">
					<?php
					if (!$is_editing) {
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-setting">
								<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_MNGLISTING_PRSETTINGS_ONLYEDIT'); ?></span>
							</div>
						</div>
						<?php
					} else {
						// display current pricing settings
						$pricing_settings = $listing->get('_pricingsettings');
						if (!is_object($pricing_settings)) {
							// we instantiate an empty JObject
							$pricing_settings = new JObject;
						} else {
							$pricing_settings = new JObject($pricing_settings);
						}
						?>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Currency</div>
							<div class="vcm-param-setting">
								<input type="text" name="listing[_pricingsettings][listing_currency]" value="<?php echo $this->escape($pricing_settings->get('listing_currency')); ?>" placeholder="i.e. EUR, USD, GBP, AUD, CAD..." />
								<span class="vcm-param-setting-comment">Currency used for setting the listing price, in the ISO 4217 (3-char) format.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Default price per night</div>
							<div class="vcm-param-setting">
								<input type="number" name="listing[_pricingsettings][default_daily_price]" min="0" value="<?php echo $pricing_settings->get('default_daily_price'); ?>" />
								<span class="vcm-param-setting-comment">The default daily price for the listing. Can change on various dates of the year by launching the Bulk Actions.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Default weekend price</div>
							<div class="vcm-param-setting">
								<input type="number" name="listing[_pricingsettings][weekend_price]" min="0" value="<?php echo $pricing_settings->get('weekend_price'); ?>" />
								<span class="vcm-param-setting-comment">The default price to apply for the weekend days. Can change on various dates of the year by launching the Bulk Actions.</span>
							</div>
						</div>

						<div class="vcm-params-block">

							<div class="vcm-param-container">
								<div class="vcm-param-setting">
									<span class="vcm-param-setting-comment"><?php VikBookingIcons::e('exclamation-triangle'); ?> <?php echo JText::_('VCM_AIRBNB_PRCSETTINGS_HELP'); ?> - <a href="index.php?option=com_vikchannelmanager&task=config" target="_blank">Airbnb - <?php echo JText::_('VCMMENUSETTINGS'); ?></a></span>
								</div>
							</div>

							<div class="vcm-param-container">
								<div class="vcm-param-label"><?php echo JText::_('VCM_AIRBNB_SECDEP'); ?></div>
								<div class="vcm-param-setting">
									<input type="number" name="listing[_pricingsettings][security_deposit]" min="0" value="<?php echo $pricing_settings->get('security_deposit'); ?>" />
									<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_AIRBNB_SECDEP_HELP'); ?></span>
								</div>
							</div>

							<div class="vcm-param-container">
								<div class="vcm-param-label"><?php echo JText::_('VCM_AIRBNB_CLEANFEE'); ?></div>
								<div class="vcm-param-setting">
									<input type="number" name="listing[_pricingsettings][cleaning_fee]" min="0" value="<?php echo $pricing_settings->get('cleaning_fee'); ?>" />
									<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_AIRBNB_CLEANFEE_HELP'); ?></span>
								</div>
							</div>

						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Guests included</div>
							<div class="vcm-param-setting">
								<input type="number" name="listing[_pricingsettings][guests_included]" min="1" step="1" value="<?php echo $pricing_settings->get('guests_included'); ?>" />
								<span class="vcm-param-setting-comment">Number of guests permitted without any additional fees. Calculated automatically by launching the Bulk Actions if prices per occupancy have been defined.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Price per extra person</div>
							<div class="vcm-param-setting">
								<input type="number" name="listing[_pricingsettings][price_per_extra_person]" min="0" value="<?php echo $pricing_settings->get('price_per_extra_person'); ?>" />
								<span class="vcm-param-setting-comment">Amount added to the listing's nightly price for each guest beyond the number specified in Guests included. To remove the price per extra person, set it to 0. Calculated automatically by launching the Bulk Actions if prices per occupancy have been defined.</span>
							</div>
						</div>

						<?php
						/**
						 * Added support for the listing's basic discounts (default pricing rules).
						 * We use them to support basic discounts related to Early Bird and Last Minute.
						 * The Default Pricing Rules are accepted for both Standard and LOS PnA models.
						 * 
						 * Airbnb API version 2023.06.30 moved the weekly and monthly discount factors
						 * onto the default pricing rules, so there is no longer a dedicated pricing setting.
						 * 
						 * @since 	1.8.23
						 * @since 	1.8.28  added support for weekly/monthly discount factors moved to "default_pricing_rules".
						 */
						$default_pricing_rules = $pricing_settings->get('default_pricing_rules', []);
						$early_bird_discount   = null;
						$last_minute_discount  = null;
						$weekly_def_discount   = null;
						$monthly_def_discount  = null;
						if (is_array($default_pricing_rules) && $default_pricing_rules) {
							foreach ($default_pricing_rules as $default_pricing_rule) {
								if (!is_object($default_pricing_rule) || !isset($default_pricing_rule->rule_type) || !isset($default_pricing_rule->price_change) || !isset($default_pricing_rule->threshold_one)) {
									continue;
								}
								if ($default_pricing_rule->rule_type == 'BOOKED_BEYOND_AT_LEAST_X_DAYS') {
									// early bird discount
									$early_bird_discount = $default_pricing_rule;
									continue;
								}
								if ($default_pricing_rule->rule_type == 'BOOKED_WITHIN_AT_MOST_X_DAYS') {
									// last minute discount
									$last_minute_discount = $default_pricing_rule;
									continue;
								}
								if ($default_pricing_rule->rule_type == 'STAYED_AT_LEAST_X_DAYS') {
									if ((int) $default_pricing_rule->threshold_one === 7) {
										// weekly discount (price) factor
										$weekly_def_discount = $default_pricing_rule;
										continue;
									}
									if ((int) $default_pricing_rule->threshold_one === 28) {
										// monthly discount (price) factor
										$monthly_def_discount = $default_pricing_rule;
										continue;
									}
								}
							}
						}

						/**
						 * Adjust weekly and monthly price factors to comply with the most recent API version.
						 * 
						 * @since 	1.8.28
						 */
						$weekly_price_factor  = $pricing_settings->get('weekly_price_factor');
						$monthly_price_factor = $pricing_settings->get('monthly_price_factor');
						if ($weekly_price_factor) {
							// must be a negative float value, so turn for example "0.7" into "-30"
							$weekly_price_factor = (1 - $weekly_price_factor) * 100;
							$weekly_price_factor = $weekly_price_factor - ($weekly_price_factor * 2);
						}
						if ($monthly_price_factor) {
							// must be a negative float value, so turn for example "0.7" into "-30"
							$monthly_price_factor = (1 - $monthly_price_factor) * 100;
							$monthly_price_factor = $monthly_price_factor - ($monthly_price_factor * 2);
						}
						if ($weekly_def_discount) {
							// new value in default pricing rules
							$weekly_price_factor = $weekly_def_discount->price_change;
						}
						if ($monthly_def_discount) {
							// new value in default pricing rules
							$monthly_price_factor = $monthly_def_discount->price_change;
						}
						?>

						<div class="vcm-params-block">
							<div class="vcm-param-container" data-related-group="default_pricing_rules">
								<div class="vcm-param-label">Weekly discount-factor %</div>
								<div class="vcm-param-setting">
									<input type="number" name="listing[_pricingsettings][weekly_price_factor]" min="-99" max="0" step="any" value="<?php echo $weekly_price_factor; ?>" />
									<span class="vcm-param-setting-comment">Defines a discount for stays equal to or longer than 7 nights. Must be a negative percent discount (i.e. -10 = 10% discount). To remove it, set it to 0.</span>
								</div>
							</div>
						</div>

						<div class="vcm-params-block">
							<div class="vcm-param-container" data-related-group="default_pricing_rules">
								<div class="vcm-param-label">Monthly discount-factor %</div>
								<div class="vcm-param-setting">
									<input type="number" name="listing[_pricingsettings][monthly_price_factor]" min="-99" max="0" step="any" value="<?php echo $monthly_price_factor; ?>" />
									<span class="vcm-param-setting-comment">Defines a discount for stays equal to or longer than 28 nights. Must be a negative percent discount (i.e. -20 = 20% discount). To remove it, set it to 0.</span>
								</div>
							</div>
						</div>

						<div class="vcm-params-block">
							<div class="vcm-param-container" data-related-group="default_pricing_rules">
								<div class="vcm-param-label">Early Bird (Booking ahead) Discount %</div>
								<div class="vcm-param-setting">
									<input type="number" name="listing[_pricingsettings][base_earlybird_discount_amount]" min="-99" max="0" step="1" value="<?php echo $early_bird_discount && !empty($early_bird_discount->price_change) ? (int)$early_bird_discount->price_change : ''; ?>" />
									<span class="vcm-param-setting-comment">Defines a basic early bird discount for those who book N days ahead. Must be a negative value that indicates the percent discount (i.e. -15 = 15% discount).</span>
								</div>
							</div>
							<div class="vcm-param-container vcm-param-nested" data-related-group="default_pricing_rules">
								<div class="vcm-param-label">Early Bird (Booking ahead) Days in advance</div>
								<div class="vcm-param-setting">
									<input type="number" name="listing[_pricingsettings][base_earlybird_discount_days]" min="28" max="900" step="1" value="<?php echo $early_bird_discount && !empty($early_bird_discount->threshold_one) ? (int)$early_bird_discount->threshold_one : ''; ?>" />
									<span class="vcm-param-setting-comment">Defines a basic early bird discount for those who book N days ahead. Must be a multiple of 28 or 30.</span>
								</div>
							</div>
						</div>

						<div class="vcm-params-block">
							<div class="vcm-param-container" data-related-group="default_pricing_rules">
								<div class="vcm-param-label">Last Minute Discount %</div>
								<div class="vcm-param-setting">
									<input type="number" name="listing[_pricingsettings][base_lastminute_discount_amount]" min="-99" max="0" step="1" value="<?php echo $last_minute_discount && !empty($last_minute_discount->price_change) ? (int)$last_minute_discount->price_change : ''; ?>" />
									<span class="vcm-param-setting-comment">Defines a basic last minute discount for those who book within at most N days. Must be a negative value that indicates the percent discount (i.e. -15 = 15% discount).</span>
								</div>
							</div>
							<div class="vcm-param-container vcm-param-nested" data-related-group="default_pricing_rules">
								<div class="vcm-param-label">Last Minute Days in advance</div>
								<div class="vcm-param-setting">
									<input type="number" name="listing[_pricingsettings][base_lastminute_discount_days]" min="0" max="28" step="1" value="<?php echo $last_minute_discount && !empty($last_minute_discount->threshold_one) ? (int)$last_minute_discount->threshold_one : ''; ?>" />
									<span class="vcm-param-setting-comment">Defines a basic last minute discount for those who book within at most N days. Must be equal to or less than 28.</span>
								</div>
							</div>
						</div>

						<div class="vcm-params-block">
							<div class="vcm-param-container">
								<div class="vcm-param-label">New Listing Promotion</div>
								<div class="vcm-param-setting">
									<div class="vcm-airbnb-newlistpromo-wrap">
										<button type="button" class="btn vcm-config-btn vcm-airbnb-newlistpromo-check" onclick="vcmActionNewListingPromo('check');"><?php VikBookingIcons::e('sync'); ?> <?php echo JText::_('VCM_CHECK_STATUS'); ?></button>
										<button type="button" class="btn btn-success vcm-airbnb-newlistpromo-enable" onclick="vcmActionNewListingPromo('enable');" style="display: none;"><?php VikBookingIcons::e('play-circle'); ?> <?php echo JText::_('VCMENABLEOPP'); ?></button>
										<button type="button" class="btn btn-warning vcm-airbnb-newlistpromo-disable" onclick="vcmActionNewListingPromo('disable');" style="display: none;"><?php VikBookingIcons::e('stop-circle'); ?> <?php echo JText::_('VCMBCAHDELETE'); ?></button>
									</div>
									<span class="vcm-param-setting-comment">Check the current status for the &quot;New Listing&quot; promotion. The promotion applies a 20% discount to the new listing's first three bookings only.</span>
								</div>
							</div>
							<div class="vcm-param-container vcm-param-nested">
								<div class="vcm-param-label"><?php echo JText::_('VCMPROMSTATUS'); ?></div>
								<div class="vcm-param-setting">
									<label class="vcm-airbnb-newlistpromo-lbl label label-info"><?php echo JText::_('VCMBCAHJOB17'); ?></label>
								</div>
							</div>
						</div>

						<?php
						/**
						 * Display the information about the host/guest service fees (read-only).
						 * The Price Settings API now returns the Guest and Host Airbnb Service
						 * Fee split for a specific listing. The Airbnb Service Fee is set on the
						 * Host account level, so all listings within an account should have the
						 * same fee structure (Host-only or split-fee).
						 * 
						 * @since 	1.8.28
						 */
						$host_fee_percent  = $pricing_settings->get('host_fee_percent', null);
						$guest_fee_percent = $pricing_settings->get('guest_fee_percent', null);
						if (!is_null($host_fee_percent) || !is_null($guest_fee_percent)) {
							$host_fee_percent  = $host_fee_percent ? ($host_fee_percent * 100) : 0;
							$guest_fee_percent = $guest_fee_percent ? ($guest_fee_percent * 100) : 0;
							?>
						<div class="vcm-params-block">
							<div class="vcm-param-container">
								<div class="vcm-param-label">Host Service Fee (%)</div>
								<div class="vcm-param-setting">
									<span class="label<?php echo $host_fee_percent ? ' label-info' : ''; ?>"><?php echo (float) $host_fee_percent . ' %'; ?></span>
									<span class="vcm-param-setting-comment">The Airbnb Service Fee for the Host is set on the Host account level.</span>
								</div>
							</div>
							<div class="vcm-param-container">
								<div class="vcm-param-label">Guest Service Fee (%)</div>
								<div class="vcm-param-setting">
									<span class="label<?php echo $guest_fee_percent ? ' label-info' : ''; ?>"><?php echo (float) $guest_fee_percent . ' %'; ?></span>
									<span class="vcm-param-setting-comment">The Airbnb Service Fee for the Guest (in case of split-fee) is set on the Host account level.</span>
								</div>
							</div>
						</div>
							<?php
						}
						?>

						<script type="text/javascript">

							function vcmActionNewListingPromo(action) {
								// display loading overlay
								vcmShowLoading();

								// make the ajax request to the controller
								VBOCore.doAjax(
									"<?php echo VikChannelManager::ajaxUrl('index.php?option=com_vikchannelmanager&task=airbnblst.new_listing_promotion'); ?>",
									{
										action_type: action || 'check',
										listing_id: '<?php echo $listing->get('id'); ?>',
									},
									(resp) => {
										// hide loading overlay
										vcmStopLoading();

										// parse the response
										if (!resp || !resp.hasOwnProperty('promoStatusCode')) {
											// invalid response
											alert("Invalid response.");
											return;
										}

										// update promotion status code
										jQuery('.vcm-airbnb-newlistpromo-lbl').text(resp['promoStatusCode']);

										if (resp['promoStatusCode'] == 'AVAILABLE') {
											// promotion can be created
											jQuery('.vcm-airbnb-newlistpromo-enable').show();
											jQuery('.vcm-airbnb-newlistpromo-disable').hide();
										} else if (resp['promoStatusCode'] == 'ONGOING') {
											// promotion can be deleted
											jQuery('.vcm-airbnb-newlistpromo-disable').show();
											jQuery('.vcm-airbnb-newlistpromo-enable').hide();
										} else {
											// promotion is probably expired
											jQuery('.vcm-airbnb-newlistpromo-enable').hide();
											jQuery('.vcm-airbnb-newlistpromo-disable').hide();
										}
									},
									(err) => {
										alert(err.responseText);
										vcmStopLoading();
										if (err.status && err.status == 406) {
											// HTTP status code in response (Not Acceptable) indicates listing is not eligible
											jQuery('.vcm-airbnb-newlistpromo-wrap').html('<p class="info">' + err.responseText + '</p>');
											jQuery('.vcm-airbnb-newlistpromo-lbl').text('Not eligible');
										}
									}
								);
							}

						</script>

						<?php
					}
					?>
					</div>
				</div>
			</fieldset>

			<fieldset class="adminform vcm-listings-listing-wrap" data-landto="locdescr">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('language'); ?> <?php echo JText::_('VCM_MNGLISTING_LOCDESCRS'); ?></legend>
					<div class="vcm-params-container">
					<?php
					if (!$is_editing) {
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-setting">
								<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_MNGLISTING_LOCDESCRS_ONLYEDIT'); ?></span>
							</div>
						</div>
						<?php
					} else {
						// display current descriptions, if any
						$current_descriptions = $listing->get('_descriptions', array());
						$current_descriptions = !is_array($current_descriptions) ? array() : $current_descriptions;
						// push fake description object to use it for cloning
						$fake_descr = new stdClass;
						$fake_descr->is_fake = 1;
						array_push($current_descriptions, $fake_descr);
						//
						?>
						<div class="vcm-listings-descriptions-wrap">
						<?php
						foreach ($current_descriptions as $k => $list_descr) {
							// wrap listing description object into a JObject
							$descr = new JObject($list_descr);
							// check whether the description is "fake", hence a placeholder
							$is_fake = $descr->get('is_fake', 0);
							// set input key for array values
							$inp_key = $is_fake ? '%d' : $k;
							?>
							<div class="<?php echo $is_fake ? 'vcm-listings-listing-description-new' : 'vcm-listings-listing-description'; ?>" style="<?php echo $is_fake ? 'display: none;' : ''; ?>">
								
								<div class="vcm-param-container">
									<div class="vcm-param-label"><?php echo JText::_('VCMBCAHLANGUAGE'); ?></div>
									<div class="vcm-param-setting">
									<?php
									if ($is_fake) {
										// placeholder to add a new description
										?>
										<select name="listing[_descriptions][locale][<?php echo $inp_key; ?>]">
											<option value=""></option>
											<option value="id">Bahasa Indonesia</option>
											<option value="ms">Bahasa Melayu</option>
											<option value="ca">Català</option>
											<option value="da">Dansk</option>
											<option value="de">Deutsch</option>
											<option value="en">English</option>
											<option value="en-GB">English (UK)</option>
											<option value="en-US">English (United States)</option>
											<option value="es">Español</option>
											<option value="el">Eλληνικά</option>
											<option value="fr">Français</option>
											<option value="hr">Hrvatski</option>
											<option value="it">Italiano</option>
											<option value="hu">Magyar</option>
											<option value="nl">Nederlands</option>
											<option value="no">Norsk</option>
											<option value="pl">Polski</option>
											<option value="pt">Português</option>
											<option value="fi">Suomi</option>
											<option value="sv">Svenska</option>
											<option value="tl">Tagalog</option>
											<option value="is">Íslenska</option>
											<option value="cs">Čeština</option>
											<option value="ru">Русский</option>
											<option value="he">עברית</option>
											<option value="th">ภาษาไทย</option>
											<option value="zh">中文</option>
											<option value="zh-TW">中文 (繁體)</option>
											<option value="ja">日本語</option>
											<option value="ko">한국어</option>
										</select>
										<?php
									} else {
										// existing description
										?>
										<input type="hidden" name="listing[_descriptions][locale][<?php echo $inp_key; ?>]" value="<?php echo $descr->get('locale'); ?>" />
										<strong><?php echo strtoupper($descr->get('locale')); ?></strong>
										<?php
									}
									if ($descr->get('machine_translated')) {
										?>
										<span class="vcm-param-setting-comment"><?php VikBookingIcons::e('robot'); ?> Machine Translation</span>
										<?php
									}
									?>
									</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label"><?php echo JText::_('VCMROOMSRELATIONSNAME'); ?></div>
									<div class="vcm-param-setting">
										<textarea maxlength="50" minlength="8" rows="4" cols="50" name="listing[_descriptions][name][<?php echo $inp_key; ?>]"><?php echo $this->escape($descr->get('name')); ?></textarea>
										<span class="vcm-param-setting-comment">Listing name. 50 character maximum; 8 characters minimum.</span>
									</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label with-buttons">
										<span class="vcm-param-label-main">Summary</span>
										<div class="vcm-genai-btn-wrap">
											<button type="button" class="btn btn-small vcm-content-genai vcm-tooltip vcm-tooltip-top" data-tooltiptext="<?php echo $this->escape(JText::_('VCM_GEN_CONTENT')); ?>" data-descr-type="summary"><?php echo JText::_('VCM_AI_CHAT_TOOLTIP'); ?></button>
										</div>
									</div>
									<div class="vcm-param-setting">
										<textarea maxlength="500" rows="4" cols="50" name="listing[_descriptions][summary][<?php echo $inp_key; ?>]"><?php echo $this->escape($descr->get('summary')); ?></textarea>
										<span class="vcm-param-setting-comment">Should cover the major features of the space and neighborhood in 500 characters or less.</span>
									</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label with-buttons">
										<span class="vcm-param-label-main">Space</span>
										<div class="vcm-genai-btn-wrap">
											<button type="button" class="btn btn-small vcm-content-genai vcm-tooltip vcm-tooltip-top" data-tooltiptext="<?php echo $this->escape(JText::_('VCM_GEN_CONTENT')); ?>" data-descr-type="space"><?php echo JText::_('VCM_AI_CHAT_TOOLTIP'); ?></button>
										</div>
									</div>
									<div class="vcm-param-setting">
										<textarea rows="4" cols="50" name="listing[_descriptions][space][<?php echo $inp_key; ?>]"><?php echo $this->escape($descr->get('space')); ?></textarea>
										<span class="vcm-param-setting-comment">What makes it unique, and how many people does it comfortably fit.</span>
									</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label with-buttons">
										<span class="vcm-param-label-main">Access</span>
										<div class="vcm-genai-btn-wrap">
											<button type="button" class="btn btn-small vcm-content-genai vcm-tooltip vcm-tooltip-top" data-tooltiptext="<?php echo $this->escape(JText::_('VCM_GEN_CONTENT')); ?>" data-descr-type="access"><?php echo JText::_('VCM_AI_CHAT_TOOLTIP'); ?></button>
										</div>
									</div>
									<div class="vcm-param-setting">
										<textarea rows="4" cols="50" name="listing[_descriptions][access][<?php echo $inp_key; ?>]"><?php echo $this->escape($descr->get('access')); ?></textarea>
										<span class="vcm-param-setting-comment">Information about what parts of the space the guests will be able to access.</span>
									</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label with-buttons">
										<span class="vcm-param-label-main">Host interaction</span>
										<div class="vcm-genai-btn-wrap">
											<button type="button" class="btn btn-small vcm-content-genai vcm-tooltip vcm-tooltip-top" data-tooltiptext="<?php echo $this->escape(JText::_('VCM_GEN_CONTENT')); ?>" data-descr-type="host interaction"><?php echo JText::_('VCM_AI_CHAT_TOOLTIP'); ?></button>
										</div>
									</div>
									<div class="vcm-param-setting">
										<textarea rows="4" cols="50" name="listing[_descriptions][interaction][<?php echo $inp_key; ?>]"><?php echo $this->escape($descr->get('interaction')); ?></textarea>
										<span class="vcm-param-setting-comment">How much the Host will interact with the guests, and if the Host will be present during the guest stay.</span>
									</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label with-buttons">
										<span class="vcm-param-label-main">Neighborhood Overview</span>
										<div class="vcm-genai-btn-wrap">
											<button type="button" class="btn btn-small vcm-content-genai vcm-tooltip vcm-tooltip-top" data-tooltiptext="<?php echo $this->escape(JText::_('VCM_GEN_CONTENT')); ?>" data-descr-type="neighborhood overview"><?php echo JText::_('VCM_AI_CHAT_TOOLTIP'); ?></button>
										</div>
									</div>
									<div class="vcm-param-setting">
										<textarea rows="4" cols="50" name="listing[_descriptions][neighborhood_overview][<?php echo $inp_key; ?>]"><?php echo $this->escape($descr->get('neighborhood_overview')); ?></textarea>
										<span class="vcm-param-setting-comment">Information about the neighborhood and surrounding region. Suggestions about what guests should experience &amp; do.</span>
									</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label with-buttons">
										<span class="vcm-param-label-main">Transit</span>
										<div class="vcm-genai-btn-wrap">
											<button type="button" class="btn btn-small vcm-content-genai vcm-tooltip vcm-tooltip-top" data-tooltiptext="<?php echo $this->escape(JText::_('VCM_GEN_CONTENT')); ?>" data-descr-type="transit"><?php echo JText::_('VCM_AI_CHAT_TOOLTIP'); ?></button>
										</div>
									</div>
									<div class="vcm-param-setting">
										<textarea rows="4" cols="50" name="listing[_descriptions][transit][<?php echo $inp_key; ?>]"><?php echo $this->escape($descr->get('transit')); ?></textarea>
										<span class="vcm-param-setting-comment">Information on getting to the property. Is there convenient public transit? Is parking included with the listing or nearby? How does the guest get to the listing from the airport?</span>
									</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label with-buttons">
										<span class="vcm-param-label-main">Notes</span>
										<div class="vcm-genai-btn-wrap">
											<button type="button" class="btn btn-small vcm-content-genai vcm-tooltip vcm-tooltip-top" data-tooltiptext="<?php echo $this->escape(JText::_('VCM_GEN_CONTENT')); ?>" data-descr-type="notes"><?php echo JText::_('VCM_AI_CHAT_TOOLTIP'); ?></button>
										</div>
									</div>
									<div class="vcm-param-setting">
										<textarea rows="4" cols="50" name="listing[_descriptions][notes][<?php echo $inp_key; ?>]"><?php echo $this->escape($descr->get('notes')); ?></textarea>
										<span class="vcm-param-setting-comment">Any additional details for the guest to know.</span>
									</div>
								</div>

								<div class="vcm-param-container">
									<div class="vcm-param-label with-buttons">
										<span class="vcm-param-label-main">House Rules</span>
										<div class="vcm-genai-btn-wrap">
											<button type="button" class="btn btn-small vcm-content-genai vcm-tooltip vcm-tooltip-top" data-tooltiptext="<?php echo $this->escape(JText::_('VCM_GEN_CONTENT')); ?>" data-descr-type="house rules"><?php echo JText::_('VCM_AI_CHAT_TOOLTIP'); ?></button>
										</div>
									</div>
									<div class="vcm-param-setting">
										<textarea rows="4" cols="50" name="listing[_descriptions][house_rules][<?php echo $inp_key; ?>]"><?php echo $this->escape($descr->get('house_rules')); ?></textarea>
										<span class="vcm-param-setting-comment">Instructions for guests on how to behave. Should also include whether pets are allowed and if there are rules about smoking.</span>
									</div>
								</div>
							
							<?php
							if (!$is_fake) {
								?>
								<div class="vcm-param-container">
									<div class="vcm-param-label">&nbsp;</div>
									<div class="vcm-param-setting">
										<a class="btn btn-danger" href="index.php?option=com_vikchannelmanager&task=airbnblst.delete_description&descr_locale=<?php echo $descr->get('locale'); ?>&listing_id=<?php echo $this->listing->id; ?>" onclick="return vcmConfirmDelete();"><?php VikBookingIcons::e('trash'); ?> <?php echo JText::_('VCMBCAHDELETE') . ' (' . strtoupper($descr->get('locale')) . ')'; ?></a>
									</div>
								</div>
								<?php
							} else {
								?>
								<div class="vcm-param-container">
									<div class="vcm-param-label">&nbsp;</div>
									<div class="vcm-param-setting">
										<button type="button" class="btn btn-danger" onclick="vcmRemoveNewDescription(this);"><?php VikBookingIcons::e('times-circle'); ?></button>
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
						
						<div class="vcm-listings-listing-description-add">
							<div class="vcm-param-container">
								<div class="vcm-param-label">
									<button type="button" class="btn vcm-config-btn" onclick="vcmAddLocaleDescription();"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::_('VCMBCAHADD'); ?></button>
								</div>
							</div>
						</div>
						<?php
					}
					?>
					</div>
				</div>
			</fieldset>

			<fieldset class="adminform vcm-listings-listing-wrap" data-landto="quality">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('thumbs-up'); ?> <?php echo JText::_('VCM_MNGLISTING_QUALITY'); ?></legend>
					<div class="vcm-params-container">
					<?php
					if (!$is_editing) {
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-setting">
								<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_NO_RESULTS'); ?></span>
							</div>
						</div>
						<?php
					} else {
						/**
						 * Display current quality data, if any.
						 * 
						 * @since 	1.9.0
						 */
						$quality_standards  = (array) $listing->get('quality_standards');
						$reservation_issues = (array) $listing->get('reservation_issues');

						if ($quality_standards['state'] ?? null) {
							// listing quality status
							$quality_status_cls = '';
							$quality_status_descr = $quality_standards['state'];
							switch (strtolower($quality_standards['state'])) {
								case 'good':
									$quality_status_cls = ' label-success';
									$quality_status_descr = 'No quality issue reported.';
									break;
								case 'educate':
									$quality_status_cls = ' label-warning';
									$quality_status_descr = 'A quality issue has been reported. Host should review guidelines and educational materials.';
									break;
								case 'warn':
									$quality_status_cls = ' label-warning';
									$quality_status_descr = 'A few quality issues have been reported. Host should review our guidelines again.';
									break;
								case 'probation':
									$quality_status_cls = ' label-warning';
									$quality_status_descr = 'A few quality issues have been reported. Listing is at risk of suspension soon. Host should review our guidelines again.';
									break;
								case 'additional_warn':
									$quality_status_cls = ' label-warning';
									$quality_status_descr = 'Too many quality issues have been reported. The listing is at risk for removal.';
									break;
								case 'pending_removal':
									$quality_status_cls = ' label-danger';
									$quality_status_descr = 'Too many quality issues have been reported. The listing is marked for removal after 30 days.';
									break;
								case 'suspended':
									$quality_status_cls = ' label-danger';
									$quality_status_descr = 'Too many quality issues have been reported. The listing has been suspended until Hosts reviews Airbnb guidelines again.';
									break;
								case 'removed':
									$quality_status_cls = ' label-danger';
									$quality_status_descr = 'Too many quality issues have been reported. The listing has been removed from Airbnb.';
									break;
								default:
									break;
							}
							?>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VBSTATUS'); ?></div>
							<div class="vcm-param-setting">
								<span class="label<?php echo $quality_status_cls; ?>"><?php echo strtoupper($quality_standards['state']); ?></span>
								<span class="vcm-param-setting-comment"><?php echo $quality_status_descr; ?></span>
							</div>
						</div>
							<?php
						}

						if ($quality_standards['metadata'] ?? null) {
							// quality standards metadata
							$quality_standards_metadata = (array) $quality_standards['metadata'];
							?>
						<div class="vcm-params-block">
							<?php
							foreach ($quality_standards_metadata as $qmeta_key => $qmeta_val) {
								if (!is_scalar($qmeta_val)) {
									continue;
								}
								?>
							<div class="vcm-param-container">
								<div class="vcm-param-label"><?php echo ucwords(str_replace('_', ' ', $qmeta_key)); ?></div>
								<div class="vcm-param-setting">
									<span class="label"><?php echo is_bool($qmeta_val) ? intval($qmeta_val) : $qmeta_val; ?></span>
								</div>
							</div>
								<?php
							}
							?>
						</div>
							<?php
						}

						if ($reservation_issues) {
							// latest reservation issues
							foreach ($reservation_issues as $res_issue) {
								if (!is_object($res_issue)) {
									continue;
								}
								$confirmation_code = $res_issue->confirmation_code ?? '0';
								$negative_review_id = $res_issue->review_issues->review_id ?? null;
								$cs_violations = (array) ($res_issue->cs_violations ?? null);
								?>
						<div class="vcm-params-block">
							<div class="vcm-param-container">
								<div class="vcm-param-label"><strong>Reservation Issue</strong></div>
								<div class="vcm-param-setting">
									<button type="button" class="btn btn-small" onclick="VBOCore.handleDisplayWidgetNotification({widget_id: 'booking_details'}, {bid: '<?php echo $confirmation_code; ?>'});"><?php echo $confirmation_code; ?></button>
								</div>
							</div>
						<?php
						if ($negative_review_id) {
							?>
							<div class="vcm-param-container">
								<div class="vcm-param-label">Negative Review</div>
								<div class="vcm-param-setting">
									<button type="button" class="btn btn-small" onclick="VBOCore.handleDisplayWidgetNotification({widget_id: 'guest_reviews'}, {ota_review_id: '<?php echo $negative_review_id; ?>'});"><?php echo $negative_review_id; ?></button>
								</div>
							</div>
							<?php
						}
						foreach ($cs_violations as $cs_violation) {
							if (!is_object($cs_violation)) {
								continue;
							}
							?>
							<div class="vcm-param-container">
								<div class="vcm-param-label">Customer Service Violation</div>
								<div class="vcm-param-setting">
									<span><?php echo $cs_violation->category ?? ''; ?></span>
									<span class="vcm-param-setting-comment"><?php echo $cs_violation->tag ?? ''; ?></span>
								</div>
							</div>
							<?php
						}
						?>
						</div>
								<?php
							}
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
	echo '<pre>'.print_r($this->listing, true).'</pre><br/>';
}

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

<a class="vcm-hidden-refresh-url" href="index.php?option=com_vikchannelmanager&task=airbnbmnglisting&idroomota=%s" style="display: none;"></a>
<a class="vcm-hidden-list-url" href="index.php?option=com_vikchannelmanager&task=airbnblistings&loaded=1" style="display: none;"></a>

<script type="text/javascript">
/* Loading Overlay */
function vcmShowLoading() {
	jQuery(".vcm-loading-overlay").show();
}

function vcmStopLoading() {
	jQuery(".vcm-loading-overlay").hide();
}

Joomla.submitbutton = function(task) {
	if (task == 'airbnblst.savelisting' || task == 'airbnblst.updatelisting' || task == 'airbnblst.updatelisting_stay') {
		// submit form to controller
		vcmDoSaving(task);

		// exit
		return false;
	}
	// other buttons can submit the form normally
	Joomla.submitform(task, document.adminForm);
}

function vcmDoSaving(task) {
	// display loading overlay
	vcmShowLoading();
	// get form values
	var qstring = jQuery('#adminForm').serialize();
	// make sure the task is not set again, or the good one will go lost.
	qstring = qstring.replace('&task=', '&');
	// make the ajax request to the controller
	var jqxhr = jQuery.ajax({
		type: "POST",
		url: "index.php?option=com_vikchannelmanager&task=" + task + '&aj=1&e4j_debug=<?php echo VikRequest::getInt('e4j_debug', 0, 'request'); ?>',
		data: qstring
	}).done(function(res) {
		vcmStopLoading();
		if (res.indexOf('e4j.ok') >= 0) {
			// success
			alert(Joomla.JText._('MSG_BASE_SUCCESS') + '!');
			// all tasks (new/update) will append the listing id to the response
			var reload_url = jQuery('.vcm-hidden-refresh-url').attr('href');
			reload_url = reload_url.replace('%s', res.replace('e4j.ok.', ''));
			if (task == 'airbnblst.updatelisting') {
				// navigate to manage listings page (save & close)
				document.location.href = jQuery('.vcm-hidden-list-url').attr('href');
			} else {
				// navigate to the same page to show the reloaded/newly-created data
				document.location.href = reload_url;
			}
		} else {
			// error or warning
			if (res.indexOf('e4j.warning.') >= 0) {
				// warning
				var warning_mess = Joomla.JText._('MSG_BASE_WARNING_BOOKING_RAR');
				warning_mess = warning_mess.replace('%s', res.replace('e4j.warning.', ''));
				alert(warning_mess);
			} else {
				// error
				alert(res.replace('e4j.error.', ''));
			}
		}
	}).fail(function() {
		alert("Error performing AJAX request, please retry");
		vcmStopLoading();
	});

	return true;
}

function vcmFormatLatLng(inp) {
	if (!inp.value.length) {
		return;
	}
	inp.value.replace(',', '.').trim();
}

function vcmConfirmDelete() {
	if (confirm(Joomla.JText._('VCMREMOVECONFIRM'))) {
		return true;
	} else {
		return false;
	}
}

function vcmAddLocaleDescription() {
	var html_fields = jQuery('.vcm-listings-listing-description-new').first().clone();
	html_fields.removeClass('vcm-listings-listing-description-new').addClass('vcm-listings-listing-description');
	html_fields.show().find('input, select, textarea').prop('disabled', false).closest('.vcm-param-container').removeClass('vcm-param-container-tmp-disabled');
	// set proper array index key to input values
	var new_inp_key = jQuery('.vcm-listings-listing-description').length;
	html_fields.find('input, select, textarea').each(function() {
		jQuery(this).attr('name', jQuery(this).attr('name').replace('%d', new_inp_key));
	});
	// append fields
	html_fields.appendTo(jQuery('.vcm-listings-descriptions-wrap'));
}

function vcmRemoveNewDescription(elem) {
	jQuery(elem).closest('.vcm-listings-listing-description').remove();
}

function vcmAddRoom() {
	// count the next room_number value
	var next_room_number = 0;
	var rooms_counted = 0;
	jQuery('.vcm-listing-room').each(function() {
		var cur_room_number = jQuery(this).attr('data-room-number');
		if (cur_room_number && cur_room_number.length) {
			next_room_number = parseInt(cur_room_number) > next_room_number ? parseInt(cur_room_number) : next_room_number;
			rooms_counted++;
		}
	});
	if (rooms_counted > 0) {
		next_room_number++;
	}
	// clone fields
	var html_fields = jQuery('.vcm-listing-room-new').first().clone();
	html_fields.removeClass('vcm-listing-room-new').addClass('vcm-listing-room');
	html_fields.show().find('input:not(.vcm-inp-beds), select:not(.vcm-inp-beds), textarea:not(.vcm-inp-beds)').prop('disabled', false).closest('.vcm-param-container').removeClass('vcm-param-container-tmp-disabled');
	// set next room number in fields
	html_fields.attr('data-room-number', next_room_number);
	html_fields.find('.vcm-listing-room-number').text(next_room_number);
	html_fields.find('.vcm-inp-room-number').val(next_room_number);
	// set proper array index key to ALL input values (including the ones related to beds)
	var new_inp_key = jQuery('.vcm-listing-room').length;
	html_fields.find('input, select, textarea').each(function() {
		jQuery(this).attr('name', jQuery(this).attr('name').replace('%d', new_inp_key));
	});
	// append fields
	html_fields.appendTo(jQuery('.vcm-listings-rooms-wrap'));
}

function vcmRemoveNewRoom(elem) {
	jQuery(elem).closest('.vcm-listing-room').remove();
}

function vcmAddRoomBed(elem) {
	var html_fields = jQuery('.vcm-listing-rooms-bed-new').first().clone();
	html_fields.removeClass('vcm-listing-rooms-bed-new').addClass('vcm-listing-rooms-bed');
	html_fields.show().find('input, select, textarea').prop('disabled', false).closest('.vcm-param-container').removeClass('vcm-param-container-tmp-disabled');
	// set proper array index key (considering that the room has already been displayed)
	var new_inp_key = (jQuery('.vcm-listing-room').length - 1);
	html_fields.find('input, select, textarea').each(function() {
		jQuery(this).attr('name', jQuery(this).attr('name').replace('%d', new_inp_key));
	});
	// append fields
	html_fields.appendTo(jQuery(elem).closest('.vcm-listing-room').find('.vcm-listing-rooms-beds-wrap'));
}

function vcmAddRoomAccessibilityFeature(elem) {
	var html_fields = jQuery('.vcm-listing-rooms-af-new').first().clone();
	html_fields.removeClass('vcm-listing-rooms-af-new').addClass('vcm-listing-rooms-af');
	html_fields.show().find('input, select, textarea').prop('disabled', false).closest('.vcm-param-container').removeClass('vcm-param-container-tmp-disabled');
	// set proper array index key (considering that the room has already been displayed)
	var new_inp_key = (jQuery('.vcm-listing-room').length - 1);
	html_fields.find('input, select, textarea').each(function() {
		jQuery(this).attr('name', jQuery(this).attr('name').replace('%d', new_inp_key));
	});
	// append fields
	html_fields.appendTo(jQuery(elem).closest('.vcm-listing-room').find('.vcm-listing-rooms-af-wrap'));
}

function vcmAddListingExpectation() {
	var html_fields = jQuery('.vcm-listing-expectation-new').first().clone();
	html_fields.removeClass('vcm-listing-expectation-new').addClass('vcm-listing-expectation');
	html_fields.show().find('input, select, textarea').prop('disabled', false).closest('.vcm-param-container').removeClass('vcm-param-container-tmp-disabled');
	// set proper array index key to input values
	var new_inp_key = jQuery('.vcm-listing-expectation').length;
	html_fields.find('input, select, textarea').each(function() {
		jQuery(this).attr('name', jQuery(this).attr('name').replace('%d', new_inp_key));
	});
	// append fields
	html_fields.appendTo(jQuery('.vcm-listing-expectations-wrap'));
}

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

function vcmCheckPhotoCategory(category) {
	var photo_amenity_wrap = jQuery('#vcm-airbphoto-amenity');
	if (!category || !category.length || category == 'listing' || category == 'room') {
		// hide amenity selection
		photo_amenity_wrap.hide();
	}
	var listing_room_wrap = jQuery('#vcm-airbphoto-roomid');
	if (category == 'room' || category == 'room_amenity') {
		// check if the listing has got some rooms defined with an existing ID
		var listing_rooms = [];
		var current_lrooms = jQuery('.vcm-hid-listing-room-id');
		if (!current_lrooms || !current_lrooms.length) {
			// display alert message for no rooms created for the listing
			alert(Joomla.JText._('VCM_LISTING_HASNO_ROOMS'));
			// unset photo category value
			jQuery('.vcm-airbphoto-categorysel').val('');
			// animate scroll to the listing rooms
			jQuery('html,body').animate({scrollTop: jQuery('.vcm-listings-listing-wrap[data-landto="rooms"]').offset().top - 50}, {duration: 400});
			return false;
		}
		// push all the current rooms of the listing
		current_lrooms.each(function() {
			var listing_rtype_opt = jQuery(this).closest('.vcm-listing-room').find('.vcm-listing-room-type option:selected');
			var rtype_descr = listing_rtype_opt && listing_rtype_opt.length ? listing_rtype_opt.text() : jQuery(this).val();
			listing_rooms.push({
				id:  	jQuery(this).val(),
				descr: 	rtype_descr
			});
		});
		// build the options of room ids to allow one selection for the photo
		var html_listroom_options = '<option value=""></option>' + "\n";
		for (var i = 0; i < listing_rooms.length; i++) {
			html_listroom_options += '<option value="' + listing_rooms[i]['id'] + '">' + listing_rooms[i]['descr'] + '</option>' + "\n";
		}
		// set select content
		listing_room_wrap.find('select').html(html_listroom_options);
		// show param
		listing_room_wrap.show();
	} else {
		// hide room selection
		listing_room_wrap.hide();
	}
	if (category == 'listing_amenity' || category == 'room_amenity') {
		// check if the listing is assigned to any accessibility amenity that needs photos
		var access_amenities = [];
		var selected_amenities = jQuery('select.vcm-airblist-amenities option[data-accessibility="1"]:selected');
		if (!selected_amenities || !selected_amenities.length) {
			// display alert message for no amenities of type accessibility selected
			alert(Joomla.JText._('VCM_NOAMENITIES_ACCESS'));
			// unset photo category value
			jQuery('.vcm-airbphoto-categorysel').val('');
			// animate scroll to the listing amenities
			jQuery('html,body').animate({scrollTop: jQuery('#vcm-airblist-amenities-wrap').offset().top - 50}, {duration: 400});
			return false;
		}
		// push all the selected amenities of type accessibility
		selected_amenities.each(function() {
			access_amenities.push({
				name:  jQuery(this).attr('value'),
				descr: jQuery(this).text()
			});
		});
		// build the options of amenities to allow one selection for the photo
		var html_amenitiy_options = '<option value=""></option>' + "\n";
		for (var i = 0; i < access_amenities.length; i++) {
			html_amenitiy_options += '<option value="' + access_amenities[i]['name'] + '">' + access_amenities[i]['descr'] + '</option>' + "\n";
		}
		// set select content
		photo_amenity_wrap.find('select').html(html_amenitiy_options);
		// show param
		photo_amenity_wrap.show();
	}
}

function vcmDebounceEvent(method, delay) {
	clearTimeout(method.timer);
	method.timer = setTimeout(function() {
		method();
	}, delay);
}

function vcmHandleScroll() {
	if (jQuery(window).scrollTop() > 1000) {
		jQuery('.vcm-floating-scrolltop').fadeIn();
	} else {
		jQuery('.vcm-floating-scrolltop').hide();
	}
}

// gallery params
var vcmFxParams = {
	sourceAttr: 'data-large-url',
	captionSelector: 'self',
	captionType: 'data',
	captionsData: 'caption',
	captionClass: 'vcm-photo-caption-active',
};

jQuery(function() {

	// disable input fields when in edit mode
	if (jQuery('#idroomota').length) {
		jQuery('#adminForm')
			.find('input:not([type="hidden"]):not(.vcm-listing-editable), select:not(.vcm-listing-editable), textarea:not(.vcm-listing-editable)')
			.prop('disabled', true)
			.closest('.vcm-param-container')
			.addClass('vcm-param-container-tmp-disabled')
			.on('click', function() {
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
	}

	// render select2
	jQuery('.vcm-multi-select').select2();

	// sortable photos
	jQuery('.vcm-airbphotos-gallery-thumbs-inner').sortable({
		items: '.vcm-airbphotos-gallery-thumb',
		helper: 'clone',
		update: function(event, ui) {
			console.log('sorted');
			// we need to enable all hidden fields, or the new sorting won't be applied
			jQuery(this).find('input[type="hidden"]:disabled').prop('disabled', false);
			/**
			 * We need to re-calculate the hidden value for the new sorting position
			 * for all photos, or the new actual sorting won't be applied.
			 */
			var all_thumbs = jQuery('.vcm-airbphotos-gallery-thumb');
			all_thumbs.each(function() {
				var new_sorting_pos = (all_thumbs.index(jQuery(this)) + 1);
				jQuery(this).find('input.vcm-hidden-inp-photo-order').val(new_sorting_pos);
			});
		},
	});
	jQuery('.vcm-airbphotos-gallery-thumbs-inner').disableSelection();

	// photo gallery
	window['vcmFxGallery'] = jQuery('.vcm-airbphotos-img').vikFxGallery(vcmFxParams);

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
	jQuery(window).scroll(function() {
		vcmDebounceEvent(vcmHandleScroll, 500);
	});

	// generate content through AI (text-area expected for setting the generated content)
	const def_gen_ai_prompt = (document.querySelector('.vcm-content-genai-field[data-field="information"]')?.value || '').trim();
	document.querySelectorAll('.vcm-content-genai').forEach((genai_btn) => {
		genai_btn.addEventListener('click', (e) => {
			let btn = e.target;
			// get the type of description and comments
			let descr_type = (btn.getAttribute('data-descr-type') || '').toUpperCase();
			let type_comments = btn.closest('.vcm-param-container')?.querySelector('.vcm-param-setting-comment');
			if (type_comments) {
				type_comments = type_comments.innerText;
			}
			type_comments = type_comments || '';

			// get the target element
			let target_element = btn.closest('.vcm-param-container')?.querySelector('textarea');
			if (!target_element) {
				throw new Error('Could not find a valid input/text-area field to contain the generated contents.');
			}

			// modify the information text for the prompt
			document.querySelector('.vcm-content-genai-field[data-field="information"]').value = def_gen_ai_prompt + "\n" + [descr_type, type_comments].filter((txt) => txt).join(' - ');

			// define the modal cancel button
			let cancel_btn = jQuery('<button></button>')
				.attr('type', 'button')
				.addClass('btn')
				.text(<?php echo json_encode(JText::_('CANCEL')); ?>)
				.on('click', function() {
					VBOCore.emitEvent('vcm-content-genai-dismiss');
				});

			// define the submit button
			let submit_btn = jQuery('<button></button>')
				.attr('type', 'button')
				.addClass('btn btn-success')
				.text(<?php echo json_encode(JText::_('VCM_GEN_CONTENT')); ?>)
				.on('click', function() {
					// start loading
					VBOCore.emitEvent('vcm-content-genai-loading');

					// perform the request
					VBOCore.doAjax(
						"<?php echo VikBooking::ajaxUrl('index.php?option=com_vikbooking&task=ai.roomContent'); ?>",
						{
							information: document.querySelector('.vcm-content-genai-field[data-field="information"]').value,
							language: document.querySelector('.vcm-content-genai-field[data-field="language"]').value.replace('__', ' - '),
						},
						(resp) => {
							// stop loading
							VBOCore.emitEvent('vcm-content-genai-loading');

							// set description content
							resp = typeof resp === 'string' ? JSON.parse(resp) : resp;

							target_element.value = resp['content'];

							// dismiss the modal on success
							VBOCore.emitEvent('vcm-content-genai-dismiss');
						},
						(error) => {
							// stop loading
							VBOCore.emitEvent('vcm-content-genai-loading');

							// display the error
							alert(error.responseText);
						}
					);
				});

			// display modal
			let modal_body = VBOCore.displayModal({
				suffix: 		'content-genai',
				extra_class: 	'vbo-modal-rounded',
				title: 			<?php echo json_encode(JText::_('VCM_GEN_CONTENT') . ' - ' . JText::_('VCM_AI_CHAT_TOOLTIP')); ?>,
				footer_left: 	cancel_btn,
				footer_right: 	submit_btn,
				loading_event:  'vcm-content-genai-loading',
				dismiss_event:  'vcm-content-genai-dismiss',
				onDismiss: 		() => {
					jQuery('.vcm-content-genai-wrap').appendTo('.vcm-content-genai-helper');
				},
			});

			jQuery('.vcm-content-genai-wrap').appendTo(modal_body);
		});
	});

});
</script>
