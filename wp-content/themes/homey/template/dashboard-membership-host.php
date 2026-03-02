<?php
/**
 * Template Name: Dashboard Membership Host
 */
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( !is_user_logged_in() ) {
?>
<form id="redirect_submit_form" action="<?php echo home_url('/'); ?>" method="post">
    <input type="hidden" name="referer_link" value="<?php echo $_SERVER['HTTP_HOST'].''.$_SERVER['REQUEST_URI']?>">
    <input type="hidden" name="is_login" value="0">
    <button id="redirect_submit_form_btn" type="submit" value=""><?php esc_html_e('Unauthorized and redirecting to home page..', 'homey'); ?></button>
</form>
    <script>document.getElementById("redirect_submit_form").submit();</script>
<?php
    wp_die();
}
global $wpdb, $current_user, $userID, $homey_local, $homey_threads;

$current_user = wp_get_current_user();
$userID = $current_user->ID;
$author = homey_get_author_by_id('100', '100', 'img-circle', $userID);

$user_email   = $current_user->user_email;
$admin_email  = get_bloginfo('admin_email');
$allowed_html = array();

$subscription_info = get_active_membership_plan();
$membership_package_name = isset($subscription_info['subscriptionObj']->post_title) ? $subscription_info['subscriptionObj']->post_title : esc_html__('No Package Selected', 'homey');
$membership_package_meta = isset($subscription_info['subscriptionObj']->ID) ?  get_post_meta($subscription_info['subscriptionObj']->ID): array();

$expiry_date_text = 'n-a';
if (isset($subscription_info['subscriptionObj']->post_title)) {
    $expiry_date_text = empty($subscription_info['subscriptionExpiryDate']) ? esc_html__('No Expiry Date', 'homey') : homey_format_date_simple($subscription_info['subscriptionExpiryDate']);
}

$membership_link = homey_get_template_link('template/template-membership-webhook.php');
$total_available_listings = isset($membership_package_meta['hm_settings_listings_included'][0]) ? $membership_package_meta['hm_settings_listings_included'][0] : 0 ;
$total_available_featured_listings = isset($membership_package_meta['hm_settings_featured_listings'][0]) ? $membership_package_meta['hm_settings_featured_listings'][0] : 0;

get_header();
?>

<section id="body-area">

    <div class="dashboard-page-title">
        <h1><?php echo esc_html__(the_title('', '', false), 'homey'); ?></h1>
    </div><!-- .dashboard-page-title -->

    <?php get_template_part('template-parts/dashboard/side-menu'); ?>

    <div class="user-dashboard-right dashboard-without-sidebar">
        <div class="dashboard-content-area">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="dashboard-area">
                            <div class="wallet-box-wrap">
                                <div class="row">
                                    <div class="col-sm-12 col-xs-12">
                                        <div class="block table-block dashboard-withdraw-table dashboard-table">
                                            <div class="block-title">
                                                <div class="block-left">
                                                    <h2 class="title"><?php echo esc_html__($membership_package_name, 'homey'); ?></h2>
                                                </div>
                                                <div class="block-right">
                                                    <a class="btn btn-primary btn-slim" href="<?php echo $membership_link; ?>"><?php echo esc_html__('Upgrade Membership', 'homey'); ?></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php if( ! empty( $subscription_info['subscriptionDate'] ) ) { ?>
                            <div class="wallet-box-wrap">
                                <div class="row">
                                    <div class="col-sm-4 col-xs-12">
                                        <div class="wallet-box">
                                            <div class="block-big-text"><?php echo esc_attr($total_available_listings); ?></div>
                                            <h3><?php echo esc_html__('Total Listings', 'homey'); ?></h3>
                                            <div class="wallet-box-info mb-0"><?php echo esc_html__('Your membership will expire on', 'homey').' '.$expiry_date_text; ?></div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 col-xs-12">
                                        <div class="wallet-box">
                                            <div class="block-big-text"><?php echo esc_attr($author['publish_listing_count']); ?></div>
                                            <h3><?php echo esc_html__('Published Listings', 'homey'); ?></h3>
                                            <div class="wallet-box-info mb-0"><?php echo esc_html__('Remaining listings:', 'homey'); ?> <strong><?php echo $total_available_listings - esc_attr($author['publish_listing_count'])?></strong> </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 col-xs-12">
                                        <div class="wallet-box">
                                            <div class="block-big-text"><?php echo esc_attr($author['all_featured_listing_count']); ?></div>
                                            <h3><?php echo esc_html__('Featured Listings', 'homey'); ?></h3>
                                            <div class="wallet-box-info mb-0"><?php echo esc_html__('Remaining featured listings:', 'homey'); ?> <strong><?php echo $total_available_featured_listings - esc_attr($author['all_featured_listing_count']); ?></strong> </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                        </div><!-- .dashboard-area -->
                    </div><!-- col-lg-12 col-md-12 col-sm-12 -->
                </div>
            </div><!-- .container-fluid -->
        </div><!-- .dashboard-content-area -->

    </div><!-- .user-dashboard-right -->

</section><!-- #body-area -->


<?php get_footer();?>
