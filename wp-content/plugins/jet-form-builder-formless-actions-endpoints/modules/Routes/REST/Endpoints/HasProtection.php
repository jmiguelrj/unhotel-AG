<?php

namespace JFB_Formless\Modules\Routes\REST\Endpoints;

use Jet_Form_Builder\Exceptions\Repository_Exception;
use JFB_Formless\REST\Interfaces\EndpointInterface;
use JFB_Modules\Captcha\Module as CaptchaModule;

class HasProtection implements EndpointInterface {

	public function get_method(): string {
		return \WP_REST_Server::READABLE;
	}

	public function has_permission(): bool {
		return current_user_can( 'manage_options' );
	}

	public function get_args(): array {
		return array();
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function process( \WP_REST_Request $request ): \WP_REST_Response {
		jet_fb_live()->form_id = absint( $request->get_param( 'form_id' ) );
		jet_fb_live()->set_specific_data_for_render();

		if (
			jet_fb_live_args()->is_use_csrf() ||
			jet_fb_live_args()->is_use_nonce()
		) {
			return new \WP_REST_Response(
				array(
					'hasProtection' => true,
					'csrf'          => jet_fb_live_args()->is_use_csrf(),
					'nonce'         => jet_fb_live_args()->is_use_nonce(),
				)
			);
		}

		return new \WP_REST_Response( array( 'hasProtection' => false ) );
	}
}
