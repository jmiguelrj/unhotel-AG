<?php
namespace Jet_Engine_Layout_Switcher;

class Frontend {

	private $current_template = array();

	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_styles' ) );

		add_action( 'jet-engine/listings/preview-scripts', array( $this, 'enqueue_preview_scripts' ) );

		add_filter( 'jet-engine/listing/render/default-settings',  array( $this, 'update_listing_default_settings' ) );
		add_filter( 'jet-engine/listing/grid/nav-widget-settings', array( $this, 'update_listing_nav_widget_settings' ), 10, 2 );

		add_action( 'wp_ajax_jet_engine_switch_layout',        array( $this, 'switch_layout' ) );
		add_action( 'wp_ajax_nopriv_jet_engine_switch_layout', array( $this, 'switch_layout' ) );

		add_filter( 'jet-engine/listing/render/jet-listing-grid/settings', array( $this, 'maybe_apply_layout_settings' ) );

		add_filter( 'jet-engine/listing/listing-id',          array( $this, 'setup_layout_listing' ), 0, 2 );
		add_filter( 'jet-engine/listing/container-classes',   array( $this, 'update_container_classes' ), 0, 2 );
		add_filter( 'jet-engine/listing/grid/add-query-data', array( $this, 'add_query_data' ), 10, 2 );

		add_action( 'jet-engine/query-builder/query/after-query-setup', array( $this, 'set_filtered_query_on_switch_layout' ) );
		add_filter( 'jet-engine/listing/grid/posts-query-args',         array( $this, 'update_query_on_switch_layout' ), 10, 3 );

		// JetThemeCore compatibility hooks
		add_action( 'jet-theme-core/theme-builder/render/location/before', array( $this, 'set_current_template' ), 10, 3 );

		$locations = array(
			'header', 'footer', 'single', 'page', 'archive', 'products-archive', 'products-card',
			'account-page', 'products-checkout-endpoint', 'single-product', 'products-checkout',
		);

		foreach ( $locations as $location ) {
			add_filter( "jet-theme-core/theme-builder/render/{$location}-location/after", array( $this, 'reset_current_template' ) );
		}

		add_filter( 'jet-engine-layout-switcher/current-post-id', array( $this, 'maybe_update_current_post_id' ) );
	}

	public function register_scripts() {
		wp_register_script(
			'jet-engine-layout-switcher',
			JET_ENGINE_LAYOUT_SWITCHER_URL . 'assets/js/layout-switcher.js',
			array( 'jquery', 'jet-engine-frontend' ),
			JET_ENGINE_LAYOUT_SWITCHER_VERSION,
			true
		);
	}

	public function register_styles() {
		wp_register_style(
			'jet-engine-layout-switcher',
			JET_ENGINE_LAYOUT_SWITCHER_URL . 'assets/css/layout-switcher.css',
			array(),
			JET_ENGINE_LAYOUT_SWITCHER_VERSION,
		);
	}

	public function enqueue_preview_scripts() {
		wp_enqueue_style( 'jet-engine-layout-switcher' );
		//wp_enqueue_script( 'jet-engine-layout-switcher' );
	}

	public function update_listing_default_settings( $settings ) {
		$settings['_id'] = '';
		$settings['_layout_listing'] = '';
		return $settings;
	}

	public function update_listing_nav_widget_settings( $result, $settings ) {
		$result['_id'] = ! empty( $settings['_id'] ) ? $settings['_id'] : '';
		$result['_layout_listing'] = ! empty( $settings['_layout_listing'] ) ? $settings['_layout_listing'] : '';
		return $result;
	}

	public function switch_layout() {
		$widget_id = ! empty( $_REQUEST['widget_id'] ) ? $_REQUEST['widget_id'] : false;
		$layout    = ! empty( $_REQUEST['layout'] ) ? $_REQUEST['layout'] : false;
		$view      = ! empty( $_REQUEST['view'] ) ? $_REQUEST['view'] : '';

		if ( empty( $widget_id ) || empty( $layout ) ) {
			wp_send_json_error();
		}

		$cookie_val = array(
			'layout' => $layout,
			'view'   => $view,
		);

		setcookie(
			'jet_engine_layout_' . esc_attr( $widget_id ),
			json_encode( $cookie_val ),
			time() + MONTH_IN_SECONDS,
			COOKIEPATH ? COOKIEPATH : '/',
			COOKIE_DOMAIN,
			( false !== strstr( get_option( 'home' ), 'https:' ) && is_ssl() ),
			true
		);

		wp_send_json_success();
	}

	public function maybe_apply_layout_settings( $settings ) {

		if ( ! empty( $settings['lazy_load'] ) ) {
			return $settings;
		}

		// Break on load more
		if ( jet_engine()->listings->is_listing_ajax( 'listing_load_more' ) ) {
			return $settings;
		}

		// Break on switch layout
		if ( ! empty( $_REQUEST['switch_layout'] ) ) {
			return $settings;
		}

		$widget_id = ! empty( $settings['_id'] ) ? $settings['_id'] : false;

		// Get the widget id on lazy load
		if ( ! $widget_id && ! empty( $_REQUEST['action'] ) && 'jet_engine_ajax' === $_REQUEST['action']
			 && ! empty( $_REQUEST['handler'] ) && 'get_listing' === $_REQUEST['handler']
			 && ! empty( $_REQUEST['element_id'] )
		) {
			$widget_id = $_REQUEST['element_id'];
		}

		if ( ! $widget_id ) {
			return $settings;
		}

		$cookie_key = 'jet_engine_layout_' . esc_attr( $widget_id );

		if ( ! isset( $_COOKIE[ $cookie_key ] ) ) {
			return $settings;
		}

		$cookie_value = isset( $_COOKIE[ $cookie_key ] ) ? json_decode( wp_unslash( $_COOKIE[ $cookie_key ] ), true ) : false;

		if ( empty( $cookie_value ) || empty( $cookie_value['layout'] ) ) {
			return $settings;
		}

		$view = ! empty( $cookie_value['view'] ) ? $cookie_value['view'] : 'elementor';
		$view_instance = jet_engine_layout_switcher()->get_view( $view );

		if ( ! $view_instance ) {
			return $settings;
		}

		if ( $view_instance->is_edit_mode() ) {
			return $settings;
		}

		$relevant_switcher = $view_instance->find_relevant_switcher_on_page( $widget_id );

		if ( ! $relevant_switcher ) {
			return $settings;
		}

		$layouts = ! empty( $relevant_switcher['layouts'] ) ? $relevant_switcher['layouts'] : array();

		if ( empty( $layouts ) ) {
			return $settings;
		}

		$layout_settings = array();

		foreach ( $layouts as $layout ) {
			$slug = Render::get_prepared_slug( $layout );

			if ( $cookie_value['layout'] === $slug ) {
				$layout_settings = $layout;
				break;
			}
		}

		if ( empty( $layout_settings ) ) {
			return $settings;
		}

		if ( ! empty( $layout_settings['is_default_layout'] ) ) {
			return $settings;
		}

		if ( ! empty( $layout_settings['lisitng_id'] ) ) {
			$settings['_layout_listing'] = (int) $layout_settings['lisitng_id'];
		}

		$not_allowed_keys_to_merge = array(
			'label',
			'slug',
			'icon',
			'icon_url',
			'is_default_layout',
			'lisitng_id' // to prevent rewrite initial listing.
		);
		
		foreach ( $not_allowed_keys_to_merge as $not_allowed_key ) {
			if ( isset( $layout_settings[ $not_allowed_key ] ) ) {
				unset( $layout_settings[ $not_allowed_key ] );
			}
		}

		return array_merge( $settings, $layout_settings );
	}

	public function setup_layout_listing( $listing_id, $settings ) {

		if ( ! empty( $settings['_layout_listing'] ) ) {
			return (int) $settings['_layout_listing'];
		}

		return $listing_id;
	}

	public function update_container_classes( $classes, $settings ) {

		if ( empty( $settings['_layout_listing'] ) ) {
			return $classes;
		}

		$initial_listing_id = $settings['lisitng_id'];

		$classes = array_filter( $classes, function( $class ) use ( $initial_listing_id ) {
			return 'jet-listing-grid--' . $initial_listing_id !== $class;
		} );

		$classes[] = 'jet-listing-grid--' . absint( $settings['_layout_listing'] );

		return $classes;
	}

	public function add_query_data( $add_query_data, $render ) {

		if ( $add_query_data ) {
			return $add_query_data;
		}

		if ( ! empty( $render->listing_query_id ) ) {
			return $add_query_data;
		}

		if ( ! function_exists( 'jet_smart_filters' ) ) {
			return $add_query_data;
		}

		if ( jet_engine()->listings->is_listing_ajax() && ! empty( $_REQUEST['switch_layout'] ) ) {
			return true;
		}

		if ( $this->is_filters_request() ) {
			return true;
		}

		return $add_query_data;
	}

	public function is_filters_request() {

		if ( ! empty( $_REQUEST['action'] ) && 'jet_smart_filters' === $_REQUEST['action'] && ! empty( $_REQUEST['provider'] ) ) {
			return true;
		}

		if ( ! empty( $_REQUEST['jet-smart-filters'] ) ) {
			return true;
		}

		if ( ! empty( $_REQUEST['jsf'] ) ) {
			return true;
		}

		return false;
	}

	public function set_filtered_query_on_switch_layout( $query ) {

		if ( ! jet_engine()->listings->is_listing_ajax() ) {
			return;
		}

		if ( empty( $_REQUEST['switch_layout'] ) || empty( $_REQUEST['listing_query_id'] ) ) {
			return;
		}

		$l_query_id = intval( $_REQUEST['listing_query_id'] );
		$query_id   = intval( $query->id );

		if ( $l_query_id !== $query_id ) {
			return;
		}

		if ( function_exists( 'jet_smart_filters' ) && ! empty( $_REQUEST['filtered_query'] ) ) {
			$filtered_query = jet_smart_filters()->query->get_query_from_request( $_REQUEST['filtered_query'] );

			if ( empty( $filtered_query['jet_smart_filters'] ) && ! empty( $_REQUEST['filter_provider'] ) ) {
				$filtered_query['jet_smart_filters'] = esc_attr( $_REQUEST['filter_provider'] );
			}

			if ( ! empty( $filtered_query ) ) {
				foreach ( $filtered_query as $prop => $value ) {
					$query->set_filtered_prop( $prop, $value );
				}
			}
		}

		// Load more props
		if ( ! empty( $_REQUEST['loadMorePages'] ) && ! empty( $query->final_query['posts_per_page'] ) ) {
			$first_page = intval( $_REQUEST['loadMorePages']['first'] );
			$last_page  = intval( $_REQUEST['loadMorePages']['last'] );

			$per_page    = absint( $query->final_query['posts_per_page'] );
			$offset      = ( $first_page - 1 ) * $per_page;
			$items_count = ( $last_page - $first_page + 1 ) * $per_page;

			$query->set_filtered_prop( '_page', 1 );
			$query->set_filtered_prop( 'offset', $offset );
			$query->set_filtered_prop( 'posts_per_page', $items_count );
		}
	}

	public function update_query_on_switch_layout( $args, $render, $settings ) {

		if ( ! jet_engine()->listings->is_listing_ajax() ) {
			return $args;
		}

		if ( empty( $_REQUEST['switch_layout'] ) ) {
			return $args;
		}

		if ( empty( $_REQUEST['query'] ) || empty( $_REQUEST['widget_settings'] ) ) {
			return $args;
		}

		if ( ! isset( $settings['_id'] ) || ! isset( $_REQUEST['widget_settings']['_id'] ) ) {
			return $args;
		}

		if ( $settings['_id'] !== $_REQUEST['widget_settings']['_id'] ) {
			return $args;
		}

		$args = wp_unslash( $_REQUEST['query'] );

		if ( isset( $args['suppress_filters'] ) ) {
			$args['suppress_filters'] = filter_var( $args['suppress_filters'], FILTER_VALIDATE_BOOLEAN );
		}

		return $args;
	}

	public function set_current_template( $location, $template_id, $content_type ) {
		$this->current_template = array(
			'template_id'  => $template_id,
			'location'     => $location,
			'content_type' => $content_type,
		);
	}

	public function reset_current_template() {
		$this->current_template = array();
	}

	public function maybe_update_current_post_id( $post_id ) {

		if ( empty( $this->current_template ) ) {
			return $post_id;
		}

		if ( empty( $this->current_template['template_id'] ) ) {
			return $post_id;
		}

		return $this->current_template['template_id'];
	}

}
