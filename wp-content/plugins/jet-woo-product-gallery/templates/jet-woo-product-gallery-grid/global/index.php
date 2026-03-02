<?php
/**
 * JetGallery Grid template.
 */

$enable_gallery    = filter_var( $settings['enable_gallery'], FILTER_VALIDATE_BOOLEAN );
$gallery_trigger   = $settings['gallery_trigger_type'];
$zoom_class        = filter_var( $settings['enable_zoom'], FILTER_VALIDATE_BOOLEAN ) ? ' jet-woo-product-gallery__image--with-zoom' : '';
$video_type        = jet_woo_gallery_video_integration()->get_video_type( $settings );
$video             = $this->get_video_html();
$first_place_video = filter_var( $settings['first_place_video'], FILTER_VALIDATE_BOOLEAN );
$columns           = $this->get_columns_settings( $settings );
$column_classes    = $this->col_classes( $columns );
$wrapper_classes   = $this->get_wrapper_classes( [ 'jet-woo-product-gallery__content' ], $settings );
if ( isset( $settings['primary_image'] ) ) {
	$wrapper_classes[] = 'jet-woo-product-gallery-grid-primary-' . $settings['primary_image'];
}
$video_display_in  = $settings['video_display_in'];
$is_primary_image  = filter_var( $settings['primary_image'], FILTER_VALIDATE_BOOLEAN );
$grid_items_count  = intval( $settings['grid_items_count'] );
$grid_overlay_text = $settings['grid_overlay_text'];

$primary_attachment_id = null;
if ( $is_primary_image && ! $with_featured_image && ! empty( $attachment_ids ) ) {
	$primary_attachment_id = array_shift( $attachment_ids );
}
?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>" data-featured-image="<?php echo esc_attr( $with_featured_image ); ?>">
	<div class="jet-woo-product-gallery-grid <?php echo esc_attr( $column_classes ); ?>">
		<?php if ( $is_primary_image ) : ?>

			<div class="jet-woo-product-gallery__primary-image">
				<?php
				if ( $with_featured_image ) {
					if ( has_post_thumbnail( $post_id ) ) {
						include $this->get_global_template( 'image' );
					} else {
						$this->render_placeholder_image();
					}
				} elseif ( $primary_attachment_id ) {
					$attachment_id = $primary_attachment_id;

					$vars = $this->prepare_thumbnail_data( $attachment_id, 1, 1, 0, true );
					extract( $vars );

					include $this->get_global_template( 'thumbnails' );
				}
				?>
			</div>

			<div class="jet-woo-product-gallery__images-grid">
				<?php include $this->get_global_template( 'grid-layout' ); ?>
			</div>

		<?php else : ?>

			<?php include $this->get_global_template( 'grid-layout' ); ?>

		<?php endif; ?>
	</div>
</div>