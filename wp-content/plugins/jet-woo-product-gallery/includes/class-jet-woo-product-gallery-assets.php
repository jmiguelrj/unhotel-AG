<?php
/**
 * Product Gallery assets class
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Woo_Product_Gallery_Assets' ) ) {

	/**
	 * Define Jet_Woo_Product_Gallery_Assets class
	 */
	class Jet_Woo_Product_Gallery_Assets {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		private $did_scripts = false;

		/**
		 * Initial class function.
		 */
		public function init() {

			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_missed_assets' ], 21);
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ] );

			add_action( 'jet-engine/listing/grid/assets', function( $settings ) {
				if ( isset( $settings['lazy_load'] ) && filter_var( $settings['lazy_load'], FILTER_VALIDATE_BOOLEAN ) ) {
					$elementor_data = get_post_meta($settings['lisitng_id'], '_elementor_data', true);

					if ( false !== strpos( $elementor_data, 'jet-woo-product-gallery-slider' ) ) {
						$this->enqueue_scripts();
					}
				}
			} );

		}

		/**
		 * Enqueue styles.
		 *
		 * Enqueue public-facing stylesheets.
		 *
		 * @since 1.0.0
		 * @since 2.1.19 Updated style name and enqueue.
		 *
		 * @return void
		 */
		public function enqueue_styles() {

			wp_register_style(
				'jet-gallery-frontend',
				jet_woo_product_gallery()->plugin_url( 'assets/css/frontend.css' ),
				$this->get_gallery_style_frontend_dependencies(),
				jet_woo_product_gallery()->get_version()
			);

			$widgets_with_styles = jet_woo_product_gallery()->elementor_views->widgets_with_styles();

			foreach ( $widgets_with_styles as $widget_name ) {
				wp_register_style(
					"jet-gallery-widget-{$widget_name}",
					jet_woo_product_gallery()->plugin_url( "assets/css/widgets/{$widget_name}.css" ),
					[ 'jet-gallery-frontend' ],
					jet_woo_product_gallery()->get_version()
				);
			}

		}

		/**
		 * Enqueue public-facing scripts.
		 *
		 * @return void
		 */
		public function enqueue_scripts() {

			if ( $this->did_scripts ) {
				return;
			}

			$this->did_scripts = true;

			wp_enqueue_script(
				'jet-woo-product-gallery',
				jet_woo_product_gallery()->plugin_url( 'assets/js/jet-woo-product-gallery' . $this->suffix() . '.js' ),
				$this->get_gallery_script_frontend_dependencies(),
				jet_woo_product_gallery()->get_version(),
				true
			);

			wp_localize_script(
				'jet-woo-product-gallery',
				'jetWooProductGalleryData',
				apply_filters( 'jet-woo-product-gallery/frontend/localize-data', [
					'product_types' => jet_woo_product_gallery_tools()->get_compatible_product_types(),
					'assets_path'=> jet_woo_product_gallery()->plugin_url('assets'),
				] )
			);

		}

		/**
		 * Return frontend styles dependencies.
		 *
		 * @return array
		 */
		public function get_gallery_style_frontend_dependencies() {

			$dependencies = [];

			if ( is_admin() ) {
				return $dependencies;
			}

			if ( ! wp_style_is( 'mediaelement' ) ) {
				$dependencies[] = 'mediaelement';
			}

			if ( ! wp_style_is( 'photoswipe' ) ) {
				$dependencies[] = 'photoswipe';
			}

			if ( ! wp_style_is( 'photoswipe-default-skin' ) ) {
				$dependencies[] = 'photoswipe-default-skin';
			}

			return $dependencies;

		}

		/**
		 * Get gallery script frontend dependencies.
		 *
		 * Return frontend script dependencies.
		 *
		 * @since  2.1.0
		 * @since  2.1.10 Added `jet-plugin` script enqueue.
		 * @access public
		 *
		 * @return array
		 */
		public function get_gallery_script_frontend_dependencies() {

			$dependencies = [ 'jquery', 'wp-hooks' ];

			if ( is_admin() ) {
				return $dependencies;
			}

			if ( ! wp_script_is( 'mediaelement' ) ) {
				$dependencies[] = 'mediaelement';
			}

			if ( ! wp_script_is( 'zoom' ) ) {
				$dependencies[] = 'zoom';
			}

			if ( ! wp_script_is( 'photoswipe' ) ) {
				$dependencies[] = 'photoswipe';
			}

			if ( ! wp_script_is( 'photoswipe-ui-default' ) ) {
				$dependencies[] = 'photoswipe-ui-default';
			}

			if ( ! wp_script_is( 'jet-plugins' ) ) {
				$dependencies[] = 'jet-plugins';
			}

			return $dependencies;

		}

		/**
		 * Enqueue missed assets.
		 *
		 * Enqueue needed scripts and styles when they're missing.
		 *
		 * @since  2.1.0
		 * @since  2.1.10 Added `jet-plugin` script registration.
		 * @access public
		 *
		 * @return void
		 */
		public function enqueue_missed_assets() {


			wp_enqueue_script(
				'jet-swiper',
				jet_woo_product_gallery()->plugin_url( 'assets/lib/swiper/swiper' . $this->suffix() . '.js' ),
				[],
				'5.3.6',
				! wp_script_is( 'swiper', 'enqueued' )
			);

			wp_add_inline_script(
				'jet-swiper',
				'const JetSwiper = window.Swiper; window.Swiper = undefined;',
			);

			if ( ! class_exists( 'WooCommerce' ) ) {
				wp_register_script(
					'zoom',
					jet_woo_product_gallery()->plugin_url( 'assets/lib/zoom/jquery.zoom' . $this->suffix() . '.js' ),
					[ 'jquery' ],
					jet_woo_product_gallery()->get_version(),
					true
				);

				wp_register_script(
					'photoswipe',
					jet_woo_product_gallery()->plugin_url( 'assets/lib/photoswipe/js/photoswipe' . $this->suffix() . '.js' ),
					[],
					jet_woo_product_gallery()->get_version(),
					true
				);

				wp_register_script(
					'photoswipe-ui-default',
					jet_woo_product_gallery()->plugin_url( 'assets/lib/photoswipe/js/photoswipe-ui-default' . $this->suffix() . '.js' ),
					[ 'photoswipe' ],
					jet_woo_product_gallery()->get_version(),
					true
				);

				wp_register_style(
					'photoswipe',
					jet_woo_product_gallery()->plugin_url( 'assets/lib/photoswipe/css/photoswipe.min.css' ),
					[],
					jet_woo_product_gallery()->get_version()
				);

				wp_register_style(
					'photoswipe-default-skin',
					jet_woo_product_gallery()->plugin_url( 'assets/lib/photoswipe/css/default-skin/default-skin.min.css' ),
					[ 'photoswipe' ],
					jet_woo_product_gallery()->get_version()
				);
			}

			wp_register_script(
				'jet-plugins',
				jet_woo_product_gallery()->plugin_url( 'assets/lib/jet-plugins/jet-plugins.js' ),
				[ 'jquery' ],
				'1.0.0',
				true
			);

		}

		/**
		 * Add suffix to scripts
		 *
		 * @return string
		 */
		public function suffix() {
			return defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @return object
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;

		}

	}

}

/**
 * Returns instance of Jet_Woo_Product_Gallery_Assets
 *
 * @return object
 */
function jet_woo_product_gallery_assets() {
	return Jet_Woo_Product_Gallery_Assets::get_instance();
}
