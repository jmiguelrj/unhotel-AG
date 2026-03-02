<?php

namespace Jet_FB_Address_Autocomplete\JetFormBuilder\Blocks;


// If this file is called directly, abort.
use Jet_FB_Address_Autocomplete\AddressAutocomplete;
use Jet_FB_Address_Autocomplete\GutenbergStyleControls;
use Jet_FB_Address_Autocomplete\Plugin;
use Jet_FB_Address_Autocomplete\Traits\AddressFieldTrait;
use Jet_Form_Builder\Admin\Tabs_Handlers\Tab_Handler_Manager;
use Jet_Form_Builder\Blocks\Types\Base;
use JetAddressAutocompleteCore\JetFormBuilder\SmartBaseBlock;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define Text field block class
 */
class AddressBlock extends Base {

	use SmartBaseBlock;
	use AddressFieldTrait;

	/**
	 * Returns block name
	 *
	 * @return [type] [description]
	 */
	public function get_name() {
		return 'address-field';
	}

	protected function _jsm_register_controls() {
		( new GutenbergStyleControls() )->register_address_controls( $this->controls_manager );
	}

	public function get_field_template( $path ) {
		return Plugin::instance()->get_template_path( $path );
	}

	public function get_path_metadata_block() {
		$path_parts = array( 'assets', 'blocks', $this->get_name() );
		$path       = implode( DIRECTORY_SEPARATOR, $path_parts );

		return Plugin::instance()->plugin_dir( $path );
	}

	public function render_instance() {
		AddressAutocomplete::instance()->settings( 'jfb-address-tab', Tab_Handler_Manager::instance() );
		AddressAutocomplete::instance()->enqueue_styles();
		$this->enqueue_scripts();

		return new AddressBlockRender( $this );
	}

	private function enqueue_scripts() {
		wp_enqueue_script(
			Plugin::instance()->slug . '-lib',
			Plugin::instance()->plugin_url( 'assets/lib/jquery-editable-select.js' ),
			array( 'jquery' ),
			JET_FB_ADDRESS_AUTOCOMPLETE_VERSION,
			true
		);

		wp_enqueue_script(
			Plugin::instance()->slug . '-init',
			Plugin::instance()->plugin_url( 'assets/dist/frontend.init.js' ),
			array(
				'jet-plugins',
			),
			JET_FB_ADDRESS_AUTOCOMPLETE_VERSION,
			true
		);

		wp_enqueue_script(
			Plugin::instance()->slug,
			Plugin::instance()->plugin_url( 'assets/dist/frontend.v3.bundle.js' ),
			array(
				'jet-form-builder-frontend-forms',
			),
			JET_FB_ADDRESS_AUTOCOMPLETE_VERSION,
			true
		);

		$api_key = AddressAutocomplete::instance()->key( 'jfb-address-tab' );

		if ( ! $api_key || AddressAutocomplete::instance()->key( 'jfb-address-tab', 'disable_js' ) ) {
			return;
		}
		wp_dequeue_script( 'jet-engine-google-maps-api' );
		wp_deregister_script( 'jet-engine-google-maps-api' );

		wp_enqueue_script(
			'jet-engine-google-maps-api',
			sprintf( AddressAutocomplete::API_URl, $api_key, 'initJFBAutocomplete' ),
			array(
				Plugin::instance()->slug . '-init',
			),
			false,
			true
		);
	}
}
