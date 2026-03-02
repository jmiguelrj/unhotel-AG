<?php

namespace JFB_Formless\DB\Constraints;

use Jet_Form_Builder\Db_Queries\Base_Db_Constraint;
use JFB_Formless\DB\Models;

class Routes extends Base_Db_Constraint {

	public function __construct() {
		$this->set_model( new Models\Routes() );
		$this->set_foreign_keys( array( 'route_id' ) );
		$this->on_delete( self::ACTION_CASCADE );
	}

}
