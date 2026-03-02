<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/functions.php';

// legacy
class_alias(
	'\JFB\ScheduleForms\ScheduleForm',
	'\Jet_FB_Schedule_Forms\ScheduleForm'
);

add_action( 'plugins_loaded', 'jet_fb_schedule_setup', 100 );
