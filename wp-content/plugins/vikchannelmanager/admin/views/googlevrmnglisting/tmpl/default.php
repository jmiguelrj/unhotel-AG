<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2024 E4J srl. All rights reserved.
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

// translator object and default language
$tn_obj = VikBooking::getTranslator();
$lang_code = $tn_obj->getDefaultLang();
$lang_code = substr(strtolower($lang_code), 0, 2);

// Vik Booking Application for special field types
$vbo_app = VikChannelManager::getVboApplication();

// wrap listing object into a JObject object
$listing = new JObject($this->listing);
$vbo_listing = new JObject($this->vbo_listing);
$attributes = new JObject($listing->get('attributes', []));

// check if we are in editing or new mode
$is_editing = count(get_object_vars($this->listing));

// check if the listing is mapped
$listing_mapped = $is_editing && isset($this->otalistings[$listing->get('id', -1)]);

// check for minimum listing contents
$minimum_contents = true;
if (!$listing->on_server || !$listing->latitude || !$listing->longitude || !$listing->address || !$listing->city || !$listing->country) {
	// mandatory details are missing
	$minimum_contents = false;
}

// lang vars for JS
JText::script('VCMREMOVECONFIRM');
JText::script('MSG_BASE_SUCCESS');
JText::script('MSG_BASE_WARNING_BOOKING_RAR');

?>

<div class="vcm-loading-overlay">
	<div class="vcm-loading-dot vcm-loading-dot1"></div>
	<div class="vcm-loading-dot vcm-loading-dot2"></div>
	<div class="vcm-loading-dot vcm-loading-dot3"></div>
	<div class="vcm-loading-dot vcm-loading-dot4"></div>
	<div class="vcm-loading-dot vcm-loading-dot5"></div>
</div>

<div class="vcm-listings-list-head">
	<h3><?php echo JText::_('VCMACCOUNTCHANNELID') . ' ' . $this->account_key; ?></h3>
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
				<span class="vcm-listing-toolbar-btn" data-jumpto="spaces">
					<a href="JavaScript: void(0);"><?php VikBookingIcons::e('bed'); ?> <span>Spaces</span></a>
				</span>
			</div>
			<div class="vcm-listing-toolbar-block">
				<span class="vcm-listing-toolbar-btn" data-jumpto="photos">
					<a href="JavaScript: void(0);"><?php VikBookingIcons::e('camera'); ?> <span><?php echo JText::_('VCMMENUBPHOTOS'); ?></span></a>
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
			<div class="vcm-listing-toolbar-block">
				<span class="vcm-listing-toolbar-btn" data-jumpto="attributes">
					<a href="JavaScript: void(0);"><?php VikBookingIcons::e('icons'); ?> <span><?php echo JText::_('VCM_ATTRIBUTES'); ?></span></a>
				</span>
			</div>
		</div>
	</div>
	<div class="vcm-listing-content-validation-status">
	<?php
	if (!$minimum_contents) {
		?>
		<p class="err"><?php VikBookingIcons::e('exclamation-circle'); ?> <?php echo JText::_('VCM_VRBO_LISTING_CONTVALIDATION_STATUS'); ?>: missing mandatory details.</p>
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
							<div class="vcm-param-label">Listing name</div>
							<div class="vcm-param-setting">
								<input type="text" name="listing[name]" id="vcm-listing-name" value="<?php echo $this->escape($listing->get('name', '')); ?>" maxlength="100" />
								<span class="vcm-param-setting-comment">The name of the listing displayed on Google.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VBSTATUS'); ?></div>
							<div class="vcm-param-setting">
								<select name="listing[active]">
									<option value="1"<?php echo $listing->get('active', null) ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMPROMSTATUSACTIVE'); ?></option>
									<option value="0"<?php echo !$listing->get('active', null) ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMPROMSTATUSINACTIVE'); ?></option>
								</select>
								<span class="vcm-param-setting-comment">Inactive listings will not be included in the Google feed.</span>
							</div>
						</div>

						<?php
						$listing_description = $listing->get('description', '');
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-label">Description</div>
							<div class="vcm-param-setting">
								<textarea name="listing[description]" maxlength="10000"><?php echo $this->escape($listing_description); ?></textarea>
								<span class="vcm-param-setting-comment">Listing description text for Google.</span>
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
								<input type="text" name="listing[locale]" value="<?php echo $this->escape($listing->get('locale', $lang_code)); ?>" maxlength="2" />
								<span class="vcm-param-setting-comment">2-char default language code for the listing contents. Leave it empty to apply the website default language code.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Website address</div>
							<div class="vcm-param-setting">
								<input type="text" name="listing[website]" value="<?php echo $this->escape($listing->get('website', '')); ?>" class="vcm-listing-editable" readonly />
								<span class="vcm-param-setting-comment">Listing website address. Not correct? This will be automatically routed by your CMS when saving.</span>
							</div>
						</div>

					</div>
				</div>
			</fieldset>

			<fieldset class="adminform vcm-listings-listing-wrap" data-landto="spaces">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('bed'); ?> Spaces</legend>
					<div class="vcm-params-container">

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCM_BEDROOMS'); ?></div>
							<div class="vcm-param-setting">
								<input type="number" name="listing[number_of_bedrooms]" min="0" value="<?php echo (int) $listing->get('number_of_bedrooms', 1); ?>" />
								<span class="vcm-param-setting-comment">Number of bedrooms available.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('BATHROOMS'); ?></div>
							<div class="vcm-param-setting">
								<input type="number" name="listing[number_of_bathrooms]" min="0" value="<?php echo (int) $listing->get('number_of_bathrooms', 1); ?>" />
								<span class="vcm-param-setting-comment">Number of bathrooms available.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCM_LISTING_BEDS'); ?></div>
							<div class="vcm-param-setting">
								<input type="number" name="listing[number_of_beds]" min="0" value="<?php echo (int) $listing->get('number_of_beds', 1); ?>" />
								<span class="vcm-param-setting-comment">Number of beds available.</span>
							</div>
						</div>

					</div>
				</div>
			</fieldset>

			<fieldset class="adminform vcm-listings-listing-wrap" data-landto="photos">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('camera'); ?> <?php echo JText::_('VCMMENUBPHOTOS'); ?></legend>
					<div class="vcm-params-container">

						<div class="vcm-param-container">
							<div class="vcm-param-setting">
								<p class="warn"><?php VikBookingIcons::e('exclamation-triangle'); ?> Listings with fewer than 5 images will be blocked from appearing on Google Vacation Rentals. Although 8 photos is the minimum recommendation, it is strongly recommended providing 20+ photos (or as many as you have available) for a competitive advantage.</p>
								<p class="info"><?php VikBookingIcons::e('exclamation-circle'); ?> Listing photos are managed through the <a href="index.php?option=com_vikbooking&task=editroom&cid[]=<?php echo $vbo_listing->get('id', 0); ?>" target="_blank">Booking Engine</a>.</p>
							</div>
						</div>

						<?php
						// display current photos, if any
						$current_photos = (array) $listing->get('photos', []);

						// attempt to read the updated photos from the VBO room
						$vbo_photos   = [];
						$vbo_mainimg  = $vbo_listing->get('img', '');
						$vbo_moreimgs = explode(';;', $vbo_listing->get('moreimgs', ''));
						if (!empty($vbo_mainimg)) {
							$photo_path = implode(DIRECTORY_SEPARATOR, [VBO_SITE_PATH, 'resources', 'uploads', $vbo_mainimg]);
							$photo_url = VBO_SITE_URI . 'resources/uploads/' . $vbo_mainimg;
							if (is_file($photo_path) && filesize($photo_path) <= 10 * pow(1024, 2)) {
								// ensure the file is not broken and owns a size not higher than 10 MB
								$vbo_photos[] = $photo_url;
							}
						}
						foreach (array_filter($vbo_moreimgs) as $vbo_moreimg) {
							$photo_path = implode(DIRECTORY_SEPARATOR, [VBO_SITE_PATH, 'resources', 'uploads', 'big_' . $vbo_moreimg]);
							$photo_url = VBO_SITE_URI . 'resources/uploads/big_' . $vbo_moreimg;
							if (is_file($photo_path) && filesize($photo_path) <= 10 * pow(1024, 2)) {
								// ensure the file is not broken and owns a size not higher than 10 MB
								$vbo_photos[] = $photo_url;
							}
						}

						if (!$current_photos || array_diff($vbo_photos, $current_photos) || ($current_photos && array_diff($current_photos, $vbo_photos))) {
							// use the VBO photos by default whenever something has changed
							$current_photos = $vbo_photos;
						}

						?>
						<div class="vcm-airbphotos-gallery-thumbs-inner vcm-googlevr-gallery-thumbs-inner">
						<?php
						foreach ($current_photos as $photo) {
							?>
							<div class="vcm-airbphotos-gallery-thumb">
								<div class="vcm-airbphotos-gallery-thumb-inner">
									<div class="vcm-airbphotos-gallery-thumb-img">
										<img src="<?php echo $photo; ?>" class="vcm-airbphotos-img check-img-size" data-large-url="<?php echo $this->escape($photo); ?>" data-caption="" data-propgallery="<?php echo $this->listing->id; ?>" />
										<input type="hidden" class="vcm-hidden-inp-photo-url" name="listing[photos][]" value="<?php echo $this->escape($photo); ?>" />
									</div>
								</div>
							</div>
							<?php
						}
						?>
						<style>
							.check-img-size.disabled {
								filter: grayscale(1) opacity(0.7) brightness(0.5);
							}
						</style>
						<script>
							(function($) {
								'use strict';

								const getImageSize = (img) => {
									return new Promise((resolve, reject) => {
										// Create an object holding the natural size of the image object.
										const _getImageSize = () => {
											return {
												width: img.naturalWidth,
												height: img.naturalHeight,
											};
										}

										if (img.naturalWidth && img.naturalHeight) {
											// image size ready, immediately resolve
											resolve(_getImageSize());
										} else {
											img.onload = () => {
												// wait for image loading completion
												resolve(_getImageSize());
											}
										}
									});
								}

								$(function() {
									$('.check-img-size').each(function() {
										getImageSize(this).then((size) => {
											if (size.width > 4000 || size.height > 4000) {
												$(this).addClass('disabled');
												$(this).attr('title', 'This image does not meet Google\'s requirements due to its size, scale, or automatic quality indicators. Make sure your image is less than 10 MB, less than 4000 pixels wide, and less than 4000 pixels high.');
												$(this).next('input').remove();
												$(this).tooltip();
											} else {
												// append sizes within the form
												$(this).parent().append('<input type="hidden" name="listing[photo_widths][]" value="' + size.width + '" />');
												$(this).parent().append('<input type="hidden" name="listing[photo_heights][]" value="' + size.height + '" />');
											}
										});
									});
								});
							})(jQuery);
						</script>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-setting">
								<p class="info"><?php VikBookingIcons::e('exclamation-circle'); ?> The image order may be auto-generated and could not be changed. Google ranks and displays images within your property listing and could take into consideration factors, such as user query terms. For example, when a user searches for "vacation rentals with a pool," pool-related images might display at a higher level.</p>
							</div>
						</div>

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

				</script>

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
								<a class="btn btn-danger" href="<?php echo VCMFactory::getPlatform()->getUri()->addCSRF('index.php?option=com_vikchannelmanager&task=googlevrlst.delete&listing_id=' . $this->listing->id, true); ?>" onclick="return vcmConfirmDelete();"><?php VikBookingIcons::e('bomb'); ?> <?php echo JText::_('VCMBCAHDELETE'); ?></a>
							</div>
						</div>

					</div>
				</div>
			</fieldset>
			<?php
		}
		?>

		</div>

		<div class="vcm-config-maintab-right">

			<fieldset class="adminform vcm-listings-listing-wrap" data-landto="location">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('map'); ?> <?php echo JText::_('VCMBCAHIMGTAG123'); ?></legend>
					<div class="vcm-params-container">

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMBCAHADDLINE'); ?>*</div>
							<div class="vcm-param-setting">
								<input type="text" id="vcm-listing-street" name="listing[address]" value="<?php echo $listing->get('address', ''); ?>" size="40" maxlength="225" />
								<span class="vcm-param-setting-comment">Required listing physical street address.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMTACHOTELCITY'); ?>*</div>
							<div class="vcm-param-setting">
								<input type="text" id="vcm-listing-city" name="listing[city]" value="<?php echo $listing->get('city', ''); ?>" size="40" maxlength="80" />
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMTACHOTELSTATE'); ?></div>
							<div class="vcm-param-setting">
								<select name="listing[state]" id="vcm-listing-state" data-stateset="<?php echo $this->escape($listing->get('state', '')); ?>">
									<option value=""></option>
								</select>
								<!-- the field listing[state] can be manipulated by Google Maps, so we also use the alias value for listing[province] in case of other OTA imports -->
								<input type="hidden" name="listing[province]" value="<?php echo $this->escape($listing->get('province', $listing->get('state', ''))); ?>" />
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMBCAHPOSCODE'); ?>*</div>
							<div class="vcm-param-setting">
								<input type="text" name="listing[zip]" value="<?php echo $listing->get('zip', ''); ?>" size="40" maxlength="50" />
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMTACHOTELCOUNTRY'); ?>*</div>
							<div class="vcm-param-setting">
								<select name="listing[country]" id="vcm-listing-country">
									<option value="" data-c3code=""></option>
								<?php
								foreach ($this->countries as $country) {
									$country_found = $listing->get('country', '') == $country['country_2_code'] || $listing->get('country', '') == $country['country_3_code'];
									?>
									<option data-c3code="<?php echo $country['country_3_code']; ?>" value="<?php echo $country['country_2_code']; ?>"<?php echo $country_found ? ' selected="selected"' : ''; ?>><?php echo $country['country_name']; ?></option>
									<?php
								}
								?>
								</select>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMTACHOTELLATITUDE'); ?>*</div>
							<div class="vcm-param-setting">
								<div class="btn-wrapper input-append">
									<input type="text" id="vcmhlat" name="listing[latitude]" value="<?php echo $listing->get('latitude', ''); ?>" data-ftype="latitude" size="40" />
									<button type="button" class="btn vcm-config-btn vcm-get-coords" title="<?php echo htmlspecialchars(JText::_('VCM_YOUR_CURR_LOCATION')); ?>"><?php VikBookingIcons::e('location-arrow'); ?></button>
								</div>
								<span class="vcm-param-setting-comment">Required latitude (decimal number) that corresponds to the location of the listing.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMTACHOTELLONGITUDE'); ?>*</div>
							<div class="vcm-param-setting">
								<input type="text" id="vcmhlng" name="listing[longitude]" value="<?php echo $listing->get('longitude', ''); ?>" data-ftype="longitude" size="40"/>
								<span class="vcm-param-setting-comment">Required longitude (decimal number) that corresponds to the location of the listing.</span>
							</div>
						</div>

						<?php
						$geocoding = VikChannelManager::getGeocodingInstance();
						if ($geocoding) {
							// load the necessary assets
							$geocoding->loadAssets();

							// check if latitude and longitude have been defined
							$list_lat = $listing->get('latitude', '');
							$list_lng = $listing->get('longitude', '');
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
							var start_lat = '<?php echo $listing->get('latitude', ''); ?>';
							var start_lng = '<?php echo $listing->get('longitude', ''); ?>';
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
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('hand-paper'); ?> <?php echo JText::_('VCMBCAHPOLICIES'); ?></legend>
					<div class="vcm-params-container">

					<?php
					$def_rplan_id = $listing->get('def_rplan_id');
					if ($this->vbo_listing_rplans) {
						if (count($this->vbo_listing_rplans) > 1) {
							?>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMRARRATEPLAN') . ' (' . JText::_('VCMROOMSRELDEFRPLAN') . ')'; ?></div>
							<div class="vcm-param-setting">
								<select name="listing[def_rplan_id]">
								<?php
								foreach ($this->vbo_listing_rplans as $vbo_rplan) {
									$is_rplan_sel = ((empty($def_rplan_id) && stripos($vbo_rplan['name'], 'standard') !== false) || $vbo_rplan['idprice'] == $def_rplan_id);
									?>
									<option value="<?php echo $vbo_rplan['idprice']; ?>"<?php echo $is_rplan_sel ? ' selected="selected"' : ''; ?>><?php echo $vbo_rplan['name']; ?></option>
									<?php
								}
								?>
								</select>
								<span class="vcm-param-setting-comment">The default rate plan from which rates will be pulled.</span>
							</div>
						</div>
							<?php
						} else {
							?>
							<input type="hidden" name="listing[def_rplan_id]" value="<?php echo $this->vbo_listing_rplans[0]['idprice']; ?>" />
							<?php
						}
					}

					$instant_bookable = (bool) $listing->get('instant_bookable', true);
					$self_checkin_checkout = (bool) $listing->get('self_checkin_checkout', false);
					?>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Instant bookable</div>
							<div class="vcm-param-setting">
								<select name="listing[instant_bookable]">
									<option value="1"<?php echo $instant_bookable ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMYES'); ?></option>
									<option value="0"<?php echo !$instant_bookable ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMNO'); ?></option>
								</select>
								<span class="vcm-param-setting-comment">Whether the listing can be booked directly through your website, or if reservations require an approval.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Self checkin/checkout</div>
							<div class="vcm-param-setting">
								<select name="listing[self_checkin_checkout]">
									<option value="0"<?php echo !$self_checkin_checkout ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMNO'); ?></option>
									<option value="1"<?php echo $self_checkin_checkout ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMYES'); ?></option>
								</select>
								<span class="vcm-param-setting-comment">Whether the property allows for self check-in and check-out..</span>
							</div>
						</div>

						<div class="vcm-params-block">
							<?php
							// check-in/check-out times
							$checkin_time = $listing->get('checkin_time', '');
							$checkout_time = $listing->get('checkout_time', '');
							?>
							<div class="vcm-param-container">
								<div class="vcm-param-label"><?php echo JText::_('VCMPVIEWORDERSVBFOUR'); ?></div>
								<div class="vcm-param-setting">
									<select name="listing[checkin_time]">
									<?php
									for ($h = 0; $h < 24; $h++) {
										for ($m = 0; $m < 60; $m += 30) {
											$say_time = ($h < 10 ? "0{$h}" : $h) . ':' . ($m < 10 ? "0{$m}" : $m);
											?>
										<option value="<?php echo $say_time; ?>"<?php echo $checkin_time == $say_time ? ' selected="selected"' : ''; ?>><?php echo $say_time; ?></option>
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
									<select name="listing[checkout_time]">
									<?php
									for ($h = 0; $h < 24; $h++) {
										for ($m = 0; $m < 60; $m += 30) {
											$say_time = ($h < 10 ? "0{$h}" : $h) . ':' . ($m < 10 ? "0{$m}" : $m);
											?>
										<option value="<?php echo $say_time; ?>"<?php echo $checkout_time == $say_time ? ' selected="selected"' : ''; ?>><?php echo $say_time; ?></option>
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
								<div class="vcm-param-label"><?php echo JText::_('VCMBCAHIMGTAG92') . ' (Max)'; ?></div>
								<div class="vcm-param-setting">
									<input type="number" name="listing[attributes][capacity]" value="<?php echo $attributes->get('capacity', 1); ?>" min="1" max="99" />
								</div>
							</div>
							<div class="vcm-param-container">
								<div class="vcm-param-label"><?php echo JText::_('VCMADULTS') . ' (Max)'; ?></div>
								<div class="vcm-param-setting">
									<input type="number" name="listing[max_adults]" value="<?php echo $listing->get('max_adults', 1); ?>" min="1" max="99" />
								</div>
							</div>
							<div class="vcm-param-container">
								<div class="vcm-param-label"><?php echo JText::_('VCMCHILDREN') . ' (Max)'; ?></div>
								<div class="vcm-param-setting">
									<input type="number" name="listing[max_children]" value="<?php echo $listing->get('max_children', 1); ?>" min="0" max="99" />
								</div>
							</div>
						</div>

					</div>
				</div>
			</fieldset>

			<fieldset class="adminform vcm-listings-listing-wrap" data-landto="attributes">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php VikBookingIcons::e('icons'); ?> <?php echo JText::_('VCM_ATTRIBUTES'); ?></legend>
					<div class="vcm-params-container">

						<div class="vcm-params-block">
							<div class="vcm-param-container">
								<div class="vcm-param-label">Listing rating</div>
								<div class="vcm-param-setting">
									<input type="number" name="listing[attributes][rating]" value="<?php echo $attributes->get('rating', ''); ?>" step="any" min="1" max="5" />
									<span class="vcm-param-setting-comment">Floating point number representing the aggregate property rating on a scale of 5. For example 4 indicates the rating 4/5.</span>
								</div>
							</div>
							<div class="vcm-param-container">
								<div class="vcm-param-label">Number of ratings</div>
								<div class="vcm-param-setting">
									<input type="number" name="listing[attributes][rating_num]" value="<?php echo $attributes->get('rating_num', ''); ?>" min="1" max="9999999" />
									<span class="vcm-param-setting-comment">Number of ratings that the property has (on how many reviews the listing rating is based on).</span>
								</div>
							</div>
							<div class="vcm-param-container">
								<div class="vcm-param-label">Rating scale</div>
								<div class="vcm-param-setting">
									<input type="number" name="listing[attributes][rating_scale]" value="<?php echo $attributes->get('rating_scale', 5); ?>" min="5" max="10" readonly />
									<span class="vcm-param-setting-comment">Recommended rating scale is 5. For example, a rating of 4.7 on a scale of 5 (4.7/5).</span>
								</div>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Hygiene link</div>
							<div class="vcm-param-setting">
								<input type="text" name="listing[attributes][partner_hygiene_link]" value="<?php echo $this->escape($attributes->get('partner_hygiene_link', '')); ?>" />
								<span class="vcm-param-setting-comment">Optional link to site providing more information about the hygiene details of the listing.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMBCAHAMENTYPE2'); ?></div>
							<div class="vcm-param-setting">
								<input type="hidden" name="listing[attributes][ac]" value="0" />
								<?php echo $vbo_app->printYesNoButtons('listing[attributes][ac]', JText::_('VCMYES'), JText::_('VCMNO'), (int) $attributes->get('ac', 0), 1, 0); ?>
								<span class="vcm-param-setting-comment">Whether the property has air conditioning.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Beach access</div>
							<div class="vcm-param-setting">
								<input type="hidden" name="listing[attributes][beach_access]" value="0" />
								<?php echo $vbo_app->printYesNoButtons('listing[attributes][beach_access]', JText::_('VCMYES'), JText::_('VCMNO'), (int) $attributes->get('beach_access', 0), 1, 0); ?>
								<span class="vcm-param-setting-comment">Whether the property has beach access.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Child friendly</div>
							<div class="vcm-param-setting">
								<input type="hidden" name="listing[attributes][child_friendly]" value="0" />
								<?php echo $vbo_app->printYesNoButtons('listing[attributes][child_friendly]', JText::_('VCMYES'), JText::_('VCMNO'), (int) $attributes->get('child_friendly', 0), 1, 0); ?>
								<span class="vcm-param-setting-comment">Whether the property is suitable for children.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Crib</div>
							<div class="vcm-param-setting">
								<input type="hidden" name="listing[attributes][crib]" value="0" />
								<?php echo $vbo_app->printYesNoButtons('listing[attributes][crib]', JText::_('VCMYES'), JText::_('VCMNO'), (int) $attributes->get('crib', 0), 1, 0); ?>
								<span class="vcm-param-setting-comment">Whether the property provides a crib.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Elevator</div>
							<div class="vcm-param-setting">
								<input type="hidden" name="listing[attributes][elevator]" value="0" />
								<?php echo $vbo_app->printYesNoButtons('listing[attributes][elevator]', JText::_('VCMYES'), JText::_('VCMNO'), (int) $attributes->get('elevator', 0), 1, 0); ?>
								<span class="vcm-param-setting-comment">Whether the property has an elevator.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Gym fitness equipment</div>
							<div class="vcm-param-setting">
								<input type="hidden" name="listing[attributes][gym_fitness_equipment]" value="0" />
								<?php echo $vbo_app->printYesNoButtons('listing[attributes][gym_fitness_equipment]', JText::_('VCMYES'), JText::_('VCMNO'), (int) $attributes->get('gym_fitness_equipment', 0), 1, 0); ?>
								<span class="vcm-param-setting-comment">Whether the property has a gym or any fitness equipment.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Heating</div>
							<div class="vcm-param-setting">
								<input type="hidden" name="listing[attributes][heating]" value="0" />
								<?php echo $vbo_app->printYesNoButtons('listing[attributes][heating]', JText::_('VCMYES'), JText::_('VCMNO'), (int) $attributes->get('heating', 0), 1, 0); ?>
								<span class="vcm-param-setting-comment">Whether the property has heating.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Hot tub</div>
							<div class="vcm-param-setting">
								<input type="hidden" name="listing[attributes][hot_tub]" value="0" />
								<?php echo $vbo_app->printYesNoButtons('listing[attributes][hot_tub]', JText::_('VCMYES'), JText::_('VCMNO'), (int) $attributes->get('hot_tub', 0), 1, 0); ?>
								<span class="vcm-param-setting-comment">Whether the property has a hot tub.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Ironing board</div>
							<div class="vcm-param-setting">
								<input type="hidden" name="listing[attributes][ironing_board]" value="0" />
								<?php echo $vbo_app->printYesNoButtons('listing[attributes][ironing_board]', JText::_('VCMYES'), JText::_('VCMNO'), (int) $attributes->get('ironing_board', 0), 1, 0); ?>
								<span class="vcm-param-setting-comment">Whether the property has ironing boards available.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Kitchen</div>
							<div class="vcm-param-setting">
								<input type="hidden" name="listing[attributes][kitchen]" value="0" />
								<?php echo $vbo_app->printYesNoButtons('listing[attributes][kitchen]', JText::_('VCMYES'), JText::_('VCMNO'), (int) $attributes->get('kitchen', 0), 1, 0); ?>
								<span class="vcm-param-setting-comment">Whether the property has a kitchen.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Microwave</div>
							<div class="vcm-param-setting">
								<input type="hidden" name="listing[attributes][microwave]" value="0" />
								<?php echo $vbo_app->printYesNoButtons('listing[attributes][microwave]', JText::_('VCMYES'), JText::_('VCMNO'), (int) $attributes->get('microwave', 0), 1, 0); ?>
								<span class="vcm-param-setting-comment">Whether the property has a microwave available.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Patio</div>
							<div class="vcm-param-setting">
								<input type="hidden" name="listing[attributes][patio]" value="0" />
								<?php echo $vbo_app->printYesNoButtons('listing[attributes][patio]', JText::_('VCMYES'), JText::_('VCMNO'), (int) $attributes->get('patio', 0), 1, 0); ?>
								<span class="vcm-param-setting-comment">Whether the property has a patio.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Pets allowed</div>
							<div class="vcm-param-setting">
								<input type="hidden" name="listing[attributes][pets_allowed]" value="0" />
								<?php echo $vbo_app->printYesNoButtons('listing[attributes][pets_allowed]', JText::_('VCMYES'), JText::_('VCMNO'), (int) $attributes->get('pets_allowed', 0), 1, 0); ?>
								<span class="vcm-param-setting-comment">Some or all rooms allow guests to bring pets - dogs or cats that aren't service animals - with them..</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Smoking free property</div>
							<div class="vcm-param-setting">
								<input type="hidden" name="listing[attributes][smoking_free_property]" value="0" />
								<?php echo $vbo_app->printYesNoButtons('listing[attributes][smoking_free_property]', JText::_('VCMYES'), JText::_('VCMNO'), (int) $attributes->get('smoking_free_property', 0), 1, 0); ?>
								<span class="vcm-param-setting-comment">Whether the property is smoke-free or no smoking allowed.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">TV</div>
							<div class="vcm-param-setting">
								<input type="hidden" name="listing[attributes][tv]" value="0" />
								<?php echo $vbo_app->printYesNoButtons('listing[attributes][tv]', JText::_('VCMYES'), JText::_('VCMNO'), (int) $attributes->get('tv', 0), 1, 0); ?>
								<span class="vcm-param-setting-comment">Whether the property has a TV.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Washer dryer</div>
							<div class="vcm-param-setting">
								<input type="hidden" name="listing[attributes][washer_dryer]" value="0" />
								<?php echo $vbo_app->printYesNoButtons('listing[attributes][washer_dryer]', JText::_('VCMYES'), JText::_('VCMNO'), (int) $attributes->get('washer_dryer', 0), 1, 0); ?>
								<span class="vcm-param-setting-comment">Whether the property has laundry appliances.</span>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">Wheelchair accessible</div>
							<div class="vcm-param-setting">
								<input type="hidden" name="listing[attributes][wheelchair_accessible]" value="0" />
								<?php echo $vbo_app->printYesNoButtons('listing[attributes][wheelchair_accessible]', JText::_('VCMYES'), JText::_('VCMNO'), (int) $attributes->get('wheelchair_accessible', 0), 1, 0); ?>
								<span class="vcm-param-setting-comment">Whether the property is wheelchair accessible.</span>
							</div>
						</div>

					</div>
				</div>
			</fieldset>

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

<a class="vcm-hidden-refresh-url" href="index.php?option=com_vikchannelmanager&view=googlevrmnglisting&idroomota=%s" style="display: none;"></a>
<a class="vcm-hidden-list-url" href="index.php?option=com_vikchannelmanager&view=googlevrlistings&loaded=1" style="display: none;"></a>

<script type="text/javascript">

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
	if (task == 'googlevrlst.savelisting' || task == 'googlevrlst.updatelisting' || task == 'googlevrlst.updatelisting_stay') {
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
				if (task == 'googlevrlst.updatelisting') {
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
	// disable input fields when in edit mode and listing contents are sufficient
	if (jQuery('#idroomota').length && <?php echo $minimum_contents ? 'true' : 'false'; ?>) {
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
