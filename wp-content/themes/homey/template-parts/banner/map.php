<?php
global $post;
$map_data_type = get_post_meta($post->ID, 'homey_map_data_type', true);
$lat = get_post_meta($post->ID, 'homey_map_lat', true);
$long = get_post_meta($post->ID, 'homey_map_long', true);
$zoom_level = homey_option('googlemap_zoom_level');
?>
<section class="top-banner-wrap <?php homey_banner_fullscreen(); ?>">    

    <div id="banner-map" 
    data-zoomlevel="<?php echo intval($zoom_level); ?>" 
    data-maptype="<?php echo esc_attr($map_data_type); ?>"
    data-lat="<?php echo esc_attr($lat); ?>"
    data-long="<?php echo esc_attr($long); ?>"
    >	
    </div>

    <?php get_template_part('template-parts/map-controls'); ?>

</section><!-- header-parallax -->