<?php

namespace JFB_Formless\Modules\Routes\REST;

use JFB_Formless\Modules\Routes\Module;
use JFB_Formless\REST\Route;

class FetchPreset extends Route {

	public function __construct(
		Endpoints\GetPresetValue $fields
	) {
		$this->set_namespace( Module::REST_NAMESPACE );
		$this->set_route( 'fetch-preset' );
		$this->add_endpoint( $fields );
	}

}
