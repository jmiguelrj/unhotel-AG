<?php

/**
 * Template part for displaying page content in page.php
 *
 * @package Unhotel
 */


// Ensure WordPress functions are available
if (!function_exists('is_front_page')) {
	require_once(ABSPATH . 'wp-includes/query.php');
}

// Get page slug for dynamic class
$page_slug = get_post_field('post_name', get_the_ID());
$page_class = !empty($page_slug) ? 'page-' . esc_attr($page_slug) : '';

// Check if title should be hidden
$hide_title = get_post_meta(get_the_ID(), 'unhotel_hide_title', true);
$header_class = !$hide_title ? 'post-header title-page-visible' : 'post-header';
?>
<div class="container <?php echo $page_class; ?>" >
	<header class="<?php echo $header_class; ?>">
		<?php
			if (!is_front_page() && !$hide_title) :
		?>
			<?php the_title('<h1 class="post-title">', '</h1>'); ?>
		<?php endif; ?>
	</header><!-- .post-header -->
	<?php unhotel_post_thumbnail(); ?>
	<div class="row">
		<div class="col-12">
			<div class="page-wrap <?php echo $page_class; ?>">
				<div class="article-main">
					<article id="post-<?php the_ID(); ?>" class="single-page-article section-content <?php echo $page_class; ?>" <?php post_class('post'); ?>>
						<div class="article-detail block-body">
							<?php
							the_content();
							wp_link_pages(
								array(
									'before' => '<div class="page-links">' . esc_html__('Pages:', 'unhotel'),
									'after'  => '</div>',
								)
							);
							?>
						</div><!-- .post-content -->

						<?php if (get_edit_post_link()) : ?>
							<footer class="post-footer">
								<?php
								edit_post_link(
									sprintf(
										wp_kses(
											/* translators: %s: Name of current post. Only visible to screen readers */
											__('Edit <span class="screen-reader-text">%s</span>', 'unhotel'),
											array(
												'span' => array(
													'class' => array(),
												),
											)
										),
										wp_kses_post(get_the_title())
									),
									'<span class="edit-link">',
									'</span>'
								);
								?>
							</footer><!-- .post-footer -->
						<?php endif; ?>

					</article><!-- section-content -->
				</div> <!-- .article-main -->
			</div> <!-- .page-wrap -->
		</div> <!-- .col-12 -->
	</div> <!-- .row -->
</div> <!-- .container -->