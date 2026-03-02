<?php

use JFB\ScheduleForms\Plugin;
use JFB\ScheduleForms\Vendor\Auryn\InjectionException;
use JFB\ScheduleForms\Vendor\Auryn\Injector;
use JFB\ScheduleForms\Vendor\Auryn\ConfigException;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

/**
 * @throws ConfigException
 * @throws InjectionException
 */
function jet_fb_schedule_setup() {
	/** @var Plugin $plugin */

	$injector = new Injector();
	$plugin   = new Plugin( $injector );
	$injector->share( $plugin );

	$plugin->setup();

	add_filter(
		'jet-fb/schedule-forms/injector',
		function () use ( $injector ) {
			return $injector;
		}
	);

	do_action( 'jet-fb/schedule-forms/setup', $injector );
}

function jet_fb_schedule_injector(): Injector {
	return apply_filters( 'jet-fb/schedule-forms/injector', false );
}
