<?php

namespace JFB_Formless\DB\Constraints;

use Jet_Form_Builder\Db_Queries\Base_Db_Constraint;
use JFB_Formless\DB\Models;

class RequestLogs extends Base_Db_Constraint {

	public function __construct() {
		$this->set_model( new Models\RequestLogs() );
		$this->set_foreign_keys( array( 'request_id' ) );
		$this->on_delete( self::ACTION_CASCADE );
	}

}
