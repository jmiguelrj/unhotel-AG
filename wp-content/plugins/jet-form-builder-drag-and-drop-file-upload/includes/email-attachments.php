<?php
/**
 * Advanced Media extension for JetFormBuilder.
 * Adds support for base64 file attachments in Send Email action.
 * If files are not saved to server but contain data:URI,
 * emails will still receive the attachments.
 */

namespace JFB_Advanced_Media\Email_Attachments;

use Jet_Form_Builder\Request\Request_Tools;
use Jet_Form_Builder\Classes\Tools;

// Exit if accessed directly.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Check if field is configured as attachment in current Send Email action.
 *
 * @param string $field_name Field name.
 * @return bool True if field is in current email's attachment list.
 * @since 1.0.0
 */
function is_field_configured_as_attachment( string $field_name ): bool {
	$attachment_fields = get_current_email_attachment_fields();

	return in_array( $field_name, $attachment_fields, true );
}

/**
 * Check if field is Drag and Drop File Upload field.
 *
 * @param string $field_name Field name.
 * @param mixed  $value      Field value for additional validation.
 * @return bool True if this is Drag and Drop File Upload field.
 * @since 1.0.0
 */
function is_advanced_media_field( string $field_name, $value = null ): bool {
	// Method 1: Check via jet_fb_context() if available
	if ( function_exists( 'jet_fb_context' ) ) {
		try {
			$field_type = jet_fb_context()->get_field_type( $field_name ?? '' );

			if ( 'drag-and-drop-file-upload' === $field_type ) {
				return true;
			}
		} catch ( \Exception $e ) {
			// Ignore exceptions during field type detection
		}
	}

	// Method 2: Check field content for data:URI patterns
	if ( function_exists( 'jet_fb_request_handler' ) ) {
		try {
			$field_type = jet_fb_request_handler()->get_type( $field_name );

			if ( 'drag-and-drop-file-upload' === $field_type ) {
				return true;
			}
		} catch ( \Exception $e ) {
			// Ignore errors
		}
	}

	return false;
}

/**
 * Get current Send Email action attachment fields.
 *
 * @return array Array of field names that should be attached to current email.
 * @since 1.0.0
 */
function get_current_email_attachment_fields(): array {
	// Try to get current action context
	if ( function_exists( 'jet_fb_action_handler' ) && jet_fb_action_handler() ) {
		// Check if we're in an action loop before calling get_current_action()
		if ( ! jet_fb_action_handler()->in_loop() ) {
			return array();
		}

		$current_action = jet_fb_action_handler()->get_current_action();

		// Check if this is Send Email action and has settings property
		if ( $current_action && property_exists( $current_action, 'settings' ) ) {
			// Access settings property directly using reflection
			$reflection = new \ReflectionClass( $current_action );

			if ( $reflection->hasProperty( 'settings' ) ) {
				$settings_property = $reflection->getProperty( 'settings' );
				$settings_property->setAccessible( true );
				$settings = $settings_property->getValue( $current_action );

				// Return attachment fields if this is Send Email action
				if ( isset( $settings['attachments'] ) && is_array( $settings['attachments'] ) ) {
					return $settings['attachments'];
				}
			}
		}
	}

	return array();
}

/**
 * Add base64 attachments to PHPMailer.
 *
 * Processes only fields that are configured as attachments in Send Email action
 * to find Advanced Media fields with base64 data and adds them as email attachments.
 *
 * @param \PHPMailer\PHPMailer\PHPMailer $phpmailer PHPMailer instance.
 * @return void
 * @since 1.0.0
 */
function add_base64_attachments_to_phpmailer( \PHPMailer\PHPMailer\PHPMailer $phpmailer ): void {
	// Get fields that should be attached to current email
	$attachment_fields = get_current_email_attachment_fields();

	// CRITICAL: Only process base64 attachments if current email action
	// has Advanced Media fields configured as attachments
	$has_advanced_media_attachments = false;

	foreach ( $attachment_fields as $field_name ) {
		if ( is_advanced_media_field( $field_name ) ) {
			$has_advanced_media_attachments = true;
			break;
		}
	}

	// If current email doesn't use Advanced Media fields as attachments, skip entirely
	if ( ! $has_advanced_media_attachments ) {
		return;
	}

	// Process only fields that are configured as attachments in current email
	$fields_to_process = ! empty( $attachment_fields )
		? array_intersect_key( Request_Tools::get_request(), array_flip( $attachment_fields ) )
		: array();

	// Process specified fields to find Advanced Media fields
	foreach ( $fields_to_process as $key => $values ) {

		// Check if field is Advanced Media field
		if ( ! is_advanced_media_field( $key, $values ) ) {
			continue;
		}

		// Additional security: double-check that this field is actually in the attachment list
		if ( ! is_field_configured_as_attachment( $key ) ) {
			continue;
		}

		if ( ! is_array( $values ) ) {
			$values = array( $values );
		}

		foreach ( $values as $value ) {
			if ( ! is_string( $value ) || empty( $value ) ) {
				continue;
			}

			// Check if this is data:URI string (single file)
			if ( preg_match( '#^data:(.+?);base64,(.+)$#', $value, $m ) ) {
				$mime = $m[1];
				$data = base64_decode( $m[2] );

				if ( false === $data ) {
					continue; // Invalid base64 string.
				}

				// Use JetFormBuilder's allowed MIME types for consistency
				// This automatically includes all MIME types allowed by WordPress
				// and can be customized via WordPress filters like 'upload_mimes'
				//
				// The filter 'jfb_am/base64_allowed_mime' allows further customization
				// specifically for email attachments if needed
				//
				// Example WordPress MIME type customization:
				// add_filter('upload_mimes', function($mimes) {
				// $mimes['ai'] = 'application/vnd.adobe.illustrator';
				// return $mimes;
				// });
				$allowed = apply_filters( 'jet-form-builder/advanced-media/base64_allowed_mime', Tools::get_allowed_mimes_list_for_js() );

				if ( ! in_array( $mime, $allowed, true ) ) {
					continue;
				}

				$ext      = explode( '/', $mime )[1] ?? 'bin';
				$filename = sanitize_file_name( $key . '.' . $ext );

				try {
					$phpmailer->addStringAttachment( $data, $filename, 'base64', $mime, 'attachment' );
				} catch ( \PHPMailer\PHPMailer\Exception $e ) {
					// Silent fail - attachment could not be added
				}
				continue;
			}

			// Check if this is JSON array of data:URI strings (Advanced Media field)
			$decoded = json_decode( $value, true );
			if ( ! is_array( $decoded ) ) {
				continue;
			}

			$file_index = 0;
			foreach ( $decoded as $file_data ) {
				if ( is_array( $file_data ) && isset( $file_data['dataUrl'] ) ) {
					$data_uri  = $file_data['dataUrl'];
					$file_name = $file_data['fileName'] ?? '';

					if ( ! is_string( $data_uri ) || ! preg_match( '#^data:(.+?);base64,(.+)$#', $data_uri, $m ) ) {
						continue;
					}

					$mime = $m[1];
					$data = base64_decode( $m[2] );

					if ( false === $data ) {
						continue;
					}

					// Use JetFormBuilder's allowed MIME types for consistency
					// This automatically includes all MIME types allowed by WordPress
					// and can be customized via WordPress filters like 'upload_mimes'
					//
					// The filter 'jet-form-builder/advanced-media/base64_allowed_mime' allows further customization
					// specifically for email attachments if needed
					//
					// Example WordPress MIME type customization:
					// add_filter('upload_mimes', function($mimes) {
					// $mimes['ai'] = 'application/vnd.adobe.illustrator';
					// return $mimes;
					// });
					$allowed = apply_filters( 'jet-form-builder/advanced-media/base64_allowed_mime', Tools::get_allowed_mimes_list_for_js() );

					if ( ! in_array( $mime, $allowed, true ) ) {
						continue;
					}

					// Use original file name if available, otherwise generate one
					if ( ! empty( $file_name ) ) {
						$filename = sanitize_file_name( $file_name );
					} else {
						$ext      = explode( '/', $mime )[1] ?? 'bin';
						$filename = sanitize_file_name( $key . '_' . $file_index . '.' . $ext );
					}

					try {
						$phpmailer->addStringAttachment( $data, $filename, 'base64', $mime, 'attachment' );
					} catch ( \PHPMailer\PHPMailer\Exception $e ) {
						// Silent fail - attachment could not be added
					}

					$file_index++;
					continue;
				}
			}
		}
	}
}

// Hook into PHPMailer to add base64 attachments
add_action(
	'phpmailer_init',
	function ( $phpmailer ) {
		add_base64_attachments_to_phpmailer( $phpmailer );
	},
	20
);
