<?php

use JFB\LimitResponses\Plugin;
use JFB\LimitResponses\Vendor\Auryn\InjectionException;
use JFB\LimitResponses\Vendor\Auryn\Injector;
use JFB\LimitResponses\Vendor\Auryn\ConfigException;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

/**
 * @throws ConfigException
 * @throws InjectionException
 */
function jet_fb_limit_forms_setup() {
	/** @var Plugin $plugin */

	$injector = new Injector();
	$plugin   = new Plugin( $injector );
	$injector->share( $plugin );

	$plugin->setup();

	add_filter(
		'jet-fb/limit-form-responses/injector',
		function () use ( $injector ) {
			return $injector;
		}
	);

	do_action( 'jet-fb/limit-form-responses/setup', $injector );
}

function jet_fb_limit_forms_injector(): Injector {
	return apply_filters( 'jet-fb/limit-form-responses/injector', false );
}
