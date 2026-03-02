<?php


namespace JetHRSelectCore\JetFormBuilder;


abstract class RestApiProxy {

	use WithInit;

	abstract public function routes(): array;

	public function plugin_version_compare(): string {
		return '2.0.0';
	}

	protected function get_controller_instance() {
		return new class extends \Jet_Form_Builder\Rest_Api\Rest_Api_Controller_Base {

			private $static = array();

			public function set_static( $routes ) {
				$this->static = $routes;

				return $this;
			}

			public function routes(): array {
				return $this->static;
			}
		};
	}

	public function on_plugin_init() {
		$this->get_controller_instance()->set_static( $this->routes() )->rest_api_init();
	}


}