<?php


namespace Jet_FB_HR_Select\JetFormBuilder\Blocks;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use Jet_Form_Builder\Exceptions\Parse_Exception;
use Jet_Form_Builder\Request\Field_Data_Parser;

/**
 * @deprecated 1.0.3
 *
 * Class HrSelectParser
 * @package Jet_FB_HR_Select\JetFormBuilder\Blocks
 */
class HrSelectParser extends Field_Data_Parser {

	public function type() {
		return 'hr-select';
	}

	public function parse_value( $value ) {
		$request  = jet_form_builder()->form_handler->request_handler->get_request();
		$levels   = $this->settings['levels'] ?? array();
		$response = array();

		foreach ( $levels as $level ) {
			if ( ! isset( $request[ $level['name'] ] ) ) {
				continue;
			}
			$response[ $level['name'] ] = $request[ $level['name'] ];
		}

		return $response;
	}

	/**
	 * @throws Parse_Exception
	 */
	public function get_response() {
		throw new Parse_Exception( 'Throw hr_select computed fields', $this->value );
	}
}
