<?php

namespace JFB\SelectAutocomplete;

use JFB\SelectAutocomplete\JetFormBuilder\AjaxHandler as JFBAjaxHandler;
use JFB\SelectAutocomplete\JetEngine\AjaxHandler as JEAjaxHandler;
use JFB\SelectAutocomplete\JetFormBuilder\SelectModifier as JFBSelectModifier;
use JFB\SelectAutocomplete\JetEngine\SelectModifier as JESelectModifier;
use JFB\SelectAutocomplete\Vendor\JFBCore\LicenceProxy;
use JFB\SelectAutocomplete\Vendor\Auryn\Injector;

if ( ! defined( 'WPINC' ) ) {
	die();
}

class Plugin {

	const SLUG = 'jet-form-builder-select-autocomplete';

	private $injector;

	public function __construct( Injector $injector ) {
		$this->injector = $injector;
	}

	public function setup() {
		JFBSelectModifier::register();
		JESelectModifier::register();

		new JFBAjaxHandler();
		new JEAjaxHandler();

		LicenceProxy::register();
	}

	/**
	 * @return Injector
	 */
	public function get_injector(): Injector {
		return $this->injector;
	}

}