<?php

namespace JFB_Formless\Modules\Routes\REST;

use JFB_Formless\Modules\Routes\Module;
use JFB_Formless\REST\Route;

class GetAllFieldsRoute extends Route {

	public function __construct(
		Endpoints\GetAllFields $fields
	) {
		$this->set_namespace( Module::REST_NAMESPACE );
		$this->set_route( '(?P<id>[\d]+)/fields-v2' );
		$this->add_endpoint( $fields );
	}

}
