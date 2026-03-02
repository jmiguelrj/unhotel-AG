<?php
/**
 * JetGallery Anchor Nav video template.
 */

$ratio_classes            = [];
$play_button_html         = $this->get_play_button_html( $settings );
$video_thumbnail_url      = $this->get_video_thumbnail_url();
$overlay_styles           = ! empty( $video_thumbnail_url ) ? 'style="background-image: url(' . $video_thumbnail_url . ');"' : '';
$anchor_nav_controller_id = $first_place_video ? $anchor_nav_controller_ids[0] : $this->get_unique_controller_id();

if ( 'self_hosted' !== $video_type ) {
	$ratio_classes = [
		'jet-woo-product-video-aspect-ratio',
		'jet-woo-product-video-aspect-ratio--' . $settings['aspect_ratio'],
	];
}
?>

<div class="jet-woo-product-gallery__image-item" id="<?php echo esc_attr( $anchor_nav_controller_id ); ?>">
	<div class="jet-woo-product-gallery__image jet-woo-product-gallery--with-video">
		<?php
		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		printf( '<div class="jet-woo-product-video %s">%s</div>', esc_attr( implode( ' ', $ratio_classes ) ), $video );
		printf( '<div class="jet-woo-product-video__overlay" %s>%s</div>', $overlay_styles, $play_button_html );
		// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
	</div>
</div>