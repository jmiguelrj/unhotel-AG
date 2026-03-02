<?php

namespace JFB_Formless\Modules\Routes\REST\Endpoints;

use Jet_Form_Builder\Db_Queries\Exceptions\Sql_Exception;
use JFB_Formless\Vendor\Auryn\InjectionException;
use JFB_Formless\REST\Interfaces\EndpointInterface;
use JFB_Formless\Services;
use JFB_Formless\Adapters;
use JFB_Formless\Plugin;

class CreateRoute implements EndpointInterface {

	private $plugin;

	public function __construct(
		Plugin $plugin
	) {
		$this->plugin = $plugin;
	}

	public function get_method(): string {
		return \WP_REST_Server::CREATABLE;
	}

	public function has_permission(): bool {
		return current_user_can( 'manage_options' );
	}

	public function get_args(): array {
		return array(
			'form_id'           => array(
				'type'     => 'integer',
				'required' => true,
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

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 * @throws InjectionException
	 */
	public function process( \WP_REST_Request $request ): \WP_REST_Response {
		$body = $request->get_json_params();

		/** @var Services\Route $adapter */
		$route = $this->plugin->get_injector()->make( Services\Route::class );
		/** @var Adapters\RouteToDatabase $adapter */
		$adapter = $this->plugin->get_injector()->make( Adapters\RouteToDatabase::class );
		/** @var Services\RouteMeta $meta */
		$meta = $this->plugin->get_injector()->make( Services\RouteMeta::class );

		try {
			// fill route
			$adapter->set_route( $route );
			$adapter->from_row( $body );

			// fill route meta
			$meta->set_route( $route );
			$meta->set_properties( $body['meta'] ?? array() );
			$meta->validate();

			$adapter->create();
			$meta->update();
		} catch ( Sql_Exception $exception ) {
			return new \WP_REST_Response(
				array(
					'code'    => 'failed_insert',
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
				'route'   => array(
					'id' => $route->get_id(),
				),
				'message' => __( 'Endpoint created.', 'jet-form-builder-formless-actions-endpoints' ),
			)
		);
	}
}
