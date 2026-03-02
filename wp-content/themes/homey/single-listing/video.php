
<?php
global $post, $homey_prefix, $homey_local;
$video_url = homey_get_listing_data('video_url');

if(!empty($video_url)) { 
?>
<div id="video-section" class="video-section">
    <div class="block">
        <div class="block-section">
            <div class="block-body">
                <div class="block-left">
                    <h3 class="title"><?php echo esc_attr(homey_option('sn_video_heading')); ?></h3>
                </div><!-- block-left -->
                <div class="block-right">
                    <div class="block-video">
                        <?php echo wp_oembed_get( $video_url ); ?>
                    </div>
                </div><!-- block-right -->
            </div><!-- block-body -->
        </div><!-- block-section -->
    </div><!-- block -->
</div>
<?php } ?>