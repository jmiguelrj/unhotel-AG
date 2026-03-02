<?php


namespace JFB_Formless;

use JFB_Formless\Vendor\Auryn\ConfigException;
use JFB_Formless\Vendor\Auryn\InjectionException;
use JFB_Formless\Vendor\Auryn\Injector;
use JFB_Formless\Modules\Routes;
use JFB_Formless\Modules\Listeners;
use JFB_Formless\Modules\BlockEditor;
use JFB_Formless\Modules\FrontComponents;
use JFB_Formless\Modules\Elementor;
use JFB_Formless\Modules\Bricks;
use JFB_Formless\Modules\PluginsPage;

final class Plugin {

	private $injector;

	const SLUG = 'jet-form-builder-formless-actions-endpoints';

	public function __construct( Injector $injector ) {
		$this->injector = $injector;

		// for bricks compatibility
		add_action( 'after_setup_theme', array( $this, 'after_setup_theme' ) );
	}

	private function declare_modules(): \Generator {
		yield FrontComponents\Module::class;
		yield Routes\Module::class;
		yield Listeners\Module::class;
		yield BlockEditor\Module::class;
		yield PluginsPage\Module::class;

		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			yield Elementor\Module::class;
		}
	}

	/**
	 * @throws ConfigException|InjectionException
	 */
	public function setup() {
		$this->injector->share( Routes\Pages\Routes::class );
		$this->injector->share( Routes\Pages\RouteSingle::class );
		$this->injector->share( RouteTypes\Builder::class );
		$this->injector->share( Routes\AJAX\CheckIsUniqueAjaxEndpoint::class );

		foreach ( $this->declare_modules() as $module_class ) {
			$this->injector->share( $module_class );
		}

		foreach ( $this->declare_modules() as $module_class ) {
			$this->injector->make( $module_class );
		}

		\JFB_License_Manager::instance();
	}

	/**
	 * @return void
	 * @throws ConfigException
	 * @throws InjectionException
	 */
	public function after_setup_theme() {
		if ( ! defined( 'BRICKS_VERSION' ) ) {
			return;
		}
		$this->injector->share( Bricks\Module::class );
		$this->injector->make( Bricks\Module::class );
	}

	public function get_injector(): Injector {
		return $this->injector;
	}

}
