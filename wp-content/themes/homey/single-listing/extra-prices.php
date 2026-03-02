<?php
global $post;
$listing_id = $post->ID;
$extra_prices = get_post_meta($listing_id, 'homey_extra_prices', true);

$homey_booking_type = homey_booking_type();
if($homey_booking_type == 'per_hour') {
	$per_night_text = esc_html__('Per Hour', 'homey');
	$per_nightguest_text = esc_html__('Per Hour Per Guest', 'homey');
} else {
	$per_night_text = esc_html__('Per Night', 'homey');
	$per_nightguest_text = esc_html__('Per Night Per Guest', 'homey');
}

if(!empty($extra_prices[0]['name']) || !empty($extra_prices[-1]['name'])) { ?>

<div class="block-body">
    <div class="block-left">
        <h3 class="title"><?php echo esc_html__('Extra Services', 'homey'); ?></h3>
    </div><!-- block-left -->
    <div class="block-right">
        <ul class="detail-list detail-list-2-cols">
           
        	<?php
			if(is_array($extra_prices)) {
			    foreach($extra_prices as $key => $option) { 
			    	$type_text = '';
			    	$type = $option['type'];
			    	if($type == 'single_fee') {
			    		$type_text = esc_html__('Single Fee', 'homey'); 
			    	} elseif($type == 'per_night') {
			    		$type_text = $per_night_text;
			    	} elseif($type == 'per_guest') {
			    		$type_text = esc_html__('Per Guest', 'homey');
			    	} elseif($type == 'per_night_per_guest') {
			    		$type_text = $per_nightguest_text;
			    	}
			    ?>
	            <li>
	                <i class="fa fa-angle-right" aria-hidden="true"></i> 
	                <?php echo esc_attr($option['name']); ?>: 
	                <strong><?php echo homey_formatted_price($option['price'], true); ?></strong>
	                <?php echo esc_attr($type_text); ?>
	            </li>
            <?php 
        		} 
        	}?>

            
        </ul>
    </div><!-- block-right -->
</div><!-- block-body -->

<?php	
}