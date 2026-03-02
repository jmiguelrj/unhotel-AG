<?php
/**
 * Plugin Name: JetFormBuilder Drag and Drop File Upload
 * Plugin URI:  https://jetformbuilder.com/addons/drag-and-drop-file-upload/
 * Description: Adds drag and drop file upload capabilities for JetFormBuilder. This plugin enhances file management and provides additional file-related features.
 * Version:     1.0.4
 * Author:      Crocoblock
 * Author URI:  https://crocoblock.com/
 * Text Domain: jet-form-builder-advanced-media
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
add_action( 'plugins_loaded', 'jfb_drag_and_drop_file_upload_init', 99 );

/**
 * Initializes the JetFormBuilder Advanced Media plugin.
 * This function defines several constants for use throughout the plugin and requires the main plugin class file.
 */
function jfb_drag_and_drop_file_upload_init() {

	// Define the current version of the plugin.
	define( 'JFB_ADVANCED_MEDIA_VERSION', '1.0.4' );

	// Define a constant for the plugin file path.
	define( 'JFB_ADVANCED_MEDIA__FILE__', __FILE__ );

	// Define a base for the plugin, used as plugin slug where needed.
	define( 'JFB_ADVANCED_MEDIA_PLUGIN_BASE', plugin_basename( JFB_ADVANCED_MEDIA__FILE__ ) );

	// Define the absolute path to the plugin's directory.
	define( 'JFB_ADVANCED_MEDIA_PATH', plugin_dir_path( JFB_ADVANCED_MEDIA__FILE__ ) );

	// Define the URL to the plugin's directory, useful for loading assets like JavaScript and CSS files.
	define( 'JFB_ADVANCED_MEDIA_URL', plugins_url( '/', JFB_ADVANCED_MEDIA__FILE__ ) );

	// Check JetFormBuilder version compatibility
	if ( ! jfb_check_jetformbuilder_version() ) {
		return;
	}

	// Composer autoloader
	require JFB_ADVANCED_MEDIA_PATH . 'vendor/autoload.php';

	// Include the main plugin file that initializes the plugin's classes and functionality.
	require JFB_ADVANCED_MEDIA_PATH . 'includes/plugin.php';
}

/**
 * Check if JetFormBuilder version is compatible.
 *
 * @return bool True if compatible, false otherwise.
 */
function jfb_check_jetformbuilder_version(): bool {
	// Check if JetFormBuilder is active
	if ( ! class_exists( 'Jet_Form_Builder\Plugin' ) ) {
		add_action( 'admin_notices', 'jfb_jetformbuilder_not_installed_notice' );
		return false;
	}

	// Get JetFormBuilder version
	$jfb_version      = defined( 'JET_FORM_BUILDER_VERSION' ) ? JET_FORM_BUILDER_VERSION : '0.0.0';
	$required_version = '3.5.2.1';

	// Compare versions
	if ( version_compare( $jfb_version, $required_version, '<' ) ) {
		add_action( 'admin_notices', 'jfb_jetformbuilder_version_notice' );
		return false;
	}

	return true;
}

/**
 * Display admin notice when JetFormBuilder is not installed.
 */
function jfb_jetformbuilder_not_installed_notice(): void {
	$message = sprintf(
		'<div class="notice notice-error"><p><strong>%s</strong> %s</p></div>',
		esc_html__( 'JetFormBuilder Drag and Drop File Upload', 'jet-form-builder-advanced-media' ),
		esc_html__( 'requires JetFormBuilder plugin to be installed and activated.', 'jet-form-builder-advanced-media' )
	);
	echo wp_kses_post( $message );
}

/**
 * Display admin notice when JetFormBuilder version is incompatible.
 */
function jfb_jetformbuilder_version_notice(): void {
	$jfb_version      = defined( 'JET_FORM_BUILDER_VERSION' ) ? JET_FORM_BUILDER_VERSION : '0.0.0';
	$required_version = '3.5.2.1';

	$message = sprintf(
		'<div class="notice notice-error"><p><strong>%s</strong> %s</p></div>',
		esc_html__( 'JetFormBuilder Drag and Drop File Upload', 'jet-form-builder-advanced-media' ),
		sprintf(
			// translators: %1$s is the required version, %2$s is the current version
			esc_html__( 'requires JetFormBuilder version %1$s or higher. Current version: %2$s', 'jet-form-builder-advanced-media' ),
			esc_html( $required_version ),
			esc_html( $jfb_version )
		)
	);
	echo wp_kses_post( $message );
}
