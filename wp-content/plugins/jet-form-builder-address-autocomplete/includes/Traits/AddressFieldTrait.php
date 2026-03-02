<?php


namespace Jet_FB_Address_Autocomplete\Traits;


trait AddressFieldTrait {

	/**
	 * Returns current block render instance
	 *
	 * @return string
	 */
	public function get_template() {
		return $this->render_instance()->set_up()->complete_render();
	}

}