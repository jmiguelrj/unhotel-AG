<?php
/**
 * Template Name: Homey Page Builder
 */
get_header(); ?>

<section class="main-content-area listing-page listing-page-full-width">
        
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                
                <?php
				while ( have_posts() ): the_post();
				the_content();
				endwhile;
				?>

            </div><!-- col-xs-12 col-sm-12 col-md-8 col-lg-8 -->
        </div><!-- .row -->
    </div>   <!-- .container -->
    
    
</section><!-- main-content-area listing-page grid-listing-page -->

<?php get_footer(); ?>