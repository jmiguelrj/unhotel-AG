<?php

namespace JFB_Formless\Modules\Listeners;

use Jet_Form_Builder\Exceptions\Query_Builder_Exception;
use JFB_Formless\DB\Views;
use JFB_Formless\Services;

class RestAPI {

	private $meta;
	private $proxy;

	public function __construct(
		Views\RoutesMeta $meta,
		ListenerProxy $proxy
	) {
		$this->meta  = $meta;
		$this->proxy = $proxy;

		add_action( 'rest_api_init', array( $this, 'add_routes' ) );
	}

	public function add_routes() {
		$this->meta->set_select(
			array(
				array(
					'as' => sprintf( 'DISTINCT %s', $this->meta->column( 'route_value' ) ),
				),
			)
		);
		$conditions = $this->meta::prepare_columns(
			array(
				'route_key' => 'rest_namespace',
			)
		);
		$this->meta->set_conditions( $conditions );

		try {
			$namespaces = $this->meta->query()->query_col();
		} catch ( Query_Builder_Exception $exception ) {
			return;
		}

		foreach ( $namespaces as $rest_namespace ) {
			register_rest_route(
				$rest_namespace,
				'(?P<route>[\w\-\/]+)',
				array( // phpcs:ignore Universal.Operators.DisallowShortTernary.Found
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'run_submit' ),
					// we check permissions inside main callback
					'permission_callback' => '__return_true',
				)
			);
		}
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function run_submit( \WP_REST_Request $request ): \WP_REST_Response {
		global $wpdb;

		$route_id_column    = $this->meta->column( 'route_id' );
		$route_key_column   = $this->meta->column( 'route_key' );
		$route_value_column = $this->meta->column( 'route_value' );
		$table              = $this->meta->table();

		// phpcs:disable WordPress.DB
		$route_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT {$route_id_column}
FROM {$table}
WHERE ({$route_key_column} = 'rest_namespace' AND {$route_value_column} = %s)
   OR ({$route_key_column} = 'rest_route' AND {$route_value_column} = %s)
GROUP BY {$route_id_column}
HAVING COUNT({$route_key_column}) = 2;",
				$this->get_namespace( $request ),
				$request->get_param( 'route' )
			)
		);
		// phpcs:enable WordPress.DB

		$this->proxy->set_action_type( Services\Route::ACTION_REST );

		if ( $request->is_json_content_type() ) {
			$this->proxy->set_request( $request->get_json_params() ?? array() );
		} else {
			$this->proxy->set_request( $request->get_body_params() ?? array() );
		}

		return $this->proxy->get_response( $route_id );
	}

	private function get_namespace( \WP_REST_Request $request ): string {
		$full_route     = $request->get_route();
		$endpoint       = $request->get_param( 'route' );
		$rest_namespace = str_replace( $endpoint, '', $full_route );

		return trim( $rest_namespace, '/' );
	}
}
