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

$rows = $this->rows;
$lim0 = $this->lim0;
$navbut = $this->navbut;

$vbo_app = VikBooking::getVboApplication();
$vbo_app->prepareModalBox();
$currencysymb = VikBooking::getCurrencySymb();
if (empty($rows)) {
	?>
	<p class="warn"><?php echo JText::_('VBNOOPTIONALSFOUND'); ?></p>
	<form action="index.php?option=com_vikbooking" method="post" name="adminForm" id="adminForm">
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikbooking" />
	</form>
	<?php
} else {
	?>
<form action="index.php?option=com_vikbooking" method="post" name="adminForm" id="adminForm" class="vbo-list-form">
<div class="table-responsive">
	<table cellpadding="4" cellspacing="0" border="0" width="100%" class="table table-striped vbo-list-table">
		<thead>
			<tr>
				<th width="20">
					<input type="checkbox" onclick="Joomla.checkAll(this)" value="" name="checkall-toggle">
				</th>
				<th class="title left" width="150"><?php echo JText::_( 'VBPVIEWOPTIONALSONE' ); ?></th>
				<th class="title left" width="150"><?php echo JText::_( 'VBPVIEWOPTIONALSTWO' ); ?></th>
				<th class="title center" align="center" width="75"><?php echo JText::_( 'VBPVIEWOPTIONALSTHREE' ); ?></th>
				<th class="title center" align="center" width="75"><?php echo JText::_( 'VBPVIEWOPTIONALSFOUR' ); ?></th>
				<th class="title center" align="center" width="75"><?php echo JText::_( 'VBPVIEWOPTIONALSEIGHT' ); ?></th>
				<th class="title center" align="center" width="75"><?php echo JText::_( 'VBPVIEWOPTIONALSFIVE' ); ?></th>
				<th class="title center" align="center" width="75"><?php echo JText::_( 'VBPVIEWOPTIONALSPERPERS' ); ?></th>
				<th class="title center" align="center" width="150"><?php echo JText::_( 'VBPVIEWOPTIONALSSIX' ); ?></th>
				<th class="title left" width="150"><?php echo JText::_( 'VBPVIEWOPTIONALSSEVEN' ); ?></th>
				<th class="title center" align="center" width="50"><?php echo JText::_( 'VBPVIEWOPTIONALSORDERING' ); ?></th>
			</tr>
		</thead>
	<?php
	$k = 0;
	$i = 0;
	$bcom_dmg_dep = false;
	for ($i = 0, $n = count($rows); $i < $n; $i++) {
		$row = $rows[$i];
		$oparams = !empty($row['oparams']) ? json_decode($row['oparams'], true) : array();
		$oparams = !is_array($oparams) ? array() : $oparams;
		?>
		<tr class="row<?php echo $k; ?>">
			<td><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row['id']; ?>" onclick="Joomla.isChecked(this.checked);"></td>
			<td class="vbo-highlighted-td"><a href="index.php?option=com_vikbooking&amp;task=editoptional&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a></td>
			<td class="vbo-table-td-wrap">
			<?php
			echo strlen((string)$row['descr']) > 150 ? substr(strip_tags($row['descr']), 0, 150) : strip_tags((string)$row['descr']);
			if ($this->bcom_enabled && !empty($oparams['damagedep'])) {
				// trigger Booking.com Damage Deposit API
				$bcom_dmg_dep = true;
				?>
				<div class="vbo-optionals-dmgdepbcom-trigger">
					<button type="button" class="btn btn-primary btn-small" onclick="vboTransmitDmgDepTrigger('<?php echo $row['id']; ?>', '<?php echo $row['cost']; ?>', <?php echo !empty($oparams['damagedep_bcom_dt']) ? "'{$oparams['damagedep_bcom_dt']}'" : 'null'; ?>);"><?php VikBookingIcons::e('cloud-upload-alt'); ?> <?php echo JText::sprintf('VBOTRANSMITDMGDEPTOOTA', 'Booking.com'); ?></button>
				</div>
				<?php
			}
			?>
			</td>
			<td class="center"><?php echo (int)$row['pcentroom'] ? $row['cost'] . '%' : VikBooking::formatCurrencyNumber($row['cost'], $currencysymb); ?></td>
			<td class="center"><?php echo !empty($row['idiva']) ? VikBooking::getAliq($row['idiva']).'%' : ''; ?></td>
			<td class="center"><?php echo VikBooking::formatCurrencyNumber((float) $row['maxprice'], $currencysymb); ?></td>
			<td class="center">
			<?php
			if ((int)$row['perday'] == 1) {
				?>
				<i class="<?php echo VikBookingIcons::i('check', 'vbo-icn-img'); ?>" style="color: #099909;"></i>
				<?php
			} else {
				?>
				<i class="<?php echo VikBookingIcons::i('times', 'vbo-icn-img'); ?>" style="color: #ff0000;"></i>
				<?php
			}
			?>
			</td>
			<td class="center">
			<?php
			if ((int)$row['perperson'] == 1) {
				?>
				<i class="<?php echo VikBookingIcons::i('check', 'vbo-icn-img'); ?>" style="color: #099909;"></i>
				<?php
			} else {
				?>
				<i class="<?php echo VikBookingIcons::i('times', 'vbo-icn-img'); ?>" style="color: #ff0000;"></i>
				<?php
			}
			?>
			</td>
			<td class="center"><?php echo (intval($row['hmany'])==1 ? "&gt; 1" : "1"); ?></td>
			<td><?php echo (file_exists(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.$row['img']) ? '<a href="'.VBO_SITE_URI.'resources/uploads/'.$row['img'].'" class="vbomodal" target="_blank">'.$row['img'].'</a>' : ''); ?></td>
			<td class="center">
				<a href="<?php echo VBOFactory::getPlatform()->getUri()->addCSRF('index.php?option=com_vikbooking&task=sortoption&cid[]=' . $row['id'] . '&mode=up', true); ?>"><?php VikBookingIcons::e('arrow-up', 'vbo-icn-img'); ?></a> 
				<a href="<?php echo VBOFactory::getPlatform()->getUri()->addCSRF('index.php?option=com_vikbooking&task=sortoption&cid[]=' . $row['id'] . '&mode=down', true); ?>"><?php VikBookingIcons::e('arrow-down', 'vbo-icn-img'); ?></a>
			</td>
		</tr>
		  <?php
		$k = 1 - $k;
	}
	?>
	</table>
</div>
	<input type="hidden" name="option" value="com_vikbooking" />
	<input type="hidden" name="task" value="optionals" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHtml::_('form.token'); ?>
	<?php echo $navbut; ?>
</form>
<?php
	if ($bcom_dmg_dep === true) {
		/**
		 * We allow some options of type damage deposit to be transmitted to Booking.com via VCM.
		 * 
		 * @since 	1.13.5
		 */
		// lang vars for JS
		JText::script('VBOTRANSMITDMGDEPLASTDT');
		?>
		<a id="vbo-dd-reloadlink" style="display: none;" href="index.php?option=com_vikbooking&task=optionals"></a>

		<div class="vbo-modal-overlay-block vbo-modal-overlay-block-bcomdmgdeposit">
			<a class="vbo-modal-overlay-close" href="javascript: void(0);"></a>
			<div class="vbo-modal-overlay-content vbo-modal-overlay-content-bcomdmgdeposit">
				<div class="vbo-modal-overlay-content-head vbo-modal-overlay-content-head-bcomdmgdeposit">
					<h3><?php VikBookingIcons::e('cloud-upload-alt'); ?> <?php echo JText::sprintf('VBOTRANSMITDMGDEPTOOTA', 'Booking.com'); ?> <span class="vbo-modal-overlay-close-times" onclick="hideVboDialogBcomDmgDeposit();">&times;</span></h3>
				</div>
				<div class="vbo-modal-overlay-content-body">
					<div class="vbo-modal-bcomdmgdeposit-addnew" data-optid="">
						<div class="vbo-modal-bcomdmgdeposit-report-elems"></div>
						<div class="vbo-modal-bcomdmgdeposit-addnew-save">
							<button type="button" id="vbo-bcomdmgdeposit-submit-btn" class="btn btn-success" onclick="vboSubmitBcomDmgDeposit();" style="display: none;"><?php echo JText::_('VBCHANNELMANAGERSENDRQ'); ?></button>
							<button type="button" class="btn btn-secondary" onclick="hideVboDialogBcomDmgDeposit();"><?php echo JText::_('VBOCLOSE'); ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>

		<script type="text/javascript">
		var vbodialogbcomdmgdeposit_on = false;
		var vbo_damage_deposit_fields = new Array;

		function hideVboDialogBcomDmgDeposit() {
			if (vbodialogbcomdmgdeposit_on === true) {
				jQuery(".vbo-modal-overlay-block-bcomdmgdeposit").fadeOut(400, function () {
					jQuery(".vbo-modal-overlay-content-bcomdmgdeposit").show();
				});
				// turn flag off
				vbodialogbcomdmgdeposit_on = false;
			}
		}

		function vboTransmitDmgDepTrigger(optid, optcost, last_dt) {
			// empty content
			jQuery('.vbo-modal-bcomdmgdeposit-report-elems').html('<?php echo $this->escape(JText::_('VIKLOADING') . '...'); ?>');
			vbo_damage_deposit_fields = new Array;
			// display modal
			jQuery('.vbo-modal-overlay-block-bcomdmgdeposit').fadeIn();
			vbodialogbcomdmgdeposit_on = true;
			// make AJAX request to VCM to always reload the form fields
			var jqxhr = jQuery.ajax({
				type: "POST",
				url: "<?php echo VikBooking::ajaxUrl('index.php?option=com_vikchannelmanager&task=get_bcom_security_deposit_fields'); ?>",
				data: {
					tmpl: "component"
				}
			}).done(function(res) {
				// parse the JSON response that contains the fields for the request
				try {
					var dmgdep_fields_rs = JSON.parse(res);
					console.log(dmgdep_fields_rs);

					// make sure the request was successful
					if (!dmgdep_fields_rs.status) {
						alert(dmgdep_fields_rs.error);
						hideVboDialogBcomDmgDeposit();
						return false;
					}

					// render form from response obtained
					var dmgdep_html = '';
					var dmgdep_fields = dmgdep_fields_rs.data;

					if (!dmgdep_fields || !dmgdep_fields.length) {
						alert('Unexptected response. Vik Channel Manager needs to be installed, configured and updated to the latest version.');
						hideVboDialogBcomDmgDeposit();
						return false;
					}

					// check last submission date to avoid multiple submissions
					if (last_dt && (last_dt + '').length) {
						var tnstring = Joomla.JText._('VBOTRANSMITDMGDEPLASTDT') + '';
						dmgdep_html += '<h4>' + tnstring.replace('%s', last_dt) + '</h4>' + "\n";
					}

					// parse fields
					for (var field in dmgdep_fields) {
						if (!dmgdep_fields.hasOwnProperty(field)) {
							continue;
						}
						var field_id = 'vbo-dmgdep-field-' + dmgdep_fields[field]['name'];
						var field_name = dmgdep_fields[field]['name'];
						var field_value = dmgdep_fields[field].hasOwnProperty('value') && dmgdep_fields[field]['value'] ? (dmgdep_fields[field]['value'] + '') : '';
						dmgdep_html += '<div class="vbo-modal-form-addnew-elem vbo-modal-bcomdmgdeposit-addnew-elem">' + "\n";
						dmgdep_html += '<label for="' + field_id + '">' + dmgdep_fields[field]['label'] + '</label>';
						if (dmgdep_fields[field]['type'] == 'number') {
							dmgdep_html += '<input data-ddname="' + field_name + '" type="number" id="' + field_id + '" value="' + (!field_value.length && optcost && optcost.length ? optcost : field_value) + '" step="any" min="0" />';
						} else if (dmgdep_fields[field]['type'] == 'text') {
							dmgdep_html += '<input data-ddname="' + field_name + '" type="text" id="' + field_id + '" value="' + field_value + '" />';
						} else if (dmgdep_fields[field]['type'] == 'text') {
							dmgdep_html += '<input data-ddname="' + field_name + '" type="text" id="' + field_id + '" value="' + field_value + '" />';
						} else if (dmgdep_fields[field]['type'] == 'textarea') {
							dmgdep_html += '<textarea data-ddname="' + field_name + '" id="' + field_id + '" rows="5" cols="50" maxlength="240">' + field_value + '</textarea>';
						} else if (dmgdep_fields[field]['type'] == 'select') {
							dmgdep_html += '<select data-ddname="' + field_name + '" id="' + field_id + '">';
							for (var choicekey in dmgdep_fields[field]['choices']) {
								if (!dmgdep_fields[field]['choices'].hasOwnProperty(choicekey)) {
									continue;
								}
								dmgdep_html += '<option value="' + choicekey + '"' + (field_value == choicekey ? ' selected="selected"' : '') + '>' + dmgdep_fields[field]['choices'][choicekey] + '</option>';
							}
							dmgdep_html += '</select>';
						}
						dmgdep_html += '</div>' + "\n";
						// push field in fields list for later submit
						vbo_damage_deposit_fields.push(field_id);
					}

					// display HTML content and submit button
					jQuery('.vbo-modal-bcomdmgdeposit-report-elems').html(dmgdep_html);
					jQuery('#vbo-bcomdmgdeposit-submit-btn').show();
					// set id option for later update request
					jQuery('.vbo-modal-bcomdmgdeposit-addnew').attr('data-optid', optid);
				} catch (e) {
					console.log(res);
					alert('Invalid response. Vik Channel Manager needs to be installed, configured and updated to the latest version.');
					hideVboDialogBcomDmgDeposit();
					return false;
				}
			}).fail(function() {
				alert('Request failed. Vik Channel Manager needs to be installed, configured and updated to the latest version.');
				hideVboDialogBcomDmgDeposit();
			});
		}

		function vboSubmitBcomDmgDeposit() {
			if (!vbo_damage_deposit_fields.length) {
				alert('Empty data to submit. Vik Channel Manager needs to be installed, configured and updated to the latest version.');
				return false;
			}
			
			// prevent double submissions
			vboBcomDmgDepositDisableSubmit();
			//

			var submit_fields = {
				tmpl: "component",
				optid: jQuery('.vbo-modal-bcomdmgdeposit-addnew').attr('data-optid'),
				bcom_keys: new Array
			};
			for (var elemid in vbo_damage_deposit_fields) {
				if (!vbo_damage_deposit_fields.hasOwnProperty(elemid)) {
					continue;
				}
				if (!jQuery('#' + vbo_damage_deposit_fields[elemid]).length || !jQuery('#' + vbo_damage_deposit_fields[elemid]).closest('.vbo-modal-bcomdmgdeposit-addnew-elem').length) {
					// form element not found, or parent container not found
					continue;
				}
				if (jQuery('#' + vbo_damage_deposit_fields[elemid]).closest('.vbo-modal-bcomdmgdeposit-addnew-elem').is(':visible')) {
					// we include this value in the AJAX request
					var field_name = jQuery('#' + vbo_damage_deposit_fields[elemid]).attr('data-ddname');
					var submit_key = 'bcom_' + field_name;
					submit_fields[submit_key] = jQuery('#' + vbo_damage_deposit_fields[elemid]).val();
					submit_fields['bcom_keys'].push(submit_key);
				}
			}

			// make the AJAX request to VCM
			var jqxhr = jQuery.ajax({
				type: "POST",
				url: "<?php echo VikBooking::ajaxUrl('index.php?option=com_vikchannelmanager&task=submit_bcom_security_deposit'); ?>",
				data: submit_fields
			}).done(function(res) {
				try {
					var dmgdep_rs = JSON.parse(res);
					console.log(submit_fields, dmgdep_rs);

					// make sure the request was successful
					if (!dmgdep_rs.status) {
						alert(dmgdep_rs.error);
						vboBcomDmgDepositEnableSubmit();
						return false;
					}

					// get current date and time
					var date_obj = new Date();
					var now_month = date_obj.getMonth() + 1;
					now_month = now_month < 10 ? ('0' + now_month) : (now_month + '');
					var now_day = date_obj.getDate();
					now_day = now_day < 10 ? ('0' + now_day) : (now_day + '');
					var now_year = date_obj.getFullYear();
					var now_full_dt = now_year + '-' + now_month + '-' + now_day + ' ' + date_obj.getHours() + ':' + date_obj.getMinutes();

					// make another AJAX request (now on VBO) to update the option params and save the last transmission date
					jQuery.ajax({
						type: "POST",
						url: "<?php echo VikBooking::ajaxUrl('index.php?option=com_vikbooking&task=update_option_params'); ?>",
						data: {
							tmpl: "component",
							optid: jQuery('.vbo-modal-bcomdmgdeposit-addnew').attr('data-optid'),
							oparams: {
								damagedep_bcom_dt: now_full_dt
							}
						}
					}).done(function(res) {
						// just reload the page in case of success
						document.location.href = jQuery('#vbo-dd-reloadlink').attr('href');
					}).fail(function() {
						// we reload the page even in case of failure
						document.location.href = jQuery('#vbo-dd-reloadlink').attr('href');
					});
				} catch (e) {
					console.log(res);
					alert('Invalid update response. Vik Channel Manager needs to be installed, configured and updated to the latest version.');
					vboBcomDmgDepositEnableSubmit();
					hideVboDialogBcomDmgDeposit();
					return false;
				}
			}).fail(function() {
				alert('Update Request failed. Vik Channel Manager needs to be installed, configured and updated to the latest version.');
				vboBcomDmgDepositEnableSubmit();
				return false;
			});
		}

		function vboBcomDmgDepositDisableSubmit() {
			jQuery('#vbo-bcomdmgdeposit-submit-btn').prepend('<i class="<?php echo VikBookingIcons::i('refresh', 'fa-spin fa-fw'); ?>"></i> ').prop('disabled', true);
		}

		function vboBcomDmgDepositEnableSubmit() {
			jQuery('#vbo-bcomdmgdeposit-submit-btn').prop('disabled', false).find('i').remove();
		}
		</script>
		<?php
	}
}
