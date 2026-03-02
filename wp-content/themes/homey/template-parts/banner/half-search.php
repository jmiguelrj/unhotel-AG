<?php 
global $post, $homey_prefix;
$page_id = $post->ID;
$image_id = get_post_meta($post->ID, $homey_prefix.'header_image', true);
$img_url = wp_get_attachment_image_src( $image_id, 'full' );
?>
<section class="top-banner-wrap half-search">    

    <div class="container">
        <div class="row">
            
            <div class="col-sm-6">
                <div class="banner-caption-side-search">
                    <div class="half-search-wrap">

                        <?php get_template_part('template-parts/banner/caption'); ?>

                        <?php get_template_part ('template-parts/search/banner-vertical'); ?>
                    </div>
                </div><!-- banner-caption -->
            </div>
            
            <div class="col-sm-6">
                <div class="half-header-image" style="background-image: url(<?php echo esc_url($img_url[0]); ?>);">

                </div><!-- half-header-image -->
            </div>
        </div>    
    </div>
    
</section><!-- half-search -->