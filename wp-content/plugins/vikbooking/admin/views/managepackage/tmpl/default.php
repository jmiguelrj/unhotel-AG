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

$package = $this->package;
$rooms = $this->rooms;

$vbo_app = VikBooking::getVboApplication();
$vbo_app->loadSelect2();
JHtml::_('behavior.tooltip');
$editor = JEditor::getInstance(JFactory::getApplication()->get('editor'));
$df = VikBooking::getDateFormat(true);
$excldf = 'Y-m-d';
if ($df == "%d/%m/%Y") {
	$usedf = 'd/m/Y';
	$excldf = 'd-m-Y';
} elseif ($df == "%m/%d/%Y") {
	$usedf = 'm/d/Y';
} else {
	$usedf = 'Y/m/d';
}
$currencysymb = VikBooking::getCurrencySymb();
$dbo = JFactory::getDbo();
$q = "SELECT * FROM `#__vikbooking_iva`;";
$dbo->setQuery($q);
$dbo->execute();
if ($dbo->getNumRows() > 0) {
	$ivas = $dbo->loadAssocList();
	$wiva = "<select name=\"aliq\"><option value=\"\"> </option>\n";
	foreach ($ivas as $iv) {
		$wiva .= "<option value=\"".$iv['id']."\"".(count($package) && $package['idiva']==$iv['id'] ? " selected=\"selected\"" : "").">".(empty($iv['name']) ? $iv['aliq']."%" : $iv['name']."-".$iv['aliq']."%")."</option>\n";
	}
	$wiva .= "</select>\n";
} else {
	$wiva = "<a href=\"index.php?option=com_vikbooking&task=iva\">".JText::_('VBNOIVAFOUND')."</a>";
}
$actexcludedays = "";
if (count($package)) {
	$diff = $package['dto'] - $package['dfrom'];
	$oldexcluded = !empty($package['excldates']) ? explode(";", $package['excldates']) : array();
	if ($diff >= 172800) {
		$daysdiff = floor($diff / 86400);
		$infoinit = getdate($package['dfrom']);
		$actexcludedays .= '<select name="excludeday[]" multiple="multiple" size="'.($daysdiff > 8 ? 8 : $daysdiff).'">';
		for ($i = 0; $i <= $daysdiff; $i++) {
			$ts = $i > 0 ? mktime(0, 0, 0, $infoinit['mon'], ((int)$infoinit['mday'] + $i), $infoinit['year']) : $package['dfrom'];
			$infots = getdate($ts);
			$optval = $infots['mon'].'-'.$infots['mday'].'-'.$infots['year'];
			$actexcludedays .= '<option value="'.$optval.'"'.(in_array($optval, $oldexcluded) ? ' selected="selected"' : '').'>'.date($excldf, $ts).'</option>';
		}
		$actexcludedays .= '</select>';
	}
}
$vbo_app->prepareModalBox();
?>
<script type="text/javascript">
function showResizeSel() {
	if (document.adminForm.autoresize.checked == true) {
		document.getElementById('resizesel').style.display='inline-block';
	} else {
		document.getElementById('resizesel').style.display='none';
	}
	return true;
}
function vboExcludeWDays() {
	var excludewdays = document.getElementById('excludewdays');
	var vboexclusion = document.getElementById('vboexclusion');
	var weekday = '0';
	var setnew = false;
	var curdate = '';
	var curwday = '0';
	for(i = 0; i < excludewdays.length; i++) {
		weekday = parseInt(excludewdays.options[i].value);
		setnew = excludewdays.options[i].selected == false ? false : true;
		for(j = 0; j < vboexclusion.length; j++) {
			curdate = vboexclusion.options[j].value;
			var dateparts = curdate.split("-");
			var dobj = new Date(dateparts[2], (parseInt(dateparts[0]) - 1), dateparts[1]);
			curwday = parseInt(dobj.getDay());
			if (weekday == curwday) {
				vboexclusion.options[j].selected = setnew;
			}
		}
	}
}
jQuery(function() {
	jQuery('#vbo-rooms').select2();
	jQuery(".vbo-select-all").click(function() {
		var elem = jQuery(this).next("select");
		elem.find("option").prop('selected', true);
		elem.trigger('change');
	});
	jQuery('#vbo-pkg-calcexcld').click(function() {
		var fdate = jQuery('#from').val();
		var tdate = jQuery('#to').val();
		if (fdate.length && tdate.length) {
			jQuery('#vbo-pkg-excldates-td').html('');
			var jqxhr = jQuery.ajax({
				type: "POST",
				url: "<?php echo VikBooking::ajaxUrl('index.php?option=com_vikbooking&task=dayselectioncount'); ?>",
				data: {
					dinit: fdate,
					dend: tdate,
					tmpl: "component"
				}
			}).done(function(cont) {
				if (cont.length) {
					jQuery("#vbo-pkg-excldates-td").html(cont);
				} else {
					jQuery('#vbo-pkg-excldates-td').html('----');
				}
			}).fail(function() {
				alert("Error Calculating the dates for exclusion");
			});
		} else {
			jQuery('#vbo-pkg-excldates-td').html('----');
		}
	});
<?php
if (count($package)) :
?>
	jQuery("#from").val("<?php echo date($usedf, $package['dfrom']); ?>").attr('data-alt-value', "<?php echo date($usedf, $package['dfrom']); ?>");
	jQuery("#to").val("<?php echo date($usedf, $package['dto']); ?>").attr('data-alt-value', "<?php echo date($usedf, $package['dto']); ?>");
<?php
endif;
?>
});
</script>

<form name="adminForm" id="adminForm" action="index.php" method="post" enctype="multipart/form-data">
	<div class="vbo-admin-container">
		<div class="vbo-config-maintab-left">
			<fieldset class="adminform">
				<div class="vbo-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VBOADMINLEGENDDETAILS'); ?></legend>
					<div class="vbo-params-container">
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWPKGNAME'); ?></div>
							<div class="vbo-param-setting"><input type="text" name="name" value="<?php echo count($package) ? $package['name'] : ''; ?>" size="50"/></div>
						</div>
						<!-- @wponly  removed alias field -->
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWPKGIMG'); ?></div>
							<div class="vbo-param-setting">
								<div class="vbo-param-setting-block">
									<?php echo (count($package) && !empty($package['img']) && file_exists(VBO_SITE_PATH.DS.'resources'.DS.'uploads'.DS.'big_'.$package['img']) ? '<a href="'.VBO_SITE_URI.'resources/uploads/big_'.$package['img'].'" class="vbomodal vbo-room-img-modal" target="_blank"><i class="' . VikBookingIcons::i('image') . '"></i> '.$package['img'].'</a>' : ""); ?>
									<input type="file" name="img" size="35"/>
								</div>
								<div class="vbo-param-setting-block">
									<span class="vbo-resize-lb-cont">
										<label style="display: inline;" for="autoresize"><?php echo JText::_('VBNEWOPTNINE'); ?></label> 
										<input type="checkbox" id="autoresize" name="autoresize" value="1" onclick="showResizeSel();"/> 
									</span>
									<span id="resizesel" style="display: none;"><span><?php echo JText::_('VBNEWOPTTEN'); ?></span><input type="number" name="resizeto" value="250" min="0" class="vbo-medium-input"/> px</span>
								</div>
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWPKGDFROM'); ?></div>
							<div class="vbo-param-setting"><?php echo $vbo_app->getCalendar('', 'from', 'from', $df, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWPKGDTO'); ?></div>
							<div class="vbo-param-setting"><?php echo $vbo_app->getCalendar('', 'to', 'to', $df, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWPKGEXCLDATES'); ?></div>
							<div class="vbo-param-setting"><button type="button" class="btn" id="vbo-pkg-calcexcld"><i class="icon-refresh"></i></button><div id="vbo-pkg-excldates-td"><?php echo $actexcludedays; ?></div></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWPKGROOMS'); ?></div>
							<div class="vbo-param-setting">
								<span class="vbo-select-all"><?php echo JText::_('VBOSELECTALL'); ?></span>
								<select id="vbo-rooms" name="rooms[]" multiple="multiple" size="<?php echo (count($rooms) > 6 ? '6' : count($rooms)); ?>">
								<?php
								foreach ($rooms as $rk => $rv) {
									?>
									<option value="<?php echo $rv['id']; ?>"<?php echo (array_key_exists('selected', $rv) ? ' selected="selected"' : ''); ?>><?php echo $rv['name']; ?></option>
									<?php
								}
								?>
								</select>
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWPKGMINLOS'); ?></div>
							<div class="vbo-param-setting"><input type="number" name="minlos" id="minlos" value="<?php echo count($package) ? $package['minlos'] : '1'; ?>" min="1" size="5"/></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWPKGMAXLOS'); ?></div>
							<div class="vbo-param-setting"><input type="number" name="maxlos" id="maxlos" value="<?php echo count($package) ? $package['maxlos'] : '0'; ?>" min="0" size="5"/></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWPKGCOST'); ?></div>
							<div class="vbo-param-setting"><input type="number" step="any" min="0" name="cost" id="cost" value="<?php echo count($package) ? $package['cost'] : ''; ?>" style="min-width: 60px;"/> <?php echo $currencysymb; ?></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWOPTFOUR'); ?></div>
							<div class="vbo-param-setting"><?php echo $wiva; ?></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWPKGCOSTTYPE'); ?></div>
							<div class="vbo-param-setting"><select name="pernight_total"><option value="1"<?php echo (count($package) && $package['pernight_total'] == 1 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBNEWPKGCOSTTYPEPNIGHT'); ?></option><option value="2"<?php echo (count($package) && $package['pernight_total'] == 2 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBNEWPKGCOSTTYPETOTAL'); ?></option></select></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWPKGCOSTTYPEPPERSON'); ?></div>
							<div class="vbo-param-setting"><?php echo $vbo_app->printYesNoButtons('perperson', JText::_('VBYES'), JText::_('VBNO'), (count($package) ? (int)$package['perperson'] : 0), 1, 0); ?></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWPKGSHOWOPT'); ?></div>
							<div class="vbo-param-setting"><select name="showoptions"><option value="1"<?php echo (count($package) && $package['showoptions'] == 1 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBNEWPKGSHOWOPTALL'); ?></option><option value="2"<?php echo (count($package) && $package['showoptions'] == 2 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBNEWPKGSHOWOPTOBL'); ?></option><option value="3"<?php echo (count($package) && $package['showoptions'] == 3 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBNEWPKGHIDEOPT'); ?></option></select></div>
						</div>
					</div>
				</div>
			</fieldset>
		</div>
		<div class="vbo-config-maintab-right">
			<fieldset class="adminform">
				<div class="vbo-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VBOADMINLEGENDSETTINGS'); ?></legend>
					<div class="vbo-params-container">
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWPKGSHORTDESCR'); ?></div>
							<div class="vbo-param-setting"><textarea name="shortdescr" rows="4" cols="60"><?php echo count($package) ? $package['shortdescr'] : ''; ?></textarea></div>
						</div>
						<div class="vbo-param-container vbo-param-container-full">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWPKGDESCR'); ?></div>
							<div class="vbo-param-setting">
								<?php
								if (interface_exists('Throwable')) {
									/**
									 * With PHP >= 7 supporting throwable exceptions for Fatal Errors
									 * we try to avoid issues with third party plugins that make use
									 * of the WP native function get_current_screen().
									 * 
									 * @wponly
									 */
									try {
										echo $editor->display( "descr", (count($package) ? $package['descr'] : ""), '100%', 300, 70, 20 );
									} catch (Throwable $t) {
										echo $t->getMessage() . ' in ' . $t->getFile() . ':' . $t->getLine() . '<br/>';
									}
								} else {
									// we cannot catch Fatal Errors in PHP 5.x
									echo $editor->display( "descr", (count($package) ? $package['descr'] : ""), '100%', 300, 70, 20 );
								}
								?>
							</div>
						</div>
						<div class="vbo-param-container vbo-param-container-full">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWPKGCONDS'); ?></div>
							<div class="vbo-param-setting">
								<?php
								if (interface_exists('Throwable')) {
									/**
									 * With PHP >= 7 supporting throwable exceptions for Fatal Errors
									 * we try to avoid issues with third party plugins that make use
									 * of the WP native function get_current_screen().
									 * 
									 * @wponly
									 */
									try {
										echo $editor->display( "conditions", (count($package) ? $package['conditions'] : ""), '100%', 300, 70, 20 );
									} catch (Throwable $t) {
										echo $t->getMessage() . ' in ' . $t->getFile() . ':' . $t->getLine() . '<br/>';
									}
								} else {
									// we cannot catch Fatal Errors in PHP 5.x
									echo $editor->display( "conditions", (count($package) ? $package['conditions'] : ""), '100%', 300, 70, 20 );
								}
								?>
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWPKGBENEFITS'); ?></div>
							<div class="vbo-param-setting"><textarea name="benefits" placeholder="<?php echo JText::_('VBNEWPKGBENEFITSHELP'); ?>" rows="3" cols="60"><?php echo count($package) ? $package['benefits'] : ''; ?></textarea></div>
						</div>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
	<input type="hidden" name="task" value="">
	<input type="hidden" name="option" value="com_vikbooking">
<?php
if (count($package)) :
?>
	<input type="hidden" name="whereup" value="<?php echo $package['id']; ?>">
<?php
endif;
?>
	<?php echo JHtml::_('form.token'); ?>
</form>
