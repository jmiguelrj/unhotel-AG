<?php
/**
 * Template Name: Dashboard Invoices
 */
if ( !is_user_logged_in() ) {
    wp_redirect(  home_url('/') );
}

get_header();

global $paged, $homey_local, $current_user, $dashboard_invoices;

wp_get_current_user();
$userID         = $current_user->ID;
$user_login     = $current_user->user_login;
$dashboard_invoices = homey_get_template_link_dash('template/dashboard-invoices.php');

$is_detail = false;

if( isset( $_GET['invoice_id']) && !empty($_GET['invoice_id']) ) {
    $is_detail = true;
}

if ( is_front_page()  ) {
    $paged = (get_query_var('page')) ? get_query_var('page') : 1;
}

$invoices_content = '';

if( ! isset( $_GET['invoice_id']) ) {
    
    $meta_query = array();

    $invoices_args = array(
        'post_type' => 'homey_invoice',
        'posts_per_page' => '9',
        'paged' => $paged,
        'order' => 'DSC'
    );

    if(homey_is_renter()) {
        $meta_query[] = array(
            'key' => 'homey_invoice_buyer',
            'value' => $userID,
            'compare' => '='
        );
    } else {
        if(!homey_is_admin()){
            $meta_query[] = array(
                'key' => 'homey_invoice_buyer',
                'value' => $userID,
                'compare' => '='
            );
            $meta_query[] = array(
                'key' => 'invoice_resv_owner',
                'value' => $userID,
                'compare' => '='
            );
            $meta_query['relation'] = 'OR';
        }
    }

    $invoices_args['meta_query'] = $meta_query;

    $invoice_query = new WP_Query($invoices_args);
    $total = 0;
}
?>

<section id="body-area">

    <div class="dashboard-page-title">
        <h1><?php echo esc_html__(the_title('', '', false), 'homey'); ?></h1>
    </div><!-- .dashboard-page-title -->

    <?php get_template_part('template-parts/dashboard/side-menu'); ?>

    <div class="user-dashboard-right <?php if($is_detail){ echo 'dashboard-with-sidebar'; } else { echo 'dashboard-without-sidebar';} ?>">
        <div class="dashboard-content-area">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="dashboard-area">

                            <?php 
                            if($is_detail) {

                                get_template_part('template-parts/dashboard/invoices/detail');

                            } else { ?>

                            <div class="block">
                                <div class="block-title">
                                    <h2 class="title"><?php echo esc_attr($homey_local['manage_label']); ?></h2>
                                </div>
                                
                                <?php 
                                get_template_part('template-parts/dashboard/invoices/search'); ?>

                                <div class="table-block dashboard-reservation-table dashboard-table">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th><?php echo esc_attr($homey_local['order']); ?></th>
                                                <th><?php echo esc_attr($homey_local['inv_date']); ?></th>
                                                <th><?php echo esc_attr($homey_local['billing_for']); ?></th>
                                                <th><?php echo esc_attr($homey_local['billing_type']); ?></th>
                                                <th><?php echo esc_attr($homey_local['inv_status']); ?></th>
                                                <th><?php echo esc_attr($homey_local['inv_pay_method']); ?></th>
                                                <th><?php echo esc_attr($homey_local['inv_total']); ?></th>
                                                <th><?php echo esc_attr($homey_local['inv_actions']); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody id="invoices_content">
                                            <?php 
                                            if( ! isset( $_GET['invoice_id']) ) {
                                                if ($invoice_query->have_posts()) :
                                                    while ($invoice_query->have_posts()) : $invoice_query->the_post();
                                                        $invoice_meta = homey_get_invoice_meta(get_the_ID());
                                                        get_template_part('template-parts/dashboard/invoices/item');

                                                        $total += $invoice_meta['invoice_item_price'] > 0 ?  $invoice_meta['invoice_item_price'] : 0;

                                                    endwhile; endif;
                                                wp_reset_postdata();
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div><!-- .block --> 
                            <?php } ?>   
                            <?php 
                            if(!$is_detail) {
                                homey_pagination( $invoice_query->max_num_pages, $range = 2 ); 
                            }
                            ?>
                        </div><!-- .dashboard-area -->
                    </div><!-- col-lg-12 col-md-12 col-sm-12 -->
                </div>
            </div><!-- .container-fluid -->
        </div><!-- .dashboard-content-area -->  

        <?php if($is_detail) { ?>
        <aside class="dashboard-sidebar">
            <a href="#" id="invoice-print-button" data-id="<?php echo intval($_GET['invoice_id']); ?>" class="btn btn-grey btn-full-width"><?php echo esc_html__('Print', 'homey'); ?></a>
            <a href="<?php echo esc_url($dashboard_invoices); ?>" class="btn btn-secondary btn-full-width"><?php echo esc_html__('Go back', 'homey'); ?></a>
        </aside><!-- .dashboard-sidebar -->   
        <?php } ?>

    </div><!-- .user-dashboard-right -->


</section><!-- #body-area -->


<?php get_footer();?>
