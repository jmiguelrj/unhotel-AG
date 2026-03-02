<?php


namespace Jet_FB_Address_Autocomplete\JetFormBuilder\Tabs;


use JetAddressAutocompleteCore\JetFormBuilder\RegisterFormTabs;

class ManagerTabs {

	use RegisterFormTabs;

	public function tabs(): array {
		return array(
			new AddressTab()
		);
	}

	public function plugin_version_compare() {
		return '1.2.2';
	}

	public function on_base_need_update() {
		$this->add_admin_notice( 'warning', __(
			'<b>Warning</b>: <b>JetFormBuilder Address Autocomplete</b> needs <b>JetFormBuilder</b> update.',
			'jet-form-builder-address-autocomplete'
		) );
	}

	public function on_base_need_install() {
	}
}