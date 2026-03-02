<?php

/**
 * Template part for displaying posts
 *
 * @package Unhotel
 */

?>

<section class="single-post-detail" id="single-post-detail">
	<div class="container">

		<article id="post-<?php the_ID(); ?>" <?php post_class('post'); ?>>
			<header class="post-header">
				<?php
				if (is_singular()) :
					the_title('<h1 class="post-title">', '</h1>');
				else :
					the_title('<h2 class="post-title"><a href="' . esc_url(get_permalink()) . '" rel="bookmark">', '</a></h2>');
				endif;

				if ('post' === get_post_type()) :
				?>
					<div class="post-meta">
						<?php
						//unhotel_posted_on();
						//unhotel_posted_by();
						?>
					</div><!-- .post-meta -->
				<?php endif; ?>

			</header><!-- .post-header -->

			<?php unhotel_post_thumbnail(); ?>

			<div class="post-content">
				<?php
				if (is_singular()) {
					the_content(
						sprintf(
							wp_kses(
								/* translators: %s: Name of current post. Only visible to screen readers */
								__('Continue reading<span class="screen-reader-text"> "%s"</span>', 'unhotel'),
								array(
									'span' => array(
										'class' => array(),
									),
								)
							),
							wp_kses_post(get_the_title())
						)
					);

					wp_link_pages(
						array(
							'before' => '<div class="page-links">' . esc_html__('Pages:', 'unhotel'),
							'after'  => '</div>',
						)
					);
				} else {
					the_excerpt();
				}
				?>
			</div><!-- .post-content -->

			<?php if (! is_singular()) : ?>
				<footer class="post-footer">
					<a href="<?php echo esc_url(get_permalink()); ?>" class="read-more">
						<?php esc_html_e('Read More', 'unhotel'); ?>
					</a>
				</footer><!-- .post-footer -->
			<?php endif; ?>
		</article><!-- #post-<?php the_ID(); ?> -->
	</div><!-- .container -->

</section><!-- .single-post-detail -->