<?php
global $post, $paged, $listing_founds, $number_of_listings;

$sidebar_meta = homey_get_sidebar_meta($post->ID);
$search_layout = homey_option('search_posts_layout');
$search_num_posts = homey_option('search_num_posts');
$sticky_sidebar = homey_option('sticky_sidebar');

if($sidebar_meta['homey_sidebar'] != 'yes') {
    $content_classes = 'col-xs-12 col-sm-12 col-md-12 col-lg-12';

} elseif($sidebar_meta['homey_sidebar'] == 'yes' && $sidebar_meta['sidebar_position'] == 'right') {
    $content_classes = 'col-xs-12 col-sm-12 col-md-8 col-lg-8';
    $sidebar_classes = 'col-xs-12 col-sm-12 col-md-4 col-lg-4';
    $sec_class = 'right-sidebar';

} elseif($sidebar_meta['homey_sidebar'] == 'yes' && $sidebar_meta['sidebar_position'] == 'left') {
    $content_classes = 'col-xs-12 col-sm-12 col-md-8 col-lg-8 col-md-push-4 col-lg-push-4';
    $sidebar_classes = 'col-xs-12 col-sm-12 col-md-4 col-lg-4 col-md-pull-8 col-lg-pull-8';
    $sec_class = 'left-sidebar';
}

$match_height_class = '';
if($search_layout == 'grid') {
    $match_height_class = 'homey-matchHeight-needed';
}


$number_of_listings = 9;

$number_of_prop = $search_num_posts;
if(!$number_of_prop){
    $number_of_prop = 9;
}

if ( is_front_page()  ) {
    $paged = (get_query_var('page')) ? get_query_var('page') : 1;
}
$search_qry = array(
    'post_type' => 'listing',
    'posts_per_page' => $number_of_prop,
    'paged' => $paged,
    'post_status' => 'publish'
);
?>

<section class="main-content-area listing-page listing-page-full-width <?php echo esc_attr($match_height_class); ?>">
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <div class="page-title">
                    <div class="block-top-title">
                        <?php get_template_part('template-parts/breadcrumb'); ?>
                        <h1 class="listing-title"><?php the_title(); ?> </h1>
                    </div><!-- block-top-title -->
                </div><!-- page-title -->
            </div>
        </div><!-- .row -->
    </div><!-- .container -->

    <div class="container">
        <div class="row">
            <div class="<?php echo esc_attr($content_classes); ?>">

                <?php 
                $search_qry = apply_filters( 'homey_search_filter', $search_qry );

                $search_qry = homey_listing_sort ( $search_qry );

                $search_qry = new WP_Query( $search_qry );
                if ( $search_qry->have_posts() ) : $listing_founds = $search_qry->found_posts; ?>

                <?php get_template_part('template-parts/listing/sort-tool'); ?>
                
                <div class="listing-wrap item-<?php echo esc_attr($search_layout);?>-view">
                    <div class="row">
                        <?php
                        while ( $search_qry->have_posts() ) : $search_qry->the_post();

                            if($search_layout == 'card') {
                                get_template_part('template-parts/listing/listing-card');
                            } else {
                                get_template_part('template-parts/listing/listing-item');
                            }

                        endwhile;
                        ?>
                    </div>
                    
                    <!--start Pagination-->
                    <?php homey_pagination( $search_qry->max_num_pages, $range = 2 ); wp_reset_postdata(); ?>
                    <!--start Pagination-->
                <?php wp_reset_query(); ?>
                </div><!-- listing-wrap -->

                <?php 
                else:
                    get_template_part('template-parts/listing/listing-none');
                endif;
                ?>

            </div>

            <?php if($sidebar_meta['homey_sidebar'] == 'yes') { ?>
            <div class="<?php echo esc_attr($sidebar_classes); if( $sticky_sidebar['listing_sidebar'] != 0 ){ echo ' homey_sticky'; }?>">
                <div class="sidebar <?php echo esc_attr($sec_class); ?>">
                    <?php get_sidebar('listing'); ?>
                </div>
            </div>
            <?php } ?>

        </div><!-- .row -->
    </div>   <!-- .container -->
    
    
</section><!-- main-content-area listing-page grid-listing-page -->