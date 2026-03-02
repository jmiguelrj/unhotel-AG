<?php


namespace Jet_FB_Address_Autocomplete\JetEngine\Fields;


use Jet_FB_Address_Autocomplete\Traits\AddressFieldTrait;
use JetAddressAutocompleteCore\JetEngine\SingleField;
use Jet_Engine\Modules\Forms\Tabs\Tab_Manager;
use Jet_FB_Address_Autocomplete\AddressAutocomplete;

class AddressField extends SingleField {

	use AddressFieldTrait;

	/**
	 * @return string
	 */
	public function get_name() {
		return 'address_autocomplete';
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return __( 'Address Autocomplete Field' );
	}

	public function render_instance() {
		AddressAutocomplete::instance()->settings( 'jef-address-tab', Tab_Manager::instance() );
		AddressAutocomplete::instance()->enqueue_scripts( 'jef-address-tab' );
		AddressAutocomplete::instance()->enqueue_styles();

		return new AddressRender( $this );
	}
}