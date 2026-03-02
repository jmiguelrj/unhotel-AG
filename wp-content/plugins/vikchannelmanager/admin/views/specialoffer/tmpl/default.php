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

// override channel name, if necessary
$channel_name = $this->channel['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI ? 'Airbnb' : $this->channel['name'];

// no template (modal) request
$rq_tmpl = VikRequest::getString('tmpl', '', 'request');

// check if the operation was successful
$success = VikRequest::getInt('success', 0, 'request');

// build values for the request
$start_date  = date('Y-m-d', $this->reservation['checkin']);
$end_date  = date('Y-m-d', $this->reservation['checkout']);
$total_price = ($this->reservation['total'] - $this->reservation['tot_taxes'] - $this->reservation['tot_city_taxes']);

?>
<form name="adminForm" id="adminForm" action="index.php" method="post">
	<div class="vcm-admin-container vcm-admin-container-full">
		
		<div class="vcm-config-maintab-left">
			<fieldset class="adminform">
				<div class="vcm-params-wrap">
					<legend class="adminlegend">
						<span><?php echo JText::sprintf('VCM_SEND_SPECOFFER_TITLE', ($this->customer['first_name'] ?? 'Guest')); ?></span> 
						<i class="<?php echo class_exists('VikBookingIcons') ? VikBookingIcons::i('refresh', 'fa-spin fa-fw vcm-specialoffer-loading-hid') : ''; ?>" style="display: none;"></i>
					</legend>
					<div class="vcm-params-container">
						<div class="vcm-param-container">
							<div class="vcm-param-setting">
								<img src="<?php echo VikChannelManager::getLogosInstance($this->channel['name'])->getTinyLogoURL(); ?>" />
								<span class="vcm-param-setting-comment"><?php echo JText::sprintf('VCM_SEND_SPECOFFER_DESCR', $channel_name, ($this->customer['first_name'] ?? 'Guest')); ?></span>
							</div>
						</div>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMPVIEWORDERSVBTHREE'); ?></div>
							<div class="vcm-param-setting">
								<?php echo $this->room_info['name']; ?>
							<?php
							if (!empty($this->room_info['img'])) {
								?>
								<div>
									<img src="<?php echo VBO_SITE_URI . 'resources/uploads/' . $this->room_info['img']; ?>" class="vcm-nice-picture" />
								</div>
								<?php
							}
							?>
							</div>
						</div>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMPVIEWORDERSVBFOUR'); ?></div>
							<div class="vcm-param-setting"><?php echo $start_date; ?></div>
						</div>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMPVIEWORDERSVBFIVE'); ?></div>
							<div class="vcm-param-setting">
								<?php echo $end_date; ?>
								<span class="vcm-param-setting-comment"><?php echo JText::_('VCMPVIEWORDERSVBSIX') . ': ' . $this->reservation['days']; ?></span>
							</div>
						</div>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMBCAHIMGTAG92'); ?></div>
							<div class="vcm-param-setting"><?php echo JText::_('VCMADULTS') . ': ' . $this->booking_room['adults'] . ', ' . JText::_('VCMCHILDREN') . ': ' . $this->booking_room['children']; ?></div>
						</div>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMPVIEWORDERSVBSEVEN'); ?></div>
							<div class="vcm-param-setting">
								<?php echo VikBooking::getCurrencySymb() . ' ' . VikBooking::numberFormat($total_price); ?>
								<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_TOTAL_BEFORE_TAX_SP'); ?></span>
							</div>
						</div>
					<?php
					if ($rq_tmpl == 'component') {
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-label"></div>
							<div class="vcm-param-setting">
								<button type="button" class="btn btn-success" onclick="vcmHandleSubmitOperation(this);"><?php echo JText::_('VCM_SEND_SPECIAL_OFFER'); ?></button>
								<button type="button" class="btn" onclick="vcmHandleCancelOperation(false);"><?php echo JText::_('BACK'); ?></button>
							</div>
						</div>
						<?php
					}
					?>
					</div>
				</div>
			</fieldset>
		</div>

	</div>
	
	<input type="hidden" name="task" value="sendSpecialOffer"/>
	<input type="hidden" name="option" value="com_vikchannelmanager" />
	<input type="hidden" name="vbo_oid" value="<?php echo $this->reservation['id']; ?>" />
	<input type="hidden" name="ota_thread_id" value="<?php echo $this->ota_thread_id; ?>" />
	<input type="hidden" name="listing_id" value="<?php echo $this->listing_id; ?>" />
	<input type="hidden" name="start_date" value="<?php echo $start_date; ?>" />
	<input type="hidden" name="nights" value="<?php echo $this->reservation['days']; ?>" />
	<input type="hidden" name="adults" value="<?php echo $this->booking_room['adults']; ?>" />
	<input type="hidden" name="children" value="<?php echo $this->booking_room['children']; ?>" />
	<input type="hidden" name="total_price" value="<?php echo $total_price; ?>" />
	<input type="hidden" name="ch_uniquekey" value="<?php echo $this->channel['uniquekey']; ?>" />
<?php
if (!empty($rq_tmpl)) {
	?>
	<input type="hidden" name="tmpl" value="<?php echo $rq_tmpl; ?>" />
	<?php
}
?>
</form>

<a href="index.php?option=com_vikbooking&task=editorder&cid[]=<?php echo $this->reservation['id']; ?>#bookhistory" class="vcm-placeholder-backlink" style="display: none;"></a>

<script type="text/javascript">
	
	function checkRequiredField(id) {
		var elem = jQuery('#'+id);
		if (!elem.length) {
			return;
		}
		var lbl = elem.closest('.vcm-param-container').find('.vcm-param-label');
		if (!lbl.length) {
			return;
		}
		if (elem.val().length) {
			lbl.removeClass('vcm-param-label-isrequired');
			return true;
		}
		lbl.addClass('vcm-param-label-isrequired');
		return false;
	}

	/**
	 * If we are inside a modal, the modal will be dismissed, otherwise
	 * we redirect the user to the booking details page in VikBooking.
	 */
	function vcmHandleCancelOperation(refresh) {
		if (refresh) {
			jQuery('.vcm-params-container').hide();
			jQuery('.vcm-specialoffer-loading-hid').show();
		}

		var nav_fallback = jQuery('.vcm-placeholder-backlink').first().attr('href');
		var modal = jQuery('.modal[id*="vbo"]');
		var needs_parent = false;
		if (!modal.length) {
			// check if we are in a iFrame and so the element we want is inside the parent
			modal = jQuery('.modal[id*="vbo"]', parent.document);
			if (modal.length) {
				needs_parent = true;
			}
		}
		if (!modal.length) {
			// we are probably not inside a modal, so navigate
			window.location.href = nav_fallback;
			return;
		}
		
		// try to dismiss the modal
		try {
			modal.modal('hide');
		} catch(e) {
			// dismissing did not succeed
		}
		
		if (refresh) {
			// navigate to refresh the page
			if (needs_parent) {
				window.parent.location.href = nav_fallback;
			} else {
				window.location.href = nav_fallback;
			}
		}
	}

	function vcmHandleSubmitOperation(elem) {
		jQuery(elem).prop('disabled', true);
		jQuery('.vcm-specialoffer-loading-hid').show();
		jQuery(elem).closest('form').submit();
	}

	jQuery(function() {
		<?php
		if ($success) {
			// dismiss modal or redirect when page loads, if process completed successfully
			echo 'vcmHandleCancelOperation(true)';
		}
		?>
	});
	
</script>
