<?php
namespace Jet_Theme_Core\Template_Conditions;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Woo_All_Product_Archives extends Base {

	/**
	 * Condition slug
	 *
	 * @return string
	 */
	public function get_id() {
		return 'woo-all-products-archives';
	}

	/**
	 * Condition label
	 *
	 * @return string
	 */
	public function get_label() {
		return __( 'All Products Archives', 'jet-theme-core' );
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
		return 9;
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
			'parent' => 'woo-shop-page',
			'inherit' => [ 'entire', 'archive-all', 'woo-shop-page' ],
			'label' => __( 'All Archives', 'jet-theme-core' ),
			'nodeInfo'  => [
				'title' => __( 'Products Archives', 'jet-theme-core' ),
				'desc'  => __( 'Templates for products archives and product single', 'jet-theme-core' ),
			]
		];
	}

	/**
	 * Condition check callback
	 *
	 * @return bool
	 */
	public function check( $args ) {
		$product_taxonomies = array_merge(['product_cat', 'product_tag'], get_object_taxonomies('product'));

		return is_tax( $product_taxonomies );
	}

}
