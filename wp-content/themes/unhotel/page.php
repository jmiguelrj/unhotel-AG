<?php

/**
 * The template for displaying all pages
 *
 * @package Unhotel
 */

get_header();
?>
<div id="section-body">
	<section class="main-content-area">
		<div class="content-fluid">
			<div class="content-area">
				<div class="container">
					<main id="primary" class="site-main">
						<?php
						while (have_posts()) :
							the_post();

							get_template_part('template-parts/content', 'page');

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
		</div><!-- .content-area -->
	</section><!-- .main-content-area -->
</div><!-- #body -->
<?php

get_footer();
