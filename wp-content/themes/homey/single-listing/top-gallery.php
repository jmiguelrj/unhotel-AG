<?php
global $post;
$size = 'homey-gallery';
$thumb_size = 'homey-gallery-thumb2';
$listing_images = rwmb_meta( 'homey_listing_images', 'type=plupload_image&size='.$size, $post->ID );
$thumbs = rwmb_meta( 'homey_listing_images', 'type=plupload_image&size='.$thumb_size, $post->ID );
$i = 0;

if(!empty($listing_images)) {
    ?>
    <div class="top-gallery-section">
        <div class="listing-slider">
            <?php foreach( $listing_images as $image ) { ?>
                <div>
                    <a data-lazy="<?php echo esc_url($image['full_url']);?>" href="<?php echo esc_url($image['full_url']);?>" class="swipebox">
                        <img data-fancy-image-index="<?php echo $i; ?>" class="img-responsive fanboxTopGallery-item" src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>">
                    </a>
                </div>
            <?php $i++; } ?>
        </div>
        <div class="listing-slider-nav">
            <?php foreach( $thumbs as $thumb ) { ?>
                <div>
                    <img class="img-responsive" data-lazy="<?php echo esc_url($thumb['url']); ?>" src="<?php echo esc_url($thumb['url']); ?>" alt="<?php echo esc_attr($thumb['alt']); ?>">
                </div>
            <?php } ?>
        </div>
    </div><!-- top-gallery-section -->
    <?php fancybox_gallery_html($listing_images, 'fanboxTopGallery');//hidden images for gallery fancybox 3 ?>
<?php } ?>