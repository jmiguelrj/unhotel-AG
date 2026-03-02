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

// build a map of OTA rooms and VBO rooms
$rooms_map = array();
foreach ($this->otarooms as $otar) {
	if (empty($otar['idroomvb'])) {
		continue;
	}
	foreach ($this->vbrooms as $vbroom) {
		if ($vbroom['id'] != $otar['idroomvb']) {
			continue;
		}
		$rooms_map[$otar['idroomota']] = array(
			'id'   => $vbroom['id'],
			'name' => $vbroom['name'],
		);
	}
}

// the controller will redirect to this View by setting loaded=1 if some listings were just returned
$loaded = VikRequest::getInt('loaded', 0, 'request');

// collect a list of unpublished listings
$unpublished_listings = [];

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
if ($this->retrieve_count < 10 && !$loaded) {
	// display download button
	?>
	<div class="vcm-listings-list-download">
		<a href="index.php?option=com_vikchannelmanager&task=airbnblst.download" class="btn vcm-config-btn" onclick="return vcmLoadListingDetails();"><i class="vboicn-cloud-download"></i> <?php echo JText::_('VCM_LOAD_DETAILS'); ?></a>
	</div>
	<?php
}
?>
</div>

<form action="index.php?option=com_vikchannelmanager" method="post" name="adminForm" id="adminForm">
<?php
if (is_array($this->listings_data) && count($this->listings_data)) {
	// display the listings with their basic information
	?>
	<div class="vcm-admin-container">

		<div class="vcm-config-maintab-left vcm-listings-list-left" style="display: none;">

		<?php
		foreach ($this->listings_data as $k => $listing) {
			if (!$listing->has_availability) {
				// push unpublished listing
				$unpublished_listings[] = $listing->id;
			}
			$main_photo = null;
			if (!empty($listing->_photos)) {
				if (!empty($listing->_photos[0]->extra_medium_url)) {
					$main_photo = $listing->_photos[0]->extra_medium_url;
				} elseif (!empty($listing->_photos[0]->small_url)) {
					$main_photo = $listing->_photos[0]->small_url;
				}
			}
			$address_info = array();
			if (!empty($listing->apt)) {
				array_push($address_info, $listing->apt);
			}
			if (!empty($listing->street)) {
				array_push($address_info, $listing->street);
			}
			if (!empty($listing->city)) {
				array_push($address_info, $listing->city);
			}
			if (!empty($listing->state)) {
				array_push($address_info, $listing->state);
			}
			if (!empty($listing->country_code)) {
				array_push($address_info, $listing->country_code);
			}
			$categories = array();
			if (!empty($listing->property_type_group)) {
				array_push($categories, ucwords($listing->property_type_group));
			}
			if (!empty($listing->property_type_category)) {
				array_push($categories, ucwords($listing->property_type_category));
			}
			if (!empty($listing->room_type_category)) {
				array_push($categories, ucwords($listing->room_type_category));
			}
			$capacities = array();
			if (!empty($listing->listing_currency) && !empty($listing->listing_price)) {
				// we keep these fields for BC, because the new Airbnb API Versions no longer include these details in the listing
				array_push($capacities, $listing->listing_currency . ' ' . $listing->listing_price);
			}
			if (!empty($listing->person_capacity)) {
				array_push($capacities, JText::sprintf('VCMRARRATEPERDAYLOSGUESTS', $listing->person_capacity));
			}
			if (!empty($listing->bedrooms)) {
				array_push($capacities, 'Bedrooms: ' . $listing->bedrooms);
			}
			if (!empty($listing->bathrooms)) {
				array_push($capacities, JText::_('BATHROOMS') . ': ' . $listing->bathrooms);
			}
			if (!empty($listing->beds)) {
				array_push($capacities, 'Beds: ' . $listing->beds);
			}
			?>
			<fieldset class="adminform vcm-listings-listing-wrap" data-listing-index="<?php echo $k; ?>">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php echo $listing->name; ?></legend>
					<div class="vcm-params-container">
					<?php
					if (!empty($main_photo)) {
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-setting">
								<img src="<?php echo $main_photo; ?>" class="vcm-nice-picture" />
							</div>
						</div>
						<?php
					}
					?>
						<div class="vcm-param-container">
							<div class="vcm-param-setting">
							<?php
							if (strcasecmp(($listing->quality_standards->state ?? 'good'), 'good')) {
								// quality status is not "Good"
								?>
								<span class="badge badge-warning"><?php echo JText::_('VCM_MNGLISTING_QUALITY') . ': ' . ucwords(str_replace('_', ' ', strtolower($listing->quality_standards->state))); ?></span>&nbsp;
								<?php
							}
							?>
								<span><?php echo implode(', ', $address_info); ?></span>
							</div>
						</div>
						<div class="vcm-param-container">
							<div class="vcm-param-label">
								<span class="badge badge-<?php echo !$listing->has_availability ? 'error' : 'success'; ?>"><?php echo 'ID ' . $listing->id; ?></span>
							</div>
							<div class="vcm-param-setting"><?php echo implode(', ', $categories); ?></div>
						</div>
						<div class="vcm-param-container">
						<?php
						if (isset($rooms_map[$listing->id])) {
							?>
							<div class="vcm-param-label">
								<a class="badge badge-info" href="index.php?option=com_vikbooking&task=editroom&cid[]=<?php echo $rooms_map[$listing->id]['id']; ?>" target="_blank"><?php echo $rooms_map[$listing->id]['name']; ?></a>
							</div>
							<?php
						}
						?>
							<div class="vcm-param-setting"><?php echo implode(', ', $capacities); ?></div>
						</div>
						<div class="vcm-param-container">
							<div class="vcm-param-setting">
								<a href="index.php?option=com_vikchannelmanager&task=airbnbmnglisting&idroomota=<?php echo $listing->id; ?>" class="btn vcm-config-btn"><?php VikBookingIcons::e('edit'); ?> <?php echo JText::_('EDIT'); ?></a>
							</div>
						</div>
					</div>
				</div>
			</fieldset>
			<?php
		}
		?>

		</div>

		<div class="vcm-config-maintab-right vcm-listings-list-right" style="display: none;"></div>

	</div>
	<?php
} else {
	// no listings found
	?>
	<p class="warn"><?php echo JText::_('VCM_AIRBNB_NOLIST_FOUND'); ?></p>
	<?php
}
if (VikRequest::getInt('e4j_debug', 0, 'request')) {
	echo '<pre>'.print_r($this->listings_data, true).'</pre><br/>';
}
?>
	<input type="hidden" name="task" value="" />
</form>

<?php
if ($unpublished_listings) {
	/**
	 * We display a button to allow to re-submit the unpublished status for all listings.
	 * This is useful in case of disconnection and re-connection.
	 * 
	 * @since 	1.8.11
	 */
	?>
<div class="vcm-airbnb-reunpublished-wrap">
	<h4><?php echo JText::sprintf('VCM_TOT_LISTINGS_UNPUBLISHED', count($unpublished_listings)); ?></h4>

	<form action="index.php?option=com_vikchannelmanager" method="post" name="vcm-airbnb-reunpublished" id="vcm-airbnb-reunpublished">
		<input type="hidden" name="task" value="airbnblst.retransmit_unpublished_status" />
	<?php
	foreach ($unpublished_listings as $listing_id) {
		?>
		<input type="hidden" name="listing_id[]" value="<?php echo htmlspecialchars($listing_id); ?>" />
		<?php
	}
	?>
		<button type="submit" class="btn btn-danger" onclick="return vcmRetransmitUnpublishedStatus(this);"><?php echo JText::_('VCM_RETRANSMIT_UNPUBLISHED_STATUS'); ?></button>
	</form>

	<p class="vcm-airbnb-retransmit-unpublished-help"><?php echo JText::_('VCM_RETRANSMIT_UNPUBLISHED_STATUS_HELP'); ?></p>
</div>
	<?php
}
?>

<script type="text/javascript">
	/* Loading Overlay */
	function vcmShowLoading() {
		jQuery(".vcm-loading-overlay").show();
	}

	function vcmStopLoading() {
		jQuery(".vcm-loading-overlay").hide();
	}

	function vcmLoadListingDetails() {
		vcmShowLoading();
		return true;
	}

	function vcmRetransmitUnpublishedStatus(elem) {
		vcmShowLoading();

		jQuery(elem).prop('disabled', true);

		return true;
	}

	jQuery(function() {
		var tot_listings = jQuery('.vcm-listings-listing-wrap').length;
		if (jQuery('.vcm-listings-list-left').length && tot_listings) {
			if (tot_listings === 1) {
				// just one listing to be displayed
				jQuery('.vcm-listings-list-left').fadeIn();
			} else {
				// move half listings to the right
				var move_right = Math.ceil(tot_listings / 2);
				var right_target = jQuery('.vcm-listings-list-right');
				for (var i = move_right; i < tot_listings; i++) {
					jQuery('.vcm-listings-listing-wrap[data-listing-index="' + i + '"]').appendTo(right_target);
				}
				// display both containers
				jQuery('.vcm-listings-list-left, .vcm-listings-list-right').fadeIn();
			}
		}
	});
</script>
