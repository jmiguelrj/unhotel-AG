<?php


namespace Jet_FB_HR_Select;

use Jet_FB_HR_Select\JetFormBuilder\Blocks\HrSelect;

abstract class BaseAjaxHandler {

	protected $termID;
	protected $form_id;
	protected $parent_field_name;
	protected $level;

	protected $instances = array();

	const PREFIX = 'jet_forms_hr_select_query__';

	abstract public function type(): string;

	abstract public function get_field_settings( $field_name ): array;

	abstract public function create_field_instance();

	public function __construct() {
		$action    = self::PREFIX . $this->type();
		$add_terms = 'jet_fb_hr_select_add_terms';

		add_action(
			"wp_ajax_{$action}",
			function () {
				$this->on_request( array( $this, 'get_response' ) );
			}
		);
		add_action(
			"wp_ajax_nopriv_{$action}",
			function () {
				$this->on_request( array( $this, 'get_response' ) );
			}
		);

		add_action(
			"wp_ajax_{$add_terms}",
			function () {
				$this->on_request( array( $this, 'add_terms' ) );
			}
		);

		add_action(
			"wp_ajax_nopriv_{$add_terms}",
			function () {
				$this->on_request( array( $this, 'add_terms' ) );
			}
		);
	}

	public function on_request( $callback ) {
		$this->form_id = absint( $_POST['formID'] );
		$options       = call_user_func( $callback );

		wp_send_json_success( $options );
	}

	protected function get_settings( $field_name ): array {
		$settings = $this->get_field_settings( $field_name );

		if ( ! $settings ) {
			wp_send_json_error(
				'Undefined settings',
				array(
					$this->form_id,
					$field_name,
				)
			);
		}

		return $settings;
	}

	protected function get_instance_with_settings( $field_name ) {
		if ( isset( $this->instances[ $field_name ] ) ) {
			return $this->instances[ $field_name ];
		}
		$settings = $this->get_settings( $field_name );

		$instance = $this->create_field_instance();
		$instance->set_block_data( $settings );
		$instance->set_preset();

		$this->instances[ $field_name ] = $instance;

		return $instance;
	}

	public function get_response(): array {
		$instance = $this->get_instance_with_settings( esc_attr( $_POST['parentFieldName'] ) );

		$termID = absint( $_POST['termID'] );
		$level  = absint( $_POST['level'] );

		$terms = $instance->query_terms( array( 'parent' => $termID ) );

		if ( ! $terms ) {
			wp_send_json_error();
		}

		return $instance->with_placeholder( $terms, ++ $level );
	}

	public function add_terms() {
		$terms        = wp_unslash( $_POST['terms'] );
		$default_args = array(
			'parent' => 0,
		);
		$inserted     = array();
		$terms_ids    = array();

		/** @var BaseHrSelectField|HrSelect $instance */

		foreach ( $terms as $term ) {
			$parent_field = sanitize_text_field( $term['parentFieldName'] ?? '' );
			$level        = absint( $term['level'] ?? '' );

			if ( ! $parent_field || ! $level ) {
				continue;
			}

			$instance = $this->get_instance_with_settings( $parent_field );

			$inserted_term = wp_insert_term(
				$term['term'],
				$term['taxonomy'],
				array_merge( $default_args, $term['args'] ?? array() )
			);

			if ( is_wp_error( $inserted_term ) ) {
				continue;
			}

			$term_id = $inserted_term['term_id'] ?? 0;

			$default_args['parent'] = $term_id;

			$inserted[ $parent_field ][] = $level;

			if ( ! is_array( $instance->block_attrs['default'] ) ) {
				$instance->block_attrs['default'] = array();
			}
			$instance->block_attrs['default'][ $level ] = $term_id;
		}

		$prepared  = array();
		$all_terms = array();

		foreach ( $inserted as $parent_field_name => $levels ) {
			$instance = $this->get_instance_with_settings( $parent_field_name );

			if ( empty( $all_terms ) ) {
				$all_terms = $instance->query_terms_with_levels( true );
			}

			if ( ! $all_terms ) {
				continue;
			}

			$prepared[ $parent_field_name ] = $instance->with_placeholder( $all_terms, $levels );
		}

		$filtered_terms = array();

		foreach ( $prepared as $parent_field_name => $levels_in_field ) {
			$instance = $this->get_instance_with_settings( $parent_field_name );

			foreach ( $levels_in_field as $current_level => $terms_on_level ) {
				foreach ( $terms_on_level as $current_term ) {

					if ( ! isset( $current_term['data-term-id'] ) ) { // skip, if it's a placeholder
						$filtered_terms[ $parent_field_name ][ $current_level ][] = $current_term;
						continue;
					}
					if ( in_array( $current_term['data-term-id'], $instance->block_attrs['default'], true ) ) {
						$filtered_terms[ $parent_field_name ][ $current_level ][] = $current_term;
					}
				}
			}
		}

		if ( ! $filtered_terms ) {
			wp_send_json_error();
		}

		return $filtered_terms;
	}


}
