<?php
/**
 * Class: Jet_Woo_Product_Gallery_Grid
 * Name: Gallery Grid
 * Slug: jet-woo-product-gallery-grid
 */

namespace Elementor;

use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class Jet_Woo_Product_Gallery_Grid extends Jet_Gallery_Widget_Base {

	public function get_name() {
		return 'jet-woo-product-gallery-grid';
	}

	public function get_title() {
		return esc_html__( 'Gallery Grid', 'jet-woo-product-gallery' );
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
		return [ 'jet-gallery-widget-gallery-grid' ];
	}

	public function get_icon() {
		return 'jet-gallery-icon-grid';
	}

	public function get_jet_help_url() {
		return 'https://crocoblock.com/knowledge-base/articles/product-gallery-grid-layout-how-to-showcase-product-images-within-the-grid-layout/';
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
			array(
				'label'   => esc_html__( 'Image Size', 'jet-woo-product-gallery' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'thumbnail',
				'options' => jet_woo_product_gallery_tools()->get_image_sizes(),
			)
		);

		$this->add_responsive_control(
			'columns',
			[
				'type'      => Controls_Manager::NUMBER,
				'label'     => __( 'Grid Columns', 'jet-woo-product-gallery' ),
				'min'       => 1,
				'max'       => 6,
				'default'   => 4,
				'selectors' => [
					'{{WRAPPER}} .jet-woo-product-gallery-grid .jet-woo-product-gallery__image-item' => '--columns: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'primary_image',
			[
				'type'          => Controls_Manager::SWITCHER,
				'label'         => __( 'Primary Gallery Image', 'jet-woo-product-gallery' ),
				'label_on'      => __( 'On', 'jet-woo-product-gallery' ),
				'label_off'     => __( 'Off', 'jet-woo-product-gallery' ),
				'return_value'  => 'yes',
				'render_type' => 'template',
				'default'       => '',
				'prefix_class'  => 'jet-woo-product-gallery-grid-primary-',
			]
		);

		$this->add_control(
			'primary_image_width',
			[
				'type'          => Controls_Manager::SLIDER,
				'label'         => __( 'Primary Image Width (%)', 'jet-woo-product-gallery' ),
				'size_units'    => [ '%' ],
				'default' => [
					'unit' => '%',
					'size' => 50,
				],
				'selectors' => [
					'{{WRAPPER}}.jet-woo-product-gallery-grid-primary-yes .jet-woo-product-gallery__primary-image' => 'max-width:{{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.jet-woo-product-gallery-grid-primary-yes .jet-woo-product-gallery__images-grid' => 'max-width: calc(100% - {{SIZE}}{{UNIT}});'
				],
				'condition' => [
					'primary_image' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'grid_items_count',
			[
				'type'        => Controls_Manager::NUMBER,
				'label'       => __( 'Grid Items Count', 'jet-woo-product-gallery' ),
				'description' => __( 'Number of images to display in the grid. Set to 0 or -1 to show all images.', 'jet-woo-product-gallery' ),
				'min'         => -1,
				'max'         => 50,
				'default'     => 4,
			]
		);

		$this->add_responsive_control(
			'grid_overlay_text',
			[
				'type'        => Controls_Manager::TEXT,
				'label'       => __( 'Overlay Text', 'jet-woo-product-gallery' ),
				'default'     => __( 'More Images', 'jet-woo-product-gallery' ),
			]
		);

		$this->end_controls_section();

		$css_scheme = apply_filters(
			'jet-woo-product-gallery-grid/css-scheme',
			array(
				'row'     => '.jet-woo-product-gallery-grid',
				'columns' => '.jet-woo-product-gallery-grid .jet-woo-product-gallery__image-item',
				'images'  => '.jet-woo-product-gallery-grid .jet-woo-product-gallery__image',
			)
		);

		$this->register_controls_columns_style( $css_scheme );

		$this->register_controls_images_style( $css_scheme );

		$this->register_controls_overlay_style( $css_scheme );

	}

	public function register_controls_columns_style( $css_scheme ) {

		$this->start_controls_section(
			'section_columns_style',
			[
				'tab'   => Controls_Manager::TAB_STYLE,
				'label' => __( 'Columns', 'jet-woo-product-gallery' ),
			]
		);

		$this->add_responsive_control(
			'columns_padding',
			[
				'type'       => Controls_Manager::DIMENSIONS,
				'label'      => __( 'Gutter', 'jet-woo-product-gallery' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['columns'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} ' . $css_scheme['row']     => 'margin-left: -{{LEFT}}{{UNIT}}; margin-right: -{{RIGHT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

	}

	public function register_controls_images_style( $css_scheme ) {

		$this->start_controls_section(
			'section_gallery_images_style',
			[
				'tab'   => Controls_Manager::TAB_STYLE,
				'label' => __( 'Images', 'jet-woo-product-gallery' ),
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

		$this->add_responsive_control(
			'gallery_images_padding',
			[
				'type'       => Controls_Manager::DIMENSIONS,
				'label'      => __( 'Padding', 'jet-woo-product-gallery' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['images'] . ':not(.jet-woo-product-gallery--with-video)' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

	}

	public function register_controls_overlay_style( $css_scheme ) {

		$this->start_controls_section(
			'section_gallery_overlay_style',
			[
				'tab'   => Controls_Manager::TAB_STYLE,
				'label' => __( 'Overlay', 'jet-woo-product-gallery' ),
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'overlay_typography',
				'selector' => '{{WRAPPER}} .jet-woo-product-gallery__image-overlay',
				'fields_options' => [
					'typography' => [
						'default' => 'custom',
					],
					'font_size' => [
						'default' => [
							'unit' => 'px',
							'size' => 16,
						],
					],
					'font_weight' => [
						'default' => '500',
					],
				],
			]
		);

		$this->add_control(
			'overlay_text_color',
			[
				'label'     => __( 'Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .jet-woo-product-gallery__image-overlay' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'overlay_text_color_hover',
			[
				'label'     => __( 'Hover Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#000000',
				'selectors' => [
					'{{WRAPPER}} .jet-woo-product-gallery__image-overlay:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'overlay_background',
			[
				'label'       => __( 'Background Type', 'jet-woo-product-gallery' ),
				'type'        => Controls_Manager::CHOOSE,
				'options'     => [
					'color' => [
						'title' => __( 'Classic', 'jet-woo-product-gallery' ),
						'icon'  => 'fa fa-paint-brush',
					],
					'gradient' => [
						'title' => __( 'Gradient', 'jet-woo-product-gallery' ),
						'icon'  => 'fa fa-barcode',
					],
				],
				'default'     => 'color',
				'label_block' => false,
				'render_type' => 'ui',
			]
		);

		$this->add_control(
			'overlay_background_color',
			[
				'label'     => __( 'Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'default'     => 'rgba(0, 0, 0, 0.5)',

				'title'     => __( 'Background Color', 'jet-woo-product-gallery' ),
				'selectors' => [
					'{{WRAPPER}} .jet-woo-product-gallery__image-overlay' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'overlay_background_color_stop',
			[
				'label'      => __( 'Location', 'jet-woo-product-gallery' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ '%' ],
				'default'    => [
					'unit' => '%',
					'size' => 0,
				],
				'render_type' => 'ui',
				'condition'   => [
					'overlay_background' => [ 'gradient' ],
				],
				'of_type' => 'gradient',
			]
		);

		$this->add_control(
			'overlay_background_color_b',
			[
				'label'       => __( 'Second Color', 'jet-woo-product-gallery' ),
				'type'        => Controls_Manager::COLOR,
				'default'     => 'rgba(255, 255, 255, 0.5)',
				'render_type' => 'ui',
				'condition'   => [
					'overlay_background' => [ 'gradient' ],
				],
				'of_type' => 'gradient',
			]
		);

		$this->add_control(
			'overlay_background_color_b_stop',
			[
				'label'      => __( 'Location', 'jet-woo-product-gallery' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ '%' ],
				'default'    => [
					'unit' => '%',
					'size' => 100,
				],
				'render_type' => 'ui',
				'condition'   => [
					'overlay_background' => [ 'gradient' ],
				],
				'of_type' => 'gradient',
			]
		);

		$this->add_control(
			'overlay_background_gradient_type',
			[
				'label'   => __( 'Type', 'jet-woo-product-gallery' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'linear' => __( 'Linear', 'jet-woo-product-gallery' ),
					'radial' => __( 'Radial', 'jet-woo-product-gallery' ),
				],
				'default'     => 'linear',
				'render_type' => 'ui',
				'condition'   => [
					'overlay_background' => [ 'gradient' ],
				],
				'of_type' => 'gradient',
			]
		);

		$this->add_control(
			'overlay_background_gradient_angle',
			[
				'label'      => __( 'Angle', 'jet-woo-product-gallery' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'deg' ],
				'default'    => [
					'unit' => 'deg',
					'size' => 180,
				],
				'range' => [
					'deg' => [
						'step' => 10,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .jet-woo-product-gallery__image-overlay' => 'background-color: transparent; background-image: linear-gradient({{SIZE}}{{UNIT}}, {{overlay_background_color.VALUE}} {{overlay_background_color_stop.SIZE}}{{overlay_background_color_stop.UNIT}}, {{overlay_background_color_b.VALUE}} {{overlay_background_color_b_stop.SIZE}}{{overlay_background_color_b_stop.UNIT}})',
				],
				'condition' => [
					'overlay_background'               => [ 'gradient' ],
					'overlay_background_gradient_type' => 'linear',
				],
				'of_type' => 'gradient',
			]
		);

		$this->add_control(
			'overlay_background_gradient_position',
			[
				'label'   => __( 'Position', 'jet-woo-product-gallery' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'center center' => __( 'Center Center', 'jet-woo-product-gallery' ),
					'center left'   => __( 'Center Left', 'jet-woo-product-gallery' ),
					'center right'  => __( 'Center Right', 'jet-woo-product-gallery' ),
					'top center'    => __( 'Top Center', 'jet-woo-product-gallery' ),
					'top left'      => __( 'Top Left', 'jet-woo-product-gallery' ),
					'top right'     => __( 'Top Right', 'jet-woo-product-gallery' ),
					'bottom center' => __( 'Bottom Center', 'jet-woo-product-gallery' ),
					'bottom left'   => __( 'Bottom Left', 'jet-woo-product-gallery' ),
					'bottom right'  => __( 'Bottom Right', 'jet-woo-product-gallery' ),
				],
				'default' => 'center center',
				'selectors' => [
					'{{WRAPPER}} .jet-woo-product-gallery__image-overlay' => 'background-color: transparent; background-image: radial-gradient(at {{VALUE}}, {{overlay_background_color.VALUE}} {{overlay_background_color_stop.SIZE}}{{overlay_background_color_stop.UNIT}}, {{overlay_background_color_b.VALUE}} {{overlay_background_color_b_stop.SIZE}}{{overlay_background_color_b_stop.UNIT}})',
				],
				'condition' => [
					'overlay_background'               => [ 'gradient' ],
					'overlay_background_gradient_type' => 'radial',
				],
				'of_type' => 'gradient',
			]
		);

		$this->add_responsive_control(
			'overlay_padding',
			[
				'type'       => Controls_Manager::DIMENSIONS,
				'label'      => __( 'Padding', 'jet-woo-product-gallery' ),
				'size_units' => $this->set_custom_size_unit( [ 'px', 'em', '%' ] ),
				'selectors'  => [
					'{{WRAPPER}} .jet-woo-product-gallery__image-overlay' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

	}

	protected function render() {
		jet_woo_product_gallery()->base->render_gallery( 'gallery-grid', $this->get_settings_for_display(), 'elementor' );
	}

}