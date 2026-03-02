<?php
/**
 * JetGallery Widget Base.
 */

namespace Elementor;

use Elementor\Modules\DynamicTags\Module as TagsModule;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'Jet_Gallery_Widget_Base' ) ) {

	/**
	 * Define Jet_Gallery_Widget_Base abstract class.
	 *
	 * @package Elementor
	 */
	abstract class Jet_Gallery_Widget_Base extends Widget_Base {

		public $__new_icon_prefix = 'selected_';

		public function get_jet_help_url() {
			return '';
		}

		public function get_help_url() {

			$url = $this->get_jet_help_url();

			if ( ! empty( $url ) ) {
				return add_query_arg(
					[
						'utm_source'   => 'need-help',
						'utm_medium'   => $this->get_name(),
						'utm_campaign' => 'jetproductgallery',
					],
					esc_url( $url )
				);
			}

			return false;

		}

		protected function register_controls() {

			$css_scheme = apply_filters(
				'jet-woo-product-gallery/base/css-scheme',
				[
					'video-overlay'             => '.jet-woo-product-video__overlay',
					'video-popup-wrapper'       => '.jet-woo-product-video__popup-wrapper',
					'video-popup-overlay'       => '.jet-woo-product-video__popup-overlay',
					'video-popup-button'        => '.jet-woo-product-video__popup-button',
					'video-play-button'         => '.jet-woo-product-video__play-button',
					'video-play-button-image'   => '.jet-woo-product-video__play-button-image',
					'photoswipe-trigger'        => '.jet-woo-product-gallery .jet-woo-product-gallery__trigger:not( .jet-woo-product-gallery__image-link )',
					'photoswipe-bg'             => '.jet-woo-product-gallery-' . $this->get_id() . ' .pswp__bg',
					'photoswipe-controls'       => '.jet-woo-product-gallery-' . $this->get_id() . ' .pswp__button::before',
					'photoswipe-controls-hover' => '.jet-woo-product-gallery-' . $this->get_id() . ' .pswp__button:hover::before',
				]
			);

			// Register content controls.
			$this->register_gallery_general_controls();
			$this->register_gallery_content_controls();
			$this->register_gallery_photoswipe_controls();
			$this->register_gallery_video_controls( $css_scheme );

			// Register style controls.
			$this->register_base_photoswipe_trigger_controls_style( $css_scheme );
			$this->register_base_photoswipe_gallery_controls_style( $css_scheme );
			$this->register_base_video_popup_button_controls_style( $css_scheme );
			$this->register_base_video_play_button_controls_style( $css_scheme );

		}

		protected function register_gallery_general_controls() {

			$this->start_controls_section(
				'section_general_content',
				[
					'label'      => __( 'General', 'jet-woo-product-gallery' ),
					'tab'        => Controls_Manager::TAB_CONTENT,
					'show_label' => false,
				]
			);

			$this->add_control(
				'gallery_source',
				[
					'label'   => __( 'Source', 'jet-woo-product-gallery' ),
					'type'    => Controls_Manager::SELECT,
					'default' => 'manual',
					'options' => jet_woo_product_gallery_tools()->get_gallery_source_options(),
				]
			);

			$this->add_control(
				'product_id',
				[
					'type'      => Controls_Manager::NUMBER,
					'label'     => __( 'Product id', 'jet-woo-product-gallery' ),
					'dynamic'   => [
						'active' => true,
					],
					'condition' => [
						'gallery_source' => 'products',
					],
				]
			);

			$this->add_control(
				'disable_feature_image',
				[
					'label'     => __( 'Disable Featured Image', 'jet-woo-product-gallery' ),
					'type'      => Controls_Manager::SWITCHER,
					'condition' => [
						'gallery_source' => 'products',
					],
				]
			);

			$this->add_control(
				'gallery_key',
				[
					'label'     => __( 'Gallery Key', 'jet-woo-product-gallery' ),
					'type'      => Controls_Manager::TEXT,
					'dynamic'   => [
						'active' => true,
					],
					'condition' => [
						'gallery_source' => 'cpt',
					],
				]
			);

			$this->add_control(
				'enable_feature_image',
				[
					'label'     => __( 'Enable Featured Image', 'jet-woo-product-gallery' ),
					'type'      => Controls_Manager::SWITCHER,
					'default'   => '',
					'condition' => [
						'gallery_source' => 'cpt',
					],
				]
			);

			$this->add_control(
				'gallery_images',
				[
					'label'      => __( 'Add Images', 'jet-woo-product-gallery' ),
					'type'       => Controls_Manager::GALLERY,
					'default'    => [],
					'show_label' => false,
					'dynamic'    => [
						'active' => true,
					],
					'condition'  => [
						'gallery_source' => 'manual',
					],
				]
			);

			$this->add_control(
				'enable_video',
				[
					'label'     => __( 'Enable Video', 'jet-woo-product-gallery' ),
					'type'      => Controls_Manager::SWITCHER,
					'default'   => '',
					'condition' => [
						'gallery_source!' => 'products',
					],
				]
			);

			$this->add_control(
				'video_type',
				[
					'label'     => __( 'Video Type', 'jet-woo-product-gallery' ),
					'type'      => Controls_Manager::SELECT,
					'default'   => 'youtube',
					'options'   => [
						'youtube'     => __( 'YouTube', 'jet-woo-product-gallery' ),
						'vimeo'       => __( 'Vimeo', 'jet-woo-product-gallery' ),
						'self_hosted' => __( 'Self Hosted', 'jet-woo-product-gallery' ),
					],
					'condition' => [
						'gallery_source' => [ 'cpt', 'manual' ],
						'enable_video'   => 'yes',
					],
				]
			);

			$this->add_control(
				'youtube_url',
				[
					'label'       => __( 'YouTube URL', 'jet-woo-product-gallery' ),
					'label_block' => true,
					'type'        => Controls_Manager::TEXT,
					'placeholder' => __( 'Enter your URL', 'jet-woo-product-gallery' ),
					'default'     => 'https://www.youtube.com/watch?v=CJO0u_HrWE8',
					'condition'   => [
						'enable_video'   => 'yes',
						'gallery_source' => [ 'cpt', 'manual' ],
						'video_type'     => 'youtube',
					],
					'dynamic'     => [
						'active' => true,
					],
				]
			);

			$this->add_control(
				'vimeo_url',
				[
					'label'       => __( 'Vimeo URL', 'jet-woo-product-gallery' ),
					'label_block' => true,
					'type'        => Controls_Manager::TEXT,
					'placeholder' => __( 'Enter your URL', 'jet-woo-product-gallery' ),
					'default'     => 'https://vimeo.com/235215203',
					'condition'   => [
						'enable_video'   => 'yes',
						'gallery_source' => [ 'cpt', 'manual' ],
						'video_type'     => 'vimeo',
					],
					'dynamic'     => [
						'active' => true,
					],
				]
			);

			$this->add_control(
				'self_hosted_url',
				[
					'label'      => __( 'Choose File', 'jet-woo-product-gallery' ),
					'type'       => Controls_Manager::MEDIA,
					'media_type' => 'video',
					'condition'  => [
						'enable_video'   => 'yes',
						'gallery_source' => [ 'cpt', 'manual' ],
						'video_type'     => 'self_hosted',
					],
					'dynamic'    => [
						'active' => true,
					],
				]
			);

			$this->add_control(
				'custom_placeholder',
				[
					'label'     => __( 'Poster', 'jet-woo-product-gallery' ),
					'type'      => Controls_Manager::MEDIA,
					'dynamic'   => [
						'active' => true,
					],
					'condition' => [
						'gallery_source' => [ 'cpt', 'manual' ],
						'enable_video'   => 'yes',
					],
				]
			);

			$this->add_control(
				'enable_zoom',
				[
					'label'     => __( 'Enable Zoom', 'jet-woo-product-gallery' ),
					'type'      => Controls_Manager::SWITCHER,
					'separator' => 'before',
				]
			);

			$this->add_control(
				'zoom_notice',
				[
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => __( "<strong>Note: Zoom works only when the image size is larger than the container it’s displayed in.</strong>", 'jet-woo-product-gallery' ),
					'condition'       => [
						'enable_zoom' => 'yes',
					],
					'separator' => 'after',
				]
			);

			$this->add_control(
				'zoom_magnify',
				[
					'label'     => __( 'Zoom Magnify', 'jet-woo-product-gallery' ),
					'type'      => Controls_Manager::NUMBER,
					'default'   => 1,
					'min'       => 1,
					'max'       => 2,
					'step'      => 0.1,
					'condition' => [
						'enable_zoom' => 'yes',
					],
				]
			);

			$this->add_control(
				'enable_gallery',
				[
					'label'     => __( 'Enable Gallery', 'jet-woo-product-gallery' ),
					'type'      => Controls_Manager::SWITCHER,
					'separator' => 'before',
				]
			);

			$this->end_controls_section();

		}

		/**
		 * Register widget style controls. Specific for each widget.
		 *
		 * @return void
		 */
		public function register_gallery_content_controls() {
		}

		protected function register_gallery_photoswipe_controls() {

			$this->start_controls_section(
				'section_gallery_style',
				[
					'tab'       => Controls_Manager::TAB_CONTENT,
					'label'     => __( 'Gallery', 'jet-woo-product-gallery' ),
					'condition' => [
						'enable_gallery' => 'yes',
					],
				]
			);

			$this->add_control(
				'gallery_trigger_type',
				[
					'type'    => Controls_Manager::SELECT,
					'label'   => __( 'Trigger Type', 'jet-woo-product-gallery' ),
					'options' => [
						'button' => __( 'Button', 'jet-woo-product-gallery' ),
						'image'  => __( 'Image', 'jet-woo-product-gallery' ),
					],
					'default' => 'button',
				]
			);

			$this->__add_advanced_icon_control(
				'gallery_button_icon',
				[
					'label'       => __( 'Button Icon', 'jet-woo-product-gallery' ),
					'type'        => Controls_Manager::ICON,
					'label_block' => true,
					'file'        => '',
					'default'     => 'fa fa-search',
					'fa5_default' => [
						'value'   => 'fas fa-search',
						'library' => 'fa-solid',
					],
					'condition'   => [
						'gallery_trigger_type' => 'button',
					],
				]
			);

			$this->add_control(
				'photoswipe_trigger_position',
				[
					'type'         => Controls_Manager::SELECT,
					'label'        => __( 'Position', 'jet-woo-product-gallery' ),
					'options'      => [
						'top-right'    => __( 'Top Right', 'jet-woo-product-gallery' ),
						'bottom-right' => __( 'Bottom Right', 'jet-woo-product-gallery' ),
						'bottom-left'  => __( 'Bottom Left', 'jet-woo-product-gallery' ),
						'top-left'     => __( 'Top Left', 'jet-woo-product-gallery' ),
						'center'       => __( 'Center Center', 'jet-woo-product-gallery' ),
					],
					'default'      => 'top-right',
					'prefix_class' => 'jet-woo-product-gallery__trigger--',
					'condition'    => [
						'gallery_trigger_type' => 'button',
					],
				]
			);

			$this->add_control(
				'photoswipe_trigger_show_on_hover',
				[
					'type'         => Controls_Manager::SWITCHER,
					'label'        => __( 'Show On Hover', 'jet-woo-product-gallery' ),
					'return_value' => 'show-on-hover',
					'prefix_class' => 'jet-woo-product-gallery__trigger--',
					'condition'    => [
						'gallery_trigger_type' => 'button',
					],
				]
			);

			$this->add_control(
				'gallery_controls_heading',
				[
					'label'     => __( 'Controls', 'jet-woo-product-gallery' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_control(
				'gallery_show_caption',
				[
					'type'    => Controls_Manager::SWITCHER,
					'label'   => __( 'Show Caption', 'jet-woo-product-gallery' ),
					'default' => 'yes',
				]
			);

			$this->add_control(
				'gallery_show_fullscreen',
				[
					'label'   => __( 'Show Full Screen', 'jet-woo-product-gallery' ),
					'type'    => Controls_Manager::SWITCHER,
					'default' => 'yes',
				]
			);

			$this->add_control(
				'gallery_show_zoom',
				[
					'label'   => __( 'Show Zoom', 'jet-woo-product-gallery' ),
					'type'    => Controls_Manager::SWITCHER,
					'default' => 'yes',
				]
			);

			$this->add_control(
				'gallery_show_share',
				[
					'label'   => __( 'Show Share', 'jet-woo-product-gallery' ),
					'type'    => Controls_Manager::SWITCHER,
					'default' => 'yes',
				]
			);

			$this->add_control(
				'gallery_show_counter',
				[
					'label'   => __( 'Show Counter', 'jet-woo-product-gallery' ),
					'type'    => Controls_Manager::SWITCHER,
					'default' => 'yes',
				]
			);

			$this->add_control(
				'gallery_show_arrows',
				[
					'label'   => __( 'Show Arrows', 'jet-woo-product-gallery' ),
					'type'    => Controls_Manager::SWITCHER,
					'default' => 'yes',
				]
			);

			$this->end_controls_section();

		}

		protected function register_gallery_video_controls( $css_scheme ) {

			$this->start_controls_section(
				'section_video',
				[
					'label'      => __( 'Video', 'jet-woo-product-gallery' ),
					'conditions' => [
						'relation' => 'or',
						'terms'    => [
							[
								'name'     => 'enable_video',
								'operator' => '===',
								'value'    => 'yes',
							],
							[
								'name'     => 'gallery_source',
								'operator' => '===',
								'value'    => 'products',
							],
						],
					],
				]
			);

			$this->add_control(
				'video_display_in',
				[
					'label'   => __( 'Display Video In', 'jet-woo-product-gallery' ),
					'type'    => Controls_Manager::SELECT,
					'default' => 'content',
					'options' => [
						'content' => __( 'Content', 'jet-woo-product-gallery' ),
						'popup'   => __( 'Popup', 'jet-woo-product-gallery' ),
					],
				]
			);

			$this->add_control(
				'aspect_ratio',
				[
					'label'       => __( 'Aspect Ratio', 'jet-woo-product-gallery' ),
					'description' => __( 'Worked just with youtube and vimeo video types.', 'jet-woo-product-gallery' ),
					'type'        => Controls_Manager::SELECT,
					'default'     => '16-9',
					'options'     => [
						'16-9' => '16:9',
						'21-9' => '21:9',
						'9-16' => '9:16',
						'4-3'  => '4:3',
						'2-3'  => '2:3',
						'3-2'  => '3:2',
						'1-1'  => '1:1',
					],
				]
			);

			$this->add_control(
				'first_place_video',
				[
					'label'     => __( 'Display Video at First Place', 'jet-woo-product-gallery' ),
					'type'      => Controls_Manager::SWITCHER,
					'condition' => [
						'video_display_in' => 'content',
					],
				]
			);

			$this->add_control(
				'video_options_heading',
				[
					'label'     => __( 'Options', 'jet-woo-product-gallery' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_control(
				'autoplay',
				[
					'label' => __( 'Autoplay', 'jet-woo-product-gallery' ),
					'type'  => Controls_Manager::SWITCHER,
				]
			);

			$this->add_control(
				'mute',
				[
					'label' => __( 'Mute', 'jet-woo-product-gallery' ),
					'type'  => Controls_Manager::SWITCHER,
				]
			);

			$this->add_control(
				'loop',
				[
					'label' => __( 'Loop', 'jet-woo-product-gallery' ),
					'type'  => Controls_Manager::SWITCHER,
				]
			);

			$this->register_product_video_in_content_controls( $css_scheme );

			$this->register_product_video_in_popup_controls( $css_scheme );

			$this->end_controls_section();

		}

		protected function register_product_video_in_content_controls( $css_scheme ) {

			$this->add_control(
				'video_overlay_heading',
				[
					'label'     => __( 'Overlay', 'jet-woo-product-gallery' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
					'condition' => [
						'video_display_in' => 'content',
					],
				]
			);

			$this->add_control(
				'overlay_color',
				[
					'label'     => __( 'Overlay Color', 'jet-woo-product-gallery' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ' . $css_scheme['video-overlay'] . ':before' => 'background-color: {{VALUE}};',
					],
					'condition' => [
						'video_display_in' => 'content',
					],
				]
			);

			$this->add_control(
				'video_play_button_heading',
				[
					'label'     => __( 'Play Button', 'jet-woo-product-gallery' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
					'condition' => [
						'video_display_in' => 'content',
					],
				]
			);

			$this->add_control(
				'show_play_button',
				[
					'type'      => Controls_Manager::SWITCHER,
					'label'     => __( 'Show', 'jet-woo-product-gallery' ),
					'default'   => 'yes',
					'condition' => [
						'video_display_in' => 'content',
					],
				]
			);

			$this->add_control(
				'play_button_type',
				[
					'type'      => Controls_Manager::CHOOSE,
					'label'     => __( 'Type', 'jet-woo-product-gallery' ),
					'default'   => 'icon',
					'toggle'    => false,
					'options'   => [
						'icon'  => [
							'title' => __( 'Icon', 'jet-woo-product-gallery' ),
							'icon'  => 'eicon-play-o',
						],
						'image' => [
							'title' => __( 'Image', 'jet-woo-product-gallery' ),
							'icon'  => 'eicon-image-bold',
						],
					],
					'condition' => [
						'video_display_in' => 'content',
						'show_play_button' => 'yes',
					],
				]
			);

			$this->__add_advanced_icon_control(
				'play_button_icon',
				[
					'label'       => __( 'Icon', 'jet-woo-product-gallery' ),
					'type'        => Controls_Manager::ICON,
					'label_block' => true,
					'file'        => '',
					'default'     => 'fa fa-play',
					'fa5_default' => [
						'value'   => 'fas fa-play',
						'library' => 'fa-solid',
					],
					'condition'   => [
						'video_display_in' => 'content',
						'show_play_button' => 'yes',
						'play_button_type' => 'icon',
					],
				]
			);

			$this->add_control(
				'play_button_image',
				[
					'label'     => __( 'Image', 'jet-woo-product-gallery' ),
					'type'      => Controls_Manager::MEDIA,
					'condition' => [
						'video_display_in' => 'content',
						'show_play_button' => 'yes',
						'play_button_type' => 'image',
					],
				]
			);

		}

		protected function register_product_video_in_popup_controls( $css_scheme ) {

			$this->add_control(
				'popup_video_overlay_heading',
				[
					'label'     => __( 'Popup Overlay', 'jet-woo-product-gallery' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
					'condition' => [
						'video_display_in' => 'popup',
					],
				]
			);

			$this->add_control(
				'popup_overlay_color',
				[
					'label'     => __( 'Overlay Color', 'jet-woo-product-gallery' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ' . $css_scheme['video-popup-overlay'] => 'background-color: {{VALUE}};',
					],
					'condition' => [
						'video_display_in' => 'popup',
					],
				]
			);

			$this->add_control(
				'video_popup_button_heading',
				[
					'label'     => __( 'Popup Button', 'jet-woo-product-gallery' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
					'condition' => [
						'video_display_in' => 'popup',
					],
				]
			);

			$this->__add_advanced_icon_control(
				'popup_button_icon',
				[
					'label'       => __( 'Icon', 'jet-woo-product-gallery' ),
					'type'        => Controls_Manager::ICON,
					'label_block' => true,
					'file'        => '',
					'default'     => 'fa fa-video',
					'fa5_default' => [
						'value'   => 'fas fa-video',
						'library' => 'fa-solid',
					],
					'condition'   => [
						'video_display_in' => 'popup',
					],
				]
			);

		}

		protected function register_base_photoswipe_trigger_controls_style( $css_scheme ) {

			$this->start_controls_section(
				'section_photoswipe_trigger_style',
				[
					'tab'       => Controls_Manager::TAB_STYLE,
					'label'     => __( 'Photoswipe Trigger', 'jet-woo-product-gallery' ),
					'condition' => [
						'enable_gallery'       => 'yes',
						'gallery_trigger_type' => 'button',
					],
				]
			);

			$this->add_responsive_control(
				'photoswipe_trigger_size',
				[
					'type'       => Controls_Manager::SLIDER,
					'label'      => __( 'Size', 'jet-woo-product-gallery' ),
					'size_units' => [ 'px' ],
					'range'      => [
						'px' => [
							'min' => 20,
							'max' => 200,
						],
					],
					'default'    => [
						'size' => 30,
						'unit' => 'px',
					],
					'selectors'  => [
						'{{WRAPPER}} ' . $css_scheme['photoswipe-trigger'] => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'photoswipe_trigger_icon_size',
				[
					'type'       => Controls_Manager::SLIDER,
					'label'      => __( 'Icon Size', 'jet-woo-product-gallery' ),
					'size_units' => [ 'px' ],
					'range'      => [
						'px' => [
							'min' => 0,
							'max' => 50,
						],
					],
					'default'    => [
						'size' => 18,
						'unit' => 'px',
					],
					'selectors'  => [
						'{{WRAPPER}} ' . $css_scheme['photoswipe-trigger'] . ' .jet-woo-product-gallery__trigger-icon' => 'font-size: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->start_controls_tabs( 'photoswipe_trigger_style_tabs' );

			$this->start_controls_tab(
				'photoswipe_trigger_normal_styles',
				[
					'label' => __( 'Normal', 'jet-woo-product-gallery' ),
				]
			);

			$this->add_control(
				'photoswipe_trigger_normal_color',
				[
					'label'     => __( 'Color', 'jet-woo-product-gallery' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ' . $css_scheme['photoswipe-trigger'] . ' .jet-woo-product-gallery__trigger-icon' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'photoswipe_trigger_normal_background_color',
				[
					'label'     => __( ' Background Color', 'jet-woo-product-gallery' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ' . $css_scheme['photoswipe-trigger'] => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->end_controls_tab();

			$this->start_controls_tab(
				'photoswipe_trigger_hover_styles',
				[
					'label' => __( 'Hover', 'jet-woo-product-gallery' ),
				]
			);

			$this->add_control(
				'photoswipe_trigger_hover_color',
				[
					'label'     => __( 'Color', 'jet-woo-product-gallery' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ' . $css_scheme['photoswipe-trigger'] . ':hover .jet-woo-product-gallery__trigger-icon' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'photoswipe_trigger_hover_background_color',
				[
					'label'     => __( 'Background Color', 'jet-woo-product-gallery' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ' . $css_scheme['photoswipe-trigger'] . ':hover' => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'photoswipe_trigger_hover_border_color',
				[
					'label'     => __( 'Border Color', 'jet-woo-product-gallery' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ' . $css_scheme['photoswipe-trigger'] . ':hover' => 'border-color: {{VALUE}}',
					],
					'condition' => [
						'photoswipe_trigger_border_border!' => '',
					],
				]
			);

			$this->end_controls_tab();

			$this->end_controls_tabs();

			$this->add_group_control(
				Group_Control_Border::get_type(),
				[
					'name'      => 'photoswipe_trigger_border',
					'separator' => 'before',
					'selector'  => '{{WRAPPER}} ' . $css_scheme['photoswipe-trigger'],
				]
			);

			$this->add_control(
				'photoswipe_trigger_border_radius',
				[
					'type'       => Controls_Manager::DIMENSIONS,
					'label'      => __( 'Border Radius', 'jet-woo-product-gallery' ),
					'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
					'selectors'  => [
						'{{WRAPPER}} ' . $css_scheme['photoswipe-trigger'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
					],
				]
			);

			$this->add_responsive_control(
				'photoswipe_trigger_margin',
				[
					'type'       => Controls_Manager::DIMENSIONS,
					'label'      => __( 'Margin', 'jet-woo-product-gallery' ),
					'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
					'selectors'  => [
						'{{WRAPPER}} ' . $css_scheme['photoswipe-trigger'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->end_controls_section();

		}

		protected function register_base_photoswipe_gallery_controls_style( $css_scheme ) {

			$this->start_controls_section(
				'photoswipe_gallery_style',
				[
					'tab'       => Controls_Manager::TAB_STYLE,
					'label'     => __( 'Photoswipe Gallery', 'jet-woo-product-gallery' ),
					'condition' => [
						'enable_gallery' => 'yes',
					],
				]
			);

			$this->add_control(
				'photoswipe_gallery_background_color',
				[
					'label'     => __( 'Background Color', 'jet-woo-product-gallery' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => [
						$css_scheme['photoswipe-bg'] => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'photoswipe_gallery_controls_heading',
				[
					'label' => __( 'Photoswipe Controls', 'jet-woo-product-gallery' ),
					'type'  => Controls_Manager::HEADING,
				]
			);

			$this->start_controls_tabs( 'photoswipe_gallery_controls_style_tabs' );

			$this->start_controls_tab(
				'photoswipe_gallery_controls_normal_styles',
				[
					'label' => __( 'Normal', 'jet-woo-product-gallery' ),
				]
			);

			$this->add_control(
				'photoswipe_gallery_controls_normal_background_color',
				[
					'label'     => __( ' Background Color', 'jet-woo-product-gallery' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => [
						$css_scheme['photoswipe-controls'] => 'background-color: {{VALUE}} !important',
					],
				]
			);

			$this->add_control(
				'photoswipe_gallery_controls_normal_border_radius',
				[
					'type'       => Controls_Manager::DIMENSIONS,
					'label'      => __( 'Border Radius', 'jet-woo-product-gallery' ),
					'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
					'selectors'  => [
						$css_scheme['photoswipe-controls'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
					],
				]
			);

			$this->end_controls_tab();

			$this->start_controls_tab(
				'photoswipe_gallery_controls_hover_styles',
				[
					'label' => __( 'Hover', 'jet-woo-product-gallery' ),
				]
			);

			$this->add_control(
				'photoswipe_gallery_controls_hover_background_color',
				[
					'label'     => __( ' Background Color', 'jet-woo-product-gallery' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => [
						$css_scheme['photoswipe-controls-hover'] => 'background-color: {{VALUE}} !important',
					],
				]
			);

			$this->add_control(
				'photoswipe_gallery_controls_hover_border_radius',
				[
					'type'       => Controls_Manager::DIMENSIONS,
					'label'      => __( 'Border Radius', 'jet-woo-product-gallery' ),
					'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
					'selectors'  => [
						$css_scheme['photoswipe-controls-hover'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
					],
				]
			);

			$this->end_controls_tab();

			$this->end_controls_tabs();

			$this->end_controls_section();

		}

		protected function register_base_video_popup_button_controls_style( $css_scheme ) {

			$this->start_controls_section(
				'video_popup_button_style',
				[
					'tab'       => Controls_Manager::TAB_STYLE,
					'label'     => __( 'Video Popup Button', 'jet-woo-product-gallery' ),
					'condition' => [
						'enable_video'     => 'yes',
						'video_display_in' => 'popup',
					],
				]
			);

			$this->add_responsive_control(
				'video_popup_button_icon_size',
				[
					'label'      => __( 'Size', 'jet-woo-product-gallery' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range'      => [
						'px' => [
							'min' => 0,
							'max' => 50,
						],
					],
					'default'    => [
						'size' => 18,
						'unit' => 'px',
					],
					'selectors'  => [
						'{{WRAPPER}} ' . $css_scheme['video-popup-button'] . ' .jet-woo-product-video__popup-button-icon' => 'font-size: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->start_controls_tabs( 'video_popup_button_style_tabs' );

			$this->start_controls_tab(
				'video_popup_button_normal_styles',
				[
					'label' => __( 'Normal', 'jet-woo-product-gallery' ),
				]
			);

			$this->add_control(
				'video_popup_button_normal_color',
				[
					'label'     => __( 'Color', 'jet-woo-product-gallery' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ' . $css_scheme['video-popup-button'] . ' .jet-woo-product-video__popup-button-icon' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'video_popup_button_normal_background_color',
				[
					'label'     => __( ' Background Color', 'jet-woo-product-gallery' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ' . $css_scheme['video-popup-button'] => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->end_controls_tab();

			$this->start_controls_tab(
				'video_popup_button_hover_styles',
				[
					'label' => __( 'Hover', 'jet-woo-product-gallery' ),
				]
			);

			$this->add_control(
				'video_popup_button_hover_color',
				[
					'label'     => __( 'Color', 'jet-woo-product-gallery' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ' . $css_scheme['video-popup-button'] . ':hover' . ' .jet-woo-product-video__popup-button-icon' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'video_popup_button_hover_background_color',
				[
					'label'     => __( 'Background Color', 'jet-woo-product-gallery' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ' . $css_scheme['video-popup-button'] . ':hover' => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'video_popup_button_hover_border_color',
				[
					'label'     => __( 'Border Color', 'jet-woo-product-gallery' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ' . $css_scheme['video-popup-button'] . ':hover' => 'border-color: {{VALUE}}',
					],
					'condition' => [
						'video_popup_button_border_border!' => '',
					],
				]
			);

			$this->end_controls_tab();

			$this->end_controls_tabs();

			$this->add_group_control(
				Group_Control_Border::get_type(),
				[
					'name'      => 'video_popup_button_border',
					'separator' => 'before',
					'selector'  => '{{WRAPPER}} ' . $css_scheme['video-popup-button'],
				]
			);

			$this->add_control(
				'video_popup_button_border_radius',
				[
					'type'       => Controls_Manager::DIMENSIONS,
					'label'      => __( 'Border Radius', 'jet-woo-product-gallery' ),
					'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
					'selectors'  => [
						'{{WRAPPER}} ' . $css_scheme['video-popup-button'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
					],
				]
			);

			$this->add_responsive_control(
				'video_popup_button_margin',
				[
					'type'       => Controls_Manager::DIMENSIONS,
					'label'      => __( 'Margin', 'jet-woo-product-gallery' ),
					'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
					'selectors'  => [
						'{{WRAPPER}} ' . $css_scheme['video-popup-button'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'video_popup_button_padding',
				[
					'type'       => Controls_Manager::DIMENSIONS,
					'label'      => __( 'Padding', 'jet-woo-product-gallery' ),
					'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
					'selectors'  => [
						'{{WRAPPER}} ' . $css_scheme['video-popup-button'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'video_popup_button_alignment',
				[
					'label'     => __( 'Alignment', 'jet-woo-product-gallery' ),
					'type'      => Controls_Manager::CHOOSE,
					'options'   => [
						'left'   => [
							'title' => __( 'Left', 'jet-woo-product-gallery' ),
							'icon'  => 'eicon-text-align-left',
						],
						'center' => [
							'title' => __( 'Center', 'jet-woo-product-gallery' ),
							'icon'  => 'eicon-text-align-center',
						],
						'right'  => [
							'title' => __( 'Right', 'jet-woo-product-gallery' ),
							'icon'  => 'eicon-text-align-right',
						],
					],
					'classes'   => 'elementor-control-align',
					'selectors' => [
						'{{WRAPPER}} ' . $css_scheme['video-popup-wrapper'] => 'text-align: {{VALUE}};',
					],
				]
			);

			$this->end_controls_section();

		}

		protected function register_base_video_play_button_controls_style( $css_scheme ) {

			$this->start_controls_section(
				'section_video_play_button_style',
				[
					'tab'        => Controls_Manager::TAB_STYLE,
					'label'      => __( 'Play Button', 'jet-woo-product-gallery' ),
					'conditions' => [
						'relation' => 'or',
						'terms'    => [
							[
								'name'     => 'gallery_source',
								'operator' => '===',
								'value'    => 'products',
							],
							[
								'relation' => 'and',
								'terms'    => [
									[
										'name'     => 'enable_video',
										'operator' => '===',
										'value'    => 'yes',
									],
									[
										'name'     => 'video_display_in',
										'operator' => '===',
										'value'    => 'content',
									],
								],
							],
						],
					],
				]
			);

			$this->add_responsive_control(
				'video_play_button_size',
				[
					'type'      => Controls_Manager::SLIDER,
					'label'     => __( 'Size', 'jet-woo-product-gallery' ),
					'range'     => [
						'px' => [
							'min' => 10,
							'max' => 300,
						],
					],
					'selectors' => [
						'{{WRAPPER}} ' . $css_scheme['video-play-button']          => 'font-size: {{SIZE}}{{UNIT}};',
						'{{WRAPPER}} ' . $css_scheme['video-play-button'] . ' img' => 'width: {{SIZE}}{{UNIT}}; height: auto;',
					],
				]
			);

			$this->add_control(
				'video_play_button_image_border_radius',
				[
					'type'       => Controls_Manager::DIMENSIONS,
					'label'      => __( 'Image Border Radius', 'jet-woo-product-gallery' ),
					'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
					'selectors'  => [
						'{{WRAPPER}} ' . $css_scheme['video-play-button-image'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
					'condition'  => [
						'video_play_button_type' => 'image',
					],
				]
			);

			$this->start_controls_tabs( 'video_play_button_tabs' );

			$this->start_controls_tab(
				'video_play_button_normal_tab',
				[
					'label' => __( 'Normal', 'jet-woo-product-gallery' ),
				]
			);

			$this->add_control(
				'video_play_button_color',
				[
					'label'     => __( 'Color', 'jet-woo-product-gallery' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ' . $css_scheme['video-play-button'] => 'color: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				'video_play_button__background_color',
				[
					'label'     => __( 'Background Color', 'jet-woo-product-gallery' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ' . $css_scheme['video-play-button'] => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->end_controls_tab();

			$this->start_controls_tab(
				'video_play_button_hover_tab',
				[
					'label' => __( 'Hover', 'jet-woo-product-gallery' ),
				]
			);

			$this->add_control(
				'video_play_button_color_hover',
				[
					'label'     => __( 'Color', 'jet-woo-product-gallery' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ' . $css_scheme['video-overlay'] . ':hover ' . $css_scheme['video-play-button'] => 'color: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				'video_play_button_hover_background_color',
				[
					'label'     => __( 'Background Color', 'jet-woo-product-gallery' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ' . $css_scheme['video-overlay'] . ':hover ' . $css_scheme['video-play-button'] => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'video_play_button_border_color_hover',
				[
					'label'     => __( 'Border Color', 'jet-woo-product-gallery' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ' . $css_scheme['video-overlay'] . ':hover ' . $css_scheme['video-play-button'] => 'border-color: {{VALUE}};',
					],
					'condition' => [
						'video_play_button_border_border!' => '',
					],
				]
			);

			$this->add_group_control(
				Group_Control_Box_Shadow::get_type(),
				[
					'name'     => 'video_play_button_box_shadow_hover',
					'selector' => '{{WRAPPER}} ' . $css_scheme['video-overlay'] . ':hover ' . $css_scheme['video-play-button'],
				]
			);

			$this->end_controls_tab();

			$this->end_controls_tabs();

			$this->add_group_control(
				Group_Control_Border::get_type(),
				[
					'name'      => 'video_play_button_border',
					'separator' => 'before',
					'selector'  => '{{WRAPPER}} ' . $css_scheme['video-play-button'],
				]
			);

			$this->add_control(
				'video_play_button_border_radius',
				[
					'type'       => Controls_Manager::DIMENSIONS,
					'label'      => __( 'Border Radius', 'jet-woo-product-gallery' ),
					'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
					'selectors'  => [
						'{{WRAPPER}} ' . $css_scheme['video-play-button'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				Group_Control_Box_Shadow::get_type(),
				[
					'name'     => 'video_play_button_box_shadow',
					'selector' => '{{WRAPPER}} ' . $css_scheme['video-play-button'],
				]
			);

			$this->add_responsive_control(
				'video_play_button_margin',
				[
					'type'       => Controls_Manager::DIMENSIONS,
					'label'      => __( 'Margin', 'jet-woo-product-gallery' ),
					'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
					'selectors'  => [
						'{{WRAPPER}} ' . $css_scheme['video-play-button'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'video_play_button_padding',
				[
					'type'       => Controls_Manager::DIMENSIONS,
					'label'      => __( 'Padding', 'jet-woo-product-gallery' ),
					'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
					'selectors'  => [
						'{{WRAPPER}} ' . $css_scheme['video-play-button'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->end_controls_section();

		}

		/**
		 * Set custom size units.
		 *
		 * Extend list of units with custom option.
		 *
		 * @since  2.1.11
		 * @access public
		 *
		 * @param array $units List of units.
		 *
		 * @return mixed
		 */
		public function set_custom_size_unit( $units ) {

			if ( version_compare( ELEMENTOR_VERSION, '3.10.0', '>=' ) ) {
				$units[] = 'custom';
			}

			return $units;

		}

		/**
		 * Add icon control
		 *
		 * @param string $id
		 * @param array  $args
		 * @param object $instance
		 */
		public function __add_advanced_icon_control( $id = null, array $args = [], $instance = null ) {

			if ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, '2.6.0', '>=' ) ) {
				$_id = $id; // old control id
				$id  = $this->__new_icon_prefix . $id;

				$args['type']             = Controls_Manager::ICONS;
				$args['fa4compatibility'] = $_id;

				unset( $args['file'] );
				unset( $args['default'] );

				if ( isset( $args['fa5_default'] ) ) {
					$args['default'] = $args['fa5_default'];

					unset( $args['fa5_default'] );
				}
			} else {
				$args['type'] = Controls_Manager::ICON;
				unset( $args['fa5_default'] );
			}

			if ( null !== $instance ) {
				$instance->add_control( $id, $args );
			} else {
				$this->add_control( $id, $args );
			}

		}

	}

}


