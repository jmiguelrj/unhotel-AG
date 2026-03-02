<?php

namespace JFB_Formless\Modules\Routes\REST\Endpoints;

use JFB_Formless\REST\Interfaces\EndpointInterface;
use JFB_Formless\DB\Views;

class CountRoutes implements EndpointInterface {

	private $view;

	public function __construct(
		Views\RoutesCount $view
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
		return array();
	}

	public function process( \WP_REST_Request $request ): \WP_REST_Response {
		return new \WP_REST_Response(
			array(
				'routes_total' => $this->view->get_count(),
			)
		);
	}
}
