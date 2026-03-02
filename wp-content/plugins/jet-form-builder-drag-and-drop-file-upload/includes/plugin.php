<?php
namespace JFB_Advanced_Media;

// Prevent direct access to the file.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use JFBAdvancedMediaCore\LicenceProxy as License_Proxy;
use JFB_Advanced_Media\Upload_Dir_Adapter;
use JFB_Advanced_Media\Ajax_Handler;
use JFB_Advanced_Media\Media_Library_Filter;
use JFB_Advanced_Media\Email_Integration;
use JFB_Advanced_Media\Form_Records_Handler;
use JFB_Advanced_Media\Image_Settings_Handler;

/**
 * Main plugin class.
 *
 * This class initializes the plugin and its components including
 * the media manager, AJAX handlers, and integrations.
 *
 * @since 1.0.0
 */
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

	/**
	 * AJAX handler instance.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var Ajax_Handler
	 */
	private $ajax_handler;

	/**
	 * Media library filter instance.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var Media_Library_Filter
	 */
	private $media_filter;

	/**
	 * Email integration instance.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var Email_Integration
	 */
	private $email_integration;

	/**
	 * Form records handler instance.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var Form_Records_Handler
	 */
	private $form_records_handler;

	/**
	 * Image settings handler instance.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var Image_Settings_Handler
	 */
	private $image_settings_handler;

	/**
	 * Register autoloader.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function register_autoloader() {
		require_once JFB_ADVANCED_MEDIA_PATH . 'includes/autoloader.php';
		Autoloader::run();
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function __construct() {
		$this->register_autoloader();

		// Initialize component instances
		$this->ajax_handler           = new Ajax_Handler();
		$this->media_filter           = new Media_Library_Filter();
		$this->image_settings_handler = new Image_Settings_Handler();

		// Initialize styles manager early to ensure loaded will work correctly
		new Styles\Manager();

		add_action( 'init', array( $this, 'init' ), 0 );
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
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialize the plugin.
	 *
	 * Loads the plugin components and registers necessary hooks.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function init() {
		// Check if JetFormBuilder is available
		if ( ! function_exists( 'jet_form_builder' ) ) {
			return;
		}

		// Register blocks and license
		Blocks\Manager::register();
		License_Proxy::register();

		// Load additional integrations
		$this->load_integrations();

		// Initialize components
		$this->init_components();
	}

	/**
	 * Register validation messages for Advanced Media field.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function register_validation_messages() {
		// Register validation messages using the filter approach
		add_filter(
			'jet-form-builder/validation-messages',
			array( $this, 'add_validation_messages' )
		);
	}

	/**
	 * Load additional integrations.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function load_integrations() {
		// Load email attachments integration if exists
		if ( file_exists( JFB_ADVANCED_MEDIA_PATH . 'includes/email-attachments.php' ) ) {
			require_once JFB_ADVANCED_MEDIA_PATH . 'includes/email-attachments.php';
		}
	}

	/**
	 * Initialize plugin components.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function init_components() {
		// Initialize AJAX handler
		$this->ajax_handler->init();

		// Initialize media library filter
		$this->media_filter->init();
	}
}

// Initialize the plugin instance.
Plugin::instance();
