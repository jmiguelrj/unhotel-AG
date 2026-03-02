<?php

namespace JFB_Formless\Modules\Listeners\Adapters;

class RESTToSendJSON {

	public function send( \WP_REST_Response $response ) {
		$ajax_response = array();

		$data = $response->get_data();

		if ( isset( $data['message'] ) ) {
			$ajax_response['message'] = $data['message'];
			unset( $data['message'] );
		}

		if ( isset( $data['code'] ) ) {
			$ajax_response['code'] = $data['code'];
			unset( $data['code'] );
		}

		if ( isset( $data['data'] ) ) {
			$ajax_response['data'] = $data['data'];
			unset( $data['data'] );
		}

		if ( ! empty( $data ) ) {
			if ( empty( $ajax_response['data'] ) ) {
				$ajax_response['data'] = array();
			}

			$ajax_response['data'] = array_merge(
				$ajax_response['data'],
				$data
			);
		}

		wp_send_json(
			$ajax_response,
			$response->get_status()
		);
	}

}
