<?php
/**
 * Google Calendar Sync module
 *
 * Version: 1.0.0
 *
 * Instructions:
 * 1. Create a Google Cloud project and enable the Google Calendar API.
 * 2. Create OAuth 2.0 credentials and set the redirect URI to your plugin's URL.
 * 3. Install the Google API Auth library via Composer or manually.
 * 4. Include this file in your plugin and instantiate the Controller class.
 * 5. Use get_auth
 */
namespace Crocoblock\Google_Calendar_Synch;

use Google\Auth\OAuth2;

class Controller {

	/**
	 * Module slug
	 *
	 * @var string
	 */
	public $slug = 'google-calendar-synch';

	/**
	 * Module version
	 *
	 * @var string
	 */
	public $version = '1.0.0';

	/**
	 * Module name
	 *
	 * @var string
	 */
	public $name = 'Google Calendar Sync';

	/**
	 * Module description
	 *
	 * @var string
	 */
	public $description = 'Synchronize anything with Google Calendar.';

	/**
	 * Module app slug
	 *
	 * @var string
	 */
	public $app_slug = 'google-calendar-synch';

	protected $client_id;
	protected $client_secret;
	protected $auth_callback = null;
	protected $module_dir = null;

	public function __construct( $args = [] ) {

		$app_slug = ! empty( $args['app_slug'] ) ? $args['app_slug'] : 'google-calendar-synch';
		$this->app_slug = $app_slug;
		$this->client_id = ! empty( $args['client_id'] ) ? $args['client_id'] : '';
		$this->client_secret = ! empty( $args['client_secret'] ) ? $args['client_secret'] : '';

		if ( ! class_exists( 'Google\Auth\OAuth2' ) ) {
			add_action( 'admin_notices', function() {
				echo '<div class="notice notice-error"><p>' . esc_html__( 'Google Calendar Sync requires the Google API PHP Client library. Please install it or make sure it is included properly.', 'jet-google-calendar-synch' ) . '</p></div>';
			} );
		}

		// phpcs:disable WordPress.Security.NonceVerification
		if ( ! empty( $_GET['app'] ) && $_GET['app'] === $this->app_slug ) {
			add_action( 'admin_action_jet-google-calendar-synch', function() {
				if ( ! empty( $_GET['code'] ) && is_callable( $this->auth_callback ) ) {
					$callback = $this->auth_callback;
					$callback( $_GET, [ $this, 'save_token' ] );
				}
			} );
		}
		// phpcs:enable WordPress.Security.NonceVerification

		$this->module_dir = trailingslashit( plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Save the token to the database.
	 *
	 * @param string $code A reponse code to get token by.
	 * @param string $redirects Redirect URLs.
	 * @return void
	 */
	public function save_token( $code, $redirects = [] ) {

		$auth_client = $this->get_oauth2_client();
		$auth_client->setCode( $code );
		$token_data = $auth_client->fetchAuthToken();

		if ( isset( $token_data['error'] ) ) {
			return;
		}

		$context_key = $this->app_slug . '_context';
		$nonce_key   = $this->app_slug . '_context_nonce';

		$context_cookie = ! empty( $_COOKIE[ $context_key ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ $context_key ] ) ) : '';
		$context_nonce = ! empty( $_COOKIE[ $nonce_key ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ $nonce_key ] ) ) : '';

		if ( empty( $context_cookie ) || empty( $context_nonce ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $context_nonce, $this->app_slug . $context_cookie ) ) {
			return;
		}

		$context      = explode( ':', $context_cookie );
		$store_type   = ! empty( $context[0] ) ? $context[0] : 'global';
		$store_object = ! empty( $context[1] ) ? $context[1] : '';

		$this->save_token_data( $token_data, $store_type, $store_object );

		$redirect_url = ! empty( $redirects[ $store_type ] ) ? $redirects[ $store_type ] : false;

		if ( $redirect_url ) {

			$redirect_url = str_replace(
				[
					'%edit_post_link%',
					'%context_object%',
					'%context_type%'
				],
				[
					get_edit_post_link( $store_object, 'redirect' ),
					$store_object,
					$store_type
				],
				$redirect_url
			);

			wp_redirect( $redirect_url ); // phpcs:ignore
			exit;
		}
	}

	/**
	 * Save token data to DB according context.
	 *
	 * @param array  $token_data
	 * @param string $store_type
	 * @param string $store_object
	 * @return void
	 */
	public function save_token_data( $token_data, $store_type = 'global', $store_object = '' ) {

		$store_key = $this->app_slug . '-token';

		if ( isset( $token_data['expires_in'] ) ) {
			$token_data['expires_at'] = time() + $token_data['expires_in'];
			unset( $token_data['expires_in'] );
		}

		switch ( $store_type ) {
			case 'global':
				update_option( $store_key, $token_data );
				break;

			case 'post':
				if ( ! empty( $store_object ) ) {
					update_post_meta( $store_object, $store_key, $token_data );
				}
				break;

			case 'user':
				$store_object = ! empty( $store_object ) ? $store_object : get_current_user_id();
				update_user_meta( $store_object, $store_key, $token_data );
				break;
		}
	}

	/**
	 * Get the Google API client.
	 *
	 * Allowed $context keys:
	 * - type:   'global', 'post', 'user'
	 * - object: ID of the post or user
	 *
	 * @param array $context The context for the storage (e.g. global, post, user and their IDs).
	 * @return API_Client|null
	 */
	public function get_api_client( $context = [] ) {

		if ( ! class_exists( '\Crocoblock\Google_Calendar_Synch\API_Client' ) ) {
			require_once $this->module_dir . 'api-client.php';
		}

		if ( ! class_exists( '\Crocoblock\Google_Calendar_Synch\Helper' ) ) {
			require_once $this->module_dir . 'helper.php';
		}

		$type = ! empty( $context['type'] ) ? $context['type'] : 'global';
		$context_object = ! empty( $context['object'] ) ? $context['object'] : '';

		$token = $this->get_token( $type, $context_object );
		$token_data = $this->get_token_data( $type, $context_object );

		if ( ! $token && ! empty( $token_data ) && isset( $token_data['refresh_token'] ) ) {
			$token = $this->refresh_token( $token_data['refresh_token'], $type, $context_object );
		}

		if ( ! empty( $token ) ) {
			$client = new API_Client( [
				'token' => $token,
			] );

			return $client;
		} else {
			return null;
		}
	}

	/**
	 * Refresh the token using the refresh token.
	 *
	 * @param string $refresh_token The refresh token.
	 * @param string $type The type of storage (e.g., 'global', 'post', 'user').
	 * @param string $object The object for the storage (e.g., post ID, user ID).
	 * @return array|WP_Error
	 */
	public function refresh_token( $refresh_token, $type = 'global', $object = '' ) {

		$response = wp_remote_post(
			'https://oauth2.googleapis.com/token',
			[
				'headers' => [
					'Content-Type' => 'application/x-www-form-urlencoded'
				],
				'body' => [
					'grant_type'    => 'refresh_token',
					'refresh_token' => $refresh_token,
					'client_id'     => $this->client_id,
					'client_secret' => $this->client_secret,
				],
			]
		);

		if ( is_wp_error( $response ) ) {
			return new \WP_Error('http_error', 'Could not reach Google', $response->get_error_message());
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $body['error'] ) ) {
			return new \WP_Error(
				'google_error',
				'Token refresh failed. Please click the Disconnect link and try to connect again.',
				$body['error_description'] ?? $body['error']
			);
		}

		$body['refresh_token'] = $refresh_token;

		$this->save_token_data( $body, $type, $object );
		return $body['access_token'];
	}

	/**
	 * Get the token data from the database.
	 *
	 * @param string $store_type Type of storage (e.g., 'global', 'post', 'user').
	 * @param string $store_object Context for the storage (e.g., post ID, user ID).
	 * @return mixed
	 */
	public function get_token_data( $store_type = 'global', $store_object = '' ) {

		$store_key = $this->app_slug . '-token';

		switch ( $store_type ) {
			case 'global':
				return get_option( $store_key );

			case 'post':
				if ( ! empty( $store_object ) ) {
					return get_post_meta( $store_object, $store_key, true );
				}
				break;

			case 'user':
				$store_object = ! empty( $store_object ) ? $store_object : get_current_user_id();
				return get_user_meta( $store_object, $store_key, true );
		}

		return null;
	}

	/**
	 * Clear the token data from the database.
	 *
	 * @param string $store_type Type of storage (e.g., 'global', 'post', 'user').
	 * @param string $store_object Context for the storage (e.g., post ID, user ID).
	 * @return void
	 */
	public function clear_token_data( $store_type = 'global', $store_object = '' ) {

		$store_key = $this->app_slug . '-token';

		// First - revoke existing token for given context.
		$token = $this->get_token( $store_type, $store_object );

		wp_remote_post( 'https://oauth2.googleapis.com/revoke', [
			'headers' => [
				'Content-Type' => 'application/x-www-form-urlencoded',
			],
			'body' => [
				'token' => $token,
			],
		] );

		// Then - delete token data from DB.
		switch ( $store_type ) {
			case 'global':
				delete_option( $store_key );
				break;

			case 'post':
				if ( ! empty( $store_object ) ) {
					delete_post_meta( $store_object, $store_key );
				}
				break;

			case 'user':
				$store_object = ! empty( $store_object ) ? $store_object : get_current_user_id();
				delete_user_meta( $store_object, $store_key );
				break;
		}
	}

	/**
	 * Get the token from the database.
	 *
	 * @param string $store_type Type of storage (e.g., 'global', 'post', 'user').
	 * @param string $store_object Object for the storage (e.g., post ID, user ID).
	 * @return mixed
	 */
	public function get_token( $store_type = 'global', $store_object = '' ) {

		$token_data = $this->get_token_data( $store_type, $store_object );

		if ( ! is_array( $token_data ) ) {
			return null;
		}

		if ( ! empty( $token_data ) && isset( $token_data['access_token'] ) ) {

			if ( isset( $token_data['expires_at'] ) && time() > $token_data['expires_at'] ) {
				if ( ! empty( $token_data['refresh_token'] ) ) {
					$token = $this->refresh_token(
						$token_data['refresh_token'],
						$store_type,
						$store_object
					);
				}

				return ! empty( $token ) ? $token : null;
			}

			return $token_data['access_token'];
		}

		return null;
	}

	/**
	 * Check if Google calendar is connected to given context.
	 *
	 * @param string $type
	 * @param string $object
	 * @return boolean
	 */
	public function is_connected( $type = 'global', $object = '' ) {
		$token = $this->get_token( $type, $object );
		return ! empty( $token );
	}

	/**
	 * Register the authorization callback.
	 *
	 * @param callable $callback The callback function to handle authorization.
	 */
	public function register_auth_callback( $callback ) {
		if ( is_callable( $callback ) ) {
			$this->auth_callback = $callback;
		}
	}

	/**
	 * Set the value of a class property.
	 *
	 * @param  string $prop  Property name.
	 * @param  mixed  $value Property value.
	 * @return void
	 */
	public function set( $prop, $value ) {
		if ( property_exists( $this, $prop ) ) {
			$this->$prop = $value;
		}
	}

	/**
	 * Get redirect URL to return after the authorization.
	 *
	 * @return string
	 */
	public function get_redirect_url() {
		$url = admin_url( 'admin.php?action=jet-google-calendar-synch&app=' . $this->app_slug );
		return $url;
	}

	/**
	 * Get the OAuth2 client.
	 *
	 * @return OAuth2
	 */
	public function get_oauth2_client() {
		$args = [
			'clientId'           => $this->client_id,
			'clientSecret'       => $this->client_secret,
			'authorizationUri'   => 'https://accounts.google.com/o/oauth2/v2/auth',
			'tokenCredentialUri' => 'https://oauth2.googleapis.com/token',
			'redirectUri'        => $this->get_redirect_url(),
			'scope' => [
				'https://www.googleapis.com/auth/calendar'
			],
		];

		return new OAuth2( $args );
	}

	/**
	 * Set context for the OAuth2 client into the cookie.
	 *
	 * @param array $context Context for the OAuth2 client.
	 */
	public function set_cookie_context( $context ) {

		$context_type   = ! empty( $context['type'] ) ? $context['type'] : 'global';
		$context_object = ! empty( $context['object'] ) ? $context['object'] : '';

		$context_value = sprintf(
			'%s:%s',
			$context_type,
			$context_object
		);

		$context_nonce = wp_create_nonce( $this->app_slug . $context_value );

		setcookie(
			$this->app_slug . '_context',
			$context_value,
			time() + 3600,
			COOKIEPATH,
			COOKIE_DOMAIN,
			is_ssl(),
			true
		);

		setcookie(
			$this->app_slug . '_context_nonce',
			$context_nonce,
			time() + 3600,
			COOKIEPATH,
			COOKIE_DOMAIN,
			is_ssl(),
			true
		);
	}
}
