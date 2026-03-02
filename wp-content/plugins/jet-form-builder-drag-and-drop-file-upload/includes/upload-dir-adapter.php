<?php
namespace JFB_Advanced_Media;

use Jet_Form_Builder\Classes\Resources\Upload_Dir;
use Jet_Form_Builder\Live_Form;
use Jet_Form_Builder\Actions\Types\Base;

/**
 * Upload Directory Adapter for Advanced Media plugin.
 *
 * Handles custom upload directory configuration and user-specific
 * directory creation for file uploads.
 *
 * @since 1.0.0
 */
class Upload_Dir_Adapter {

	private $temp = false;

	/**
	 * Add hooks to modify uploads dir
	 *
	 * @return void
	 */
	public function apply_upload_dir(): void {
		add_filter(
			'jet-form-builder/file-upload/dir',
			array( $this, 'file_upload_dir' )
		);

		add_filter(
			'jet-form-builder/file-upload/user-dir-name',
			array( $this, 'user_dir' )
		);
	}

	/**
	 * Remove hooks to modify uploads dir
	 *
	 * @return void
	 */
	public function remove_apply_upload_dir(): void {
		remove_filter(
			'jet-form-builder/file-upload/dir',
			array( $this, 'file_upload_dir' )
		);

		remove_filter(
			'jet-form-builder/file-upload/user-dir-name',
			array( $this, 'user_dir' )
		);

		// No upload_dir filter cleanup needed since we don't add it
	}

	/**
	 * Get stable user directory based on user ID for logged in users
	 * and user IP for not-logged-in users
	 *
	 * @return string
	 */
	public function user_dir(): string {

		if ( is_user_logged_in() ) {
			$user_id = get_current_user_id();
		} else {
			$user_id = $this->get_user_ip();
		}

		return md5( $user_id . Live_Form::instance()->form_id );
	}

	/**
	 * Get user IP to use for user dir encoding
	 *
	 * @return string
	 */
	public function get_user_ip(): string {

		if ( isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) );
		}

		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) && filter_var( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ), FILTER_VALIDATE_IP ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		}

		// Check for IPs passed from proxies
		if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip_list = explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
			foreach ( $ip_list as $ip ) {
				$ip = trim( $ip ); // Remove any spaces
				if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
					return $ip;
				}
			}
		}

		// Fallback to REMOTE_ADDR
		if ( ! empty( $_SERVER['REMOTE_ADDR'] ) && filter_var( wp_unslash( $_SERVER['REMOTE_ADDR'] ), FILTER_VALIDATE_IP ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		if ( session_id() ) {
			return session_id();
		}

		// Return 'guest' if no valid IP is found
		return 'guest';
	}

	/**
	 * Modify full upload dir
	 *
	 * @param string $dir Upload directory path.
	 * @return string Modified upload directory path.
	 */
	public function file_upload_dir( string $dir ): string {
		$unique_dir = self::unique_dir_name();
		return sprintf( $this->is_temp() ? '%1$s/temp/%2$s' : '%1$s/%2$s', $dir, $unique_dir );
	}

	/**
	 * Switch temp directory mode
	 *
	 * @param Base $action Action instance.
	 * @return void
	 */
	public function change_is_temp( Base $action ): void {
		if ( empty( $action->settings['save_uploaded_file'] ) ) {
			$this->set_temp( true );

			return;
		}
		$this->set_temp( false );
	}

	/**
	 * Get unique directory name for advanced media uploads
	 *
	 * @return string
	 */
	public static function unique_dir_name(): string {

		$dir_name = get_option( 'jfb_advanced_media_unique_name' );

		if ( ! $dir_name ) {
			$dir_name = md5( rand( 100000000, 999999999 ) );
			update_option( 'jfb_advanced_media_unique_name', $dir_name, false );
		}

		return $dir_name;
	}

	/**
	 * Set temp directory mode
	 *
	 * @param bool $temp Whether to use temp directory.
	 * @return void
	 */
	public function set_temp( bool $temp ): void {
		$this->temp = $temp;
	}

	/**
	 * Check if temp directory mode is enabled
	 *
	 * @return bool
	 */
	public function is_temp(): bool {
		return $this->temp;
	}

}
