<?php
/**
 * Template Name: Reservation Payment
 */
if ( !is_user_logged_in() ) {
    wp_redirect(  home_url('/') );
}

get_header();
global $current_user;

wp_get_current_user();
$userID = $current_user->ID;

$reservation_id = $reservation_status = '';
if(isset($_GET['reservation_id']) && !empty($_GET['reservation_id'])) {
    $reservation_id = $_GET['reservation_id'];
    $reservation_status = get_post_meta($reservation_id, 'reservation_status', true);
} 
$offsite_payment = homey_option('off-site-payment');

$enable_paypal = homey_option('enable_paypal');
$enable_stripe = homey_option('enable_stripe');
$stripe_processor_link = homey_get_template_link('template/template-stripe-charge.php');
$is_hourly = get_post_meta($reservation_id, 'is_hourly', true);

$listing_owner = get_post_meta($reservation_id, 'listing_owner', true);

$user_meta = homey_get_author_by_id('100', '100', 'img-circle', $listing_owner);

$payout_payment_method = $user_meta['payout_payment_method'];
$payout_paypal_email = $user_meta['payout_paypal_email'];
$payout_skrill_email = $user_meta['payout_skrill_email'];

// Beneficiary Information
$ben_first_name = $user_meta['ben_first_name'];
$ben_last_name = $user_meta['ben_last_name'];
$ben_company_name = $user_meta['ben_company_name'];
$ben_tax_number = $user_meta['ben_tax_number'];
$ben_street_address = $user_meta['ben_street_address'];
$ben_apt_suit = $user_meta['ben_apt_suit'];
$ben_city = $user_meta['ben_city'];
$ben_state = $user_meta['ben_state'];
$ben_zip_code = $user_meta['ben_zip_code'];

//Wire Transfer Information
$bank_account = $user_meta['bank_account'];
$swift = $user_meta['swift'];
$bank_name = $user_meta['bank_name'];
$wir_street_address = $user_meta['wir_street_address'];
$wir_aptsuit = $user_meta['wir_aptsuit'];
$wir_city = $user_meta['wir_city'];
$wir_state = $user_meta['wir_state'];
$wir_zip_code = $user_meta['wir_zip_code'];
?>

<section id="body-area">

    <div class="dashboard-page-title">
        <h1><?php echo esc_html__(the_title('', '', false), 'homey'); ?></h1>
    </div><!-- .dashboard-page-title -->

    <?php get_template_part('template-parts/dashboard/side-menu'); ?>

    <div class="user-dashboard-right dashboard-with-sidebar">
        <div class="dashboard-content-area">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <?php 
                        if($offsite_payment == 1) { ?>

                            <div class="dashboard-area">

                                <div class="block">
                                    <div class="block-title">
                                        <div class="block-left">
                                            <h2 class="title"><?php esc_html_e('Payment info', 'homey'); ?></h2>
                                        </div>
                                        <div class="block-right">
                                            <a href="<?php echo esc_url(reservation_detail_link($reservation_id)); ?>" class="btn btn-primary btn-slim"><?php esc_html_e('Back', 'homey'); ?></a>
                                        </div><!-- block-right -->
                                        
                                    </div>

                                    <div class="local-payment-info">
                                        <?php echo homey_option('offsite-payment-instruction'); ?>
                                    </div>

                                    <div class="block-body">

                                        <ul class="list-unstyled">
                                            <li><strong><?php esc_html_e('Method', 'homey'); ?></strong> <?php echo homey_get_payout_method($payout_payment_method); ?></li>
                                        </ul>
                                        <ul class="list-unstyled list-lined">
                                            <li>
                                                <strong><?php esc_html_e('Beneficiary Name', 'homey'); ?></strong> 
                                                <?php 
                                                if(!empty($ben_first_name) || !empty($ben_last_name)) {
                                                    echo esc_attr($ben_first_name).' '.esc_attr($ben_last_name); 
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </li>
                                            
                                        </ul>
                                        <ul class="list-unstyled list-lined">
                                            <li>
                                                <strong><?php esc_html_e('Address', 'homey'); ?></strong> 
                                                <?php 
                                                if(!empty($ben_street_address)) {
                                                    echo esc_attr($ben_street_address); 
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </li>
                                        </ul>

                                        <ul class="list-unstyled list-lined mb-0">
                                            <?php if($payout_payment_method == 'paypal') { ?>

                                                    <li>
                                                        <strong><?php esc_html_e('PayPal Email', 'homey'); ?></strong> 
                                                        <?php echo esc_attr($payout_paypal_email); ?>
                                                    </li>

                                            <?php } elseif ($payout_payment_method == 'skrill') { ?>

                                                    <li>
                                                        <strong><?php esc_html_e('Skrill Email', 'homey'); ?></strong> 
                                                        <?php echo esc_attr($payout_skrill_email); ?>
                                                    </li>
                                                
                                            <?php } elseif ($payout_payment_method == 'wire') { ?>
                                                <li>
                                                    <strong><?php esc_html_e('Beneficiary Account Number', 'homey'); ?></strong> 
                                                    <?php 
                                                    if(!empty($bank_account)) {
                                                        echo esc_attr($bank_account); 
                                                    } else {
                                                        echo '-';
                                                    }
                                                    ?>
                                                </li>
                                                <li>
                                                    <strong><?php esc_html_e('SWIFT', 'homey'); ?></strong> 
                                                    <?php 
                                                    if(!empty($swift)) {
                                                        echo esc_attr($swift); 
                                                    } else {
                                                        echo '-';
                                                    }
                                                    ?>
                                                </li>
                                                <li>
                                                    <strong><?php esc_html_e('Bank Name', 'homey'); ?></strong> 
                                                    <?php 
                                                    if(!empty($bank_name)) {
                                                        echo esc_attr($bank_name); 
                                                    } else {
                                                        echo '-';
                                                    }
                                                    ?>
                                                </li>
                                                <li>
                                                    <strong><?php esc_html_e('Bank Address', 'homey'); ?></strong> 
                                                    <?php 
                                                    if(!empty($wir_street_address)) {
                                                        echo esc_attr($wir_street_address).', '.esc_attr($wir_city).', '.esc_attr($wir_state).', '.esc_attr($wir_zip_code);
                                                    } else {
                                                        echo '-';
                                                    }
                                                    ?>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                </div>

                                <?php if($reservation_status == 'available') { ?>
                                <div class="payment-buttons">
                                    <div id="homey_notify"></div>
                                    <input type="hidden" name="reservation_id" id="reservation_id" value="<?php echo intval($reservation_id); ?>">

                                    <button id="guest_paid_button" class="btn btn-success btn-full-width"><?php esc_html_e('Mark as Paid', 'homey'); ?></button>
                                </div>
                                <?php } ?>
                            </div>

                        <?php    
                        } else { ?>
                        <form name="homey_checkout" method="post" class="homey_payment_form" action="<?php echo esc_url($stripe_processor_link); ?>">
                            <div class="dashboard-area">

                                <div class="block">
                                    <div class="block-head">
                                        <div class="block-left">
                                            <h2 class="title"><?php esc_html_e('Select the payment method', 'homey'); ?></h2>
                                        </div><!-- block-left -->
                                    </div><!-- block-head -->
                                
                                    <div class="block-body">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="payment-method">
                                                    <?php if($enable_paypal != 0) { ?>
                                                    <div class="payment-method-block paypal-method">
                                                        <div class="form-group">
                                                            <label class="control control--radio radio-tab">
                                                                <input class="homey_check_gateway" name="payment_gateway" value="paypal" type="radio">
                                                                <span class="control-text"><?php esc_html_e('Paypal', 'homey'); ?></span>
                                                                <span class="control__indicator"></span>
                                                                <span class="radio-tab-inner"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <?php } ?>

                                                    <?php if($enable_stripe != 0) { ?>
                                                    <div class="payment-method-block stripe-method">
                                                        <div class="form-group">
                                                            <label class="control control--radio radio-tab">
                                                                <input class="homey_check_gateway" name="payment_gateway" value="stripe" type="radio">
                                                                <span class="control-text"><?php esc_html_e('Stripe', 'homey'); ?></span>
                                                                <span class="control__indicator"></span>
                                                                <span class="radio-tab-inner"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <?php } ?>
                                               
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                  
                                    <div class="block-section">
                                        <div class="block-body">
                                            <div class="block-left">
                                                <h2 class="title"><?php esc_html_e('Payment', 'homey'); ?></h2>
                                            </div><!-- block-left -->
                                            <div class="block-right">
                                                <?php 
                                                if($is_hourly == 'yes') {
                                                    echo homey_calculate_hourly_reservation_cost($reservation_id);
                                                } else {
                                                    echo homey_calculate_reservation_cost($reservation_id);
                                                }
                                                ?>
                                            </div><!-- block-right -->
                                        </div><!-- block-body -->
                                    </div><!-- block-section -->
                                </div><!-- .block -->

                                <?php 
                                if( $enable_stripe != 0 ) {
                                    if($is_hourly == 'yes') {
                                        homey_hourly_stripe_payment($reservation_id);
                                    } else {
                                        homey_stripe_payment($reservation_id);
                                    }
                                }
                                ?>

                                <?php if($reservation_status == 'available') { ?>
                                <div id="without_stripe" class="payment-buttons">
                                    <div id="homey_notify"></div>
                                    <input type="hidden" name="reservation_id" id="reservation_id" value="<?php echo intval($reservation_id); ?>">
                                    <input type="hidden" name="checkout-security" id="checkout-security" value="<?php echo wp_create_nonce('checkout-security-nonce'); ?>"/>

                                    <?php
                                    if($is_hourly == 'yes') { ?>
                                        <button id="make_hourly_booking_payment" class="btn btn-success btn-full-width"><?php esc_html_e('Process Payment', 'homey'); ?></button>
                                    <?php    
                                    } else { ?>
                                        <button id="make_booking_payment" class="btn btn-success btn-full-width"><?php esc_html_e('Process Payment', 'homey'); ?></button>
                                    <?php
                                    }
                                    ?>
                                    

                                </div>
                                <?php } ?>
                            </div><!-- .dashboard-area -->
                        </form>
                        <?php } ?>
                    </div><!-- col-lg-12 col-md-12 col-sm-12 -->
                </div>
            </div><!-- .container-fluid -->
        </div><!-- .dashboard-content-area -->    
        
    </div><!-- .user-dashboard-right -->

</section><!-- #body-area -->

<?php get_footer();?>
