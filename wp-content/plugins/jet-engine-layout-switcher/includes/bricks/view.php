<?php
namespace Jet_Engine_Layout_Switcher\Bricks;

class View extends \Jet_Engine_Layout_Switcher\Base\View {

	public function get_id() {
		return 'bricks';
	}

	public function is_edit_mode() {
		return jet_engine()->bricks_views->is_bricks_editor();
	}

	public function get_uniq_wrap_selector( $element_id ) {
		return '.brxe-jet-engine-listing-grid.brxe-' . $element_id;
	}

	public function get_breakpoint_divider() {
		return ':';
	}

	public function get_active_breakpoints() {
		$result      = array();
		$breakpoints = \Bricks\Breakpoints::get_breakpoints();

		foreach ( $breakpoints as $breakpoint ) {

			if ( empty( $breakpoint['key'] ) ) {
				continue;
			}

			if ( 'desktop' === $breakpoint['key'] ) {
				continue;
			}

			$result[ $breakpoint['key'] ] = array(
				'direction' => 'max',
				'value'     => $breakpoint['width']
			);
		}

		return $result;
	}

	public function get_listing_settings_by_id( $element_id ) {

		if ( empty( $element_id ) ) {
			return array();
		}

		$bricks_data = $this->get_bricks_data();

		if ( empty( $bricks_data ) ) {
			return array();
		}

		foreach ( $bricks_data as $element ) {
			if ( $element['id'] === $element_id ) {
				return $element['settings'];
			}
		}

		return array();
	}

	public function find_relevant_switcher_on_page( $grid_element_id ) {

		$bricks_data = $this->get_bricks_data();

		if ( empty( $bricks_data ) ) {
			return false;
		}

		foreach ( $bricks_data as $element ) {
			if ( ! empty( $element['name'] ) && 'jet-engine-layout-switcher' === $element['name']
				 && ! empty( $element['settings'] ) && ! empty( $element['settings']['widget_id'] )
				 && $grid_element_id === $element['settings']['widget_id']
			) {
				return $element['settings'];
			}
		}

		return false;
	}

	public function get_bricks_data() {
		$post_id      = false;
		$content_type = \Bricks\Database::$active_templates['content_type'] ?? false;

		if ( $content_type ) {
			$post_id = \Bricks\Database::$active_templates[ $content_type ] ?? false;
		}

		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		if ( ! $post_id ) {
			return array();
		}

		$data = get_post_meta( $post_id, BRICKS_DB_PAGE_CONTENT, true );

		return ! empty( $data ) ? $data : array();
	}

}
