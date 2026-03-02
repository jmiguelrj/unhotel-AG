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
$orderby = $this->orderby;
$ordersort = $this->ordersort;

$pfiltercustomer = VikRequest::getString('filtercustomer', '', 'request');
?>
<div class="vbo-list-form-filters vbo-btn-toolbar">
	<form action="index.php?option=com_vikbooking&amp;task=operators" method="post" name="operatorsform">
		<div style="width: 100%; display: inline-block;" class="btn-toolbar" id="filter-bar">
			<div class="btn-group pull-left input-append">
				<input type="text" name="filtercustomer" id="filtercustomer" value="<?php echo $pfiltercustomer; ?>" size="40" placeholder="<?php echo JText::_( 'VBCUSTOMERFIRSTNAME' ).', '.JText::_( 'VBCUSTOMERLASTNAME' ).', '.JText::_( 'VBCUSTOMEREMAIL' ).', '.JText::_( 'VBOCODEOPERATOR' ); ?>"/>
				<button type="button" class="btn btn-secondary" onclick="document.operatorsform.submit();"><i class="icon-search"></i></button>
			</div>
			<div class="btn-group pull-left">
				<button type="button" class="btn btn-secondary" onclick="document.getElementById('filtercustomer').value='';document.operatorsform.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
			</div>
			
		</div>
		<input type="hidden" name="task" value="operators" />
		<input type="hidden" name="option" value="com_vikbooking" />
	</form>
</div>
<?php
if (empty($rows)) {
	?>
	<p class="warn"><?php echo JText::_('VBNOOPERATORS'); ?></p>
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
			<th class="title left" width="75">
				<a href="index.php?option=com_vikbooking&amp;task=operators&amp;vborderby=id&amp;vbordersort=<?php echo ($orderby == "id" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "id" && $ordersort == "ASC" ? "vbo-list-activesort" : ($orderby == "id" ? "vbo-list-activesort" : "")); ?>">
					ID<?php echo ($orderby == "id" && $ordersort == "ASC" ? '<i class="'.VikBookingIcons::i('sort-asc').'"></i>' : ($orderby == "id" ? '<i class="'.VikBookingIcons::i('sort-desc').'"></i>' : '<i class="'.VikBookingIcons::i('sort').'"></i>')); ?>
				</a>
			</th>
			<th class="title left" width="75">
				<a href="index.php?option=com_vikbooking&amp;task=operators&amp;vborderby=first_name&amp;vbordersort=<?php echo ($orderby == "first_name" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "first_name" && $ordersort == "ASC" ? "vbo-list-activesort" : ($orderby == "first_name" ? "vbo-list-activesort" : "")); ?>">
					<?php echo JText::_('VBCUSTOMERFIRSTNAME').($orderby == "first_name" && $ordersort == "ASC" ? '<i class="'.VikBookingIcons::i('sort-asc').'"></i>' : ($orderby == "first_name" ? '<i class="'.VikBookingIcons::i('sort-desc').'"></i>' : '<i class="'.VikBookingIcons::i('sort').'"></i>')); ?>
				</a>
			</th>
			<th class="title left" width="75">
				<a href="index.php?option=com_vikbooking&amp;task=operators&amp;vborderby=last_name&amp;vbordersort=<?php echo ($orderby == "last_name" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "last_name" && $ordersort == "ASC" ? "vbo-list-activesort" : ($orderby == "last_name" ? "vbo-list-activesort" : "")); ?>">
					<?php echo JText::_('VBCUSTOMERLASTNAME').($orderby == "last_name" && $ordersort == "ASC" ? '<i class="'.VikBookingIcons::i('sort-asc').'"></i>' : ($orderby == "last_name" ? '<i class="'.VikBookingIcons::i('sort-desc').'"></i>' : '<i class="'.VikBookingIcons::i('sort').'"></i>')); ?>
				</a>
			</th>
			<th class="title center" width="40" align="center"><?php echo JText::_('VBO_CUSTOMER_PROF_PIC'); ?></th>
			<th class="title left" width="75">
				<a href="index.php?option=com_vikbooking&amp;task=operators&amp;vborderby=email&amp;vbordersort=<?php echo ($orderby == "email" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "email" && $ordersort == "ASC" ? "vbo-list-activesort" : ($orderby == "email" ? "vbo-list-activesort" : "")); ?>">
					<?php echo JText::_('VBCUSTOMEREMAIL').($orderby == "email" && $ordersort == "ASC" ? '<i class="'.VikBookingIcons::i('sort-asc').'"></i>' : ($orderby == "email" ? '<i class="'.VikBookingIcons::i('sort-desc').'"></i>' : '<i class="'.VikBookingIcons::i('sort').'"></i>')); ?>
				</a>
			</th>
			<th class="title left" width="75">
				<a href="index.php?option=com_vikbooking&amp;task=operators&amp;vborderby=phone&amp;vbordersort=<?php echo ($orderby == "phone" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "phone" && $ordersort == "ASC" ? "vbo-list-activesort" : ($orderby == "phone" ? "vbo-list-activesort" : "")); ?>">
					<?php echo JText::_('VBCUSTOMERPHONE').($orderby == "phone" && $ordersort == "ASC" ? '<i class="'.VikBookingIcons::i('sort-asc').'"></i>' : ($orderby == "phone" ? '<i class="'.VikBookingIcons::i('sort-desc').'"></i>' : '<i class="'.VikBookingIcons::i('sort').'"></i>')); ?>
				</a>
			</th>
			<th class="title center" width="75">
				<a href="index.php?option=com_vikbooking&amp;task=operators&amp;vborderby=code&amp;vbordersort=<?php echo ($orderby == "code" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "code" && $ordersort == "ASC" ? "vbo-list-activesort" : ($orderby == "code" ? "vbo-list-activesort" : "")); ?>">
					<?php echo JText::_('VBOCODEOPERATOR').($orderby == "code" && $ordersort == "ASC" ? '<i class="'.VikBookingIcons::i('sort-asc').'"></i>' : ($orderby == "code" ? '<i class="'.VikBookingIcons::i('sort-desc').'"></i>' : '<i class="'.VikBookingIcons::i('sort').'"></i>')); ?>
				</a>
			</th>
		</tr>
		</thead>
	<?php
	$kk = 0;
	$i = 0;
	for ($i = 0, $n = count($rows); $i < $n; $i++) {
		$row = $rows[$i];
		?>
		<tr class="row<?php echo $kk; ?>">
			<td><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row['id']; ?>" onclick="Joomla.isChecked(this.checked);"></td>
			<td><a href="index.php?option=com_vikbooking&amp;task=editoperator&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['id']; ?></a></td>
			<td class="vbo-highlighted-td"><a href="index.php?option=com_vikbooking&amp;task=editoperator&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['first_name']; ?></a></td>
			<td><?php echo $row['last_name']; ?></td>
			<td class="center">
				<div class="vbo-customer-info-box">
					<div class="vbo-customer-info-box-avatar vbo-customer-avatar-small">
						<span>
						<?php
						if (!empty($row['pic'])) {
							$avatar_caption = rtrim($row['first_name'] . ' ' . $row['last_name']);
							?>
							<img src="<?php echo strpos($row['pic'], 'http') === 0 ? $row['pic'] : VBO_SITE_URI . 'resources/uploads/' . $row['pic']; ?>" data-caption="<?php echo htmlspecialchars($avatar_caption); ?>" />	
							<?php
						} else {
							VikBookingIcons::e('user-tie');
						}
						?>
						</span>
					</div>
				</div>
			</td>
			<td><?php echo $row['email']; ?></td>
			<td><?php echo $row['phone']; ?></td>
			<td class="center"><?php echo !empty($row['code']) ? $row['code'] : '-----'; ?></td>
		 </tr>
		<?php
		$kk = 1 - $kk;
	}
	?>
	</table>
</div>
	<input type="hidden" name="option" value="com_vikbooking" />
	<input type="hidden" name="task" value="operators" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHtml::_('form.token'); ?>
	<?php echo $navbut; ?>
</form>

<script type="text/javascript">
var vbo_overlay_on = false;

if (typeof jQuery.fn.tooltip === 'function') {
	jQuery(".hasTooltip").tooltip();
}

function vboToggleSendSMS(phone, firstname) {
	jQuery("#smstophone").val(phone);
	jQuery("#smstophone-lbl").text(firstname+" "+phone);
	jQuery(".vbo-info-overlay-block").fadeToggle(400, function() {
		if (jQuery(".vbo-info-overlay-block").is(":visible")) {
			vbo_overlay_on = true;
		} else {
			vbo_overlay_on = false;
		}
	});
}

jQuery(function() {

	// zoom-able avatars
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

			// build modal content
			var zoom_modal = jQuery('<div></div>').addClass('vbo-modal-overlay-block vbo-modal-overlay-zoom-image').css('display', 'block');
			var zoom_dismiss = jQuery('<a></a>').addClass('vbo-modal-overlay-close');
			zoom_dismiss.on('click', function() {
				jQuery('.vbo-modal-overlay-zoom-image').fadeOut();
			});
			zoom_modal.append(zoom_dismiss);
			var zoom_content = jQuery('<div></div>').addClass('vbo-modal-overlay-content vbo-modal-overlay-content-zoom-image');
			var zoom_head = jQuery('<div></div>').addClass('vbo-modal-overlay-content-head');
			var zoom_head_title = jQuery('<span></span>');
			if (caption) {
				zoom_head_title.text(caption);
			}
			var zoom_head_close = jQuery('<span></span>').addClass('vbo-modal-overlay-close-times').html('&times;');
			zoom_head_close.on('click', function() {
				jQuery('.vbo-modal-overlay-zoom-image').fadeOut();
			});
			zoom_head.append(zoom_head_title).append(zoom_head_close);
			var zoom_body = jQuery('<div></div>').addClass('vbo-modal-overlay-content-body vbo-modal-overlay-content-body-scroll');
			var zoom_image = jQuery('<div></div>').addClass('vbo-modal-zoom-image-wrap');
			zoom_image.append(jQuery(this).clone());
			zoom_body.append(zoom_image);
			zoom_content.append(zoom_head).append(zoom_body);
			zoom_modal.append(zoom_content);
			// append modal to body
			if (jQuery('.vbo-modal-overlay-zoom-image').length) {
				jQuery('.vbo-modal-overlay-zoom-image').remove();
			}
			jQuery('body').append(zoom_modal);
		});
	});

});
</script>
<?php
}
