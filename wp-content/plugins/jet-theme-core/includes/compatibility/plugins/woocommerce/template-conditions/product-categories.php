<?php
namespace Jet_Theme_Core\Template_Conditions;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Woo_Product_Categories extends Base {

	/**
	 * Condition slug
	 *
	 * @return string
	 */
	public function get_id() {
		return 'woo-product-categories';
	}

	/**
	 * Condition label
	 *
	 * @return string
	 */
	public function get_label() {
		return __( 'Product Categories', 'jet-theme-core' );
	}

	/**
	 * Condition group
	 *
	 * @return string
	 */
	public function get_group() {
		return 'woocommerce';
	}

	/**
	 * @return string
	 */
	public function get_sub_group() {
		return 'woocommerce-archive';
	}

	/**
	 * @return int
	 */
	public  function get_priority() {
		return 8;
	}

	/**
	 * @return string
	 */
	public function get_body_structure() {
		return 'jet_products_archive';
	}

	/**
	 * @return array
	 */
	public function get_node_data() {
		return [
			'node'   => $this->get_id(),
			'parent' => 'woo-all-products-archives',
			'inherit' => [ 'entire', 'archive-all', 'woo-all-products-archives' ],
			'label' => $this->get_label(),
			'nodeInfo'  => [
				'title' => __( 'Category taxonomy', 'jet-theme-core' ),
				'desc'  => __( 'Product category taxonomy archive and single post', 'jet-theme-core' ),
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
			'placeholder' => __( 'Select page', 'jet-theme-core' ),
		];
	}

	/**
	 * [ajax_action description]
	 * @return [type] [description]
	 */
	public function ajax_action() {
		return [
			'action' => 'get-product-categories',
			'params' => [],
		];
	}

	/**
	 * [get_label_by_value description]
	 * @param  string $value [description]
	 * @return [type]        [description]
	 */
	public function get_label_by_value( $value = '' ) {

		if ( in_array( 'all', $value ) ) {
			$result[] = __( 'All', 'jet-theme-core' );
		}

		foreach ( $value as $id ) {
			$result[] = get_term_by( 'id', $id, 'product_cat' )->name;
		}

		return implode( ', ', $result );
	}

	/**
	 * @return string|null
	 */
	public function get_preview_link() {

		$categories = get_terms( [
			'taxonomy'   => 'product_cat',
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
	public function check( $arg ) {

		if ( empty( $arg ) ) {
			return is_product_category();
		}

		if ( in_array( 'all', $arg ) ) {
			return is_product_category();
		}

		foreach ( $arg as $id ) {
			$category_obj = get_term_by( 'id', $id, 'product_cat' );

			$is_product_category = is_product_category( $category_obj->slug );

			if ( $is_product_category ) {
				return true;
			}
		}

		return false;
	}

}
