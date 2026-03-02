<?php
/**
 * @package     VikBooking
 * @subpackage  com_vikbooking
 * Template: Minimal & Modern Invoice with Unhotel Colors
 */

// ------------------------------------------------
// Adjust PDF layout parameters if needed
// (Margins, orientation, etc.)
// ------------------------------------------------
defined('VBO_INVOICE_PDF_PAGE_ORIENTATION') OR define('VBO_INVOICE_PDF_PAGE_ORIENTATION', 'P');
defined('VBO_INVOICE_PDF_UNIT') OR define('VBO_INVOICE_PDF_UNIT', 'mm');
defined('VBO_INVOICE_PDF_PAGE_FORMAT') OR define('VBO_INVOICE_PDF_PAGE_FORMAT', 'A4');
defined('VBO_INVOICE_PDF_MARGIN_LEFT') OR define('VBO_INVOICE_PDF_MARGIN_LEFT', 10);
defined('VBO_INVOICE_PDF_MARGIN_TOP') OR define('VBO_INVOICE_PDF_MARGIN_TOP', 10);
defined('VBO_INVOICE_PDF_MARGIN_RIGHT') OR define('VBO_INVOICE_PDF_MARGIN_RIGHT', 10);
defined('VBO_INVOICE_PDF_MARGIN_HEADER') OR define('VBO_INVOICE_PDF_MARGIN_HEADER', 1);
defined('VBO_INVOICE_PDF_MARGIN_FOOTER') OR define('VBO_INVOICE_PDF_MARGIN_FOOTER', 5);
defined('VBO_INVOICE_PDF_MARGIN_BOTTOM') OR define('VBO_INVOICE_PDF_MARGIN_BOTTOM', 5);
defined('VBO_INVOICE_PDF_IMAGE_SCALE_RATIO') OR define('VBO_INVOICE_PDF_IMAGE_SCALE_RATIO', 1.25);

$invoice_params = array(
    'show_header' => 0,
    'header_data' => array(),
    'show_footer' => 0,
    'pdf_page_orientation' => 'VBO_INVOICE_PDF_PAGE_ORIENTATION',
    'pdf_unit' => 'VBO_INVOICE_PDF_UNIT',
    'pdf_page_format' => 'VBO_INVOICE_PDF_PAGE_FORMAT',
    'pdf_margin_left' => 'VBO_INVOICE_PDF_MARGIN_LEFT',
    'pdf_margin_top' => 'VBO_INVOICE_PDF_MARGIN_TOP',
    'pdf_margin_right' => 'VBO_INVOICE_PDF_MARGIN_RIGHT',
    'pdf_margin_header' => 'VBO_INVOICE_PDF_MARGIN_HEADER',
    'pdf_margin_footer' => 'VBO_INVOICE_PDF_MARGIN_FOOTER',
    'pdf_margin_bottom' => 'VBO_INVOICE_PDF_MARGIN_BOTTOM',
    'pdf_image_scale_ratio' => 'VBO_INVOICE_PDF_IMAGE_SCALE_RATIO',
    'header_font_size' => '10',
    'body_font_size' => '10',
    'footer_font_size' => '8',
    'show_lines_taxrate_col' => 0,
);
defined('_VIKBOOKING_INVOICE_PARAMS') OR define('_VIKBOOKING_INVOICE_PARAMS', '1');
?>

<!-- Main Container -->
<div style="font-family: Arial, sans-serif; color: #1F2020; font-size: 10px; margin: 0; padding: 0; line-height: 1.4;">

  <!-- LOGO & COMPANY INFO -->
  <table width="100%" border="0" cellspacing="0" cellpadding="5" style="margin-bottom: 20px;">
    <tr>
      <td align="left" valign="middle">
        <!-- Logo max-width: 150px; Adjust as desired -->
        <div style="display:inline-block; vertical-align:middle;">
          <span style="display:block; width:50px; max-width:50px;">{company_logo}</span>
        </div>
        <div style="display:inline-block; vertical-align:middle; margin-left: 15px;">
          <strong style="font-size: 12px;">{company_info}</strong>
        </div>
      </td>
      <td align="right" valign="middle">
        <div style="text-align: right; font-size: 11px;">
          <?php echo JText::_('VBOINVNUM'); ?> 
          <strong><span>{invoice_number}</span><span>{invoice_suffix}</span></strong><br/>
          <?php echo JText::_('VBOINVDATE'); ?> 
          <strong><span>{invoice_date}</span></strong>
        </div>
      </td>
    </tr>
  </table>

  <!-- PRODUCTS TABLE -->
  <table width="100%" border="0" cellspacing="0" cellpadding="5" style="border-collapse:collapse; margin-bottom:20px;">
    <tr style="background-color: #FA4676; color: #FFFFFF;">
      <th align="left" width="40%" style="font-size:11px;"><?php echo JText::_('VBOINVCOLDESCR'); ?></th>
      <th align="right" width="20%" style="font-size:11px;"><?php echo JText::_('VBOINVCOLNETPRICE'); ?></th>
      <th align="right" width="20%" style="font-size:11px;"><?php echo JText::_('VBOINVCOLTAX'); ?></th>
      <th align="right" width="20%" style="font-size:11px;"><?php echo JText::_('VBOINVCOLPRICE'); ?></th>
    </tr>
    {invoice_products_descriptions}
  </table>

  <!-- TAX SUMMARY TABLE (if any) -->
  <table width="100%" border="0" cellspacing="0" cellpadding="5" style="border-collapse:collapse; margin-bottom:20px;">
    <tr style="background-color: #FA4676; color: #FFFFFF;">
      <th align="left" width="40%" style="font-size:11px;"><?php echo JText::_('VBO_INV_TAX_SUMMARY'); ?></th>
      <th align="right" width="30%" style="font-size:11px;"><?php echo JText::_('VBO_INV_TAX_ALIQUOTE'); ?></th>
      <th align="right" width="30%" style="font-size:11px;"><?php echo JText::_('VBOINVCOLTOTAL'); ?></th>
    </tr>
    {invoice_tax_summary}
  </table>

  <!-- CUSTOMER INFO, BOOKING DETAILS, AND TOTALS -->
  <table width="100%" border="0" cellspacing="0" cellpadding="5" style="margin-bottom:20px;">
    <tr>
      <td width="40%" valign="top" style="border:1px solid #FA4676;">
        <strong><?php echo JText::_('VBOINVCOLCUSTINFO'); ?></strong><br/>
        {customer_info}
      </td>
      <td width="30%" valign="top" style="border:1px solid #FA4676;">
        <strong><?php echo JText::_('VBOINVCOLBOOKINGDETS'); ?></strong><br/>
        <?php echo JText::_('VBOINVCHECKIN'); ?>: <span>{checkin_date}</span><br/>
        <?php echo JText::_('VBOINVCHECKOUT'); ?>: <span>{checkout_date}</span><br/>
        <?php echo JText::_('VBOINVTOTGUESTS'); ?>: <span>{tot_guests}</span>
      </td>
      <td width="30%" valign="top" style="border:1px solid #FA4676;">
        <div style="text-align:right;">
          <div><strong><?php echo JText::_('VBOINVCOLTOTAL'); ?>:</strong> <span>{invoice_totalnet}</span></div>
          <div><strong><?php echo JText::_('VBOINVCOLTAX'); ?>:</strong> <span>{invoice_totaltax}</span></div>
          <div style="margin-top:10px; font-weight:bold; font-size:12px;">
            <?php echo JText::_('VBOINVCOLGRANDTOTAL'); ?>: <span>{invoice_grandtotal}</span>
          </div>
        </div>
      </td>
    </tr>
  </table>

  <!-- ADDITIONAL NOTES -->
  <div style="margin-top:10px; font-size:10px;">
    {inv_notes}
  </div>

</div>