<?php
/**
 * JetGallery Grid Block Type.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Gallery_Blocks_Views_Type_Grid' ) ) {

	/**
	 * Define Jet_Gallery_Blocks_Views_Type_Grid class.
	 */
	class Jet_Gallery_Blocks_Views_Type_Grid extends Jet_Gallery_Blocks_Views_Type_Base {

		/**
		 * Returns block name.
		 *
		 * @return string
		 */
		public function get_name() {
			return 'gallery-grid';
		}

		public function get_css_scheme() {

			$css_scheme = [
				'row'     => '.jet-woo-product-gallery-grid',
				'columns' => '.jet-woo-product-gallery__image-item',
			];

			return array_merge( parent::get_css_scheme(), $css_scheme );

		}

		/**
		 * Add style block options.
		 *
		 * @return boolean
		 */
		public function add_style_manager_options() {

			// Columns style controls.
			$this->controls_manager->start_section(
				'style_controls',
				[
					'id'           => 'section_columns_style',
					'title'        => __( 'Columns', 'jet-woo-product-gallery' ),
					'initial_open' => true,
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'columns_padding',
					'type'         => 'dimensions',
					'label'        => __( 'Gutter', 'jet-woo-product-gallery' ),
					'responsive' => true,
					'css_selector' => [
						$this->css_selector( $this->css_scheme['columns'] ) => 'padding: {{TOP}} {{RIGHT}} {{BOTTOM}} {{LEFT}};',
						$this->css_selector( $this->css_scheme['row'] )     => 'margin-left: -{{LEFT}}; margin-right: -{{RIGHT}};',
					],
				]
			);

			$this->controls_manager->end_section();

			// Images style controls.
			$this->controls_manager->start_section(
				'style_controls',
				[
					'id'    => 'section_images_style',
					'title' => __( 'Images', 'jet-woo-product-gallery' ),
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'primary_image_width',
					'type'         => 'range',
					'label'        => __( 'Primary Image Width (%)', 'jet-woo-product-gallery' ),
					'separator'    => 'after',
					'responsive'   => true,
					'css_selector' => [
						'.jet-woo-product-gallery-grid-primary-yes .jet-woo-product-gallery-grid .jet-woo-product-gallery__primary-image' => 'max-width: {{VALUE}}%;',
						'.jet-woo-product-gallery-grid-primary-yes .jet-woo-product-gallery-grid .jet-woo-product-gallery__images-grid'   => 'max-width: calc(100% - {{VALUE}}%);',
					],
					'attributes'    => [
						'default' => [
							'value' => [
								'value' => 50,
								'unit'  => '%',
							]
						]
					],
					'units'        => [
						[
							'value'     => '%',
							'intervals' => [
								'step' => 1,
								'min' => 0,
								'max' => 100,
							]
						],
					],
				]
			);

			// Common images controls.
			$this->register_common_images_style_controls();

			$this->controls_manager->end_section();

			$this->controls_manager->start_section(
				'style_controls',
				[
					'id'    => 'section_overlay_style',
					'title' => __( 'Overlay', 'jet-woo-product-gallery' ),
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'overlay_typography',
					'label'        => __( 'Typography', 'jet-woo-product-gallery' ),
					'type'         => 'typography',
					'separator'    => 'after',
					'attributes'    => [
						'default' => [
							'value' => [
								'size' => 16,
								's_unit' => 'px',
								'weight' => '500',
							]
						]
					],
					'css_selector' => [
						'.jet-woo-product-gallery-grid .jet-woo-product-gallery__image-item a.jet-woo-product-gallery__image-overlay' =>
							'font-family: {{FAMILY}}; font-weight: {{WEIGHT}}; text-transform: {{TRANSFORM}}; font-style: {{STYLE}}; text-decoration: {{DECORATION}}; line-height: {{LINEHEIGHT}}{{LH_UNIT}}; letter-spacing: {{LETTERSPACING}}{{LS_UNIT}}; font-size: {{SIZE}}{{S_UNIT}};',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'overlay_text_color',
					'type'         => 'color-picker',
					'label'        => __( 'Color', 'jet-woo-product-gallery' ),
					'attributes'    => [
						'default' => [
							'value' => '#ffffff',
						]
					],
					'css_selector' => [
						$this->css_selector( '.jet-woo-product-gallery__image-overlay' ) => 'color: {{VALUE}};',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'overlay_text_color_hover',
					'type'         => 'color-picker',
					'label'        => __( 'Hover Color', 'jet-woo-product-gallery' ),
					'attributes'    => [
						'default' => [
							'value' => '#000000',
						]
					],
					'separator'    => 'after',
					'css_selector' => [
						$this->css_selector( '.jet-woo-product-gallery__image-overlay:hover' ) => 'color: {{VALUE}};',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'overlay_background',
					'type'         => 'color-picker',
					'label'        => __( 'Background Color', 'jet-woo-product-gallery' ),
					'attributes'    => [
						'default' => [
							'value' => '#9D9D9D',
						]
					],
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( '.jet-woo-product-gallery__image-overlay' ) => 'background-color: {{VALUE}};',
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'overlay_padding',
					'type'         => 'dimensions',
					'label'        => __( 'Padding', 'jet-woo-product-gallery' ),
					'units'        => [ 'px', 'em', '%' ],
					'responsive' => true,
					'css_selector' => [
						$this->css_selector( '.jet-woo-product-gallery__image-overlay' ) => 'padding: {{TOP}} {{RIGHT}} {{BOTTOM}} {{LEFT}};',
					],
				]
			);

			$this->controls_manager->end_section();

			// Photoswipe Gallery trigger button style controls.
			$this->register_photoswipe_gallery_button_trigger_style_controls();

			// Photoswipe Gallery view style controls.
			$this->register_photoswipe_gallery_style_controls();

			// Video style controls.
			$this->register_video_style_controls();

			// Video play button style controls.
			$this->register_video_play_button_style_controls();

			// Video popup button style controls.
			$this->register_video_popup_button_style_controls();

		}

	}

}