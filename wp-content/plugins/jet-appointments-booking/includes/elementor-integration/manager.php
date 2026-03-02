<?php
namespace JET_APB\Elementor_Integration;

use JET_APB\Plugin;

class Manager {

	public function __construct() {
		add_action( 'elementor/init', [ $this, 'init_components' ] );
	}

	public function init_components() {
		add_filter( 'jet-engine/elementor-view/dynamic-link/generel-options', [ $this, 'register_dynamic_link_option' ] );
		add_filter( 'jet-apb/settings/templates-post-types', [ $this, 'add_template_post_type' ] );
	}

	public function add_template_post_type( $post_types = [] ) {
		$post_types[] = 'elementor_library';
		return $post_types;
	}

	public function register_dynamic_link_option( $options ) {
		$options[ Plugin::instance()->google_cal->query_var ] = __( 'Jet Appointments Booking: add booking to Google calendar', 'jet-appointments-booking' );
		return $options;
	}

}
