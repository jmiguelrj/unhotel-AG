<?php
/**
 * Template Name: Wallet
 */

$enable_wallet = homey_option('enable_wallet');
if ( !is_user_logged_in() || $enable_wallet != 1) {
    wp_redirect(  home_url('/') );
}

get_header(); 

global $current_user, $post, $homey_local, $reservation_page_link, $wallet_page_link, $earnings_page_link, $payout_request_link, $payouts_page_link, $payouts_setup_page, $security_deposits_page;
$current_user = wp_get_current_user();
$userID       = $current_user->ID;

$reservation_page_link = homey_get_template_link('template/dashboard-reservations.php');
$wallet_page_link = homey_get_template_link('template/dashboard-wallet.php');
$earnings_page_link = add_query_arg( 'dpage', 'earnings', $wallet_page_link );
$payout_request_link = add_query_arg( 'dpage', 'payout-request', $wallet_page_link );
$payouts_page_link = add_query_arg( 'dpage', 'payouts', $wallet_page_link );
$payouts_setup_page = add_query_arg( 'dpage', 'payment-method', $wallet_page_link );
$security_deposits_page = add_query_arg( 'dpage', 'security-deposits', $wallet_page_link );

$is_homey_guest = false;
if( (homey_is_renter()) || (isset($_GET['guest']) && $_GET['guest'] != '') ) {
    $is_homey_guest = true;
}
?>

<section id="body-area">

    <div class="dashboard-page-title">
        <h1><?php echo esc_html__(the_title('', '', false), 'homey'); ?></h1>
    </div><!-- .dashboard-page-title -->

    <?php get_template_part('template-parts/dashboard/side-menu'); ?>

    <?php
    if( (isset($_GET['dpage']) && $_GET['dpage'] == 'payout-detail') && (isset($_GET['payout_id']) && $_GET['payout_id'] != '') ) {
        //if(homey_is_admin() || homey_is_host()) {
            get_template_part('template-parts/dashboard/wallet/payout-detail');
        //}

    } else { ?>

        <div class="user-dashboard-right dashboard-without-sidebar">
            <div class="dashboard-content-area">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="dashboard-area">

                                <?php 
                                if(isset($_GET['detail']) && $_GET['detail'] != '') {
                                    get_template_part('template-parts/dashboard/wallet/detail');

                                } elseif(isset($_GET['dpage']) && $_GET['dpage'] == 'earnings') {
                                    get_template_part('template-parts/dashboard/wallet/earnings');

                                } elseif(isset($_GET['dpage']) && $_GET['dpage'] == 'security-deposits') {
                                    get_template_part('template-parts/dashboard/wallet/security-deposits');

                                } /*elseif(isset($_GET['dpage']) && $_GET['dpage'] == 'payment-method') {
                                    get_template_part('template-parts/dashboard/wallet/payment-method');

                                }*/ elseif(isset($_GET['dpage']) && $_GET['dpage'] == 'payout-request') {
                                    get_template_part('template-parts/dashboard/wallet/payout-request');

                                } elseif(isset($_GET['dpage']) && $_GET['dpage'] == 'payouts') {
                                    if(homey_is_admin()) {
                                        get_template_part('template-parts/dashboard/wallet/payouts');
                                    } elseif(homey_is_host() || homey_is_renter()) {
                                        get_template_part('template-parts/dashboard/wallet/payouts-host');
                                    }

                                } else {
                                    if($is_homey_guest) {
                                        get_template_part('template-parts/dashboard/wallet/main-guest');
                                    } else {
                                        get_template_part('template-parts/dashboard/wallet/main');
                                    }
                                }
                                ?>

                            </div><!-- .dashboard-area -->
                            
                        </div><!-- col-lg-12 col-md-12 col-sm-12 -->
                    </div>
                </div><!-- .container-fluid -->
            </div><!-- .dashboard-content-area --> 
        </div><!-- .user-dashboard-right -->

    <?php
    }
    ?>

</section><!-- #body-area -->


<?php get_footer();?>
