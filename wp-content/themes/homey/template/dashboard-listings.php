<?php
/**
 * Template Name: Dashboard Listings
 */
if ( !is_user_logged_in() || homey_is_renter() ) {
    wp_redirect(  home_url('/') );
}

get_header(); 

global $current_user, $post;
$hide_labels = homey_option('show_hide_labels');

wp_get_current_user();
$userID         = $current_user->ID;
$user_login     = $current_user->user_login;
$edit_link      = homey_get_template_link('template/dashboard-submission.php');
$listings_page  = homey_get_template_link('template/dashboard-listings.php');

$publish_active = $pending_active = $draft_active = $mine_active = $all_active = $disabled_active = 'btn btn-primary-outlined btn-slim';
if( isset( $_GET['status'] ) && $_GET['status'] == 'publish' ) {
    $publish_active = 'btn btn-primary btn-slim';

} elseif( isset( $_GET['status'] ) && $_GET['status'] == 'pending' ) {
    $pending_active = 'btn btn-primary btn-slim';

} elseif( isset( $_GET['status'] ) && $_GET['status'] == 'draft' ) {
    $draft_active = 'btn btn-primary btn-slim';

} elseif( isset( $_GET['status'] ) && $_GET['status'] == 'disabled' ) {
    $disabled_active = 'btn btn-primary btn-slim';

} elseif( isset( $_GET['status'] ) && $_GET['status'] == 'mine' ) {
    $mine_active = 'btn btn-primary btn-slim';

} else {
    $all_active = 'btn btn-primary btn-slim';
}

$all_link = add_query_arg( 'status', 'any', $listings_page );
$publish_link = add_query_arg( 'status', 'publish', $listings_page );
$pending_link = add_query_arg( 'status', 'pending', $listings_page );
$draft_link = add_query_arg( 'status', 'draft', $listings_page );
$disabled_link = add_query_arg( 'status', 'disabled', $listings_page );
$mine_link = add_query_arg( 'status', 'mine', $listings_page );

$qry_status = isset( $_GET['status'] ) ? $_GET['status'] : 'any';

$no_of_listing   =  '9';
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$args = array(
    'post_type'         =>  'listing',
    'orderby'           => 'modified',
    'order'             => 'DESC',
    'paged'             => $paged,
    'posts_per_page'    => $no_of_listing,
    'post_status'       =>  $qry_status
);

if(homey_is_host() || homey_is_renter()) {
    $args['author'] = $userID;
} else {
    if( isset( $_GET['status'] ) && $_GET['status'] == 'mine' ) {
        $args['author'] = $userID;
    }
}

if( isset ( $_GET['keyword'] ) ) {
    $keyword = trim( $_GET['keyword'] );
    if ( ! empty( $keyword ) ) {
        $args['s'] = $keyword;

        // to search with ID
        if( is_numeric( $keyword ) ) {
            $id = abs( intval( $keyword ) );
            if( $id > 0 ) {
                unset( $args[ 's' ] );
                $args['post__in'] = array($keyword);
            }
        }
        // end of to search with ID
    }
}

$args = homey_listing_sort ( $args );
$listing_qry = new WP_Query($args);

$post_type = 'listing';
$user_post_count = count_user_posts( $userID , $post_type );
$num_posts    = wp_count_posts( $post_type, 'readable' );
/*print_r($num_posts);
echo $num_posts->publish;*/
$num_post_arr = (array) $num_posts;
unset($num_post_arr['auto-draft']);
$total_posts  = array_sum($num_post_arr);
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
                        <div id="listings_module_section" class="dashboard-area">
                            <div class="block">
                                <div class="block-title">
                                    <div class="block-left">
                                        <h2 class="title"><?php echo esc_attr($homey_local['manage_label']); ?></h2>
                                        <div class="mt-10">
                                            <?php 
                                                

                                                if(homey_is_admin()) {
                                                    echo '<a class="'.esc_attr($all_active).'" href="'.esc_url($all_link).'">'.esc_html__('All', 'homey').' ('.$total_posts.')</a> ';

                                                    echo '<a class="'.$mine_active.'" href="'.esc_url($mine_link).'">'.esc_html__('Mine', 'homey').' ('.$user_post_count.')</a> '; 
                                                } else {
                                                    echo '<a class="'.esc_attr($all_active).'" href="'.esc_url($all_link).'">'.esc_html__('All', 'homey').'</a> ';
                                                }

                                                foreach ($num_posts as $key => $value) {
                                                    if($value != 0) {
                                                        if($key == 'publish' || $key == 'pending' || $key == 'draft' || $key == 'disabled') {
                                                            if($key == 'publish') {
                                                                $key_text = esc_html__('Published', 'homey');
                                                                $b_class = $publish_active;
                                                                $b_link = $publish_link;

                                                            } elseif($key == 'pending') {
                                                                $key_text = esc_html__('Pending', 'homey');
                                                                $b_class = $pending_active;
                                                                $b_link = $pending_link;

                                                            } elseif($key == 'draft') {
                                                                $key_text = esc_html__('Draft', 'homey');
                                                                $b_class = $draft_active;
                                                                $b_link = $draft_link;
                                                            } elseif($key == 'disabled') {
                                                                $key_text = esc_html__('Disabled', 'homey');
                                                                $b_class = $disabled_active;
                                                                $b_link = $disabled_link;
                                                            }
                                                            if(homey_is_admin()) {
                                                                echo '<a class="'.$b_class.'" href="'.esc_url($b_link).'">'.$key_text.' ('.$value.')</a> ';
                                                            } else {
                                                                echo '<a class="'.$b_class.'" href="'.esc_url($b_link).'">'.$key_text.'</a> ';
                                                            }
                                                        }
                                                    }
                                                }
                                            ?>

                                        </div>
                                        
                                    </div>
                                    <div class="block-right">
                                        <div class="dashboard-form-inline">
                                            <form class="form-inline">
                                                <div class="form-group">
                                                    <input name="keyword" type="text" class="form-control" value="<?php echo isset($_GET['keyword']) ? $_GET['keyword'] : '';?>" placeholder="<?php echo esc_attr__('Search listing', 'homey'); ?>">
                                                </div>
                                                <button type="submit" class="btn btn-primary btn-search-icon"><i class="fa fa-search" aria-hidden="true"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <?php 
                                if($listing_qry->have_posts()): ?>
                                    <div class="table-block dashboard-listing-table dashboard-table">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th><?php echo esc_attr($homey_local['thumb_label']); ?></th>
                                                    <th><?php echo esc_attr($homey_local['address']); ?></th>
                                                    <th><?php echo homey_option('sn_type_label'); ?></th>
                                                    <th><?php echo esc_attr($homey_local['price_label']); ?></th>

                                                <?php if($hide_labels['sn_bedrooms_label'] != 1){?>
                                                    <th><?php echo homey_option('glc_bedrooms_label');?></th>
                                                <?php } ?>

                                                <?php if($hide_labels['sn_bathrooms_label'] != 1){?>
                                                    <th><?php echo homey_option('glc_baths_label');?></th>
                                                <?php } ?>

                                                <?php if($hide_labels['sn_guests_label'] != 1){?>
                                                    <th><?php echo homey_option('glc_guests_label');?></th>
                                                <?php } ?>


                                                    <th><?php echo homey_option('sn_id_label');?></th>
                                                    <th><?php echo esc_attr($homey_local['status_label']); ?></th>
                                                    <th><?php echo esc_attr($homey_local['actions_label']); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody id="module_listings">
                                                <?php 
                                                while ($listing_qry->have_posts()): $listing_qry->the_post();
                                                    get_template_part('template-parts/dashboard/listing-item');
                                                endwhile;
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php
                                else:
                                    echo '<div class="block-body">';
                                    echo esc_attr($homey_local['listing_dont_have']);  
                                    echo '</div>';      
                                endif; 
                                ?>
                            </div><!-- .block -->

                            <?php homey_pagination( $listing_qry->max_num_pages, $range = 2 ); ?>

                        </div><!-- .dashboard-area -->
                    </div><!-- col-lg-12 col-md-12 col-sm-12 -->
                </div>
            </div><!-- .container-fluid -->
        </div><!-- .dashboard-content-area --> 
    </div><!-- .user-dashboard-right -->

</section><!-- #body-area -->


<?php get_footer();?>
