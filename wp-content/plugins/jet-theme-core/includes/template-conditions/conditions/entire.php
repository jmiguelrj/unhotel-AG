<?php
namespace Jet_Theme_Core\Template_Conditions;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Entire extends Base {

	/**
	 * Condition slug
	 *
	 * @return string
	 */
	public function get_id() {
		return 'entire';
	}

	/**
	 * Condition label
	 *
	 * @return string
	 */
	public function get_label() {
		return __( 'Entire Site', 'jet-theme-core' );
	}

	/**
	 * Condition group
	 *
	 * @return string
	 */
	public function get_group() {
		return 'entire';
	}

	/**
	 * @return int
	 */
	public  function get_priority() {
		return 100;
	}

	/**
	 * @return string
	 */
	public function get_body_structure() {
		return 'jet_page';
	}

	/**
	 * @return array
	 */
	public function get_node_data() {
		return [
			'node' => $this->get_id(),
			'parent' => false,
			'label' => __( 'Entire', 'jet-theme-core' ),
			'nodeInfo'  => [
				'title' => __( 'Entire Site', 'jet-theme-core' ),
				'desc' => __( 'Description Entire Site', 'jet-theme-core' ),
			],
			'previewLink' => get_home_url(),
		];
	}

	/**
	 * Condition check callback
	 *
	 * @return bool
	 */
	public function check( $arg = '' ) {
		return true;
	}

}
