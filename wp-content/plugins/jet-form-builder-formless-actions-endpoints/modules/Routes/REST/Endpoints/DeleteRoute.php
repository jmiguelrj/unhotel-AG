<?php

namespace JFB_Formless\Modules\Routes\REST\Endpoints;

use Jet_Form_Builder\Db_Queries\Exceptions\Sql_Exception;
use JFB_Formless\REST\Interfaces\EndpointInterface;
use JFB_Formless\Services;
use JFB_Formless\Adapters;

class DeleteRoute implements EndpointInterface {

	private $route_to_db;

	public function __construct(
		Adapters\RouteToDatabase $route_to_db,
		Services\Route $route
	) {
		$this->route_to_db = $route_to_db;
		$this->route_to_db->set_route( $route );
	}

	public function get_method(): string {
		return \WP_REST_Server::DELETABLE;
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
			$this->route_to_db->delete();
		} catch ( Sql_Exception $exception ) {
			return new \WP_REST_Response(
				array(
					'code'    => 'failed_delete',
					'message' => $exception->getMessage(),
					'data'    => $exception->get_additional(),
				),
				400
			);
		} catch ( Services\ValidateException $exception ) {
			return new \WP_REST_Response(
				array(
					'message' => __( 'Bad request', 'jet-form-builder-formless-actions-endpoints' ),
					'code'    => $exception->getMessage(),
				),
				400
			);
		}

		return new \WP_REST_Response( array() );
	}
}
