<?php
/**
 * Plugin Name: JetEngine - Layout Switcher
 * Plugin URI:
 * Description: Layout Switcher module for JetEngine Listing Grid
 * Version:     1.0.0
 * Author:      Crocoblock
 * Author URI:  https://crocoblock.com/
 * Text Domain: jet-engine-layout-switcher
 * License:     GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

define( 'JET_ENGINE_LAYOUT_SWITCHER_VERSION', '1.0.0' );

define( 'JET_ENGINE_LAYOUT_SWITCHER__FILE__', __FILE__ );
define( 'JET_ENGINE_LAYOUT_SWITCHER_PLUGIN_BASE', plugin_basename( JET_ENGINE_LAYOUT_SWITCHER__FILE__ ) );
define( 'JET_ENGINE_LAYOUT_SWITCHER_PATH', plugin_dir_path( JET_ENGINE_LAYOUT_SWITCHER__FILE__ ) );
define( 'JET_ENGINE_LAYOUT_SWITCHER_URL', plugins_url( '/', JET_ENGINE_LAYOUT_SWITCHER__FILE__ ) );

add_action( 'plugins_loaded', 'jet_engine_layout_switcher_init' );

function jet_engine_layout_switcher_init() {
	require JET_ENGINE_LAYOUT_SWITCHER_PATH . 'includes/plugin.php';
}

function jet_engine_layout_switcher() {
	return Jet_Engine_Layout_Switcher\Plugin::instance();
}
