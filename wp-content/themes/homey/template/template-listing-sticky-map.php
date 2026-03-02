<?php
/**
 * Template Name: Listings with Sticky Map
 */
get_header();
global $paged, $post, $paged, $listing_founds, $number_of_listings;

if(empty($paged))$paged = 1;


$pagination_type = 'number';

$page_id = $post->ID;
$listings_sort = get_post_meta( $page_id, $homey_prefix.'listings_sort', true );
$number_of_listings = get_post_meta( $page_id, $homey_prefix.'listings_num', true );
$types = get_post_meta( $page_id, $homey_prefix.'types', false );
$room_types = get_post_meta( $page_id, $homey_prefix.'room_types', false );
$countries = get_post_meta( $page_id, $homey_prefix.'countries', false );
$states = get_post_meta( $page_id, $homey_prefix.'states', false );
$cities = get_post_meta( $page_id, $homey_prefix.'cities', false );
$areas = get_post_meta( $page_id, $homey_prefix.'areas', false );
$booking_type = get_post_meta( $page_id, $homey_prefix.'listings_booking_type', true );

if(!empty($number_of_listings)) {
    $posts_per_page  = $number_of_listings;
} else {
    $posts_per_page = 9;
}
$sort_by = $listings_sort;       
if ( isset( $_GET['sortby'] ) ) {
    $sort_by = $_GET['sortby'];
}


$layout = homey_option('sticky_map_layout');

if ( isset( $_GET['list_type'] ) ) {
    $layout = $_GET['list_type'];
}

$listing_args = array(
    'post_type' => 'listing',
    'posts_per_page' => $posts_per_page,
    'paged' => $paged,
    'post_status' => 'publish'
);

$listing_args = apply_filters( 'homey_listing_filter', $listing_args );

$listing_args = homey_listing_sort ( $listing_args );

$listing_qry = new WP_Query( $listing_args );
?>

<section class="main-content-area listing-page listing-sticky-map">
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
                <div class="page-title">
                    <div class="block-top-title">
                        <?php get_template_part('template-parts/breadcrumb'); ?>
                        <h1 class="listing-title"><?php echo esc_html__(the_title('', '', false), 'homey'); ?></h1>
                    </div><!-- block-top-title -->
                </div><!-- page-title -->
            </div><!-- col-xs-12 col-sm-12 col-md-12 col-lg-12 -->
        </div><!-- .row -->
    </div><!-- .container -->

    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-6 col-md-push-6 col-lg-6 col-lg-push-6 homey-sticky-map">
                <div class="sticky-map">
                    <div id="homey_sticky_map" data-mapPaged="<?php echo intval($paged); ?>"></div>
                    <?php get_template_part('template-parts/map-controls'); ?>
                </div>
            </div>

            <div class="col-xs-12 col-sm-12 col-md-6 col-md-pull-6 col-lg-6 col-lg-pull-6">

                <?php
                if ( $listing_qry->have_posts() ) : $listing_founds = $listing_qry->found_posts; ?>

                <?php get_template_part('template-parts/listing/sort-tool'); ?>

                
                
                <div id="listings_module_section" class="listing-wrap item-<?php echo esc_attr($layout);?>-view">
                    <div id="module_listings" class="row">
                        <?php
                        while ( $listing_qry->have_posts() ) : $listing_qry->the_post();

                            if($layout == 'card') {
                                get_template_part('template-parts/listing/listing-card');
                            } else {
                                get_template_part('template-parts/listing/listing-item');
                            }

                        endwhile;
                        ?>
                    </div>

                    <!--start Pagination-->
                    <?php 
                    if($pagination_type == 'number') { 
                        homey_pagination( $listing_qry->max_num_pages, $range = 2 ); 

                    } else if($pagination_type == 'loadmore') { ?>

                        <div class="homey-loadmore loadmore text-center">
                            <a
                            data-paged="2" 
                            data-limit="<?php echo esc_attr($posts_per_page); ?>" 
                            data-style="<?php echo esc_attr($layout); ?>"  
                            data-type="<?php homey_array_to_comma_string($types); ?>" 
                            data-roomtype="<?php homey_array_to_comma_string($room_types); ?>"
                            data-country="<?php homey_array_to_comma_string($countries); ?>"  
                            data-state="<?php homey_array_to_comma_string($states); ?>" 
                            data-city="<?php homey_array_to_comma_string($cities); ?>" 
                            data-area="<?php homey_array_to_comma_string($areas); ?>" 
                            data-booking_type="<?php echo esc_attr($booking_type); ?>" 
                            data-featured="" 
                            data-offset=""
                            data-sortby="<?php echo esc_attr($sort_by); ?>"
                            href="#" 
                            class="btn btn-primary btn-long">
                                <i id="spinner-icon" class="fa fa-spinner fa-pulse fa-spin fa-fw" style="display: none;"></i>
                                <?php echo esc_attr($homey_local['loadmore_btn']); ?>
                            </a>
                        </div>

                    <?php } ?>
                    <!--start Pagination-->

                    <?php wp_reset_postdata(); ?>

                </div><!-- listing-wrap -->

                <?php 
                else:
                    get_template_part('template-parts/listing/listing-none');
                endif;
                ?>

            </div><!-- col-xs-12 col-sm-12 col-md-8 col-lg-8 -->
            
        </div><!-- .row -->
    </div>   <!-- .container -->
    
    
</section><!-- main-content-area listing-page grid-listing-page -->

<?php get_footer(); ?>
