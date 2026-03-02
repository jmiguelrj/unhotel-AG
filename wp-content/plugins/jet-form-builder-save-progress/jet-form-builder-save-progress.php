<?php
/**
 * Plugin Name: JetFormBuilder Save Form Progress
 * Plugin URI:  https://jetformbuilder.com/addons/save-form-progress/
 * Description: A tweak to autosave the form progress and inputted data if the filling process was interrupted.
 * Version:     1.0.11
 * Author:      Crocoblock
 * Author URI:  https://crocoblock.com/
 * Text Domain: jet-form-builder-save-progress
 * License:     GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

define( 'JET_FB_SAVE_PROGRESS_VERSION', '1.0.11' );
define( 'JET_FB_SAVE_PROGRESS__FILE__', __FILE__ );
define( 'JET_FB_SAVE_PROGRESS_PLUGIN_BASE', plugin_basename( __FILE__ ) );
define( 'JET_FB_SAVE_PROGRESS_PATH', plugin_dir_path( __FILE__ ) );
define( 'JET_FB_SAVE_PROGRESS_URL', plugins_url( '/', __FILE__ ) );

require JET_FB_SAVE_PROGRESS_PATH . 'vendor/autoload.php';

add_action( 'plugins_loaded', function () {

	if ( ! version_compare( PHP_VERSION, '7.0.0', '>=' ) ) {
		add_action( 'admin_notices', function () {
			$class   = 'notice notice-error';
			$message = __(
				'<b>Error:</b> <b>JetFormBuilder Save Form Progress</b> plugin requires a PHP version ">= 7.0"',
				'jet-form-builder-save-progress'
			);
			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), wp_kses_post( $message ) );
		} );

		return;
	}

	require JET_FB_SAVE_PROGRESS_PATH . 'includes/plugin.php';

}, 100 );

