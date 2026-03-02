<?php

namespace JFB_Formless\Modules\Routes\REST;

use JFB_Formless\REST\Route;

class RequestLogsCountRoute extends Route {

	public function __construct(
		Endpoints\CountRequestLogs $count_request_logs
	) {
		$this->set_route( 'count' );
		$this->add_endpoint( $count_request_logs );
	}

}
