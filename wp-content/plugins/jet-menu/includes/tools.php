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

if ( ! class_exists( 'Jet_Menu_Tools' ) ) {

	/**
	 * Define Jet_Menu_Tools class
	 */
	class Jet_Menu_Tools {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Returns the instance.
		 *
		 * @return object
		 * @since  1.0.0
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		/**
		 * @param string $svg_id
		 * @param bool $wrapper
		 *
		 * @return string
		 */
		public function get_svg_icon_html( $svg_id = '', $default = '', $attr = array(), $wrapper = true ) {

			if ( empty( $svg_id ) ) {
				return $default;
			}

			$url = wp_get_attachment_url( $svg_id );

			if ( ! $url ) {
				return $default;
			}

			return $this->get_image_by_url( $url, $attr, $wrapper );
		}

		/**
		 * Render Icon HTML
		 *
		 * @param string $icon Icon slug to render.
		 *
		 * @return string
		 */
		public function get_icon_html( $icon = '', $class = 'jet-menu-icon' ) {
			$format = apply_filters( 'jet-menu/tools/icon-format', "<i class='$class fa %s'></i>", $icon );

			return sprintf( $format, esc_attr( $icon ) );
		}

		/**
		 * [get_svg_html description]
		 *
		 * @param string $svg_id [description]
		 *
		 * @return [type]         [description]
		 */
		public function get_svg_html( $svg_id = '', $wrapper = true ) {

			if ( empty( $svg_id ) ) {
				return '';
			}

			$url = wp_get_attachment_url( $svg_id );

			if ( ! $url ) {
				return '';
			}

			return $this->get_image_by_url( $url, array ( 'class' => 'jet-menu-icon' ), $wrapper );
		}

		/**
		 * Rturns image tag or raw SVG
		 *
		 * @param string $url image URL.
		 * @param array $attr [description]
		 *
		 * @return string
		 */
		public function get_image_by_url( $url = null, $attr = array (), $wrapper = true ) {

			$url = esc_url( $url );

			if ( empty( $url ) ) {
				return;
			}

			$ext  = pathinfo( $url, PATHINFO_EXTENSION );
			$attr = array_merge( array ( 'alt' => '' ), $attr );

			if ( 'svg' !== strtolower( $ext ) ) {
				return sprintf( '<img src="%1$s"%2$s>', $url, $this->get_attr_string( $attr ) );
			}

			$base_url = site_url( '/' );
			$svg_path = str_replace( $base_url, ABSPATH, $url );
			$key      = md5( $svg_path );
			$svg      = get_transient( $key );

			if ( ! $svg ) {
				$svg = '';

				if ( $svg_path && @is_file( $svg_path ) && @is_readable( $svg_path ) ) {
					$svg = @file_get_contents( $svg_path );
				}

				if ( ! $svg ) {
					$resp = wp_remote_get( $url, array( 'timeout' => 3 ) );

					if ( ! is_wp_error( $resp ) ) {
						$body = wp_remote_retrieve_body( $resp );
						$ct   = wp_remote_retrieve_header( $resp, 'content-type' );

						if ( $body && ( empty( $ct ) || false !== stripos( $ct, 'image/svg+xml' ) ) ) {
							$svg = $body;
						}

					}

				}

				if ( $svg ) {
					set_transient( $key, $svg, DAY_IN_SECONDS );
				}
			}

			if ( ! $svg ) {
				return sprintf( '<img src="%1$s"%2$s>', $url, $this->get_attr_string( $attr ) );
			}

			$lower = strtolower( $svg );

			$has_unsafe_tags =
				( false !== strpos( $lower, '<image' ) ) ||
				( false !== strpos( $lower, '<pattern' ) ) ||
				( false !== strpos( $lower, '<use' ) ) ||
				( false !== strpos( $lower, '<script' ) ) ||
				( false !== strpos( $lower, '<foreignobject' ) );

			$too_large = strlen( $svg ) > 100 * 1024;

			if ( $has_unsafe_tags || $too_large ) {
				return sprintf( '<img src="%1$s"%2$s>', $url, $this->get_attr_string( $attr ) );
			}

			if ( ! $wrapper ) {
				return $svg;
			}

			unset( $attr[ 'alt' ] );

			return sprintf( '<div%2$s>%1$s</div>', $svg, $this->get_attr_string( $attr ) );
		}

		/**
		 * Return attributes string from attributes array.
		 *
		 * @param array $attr Attributes string.
		 *
		 * @return string
		 */
		public function get_attr_string( $attr = array () ) {

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
		 * Render Icon HTML
		 *
		 * @param string $icon Icon slug to render.
		 * @param string $icon_base Base icon class to render.
		 *
		 * @return string
		 */
		public function get_dropdown_arrow_icon_html( $icon = '', $icon_base = 'fa' ) {
			$format = apply_filters( 'jet-menu/tools/dropdown-arrow-format', '<i class="jet-dropdown-arrow %2$s %1$s"></i>', $icon, $icon_base );

			return sprintf( $format, esc_attr( $icon ), esc_attr( $icon_base ) );
		}

		/**
		 * @param string $svg_id
		 * @param bool $wrapper
		 *
		 * @return string
		 */
		public function get_dropdown_arrow_svg_html( $svg_id = '', $wrapper = true ) {

			if ( empty( $svg_id ) ) {
				return '';
			}

			$url = wp_get_attachment_url( $svg_id );

			if ( ! $url ) {
				return '';
			}

			return $this->get_image_by_url( $url, array ( 'class' => 'jet-dropdown-arrow' ), $wrapper );
		}

		/**
		 * Get menu badge HTML
		 *
		 * @param string $badge Badge HTML.
		 *
		 * @return string
		 */
		public function get_badge_html( $badge = '', $depth = 0 ) {

			if ( 0 < $depth ) {
				$is_hidden = jet_menu()->settings_manager->options_manager->get_option( 'jet-menu-sub-badge-hide', 'false' );
			} else {
				$is_hidden = jet_menu()->settings_manager->options_manager->get_option( 'jet-menu-top-badge-hide', 'false' );
			}

			$hide_on_mobile = ( 'true' === $is_hidden ) ? ' jet-hide-mobile' : '';

			$format = apply_filters( 'jet-menu/tools/badge-format', '<small class="jet-menu-badge%2$s"><span class="jet-menu-badge__inner">%1$s</span></small>', $badge, $depth );

			return sprintf( $format, $badge, $hide_on_mobile );
		}

		/**
		 * Add menu item dynamic CSS
		 *
		 * @param integer $item_id [description]
		 * @param string $wrapper [description]
		 */
		public function add_menu_css( $item_id = 0, $wrapper = '' ) {
			$settings = jet_menu()->settings_manager->get_item_settings( $item_id );

			$css_scheme = apply_filters( 'jet-menu/item-css/scheme', array (
				'icon_color' => array (
					'selector' => array (
						'> a .jet-menu-icon'                                       => 'color',
						'> a .jet-menu-icon:before'                                => 'color',
						'> .jet-mobile-menu__item-inner > a .jet-menu-icon'        => 'color',
						'> .jet-mobile-menu__item-inner > a .jet-menu-icon:before' => 'color',
					),
					'rule'     => 'color',
					'value'    => '%1$s !important;',
				),
				'icon_size'              => array (
					'selector' => array (
						'> a .jet-menu-icon:before'                             => 'font-size',
						'> a .jet-menu-icon svg'                                => 'width',
						'> a .jet-menu-icon img'                                => 'width',
						'> .jet-mobile-menu__item-inner > a .jet-menu-icon'     => 'font-size',
						'> .jet-mobile-menu__item-inner > a .jet-menu-icon img' => 'width',
						'> .jet-mobile-menu__item-inner > a .jet-menu-icon svg' => 'width',
					),
					'rule'     => 'font-size',
					'value'    => '%1$spx !important;',
				),
				'badge_color'            => array (
					'selector' => array (
						'> a .jet-menu-badge .jet-menu-badge__inner'                                => 'color',
						'> .jet-mobile-menu__item-inner > a .jet-menu-badge .jet-menu-badge__inner' => 'color',
					),
					'rule'     => 'color',
					'value'    => '%1$s !important;',
				),
				'badge_bg_color'         => array (
					'selector' => array (
						'> a .jet-menu-badge .jet-menu-badge__inner'                                => 'background-color',
						'> .jet-mobile-menu__item-inner > a .jet-menu-badge .jet-menu-badge__inner' => 'background-color',
					),
					'rule'     => 'background-color',
					'value'    => '%1$s !important;',
				),
				'badge_svg_size'              => array (
					'selector' => array (
						'> a .jet-mega-menu-item__badge svg'                     => 'width',
						'> a .jet-mega-menu-item__badge img'                     => 'width',
						'> .jet-mobile-menu__item-inner > a .jet-menu-badge svg' => 'width',
						'> .jet-mobile-menu__item-inner > a .jet-menu-badge img' => 'width',
						'> a .jet-menu-badge svg'                                => 'width',
						'> a .jet-menu-badge img'                                => 'width',
					),
					'rule'     => 'width',
					'value'    => '%1$spx !important;',
				),
				'badge_offset_x'              => array (
					'selector' => array (
						'> a .jet-menu-badge'                                => '--jmm-menu-badge-offset-x',
						'> .jet-mobile-menu__item-inner > a .jet-menu-badge' => '--jmm-menu-badge-offset-x',
					),
					'rule'     => '--jmm-menu-badge-offset-x',
					'value'    => '%1$spx !important;',
				),
				'badge_offset_y'              => array (
					'selector' => array (
						'> a .jet-menu-badge'                                => '--jmm-menu-badge-offset-y',
						'> .jet-mobile-menu__item-inner > a .jet-menu-badge' => '--jmm-menu-badge-offset-y',
					),
					'rule'     => '--jmm-menu-badge-offset-y',
					'value'    => '%1$spx !important;',
				),
				'item_padding'           => array (
					'selector' => array (
						'> a'                                => 'padding-%s',
						'> .jet-mobile-menu__item-inner > a' => 'padding-%s',
					),
					'rule'     => 'padding-%s',
					'value'    => '',
					'desktop'  => true,
				),
				'custom_mega_menu_width' => array (
					'selector' => array(
						'> .jet-sub-mega-menu'        => 'width',
						'> .jet-custom-nav__mega-sub' => 'width',
						'> .jet-mega-menu-mega-container' => 'width',
					),
					'rule'     => 'width',
					'value'    => '%1$spx !important;',
				),
			) );

			foreach ( $css_scheme as $setting => $data ) {

				if ( empty( $settings[ $setting ] ) ) {
					continue;
				}

				$_wrapper = $wrapper;

				if ( isset( $data[ 'desktop' ] ) && true === $data[ 'desktop' ] ) {
					$_wrapper = '.jet-menu ' . $wrapper;
				}

				if ( is_array( $settings[ $setting ] ) && isset( $settings[ $setting ][ 'units' ] ) ) {

					if ( is_array( $data[ 'selector' ] ) ) {
						foreach ( $data[ 'selector' ] as $selector => $rule ) {
							jet_menu_dynmic_css()->add_dimensions_css( array (
								'selector'  => sprintf( '%1$s %2$s', $_wrapper, $selector ),
								'rule'      => $rule,
								'values'    => $settings[ $setting ],
								'important' => true,
							) );
						}
					} else {
						jet_menu_dynmic_css()->add_dimensions_css( array (
							'selector'  => sprintf( '%1$s %2$s', $_wrapper, $data[ 'selector' ] ),
							'rule'      => $data[ 'rule' ],
							'values'    => $settings[ $setting ],
							'important' => true,
						) );
					}

				} else {

					if ( ! isset( $settings[ $setting ] ) || false === $settings[ $setting ] || 'false' === $settings[ $setting ] || '' === $settings[ $setting ] ) {
						continue;
					}

					if ( is_array( $data[ 'selector' ] ) ) {
						foreach ( $data[ 'selector' ] as $selector => $rule ) {

							jet_menu()->dynamic_css_manager->add_style( sprintf( '%1$s %2$s', $_wrapper, $selector ), array (
								$rule => sprintf( $data[ 'value' ], esc_attr( $settings[ $setting ] ) ),
							) );
						}
					} else {
						jet_menu()->dynamic_css_manager->add_style( sprintf( '%1$s %2$s', $_wrapper, $data[ 'selector' ] ), array (
							$data[ 'rule' ] => sprintf( $data[ 'value' ], esc_attr( $settings[ $setting ] ) ),
						) );
					}
				}
			}
		}

		/**
		 * [get_arrows_icons description]
		 * @return [type] [description]
		 */
		public function get_arrows_icons() {
			return apply_filters( 'jet-menu/arrow-icons', array (
				'fa-angle-down',
				'fa-angle-double-down',
				'fa-arrow-circle-down',
				'fa-arrow-down',
				'fa-caret-down',
				'fa-chevron-circle-down',
				'fa-chevron-down',
				'fa-long-arrow-down',
				'fa-angle-right',
				'fa-angle-double-right',
				'fa-arrow-circle-right',
				'fa-arrow-right',
				'fa-caret-right',
				'fa-chevron-circle-right',
				'fa-chevron-right',
				'fa-long-arrow-right',
				'fa-angle-left',
				'fa-angle-double-left',
				'fa-arrow-circle-left',
				'fa-arrow-left',
				'fa-caret-left',
				'fa-chevron-circle-left',
				'fa-chevron-left',
				'fa-long-arrow-left',
			) );
		}

		/**
		 * @return array
		 */
		public function get_default_dimensions() {
			return array(
				'top'       => '',
				'right'     => '',
				'bottom'    => '',
				'left'      => '',
				'is_linked' => true,
				'units'     => 'px',
			);
		}

		/**
		 * [get_elementor_templates_select_options description]
		 * @return [type] [description]
		 */
		public function get_elementor_templates_select_options() {

			if ( ! jet_menu_tools()->has_elementor() || ! is_admin() ) {
				return array ();
			}

			$templates = jet_menu()->elementor()->templates_manager->get_source( 'local' )->get_items();

			if ( ! $templates ) {
				return array ();
			}

			$select_options[] = array (
				'label' => esc_html__( 'None', 'jet-menu' ),
				'value' => '',
			);

			foreach ( $templates as $key => $template ) {
				$select_options[] = array (
					'label' => $template[ 'title' ],
					'value' => $template[ 'template_id' ],
				);
			}

			return $select_options;

		}

		/**
		 * [my_wp_is_mobile description]
		 * @return [type] [description]
		 */
		// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		public static function is_phone() {
			static $is_mobile;

			if ( isset( $is_mobile ) ) {
				return $is_mobile;
			}

			if ( empty( $_SERVER[ 'HTTP_USER_AGENT' ] ) ) {
				$is_mobile = false;
			} elseif ( strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'Android' ) !== false || strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'Silk/' ) !== false || strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'Kindle' ) !== false || strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'BlackBerry' ) !== false || strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'Opera Mini' ) !== false ) {
				$is_mobile = true;
			} elseif ( strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'Mobile' ) !== false && strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'iPad' ) == false ) {
				$is_mobile = true;
			} elseif ( strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'iPad' ) !== false ) {
				$is_mobile = false;
			} else {
				$is_mobile = false;
			}

			return $is_mobile;
		}
		// phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash

		/**
		 * @return string
		 */
		public function get_current_device() {
			$mobile_detect = new Mobile_Detect;

			if ( isset( $_COOKIE['is_ipad_pro'] ) && 'true' === $_COOKIE['is_ipad_pro'] ) {
				return 'tablet';
			}

			if ( $mobile_detect->isTablet() ) {
				return 'tablet';
			} elseif ( $mobile_detect->isMobile() ) {
				return 'mobile';
			}

			return 'desktop';
		}

		/**
		 * @return mixed
		 */
		public function is_nextgen_mode() {
			$is_nextgen = ! get_option( jet_menu()->settings_manager->options_manager->options_slug ) ? 'true' : 'false';

			return filter_var( jet_menu()->settings_manager->options_manager->get_option( 'plugin-nextgen-edition', true ), FILTER_VALIDATE_BOOLEAN );
		}

		/**
		 * Get available menus list
		 *
		 * @return array
		 */
		public function get_available_menus_options() {

			$raw_menus = wp_get_nav_menus();
			$menus     = wp_list_pluck( $raw_menus, 'name', 'term_id' );
			$parent    = isset( $_GET['parent_menu'] ) ? absint( $_GET['parent_menu'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( 0 < $parent && isset( $menus[ $parent ] ) ) {
				unset( $menus[ $parent ] );
			}

			$options = [];

			if ( ! empty( $menus ) ) {
				foreach ( $menus as $key => $label ) {
					$options[] = [
						'value' => $key,
						'label' => $label,
					];
				}
			}

			return $options;
		}

		/**
		 * @return mixed|void
		 */
		public function get_breakpoints() {
			return apply_filters( 'jet-menu/breakpoints/', [
				'xs'  => [
					'size' => 320,
					'label' => esc_html__( 'X-Small(less then 320px)', 'jet-menu' ),
				],
				'sm'  => [
					'size' => 576,
					'label' => esc_html__( 'Small(less then 576px)', 'jet-menu' ),
				],
				'md'  => [
					'size' => 768,
					'label' => esc_html__( 'Medium(less then 768px)', 'jet-menu' ),
				],
				'lg'  => [
					'size' => 992,
					'label' => esc_html__( 'Large(less then 992px)', 'jet-menu' ),
				],
				'xl'  => [
					'size' => 1200,
					'label' => esc_html__( 'Extra large(less then 1200px)', 'jet-menu' ),
				],
				'xxl' => [
					'size' => 1400,
					'label' => esc_html__( 'Extra extra large(less then 1400px)', 'jet-menu' ),
				],
			] );
		}

		/**
		 * @return array|array[]
		 */
		public function get_breakpoints_options() {

			$options = [];

			foreach ( $this->get_breakpoints() as $key => $breakpoint ) {
				$options[] = [
					'value' => $breakpoint['size'],
					'label' => $breakpoint['label'],
				];
			}

			return $options;
		}

		/**
		 * @return bool
		 */
		public function has_elementor() {
			return defined( 'ELEMENTOR_VERSION' );
		}

		/**
		 * @return array
		 */
		public function get_menu_time_delay_list( $options = false ) {
			$list = [
				'none'      => esc_html__( 'None', 'jet-menu' ),
				'minute'    => esc_html__( 'Minute', 'jet-menu' ),
				'10minutes' => esc_html__( '10 Minutes', 'jet-menu' ),
				'30minutes' => esc_html__( '30 Minutes', 'jet-menu' ),
				'hour'      => esc_html__( '1 Hour', 'jet-menu' ),
				'3hours'    => esc_html__( '3 Hours', 'jet-menu' ),
				'6hours'    => esc_html__( '6 Hours', 'jet-menu' ),
				'12hours'   => esc_html__( '12 Hours', 'jet-menu' ),
				'day'       => esc_html__( 'Day', 'jet-menu' ),
				'3days'     => esc_html__( '3 Days', 'jet-menu' ),
				'week'      => esc_html__( 'Week', 'jet-menu' ),
				'month'     => esc_html__( 'Month', 'jet-menu' ),
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
		 * Returns TRUE if “dev mode” (no cache) is active for current user/context.
		 * Default: users with 'edit_posts'. Overridable via 'jet-menu/template_cache/is_dev'.
		 *
		 * @param string $context Context label (e.g. 'localize', 'walker').
		 * @param array  $args    Extra context (optional).
		 * @return bool           TRUE = bypass cache, FALSE = use cache.
		 */
		function is_dev_mode( $context = 'localize', $args = [] ) {
			$default = current_user_can( 'edit_posts' );

			return (bool) apply_filters(
				'jet-menu/template_cache/is_dev',
				$default,
				array_merge( [
					'context' => $context,
				], $args )
			);

		}
	}

}

/**
 * Returns instance of Jet_Menu_Tools
 *
 * @return object
 */
function jet_menu_tools() {
	return Jet_Menu_Tools::get_instance();
}
