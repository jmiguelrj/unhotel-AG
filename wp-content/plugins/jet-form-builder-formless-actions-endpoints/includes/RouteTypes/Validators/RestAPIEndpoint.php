<?php

namespace JFB_Formless\RouteTypes\Validators;

use JFB_Formless\RouteTypes\Validators\Interfaces\ValidatorInterface;
use JFB_Formless\Services\Route;
use JFB_Formless\Services\RouteMeta;
use JFB_Formless\Services\ValidateException;

class RestAPIEndpoint implements ValidatorInterface {

	public function is_supported( RouteMeta $route_meta ): bool {
		return Route::ACTION_REST === $route_meta->get_route()->get_action_type();
	}

	/**
	 * @param RouteMeta $route_meta
	 *
	 * @return void
	 * @throws ValidateException
	 */
	public function apply( RouteMeta $route_meta ) {
		$rest_namespace = $route_meta->get_property( 'rest_namespace' );
		$route          = $route_meta->get_property( 'rest_route' );

		if ( ! $rest_namespace || ! $route ) {
			throw new ValidateException( 'empty_required_action' );
		}
		if ( ! is_string( $rest_namespace ) || ! is_string( $route ) ) {
			throw new ValidateException( 'invalid_action_format' );
		}

		$rest_namespace = $this->sanitize_slug( $rest_namespace );
		$route          = $this->sanitize_slug( $route );

		if ( ! $rest_namespace || ! $route ) {
			throw new ValidateException( 'empty_required_action' );
		}

		$route_meta->set_property( 'rest_namespace', $rest_namespace );
		$route_meta->set_property( 'rest_route', $route );

		if ( ! did_action( 'rest_api_init' ) ) {
			throw new ValidateException( 'invalid_call' );
		}

		$routes = array_keys( rest_get_server()->get_routes( $rest_namespace ) );

		// remove the first route with namespace only
		array_shift( $routes );

		$full_endpoint = sprintf( '/%s/%s', $rest_namespace, $route );
		$full_route    = sprintf( '/%s/(?P<route>[\w\-\/]+)', $rest_namespace );

		foreach ( $routes as $route_item ) {
			if ( $full_route === $route_item ) {
				continue;
			}
			if ( ! preg_match( sprintf( '#%s#', $route_item ), $full_endpoint ) ) {
				continue;
			}
			throw new ValidateException( 'override' );
		}
	}

	private function sanitize_slug( string $value ): string {
		// Use preg_replace instead of .replace and specify the pattern with delimiters
		$sanitized_slug = preg_replace( '/[^\w\-\/]+/', '', $value );
		// Convert to lowercase
		$sanitized_slug = strtolower( $sanitized_slug );
		// Split the string into an array by '/' and filter empty values using array_filter
		$parts = explode( '/', $sanitized_slug );
		$parts = array_filter( $parts, 'strlen' );

		// Join the array elements back into a string
		return implode( '/', $parts );
	}
}
