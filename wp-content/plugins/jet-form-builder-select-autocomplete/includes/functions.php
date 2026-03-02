<?php

use JFB\SelectAutocomplete\Plugin;
use JFB\SelectAutocomplete\Vendor\Auryn\InjectionException;
use JFB\SelectAutocomplete\Vendor\Auryn\Injector;
use JFB\SelectAutocomplete\Vendor\Auryn\ConfigException;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

/**
 * @throws ConfigException
 * @throws InjectionException
 */
function jet_fb_select_autocomplete_setup() {
	/** @var Plugin $plugin */

	$injector = new Injector();
	$plugin   = new Plugin( $injector );
	$injector->share( $plugin );

	$plugin->setup();

	add_filter(
		'jet-fb/select-autocomplete/injector',
		function () use ( $injector ) {
			return $injector;
		}
	);

	do_action( 'jet-fb/select-autocomplete/setup', $injector );
}

function jet_fb_select_autocomplete_injector(): Injector {
	return apply_filters( 'jet-fb/select-autocomplete/injector', false );
}
