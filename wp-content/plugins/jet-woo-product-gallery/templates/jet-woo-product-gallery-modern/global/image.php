<?php
/**
 * JetGallery Modern main image template.
 */

$image_id   = get_post_thumbnail_id( $post_id );
$image_src  = wp_get_attachment_image_src( $image_id, 'full' );
$image      = $this->get_gallery_image( $image_id, $settings['image_size'], $image_src, true );
$link_attrs = $this->get_image_link_attrs( $image_id );
?>

<div class="jet-woo-product-gallery__image-item featured">
	<div class="jet-woo-product-gallery__image<?php echo esc_attr( $zoom_class ); ?>">
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- attributes are escaped inside helper;
		printf( '<a %s>%s</a>', jet_woo_product_gallery_tools()->implode_html_attributes( $link_attrs ), wp_kses_post( $image ) );

		if ( $enable_gallery && 'button' === $gallery_trigger ) {
			$this->get_gallery_trigger_button( $this->render_icon( 'gallery_button_icon', '%s', '', false ) );
		}
		?>
	</div>
</div>