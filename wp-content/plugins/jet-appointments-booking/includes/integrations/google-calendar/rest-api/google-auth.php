<?php
namespace JET_APB\Integrations\Google_Calendar\Rest_API;

use JET_APB\Integrations\Manager as Integrations_Manager;
use JET_APB\Integrations\Google_Calendar\Calendar_Meta_Box;

class Google_Auth extends \Jet_Engine_Base_API_Endpoint {

	protected $google_calendar_module = null;

	/**
	 * Returns route name
	 *
	 * @return string
	 */
	public function get_name() {
		return 'appointment-google-auth';
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

		$settings = isset( $params['settings'] ) ? $params['settings'] : [];
		$g_cal = Integrations_Manager::instance()->get_integrations( 'google-calendar' );
		$saved_settings = $g_cal->get_data();
		$action = ! empty( $params['action'] ) ? $params['action'] : 'authorize';

		$settings = array_merge( $saved_settings, $settings );

		$client_id     = isset( $settings['client_id'] ) ? $settings['client_id'] : '';
		$client_secret = isset( $settings['client_secret'] ) ? $settings['client_secret'] : '';

		if ( empty( $client_id ) || empty( $client_secret ) ) {
			return rest_ensure_response( [
				'success' => false,
				'message' => __(
					'Please provide Client ID and Client Secret',
					'jet-appointments-booking'
				),
			] );
		}

		$this->google_calendar_module->set( 'client_id', $client_id );
		$this->google_calendar_module->set( 'client_secret', $client_secret );

		$context = [
			'type' => ! empty( $params['context_type'] ) ? $params['context_type'] : 'global',
			'object' => ! empty( $params['context_object'] ) ? $params['context_object'] : '',
		];

		if ( 'disconnect' === $action ) {

			$this->disconnect( $context );

			return rest_ensure_response( [
				'success' => true,
				'message' => __( 'Google Calendar disconnected', 'jet-appointments-booking' ),
			] );
		}

		$oauth2 = $this->google_calendar_module->get_oauth2_client();

		$this->google_calendar_module->set_cookie_context( $context );

		$auth_url = $oauth2->buildFullAuthorizationUri( [
			'access_type' => 'offline',
			'prompt'      => 'consent',
		] );

		return rest_ensure_response( [
			'success' => true,
			'redirect' => (string) $auth_url,
		] );
	}

	/**
	 * Disconnect context from Google account
	 *
	 * @param array $context Context data.
	 */
	public function disconnect( $context ) {

		$this->google_calendar_module->clear_token_data( $context['type'], $context['object'] );

		if ( 'post' === $context['type'] && ! empty( $context['object'] ) ) {
			$existing_meta = Calendar_Meta_Box::get_meta( $context['object'] );
			$existing_meta['use_local_connection'] = false;
			$existing_meta['calendar_id']     = '';
			Calendar_Meta_Box::update_meta( $context['object'], $existing_meta );
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
		return 'POST';
	}

	/**
	 * Returns arguments config
	 *
	 * @return array
	 */
	public function get_args() {
		return [
			'settings' => [
				'default'  => [],
				'required' => false,
			],
			'action' => [
				'default'  => 'authorize',
				'required' => false,
			],
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