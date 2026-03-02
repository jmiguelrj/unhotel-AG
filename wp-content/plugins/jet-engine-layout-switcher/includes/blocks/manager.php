<?php
namespace Jet_Engine_Layout_Switcher\Blocks;

class Manager {

	public function __construct() {
		add_action( 'jet-engine/blocks-views/register-block-types', array( $this, 'register_block' ) );
		add_action( 'jet-engine/blocks-views/editor-script/after',  array( $this, 'register_block_script' ) );
		add_filter( 'jet-engine/blocks-views/editor/config',        array( $this, 'localize_editor_config' ) );
		add_action( 'jet-engine-layout-switcher/init',              array( $this, 'register_view' ) );
		add_action( 'enqueue_block_editor_assets',                  array( $this, 'enqueue_preview_scripts' ) );

		add_filter( 'jet-engine/listing/render/jet-listing-grid/settings', array( $this, 'add_uniq_id_setting' ), 0 );
	}

	public function register_block( $blocks_manager ) {
		$blocks_manager->register_block_type( new Layout_Switcher_Block() );
	}

	public function register_block_script() {
		wp_enqueue_script(
			'jet-engine-layout-switcher-block',
			JET_ENGINE_LAYOUT_SWITCHER_URL . 'assets/js/blocks/blocks.js',
			array(),
			JET_ENGINE_LAYOUT_SWITCHER_VERSION,
			true
		);
	}

	public function localize_editor_config( $config ) {
		$config['atts']['layoutSwitcher'] = jet_engine()->blocks_views->block_types->get_block_atts( 'layout-switcher' );
		return $config;
	}

	public function register_view( $plugin ) {
		$plugin->register_view( new View() );
	}

	public function enqueue_preview_scripts() {
		jet_engine_layout_switcher()->frontend->register_styles();
		jet_engine_layout_switcher()->frontend->enqueue_preview_scripts();
	}

	public function add_uniq_id_setting( $settings ) {

		if ( ! empty( $settings['_block_id'] ) ) {
			$settings['_id'] = $settings['_block_id'];
		}

		return $settings;
	}

}
