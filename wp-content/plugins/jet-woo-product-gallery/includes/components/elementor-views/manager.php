<?php
/**
 * JetGallery Elementor views manager.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Gallery_Elementor_Views' ) ) {

	/**
	 * Define Jet_Gallery_Elementor_Views class.
	 */
	class Jet_Gallery_Elementor_Views {

		// Check if processing elementor widget.
		private $is_elementor_ajax = false;

		function __construct() {

			add_action( 'elementor/elements/categories_registered', [ $this, 'register_category' ] );

			if ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, '3.5.0', '>=' ) ) {
				add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ], 10 );
			} else {
				add_action( 'elementor/widgets/widgets_registered', [ $this, 'register_widgets' ], 10 );
			}

			add_action( 'wp_ajax_elementor_render_widget', [ $this, 'set_elementor_ajax' ], 10, -1 );
			add_action( 'elementor/preview/enqueue_scripts', [ $this, 'preview_scripts' ] );
			add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'editor_styles' ] );
			add_action( 'elementor/preview/enqueue_styles', [ $this, 'editor_styles' ] );

			// Disable page transition on image link click.
			add_filter( 'jet-gallery/render/image-link-attrs', function ( $attrs ) {
				$attrs['data-e-disable-page-transition'] = '';
				return $attrs;
			} );

		}

		/**
		 * Enqueue preview scripts.
		 */
		public function preview_scripts() {
			jet_woo_product_gallery_assets()->enqueue_scripts();
		}

		/**
		 * Enqueue editor styles.
		 */
		public function editor_styles() {
			wp_enqueue_style(
				'jet-gallery-icons',
				jet_woo_product_gallery()->plugin_url( 'assets/lib/jet-gallery-icons/icons.css' ),
				[],
				jet_woo_product_gallery()->get_version()
			);
		}

		/**
		 * Set $this->is_elementor_ajax to true on Elementor AJAX processing.
		 *
		 * @return  void
		 */
		public function set_elementor_ajax() {
			$this->is_elementor_ajax = true;
		}

		/**
		 * Check if we currently in Elementor mode.
		 *
		 * @return void
		 */
		public function in_elementor() {

			$result = false;

			if ( wp_doing_ajax() ) {
				$result = $this->is_elementor_ajax;
			} elseif ( Elementor\Plugin::instance()->editor->is_edit_mode() || Elementor\Plugin::instance()->preview->is_preview_mode() ) {
				$result = true;
			}

			return apply_filters( 'jet-woo-product-gallery/in-elementor', $result );

		}

		/**
		 * Register widgets.
		 *
		 * Register plugin Elementor widgets.
		 *
		 * @since  1.0.0
		 * @since  2.1.9 Updated widgets path.
		 * @access public
		 *
		 * @param object $widgets_manager Elementor widgets manager instance.
		 *
		 * @return void
		 */
		public function register_widgets( $widgets_manager ) {

			$gallery_available_widgets = jet_woo_product_gallery_settings()->get( 'product_gallery_available_widgets' );

			require $this->component_path( 'widget-base.php' );

			foreach ( glob( $this->component_path( 'widgets/' ) . '*.php' ) as $file ) {
				$slug    = basename( $file, '.php' );
				$enabled = $gallery_available_widgets[ $slug ] ?? '';

				if ( filter_var( $enabled, FILTER_VALIDATE_BOOLEAN ) || ! $gallery_available_widgets ) {
					$this->register_widget( $file, $widgets_manager );
				}
			}

		}

		/**
		 * Register addon by file name.
		 *
		 * @param string $file            File name.
		 * @param object $widgets_manager Widgets manager instance.
		 *
		 * @return void
		 */
		public function register_widget( $file, $widgets_manager ) {

			$base  = basename( str_replace( '.php', '', $file ) );
			$class = ucwords( str_replace( '-', ' ', $base ) );
			$class = str_replace( ' ', '_', $class );
			$class = sprintf( 'Elementor\%s', $class );

			require $file;

			if ( class_exists( $class ) ) {
				if ( method_exists( $widgets_manager, 'register' ) ) {
					$widgets_manager->register( new $class );
				} else {
					$widgets_manager->register_widget_type( new $class );
				}
			}

		}

		/**
		 * Register category for elementor if not exists.
		 *
		 * @return void
		 */
		public function register_category() {
			Elementor\Plugin::instance()->elements_manager->add_category(
				'jet-woo-product-gallery',
				[
					'title' => __( 'JetProductGallery', 'jet-woo-product-gallery' ),
					'icon'  => 'font',
				]
			);
		}

		/**
		 * Widgets with styles.
		 *
		 * This method returns the list of all the widgets that have styles.
		 *
		 * @since 2.1.19
		 *
		 * @return array The names of the widgets that have styles.
		 */
		public function widgets_with_styles(): array {
			return [
				'gallery-anchor-nav',
				'gallery-grid',
				'gallery-modern',
				'gallery-slider',
			];
		}

		/**
		 * Component path.
		 *
		 * Return path to file inside component.
		 *
		 * @since  2.1.9
		 * @access public
		 *
		 * @param string $path Path name.
		 *
		 * @return string
		 */
		public function component_path( $path ) {
			return jet_woo_product_gallery()->plugin_path( 'includes/components/elementor-views/' . $path );
		}

	}

}