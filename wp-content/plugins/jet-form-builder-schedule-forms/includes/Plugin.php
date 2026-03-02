<?php

namespace JFB\ScheduleForms;

use JFB\ScheduleForms\Queries\SettingsQuery;
use JFB\ScheduleForms\Vendor\Auryn\Injector;
use JFB\ScheduleForms\Vendor\JFBCore\LicenceProxy;

if ( ! defined( 'WPINC' ) ) {
	die();
}

class Plugin {

	const SLUG = 'jet-form-builder-schedule-forms';

	private $injector;

	public function __construct( Injector $injector ) {
		$this->injector = $injector;
	}

	/**
	 * @return void
	 * @throws Vendor\Auryn\ConfigException
	 * @throws Vendor\Auryn\InjectionException
	 */
	public function setup() {
		JetFormBuilder\PluginManager::register();

		// jet-form-builder
		$this->injector->share( SettingsQuery::class );
		$this->injector->share( ScheduleForm::class );
		$this->injector->make( JetFormBuilder\PreventFormSubmit::class );
		$this->injector->make( JetFormBuilder\PreventFormRender::class );
		$this->injector->make( JetFormBuilder\StyleManager::class );

		// jet-engine
		$this->injector->make( JetEngine\ScheduleMetaBox::class );
		$this->injector->make( JetEngine\PreventFormSubmit::class );
		$this->injector->make( JetEngine\PreventFormRender::class );

		// common
		$this->injector->make( ElementorStyleManager::class );

		LicenceProxy::register();

	}

}