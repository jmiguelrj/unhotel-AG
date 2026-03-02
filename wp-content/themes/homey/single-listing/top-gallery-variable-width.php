<?php
global $post;
$size = 'homey-variable-slider';
$listing_images = rwmb_meta( 'homey_listing_images', 'type=plupload_image&size='.$size, $post->ID );
$i = 0;

if(!empty($listing_images)) {
    ?>
    <div class="top-gallery-section top-gallery-variable-width-section">
        <div class="listing-slider-variable-width">

            <?php foreach( $listing_images as  $image ) { ?>
                <div>
                    <a href="<?php echo esc_url($image['full_url']);?>" class="swipebox">
                        <img data-fancy-image-index="<?php echo $i; ?>" class="img-responsive fanboxTopGalleryVar-item" data-lazy="<?php echo esc_url($image['url']); ?>" src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>">
                    </a>
                </div>
            <?php $i++; } ?>
        </div>
    </div><!-- top-gallery-section -->
    <?php fancybox_gallery_html($listing_images, "fanboxTopGalleryVar");//hidden images for gallery fancybox 3 ?>
<?php } ?>