<article id="post-<?php the_ID(); ?>" <?php post_class('single-page-article block'); ?>>
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
	</div><!-- .entry-content -->
</article><!-- #post-## -->