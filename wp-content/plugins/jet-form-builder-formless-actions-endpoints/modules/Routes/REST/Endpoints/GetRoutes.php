<?php

namespace JFB_Formless\Modules\Routes\REST\Endpoints;

use Jet_Form_Builder\Db_Queries\Query_Conditions_Builder;
use Jet_Form_Builder\Exceptions\Query_Builder_Exception;
use JFB_Formless\REST\Interfaces\EndpointInterface;
use JFB_Formless\DB\Views;
use JFB_Formless\Adapters;
use JFB_Formless\Services;

class GetRoutes implements EndpointInterface {

	private $view;
	private $data_view;
	private $routes_list;

	public function __construct(
		Views\Routes $view,
		Adapters\DataViewArguments $data_view,
		Services\RoutesList $routes_list
	) {
		$this->view        = $view;
		$this->data_view   = $data_view;
		$this->routes_list = $routes_list;
	}

	public function get_method(): string {
		return \WP_REST_Server::READABLE;
	}

	public function has_permission(): bool {
		return current_user_can( 'manage_options' );
	}

	public function get_args(): array {
		return array(
			'limit'   => array(
				'type' => 'integer',
			),
			'page'    => array(
				'type' => 'integer',
			),
			'order'   => array(
				'type' => 'string',
			),
			'orderBy' => array(
				'type' => 'string',
			),
		);
	}

	public function process( \WP_REST_Request $request ): \WP_REST_Response {
		$body = $request->get_query_params();
		$this->data_view->inject_arguments( $this->view, $body );

		try {
			$routes = $this->view->query()->query_all();
		} catch ( Query_Builder_Exception $exception ) {
			return new \WP_REST_Response(
				array(
					'message' => __( 'Routes not found.', 'jet-form-builder' ),
					'code'    => 'not_found',
				),
				404
			);
		}

		$this->routes_list->inject_endpoints( $routes );
		$this->inject_form_titles( $routes );

		return new \WP_REST_Response(
			array(
				'routes' => $routes,
			)
		);
	}

	private function inject_form_titles( array &$routes ) {
		$ids = array();

		foreach ( $routes as $route ) {
			$ids[ (int) ( $route['form_id'] ?? '' ) ] = 1;
		}

		$forms = get_posts(
			array(
				'include'   => array_keys( $ids ),
				'post_type' => 'jet-form-builder',
			)
		);

		$sorted_forms = array();

		foreach ( $forms as $form ) {
			$sorted_forms[ $form->ID ] = $form->post_title;
		}

		foreach ( $routes as &$route ) {
			if ( empty( $sorted_forms[ $route['form_id'] ] ) ) {
				continue;
			}
			$route['form_title'] = $sorted_forms[ $route['form_id'] ];
		}
	}
}
