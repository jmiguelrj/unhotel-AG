<?php

namespace JFB\SelectAutocomplete\Compatibility\Elementor;

use Elementor\Controls_Stack;
use Elementor\Plugin;

class Module {

	public function __construct() {
		$this->init_hooks();
	}

	public function inject_additional_selector( Controls_Stack $element, array $args ) {
		$controls = array(
			'fields_typography_font_family',
			'fields_typography_font_size',
			'fields_typography_font_weight',
			'fields_typography_font_style',
			'fields_typography_letter_spacing',
			'fields_typography_text_transform',
			'fields_typography_text_decoration',
			'fields_typography_letter_spacing',
			'fields_color',
			'fields_background_color',
			'fields_padding',
			'fields_margin',
			'fields_border_radius',
			'fields_width',
		);

		foreach ( $controls as $control ) {
			$this->add_selection_selector( $control, $element );
		}

		// ----------

		$controls = array(
			'fields_typography_line_height',
		);

		foreach ( $controls as $control ) {
			$this->add_selection_rendered_selector( $control, $element );
		}
	}

	public function init_hooks() {
		add_action(
			'elementor/element/jet-form-builder-form/section_form_input_fields/after_section_end',
			array( $this, 'inject_additional_selector' ),
			10,
			2
		);
	}

	protected function add_selection_selector( string $control_name, Controls_Stack $element ) {
		$old_control_data = Plugin::instance()->controls_manager->get_control_from_stack(
			$element->get_unique_name(),
			$control_name
		);
		list( $selector ) = array_keys( $old_control_data['selectors'] );

		$element->update_control(
			$control_name,
			array(
				'selectors' => array(
					$selector . ',{{WRAPPER}} .select2-selection.select2-selection' => $old_control_data['selectors'][ $selector ],
				),
			)
		);
	}

	protected function add_selection_rendered_selector( string $control_name, Controls_Stack $element ) {
		$old_control_data = Plugin::instance()->controls_manager->get_control_from_stack(
			$element->get_unique_name(),
			$control_name
		);
		list( $selector ) = array_keys( $old_control_data['selectors'] );

		$element->update_control(
			$control_name,
			array(
				'selectors' => array(
					$selector . ',{{WRAPPER}} .select2-selection__rendered' => $old_control_data['selectors'][ $selector ],
				),
			)
		);
	}

}