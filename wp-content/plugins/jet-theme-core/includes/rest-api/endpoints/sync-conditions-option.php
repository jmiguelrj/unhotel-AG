<?php
namespace Jet_Theme_Core\Endpoints;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
/**
 * Define Posts class
 */
class Sync_Conditions_Option extends Base {

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
		return 'sync-conditions-option';
	}

	/**
	 * [callback description]
	 * @param  [type]   $request [description]
	 * @return function          [description]
	 */
	public function callback( $request ) {

		$sync_conditions = jet_theme_core()->templates->sync_conditions_option();

		if ( ! $sync_conditions ) {
			return rest_ensure_response( array(
				'success' => false,
				'message' => __( 'Server Error', 'jet-theme-core' ),
			) );
		}

		return rest_ensure_response( array(
			'status'  => 'success',
			'message' => __( 'Templates library have been synchronized', 'jet-theme-core' ),
		) );
	}
}
