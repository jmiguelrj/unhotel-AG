<?php

namespace JFB_Formless\Modules\Routes\REST;

use JFB_Formless\Modules\Routes\Module;
use JFB_Formless\REST\Route;

class RoutesRoute extends Route {

	public function __construct(
		Endpoints\GetRoutes $get_routes,
		Endpoints\CreateRoute $create_route
	) {
		$this->set_namespace( Module::REST_NAMESPACE );
		$this->set_route( 'routes' );
		$this->add_endpoint( $get_routes );
		$this->add_endpoint( $create_route );
	}

}
