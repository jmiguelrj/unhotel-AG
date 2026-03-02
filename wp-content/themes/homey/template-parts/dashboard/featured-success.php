<?php
/**
 * Template Name: Dashboard Reservations
 */
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( !is_user_logged_in() ) {
    wp_redirect( home_url('/') );
}
global $homey_local, $current_user;
$paymentMethod = '';
wp_get_current_user();
$userID = $current_user->ID;

$price_featured_submission = homey_option('price_featured_listing');
$price_featured_submission = floatval( $price_featured_submission );

$user_email   = $current_user->user_email;
$admin_email  = get_bloginfo('admin_email');
$allowed_html = array();

if ( isset($_GET['token']) && isset($_GET['PayerID']) ){
    $token    = wp_kses ( $_GET['token'], $allowed_html );
    $payerID  = wp_kses ( $_GET['PayerID'] ,$allowed_html);

    /* Get saved data in database during execution
     -----------------------------------------------*/
    $transfered_data     = get_option('homey_featured_paypal_transfer');
    $payment_execute_url = $transfered_data[ $userID ]['payment_execute_url'];
    $is_listing_upgrade  = $transfered_data[ $userID ]['is_listing_upgrade'];
    $token               = $transfered_data[ $userID ]['paypal_token'];
    $listing_id          = $transfered_data[ $userID ]['listing_id'];

    $payment_execute = array(
        'payer_id' => $payerID
    );

    $json           = json_encode( $payment_execute );
    $json_response  = homey_execute_paypal_request( $payment_execute_url, $json, $token );

    $transfered_data[$current_user->ID ]  =   array();
    update_option ('homey_featured_paypal_transfer',$transfered_data);
    $paymentMethod = 'Paypal';

    if( $json_response['state']=='approved' ) {

        $time = time();
        $date = date( 'Y-m-d H:i:s', $time );

        update_post_meta($listing_id, 'homey_featured', 1);
        
        $invoiceID = homey_generate_invoice( 'upgrade_featured','one_time', $listing_id, $date, $userID, 0, 1, '', $paymentMethod );
        
        update_post_meta( $invoiceID, 'invoice_payment_status', 1 );

        $args = array(
            'listing_title'  =>  get_the_title($listing_id),
            'listing_id'     =>  $listing_id,
            'invoice_no' =>  $invoiceID,
        );
        

    }

}
?>
<div class="user-dashboard-right dashboard-with-sidebar">
    <div class="dashboard-content-area">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="dashboard-area">

                        <div class="block">
                            <div class="block-head">
                                <div class="block-left">
                                    <h2 class="title"><?php echo esc_attr($homey_local['pay_receive_label']); ?></h2>
                                </div><!-- block-left -->
                            </div><!-- block-head -->

                            <div class="block-body">
                                <p><strong><?php echo esc_attr($homey_local['order_info_label']); ?>:</strong></p>
                                <div class="payment-list">
                                    <ul>
                                        <li><?php echo esc_attr($homey_local['inv_pay_method']); ?>: <span><?php echo esc_attr($paymentMethod);?></span></li>
                                        <li><?php echo esc_attr($homey_local['inv_total']); ?>: <span><?php echo homey_formatted_price($price_featured_submission); ?></span></li>
                                    </ul>
                                </div><!-- payment-list --> 
                            </div>
                        </div><!-- .block -->
                    </div><!-- .dashboard-area -->
                </div><!-- col-lg-12 col-md-12 col-sm-12 -->
            </div>
        </div><!-- .container-fluid -->
    </div><!-- .dashboard-content-area -->                
</div><!-- .user-dashboard-right -->