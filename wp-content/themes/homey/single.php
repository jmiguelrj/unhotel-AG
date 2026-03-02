<?php
/**
 * The Template for displaying all single posts
 * @since Homey 1.0
 */
get_header();
global $post, $homey_local;
$sticky_sidebar = homey_option('sticky_sidebar');

$default_sidebar = isset($sticky_sidebar['default_sidebar']) ? $sticky_sidebar['default_sidebar'] : 0;
$blog_author_box = 1;
$content_classes = "col-xs-12 col-sm-12 col-md-8 col-lg-8";
if(!is_active_sidebar('blog-sidebar')) {
    $content_classes = "col-xs-12 col-sm-12 col-md-12 col-lg-12";
}

if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'single' ) ) {
?>
<section class="main-content-area">
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <div class="page-title">
                    <div class="block-top-title">
                        <?php get_template_part('template-parts/breadcrumb'); ?>
                    </div><!-- block-top-title -->
                </div><!-- page-title -->
            </div><!-- col-xs-12 col-sm-12 col-md-12 col-lg-12 -->
        </div><!-- .row -->
    </div><!-- .container -->

    <div class="container">
        <div class="row">
            <div class="<?php echo esc_attr($content_classes); ?>">

                <div class="blog-wrap">
                    <div class="article-main">
                        <?php
                        // Start the Loop.
                        while ( have_posts() ) : the_post(); $author = homey_get_author('48', '48', 'img-circle'); ?>

                        <article class="single-blog-article block">
                            <div class="block-body">
                                <h1><?php the_title(); ?></h1>

                                <ul class="article-meta list-inline pull-left">
                                    <li class="post-author">
                                        <?php echo ''.$author['photo']; ?>
                                        <?php echo esc_attr($homey_local['by_text_sm']); ?> <a href="<?php echo esc_url( $author['link'] ); ?>"><?php echo esc_attr( $author['name'] ); ?></a>
                                    </li>
                                    <li class="post-date"><i class="fa fa-calendar-o"></i> <?php echo esc_attr( get_the_date() ); ?> </li>
                                    <li class="post-category"><i class="fa fa-bookmark-o"></i> <?php echo get_the_category_list( _x( ', ', 'Used between list items, there is a space after the comma.', 'homey' ) ); ?></li>
                                    <li><i class="fa fa-comments-o"></i> <?php echo comments_number( '0', '1' ); ?></li>
                                </ul>
                            </div>

                            <?php if( has_post_thumbnail( $post->ID ) ) { ?>
                            <div class="post-featured-image">
                                <?php the_post_thumbnail( 'homey-gallery' ); ?>
                            </div>
                            <?php } ?>

                            <div class="article-detail block-body">
                                <?php the_content(); ?>

                                <?php
                                $args = array(
                                    'before'           => '<div class="pagination-main"><ul class="pagination">' . esc_html__('Pages:','homey'),
                                    'after'            => '</ul></div>',
                                    'link_before'      => '<span>',
                                    'link_after'       => '</span>',
                                    'next_or_number'   => 'next',
                                    'nextpagelink'     => '<span aria-hidden="true"><i class="fa fa-angle-right"></i></span>',
                                    'previouspagelink' => '<span aria-hidden="true"><i class="fa fa-angle-left"></i></span>',
                                    'pagelink'         => '%',
                                    'echo'             => 1
                                );
                                wp_link_pages( $args );
                                ?>

                            </div>

                            <?php get_template_part( 'single-post/tags' ); ?>
                    
                        </article>

                        <?php get_template_part( 'single-post/post-nav' ); ?>

                        <?php
                        if( $blog_author_box != 0 ) {
                            get_template_part('single-post/author');
                        }?>

                        <?php get_template_part( 'single-post/related-posts' ); ?>

                    
                        <?php
                        // If comments are open or we have at least one comment, load up the comment template.
                        if ( comments_open() || get_comments_number() ) {
                            comments_template();
                        }
                        endwhile; ?>
                    </div>
                </div><!-- grid-listing-page -->

            </div><!-- col-xs-12 col-sm-12 col-md-8 col-lg-8 -->

            <?php if(is_active_sidebar('blog-sidebar')) { ?>
            <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4  <?php if( $default_sidebar != 0 ){ echo ' homey_sticky'; } ?>">
                <div class="sidebar">
                    <?php get_sidebar(); ?>
                </div>
            </div>
            <?php } ?>
        </div><!-- .row -->
    </div>   <!-- .container -->
    
    
</section><!-- main-content-area listing-page grid-listing-page -->
<?php
}
get_footer();
