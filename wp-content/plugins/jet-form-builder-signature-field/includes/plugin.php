<?php
namespace JFB_Signature_Field;

// Prevent direct access to the file.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use JFBSignatureFieldCore\LicenceProxy as License_Proxy;

/**
 * Main plugin class.
 *
 * This class initializes the plugin, including its components like the
 * columns controller and the shortcode handler.
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
	 * Register autoloader.
	 */
	private function register_autoloader() {
		require JFB_SIGNATURE_FIELD_PATH . 'includes/autoloader.php';
		Autoloader::run();
	}

	/**
	 * Constructor.
	 *
	 * The constructor is private to prevent creating multiple instances
	 * of the singleton.
	 */
	private function __construct() {
		$this->register_autoloader();
		add_action( 'init', [ $this, 'init' ], -1 );
		add_filter( 'jet-form-builder/parsers-request/register', [ $this, 'register_field_parser' ] );
	}

	/**
	 * Register field parser
	 *
	 * @param  array $parsers Registered parsers list.
	 * @return array
	 */
	public function register_field_parser( $parsers ) {
		$parsers[] = new Blocks\Signature_Field_Parser();
		return $parsers;
	}

	/**
	 * Check if SVG uploads are allowed
	 *
	 * @return boolean
	 */
	public function is_svg_upload_allowed() {
		$mime_types = apply_filters('upload_mimes', []);
		return isset($mime_types['svg']) && $mime_types['svg'] === 'image/svg+xml';
	}

	/**
	 * Instantiate plugin parts
	 */
	public function init() {

		if ( ! function_exists( 'jet_form_builder' ) ) {
			return;
		}

		Blocks\Manager::register();
		License_Proxy::register();
	}
}

// Initialize the plugin instance.
Plugin::instance();
