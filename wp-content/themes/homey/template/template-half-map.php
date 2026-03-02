<?php
/**
 * Template Name: Listing Half Map
 */
global $post, $wp_query, $homey_prefix, $paged, $booking_type;

$page_id = $post->ID;
$zoom_level = homey_option('halfmap_zoom_level');
$halfmap_layout = homey_option('halfmap_posts_layout');
$halfmap_default_order = get_post_meta( $post->ID, 'homey_listings_halfmap_sort', true );
$number_of_listings = get_post_meta( $post->ID, 'homey_listings_halfmap_num', true );
$types = get_post_meta( $page_id, 'homey_halfmap_types', false );
$booking_type = get_post_meta( $page_id, 'homey_halfmap_booking_type', true );

if(!empty($number_of_listings)) {
    $halfmap_num_posts  = $number_of_listings;
} else {
    $halfmap_num_posts = 9;
}

get_header(); 
?>

<section class="half-map-wrap map-on-left clearfix">
        
        <div class="half-map-right-wrap">
            <div id="homey-halfmap" 
                data-zoom="<?php echo intval($zoom_level); ?>"
                data-layout="<?php echo esc_attr($halfmap_layout); ?>"
                data-num-posts="<?php echo esc_attr($halfmap_num_posts); ?>"
                data-order="<?php echo esc_attr($halfmap_default_order); ?>"
                data-type="<?php homey_array_to_comma_string($types); ?>"
                data-booking_type="<?php echo esc_attr($booking_type); ?>"
            >
            </div>
            <?php get_template_part('template-parts/map-controls'); ?>
        </div><!-- .half-map-right-wrap -->

        <div class="half-map-left-wrap homey-matchHeight-needed">
            <?php get_template_part('template-parts/search/search-half-map'); ?>
            <?php get_template_part('template-parts/listing/sort-tool_2'); ?>

            <div id="homey_halfmap_listings_container" class="listing-wrap item-<?php echo esc_attr($halfmap_layout); ?>-view">
            </div><!-- grid-listing-page -->
        </div><!-- .half-map-left-wrap -->
        
    </section><!-- .half-map-wrap -->


<?php get_footer(); ?>