<?php

use JFB_Formless\Plugin;
use JFB_Formless\Vendor\Auryn\Injector;
use JFB_Formless\Vendor\Auryn\InjectionException;
use JFB_Formless\Vendor\Auryn\ConfigException;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

/**
 * @throws InjectionException|ConfigException
 */
function jet_fb_formless_setup() {
	/**
	 * We use additional check for case, when site administrator manually
	 * delete or deactivate JetFormBuilder plugin (not via plugin's page)
	 */
	if ( ! function_exists( 'jet_form_builder' ) ) {
		return;
	}

	/** @var Plugin $plugin */

	$injector = new Injector();
	$plugin   = new Plugin( $injector );
	$injector->share( $plugin );

	$plugin->setup();

	add_filter(
		'jet-form-builder-formless/injector',
		function () use ( $injector ) {
			return $injector;
		}
	);

	do_action( 'jet-form-builder-formless/setup', $injector );
}

function jet_fb_formless_injector(): Injector {
	return apply_filters( 'jet-form-builder-formless/injector', false );
}
