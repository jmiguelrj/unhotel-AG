<?php


namespace Jet_FB_Address_Autocomplete;


class GutenbergStyleControls {

	use CssSelector;

	public function register_address_controls( $manager ) {
		$manager->start_section(
			'style_controls',
			array(
				'id'    => $this->uniq_id( 'style' ),
				'title' => __( 'Addresses Dropdown', 'jet-form-builder' )
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

		$manager->add_responsive_control( [
			'id'           => $this->uniq_id( 'list_height' ),
			'type'         => 'range',
			'separator'    => 'after',
			'label'        => __( 'List Height', 'jet-form-builder' ),
			'units'        => [
				[
					'value'     => 'px',
					'intervals' => [
						'step' => 1,
						'min'  => 10,
						'max'  => 1000,
					]
				],
			],
			'css_selector' => [
				$this->selector() => 'max-height: {{VALUE}}px',
			],
			'attributes'   => array(
				'default' => array(
					'value' => 160
				),
			),
		] );

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
			'id'           => $this->uniq_id( 'background_color' ),
			'type'         => 'color-picker',
			'label'        => __( 'Background Color', 'jet-form-builder' ),
			'css_selector' => array(
				$this->selector() => 'background-color: {{VALUE}}',
			),
		) );

		$manager->end_section();

		$manager->start_section(
			'style_controls',
			array(
				'id'    => $this->uniq_id( 'item-style' ),
				'title' => __( 'Addresses Dropdown Item', 'jet-form-builder' )
			)
		);
		$manager->add_responsive_control( array(
			'id'           => $this->uniq_id( 'item-padding' ),
			'type'         => 'dimensions',
			'separator'    => 'after',
			'label'        => __( 'Padding', 'jet-form-builder' ),
			'units'        => array( 'px', '%' ),
			'css_selector' => array(
				$this->selector() => 'padding: {{TOP}} {{RIGHT}} {{BOTTOM}} {{LEFT}};',
			),
		) );

		$manager->add_responsive_control( [
			'id'           => $this->uniq_id( 'divider_height' ),
			'type'         => 'range',
			'label'        => __( 'Divider Height', 'jet-form-builder' ),
			'units'        => [
				[
					'value'     => 'px',
					'intervals' => [
						'step' => 1,
						'min'  => 0,
						'max'  => 20,
					]
				],
			],
			'css_selector' => [
				$this->selector( ' li:not(:first-child)' ) => 'border-style:solid;border-width: {{VALUE}}{{UNIT}} 0 0 0;',
			],
		] );
		$manager->add_control( array(
			'id'           => $this->uniq_id( 'divider_color' ),
			'type'         => 'color-picker',
			'label'        => __( 'Divider Color', 'jet-form-builder' ),
			'css_selector' => array(
				$this->selector( ' li:not(:first-child)' ) => 'border-color: {{VALUE}}',
			),
		) );

		$manager->start_tabs(
			'style_controls',
			[
				'id' => $this->uniq_id( 'item-tabs' ),
			]
		);
		$manager->start_tab(
			'style_controls',
			[
				'id'    => $this->uniq_id( 'item-tab-normal' ),
				'title' => __( 'Normal', 'jet-form-builder' ),
			]
		);
		$manager->add_control( array(
			'id'           => $this->uniq_id( 'item_text_color' ),
			'type'         => 'color-picker',
			'label'        => __( 'Text Color', 'jet-form-builder' ),
			'css_selector' => array(
				$this->selector( ' li' ) => 'color: {{VALUE}};',
			),
		) );
		$manager->add_control( array(
			'id'           => $this->uniq_id( 'item_bg_color' ),
			'type'         => 'color-picker',
			'label'        => __( 'Background Color', 'jet-form-builder' ),
			'css_selector' => array(
				$this->selector( ' li' ) => 'background-color: {{VALUE}};',
			),
		) );
		$manager->end_tab();
		$manager->start_tab(
			'style_controls',
			[
				'id'    => $this->uniq_id( 'item-tab-selected' ),
				'title' => __( 'Selected', 'jet-form-builder' ),
			]
		);
		$manager->add_control( array(
			'id'           => $this->uniq_id( 'item_text_color_selected' ),
			'type'         => 'color-picker',
			'label'        => __( 'Text Color', 'jet-form-builder' ),
			'css_selector' => array(
				$this->selector( ' li.selected' ) => 'color: {{VALUE}};',
			),
		) );
		$manager->add_control( array(
			'id'           => $this->uniq_id( 'item_bg_color_selected' ),
			'type'         => 'color-picker',
			'label'        => __( 'Background Color', 'jet-form-builder' ),
			'css_selector' => array(
				$this->selector( ' li.selected' ) => 'background-color: {{VALUE}};',
			),
		) );
		$manager->end_tab();
		$manager->end_tabs();
		$manager->end_section();
	}

}