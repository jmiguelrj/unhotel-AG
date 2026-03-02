<?php
global $post, $homey_prefix;

if (class_exists( 'RevSlider' )) {
    $revslider_alias = get_post_meta($post->ID, $homey_prefix.'header_revslider', true);
    ?>

    <section class="top-banner-wrap top-banner-sr">    

        <?php putRevSlider($revslider_alias) ?>

        <?php if(homey_banner_search()) { ?>
        <div class="banner-caption <?php homey_banner_search_class(); ?>">
            <?php homey_banner_search_div_start(); ?>

            <?php get_template_part('template-parts/banner/caption'); ?>

            <?php get_template_part ('template-parts/search/banner-'.homey_banner_search_style());  ?>

            <?php homey_banner_search_div_end(); ?>
        </div><!-- banner-caption -->
    	<?php } ?>

    </section><!-- header-parallax -->


<?php
}
?>
