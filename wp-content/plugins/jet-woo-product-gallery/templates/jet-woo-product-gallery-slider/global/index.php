<?php
/**
 * JetGallery Slider template.
 */

$enable_gallery          = filter_var( $settings['enable_gallery'], FILTER_VALIDATE_BOOLEAN );
$gallery_trigger         = $settings['gallery_trigger_type'];
$zoom_class              = filter_var( $settings['enable_zoom'], FILTER_VALIDATE_BOOLEAN ) ? ' jet-woo-product-gallery__image--with-zoom' : '';
$video_type              = jet_woo_gallery_video_integration()->get_video_type( $settings );
$video                   = $this->get_video_html();
$video_display_type      = $settings['video_display_in'];
$first_place_video       = filter_var( $settings['first_place_video'], FILTER_VALIDATE_BOOLEAN );
$pagination              = filter_var( $settings['slider_show_pagination'], FILTER_VALIDATE_BOOLEAN );
$pagination_type         = $settings['slider_pagination_type'];
$pagination_control_type = $settings['slider_pagination_controller_type'];
$wrapper_classes         = $this->get_wrapper_classes( [ 'jet-woo-product-gallery__content' ], $settings );
$swiper_wrapper_classes  = [ 'jet-woo-swiper' ];
$slider_html_attrs       = $this->get_slider_data_settings();

if ( $pagination ) {
	$pagination_direction = $settings['slider_pagination_direction'];
	$pagination_position  = 'vertical' === $pagination_direction ? $settings['slider_pagination_v_position'] : $settings['slider_pagination_h_position'];

	array_push(
		$swiper_wrapper_classes,
		'jet-woo-swiper-' . $pagination_direction,
		'jet-gallery-swiper-' . $pagination_direction . '-pos-' . $pagination_position
	);
}
?>
<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>" data-featured-image="<?php echo esc_attr( $with_featured_image ); ?>">
	<div class="<?php echo esc_attr( implode( ' ', $swiper_wrapper_classes ) ); ?>">
		<div class="jet-gallery-swiper-slider">
			<div class="jet-woo-product-gallery-slider swiper-container"
				<?php 
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- attributes are properly escaped inside implode_html_attributes().
				echo jet_woo_product_gallery_tools()->implode_html_attributes( $slider_html_attrs );
				?> >
				<div class="swiper-wrapper">
					<?php
					if ( 'content' === $video_display_type && $first_place_video ) {
						include $this->get_global_template( 'video' );
					}

					if ( $with_featured_image ) {
						if ( has_post_thumbnail( $post_id ) ) {
							include $this->get_global_template( 'image' );
						} else {
							printf(
								'<div class="jet-woo-product-gallery__image-item featured no-image swiper-slide"><div class="jet-woo-product-gallery__image image-with-placeholder"><img src="%s" alt="%s" class="wp-post-image"></div></div>',
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

					if ( 'content' === $video_display_type && ! $first_place_video ) {
						include $this->get_global_template( 'video' );
					}
					?>
				</div>

				<?php
				if ( filter_var( $settings['slider_show_nav'], FILTER_VALIDATE_BOOLEAN ) ) {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- properly escaped inside get_slider_navigation().
					echo $this->get_slider_navigation( 'slider_nav_arrow_prev', 'slider_nav_arrow_next' );
				}
				?>

				<?php if ( $pagination && 'thumbnails' !== $pagination_type ) : ?>
					<?php if ( 'progressbar' === $pagination_control_type ) : ?>
						<div class="swiper-pagination swiper-pagination-progressbar">
							<span class="swiper-pagination-progressbar-fill placeholder"></span>
						</div>
					<?php elseif ( 'fraction' === $pagination_control_type ) : ?>
						<div class="swiper-pagination swiper-pagination-fraction">
							<span class="swiper-pagination-current placeholder">1</span>/
							<span class="swiper-pagination-total placeholder">3</span>
						</div>
					<?php else : ?>
						<div class="swiper-pagination swiper-pagination-bullets">
							<span class="swiper-pagination-bullet swiper-pagination-bullet-active placeholder"></span>
							<span class="swiper-pagination-bullet placeholder"></span>
							<span class="swiper-pagination-bullet placeholder"></span>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>

		<?php
		if ( $pagination && 'thumbnails' === $pagination_type ) {
			if ( count( $attachment_ids ) > 1 || $with_featured_image && ! empty( $attachment_ids ) || $with_featured_image && $this->gallery_has_video() || ! empty( $attachment_ids ) && $this->gallery_has_video() ) {
				include $this->get_global_template( 'thumbnails-pagination' );
			}
		}
		?>

	</div>

	<?php
	if ( 'popup' === $video_display_type ) {
		include $this->get_global_template( 'popup-video' );
	}
	?>

</div>
