<?php
global $post;
$listing_id = $post->ID;
$extra_prices = get_post_meta($listing_id, 'homey_extra_prices', true);

if(!empty($extra_prices[0]['name']) || !empty($extra_prices[-1]['name'])) {
?>
<div class="search-extra-services">
	<strong><?php esc_html_e('Extra services', 'homey'); ?></strong>
	<ul class="extra-services-list list-unstyled clearfix">
		<?php
		if(is_array($extra_prices)) {
		    foreach($extra_prices as $key => $option) { ?>
		    	<li>
					<label class="homey_extra_price control control--checkbox">
						<input type="checkbox" name="extra_price[]" data-name="<?php echo esc_html__(esc_attr($option['name']), 'homey'); ?>" data-price="<?php echo esc_attr($option['price']); ?>" data-type="<?php echo esc_html__(esc_attr($option['type']), 'homey'); ?>">
						<span class="control-text"><?php echo esc_html__(esc_attr($option['name']), 'homey'); ?></span>
						<span class="control__indicator"></span>
					</label>
					<span><?php echo homey_formatted_price($option['price']); ?></span>
				</li>
		<?php    	
		    }
		}
		?>
	</ul>
</div>
<?php } ?>