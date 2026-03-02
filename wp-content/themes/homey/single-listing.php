<?php get_header();

global $post, $listing_author, $layout_order, $hide_labels;
$current_user = wp_get_current_user();
$all_meta_for_user = get_user_meta( $current_user->ID );
$listing_layout = homey_option('detail_layout');
$hide_labels = homey_option('show_hide_labels');
$listing_nav = homey_option('listing-detail-nav');
$homey_booking_type = homey_booking_type();
if(isset($_GET['detail_layout'])) {
    $listing_layout = $_GET['detail_layout'];
}

$layout_order = homey_option('listing_blocks');
$layout_order = $layout_order['enabled'];

$already_blocked_dates = homey_get_booked_hours($post->ID);
$already_blocked_dates = homey_get_booked_hours($post->ID);
if( have_posts() ):

    while( have_posts() ): the_post();
    

        $listing_author = homey_get_author('70', '70', 'img-circle media-object');

        if( $listing_layout == 'v1' ) {
            get_template_part( 'single-listing/single-listing', 'v1');

        } else if( $listing_layout == 'v2' ) {
            get_template_part( 'single-listing/single-listing', 'v2');
            
        } else if( $listing_layout == 'v3' ) {
            get_template_part( 'single-listing/single-listing', 'v3');

        }  else if( $listing_layout == 'v4' ) {
            get_template_part( 'single-listing/single-listing', 'v4');

        } else {
            get_template_part( 'single-listing/single-listing', 'v1');
        }
        
        get_template_part('single-listing/contact-host');

        if($homey_booking_type == 'per_hour') {
            get_template_part('single-listing/booking/overlay-hourly-booking');
        } else {
            get_template_part('single-listing/booking/overlay-booking');
        }

        if($listing_nav) {
            get_template_part('single-listing/listing-nav');
        }

    endwhile; 
endif; 
?>
<div id="post_already_bookd_dates">
    <?php foreach($already_blocked_dates as $datetimestr => $b){ ?>
        <span data-datetime="<?php echo date("d-m-Y H:i:s", $datetimestr); ?>"></span>
    <?php } ?>
</div>
<?php get_footer(); ?>