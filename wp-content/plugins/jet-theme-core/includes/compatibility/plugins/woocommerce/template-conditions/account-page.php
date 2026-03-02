<?php
namespace Jet_Theme_Core\Template_Conditions;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Woo_Account_Page extends Base {

	/**
	 * Condition slug
	 *
	 * @return string
	 */
	public function get_id() {
		return 'woo-account-page';
	}

	/**
	 * Condition label
	 *
	 * @return string
	 */
	public function get_label() {
		return __( 'Account Page', 'jet-theme-core' );
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
		return 'woocommerce-my-account';
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
		return 'jet_account_page';
	}

	/**
	 * @return array
	 */
	public function get_node_data() {
		return [
			'node'   => $this->get_id(),
			'parent' => 'woo-shop-page',
			'inherit' => [ 'entire' ],
			'label' => $this->get_label(),
			'nodeInfo'  => [
				'title' => __( 'Account Pages', 'jet-theme-core' ),
				'desc'  => __( 'Account Pages templates', 'jet-theme-core' ),
			],
			'previewLink' => $this->get_preview_link(),
		];
	}

	/**
	 * @return string|null
	 */
	public function get_preview_link() {
		$checkout_page_id = get_option( 'woocommerce_myaccount_page_id' );
		$checkout_url = get_permalink( $checkout_page_id );

		return esc_url( $checkout_url );
	}

	/**
	 * Condition check callback
	 *
	 * @return bool
	 */
	public function check( $args ) {
		return is_account_page();
	}

}
