<?php
/**
 * JetGallery Blocks Views Type Base.
 */

use JET_SM\Gutenberg\Controls_Manager;
use JET_SM\Gutenberg\Block_Manager;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Gallery_Blocks_Views_Type_Base' ) ) {

	/**
	 * Define Jet_Gallery_Blocks_Views_Type_Base class.
	 */
	abstract class Jet_Gallery_Blocks_Views_Type_Base {

		protected $namespace = 'jet-gallery/';

		public $block_manager    = null;
		public $controls_manager = null;
		public $css_scheme;

		/**
		 * Constructor for the class.
		 */
		public function __construct() {

			if ( class_exists( 'JET_SM\Gutenberg\Controls_Manager' ) && class_exists( 'JET_SM\Gutenberg\Block_Manager' ) ) {
				$this->css_scheme = $this->get_css_scheme();

				$this->set_style_manager_instance();
				$this->add_style_manager_options();
			}

			$this->register_block_types();

			add_filter( 'jet-gallery/render/wrapper-attrs', [ $this, 'add_block_type_attrs' ], 10, 2 );

		}

		/**
		 * Get block name.
		 *
		 * @since  2.2.0
		 * @access public
		 *
		 * @return string
		 */
		public function get_block_name() {
			return $this->namespace . $this->get_name();
		}

		/**
		 * Add block type attrs.
		 *
		 * Returns block attributes list with specific block type data.
		 *
		 * @since  2.2.0
		 * @access public
		 *
		 * @param $attrs
		 * @param $render_instance
		 *
		 * @return mixed
		 */
		public function add_block_type_attrs( $attrs, $render_instance ) {

			if ( 'blocks' !== $render_instance->get_editor_type() || $this->get_name() !== $render_instance->get_gallery_type() ) {
				return $attrs;
			}

			$attrs['data-is-block'] = $this->get_block_name();
			$attrs['class']         .= ' blocks-jet-woo-product-' . $render_instance->get_gallery_type();

			return $attrs;

		}

		/**
		 * Set style manager class instance.
		 *
		 * @return boolean
		 */
		public function set_style_manager_instance() {

			$block_name = $this->get_block_name();

			$this->block_manager    = Block_Manager::get_instance();
			$this->controls_manager = new Controls_Manager( $block_name );

		}

		/**
		 * Returns blocks CSS selector.
		 *
		 * @param string $el
		 *
		 * @return string
		 */
		public function css_selector( $el = '' ) {
			return sprintf( '{{WRAPPER}} .blocks-jet-woo-product-%s %s', $this->get_name(), $el );
		}

		/**
		 * Add style block options.
		 *
		 * @return boolean
		 */
		public function add_style_manager_options() {
		}

		/**
		 * Register most common gallery images style controls.
		 */
		public function register_common_images_style_controls() {

			$this->controls_manager->add_control(
				[
					'id'           => 'images_bg',
					'type'         => 'color-picker',
					'label'        => __( 'Background Color', 'jet-woo-product-gallery' ),
					'css_selector' => [
						$this->css_selector( $this->css_scheme['images'] ) => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'images_border',
					'type'         => 'border',
					'label'        => __( 'Border', 'jet-woo-product-gallery' ),
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['images'] . ' img' ) => 'border-style: {{STYLE}}; border-width: {{WIDTH}}; border-radius: {{RADIUS}}; border-color: {{COLOR}}',
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'images_padding',
					'type'         => 'dimensions',
					'label'        => __( 'Padding', 'jet-woo-product-gallery' ),
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['images'] . ':not(.jet-woo-product-gallery--with-video)' ) => 'padding: {{TOP}} {{RIGHT}} {{BOTTOM}} {{LEFT}};',
					],
				]
			);

		}

		/**
		 * Register controls for styling Photoswipe Gallery view.
		 */
		public function register_photoswipe_gallery_style_controls() {

			$this->controls_manager->start_section(
				'style_controls',
				[
					'id'        => 'section_photoswipe_gallery_style_controls',
					'title'     => __( 'Photoswipe Gallery', 'jet-woo-product-gallery' ),
					'condition' => [
						'enable_gallery' => true,
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'photoswipe_gallery_bg',
					'label'        => __( 'Background Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'css_selector' => [
						'{{WRAPPER}}-jet-woo-product-gallery .pswp__bg' => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'      => 'photoswipe_gallery_nav_tabs_heading',
					'type'    => 'text',
					'content' => __( 'Photoswipe Controls', 'jet-woo-product-gallery' ),
				]
			);

			$this->controls_manager->start_tabs(
				'style_controls',
				[
					'id' => 'photoswipe_gallery_nav_style_tabs',
				]
			);

			$this->controls_manager->start_tab(
				'style_controls',
				[
					'id'    => 'photoswipe_gallery_nav_normal_style_tab',
					'title' => __( 'Normal', 'jet-woo-product-gallery' ),
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'photoswipe_gallery_nav_bg',
					'label'        => __( 'Background Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'css_selector' => [
						'{{WRAPPER}}-jet-woo-product-gallery .pswp__button::before' => 'background-color: {{VALUE}} !important',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'photoswipe_gallery_nav_border_radius',
					'type'         => 'dimensions',
					'label'        => __( 'Border Radius', 'jet-woo-product-gallery' ),
					'css_selector' => [
						'{{WRAPPER}}-jet-woo-product-gallery .pswp__button::before' => 'border-radius: {{TOP}} {{RIGHT}} {{BOTTOM}} {{LEFT}}; overflow:hidden;',
					],
				]
			);

			$this->controls_manager->end_tab();

			$this->controls_manager->start_tab(
				'style_controls',
				[
					'id'    => 'photoswipe_gallery_nav_hover_style_tab',
					'title' => __( 'Hover', 'jet-woo-product-gallery' ),
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'photoswipe_gallery_nav_bg_hover',
					'label'        => __( 'Background Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'css_selector' => [
						'{{WRAPPER}}-jet-woo-product-gallery .pswp__button:hover::before' => 'background-color: {{VALUE}} !important',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'photoswipe_gallery_nav_border_radius_hover',
					'type'         => 'dimensions',
					'label'        => __( 'Border Radius', 'jet-woo-product-gallery' ),
					'css_selector' => [
						'{{WRAPPER}}-jet-woo-product-gallery .pswp__button:hover::before' => 'border-radius: {{TOP}} {{RIGHT}} {{BOTTOM}} {{LEFT}}; overflow:hidden;',
					],
				]
			);

			$this->controls_manager->end_tab();

			$this->controls_manager->end_tabs();

			$this->controls_manager->end_section();

		}

		/**
		 * Register controls for styling Photoswipe Gallery trigger button.
		 */
		public function register_photoswipe_gallery_button_trigger_style_controls() {

			$this->controls_manager->start_section(
				'style_controls',
				[
					'id'        => 'section_photoswipe_gallery_trigger_button_style_controls',
					'title'     => __( 'Photoswipe Trigger Button', 'jet-woo-product-gallery' ),
					'condition' => [
						'enable_gallery'       => true,
						'gallery_trigger_type' => 'button',
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'photoswipe_gallery_trigger_button_size',
					'type'         => 'range',
					'label'        => __( 'Size', 'jet-woo-product-gallery' ),
					'css_selector' => [
						$this->css_selector( $this->css_scheme['photoswipe-trigger'] ) => 'width: {{VALUE}}px; height: {{VALUE}}px;',
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'photoswipe_gallery_trigger_button_icon_size',
					'type'         => 'range',
					'label'        => __( 'Icon Size', 'jet-woo-product-gallery' ),
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['photoswipe-trigger'] . ' .jet-woo-product-gallery__trigger-icon' ) => 'font-size: {{VALUE}}px;',
					],
				]
			);

			$this->controls_manager->start_tabs(
				'style_controls',
				[
					'id' => 'photoswipe_gallery_trigger_button_style_tabs',
				]
			);

			$this->controls_manager->start_tab(
				'style_controls',
				[
					'id'    => 'photoswipe_gallery_trigger_button_normal_style_tab',
					'title' => __( 'Normal', 'jet-woo-product-gallery' ),
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'photoswipe_gallery_trigger_button_color',
					'label'        => __( 'Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['photoswipe-trigger'] . ' .jet-woo-product-gallery__trigger-icon' ) => 'color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'photoswipe_gallery_trigger_button_bg',
					'type'         => 'color-picker',
					'label'        => __( 'Background Color', 'jet-woo-product-gallery' ),
					'css_selector' => [
						$this->css_selector( $this->css_scheme['photoswipe-trigger'] ) => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->end_tab();

			$this->controls_manager->start_tab(
				'style_controls',
				[
					'id'    => 'photoswipe_gallery_trigger_button_hover_style_tab',
					'title' => __( 'Hover', 'jet-woo-product-gallery' ),
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'photoswipe_gallery_trigger_button_color_hover',
					'label'        => __( 'Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['photoswipe-trigger'] . ':hover .jet-woo-product-gallery__trigger-icon' ) => 'color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'photoswipe_gallery_trigger_button_bg_hover',
					'type'         => 'color-picker',
					'label'        => __( 'Background Color', 'jet-woo-product-gallery' ),
					'css_selector' => [
						$this->css_selector( $this->css_scheme['photoswipe-trigger'] . ':hover' ) => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'photoswipe_gallery_trigger_button_border_color_hover',
					'type'         => 'color-picker',
					'label'        => __( 'Border Color', 'jet-woo-product-gallery' ),
					'css_selector' => [
						$this->css_selector( $this->css_scheme['photoswipe-trigger'] . ':hover' ) => 'border-color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->end_tab();

			$this->controls_manager->end_tabs();

			$this->controls_manager->add_control(
				[
					'id'           => 'photoswipe_gallery_trigger_button_border',
					'type'         => 'border',
					'label'        => __( 'Border', 'jet-woo-product-gallery' ),
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['photoswipe-trigger'] ) => 'border-style: {{STYLE}}; border-width: {{WIDTH}}; border-radius: {{RADIUS}}; border-color: {{COLOR}}',
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'photoswipe_gallery_trigger_button_margin',
					'type'         => 'dimensions',
					'label'        => __( 'Margin', 'jet-woo-product-gallery' ),
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['photoswipe-trigger'] ) => 'margin: {{TOP}} {{RIGHT}} {{BOTTOM}} {{LEFT}};',
					],
				]
			);

			$this->controls_manager->end_section();

		}

		/**
		 * Register controls for styling video.
		 */
		public function register_video_style_controls() {

			$this->controls_manager->start_section(
				'style_controls',
				[
					'id'           => 'section_video_style_controls',
					'initial_open' => false,
					'title'        => __( 'Video', 'jet-woo-product-gallery' ),
					'condition'    => [
						'enable_video' => true,
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'video_overlay_color',
					'label'        => __( 'Overlay Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['video-overlay'] . ':before' ) => 'background-color: {{VALUE}}',
						$this->css_selector( $this->css_scheme['video-popup-overlay'] )       => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->end_section();

		}

		/**
		 * Register controls for styling play button in content type video.
		 */
		public function register_video_play_button_style_controls() {

			$this->controls_manager->start_section(
				'style_controls',
				[
					'id'        => 'section_video_play_button_style_controls',
					'title'     => __( 'Video Play Button', 'jet-woo-product-gallery' ),
					'condition' => [
						'enable_video'     => true,
						'video_display_in' => 'content',
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'video_play_button_icon_size',
					'type'         => 'range',
					'label'        => __( 'Size', 'jet-woo-product-gallery' ),
					'css_selector' => [
						$this->css_selector( $this->css_scheme['video-play-button'] ) => 'font-size: {{VALUE}}px;',
					],
				]
			);

			$this->controls_manager->start_tabs(
				'style_controls',
				[
					'id' => 'video_play_button_style_tabs',
				]
			);

			$this->controls_manager->start_tab(
				'style_controls',
				[
					'id'    => 'video_play_button_normal_style_tab',
					'title' => __( 'Normal', 'jet-woo-product-gallery' ),
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'video_play_button_color',
					'label'        => __( 'Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['video-play-button'] ) => 'color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'video_play_button_bg',
					'label'        => __( 'Background Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['video-play-button'] ) => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->end_tab();

			$this->controls_manager->start_tab(
				'style_controls',
				[
					'id'    => 'video_play_button_hover_style_tab',
					'title' => __( 'Hover', 'jet-woo-product-gallery' ),
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'video_play_button_color_hover',
					'label'        => __( 'Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['video-overlay'] . ':hover ' . $this->css_scheme['video-play-button'] ) => 'color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'video_play_button_bg_hover',
					'label'        => __( 'Background Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['video-overlay'] . ':hover ' . $this->css_scheme['video-play-button'] ) => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'video_play_button_border_color_hover',
					'label'        => __( 'Border Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['video-overlay'] . ':hover ' . $this->css_scheme['video-play-button'] ) => 'border-color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->end_tab();

			$this->controls_manager->end_tabs();

			$this->controls_manager->add_control(
				[
					'id'           => 'video_play_button_border',
					'label'        => __( 'Border', 'jet-woo-product-gallery' ),
					'type'         => 'border',
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['video-play-button'] ) => 'border-style: {{STYLE}}; border-width: {{WIDTH}}; border-radius: {{RADIUS}}; border-color: {{COLOR}}',
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'video_play_button_margin',
					'type'         => 'dimensions',
					'label'        => __( 'Margin', 'jet-woo-product-gallery' ),
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['video-play-button'] ) => 'margin: {{TOP}} {{RIGHT}} {{BOTTOM}} {{LEFT}};',
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'video_play_button_padding',
					'type'         => 'dimensions',
					'label'        => __( 'Padding', 'jet-woo-product-gallery' ),
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['video-play-button'] ) => 'padding: {{TOP}} {{RIGHT}} {{BOTTOM}} {{LEFT}};',
					],
				]
			);

			$this->controls_manager->end_section();

		}

		/**
		 * Register controls for styling popup button for video.
		 */
		public function register_video_popup_button_style_controls() {

			$this->controls_manager->start_section(
				'style_controls',
				[
					'id'        => 'section_video_popup_button_style_controls',
					'title'     => __( 'Video Popup Button', 'jet-woo-product-gallery' ),
					'condition' => [
						'enable_video'     => true,
						'video_display_in' => 'popup',
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'video_popup_button_icon_size',
					'type'         => 'range',
					'label'        => __( 'Size', 'jet-woo-product-gallery' ),
					'css_selector' => [
						$this->css_selector( $this->css_scheme['video-popup-button'] . ' .jet-woo-product-video__popup-button-icon' ) => 'font-size: {{VALUE}}px;',
					],
				]
			);

			$this->controls_manager->start_tabs(
				'style_controls',
				[
					'id' => 'video_popup_button_style_tabs',
				]
			);

			$this->controls_manager->start_tab(
				'style_controls',
				[
					'id'    => 'video_popup_button_normal_style_tab',
					'title' => __( 'Normal', 'jet-woo-product-gallery' ),
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'video_popup_button_color',
					'label'        => __( 'Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['video-popup-button'] . ' .jet-woo-product-video__popup-button-icon' ) => 'color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'video_popup_button_bg',
					'type'         => 'color-picker',
					'label'        => __( 'Background Color', 'jet-woo-product-gallery' ),
					'css_selector' => [
						$this->css_selector( $this->css_scheme['video-popup-button'] ) => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->end_tab();

			$this->controls_manager->start_tab(
				'style_controls',
				[
					'id'    => 'video_popup_button_hover_style_tab',
					'title' => __( 'Hover', 'jet-woo-product-gallery' ),
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'video_popup_button_color_hover',
					'label'        => __( 'Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['video-popup-button'] . ':hover .jet-woo-product-video__popup-button-icon' ) => 'color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'video_popup_button_bg_hover',
					'type'         => 'color-picker',
					'label'        => __( 'Background Color', 'jet-woo-product-gallery' ),
					'css_selector' => [
						$this->css_selector( $this->css_scheme['video-popup-button'] . ':hover' ) => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'video_popup_button_border_color_hover',
					'type'         => 'color-picker',
					'label'        => __( 'Border Color', 'jet-woo-product-gallery' ),
					'css_selector' => [
						$this->css_selector( $this->css_scheme['video-popup-button'] . ':hover' ) => 'border-color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->end_tab();

			$this->controls_manager->end_tabs();

			$this->controls_manager->add_control(
				[
					'id'           => 'video_popup_button_border',
					'label'        => __( 'Border', 'jet-woo-product-gallery' ),
					'type'         => 'border',
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['video-popup-button'] ) => 'border-style: {{STYLE}}; border-width: {{WIDTH}}; border-radius: {{RADIUS}}; border-color: {{COLOR}}',
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'video_popup_button_margin',
					'label'        => __( 'Margin', 'jet-woo-product-gallery' ),
					'type'         => 'dimensions',
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['video-popup-button'] ) => 'margin: {{TOP}} {{RIGHT}} {{BOTTOM}} {{LEFT}};',
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'video_popup_button_padding',
					'label'        => __( 'Padding', 'jet-woo-product-gallery' ),
					'type'         => 'dimensions',
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['video-popup-button'] ) => 'padding: {{TOP}} {{RIGHT}} {{BOTTOM}} {{LEFT}};',
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'video_popup_button_alignment',
					'type'         => 'choose',
					'label'        => __( 'Alignment', 'jet-woo-product-gallery' ),
					'options'      => [
						'left'   => [
							'shortcut' => __( 'Left', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-editor-alignleft',
						],
						'center' => [
							'shortcut' => __( 'Center', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-editor-aligncenter',
						],
						'right'  => [
							'shortcut' => __( 'Right', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-editor-alignright',
						],
					],
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( ' .jet-woo-product-video__popup-wrapper' ) => 'text-align: {{VALUE}};',
					],
				]
			);

			$this->controls_manager->end_section();

		}

		/**
		 * Register galleries block types.
		 */
		public function register_block_types() {

			$block_path = jet_woo_product_gallery()->plugin_path( 'assets/js/admin/blocks-views/src/blocks/' . $this->get_name() );

			register_block_type(
				$block_path,
				[ 'render_callback' => [ $this, 'render_callback' ] ]
			);

		}

		/**
		 * Call the name of the gallery.
		 *
		 * @return mixed
		 */
		abstract public function get_name();

		/**
		 * Returns css selectors.
		 *
		 * @return array
		 */
		public function get_css_scheme() {
			return [
				'images'              => '.jet-woo-product-gallery__image',
				'photoswipe-trigger'  => '.jet-woo-product-gallery__trigger:not( .jet-woo-product-gallery__image-link )',
				'video-overlay'       => '.jet-woo-product-video__overlay',
				'video-play-button'   => '.jet-woo-product-video__play-button',
				'video-popup-button'  => '.jet-woo-product-video__popup-button',
				'video-popup-overlay' => '.jet-woo-product-video__popup-overlay',
			];
		}

		/**
		 * Check if is blocks edit mode.
		 *
		 * @return boolean
		 */
		public function is_edit_mode() {

			// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only checks.
			$context_valid  = isset( $_GET['context'] ) && 'edit' === sanitize_key( wp_unslash( $_GET['context'] ) );
			$has_attributes = isset( $_GET['attributes'] );
			$locale_valid   = isset( $_GET['_locale'] ) && '' !== sanitize_text_field( wp_unslash( $_GET['_locale'] ) );
			// phpcs:enable

			return ( $context_valid && $has_attributes && $locale_valid );
		}

		/**
		 * Allow to filter raw attributes from block type instance to adjust JS and PHP attributes format.
		 *
		 * @param  $attributes
		 *
		 * @return mixed
		 */
		public function prepare_attributes( $attributes ) {
			return $attributes;
		}

		/**
		 * Returns current render instance.
		 *
		 * @param $attributes
		 *
		 * @return object|void
		 */
		public function get_render_instance( $attributes ) {
			return jet_woo_product_gallery()->base->get_render_instance( $this->get_name(), $attributes, 'blocks' );
		}

		/**
		 * Render gallery blocks type.
		 *
		 * @param array $attributes
		 *
		 * @return void
		 */
		public function render_callback( $attributes = [] ) {

			$attributes = $this->prepare_attributes( $attributes );
			$render     = $this->get_render_instance( $attributes );

			if ( ! $render ) {
				return __( 'Gallery renderer class not found.', 'jet-woo-product-gallery' );
			}

			return $render->get_content();

		}

	}

}