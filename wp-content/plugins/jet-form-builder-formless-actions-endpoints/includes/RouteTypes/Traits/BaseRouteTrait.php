<?php

namespace JFB_Formless\RouteTypes\Traits;

use JFB_Formless\RouteTypes\Interfaces\RouteInterface;
use JFB_Formless\Services;

trait BaseRouteTrait {

	/**
	 * @var Services\RouteMeta
	 */
	private $route_meta;

	/**
	 * @var array
	 */
	private $body;

	private $context;

	/**
	 * @param Services\RouteMeta $route_meta
	 */
	public function set_route_meta( Services\RouteMeta $route_meta ) {
		$this->route_meta = $route_meta;
	}

	/**
	 * @return Services\RouteMeta
	 */
	public function get_route_meta(): Services\RouteMeta {
		return $this->route_meta;
	}


	/**
	 * @return array|null
	 */
	public function get_body() {
		return $this->body;
	}

	public function set_body( $body ) {
		if ( is_array( $body ) ) {
			$this->body = $body;

			return;
		}

		$this->body = json_decode( $body, true );
	}

	public function set_context( string $context ) {
		$this->context = $context;
	}

	public function is_frontend_context(): bool {
		return RouteInterface::CONTEXT_FRONTEND === $this->context;
	}

}
