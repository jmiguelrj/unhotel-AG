<?php
namespace Jet_Theme_Core\Theme_Builder;

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

class Admin_Bar {

	/**
	 * A reference to an instance of this class.
	 *
	 * @access private
	 * @var    object
	 */
	private static $instance = null;

	/**
	 * A reference to an instance of this class.
	 *
	 * @access private
	 * @var    object
	 */
	public $args = array();

	/**
	 * Returns the instance.
	 *
	 * @param  array $args
	 * @access public
	 * @return object
	 */
	public static function get_instance( array $args = array() ) {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self( $args );
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @param  array $args
	 * @access public
	 * @return void
	 */
	public function __construct( $args = array() ) {

		return;

		if ( is_admin() || ! is_admin_bar_showing() ) {
			return;
		}

		remove_action( 'wp_body_open', 'wp_admin_bar_render', 0 );

		add_action( 'admin_bar_menu', array( $this, 'register_items' ), 99 );

		//add_action( 'wp_enqueue_scripts', array( $this, 'add_inline_style' ) );
	}


	/**
	 * Register items.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar
	 */
	public function register_items( $wp_admin_bar ) {
		$page_template_layouts = jet_theme_core()->theme_builder->frontend_manager->get_matched_page_template_layouts();
		$all_site_conditions = jet_theme_core()->theme_builder->page_templates_manager->get_site_page_template_conditions();

		$wp_admin_bar->add_node( [
			'id'    => 'jet_theme_core_node',
			'title' => esc_html__( 'JetThemeCore', 'jet-theme-core' ),
			'href'  => '#',
		] );
	}

	public function add_inline_style() {
		$css = '
				#wpadminbar #wp-admin-bar-jet_plugins > .ab-item::before {
					content: "";
					width: 20px;
					height: 18px;
					top: 3px;
					background-size: contain;
					background-repeat: no-repeat;
					background-position: center center;
					background-image: url("data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0iI2E3YWFhZCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cGF0aCBmaWxsLXJ1bGU9ImV2ZW5vZGQiIGNsaXAtcnVsZT0iZXZlbm9kZCIgZD0iTTIwIDFINEMyLjM0MzE1IDEgMSAyLjM0MzE1IDEgNFYyMEMxIDIxLjY1NjkgMi4zNDMxNSAyMyA0IDIzSDIwQzIxLjY1NjkgMjMgMjMgMjEuNjU2OSAyMyAyMFY0QzIzIDIuMzQzMTUgMjEuNjU2OSAxIDIwIDFaTTQgMEMxLjc5MDg2IDAgMCAxLjc5MDg2IDAgNFYyMEMwIDIyLjIwOTEgMS43OTA4NiAyNCA0IDI0SDIwQzIyLjIwOTEgMjQgMjQgMjIuMjA5MSAyNCAyMFY0QzI0IDEuNzkwODYgMjIuMjA5MSAwIDIwIDBINFoiIGZpbGw9IiNhN2FhYWQiLz48cGF0aCBmaWxsLXJ1bGU9ImV2ZW5vZGQiIGNsaXAtcnVsZT0iZXZlbm9kZCIgZD0iTTIxLjYyOTMgNi4wMDA2NkMyMS45NDAyIDUuOTgxNDggMjIuMTE3NiA2LjM4NTc4IDIxLjkxMSA2LjY0Mjc3TDIwLjA3MjIgOC45MzAzNUMxOS44NTY5IDkuMTk4MjQgMTkuNDU1NiA5LjAyNjk4IDE5LjQ1OTggOC42NjlMMTkuNDcwOCA3Ljc0MDg0QzE5LjQ3MjIgNy42MTkyMyAxOS40MjE2IDcuNTAzOTggMTkuMzM0MyA3LjQyOTc1TDE4LjY2NzYgNi44NjMyMUMxOC40MTA1IDYuNjQ0NyAxOC41Mzc4IDYuMTkxMzQgMTguODYxOSA2LjE3MTM1TDIxLjYyOTMgNi4wMDA2NlpNNi45OTgzNSAxMi4wMDhDNi45OTgzNSAxNC4xOTkzIDUuMjA3MDYgMTUuOTc1MSAyLjk5OTY3IDE1Ljk3NTFDMi40NDY1NSAxNS45NzUxIDIgMTUuNTI5MyAyIDE0Ljk4MjdDMiAxNC40MzYxIDIuNDQ2NTUgMTMuOTkyOCAyLjk5OTY3IDEzLjk5MjhDNC4xMDMzNiAxMy45OTI4IDQuOTk5MDEgMTMuMTAzNiA0Ljk5OTAxIDEyLjAwOFY5LjAzMzIzQzQuOTk5MDEgOC40ODQxMyA1LjQ0NTU2IDguMDQwODIgNS45OTg2OCA4LjA0MDgyQzYuNTUxNzkgOC4wNDA4MiA2Ljk5ODM1IDguNDg0MTMgNi45OTgzNSA5LjAzMzIzVjEyLjAwOFpNMTcuNzc2NSAxMi4wMDhDMTcuNzc2NSAxMy4xMDM2IDE4LjY3MjEgMTMuOTkyOCAxOS43NzU4IDEzLjk5MjhDMjAuMzI5IDEzLjk5MjggMjAuNzc1NSAxNC40MzM2IDIwLjc3NTUgMTQuOTgyN0MyMC43NzU1IDE1LjUzMTggMjAuMzI5IDE1Ljk3NTEgMTkuNzc1OCAxNS45NzUxQzE3LjU2ODQgMTUuOTc1MSAxNS43NzcyIDE0LjE5OTMgMTUuNzc3MiAxMi4wMDhWOS4wMzMyM0MxNS43NzcyIDguNDg0MTMgMTYuMjIzNyA4LjA0MDgyIDE2Ljc3NjggOC4wNDA4MkMxNy4zMyA4LjA0MDgyIDE3Ljc3NjUgOC40ODY2NSAxNy43NzY1IDkuMDMzMjNWOS45MjIzN0gxOC41NzA3QzE5LjEyMzggOS45MjIzNyAxOS41NzI5IDEwLjM2ODIgMTkuNTcyOSAxMC45MTczQzE5LjU3MjkgMTEuNDY2NCAxOS4xMjM4IDExLjkxMjIgMTguNTcwNyAxMS45MTIySDE3Ljc3NjVWMTIuMDA4Wk0xNS4yMDM4IDEwLjYxNzZDMTUuMjA2MyAxMC42MTUxIDE1LjIwODggMTAuNjE1MSAxNS4yMDg4IDEwLjYxNTFDMTQuODk0MiA5Ljc5MzkzIDE0LjMwNTYgOS4wNzM1NSAxMy40ODM1IDguNjAwMDFDMTEuNTc1NSA3LjUwMTgxIDkuMTM5NzkgOC4xNTE2NiA4LjA0MTE3IDEwLjA1MDhDNi45NDAwMSAxMS45NDc1IDcuNTk0NjIgMTQuMzczMSA5LjUwMDA4IDE1LjQ2ODhDMTAuOTAzMiAxNi4yNzQ5IDEyLjU5MyAxNi4xMzM4IDEzLjgyNjEgMTUuMjQ3MkwxMy44MTg0IDE1LjIzNzFDMTQuMTAyNiAxNS4wNjMzIDE0LjI5MDQgMTQuNzUxIDE0LjI5MDQgMTQuMzk1OEMxNC4yOTA0IDEzLjg0OTIgMTMuODQzOCAxMy40MDU5IDEzLjI5MzIgMTMuNDA1OUMxMy4wMjY4IDEzLjQwNTkgMTIuNzgzMyAxMy41MDkyIDEyLjYwNTcgMTMuNjgwNUMxMi4wMDY5IDE0LjA4MSAxMS4yMTAyIDE0LjE0MzkgMTAuNTM3OCAxMy43NzYyTDE0LjU2NDQgMTEuOTE5OEMxNC43OTc4IDExLjg0OTMgMTUuMDA1OSAxMS42OTMxIDE1LjEzNTMgMTEuNDY2NEMxNS4yOTI2IDExLjE5NjkgMTUuMzA3OCAxMC44ODcxIDE1LjIwMzggMTAuNjE3NlpNMTIuNDg2NCAxMC4zMTUzQzEyLjYwNTcgMTAuMzgzMyAxMi43MTIyIDEwLjQ2MTQgMTIuODExMiAxMC41NDcxTDkuNDk3NTQgMTIuMDcwOUM5LjQ4OTkzIDExLjcyMDggOS41NzYyIDExLjM2NTcgOS43NjM5NSAxMS4wNDA3QzEwLjMxNDUgMTAuMDkzNyAxMS41MzI0IDkuNzY4NzQgMTIuNDg2NCAxMC4zMTUzWiIgZmlsbD0iI2E3YWFhZCIvPjwvc3ZnPg==")!important;
				}
				#wpadminbar .jet-ab-item .ab-item {
					display: flex;
					justify-content: space-between;
					align-items: center;
					gap: 10px;
				}
				#wpadminbar .jet-ab-title {
					white-space: nowrap;
					text-overflow: ellipsis;
					overflow: hidden;
					width: 100%;
				}
				#wpadminbar .jet-ab-sub-title {
					padding: 4px 8px;
					font-size: 11px;
					line-height: 9px;
					background: #55595c;
					border-radius: 3px;
				}
			';

		wp_add_inline_style( 'admin-bar', $css );
	}
}
