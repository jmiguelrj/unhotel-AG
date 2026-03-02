<?php
namespace Jet_Menu\Compatibility;

// Exit if accessed directly.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Compatibility Manager
 */
class Jet_Theme_Core {

	/**
	 * @param $prevent
	 * @param $location
	 * @param $menu_id
	 * @return void
	 */
	public function prevent_modify_nav_menu( $prevent, $location, $menu_id ) {
		$is_theme_builder_render = jet_theme_core()->theme_builder->frontend_manager->is_theme_builder_render;

		if ( $is_theme_builder_render ) {
			return true;
		}

		return $prevent;
	}

	/**
	 * Jet_Theme_Core constructor.
	 */
	public function __construct() {

		if ( ! defined( 'JET_THEME_CORE_VERSION' ) ) {
			return false;
		}

		add_filter( 'jet-menu/mega-menu/location/prevent-modify-nav-menu', array( $this, 'prevent_modify_nav_menu' ), 10, 4);

	}
}
