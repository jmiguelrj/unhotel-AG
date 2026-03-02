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

// check if the cancellation operation was successful
$cancel_success = VikRequest::getInt('cancel_success', 0, 'request');

?>
<form name="adminForm" id="adminForm" action="index.php" method="post">
	<div class="vcm-admin-container vcm-admin-container-full">
		
		<div class="vcm-config-maintab-left">
			<fieldset class="adminform">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VCM_CANCACTIVE_BOOKING_TITLE'); ?> <i class="<?php echo class_exists('VikBookingIcons') ? VikBookingIcons::i('refresh', 'fa-spin fa-fw vcm-cancactivebook-loading-hid') : ''; ?>" style="display: none;"></i></legend>
					<div class="vcm-params-container">
						<div class="vcm-param-container">
							<div class="vcm-param-setting">
								<span class="vcm-param-setting-comment"><?php echo JText::sprintf('VCM_CANCACTIVE_BOOKING_DESCR', $channel_name); ?></span>
							</div>
						</div>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMRESLOGSIDORDOTA'); ?></div>
							<div class="vcm-param-setting"><?php echo $this->reservation['idorderota']; ?></div>
						</div>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCM_AIRBNB_CANCACTIVE_REASON'); ?>*</div>
							<div class="vcm-param-setting">
								<select name="canc_reason" id="vcm-canc-reason" onBlur="checkRequiredField('vcm-canc-reason');">
									<option value=""></option>
									<option value="DECLINE_REASON_HOST_DOUBLE"><?php echo JText::_('VCM_DECL_REASON_DATES_NOT_AVAILABLE'); ?></option>
									<option value="DECLINE_REASON_HOST_CHANGE"><?php echo JText::_('VCM_DECL_REASON_NEEDCHANGE'); ?></option>
									<option value="DECLINE_REASON_HOST_BAD_REVIEWS_SPARSE_PROFILE"><?php echo JText::_('VCM_DECL_REASON_BADREV_SPARSEPROF'); ?></option>
									<option value="DECLINE_REASON_HOST_UNAUTHORIZED_PARTY"><?php echo JText::_('VCM_DECL_REASON_UNAUTHORIZED_PARTY'); ?></option>
									<option value="DECLINE_REASON_HOST_BEHAVIOR"><?php echo JText::_('VCM_DECL_REASON_BEHAVIOR'); ?></option>
									<option value="DECLINE_REASON_HOST_BAD_FIT"><?php echo JText::_('VCM_DECL_REASON_NOT_A_GOOD_FIT'); ?></option>
									<option value="DECLINE_REASON_HOST_ASKED"><?php echo JText::_('VCM_DECL_REASON_ASKED'); ?></option>
									<option value="DECLINE_REASON_HOST_OTHER"><?php echo JText::_('VCM_DECL_REASON_OTHER'); ?></option>
								</select>
								<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_AIRBNB_CANCACTIVE_REASON_DESCR'); ?></span>
							</div>
						</div>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCM_AIRBNB_DECLINE_GUESTMESS'); ?>*</div>
							<div class="vcm-param-setting">
								<textarea name="canc_guest_mess" id="vcm-canc-guest-mess" rows="4" cols="30" onBlur="checkRequiredField('vcm-canc-guest-mess');"></textarea>
								<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_AIRBNB_CANCACTIVE_GUESTMESS_DESCR'); ?></span>
							</div>
						</div>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCM_AIRBNB_DECLINE_AIRBNBMESS'); ?></div>
							<div class="vcm-param-setting">
								<textarea name="canc_ota_mess" id="vcm-canc-ota-mess" rows="4" cols="30"></textarea>
								<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_AIRBNB_DECLINE_AIRBNBMESS_DESCR'); ?></span>
							</div>
						</div>
					<?php
					if ($rq_tmpl == 'component') {
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-label"></div>
							<div class="vcm-param-setting">
								<button type="button" class="btn btn-danger" onclick="vcmHandleSubmitOperation(this);"><?php echo JText::_('VCM_CANCACTIVE_BOOKING_TITLE'); ?></button>
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
	
	<input type="hidden" name="task" value="doCancelReservation"/>
	<input type="hidden" name="option" value="com_vikchannelmanager" />
	<input type="hidden" name="vbo_oid" value="<?php echo $this->reservation['id']; ?>" />
<?php
if (!empty($rq_tmpl)) {
	?>
	<input type="hidden" name="tmpl" value="<?php echo $rq_tmpl; ?>" />
	<?php
}
?>
</form>

<a href="index.php?option=com_vikbooking&task=editorder&cid[]=<?php echo $this->reservation['id']; ?>" class="vcm-placeholder-backlink" style="display: none;"></a>
<a href="index.php?option=com_vikbooking&task=removeorders&cid[]=<?php echo $this->reservation['id']; ?>&goto=<?php echo $this->reservation['id']; ?>" class="vcm-placeholder-removelink" style="display: none;"></a>

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
			jQuery('.vcm-cancactivebook-loading-hid').show();
		}

		var nav_fallback = jQuery('.vcm-placeholder-backlink').first().attr('href');
		var remove_fallback = jQuery('.vcm-placeholder-removelink').first().attr('href');
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
			// navigate to the task to remove the booking
			if (needs_parent) {
				window.parent.location.href = remove_fallback;
			} else {
				window.location.href = remove_fallback;
			}
		}
	}

	function vcmHandleSubmitOperation(elem) {
		if (confirm('<?php echo addslashes(JText::_('VCMREMOVECONFIRM')); ?>')) {
			jQuery(elem).prop('disabled', true);
			jQuery('.vcm-cancactivebook-loading-hid').show();
			jQuery(elem).closest('form').submit();
		}
	}

	jQuery(document).ready(function() {
		<?php
		if ($cancel_success) {
			// dismiss modal or redirect when page loads, if process completed successfully
			echo 'vcmHandleCancelOperation(true)';
		}
		?>
	});
	
</script>
