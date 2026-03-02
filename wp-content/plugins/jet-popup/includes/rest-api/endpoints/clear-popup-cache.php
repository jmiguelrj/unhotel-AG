<?php
namespace Jet_Popup\Endpoints;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
/**
 * Define Posts class
 */
class Clear_Popup_Cache extends Base {

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
		return 'clear-popup-cache';
	}

	/**
	 * Check user access to current end-popint
	 *
	 * @return bool
	 */
	public function permission_callback( $request ) {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * [callback description]
	 * @param  [type]   $request [description]
	 * @return function          [description]
	 */
	public function callback( $request ) {
		$data = $request->get_params();
		$status = \Jet_Cache\Manager::get_instance()->db_manager->delete_cache_by_source( 'jet-popup' );

		if ( ! $status ) {
			return rest_ensure_response( [
				'status'  => 'error',
				'message' => __( 'Server Error', 'jet-popup' ),
			] );
		}

		return rest_ensure_response( [
			'status'  => 'success',
			'message' => __( 'Popups cache have been cleared', 'jet-popup' ),
		] );
	}

}
