<?php
namespace Jet_Engine_Layout_Switcher;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Main file
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
	 * Holder for frontend component
	 *
	 * @var Frontend
	 */
	public $frontend = null;

	/**
	 * Registered views list.
	 */
	private $views = array();

	/**
	 * Plugin constructor.
	 */
	private function __construct() {

		if ( ! function_exists( 'jet_engine' ) ) {
			return;
		}

		if ( ! version_compare( jet_engine()->get_version(), '3.2.2', '>=' ) ) {
			return;
		}

		$this->register_autoloader();

		add_action( 'init', array( $this, 'on_init' ), 12 );

		$this->on_load();

	}

	/**
	 * Instance.
	 *
	 * Ensures only one instance of the plugin class is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @return Plugin An instance of the class.
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
		require JET_ENGINE_LAYOUT_SWITCHER_PATH . 'includes/autoloader.php';
		Autoloader::run();
	}

	/**
	 * Initialize plugin parts
	 *
	 * @return void
	 */
	public function on_init() {

		$this->frontend = new Frontend();

		if ( did_action( 'elementor/init' )
			 && \Jet_Engine\Modules\Performance\Module::instance()->is_tweak_active( 'enable_elementor_views' )
		) {
			new Elementor\Manager();
		}

		if ( \Jet_Engine\Modules\Performance\Module::instance()->is_tweak_active( 'enable_blocks_views' ) ) {
			new Blocks\Manager();
		}

		if ( defined( 'BRICKS_VERSION' )
			 && \Jet_Engine\Modules\Performance\Module::instance()->is_tweak_active( 'enable_bricks_views' )
		) {
			new Bricks\Manager();
		}

		$pathinfo = pathinfo( JET_ENGINE_LAYOUT_SWITCHER_PLUGIN_BASE );

		jet_engine()->modules->updater->register_plugin( array(
			'slug'    => $pathinfo['filename'],
			'file'    => JET_ENGINE_LAYOUT_SWITCHER_PLUGIN_BASE,
			'version' => JET_ENGINE_LAYOUT_SWITCHER_VERSION
		) );

		do_action( 'jet-engine-layout-switcher/init', $this );
	}

	/**
	 * On load hooks
	 *
	 * @return void
	 */
	public function on_load() {
		add_action( 'jet-engine/listings/renderers/registered', array( $this, 'register_renderer' ) );
	}

	/**
	 * Register renderer class
	 *
	 * @param object $manager Listing manager instance
	 */
	public function register_renderer( $manager ) {
		$manager->register_render_class(
			'layout-switcher',
			array(
				'class_name' => '\Jet_Engine_Layout_Switcher\Render',
				'path'       => JET_ENGINE_LAYOUT_SWITCHER_PATH . 'includes/render.php',
			)
		);
	}

	/**
	 * Register view instance
	 *
	 * @param $view_class
	 */
	public function register_view( $view_class ) {
		$this->views[ $view_class->get_id() ] = $view_class;
	}

	/**
	 * Get view instance
	 *
	 * @param $view_id
	 * @return object
	 */
	public function get_view( $view_id ) {
		return isset( $this->views[ $view_id ] ) ? $this->views[ $view_id ] : null;
	}

}

Plugin::instance();
