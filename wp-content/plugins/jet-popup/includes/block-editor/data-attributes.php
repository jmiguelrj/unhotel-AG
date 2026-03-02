<?php
namespace Jet_Popup;

if ( ! defined( 'WPINC' ) ) {
	die;
}

class Data_Attributes {

	private $_attributes = [];

	public function register_attributes( $attributes = [] ) {

		foreach ( $attributes as $attribute ) {
			$this->register_attribute( $attribute );
		}

		do_action( 'jet-popup/data-attributes/register', $this );

	}

	/**
	 * Attribute example
	 * $attribute = array(
	 *     'name'        => 'jetPopupCustomSelector',
	 *     'type'        => 'text',
	 *     'dataType'    => 'text',
	 *     'dataAttr'    => 'data-popup-custom-selector',
	 *     'options'     => array or callback (for select types),
	 *     'default'     => '',
	 *     'label'       => __( 'Custom Selector' ),
	 *     'description' => __( 'Enter the css selector used in the block HTML' ),
	 *     'condition'   => array(
	 *         'jetPopupInstance!'   => 'none',
	 *         'jetPopupTriggerType' => 'click-selector',
	 *     ),
	 * );
	 * @param  array  $attribute [description]
	 * @return [type]            [description]
	 */
	public function register_attribute( $attribute = [] ) {

		if ( empty( $attribute['name'] ) ) {
			return;
		}

		$attribute = wp_parse_args( $attribute, [
			'dataType' => 'string',
			'default'  => '',
		] );

		$this->_attributes[ $attribute['name'] ] = $attribute;
	}

	/**
	 * Return data attributes array formatted to use as localized data
	 * 
	 * @return [type] [description]
	 */
	public function to_localized_data() {
		return array_map( function( $attr ) {
			
			if ( isset( $attr['options'] ) && is_callable( $attr['options'] ) ) {
				$attr['options'] = call_user_func( $attr['options'] );
			}

			return $attr;

		}, $this->_attributes );
	}

	/**
	 * Return data attributes array formatted to use as bock attibutes
	 * 
	 * @return [type] [description]
	 */
	public function to_block_attrs() {
		
		$result = [];

		foreach ( $this->_attributes as $attr => $data ) {
			$result[ $attr ] = [
				'type'    => $data['dataType'],
				'default' => $data['default'],
			];
		}

		return $result;

	}

	/**
	 * Add attributed into HTML with selected way (callback to set exact attribute is given from outside)
	 * @param  array   $saved_attrs      [description]
	 * @param  boolean $process_callback [description]
	 * @return [type]                    [description]
	 */
	public function process_attributes( $saved_attrs = [], $process_callback = false ) {

		if ( ! is_callable( $process_callback ) ) {
			return;
		}

		foreach ( $this->_attributes as $attr => $data ) {
			$value = isset( $saved_attrs[ $attr ] ) ? $saved_attrs[ $attr ] : $data['default'];
			call_user_func( $process_callback, $data['dataAttr'], $value );
		}

	}

}
