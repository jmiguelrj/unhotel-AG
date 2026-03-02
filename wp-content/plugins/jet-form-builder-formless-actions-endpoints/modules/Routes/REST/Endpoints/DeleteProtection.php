<?php

namespace JFB_Formless\Modules\Routes\REST\Endpoints;

use Jet_Form_Builder\Exceptions\Repository_Exception;
use JFB_Formless\REST\Interfaces\EndpointInterface;
use JFB_Modules\Post_Type\Module;

class DeleteProtection implements EndpointInterface {

	public function get_method(): string {
		return \WP_REST_Server::DELETABLE;
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
	 * @throws Repository_Exception
	 */
	public function process( \WP_REST_Request $request ): \WP_REST_Response {
		jet_fb_live()->form_id = absint( $request->get_param( 'form_id' ) );

		$this->remove_validation_args();

		return new \WP_REST_Response( array(), 204 );
	}

	/**
	 * @return void
	 * @throws Repository_Exception
	 */
	private function remove_captcha() {
		/** @var Module $module */
		$module  = jet_form_builder()->module( 'post-type' );
		$captcha = $module->get_captcha();

		if ( empty( $captcha['captcha'] ) ) {
			return;
		}

		$captcha['captcha'] = '';

		update_post_meta(
			jet_fb_live()->form_id,
			'_jf_recaptcha',
			wp_json_encode( $captcha )
		);
	}

	/**
	 * @return void
	 * @throws Repository_Exception
	 */
	private function remove_validation_args() {
		/** @var Module $module */
		$module = jet_form_builder()->module( 'post-type' );
		$args   = $module->get_args();

		if ( isset( $args['load_nonce'] ) ) {
			$args['load_nonce'] = 'hide';

		}
		if ( isset( $args['use_csrf'] ) ) {
			unset( $args['use_csrf'] );
		}

		update_post_meta(
			jet_fb_live()->form_id,
			'_jf_args',
			wp_json_encode( $args )
		);
	}
}
