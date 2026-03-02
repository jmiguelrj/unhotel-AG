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

$rowsmap = $this->rowsmap;
$rows = $this->rows;

JText::script('VCM_EXPORT_ROOM_ICAL');
JText::script('VCM_EXPORT_ROOM_ICAL_DESCR');

$vik = new VikApplication(VersionListener::getID());

if (!$rows) {
	?>
	<p class="vcmfatal"><?php echo JText::_('VCMNOROOMSASSOCFOUND'); ?></p>
	<br clear="all"/>
	<span class="vcmsynchspan">
		<a class="vcmsyncha" href="index.php?option=com_vikchannelmanager&amp;task=roomsynch"><?php echo JText::_('VCMGOSYNCHROOMS'); ?></a>
	</span>
	<form action="index.php?option=com_vikchannelmanager" method="post" name="adminForm" id="adminForm">
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="option" value="com_vikchannelmanager" />
	</form>
<?php
} else {
	$channels = [];
	$schema = [];
	$rooms_ch_map = [];
	$override_chname = [];
	foreach ($rowsmap as $row) {
		if ($row['idchannel'] == VikChannelManagerConfig::VRBOAPI) {
			// immediately overwrite room information to avoid duplicates and conflicts with Google
			$override_chname[$row['channel']] = 'Vrbo';
			$row['idroomota'] = "V-{$row['idroomota']}";
		}
		$channels[$row['channel']] = $row['channel'];
		$schema[$row['idroomota'].'_'.$row['otaroomname']][] = $row;
		if ($row['idchannel'] == VikChannelManagerConfig::AIRBNBAPI) {
			$override_chname[$row['channel']] = 'Airbnb';
		} elseif ($row['idchannel'] == VikChannelManagerConfig::GOOGLEHOTEL) {
			$override_chname[$row['channel']] = 'Google';
		} elseif ($row['idchannel'] == VikChannelManagerConfig::GOOGLEVR) {
			$override_chname[$row['channel']] = 'Google';
		}
		$rooms_ch_map[$row['idroomota'].'_'.$row['otaroomname']] = $row['channel'];
	}
	if ($this->first_summary > 0) {
	?>
	<div class="vcm-bfirstsummary">
		<h3><?php echo JText::_('VCMFIRSTBSUMMTITLE'); ?></h3>
		<p><?php echo JText::_('VCMFIRSTBSUMMDESC'); ?></p>
		<button type="button" class="btn btn-success" onclick="vcmDoImport('1');"><i class="icon-download"></i> <?php echo JText::_('VCMFIRSTBSUMMOK'); ?></button>
		&nbsp;&nbsp;&nbsp;
		<button type="button" class="btn btn-danger" onclick="vcmDoImport('0');"><i class="icon-cancel"></i> <?php echo JText::_('VCMFIRSTBSUMMKO'); ?></button>
	</div>
	<a id="vcm-hidden-link-fs" style="display: none;" href="index.php?option=com_vikchannelmanager&task=first_summary&suggest_ba=1"></a>
	<script type="text/javascript">
	function vcmDoImport(act) {
		document.location.href = jQuery('#vcm-hidden-link-fs').attr('href') + '&imp='+act;
	}
	</script>
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
		<div class="vcm-info-overlay-content"></div>
	</div>
	<h3 class="vcmlargeheading"><?php echo JText::_('VCMROOMSRELATIONS'); ?></h3>
	<div class="table-responsive vcmrelationshema">
		<table class="vcmtableschema">
			<tr class="vcmtrbigheader">
				<td colspan="2"><div class="vcmrheadtype headtype_<?php echo count($channels) > 1 ? 'channels' : $channels[key($channels)]; ?>"><?php echo JText::_('VCMROOMSRELATIONSOTA'); ?></div></td>
				<td rowspan="2" class="vcmtdlinked"><i class="vboicn-link"></i></td>
				<td colspan="2"><div class="vcmrheadtype headtype_vikbooking"><?php echo JText::_('VCMROOMSRELATIONSVB'); ?></div></td>
			</tr>
			<tr class="vcmtrmediumheader">
				<td class="vcmrsmallheadtype vcmfirsttd"><?php echo JText::_('VCMROOMSRELATIONSID'); ?></td>
				<td class="vcmrsmallheadtype"><?php echo JText::_('VCMROOMSRELATIONSNAME'); ?></td>
				<td class="vcmrsmallheadtype"><?php echo JText::_('VCMROOMSRELATIONSNAME'); ?></td>
				<td class="vcmrsmallheadtype vcmlasttd"><?php echo JText::_('VCMROOMSRELATIONSID'); ?></td>
			</tr>
			<tr class="vcmtrmediumheader-separe">
				<td colspan="5">&nbsp;</td>
			</tr>
		<?php
		$lastvbidr = 0;
		$reln1 = false;
		$keys = array_keys($schema);
		$j = 0;
		foreach ($schema as $otak => $relval) {
			$otaparts = explode('_', $otak);
			if (isset($rooms_ch_map[$otak]) && !strcasecmp($rooms_ch_map[$otak], 'googlehotel')) {
				$otaparts[0] = 'G-' . $otaparts[0];
			}
			?>
			<tr class="vcmschemarow">
				<td><?php echo (array_key_exists($otak, $rooms_ch_map) ? '<span class="vcm-relation-label-small ' . $rooms_ch_map[$otak] . '">' . (isset($override_chname[$rooms_ch_map[$otak]]) ? $override_chname[$rooms_ch_map[$otak]] : ucwords($rooms_ch_map[$otak])) . '</span>' : '') . $otaparts[0]; ?></td>
				<td><?php echo count($relval) < 2 ? '<span class="vcmshowinfo" data-vcmrelid="'.$relval[0]['id'].'">'.$otaparts[1].'</span>' : $otaparts[1]; ?></td>
			<?php
			foreach ($relval as $relk => $rel) {
				$css_rel_linker = '';
				$is_single = false;
				$invalid_rel = false;
				if ($relk > 0) {
					// re-writing the TR tag is necessary for inner loop
					?>
				<tr class="vcmschemarow">
					<td></td>
					<td></td>
					<?php
					if (count($relval) != ($relk + 1)) {
						$invalid_rel = true;
						$relimg = 'rel_middle.png';
						$css_rel_linker = '<span class="vcmschema-room-rel-one-right"></span>' . "\n";
						$css_rel_linker .= '<span class="vcmschema-room-rel-one-multi"></span>' . "\n";
					} else {
						$invalid_rel = true;
						$relimg = 'rel_last.png';
						$css_rel_linker = '<span class="vcmschema-room-rel-one-right"></span>' . "\n";
						$css_rel_linker .= '<span class=""></span>' . "\n";
					}
				} else {
					if (count($relval) > 1) {
						$relimg = 'rel_first_multi.png';
						$css_rel_linker = '<span class="vcmschema-room-rel-one-one"></span>' . "\n";
						$css_rel_linker .= '<span class="vcmschema-room-rel-one-multi"></span>' . "\n";
					} else {
						$relimg = 'rel_first_single.png';
						$css_rel_linker = '<span class="vcmschema-room-rel-one-one"></span>' . "\n";
                		$css_rel_linker .= '<span class=""></span>' . "\n";
						$is_single = true;
						// check rel OTA-VBO = n-1
						if (array_key_exists(($j + 1), $keys) && $keys[($j + 1)] != $otak && !(count($schema[$keys[($j + 1)]]) > 1)) {
							if ($schema[$keys[($j + 1)]][0]['idroomvb'] == $rel['idroomvb']) {
								$relimg = 'rel_first_multi.png';
								$css_rel_linker = '<span class="vcmschema-room-rel-one-one"></span>' . "\n";
								$css_rel_linker .= '<span class="vcmschema-room-rel-one-multi"></span>' . "\n";
								if ($reln1 === true) {
									$relimg = 'rel_middle_reverse.png';
									$css_rel_linker = '<span class="vcmschema-room-rel-one-left"></span>' . "\n";
									$css_rel_linker .= '<span class="vcmschema-room-rel-one-multi"></span>' . "\n";
								} else {
									$reln1 = true;
									$lastvbidr = 0;
								}
							} elseif ($reln1 === true) {
								$relimg = 'rel_last_reverse.png';
								$css_rel_linker = '<span class="vcmschema-room-rel-one-left"></span>' . "\n";
                				$css_rel_linker .= '<span class=""></span>' . "\n";
								$reln1 = false;
							}
						} else {
							if ($reln1 === true) {
								$relimg = 'rel_last_reverse.png';
								$css_rel_linker = '<span class="vcmschema-room-rel-one-left"></span>' . "\n";
                				$css_rel_linker .= '<span class=""></span>' . "\n";
							}
							$reln1 = false;
						}
						// end check rel OTA-VBO = n-1
					}
				}
				?>
				<td class="vcmschema-room-rel<?php echo $invalid_rel ? ' vcmschema-room-rel-invalid' : ''; ?>">
					<?php
					/**
					 * We no longer use the images to explain the relation, but we rather use pure HTML and CSS.
					 * Original path for images was: VCM_ADMIN_URI . 'assets/css/images/' . $relimg;
					 * 
					 * @since 	1.8.4
					 */
					echo $css_rel_linker;
					?>
				</td>
				<td><?php echo ($is_single === false || $rel['idroomvb'] !== $lastvbidr) ? '<span class="vcm-vboroom-calinfo" data-vboid="' . $rel['idroomvb'] . '">' . $rel['name'] . '</span>' : ''; ?></td>
				<td><?php echo ($is_single === false || $rel['idroomvb'] !== $lastvbidr) ? $rel['idroomvb'] : ''; ?></td>
			</tr>
				<?php
				if ($relimg == 'rel_last_reverse.png') {
					// we put an empty row to separate the relations
					?>
			<tr class="vcmschemarow vcmschemarow-separe">
				<td colspan="5">&nbsp;</td>
			</tr>
					<?php
				}
				$lastvbidr = $rel['idroomvb'];
			}
			$j++;
		}
		?>
		</table>
	</div>
	<br clear="all"/>
			
	<form action="index.php?option=com_vikchannelmanager" method="post" name="adminForm" id="adminForm" class="vcm-list-form">
		<div class="table-responsive">
			<table cellpadding="4" cellspacing="0" border="0" width="100%" class="<?php echo $vik->getAdminTableClass(); ?> vcm-list-table">
			<?php echo $vik->openTableHead(); ?>
				<tr>
					<th width="20">
						<?php echo $vik->getAdminToggle(count($rows)); ?>
					</th>
					<th class="title" width="200"><?php echo JText::_('VCMROOMNAMEOTA'); ?></th>
					<th class="title" width="200"><?php echo JText::_('VCMROOMNAMEVB'); ?></th>
					<th class="title center" width="100" align="center"><?php echo JText::_('VCMCHANNEL'); ?></th>
					<th class="title center" width="75" align="center"><?php echo JText::_('VCMACCOUNTCHANNELID'); ?></th>
					<th class="title center" width="75" align="center"><?php echo JText::_('VCMROOMCHANNELID'); ?></th>
					<th class="title center" width="75" align="center"><?php echo JText::_('VCMROOMVBID'); ?></th>
				</tr>
			<?php echo $vik->closeTableHead(); ?>
			<?php
			$k = 0;
			$i = 0;
			for ($i = 0, $n = count($rows); $i < $n; $i++) {
				$row = $rows[$i];
				$chaccount_param = json_decode($row['prop_params'], true);
				$chaccount_param = is_array($chaccount_param) ? $chaccount_param : [];
				$prop_id = isset($chaccount_param['hotelid']) ? $chaccount_param['hotelid'] : (isset($chaccount_param['id']) ? $chaccount_param['id'] : '');
				$say_chname = $row['idchannel'] == VikChannelManagerConfig::AIRBNBAPI ? 'Airbnb' : ucwords($row['channel']);
				if ($row['idchannel'] == VikChannelManagerConfig::GOOGLEHOTEL) {
					$say_chname = 'Google';
					$row['idroomota'] = 'G-' . $row['idroomota'];
				} elseif ($row['idchannel'] == VikChannelManagerConfig::GOOGLEVR) {
					$say_chname = 'Google VR';
					$row['idroomota'] = 'G-' . $row['idroomota'];
				} elseif ($row['idchannel'] == VikChannelManagerConfig::VRBOAPI) {
					$say_chname = 'Vrbo';
					$row['idroomota'] = 'V-' . $row['idroomota'];
				}
				?>
				<tr class="row<?php echo $k; ?>">
					<td><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row['id']; ?>" onClick="<?php echo $vik->checkboxOnClick(); ?>"></td>
					<td><?php echo $row['otaroomname']; ?></td>
					<td><?php echo $row['name']; ?></td>
					<td class="center">
						<span class="vbotasp <?php echo $row['channel']; ?>"><?php echo $say_chname; ?></span>
					</td>
					<td class="center"><?php echo (!empty($row['prop_name']) ? '<span'.(!empty($prop_id) ? ' title="ID '.$prop_id.'"' : '').'>'.$row['prop_name'].'</span>' : $prop_id); ?></td>
					<td class="center"><?php echo $row['idroomota']; ?></td>
					<td class="center"><?php echo $row['idroomvb']; ?></td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			</table>
		</div>
		<input type="hidden" name="option" value="com_vikchannelmanager" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>

	<script type="text/javascript">
	/* Loading Overlay */
	function vcmShowLoading() {
		jQuery(".vcm-loading-overlay").show();
	}
	function vcmHideLoading() {
		jQuery(".vcm-loading-overlay").hide();
	}
	var vcm_overlay_on = false;
	function vcmCloseModal() {
		jQuery(".vcm-info-overlay-block").fadeOut(400, function() {
			jQuery(this).attr("class", "vcm-info-overlay-block");
		});
		vcm_overlay_on = false;
	}
	function vcmCopyShareCalUrl() {
		var read_input = jQuery('.vcm-room-exportcal-url');
		if (!read_input.length) {
			return false;
		}
		
		var temptarea = document.createElement('textarea');
		document.body.appendChild(temptarea);
		temptarea.value = read_input.val();
		temptarea.select();
		temptarea.setSelectionRange(0, temptarea.value.length);
		document.execCommand('copy');
		document.body.removeChild(temptarea);

		jQuery('.vcm-room-exportcal-btn').find('i').attr('class', '<?php echo VikBookingIcons::i('check-square'); ?>');
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
		jQuery(".vcmshowinfo").click(function() {
			vcmShowLoading();
			var rel_id = jQuery(this).attr("data-vcmrelid");
			var rinfo_cont_html = "<h3>"+jQuery(this).text()+"</h3>";
			var jqxhr = jQuery.ajax({
				type: "POST",
				url: "index.php",
				data: { option: "com_vikchannelmanager", task: "rooms_rel_rplan", relid: rel_id<?php echo isset($_REQUEST['e4j_debug']) && (int)$_REQUEST['e4j_debug'] == 1 ? ', e4j_debug: 1' : ''; ?>, tmpl: "component" }
			}).done(function(res) {
				if(res.substr(0, 9) == 'e4j.error') {
					alert(res.replace("e4j.error.", ""));
				} else {
					rinfo_cont_html += res;
					jQuery(".vcm-info-overlay-content").html(rinfo_cont_html);
					jQuery(".vcm-info-overlay-block").fadeIn();
					vcm_overlay_on = true;
				}
				vcmHideLoading();
			}).fail(function() {
				alert("Error Performing Ajax Request");
				vcmHideLoading();
			});
		});
		jQuery('.vcm-vboroom-calinfo').click(function() {
			var rinfo_cont_html = "<h3>"+jQuery(this).text()+"</h3>";
			var id_room = jQuery(this).attr('data-vboid');
			var base_auth = '<?php echo md5(VikChannelManager::getApiKey(true)); ?>';
			var share_cal_url = '<?php echo JUri::root(); ?>index.php?option=com_vikchannelmanager&task=get_ical&id_room=' + id_room + '&auth=' + base_auth;
			rinfo_cont_html += '<h4>' + Joomla.JText._('VCM_EXPORT_ROOM_ICAL') + '</h4>';
			rinfo_cont_html += '<div class="vcm-room-exportcal-wrap">';
			rinfo_cont_html += '	<div class="btn-group input-append" style="margin: 0;">';
			rinfo_cont_html += '		<input type="text" class="vcm-room-exportcal-url" value="' + share_cal_url + '" readonly="readonly" style="min-width: 80%;" onclick="this.setSelectionRange(0, this.value.length);" />';
			rinfo_cont_html += '		<button type="button" class="btn btn-primary vcm-room-exportcal-btn" onclick="vcmCopyShareCalUrl();"><?php VikBookingIcons::e('copy'); ?></button>';
			rinfo_cont_html += '	</div>';
			rinfo_cont_html += '</div>';
			rinfo_cont_html += '<p class="info">' + Joomla.JText._('VCM_EXPORT_ROOM_ICAL_DESCR') + '</p>';
			// populate modal
			jQuery(".vcm-info-overlay-content").html(rinfo_cont_html);
			jQuery(".vcm-info-overlay-block").fadeIn();
			vcm_overlay_on = true;
		});
	});
	</script>
<?php
}
