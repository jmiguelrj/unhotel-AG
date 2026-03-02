<?php


namespace Jet_FB_Address_Autocomplete;


trait CssSelector {

	public function selector( $selector = '' ) {
		return "{{WRAPPER}} .jet-address-autocomplete + .jet-adr-list{$selector}";
	}

	public function uniq_id( $suffix ) {
		return "jet_fb_address_autocomplete__{$suffix}";
	}

}