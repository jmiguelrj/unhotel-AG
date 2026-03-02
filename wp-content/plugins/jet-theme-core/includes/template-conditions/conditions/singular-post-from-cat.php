<?php
namespace Jet_Theme_Core\Template_Conditions;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Post_From_Category extends Base {

	/**
	 * Condition slug
	 *
	 * @return string
	 */
	public function get_id() {
		return 'singular-post-from-cat';
	}

	/**
	 * Condition label
	 *
	 * @return string
	 */
	public function get_label() {
		return __( 'In Post categories', 'jet-theme-core' );
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
			'parent' => 'archive-category',
			'inherit' => [ 'entire', 'singular-post' ],
			'label'  => __( 'Post categories Single', 'jet-theme-core' ),
			'previewLink' => $this->get_preview_link(),
		];
	}

	/**
	 * [get_control description]
	 * @return [type] [description]
	 */
	public function get_control() {
		return [
			'type'        => 'select',
			'placeholder' => __( 'Select category', 'jet-theme-core' ),
		];
	}

	/**
	 * [ajax_action description]
	 * @return [type] [description]
	 */
	public function ajax_action() {
		return [
			'action' => 'get-post-categories',
			'params' => []
		];
	}

	/**
	 * [get_label_by_value description]
	 * @param  string $value [description]
	 * @return [type]        [description]
	 */
	public function get_label_by_value( $value = '' ) {

		$terms = get_terms( array(
			'include'    => $value,
			'taxonomy'   => 'category',
			'hide_empty' => false,
		) );

		$label = '';

		if ( ! empty( $terms ) ) {
			foreach ( $terms as $key => $term ) {
				$label .= $term->name;
			}
		}

		return $label;
	}

	/**
	 * @return string
	 */
	public function get_preview_link() {

		$categories = get_terms( [
			'taxonomy'   => 'category',
			'hide_empty' => false,
		] );

		if ( empty( $categories ) ) {
			return false;
		}

		$first_category = $categories[0];
		$first_post = get_posts( [
			'category' => $first_category->term_id,
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

		if ( empty( $arg ) ) {
			return false;
		}

		if ( ! is_single() ) {
			return false;
		}

		global $post;

		return in_category( $arg, $post );
	}

}
