<?php
/** 
 * @package     VikBooking - Libraries
 * @subpackage  html.license
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2022 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

$view = $displayData['view'];

$lookup = [
	'coupons' => [
		'title' => JText::_('VBMENUCOUPONS'),
		'desc'  => JText::_('VBOFREECOUPONSDESCR'),
	],
	'crons' => [
		'title' => JText::_('VBMENUCRONS'),
		'desc'  => JText::_('VBOFREECRONSDESCR'),
	],
	'customers' => [
		'title' => JText::_('VBMENUCUSTOMERS'),
		'desc'  => JText::_('VBOFREECUSTOMERSDESCR'),
	],
	'einvoicing' => [
		'title' => JText::_('VBMENUEINVOICING'),
		'desc'  => JText::_('VBOFREEEINVDESCR'),
	],
	'invoices' => [
		'title' => JText::_('VBMENUINVOICES'),
		'desc'  => JText::_('VBOFREEINVOICESDESCR'),
	],
	'operators' => [
		'title' => JText::_('VBMENUOPERATORS'),
		'desc'  => JText::_('VBOFREEOPERATORSDESCR'),
	],
	'optionals' => [
		'title' => JText::_('VBMENUTENFIVE'),
		'desc'  => JText::_('VBOFREEOPTIONSDESCR'),
	],
	'packages' => [
		'title' => JText::_('VBMENUPACKAGES'),
		'desc'  => JText::_('VBOFREEPACKAGESDESCR'),
	],
	'payments' => [
		'title' => JText::_('VBMENUTENEIGHT'),
		'desc'  => JText::_('VBOFREEPAYMENTSDESCR'),
	],
	'pmsreports' => [
		'title' => JText::_('VBMENUPMSREPORTS'),
		'desc'  => JText::_('VBOFREEREPORTSDESCR'),
	],
	'restrictions' => [
		'title' => JText::_('VBMENURESTRICTIONS'),
		'desc'  => JText::_('VBOFREERESTRSDESCR'),
	],
	'seasons' => [
		'title' => JText::_('VBMENUTENSEVEN'),
		'desc'  => JText::_('VBOFREESEASONSDESCR'),
	],
	'stats' => [
		'title' => JText::_('VBMENUSTATS'),
		'desc'  => JText::_('VBOFREESTATSDESCR'),
	],
];

if (!isset($lookup[$view]))
{
	return;
}

// set up toolbar title
JToolbarHelper::title('VikBooking - ' . $lookup[$view]['title']);

if (empty($lookup[$view]['image']))
{
	// use the default logo image
	$lookup[$view]['image'] = 'vikwp_free_logo.png';
}

?>

<div class="vbo-free-nonavail-wrap">

	<div class="vbo-free-nonavail-inner">

		<div class="vbo-free-nonavail-logo">
			<img src="<?php echo VBO_SITE_URI . 'resources/' . $lookup[$view]['image']; ?>" />
		</div>

		<div class="vbo-free-nonavail-expl">
			<h3><?php echo $lookup[$view]['title']; ?></h3>

			<p class="vbo-free-nonavail-descr"><?php echo $lookup[$view]['desc']; ?></p>
			
			<p class="vbo-free-nonavail-footer-descr">
				<a href="admin.php?option=com_vikbooking&amp;view=gotopro" class="btn vbo-free-nonavail-gopro">
					<?php VikBookingIcons::e('rocket'); ?> <span><?php echo JText::_('VBOGOTOPROBTN'); ?></span>
				</a>
			</p>
		</div>

	</div>

</div>