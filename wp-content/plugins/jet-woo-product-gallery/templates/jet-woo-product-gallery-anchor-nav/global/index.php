<?php
/**
 * JetGallery Anchor template.
 */

$enable_gallery            = filter_var( $settings['enable_gallery'], FILTER_VALIDATE_BOOLEAN );
$gallery_trigger           = $settings['gallery_trigger_type'];
$zoom_class                = filter_var( $settings['enable_zoom'], FILTER_VALIDATE_BOOLEAN ) ? ' jet-woo-product-gallery__image--with-zoom' : '';
$video_type                = jet_woo_gallery_video_integration()->get_video_type( $settings );
$video                     = $this->get_video_html();
$video_display_type        = $settings['video_display_in'];
$first_place_video         = 'content' === $video_display_type ? filter_var( $settings['first_place_video'], FILTER_VALIDATE_BOOLEAN ) : false;
$navigation_type           = $settings['navigation_type'];
$wrapper_classes           = $this->get_wrapper_classes( [ 'jet-woo-product-gallery-anchor-nav' ], $settings );
$anchor_nav_controller_ids = [];

if ( isset( $settings['navigation_controller_position'] ) ) {
	$wrapper_classes[] = 'jet-woo-product-gallery-anchor-nav-controller-' . $settings['navigation_controller_position'];
}

if ( isset( $settings['navigation_position'] ) ) {
	$wrapper_classes[] = 'navigation-position-' . $settings['navigation_position'];
}

if ( ! $with_featured_image && $first_place_video || $with_featured_image ) {
	$anchor_nav_controller_ids = [ $this->get_unique_controller_id() ];
}
?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>" data-featured-image="<?php echo esc_attr( $with_featured_image ); ?>">
	<div class="jet-woo-product-gallery-anchor-nav-items">
		<?php
		if ( 'content' === $video_display_type && $this->gallery_has_video() && $first_place_video ) {
			include $this->get_global_template( 'video' );
		}

		if ( $with_featured_image ) {
			if ( has_post_thumbnail( $post_id ) ) {
				include $this->get_global_template( 'image' );
			} else {
				if ( $this->gallery_has_video() && $first_place_video ) {
					array_push( $anchor_nav_controller_ids, esc_attr( $this->get_unique_controller_id() ) );
				}

				printf(
					'<div class="jet-woo-product-gallery__image-item featured no-image" id="%s"><div class="jet-woo-product-gallery__image image-with-placeholder"><img src="%s" alt="%s" class="wp-post-image"></div></div>',
					esc_attr( $this->gallery_has_video() && $first_place_video ? $anchor_nav_controller_ids[1] : $anchor_nav_controller_ids[0] ),
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

		if ( 'content' === $video_display_type && $this->gallery_has_video() && ! $first_place_video ) {
			include $this->get_global_template( 'video' );
			array_push( $anchor_nav_controller_ids, esc_attr( $anchor_nav_controller_id ) );
		}
		?>
	</div>

	<?php
	if ( isset( $navigation_type ) && 'thumbnails' === $navigation_type ) {
		include $this->get_global_template( 'thumbnails-controller' );
	} else {
		include $this->get_global_template( 'controller' );
	}
	?>

</div>