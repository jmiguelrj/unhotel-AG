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

if (VikRequest::getInt('e4j_debug', 0, 'request')) {
	echo 'Debug<pre>' . print_r($this->config_data, true) . '</pre>';
}

$prop_name = !empty($this->config_data['Hotel']['name']) ? $this->config_data['Hotel']['name'] : (!empty($this->config_data['Hotel']['id']) ? $this->config_data['Hotel']['id'] : '');
if (!empty($prop_name) && $this->config_data['Hotel']['id'] != $prop_name) {
	$prop_name .= " ({$this->config_data['Hotel']['id']})";
}

$room_rplans = array();
foreach ($this->config_data['Rooms'] as $otaroom) {
	if (!isset($otaroom['rplans']) || !count($otaroom['rplans'])) {
		continue;
	}
	foreach ($otaroom['rplans'] as $rplanid) {
		if (!in_array($rplanid, $room_rplans)) {
			array_push($room_rplans, $rplanid);
		}
	}
}

?>
<form name="adminForm" action="index.php" method="post" id="adminForm">
	<input type="hidden" name="task" value="">
	<input type="hidden" name="option" value="com_vikchannelmanager">
</form>

<div class="vcm-loading-overlay">
	<div class="vcm-loading-dot vcm-loading-dot1"></div>
	<div class="vcm-loading-dot vcm-loading-dot2"></div>
	<div class="vcm-loading-dot vcm-loading-dot3"></div>
	<div class="vcm-loading-dot vcm-loading-dot4"></div>
	<div class="vcm-loading-dot vcm-loading-dot5"></div>
</div>

<div class="vcm-cfgimpwizard-wrap">
	<div class="vcm-cfgimpwizard-inner">
		<h3><?php echo JText::sprintf('VCMCFGIMPWIZPROP', $prop_name); ?></h3>
		<div class="vcm-dotslider-wrap">
			<div class="vcm-dotslider-modal">
				<div class="vcm-dotslider-modal-inner">
					<div class="vcm-dotslider" data-pos="0">
						<div class="vcm-dotslider-slides">
							<div class="vcm-dotslider-slide" data-step="0">
								<h4><?php echo JText::_('VCMCFGIMPWIZROOMS'); ?></h4>
								<div class="vcm-cfgimpwizard-roomtypes">
									<div class="vcm-cfgimpwizard-roomtypes-inner">
									<?php
									foreach ($this->config_data['Rooms'] as $otarid => $otaroom) {
										$photo_url = isset($this->config_data['Photos'][$otarid]) && count($this->config_data['Photos'][$otarid]) && !empty($this->config_data['Photos'][$otarid][0]['url']) ? $this->config_data['Photos'][$otarid][0]['url'] : '';
										?>
										<div class="vcm-cfgimpwizard-room-wrap">
											<div class="vcm-cfgimpwizard-room-wrap-inner">
												<div class="vcm-cfgimpwizard-room-thumb">
												<?php
												if (!empty($photo_url)) {
													?>
													<img src="<?php echo $photo_url; ?>" alt="<?php echo $this->config_data['Photos'][$otarid][0]['name']; ?>" />
													<?php
												} else {
													VikBookingIcons::e('image');
												}
												?>
												</div>
												<div class="vcm-cfgimpwizard-room-info">
													<div class="vcm-cfgimpwizard-room-name">
														<span><?php echo $otaroom['name']; ?></span>
													</div>
													<div class="vcm-cfgimpwizard-room-people">
														<span><?php VikBookingIcons::e('male'); ?> <?php echo $otaroom['max_adults']; ?></span>
														<span><?php VikBookingIcons::e('child'); ?> <?php echo $otaroom['max_children']; ?></span>
													</div>
													<div class="vcm-cfgimpwizard-import-toggle">
														<span class="vcm-cfgimpwizard-room-ckbox"><?php echo VikBooking::getVboApplication()->printYesNoButtons("rooms[{$otarid}]", JText::_('VCMYES'), JText::_('VCMNO'), $otarid, $otarid, 0); ?></span>
													</div>
												</div>
											</div>
										</div>
										<?php
									}
									?>
									</div>
									<div class="vcm-cfgimpwizard-import-box">
										<button type="button" class="btn btn-large btn-success" onclick="vcmCfgImpDoStep(this);"><?php echo JText::_('VCMCFGIMPWIZNEXTSTEP'); ?></button>
									</div>
								</div>
							</div>
							<div class="vcm-dotslider-slide" data-step="1">
								<h4><?php echo JText::_('VCMCFGIMPWIZRPLANS'); ?></h4>
								<div class="vcm-cfgimpwizard-rateplans">
									<div class="vcm-cfgimpwizard-rateplans-inner">
									<?php
									foreach ($this->config_data['Rplans'] as $otarplanid => $otarplan) {
										if (!in_array($otarplanid, $room_rplans)) {
											// this rate plan is not used by any room type
											continue;
										}
										$def_imp_status = $otarplanid;
										if (stripos($otarplan['name'], 'standard') === false && stripos($otarplan['name'], 'refundable') === false) {
											// common rate plan names are "Standard Rate" and "Non Refundable Rate", the others should be left as not needed for import
											$def_imp_status = 0;
										}
										?>
										<div class="vcm-cfgimpwizard-rplan-wrap">
											<div class="vcm-cfgimpwizard-rplan-wrap-inner">
												<div class="vcm-cfgimpwizard-rplan-name">
													<span><?php echo ucwords($otarplan['name']); ?></span>
												</div>
											<?php
											if (!empty($otarplan['policy'])) {
												?>
												<div class="vcm-cfgimpwizard-rplan-policy">
													<span><?php echo ucwords($otarplan['policy']); ?></span>
												</div>
												<?php
											}
											?>
												<div class="vcm-cfgimpwizard-rplan-cost">
													<label for="dcpn<?php echo $otarplanid; ?>"><?php echo JText::_('VCMRATESPUSHPERNIGHT'); ?></label>
													<input type="number" name="dcpn[<?php echo $otarplanid; ?>]" id="dcpn<?php echo $otarplanid; ?>" value="<?php echo $otarplan['cost']; ?>" />
												</div>
												<div class="vcm-cfgimpwizard-import-toggle">
													<span class="vcm-cfgimpwizard-rplan-ckbox"><?php echo VikBooking::getVboApplication()->printYesNoButtons("rplans[{$otarplanid}]", JText::_('VCMYES'), JText::_('VCMNO'), $def_imp_status, $otarplanid, 0); ?></span>
												</div>
											</div>
										</div>
										<?php
									}
									?>
									</div>
									<div class="vcm-cfgimpwizard-import-box">
										<button type="button" class="btn btn-large btn-success" onclick="vcmCfgImpDoStep(this);"><?php echo JText::_('VCMCFGIMPWIZNEXTSTEP'); ?></button>
									</div>
								</div>
							</div>
							<div class="vcm-dotslider-slide" data-step="2">
								<h4><?php echo JText::_('VCMCFGIMPWIZRPHOTOS'); ?></h4>
								<div class="vcm-cfgimpwizard-photos">
									<div class="vcm-cfgimpwizard-photos-inner">
									<?php
									if (count($this->config_data['Photos']) < 2) {
										// property gallery and room gallery must be at least present
										?>
										<p class="warn"><?php echo JText::_('VCMBPHOTONOPFOUNDING'); ?></p>
										<?php
									} else {
										// count all photos in all room galleries
										$tot_photos = 0;
										foreach ($this->config_data['Photos'] as $prop => $prop_photos) {
											if ($prop == 'property') {
												continue;
											}
											$tot_photos += count($prop_photos);
										}
										?>
										<div class="vcm-cfgimpwizard-import-toggle">
											<label for="downphotos-on"><?php echo JText::sprintf('VCMCFGIMPWIZDWNEXTRAPHOTOS', $tot_photos); ?></label>
											<span class="vcm-cfgimpwizard-downphotos-ckbox"><?php echo VikBooking::getVboApplication()->printYesNoButtons('downphotos', JText::_('VCMYES'), JText::_('VCMNO'), 1, 1, 0); ?></span>
										</div>
										<?php
									}
									?>
									</div>
									<div class="vcm-cfgimpwizard-import-box">
										<button type="button" class="btn btn-large btn-success" onclick="vcmCfgImpDoStep(this);"><?php echo JText::_('VCMCFGIMPWIZNEXTSTEP'); ?></button>
									</div>
								</div>
							</div>
							<div class="vcm-dotslider-slide" data-step="3">
								<?php
								$active_bookings = 0;
								$loop_bookings = array();
								if (count($this->config_data['Bookings'])) {
									// bookings can be delivered in two different formats ("PND" and "List")
									if (isset($this->config_data['Bookings']['pnd'])) {
										$active_bookings = count($this->config_data['Bookings']['pnd']);
										$loop_bookings = $this->config_data['Bookings']['pnd'];
									} elseif (isset($this->config_data['Bookings']['list']) && isset($this->config_data['Bookings']['list']['orders'])) {
										$active_bookings = count($this->config_data['Bookings']['list']['orders']);
										$loop_bookings = $this->config_data['Bookings']['list']['orders'];
									}
								}
								?>
								<h4><?php echo JText::_('VCMCFGIMPWIZBOOKINGS') . ($active_bookings > 0 ? " ({$active_bookings})" : ''); ?></h4>
								<div class="vcm-cfgimpwizard-bookings">
									<div class="vcm-cfgimpwizard-bookings-inner">
									<?php
									if (!count($this->config_data['Bookings'])) {
										?>
										<p class="warn"><?php echo JText::_('VCMCFGIMPWIZBOOKINGSNONE'); ?></p>
										<?php
									} else {
										foreach ($loop_bookings as $book) {
											$checkin = isset($book['checkin']) ? $book['checkin'] : $book['info']['checkin'];
											$checkout = isset($book['checkout']) ? $book['checkout'] : $book['info']['checkout'];
											$bid = isset($book['id']) ? $book['id'] : $book['info']['idorderota'];
											?>
										<div class="vcm-cfgimpwizard-booking-container">
											<div class="vcm-cfgimpwizard-booking-inner">
												<div class="vcm-cfgimpwizard-booking-det">
													<span class="vcm-cfgimpwizard-booking-det-lbl"><?php echo JText::_('VCMSMARTBALBID'); ?></span>
													<span class="vcm-cfgimpwizard-booking-det-val"><?php echo $bid; ?></span>
												</div>
												<div class="vcm-cfgimpwizard-booking-det">
													<span class="vcm-cfgimpwizard-booking-det-lbl"><?php echo JText::_('VCMPVIEWORDERSVBFOUR'); ?></span>
													<span class="vcm-cfgimpwizard-booking-det-val"><?php echo $checkin; ?></span>
												</div>
												<div class="vcm-cfgimpwizard-booking-det">
													<span class="vcm-cfgimpwizard-booking-det-lbl"><?php echo JText::_('VCMPVIEWORDERSVBFIVE'); ?></span>
													<span class="vcm-cfgimpwizard-booking-det-val"><?php echo $checkout; ?></span>
												</div>
											</div>
										</div>
											<?php
										}
									}
									?>
									</div>
									<div class="vcm-cfgimpwizard-import-box">
										<button type="button" class="btn btn-large btn-success" onclick="vcmCfgImpDoStep(this);"><?php echo JText::_('VCMCOPYRATESINVDONE'); ?></button>
									</div>
								</div>
							</div>
						</div>
						<div class="vcm-dotslider-dots">
							<a href="JavaScript: void(0);" class="vcm-dotslider-indicator"></a>
							<a href="JavaScript: void(0);" class="vcm-dotslider-dot" data-pos="0" data-completed="0"></a>
							<a href="JavaScript: void(0);" class="vcm-dotslider-dot" data-pos="1" data-completed="0"></a>
							<a href="JavaScript: void(0);" class="vcm-dotslider-dot" data-pos="2" data-completed="0"></a>
							<a href="JavaScript: void(0);" class="vcm-dotslider-dot" data-pos="3" data-completed="0"></a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<a style="display: none;" id="vcm-cfgimpwizard-redirect" href="index.php?option=com_vikchannelmanager"></a>

<script type="text/javascript">

function vcmShowLoading() {
	jQuery(".vcm-loading-overlay").show();
}

function vcmStopLoading() {
	jQuery(".vcm-loading-overlay").hide();
}

function vcmCfgImpDoStep(elem) {
	var elem = jQuery(elem);
	var stepbox = elem.closest('.vcm-dotslider-slide');
	if (!stepbox || !stepbox.length) {
		alert('Invalid step provided');
		return false;
	}
	var do_step = parseInt(stepbox.attr('data-step'));
	if (isNaN(do_step) || do_step < 0) {
		alert('Invalid step number');
		return false;
	}

	// check whether this step has been completed already
	var completed = parseInt(jQuery('.vcm-dotslider-dot[data-pos="' + do_step + '"]').attr('data-completed'));
	if (isNaN(completed) || completed === 1) {
		// current step is completed, navigate without making any requests
		vcmCfgImpNavigate('next');
		return;
	}

	// build up vars for the request
	var room_ids = new Array;
	jQuery('.vcm-cfgimpwizard-room-ckbox').each(function() {
		var inp = jQuery(this).find('input');
		if (!inp.length) {
			return;
		}
		if (inp.attr('type') == 'checkbox' && !inp.is(':checked')) {
			return;
		}
		var inpval = inp.val();
		if (!inpval.length) {
			return;
		}
		room_ids.push(inpval);
	});

	var rateplan_ids = new Array;
	jQuery('.vcm-cfgimpwizard-rplan-ckbox').each(function() {
		var inp = jQuery(this).find('input');
		if (!inp.length) {
			return;
		}
		if (inp.attr('type') == 'checkbox' && !inp.is(':checked')) {
			return;
		}
		var inpval = inp.val();
		if (!inpval.length) {
			return;
		}
		rateplan_ids.push(inpval);
	});

	var rateplan_costs = new Array;
	if (rateplan_ids.length) {
		for (var i = 0; i < rateplan_ids.length; i++) {
			var cost_elem = jQuery('#dcpn' + rateplan_ids[i]);
			if (cost_elem.length) {
				rateplan_costs.push(parseFloat(cost_elem.val()));
			} else {
				rateplan_costs.push(0);
			}
		}
	}

	var downphotos = 0;
	if (jQuery('.vcm-cfgimpwizard-downphotos-ckbox').length && jQuery('.vcm-cfgimpwizard-downphotos-ckbox').find('input[type="checkbox"]').length) {
		if (jQuery('.vcm-cfgimpwizard-downphotos-ckbox').find('input[type="checkbox"]').is(':checked')) {
			downphotos = 1;
		}
	}

	// at least one photo must be selected
	if (!room_ids.length) {
		alert('Please select at least one room for import, or exit this wizard');
		return false;
	}

	// if no rate plans selected, display warning message
	if (do_step === 1 && !rateplan_ids.length) {
		if (!confirm('No rate plans selected. You will have to manually create at least one standard rate from your system in order to complete the configuration.')) {
			return false;
		}
	}

	// show loading
	vcmShowLoading();
	
	// make the AJAX request
	var jqxhr = jQuery.ajax({
		type: "POST",
		url: "index.php",
		data: {
			option: "com_vikchannelmanager",
			task: "config_import_exec_step",
			step: do_step,
			rooms: room_ids,
			rplans: rateplan_ids,
			costs: rateplan_costs,
			downphotos: downphotos,
			tmpl: "component"
		}
	}).done(function(resp) {
		// stop loading and navigate to next step
		vcmStopLoading();

		// parse the response
		try {
			resp = JSON.parse(resp);
		} catch (e) {
			console.error('could not decode response', e, resp);
			resp = null;
		}
		if (!resp || !resp.status) {
			alert(resp.error);
			return;
		}

		if (resp.hasOwnProperty('mess') && resp.mess.length) {
			var result_container = stepbox.find('.vcm-cfgimpwizard-import-box');
			result_container.find('.vcm-cfgimpwizard-step-resultmess').remove();
			result_container.prepend('<h4 class="vcm-cfgimpwizard-step-resultmess">' + resp.mess + '</h4>');
		}

		// mark this step as completed
		jQuery('.vcm-dotslider-dot[data-pos="' + do_step + '"]').attr('data-completed', '1');

		if (resp.status > 1) {
			// process completed, redirect to dashboard after 8 seconds to allow the bookings to be imported by the last step
			vcmShowLoading();
			setTimeout(function() {
				document.location.href = jQuery('#vcm-cfgimpwizard-redirect').attr('href');
			}, 8000);
		} else {
			// navigate to the next step
			vcmCfgImpNavigate('next');
		}
	}).fail(function(err) {
		console.error(err.responseText);
		alert("Error performing the request");
		vcmStopLoading();
	});
}

function vcmCfgImpNavigate(to) {
	if (isNaN(to) && to != 'next' && to != 'prev') {
		alert('Invalid navigation parameter');
		return false;
	}

	var currentPos  = parseInt(jQuery('.vcm-dotslider').attr('data-pos'));
	var gotoPos 	= 0;

	if (to == 'next') {
		gotoPos = jQuery('.vcm-dotslider-dot[data-pos="' + (currentPos + 1) + '"]').length ? (currentPos + 1) : gotoPos;
	} else if (to == 'prev') {
		gotoPos = jQuery('.vcm-dotslider-dot[data-pos="' + (currentPos - 1) + '"]').length ? (currentPos - 1) : parseInt(jQuery('.vcm-dotslider-dot').last().attr('data-pos'));
	} else {
		gotoPos = jQuery('.vcm-dotslider-dot[data-pos="' + to + '"]').length ? to : gotoPos;
	}
	gotoPos = isNaN(gotoPos) ? 0 : gotoPos;

	// trigger navigation event
	jQuery('.vcm-dotslider-dot[data-pos="' + gotoPos + '"]').trigger('vcmNavigateSlider');
}

jQuery(document).ready(function() {
	
	var sliderElem    = jQuery('.vcm-dotslider');
	var dotElems      = sliderElem.find('.vcm-dotslider-dot');
	var indicatorElem = sliderElem.find('.vcm-dotslider-indicator');

	dotElems.each(function() {
		jQuery(this).on('click vcmNavigateSlider', function(e, params) {
			var dotElem 	= jQuery(this);
			var currentPos  = parseInt(sliderElem.attr('data-pos'));
			var newPos      = parseInt(dotElem.attr('data-pos'));

			if (typeof params == 'object' && params.hasOwnProperty('step')) {
				newPos = parseInt(params.step);
			}

			if (e.type == 'click') {
				// check whether the new step needs to be completed when clicking on a dot
				var completed = parseInt(jQuery('.vcm-dotslider-dot[data-pos="' + newPos + '"]').attr('data-completed'));
				if (isNaN(completed) || completed === 0) {
					alert('Please complete the previous or current step first.');
					return;
				}
			}

			var newDirection     = (newPos > currentPos ? 'right' : 'left');
			var currentDirection = (newPos < currentPos ? 'right' : 'left');

			indicatorElem.removeClass('slider-indicator-' + currentDirection);
			indicatorElem.addClass('slider-indicator-' + newDirection);		
			sliderElem.attr('data-pos', newPos);
		});
	});

});

</script>
