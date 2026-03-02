<?php
/**
 * @package     VikBooking
 * @subpackage  com_vikbooking
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

$sdata = $this->sdata;
$adults_diff = $this->adults_diff;

$vbo_app = VikBooking::getVboApplication();
$vbo_app->loadSelect2();
$editor = JEditor::getInstance(JFactory::getApplication()->get('editor'));

$pfrom = VikRequest::getString('from', '', 'request');
$pto = VikRequest::getString('to', '', 'request');
$ptype = VikRequest::getInt('type', 0, 'request');
$pdiffcost = VikRequest::getFloat('diffcost', 0, 'request');
$pval_pcent = VikRequest::getInt('val_pcent', 0, 'request');
$pwdays = VikRequest::getVar('wdays', array(), 'request', 'array');
$prooms = VikRequest::getVar('rooms', array(), 'request', 'array');
$prplans = VikRequest::getVar('rplans', array(), 'request', 'array');
$ppromodaysadv = VikRequest::getInt('promodaysadv', 0, 'request');
$ppromolastmind = VikRequest::getInt('promolastmind', 0, 'request');
$ppromominlos = VikRequest::getInt('promominlos', 0, 'request');
$ppromo = VikRequest::getInt('promo', 0, 'request');
$ppromoname = VikRequest::getString('promoname', '', 'request');
if (!count($sdata) && $ppromo && empty($ppromoname)) {
	$ppromoname = JText::_('VBSPPROMOTIONLABEL');
}

// js lang def
JText::script('VBDELCONFIRM');
JText::script('VBOROOMNORATE');

if (count($this->allrooms)) {
	$caldf = VikBooking::getDateFormat(true);
	$currencysymb = VikBooking::getCurrencySymb();
	if ($caldf == "%d/%m/%Y") {
		$df = 'd/m/Y';
	} elseif ($caldf == "%m/%d/%Y") {
		$df = 'm/d/Y';
	} else {
		$df = 'Y/m/d';
	}
	if (count($sdata) && ($sdata['from'] > 0 || $sdata['to'] > 0)) {
		$nowyear = !empty($sdata['year']) ? $sdata['year'] : date('Y');
		$frombase = mktime(0, 0, 0, 1, 1, $nowyear);
		$fromdate = date($df, ($frombase + $sdata['from']));
		if ($sdata['to'] < $sdata['from']) {
			$nowyear = $nowyear + 1;
			$frombase = mktime(0, 0, 0, 1, 1, $nowyear);
		}
		$todate = date($df, ($frombase + $sdata['to']));
		//leap years
		$checkly = !empty($sdata['year']) ? $sdata['year'] : date('Y');
		if ($checkly % 4 == 0 && ($checkly % 100 != 0 || $checkly % 400 == 0)) {
			$frombase = mktime(0, 0, 0, 1, 1, $checkly);
			$infoseason = getdate($frombase + $sdata['from']);
			$leapts = mktime(0, 0, 0, 2, 29, $infoseason['year']);
			if ($infoseason[0] > $leapts) {
				/**
				 * Timestamp must be greater than the leap-day of Feb 29th.
				 * It used to be checked for >= $leapts.
				 * 
				 * @since 	July 3rd 2019
				 */
				$fromdate = date($df, ($frombase + $sdata['from'] + 86400));
				$frombase = mktime(0, 0, 0, 1, 1, $nowyear);
				if ($sdata['from'] < $sdata['to']) {
					/**
					 * When starting from a leap year, do not increase by one day the end date in case
					 * the season end date is on the next year. Do it only if within the same year.
					 * 
					 * @since 	1.16.7 (J) - 1.6.7 (WP)
					 */
					$todate = date($df, ($frombase + $sdata['to'] + 86400));
				}
			}
		}
		//
	} else {
		$fromdate = $pfrom;
		$todate = $pto;
	}
	$actweekdays = count($sdata) ? explode(";", (string)$sdata['wdays']) : $pwdays;

	$actvalueoverrides = '';
	if ($sdata && strlen((string)$sdata['losoverride'])) {
		$losoverrides = explode('_', $sdata['losoverride']);
		foreach($losoverrides as $loso) {
			if (!empty($loso)) {
				$losoparts = explode(':', $loso);
				$losoparts[2] = strstr($losoparts[0], '-i') != false ? 1 : 0;
				$losoparts[0] = str_replace('-i', '', $losoparts[0]);
				$actvalueoverrides .= '<p>'.JText::_('VBNEWSEASONNIGHTSOVR').' <input type="number" min="1" name="nightsoverrides[]" value="'.$losoparts[0].'" /> <select name="andmoreoverride[]"><option value="0">-------</option><option value="1"'.($losoparts[2] == 1 ? ' selected="selected"' : '').'>'.JText::_('VBNEWSEASONVALUESOVREMORE').'</option></select> - '.JText::_('VBNEWSEASONVALUESOVR').' <input type="number" step="any" name="valuesoverrides[]" value="'.$losoparts[1].'" style="min-width: 60px !important;"/> '.(intval($sdata['val_pcent']) == 2 ? '%' : $currencysymb).'</p>';
			}
		}
	}
	
	?>
<script type="text/javascript">
Joomla.submitbutton = function(task) {
	if ( task == 'removeseasons') {
		if (confirm(Joomla.JText._('VBDELCONFIRM'))) {
			Joomla.submitform(task, document.adminForm);
		} else {
			return false;
		}
	} else if (task.indexOf('create') >= 0 || task.indexOf('update') >= 0) {
		// make sure all data is valid if creating a promotion for one or more channels
		if (!jQuery('input[name="promo"]').prop('checked') || !jQuery('select[name="channels[]"]').val() || !jQuery('select[name="channels[]"]').val().length) {
			// not a promotion or no channels selected, so go ahead
			Joomla.submitform(task, document.adminForm);
			return;
		}

		// in case of promotions for OTAs, at least one rate plan must be selected
		if (!jQuery('select[name="idrooms[]"]').val().length || !jQuery('select[name="idprices[]"]').val().length) {
			// raise error and prevent form submission
			const only_chars = new RegExp(/[^A-Z0-9 ]/, 'i');
			let err_str = Joomla.JText._('VBOROOMNORATE').replace(only_chars, '').trim();
			alert(err_str.charAt(0).toUpperCase() + err_str.slice(1));
			return false;
		}

		// go ahead, all looks good
		Joomla.submitform(task, document.adminForm);
		return;
	} else {
		Joomla.submitform(task, document.adminForm);
	}
}

function addMoreOverrides() {
	var sel = document.getElementById('val_pcent');
	var curpcent = sel.options[sel.selectedIndex].text;
	var ni = document.getElementById('myDiv');
	var numi = document.getElementById('morevalueoverrides');
	var num = (document.getElementById('morevalueoverrides').value -1)+ 2;
	numi.value = num;
	var newdiv = document.createElement('div');
	var divIdName = 'my'+num+'Div';
	newdiv.setAttribute('id',divIdName);
	newdiv.innerHTML = '<p><?php echo addslashes(JText::_('VBNEWSEASONNIGHTSOVR')); ?> <input type=\'number\' min=\'1\' name=\'nightsoverrides[]\' value=\'\' /> <select name=\'andmoreoverride[]\'><option value=\'0\'>-------</option><option value=\'1\'><?php echo addslashes(JText::_('VBNEWSEASONVALUESOVREMORE')); ?></option></select> - <?php echo addslashes(JText::_('VBNEWSEASONVALUESOVR')); ?> <input type=\'number\' step=\'any\' name=\'valuesoverrides[]\' value=\'\' style=\'min-width: 60px !important;\'/> '+curpcent+'</p>';
	ni.appendChild(newdiv);
}
var rooms_sel_ids = [];
var rooms_names_map = [];
var rooms_adults_pricing = <?php echo json_encode($adults_diff); ?>;
jQuery(function() {
	var rseltag = document.getElementById("idrooms");
	for(var i=0; i < rseltag.length; i++) {
		rooms_names_map[rseltag.options[i].value] = rseltag.options[i].text;
	}
	jQuery(".vbo-select-all").click(function() {
		var nextsel = jQuery(this).next("select");
		nextsel.find("option").prop('selected', true);
		nextsel.trigger('change');
	});
	jQuery("#idrooms").change(function() {
		if (jQuery(this).val() !== null) {
			rooms_sel_ids = jQuery(this).val();
		} else {
			rooms_sel_ids = [];
		}
		updateOccupancyPricing();
	});
	jQuery(document.body).on('click', ".occupancy-room-name", function() {
		jQuery(this).next(".occupancy-room-data").fadeToggle();
	});
	jQuery('#idrooms, #idprices, #vbo-selwdays, #vbo-promo-channels').select2();
	// edit mode must trigger the change event when the document is ready
	jQuery("#idrooms").trigger("change");
});
function isFullObject(obj) {
	var jk;
	for(jk in obj) {
		return obj.hasOwnProperty(jk);
	}
}
function updateOccupancyPricing() {
	var occupancy_cont = jQuery("#vbo-occupancy-container");
	var usage_lbl = '<?php echo addslashes(JText::_('VBADULTSDIFFNUM')); ?>';
	if (rooms_sel_ids.length > 0) {
		jQuery("#vbo-occupancy-pricing-fieldset").fadeIn();
		jQuery(rooms_sel_ids).each(function(k, v){
			if (!rooms_adults_pricing.hasOwnProperty(v)) {
				return true;
			}
			if (jQuery("#occupancy-r"+v).length) {
				return true;
			}
			if (isFullObject(rooms_adults_pricing[v])) {
				//Occupancy supported
				var is_ovr = false;
				var occ_data = "<div id=\"occupancy-r"+v+"\" class=\"occupancy-room\"  data-roomid=\""+v+"\">"+
					"<div class=\"occupancy-room-name\">"+rooms_names_map[v]+"</div>"+
					"<div class=\"occupancy-room-data\">";
				for(var occ in rooms_adults_pricing[v]) {
					if (rooms_adults_pricing[v].hasOwnProperty(occ)) {
						occ_data += "<div class=\"occupancy-adults-data\">"+
							"<span class=\"occupancy-adults-lbl\">"+usage_lbl.replace("%s", occ)+"</span>"+
							"<div class=\"occupancy-adults-ovr\">"+
								"<select name=\"adultsdiffchdisc["+v+"]["+occ+"]\"><option value=\"1\""+(rooms_adults_pricing[v][occ].hasOwnProperty('chdisc') && rooms_adults_pricing[v][occ]['chdisc'] == 1 ? " selected=\"selected\"" : "")+"><?php echo addslashes(JText::_('VBADULTSDIFFCHDISCONE')); ?></option><option value=\"2\""+(rooms_adults_pricing[v][occ].hasOwnProperty('chdisc') && rooms_adults_pricing[v][occ]['chdisc'] == 2 ? " selected=\"selected\"" : "")+"><?php echo addslashes(JText::_('VBADULTSDIFFCHDISCTWO')); ?></option></select>"+
								"<input type=\"number\" step=\"any\" name=\"adultsdiffval["+v+"]["+occ+"]\" value=\""+(rooms_adults_pricing[v][occ].hasOwnProperty('override') && rooms_adults_pricing[v][occ].hasOwnProperty('value') ? rooms_adults_pricing[v][occ]['value'] : "")+"\" placeholder=\""+(rooms_adults_pricing[v][occ].hasOwnProperty('value') ? rooms_adults_pricing[v][occ]['value'] : "0.00")+"\" style=\"min-width: 60px !important;\"/>"+
								"<select name=\"adultsdiffvalpcent["+v+"]["+occ+"]\"><option value=\"1\""+(rooms_adults_pricing[v][occ].hasOwnProperty('valpcent') && rooms_adults_pricing[v][occ]['valpcent'] == 1 ? " selected=\"selected\"" : "")+"><?php echo $currencysymb; ?></option><option value=\"2\""+(rooms_adults_pricing[v][occ].hasOwnProperty('valpcent') && rooms_adults_pricing[v][occ]['valpcent'] == 2 ? " selected=\"selected\"" : "")+">%</option></select>"+
								"<select name=\"adultsdiffpernight["+v+"]["+occ+"]\"><option value=\"1\""+(rooms_adults_pricing[v][occ].hasOwnProperty('pernight') && rooms_adults_pricing[v][occ]['pernight'] >= 1 ? " selected=\"selected\"" : "")+"><?php echo addslashes(JText::_('VBADULTSDIFFONPERNIGHT')); ?></option><option value=\"0\""+(rooms_adults_pricing[v][occ].hasOwnProperty('pernight') && rooms_adults_pricing[v][occ]['pernight'] <= 0 ? " selected=\"selected\"" : "")+"><?php echo addslashes(JText::_('VBADULTSDIFFONTOTAL')); ?></option></select>"+
							"</div>"+
							"</div>";
						is_ovr = rooms_adults_pricing[v][occ].hasOwnProperty('override') ? true : is_ovr;
					}
				}
				occ_data += "</div>"+
					"</div>";
				occupancy_cont.append(occ_data);
				if (is_ovr === true) {
					jQuery("#occupancy-r"+v).find(".occupancy-room-name").trigger("click");
				}
			} else {
				//Occupancy not supported (same fromadult and toadult)
				occupancy_cont.append("<div id=\"occupancy-r"+v+"\" class=\"occupancy-room\" data-roomid=\""+v+"\">"+
					"<div class=\"occupancy-room-name\">"+rooms_names_map[v]+"</div>"+
					"<div class=\"occupancy-room-data\"><p><?php echo addslashes(JText::_('VBOROOMOCCUPANCYPRNOTSUPP')); ?></p></div>"+
					"</div>");
			}
		});
	} else {
		jQuery("#vbo-occupancy-pricing-fieldset").fadeOut();
	}
	//hide the un-selected rooms
	jQuery(".occupancy-room").each(function() {
		var rid = jQuery(this).attr("data-roomid");
		if (jQuery.inArray(rid, rooms_sel_ids) == -1) {
			jQuery(this).remove();
		}
	});
	//
}
function togglePromotion() {
	var promo_on = jQuery('input[name="promo"]').prop('checked');
	if (promo_on === true) {
		var cur_startd = jQuery('#from').val();
		var cur_endd = jQuery('#to').val();
	<?php
	if (!count($sdata)) {
		// when creating a new promotion, we make sure the dates are defined to disallow promos with no dates
		?>
		if (!cur_startd.length || !cur_endd.length) {
			alert('<?php echo addslashes(JText::_('VBOPROMOWARNNODATES')); ?>');
			jQuery('input[name="promo"]').prop('checked', false);
			jQuery('.promotr').fadeOut();
			return false;
		}
		<?php
	}
	?>
		jQuery('.promotr').fadeIn();
		jQuery('#promovalidity span').text('');
		if (cur_startd.length) {
			jQuery('#promovalidity span').text(' ('+cur_startd+')');
		}
	} else {
		jQuery('.promotr').fadeOut();
	}
}
</script>
<input type="hidden" value="0" id="morevalueoverrides" />

<form name="adminForm" id="adminForm" action="index.php" method="post">

	<div class="vbo-admin-container">
		<div class="vbo-config-maintab-left">

			<fieldset class="adminform">
				<div class="vbo-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VBSPRICESHELPTITLE'); ?> &nbsp; <?php echo $vbo_app->createPopover(array('title' => JText::_('VBSPRICESHELPTITLE'), 'content' => JText::_('VBSPRICESHELP'))); ?></legend>
					<div class="vbo-params-container">
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBSEASON'); ?></div>
							<div class="vbo-param-setting">
								<div style="display: block; margin-bottom: 3px;">
									<?php echo '<span class="vbrestrdrangesp">'.JText::_('VBNEWRESTRICTIONDFROMRANGE').'</span>'.$vbo_app->getCalendar($fromdate, 'from', 'from', $caldf, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?>
								</div>
								<div style="display: block; margin-bottom: 3px;">
									<?php echo '<span class="vbrestrdrangesp">'.JText::_('VBNEWRESTRICTIONDTORANGE').'</span>'.$vbo_app->getCalendar($todate, 'to', 'to', $caldf, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?>
								</div>
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBWEEKDAYS'); ?></div>
							<div class="vbo-param-setting">
								<span class="vbo-select-all"><?php echo JText::_('VBOSELECTALL'); ?></span>
								<select multiple="multiple" size="7" name="wdays[]" id="vbo-selwdays">
									<option value="0"<?php echo (in_array("0", $actweekdays) ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBSUNDAY'); ?></option>
									<option value="1"<?php echo (in_array("1", $actweekdays) ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBMONDAY'); ?></option>
									<option value="2"<?php echo (in_array("2", $actweekdays) ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBTUESDAY'); ?></option>
									<option value="3"<?php echo (in_array("3", $actweekdays) ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBWEDNESDAY'); ?></option>
									<option value="4"<?php echo (in_array("4", $actweekdays) ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBTHURSDAY'); ?></option>
									<option value="5"<?php echo (in_array("5", $actweekdays) ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBFRIDAY'); ?></option>
									<option value="6"<?php echo (in_array("6", $actweekdays) ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBSATURDAY'); ?></option>
								</select>
								<span class="vbo-param-setting-comment"><?php echo JText::_('VBOSPWDAYSHELP'); ?></span>
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBSPNAME'); ?></div>
							<div class="vbo-param-setting">
								<input type="text" name="spname" value="<?php echo $this->escape(count($sdata) ? $sdata['spname'] : $ppromoname); ?>" size="30"/>
								<span class="vbo-param-setting-comment"><?php echo JText::_('VBOSPNAMEHELP'); ?></span>
							</div>
						</div>
						<div class="vbo-param-container" style="<?php echo !$sdata || !empty($sdata['year']) ? 'display: none;' : ''; ?>">
							<div class="vbo-param-label"><?php echo JText::_('VBSPYEARTIED'); ?></div>
							<div class="vbo-param-setting">
								<?php echo $vbo_app->printYesNoButtons('yeartied', JText::_('VBYES'), JText::_('VBNO'), (!$sdata || ($sdata && !empty($sdata['year'])) ? 1 : 0), 1, 0); ?>
								<span class="vbo-param-setting-comment"><?php echo JText::_('VBOSPYEARTIEDHELP'); ?></span>
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBSPONLYPICKINCL'); ?></div>
							<div class="vbo-param-setting">
								<?php echo $vbo_app->printYesNoButtons('checkinincl', JText::_('VBYES'), JText::_('VBNO'), (count($sdata) ? (int)$sdata['checkinincl'] : 0), 1, 0); ?>
								<span class="vbo-param-setting-comment"><?php echo JText::_('VBOSPONLCKINHELP'); ?></span>
							</div>
						</div>
					<?php
					if (!empty($sdata['createdon']) && !preg_match('/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}/', ($sdata['spname'] ?? ''))) {
						?>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBOINVCREATIONDATE'); ?></div>
							<div class="vbo-param-setting">
								<span class="vbo-param-setting-comment"><?php echo $sdata['createdon']; ?></span>
							</div>
						</div>
						<?php
					}
					if (!empty($sdata['modifiedon'])) {
						?>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo trim(JText::sprintf('VBO_MODIFIED_ON_SMT', '')); ?></div>
							<div class="vbo-param-setting">
								<span class="vbo-param-setting-comment"><?php echo $sdata['modifiedon']; ?></span>
							</div>
						</div>
						<?php
					}
					?>
					</div>
				</div>
			</fieldset>

			<fieldset class="adminform">
				<div class="vbo-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VBSPPROMOTIONLABEL'); ?></legend>
					<div class="vbo-params-container">
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBOISPROMOTION'); ?></div>
							<div class="vbo-param-setting">
								<?php echo $vbo_app->printYesNoButtons('promo', JText::_('VBYES'), JText::_('VBNO'), (count($sdata) && $sdata['promo'] ? 1 : $ppromo), 1, 0, 'togglePromotion();'); ?>
								<span class="vbo-param-setting-comment"><?php echo JText::_('VBOSPTPROMOHELP'); ?></span>
							</div>
						</div>
						<?php
						$handlers = VikBooking::getPromotionHandlers();
						if (!count($sdata) && ($handlers === false || (is_array($handlers) && count($handlers)))) {
							// display this parameter only if VCM is not installed or if there are handlers, and if creating a new special price (no editing)
							?>
						<div class="vbo-param-container promotr">
							<div class="vbo-param-label"><?php echo JText::_('VBOCHANNELS'); ?></div>
							<div class="vbo-param-setting">
							<?php
							if ($handlers === false) {
								// VCM is not installed
								?>
								<span class="vbo-param-setting-comment vbo-param-setting-comment-danger"><?php echo JText::_('VBCONFIGVCMAUTOUPDMISS'); ?></span>
								<?php
							} else {
								// some handlers are available
								?>
								<select name="channels[]" multiple="multiple" size="5" id="vbo-promo-channels">
								<?php
								foreach ($handlers as $promo_obj) {
									?>
									<option value="<?php echo $promo_obj->key; ?>"<?php echo $ppromo || !strcasecmp($promo_obj->key, 'googlehotel') || !strcasecmp($promo_obj->key, 'googlevr') ? ' selected="selected"' : ''; ?>><?php echo $promo_obj->name; ?></option>
									<?php
								}
								?>
								</select>
								<span class="vbo-param-setting-comment"><?php echo JText::_('VBOPROMOCHANNELSHELP'); ?></span>
								<?php
							}
							?>
							</div>
						</div>
						<?php
						}

						/**
						 * Display if this promo was transmitted to other channels.
						 * 
						 * @since 	1.16.0 (J) - 1.6.0 (WP)
						 */
						if (count($sdata) && is_array($handlers) && count($handlers) && class_exists('VikChannelManagerPromo') && method_exists('VikChannelManagerPromo', 'getPromoChannelsInvolved')) {
							$ota_promotion_data = VikChannelManagerPromo::getPromoChannelsInvolved($sdata['id']);
							$ota_logos_map = [];
							foreach ($ota_promotion_data as $ota_data) {
								$ota_logo_obj = VikBooking::getVcmChannelsLogo($ota_data['channel'], $get_istance = true);
								$ota_logo_url = $ota_logo_obj->getSmallLogoURL();
								if (!empty($ota_logo_url)) {
									$ota_logos_map[$ota_data['channel']] = $ota_logo_url;
								}
							}
							if (count($ota_logos_map)) {
								?>
						<div class="vbo-param-container promotr">
							<div class="vbo-param-label">&nbsp;</div>
							<div class="vbo-param-setting">
								<div class="vbo-room-channels-mapped-wrap vbo-promo-channels-involved-wrap">
								<?php
								foreach ($ota_logos_map as $ch_name => $ota_logo_url) {
									?>
									<div class="vbo-room-channels-mapped-ch">
										<span class="vbo-room-channels-mapped-ch-lbl hasTooltip" title="<?php echo $this->escape(ucfirst($ch_name)); ?>">
											<img src="<?php echo $ota_logo_url; ?>" />
										</span>
									</div>
									<?php
								}
								?>
								</div>
							</div>
						</div>
								<?php
							}
						}
						?>
						<div class="vbo-param-container promotr">
							<div class="vbo-param-label"><?php echo JText::_('VBOPROMOVALIDITY'); ?> <?php echo $vbo_app->createPopover(array('title' => JText::_('VBOPROMOVALIDITY'), 'content' => JText::_('VBOPROMOVALIDITYHELP'))); ?></div>
							<div class="vbo-param-setting">
								<input type="number" name="promodaysadv" value="<?php echo !count($sdata) || (count($sdata) && empty($sdata['promodaysadv'])) ? $ppromodaysadv : $sdata['promodaysadv']; ?>" size="5"/>
								<span id="promovalidity"><?php echo JText::_('VBOPROMOVALIDITYDAYSADV'); ?>
									<span></span>
								</span>
							</div>
						</div>
						<div class="vbo-param-container promotr">
							<div class="vbo-param-label"><?php echo JText::_('VBOPROMOLASTMINUTE'); ?> <?php echo $vbo_app->createPopover(array('title' => JText::_('VBOPROMOLASTMINUTE'), 'content' => JText::_('VBOPROMOLASTMINUTEHELP'))); ?></div>
							<div class="vbo-param-setting">
							<?php
							$lastmind = $ppromolastmind;
							$lastminh = 0;
							if (count($sdata) && !empty($sdata['promolastmin'])) {
								$lastmind = floor($sdata['promolastmin'] / 86400);
								$lastminh = floor(($sdata['promolastmin'] - ($lastmind * 86400)) / 3600);
							}
							?>
								<div style="display: inline-block; margin-right: 10px;">
									<input type="number" name="promolastmind" value="<?php echo $lastmind; ?>" min="0"/>
									<span class="lastminlbl"><?php echo strtolower(JText::_('VBCONFIGSEARCHPMAXDATEDAYS')); ?></span>
								</div>
								<div style="display: inline-block;">
									<input type="number" name="promolastminh" value="<?php echo $lastminh; ?>" min="0" max="23"/>
									<span class="lastminlbl"><?php echo strtolower(JText::_('VBCONFIGONETENEIGHT')); ?></span>
								</div>
							</div>
						</div>
						<div class="vbo-param-container promotr">
							<div class="vbo-param-label"><?php echo JText::_('VBPROMOFORCEMINLOS'); ?></div>
							<div class="vbo-param-setting">
								<input type="number" name="promominlos" value="<?php echo !count($sdata) || (count($sdata) && empty($sdata['promominlos'])) ? $ppromominlos : $sdata['promominlos']; ?>" size="5"/>
								<span class="vbo-param-setting-comment">(<?php echo JText::_('VBDAYS'); ?>) - <?php echo JText::_('VBO_USEFUL_LONGSTAY_PROMOS'); ?></span>
							</div>
						</div>
						<div class="vbo-param-container promotr">
							<div class="vbo-param-label"><?php echo JText::_('VBOPROMOONFINALPRICE'); ?></div>
							<div class="vbo-param-setting">
								<?php echo $vbo_app->printYesNoButtons('promofinalprice', JText::_('VBYES'), JText::_('VBNO'), ((!count($sdata) || (count($sdata) && $sdata['promofinalprice'])) ? 1 : 0), 1, 0); ?>
								<span class="vbo-param-setting-comment"><?php echo JText::_('VBOPROMOONFINALPRICEHELP'); ?> <span style="cursor: pointer;" onclick="vboOpenModalHelp();"><?php VikBookingIcons::e('info-circle'); ?></span></span>
							</div>
						</div>
						<div class="vbo-param-container vbo-param-container-full promotr">
							<div class="vbo-param-label"><?php echo JText::_('VBOPROMOTEXT'); ?></div>
							<div class="vbo-param-setting">
								<?php
								try {
									echo $editor->display( "promotxt", (count($sdata) ? $sdata['promotxt'] : ""), '100%', 300, 70, 20 );
								} catch (Throwable $t) {
									echo $t->getMessage() . ' in ' . $t->getFile() . ':' . $t->getLine() . '<br/>';
								}
								?>
								<span class="vbo-param-setting-comment"><?php echo JText::_('VBOPROMOTEXTHELP'); ?></span>
							</div>
						</div>
					</div>
				</div>
			</fieldset>

		</div>

		<div class="vbo-config-maintab-right">
			
			<fieldset class="adminform">
				<div class="vbo-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VBSPMAINSETTINGS'); ?></legend>
					<div class="vbo-params-container">
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWSEASONTHREE'); ?></div>
							<div class="vbo-param-setting">
								<select name="type">
									<option value="1"<?php echo (count($sdata) && intval($sdata['type']) == 1) || $ptype === 1 ? " selected=\"selected\"" : ""; ?>><?php echo JText::_('VBNEWSEASONSIX'); ?></option>
									<option value="2"<?php echo (count($sdata) && intval($sdata['type']) == 2) || $ptype === 2 || $ppromo ? " selected=\"selected\"" : ""; ?>><?php echo JText::_('VBNEWSEASONSEVEN'); ?></option>
								</select>
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWSEASONFOUR'); ?></div>
							<div class="vbo-param-setting">
								<input type="number" step="any" name="diffcost" value="<?php echo count($sdata) ? $sdata['diffcost'] : ($ppromo && empty($pdiffcost) ? '10' : $pdiffcost); ?>" style="min-width: 60px !important;"/> 
								<select name="val_pcent" id="val_pcent">
									<option value="2"<?php echo (count($sdata) && intval($sdata['val_pcent']) == 2) || $pval_pcent === 2 || $ppromo ? " selected=\"selected\"" : ""; ?>>%</option>
									<option value="1"<?php echo (count($sdata) && intval($sdata['val_pcent']) == 1) || $pval_pcent === 1 ? " selected=\"selected\"" : ""; ?>><?php echo $currencysymb; ?></option>
								</select> 
								&nbsp;
								<?php echo $vbo_app->createPopover(array('title' => JText::_('VBNEWSEASONFOUR'), 'content' => JText::_('VBSPECIALPRICEVALHELP'))); ?>
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWSEASONVALUEOVERRIDE'); ?> <?php echo $vbo_app->createPopover(array('title' => JText::_('VBNEWSEASONVALUEOVERRIDE'), 'content' => JText::_('VBNEWSEASONVALUEOVERRIDEHELP'))); ?></div>
							<div class="vbo-param-setting">
								<div id="myDiv" style="display: block;"><?php echo $actvalueoverrides; ?></div>
								<a class="btn vbo-config-btn" href="javascript: void(0);" onclick="addMoreOverrides();"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::_('VBNEWSEASONADDOVERRIDE'); ?></a>
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWSEASONROUNDCOST'); ?></div>
							<div class="vbo-param-setting">
								<select name="roundmode">
									<option value=""><?php echo JText::_('VBNEWSEASONROUNDCOSTNO'); ?></option>
									<option value="PHP_ROUND_HALF_UP"<?php echo (count($sdata) && $sdata['roundmode'] == 'PHP_ROUND_HALF_UP' ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBNEWSEASONROUNDCOSTUP'); ?></option>
									<option value="PHP_ROUND_HALF_DOWN"<?php echo (count($sdata) && $sdata['roundmode'] == 'PHP_ROUND_HALF_DOWN' ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBNEWSEASONROUNDCOSTDOWN'); ?></option>
								</select>
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWSEASONFIVE'); ?></div>
							<div class="vbo-param-setting">
								<span class="vbo-select-all"><?php echo JText::_('VBOSELECTALL'); ?></span>
								<?php
								$split = count($sdata) ? explode(",", (string)$sdata['idrooms']) : array();
								?>
								<select id="idrooms" name="idrooms[]" multiple="multiple" size="5">
								<?php
								foreach ($this->allrooms as $d) {
									echo "<option value=\"".$d['id']."\"".(in_array("-".$d['id']."-", $split) || in_array($d['id'], $prooms) || ($ppromo && !count($prooms)) ? " selected=\"selected\"" : "").">".$d['name']."</option>\n";
								}
								?>
								</select>
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBOSPTYPESPRICE'); ?></div>
							<div class="vbo-param-setting">
								<span class="vbo-select-all"><?php echo JText::_('VBOSELECTALL'); ?></span>
								<?php
								$splitprices = count($sdata) ? explode(",", (string)$sdata['idprices']) : array();
								?>
								<select id="idprices" name="idprices[]" multiple="multiple" size="5">
								<?php
								foreach ($this->allprices as $k => $d) {
									echo "<option value=\"".$d['id']."\"".(in_array("-".$d['id']."-", $splitprices) || in_array($d['id'], $prplans) || ($ppromo && $k < 1) ? " selected=\"selected\"" : "").">".$d['name']."</option>\n";
								}
								?>
								</select>
							</div>
						</div>
					</div>
				</div>
			</fieldset>

			<fieldset class="adminform" id="vbo-occupancy-pricing-fieldset" style="display: none;">
				<div class="vbo-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VBSEASONOCCUPANCYPR'); ?></legend>
					<div class="vbo-params-container">
						<div id="vbo-occupancy-container"></div>
					</div>
				</div>
			</fieldset>

		</div>
	</div>

	<input type="hidden" name="task" value="">
	<input type="hidden" name="option" value="com_vikbooking">
<?php
if (count($sdata)) :
	?>
	<input type="hidden" name="where" value="<?php echo $sdata['id']; ?>">
	<?php
endif;
?>
	<?php echo JHtml::_('form.token'); ?>
</form>

<div class="vbo-modal-overlay-block vbo-modal-overlay-block-promofphelp">
	<a class="vbo-modal-overlay-close" href="javascript: void(0);"></a>
	<div class="vbo-modal-overlay-content vbo-modal-overlay-content-promofphelp">
		<div class="vbo-modal-overlay-content-head vbo-modal-overlay-content-head-promofphelp">
			<h3><?php VikBookingIcons::e('info-circle'); ?> <?php echo JText::_('VBOPROMOONFINALPRICE'); ?> <span class="vbo-modal-overlay-close-times" onclick="hideVboModalHelp();">&times;</span></h3>
		</div>
		<div class="vbo-modal-overlay-content-body vbo-modal-overlay-content-body-scroll">
			<p><?php echo JText::_('VBOPROMOONFINALPRICETXT'); ?></p>
		</div>
	</div>
</div>

<script type="text/javascript">
var vbodialoghelp_on = false;

jQuery(function() {
	jQuery('#from').val('<?php echo $fromdate; ?>').attr('data-alt-value', '<?php echo $fromdate; ?>');
	jQuery('#to').val('<?php echo $todate; ?>').attr('data-alt-value', '<?php echo $todate; ?>');
	
	setTimeout(function() {
		togglePromotion();
	}, 100);

	jQuery(document).keydown(function(e) {
		if (e.keyCode == 27) {
			if (vbodialoghelp_on === true) {
				hideVboModalHelp();
			}
		}
	});

	jQuery(document).mouseup(function(e) {
		if (!vbodialoghelp_on) {
			return;
		}
		if (vbodialoghelp_on) {
			var vbo_overlay_cont = jQuery(".vbo-modal-overlay-content-promofphelp");
			if (!vbo_overlay_cont.is(e.target) && vbo_overlay_cont.has(e.target).length === 0) {
				hideVboModalHelp();
			}
		}
	});

	if (typeof jQuery.fn.tooltip === 'function') {
		jQuery(".hasTooltip").tooltip();
	} else {
		jQuery.fn.tooltip = function(){};
	}
});

function vboOpenModalHelp() {
	jQuery('.vbo-modal-overlay-block-promofphelp').fadeIn();
	vbodialoghelp_on = true;
}

function hideVboModalHelp() {
	if (vbodialoghelp_on === true) {
		jQuery(".vbo-modal-overlay-block-promofphelp").fadeOut(400, function () {
			jQuery(".vbo-modal-overlay-content-promofphelp").show();
		});
		// turn flag off
		vbodialoghelp_on = false;
	}
}
</script>
<?php
} else {
	?>
<p class="err"><a href="index.php?option=com_vikbooking&amp;task=newroom"><?php echo JText::_('VBNOROOMSFOUNDSEASONS'); ?></a></p>
<form action="index.php?option=com_vikbooking" method="post" name="adminForm" id="adminForm">
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikbooking" />
	<?php echo JHtml::_('form.token'); ?>
</form>
	<?php
}
