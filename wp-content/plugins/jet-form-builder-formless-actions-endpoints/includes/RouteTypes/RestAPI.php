<?php

namespace JFB_Formless\RouteTypes;

use JFB_Formless\RouteTypes\Injectors\SecurityFieldsInsideBodyInjector;
use JFB_Formless\RouteTypes\Interfaces\RouteInterface;
use JFB_Formless\RouteTypes\Traits\BaseRouteTrait;


class RestAPI implements RouteInterface {

	use BaseRouteTrait;

	private $injector;

	public function __construct(
		SecurityFieldsInsideBodyInjector $injector
	) {
		$this->injector = $injector;
	}

	public function get_url(): string {
		return rest_url(
			$this->get_route_meta()->get_property( 'rest_namespace' ) .
			'/' .
			$this->get_route_meta()->get_property( 'rest_route' )
		);
	}

	public function fetch_meta() {
		/** @noinspection PhpUnhandledExceptionInspection */
		$this->get_route_meta()->set_properties( array( 'rest_namespace', 'rest_route' ) );
		/** @noinspection PhpUnhandledExceptionInspection */
		$this->get_route_meta()->find();
	}

	public function generate_attributes(): \Generator {
		yield 'url' => $this->get_url();

		$this->injector->inject( $this );

		if ( $this->get_body() ) {
			yield 'body' => $this->get_body();
		}
	}

}
