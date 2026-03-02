<?php
global $homey_local;
$pets = '';
if(isset($_GET['pets'])) {
	$pets = $_GET['pets'];
}
$search_fields = homey_option('search_hide_fields');

$adults = isset($search_fields['adults']) ? $search_fields['adults'] : 0;
$children = isset($search_fields['children']) ? $search_fields['children'] : 0;
$enable_pets = isset($search_fields['pets']) ? $search_fields['pets'] : 0;
?>
<div class="search-guests-wrap search-guests-wrap-js clearfix">
	<input type="hidden" name="adult_guest" class="search_adult_guest" value="<?php echo isset($_GET['adult_guest']) ? (int) $_GET['adult_guest'] : 0 ?>">
	<input type="hidden" name="child_guest" class="search_child_guest" value="<?php echo isset($_GET['child_guest']) ? (int) $_GET['child_guest'] : 0 ?>">

	<?php if($adults != 1) { ?>
	<div class="adults-calculator">
		<span class="quantity-calculator search_homey_adult"><?php echo isset($_GET['adult_guest']) ? (int) $_GET['adult_guest'] : 0 ?></span>
		<span class="calculator-label"><?php echo esc_attr(homey_option('srh_adults_label')); ?></span>
		<button class="search_adult_plus btn btn-secondary-outlined" type="button"><i class="fa fa-plus" aria-hidden="true"></i></button>
		<button class="search_adult_minus btn btn-secondary-outlined" type="button"><i class="fa fa-minus" aria-hidden="true"></i></button>
	</div>
	<?php } ?>

	<?php if($children != 1) { ?>
	<div class="children-calculator">
		<span class="quantity-calculator search_homey_child"><?php echo isset($_GET['child_guest']) ? (int) $_GET['child_guest'] : 0 ?></span>
		<span class="calculator-label"><?php echo esc_attr(homey_option('srh_child_label')); ?></span>
		<button class="search_child_plus btn btn-secondary-outlined" type="button"><i class="fa fa-plus" aria-hidden="true"></i></button>
		<button class="search_child_minus btn btn-secondary-outlined" type="button"><i class="fa fa-minus" aria-hidden="true"></i></button>
	</div>
	<?php } ?>

	<?php if($enable_pets != 1) { ?>
	<div class="pets-calculator">
		<span class="calculator-label"><?php echo esc_html__('Pets', 'homey'); ?></span>
		<div class="pets-calculator-control-wrap">
			<label class="control control--radio radio-tab">
				<input type="radio" <?php checked( $pets, 1 ); ?> name="pets" value="1">
				<span class="control-text"><?php echo esc_html__('Yes', 'homey'); ?></span>
				<span class="control__indicator"></span>
				<span class="radio-tab-inner"></span>
			</label>
			<label class="control control--radio radio-tab">
				<input type="radio" <?php checked( $pets, 0 ); ?> name="pets" value="0">
				<span class="control-text"><?php echo esc_html__('No', 'homey'); ?></span>
				<span class="control__indicator"></span>
				<span class="radio-tab-inner"></span>
			</label>		
		</div>
	</div><!-- pets-calculator -->
	<?php } ?>
	<div class="guest-apply-btn">
		<button class="btn btn-primary" type="button"><?php echo esc_html__(esc_attr($homey_local['sr_apply_label']), 'homey'); ?></button>
	</div><!-- guest-apply-btn -->
</div><!-- search-guests -->