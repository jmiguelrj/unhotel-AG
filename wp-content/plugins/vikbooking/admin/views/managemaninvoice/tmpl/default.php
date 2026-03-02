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

$invoice = $this->invoice;
$taxrates = $this->taxrates;
$customer = count($invoice) ? VikBooking::getCPinIstance()->getCustomerByID($invoice['idcustomer']) : array();

$wiva = '<select name="aliq[]"><option value="">'.htmlspecialchars(JText::_('VBNEWIVATWO')).'</option>';
foreach ($taxrates as $iv) {
	$wiva .= '<option value="'.(float)$iv['aliq'].'" data-aliq="'.(float)$iv['aliq'].'" data-taxcap="'.(float)$iv['taxcap'].'">'.(empty($iv['name']) ? $iv['aliq']."%" : htmlentities($iv['name'], ENT_QUOTES)." (".$iv['aliq']."%)").'</option>';
}
$wiva .= '</select>';

$vbo_app = VikBooking::getVboApplication();
$df = VikBooking::getDateFormat(true);
if ($df == "%d/%m/%Y") {
	$usedf = 'd/m/Y';
} elseif ($df == "%m/%d/%Y") {
	$usedf = 'm/d/Y';
} else {
	$usedf = 'Y/m/d';
}
$currencysymb = VikBooking::getCurrencySymb();
$formatvals = VikBooking::getNumberFormatData();
$formatparts = explode(':', $formatvals);
?>

<form name="adminForm" id="adminForm" action="index.php" method="post" enctype="multipart/form-data">

	<div class="vbo-admin-container vbo-config-tab-container">

		<fieldset class="adminform">
			<div class="vbo-params-wrap vbo-params-wrap-fullwidth">
				<legend class="adminlegend"><?php echo JText::_('VBOCUSTOMERDETAILS'); ?></legend>
				<div class="vbo-params-container">

				<?php
				if (count($invoice) && count($customer)) {
					// edit mode
					?>
					<div class="vbo-param-container">
						<div class="vbo-param-label"><?php echo JText::_('VBCUSTOMERNOMINATIVE'); ?></div>
						<div class="vbo-param-setting">
							<div class="vbo-customer-info-box">
							<?php
							if (!empty($customer['pic'])) {
								$avatar_caption = rtrim($customer['first_name'] . ' ' . $customer['last_name']);
								?>
								<div class="vbo-customer-info-box-avatar vbo-customer-avatar-small">
									<span>
										<img src="<?php echo strpos($customer['pic'], 'http') === 0 ? $customer['pic'] : VBO_SITE_URI . 'resources/uploads/' . $customer['pic']; ?>" data-caption="<?php echo htmlspecialchars($avatar_caption); ?>" />
									</span>
								</div>
							<?php
							}
							?>
								<div class="vbo-customer-info-box-name">
									<a href="index.php?option=com_vikbooking&amp;task=editcustomer&amp;cid[]=<?php echo $customer['id']; ?>" target="_blank"><?php echo rtrim($customer['first_name'] . ' ' . $customer['last_name']); ?></a>
								</div>
							</div>
							<input type="hidden" name="idcustomer" value="<?php echo $customer['id']; ?>"/>
						</div>
					</div>
					<?php
					if (!empty($customer['company'])) {
						?>
					<div class="vbo-param-container">
						<div class="vbo-param-label"><?php echo JText::_('VBCUSTOMERCOMPANY'); ?></div>
						<div class="vbo-param-setting">
							<span><?php echo $customer['company']; ?></span>
						<?php
						if (!empty($customer['vat'])) {
							?>
							<div><?php echo JText::_('VBCUSTOMERCOMPANYVAT') . ': ' . $customer['vat']; ?></div>
							<?php
						}
						?>
						</div>
					</div>
						<?php
					}
				} else {
					// new manual invoice - select existing customer or create a new one
					?>
					<div class="vbo-param-container">
						<div class="vbo-param-label"><?php echo JText::_('VBOSEARCHEXISTCUST'); ?> <sup>*</sup></div>
						<div class="vbo-param-setting">
							<span class="vbo-eorder-assigncust">
								<input type="text" id="vbo-searchcust" autocomplete="off" value="" placeholder="<?php echo JText::_('VBOSEARCHCUSTBY'); ?>" size="60" />
							</span>
							<span id="vbo-searchcust-loading">
								<i class="vboicn-hour-glass"></i>
							</span>
							<div class="vbo-maninvoice-customerinfo" style="display: block; margin: 10px 0;">
								<input type="hidden" name="idcustomer" id="idcustomer" value="" />
							</div>
							<div id="vbo-searchcust-res"></div>
						</div>
					</div>

					<div class="vbo-param-container">
						<div class="vbo-param-label">&nbsp;</div>
						<div class="vbo-param-setting">
							<span class="vbo-eorder-assignnewcust">
								<a class="vbo-assign-customer" href="index.php?option=com_vikbooking&task=newcustomer&goto=<?php echo base64_encode('index.php?option=com_vikbooking&task=newmaninvoice'); ?>">
									<?php VikBookingIcons::e('user-circle'); ?>
									<span><?php echo JText::_('VBOCREATENEWCUST'); ?></span>
								</a>
							</span>
						</div>
					</div>
					<?php
				}
				?>

				</div>
			</div>
		</fieldset>

		<fieldset class="adminform" id="vbo-maninvoice-cont" style="<?php echo !count($invoice) ? 'display: none;' : ''; ?>">
			<div class="vbo-params-wrap vbo-params-wrap-fullwidth">
				<legend class="adminlegend"><?php echo JText::_('VBOMANINVDETAILS'); ?></legend>
				<div class="vbo-params-container">

					<div class="vbo-param-container">
						<div class="vbo-param-label"><?php echo JText::_('VBINVSTARTNUM'); ?></div>
						<div class="vbo-param-setting">
							<input type="number" min="1" value="<?php echo count($invoice) ? preg_replace("/[^\d]+/", '', $invoice['number']) : VikBooking::getNextInvoiceNumber(); ?>" id="invoice_num" name="invoice_num" />
						</div>
					</div>

					<div class="vbo-param-container">
						<div class="vbo-param-label"><?php echo JText::_('VBINVNUMSUFFIX'); ?></div>
						<div class="vbo-param-setting">
							<input type="text" value="<?php echo VikBooking::getInvoiceNumberSuffix(); ?>" id="invoice_suff" name="invoice_suff" />
						</div>
					</div>

					<div class="vbo-param-container">
						<div class="vbo-param-label"><?php echo JText::_('VBINVCOMPANYINFO'); ?></div>
						<div class="vbo-param-setting">
							<textarea name="company_info" id="company_info"><?php echo htmlspecialchars((string)VikBooking::getInvoiceCompanyInfo()); ?></textarea>
						</div>
					</div>

					<div class="vbo-param-container">
						<div class="vbo-param-label">
							<?php echo JText::_('VBOMANINVSERVICES'); ?>
						</div>
						<div class="vbo-param-setting">
							<button type="button" class="btn btn-secondary" onclick="vboAddInvRow();"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::_('VBOMANINVADDSRV'); ?></button>
						</div>
					</div>

					<div class="vbo-param-container vbo-param-container-full">
						<div class="vbo-param-setting">
							<table class="vbo-maninvoice-tbcont" id="vbo-inv-body">
								<thead>
									<tr>
										<th width="35%" class="vbo-maninvoice-th-srv"><?php echo JText::_('VBOMANINVSERVICENM'); ?></th>
										<th width="20%" class="vbo-maninvoice-th-net"><?php echo JText::_('VBCALCRATESNET'); ?></th>
										<th width="20%" class="vbo-maninvoice-th-tax"><?php echo JText::_('VBCALCRATESTAX'); ?></th>
										<th width="20%" class="vbo-maninvoice-th-tot"><?php echo JText::_('VBCALCRATESTOT'); ?></th>
										<th width="5%" class="vbo-maninvoice-th-del">&nbsp;</th>
									</tr>
								</thead>
								<tbody>
								<?php
								$rawcont = array();
								$rows = array();
								if (count($invoice)) {
									$rawcont = !empty($invoice['rawcont']) ? json_decode($invoice['rawcont'], true) : array();
									$rawcont = is_array($rawcont) ? $rawcont : array();
									$rows = isset($rawcont['rows']) ? $rawcont['rows'] : $rows;
								}
								foreach ($rows as $row) {
									?>
									<tr>
										<td class="vbo-maninvoice-td-srv">
											<input type="text" name="service[]" value="<?php echo $row['service']; ?>" placeholder="<?php echo htmlspecialchars(JText::_('VBOMANINVSERVICENM')); ?>" size="30" />
										</td>
										<td class="vbo-maninvoice-td-net">
											<div class="vbo-maninvoice-inp-wrap">
												<input type="number" name="net[]" value="<?php echo $row['net']; ?>" step="any" />
											</div>
										</td>
										<td class="vbo-maninvoice-td-tax">
											<div class="vbo-maninvoice-seltax"><?php echo str_replace('value="'.(float)$row['aliq'].'"', 'value="'.(float)$row['aliq'].'" selected="selected"', $wiva); ?></div>
											<div class="vbo-maninvoice-inp-wrap">
												<input type="number" name="tax[]" value="<?php echo $row['tax']; ?>" step="any" />
											</div>
										</td>
										<td class="vbo-maninvoice-td-tot">
											<div class="vbo-maninvoice-inp-wrap">
												<input type="number" name="tot[]" value="<?php echo $row['tot']; ?>" step="any" />
											</div>
										</td>
										<td class="vbo-maninvoice-td-del">
											<button type="button" class="btn btn-danger" onclick="vboRemoveInvRow(this);"><?php VikBookingIcons::e('times'); ?></button>
										</td>
									</tr>
									<?php
								}
								?>
								</tbody>
								<tfoot>
								<?php
								$invtotnet = isset($rawcont['totalnet']) ? $rawcont['totalnet'] : 0;
								$invtottax = isset($rawcont['totaltax']) ? $rawcont['totaltax'] : 0;
								$invtottot = isset($rawcont['totaltot']) ? $rawcont['totaltot'] : 0;
								?>
									<tr>
										<td>&nbsp;</td>
										<td>
											<span class="vbo-maninvoice-totals-format vbo-maninvoice-totals-net">
												<span class="vbo-maninvoice-currency"><?php echo $currencysymb; ?></span>
												<input type="number" class="vbo-maninvoice-totals-amount" step="any" name="totalnet" value="<?php echo VikBooking::numberFormat($invtotnet); ?>" />
											</span>
										</td>
										<td>
											<span class="vbo-maninvoice-totals-format vbo-maninvoice-totals-tax">
												<span class="vbo-maninvoice-currency"><?php echo $currencysymb; ?></span>
												<input type="number" class="vbo-maninvoice-totals-amount" step="any" name="totaltax" value="<?php echo VikBooking::numberFormat($invtottax); ?>" />
											</span>
										</td>
										<td>
											<span class="vbo-maninvoice-totals-format vbo-maninvoice-totals-tot">
												<span class="vbo-maninvoice-currency"><?php echo $currencysymb; ?></span>
												<input type="number" class="vbo-maninvoice-totals-amount" step="any" name="totaltot" value="<?php echo VikBooking::numberFormat($invtottot); ?>" />
											</span>
										</td>
										<td>&nbsp;</td>
									</tr>
								</tfoot>
							</table>
						</div>
					</div>

					<div class="vbo-param-container">
						<div class="vbo-param-label"><?php echo JText::_('ORDER_NOTES'); ?></div>
						<div class="vbo-param-setting">
							<textarea name="invoice_notes" id="invoice_notes"><?php echo isset($rawcont['notes']) ? htmlspecialchars($rawcont['notes']) : ''; ?></textarea>
						</div>
					</div>

				</div>
			</div>
		</fieldset>

	</div>

	<input type="hidden" name="task" value="<?php echo count($invoice) ? 'updatemaninvoice' : 'savemaninvoice'; ?>">
	<input type="hidden" name="option" value="com_vikbooking">
	<?php
if (count($invoice)) {
	?>
	<input type="hidden" name="whereup" value="<?php echo $invoice['id']; ?>">
	<?php
}
$pgoto = VikRequest::getString('goto', '', 'request', VIKREQUEST_ALLOWRAW);
if (!empty($pgoto)) {
	?>
	<input type="hidden" name="goto" value="<?php echo $pgoto; ?>">
	<?php
}
?>
	<?php echo JHtml::_('form.token'); ?>
</form>

<script type="text/javascript">
var wiva = '<?php echo $wiva; ?>';
var vbonumdecimals = <?php echo (int)$formatparts[0]; ?>;

function vboRemoveInvRow(elem) {
	jQuery(elem).closest('tr').remove();
}

function vboAddInvRow() {
	var newrow = '<tr>'+
					'<td class="vbo-maninvoice-td-srv">'+
						'<input type="text" name="service[]" value="" placeholder="<?php echo htmlspecialchars(JText::_('VBOMANINVSERVICENM')); ?>" size="30" />'+
					'</td>'+
					'<td class="vbo-maninvoice-td-net">'+
						'<div class="vbo-maninvoice-inp-wrap">'+
							'<input type="number" name="net[]" value="0" step="any" />'+
						'</div>'+
					'</td>'+
					'<td class="vbo-maninvoice-td-tax">'+
						'<div class="vbo-maninvoice-seltax">'+wiva+'</div>'+
						'<div class="vbo-maninvoice-inp-wrap">'+
							'<input type="number" name="tax[]" value="0" step="any" />'+
						'</div>'+
					'</td>'+
					'<td class="vbo-maninvoice-td-tot">'+
						'<div class="vbo-maninvoice-inp-wrap">'+
							'<input type="number" name="tot[]" value="0" step="any" />'+
						'</div>'+
					'</td>'+
					'<td class="vbo-maninvoice-td-del">'+
						'<button type="button" class="btn btn-danger" onclick="vboRemoveInvRow(this);"><?php VikBookingIcons::e('times'); ?></button>'+
					'</td>'+
				'</tr>';
	jQuery('#vbo-inv-body').find('tbody').append(newrow);
}

function vboUpdateTotals(type) {
	var totamount = 0;
	var inputs = jQuery('input[name="'+type+'[]"]');
	if (!inputs || !inputs.length) {
		return;
	}
	inputs.each(function(k, v) {
		var nowval = jQuery(v).val();
		if (!nowval || !nowval.length) {
			return true;
		}
		nowval = parseFloat(nowval);
		if (isNaN(nowval)) {
			return true;
		}
		totamount += nowval;
	});
	jQuery('.vbo-maninvoice-totals-'+type).find('.vbo-maninvoice-totals-amount').val(totamount.toFixed(vbonumdecimals));
}

jQuery(function() {

	jQuery(document.body).on('change', 'input[name="net[]"]', function() {
		vboUpdateTotals('net');
	});

	jQuery(document.body).on('change', 'input[name="tax[]"]', function() {
		vboUpdateTotals('tax');
	});

	jQuery(document.body).on('change', 'input[name="tot[]"]', function() {
		vboUpdateTotals('tot');
	});

	jQuery(document.body).on('change', 'select[name="aliq[]"]', function() {
		var aliq = 0;
		var taxcap = 0;
		var optaliq = jQuery(this).find('option:selected').attr('data-aliq');
		if (optaliq && optaliq.length) {
			aliq = parseFloat(optaliq);
		}
		var opttaxcap = jQuery(this).find('option:selected').attr('data-taxcap');
		if (opttaxcap && opttaxcap.length) {
			taxcap = parseFloat(opttaxcap);
		}
		if (!(aliq > 0)) {
			return;
		}
		// check if net field is not empty to update tax and total for this row
		var nownet = jQuery(this).closest('tr').find('input[name="net[]"]').val();
		if (nownet && nownet.length) {
			nownet = parseFloat(nownet);
			if (!isNaN(nownet) && nownet > 0) {
				var newtax = nownet * aliq / 100;
				if (taxcap > 0 && newtax > taxcap) {
					newtax = taxcap;
				}
				jQuery(this).closest('tr').find('input[name="tax[]"]').val(newtax.toFixed(vbonumdecimals)).trigger('change');
				var newtot = nownet + newtax;
				jQuery(this).closest('tr').find('input[name="tot[]"]').val(newtot.toFixed(vbonumdecimals)).trigger('change');
				return true;
			}
		}
		// check if tot field is not empty to update net and tax for this row
		var nowtot = jQuery(this).closest('tr').find('input[name="tot[]"]').val();
		if (nowtot && nowtot.length) {
			nowtot = parseFloat(nowtot);
			if (!isNaN(nowtot) && nowtot > 0) {
				var divider = (100 + aliq) / 100;
				var newnet = nowtot / divider;
				if (taxcap > 0 && (nowtot - newnet) > taxcap) {
					newnet = nowtot - taxcap;
				}
				jQuery(this).closest('tr').find('input[name="net[]"]').val(newnet.toFixed(vbonumdecimals)).trigger('change');
				var newtax = nowtot - newnet;
				jQuery(this).closest('tr').find('input[name="tax[]"]').val(newtax.toFixed(vbonumdecimals)).trigger('change');
				return true;
			}
		}
	});

	// Search customer - Start
	var vbocustsdelay = (function() {
		var timer = 0;
		return function(callback, ms) {
			clearTimeout(timer);
			timer = setTimeout(callback, ms);
		};
	})();

	function vboCustomerSearch(words) {
		jQuery("#vbo-searchcust-res").hide().html("");
		jQuery("#vbo-searchcust-loading").show();
		var jqxhr = jQuery.ajax({
			type: "POST",
			url: "<?php echo VikBooking::ajaxUrl('index.php?option=com_vikbooking&task=searchcustomer'); ?>",
			data: {
				kw: words,
				tmpl: "component"
			}
		}).done(function(cont) {
			if (cont) {
				var obj_res = typeof cont === 'string' ? JSON.parse(cont) : cont;
				customers_search_vals = obj_res[0];
				jQuery("#vbo-searchcust-res").html(obj_res[1]);
			} else {
				customers_search_vals = "";
				jQuery("#vbo-searchcust-res").html("----");
			}
			jQuery("#vbo-searchcust-res").show();
			jQuery("#vbo-searchcust-loading").hide();
		}).fail(function() {
			jQuery("#vbo-searchcust-loading").hide();
			alert("Error Searching.");
		});
	}

	jQuery("#vbo-searchcust").keyup(function(event) {
		vbocustsdelay(function() {
			var keywords = jQuery("#vbo-searchcust").val();
			var chars = keywords.length;
			if (chars > 1) {
				if ((event.which > 96 && event.which < 123) || (event.which > 64 && event.which < 91) || event.which == 13) {
					vboCustomerSearch(keywords);
				}
			} else {
				if (jQuery("#vbo-searchcust-res").is(":visible")) {
					jQuery("#vbo-searchcust-res").hide();
				}
			}
		}, 600);
	});

	jQuery("body").on("click", ".vbo-custsearchres-entry", function() {
		var custid = jQuery(this).attr("data-custid");
		var custname = jQuery(this).attr("data-firstname");
		var custlname = jQuery(this).attr("data-lastname");
		jQuery('#vbo-searchcust').val(custname + ' ' + custlname);
		jQuery('#idcustomer').val(custid);
		jQuery('#vbo-searchcust-res').hide().html('');
		jQuery('#vbo-maninvoice-cont').fadeIn();
	});
	// Search customer - End

	// zoom-able avatar
	jQuery('.vbo-customer-info-box-avatar').each(function() {
		var img = jQuery(this).find('img');
		if (!img.length) {
			return;
		}
		// register click listener
		img.on('click', function(e) {
			// stop events propagation
			e.preventDefault();
			e.stopPropagation();

			// check for caption
			var caption = jQuery(this).attr('data-caption');

			// modal content
			var zoom_img_html = jQuery(this).clone();

			// display modal
			VBOCore.displayModal({
				suffix: 'zoom-image',
				title: caption,
				body: zoom_img_html,
			});
		});
	});

<?php
if (!count($invoice)) {
	// when creating a new manual invoice we automatically add a row when the page loads
	echo 'vboAddInvRow();' . "\n";
}
?>
});
</script>
