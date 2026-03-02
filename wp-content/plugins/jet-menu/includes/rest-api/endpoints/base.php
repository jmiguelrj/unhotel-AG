<?php
namespace Jet_Menu\Endpoints;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
/**
 * Define Endpoint_Base class
 */
abstract class Base {

	/**
	 * Returns route name
	 *
	 * @return string
	 */
	abstract function get_name();

	/**
	 * API callback
	 * @return void
	 */
	abstract function callback( $request );

	/**
	 * Returns endpoint request method - GET/POST/PUT/DELTE
	 *
	 * @return string
	 */
	public function get_method() {
		return 'GET';
	}

	/**
	 * Check user access to current end-popint
	 * Should be rewritten in any end-point to ensure correct permissions check applied
	 *
	 * @return bool
	 */
	public function permission_callback( $request ) {
		$template_id = $request->get_param('id');
		$signature   = $request->get_param('signature');

		if ( ! $template_id || !$signature ) {
			return false;
		}

		$expected_signature = $this->generate_signature( $template_id );

		return hash_equals( $expected_signature, $signature );
	}

	/**
	 * Get query param. Regex with query parameters
	 *
	 * Example:
	 *
	 * (?P<id>[\d]+)/(?P<meta_key>[\w-]+)
	 *
	 * @return string
	 */
	public function get_query_params() {
		return '';
	}

	/**
	 * Returns arguments config
	 *
	 * Example:
	 *
	 * 	array(
	 * 		array(
	 * 			'type' => array(
	 * 			'default'  => '',
	 * 			'required' => false,
	 * 		),
	 * 	)
	 *
	 * @return array
	 */
	public function get_args() {
		return array();
	}

	/**
	 * Retrieves or generates a unique identifier for the site.
	 *
	 * @return string
	 */
	public function get_unique_id() {
		$unique_id = get_option( 'jet_menu_unique_id' );

		if ( empty( $unique_id ) ) {
			$unique_id = time();

			update_option( 'jet_menu_unique_id', $unique_id, true );
		}

		return $unique_id;
	}

	/**
	 * Generates a secure signature based on the unique site identifier,
	 * the template ID, and the NONCE_KEY constant.
	 *
	 * @param int|string $template_id
	 *
	 * @return string
	 */
	public function generate_signature( $template_id ) {
		$unique_id = $this->get_unique_id();
		$nonce_key = defined( 'NONCE_KEY' ) ? NONCE_KEY : ( defined( 'AUTH_KEY' ) ? AUTH_KEY : 'jet_menu_fallback_nonce' );

		return md5( $unique_id . $template_id . $nonce_key );
	}

}
