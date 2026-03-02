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

if ( ! class_exists( 'Jet_Popup_Utils' ) ) {

	/**
	 * Define Jet_Popup_Utils class
	 */
	class Jet_Popup_Utils {

		/**
		 * @return bool
		 */
		public static function has_elementor() {
			return defined( 'ELEMENTOR_VERSION' );
		}

		/**
		 * [get_plugin_license description]
		 * @return [type] [description]
		 */
		public static function get_plugin_license() {
			$license_key = \Jet_Dashboard\Utils::get_plugin_license_key( 'jet-popup/jet-popup.php' );

			if ( ! empty( $license_key ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Get post types options list
		 *
		 * @return array
		 */
		public static function get_post_types() {
			$post_types = get_post_types( array( 'public' => true ), 'objects' );

			$deprecated = apply_filters(
				'jet-popup/post-types-list/deprecated',
				array(
					'page',
					'jet-woo-builder',
					'e-landing-page',
					'jet-form-builder',
					'jet-engine',
					'jet-menu',
					'attachment',
					'elementor_library',
					'jet-theme-core',
					jet_popup()->post_type->slug(),
				)
			);

			$result = array();

			if ( empty( $post_types ) ) {
				return $result;
			}

			foreach ( $post_types as $slug => $post_type ) {

				if ( in_array( $slug, $deprecated ) ) {
					continue;
				}

				$result[ $slug ] = $post_type->label;

			}

			return $result;

		}

		/**
		 * Get post types options list
		 *
		 * @return array
		 */
		public static function get_post_types_options() {

			$post_types = self::get_post_types();

			$result = [];

			if ( empty( $post_types ) ) {
				return $result;
			}

			foreach ( $post_types as $slug => $label ) {
				$result[] = [
					'label' => $label,
					'value' => $slug,
				];
			}

			return $result;

		}

		/**
		 * Get cherry popups query
		 *
		 * @since 1.0.0
		 * @return object
		 */
		public static function get_avaliable_popups() {

			$avaliable_popups = [];

			$avaliable_popups = [
				'' => esc_html__( 'Not Selected', 'jet-popup' ),
			];

			$query_args = apply_filters( 'jet_popup_default_query_args',
				[
					'post_type'      => jet_popup()->post_type->slug(),
					'order'          => 'DESC',
					'orderby'        => 'date',
					'posts_per_page' => -1,
					'post_status'    => 'publish',
				]
			);

			$popups_query = new WP_Query( $query_args );

			if ( is_wp_error( $popups_query ) ) {
				return false;
			}

			if ( $popups_query->have_posts() ) {

				foreach ( $popups_query->posts as $popup ) {

					$post_id = $popup->ID;
					$post_title = $popup->post_title;
					$avaliable_popups[ $post_id ] = $post_title;
				}
			} else {
				return false;
			}

			return $avaliable_popups;
		}

		/**
		 * [get_avaliable_popups description]
		 * @return [type] [description]
		 */
		public static function get_roles_list() {

			if ( ! function_exists( 'get_editable_roles' ) ) {
				require_once ABSPATH . 'wp-admin/includes/user.php';
			}

			$roles['guest'] = esc_html__( 'Guest', 'jet-popup' );

			foreach ( get_editable_roles() as $role_slug => $role_data ) {
				$roles[ $role_slug ] = $role_data['name'];
			}

			return $roles;
		}

		/**
		 * [is_avaliable_for_user description]
		 * @param  [type]  $popup_roles [description]
		 * @return boolean              [description]
		 */
		public static function is_avaliable_for_user( $roles ) {

			if ( empty( $roles ) ) {
				return true;
			}

			$user     = wp_get_current_user();
			$is_guest = empty( $user->roles ) ? true : false;

			if ( ! $is_guest ) {
				$user_role = $user->roles[0];
			} else {
				$user_role = 'guest';
			}

			if ( in_array( $user_role, $roles ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Returns all custom taxonomies
		 *
		 * @return [type] [description]
		 */
		public static function get_taxonomies() {

			$taxonomies = get_taxonomies( array(
				'public'   => true,
				'_builtin' => false
			), 'objects' );

			$deprecated = apply_filters(
				'jet-popup/taxonomies-list/deprecated',
				array()
			);

			$result = array();

			if ( empty( $taxonomies ) ) {
				return $result;
			}

			foreach ( $taxonomies as $slug => $tax ) {

				if ( in_array( $slug, $deprecated ) ) {
					continue;
				}

				$result[ $slug ] = $tax->label;

			}

			return $result;

		}

		/**
		 * Get post types options list
		 *
		 * @return array
		 */
		public static function get_taxonomies_options() {

			$taxonomies = self::get_taxonomies();

			$result = [];

			if ( empty( $taxonomies ) ) {
				return $result;
			}

			foreach ( $taxonomies as $slug => $label ) {
				$result[] = [
					'label' => $label,
					'value' => $slug,
				];
			}

			return $result;

		}

		/**
		 * @param false $post_type
		 *
		 * @return array
		 */
		public static function get_taxonomies_by_post_type( $post_type = false ) {
			$taxonomies = get_object_taxonomies( $post_type, 'objects' );

			$post_type_taxonomies = wp_filter_object_list( $taxonomies, [
				'public'            => true,
				'show_in_nav_menus' => true,
			] );

			return $post_type_taxonomies;
		}

		/**
		 * Force query to look in post title while searching
		 * @return [type] [description]
		 */
		public static function force_search_by_title( $where, $query ) {

			$args = $query->query;

			if ( ! isset( $args['s_title'] ) ) {
				return $where;
			} else {
				global $wpdb;

				$searh = esc_sql( $wpdb->esc_like( $args['s_title'] ) );
				$where .= " AND {$wpdb->posts}.post_title LIKE '%$searh%'";

			}

			return $where;
		}

		/**
		 * [search_posts_by_type description]
		 * @param  [type] $type  [description]
		 * @param  [type] $query [description]
		 * @return [type]        [description]
		 */
		public static function get_posts_by_type( $type, $query = '', $ids = [], $include_all = true ) {

			add_filter( 'posts_where', [ __CLASS__, 'force_search_by_title' ], 10, 2 );

			$posts = get_posts( [
				'post_type'           => $type,
				'ignore_sticky_posts' => true,
				'posts_per_page'      => -1,
				'suppress_filters'     => false,
				's_title'             => $query,
				'post_status'         => [ 'publish', 'private' ],
				'include'             => $ids,
			] );

			remove_filter( 'posts_where', array( __CLASS__, 'force_search_by_title' ), 10, 2 );

			$result = [];

			if ( ( empty( $ids ) || in_array( 'all', $ids ) ) && $include_all ) {
				$result[] = [
					'value' => 'all',
					'label' => __( 'All', 'jet-popup' ),
				];
			}

			if ( ! empty( $posts ) ) {
				foreach ( $posts as $post ) {
					$result[] = [
						'value' => $post->ID,
						'label' => $post->post_title,
					];
				}
			}

			return $result;
		}

		/**
		 * [search_terms_by_tax description]
		 * @param  [type] $tax   [description]
		 * @param  [type] $query [description]
		 * @return [type]        [description]
		 */
		public static function get_terms_by_tax( $tax, $query = '', $ids = [] ) {

			$terms = get_terms( [
				'taxonomy'   => $tax,
				'hide_empty' => false,
				'name__like' => $query,
				'include'    => $ids,
			] );

			$result = [];

			if ( empty( $ids ) || in_array( 'all', $ids ) ) {
				$result[] = [
					'value' => 'all',
					'label' => __( 'All', 'jet-popup' ),
				];
			}

			if ( ! empty( $terms ) ) {
				foreach ( $terms as $term ) {
					$result[] = [
						'value' => $term->term_id,
						'label' => $term->name,
					];
				}
			}

			return $result;

		}

		/**
		 * @param $tax
		 * @param string $query
		 * @param array $ids
		 *
		 * @return array
		 */
		public static function get_terms_options_by_taxonomy( $tax, $query = '', $ids = [] ) {

			$terms = get_terms( [
				'taxonomy'   => $tax,
				'hide_empty' => false,
				'name__like' => $query,
				'include'    => $ids,
			] );

			$options = [];

			if ( empty( $ids ) || in_array( 'all', $ids ) ) {
				$options[] = [
					'value' => 'all',
					'label' => __( 'All', 'jet-popup' ),
				];
			}

			if ( ! empty( $terms ) ) {
				foreach ( $terms as $term ) {
					$options[] = [
						'label' => $term->name,
						'value' => $term->term_id,
					];
				}
			}

			return $options;

		}

		/**
		 * [get_avaliable_mailchimp_list description]
		 */
		public static function get_avaliable_mailchimp_list() {
			$mailchimp_data = get_option( 'jet-popup-settings_mailchimp', [] );

			$apikey = jet_popup()->settings->get( 'apikey', '' );

			if ( empty( $mailchimp_data ) ) {
				return false;
			}

			if ( empty( $apikey ) ) {
				return false;
			}

			if ( ! array_key_exists( $apikey , $mailchimp_data ) ) {
				return false;
			}

			$mailchimp_account = $mailchimp_data[ $apikey ];

			if ( ! array_key_exists( 'lists' , $mailchimp_account ) ) {
				return false;
			}

			$lists = $mailchimp_account['lists'];

			$avaliable_lists = [];

			foreach ( $lists as $key => $data ) {
				$info = $data['info'];
				$avaliable_lists[ $info['id'] ] = $info['name'];
			}

			if ( ! empty( $avaliable_lists ) ) {
				return $avaliable_lists;
			}

			return false;

		}

		/**
		 * [get_avaliable_mailchimp_merge_fields description]
		 * @param  [type] $list_id [description]
		 * @return [type]          [description]
		 */
		public static function get_avaliable_mailchimp_merge_fields( $list_id ) {
			$mailchimp_data = get_option( 'jet-popup-settings_mailchimp', [] );

			$apikey = jet_popup()->settings->get( 'apikey', '' );

			if ( empty( $mailchimp_data ) ) {
				return false;
			}

			if ( empty( $apikey ) ) {
				return false;
			}

			if ( ! array_key_exists( $apikey , $mailchimp_data ) ) {
				return false;
			}

			$mailchimp_account = $mailchimp_data[ $apikey ];

			if ( ! array_key_exists( 'lists', $mailchimp_account ) ) {
				return false;
			}

			$lists = $mailchimp_account['lists'];

			if ( ! array_key_exists( $list_id, $lists ) ){
				return false;
			}

			$list = $lists[ $list_id ];

			if ( ! array_key_exists( 'merge_fields', $list ) ) {
				return false;
			}

			return $list['merge_fields'];
		}

		/**
		 * Print HTML icon template
		 *
		 * @param  array  $setting
		 * @param  string $format
		 * @param  string $icon_class
		 * @param  bool   $echo
		 *
		 * @return void|string
		 */
		public static function __render_icon( $settings = null, $setting = null, $format = '%s', $icon_class = '', $echo = false ) {
			$new_setting = 'selected_' . $setting;

			$migrated = isset( $settings[ $new_setting ] );
			$is_new   = empty( $settings[ $setting ] ) && class_exists( 'Elementor\Icons_Manager' ) && Elementor\Icons_Manager::is_migration_allowed();

			$icon_html = '';

			if ( $is_new || $migrated ) {

				$attr = array( 'aria-hidden' => 'true' );

				if ( ! empty( $icon_class ) ) {
					$attr['class'] = $icon_class;
				}

				if ( isset( $settings[ $new_setting ] ) ) {
					ob_start();
					Elementor\Icons_Manager::render_icon( $settings[ $new_setting ], $attr );

					$icon_html = ob_get_clean();
				}

			} else if ( ! empty( $settings[ $setting ] ) ) {

				if ( empty( $icon_class ) ) {
					$icon_class = $settings[ $setting ];
				} else {
					$icon_class .= ' ' . $settings[ $setting ];
				}

				$icon_html = sprintf( '<i class="%s" aria-hidden="true"></i>', $icon_class );
			}

			if ( empty( $icon_html ) ) {
				return false;
			}

			if ( ! $echo ) {
				return sprintf( $format, $icon_html );
			}

			printf( $format, $icon_html ); // phpcs:ignore
		}

		/**
		 * @param string $svg_id
		 * @param bool $wrapper
		 *
		 * @return string
		 */
		public static function get_svg_icon_html( $svg_id = '', $default = '', $attr = array(), $wrapper = true ) {

			if ( empty( $svg_id ) ) {
				return $default;
			}

			$url = wp_get_attachment_url( $svg_id );

			if ( ! $url ) {
				return $default;
			}

			return self::get_image_by_url( $url, $attr, $wrapper );
		}

		/**
		 * Rturns image tag or raw SVG
		 *
		 * @param string $url image URL.
		 * @param array $attr [description]
		 *
		 * @return string
		 */
		public static function get_image_by_url( $url = null, $attr = array (), $wrapper = true ) {

			$url = esc_url( $url );

			if ( empty( $url ) ) {
				return;
			}

			$ext  = pathinfo( $url, PATHINFO_EXTENSION );
			$attr = array_merge( array ( 'alt' => '' ), $attr );

			if ( 'svg' !== $ext ) {
				return sprintf( '<img src="%1$s"%2$s>', $url, self::get_attr_string( $attr ) );
			}

			$base_url = site_url( '/' );
			$svg_path = str_replace( $base_url, ABSPATH, $url );
			$key      = md5( $svg_path );
			$svg      = get_transient( $key );

			if ( ! $svg ) {
				$svg = file_get_contents( $svg_path );
			}

			if ( ! $svg ) {
				return sprintf( '<img src="%1$s"%2$s>', $url, self::get_attr_string( $attr ) );
			}

			set_transient( $key, $svg, DAY_IN_SECONDS );

			if ( ! $wrapper ) {
				return $svg;
			}

			unset( $attr[ 'alt' ] );

			return sprintf( '<div%2$s>%1$s</div>', $svg, self::get_attr_string( $attr ) );
		}

		/**
		 * Return attributes string from attributes array.
		 *
		 * @param array $attr Attributes string.
		 *
		 * @return string
		 */
		public static function get_attr_string( $attr = array () ) {

			if ( empty( $attr ) || ! is_array( $attr ) ) {
				return '';
			}

			$result = '';

			foreach ( $attr as $key => $value ) {

				if ( is_array( $value ) ) {
					$value = implode( ' ', $value );
				}

				$result .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $value ) );
			}

			return $result;
		}

		/**
		 * @param string $icon
		 * @param array $classes
		 *
		 * @return array|string|string[]|null
		 */
		public static function get_default_svg_html( $icon = '', $classes = [] ) {
			$icons = apply_filters( 'jet-popup/default-svg-list', [
				'close' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12.2218 13.6066L20 21.3848L21.4142 19.9706L13.636 12.1924L21.3848 4.44366L19.9706 3.02945L12.2218 10.7782L4.44365 3L3.02944 4.41421L10.8076 12.1924L3 20L4.41421 21.4142L12.2218 13.6066Z" fill="currentColor"></path></svg>',
			] );

			$classes   = (array) $classes;
			$classes[] = 'svg-icon';

			if ( array_key_exists( $icon, $icons ) ) {
				$repl = sprintf( '<svg class="%s" aria-hidden="true" role="img" focusable="false" ', join( ' ', $classes ) );
				$svg  = preg_replace( '/^<svg /', $repl, trim( $icons[ $icon ] ) ); // Add extra attributes to SVG code.
				$svg  = preg_replace( "/([\n\t]+)/", ' ', $svg ); // Remove newlines & tabs.
				$svg  = preg_replace( '/>\s*</', '><', $svg ); // Remove white space between SVG tags.

				return $svg;
			}

			return false;
		}

		/**
		 * [get_milliseconds_by_tag description]
		 * @param  string $tag [description]
		 * @return [type]      [description]
		 */
		public static function get_milliseconds_by_tag( $tag = 'none' ) {

			if ( 'none' === $tag ) {
				return 'none';
			}

			switch ( $tag ) {

				case 'minute':
					$delay = MINUTE_IN_SECONDS * 1000;
					break;

				case '10minutes':
					$delay = 10 * MINUTE_IN_SECONDS * 1000;
					break;

				case '30minutes':
					$delay = 30 * MINUTE_IN_SECONDS * 1000;
					break;

				case 'hour':
					$delay = HOUR_IN_SECONDS * 1000;
					break;

				case '3hours':
					$delay = 3 * HOUR_IN_SECONDS * 1000;
					break;

				case '6hours':
					$delay = 6 * HOUR_IN_SECONDS * 1000;
					break;

				case '12hours':
					$delay = 12 * HOUR_IN_SECONDS * 1000;
					break;

				case 'day':
					$delay = DAY_IN_SECONDS * 1000;
					break;

				case '3days':
					$delay = 3 * DAY_IN_SECONDS * 1000;
					break;

				case 'week':
					$delay = WEEK_IN_SECONDS * 1000;
					break;

				case 'month':
					$delay = MONTH_IN_SECONDS * 1000;
					break;

				default:
					$delay = 'none';
					break;
			}

			return $delay;
		}

		/**
		 * @param false $icon
		 *
		 * @return false|string
		 */
		public static function get_admin_ui_icon( $icon = false ) {

			if ( ! $icon ) {
				return false;
			}

			$ui_icons = [
				'warning' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.47 21H19.53C21.07 21 22.03 19.33 21.26 18L13.73 4.99C12.96 3.66 11.04 3.66 10.27 4.99L2.74 18C1.97 19.33 2.93 21 4.47 21ZM12 14C11.45 14 11 13.55 11 13V11C11 10.45 11.45 10 12 10C12.55 10 13 10.45 13 11V13C13 13.55 12.55 14 12 14ZM13 18H11V16H13V18Z" fill="#fcb92c"/></svg>',
				'edit'    => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2.5 14.3751V17.5001H5.625L14.8417 8.28346L11.7167 5.15846L2.5 14.3751ZM17.2583 5.8668C17.5833 5.5418 17.5833 5.0168 17.2583 4.6918L15.3083 2.7418C14.9833 2.4168 14.4583 2.4168 14.1333 2.7418L12.6083 4.2668L15.7333 7.3918L17.2583 5.8668Z" fill="#007CBA"></path></svg>',
				'plus'    => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15.8332 10.8337H10.8332V15.8337H9.1665V10.8337H4.1665V9.16699H9.1665V4.16699H10.8332V9.16699H15.8332V10.8337Z" fill="#007CBA"></path></svg>',
				'info'    => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9.99984 1.66675C5.39984 1.66675 1.6665 5.40008 1.6665 10.0001C1.6665 14.6001 5.39984 18.3334 9.99984 18.3334C14.5998 18.3334 18.3332 14.6001 18.3332 10.0001C18.3332 5.40008 14.5998 1.66675 9.99984 1.66675ZM10.8332 14.1667H9.1665V9.16675H10.8332V14.1667ZM10.8332 7.50008H9.1665V5.83341H10.8332V7.50008Z" fill="#2271B1"/></svg>',
				'close'   => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M17.1429 4.2953L11.4382 10L17.1429 15.7047L15.7047 17.1429L10 11.4382L4.2953 17.1429L2.85714 15.7047L8.56184 10L2.85714 4.2953L4.2953 2.85714L10 8.56184L15.7047 2.85714L17.1429 4.2953Z" fill="#D6336C"/></svg>',
			];

			if ( isset( $ui_icons[ $icon ] ) ) {
				return $ui_icons[ $icon ];
			}

			return false;
		}

		/**
		 * @return array
		 */
		public static function get_popup_animation_list( $options = false ) {
			$list = [
				'fade'           => esc_html__( 'Fade', 'jet-popup' ),
				'zoom-in'        => esc_html__( 'ZoomIn', 'jet-popup' ),
				'zoom-out'       => esc_html__( 'ZoomOut', 'jet-popup' ),
				'rotate'         => esc_html__( 'Rotate', 'jet-popup' ),
				'move-up'        => esc_html__( 'MoveUp', 'jet-popup' ),
				'flip-x'          => esc_html__( 'Horizontal Flip', 'jet-popup' ),
				'flip-y'          => esc_html__( 'Vertical Flip', 'jet-popup' ),
				'bounce-in'      => esc_html__( 'BounceIn', 'jet-popup' ),
				'bounce-out'     => esc_html__( 'BounceOut', 'jet-popup' ),
				'slide-in-up'    => esc_html__( 'SlideInUp', 'jet-popup' ),
				'slide-in-right' => esc_html__( 'SlideInRight', 'jet-popup' ),
				'slide-in-down'  => esc_html__( 'SlideInDown', 'jet-popup' ),
				'slide-in-left'  => esc_html__( 'SlideInLeft', 'jet-popup' ),
			];

			if ( $options ) {
				$options = [];

				foreach ( $list as $value => $label ) {
					$options[] = [
						'label' => $label,
						'value' => $value,
					];
				}

				return $options;
			}

			return $list;
		}

		/**
		 * @return array
		 */
		public static function get_popup_open_trigger_list( $options = false ) {
			$list = [
				'attach'           => esc_html__( 'Not Selected', 'jet-popup' ),
				'page-load'        => esc_html__( 'On page load(s)', 'jet-popup' ),
				'user-inactive'    => esc_html__( 'Inactivity time after(s)', 'jet-popup' ),
				'scroll-trigger'   => esc_html__( 'Page Scrolled(%)', 'jet-popup' ),
				'try-exit-trigger' => esc_html__( 'Try exit', 'jet-popup' ),
				'on-date'          => esc_html__( 'On Date', 'jet-popup' ),
				'on-time'          => esc_html__( 'On Time', 'jet-popup' ),
				'on-date-and-time' => esc_html__( 'On Date and Time', 'jet-popup' ),
				'custom-selector'  => esc_html__( 'Custom Selector Click', 'jet-popup' ),
			];

			if ( $options ) {
				$options = [];

				foreach ( $list as $value => $label ) {
					$options[] = [
						'label' => $label,
						'value' => $value,
					];
				}

				return $options;
			}

			return $list;
		}

		/**
		 * @return array
		 */
		public static function get_popup_time_delay_list( $options = false ) {
			$list = [
				'none'      => esc_html__( 'None', 'jet-popup' ),
				'minute'    => esc_html__( 'Minute', 'jet-popup' ),
				'10minutes' => esc_html__( '10 Minutes', 'jet-popup' ),
				'30minutes' => esc_html__( '30 Minutes', 'jet-popup' ),
				'hour'      => esc_html__( '1 Hour', 'jet-popup' ),
				'3hours'    => esc_html__( '3 Hours', 'jet-popup' ),
				'6hours'    => esc_html__( '6 Hours', 'jet-popup' ),
				'12hours'   => esc_html__( '12 Hours', 'jet-popup' ),
				'day'       => esc_html__( 'Day', 'jet-popup' ),
				'3days'     => esc_html__( '3 Days', 'jet-popup' ),
				'week'      => esc_html__( 'Week', 'jet-popup' ),
				'month'     => esc_html__( 'Month', 'jet-popup' ),
			];

			if ( $options ) {
				$options = [];

				foreach ( $list as $value => $label ) {
					$options[] = [
						'label' => $label,
						'value' => $value,
					];
				}

				return $options;
			}

			return $list;
		}

		/**
		 * @param $options
		 * @return array
		 */
		public static function get_popup_attached_trigger_list( $options = false ) {
			$list = [
				'none'           => __( 'None', 'jet-popup' ),
				'click-self'     => __( 'Click', 'jet-popup' ),
				'click-selector' => __( 'Custom Selector Click', 'jet-popup' ),
				'hover'          => __( 'Hover', 'jet-popup' ),
				'scroll-to'      => __( 'Scroll To Block', 'jet-popup' ),
			];

			if ( $options ) {
				$options = [];

				foreach ( $list as $value => $label ) {
					$options[] = [
						'label' => $label,
						'value' => $value,
					];
				}

				return $options;
			}

			return $list;
		}

		/**
		 * @param $options
		 * @return array
		 */
		public static function get_popup_action_list( $options = false ) {
			$list = [
				'link'                 => esc_html__( 'Link', 'jet-popup' ),
				'leave'                => esc_html__( 'Leave Page', 'jet-popup' ),
				'close-popup'          => esc_html__( 'Close Popup', 'jet-popup' ),
				'close-all-popups'     => esc_html__( 'Close All Popups', 'jet-popup' ),
				'close-constantly'     => esc_html__( 'Close Popup Сonstantly', 'jet-popup' ),
				'close-all-constantly' => esc_html__( 'Close All Popups Сonstantly', 'jet-popup' ),
			];

			if ( $options ) {
				$options = [];

				foreach ( $list as $value => $label ) {
					$options[] = [
						'label' => $label,
						'value' => $value,
					];
				}

				return $options;
			}

			return $list;
		}

				/**
		 * @param $options
		 * @return array
		 */
		public static function get_popup_on_сlose_event( $options = false ) {
			$list = [
				'none'              => esc_html__( 'Not Selected', 'jet-popup' ),
				'scroll-to-top'     => esc_html__( 'Scroll to top', 'jet-popup' ),
				'scroll-to-anchor'  => esc_html__( 'Scroll to anchor', 'jet-popup' ),
			];

			if ( $options ) {
				$options = [];

				foreach ( $list as $value => $label ) {
					$options[] = [
						'label' => $label,
						'value' => $value,
					];
				}

				return $options;
			}

			return $list;
		}

		/**
		 * @return mixed|void
		 */
		public static function wp_doing_rest() {
			return apply_filters( 'jet-popup/utils/wp_doing_rest', defined( 'REST_REQUEST' ) && REST_REQUEST );
		}

		/**
		 * @param $popup_id
		 * @param $use_url
		 * @return false|string
		 */
		public static function get_render_data_cache_key( $popup_id, $use_url = false ) {
			$cache_key = false;
			$base = "jet-popup-render-data-{$popup_id}";
			// phpcs:disable
			if ( $use_url ) {
				$current_url = home_url( $_SERVER['REQUEST_URI'] );
				$parsed = wp_parse_url( $current_url );
				$path = str_replace('/', '-', $parsed['path'] );
				$params = isset( $parsed['query'] ) ? str_replace( [ '&', '=' ], [ '-' ], $parsed['query'] ) : '';
				$base = "jet-popup-render-data-{$popup_id}-{$parsed['host']}{$path}{$params}";
			}
			// phpcs:enable
			return md5( $base );
		}
	}
}
