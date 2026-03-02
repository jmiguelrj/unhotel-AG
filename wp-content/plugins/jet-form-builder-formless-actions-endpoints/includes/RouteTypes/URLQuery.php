<?php

namespace JFB_Formless\RouteTypes;

use JFB_Formless\RouteTypes\Injectors\SecurityFieldsInsideBodyInjector;
use JFB_Formless\RouteTypes\Interfaces\RouteInterface;
use JFB_Formless\RouteTypes\Traits\BaseRouteTrait;
class URLQuery implements RouteInterface {

	use BaseRouteTrait;

	private $injector;

	public function __construct(
		SecurityFieldsInsideBodyInjector $injector
	) {
		$this->injector = $injector;
	}

	/**
	 * @return string
	 */
	public function get_url(): string {
		$url = add_query_arg(
			array(
				'jfb_submit' => $this->get_route_meta()->get_property( 'url_action' ),
			),
			user_trailingslashit( site_url() )
		);

		if ( ! $this->get_body() && ! $this->is_frontend_context() ) {
			return $url;
		}

		$this->injector->inject( $this );

		if ( ! $this->get_body() ) {
			return $url;
		}

		return add_query_arg(
			array(
				'data' => $this->get_body(),
			),
			$url
		);
	}

	public function fetch_meta() {
		/** @noinspection PhpUnhandledExceptionInspection */
		$this->get_route_meta()->set_properties( array( 'url_action' ) );
		/** @noinspection PhpUnhandledExceptionInspection */
		$this->get_route_meta()->find();
	}

	public function generate_attributes(): \Generator {
		yield 'url' => $this->get_url();
	}

}
