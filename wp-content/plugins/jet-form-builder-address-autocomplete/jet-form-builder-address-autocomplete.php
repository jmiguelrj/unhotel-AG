<?php
/**
 * Plugin Name: JetFormBuilder Address Autocomplete
 * Plugin URI:  https://jetformbuilder.com/addons/address-autocomplete/
 * Description: A dynamic addon that suggests up to 5 places in order to auto-fill the Address field.
 * Version:     1.0.10
 * Author:      Crocoblock
 * Author URI:  https://crocoblock.com/
 * Text Domain: jet-form-builder-address-autocomplete
 * License:     GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

define( 'JET_FB_ADDRESS_AUTOCOMPLETE_VERSION', '1.0.10' );

define( 'JET_FB_ADDRESS_AUTOCOMPLETE__FILE__', __FILE__ );
define( 'JET_FB_ADDRESS_AUTOCOMPLETE_PLUGIN_BASE', plugin_basename( __FILE__ ) );
define( 'JET_FB_ADDRESS_AUTOCOMPLETE_PATH', plugin_dir_path( __FILE__ ) );
define( 'JET_FB_ADDRESS_AUTOCOMPLETE_URL', plugins_url( '/', __FILE__ ) );

require JET_FB_ADDRESS_AUTOCOMPLETE_PATH . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

add_action( 'plugins_loaded', function () {

	if ( ! version_compare( PHP_VERSION, '7.0.0', '>=' ) ) {
		add_action( 'admin_notices', function () {
			$class   = 'notice notice-error';
			$message = __(
				'<b>Error:</b> <b>JetFormBuilder Address Autocomplete</b> plugin requires a PHP version ">= 7.0"',
				'jet-form-builder-address-autocomplete'
			);
			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), wp_kses_post( $message ) );
		} );

		return;
	}

	require JET_FB_ADDRESS_AUTOCOMPLETE_PATH . 'includes' . DIRECTORY_SEPARATOR . 'plugin.php';

}, 100 );

