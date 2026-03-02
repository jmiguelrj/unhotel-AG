<?php
namespace JFB_Advanced_Media\Blocks;

use Jet_Form_Builder\Classes\Resources\Has_Error_File;
use Jet_Form_Builder\Classes\Resources\Upload_Exception;
use JFB_Modules\Block_Parsers\Field_Data_Parser;
use Jet_Form_Builder\Request\Exceptions\Sanitize_Value_Exception;
use Jet_Form_Builder\Classes\Resources\Media_Block_Value;
use JFB_Modules\Block_Parsers\File_Uploader;
use JFB_Advanced_Media\Upload_Dir_Adapter;
use JFB_Advanced_Media\Image_Settings_Handler;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Advanced Media Field Parser.
 *
 * This class handles parsing and processing of advanced media field data
 * including file uploads, base64 encoding, and media library integration.
 *
 * @since 1.0.0
 */
class Advanced_Media_Field_Parser extends Field_Data_Parser {

	/**
	 * Get the field type.
	 *
	 * @return string Field type identifier.
	 * @since 1.0.0
	 */
	public function type() {
		return 'drag-and-drop-file-upload';
	}

	/**
	 * Get the response value for the field.
	 *
	 * This method processes the field value based on the save_uploaded_file setting.
	 * It handles both file upload mode and base64 encoding mode.
	 *
	 * @return array|false|int|string|null Processed field value.
	 * @throws Sanitize_Value_Exception When value sanitization fails.
	 * @since 1.0.0
	 */
	public function get_response() {
		// Validate user access permissions based on allowed_user_cap setting
		$allowed_user_cap = $this->settings['allowed_user_cap'] ?? 'manage_options';
		$is_guest         = ! is_user_logged_in();

		// If user is guest and allowed_user_cap is not 'any_user', deny access
		if ( $is_guest && 'any_user' !== $allowed_user_cap ) {
			$this->collect_error(
				'guest_access_denied',
				esc_html__( 'Guest users are not allowed to use this field. Please log in to continue.', 'jet-form-builder-advanced-media' )
			);
			return false;
		}

		// Check if logged-in user has required capability (if not 'any_user' and not 'all')
		if ( ! $is_guest && 'any_user' !== $allowed_user_cap && 'all' !== $allowed_user_cap ) {
			if ( ! current_user_can( $allowed_user_cap ) ) {
				$this->collect_error(
					'insufficient_permissions',
					esc_html__( 'You do not have permission to use this field.', 'jet-form-builder-advanced-media' )
				);
				return false;
			}
		}

		// Check save_uploaded_file flag before calling get_file() to avoid File_Collection creation
		$raw_flag  = $this->settings['save_uploaded_file'] ?? '1';
		$save_flag = filter_var( $raw_flag, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );

		// Check if this is instant upload mode - files are already uploaded via AJAX
		$upload_mode = $this->settings['upload_mode'] ?? 'on_submit';

		if ( $is_guest ) {
			$upload_mode = 'on_submit';
		}

		// Check for JSON fields with attachment data (from instant upload or media library selection)
		// This works for both instant and on_submit modes when files are saved to server
		$media_library_attachment_ids = array();

		if ( $save_flag && ! empty( $this->settings['insert_attachment'] ) ) {
			// Instant upload requires user authentication for security
			if ( 'instant' === $upload_mode && ! is_user_logged_in() ) {
				throw new Sanitize_Value_Exception( esc_html__( 'Instant upload requires user authentication', 'jet-form-builder-advanced-media' ) );
			}

			// For instant mode and presets, check if we have JSON data in $this->value
			if ( isset( $this->value ) ) {
				$values_to_process = array();

				// Handle different input formats
				if ( is_array( $this->value ) ) {
					// Array of JSON strings (from multiple preset fields)
					$values_to_process = $this->value;
				} elseif ( is_string( $this->value ) ) {
					// Single JSON string (from single preset field)
					$values_to_process = array( $this->value );
				}

				foreach ( $values_to_process as $index => $file_data ) {
					if ( ! is_string( $file_data ) ) {
						continue;
					}

					// Try to decode JSON - the value is already properly formatted
					$decoded = json_decode( $file_data, true );

					if ( is_array( $decoded ) ) {
						unset( $decoded['name'], $decoded['type'] );
					}

					if (
						is_array( $decoded )
						&& isset( $decoded['id'] )
						&& is_numeric( $decoded['id'] )
					) {
						$media_library_attachment_ids[] = absint( $decoded['id'] );
					}
				}

				if ( ! empty( $media_library_attachment_ids ) ) {

					// Check if there are new uploaded files in addition to preset files
					// We need to process new files and combine them with preset files
					$has_new_files = false;

					// Check $_FILES directly for new uploaded files
					$field_name = $this->settings['name'] ?? '';
					$field_name = sanitize_text_field( $field_name );

					// phpcs:ignore WordPress.Security.NonceVerification.Missing
					if ( ! empty( $field_name ) && isset( $_FILES[ $field_name ] ) ) {
						// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing
						$file_data = $_FILES[ $field_name ];
						// Check if file was actually uploaded (not empty)
						if ( ! empty( $file_data['name'] ) ) {
							// Handle multiple files
							if ( is_array( $file_data['name'] ) ) {
								$has_new_files = ! empty( array_filter( $file_data['name'] ) );
							} else {
								// Single file
								$has_new_files = ! empty( $file_data['name'] );
							}
						}
					}

					// Also check get_file() as fallback
					if ( ! $has_new_files ) {
						$file_collection = $this->get_file();

						if ( ! empty( $file_collection ) && ! ( is_object( $file_collection ) && is_a( $file_collection, Has_Error_File::class ) && $file_collection->has_error() ) ) {
							// File_Collection is not empty and has no errors - likely has files
							$has_new_files = true;
						}
					}

					// If there are new files, we need to process them and combine with preset files
					// Don't return immediately - let the code continue to process new files
					if ( ! $has_new_files ) {
						// Store the attachment IDs for Request_Tools to process
						// This ensures compatibility with send-email and other actions
						$this->value = $media_library_attachment_ids;

						// Return formatted value immediately for preset/media library files ONLY
						// Don't proceed to file upload since these files are already uploaded
						$value_format = $this->get_value_format();

						switch ( $value_format ) {
							case 'id':
								$max_files = $this->settings['max_files'] ?? 1;

								if ( $max_files <= 1 && count( $this->value ) === 1 ) {
									$result = reset( $this->value );
									return $result;
								}

								$result = implode( ',', $this->value );
								return $result;
							case 'both':
								$max_files = $this->settings['max_files'] ?? 1;

								if ( $max_files <= 1 && count( $this->value ) === 1 ) {
									$result = array(
										'id'  => $this->value[0],
										'url' => wp_get_attachment_url( $this->value[0] ),
									);
									return $result;
								} else {
									$both = array();
									foreach ( $this->value as $attachment_id ) {
										$url = wp_get_attachment_url( $attachment_id );
										$both[] = array(
											'id'  => $attachment_id,
											'url' => $url,
										);
									}
									return $both;
								}
							case 'ids':
								return $this->value;
							default: // 'url'
								$result = array_map( 'wp_get_attachment_url', $this->value );
								return $result;
						}
					} else {
						// Store preset/media library attachment IDs for later combination
						// Don't return - let code continue to process new files
						$this->value = $media_library_attachment_ids;
					}
				}
			}
		}

		// Handle instant upload files even without insert_attachment setting
		if ( $save_flag && empty( $this->settings['insert_attachment'] ) && isset( $this->value ) ) {
			$uploaded_files    = array();
			$preset_urls       = array(); // For preset files with attachment ID
			$values_to_process = array();

			// Handle different input formats.
			if ( is_array( $this->value ) ) {
				$values_to_process = $this->value;
			} elseif ( is_string( $this->value ) ) {
				$values_to_process = array( $this->value );
			}

			foreach ( $values_to_process as $index => $file_data ) {
				if ( ! is_string( $file_data ) ) {
					continue;
				}

				// Skip empty strings
				if ( empty( trim( $file_data ) ) ) {
					continue;
				}

				$decoded = json_decode( $file_data, true );

				if ( json_last_error() !== JSON_ERROR_NONE ) {
					continue;
				}

				// Check if this is a preset file with attachment ID
				if (
					is_array( $decoded )
					&& isset( $decoded['url'] )
					&& ! empty( $decoded['url'] )
					&& isset( $decoded['id'] )
					&& is_numeric( $decoded['id'] )
				) {
					// This is a preset file with attachment ID - just use the URL
					$preset_urls[] = $decoded['url'];
				} elseif (
					is_array( $decoded )
					&& isset( $decoded['url'] )
					&& ! empty( $decoded['url'] )
					&& ( empty( $decoded['id'] ) || ! is_numeric( $decoded['id'] ) )
				) {
					// This is a file without attachment ID - create Uploaded_File object
					$uploaded_file = $this->create_uploaded_file_from_json( $decoded );
					if ( $uploaded_file ) {
						$uploaded_files[] = $uploaded_file;
					}
				}
			}

			// Check if there are new uploaded files in addition to preset files
			$has_new_files = false;
			$field_name    = $this->settings['name'] ?? '';
			$field_name    = sanitize_text_field( $field_name );
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( ! empty( $field_name ) && isset( $_FILES[ $field_name ] ) ) {
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing
				$file_data = $_FILES[ $field_name ];
				if ( ! empty( $file_data['name'] ) ) {
					if ( is_array( $file_data['name'] ) ) {
						$has_new_files = ! empty( array_filter( $file_data['name'] ) );
					} else {
						$has_new_files = ! empty( $file_data['name'] );
					}
				}
			}

			// If we have preset URLs but no new files, return them directly
			if ( ! empty( $preset_urls ) && ! $has_new_files ) {
				// For single file, return string; for multiple, return array
				if ( count( $preset_urls ) === 1 ) {
					return $preset_urls[0];
				}
				return $preset_urls;
			}

			// If we have preset URLs AND new files, store them for later combination
			if ( ! empty( $preset_urls ) && $has_new_files ) {
				// Store preset URLs in $this->value for later combination
				$this->value = $preset_urls;
			}

			// Otherwise, process uploaded files
			if ( ! empty( $uploaded_files ) ) {
				$uploaded_collection = new \Jet_Form_Builder\Classes\Resources\Uploaded_Collection( $uploaded_files );

				$this->set_file( $uploaded_collection );

				// Store file paths for Request_Tools compatibility
				$file_paths = array();

				foreach ( $uploaded_files as $uploaded_file ) {
					$file_paths[] = $uploaded_file->get_file();
				}

				$this->value = $file_paths;
			}
		}
		// File upload mode - process files through WordPress attachment system
		$file                  = $this->get_file();
		$file_empty            = empty( $file );
		$file_has_error        = is_object( $file ) && is_a( $file, Has_Error_File::class ) && $file->has_error();
		$has_insert_attachment = ! empty( $this->settings['insert_attachment'] );

		if (
			( $file_empty || $file_has_error )
			&& $has_insert_attachment
		) {
			/**
			 * We should leave here $this->value for case, while this field exporting
			 * with values from Form Record
			 *
			 * @see https://github.com/Crocoblock/issues-tracker/issues/4422
			 */
			if ( is_array( $this->value ) && ! empty( $this->value ) ) {
				// $this->value contains attachment IDs after processing
				$format = $this->get_value_format();

				switch ( $format ) {
					case 'id':
						$result = implode( ',', $this->value );
						return $result;
					case 'both':
						$max_files = $this->settings['max_files'] ?? 1;

						if ( $max_files <= 1 && count( $this->value ) === 1 ) {
							$result = array(
								'id'  => $this->value[0],
								'url' => wp_get_attachment_url( $this->value[0] ),
							);
							return $result;
						} else {
							$both = array();
							foreach ( $this->value as $attachment_id ) {
								$both[] = array(
									'id'  => $attachment_id,
									'url' => wp_get_attachment_url( $attachment_id ),
								);
							}
							return $both;
						}
					case 'ids':
						return $this->value;
					default: // 'url'
						$result = array_map( 'wp_get_attachment_url', $this->value );
						return $result;
				}
			}

			return $this->value;
		}

		// Apply custom upload directory - creates separate folder like signature field
		$adapter = new Upload_Dir_Adapter();
		$adapter->apply_upload_dir(); // Regular mode - let Uploaded_File::upload() handle upload_dir filter

		// Apply image quality and size filters for server-side processing
		Image_Settings_Handler::apply_image_filters( $this->settings );

		// Hook for before upload processing
		do_action( 'jet-form-builder/advanced-media-field/before-upload', $this );

		// Check if we already have Uploaded_Collection from instant upload processing
		$existing_file = $this->get_file();

		if ( $existing_file instanceof \Jet_Form_Builder\Classes\Resources\Uploaded_Collection ) {
			// Remove upload directory filters since we're not uploading
			$adapter->remove_apply_upload_dir();
			// Remove image processing filters since we're not processing
			Image_Settings_Handler::remove_image_filters();

			$value_format = $this->get_value_format();

			// Return formatted value based on field configuration
			switch ( $value_format ) {
				case 'id':
					$result = $existing_file->get_attachment_id();
					return $result;
				case 'both':
					$result = $existing_file->get_attachment_both();
					return $result;
				case 'ids':
					$result = $existing_file->get_attachment_ids();
					return $result;
				default:
					$result = $existing_file->get_attachment_url();
					return $result;
			}
		}

		// FROM issue/18160: Process uploaded files (drag-and-drop) if any
		$all_attachment_ids = $media_library_attachment_ids;

		// Check if there are files to upload (drag-and-drop files)
		$has_files_to_upload = false;
		if ( ! empty( $this->get_file() ) && ! ( is_object( $this->get_file() ) && is_a( $this->get_file(), Has_Error_File::class ) && $this->get_file()->has_error() ) ) {
			$has_files_to_upload = true;
		} elseif ( ! empty( $_FILES[ $this->name ] ?? array() ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing
			$file_input = $_FILES[ $this->name ] ?? array();
			if ( ! empty( $file_input['name'] ) && ( is_array( $file_input['name'] ) ? ! empty( array_filter( $file_input['name'] ) ) : ! empty( $file_input['name'] ) ) ) {
				$has_files_to_upload = true;
			}
		} elseif ( ! empty( $this->value ) && $save_flag ) {
			// Check $this->value for JSON data (from AJAX instant upload or drag-and-drop)
			// This is important for insert_attachment=false case
			$values_to_check = is_array( $this->value ) ? $this->value : array( $this->value );
			foreach ( $values_to_check as $val ) {
				if ( is_string( $val ) && ! empty( trim( $val ) ) ) {
					$decoded = json_decode( $val, true );
					// If it's valid JSON with url or file data, we have files
					if ( is_array( $decoded ) && ( isset( $decoded['url'] ) || isset( $decoded['file'] ) || isset( $decoded['data'] ) ) ) {
						$has_files_to_upload = true;
						break;
					}
				} elseif ( ! empty( $val ) ) {
					// Non-empty non-string value might be file data
					$has_files_to_upload = true;
					break;
				}
			}
		}

		// Create file uploader instance and process uploaded files
		if ( $has_files_to_upload && ! empty( $this->settings['insert_attachment'] ) ) {
			$uploader = ( new File_Uploader() )->set_context( $this );
			// Add hook to process original file after upload
			add_action(
				'wp_generate_attachment_metadata',
				function ( $metadata, $attachment_id ) {
					return Image_Settings_Handler::process_original_file( $metadata, $attachment_id, $this->settings );
				},
				10,
				2
			);

			try {
				/** @var Media_Block_Value $uploads */
				$uploads = $uploader->upload();

				// Get attachment IDs from uploaded files
				if ( $uploads instanceof Media_Block_Value ) {
					$uploaded_attachment_ids = $uploads->get_attachment_ids();
					if ( is_array( $uploaded_attachment_ids ) ) {
						// Merge with media library attachment IDs
						$all_attachment_ids = array_merge( $media_library_attachment_ids, $uploaded_attachment_ids );
					}
				}

				$this->set_file( $uploads );
			} catch ( Upload_Exception $exception ) {
				// Remove upload directory filters before error return
				$adapter->remove_apply_upload_dir(); // Regular mode

				// If we have media library files, return them even if upload failed
				if ( ! empty( $media_library_attachment_ids ) ) {
					$adapter->remove_apply_upload_dir();
					Image_Settings_Handler::remove_image_filters();
					$this->value = $media_library_attachment_ids;
					$format = $this->get_value_format();

					switch ( $format ) {
						case 'id':
							return implode( ',', $this->value );
						case 'both':
							$max_files = $this->settings['max_files'] ?? 1;
							if ( $max_files <= 1 && count( $this->value ) === 1 ) {
								return array(
									'id'  => $this->value[0],
									'url' => wp_get_attachment_url( $this->value[0] ),
								);
							} else {
								$both = array();
								foreach ( $this->value as $attachment_id ) {
									$both[] = array(
										'id'  => $attachment_id,
										'url' => wp_get_attachment_url( $attachment_id ),
									);
								}
								return $both;
							}
						case 'ids':
							return $this->value;
						default: // 'url'
							return array_map( 'wp_get_attachment_url', $this->value );
					}
				}

				$this->collect_error( $exception->getMessage() );
				return false;
			}

			// Remove custom upload directory filters
			$adapter->remove_apply_upload_dir(); // Regular mode

			// Remove image processing filters after upload
			Image_Settings_Handler::remove_image_filters();

			// If we have combined attachment IDs (media library + uploaded), return them
			if ( ! empty( $all_attachment_ids ) ) {
				$this->value = $all_attachment_ids;
				$format = $this->get_value_format();

				switch ( $format ) {
					case 'id':
						return implode( ',', $this->value );
					case 'both':
						$max_files = $this->settings['max_files'] ?? 1;
						if ( $max_files <= 1 && count( $this->value ) === 1 ) {
							return array(
								'id'  => $this->value[0],
								'url' => wp_get_attachment_url( $this->value[0] ),
							);
						} else {
							$both = array();
							foreach ( $this->value as $attachment_id ) {
								$both[] = array(
									'id'  => $attachment_id,
									'url' => wp_get_attachment_url( $attachment_id ),
								);
							}
							return $both;
						}
					case 'ids':
						return $this->value;
					default: // 'url'
						return array_map( 'wp_get_attachment_url', $this->value );
				}
			}

			// Return formatted value based on field configuration for uploaded files only
			$format = $this->get_value_format();
			switch ( $format ) {
				case 'id':
					$result = $uploads->get_attachment_id();
					return $result;
				case 'both':
					$result = $uploads->get_attachment_both();
					return $result;
				case 'ids':
					$result = $uploads->get_attachment_ids();
					return $result;
				default:
					$result = $uploads->get_attachment_url();
					return $result;
			}
		}

		// If we only have media library files (no uploaded files), return them
		if ( ! empty( $media_library_attachment_ids ) && empty( $has_files_to_upload ) ) {
			// Remove upload directory filters
			$adapter->remove_apply_upload_dir();
			Image_Settings_Handler::remove_image_filters();

			$this->value = $media_library_attachment_ids;
			$format = $this->get_value_format();

			switch ( $format ) {
				case 'id':
					return implode( ',', $this->value );
				case 'both':
					$max_files = $this->settings['max_files'] ?? 1;
					if ( $max_files <= 1 && count( $this->value ) === 1 ) {
						return array(
							'id'  => $this->value[0],
							'url' => wp_get_attachment_url( $this->value[0] ),
						);
					} else {
						$both = array();
						foreach ( $this->value as $attachment_id ) {
							$both[] = array(
								'id'  => $attachment_id,
								'url' => wp_get_attachment_url( $attachment_id ),
							);
						}
						return $both;
					}
				case 'ids':
					return $this->value;
				default: // 'url'
					return array_map( 'wp_get_attachment_url', $this->value );
			}
		}

		// If we have uploaded files but no media library files, process normally
		if ( $has_files_to_upload && empty( $media_library_attachment_ids ) ) {

			// Only process attachments if insert_attachment is enabled
			if ( ! empty( $this->settings['insert_attachment'] ) ) {
				$uploader = ( new File_Uploader() )->set_context( $this );
				// Add hook to process original file after upload
				add_action(
					'wp_generate_attachment_metadata',
					function ( $metadata, $attachment_id ) {
						return Image_Settings_Handler::process_original_file( $metadata, $attachment_id, $this->settings );
					},
					10,
					2
				);

				try {
					/** @var Media_Block_Value $uploads */
					$uploads = $uploader->upload();
				} catch ( Upload_Exception $exception ) {
					// Remove upload directory filters before error return
					$adapter->remove_apply_upload_dir(); // Regular mode
					$this->collect_error( $exception->getMessage() );
					return false;
				}

				// Remove custom upload directory filters
				$adapter->remove_apply_upload_dir(); // Regular mode

				// Remove image processing filters after upload
				Image_Settings_Handler::remove_image_filters();

				// Regular scenario: only uploaded files
				$this->set_file( $uploads );

				// Return formatted value based on field configuration
				$format = $this->get_value_format();
				switch ( $format ) {
					case 'id':
						$result = $uploads->get_attachment_id();
						return $result;
					case 'both':
						$result = $uploads->get_attachment_both();
						return $result;
					case 'ids':
						$result = $uploads->get_attachment_ids();
						return $result;
					default:
						$result = $uploads->get_attachment_url();
						return $result;
				}
			} else {
				// insert_attachment=false - process files without creating attachments
				$uploader = ( new File_Uploader() )->set_context( $this );

				try {
					/** @var Media_Block_Value $uploads */
					$uploads = $uploader->upload();
				} catch ( Upload_Exception $exception ) {
					// Remove upload directory filters before error return
					$adapter->remove_apply_upload_dir();
					$this->collect_error( $exception->getMessage() );
					return false;
				}

				// Remove custom upload directory filters
				$adapter->remove_apply_upload_dir();

				// Remove image processing filters
				Image_Settings_Handler::remove_image_filters();

				// Set the uploaded files
				$this->set_file( $uploads );

				// Return URLs only
				$result = $uploads->get_attachment_url();
				return $result;
			}
		}

		// Handle case when save_uploaded_file = false (files not saved to server)
		// Files are still uploaded but not saved - we need to process them for validation
		if ( ! $save_flag ) {

			// Check if we have files in $_FILES
			$field_name = $this->settings['name'] ?? '';
			$field_name = sanitize_text_field( $field_name );

			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( ! empty( $field_name ) && isset( $_FILES[ $field_name ] ) ) {
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing
				$file_data = $_FILES[ $field_name ];

				if ( ! empty( $file_data['name'] ) ) {
					// Files were uploaded - return temporary file paths or URLs
					// For save_flag=false, we typically return file paths or URLs
					// Check if we have file paths in $file_data
					if ( ! empty( $file_data['tmp_name'] ) ) {
						// Return temporary file paths
						if ( is_array( $file_data['tmp_name'] ) ) {
							$result = array_values( array_filter( $file_data['tmp_name'] ) );
							return $result;
						} else {
							$result = $file_data['tmp_name'];
							return $result;
						}
					}
				}
			}

			// Also check $this->value for base64 or URL data
			if ( ! empty( $this->value ) ) {
				// If value is already set (from frontend), return it
				// This handles base64 encoded files or URLs
				return $this->value;
			}

			return null;
		}

		// If no files were processed, return null or empty value
		return null;
	}

	/**
	 * Create Uploaded_File object from JSON data for instant upload files.
	 *
	 * @param array $file_data JSON data containing file information.
	 * @return \Jet_Form_Builder\Classes\Resources\Uploaded_File|false Uploaded_File object or false on failure.
	 * @since 1.0.0
	 */
	private function create_uploaded_file_from_json( $file_data ) {
		if ( ! is_array( $file_data ) || empty( $file_data['url'] ) ) {
			return false;
		}

		$file_url = $file_data['url'];

		// Convert URL to file path
		$file_path = $this->url_to_file_path( $file_url );

		if ( ! $file_path || ! file_exists( $file_path ) ) {
			return false;
		}

		// Create Uploaded_File object
		$uploaded_file = new \Jet_Form_Builder\Classes\Resources\Uploaded_File();

		// Use reflection to set protected properties
		$reflection = new \ReflectionClass( $uploaded_file );

		// Set file path
		$file_property = $reflection->getProperty( 'file' );
		$file_property->setAccessible( true );
		$file_property->setValue( $uploaded_file, $file_path );

		// Set URL
		$url_property = $reflection->getProperty( 'url' );
		$url_property->setAccessible( true );
		$url_property->setValue( $uploaded_file, $file_url );

		// Set MIME type
		$mime_type     = $file_data['type'] ?? wp_check_filetype( $file_path )['type'] ?? 'application/octet-stream';
		$type_property = $reflection->getProperty( 'type' );
		$type_property->setAccessible( true );
		$type_property->setValue( $uploaded_file, $mime_type );

		// Set attachment_id to empty string (not JSON) since this is not a WordPress attachment
		$attachment_id_property = $reflection->getProperty( 'attachment_id' );
		$attachment_id_property->setAccessible( true );
		$attachment_id_property->setValue( $uploaded_file, '' );

		return $uploaded_file;
	}

	/**
	 * Convert file URL to file path.
	 *
	 * @param string $url File URL.
	 * @return string|false File path or false on failure.
	 * @since 1.0.0
	 */
	private function url_to_file_path( $url ) {
		// Remove query parameters
		$url = strtok( $url, '?' );

		// Get upload directory
		$upload_dir  = wp_upload_dir();
		$upload_url  = $upload_dir['baseurl'];
		$upload_path = $upload_dir['basedir'];

		// Check if URL is within upload directory
		if ( strpos( $url, $upload_url ) !== 0 ) {
			return false;
		}

		// Convert URL to file path
		$relative_path = str_replace( $upload_url, '', $url );
		$file_path     = $upload_path . $relative_path;

		return $file_path;
	}

	/**
	 * Get the value format for the field.
	 *
	 * @return string Value format (id, both, ids, url).
	 * @since 1.0.0
	 */
	private function get_value_format() {
		return empty( $this->settings['insert_attachment'] )
			? 'url'
			: ( $this->settings['value_format'] ?? 'url' );
	}
}
