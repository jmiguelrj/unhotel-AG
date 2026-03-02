<?php
global $post, $homey_local;

$listing_id = isset($_GET['listing_id']) ? $_GET['listing_id'] : '';
$guests = isset($_GET['guest']) ? $_GET['guest'] : '';
$check_in = isset($_GET['check_in']) ? $_GET['check_in'] : '';
$check_out = isset($_GET['check_out']) ? $_GET['check_out'] : '';

$check_in_unix = strtotime($check_in);
$check_out_unix = strtotime($check_out);

$booking_hide_fields = homey_option('booking_hide_fields');

$check_in_date = date(get_homey_to_std_date_format(), $check_in_unix);
$check_out_date = date(get_homey_to_std_date_format(), $check_out_unix);

$room_type = homey_taxonomy_simple_by_ID('room_type', $listing_id);
$listing_type = homey_taxonomy_simple_by_ID('listing_type', $listing_id);
$slash = '';
if(!empty($room_type) && !empty($listing_type)) {
    $slash = '/';
}

$guests_label = homey_option('srh_guest_label');
if($guests > 1) {
    $guests_label = homey_option('srh_guests_label');
}

$rating = homey_option('rating');
$total_rating = get_post_meta( $listing_id, 'listing_total_rating', true );
?>
<div class="booking-sidebar">

    <div class="block">

        <div class="booking-property clearfix">
            <div class="booking-property-info">
                <h3><?php echo get_the_title($listing_id); ?></h3>
                <div><?php echo esc_attr($room_type).' '.$slash.' '.esc_attr($listing_type); ?></div>

                <?php 
                if($rating && ($total_rating != '' && $total_rating != 0 ) ) { ?>
                <div class="list-inline rating">
                    <?php echo homey_get_review_stars($total_rating, false, true); ?>
                </div>
                <?php } ?>

            </div>
            <div class="booking-property-img">
                <?php
                if( has_post_thumbnail( $listing_id ) ) {
                    echo get_the_post_thumbnail( $listing_id, 'homey-listing-thumb',  array('class' => 'img-responsive' ) );
                }else{
                    homey_image_placeholder( 'homey-listing-thumb' );
                }
                ?>  
            </div>
        </div>

        <div class="block-body">

            <div class="booking-data clearfix">
                <div class="booking-data-block booking-data-arrive">
                    <div class="booking-data-top">
                        <i class="fa fa-arrow-circle-o-down" aria-hidden="true"></i> <?php echo esc_attr(homey_option('srh_arrive_label')); ?>
                    </div>
                    <div class="booking-data-bottom">
                        <?php if(!empty($check_in_date)) { echo esc_attr($check_in_date); } ?>
                    </div>
                </div>
                <div class="booking-data-block booking-data-depart">
                    <div class="booking-data-top">
                        <i class="fa fa-arrow-circle-o-up" aria-hidden="true"></i> <?php echo esc_attr(homey_option('srh_depart_label')); ?> 
                    </div>
                    <div class="booking-data-bottom">
                        <?php if(!empty($check_out_date)) { echo esc_attr($check_out_date); } ?>
                    </div>
                </div>
                <?php if($booking_hide_fields['guests'] != 1) {?>
                <div class="booking-data-block booking-data-guests">
                    <div class="booking-data-top">
                        <i class="fa fa-user" aria-hidden="true"></i> <?php echo esc_attr(homey_option('srh_guests_label')); ?> 
                    </div>
                    <div class="booking-data-bottom">
                        <?php echo esc_attr($guests); ?> <?php echo esc_attr($guests_label); ?>
                    </div>
                </div>
                <?php } ?>
            </div> 

            <?php 
            $booking_type = homey_booking_type_by_id($listing_id);

            if( $booking_type == 'per_week' ) {
                get_template_part('single-listing/booking/payment-list-collapse-weekly'); 
            } else if( $booking_type == 'per_month' ) {
                get_template_part('single-listing/booking/payment-list-collapse-monthly'); 
            } else if( $booking_type == 'per_day_date' ) {
                get_template_part('single-listing/booking/payment-list-collapse-daily');
            } else {
                get_template_part('single-listing/booking/payment-list-collapse'); 
            }?>
        </div><!-- block-body -->
    </div><!-- block -->
</div><!-- booking-sidebar -->
