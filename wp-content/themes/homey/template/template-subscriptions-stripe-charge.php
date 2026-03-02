<?php
/**
 * Template Name1: Subscription Plans Stripe Webhook
 * Created by PhpStorm.
 * User: Zahid Khurshid
 * Date: 14/07/2020
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
$endpoint_secret = get_option('hm_memberships_options');
$endpoint_secret = $endpoint_secret['webhook'];

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

$intent = $event->data;
  http_response_code(200);
// Handle the event
switch ($event->type) {

    case 'customer.subscription.deleted':
      $homey_payment_type = $event->data->object->metadata->payment_type;
      $userID           = intval($event->data->object->metadata->userID);
      $subscriptionID   = $event->data->object->id;
      $eventID          = $event->id;
      $subscriptionInfo = hm_subscription_detail($subscriptionID);

     // file_put_contents('./log_stripe_'.date("j.n.Y").'.txt', ' p title '.$subscriptionInfo->post_title.' <> '.$userID.' < uId > paymenTyp > '.$homey_payment_type.' '.$event, FILE_APPEND);
      
            if ( isset ($homey_payment_type) && $homey_payment_type == 'subscription_fee'  ) {
                update_post_meta($subscriptionInfo->ID, 'hm_subscription_detail_status', 'expired');

                //Create messages thread
                //do_action('homey_create_messages_thread', 'yeh wo', $reservation_id);
            }

             // Emails

            $admin_email = get_option('admin_email');

            $owner = homey_usermeta($userID);
            $owner_email = $owner['email'];
            $memberships_url = homey_get_template_link('template/template-membership-webhook.php');

            $message = 'Your Homey Membership Plan is updated and you can visit this linke '.$memberships_url.' for more details.';
            homey_send_emails( $admin_email, $owner_email.' Homey Membership Plan is updated.',  $message);
            homey_send_emails( $owner_email, 'Your Homey Membership Plan is updated.',  $message);

        break;

    case 'customer.subscription.updated':
      $homey_payment_type = $event->data->object->metadata->payment_type;
      $userID           = intval($event->data->object->metadata->userID);
      $subscriptionID   = $event->data->object->items->data[0]->subscription;
      $eventID          = $event->id;
      $subscriptionInfo = homey_get_membership_detail($subscriptionID);

//      file_put_contents('./log_stripe_'.date("j.n.Y").'.txt', $log, FILE_APPEND);
      
        add_post_meta($subscriptionInfo->post_id, 'hm_subscription_detail_status', 'expired');
        break;
    // ... handle other event types
    default:
        // Unexpected event type
        http_response_code(400);
        exit();
}

http_response_code(200);
