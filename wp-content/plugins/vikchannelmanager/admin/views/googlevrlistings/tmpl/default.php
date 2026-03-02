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

if (empty($this->channel['params']['hotelid'])) {
	$this->channel['params']['hotelid'] = VCMFactory::getConfig()->get('account_key_' . VikChannelManagerConfig::GOOGLEVR, '');
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

// list of eligible listing IDs to perform the transaction property data
$tn_prop_data_valid_listings = [];

?>

<div class="vcm-loading-overlay">
	<div class="vcm-loading-dot vcm-loading-dot1"></div>
	<div class="vcm-loading-dot vcm-loading-dot2"></div>
	<div class="vcm-loading-dot vcm-loading-dot3"></div>
	<div class="vcm-loading-dot vcm-loading-dot4"></div>
	<div class="vcm-loading-dot vcm-loading-dot5"></div>
</div>

<div class="vcm-listings-list-head">
	<h3><?php echo JText::_('VCMACCOUNTCHANNELID') . ' ' . ($this->channel['params']['hotelid'] ?: '???'); ?></h3>
<?php
if (!$loaded) {
	// display download button
	?>
	<div class="vcm-listings-list-download">
		<a href="<?php echo VCMFactory::getPlatform()->getUri()->addCSRF('index.php?option=com_vikchannelmanager&task=googlevrlst.generate', true); ?>" class="btn vcm-config-btn" onclick="return vcmLoadListingDetails();"><i class="vboicn-cloud-download"></i> <?php echo JText::_('VCM_VRBO_GEN_FROM_WEBSITE'); ?></a>
	</div>
	<?php
}
?>
</div>

<form action="index.php?option=com_vikchannelmanager" method="post" name="adminForm" id="adminForm">
<?php
if ($this->listings_data) {
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
							<div class="vcm-param-label"><?php echo JText::_('VCMCONFCUSTAONE'); ?></div>
							<div class="vcm-param-setting">
								<span class="badge badge-info"><?php echo $listing->id; ?></span>
							</div>
						</div>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMMENUTACSTATUS'); ?></div>
							<div class="vcm-param-setting">
							<?php
							if (!($listing->on_server ?? false)) {
								?>
								<span class="badge badge-warning"><?php VikBookingIcons::e('exclamation-triangle'); ?> Review details</span>
								<?php
							} else {
								?>
								<span class="badge badge-<?php echo $listing->active ? 'success' : 'error'; ?>"><?php echo $listing->active ? JText::_('VCMTACROOMPUBLISHED') : JText::_('VCMTACROOMUNPUBLISHED'); ?></span>
								<?php
							}
							?>
							</div>
						</div>
						<?php
						$listing_mapped = isset($this->otalistings[$listing->id]);
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCM_MAPPING_STATUS'); ?></div>
							<div class="vcm-param-setting">
								<span class="badge badge-<?php echo $listing_mapped ? 'success' : 'error'; ?>"><?php echo $listing_mapped ? JText::_('VCM_LISTING_SYNCED') : JText::_('VCM_LISTING_NOT_SYNCED'); ?></span>
							</div>
						</div>
						<div class="vcm-param-container">
							<div class="vcm-param-label">
								<a href="index.php?option=com_vikchannelmanager&view=googlevrmnglisting&idroomota=<?php echo $listing->id; ?>" class="btn vcm-config-btn"><?php VikBookingIcons::e('edit'); ?> <?php echo JText::_('EDIT'); ?></a>
							</div>
						<?php
						if ($listing_mapped) {
							// allow to perform the transaction property data
							$tn_btn_class = 'btn-primary';
							$tn_btn_text = JText::_('VCM_GOOGLEVR_UPDATE_DATA');
							$tn_btn_help = JText::_('VCM_GOOGLEVR_UPDATE_DATA_HELP');
							if (!($listing->transactioned_on ?? null)) {
								$tn_btn_class = 'btn-success';
								$tn_btn_text = JText::_('VCM_GOOGLEVR_SEND_DATA');
								$tn_btn_help = JText::sprintf('VCM_GOOGLEVR_WAIT', $listing->created_on ?? '????');
							}
							// push eligible listing ID to perform the transaction property data
							$tn_prop_data_valid_listings[] = $listing->id;
							?>
							<div class="vcm-param-setting">
								<a href="<?php echo VCMFactory::getPlatform()->getUri()->addCSRF('index.php?option=com_vikchannelmanager&task=googlevrlst.sendTransaction&listing_id=' . $listing->id, true); ?>" class="btn <?php echo $tn_btn_class; ?>"><?php VikBookingIcons::e('rocket'); ?> <?php echo $tn_btn_text; ?></a>
								<span class="vcm-param-setting-comment"><?php echo $tn_btn_help; ?></span>
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

		<div class="vcm-config-maintab-right vcm-listings-list-right" style="display: none;"></div>

	</div>
	<?php
} else {
	// no listings found
	?>
	<p class="warn"><?php echo JText::_('VCM_VRBO_NO_LISTINGS'); ?></p>
	<?php
}
?>
	<input type="hidden" name="task" value="" />
</form>

<?php
/**
 * In order to ease the process for sending to Google the listing transaction property data, we display
 * a button for scheduling the operation for all listings via AJAX. Useful in case of many listings.
 * 
 * @since 	1.9.13
 */
if (count($tn_prop_data_valid_listings) > 1) {
?>
<div class="vcm-airbnb-reunpublished-wrap vcm-googlevr-tnpropdata-wrap">
	<h4><?php echo JText::_('VCM_GOOGLEVR_UPDATE_DATA'); ?></h4>
	<p><?php echo JText::_('VCM_GOOGLEVR_UPDATE_DATA_HELP'); ?></p>
	<button type="button" class="btn btn-primary vcm-googlevr-bulk-tnpropdata"><?php VikBookingIcons::e('rocket'); ?> <?php echo JText::_('VCM_GOOGLEVR_SEND_DATA') . ' (' . JText::_('VCM_ALL_LISTINGS') . ')'; ?></button>
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

	/**
	 * @var 	array
	 */
	const tn_prop_data_valid_listings = <?php echo json_encode($tn_prop_data_valid_listings); ?>;

	/**
     * Register function to process a list of transaction property data operations for the eligible Google VR listings.
     * 
     * @param   array       requests    List of involved listing IDs to process.
     * @param   function    onProgress  Optional callback when a request is completed.
     * @param   function    onComplete  Optional callback when all requests have been completed.
     * @param   function    onError     Optional callback in case of request error.
     * 
     * @return  void
     */
	const vcmGoogleVrDispatchTnPropDataRequests = (requests, onProgress, onComplete, onError) => {
		if (!Array.isArray(requests) || !requests.length) {
            if (typeof onComplete === 'function') {
                onComplete();
            }

            // abort
            return;
        }

        // obtain the listing ID to process
        const listingId = requests.shift();

        // perform the request
        VBOCore.doAjax(
            "<?php echo VikChannelManager::ajaxUrl('index.php?option=com_vikchannelmanager&task=googlevrlst.sendTransaction'); ?>",
            {
            	listing_id: listingId,
            	is_ajax: 1,
            },
            (res) => {
                try {
                    // check the response
                    let obj_res = typeof res === 'string' ? JSON.parse(res) : res;

                    if (typeof onProgress === 'function') {
                        // call the given function by passing the operation result object and the listing ID
                        onProgress(obj_res, listingId);
                    }

                    // recursively call the same function to process the next request
                    vcmGoogleVrDispatchTnPropDataRequests(requests, onProgress, onComplete, onError);
                } catch(err) {
                    // log error
                    console.error(err);

                    if (typeof onError === 'function') {
                        // unrecoverable error
                        onError(err, true);
                    }

                    // recursively call the same function to process the next request, if any
                    vcmGoogleVrDispatchTnPropDataRequests(requests, onProgress, onComplete, onError);
                }
            },
            (err) => {
                // display error
                let err_mess = err.responseText || 'Request failed due to connection error';
                console.error(err_mess);

                if (typeof onError === 'function') {
                    // connection error
                    onError(err_mess, false);
                }

                // recursively call the same function to process the next request, if any
                vcmGoogleVrDispatchTnPropDataRequests(requests, onProgress, onComplete, onError);
            }
        );
	};

	jQuery(function() {
		// display listings properly
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

		// bulk transaction property data
		document.querySelector('.vcm-googlevr-bulk-tnpropdata').addEventListener('click', () => {
			if (!tn_prop_data_valid_listings.length || !confirm(<?php echo json_encode(JText::_('VCM_GOOGLEVR_SEND_DATA') . '?'); ?>)) {
				return false;
			}

			// start loading animation
			vcmShowLoading();

			// dispatch the update requests
			vcmGoogleVrDispatchTnPropDataRequests(
				tn_prop_data_valid_listings,
				(obj_res, listingId) => {
					// on progress
					console.info('vcmGoogleVrDispatchTnPropDataRequests on progress', obj_res, listingId);
				},
				() => {
					// process completed, stop loading animation and reload the page
					vcmStopLoading();
					window.location.reload();
				},
				(err_mess, unrecoverable) => {
					// on error
					alert(err_mess);
				}
			);
		});
	});
</script>
