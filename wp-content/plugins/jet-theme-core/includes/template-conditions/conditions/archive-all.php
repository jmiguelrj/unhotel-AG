<?php
namespace Jet_Theme_Core\Template_Conditions;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Archive_All extends Base {

	/**
	 * Condition slug
	 *
	 * @return string
	 */
	public function get_id() {
		return 'archive-all';
	}

	/**
	 * Condition label
	 *
	 * @return string
	 */
	public function get_label() {
		return __( 'All Archives', 'jet-theme-core' );
	}

	/**
	 * Condition group
	 *
	 * @return string
	 */
	public function get_group() {
		return 'archive';
	}

	/**
	 * @return int
	 */
	public  function get_priority() {
		return 10;
	}

	/**
	 * @return string
	 */
	public function get_body_structure() {
		return 'jet_archive';
	}

	/**
	 * @return array
	 */
	public function get_node_data() {
		return [
			'node'      => $this->get_id(),
			'parent'    => 'entire',
			'inherit' => [ 'entire' ],
			'label' => __( 'All Archives', 'jet-theme-core' ),
			'nodeInfo'  => [
				'title' => __( 'All Archives', 'jet-theme-core' ),
				'desc'  => __( 'Templates for all site archives', 'jet-theme-core' ),
			]
		];
	}

	/**
	 * Condition check callback
	 *
	 * @return bool
	 */
	public function check( $args ) {
		return is_archive();
	}

}
