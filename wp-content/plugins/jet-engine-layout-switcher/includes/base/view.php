<?php
namespace Jet_Engine_Layout_Switcher\Base;

class View {

	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_view_js_object' ), 20 );
	}

	public function get_id() {
		return '';
	}

	public function is_edit_mode() {
		return false;
	}

	public function is_preview_mode() {
		return false;
	}

	public function get_uniq_wrap_selector( $widget_id ) {
		return '';
	}

	public function get_breakpoint_divider() {
		return '_';
	}

	public function get_active_breakpoints() {
		return array();
	}

	public function get_listing_settings_by_id( $widget_id ) {
		return array();
	}

	public function find_relevant_switcher_on_page( $widget_id ) {
		return false;
	}

	public function register_view_js_object() {
		$data = sprintf(
			'
			window.JetEngineLayoutSwitcherViews = window.JetEngineLayoutSwitcherViews || {};
			window.JetEngineLayoutSwitcherViews[\'%1$s\'] = {
				getUniqWrapSelector: function( widgetId ) {
					return \'%2$s\'.replace( \'__id__\', widgetId );
				},
				getActiveBreakpoints: function() {
					return %3$s;
				},
				getBreakpointDivider: function() {
					return \'%4$s\';
				},
			};',
			$this->get_id(),
			$this->get_uniq_wrap_selector( '__id__' ),
			json_encode( $this->get_active_breakpoints() ),
			$this->get_breakpoint_divider()
		);

		wp_add_inline_script( 'jet-engine-layout-switcher', $data, 'before' );
	}

}
