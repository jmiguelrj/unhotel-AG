<?php
namespace Jet_Menu\Endpoints;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define Posts class
 */
class Clear_Cache extends Base {

	/**
	 * [get_method description]
	 * @return [type] [description]
	 */
	public function get_method() {
		return 'POST';
	}

	/**
	 * Returns route name
	 *
	 * @return string
	 */
	public function get_name() {
		return 'clear-cache';
	}

	/**
	 * Check user access to current endpoint
	 *
	 * @return bool
	 */
	public function permission_callback( $request ) {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Clear cache endpoint callback.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function callback( $request ) {
		$type = isset( $request['type'] ) ? sanitize_key( $request['type'] ) : 'template';

		if ( 'template' === $type ) {
			$result = \Jet_Cache\Manager::get_instance()->db_manager->delete_cache_by_source( 'jet-menu' );

			if ( false === $result ) {
				return rest_ensure_response( [
					'status'  => 'error',
					'message' => __( 'Server Error', 'jet-menu' ),
				] );
			}

			return rest_ensure_response( [
				'status'  => 'success',
				'message' => __( 'Menu template cache cleared', 'jet-menu' ),
			] );
		}

		if ( 'css' === $type ) {
			if ( function_exists( 'jet_menu_css_file' ) ) {
				jet_menu_css_file()->remove_css_file();

				return rest_ensure_response( [
					'status'  => 'success',
					'message' => __( 'Menu CSS cache cleared', 'jet-menu' ),
				] );
			}

			return rest_ensure_response( [
				'status'  => 'error',
				'message' => __( 'Server Error', 'jet-menu' ),
			] );
		}

		return rest_ensure_response( [
			'status'  => 'error',
			'message' => __( 'Unknown cache type', 'jet-menu' ),
		] );
	}

}
