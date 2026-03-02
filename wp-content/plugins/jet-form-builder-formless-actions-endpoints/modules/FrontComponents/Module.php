<?php

namespace JFB_Formless\Modules\FrontComponents;

use JFB_Modules\Jet_Plugins;

class Module {

	const HANDLE          = 'crocoblock-front-components';
	const HANDLE_REDIRECT = self::HANDLE . '-redirect';


	public function __construct() {
		add_action(
			'jet-form-builder-formless/frontend-assets',
			array( $this, 'register_assets' )
		);
	}

	public function register_assets() {
		$core_asset     = require_once $this->get_path( 'assets/build/core.asset.php' );
		$redirect_asset = require_once $this->get_path( 'assets/build/redirect.asset.php' );

		// if it had already executed before
		if ( true === $core_asset ) {
			return;
		}

		$core_url     = $this->get_url( 'assets/build/core.js' );
		$redirect_url = $this->get_url( 'assets/build/redirect.js' );

		wp_register_script(
			self::HANDLE,
			$core_url,
			$core_asset['dependencies'],
			$core_asset['version'],
			true
		);

		$this->register_jet_plugins_lib();
		array_push(
			$redirect_asset['dependencies'],
			'jet-plugins'
		);

		wp_register_script(
			self::HANDLE_REDIRECT,
			$redirect_url,
			$redirect_asset['dependencies'],
			$redirect_asset['version'],
			true
		);

		$style_url = $this->get_url( 'assets/build/core.css' );

		wp_register_style(
			self::HANDLE,
			$style_url,
			array(),
			$core_asset['version']
		);
	}

	public function get_url( string $url = '' ): string {
		return JFB_FORMLESS_URL . 'modules/FrontComponents/' . $url;
	}

	public function get_path( string $path = '' ): string {
		return JFB_FORMLESS_PATH . 'modules/FrontComponents/' . $path;
	}

	private function register_jet_plugins_lib() {
		/** @var Jet_Plugins\Module $lib_module */
		/** @noinspection PhpUnhandledExceptionInspection */
		$lib_module = jet_form_builder()->module( 'jet-plugins' );

		$lib_module->register_scripts();
	}

}
