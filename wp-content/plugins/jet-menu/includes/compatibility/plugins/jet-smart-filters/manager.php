<?php
namespace Jet_Menu\Compatibility;

// Exit if accessed directly.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Compatibility Manager
 */
class Jet_Smart_Filters {

	/**
	 * Jet_Smart_Filters constructor.
	 */
	public function __construct() {

		if ( ! class_exists( '\Jet_Smart_Filters' ) || ! function_exists( 'jet_smart_filters' ) ) {
			return;
		}

		// Check if a JetSmartFilters widget is being rendered
		add_action( 'elementor/frontend/widget/before_render', array( $this, 'maybe_use_filters' ) );

		// Enqueue styles only if a filter is used
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles_if_needed' ), 20 );
	}

	/**
	 * Detects if a JetSmartFilters widget is present and marks filters as used.
	 *
	 * @param \Elementor\Widget_Base $widget
	 */
	public function maybe_use_filters( $widget ) {

		if ( ! method_exists( $widget, 'get_categories' ) ) {
			return;
		}

		// Check if the widget belongs to JetSmartFilters category
		if ( in_array( jet_smart_filters()->widgets->get_category(), $widget->get_categories(), true ) ) {
			jet_smart_filters()->filters_not_used = false;

			remove_action( 'elementor/frontend/widget/before_render', array( $this, 'maybe_use_filters' ) );
		}

	}

	/**
	 * Enqueue JetSmartFilters styles only if a filter widget is actually used on the page.
	 */
	public function enqueue_styles_if_needed() {

		if ( ! jet_smart_filters()->filters_not_used ) {
			wp_enqueue_style( 'jet-smart-filters' );
		}

	}
}
