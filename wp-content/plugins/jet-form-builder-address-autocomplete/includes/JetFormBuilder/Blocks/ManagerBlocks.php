<?php


namespace Jet_FB_Address_Autocomplete\JetFormBuilder\Blocks;


use JetAddressAutocompleteCore\JetFormBuilder\BlocksManager;
use Jet_FB_Address_Autocomplete\Plugin;

class ManagerBlocks extends BlocksManager {

	public function fields() {
		return array(
			new AddressBlock()
		);
	}

	/**
	 * @return void
	 */
	public function before_init_editor_assets() {
		wp_enqueue_script(
			Plugin::instance()->slug . '-editor',
			Plugin::instance()->plugin_url( 'assets/dist/builder.editor.bundle.js' ),
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