<?php


namespace Jet_FB_HR_Select;

trait BaseRenderHrSelect {

	public function _preset_attributes_map() {
		return array(
			'class'           => array( 'jet-form-builder__field-wrap', $this->get_arg( 'class_name', '' ) ),
			'data-field-name' => $this->get_arg( 'name', 'field_name' ),
		);
	}

	private function prepare_attributes( $item ) {
		$prepared = array();
		$exclude  = array( 'data-term-level' );

		foreach ( $item as $attr_name => $attr_value ) {
			if ( false !== $attr_value && ! in_array( $attr_name, $exclude, true ) ) {
				$prepared[] = "{$attr_name}=\"{$attr_value}\"";
			}
		}

		return implode( ' ', $prepared );
	}


	public function get_options_string( $options ) {
		return implode(
			"\n",
			array_map(
				function ( $item ) {
					$item_attrs = $this->prepare_attributes( $item );

					return sprintf( '<option %1$s>%2$s</option>', $item_attrs, $item['label'] );

				},
				$options
			)
		);
	}

	public function get_select_levels_string( $levels ) {
		return implode(
			"\n",
			array_map(
				function ( $level, $level_index ) {
					$name           = $level['name'] ?? '';
					$placeholder    = $level['placeholder'] ?? '';
					$preset_options = $level['options'] ?? array();
					$display_input  = $level['display_input'] ?? false;
					$initial_text   = ( $level['_initial_text'] ?? false ) && $display_input;

					$options = array();

					if ( $placeholder ) {
						$options[] = HrSelectEditor::prepare_placeholder( $placeholder );
					}

					$attributes = array(
						'type'               => $initial_text ? 'text' : false,
						'placeholder'        => ( $initial_text && $placeholder ) ? $placeholder : false,
						'name'               => $this->get_field_name( $name ),
						'required'           => $this->get_required_val() ?: false,
						'class'              => "{$this->main_field_class()} jet-form-builder-hr-select select-field",
						'data-field-name'    => $name,
						'data-display-input' => $display_input,
						'data-placeholder'   => $placeholder ?: false,
						'data-taxonomy'      => $this->get_arg( 'parent_taxonomy' ),
					);

					$if_empty_child = $this->get_arg( 'if_empty_child', '' );
					if ($if_empty_child !== '') {
						$attributes['data-if_empty_child'] = $if_empty_child;
					}

					$field_template = $initial_text
						? sprintf(
							'<input %s />',
							$this->prepare_attributes( $attributes )
						)
						: sprintf(
							'<select %1$s>%2$s</select>',
							$this->prepare_attributes( $attributes ),
							$this->get_options_string( ( $options + $preset_options ) )
						);

					$field = $this->include_layout(
						$field_template,
						array(
							$level['label'] ?? '',
							$level['desc'] ?? '',
						)
					);

					return sprintf(
						'<div class="jet-form-builder-hr-select-level" data-level="%1$s" data-jfb-sync>%2$s</div>',
						$level_index,
						$field
					);

				},
				array_values( $levels ),
				array_keys( $levels )
			)
		);
	}

	public function render_field( $attrs_string ) {
		// jfb 3.0.0
		if ( class_exists( '\Jet_Form_Builder\Blocks\Validation' ) ) {
			wp_enqueue_script(
				Plugin::instance()->slug . '-jfb',
				Plugin::instance()->plugin_url( 'assets/dist/jfb.frontend.js' ),
				array( 'jet-form-builder-frontend-forms' ),
				Plugin::instance()->get_version(),
				true
			);
		}

		wp_enqueue_style(
			Plugin::instance()->slug,
			Plugin::instance()->plugin_url( 'assets/css/frontend.css' ),
			array(),
			Plugin::instance()->get_version()
		);

		wp_enqueue_script(
			Plugin::instance()->slug,
			Plugin::instance()->plugin_url( 'assets/js/frontend.js' ),
			array(
				'jet-form-builder-frontend-forms'
			),
			Plugin::instance()->get_version(),
			true
		);
		wp_localize_script(
			Plugin::instance()->slug,
			'JetFormHrSelectSettings',
			array(
				'url'    => esc_url( admin_url( 'admin-ajax.php' ) ),
				'action' => BaseAjaxHandler::PREFIX,
			)
		);

		$levels = $this->get_select_levels_string( $this->get_arg( 'levels', array() ) );

		return "<div {$attrs_string}>
			{$levels}
		</div>";
	}

}
