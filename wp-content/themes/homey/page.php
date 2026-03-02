<?php 
/**
 * The template for displaying all pages
 *
 * @package Homey
 * @since 	Homey 1.0
 * @author  Waqas Riaz
**/

get_header();
global $post;

$sidebar_meta = homey_get_sidebar_meta($post->ID);
$sticky_sidebar = homey_option('sticky_sidebar');

if($sidebar_meta['homey_sidebar'] != 'yes') {
    $content_classes = 'col-xs-12 col-sm-12 col-md-12 col-lg-12';

} elseif($sidebar_meta['homey_sidebar'] == 'yes' && $sidebar_meta['sidebar_position'] == 'right') {
    $content_classes = 'col-xs-12 col-sm-12 col-md-8 col-lg-8';
    $sidebar_classes = 'col-xs-12 col-sm-12 col-md-4 col-lg-4';

} elseif($sidebar_meta['homey_sidebar'] == 'yes' && $sidebar_meta['sidebar_position'] == 'left') {
    $content_classes = 'col-xs-12 col-sm-12 col-md-8 col-lg-8 col-md-push-4 col-lg-push-4';
    $sidebar_classes = 'col-xs-12 col-sm-12 col-md-4 col-lg-4 col-md-pull-8 col-lg-pull-8';
}
?>

<section class="main-content-area">
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
                <div class="page-title">
                    <div class="block-top-title">
                        <?php get_template_part('template-parts/breadcrumb'); ?>
                        <h1 class="listing-title"><?php the_title(); ?></h1>
                    </div><!-- block-top-title -->
                </div><!-- page-title -->
            </div>
        </div><!-- .row -->
    </div><!-- .container -->

    <div class="container">
        <div class="row">
            <div class="<?php echo esc_attr($content_classes); ?>">

                <div class="page-wrap">
                    <div class="article-main">
                    	<?php
						// Start the loop.
						while ( have_posts() ) : the_post();

							// Include the page content template.
							get_template_part( 'content', 'page' );

							// If comments are open or we have at least one comment, load up the comment template.
							if ( comments_open() || get_comments_number() ) :
								comments_template();
							endif;

							// End the loop.
						endwhile;
						?>
                    </div>
                </div><!-- grid-listing-page -->

            </div>

            <?php if($sidebar_meta['homey_sidebar'] == 'yes') { ?>
            <div class="<?php echo esc_attr($sidebar_classes); if( $sticky_sidebar['page_sidebar'] != 0 ){ echo ' homey_sticky'; }?>">
                <div class="sidebar">
                    <?php get_sidebar(); ?>
                </div>
            </div>
            <?php } ?>

        </div><!-- .row -->
    </div>   <!-- .container -->
    
    
</section><!-- main-content-area listing-page grid-listing-page -->
<?php get_footer(); ?>