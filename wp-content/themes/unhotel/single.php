<?php

/**
 * The template for displaying all single posts
 *
 * @package Unhotel
 */

get_header();
?>

<div class="content-area content-single-post">
	<div class="container">
		<main id="primary" class="site-main">

			<?php
			while (have_posts()) :
				the_post();

				get_template_part('template-parts/content', get_post_type());

				// Post Navigation (Previous/Next)
			?>
				<div class="container">
					<nav class="post-navigation-wrapper">
						<?php
						$prev_post = get_previous_post();
						$next_post = get_next_post();
						?>

						<?php if ($prev_post) : ?>
							<div class="nav-previous">
								<a href="<?php echo esc_url(get_permalink($prev_post->ID)); ?>">
									<?php if (has_post_thumbnail($prev_post->ID)) : ?>
										<div class="nav-thumbnail">
											<?php echo get_the_post_thumbnail($prev_post->ID, 'large'); ?>
										</div>
									<?php endif; ?>
									<div class="nav-content">
										<span class="nav-subtitle"><?php esc_html_e('Post anterior', 'unhotel'); ?></span>
										<span class="nav-title"><?php echo esc_html(get_the_title($prev_post->ID)); ?></span>
									</div>
								</a>
							</div>
						<?php endif; ?>

						<?php if ($next_post) : ?>
							<div class="nav-next">
								<a href="<?php echo esc_url(get_permalink($next_post->ID)); ?>">
									<?php if (has_post_thumbnail($next_post->ID)) : ?>
										<div class="nav-thumbnail">
											<?php echo get_the_post_thumbnail($next_post->ID, 'large'); ?>
										</div>
									<?php endif; ?>
									<div class="nav-content">
										<span class="nav-subtitle"><?php esc_html_e('Próxima postagem', 'unhotel'); ?></span>
										<span class="nav-title"><?php echo esc_html(get_the_title($next_post->ID)); ?></span>
									</div>
								</a>
							</div>
						<?php endif; ?>
					</nav>
				</div>
				<?php

				// Related Posts Section
				$categories = get_the_category();
				if ($categories) {
					$category_ids = array();
					foreach ($categories as $category) {
						$category_ids[] = $category->term_id;
					}

					$related_args = array(
						'category__in'        => $category_ids,
						'post__not_in'        => array(get_the_ID()),
						'posts_per_page'      => 3,
						'ignore_sticky_posts' => 1,
					);

					$related_posts = new WP_Query($related_args);

					if ($related_posts->have_posts()) :
				?>
						<div class="container">
						<div class="related-posts">
							<h2 class="related-posts-title"><?php esc_html_e('Postagens relacionadas', 'unhotel'); ?></h2>
							<div class="related-posts-list">
								<?php while ($related_posts->have_posts()) : $related_posts->the_post(); ?>
									<article class="related-post">
										<?php if (has_post_thumbnail()) : ?>
											<a href="<?php the_permalink(); ?>" class="related-post-thumbnail">
												<?php the_post_thumbnail('medium'); ?>
											</a>
										<?php endif; ?>
										<div class="related-post-content">
											<h3 class="related-post-title">
												<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
											</h3>
											<div class="related-post-meta">
												<time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
													<?php echo esc_html(get_the_date()); ?>
												</time>
												<?php
												$author_id = get_the_author_meta('ID');
												$author_name = get_the_author();
												?>
												<span class="related-post-author"><?php echo esc_html($author_name); ?></span>
											</div>
											<div class="related-post-excerpt">
												<?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?>
											</div>
											<a href="<?php the_permalink(); ?>" class="related-post-link">
												<?php esc_html_e('Saiba Mais', 'unhotel'); ?>
											</a>
										</div>
									</article>
								<?php endwhile; ?>
							</div>
						</div>
						</div>
			<?php
						wp_reset_postdata();
					endif;
				}
				
				// If comments are open or we have at least one comment, load up the comment template.
				if (comments_open() || get_comments_number()) :
					comments_template();
				endif;

			endwhile; // End of the loop.
			?>

		</main><!-- #main -->

		<?php get_sidebar(); ?>
	</div><!-- .container -->
</div><!-- .content-area -->

<?php
get_footer();
