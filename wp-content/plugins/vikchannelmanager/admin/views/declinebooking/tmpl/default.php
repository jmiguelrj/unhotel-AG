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

// check if the decline operation was successful
$decline_success = VikRequest::getInt('decline_success', 0, 'request');

?>
<form name="adminForm" id="adminForm" action="index.php" method="post">
	<div class="vcm-admin-container vcm-admin-container-full">
		
		<div class="vcm-config-maintab-left">
			<fieldset class="adminform">
				<div class="vcm-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VCM_DECLINE_BOOKING_TITLE'); ?> <i class="<?php echo class_exists('VikBookingIcons') ? VikBookingIcons::i('refresh', 'fa-spin fa-fw vcm-declinebook-loading-hid') : ''; ?>" style="display: none;"></i></legend>
					<div class="vcm-params-container">
						<div class="vcm-param-container">
							<div class="vcm-param-setting">
								<span class="vcm-param-setting-comment"><?php echo JText::sprintf('VCM_DECLINE_BOOKING_DESCR', $channel_name); ?></span>
							</div>
						</div>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMRESLOGSIDORDOTA'); ?></div>
							<div class="vcm-param-setting"><?php echo $this->reservation['idorderota']; ?></div>
						</div>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCM_AIRBNB_DECLINE_REASON'); ?>*</div>
							<div class="vcm-param-setting">
								<select name="decline_reason" id="vcm-decline-reason" onBlur="checkRequiredField('vcm-decline-reason');">
									<option value=""></option>
									<option value="dates_not_available"><?php echo JText::_('VCM_DECL_REASON_DATES_NOT_AVAILABLE'); ?></option>
									<option value="not_a_good_fit"><?php echo JText::_('VCM_DECL_REASON_NOT_A_GOOD_FIT'); ?></option>
									<option value="waiting_for_better_reservation"><?php echo JText::_('VCM_DECL_REASON_WAITING_FOR_BETTER_RESERVATION'); ?></option>
									<option value="not_comfortable"><?php echo JText::_('VCM_DECL_REASON_NOT_COMFORTABLE'); ?></option>
								</select>
								<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_AIRBNB_DECLINE_REASON_DESCR'); ?></span>
							</div>
						</div>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCM_AIRBNB_DECLINE_GUESTMESS'); ?>*</div>
							<div class="vcm-param-setting">
								<textarea name="decline_guest_mess" id="vcm-decline-guest-mess" rows="4" cols="30" onBlur="checkRequiredField('vcm-decline-guest-mess');"></textarea>
								<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_AIRBNB_DECLINE_GUESTMESS_DESCR'); ?></span>
							</div>
						</div>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCM_AIRBNB_DECLINE_AIRBNBMESS'); ?></div>
							<div class="vcm-param-setting">
								<textarea name="decline_ota_mess" id="vcm-decline-ota-mess" rows="4" cols="30"></textarea>
								<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_AIRBNB_DECLINE_AIRBNBMESS_DESCR'); ?></span>
							</div>
						</div>
					<?php
					if ($rq_tmpl == 'component') {
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-label"></div>
							<div class="vcm-param-setting">
								<button type="button" class="btn btn-danger" onclick="vcmHandleSubmitOperation(this);"><?php echo JText::_('VCM_DECLINE_BOOKING_TITLE'); ?></button>
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
	
	<input type="hidden" name="task" value="doDeclineBooking"/>
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
			jQuery('.vcm-declinebook-loading-hid').show();
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
		if (confirm('<?php echo addslashes(JText::_('VCMREMOVECONFIRM')); ?>')) {
			jQuery(elem).prop('disabled', true);
			jQuery('.vcm-declinebook-loading-hid').show();
			jQuery(elem).closest('form').submit();
		}
	}

	jQuery(document).ready(function() {
		<?php
		if ($decline_success) {
			// dismiss modal or redirect when page loads, if process completed successfully
			echo 'vcmHandleCancelOperation(true)';
		}
		?>
	});
	
</script>
