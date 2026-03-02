<?php
/**
 * Run cron Jobs for different events
 */

require 'Carbon/autoload.php';

use Carbon\Carbon;

/*-----------------------------------------------------------------------------------*/
// Add Weekly crop interval
/*-----------------------------------------------------------------------------------*/
add_filter( 'cron_schedules', 'homey_add_weekly_cron_schedule' );
if( !function_exists('homey_add_weekly_cron_schedule') ):
    function homey_add_weekly_cron_schedule( $schedules ) {

        $schedules['weekly'] = array(
            'interval' => 7 * 24 * 60 * 60, //7 days * 24 hours * 60 minutes * 60 seconds
            'display'  => 'Once Weekly',
        );
        $schedules['one_minute'] = array(
            'interval' => 30,
            'display'  => 'One minute',
        );

        return $schedules;
    }
endif;

add_action( 'homey_reservation_declined', 'homey_reservation_declined_callback' );
if( !function_exists('homey_reservation_declined_callback') ) {
    function homey_reservation_declined_callback () {
        //reservation_status 
        $args = array(
            'post_type' => 'homey_reservation',
            'post_status' => 'publish'
        );

        $args['meta_key'] = 'reservation_status';
        $args['meta_value'] = 'available';

        $listing_selection = new WP_Query($args);

        if($listing_selection->have_posts()):
            while ($listing_selection->have_posts()): $listing_selection->the_post();

                $reservation_id = get_the_ID();
                $listing_id = get_post_meta($reservation_id, 'reservation_listing_id', true);
                $booked_on = get_post_meta($reservation_id, 'reservation_confirm_date_time', true);

                $num_hours = homey_option('num_0f_hours_to_remove_pending_resrv');
                if($num_hours >  0){
                    $num_hours = $num_hours - 1;
                }

                $carbon_booked_datetime = Carbon::parse($booked_on);
                $now = Carbon::now();
                if ($carbon_booked_datetime->diffInHours($now) > $num_hours) {
                    //Remove Pending Dates
                    $pending_dates_array = homey_remove_booking_pending_days($listing_id, $reservation_id);
                    update_post_meta($listing_id, 'reservation_pending_dates', $pending_dates_array);
                    update_post_meta($reservation_id, 'reservation_status', 'declined');
                    update_post_meta($reservation_id, 'reservation_declined', $now->toDateTimeString());
                }
            endwhile;
        endif;
    }
}


add_action( 'homey_featured_listing_expire_check', 'homey_featured_listing_expire_callback' );
if( !function_exists('homey_featured_listing_expire_callback') ) {
    function homey_featured_listing_expire_callback () {

        $featured_listing_expire = intval ( homey_option('featured_listing_expire') );


        if ( $featured_listing_expire != '-1' ) {

            $args = array(
                'post_type' => 'listing',
                'post_status' => 'publish'
            );

            $args['meta_key'] = 'homey_featured';
            $args['meta_value'] = '1';

            $listing_selection = new WP_Query($args);
            while ($listing_selection->have_posts()): $listing_selection->the_post();

                $the_id = get_the_ID();

                $prop_listed_date = get_post_meta($the_id, 'homey_featured_datetime', true);

                $prop_listed_date = strtotime($prop_listed_date);

                $expiration_date = $prop_listed_date + $featured_listing_expire * 24 * 60 * 60;

                $today = strtotime(date( 'Y-m-d G:i:s', current_time( 'timestamp', 0 ) ));

                $user_id = homey_get_author_by_post_id($the_id);
                $user = new WP_User($user_id); //administrator
                $user_role = $user->roles[0];

                if(!empty($prop_listed_date)) {
                    //if ($user_role != 'administrator') {
                    if ($expiration_date < $today) {
                        homey_listing_set_to_expire($the_id);
                    }
                    //}
                }

            endwhile;

        }


    }
}


/* -----------------------------------------------------------------------------------------------------------
 *  Set Listings as expire for featured - keep
 -------------------------------------------------------------------------------------------------------------*/
if( !function_exists('homey_listing_set_to_expire') ):
    function homey_listing_set_to_expire($post_id) {

        update_post_meta( $post_id, 'homey_featured', 0 );
        delete_post_meta( $post_id, 'homey_featured_datetime' );

    }
endif;
