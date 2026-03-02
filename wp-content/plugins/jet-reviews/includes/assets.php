<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Reviews_Assets' ) ) {

	/**
	 * Define Jet_Reviews_Assets class
	 */
	class Jet_Reviews_Assets {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Constructor for the class
		 */
		public function init() {

			add_action( 'wp_enqueue_scripts', array( $this, 'register_styles' ), 5 );

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );

			add_action( 'elementor/editor/after_enqueue_styles',   array( $this, 'editor_styles' ) );

			add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ), 5 );

            if ( ! jet_reviews_tools()->has_elementor() ) {
	            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
            } else {
	            add_action( 'elementor/frontend/before_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
            }

			add_action( 'wp_footer', array( $this, 'render_vue_template' ) );
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
		 * @return void
		 */
        public function register_styles() {
	        wp_register_style(
		        'photoswipe',
		        jet_reviews()->plugin_url( 'assets/lib/photoswipe/css/photoswipe.min.css' ),
		        [],
		        jet_reviews()->get_version()
	        );

	        wp_register_style(
		        'photoswipe-default-skin',
		        jet_reviews()->plugin_url( 'assets/lib/photoswipe/css/default-skin/default-skin.min.css' ),
		        [ 'photoswipe' ],
		        jet_reviews()->get_version()
	        );
        }

		/**
		 * Enqueue public-facing stylesheets.
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function enqueue_styles() {
			$frontend_deps_styles = apply_filters( 'jet-reviews/frontend/deps-styles', [ 'photoswipe' ] );

			wp_enqueue_style(
				'jet-reviews-frontend',
				jet_reviews()->plugin_url( 'assets/css/jet-reviews.css' ),
				$frontend_deps_styles,
				jet_reviews()->get_version()
			);
		}

			/**
		 * Enqueue editor styles
		 *
		 * @return void
		 */
		public function editor_styles() {
			wp_enqueue_style(
				'jet-reviews-icons',
				jet_reviews()->plugin_url( 'assets/lib/jetreviews-icons/icons.css' ),
				array(),
				jet_reviews()->get_version() . '-icons'
			);

			wp_enqueue_style(
				'jet-reviews-editor',
				jet_reviews()->plugin_url( 'assets/css/jet-reviews-editor.css' ),
				array(),
				jet_reviews()->get_version()
			);
		}

		/**
		 * [register_scripts description]
		 * @return [type] [description]
		 */
		public function register_scripts() {
			do_action( 'jet-reviews/frontend/before_register_scripts' );

			wp_register_script(
				'jet-vue',
				jet_reviews()->plugin_url( 'assets/js/lib/vue' . $this->suffix() . '.js' ),
				[],
				'2.6.11',
				true
			);

			wp_register_script(
				'photoswipe',
				jet_reviews()->plugin_url( 'assets/lib/photoswipe/js/photoswipe' . $this->suffix() . '.js' ),
				[],
				jet_reviews()->get_version(),
				true
			);

			wp_register_script(
				'photoswipe-ui-default',
				jet_reviews()->plugin_url( 'assets/lib/photoswipe/js/photoswipe-ui-default' . $this->suffix() . '.js' ),
				[ 'photoswipe' ],
				jet_reviews()->get_version(),
				true
			);

			do_action( 'jet-reviews/frontend/after_register_scripts' );

		}

		/**
		 * Enqueue plugin scripts only with elementor scripts
		 *
		 * @return void
		 */
		public function enqueue_scripts() {
			$frontend_deps_scripts = apply_filters( 'jet-reviews/frontend/deps-scripts',
				array( 'jquery', 'wp-api-fetch', 'jet-vue', 'photoswipe' )
			);

			wp_enqueue_script(
				'jet-reviews-frontend',
				jet_reviews()->plugin_url( 'assets/js/jet-reviews-frontend.js' ),
				$frontend_deps_scripts,
				jet_reviews()->get_version(),
				true
			);

			wp_localize_script(
				'jet-reviews-frontend',
				'jetReviewPublicConfig',
				$this->get_front_localize_data()
			);

		}

		/**
		 * @return mixed|void
		 */
        public function get_front_localize_data() {
	        global $wp;

            return apply_filters( 'jet-reviews/public/localized-data', array(
	            'version'                  => jet_reviews()->get_version(),
	            'ajax_url'                 => esc_url( admin_url( 'admin-ajax.php' ) ),
	            'current_url'              => esc_url( home_url( add_query_arg( [], $wp->request ) ) ),
	            'getPublicReviewsRoute'    => '/jet-reviews-api/v1/get-public-reviews-list',
	            'submitReviewCommentRoute' => '/jet-reviews-api/v1/submit-review-comment',
	            'submitReviewRoute'        => '/jet-reviews-api/v1/submit-review',
	            'likeReviewRoute'          => '/jet-reviews-api/v1/update-review-approval',
	            'labels'                   => array(
		            'alreadyReviewed'           => __( '*Already reviewed', 'jet-reviews' ),
		            'notApprove'                => __( '*Your review must be approved by the moderator', 'jet-reviews' ),
		            'notValidField'             => __( '*This field is required or not valid', 'jet-reviews' ),
		            'captchaValidationFailed'   => __( '*Captcha validation failed', 'jet-reviews' ),
	            )
            ) );
        }

		/**
		 * [render_vue_template description]
		 * @return [type] [description]
		 */
		public function render_vue_template() {

			$vue_templates = array(
				'jet-advanced-reviews-item',
				'jet-advanced-reviews-comment',
				'jet-advanced-reviews-point-field',
				'jet-advanced-reviews-star-field',
				'jet-advanced-reviews-form',
				'jet-advanced-reviews-slider-input',
				'jet-advanced-reviews-stars-input',
				'jet-reviews-widget-pagination',
				'jet-reviews-widget-file-input',
			);

			// Path to the folder with templates in the theme
			$theme_template_path = get_stylesheet_directory() . '/jet-reviews/templates/';

			foreach ( $vue_templates as $template_name ) {
				// First, try to find the template in the theme
				$theme_template_file = $theme_template_path . $template_name . '.php';

				if ( file_exists( $theme_template_file ) ) {
					?>
					<script type="text/x-template" id="<?php echo $template_name; ?>-template"><?php
						require $theme_template_file;
						?>
					</script>
					<?php
				} else {
					// If the template is not found in the theme, fallback to the plugin's template
					$plugin_template_file = jet_reviews()->plugin_path() . 'templates/public/vue-templates/' . $template_name . '.php';

					if ( file_exists( $plugin_template_file ) ) {
						?>
						<script type="text/x-template" id="<?php echo $template_name; ?>-template"><?php
							require $plugin_template_file;
							?>
						</script>
						<?php
					}
				}
			}
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
 * Returns instance of Jet_Reviews_Assets
 *
 * @return object
 */
function jet_reviews_assets() {
	return Jet_Reviews_Assets::get_instance();
}
