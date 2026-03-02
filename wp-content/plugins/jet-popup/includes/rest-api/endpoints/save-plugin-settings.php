<?php
namespace Jet_Popup\Endpoints;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
/**
 * Define Posts class
 */
class Save_Plugin_Settings extends Base {

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
		return 'save-plugin-settings';
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
		$current = get_option( jet_popup()->settings->key, [] );

		if ( is_wp_error( $current ) ) {
			return rest_ensure_response( [
				'status'  => 'error',
				'message' => __( 'Server Error', 'jet-popup' ),
			] );
		}
		$messages = [ __( 'Settings have been saved', 'jet-popup' ) ];
		$current_cache_expiration = isset( $current['useContentCache'] ) ? $current['useContentCache']['cacheExpiration'] : 'week';

		foreach ( $data as $key => $value ) {
			$current[ $key ] = is_array( $value ) ? $value : esc_attr( $value );

			if ( 'useContentCache' === $key ) {
				$cache_expiration = $value['cacheExpiration'];

				if ( $current_cache_expiration !== $cache_expiration ) {
					\Jet_Cache\Manager::get_instance()->db_manager->delete_cache_by_source( 'jet-popup' );
					$messages[] = __( 'Popups cache have been cleared', 'jet-popup' );
				}
			}
		}

		update_option( jet_popup()->settings->key, $current );

		return rest_ensure_response( [
			'status'  => 'success',
			'message' => implode( '. ', $messages ),
		] );
	}

}
