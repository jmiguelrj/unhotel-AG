<?php
if ( get_option( 'homey_1_3_db' ) == false ) {
    //add_action( 'admin_notices', 'homey_db_update_notice' );

    if ( isset( $_REQUEST['homey_update_bd'] ) && $_REQUEST['homey_update_bd'] == true ) {
	    add_action( 'admin_init', 'homey_update_resrv_meta' );
        add_action( 'admin_init', 'homey_update_db' );
	}
}
function homey_db_update_notice() {

    $update_url     = add_query_arg( array(
        'homey_update_bd' => 'true'
    ), admin_url() );

    ?>
    <div class="error notice">
        <h3><?php _e( 'Database need to be update for homey 1.3.0', 'homey' ); ?></h3>
        <p><a href="<?php echo esc_url( $update_url ); ?>"><?php _e( 'Click here for database update, It is required', 'homey' ); ?></a></p>
    </div>
    <?php

}

function homey_update_db() {
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    global $wpdb;

    $table_name         = $wpdb->prefix . 'homey_earnings';
    $charset_collate    = $wpdb->get_charset_collate();
    $sql                = "CREATE TABLE $table_name (
       id bigint(20) NOT NULL AUTO_INCREMENT,
       user_id bigint(20) NOT NULL,
       guest_id bigint(20) NOT NULL,
       listing_id bigint(20) NOT NULL,
       reservation_id bigint(20) NOT NULL,
       services_fee varchar(255) NOT NULL DEFAULT '0',
       host_fee varchar(255) NOT NULL DEFAULT '0',
       upfront_payment varchar(255) NOT NULL DEFAULT '0',
       payment_due varchar(255) NOT NULL DEFAULT '0',
       net_earnings varchar(255) NOT NULL DEFAULT '0',
       total_amount varchar(255) NOT NULL DEFAULT '0',
       security_deposit varchar(255) NOT NULL DEFAULT '0',
       chargeable_amount varchar(255) NOT NULL DEFAULT '0',
       host_fee_percent bigint(20) NOT NULL DEFAULT '0',
       time TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
       UNIQUE KEY id (id)
   ) $charset_collate;";

    dbDelta( $sql );

    $table_name         = $wpdb->prefix . 'homey_payouts';
    $charset_collate    = $wpdb->get_charset_collate();
    $sql                = "CREATE TABLE $table_name (
       payout_id bigint(20) NOT NULL AUTO_INCREMENT,
       user_id bigint(20) NOT NULL,
       total_amount varchar(255) NOT NULL DEFAULT '0',
       transfer_fee varchar(255) NOT NULL DEFAULT '0',
       payout_method varchar(255) NULL DEFAULT '',
       payout_method_data varchar(255) NULL DEFAULT '',
       payout_beneficiary varchar(255) NULL DEFAULT '',
       payout_status bigint(20) NOT NULL DEFAULT '1',
       action varchar(55) NULL DEFAULT 'host_payout',
       note text NULL DEFAULT '',
       date_requested TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
       date_processed datetime DEFAULT '0000-00-00 00:00' NOT NULL,
       UNIQUE KEY id (payout_id)
   ) $charset_collate;";

    dbDelta( $sql );
}

function homey_update_resrv_meta() {

    $args = array(
        'post_type'        =>  'homey_reservation',
        'posts_per_page'    => -1,
    );

    $res_query = new WP_Query($args);
    if( $res_query->have_posts() ): 
        while ($res_query->have_posts()): $res_query->the_post(); 

            $check_in_date = get_post_meta(get_the_ID(), 'reservation_checkin_date', true);
            $check_out_date = get_post_meta(get_the_ID(), 'reservation_checkout_date', true);
            $listing_id = get_post_meta(get_the_ID(), 'reservation_listing_id', true);
            $guests = get_post_meta(get_the_ID(), 'reservation_guests', true);
            $is_hourly = get_post_meta(get_the_ID(), 'is_hourly', true);

            $reservation_id = get_the_ID();

            if($is_hourly == 'yes') {
                $check_in_hour = get_post_meta(get_the_ID(), 'reservation_checkin_hour', true);
                $check_out_hour = get_post_meta(get_the_ID(), 'reservation_checkout_hour', true);
                hm_update_hourly_resrv_meta($reservation_id, $check_in_date, $check_in_hour, $check_out_hour, $listing_id, $guests);
            } else {
                hm_update_daily_resrv_meta($reservation_id, $check_in_date, $check_out_date, $listing_id, $guests);
            }

        endwhile;
    endif;
    update_option( 'homey_1_3_db', true );
    header( 'Location: ' . admin_url() );
        
}

function hm_update_daily_resrv_meta($reservation_id, $check_in_date, $check_out_date, $listing_id, $guests) {
    $prices_array = homey_get_prices($check_in_date, $check_out_date, $listing_id, $guests);
            
    $reservation_meta['no_of_days'] = $prices_array['days_count'];
    $reservation_meta['additional_guests'] = $prices_array['additional_guests'];

    $upfront_payment = $prices_array['upfront_payment'];
    $balance = $prices_array['balance'];
    $total_price = $prices_array['total_price'];
    $price_per_night = $prices_array['price_per_night'];
    $nights_total_price = $prices_array['nights_total_price'];
    $cleaning_fee = $prices_array['cleaning_fee'];
    $city_fee = $prices_array['city_fee'];
    $services_fee = $prices_array['services_fee'];
    $days_count = $prices_array['days_count'];
    $period_days = $prices_array['period_days'];
    $taxes = $prices_array['taxes'];
    $taxes_percent = $prices_array['taxes_percent'];
    $security_deposit = $prices_array['security_deposit'];
    $additional_guests = $prices_array['additional_guests'];
    $additional_guests_price = $prices_array['additional_guests_price'];
    $additional_guests_total_price = $prices_array['additional_guests_total_price'];
    $booking_has_weekend = $prices_array['booking_has_weekend'];
    $booking_has_custom_pricing = $prices_array['booking_has_custom_pricing'];

    $reservation_meta['check_in_date'] = $check_in_date;
    $reservation_meta['check_out_date'] = $check_out_date;
    $reservation_meta['guests'] = $guests;
    $reservation_meta['listing_id'] = $listing_id;
    $reservation_meta['upfront'] = $upfront_payment;
    $reservation_meta['balance'] = $balance;
    $reservation_meta['total'] = $total_price;
    $reservation_meta['price_per_night'] = $price_per_night;
    $reservation_meta['nights_total_price'] = $nights_total_price;
    $reservation_meta['cleaning_fee'] = $cleaning_fee;
    $reservation_meta['city_fee'] = $city_fee;
    $reservation_meta['services_fee'] = $services_fee;
    $reservation_meta['period_days'] = $period_days;
    $reservation_meta['taxes'] = $taxes;
    $reservation_meta['taxes_percent'] = $taxes_percent;
    $reservation_meta['security_deposit'] = $security_deposit;
    $reservation_meta['additional_guests_price'] = $additional_guests_price;
    $reservation_meta['additional_guests_total_price'] = $additional_guests_total_price;
    $reservation_meta['booking_has_weekend'] = $booking_has_weekend;
    $reservation_meta['booking_has_custom_pricing'] = $booking_has_custom_pricing;

    update_post_meta($reservation_id, 'reservation_meta', $reservation_meta);
}

function hm_update_hourly_resrv_meta($reservation_id, $check_in_date, $check_in_hour, $check_out_hour, $listing_id, $guests) {
    $prices_array = homey_get_hourly_prices($check_in_hour, $check_out_hour, $listing_id, $guests);

    $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);
            
    $reservation_meta['no_of_hours'] = $prices_array['hours_count'];
    $reservation_meta['additional_guests'] = $prices_array['additional_guests'];

    $upfront_payment = $prices_array['upfront_payment'];
    $balance = $prices_array['balance'];
    $total_price = $prices_array['total_price'];

    $reservation_meta['check_in_date'] = $check_in_date;
    $reservation_meta['check_in_hour'] = $check_in_hour;
    $reservation_meta['check_out_hour'] = $check_out_hour;
    $reservation_meta['start_hour'] = $reservation_meta['start_hour'];
    $reservation_meta['end_hour'] = $reservation_meta['end_hour'];
    $reservation_meta['guests'] = $guests;
    $reservation_meta['listing_id'] = $listing_id;
    
    $reservation_meta['price_per_hour'] = $prices_array['price_per_hour'];
    $reservation_meta['hours_total_price'] = $prices_array['hours_total_price']; //$hours_total_price;
    
    $reservation_meta['cleaning_fee'] = $prices_array['cleaning_fee'];
    $reservation_meta['city_fee'] = $prices_array['city_fee'];
    $reservation_meta['services_fee'] = $prices_array['services_fee'];
    
    $reservation_meta['taxes'] = $prices_array['taxes'];
    $reservation_meta['taxes_percent'] = $prices_array['taxes_percent'];
    $reservation_meta['security_deposit'] = $prices_array['security_deposit'];
    
    $reservation_meta['additional_guests_price'] = $prices_array['additional_guests_price'];
    $reservation_meta['additional_guests_total_price'] = $prices_array['additional_guests_total_price'];
    $reservation_meta['booking_has_weekend'] = $prices_array['booking_has_weekend'];
    $reservation_meta['booking_has_custom_pricing'] = $prices_array['booking_has_custom_pricing'];
    $reservation_meta['upfront'] = $upfront_payment;
    $reservation_meta['balance'] = $balance;
    $reservation_meta['total'] = $total_price;

    update_post_meta($reservation_id, 'reservation_meta', $reservation_meta);
}