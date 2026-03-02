<?php
namespace Jet_Engine_Layout_Switcher\Elementor\Controls;

class Finder_Widget_Control extends \Elementor\Control_Select2 {

	public function get_type() {
		return 'jet-finder-widget';
	}

	public function enqueue() {
		// Scripts
		wp_register_script(
			'jet-finder-widget-control',
			JET_ENGINE_LAYOUT_SWITCHER_URL . 'includes/elementor/controls/finder-widget-control.js',
			array( 'jquery' ),
			JET_ENGINE_LAYOUT_SWITCHER_VERSION,
			true
		);

		wp_enqueue_script( 'jet-finder-widget-control' );
	}

	protected function get_default_settings() {
		return array_merge(
			parent::get_default_settings(),
			array(
				'widget_name' => '',
			)
		);
	}
}
