<?php
/**
 * JetGallery Anchor Navigation thumbnails template.
 */

if ( $with_featured_image && has_post_thumbnail( $post_id ) ) {
	array_unshift( $attachment_ids, intval( get_post_thumbnail_id( $post_id ) ) );
}

$thumbs_video_placeholder_html = '';
$thumbs_html                   = '';

if ( $this->gallery_has_video() && 'content' === $video_display_type ) {
	if ( $this->video_has_custom_placeholder( $settings ) ) {
		$video_placeholder_id = jet_woo_gallery_video_integration()->get_video_custom_placeholder( $settings );	
		if ( $first_place_video ) {
			array_unshift( $attachment_ids, $video_placeholder_id );
		} else {
			$attachment_ids[] = $video_placeholder_id;
		}
	} else {
		$video_placeholder_id = 'default_video_placeholder';
		if ( $first_place_video ) {
			array_unshift( $attachment_ids, $video_placeholder_id );
		} else {
			$attachment_ids[] = $video_placeholder_id;
		}
	}
}

if ( $with_featured_image && ! has_post_thumbnail( $post_id ) ) {
	$thumbs_html .= sprintf(
		'<div class="jet-woo-product-gallery__image-item featured no-image swiper-slide"><div class="jet-woo-product-gallery__image image-with-placeholder"><img src="%s" alt="%s" class="wp-post-image"></div></div>',
		esc_url( $this->get_featured_image_placeholder() ),
		esc_attr__( 'Placeholder', 'jet-woo-product-gallery' )
	);
}

foreach ( $attachment_ids as $index => $attachment_id ) {
	$anchor_id = isset( $anchor_nav_controller_ids[ $index ] ) ? $anchor_nav_controller_ids[ $index ] : $index;
	if ( $attachment_id === 'default_video_placeholder' ) {
		$thumbs_html .= sprintf(
			'<li class="controller-item">
				<a href="#%2$s" data-index="%2$s" data-role="gallery-controller">
					<span class="controller-item__thumbnail"><img src="%1$s"></span>
				</a>
			</li>',
			esc_url( jet_woo_product_gallery()->plugin_url( 'assets/images/video-thumbnails-placeholder.png' ) ),
			esc_attr( $anchor_id )
		);
		continue;
	}

	$image_src = wp_get_attachment_image_src( $attachment_id, 'full' );
	$image     = $this->get_gallery_image( $attachment_id, $settings['thumbs_image_size'], $image_src, false );

	$thumbs_html .= sprintf(
		'<li class="controller-item">
			<a href="#%1$s" data-index="%1$s" data-role="gallery-controller">
				<span class="controller-item__thumbnail">%2$s</span>
			</a>
		</li>',
		esc_attr( $anchor_id ),
		$image
	);
}

if ( isset( $thumbs_video_placeholder_html ) && ! $first_place_video ) {
	$thumbs_html .= $thumbs_video_placeholder_html;
}
?>

<ul class="jet-woo-product-gallery-anchor-nav-controller">
	<?php
		// phpcs:ignore
		echo $thumbs_html;

		if ( 'popup' === $settings['video_display_in'] && $this->gallery_has_video() ) {
			include $this->get_global_template( 'popup-video' );
		}
	?>
</ul>