<?php if( has_tag() ) { ?>
<div class="article-footer block-footer">
    <h3 class="meta-title"><?php esc_html_e('Tags:', 'homey'); ?></h3>
    <?php the_tags( '<ul class="meta-tags list-inline"><li>', '</li><li>', '</li></ul>' ); ?>
</div>
<?php } ?>