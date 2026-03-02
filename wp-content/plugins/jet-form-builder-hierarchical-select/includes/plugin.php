<?php

namespace Jet_FB_HR_Select;

use Jet_FB_HR_Select\JetFormBuilder\Blocks\ManagerBlocks;
use Jet_FB_HR_Select\JetFormBuilder\AjaxHandler as JFBAjaxHandler;
use Jet_FB_HR_Select\JetFormBuilder\Blocks\ParserManager;
use JetHRSelectCore\LicenceProxy;

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

	public $slug = 'jet-form-builder-hierarchical-select';

	public function __construct() {
		ManagerBlocks::register();
		ParserManager::register();
		LicenceProxy::register();

		if ( wp_doing_ajax() ) {
			new JFBAjaxHandler();
		}
	}

	public function get_version() {
		return JET_FB_HR_SELECT_VERSION;
	}

	public function plugin_url( $path ) {
		return JET_FB_HR_SELECT_URL . $path;
	}

	public function plugin_dir( $path ) {
		return JET_FB_HR_SELECT_PATH . $path;
	}

	public function get_template_path( $template ) {
		return $this->plugin_dir( "templates/$template" );
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