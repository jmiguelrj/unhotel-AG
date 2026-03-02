<?php
namespace Jet_Theme_Core\Template_Conditions;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class WP_Option extends Base {

	/**
	 * Condition slug
	 *
	 * @return string
	 */
	public function get_id() {
		return 'wp-option';
	}

	/**
	 * Condition label
	 *
	 * @return string
	 */
	public function get_label() {
		return __( 'WP Option', 'jet-theme-core' );
	}

	/**
	 * Condition group
	 *
	 * @return string
	 */
	public function get_group() {
		return 'advanced';
	}

	/**
	 * @return int
	 */
	public  function get_priority() {
		return 95;
	}

	/**
	 * @return string
	 */
	public function get_body_structure() {
		return 'jet_page';
	}

	/**
	 * [get_control description]
	 * @return [type] [description]
	 */
	public function get_control() {
		return [
			'type'        => 'f-search-select',
			'multiple'    => false,
			'placeholder' => __( 'Select option', 'jet-theme-core' ),
		];
	}

	/**
	 * [get_control description]
	 * @return [type] [description]
	 */
	public function get_arg_control() {
		return [
			'type'        => 'input',
			'placeholder' => __( 'Enter value', 'jet-theme-core' ),
			'options'     => false,
			'action'      => false,
		];
	}

	/**
	 * [ajax_action description]
	 * @return [type] [description]
	 */
	public function ajax_action() {
		return [
			'action' => 'get-wp-options',
			'params' => [],
		];
	}

	/**
	 * [get_label_by_value description]
	 * @param  string $value [description]
	 * @return [type]        [description]
	 */
	public function get_label_by_value( $value = '' ) {
		return $value;
	}

	/**
	 * Condition check callback
	 *
	 * @return bool
	 */
	public function check( $arg = '', $sub_group = false, $sub_group_arg = false ) {

		if ( is_array( $arg ) ) {
			$arg = implode(', ', $arg );
		}

		$option_value = get_option( $arg, false );

		if ( ! $option_value ) {
			return false;
		}

		return $option_value === $sub_group_arg ? true : false;
	}

}