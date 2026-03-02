<?php
/**
 * Plugin Name:         JetFormBuilder Limit Form Responses
 * Plugin URI:          https://jetformbuilder.com/addons/limit-form-responses/
 * Description:         A lightweight addon to control the overall number of form submissions and those per user.
 * Version:             1.1.1
 * Author:              Crocoblock
 * Author URI:          https://crocoblock.com/
 * Text Domain:         jet-form-builder-limit-form-responses
 * License:             GPL-3.0+
 * License URI:         http://www.gnu.org/licenses/gpl-3.0.txt
 * Requires PHP:        7.0
 * Requires at least:   6.0
 * Domain Path:         /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

define( 'JET_FB_LIMIT_FORM_RESPONSES_VERSION', '1.1.1' );

define( 'JET_FB_LIMIT_FORM_RESPONSES__FILE__', __FILE__ );
define( 'JET_FB_LIMIT_FORM_RESPONSES_PLUGIN_BASE', plugin_basename( __FILE__ ) );
define( 'JET_FB_LIMIT_FORM_RESPONSES_PATH', plugin_dir_path( __FILE__ ) );
define( 'JET_FB_LIMIT_FORM_RESPONSES_URL', plugins_url( '/', __FILE__ ) );

require __DIR__ . '/includes/load.php';

