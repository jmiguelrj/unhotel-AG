<?php
namespace Jet_Engine_Layout_Switcher\Elementor;

class View extends \Jet_Engine_Layout_Switcher\Base\View {

	public function get_id() {
		return 'elementor';
	}

	public function is_edit_mode() {
		return \Elementor\Plugin::instance()->editor->is_edit_mode();
	}

	public function is_preview_mode() {
		return \Elementor\Plugin::$instance->preview->is_preview_mode();
	}

	public function get_uniq_wrap_selector( $widget_id ) {
		return '.elementor-element-' . $widget_id  . ' > .elementor-widget-container';
	}

	public function get_active_breakpoints() {
		$result = array();

		$active_breakpoints = \Elementor\Plugin::$instance->breakpoints->get_active_breakpoints();
		$active_breakpoints = array_reverse( $active_breakpoints );

		foreach ( $active_breakpoints as $name => $breakpoint_obj ) {
			$result[ $name ] = array(
				'direction' => $breakpoint_obj->get_direction(),
				'value'     => $breakpoint_obj->get_value(),
			);
		}

		return $result;
	}

	public function get_listing_settings_by_id( $widget_id ) {
		$widget_settings = array();
		$elementor       = \Elementor\Plugin::instance();
		$document        = $elementor->documents->get_current();

		if ( $document ) {
			$widget = \Elementor\Utils::find_element_recursive( $document->get_elements_data(), $widget_id );

			if ( $widget ) {
				$widget_instance = $elementor->elements_manager->create_element_instance( $widget );
				$widget_settings = $widget_instance->get_settings();
			}
		}

		return $widget_settings;
	}

	public function find_relevant_switcher_on_page( $grid_widget_id ) {

		$document = \Elementor\Plugin::instance()->documents->get_current();

		// Get the document on lazy load
		if ( ! $document && ! empty( $_REQUEST['action'] ) && 'jet_engine_ajax' === $_REQUEST['action']
			 && ! empty( $_REQUEST['handler'] ) && 'get_listing' === $_REQUEST['handler']
			 && ! empty( $_REQUEST['post_id'] )
		) {
			$document = \Elementor\Plugin::instance()->documents->get_doc_for_frontend( $_REQUEST['post_id'] );
		}

		if ( ! $document ) {
			return false;
		}

		return $this->find_switcher_settings_by_grid_id( $document->get_elements_data(), $grid_widget_id );
	}

	public function find_switcher_settings_by_grid_id( $elements, $grid_widget_id ) {

		foreach ( $elements as $element ) {

			if ( ! empty( $element['widgetType'] )
				 && 'jet-engine-layout-switcher' === $element['widgetType']
				 && ! empty( $element['settings']['widget_id'] )
				 && $grid_widget_id === $element['settings']['widget_id']
			) {
				return $element['settings'];
			}

			if ( ! empty( $element['elements'] ) ) {
				$result = $this->find_switcher_settings_by_grid_id( $element['elements'], $grid_widget_id );

				if ( ! empty( $result ) ) {
					return $result;
				}
			}
		}

		return false;
	}

}
