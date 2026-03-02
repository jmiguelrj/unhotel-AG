<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/functions.php';

// legacy
class_alias(
	'\JFB\LimitResponses\LimitResponses',
	'\Jet_FB_Limit_Form_Responses\LimitResponses'
);
class_alias(
	'\JFB\LimitResponses\Vendor\JFBCore\Common\MetaQuery',
	'\JetLimitResponsesCore\Common\MetaQuery'
);

add_action( 'plugins_loaded', 'jet_fb_limit_forms_setup', 100 );
