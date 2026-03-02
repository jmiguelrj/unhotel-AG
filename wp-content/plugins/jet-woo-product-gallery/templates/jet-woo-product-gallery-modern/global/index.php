<?php
/**
 * JetGallery Modern template.
 */

$enable_gallery    = filter_var( $settings['enable_gallery'], FILTER_VALIDATE_BOOLEAN );
$gallery_trigger   = $settings['gallery_trigger_type'];
$zoom_class        = filter_var( $settings['enable_zoom'], FILTER_VALIDATE_BOOLEAN ) ? ' jet-woo-product-gallery__image--with-zoom' : '';
$video_type        = jet_woo_gallery_video_integration()->get_video_type( $settings );
$video             = $this->get_video_html();
$first_place_video = filter_var( $settings['first_place_video'], FILTER_VALIDATE_BOOLEAN );
$wrapper_classes   = $this->get_wrapper_classes( [ 'jet-woo-product-gallery__content' ], $settings );
?>
<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>" data-featured-image="<?php echo esc_attr( $with_featured_image ); ?>">
	<div class="jet-woo-product-gallery-modern">
		<?php
		if ( 'content' === $settings['video_display_in'] && $first_place_video ) {
			include $this->get_global_template( 'video' );
		}

		if ( $with_featured_image ) {
			if ( has_post_thumbnail( $post_id ) ) {
				include $this->get_global_template( 'image' );
			} else {
				printf(
					'<div class="jet-woo-product-gallery__image-item featured no-image"><div class="jet-woo-product-gallery__image image-with-placeholder"><img src="%s" alt="%s" class="wp-post-image"></div></div>',
					esc_url( $this->get_featured_image_placeholder() ),
					esc_attr__( 'Placeholder', 'jet-woo-product-gallery' ),
				);
			}
		}

		if ( $attachment_ids ) {
			foreach ( $attachment_ids as $attachment_id ) {
				include $this->get_global_template( 'thumbnails' );
			}
		}

		if ( 'content' === $settings['video_display_in'] && ! $first_place_video ) {
			include $this->get_global_template( 'video' );
		}
		?>
	</div>

	<?php if ( 'popup' === $settings['video_display_in'] ) {
		include $this->get_global_template( 'popup-video' );
	} ?>
</div>