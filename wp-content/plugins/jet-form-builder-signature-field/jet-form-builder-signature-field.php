<?php
/**
 * Plugin Name: JetFormBuilder Signature
 * Plugin URI:  https://jetformbuilder.com/addons/signature/
 * Description: Adds signature field for JetFormBuilder. This field allows the user to draw a signature, which will be saved as an image and can be used later as proof of identity.
 * Version:     1.0.1
 * Author:      Crocoblock
 * Author URI:  https://crocoblock.com/
 * Text Domain: jet-form-builder-signature-field
 * License:     GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path: /languages
 * Requires Plugins: jetformbuilder
 */

// Prevent direct access to the file, ensuring the file is only accessed through WordPress mechanisms.
if ( ! defined( 'WPINC' ) ) {
	die(); // Exit if accessed directly.
}

// Register an action with WordPress to initialize the plugin once all plugins are loaded.
add_action( 'plugins_loaded', 'jfb_signature_field_init', 99 );

/**
 * Initializes the Jet WooCommerce Product Table plugin.
 * This function defines several constants for use throughout the plugin and requires the main plugin class file.
 */
function jfb_signature_field_init() {

	// Define the current version of the plugin.
	define( 'JFB_SIGNATURE_FIELD_VERSION', '1.0.1' . time() );

	// Define a constant for the plugin file path.
	define( 'JFB_SIGNATURE_FIELD__FILE__', __FILE__ );

	// Define a base for the plugin, used as plugin slug where needed.
	define( 'JFB_SIGNATURE_FIELD_PLUGIN_BASE', plugin_basename( JFB_SIGNATURE_FIELD__FILE__ ) );

	// Define the absolute path to the plugin's directory.
	define( 'JFB_SIGNATURE_FIELD_PATH', plugin_dir_path( JFB_SIGNATURE_FIELD__FILE__ ) );

	// Define the URL to the plugin's directory, useful for loading assets like JavaScript and CSS files.
	define( 'JFB_SIGNATURE_FIELD_URL', plugins_url( '/', JFB_SIGNATURE_FIELD__FILE__ ) );

	// Composer autoloader
	require JFB_SIGNATURE_FIELD_PATH . 'vendor/autoload.php';

	// Include the main plugin file that initializes the plugin's classes and functionality.
	require JFB_SIGNATURE_FIELD_PATH . 'includes/plugin.php';
}
