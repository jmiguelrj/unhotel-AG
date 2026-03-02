<?php
/**
 * Plugin Name:         JetFormBuilder Schedule Forms
 * Plugin URI:          https://jetformbuilder.com/addons/schedule-forms/
 * Description:         A supplementary plugin that lets you set the form availability time frame.
 * Version:             1.0.3
 * Author:              Crocoblock
 * Author URI:          https://crocoblock.com/
 * Text Domain:         jet-form-builder-schedule-forms
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

define( 'JET_FB_SCHEDULE_FORMS_VERSION', '1.0.3' );

define( 'JET_FB_SCHEDULE_FORMS__FILE__', __FILE__ );
define( 'JET_FB_SCHEDULE_FORMS_PLUGIN_BASE', plugin_basename( __FILE__ ) );
define( 'JET_FB_SCHEDULE_FORMS_PATH', plugin_dir_path( __FILE__ ) );
define( 'JET_FB_SCHEDULE_FORMS_URL', plugins_url( '/', __FILE__ ) );

require __DIR__ . '/includes/load.php';
