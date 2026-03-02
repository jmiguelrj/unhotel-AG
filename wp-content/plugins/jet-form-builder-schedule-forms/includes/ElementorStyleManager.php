<?php


namespace JFB\ScheduleForms;


use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

class ElementorStyleManager {

	use CssSelector;

	public function __construct() {
		add_action(
			'elementor/element/jet-form-builder-form/section_message_error_style/after_section_end',
			array( $this, 'add_schedule_form_controls' ), 10, 2
		);
		add_action(
			'elementor/element/jet-engine-booking-form/form_messages_style/after_section_end',
			array( $this, 'add_schedule_form_controls' ), 10, 2
		);
	}

	public function add_schedule_form_controls( Widget_Base $element, $args ) {
		$element->start_controls_section(
			$this->uniq_id( 'section' ),
			array(
				'label' => __( 'Schedule Form Message', 'jet-form-builder-schedule-forms' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$element->add_responsive_control(
			$this->uniq_id( 'padding' ),
			array(
				'label'      => __( 'Padding', 'jet-form-builder-schedule-forms' ),
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
				'label'      => __( 'Margin', 'jet-form-builder-schedule-forms' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					$this->selector() => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
			$this->uniq_id( 'color' ),
			array(
				'label'     => __( 'Text Color', 'jet-form-builder-schedule-forms' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					$this->selector() => 'color: {{VALUE}};',
				),
			)
		);
		$element->add_control(
			$this->uniq_id( 'bg_color' ),
			array(
				'label'     => __( 'Background Color', 'jet-form-builder-schedule-forms' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					$this->selector() => 'background-color: {{VALUE}};',
				),
			)
		);
		$element->end_controls_section();
	}

}