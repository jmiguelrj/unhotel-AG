<?php
global $post, $homey_local;
?>
<div class="related-post clearfix">
    <?php if( has_post_thumbnail( $post->ID ) ) { ?>
	<a href="<?php the_permalink(); ?>" class="post-image hover-effect">
        <?php the_post_thumbnail( 'homey-listing-thumb' ); ?>
	</a>
	<?php } ?>
    <div class="post-body">
        <h4 class="title"><?php the_title(); ?></h4>
        <ul class="list-inline">
            <li><i class="fa fa-calendar-o"></i> <?php echo esc_attr( get_the_date() ); ?> </li>
            <li><i class="fa fa-bookmark-o"></i> <?php echo get_the_category_list( _x( ', ', 'Used between list items, there is a space after the comma.', 'homey' ) ); ?></li>
        </ul>
        <p><?php echo homey_get_content(23); ?> <a href="<?php the_permalink(); ?>" class="read"><?php echo esc_attr($homey_local['read_more']); ?></a></p>
    </div>
</div>