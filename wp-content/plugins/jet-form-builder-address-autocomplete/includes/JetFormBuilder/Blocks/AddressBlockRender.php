<?php


namespace Jet_FB_Address_Autocomplete\JetFormBuilder\Blocks;

use Jet_FB_Address_Autocomplete\Traits\AddressFieldRenderTrait;
use Jet_Form_Builder\Blocks\Dynamic_Value;
use Jet_Form_Builder\Blocks\Render\Base;
use JetAddressAutocompleteCore\JetFormBuilder\RenderBlock;

class AddressBlockRender extends Base {

	use RenderBlock;
	use AddressFieldRenderTrait {
		AddressFieldRenderTrait::attributes_values insteadof RenderBlock;
	}

	public function set_up() {
		$this->args = $this->block_type->block_attrs;

		if ( ! class_exists( '\Jet_Form_Builder\Blocks\Dynamic_Value' ) ||
			! jet_form_builder()->regexp->has_macro( $this->args['default'] )
		) {
			return $this;
		}

		wp_enqueue_script( Dynamic_Value::HANDLE );

		$this->args['data-value'] = $this->args['default'];
		$this->args['default']    = '';

		return $this;
	}

	public function get_name() {
		return 'address-field';
	}

	public function get_address_autocomplete_args() {
		return esc_attr(
			wp_json_encode(
				$this->get_args(
					array(
						'countries',
						'types',
					)
				)
			)
		);
	}


}
