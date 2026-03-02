<?php
/**
 * JetGallery Grid thumbnails template.
 */

$image_src             = wp_get_attachment_image_src( $attachment_id, 'full' );
$image                 = $this->get_gallery_image( $attachment_id, $settings['image_size'], $image_src, false );
$link_attrs            = $this->get_image_link_attrs( $attachment_id );
$thumbnails_zoom_class = ( $last_grid_item && $hidden_count > 0 ) ? '' : $zoom_class;
?>

<div class="jet-woo-product-gallery__image-item<?php echo esc_attr( $hidden_item_class ); ?>">
	<div class="jet-woo-product-gallery__image<?php echo esc_attr( $thumbnails_zoom_class ); ?>">
		<?php
		if ( $enable_gallery && 'button' === $gallery_trigger ) {
			$this->get_gallery_trigger_button( $this->render_icon( 'gallery_button_icon', '%s', '', false ) );
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- attributes are properly escaped inside implode_html_attributes().
		echo '<a ' . jet_woo_product_gallery_tools()->implode_html_attributes( $link_attrs ) . '>';
		echo wp_kses_post( $image );
		if ( $last_grid_item && $hidden_count > 0 ) {
			echo '<a class="jet-woo-product-gallery__image-overlay"><span>+ ' . esc_html( $hidden_count ) . ' ' . esc_html( $grid_overlay_text ) . '</span></a>';
		}
		echo '</a>';
		?>
	</div>
</div>