<?php


namespace Jet_FB_Address_Autocomplete\JetEngine\Fields;


use Jet_FB_Address_Autocomplete\Traits\AddressFieldRenderTrait;
use JetAddressAutocompleteCore\JetEngine\RenderField;

class AddressRender {

	use RenderField;
	use AddressFieldRenderTrait {
		AddressFieldRenderTrait::attributes_values insteadof RenderField;
	}

	public function get_name() {
		return 'address_autocomplete';
	}

	public function get_address_autocomplete_args() {
		return esc_attr( wp_json_encode( $this->get_arg( 'address_autocomplete', array() ) ) );
	}

}