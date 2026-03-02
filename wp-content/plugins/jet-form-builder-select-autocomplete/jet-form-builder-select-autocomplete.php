<?php
/**
 * Plugin Name: JetFormBuilder Select Autocomplete
 * Plugin URI:  https://jetformbuilder.com/addons/select-autocomplete/
 * Description: A tweak to auto-fill the values dynamically in the Select field type.
 * Version:     1.0.7
 * Author:      Crocoblock
 * Author URI:  https://crocoblock.com/
 * Text Domain: jet-form-builder-select-autocomplete
 * License:     GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

define( 'JET_FB_SELECT_AUTOCOMPLETE_VERSION', '1.0.7' );

define( 'JET_FB_SELECT_AUTOCOMPLETE__FILE__', __FILE__ );
define( 'JET_FB_SELECT_AUTOCOMPLETE_PLUGIN_BASE', plugin_basename( __FILE__ ) );
define( 'JET_FB_SELECT_AUTOCOMPLETE_PATH', plugin_dir_path( __FILE__ ) );
define( 'JET_FB_SELECT_AUTOCOMPLETE_URL', plugins_url( '/', __FILE__ ) );

require __DIR__ . '/includes/load.php';

