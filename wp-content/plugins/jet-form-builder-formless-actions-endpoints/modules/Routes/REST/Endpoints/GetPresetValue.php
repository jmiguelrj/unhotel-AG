<?php

namespace JFB_Formless\Modules\Routes\REST\Endpoints;

use JFB_Formless\REST\Interfaces\EndpointInterface;

class GetPresetValue implements EndpointInterface {

	public function get_method(): string {
		return \WP_REST_Server::READABLE;
	}

	public function has_permission(): bool {
		return current_user_can( 'manage_options' );
	}

	public function get_args(): array {
		return array(
			'preset' => array(
				'type' => 'object',
			),
		);
	}

	public function process( \WP_REST_Request $request ): \WP_REST_Response {
		$preset = $request->get_param( 'preset' );

		if ( ! is_array( $preset ) ) {
			return new \WP_REST_Response(
				array(
					'code'    => 'invalid_preset',
					'message' => __(
						'Your preset is empty has invalid value',
						'jet-form-builder-formless-actions-endpoints'
					),
				),
				400
			);
		}

		return new \WP_REST_Response(
			array(
				'value' => jet_fb_parse_dynamic( wp_json_encode( $preset ) ),
			)
		);
	}
}
