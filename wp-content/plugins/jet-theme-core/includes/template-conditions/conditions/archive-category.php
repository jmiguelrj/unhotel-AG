<?php
namespace Jet_Theme_Core\Template_Conditions;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Archive_Category extends Base {

	/**
	 * Condition slug
	 *
	 * @return string
	 */
	public function get_id() {
		return 'archive-category';
	}

	/**
	 * Condition label
	 *
	 * @return string
	 */
	public function get_label() {
		return __( 'Post categories', 'jet-theme-core' );
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
	 * @return string
	 */
	public function get_sub_group() {
		return 'post-archive';
	}

	/**
	 * @return int
	 */
	public  function get_priority() {
		return 50;
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
			'node'   => $this->get_id(),
			'parent' => 'archive-all-post',
			'inherit' => [ 'entire', 'archive-all', 'archive-all-post' ],
			'label' => __( 'Category Archives', 'jet-theme-core' ),
			'nodeInfo'  => [
				'title' => __( 'Category Taxonomy', 'jet-theme-core' ),
				'desc' => __( 'Category taxonomy archive and single post', 'jet-theme-core' ),
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

		if ( ! empty( $categories ) ) {
			$first_category = $categories[0];
			$first_category_link = get_category_link( $first_category->term_id );

			return esc_url( $first_category_link );
		}

		return false;
	}

	/**
	 * Condition check callback
	 *
	 * @return bool
	 */
	public function check( $arg = '' ) {

		if ( empty( $arg ) || 'all' === $arg ) {
			return is_category();
		}

		return is_category( $arg );
	}

}
