<?php

namespace JFB_Formless\Modules\Routes\REST;

use JFB_Formless\Modules\Routes\Module;
use JFB_Formless\Modules\Routes\REST\Endpoints\DeleteProtection;
use JFB_Formless\Modules\Routes\REST\Endpoints\HasProtection;
use JFB_Formless\REST\Route;

class Protection extends Route {

	public function __construct(
		HasProtection $has_protection,
		DeleteProtection $delete_protection
	) {
		$this->set_namespace( Module::REST_NAMESPACE );
		$this->set_route( '(?P<form_id>[\d]+)/protection' );
		$this->add_endpoint( $has_protection );
		$this->add_endpoint( $delete_protection );
	}

}
