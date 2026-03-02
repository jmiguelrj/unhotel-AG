<?php
global $current_user, $post, $homey_local, $reservation_page_link, $wallet_page_link, $earnings_page_link, $payout_request_link, $payouts_page_link;
$current_user = wp_get_current_user();
$userID       = $current_user->ID;
$local = homey_get_localization();

if(homey_is_renter($userID)){
    $available_balance = homey_get_get_security_deposit($userID);
}else{
    $available_balance = homey_get_host_available_earnings($userID);
}

$minimum_payout_amount = homey_get_minimum_payout_amount();

$dashboard_profile = homey_get_template_link_dash('template/dashboard-profile.php');
$payment_method_setup = add_query_arg( 'dpage', 'payment-method', $dashboard_profile );
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="dashboard-area">

                <div id="payout_alert" class="alert alert-success alert-dismissible" role="alert" style="display: none;">
                    <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>
                    <span></span>
                </div>

                <div class="block">
                    <div class="block-title">
                        <div class="block-left">
                            <h2 class="title"><?php esc_html_e('Available Balance', 'homey'); ?> <?php echo homey_formatted_price($available_balance); ?></h2>
                        </div>
                        <div class="block-right">
                            <a href="<?php echo esc_url($payment_method_setup); ?>" class="btn btn-primary btn-slim"><?php esc_html_e('Setup Payout Method', 'homey'); ?></a>
                        </div>
                    </div>

                    <div class="block-body">
                        <div class="form-group">
                            <p><?php esc_html_e('Note: the minimum amount for a payout is', 'homey'); ?> <?php echo homey_formatted_price($minimum_payout_amount, true); ?></p>
                            <label for="property-title"><?php esc_html_e('Payout Amount', 'homey'); ?></label>
                            <input type="text" id="payout_amount" name="title" class="form-control" placeholder="<?php esc_html_e('Enter the payout amount. Only numbers', 'homey'); ?>">
                        </div>
                        <?php wp_nonce_field( 'homey_payout_request_nonce', 'homey_payout_request_security' ); ?>
                        <button id="request_payout" type="submit" class="btn btn-success btn-xs-full-width"><?php esc_html_e('Request a Payout', 'homey'); ?></button>
                    </div>
                </div>
            </div>
        </div><!-- .dashboard-area -->
    </div><!-- col-lg-12 col-md-12 col-sm-12 -->
</div>