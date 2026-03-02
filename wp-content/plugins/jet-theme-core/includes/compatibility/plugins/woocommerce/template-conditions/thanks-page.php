<?php
namespace Jet_Theme_Core\Template_Conditions;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Woo_Thanks_Page extends Base {

	/**
	 * Condition slug
	 *
	 * @return string
	 */
	public function get_id() {
		return 'woo-thanks-page';
	}

	/**
	 * Condition label
	 *
	 * @return string
	 */
	public function get_label() {
		return __( 'Thank You Page', 'jet-theme-core' );
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
		return 'woocommerce-page';
	}

	/**
	 * @return int
	 */
	public function get_priority() {
		return 58;
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
			'parent' => 'woo-product-checkout',
			'inherit' => [ 'entire', 'woo-product-checkout' ],
			'label' => $this->get_label(),
			'previewLink' => $this->get_preview_link(),
		];
	}

	/**
	 * @return string|null
	 */
	public function get_preview_link() {
		$customer_orders = wc_get_orders( [
			'limit'    => 1,
			'customer' => get_current_user_id(),
			'orderby'  => 'date',
			'order'    => 'DESC',
		] );

		if ( ! empty( $customer_orders ) ) {
			$order = reset( $customer_orders );
			$thank_you_url = wc_get_endpoint_url( 'order-received', $order->get_id(), wc_get_checkout_url() );

			return esc_url( $thank_you_url );
		}

		return false;
	}

	/**
	 * Condition check callback
	 *
	 * @return bool
	 */
	public function check( $arg ) {
		global $wp;

		return is_checkout() && ! empty( $wp->query_vars['order-received'] );
	}

}
