<?php

namespace JFB_Formless\Services;

use Jet_Form_Builder\Db_Queries\Exceptions\Sql_Exception;
use Jet_Form_Builder\Db_Queries\Query_Conditions_Builder;
use Jet_Form_Builder\Exceptions\Query_Builder_Exception;
use JFB_Formless\DB\Models;
use JFB_Formless\DB\Views;
use JFB_Formless\Plugin;
use JFB_Formless\RouteTypes\Validators;
use JFB_Formless\Vendor\Auryn\InjectionException;

class RouteMeta {

	private $view;
	private $model;
	/**
	 * @var Route
	 */
	private $route;
	private $plugin;

	protected $properties = array();

	public function __construct(
		Models\RoutesMeta $model,
		Views\RoutesMeta $view,
		Plugin $plugin
	) {
		$this->model  = $model;
		$this->view   = $view;
		$this->plugin = $plugin;
	}


	/**
	 * @return void
	 * @throws Sql_Exception|ValidateException
	 */
	public function update() {
		if ( ! $this->get_route() ) {
			throw new ValidateException( 'missed_route' );
		}

		$properties = $this->get_properties();
		if ( ! $properties ) {
			return;
		}

		$this->view->set_conditions( $this->get_conditions() )->set_select(
			array(
				'route_key',
				'route_value',
			)
		);

		try {
			$existing_meta = $this->view->query()->query_all();
		} catch ( Query_Builder_Exception $exception ) {
			$this->insert_many( $properties );

			return;
		}

		foreach ( $existing_meta as $route_meta ) {
			$updated_value = $properties[ $route_meta['route_key'] ];

			if ( '' === ( (string) $updated_value ) ) {
				$this->delete_single( $route_meta['route_key'] );
				unset( $properties[ $route_meta['route_key'] ] );
				continue;
			}

			if ( $updated_value !== $route_meta['route_value'] ) {
				$this->update_single( $route_meta['route_key'], $updated_value );
			}
			// leave only values for insert
			unset( $properties[ $route_meta['route_key'] ] );
		}

		$this->insert_many( $properties );
	}

	/**
	 * @return void
	 * @throws Query_Builder_Exception
	 * @throws ValidateException
	 */
	public function find() {
		if ( ! $this->get_route() ) {
			throw new ValidateException( 'missed_route' );
		}
		if ( ! $this->get_properties() ) {
			throw new ValidateException( 'missed_properties' );
		}

		$this->view->set_conditions( $this->get_conditions() )->set_select(
			array(
				'route_key',
				'route_value',
			)
		);

		try {
			$this->set_properties(
				iterator_to_array(
					$this->generate_map_from_rows( $this->view->query()->query_all() )
				)
			);
		} catch ( Query_Builder_Exception $exception ) {
			// reset properties
			$this->set_properties( array() );

			throw $exception;
		}
	}

	/**
	 * @param string $route_key
	 * @param $value
	 *
	 * @return void
	 * @throws Sql_Exception
	 */
	private function update_single( string $route_key, $value ) {
		$this->model->update(
			array(
				'route_value' => (string) $value,
			),
			array(
				'route_key' => $route_key,
				'route_id'  => $this->get_route()->get_id(),
			)
		);
	}

	/**
	 * @param string $route_key
	 *
	 * @return void
	 * @throws Sql_Exception
	 */
	private function delete_single( string $route_key ) {
		$this->model->delete(
			array(
				'route_key' => $route_key,
				'route_id'  => $this->get_route()->get_id(),
			)
		);
	}

	/**
	 * @param array $properties
	 *
	 * @return void
	 * @throws Sql_Exception
	 */
	private function insert_many( array $properties ) {
		$insert_rows = iterator_to_array(
			$this->generate_rows_from_map( $properties )
		);

		$this->model->insert_many( $insert_rows );
	}

	private function get_conditions(): array {
		$properties = $this->get_properties();

		// resolve all meta fields
		if ( '*' === ( $properties[0] ?? '' ) ) {
			return array(
				array(
					'type'   => Query_Conditions_Builder::TYPE_EQUAL,
					'values' => array( 'route_id', $this->get_route()->get_id() ),
				),
			);
		}

		if ( ! wp_is_numeric_array( $properties ) ) {
			$properties = array_keys( $properties );
		}

		return array(
			array(
				'type'   => Query_Conditions_Builder::TYPE_IN,
				'values' => array( 'route_key', $properties ),
			),
			array(
				'type'   => Query_Conditions_Builder::TYPE_EQUAL,
				'values' => array( 'route_id', $this->get_route()->get_id() ),
			),
		);
	}

	private function generate_rows_from_map( array $properties ): \Generator {
		foreach ( $properties as $route_key => $route_value ) {
			yield array(
				'route_id'    => $this->get_route()->get_id(),
				'route_key'   => $route_key,
				'route_value' => (string) $route_value,
			);
		}
	}

	private function generate_map_from_rows( array $route_meta_rows ): \Generator {
		foreach ( $route_meta_rows as $route_meta_row ) {
			yield $route_meta_row['route_key'] => $route_meta_row['route_value'];
		}
	}

	public function validate() {
		foreach ( $this->declare_validators() as $validator_class ) {
			/** @var Validators\Interfaces\ValidatorInterface $validator */
			/** @noinspection PhpUnhandledExceptionInspection */
			$validator = $this->plugin->get_injector()->make( $validator_class );

			if ( ! $validator->is_supported( $this ) ) {
				continue;
			}

			$validator->apply( $this );
		}
	}

	/**
	 * @param array $properties
	 *
	 * @return void
	 * @throws ValidateException
	 */
	public function set_properties( array $properties ) {
		foreach ( $properties as $route_key => $route_value ) {
			if ( is_scalar( $route_value ) ) {
				continue;
			}
			throw new ValidateException( 'invalid_route_meta_value' );
		}
		$this->properties = $properties;
	}

	public function set_property( string $name, $value ) {
		$this->properties[ $name ] = $value;
	}

	public function get_property( string $route_key ) {
		return $this->properties[ $route_key ] ?? false;
	}

	/**
	 * @return array
	 */
	public function get_properties(): array {
		return $this->properties;
	}

	/**
	 * @param Route $route
	 */
	public function set_route( Route $route ) {
		$this->route = $route;
	}

	/**
	 * @return Route|null
	 */
	public function get_route() {
		return $this->route;
	}

	private function declare_validators(): \Generator {
		yield Validators\RestAPIEndpoint::class;
		yield Validators\AJAXEndpoint::class;
		yield Validators\URLQueryEndpoint::class;
	}

}
