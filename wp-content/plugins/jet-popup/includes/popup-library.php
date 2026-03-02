<?php
/**
 * Class description
 *
 * @package   package_name
 * @author    Cherry Team
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Popup_Library' ) ) {

	/**
	 * Define Jet_Popup_Library class
	 */
	class Jet_Popup_Library {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    object
		 */
		private static $instance = null;

		/**
		 * [$key description]
		 * @var string
		 */
		public $key = 'jet-popup-library';

		/**
		 * Init page
		 */
		public function __construct() {
			add_action( 'admin_menu', [ $this, 'add_popup_library_page' ], 90 );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_library_assets' ), 11 );
			add_action( 'admin_footer', [ $this, 'render_vue_template' ] );
			add_action( 'wp_ajax_jet_plugin_action', [ $this, 'jet_plugin_action' ] );
		}

		/**
		 * [get_settings_page description]
		 * @return [type] [description]
		 */
		public function get_library_page_url() {
			return admin_url( 'admin.php?&page=' . $this->key );
		}

		/**
		 * [add_popup_library_page description]
		 */
		public function add_popup_library_page() {

			add_submenu_page(
				'edit.php?post_type=' . jet_popup()->post_type->slug(),
				__( 'Preset Library', 'jet-popup' ),
				__( 'Preset Library', 'jet-popup' ),
				'edit_pages',
				$this->key,
				[ $this, 'library_page_render'],
				61
			);
		}

		/**
		 * [library_page_render description]
		 * @return [type] [description]
		 */
		public function library_page_render() {
			$crate_action = add_query_arg(
				array(
					'action' => 'jet_popup_create_from_preset',
				),
				esc_url( admin_url( 'admin.php' ) )
			);

			require jet_popup()->plugin_path( 'templates/vue-templates/admin/preset-page.php' );
		}

		/**
		 * [enqueue_admin_library_assets description]
		 * @return [type] [description]
		 */
		public function enqueue_admin_library_assets() {
			// phpcs:disable WordPress.Security.NonceVerification
			if ( isset( $_REQUEST['page'] ) && $this->key === $_REQUEST['page'] ) {

				$module_data = jet_popup()->module_loader->get_included_module_data( 'cherry-x-vue-ui.php' );
				$cx_vue_ui   = new CX_Vue_UI( $module_data );

				$cx_vue_ui->enqueue_assets();

				wp_enqueue_style(
					'jet-popup-admin',
					jet_popup()->plugin_url( 'assets/css/jet-popup-admin.css' ),
					[],
					jet_popup()->get_version()
				);

				wp_enqueue_script(
					'jet-popup-admin',
					jet_popup()->plugin_url( 'assets/js/jet-popup-admin.js' ),
					[
						'jquery',
						'jet-axios',
						'cx-vue-ui',
						'wp-api-fetch',
					],
					jet_popup()->get_version(),
					true
				);

				$localize_data = array(
					'version' => jet_popup()->get_version(),
					'ajaxUrl' => esc_url( admin_url( 'admin-ajax.php' ) ),
					'pluginActionsNonce' => wp_create_nonce( 'jet-popup-plugin-actions-nonce' ),
					'requiredPluginData' => $this->get_required_plugins_data(),
					'libraryPresetsUrl' => 'https://crocoblock.com/interactive-popups/wp-json/croco/v1/presets',
					'libraryPresetsCategoryUrl' => 'https://crocoblock.com/interactive-popups/wp-json/croco/v1/presets-categories',
					'pluginActionPath' => 'jet-popup/v2/plugin-action',
					//'libraryPresetsUrl'         => 'https://crocoblock-dev:8890//wp-json/croco/v1/presets',
					//'libraryPresetsCategoryUrl' => 'https://crocoblock-dev:8890//wp-json/croco/v1/presets-categories',
					'pluginActivated' => filter_var( Jet_Popup_Utils::get_plugin_license(), FILTER_VALIDATE_BOOLEAN ) ? 'true' : 'false',
					'createPopupLink' => add_query_arg(
						array( 'action' => 'jet_popup_create_from_library_preset' ),
						esc_url( admin_url( 'admin.php' ) )
					),
					'licenseActivationLink' => admin_url( 'admin.php?page=jet-dashboard-license-page&subpage=license-manager' ),
					'contentTypeIcons' => jet_popup()->post_type->get_popup_content_type_icons(),
					'contentTypeOptions' => jet_popup()->post_type->get_popup_content_type_options(),
				);

				$localize_data = apply_filters( 'jet-popup/admin/localized-data', $localize_data );

				wp_localize_script(
					'jet-popup-admin',
					'jetPopupData',
					$localize_data
				);
			}
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * @return array
		 */
		public function get_required_plugins_data() {
			$jet_elements_license = \Jet_Dashboard\Utils::get_plugin_license_key( 'jet-elements/jet-elements.php' );
			$jet_blocks_license = \Jet_Dashboard\Utils::get_plugin_license_key( 'jet-blocks/jet-blocks.php' );
			$jet_tricks_license = \Jet_Dashboard\Utils::get_plugin_license_key( 'jet-tricks/jet-tricks.php' );

			return apply_filters( 'jet-popup/admin/preset-library/required-plugins-data', [
				'jet-elements' => array(
					'name' => __( 'Jet Elements', 'jet-popup' ),
					'badge' => 'https://account.crocoblock.com/free-download/images/jetlogo/jetelements.svg',
					'link' => 'https://crocoblock.com/plugins/jetelements/',
					'status' => \Jet_Dashboard\Dashboard::get_instance()->plugin_manager->get_user_plugin( 'jet-elements/jet-elements.php' ),
					'plugin_file' => 'jet-elements/jet-elements.php',
					'source' => 'crocoblock',
					'zip_path' => false,
					'license' => $jet_elements_license ? true : false,
				),
				'jet-blocks' => array(
					'name' => __( 'Jet Blocks', 'jet-popup' ),
					'badge' => 'https://account.crocoblock.com/free-download/images/jetlogo/jetblocks.svg',
					'link' => 'https://crocoblock.com/plugins/jetblocks/',
					'status' => \Jet_Dashboard\Dashboard::get_instance()->plugin_manager->get_user_plugin( 'jet-blocks/jet-blocks.php' ),
					'plugin_file' => 'jet-blocks/jet-blocks.php',
					'source' => 'crocoblock',
					'zip_path' => false,
					'license' => $jet_blocks_license ? true : false,
				),
				'jet-tricks' => array(
					'name' => __( 'Jet Tricks', 'jet-popup' ),
					'badge' => 'https://account.crocoblock.com/free-download/images/jetlogo/jettricks.svg',
					'link' => 'https://crocoblock.com/plugins/jettricks/',
					'status' => \Jet_Dashboard\Dashboard::get_instance()->plugin_manager->get_user_plugin( 'jet-tricks/jet-tricks.php' ),
					'plugin_file' => 'jet-tricks/jet-tricks.php',
					'source' => 'crocoblock',
					'zip_path' => false,
					'license' => $jet_tricks_license ? true : false,
				),
				'cf7' => array(
					'name' => __( 'Contact Form 7', 'jet-popup' ),
					'badge' => jet_popup()->plugin_url( 'assets/image/cf7-badge.png' ),
					'link' => 'https://wordpress.org/plugins/contact-form-7/',
					'status' => \Jet_Dashboard\Dashboard::get_instance()->plugin_manager->get_user_plugin( 'contact-form-7/wp-contact-form-7.php' ),
					'plugin_file' => 'contact-form-7/wp-contact-form-7.php',
					'source' => 'org',
					'zip_path' => 'https://downloads.wordpress.org/plugin/contact-form-7.latest-stable.zip',
					'license' => true,
				),
				'jet-style-manager' => array(
					'name' => __( 'JetStyleManager', 'jet-popup' ),
					'badge' => 'https://account.crocoblock.com/free-download/images/jetlogo/jetstylemanager.svg',
					'link' => 'https://wordpress.org/plugins/jet-style-manager/',
					'status' => \Jet_Dashboard\Dashboard::get_instance()->plugin_manager->get_user_plugin( 'jet-style-manager/jet-style-manager.php' ),
					'plugin_file' => 'jet-style-manager/jet-style-manager.php',
					'source' => 'org',
					'zip_path' => 'https://downloads.wordpress.org/plugin/jet-style-manager.latest-stable.zip',
					'license' => true,
				),
			] );
		}

		/**
		 * [render_vue_template description]
		 * @return [type] [description]
		 */
		public function render_vue_template() {

			$vue_templates = [
				'preset-library',
				'preset-list',
				'preset-item',
				'required-plugin',
			];

			foreach ( glob( jet_popup()->plugin_path( 'templates/vue-templates/admin/' ) . '*.php' ) as $file ) {
				$path_info = pathinfo( $file );
				$template_name = $path_info['filename'];

				if ( in_array( $template_name, $vue_templates ) ) {?>
					<script type="text/x-template" id="<?php echo esc_html( $template_name ); ?>-template"><?php
						require $file; ?>
					</script><?php
				}
			}
		}

		/**
		 * [jet_popup_get_content description]
		 * @return [type] [description]
		 */
		public function jet_plugin_action() {

			$data = ( ! empty( $_POST['data'] ) ) ? $_POST['data'] : false; // phpcs:ignore
			$action = isset( $data['action'] ) ? $data['action'] : false;
			$plugin = isset( $data['plugin'] ) ? $data['plugin'] : false;

			if ( ! isset( $data['nonce'] ) || ! wp_verify_nonce( $data['nonce'], 'jet-popup-plugin-actions-nonce' ) ) {
				wp_send_json( [
					'status'  => 'error',
					'message' => __( 'Page has expired. Please reload this page.', 'jet-popup' ),
				] );
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json( [
					'status'  => 'error',
					'message' => __( 'You not allowed to do this.', 'jet-popup' ),
				] );
			}

			if ( ! $plugin ) {
				wp_send_json(
					array(
						'status'  => 'error',
						'message' => 'Plugin slug is required'
					)
				);
			}

			$required_plugins_data = jet_popup()->popup_library->get_required_plugins_data();

			if ( ! isset( $required_plugins_data[ $plugin ] ) ) {
				wp_send_json(
					array(
						'status'  => 'error',
						'message' => 'Plugin data not found',
					)
				);
			}

			$required_plugin_data = $required_plugins_data[ $plugin ];

			switch ( $action ) {
				case 'install':

					switch ( $required_plugin_data['source'] ) {
						case 'org':

							if ( ! $required_plugin_data['zip_path'] ) {
								wp_send_json(
									array(
										'status'  => 'error',
										'message' => 'Plugin package not found',
									)
								);
							}

							\Jet_Dashboard\Dashboard::get_instance()->plugin_manager->install_plugin( $required_plugin_data['plugin_file'], $required_plugin_data['zip_path'] );

							break;
						case 'crocoblock':
							\Jet_Dashboard\Dashboard::get_instance()->plugin_manager->install_plugin( $required_plugin_data['plugin_file'] );
							break;
					}

					break;
				case 'activate':
					\Jet_Dashboard\Dashboard::get_instance()->plugin_manager->activate_plugin( $required_plugin_data['plugin_file'] );

					break;
			}
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @access public
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
