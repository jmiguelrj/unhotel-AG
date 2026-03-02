<?php

namespace JFB_Formless\RouteTypes\Validators;

use JFB_Formless\Modules\Routes\AJAX\CheckIsUniqueAjaxEndpoint;
use JFB_Formless\RouteTypes\Validators\Interfaces\ValidatorInterface;
use JFB_Formless\Services\Route;
use JFB_Formless\Services\RouteMeta;
use JFB_Formless\Services\ValidateException;

class AJAXEndpoint implements ValidatorInterface {

	private $ajax_endpoint;

	public function __construct( CheckIsUniqueAjaxEndpoint $ajax_endpoint ) {
		$this->ajax_endpoint = $ajax_endpoint;
	}


	public function is_supported( RouteMeta $route_meta ): bool {
		return ! $route_meta->get_route()->get_action_type();
	}

	/**
	 * @param RouteMeta $route_meta
	 *
	 * @return void
	 * @throws ValidateException
	 */
	public function apply( RouteMeta $route_meta ) {
		$action = $route_meta->get_property( 'ajax_action' );

		if ( ! $action ) {
			throw new ValidateException( 'empty_required_action' );
		}
		if ( ! is_string( $action ) ) {
			throw new ValidateException( 'invalid_action_format' );
		}

		$action = sanitize_key( $action );

		if ( ! $action || $this->ajax_endpoint::ACTION === $action ) {
			throw new ValidateException( 'empty_required_action' );
		}

		$route_meta->set_property( 'ajax_action', $action );
		$is_unique = $this->is_unique( $action );

		if ( ! $is_unique ) {
			throw new ValidateException( 'override' );
		}
	}

	/**
	 * @param string $action
	 *
	 * @return bool
	 * @throws ValidateException
	 */
	private function is_unique( string $action ): bool {
		$url = $this->ajax_endpoint->get_url( $action );

		// Use wp_remote_get to make the request.
		$response = wp_remote_get( $url );

		// Check for errors.
		if ( is_wp_error( $response ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			throw new ValidateException( $response->get_error_code() );
		}

		// Retrieve the response body.
		$body = wp_remote_retrieve_body( $response );

		// Optionally, decode the JSON response.
		$data = json_decode( $body, true );

		if ( ! is_array( $data ) ) {
			throw new ValidateException( 'invalid_json' );
		}

		return $data['success'] ?? true;
	}


}
