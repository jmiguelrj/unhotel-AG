<?php
namespace Jet_Popup;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Render_Manager {

	/**
	 * A reference to an instance of this class.
	 *
	 * @since 1.0.0
	 * @var   object
	 */
	private static $instance = null;

	/**
	 * [$popup_id_list description]
	 * @var array
	 */
	public $popup_id_list = [];

	/**
	 * @var array
	 */
	public $attached_popups = [];

	/**
	 * @var array
	 */
	public $defined_popup_list = [];

	/**
	 * [$ajax_popup_id_list description]
	 * @var array
	 */
	public $ajax_popup_defined = false;

	/**
	 * @var array
	 */
	public $styles_to_enqueue = [];

	/**
	 * [$depended_scripts description]
	 * @var array
	 */
	public $scripts_to_enqueue = [];

	/**
	 * Constructor for the class
	 */
	public function __construct() {
		$this->load_files();

		add_action( 'wp_footer', [ $this, 'page_popups_init' ], 1 );
		add_action( 'wp_footer', [ $this, 'page_popups_render' ], 2 );

	}

	/**
	 * Load files
	 */
	public function load_files() {
		require jet_popup()->plugin_path( 'includes/render/base-render.php' );
		require jet_popup()->plugin_path( 'includes/render/render-modules/block-editor-content-render.php' );
		require jet_popup()->plugin_path( 'includes/render/render-modules/elementor-content-render.php' );
		require jet_popup()->plugin_path( 'includes/render/render-modules/action-button-render.php' );
	}

	/**
	 * @param $popup_id
	 *
	 * @return void
	 */
	public function add_attached_popup( $popup_id = false ) {

		$status = get_post_status( $popup_id );
	
		if ( $status && 'publish' === $status ) {
	
			$this->attached_popups[] = $popup_id;
	
			$is_element_cache_active = 'disable' !== get_option( 'elementor_element_cache_ttl', '' );
	
			if ( $is_element_cache_active ) {
				$stored = get_transient( 'jetpopup_attached_popups' ) ?: [];
				if ( ! in_array( $popup_id, $stored, true ) ) {
					$stored[] = $popup_id;
					set_transient( 'jetpopup_attached_popups', $stored, HOUR_IN_SECONDS );
				}
			}
		}
	
		return false;
	}

	/**
	 * [get_attached_popups description]
	 * @return [type] [description]
	 */
	public function get_attached_popups() {
		return apply_filters( 'jet-popup/popup-generator/attached-popups', $this->attached_popups );
	}

	/**
	 * @return mixed|void
	 */
	public function get_defined_popup_list() {
		return apply_filters( 'jet-popup/popup-generator/defined-popup-list', $this->defined_popup_list );
	}

	/**
	 * Get styles dependencies.
	 *
	 * Retrieve the list of style dependencies the element requires.
	 *
	 * @since 1.3.0
	 * @access public
	 *
	 * @return array Element scripts dependencies.
	 */
	public function get_styles_to_enqueue() {
		return $this->styles_to_enqueue;
	}

	/**
	 * Get script dependencies.
	 *
	 * Retrieve the list of script dependencies the element requires.
	 *
	 * @since 1.3.0
	 * @access public
	 *
	 * @return array Element scripts dependencies.
	 */
	public function get_scripts_to_enqueue() {
		return $this->scripts_to_enqueue;
	}

	/**
	 * Page popup initialization
	 *
	 * @since 1.0.0
	 * @return void|boolean
	 */
	public function page_popups_init() {
		$is_define_popups = apply_filters( 'jet-popup/popup-generator/is-define-popups', true );

		if ( $is_define_popups ) {
			$this->define_page_popups();
		}
	}

	/**
	 * [get_page_popups description]
	 * @return [type] [description]
	 */
	public function define_page_popups() {

		$is_element_cache_active = 'disable' !== get_option( 'elementor_element_cache_ttl', '' );

		if ( $is_element_cache_active ) {
			$this->attached_popups = get_transient('jetpopup_attached_popups') ?: [];
		}

		$condition_popups = jet_popup()->conditions_manager->find_matched_popups_by_conditions();

		$attached_popups = $this->get_attached_popups();

		if ( ! empty( $condition_popups ) && is_array( $condition_popups ) ) {
			$this->popup_id_list = array_merge( $this->popup_id_list, $condition_popups );
		}

		if ( ! empty( $attached_popups ) && is_array( $attached_popups ) ) {
			$this->popup_id_list = array_merge( $this->popup_id_list, $attached_popups );
		}

		if ( empty( $this->popup_id_list ) ) {
			return false;
		}

		$this->popup_id_list = array_unique( $this->popup_id_list );

		$this->defined_popup_list = array_map( function ( $popup_id ) {
			$popup_settings = jet_popup()->settings->get_popup_settings( $popup_id );

			if ( isset( $popup_settings['jet_popup_use_ajax'] ) && filter_var( $popup_settings['jet_popup_use_ajax'], FILTER_VALIDATE_BOOLEAN ) ) {
				$this->ajax_popup_defined = true;
			}

			return [
				'id'       => $popup_id,
				'settings' => $popup_settings,
			];
		}, $this->popup_id_list );

		do_action( 'jet-popup/render-manager/define-popups/after', $this->defined_popup_list, $this->ajax_popup_defined );
	}

	/**
	 * [page_popups_render description]
	 * @return [type] [description]
	 */
	public function page_popups_render() {
		$defined_popup_list = $this->get_defined_popup_list();

		if ( empty( $defined_popup_list ) ) {
			return false;
		}

		foreach ( $defined_popup_list as $key => $popup_data ) {
			$this->popup_render( $popup_data['id'], $popup_data['settings'] );
			jet_popup()->admin_bar->register_post_item( $popup_data['id'] );
		}

		$this->maybe_enqueue_styles();
		$this->maybe_enqueue_scripts();
	}

	/**
	 * [popup_render description]
	 * @param  [type] $popup_id [description]
	 * @return [type]           [description]
	 */
	public function popup_render( $popup_id = false, $popup_settings = [], $attrs = [] ) {

		if ( empty( $popup_settings ) ) {
			return false;
		}

		$close_button_html = '';
		$use_close_button = isset( $popup_settings['use_close_button'] ) ? filter_var( $popup_settings['use_close_button'], FILTER_VALIDATE_BOOLEAN ) : true;

		if ( isset( $popup_settings['close_button_icon'] ) && $use_close_button ) {
			$default_close_button_html = sprintf( '<div class="jet-popup__close-button">%s</div>', \Jet_Popup_Utils::get_default_svg_html( 'close' ) );
			$close_button_html = \Jet_Popup_Utils::get_svg_icon_html( $popup_settings[ 'close_button_icon' ], $default_close_button_html, [ 'class' => 'jet-popup__close-button' ], true );
			$close_button_html = apply_filters( 'jet-popup/popup-generator/close-icon-html', $close_button_html, $popup_id, $popup_settings, 'close_button_icon' );

			if ( empty( $close_button_html ) ) {
				$close_button_html = $default_close_button_html;
			}
		}

		$overlay_html      = '';
		$use_overlay       = isset( $popup_settings['use_overlay'] ) ? filter_var( $popup_settings['use_overlay'], FILTER_VALIDATE_BOOLEAN ) : true;
		$use_ajax          = filter_var( $popup_settings['jet_popup_use_ajax'], FILTER_VALIDATE_BOOLEAN );
		$use_content_cache = filter_var( $popup_settings['use_content_cache'], FILTER_VALIDATE_BOOLEAN );

		if ( $use_overlay ) {
			$overlay_html = sprintf(
				'<div class="jet-popup__overlay">%s</div>',
				$use_ajax ? '<div class="jet-popup-loader"></div>' : ''
			);
		}

		$jet_popup_show_again_delay = \Jet_Popup_Utils::get_milliseconds_by_tag( $popup_settings['jet_popup_show_again_delay'] );
		$content_type = jet_popup()->post_type->get_popup_content_type( $popup_id );

		$popup_json = [
			'id'                     => $popup_id,
			'jet-popup-id'           => 'jet-popup-' . $popup_id,
			'type'                   => $popup_settings['jet_popup_type'],
			'animation'              => $popup_settings['jet_popup_animation'],
			'open-trigger'           => $popup_settings['jet_popup_open_trigger'],
			'close-event'            => $popup_settings['jet_popup_on_close_event'],
			'сlose-event-anchor'     => $popup_settings['jet_popup_scroll_to_anchor'],
			'page-load-delay'        => $popup_settings['jet_popup_page_load_delay'],
			'user-inactivity-time'   => $popup_settings['jet_popup_user_inactivity_time'],
			'scrolled-to'            => $popup_settings['jet_popup_scrolled_to_value'],
			'on-date'                => $popup_settings['jet_popup_on_date_value'],
			'on-time-start'          => $popup_settings['jet_popup_on_time_start_value'],
			'on-time-end'            => $popup_settings['jet_popup_on_time_end_value'],
			'start-date-and-time'    => $popup_settings['jet_popup_start_date_and_time'],
			'end-date-and-time'      => $popup_settings['jet_popup_end_date_and_time'],
			'custom-selector'        => $popup_settings['jet_popup_custom_selector'],
			'prevent-scrolling'      => filter_var( $popup_settings['jet_popup_prevent_scrolling'], FILTER_VALIDATE_BOOLEAN ),
			'show-once'              => filter_var( $popup_settings['jet_popup_show_once'], FILTER_VALIDATE_BOOLEAN ),
			'show-again-delay'       => $jet_popup_show_again_delay,
			'use-ajax'               => $use_ajax,
			'force-ajax'             => filter_var( $popup_settings['jet_popup_force_ajax'], FILTER_VALIDATE_BOOLEAN ),
			'close-on-overlay-click' => filter_var( $popup_settings['close_on_overlay_click'], FILTER_VALIDATE_BOOLEAN ),
			'content-type'           => $content_type,
		];

		$popup_json_data = htmlspecialchars( json_encode( $popup_json ) );

		$class_array = [
			'jet-popup',
			'jet-popup--front-mode',
			'jet-popup--hide-state',
			'jet-popup--animation-' . $popup_settings['jet_popup_animation'],
		];

		if ( isset( $attrs['classes'] ) && is_array( $attrs['classes'] ) ) {
			$class_array = array_merge( $class_array, $attrs['classes'] );
		}

		$popup_inline_styles = '';

		$popup_dependencies = get_post_meta( $popup_id, '_is_deps_ready', true );
		$is_content = ! $use_ajax || empty( $popup_dependencies ) ? true : false;

		switch ( $content_type ) {
			case 'default':
				$render_instance = new \Jet_Popup\Render\Block_Editor_Content_Render( [
					'popup_id'         => $popup_id,
					'with_css'         => true,
					'is_content'       => $is_content,
					'is_content_cache' => $use_content_cache,
				] );

				$popup_inline_styles = $render_instance->get_css();

				break;
			case 'elementor':
				$render_instance = new \Jet_Popup\Render\Elementor_Content_Render( [
					'popup_id'         => $popup_id,
					'with_css'         => true,
					'is_content'       => $is_content,
					'is_content_cache' => $use_content_cache,
				] );

				break;
		}

		if ( $is_content ) {
			update_post_meta( $popup_id, '_is_deps_ready', 'true' );
		}

		$content_cache_settings = jet_popup()->settings->get( 'useContentCache', [
			'enable'          => false,
			'cacheByUrl'      => false,
			'cacheExpiration' => 'week',
		] );

		if ( ! $use_ajax && $content_cache_settings['enable'] ) {
			$cache_expiration_timeout = \Jet_Popup_Utils::get_milliseconds_by_tag( $content_cache_settings['cacheExpiration'] );
			$cache_expiration_timeout = 'none' !== $cache_expiration_timeout ? $cache_expiration_timeout : YEAR_IN_SECONDS;
			$transient_key = \Jet_Popup_Utils::get_render_data_cache_key( $popup_id, $content_cache_settings['cacheByUrl'] );
			$transient_data = jet_get_transient( $transient_key, false );

			if ( ! empty( $transient_data ) ) {
				$this->update_popup_cache( $popup_id );
				$transient_data = jet_get_transient( $transient_key, false );
			} else {
				$render_data = $render_instance->get_render_data();
				jet_set_transient( $transient_key, $render_data, $cache_expiration_timeout, $popup_id, 'jet-popup' );
		
				$transient_data = jet_get_transient( $transient_key, false );
		
				if ( empty( $transient_data ) ) {
					$this->update_popup_cache( $popup_id );
					$transient_data = jet_get_transient( $transient_key, false );
				}
			}
		
			$render_data = ! empty( $transient_data ) ? $transient_data : $render_instance->get_render_data();

		} else {
			$render_data = $render_instance->get_render_data();
		}

		$render_data = apply_filters( 'jet-plugins/render/render-data', $render_data, $popup_id, $content_type );

		$popup_content = ! $use_ajax ? $render_data['content'] : '';
		$popup_styles  = $render_data['styles'];
		$popup_scripts = $render_data['scripts'];

		$this->styles_to_enqueue = wp_parse_args( $popup_styles, $this->styles_to_enqueue );
		$this->scripts_to_enqueue = wp_parse_args( $popup_scripts, $this->scripts_to_enqueue );

		$content_html = sprintf( '<div class="jet-popup__container-content">%1$s</div>', $popup_content );
		$container_html = sprintf( '<div class="jet-popup__container"><div class="jet-popup__container-inner"><div class="jet-popup__container-overlay"></div>%1$s</div>%2$s</div>',
			$content_html,
			$close_button_html
		);

		$html = sprintf( '<div id="jet-popup-%1$s" class="%2$s" data-settings="%3$s"><div class="jet-popup__inner">%4$s%5$s</div>%6$s</div>',
			$popup_id,
			implode( ' ', $class_array ),
			$popup_json_data,
			$overlay_html,
			$container_html,
			$popup_inline_styles
		);

		echo $html; // phpcs:ignore
	}

	public function update_popup_cache( $popup_id ) {

		if ( ! get_post( $popup_id ) ) {
			return;
		}

		$popup_settings = jet_popup()->settings->get_popup_settings( $popup_id );
	
		$use_ajax          = filter_var( $popup_settings['jet_popup_use_ajax'] ?? false, FILTER_VALIDATE_BOOLEAN );
		$use_content_cache = filter_var( $popup_settings['use_content_cache'] ?? false, FILTER_VALIDATE_BOOLEAN );
	
		$content_cache_settings = jet_popup()->settings->get( 'useContentCache', [
			'enable'          => false,
			'cacheByUrl'      => false,
			'cacheExpiration' => 'week',
		] );
	
		if ( $use_ajax || ! $use_content_cache || ! $content_cache_settings['enable'] ) {
			return;
		}
	
		$content_type = jet_popup()->post_type->get_popup_content_type( $popup_id );
	
		$is_content = true;
		$render_instance = null;
		$popup_inline_styles = '';
	
		switch ( $content_type ) {
			case 'default':
				$render_instance = new \Jet_Popup\Render\Block_Editor_Content_Render( [
					'popup_id'         => $popup_id,
					'with_css'         => true,
					'is_content'       => $is_content,
					'is_content_cache' => $use_content_cache,
				] );
	
				$popup_inline_styles = $render_instance->get_css();
				break;
	
			case 'elementor':
				$render_instance = new \Jet_Popup\Render\Elementor_Content_Render( [
					'popup_id'         => $popup_id,
					'with_css'         => true,
					'is_content'       => $is_content,
					'is_content_cache' => $use_content_cache,
				] );
				break;
	
			default:
				// Unsupported content type
				return;
		}

		if ( ! $render_instance ) {
			return;
		}
	
		$render_data = $render_instance->get_render_data();
	
		$render_data = apply_filters( 'jet_popup/cache/render_data', $render_data, $popup_id );
	
		$cache_expiration_timeout = \Jet_Popup_Utils::get_milliseconds_by_tag( $content_cache_settings['cacheExpiration'] );
		$cache_expiration_timeout = ( 'none' !== $cache_expiration_timeout ) ? $cache_expiration_timeout : YEAR_IN_SECONDS;
	
		// Generate the cache key
		$transient_key = \Jet_Popup_Utils::get_render_data_cache_key( $popup_id, $content_cache_settings['cacheByUrl'] );
	
		jet_set_transient( $transient_key, $render_data, $cache_expiration_timeout, $popup_id, 'jet-popup' );
	
		if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
			\Elementor\Core\Files\CSS\Post::create( $popup_id )->update();
		}
	}

	/**
	 * @return false|void
	 */
	public function maybe_enqueue_styles() {
		$style_depends = $this->get_styles_to_enqueue();

		if ( empty( $style_depends ) ) {
			return false;
		}

		foreach ( $style_depends as $key => $style_data ) {
			$style_handle = $style_data['handle'];

			if ( wp_style_is( $style_handle ) ) {
				continue;
			}

			$style_obj = $style_data['obj'];

			if ( ! isset( wp_styles()->registered[ $style_handle ] ) ) {
				wp_styles()->registered[ $style_handle ] = $style_obj;
			}

			wp_enqueue_style( $style_obj->handle, $style_obj->src, $style_obj->deps, $style_obj->ver );
		}
	}

	/**
	 * [page_popups_before_enqueue_scripts description]
	 * @return [type] [description]
	 */
	public function maybe_enqueue_scripts() {
		$script_depends = $this->get_scripts_to_enqueue();

		if ( empty( $script_depends ) ) {
			return false;
		}

		foreach ( $script_depends as $script => $script_data ) {

			$script_handle = $script_data['handle'];
			$script_obj = $script_data['obj'];

			if ( wp_script_is( $script_handle ) ) {
				continue;
			}

			wp_scripts()->registered[ $script_handle ] = $script_obj;

		}

		foreach ( $script_depends as $script => $script_data ) {
			
			$script_handle = $script_data['handle'];

			if ( wp_script_is( $script_handle ) ) {
				continue;
			}

			$script_obj = $script_data['obj'];

			wp_enqueue_script( $script_obj->handle, $script_obj->src, $script_obj->deps, $script_obj->ver );

			wp_scripts()->print_extra_script( $script_obj->handle );
		}
	}

	/*
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
