<?php
/**
 * Plugin Name: JetFormBuilder Hierarchical Select
 * Plugin URI:  https://jetformbuilder.com/addons/hierarchical-select/
 * Description: A form-enriching tweak that allows you to output multi-level Select field groups.
 * Version:     1.0.7
 * Author:      Crocoblock
 * Author URI:  https://crocoblock.com/
 * Text Domain: jet-form-builder-hr-select
 * License:     GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

define( 'JET_FB_HR_SELECT_VERSION', '1.0.7' );

define( 'JET_FB_HR_SELECT__FILE__', __FILE__ );
define( 'JET_FB_HR_SELECT_PLUGIN_BASE', plugin_basename( __FILE__ ) );
define( 'JET_FB_HR_SELECT_PATH', plugin_dir_path( __FILE__ ) );
define( 'JET_FB_HR_SELECT_URL', plugins_url( '/', __FILE__ ) );


if ( version_compare( PHP_VERSION, '7.0.0', '>=' ) ) {
	require JET_FB_HR_SELECT_PATH . 'vendor/autoload.php';

	add_action( 'plugins_loaded', function () {
		require JET_FB_HR_SELECT_PATH . 'includes/plugin.php';
	}, 100 );
} else {
	add_action( 'admin_notices', function () {
		$class   = 'notice notice-error';
		$message = __(
			'<b>Error:</b> <b>JetFormBuilder Hierarchical Select</b> plugin requires a PHP version ">= 7.0.0" to work properly!',
			'jet-form-builder-hr-select'
		);
		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), wp_kses_post( $message ) );
	} );

	add_action( 'admin_init', function () {
		deactivate_plugins( plugin_basename( __FILE__ ) );
	} );

}

