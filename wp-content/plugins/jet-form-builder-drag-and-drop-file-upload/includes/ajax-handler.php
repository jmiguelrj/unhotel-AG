<?php
namespace JFB_Advanced_Media;

// Prevent direct access to the file.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use JFB_Advanced_Media\Upload_Dir_Adapter;
use JFB_Advanced_Media\Image_Settings_Handler;
use Jet_Form_Builder\Classes\Tools;

/**
 * Handles AJAX requests for the Advanced Media plugin.
 *
 * This class manages all AJAX-related functionality including
 * file uploads and media library operations.
 *
 * @since 1.0.0
 */
class Ajax_Handler {

	/**
	 * Initialize AJAX hooks.
	 *
	 * @since 1.0.0
	 */
	public function init(): void {
		// Register AJAX handlers for instant upload
		add_action( 'wp_ajax_jfb_advanced_media_upload', array( $this, 'handle_ajax_upload' ) );
		add_action( 'wp_ajax_nopriv_jfb_advanced_media_upload', array( $this, 'handle_ajax_upload' ) );
	}

	/**
	 * Handle AJAX file upload requests.
	 *
	 * This method processes uploaded files, validates them, and creates
	 * WordPress attachments if needed.
	 *
	 * @since 1.0.0
	 */
	public function handle_ajax_upload(): void {
		try {
			// Verify nonce for security
			if ( ! check_ajax_referer( 'jfb_advanced_media_upload', 'nonce', false ) ) {
				wp_send_json_error(
					__( 'Security check failed', 'jet-form-builder-advanced-media' ),
					403
				);
			}

			// Require authentication and proper capability for instant upload
			if ( ! is_user_logged_in() || ! current_user_can( 'upload_files' ) ) {
				wp_send_json_error(
					__( 'Instant upload requires authentication and proper permissions', 'jet-form-builder-advanced-media' ),
					403
				);
			}

			// Process the upload
			$this->process_upload();

		} catch ( \Exception $e ) {
			wp_send_json_error(
				__( 'Upload failed', 'jet-form-builder-advanced-media' ),
				500
			);
		}
	}

	/**
	 * Process the file upload.
	 *
	 * @since 1.0.0
	 */
	private function process_upload(): void {
		// Include WordPress file handling functions
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		// Set form_id for Live_Form to fix upload directory issue
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$form_id = absint( wp_unslash( $_POST['form_id'] ?? 0 ) );
		if ( $form_id ) {
			\Jet_Form_Builder\Live_Form::instance()->set_form_id( $form_id );
		}

		// Apply custom upload directory - same as parser
		$adapter = new Upload_Dir_Adapter();
		$adapter->apply_upload_dir(); // Regular mode, same as parser

		try {
			// Use the same File_Uploader approach as parser for consistency
			$this->upload_via_file_uploader( $adapter );
		} finally {
			// Always remove upload directory filters
			$adapter->remove_apply_upload_dir(); // Regular mode, same as parser
		}
	}

	/**
	 * Upload file using File_Uploader - same approach as parser
	 *
	 * @param Upload_Dir_Adapter $adapter Upload directory adapter.
	 * @since 1.0.0
	 */
	private function upload_via_file_uploader( Upload_Dir_Adapter $adapter ): void {
		// Create a File object from $_FILES data
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$file_data = $_FILES['file'] ?? array();

		// Correct MIME type using WordPress function for cross-browser compatibility
		$file_name = $file_data['name'] ?? '';
		$file_tmp  = $file_data['tmp_name'] ?? '';

		if ( ! empty( $file_name ) && ! empty( $file_tmp ) ) {
			// Enhanced HEIC file detection for Windows 11 compatibility
			$is_heic_file = $this->detect_heic_file( $file_name, $file_tmp, $file_data['type'] ?? '' );

			if ( $is_heic_file ) {
				// Set correct HEIC MIME type based on file extension
				$file_extension = strtolower( pathinfo( $file_name, PATHINFO_EXTENSION ) );
				if ( in_array( $file_extension, array( 'heic', 'heics' ) ) ) {
					$file_data['type'] = 'image/heic';
				} elseif ( in_array( $file_extension, array( 'heif', 'heifs' ) ) ) {
					$file_data['type'] = 'image/heif';
				} else {
					// Default to HEIC if we can't determine from extension
					$file_data['type'] = 'image/heic';
				}
			} else {
				// Use wp_check_filetype_and_ext to get correct MIME type for non-HEIC files
				$wp_file_type = wp_check_filetype_and_ext( $file_tmp, $file_name );
				// If WordPress detected a valid type, use it instead of browser-provided type
				if ( ! empty( $wp_file_type['type'] ) ) {
					$file_data['type'] = $wp_file_type['type'];
				}
			}
		}

		try {
			$file = new \Jet_Form_Builder\Classes\Resources\File( $file_data );
		} catch ( \Jet_Form_Builder\Classes\Resources\Sanitize_File_Exception $exception ) {
			wp_send_json_error(
				__( 'Invalid file', 'jet-form-builder-advanced-media' ),
				400
			);
		}

		// Get field settings from AJAX request
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$allowed_user_cap   = sanitize_text_field( wp_unslash( $_POST['allowed_user_cap'] ?? 'upload_files' ) );
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$insert_attachment  = filter_var( wp_unslash( $_POST['insert_attachment'] ?? '1' ), FILTER_VALIDATE_BOOLEAN );
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$save_uploaded_file = filter_var( wp_unslash( $_POST['save_uploaded_file'] ?? '1' ), FILTER_VALIDATE_BOOLEAN );

		// Get image processing settings from AJAX request
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$max_image_width  = intval( wp_unslash( $_POST['max_image_width'] ?? 0 ) );
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$max_image_height = intval( wp_unslash( $_POST['max_image_height'] ?? 0 ) );
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$image_quality    = intval( wp_unslash( $_POST['image_quality'] ?? 100 ) );

		// Process allowed_mimes - convert string to array if needed and intersect with WP allowed
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$allowed_mimes = wp_unslash( $_POST['allowed_mimes'] ?? array() );
		if ( is_string( $allowed_mimes ) ) {
			if ( ! empty( $allowed_mimes ) ) {
				$allowed_mimes = array_filter( array_map( 'trim', explode( ',', $allowed_mimes ) ) );
			} else {
				$allowed_mimes = array();
			}
		} elseif ( ! is_array( $allowed_mimes ) ) {
			$allowed_mimes = array();
		}

		// Enhanced MIME type validation with custom types support
		if ( ! empty( $allowed_mimes ) ) {
			// Use the provided MIME types as-is
			$allowed_mimes = array_values( $allowed_mimes );
		} else {
			// Use WordPress allowed MIME types as fallback (includes custom types)
			$wp_allowed_mimes = Tools::get_allowed_mimes_list_for_js();
			$allowed_mimes    = array_values( $wp_allowed_mimes );
		}

		// Enhanced file type validation with fallback mechanisms
		$file_extension     = strtolower( pathinfo( $file_name, PATHINFO_EXTENSION ) );
		$detected_mime_type = $file_data['type'];

		// Use universal MIME type validation
		$validation_result = $this->universal_mime_validation( $file_name, $file_tmp, $detected_mime_type, $allowed_mimes );

		// If validation failed, return error
		if ( ! $validation_result['is_valid'] ) {
			wp_send_json_error( $validation_result['message'], 400 );
		}

		// Update file data with corrected MIME type
		$file_data['type'] = $validation_result['corrected_mime_type'];

		// Bound max_size to server limit and ensure numeric
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing
		$max_size_raw = wp_unslash( $_POST['max_size'] ?? '' );

		if ( is_numeric( $max_size_raw ) ) {
			$max_size = (int) $max_size_raw;
			$max_size = max( 0, min( $max_size, (int) wp_max_upload_size() ) );
		} else {
			$max_size = '';
		}

		// Check if user is guest (any_user) and adjust insert_attachment accordingly
		$is_guest_user = ! is_user_logged_in();
		$is_any_user   = 'any_user' === $allowed_user_cap;
		// If user is guest and insert_attachment is enabled, disable it for security
		if ( $is_guest_user && $insert_attachment && $is_any_user ) {
			$insert_attachment = false;
		}

		// If save_uploaded_file is disabled, we can't proceed with upload
		if ( ! $save_uploaded_file ) {
			wp_send_json_error(
				__( 'File saving is disabled for this field', 'jet-form-builder-advanced-media' ),
				400
			);
		}

		// Create settings array for image processing
		$image_settings = array(
			'save_uploaded_file' => $save_uploaded_file,
			'max_image_width'    => $max_image_width,
			'max_image_height'   => $max_image_height,
			'image_quality'      => $image_quality,
		);

		// Apply image quality and size filters for server-side processing
		Image_Settings_Handler::apply_image_filters( $image_settings );

		// Create uploader with settings from AJAX request
		$uploader = new \JFB_Modules\Block_Parsers\File_Uploader();
		$settings = array(
			'insert_attachment' => $insert_attachment,
			'max_files'         => 1,
			'max_size'          => $max_size,
			'allowed_mimes'     => $allowed_mimes,
			'allowed_user_cap'  => $allowed_user_cap,
		);

		// For guest users, force insert_attachment to false for security
		if ( $is_guest_user ) {
			$settings['insert_attachment'] = false;
		}

		$uploader->set_settings( $settings );
		$uploader->set_file( $file );

		try {
			$uploads = $uploader->upload();
		} catch ( \Jet_Form_Builder\Classes\Resources\Upload_Exception $exception ) {
			// Remove image processing filters on error
			Image_Settings_Handler::remove_image_filters();
			wp_send_json_error( $exception->getMessage(), 500 );
		}

		// Save metadata for media library filtering only if attachment was created
		if ( $uploads->get_attachment_id() ) {
			update_post_meta( $uploads->get_attachment_id(), '_jfb_user_hash', $adapter->user_dir() );

			// Process original file after upload to apply quality and size settings
			$attachment_id = $uploads->get_attachment_id();
			$metadata      = wp_get_attachment_metadata( $attachment_id );
			if ( $metadata ) {
				$processed_metadata = Image_Settings_Handler::process_original_file( $metadata, $attachment_id, $image_settings );
				wp_update_attachment_metadata( $attachment_id, $processed_metadata );
			}
		}

		// Remove image processing filters after successful upload
		Image_Settings_Handler::remove_image_filters();

		wp_send_json_success(
			array(
				'id'  => $uploads->get_attachment_id(),
				'url' => $uploads->get_attachment_url(),
			)
		);
	}

	/**
	 * Detect HEIC/HEIF files by file signature and extension.
	 *
	 * @since 1.0.3
	 *
	 * @param string $file_name File name
	 * @param string $file_tmp  Temporary file path
	 * @param string $mime_type Browser-provided MIME type
	 * @return bool Whether file is HEIC/HEIF format
	 */
	private function detect_heic_file( string $file_name, string $file_tmp, string $mime_type ): bool {
		// Check by file extension first (most reliable)
		$file_extension  = strtolower( pathinfo( $file_name, PATHINFO_EXTENSION ) );
		$heic_extensions = array( 'heic', 'heif', 'heics', 'heifs' );

		if ( in_array( $file_extension, $heic_extensions ) ) {
			return true;
		}

		// Check by MIME type
		$heic_mime_types = array( 'image/heic', 'image/heif', 'image/heic-sequence', 'image/heif-sequence' );
		$mime_type_lower = strtolower( $mime_type );

		foreach ( $heic_mime_types as $heic_mime ) {
			if ( strpos( $mime_type_lower, $heic_mime ) !== false ) {
				return true;
			}
		}

		$incorrect_mime_types = array(
			'application/octet-stream',
			'application/unknown',
			'image/jpeg', // Sometimes HEIC files get JPEG MIME type
			'image/png',  // Sometimes HEIC files get PNG MIME type
		);

		if ( in_array( $mime_type_lower, $incorrect_mime_types ) ) {
			// Check file signature (magic bytes) for more accurate detection
			return $this->check_heic_file_signature( $file_tmp );
		}

		return false;
	}

	/**
	 * Check HEIC file signature (magic bytes) for more accurate detection.
	 * This is especially useful for Windows 11 where MIME type might be incorrect.
	 *
	 * @since 1.0.3
	 *
	 * @param string $file_tmp Temporary file path
	 * @return bool Whether file has HEIC signature
	 */
	private function check_heic_file_signature( string $file_tmp ): bool {
		if ( ! file_exists( $file_tmp ) || ! is_readable( $file_tmp ) ) {
			return false;
		}

		try {
			// Read first 12 bytes to check for HEIC signature
			$handle = fopen( $file_tmp, 'rb' );

			if ( ! $handle ) {
				return false;
			}

			$bytes = fread( $handle, 12 );

			fclose( $handle );

			if ( strlen( $bytes ) < 8 ) {
				return false;
			}

			// HEIC files start with specific signatures:
			// - ftyp box with 'heic' brand
			// - ftyp box with 'heix' brand (HEIC sequence)
			// - ftyp box with 'hevc' brand
			// - ftyp box with 'hevx' brand (HEVC sequence)

			// Check for 'ftyp' at position 4 (after 4-byte size)
			if ( 'f' === $bytes[4] && 't' === $bytes[5] && 'y' === $bytes[6] && 'p' === $bytes[7] ) {
				// Check for HEIC-related brands
				$brand       = substr( $bytes, 8, 4 );
				$heic_brands = array( 'heic', 'heix', 'hevc', 'hevx', 'mif1' );

				foreach ( $heic_brands as $heic_brand ) {
					if ( strpos( $brand, $heic_brand ) !== false ) {
						return true;
					}
				}
			}

			return false;
		} catch ( \Exception $e ) {
			// If signature check fails, return false
			return false;
		}
	}

	/**
	 * Universal MIME type validation with fallback mechanisms.
	 * Handles custom MIME types and unknown file types gracefully.
	 *
	 * @param string $file_name File name
	 * @param string $file_tmp Temporary file path
	 * @param string $mime_type Browser-provided MIME type
	 * @param array $allowed_mimes Allowed MIME types
	 * @return array Validation result with corrected MIME type
	 * @since 1.0.3
	 */
	private function universal_mime_validation( string $file_name, string $file_tmp, string $mime_type, array $allowed_mimes ): array {
		$file_extension      = strtolower( pathinfo( $file_name, PATHINFO_EXTENSION ) );
		$corrected_mime_type = $mime_type;
		$is_valid            = false;
		$validation_message  = '';

		// Get all WordPress allowed MIME types (including custom ones)
		$wp_allowed_mimes = get_allowed_mime_types();

		// 1. Check if we have a custom MIME type for this extension
		if ( isset( $wp_allowed_mimes[ $file_extension ] ) ) {
			$corrected_mime_type = $wp_allowed_mimes[ $file_extension ];
			$is_valid = in_array( $corrected_mime_type, $allowed_mimes, true );
		} elseif ( empty( $mime_type ) ||
				 'application/octet-stream' === $mime_type ||
				 'application/unknown' === $mime_type ) {

			// Try to get MIME type from WordPress allowed types
			if ( isset( $wp_allowed_mimes[ $file_extension ] ) ) {
				$corrected_mime_type = $wp_allowed_mimes[ $file_extension ];
				$is_valid            = in_array( $corrected_mime_type, $allowed_mimes, true );
			} else {
				// Extension not recognized - this might be a custom file type
				$is_valid = false;
				$validation_message = sprintf(
					// translators: %s is the file extension
					__( 'File extension "%s" is not recognized. Please ensure the file type is allowed.', 'jet-form-builder-advanced-media' ),
					$file_extension
				);
			}
		} else {
			$is_valid = in_array( $mime_type, $allowed_mimes, true );
			if ( ! $is_valid ) {
				// Try to correct MIME type based on extension
				if ( isset( $wp_allowed_mimes[ $file_extension ] ) ) {
					$corrected_mime_type = $wp_allowed_mimes[ $file_extension ];
					$is_valid            = in_array( $corrected_mime_type, $allowed_mimes, true );
				}
			}
		}

		// 4. Final fallback: if still not valid, check if it's a generic binary file
		if ( ! $is_valid && empty( $validation_message ) ) {
			// Allow generic binary files if explicitly allowed
			if ( in_array( 'application/octet-stream', $allowed_mimes, true ) ) {
				$corrected_mime_type = 'application/octet-stream';
				$is_valid            = true;
			} else {
				$validation_message = sprintf(
					// translators: %1$s is the file MIME type, %2$s is the list of allowed types
					__( 'File type "%1$s" is not allowed. Allowed types: %2$s', 'jet-form-builder-advanced-media' ),
					$corrected_mime_type,
					implode( ', ', $allowed_mimes )
				);
			}
		}

		return array(
			'is_valid'            => $is_valid,
			'corrected_mime_type' => $corrected_mime_type,
			'message'             => $validation_message,
		);
	}
}
