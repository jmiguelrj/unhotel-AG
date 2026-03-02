<?php
/**
 * Used for both single and index/archive/search.
 *
 */
global $post, $homey_local;
$blog_date = '1';
$blog_author = '1';
$author = homey_get_author('48', '48', 'img-circle');
?>
<article id="post-<?php the_ID(); ?>" <?php post_class('blog-article block'); ?>>

	<?php if( has_post_thumbnail( $post->ID ) ) { ?>
	<a href="<?php the_permalink(); ?>">
        <?php the_post_thumbnail( 'homey-gallery' ); ?>
	</a>
	<?php } ?>
	
	<div class="block-body">
		<?php if(get_the_title()) { ?>
			<h2>
			<a href="<?php the_permalink(); ?>">
			<?php the_title(); ?>
			</a>
			</h2>
		<?php } ?>
		<?php the_excerpt(); ?>
	</div>
	<div class="block-footer clearfix">

		<ul class="article-meta list-inline pull-left">
			<li class="post-author">
				<?php echo ''.$author['photo']; ?>
				<?php echo esc_attr($homey_local['by_text_sm']); ?> <a href="<?php echo esc_url( $author['link'] ); ?>"><?php echo esc_attr( $author['name'] ); ?></a>
			</li>
			<li class="post-date"><i class="fa fa-calendar-o"></i> <?php echo esc_attr( get_the_date() ); ?> </li>
			<li class="post-category"><i class="fa fa-bookmark-o"></i> <?php echo get_the_category_list( _x( ', ', 'Used between list items, there is a space after the comma.', 'homey' ) ); ?></li>
		</ul>

		<a href="<?php the_permalink(); ?>" class="btn btn-primary btn-sm-full-width pull-right"><?php echo esc_attr($homey_local['read_more']); ?></a>
	</div>
</article>
