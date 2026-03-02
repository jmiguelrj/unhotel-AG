<?php
namespace Jet_Theme_Core\Template_Conditions;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Front_Page extends Base {

	/**
	 * Condition slug
	 *
	 * @return string
	 */
	public function get_id() {
		return 'singular-front-page';
	}

	/**
	 * Condition label
	 *
	 * @return string
	 */
	public function get_label() {
		return __( 'Front Page', 'jet-theme-core' );
	}

	/**
	 * Condition group
	 *
	 * @return string
	 */
	public function get_group() {
		return 'singular';
	}

	/**
	 * @return string
	 */
	public function get_sub_group() {
		return 'page-singular';
	}

	/**
	 * @return int
	 */
	public  function get_priority() {
		return 70;
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
			'node'    => $this->get_id(),
			'parent'  => 'entire',
			'inherit' => [ 'entire' ],
			'subNode' => true,
			'label'   => $this->get_label(),
			'previewLink' => get_home_url(),
		];
	}

	/**
	 * Condition check callback
	 *
	 * @return bool
	 */
	public function check( $arg = '' ) {
		return is_front_page();
	}

}
