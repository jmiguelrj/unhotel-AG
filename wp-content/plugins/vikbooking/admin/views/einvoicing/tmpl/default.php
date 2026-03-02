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

$driver_objs = $this->driver_objs;

$config = VBOFactory::getConfig();

$vbo_app = VikBooking::getVboApplication();
$vbo_app->loadSelect2();
$pdriver = VikRequest::getString('driver', '', 'request');
$pkrsort = VikRequest::getString('krsort', '', 'request');
$pkrorder = VikRequest::getString('krorder', '', 'request');
$pexecdriver = VikRequest::getString('execdriver', '', 'request');
$execdriver = !empty($pexecdriver);
$pdriveraction = VikRequest::getString('driveraction', '', 'request');

$driver_obj = null;

// if only one driver, we force it to be pre-selected
if (count($driver_objs) === 1) {
	$pdriver = $driver_objs[0]->getFileName();
	$execdriver = true;
} elseif (empty($pdriver)) {
	// check if we have the latest driver used
	$last_driver = $config->get('einvoicing_last_driver');
	if ($last_driver) {
		$pdriver = $last_driver;
		$execdriver = true;
	}
}

// get active driver
foreach ($driver_objs as $obj) {
	if ($obj->getFileName() == $pdriver) {
		// get current driver object
		$driver_obj = $obj;
		// update last driver
		$config->set('einvoicing_last_driver', $pdriver);
		// check flag status
		if ($driver_obj->hasFiltersSet()) {
			/**
			 * if the driver has some session values, call immediately
			 * getBookingsData() by flagging the post execution to true.
			 */
			$pexecdriver = 1;
		}
		break;
	}
}

if (!is_null($driver_obj) && $execdriver && !empty($pdriveraction)) {
	// call driver action
	if (method_exists($driver_obj, $pdriveraction)) {
		$driver_obj->{$pdriveraction}();
	}
}

?>

<div class="vbo-modal-overlay-block vbo-modal-overlay-block-einvoicing">
	<a class="vbo-modal-overlay-close" href="javascript: void(0);"></a>
	<div class="vbo-modal-overlay-content vbo-modal-overlay-content-einvoicing vbo-info-overlay-driver">
		<div class="vbo-modal-overlay-content-head vbo-modal-overlay-content-head-einvoicing">
			<h3><span id="vbo-modal-einvoicing-title"><?php echo !is_null($driver_obj) ? $driver_obj->getName() : ''; ?></span> <span class="vbo-modal-overlay-close-times" onclick="vboHideOverlay();">&times;</span></h3>
		</div>
		<div class="vbo-modal-overlay-content-body vbo-modal-overlay-content-body-scroll">
			<div class="vbo-info-overlay-driver-content">
				<form name="drivercontent" action="index.php?option=com_vikbooking&task=einvoicing" method="post" enctype="multipart/form-data" id="drivercontentform">
				<?php
				if (!is_null($driver_obj)) {
					$driver_obj->printOverlayContent();
				}
				?>
				</form>
			</div>
			<div class="vbo-info-overlay-driver-settings">
				<form name="driversettings" action="index.php?option=com_vikbooking&task=einvoicing" method="post" enctype="multipart/form-data" id="driversettingsform">
				<?php
				if (!is_null($driver_obj) && $driver_obj->hasSettings()) {
					$driver_obj->printSettings();
				}
				?>
					<input type="hidden" name="e4j_debug" value="<?php echo VikRequest::getInt('e4j_debug', 0, 'request'); ?>" />
					<input type="hidden" name="task" value="einvoicing" />
					<input type="hidden" name="option" value="com_vikbooking" />
				</form>
			</div>
		</div>
		<div class="vbo-modal-overlay-content-footer">
			<div class="vbo-modal-overlay-content-footer-left">
				<button type="button" class="btn btn-secondary" onclick="vboHideOverlay();"><?php echo JText::_('VBANNULLA'); ?></button>
			</div>
			<div class="vbo-modal-overlay-content-footer-right">
				<button type="button" class="btn btn-success" onclick="vboSubmitHelperModal();"><i class="vboicn-checkmark"></i> <?php echo JText::_('VBSAVE'); ?></button>
			</div>
		</div>
	</div>
</div>

<div class="vbo-drivers-container">
	<form name="adminForm" action="index.php?option=com_vikbooking&task=einvoicing" method="post" enctype="multipart/form-data" id="adminForm">
		<div class="vbo-drivers-filters-outer vbo-btn-toolbar">
			<div class="vbo-drivers-topbar">
				<div class="vbo-drivers-filters-main">
					<select id="choose-driver" name="driver" onchange="document.adminForm.submit();">
						<option></option>
					<?php
					foreach ($driver_objs as $obj) {
						?>
						<option value="<?php echo $obj->getFileName(); ?>"<?php echo $obj->getFileName() == $pdriver ? ' selected="selected"' : ''; ?>><?php echo $obj->getName(); ?></option>
						<?php
					}
					?>
					</select>
				</div>
			<?php
			if ($driver_obj !== null && $driver_obj->hasSettings()) {
				?>
				<div class="vbo-drivers-filters-settings">
					<a href="JavaScript: void(0);" onclick="vboShowDriverSettings();" class="vbcsvexport"><i class="vboicn-cogs icn-nomargin"></i> <span><?php echo JText::_('VBODRIVERSETTINGS'); ?></span></a>
				</div>
				<?php
			}
			?>
			</div>
			<div class="vbo-drivers-wrap-middlebar">
			<?php
			$driver_filters = $driver_obj !== null ? $driver_obj->getFilters() : [];
			if ($driver_filters || $driver_obj !== null) {
				?>
				<div class="vbo-drivers-filters-driver">
				<?php
				foreach ($driver_filters as $filt) {
					?>
					<div class="vbo-report-filter-wrap vbo-driver-filter-wrap">
					<?php
					if (!empty($filt['label'])) {
						?>
						<div class="vbo-report-filter-lbl vbo-driver-filter-lbl">
							<span><?php echo $filt['label']; ?></span>
						</div>
						<?php
					}
					if (!empty($filt['html'])) {
						?>
						<div class="vbo-report-filter-val vbo-driver-filter-val">
							<?php echo $filt['html']; ?>
						</div>
						<?php
					}
					?>
					</div>
					<?php
				}
				if ($driver_obj !== null) {
					?>
					<div class="vbo-drivers-filters-launch">
						<input type="submit" class="btn" name="execdriver" value="<?php echo JText::_('VBODRIVERLOAD'); ?>" />
					</div>
					<?php
				}
				?>
				</div>
				<?php
			}
			if ($driver_obj !== null) {
				?>
				<div class="vbo-drivers-custom-actions">
					<?php
					if ($execdriver) {
						// print buttons for the driver custom actions
						foreach ($driver_obj->getButtons() as $htmlbutton) {
							?>
					<div class="vbo-driver-custom-button">
						<?php echo $htmlbutton; ?>
					</div>
							<?php
						}
					}
					?>
				</div>
				<?php
			}
			?>
			</div>
		</div>
		<div id="vbo_hidden_fields"></div>
		<input type="hidden" name="krsort" value="<?php echo $pkrsort; ?>" />
		<input type="hidden" name="krorder" value="<?php echo $pkrorder; ?>" />
		<input type="hidden" name="e4j_debug" value="<?php echo VikRequest::getInt('e4j_debug', 0, 'request'); ?>" />
		<input type="hidden" name="goto" value="<?php echo base64_encode('index.php?option=com_vikbooking&task=einvoicing'); ?>" />
		<input type="hidden" name="task" value="einvoicing" />
		<input type="hidden" name="option" value="com_vikbooking" />
	</form>
<?php
if ($driver_obj !== null && $execdriver && !empty($pexecdriver)) {
	// execute the driver
	$res = $driver_obj->getBookingsData();

	?>
	<div class="vbo-drivers-output">
	<?php
	if (!$res) {
		// error generating the driver
		?>
		<p class="vbo-dismiss-message err"><?php echo $driver_obj->getError(); ?></p>
		<?php
		// attempt to display also any warning message in case something was set
		if (strlen($driver_obj->getWarning())) {
			?>
		<p class="vbo-dismiss-message warn"><?php echo $driver_obj->getWarning(); ?></p>
			<?php
		}
	} else {
		//display the driver and set default ordering and sorting
		if (empty($pkrsort) && property_exists($driver_obj, 'defaultKeySort')) {
			$pkrsort = $driver_obj->defaultKeySort;
		}
		if (empty($pkrorder) && property_exists($driver_obj, 'defaultKeyOrder')) {
			$pkrorder = $driver_obj->defaultKeyOrder;
		}
		if (strlen($driver_obj->getInfo())) {
			// info message should not stop the driver from rendering
			?>
			<p class="vbo-dismiss-message info"><?php VikBookingIcons::e('info-circle'); ?> <?php echo $driver_obj->getInfo(); ?></p>
			<?php
		}
		if (strlen($driver_obj->getWarning())) {
			// warning message should not stop the driver from rendering
			?>
			<p class="vbo-dismiss-message warn"><?php echo $driver_obj->getWarning(); ?></p>
			<?php
		}
		?>
		<div class="table-responsive">
			<table class="table">
				<thead>
					<tr>
					<?php
					foreach ($driver_obj->getDriverCols() as $col) {
						$col_cont = (isset($col['tip']) ? $vbo_app->createPopover(array('title' => $col['label'], 'content' => $col['tip'])) : '').$col['label'];
						?>
						<th<?php echo ($col['attr'] ?? []) ? ' '.implode(' ', $col['attr']) : ''; ?>>
						<?php
						if (isset($col['sortable'])) {
							$krorder = $pkrsort == $col['key'] && $pkrorder == 'DESC' ? 'ASC' : 'DESC';
							?>
							<a href="JavaScript: void(0);" onclick="vboSetFilters({krsort: '<?php echo $col['key']; ?>', krorder: '<?php echo $krorder; ?>'}, true);" class="<?php echo $pkrsort == $col['key'] ? 'vbo-list-activesort' : ''; ?>">
								<span><?php echo $col_cont; ?></span>
								<i class="fa <?php echo $pkrsort == $col['key'] && $krorder == 'DESC' ? 'fa-sort-asc' : ($pkrsort == $col['key'] ? 'fa-sort-desc' : 'fa-sort'); ?>"></i>
							</a>
							<?php
						} else {
							?>
							<span><?php echo $col_cont; ?></span>
							<?php
						}
						?>
						</th>
						<?php
					}
					?>
					</tr>
				</thead>
				<tbody>
				<?php
				foreach ($driver_obj->getDriverRows() as $row) {
					?>
					<tr>
					<?php
					foreach ($row as $cell) {
						?>
						<td<?php echo ($cell['attr'] ?? []) ? ' '.implode(' ', $cell['attr']) : ''; ?>>
							<span><?php echo is_callable($cell['callback'] ?? null) ? $cell['callback']($cell['value']) : $cell['value']; ?></span>
						</td>
						<?php
					}
					?>
					</tr>
					<?php
				}
				?>
				</tbody>
			<?php
			if (count($driver_obj->getDriverFooterRow())) {
				?>
				<tfoot>
				<?php
				foreach ($driver_obj->getDriverFooterRow() as $row) {
					?>
					<tr>
					<?php
					foreach ($row as $cell) {
						?>
						<td<?php echo ($cell['attr'] ?? []) ? ' '.implode(' ', $cell['attr']) : ''; ?>>
							<span><?php echo is_callable($cell['callback'] ?? null) ? $cell['callback']($cell['value']) : $cell['value']; ?></span>
						</td>
						<?php
					}
					?>
					</tr>
					<?php
				}
				?>
				</tfoot>
				<?php
			}
			?>
			</table>
		</div>
		<?php
	}
	?>
	</div>
	<?php
}
?>
</div>

<script type="text/javascript">
function vboSetFilters(obj, dosubmit) {
	if (typeof obj != "object") {
		console.log("arg is not an object");
		return;
	}
	for (var p in obj) {
		if (!obj.hasOwnProperty(p)) {
			continue;
		}
		var elem = document.adminForm[p];
		if (elem) {
			document.adminForm[p].value = obj[p];
		} else {
			document.getElementById("vbo_hidden_fields").innerHTML += "<input type='hidden' name='"+p+"' value='"+obj[p]+"' />";
		}
	}
	if (!obj.hasOwnProperty('execdriver') && !document.getElementById('execdriverinp')) {
		document.getElementById("vbo_hidden_fields").innerHTML += "<input type='hidden' id='execdriverinp' name='execdriver' value='1' />";
	}
	if (dosubmit) {
		document.adminForm.submit();
	}
}

function vboDriverDoAction(action, blank) {
	if (blank) {
		document.adminForm.target = '_blank';
		document.adminForm.action += '&tmpl=component';
	}
	vboSetFilters({driveraction: action}, true);
	if (blank) {
		setTimeout(function() {
			document.adminForm.target = '';
			document.adminForm.action = document.adminForm.action.replace('&tmpl=component', '');
			vboSetFilters({driveraction: '0'}, false);
		}, 1000);
	}
}

var vbo_overlay_on = false;

function vboShowOverlay() {
	jQuery(".vbo-modal-overlay-block-einvoicing").fadeIn(400, function() {
		if (jQuery(".vbo-modal-overlay-block-einvoicing").is(":visible")) {
			vbo_overlay_on = true;
		} else {
			vbo_overlay_on = false;
		}
	});
}

function vboHideOverlay() {
	jQuery(".vbo-modal-overlay-block-einvoicing").fadeOut();
	vbo_overlay_on = false;
}

function vboShowDriverSettings() {
	jQuery('.vbo-info-overlay-driver-content').hide();
	jQuery('.vbo-info-overlay-driver-settings').show();
	vboShowOverlay();
}

function vboShowDriverContent() {
	jQuery('.vbo-info-overlay-driver-settings').hide();
	jQuery('.vbo-info-overlay-driver-content').show();
	vboShowOverlay();
}

function vboSubmitHelperModal() {
	if (jQuery('.vbo-info-overlay-driver-settings').is(':visible')) {
		jQuery('#driversettingsform').submit();
		return true;
	}
	if (jQuery('.vbo-info-overlay-driver-content').is(':visible')) {
		jQuery('#drivercontentform').submit();
		return true;
	}
	return false;
}

jQuery(function() {
	jQuery("#choose-driver").select2({
		placeholder: '<?php echo addslashes(JText::_('VBODRIVERSELECT')); ?>',
		width: "300px",
	});
	jQuery(document).mouseup(function(e) {
		if (!vbo_overlay_on) {
			return;
		}
		var vbo_overlay_cont = jQuery(".vbo-modal-overlay-content-einvoicing");
		if (!vbo_overlay_cont.is(e.target) && vbo_overlay_cont.has(e.target).length === 0) {
			vboHideOverlay();
		}
	});
	jQuery(document).keyup(function(e) {
		if (e.keyCode == 27 && vbo_overlay_on) {
			vboHideOverlay();
		}
	});
	jQuery('.vbo-dismiss-message').dblclick(function() {
		jQuery(this).remove();
	});
});
<?php echo $driver_obj !== null && strlen($driver_obj->getScript()) ? $driver_obj->getScript() : ''; ?>
</script>
