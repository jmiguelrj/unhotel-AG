<?php

namespace JFB_Formless\DB\Views;

use Jet_Form_Builder\Db_Queries\Query_Builder;
use Jet_Form_Builder\Db_Queries\Views\View_Base;
use JFB_Formless\DB\Models;

class ResponseLogs extends View_Base {

	protected $order_by = array(
		array(
			'column' => 'id',
			'sort'   => self::FROM_HIGH_TO_LOW,
		),
	);

	public function table(): string {
		return Models\ResponseLogs::table();
	}

	public function query(): Query_Builder {
		$this->prepare_dependencies();

		if ( ! $this->select ) {
			$this->set_select( Models\ResponseLogs::schema_columns() );
		}

		return ( new Query_Builder() )->set_view( $this );
	}

	public function get_dependencies(): array {
		return array(
			new Models\RequestLogs(),
		);
	}

}
