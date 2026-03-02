<?php
/**
 * Template Name: Homey Subscriptions Payment Complete
 */
get_header();
global $homey_local, $homey_prefix, $wpdb;


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
if(isset($_SERVER['HTTP_STRIPE_SIGNATURE'])){


}
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

if(isset($event->type)){
    // Handle the event
    switch ($event->type) {
        case 'customer.subscription.updated':

            $intent = $event->data->object;

            file_put_contents('log_stripe_mem_' . date("j.n.Y") . '.log', $intent, FILE_APPEND);

            break;
        // ... handle other event types
        default:
            // Unexpected event type
            http_response_code(400);
            exit();
    }
//end of webhook events
}else{// after redirect

    $all_vairables = '';
    $counter = 0;
    $postID = -1;
    $memberships = array();
    $order_number = '';

    $args = array(
        'post_type' => 'hm_homey_memberships',
        'post_status' => 'publish',
        'order' => 'ASC',
        'orderby' => 'post__in'
    );

//get which payment method is used
    $paymentMethod = isset($_REQUEST['payment_gateway']) ? $_REQUEST['payment_gateway'] : 'stripe';

    if (isset($_REQUEST['is_homey_membership']) && $paymentMethod == 'stripe') {
        if (isset($_REQUEST['session_id'])) {
            //get session information from stripe
            $stripeSessionInfo = \Stripe\Checkout\Session::retrieve($_REQUEST['session_id']);

            //check if this session ID is already used and get $order_number
            $isAlreadySubscribed = homey_is_stripe_id_used($_REQUEST['session_id']);

            if ($isAlreadySubscribed > -1) {
                //get order number from homey db
                $order_number = get_post_meta($isAlreadySubscribed, 'hm_subscription_detail_order_number', true);
            }

            //get customer information
            $stripeCustomerInfo = \Stripe\Customer::retrieve($stripeSessionInfo['customer']);

            //get if valid or set to zero if invalid $stripePlanId
            $stripePlanId = isset($stripeSessionInfo['display_items'][0]['plan']['id']) ? $stripeSessionInfo['display_items'][0]['plan']['id'] : 0;

            // get membership information using $stripePlanId from homey DB
            $membershipInfo = homey_get_membership_detail($stripePlanId);
            $postID = isset($membershipInfo->ID) ? $membershipInfo->ID : -1;

            if (isset($stripeSessionInfo['customer']) && $isAlreadySubscribed < 1) {
                if ($postID != -1) {

                    //get stripe plan information using plan id
                    $stripePlanInfo = \Stripe\Plan::retrieve($stripePlanId);

                    //get $tax_id_stripe from homey DB using $postID
                    $tax_id_stripe = get_post_meta($postID, 'hm_settings_tax_id_stripe', true);

                    //get stripe subscription information using subscription ID from stripe
                    $stripeSubscriptionInfo = \Stripe\Subscription::retrieve($stripeSessionInfo['subscription']);

                    $stripeSubscription_ID = $purchase_date = $expiry_date = $stripeInvoiceInfo = $stripeInvoiceNumber = $latest_invoice = '';
                    if (isset($stripeSubscriptionInfo['id'])) {
                        $stripeSubscription_ID = $stripeSubscriptionInfo['id'];
                        $purchase_date = $stripeSubscriptionInfo['current_period_start'];
                        $expiry_date = $stripeSubscriptionInfo['current_period_end'];
                        $latest_invoice = $stripeSubscriptionInfo['latest_invoice'];
                    }

                    if (!empty($latest_invoice)) {
                        //get invoice number by retrieving from stripe using latest_invoice
                        $stripeInvoiceInfo = \Stripe\Invoice::retrieve($latest_invoice);
                        $stripeInvoiceNumber = $order_number = $stripeInvoiceInfo['number'];
                    }

                    //if applicable any tax then update the subscriptions or leave as it is
                    if (!empty($tax_id_stripe)) {
                        \Stripe\Subscription::update(
                            $stripeSessionInfo['subscription'],
                            [
                                'default_tax_rates' => [
                                    $tax_id_stripe,
                                ],
                            ]
                        );
                    }

                    //get total number of allowed listings for selected plan
                    $totalAllowedListings = get_post_meta($postID, 'hm_settings_listings_included', true);

                    //save subscription to homey DB
                    $subscriptionInfo = array(
                        'ID' => '',
                        'post_title' => $stripeCustomerInfo['email'],
                        'post_content' => '',
                        'post_status' => 'publish',
                        'post_author' => get_current_user_id(),
                        'post_type' => "hm_subscriptions"
                    );

                    //inserting post wp_insert_post() will return ID of inserted post
                    $subscription_ID = wp_insert_post($subscriptionInfo);

                    // update post meta for saved subscription in homey DB
                    add_post_meta($subscription_ID, 'hm_subscription_detail_status', 'active');
                    add_post_meta($subscription_ID, 'hm_subscription_detail_payment_gateway', 'stripe');
                    add_post_meta($subscription_ID, 'hm_subscription_detail_order_number', $stripeInvoiceNumber);
                    add_post_meta($subscription_ID, 'hm_subscription_detail_session_id', $_REQUEST['session_id']);
                    add_post_meta($subscription_ID, 'hm_subscription_detail_plan_id', $postID . '-' . $membershipInfo->post_title);
                    add_post_meta($subscription_ID, 'hm_subscription_detail_sub_id', $stripeSubscription_ID);
                    add_post_meta($subscription_ID, 'hm_subscription_detail_total_listings', $totalAllowedListings);
                    add_post_meta($subscription_ID, 'hm_subscription_detail_remaining_listings', $totalAllowedListings);
                    add_post_meta($subscription_ID, 'hm_subscription_detail_purchase_date', date('d/M/Y h:i:s', $purchase_date));
                    add_post_meta($subscription_ID, 'hm_subscription_detail_expiry_date', date('d/M/Y h:i:s', $expiry_date));
                    //end of save subscription
                }
            }
        }

    } else
        if (isset($_REQUEST['is_homey_membership']) && $paymentMethod == 'paypal') {
            //get file content, always use this method for security reasons
            $raw_post_data = file_get_contents('php://input');

            //prepare post data array
            $raw_post_array = explode('&', $raw_post_data);

            $myPost = array();
            foreach ($raw_post_array as $keyval) {
                $keyval = explode('=', $keyval);
                if (count($keyval) == 2)
                    $myPost[$keyval[0]] = urldecode($keyval[1]);
            }

            // Read the post from PayPal system and add 'cmd'
            $req = 'cmd=_notify-validate';
            if (function_exists('get_magic_quotes_gpc')) {
                $get_magic_quotes_exists = true;
            }

            foreach ($myPost as $key => $value) {
                if ($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
                    $value = urlencode(stripslashes($value));
                } else {
                    $value = urlencode($value);
                }
                $req .= "&$key=$value";
            }

            /*
             * Post IPN data back to PayPal to validate the IPN data is genuine
             * Without this step anyone can fake IPN data
             */
            $paypalURL = PAYPAL_URL;
            $ch = curl_init($paypalURL);
            if ($ch == FALSE) {
                return FALSE;
            }

            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
            curl_setopt($ch, CURLOPT_SSLVERSION, 6);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);

            // Set TCP timeout to 30 seconds
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close', 'User-Agent: company-name'));
            $res = curl_exec($ch);

            /*
             * Inspect IPN validation result and act accordingly
             * Split response headers and payload, a better way for strcmp
             */
            $tokens = explode("\r\n\r\n", trim($res));
            $res = trim(end($tokens));

            if (strcmp($res, "VERIFIED") == 0 || strcasecmp($res, "VERIFIED") == 0) {
                // Retrieve transaction data from PayPal
                $paypalInfo = $_POST;
                $subscr_id = $paypalInfo['subscr_id'];
                $payer_email = $paypalInfo['payer_email'];
                $item_name = $paypalInfo['item_name'];
                $itemPrice = $paypalInfo['item_price'];
                $stripePlanId = $item_number = $paypalInfo['item_number'];
                $txn_id = !empty($paypalInfo['txn_id']) ? $paypalInfo['txn_id'] : '';
                $payment_gross = !empty($paypalInfo['mc_gross']) ? $paypalInfo['mc_gross'] : 0;
                $currency_code = $paypalInfo['mc_currency'];
                $subscr_period = !empty($paypalInfo['period3']) ? $paypalInfo['period3'] : floor($payment_gross / $itemPrice);
                $payment_status = !empty($paypalInfo['payment_status']) ? $paypalInfo['payment_status'] : '';
                $custom = $paypalInfo['custom'];
                $subscr_date = !empty($paypalInfo['subscr_date']) ? $paypalInfo['subscr_date'] : date("Y-m-d H:i:s");
                $dt = new DateTime($subscr_date);
                $subscr_date = $dt->format("Y-m-d H:i:s");
                $subscr_date_valid_to = date("Y-m-d H:i:s", strtotime(" + $subscr_period month", strtotime($subscr_date)));

                if (!empty($txn_id)) {

                    // Check if transaction data exists with the same TXN ID
                    $isAlreadySubscribed = homey_is_stripe_id_used($txn_id);

                    $order_number = '';

                    if ($isAlreadySubscribed > -1) {

                        //get already used $order_number
                        $order_number = get_post_meta($isAlreadySubscribed, 'hm_subscription_detail_order_number', true);

                    } else {

                        //get membership information from homey DB
                        $membershipInfo = homey_get_membership_detail($stripePlanId);

                        $postID = isset($membershipInfo->ID) ? $membershipInfo->ID : -1;

                        //get total number of allowed listings
                        $totalAllowedListings = get_post_meta($postID, 'hm_settings_listings_included', true);

                        $purchase_date = $subscr_date;
                        $expiry_date = $subscr_date_valid_to;

                        //save subscription for homey DB
                        $subscriptionInfo = array(
                            'ID' => '',
                            'post_title' => $payer_email,
                            'post_content' => '',
                            'post_status' => 'publish',
                            'post_author' => get_current_user_id(),
                            'post_type' => "hm_subscriptions"
                        );

                        //inserting post wp_insert_post() will return ID of inserted post
                        $subscription_ID = wp_insert_post($subscriptionInfo);

                        //generate stripe invoice number
                        $stripeInvoiceNumber = rand(111111111, 999999999);

                        if (empty($order_number)) {
                            $order_number = $stripeInvoiceNumber;
                        }

                        add_post_meta($subscription_ID, 'hm_subscription_is_expired', 0);
                        add_post_meta($subscription_ID, 'hm_subscription_detail_payment_gateway', 'paypal');
                        add_post_meta($subscription_ID, 'hm_subscription_detail_order_number', $stripeInvoiceNumber);
                        add_post_meta($subscription_ID, 'hm_subscription_detail_session_id', $txn_id);
                        add_post_meta($subscription_ID, 'hm_subscription_detail_plan_id', $postID . '-' . $membershipInfo->post_title);
                        add_post_meta($subscription_ID, 'hm_subscription_detail_total_listings', $totalAllowedListings);
                        add_post_meta($subscription_ID, 'hm_subscription_detail_remaining_listings', $totalAllowedListings);
                        add_post_meta($subscription_ID, 'hm_subscription_detail_purchase_date', date('d/M/Y h:i:s', $purchase_date));
                        add_post_meta($subscription_ID, 'hm_subscription_detail_expiry_date', date('d/M/Y h:i:s', $expiry_date));
                        //end of save subscription
                    }
                }
            }
        }
}



if ($postID > -1) {
    $billing_period = get_post_meta($postID, 'hm_settings_bill_period', true);
    $billing_frequency = get_post_meta($postID, 'hm_settings_billing_frequency', true);
    $listings_included = get_post_meta($postID, 'hm_settings_listings_included', true);
    $unlimited_listings = get_post_meta($postID, 'hm_settings_unlimited_listings', true);
    $featured_listings = get_post_meta($postID, 'hm_settings_featured_listings', true);
    $stripe_package_id = get_post_meta($postID, 'hm_settings_stripe_package_id', true);
    $visibility = get_post_meta($postID, 'hm_settings_visibility', true);
    $images_per_listing = get_post_meta($postID, 'hm_settings_images_per_listing', true);
    $unlimited_images = get_post_meta($postID, 'hm_settings_unlimited_images', true);
//    $taxes = get_post_meta( $postID, 'hm_settings_taxes', true );
    $popular_featured = get_post_meta($postID, 'hm_settings_popular_featured', true);
    $custom_link = get_post_meta($postID, 'hm_settings_custom_link', true);
    $package_total_price = $package_price = get_post_meta($postID, 'hm_settings_package_price', true);
}
?>

    <section class="main-content-area">
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <div class="page-title">
                        <div class="block-top-title">
                            <?php get_template_part('template-parts/breadcrumb'); ?>
                            <h2><?php echo esc_html__(the_title('', '', false), 'homey'); ?></h2>
                        </div><!-- block-top-title -->
                    </div><!-- page-title -->
                </div><!-- col-xs-12 col-sm-12 col-md-12 col-lg-12 -->
            </div><!-- .row -->
        </div><!-- .container -->

        <div class="container">
            <?php
            if (isset($_REQUEST['limit-exceeded'])) { ?>
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8 col-md-offset-2 col-lg-offset-2">
                        <h3 class="error"><?php echo esc_html__("You have to subscribe from following plans to 'Add New Listings'.", 'homey')?></h3>
                    </div>
                </div>
            <?php } ?>
            <?php
            if (isset($_REQUEST['success'])):
                ?>
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8 col-md-offset-2 col-lg-offset-2">
                        <div class="membership-package-wrap">
                            <div class="block">
                                <div class="block-title">
                                    <div class="block-left">
                                        <h2 class="title">Thank you for your payment!</h2>
                                    </div><!-- block-left -->
                                </div>
                                <div class="block-body">
                                    The order <strong>#<?php echo $order_number; ?></strong> has been completed (Payment
                                    method: <?php echo $paymentMethod; ?>) and a confirmation email has been sent to
                                    <strong><?php echo isset($stripeCustomerInfo['email']) ? $stripeCustomerInfo['email'] : '-'; ?></strong>
                                </div><!-- block-body -->
                            </div><!-- block -->
                            <div class="block">
                                <div class="block-title">
                                    <div class="block-left">
                                        <h2 class="title">Order Summary</h2>
                                    </div><!-- block-left -->
                                </div>
                                <div class="block-body">
                                    <ul class="list-unstyled mebership-list-info">
                                        <li><i class="fa fa-check" aria-hidden="true"></i> Package
                                            <strong><?php echo isset($membershipInfo->post_title) ? $membershipInfo->post_title : '-'; ?></strong>
                                        </li>
                                        <li><i class="fa fa-check" aria-hidden="true"></i> Price
                                            <strong>$<?php echo $package_price; ?></strong></li>
                                        <li><i class="fa fa-check" aria-hidden="true"></i> Time Period:
                                            <strong><?php echo $billing_frequency . ' ' . $billing_period; ?></strong>
                                        </li>
                                        <li><i class="fa fa-check" aria-hidden="true"></i> Listings:
                                            <strong><?php echo $listings_included; ?></strong></li>
                                        <li><i class="fa fa-check" aria-hidden="true"></i> Featured Listings:
                                            <strong><?php echo $featured_listings; ?></strong></li>
                                        <!--                                    <li><i class="fa fa-check" aria-hidden="true"></i> Taxes <strong>-->
                                        <?php //echo $taxes;
                                        ?><!--%</strong></li>-->
                                        <li class="total-price">Total Price
                                            <strong>$<?php echo $package_total_price; ?></strong></li>
                                    </ul>
                                </div><!-- block-body -->
                            </div><!-- block -->
                        </div><!-- membership-package-wrap -->
                        <div class="membership-nav-wrap">
                            <button class="btn btn-primary btn-block" onclick="window.location.href='#'">Create a
                                Listing
                            </button>
                        </div>
                    </div><!-- col-xs-12 col-sm-12 col-md-8 col-lg-8 -->
                </div><!-- .row -->
            <?php
            elseif (isset($_REQUEST['cancel'])):
                ?>
                <h1><?php echo __("Your payment wasn't successful give it another try."); ?> </h1>

            <?php
            else:
                ?><!--cancel message html-->
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="membership-package-wrap">
                            <div class="row no-margin">
                                <?php
                                //do the query
                                $the_query = new WP_Query($args);
                                if ($the_query->have_posts()):
                                    while ($the_query->have_posts()):
                                        $the_query->the_post();

                                        $is_featured = get_post_meta(get_the_ID(), 'hm_settings_popular_featured', true);
                                        $is_visible = get_post_meta(get_the_ID(), 'hm_settings_visibility', true);
                                        if ($is_visible == 'yes') {


                                            ?>
                                            <div class="col-sm-3">
                                                <?php $price_table_name = ($is_featured == 'yes') ? 'featured-price-table' : 'price-table' ?>
                                                <?php get_template_part("template-parts/memberships/$price_table_name"); ?>
                                            </div>
                                        <?php }
                                    endwhile;
                                endif;
                                ?>
                            </div>
                        </div><!-- membership-package-wrap -->
                    </div><!-- col-xs-12 col-sm-12 col-md-12 col-lg-12 -->
                </div><!-- .row -->
            <?php endif; ?>
        </div>   <!-- .container -->

    </section><!-- main-content-area listing-page grid-listing-page -->
<?php get_footer(); ?>
