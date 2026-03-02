<?php

namespace JFB\LimitResponses;

use JFB\LimitResponses\MetaQueries\FormCountersMetaQuery;
use JFB\LimitResponses\MetaQueries\SettingsMetaQuery;
use JFB\LimitResponses\MetaQueries\UserCounterMetaQuery;
use JFB\LimitResponses\Vendor\Auryn\Injector;
use JFB\LimitResponses\Vendor\JFBCore\LicenceProxy;

if ( ! defined( 'WPINC' ) ) {
	die();
}

final class Plugin {

	const SLUG = 'jet-form-builder-limit-form-responses';

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
		$this->injector->share( LimitResponses::class );
		$this->injector->share( SettingsMetaQuery::class );
		$this->injector->share( FormCountersMetaQuery::class );
		$this->injector->share( UserCounterMetaQuery::class );
		$this->injector->make( LimitResponses::class );

		add_action( 'after_setup_theme', array( $this, 'init_components' ), - 9999 );
	}

	/**
	 * @return void
	 * @throws Vendor\Auryn\InjectionException
	 */
	public function init_components() {
		// jet-engine
		$this->injector->make( JetEngine\MetaBox::class );
		$this->injector->make( JetEngine\PreventSubmit::class );
		$this->injector->make( JetEngine\PreventRender::class );

		// jetformbuilder
		JetFormBuilder\LimitPluginManager::register();
		$this->injector->make( JetFormBuilder\PreventSubmit::class );
		$this->injector->make( JetFormBuilder\PreventRender::class );

		$this->injector->make( ElementorStyleManager::class );
		$this->injector->make( GutenbergStyleManager::class );

		LicenceProxy::register();
	}

	public function get_injector(): Injector {
		return $this->injector;
	}

}
