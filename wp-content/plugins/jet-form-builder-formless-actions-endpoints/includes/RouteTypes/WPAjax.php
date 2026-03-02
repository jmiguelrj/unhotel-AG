<?php

namespace JFB_Formless\RouteTypes;

use JFB_Formless\RouteTypes\Injectors\SecurityFieldsInsideBodyInjector;
use JFB_Formless\RouteTypes\Interfaces\RouteInterface;
use JFB_Formless\RouteTypes\Traits\BaseRouteTrait;

class WPAjax implements RouteInterface {

	use BaseRouteTrait;

	private $injector;

	public function __construct(
		SecurityFieldsInsideBodyInjector $injector
	) {
		$this->injector = $injector;
	}

	public function get_url(): string {
		return add_query_arg(
			array(
				'action' => $this->get_route_meta()->get_property( 'ajax_action' ),
			),
			admin_url( 'admin-ajax.php' )
		);
	}

	public function fetch_meta() {
		/** @noinspection PhpUnhandledExceptionInspection */
		$this->get_route_meta()->set_properties( array( 'ajax_action' ) );
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
