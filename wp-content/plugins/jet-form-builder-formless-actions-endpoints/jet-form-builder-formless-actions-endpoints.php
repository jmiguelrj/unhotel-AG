<?php
/**
 * Plugin Name:         JetFormBuilder Formless Actions Endpoints
 * Plugin URI:          https://jetformbuilder.com/
 * Description:         A tweak to execute specific after-submit actions without front-end forms.
 * Version:             1.0.4
 * Author:              Crocoblock
 * Author URI:          https://crocoblock.com/
 * Text Domain:         jet-form-builder-formless-actions-endpoints
 * License:             GPL-3.0+
 * License URI:         http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path:         /languages
 * Requires PHP:        7.0
 * Requires at least:   6.5
 * Requires Plugins:    jetformbuilder
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

const JFB_FORMLESS_VERSION = '1.0.4';
const JFB_FORMLESS__FILE__ = __FILE__;

define( 'JFB_FORMLESS_PLUGIN_BASE', plugin_basename( JFB_FORMLESS__FILE__ ) );
define( 'JFB_FORMLESS_PATH', plugin_dir_path( JFB_FORMLESS__FILE__ ) );
define( 'JFB_FORMLESS_URL', plugins_url( '/', JFB_FORMLESS__FILE__ ) );

require __DIR__ . '/includes/load.php';
