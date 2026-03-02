<?php


namespace JFB\SelectAutocomplete\JetEngine;


use JFB\SelectAutocomplete\BaseAjaxHandler;

class AjaxHandler extends BaseAjaxHandler {

	public function type(): string {
		return 'jef';
	}

	public function get_field_options(): array {
		$form_data = jet_engine()->forms->editor->get_form_data( $this->form_id );

		$field = $this->find_field_by_name( $form_data );

		if ( ! $field ) {
			wp_send_json_error();
		}

		return jet_engine()->forms
			->get_form_builder( $this->form_id, $form_data )
			->get_field_options( $field['settings'] );
	}

	public function find_field_by_name( $form_data ) {
		$result = array_filter( $form_data, function ( $field ) {
			return (
				isset( $field['settings']['name'] )
				&& $field['settings']['name']
				&& $this->field_name === $field['settings']['name']
			);
		} );

		return array_shift( $result );
	}


}