<?php
namespace Jet_Theme_Core\Template_Conditions;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Post extends Base {

	/**
	 * Condition slug
	 *
	 * @return string
	 */
	public function get_id() {
		return 'singular-post';
	}

	/**
	 * Condition label
	 *
	 * @return string
	 */
	public function get_label() {
		return __( 'Posts', 'jet-theme-core' );
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
		return 'post-singular';
	}

	/**
	 * @return int
	 */
	public  function get_priority() {
		return 30;
	}

	/**
	 * @return string
	 */
	public function get_body_structure() {
		return 'jet_single';
	}

	/**
	 * @return array
	 */
	public function get_node_data() {
		return [
			'node'   => $this->get_id(),
			'parent' => 'archive-all-post',
			'inherit' => [ 'entire' ],
			'subNode' => false,
			'label' => __( 'Single', 'jet-theme-core' ),
			'nodeInfo'  => [
				'title'     => __( 'Single Post', 'jet-theme-core' ),
				'desc'      => __( 'Templates for single post', 'jet-theme-core' ),
			],
			'previewLink' => $this->get_preview_link(),
		];
	}

	/**
	 * [get_control description]
	 * @return [type] [description]
	 */
	public function get_control() {
		return [
			'type'        => 'f-search-select',
			'placeholder' => __( 'Select Post', 'jet-theme-core' ),
		];
	}

	/**
	 * [ajax_action description]
	 * @return [type] [description]
	 */
	public function ajax_action() {
		return [
			'action' => 'get-posts',
			'params' => [
				'post_type' => 'post',
				'query'     => '',
			]
		];
	}


	/**
	 * [get_label_by_value description]
	 * @param  string $value [description]
	 * @return [type]        [description]
	 */
	public function get_label_by_value( $value = '' ) {
		return get_the_title( $value );
	}

	/**
	 * @return string
	 */
	public function get_preview_link() {
		$first_post = get_posts( [
			'numberposts' => 1,
			'orderby'     => 'date',
			'order'       => 'ASC',
		] );

		if ( ! empty( $first_post ) ) {
			return esc_url( get_permalink( $first_post[0]->ID ) );
		}

		return false;
	}

	/**
	 * Condition check callback
	 *
	 * @return bool
	 */
	public function check( $arg = '' ) {

		$arg = ! is_array( $arg ) ? explode(' ', $arg ) : $arg;

		if ( empty( $arg ) || in_array( 'all', $arg ) ) {
			return is_singular() && 'post' === get_post_type();
		}

		foreach ( $arg as $id ) {
			$is_single = is_single( $id );

			if ( $is_single ) {
				return true;
			}
		}

		return false;
	}

}
