<?php
/**
 * Class: Jet_Woo_Product_Gallery_Modern
 * Name: Gallery Modern
 * Slug: jet-woo-product-gallery-modern
 */

namespace Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class Jet_Woo_Product_Gallery_Modern extends Jet_Gallery_Widget_Base {

	public function get_name() {
		return 'jet-woo-product-gallery-modern';
	}

	public function get_title() {
		return esc_html__( 'Gallery Modern', 'jet-woo-product-gallery' );
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
		return [ 'jet-gallery-widget-gallery-modern' ];
	}

	public function get_icon() {
		return 'jet-gallery-icon-modern';
	}

	public function get_jet_help_url() {
		return 'https://crocoblock.com/knowledge-base/articles/jetproductgallery-how-to-showcase-the-product-images-with-gallery-modern-widget-proportions-and-style-settings/';
	}

	public function get_categories() {
		return array( 'jet-woo-product-gallery' );
	}

	public function register_gallery_content_controls() {

		$this->start_controls_section(
			'section_product_images',
			[
				'tab'   => Controls_Manager::TAB_CONTENT,
				'label' => __( 'Images', 'jet-woo-product-gallery' ),
			]
		);

		$this->add_control(
			'image_size',
			[
				'type'    => Controls_Manager::SELECT,
				'label'   => __( 'Image Size', 'jet-woo-product-gallery' ),
				'options' => jet_woo_product_gallery_tools()->get_image_sizes(),
			]
		);

		$this->end_controls_section();

		$css_scheme = apply_filters(
			'jet-woo-product-gallery-modern/css-scheme',
			array(
				'wrapper' => '.jet-woo-product-gallery-modern',
				'items'   => '.jet-woo-product-gallery-modern .jet-woo-product-gallery__image-item',
				'images'  => '.jet-woo-product-gallery-modern .jet-woo-product-gallery__image',
				'image-2' => '.jet-woo-product-gallery-modern .jet-woo-product-gallery__image-item:nth-child(5n+2)',
				'image-3' => '.jet-woo-product-gallery-modern .jet-woo-product-gallery__image-item:nth-child(5n+3)',
				'image-4' => '.jet-woo-product-gallery-modern .jet-woo-product-gallery__image-item:nth-child(5n+4)',
				'image-5' => '.jet-woo-product-gallery-modern .jet-woo-product-gallery__image-item:nth-child(5n+5)',
			)
		);

		$this->register_controls_images_style( $css_scheme );

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
			'gallery_images_proportion_1',
			[
				'type'       => Controls_Manager::SLIDER,
				'label'      => __( 'Images Proportion 1 (%)', 'jet-woo-product-gallery' ),
				'size_units' => [ '%' ],
				'range'      => [
					'%' => [
						'min' => 10,
						'max' => 90,
					],
				],
				'default'    => [
					'size' => 30,
					'unit' => '%',
				],
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['image-2'] => 'max-width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} ' . $css_scheme['image-3'] => 'max-width: calc(100% - {{SIZE}}{{UNIT}});',
				],
			]
		);

		$this->add_responsive_control(
			'gallery_images_proportion_2',
			[
				'type'       => Controls_Manager::SLIDER,
				'label'      => __( 'Images Proportion 2 (%)', 'jet-woo-product-gallery' ),
				'size_units' => [ '%' ],
				'range'      => [
					'%' => [
						'min' => 10,
						'max' => 90,
					],
				],
				'default'    => [
					'size' => 70,
					'unit' => '%',
				],
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['image-4'] => 'max-width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} ' . $css_scheme['image-5'] => 'max-width: calc(100% - {{SIZE}}{{UNIT}});',
				],
			]
		);

		$this->add_control(
			'gallery_images_2_heading',
			array(
				'label'     => esc_html__( 'Image 2', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'gallery_images_2_alignment',
			[
				'type'      => Controls_Manager::CHOOSE,
				'label'     => __( 'Vertical Alignment', 'jet-woo-product-gallery' ),
				'options'   => jet_woo_product_gallery_tools()->get_vertical_flex_alignment(),
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['image-2'] => 'align-self: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'gallery_images_2_margin',
			[
				'type'               => Controls_Manager::DIMENSIONS,
				'label'              => __( 'Margin', 'jet-woo-product-gallery' ),
				'size_units'         => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'allowed_dimensions' => 'vertical',
				'placeholder'        => [
					'top'    => '',
					'right'  => 'auto',
					'bottom' => '',
					'left'   => 'auto',
				],
				'selectors'          => [
					'{{WRAPPER}} ' . $css_scheme['image-2'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'gallery_images_3_heading',
			array(
				'label'     => esc_html__( 'Image 3', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'gallery_images_3_alignment',
			[
				'type'      => Controls_Manager::CHOOSE,
				'label'     => __( 'Vertical Alignment', 'jet-woo-product-gallery' ),
				'options'   => jet_woo_product_gallery_tools()->get_vertical_flex_alignment(),
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['image-3'] => 'align-self: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'gallery_images_3_margin',
			[
				'label'              => __( 'Margin', 'jet-woo-product-gallery' ),
				'type'               => Controls_Manager::DIMENSIONS,
				'size_units'         => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'allowed_dimensions' => 'vertical',
				'placeholder'        => [
					'top'    => '',
					'right'  => 'auto',
					'bottom' => '',
					'left'   => 'auto',
				],
				'selectors'          => [
					'{{WRAPPER}} ' . $css_scheme['image-3'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'gallery_images_4_heading',
			array(
				'label'     => esc_html__( 'Image 4', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'gallery_images_4_alignment',
			[
				'type'      => Controls_Manager::CHOOSE,
				'label'     => __( 'Vertical Alignment', 'jet-woo-product-gallery' ),
				'options'   => jet_woo_product_gallery_tools()->get_vertical_flex_alignment(),
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['image-4'] => 'align-self: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'gallery_images_4_margin',
			[
				'type'               => Controls_Manager::DIMENSIONS,
				'label'              => __( 'Margin', 'jet-woo-product-gallery' ),
				'size_units'         => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'allowed_dimensions' => 'vertical',
				'placeholder'        => [
					'top'    => '',
					'right'  => 'auto',
					'bottom' => '',
					'left'   => 'auto',
				],
				'selectors'          => [
					'{{WRAPPER}} ' . $css_scheme['image-4'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'gallery_images_5_heading',
			array(
				'label'     => esc_html__( 'Image 5', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'gallery_images_5_alignment',
			[
				'type'      => Controls_Manager::CHOOSE,
				'label'     => __( 'Vertical Alignment', 'jet-woo-product-gallery' ),
				'options'   => jet_woo_product_gallery_tools()->get_vertical_flex_alignment(),
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['image-5'] => 'align-self: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'gallery_images_5_margin',
			[
				'type'               => Controls_Manager::DIMENSIONS,
				'label'              => __( 'Margin', 'jet-woo-product-gallery' ),
				'size_units'         => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'allowed_dimensions' => 'vertical',
				'placeholder'        => [
					'top'    => '',
					'right'  => 'auto',
					'bottom' => '',
					'left'   => 'auto',
				],
				'selectors'          => [
					'{{WRAPPER}} ' . $css_scheme['image-5'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'gallery_images_background_color',
			[
				'type'      => Controls_Manager::COLOR,
				'label'     => __( 'Background Color', 'jet-woo-product-gallery' ),
				'separator' => 'before',
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
			[
				'name'     => 'gallery_images_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['images'],
			]
		);

		$this->add_responsive_control(
			'gallery_images_padding',
			[
				'type'       => Controls_Manager::DIMENSIONS,
				'label'      => __( 'Padding', 'jet-woo-product-gallery' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['items'] . ':not(.jet-woo-product-gallery--with-video)' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'gallery_images_margin',
			[
				'type'       => Controls_Manager::DIMENSIONS,
				'label'      => __( 'Outer Offset', 'jet-woo-product-gallery' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['wrapper'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

	}

	protected function render() {
		jet_woo_product_gallery()->base->render_gallery( 'gallery-modern', $this->get_settings_for_display(), 'elementor' );
	}

}