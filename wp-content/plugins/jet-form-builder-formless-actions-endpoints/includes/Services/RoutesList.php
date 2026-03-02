<?php

namespace JFB_Formless\Services;

use Jet_Form_Builder\Db_Queries\Query_Conditions_Builder;
use Jet_Form_Builder\Exceptions\Query_Builder_Exception;
use JFB_Formless\Vendor\Auryn\InjectionException;
use JFB_Formless\DB\Views;
use JFB_Formless\DB\Views\RoutesMeta;
use JFB_Formless\RouteTypes\Interfaces\RouteInterface;
use JFB_Formless\Services;
use JFB_Formless\RouteTypes\Builder;

class RoutesList {

	private $view;
	private $route_meta;
	private $builder;

	private $format_list = true;

	public function __construct(
		Views\Routes $view,
		Builder $builder,
		Services\RouteMeta $route_meta,
		Services\Route $route
	) {
		$this->view       = $view;
		$this->builder    = $builder;
		$this->route_meta = $route_meta;
		$this->route_meta->set_route( $route );
	}

	public function get_list(): array {
		try {
			$routes = $this->view->query()->query_all();
		} catch ( Query_Builder_Exception $exception ) {
			return array();
		}

		$this->inject_endpoints( $routes );

		return iterator_to_array(
			$this->format( $routes )
		);
	}

	private function format( array $routes ): \Generator {
		foreach ( $routes as $route ) {
			if ( empty( $route['path'] ) ) {
				continue;
			}
			if ( ! $this->is_format_list() ) {
				yield $route['id'] => $route['path'];
				continue;
			}

			yield array(
				'label' => $route['path'],
				'value' => $route['id'],
			);
		}
	}

	public function inject_endpoints( array &$routes ) {
		$ids = array();

		foreach ( $routes as $route ) {
			$ids[] = (int) ( $route['id'] ?? '' );
		}

		try {
			$meta_generator = RoutesMeta::find(
				array(
					array(
						'type'   => Query_Conditions_Builder::TYPE_IN,
						'values' => array( 'route_id', $ids ),
					),
					array(
						'type'   => Query_Conditions_Builder::TYPE_IN,
						'values' => array(
							'route_key',
							array( 'rest_namespace', 'rest_route', 'url_action', 'ajax_action' ),
						),
					),
				)
			)->query()->generate_all();

			$sorted_meta = array();

			foreach ( $meta_generator as $route_meta_row ) {
				$route_id  = $route_meta_row->route_id;
				$route_key = $route_meta_row->route_key ?? '';

				if ( ! isset( $sorted_meta[ $route_id ] ) ) {
					$sorted_meta[ $route_id ] = array();
				}

				$sorted_meta[ $route_id ][ $route_key ] = $route_meta_row->route_value;
			}
		} catch ( Query_Builder_Exception $exception ) {
			return;
		}

		$site_url = site_url();

		foreach ( $routes as &$route ) {
			if ( empty( $sorted_meta[ $route['id'] ] ) ) {
				continue;
			}
			/** @noinspection PhpUnhandledExceptionInspection */
			$render_route = $this->builder->create_from_action_type( $route['action_type'] ?? 0 );

			try {
				$this->route_meta->set_properties( $sorted_meta[ $route['id'] ] );
			} catch ( Services\ValidateException $exception ) {
				continue;
			}
			$render_route->set_route_meta( $this->route_meta );

			$route['url']  = $render_route->get_url();
			$route['path'] = str_replace( untrailingslashit( $site_url ), '', $route['url'] );
		}
	}


	/**
	 * @param bool $format_list
	 */
	public function set_format_list( bool $format_list ) {
		$this->format_list = $format_list;
	}

	/**
	 * @return bool
	 */
	public function is_format_list(): bool {
		return $this->format_list;
	}

}
