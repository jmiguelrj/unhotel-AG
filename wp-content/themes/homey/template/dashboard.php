<?php
/**
 * Template Name: Dashboard
 */
if ( !is_user_logged_in() ) {
    wp_redirect(  home_url('/') );
}

global $homey_local, $homey_prefix;

$upgrade_featured = false;
$featured_success = false;
$all_users = false;
$user_detail = false;

if(isset($_GET['dpage']) && $_GET['dpage'] == 'upgrade_featured') {
    $upgrade_featured = true;

}elseif(isset($_GET['dpage']) && $_GET['dpage'] == 'featured_success') {
    $featured_success = true;
} elseif( (isset($_GET['dpage']) && $_GET['dpage'] == 'users') && (isset($_GET['user-id']) && $_GET['user-id'] != '') ) {
    if(!homey_is_admin()) {
        wp_redirect(  home_url('/') );
    }
    $user_detail = true;
} elseif(isset($_GET['dpage']) && $_GET['dpage'] == 'users') {
    if(!homey_is_admin()) {
        wp_redirect(  home_url('/') );
    }
    $all_users = true;
}
get_header();
?>


<section id="body-area">

    <div class="dashboard-page-title">
        <h1>
        <?php 
        if($upgrade_featured) {
            echo esc_html__('Upgrade to featured', 'homey'); 
        } elseif($featured_success) {
            echo esc_html__('Payment Received', 'homey');
        } elseif($all_users) {
            echo esc_html__('Users', 'homey');
        } elseif($user_detail) {
            echo esc_html__('User Profile', 'homey');
        } else {
            echo esc_html__(the_title('', '', false), 'homey');
        } 
        ?>
        </h1>
    </div><!-- .dashboard-page-title -->

    <?php get_template_part('template-parts/dashboard/side-menu'); ?>

    <?php
    if($upgrade_featured) {
        get_template_part('template-parts/dashboard/upgrade-featured');

    } elseif($featured_success) {
        get_template_part('template-parts/dashboard/featured-success');

    } elseif($all_users) {
        get_template_part('template-parts/dashboard/users/users');

    } elseif($user_detail) {
        get_template_part('template-parts/dashboard/users/user-detail');

    } else {
        get_template_part('template-parts/dashboard/dashboard'); 
    }
    ?>

</section><!-- #body-area -->


<?php get_footer();?>
