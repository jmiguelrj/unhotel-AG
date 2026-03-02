<?php 
global $homey_local;
$compared = isset($_COOKIE['homey_compare_listings']) ? $_COOKIE['homey_compare_listings'] : '';
$ids = explode(',', $compared);
?>
<div id="compare-property-panel" class="compare-property-panel compare-property-panel-vertical compare-property-panel-right">
	<button class="compare-property-label" style="display: none;">
		<span class="compare-count"></span>
		<i class="fa fa-exchange" aria-hidden="true"></i>
	</button>
	<h2 class="title"><?php echo esc_html__('Compare listings', 'homey'); ?></h2>

	<div class="compare-wrap">
	<?php 
	if(!empty($ids[0])) {
	foreach($ids as $id ) { ?>
		<div class="compare-item remove-<?php echo intval($id); ?>">
			<a href="" class="remove-compare remove-icon" data-listing_id="<?php echo intval($id); ?>"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
			<img class="img-responsive" src="<?php echo get_the_post_thumbnail_url($id, 'homey-listing-thumb'); ?>" width="450" height="300" alt="<?php esc_attr_e('Thumb', 'homey'); ?>">
		</div>
	<?php } 
	}?>
	</div>

	<a class="compare-btn btn btn-primary btn-full-width" href=""><?php echo esc_attr($homey_local['compare_label']); ?></a>
	<button class="btn btn-grey-outlined btn-full-width close-compare-panel"><?php echo esc_html__('Close', 'homey'); ?></button>
</div>