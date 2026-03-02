<?php

namespace JFB_Formless\Modules\Routes\REST\Endpoints;

use Jet_Form_Builder\Db_Queries\Query_Conditions_Builder;
use Jet_Form_Builder\Exceptions\Query_Builder_Exception;
use JFB_Formless\REST\Interfaces\EndpointInterface;
use JFB_Formless\DB\Views;
use JFB_Formless\Adapters;

class GetRequestLogs implements EndpointInterface {

	private $view;
	private $data_view;

	public function __construct(
		Views\RequestLogs $view,
		Adapters\DataViewArguments $data_view
	) {
		$this->view      = $view;
		$this->data_view = $data_view;
	}

	public function get_method(): string {
		return \WP_REST_Server::READABLE;
	}

	public function has_permission(): bool {
		return current_user_can( 'manage_options' );
	}

	public function get_args(): array {
		return array(
			'id'      => array(
				'type'     => 'integer',
				'required' => true,
			),
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
		$route_id = absint( $request->get_param( 'id' ) );
		$body     = $request->get_query_params();
		$this->data_view->inject_arguments( $this->view, $body );

		$this->view->set_conditions(
			array(
				array(
					'type'   => Query_Conditions_Builder::TYPE_EQUAL,
					'values' => array( 'route_id', $route_id ),
				),
			)
		);
		try {
			$logs = $this->view->query()->query_all();
		} catch ( Query_Builder_Exception $exception ) {
			return new \WP_REST_Response(
				array(
					'message' => __( 'Request logs not found.', 'jet-form-builder' ),
					'code'    => 'not_found',
				),
				404
			);
		}

		return new \WP_REST_Response(
			array(
				'request_logs' => $logs,
			)
		);
	}


}