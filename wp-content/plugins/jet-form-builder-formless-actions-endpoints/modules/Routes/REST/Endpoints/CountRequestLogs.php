<?php

namespace JFB_Formless\Modules\Routes\REST\Endpoints;

use Jet_Form_Builder\Db_Queries\Query_Conditions_Builder;
use JFB_Formless\REST\Interfaces\EndpointInterface;
use JFB_Formless\DB\Views;

class CountRequestLogs implements EndpointInterface {

	private $view;

	public function __construct(
		Views\RequestLogsCount $view
	) {
		$this->view = $view;
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

		$this->view->set_conditions(
			array(
				array(
					'type'   => Query_Conditions_Builder::TYPE_EQUAL,
					'values' => array( 'route_id', $route_id ),
				),
			)
		);

		return new \WP_REST_Response(
			array(
				'requests_total' => $this->view->get_count(),
			)
		);
	}
}
