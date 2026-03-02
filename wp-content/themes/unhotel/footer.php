<?php
/**
 * The template for displaying the footer
 *
 * @package Unhotel
 */
?>

	</div><!-- #page -->

	<footer id="colophon" class="site-footer">
		<div class="container">
			<?php
			if ( is_active_sidebar( 'footer-1' ) ) {
				?>
				<div class="footer-widgets">
					<?php dynamic_sidebar( 'footer-1' ); ?>
				</div>
				<?php
			}
			?>
			
			<div class="footer-bottom">
				<div class="footer-logo">
					<?php unhotel_display_footer_logo(); ?>
				<div class="footer-description"><?php echo unhotel_get_footer_description(); ?></div>
				</div>
				
				<nav class="footer-navigation">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer',
							'menu_id'        => 'footer-menu',
							'fallback_cb'    => false,
							'depth'          => 1,
						)
					);
					?>
				</nav>
				
				<div class="site-info">
					<p>
						&copy; <?php echo date( 'Y' ); ?> <?php bloginfo( 'name' ); ?>. All rights reserved
					</p>
				</div><!-- .site-info -->
			</div><!-- .footer-bottom -->
		</div><!-- .container -->
	</footer><!-- #colophon -->

<?php wp_footer(); ?>

</body>
</html>

