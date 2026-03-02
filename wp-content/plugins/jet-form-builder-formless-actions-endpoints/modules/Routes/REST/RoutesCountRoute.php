<?php

namespace JFB_Formless\Modules\Routes\REST;

use JFB_Formless\REST\Route;

class RoutesCountRoute extends Route {

	public function __construct(
		Endpoints\CountRoutes $count_routes
	) {
		$this->set_route( 'count' );
		$this->add_endpoint( $count_routes );
	}

}
