<?php

namespace JFB_Formless\DB\Views;

use Jet_Form_Builder\Db_Queries\Query_Builder;
use Jet_Form_Builder\Db_Queries\Query_Conditions_Builder;
use Jet_Form_Builder\Db_Queries\Views\View_Base;
use Jet_Form_Builder\Exceptions\Query_Builder_Exception;
use JFB_Formless\DB\Models;

class Routes extends View_Base {

	protected $order_by = array(
		array(
			'column' => 'id',
			'sort'   => self::FROM_HIGH_TO_LOW,
		),
	);

	public function table(): string {
		return Models\Routes::table();
	}

	public function query(): Query_Builder {
		$this->prepare_dependencies();

		if ( ! $this->select ) {
			$this->set_select( Models\Routes::schema_columns() );
		}

		return ( new Query_Builder() )->set_view( $this );
	}

	/**
	 * TODO: Make the same in the JetFormBuilder
	 *
	 * @param array $order_by
	 *
	 * @return View_Base
	 */
	public function set_order_by( array $order_by ): View_Base {
		$this->order_by = $order_by;

		return $this;
	}

	public function get_dependencies(): array {
		return array(
			new Models\Routes(),
		);
	}

}
