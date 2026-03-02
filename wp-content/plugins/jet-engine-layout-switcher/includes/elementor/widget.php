<?php
namespace Jet_Engine_Layout_Switcher\Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'jet-engine-layout-switcher';
	}

	public function get_title() {
		return esc_html__( 'Layout Switcher', 'jet-engine' );
	}

	public function get_icon() {
		return 'jet-engine-icon-layout-switcher';
	}

	public function get_categories() {
		return array( 'jet-listing-elements' );
	}

	public function get_help_url() {
		return false;
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_general',
			array(
				'label' => esc_html__( 'General', 'jet-engine' ),
			)
		);

		$this->add_control(
			'widget_id',
			array(
				'label'       => esc_html__( 'Select a Listing Grid widget', 'jet-engine' ),
				'label_block' => true,
				'type'        => 'jet-finder-widget',
				'widget_name' => 'jet-listing-grid',
			)
		);

		$repeater = new Repeater();

		$repeater->start_controls_tabs(
			'tabs'
		);

		$repeater->start_controls_tab(
			'tab_content',
			array(
				'label' => esc_html__( 'Content', 'jet-engine' ),
			)
		);

		$repeater->add_control(
			'label',
			array(
				'label' => esc_html__( 'Label', 'jet-engine' ),
				'type'  => Controls_Manager::TEXT,
			)
		);

		$repeater->add_control(
			'slug',
			array(
				'label'       => esc_html__( 'Slug', 'jet-engine' ),
				'description' => esc_html__( 'Should contain only Latin letters, numbers, `-` or `_` chars', 'jet-engine' ),
				'type'        => Controls_Manager::TEXT,
			)
		);

		$repeater->add_control(
			'icon',
			array(
				'label'       => esc_html__( 'Icon', 'jet-engine' ),
				'type'        => Controls_Manager::ICONS,
				'skin'        => 'inline',
				'label_block' => false,
			)
		);

		$repeater->end_controls_tab();

		$repeater->start_controls_tab(
			'tab_settings',
			array(
				'label' => esc_html__( 'Settings', 'jet-engine' ),
			)
		);

		$repeater->add_control(
			'is_default_layout',
			array(
				'label'   => esc_html__( 'Is Default Layout', 'jet-engine' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => '',
			)
		);

		$repeater->add_control(
			'lisitng_id',
			array(
				'label'          => esc_html__( 'Listing', 'jet-engine' ),
				'type'           => 'jet-query',
				'query_type'     => 'post',
				'select2options' => array(
					'placeholder' => esc_html__( 'Default', 'jet-engine' ),
				),
				'query' => array(
					'post_type' => jet_engine()->post_type->slug(),
					'meta_query' => array(
						'relation' => 'or',
						array(
							'key'     => '_entry_type',
							'value'   => '',
							'compare' => 'NOT EXISTS',
						),
						array(
							'key'     => '_entry_type',
							'value'   => 'listing',
						),
					),
				),
				'condition' => array(
					'is_default_layout' => '',
				),
			)
		);

		$repeater->add_responsive_control(
			'columns',
			array(
				'label'   => esc_html__( 'Columns Number', 'jet-engine' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => array(
					'' => esc_html__( 'Default', 'jet-engine' ),
					1  => 1,
					2  => 2,
					3  => 3,
					4  => 4,
					5  => 5,
					6  => 6,
					7  => 7,
					8  => 8,
					9  => 9,
					10 => 10,
					'auto' => esc_html__( 'Auto', 'jet-engine' ),
				),
				'condition' => array(
					'is_default_layout' => '',
				),
			)
		);

		$repeater->add_responsive_control(
			'column_min_width',
			array(
				'label'     => esc_html__( 'Column Min Width', 'jet-engine' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 240,
				'min'       => 0,
				'max'       => 1600,
				'step'      => 1,
				'condition' => array(
					'is_default_layout' => '',
					'columns' => 'auto',
				),
			)
		);

		$repeater->add_responsive_control(
			'column_auto_note',
			array(
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Note: The Masonry Listing combined with Auto Columns might cause unexpected results and break the layout.', 'jet-engine' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
				'condition'       => array(
					'is_default_layout' => '',
					'columns' => 'auto',
				),
			)
		);

		$repeater->end_controls_tab();

		$repeater->end_controls_tabs();

		$this->add_control(
			'layouts',
			array(
				'label'   => esc_html__( 'Layouts', 'jet-engine' ),
				'type'    => Controls_Manager::REPEATER,
				'fields'  => $repeater->get_controls(),
				'default' => array(
					array(
						'label' => esc_html__( 'Grid', 'jet-engine' ),
						'slug'  => 'grid',
						'is_default_layout' => 'yes',
					),
					array(
						'label'   => esc_html__( 'List', 'jet-engine' ),
						'slug'    => 'list',
						'columns' => '1',
					)
				),
				'title_field' => '{{{ label }}}',
			)
		);

		$this->add_control(
			'show_label',
			array(
				'label'     => esc_html__( 'Labels', 'jet-engine' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'label_on'  => esc_html__( 'Show', 'jet-engine' ),
				'label_off' => esc_html__( 'Hide', 'jet-engine' ),
				'separator' => 'before',
			)
		);

		$this->end_controls_section();

		/**
		 * `Group` Style Section
		 */
		$this->start_controls_section(
			'section_group_style',
			array(
				'label' => esc_html__( 'Group', 'jet-engine' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'align',
			array(
				'label'   => esc_html__( 'Alignment', 'jet-engine' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => array(
					'flex-start' => array(
						'title' => esc_html__( 'Start', 'jet-engine' ),
						'icon'  => ! is_rtl() ? 'eicon-h-align-left' : 'eicon-h-align-right',
					),
					'center' => array(
						'title' => esc_html__( 'Center', 'jet-engine' ),
						'icon'  => 'eicon-h-align-center',
					),
					'flex-end' => array(
						'title' => esc_html__( 'End', 'jet-engine' ),
						'icon'  => ! is_rtl() ? 'eicon-h-align-right' : 'eicon-h-align-left',
					),
					'stretch' => array(
						'title' => esc_html__( 'Stretch', 'jet-engine' ),
						'icon' => 'eicon-h-align-stretch',
					),
				),
				'selectors_dictionary' => array(
					'flex-start' => '--je-layout-switcher-align: flex-start; --je-layout-switcher-btn-grow: initial;',
					'center'     => '--je-layout-switcher-align: center; --je-layout-switcher-btn-grow: initial;',
					'flex-end'   => '--je-layout-switcher-align: flex-end; --je-layout-switcher-btn-grow: initial;',
					'stretch'    => '--je-layout-switcher-align: stretch; --je-layout-switcher-btn-grow: 1;',
				),
				'selectors' => array(
					'{{WRAPPER}} .je-layout-switcher' => '{{VALUE}}',
				),
			)
		);

		$this->add_control(
			'gap',
			array(
				'label' => esc_html__( 'Gap', 'jet-engine' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => array(
					'px' => array(
						'min' => 0,
						'max' => 50,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .je-layout-switcher__group'  => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		/**
		 * `Buttons` Style Section
		 */
		$this->start_controls_section(
			'section_buttons_style',
			array(
				'label' => esc_html__( 'Buttons', 'jet-engine' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'button_typography',
				'selector' => '{{WRAPPER}} .je-layout-switcher__btn',
			)
		);

		$this->add_control(
			'button_icon_size',
			array(
				'label' => esc_html__( 'Icon Size', 'jet-engine' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => array(
					'px' => array(
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .je-layout-switcher__btn-icon' => 'font-size: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'button_icon_spacing',
			array(
				'label' => esc_html__( 'Icon Spacing', 'jet-engine' ),
				'type'  => \Elementor\Controls_Manager::SLIDER,
				'range' => array(
					'px' => array(
						'max' => 50,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .je-layout-switcher__btn' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_button_style' );

		$this->start_controls_tab(
			'tab_button_normal',
			array(
				'label' => esc_html__( 'Normal', 'jet-engine' ),
			)
		);

		$this->add_control(
			'button_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-engine' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .je-layout-switcher__btn'     => 'color: {{VALUE}};',
					'{{WRAPPER}} .je-layout-switcher__btn svg' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'button_bg',
			array(
				'label'     => esc_html__( 'Background', 'jet-engine' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .je-layout-switcher__btn' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_hover',
			array(
				'label' => esc_html__( 'Hover', 'jet-engine' ),
			)
		);

		$this->add_control(
			'button_color_hover',
			array(
				'label'     => esc_html__( 'Color', 'jet-engine' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .je-layout-switcher__btn:hover'     => 'color: {{VALUE}};',
					'{{WRAPPER}} .je-layout-switcher__btn:hover svg' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'button_bg_hover',
			array(
				'label'     => esc_html__( 'Background', 'jet-engine' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .je-layout-switcher__btn:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'button_border_color_hover',
			array(
				'label'     => esc_html__( 'Border Color', 'jet-engine' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => array(
					'button_border_border!' => '',
				),
				'selectors' => array(
					'{{WRAPPER}} .je-layout-switcher__btn:hover' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_active',
			array(
				'label' => esc_html__( 'Active', 'jet-engine' ),
			)
		);

		$this->add_control(
			'button_color_active',
			array(
				'label'     => esc_html__( 'Color', 'jet-engine' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .je-layout-switcher__btn--active'     => 'color: {{VALUE}};',
					'{{WRAPPER}} .je-layout-switcher__btn--active svg' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'button_bg_active',
			array(
				'label'     => esc_html__( 'Background', 'jet-engine' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .je-layout-switcher__btn--active' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'button_border_color_active',
			array(
				'label'     => esc_html__( 'Border Color', 'jet-engine' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => array(
					'button_border_border!' => '',
				),
				'selectors' => array(
					'{{WRAPPER}} .je-layout-switcher__btn--active' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'      => 'button_border',
				'selector'  => '{{WRAPPER}} .je-layout-switcher__btn',
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'button_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'jet-engine' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
				'selectors'  => array(
					'{{WRAPPER}} .je-layout-switcher__btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'button_padding',
			array(
				'label'      => esc_html__( 'Padding', 'jet-engine' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem', 'vw', 'custom' ),
				'selectors'  => array(
					'{{WRAPPER}} .je-layout-switcher__btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);

		$this->end_controls_section();
	}

	protected function render() {

		$settings = $this->get_settings();
		$settings['view'] = 'elementor';

		jet_engine()->listings->render_item( 'layout-switcher', $settings );
	}

}
