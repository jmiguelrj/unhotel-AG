<?php
namespace Jet_Menu\Endpoints;

/**
 * Define Posts class
 */
class Plugin_Settings extends Base {

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
		return 'plugin-settings';
	}

	/**
	 * [callback description]
	 * @param  [type]   $request [description]
	 * @return function          [description]
	 */
	public function callback( $request ) {

		$data = $request->get_params();
		$slug    = jet_menu()->settings_manager->options_manager->options_slug;
		$current = get_option( jet_menu()->settings_manager->options_manager->options_slug, array() );

		if ( is_wp_error( $current ) ) {
			return rest_ensure_response( [
				'status'  => 'error',
				'message' => __( 'Server Error', 'jet-menu' ),
			] );
		}

		$messages = array( __( 'Settings have been saved', 'jet-menu' ) );

		$old_cache_expiration = isset( $current['template-cache-expiration'] ) ? $current['template-cache-expiration'] : '';
		$old_cache_usage      = isset( $current['use-template-cache'] ) ? $current['use-template-cache'] : '';

		foreach ( $data as $key => $value ) {
			$current[ $key ] = is_array( $value ) ? $value : esc_attr( $value );
		}

		$new_cache_expiration = isset( $current['template-cache-expiration'] ) ? $current['template-cache-expiration'] : '';
		$new_cache_usage      = isset( $current['use-template-cache'] ) ? $current['use-template-cache'] : '';

		$is_expiration_changed = ( $old_cache_expiration !== $new_cache_expiration );
		$is_usage_changed      = ( $old_cache_usage !== $new_cache_usage );

		jet_menu()->settings_manager->options_manager->save_options( $slug, $current );

		if ( ( $is_expiration_changed || $is_usage_changed ) && class_exists( '\Jet_Cache\Manager' ) ) {
			\Jet_Cache\Manager::get_instance()->db_manager->delete_cache_by_source( 'jet-menu' );
			$messages[] = __( 'Menu templates cache has been cleared', 'jet-menu' );
		}

		return rest_ensure_response( array(
			'status'  => 'success',
			'message' => implode( '. ', $messages ),
		) );
	}

	/**
	 * Check user access to current end-popint
	 *
	 * @return string|bool
	 */
	public function permission_callback( $request ) {
		return current_user_can( 'manage_options' );
	}
	
}
