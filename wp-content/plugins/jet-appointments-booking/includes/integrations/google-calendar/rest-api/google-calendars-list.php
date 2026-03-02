<?php
namespace JET_APB\Integrations\Google_Calendar\Rest_API;

class Google_Calendars_List extends \Jet_Engine_Base_API_Endpoint {

	protected $google_calendar_module = null;

	/**
	 * Returns route name
	 *
	 * @return string
	 */
	public function get_name() {
		return 'appointment-google-calendars-list';
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

		$params = $request->get_params();
		$context_type = ! empty( $params['context_type'] ) ? $params['context_type'] : 'global';
		$context_object = ! empty( $params['context_object'] ) ? $params['context_object'] : '';

		$api_client = $this->google_calendar_module->get_api_client( [
			'type'   => $context_type,
			'object' => $context_object,
		] );

		if ( empty( $api_client ) ) {
			return rest_ensure_response( [
				'success' => false,
				'message' => __( 'Google Calendar API client not initialized', 'jet-appointments-booking' ),
			] );
		}

		try {
			$calendars = $api_client->get_available_calendars();

			if ( empty( $calendars ) ) {
				return rest_ensure_response( [
					'success' => false,
					'message' => __( 'No calendars found', 'jet-appointments-booking' ),
				] );
			}

			$calendars = array_map( function( $calendar ) {
				return [
					'id'    => $calendar['id'],
					'name'  => $calendar['summary'],
				];
			}, $calendars );

			return rest_ensure_response( [
				'success'   => true,
				'calendars' => array_values( $calendars ),
			] );
		} catch ( \Exception $e ) {
			return rest_ensure_response( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	/**
	 * Check user access to current end-popint
	 *
	 * @return bool
	 */
	public function permission_callback( $request ) {

		$params       = $request->get_params();
		$context_type = ! empty( $params['context_type'] ) ? $params['context_type'] : 'global';

		if ( 'global' === $context_type ) {
			return current_user_can( 'manage_options' );
		} elseif ( 'post' === $context_type ) {

			$post_id = ! empty( $params['context_object'] ) ? absint( $params['context_object'] ) : 0;

			if ( ! empty( $post_id ) && is_numeric( $post_id ) ) {
				return current_user_can( 'edit_post', $post_id );
			} else {
				return false;
			}
		} elseif ( 'user' === $context_type ) {
			return current_user_can( 'manage_options' );
		} else {
			// No other context are allowed at this moment
			return false;
		}
	}

	/**
	 * Returns endpoint request method - GET/POST/PUT/DELTE
	 *
	 * @return string
	 */
	public function get_method() {
		return 'GET';
	}

	/**
	 * Returns arguments config
	 *
	 * @return array
	 */
	public function get_args() {
		return [
			'context_type' => [
				'default'  => 'global',
				'required' => true,
			],
			'context_object' => [
				'default'  => '',
				'required' => false,
			],
		];
	}

}