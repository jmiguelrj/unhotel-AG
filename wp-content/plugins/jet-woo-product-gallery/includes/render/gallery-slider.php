<?php
/**
 * JetGallery Slider widget views manager.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Gallery_Slider' ) ) {

	/**
	 * Define Jet_Gallery_Slider class.
	 */
	class Jet_Gallery_Slider extends Jet_Gallery_Render_Base {

		public function get_name() {
			return 'jet-woo-product-gallery-slider';
		}

		public function default_settings() {

			$default_settings = [
				'image_size'                                 => 'thumbnail',
				'thumbs_image_size'                          => 'thumbnail',
				'slider_enable_infinite_loop'                => false,
				'slider_equal_slides_height'                 => false,
				'slider_sensitivity'                         => 0.8,
				'slider_enable_center_mode'                  => false,
				'slider_center_mode_slides'                  => 4,
				'slider_center_mode_slides_tablet'           => 3,
				'slider_center_mode_slides_mobile'           => 2,
				'slider_center_mode_space_between'           => 10,
				'slider_center_mode_space_between_tablet'    => 10,
				'slider_center_mode_space_between_mobile'    => 10,
				'slider_transition_effect'                   => 'slide',
				'slider_show_nav'                            => true,
				'slider_nav_arrow_prev'                      => [],
				'slider_nav_arrow_next'                      => [],
				'slider_show_pagination'                     => true,
				'slider_pagination_type'                     => 'bullets',
				'slider_pagination_controller_type'          => 'bullets',
				'slider_pagination_bullets_dynamic'          => false,
				'pagination_thumbnails_columns'              => 4,
				'pagination_thumbnails_columns_tablet'       => 3,
				'pagination_thumbnails_columns_mobile'       => 2,
				'pagination_thumbnails_space_between'        => 10,
				'pagination_thumbnails_space_between_tablet' => 10,
				'pagination_thumbnails_space_between_mobile' => 10,
				'slider_pagination_direction'                => 'horizontal',
				'slider_pagination_v_position'               => 'start',
				'slider_pagination_h_position'               => 'bottom',
				'slider_show_thumb_nav'                      => false,
				'pagination_thumbnails_slider_arrow_prev'    => [],
				'pagination_thumbnails_slider_arrow_next'    => [],
			];

			return array_merge( parent::default_settings(), $default_settings );

		}

		public function render() {
			jet_woo_product_gallery_assets()->enqueue_scripts();
			$this->get_render_gallery_content();
		}

		/**
		 * Slider settings.
		 *
		 * Returns swiper slider setting options data.
		 *
		 * @since  2.0.0
		 * @since  2.1.7 Updated settings data output.
		 * @access public
		 *
		 * @return array
		 */
		public function get_slider_data_settings() {

			$settings = $this->get_settings();

			$slider_settings = [
				'slider_enable_infinite_loop'                => filter_var( $settings['slider_enable_infinite_loop'], FILTER_VALIDATE_BOOLEAN ),
				'slider_equal_slides_height'                 => ! filter_var( $settings['slider_equal_slides_height'], FILTER_VALIDATE_BOOLEAN ),
				'slider_sensitivity'                         => ! empty( $settings['slider_sensitivity'] ) ? $settings['slider_sensitivity'] : 1,
				'slider_enable_center_mode'                  => filter_var( $settings['slider_enable_center_mode'], FILTER_VALIDATE_BOOLEAN ),
				'slider_transition_effect'                   => $settings['slider_transition_effect'],
				'show_navigation'                            => filter_var( $settings['slider_show_nav'], FILTER_VALIDATE_BOOLEAN ),
				'show_pagination'                            => filter_var( $settings['slider_show_pagination'], FILTER_VALIDATE_BOOLEAN ),
				'pagination_type'                            => $settings['slider_pagination_type'],
				'pagination_controller_type'                 => $settings['slider_pagination_controller_type'],
				'pagination_dynamic_bullets'                 => filter_var( $settings['slider_pagination_bullets_dynamic'], FILTER_VALIDATE_BOOLEAN ),
				'pagination_direction'                       => $settings['slider_pagination_direction'],
				'slider_center_mode_slides'                  => $settings['slider_center_mode_slides'] ?? 4,
				'slider_center_mode_slides_tablet'           => $settings['slider_center_mode_slides_tablet'] ?? 3,
				'slider_center_mode_slides_mobile'           => $settings['slider_center_mode_slides_mobile'] ?? 2,
				'slider_center_mode_space_between'           => $settings['slider_center_mode_space_between'] ?? 10,
				'slider_center_mode_space_between_tablet'    => $settings['slider_center_mode_space_between_tablet'] ?? 10,
				'slider_center_mode_space_between_mobile'    => $settings['slider_center_mode_space_between_mobile'] ?? 10,
				'show_thumbnails_navigation'                 => filter_var( $settings['slider_show_thumb_nav'], FILTER_VALIDATE_BOOLEAN ),
				'pagination_thumbnails_columns'              => $settings['pagination_thumbnails_columns'] ?? 4,
				'pagination_thumbnails_columns_tablet'       => $settings['pagination_thumbnails_columns_tablet'] ?? 3,
				'pagination_thumbnails_columns_mobile'       => $settings['pagination_thumbnails_columns_mobile'] ?? 2,
				'pagination_thumbnails_space_between'        => $settings['pagination_thumbnails_space_between'] ?? 10,
				'pagination_thumbnails_space_between_tablet' => $settings['pagination_thumbnails_space_between_tablet'] ?? 10,
				'pagination_thumbnails_space_between_mobile' => $settings['pagination_thumbnails_space_between_mobile'] ?? 10,
			];

			$slider_settings = apply_filters( 'jet-woo-product-gallery/slider/pre-options', $slider_settings, $settings );

			$options = [
				'autoHeight'               => $slider_settings['slider_equal_slides_height'],
				'centeredSlides'           => 'horizontal' === $slider_settings['pagination_direction'] ? $slider_settings['slider_enable_center_mode'] : false,
				'direction'                => $slider_settings['pagination_direction'],
				'effect'                   => ! $slider_settings['slider_enable_center_mode'] ? $slider_settings['slider_transition_effect'] : 'slide',
				'longSwipesRatio'          => $slider_settings['slider_sensitivity'],
				'showNavigation'           => $slider_settings['show_navigation'],
				'showPagination'           => $slider_settings['show_pagination'],
				'loop'                     => $slider_settings['slider_enable_infinite_loop'],
				'paginationControllerType' => $slider_settings['pagination_controller_type'],
				'paginationType'           => $slider_settings['pagination_type'],
				'dynamicBullets'           => $slider_settings['pagination_dynamic_bullets'],
			];

			if ( $options['centeredSlides'] ) {
				$options['breakpoints'] = [
					0    => [
						'slidesPerView' => $slider_settings['slider_center_mode_slides_mobile'],
						'spaceBetween'  => $slider_settings['slider_center_mode_space_between_mobile'],
					],
					768  => [
						'slidesPerView' => $slider_settings['slider_center_mode_slides_tablet'],
						'spaceBetween'  => $slider_settings['slider_center_mode_space_between_tablet'],
					],
					1025 => [
						'slidesPerView' => $slider_settings['slider_center_mode_slides'],
						'spaceBetween'  => $slider_settings['slider_center_mode_space_between'],
					],
				];
			}

			$thumb_options = [];

			if ( $slider_settings['show_pagination'] && 'thumbnails' === $slider_settings['pagination_type'] ) {
				$thumb_options = [
					'direction'      => $slider_settings['pagination_direction'],
					'showNavigation' => $slider_settings['show_thumbnails_navigation'],
					'breakpoints'    => [
						0    => [
							'slidesPerView' => $slider_settings['pagination_thumbnails_columns_mobile'],
							'spaceBetween'  => $slider_settings['pagination_thumbnails_space_between_mobile'],
						],
						768  => [
							'slidesPerView' => $slider_settings['pagination_thumbnails_columns_tablet'],
							'spaceBetween'  => $slider_settings['pagination_thumbnails_space_between_tablet'],
						],
						1025 => [
							'slidesPerView' => $slider_settings['pagination_thumbnails_columns'],
							'spaceBetween'  => $slider_settings['pagination_thumbnails_space_between'],
						],
					],
				];
			}

			$options       = apply_filters( 'jet-woo-product-gallery/slider/options', $options, $settings );
			$thumb_options = apply_filters( 'jet-woo-product-gallery/slider/thumb-options', $thumb_options, $settings );

			return [
				'data-swiper-settings'       => htmlspecialchars( json_encode( $options ) ),
				'data-swiper-thumb-settings' => htmlspecialchars( json_encode( $thumb_options ) ),
				'dir'                        => is_rtl() ? 'rtl' : 'ltr',
			];

		}

		/**
		 * Get slider navigation.
		 *
		 * Returns swiper slider navigation arrows.
		 *
		 * @since  2.1.0
		 * @since  2.1.9 Added `$main` param that point on main slider navigation.
		 * @access public
		 *
		 * @param string $prev_arrow Previous arrow name key.
		 * @param string $next_arrow Next arrow name key.
		 * @param bool   $main       Is main slider navigation.
		 *
		 * @return string|null
		 */
		public function get_slider_navigation( $prev_arrow = '', $next_arrow = '', $main = true ) {

			$nav_prev_icon = $this->render_icon( $prev_arrow, '%s', '', false );
			$nav_next_icon = $this->render_icon( $next_arrow, '%s', '', false );

			$swiper_prev_arrow = $this->get_slider_arrow( [ 'jet-swiper-nav', 'jet-swiper-button-prev' ], $nav_prev_icon, $main );
			$swiper_next_arrow = $this->get_slider_arrow( [ 'jet-swiper-nav', 'jet-swiper-button-next' ], $nav_next_icon, $main );

			return $swiper_prev_arrow . $swiper_next_arrow;

		}

	}

}
