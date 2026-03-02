<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

$app = JFactory::getApplication();

$max_nodes = VikRequest::getInt('max_nodes', '', 'request');
$max_nodes = empty($max_nodes) || $max_nodes <= 0 ? 50 : $max_nodes;

$now = getdate();
$def_fromdate = date('Y-m-d');
$def_todate = date('Y-m-d', mktime(0, 0, 0, $now['mon'], $now['mday'], ($now['year'] + 1)));

$vbo_app = VikBooking::getVboApplication();
$filters_supported = method_exists($vbo_app, 'renderElementsDropDown');

JText::script('VCM_FILTERS');
JText::script('VCMRARADDLOSAPPLY');
JText::script('CANCEL');

?>
<script type="text/javascript">
/* Loading Overlay */
function vcmShowLoading() {
	jQuery(".vcm-loading-overlay").show();
}
function vcmStopLoading() {
	jQuery(".vcm-loading-overlay").hide();
}
/* Show loading when sending CUSTA_RQ to prevent double submit */
Joomla.submitbutton = function(task) {
	if ( task == 'avpushsubmit' ) {
		vcmShowLoading();
	}
	Joomla.submitform(task, document.adminForm);
}
</script>

<div class="vcm-loading-overlay">
	<div class="vcm-loading-dot vcm-loading-dot1"></div>
	<div class="vcm-loading-dot vcm-loading-dot2"></div>
	<div class="vcm-loading-dot vcm-loading-dot3"></div>
	<div class="vcm-loading-dot vcm-loading-dot4"></div>
	<div class="vcm-loading-dot vcm-loading-dot5"></div>
</div>

<div class="vcm-avpush-info">
	<h3><?php echo JText::_('VCMAVPUSHTITLE'); ?></h3>
	<p><?php echo JText::_('VCMAVPUSHINFO'); ?></p>
</div>

<p class="warn vcm-closeall-warn" style="display: none;"></p>

<form action="index.php?option=com_vikchannelmanager" method="post" name="adminForm" id="adminForm">
	<div class="vcm-table-responsive vcm-table-rounded">
		<table class="vcmavpushtable vcm-table">
			<thead class="vcm-pushtable-head">
				<tr>
					<th class="vcm-pushtable-head-th-left">
						<?php
						echo JText::_('VCMBCARCROOMTYPE');
						if ($filters_supported) {
							?>
						<div class="vcm-bulkaction-filters-wrap<?php echo ((array) $app->input->get('listingsfilter', [])) ? ' vcm-bulkaction-filters-active' : ''; ?>">
							<span class="vcm-bulkaction-filters-show vcm-tooltip vcm-tooltip-top" data-tooltiptext="<?php echo $this->escape(JText::_('VCM_FILTERS')); ?>"><?php VikBookingIcons::e('filter'); ?></span>
						</div>
							<?php
						}
						?>
					</th>
					<th class="vcm-pushtable-head-th-left"><?php echo JText::_('VCMAVPUSHHEADRANGEDT'); ?></th>
					<th class="vcm-pushtable-head-th-left"><?php echo JText::_('VCMAVPUSHHEADCHTOUPD'); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php
			foreach ($this->rows as $room) {
				if (!$room['channels']) {
					continue;
				}
				?>
				<tr>
					<td class="vcmavpush-td-room">
						<div class="vcmavpush-roominfo">
							<span class="vcm-oversight-roomunits"><?php echo $room['units']; ?></span> <span class="vcm-oversight-roomname"><?php echo $room['name']; ?></span><input type="hidden" name="rooms[]" id="room_<?php echo $room['id']; ?>" value="<?php echo $room['id']; ?>" />
						</div>
						<div class="vcmavpush-roomstatus">
							<button type="button" class="btn vcmavpush-togglerstatus" data-roomid="<?php echo $room['id']; ?>"><?php echo JText::_('VCMDESELECT'); ?></button>
						</div>
					</td>
					<td class="vcmavpush-td-dates"><span class="vcmavpush-dpick-cont"><label for="from_<?php echo $room['id']; ?>"><?php echo JText::_('VCMFROMDATE'); ?></label> <input type="text" name="from[]" size="13" value="<?php echo $def_fromdate; ?>" class="vcmdatepickerav" id="from_<?php echo $room['id']; ?>" autocomplete="off" /></span> <span class="vcmavpush-dpick-cont"><label for="to_<?php echo $room['id']; ?>"><?php echo JText::_('VCMTODATE'); ?></label> <input type="text" name="to[]" size="13" value="<?php echo $def_todate; ?>" class="vcmdatepickerav" id="to_<?php echo $room['id']; ?>" autocomplete="off" /></span> <span class="vcmavpush-dpick-totdays" id="totdays_<?php echo $room['id']; ?>">x</span></td>
					<td class="vcmavpush-td-channels">
						<div class="vcm-custa-channelhead">
							<button type="button" class="btn btn-light vcm-check-all" onclick="vcmBulkActionToggleChannels('.check-<?php echo $room['id']; ?>', true);"><?php echo JText::_('VCMOSCHECKALL'); ?></button>
							<button type="button" class="btn btn-light vcm-uncheck-all" onclick="vcmBulkActionToggleChannels('.check-<?php echo $room['id']; ?>', false);"><?php echo JText::_('VCMOSUNCHECKALL'); ?></button>
						</div>
						<div class="vcmavpush-channels-wrap">
						<?php
						foreach ($room['channels'] as $kc => $channel) {
							$orig_ch_name = $channel['channel'];
							if ($channel['idchannel'] == VikChannelManagerConfig::AIRBNBAPI) {
								$channel['channel'] = 'Airbnb';
							} elseif ($channel['idchannel'] == VikChannelManagerConfig::GOOGLEHOTEL) {
								$channel['channel'] = 'Google Hotel';
							} elseif ($channel['idchannel'] == VikChannelManagerConfig::GOOGLEVR) {
								$channel['channel'] = 'Google VR';
							} elseif ($channel['idchannel'] == VikChannelManagerConfig::VRBOAPI) {
								$channel['channel'] = 'Vrbo';
							}
							?>
							<span class="vbotasp <?php echo $orig_ch_name; ?> vcmavpush-channel-cont vcmavpush-channel-cont-deactive">
								<label for="ch<?php echo $room['id'].$channel['idchannel']; ?>"><?php echo ucwords($channel['channel']); ?></label>
								<input type="checkbox" class="vcm-avpush-checkbox check-<?php echo $room['id']; ?>" name="channels[<?php echo $room['id']; ?>][]" id="ch<?php echo $room['id'].$channel['idchannel']; ?>" value="<?php echo $channel['idchannel']; ?>" onchange="vcmBulkActionChkboxToggle(this);" />
							</span>
							<?php
						}
						?>
						</div>
					</td>
				</tr>
				<?php
			}
			?>
			</tbody>
		</table>
	</div>
	<br clear="all" />
	<p class="warn vcm-closeall-warn" style="display: none;"></p>
	<div class="vcm-avpush-advanced-left" style="float: left;">
		<button type="button" id="vcm-avpush-toggleall" class="btn btn-primary"><i class="vboicn-switch"></i><?php echo JText::_('VCMRATESPUSHTOGGLEALL'); ?></button>
	</div>
	<div class="vcm-avpush-advanced-right" style="float: right;">
		<button type="button" id="vcm-avpush-ch-selall" class="btn btn-primary"><i class="vboicn-checkmark"></i><?php echo JText::_('VCMRATESPUSHSELALLCH'); ?></button>
		&nbsp;
		<button type="button" id="vcm-avpush-ch-unselall" class="btn btn-warning"><i class="vboicn-cross"></i><?php echo JText::_('VCMRATESPUSHUNSELALLCH'); ?></button>
		&nbsp;
		<button type="button" id="vcm-avpush-closeall" class="btn btn-danger" onclick="return vcmConfirmCloseAll();"><i class="vboicn-blocked"></i><?php echo JText::_('VCMRATESPUSHCLOSEALL'); ?></button>
	</div>
	<input type="hidden" name="max_nodes" value="<?php echo $max_nodes; ?>" />
	<input type="hidden" name="task" value="avpush" />
	<input type="hidden" name="closeall" id="closeall" value="0" />
<?php
foreach ((array) $app->input->get('listingsfilter', []) as $flisting_id) {
	?>
	<input type="hidden" name="listingsfilter[]" value="<?php echo (int) $flisting_id; ?>" />
	<?php
}
if ($app->input->getBool('e4j_debug')) {
	echo '<input type="hidden" name="e4j_debug" value="1" />'."\n";
}
?>
	<?php echo '<br/>'.$this->navbut; ?>
</form>

<?php
if ($filters_supported) {
	?>
<div class="vcm-bulkaction-filters-helper" style="display: none;">
	<div class="vcm-bulkaction-filters-container">
		<form action="index.php?option=com_vikchannelmanager&task=avpush" method="post" class="vcm-bulkaction-filters-form">
			<div class="vcm-admin-container vcm-admin-container-full vcm-admin-container-compact">
				<div class="vcm-params-wrap">
					<div class="vcm-params-container">
						<div class="vcm-params-block">

							<div class="vcm-param-container">
								<div class="vcm-param-label"><?php echo JText::_('VCM_FILTER_LISTINGS'); ?></div>
								<div class="vcm-param-setting">
								<?php
								/**
								 * Display listings dropdown.
								 * 
								 * @since 	1.9.6
								 */
								echo $vbo_app->renderElementsDropDown([
									'id'              => 'listingsfilter',
									'elements'        => 'listings',
									'load_categories' => 1,
									'categories_lbl'  => JText::_('VCM_FILTER_BY_CATEGORY'),
									'placeholder'     => JText::_('VCM_FILTER_LISTINGS'),
									'attributes'      => [
										'name'     => 'listingsfilter[]',
										'multiple' => 'multiple',
									],
									'selected_values' => (array) $app->input->get('listingsfilter', []),
								]);
								?>
								</div>
							</div>

						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
	<?php
}
?>

<script type="text/javascript">
	function vcmConfirmCloseAll() {
		if (confirm('<?php echo addslashes(JText::_('VCMRATESPUSHCLOSEALLCONF')); ?>')) {
			jQuery('#closeall').val('1');
			jQuery('#vcm-avpush-closeall').remove();
			jQuery('.vcm-closeall-warn').text('<?php echo addslashes(JText::_('VCMRATESPUSHCLOSEALLCONS')); ?>').show();
			return true;
		}
		return false;
	}

	function vcmCheckDates(selectedDate, inst) {
		if (selectedDate !== null && inst !== null) {
			var inpidparts = jQuery(this).attr('id').split('_');
			var cur_from_date = jQuery(this).val();
			if (inpidparts[0] == 'from' && cur_from_date.length > 0) {
				var nowstart = jQuery(this).datepicker('getDate');
				var nowstartdate = new Date(nowstart.getTime());
				var nextyear = new Date(nowstart.getTime());
				nextyear.setFullYear((nextyear.getFullYear() + 1));
				nextyear.setMonth((nextyear.getMonth() + 6));
				jQuery('#to_'+inpidparts[1]).datepicker( 'option', { minDate: nowstartdate, maxDate: nextyear } );
			}
		}
		jQuery('.vcmdatepickerav:input').each(function(k, v) {
			var inpidparts = jQuery(this).attr('id').split('_');
			var cur_from_date = jQuery(this).val();
			var cur_to_date = jQuery('#to_'+inpidparts[1]).val();
			if (inpidparts[0] != 'from' || cur_from_date.length <= 0 || cur_to_date.length <= 0) {
				return true;
			}
			var fromd = new Date(cur_from_date);
			fromd.setMinutes(fromd.getMinutes() - fromd.getTimezoneOffset());
			var tod = new Date(cur_to_date);
			tod.setMinutes(tod.getMinutes() - tod.getTimezoneOffset());
			var daysdiff = Math.round((tod - fromd) / 86400000);
			if (parseInt(daysdiff) < 2) {
				daysdiff = 2;
				fromd.setDate(fromd.getDate() + 2);
				jQuery('#to_'+inpidparts[1]).datepicker( 'setDate', fromd );
			}
			jQuery('#totdays_'+inpidparts[1]).html(daysdiff+" <?php echo addslashes(JText::_('VCMDAYS')); ?>");
		});
	}

	function vcmBulkActionChkboxToggle(ckbox) {
		var elem = jQuery(ckbox);
		if (!elem.length) {
			return;
		}
		var checked = elem.prop('checked');
		var container = elem.closest('.vcmavpush-channel-cont');
		if (!container.length) {
			return;
		}
		if (checked) {
			container.removeClass('vcmavpush-channel-cont-deactive');
		} else {
			container.addClass('vcmavpush-channel-cont-deactive');
		}
	}

	function vcmBulkActionToggleChannels(selector, status) {
		var channel_ckboxes = jQuery(selector);
		if (!channel_ckboxes.length) {
			return;
		}
		channel_ckboxes.each(function() {
			var ckbox = jQuery(this);
			if (!ckbox.length) {
				return;
			}
			ckbox.prop('checked', status);
			// trigger "vcmBulkActionChkboxToggle" with change event
			ckbox.trigger('change');
		});
	}

	jQuery(function() {
		
		vcmCheckDates(null, null);
		
		jQuery('.vcmdatepickerav:input').datepicker({
			dateFormat: "yy-mm-dd",
			minDate: 0,
			maxDate: "+1y +6m",
			onSelect: vcmCheckDates
		});

		jQuery('.vcmavpush-togglerstatus').click(function() {
			var roomid = jQuery(this).attr('data-roomid');
			if (jQuery('#from_'+roomid).prop('disabled') === true) {
				jQuery('#from_'+roomid).prop('disabled', false);
				jQuery('#to_'+roomid).prop('disabled', false);
				jQuery('.check-'+roomid).prop('disabled', false);
				jQuery('#room_'+roomid).prop('disabled', false).attr('name', 'rooms[]');
				jQuery(this).text("<?php echo addslashes(JText::_('VCMDESELECT')); ?>");
				jQuery(this).closest('td').next().css('opacity', 1).next().css('opacity', 1);
			} else {
				jQuery('#from_'+roomid).prop('disabled', true);
				jQuery('#to_'+roomid).prop('disabled', true);
				jQuery('.check-'+roomid).prop('disabled', true);
				jQuery('#room_'+roomid).prop('disabled', true).attr('name', 'disabledrooms[]');
				jQuery(this).text("<?php echo addslashes(JText::_('VCMSELECT')); ?>");
				jQuery(this).closest('td').next().css('opacity', 0.5).next().css('opacity', 0.5);
			}
		});

		jQuery('.vcm-avpush-info').click(function() {
			jQuery(this).remove();
		});

		jQuery('#vcm-avpush-toggleall').click(function() {
			jQuery('.vcmavpush-togglerstatus').trigger('click');
		});

		jQuery('#vcm-avpush-ch-selall').click(function() {
			jQuery('.vcm-check-all').trigger('click');
		});

		jQuery('#vcm-avpush-ch-unselall').click(function() {
			jQuery('.vcm-uncheck-all').trigger('click');
		});

		// unify the range of dates to update across all rooms
		jQuery('.vcmavpush-dpick-totdays').click(function() {
			var container = jQuery(this).closest('.vcmavpush-td-dates');
			var from_date = container.find('input[name="from[]"]').datepicker('getDate');
			var to_date   = container.find('input[name="to[]"]').datepicker('getDate');
			var all_elements = jQuery('.vcmavpush-dpick-totdays');
			if (all_elements.length > 1) {
				all_elements.each(function() {
					var cal_container = jQuery(this).closest('.vcmavpush-td-dates');
					cal_container.find('input[name="from[]"]').datepicker('setDate', from_date);
					cal_container.find('input[name="to[]"]').datepicker('setDate', to_date);
				});
				// calculate the new number of days in the range
				vcmCheckDates(null, null);
			}
		});

		/**
		 * Display listings filtering modal.
		 */
		jQuery('.vcm-bulkaction-filters-show').click(function() {
			let cancel_btn = jQuery('<button></button>')
				.addClass('btn')
				.text(Joomla.JText._('CANCEL'))
				.on('click', function() {
					jQuery('select[name="listingsfilter[]"]').val('').trigger('change');
					jQuery('form.vcm-bulkaction-filters-form').submit();
					VBOCore.emitEvent('vcm-bulkaction-filter-listings-dismiss');
				});

			let apply_btn = jQuery('<button></button>')
				.addClass('btn btn-success')
				.text(Joomla.JText._('VCMRARADDLOSAPPLY'))
				.on('click', function() {
					jQuery('form.vcm-bulkaction-filters-form').submit();
					VBOCore.emitEvent('vcm-bulkaction-filter-listings-dismiss');
				});

			let modal_body = VBOCore.displayModal({
				title: Joomla.JText._('VCM_FILTERS'),
				footer_left: cancel_btn,
				footer_right: apply_btn,
				dismiss_event: 'vcm-bulkaction-filter-listings-dismiss',
				onDismiss: () => {
					jQuery('.vcm-bulkaction-filters-container').appendTo(jQuery('.vcm-bulkaction-filters-helper'));
				},
			});

			jQuery('.vcm-bulkaction-filters-container').appendTo(modal_body);
		});

	});
</script>
