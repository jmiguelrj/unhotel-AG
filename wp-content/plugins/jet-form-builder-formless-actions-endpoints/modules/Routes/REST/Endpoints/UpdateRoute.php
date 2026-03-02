<?php

namespace JFB_Formless\Modules\Routes\REST\Endpoints;

use Jet_Form_Builder\Db_Queries\Exceptions\Sql_Exception;
use Jet_Form_Builder\Exceptions\Query_Builder_Exception;
use JFB_Formless\REST\Interfaces\EndpointInterface;
use JFB_Formless\Services;
use JFB_Formless\Adapters;

class UpdateRoute implements EndpointInterface {

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
		return \WP_REST_Server::CREATABLE;
	}

	public function has_permission(): bool {
		return current_user_can( 'manage_options' );
	}

	public function get_args(): array {
		return array(
			'id'                => array(
				'type'     => 'integer',
				'required' => true,
			),
			'form_id'           => array(
				'type' => 'integer',
			),
			'action_type'       => array(
				'description' => __(
					'Possible values: ajax (by default), rest, url',
					'jet-form-builder-formless-actions-endpoints'
				),
				'type'        => 'integer',
			),
			'restriction_type'  => array(
				'description' => __(
					'Possible values: 0 (for any user), 1 (by role), 2 (by capability)',
					'jet-form-builder-formless-actions-endpoints'
				),
				'type'        => 'integer',
			),
			'restriction_roles' => array(
				'description' => __(
					'Roles slugs, if restriction_type equals to 1',
					'jet-form-builder-formless-actions-endpoints'
				),
				'type'        => 'array',
			),
			'restriction_cap'   => array(
				'description' => __(
					'Capability slug, if restriction_type equals to 2',
					'jet-form-builder-formless-actions-endpoints'
				),
				'type'        => 'string',
			),
			'log'               => array(
				'type' => 'boolean',
			),
			'restricted'        => array(
				'type' => 'boolean',
			),
			'meta'              => array(
				'type' => 'object',
			),
		);
	}

	public function process( \WP_REST_Request $request ): \WP_REST_Response {
		$route_id = absint( $request->get_param( 'id' ) );
		$body     = $request->get_json_params();
		$meta     = $body['meta'] ?? array();

		try {
			$this->route_to_db->get_route()->set_id( $route_id );
			$this->route_to_db->from_row( $body );

			$this->route_to_api->get_meta()->set_properties( $meta );
			$this->route_to_api->get_meta()->validate();

			$this->route_to_db->update();
			$this->route_to_api->get_meta()->update();
		} catch ( Query_Builder_Exception $exception ) {
			return new \WP_REST_Response(
				array(
					'message' => $exception->getMessage(),
					'code'    => 'not_found',
				),
				404
			);
		} catch ( Sql_Exception $exception ) {
			return new \WP_REST_Response(
				array(
					'code'    => 'failed_update',
					'message' => $exception->getMessage(),
					'data'    => $exception->get_additional(),
				),
				400
			);
		} catch ( Services\ValidateException $exception ) {
			return new \WP_REST_Response(
				array(
					'message' => sprintf(
						/* translators: string-code of the exception */
						__( 'Problem occurred: %s', 'jet-form-builder-formless-actions-endpoints' ),
						$exception->getMessage()
					),
					'code'    => $exception->getMessage(),
				),
				400
			);
		}

		return new \WP_REST_Response(
			array(
				'message' => __( 'Endpoint updated.', 'jet-form-builder-formless-actions-endpoints' )
			)
		);
	}
}
