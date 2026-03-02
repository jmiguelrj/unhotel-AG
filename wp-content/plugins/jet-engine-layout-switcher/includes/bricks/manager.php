<?php
namespace Jet_Engine_Layout_Switcher\Bricks;

class Manager {

	public function __construct() {
		add_action( 'init', array( $this, 'register_elements' ), 13 );
		add_action( 'jet-engine-layout-switcher/init', array( $this, 'register_view' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_preview_scripts' ) );
		add_action( 'wp_ajax_jet_engine_get_listings_elements_options', array( $this, 'get_listings_elements_options' ) );

		add_action( 'jet-engine/listing/grid/before-render',   array( $this, 'set_query_on_render' ) );
		add_action( 'jet-engine/listing/grid/after-render',    array( $this, 'destroy_bricks_query' ) );
		add_action( 'jet-engine/ajax-handlers/before-do-ajax', array( $this, 'set_query_on_listing_ajax' ), 10, 2 );
		add_action( 'jet-smart-filters/render/ajax/before',    array( $this, 'set_query_on_filters_ajax' ) );
	}

	public function register_elements() {

		if ( ! class_exists('\Jet_Engine\Bricks_Views\Elements\Base') ) {
			return;
		}

		\Bricks\Elements::register_element( JET_ENGINE_LAYOUT_SWITCHER_PATH . 'includes/bricks/layout-switcher.php' );
	}

	public function register_view( $plugin ) {
		$plugin->register_view( new View() );
	}

	public function enqueue_preview_scripts() {

		if ( ! bricks_is_builder() ) {
			return;
		}

		jet_engine_layout_switcher()->frontend->enqueue_preview_scripts();
	}

	public function get_listings_elements_options() {

		\Bricks\Ajax::verify_request( 'bricks-nonce-builder' );

		$result = array(
			'' => esc_html__( 'Select...', 'jet-engine' ),
		);

		if ( empty( $_REQUEST['postId'] ) ) {
			wp_send_json_success( $result );
		}

		$post_id = intval( $_REQUEST['postId'] );

		$bricks_data = get_post_meta( $post_id, BRICKS_DB_PAGE_CONTENT, true );

		if ( empty( $bricks_data ) ) {
			wp_send_json_success( $result );
		}

		$count = 1;

		foreach ( $bricks_data as $element ) {

			if ( empty( $element['name'] ) ) {
				continue;
			}

			if ( 'jet-engine-listing-grid' !== $element['name'] ) {
				continue;
			}

			$label = sprintf(
				'%1$s #%2$s (%3$s)',
				esc_html__( 'Listing Grid', 'jet-engine' ),
				$count,
				$element['id']
			);

			if ( ! empty( $element['label'] ) ) {
				$label .= ' - ' . $element['label'];
			}

			if ( ! empty( $element['settings']['_cssId'] ) ) {
				$label .= ' - #' . $element['settings']['_cssId'];
			}

			$result[ $element['id'] ] = $label;
			$count++;
		}

		wp_send_json_success( $result );
	}

	public function set_query_on_render( $render ) {
		$listing_id = $render->get_settings( '_layout_listing' );

		if ( $listing_id ) {
			jet_engine()->bricks_views->listing->render->set_bricks_query( $listing_id, $render->get_settings() );
		}
	}

	public function destroy_bricks_query( $render ) {
		$listing_id = $render->get_settings( '_layout_listing' );

		if ( $listing_id ) {
			jet_engine()->bricks_views->listing->render->destroy_bricks_query_for_listing( $listing_id );
		}
	}

	public function set_query_on_listing_ajax( $ajax_handler, $request ) {
		$settings   = $request['widget_settings'] ?? $request['settings'] ?? array();
		$listing_id = ! empty ( $settings['_layout_listing'] ) ? $settings['_layout_listing'] : false;

		if ( $listing_id ) {
			jet_engine()->bricks_views->listing->render->set_bricks_query( $listing_id, $settings );
		}
	}

	public function set_query_on_filters_ajax() {
		$settings   = isset( $_REQUEST['settings'] ) ? $_REQUEST['settings'] : array();
		$listing_id = ! empty ( $settings['_layout_listing'] ) ? $settings['_layout_listing'] : false;

		if ( $listing_id ) {
			jet_engine()->bricks_views->listing->render->set_bricks_query( $listing_id, $settings );
		}
	}

}
