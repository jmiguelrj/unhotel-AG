<?php


namespace Jet_FB_Address_Autocomplete\JetEngine\Fields;


use JetAddressAutocompleteCore\JetEngine\FieldsManager;
use Jet_FB_Address_Autocomplete\Plugin;

class ManagerFields extends FieldsManager {

	public function fields() {
		return array(
			new AddressField()
		);
	}

	public function register_assets() {
		wp_enqueue_script(
			Plugin::instance()->slug,
			Plugin::instance()->plugin_url( 'assets/dist/engine.editor.bundle.js' ),
			array(),
			Plugin::instance()->get_version(),
			true
		);
	}

	public function on_base_need_update() {
	}

	public function on_base_need_install() {
	}
}