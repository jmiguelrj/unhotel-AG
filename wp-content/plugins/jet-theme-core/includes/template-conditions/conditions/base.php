<?php
namespace Jet_Theme_Core\Template_Conditions;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

abstract class Base {

	/**
	 * Condition slug
	 *
	 * @return string
	 */
	abstract public function get_id();

	/**
	 * Condition label
	 *
	 * @return string
	 */
	abstract public function get_label();

	/**
	 * Condition group
	 *
	 * @return string
	 */
	abstract public function get_group();

	/**
	 * @return mixed
	 */
	abstract public function get_priority();

	/**
	 * @return mixed
	 */
	abstract public function get_body_structure();

	/**
	 * @return false
	 */
	public function get_sub_group() {
		return false;
	}

	/**
	 * Returns parent codition ID for current condition
	 *
	 * @return array
	 */
	public function get_node_data() {
		return false;
	}

	/**
	 * Returns parent codition ID for current condition
	 *
	 * @return array
	 */
	public function get_control() {
		return false;
	}

	/**
	 * Returns parent codition ID for current condition
	 *
	 * @return array
	 */
	public function get_arg_control() {
		return false;
	}

	/**
	 * Returns parent codition ID for current condition
	 *
	 * @return array
	 */
	public function get_avaliable_options() {
		return false;
	}

	/**
	 * [action description]
	 * @return [type] [description]
	 */
	public function ajax_action() {
		return false;
	}

	/**
	 * Returns human-reading information about active arguments for condition
	 *
	 * @param  arrayu $args
	 * @return string
	 */
	public function verbose_args( $args ) {
		return '';
	}

	/**
	 * [get_label_by_value description]
	 * @param  [type] $value [description]
	 * @return [type]        [description]
	 */
	public function get_label_by_value( $value ) {
		return '';
	}

	/**
	 * @return array
	 */
	public function get_base_condition() {
		return [
			'group' => $this->get_group(),
			'subGroup' => $this->get_sub_group(),
		];
	}

	/**
	 * Condition check callback
	 *
	 * @return bool
	 */
	abstract public function check( $args );

}
