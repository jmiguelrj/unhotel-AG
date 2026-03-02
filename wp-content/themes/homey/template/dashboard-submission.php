<?php
/**
 * Template Name: Dashboard Add Listing
 */
if ( !is_user_logged_in() ) {
    wp_redirect(  home_url('/') );
}

if ( homey_is_renter() ) {
    wp_redirect(  home_url('/') );
}

global $current_user, $hide_fields, $homey_local, $wpdb;
wp_get_current_user();
$userID = $current_user->ID;

$user_email = $current_user->user_email;
$admin_email =  get_bloginfo('admin_email');
$panel_class = '';

$invalid_nonce = false;
$submitted_successfully = false;
$updated_successfully = false;
$dashboard_listings = homey_get_template_link('template/dashboard-listings.php');
$dashboard_submission = homey_get_template_link('template/dashboard-submission.php');
$submitted_page_url = homey_get_template_link('template/dashboard-listing-submitted.php');
$hide_fields = homey_option('add_hide_fields');
$required_fields = homey_option('add_listing_required_fields');

$listing_mode = isset($_GET['mode']) ? $_GET['mode'] : '';


if( isset( $_POST['action'] ) ) {

    $submission_action = $_POST['action'];
    $tab = isset($_POST['current_tab']) ? $_POST['current_tab'] : '';

    $new_listing = array(
        'post_type' => 'listing'
    );

    $listing_id = apply_filters('listing_submission_filter', $new_listing);

    $listing_id = intval($listing_id);

    $args = array(
        'listing_title'  =>  get_the_title($listing_id),
        'listing_id'     =>  $listing_id
    );

    if( $submission_action == 'update_listing' ) {
        $return_url  = add_query_arg(
            array(
                'edit_listing' => $listing_id,
                'tab' => $tab,
                'message' => true,
            ),
            $dashboard_submission );
    } else {
        $return_url  = add_query_arg( 'listing_id', $listing_id, $submitted_page_url );
    }

    wp_redirect($return_url);
}
get_header();
//check before header to print message
hm_validity_check();//check if users membership is expired or not
$listing_limit_check = hm_listing_limit_check($userID, get_post_status($listing_id));

?>


    <section id="body-area">

        <div class="dashboard-page-title">
            <h1><?php if(isset($_GET['edit_listing'])){
                   echo __( 'Edit Listing', 'homey');
                }else{
                echo esc_html__(the_title('', '', false), 'homey');
            } ?>
            </h1>
        </div><!-- .dashboard-page-title -->

        <?php get_template_part('template-parts/dashboard/side-menu'); ?>

        <div class="user-dashboard-right dashboard-with-sidebar">
            <div class="dashboard-content-area">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="dashboard-area">

                                <?php if(isset($_GET['edit_listing']) && (isset($_GET['message']) && $_GET['message'] == 1)) { ?>
                                    <div class="alert alert-success alert-dismissible" role="alert">
                                        <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>
                                        <?php echo esc_attr($homey_local['list_updated']); ?>
                                    </div>
                                <?php } ?>

                                <?php if(isset($_GET['edit_listing']) && (isset($_GET['featured']) && $_GET['featured'] == 1)) { ?>
                                    <div class="alert alert-success alert-dismissible" role="alert">
                                        <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>
                                        <?php echo esc_attr($homey_local['list_upgrade_featured']); ?>
                                    </div>
                                <?php } ?>

                                <div class="validate-errors alert alert-danger alert-dismissible" role="alert">
                                    <button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    <?php echo wp_kses(__( '<strong>Error!</strong> Please fill out the required fields.', 'homey' ), homey_allowed_html() ); ?>
                                </div>
                                <div class="validate-errors-gal alert alert-danger alert-dismissible" role="alert">
                                    <button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    <?php echo wp_kses(__( '<strong>Error!</strong> Upload at least one image.', 'homey' ), homey_allowed_html() ); ?>
                                </div>

                                <?php
                                if (isset($_GET['edit_listing']) && !empty($_GET['edit_listing'])) {

                                    get_template_part('template-parts/dashboard/edit-listing/submit-form');

                                } else {

                                    if( homey_option('homey_site_mode') == 'both' && $listing_mode == '' ) {
                                        get_template_part('template-parts/dashboard/submit-listing/listing-mode');
                                    } else {
                                        get_template_part('template-parts/dashboard/submit-listing/submit-form');
                                    }

                                }
                                ?>

                            </div><!-- .dashboard-area -->
                        </div><!-- col-lg-12 col-md-12 col-sm-12 -->
                    </div>
                </div><!-- .container-fluid -->
            </div><!-- .dashboard-content-area -->

            <aside class="dashboard-sidebar">
                <?php get_template_part('template-parts/dashboard/sidebar-listing');?>
            </aside><!-- .dashboard-sidebar -->

        </div><!-- .user-dashboard-right -->

    </section><!-- #body-area -->

    <script>
        jQuery(".draft-listing-validation").on("click", function(){
            var allInput  = jQuery("#submit_listing_form").find('input');
            var allSelect = jQuery("#submit_listing_form").find('select');
            var isValid   = true;
            var validation_string = '';

            jQuery(allInput).each(function(i, itm){
                if(jQuery(itm).attr('required') == 'required' && jQuery(itm).val() == ''){
                    if(validation_string != ''){
                        validation_string += ', ';
                    }
                    validation_string += isElementValid(itm);
                }
            });

            jQuery(allSelect).each(function(i, itm){
                if(jQuery(itm).attr('required') =='required'){
                    if(jQuery(itm).attr('selected', 'selected') == ''){
                        if(validation_string != ''){
                            validation_string += ', ';
                        }
                        validation_string += isElementValid(itm);
                    }
                }
            });

            if(validation_string == '') {
                jQuery("#submit_listing_form").submit();
                return true;
            }
            jQuery(".validate-errors").show();
            jQuery(".validate-errors").text('<?php echo esc_html__('Please fill the following required fields.', 'homey'); ?>'+ validation_string);

            jQuery([document.documentElement, document.body]).animate({
                scrollTop: jQuery(".validate-errors").offset().top - 500
            }, 500);

            return validation_string;
        });

        function isElementValid(itm){
           if(typeof jQuery(itm).data('inputTitle') != 'undefined'){
                return jQuery(itm).data('inputTitle');
            }else{
                return '';
            }
        }

    </script>
<?php get_footer();?>
