<?php
namespace Jet_Menu\Compatibility;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Manager {

	/**
	 * Registered plugins compatibility modules.
	 *
	 * @var array
	 */
	private $registered_modules = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->registered_modules = apply_filters( 'jet-menu/compatibility-manager/registered-plugins', [
			'jet-smart-filters' => array(
				'class'    => '\\Jet_Menu\\Compatibility\\Jet_Smart_Filters',
				'instance' => false,
				'path'     => jet_menu()->plugin_path( 'includes/compatibility/plugins/jet-smart-filters/manager.php' ),
			),
			'jet-theme-core' => array(
				'class'    => '\\Jet_Menu\\Compatibility\\Jet_Theme_Core',
				'instance' => false,
				'path'     => jet_menu()->plugin_path( 'includes/compatibility/plugins/jet-theme-core/manager.php' ),
			),
		] );

		$this->load_compatibility_modules();
	}

	/**
	 * Loads each registered compatibility module.
	 */
	public function load_compatibility_modules() {

		$this->registered_modules = array_map( function ( $module_data ) {
			$class = $module_data['class'];

			if ( file_exists( $module_data['path'] ) ) {
				require $module_data['path'];
			}

			if ( ! $module_data['instance'] && class_exists( $class ) ) {
				$module_data['instance'] = new $class();
			}

			return $module_data;
		}, $this->registered_modules );

	}

}
