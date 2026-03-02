<?php
global $homey_local;
$booking_hide_fields = homey_option('booking_hide_fields');
$prefilled = homey_get_dates_for_booking();
$guest_val = $prefilled['guest'];
if($guest_val > 0) {
	$guest_val = $guest_val;
	$guest_val2 = $guest_val;
} else {
	$guest_val = '0';
	$guest_val2 = '';
}

if($booking_hide_fields['guests'] != 1) {
?>
<div class="search-guests single-guests-js">
	<input name="guests" value="<?php echo esc_attr($guest_val2); ?>" readonly type="text" class="form-control" autocomplete="off" placeholder="<?php echo esc_attr(homey_option('srh_guests_label')); ?>">
	<input type="hidden" name="adult_guest" value="<?php echo esc_attr($guest_val); ?>">
	<input type="hidden" name="child_guest" value="0">
	<div class="search-guests-wrap single-form-guests-js clearfix">
	
		<div class="adults-calculator">
			<span class="quantity-calculator homey_adult"><?php echo esc_attr($guest_val); ?></span>
			<span class="calculator-label"><?php echo esc_attr(homey_option('srh_adults_label')); ?></span>
			<button class="adult_plus btn btn-secondary-outlined" type="button"><i class="fa fa-plus" aria-hidden="true"></i></button>
			<button class="adult_minus btn btn-secondary-outlined" type="button"><i class="fa fa-minus" aria-hidden="true"></i></button>
		</div>

		<?php if($booking_hide_fields['children'] != 1) { ?>
		<div class="children-calculator">
			<span class="quantity-calculator homey_child">0</span>
			<span class="calculator-label"><?php echo esc_attr(homey_option('srh_child_label')); ?></span>
			<button class="child_plus btn btn-secondary-outlined" type="button"><i class="fa fa-plus" aria-hidden="true"></i></button>
			<button class="child_minus btn btn-secondary-outlined" type="button"><i class="fa fa-minus" aria-hidden="true"></i></button>
		</div>
		<?php } ?>
		<div class="guest-apply-btn">
			<button id="apply_guests_hourly" class="btn btn-primary" type="button"><?php echo esc_html__(esc_attr($homey_local['sr_apply_label']),'homey'); ?></button>
		</div><!-- guest-apply-btn -->
	</div><!-- search-guests -->
</div>
<?php } ?>