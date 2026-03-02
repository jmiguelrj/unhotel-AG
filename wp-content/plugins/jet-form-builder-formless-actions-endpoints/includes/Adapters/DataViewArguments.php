<?php

namespace JFB_Formless\Adapters;

use Jet_Form_Builder\Db_Queries\Views\View_Base;

class DataViewArguments {

	public function inject_arguments( View_Base $view, array $params ) {
		$limit  = (int) ( $params['limit'] ?? 20 );
		$page   = (int) ( $params['page'] ?? 1 );
		$offset = $this->get_offset( $page, $limit );

		$order    = strtoupper( $params['order'] ?? View_Base::FROM_HIGH_TO_LOW );
		$order_by = sanitize_key( $params['orderBy'] ?? '' );

		if ( ! in_array(
			$order,
			array( View_Base::FROM_HIGH_TO_LOW, View_Base::FROM_LOW_TO_HIGH ),
			true
		) ) {
			$order = View_Base::FROM_HIGH_TO_LOW;
		}

		if ( $order_by ) {
			$view->set_order_by(
				array(
					array( 'column' => $order_by, 'sort' => $order ),
				)
			);
		}

		$view->set_limit( array( $offset, $limit ) );
	}

	private function get_offset( int $page, int $limit ) {
		return 1 === $page ? 0 : ( ( $page - 1 ) * $limit );
	}

}
