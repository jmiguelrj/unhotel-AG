<?php
namespace JET_APB\Integrations\Google_Calendar\Rest_API;

use JET_APB\Integrations\Google_Calendar\Calendar_Meta_Box;

class Google_Calendars_Meta extends \Jet_Engine_Base_API_Endpoint {

	protected $google_calendar_module = null;

	/**
	 * Returns route name
	 *
	 * @return string
	 */
	public function get_name() {
		return 'appointment-google-calendars-meta';
	}

	/**
	 * Set Google Calendar module
	 *
	 * @param object $google_calendar_module Google Calendar module instance.
	 */
	public function set_google_calendar_module( $google_calendar_module ) {
		$this->google_calendar_module = $google_calendar_module;
	}

	/**
	 * API callback
	 *
	 * @return void
	 */
	public function callback( $request ) {

		$params  = $request->get_params();
		$post_id = ! empty( $params['post_id'] ) ? absint( $params['post_id'] ) : '';
		$meta    = ! empty( $params['meta_value'] ) ? $params['meta_value'] : [];

		if ( empty( $post_id ) || empty( $meta ) ) {
			return rest_ensure_response( [
				'success' => false,
				'message' => __( 'Post ID or meta value is missing', 'jet-appointments-booking' ),
			] );
		}

		Calendar_Meta_Box::update_meta( $post_id, $meta );

		return rest_ensure_response( [
			'success' => true,
			'message' => __( 'Meta value updated successfully', 'jet-appointments-booking' ),
		] );
	}

	/**
	 * Check user access to current end-popint
	 *
	 * @return bool
	 */
	public function permission_callback( $request ) {

		$params  = $request->get_params();
		$nonce   = ! empty( $params['nonce'] ) ? $params['nonce'] : '';
		$post_id = ! empty( $params['post_id'] ) ? $params['post_id'] : '';

		if ( empty( $nonce ) || empty( $post_id ) ) {
			return false;
		}

		if ( ! wp_verify_nonce( $nonce, Calendar_Meta_Box::$meta_key ) ) {
			return false;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Returns endpoint request method - GET/POST/PUT/DELTE
	 *
	 * @return string
	 */
	public function get_method() {
		return 'POST';
	}

	/**
	 * Returns arguments config
	 *
	 * @return array
	 */
	public function get_args() {
		return [
			'nonce' => [
				'default'  => '',
				'required' => true,
			],
			'post_id' => [
				'default'  => '',
				'required' => true,
			],
			'meta_value' => [
				'default'  => [],
				'required' => true,
			],
		];
	}

}