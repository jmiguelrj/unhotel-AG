<?php

namespace Jet_FB_Login\JetFormBuilder\Actions;

use Jet_FB_Login\JetFormBuilder\ActionsMessages\UserLoginMessages;
use Jet_Form_Builder\Actions\Action_Handler;
use Jet_Form_Builder\Actions\Types\Base;
use Jet_Form_Builder\Exceptions\Action_Exception;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define Base_Type class
 */
class Action extends Base {

	private $message_store;

	public function __construct() {
		parent::__construct();

		// shouldn't be cloned
		$this->message_store = new UserLoginMessages();
	}

	public function get_id() {
		return 'login';
	}

	public function get_name() {
		return __( 'User Login', 'jet-form-builder-login-action' );
	}

	/**
	 * Safe replacement for wp_signon() for WordPress 6.8+
	 *
	 * @param array $credentials Array with keys: 'user_login', 'user_password', 'remember'
	 * @param bool  $secure_cookie Optional. Whether to use a secure cookie. Default: auto-detect via is_ssl().
	 *
	 * @return WP_User|WP_Error
	 */
	function wp_signon_safe( $credentials = [], $secure_cookie = null ) {

		if ( empty( $credentials['user_login'] ) || empty( $credentials['user_password'] ) ) {
			return new \WP_Error( 'empty_credentials', 'Username or password is empty.' );
		}

		$login_value = $credentials['user_login'];
		$password    = $credentials['user_password'];
		$remember    = ! empty( $credentials['remember'] );

		// Auto-detect secure cookie if not explicitly passed
		if ( null === $secure_cookie ) {
			$secure_cookie = is_ssl();
		}

		// Get user by email or login
		$user = is_email( $login_value ) ? get_user_by( 'email', $login_value ) : get_user_by( 'login', $login_value );

		if ( ! $user ) {
			return new \WP_Error( 'invalid_username', 'Invalid username or email.' );
		}

		// Convert email to username if needed (for filters)
		if ( is_email( $login_value ) ) {
			$credentials['user_login'] = $user->user_login;
		}

		$password_check = wp_check_password( $password, $user->user_pass, $user->ID );

		// Default password check
		if ( ! $password_check ) {
			$password_check = wp_check_password( $password, $user->user_pass, $user->ID );

			// If wp_check_password failed and hash is WordPress 6.8+ format ($wp$2y$), try manual verification
			// This matches the exact implementation from wp_check_password() in WordPress core
			$password_hash = $user->user_pass;

			if ( ! $password_check && 0 === strpos( $password_hash, '$wp' ) ) {
				// WordPress 6.8+ uses SHA-384 pre-hashed bcrypt
				// Format: $wp$2y$10$... where $wp is prefix (3 chars), $2y$10$ is bcrypt format
				// The password is first hashed with SHA-384, then bcrypt is applied
				//
				// From wp_check_password() in pluggable.php:
				// $password_to_verify = base64_encode( hash_hmac( 'sha384', $password, 'wp-sha384', true ) );
				// $check = password_verify( $password_to_verify, substr( $hash, 3 ) );

				// Pre-hash password with SHA-384 exactly as WordPress does
				$password_to_verify = base64_encode( hash_hmac( 'sha384', $password, 'wp-sha384', true ) );

				// Remove $wp prefix (first 3 characters)
				$bcrypt_hash = substr( $password_hash, 3 );

				// Now verify the pre-hashed password against the bcrypt hash
				$manual_check = password_verify( $password_to_verify, $bcrypt_hash );

				if ( $manual_check ) {
					wp_set_current_user( $user->ID );
					wp_set_auth_cookie( $user->ID, $remember, $secure_cookie );
					do_action( 'wp_login', $user->user_login, $user );

					return $user;
				} else {
					return new \WP_Error(
						'incorrect_password',
						sprintf(
							'<strong>Error:</strong> The password you entered for the username/email <strong>%s</strong> is incorrect.',
							esc_html( $login_value )
						)
					);
				}
			}
		}
	}

	/**
	 * @param array $request
	 * @param Action_Handler $handler
	 *
	 * @return void
	 * @throws Action_Exception
	 */
	public function do_action( array $request, Action_Handler $handler ) {
		$credentials   = $this->get_credentials();
		$secure_cookie = (bool) ( $this->settings['secure_cookie'] ?? true );

		$user = wp_signon( $credentials, $secure_cookie );

		if ( ! ( $user instanceof \WP_Error ) ) {
			wp_set_current_user( $user->ID );
			return;
		} else {
			$user = $this->wp_signon_safe( $credentials, $secure_cookie );

			if ( ! ( $user instanceof \WP_Error ) ) {
				return;
			}
		}

		if ( empty( $this->settings['use_custom_errors'] ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			throw new Action_Exception( $user->get_error_message() );
		}

		$messages = $this->message_store->get_messages();

		if ( ! array_key_exists(
			UserLoginMessages::PREFIX . $user->get_error_code(),
			$messages
		) ) {
			throw new Action_Exception( 'failed' );
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		throw new Action_Exception( UserLoginMessages::PREFIX . $user->get_error_code() );
	}

	public function get_credentials(): array {
		$fields        = array(
			'user_login'    => 'user_login_field',
			'user_password' => 'user_pass_field',
			'remember'      => 'remember_field',
		);
		$fields_values = array();

		foreach ( $fields as $name => $settings_name ) {
			$field = $this->settings[ $settings_name ] ?? '';

			if ( ! jet_fb_context()->has_field( $field ) ) {
				continue;
			}
			$fields_values[ $name ] = jet_fb_context()->get_value( $field );
		}

		return $fields_values;
	}
}
