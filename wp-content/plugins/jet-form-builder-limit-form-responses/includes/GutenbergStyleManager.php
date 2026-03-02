<?php


namespace JFB\LimitResponses;



class GutenbergStyleManager {

	use CssSelector;

	public function __construct() {
		add_action(
			'jet-sm/controls/jet-forms/form-block/error_style/after_end',
			array( $this, 'add_limit_form_controls' )
		);
	}

	public function add_limit_form_controls( $manager ) {
		$manager->start_section(
			'style_controls',
			array(
				'id'    => $this->uniq_id( 'style' ),
				'title' => __( 'Limit Form Message', 'jet-form-builder' )
			)
		);

		$manager->add_responsive_control( array(
			'id'           => $this->uniq_id( 'padding' ),
			'type'         => 'dimensions',
			'separator'    => 'after',
			'label'        => __( 'Padding', 'jet-form-builder' ),
			'units'        => array( 'px', '%' ),
			'css_selector' => array(
				$this->selector() => 'padding: {{TOP}} {{RIGHT}} {{BOTTOM}} {{LEFT}};',
			),
		) );

		$manager->add_responsive_control( array(
			'id'           => $this->uniq_id( 'margin' ),
			'type'         => 'dimensions',
			'separator'    => 'after',
			'label'        => __( 'Margin', 'jet-form-builder' ),
			'units'        => array( 'px', '%' ),
			'css_selector' => array(
				$this->selector() => 'margin: {{TOP}} {{RIGHT}} {{BOTTOM}} {{LEFT}};',
			),
		) );

		$manager->add_responsive_control( array(
			'id'           => $this->uniq_id( 'alignment' ),
			'type'         => 'choose',
			'label'        => __( 'Alignment', 'jet-form-builder' ),
			'separator'    => 'after',
			'options'      => [
				'left'   => [
					'shortcut' => __( 'Left', 'jet-form-builder' ),
					'icon'     => 'dashicons-editor-alignleft',
				],
				'center' => [
					'shortcut' => __( 'Center', 'jet-form-builder' ),
					'icon'     => 'dashicons-editor-aligncenter',
				],
				'right'  => [
					'shortcut' => __( 'Right', 'jet-form-builder' ),
					'icon'     => 'dashicons-editor-alignright',
				],
			],
			'css_selector' => [
				$this->selector() => 'text-align: {{VALUE}};',
			]
		) );

		$manager->add_control( array(
			'id'           => $this->uniq_id( 'typography' ),
			'type'         => 'typography',
			'separator'    => 'after',
			'css_selector' => [
				$this->selector() => 'font-family: {{FAMILY}}; font-weight: {{WEIGHT}}; text-transform: {{TRANSFORM}}; font-style: {{STYLE}}; text-decoration: {{DECORATION}}; line-height: {{LINEHEIGHT}}{{LH_UNIT}}; letter-spacing: {{LETTERSPACING}}{{LS_UNIT}}; font-size: {{SIZE}}{{S_UNIT}};',

			],
		) );

		$manager->add_control( array(
			'id'           => $this->uniq_id( 'border' ),
			'type'         => 'border',
			'separator'    => 'after',
			'label'        => __( 'Border', 'jet-form-builder' ),
			'css_selector' => array(
				$this->selector() => 'border-style:{{STYLE}};border-width:{{WIDTH}};border-radius:{{RADIUS}};border-color:{{COLOR}};',
			),
		) );

		$manager->add_control( array(
			'id'           => $this->uniq_id( 'color' ),
			'type'         => 'color-picker',
			'label'        => __( 'Text Color', 'jet-form-builder' ),
			'separator'    => 'after',
			'css_selector' => array(
				$this->selector() => 'color: {{VALUE}}',
			),
		) );

		$manager->add_control( array(
			'id'           => $this->uniq_id( 'background_color' ),
			'type'         => 'color-picker',
			'label'        => __( 'Background Color', 'jet-form-builder' ),
			'css_selector' => array(
				$this->selector() => 'background-color: {{VALUE}}',
			),
		) );

		$manager->end_section();
	}


}