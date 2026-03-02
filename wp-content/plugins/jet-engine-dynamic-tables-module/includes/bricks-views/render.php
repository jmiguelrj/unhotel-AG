<?php
/**
 * Bricks views manager
 */
namespace Jet_Engine_Dynamic_Tables\Bricks_Views;

use Jet_Engine_Dynamic_Tables\Plugin;

/**
 * Define render class
 */
class Render {

	public function __construct() {
		add_action( 'jet-engine/data-tables/before-render', [ $this, 'set_query_on_render' ] );
		add_action( 'jet-engine/data-tables/after-render', [ $this, 'destroy_bricks_query' ] );

		add_action( 'jet-smart-filters/render/ajax/before', [ $this, 'set_query_on_filters_ajax' ] );
		add_filter( 'jet-engine/table-builder/render/default-settings', [  $this, 'add_default_settings' ] );
	}

	public function set_bricks_query( $table_id = 0, $settings = [] ) {
		$this->handle_bricks_queries( $table_id, function( $template_id ) use ( $settings ) {
			jet_engine()->bricks_views->listing->render->set_bricks_query( $template_id, $settings );
		} );
	}

	public function set_query_on_filters_ajax() {
		$settings = isset( $_REQUEST['settings'] ) ? $_REQUEST['settings'] : [];
		$table_id = ! empty ( $settings['table_id'] ) ? $settings['table_id'] : 0;
		$this->set_bricks_query( $table_id, $settings );
	}

	public function set_query_on_render( $render ) {
		$table_id = $render->get_settings( 'table_id' );
		$settings = $render->get_settings();
		$this->set_bricks_query( $table_id, $settings );
	}

	public function destroy_bricks_query( $render ) {
		$table_id = $render->get_settings( 'table_id' );

		$this->handle_bricks_queries( $table_id, function( $template_id ) {
			jet_engine()->bricks_views->listing->render->destroy_bricks_query_for_listing( $template_id );
		} );
	}

	private function handle_bricks_queries( $table_id, $callback ) {
		$table_data = Plugin::instance()->data->get_item_for_edit( $table_id );

		if ( empty( $table_data['meta_fields'] ) || ! is_array( $table_data['meta_fields'] ) ) {
			return;
		}

		foreach ( $table_data['meta_fields'] as $meta_field ) {
			if ( $meta_field['content'] !== 'template' ) {
				continue;
			}

			call_user_func( $callback, $meta_field['template_id'] );
		}
	}

	public function add_default_settings( $settings ) {
		$settings['_id'] = '';

		return $settings;
	}
}
