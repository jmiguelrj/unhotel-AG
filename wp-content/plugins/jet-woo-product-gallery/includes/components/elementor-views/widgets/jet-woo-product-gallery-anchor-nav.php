<?php
/**
 * Class: Jet_Woo_Product_Gallery_Anchor_Nav
 * Name: Gallery Anchor Navigation
 * Slug: jet-woo-product-gallery-anchor-nav
 */

namespace Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class Jet_Woo_Product_Gallery_Anchor_Nav extends Jet_Gallery_Widget_Base {

	public function get_name() {
		return 'jet-woo-product-gallery-anchor-nav';
	}

	public function get_title() {
		return __( 'Gallery Anchor Navigation', 'jet-woo-product-gallery' );
	}

	public function get_script_depends() {
		return array( 'zoom', 'wc-single-product', 'mediaelement', 'photoswipe-ui-default', 'photoswipe' );
	}

	/**
	 * Get style dependencies.
	 *
	 * Retrieve the list of style dependencies the widget requires.
	 *
	 * @since 1.0.0
	 * @since 2.1.19 Added widget styles.
	 *
	 * @return array Widget style dependencies.
	 */
	public function get_style_depends(): array {
		return [ 'jet-gallery-widget-gallery-anchor-nav' ];
	}

	public function get_icon() {
		return 'jet-gallery-icon-anchor-nav';
	}

	public function get_jet_help_url() {
		return 'https://crocoblock.com/knowledge-base/articles/jetproductgallery-gallery-anchor-navigation-widget-how-to-create-a-scrollable-product-images-layout-with-an-anchor-navigation-element/';
	}

	public function get_categories() {
		return array( 'jet-woo-product-gallery' );
	}

	public function register_gallery_content_controls() {

		$this->start_controls_section(
			'section_product_images',
			array(
				'label'      => __( 'Images', 'jet-woo-product-gallery' ),
				'tab'        => Controls_Manager::TAB_CONTENT,
				'show_label' => false,
			)
		);

		$this->add_control(
			'image_size',
			array(
				'label'   => __( 'Image Size', 'jet-woo-product-gallery' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'full',
				'options' => jet_woo_product_gallery_tools()->get_image_sizes(),
			)
		);

		$this->add_control(
			'thumbs_image_size',
			[
				'label'   => __( 'Thumbnails Size', 'jet-woo-product-gallery' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'thumbnail',
				'options' => jet_woo_product_gallery_tools()->get_image_sizes(),
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_navigation_settings',
			array(
				'label'      => __( 'Navigation', 'jet-woo-product-gallery' ),
				'tab'        => Controls_Manager::TAB_CONTENT,
				'show_label' => false,
			)
		);

		$this->add_control(
			'navigation_type',
			array(
				'type'    => Controls_Manager::CHOOSE,
				'label'   => __( 'Navigation Type', 'jet-woo-product-gallery' ),
				'options' => [
					'bullets'    => [
						'title' => __( 'Bullets', 'jet-woo-product-gallery' ),
						'icon'  => 'eicon-navigation-horizontal',
					],
					'thumbnails' => [
						'title' => __( 'Thumbnails', 'jet-woo-product-gallery' ),
						'icon'  => 'eicon-image',
					],
				],
				'default' => 'bullets',
			)
		);
		
		$this->add_control(
			'navigation_position',
			array(
				'label'        => __( 'Navigation Position', 'jet-woo-product-gallery' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'outside',
				'options'      => array(
					'outside' => __( 'Outside', 'jet-woo-product-gallery' ),
					'inside'  => __( 'Inside', 'jet-woo-product-gallery' ),
				),
				'prefix_class' => 'navigation-position-',
			)
		);
		
		$this->end_controls_section();
		
		$css_scheme = apply_filters(
			'jet-woo-product-gallery-anchor-nav/css-scheme',
			[
				'item'                         => '.jet-woo-product-gallery__image-item',
				'items'                        => '.jet-woo-product-gallery-anchor-nav-items',
				'images'                       => '.jet-woo-product-gallery-anchor-nav .jet-woo-product-gallery__image',
				'controller'                   => '.jet-woo-product-gallery-anchor-nav-controller',
				'controller-item'              => '.jet-woo-product-gallery-anchor-nav-controller .controller-item',
				'controller-bullet'            => '.jet-woo-product-gallery-anchor-nav-controller .controller-item__bullet',
				'controller-bullet-current'    => '.jet-woo-product-gallery-anchor-nav-controller .current-item .controller-item__bullet',
				'controller-thumbnail'         => '.jet-woo-product-gallery-anchor-nav-controller .controller-item__thumbnail img',
				'controller-thumbnail-current' => '.jet-woo-product-gallery-anchor-nav-controller .current-item .controller-item__thumbnail img',
			]
		);

		$this->register_controls_images_style( $css_scheme );

		$this->register_controls_controller_style( $css_scheme );

	}

	public function register_controls_images_style( $css_scheme ) {

		$this->start_controls_section(
			'section_gallery_images_style',
			[
				'tab'   => Controls_Manager::TAB_STYLE,
				'label' => __( 'Images', 'jet-woo-product-gallery' ),
			]
		);

		$this->add_responsive_control(
			'gallery_images_space_between',
			[
				'type'       => Controls_Manager::SLIDER,
				'label'      => __( 'Space Between Images', 'jet-woo-product-gallery' ),
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default'   => [
					'size' => 5,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['item'] . '+' . $css_scheme['item'] => 'margin-top: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'gallery_images_background_color',
			[
				'type'      => Controls_Manager::COLOR,
				'label'     => __( 'Background Color', 'jet-woo-product-gallery' ),
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['images'] => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'gallery_images_border',
				'selector' => '{{WRAPPER}} ' . $css_scheme['images'],
			]
		);

		$this->add_control(
			'gallery_images_border_radius',
			[
				'type'       => Controls_Manager::DIMENSIONS,
				'label'      => __( 'Border Radius', 'jet-woo-product-gallery' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['images'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'gallery_images_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['images'],
			)
		);

		$this->end_controls_section();

	}

	public function register_controls_controller_style( $css_scheme ) {
		$this->start_controls_section(
			'section_controller_style',
			[
				'tab'   => Controls_Manager::TAB_STYLE,
				'label' => __( 'Navigation', 'jet-woo-product-gallery' ),
			]
		);

		$this->add_responsive_control(
			'controller_width',
			[
				'type'        => Controls_Manager::SLIDER,
				'label'       => __( 'Width', 'jet-woo-product-gallery' ),
				'render_type' => 'template',
				'size_units'  => $this->set_custom_size_unit( [ 'px', '%' ] ),
				'range'       => [
					'px' => [
						'min' => 70,
						'max' => 500,
					],
					'%'  => [
						'min' => 0,
						'max' => 50,
					],
				],
				'default'     => [
					'size' => 80,
					'unit' => 'px',
				],
				'selectors'   => [
					'{{WRAPPER}} ' . $css_scheme['controller'] => 'max-width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} ' . $css_scheme['items']      => 'max-width: calc(100% - {{SIZE}}{{UNIT}});',
				],
				'condition'   => [
					'navigation_position' => 'outside',
				],
			]
		);

		$this->add_responsive_control(
			'controller_offset_top',
			[
				'type'       => Controls_Manager::SLIDER,
				'label'      => __( 'Offset Top (px)', 'jet-woo-product-gallery' ),
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 500,
					],
				],
				'default'    => [
					'size' => 0,
					'unit' => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['controller'] => 'margin-top: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'controller_position',
			array(
				'label'        => __( 'Position', 'jet-woo-product-gallery' ),
				'type'         => Controls_Manager::CHOOSE,
				'default'      => 'left',
				'options'      => array(
					'left'  => array(
						'title' => __( 'Start', 'jet-woo-product-gallery' ),
						'icon'  => ! is_rtl() ? 'eicon-h-align-left' : 'eicon-h-align-right',
					),
					'right' => array(
						'title' => __( 'End', 'jet-woo-product-gallery' ),
						'icon'  => ! is_rtl() ? 'eicon-h-align-right' : 'eicon-h-align-left',
					),
				),
				'prefix_class' => 'jet-woo-product-gallery-anchor-nav-controller-',
			)
		);

		$this->add_control(
			'controller_bullets_heading',
			array(
				'label'      => __( 'Bullets', 'jet-woo-product-gallery' ),
				'type'       => Controls_Manager::HEADING,
				'separator'  => 'before',
				'condition'  => [
					'navigation_type' => 'bullets',
				],
			)
		);

		$this->add_control(
			'controller_thumbnails_heading',
			array(
				'label'     => __( 'Thumbnails', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'navigation_type' => 'thumbnails',
				],
			)
		);

		$this->add_responsive_control(
			'controller_bullets_width',
			[
				'type'       => Controls_Manager::SLIDER,
				'label'      => __( 'Width', 'jet-woo-product-gallery' ),
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['controller-bullet'] => 'width: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'navigation_type' => 'bullets',
				],
			]
		);

		$this->add_responsive_control(
			'controller_bullets_height',
			[
				'type'       => Controls_Manager::SLIDER,
				'label'      => __( 'Height', 'jet-woo-product-gallery' ),
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['controller-bullet'] => 'height: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'navigation_type' => 'bullets',
				],
			]
		);

		$this->start_controls_tabs( 'controller_style_tabs',
		);

		$this->start_controls_tab(
			'controller_normal_styles',
			[
				'label' => __( 'Normal', 'jet-woo-product-gallery' ),
			]
		);

		$this->add_control(
			'controller_thumbnails_opacity',
			[
				'type'      => Controls_Manager::SLIDER,
				'label'     => __( 'Opacity', 'jet-woo-product-gallery' ),
				'range'     => [
					'px' => [
						'max'  => 1,
						'min'  => 0.10,
						'step' => 0.01,
					],
				],
				'default' => [
					'size' => 0.5,
				],
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['controller-thumbnail'] => 'opacity: {{SIZE}};',
				],
				'condition'  => [
					'navigation_type' => 'thumbnails',
				],
			]
		);

		$this->add_control(
			'controller_bullets_normal_background_color',
			[
				'type'      => Controls_Manager::COLOR,
				'label'     => __( 'Background Color', 'jet-woo-product-gallery' ),
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['controller-bullet'] => 'background-color: {{VALUE}}',
					'{{WRAPPER}} ' . $css_scheme['controller-thumbnail'] => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'controller_hover_styles',
			array(
				'label' => __( 'Hover', 'jet-woo-product-gallery' ),
			)
		);

		$this->add_control(
			'controller_thumbnails_opacity_hover',
			[
				'type'      => Controls_Manager::SLIDER,
				'label'     => __( 'Opacity', 'jet-woo-product-gallery' ),
				'range'     => [
					'px' => [
						'max'  => 1,
						'min'  => 0.10,
						'step' => 0.01,
					],
				],
				'default' => [
					'size' => 1,
				],
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['controller-thumbnail'] . ':hover' => 'opacity: {{SIZE}};',
				],
				'condition' => [
					'navigation_type' => 'thumbnails',
				],
			]
		);

		$this->add_control(
			'controller_bullets_hover_background_color',
			array(
				'label'     => __( 'Background Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['controller-bullet'] . ':hover' => 'background-color: {{VALUE}}',
					'{{WRAPPER}} ' . $css_scheme['controller-thumbnail'] . ':hover' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'controller_bullets_hover_border_color',
			array(
				'label'     => __( 'Border Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['controller-bullet'] . ':hover' => 'border-color: {{VALUE}}',
					'{{WRAPPER}} ' . $css_scheme['controller-thumbnail'] . ':hover' => 'border-color: {{VALUE}}',

				),
				'condition' => array(
					'controller_bullets_normal_border_border!' => '',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'controller_bullets_hover_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['controller-bullet'] . ':hover' . ', {{WRAPPER}} ' . $css_scheme['controller-thumbnail'] . ':hover',
			)
		);


		$this->end_controls_tab();

		$this->start_controls_tab(
			'controller_current_styles',
			array(
				'label' => __( 'Current', 'jet-woo-product-gallery' ),
			)
		);

		$this->add_control(
			'controller_thumbnails_opacity_active',
			[
				'type'      => Controls_Manager::SLIDER,
				'label'     => __( 'Opacity', 'jet-woo-product-gallery' ),
				'range'     => [
					'px' => [
						'max'  => 1,
						'min'  => 0.10,
						'step' => 0.01,
					],
				],
				'default' => [
					'size' => 1,
				],
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['controller-thumbnail-current'] => 'opacity: {{SIZE}};',
				],
				'condition' => [
					'navigation_type' => 'thumbnails',
				],
			]
		);

		$this->add_control(
			'controller_bullets_current_background_color',
			array(
				'label'     => __( 'Background Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['controller-bullet-current'] => 'background-color: {{VALUE}}',
					'{{WRAPPER}} ' . $css_scheme['controller-thumbnail-current'] => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'controller_bullets_current_border_color',
			array(
				'label'     => __( 'Border Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['controller-bullet-current'] => 'border-color: {{VALUE}}',
					'{{WRAPPER}} ' . $css_scheme['controller-thumbnail-current'] => 'border-color: {{VALUE}}',
				),
				'condition' => array(
					'controller_bullets_normal_border_border!' => '',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'controller_bullets_current_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['controller-bullet-current'] . ', {{WRAPPER}} ' . $css_scheme['controller-thumbnail-current'],
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'controller_bullets_normal_border',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} ' . $css_scheme['controller-bullet'] . ', {{WRAPPER}} ' . $css_scheme['controller-thumbnail'],
			]
		);

		$this->add_control(
			'controller_bullets_border_radius',
			[
				'type'       => Controls_Manager::DIMENSIONS,
				'label'      => __( 'Border Radius', 'jet-woo-product-gallery' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['controller-bullet'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} ' . $css_scheme['controller-thumbnail'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'      => 'controller_bullets_normal_shadow',
				'selector'  => '{{WRAPPER}} ' . $css_scheme['controller-bullet'] . ', {{WRAPPER}} ' . $css_scheme['controller-thumbnail'],
			]
		);

		$this->add_responsive_control(
			'controller_bullets_margin',
			[
				'type'       => Controls_Manager::DIMENSIONS,
				'label'      => __( 'Margin', 'jet-woo-product-gallery' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['controller-bullet'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  => [
					'navigation_type' => 'bullets',
				],
			]
		);

		$this->add_responsive_control(
			'controller_thumbnails_gutter',
			[
				'type'               => Controls_Manager::DIMENSIONS,
				'label'              => __( 'Gutter', 'jet-woo-product-gallery' ),
				'separator'          => 'before',
				'size_units'         => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'allowed_dimensions' => 'horizontal',
				'render_type'        => 'template',
				'placeholder'        => [
					'top'    => 'auto',
					'right'  => '',
					'bottom' => 'auto',
					'left'   => '',
				],
				'selectors'          => [
					'{{WRAPPER}} ' . $css_scheme['controller'] => 'padding-left: {{LEFT}}{{UNIT}}; padding-right: {{RIGHT}}{{UNIT}};',
				],
				'condition'          => [
					'navigation_type' => 'thumbnails',
				]
			]
		);

		$this->add_responsive_control(
			'controller_thumbnails_width',
			[
				'type'        => Controls_Manager::SLIDER,
				'label'       => __( 'Thumbnails Width', 'jet-woo-product-gallery' ),
				'render_type' => 'template',
				'size_units'  => $this->set_custom_size_unit( [ 'px', '%' ] ),
				'range'       => [
					'px' => [
						'min' => 70,
						'max' => 500,
					],
					'%'  => [
						'min' => 0,
						'max' => 50,
					],
				],
				'default'     => [
					'size' => 150,
					'unit' => 'px',
				],
				'selectors'   => [
					'{{WRAPPER}} ' . $css_scheme['controller'] => 'max-width: {{SIZE}}{{UNIT}};',
				],
				'condition'   => [
					'navigation_type' => 'thumbnails',
					'navigation_position' => 'inside',
				],
			]
		);

		$this->add_responsive_control(
			'controller_thumbnails_spacing',
			[
				'type'       => Controls_Manager::SLIDER,
				'label'      => __( 'Thumbnails Spacing', 'jet-woo-product-gallery' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['controller-item'] . ':not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'navigation_type' => 'thumbnails',
				]
			]
		);

		$this->end_controls_section();

	}

	protected function render() {
		jet_woo_product_gallery()->base->render_gallery( 'gallery-anchor-nav', $this->get_settings_for_display(), 'elementor' );
	}

}