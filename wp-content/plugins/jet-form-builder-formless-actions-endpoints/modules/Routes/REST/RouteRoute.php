<?php

namespace JFB_Formless\Modules\Routes\REST;

use JFB_Formless\REST\Route;

class RouteRoute extends Route {

	public function __construct(
		Endpoints\GetRoute $get_route,
		Endpoints\UpdateRoute $update_route,
		Endpoints\DeleteRoute $delete_route
	) {
		$this->set_route( '(?P<id>[\d]+)' );
		$this->add_endpoint( $get_route );
		$this->add_endpoint( $update_route );
		$this->add_endpoint( $delete_route );
	}

}
