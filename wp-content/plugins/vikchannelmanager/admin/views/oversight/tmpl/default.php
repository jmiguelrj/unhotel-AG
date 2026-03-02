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

$MAX_DAYS = $this->maxDays;
$MAX_TO_DISPLAY = $this->maxDaysToDisplay;
$curr_symb = VikChannelManager::getCurrencySymb(true);

$cookie = JFactory::getApplication()->input->cookie;

$room_max_units = array();

$nowts = getdate($this->tsstart);
$days_labels = array(
	JText::_('VBSUN'),
	JText::_('VBMON'),
	JText::_('VBTUE'),
	JText::_('VBWED'),
	JText::_('VBTHU'),
	JText::_('VBFRI'),
	JText::_('VBSAT'),
);

$months_labels = array(
	JText::_('VCMMONTHONE'),
	JText::_('VCMMONTHTWO'),
	JText::_('VCMMONTHTHREE'),
	JText::_('VCMMONTHFOUR'),
	JText::_('VCMMONTHFIVE'),
	JText::_('VCMMONTHSIX'),
	JText::_('VCMMONTHSEVEN'),
	JText::_('VCMMONTHEIGHT'),
	JText::_('VCMMONTHNINE'),
	JText::_('VCMMONTHTEN'),
	JText::_('VCMMONTHELEVEN'),
	JText::_('VCMMONTHTWELVE'),
);

foreach ($months_labels as $i => $v) {
	$months_labels[$i] = function_exists('mb_substr') ? mb_substr($v, 0, 3, 'UTF-8') : substr($v, 0, 3);
}

/**
 * Grab a list of future block dates.
 * 
 * @since 	1.8.3
 */
$block_dates = VikChannelManager::getSmartBalancerInstance()->getFutureBlockDates();

// JS vars
JText::script('VCM_DATE_BLOCKED');
JText::script('VCM_DATE_BLOCKED_HELP');
JText::script('VCM_UNLOCK_DAY_OPEN');
JText::script('CANCEL');

?>
<script type="text/javascript">
var vcm_acmp_obj,
	vcm_overlay_on = false,
	hold_alt = false,
	sel_format = "<?php echo VikChannelManager::getClearDateFormat(true); ?>";

if (sel_format == "Y/m/d") {
	Date.prototype.format = "yy/mm/dd";
} else if (sel_format == "m/d/Y") {
	Date.prototype.format = "mm/dd/yy";
} else {
	Date.prototype.format = "dd/mm/yy";
}

/* Loading Overlay */
function vcmShowLoading() {
	jQuery(".vcm-loading-overlay").show();
}

function vcmStopLoading() {
	jQuery(".vcm-loading-overlay").hide();
}

/* Modal Window */
function vcmCloseModal(dontaskagain) {
	jQuery(".vcm-info-overlay-block").fadeOut(400, function() {
		jQuery(this).attr("class", "vcm-info-overlay-block");
	});
	vcm_overlay_on = false;
	if (dontaskagain === null) {
		// do nothing about the reminder
		return;
	}
	// set cookie to remind or not the selection
	var nd = new Date();
	if (dontaskagain) {
		nd.setTime(nd.getTime() + (365*24*60*60*1000));
	} else {
		nd.setTime(nd.getTime() - (24*60*60*1000));
	}
	document.cookie = "vcmOverviewATip=1; expires=" + nd.toUTCString() + "; path=/; SameSite=Lax";
}

function vcmTipModal() {
	var vcm_modal_cont = "<h3><?php echo addslashes(JText::_('VCMTIPMODALTITLE')); ?></h3>";
	vcm_modal_cont += "<p style=\"text-align: center;\"><?php echo addslashes(JText::_('VCMTIPMODALTEXTAV')); ?></p><p style=\"text-align: center;\"><img src=\"<?php echo VCM_ADMIN_URI; ?>assets/css/images/overview_tip_av.gif\" alt=\"Set Custom Availability Example\"/></p>";
	vcm_modal_cont += "<div class=\"vcm-tip-modal-done\"><button type=\"button\" class=\"btn btn-primary\" onclick=\"javascript: vcmCloseModal(false);\"><?php echo addslashes(JText::_('VCMTIPMODALOKREMIND')); ?></button> &nbsp;&nbsp; <button type=\"button\" class=\"btn btn-success\" onclick=\"javascript: vcmCloseModal(true);\"><?php echo addslashes(JText::_('VCMTIPMODALOK')); ?></button></div>";
	jQuery(".vcm-info-overlay-content").html(vcm_modal_cont);
	jQuery(".vcm-info-overlay-block").addClass("vcm-modal-tip").fadeIn();
	vcm_overlay_on = true;
}

function vcmBlockDatesModal(ymd, rid) {
	// make sure we've got info about this block-date
	if (!vcm_block_dates) {
		console.error('object vcm_block_dates is not defined', vcm_block_dates);
		return false;
	}
	if (!vcm_block_dates.hasOwnProperty(ymd)) {
		alert('block date not found: ' + ymd);
		return false;
	}
	// build modal content
	var base_smbal_uri = jQuery('#vcm-base-smbal-link').attr('href');
	var vcm_modal_cont = '<h3>' + ymd + ' - ' + Joomla.JText._('VCM_DATE_BLOCKED') + '</h3>' + "\n";
	vcm_modal_cont += '<p class="info">' + Joomla.JText._('VCM_DATE_BLOCKED_HELP') + '</p>' + "\n";
	if (vcm_block_dates[ymd].hasOwnProperty('rule_names') && vcm_block_dates[ymd].hasOwnProperty('rule_ids')) {
		vcm_modal_cont += '<ul>' + "\n";
		for (var i = 0; i < vcm_block_dates[ymd]['rule_names'].length; i++) {
			var link_to_smbal = base_smbal_uri.replace('%d', vcm_block_dates[ymd]['rule_ids'][i]);
			vcm_modal_cont += '<li>- <a href="' + link_to_smbal + '" target="_blank">' + vcm_block_dates[ymd]['rule_names'][i] + '</a></li>' + "\n";
		}
		vcm_modal_cont += '</ul>' + "\n";
	}
	var multi_rooms_blocked = false;
	if (vcm_block_dates[ymd].hasOwnProperty('room_ids') && vcm_block_dates[ymd]['room_ids'].length > 1) {
		multi_rooms_blocked = true;
	}
	var unlock_all_txt = Joomla.JText._('VCM_UNLOCK_DAY_OPEN');
	vcm_modal_cont += '<div style="margin-top: 10px; text-align: center;">' + "\n";
	vcm_modal_cont += '	<button type="button" class="btn btn-danger" onclick="vcmBlockDateSetExcluded(\'' + ymd + '\');"><?php VikBookingIcons::e('unlock'); ?> ' + unlock_all_txt.replace('%s', ymd) + '</button>' + "\n";
	vcm_modal_cont += '</div>' + "\n";
	vcm_modal_cont += '<div style="margin-top: 10px; text-align: center;">' + "\n";
	vcm_modal_cont += '	<button type="button" class="btn btn-light" onclick="vcmCloseModal(null);">' + Joomla.JText._('CANCEL') + '</button>' + "\n";
	vcm_modal_cont += '</div>' + "\n";
	// show modal
	jQuery(".vcm-info-overlay-content").html(vcm_modal_cont);
	jQuery(".vcm-info-overlay-block").addClass("vcm-modal-blocked-dates").fadeIn();
	vcm_overlay_on = true;
}

/**
 * Makes an AJAX request to add one excluded date
 * to all Smart Balancer rules blocking this date.
 */
function vcmBlockDateSetExcluded(ymd) {
	if (!ymd || !ymd.length) {
		console.error('empty date', ymd);
		return false;
	}
	// show loading
	vcmShowLoading();
	// make the request
	var jqxhr = jQuery.ajax({
		type: "POST",
		url: "index.php",
		data: {
			option: "com_vikchannelmanager",
			task: "smart_balancer_exclude_day",
			exclude_dt: ymd,
			tmpl: "component"
		}
	}).done(function(res) {
		// stop loading
		vcmStopLoading();
		// validate response
		if (res.indexOf('e4j.ok') >= 0) {
			// delete object property
			delete vcm_block_dates[ymd];
			// close modal
			vcmCloseModal(null);
			// remove all locks in this date, and the class to the parent cell
			jQuery('.vcm-otablockdate-roomday[data-ymd="' + ymd + '"]').each(function() {
				jQuery(this).parent('td').removeClass('vcm-otablockdate-cell');
				jQuery(this).remove();
			});
		} else {
			console.error(res);
			alert('Something went wrong. Check your browser console for the full error details');
		}
	}).fail(function() {
		alert("Error Performing Ajax Request");
		// stop loading
		vcmStopLoading();
	});
}

function vcmAcmpCheckData(date, obj) {
	<?php
	if (!empty($this->acmp_last_request)) :
	?>
	var vcm_skip_date = jQuery.datepicker.formatDate(new Date().format, new Date("<?php echo $this->acmp_last_request; ?>"));
	//console.log(date+' - '+vcm_skip_date);
	if (date == vcm_skip_date) {
		jQuery('#loadacmp').val("1");
		document.vcmoverview.submit();
		return true;
	}
	if (confirm("<?php echo addslashes(JText::sprintf('VCMCONFIRMACMPSESSVAL', $this->acmp_last_request)); ?>")) {
		var acmp_last_date = new Date("<?php echo $this->acmp_last_request; ?>");
		jQuery('#vcmdatepicker:input').datepicker( "setDate", acmp_last_date );
		jQuery('#loadacmp').val("1");
		document.vcmoverview.submit();
	} else {
		document.vcmoverview.submit();
	}
	<?php
	else:
	?>
	document.vcmoverview.submit();
	<?php
	endif;
	?>
}

jQuery(document).ready(function() {
	jQuery(document).mouseup(function(e) {
		if (!vcm_overlay_on) {
			return false;
		}
		var vcm_overlay_cont = jQuery(".vcm-info-overlay-content");
		if (!vcm_overlay_cont.is(e.target) && vcm_overlay_cont.has(e.target).length === 0) {
			vcmCloseModal(false);
		}
	});

	jQuery(document).keyup(function(e) {
		hold_alt = false;
		jQuery('#vcm-acmp-toggleall').hide();
		if (e.keyCode == 27 && vcm_overlay_on) {
			vcmCloseModal(true);
		}
	});

	jQuery('#vcmdatepicker:input').datepicker({
		dateFormat: new Date().format,
		onSelect: vcmAcmpCheckData
	});

	jQuery('.vcm-otablockdate-roomday').click(function() {
		// lock day clicked
		var ymd = jQuery(this).attr('data-ymd');
		var rid = jQuery(this).attr('data-rid');
		// open modal
		vcmBlockDatesModal(ymd, rid);
	});

<?php
$cookie_ovavtip = $cookie->get('vcmOverviewATip', '', 'string');
if (empty($cookie_ovavtip)) {
	?>
	vcmTipModal();
	<?php
}
?>
});
</script>

<div class="vcm-loading-overlay">
	<div class="vcm-loading-dot vcm-loading-dot1"></div>
	<div class="vcm-loading-dot vcm-loading-dot2"></div>
	<div class="vcm-loading-dot vcm-loading-dot3"></div>
	<div class="vcm-loading-dot vcm-loading-dot4"></div>
	<div class="vcm-loading-dot vcm-loading-dot5"></div>
</div>
<div class="vcm-info-overlay-block">
	<div class="vcm-info-overlay-content"></div>
</div>

<div class="vcm-oversight-toolbar-left">
	<form action="index.php?option=com_vikchannelmanager&amp;task=oversight" method="post" name="vboverview">
		<?php echo $this->wmonthsel; ?>
	</form>
	<div class="vcm-customa-changes">
		<span><?php echo JText::_('VCMCUSTOMACHANGES'); ?></span>
		<span id="vcm-totchanges">0</span>
	</div>
	<div class="vcm-customa-legend-block">
		<div class="vcm-customa-legend-entry">
			<span class="vcm-customa-legend-box vcm-customa-legend-green"> </span>
			<span class="vcm-customa-legend-label"><?php echo JText::_('VCMOVERSIGHTLEGENDGREEN'); ?></span>
		</div>
		<div class="vcm-customa-legend-entry">
			<span class="vcm-customa-legend-box vcm-customa-legend-purple"> </span>
			<span class="vcm-customa-legend-label"><?php echo JText::_('VCMOVERSIGHTLEGENDPURPLE'); ?></span>
		</div>
		<div class="vcm-customa-legend-entry">
			<span class="vcm-customa-legend-box vcm-customa-legend-greenself"> </span>
			<span class="vcm-customa-legend-label"><?php echo JText::_('VCMOVERSIGHTLEGENDGREENSELF'); ?></span>
		</div>
		<div class="vcm-customa-legend-entry">
			<span class="vcm-customa-legend-box vcm-customa-legend-red"> </span>
			<span class="vcm-customa-legend-label"><?php echo JText::_('VCMOVERSIGHTLEGENDRED'); ?></span>
		</div>
		<div class="vcm-customa-legend-entry">
			<span class="vcm-customa-legend-box vcm-customa-legend-sky"> </span>
			<span class="vcm-customa-legend-label"><?php echo JText::_('VCMOVERSIGHTLEGENDSKY'); ?></span>
		</div>
		<div class="vcm-customa-legend-entry">
			<span class="vcm-customa-legend-box vcm-customa-legend-pink"> </span>
			<span class="vcm-customa-legend-label"><?php echo JText::_('VCMOVERSIGHTLEGENDPINK'); ?></span>
		</div>
		<div class="vcm-customa-legend-entry">
			<span class="vcm-customa-legend-box vcm-customa-legend-dashed">--</span>
			<span class="vcm-customa-legend-label"><?php echo JText::_('VCMOVERSIGHTLEGENDDASHED'); ?></span>
		</div>
	</div>
<?php
if ($this->acmp_rq_enabled == 1) {
	?>
	<div class="vcm-oversight-acmp-block">
		<span class="vcmsynchspan vcmsynchspan-acmp">
			<a id="vcmstartsynch" href="javascript: void(0);" class="vcmsyncha" data-startdate="<?php echo $this->acmp_rq_start; ?>"><?php echo JText::_('VCMOVERVIEWACMPRQLAUNCH'); ?></a>
		</span>
		<div class="vcm-oversight-acmp-response"></div>
	</div>
	<input type="hidden" value="" id="exclude_rooms" />
	<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery('#vcm-acmp-toggleall').click(function() {
			if (hold_alt !== true) {
				return true;
			}
			var cur_excluded = jQuery('#exclude_rooms').val();
			jQuery(".vcm-oversight-roomname").each(function(k, v) {
				var rid = jQuery(this).attr("data-vcmrid");
				console.log(rid);
				if (rid.length) {
					if (cur_excluded.indexOf(';'+rid+';,') >= 0) {
						//this room is being excluded
						cur_excluded = cur_excluded.replace(';'+rid+';,', '');
						jQuery(this).removeClass("vcm-acmp-room-excluded");
					} else {
						//room not being excluded
						cur_excluded += ';'+rid+';,';
						jQuery(this).addClass("vcm-acmp-room-excluded");
					}
				}
			});
			jQuery('#exclude_rooms').val(cur_excluded);
		});
		jQuery(".vcm-oversight-roomname").click(function() {
			if (hold_alt !== true) {
				return true;
			}
			var rid = jQuery(this).attr("data-vcmrid");
			if (rid.length) {
				if (jQuery('#exclude_rooms').val().indexOf(';'+rid+';,') >= 0) {
					//this room is being excluded
					if (confirm('<?php echo addslashes(JText::_('VCMACMPINCLROOM')); ?>')) {
						jQuery('#exclude_rooms').val(jQuery('#exclude_rooms').val().replace(';'+rid+';,', ''));
						jQuery(this).removeClass("vcm-acmp-room-excluded");
					}
				} else {
					//room not being excluded
					if (confirm('<?php echo addslashes(JText::_('VCMACMPEXCLROOM')); ?>')) {
						jQuery('#exclude_rooms').val(jQuery('#exclude_rooms').val()+';'+rid+';,');
						jQuery(this).addClass("vcm-acmp-room-excluded");
					}
				}
			}
		});
		jQuery("#vcmstartsynch").click(function() {
			/* Show loading when sending ACMP_RQ to prevent double submit */
			vcmShowLoading();
			/*  */
			var acmp_fromdate = jQuery(this).attr("data-startdate");
			jQuery(".vcmsynchspan").removeClass("vcmsynchspansuccess");
			jQuery(".vcmsynchspan").removeClass("vcmsynchspanerror").addClass("vcmsynchspanloading");
			jQuery(".vcm-oversight-acmp-response").html("");
			jQuery(".vcm-acmp-channel-row").remove();
			var jqxhr = jQuery.ajax({
				type: "POST",
				url: "index.php",
				data: { option: "com_vikchannelmanager", task: "exec_acmp_rq", from: acmp_fromdate<?php echo isset($_REQUEST['e4j_debug']) && (int)$_REQUEST['e4j_debug'] == 1 ? ', e4j_debug: 1' : ''; ?>, excludeids: jQuery('#exclude_rooms').val(), tmpl: "component" }
			}).done(function(res) {
				jQuery(".vcmsynchspan").removeClass("vcmsynchspanloading");
				if (res.substr(0, 9) == 'e4j.error') {
					jQuery(".vcmsynchspan").addClass("vcmsynchspanerror");
					jQuery(".vcm-oversight-acmp-response").html("<pre class='vcmpreerror'>" + res.replace("e4j.error.", "") + "</pre>");
				} else {
					jQuery(".vcmsynchspan").addClass("vcmsynchspansuccess");
					vcm_acmp_obj = JSON.parse(res);
					if (vcm_acmp_obj.hasOwnProperty('errors')) {
						jQuery(".vcm-oversight-acmp-response").html("<pre class='vcmpreerror'>" + vcm_acmp_obj.errors + "</pre>");
						delete vcm_acmp_obj.errors;
					}
					jQuery.each(vcm_acmp_obj, function(idroomvb, channels){
						var vcm_room_row = jQuery("#vcm-acmp-row-"+idroomvb);
						if (!vcm_room_row.length) {
							return;
						}
						var tot_cells = vcm_room_row.find("td").length;
						var row_cells = vcm_room_row.find("td");
						jQuery.each(channels, function(channel_name, avdates) {
							var channel_row = "<tr class=\"vcm-acmp-channel-row vcm-acmp-channel-"+channel_name.replace(".", "").toLowerCase()+"\">";
							// adjust Airbnbapi to just Airbnb
							if (channel_name.toLowerCase() == 'airbnbapi') {
								channel_name = 'Airbnb';
							} else if (channel_name.toLowerCase() == 'googlehotel') {
								channel_name = 'Google Hotel';
							}
							//
							channel_row += "<td class=\"vcm-acmp-channel-cell\">"+channel_name+"</td>";
							for(var tdind = 1; tdind < tot_cells; tdind++) {
								var td_date = jQuery(row_cells[tdind]).attr("data-acmpdate");
								var td_vbounits = jQuery(row_cells[tdind]).attr("data-vbounits");
								// we no longer hide TDs
								// var td_visible = jQuery(row_cells[tdind]).is(":visible") ? "" : " style=\"display: none;\"";
								var td_visible = '';
								//
								var td_date_parts = td_date.split("-");
								if (avdates.hasOwnProperty(td_date)) {
									var td_roomclosed = parseInt(avdates[td_date]['Closed']) == 1 ? 'vcm-closedinventorytd ' : '';
									var td_unitsmismatch = parseInt(td_vbounits) != parseInt(avdates[td_date]['Inventory']) ? 'vcm-tdcomparemismatch ' : '';
									td_unitsmismatch = parseInt(avdates[td_date]['Closed']) == 1 && parseInt(td_vbounits) <= 0 ? '' : td_unitsmismatch;
									channel_row += "<td class=\""+td_roomclosed+td_unitsmismatch+"cell-"+parseInt(td_date_parts[2])+"-"+parseInt(td_date_parts[1])+"\""+td_visible+">"+avdates[td_date]['Inventory']+"</td>";
								} else {
									channel_row += "<td class=\"vcm-noinventorytd cell-"+parseInt(td_date_parts[2])+"-"+parseInt(td_date_parts[1])+"\""+td_visible+">---</td>";
								}
							}
							channel_row += "</tr>";
							vcm_room_row.after(channel_row);
						});
					});
				}
				/* Stop loading when sending ACMP_RQ to prevent double submit */
				vcmStopLoading();
				/*  */
			}).fail(function() {
				jQuery(".vcmsynchspan").removeClass("vcmsynchspanloading").addClass("vcmsynchspanerror");
				alert("Error Performing Ajax Request");
				/* Stop loading when sending ACMP_RQ to prevent double submit */
				vcmStopLoading();
				/*  */
			});
		});
		<?php
	$force_acmp_load = VikRequest::getInt('loadacmp', '', 'request');
	if ($force_acmp_load == 1 && !empty($this->acmp_last_request)) {
		?>
		jQuery("#vcmstartsynch").trigger("click");
		<?php
	}
	?>
	});
	</script>
	<?php
}
?>
</div>

<br clear="all" />

<div class="vcm-overviewtable-wrapper">
	<div class="vcm-table-responsive">
		<table class="vcmoverviewtable vcm-table">
			<tr class="vcmoverviewtablerowone">
				<td class="bluedays">
					<form action="index.php?option=com_vikchannelmanager&amp;task=oversight" method="post" name="vcmoverview">
						<div class="vcm-overview-datecmd-top">
							<div class="vcm-overview-datecmd-date">
								<div class="btn-group input-append" style="margin: 0;">
									<input type="text" name="datepicker" id="vcmdatepicker" class="vcmdatepicker" value="<?php echo date(VikChannelManager::getClearDateFormat(true), $this->tsstart); ?>" autocomplete="off"/><button type="button" class="btn" onclick="jQuery('#vcmdatepicker').focus();"><?php VikBookingIcons::e('calendar'); ?></button>
								</div>
							</div>
						</div>
						<input type="hidden" id="loadacmp" name="loadacmp" value="0" />
					</form>
				</td>
<?php
$mon = $nowts['mon'];
$cell_count = 0;

$weekend_arr = array(0, 6);

$start_day_id = 'cell-'.$nowts['mday'].'-'.$nowts['mon'];
$end_day_id = '';
while ($cell_count < $MAX_DAYS) {
	$style = '';
	if (false && $cell_count >= $MAX_TO_DISPLAY) {
		$style = 'style="display: none;"';
	} else {
		$end_day_id = 'cell-'.$nowts['mday'].'-'.$nowts['mon'];
	}
	$cell_classes = array('bluedays', 'cell-'.$nowts['mday'].'-'.$nowts['mon']);
	if (in_array((int)$nowts['wday'], $weekend_arr)) {
		array_push($cell_classes, 'vcm-tablewday-wend');
	}
	echo '<td class="' . implode(' ', $cell_classes) . '" '.$style.'><span class="vcm-oversight-tablemonth">'.$months_labels[$nowts['mon']-1].'</span><span class="vcm-oversight-tablemday">'.$nowts['mday'].'</span><span class="vcm-oversight-tablewday">'.$days_labels[$nowts['wday']].'</span></td>';
	$dayts = mktime(0, 0, 0, $nowts['mon'], ($nowts['mday'] + 1), $nowts['year']);
	$nowts = getdate($dayts);
	$cell_count++;
}
?>
			</tr>
<?php
foreach ($this->rows as $room) {
	$nowts 	= getdate($this->tsstart);
	$mon 	= $nowts['mon'];
	echo '<tr class="vcmoverviewtablerow" id="vcm-acmp-row-'.$room['id'].'">';
	echo '<td class="roomname"><span class="vcm-oversight-roomunits">'.$room['units'].'</span> <span class="vcm-oversight-roomname" data-vcmrid="'.$room['id'].'">'.$room['name'].'</span></td>';
	
	$room_max_units[$room['id']] = $room['units'];
	
	$cell_count = 0;
	while ($cell_count < $MAX_DAYS) {
		$dclass 	= 'notbusy';
		$dalt 		= '';
		$bid 		= '';
		$totfound 	= 0;
		if (is_array($this->arrbusy[$room['id']])) {
			foreach ($this->arrbusy[$room['id']] as $b) {
				$tmpone = getdate($b['checkin']);
				$ritts = mktime(0, 0, 0, $tmpone['mon'], $tmpone['mday'], $tmpone['year']);
				$tmptwo = getdate($b['checkout']);
				$conts = mktime(0, 0, 0, $tmptwo['mon'], $tmptwo['mday'], $tmptwo['year']);
				if ($nowts[0] >= $ritts && $nowts[0] < $conts) {
					$dclass = 'busy';
					$bid = $b['idorder'];
					if ($nowts[0] == $ritts) {
						$dalt = JText::_('VBPICKUPAT')." ".date('H:i', $b['checkin']);
					} elseif ($nowts[0] == $conts) {
						$dalt = JText::_('VBRELEASEAT')." ".date('H:i', $b['checkout']);
					}
					$totfound++;
				}
			}
		}
		$style = '';
		if (false && $cell_count >= $MAX_TO_DISPLAY) {
			$style = 'style="display: none;"';
		}

		$id_block = "cell-".$nowts['mday'].'-'.$nowts['mon']."-".$nowts['year']."-".$room['id'];
		$dclass .= ' day-block';

		$cell_ymd = date('Y-m-d', $nowts[0]);
		$cell_info = '';
		$has_blocked_date = false;
		// check if this room has a block-date rule for the OTAs on this day
		if (isset($block_dates[$cell_ymd]) && (!count($block_dates[$cell_ymd]['room_ids']) || in_array($room['id'], $block_dates[$cell_ymd]['room_ids']))) {
			// room-day has a block date rule (empty array room_ids means all rooms are affected)
			$cell_info = '<span class="vcm-otablockdate-roomday" data-ymd="' . $cell_ymd . '" data-rid="' . $room['id'] . '"><i class="' . VikBookingIcons::i('lock') . '"></i></span>';
			// push additional class
			$dclass .= ' vcm-otablockdate-cell';
			// turn flag on
			$has_blocked_date = true;
		}
		
		if ($totfound > 0 && $totfound < $room['units']) {
			$dlnk = $room['units'] - $totfound;
			$day_cont = $cell_info . $dlnk;
			$cal = "<td align=\"center\" $style class=\"".$dclass." cell-".$nowts['mday'].'-'.$nowts['mon']."\" id=\"".$id_block."\" data-vbounits=\"".$dlnk."\" data-acmpdate=\"".$cell_ymd."\">".$day_cont."</td>\n";
		} else if ($totfound >= $room['units']) {
			$dlnk = 0;
			$day_cont = $cell_info . $dlnk;
			$dclass = 'full day-block' . ($has_blocked_date ? ' vcm-otablockdate-cell' : '');
			$cal = "<td align=\"center\" $style class=\"".$dclass." cell-".$nowts['mday'].'-'.$nowts['mon']."\" id=\"".$id_block."\" data-vbounits=\"".$dlnk."\" data-acmpdate=\"".$cell_ymd."\">".$day_cont."</td>\n";
		} else {
			$dlnk = $room['units'];
			$day_cont = $cell_info . $dlnk;
			$cal = "<td align=\"center\" $style class=\"".$dclass." cell-".$nowts['mday'].'-'.$nowts['mon']."\" id=\"".$id_block."\" data-vbounits=\"".$dlnk."\" data-acmpdate=\"".$cell_ymd."\">".$day_cont."</td>\n";
		}
		
		echo $cal;
		$dayts = mktime(0, 0, 0, $nowts['mon'], ($nowts['mday'] + 1), $nowts['year']);
		$nowts = getdate($dayts);
		
		$cell_count++;
	}
	echo '</tr>';
}
?>
		</table>
	</div>
</div>
<button type="button" id="vcm-acmp-toggleall" style="display: none; margin-top: 10px;" class="btn btn-primary"><i class="vboicn-switch"></i><?php echo JText::_('VCMRATESPUSHTOGGLEALL'); ?></button>

<form action="index.php?option=com_vikchannelmanager" method="post" name="adminForm" id="adminForm">
<?php
if (isset($_REQUEST['e4j_debug']) && (int)$_REQUEST['e4j_debug'] == 1) {
	echo '<input type="hidden" name="e4j_debug" value="1" />'."\n";
}
?>
<input type="hidden" name="task" value="oversight" />
<?php echo '<br/>'.$this->navbut; ?>
</form>

<div id="dialog-confirm" title="<?php echo JText::_('VCMOSDIALOGTITLE');?>" style="display: none;">

	<div class="vcm-dialog-toolbar">
		<div class="vcm-dialog-tab-btn vcm-dialog-tab-btn-active">
			<a href="javascript: void(0);" onClick="dialogTabButtonClicked('avail', this);" id="vcm-tab-link-avail" class="active"><?php echo JText::_('VCMOSTAB1'); ?></a>
		</div>
		<div class="vcm-dialog-tab-btn">
			<a href="javascript: void(0);" onClick="dialogTabButtonClicked('rates', this);" id="vcm-tab-link-rates" class=""><?php echo JText::_('VCMOSTAB2'); ?></a>
		</div>
	</div>

	<div class="vcmos-dialog-fromto">
		<div class="vcmos-dialog-line">
			<span class="vcmos-dialog-info"><i class="vboicn-calendar"></i><?php echo JText::_('VCMOSFROMDATE'); ?></span>
			<span class="vcmos-dialog-value" id="vcmos-from"></span>
		</div>
		<div class="vcmos-dialog-line">
			<span class="vcmos-dialog-info"><i class="vboicn-calendar"></i><?php echo JText::_('VCMOSTODATE'); ?></span>
			<span class="vcmos-dialog-value" id="vcmos-to"></span>
		</div>
	</div>

	<div class="vcm-dialog-section" id="vcm-dialog-section-avail">
		
		<div class="vcmos-dialog-line">
			<span class="vcmos-dialog-param-info"><i class="vboicn-circle-down"></i><?php echo JText::_('VCMOSCLOSEUNITS'); ?></span>
			<span class="vcmos-dialog-param-check">
				<label for="vcmos-close-all"><i class="vboicn-blocked"></i><?php echo JText::_('VCMOSCLOSEALL'); ?></label>
				<input type="checkbox" id="vcmos-close-all" onChange="closeAllValueChanged();" value="1"/>
			</span>
			<span class="vcmos-dialog-param-value">
				<input type="number" value="0" min="0" max="9999" id="vcmos-close-units" style="width: 100px !important;" onChange="closeUnitsValueChanged();"/>
				&nbsp;<?php echo JText::_('VCMOSUNITSLABEL'); ?>
			</span>
		</div>
		
		<div class="vcmos-dialog-line">
			<span class="vcmos-dialog-param-info"><i class="vboicn-circle-up"></i><?php echo JText::_('VCMOSOPENUNITS'); ?></span>
			<span class="vcmos-dialog-param-check">
				<label for="vcmos-open-all"><i class="vboicn-enter"></i><?php echo JText::_('VCMOSOPENALL'); ?></label>
				<input type="checkbox" id="vcmos-open-all" onChange="openAllValueChanged();" value="1"/>
			</span>
			<span class="vcmos-dialog-param-value">
				<input type="number" value="0" min="0" max="9999" id="vcmos-open-units" style="width: 100px !important;" onChange="openUnitsValueChanged();"/>
				&nbsp;<?php echo JText::_('VCMOSUNITSLABEL'); ?>
			</span>
		</div>

	</div>

	<div class="vcm-dialog-section" id="vcm-dialog-section-rates" style="display: none;">

		<div class="vcmos-dialog-line vcmos-dialog-line-rates">
			<div class="vcmos-dialog-line-rates-alter">
				<div class="vcmos-dialog-line-rates-alter-exact">
					<label for="vcm-setnewrate"><?php echo JText::_('VCMOSSETNEWRATE'); ?></label>
					<span class="vcmos-setnewrate-currency"><?php echo $curr_symb; ?></span>
					<input id="vcm-setnewrate" type="number" min="0" step="any" value="0.00" onChange="ratesAmountValueChanged();" />
				</div>
				<div class="vcmos-dialog-line-rates-alter-or"><span><?php echo JText::_('VCMOR'); ?></span></div>
				<div class="vcmos-dialog-line-rates-alter-incrdecr">
					<select id="vcmos-rates-type">
						<option value="1"><?php echo JText::_('VCMOSINCREASERATES'); ?></option>
						<option value="-1"><?php echo JText::_('VCMOSDECREASERATES'); ?></option>
					</select>
					<span>&nbsp;<?php echo JText::_('VCMOSSYNCRATESBY'); ?>&nbsp;</span>
					<input type="number" value="0" min="0" step="any" id="vcmos-rates-amount" onChange="document.getElementById('vcm-setnewrate').value='0';ratesAmountValueChanged();"/>
					<select id="vcmos-rates-percentot">
						<option value="1">%</option>
						<option value="2"><?php echo $curr_symb; ?></option>
					</select>
				</div>
				<div class="vcmos-dialog-line-rates-alter-or vcmos-dialog-line-rates-restr"><span><?php echo JText::_('VCMOSSETRESTR'); ?></span></div>
				<div class="vcmos-dialog-line-rates-alter-restr">
					<div class="vcmos-dialog-line-rates-alter-restr-elem">
						<label for="vcm-newminlos"><?php echo JText::_('VCMRARRESTRMINLOS'); ?></label>
						<input id="vcm-newminlos" type="number" min="0" step="1" value="" onChange="ratesAmountValueChanged();" />
					</div>
					<div class="vcmos-dialog-line-rates-alter-restr-elem">
						<label for="vcm-newmaxlos"><?php echo JText::_('VCMRARRESTRMAXLOS'); ?></label>
						<input id="vcm-newmaxlos" type="number" min="0" step="1" value="" onChange="ratesAmountValueChanged();" />
					</div>
					<div class="vcmos-dialog-line-rates-alter-restr-elem">
						<label for="vcm-newcta"><?php echo JText::_('VCMRARRESTRCLOSEDARRIVAL'); ?></label>
						<input id="vcm-newcta" type="checkbox" value="1" onClick="ratesAmountValueChanged();" />
					</div>
					<div class="vcmos-dialog-line-rates-alter-restr-elem">
						<label for="vcm-newctd"><?php echo JText::_('VCMRARRESTRCLOSEDDEPARTURE'); ?></label>
						<input id="vcm-newctd" type="checkbox" value="1" onClick="ratesAmountValueChanged();" />
					</div>
				</div>
			</div>
			<div class="vcmos-dialog-line-rates-current"></div>
		</div>

	</div>

</div>

<a href="index.php?option=com_vikchannelmanager&task=editsmartbalancer&cid[]=%d" style="display: none;" id="vcm-base-smbal-link"></a>

<script type="text/javascript">
var vcm_currency = '<?php echo $curr_symb; ?>';
var months_labels = JSON.parse('<?php echo json_encode($months_labels); ?>');
var days_labels = JSON.parse('<?php echo json_encode($days_labels); ?>');
// block dates
var vcm_block_dates = JSON.parse('<?php echo is_array($block_dates) ? json_encode($block_dates) : '[]'; ?>');
</script>

<script type="text/javascript">

	var ROOM_MAX_UNITS = <?php echo json_encode($room_max_units); ?>;
	var TOT_CHANGES = 0;
	
	function openDialog() {
		var format = new Date().format;
		
		jQuery('#vcmos-from').html(listener.first.toDate(format));
		jQuery('#vcmos-to').html(listener.last.toDate(format));
		jQuery('#vcmos-close-units').val('0');
		jQuery('#vcmos-close-units').prop('readonly', false);
		jQuery('#vcmos-open-units').val('0');
		jQuery('#vcmos-open-units').prop('readonly', false);
		jQuery('#vcmos-close-all').prop('checked', false);
		jQuery('#vcmos-open-all').prop('checked', false);
		jQuery('.vcmos-dialog-line-rates-current').html("");

		populateDialogCurrentRates();
		
		jQuery( "#dialog-confirm" ).dialog({
			resizable: false,
			height: Math.round(jQuery(window).width() * 0.30),
			width: Math.round(jQuery(window).width() * 0.30),
			modal: true,
			close: function() {
				listener.clear();
				jQuery('.day-block').removeClass('block-picked-start block-picked-middle block-picked-end');
			},
			buttons: [
				{
					text: "<?php echo addslashes(JText::_('VCMOSDIALOGAPPLYBUTTON')); ?>",
					id: "dialog-apply-btn",
					class: "btn btn-success",
					click: function() {
						jQuery('.vcm-dialog-tab-btn a:not(#vcm-tab-link-'+dialogActiveTab+')').addClass('disabled');

						applyDialogChanges();
						jQuery( this ).dialog( "close" );
					}
				}, {
					text: "<?php echo addslashes(JText::_('VCMOSDIALOGCANCBUTTON')); ?>",
					class: "btn btn-light",
					click: function() {
						jQuery( this ).dialog( "close" );
					}
				}
			]
		});
	}

	function dialogTabButtonClicked(section, link) {

		if (jQuery(link).hasClass('disabled')) {
			return;
		}

		jQuery('.vcm-dialog-tab-btn').removeClass('vcm-dialog-tab-btn-active');
		jQuery('.vcm-dialog-tab-btn a').removeClass('active');
		jQuery(link).addClass('active').parent('.vcm-dialog-tab-btn').addClass('vcm-dialog-tab-btn-active');

		jQuery('.vcm-dialog-section').hide();
		jQuery('#vcm-dialog-section-'+section).show();

		dialogActiveTab = section;

		if (section == 'avail') {
			jQuery('.ui-dialog-title').text("<?php echo addslashes(JText::_('VCMOSDIALOGTITLE')); ?>");
			if (!inputHasChanged.avail) {
				// changeTextDialogApplyButton('<?php echo addslashes(JText::_('VCMOSDIALOGAPPLYAVBUTTON')); ?>');
			} else {
				changeTextDialogApplyButton('<?php echo addslashes(JText::_('VCMOSDIALOGAPPLYBUTTON')); ?>');
			}
		} else {
			jQuery('.ui-dialog-title').text("<?php echo addslashes(JText::_('VCMOSDIALOGTITLE2')); ?>");
			if (!inputHasChanged.rates) {
				// changeTextDialogApplyButton('<?php echo addslashes(JText::_('VCMOSDIALOGAPPLYRATESBUTTON')); ?>');
			} else {
				changeTextDialogApplyButton('<?php echo addslashes(JText::_('VCMOSDIALOGAPPLYBUTTON')); ?>');
			}
		}

	}

	var inputHasChanged = {avail: 0, rates: 0};
	var dialogActiveTab = 'avail';
	var lastApplyBtnText = '';

	function changeTextDialogApplyButton(txt) {
		jQuery('#dialog-apply-btn').text(txt);

		lastApplyBtnText = txt;
	}
	
	function closeUnitsValueChanged() {
		jQuery('#vcmos-open-units').prop('readonly', (jQuery('#vcmos-close-units').val() > 0 ? true : false) );

		changeTextDialogApplyButton("<?php echo addslashes(JText::_('VCMOSDIALOGAPPLYBUTTON')); ?>");
		inputHasChanged.avail = 1;
	}
	
	function openUnitsValueChanged() {
		jQuery('#vcmos-close-units').prop('readonly', (jQuery('#vcmos-open-units').val() > 0 ? true : false) );

		changeTextDialogApplyButton("<?php echo addslashes(JText::_('VCMOSDIALOGAPPLYBUTTON')); ?>");
		inputHasChanged.avail = 1;
	}
	
	function closeAllValueChanged() {
		var is = jQuery('#vcmos-close-all').is(':checked');
		jQuery('#vcmos-open-all').prop('checked', false);
		jQuery('#vcmos-close-units, #vcmos-open-units').prop('readonly', (is ? true : false));
		jQuery('#vcmos-close-units').val( ROOM_MAX_UNITS[listener.first.room] );
		jQuery('#vcmos-open-units').val(0);

		changeTextDialogApplyButton("<?php echo addslashes(JText::_('VCMOSDIALOGAPPLYBUTTON')); ?>");
		inputHasChanged.avail = 1;
	}
	
	function openAllValueChanged() {
		var is = jQuery('#vcmos-open-all').is(':checked');
		jQuery('#vcmos-close-all').prop('checked', false);
		jQuery('#vcmos-close-units, #vcmos-open-units').prop('readonly', (is ? true : false));
		jQuery('#vcmos-open-units').val( ROOM_MAX_UNITS[listener.first.room] );
		jQuery('#vcmos-close-units').val(0);

		changeTextDialogApplyButton("<?php echo addslashes(JText::_('VCMOSDIALOGAPPLYBUTTON')); ?>");
		inputHasChanged.avail = 1;
	}

	function ratesAmountValueChanged() {

		changeTextDialogApplyButton("<?php echo addslashes(JText::_('VCMOSDIALOGAPPLYBUTTON')); ?>");
		inputHasChanged.rates = 1;

	}

	function applyDialogChanges() {
		var all_blocks = getAllBlocksBetween(listener.first, listener.last, true);
		if (all_blocks === false) {
			return false;
		}

		if (dialogActiveTab == 'avail') {
			changeAvailability(all_blocks);
		} else {
			changeRates(all_blocks);
		}

		increaseChanges();

	}

	function populateDialogCurrentRates() {
		var all_blocks = getAllBlocksBetween(listener.first, listener.last, true);
		if (all_blocks === false) {
			return false;
		}
		var date_start = listener.first.toDate("yy-mm-dd");
		var date_end = listener.last.toDate("yy-mm-dd");
		var id_room = listener.first.room;

		var jqxhr = jQuery.ajax({
			type: "POST",
			url: "index.php",
			data: {
				option: "com_vikchannelmanager",
				task: "get_vbo_dayrates",
				from: date_start,
				to: date_end,
				room: id_room,
				tmpl: "component"
			}
		}).done(function(res) {
			if (res.substr(0, 9) == 'e4j.error') {
				console.log(res.replace("e4j.error.", ""));
			} else {
				var cur_rates = JSON.parse(res);
				var ratescont = '';
				for(var day in cur_rates) {
					if (!cur_rates.hasOwnProperty(day)) {
						continue;
					}
					var dayobj = new Date(day);
					ratescont += '<div class="vcm-ovs-curdayrate-block">';
					ratescont += '	<div class="vcm-ovs-curdayrate-inner">';
					ratescont += '		<div class="vcm-ovs-curdayrate-day">'+months_labels[dayobj.getMonth()]+" "+dayobj.getDate()+" "+days_labels[dayobj.getDay()]+'</div>';
					ratescont += '		<div class="vcm-ovs-curdayrate-rates">';
					for(var tarid in cur_rates[day]) {
						if (!cur_rates[day].hasOwnProperty(tarid)) {
							continue;
						}
						var tarname = cur_rates[day][tarid].name;
						ratescont += '		<div class="vcm-ovs-curdayrate-rates-inner">';
						ratescont += '			<div class="vcm-ovs-curdayrate-ratename">'+(tarname.length > 10 ? tarname.substring(0, 10)+'.' : tarname)+'</div>';
						ratescont += '			<div class="vcm-ovs-curdayrate-rateprice"><span class="vcm-ovs-curdayrate-rateprice-currency">'+vcm_currency+'</span><span class="vcm-ovs-curdayrate-rateprice-cost">'+cur_rates[day][tarid].cost+'</span></div>';
						ratescont += '		</div>';
					}
					ratescont += '		</div>';
					ratescont += '	</div>';
					ratescont += '</div>';
				}
				jQuery('.vcmos-dialog-line-rates-current').html(ratescont);
			}
		}).fail(function() {
			console.log("Error Performing Ajax Request");
		});
	}
	
	function changeAvailability(all_blocks) {
		
		var new_units = 0;
		var close_units = Math.max(0, parseInt(jQuery('#vcmos-close-units').val()) );
		var open_units = Math.max(0, parseInt(jQuery('#vcmos-open-units').val()) );
		if (close_units > 0) {
			new_units = close_units;
		} else {
			new_units -= open_units;
		}
		
		jQuery.each(all_blocks, function(k, v){
			var units = parseInt( v.html() );
			var res = Math.max( 0, Math.min( ROOM_MAX_UNITS[listener.first.room], (units-new_units) ) );
			v.html(res);
			
			v.removeClass('busy notbusy equal full').addClass('tomodify');
			if (res == 0) {
				v.addClass('full');
			} else if (res == ROOM_MAX_UNITS[listener.first.room]) {
				v.addClass('notbusy equal');
			} else {
				v.addClass('busy');
			}
			
			storeDay(v.attr('id'), res, v.attr('data-vbounits'));
		});
		
	}

	function changeRates(all_blocks) {

		var type = parseInt(jQuery('#vcmos-rates-type').val());
		var amount = parseFloat(jQuery('#vcmos-rates-amount').val());
		var percentot = parseInt(jQuery('#vcmos-rates-percentot').val());
		var exactcost = parseFloat(jQuery('#vcm-setnewrate').val());
		var minlos = parseInt(jQuery('#vcm-newminlos').val());
		var maxlos = parseInt(jQuery('#vcm-newmaxlos').val());
		var cta = jQuery('#vcm-newcta').is(':checked') ? 1 : 0;
		var ctd = jQuery('#vcm-newctd').is(':checked') ? 1 : 0;
		type = (type > 0 ? 'I' : 'D');
		var saytype = (type == 'I' ? '+ ' : '- ');
		if (exactcost > 0) {
			amount = exactcost;
			percentot = 2;
			type = 'E';
			saytype = '';
		}
		var restr_str = '-%min-%max-%cta-%ctd';
		var restr_detailmode = '';
		if (!isNaN(minlos) && minlos > 0) {
			restr_str = restr_str.replace('%min', minlos);
			restr_detailmode = ' (R)';
		} else {
			restr_str = restr_str.replace('%min', '');
		}
		if (!isNaN(maxlos) && maxlos > 0) {
			restr_str = restr_str.replace('%max', maxlos);
			restr_detailmode = ' (R)';
		} else {
			restr_str = restr_str.replace('%max', '');
		}
		if (cta > 0) {
			restr_str = restr_str.replace('%cta', cta);
			restr_detailmode = ' (R)';
		} else {
			restr_str = restr_str.replace('%cta', '');
		}
		if (ctd > 0) {
			restr_str = restr_str.replace('%ctd', ctd);
			restr_detailmode = ' (R)';
		} else {
			restr_str = restr_str.replace('%ctd', '');
		}
		
		jQuery.each(all_blocks, function(k, v) {
			v.addClass('tomodify');
			v.addClass('vcm-detailsmod');

			var say_alter = saytype + (percentot > 1 ? (vcm_currency + ' ') : '') + amount + (percentot > 1 ? '' : ' %') + restr_detailmode;
			v.attr('data-detailsmod', say_alter);

			storeDay(v.attr('id'), amount, type + percentot + restr_str);
		});
	}
	
	function storeDay(id, units, vbounits) {
		var input = jQuery('#input-'+id);
		if (input.length > 0) {
			input.val(id+'-'+units+'-'+vbounits);
		} else {
			jQuery('#adminForm').append('<input type="hidden" name="cust_av[]" id="input-'+id+'" value="'+id+'-'+units+'-'+vbounits+'"/>');
		}
	}

	function increaseChanges() {
		TOT_CHANGES++;
		jQuery('#vcm-totchanges').text(TOT_CHANGES).parent('div').show();
	}

	Joomla.submitbutton = function(task) {
		if (task == 'confirmcustoma' && dialogActiveTab == 'rates') {
			task = 'confirmcustomr';
		}
		Joomla.submitform(task, document.adminForm);
	}
   
   /////////////////////////////////
	
	var _START_DAY_ = '<?php echo $start_day_id; ?>';
	var _END_DAY_ = '<?php echo $end_day_id; ?>';
	
	function prevDay() {
		if (canPrev(_START_DAY_)) {
			jQuery('.'+_START_DAY_).prev().show();
			jQuery('.'+_END_DAY_).hide();
			
			if (canPrev(_START_DAY_)) {
				var start = jQuery('.'+_START_DAY_).first();
				var end = jQuery('.'+_END_DAY_).first();
				
				_START_DAY_ = start.prev().prop('class').split(' ')[1];
				_END_DAY_ = end.prev().prop('class').split(' ')[1];
				
				return true;
			} 
		}
		
		return false;
	}
	
	function nextDay() {
		if (canNext(_END_DAY_)) {
			jQuery('.'+_START_DAY_).hide();
			jQuery('.'+_END_DAY_).next().show();
			
			if (canNext(_END_DAY_)) {
				var start = jQuery('.'+_START_DAY_).first();
				var end = jQuery('.'+_END_DAY_).first();
				
				_START_DAY_ = start.next().prop('class').split(' ')[1];
				_END_DAY_ = end.next().prop('class').split(' ')[1];
				
				return true;
			} 
		}
		
		return false;
	}
	
	function prevWeek() {
		var i = 0;
		while ( i++ < 7 && prevDay() );
	}
	
	function nextWeek() {
		var i = 0;
		while ( i++ < 7 && nextDay() );
	}
	
	function canPrev(start) {
		return ( jQuery('.'+start).first().prev().prop('class').split(' ').length > 1 );
	}
	
	function canNext(end) {
		return ( jQuery('.'+end).first().next().length > 0 );
	}
	
	/////////////////////////////////
	
	var listener = null;
	
	jQuery(document).ready(function() {
		
		listener = new CalendarListener();
		
		jQuery('.day-block').click(function(){
			pickBlock( jQuery(this).attr('id') );
		});
		
		jQuery('.day-block').hover(
			function() {
				if (listener.isFirstPicked() && !listener.isLastPicked()) {
					var struct = initBlockStructure(jQuery(this).attr('id'));
					var all_blocks = getAllBlocksBetween(listener.first, struct, false);
					if (all_blocks !== false) {
						jQuery.each(all_blocks, function(k, v){
							if (!v.hasClass('block-picked-middle')) {
								v.addClass('block-picked-middle');
							}
						});
						jQuery(this).addClass('block-picked-end');
					}
				}
			},
			function() {
				if (!listener.isLastPicked()) {
					jQuery('.day-block').removeClass('block-picked-middle block-picked-end');
				}
			}
		);
		
		jQuery(document).keydown(function(e){
			if (e.altKey === true) {
				hold_alt = true;
				jQuery('#vcm-acmp-toggleall').show();
			}
			if (e.keyCode == 27) {
				listener.clear();
				jQuery('.day-block').removeClass('block-picked-start block-picked-middle block-picked-end');
			}
		});
	});
	
	function pickBlock(id) {
		var struct = initBlockStructure(id);
		
		if (!listener.pickFirst(struct)) {
			// first already picked
			if ((listener.first.isBeforeThan(struct) || listener.first.isSameDay(struct)) && listener.first.isSameRoom(struct)) {
				// last > first : pick last
				if (listener.pickLast(struct)) {
					var all_blocks = getAllBlocksBetween(listener.first, listener.last, false);
					if (all_blocks !== false) {
						jQuery.each(all_blocks, function(k, v){
							if (!v.hasClass('block-picked-middle')) {
								v.addClass('block-picked-middle');
							}
						});
						jQuery('#'+listener.last.id).addClass('block-picked-end');
						openDialog();
					}
				}
			} else {
				// last < first : clear selection
				listener.clear();
				jQuery('.day-block').removeClass('block-picked-start block-picked-middle block-picked-end');
			}
		} else {
			// first picked
			jQuery('#'+listener.first.id).addClass('block-picked-start');
		}
	}
	
	function getAllBlocksBetween(start, end, outers_included) {
		if (!start.isSameRoom(end)) {
			return false;
		}
		
		if (start.isAfterThan(end)) {
			return false;
		}
		
		var queue = new Array();
		
		if (outers_included) {
			queue.push(jQuery('#'+start.id));
		}
		
		if (start.isSameDay(end)) {
			return queue;
		}
		
		var node = jQuery('#'+end.id).prev();
		var start_id = jQuery('#'+start.id).attr('id');
		while (node.length > 0 && node.attr('id') != start_id) {
			queue.push(node);
			node = node.prev();
		}
		
		if (outers_included) {
			queue.push(jQuery('#'+end.id));
		}
		
		return queue;
	}
	
	function initBlockStructure(id) {
		var s = id.split("-");
		if (s.length != 5) {
			return {};
		}
		
		return {
			"day":parseInt(s[1]),
			"month":parseInt(s[2]),
			"year":parseInt(s[3]),
			"room":s[4],
			"id":id,
			"isSameDay" : function(block) {
				return ( this.month == block.month && this.day == block.day && this.year == block.year );
			},
			"isBeforeThan" : function(block) {
				return ( 
					( this.year < block.year ) || 
					( this.year == block.year && this.month < block.month ) || 
					( this.year == block.year &&  this.month == block.month && this.day < block.day ) );
			},
			"isAfterThan" : function(block) {
				return ( 
					( this.year > block.year ) || 
					( this.year == block.year && this.month > block.month ) || 
					( this.year == block.year && this.month == block.month && this.day > block.day ) );
			},
			"isSameRoom" : function(block) {
				return ( this.room == block.room );
			},
			"toDate" : function(format) {
				return format.replace(
					'dd', ( this.day < 10 ? '0' : '' )+this.day
				).replace(
					'mm', ( this.month < 10 ? '0' : '' )+this.month
				).replace(
					'yy', this.year
				);
			}
		};
	}
	
	function CalendarListener() {
		this.first = null;
		this.last = null;
	}
	
	CalendarListener.prototype.pickFirst = function(struct) {
		if (!this.isFirstPicked()) {
			this.first = struct;
			return true;
		}
		return false;
	}
	
	CalendarListener.prototype.pickLast = function(struct) {
		if (!this.isLastPicked() && this.isFirstPicked()) {
			this.last = struct;
			return true;
		}
		return false;
	}
	
	CalendarListener.prototype.clear = function() {
		this.first = null;
		this.last = null;
	}
	
	CalendarListener.prototype.isFirstPicked = function() {
		return this.first != null;
	}
	
	CalendarListener.prototype.isLastPicked = function() {
		return this.last != null;
	}
	
</script>
