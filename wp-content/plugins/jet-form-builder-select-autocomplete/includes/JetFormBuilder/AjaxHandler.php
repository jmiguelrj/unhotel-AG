<?php


namespace JFB\SelectAutocomplete\JetFormBuilder;

use JFB\SelectAutocomplete\BaseAjaxHandler;
use Jet_Form_Builder\Blocks\Types\Select_Field;
use Jet_Form_Builder\Exceptions\Repository_Exception;
use Jet_Form_Builder\Plugin as JFBPlugin;

class AjaxHandler extends BaseAjaxHandler {

	public function type(): string {
		return 'jfb';
	}

	/**
	 * @return array
	 * @throws Repository_Exception
	 */
	public function get_field_options(): array {
		$field = JFBPlugin::instance()->form->get_field_by_name( $this->form_id, $this->field_name );

		if ( ! $field ) {
			wp_send_json_error( array( 'error' => 'not_found_field' ) );
		}

		$block = JFBPlugin::instance()->blocks->get_field_by_name( $field['blockName'] );

		if ( ! $block ) {
			wp_send_json_error( array( 'error' => 'not_found_block' ) );
		}
		/** @var Select_Field $block */
		$block->set_block_data( $field['attrs'] );

		/**
		 * Compatibility with JFB <= 3.3.1
		 */
		if ( ! is_a( $block, '\JFB_Modules\Option_Field\Interfaces\Support_Option_Query_It' ) ) {
			return $block->get_field_options();
		}

		if ( $block->get_query()->is_support_feature( 'search' ) ) {
			$this->set_need_to_filter( false );
		}

		$block->get_query()->set_query( 'search', $this->search );

		return iterator_to_array( $block->get_query()->fetch() );
	}


}
