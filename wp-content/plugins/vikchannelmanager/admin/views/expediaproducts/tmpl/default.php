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
		if (isset($prop_params['hotelid']) && $prop_params['hotelid'] != $this->channel['params']['hotelid']) {
			// skip this room mapping as it's for a different hotel ID
			continue;
		}
	}
	$hotel_name = !empty($otar['prop_name']) && $otar['prop_name'] != $this->channel['params']['hotelid'] ? $otar['prop_name'] : $hotel_name;
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
if ($this->retrieve_count < 3 && !$loaded) {
	// display download button
	?>
	<div class="vcm-listings-list-download">
		<a href="index.php?option=com_vikchannelmanager&task=expediaproduct.download" class="btn vcm-config-btn" onclick="return vcmLoadListingDetails();"><i class="vboicn-cloud-download"></i> <?php echo JText::_('VCM_LOAD_DETAILS'); ?></a>
	</div>
	<?php
}
?>
</div>

<?php
if (is_object($this->property_data) && count(get_object_vars($this->property_data))) {
	// display property data
	?>
<div class="vcm-admin-container vcm-admin-container-full vcm-admin-container-inline">
	<div class="vcm-config-maintab-left">
		<fieldset class="adminform">
			<div class="vcm-params-wrap">
				<legend class="adminlegend"><?php VikBookingIcons::e('hotel'); ?> <?php echo $this->property_data->name . ' - ' . $this->property_data->resourceId; ?></legend>
				<div class="vcm-params-container">
					<div class="vcm-param-container">
						<div class="vcm-param-label"><?php echo JText::_('VCMMENUTACSTATUS'); ?></div>
						<div class="vcm-param-setting">
							<span class="badge badge-<?php echo !strcasecmp($this->property_data->status, 'Active') ? 'success' : 'error'; ?>"><?php echo $this->property_data->status; ?></span>
						</div>
					</div>
					<div class="vcm-param-container">
						<div class="vcm-param-label"><?php echo JText::_('VCMCONFCURNAME'); ?></div>
						<div class="vcm-param-setting">
							<span><?php echo $this->property_data->currency; ?></span>
						</div>
					</div>
					<?php
					$address_parts = [];
					foreach ($this->property_data->address as $addr_type => $addr_val) {
						if (is_scalar($addr_val) && !empty($addr_val)) {
							$address_parts[] = (string)$addr_val;
						}
					}
					?>
					<div class="vcm-param-container">
						<div class="vcm-param-label"><?php echo JText::_('VCMBCAHADDRESS'); ?></div>
						<div class="vcm-param-setting">
							<span><?php echo implode(', ', $address_parts); ?></span>
						</div>
					</div>
					<div class="vcm-param-container">
						<div class="vcm-param-label">Distribution Model</div>
						<div class="vcm-param-setting">
							<span><?php echo is_array($this->property_data->distributionModels) && count($this->property_data->distributionModels) && is_scalar($this->property_data->distributionModels[0]) ? implode(', ', $this->property_data->distributionModels) : '-----'; ?></span>
						</div>
					</div>
				<?php
				if (isset($this->property_data->rateAcquisitionType)) {
					?>
					<div class="vcm-param-container">
						<div class="vcm-param-label">Rate Acquisition Type</div>
						<div class="vcm-param-setting">
							<span><?php echo $this->property_data->rateAcquisitionType; ?></span>
						</div>
					</div>
					<?php
				}
				if (isset($this->property_data->pricingModel)) {
					?>
					<div class="vcm-param-container">
						<div class="vcm-param-label">Pricing Model</div>
						<div class="vcm-param-setting">
							<span><?php echo $this->property_data->pricingModel; ?></span>
						</div>
					</div>
					<?php
				}
				if (isset($this->property_data->taxInclusive)) {
					?>
					<div class="vcm-param-container">
						<div class="vcm-param-label"><?php echo JText::_('VCM_PRICE_COMPARE_TAX_INCL'); ?></div>
						<div class="vcm-param-setting">
							<span class="label label-<?php echo $this->property_data->taxInclusive ? 'success' : 'error'; ?>"><?php echo $this->property_data->taxInclusive ? JText::_('VCMYES') : JText::_('VCMNO'); ?></span>
						</div>
					</div>
					<?php
				}
				?>
					<div class="vcm-param-container">
						<div class="vcm-param-label">Timezone</div>
						<div class="vcm-param-setting">
							<span><?php echo $this->property_data->timezone; ?></span>
						</div>
					</div>
					<div class="vcm-param-container">
						<div class="vcm-param-label">Allowed Age Categories</div>
						<div class="vcm-param-setting">
							<span><?php echo is_array($this->property_data->allowedAgeCategories) && count($this->property_data->allowedAgeCategories) && is_scalar($this->property_data->allowedAgeCategories[0]) ? implode(', ', $this->property_data->allowedAgeCategories) : '-----'; ?></span>
						</div>
					</div>
					<div class="vcm-param-container">
						<div class="vcm-param-label">Cancellation time</div>
						<div class="vcm-param-setting">
							<span><?php echo $this->property_data->cancellationTime; ?></span>
						</div>
					</div>
				<?php
				if (isset($this->property_data->reservationCutOff) && is_object($this->property_data->reservationCutOff)) {
					$res_cut_off_parts = [];
					foreach ($this->property_data->reservationCutOff as $cutoff_key => $cutoff_val) {
						if (!is_scalar($cutoff_val)) {
							continue;
						}
						$res_cut_off_parts[] = ucfirst($cutoff_key) . ': ' . $cutoff_val;
					}
					if ($res_cut_off_parts) {
						?>
					<div class="vcm-param-container">
						<div class="vcm-param-label">Reservations cut-off time</div>
						<div class="vcm-param-setting">
							<span><?php echo implode(', ', $res_cut_off_parts); ?></span>
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
}
?>

<form action="index.php?option=com_vikchannelmanager" method="post" name="adminForm" id="adminForm">
<?php
if (is_array($this->listings_data) && count($this->listings_data)) {
	// display the listings with their basic information
	?>
	<div class="vcm-admin-container">

		<div class="vcm-config-maintab-left vcm-listings-list-left" style="display: none;">

		<?php
		$default_logo = VikChannelManager::getLogosInstance('expedia')->getSmallLogoURL();
		foreach ($this->listings_data as $k => $listing) {
			// we should not use the Expedia logo for the moment, and room-types do not include any main photo URL
			$main_photo = '';
			?>
			<fieldset class="adminform vcm-listings-listing-wrap" data-listing-index="<?php echo $k; ?>">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php echo $listing->name->value; ?></legend>
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
					if (!empty($listing->partnerCode)) {
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-setting">
								<strong><?php echo $listing->partnerCode; ?></strong>
							</div>
						</div>
						<?php
					}
					?>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMCONFCUSTAONE'); ?></div>
							<div class="vcm-param-setting">
								<span class="badge badge-info"><?php echo $listing->resourceId; ?></span>
							</div>
						</div>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMMENUTACSTATUS'); ?></div>
							<div class="vcm-param-setting">
								<span class="badge badge-<?php echo !strcasecmp($listing->status, 'Active') ? 'success' : 'error'; ?>"><?php echo $listing->status; ?></span>
							</div>
						</div>
						<?php
						$max_occ_parts = [
							JText::_('VCMBCAHIMGTAG92') . ': ' . $listing->maxOccupancy->total,
							JText::_('VCMADULTS') . ': ' . $listing->maxOccupancy->adults,
							JText::_('VCMCHILDREN') . ': ' . $listing->maxOccupancy->children,
						];
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMBCARCMAXOCCUP'); ?></div>
							<div class="vcm-param-setting">
								<span><?php echo implode(', ', $max_occ_parts); ?></span>
							</div>
						</div>
					<?php
					if (isset($listing->regulatoryRecords) && isset($listing->regulatoryRecords->category)) {
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCM_CATEGORY'); ?></div>
							<div class="vcm-param-setting">
								<span><?php echo $listing->regulatoryRecords->category; ?></span>
							</div>
						</div>
						<?php
					}
					?>
						<div class="vcm-param-container">
							<div class="vcm-param-setting">
								<a href="index.php?option=com_vikchannelmanager&view=expediamngproduct&idroomota=<?php echo $listing->resourceId; ?>" class="btn vcm-config-btn"><?php VikBookingIcons::e('edit'); ?> <?php echo JText::_('EDIT'); ?></a>
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
	<p class="warn"><?php echo JText::_('VCM_CH_NOROOMS_FOUND'); ?></p>
	<?php
}

if (VikRequest::getInt('e4j_debug', 0, 'request')) {
	echo 'property_data<pre>' . print_r($this->property_data, true) . '</pre>';
	echo 'listings_data<pre>' . print_r($this->listings_data, true) . '</pre>';
}
?>
	<input type="hidden" name="task" value="" />
</form>

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
