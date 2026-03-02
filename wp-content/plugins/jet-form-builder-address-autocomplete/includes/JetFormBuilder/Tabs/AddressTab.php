<?php


namespace Jet_FB_Address_Autocomplete\JetFormBuilder\Tabs;


use Jet_FB_Address_Autocomplete\Traits\AddressTabTrait;
use Jet_FB_Address_Autocomplete\Plugin;
use Jet_Form_Builder\Admin\Tabs_Handlers\Base_Handler;

class AddressTab extends Base_Handler {

	use AddressTabTrait;

	public function slug() {
		return 'jfb-address-tab';
	}

	public function before_assets() {
		wp_enqueue_script(
			Plugin::instance()->slug . "-{$this->slug()}",
			Plugin::instance()->plugin_url( 'assets/dist/builder.admin.bundle.js' ),
			array(),
			Plugin::instance()->get_version(),
			true
		);
	}
}