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

$vik = new VikApplication(VersionListener::getID());
$df = VikChannelManager::getClearDateFormat();
$debug_val = VikRequest::getInt('e4j_debug', '', 'request');

if (class_exists('VikBooking') && method_exists('VikBooking', 'getVboApplication')) {
	//load BS Modal
	$vbo_app = VikBooking::getVboApplication();
	echo $vbo_app->getJmodalScript();
	echo $vbo_app->getJmodalHtml('vcm-smartbal-logs', JText::_('VCMSMARTBALRLOGS'));
}

if (count($this->rows)) {
	$show_bookings_stats = false;
	foreach ($this->rows as $v) {
		if ($v['type'] == 'rt') {
			$show_bookings_stats = true;
			break;
		}
	}
	if ($show_bookings_stats) {
		?>
<div style="width: 100%; display: inline-block;" class="btn-toolbar" id="filter-bar">
	<div class="btn-group pull-left">
		<button type="button" class="btn btn-success" onclick="vcmOpenWizard();"><i class="vboicn-magic-wand"></i> <?php echo JText::_('VCMSMARTBALOPENWIZARD'); ?></button>
	</div>
	<div class="btn-group pull-right">
		<a class="btn btn-primary" href="index.php?option=com_vikchannelmanager&task=smartbalancerstats"><i class="vboicn-stats-dots"></i> <?php echo JText::_('VCMSMARTBALRSTATS'); ?></a>
	</div>
</div>
		<?php
	}
?>

<form action="index.php?option=com_vikchannelmanager" method="post" name="adminForm" id="adminForm" class="vcm-list-form">
	<div class="table-responsive">
		<table cellpadding="4" cellspacing="0" border="0" width="100%" class="<?php echo $vik->getAdminTableClass(); ?> vcm-list-table">
		<?php echo $vik->openTableHead(); ?>
			<tr>
				<th width="20">
					<?php echo $vik->getAdminToggle(count($this->rows)); ?>
				</th>
				<th class="title" width="50"><?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'id', $this->orderingDir, $this->ordering); ?></th>
				<th class="title" width="200"><?php echo JHtml::_('grid.sort', 'VCMSMARTBALRNAME', 'name', $this->orderingDir, $this->ordering); ?></th>
				<th class="title" width="200"><?php echo JHtml::_('grid.sort', 'VCMSMARTBALRTYPE', 'type', $this->orderingDir, $this->ordering); ?></th>
				<th class="title center" width="100" align="center"><?php echo JHtml::_('grid.sort', 'VCMFROMDATE', 'from_ts', $this->orderingDir, $this->ordering); ?></th>
				<th class="title center" width="100" align="center"><?php echo JHtml::_('grid.sort', 'VCMTODATE', 'to_ts', $this->orderingDir, $this->ordering); ?></th>
				<th class="title center" width="75" align="center"><?php echo JText::_('VCMSMARTBALRROOMSAFF'); ?></th>
				<th class="title center" width="100" align="center"><?php echo JText::_('VCMSMARTBALRLASTEXEC'); ?></th>
				<th class="title center" width="75" align="center"><?php echo JText::_('VCMSMARTBALRLOGS'); ?></th>
			</tr>
		<?php echo $vik->closeTableHead(); ?>
		<?php
		$k = 0;
		$i = 0;
		for ($i = 0, $n = count($this->rows); $i < $n; $i++) {
			$row = $this->rows[$i];
			$rule_logs = !empty($row['logs']) ? json_decode($row['logs'], true) : array();
			$rule_data = json_decode($row['rule']);
			$say_rule_type = $row['type'] == 'av' ? JText::_('VCMSMARTBALRTYPEAV') : JText::_('VCMSMARTBALRTYPERATES');
			if ($row['type'] == 'av' && is_object($rule_data) && isset($rule_data->type)) {
				if ($rule_data->type == 'limit') {
					$say_rule_type .= ' <span class="badge badge-info">' . JText::_('VCM_SMBAL_AVTYPE_LIMIT') . '</span>';
				} elseif ($rule_data->type == 'units') {
					$say_rule_type .= ' <span class="badge badge-info">' . JText::_('VCM_SMBAL_AVTYPE_UNITS') . '</span>';
				} elseif ($rule_data->type == 'block') {
					$say_rule_type .= ' <span class="badge badge-error">' . JText::_('VCM_BLOCK_DATES') . '</span>';
				}
			}
			?>
			<tr class="row<?php echo $k; ?>">
				<td><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row['id']; ?>" onClick="<?php echo $vik->checkboxOnClick(); ?>"></td>
				<td><a href="index.php?option=com_vikchannelmanager&task=editsmartbalancer&cid[]=<?php echo $row['id']; ?>"><?php echo $row['id']; ?></a></td>
				<td><a href="index.php?option=com_vikchannelmanager&task=editsmartbalancer&cid[]=<?php echo $row['id']; ?>"><?php echo htmlentities($row['name']); ?></a></td>
				<td><?php echo $say_rule_type; ?></td>
				<td class="center"><?php echo date($df, $row['from_ts']); ?></td>
				<td class="center"><?php echo date($df, $row['to_ts']); ?></td>
				<td class="center"><span title="<?php echo implode(', ', $row['rooms_aff']); ?>"><?php echo count($row['rooms_aff']); ?></span></td>
				<td class="center">
				<?php
				if (is_array($rule_logs) && count($rule_logs)) {
					foreach ($rule_logs as $rlog) {
						echo VikChannelManager::formatDate(JFactory::getDate(date('Y-m-d H:i:s', $rlog['ts']), date_default_timezone_get()));
						break;
					}
				} else {
					echo '-----';
				}
				?>
				</td>
				<td class="center">
				<?php
				if (!empty($row['logs']) || $debug_val == 1) {
					?>
					<a href="javascript: void(0);" onclick="vboOpenJModal('vcm-smartbal-logs', 'index.php?option=com_vikchannelmanager&task=smartbalancerlogs&cid[]=<?php echo $row['id'].($debug_val == 1 ? '&e4j_debug=1' : ''); ?>&tmpl=component');" class="vcm-smartbal-openlogs">
						<i class="vboicn-file-text vcm-smartbal-iconlogs"></i>
					</a>
					<?php
				} else {
					?>
					<i class="vboicn-file-text vcm-smartbal-iconlogs"></i>
					<?php
				}
				?>
				</td>
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
		</table>
	</div>
	<input type="hidden" name="filter_order" value="<?php echo $this->ordering; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->orderingDir; ?>" />
	<input type="hidden" name="option" value="com_vikchannelmanager" />
	<input type="hidden" name="task" value="smartbalancer" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<?php echo '<br/>'.$this->navbut; ?>
</form>
<?php
} else {
	?>
<div style="width: 100%; display: inline-block;" class="btn-toolbar" id="filter-bar">
	<div class="btn-group pull-left">
		<button type="button" class="btn btn-success" onclick="vcmOpenWizard();"><i class="vboicn-magic-wand"></i> <?php echo JText::_('VCMSMARTBALOPENWIZARD'); ?></button>
	</div>
</div>
<p class="warn"><?php echo JText::_('VCMSMARTBALNORULES'); ?></p>
<div>
	<a class="btn vcm-config-btn" href="index.php?option=com_vikchannelmanager&task=newsmartbalancer"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::_('NEW'); ?></a>
</div>
<form action="index.php?option=com_vikchannelmanager" method="post" name="adminForm" id="adminForm">
	<input type="hidden" name="option" value="com_vikchannelmanager" />
	<input type="hidden" name="task" value="" />
</form>
	<?php
}
?>
<div class="vcm-loading-overlay">
	<div class="vcm-loading-dot vcm-loading-dot1"></div>
	<div class="vcm-loading-dot vcm-loading-dot2"></div>
	<div class="vcm-loading-dot vcm-loading-dot3"></div>
	<div class="vcm-loading-dot vcm-loading-dot4"></div>
	<div class="vcm-loading-dot vcm-loading-dot5"></div>
</div>
<div class="vcm-info-overlay-block">
	<div class="vcm-info-overlay-content vcm-info-overlay-content-smbal">
		<h3><?php echo JText::_('VCMSMARTBALWIZARD'); ?></h3>
		<p class="vcm-smbal-wizard-desc"><?php echo JText::_('VCMSMARTBALDESCWIZARD'); ?></p>
		<div class="vcm-smbal-wizard-content">
			<h4><?php echo JText::_('VCMFESTCHOOSEHELP'); ?></h4>
			<form action="index.php?option=com_vikchannelmanager" method="post" id="vcmWizardForm">
				<select id="vcmWizardRegion" onchange="vcmWizardChangeRegion(this.value);">
					<option value=""><?php echo JText::_('VCMFESTCHOOSEREGION'); ?></option>
				</select>
				<select id="vcmWizardFest" onchange="vcmWizardChangeFest(this.value);">
					<option value=""><?php echo JText::_('VCMFESTCHOOSEFEST'); ?></option>
				</select>
				<input type="hidden" name="task" value="newsmartbalancer" />
				<input type="hidden" name="wizard_fest" id="wizard_fest" value="" />
				<input type="hidden" name="wizard_in_days" id="wizard_in_days" value="" />
				<input type="hidden" name="wizard_min_gtlt" id="wizard_min_gtlt" value="" />
				<input type="hidden" name="wizard_from_date" id="wizard_from_date" value="" />
				<input type="hidden" name="wizard_to_date" id="wizard_to_date" value="" />
				<input type="hidden" name="wizard_radjustment" id="wizard_radjustment" value="" />
				<div class="vcm-smbal-wizard-dates">
					<h4 id="vcmWizardFestInfo"></h4>
					<p><?php echo JText::_('VCMFESTDATESRECOMM'); ?></p>
					<div class="vcm-smbal-wizard-recommdates"></div>
					<div class="vcm-smbal-wizard-occupancy"></div>
					<button type="submit" class="btn btn-large btn-success vcm-wizard-complete"><?php echo JText::_('VCMSMARTBALWIZCOMPLETE'); ?></button>
				</div>
			</form>
		</div>
	</div>
</div>
<script type="text/javascript">
/* Loading Overlay */
function vcmShowLoading() {
	jQuery(".vcm-loading-overlay").show();
}
function vcmHideLoading() {
	jQuery(".vcm-loading-overlay").hide();
}
var vcm_overlay_on = false;
var vcm_occ_loader = null;
function vcmEmptySelectOptions(selector) {
	if (jQuery(selector).length) {
		jQuery(selector+' option').each(function() {
			if (jQuery(this).val().length) {
				jQuery(this).remove();
			}
		});
	}
}
function vcmCloseModal() {
	jQuery(".vcm-info-overlay-block").fadeOut(400, function() {
		jQuery(this).attr("class", "vcm-info-overlay-block");
		vcmEmptySelectOptions('#vcmWizardRegion');
		vcmEmptySelectOptions('#vcmWizardFest');
	});
	vcm_overlay_on = false;
}
function vcmOpenWizard() {
	jQuery('.vcm-smbal-wizard-dates').hide();
	jQuery(".vcm-info-overlay-block").fadeIn(400, function() {
		vcm_overlay_on = true;
		vcmShowLoading();
		var jqxhr = jQuery.ajax({
			type: "POST",
			url: "index.php",
			data: { option: "com_vikchannelmanager", task: "load_fests_regions", tmpl: "component" }
		}).done(function(res) {
			if(res.substr(0, 9) == 'e4j.error') {
				alert(res.replace("e4j.error.", ""));
			}else {
				var vcm_wizard_regions = JSON.parse(res);
				vcmEmptySelectOptions('#vcmWizardRegion');
				vcmEmptySelectOptions('#vcmWizardFest');
				for (var prop in vcm_wizard_regions) {
					if (vcm_wizard_regions.hasOwnProperty(prop)) {
						jQuery('#vcmWizardRegion').append("<option value=\""+prop+"\">"+vcm_wizard_regions[prop]+"</option>");
					}
				}
			}
			vcmHideLoading();
		}).fail(function() {
			alert("Error Performing Ajax Request");
			vcmHideLoading();
		});
	});
}
function vcmWizardChangeRegion(region) {
	if (!region.length) {
		jQuery('.vcm-smbal-wizard-dates').hide();
		vcmEmptySelectOptions('#vcmWizardFest');
		return true;
	}
	vcmShowLoading();
	vcmEmptySelectOptions('#vcmWizardFest');
	jQuery('#vcmWizardFest').val('').trigger('change');
	var jqxhr = jQuery.ajax({
		type: "POST",
		url: "index.php",
		data: { option: "com_vikchannelmanager", task: "load_festivities", region: region, tmpl: "component" }
	}).done(function(res) {
		if(res.substr(0, 9) == 'e4j.error') {
			alert(res.replace("e4j.error.", ""));
		}else {
			var vcm_wizard_fests = JSON.parse(res);
			for (var prop in vcm_wizard_fests) {
				if (vcm_wizard_fests.hasOwnProperty(prop)) {
					jQuery('#vcmWizardFest').append("<option value=\""+prop+"\" data-next-day=\""+vcm_wizard_fests[prop]['next_day']+"\" data-from-day=\""+vcm_wizard_fests[prop]['from_day']+"\" data-to-day=\""+vcm_wizard_fests[prop]['to_day']+"\" data-week-day=\""+vcm_wizard_fests[prop]['week_day']+"\" data-fromweek-day=\""+vcm_wizard_fests[prop]['from_week_day']+"\" data-toweek-day=\""+vcm_wizard_fests[prop]['to_week_day']+"\" title=\""+vcm_wizard_fests[prop]['date_diff']+"\">"+vcm_wizard_fests[prop]['trans_name']+"</option>");
				}
			}
		}
		vcmHideLoading();
	}).fail(function() {
		alert("Error Performing Ajax Request");
		vcmHideLoading();
	});
}
function vcmWizardChangeFest(fest) {
	if (!fest.length) {
		jQuery('.vcm-smbal-wizard-dates').hide();
		return true;
	}
	var optelem = jQuery('#vcmWizardFest option:selected');
	var next_day = optelem.attr('data-next-day');
	var date_diff = optelem.attr('title');
	var fest_info = optelem.text()+' - '+optelem.attr('data-week-day')+', '+next_day+(next_day != date_diff ? ' ('+date_diff+')' : '');
	jQuery('#vcmWizardFestInfo').text(fest_info);

	var from_day = optelem.attr('data-from-day');
	var to_day = optelem.attr('data-to-day');
	var recomm_dates = '<span class="vcm-smbal-wizard-favdate"><span class="vcm-smbal-wizard-favdate-wday">'+optelem.attr('data-fromweek-day')+'</span><span class="vcm-smbal-wizard-favdate-day">'+from_day+'</span></span>';
	if (from_day != to_day) {
		recomm_dates += ' - <span class="vcm-smbal-wizard-favdate"><span class="vcm-smbal-wizard-favdate-wday">'+optelem.attr('data-toweek-day')+'</span><span class="vcm-smbal-wizard-favdate-day">'+to_day+'</span></span>';
	}
	jQuery('.vcm-smbal-wizard-recommdates').html(recomm_dates);
	jQuery('#wizard_fest').val(optelem.text());
	jQuery('#wizard_from_date').val(from_day);
	jQuery('#wizard_to_date').val(to_day);

	//load occupancy
	vcmWizardLoadOccupancyFest(from_day, to_day);
	//

	jQuery('.vcm-smbal-wizard-dates').show();
}
function vcmWizardLoadOccupancyFest(from_day, to_day) {
	jQuery('.vcm-smbal-wizard-occupancy').html('<span class="vcm-smbal-wizard-loadocc"><?php echo addslashes(JText::_('VCMSMARTBALWIZARDLOADOCC')); ?> <span id="vcmWizardOccload">.</span></span>');
	//stop and start loading dots for occupancy
	vcmWizardOccLoadingStop();
	jQuery('.vcm-smbal-wizard-occupancy').addClass('vcm-smbal-wizard-occupancy-loading');
	vcm_occ_loader = setInterval(vcmWizardOccLoading, 400);
	//
	var jqxhr = jQuery.ajax({
		type: "POST",
		url: "index.php",
		data: { option: "com_vikchannelmanager", task: "load_fest_occupancy", from_date: from_day, to_date: to_day, tmpl: "component" }
	}).done(function(res) {
		if(res.substr(0, 9) == 'e4j.error') {
			alert(res.replace("e4j.error.", ""));
			jQuery('.vcm-smbal-wizard-occupancy').html("----");
		}else {
			var vcm_wizard_occ_res = JSON.parse(res);
			var vcm_occ_resp_str = '';
			vcm_occ_resp_str += '<span class="vcm-smbal-wizard-occ-resp"><span class="vcm-smbal-wizard-occ-resp-key"><?php echo addslashes(JText::_('VCMSMARTBALWIZARDTOTDAYS')); ?>:</span><span class="vcm-smbal-wizard-occ-resp-val">'+vcm_wizard_occ_res['fest_num_days']+'</span></span>';
			vcm_occ_resp_str += '<span class="vcm-smbal-wizard-occ-resp"><span class="vcm-smbal-wizard-occ-resp-key"><?php echo addslashes(JText::_('VCMSMARTBALWIZARDTOTBOOKS')); ?>:</span><span class="vcm-smbal-wizard-occ-resp-val">'+vcm_wizard_occ_res['all_bookings'].length+'</span></span>';
			vcm_occ_resp_str += '<span class="vcm-smbal-wizard-occ-resp"><span class="vcm-smbal-wizard-occ-resp-key"><?php echo addslashes(JText::_('VCMSMARTBALWIZARDNBOOKED')); ?>:</span><span class="vcm-smbal-wizard-occ-resp-val">'+vcm_wizard_occ_res['nights_booked']+'</span></span>';
			vcm_occ_resp_str += '<span class="vcm-smbal-wizard-occ-resp"><span class="vcm-smbal-wizard-occ-resp-key"><?php echo addslashes(JText::_('VCMSMARTBALWIZARDGLOBOCC')); ?>:</span><span class="vcm-smbal-wizard-occ-resp-val">'+vcm_wizard_occ_res['pcent_occupied']+'%</span></span>';
			jQuery('.vcm-smbal-wizard-occupancy').html(vcm_occ_resp_str);
			//suggestion
			if (vcm_wizard_occ_res['suggestionmsg'].length) {
				jQuery('#wizard_radjustment').val(vcm_wizard_occ_res['suggestion']);
				jQuery('.vcm-smbal-wizard-occupancy').append('<div class="vcm-smbal-wizard-rates-suggestion"><span><i class="vboicn-star-full"></i> '+(vcm_wizard_occ_res['suggestionmsg']+'').replace('%s', (vcm_wizard_occ_res['suggestion']+'').substr(1))+'</span></div>');
			} else {
				jQuery('#wizard_radjustment').val('');
			}
			jQuery('#wizard_in_days').val(vcm_wizard_occ_res['in_days']);
			jQuery('#wizard_min_gtlt').val(vcm_wizard_occ_res['min_gtlt']);
		}
		vcmWizardOccLoadingStop();
	}).fail(function() {
		alert("Error Performing Ajax Request");
		vcmWizardOccLoadingStop();
		jQuery('.vcm-smbal-wizard-occupancy').html("----");
	});
}
function vcmWizardOccLoading() {
	var load_dots = jQuery('#vcmWizardOccload').text().length;
	if (load_dots > 3) {
		load_dots = 1;
	} else {
		load_dots++;
	}
	var dotstr = '';
	for (var i = 1; i <= load_dots; i++) {
		dotstr += '.';
	}
	jQuery('#vcmWizardOccload').text(dotstr);
}
function vcmWizardOccLoadingStop() {
	jQuery('.vcm-smbal-wizard-occupancy').removeClass('vcm-smbal-wizard-occupancy-loading');
	if (vcm_occ_loader !== null) {
		clearInterval(vcm_occ_loader);
		vcm_occ_loader = null;
	}
}
jQuery(document).ready(function() {
	jQuery(document).mouseup(function(e) {
		if(!vcm_overlay_on) {
			return false;
		}
		var vcm_overlay_cont = jQuery(".vcm-info-overlay-content");
		if(!vcm_overlay_cont.is(e.target) && vcm_overlay_cont.has(e.target).length === 0) {
			vcmCloseModal();
		}
	});
	jQuery(document).keyup(function(e) {
		if (e.keyCode == 27 && vcm_overlay_on) {
			vcmCloseModal();
		}
	});
});
</script>
