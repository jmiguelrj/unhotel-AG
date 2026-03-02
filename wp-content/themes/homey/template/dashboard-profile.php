<?php
/**
 * Template Name: Dashboard User Profile
 */
/*-----------------------------------------------------------------------------------*/
// Social Logins
/*-----------------------------------------------------------------------------------*/

if( isset($_GET['verification_id']) ){
    $args = array(
        'meta_query' => array(
            array(
                'key' => 'verification_id',
                'value' => $_GET['verification_id'],
                'compare' => '='
            )
        )
    );
     
    $verify_users = get_users($args); 
    $verify_user_id = 0;
    if ($verify_users) {  
      foreach ($verify_users as $user) {  
        $verify_user_id = $user->ID;
        update_user_meta($verify_user_id, 'is_email_verified', 1);
        update_user_meta($verify_user_id, 'verification_id', '');
      }
    }

    if($verify_user_id > 0){
        nocache_headers();
        header("Location: ". home_url('/?auth_message=your-profile-is-activated') );
        die();
    }
}

if( ( isset($_GET['code']) && isset($_GET['state']) ) ){
    homey_facebook_login($_GET);

} else if( isset( $_GET['openid_mode']) && $_GET['openid_mode'] == 'id_res' ) {
    homey_openid_login($_GET);

} else if (isset($_GET['code'])){
    homey_google_oauth_login($_GET);

} else {
    if ( !is_user_logged_in() ) {
        wp_redirect(  home_url('/') );
    }
}


get_header();
global $current_user, $author_info;

wp_get_current_user();
$userID = $current_user->ID;
$user_email = $current_user->user_email;

$admin_email =  get_bloginfo('admin_email');
$author_info = homey_get_author_by_id('100', '100', 'img-circle', $userID);
$offsite_payment = homey_option('off-site-payment');
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
                        <div class="dashboard-area">

                            <div id="profile_message"></div>

                            <?php 
                            if(isset($_GET['dpage']) && $_GET['dpage'] == 'password-reset') {
                                get_template_part('template-parts/dashboard/profile/password');

                            } elseif(isset($_GET['dpage']) && $_GET['dpage'] == 'verification') {
                                get_template_part('template-parts/dashboard/profile/verification');

                            } elseif(isset($_GET['dpage']) && $_GET['dpage'] == 'payment-method') { 
                                get_template_part('template-parts/dashboard/profile/payment-method');
                                
                            } else {

                                get_template_part('template-parts/dashboard/profile/progress'); 
                                get_template_part('template-parts/dashboard/profile/photo'); 
                                get_template_part('template-parts/dashboard/profile/information');
                                get_template_part('template-parts/dashboard/profile/address'); 
                                get_template_part('template-parts/dashboard/profile/contact');

                                if(!homey_is_renter()) {
                                    get_template_part('template-parts/dashboard/profile/social');
                                }
                                if(!homey_is_admin()) {
                                    get_template_part('template-parts/dashboard/profile/delete');
                                }

                            }
                            
                            wp_nonce_field( 'homey_profile_nonce', 'homey_profile_security' );   
                            ?>

                        </div><!-- .dashboard-area -->
                    </div><!-- col-lg-12 col-md-12 col-sm-12 -->
                </div>
            </div><!-- .container-fluid -->
        </div><!-- .dashboard-content-area -->    
        
        <aside class="dashboard-sidebar">
            <?php 
            if(isset($_GET['dpage']) && $_GET['dpage'] == 'payment-method' && $offsite_payment == 1) { 
                get_template_part('template-parts/dashboard/profile/upfront');
            } else {
                get_template_part('template-parts/dashboard/profile/status');
            }
            ?>
        </aside><!-- .dashboard-sidebar -->
        
    </div><!-- .user-dashboard-right -->

</section><!-- #body-area -->


<?php get_footer();?>
