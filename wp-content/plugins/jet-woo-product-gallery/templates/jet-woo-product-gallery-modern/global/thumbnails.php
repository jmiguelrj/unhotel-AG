<?php
/**
 * JetGallery Modern thumbnails template.
 */

$image_src  = wp_get_attachment_image_src( $attachment_id, 'full' );
$image      = $this->get_gallery_image( $attachment_id, $settings['image_size'], $image_src, false );
$link_attrs = $this->get_image_link_attrs( $attachment_id );
?>

<div class="jet-woo-product-gallery__image-item">
	<div class="jet-woo-product-gallery__image<?php echo esc_attr( $zoom_class ); ?>">
		<?php
		if ( $enable_gallery && 'button' === $gallery_trigger ) {
			$this->get_gallery_trigger_button( $this->render_icon( 'gallery_button_icon', '%s', '', false ) );
		}
		
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- attributes are properly escaped inside implode_html_attributes().
		printf( '<a %s>%s</a>', jet_woo_product_gallery_tools()->implode_html_attributes( $link_attrs ), wp_kses_post( $image ) );
		?>
	</div>
</div>