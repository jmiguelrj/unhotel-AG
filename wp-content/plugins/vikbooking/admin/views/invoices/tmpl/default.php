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
$archive = $this->archive;
$lim0 = $this->lim0;
$navbut = $this->navbut;

$pmonyear = VikRequest::getString('monyear', '', 'request');
$pfilterinvoices = VikRequest::getString('filterinvoices', '', 'request');
$pshow = VikRequest::getInt('show', 0, 'request');
$pinvstatus = VikRequest::getInt('invstatus', 0, 'request');
$nowdf = VikBooking::getDateFormat(true);
if ($nowdf == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($nowdf == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}
$datesep = VikBooking::getDateSeparator(true);
$arrmonths = array(
	1 => JText::_('VBMONTHONE'),
	2 => JText::_('VBMONTHTWO'),
	3 => JText::_('VBMONTHTHREE'),
	4 => JText::_('VBMONTHFOUR'),
	5 => JText::_('VBMONTHFIVE'),
	6 => JText::_('VBMONTHSIX'),
	7 => JText::_('VBMONTHSEVEN'),
	8 => JText::_('VBMONTHEIGHT'),
	9 => JText::_('VBMONTHNINE'),
	10 => JText::_('VBMONTHTEN'),
	11 => JText::_('VBMONTHELEVEN'),
	12 => JText::_('VBMONTHTWELVE')
);
$currencysymb = VikBooking::getCurrencySymb();
?>
<div class="vbo-timeline-container">
	<ul id="vbo-timeline">
		<?php
		foreach ($archive as $monyear => $totinvoices) {
			$monyear_parts = explode('_', $monyear);
			?>
		<li data-month="<?php echo $monyear; ?>">
			<input type="radio" name="timeline" class="vbo-timeline-radio" id="vbo-timeline-dot<?php echo $monyear; ?>" <?php echo $monyear == $pmonyear ? 'checked="checked"' : ''; ?>/>
			<div class="vbo-timeline-relative">
				<label class="vbo-timeline-label" for="vbo-timeline-dot<?php echo $monyear; ?>"><?php echo $arrmonths[(int)$monyear_parts[1]].' '.$monyear_parts[0]; ?></label>
				<span class="vbo-timeline-date"><i class="vboicn-file-text2"></i><?php echo $totinvoices; ?></span>
				<span class="vbo-timeline-circle" onclick="Javascript: jQuery('#vbo-timeline-dot<?php echo $monyear; ?>').trigger('click');"></span>
			</div>
			<div class="vbo-timeline-content">
				<p><span class="label label-info"><?php echo JText::_('VBTOTINVOICES').': <span class="badge">'.$totinvoices.'</span>'; ?></span> <button type="button" class="btn<?php echo ($monyear == $pmonyear ? ' btn-danger' : ''); ?>" onclick="document.location.href='index.php?option=com_vikbooking&amp;task=invoices&amp;monyear=<?php echo ($monyear == $pmonyear ? '0' : $monyear); ?>';"><i class="vboicn-<?php echo ($monyear == $pmonyear ? 'cross' : 'checkmark'); ?>"></i><?php echo JText::_(($monyear == $pmonyear ? 'VBOINVREMOVEFILTER' : 'VBOINVAPPLYFILTER')); ?></button></p>
			</div>
		</li>
			<?php
		}
		?>
	</ul>
</div>

<script>
jQuery(function() {
	jQuery('.vbo-timeline-container').css('min-height', (jQuery('.vbo-timeline-container').outerHeight() + 5));

	jQuery('.vbo-invoices-inv-back-commands button').click(function(e) {
		e.stopPropagation();
		e.preventDefault();
	});

	jQuery('.vbo-invoices-inv-container').click(function() {
		var rowkey = jQuery(this).attr("data-rowkey");
		if (jQuery(this).hasClass('vbo-invoice-active')) {
			jQuery("#cb"+rowkey).prop("checked", false);
			document.adminForm.boxchecked.value--;
			jQuery(this).removeClass('vbo-invoice-active');
			try {
				Joomla.isChecked(false);
			} catch(e) {

			}
		} else {
			jQuery("#cb"+rowkey).prop("checked", true);
			jQuery(this).addClass('vbo-invoice-active');
			document.adminForm.boxchecked.value++;
			try {
				Joomla.isChecked(true);
			} catch(e) {
				
			}
		}
	});

	jQuery('#vbo-selall').click(function() {
		jQuery('.vbo-invoices-inv-container').each(function() {
			var rowkey = jQuery(this).attr("data-rowkey");
			if (!jQuery(this).hasClass('vbo-invoice-active')) {
				jQuery(this).addClass('vbo-invoice-active');
			}
			if (jQuery("#cb"+rowkey).prop("checked") === false) {
				jQuery("#cb"+rowkey).prop("checked", true);
				document.adminForm.boxchecked.value++;
			}
		});
		try {
			Joomla.isChecked(true);
		} catch(e) {
			
		}
	});

	jQuery('#vbo-deselall').click(function() {
		jQuery('.vbo-invoices-inv-container').each(function() {
			var rowkey = jQuery(this).attr("data-rowkey");
			if (jQuery(this).hasClass('vbo-invoice-active')) {
				jQuery(this).removeClass('vbo-invoice-active');
			}
			if (jQuery("#cb"+rowkey).prop("checked") === true) {
				jQuery("#cb"+rowkey).prop("checked", false);
				document.adminForm.boxchecked.value--;
			}
		});
		try {
			Joomla.isChecked(false);
		} catch(e) {
			
		}
	});
<?php
if ($pshow > 0) {
	?>
	if (jQuery(".vbo-invoices-inv-container[data-vboid='<?php echo $pshow; ?>']").length) {
		jQuery(".vbo-invoices-inv-container[data-vboid='<?php echo $pshow; ?>']").trigger('click');
	}
	<?php
}
?>
});
</script>

<form action="index.php?option=com_vikbooking&amp;task=invoices" method="post" name="invoicesform">
	<div id="filter-bar" class="btn-toolbar vbo-btn-toolbar" style="width: 100%; display: inline-block;">
		<div class="btn-group pull-left">
			<input type="text" name="filterinvoices" id="filterinvoices" value="<?php echo $pfilterinvoices; ?>" size="43" placeholder="<?php echo JText::_( 'VBOINVNUM' ).', '.JText::_( 'VBCUSTOMERFIRSTNAME' ).', '.JText::_( 'VBCUSTOMERLASTNAME' ).', '.JText::_( 'VBCUSTOMEREMAIL' ); ?>"/>
		</div>
		<div class="btn-group pull-left">
			<button type="button" class="btn btn-primary" onclick="document.invoicesform.submit();"><i class="icon-search"></i></button>
			<button type="button" class="btn btn-secondary" onclick="document.getElementById('filterinvoices').value='';document.getElementById('invstatus').value='';document.invoicesform.submit();"><i class="icon-remove"></i></button>
		</div>
		<div class="btn-group pull-left">
			<select name="invstatus" id="invstatus" onchange="document.invoicesform.submit();">
				<option value=""><?php echo JText::_('VBSTATUS'); ?></option>
				<option value="1"<?php echo $pinvstatus === 1 ? ' selected="selected"' : ''; ?>>- <?php echo JText::_('VBO_STATUS_PAID'); ?></option>
				<option value="2"<?php echo $pinvstatus === 2 ? ' selected="selected"' : ''; ?>>- <?php echo JText::_('VBO_STATUS_UNPAID'); ?></option>
			</select>
		</div>
	<?php
	if (!empty($rows)) {
	?>
		<div class="btn-group pull-right">
			<button type="button" class="btn" id="vbo-deselall"><?php echo JText::_('VBINVDESELECTALL'); ?></button>
		</div>
		<div class="btn-group pull-right">
			<button type="button" class="btn" id="vbo-selall"><?php echo JText::_('VBINVSELECTALL'); ?></button>
		</div>
	<?php
	}
	?>
	</div>
	<input type="hidden" name="task" value="invoices" />
	<input type="hidden" name="option" value="com_vikbooking" />
</form>
<?php
if (empty($rows)) {
	?>
<p class="warn"><?php echo JText::_('VBNOINVOICESFOUND'); ?></p>
<form action="index.php?option=com_vikbooking" method="post" name="adminForm" id="adminForm">
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikbooking" />
</form>
	<?php
} else {
	?>
<form action="index.php?option=com_vikbooking" method="post" name="adminForm" id="adminForm">
	<div class="vbo-invoices-wrapper">
	<?php
	foreach ($rows as $k => $row) {
		$row['country'] = empty($row['country']) && !empty($row['customer_country']) ? $row['customer_country'] : $row['country'];
		if ($row['idorder'] < 0) {
			// manual invoice
			$saystaus = '<span class="label label-success">'.JText::_('VBOMANUALINVOICE').'</span>';
		} elseif ($row['status'] == "confirmed") {
			$saystaus = '<span class="label label-success">'.JText::_('VBCONFIRMED').'</span>';
		} elseif ($row['status'] == "standby") {
			$saystaus = '<span class="label label-warning">'.JText::_('VBSTANDBY').'</span>';
		} else {
			$saystaus = '<span class="label label-error">'.JText::_('VBCANCELLED').'</span>';
		}
		?>
		<div class="vbo-invoices-inv-container" data-rowkey="<?php echo $k; ?>" data-vboid="<?php echo $row['idorder']; ?>">
			<div class="vbo-invoices-inv-inner">
				<div class="vbo-invoices-inv-front vbo-invoices-inv-face">
					<div class="vbo-invoices-inv-frontleft">
					<?php
					if (!is_null($row['total'])) {
						// display paid/unpaid status only for booking invoices (ignore manual invoices)
						?>
						<div class="vbo-invoices-inv-totals">
							<div class="vbo-invoices-inv-total">
								<?php echo VikBooking::formatCurrencyNumber(VikBooking::numberFormat($row['total']), $currencysymb, ['<span>%s</span>', '<span>%s</span>']); ?>
							</div>
							<div class="vbo-invoices-inv-totpaid">
							<?php
							if ($row['total'] > 0 && $row['totpaid'] >= $row['total']) {
								?>
								<span class="badge badge-success"><?php echo JText::_('VBO_STATUS_PAID'); ?></span>
								<?php
							} else {
								echo VikBooking::formatCurrencyNumber(VikBooking::numberFormat($row['totpaid']), $currencysymb, ['<span>%s</span>', '<span>%s</span>']);
							}
							?>
							</div>
						</div>
						<?php
					}
					?>
					</div>
					<div class="vbo-invoices-inv-frontright">
						<div class="vbo-invoices-inv-entry">
							<span class="vbo-invoices-inv-entry-lbl"><?php echo JText::_('VBOINVNUM'); ?></span>
							<span class="vbo-invoices-inv-entry-val"><?php echo $row['number']; ?></span>
						</div>
						<div class="vbo-invoices-inv-entry">
							<span class="vbo-invoices-inv-entry-lbl"><?php echo JText::_('VBOINVDATE'); ?></span>
							<span class="vbo-invoices-inv-entry-val"><?php echo date(str_replace("/", $datesep, $df), $row['for_date']); ?></span>
						</div>
						<div class="vbo-invoices-inv-entry">
							<span class="vbo-invoices-inv-entry-lbl"><?php echo JText::_('VBOINVCREATIONDATE'); ?></span>
							<span class="vbo-invoices-inv-entry-val"><?php echo date(str_replace("/", $datesep, $df), $row['created_on']); ?></span>
						</div>
						<div class="vbo-invoices-inv-entry">
						<?php
						if ((int)$row['idorder'] > 0) {
							// regular invoice for a real booking
							?>
							<span class="vbo-invoices-inv-entry-lbl"><?php echo JText::_('VBOINVBOOKINGID'); ?></span>
							<span class="vbo-invoices-inv-entry-val"><?php echo $row['idorder']; ?></span>
							<?php
						} else {
							// manual invoice
							?>
							<span class="vbo-invoices-inv-entry-lbl"><?php echo JText::_('VBOMANUALINVOICE'); ?></span>
							<?php
						}
						?>
						</div>
					<?php
					if (strlen($row['customer_name']) > 2) {
						?>
						<div class="vbo-invoices-inv-entry vbo-invoices-inv-entry-custname">
							<span class="vbo-invoices-inv-entry-val"><strong><?php echo $row['customer_name']; ?></strong></span>
						</div>
						<?php
					}
					$country_flag = '';
					if (file_exists(VBO_ADMIN_PATH.DS.'resources'.DS.'countries'.DS.$row['country'].'.png')) {
						$country_flag = '<img src="'.VBO_ADMIN_URI.'resources/countries/'.$row['country'].'.png'.'" title="'.$row['country'].'" class="vbo-country-flag vbo-country-flag-left"/>';
					}
					if (!empty($country_flag) || !empty($row['country_name'])) {
						?>
						<div class="vbo-invoices-inv-entry">
						<?php
						if (!empty($country_flag)) {
							?>
							<span class="vbo-invoices-inv-entry-lbl"><?php echo $country_flag; ?></span>
							<?php
						}
						if (!empty($row['country_name'])) {
							?>
							<span class="vbo-invoices-inv-entry-val"><?php echo $row['country_name']; ?></span>
							<?php
						}
						?>
						</div>
						<?php
					}
					?>
					</div>
				</div>
				<div class="vbo-invoices-inv-back vbo-invoices-inv-face">
					<div class="vbo-invoices-inv-ckbox">
						<input type="checkbox" id="cb<?php echo $k;?>" name="cid[]" value="<?php echo $row['id']; ?>">
					</div>
					<div class="vbo-invoices-inv-back-commands">
						<button type="button" class="btn vbo-light-btn" onclick="document.location.href='index.php?option=com_vikbooking&amp;task=resendinvoices&amp;cid[]=<?php echo $row['id']; ?>';"><?php VikBookingIcons::e('envelope-o'); ?> <?php echo JText::_(($row['emailed'] > 0 && !empty($row['emailed_to']) ? 'VBOINVREEMAIL' : 'VBOINVEMAILNOW')); ?></button>
						<button type="button" class="btn vbo-light-btn" onclick="window.open('<?php echo VBO_SITE_URI; ?>helpers/invoices/generated/<?php echo $row['file_name']; ?>', '_blank');"><?php VikBookingIcons::e('file-text-o'); ?> <?php echo JText::_('VBOINVOPEN'); ?></button>
						<button type="button" class="btn vbo-light-btn" onclick="document.location.href='index.php?option=com_vikbooking&amp;task=downloadinvoices&amp;cid[]=<?php echo $row['id']; ?>';"><?php VikBookingIcons::e('download'); ?> <?php echo JText::_('VBOINVDOWNLOAD'); ?></button>
					<?php
					if ($row['idorder'] < 0) {
						// manual invoice
						?>
						<button type="button" class="btn vbo-light-btn" onclick="window.open('index.php?option=com_vikbooking&amp;task=editmaninvoice&amp;cid[]=<?php echo $row['id']; ?>', '_blank');"><?php VikBookingIcons::e('external-link'); ?> <?php echo JText::_('VBOMANINVDETAILS'); ?></button>
						<?php
					} else {
						// regular invoice for a real booking
						?>
						<button type="button" class="btn vbo-light-btn" onclick="window.open('index.php?option=com_vikbooking&amp;task=editorder&amp;cid[]=<?php echo $row['idorder']; ?>', '_blank');"><?php VikBookingIcons::e('external-link'); ?> <?php echo JText::_('VBOINVBOOKDETAILS'); ?></button>
						<?php
					}
					?>
					</div>
					<div class="vbo-invoices-inv-back-entries">
						<div class="vbo-invoices-inv-entry">
							<span class="vbo-invoices-inv-entry-lbl"><?php echo JText::_('VBOINVNUM'); ?></span>
							<span class="vbo-invoices-inv-entry-val"><?php echo $row['number']; ?></span>
						</div>
						<div class="vbo-invoices-inv-entry">
						<?php
						if ($row['idorder'] < 0) {
							// manual invoice
							?>
							<span class="vbo-invoices-inv-entry-val"><?php echo $saystaus; ?></span>
							<?php
						} else {
							// regular invoice for a real booking
							?>
							<span class="vbo-invoices-inv-entry-lbl"><?php echo JText::_('VBOINVBOOKINGID'); ?></span>
							<span class="vbo-invoices-inv-entry-val"><?php echo $row['idorder'].' '.$saystaus; ?></span>
							<?php
						}
						?>
						</div>
						<div class="vbo-invoices-inv-entry">
							<span class="vbo-invoices-inv-entry-lbl"><?php VikBookingIcons::e('envelope-o'); ?> <?php echo JText::_('VBOINVEMAILED'); ?></span>
							<span class="vbo-invoices-inv-entry-val"><?php echo $row['emailed'] > 0 && !empty($row['emailed_to']) ? '<small>'.$row['emailed_to'].'</small>' : '----'; ?></span>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
	?>
	</div>
	<input type="hidden" name="option" value="com_vikbooking" />
	<input type="hidden" name="task" value="invoices" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHtml::_('form.token'); ?>
	<?php echo $navbut; ?>
	<?php
	if (!empty($pfilterinvoices)) {
		?>
		<input type="hidden" name="filterinvoices" value="<?php echo $pfilterinvoices; ?>" />
		<?php
	}
	if (!empty($pinvstatus)) {
		?>
		<input type="hidden" name="invstatus" value="<?php echo $pinvstatus; ?>" />
		<?php
	}
	?>
</form>
	<?php
}