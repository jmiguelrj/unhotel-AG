<?php 
/**
 * 404 error page
 *
 * @package homey
 * @since 	homey 1.0
**/
global $homey_local;
$title_404 = homey_option('404-title');
$title_des = homey_option('404-des');
?>

<?php get_header(); ?>

<section class="main-content-area">
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <div class="page-wrap">
                    <div class="article-main">
                        <article class="single-page-article text-center">
                            <div class="article-detail block-body">
                                <h1><?php echo esc_html( $title_404 ); ?></h1>
                                <p><?php echo wp_kses_post( $title_des ); ?></p>
                                <p><a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Back to the homepage', 'homey'); ?></a></p>
                            </div>
                        </article>
                    </div>
                </div><!-- grid-listing-page -->
            </div><!-- col-xs-12 col-sm-12 col-md-8 col-lg-8 -->
        </div><!-- .row -->
    </div>   <!-- .container -->
</section><!-- main-content-area listing-page grid-listing-page -->
	
<?php get_footer(); ?>