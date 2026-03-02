<?php
namespace Jet_Theme_Core\Template_Conditions;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Post_From_Tag extends Base {

	/**
	 * Condition slug
	 *
	 * @return string
	 */
	public function get_id() {
		return 'singular-post-from-tag';
	}

	/**
	 * Condition label
	 *
	 * @return string
	 */
	public function get_label() {
		return __( 'In Post tags', 'jet-theme-core' );
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
			'parent' => 'archive-tag',
			'inherit' => [ 'entire', 'singular-post' ],
			'label' => __( 'Post tags Single', 'jet-theme-core' ),
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
			'placeholder' => __( 'Select tag', 'jet-theme-core' ),
		];
	}

	/**
	 * [ajax_action description]
	 * @return [type] [description]
	 */
	public function ajax_action() {
		return [
			'action' => 'get-post-tags',
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
			'taxonomy'   => 'post_tag',
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
		$tags = get_terms( array(
			'taxonomy'   => 'post_tag',
			'hide_empty' => false,
		) );

		if ( empty( $tags ) ) {
			return false;
		}

		$first_tag = $tags[0];

		$tag_query = new \WP_Query( [
			'tag_id'         => $first_tag->term_id,
			'posts_per_page' => 1,
			'orderby'     => 'date',
			'order'       => 'ASC',
		] );

		$first_post = $tag_query->posts;

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

		if ( 'all' === $arg ) {
			return has_tag( '', $post );
		}

		return has_tag( $arg, $post );
	}

}
