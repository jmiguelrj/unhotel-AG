<?php


namespace Jet_FB_Address_Autocomplete\JetEngine\FormTabs;


use Jet_Engine\Modules\Forms\Tabs\Base_Form_Tab;
use Jet_FB_Address_Autocomplete\Traits\AddressTabTrait;
use Jet_FB_Address_Autocomplete\Plugin;


class AddressTab extends Base_Form_Tab {

	use AddressTabTrait;

	public function slug(): string {
		return 'jef-address-tab';
	}

	public function render_assets() {
		wp_enqueue_script(
			Plugin::instance()->slug . "-{$this->slug()}",
			Plugin::instance()->plugin_url( 'assets/dist/engine.admin.bundle.js' ),
			array(),
			Plugin::instance()->get_version(),
			true
		);
	}
}