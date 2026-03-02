<?php
namespace JFB_Signature_Field;

use Jet_Form_Builder\Classes\Resources\Upload_Dir;
use Jet_Form_Builder\Live_Form;

class Upload_Dir_Adapter {

	private $temp = false;

	/**
	 * Add hooks to modify uploads dir
	 *
	 * @return void
	 */
	public function apply_upload_dir() {

		add_filter(
			'jet-form-builder/file-upload/dir',
			array( $this, 'file_upload_dir' )
		);

		add_filter(
			'jet-form-builder/file-upload/user-dir-name',
			array( $this, 'user_dir' )
		);

		add_filter( 'upload_dir', array( Upload_Dir::class, 'apply_upload_dir' ) );
	}

	/**
	 * Remove hooks to modify uploads dir
	 *
	 * @return void
	 */
	public function remove_apply_upload_dir() {

		remove_filter(
			'jet-form-builder/file-upload/dir',
			array( $this, 'file_upload_dir' )
		);

		remove_filter(
			'jet-form-builder/file-upload/user-dir-name',
			array( $this, 'user_dir' )
		);

		remove_filter( 'upload_dir', array( Upload_Dir::class, 'apply_upload_dir' ) );
	}

	/**
	 * Get stable user directory based on user ID for logged in users
	 * and user IP for not-logged-in users
	 *
	 * @return string
	 */
	public function user_dir() {

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
	public function get_user_ip() {

		if ( isset($_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
			return $_SERVER['HTTP_CF_CONNECTING_IP'];
		}

		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) && filter_var( $_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP ) ) {
			return $_SERVER['HTTP_CLIENT_IP'];
		}

		// Check for IPs passed from proxies
		if ( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip_list = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );
			foreach ( $ip_list as $ip ) {
				$ip = trim( $ip ); // Remove any spaces
				if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
					return $ip;
				}
			}
		}

		// Fallback to REMOTE_ADDR
		if ( !empty( $_SERVER['REMOTE_ADDR'] ) && filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP ) ) {
			return $_SERVER['REMOTE_ADDR'];
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
	 * @return string
	 */
	public function file_upload_dir( string $dir ): string {
		$unique_dir = self::unique_dir_name();
		return sprintf( $this->is_temp() ? '%1$s/temp/%2$s' : '%1$s/%2$s', $dir, $unique_dir );
	}

	/**
	 * Switch temp directory mode
	 *
	 * @return void
	 */
	public function change_is_temp( Action $action ) {
		if ( empty( $action->settings['save'] ) ) {
			$this->set_temp( true );

			return;
		}
		$this->set_temp( false );
	}

	/**
	 * Get unique directory name for signature uploads
	 *
	 * @return string
	 */
	public static function unique_dir_name() {

		$dir_name = get_option( 'jfb_signature_unique_name' );

		if ( ! $dir_name ) {
			$dir_name = md5( rand( 100000000, 999999999 ) );
			update_option( 'jfb_signature_unique_name', $dir_name, false );
		}

		return $dir_name;

	}

	/**
	 * @param bool $temp
	 */
	public function set_temp( bool $temp ) {
		$this->temp = $temp;
	}

	/**
	 * @return bool
	 */
	public function is_temp(): bool {
		return $this->temp;
	}

}
