<?php
if(!function_exists('homey_get_host_fee_percent')) {
    function homey_get_host_fee_percent($host_id = null) {
        $host_fee_percent = homey_option('host_fee');
        if(empty($host_fee_percent)) {
            return '0';
        }
        return $host_fee_percent;
    }
}

if(!function_exists('homey_host_fee_percent')) {
    function homey_host_fee_percent($host_id = null) {
        echo homey_get_host_fee_percent($host_id);
    }
}

if(!function_exists('homey_get_minimum_payout_amount')) {
    function homey_get_minimum_payout_amount() {
        $minimum_payout_amount = homey_option('minimum_payout_amount');
        if(empty($minimum_payout_amount)) {
            return '0';
        }
        return $minimum_payout_amount;
    }
}

if(!function_exists('homey_minimum_payout_amount')) {
    function homey_minimum_payout_amount() {
        echo homey_get_minimum_payout_amount();
    }
}

if(!function_exists('homey_add_earning')) {
    function homey_add_earning($reservation_id) {
        global $wpdb;
        $allowded_html = array();

        $reservation_payment = homey_option('reservation_payment');
        if($reservation_payment != 'percent' && $reservation_payment != 'full') {
            return;
        }
       

        //host fee in % set by admin
        $host_fee_percent = homey_get_host_fee_percent();

        if(empty($reservation_id)) {
            return;
        }
        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);
        $extra_options = get_post_meta($reservation_id, 'extra_options', true);

        $listing_host = get_post_meta($reservation_id, 'listing_owner', true);
        $listing_guest = get_post_meta($reservation_id, 'listing_renter', true);
        $is_hourly = get_post_meta($reservation_id, 'is_hourly', true);

        $listing_id     = intval($reservation_meta['listing_id']);
        $check_in_date  = wp_kses ( $reservation_meta['check_in_date'], $allowded_html );
        $check_out_date = '';
        $guests         = intval($reservation_meta['guests']);

        $booking_type = homey_booking_type_by_id($listing_id);
        
        if($is_hourly != 'yes') {
            $check_out_date = wp_kses ( @$reservation_meta['check_out_date'], $allowded_html );

            if( $booking_type == 'per_week' ) {
                $prices_array = homey_get_weekly_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);
            } else if( $booking_type == 'per_month' ) {
                $prices_array = homey_get_monthly_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);
            } else if( $booking_type == 'per_day_date' ) {
                $prices_array = homey_get_day_date_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);
            } else {
                $prices_array = homey_get_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);
            }

        } else {
            $check_in_hour = get_post_meta($reservation_id, 'reservation_checkin_hour', true);
            $check_out_hour = get_post_meta($reservation_id, 'reservation_checkout_hour', true);

            $prices_array = homey_get_hourly_prices($check_in_hour, $check_out_hour, $listing_id, $guests, $extra_options);
        }
        
        
        $total_price = $prices_array['total_price'];
        $services_fee = $prices_array['services_fee'];
        $security_deposit = $prices_array['security_deposit'];
        $upfront_payment = $prices_array['upfront_payment'];
        $payment_due = $prices_array['balance'];

        $extra_expenses = homey_get_extra_expenses($reservation_id);
        $extra_discount = homey_get_extra_discount($reservation_id);

        if(!empty($extra_expenses)) {
            $expenses_total_price = $extra_expenses['expenses_total_price'];
            $total_price = $total_price + $expenses_total_price;
        }

        if(!empty($extra_discount)) {
            $discount_total_price = $extra_discount['discount_total_price'];
            $total_price = $total_price - $discount_total_price;
        }

        if(homey_option('reservation_payment') == 'full') {
            $upfront_payment = $total_price; 
        }


        //deduct services fee and security deposit from total
        $sf_and_sd = $services_fee + $security_deposit;

        //chargeable amount for host fee
        $chargeable_amount = $total_price - $sf_and_sd;

        //Host fee
        $host_fee = ($host_fee_percent / 100) * $chargeable_amount;

        /*
        * Calculate net earning.
        * Net earning will be on upfront payment
        */
        $net_earnings = $upfront_payment - $services_fee; //deduct services fee from $upfront
        $net_earnings = $net_earnings - $host_fee; //deduct host fee from upfront_payment
        $net_earnings = $net_earnings - $security_deposit; //deduct security deposit

        $table_name = $wpdb->prefix . 'homey_earnings';

        $is_added = $wpdb->query(
            "SELECT * FROM $table_name WHERE user_id = $listing_host AND  guest_id = $listing_guest AND listing_id = $listing_id AND reservation_id = $reservation_id"
        );

        if($is_added < 1){
            $order_id = $wpdb->query( $wpdb->prepare(
                "INSERT INTO $table_name
            ( user_id, guest_id, listing_id, reservation_id, services_fee, host_fee, upfront_payment, payment_due, net_earnings, total_amount, security_deposit, chargeable_amount, host_fee_percent )
            VALUES ( %d, %d, %d, %d, %s, %s, %s, %s, %s, %s, %s, %s, %s )",
                $listing_host,
                $listing_guest,
                $listing_id,
                $reservation_id,
                $services_fee,
                $host_fee,
                $upfront_payment,
                $payment_due,
                $net_earnings,
                $total_price,
                $security_deposit,
                $chargeable_amount,
                $host_fee_percent
            ) );

            if($order_id) {
                $all_fees = $services_fee + $security_deposit + $host_fee;
                $total_net_earnings = $total_price - $all_fees;
                homey_add_host_earnings($listing_host, $net_earnings, $total_net_earnings);
                homey_add_guest_security($listing_guest, $security_deposit);
            }
        }
    }
}

if(!function_exists('homey_add_host_earnings')) {
    function homey_add_host_earnings($listing_host, $net_earnings, $total_net_earnings) {
        $current_available_earnings = homey_get_host_available_earnings($listing_host);
        $current_total_earnings = homey_get_host_total_earnings($listing_host);

        $available_earnings = $current_available_earnings + $net_earnings;
        $total_earnings = $current_total_earnings + $total_net_earnings;

        update_user_meta($listing_host, 'homey_host_available_earnings', $available_earnings);
        update_user_meta($listing_host, 'homey_host_total_earnings', $total_earnings);
    }
}

if(!function_exists('homey_get_host_available_earnings')) {
    function homey_get_host_available_earnings($host_id) {
        $earnings = get_user_meta($host_id, 'homey_host_available_earnings', true);
        if(empty($earnings)) {
            return '0';
        }
        return $earnings;
    }
}

if(!function_exists('homey_get_host_total_earnings')) {
    function homey_get_host_total_earnings($host_id) {
        $earnings = get_user_meta($host_id, 'homey_host_total_earnings', true);
        if(empty($earnings)) {
            return '0';
        }
        return $earnings;
    }
}

if(!function_exists('homey_adjust_host_available_balance')) {
    function homey_adjust_host_available_balance($host_id, $payout_amount) {
        $current_available_earnings = homey_get_host_available_earnings($host_id);
        $available_earnings = $current_available_earnings - $payout_amount;
        update_user_meta($host_id, 'homey_host_available_earnings', $available_earnings);
    }
}

if(!function_exists('homey_adjust_host_available_balance_2')) {
    function homey_adjust_host_available_balance_2($host_id, $payout_amount) {
        $current_available_earnings = homey_get_host_available_earnings($host_id);
        $available_earnings = $current_available_earnings - $payout_amount;
        update_user_meta($host_id, 'homey_host_available_earnings', $available_earnings);

        $current_total_earnings = homey_get_host_total_earnings($host_id);
        $total_earnings = $current_total_earnings - $payout_amount;
        update_user_meta($host_id, 'homey_host_total_earnings', $total_earnings);
    }
}

if(!function_exists('homey_addto_host_available_balance')) {
    function homey_addto_host_available_balance($host_id, $payout_amount) {
        $current_available_earnings = homey_get_host_available_earnings($host_id);
        $available_earnings = $current_available_earnings + $payout_amount;
        update_user_meta($host_id, 'homey_host_available_earnings', $available_earnings);
    }
}

if(!function_exists('homey_addto_host_available_balance_2')) {
    function homey_addto_host_available_balance_2($host_id, $payout_amount) {
        $current_available_earnings = homey_get_host_available_earnings($host_id);
        $available_earnings = $current_available_earnings + $payout_amount;
        update_user_meta($host_id, 'homey_host_available_earnings', $available_earnings);

        $current_total_earnings = homey_get_host_total_earnings($host_id);
        $total_earnings = $current_total_earnings + $payout_amount;
        update_user_meta($host_id, 'homey_host_total_earnings', $total_earnings);
    }
}

if(!function_exists('homey_add_guest_security')) {
    function homey_add_guest_security($listing_guest, $security_deposit) {
        $current_total_security = homey_get_get_security_deposit($listing_guest);

        $total_security_deposit = $current_total_security + $security_deposit;

        update_user_meta($listing_guest, 'homey_guest_total_security_deposit', $total_security_deposit);
    }
}

if(!function_exists('homey_get_get_security_deposit')) {
    function homey_get_get_security_deposit($guest_id) {
        $security_deposit = get_user_meta($guest_id, 'homey_guest_total_security_deposit', true);
        if(empty($security_deposit)) {
            return '0';
        }
        return $security_deposit;
    }
}

if(!function_exists('homey_adjust_guest_security_balance')) {
    function homey_adjust_guest_security_balance($guest_id, $payout_amount) {
        $current_security_deposit = homey_get_get_security_deposit($guest_id);
        $security_deposit = $current_security_deposit - $payout_amount;
        update_user_meta($guest_id, 'homey_guest_total_security_deposit', $security_deposit);
    }
}

if(!function_exists('homey_addto_guest_security_balance')) {
    function homey_addto_guest_security_balance($guest_id, $payout_amount) {
        $current_security_deposit = homey_get_get_security_deposit($guest_id);
        $security_deposit = $current_security_deposit + $payout_amount;
        update_user_meta($guest_id, 'homey_guest_total_security_deposit', $security_deposit);
    }
}


if(!function_exists('homey_get_earnings')) {
    function homey_get_earnings($limit = 5, $host_id = null) {
        global $wpdb, $current_user;
        $current_user = wp_get_current_user();
        $userID       = $current_user->ID;
        $local = homey_get_localization();
        $allowded_html = array();

        if(!empty($host_id)) {
            $host_id = $host_id;
        } else {
            $host_id = $userID;
        }

        $table_name = $wpdb->prefix . 'homey_earnings';

        $sql_query = $wpdb->prepare( 
            "
            SELECT * 
            FROM $table_name 
            WHERE user_id = %d ORDER BY id DESC 
            LIMIT %d
            ", 
            $host_id,
            $limit
        );

        $results = $wpdb->get_results($sql_query);

        if ( sizeof( $results ) != 0 ) {
            return $results;
        } else {
            return '';
        }
    }
}

if(!function_exists('homey_get_security_deposit')) {
    function homey_get_security_deposit($limit = 5, $guest_id = null) {
        global $wpdb, $current_user;
        $current_user = wp_get_current_user();
        $userID       = $current_user->ID;
        $local = homey_get_localization();
        $allowded_html = array();

        if(!empty($guest_id)) {
            $guest_id = $guest_id;
        } else {
            $guest_id = $userID;
        }

        $table_name = $wpdb->prefix . 'homey_earnings';

        $sql_query = $wpdb->prepare( 
            "
            SELECT * 
            FROM $table_name 
            WHERE guest_id = %d ORDER BY id DESC 
            LIMIT %d
            ", 
            $guest_id,
            $limit
        );

        $results = $wpdb->get_results($sql_query);

        if ( sizeof( $results ) != 0 ) {
            return $results;
        } else {
            return '';
        }
    }
}

if(!function_exists('homey_get_all_payouts')) {
    function homey_get_all_payouts($start_page, $perpage) {
        global $wpdb;
        $sub_query = '';
        $where = 'where ';
        $condition = '';

        $table_name = $wpdb->prefix . 'homey_payouts';

        if(isset($_GET['status']) && $_GET['status'] != '') {
            $status = $_GET['status'];
            $sub_query .= 'payout_status = '.$status;
            
        }
        if(isset($_GET['host'])  && $_GET['host'] != '') {
            $host = $_GET['host'];
            if(!empty($sub_query)) {
                $sub_query .= ' and ';
            }
            $sub_query .= ' user_id = '.$host;
            
        }

        if(isset($_GET['payout_id'])  && $_GET['payout_id'] != '') {
            $payout_id = $_GET['payout_id'];
            if(!empty($sub_query)) {
                $sub_query .= ' and ';
            }
            $sub_query .= ' payout_id = '.$payout_id;
            
        }

        if(!empty($sub_query)) {
            $sub_query = $where.$sub_query;
        }

        //$results = $wpdb->get_results('SELECT * FROM '.$table_name.' ORDER BY payout_id DESC LIMIT '.$start_page.', '.$perpage);
        $results = $wpdb->get_results('SELECT * FROM '.$table_name.' '.$sub_query.' ORDER BY payout_id DESC');

        if ( sizeof( $results ) != 0 ) {
            return $results;
        } else {
            return '';
        }
    }
}

if(!function_exists('homey_get_host_payouts')) {
    function homey_get_host_payouts($limit = 5, $host_id = null) {
        global $wpdb, $current_user;
        $current_user = wp_get_current_user();
        $userID       = $current_user->ID;

        if(empty($host_id)) {
            $host_id = $userID;
        }

        $table_name = $wpdb->prefix . 'homey_payouts';

        $results = $wpdb->get_results('SELECT * FROM '.$table_name.' WHERE user_id = '.$host_id.' ORDER BY payout_id DESC limit '.$limit);

        if ( sizeof( $results ) != 0 ) {
            return $results;
        } else {
            return '';
        }
    }
}

if(!function_exists('homey_signle_payout')) {
    function homey_signle_payout($id) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'homey_payouts';

        $sql_query = $wpdb->prepare( 
            "
            SELECT * 
            FROM $table_name 
            WHERE payout_id = %d
            ",
            $id
        );

        $results = $wpdb->get_row($sql_query);

        return $results;

    }
}

if(!function_exists('homey_signle_payout_access')) {
    function homey_signle_payout_access($id) {
        global $wpdb;
        $user_id = get_current_user_id();

        $table_name = $wpdb->prefix . 'homey_payouts';

        $sql_query = $wpdb->prepare( 
            "
            SELECT * 
            FROM $table_name 
            WHERE payout_id = %d AND user_id = %d
            ",
            $id,
            $user_id
        );

        $results = $wpdb->get_row($sql_query);

        return $results;

    }
}

if(!function_exists('homey_get_earning_detail')) {
    function homey_get_earning_detail($id) {
        global $wpdb, $current_user;
        $current_user = wp_get_current_user();
        $userID       = $current_user->ID;
        $local = homey_get_localization();
        $allowded_html = array();

        $table_name = $wpdb->prefix . 'homey_earnings';

        $sql_query = $wpdb->prepare( 
            "
            SELECT * 
            FROM $table_name 
            WHERE user_id = %d AND id = %d
            ", 
            $userID,
            $id
        );

        $results = $wpdb->get_row($sql_query);

        return $results;

    }
}

if(!function_exists('homey_get_earning_by_reservation_id')) {
    function homey_get_earning_by_reservation_id($reservation_id) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'homey_earnings';

        $sql_query = $wpdb->prepare( 
            "
            SELECT * 
            FROM $table_name 
            WHERE reservation_id = %d
            ", 
            $reservation_id
        );

        $results = $wpdb->get_row($sql_query);

        return $results;

    }
}

add_action( 'wp_ajax_homey_add_payout', 'homey_add_payout' );
if(!function_exists('homey_add_payout')) {
    function homey_add_payout() {
        global $wpdb, $current_user;
        $current_user = wp_get_current_user();
        $userID       = $current_user->ID;
        $local = homey_get_localization();
        $allowded_html = array();
        $payout_beneficiary = array();

        $minimum_payout_amount = homey_get_minimum_payout_amount();
        $homey_is_host_payout = homey_is_host_payout($userID);
        if(homey_is_host()){
            $available_balance = homey_get_host_available_earnings($userID);
        }else{
            $available_balance = homey_get_get_security_deposit($userID);
        }

        $payout_amount = sanitize_text_field($_POST['payout_amount']);
        $host = homey_get_author_by_id('36', '36', 'img-responsive img-circle', $userID);
        
        $payout_beneficiary['ben_first_name'] = $host['ben_first_name'];
        $payout_beneficiary['ben_last_name'] = $host['ben_last_name'];
        $payout_beneficiary['ben_company_name'] = $host['ben_company_name'];
        $payout_beneficiary['ben_tax_number'] = $host['ben_tax_number'];
        $payout_beneficiary['ben_street_address'] = $host['ben_street_address'];
        $payout_beneficiary['ben_apt_suit'] = $host['ben_apt_suit'];
        $payout_beneficiary['ben_city'] = $host['ben_city'];
        $payout_beneficiary['ben_state'] = $host['ben_state'];
        $payout_beneficiary['ben_zip_code'] = $host['ben_zip_code'];

        $payout_beneficiary = json_encode($payout_beneficiary);

        $payout_payment_method = get_user_meta($userID, 'payout_payment_method', true);
        $payout_paypal_email = get_user_meta($userID, 'payout_paypal_email', true);
        $payout_skrill_email = get_user_meta($userID, 'payout_skrill_email', true);

        if($payout_payment_method == 'paypal') {
            $payout_data = $payout_paypal_email;

        } elseif($payout_payment_method == 'skrill') {
            $payout_data = $payout_skrill_email;

        } elseif($payout_payment_method == 'wire') {
            $payout_data = array();
            $payout_data['bank_account'] = $host['bank_account'];
            $payout_data['swift'] = $host['swift'];
            $payout_data['bank_name'] = $host['bank_name'];
            $payout_data['wir_street_address'] = $host['wir_street_address'];
            $payout_data['wir_aptsuit'] = $host['wir_aptsuit'];
            $payout_data['wir_city'] = $host['wir_city'];
            $payout_data['wir_state'] = $host['wir_state'];
            $payout_data['wir_zip_code'] = $host['wir_zip_code'];

            $payout_data = json_encode($payout_data);
        }

        $verify_nonce = $_REQUEST['security'];
        if ( ! wp_verify_nonce( $verify_nonce, 'homey_payout_request_nonce' ) ) {
            echo json_encode( array( 'success' => false , 'msg' => 'Invalid request' ) );
            die;
        }

        if(empty($payout_amount)) {
            echo json_encode( 
                array( 
                    'success' => false,
                    'msg' => esc_html__('Please enter an amount greater then 0', 'homey')
                )
            );
            wp_die();
        }

        if(!is_numeric($payout_amount)) {
            echo json_encode( 
                array( 
                    'success' => false,
                    'msg' => esc_html__('Only numbers are allowed', 'homey')
                )
            );
            wp_die();
        }

        if(empty($homey_is_host_payout)) {
            echo json_encode( 
                array( 
                    'success' => false,
                    'msg' => esc_html__('Please setup your payment method', 'homey')
                )
            );
            wp_die();
        }

        if($payout_amount < $minimum_payout_amount) {
            echo json_encode( 
                array( 
                    'success' => false,
                    'msg' => esc_html__('Minimum payout amount is', 'homey').' '.$minimum_payout_amount
                )
            );
            wp_die();
        }

        if($payout_amount > $available_balance) {
            echo json_encode( 
                array( 
                    'success' => false,
                    'msg' => esc_html__('The requested amount is greater then available balance.', 'homey')
                )
            );
            wp_die();
        }

        $table_name = $wpdb->prefix . 'homey_payouts';
        $order_id = $wpdb->query( $wpdb->prepare(
            "INSERT INTO $table_name
            ( user_id, total_amount, payout_method, payout_method_data, payout_beneficiary )
            VALUES ( %d, %s, %s, %s, %s)", 
            $userID, 
            $payout_amount, 
            $payout_payment_method,
            $payout_data,
            $payout_beneficiary
        ) );

        if($order_id) {
            homey_adjust_host_available_balance($userID, $payout_amount);

            $admin_email = get_option('admin_email');
            $wallet_page_link = homey_get_template_link('template/dashboard-wallet.php');
            $payouts_detail_link = add_query_arg( 
                array(
                    'dpage' => 'payout-detail',
                    'payout_id' => $wpdb->insert_id,
                ), 
                $wallet_page_link 
            );
            
            $admin_orgs = array(
                'payout_amount' => homey_formatted_price($payout_amount, false),
                'payout_link' => $payouts_detail_link
            );
            homey_email_composer( $admin_email, 'admin_payout_request', $admin_orgs );

            $host_orgs = array(
                'payout_amount' => homey_formatted_price($payout_amount, false),
                'host_name' => $host['name']
            );
            homey_email_composer( $host['email'], 'payout_request', $host_orgs );
        }

        echo json_encode( 
            array( 
                'success' => true,
                'msg' => esc_html__('Request sent successfully.', 'homey')
            )
        );
        wp_die();
        

    }
}

add_action( 'wp_ajax_homey_make_host_adjustments', 'homey_make_host_adjustments' );
if( !function_exists('homey_make_host_adjustments') ) {
    function homey_make_host_adjustments() {
        global $wpdb;

        $nonce = $_POST['adjustment_security'];
        if (!wp_verify_nonce( $nonce, 'adjustment_security-nonce') ) {
            echo json_encode(array(
                'success' => false,
                'msg' => esc_html__('Invalid Nonce!', 'homey')
            ));
            wp_die();
        }

        $adj_title = sanitize_text_field( $_POST['adj_title'] );
        $adj_amount = sanitize_text_field( $_POST['adj_amount'] );
        $adj_action = sanitize_text_field( $_POST['adj_action'] );
        $adj_reason = sanitize_text_field( $_POST['adj_reason'] );
        $host_id = sanitize_text_field( $_POST['host_id'] );

        if(empty($host_id)) {
            echo json_encode(array(
                'success' => false,
                'msg' => esc_html__('Something went wrong', 'homey')
            ));
            wp_die();
        }

        if(empty($adj_title)) {
            echo json_encode(array(
                'success' => false,
                'msg' => esc_html__('Please enter the title', 'homey')
            ));
            wp_die();
        }

        if(empty($adj_amount)) {
            echo json_encode(array(
                'success' => false,
                'msg' => esc_html__('Please enter the amount', 'homey')
            ));
            wp_die();
        }

        if(empty($adj_action)) {
            echo json_encode(array(
                'success' => false,
                'msg' => esc_html__('Please choose an action', 'homey')
            ));
            wp_die();
        }

        if(empty($adj_reason)) {
            echo json_encode(array(
                'success' => false,
                'msg' => esc_html__('Please enter the reason', 'homey')
            ));
            wp_die();
        }

        $date_processed = date( 'Y-m-d G:i:s', current_time( 'timestamp', 0 ));
        $payout_status = 3;

        $table_name = $wpdb->prefix . 'homey_payouts';
        $order_id = $wpdb->query( $wpdb->prepare(
            "INSERT INTO $table_name
            ( user_id, total_amount, payout_status, note, date_processed, action )
            VALUES ( %d, %s, %d, %s, %s, %s)", 
            $host_id, 
            $adj_amount,
            $payout_status,
            $adj_reason,
            $date_processed,
            $adj_action
        ) );

        if($order_id) {

            $host_email = get_the_author_meta( 'email' , $host_id);
            $host_name = get_the_author_meta( 'display_name' , $host_id);

            if($adj_action == 'deduct_money') {
                homey_adjust_host_available_balance_2($host_id, $adj_amount);

                $host_orgs = array(
                    'amount' => homey_formatted_price_for_payout($adj_amount),
                    'username' => $host_name,
                    'reason' => $adj_reason,
                );
                homey_email_composer( $host_email, 'payment_deduct_money', $host_orgs );

            } elseif($adj_action == 'add_money') {
                homey_addto_host_available_balance_2($host_id, $adj_amount);

                $host_orgs = array(
                    'amount' => homey_formatted_price_for_payout($adj_amount),
                    'username' => $host_name,
                    'reason' => $adj_reason,
                );
                homey_email_composer( $host_email, 'payment_add_money', $host_orgs );
            }
        }

        echo json_encode( 
            array( 
                'success' => true,
                //'msg' => esc_html__('Request sent successfully.', 'homey')
            )
        );
        wp_die();

    }
}

add_action( 'wp_ajax_homey_make_guest_adjustments', 'homey_make_guest_adjustments' );
if( !function_exists('homey_make_guest_adjustments') ) {
    function homey_make_guest_adjustments() {
        global $wpdb;

        $nonce = $_POST['adjustment_security'];
        if (!wp_verify_nonce( $nonce, 'adjustment_security-nonce') ) {
            echo json_encode(array(
                'success' => false,
                'msg' => esc_html__('Invalid Nonce!', 'homey')
            ));
            wp_die();
        }

        $adj_title = sanitize_text_field( $_POST['adj_title'] );
        $adj_amount = sanitize_text_field( $_POST['adj_amount'] );
        $adj_action = sanitize_text_field( $_POST['adj_action'] );
        $adj_reason = sanitize_text_field( $_POST['adj_reason'] );
        $guest_id = sanitize_text_field( $_POST['guest_id'] );

        $adj_reason = stripslashes($adj_reason);

        if(empty($guest_id)) {
            echo json_encode(array(
                'success' => false,
                'msg' => esc_html__('Something went wrong', 'homey')
            ));
            wp_die();
        }

        if(empty($adj_title)) {
            echo json_encode(array(
                'success' => false,
                'msg' => esc_html__('Please enter the title', 'homey')
            ));
            wp_die();
        }

        if(empty($adj_amount)) {
            echo json_encode(array(
                'success' => false,
                'msg' => esc_html__('Please enter the amount', 'homey')
            ));
            wp_die();
        }

        if(empty($adj_action)) {
            echo json_encode(array(
                'success' => false,
                'msg' => esc_html__('Please choose an action', 'homey')
            ));
            wp_die();
        }

        if(empty($adj_reason)) {
            echo json_encode(array(
                'success' => false,
                'msg' => esc_html__('Please enter the reason', 'homey')
            ));
            wp_die();
        }

        $date_processed = date( 'Y-m-d G:i:s', current_time( 'timestamp', 0 ));
        $payout_status = 3;

        $table_name = $wpdb->prefix . 'homey_payouts';
        $order_id = $wpdb->query( $wpdb->prepare(
            "INSERT INTO $table_name
            ( user_id, total_amount, payout_status, note, date_processed, action )
            VALUES ( %d, %s, %d, %s, %s, %s)", 
            $guest_id, 
            $adj_amount,
            $payout_status,
            $adj_reason,
            $date_processed,
            $adj_action
        ) );

        if($order_id) {

            $guest_email = get_the_author_meta( 'email' , $guest_id);
            $guest_name = get_the_author_meta( 'display_name' , $guest_id);

            if($adj_action == 'deduct_money') {
                homey_adjust_guest_security_balance($guest_id, $adj_amount);

                $guest_orgs = array(
                    'amount' => homey_formatted_price_for_payout($adj_amount),
                    'username' => $guest_name,
                    'reason' => $adj_reason,
                );
                homey_email_composer( $guest_email, 'payment_deduct_money', $guest_orgs );

            } elseif($adj_action == 'add_money') {
                homey_addto_guest_security_balance($guest_id, $adj_amount);

                $guest_orgs = array(
                    'amount' => homey_formatted_price_for_payout($adj_amount),
                    'username' => $guest_name,
                    'reason' => $adj_reason,
                );
                homey_email_composer( $guest_email, 'payment_add_money', $guest_orgs );
            }


        }

        echo json_encode( 
            array( 
                'success' => true,
                //'msg' => esc_html__('Request sent successfully.', 'homey')
            )
        );
        wp_die();

    }
}

add_action( 'wp_ajax_homey_update_payout_status', 'homey_update_payout_status' );
if(!function_exists('homey_update_payout_status')) {
    function homey_update_payout_status() {
        global $wpdb;

        $payout_id = sanitize_text_field($_POST['payout_id']);
        $payout_status = sanitize_text_field($_POST['payout_status']);
        $transfer_fee = sanitize_text_field($_POST['transfer_fee']);
        $transfer_note = sanitize_text_field($_POST['transfer_note']);

        if(empty($transfer_fee)) {
            $transfer_fee = 0;
        }

        $verify_nonce = $_REQUEST['security'];
        if ( ! wp_verify_nonce( $verify_nonce, 'homey_payout_status_nonce' ) ) {
            echo json_encode( array( 'success' => false , 'msg' => 'Invalid request' ) );
            die;
        }

        if(!is_numeric($transfer_fee)) {
            echo json_encode( 
                array( 
                    'success' => false,
                    'msg' => esc_html__('Only numbers are allowed for the transfert fee', 'homey')
                )
            );
            wp_die();
        } 

        if(empty($payout_id)) {
            echo json_encode( 
                array( 
                    'success' => false,
                    'msg' => esc_html__('Something went wrong.', 'homey')
                )
            );
            wp_die();
        }

        if(empty($payout_status)) {
            echo json_encode( 
                array( 
                    'success' => false,
                    'msg' => esc_html__('Please select the payout status', 'homey')
                )
            );
            wp_die();
        }

        $table_name = $wpdb->prefix . 'homey_payouts';
        $order_id = $wpdb->query( $wpdb->prepare(
            "UPDATE $table_name set payout_status = %d, transfer_fee = %d, note = %s WHERE payout_id = %d", 
            $payout_status, 
            $transfer_fee, 
            $transfer_note, 
            $payout_id
        ) );

        if($order_id) {

            $payout = homey_get_payout_by_id_and_status($payout_id, $payout_status);
            $host_id = $payout->user_id;
            $host_email = get_the_author_meta( 'email' , $host_id);
            $host_name = get_the_author_meta( 'display_name' , $host_id);
            $total_amount = $payout->total_amount;


            if($payout_status == 4) { // Cancel
                homey_adjust_host_balance_after_cancelled($payout_id, $payout_status);

                $args = array(
                    'host_name' => $host_name,
                    'payout_amount' => homey_formatted_price_for_payout($total_amount)
                );
                homey_email_composer( $host_email, 'payout_request_cancelled', $args );

            } elseif($payout_status == 3) { // Completed
                if(homey_is_renter($payout->user_id)){
                    homey_adjust_guest_security_balance($payout->user_id, $payout->total_amount);
                }

                $args = array(
                    'host_name' => $host_name,
                    'payout_amount' => homey_formatted_price_for_payout($total_amount),
                    'transfer_fee' => homey_formatted_price_for_payout($transfer_fee)
                );
                homey_email_composer( $host_email, 'payout_request_completed', $args );
            }

            homey_update_payout_processed_date($payout_id);
        }
        
        echo json_encode( 
            array( 
                'success' => true,
                'msg' => ''
            )
        );
        wp_die();

    }
}

if(!function_exists('homey_update_payout_processed_date')) {
    function homey_update_payout_processed_date($payout_id) {
        global $wpdb;
        $datetime = date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ));
        $table_name = $wpdb->prefix . 'homey_payouts';
        $order_id = $wpdb->query( $wpdb->prepare(
            "UPDATE $table_name set date_processed = %s WHERE payout_id = %d", 
            $datetime, 
            $payout_id
        ) );
    }
}

if(!function_exists('homey_adjust_host_balance_after_cancelled')) {
    function homey_adjust_host_balance_after_cancelled($payout_id, $payout_status) {
        $payout = homey_get_payout_by_id_and_status($payout_id, $payout_status);

        $total_payout_amount = $payout->total_amount;

        $host_id = $payout->user_id;
        $current_available_earnings = homey_get_host_available_earnings($host_id);

        $available_earnings = $current_available_earnings + $total_payout_amount;
        update_user_meta($host_id, 'homey_host_available_earnings', $available_earnings);
    }
}

if(!function_exists('homey_get_payout_by_id_and_status')) {
    function homey_get_payout_by_id_and_status($payout_id, $payout_status) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'homey_payouts';

        $sql_query = $wpdb->prepare( 
            "
            SELECT * 
            FROM $table_name 
            WHERE payout_id = %d AND payout_status = %d
            ", 
            $payout_id,
            $payout_status
        );

        $results = $wpdb->get_row($sql_query);

        return $results;
    }
}




add_action( 'wp_ajax_homey_save_payout_method_info', 'homey_save_payout_method_info' );
if(!function_exists('homey_save_payout_method_info')) {
    function homey_save_payout_method_info() {
        global $wpdb, $current_user;
        $current_user = wp_get_current_user();
        $userID       = $current_user->ID;
        $local = homey_get_localization();
        $allowded_html = array();

        $verify_nonce = $_REQUEST['security'];
        if ( ! wp_verify_nonce( $verify_nonce, 'homey_payout_method_nonce' ) ) {
            echo json_encode( array( 'success' => false , 'msg' => 'Invalid request' ) );
            die;
        }

        $required = array();

        $payout_method = sanitize_text_field($_POST['payout_method']);
        $paypal_email = sanitize_text_field($_POST['paypal_email']);
        $skrill_email = sanitize_text_field($_POST['skrill_email']);

        // Beneficiary Information
        $ben_first_name = sanitize_text_field($_POST['ben_first_name']);
        $ben_last_name = sanitize_text_field($_POST['ben_last_name']);
        $ben_company_name = sanitize_text_field($_POST['ben_company_name']);
        $ben_tax_number = sanitize_text_field($_POST['ben_tax_number']);
        $ben_street_address = sanitize_text_field($_POST['ben_street_address']);
        $ben_apt_suit = sanitize_text_field($_POST['ben_apt_suit']);
        $ben_city = sanitize_text_field($_POST['ben_city']);
        $ben_state = sanitize_text_field($_POST['ben_state']);
        $ben_zip_code = sanitize_text_field($_POST['ben_zip_code']);

        if(empty($ben_first_name)) {
            $required[] = 'ben_first_name';
        }

        if(empty($ben_last_name)) {
            $required[] = 'ben_last_name';
        }

        if(empty($ben_street_address)) {
            $required[] = 'ben_street_address';
        }

        if(empty($ben_city)) {
            $required[] = 'ben_city';
        }

        if(empty($ben_state)) {
            $required[] = 'ben_state';
        }

        if(empty($ben_zip_code)) {
            $required[] = 'ben_zip_code';
        }

        //Wire Transfer Information
        if($payout_method == 'wire') {
            $bank_account = sanitize_text_field($_POST['bank_account']);
            $swift = sanitize_text_field($_POST['swift']);
            $bank_name = sanitize_text_field($_POST['bank_name']);
            $wir_street_address = sanitize_text_field($_POST['wir_street_address']);
            $wir_aptsuit = sanitize_text_field($_POST['wir_aptsuit']);
            $wir_city = sanitize_text_field($_POST['wir_city']);
            $wir_state = sanitize_text_field($_POST['wir_state']);
            $wir_zip_code = sanitize_text_field($_POST['wir_zip_code']);

            if(empty($bank_account)) {
                $required[] = 'bank_account';
            }

            if(empty($swift)) {
                $required[] = 'swift';
            }

            if(empty($bank_name)) {
                $required[] = 'bank_name';
            }

            if(empty($wir_street_address)) {
                $required[] = 'wir_street_address';
            }

            if(empty($wir_city)) {
                $required[] = 'wir_city';
            }

            if(empty($wir_state)) {
                $required[] = 'wir_state';
            }

            if(empty($wir_zip_code)) {
                $required[] = 'wir_zip_code';
            }
        }

        //PayPal
        if($payout_method == 'paypal') {
            if(empty($paypal_email)) {
                $required[] = 'paypal_email';
            }
        }

        //Skrill
        if($payout_method == 'skrill') {
            if(empty($skrill_email)) {
                $required[] = 'skrill_email';
            }
        }

        if(!empty($required)) {
            echo json_encode( 
                array( 
                    'success' => false, 
                    'message' => '',
                    'req' => $required 
                ) 
             );
             wp_die();
        } else {

            // Beneficiary Information
            update_user_meta( $userID, 'ben_first_name', $ben_first_name );
            update_user_meta( $userID, 'ben_last_name', $ben_last_name );
            update_user_meta( $userID, 'ben_street_address', $ben_street_address );
            update_user_meta( $userID, 'ben_city', $ben_city );
            update_user_meta( $userID, 'ben_state', $ben_state );
            update_user_meta( $userID, 'ben_zip_code', $ben_zip_code );

            if ( !empty( $ben_company_name ) ) {
                update_user_meta( $userID, 'ben_company_name', $ben_company_name );
            } else {
                delete_user_meta( $userID, 'ben_company_name' );
            }

            if ( !empty( $ben_tax_number ) ) {
                update_user_meta( $userID, 'ben_tax_number', $ben_tax_number );
            } else {
                delete_user_meta( $userID, 'ben_tax_number' );
            }

            if ( !empty( $ben_apt_suit ) ) {
                update_user_meta( $userID, 'ben_apt_suit', $ben_apt_suit );
            } else {
                delete_user_meta( $userID, 'ben_apt_suit' );
            }

            update_user_meta( $userID, 'payout_payment_method', $payout_method );

            //Wire Transfer Information
            if($payout_method == 'wire') {
                update_user_meta( $userID, 'bank_account', $bank_account );
                update_user_meta( $userID, 'swift', $swift );
                update_user_meta( $userID, 'bank_name', $bank_name );
                update_user_meta( $userID, 'wir_street_address', $wir_street_address );
                update_user_meta( $userID, 'wir_city', $wir_city );
                update_user_meta( $userID, 'wir_state', $wir_state );
                update_user_meta( $userID, 'wir_zip_code', $wir_zip_code );
                if ( !empty( $wir_aptsuit ) ) {
                    update_user_meta( $userID, 'wir_aptsuit', $wir_aptsuit );
                } else {
                    delete_user_meta( $userID, 'wir_aptsuit' );
                }
            } // $payout_method wire

            //PayPal
            if($payout_method == 'paypal') {
                update_user_meta( $userID, 'payout_paypal_email', $paypal_email );
            }

            //Skrill
            if($payout_method == 'skrill') {
                update_user_meta( $userID, 'payout_skrill_email', $skrill_email );
            }

            echo json_encode( 
                array( 
                    'success' => true, 
                    'message' => esc_html__('Information saved successfully.', 'homey'),
                    'req' => ''
                ) 
             );
             wp_die();

        }

    }
}

if(!function_exists('homey_get_payout_method')) {
    function homey_get_payout_method($payout_method) {
        if($payout_method == 'paypal') {
            $output = esc_html__('PayPal', 'homey');

        } elseif($payout_method == 'skrill') {
            $output = esc_html__('Skrill', 'homey');

        } elseif($payout_method == 'wire') {
            $output = esc_html__('Wire Transfer', 'homey');
        } else {
            $output = $payout_method;
        }
        return $output;
    }
}

if(!function_exists('homey_get_payout_status')) {
    function homey_get_payout_status($payout_status) {
        if($payout_status == 1) {
            $status = homey_option('payout_pending_label');
        } elseif($payout_status == 2) {
            $status = homey_option('payout_inprogress_label');
        } elseif($payout_status == 3) {
            $status = homey_option('payout_completed_label');
        } elseif($payout_status == 4) {
            $status = homey_option('payout_cancel_label');
        } else {
            $status = $payout_status;
        }
        return $status;
    }
}

if( !function_exists('homey_calculate_cost_for_wallet') ) {
    function homey_calculate_cost_for_wallet($reservation_id) {
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
            return homey_calculate_cost_for_wallet_weekly($reservation_id);
        } else if( $booking_type == 'per_month' ) {
            return homey_calculate_cost_for_wallet_monthly($reservation_id);
        } else if( $booking_type == 'per_day_date' ) {
            return homey_calculate_cost_for_wallet_day_date($reservation_id);
        } else {
            return homey_calculate_cost_for_wallet_nightly($reservation_id);
        }

    }
}

if( !function_exists('homey_calculate_cost_for_wallet_weekly') ) {
    function homey_calculate_cost_for_wallet_weekly($reservation_id) {
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

        $output = '';
            

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

        if(!empty($reservation_meta['city_fee']) && $reservation_meta['city_fee'] != 0) {
            $output .= '<li>'.$local['cs_city_fee'].' <span>'.$city_fee.'</span></li>';
        }

        if(!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<li>'.$local['cs_sec_deposit'].' <span>'.homey_formatted_price($security_deposit).'</span></li>';
        }
        
        if(!homey_is_host()) {
            if(!empty($services_fee) && $services_fee != 0 ) {
                $output .= '<li>'.$local['cs_services_fee'].' <span>'.homey_formatted_price($services_fee).'</span></li>';
            }
        }

        if(!empty($taxes) && $taxes != 0 ) {
            $output .= '<li>'.$local['cs_taxes'].' '.$taxes_percent.'% <span>'.homey_formatted_price($taxes).'</span></li>';
        }            

        return $output;
    } 
}

if( !function_exists('homey_calculate_cost_for_wallet_monthly') ) {
    function homey_calculate_cost_for_wallet_monthly($reservation_id) {
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
        
        $price_per_month = homey_formatted_price($reservation_meta['price_per_month'], true);
        $no_of_days = $reservation_meta['no_of_days'];
        $no_of_months = $reservation_meta['total_months_count'];

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

        $output = '';
            

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

        if(!empty($reservation_meta['city_fee']) && $reservation_meta['city_fee'] != 0) {
            $output .= '<li>'.$local['cs_city_fee'].' <span>'.$city_fee.'</span></li>';
        }

        if(!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<li>'.$local['cs_sec_deposit'].' <span>'.homey_formatted_price($security_deposit).'</span></li>';
        }
        
        if(!homey_is_host()) {
            if(!empty($services_fee) && $services_fee != 0 ) {
                $output .= '<li>'.$local['cs_services_fee'].' <span>'.homey_formatted_price($services_fee).'</span></li>';
            }
        }

        if(!empty($taxes) && $taxes != 0 ) {
            $output .= '<li>'.$local['cs_taxes'].' '.$taxes_percent.'% <span>'.homey_formatted_price($taxes).'</span></li>';
        }            

        return $output;
    } 
}

if( !function_exists('homey_calculate_cost_for_wallet_nightly') ) {
    function homey_calculate_cost_for_wallet_nightly($reservation_id) {
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

        $output = '';
            
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

        if(!empty($reservation_meta['city_fee']) && $reservation_meta['city_fee'] != 0) {
            $output .= '<li>'.$local['cs_city_fee'].' <span>'.$city_fee.'</span></li>';
        }

        if(!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<li>'.$local['cs_sec_deposit'].' <span>'.homey_formatted_price($security_deposit).'</span></li>';
        }
        
        if(!homey_is_host()) {
            if(!empty($services_fee) && $services_fee != 0 ) {
                $output .= '<li>'.$local['cs_services_fee'].' <span>'.homey_formatted_price($services_fee).'</span></li>';
            }
        }

        if(!empty($taxes) && $taxes != 0 ) {
            $output .= '<li>'.$local['cs_taxes'].' '.$taxes_percent.'% <span>'.homey_formatted_price($taxes).'</span></li>';
        }            

        return $output;
    } 
}

if( !function_exists('homey_calculate_cost_for_wallet_day_date') ) {
    function homey_calculate_cost_for_wallet_day_date($reservation_id) {
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
        
        $price_per_night = homey_formatted_price($reservation_meta['price_per_day_date'], true);
        $no_of_days = $reservation_meta['no_of_days'];

        $nights_total_price = homey_formatted_price($reservation_meta['days_total_price'], false);

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
            $night_label = homey_option('glc_day_dates_label');
        } else {
            $night_label = homey_option('glc_day_date_label');
        }

        if($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }

        $output = '';
            
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

        if(!empty($reservation_meta['city_fee']) && $reservation_meta['city_fee'] != 0) {
            $output .= '<li>'.$local['cs_city_fee'].' <span>'.$city_fee.'</span></li>';
        }

        if(!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<li>'.$local['cs_sec_deposit'].' <span>'.homey_formatted_price($security_deposit).'</span></li>';
        }
        
        if(!homey_is_host()) {
            if(!empty($services_fee) && $services_fee != 0 ) {
                $output .= '<li>'.$local['cs_services_fee'].' <span>'.homey_formatted_price($services_fee).'</span></li>';
            }
        }

        if(!empty($taxes) && $taxes != 0 ) {
            $output .= '<li>'.$local['cs_taxes'].' '.$taxes_percent.'% <span>'.homey_formatted_price($taxes).'</span></li>';
        }            

        return $output;
    } 
}

if( !function_exists('homey_calculate_cost_for_wallet_old') ) {
    function homey_calculate_cost_for_wallet_old($reservation_id) {
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

        $output = '';
            
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

        if(!empty($reservation_meta['city_fee']) && $reservation_meta['city_fee'] != 0) {
            $output .= '<li>'.$local['cs_city_fee'].' <span>'.$city_fee.'</span></li>';
        }

        if(!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<li>'.$local['cs_sec_deposit'].' <span>'.homey_formatted_price($security_deposit).'</span></li>';
        }
        
        if(!homey_is_host()) {
            if(!empty($services_fee) && $services_fee != 0 ) {
                $output .= '<li>'.$local['cs_services_fee'].' <span>'.homey_formatted_price($services_fee).'</span></li>';
            }
        }

        if(!empty($taxes) && $taxes != 0 ) {
            $output .= '<li>'.$local['cs_taxes'].' '.$taxes_percent.'% <span>'.homey_formatted_price($taxes).'</span></li>';
        }            

        return $output;
    } 
}

if( !function_exists('homey_calculate_hourly_cost_for_wallet') ) {
    function homey_calculate_hourly_cost_for_wallet($reservation_id) {
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
        $check_in_hour = wp_kses ( $reservation_meta['check_in_hour'], $allowded_html );
        $check_out_hour = wp_kses ( $reservation_meta['check_out_hour'], $allowded_html );
        $guests         = intval($reservation_meta['guests']);
    
        
        $price_per_hour = homey_formatted_price($reservation_meta['price_per_hour'], true);
        $no_of_hours = $reservation_meta['no_of_hours'];

        $hours_total_price = homey_formatted_price($reservation_meta['hours_total_price'], false);

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

        if($no_of_hours > 1) {
            $hour_label = $local['hours_label'];
        } else {
            $hour_label = $local['hour_label'];
        }

        if($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }

        

        $output = '';
            
        if($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) { 
            $output .= '<li>'.$no_of_hours.' '.$hour_label.' ('.$local['with_custom_period_and_weekend_label'].') <span>'.$hours_total_price.'</span></li>';
            
        } elseif($booking_has_weekend == 1) {
            $output .= '<li>'.esc_attr($price_per_hour).' x '.$no_of_hours.' '.$hour_label.' ('.$with_weekend_label.') <span>'.$hours_total_price.'</span></li>';

        } elseif($booking_has_custom_pricing == 1) { 
            $output .= '<li>'.$no_of_hours.' '.$hour_label.' ('.$local['with_custom_period_label'].') <span>'.$hours_total_price.'</span></li>';

        } else {
            $output .= '<li>'.$price_per_hour.' x '.$no_of_hours.' '.$hour_label.' <span>'.$hours_total_price.'</span></li>';
        }

        if(!empty($additional_guests)) {
            $output .= '<li>'.$additional_guests.' '.$add_guest_label.' <span>'.homey_formatted_price($additional_guests_total_price).'</span></li>';
        }
        
        if(!empty($reservation_meta['cleaning_fee']) && $reservation_meta['cleaning_fee'] != 0) {
            $output .= '<li>'.$local['cs_cleaning_fee'].' <span>'.$cleaning_fee.'</span></li>';
        }

        if(!empty($reservation_meta['city_fee']) && $reservation_meta['city_fee'] != 0) {
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

        return $output;
    } 
}
?>
