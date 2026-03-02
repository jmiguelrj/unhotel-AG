<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @package Unhotel
 */

get_header();
?>

<div class="content-area" style="min-height: 60vh; display: flex; align-items: center; justify-content: center; background: #f8f9fa;">
	<div class="container" style="text-align: center; max-width: 700px;">
		<main id="primary" class="site-main">
			<section class="error-404 not-found">
				<header class="page-header">
					<h1 class="page-title" style="font-size: 2.2rem; font-weight: 700; margin-bottom: 1rem; color: #343a40;">
						<?php esc_html_e( 'Oh oh! Page not found.', 'unhotel' ); ?>
					</h1>
				</header>
				<div class="page-content" style="font-size: 1.15rem; color: #555; margin-bottom: 2rem;">
					<p><?php esc_html_e( "We're sorry, but the page you are looking for doesn't exist.", 'unhotel' ); ?></p>
					<p><?php esc_html_e( 'You can search your topic using the box below or return to the homepage.', 'unhotel' ); ?></p>
				</div>
				   <a href="<?php echo esc_url( apply_filters( 'wpml_home_url', home_url( '/' ) ) ); ?>" style="color: #ff5a7a; font-weight: 500; font-size: 1.1rem; text-decoration: none;">
					   <?php esc_html_e( 'Back to homepage', 'unhotel' ); ?>
				   </a>
			</section>
		</main>
	</div>
</div>

<?php
get_footer();

