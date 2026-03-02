<?php

namespace JFB_Formless\Modules\Routes\REST\Endpoints;

use Jet_Form_Builder\Db_Queries\Exceptions\Sql_Exception;
use JFB_Formless\REST\Interfaces\EndpointInterface;
use JFB_Formless\DB\Models;

class DeleteRequestLog implements EndpointInterface {

	private $logs;

	public function __construct(
		Models\RequestLogs $logs
	) {
		$this->logs = $logs;
	}

	public function get_method(): string {
		return \WP_REST_Server::DELETABLE;
	}

	public function has_permission(): bool {
		return current_user_can( 'manage_options' );
	}

	public function get_args(): array {
		return array();
	}

	public function process( \WP_REST_Request $request ): \WP_REST_Response {
		$log_id = absint( $request->get_param( 'id' ) );

		try {
			$this->logs->delete(
				array(
					'id' => $log_id,
				)
			);
		} catch ( Sql_Exception $exception ) {
			return new \WP_REST_Response(
				array(
					'code'    => 'failed_delete',
					'message' => $exception->getMessage(),
					'data'    => $exception->get_additional(),
				),
				400
			);
		}

		return new \WP_REST_Response( array() );
	}
}
