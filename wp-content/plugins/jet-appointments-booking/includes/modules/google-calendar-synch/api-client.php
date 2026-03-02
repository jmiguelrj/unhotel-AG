<?php
namespace Crocoblock\Google_Calendar_Synch;

class API_Client {

	protected $token;
	protected $base_url = 'https://www.googleapis.com/calendar/v3/';

	public function __construct( $args = [] ) {
		$this->token = ! empty( $args['token'] ) ? $args['token'] : null;
	}

	/**
	 * Perform get request to give endpoint with given params.
	 *
	 * @param  string $endpoint
	 * @param  array $params
	 * @return mixed
	 */
	public function get_request( $endpoint, $params = [] ) {

		$url = $this->base_url . $endpoint;

		if ( ! empty( $params ) ) {
			$url = add_query_arg( $params, $url );
		}

		if ( $this->token && is_wp_error( $this->token ) ) {
			throw new \Exception( esc_html( $this->token->get_error_message() ) );
		}

		$options = [
			'headers' => [
				'Authorization' => 'Bearer ' . $this->token,
			],
			'timeout' => 15,
		];

		$response = wp_remote_get( $url, $options );

		if ( is_wp_error( $response ) ) {
			throw new \Exception( esc_html( $response->get_error_message() ) );
		}

		return json_decode( wp_remote_retrieve_body( $response ), true );
	}

	/**
	 * Perform post request to give endpoint with given params.
	 *
	 * @param  string $endpoint
	 * @param  array $body
	 * @return mixed
	 */
	public function post_request( $endpoint, $body = [] ) {

		$url = $this->base_url . $endpoint;

		$options = [
			'headers' => [
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $this->token,
			],
			'timeout' => 30,
			'body'    => json_encode( $body ),
		];

		$response = wp_remote_post( $url, $options );

		if ( is_wp_error( $response ) ) {
			throw new \Exception( esc_html( $response->get_error_message() ) );
		}

		return json_decode( wp_remote_retrieve_body( $response ), true );
	}

	/**
	 * Create event in the given calendar with the given data.
	 *
	 * @param  string $calendar_id Calendar ID to put event into.
	 * @param  array  $event_data  Event data.
	 * @param  bool   $create_meet Whether to create a meet with an event.
	 * @return mixed
	 */
	public function create_event( $calendar_id = '', $event_data = [], $create_meet = false ) {

		$endpoint = 'calendars/' . urlencode( $calendar_id ) . '/events';

		if ( true === $create_meet ) {
			$event_data['conferenceData'] = [
				'createRequest' => [
					'requestId' => uniqid( 'gc', true ),
					'conferenceSolutionKey' => [
						'type' => 'hangoutsMeet',
					],
				],
			];

			$endpoint .= '?conferenceDataVersion=1';
		}

		return $this->post_request( $endpoint, $event_data );
	}

	/**
	 * Remove event in the given calendar.
	 *
	 * @param  string $calendar_id Calendar ID to put event into.
	 * @param  string $event_id  ID of calendar event.
	 */
	public function remove_event( $calendar_id = '', $event_id = '' ) {

		$url = $this->base_url . 'calendars/' . urlencode( $calendar_id ) . '/events/' . $event_id;

		$options = [
			'method' => 'DELETE',
			'headers' => [
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $this->token,
			],
			'timeout' => 30,
		];

		wp_remote_request( $url, $options );

	}

	/**
	 * Moves an event to given calendar ID.
	 *
	 * @param  string $appointtment_calendar_id Calendar ID of the source calendar where the event currently is on.
	 * @param  string $event_id  ID of calendar event.
	 * @param  string $new_calendar_id  Calendar ID where the event is to be moved to.
	 */
	public function move_event( $appointtment_calendar_id = '', $event_id = '', $new_calendar_id = '' ) {

		$destination_id = '?destination=' . $new_calendar_id;

		$url = $this->base_url . 'calendars/' . urlencode( $appointtment_calendar_id ) . '/events/' . $event_id . '/move' . $destination_id;

		$options = [
			'method' => 'POST',
			'headers' => [
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $this->token,
			],
			'timeout' => 30,
		];

		wp_remote_request( $url, $options );

	}

	/**
	 * Update event in the given calendar with the given data.
	 *
	 * @param  string $calendar_id Calendar ID to put event into.
	 * @param  string $event_id  ID of calendar event.
	 * @param  array  $event_data  Event data.
	 */
	public function update_event( $calendar_id, $event_id, $event_data ) {

		$url = $this->base_url . 'calendars/' . urlencode( $calendar_id ) . '/events/' . $event_id;

		$options = [
			'method' => 'PUT',
			'headers' => [
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $this->token,
			],
			'timeout' => 30,
			'body'    => json_encode( $event_data )
		];

		$response = wp_remote_request( $url, $options );

		if ( is_wp_error( $response ) ) {
			throw new \Exception( esc_html( $response->get_error_message() ) );
		}
	}

	/**
	 * Get events from the given calendar.
	 *
	 * @param  string $calendar_id Calendar ID to get events from.
	 * @param  int    $max_results Max results to return.
	 * @return mixed
	 */
	public function get_events( $calendar_id = '', $max_results = 100 ) {

		$endpoint = 'calendars/' . urlencode( $calendar_id ) . '/events';

		$params = [
			'maxResults'   => (string) $max_results,
			'singleEvents' => 'true', // Google API expects string true/false in the request.
			'timeZone'     => 'UTC',
			'timeMin'      => urlencode( gmdate('c') ),
		];

		return $this->get_request( $endpoint, $params );
	}

	/**
	 * Get available calendars list for the authenticated user.
	 *
	 * @return array
	 */
	public function get_available_calendars() {

		$response = $this->get_request( 'users/me/calendarList' );

		if ( $response && isset( $response['error'] ) ) {
			throw new \Exception( esc_html( $response['error']['message'] ) );
		}

		return $response['items'];
	}
}
