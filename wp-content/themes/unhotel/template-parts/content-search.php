<?php
/**
 * Template part for displaying results in search pages
 *
 * @package Unhotel
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'post' ); ?>>
	<header class="post-header">
		<?php the_title( sprintf( '<h2 class="post-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>

		<?php if ( 'post' === get_post_type() ) : ?>
			<div class="post-meta">
				<?php
				unhotel_posted_on();
				unhotel_posted_by();
				?>
			</div><!-- .post-meta -->
		<?php endif; ?>
	</header><!-- .post-header -->

	<?php unhotel_post_thumbnail(); ?>

	<div class="post-content">
		<?php the_excerpt(); ?>
	</div><!-- .post-content -->

	<footer class="post-footer">
		<a href="<?php echo esc_url( get_permalink() ); ?>" class="read-more">
			<?php esc_html_e( 'Read More', 'unhotel' ); ?>
		</a>
	</footer><!-- .post-footer -->
</article><!-- #post-<?php the_ID(); ?> -->

