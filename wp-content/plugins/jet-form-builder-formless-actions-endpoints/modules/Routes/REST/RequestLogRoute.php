<?php

namespace JFB_Formless\Modules\Routes\REST;

use JFB_Formless\REST\Route;

class RequestLogRoute extends Route {

	public function __construct(
		Endpoints\DeleteRequestLog $delete_request_log
	) {
		$this->set_route( 'logs/(?P<id>[\d]+)' );
		$this->add_endpoint( $delete_request_log );
	}

}
