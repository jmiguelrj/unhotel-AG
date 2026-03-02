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

if (empty($this->channel['params']) || empty($this->channel['params']['hotelid'])) {
	$this->channel['params']['hotelid'] = '???';
}

// show the PM name
$pm_name = VCMFactory::getConfig()->get('account_name_' . VikChannelManagerConfig::VRBOAPI, '');

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
	<h3><?php echo JText::_('VCMACCOUNTCHANNELID') . ' ' . $this->channel['params']['hotelid'] . (!empty($pm_name) ? ' - ' . $pm_name : ''); ?></h3>
<?php
if (!$loaded) {
	// display download button
	?>
	<div class="vcm-listings-list-download">
		<a href="index.php?option=com_vikchannelmanager&task=vrbolst.generate" class="btn vcm-config-btn" onclick="return vcmLoadListingDetails();"><i class="vboicn-cloud-download"></i> <?php echo JText::_('VCM_VRBO_GEN_FROM_WEBSITE'); ?></a>
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
			$main_photo = '';
			if (!empty($listing->main_photo)) {
				$main_photo = $listing->main_photo;
			}
			$content_validated_info = VCMVrboListing::contentValidationPass($listing);
			$is_content_validated = ($content_validated_info[0] === true);
			$content_validation_err = !$is_content_validated ? $content_validated_info[1] : '';
			?>
			<fieldset class="adminform vcm-listings-listing-wrap <?php echo $is_content_validated ? 'vcm-listing-content-valid' : 'vcm-listing-content-invalid'; ?>" data-listing-index="<?php echo $k; ?>">
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
							<div class="vcm-param-label"><?php echo JText::_('VCMCONFCUSTAONE'); ?></div>
							<div class="vcm-param-setting">
								<span class="badge badge-info"><?php echo $listing->id; ?></span>
							</div>
						</div>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMMENUTACSTATUS'); ?></div>
							<div class="vcm-param-setting">
								<span class="badge badge-<?php echo $listing->active ? 'success' : 'error'; ?>"><?php echo $listing->active ? JText::_('VCMTACROOMPUBLISHED') : JText::_('VCMTACROOMUNPUBLISHED'); ?></span>
							</div>
						</div>
					<?php
					if ($content_validation_err && !$is_content_validated) {
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-setting">
								<p class="err"><?php echo $content_validation_err; ?></p>
							</div>
						</div>
						<?php
					} else {
						$listing_mapped = isset($this->otalistings[$listing->id]);
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCM_MAPPING_STATUS'); ?></div>
							<div class="vcm-param-setting">
								<span class="badge badge-<?php echo $listing_mapped ? 'success' : 'error'; ?>"><?php echo $listing_mapped ? JText::_('VCM_LISTING_SYNCED') : JText::_('VCM_LISTING_NOT_SYNCED'); ?></span>
							<?php
							if (!$listing_mapped) {
								?>
								<span class="vcm-param-setting-comment"><a href="index.php?option=com_vikchannelmanager&task=roomsynch" target="_blank"><?php VikBookingIcons::e('link'); ?> <?php echo JText::_('VCMMENUEXPSYNCH'); ?></a></span>
								<?php
							}
							?>
							</div>
						</div>
						<?php
					}
					?>
						<div class="vcm-param-container">
							<div class="vcm-param-setting">
								<a href="index.php?option=com_vikchannelmanager&view=vrbomnglisting&idroomota=<?php echo $listing->id; ?>" class="btn vcm-config-btn"><?php VikBookingIcons::e('edit'); ?> <?php echo JText::_('EDIT'); ?></a>
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
	<p class="warn"><?php echo JText::_('VCM_VRBO_NO_LISTINGS'); ?></p>
	<?php
}

if (VikRequest::getInt('e4j_debug', 0, 'request')) {
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
				// list eligible listings on top
				var left_target = jQuery('.vcm-listings-list-left');
				jQuery('.vcm-listings-list-left').find('.vcm-listing-content-valid').each(function() {
					jQuery(this).prependTo(left_target);
				});
				jQuery('.vcm-listings-list-right').find('.vcm-listing-content-valid').each(function() {
					jQuery(this).prependTo(right_target);
				});
				// display both containers
				jQuery('.vcm-listings-list-left, .vcm-listings-list-right').fadeIn();
			}
		}
	});
</script>
