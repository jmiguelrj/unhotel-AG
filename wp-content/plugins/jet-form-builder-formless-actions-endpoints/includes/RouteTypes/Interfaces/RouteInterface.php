<?php

namespace JFB_Formless\RouteTypes\Interfaces;

use JFB_Formless\Services;

interface RouteInterface {

	const ATTRIBUTE        = 'data-jfb-submit-endpoint';
	const CONTEXT_FRONTEND = 'frontend';

	public function get_url(): string;

	public function fetch_meta();

	public function generate_attributes(): \Generator;

	/**
	 * @return array|null
	 */
	public function get_body();

	/**
	 * @param array|string $body
	 *
	 * @return void
	 */
	public function set_body( $body );

	public function set_route_meta( Services\RouteMeta $route_meta );

	public function get_route_meta(): Services\RouteMeta;


}
