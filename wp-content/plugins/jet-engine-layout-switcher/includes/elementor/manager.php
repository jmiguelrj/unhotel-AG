<?php
namespace Jet_Engine_Layout_Switcher\Elementor;

class Manager {

	public function __construct() {
		add_action( 'elementor/widgets/register',      array( $this, 'register_widget' ) );
		add_action( 'elementor/controls/register',     array( $this, 'register_control' ) );
		add_action( 'jet-engine-layout-switcher/init', array( $this, 'register_view' ) );
	}

	public function register_widget( $widgets_manager ) {
		$widgets_manager->register( new Widget() );
	}

	public function register_control( $controls_manager ) {
		$controls_manager->register( new Controls\Finder_Widget_Control() );
	}

	public function register_view( $plugin ) {
		$plugin->register_view( new View() );
	}

}
