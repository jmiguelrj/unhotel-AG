<?php


namespace Jet_FB_Address_Autocomplete\Traits;

trait AddressFieldRenderTrait {

	abstract public function get_address_autocomplete_args();

	public function attributes_values() {
		$settings = $this->get_address_autocomplete_args();

		return array(
			'data-jfb-sync'         => true,
			'data-address-settings' => $settings,
			'class'                 => array( 'jet-address-autocomplete' ),
			'placeholder'           => $this->get_arg( 'placeholder', 'Type address...' ),
			'type'                  => 'text',
			'autocomplete'          => 'false',
		);
	}

	public function render_field( $attrs_string ) {
		return "<input $attrs_string />";
	}

}
