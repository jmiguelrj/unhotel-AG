<?php

namespace JFB_Formless\Adapters;

use Jet_Form_Builder\Db_Queries\Exceptions\Sql_Exception;
use Jet_Form_Builder\Db_Queries\Query_Conditions_Builder;
use Jet_Form_Builder\Exceptions\Query_Builder_Exception;
use JFB_Formless\Services\Route;
use JFB_Formless\Services\ValidateException;
use JFB_Formless\DB\Views;
use JFB_Formless\DB\Models;

class RouteToDatabase {

	/**
	 * @var Route
	 */
	private $route;
	/**
	 * @var Models\Routes
	 */
	private $model;
	/**
	 * @var Views\Routes
	 */
	private $view;

	public function __construct(
		Views\Routes $view,
		Models\Routes $model
	) {
		$this->view  = $view;
		$this->model = $model;
	}

	/**
	 * @return void
	 * @throws Sql_Exception|ValidateException
	 */
	public function create() {
		$this->get_route()->validate_required();

		$this->get_route()->set_id(
			$this->model->insert(
				iterator_to_array( $this->generate_db_row() )
			)
		);
	}

	/**
	 * @return void
	 * @throws Query_Builder_Exception
	 * @throws Sql_Exception
	 */
	public function update() {
		$route = $this->view::findById( $this->get_route()->get_id() );

		if ( ! $this->has_changes( $route ) ) {
			return;
		}

		$this->model->update(
			iterator_to_array( $this->generate_db_row() ),
			array(
				'id' => $this->get_route()->get_id(),
			)
		);
	}

	/**
	 * @return void
	 * @throws Sql_Exception
	 */
	public function delete() {
		$this->model->delete(
			array(
				'id' => $this->get_route()->get_id(),
			)
		);
	}

	/**
	 * @return void
	 * @throws Query_Builder_Exception
	 * @throws ValidateException
	 */
	public function find() {
		// reset to defaults
		if ( $this->get_route()->get_id() ) {
			$row = $this->view::findById( $this->get_route()->get_id() );
			$this->get_route()->reset();

			$this->from_row( $row );

			return;
		}
		$this->view->set_conditions(
			array(
				array(
					'type'   => Query_Conditions_Builder::TYPE_EQUAL,
					'values' => array( 'form_id', $this->get_route()->get_form_id() ),
				),
			)
		);
		if ( $this->get_route()->get_action_type() ) {
			$this->view->add_conditions(
				array(
					array(
						'type'   => Query_Conditions_Builder::TYPE_EQUAL,
						'values' => array( 'action_type', $this->get_route()->get_action_type() ),
					),
				)
			);
		}

		$row = $this->view->set_limit( array( 1 ) )->query()->query_one();
		$this->get_route()->reset();

		$this->from_row( $row );
	}

	public function generate_db_row(): \Generator {
		yield 'form_id' => $this->get_route()->get_form_id();

		if ( ! is_null( $this->get_route()->get_action_type() ) ) {
			yield 'action_type' => $this->get_route()->get_action_type();
		}
		if ( ! is_null( $this->get_route()->get_restriction_type() ) ) {
			yield 'restriction_type' => $this->get_route()->get_restriction_type();
		}
		if ( ! is_null( $this->get_route()->get_restriction_cap() ) ) {
			yield 'restriction_cap' => $this->get_route()->get_restriction_cap();
		}
		if ( ! is_null( $this->get_route()->get_restriction_roles() ) ) {
			yield 'restriction_roles' => wp_json_encode( $this->get_route()->get_restriction_roles() );
		}
		if ( ! is_null( $this->get_route()->is_log() ) ) {
			yield 'log' => (int) $this->get_route()->is_log();
		}
		if ( ! is_null( $this->get_route()->is_restricted() ) ) {
			yield 'restricted' => (int) $this->get_route()->is_restricted();
		}
	}

	/**
	 * @param array $route_row
	 *
	 * @return void
	 * @throws ValidateException
	 */
	public function from_row( array $route_row ) {
		if ( isset( $route_row['id'] ) ) {
			$this->get_route()->set_id( (int) $route_row['id'] );
		}
		if ( isset( $route_row['form_id'] ) ) {
			$this->get_route()->set_form_id( (int) $route_row['form_id'] );
		}
		if ( isset( $route_row['action_type'] ) ) {
			$this->get_route()->set_action_type( (int) $route_row['action_type'] );
		}
		if ( isset( $route_row['restriction_type'] ) ) {
			$this->get_route()->set_restriction_type( $route_row['restriction_type'] );
		}
		if ( isset( $route_row['restriction_cap'] ) ) {
			$this->get_route()->set_restriction_cap( $route_row['restriction_cap'] );
		}
		if ( isset( $route_row['restriction_roles'] ) ) {
			if ( is_array( $route_row['restriction_roles'] ) ) {
				$this->get_route()->set_restriction_roles( $route_row['restriction_roles'] );
			} else {
				$this->get_route()->set_restriction_roles( json_decode( $route_row['restriction_roles'], true ) );
			}
		}
		if ( isset( $route_row['log'] ) ) {
			$this->get_route()->set_log( (bool) $route_row['log'] );
		}
		if ( isset( $route_row['restricted'] ) ) {
			$this->get_route()->set_restricted( (bool) $route_row['restricted'] );
		}
	}

	/**
	 * @param array $base_route
	 *
	 * @return bool
	 */
	private function has_changes( array $base_route ): bool {
		$new_row = iterator_to_array( $this->generate_db_row() );

		foreach ( $new_row as $model_column => $model_value ) {
			if ( $base_route[ $model_column ] === $model_value ) {
				continue;
			}

			return true;
		}

		return false;
	}

	/**
	 * @return Route
	 */
	public function get_route(): Route {
		return $this->route;
	}

	/**
	 * @param Route $route
	 */
	public function set_route( Route $route ) {
		$this->route = $route;
	}

}
