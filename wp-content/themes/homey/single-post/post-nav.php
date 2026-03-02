<div class="next-prev-block next-prev-blog blog-section clearfix">
    <?php
    $prevPost = get_previous_post(true);
    if($prevPost) {
    $args = array(
        'posts_per_page' => 1,
        'include' => $prevPost->ID
    );
    $prevPost = get_posts($args);
    foreach ($prevPost as $post) {
        setup_postdata($post);
        ?>
        <div class="prev-box pull-left text-left">
            <a class="hover-effect" href="<?php the_permalink(); ?>">
                <div class="next-prev-block-content">
                    <p><?php esc_html_e( 'Prev Post', 'homey' ); ?></p>
                    <p><strong><?php the_title(); ?></strong></p>
                </div>
                <?php
                if( has_post_thumbnail( $post->ID ) ) {
                    the_post_thumbnail( 'homey_thumb_555_360' );
                }
                ?>
            </a>
        </div>
        <?php
        wp_reset_postdata();
        } //end foreach
    } // end if
    

    $nextPost = get_next_post(true);
    if($nextPost) {
    $args = array(
        'posts_per_page' => 1,
        'include' => $nextPost->ID
    );
    $nextPost = get_posts($args);
        foreach ($nextPost as $post) {
        setup_postdata($post);
        ?>
        <div class="next-box pull-right text-right">
            <a class="hover-effect" href="<?php the_permalink(); ?>">
                <div class="next-prev-block-content">
                    <p><?php esc_html_e( 'Next post', 'homey' ); ?></p>
                    <p><strong><?php the_title(); ?></strong></p>
                </div>
                <?php
                if( has_post_thumbnail( $post->ID ) ) {
                    the_post_thumbnail( 'homey_thumb_555_360' );
                }
                ?>
            </a>
        </div>
    <?php
    wp_reset_postdata();
        } //end foreach
    } // end if
    ?>
</div>