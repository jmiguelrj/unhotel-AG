<?php
/**
 */
get_header();

global $post, $listing_founds, $listing_view;



$sticky_sidebar = homey_option('sticky_sidebar');

$taxonomy_sidebar = homey_option('taxonomy_layout');
$taxonomy_layout = homey_option('taxonomy_posts_layout');
$taxonomy_num_posts = homey_option('taxonomy_num_posts');

if($taxonomy_sidebar == 'no-sidebar') {
    $content_classes = 'col-xs-12 col-sm-12 col-md-12 col-lg-12';

} elseif($taxonomy_sidebar == 'right-sidebar') {
    $content_classes = 'col-xs-12 col-sm-12 col-md-8 col-lg-8';
    $sidebar_classes = 'col-xs-12 col-sm-12 col-md-4 col-lg-4';
    $sec_class = "right-sidebar";

} elseif($taxonomy_sidebar == 'left-sidebar') {
    $content_classes = 'col-xs-12 col-sm-12 col-md-8 col-lg-8 col-md-push-4 col-lg-push-4';
    $sidebar_classes = 'col-xs-12 col-sm-12 col-md-4 col-lg-4 col-md-pull-8 col-lg-pull-8';
    $sec_class = "left-sidebar";
}

$number_of_listings = 9;

$number_of_prop = $taxonomy_num_posts;
if(!$number_of_prop){
    $number_of_prop = 9;
}

$sort_args = array('posts_per_page' => $number_of_prop, 'post_status' => 'publish');
$sort_args = homey_listing_sort($sort_args);

global $wp_query;
$args = array_merge( $wp_query->query_vars, $sort_args );

$wp_query = new WP_Query( $args );
?>

<section class="main-content-area listing-page listing-page-full-width">
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
                <div class="page-title">
                    <div class="block-top-title">
                        <h1 class="listing-title">
                            <?php echo post_type_archive_title(); ?>
                        </h1>
                    </div><!-- block-top-title -->
                </div><!-- page-title -->
            </div>

            <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3 hidden-xs">
                <?php get_template_part('template-parts/listing/layout-tool'); ?>
            </div>
        </div><!-- .row -->
    </div><!-- .container -->

    <div class="container">
        <div class="row">
            <div class="<?php echo esc_attr($content_classes); ?>">

                <?php if ( $wp_query->have_posts() ) : $listing_founds = $wp_query->found_posts; ?>

                <?php get_template_part('template-parts/listing/sort-tool'); ?>
                
                <div class="listing-wrap item-<?php echo esc_attr($taxonomy_layout);?>-view">
                    <div class="row">
                        <?php
                        while ( $wp_query->have_posts() ) : $wp_query->the_post();

                            if($taxonomy_layout == 'card') {
                                get_template_part('template-parts/listing/listing-card');
                            } else {
                                get_template_part('template-parts/listing/listing-item');
                            }

                        endwhile;
                        ?>
                    </div>

                    <!--start Pagination-->
                    <?php homey_pagination( $wp_query->max_num_pages, $range = 2 ); wp_reset_postdata(); ?>
                    <!--start Pagination-->

                </div><!-- listing-wrap -->

                <?php 
                else:
                    get_template_part('template-parts/listing/listing-none');
                endif;
                ?>

            </div>

            <?php if($taxonomy_sidebar != 'no-sidebar') { ?>
            <div class="<?php echo esc_attr($sidebar_classes); if( $sticky_sidebar['listing_sidebar'] != 0 ){ echo ' homey_sticky'; }?>">
                <div class="sidebar <?php echo esc_attr($sec_class); ?>">
                    <?php get_sidebar('listing'); ?>
                </div>
            </div>
            <?php } ?>

        </div><!-- .row -->
    </div>   <!-- .container -->
    
    
</section><!-- main-content-area listing-page grid-listing-page -->

<?php get_footer();?>