<?php
namespace Jet_Popup;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Elementor {

	/**
	 * A reference to an instance of this class.
	 *
	 * @since 1.0.0
	 * @var   object
	 */
	private static $instance = null;

	/**
	 * editor localized data
	 *
	 * @var array
	 */
	public $editor_localize_data = [];

	/**
	 * [$extensions description]
	 * @var null
	 */
	public $extensions = null;

	/**
	 * @var null
	 */
	public $finder = null;

	/**
	 * Initalize integration hooks
	 *
	 * @return void
	 */
	public function __construct() {

		add_filter( 'jet-popup/post-type/content-type-icons', array( $this, 'modify_content_type_icons' ), 10, 2 );

		if ( ! \Jet_Popup_Utils::has_elementor() ) {
			return;
		}

		$this->load_files();

		add_action( 'init', [ $this, 'init' ], -998 );
		add_action( 'elementor/init', [ $this, 'init_extension_module' ], 0 );
		add_action( 'elementor/init', [ $this, 'register_category' ] );
		add_action( 'elementor/widgets/register', [ $this, 'register_addons' ], 10 );
		add_action( 'elementor/controls/controls_registered', [ $this, 'add_controls' ], 10 );
		add_action( 'elementor/documents/register', [ $this, 'register_document_type' ] );
		add_action( 'elementor/finder/register', [ $this, 'add_jet_popup_finder_category' ] );
		add_action( 'elementor/editor/before_enqueue_scripts', array( $this, 'editor_scripts' ) );
		add_action( 'elementor/preview/enqueue_styles', array( $this, 'preview_styles' ) );
		add_action( 'elementor/frontend/widget/before_render', array( $this, 'before_element_render' ), 11 );
		add_filter( 'elementor/documents/ajax_save/return_data', [ $this, 'document_ajax_save' ], 10, 3 );
		add_action( 'elementor/frontend/before_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_action( 'wp_insert_post', [ $this, 'set_document_type_on_post_create' ], 10, 2 );
		add_action( 'jet_plugins/frontend/register_scripts', array( $this, 'frontend_register_scripts' ) );
		add_action( 'jet-popup/render-manager/define-popups/after', [ $this, 'after_define_popups' ], 10, 3 );

		//add_filter( 'jet-popup/settings/default-popup-settings', [ $this, 'modify_default_popup_settings' ], 10, 2 );
		add_filter( 'jet-popup/assets/frontend-css-deps', [ $this, 'modify_frontend_css_deps' ], 10, 2 );
		add_filter( 'jet-popup/post-type/content-type-options', array( $this, 'modify_content_type_options' ), 10, 2 );
		add_filter( 'jet-popup/post-type/default-content-type', array( $this, 'modify_default_content_type' ), 10, 2 );
		add_filter( 'jet-popup/popup-generator/close-icon-html', array( $this, 'modify_close_icon_html' ), 10, 5 );
		add_filter( 'jet-popup/export-import/export-data', array( $this, 'modify_export_data' ), 10, 3 );
		add_filter( 'jet-popups/post-type/single-template', array( $this, 'modify_single_template' ), 10, 3 );
		//add_filter( 'jet-popup/frontend/deps-scripts', array( $this, 'modify_frontend_deps_scripts' ) );
		add_filter( 'jet-popup/popup-generator/is-define-popups', array( $this, 'maybe_popup_define_disabled' ) );
		// add_filter( 'elementor/utils/is_post_support', array( $this, 'maybe_disable_post_support' ), 10, 5 );

	}

	/**
	 * Load required files.
	 *
	 * @return void
	 */
	public function load_files() {
		require jet_popup()->plugin_path( 'includes/elementor/extensions.php' );
	}

	/**
	 * @return void
	 */
	public function init() {
		$this->extensions = new Elementor_Extensions();
	}

	/**
	 * Register cherry category for elementor if not exists
	 *
	 * @return void
	 */
	public function register_category() {

		$elements_manager = \Elementor\Plugin::instance()->elements_manager;
		$category         = 'jet-popup';

		$elements_manager->add_category(
			$category,
			array(
				'title' => esc_html__( 'JetPopup', 'jet-popup' ),
				'icon'  => 'font',
			)
		);
	}

	/**
	 * Init JetElementorExtension Module
	 */
	public function init_extension_module() {
		$ext_module_data = jet_popup()->module_loader->get_included_module_data( 'jet-elementor-extension.php' );
		\Jet_Elementor_Extension\Module::get_instance( $ext_module_data );
	}

	/**
	 * Register plugin addons
	 *
	 * @param  object $widgets_manager Elementor widgets manager instance.
	 * @return void
	 */
	public function register_addons( $widgets_manager ) {
		require jet_popup()->plugin_path( 'includes/elementor/base/class-jet-popup-base.php' );

		foreach ( glob( jet_popup()->plugin_path( 'includes/elementor/addons/' ) . '*.php' ) as $file ) {
			$this->register_addon( $file, $widgets_manager );
		}

	}

	/**
	 * Register addon by file name
	 *
	 * @param  string $file            File name.
	 * @param  object $widgets_manager Widgets manager instance.
	 * @return void
	 */
	public function register_addon( $file, $widgets_manager ) {

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
	 * Add new controls.
	 *
	 * @param  object $controls_manager Controls manager instance.
	 * @return void
	 */
	public function add_controls( $controls_manager ) {

		$grouped = [
			'jet-popup-box-style'       => [
				'class' => 'Jet_Popup_Group_Control_Box_Style',
				'file'   => jet_popup()->plugin_path( 'includes/elementor/controls/groups/group-control-box-style.php' ),
			],
			'jet-popup-transform-style'       => [
				'class' => 'Jet_Popup_Group_Control_Transform_Style',
				'file'   => jet_popup()->plugin_path( 'includes/elementor/controls/groups/group-control-transform-style.php' ),
			],
		];

		foreach ( $grouped as $control_id => $control_data ) {

			if ( file_exists( $control_data['file'] ) ) {
				require $control_data['file'];

				$class_name = $control_data['class'];
				$controls_manager->add_group_control( $control_id, new $class_name() );
			}
		}

		$controls = [
			'jet_popup_search'       => [
				'class' => 'Jet_Popup_Control_Search',
				'file'   => jet_popup()->plugin_path( 'includes/elementor/controls/control-search.php' ),
			],
		];

		foreach ( $controls as $control_id => $control_data ) {

			if ( file_exists( $control_data['file'] ) ) {
				require $control_data['file'];

				$class_name = 'Elementor\\' . $control_data['class'];

				if ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, '3.5.0', '>=' ) ) {
					$controls_manager->register( new $class_name() );
				} else {
					$controls_manager->register_control( $control_id, new $class_name() );
				}
			}
		}
	}

	/**
	 * Register apropriate document type for 'jet-woo-builder' post type
	 *
	 * @param  Elementor\Core\Documents_Manager $documents_manager [description]
	 * @return void
	 */
	public function register_document_type( $documents_manager ) {
		require jet_popup()->plugin_path( 'includes/elementor/document-types/document.php' );
		require jet_popup()->plugin_path( 'includes/elementor/document-types/not-supported.php' );

		$documents_manager->register_document_type( jet_popup()->post_type->slug(), 'Jet_Popup_Document' );
		$documents_manager->register_document_type( jet_popup()->post_type->slug() . '-not-supported', 'Jet_Popup_Not_Supported' );
	}

	/**
	 * Set apropriate document type on post creation
	 *
	 * @param int     $post_id Created post ID.
	 * @param WP_Post $post    Created post object.
	 */
	public function set_document_type_on_post_create( $post_id, $post ) {

		if ( $post->post_type !== jet_popup()->post_type->slug() ) {
			return;
		}

		$documents = \Elementor\Plugin::instance()->documents;
		$doc_type  = $documents->get_document_type( jet_popup()->post_type->slug() );

		update_post_meta( $post_id, $doc_type::TYPE_META_KEY, jet_popup()->post_type->slug() );
	}

	/**
	 * [add_jet_popup_category description]
	 * @param [type] $categories_manager [description]
	 */
	public function add_jet_popup_finder_category( $categories_manager ) {

		require jet_popup()->plugin_path( 'includes/elementor/finder-categories/finder-category.php' );

		if ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, '3.5.0', '>=' ) ) {
			$categories_manager->register( new Finder_Category() );
		} else {
			$categories_manager->add_category( 'jet-popup-finder-category', new Finder_Category() );
		}
	}
	/**
	 * Enqueue plugin scripts only with elementor scripts
	 *
	 * @return void
	 */
	public function editor_scripts() {

		$screen = get_current_screen();

		if ( 'jet-popup' !== $screen->post_type ) {
			return;
		}

		wp_enqueue_script(
			'jet-anime-js',
			jet_popup()->plugin_url( 'assets/js/lib/anime-js/anime.min.js' ),
			array( 'jquery' ),
			'2.0.2',
			true
		);

		wp_enqueue_script(
			'jet-popup-editor',
			jet_popup()->plugin_url( 'assets/js/jet-popup-editor.js' ),
			array(
				'jquery',
				'underscore',
				'backbone-marionette',
				'wp-api-fetch',
			),
			jet_popup()->get_version(),
			true
		);

		$this->editor_localize_data = apply_filters( 'jet-popups/assets/editor_localize_data', [
			'version'              => jet_popup()->get_version(),
			'conditionManagerUrl'  => admin_url( sprintf( 'edit.php?post_type=jet-popup&library_action=edit_conditions&popup_id=%1$s', get_the_ID() ) ),
			'getElementorIconHtml' => 'jet-popup/v2/get-elementor-icon-html',
		] );

		wp_localize_script( 'jet-popup-editor', 'jetPopupData', $this->editor_localize_data );
	}

	/**
	 * Load preview assets
	 *
	 * @return void
	 */
	public function preview_styles() {
		wp_enqueue_style(
			'jet-popup-preview',
			jet_popup()->plugin_url( 'assets/css/jet-popup-preview.css' ),
			array(),
			jet_popup()->get_version()
		);
	}

	/**
	 * @param $element
	 *
	 * @return void
	 */
	public function before_element_render( $element ) {
		$settings = $element->get_settings_for_display();
		$jedv_check_status = isset( $element->jedv_check_status ) && ! $element->jedv_check_status ? false : true;

		if ( ! empty( $settings['jet_attached_popup'] ) && $jedv_check_status ) {
			jet_popup()->generator->add_attached_popup( $settings['jet_attached_popup'] );
		}
	}

	/**
	 * @param $defined_popup_list
	 * @param $ajax_popup_defined
	 *
	 * @return void
	 */
	public function after_define_popups( $defined_popup_list, $ajax_popup_defined ) {

		// Init Elementor frontend essets if popup loaded using ajax
		if ( $ajax_popup_defined && ! \Elementor\Plugin::$instance->frontend->has_elementor_in_page() ) {
			\Elementor\Plugin::$instance->frontend->enqueue_styles();
			\Elementor\Plugin::$instance->frontend->enqueue_scripts();
		}
	}

	/**
	 * @param $document
	 * @param $data
	 *
	 * @return void
	 */
	public function document_ajax_save( $data, $document ) {

		if ( ! is_a( $document, 'Jet_Popup_Document') ) {
			return $data;
		}

		$popup_id = $document->get_main_id();
		$settings = $document->get_settings();
		$popup_settings = jet_popup()->settings->merge_with_defaults_settings( $settings );

		update_post_meta( $popup_id, '_settings', $popup_settings );

		return $data;
	}

	/**
	 * @param $defaults
	 *
	 * @return mixed
	 */
	public function modify_default_popup_settings( $defaults ) {

		$defaults['selected_close_button_icon'] = [
			'value'   => 'fas fa-times',
			'library' => 'fa-solid',
		];

		return $defaults;
	}

	/**
	 * @param $deps
	 *
	 * @return mixed
	 */
	public function modify_frontend_css_deps( $deps ) {

		if ( ! \Elementor\Plugin::$instance->frontend->has_elementor_in_page() ) {
			//$deps[] = 'font-awesome';
		}

		return $deps;
	}

	/**
	 * @return void
	 */
	public function frontend_register_scripts() {
		\Elementor\Plugin::instance()->frontend->register_scripts();
	}

	/**
	 * @param $options
	 *
	 * @return mixed
	 */
	public function modify_content_type_options( $options ) {
		$options[] = [
			'label' => __( 'Elementor', 'jet-popup' ),
			'value' => 'elementor',
		];

		return $options;
	}

	/**
	 * @param $icons
	 *
	 * @return mixed
	 */
	public function modify_content_type_icons( $icons ) {
		$icons['elementor'] = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M6.6 3H3V21H6.6V3ZM21 13.7964V10.1964L10.2 10.1964V13.7964H21ZM21 3V6.6L10.2 6.6V3H21ZM21 20.9921V17.3921H10.2V20.9921H21Z" fill="#23282D"/></svg>';

		return $icons;
	}

	/**
	 * @param $type
	 *
	 * @return string
	 */
	public function modify_default_content_type( $type ) {
		return 'elementor';
	}

	/**
	 * @param $html
	 * @param $popup_id
	 * @param $popup_settings
	 * @param $setting
	 *
	 * @return string|void
	 */
	public function modify_close_icon_html( $html, $popup_id, $popup_settings, $setting ) {

		if ( 'elementor' === jet_popup()->post_type->get_popup_content_type( $popup_id ) ) {
			return \Jet_Popup_Utils::__render_icon( $popup_settings, $setting, '<div class="jet-popup__close-button">%s</div>' );
		}

		return $html;
	}

	/**
	 * @param $export_data
	 * @param $popup_id
	 * @return mixed
	 */
	public function modify_export_data( $export_data, $popup_id ) {

		if ( ! \Elementor\Plugin::$instance->documents->get( $popup_id )->is_built_with_elementor() ) {
			return $export_data;
		}

		$db = \Elementor\Plugin::$instance->db;
		$export_data['content'] = $db->get_builder( $popup_id );

		$elementor_page_settings = get_post_meta( $popup_id, '_elementor_page_settings', true );

		if ( $elementor_page_settings ) {
			$export_data['page_settings'] = $elementor_page_settings;
		}

		return $export_data;
	}

	/**
	 * @param $deps
	 *
	 * @return mixed
	 */
	public function modify_frontend_deps_scripts( $deps ) {
		$deps[] = 'elementor-frontend';

		return $deps;
	}

	/**
	 * @param $template
	 * @return mixed
	 */
	public function modify_single_template( $template ) {

		if ( \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
			return jet_popup()->plugin_path( 'templates/editor.php' );
		}

		return $template;
	}

	/**
	 * @param maybe_popup_define_disabled
	 * @return array
	 */
	public function maybe_popup_define_disabled( $define ) {

		if ( \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
			return false;
		}

		return $define;
	}

	/**
	 * @return void
	 */
	public function enqueue_scripts() {
		$frontend_deps_scripts = apply_filters( 'jet-popup/elementor/frontend/deps-scripts', [ 'jquery', 'elementor-frontend' ] );

		wp_enqueue_script(
			'jet-popup-elementor-frontend',
			jet_popup()->plugin_url( 'includes/elementor/assets/js/jet-popup-elementor-frontend.js' ),
			$frontend_deps_scripts,
			jet_popup()->get_version(),
			true
		);
	}

	/**
	 * @param $is_supported
	 * @param $popup_id
	 * @param $post_type
	 * @return false|mixed
	 */
	public function maybe_disable_post_support( $is_supported, $popup_id, $post_type ) {

		if ( empty( $popup_id ) ) {
			$post = get_post();
			$popup_id = $post->ID;
		}

		if ( 'jet-popup' === $post_type ) {
			$content_type = jet_popup()->post_type->get_popup_content_type( $popup_id );

			if ( 'default' === $content_type ) {
				return false;
			}
		}

		return $is_supported;
	}

	/**
	 * Returns the instance.
	 *
	 * @since  1.0.0
	 * @return object
	 */
	public static function get_instance( $shortcodes = array() ) {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self( $shortcodes );
		}
		return self::$instance;
	}
}