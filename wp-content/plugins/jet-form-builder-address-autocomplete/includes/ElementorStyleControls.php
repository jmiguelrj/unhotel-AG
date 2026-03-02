<?php


namespace Jet_FB_Address_Autocomplete;


use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

class ElementorStyleControls {

	use CssSelector;

	public function __construct() {
		add_action(
			'elementor/element/jet-form-builder-form/section_message_error_style/after_section_end',
			array( $this, 'register_controls' ), 10, 2
		);
		add_action(
			'elementor/element/jet-engine-booking-form/form_messages_style/after_section_end',
			array( $this, 'register_controls' ), 10, 2
		);
	}

	public function register_controls( Widget_Base $element, $args ) {
		$element->start_controls_section(
			$this->uniq_id( 'section' ),
			array(
				'label' => __( 'Addresses Dropdown', 'jet-form-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$element->add_responsive_control(
			$this->uniq_id( 'padding' ),
			array(
				'label'      => __( 'Padding', 'jet-form-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					$this->selector() => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$element->add_responsive_control(
			$this->uniq_id( 'margin' ),
			array(
				'label'      => __( 'Margin', 'jet-form-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					$this->selector() => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$element->add_responsive_control(
			$this->uniq_id( 'list-height' ),
			array(
				'label'      => __( 'List Height', 'jet-form-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'separator'  => 'before',
				'description' => __( 'Note: There will be no more than 5 items in the list', 'jet-form-builder' ),
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 1000,
					),
				),
				'selectors'  => array(
					$this->selector() => 'max-height: {{SIZE}}{{UNIT}};',
				),
			)
		);
		$element->add_responsive_control(
			$this->uniq_id( 'alignment' ),
			array(
				'label'       => __( 'Alignment', 'jet-form-builder' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => false,
				'default'     => 'left',
				'separator'   => 'before',
				'options'     => array(
					'left'   => array(
						'title' => __( 'Left', 'jet-form-builder' ),
						'icon'  => 'eicon-h-align-left',
					),
					'center' => array(
						'title' => __( 'Center', 'jet-form-builder' ),
						'icon'  => 'eicon-h-align-center',
					),
					'right'  => array(
						'title' => __( 'Right', 'jet-form-builder' ),
						'icon'  => 'eicon-h-align-right',
					),
				),
				'selectors'   => array(
					$this->selector() => 'text-align: {{VALUE}};',
				),
			)
		);
		$element->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => $this->uniq_id( 'typography' ),
				'selector' => $this->selector(),
			)
		);
		$element->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => $this->uniq_id( 'border' ),
				'label'       => __( 'Border', 'jet-form-builder' ),
				'placeholder' => '1px',
				'selector'    => $this->selector(),
			)
		);
		$element->add_responsive_control(
			$this->uniq_id( 'border_radius' ),
			array(
				'label'      => __( 'Border Radius', 'jet-form-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					$this->selector() => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$element->add_control(
			$this->uniq_id( 'bg_color' ),
			array(
				'label'     => __( 'Background Color', 'jet-form-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					$this->selector() => 'background-color: {{VALUE}};',
				),
			)
		);
		$element->end_controls_section();

		$element->start_controls_section(
			$this->uniq_id( 'item-section' ),
			array(
				'label' => __( 'Addresses Dropdown Item', 'jet-form-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$element->add_control(
			$this->uniq_id( 'item-padding' ),
			array(
				'label'      => __( 'Padding', 'jet-form-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					$this->selector( ' li' ) => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$element->add_responsive_control(
			$this->uniq_id( 'item-divider' ),
			array(
				'label'      => __( 'Divider Height', 'jet-form-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'separator'  => 'before',
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 20,
					),
				),
				'selectors'  => array(
					$this->selector( ' li:not(:first-child)' ) => 'border-style:solid;border-width: {{SIZE}}{{UNIT}} 0 0 0;',
				),
			)
		);
		$element->add_control(
			$this->uniq_id( 'divider-color' ),
			array(
				'label'     => __( 'Divider Color', 'jet-form-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					$this->selector( ' li:not(:first-child)' ) => 'border-color: {{VALUE}};',
				),
			)
		);

		$element->start_controls_tabs( $this->uniq_id( 'item-tabs' ) );
		$element->start_controls_tab(
			$this->uniq_id( 'item-normal' ),
			array(
				'label' => __( 'Normal', 'jet-form-builder' ),
			)
		);

		$element->add_control(
			$this->uniq_id( 'item-color' ),
			array(
				'label'     => __( 'Text Color', 'jet-form-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					$this->selector( ' li' ) => 'color: {{VALUE}};',
				),
			)
		);
		$element->add_control(
			$this->uniq_id( 'item-bg-color' ),
			array(
				'label'     => __( 'Background Color', 'jet-form-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					$this->selector( ' li' ) => 'background-color: {{VALUE}};',
				),
			)
		);
		$element->end_controls_tab();
		$element->start_controls_tab(
			$this->uniq_id( 'item-hover' ),
			array(
				'label' => __( 'Hover', 'jet-form-builder' ),
			)
		);

		$element->add_control(
			$this->uniq_id( 'item-color-hover' ),
			array(
				'label'     => __( 'Text Color', 'jet-form-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					$this->selector( ' li.selected' ) => 'color: {{VALUE}};',
				),
			)
		);
		$element->add_control(
			$this->uniq_id( 'item-bg-color-hover' ),
			array(
				'label'     => __( 'Background Color', 'jet-form-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					$this->selector( ' li.selected' ) => 'background-color: {{VALUE}};',
				),
			)
		);
		$element->end_controls_tab();
		$element->end_controls_tabs();
		$element->end_controls_section();
	}

}