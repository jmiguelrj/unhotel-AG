<?php


namespace Jet_FB_Address_Autocomplete\JetEngine\FormTabs;


use JetAddressAutocompleteCore\JetEngine\RegisterFormTabs;

class ManagerTabs {

	use RegisterFormTabs;

	public function tabs(): array {
		return array(
			new AddressTab()
		);
	}

	public function plugin_version_compare(): string {
		return '2.8.3';
	}

	public function on_base_need_update() {
		$this->add_admin_notice( 'warning', __(
			'<b>Warning</b>: <b>JetFormBuilder Address Autocomplete</b> needs <b>JetEngine</b> update.',
			'jet-form-builder-address-autocomplete'
		) );
	}

	public function on_base_need_install() {
	}

}