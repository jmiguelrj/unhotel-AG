<?php
/**
 * Template Name: Stripe Webhook
 * Created by PhpStorm.
 * User: waqasriaz
 * Date: 27/06/16
 * Time: 5:18 AM
 */

require_once( HOMEY_PLUGIN_PATH . '/includes/stripe-php/init.php' );

$allowed_html = array();

$current_user = wp_get_current_user();
$userID       =   $current_user->ID;
$user_email   =   $current_user->user_email;
$admin_email  =  get_bloginfo('admin_email');
$username     =   $current_user->user_login;
$submission_currency = homey_option('payment_currency');
$paymentMethod = 'Stripe';

$date = date( 'Y-m-d G:i:s', current_time( 'timestamp', 0 ));

$stripe_secret_key = homey_option('stripe_secret_key');

// Set your secret key: remember to change this to your live secret key in production
// See your keys here: https://dashboard.stripe.com/account/apikeys
\Stripe\Stripe::setApiKey($stripe_secret_key);

// If you are testing your webhook locally with the Stripe CLI you
// can find the endpoint's secret by running `stripe listen`
// Otherwise, find your endpoint's secret in your webhook settings in the Developer Dashboard
$endpoint_secret = homey_option('stripe_webhook_secret');

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
$event = null;

try {
    $event = \Stripe\Webhook::constructEvent(
        $payload, $sig_header, $endpoint_secret
    );
} catch(\UnexpectedValueException $e) {
    // Invalid payload
    http_response_code(400);
    exit();
} catch(\Stripe\Exception\SignatureVerificationException $e) {
    // Invalid signature
    http_response_code(400);
    exit();
}

// Handle the event
switch ($event->type) {
    case 'payment_intent.succeeded':
        //file_put_contents('./log_stripe_'.date("j.n.Y").'.txt', ' event type '.$event->type, FILE_APPEND);

        $intent = $event->data->object;

        $homey_payment_type = $event->data->object->charges->data[0]->metadata->payment_type; 
        
        if ( isset ($homey_payment_type) && $homey_payment_type == 'reservation_fee'  ) {

            $userID          = intval($event->data->object->charges->data[0]->metadata->userID); 
            $reservation_id  = intval($event->data->object->charges->data[0]->metadata->reservation_id_for_stripe);
            $pay_ammout      = intval($intent->amount);

            $is_extra_options  = intval($event->data->object->charges->data[0]->metadata->extra_options);

            $is_instance_booking = intval($event->data->object->charges->data[0]->metadata->is_instance_booking);
            $is_instance_booking = isset($is_instance_booking) ? $is_instance_booking : 0;

            $is_hourly = intval($event->data->object->charges->data[0]->metadata->is_hourly);
            $is_hourly = isset($is_hourly) ? $is_hourly : 0;

            $stripe_des = esc_html__( 'Reservation ID','homey').' '.$reservation_id;
            if($is_instance_booking == 1) {

                $listing_id = intval($event->data->object->charges->data[0]->metadata->listing_id);
                $stripe_des = esc_html__( 'Listing ID','homey').' '.$listing_id;
            }

            if( $is_instance_booking == 0 ) { 
                $listing_id = get_post_meta($reservation_id, 'reservation_listing_id', true );

                
                if($is_hourly == 1) {
                    //Book hours
                    $booked_days_array = homey_make_hours_booked($listing_id, $reservation_id);
                    update_post_meta($listing_id, 'reservation_booked_hours', $booked_days_array);

                    //Remove Pending Hours
                    $pending_dates_array = homey_remove_booking_pending_hours($listing_id, $reservation_id);
                    update_post_meta($listing_id, 'reservation_pending_hours', $pending_dates_array);
                    
                } else {
                    //Book dates
                    $booked_days_array = homey_make_days_booked($listing_id, $reservation_id);
                    update_post_meta($listing_id, 'reservation_dates', $booked_days_array);

                    //Remove Pending Dates
                    $pending_dates_array = homey_remove_booking_pending_days($listing_id, $reservation_id);
                    update_post_meta($listing_id, 'reservation_pending_dates', $pending_dates_array);
                }
                
                // Update reservation status
                update_post_meta( $reservation_id, 'reservation_status', 'booked' );

            } elseif( $is_instance_booking == 1 ) {
                $listing_id = intval($event->data->object->charges->data[0]->metadata->listing_id);

                $guests = $event->data->object->charges->data[0]->metadata->guests;
                $guests = isset($guests) ? $guests : '';

                $renter_message = $event->data->object->charges->data[0]->metadata->guest_message;
                $renter_user_id = $event->data->object->charges->data[0]->metadata->userID;
                $renter_message = isset($renter_message) ? $renter_message : '';

                $check_in_date = $event->data->object->charges->data[0]->metadata->check_in_date;
                $check_in_date = isset($check_in_date) ? $check_in_date : '';

                $extra_options_data = '';
                if($is_extra_options == 1) {
                    $extra_options_data = get_user_meta($userID, 'extra_prices', true);
                }

                if($is_hourly == 1) {

                    $check_in_hour = $event->data->object->charges->data[0]->metadata->check_in_hour;
                    $check_in_hour = isset($check_in_hour) ? $check_in_hour : '';

                    $check_out_hour = $event->data->object->charges->data[0]->metadata->check_out_hour;
                    $check_out_hour = isset($check_out_hour) ? $check_out_hour : '';

                    $start_hour = $event->data->object->charges->data[0]->metadata->start_hour;
                    $start_hour = isset($start_hour) ? $start_hour : '';

                    $end_hour = $event->data->object->charges->data[0]->metadata->end_hour;
                    $end_hour = isset($end_hour) ? $end_hour : '';

                    $reservation_id = homey_add_hourly_instance_booking($listing_id, $check_in_date, $check_in_hour, $check_out_hour, $start_hour, $end_hour, $guests, $renter_message, $extra_options_data, $renter_user_id);

                } else {
                    
                    $check_out_date = $event->data->object->charges->data[0]->metadata->check_out_date;
                    $check_out_date = isset($check_out_date) ? $check_out_date : '';
                    $reservation_id = homey_add_instance_booking($listing_id, $check_in_date, $check_out_date, $guests, $renter_message, $extra_options_data, $renter_user_id);
                }

            }

            

            //Add host earning history
            homey_add_earning($reservation_id);

            //Add invoice
            $invoiceID = homey_generate_invoice( 'reservation','one_time', $reservation_id, $date, $userID, 0, 0, '', $paymentMethod );
            
            update_post_meta( $invoiceID, 'invoice_payment_status', 1 );

            // Emails
            $listing_owner = get_post_meta($reservation_id, 'listing_owner', true);
            $listing_renter = get_post_meta($reservation_id, 'listing_renter', true);

            $renter = homey_usermeta($listing_renter);
            $renter_email = $renter['email'];

            $owner = homey_usermeta($listing_owner);
            $owner_email = $owner['email'];

            $email_args = array('reservation_detail_url' => reservation_detail_link($reservation_id) );
            homey_email_composer( $renter_email, 'booked_reservation', $email_args );
            homey_email_composer( $owner_email, 'admin_booked_reservation', $email_args );
        
            
        //Featured listing fee
        } elseif( isset ($homey_payment_type) && $homey_payment_type == 'featured_fee' ) { 

            $userID      = intval($event->data->object->charges->data[0]->metadata->userID); 
            $listing_id = intval($event->data->object->charges->data[0]->metadata->listing_id);
            $pay_ammout  = intval($intent->amount); 

            update_post_meta( $listing_id, 'homey_featured', 1 );
            update_post_meta( $listing_id, 'homey_featured_datetime', $date );
            $invoiceID = homey_generate_invoice( 'upgrade_featured','one_time', $listing_id, $date, $userID, 0, 0, '', $paymentMethod );
            update_post_meta( $invoiceID, 'invoice_payment_status', 1 );

            $args = array(
                'listing_title'  =>  get_the_title($listing_id),
                'listing_id'     =>  $listing_id,
                'invoice_no' =>  $invoiceID,
            );
            /*
             * Send email
             * */

            homey_email_composer( $user_email, 'featured_submission_listing', $args );
            homey_email_composer( $admin_email, 'admin_featured_submission_listing', $args );

        }
    

        break;

    case 'payment_method.attached':
        $paymentMethod = $event->data->object; // contains a StripePaymentMethod
        handlePaymentMethodAttached($paymentMethod);
        break;
    // ... handle other event types
    default:
        // Unexpected event type
        http_response_code(400);
        exit();
}

http_response_code(200);
