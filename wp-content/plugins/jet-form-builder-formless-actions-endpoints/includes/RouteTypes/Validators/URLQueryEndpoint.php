<?php

namespace JFB_Formless\RouteTypes\Validators;

use JFB_Formless\RouteTypes\Validators\Interfaces\ValidatorInterface;
use JFB_Formless\Services\Route;
use JFB_Formless\Services\RouteMeta;
use JFB_Formless\Services\ValidateException;

class URLQueryEndpoint implements ValidatorInterface {

	public function is_supported( RouteMeta $route_meta ): bool {
		return Route::ACTION_URL === $route_meta->get_route()->get_action_type();
	}

	/**
	 * @param RouteMeta $route_meta
	 *
	 * @return void
	 * @throws ValidateException
	 */
	public function apply( RouteMeta $route_meta ) {
		$action = $route_meta->get_property( 'url_action' );

		if ( ! $action ) {
			throw new ValidateException( 'empty_required_action' );
		}
		if ( ! is_string( $action ) ) {
			throw new ValidateException( 'invalid_action_format' );
		}

		$action = sanitize_key( $action );

		if ( ! $action ) {
			throw new ValidateException( 'empty_required_action' );
		}

		$route_meta->set_property( 'url_action', $action );
	}

}
