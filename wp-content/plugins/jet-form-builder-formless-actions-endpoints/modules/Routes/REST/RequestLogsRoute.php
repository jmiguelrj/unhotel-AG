<?php

namespace JFB_Formless\Modules\Routes\REST;

use JFB_Formless\REST\Route;

class RequestLogsRoute extends Route {

	public function __construct(
		Endpoints\GetRequestLogs $get_request_logs
	) {
		$this->set_route( 'logs' );
		$this->add_endpoint( $get_request_logs );
	}

}
