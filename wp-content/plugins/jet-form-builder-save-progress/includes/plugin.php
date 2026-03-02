<?php

namespace Jet_FB_Save_Progress;


use Jet_FB_Save_Progress\JetEngine\MetaBox;
use Jet_FB_Save_Progress\JetFormBuilder\SavePluginManager;
use JetSaveProgressCore\LicenceProxy;

if ( ! defined( 'WPINC' ) ) {
	die();
}

class Plugin {
	/**
	 * Instance.
	 *
	 * Holds the plugin instance.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @var Plugin
	 */
	public static $instance = null;

	public $slug = 'jet-form-builder-save-progress';

	public function __construct() {
		SaveProgress::instance()->enqueue_after_end();
		SaveProgress::instance()->after_submit_form();

		SavePluginManager::register();
		MetaBox::register();

		LicenceProxy::register();
	}

	public function get_version() {
		return JET_FB_SAVE_PROGRESS_VERSION;
	}

	public function plugin_url( $path ) {
		return JET_FB_SAVE_PROGRESS_URL . $path;
	}

	public function plugin_dir( $path = '' ) {
		return JET_FB_SAVE_PROGRESS_PATH . $path;
	}

	public function get_template_path( $template ) {
		$path = JET_FB_SAVE_PROGRESS_PATH . 'templates' . DIRECTORY_SEPARATOR;

		return ( $path . $template );
	}


	/**
	 * Instance.
	 *
	 * Ensures only one instance of the plugin class is loaded or can be loaded.
	 *
	 * @return Plugin An instance of the class.
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}

Plugin::instance();