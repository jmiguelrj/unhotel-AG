<?php
namespace Jet_Theme_Core\Template_Conditions;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Woo_Product_Checkout extends Base {

	/**
	 * Condition slug
	 *
	 * @return string
	 */
	public function get_id() {
		return 'woo-product-checkout';
	}

	/**
	 * Condition label
	 *
	 * @return string
	 */
	public function get_label() {
		return __( 'Products Checkout', 'jet-theme-core' );
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
		return 'woocommerce-сheckout';
	}

	/**
	 * @return int
	 */
	public function get_priority() {
		return 59;
	}

	/**
	 * @return string
	 */
	public function get_body_structure() {
		return 'jet_products_checkout';
	}

	/**
	 * @return array
	 */
	public function get_node_data() {
		return [
			'node'   => $this->get_id(),
			'parent' => 'woo-shop-page',
			'inherit' => [ 'entire' ],
			'label' => __( 'Checkout Page', 'jet-theme-core' ),
			'nodeInfo'  => [
				'title' => __( 'Checkout Pages', 'jet-theme-core' ),
				'desc'  => __( 'Checkout pages templates', 'jet-theme-core' ),
			],
			'previewLink' => $this->get_preview_link(),
		];
	}

	/**
	 * @return string|null
	 */
	public function get_preview_link() {
		$checkout_page_id = get_option( 'woocommerce_checkout_page_id' );
		$checkout_url = get_permalink( $checkout_page_id );

		return esc_url( $checkout_url );
	}

	/**
	 * Condition check callback
	 *
	 * @return bool
	 */
	public function check( $arg ) {
		return is_checkout() && ! is_wc_endpoint_url();
	}

}
