<?php
namespace Jet_Theme_Core\Endpoints;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
/**
 * Define Posts class
 */
class Get_WP_Options extends Base {

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
		return 'get-wp-options';
	}

	/**
	 * Returns arguments config
	 *
	 * @return [type] [description]
	 */
	public function get_args() {
		return [];
	}

	/**
	 * [callback description]
	 * @param  [type]   $request [description]
	 * @return function          [description]
	 */
	public function callback( $request ) {
		global $wpdb;

		$args = $request->get_params();
		$query = $args['query'];
		$values = $args['values'];

		if ( empty( $values ) ) {
			$option_query = $wpdb->get_results( "SELECT option_name FROM $wpdb->options WHERE option_name LIKE '%" . $query . "%' AND autoload = 'yes'" );
		} else {
			$escapedValues = array_map( function ( $value ) use ( $wpdb ) {
				return "'" . $wpdb->_real_escape( $value ) . "'";
			}, $values );

			$option_query = $wpdb->get_results( "SELECT option_name FROM $wpdb->options WHERE option_name IN (" . implode(',', $escapedValues) . ") AND autoload = 'yes'" );
		}

		$option_query = apply_filters( 'jet-theme-core/get-wp-options', $option_query, $args );

		if ( empty( $option_query ) ) {
			return rest_ensure_response( $option_query );
		}

		$options = array_map( function ( $option ) {
			return [
				'label' => $option->option_name,
				'value' => $option->option_name,
			];
		}, $option_query );

		return rest_ensure_response( $options );
	}

}
