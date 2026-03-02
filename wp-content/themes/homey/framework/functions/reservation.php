<?php
add_action( 'wp_ajax_nopriv_homey_add_reservation', 'homey_add_reservation' );
add_action( 'wp_ajax_homey_add_reservation', 'homey_add_reservation' );
if( !function_exists('homey_add_reservation') ) {
    function homey_add_reservation() {
        global $current_user;

        $admin_email = get_option( 'admin_email' );

        $current_user = wp_get_current_user();
        $userID       = $current_user->ID;

        //check security
//        $nonce = $_REQUEST['security'];
//        if ( ! wp_verify_nonce( $nonce, 'reservation-security-nonce' ) ) {
//
//            echo json_encode(
//                array(
//                    'success' => false,
//                    'message' => $local['security_check_text']
//                )
//            );
//            wp_die();
//        }

        $no_login_needed_for_booking = homey_option('no_login_needed_for_booking');

        if($current_user->ID == 0 && $no_login_needed_for_booking == "yes" && isset($_REQUEST['new_reser_request_user_email'])) {
            $email = trim($_REQUEST['new_reser_request_user_email']);

            if(empty($email)){
                echo json_encode(
                    array(
                        'success' => false,
                        'message' => esc_html__('Enter email address', 'homey')
                    )
                );
                wp_die();
            }

            $user = get_user_by('email', $email);

            if (isset($user->ID)) {
                add_filter('authenticate', 'for_reservation_nop_auto_login', 3, 10);
                for_reservation_nop_auto_login($user);
            } else { //create user from email
                $user_login = $email;
                $user_email = $email;
                $user_pass = wp_generate_password(8, false);
                $userdata = compact('user_login', 'user_email', 'user_pass');
                $new_user_id = wp_insert_user($userdata);

                if($new_user_id > 0){
                    homey_wp_new_user_notification( $new_user_id, $user_pass );
                }

                update_user_meta($new_user_id, 'viaphp', 1);

                // log in automatically
                if (!is_user_logged_in()) {
                    $user = get_user_by('email', $email);

                    add_filter('authenticate', 'for_reservation_nop_auto_login', 3, 10);
                    for_reservation_nop_auto_login($user);
                }
            }
        }

        $current_user = wp_get_current_user();
        $userID       = $current_user->ID;

        $local = homey_get_localization();
        $allowded_html = array();
        $reservation_meta = array();

        $listing_id = intval($_POST['listing_id']);
        $listing_owner_id  =  get_post_field( 'post_author', $listing_id );
        $check_in_date     =  wp_kses ( $_POST['check_in_date'], $allowded_html );
        $check_out_date    =  wp_kses ( $_POST['check_out_date'], $allowded_html );
        $extra_options    =  $_POST['extra_options'];
        $guest_message = stripslashes ( $_POST['guest_message'] );
        $guests   =  intval($_POST['guests']);
        $title = $local['reservation_text'];

        $booking_type = homey_booking_type_by_id($listing_id);

        $owner = homey_usermeta($listing_owner_id);
        $owner_email = $owner['email'];

        if ( !is_user_logged_in() || $userID === 0 ) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['login_for_reservation']
                )
            );
            wp_die();
        }

        if($userID == $listing_owner_id) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['own_listing_error']
                )
            );
            wp_die();
        }

        if(strtotime($check_out_date) <= strtotime($check_in_date)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['dates_not_available']
                )
            );
            wp_die();
        }

        $check_availability = check_booking_availability($check_in_date, $check_out_date, $listing_id, $guests);
        $is_available = $check_availability['success'];
        $check_message = $check_availability['message'];

        if($is_available) {

            if( $booking_type == 'per_week' ) {
                $prices_array = homey_get_weekly_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);

                $price_per_week    = $prices_array['price_per_week'];
                $weeks_total_price = $prices_array['weeks_total_price'];
                $total_weeks_count = $prices_array['total_weeks_count'];

                $reservation_meta['price_per_week'] = $price_per_week;
                $reservation_meta['weeks_total_price'] = $weeks_total_price;
                $reservation_meta['total_weeks_count'] = $total_weeks_count;
                $reservation_meta['reservation_listing_type'] = 'per_week';

            } else if( $booking_type == 'per_month' ) {
                $prices_array = homey_get_monthly_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);

                $price_per_month    = $prices_array['price_per_month'];
                $months_total_price = $prices_array['months_total_price'];
                $total_months_count = $prices_array['total_months_count'];

                $reservation_meta['price_per_month'] = $price_per_month;
                $reservation_meta['months_total_price'] = $months_total_price;
                $reservation_meta['total_months_count'] = $total_months_count;
                $reservation_meta['reservation_listing_type'] = 'per_month';

            } else if( $booking_type == 'per_day_date' ) {

                $prices_array = homey_get_day_date_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);
                $price_per_night = $prices_array['price_per_day_date'];
                $nights_total_price = $prices_array['nights_total_price'];

                $reservation_meta['price_per_day_date'] = $price_per_night;
                $reservation_meta['price_per_night'] = $price_per_night;
                $reservation_meta['days_total_price'] = $nights_total_price;
                $reservation_meta['reservation_listing_type'] = 'per_day_date';
            } else {

                $prices_array = homey_get_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);
                $price_per_night = $prices_array['price_per_night'];
                $nights_total_price = $prices_array['nights_total_price'];

                $reservation_meta['price_per_night'] = $price_per_night;
                $reservation_meta['nights_total_price'] = $nights_total_price;
                $reservation_meta['reservation_listing_type'] = 'per_night';
            }

            $reservation_meta['no_of_days'] = $prices_array['days_count'] = $booking_type == 'per_day_date' ? $prices_array['days_count'] : $prices_array['days_count'];
            $reservation_meta['additional_guests'] = $prices_array['additional_guests'];

            $upfront_payment = $prices_array['upfront_payment'];
            $balance = $prices_array['balance'];
            $total_price = $prices_array['total_price'];
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

            $reservation = array(
                'post_title'    => $title,
                'post_status'   => 'publish',
                'post_type'     => 'homey_reservation' ,
                'post_author'   => $userID
            );
            $reservation_id =  wp_insert_post($reservation );

            $reservation_update = array(
                'ID'         => $reservation_id,
                'post_title' => $title.' '.$reservation_id
            );
            wp_update_post( $reservation_update );

            update_post_meta($reservation_id, 'reservation_listing_id', $listing_id);
            update_post_meta($reservation_id, 'listing_owner', $listing_owner_id);
            update_post_meta($reservation_id, 'listing_renter', $userID);
            update_post_meta($reservation_id, 'reservation_checkin_date', $check_in_date);
            update_post_meta($reservation_id, 'reservation_checkout_date', $check_out_date);
            update_post_meta($reservation_id, 'reservation_guests', $guests);
            update_post_meta($reservation_id, 'reservation_meta', $reservation_meta);
            update_post_meta($reservation_id, 'reservation_status', 'under_review');
            update_post_meta($reservation_id, 'is_hourly', 'no');
            update_post_meta($reservation_id, 'extra_options', $extra_options);

            update_post_meta($reservation_id, 'reservation_upfront', $upfront_payment);
            update_post_meta($reservation_id, 'reservation_balance', $balance);
            update_post_meta($reservation_id, 'reservation_total', $total_price);

            $pending_dates_array = homey_get_booking_pending_days($listing_id);
            update_post_meta($listing_id, 'reservation_pending_dates', $pending_dates_array);

            echo json_encode(
                array(
                    'success' => true,
                    'message' => $local['request_sent']
                )
            );

            $message_link = homey_thread_link_after_reservation($reservation_id);
            $email_args = array(
                'reservation_detail_url' => reservation_detail_link($reservation_id),
                'guest_message' => $guest_message,
                'message_link' => $message_link
            );

            if(!empty(trim($guest_message)) ){
                do_action('homey_create_messages_thread', $guest_message, $reservation_id);
            }

            homey_email_composer( $owner_email, 'new_reservation', $email_args );
            homey_email_composer( $admin_email, 'admin_booked_reservation', $email_args );

            if(isset($current_user->user_email)){
                $reservation_page = homey_get_template_link_dash('template/dashboard-reservations2.php');
                $reservation_detail_link = add_query_arg( 'reservation_detail', $reservation_id, $reservation_page );
                $email_args = array( 'reservation_detail_url' => $reservation_detail_link );

                homey_email_composer( $current_user->user_email, 'new_reservation_sent', $email_args );
            }

            wp_die();

        } else { // end $check_availability
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $check_message
                )
            );
            wp_die();
        }

    }
}

if( !function_exists('homey_add_instance_booking') ) {
    function homey_add_instance_booking($listing_id, $check_in_date, $check_out_date, $guests, $renter_message, $extra_options, $user_id = null) {
        global $current_user;
        $current_user = wp_get_current_user();
        $userID       = $current_user->ID;

        if(!empty($user_id)) {
            $userID = $user_id;
        }

        $booking_type = homey_booking_type_by_id($listing_id);

        $local = homey_get_localization();
        $allowded_html = array();
        $reservation_meta = array();

        $listing_owner_id  =  get_post_field( 'post_author', $listing_id );
        $title = $local['reservation_text'];


        if( $booking_type == 'per_week' ) {
            $prices_array = homey_get_weekly_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);

            $price_per_week    = $prices_array['price_per_week'];
            $weeks_total_price = $prices_array['weeks_total_price'];
            $total_weeks_count = $prices_array['total_weeks_count'];

            $reservation_meta['price_per_week'] = $price_per_week;
            $reservation_meta['weeks_total_price'] = $weeks_total_price;
            $reservation_meta['total_weeks_count'] = $total_weeks_count;
            $reservation_meta['reservation_listing_type'] = 'per_week';

        } else if( $booking_type == 'per_month' ) {
            $prices_array = homey_get_monthly_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);

            $price_per_month    = $prices_array['price_per_month'];
            $months_total_price = $prices_array['months_total_price'];
            $total_months_count = $prices_array['total_months_count'];

            $reservation_meta['price_per_month'] = $price_per_month;
            $reservation_meta['months_total_price'] = $months_total_price;
            $reservation_meta['total_months_count'] = $total_months_count;
            $reservation_meta['reservation_listing_type'] = 'per_month';

        } else if( $booking_type == 'per_day_date' ) {

            $prices_array = homey_get_day_date_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);
            $price_per_day_date = $prices_array['price_per_day_date'];
            $nights_total_price = $prices_array['nights_total_price'];

            $reservation_meta['price_per_day_date'] = $price_per_day_date;
            $reservation_meta['days_total_price'] = $nights_total_price;
            $reservation_meta['reservation_listing_type'] = 'per_day_date';
        }else {

            $prices_array = homey_get_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);
            $price_per_night = $prices_array['price_per_night'];
            $nights_total_price = $prices_array['nights_total_price'];

            $reservation_meta['price_per_night'] = $price_per_night;
            $reservation_meta['nights_total_price'] = $nights_total_price;
            $reservation_meta['reservation_listing_type'] = 'per_night';
        }


        $reservation_meta['no_of_days'] = $prices_array['days_count'];
        $reservation_meta['additional_guests'] = $prices_array['additional_guests'];

        $upfront_payment = $prices_array['upfront_payment'];
        $balance = $prices_array['balance'];
        $total_price = $prices_array['total_price'];
        $cleaning_fee = $prices_array['cleaning_fee'];
        $city_fee = $prices_array['city_fee'];
        $services_fee = $prices_array['services_fee'];
        $days_count = ( $booking_type == 'per_day_date' ) ? $prices_array['days_count'] + 1 : $prices_array['days_count'];
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

        $reservation = array(
            'post_title'    => $title,
            'post_status'   => 'publish',
            'post_type'     => 'homey_reservation' ,
            'post_author'   => $userID
        );
        $reservation_id =  wp_insert_post($reservation );

        $reservation_update = array(
            'ID'         => $reservation_id,
            'post_title' => $title.' '.$reservation_id
        );
        wp_update_post( $reservation_update );

        update_post_meta($reservation_id, 'reservation_listing_id', $listing_id);
        update_post_meta($reservation_id, 'listing_owner', $listing_owner_id);
        update_post_meta($reservation_id, 'listing_renter', $userID);
        update_post_meta($reservation_id, 'reservation_checkin_date', $check_in_date);
        update_post_meta($reservation_id, 'reservation_checkout_date', $check_out_date);
        update_post_meta($reservation_id, 'reservation_guests', $guests);
        update_post_meta($reservation_id, 'reservation_meta', $reservation_meta);
        update_post_meta($reservation_id, 'reservation_status', 'booked');
        update_post_meta($reservation_id, 'is_hourly', 'no');

        update_post_meta($reservation_id, 'extra_options', $extra_options);

        update_post_meta($reservation_id, 'reservation_upfront', $upfront_payment);
        update_post_meta($reservation_id, 'reservation_balance', $balance);
        update_post_meta($reservation_id, 'reservation_total', $total_price);

        //Book dates
        $booked_days_array = homey_make_days_booked($listing_id, $reservation_id);
        update_post_meta($listing_id, 'reservation_dates', $booked_days_array);

        do_action('homey_create_messages_thread', $renter_message, $reservation_id, $user_id);

        return $reservation_id;

    }
}

add_action( 'wp_ajax_nopriv_homey_reserve_period_host', 'homey_reserve_period_host' );
add_action( 'wp_ajax_homey_reserve_period_host', 'homey_reserve_period_host' );
if( !function_exists('homey_reserve_period_host') ) {
    function homey_reserve_period_host() {
        global $current_user;
        $current_user = wp_get_current_user();
        $userID       = $current_user->ID;
        $local = homey_get_localization();
        $allowded_html = array();
        $reservation_meta = array();


        $time = time();
        $date = date( 'Y-m-d H:i:s', $time );

        $listing_id = intval($_POST['listing_id']);
        $listing_owner_id  =  get_post_field( 'post_author', $listing_id );
        $check_in_date     =  date('d-m-Y', custom_strtotime(wp_kses ( $_POST['check_in_date'], $allowded_html )));
        $check_out_date    =  date('d-m-Y', custom_strtotime(wp_kses ( $_POST['check_out_date'], $allowded_html )));

        $period_note   =  wp_kses ( $_POST['period_note'], $allowded_html );
        $title = $local['reservation_text'];
        $guests = 0;

        $owner = homey_usermeta($listing_owner_id);
        $owner_email = $owner['email'];

        if ( !is_user_logged_in() || $userID === 0 ) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['login_for_reservation']
                )
            );
            wp_die();
        }

        //check security
        $nonce = $_REQUEST['security'];
        if ( ! wp_verify_nonce( $nonce, 'period-security-nonce' ) ) {

            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['security_check_text']
                )
            );
            wp_die();
        }

        if( $listing_owner_id != $userID ) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['listing_owner_text']
                )
            );
            wp_die();
        }

        $check_availability = check_booking_availability_for_reserve_period_host($check_in_date, $check_out_date, $listing_id);
        $is_available = $check_availability['success'];
        $check_message = $check_availability['message'];

        if($is_available) {
            $prices_array = homey_get_prices($check_in_date, $check_out_date, $listing_id, $guests);


            $reservation_meta['renter_msg'] = $period_note;

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

            $reservation = array(
                'post_title'    => $title,
                'post_status'   => 'publish',
                'post_type'     => 'homey_reservation' ,
                'post_author'   => $userID
            );
            $reservation_id =  wp_insert_post($reservation );

            $reservation_update = array(
                'ID'         => $reservation_id,
                'post_title' => $title.' '.$reservation_id
            );
            wp_update_post( $reservation_update );

            update_post_meta($reservation_id, 'reservation_listing_id', $listing_id);
            update_post_meta($reservation_id, 'listing_owner', $listing_owner_id);
            update_post_meta($reservation_id, 'listing_renter', $userID);
            update_post_meta($reservation_id, 'reservation_checkin_date', $check_in_date);
            update_post_meta($reservation_id, 'reservation_checkout_date', $check_out_date);
            update_post_meta($reservation_id, 'reservation_guests', $guests);
            update_post_meta($reservation_id, 'reservation_meta', $reservation_meta);
            update_post_meta($reservation_id, 'reservation_status', 'booked');

            update_post_meta($reservation_id, 'reservation_upfront', $upfront_payment);
            update_post_meta($reservation_id, 'reservation_balance', $balance);
            update_post_meta($reservation_id, 'reservation_total', $total_price);

            $booked_dates_array = homey_get_booked_days_host_period($listing_id);
            update_post_meta($listing_id, 'reservation_dates', $booked_dates_array);

            echo json_encode(
                array(
                    'success' => true,
                    'message' => $local['reserve_period_success']
                )
            );

            $invoiceID = homey_generate_invoice( 'reservation','one_time', $reservation_id, $date, $userID, 0, 0, '', 'Self' );

            update_post_meta( $invoiceID, 'invoice_payment_status', 1 );

            wp_die();

        } else { // end $check_availability
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $check_message
                )
            );
            wp_die();
        }

    }
}




if (!function_exists("homey_get_booking_pending_days")) {
    function homey_get_booking_pending_days($listing_id) {
        $now = time();
        $daysAgo = $now-3*24*60*60;

        $args = array(
            'post_type'        => 'homey_reservation',
            'post_status'      => 'any',
            'posts_per_page'   => -1,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key'       => 'reservation_listing_id',
                    'value'     => $listing_id,
                    'type'      => 'NUMERIC',
                    'compare'   => '='
                ),
                array(
                    'key'       => 'reservation_status',
                    'value'     => 'declined',
                    'type'      => 'CHAR',
                    'compare'   => '!='
                ),
                array(
                    'key'       => 'reservation_status',
                    'value'     => 'cancelled',
                    'type'      => 'CHAR',
                    'compare'   => '!='
                )
            )
        );

        $pending_dates_array = get_post_meta($listing_id, 'reservation_pending_dates', true );

        if( !is_array($pending_dates_array) || empty($pending_dates_array) ) {
            $pending_dates_array  = array();
        }

        $wpQry = new WP_Query($args);

        if ($wpQry->have_posts()) {

            while ($wpQry->have_posts()): $wpQry->the_post();

                $resID = get_the_ID();

                $check_in_date  = get_post_meta( $resID, 'reservation_checkin_date', true );
                $check_out_date = get_post_meta( $resID, 'reservation_checkout_date', true );

                $unix_time_start = strtotime ($check_in_date);

                if ($unix_time_start > $daysAgo) {
                    $check_in       =   new DateTime($check_in_date);
                    $check_in_unix  =   $check_in->getTimestamp();
                    $check_out      =   new DateTime($check_out_date);
                    $check_out_unix =   $check_out->getTimestamp();


                    $pending_dates_array[$check_in_unix] = $resID;

                    $check_in_unix =   $check_in->getTimestamp();

                    while ($check_in_unix < $check_out_unix){

                        $pending_dates_array[$check_in_unix] = $resID;

                        $check_in->modify('tomorrow');
                        $check_in_unix =   $check_in->getTimestamp();
                    }
                }
            endwhile;
            wp_reset_postdata();
        }

        return $pending_dates_array;

    }
}

if (!function_exists("homey_get_booked_days")) {
    function homey_get_booked_days($listing_id) {
        $now = time();
        $daysAgo = $now-3*24*60*60;

        $args = array(
            'post_type'        => 'homey_reservation',
            'post_status'      => 'any',
            'posts_per_page'   => -1,
            'meta_query' => array(
                array(
                    'key'       => 'reservation_listing_id',
                    'value'     => $listing_id,
                    'type'      => 'NUMERIC',
                    'compare'   => '='
                ),
                array(
                    'key'       =>  'reservation_status',
                    'value'     =>  'booked',
                    'compare'   =>  '='
                )
            )
        );

        $booked_dates_array = get_post_meta($listing_id, 'reservation_dates', true );

        if( !is_array($booked_dates_array) || empty($booked_dates_array) ) {
            $booked_dates_array  = array();
        }

        $wpQry = new WP_Query($args);

        if ($wpQry->have_posts()) {

            while ($wpQry->have_posts()): $wpQry->the_post();

                $resID = get_the_ID();

                $check_in_date  = get_post_meta( $resID, 'reservation_checkin_date', true );
                $check_out_date = get_post_meta( $resID, 'reservation_checkout_date', true );

                $unix_time_start = strtotime ($check_in_date);

                if ($unix_time_start > $daysAgo) {
                    $check_in       =   new DateTime($check_in_date);
                    $check_in_unix  =   $check_in->getTimestamp();
                    $check_out      =   new DateTime($check_out_date);
                    $check_out_unix =   $check_out->getTimestamp();


                    $booked_dates_array[$check_in_unix] = $resID;

                    $check_in_unix =   $check_in->getTimestamp();

                    while ($check_in_unix < $check_out_unix){

                        $booked_dates_array[$check_in_unix] = $resID;

                        $check_in->modify('tomorrow');
                        $check_in_unix =   $check_in->getTimestamp();
                    }
                }
            endwhile;
            wp_reset_postdata();
        }

        return $booked_dates_array;

    }
}

if (!function_exists("homey_make_days_booked")) {
    function homey_make_days_booked($listing_id, $resID) {
        $now = time();
        $daysAgo = $now-3*24*60*60;

        $check_in_date  = get_post_meta( $resID, 'reservation_checkin_date', true );
        $check_out_date = get_post_meta( $resID, 'reservation_checkout_date', true );

        $reservation_dates_array = get_post_meta($listing_id, 'reservation_dates', true );

        if( !is_array($reservation_dates_array) || empty($reservation_dates_array) ) {
            $reservation_dates_array  = array();
        }

        $unix_time_start = strtotime ($check_in_date);
        $booking_type = homey_booking_type_by_id($listing_id);

        if ($unix_time_start > $daysAgo) {
            $check_in       =   new DateTime($check_in_date);
            $check_in_unix  =   $check_in->getTimestamp();
            $check_out      =   new DateTime($check_out_date);
            $check_out_unix =   $check_out->getTimestamp();

            $check_in_unix =   $check_in->getTimestamp();
            if($booking_type == 'per_day_date'){
                while ($check_in_unix <= $check_out_unix){

                    $reservation_dates_array[$check_in_unix] = $resID;

                    $check_in->modify('tomorrow');
                    $check_in_unix =   $check_in->getTimestamp();
                }
            }else{
                while ($check_in_unix < $check_out_unix){

                    $reservation_dates_array[$check_in_unix] = $resID;

                    $check_in->modify('tomorrow');
                    $check_in_unix =   $check_in->getTimestamp();
                }
            }

        }

        return $reservation_dates_array;
    }
}

if (!function_exists("homey_remove_booking_pending_days")) {
    function homey_remove_booking_pending_days($listing_id, $resID, $delete_dates = false) {
        $now = time();
        $daysAgo = $now-3*24*60*60;

        $check_in_date  = get_post_meta( $resID, 'reservation_checkin_date', true );
        $check_out_date = get_post_meta( $resID, 'reservation_checkout_date', true );

        $pending_dates_array = get_post_meta($listing_id, 'reservation_pending_dates', true );

        if( !is_array($pending_dates_array) || empty($pending_dates_array) ) {
            $pending_dates_array  = array();
        }

        $unix_time_start = strtotime ($check_in_date);

        if ($unix_time_start > $daysAgo || $delete_dates == true) {
            $check_in       =   new DateTime($check_in_date);
            $check_in_unix  =   $check_in->getTimestamp();
            $check_out      =   new DateTime($check_out_date);
            $check_out_unix =   $check_out->getTimestamp();

            $check_in_unix =   $check_in->getTimestamp();

            while ($check_in_unix < $check_out_unix){

                unset($pending_dates_array[$check_in_unix]);

                $check_in->modify('tomorrow');
                $check_in_unix =   $check_in->getTimestamp();
            }
        }

        return $pending_dates_array;
    }
}

if (!function_exists("homey_remove_booking_booked_days")) {
    function homey_remove_booking_booked_days($listing_id, $resID) {
        $now = time();
        $daysAgo = $now-3*24*60*60;

        $check_in_date  = get_post_meta( $resID, 'reservation_checkin_date', true );
        $check_out_date = get_post_meta( $resID, 'reservation_checkout_date', true );

        $booked_dates_array = get_post_meta($listing_id, 'reservation_dates', true );

        if( !is_array($booked_dates_array) || empty($booked_dates_array) ) {
            $booked_dates_array  = array();
        }

        $unix_time_start = strtotime ($check_in_date);

        if ($unix_time_start > $daysAgo) {
            $check_in       =   new DateTime($check_in_date);
            $check_in_unix  =   $check_in->getTimestamp();
            $check_out      =   new DateTime($check_out_date);
            $check_out_unix =   $check_out->getTimestamp();

            $check_in_unix =   $check_in->getTimestamp();

            while ($check_in_unix < $check_out_unix){

                unset($booked_dates_array[$check_in_unix]);

                $check_in->modify('tomorrow');
                $check_in_unix =   $check_in->getTimestamp();
            }
        }

        return $booked_dates_array;
    }
}

if (!function_exists("homey_get_booked_days_host_period")) {
    function homey_get_booked_days_host_period($listing_id) {
        $now = time();
        $daysAgo = $now-3*24*60*60;

        $args = array(
            'post_type'        => 'homey_reservation',
            'post_status'      => 'any',
            'posts_per_page'   => -1,
            'meta_query' => array(
                'relation' => 'AND', // Optional, defaults to "AND"
                array(
                    'key'       => 'reservation_listing_id',
                    'value'     => $listing_id,
                    'type'      => 'NUMERIC',
                    'compare'   => '='
                ),
                array(
                    'key'     => 'reservation_status',
                    'value'   => 'booked',
                    'compare' => '='
                )
            )
        );

        $booked_dates_array = get_post_meta($listing_id, 'reservation_dates', true );

        if( !is_array($booked_dates_array) || empty($booked_dates_array) ) {
            $booked_dates_array  = array();
        }

        $wpQry = new WP_Query($args);

        if ($wpQry->have_posts()) {

            while ($wpQry->have_posts()): $wpQry->the_post();

                $resID = get_the_ID();

                $check_in_date  = get_post_meta( $resID, 'reservation_checkin_date', true );
                $check_out_date = get_post_meta( $resID, 'reservation_checkout_date', true );

                $unix_time_start = strtotime ($check_in_date);

                if ($unix_time_start > $daysAgo) {
                    $check_in       =   new DateTime($check_in_date);
                    $check_in_unix  =   $check_in->getTimestamp();
                    $check_out      =   new DateTime($check_out_date);
                    $check_out_unix =   $check_out->getTimestamp();


                    $booked_dates_array[$check_in_unix] = $resID;

                    $check_in_unix =   $check_in->getTimestamp();

                    while ($check_in_unix < $check_out_unix){

                        $booked_dates_array[$check_in_unix] = $resID;

                        $check_in->modify('tomorrow');
                        $check_in_unix =   $check_in->getTimestamp();
                    }
                }
            endwhile;
            wp_reset_postdata();
        }

        return $booked_dates_array;

    }
}

if(!function_exists('check_booking_availability')) {
    function check_booking_availability($check_in_date, $check_out_date, $listing_id, $guests) {
        $return_array = array();
        $local = homey_get_localization();
        $booking_proceed = true;

        $booking_hide_fields = homey_option('booking_hide_fields');
        $booking_type = homey_booking_type_by_id($listing_id);

        $homey_allow_additional_guests = get_post_meta($listing_id, 'homey_allow_additional_guests', true);
        $allowed_guests = get_post_meta($listing_id, 'homey_guests', true);

        if(!empty($allowed_guests)) {
            if( ($homey_allow_additional_guests != 'yes') && ($guests > $allowed_guests)) {
                $return_array['success'] = false;
                $return_array['message'] = $local['guest_allowed'].' '.$allowed_guests;
                return $return_array;
            }
        }

        if($booking_type != "per_day_date" && strtotime($check_out_date) <= strtotime($check_in_date)) {
            $booking_proceed = false;
        }

        if(empty($check_in_date) && empty($check_out_date) && empty($guests)) {
            $return_array['success'] = false;
            $return_array['message'] = $local['fill_all_fields'];
            return $return_array;

        }

        if(empty($check_in_date)) {
            $return_array['success'] = false;
            $return_array['message'] = $local['choose_checkin'];
            return $return_array;

        }

        if(empty($check_out_date)) {
            $return_array['success'] = false;
            $return_array['message'] = $local['choose_checkout'];
            return $return_array;

        }


        $time_difference = abs( strtotime($check_in_date) - strtotime($check_out_date) );
        $days_count      = $time_difference/86400;
        $days_count      = intval($days_count);
        if($booking_type == "per_day_date"){ $days_count += 1; }


        if( $booking_type == 'per_week' ) {

            $total_weeks_count = $days_count / 7;

            $min_book_weeks = get_post_meta($listing_id, 'homey_min_book_weeks', true);
            $max_book_weeks = get_post_meta($listing_id, 'homey_max_book_weeks', true);

            if($total_weeks_count < $min_book_weeks) {
                $return_array['success'] = false;
                $return_array['message'] = $local['min_book_weeks_error'].' '.$min_book_weeks;
                return $return_array;
            }

            if(($total_weeks_count > $max_book_weeks) && !empty($max_book_weeks)) {
                $return_array['success'] = false;
                $return_array['message'] = $local['max_book_weeks_error'].' '.$max_book_weeks;
                return $return_array;
            }

        } else if( $booking_type == 'per_month' ) {

            $total_months_count = $days_count / 30;

            $min_book_months = get_post_meta($listing_id, 'homey_min_book_months', true);
            $max_book_months = get_post_meta($listing_id, 'homey_max_book_months', true);

            if($total_months_count < $min_book_months) {
                $return_array['success'] = false;
                $return_array['message'] = $local['min_book_months_error'].' '.$min_book_months;
                return $return_array;
            }

            if(($total_months_count > $max_book_months) && !empty($max_book_months)) {
                $return_array['success'] = false;
                $return_array['message'] = $local['max_book_months_error'].' '.$max_book_months;
                return $return_array;
            }

        } else if( $booking_type == 'per_day_date' ) { // per day
            $min_book_days = get_post_meta($listing_id, 'homey_min_book_days', true);
            $max_book_days = get_post_meta($listing_id, 'homey_max_book_days', true);

            if($days_count < $min_book_days) {
                echo json_encode(
                    array(
                        'success' => false,
                        'message' => $local['min_book_day_dates_error'].' '.$min_book_days
                    )
                );
                wp_die();
            }

            if(($days_count > $max_book_days) && !empty($max_book_days)) {
                echo json_encode(
                    array(
                        'success' => false,
                        'message' => $local['max_book_day_dates_error'].' '.$max_book_days
                    )
                );
                wp_die();
            }
        } else {

            $min_book_days = get_post_meta($listing_id, 'homey_min_book_days', true);
            $max_book_days = get_post_meta($listing_id, 'homey_max_book_days', true);

            if($days_count < $min_book_days) {
                $return_array['success'] = false;
                $return_array['message'] = $local['min_book_days_error'].' '.$min_book_days;
                return $return_array;
            }

            if(($days_count > $max_book_days) && !empty($max_book_days)) {
                $return_array['success'] = false;
                $return_array['message'] = $local['max_book_days_error'].' '.$max_book_days;
                return $return_array;
            }
        }

        if(empty($guests) && $booking_hide_fields['guests'] != 1) {
            $return_array['success'] = false;
            $return_array['message'] = $local['choose_guests'];
            return $return_array;

        }

        if(!$booking_proceed) {
            $return_array['success'] = false;
            $return_array['message'] = $local['ins_book_proceed'];
            return $return_array;
        }


        $reservation_booked_array = get_post_meta($listing_id, 'reservation_dates', true);
        if(empty($reservation_booked_array)) {
            $reservation_booked_array = homey_get_booked_days($listing_id);
        }

        $reservation_pending_array = get_post_meta($listing_id, 'reservation_pending_dates', true);
        if(empty($reservation_pending_array)) {
            $reservation_pending_array = homey_get_booking_pending_days($listing_id);
        }

        $reservation_unavailable_array = get_post_meta($listing_id, 'reservation_unavailable', true);
        if(empty($reservation_unavailable_array)) {
            $reservation_unavailable_array = array();
        }

        $check_in      = new DateTime($check_in_date);
        $check_in_unix = $check_in->getTimestamp();

        $check_out     = new DateTime($check_out_date);
        $check_out->modify('yesterday');
        $check_out_unix = $check_out->getTimestamp();

        while ($check_in_unix <= $check_out_unix) {

            if( array_key_exists($check_in_unix, $reservation_booked_array)  || array_key_exists($check_in_unix, $reservation_pending_array) || array_key_exists($check_in_unix, $reservation_unavailable_array) ) {

                $return_array['success'] = false;
                $return_array['message'] = $local['dates_not_available'];
                if(homey_is_instance_page()) {
                    $return_array['message'] = $local['ins_unavailable'];
                }
                return $return_array; //dates are not available

            }
            $check_in->modify('tomorrow');
            $check_in_unix =   $check_in->getTimestamp();
        }

        //dates are available
        $return_array['success'] = true;
        $return_array['message'] = $local['dates_available'];
        return $return_array;

    }
}

if(!function_exists('check_booking_availability_for_reserve_period_host')) {
    function check_booking_availability_for_reserve_period_host($check_in_date, $check_out_date, $listing_id) {
        $return_array = array();
        $local = homey_get_localization();
        $booking_proceed = true;

        if(strtotime($check_out_date) <= strtotime($check_in_date)) {
            $booking_proceed = false;
        }

        if(empty($check_in_date) && empty($check_out_date)) {
            $return_array['success'] = false;
            $return_array['message'] = $local['fill_all_fields'];
            return $return_array;

        }

        if(empty($check_in_date)) {
            $return_array['success'] = false;
            $return_array['message'] = $local['start_date_label'];
            return $return_array;

        }

        if(empty($check_out_date)) {
            $return_array['success'] = false;
            $return_array['message'] = $local['end_date_label'];
            return $return_array;

        }


        $time_difference = abs( strtotime($check_in_date) - strtotime($check_out_date) );
        $days_count      = $time_difference/86400;
        $days_count      = intval($days_count);

        if(!$booking_proceed) {
            $return_array['success'] = false;
            $return_array['message'] = $local['ins_book_proceed'];
            return $return_array;
        }


        $reservation_booked_array = get_post_meta($listing_id, 'reservation_dates', true);
        if(empty($reservation_booked_array)) {
            $reservation_booked_array = homey_get_booked_days($listing_id);
        }

        $reservation_pending_array = get_post_meta($listing_id, 'reservation_pending_dates', true);
        if(empty($reservation_pending_array)) {
            $reservation_pending_array = homey_get_booking_pending_days($listing_id);
        }

        $check_in      = new DateTime($check_in_date);
        $check_in_unix = $check_in->getTimestamp();

        $check_out     = new DateTime($check_out_date);
        $check_out->modify('yesterday');
        $check_out_unix = $check_out->getTimestamp();

        while ($check_in_unix <= $check_out_unix) {

            if( array_key_exists($check_in_unix, $reservation_booked_array)  || array_key_exists($check_in_unix, $reservation_pending_array) ) {

                $return_array['success'] = false;
                $return_array['message'] = $local['dates_not_available'];
                if(homey_is_instance_page()) {
                    $return_array['message'] = $local['ins_unavailable'];
                }
                return $return_array; //dates are not available

            }
            $check_in->modify('tomorrow');
            $check_in_unix =   $check_in->getTimestamp();
        }

        //dates are available
        $return_array['success'] = true;
        $return_array['message'] = $local['dates_available'];
        return $return_array;

    }
}


if(!function_exists('get_custom_periods')) {
    function get_custom_periods($listing_id) {
        if(empty($listing_id)) {
            return;
        }

        $period_array = get_post_meta($listing_id, 'homey_custom_period', true);

        if(empty($period_array)) {
            return;
        }

        if(is_array($period_array)) {
            ksort($period_array);
        }

        $periods_by_name = array();

        foreach ($period_array as $timestamp => $data) {
            $data['timestamp'] = $timestamp;
            $periods_by_name[$data['period_name']][] = $data;
        }

        $periods = array();

        foreach ($periods_by_name as $period) {
            $start_key = array_key_first($period);
            $end_key = array_key_last($period);

            $from_date = new DateTime("@".$period[$start_key]['timestamp']);
            $to_date = new DateTime("@".$period[$end_key]['timestamp']);

            array_push($periods, array(
                'period_name' => $period[$start_key]['period_name'],
                'start_date' => $from_date->format('Y-m-d'),
                'end_date' => $to_date->format('Y-m-d'),
                'night_minimum' => $period[$start_key]['night_minimum'],
            ));
        }

        return $periods;
    }
}

if(!function_exists('date_range')) {
    function date_range($from, $to) {

        if(gettype($from) == 'integer') {
            $from = date('Y-m-d', $from);
        }

        if(gettype($to) == 'integer') {
            $to = date('Y-m-d', $to);
        }

        return array_map(function($arg) {
            return date('Y-m-d', $arg);
        }, range(strtotime($from), strtotime($to), 86400));
    }
}


add_action( 'wp_ajax_nopriv_check_booking_availability_on_date_change', 'check_booking_availability_on_date_change' );
add_action( 'wp_ajax_check_booking_availability_on_date_change', 'check_booking_availability_on_date_change' );
if(!function_exists('check_booking_availability_on_date_change')) {
    function check_booking_availability_on_date_change() {
        $local = homey_get_localization();
        $allowded_html = array();
        $booking_proceed = true;

        $listing_id = intval($_POST['listing_id']);
        $check_in_date     =  wp_kses ( $_POST['check_in_date'], $allowded_html );
        $check_out_date    =  wp_kses ( $_POST['check_out_date'], $allowded_html );

        $booking_type = homey_booking_type_by_id( $listing_id );

        if($booking_type != "per_day_date" && strtotime($check_out_date) <= strtotime($check_in_date)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['ins_book_proceed']
                )
            );
            wp_die();
        }

        $time_difference = abs( strtotime($check_in_date) - strtotime($check_out_date) );
        $days_count      = $time_difference/86400;
        $days_count      = intval($days_count);

        if($booking_type == "per_day_date"){ $days_count += 1; }
        $custom_periods = get_custom_periods($listing_id);

        foreach($custom_periods as $custom_period) {
            $period_name = $custom_period['period_name'];
            $start_date = strtotime($custom_period['start_date']);
            $end_date = strtotime($custom_period['end_date']);
            $night_minimum = $custom_period['night_minimum'];

            $arrival_date = strtotime($check_in_date);
            $departure_date = strtotime($check_out_date);

            if(
                ($arrival_date >= $start_date && $arrival_date <= $end_date) ||
                ($departure_date >= $start_date && $departure_date <= $end_date)
            ) {

                $period_rent = date_range($arrival_date, $departure_date);
                $custom_range = date_range($start_date, $end_date);

                // $count_rented_days = 0;

                // foreach ($period_rent as $rented_day) {
                //     foreach ($custom_range as $custom_range_day) {
                //         if ($rented_day == $custom_range_day) {
                //             $count_rented_days += 1;
                //         }
                //     }
                // }

                // Count rented days minus one.
                // The last day is ignored, because is the departure day.
                $count_rented_days = count($period_rent) - 1;

                if ($count_rented_days >= $night_minimum) {
                    break;
                } else {
                    $start_date = date(get_option('date_format'), $start_date);
                    $end_date = date(get_option('date_format'), $end_date);

                    echo json_encode(
                        array(
                            'success' => false,
                            'message' => sprintf( $local['min_book_custom_period_days_error'], $period_name, $night_minimum, $start_date, $end_date)

                        )
                    );
                    wp_die();
                }
            }
        }


        if( $booking_type == 'per_week' ) {

            $min_book_weeks = get_post_meta($listing_id, 'homey_min_book_weeks', true);
            $max_book_weeks = get_post_meta($listing_id, 'homey_max_book_weeks', true);

            $total_weeks_count = $days_count / 7;

            if($total_weeks_count < $min_book_weeks) {
                echo json_encode(
                    array(
                        'success' => false,
                        'message' => $local['min_book_weeks_error'].' '.$min_book_weeks
                    )
                );
                wp_die();
            }

            if(($total_weeks_count > $max_book_weeks) && !empty($max_book_weeks)) {
                echo json_encode(
                    array(
                        'success' => false,
                        'message' => $local['max_book_weeks_error'].' '.$max_book_weeks
                    )
                );
                wp_die();
            }

        } else if( $booking_type == 'per_month' ) {

            $min_book_months = get_post_meta($listing_id, 'homey_min_book_months', true);
            $max_book_months = get_post_meta($listing_id, 'homey_max_book_months', true);

            $total_months_count = $days_count / 30;

            if($total_months_count < $min_book_months) {
                echo json_encode(
                    array(
                        'success' => false,
                        'message' => $local['min_book_months_error'].' '.$min_book_months
                    )
                );
                wp_die();
            }

            if(($total_months_count > $max_book_months) && !empty($max_book_months)) {
                echo json_encode(
                    array(
                        'success' => false,
                        'message' => $local['max_book_months_error'].' '.$max_book_months
                    )
                );
                wp_die();
            }

        } else if( $booking_type == 'per_day_date' ) { // per day
            $min_book_days = get_post_meta($listing_id, 'homey_min_book_days', true);
            $max_book_days = get_post_meta($listing_id, 'homey_max_book_days', true);

            if($days_count < $min_book_days) {
                echo json_encode(
                    array(
                        'success' => false,
                        'message' => $local['min_book_day_dates_error'].' '.$min_book_days
                    )
                );
                wp_die();
            }

            if(($days_count > $max_book_days) && !empty($max_book_days)) {
                echo json_encode(
                    array(
                        'success' => false,
                        'message' => $local['max_book_day_dates_error'].' '.$max_book_days
                    )
                );
                wp_die();
            }
        } else { // Per Night
            $min_book_days = get_post_meta($listing_id, 'homey_min_book_days', true);
            $max_book_days = get_post_meta($listing_id, 'homey_max_book_days', true);

            if($days_count < $min_book_days) {
                echo json_encode(
                    array(
                        'success' => false,
                        'message' => $local['min_book_days_error'].' '.$min_book_days
                    )
                );
                wp_die();
            }

            if(($days_count > $max_book_days) && !empty($max_book_days)) {
                echo json_encode(
                    array(
                        'success' => false,
                        'message' => $local['max_book_days_error'].' '.$max_book_days
                    )
                );
                wp_die();
            }
        }

        $reservation_booked_array = get_post_meta($listing_id, 'reservation_dates', true);
        if(empty($reservation_booked_array)) {
            $reservation_booked_array = homey_get_booked_days($listing_id);
        }

        $reservation_pending_array = get_post_meta($listing_id, 'reservation_pending_dates', true);
        if(empty($reservation_pending_array)) {
            $reservation_pending_array = homey_get_booking_pending_days($listing_id);
        }

        $reservation_unavailable_array = get_post_meta($listing_id, 'reservation_unavailable', true);
        if(empty($reservation_unavailable_array)) {
            $reservation_unavailable_array = array();
        }

        $check_in      = new DateTime($check_in_date);
        $check_in_unix = $check_in->getTimestamp();

        $check_out     = new DateTime($check_out_date);

        if($booking_type != "per_day_date"){
            $check_out->modify('yesterday');
        }

        $check_out_unix = $check_out->getTimestamp();

        while ($check_in_unix <= $check_out_unix) {

            if( array_key_exists($check_in_unix, $reservation_booked_array)  || array_key_exists($check_in_unix, $reservation_pending_array) || array_key_exists($check_in_unix, $reservation_unavailable_array) ) {

                echo json_encode(
                    array(
                        'success' => false,
                        'message' => $local['dates_not_available']
                    )
                );
                wp_die();

            }
            $check_in->modify('tomorrow');
            $check_in_unix =   $check_in->getTimestamp();
        }
        echo json_encode(
            array(
                'success' => true,
                'message' => $local['dates_available']
            )
        );
        wp_die();
    }
}

add_action('wp_ajax_nopriv_homey_instance_booking', 'homey_instance_booking');
add_action('wp_ajax_homey_instance_booking', 'homey_instance_booking');
if(!function_exists('homey_instance_booking')) {
    function homey_instance_booking() {
        global $current_user;
        $current_user = wp_get_current_user();
        $userID       = $current_user->ID;
        $local = homey_get_localization();
        $allowded_html = array();
        $instace_page_link = homey_get_template_link_2('template/template-instance-booking.php');

        $booking_hide_fields = homey_option('booking_hide_fields');
        $no_login_needed_for_booking = homey_option('no_login_needed_for_booking');


        if ( $no_login_needed_for_booking == 'no' && ( !is_user_logged_in() || $userID === 0 ) ) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['login_for_reservation']
                )
            );
            wp_die();
        }

        if ( empty($instace_page_link) ) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['instance_booking_page']
                )
            );
            wp_die();
        }

        //check security
        $nonce = $_REQUEST['security'];
        if ( ! wp_verify_nonce( $nonce, 'reservation-security-nonce' ) ) {

            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['security_check_text']
                )
            );
            wp_die();
        }

        $listing_id = intval($_POST['listing_id']);
        $listing_owner_id  =  get_post_field( 'post_author', $listing_id );
        $check_in_date     =  wp_kses ( $_POST['check_in_date'], $allowded_html );
        $check_out_date    =  wp_kses ( $_POST['check_out_date'], $allowded_html );
        $guest_message    =  wp_kses ( $_POST['guest_message'], $allowded_html );

        $guests   =  intval($_POST['guests']);
        $adult_guest   =  intval($_POST['adult_guest']);
        $child_guest   =  intval($_POST['child_guest']);

        $extra_options   =  $_POST['extra_options'];

        if($no_login_needed_for_booking == 'no' && $userID == $listing_owner_id) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['own_listing_error']
                )
            );
            wp_die();
        }
        /*
        if(!homey_is_renter()) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['host_user_cannot_book']
                )
            );
            wp_die();
        }
        */

        if(empty($guests) && $booking_hide_fields['guests'] != 1) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['choose_guests']
                )
            );
            wp_die();

        }

        $instance_page = add_query_arg( array(
            'check_in' => $check_in_date,
            'check_out' => $check_out_date,

            'guest' => $guests,
            'adult_guest' => $adult_guest,
            'child_guest' => $child_guest,

            'extra_options' => $extra_options,
            'listing_id' => $listing_id,
            'guest_message' => $guest_message,
        ), $instace_page_link );

        echo json_encode(
            array(
                'success' => true,
                'message' => __('Submitting, Please wait...', 'homey'),
                'instance_url' =>  $instance_page
            )
        );
        wp_die();
    }
}

if(!function_exists('homey_get_extra_expenses')) {
    function homey_get_extra_expenses($reservation_id) {
        $expenses_meta = get_post_meta($reservation_id, 'homey_reservation_extra_expenses', true);
        $expenses_output = '';
        $total_expense = 0;
        $output_array = array();

        if(!empty($expenses_meta)) {
            foreach($expenses_meta as $expense) {
                $expense_name = esc_attr($expense['expense_name']);
                $expense_value = esc_attr($expense['expense_value']);

                $total_expense = $total_expense + $expense_value;
                $expenses_output .= '<li>'.esc_attr($expense_name).' <span>'.homey_formatted_price($expense_value).'</span></li>';
            }

            $output_array['expenses_total_price'] = $total_expense;
            $output_array['expenses_html'] = $expenses_output;
        }

        return $output_array;
    }
}

if(!function_exists('homey_get_extra_discount')) {
    function homey_get_extra_discount($reservation_id) {
        $discount_meta = get_post_meta($reservation_id, 'homey_reservation_discount', true);
        $discount_output = '';
        $total_discount = 0;
        $output_array = array();

        if(!empty($discount_meta)) {
            foreach($discount_meta as $discount) {
                $discount_name = esc_attr($discount['discount_name']);
                $discount_value = esc_attr($discount['discount_value']);

                $total_discount = $total_discount + $discount_value;
                $discount_output .= '<li>'.esc_attr($discount_name).' <span> -'.homey_formatted_price($discount_value).'</span></li>';
            }

            $output_array['discount_total_price'] = $total_discount;
            $output_array['discount_html'] = $discount_output;
        }

        return $output_array;
    }
}

if(!function_exists('homey_get_extra_prices')) {
    function homey_get_extra_prices($extra_options, $no_of_days, $guests) {
        $total_extra_services = 0;
        $extra_prices_output = '';
        $output_array = array();

        if(!empty($extra_options)) {


            foreach ($extra_options as $extra_price) {
                $single_price = explode('|', $extra_price);

                $name = $single_price[0];
                $price = doubleval($single_price[1]);
                $type = $single_price[2];

                if($type == 'single_fee') {
                    $price = $price;

                } elseif($type == 'per_night') {
                    $price = $price*$no_of_days;
                } elseif($type == 'per_guest') {
                    $price = $price*$guests;
                } elseif($type == 'per_night_per_guest') {
                    $price = $price* $no_of_days*$guests;
                }

                $total_extra_services = $total_extra_services + $price;

                $extra_prices_output .= '<li>'.esc_attr($name).' <span>'.homey_formatted_price($price).'</span></li>';
            }

            $output_array['extra_total_price'] = $total_extra_services;
            $output_array['extra_html'] = $extra_prices_output;

            return $output_array;

        }
    }
}


add_action( 'wp_ajax_nopriv_homey_calculate_booking_cost', 'homey_calculate_booking_cost_ajax' );
add_action( 'wp_ajax_homey_calculate_booking_cost', 'homey_calculate_booking_cost_ajax' );

if( !function_exists('homey_calculate_booking_cost_ajax') ) {
    function homey_calculate_booking_cost_ajax() {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        $listing_id     = intval($_POST['listing_id']);
        $check_in_date  = wp_kses ( $_POST['check_in_date'], $allowded_html );
        $check_out_date = wp_kses ( $_POST['check_out_date'], $allowded_html );
        $extra_options = isset($_POST['extra_options']) ? $_POST['extra_options'] : '';
        $guests         = intval($_POST['guests']);

        $booking_type = homey_booking_type_by_id($listing_id);

        if( $booking_type == 'per_week' ) {
            homey_calculate_booking_cost_ajax_weekly($listing_id, $check_in_date, $check_out_date, $guests, $extra_options);
        } else if( $booking_type == 'per_month' ) {
            homey_calculate_booking_cost_ajax_monthly($listing_id, $check_in_date, $check_out_date, $guests, $extra_options);
        } else if( $booking_type == 'per_day_date' ) {
            homey_calculate_booking_cost_ajax_day_date($listing_id, $check_in_date, $check_out_date, $guests, $extra_options);
        } else {
            homey_calculate_booking_cost_ajax_nightly($listing_id, $check_in_date, $check_out_date, $guests, $extra_options);
        }

        wp_die();

    }
}

if( !function_exists('homey_calculate_booking_cost_ajax_weekly') ) {
    function homey_calculate_booking_cost_ajax_weekly($listing_id, $check_in_date, $check_out_date, $guests, $extra_options) {

        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';



        $prices_array = homey_get_weekly_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);

        $price_per_week = homey_formatted_price($prices_array['price_per_week'], true);
        $no_of_weeks = $prices_array['total_weeks_count'];
        $no_of_days = $prices_array['days_count'];
        $weeks_total_price = homey_formatted_price($prices_array['weeks_total_price'], false);

        $cleaning_fee = homey_formatted_price($prices_array['cleaning_fee']);
        $services_fee = $prices_array['services_fee'];
        $taxes = $prices_array['taxes'];
        $taxes_percent = $prices_array['taxes_percent'];
        $city_fee = homey_formatted_price($prices_array['city_fee']);
        $security_deposit = $prices_array['security_deposit'];
        $additional_guests = $prices_array['additional_guests'];
        $additional_guests_price = $prices_array['additional_guests_price'];
        $additional_guests_total_price = $prices_array['additional_guests_total_price'];

        $extra_prices_html = $prices_array['extra_prices_html'];
        $upfront_payment = $prices_array['upfront_payment'];
        $balance = $prices_array['balance'];
        $total_price = $prices_array['total_price'];

        if($no_of_days > 1) {
            $night_label = homey_option('glc_day_nights_label');
        } else {
            $night_label = homey_option('glc_day_night_label');
        }

        if($no_of_weeks > 1) {
            $week_label = homey_option('glc_weeks_label');
        } else {
            $week_label = homey_option('glc_week_label');
        }

        if($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }


        $output = '<div class="payment-list-price-detail clearfix">';
        $output .= '<div class="pull-left">';
        $output .= '<div class="payment-list-price-detail-total-price">'.esc_attr($local['cs_total']).'</div>';
        $output .= '<div class="payment-list-price-detail-note">'.esc_attr($local['cs_tax_fees']).'</div>';
        $output .= '</div>';

        $output .= '<div class="pull-right text-right">';
        $output .= '<div class="payment-list-price-detail-total-price">'.homey_formatted_price($total_price).'</div>';
        $output .= '<a class="payment-list-detail-btn" data-toggle="collapse" data-target=".collapseExample" href="javascript:void(0);" aria-expanded="false" aria-controls="collapseExample">'.esc_attr($local['cs_view_details']).'</a>';
        $output .= '</div>';
        $output .= '</div>';

        $output .= '<div class="collapse collapseExample" id="collapseExample">';
        $output .= '<ul>';


        $output .= '<li class="homey_price_first">'.($price_per_week).' x '.esc_attr($no_of_weeks).' '.esc_attr($week_label);

        if( $no_of_days > 0 ) {
            $output .= ' '.esc_html__('and', 'homey').' '.esc_attr($no_of_days).' '.esc_attr($night_label);
        }

        $output .= '<span>'.$weeks_total_price.'</span></li>';


        if(!empty($additional_guests)) {
            $output .= '<li>'.esc_attr($additional_guests).' '.esc_attr($add_guest_label).' <span>'.homey_formatted_price($additional_guests_total_price).'</span></li>';
        }

        if(!empty($prices_array['cleaning_fee']) && $prices_array['cleaning_fee'] != 0) {
            $output .= '<li>'.esc_attr($local['cs_cleaning_fee']).' <span>'.($cleaning_fee).'</span></li>';
        }

        if(!empty($extra_prices_html)) {
            $output .= $extra_prices_html;
        }

        if(!empty($prices_array['city_fee']) && $prices_array['city_fee'] != 0) {
            $output .= '<li>'.esc_attr($local['cs_city_fee']).' <span>'.($city_fee).'</span></li>';
        }



        if(!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<li>'.esc_attr($local['cs_sec_deposit']).' <span>'.homey_formatted_price($security_deposit).'</span></li>';
        }

        if(!empty($services_fee) && $services_fee != 0 ) {
            $output .= '<li>'.esc_attr($local['cs_services_fee']).' <span>'.homey_formatted_price($services_fee).'</span></li>';
        }

        if(!empty($taxes) && $taxes != 0 ) {
            $output .= '<li>'.esc_attr($local['cs_taxes']).' '.esc_attr($taxes_percent).'% <span>'.homey_formatted_price($taxes).'</span></li>';
        }

        if(!empty($upfront_payment) && $upfront_payment != 0) {
            $output .= '<li class="payment-due">'.esc_attr($local['cs_payment_due']).' <span>'.homey_formatted_price($upfront_payment).'</span></li>';
            $output .= '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="'.$upfront_payment.'">';
        }

        $output .= '</ul>';
        $output .= '</div>';


        $output_escaped = $output;
        print ''.$output_escaped;

        wp_die();

    }
}

if( !function_exists('homey_calculate_booking_cost_ajax_monthly') ) {
    function homey_calculate_booking_cost_ajax_monthly($listing_id, $check_in_date, $check_out_date, $guests, $extra_options) {

        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        $prices_array = homey_get_monthly_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);

        $price_per_month = homey_formatted_price($prices_array['price_per_month'], true);
        $no_of_months = $prices_array['total_months_count'];
        $no_of_days = $prices_array['days_count'];
        $months_total_price = homey_formatted_price($prices_array['months_total_price'], false);

        $cleaning_fee = homey_formatted_price($prices_array['cleaning_fee']);
        $services_fee = $prices_array['services_fee'];
        $taxes = $prices_array['taxes'];
        $taxes_percent = $prices_array['taxes_percent'];
        $city_fee = homey_formatted_price($prices_array['city_fee']);
        $security_deposit = $prices_array['security_deposit'];
        $additional_guests = $prices_array['additional_guests'];
        $additional_guests_price = $prices_array['additional_guests_price'];
        $additional_guests_total_price = $prices_array['additional_guests_total_price'];

        $extra_prices_html = $prices_array['extra_prices_html'];
        $upfront_payment = $prices_array['upfront_payment'];
        $balance = $prices_array['balance'];
        $total_price = $prices_array['total_price'];

        if($no_of_days > 1) {
            $night_label = homey_option('glc_day_nights_label');
        } else {
            $night_label = homey_option('glc_day_night_label');
        }

        if($no_of_months > 1) {
            $month_label = homey_option('glc_months_label');
        } else {
            $month_label = homey_option('glc_month_label');
        }

        if($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }


        $output = '<div class="payment-list-price-detail clearfix">';
        $output .= '<div class="pull-left">';
        $output .= '<div class="payment-list-price-detail-total-price">'.esc_attr($local['cs_total']).'</div>';
        $output .= '<div class="payment-list-price-detail-note">'.esc_attr($local['cs_tax_fees']).'</div>';
        $output .= '</div>';

        $output .= '<div class="pull-right text-right">';
        $output .= '<div class="payment-list-price-detail-total-price">'.homey_formatted_price($total_price).'</div>';
        $output .= '<a class="payment-list-detail-btn" data-toggle="collapse" data-target=".collapseExample" href="javascript:void(0);" aria-expanded="false" aria-controls="collapseExample">'.esc_attr($local['cs_view_details']).'</a>';
        $output .= '</div>';
        $output .= '</div>';

        $output .= '<div class="collapse collapseExample" id="collapseExample">';
        $output .= '<ul>';


        $output .= '<li class="homey_price_first">'.($price_per_month).' x '.esc_attr($no_of_months).' '.esc_attr($month_label);

        if( $no_of_days > 0 ) {
            $output .= ' '.esc_html__('and', 'homey').' '.esc_attr($no_of_days).' '.esc_attr($night_label);
        }

        $output .= '<span>'.$months_total_price.'</span></li>';


        if(!empty($additional_guests)) {
            $output .= '<li>'.esc_attr($additional_guests).' '.esc_attr($add_guest_label).' <span>'.homey_formatted_price($additional_guests_total_price).'</span></li>';
        }

        if(!empty($prices_array['cleaning_fee']) && $prices_array['cleaning_fee'] != 0) {
            $output .= '<li>'.esc_attr($local['cs_cleaning_fee']).' <span>'.($cleaning_fee).'</span></li>';
        }

        if(!empty($extra_prices_html)) {
            $output .= $extra_prices_html;
        }

        if(!empty($prices_array['city_fee']) && $prices_array['city_fee'] != 0) {
            $output .= '<li>'.esc_attr($local['cs_city_fee']).' <span>'.($city_fee).'</span></li>';
        }



        if(!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<li>'.esc_attr($local['cs_sec_deposit']).' <span>'.homey_formatted_price($security_deposit).'</span></li>';
        }

        if(!empty($services_fee) && $services_fee != 0 ) {
            $output .= '<li>'.esc_attr($local['cs_services_fee']).' <span>'.homey_formatted_price($services_fee).'</span></li>';
        }

        if(!empty($taxes) && $taxes != 0 ) {
            $output .= '<li>'.esc_attr($local['cs_taxes']).' '.esc_attr($taxes_percent).'% <span>'.homey_formatted_price($taxes).'</span></li>';
        }

        if(!empty($upfront_payment) && $upfront_payment != 0) {
            $output .= '<li class="payment-due">'.esc_attr($local['cs_payment_due']).' <span>'.homey_formatted_price($upfront_payment).'</span></li>';
            $output .= '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="'.$upfront_payment.'">';
        }

        $output .= '</ul>';
        $output .= '</div>';


        $output_escaped = $output;
        print ''.$output_escaped;

        wp_die();

    }
}

if( !function_exists('homey_calculate_booking_cost_ajax_day_date') ) {
    function homey_calculate_booking_cost_ajax_day_date($listing_id, $check_in_date, $check_out_date, $guests, $extra_options) {

        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        $prices_array = homey_get_day_date_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);

        $price_per_night = homey_formatted_price($prices_array['price_per_day_date'], true);
        $no_of_days = $prices_array['days_count'];

        $nights_total_price = homey_formatted_price($prices_array['nights_total_price'], false);

        $cleaning_fee = homey_formatted_price($prices_array['cleaning_fee']);
        $services_fee = $prices_array['services_fee'];
        $taxes = $prices_array['taxes'];
        $taxes_percent = $prices_array['taxes_percent'];
        $city_fee = homey_formatted_price($prices_array['city_fee']);
        $security_deposit = $prices_array['security_deposit'];
        $additional_guests = $prices_array['additional_guests'];
        $additional_guests_price = $prices_array['additional_guests_price'];
        $additional_guests_total_price = $prices_array['additional_guests_total_price'];

        $booking_has_weekend = $prices_array['booking_has_weekend'];
        $booking_has_custom_pricing = $prices_array['booking_has_custom_pricing'];
        $with_weekend_label = $local['with_weekend_label'];

        $extra_prices_html = $prices_array['extra_prices_html'];
        $upfront_payment = $prices_array['upfront_payment'];
        $balance = $prices_array['balance'];
        $total_price = $prices_array['total_price'];

        if($no_of_days > 1) {
            $day_label = homey_option('glc_day_dates_label');
        } else {
            $day_label = homey_option('glc_day_date_label');
        }

        if($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }


        $output = '<div class="payment-list-price-detail clearfix">';
        $output .= '<div class="pull-left">';
        $output .= '<div class="payment-list-price-detail-total-price">'.esc_attr($local['cs_total']).'</div>';
        $output .= '<div class="payment-list-price-detail-note">'.esc_attr($local['cs_tax_fees']).'</div>';
        $output .= '</div>';

        $output .= '<div class="pull-right text-right">';
        $output .= '<div class="payment-list-price-detail-total-price">'.homey_formatted_price($total_price).'</div>';
        $output .= '<a class="payment-list-detail-btn" data-toggle="collapse" data-target=".collapseExample" href="javascript:void(0);" aria-expanded="false" aria-controls="collapseExample">'.esc_attr($local['cs_view_details']).'</a>';
        $output .= '</div>';
        $output .= '</div>';

        $output .= '<div class="collapse collapseExample" id="collapseExample">';
        $output .= '<ul>';

        if($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
            $output .= '<li class="homey_price_first">'.esc_attr($no_of_days).' '.esc_attr($day_label).' ('.esc_attr($local['with_custom_period_and_weekend_label']).') <span>'.esc_attr($nights_total_price).'</span></li>';

        } elseif($booking_has_weekend == 1) {
            $output .= '<li class="homey_price_first">'.esc_attr($no_of_days).' '.esc_attr($day_label).' ('.esc_attr($with_weekend_label).') <span>'.$nights_total_price.'</span></li>';

        } elseif($booking_has_custom_pricing == 1) {
            $output .= '<li class="homey_price_first">'.esc_attr($no_of_days).' '.esc_attr($day_label).' ('.esc_attr($local['with_custom_period_label']).') <span>'.esc_attr($nights_total_price).'</span></li>';

        } else {
            $output .= '<li class="homey_price_first">'.($price_per_night).' x '.esc_attr($no_of_days).' '.esc_attr($day_label).' <span>'.$nights_total_price.'</span></li>';
        }

        if(!empty($additional_guests)) {
            $output .= '<li>'.esc_attr($additional_guests).' '.esc_attr($add_guest_label).' <span>'.homey_formatted_price($additional_guests_total_price).'</span></li>';
        }

        if(!empty($prices_array['cleaning_fee']) && $prices_array['cleaning_fee'] != 0) {
            $output .= '<li>'.esc_attr($local['cs_cleaning_fee']).' <span>'.($cleaning_fee).'</span></li>';
        }

        if(!empty($extra_prices_html)) {
            $output .= $extra_prices_html;
        }

        if(!empty($prices_array['city_fee']) && $prices_array['city_fee'] != 0) {
            $output .= '<li>'.esc_attr($local['cs_city_fee']).' <span>'.($city_fee).'</span></li>';
        }



        if(!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<li>'.esc_attr($local['cs_sec_deposit']).' <span>'.homey_formatted_price($security_deposit).'</span></li>';
        }

        if(!empty($services_fee) && $services_fee != 0 ) {
            $output .= '<li>'.esc_attr($local['cs_services_fee']).' <span>'.homey_formatted_price($services_fee).'</span></li>';
        }

        if(!empty($taxes) && $taxes != 0 ) {
            $output .= '<li>'.esc_attr($local['cs_taxes']).' '.esc_attr($taxes_percent).'% <span>'.homey_formatted_price($taxes).'</span></li>';
        }

        if(!empty($upfront_payment) && $upfront_payment != 0) {
            $output .= '<li class="payment-due">'.esc_attr($local['cs_payment_due']).' <span>'.homey_formatted_price($upfront_payment).'</span></li>';
            $output .= '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="'.$upfront_payment.'">';
        }

        $output .= '</ul>';
        $output .= '</div>';

        // This variable has been safely escaped in same file: Line: 1071 - 1128
        $output_escaped = $output;
        print ''.$output_escaped;

        wp_die();

    }
}

if( !function_exists('homey_calculate_booking_cost_ajax_nightly') ) {
    function homey_calculate_booking_cost_ajax_nightly($listing_id, $check_in_date, $check_out_date, $guests, $extra_options) {

        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        $prices_array = homey_get_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);

        $price_per_night = homey_formatted_price($prices_array['price_per_night'], true);
        $no_of_days = $prices_array['days_count'];

        $nights_total_price = homey_formatted_price($prices_array['nights_total_price'], false);

        $cleaning_fee = homey_formatted_price($prices_array['cleaning_fee']);
        $services_fee = $prices_array['services_fee'];
        $taxes = $prices_array['taxes'];
        $taxes_percent = $prices_array['taxes_percent'];
        $city_fee = homey_formatted_price($prices_array['city_fee']);
        $security_deposit = $prices_array['security_deposit'];
        $additional_guests = $prices_array['additional_guests'];
        $additional_guests_price = $prices_array['additional_guests_price'];
        $additional_guests_total_price = $prices_array['additional_guests_total_price'];

        $booking_has_weekend = $prices_array['booking_has_weekend'];
        $booking_has_custom_pricing = $prices_array['booking_has_custom_pricing'];
        $with_weekend_label = $local['with_weekend_label'];

        $extra_prices_html = $prices_array['extra_prices_html'];
        $upfront_payment = $prices_array['upfront_payment'];
        $balance = $prices_array['balance'];
        $total_price = $prices_array['total_price'];

        if($no_of_days > 1) {
            $night_label = homey_option('glc_day_nights_label');
        } else {
            $night_label = homey_option('glc_day_night_label');
        }

        if($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }


        $output = '<div class="payment-list-price-detail clearfix">';
        $output .= '<div class="pull-left">';
        $output .= '<div class="payment-list-price-detail-total-price">'.esc_attr($local['cs_total']).'</div>';
        $output .= '<div class="payment-list-price-detail-note">'.esc_attr($local['cs_tax_fees']).'</div>';
        $output .= '</div>';

        $output .= '<div class="pull-right text-right">';
        $output .= '<div class="payment-list-price-detail-total-price">'.homey_formatted_price($total_price).'</div>';
        $output .= '<a class="payment-list-detail-btn" data-toggle="collapse" data-target=".collapseExample" href="javascript:void(0);" aria-expanded="false" aria-controls="collapseExample">'.esc_attr($local['cs_view_details']).'</a>';
        $output .= '</div>';
        $output .= '</div>';

        $output .= '<div class="collapse collapseExample" id="collapseExample">';
        $output .= '<ul>';

        if($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
            $output .= '<li class="homey_price_first">'.esc_attr($no_of_days).' '.esc_attr($night_label).' ('.esc_attr($local['with_custom_period_and_weekend_label']).') <span>'.esc_attr($nights_total_price).'</span></li>';

        } elseif($booking_has_weekend == 1) {
            $output .= '<li class="homey_price_first">'.esc_attr($no_of_days).' '.esc_attr($night_label).' ('.esc_attr($with_weekend_label).') <span>'.$nights_total_price.'</span></li>';

        } elseif($booking_has_custom_pricing == 1) {
            $output .= '<li class="homey_price_first">'.esc_attr($no_of_days).' '.esc_attr($night_label).' ('.esc_attr($local['with_custom_period_label']).') <span>'.esc_attr($nights_total_price).'</span></li>';

        } else {
            $output .= '<li class="homey_price_first">'.($price_per_night).' x '.esc_attr($no_of_days).' '.esc_attr($night_label).' <span>'.$nights_total_price.'</span></li>';
        }

        if(!empty($additional_guests)) {
            $output .= '<li>'.esc_attr($additional_guests).' '.esc_attr($add_guest_label).' <span>'.homey_formatted_price($additional_guests_total_price).'</span></li>';
        }

        if(!empty($prices_array['cleaning_fee']) && $prices_array['cleaning_fee'] != 0) {
            $output .= '<li>'.esc_attr($local['cs_cleaning_fee']).' <span>'.($cleaning_fee).'</span></li>';
        }

        if(!empty($extra_prices_html)) {
            $output .= $extra_prices_html;
        }

        if(!empty($prices_array['city_fee']) && $prices_array['city_fee'] != 0) {
            $output .= '<li>'.esc_attr($local['cs_city_fee']).' <span>'.($city_fee).'</span></li>';
        }



        if(!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<li>'.esc_attr($local['cs_sec_deposit']).' <span>'.homey_formatted_price($security_deposit).'</span></li>';
        }

        if(!empty($services_fee) && $services_fee != 0 ) {
            $output .= '<li>'.esc_attr($local['cs_services_fee']).' <span>'.homey_formatted_price($services_fee).'</span></li>';
        }

        if(!empty($taxes) && $taxes != 0 ) {
            $output .= '<li>'.esc_attr($local['cs_taxes']).' '.esc_attr($taxes_percent).'% <span>'.homey_formatted_price($taxes).'</span></li>';
        }

        if(!empty($upfront_payment) && $upfront_payment != 0) {
            $output .= '<li class="payment-due">'.esc_attr($local['cs_payment_due']).' <span>'.homey_formatted_price($upfront_payment).'</span></li>';
            $output .= '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="'.$upfront_payment.'">';
        }

        $output .= '</ul>';
        $output .= '</div>';

        // Adding dynamic nightly price:
        $dynamic_nightly_price = '<price>'. $prices_array['nights_total_price'] / $prices_array['days_count'] .'</price>';
        $output .= $dynamic_nightly_price;

        // This variable has been safely escaped in same file: Line: 1071 - 1128
        $output_escaped = $output;
        print ''.$output_escaped;

        wp_die();

    }
}


if( !function_exists('homey_calculate_booking_cost_ajax_1_5_3') ) {
    function homey_calculate_booking_cost_ajax_1_5_3() {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        $listing_id     = intval($_POST['listing_id']);
        $check_in_date  = wp_kses ( $_POST['check_in_date'], $allowded_html );
        $check_out_date = wp_kses ( $_POST['check_out_date'], $allowded_html );
        $extra_options = $_POST['extra_options'];
        $guests         = intval($_POST['guests']);

        $prices_array = homey_get_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);

        $price_per_night = homey_formatted_price($prices_array['price_per_night'], true);
        $no_of_days = $prices_array['days_count'];

        $nights_total_price = homey_formatted_price($prices_array['nights_total_price'], false);

        $cleaning_fee = homey_formatted_price($prices_array['cleaning_fee']);
        $services_fee = $prices_array['services_fee'];
        $taxes = $prices_array['taxes'];
        $taxes_percent = $prices_array['taxes_percent'];
        $city_fee = homey_formatted_price($prices_array['city_fee']);
        $security_deposit = $prices_array['security_deposit'];
        $additional_guests = $prices_array['additional_guests'];
        $additional_guests_price = $prices_array['additional_guests_price'];
        $additional_guests_total_price = $prices_array['additional_guests_total_price'];

        $booking_has_weekend = $prices_array['booking_has_weekend'];
        $booking_has_custom_pricing = $prices_array['booking_has_custom_pricing'];
        $with_weekend_label = $local['with_weekend_label'];

        $extra_prices_html = $prices_array['extra_prices_html'];
        $upfront_payment = $prices_array['upfront_payment'];
        $balance = $prices_array['balance'];
        $total_price = $prices_array['total_price'];

        if($no_of_days > 1) {
            $night_label = homey_option('glc_day_nights_label');
        } else {
            $night_label = homey_option('glc_day_night_label');
        }

        if($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }


        $output = '<div class="payment-list-price-detail clearfix">';
        $output .= '<div class="pull-left">';
        $output .= '<div class="payment-list-price-detail-total-price">'.esc_attr($local['cs_total']).'</div>';
        $output .= '<div class="payment-list-price-detail-note">'.esc_attr($local['cs_tax_fees']).'</div>';
        $output .= '</div>';

        $output .= '<div class="pull-right text-right">';
        $output .= '<div class="payment-list-price-detail-total-price">'.homey_formatted_price($total_price).'</div>';
        $output .= '<a class="payment-list-detail-btn" data-toggle="collapse" data-target=".collapseExample" href="javascript:void(0);" aria-expanded="false" aria-controls="collapseExample">'.esc_attr($local['cs_view_details']).'</a>';
        $output .= '</div>';
        $output .= '</div>';

        $output .= '<div class="collapse collapseExample" id="collapseExample">';
        $output .= '<ul>';

        if($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
            $output .= '<li class="homey_price_first">'.esc_attr($no_of_days).' '.esc_attr($night_label).' ('.esc_attr($local['with_custom_period_and_weekend_label']).') <span>'.esc_attr($nights_total_price).'</span></li>';

        } elseif($booking_has_weekend == 1) {
            $output .= '<li class="homey_price_first">'.esc_attr($no_of_days).' '.esc_attr($night_label).' ('.esc_attr($with_weekend_label).') <span>'.$nights_total_price.'</span></li>';

        } elseif($booking_has_custom_pricing == 1) {
            $output .= '<li class="homey_price_first">'.esc_attr($no_of_days).' '.esc_attr($night_label).' ('.esc_attr($local['with_custom_period_label']).') <span>'.esc_attr($nights_total_price).'</span></li>';

        } else {
            $output .= '<li class="homey_price_first">'.($price_per_night).' x '.esc_attr($no_of_days).' '.esc_attr($night_label).' <span>'.$nights_total_price.'</span></li>';
        }

        if(!empty($additional_guests)) {
            $output .= '<li>'.esc_attr($additional_guests).' '.esc_attr($add_guest_label).' <span>'.homey_formatted_price($additional_guests_total_price).'</span></li>';
        }

        if(!empty($prices_array['cleaning_fee']) && $prices_array['cleaning_fee'] != 0) {
            $output .= '<li>'.esc_attr($local['cs_cleaning_fee']).' <span>'.($cleaning_fee).'</span></li>';
        }

        if(!empty($extra_prices_html)) {
            $output .= $extra_prices_html;
        }

        if(!empty($prices_array['city_fee']) && $prices_array['city_fee'] != 0) {
            $output .= '<li>'.esc_attr($local['cs_city_fee']).' <span>'.($city_fee).'</span></li>';
        }



        if(!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<li>'.esc_attr($local['cs_sec_deposit']).' <span>'.homey_formatted_price($security_deposit).'</span></li>';
        }

        if(!empty($services_fee) && $services_fee != 0 ) {
            $output .= '<li>'.esc_attr($local['cs_services_fee']).' <span>'.homey_formatted_price($services_fee).'</span></li>';
        }

        if(!empty($taxes) && $taxes != 0 ) {
            $output .= '<li>'.esc_attr($local['cs_taxes']).' '.esc_attr($taxes_percent).'% <span>'.homey_formatted_price($taxes).'</span></li>';
        }

        if(!empty($upfront_payment) && $upfront_payment != 0) {
            $output .= '<li class="payment-due">'.esc_attr($local['cs_payment_due']).' <span>'.homey_formatted_price($upfront_payment).'</span></li>';
            $output .= '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="'.$upfront_payment.'">';
        }

        $output .= '</ul>';
        $output .= '</div>';

        // This variable has been safely escaped in same file: Line: 1071 - 1128
        $output_escaped = $output;
        print ''.$output_escaped;

        wp_die();

    }
}


if( !function_exists('homey_calculate_booking_cost_instance_monthly') ) {
    function homey_calculate_booking_cost_instance_monthly() {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        $listing_id     = intval($_GET['listing_id']);
        $check_in_date  = wp_kses ( $_GET['check_in'], $allowded_html );
        $check_out_date = wp_kses ( $_GET['check_out'], $allowded_html );
        $guests         = intval($_GET['guest']);
        $extra_options  = isset($_GET['extra_options']) ? $_GET['extra_options'] : '';

        $prices_array = homey_get_monthly_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);

        $price_per_month = homey_formatted_price($prices_array['price_per_month'], true);
        $no_of_months = $prices_array['total_months_count'];
        $no_of_days = $prices_array['days_count'];
        $months_total_price = homey_formatted_price($prices_array['months_total_price'], false);

        $cleaning_fee = homey_formatted_price($prices_array['cleaning_fee']);
        $services_fee = $prices_array['services_fee'];
        $taxes = $prices_array['taxes'];
        $taxes_percent = $prices_array['taxes_percent'];
        $city_fee = homey_formatted_price($prices_array['city_fee']);
        $security_deposit = $prices_array['security_deposit'];
        $additional_guests = $prices_array['additional_guests'];
        $additional_guests_price = $prices_array['additional_guests_price'];
        $additional_guests_total_price = $prices_array['additional_guests_total_price'];

        $extra_prices_html = $prices_array['extra_prices_html'];
        $upfront_payment = $prices_array['upfront_payment'];
        $balance = $prices_array['balance'];
        $total_price = $prices_array['total_price'];

        if($no_of_days > 1) {
            $night_label = homey_option('glc_day_nights_label');
        } else {
            $night_label = homey_option('glc_day_night_label');
        }

        if($no_of_months > 1) {
            $month_label = homey_option('glc_months_label');
        } else {
            $month_label = homey_option('glc_month_label');
        }

        if($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }

        $output = '<div class="payment-list-price-detail clearfix">';
        $output .= '<div class="pull-left">';
        $output .= '<div class="payment-list-price-detail-total-price">'.$local['cs_total'].'</div>';
        $output .= '<div class="payment-list-price-detail-note">'.$local['cs_tax_fees'].'</div>';
        $output .= '</div>';

        $output .= '<div class="pull-right text-right">';
        $output .= '<div class="payment-list-price-detail-total-price">'.homey_formatted_price($total_price).'</div>';
        $output .= '<a class="payment-list-detail-btn" data-toggle="collapse" data-target=".collapseExample" href="javascript:void(0);" aria-expanded="false" aria-controls="collapseExample">'.$local['cs_view_details'].'</a>';
        $output .= '</div>';
        $output .= '</div>';

        $output .= '<div class="collapse collapseExample" id="collapseExample">';
        $output .= '<ul>';

        $output .= '<li class="homey_price_first">'.($price_per_month).' x '.esc_attr($no_of_months).' '.esc_attr($month_label);

        if( $no_of_days > 0 ) {
            $output .= ' '.esc_html__('and', 'homey').' '.esc_attr($no_of_days).' '.esc_attr($night_label);
        }

        $output .= '<span>'.$months_total_price.'</span></li>';

        if(!empty($additional_guests)) {
            $output .= '<li>'.$additional_guests.' '.$add_guest_label.' <span>'.homey_formatted_price($additional_guests_total_price).'</span></li>';
        }

        if(!empty($prices_array['cleaning_fee']) && $prices_array['cleaning_fee'] != 0) {
            $output .= '<li>'.$local['cs_cleaning_fee'].' <span>'.$cleaning_fee.'</span></li>';
        }

        if(!empty($extra_prices_html)) {
            $output .= $extra_prices_html;
        }

        if(!empty($prices_array['city_fee']) && $prices_array['city_fee'] != 0) {
            $output .= '<li>'.$local['cs_city_fee'].' <span>'.$city_fee.'</span></li>';
        }

        if(!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<li>'.$local['cs_sec_deposit'].' <span>'.homey_formatted_price($security_deposit).'</span></li>';
        }

        if(!empty($services_fee) && $services_fee != 0 ) {
            $output .= '<li>'.$local['cs_services_fee'].' <span>'.homey_formatted_price($services_fee).'</span></li>';
        }

        if(!empty($taxes) && $taxes != 0 ) {
            $output .= '<li>'.$local['cs_taxes'].' '.$taxes_percent.'% <span>'.homey_formatted_price($taxes).'</span></li>';
        }

        if(!empty($upfront_payment) && $upfront_payment != 0) {
            $output .= '<li class="payment-due">'.$local['cs_payment_due'].' <span>'.homey_formatted_price($upfront_payment).'</span></li>';
            $output .= '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="'.$upfront_payment.'">';
        }

        if(!empty($balance) && $balance != 0) {
            $output .= '<li><i class="fa fa-info-circle"></i> '.$local['cs_pay_rest_1'].' '.homey_formatted_price($balance).' '.$local['cs_pay_rest_2'].'</li>';
        }
        $output .= '</ul>';
        $output .= '</div>';

        return $output;
    }
}

if( !function_exists('homey_calculate_booking_cost_instance_weekly') ) {
    function homey_calculate_booking_cost_instance_weekly() {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        $listing_id     = intval($_GET['listing_id']);
        $check_in_date  = wp_kses ( $_GET['check_in'], $allowded_html );
        $check_out_date = wp_kses ( $_GET['check_out'], $allowded_html );
        $guests         = intval($_GET['guest']);
        $extra_options  = isset($_GET['extra_options']) ? $_GET['extra_options'] : '';

        $prices_array = homey_get_weekly_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);

        $price_per_week = homey_formatted_price($prices_array['price_per_week'], true);
        $no_of_weeks = $prices_array['total_weeks_count'];
        $no_of_days = $prices_array['days_count'];
        $weeks_total_price = homey_formatted_price($prices_array['weeks_total_price'], false);

        $cleaning_fee = homey_formatted_price($prices_array['cleaning_fee']);
        $services_fee = $prices_array['services_fee'];
        $taxes = $prices_array['taxes'];
        $taxes_percent = $prices_array['taxes_percent'];
        $city_fee = homey_formatted_price($prices_array['city_fee']);
        $security_deposit = $prices_array['security_deposit'];
        $additional_guests = $prices_array['additional_guests'];
        $additional_guests_price = $prices_array['additional_guests_price'];
        $additional_guests_total_price = $prices_array['additional_guests_total_price'];

        $extra_prices_html = $prices_array['extra_prices_html'];
        $upfront_payment = $prices_array['upfront_payment'];
        $balance = $prices_array['balance'];
        $total_price = $prices_array['total_price'];

        if($no_of_days > 1) {
            $night_label = homey_option('glc_day_nights_label');
        } else {
            $night_label = homey_option('glc_day_night_label');
        }

        if($no_of_weeks > 1) {
            $week_label = homey_option('glc_weeks_label');
        } else {
            $week_label = homey_option('glc_week_label');
        }

        if($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }

        $output = '<div class="payment-list-price-detail clearfix">';
        $output .= '<div class="pull-left">';
        $output .= '<div class="payment-list-price-detail-total-price">'.$local['cs_total'].'</div>';
        $output .= '<div class="payment-list-price-detail-note">'.$local['cs_tax_fees'].'</div>';
        $output .= '</div>';

        $output .= '<div class="pull-right text-right">';
        $output .= '<div class="payment-list-price-detail-total-price">'.homey_formatted_price($total_price).'</div>';
        $output .= '<a class="payment-list-detail-btn" data-toggle="collapse" data-target=".collapseExample" href="javascript:void(0);" aria-expanded="false" aria-controls="collapseExample">'.$local['cs_view_details'].'</a>';
        $output .= '</div>';
        $output .= '</div>';

        $output .= '<div class="collapse collapseExample" id="collapseExample">';
        $output .= '<ul>';

        $output .= '<li class="homey_price_first">'.($price_per_week).' x '.esc_attr($no_of_weeks).' '.esc_attr($week_label);

        if( $no_of_days > 0 ) {
            $output .= ' '.esc_html__('and', 'homey').' '.esc_attr($no_of_days).' '.esc_attr($night_label);
        }

        $output .= '<span>'.$weeks_total_price.'</span></li>';

        if(!empty($additional_guests)) {
            $output .= '<li>'.$additional_guests.' '.$add_guest_label.' <span>'.homey_formatted_price($additional_guests_total_price).'</span></li>';
        }

        if(!empty($prices_array['cleaning_fee']) && $prices_array['cleaning_fee'] != 0) {
            $output .= '<li>'.$local['cs_cleaning_fee'].' <span>'.$cleaning_fee.'</span></li>';
        }

        if(!empty($extra_prices_html)) {
            $output .= $extra_prices_html;
        }

        if(!empty($prices_array['city_fee']) && $prices_array['city_fee'] != 0) {
            $output .= '<li>'.$local['cs_city_fee'].' <span>'.$city_fee.'</span></li>';
        }

        if(!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<li>'.$local['cs_sec_deposit'].' <span>'.homey_formatted_price($security_deposit).'</span></li>';
        }

        if(!empty($services_fee) && $services_fee != 0 ) {
            $output .= '<li>'.$local['cs_services_fee'].' <span>'.homey_formatted_price($services_fee).'</span></li>';
        }

        if(!empty($taxes) && $taxes != 0 ) {
            $output .= '<li>'.$local['cs_taxes'].' '.$taxes_percent.'% <span>'.homey_formatted_price($taxes).'</span></li>';
        }

        if(!empty($upfront_payment) && $upfront_payment != 0) {
            $output .= '<li class="payment-due">'.$local['cs_payment_due'].' <span>'.homey_formatted_price($upfront_payment).'</span></li>';
            $output .= '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="'.$upfront_payment.'">';
        }

        if(!empty($balance) && $balance != 0) {
            $output .= '<li><i class="fa fa-info-circle"></i> '.$local['cs_pay_rest_1'].' '.homey_formatted_price($balance).' '.$local['cs_pay_rest_2'].'</li>';
        }
        $output .= '</ul>';
        $output .= '</div>';

        return $output;
    }
}

if( !function_exists('homey_calculate_booking_cost_instance_daily') ) {
    function homey_calculate_booking_cost_instance_daily() {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        $listing_id     = intval($_GET['listing_id']);
        $check_in_date  = wp_kses ( $_GET['check_in'], $allowded_html );
        $check_out_date = wp_kses ( $_GET['check_out'], $allowded_html );
        $guests         = intval($_GET['guest']);
        $extra_options  = isset($_GET['extra_options']) ? $_GET['extra_options'] : '';

        $prices_array = homey_get_day_date_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);

        $price_per_day_date = homey_formatted_price($prices_array['price_per_day_date'], true);
        $no_of_days = $prices_array['days_count'];

        $nights_total_price = homey_formatted_price($prices_array['nights_total_price'], false);

        $cleaning_fee = homey_formatted_price($prices_array['cleaning_fee']);
        $services_fee = $prices_array['services_fee'];
        $taxes = $prices_array['taxes'];
        $taxes_percent = $prices_array['taxes_percent'];
        $city_fee = homey_formatted_price($prices_array['city_fee']);
        $security_deposit = $prices_array['security_deposit'];
        $additional_guests = $prices_array['additional_guests'];
        $additional_guests_price = $prices_array['additional_guests_price'];
        $additional_guests_total_price = $prices_array['additional_guests_total_price'];

        $extra_prices_html = $prices_array['extra_prices_html'];

        $upfront_payment = $prices_array['upfront_payment'];
        $balance = $prices_array['balance'];
        $total_price = $prices_array['total_price'];

        $booking_has_weekend = $prices_array['booking_has_weekend'];
        $booking_has_custom_pricing = $prices_array['booking_has_custom_pricing'];
        $with_weekend_label = $local['with_weekend_label'];

        if($no_of_days > 1) {
            $day_date_label = homey_option('glc_day_dates_label');
        } else {
            $day_date_label = homey_option('glc_day_date_label');
        }

        if($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }

        $output = '<div class="payment-list-price-detail clearfix">';
        $output .= '<div class="pull-left">';
        $output .= '<div class="payment-list-price-detail-total-price">'.$local['cs_total'].'</div>';
        $output .= '<div class="payment-list-price-detail-note">'.$local['cs_tax_fees'].'</div>';
        $output .= '</div>';

        $output .= '<div class="pull-right text-right">';
        $output .= '<div class="payment-list-price-detail-total-price">'.homey_formatted_price($total_price).'</div>';
        $output .= '<a class="payment-list-detail-btn" data-toggle="collapse" data-target=".collapseExample" href="javascript:void(0);" aria-expanded="false" aria-controls="collapseExample">'.$local['cs_view_details'].'</a>';
        $output .= '</div>';
        $output .= '</div>';

        $output .= '<div class="collapse collapseExample" id="collapseExample">';
        $output .= '<ul>';

        if($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
            $output .= '<li>'.$no_of_days.' '.$day_date_label.' ('.$local['with_custom_period_and_weekend_label'].') <span>'.$nights_total_price.'</span></li>';

        } elseif($booking_has_weekend == 1) {
            $output .= '<li>'.$no_of_days.' '.$day_date_label.' ('.$with_weekend_label.') <span>'.$nights_total_price.'</span></li>';

        } elseif($booking_has_custom_pricing == 1) {
            $output .= '<li>'.$no_of_days.' '.$day_date_label.' ('.$local['with_custom_period_label'].') <span>'.$nights_total_price.'</span></li>';

        } else {
            $output .= '<li>'.$price_per_day_date.' x '.$no_of_days.' '.$day_date_label.' <span>'.$nights_total_price.'</span></li>';
        }

        if(!empty($additional_guests)) {
            $output .= '<li>'.$additional_guests.' '.$add_guest_label.' <span>'.homey_formatted_price($additional_guests_total_price).'</span></li>';
        }

        if(!empty($prices_array['cleaning_fee']) && $prices_array['cleaning_fee'] != 0) {
            $output .= '<li>'.$local['cs_cleaning_fee'].' <span>'.$cleaning_fee.'</span></li>';
        }

        if(!empty($extra_prices_html)) {
            $output .= $extra_prices_html;
        }

        if(!empty($prices_array['city_fee']) && $prices_array['city_fee'] != 0) {
            $output .= '<li>'.$local['cs_city_fee'].' <span>'.$city_fee.'</span></li>';
        }

        if(!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<li>'.$local['cs_sec_deposit'].' <span>'.homey_formatted_price($security_deposit).'</span></li>';
        }

        if(!empty($services_fee) && $services_fee != 0 ) {
            $output .= '<li>'.$local['cs_services_fee'].' <span>'.homey_formatted_price($services_fee).'</span></li>';
        }

        if(!empty($taxes) && $taxes != 0 ) {
            $output .= '<li>'.$local['cs_taxes'].' '.$taxes_percent.'% <span>'.homey_formatted_price($taxes).'</span></li>';
        }

        if(!empty($upfront_payment) && $upfront_payment != 0) {
            $output .= '<li class="payment-due">'.$local['cs_payment_due'].' <span>'.homey_formatted_price($upfront_payment).'</span></li>';
            $output .= '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="'.$upfront_payment.'">';
        }

        if(!empty($balance) && $balance != 0) {
            $output .= '<li><i class="fa fa-info-circle"></i> '.$local['cs_pay_rest_1'].' '.homey_formatted_price($balance).' '.$local['cs_pay_rest_2'].'</li>';
        }
        $output .= '</ul>';
        $output .= '</div>';

        return $output;
    }
}

if( !function_exists('homey_calculate_booking_cost_instance') ) {
    function homey_calculate_booking_cost_instance() {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        $listing_id     = intval($_GET['listing_id']);
        $check_in_date  = wp_kses ( $_GET['check_in'], $allowded_html );
        $check_out_date = wp_kses ( $_GET['check_out'], $allowded_html );
        $guests         = intval($_GET['guest']);
        $extra_options  = isset($_GET['extra_options']) ? $_GET['extra_options'] : '';

        $prices_array = homey_get_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);

        $price_per_night = homey_formatted_price($prices_array['price_per_night'], true);
        $no_of_days = $prices_array['days_count'];

        $nights_total_price = homey_formatted_price($prices_array['nights_total_price'], false);

        $cleaning_fee = homey_formatted_price($prices_array['cleaning_fee']);
        $services_fee = $prices_array['services_fee'];
        $taxes = $prices_array['taxes'];
        $taxes_percent = $prices_array['taxes_percent'];
        $city_fee = homey_formatted_price($prices_array['city_fee']);
        $security_deposit = $prices_array['security_deposit'];
        $additional_guests = $prices_array['additional_guests'];
        $additional_guests_price = $prices_array['additional_guests_price'];
        $additional_guests_total_price = $prices_array['additional_guests_total_price'];

        $extra_prices_html = $prices_array['extra_prices_html'];

        $upfront_payment = $prices_array['upfront_payment'];
        $balance = $prices_array['balance'];
        $total_price = $prices_array['total_price'];

        $booking_has_weekend = $prices_array['booking_has_weekend'];
        $booking_has_custom_pricing = $prices_array['booking_has_custom_pricing'];
        $with_weekend_label = $local['with_weekend_label'];

        if($no_of_days > 1) {
            $night_label = homey_option('glc_day_nights_label');
        } else {
            $night_label = homey_option('glc_day_night_label');
        }

        if($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }

        $output = '<div class="payment-list-price-detail clearfix">';
        $output .= '<div class="pull-left">';
        $output .= '<div class="payment-list-price-detail-total-price">'.$local['cs_total'].'</div>';
        $output .= '<div class="payment-list-price-detail-note">'.$local['cs_tax_fees'].'</div>';
        $output .= '</div>';

        $output .= '<div class="pull-right text-right">';
        $output .= '<div class="payment-list-price-detail-total-price">'.homey_formatted_price($total_price).'</div>';
        $output .= '<a class="payment-list-detail-btn" data-toggle="collapse" data-target=".collapseExample" href="javascript:void(0);" aria-expanded="false" aria-controls="collapseExample">'.$local['cs_view_details'].'</a>';
        $output .= '</div>';
        $output .= '</div>';

        $output .= '<div class="collapse collapseExample" id="collapseExample">';
        $output .= '<ul>';

        if($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
            $output .= '<li>'.$no_of_days.' '.$night_label.' ('.$local['with_custom_period_and_weekend_label'].') <span>'.$nights_total_price.'</span></li>';

        } elseif($booking_has_weekend == 1) {
            $output .= '<li>'.$no_of_days.' '.$night_label.' ('.$with_weekend_label.') <span>'.$nights_total_price.'</span></li>';

        } elseif($booking_has_custom_pricing == 1) {
            $output .= '<li>'.$no_of_days.' '.$night_label.' ('.$local['with_custom_period_label'].') <span>'.$nights_total_price.'</span></li>';

        } else {
            $output .= '<li>'.$price_per_night.' x '.$no_of_days.' '.$night_label.' <span>'.$nights_total_price.'</span></li>';
        }

        if(!empty($additional_guests)) {
            $output .= '<li>'.$additional_guests.' '.$add_guest_label.' <span>'.homey_formatted_price($additional_guests_total_price).'</span></li>';
        }

        if(!empty($prices_array['cleaning_fee']) && $prices_array['cleaning_fee'] != 0) {
            $output .= '<li>'.$local['cs_cleaning_fee'].' <span>'.$cleaning_fee.'</span></li>';
        }

        if(!empty($extra_prices_html)) {
            $output .= $extra_prices_html;
        }

        if(!empty($prices_array['city_fee']) && $prices_array['city_fee'] != 0) {
            $output .= '<li>'.$local['cs_city_fee'].' <span>'.$city_fee.'</span></li>';
        }

        if(!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<li>'.$local['cs_sec_deposit'].' <span>'.homey_formatted_price($security_deposit).'</span></li>';
        }

        if(!empty($services_fee) && $services_fee != 0 ) {
            $output .= '<li>'.$local['cs_services_fee'].' <span>'.homey_formatted_price($services_fee).'</span></li>';
        }

        if(!empty($taxes) && $taxes != 0 ) {
            $output .= '<li>'.$local['cs_taxes'].' '.$taxes_percent.'% <span>'.homey_formatted_price($taxes).'</span></li>';
        }

        if(!empty($upfront_payment) && $upfront_payment != 0) {
            $output .= '<li class="payment-due">'.$local['cs_payment_due'].' <span>'.homey_formatted_price($upfront_payment).'</span></li>';
            $output .= '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="'.$upfront_payment.'">';
        }

        if(!empty($balance) && $balance != 0) {
            $output .= '<li><i class="fa fa-info-circle"></i> '.$local['cs_pay_rest_1'].' '.homey_formatted_price($balance).' '.$local['cs_pay_rest_2'].'</li>';
        }
        $output .= '</ul>';
        $output .= '</div>';

        return $output;
    }
}

if( !function_exists('homey_calculate_booking_cost') ) {
    function homey_calculate_booking_cost($reservation_id, $collapse = false) {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        if(empty($reservation_id)) {
            return;
        }
        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);
        $extra_options = get_post_meta($reservation_id, 'extra_options', true);

        $listing_id     = intval($reservation_meta['listing_id']);
        $check_in_date  = wp_kses ( $reservation_meta['check_in_date'], $allowded_html );
        $check_out_date = wp_kses ( $reservation_meta['check_out_date'], $allowded_html );
        $guests         = intval($reservation_meta['guests']);

        $prices_array = homey_get_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);

        $price_per_night = homey_formatted_price($prices_array['price_per_night'], true);
        $no_of_days = $prices_array['days_count'];

        $nights_total_price = homey_formatted_price($prices_array['nights_total_price'], false);

        $cleaning_fee = homey_formatted_price($prices_array['cleaning_fee']);
        $services_fee = $prices_array['services_fee'];
        $taxes = $prices_array['taxes'];
        $taxes_percent = $prices_array['taxes_percent'];
        $city_fee = homey_formatted_price($prices_array['city_fee']);
        $security_deposit = $prices_array['security_deposit'];
        $additional_guests = $prices_array['additional_guests'];
        $additional_guests_price = $prices_array['additional_guests_price'];
        $additional_guests_total_price = $prices_array['additional_guests_total_price'];

        $upfront_payment = $prices_array['upfront_payment'];
        $balance = $prices_array['balance'];
        $total_price = $prices_array['total_price'];

        $booking_has_weekend = $prices_array['booking_has_weekend'];
        $booking_has_custom_pricing = $prices_array['booking_has_custom_pricing'];
        $with_weekend_label = $local['with_weekend_label'];

        if($no_of_days > 1) {
            $night_label = homey_option('glc_day_nights_label');
        } else {
            $night_label = homey_option('glc_day_night_label');
        }

        if($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }

        $start_div = '<div class="payment-list">';

        if($collapse) {
            $output = '<div class="payment-list-price-detail clearfix">';
            $output .= '<div class="pull-left">';
            $output .= '<div class="payment-list-price-detail-total-price">'.$local['cs_total'].'</div>';
            $output .= '<div class="payment-list-price-detail-note">'.$local['cs_tax_fees'].'</div>';
            $output .= '</div>';

            $output .= '<div class="pull-right text-right">';
            $output .= '<div class="payment-list-price-detail-total-price">'.homey_formatted_price($total_price).'</div>';
            $output .= '<a class="payment-list-detail-btn" data-toggle="collapse" data-target=".collapseExample" href="javascript:void(0);" aria-expanded="false" aria-controls="collapseExample">'.$local['cs_view_details'].'</a>';
            $output .= '</div>';
            $output .= '</div>';

            $start_div  = '<div class="collapse collapseExample" id="collapseExample">';
        }


        $output .= $start_div;
        $output .= '<ul>';

        if($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
            $output .= '<li>'.$no_of_days.' '.$night_label.' ('.$local['with_custom_period_and_weekend_label'].') <span>'.$nights_total_price.'</span></li>';

        } elseif($booking_has_weekend == 1) {
            $output .= '<li>'.$no_of_days.' '.$night_label.' ('.$with_weekend_label.') <span>'.$nights_total_price.'</span></li>';

        } elseif($booking_has_custom_pricing == 1) {
            $output .= '<li>'.$no_of_days.' '.$night_label.' ('.$local['with_custom_period_label'].') <span>'.$nights_total_price.'</span></li>';

        } else {
            $output .= '<li>'.$price_per_night.' x '.$no_of_days.' '.$night_label.' <span>'.$nights_total_price.'</span></li>';
        }

        if(!empty($additional_guests)) {
            $output .= '<li>'.$additional_guests.' '.$add_guest_label.' <span>'.homey_formatted_price($additional_guests_total_price).'</span></li>';
        }

        if(!empty($prices_array['cleaning_fee']) && $prices_array['cleaning_fee'] != 0) {
            $output .= '<li>'.$local['cs_cleaning_fee'].' <span>'.$cleaning_fee.'</span></li>';
        }

        if(!empty($prices_array['city_fee']) && $prices_array['city_fee'] != 0) {
            $output .= '<li>'.$local['cs_city_fee'].' <span>'.$city_fee.'</span></li>';
        }

        if(!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<li>'.$local['cs_sec_deposit'].' <span>'.homey_formatted_price($security_deposit).'</span></li>';
        }

        if(!empty($services_fee) && $services_fee != 0 ) {
            $output .= '<li>'.$local['cs_services_fee'].' <span>'.homey_formatted_price($services_fee).'</span></li>';
        }

        if(!empty($taxes) && $taxes != 0 ) {
            $output .= '<li>'.$local['cs_taxes'].' '.$taxes_percent.'% <span>'.homey_formatted_price($taxes).'</span></li>';
        }

        if(!empty($upfront_payment) && $upfront_payment != 0) {
            $output .= '<li class="payment-due">'.$local['cs_payment_due'].' <span>'.homey_formatted_price($upfront_payment).'</span></li>';
            $output .= '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="'.$upfront_payment.'">';
        }

        if(!empty($balance) && $balance != 0) {
            $output .= '<li><i class="fa fa-info-circle"></i> '.$local['cs_pay_rest_1'].' '.homey_formatted_price($balance).' '.$local['cs_pay_rest_2'].'</li>';
        }

        $output .= '</ul>';
        $output .= '</div>';

        return $output;
    }
}

if( !function_exists('homey_calculate_reservation_cost') ) {
    function homey_calculate_reservation_cost($reservation_id, $collapse = false) {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        if(empty($reservation_id)) {
            return;
        }

        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);
        $listing_id     = intval(isset($reservation_meta['listing_id'])?$reservation_meta['listing_id']:0);
        $booking_type = homey_booking_type_by_id($listing_id);

        if( $booking_type == 'per_week' ) {
            return homey_calculate_reservation_cost_weekly($reservation_id, $collapse);
        } else if( $booking_type == 'per_month' ) {
            return homey_calculate_reservation_cost_monthly($reservation_id, $collapse);
        } else if( $booking_type == 'per_day_date' ) {
            return homey_calculate_reservation_cost_day_date($reservation_id, $collapse);
        } else {
            return homey_calculate_reservation_cost_nightly($reservation_id, $collapse);
        }
    }
}

if( !function_exists('homey_calculate_reservation_cost_monthly') ) {
    function homey_calculate_reservation_cost_monthly($reservation_id, $collapse = false) {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        if(empty($reservation_id)) {
            return;
        }
        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);
        $extra_options = get_post_meta($reservation_id, 'extra_options', true);

        $listing_id     = intval($reservation_meta['listing_id']);
        $check_in_date  = wp_kses ( $reservation_meta['check_in_date'], $allowded_html );
        $check_out_date = wp_kses ( $reservation_meta['check_out_date'], $allowded_html );
        $guests         = intval($reservation_meta['guests']);


        $price_per_month = homey_formatted_price($reservation_meta['price_per_month'], true);
        $no_of_months = $reservation_meta['total_months_count'];
        $no_of_days = $reservation_meta['no_of_days'];
        $months_total_price = homey_formatted_price($reservation_meta['months_total_price'], false);

        $cleaning_fee = homey_formatted_price($reservation_meta['cleaning_fee']);
        $services_fee = $reservation_meta['services_fee'];
        $taxes = $reservation_meta['taxes'];
        $taxes_percent = $reservation_meta['taxes_percent'];
        $city_fee = homey_formatted_price($reservation_meta['city_fee']);
        $security_deposit = $reservation_meta['security_deposit'];
        $additional_guests = $reservation_meta['additional_guests'];
        $additional_guests_price = $reservation_meta['additional_guests_price'];
        $additional_guests_total_price = $reservation_meta['additional_guests_total_price'];

        $upfront_payment = $reservation_meta['upfront'];

        $balance = $reservation_meta['balance'];
        $total_price = $reservation_meta['total'];

        $booking_has_weekend = $reservation_meta['booking_has_weekend'];
        $booking_has_custom_pricing = $reservation_meta['booking_has_custom_pricing'];
        $with_weekend_label = $local['with_weekend_label'];

        if($no_of_days > 1) {
            $night_label = homey_option('glc_day_nights_label');
        } else {
            $night_label = homey_option('glc_day_night_label');
        }

        if($no_of_months > 1) {
            $month_label = homey_option('glc_months_label');
        } else {
            $month_label = homey_option('glc_month_label');
        }

        if($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }

        $invoice_id = isset($_GET['invoice_id']) ? $_GET['invoice_id'] : '';
        $reservation_detail_id = isset($_GET['reservation_detail']) ? $_GET['reservation_detail'] : '';
        $is_host = false;
        $homey_invoice_buyer = get_post_meta($reservation_id, 'listing_renter', true);

        if( homey_is_host() && $homey_invoice_buyer != get_current_user_id() ) {
            $is_host = true;
        }

        $extra_prices = homey_get_extra_prices($extra_options, $no_of_days, $guests);
        $extra_expenses = homey_get_extra_expenses($reservation_id);
        $extra_discount = homey_get_extra_discount($reservation_id);

        if($is_host && !empty($services_fee)) {
            $total_price = $total_price - $services_fee;
        }

        if(!empty($extra_expenses)) {
            $expenses_total_price = $extra_expenses['expenses_total_price'];
            $total_price = $total_price + $expenses_total_price;
//            $balance = $balance + $expenses_total_price; //just to exclude from payment to local
        }

        if(!empty($extra_discount)) {
            $discount_total_price = $extra_discount['discount_total_price'];
            $total_price = $total_price - $discount_total_price;
//            $balance = $balance - $discount_total_price; //just to exclude from payment to local
        }

        if(homey_option('reservation_payment') == 'full') {
            $upfront_payment = $total_price;
            $balance = 0;
        }

        $start_div = '<div class="payment-list">';

        if($collapse) {
            $output = '<div class="payment-list-price-detail clearfix">';
            $output .= '<div class="pull-left">';
            $output .= '<div class="payment-list-price-detail-total-price">'.$local['cs_total'].'</div>';
            $output .= '<div class="payment-list-price-detail-note">'.$local['cs_tax_fees'].'</div>';
            $output .= '</div>';

            $output .= '<div class="pull-right text-right">';
            $output .= '<div class="payment-list-price-detail-total-price">'.homey_formatted_price($total_price).'</div>';
            $output .= '<a class="payment-list-detail-btn" data-toggle="collapse" data-target=".collapseExample" href="javascript:void(0);" aria-expanded="false" aria-controls="collapseExample">'.$local['cs_view_details'].'</a>';
            $output .= '</div>';
            $output .= '</div>';

            $start_div  = '<div class="collapse collapseExample" id="collapseExample">';
        }


        $output .= $start_div;
        $output .= '<ul>';


        $output .= '<li class="homey_price_first">'.($price_per_month).' x '.esc_attr($no_of_months).' '.esc_attr($month_label);

        if( $no_of_days > 0 ) {
            $output .= ' '.esc_html__('and', 'homey').' '.esc_attr($no_of_days).' '.esc_attr($night_label);
        }

        $output .= '<span>'.$months_total_price.'</span></li>';

        if(!empty($additional_guests)) {
            $output .= '<li>'.$additional_guests.' '.$add_guest_label.' <span>'.homey_formatted_price($additional_guests_total_price).'</span></li>';
        }

        if(!empty($reservation_meta['cleaning_fee']) && $reservation_meta['cleaning_fee'] != 0) {
            $output .= '<li>'.$local['cs_cleaning_fee'].' <span>'.$cleaning_fee.'</span></li>';
        }

        if(!empty($extra_prices)) {
            $output .= $extra_prices['extra_html'];
        }

        if(!empty($reservation_meta['city_fee']) && $reservation_meta['city_fee'] != 0) {
            $output .= '<li>'.$local['cs_city_fee'].' <span>'.$city_fee.'</span></li>';
        }

        if(!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<li>'.$local['cs_sec_deposit'].' <span>'.homey_formatted_price($security_deposit).'</span></li>';
        }


        if(!empty($services_fee) && !$is_host) {
            $output .= '<li>'.$local['cs_services_fee'].' <span>'.homey_formatted_price($services_fee).'</span></li>';
        }

        if(!empty($extra_expenses)) {
            $output .= $extra_expenses['expenses_html'];
        }

        if(!empty($extra_discount)) {
            $output .= $extra_discount['discount_html'];
        }


        if(!empty($taxes) && $taxes != 0 ) {
            $output .= '<li>'.$local['cs_taxes'].' '.$taxes_percent.'% <span>'.homey_formatted_price($taxes).'</span></li>';
        }

        if(homey_option('reservation_payment') == 'full') {

            if($is_host && !empty($services_fee)) {
                $upfront_payment = $upfront_payment - $services_fee;
            }
            $output .= '<li class="payment-due">'.$local['inv_total'].' <span>'.homey_formatted_price($upfront_payment).'</span></li>';
            $output .= '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="'.$upfront_payment.'">';

        } else {
            if(!empty($upfront_payment) && $upfront_payment != 0) {
                if($is_host && !empty($services_fee)) {
                    $upfront_payment = $upfront_payment - $services_fee;
                }
                $output .= '<li class="payment-due">'.$local['cs_payment_due'].' <span>'.homey_formatted_price($upfront_payment).'</span></li>';
                $output .= '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="'.$upfront_payment.'">';
            }
        }

        if(!empty($balance) && $balance != 0) {
            $output .= '<li><i class="fa fa-info-circle"></i> '.$local['cs_pay_rest_1'].' '.homey_formatted_price($balance).' '.$local['cs_pay_rest_2'].'</li>';
        }


        $output .= '</ul>';
        $output .= '</div>';

        return $output;
    }
}

if( !function_exists('homey_calculate_reservation_cost_weekly') ) {
    function homey_calculate_reservation_cost_weekly($reservation_id, $collapse = false) {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        if(empty($reservation_id)) {
            return;
        }
        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);
        $extra_options = get_post_meta($reservation_id, 'extra_options', true);

        $listing_id     = intval($reservation_meta['listing_id']);
        $check_in_date  = wp_kses ( $reservation_meta['check_in_date'], $allowded_html );
        $check_out_date = wp_kses ( $reservation_meta['check_out_date'], $allowded_html );
        $guests         = intval($reservation_meta['guests']);


        $price_per_week = homey_formatted_price($reservation_meta['price_per_week'], true);
        $no_of_days = $reservation_meta['no_of_days'];
        $no_of_weeks = $reservation_meta['total_weeks_count'];

        $weeks_total_price = homey_formatted_price($reservation_meta['weeks_total_price'], false);

        $cleaning_fee = homey_formatted_price($reservation_meta['cleaning_fee']);
        $services_fee = $reservation_meta['services_fee'];
        $taxes = $reservation_meta['taxes'];
        $taxes_percent = $reservation_meta['taxes_percent'];
        $city_fee = homey_formatted_price($reservation_meta['city_fee']);
        $security_deposit = $reservation_meta['security_deposit'];
        $additional_guests = $reservation_meta['additional_guests'];
        $additional_guests_price = $reservation_meta['additional_guests_price'];
        $additional_guests_total_price = $reservation_meta['additional_guests_total_price'];

        $upfront_payment = $reservation_meta['upfront'];

        $balance = $reservation_meta['balance'];
        $total_price = $reservation_meta['total'];

        $booking_has_weekend = $reservation_meta['booking_has_weekend'];
        $booking_has_custom_pricing = $reservation_meta['booking_has_custom_pricing'];
        $with_weekend_label = $local['with_weekend_label'];

        if($no_of_days > 1) {
            $night_label = homey_option('glc_day_nights_label');
        } else {
            $night_label = homey_option('glc_day_night_label');
        }

        if($no_of_weeks > 1) {
            $week_label = homey_option('glc_weeks_label');
        } else {
            $week_label = homey_option('glc_week_label');
        }

        if($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }

        $invoice_id = isset($_GET['invoice_id']) ? $_GET['invoice_id'] : '';
        $reservation_detail_id = isset($_GET['reservation_detail']) ? $_GET['reservation_detail'] : '';
        $is_host = false;
        $homey_invoice_buyer = get_post_meta($reservation_id, 'listing_renter', true);

        if( homey_is_host() && $homey_invoice_buyer != get_current_user_id() ) {
            $is_host = true;
        }

        $extra_prices = homey_get_extra_prices($extra_options, $no_of_days, $guests);
        $extra_expenses = homey_get_extra_expenses($reservation_id);
        $extra_discount = homey_get_extra_discount($reservation_id);

        if($is_host && !empty($services_fee)) {
            $total_price = $total_price - $services_fee;
        }

        if(!empty($extra_expenses)) {
            $expenses_total_price = $extra_expenses['expenses_total_price'];
            $total_price = $total_price + $expenses_total_price;
            $balance = $balance + $expenses_total_price;
        }

        if(!empty($extra_discount)) {
            $discount_total_price = $extra_discount['discount_total_price'];
            $total_price = $total_price - $discount_total_price;
            $balance = $balance - $discount_total_price;
        }

        if(homey_option('reservation_payment') == 'full') {
            $upfront_payment = $total_price;
            $balance = 0;
        }

        $start_div = '<div class="payment-list">';

        if($collapse) {
            $output = '<div class="payment-list-price-detail clearfix">';
            $output .= '<div class="pull-left">';
            $output .= '<div class="payment-list-price-detail-total-price">'.$local['cs_total'].'</div>';
            $output .= '<div class="payment-list-price-detail-note">'.$local['cs_tax_fees'].'</div>';
            $output .= '</div>';

            $output .= '<div class="pull-right text-right">';
            $output .= '<div class="payment-list-price-detail-total-price">'.homey_formatted_price($total_price).'</div>';
            $output .= '<a class="payment-list-detail-btn" data-toggle="collapse" data-target=".collapseExample" href="javascript:void(0);" aria-expanded="false" aria-controls="collapseExample">'.$local['cs_view_details'].'</a>';
            $output .= '</div>';
            $output .= '</div>';

            $start_div  = '<div class="collapse collapseExample" id="collapseExample">';
        }


        $output .= $start_div;
        $output .= '<ul>';


        $output .= '<li class="homey_price_first">'.($price_per_week).' x '.esc_attr($no_of_weeks).' '.esc_attr($week_label);

        if( $no_of_days > 0 ) {
            $output .= ' '.esc_html__('and', 'homey').' '.esc_attr($no_of_days).' '.esc_attr($night_label);
        }

        $output .= '<span>'.$weeks_total_price.'</span></li>';

        if(!empty($additional_guests)) {
            $output .= '<li>'.$additional_guests.' '.$add_guest_label.' <span>'.homey_formatted_price($additional_guests_total_price).'</span></li>';
        }

        if(!empty($reservation_meta['cleaning_fee']) && $reservation_meta['cleaning_fee'] != 0) {
            $output .= '<li>'.$local['cs_cleaning_fee'].' <span>'.$cleaning_fee.'</span></li>';
        }

        if(!empty($extra_prices)) {
            $output .= $extra_prices['extra_html'];
        }

        if(!empty($reservation_meta['city_fee']) && $reservation_meta['city_fee'] != 0) {
            $output .= '<li>'.$local['cs_city_fee'].' <span>'.$city_fee.'</span></li>';
        }

        if(!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<li>'.$local['cs_sec_deposit'].' <span>'.homey_formatted_price($security_deposit).'</span></li>';
        }


        if(!empty($services_fee) && !$is_host) {
            $output .= '<li>'.$local['cs_services_fee'].' <span>'.homey_formatted_price($services_fee).'</span></li>';
        }

        if(!empty($extra_expenses)) {
            $output .= $extra_expenses['expenses_html'];
        }

        if(!empty($extra_discount)) {
            $output .= $extra_discount['discount_html'];
        }


        if(!empty($taxes) && $taxes != 0 ) {
            $output .= '<li>'.$local['cs_taxes'].' '.$taxes_percent.'% <span>'.homey_formatted_price($taxes).'</span></li>';
        }

        if(homey_option('reservation_payment') == 'full') {

            if($is_host && !empty($services_fee)) {
                $upfront_payment = $upfront_payment - $services_fee;
            }
            $output .= '<li class="payment-due">'.$local['inv_total'].' <span>'.homey_formatted_price($upfront_payment).'</span></li>';
            $output .= '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="'.$upfront_payment.'">';

        } else {
            if(!empty($upfront_payment) && $upfront_payment != 0) {
                if($is_host && !empty($services_fee)) {
                    $upfront_payment = $upfront_payment - $services_fee;
                }
                $output .= '<li class="payment-due">'.$local['cs_payment_due'].' <span>'.homey_formatted_price($upfront_payment).'</span></li>';
                $output .= '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="'.$upfront_payment.'">';
            }
        }

        if(!empty($balance) && $balance != 0) {
            $output .= '<li><i class="fa fa-info-circle"></i> '.$local['cs_pay_rest_1'].' '.homey_formatted_price($balance).' '.$local['cs_pay_rest_2'].'</li>';
        }


        $output .= '</ul>';
        $output .= '</div>';

        return $output;
    }
}

if( !function_exists('homey_calculate_reservation_cost_nightly') ) {
    function homey_calculate_reservation_cost_nightly($reservation_id, $collapse = false) {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        if(empty($reservation_id)) {
            return;
        }

        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);
        $extra_options = get_post_meta($reservation_id, 'extra_options', true);

        $listing_id     = intval(isset($reservation_meta['listing_id'])?$reservation_meta['listing_id']:0);
        $check_in_date  = wp_kses ( isset($reservation_meta['check_in_date'])?$reservation_meta['check_in_date']:'', $allowded_html );
        $check_out_date = wp_kses ( isset($reservation_meta['check_out_date'])?$reservation_meta['check_out_date']:'', $allowded_html );
        $guests         = intval(isset($reservation_meta['guests'])?$reservation_meta['guests']:0);


        $price_per_night = homey_formatted_price(isset($reservation_meta['price_per_night'])?$reservation_meta['price_per_night']:0, true);
        $no_of_days = isset($reservation_meta['no_of_days'])?$reservation_meta['no_of_days']:0;

        $nights_total_price = homey_formatted_price(isset($reservation_meta['nights_total_price'])?$reservation_meta['nights_total_price']:0, false);

        $cleaning_fee = homey_formatted_price(isset($reservation_meta['cleaning_fee'])?$reservation_meta['cleaning_fee']:0);
        $services_fee = isset($reservation_meta['services_fee'])?$reservation_meta['services_fee']:0;
        $taxes = isset($reservation_meta['taxes'])?$reservation_meta['taxes']:0;
        $taxes_percent = isset($reservation_meta['taxes_percent'])?$reservation_meta['taxes_percent']:0;
        $city_fee = homey_formatted_price(isset($reservation_meta['city_fee'])?$reservation_meta['city_fee']:0);
        $security_deposit = isset($reservation_meta['security_deposit'])?$reservation_meta['security_deposit']:0;
        $additional_guests = isset($reservation_meta['additional_guests'])?$reservation_meta['additional_guests']:0;
        $additional_guests_price = isset($reservation_meta['additional_guests_price'])?$reservation_meta['additional_guests_price']:0;
        $additional_guests_total_price = isset($reservation_meta['additional_guests_total_price'])?$reservation_meta['additional_guests_total_price']:0;

        $upfront_payment = isset($reservation_meta['upfront'])?$reservation_meta['upfront']:0;

        $balance = isset($reservation_meta['balance'])?$reservation_meta['balance']:0;
        $total_price = isset($reservation_meta['total'])?$reservation_meta['total']:0;

        $booking_has_weekend = isset($reservation_meta['booking_has_weekend'])?$reservation_meta['booking_has_weekend']:0;
        $booking_has_custom_pricing = isset($reservation_meta['booking_has_custom_pricing'])?$reservation_meta['booking_has_custom_pricing']:0;
        $with_weekend_label = $local['with_weekend_label'];

        if($no_of_days > 1) {
            $night_label = homey_option('glc_day_nights_label');
        } else {
            $night_label = homey_option('glc_day_night_label');
        }

        if($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }

        $invoice_id = isset($_GET['invoice_id']) ? $_GET['invoice_id'] : '';
        $reservation_detail_id = isset($_GET['reservation_detail']) ? $_GET['reservation_detail'] : '';
        $is_host = false;
        $homey_invoice_buyer = get_post_meta($reservation_id, 'listing_renter', true);

        if( homey_is_host() && $homey_invoice_buyer != get_current_user_id() ) {
            $is_host = true;
        }

        $extra_prices = homey_get_extra_prices($extra_options, $no_of_days, $guests);
        $extra_expenses = homey_get_extra_expenses($reservation_id);
        $extra_discount = homey_get_extra_discount($reservation_id);

        if($is_host && !empty($services_fee)) {
            $total_price = $total_price - $services_fee;
        }

        if(!empty($extra_expenses)) {
            $expenses_total_price = $extra_expenses['expenses_total_price'];
            $total_price = $total_price + $expenses_total_price;
            $balance = $balance + $expenses_total_price;
        }

        if(!empty($extra_discount)) {
            $discount_total_price = $extra_discount['discount_total_price'];
            $total_price = $total_price - $discount_total_price;
            //zahid.k added for discount
            $upfront_payment = $upfront_payment - $discount_total_price;
            //zahid.k added for discount
            $balance = $balance - $discount_total_price;
        }

        if(homey_option('reservation_payment') == 'full') {
            $upfront_payment = $total_price;
            $balance = 0;
        }

        $start_div = '<div class="payment-list">';

        if($collapse) {
            $output = '<div class="payment-list-price-detail clearfix">';
            $output .= '<div class="pull-left">';
            $output .= '<div class="payment-list-price-detail-total-price">'.$local['cs_total'].'</div>';
            $output .= '<div class="payment-list-price-detail-note">'.$local['cs_tax_fees'].'</div>';
            $output .= '</div>';

            $output .= '<div class="pull-right text-right">';
            $output .= '<div class="payment-list-price-detail-total-price">'.homey_formatted_price($total_price).'</div>';
            $output .= '<a class="payment-list-detail-btn" data-toggle="collapse" data-target=".collapseExample" href="javascript:void(0);" aria-expanded="false" aria-controls="collapseExample">'.$local['cs_view_details'].'</a>';
            $output .= '</div>';
            $output .= '</div>';

            $start_div  = '<div class="collapse collapseExample" id="collapseExample">';
        }


        $output .= $start_div;
        $output .= '<ul>';

        if($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
            $output .= '<li>'.$no_of_days.' '.$night_label.' ('.$local['with_custom_period_and_weekend_label'].') <span>'.$nights_total_price.'</span></li>';

        } elseif($booking_has_weekend == 1) {
            $output .= '<li>'.$no_of_days.' '.$night_label.' ('.$with_weekend_label.') <span>'.$nights_total_price.'</span></li>';

        } elseif($booking_has_custom_pricing == 1) {
            $output .= '<li>'.$no_of_days.' '.$night_label.' ('.$local['with_custom_period_label'].') <span>'.$nights_total_price.'</span></li>';

        } else {
            $output .= '<li>'.$price_per_night.' x '.$no_of_days.' '.$night_label.' <span>'.$nights_total_price.'</span></li>';
        }

        if(!empty($additional_guests)) {
            $output .= '<li>'.$additional_guests.' '.$add_guest_label.' <span>'.homey_formatted_price($additional_guests_total_price).'</span></li>';
        }

        if(isset($reservation_meta['cleaning_fee'])){
            if(!empty($reservation_meta['cleaning_fee']) && $reservation_meta['cleaning_fee'] != 0) {
                $output .= '<li>'.$local['cs_cleaning_fee'].' <span>'.$cleaning_fee.'</span></li>';
            }
        }

        if(!empty($extra_prices)) {
            $output .= $extra_prices['extra_html'];
        }

        if(isset($reservation_meta['city_fee'])){
            if(!empty($reservation_meta['city_fee']) && $reservation_meta['city_fee'] != 0) {
                $output .= '<li>'.$local['cs_city_fee'].' <span>'.$city_fee.'</span></li>';
            }
        }

        if(!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<li>'.$local['cs_sec_deposit'].' <span>'.homey_formatted_price($security_deposit).'</span></li>';
        }


        if(!empty($services_fee) && !$is_host) {
            $output .= '<li>'.$local['cs_services_fee'].' <span>'.homey_formatted_price($services_fee).'</span></li>';
        }

        if(!empty($extra_expenses)) {
            $output .= $extra_expenses['expenses_html'];
        }

        if(!empty($extra_discount)) {
            $output .= $extra_discount['discount_html'];
        }


        if(!empty($taxes) && $taxes != 0 ) {
            $output .= '<li>'.$local['cs_taxes'].' '.$taxes_percent.'% <span>'.homey_formatted_price($taxes).'</span></li>';
        }

        if(homey_option('reservation_payment') == 'full') {

            if($is_host && !empty($services_fee)) {
                $upfront_payment = $upfront_payment - $services_fee;
            }
            $output .= '<li class="payment-due">'.$local['inv_total'].' <span>'.homey_formatted_price($upfront_payment).'</span></li>';
            $output .= '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="'.$upfront_payment.'">';

        } else {
            if(!empty($upfront_payment) && $upfront_payment != 0) {
                if($is_host && !empty($services_fee)) {
                    $upfront_payment = $upfront_payment - $services_fee;
                }
                $output .= '<li class="payment-due">'.$local['cs_payment_due'].' <span>'.homey_formatted_price($upfront_payment).'</span></li>';
                $output .= '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="'.$upfront_payment.'">';
            }
        }

        if(!empty($balance) && $balance != 0) {
            $output .= '<li><i class="fa fa-info-circle"></i> '.$local['cs_pay_rest_1'].' '.homey_formatted_price($balance).' '.$local['cs_pay_rest_2'].'</li>';
        }


        $output .= '</ul>';
        $output .= '</div>';

        return $output;
    }
}

if( !function_exists('homey_calculate_reservation_cost_day_date') ) {
    function homey_calculate_reservation_cost_day_date($reservation_id, $collapse = false) {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        if(empty($reservation_id)) {
            return;
        }

        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);
        $extra_options = get_post_meta($reservation_id, 'extra_options', true);

        $listing_id     = intval(isset($reservation_meta['listing_id'])?$reservation_meta['listing_id']:0);
        $check_in_date  = wp_kses ( isset($reservation_meta['check_in_date'])?$reservation_meta['check_in_date']:'', $allowded_html );
        $check_out_date = wp_kses ( isset($reservation_meta['check_out_date'])?$reservation_meta['check_out_date']:'', $allowded_html );
        $guests         = intval(isset($reservation_meta['guests'])?$reservation_meta['guests']:0);


        $price_per_day_date = homey_formatted_price(isset($reservation_meta['price_per_day_date'])?$reservation_meta['price_per_day_date']:0, true);
        $no_of_days = isset($reservation_meta['no_of_days'])?$reservation_meta['no_of_days']:0;

        $days_total_price = homey_formatted_price(isset($reservation_meta['days_total_price'])?$reservation_meta['days_total_price']:0, false);

        $cleaning_fee = homey_formatted_price(isset($reservation_meta['cleaning_fee'])?$reservation_meta['cleaning_fee']:0);
        $services_fee = isset($reservation_meta['services_fee'])?$reservation_meta['services_fee']:0;
        $taxes = isset($reservation_meta['taxes'])?$reservation_meta['taxes']:0;
        $taxes_percent = isset($reservation_meta['taxes_percent'])?$reservation_meta['taxes_percent']:0;
        $city_fee = homey_formatted_price(isset($reservation_meta['city_fee'])?$reservation_meta['city_fee']:0);
        $security_deposit = isset($reservation_meta['security_deposit'])?$reservation_meta['security_deposit']:0;
        $additional_guests = isset($reservation_meta['additional_guests'])?$reservation_meta['additional_guests']:0;
        $additional_guests_price = isset($reservation_meta['additional_guests_price'])?$reservation_meta['additional_guests_price']:0;
        $additional_guests_total_price = isset($reservation_meta['additional_guests_total_price'])?$reservation_meta['additional_guests_total_price']:0;

        $upfront_payment = isset($reservation_meta['upfront'])?$reservation_meta['upfront']:0;

        $balance = isset($reservation_meta['balance'])?$reservation_meta['balance']:0;
        $total_price = isset($reservation_meta['total'])?$reservation_meta['total']:0;

        $booking_has_weekend = isset($reservation_meta['booking_has_weekend'])?$reservation_meta['booking_has_weekend']:0;
        $booking_has_custom_pricing = isset($reservation_meta['booking_has_custom_pricing'])?$reservation_meta['booking_has_custom_pricing']:0;
       $with_weekend_label = $local['with_weekend_label'];

        if($no_of_days > 1) {
            $night_label = homey_option('glc_day_dates_label');
        } else {
            $night_label = homey_option('glc_day_date_label');
        }

        if($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }

        $invoice_id = isset($_GET['invoice_id']) ? $_GET['invoice_id'] : '';
        $reservation_detail_id = isset($_GET['reservation_detail']) ? $_GET['reservation_detail'] : '';
        $is_host = false;
        $homey_invoice_buyer = get_post_meta($reservation_id, 'listing_renter', true);

        if( homey_is_host() && $homey_invoice_buyer != get_current_user_id() ) {
            $is_host = true;
        }

        $extra_prices = homey_get_extra_prices($extra_options, $no_of_days, $guests);
        $extra_expenses = homey_get_extra_expenses($reservation_id);
        $extra_discount = homey_get_extra_discount($reservation_id);

        if($is_host && !empty($services_fee)) {
            $total_price = $total_price - $services_fee;
        }

        if(!empty($extra_expenses)) {
            $expenses_total_price = $extra_expenses['expenses_total_price'];
            $total_price = $total_price + $expenses_total_price;
            $balance = $balance + $expenses_total_price;
        }

        if(!empty($extra_discount)) {
            $discount_total_price = $extra_discount['discount_total_price'];
            $total_price = $total_price - $discount_total_price;
            //zahid.k added for discount
            $upfront_payment = $upfront_payment - $discount_total_price;
            //zahid.k added for discount
            $balance = $balance - $discount_total_price;
        }

        if(homey_option('reservation_payment') == 'full') {
            $upfront_payment = $total_price;
            $balance = 0;
        }

        $start_div = '<div class="payment-list">';

        if($collapse) {
            $output = '<div class="payment-list-price-detail clearfix">';
            $output .= '<div class="pull-left">';
            $output .= '<div class="payment-list-price-detail-total-price">'.$local['cs_total'].'</div>';
            $output .= '<div class="payment-list-price-detail-note">'.$local['cs_tax_fees'].'</div>';
            $output .= '</div>';

            $output .= '<div class="pull-right text-right">';
            $output .= '<div class="payment-list-price-detail-total-price">'.homey_formatted_price($total_price).'</div>';
            $output .= '<a class="payment-list-detail-btn" data-toggle="collapse" data-target=".collapseExample" href="javascript:void(0);" aria-expanded="false" aria-controls="collapseExample">'.$local['cs_view_details'].'</a>';
            $output .= '</div>';
            $output .= '</div>';

            $start_div  = '<div class="collapse collapseExample" id="collapseExample">';
        }


        $output .= $start_div;
        $output .= '<ul>';

        if($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
            $output .= '<li>'.$no_of_days.' '.$night_label.' ('.$local['with_custom_period_and_weekend_label'].') <span>'.$days_total_price.'</span></li>';

        } elseif($booking_has_weekend == 1) {
            $output .= '<li>'.$no_of_days.' '.$night_label.' ('.$with_weekend_label.') <span>'.$days_total_price.'</span></li>';

        } elseif($booking_has_custom_pricing == 1) {
            $output .= '<li>'.$no_of_days.' '.$night_label.' ('.$local['with_custom_period_label'].') <span>'.$days_total_price.'</span></li>';

        } else {
            $output .= '<li>'.$price_per_day_date.' x '.$no_of_days.' '.$night_label.' <span>'.$days_total_price.'</span></li>';
        }

        if(!empty($additional_guests)) {
            $output .= '<li>'.$additional_guests.' '.$add_guest_label.' <span>'.homey_formatted_price($additional_guests_total_price).'</span></li>';
        }

        if(isset($reservation_meta['cleaning_fee'])){
            if(!empty($reservation_meta['cleaning_fee']) && $reservation_meta['cleaning_fee'] != 0) {
                $output .= '<li>'.$local['cs_cleaning_fee'].' <span>'.$cleaning_fee.'</span></li>';
            }
        }

        if(!empty($extra_prices)) {
            $output .= $extra_prices['extra_html'];
        }

        if(isset($reservation_meta['city_fee'])){
            if(!empty($reservation_meta['city_fee']) && $reservation_meta['city_fee'] != 0) {
                $output .= '<li>'.$local['cs_city_fee'].' <span>'.$city_fee.'</span></li>';
            }
        }

        if(!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<li>'.$local['cs_sec_deposit'].' <span>'.homey_formatted_price($security_deposit).'</span></li>';
        }


        if(!empty($services_fee) && !$is_host) {
            $output .= '<li>'.$local['cs_services_fee'].' <span>'.homey_formatted_price($services_fee).'</span></li>';
        }

        if(!empty($extra_expenses)) {
            $output .= $extra_expenses['expenses_html'];
        }

        if(!empty($extra_discount)) {
            $output .= $extra_discount['discount_html'];
        }


        if(!empty($taxes) && $taxes != 0 ) {
            $output .= '<li>'.$local['cs_taxes'].' '.$taxes_percent.'% <span>'.homey_formatted_price($taxes).'</span></li>';
        }

        if(homey_option('reservation_payment') == 'full') {

            if($is_host && !empty($services_fee)) {
                $upfront_payment = $upfront_payment - $services_fee;
            }
            $output .= '<li class="payment-due">'.$local['inv_total'].' <span>'.homey_formatted_price($upfront_payment).'</span></li>';
            $output .= '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="'.$upfront_payment.'">';

        } else {
            if(!empty($upfront_payment) && $upfront_payment != 0) {
                if($is_host && !empty($services_fee)) {
                    $upfront_payment = $upfront_payment - $services_fee;
                }
                $output .= '<li class="payment-due">'.$local['cs_payment_due'].' <span>'.homey_formatted_price($upfront_payment).'</span></li>';
                $output .= '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="'.$upfront_payment.'">';
            }
        }

        if(!empty($balance) && $balance != 0) {
            $output .= '<li><i class="fa fa-info-circle"></i> '.$local['cs_pay_rest_1'].' '.homey_formatted_price($balance).' '.$local['cs_pay_rest_2'].'</li>';
        }


        $output .= '</ul>';
        $output .= '</div>';

        return $output;
    }
}
// / Zahid.k beta for new payment info beppe

if( !function_exists('homey_calculate_reservation_cost_1_5_3') ) {
    function homey_calculate_reservation_cost_1_5_3($reservation_id, $collapse = false) {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        if(empty($reservation_id)) {
            return;
        }
        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);
        $extra_options = get_post_meta($reservation_id, 'extra_options', true);

        $listing_id     = intval($reservation_meta['listing_id']);
        $check_in_date  = wp_kses ( $reservation_meta['check_in_date'], $allowded_html );
        $check_out_date = wp_kses ( $reservation_meta['check_out_date'], $allowded_html );
        $guests         = intval($reservation_meta['guests']);


        $price_per_night = homey_formatted_price($reservation_meta['price_per_night'], true);
        $no_of_days = $reservation_meta['no_of_days'];

        $nights_total_price = homey_formatted_price($reservation_meta['nights_total_price'], false);

        $cleaning_fee = homey_formatted_price($reservation_meta['cleaning_fee']);
        $services_fee = $reservation_meta['services_fee'];
        $taxes = $reservation_meta['taxes'];
        $taxes_percent = $reservation_meta['taxes_percent'];
        $city_fee = homey_formatted_price($reservation_meta['city_fee']);
        $security_deposit = $reservation_meta['security_deposit'];
        $additional_guests = $reservation_meta['additional_guests'];
        $additional_guests_price = $reservation_meta['additional_guests_price'];
        $additional_guests_total_price = $reservation_meta['additional_guests_total_price'];

        $upfront_payment = $reservation_meta['upfront'];

        $balance = $reservation_meta['balance'];
        $total_price = $reservation_meta['total'];

        $booking_has_weekend = $reservation_meta['booking_has_weekend'];
        $booking_has_custom_pricing = $reservation_meta['booking_has_custom_pricing'];
        $with_weekend_label = $local['with_weekend_label'];

        if($no_of_days > 1) {
            $night_label = homey_option('glc_day_nights_label');
        } else {
            $night_label = homey_option('glc_day_night_label');
        }

        if($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }

        $invoice_id = isset($_GET['invoice_id']) ? $_GET['invoice_id'] : '';
        $reservation_detail_id = isset($_GET['reservation_detail']) ? $_GET['reservation_detail'] : '';
        $is_host = false;
        $homey_invoice_buyer = get_post_meta($reservation_id, 'listing_renter', true);

        if( homey_is_host() && $homey_invoice_buyer != get_current_user_id()) {
            $is_host = true;
        }

        $extra_prices = homey_get_extra_prices($extra_options, $no_of_days, $guests);
        $extra_expenses = homey_get_extra_expenses($reservation_id);
        $extra_discount = homey_get_extra_discount($reservation_id);

        if($is_host && !empty($services_fee)) {
            $total_price = $total_price - $services_fee;
        }

        if(!empty($extra_expenses)) {
            $expenses_total_price = $extra_expenses['expenses_total_price'];
            $total_price = $total_price + $expenses_total_price;
            $balance = $balance + $expenses_total_price;
        }

        if(!empty($extra_discount)) {
            $discount_total_price = $extra_discount['discount_total_price'];
            $total_price = $total_price - $discount_total_price;
            $balance = $balance - $discount_total_price;
        }

        if(homey_option('reservation_payment') == 'full') {
            $upfront_payment = $total_price;
            $balance = 0;
        }

        $start_div = '<div class="payment-list">';

        if($collapse) {
            $output = '<div class="payment-list-price-detail clearfix">';
            $output .= '<div class="pull-left">';
            $output .= '<div class="payment-list-price-detail-total-price">'.$local['cs_total'].'</div>';
            $output .= '<div class="payment-list-price-detail-note">'.$local['cs_tax_fees'].'</div>';
            $output .= '</div>';

            $output .= '<div class="pull-right text-right">';
            $output .= '<div class="payment-list-price-detail-total-price">'.homey_formatted_price($total_price).'</div>';
            $output .= '<a class="payment-list-detail-btn" data-toggle="collapse" data-target=".collapseExample" href="javascript:void(0);" aria-expanded="false" aria-controls="collapseExample">'.$local['cs_view_details'].'</a>';
            $output .= '</div>';
            $output .= '</div>';

            $start_div  = '<div class="collapse collapseExample" id="collapseExample">';
        }


        $output .= $start_div;
        $output .= '<ul>';

        if($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
            $output .= '<li>'.$no_of_days.' '.$night_label.' ('.$local['with_custom_period_and_weekend_label'].') <span>'.$nights_total_price.'</span></li>';

        } elseif($booking_has_weekend == 1) {
            $output .= '<li>'.$no_of_days.' '.$night_label.' ('.$with_weekend_label.') <span>'.$nights_total_price.'</span></li>';

        } elseif($booking_has_custom_pricing == 1) {
            $output .= '<li>'.$no_of_days.' '.$night_label.' ('.$local['with_custom_period_label'].') <span>'.$nights_total_price.'</span></li>';

        } else {
            $output .= '<li>'.$price_per_night.' x '.$no_of_days.' '.$night_label.' <span>'.$nights_total_price.'</span></li>';
        }

        if(!empty($additional_guests)) {
            $output .= '<li>'.$additional_guests.' '.$add_guest_label.' <span>'.homey_formatted_price($additional_guests_total_price).'</span></li>';
        }

        if(!empty($reservation_meta['cleaning_fee']) && $reservation_meta['cleaning_fee'] != 0) {
            $output .= '<li>'.$local['cs_cleaning_fee'].' <span>'.$cleaning_fee.'</span></li>';
        }

        if(!empty($extra_prices)) {
            $output .= $extra_prices['extra_html'];
        }

        if(!empty($reservation_meta['city_fee']) && $reservation_meta['city_fee'] != 0) {
            $output .= '<li>'.$local['cs_city_fee'].' <span>'.$city_fee.'</span></li>';
        }

        if(!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<li>'.$local['cs_sec_deposit'].' <span>'.homey_formatted_price($security_deposit).'</span></li>';
        }


        if(!empty($services_fee) && !$is_host) {
            $output .= '<li>'.$local['cs_services_fee'].' <span>'.homey_formatted_price($services_fee).'</span></li>';
        }

        if(!empty($extra_expenses)) {
            $output .= $extra_expenses['expenses_html'];
        }

        if(!empty($extra_discount)) {
            $output .= $extra_discount['discount_html'];
        }


        if(!empty($taxes) && $taxes != 0 ) {
            $output .= '<li>'.$local['cs_taxes'].' '.$taxes_percent.'% <span>'.homey_formatted_price($taxes).'</span></li>';
        }

        if(homey_option('reservation_payment') == 'full') {

            if($is_host && !empty($services_fee)) {
                $upfront_payment = $upfront_payment - $services_fee;
            }
            $output .= '<li class="payment-due">'.$local['inv_total'].' <span>'.homey_formatted_price($upfront_payment).'</span></li>';
            $output .= '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="'.$upfront_payment.'">';

        } else {
            if(!empty($upfront_payment) && $upfront_payment != 0) {
                if($is_host && !empty($services_fee)) {
                    $upfront_payment = $upfront_payment - $services_fee;
                }
                $output .= '<li class="payment-due">'.$local['cs_payment_due'].' <span>'.homey_formatted_price($upfront_payment).'</span></li>';
                $output .= '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="'.$upfront_payment.'">';
            }
        }

        if(!empty($balance) && $balance != 0) {
            $output .= '<li><i class="fa fa-info-circle"></i> '.$local['cs_pay_rest_1'].' '.homey_formatted_price($balance).' '.$local['cs_pay_rest_2'].'</li>';
        }


        $output .= '</ul>';
        $output .= '</div>';

        return $output;
    }
}

if( !function_exists('homey_calculate_booking_cost_admin') ) {
    function homey_calculate_booking_cost_admin($reservation_id, $collapse = false) {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        if(empty($reservation_id)) {
            return;
        }
        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);
        $extra_options = get_post_meta($reservation_id, 'extra_options', true);

        $listing_id     = intval($reservation_meta['listing_id']);
        $check_in_date  = wp_kses ( $reservation_meta['check_in_date'], $allowded_html );
        $check_out_date = wp_kses ( $reservation_meta['check_out_date'], $allowded_html );
        $guests         = intval($reservation_meta['guests']);

        $prices_array = homey_get_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);

        $price_per_night = homey_formatted_price($prices_array['price_per_night'], true);
        $no_of_days = $prices_array['days_count'];

        $nights_total_price = homey_formatted_price($prices_array['nights_total_price'], false);

        $cleaning_fee = homey_formatted_price($prices_array['cleaning_fee']);
        $services_fee = $prices_array['services_fee'];
        $taxes = $prices_array['taxes'];
        $taxes_percent = $prices_array['taxes_percent'];
        $city_fee = homey_formatted_price($prices_array['city_fee']);
        $security_deposit = $prices_array['security_deposit'];
        $additional_guests = $prices_array['additional_guests'];
        $additional_guests_price = $prices_array['additional_guests_price'];
        $additional_guests_total_price = $prices_array['additional_guests_total_price'];

        $upfront_payment = $prices_array['upfront_payment'];
        $balance = $prices_array['balance'];
        $total_price = $prices_array['total_price'];

        $booking_has_weekend = $prices_array['booking_has_weekend'];
        $booking_has_custom_pricing = $prices_array['booking_has_custom_pricing'];
        $with_weekend_label = $local['with_weekend_label'];

        if($no_of_days > 1) {
            $night_label = homey_option('glc_day_nights_label');
        } else {
            $night_label = homey_option('glc_day_night_label');
        }

        if($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }

        if($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
            $output .= '<tr>
                    <td class="manage-column">'.$no_of_days.' '.$night_label.' ('.$local['with_custom_period_and_weekend_label'].')</td>
                    <td>'.$nights_total_price.'</td>
                    </tr>';

        } elseif($booking_has_weekend == 1) {
            $output .= '<tr>
                <td class="manage-column">'.$no_of_days.' '.$night_label.' ('.$with_weekend_label.') </td>
                <td>'.$nights_total_price.'</td>
                </tr>';

        } elseif($booking_has_custom_pricing == 1) {
            $output .= '<tr>
                <td class="manage-column">'.$no_of_days.' '.$night_label.' ('.$local['with_custom_period_label'].') </td>
                <td>'.$nights_total_price.'</td>
                </tr>';

        } else {
            $output .= '<tr>
                <td class="manage-column">'.$price_per_night.' x '.$no_of_days.' '.$night_label.' </td>
                <td>'.$nights_total_price.'</td>
                </tr>';
        }

        if(!empty($additional_guests)) {
            $output .= '<tr><td class="manage-column">'.$additional_guests.' '.$add_guest_label.'</td> <td>'.homey_formatted_price($additional_guests_total_price).'</td></tr>';
        }

        $output .= '<tr><td class="manage-column">'.$local['cs_cleaning_fee'].'</td> <td>'.$cleaning_fee.'</td></tr>';
        $output .= '<tr><td class="manage-column">'.$local['cs_city_fee'].'</td> <td>'.$city_fee.'</td></tr>';

        if(!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<tr><td class="manage-column">'.$local['cs_sec_deposit'].'</td> <td>'.homey_formatted_price($security_deposit).'</td></tr>';
        }

        if(!empty($services_fee) && $services_fee != 0 ) {
            $output .= '<tr><td class="manage-column">'.$local['cs_services_fee'].'</td> <td>'.homey_formatted_price($services_fee).'</td></tr>';
        }

        if(!empty($taxes) && $taxes != 0 ) {
            $output .= '<tr><td class="manage-column">'.$local['cs_taxes'].' '.$taxes_percent.'%</td> <td>'.homey_formatted_price($taxes).'</td></tr>';
        }


        $output .= '<tr class="payment-due"><td class="manage-column"><strong>'.$local['cs_total'].'</strong></td> <td><strong>'.homey_formatted_price($total_price).'</strong></td></tr>';


        if(!empty($upfront_payment) && $upfront_payment != 0) {
            $output .= '<tr class="payment-due"><td class="manage-column"><strong>'.$local['cs_payment_due'].'</strong></td> <td><strong>'.homey_formatted_price($upfront_payment).'</strong></td></tr>';
        }

        if(!empty($balance) && $balance != 0) {
            $output .= '<tr><td class="manage-column"><i class="fa fa-info-circle"></i> '.$local['cs_pay_rest_1'].' <strong>'.homey_formatted_price($balance).'</strong> '.$local['cs_pay_rest_2'].'</td></tr>';
        }



        return $output;
    }
}

if( !function_exists('homey_calculate_reservation_cost_admin') ) {
    function homey_calculate_reservation_cost_admin($reservation_id, $collapse = false) {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        if(empty($reservation_id)) {
            return;
        }

        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);
        $listing_id     = intval($reservation_meta['listing_id']);
        $booking_type = homey_booking_type_by_id($listing_id);

        if( $booking_type == 'per_week' ) {
            return homey_calculate_reservation_cost_admin_weekly($reservation_id);
        } else if( $booking_type == 'per_month' ) {
            return homey_calculate_reservation_cost_admin_monthly($reservation_id);
        } else if( $booking_type == 'per_day_date' ) {
            return homey_calculate_reservation_cost_admin_per_day($reservation_id);
        } else {
            return homey_calculate_reservation_cost_admin_nightly($reservation_id);
        }


    }
}

if( !function_exists('homey_calculate_reservation_cost_admin_weekly') ) {
    function homey_calculate_reservation_cost_admin_weekly($reservation_id, $collapse = false) {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        if(empty($reservation_id)) {
            return;
        }
        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);

        $listing_id     = intval($reservation_meta['listing_id']);
        $check_in_date  = wp_kses ( $reservation_meta['check_in_date'], $allowded_html );
        $check_out_date = wp_kses ( $reservation_meta['check_out_date'], $allowded_html );
        $guests         = intval($reservation_meta['guests']);


        $price_per_week = homey_formatted_price($reservation_meta['price_per_week'], true);
        $no_of_days = $reservation_meta['no_of_days'];
        $no_of_weeks = $reservation_meta['total_weeks_count'];

        $weeks_total_price = homey_formatted_price($reservation_meta['weeks_total_price'], false);

        $cleaning_fee = homey_formatted_price($reservation_meta['cleaning_fee']);
        $services_fee = $reservation_meta['services_fee'];
        $taxes = $reservation_meta['taxes'];
        $taxes_percent = $reservation_meta['taxes_percent'];
        $city_fee = homey_formatted_price($reservation_meta['city_fee']);
        $security_deposit = $reservation_meta['security_deposit'];
        $additional_guests = $reservation_meta['additional_guests'];
        $additional_guests_price = $reservation_meta['additional_guests_price'];
        $additional_guests_total_price = $reservation_meta['additional_guests_total_price'];

        $upfront_payment = $reservation_meta['upfront'];
        $balance = $reservation_meta['balance'];
        $total_price = $reservation_meta['total'];

        $booking_has_weekend = $reservation_meta['booking_has_weekend'];
        $booking_has_custom_pricing = $reservation_meta['booking_has_custom_pricing'];
        $with_weekend_label = $local['with_weekend_label'];

        if($no_of_days > 1) {
            $night_label = homey_option('glc_day_nights_label');
        } else {
            $night_label = homey_option('glc_day_night_label');
        }

        if($no_of_weeks > 1) {
            $week_label = homey_option('glc_weeks_label');
        } else {
            $week_label = homey_option('glc_week_label');
        }

        if($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }


        $output .= '<tr>
            <td class="manage-column">'.$price_per_week.' x '.$no_of_weeks.' '.$week_label.' ';

        if( $no_of_days > 0 ) {
            $output .= ' '.esc_html__('and', 'homey').' '.esc_attr($no_of_days).' '.esc_attr($night_label);
        }

        $output .= '</td>';
        $output .= '<td>'.$weeks_total_price.'</td>
            </tr>';

        if(!empty($additional_guests)) {
            $output .= '<tr><td class="manage-column">'.$additional_guests.' '.$add_guest_label.'</td> <td>'.homey_formatted_price($additional_guests_total_price).'</td></tr>';
        }

        $output .= '<tr><td class="manage-column">'.$local['cs_cleaning_fee'].'</td> <td>'.$cleaning_fee.'</td></tr>';
        $output .= '<tr><td class="manage-column">'.$local['cs_city_fee'].'</td> <td>'.$city_fee.'</td></tr>';

        if(!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<tr><td class="manage-column">'.$local['cs_sec_deposit'].'</td> <td>'.homey_formatted_price($security_deposit).'</td></tr>';
        }

        if(!empty($services_fee) && $services_fee != 0 ) {
            $output .= '<tr><td class="manage-column">'.$local['cs_services_fee'].'</td> <td>'.homey_formatted_price($services_fee).'</td></tr>';
        }

        if(!empty($taxes) && $taxes != 0 ) {
            $output .= '<tr><td class="manage-column">'.$local['cs_taxes'].' '.$taxes_percent.'%</td> <td>'.homey_formatted_price($taxes).'</td></tr>';
        }


        $output .= '<tr class="payment-due"><td class="manage-column"><strong>'.$local['cs_total'].'</strong></td> <td><strong>'.homey_formatted_price($total_price).'</strong></td></tr>';


        if(!empty($upfront_payment) && $upfront_payment != 0) {
            $output .= '<tr class="payment-due"><td class="manage-column"><strong>'.$local['cs_payment_due'].'</strong></td> <td><strong>'.homey_formatted_price($upfront_payment).'</strong></td></tr>';
        }

        if(!empty($balance) && $balance != 0) {
            $output .= '<tr><td class="manage-column"><i class="fa fa-info-circle"></i> '.$local['cs_pay_rest_1'].' <strong>'.homey_formatted_price($balance).'</strong> '.$local['cs_pay_rest_2'].'</td></tr>';
        }



        return $output;
    }
}

if( !function_exists('homey_calculate_reservation_cost_admin_monthly') ) {
    function homey_calculate_reservation_cost_admin_monthly($reservation_id, $collapse = false) {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        if(empty($reservation_id)) {
            return;
        }
        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);

        $listing_id     = intval($reservation_meta['listing_id']);
        $check_in_date  = wp_kses ( $reservation_meta['check_in_date'], $allowded_html );
        $check_out_date = wp_kses ( $reservation_meta['check_out_date'], $allowded_html );
        $guests         = intval($reservation_meta['guests']);


        $price_per_week = homey_formatted_price($reservation_meta['price_per_month'], true);
        $no_of_days = $reservation_meta['no_of_days'];
        $no_of_weeks = $reservation_meta['total_months_count'];

        $weeks_total_price = homey_formatted_price($reservation_meta['months_total_price'], false);

        $cleaning_fee = homey_formatted_price($reservation_meta['cleaning_fee']);
        $services_fee = $reservation_meta['services_fee'];
        $taxes = $reservation_meta['taxes'];
        $taxes_percent = $reservation_meta['taxes_percent'];
        $city_fee = homey_formatted_price($reservation_meta['city_fee']);
        $security_deposit = $reservation_meta['security_deposit'];
        $additional_guests = $reservation_meta['additional_guests'];
        $additional_guests_price = $reservation_meta['additional_guests_price'];
        $additional_guests_total_price = $reservation_meta['additional_guests_total_price'];

        $upfront_payment = $reservation_meta['upfront'];
        $balance = $reservation_meta['balance'];
        $total_price = $reservation_meta['total'];

        $booking_has_weekend = $reservation_meta['booking_has_weekend'];
        $booking_has_custom_pricing = $reservation_meta['booking_has_custom_pricing'];
        $with_weekend_label = $local['with_weekend_label'];

        if($no_of_days > 1) {
            $night_label = homey_option('glc_day_nights_label');
        } else {
            $night_label = homey_option('glc_day_night_label');
        }

        if($no_of_weeks > 1) {
            $week_label = homey_option('glc_months_label');
        } else {
            $week_label = homey_option('glc_month_label');
        }

        if($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }


        $output .= '<tr>
            <td class="manage-column">'.$price_per_week.' x '.$no_of_weeks.' '.$week_label.' ';

        if( $no_of_days > 0 ) {
            $output .= ' '.esc_html__('and', 'homey').' '.esc_attr($no_of_days).' '.esc_attr($night_label);
        }

        $output .= '</td>';
        $output .= '<td>'.$weeks_total_price.'</td>
            </tr>';

        if(!empty($additional_guests)) {
            $output .= '<tr><td class="manage-column">'.$additional_guests.' '.$add_guest_label.'</td> <td>'.homey_formatted_price($additional_guests_total_price).'</td></tr>';
        }

        $output .= '<tr><td class="manage-column">'.$local['cs_cleaning_fee'].'</td> <td>'.$cleaning_fee.'</td></tr>';
        $output .= '<tr><td class="manage-column">'.$local['cs_city_fee'].'</td> <td>'.$city_fee.'</td></tr>';

        if(!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<tr><td class="manage-column">'.$local['cs_sec_deposit'].'</td> <td>'.homey_formatted_price($security_deposit).'</td></tr>';
        }

        if(!empty($services_fee) && $services_fee != 0 ) {
            $output .= '<tr><td class="manage-column">'.$local['cs_services_fee'].'</td> <td>'.homey_formatted_price($services_fee).'</td></tr>';
        }

        if(!empty($taxes) && $taxes != 0 ) {
            $output .= '<tr><td class="manage-column">'.$local['cs_taxes'].' '.$taxes_percent.'%</td> <td>'.homey_formatted_price($taxes).'</td></tr>';
        }


        $output .= '<tr class="payment-due"><td class="manage-column"><strong>'.$local['cs_total'].'</strong></td> <td><strong>'.homey_formatted_price($total_price).'</strong></td></tr>';


        if(!empty($upfront_payment) && $upfront_payment != 0) {
            $output .= '<tr class="payment-due"><td class="manage-column"><strong>'.$local['cs_payment_due'].'</strong></td> <td><strong>'.homey_formatted_price($upfront_payment).'</strong></td></tr>';
        }

        if(!empty($balance) && $balance != 0) {
            $output .= '<tr><td class="manage-column"><i class="fa fa-info-circle"></i> '.$local['cs_pay_rest_1'].' <strong>'.homey_formatted_price($balance).'</strong> '.$local['cs_pay_rest_2'].'</td></tr>';
        }



        return $output;
    }
}

if( !function_exists('homey_calculate_reservation_cost_admin_nightly') ) {
    function homey_calculate_reservation_cost_admin_nightly($reservation_id, $collapse = false) {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        if(empty($reservation_id)) {
            return;
        }
        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);

        $listing_id     = intval($reservation_meta['listing_id']);
        $check_in_date  = wp_kses ( $reservation_meta['check_in_date'], $allowded_html );
        $check_out_date = wp_kses ( $reservation_meta['check_out_date'], $allowded_html );
        $guests         = intval($reservation_meta['guests']);


        $price_per_night = isset($reservation_meta['price_per_night']) ? homey_formatted_price($reservation_meta['price_per_night'], true) : homey_formatted_price(0, true);
        $no_of_days = $reservation_meta['no_of_days'];

        $nights_total_price = isset($reservation_meta['nights_total_price']) ? homey_formatted_price($reservation_meta['nights_total_price'], true) : homey_formatted_price(0, true);

        $cleaning_fee = isset($reservation_meta['cleaning_fee']) ? homey_formatted_price($reservation_meta['cleaning_fee'], true) : homey_formatted_price(0, true);
        $services_fee = isset($reservation_meta['services_fee']) ? $reservation_meta['services_fee'] : '';
        $taxes = isset($reservation_meta['taxes']) ? $reservation_meta['taxes'] : '';
        $taxes_percent = isset($reservation_meta['taxes_percent']) ? $reservation_meta['taxes_percent'] : '';
        $city_fee = isset($reservation_meta['city_fee']) ? $reservation_meta['city_fee'] : '';
        $security_deposit = isset($reservation_meta['security_deposit']) ? $reservation_meta['security_deposit'] : '';
        $additional_guests = isset($reservation_meta['additional_guests']) ? $reservation_meta['additional_guests'] : '';
        $additional_guests_price = isset($reservation_meta['additional_guests_price']) ? $reservation_meta['additional_guests_price'] : '';
        $additional_guests_total_price = isset($reservation_meta['additional_guests_total_price']) ? $reservation_meta['additional_guests_total_price'] : '';

        $upfront_payment = $reservation_meta['upfront'];
        $balance = $reservation_meta['balance'];
        $total_price = $reservation_meta['total'];

        $booking_has_weekend = isset($reservation_meta['booking_has_weekend']) ? $reservation_meta['booking_has_weekend'] : '';
        $booking_has_custom_pricing = isset($reservation_meta['booking_has_custom_pricing']) ? $reservation_meta['booking_has_custom_pricing'] : '';
        $with_weekend_label = $local['with_weekend_label'];

        if($no_of_days > 1) {
            $night_label = homey_option('glc_day_nights_label');
        } else {
            $night_label = homey_option('glc_day_night_label');
        }

        if($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }

        if($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
            $output .= '<tr>
                    <td class="manage-column">'.$no_of_days.' '.$night_label.' ('.$local['with_custom_period_and_weekend_label'].')</td>
                    <td>'.$nights_total_price.'</td>
                    </tr>';

        } elseif($booking_has_weekend == 1) {
            $output .= '<tr>
                <td class="manage-column">'.$no_of_days.' '.$night_label.' ('.$with_weekend_label.') </td>
                <td>'.$nights_total_price.'</td>
                </tr>';

        } elseif($booking_has_custom_pricing == 1) {
            $output .= '<tr>
                <td class="manage-column">'.$no_of_days.' '.$night_label.' ('.$local['with_custom_period_label'].') </td>
                <td>'.$nights_total_price.'</td>
                </tr>';

        } else {
            $output .= '<tr>
                <td class="manage-column">'.$price_per_night.' x '.$no_of_days.' '.$night_label.' </td>
                <td>'.$nights_total_price.'</td>
                </tr>';
        }

        if(!empty($additional_guests)) {
            $output .= '<tr><td class="manage-column">'.$additional_guests.' '.$add_guest_label.'</td> <td>'.homey_formatted_price($additional_guests_total_price).'</td></tr>';
        }

        $output .= '<tr><td class="manage-column">'.$local['cs_cleaning_fee'].'</td> <td>'.$cleaning_fee.'</td></tr>';
        $output .= '<tr><td class="manage-column">'.$local['cs_city_fee'].'</td> <td>'.$city_fee.'</td></tr>';

        if(!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<tr><td class="manage-column">'.$local['cs_sec_deposit'].'</td> <td>'.homey_formatted_price($security_deposit).'</td></tr>';
        }

        if(!empty($services_fee) && $services_fee != 0 ) {
            $output .= '<tr><td class="manage-column">'.$local['cs_services_fee'].'</td> <td>'.homey_formatted_price($services_fee).'</td></tr>';
        }

        if(!empty($taxes) && $taxes != 0 ) {
            $output .= '<tr><td class="manage-column">'.$local['cs_taxes'].' '.$taxes_percent.'%</td> <td>'.homey_formatted_price($taxes).'</td></tr>';
        }


        $output .= '<tr class="payment-due"><td class="manage-column"><strong>'.$local['cs_total'].'</strong></td> <td><strong>'.homey_formatted_price($total_price).'</strong></td></tr>';


        if(!empty($upfront_payment) && $upfront_payment != 0) {
            $output .= '<tr class="payment-due"><td class="manage-column"><strong>'.$local['cs_payment_due'].'</strong></td> <td><strong>'.homey_formatted_price($upfront_payment).'</strong></td></tr>';
        }

        if(!empty($balance) && $balance != 0) {
            $output .= '<tr><td class="manage-column"><i class="fa fa-info-circle"></i> '.$local['cs_pay_rest_1'].' <strong>'.homey_formatted_price($balance).'</strong> '.$local['cs_pay_rest_2'].'</td></tr>';
        }



        return $output;
    }
}

if( !function_exists('homey_calculate_reservation_cost_admin_per_day') ) {
    function homey_calculate_reservation_cost_admin_per_day($reservation_id, $collapse = false) {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        if(empty($reservation_id)) {
            return;
        }
        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);

        $listing_id     = intval($reservation_meta['listing_id']);
        $check_in_date  = wp_kses ( $reservation_meta['check_in_date'], $allowded_html );
        $check_out_date = wp_kses ( $reservation_meta['check_out_date'], $allowded_html );
        $guests         = intval($reservation_meta['guests']);


        $price_per_night = isset($reservation_meta['price_per_day_date']) ? homey_formatted_price($reservation_meta['price_per_day_date'], true) : homey_formatted_price(0, true);
        $no_of_days = $reservation_meta['no_of_days'];

        $nights_total_price = isset($reservation_meta['days_total_price']) ? homey_formatted_price($reservation_meta['days_total_price'], true) : homey_formatted_price(0, true);

        $cleaning_fee = isset($reservation_meta['cleaning_fee']) ? homey_formatted_price($reservation_meta['cleaning_fee'], true) : homey_formatted_price(0, true);
        $services_fee = isset($reservation_meta['services_fee']) ? $reservation_meta['services_fee'] : '';
        $taxes = isset($reservation_meta['taxes']) ? $reservation_meta['taxes'] : '';
        $taxes_percent = isset($reservation_meta['taxes_percent']) ? $reservation_meta['taxes_percent'] : '';
        $city_fee = isset($reservation_meta['city_fee']) ? $reservation_meta['city_fee'] : '';
        $security_deposit = isset($reservation_meta['security_deposit']) ? $reservation_meta['security_deposit'] : '';
        $additional_guests = isset($reservation_meta['additional_guests']) ? $reservation_meta['additional_guests'] : '';
        $additional_guests_price = isset($reservation_meta['additional_guests_price']) ? $reservation_meta['additional_guests_price'] : '';
        $additional_guests_total_price = isset($reservation_meta['additional_guests_total_price']) ? $reservation_meta['additional_guests_total_price'] : '';

        $upfront_payment = $reservation_meta['upfront'];
        $balance = $reservation_meta['balance'];
        $total_price = $reservation_meta['total'];

        $booking_has_weekend = isset($reservation_meta['booking_has_weekend']) ? $reservation_meta['booking_has_weekend'] : '';
        $booking_has_custom_pricing = isset($reservation_meta['booking_has_custom_pricing']) ? $reservation_meta['booking_has_custom_pricing'] : '';
        $with_weekend_label = $local['with_weekend_label'];

        if($no_of_days > 1) {
            $night_label = homey_option('glc_day_dates_label');
        } else {
            $night_label = homey_option('glc_day_date_label');
        }

        if($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }

        if($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
            $output .= '<tr>
                    <td class="manage-column">'.$no_of_days.' '.$night_label.' ('.$local['with_custom_period_and_weekend_label'].')</td>
                    <td>'.$nights_total_price.'</td>
                    </tr>';

        } elseif($booking_has_weekend == 1) {
            $output .= '<tr>
                <td class="manage-column">'.$no_of_days.' '.$night_label.' ('.$with_weekend_label.') </td>
                <td>'.$nights_total_price.'</td>
                </tr>';

        } elseif($booking_has_custom_pricing == 1) {
            $output .= '<tr>
                <td class="manage-column">'.$no_of_days.' '.$night_label.' ('.$local['with_custom_period_label'].') </td>
                <td>'.$nights_total_price.'</td>
                </tr>';

        } else {
            $output .= '<tr>
                <td class="manage-column">'.$price_per_night.' x '.$no_of_days.' '.$night_label.' </td>
                <td>'.$nights_total_price.'</td>
                </tr>';
        }

        if(!empty($additional_guests)) {
            $output .= '<tr><td class="manage-column">'.$additional_guests.' '.$add_guest_label.'</td> <td>'.homey_formatted_price($additional_guests_total_price).'</td></tr>';
        }

        $output .= '<tr><td class="manage-column">'.$local['cs_cleaning_fee'].'</td> <td>'.$cleaning_fee.'</td></tr>';
        $output .= '<tr><td class="manage-column">'.$local['cs_city_fee'].'</td> <td>'.$city_fee.'</td></tr>';

        if(!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<tr><td class="manage-column">'.$local['cs_sec_deposit'].'</td> <td>'.homey_formatted_price($security_deposit).'</td></tr>';
        }

        if(!empty($services_fee) && $services_fee != 0 ) {
            $output .= '<tr><td class="manage-column">'.$local['cs_services_fee'].'</td> <td>'.homey_formatted_price($services_fee).'</td></tr>';
        }

        if(!empty($taxes) && $taxes != 0 ) {
            $output .= '<tr><td class="manage-column">'.$local['cs_taxes'].' '.$taxes_percent.'%</td> <td>'.homey_formatted_price($taxes).'</td></tr>';
        }


        $output .= '<tr class="payment-due"><td class="manage-column"><strong>'.$local['cs_total'].'</strong></td> <td><strong>'.homey_formatted_price($total_price).'</strong></td></tr>';


        if(!empty($upfront_payment) && $upfront_payment != 0) {
            $output .= '<tr class="payment-due"><td class="manage-column"><strong>'.$local['cs_payment_due'].'</strong></td> <td><strong>'.homey_formatted_price($upfront_payment).'</strong></td></tr>';
        }

        if(!empty($balance) && $balance != 0) {
            $output .= '<tr><td class="manage-column"><i class="fa fa-info-circle"></i> '.$local['cs_pay_rest_1'].' <strong>'.homey_formatted_price($balance).'</strong> '.$local['cs_pay_rest_2'].'</td></tr>';
        }



        return $output;
    }
}

if( !function_exists('homey_calculate_reservation_cost_admin_1_5_3') ) {
    function homey_calculate_reservation_cost_admin_1_5_3($reservation_id, $collapse = false) {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        if(empty($reservation_id)) {
            return;
        }
        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);

        $listing_id     = intval($reservation_meta['listing_id']);
        $check_in_date  = wp_kses ( $reservation_meta['check_in_date'], $allowded_html );
        $check_out_date = wp_kses ( $reservation_meta['check_out_date'], $allowded_html );
        $guests         = intval($reservation_meta['guests']);


        $price_per_night = homey_formatted_price($reservation_meta['price_per_night'], true);
        $no_of_days = $reservation_meta['no_of_days'];

        $nights_total_price = homey_formatted_price($reservation_meta['nights_total_price'], false);

        $cleaning_fee = homey_formatted_price($reservation_meta['cleaning_fee']);
        $services_fee = $reservation_meta['services_fee'];
        $taxes = $reservation_meta['taxes'];
        $taxes_percent = $reservation_meta['taxes_percent'];
        $city_fee = homey_formatted_price($reservation_meta['city_fee']);
        $security_deposit = $reservation_meta['security_deposit'];
        $additional_guests = $reservation_meta['additional_guests'];
        $additional_guests_price = $reservation_meta['additional_guests_price'];
        $additional_guests_total_price = $reservation_meta['additional_guests_total_price'];

        $upfront_payment = $reservation_meta['upfront'];
        $balance = $reservation_meta['balance'];
        $total_price = $reservation_meta['total'];

        $booking_has_weekend = $reservation_meta['booking_has_weekend'];
        $booking_has_custom_pricing = $reservation_meta['booking_has_custom_pricing'];
        $with_weekend_label = $local['with_weekend_label'];

        if($no_of_days > 1) {
            $night_label = homey_option('glc_day_nights_label');
        } else {
            $night_label = homey_option('glc_day_night_label');
        }

        if($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }

        if($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
            $output .= '<tr>
                    <td class="manage-column">'.$no_of_days.' '.$night_label.' ('.$local['with_custom_period_and_weekend_label'].')</td>
                    <td>'.$nights_total_price.'</td>
                    </tr>';

        } elseif($booking_has_weekend == 1) {
            $output .= '<tr>
                <td class="manage-column">'.$no_of_days.' '.$night_label.' ('.$with_weekend_label.') </td>
                <td>'.$nights_total_price.'</td>
                </tr>';

        } elseif($booking_has_custom_pricing == 1) {
            $output .= '<tr>
                <td class="manage-column">'.$no_of_days.' '.$night_label.' ('.$local['with_custom_period_label'].') </td>
                <td>'.$nights_total_price.'</td>
                </tr>';

        } else {
            $output .= '<tr>
                <td class="manage-column">'.$price_per_night.' x '.$no_of_days.' '.$night_label.' </td>
                <td>'.$nights_total_price.'</td>
                </tr>';
        }

        if(!empty($additional_guests)) {
            $output .= '<tr><td class="manage-column">'.$additional_guests.' '.$add_guest_label.'</td> <td>'.homey_formatted_price($additional_guests_total_price).'</td></tr>';
        }

        $output .= '<tr><td class="manage-column">'.$local['cs_cleaning_fee'].'</td> <td>'.$cleaning_fee.'</td></tr>';
        $output .= '<tr><td class="manage-column">'.$local['cs_city_fee'].'</td> <td>'.$city_fee.'</td></tr>';

        if(!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<tr><td class="manage-column">'.$local['cs_sec_deposit'].'</td> <td>'.homey_formatted_price($security_deposit).'</td></tr>';
        }

        if(!empty($services_fee) && $services_fee != 0 ) {
            $output .= '<tr><td class="manage-column">'.$local['cs_services_fee'].'</td> <td>'.homey_formatted_price($services_fee).'</td></tr>';
        }

        if(!empty($taxes) && $taxes != 0 ) {
            $output .= '<tr><td class="manage-column">'.$local['cs_taxes'].' '.$taxes_percent.'%</td> <td>'.homey_formatted_price($taxes).'</td></tr>';
        }


        $output .= '<tr class="payment-due"><td class="manage-column"><strong>'.$local['cs_total'].'</strong></td> <td><strong>'.homey_formatted_price($total_price).'</strong></td></tr>';


        if(!empty($upfront_payment) && $upfront_payment != 0) {
            $output .= '<tr class="payment-due"><td class="manage-column"><strong>'.$local['cs_payment_due'].'</strong></td> <td><strong>'.homey_formatted_price($upfront_payment).'</strong></td></tr>';
        }

        if(!empty($balance) && $balance != 0) {
            $output .= '<tr><td class="manage-column"><i class="fa fa-info-circle"></i> '.$local['cs_pay_rest_1'].' <strong>'.homey_formatted_price($balance).'</strong> '.$local['cs_pay_rest_2'].'</td></tr>';
        }



        return $output;
    }
}

if(!function_exists('homey_get_weekly_prices')) {
    function homey_get_weekly_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options = null) {
        $prefix = 'homey_';

        $enable_services_fee = homey_option('enable_services_fee');
        $enable_taxes = homey_option('enable_taxes');
        $offsite_payment = homey_option('off-site-payment');
        $reservation_payment_type = homey_option('reservation_payment');
        $booking_percent = homey_option('booking_percent');
        $tax_type = homey_option('tax_type');
        $apply_taxes_on_service_fee  =   homey_option('apply_taxes_on_service_fee');
        $taxes_percent_global  =   homey_option('taxes_percent');
        $single_listing_tax = get_post_meta($listing_id, 'homey_tax_rate', true);

        $period_price = get_post_meta($listing_id, 'homey_custom_period', true);
        if(empty($period_price)) {
            $period_price =  array();
        }

        $total_extra_services = 0;
        $extra_prices_html = '';
        $taxes_final = 0;
        $total_weeks_count = 0;
        $total_weeks_count_for_price = 0;
        $days_after_weeks = 0;
        $taxes_percent = 0;
        $total_price = 0;
        $taxable_amount = 0;
        $total_guests_price = 0;
        $upfront_payment = 0;
        $weeks_total_price = 0;
        $booking_has_weekend = 0;
        $booking_has_custom_pricing = 0;
        $balance = 0;
        $period_days = 0;
        $security_deposit = '';
        $additional_guests = '';
        $additional_guests_total_price = '';
        $services_fee_final = '';
        $taxes_fee_final = '';
        $prices_array = array();

        $listing_guests          = floatval( get_post_meta($listing_id, $prefix.'guests', true) );
        $weekly_price            = floatval( get_post_meta($listing_id, $prefix.'night_price', true));
        $price_per_week          = $weekly_price;
        $security_deposit        = floatval( get_post_meta($listing_id, $prefix.'security_deposit', true) );

        $cleaning_fee            = floatval( get_post_meta($listing_id, $prefix.'cleaning_fee', true) );
        $cleaning_fee_type       = get_post_meta($listing_id, $prefix.'cleaning_fee_type', true);

        $city_fee                = floatval( get_post_meta($listing_id, $prefix.'city_fee', true) );
        $city_fee_type           = get_post_meta($listing_id, $prefix.'city_fee_type', true);

        $extra_guests_price      = floatval( get_post_meta($listing_id, $prefix.'additional_guests_price', true) );
        $additional_guests_price = $extra_guests_price;

        $allow_additional_guests = get_post_meta($listing_id, $prefix.'allow_additional_guests', true);

        $check_in        =  new DateTime($check_in_date);
        $check_in_unix   =  $check_in->getTimestamp();
        $check_in_unix_first_day   =  $check_in->getTimestamp();
        $check_out       =  new DateTime($check_out_date);
        $check_out_unix  =  $check_out->getTimestamp();

        $time_difference = abs( strtotime($check_in_date) - strtotime($check_out_date) );
        $days_count      = $time_difference/86400;
        $days_count      = intval($days_count);

        if( isset($period_price[$check_in_unix]) && isset( $period_price[$check_in_unix]['night_price'] ) &&  $period_price[$check_in_unix]['night_price']!=0 ){
            $price_per_week = $period_price[$check_in_unix]['night_price'];

            $booking_has_custom_pricing = 1;
            $period_days = $period_days + 1;
        }


        $time_difference = abs( strtotime($check_in_date) - strtotime($check_out_date) );
        $days_count      = $time_difference/86400;
        $days_count      = intval($days_count);

        $total_weeks_count_for_price = $days_count / 7;

        $total_weeks_count = favethemes_datediff( 'ww', $check_in_date, $check_out_date );

        $days_after_weeks = $days_count % 7;

        $weeks_total_price = $price_per_week * $total_weeks_count_for_price;

        if( $total_weeks_count < 1 ) {
            $weeks_total_price = $price_per_week;
            $days_after_weeks = 0;
        }

        $total_price = $weeks_total_price;


        // Check additional guests price
        if( $allow_additional_guests == 'yes' && $guests > 0 && !empty($guests) ) {
            if( $guests > $listing_guests) {
                $additional_guests = $guests - $listing_guests;

                $guests_price_return = homey_calculate_guests_price($period_price, $check_in_unix, $additional_guests, $additional_guests_price);

                $total_guests_price = $total_guests_price + $guests_price_return;
            }
        }


        if( $cleaning_fee_type == 'daily' ) {
            $cleaning_fee = $cleaning_fee * $total_weeks_count_for_price;
            $total_price = $total_price + $cleaning_fee;
        } else {
            $total_price = $total_price + $cleaning_fee;
        }


        //Extra prices =======================================
        if($extra_options != '') {

            $extra_prices_output = '';
            foreach ($extra_options as $extra_price) {
                $ex_single_price = explode('|', $extra_price);

                $ex_name = $ex_single_price[0];
                $ex_price = $ex_single_price[1];
                $ex_type = $ex_single_price[2];

                if($ex_type == 'single_fee') {
                    $ex_price = $ex_price;

                } elseif($ex_type == 'per_night') {
                    $ex_price = $ex_price*$days_count;
                } elseif($ex_type == 'per_guest') {
                    $ex_price = $ex_price*$guests;
                } elseif($ex_type == 'per_night_per_guest') {
                    $ex_price = $ex_price* $days_count*$guests;
                }

                $total_extra_services = $total_extra_services + $ex_price;

                $extra_prices_output .= '<li>'.esc_attr($ex_name).'<span>'.homey_formatted_price($ex_price).'</span></li>';
            }

            $total_price = $total_price + $total_extra_services;
            $extra_prices_html = $extra_prices_output;
        }

        //Calculate taxes based of original price (Excluding city, security deposit etc)
        if($enable_taxes == 1) {

            if($tax_type == 'global_tax') {
                $taxes_percent = $taxes_percent_global;
            } else {
                if(!empty($single_listing_tax)) {
                    $taxes_percent = $single_listing_tax;
                }
            }

            $taxable_amount = $total_price + $total_guests_price;
            $taxes_final = homey_calculate_taxes($taxes_percent, $taxable_amount);
            $total_price = $total_price + $taxes_final;
        }


        //Calculate sevices fee based of original price (Excluding cleaning, city, sevices fee etc)
        if($enable_services_fee == 1 && $offsite_payment != 1) {
            $services_fee_type  = homey_option('services_fee_type');
            $services_fee  =   homey_option('services_fee');
            $price_for_services_fee = $total_price + $total_guests_price;
            $services_fee_final = homey_calculate_services_fee($services_fee_type, $services_fee, $price_for_services_fee);
            $total_price = $total_price + $services_fee_final;
        }


        if( $city_fee_type == 'daily' ) {
            $city_fee = $city_fee * $total_weeks_count_for_price;
            $total_price = $total_price + $city_fee;
        } else {
            $total_price = $total_price + $city_fee;
        }

        if(!empty($security_deposit) && $security_deposit != 0) {
            $total_price = $total_price + $security_deposit;
        }

        if($total_guests_price !=0) {
            $total_price = $total_price + $total_guests_price;
        }



        $listing_host_id = get_post_field( 'post_author', $listing_id );
        $host_reservation_payment_type = get_user_meta($listing_host_id, 'host_reservation_payment', true);
        $host_booking_percent = get_user_meta($listing_host_id, 'host_booking_percent', true);

        if($offsite_payment == 1 && !empty($host_reservation_payment_type)) {

            if($host_reservation_payment_type == 'percent') {
                if(!empty($host_booking_percent) && $host_booking_percent != 0) {
                    $upfront_payment = round($host_booking_percent*$total_price/100,2);
                }

            } elseif($host_reservation_payment_type == 'full') {
                $upfront_payment = $total_price;

            } elseif($host_reservation_payment_type == 'only_security') {
                $upfront_payment = $security_deposit;

            } elseif($host_reservation_payment_type == 'only_services') {
                $upfront_payment = $services_fee_final;

            } elseif($host_reservation_payment_type == 'services_security') {
                $upfront_payment = $security_deposit+$services_fee_final;
            }

        } else {

            if($reservation_payment_type == 'percent') {
                if(!empty($booking_percent) && $booking_percent != 0) {
                    $upfront_payment = round($booking_percent*$total_price/100,2);
                }

            } elseif($reservation_payment_type == 'full') {
                $upfront_payment = $total_price;

            } elseif($reservation_payment_type == 'only_security') {
                $upfront_payment = $security_deposit;

            } elseif($reservation_payment_type == 'only_services') {
                $upfront_payment = $services_fee_final;

            } elseif($reservation_payment_type == 'services_security') {
                $upfront_payment = $security_deposit+$services_fee_final;
            }
        }

        $balance = $total_price - $upfront_payment;

        $prices_array['price_per_week'] = $price_per_week;
        $prices_array['weeks_total_price'] = $weeks_total_price;
        $prices_array['total_price']     = $total_price;
        $prices_array['check_in_date']   = $check_in_date;
        $prices_array['check_out_date']  = $check_out_date;
        $prices_array['cleaning_fee']    = $cleaning_fee;
        $prices_array['city_fee']        = $city_fee;
        $prices_array['services_fee']    = $services_fee_final;
        $prices_array['days_count']      = $days_after_weeks;
        $prices_array['total_weeks_count']  = $total_weeks_count;
        $prices_array['taxes']           = $taxes_final;
        $prices_array['taxes_percent']   = $taxes_percent;
        $prices_array['security_deposit'] = $security_deposit;
        $prices_array['additional_guests'] = $additional_guests;
        $prices_array['additional_guests_price'] = $additional_guests_price;
        $prices_array['additional_guests_total_price'] = $total_guests_price;
        $prices_array['extra_prices_html'] = $extra_prices_html;
        $prices_array['balance'] = $balance;
        $prices_array['upfront_payment'] = $upfront_payment;

        return $prices_array;

    }
}

if(!function_exists('homey_get_monthly_prices')) {
    function homey_get_monthly_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options = null) {
        $prefix = 'homey_';

        $enable_services_fee = homey_option('enable_services_fee');
        $enable_taxes = homey_option('enable_taxes');
        $offsite_payment = homey_option('off-site-payment');
        $reservation_payment_type = homey_option('reservation_payment');
        $booking_percent = homey_option('booking_percent');
        $tax_type = homey_option('tax_type');
        $apply_taxes_on_service_fee  =   homey_option('apply_taxes_on_service_fee');
        $taxes_percent_global  =   homey_option('taxes_percent');
        $single_listing_tax = get_post_meta($listing_id, 'homey_tax_rate', true);

        $period_price = get_post_meta($listing_id, 'homey_custom_period', true);
        if(empty($period_price)) {
            $period_price =  array();
        }

        $total_extra_services = 0;
        $extra_prices_html = '';
        $taxes_final = 0;
        $months_total_price = 0;
        $total_months_count = 0;
        $price_per_month = 0;
        $taxable_amount = 0;
        $total_months_count_for_price = 0;
        $days_after_months_price = 0;
        $days_after_months = 0;
        $taxes_percent = 0;
        $total_price = 0;
        $total_months_price = 0;
        $total_guests_price = 0;
        $upfront_payment = 0;
        $weeks_total_price = 0;
        $booking_has_weekend = 0;
        $booking_has_custom_pricing = 0;
        $balance = 0;
        $period_days = 0;
        $security_deposit = '';
        $additional_guests = '';
        $additional_guests_total_price = '';
        $services_fee_final = '';
        $taxes_fee_final = '';
        $prices_array = array();

        $listing_guests          = floatval( get_post_meta($listing_id, $prefix.'guests', true) );
        $monthly_price           = floatval( get_post_meta($listing_id, $prefix.'night_price', true));
        $price_per_month         = $monthly_price;
        $security_deposit        = floatval( get_post_meta($listing_id, $prefix.'security_deposit', true) );

        $cleaning_fee            = floatval( get_post_meta($listing_id, $prefix.'cleaning_fee', true) );
        $cleaning_fee_type       = get_post_meta($listing_id, $prefix.'cleaning_fee_type', true);

        $city_fee                = floatval( get_post_meta($listing_id, $prefix.'city_fee', true) );
        $city_fee_type           = get_post_meta($listing_id, $prefix.'city_fee_type', true);

        $extra_guests_price      = floatval( get_post_meta($listing_id, $prefix.'additional_guests_price', true) );
        $additional_guests_price = $extra_guests_price;

        $allow_additional_guests = get_post_meta($listing_id, $prefix.'allow_additional_guests', true);

        $check_in        =  new DateTime($check_in_date);
        $check_in_unix   =  $check_in->getTimestamp();
        $check_in_unix_first_day   =  $check_in->getTimestamp();
        $check_out       =  new DateTime($check_out_date);
        $check_out_unix  =  $check_out->getTimestamp();

        $time_difference = abs( strtotime($check_in_date) - strtotime($check_out_date) );
        $days_count      = $time_difference/86400;
        $days_count      = intval($days_count);

        $years = floor($time_difference / (365*60*60*24));
        $total_months_count = floor(($time_difference - $years * 365*60*60*24) / (30*60*60*24));
        $days_after_months = floor(($time_difference - $years * 365*60*60*24 - $total_months_count*30*60*60*24)/ (60*60*24));

        if($years > 0) {
            $years_months = $years * 12;
            $total_months_count = $total_months_count + $years_months;
        }

        $total_months_count_for_price = $days_count / 30;
        $days_after_months = $days_count % 30;

        $months_total_price = $price_per_month * $total_months_count_for_price;

        if( $total_months_count < 1 ) {
            $months_total_price = $price_per_month;
            $days_after_months = 0;
        }

        $total_price = $months_total_price;


        // Check additional guests price
        if( $allow_additional_guests == 'yes' && $guests > 0 && !empty($guests) ) {
            if( $guests > $listing_guests) {
                $additional_guests = $guests - $listing_guests;

                $guests_price_return = homey_calculate_guests_price($period_price, $check_in_unix, $additional_guests, $additional_guests_price);

                //zahid k calculation for extra months and days
                $divider_to_find_day_fee = (int) 30 / $days_after_months;
                $ex_price_for_days = ($guests_price_return / $divider_to_find_day_fee) * $days_after_months;
                //echo  'beo'.$guests_price_return .'+'. $ex_price_for_days;
                $guests_price_return = $guests_price_return + $ex_price_for_days;
                //add per month price
                //echo ' amaze '.$guests_price_return ."*". $total_months_count.' << ';
                $guests_price_return = $guests_price_return * $total_months_count;
                //zahid k calculation for extra months and days

                $total_guests_price = $total_guests_price + $guests_price_return;
            }
        }


        if( $cleaning_fee_type == 'daily' ) {
            $cleaning_fee = $cleaning_fee * $total_months_count_for_price;
            $total_price = $total_price + $cleaning_fee;
        } else {
            $total_price = $total_price + $cleaning_fee;
        }


        //Extra prices =======================================
        if($extra_options != '') {
            $multiply_factor = $total_months_count > 0 ? $total_months_count : $days_count;
            $extra_prices_output = '';
            foreach ($extra_options as $extra_price) {
                $ex_single_price = explode('|', $extra_price);
                $ex_name = $ex_single_price[0];
                $ex_price = $ex_single_price[1];
                $ex_type = $ex_single_price[2];
                if($ex_type == 'single_fee') {
                    $ex_price = $ex_price;
                } elseif($ex_type == 'per_night') {
                    $ex_price = $ex_price * $multiply_factor;
                    if($days_after_months > 0){
                        $divider_to_find_day_fee = (int) 30 / $days_after_months;
                        $ex_price_for_days = ($ex_price / $divider_to_find_day_fee) * $days_after_months;
                        //echo ' days prices wih month';
                        $ex_price = $ex_price + $ex_price_for_days;
                    }
                } elseif($ex_type == 'per_guest') {
                    $ex_price = $ex_price * $guests;
                } elseif($ex_type == 'per_night_per_guest') {
                    $ex_price = $ex_price * $multiply_factor*$guests;
                    if($days_after_months > 0){
                        $divider_to_find_day_fee = (int) 30 / $days_after_months;
                        $ex_price_for_days += ($ex_price / $divider_to_find_day_fee) * $days_after_months * $guests;
                        //echo ' days prices with month and guest';
                        $ex_price = $ex_price + $ex_price_for_days;
                    }
                }
                $total_extra_services = $total_extra_services + $ex_price;
                $extra_prices_output .= '<li>'.esc_attr($ex_name).'<span>'.homey_formatted_price($ex_price).'</span></li>';
            }
            $total_price = $total_price + $total_extra_services;
            $extra_prices_html = $extra_prices_output;
        }
        //Calculate taxes based of original price (Excluding city, security deposit etc)
        if($enable_taxes == 1) {

            if($tax_type == 'global_tax') {
                $taxes_percent = $taxes_percent_global;
            } else {
                if(!empty($single_listing_tax)) {
                    $taxes_percent = $single_listing_tax;
                }
            }

            $taxable_amount = $total_price + $total_guests_price;
            $taxes_final = homey_calculate_taxes($taxes_percent, $taxable_amount);
            $total_price = $total_price + $taxes_final;
        }


        //Calculate sevices fee based of original price (Excluding cleaning, city, sevices fee etc)
        if($enable_services_fee == 1 && $offsite_payment != 1) {
            $services_fee_type  = homey_option('services_fee_type');
            $services_fee  =   homey_option('services_fee');
            $price_for_services_fee = $total_price + $total_guests_price;
            $services_fee_final = homey_calculate_services_fee($services_fee_type, $services_fee, $price_for_services_fee);
            $total_price = $total_price + $services_fee_final;
        }


        if( $city_fee_type == 'daily' ) {
            $city_fee = $city_fee * $total_months_count_for_price;
            $total_price = $total_price + $city_fee;
        } else {
            $total_price = $total_price + $city_fee;
        }

        if(!empty($security_deposit) && $security_deposit != 0) {
            $total_price = $total_price + $security_deposit;
        }

        if($total_guests_price !=0) {
            $total_price = $total_price + $total_guests_price;
        }



        $listing_host_id = get_post_field( 'post_author', $listing_id );
        $host_reservation_payment_type = get_user_meta($listing_host_id, 'host_reservation_payment', true);
        $host_booking_percent = get_user_meta($listing_host_id, 'host_booking_percent', true);

        if($offsite_payment == 1 && !empty($host_reservation_payment_type)) {

            if($host_reservation_payment_type == 'percent') {
                if(!empty($host_booking_percent) && $host_booking_percent != 0) {
                    $upfront_payment = round($host_booking_percent*$total_price/100,2);
                }

            } elseif($host_reservation_payment_type == 'full') {
                $upfront_payment = $total_price;

            } elseif($host_reservation_payment_type == 'only_security') {
                $upfront_payment = $security_deposit;

            } elseif($host_reservation_payment_type == 'only_services') {
                $upfront_payment = $services_fee_final;

            } elseif($host_reservation_payment_type == 'services_security') {
                $upfront_payment = $security_deposit+$services_fee_final;
            }

        } else {

            if($reservation_payment_type == 'percent') {
                if(!empty($booking_percent) && $booking_percent != 0) {
                    $upfront_payment = round($booking_percent*$total_price/100,2);
                }

            } elseif($reservation_payment_type == 'full') {
                $upfront_payment = $total_price;

            } elseif($reservation_payment_type == 'only_security') {
                $upfront_payment = $security_deposit;

            } elseif($reservation_payment_type == 'only_services') {
                $upfront_payment = $services_fee_final;

            } elseif($reservation_payment_type == 'services_security') {
                $upfront_payment = $security_deposit+$services_fee_final;
            }
        }

        $balance = $total_price - $upfront_payment;

        $prices_array['price_per_month'] = $price_per_month;
        $prices_array['months_total_price'] = $months_total_price;
        $prices_array['total_months_count'] = $total_months_count;

        $prices_array['total_price']     = $total_price;
        $prices_array['check_in_date']   = $check_in_date;
        $prices_array['check_out_date']  = $check_out_date;
        $prices_array['cleaning_fee']    = $cleaning_fee;
        $prices_array['city_fee']        = $city_fee;
        $prices_array['services_fee']    = $services_fee_final;
        $prices_array['days_count']      = $days_after_months;
        $prices_array['taxes']           = $taxes_final;
        $prices_array['taxes_percent']   = $taxes_percent;
        $prices_array['security_deposit'] = $security_deposit;
        $prices_array['additional_guests'] = $additional_guests;
        $prices_array['additional_guests_price'] = $additional_guests_price;
        $prices_array['additional_guests_total_price'] = $total_guests_price;
        $prices_array['extra_prices_html'] = $extra_prices_html;
        $prices_array['balance'] = $balance;
        $prices_array['upfront_payment'] = $upfront_payment;

        return $prices_array;

    }
}



if(!function_exists('homey_get_day_date_prices')) {
    function homey_get_day_date_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options = null) {
        $prefix = 'homey_';

        $enable_services_fee = homey_option('enable_services_fee');
        $enable_taxes = homey_option('enable_taxes');
        $offsite_payment = homey_option('off-site-payment');
        $reservation_payment_type = homey_option('reservation_payment');
        $booking_percent = homey_option('booking_percent');
        $tax_type = homey_option('tax_type');
        $apply_taxes_on_service_fee  =   homey_option('apply_taxes_on_service_fee');
        $taxes_percent_global  =   homey_option('taxes_percent');
        $single_listing_tax = get_post_meta($listing_id, 'homey_tax_rate', true);

        $period_price = get_post_meta($listing_id, 'homey_custom_period', true);

        if(empty($period_price)) {
            $period_price =  array();
        }

        $total_extra_services = 0;
        $extra_prices_html = "";
        $taxes_final = 0;
        $taxes_percent = 0;
        $total_price = 0;
        $total_guests_price = 0;
        $upfront_payment = 0;
        $nights_total_price = 0;
        $booking_has_weekend = 0;
        $booking_has_custom_pricing = 0;
        $balance = 0;
        $taxable_amount = 0;
        $period_days = 0;
        $security_deposit = '';
        $additional_guests = '';
        $additional_guests_total_price = '';
        $services_fee_final = '';
        $taxes_fee_final = '';
        $prices_array = array();

        $listing_guests          = floatval( get_post_meta($listing_id, $prefix.'guests', true) );
        $day_date_price          = floatval( get_post_meta($listing_id, $prefix.'day_date_price', true));
        $price_per_day_date      = $day_date_price;
        $weekends_price          = floatval( get_post_meta($listing_id, $prefix.'weekends_price', true) );
        $weekends_days           = get_post_meta($listing_id, $prefix.'weekends_days', true);
        $priceWeek               = floatval( get_post_meta($listing_id, $prefix.'priceWeek', true) ); // 7 Nights
        $priceMonthly            = floatval( get_post_meta($listing_id, $prefix.'priceMonthly', true) );  // 30 Nights
        $security_deposit        = floatval( get_post_meta($listing_id, $prefix.'security_deposit', true) );

        $cleaning_fee            = floatval( get_post_meta($listing_id, $prefix.'cleaning_fee', true) );
        $cleaning_fee_type       = get_post_meta($listing_id, $prefix.'cleaning_fee_type', true);

        $city_fee                = floatval( get_post_meta($listing_id, $prefix.'city_fee', true) );
        $city_fee_type           = get_post_meta($listing_id, $prefix.'city_fee_type', true);

        $extra_guests_price      = floatval( get_post_meta($listing_id, $prefix.'additional_guests_price', true) );
        $additional_guests_price = $extra_guests_price;

        $allow_additional_guests = get_post_meta($listing_id, $prefix.'allow_additional_guests', true);

        $check_in        =  new DateTime($check_in_date);
        $check_in_unix   =  $check_in->getTimestamp();
        $check_in_unix_first_day   =  $check_in->getTimestamp();
        $check_out       =  new DateTime($check_out_date);
        $check_out_unix  =  $check_out->getTimestamp();

        $time_difference = abs( strtotime($check_in_date) - strtotime($check_out_date) );
        $days_count      = $time_difference/86400;
        $days_count      = intval($days_count + 1);

        //print_r($check_in_unix);

        if( isset($period_price[$check_in_unix]) && isset( $period_price[$check_in_unix]['day_date_price'] ) &&  $period_price[$check_in_unix]['day_date_price']!=0 ){
            $price_per_day_date = $period_price[$check_in_unix]['day_date_price'];

            $booking_has_custom_pricing = 1;
            $period_days = $period_days + 1;
        }

        if( $days_count > 7 && $priceWeek != 0 ) {
            $price_per_day_date = $priceWeek;
        }

        if( $days_count > 30 && $priceMonthly != 0 ) {
            $price_per_day_date = $priceMonthly;
        }

        // Check additional guests price
        if( $allow_additional_guests == 'yes' && $guests > 0 && !empty($guests) ) {
            if( $guests > $listing_guests) {
                $additional_guests = $guests - $listing_guests;

                $guests_price_return = homey_calculate_guests_price($period_price, $check_in_unix, $additional_guests, $additional_guests_price);

                // echo ', prev price='.$total_guests_price .' + weekend or reg price='. $guests_price_return.'<br>';
                $total_guests_price = $total_guests_price + $guests_price_return;
            }
        }
        //echo $price_per_day_date.' only price ';

        // Check for weekend and add weekend price
        $returnPrice = homey_cal_weekend_price($check_in_unix, $weekends_price, $price_per_day_date, $weekends_days, $period_price);
        //echo  ', prev price='.$price_per_day_date .' + weekend or reg price= '. $returnPrice.'<br>';
        $nights_total_price = $nights_total_price + $returnPrice;
        $total_price = $total_price + $returnPrice;


        //$check_in->modify('tomorrow');
        $check_in_unix =   $check_in->getTimestamp();

        $weekday = date('N', $check_in_unix_first_day);
        if(homey_check_weekend($weekday, $weekends_days, $weekends_price)) {
            $booking_has_weekend = 1;
        }

        while ($check_in_unix < $check_out_unix) {
//            echo ' * This date * '.date('d-m-Y',$check_in_unix).'<br>';

            $weekday = date('N', $check_in_unix);
            if(homey_check_weekend($weekday, $weekends_days, $weekends_price)) {
                $booking_has_weekend = 1;
            }

            if( isset($period_price[$check_in_unix]) && isset( $period_price[$check_in_unix]['day_date_price'] ) &&  $period_price[$check_in_unix]['day_date_price']!=0 ){

                $price_per_day_date = $period_price[$check_in_unix]['day_date_price'];
                //echo 'cond> <pre>  if( isset('.$period_price[$check_in_unix].') && isset('. $period_price[$check_in_unix]['day_date_price'] .') && '. $period_price[$check_in_unix]['day_date_price'] .'!=0 ){';
                //print_r($period_price[$check_in_unix]);
                //echo date('d-m-Y', $check_in_unix).' its custom pr '.$price_per_day_date.' custom price <br>';
                $booking_has_custom_pricing = 1;
                $period_days = $period_days + 1;
            } else {
                if( !($days_count > 7) && !($priceWeek != 0) && !($days_count > 30) && !($priceMonthly != 0) )
                    $price_per_day_date = $day_date_price;
                //echo date('d-m-Y', $check_in_unix).' its reg price '.$price_per_day_date.' <br>' ;
            }

            if( $allow_additional_guests == 'yes' && $guests > 0 && !empty($guests) ) {
                if( $guests > $listing_guests) {
                    $additional_guests = $guests - $listing_guests;

                    $guests_price_return = homey_calculate_guests_price($period_price, $check_in_unix, $additional_guests, $additional_guests_price);

                    //echo ', prev price='.$total_guests_price .' + guest price='. $guests_price_return.'<br>';
                    $total_guests_price = $total_guests_price + $guests_price_return;
                }
            }

            $returnPrice = homey_cal_weekend_price($check_in_unix, $weekends_price, $price_per_day_date, $weekends_days, $period_price);

            //echo ', prev price='.$nights_total_price .' + weekend or reg price='. $returnPrice.'<br>';

            $nights_total_price = $nights_total_price + $returnPrice;
            $total_price = $total_price + $returnPrice;

            $check_in->modify('tomorrow');
            $check_in_unix =   $check_in->getTimestamp();

        }

        if( $cleaning_fee_type == 'daily' ) {
            $cleaning_fee = $cleaning_fee * $days_count;
            $total_price = $total_price + $cleaning_fee;
        } else {
            $total_price = $total_price + $cleaning_fee;
        }


        //Extra prices =======================================
        if($extra_options != '') {

            $extra_prices_output = '';
            foreach ($extra_options as $extra_price) {
                $ex_single_price = explode('|', $extra_price);

                $ex_name = $ex_single_price[0];
                $ex_price = floatval($ex_single_price[1]);
                $ex_type = $ex_single_price[2];

                if($ex_type == 'single_fee') {
                    $ex_price = $ex_price;

                } elseif($ex_type == 'per_night') {
                    $ex_price = $ex_price*$days_count;
                } elseif($ex_type == 'per_guest') {
                    $ex_price = $ex_price*$guests;
                } elseif($ex_type == 'per_night_per_guest') {
                    $ex_price = $ex_price* $days_count*$guests;
                }

                $total_extra_services = $total_extra_services + $ex_price;

                $extra_prices_output .= '<li>'.esc_attr($ex_name).'<span>'.homey_formatted_price($ex_price).'</span></li>';
            }

            $total_price = $total_price + $total_extra_services;
            $extra_prices_html = $extra_prices_output;
        }

        //Calculate taxes based of original price (Excluding city, security deposit etc)
        if($enable_taxes == 1) {

            if($tax_type == 'global_tax') {
                $taxes_percent = $taxes_percent_global;
            } else {
                if(!empty($single_listing_tax)) {
                    $taxes_percent = $single_listing_tax;
                }
            }

            $taxable_amount = $total_price + $total_guests_price;
            $taxes_final = homey_calculate_taxes($taxes_percent, $taxable_amount);
            $total_price = $total_price + $taxes_final;
        }


        //Calculate sevices fee based of original price (Excluding cleaning, city, sevices fee etc)
        if($enable_services_fee == 1 && $offsite_payment != 1) {
            $services_fee_type  = homey_option('services_fee_type');
            $services_fee  =   homey_option('services_fee');
            $price_for_services_fee = $total_price + $total_guests_price;
            $services_fee_final = homey_calculate_services_fee($services_fee_type, $services_fee, $price_for_services_fee);
            $total_price = $total_price + $services_fee_final;
        }


        if( $city_fee_type == 'daily' ) {
            $city_fee = $city_fee * $days_count;
            $total_price = $total_price + $city_fee;
        } else {
            $total_price = $total_price + $city_fee;
        }

        if(!empty($security_deposit) && $security_deposit != 0) {
            $total_price = $total_price + $security_deposit;
        }

        if($total_guests_price !=0) {
            $total_price = $total_price + $total_guests_price;
        }



        $listing_host_id = get_post_field( 'post_author', $listing_id );
        $host_reservation_payment_type = get_user_meta($listing_host_id, 'host_reservation_payment', true);
        $host_booking_percent = get_user_meta($listing_host_id, 'host_booking_percent', true);

        if($offsite_payment == 1 && !empty($host_reservation_payment_type)) {

            if($host_reservation_payment_type == 'percent') {
                if(!empty($host_booking_percent) && $host_booking_percent != 0) {
                    $upfront_payment = round($host_booking_percent*$total_price/100,2);
                }

            } elseif($host_reservation_payment_type == 'full') {
                $upfront_payment = $total_price;

            } elseif($host_reservation_payment_type == 'only_security') {
                $upfront_payment = $security_deposit;

            } elseif($host_reservation_payment_type == 'only_services') {
                $upfront_payment = $services_fee_final;

            } elseif($host_reservation_payment_type == 'services_security') {
                $upfront_payment = $security_deposit+$services_fee_final;
            }

        } else {

            if($reservation_payment_type == 'percent') {
                if(!empty($booking_percent) && $booking_percent != 0) {
                    $upfront_payment = round($booking_percent*$total_price/100,2);
                }

            } elseif($reservation_payment_type == 'full') {
                $upfront_payment = $total_price;

            } elseif($reservation_payment_type == 'only_security') {
                $upfront_payment = $security_deposit;

            } elseif($reservation_payment_type == 'only_services') {
                $upfront_payment = $services_fee_final;

            } elseif($reservation_payment_type == 'services_security') {
                $upfront_payment = $security_deposit+$services_fee_final;
            }
        }

        $balance = $total_price - $upfront_payment;

        $prices_array['price_per_day_date'] = $price_per_day_date;
        $prices_array['nights_total_price'] = $nights_total_price;
        $prices_array['total_price']     = $total_price;
        $prices_array['check_in_date']   = $check_in_date;
        $prices_array['check_out_date']  = $check_out_date;
        $prices_array['cleaning_fee']    = $cleaning_fee;
        $prices_array['city_fee']        = $city_fee;
        $prices_array['services_fee']    = $services_fee_final;
        $prices_array['days_count']      = $days_count;
        $prices_array['period_days']      = $period_days;
        $prices_array['taxes']           = $taxes_final;
        $prices_array['taxes_percent']   = $taxes_percent;
        $prices_array['security_deposit'] = $security_deposit;
        $prices_array['additional_guests'] = $additional_guests;
        $prices_array['additional_guests_price'] = $additional_guests_price;
        $prices_array['additional_guests_total_price'] = $total_guests_price;
        $prices_array['booking_has_weekend'] = $booking_has_weekend;
        $prices_array['booking_has_custom_pricing'] = $booking_has_custom_pricing;
        $prices_array['extra_prices_html'] = $extra_prices_html;
        $prices_array['balance'] = $balance;
        $prices_array['upfront_payment'] = $upfront_payment;

        return $prices_array;

    }
}

if(!function_exists('homey_get_prices')) {
    function homey_get_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options = null) {
        $prefix = 'homey_';

        $enable_services_fee = homey_option('enable_services_fee');
        $enable_taxes = homey_option('enable_taxes');
        $offsite_payment = homey_option('off-site-payment');
        $reservation_payment_type = homey_option('reservation_payment');
        $booking_percent = homey_option('booking_percent');
        $tax_type = homey_option('tax_type');
        $apply_taxes_on_service_fee  =   homey_option('apply_taxes_on_service_fee');
        $taxes_percent_global  =   homey_option('taxes_percent');
        $single_listing_tax = get_post_meta($listing_id, 'homey_tax_rate', true);

        $period_price = get_post_meta($listing_id, 'homey_custom_period', true);
        /*echo '<pre> its period prices > ';
        print_r($period_price);*/

        if(empty($period_price)) {
            $period_price =  array();
        }

        $total_extra_services = 0;
        $extra_prices_html = "";
        $taxes_final = 0;
        $taxes_percent = 0;
        $total_price = 0;
        $total_guests_price = 0;
        $upfront_payment = 0;
        $nights_total_price = 0;
        $booking_has_weekend = 0;
        $booking_has_custom_pricing = 0;
        $balance = 0;
        $taxable_amount = 0;
        $period_days = 0;
        $security_deposit = '';
        $additional_guests = '';
        $additional_guests_total_price = '';
        $services_fee_final = '';
        $taxes_fee_final = '';
        $prices_array = array();

        $listing_guests          = floatval( get_post_meta($listing_id, $prefix.'guests', true) );
        $nightly_price           = floatval( get_post_meta($listing_id, $prefix.'night_price', true));
        $price_per_night         = $nightly_price;
        $weekends_price          = floatval( get_post_meta($listing_id, $prefix.'weekends_price', true) );
        $weekends_days           = get_post_meta($listing_id, $prefix.'weekends_days', true);
        $priceWeek               = floatval( get_post_meta($listing_id, $prefix.'priceWeek', true) ); // 7 Nights
        $priceMonthly            = floatval( get_post_meta($listing_id, $prefix.'priceMonthly', true) );  // 30 Nights
        $security_deposit        = floatval( get_post_meta($listing_id, $prefix.'security_deposit', true) );

        $cleaning_fee            = floatval( get_post_meta($listing_id, $prefix.'cleaning_fee', true) );
        $cleaning_fee_type       = get_post_meta($listing_id, $prefix.'cleaning_fee_type', true);

        $city_fee                = floatval( get_post_meta($listing_id, $prefix.'city_fee', true) );
        $city_fee_type           = get_post_meta($listing_id, $prefix.'city_fee_type', true);

        $extra_guests_price      = floatval( get_post_meta($listing_id, $prefix.'additional_guests_price', true) );
        $additional_guests_price = $extra_guests_price;

        $allow_additional_guests = get_post_meta($listing_id, $prefix.'allow_additional_guests', true);

        $check_in        =  new DateTime($check_in_date);
        $check_in_unix   =  $check_in->getTimestamp();
        $check_in_unix_first_day   =  $check_in->getTimestamp();
        $check_out       =  new DateTime($check_out_date);
        $check_out_unix  =  $check_out->getTimestamp();

        $time_difference = abs( strtotime($check_in_date) - strtotime($check_out_date) );
        $days_count      = $time_difference/86400;
        $days_count      = intval($days_count);

        //print_r($check_in_unix);

        if( isset($period_price[$check_in_unix]) && isset( $period_price[$check_in_unix]['night_price'] ) &&  $period_price[$check_in_unix]['night_price']!=0 ){
            $price_per_night = $period_price[$check_in_unix]['night_price'];

            $booking_has_custom_pricing = 1;
            $period_days = $period_days + 1;
        }

        if( $days_count > 7 && $priceWeek != 0 ) {
            $price_per_night = $priceWeek;
        }

        if( $days_count > 30 && $priceMonthly != 0 ) {
            $price_per_night = $priceMonthly;
        }


        // Check additional guests price
        if( $allow_additional_guests == 'yes' && $guests > 0 && !empty($guests) ) {
            if( $guests > $listing_guests) {
                $additional_guests = $guests - $listing_guests;

                $guests_price_return = homey_calculate_guests_price($period_price, $check_in_unix, $additional_guests, $additional_guests_price);

                // echo ', prev price='.$total_guests_price .' + weekend or reg price='. $guests_price_return.'<br>';
                $total_guests_price = $total_guests_price + $guests_price_return;
            }
        }
        //echo $price_per_night.' only price ';

        // Check for weekend and add weekend price
//                     echo ' * This first date * '.date('d-m-Y',$check_in_unix).'<br>';

        if( isset($period_price[$check_in_unix]) && isset( $period_price[$check_in_unix]['night_price'] ) &&  $period_price[$check_in_unix]['night_price']!=0 ){
            $returnPrice = $period_price[$check_in_unix]['night_price'];
        }else{
            $returnPrice = homey_cal_weekend_price($check_in_unix, $weekends_price, $price_per_night, $weekends_days, $period_price);
        }
//         echo  ' first night price= '. $returnPrice.'<br>';
        $nights_total_price = $nights_total_price + $returnPrice;
        $total_price = $total_price + $returnPrice;


        $check_in->modify('tomorrow');
        $check_in_unix =   $check_in->getTimestamp();

        $weekday = date('N', $check_in_unix_first_day);
        if(homey_check_weekend($weekday, $weekends_days, $weekends_price)) {
            $booking_has_weekend = 1;
        }

        while ($check_in_unix < $check_out_unix) {
//             echo ' * This date * '.date('d-m-Y',$check_in_unix).'<br>';

            $weekday = date('N', $check_in_unix);
            if(homey_check_weekend($weekday, $weekends_days, $weekends_price)) {
                $booking_has_weekend = 1;
            }

            if( isset($period_price[$check_in_unix]) && isset( $period_price[$check_in_unix]['night_price'] ) &&  $period_price[$check_in_unix]['night_price']!=0 ){

                $price_per_night = $period_price[$check_in_unix]['night_price'];
                //echo 'cond> <pre>  if( isset('.$period_price[$check_in_unix].') && isset('. $period_price[$check_in_unix]['night_price'] .') && '. $period_price[$check_in_unix]['night_price'] .'!=0 ){';
                //print_r($period_price[$check_in_unix]);
                //echo date('d-m-Y', $check_in_unix).' its custom pr '.$price_per_night.' custom price <br>';
                $booking_has_custom_pricing = 1;
                $period_days = $period_days + 1;
            } else {
                $price_per_night = $nightly_price; // this creates issue for 7+ and 30+ nights issue
            }

            if( $allow_additional_guests == 'yes' && $guests > 0 && !empty($guests) ) {
                if( $guests > $listing_guests) {
                    $additional_guests = $guests - $listing_guests;

                    $guests_price_return = homey_calculate_guests_price($period_price, $check_in_unix, $additional_guests, $additional_guests_price);

                    //echo ', prev price='.$total_guests_price .' + guest price='. $guests_price_return.'<br>';
                    $total_guests_price = $total_guests_price + $guests_price_return;
                }
            }

            $returnPrice = homey_cal_weekend_price($check_in_unix, $weekends_price, $price_per_night, $weekends_days, $period_price);

//             echo ' the day => price='. $returnPrice.'<br>';

            $nights_total_price = $nights_total_price + $returnPrice;
            $total_price = $total_price + $returnPrice;

            $check_in->modify('tomorrow');
            $check_in_unix =   $check_in->getTimestamp();

        }

        if( $cleaning_fee_type == 'daily' ) {
            $cleaning_fee = $cleaning_fee * $days_count;
            $total_price = $total_price + $cleaning_fee;
        } else {
            $total_price = $total_price + $cleaning_fee;
        }


        //Extra prices =======================================
        if($extra_options != '') {

            $extra_prices_output = '';
            foreach ($extra_options as $extra_price) {
                $ex_single_price = explode('|', $extra_price);

                $ex_name = $ex_single_price[0];
                $ex_price = floatval($ex_single_price[1]);
                $ex_type = $ex_single_price[2];

                if($ex_type == 'single_fee') {
                    $ex_price = $ex_price;

                } elseif($ex_type == 'per_night') {
                    $ex_price = $ex_price*$days_count;
                } elseif($ex_type == 'per_guest') {
                    $ex_price = $ex_price*$guests;
                } elseif($ex_type == 'per_night_per_guest') {
                    $ex_price = $ex_price* $days_count*$guests;
                }

                $total_extra_services = $total_extra_services + $ex_price;

                $extra_prices_output .= '<li>'.esc_attr($ex_name).'<span>'.homey_formatted_price($ex_price).'</span></li>';
            }

            $total_price = $total_price + $total_extra_services;
            $extra_prices_html = $extra_prices_output;
        }

        //Calculate taxes based of original price (Excluding city, security deposit etc)
        if($enable_taxes == 1) {

            if($tax_type == 'global_tax') {
                $taxes_percent = $taxes_percent_global;
            } else {
                if(!empty($single_listing_tax)) {
                    $taxes_percent = $single_listing_tax;
                }
            }

            $taxable_amount = $total_price + $total_guests_price;
            $taxes_final = homey_calculate_taxes($taxes_percent, $taxable_amount);
            $total_price = $total_price + $taxes_final;
        }


        //Calculate sevices fee based of original price (Excluding cleaning, city, sevices fee etc)
        if($enable_services_fee == 1 && $offsite_payment != 1) {
            $services_fee_type  = homey_option('services_fee_type');
            $services_fee  =   homey_option('services_fee');
            $price_for_services_fee = $total_price + $total_guests_price;
            $services_fee_final = homey_calculate_services_fee($services_fee_type, $services_fee, $price_for_services_fee);
            $total_price = $total_price + $services_fee_final;
        }


        if( $city_fee_type == 'daily' ) {
            $city_fee = $city_fee * $days_count;
            $total_price = $total_price + $city_fee;
        } else {
            $total_price = $total_price + $city_fee;
        }

        if(!empty($security_deposit) && $security_deposit != 0) {
            $total_price = $total_price + $security_deposit;
        }

        if($total_guests_price !=0) {
            $total_price = $total_price + $total_guests_price;
        }



        $listing_host_id = get_post_field( 'post_author', $listing_id );
        $host_reservation_payment_type = get_user_meta($listing_host_id, 'host_reservation_payment', true);
        $host_booking_percent = get_user_meta($listing_host_id, 'host_booking_percent', true);

        if($offsite_payment == 1 && !empty($host_reservation_payment_type)) {

            if($host_reservation_payment_type == 'percent') {
                if(!empty($host_booking_percent) && $host_booking_percent != 0) {
                    $upfront_payment = round($host_booking_percent*$total_price/100,2);
                }

            } elseif($host_reservation_payment_type == 'full') {
                $upfront_payment = $total_price;

            } elseif($host_reservation_payment_type == 'only_security') {
                $upfront_payment = $security_deposit;

            } elseif($host_reservation_payment_type == 'only_services') {
                $upfront_payment = $services_fee_final;

            } elseif($host_reservation_payment_type == 'services_security') {
                $upfront_payment = $security_deposit+$services_fee_final;
            }

        } else {

            if($reservation_payment_type == 'percent') {
                if(!empty($booking_percent) && $booking_percent != 0) {
                    $upfront_payment = round($booking_percent*$total_price/100,2);
                }

            } elseif($reservation_payment_type == 'full') {
                $upfront_payment = $total_price;

            } elseif($reservation_payment_type == 'only_security') {
                $upfront_payment = $security_deposit;

            } elseif($reservation_payment_type == 'only_services') {
                $upfront_payment = $services_fee_final;

            } elseif($reservation_payment_type == 'services_security') {
                $upfront_payment = $security_deposit+$services_fee_final;
            }
        }

        $balance = $total_price - $upfront_payment;

        $prices_array['price_per_night'] = $price_per_night;
        $prices_array['nights_total_price'] = $nights_total_price;
        $prices_array['total_price']     = $total_price;
        $prices_array['check_in_date']   = $check_in_date;
        $prices_array['check_out_date']  = $check_out_date;
        $prices_array['cleaning_fee']    = $cleaning_fee;
        $prices_array['city_fee']        = $city_fee;
        $prices_array['services_fee']    = $services_fee_final;
        $prices_array['days_count']      = $days_count;
        $prices_array['period_days']      = $period_days;
        $prices_array['taxes']           = $taxes_final;
        $prices_array['taxes_percent']   = $taxes_percent;
        $prices_array['security_deposit'] = $security_deposit;
        $prices_array['additional_guests'] = $additional_guests;
        $prices_array['additional_guests_price'] = $additional_guests_price;
        $prices_array['additional_guests_total_price'] = $total_guests_price;
        $prices_array['booking_has_weekend'] = $booking_has_weekend;
        $prices_array['booking_has_custom_pricing'] = $booking_has_custom_pricing;
        $prices_array['extra_prices_html'] = $extra_prices_html;
        $prices_array['balance'] = $balance;
        $prices_array['upfront_payment'] = $upfront_payment;

        return $prices_array;

    }
}

if(!function_exists('homey_calculate_guests_price')) {
    function homey_calculate_guests_price($period_price, $check_in_unix, $additional_guests, $additional_guests_price) {
        if( isset($period_price[$check_in_unix]) && isset( $period_price[$check_in_unix]['guest_price'] ) &&  $period_price[$check_in_unix]['guest_price']!=0 ) {
            $return_guest_price = $period_price[$check_in_unix]['guest_price'] * $additional_guests;
        } else {
            $return_guest_price = $additional_guests_price * $additional_guests;
        }
        return $return_guest_price;
    }
}

if(!function_exists('homey_calculate_services_fee')) {
    function  homey_calculate_services_fee($services_fee_type, $services_fee, $total_price) {

        if( !empty($services_fee) && $services_fee != 0 ) {
            if ( $services_fee_type == 'percent') {

                if( empty($services_fee) || $services_fee == 0 ) {
                    $fee = 0;

                } else {
                    $fee = round($services_fee*$total_price/100,2);
                }

            } else {
                $fee = $services_fee;
            }
            return $fee;
        }
        return '';

    }
}

if(!function_exists('homey_calculate_taxes')) {
    function homey_calculate_taxes($taxes_percent, $total_price) {

        if( empty($taxes_percent) || $taxes_percent == 0 ) {
            $taxes = 0;
        } else {
            $taxes = round($taxes_percent*$total_price/100,2);
        }
        return $taxes;

    }
}

if(!function_exists('homey_calculate_taxes_2')) {
    function homey_calculate_taxes_2($listing_id, $total_price) {

        $tax_type = homey_option('tax_type');
        $apply_taxes_on_service_fee  =   homey_option('apply_taxes_on_service_fee');
        $taxes_percent_global  =   homey_option('taxes_percent');
        $single_listing_tax = get_post_meta($listing_id, 'homey_tax_rate', true);

        if($tax_type == 'global_tax') {
            $taxes_percent = $taxes_percent_global;
        } else {
            if(!empty($single_listing_tax)) {
                $taxes_percent = $single_listing_tax;
            }
        }

        if( empty($taxes_percent) || $taxes_percent == 0 ) {
            $taxes = 0;
        } else {
            $taxes = round($taxes_percent*$total_price/100,2);
        }
        return $taxes;

    }
}

if(!function_exists('homey_check_weekend')) {
    function homey_check_weekend($weekday, $weekends_days, $weekends_price) {

        if(empty($weekends_price) && $weekends_price == 0 ) {
            return false;

        } else {

            if($weekends_days == 'sat_sun' && ($weekday ==6 || $weekday==7)) {
                return true;

            } elseif($weekends_days == 'fri_sat' && ($weekday ==5 || $weekday==6)) {
                return true;

            } elseif($weekends_days == 'fri_sat_sun' && ($weekday ==5 || $weekday ==6 || $weekday==7)) {
                return true;

            } else {
                return false;
            }
        }
        return false;

    }
}

if(!function_exists('homey_cal_weekend_price') ) {
    function homey_cal_weekend_price($check_in_unix, $weekends_price, $price_per_night, $weekends_days, $period_price){
        $weekday = date('N', $check_in_unix);

        if($weekends_days == 'sat_sun' && ($weekday ==6 || $weekday==7)) {
            $return_price = homey_get_weekend_price($check_in_unix, $weekends_price, $price_per_night, $weekends_days, $period_price);

        } elseif($weekends_days == 'fri_sat' && ($weekday ==5 || $weekday==6)) {
            $return_price = homey_get_weekend_price($check_in_unix, $weekends_price, $price_per_night, $weekends_days, $period_price);

        } elseif($weekends_days == 'fri_sat_sun' && ($weekday ==5 || $weekday ==6 || $weekday==7)) {
            $return_price = homey_get_weekend_price($check_in_unix, $weekends_price, $price_per_night, $weekends_days, $period_price);

        } else {
            $return_price = $price_per_night;
        }

        return $return_price;

    }
}


if(!function_exists('homey_get_weekend_price')) {
    function homey_get_weekend_price($check_in_unix, $weekends_price, $price_per_night, $weekends_days, $period_price) {
        if( isset($period_price[$check_in_unix]) && isset( $period_price[$check_in_unix]['weekend_price'] ) &&  $period_price[$check_in_unix]['weekend_price']!=0 ){

            $return_price = $period_price[$check_in_unix]['weekend_price'];

        } elseif(!empty($weekends_price) && $weekends_price != 0) {
            $return_price = $weekends_price;
        } else {
            $return_price = $price_per_night;
        }

        return $return_price;
    }
}

add_action( 'wp_ajax_homey_guest_made_payment', 'homey_guest_made_payment' );
if(!function_exists('homey_guest_made_payment')) {
    function homey_guest_made_payment() {
        global $current_user;
        $current_user = wp_get_current_user();
        $userID       = $current_user->ID;
        $date = date( 'Y-m-d G:i:s', current_time( 'timestamp', 0 ));

        $reservation_id = intval($_POST['reservation_id']);

        update_post_meta($reservation_id, 'reservation_status', 'waiting_host_payment_verification');

        $listing_id = get_post_meta($reservation_id, 'reservation_listing_id', true);

        //Book dates
        $booked_days_array = homey_make_days_booked($listing_id, $reservation_id);
        update_post_meta($listing_id, 'reservation_dates', $booked_days_array);

        //Remove Pending Dates
        $pending_dates_array = homey_remove_booking_pending_days($listing_id, $reservation_id);
        update_post_meta($listing_id, 'reservation_pending_dates', $pending_dates_array);

        echo json_encode(
            array(
                'success' => true,
                'message' => homey_get_reservation_notification('sent_for_verification')
            )
        );

        $listing_owner = get_post_meta($reservation_id, 'listing_owner', true);
        $owner = homey_usermeta($listing_owner);
        $owner_email = $owner['email'];

        $email_args = array('reservation_detail_url' => reservation_detail_link($reservation_id) );
        homey_email_composer( $owner_email, 'guest_sent_payment_reservation', $email_args );

//        $admin_email = get_option( 'admin_email' );
//        homey_email_composer( $admin_email, 'guest_sent_payment_reservation', $email_args );

        //zahid generate invoice on off site payment
        homey_generate_invoice( 'reservation','one_time', $reservation_id, $date, $userID, 0, 0, '', 'Self' );

        wp_die();
    }
}

add_action( 'wp_ajax_homey_confirm_offsite_reservation', 'homey_confirm_offsite_reservation' );
if(!function_exists('homey_confirm_offsite_reservation')) {
    function homey_confirm_offsite_reservation() {
        global $current_user;
        $current_user = wp_get_current_user();
        $userID       = $current_user->ID;
        $local = homey_get_localization();
        $no_upfront = homey_option('reservation_payment');

        $date = date( 'Y-m-d G:i:s', current_time( 'timestamp', 0 ));

        $reservation_id = intval($_POST['reservation_id']);

        $listing_owner = get_post_meta($reservation_id, 'listing_owner', true);
        $listing_renter = get_post_meta($reservation_id, 'listing_renter', true);
        $is_hourly = get_post_meta($reservation_id, 'is_hourly', true);

        $listing_host_id = $listing_owner;
        $host_reservation_payment_type = get_user_meta($listing_host_id, 'host_reservation_payment', true);

        $homey_is_host_payment_method = homey_is_host_payout($userID);

        $renter = homey_usermeta($listing_renter);
        $renter_email = $renter['email'];

        if(empty($homey_is_host_payment_method) && homey_option('off-site-payment') == 0) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => homey_get_reservation_notification('host_payment_method')
                )
            );
            wp_die();
        }

        if( $listing_owner != $userID ) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => homey_get_reservation_notification('not_owner')
                )
            );
            wp_die();
        }

        // If no upfront option select then book at this step
        if($host_reservation_payment_type == 'no_upfront') {

            if($is_hourly =='yes') {
                homey_hourly_booking_with_no_upfront($reservation_id);
            } else {
                homey_booking_with_no_upfront($reservation_id);
            }

            echo json_encode(
                array(
                    'success' => true,
                    'message' => homey_get_reservation_notification('booked')
                )
            );

        } else {
            // Set reservation status from under_review to available
            update_post_meta($reservation_id, 'reservation_status', 'available');
            update_post_meta($reservation_id, 'reservation_confirm_date_time', $date );

            echo json_encode(
                array(
                    'success' => true,
                    'message' => homey_get_reservation_notification('available')
                )
            );

            $email_args = array('reservation_detail_url' => reservation_detail_link($reservation_id) );
            homey_email_composer( $renter_email, 'confirm_reservation', $email_args );

//             $admin_email = get_option( 'admin_email' );
//             homey_email_composer( $admin_email, 'confirm_reservation', $email_args );

        }

        wp_die();
    }
}

add_action( 'wp_ajax_homey_confirm_reservation', 'homey_confirm_reservation' );
if(!function_exists('homey_confirm_reservation')) {
    function homey_confirm_reservation() {
        global $current_user;
        $current_user = wp_get_current_user();
        $userID       = $current_user->ID;
        $local = homey_get_localization();
        $no_upfront = homey_option('reservation_payment');

        $date = date( 'Y-m-d G:i:s', current_time( 'timestamp', 0 ));

        $reservation_id = intval($_POST['reservation_id']);

        $listing_owner = get_post_meta($reservation_id, 'listing_owner', true);
        $listing_renter = get_post_meta($reservation_id, 'listing_renter', true);
        $is_hourly = get_post_meta($reservation_id, 'is_hourly', true);

        $renter = homey_usermeta($listing_renter);
        $renter_email = $renter['email'];

        if( $listing_owner != $userID ) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => homey_get_reservation_notification('not_owner')
                )
            );
            wp_die();
        }

        // If no upfront option select then book at this step
        if($no_upfront == 'no_upfront') {

            if($is_hourly =='yes') {
                homey_hourly_booking_with_no_upfront($reservation_id);
            } else {
                homey_booking_with_no_upfront($reservation_id);
            }

            echo json_encode(
                array(
                    'success' => true,
                    'message' => homey_get_reservation_notification('booked')
                )
            );

        } else {
            // Set reservation status from under_review to available
            update_post_meta($reservation_id, 'reservation_status', 'available');
            update_post_meta($reservation_id, 'reservation_confirm_date_time', $date );

            echo json_encode(
                array(
                    'success' => true,
                    'message' => homey_get_reservation_notification('available')
                )
            );

            $email_args = array('reservation_detail_url' => reservation_detail_link($reservation_id) );
            homey_email_composer( $renter_email, 'confirm_reservation', $email_args );
//            $admin_email = get_option( 'admin_email' );
//            homey_email_composer( $admin_email, 'confirm_reservation', $email_args );
        }

        wp_die();
    }
}

if(!function_exists('homey_booking_with_no_upfront')) {
    function homey_booking_with_no_upfront($reservation_id) {
        $listing_id = get_post_meta($reservation_id, 'reservation_listing_id', true );
        $admin_email = get_option( 'admin_email' );

        //Book days
        $booked_days_array = homey_make_days_booked($listing_id, $reservation_id);
        update_post_meta($listing_id, 'reservation_dates', $booked_days_array);

        //Remove Pending Dates
        $pending_dates_array = homey_remove_booking_pending_days($listing_id, $reservation_id);
        update_post_meta($listing_id, 'reservation_pending_dates', $pending_dates_array);

        // Update reservation status
        update_post_meta( $reservation_id, 'reservation_status', 'booked' );

        // Emails
        $listing_owner = get_post_meta($reservation_id, 'listing_owner', true);
        $listing_renter = get_post_meta($reservation_id, 'listing_renter', true);

        $renter = homey_usermeta($listing_renter);
        $renter_email = $renter['email'];

        $owner = homey_usermeta($listing_owner);
        $owner_email = $owner['email'];

        $email_args = array('reservation_detail_url' => reservation_detail_link($reservation_id) );
        homey_email_composer( $renter_email, 'booked_reservation', $email_args );
        homey_email_composer( $admin_email, 'admin_booked_reservation', $email_args );

        return true;
    }
}

add_action( 'wp_ajax_homey_decline_reservation', 'homey_decline_reservation' );
if(!function_exists('homey_decline_reservation')) {
    function homey_decline_reservation() {
        global $current_user;
        $current_user = wp_get_current_user();
        $userID       = $current_user->ID;
        $local = homey_get_localization();

        $reservation_id = intval($_POST['reservation_id']);
        $listing_id = get_post_meta($reservation_id, 'reservation_listing_id', true);
        $reason = sanitize_text_field($_POST['reason']);

        $listing_owner = get_post_meta($reservation_id, 'listing_owner', true);
        $listing_renter = get_post_meta($reservation_id, 'listing_renter', true);

        $renter = homey_usermeta($listing_renter);
        $renter_email = $renter['email'];

        if( $listing_owner != $userID && !homey_is_admin() ) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['listing_owner_text']
                )
            );
            wp_die();
        }

        // Set reservation status from under_review to available
        update_post_meta($reservation_id, 'reservation_status', 'declined');
        update_post_meta($reservation_id, 'res_decline_reason', $reason);

        //Remove Pending Dates
        $pending_dates_array = homey_remove_booking_pending_days($listing_id, $reservation_id, true);
        update_post_meta($listing_id, 'reservation_pending_dates', $pending_dates_array);

        echo json_encode(
            array(
                'success' => true,
                'message' => esc_html__('success', 'homey')
            )
        );

        $email_args = array('reservation_detail_url' => reservation_detail_link($reservation_id) );
        homey_email_composer( $renter_email, 'declined_reservation', $email_args );
//        $admin_email = get_option( 'admin_email' );
//        homey_email_composer( $admin_email, 'declined_reservation', $email_args );
        wp_die();
    }
}

add_action( 'wp_ajax_homey_cancelled_reservation', 'homey_cancelled_reservation' );
if(!function_exists('homey_cancelled_reservation')) {
    function homey_cancelled_reservation() {
        global $current_user;
        $current_user = wp_get_current_user();
        $userID       = $current_user->ID;
        $local = homey_get_localization();

        $reservation_id = intval($_POST['reservation_id']);
        $listing_id = get_post_meta($reservation_id, 'reservation_listing_id', true);
        $reason = sanitize_text_field($_POST['reason']);
        $host_cancel = sanitize_text_field($_POST['host_cancel']);

        $listing_owner = get_post_meta($reservation_id, 'listing_owner', true);
        $listing_renter = get_post_meta($reservation_id, 'listing_renter', true);

        //cancellation date is expired check
        $num_hours_before_cancel = homey_option('num_0f_hours_before_checkin_remove_resrv');
        $cancel_before_date = strtotime(date('d-m-Y')) + $num_hours_before_cancel * 60 * 60;
        $check_in_date     =  strtotime(date('d-m-Y', custom_strtotime( get_post_meta($reservation_id, "reservation_checkin_date", true) )));

        if( $cancel_before_date <= $checkin_date ) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => isset($local['cancelation_date_expired']) ? $local['cancelation_date_expired'] : 'Cancellation date is expired.'
                )
            );
            wp_die();
        }
        //cancellation date is expired check


        if( ($listing_renter != $userID) && ($listing_owner != $userID) ) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['listing_renter_text']
                )
            );
            wp_die();
        }

        if(empty($reason)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['reason_text_req']
                )
            );
            wp_die();
        }

        // Set reservation status from under_review to available
        update_post_meta($reservation_id, 'reservation_status', 'cancelled');
        update_post_meta($reservation_id, 'res_cancel_reason', $reason);

        //Remove Pending Dates
        $pending_dates_array = homey_remove_booking_pending_days($listing_id, $reservation_id);
        update_post_meta($listing_id, 'reservation_pending_dates', $pending_dates_array);

        //Remove Booked Dates
        $booked_dates_array = homey_remove_booking_booked_days($listing_id, $reservation_id);
        update_post_meta($listing_id, 'reservation_dates', $booked_dates_array);

        if($host_cancel == 'cancelled_by_host') {
            $renter = homey_usermeta($listing_renter);
            $to_email = $renter['email'];
        } else {
            $owner = homey_usermeta($listing_owner);
            $to_email = $owner['email'];
        }


        $host_earning = homey_get_earning_by_reservation_id($reservation_id);
        if(!empty($host_earning)) {
            $host_id = $host_earning->user_id;
            $deduct_amount = $host_earning->net_earnings;
            homey_adjust_host_available_balance_2($host_id, $deduct_amount);
        }


        echo json_encode(
            array(
                'success' => true,
                'message' => esc_html__('success', 'homey')
            )
        );

        $email_args = array('reservation_detail_url' => reservation_detail_link($reservation_id) );

        homey_email_composer( $to_email, 'cancelled_reservation', $email_args );
//        $admin_email = get_option( 'admin_email' );
//        homey_email_composer( $admin_email, 'cancelled_reservation', $email_args );
        wp_die();
    }
}


if(!function_exists('homey_get_reservation_days')) {
    function homey_get_reservation_days($listing_id) {
        $args=array(
            'post_type'        => 'homey_reservation',
            'post_status'      => 'any',
            'posts_per_page'   => -1,
            'meta_query' => array(
                array(
                    'key'       => 'reservation_id',
                    'value'     => $listing_id,
                    'type'      => 'NUMERIC',
                    'compare'   => '='
                ),
                array(
                    'key'       =>  'booking_status',
                    'value'     =>  'confirmed',
                    'compare'   =>  '='
                )
            )
        );
    }
}

/*-----------------------------------------------------------------------------------*/
// Add in-review post status Expired
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists('homey_approved_post_status') ) {
    function homey_approved_post_status() {

        $args = array(
            'label'                     => _x( 'Approved', 'Approved', 'homey' ),
            'label_count'               => _n_noop( 'Approved (%s)',  'Approved (%s)', 'homey' ),
            'public'                    => true,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'exclude_from_search'       => false,
        );
        register_post_status( 'publish', $args );

    }

}

if ( ! function_exists('homey_in-review_post_status') ) {
    function homey_in_review_post_status() {

        $args = array(
            'label'                     => _x( 'Waiting Approval', 'Waiting Approval', 'homey' ),
            'label_count'               => _n_noop( 'Waiting Approval (%s)',  'Waiting Approval (%s)', 'homey' ),
            'public'                    => true,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'exclude_from_search'       => false,
        );
        register_post_status( 'pending', $args );

    }

}

if ( ! function_exists('homey_declined_post_status') ) {
    function homey_declined_post_status() {

        $args = array(
            'label'                     => _x( 'Declined', 'Status General Name', 'homey' ),
            'label_count'               => _n_noop( 'Declined (%s)',  'Declined (%s)', 'homey' ),
            'public'                    => true,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'exclude_from_search'       => false,
        );
        register_post_status( 'declined', $args );

    }
    add_action( 'init', 'homey_declined_post_status', 1 );
}


/* -----------------------------------------------------------------------------------------------------------
*  Stripe Form
-------------------------------------------------------------------------------------------------------------*/

if( !function_exists('homey_stripe_payment') ) {
    function homey_stripe_payment( $reservation_id ) {

        $allowded_html = array();
        $current_user = wp_get_current_user();
        $userID = $current_user->ID;
        $user_email = $current_user->user_email;
        $reservation_payment_type = homey_option('reservation_payment');

        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);
        $extra_options = get_post_meta($reservation_id, 'extra_options', true);


        $listing_id     = intval($reservation_meta['listing_id']);
        $check_in_date  = wp_kses ( $reservation_meta['check_in_date'], $allowded_html );
        $check_out_date = wp_kses ( $reservation_meta['check_out_date'], $allowded_html );
        $guests         = intval($reservation_meta['guests']);

        $booking_type = homey_booking_type_by_id($listing_id);

        if($booking_type == 'per_day_date'){
            $prices_array = homey_get_day_date_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);
        }else{
           $prices_array = homey_get_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);
        }

        $upfront_payment = floatval( $reservation_meta['upfront'] );

        $extra_expenses = homey_get_extra_expenses($reservation_id);
        $extra_discount = homey_get_extra_discount($reservation_id);

        if( ! empty($extra_expenses) && $reservation_payment_type == 'full' ) {
            $expenses_total_price = $extra_expenses['expenses_total_price'];
            $upfront_payment = $upfront_payment + $expenses_total_price;
        }

        if( ! empty($extra_discount) && $reservation_payment_type == 'full' ) {
            $discount_total_price = $extra_discount['discount_total_price'];
            $upfront_payment = $upfront_payment - $discount_total_price;
        }

        $minimum_currency_amount = get_minimum_currency();
        if($upfront_payment < $minimum_currency_amount){
            echo $minimum_amount_error = esc_html__( "You can't pay using Stripe because minimum amount limit is 0.5",'homey');
            return $minimum_amount_error;
        }

        $description = esc_html__( 'Reservation ID','homey').' '.$reservation_id;

        require_once( HOMEY_PLUGIN_PATH . '/classes/class-stripe.php' );

        $stripe_payments = new Homey_Stripe();

        print '<div class="stripe-wrapper" id="homey_stripe_simple"> ';
        $metadata=array(
            'reservation_id_for_stripe' =>  $reservation_id,
            'userID'                    =>  $userID,
            'is_hourly'                 =>  0,
            'payment_type'              =>  'reservation_fee',
            'extra_options'             =>  0,
            'message'                   =>  esc_html__( 'Reservation Payment','homey')
        );

        if($upfront_payment > 0){
            $stripe_payments->homey_stripe_form($upfront_payment, $metadata, $description);
        }else{
            $message_text = esc_html__('Your amount in your wallet is: ', 'homey');
            $upfront_payment_with_symbol = homey_option("currency_symbol").' '.$upfront_payment;
            echo '<h3>'.$message_text.' '.$upfront_payment_with_symbol.'</h3>';
        }

        print'
        </div>';



    }
}

if( !function_exists('homey_stripe_payment_instance') ) {
    function homey_stripe_payment_instance($listing_id, $check_in, $check_out, $guests, $renter_message = '') {

        $allowded_html = array();
        $current_user = wp_get_current_user();
        $userID = $current_user->ID;
        $user_email = $current_user->user_email;

        $listing_id     = intval($listing_id);
        $check_in_date  = wp_kses ($check_in, $allowded_html);
        $check_out_date = wp_kses ($check_out, $allowded_html);
        $renter_message = $renter_message;
        $guests         = intval($guests);

        $extra_options = isset($_GET['extra_options']) ? $_GET['extra_options'] : '';

        update_user_meta($userID, 'extra_prices', $extra_options);

        if(!empty($extra_options)) {
            $extra_prices = 1;
        } else {
            $extra_prices = 0;
        }

        $check_availability = check_booking_availability($check_in_date, $check_out_date, $listing_id, $guests);
        $is_available = $check_availability['success'];
        $check_message = $check_availability['message'];

        if(!$is_available) {

            echo json_encode(
                array(
                    'success' => false,
                    'message' => $check_message,
                    'payment_execute_url' => ''
                )
            );
            wp_die();

        } else {

            $booking_type = homey_booking_type_by_id($listing_id);

            if( $booking_type == 'per_week' ) {
                $prices_array = homey_get_weekly_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);
            } else if( $booking_type == 'per_month' ) {
                $prices_array = homey_get_monthly_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);
            } else if( $booking_type == 'per_day_date' ) {
                $prices_array = homey_get_day_date_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);
            } else {
                $prices_array = homey_get_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);
            }
            $upfront_payment  =  floatval( $prices_array['upfront_payment'] );

            $upfront_payment = round($upfront_payment, 2);

        }

        $minimum_currency_amount = get_minimum_currency();
        if($upfront_payment < $minimum_currency_amount){
            echo $minimum_amount_error = esc_html__( "You can't pay using Stripe because minimum amount limit is 0.5",'homey');
            return $minimum_amount_error;
        }

        require_once( HOMEY_PLUGIN_PATH . '/classes/class-stripe.php' );

        $description = esc_html__( 'Instant Reservation, Listing ID','homey').' '.$listing_id;

        $stripe_payments = new Homey_Stripe();

        print '<div class="stripe-wrapper" id="homey_stripe_simple"> ';
        $metadata=array(
            'listing_id'    =>  $listing_id,
            'reservation_id_for_stripe' =>  0,
            'userID'              =>  $userID,
            'is_hourly'           =>  0,
            'is_instance_booking' =>  1,
            'check_in_date'       =>  $check_in_date,
            'check_out_date'      =>  $check_out_date,
            'guests'              =>  $guests,
            'extra_options'       =>  $extra_prices,
            'guest_message'       =>  $renter_message,
            'payment_type'        =>  'reservation_fee',
            'message'             =>  esc_html__( 'Reservation Payment','homey')
        );

        $stripe_payments->homey_stripe_form($upfront_payment, $metadata, $description);
        print'
        </div>';

    }
}


if( !function_exists('homey_stripe_payment_old') ) {
    function homey_stripe_payment_old( $reservation_id ) {

        require_once( HOMEY_PLUGIN_PATH . '/includes/stripe-php/init.php' );
        $stripe_secret_key = homey_option('stripe_secret_key');
        $stripe_publishable_key = homey_option('stripe_publishable_key');
        $allowded_html = array();

        $stripe = array(
            "secret_key" => $stripe_secret_key,
            "publishable_key" => $stripe_publishable_key
        );

        \Stripe\Stripe::setApiKey($stripe['secret_key']);
        $submission_currency = homey_option('payment_currency');
        $current_user = wp_get_current_user();
        $userID = $current_user->ID;
        $user_email = $current_user->user_email;

        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);
        $extra_options = get_post_meta($reservation_id, 'extra_options', true);


        $listing_id     = intval($reservation_meta['listing_id']);
        $check_in_date  = wp_kses ( $reservation_meta['check_in_date'], $allowded_html );
        $check_out_date = wp_kses ( $reservation_meta['check_out_date'], $allowded_html );
        $guests         = intval($reservation_meta['guests']);

        $prices_array = homey_get_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);
        $upfront_payment = floatval( $reservation_meta['upfront'] );

        $extra_expenses = homey_get_extra_expenses($reservation_id);
        $extra_discount = homey_get_extra_discount($reservation_id);

        if(!empty($extra_expenses)) {
            $expenses_total_price = $extra_expenses['expenses_total_price'];
            $upfront_payment = $upfront_payment + $expenses_total_price;
        }

        if(!empty($extra_discount)) {
            $discount_total_price = $extra_discount['discount_total_price'];
            $upfront_payment = $upfront_payment - $discount_total_price;
        }

        if( $submission_currency == 'JPY') {
            $upfront_payment = $upfront_payment;
        } else {
            $upfront_payment = $upfront_payment * 100;
        }

        print '
        <div class="homey_stripe_simple">
            <script src="https://checkout.stripe.com/checkout.js"
            class="stripe-button"
            data-key="' . $stripe_publishable_key . '"
            data-amount="' . $upfront_payment . '"
            data-email="' . $user_email . '"
            data-zip-code="true"
            data-billing-address="true"
            data-locale="'.get_locale().'"
            data-currency="' . $submission_currency . '"
            data-label="' . esc_html__('Pay with Credit Card', 'homey') . '"
            data-description="' . esc_html__('Reservation Payment', 'homey') . '">
            </script>
        </div>
        <input type="hidden" id="reservation_id_for_stripe" name="reservation_id_for_stripe" value="' . $reservation_id . '">
        <input type="hidden" id="reservation_pay" name="reservation_pay" value="1">
        <input type="hidden" id="is_hourly" name="is_hourly" value="0">
        <input type="hidden" id="is_instance_booking" name="is_instance_booking" value="0">
        <input type="hidden" name="extra_options" value="0">
        <input type="hidden" name="userID" value="' . $userID . '">
        <input type="hidden" id="pay_ammout" name="pay_ammout" value="' . $upfront_payment . '">
        ';
    }
}

/* -----------------------------------------------------------------------------------------------------------
*  Stripe Form
-------------------------------------------------------------------------------------------------------------*/
if( !function_exists('homey_stripe_payment_instance_old') ) {
    function homey_stripe_payment_instance_old($listing_id, $check_in, $check_out, $guests) {

        require_once( HOMEY_PLUGIN_PATH . '/includes/stripe-php/init.php' );
        $stripe_secret_key = homey_option('stripe_secret_key');
        $stripe_publishable_key = homey_option('stripe_publishable_key');
        $allowded_html = array();

        $stripe = array(
            "secret_key" => $stripe_secret_key,
            "publishable_key" => $stripe_publishable_key
        );

        \Stripe\Stripe::setApiKey($stripe['secret_key']);
        $submission_currency = homey_option('payment_currency');
        $current_user = wp_get_current_user();
        $userID = $current_user->ID;
        $user_email = $current_user->user_email;

        $listing_id     = intval($listing_id);
        $check_in_date  = wp_kses ($check_in, $allowded_html);
        $check_out_date = wp_kses ($check_out, $allowded_html);
        $renter_message = '';//$renter_message;
        $guests         = intval($guests);

        $extra_options = isset($_GET['extra_options']) ? $_GET['extra_options'] : '';

        update_user_meta($userID, 'extra_prices', $extra_options);

        if(!empty($extra_options)) {
            $extra_prices = 1;
        } else {
            $extra_prices = 0;
        }

        $check_availability = check_booking_availability($check_in_date, $check_out_date, $listing_id, $guests);
        $is_available = $check_availability['success'];
        $check_message = $check_availability['message'];

        if(!$is_available) {

            echo json_encode(
                array(
                    'success' => false,
                    'message' => $check_message,
                    'payment_execute_url' => ''
                )
            );
            wp_die();

        } else {

            $prices_array = homey_get_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);
            $upfront_payment  =  floatval( $prices_array['upfront_payment'] );
        }

        if( $submission_currency == 'JPY') {
            $upfront_payment = $upfront_payment;
        } else {
            $upfront_payment = $upfront_payment * 100;
        }

        print '
        <div class="homey_stripe_simple">
            <script src="https://checkout.stripe.com/checkout.js"
            class="stripe-button"
            data-key="' . $stripe_publishable_key . '"
            data-amount="' . $upfront_payment . '"
            data-email="' . $user_email . '"
            data-zip-code="true"
            data-billing-address="true"
            data-locale="'.get_locale().'"
            data-currency="' . $submission_currency . '"
            data-label="' . esc_html__('Pay with Credit Card', 'homey') . '"
            data-description="' . esc_html__('Reservation Payment', 'homey') . '">
            </script>
        </div>
        <input type="hidden" id="reservation_id_for_stripe" name="reservation_id_for_stripe" value="0">
        <input type="hidden" id="reservation_pay" name="reservation_pay" value="1">
        <input type="hidden" id="is_instance_booking" name="is_instance_booking" value="1">
        <input type="hidden" name="check_in_date" value="'.$check_in_date.'">
        <input type="hidden" name="check_out_date" value="'.$check_out_date.'">
        <input type="hidden" name="guests" value="'.$guests.'">
        <input type="hidden" name="listing_id" value="'.$listing_id.'">
        <input type="hidden" name="extra_options" value="'.$extra_prices.'">
        <input type="hidden" id="guest_message" name="guest_message" value="'.$renter_message.'">
        <input type="hidden" name="userID" value="' . $userID . '">
        <input type="hidden" id="pay_ammout" name="pay_ammout" value="' . $upfront_payment . '">
        ';
    }
}

add_action( 'wp_ajax_homey_booking_paypal_payment', 'homey_booking_paypal_payment' );
if( !function_exists('homey_booking_paypal_payment') ) {
    function homey_booking_paypal_payment() {
        global $current_user;
        $allowded_html = array();
        $blogInfo = esc_url( home_url('/') );
        wp_get_current_user();
        $userID =   $current_user->ID;
        $local = homey_get_localization();
        $reservation_id = intval($_POST['reservation_id']);


        //check security
        $nonce = $_REQUEST['security'];
        if ( ! wp_verify_nonce( $nonce, 'checkout-security-nonce' ) ) {

            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['security_check_text']
                )
            );
            wp_die();
        }

        if(empty($reservation_id)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['something_went_wrong']
                )
            );
            wp_die();
        }

        $reservation = get_post($reservation_id);

        if( $reservation->post_author != $userID ) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['belong_to']
                )
            );
            wp_die();
        }
        $reservation_status = get_post_meta($reservation_id, 'reservation_status', true);

        if( $reservation_status != 'available') {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['something_went_wrong']
                )
            );
            wp_die();
        }


        $currency = homey_option('payment_currency');
        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);
        $extra_options = get_post_meta($reservation_id, 'extra_options', true);


        $listing_id     = intval($reservation_meta['listing_id']);
        $check_in_date  = wp_kses ( $reservation_meta['check_in_date'], $allowded_html );
        $check_out_date = wp_kses ( $reservation_meta['check_out_date'], $allowded_html );
        $guests         = intval($reservation_meta['guests']);


        $is_paypal_live         =  homey_option('paypal_api');
        $host                   =  'https://api.sandbox.paypal.com';
        $upfront_payment          =  floatval( $reservation_meta['upfront'] );
        $submission_curency     =  esc_html( $currency );
        $payment_description    =  esc_html__('Reservation payment on ','homey').$blogInfo;

        $extra_expenses = homey_get_extra_expenses($reservation_id);
        $extra_discount = homey_get_extra_discount($reservation_id);

        $reservation_payment_type = homey_option('reservation_payment');

        if( ! empty($extra_expenses) && $reservation_payment_type == 'full' ) {
            $expenses_total_price = $extra_expenses['expenses_total_price'];
            $upfront_payment = $upfront_payment + $expenses_total_price;
        }

        if( ! empty($extra_discount) && $reservation_payment_type == 'full'  ) {
            $discount_total_price = $extra_discount['discount_total_price'];
            $upfront_payment = $upfront_payment - $discount_total_price;
        }

        $total_price =  number_format( $upfront_payment, 2, '.','' );

        // Check if payal live
        if( $is_paypal_live =='live'){
            $host='https://api.paypal.com';
        }

        $url             =   $host.'/v1/oauth2/token';
        $postArgs        =   'grant_type=client_credentials';

        // Get Access token
        $paypal_token    =   homey_get_paypal_access_token( $url, $postArgs );
        $url             =   $host.'/v1/payments/payment';

        $payment_page_link = homey_get_template_link_2('template/dashboard-payment.php');
        $reservation_page_link = homey_get_template_link('template/dashboard-reservations.php');

        $cancel_link = add_query_arg( array('reservation_id' => $reservation_id), $payment_page_link );
        $return_link = add_query_arg( 'reservation_detail', $reservation_id, $reservation_page_link );

        $payment = array(
            'intent' => 'sale',
            "redirect_urls" => array(
                "return_url" => $return_link,
                "cancel_url" => $cancel_link
            ),
            'payer' => array("payment_method" => "paypal"),
        );

        /* Prepare basic payment details
        *--------------------------------------*/
        $payment['transactions'][0] = array(
            'amount' => array(
                'total' => $total_price,
                'currency' => $submission_curency,
                'details' => array(
                    'subtotal' => $total_price,
                    'tax' => '0.00',
                    'shipping' => '0.00'
                )
            ),
            'description' => $payment_description
        );


        /* Prepare individual items
        *--------------------------------------*/
        $payment['transactions'][0]['item_list']['items'][] = array(
            'quantity' => '1',
            'name' => esc_html__( 'Reservation ID','homey').' '.$reservation_id.' '.esc_html__( 'Listing ID','homey').' '.$listing_id,
            'price' => $total_price,
            'currency' => $submission_curency,
            'sku' => 'Paid Reservation',
        );

        /* Convert PHP array into json format
        *--------------------------------------*/
        $jsonEncode = json_encode($payment);
        $json_response = homey_execute_paypal_request( $url, $jsonEncode, $paypal_token );

        //print_r($json_response);
        foreach ($json_response['links'] as $link) {
            if($link['rel'] == 'execute'){
                $payment_execute_url = $link['href'];
            } else  if($link['rel'] == 'approval_url'){
                $payment_approval_url = $link['href'];
            }
        }

        // Save data in database for further use on processor page
        $output['payment_execute_url'] = $payment_execute_url;
        $output['paypal_token']        = $paypal_token;
        $output['reservation_id']      = $reservation_id;

        $output['listing_id']          = '';
        $output['check_in_date']       = '';
        $output['check_out_date']      = '';
        $output['guests']              = '';
        $output['extra_options']       = '';
        $output['renter_message']      = '';
        $output['is_instance_booking'] = 0;
        $output['is_hourly'] = 0;

        $save_output[$userID]   =   $output;
        update_option('homey_paypal_transfer',$save_output);

        //Add host earning history
        homey_add_earning($reservation_id);

        echo json_encode(
            array(
                'success' => true,
                'message' => 'success',
                'payment_execute_url' => $payment_approval_url
            )
        );

        wp_die();
    }
}

add_action( 'wp_ajax_homey_instance_booking_paypal_payment', 'homey_instance_booking_paypal_payment' );
if( !function_exists('homey_instance_booking_paypal_payment') ) {
    function homey_instance_booking_paypal_payment() {
        global $current_user;
        $allowded_html = array();
        $blogInfo = esc_url( home_url('/') );
        wp_get_current_user();
        $userID =   $current_user->ID;
        $local = homey_get_localization();

        //check security
//        $nonce = $_REQUEST['security'];
//        if ( ! wp_verify_nonce( $nonce, 'checkout-security-nonce' ) ) {
//
//            echo json_encode(
//                array(
//                    'success' => false,
//                    'message' => $local['security_check_text']
//                )
//            );
//            wp_die();
//        }

        $currency = homey_option('payment_currency');

        $listing_id     = intval($_POST['listing_id']);
        $check_in_date  = wp_kses ($_POST['check_in'], $allowded_html);
        $check_out_date = wp_kses ($_POST['check_out'], $allowded_html);
        $renter_message = wp_kses ($_POST['renter_message'], $allowded_html);
        $guests         = intval($_POST['guests']);
        $extra_options  = $_POST['extra_options'];

        $reservor_name  = wp_kses ($_POST['reservor_name'], $allowded_html);
        $reservor_phone  = wp_kses ($_POST['reservor_phone'], $allowded_html);

        $check_availability = check_booking_availability($check_in_date, $check_out_date, $listing_id, $guests);
        $is_available = $check_availability['success'];
        $check_message = $check_availability['message'];

        if(!$is_available) {

            echo json_encode(
                array(
                    'success' => false,
                    'message' => $check_message,
                    'payment_execute_url' => ''
                )
            );
            wp_die();


        } else {

            $booking_type = homey_booking_type_by_id($listing_id);

            if( $booking_type == 'per_week' ) {
                $prices_array = homey_get_weekly_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);
            } else if( $booking_type == 'per_month' ) {
                $prices_array = homey_get_monthly_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);
             } else if( $booking_type == 'per_day_date' ) {
                $prices_array = homey_get_day_date_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);
            } else {
                $prices_array = homey_get_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);
            }

            $is_paypal_live         =  homey_option('paypal_api');
            $host                   =  'https://api.sandbox.paypal.com';
            $upfront_payment          =  floatval( $prices_array['upfront_payment'] );

            $submission_curency     =  esc_html( $currency );
            $payment_description    =  esc_html__('Reservation payment on ','homey').$blogInfo;

            $total_price =  number_format( $upfront_payment, 2, '.','' );

            // Check if payal live
            if( $is_paypal_live =='live'){
                $host='https://api.paypal.com';
            }

            $url             =   $host.'/v1/oauth2/token';
            $postArgs        =   'grant_type=client_credentials';

            // Get Access token
            $paypal_token    =   homey_get_paypal_access_token( $url, $postArgs );
            $url             =   $host.'/v1/payments/payment';

            $instance_payment_page_link = homey_get_template_link_2('template/template-instance-booking.php');
            $reservation_page_link = homey_get_template_link('template/dashboard-reservations.php');

            $cancel_link = add_query_arg(
                array(
                    'check_in' => $check_in_date,
                    'check_out' => $check_out_date,
                    'guest' => $guests,
                    //'extra_options' => $extra_options,
                    'listing_id' => $listing_id,
                ), $instance_payment_page_link );

            $return_link = add_query_arg( 'reservation_detail', $reservation_id, $reservation_page_link );

            $payment = array(
                'intent' => 'sale',
                "redirect_urls" => array(
                    "return_url" => $return_link,
                    "cancel_url" => $cancel_link
                ),
                'payer' => array("payment_method" => "paypal"),
            );

            /* Prepare basic payment details
            *--------------------------------------*/
            $payment['transactions'][0] = array(
                'amount' => array(
                    'total' => $total_price,
                    'currency' => $submission_curency,
                    'details' => array(
                        'subtotal' => $total_price,
                        'tax' => '0.00',
                        'shipping' => '0.00'
                    )
                ),
                'description' => $payment_description
            );


            /* Prepare individual items
            *--------------------------------------*/
            $payment['transactions'][0]['item_list']['items'][] = array(
                'quantity' => '1',
                'name' => esc_html__( 'Listing ID','homey').' '.$listing_id,
                'price' => $total_price,
                'currency' => $submission_curency,
                'sku' => 'Paid Reservation',
            );

            /* Convert PHP array into json format
            *--------------------------------------*/
            $jsonEncode = json_encode($payment);
            $json_response = homey_execute_paypal_request( $url, $jsonEncode, $paypal_token );

            //print_r($json_response);
            foreach ($json_response['links'] as $link) {
                if($link['rel'] == 'execute'){
                    $payment_execute_url = $link['href'];
                } else  if($link['rel'] == 'approval_url'){
                    $payment_approval_url = $link['href'];
                }
            }

            // Save data in database for further use on processor page
            $output['payment_execute_url'] = $payment_execute_url;
            $output['paypal_token']        = $paypal_token;
            $output['reservation_id']      = '';
            $output['listing_id']          = $listing_id;
            $output['check_in_date']       = $check_in_date;
            $output['check_out_date']      = $check_out_date;
            $output['guests']              = $guests;
            $output['extra_options']      = $extra_options;
            $output['renter_message']      = $renter_message;
            $output['is_instance_booking'] = 1;
            $output['is_hourly'] = 0;

            $save_output[$userID]   =   $output;
            update_option('homey_paypal_transfer',$save_output);
            //Add host earning history
            homey_add_earning($reservation_id);
            echo json_encode(
                array(
                    'success' => true,
                    'message' => $local['processing_text'],
                    'payment_execute_url' => $payment_approval_url
                )
            );
            wp_die();
        }
    }
}

add_action( 'wp_ajax_nopriv_homey_instance_step_1', 'homey_instance_step_1' );
add_action( 'wp_ajax_homey_instance_step_1', 'homey_instance_step_1' );
if( !function_exists('homey_instance_step_1') ) {
    function homey_instance_step_1() {
        global $current_user;
        $current_user = wp_get_current_user();
        $userID       = $current_user->ID;
        $local = homey_get_localization();
        $allowded_html = array();

        $first_name     =  wp_kses ( $_POST['first_name'], $allowded_html );
        $last_name    =  wp_kses ( $_POST['last_name'], $allowded_html );
        $email    =  wp_kses ( @$_POST['email'], $allowded_html );
        $phone    =  wp_kses ( $_POST['phone'], $allowded_html );

        $no_login_needed_for_booking = homey_option('no_login_needed_for_booking');

        if($current_user->ID == 0 && $no_login_needed_for_booking == "yes" && isset($_REQUEST['email'])) {
            $user = get_user_by('email', $email);

            if(empty(trim($email))){
                echo json_encode(
                    array(
                        'success' => false,
                        'message' => esc_html__('Enter email address', 'homey')
                    )
                );
                wp_die();
            }

            if (isset($user->ID)) {
                add_filter('authenticate', 'for_reservation_nop_auto_login', 3, 10);
                for_reservation_nop_auto_login($user);
            } else { //create user from email
                $user_login = $email;
                $user_email = $email;
                $user_pass = wp_generate_password(8, false);
                $userdata = compact('user_login', 'user_email', 'user_pass');
                $new_user_id = wp_insert_user($userdata);

                if($new_user_id > 0){
                    homey_wp_new_user_notification( $new_user_id, $user_pass );
                }

                update_user_meta($new_user_id, 'viaphp', 1);

                // log in automatically
                if (!is_user_logged_in()) {
                    $user = get_user_by('email', $email);

                    add_filter('authenticate', 'for_reservation_nop_auto_login', 3, 10);
                    for_reservation_nop_auto_login($user);
                }
            }
        }

        $current_user = wp_get_current_user();
        $userID       = $current_user->ID;

        if ( !is_user_logged_in() || $userID === 0 ) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['login_for_reservation']
                )
            );
            wp_die();
        }

        if(empty($first_name)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['first_name_req']
                )
            );
            wp_die();
        }

        if(empty($last_name)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['last_name_req']
                )
            );
            wp_die();
        }

        if(empty($phone)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['phone_req']
                )
            );
            wp_die();
        }


        update_user_meta( $userID, 'first_name', $first_name);
        update_user_meta( $userID, 'last_name', $last_name);
        update_user_meta( $userID, 'phone', $phone);

        echo json_encode(
            array(
                'success' => true,
                'message' => ''
            )
        );
        wp_die();
    }
}


if(!function_exists('homey_get_reservation_label')) {
    function homey_get_reservation_label($status, $reservation_id = null) {
        $status_label = '<span class="label label-warning">'.esc_html__(ucfirst($status), 'homey').'</span>';
        $local = homey_get_localization();

        if(homey_listing_guest($reservation_id)) {

            if($status == 'under_review') {
                $status_label = '<span class="label label-warning">'.$local['under_review_label'].'</span>';
            } elseif($status == 'available') {
                $status_label = '<span class="label label-secondary">'.$local['res_avail_label'].'</span>';
            }

        } else {
            if($status == 'under_review') {
                $status_label = '<span class="label label-secondary">'.$local['new_label'].'</span>';

            } elseif($status == 'available') {
                $status_label = '<span class="label label-secondary">'.$local['payment_process_label'].'</span>';
            }
        }

        if($status == 'booked') {
            $status_label = '<span class="label label-success">'.$local['res_booked_label'].'</span>';

        } elseif ($status == 'declined') {
            $status_label = '<span class="label label-danger">'.$local['res_declined_label'].'</span>';

        } elseif ($status == 'cancelled') {
            $status_label = '<span class="label label-grey">'.$local['res_cancelled_label'].'</span>';
        }

        return $status_label;

    }
}

if(!function_exists('homey_reservation_label')) {
    function homey_reservation_label($status, $reservation_id = null) {
        echo homey_get_reservation_label($status, $reservation_id);
    }
}

if(!function_exists('homey_get_reservation_notification')) {
    function homey_get_reservation_notification($status, $reservation_id = null) {
        $notification = '';
        $local = homey_get_localization();

        if( homey_listing_guest($reservation_id) ) {

            if($status == 'under_review') {
                $notification = '<div class="alert alert-success alert-dismissible" role="alert">
                                <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>
                                '.esc_html__('Your request has been submitted to the host to be confirmed availability.', 'homey').'
                            </div>';
            } elseif($status == 'available') {
                $notification = '<div class="alert alert-info alert-dismissible" role="alert">
                                        <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>
                                        '.esc_html__('So far so good! Host confirmed availability for this reservation. Complete the payment due.', 'homey').'
                                    </div>';
            } elseif($status == 'booked') {
                $notification = '<div class="alert alert-success alert-dismissible" role="alert">
                                        <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>
                                        '.esc_html__('Well done! Payment received the reservation has been booked.', 'homey').'
                                    </div>';

            } elseif($status == 'declined') {
                $notification = '<div class="alert alert-danger alert-dismissible" role="alert">
                                    <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>
                                    '.esc_html__('Your reservation has been declined by the host', 'homey').'
                                </div>';
            } elseif($status == 'cancelled') {
                $notification = '<div class="alert alert-danger alert-dismissible" role="alert">
                                    <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>
                                    '.esc_html__('You have cancellated the reservation', 'homey').'
                                </div>';
            }

        } else {
            if($status == 'under_review') {
                $notification = '<div class="alert alert-info alert-dismissible" role="alert">
                                <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>
                                '.esc_html__('Hooray! You received a new reservation. Confirm Availability.', 'homey').'
                            </div>';
            } elseif($status == 'available') {
                $notification = '<div class="alert alert-success alert-dismissible" role="alert">
                                        <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>
                                        '.esc_html__('You confirmed availability for this reservation.', 'homey').'
                                    </div>';
            } elseif($status == 'booked') {
                $notification = '<div class="alert alert-success alert-dismissible" role="alert">
                                        <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>
                                        '.esc_html__('Congratulations! The reservation has been booked.', 'homey').'
                                    </div>';

            } elseif($status == 'declined') {
                $notification = '<div class="alert alert-danger alert-dismissible" role="alert">
                                    <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>
                                    '.esc_html__('You have declined the reservation', 'homey').'
                                </div>';

            } elseif($status == 'cancelled') {
                $notification = '<div class="alert alert-danger alert-dismissible" role="alert">
                                    <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>
                                    '.esc_html__('The reservation has been cancellated', 'homey').'
                                </div>';
            }
        }

        if($status == 'not_owner') {
            $notification = '<div class="alert alert-danger alert-dismissible" role="alert">
                                <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>
                                '.$local['listing_owner_text'].'
                            </div>';
        }

        if($status == 'host_payment_method') {
            $notification = '<div class="alert alert-danger alert-dismissible" role="alert">
                                <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>
                                '.esc_html__('Please setup your payment method first', 'homey').'
                            </div>';
        }

        if($status == 'sent_for_verification') {
            $notification = '<div class="alert alert-success alert-dismissible" role="alert">
                                <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>
                                '.esc_html__('Request successfully sent for verification', 'homey').'
                            </div>';
        }

        if($status == 'waiting_host_payment_verification') {
            $notification = '<div class="alert alert-success alert-dismissible" role="alert">
                                <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>
                                '.esc_html__('Payment Received? Mark as Paid', 'homey').'
                            </div>';
        }

        return $notification;

    }
}



if(!function_exists('homey_reservation_notification')) {
    function homey_reservation_notification($status, $reservation_id = "") {
        echo homey_get_reservation_notification($status, $reservation_id);
    }
}

if(!function_exists('homey_get_reservation_action_20_Sep_2019')) {
    function homey_get_reservation_action_20_Sep_2019($status, $upfront_payment, $payment_link, $ID, $class) {
        $action = '';
        $local = homey_get_localization();

        $offsite_payment = homey_option('off-site-payment');

        if(homey_is_renter()) {

            if($status == 'under_review') {
                $action = '<span class="btn btn-success-outlined '.esc_attr($class).'"><i class="fa fa-check-circle-o"></i>'.esc_html__('Submitted', 'homey').'</span>';

                $action .= '<button class="btn btn-grey-light '.esc_attr($class).'" data-toggle="collapse" id="cancel-reservation-btn" data-target="#cancel-reservation" aria-expanded="false" aria-controls="collapseExample">'.esc_html__('Cancel', 'homey').'</button>';

            } elseif ($status == 'available') {
                $action = '<a href="'.esc_url($payment_link).'" class="btn btn-success '.esc_attr($class).'">'.esc_html__('Pay Now', 'homey').' '.$upfront_payment.'</a>';

                $action .= '<button class="btn btn-grey-light '.esc_attr($class).'" data-toggle="collapse" id="cancel-reservation-btn" data-target="#cancel-reservation" aria-expanded="false" aria-controls="collapseExample">'.esc_html__('Cancel', 'homey').'</button>';

            } elseif ($status == 'booked') {
                $action = '<span class="btn btn-success-outlined '.esc_attr($class).'"><i class="fa fa-check-circle-o"></i> '.esc_html__('Booked', 'homey').'</span>';

                $action .= '<button class="btn btn-grey-light btn-full-width" data-toggle="collapse" id="cancel-reservation-btn" data-target="#cancel-reservation" aria-expanded="false" aria-controls="collapseExample">'.esc_html__('Cancel', 'homey').'</button>';

            } elseif($status == 'waiting_host_payment_verification') {
                $action = '<span class="btn btn-warning-outlined '.esc_attr($class).'"> '.esc_html__('Waiting Approval', 'homey').'</span>';
            }

        } else {

            if($status == 'under_review') {

                if($offsite_payment == 1) {
                    $action = '<button data-reservation_id="'.intval($ID).'" class="confirm-offsite-reservation btn btn-success '.esc_attr($class).'">'.esc_html__('Confirm Availability', 'homey').'</button>';
                } else {
                    $action = '<button data-reservation_id="'.intval($ID).'" class="confirm-reservation btn btn-success '.esc_attr($class).'">'.esc_html__('Confirm Availability', 'homey').'</button>';
                }

                $action .= '<button class="btn btn-grey-light '.esc_attr($class).'" data-toggle="collapse" id="decline-reservation-btn" data-target="#decline-reservation" aria-expanded="false" aria-controls="collapseExample">'.esc_html__('Decline', 'homey').'</button>';

            } elseif ($status == 'available') {
                $action = '<span class="btn btn-success-outlined '.esc_attr($class).'"><i class="fa fa-check-circle-o"></i>'.esc_html__('Available', 'homey').'</span>';

                $action .= '<button class="btn btn-grey-light '.esc_attr($class).'" data-toggle="collapse" id="decline-reservation-btn" data-target="#decline-reservation" aria-expanded="false" aria-controls="collapseExample">'.esc_html__('Decline', 'homey').'</button>';


            } elseif ($status == 'booked') {
                $action = '<span class="btn btn-success-outlined '.esc_attr($class).'"><i class="fa fa-check-circle-o"></i> '.esc_html__('Booked', 'homey').'</span>';

                $action .= '<button class="btn btn-grey-light btn-full-width" data-toggle="collapse" id="cancel-reservation-btn" data-target="#cancel-reservation" aria-expanded="false" aria-controls="collapseExample">'.esc_html__('Cancel', 'homey').'</button>';

            } elseif($status == 'waiting_host_payment_verification') {
                $action = '<a href="#" data-id="'.intval($ID).'" class="mark-as-paid btn btn-success '.esc_attr($class).'">'.esc_html__('Payment Received? Mark as Paid', 'homey').'</a>';
            }

        }

        if ($status == 'declined') {
            $action = '<span class="btn btn-danger-outlined '.esc_attr($class).'"><i class="fa fa-check-circle-o"></i> '.esc_html__('Declined', 'homey').'</span>';
        }

        if( !homey_is_renter() && $status == 'under_review') {
            $action .= '<button class="btn btn-grey-light '.esc_attr($class).'" data-toggle="modal" data-target="#modal-extra-expenses">'.esc_html__('Extra Expenses', 'homey').'</button>';

            $action .= '<button class="btn btn-grey-light '.esc_attr($class).'" data-toggle="modal" data-target="#modal-discount" aria-expanded="false" aria-controls="collapseExample">'.esc_html__('Discount', 'homey').'</button>';
        }

        return $action;

    }
}

if(!function_exists('homey_get_reservation_action')) {
    function homey_get_reservation_action($status, $upfront_payment, $payment_link, $ID, $class) {
        $action = '';
        $local = homey_get_localization();

        $offsite_payment = homey_option('off-site-payment');

        if(homey_listing_guest($ID)) {

            if($status == 'under_review') {
                $action = '<span class="btn btn-success-outlined '.esc_attr($class).'"><i class="fa fa-check-circle-o"></i>'.esc_html__('Submitted', 'homey').'</span>';

                $action .= '<button class="btn btn-grey-light '.esc_attr($class).'" data-toggle="collapse" id="cancel-reservation-btn" data-target="#cancel-reservation" aria-expanded="false" aria-controls="collapseExample">'.esc_html__('Cancel', 'homey').'</button>';

            } elseif ($status == 'available') {

                if( homey_is_woocommerce() ) {

                    $action = '<a href="#" data-reservation_id="'.intval($ID).'" class="homey-woo-reservation-pay btn btn-success '.esc_attr($class).'">'.esc_html__('Pay Now', 'homey').' '.$upfront_payment.'</a>';

                } else {
                    $action = '<a href="'.esc_url($payment_link).'" class="btn btn-success '.esc_attr($class).'">'.esc_html__('Pay Now', 'homey').' '.$upfront_payment.'</a>';
                }

                $action .= '<button class="btn btn-grey-light '.esc_attr($class).'" data-toggle="collapse" id="cancel-reservation-btn" data-target="#cancel-reservation" aria-expanded="false" aria-controls="collapseExample">'.esc_html__('Cancel', 'homey').'</button>';

            } elseif ($status == 'booked') {
                $action = '<span class="btn btn-success-outlined '.esc_attr($class).'"><i class="fa fa-check-circle-o"></i> '.esc_html__('Booked', 'homey').'</span>';

                $action .= '<button class="btn btn-grey-light btn-full-width" data-toggle="collapse" id="cancel-reservation-btn" data-target="#cancel-reservation" aria-expanded="false" aria-controls="collapseExample">'.esc_html__('Cancel', 'homey').'</button>';

            } elseif($status == 'waiting_host_payment_verification') {
                $action = '<span class="btn btn-warning-outlined '.esc_attr($class).'"> '.esc_html__('Waiting Approval', 'homey').'</span>';
            }

        } else {

            if($status == 'under_review') {

                if($offsite_payment == 1) {
                    $action = '<button data-reservation_id="'.intval($ID).'" class="confirm-offsite-reservation btn btn-success '.esc_attr($class).'">'.esc_html__('Confirm Availability', 'homey').'</button>';
                } else {
                    $action = '<button data-reservation_id="'.intval($ID).'" class="confirm-reservation btn btn-success '.esc_attr($class).'">'.esc_html__('Confirm Availability', 'homey').'</button>';
                }

                $action .= '<button class="btn btn-grey-light '.esc_attr($class).'" data-toggle="collapse" id="decline-reservation-btn" data-target="#decline-reservation" aria-expanded="false" aria-controls="collapseExample">'.esc_html__('Decline', 'homey').'</button>';

            } elseif ($status == 'available') {
                $action = '<span class="btn btn-success-outlined '.esc_attr($class).'"><i class="fa fa-check-circle-o"></i>'.esc_html__('Available', 'homey').'</span>';

                $action .= '<button class="btn btn-grey-light '.esc_attr($class).'" data-toggle="collapse" id="decline-reservation-btn" data-target="#decline-reservation" aria-expanded="false" aria-controls="collapseExample">'.esc_html__('Decline', 'homey').'</button>';


            } elseif ($status == 'booked') {
                $action = '<span class="btn btn-success-outlined '.esc_attr($class).'"><i class="fa fa-check-circle-o"></i> '.esc_html__('Booked', 'homey').'</span>';

                $action .= '<button class="btn btn-grey-light btn-half-width" data-toggle="collapse" id="cancel-reservation-btn" data-target="#cancel-reservation" aria-expanded="false" aria-controls="collapseExample">'.esc_html__('Cancel', 'homey').'</button>';

            } elseif($status == 'waiting_host_payment_verification') {
                $action = '<a href="#" data-id="'.intval($ID).'" class="mark-as-paid btn btn-success '.esc_attr($class).'">'.esc_html__('Payment Received? Mark as Paid', 'homey').'</a>';
            }

        }

        if ($status == 'declined') {
            $action = '<span class="btn btn-danger-outlined '.esc_attr($class).'"><i class="fa fa-check-circle-o"></i> '.esc_html__('Declined', 'homey').'</span>';
        }

        if( !homey_listing_guest($ID) && $status == 'under_review') {
            $action .= '<button class="btn btn-grey-light '.esc_attr($class).'" data-toggle="modal" data-target="#modal-extra-expenses">'.esc_html__('Extra Expenses', 'homey').'</button>';

            $action .= '<button class="btn btn-grey-light '.esc_attr($class).'" data-toggle="modal" data-target="#modal-discount" aria-expanded="false" aria-controls="collapseExample">'.esc_html__('Discount', 'homey').'</button>';
        }

        return $action;

    }
}

if(!function_exists('homey_reservation_action')) {
    function homey_reservation_action($status, $upfront_payment, $payment_link, $ID, $class) {
        echo homey_get_reservation_action($status, $upfront_payment, $payment_link, $ID, $class);
    }
}

add_action( 'wp_ajax_homey_make_date_unavaiable', 'homey_make_date_unavaiable' );
if(!function_exists('homey_make_date_unavaiable')) {
    function homey_make_date_unavaiable() {
        global $current_user;
        $now = time();
        $daysAgo = $now-3*24*60*60;

        $current_user   = wp_get_current_user();
        $userID         = $current_user->ID;
        $local          = homey_get_localization();

        $listing_id     = intval($_POST['listing_id']);
        $the_post       = get_post($listing_id);
        $post_owner     = $the_post->post_author;
        $selected_date = $_POST['selected_date'];

        if ( !is_user_logged_in() || $userID === 0 ) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => esc_html__('Login required', 'homey')
                )
            );
            wp_die();
        }

        if(!is_numeric($listing_id)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => esc_html__('Something went wrong, please contact site administer', 'homey')
                )
            );
            wp_die();
        }

        if( ($userID != $post_owner) && !homey_is_admin())  {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => esc_html__("You don't have rights to do this.", 'homey')
                )
            );
            wp_die();
        }

        $check_in_date  = $selected_date;

        $reservation_unavailable_array = get_post_meta($listing_id, 'reservation_unavailable', true );

        if( !is_array($reservation_unavailable_array) || empty($reservation_unavailable_array) ) {
            $reservation_unavailable_array  = array();
        }

        $unix_time_start = strtotime ($check_in_date);

        if ($unix_time_start > $daysAgo) {
            $check_in       =   new DateTime($check_in_date);
            $check_in_unix =   $check_in->getTimestamp();

            if( array_key_exists($check_in_unix, $reservation_unavailable_array)) {
                unset($reservation_unavailable_array[$check_in_unix]);
                echo json_encode(
                    array(
                        'success' => true,
                        'message' => 'made_available'
                    )
                );
            } else {
                $reservation_unavailable_array[$check_in_unix] = $listing_id;
                echo json_encode(
                    array(
                        'success' => true,
                        'message' => 'made_unavailable'
                    )
                );
            }
        }

        //return $reservation_unavailable_array;

        //$unavailable_days_array = homey_get_unavailable_dates($listing_id, $selected_date);
        update_post_meta($listing_id, 'reservation_unavailable', $reservation_unavailable_array);

        /*echo json_encode(
            array(
                'success' => true,
                'message' => ''
            )
         );*/
        wp_die();
    }
}

add_action( 'wp_ajax_homey_reservation_del', 'homey_reservation_del' );
if(!function_exists('homey_reservation_del')) {
    function homey_reservation_del() {
        $reservation_id = intval($_POST['reservation_id']);

        homey_delete_reservation($reservation_id);
        wp_delete_post( $reservation_id );
        echo json_encode(
            array(
                'success' => true,
                'url' => homey_get_template_link('template/dashboard-reservations.php')
            )
        );
        wp_die();
    }
}

add_action( 'wp_ajax_homey_reservation_mark_paid', 'homey_reservation_mark_paid' );
if(!function_exists('homey_reservation_mark_paid')) {
    function homey_reservation_mark_paid() {
        $reservation_id = intval($_POST['reservation_id']);
        update_post_meta($reservation_id, 'reservation_status', 'booked');
        $admin_email = get_option( 'admin_email' );

        // Emails
        $listing_owner = get_post_meta($reservation_id, 'listing_owner', true);
        $listing_renter = get_post_meta($reservation_id, 'listing_renter', true);
        $listing_id = get_post_meta($reservation_id, 'reservation_listing_id', true);

        //Book dates
        $booked_days_array = homey_make_days_booked($listing_id, $reservation_id);
        update_post_meta($listing_id, 'reservation_dates', $booked_days_array);

        //Remove Pending Dates
        $pending_dates_array = homey_remove_booking_pending_days($listing_id, $reservation_id);
        update_post_meta($listing_id, 'reservation_pending_dates', $pending_dates_array);

        $renter = homey_usermeta($listing_renter);
        $renter_email = $renter['email'];

        $owner = homey_usermeta($listing_owner);
        $owner_email = $owner['email'];

        $email_args = array('reservation_detail_url' => reservation_detail_link($reservation_id) );
        homey_email_composer( $renter_email, 'booked_reservation', $email_args );
        homey_email_composer( $admin_email, 'admin_booked_reservation', $email_args );

        // on mark paid generating invoice, if not in need you can delete or comment the code below
        $time = time();
        $date = date( 'Y-m-d G:i:s', $time );

        homey_generate_invoice( 'reservation','one_time', $reservation_id, $date, $listing_renter, 0, 0, '', 'Self' );
        // on mark paid generating invoice, if not in need you can delete or comment the code above

        echo json_encode(
            array(
                'success' => true,
                'url' => homey_get_template_link('template/dashboard-reservations.php')
            )
        );
        wp_die();
    }
}

add_action( 'wp_ajax_homey_save_extra_expenses', 'homey_save_extra_expenses' );
if(!function_exists('homey_save_extra_expenses')) {
    function homey_save_extra_expenses() {
        global $current_user;
        $current_user   = wp_get_current_user();
        $userID         = $current_user->ID;
        $local          = homey_get_localization();
        $store_feeds_array = array();
        $temp_array     = array();
        $allowded_html  = array();

        $reservation_id     = intval($_POST['reservation_id']);
        $the_post       = get_post($reservation_id);
        $post_owner     = $the_post->post_author;
        $expense_name = $_POST['expense_name'];
        $expense_value  = $_POST['expense_value'];

        if ( !is_user_logged_in() || $userID === 0 ) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => esc_html__('Login required', 'homey')
                )
            );
            wp_die();
        }

        if(!is_numeric($reservation_id)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => esc_html__('Something went wrong, please contact site administer', 'homey')
                )
            );
            wp_die();
        }



        foreach ($expense_value as $key => $value) {
            if(!empty($value)) {
                $temp_array['expense_value'] = esc_html($value);
                $temp_array['expense_name'] = esc_html($expense_name[$key]);
                $store_feeds_array[] = $temp_array;
            }
        }

        if( !empty($store_feeds_array)) {
            update_post_meta($reservation_id, 'homey_reservation_extra_expenses', $store_feeds_array);
        }

        $dashboard_reservations = homey_get_template_link('template/dashboard-reservations.php');
        $return_url  = add_query_arg(
            array(
                'reservation_detail' => $reservation_id
            ),
            $dashboard_reservations
        );

        echo json_encode(
            array(
                'success' => true,
                'message' => esc_html__('Successfully saved', 'homey'),
                'url' => $return_url,
            )
        );
        wp_die();

    }
}

add_action( 'wp_ajax_homey_remove_extra_expense', 'homey_remove_extra_expense' );
if(!function_exists('homey_remove_extra_expense')) {
    function homey_remove_extra_expense() {
        global $current_user;
        $current_user   = wp_get_current_user();
        $userID         = $current_user->ID;
        $local          = homey_get_localization();
        $allowded_html  = array();

        $reservation_id     = intval($_POST['reservation_id']);
        $the_post       = get_post($reservation_id);
        $post_owner     = $the_post->post_author;
        $remove_index = $_POST['remove_index'];

        if ( !is_user_logged_in() || $userID === 0 ) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => esc_html__('Login required', 'homey')
                )
            );
            wp_die();
        }

        if(!is_numeric($reservation_id) || !intval($reservation_id)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => esc_html__('Something went wrong, please contact site administer', 'homey')
                )
            );
            wp_die();
        }


        // Remove expense
        $homey_expense_meta = get_post_meta($reservation_id, 'homey_reservation_extra_expenses',true);
        $feed_for_delete =   $homey_expense_meta[$remove_index]['expense_name'];
        unset($homey_expense_meta[$remove_index]);
        update_post_meta($reservation_id, 'homey_reservation_extra_expenses', $homey_expense_meta);

        echo json_encode(
            array(
                'success' => true,
                'message' => esc_html__("Removed Successfully.", 'homey')
            )
        );
        wp_die();

    }
}

add_action( 'wp_ajax_homey_save_discounts', 'homey_save_discounts' );
if(!function_exists('homey_save_discounts')) {
    function homey_save_discounts() {
        global $current_user;
        $current_user   = wp_get_current_user();
        $userID         = $current_user->ID;
        $local          = homey_get_localization();
        $store_feeds_array = array();
        $temp_array     = array();
        $allowded_html  = array();

        $reservation_id     = intval($_POST['reservation_id']);
        $the_post       = get_post($reservation_id);
        $post_owner     = $the_post->post_author;
        $discount_name = $_POST['discount_name'];
        $discount_value  = $_POST['discount_value'];

        if ( !is_user_logged_in() || $userID === 0 ) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => esc_html__('Login required', 'homey')
                )
            );
            wp_die();
        }

        if(!is_numeric($reservation_id)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => esc_html__('Something went wrong, please contact site administer', 'homey')
                )
            );
            wp_die();
        }


        foreach ($discount_value as $key => $value) {
            if(!empty($value)) {
                $temp_array['discount_value'] = esc_html($value);
                $temp_array['discount_name'] = esc_html($discount_name[$key]);
                $store_feeds_array[] = $temp_array;
            }
        }

        if( !empty($store_feeds_array)) {
            update_post_meta($reservation_id, 'homey_reservation_discount', $store_feeds_array);
        }

        $dashboard_reservations = homey_get_template_link('template/dashboard-reservations.php');
        $return_url  = add_query_arg(
            array(
                'reservation_detail' => $reservation_id
            ),
            $dashboard_reservations
        );

        echo json_encode(
            array(
                'success' => true,
                'message' => esc_html__('Successfully saved', 'homey'),
                'url' => $return_url,
            )
        );
        wp_die();

    }
}

add_action( 'wp_ajax_homey_remove_discount', 'homey_remove_discount' );
if(!function_exists('homey_remove_discount')) {
    function homey_remove_discount() {
        global $current_user;
        $current_user   = wp_get_current_user();
        $userID         = $current_user->ID;
        $local          = homey_get_localization();
        $allowded_html  = array();

        $reservation_id     = intval($_POST['reservation_id']);
        $the_post       = get_post($reservation_id);
        $post_owner     = $the_post->post_author;
        $remove_index = $_POST['remove_index'];

        if ( !is_user_logged_in() || $userID === 0 ) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => esc_html__('Login required', 'homey')
                )
            );
            wp_die();
        }

        if(!is_numeric($reservation_id) || !intval($reservation_id)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => esc_html__('Something went wrong, please contact site administer', 'homey')
                )
            );
            wp_die();
        }


        // Remove discount
        $homey_discount_meta = get_post_meta($reservation_id, 'homey_reservation_discount',true);
        $feed_for_delete =   $homey_discount_meta[$remove_index]['discount_name'];
        unset($homey_discount_meta[$remove_index]);
        update_post_meta($reservation_id, 'homey_reservation_discount', $homey_discount_meta);

        echo json_encode(
            array(
                'success' => true,
                'message' => esc_html__("Removed Successfully.", 'homey')
            )
        );
        wp_die();

    }
}

/*if (!function_exists("homey_get_unavailable_dates")) {
    function homey_get_unavailable_dates($listing_id, $selected_date) {
        $now = time();
        $daysAgo = $now-3*24*60*60;

        $check_in_date  = $selected_date;

        $reservation_unavailable_array = get_post_meta($listing_id, 'reservation_unavailable', true );

        if( !is_array($reservation_unavailable_array) || empty($reservation_unavailable_array) ) {
            $reservation_unavailable_array  = array();
        }

        $unix_time_start = strtotime ($check_in_date);

        if ($unix_time_start > $daysAgo) {
            $check_in       =   new DateTime($check_in_date);
            $check_in_unix =   $check_in->getTimestamp();

            if( array_key_exists($check_in_unix, $reservation_unavailable_array) {
                unset($reservation_unavailable_array[$check_in_unix]);
            } else {
                $reservation_unavailable_array[$check_in_unix] = $listing_id;
            }
        }

        return $reservation_unavailable_array;
    }
}*/

















