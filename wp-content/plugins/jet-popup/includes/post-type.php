<?php
/**
 * JetPopup post type template
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Popup_Post_Type' ) ) {

	/**
	 * Define Jet_Popup_Post_Type class
	 */
	class Jet_Popup_Post_Type {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * [$post_type description]
		 * @var string
		 */
		protected $post_type = 'jet-popup';

		/**
		 * [$meta_key description]
		 * @var string
		 */
		protected $meta_key = 'jet-popup-item';

		/**
		 * Constructor for the class
		 */
		public function __construct() {

			self::register_post_type();

			add_filter( 'option_elementor_cpt_support', [ $this, 'set_option_support' ] );
			add_filter( 'default_option_elementor_cpt_support', [ $this, 'set_option_support' ] );
			add_filter( 'manage_' . $this->slug() . '_posts_columns', [ $this, 'set_post_columns' ] );
			add_action( 'manage_' . $this->slug() . '_posts_custom_column', [ $this, 'post_columns' ], 10, 2 );
			add_action( 'template_include', [ $this, 'set_post_type_template' ], 9999 );
			add_action( 'save_post', [ $this, 'save_popup_post_type'], 10, 3 );
			add_action( 'wp_trash_post', [ $this, 'remove_popup_from_site_conditions' ] );
			add_action( 'admin_footer', [ $this, 'print_vue_templates' ], 998 );
			add_action( 'admin_footer', [ $this, 'print_template_library' ], 999 );
			add_action( 'load-post-new.php', [ $this, 'create_new_popup_redirect' ] );
			add_filter( 'admin_body_class', [ $this, 'body_status_classes' ] );
		}

		/**
		 * Returns post type slug
		 *
		 * @return string
		 */
		public function slug() {
			return $this->post_type;
		}

		/**
		 * Returns Mega Menu meta key
		 *
		 * @return string
		 */
		public function meta_key() {
			return $this->meta_key;
		}

		/**
		 * Set required post columns
		 *
		 * @param [type] $columns [description]
		 */
		public function set_post_columns( $columns ) {

			unset( $columns['date'] );
			unset( $columns['author'] );

			$columns['info']       = __( 'Info', 'jet-popup' );
			$columns['settings']   = __( 'Settings', 'jet-popup' );
			$columns['conditions'] = __( 'Visibility Conditions', 'jet-popup' );
			$columns['date']       = __( 'Date', 'jet-popup' );

			return $columns;
		}

		/**
		 * Manage post columns content
		 *
		 * @return [type] [description]
		 */
		public function post_columns( $column, $post_id ) {

			switch ( $column ) {
				case 'info':
					printf( '<div class="jet-popup-info" data-popup-id="%1$s">', esc_attr( $post_id ) );
					printf(
						'<div class="jet-popup-info-list">%1$s</div>',
						wp_kses_post( $this->popup_info_verbose( $post_id ) )
					);
					echo '</div>';
					break;

				case 'settings':
					printf( '<div class="jet-popup-settings" data-popup-id="%1$s">', esc_attr( $post_id ) );
					printf(
						'<div class="jet-popup-settings-list">%1$s</div>',
						wp_kses_post( $this->popup_settings_verbose( $post_id ) )
					);
					
					printf(
						'<a class="jet-popup-settings__edit-settings" href="#" data-popup-id="%1$s">%2$s<span>%3$s</span></a>',
						esc_attr( $post_id ),
						wp_kses_post( \Jet_Popup_Utils::get_admin_ui_icon( 'edit' ) ),
						esc_html__( 'Edit Settings', 'jet-popup' )
					);

					printf( '</div>' );
					break;

				case 'conditions':

					printf(
						'<div class="jet-popup-conditions" data-popup-id="%1$s">',
						esc_attr( $post_id )
					);
					
					printf(
						'<div class="jet-popup-conditions-list">%1$s</div>',
						wp_kses_post( jet_popup()->conditions_manager->popup_conditions_verbose( $post_id ) )
					);

					$edit_conditions_html = sprintf(
						'<a class="jet-popup-conditions__action jet-popup-conditions__edit-conditions" href="#" data-popup-id="%1$s">%2$s<span>%3$s</span></a>',
						$post_id,
						\Jet_Popup_Utils::get_admin_ui_icon( 'edit' ),
						__( 'Edit', 'jet-popup' )
					);

					$clear_conditions_html = sprintf(
						'<a class="jet-popup-conditions__action jet-popup-conditions__clear-conditions" href="#" data-popup-id="%1$s">%2$s<span>%3$s</span></a>',
						esc_attr( $post_id ),
						wp_kses_post( \Jet_Popup_Utils::get_admin_ui_icon( 'close' ) ),
						esc_html__( 'Clear', 'jet-popup' )
					);
					
					printf(
						'<div class="jet-popup-conditions__actions">%1$s%2$s</div>',
						wp_kses_post( $edit_conditions_html ),
						wp_kses_post( $clear_conditions_html )
					);

					printf( '</div>' );

					break;
			}
		}

		/**
		 * Add elementor support for mega menu items.
		 */
		public function set_option_support( $value ) {

			if ( empty( $value ) ) {
				$value = array();
			}

			return array_merge( $value, array( $this->slug() ) );
		}

		/**
		 * Register post type
		 *
		 * @return void
		 */
		static public function register_post_type() {

			$labels = array(
				'name'          => esc_html__( 'JetPopup', 'jet-popup' ),
				'singular_name' => esc_html__( 'JetPopup', 'jet-popup' ),
				'all_items'     => esc_html__( 'All Popups', 'jet-popup' ),
				'add_new'       => esc_html__( 'Create New Popup', 'jet-popup' ),
				'add_new_item'  => esc_html__( 'Create New Popup', 'jet-popup' ),
				'edit_item'     => esc_html__( 'Edit Popup', 'jet-popup' ),
				'menu_name'     => esc_html__( 'JetPopup', 'jet-popup' ),
			);

			$supports = apply_filters( 'jet-popups/post-type/register/supports', [ 'title', 'editor', 'author', 'elementor', 'custom-fields' ] );

			$args = array(
				'labels'              => $labels,
				'hierarchical'        => false,
				'description'         => 'description',
				'taxonomies'          => [],
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_admin_bar'   => true,
				'menu_position'       => 101,
				'menu_icon'           => 'data:image/svg+xml;base64,' . base64_encode('<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M20 1H4C2.34315 1 1 2.34315 1 4V20C1 21.6569 2.34315 23 4 23H20C21.6569 23 23 21.6569 23 20V4C23 2.34315 21.6569 1 20 1ZM4 0C1.79086 0 0 1.79086 0 4V20C0 22.2091 1.79086 24 4 24H20C22.2091 24 24 22.2091 24 20V4C24 1.79086 22.2091 0 20 0H4Z" fill="black"/><path fill-rule="evenodd" clip-rule="evenodd" d="M21.6293 6.00066C21.9402 5.98148 22.1176 6.38578 21.911 6.64277L20.0722 8.93035C19.8569 9.19824 19.4556 9.02698 19.4598 8.669L19.4708 7.74084C19.4722 7.61923 19.4216 7.50398 19.3343 7.42975L18.6676 6.86321C18.4105 6.6447 18.5378 6.19134 18.8619 6.17135L21.6293 6.00066ZM6.99835 12.008C6.99835 14.1993 5.20706 15.9751 2.99967 15.9751C2.44655 15.9751 2 15.5293 2 14.9827C2 14.4361 2.44655 13.9928 2.99967 13.9928C4.10336 13.9928 4.99901 13.1036 4.99901 12.008V9.03323C4.99901 8.48413 5.44556 8.04082 5.99868 8.04082C6.55179 8.04082 6.99835 8.48413 6.99835 9.03323V12.008ZM17.7765 12.008C17.7765 13.1036 18.6721 13.9928 19.7758 13.9928C20.329 13.9928 20.7755 14.4336 20.7755 14.9827C20.7755 15.5318 20.329 15.9751 19.7758 15.9751C17.5684 15.9751 15.7772 14.1993 15.7772 12.008V9.03323C15.7772 8.48413 16.2237 8.04082 16.7768 8.04082C17.33 8.04082 17.7765 8.48665 17.7765 9.03323V9.92237H18.5707C19.1238 9.92237 19.5729 10.3682 19.5729 10.9173C19.5729 11.4664 19.1238 11.9122 18.5707 11.9122H17.7765V12.008ZM15.2038 10.6176C15.2063 10.6151 15.2088 10.6151 15.2088 10.6151C14.8942 9.79393 14.3056 9.07355 13.4835 8.60001C11.5755 7.50181 9.13979 8.15166 8.04117 10.0508C6.94001 11.9475 7.59462 14.3731 9.50008 15.4688C10.9032 16.2749 12.593 16.1338 13.8261 15.2472L13.8184 15.2371C14.1026 15.0633 14.2904 14.751 14.2904 14.3958C14.2904 13.8492 13.8438 13.4059 13.2932 13.4059C13.0268 13.4059 12.7833 13.5092 12.6057 13.6805C12.0069 14.081 11.2102 14.1439 10.5378 13.7762L14.5644 11.9198C14.7978 11.8493 15.0059 11.6931 15.1353 11.4664C15.2926 11.1969 15.3078 10.8871 15.2038 10.6176ZM12.4864 10.3153C12.6057 10.3833 12.7122 10.4614 12.8112 10.5471L9.49754 12.0709C9.48993 11.7208 9.5762 11.3657 9.76395 11.0407C10.3145 10.0937 11.5324 9.76874 12.4864 10.3153Z" fill="#24292D"/></svg>'),
				'show_in_nav_menus'   => false,
				'show_in_rest'        => true,
				'publicly_queryable'  => true,
				'exclude_from_search' => true,
				'has_archive'         => false,
				'query_var'           => true,
				'can_export'          => true,
				'rewrite'             => true,
				'capabilities'        => array(
					'edit_post' => jet_popup()->get_admin_ui_cap(),
					'read_post' => jet_popup()->get_admin_ui_cap(),
					'delete_post' => jet_popup()->get_admin_ui_cap(),
					'edit_posts' => jet_popup()->get_admin_ui_cap(),
					'edit_others_posts' => jet_popup()->get_admin_ui_cap(),
					'delete_posts' => jet_popup()->get_admin_ui_cap(),
					'publish_posts' => jet_popup()->get_admin_ui_cap(),
					'read_private_posts' => jet_popup()->get_admin_ui_cap(),
					'read' => jet_popup()->get_admin_ui_cap(),
					'delete_private_posts' => jet_popup()->get_admin_ui_cap(),
					'delete_published_posts' => jet_popup()->get_admin_ui_cap(),
					'delete_others_posts' => jet_popup()->get_admin_ui_cap(),
					'edit_private_posts' => jet_popup()->get_admin_ui_cap(),
					'edit_published_posts' => jet_popup()->get_admin_ui_cap(),
					'create_posts' => jet_popup()->get_admin_ui_cap(),
				),
				'supports'            => $supports,
			);

			register_post_type( 'jet-popup', $args );

		}

		/**
		 * @return void
		 */

		public function create_new_popup_redirect() {
			// phpcs:disable WordPress.Security.NonceVerification
			if ( ! empty( $_GET['post_type'] ) && $this->post_type === $_GET['post_type'] ) {
				wp_redirect('edit.php?post_type=' . $this->post_type . '&library_action=open_create_new_popup' );// phpcs:ignore
			}
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * [print_vue_templates description]
		 * @return [type] [description]
		 */
		public function print_vue_templates() {

			$map = [
				'conditions-item',
				'conditions-manager',
				'create-popup-form',
				'import-popup-form',
				'settings-manager',
			];

			foreach ( glob( jet_popup()->plugin_path( 'templates/vue-templates/admin/popup-library/' )  . '*.php' ) as $file ) {
				$name = basename( $file, '.php' );

				if ( ! in_array( $name,  $map ) ) {
					continue;
				}

				ob_start();
				include $file;
				printf( '<script type="x-template" id="tmpl-jet-popup-library-%1$s">%2$s</script>', $name, ob_get_clean() ); // phpcs:ignore
			}

		}

		/**
		 * Print template type form HTML
		 *
		 * @return void
		 */
		public function print_template_library() {
			$screen = get_current_screen();

			if ( $screen->id !== 'edit-' . $this->slug() ) {
				return;
			}

			include jet_popup()->get_template( 'vue-templates/admin/popup-library/popup-library.php' );
		}

		/**
		 * Set blank template for editor
		 */
		public function set_post_type_template( $template ) {

			if ( is_singular( jet_popup()->post_type->slug() ) ) {
				$template = jet_popup()->plugin_path( 'templates/single.php' );

				do_action( 'jet-popups/template-include/found' );

				return apply_filters( 'jet-popups/post-type/single-template', $template );
			}

			return $template;
		}

		/**
		 * @param $popup_id
		 *
		 * @return void
		 */
		public function save_popup_post_type( $popup_id, $post, $update ) {

			if ( empty( $post ) || 'jet-popup' !== $post->post_type  ) {
				return;
			}

			delete_post_meta( $popup_id, '_is_deps_ready' );
			delete_post_meta( $popup_id, '_is_script_deps' );
			delete_post_meta( $popup_id, '_is_style_deps' );
			delete_post_meta( $popup_id, '_is_content_elements' );
			delete_transient( md5( sprintf( 'jet_popup_render_content_data_styles_%s', $popup_id ) ) );
			delete_transient( md5( sprintf( 'jet_popup_render_content_data_scripts_%s', $popup_id ) ) );

			\Jet_Cache\Manager::get_instance()->db_manager->delete_cache_by_instance_id( $popup_id, 'jet-popup' );
		}

		/**
		 * @param $popup_id
		 *
		 * @return void
		 */
		public function remove_popup_from_site_conditions( $popup_id = 0 ) {
			$conditions = get_option( jet_popup()->conditions_manager->conditions_key, [] );
			$conditions = $this->remove_popup_from_conditions_array( $popup_id, $conditions );

			update_option( jet_popup()->conditions_manager->conditions_key, $conditions, true );
		}

		/**
		 * Check if post currently presented in conditions array and remove it if yes.
		 *
		 * @param  integer $post_id    [description]
		 * @param  array   $conditions [description]
		 * @return [type]              [description]
		 */
		public function remove_popup_from_conditions_array( $popup_id = 0, $conditions = array() ) {

			foreach ( $conditions as $type => $type_conditions ) {
				if ( array_key_exists( $popup_id, $type_conditions ) ) {
					unset( $conditions[ $type ][ $popup_id ] );
				}
			}

			return $conditions;
		}

		/**
		 * @param $popup_id
		 *
		 * @return false|string
		 */
		public function popup_settings_verbose( $popup_id = null ) {

			$settings = jet_popup()->settings->get_popup_settings( $popup_id );
			$verbose = '';

			$animation_list = Jet_Popup_Utils::get_popup_animation_list();
			$open_trigger_list = Jet_Popup_Utils::get_popup_open_trigger_list();
			$сlose_event_list = Jet_Popup_Utils::get_popup_on_сlose_event();

			$animation_type = $settings['jet_popup_animation'];
			$open_trigger = $settings['jet_popup_open_trigger'];
			$сlose_event  = $settings['jet_popup_on_close_event'];
			$prevent_scrolling = filter_var( $settings['jet_popup_prevent_scrolling'], FILTER_VALIDATE_BOOLEAN );
			$show_once = filter_var( $settings['jet_popup_show_once'], FILTER_VALIDATE_BOOLEAN );
			$use_ajax = filter_var( $settings['jet_popup_use_ajax'], FILTER_VALIDATE_BOOLEAN );
			$force_ajax = filter_var( $settings['jet_popup_force_ajax'], FILTER_VALIDATE_BOOLEAN );
			$close_on_overlay = filter_var( $settings['close_on_overlay_click'], FILTER_VALIDATE_BOOLEAN );

			$verbose .= sprintf( '<div class="jet-popup-settings-list-item"><span>%1$s: </span><i class="%2$s">%3$s</i></div>',
				__( 'Open event', 'jet-popup' ),
				'attach' === $open_trigger ? 'disable-label' : 'enable-label',
				$open_trigger_list[ $open_trigger ]
			);

			switch ( $open_trigger ) {
				case 'page-load':
					$verbose .= sprintf( '<div class="jet-popup-settings-list-item sub-setting"><span>%1$s: </span><i>%2$s</i></div>',
						__( 'Open delay', 'jet-popup' ),
						$settings[ 'jet_popup_page_load_delay' ]
					);
					break;
				case 'user-inactive':
					$verbose .= sprintf( '<div class="jet-popup-settings-list-item sub-setting"><span>%1$s: </span><i>%2$s</i></div>',
						__( 'User inactivity time', 'jet-popup' ),
						$settings[ 'jet_popup_user_inactivity_time' ]
					);
					break;
				case 'scroll-trigger':
					$verbose .= sprintf( '<div class="jet-popup-settings-list-item sub-setting"><span>%1$s: </span><i>%2$s</i></div>',
						__( 'Scroll Page Progress(%)', 'jet-popup' ),
						$settings[ 'jet_popup_scrolled_to_value' ]
					);
					break;

				case 'on-date':
					$verbose .= sprintf( '<div class="jet-popup-settings-list-item sub-setting"><span>%1$s: </span><i>%2$s</i></div>',
						__( 'Open Date', 'jet-popup' ),
						$settings[ 'jet_popup_on_date_value' ]
					);
					break;
				case 'on-time':
					$verbose .= sprintf( '<div class="jet-popup-settings-list-item sub-setting"><span>%1$s: </span><i>%2$s</i></div>',
						__( 'Start Time', 'jet-popup' ),
						$settings[ 'jet_popup_on_time_start_value' ]
					);
					$verbose .= sprintf( '<div class="jet-popup-settings-list-item sub-setting"><span>%1$s: </span><i>%2$s</i></div>',
						__( 'End Time', 'jet-popup' ),
						$settings[ 'jet_popup_on_time_end_value' ]
					);
					break;
				case 'on-date-and-time':
						$verbose .= sprintf( '<div class="jet-popup-settings-list-item sub-setting"><span>%1$s: </span><i>%2$s</i></div>',
							__( 'Start of Date and Time', 'jet-popup' ),
							$settings[ 'jet_popup_start_date_and_time' ]
						);
						$verbose .= sprintf( '<div class="jet-popup-settings-list-item sub-setting"><span>%1$s: </span><i>%2$s</i></div>',
							__( 'End of Date and Time', 'jet-popup' ),
							$settings[ 'jet_popup_end_date_and_time' ]
						);
					break;
				case 'custom-selector':
					$verbose .= sprintf( '<div class="jet-popup-settings-list-item sub-setting"><span>%1$s: </span><i>%2$s</i></div>',
						__( 'Custom selector', 'jet-popup' ),
						$settings[ 'jet_popup_custom_selector' ]
					);
					break;
			}

			$verbose .= sprintf( '<div class="jet-popup-settings-list-item"><span>%1$s: </span><i class="%2$s">%3$s</i></div>',
			__( 'On close event', 'jet-popup' ),
			'none' === $сlose_event ? 'disable-label' : 'enable-label',
			$сlose_event_list[ $сlose_event ]
			);

			$verbose .= sprintf( '<div class="jet-popup-settings-list-item"><span>%1$s: </span><i>%2$s</i></div>',
				__( 'Animation type', 'jet-popup' ),
				$animation_list[ $animation_type ]
			);

			$verbose .= sprintf( '<div class="jet-popup-settings-list-item"><span>%1$s: </span><i class="%2$s">%3$s</i></div>',
				__( 'Loading content with Ajax', 'jet-popup' ),
				$use_ajax ? 'enable-label' : 'disable-label',
				$use_ajax ? __( 'Yes', 'jet-popup' ) : __( 'No', 'jet-popup' )
			);

			$verbose .= sprintf( '<div class="jet-popup-settings-list-item sub-setting"><span>%1$s: </span><i class="%2$s">%3$s</i></div>',
				__( 'Use ajax every time you open the popup', 'jet-popup' ),
				$force_ajax ? 'enable-label' : 'disable-label',
				$force_ajax ? __( 'Yes', 'jet-popup' ) : __( 'No', 'jet-popup' )
			);

			$verbose .= sprintf( '<div class="jet-popup-settings-list-item"><span>%1$s: </span><i class="%2$s">%3$s</i></div>',
				__( 'Disable page scrolling', 'jet-popup' ),
				$prevent_scrolling ? 'enable-label' : 'disable-label',
				$prevent_scrolling ? __( 'Yes', 'jet-popup' ) : __( 'No', 'jet-popup' )
			);

			$verbose .= sprintf( '<div class="jet-popup-settings-list-item"><span>%1$s: </span><i class="%2$s">%3$s</i></div>',
				__( 'Show once', 'jet-popup' ),
				$show_once ? 'enable-label' : 'disable-label',
				$show_once ? __( 'Yes', 'jet-popup' ) : __( 'No', 'jet-popup' )
			);

			return $verbose;
		}

		/**
		 * @param $popup_id
		 *
		 * @return string
		 */
		public function popup_info_verbose( $popup_id = null ) {
			$verbose = '';

			$content_type = $this->get_popup_content_type( $popup_id );
			$content_type_icons = $this->get_popup_content_type_icons();
			$icon = isset( $content_type_icons[ $content_type ] ) ? $content_type_icons[ $content_type ] : '';

			$verbose .= sprintf( '<div class="jet-popup-info-list-item"><span>%1$s: </span><span class="item-icon">%2$s</span><i>%3$s</i></div>',
				__( 'Content type', 'jet-popup' ),
				$icon,
				'default' === $content_type ? __( 'Gutenberg', 'jet-popup' ) : __( 'Elementor', 'jet-popup' )
			);

			return $verbose;
		}

		/**
		 * [predesigned_popups description]
		 * @return [type] [description]
		 */
		public function get_predesigned_popups() {

			$base_url = jet_popup()->plugin_url( 'templates/dummy-popups/' );
			$base_dir = jet_popup()->plugin_path( 'templates/dummy-popups/' );

			return apply_filters( 'jet-popup/predesigned-popups', [
				'popup-1' => [
					'id' => 'popup-1',
					'title' => __( 'Classic', 'jet-popup' ),
					'preset-default' => $base_dir . 'popup-1/preset-default.json',
					'preset-elementor' => $base_dir . 'popup-1/preset-elementor.json',
					'svg' => '<svg width="340" height="240" viewBox="0 0 340 240" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="1" y="1" width="338" height="238" rx="3" fill="white" stroke="#E2E3F3" stroke-width="2"/><path d="M1 4C1 2.34315 2.34315 1 4 1H336C337.657 1 339 2.34315 339 4V15H1V4Z" fill="white" stroke="#E2E3F3" stroke-width="2"/><circle cx="8" cy="8" r="2" fill="#E2E3F3"/><circle cx="14" cy="8" r="2" fill="#E2E3F3"/><circle cx="20" cy="8" r="2" fill="#E2E3F3"/><rect x="90" y="75" width="160" height="90" rx="2" fill="#933AFE"/><rect opacity="0.4" x="126" y="96" width="88" height="6" rx="2" fill="white"/><rect opacity="0.41" x="136" y="108" width="68" height="6" rx="2" fill="white"/><rect x="145" y="126" width="50" height="18" rx="2" fill="#1EE2A2"/><path d="M192.213 152.6L192.198 152.584L192.183 152.569L184.687 145.055L188.244 141.49C189.909 139.82 189.025 136.995 186.78 136.506L186.78 136.505L186.774 136.504L170.578 133.076C168.459 132.581 166.677 134.5 167.051 136.561L167.053 136.575L167.056 136.588L170.479 152.834L170.479 152.834L170.48 152.84C170.967 155.084 173.788 155.979 175.458 154.305L179.141 150.613L186.634 158.124C187.777 159.269 189.651 159.32 190.776 158.11L192.118 156.765C193.278 155.602 193.277 153.764 192.213 152.6Z" fill="#4F56EF" stroke="white" stroke-width="2"/><path d="M237.306 87.694C236.898 87.286 236.898 86.6244 237.306 86.2164L243.216 80.306C243.624 79.898 244.286 79.898 244.694 80.306C245.102 80.714 245.102 81.3756 244.694 81.7836L238.784 87.694C238.376 88.102 237.714 88.102 237.306 87.694Z" fill="white"/><path d="M244.694 87.694C245.102 87.286 245.102 86.6244 244.694 86.2164L238.784 80.306C238.376 79.898 237.714 79.898 237.306 80.306C236.898 80.714 236.898 81.3756 237.306 81.7836L243.216 87.694C243.624 88.102 244.286 88.102 244.694 87.694Z" fill="white"/></svg>',
				],
				'popup-2' => [
					'id' => 'popup-2',
					'title' => __( 'Slide In', 'jet-popup' ),
					'preset-default' => $base_dir . 'popup-2/preset-default.json',
					'preset-elementor' => $base_dir . 'popup-2/preset-elementor.json',
					'svg' => '<svg width="340" height="240" viewBox="0 0 340 240" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="1" y="1" width="338" height="238" rx="3" fill="white" stroke="#E2E3F3" stroke-width="2"/><path d="M1 4C1 2.34315 2.34315 1 4 1H336C337.657 1 339 2.34315 339 4V15H1V4Z" fill="white" stroke="#E2E3F3" stroke-width="2"/><circle cx="8" cy="8" r="2" fill="#E2E3F3"/><circle cx="14" cy="8" r="2" fill="#E2E3F3"/><circle cx="20" cy="8" r="2" fill="#E2E3F3"/><rect x="10" y="24" width="64" height="206" rx="2" fill="#933AFE"/><rect opacity="0.4" x="17" y="101" width="50" height="6" rx="2" fill="white"/><rect opacity="0.41" x="23" y="113" width="38.6364" height="6" rx="2" fill="white"/><rect x="17" y="131" width="50" height="18" rx="2" fill="#1EE2A2"/><path d="M63.213 161.6L63.1983 161.584L63.183 161.569L55.6867 154.055L59.244 150.49C60.9094 148.82 60.0248 145.995 57.7803 145.506L57.7803 145.505L57.7745 145.504L41.5777 142.076C39.4586 141.581 37.6769 143.5 38.0507 145.561L38.0532 145.575L38.0561 145.588L41.4786 161.834L41.4785 161.834L41.4798 161.84C41.9665 164.084 44.7879 164.979 46.4583 163.305L50.1414 159.613L57.6344 167.124C58.7766 168.269 60.6511 168.32 61.7761 167.11L63.1184 165.765C64.2781 164.602 64.277 162.764 63.213 161.6Z" fill="#4F56EF" stroke="white" stroke-width="2"/><path d="M61.306 36.694C60.898 36.286 60.898 35.6244 61.306 35.2164L67.2163 29.306C67.6244 28.898 68.2859 28.898 68.6939 29.306C69.1019 29.714 69.1019 30.3756 68.6939 30.7836L62.7836 36.694C62.3756 37.102 61.714 37.102 61.306 36.694Z" fill="white"/><path d="M68.694 36.694C69.102 36.286 69.102 35.6244 68.694 35.2164L62.7837 29.306C62.3756 28.898 61.7141 28.898 61.3061 29.306C60.8981 29.714 60.8981 30.3756 61.3061 30.7836L67.2164 36.694C67.6244 37.102 68.286 37.102 68.694 36.694Z" fill="white"/></svg>',
				],
				'popup-3' => [
					'id' => 'popup-3',
					'title' => __( 'Bar', 'jet-popup' ),
					'preset-default' => $base_dir . 'popup-3/preset-default.json',
					'preset-elementor' => $base_dir . 'popup-3/preset-elementor.json',
					'svg' => '<svg width="340" height="240" viewBox="0 0 340 240" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="1" y="1" width="338" height="238" rx="3" fill="white" stroke="#E2E3F3" stroke-width="2"/><path d="M4 1H336C337.657 1 339 2.34315 339 4V15H1V4C1 2.34315 2.34315 1 4 1Z" fill="white" stroke="#E2E3F3" stroke-width="2"/><circle cx="8" cy="8" r="2" fill="#E2E3F3"/><circle cx="14" cy="8" r="2" fill="#E2E3F3"/><circle cx="20" cy="8" r="2" fill="#E2E3F3"/><rect x="10" y="24" width="320" height="40" rx="2" fill="#933AFE"/><rect opacity="0.4" x="93" y="35" width="88" height="6" rx="2" fill="white"/><rect opacity="0.41" x="93" y="47" width="68" height="6" rx="2" fill="white"/><rect x="196" y="35" width="50" height="18" rx="2" fill="#1EE2A2"/><path d="M242.213 65.6003L242.198 65.5843L242.183 65.5689L234.687 58.0553L238.244 54.4898C239.909 52.8205 239.025 49.9945 236.78 49.5055L236.78 49.5055L236.774 49.5042L220.578 46.0763C218.459 45.5806 216.677 47.5003 217.051 49.5608L217.053 49.5746L217.056 49.5884L220.479 65.8341L220.479 65.8341L220.48 65.8399C220.967 68.084 223.788 68.9793 225.458 67.3051L229.141 63.6134L236.634 71.1238C237.777 72.2686 239.651 72.32 240.776 71.11L242.118 69.7646C243.278 68.6022 243.277 66.7637 242.213 65.6003Z" fill="#4F56EF" stroke="white" stroke-width="2"/><path d="M317.306 36.694C316.898 36.286 316.898 35.6244 317.306 35.2164L323.216 29.306C323.624 28.898 324.286 28.898 324.694 29.306C325.102 29.714 325.102 30.3756 324.694 30.7836L318.784 36.694C318.376 37.102 317.714 37.102 317.306 36.694Z" fill="white"/><path d="M324.694 36.694C325.102 36.286 325.102 35.6244 324.694 35.2164L318.784 29.306C318.376 28.898 317.714 28.898 317.306 29.306C316.898 29.714 316.898 30.3756 317.306 30.7836L323.216 36.694C323.624 37.102 324.286 37.102 324.694 36.694Z" fill="white"/></svg>',
				],
				'popup-4' => [
					'id' => 'popup-4',
					'title' => __( 'Bordering', 'jet-popup' ),
					'preset-default' => $base_dir . 'popup-4/preset-default.json',
					'preset-elementor' => $base_dir . 'popup-4/preset-elementor.json',
					'svg' => '<svg width="340" height="240" viewBox="0 0 340 240" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="1" y="1" width="338" height="238" rx="3" fill="white" stroke="#E2E3F3" stroke-width="2"/><path d="M1 4C1 2.34315 2.34315 1 4 1H336C337.657 1 339 2.34315 339 4V15H1V4Z" fill="white" stroke="#E2E3F3" stroke-width="2"/><circle cx="8" cy="8" r="2" fill="#E2E3F3"/><circle cx="14" cy="8" r="2" fill="#E2E3F3"/><circle cx="20" cy="8" r="2" fill="#E2E3F3"/><rect x="154" y="200" width="176" height="30" rx="2" fill="#933AFE"/><rect opacity="0.4" x="160" y="206" width="88" height="6" rx="2" fill="white"/><rect opacity="0.4" x="160" y="218" width="63" height="6" rx="2" fill="white"/><rect x="259" y="206" width="50" height="18" rx="2" fill="#1EE2A2"/><path d="M306.213 232.6L306.198 232.584L306.183 232.569L298.687 225.055L302.244 221.49C303.909 219.82 303.025 216.995 300.78 216.506L300.78 216.505L300.774 216.504L284.578 213.076C282.459 212.581 280.677 214.5 281.051 216.561L281.053 216.575L281.056 216.588L284.479 232.834L284.479 232.834L284.48 232.84C284.967 235.084 287.788 235.979 289.458 234.305L293.141 230.613L300.634 238.124C301.777 239.269 303.651 239.32 304.776 238.11L306.118 236.765C307.278 235.602 307.277 233.764 306.213 232.6Z" fill="#4F56EF" stroke="white" stroke-width="2"/><path d="M317.306 212.694C316.898 212.286 316.898 211.624 317.306 211.216L323.216 205.306C323.624 204.898 324.286 204.898 324.694 205.306C325.102 205.714 325.102 206.376 324.694 206.784L318.784 212.694C318.376 213.102 317.714 213.102 317.306 212.694Z" fill="white"/><path d="M324.694 212.694C325.102 212.286 325.102 211.624 324.694 211.216L318.784 205.306C318.376 204.898 317.714 204.898 317.306 205.306C316.898 205.714 316.898 206.376 317.306 206.784L323.216 212.694C323.624 213.102 324.286 213.102 324.694 212.694Z" fill="white"/></svg>',
				],
				'popup-5' => [
					'id' => 'popup-5',
					'title' => __( 'Full View', 'jet-popup' ),
					'preset-default' => $base_dir . 'popup-5/preset-default.json',
					'preset-elementor' => $base_dir . 'popup-5/preset-elementor.json',
					'svg' => '<svg width="340" height="240" viewBox="0 0 340 240" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="1" y="1" width="338" height="238" rx="3" fill="white" stroke="#E2E3F3" stroke-width="2"/><path d="M1 4C1 2.34315 2.34315 1 4 1H336C337.657 1 339 2.34315 339 4V15H1V4Z" fill="white" stroke="#E2E3F3" stroke-width="2"/><circle cx="8" cy="8" r="2" fill="#E2E3F3"/><circle cx="14" cy="8" r="2" fill="#E2E3F3"/><circle cx="20" cy="8" r="2" fill="#E2E3F3"/><rect x="10" y="24" width="320" height="206" rx="2" fill="#933AFE"/><rect opacity="0.4" x="126" y="96" width="88" height="6" rx="2" fill="white"/><rect opacity="0.41" x="136" y="108" width="68" height="6" rx="2" fill="white"/><rect x="145" y="126" width="50" height="18" rx="2" fill="#1EE2A2"/><path d="M192.213 152.6L192.198 152.584L192.183 152.569L184.687 145.055L188.244 141.49C189.909 139.82 189.025 136.995 186.78 136.506L186.78 136.505L186.774 136.504L170.578 133.076C168.459 132.581 166.677 134.5 167.051 136.561L167.053 136.575L167.056 136.588L170.479 152.834L170.479 152.834L170.48 152.84C170.967 155.084 173.788 155.979 175.458 154.305L179.141 150.613L186.634 158.124C187.777 159.269 189.651 159.32 190.776 158.11L192.118 156.765C193.278 155.602 193.277 153.764 192.213 152.6Z" fill="#4F56EF" stroke="white" stroke-width="2"/><path d="M317.306 36.694C316.898 36.286 316.898 35.6244 317.306 35.2164L323.216 29.306C323.624 28.898 324.286 28.898 324.694 29.306C325.102 29.714 325.102 30.3756 324.694 30.7836L318.784 36.694C318.376 37.102 317.714 37.102 317.306 36.694Z" fill="white"/><path d="M324.694 36.694C325.102 36.286 325.102 35.6244 324.694 35.2164L318.784 29.306C318.376 28.898 317.714 28.898 317.306 29.306C316.898 29.714 316.898 30.3756 317.306 30.7836L323.216 36.694C323.624 37.102 324.286 37.102 324.694 36.694Z" fill="white"/></svg>',
				],
				'popup-6' => [
					'id' => 'popup-6',
					'title' => __( 'Full Width', 'jet-popup' ),
					'preset-default' => $base_dir . 'popup-6/preset-default.json',
					'preset-elementor' => $base_dir . 'popup-6/preset-elementor.json',
					'svg' => '<svg width="340" height="240" viewBox="0 0 340 240" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="1" y="1" width="338" height="238" rx="3" fill="white" stroke="#E2E3F3" stroke-width="2"/><path d="M1 4C1 2.34315 2.34315 1 4 1H336C337.657 1 339 2.34315 339 4V15H1V4Z" fill="white" stroke="#E2E3F3" stroke-width="2"/><circle cx="8" cy="8" r="2" fill="#E2E3F3"/><circle cx="14" cy="8" r="2" fill="#E2E3F3"/><circle cx="20" cy="8" r="2" fill="#E2E3F3"/><rect x="10" y="74" width="320" height="88" rx="2" fill="#933AFE"/><rect opacity="0.4" x="126" y="96" width="88" height="6" rx="2" fill="white"/><rect opacity="0.41" x="136" y="108" width="68" height="6" rx="2" fill="white"/><rect x="145" y="126" width="50" height="18" rx="2" fill="#1EE2A2"/><path d="M192.213 152.6L192.198 152.584L192.183 152.569L184.687 145.055L188.244 141.49C189.909 139.82 189.025 136.995 186.78 136.506L186.78 136.505L186.774 136.504L170.578 133.076C168.459 132.581 166.677 134.5 167.051 136.561L167.053 136.575L167.056 136.588L170.479 152.834L170.479 152.834L170.48 152.84C170.967 155.084 173.788 155.979 175.458 154.305L179.141 150.613L186.634 158.124C187.777 159.269 189.651 159.32 190.776 158.11L192.118 156.765C193.278 155.602 193.277 153.764 192.213 152.6Z" fill="#4F56EF" stroke="white" stroke-width="2"/><path d="M317.306 86.694C316.898 86.286 316.898 85.6244 317.306 85.2164L323.216 79.306C323.624 78.898 324.286 78.898 324.694 79.306C325.102 79.714 325.102 80.3756 324.694 80.7836L318.784 86.694C318.376 87.102 317.714 87.102 317.306 86.694Z" fill="white"/><path d="M324.694 86.694C325.102 86.286 325.102 85.6244 324.694 85.2164L318.784 79.306C318.376 78.898 317.714 78.898 317.306 79.306C316.898 79.714 316.898 80.3756 317.306 80.7836L323.216 86.694C323.624 87.102 324.286 87.102 324.694 86.694Z" fill="white"/></svg>',
				],
			] );
		}

		/**
		 * @param false $template_type
		 * @param string $content_type
		 * @param string $template_name
		 *
		 * @return array
		 */
		public function create_popup( $preset = false, $content_type = 'default', $name = '' ) {

			if ( ! current_user_can( 'edit_posts' ) ) {
				return [
					'type'          => 'error',
					'message'       => __( 'You don\'t have permissions to do this', 'jet-popup' ),
					'redirect'      => false,
					'newTemplateId' => false,
				];
			}

			$popup_default_settings = jet_popup()->settings->get_popup_default_settings();
			$popup_default_styles   = jet_popup()->settings->get_popup_styles();

			if ( ! $preset ) {

				switch ( $content_type ) {
					case 'default':
						$meta_input = [
							'_content_type'  => $content_type,
							'_conditions'    => [],
							'_relation_type' => 'or',
							'_settings'      => $popup_default_settings,
							'_styles'        => $popup_default_styles,
							'_elementor_template_type' => $this->slug(), // Elementor Compatibility / add doc type meta field
						];
						break;
					case 'elementor':
						$documents = \Elementor\Plugin::instance()->documents;
						$doc_type  = $documents->get_document_type( $this->slug() );
						$preset_data = false;

						if ( ! $doc_type ) {
							return [
								'type'          => 'error',
								'message'       => __( 'Incorrect type', 'jet-popup' ),
								'redirect'      => false,
								'newTemplateId' => false,
							];
						}

						$meta_input = [
							'_elementor_edit_mode'   => 'builder',
							$doc_type::TYPE_META_KEY => $this->slug(),
							'_content_type'          => $content_type,
							'_conditions'            => [],
							'_relation_type'         => 'or',
							'_settings'              => [],
							'_styles'                => $popup_default_styles,
						];

						break;
				}

				$new_popup_args = [
					'post_status' => 'publish',
					'post_title'  => ! empty( $name ) ? $name : __( 'Jet Popup', 'jet-popup' ),
					'post_type'   => $this->slug(),
					'meta_input'  => $meta_input,
				];

			} else {
				$preset_data = $this->get_preset_data( $preset, $content_type );
				$new_popup_args = jet_popup()->export_import->get_new_popup_args( $preset_data );

				if ( ! empty( $name ) ) {
					$new_popup_args['post_title'] = $name;
				}
			}

			$popup_id = wp_insert_post( $new_popup_args, true );

			if ( empty( $name ) ) {
				$post_title = $new_popup_args['post_title'] . ' #' . $popup_id;

				wp_update_post( [
					'ID'         => $popup_id,
					'post_title' => $post_title,
				] );
			}

			if ( $popup_id ) {

				do_action ( 'jet-popup/create-popup/created', $popup_id, $this );

				switch ( $content_type ) {
					case 'default':
						$redirect = get_edit_post_link( $popup_id, '' );
						break;
					case 'elementor':
						$redirect = \Elementor\Plugin::$instance->documents->get( $popup_id )->get_edit_url();
						break;
				}

				return [
					'type'       => 'success',
					'message'    => __( 'Popup has been created', 'jet-popup' ),
					'redirect'   => $redirect,
					'newPopupId' => $popup_id,
				];
			} else {
				return [
					'type'          => 'error',
					'message'       => __( 'Server Error. Please try again later.', 'jet-popup' ),
					'redirect'      => false,
					'newTemplateId' => false,
				];
			}
		}

		/**
		 * @param $preset
		 *
		 * @return array|false
		 */
		public function get_preset_data( $preset = false, $content_type = 'default' ) {

			if ( ! $preset ) {
				return false;
			}

			$predesigned_popups = $this->get_predesigned_popups();

			if ( ! isset( $predesigned_popups[ $preset ] ) ) {
				return false;
			}

			$data = $predesigned_popups[ $preset ];

			$dummy_file = '';

			switch ( $content_type ) {
				case 'default':
					$dummy_file = $data['preset-default'];
					break;
				case 'elementor':
					$dummy_file = $data['preset-elementor'];
					break;
			}

			if ( empty( $dummy_file ) ) {
				return false;
			}

			ob_start();
			include $dummy_file;
			$preset_data = ob_get_clean();

			$preset_data = json_decode( $preset_data, true );

			return $preset_data;
		}

		/**
		 * @param false $page_template_id
		 *
		 * @return array|mixed
		 */
		public function get_popup_relation_type( $popup_id = false ) {

			if ( ! $popup_id ) {
				return 'or';
			}

			$relation_type = get_post_meta( $popup_id, '_relation_type', true );

			return ! empty( $relation_type ) ? $relation_type : 'or';
		}

		/**
		 * @param false $page_template_id
		 *
		 * @return array|mixed
		 */
		public function get_popup_content_type( $popup_id = false ) {

			if ( ! $popup_id ) {
				return 'default';
			}

			$content_type = get_post_meta( $popup_id, '_content_type', true );

			return ! empty( $content_type ) ? $content_type : 'elementor';
		}

		/**
		 * @return mixed|void
		 */
		public function get_popup_content_type_options() {
			return apply_filters( 'jet-popup/post-type/content-type-options', [
				[
					'label' => __( 'Block Editor', 'jet-popup' ),
					'value' => 'default',
				],
			] );
		}

		/**
		 * @return mixed|void
		 */
		public function get_popup_default_content_type() {
			return apply_filters( 'jet-popup/post-type/default-content-type', 'default' );
		}

		/**
		 * @return mixed|void
		 */
		public function get_popup_content_type_icons() {
			return apply_filters( 'jet-popup/post-type/content-type-icons', [
				'default' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M24 12C24 18.6274 18.6274 24 12 24C5.37258 24 0 18.6274 0 12C0 5.37258 5.37258 0 12 0C18.6274 0 24 5.37258 24 12ZM13.1079 0.927195L23.0728 10.8921C22.5528 5.63123 18.3688 1.44724 13.1079 0.927195ZM23.0728 13.1079C22.5528 18.3688 18.3688 22.5528 13.1079 23.0728L23.0728 13.1079ZM11.1313 23.0939L0.906137 12.8687C1.3272 18.3215 5.67854 22.6728 11.1313 23.0939ZM0.906137 11.1313C1.3272 5.67854 5.67854 1.3272 11.1313 0.906137L0.906137 11.1313ZM17.0621 10.5758C16.8846 10.4529 16.6433 10.5036 16.5226 10.6843C15.8199 11.7614 14.3294 11.8192 14.2513 11.8192H14.2158C12.3775 11.8192 11.6748 13.4168 11.6464 13.4819C11.5612 13.6843 11.6535 13.9156 11.8451 14.0023C11.8948 14.024 11.9516 14.0385 12.0013 14.0385C12.1503 14.0385 12.2923 13.9517 12.3562 13.7999L12.3571 13.798C12.3817 13.7446 12.8692 12.6853 14.088 12.6144V14.6602C14.0384 15.1011 13.8325 15.4481 13.4705 15.7084C13.0943 15.9758 12.5904 16.1132 11.9729 16.1132C11.2347 16.1132 10.6314 15.8529 10.1842 15.3397C9.72993 14.8264 9.5028 14.0963 9.5028 13.1566L9.5099 10.9011C9.54539 10.0698 9.76542 9.41198 10.1842 8.9421C10.6385 8.42885 11.2347 8.16861 11.9729 8.16861C12.5904 8.16861 13.0943 8.30596 13.4705 8.57343C13.8467 8.8409 14.0597 9.20957 14.0951 9.68668V9.73729C14.0951 10.012 14.3152 10.2361 14.5849 10.2361C14.8546 10.2361 15.0747 10.012 15.0747 9.73729V9.68668C15.0037 8.97102 14.6843 8.40717 14.1093 7.98066C13.5344 7.55415 12.8175 7.34451 11.9516 7.34451C10.9224 7.34451 10.0919 7.6915 9.46021 8.37825C8.86399 9.02162 8.55168 9.86741 8.51619 10.9084C8.51619 10.9445 8.51442 10.9806 8.51264 11.0168L8.51264 11.0168C8.51087 11.0529 8.50909 11.0891 8.50909 11.1252L8.51619 13.1566H8.50909C8.50909 14.306 8.8285 15.224 9.46021 15.9108C10.0919 16.5975 10.9224 16.9445 11.9516 16.9445C12.8175 16.9445 13.5344 16.7349 14.1093 16.3084C14.6346 15.918 14.9469 15.4048 15.0534 14.7686L15.0747 12.4987C15.7206 12.3397 16.6007 11.9783 17.1543 11.1252C17.2963 10.9445 17.2466 10.6987 17.0621 10.5758ZM12.1091 22.8374L1.27166 12L12.1091 1.16257L22.9465 12L12.1091 22.8374Z" fill="#23282D"/></svg>',
			] );
		}

		/**
		 * @param $classes
		 * @return mixed
		 */
		public function body_status_classes( $classes ) {
			global $pagenow;

			if ( in_array( $pagenow, [ 'post.php', 'post-new.php' ], true ) ) {
				$post = get_post();
				$popup_id = $post->ID;
				$post_type = get_post_type( $popup_id );

				if ( 'jet-popup' === $post_type ) {
					$content_type = jet_popup()->post_type->get_popup_content_type( $popup_id );
					$classes .= ' ' . 'post-type-jet-popup--' . $content_type . '-content-type';
				}
			}

			return $classes;
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