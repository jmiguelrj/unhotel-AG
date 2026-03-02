<?php


namespace Jet_FB_Address_Autocomplete\Traits;

trait AddressTabTrait {

	public function on_get_request() {
		$api_key    = sanitize_text_field( $_POST['api_key'] );
		$disable_js = 'true' === sanitize_key( $_POST['disable_js'] ?? '' );

		$result = $this->update_options(
			array(
				'api_key'    => $api_key,
				'disable_js' => $disable_js,
			)
		);

		$result ? wp_send_json_success(
			array(
				'message' => __( 'Saved successfully!', 'jet-form-builder' ),
			)
		) : wp_send_json_error(
			array(
				'message' => __( 'Unsuccessful save.', 'jet-form-builder' ),
			)
		);
	}

	public function on_load() {
		return $this->get_options(
			array(
				'api_key'    => '',
				'disable_js' => false,
			)
		);
	}

}
