<?php

namespace Jet_Popup\Blocks;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Action_Button extends Base {

	/**
	 * Returns block name
	 *
	 * @return string
	 */
	public function get_name() {
		return 'action-button';
	}

	/**
	 * Return attributes array
	 *
	 * @return array
	 */
	public function get_attributes() {
		return [
			'__internalWidgetId'   => [
				'type'    => 'string',
				'default' => '',
			],
			'blockPreview' => [
				'type'    => 'boolean',
				'default' => false,
			],
			// General
			'actionType' => [
				'type'    => 'string',
				'default' => 'link',
			],
			'buttonAlignment' => [
				'type'    => 'string',
				'default' => 'center',
			],
			'buttonText' => [
				'type'    => 'string',
				'default' => __( 'Click Here', 'jet-popup' ),
			],
			'buttonLink' => [
				'type'    => 'string',
				'default' => '',
			],
			'buttonLinkBlank' => [
				'type'    => 'boolean',
				'default' => false,
			],
			'buttonLinkNofollow' => [
				'type'    => 'boolean',
				'default' => false,
			],
			'iconId'  => [
				'type'    => 'number',
				'default' => 0,
			],
			'iconUrl' => [
				'type'    => 'string',
				'default' => '',
			],
		];
	}

	/**
	 * Return callback
	 *
	 * @return html
	 */
	public function render_callback( $settings = [] ) {

		if ( filter_var( $settings[ 'blockPreview' ], FILTER_VALIDATE_BOOLEAN ) ) {
			return sprintf( '<img src="%s" alt="">', jet_popup()->plugin_url( 'assets/image/block-previews/action-button.png' ) );
		}

		$icon_html = \Jet_Popup_Utils::get_svg_icon_html( $settings['iconId'], '', [], false );

		$render_instance = new \Jet_Popup\Render\Action_Button_Render( [
			'action_type' => $settings['actionType'],
			'button_alignment' => $settings['buttonAlignment'],
			'button_text' => $settings['buttonText'],
			'button_link' => $settings['buttonLink'],
			'button_link_blank' => $settings['buttonLinkBlank'],
			'button_link_nofollow' => $settings['buttonLinkNofollow'],
			'icon_html' => $icon_html,
		] );

		return $render_instance->get_content();

	}

	/**
	 * @return void
	 */
	public function add_style_manager_options() {

		$this->controls_manager->start_section( 'style_controls', [
			'id'          => 'section_general_styles',
			'initialOpen' => true,
			'title'       => esc_html__( 'General', 'jet-popup' ),
		] );

		$this->controls_manager->add_responsive_control( [
			'id'           => 'icon_distance',
			'label'        => __( 'Icon Distance', 'jet-popup' ),
			'separator'    => 'before',
			'type'         => 'range',
			'units'        => [
				[
					'value'     => 'px',
					'intervals' => [
						'step' => 1,
						'min'  => 0,
						'max'  => 50,
					]
				],
			],
			'css_selector' => [
				'{{WRAPPER}} .jet-popup-action-button' => '--jp-ab-icon-distance:{{VALUE}}{{UNIT}};',
			],
		] );

		$this->controls_manager->add_responsive_control( [
			'id'           => 'button_hor_padding',
			'label'        => __( 'Horizontal Padding', 'jet-popup' ),
			'type'         => 'range',
			'units'        => [
				[
					'value'     => 'px',
					'intervals' => [
						'step' => 1,
						'min'  => 0,
						'max'  => 50,
					]
				],
			],
			'css_selector' => [
				'{{WRAPPER}} .jet-popup-action-button' => '--jp-ab-hor-padding:{{VALUE}}{{UNIT}};',
			],
		] );

		$this->controls_manager->add_responsive_control( [
			'id'           => 'button_ver_padding',
			'label'        => __( 'Vertical Padding', 'jet-popup' ),
			'type'         => 'range',
			'units'        => [
				[
					'value'     => 'px',
					'intervals' => [
						'step' => 1,
						'min'  => 0,
						'max'  => 50,
					]
				],
			],
			'css_selector' => [
				'{{WRAPPER}} .jet-popup-action-button' => '--jp-ab-ver-padding:{{VALUE}}{{UNIT}};',
			],
		] );

		$this->controls_manager->add_control(
			array(
				'id'             => 'button_border',
				'label'          => __( 'Border', 'jet-popup' ),
				'separator'      => 'after',
				'type'           => 'border',
				'disable_color'  => true,
				'css_selector'   => [
					'{{WRAPPER}} .jet-popup-action-button' => '--jp-ab-border-style: {{STYLE}}; --jp-ab-border-width: {{WIDTH}}; --jp-ab-border-radius: {{RADIUS}};',
				],
			)
		);

		$this->controls_manager->add_responsive_control( [
			'id'           => 'icon_distance',
			'label'        => __( 'Icon Distance', 'jet-popup' ),
			'type'         => 'range',
			'units'        => [
				[
					'value'     => 'px',
					'intervals' => [
						'step' => 1,
						'min'  => 0,
						'max'  => 50,
					]
				],
			],
			'css_selector' => [
				'{{WRAPPER}} .jet-popup-action-button' => '--jp-ab-icon-distance:{{VALUE}}{{UNIT}};',
			],
		] );

		$this->controls_manager->add_responsive_control( [
			'id'           => 'icon_size',
			'label'        => __( 'Icon Size', 'jet-popup' ),
			'type'         => 'range',
			'units'        => [
				[
					'value'     => 'px',
					'intervals' => [
						'step' => 1,
						'min'  => 0,
						'max'  => 50,
					]
				],
			],
			'css_selector' => [
				'{{WRAPPER}} .jet-popup-action-button' => '--jp-ab-icon-size:{{VALUE}}{{UNIT}};',
			],
		] );

		$this->controls_manager->add_control( [
			'id'           => 'label_typography',
			'label'        => __( 'Label Typography', 'jet-popup' ),
			'type'         => 'typography',
			'css_selector' => [
				'{{WRAPPER}} .jet-popup-action-button .jet-popup-action-button__text' => 'font-family: {{FAMILY}}; font-weight: {{WEIGHT}}; text-transform: {{TRANSFORM}}; font-style: {{STYLE}}; text-decoration: {{DECORATION}}; line-height: {{LINEHEIGHT}}{{LH_UNIT}}; letter-spacing: {{LETTERSPACING}}{{LS_UNIT}}; font-size: {{SIZE}}{{S_UNIT}};',
			],
		] );

		$this->controls_manager->end_section();

		$this->controls_manager->start_section( 'style_controls', [
			'id'          => 'section_state_styles',
			'initialOpen' => true,
			'title'       => esc_html__( 'States', 'jet-popup' ),
		] );

		$this->controls_manager->start_tabs( 'style_controls', [
			'id' => 'state_styles',
		] );

		$this->controls_manager->start_tab( 'style_controls', [
			'id'    => 'normal_state',
			'title' => esc_html__( 'Normal', 'jet-popup' ),
		] );

		$this->controls_manager->add_control( [
			'id'           => 'icon_color',
			'type'         => 'color-picker',
			'label'        => esc_html__( 'Icon Color', 'jet-menu' ),
			'css_selector' => array (
				'{{WRAPPER}} .jet-popup-action-button' => '--jp-ab-icon-color: {{VALUE}};',
			),
		] );

		$this->controls_manager->add_control( [
			'id'           => 'label_color',
			'type'         => 'color-picker',
			'label'        => esc_html__( 'Label Color', 'jet-menu' ),
			'css_selector' => array (
				'{{WRAPPER}} .jet-popup-action-button' => '--jp-ab-label-color: {{VALUE}};',
			),
		] );

		$this->controls_manager->add_control( [
			'id'           => 'bg_color',
			'type'         => 'color-picker',
			'label'        => esc_html__( 'Background Color', 'jet-menu' ),
			'css_selector' => array (
				'{{WRAPPER}} .jet-popup-action-button' => '--jp-ab-bg-color: {{VALUE}};',
			),
		] );

		$this->controls_manager->add_control( [
			'id'           => 'border_color',
			'type'         => 'color-picker',
			'label'        => esc_html__( 'Border Color', 'jet-menu' ),
			'css_selector' => array (
				'{{WRAPPER}} .jet-popup-action-button' => '--jp-ab-border-color: {{VALUE}};',
			),
		] );

		$this->controls_manager->end_tab();

		$this->controls_manager->start_tab( 'style_controls', [
			'id'    => 'hover_state',
			'title' => esc_html__( 'Hover', 'jet-popup' ),
		] );

		$this->controls_manager->add_control( [
			'id'           => 'icon_hover_color',
			'type'         => 'color-picker',
			'label'        => esc_html__( 'Icon Color', 'jet-menu' ),
			'css_selector' => array (
				'{{WRAPPER}} .jet-popup-action-button' => '--jp-ab-icon-hover-color: {{VALUE}};',
			),
		] );

		$this->controls_manager->add_control( [
			'id'           => 'label_hover_color',
			'type'         => 'color-picker',
			'label'        => esc_html__( 'Label Color', 'jet-menu' ),
			'css_selector' => array (
				'{{WRAPPER}} .jet-popup-action-button' => '--jp-ab-label-hover-color: {{VALUE}};',
			),
		] );

		$this->controls_manager->add_control( [
			'id'           => 'bg_hover_color',
			'type'         => 'color-picker',
			'label'        => esc_html__( 'Background Color', 'jet-menu' ),
			'css_selector' => array (
				'{{WRAPPER}} .jet-popup-action-button' => '--jp-ab-bg-hover-color: {{VALUE}};',
			),
		] );

		$this->controls_manager->add_control( [
			'id'           => 'border_hover_color',
			'type'         => 'color-picker',
			'label'        => esc_html__( 'Border Color', 'jet-menu' ),
			'css_selector' => array (
				'{{WRAPPER}} .jet-popup-action-button' => '--jp-ab-border-hover-color: {{VALUE}};',
			),
		] );

		$this->controls_manager->end_tab();

		$this->controls_manager->end_tabs();

		$this->controls_manager->end_section();
	}
}
