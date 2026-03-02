<?php

namespace JFB_Formless\Modules\Routes\REST\Endpoints;

use Jet_Form_Builder\Exceptions\Query_Builder_Exception;
use JFB_Formless\REST\Interfaces\EndpointInterface;
use JFB_Formless\Services;
use JFB_Formless\Adapters;

class GetRoute implements EndpointInterface {

	private $route_to_db;
	private $route_to_api;

	public function __construct(
		Adapters\RouteToDatabase $route_to_db,
		Adapters\RouteToRestApi $route_to_api,
		Services\Route $route,
		Services\RouteMeta $meta
	) {
		$meta->set_route( $route );

		$this->route_to_db  = $route_to_db;
		$this->route_to_api = $route_to_api;

		$this->route_to_db->set_route( $route );
		$this->route_to_api->set_meta( $meta );
	}

	public function get_method(): string {
		return \WP_REST_Server::READABLE;
	}

	public function has_permission(): bool {
		return current_user_can( 'manage_options' );
	}

	public function get_args(): array {
		return array(
			'id' => array(
				'type'     => 'integer',
				'required' => true,
			),
		);
	}

	public function process( \WP_REST_Request $request ): \WP_REST_Response {
		$route_id = absint( $request->get_param( 'id' ) );

		try {
			$this->route_to_db->get_route()->set_id( $route_id );
			$this->route_to_db->find();
		} catch ( Services\ValidateException $exception ) {
			return new \WP_REST_Response(
				array(
					'message' => __( 'Route not found.', 'jet-form-builder' ),
					'code'    => 'bad_request',
				),
				404
			);
		} catch ( Query_Builder_Exception $exception ) {
			return new \WP_REST_Response(
				array(
					'message' => __( 'Route not found.', 'jet-form-builder' ),
					'code'    => 'not_found',
				),
				404
			);
		}

		// metadata is not important
		try {
			$this->route_to_api->get_meta()->set_properties( array( '*' ) );
			$this->route_to_api->get_meta()->find();
		} catch ( Services\ValidateException $exception ) {
			// do nothing
		} catch ( Query_Builder_Exception $e ) {
			// do nothing
		}

		return new \WP_REST_Response(
			array(
				'route' => iterator_to_array( $this->route_to_api->generate_api_row() ),
			)
		);
	}
}
