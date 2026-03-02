<?php
namespace JFB_Advanced_Media\Presets;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Advanced Media Preset Handler.
 *
 * Handles preset functionality for Advanced Media fields,
 * following the same pattern as JetFormBuilder media field.
 *
 * @since 1.0.0
 */
class Advanced_Media_Preset {

	/**
	 * Parse preset value for Advanced Media field.
	 *
	 * @param mixed $preset Raw preset value.
	 * @return array Parsed files array.
	 * @since 1.0.0
	 */
	public static function parse_preset( $preset ): array {
		if ( empty( $preset ) ) {
			return array();
		}

		// Handle different preset formats
		$files = array();

		// If it's already an array (from form builder editor)
		if ( is_array( $preset ) ) {
			// Check if this is a single file object or array of files
			if ( self::is_single_file_array( $preset ) ) {
				$result = self::normalize_files_array( array( $preset ) );
			} else {
				$result = self::normalize_files_array( $preset );
			}
			return $result;
		}

		// Handle comma-separated IDs or URLs
		if ( is_string( $preset ) || is_numeric( $preset ) ) {
			$items = is_string( $preset ) ? explode( ',', str_replace( ', ', ',', $preset ) ) : array( $preset );

			foreach ( $items as $item ) {
				$item = trim( $item );

				if ( empty( $item ) ) {
					continue;
				}

				// Check if it's an attachment ID
				if ( is_numeric( $item ) ) {
					$attachment_id = absint( $item );
					$file_url     = wp_get_attachment_url( $attachment_id );

					if ( $file_url ) {
						$files[] = array(
							'id'  => $attachment_id,
							'url' => $file_url,
						);
					}
				} elseif ( filter_var( $item, FILTER_VALIDATE_URL ) ) { // Check if it's a URL
					$files[] = array(
						'url' => $item,
					);
				}
			}
		}

		return self::normalize_files_array( $files );
	}

	/**
	 * Normalize files array structure.
	 *
	 * @param array $files Raw files array.
	 * @return array Normalized files array.
	 * @since 1.0.0
	 */
	private static function normalize_files_array( array $files ): array {
		$normalized = array();

		foreach ( $files as $index => $file ) {
			if ( empty( $file ) ) {
				continue;
			}

			// Handle nested structure
			if ( isset( $file['id'] ) && is_array( $file['id'] ) ) {
				$file = $file['id'];
			}

			$normalized_file = array();

			// Ensure we have URL
			if ( isset( $file['url'] ) ) {
				$normalized_file['url'] = $file['url'];
			} elseif ( isset( $file['id'] ) && is_numeric( $file['id'] ) ) {
				$attachment_url = wp_get_attachment_url( $file['id'] );
				if ( $attachment_url ) {
					$normalized_file['url'] = $attachment_url;
				}
			} elseif ( is_numeric( $file ) ) {
				$attachment_url = wp_get_attachment_url( $file );
				if ( $attachment_url ) {
					$normalized_file['url'] = $attachment_url;
					$normalized_file['id']  = absint( $file );
				}
			} elseif ( is_string( $file ) && ( filter_var( $file, FILTER_VALIDATE_URL ) || self::is_valid_url_with_unicode( $file ) ) ) {
				$normalized_file['url'] = $file;
			}

			// Ensure we have ID if possible
			if ( isset( $file['id'] ) && is_numeric( $file['id'] ) ) {
				$normalized_file['id'] = absint( $file['id'] );
			} elseif ( is_numeric( $file ) ) {
				$normalized_file['id'] = absint( $file );
			} elseif ( isset( $normalized_file['url'] ) && ! isset( $normalized_file['id'] ) ) {
				// Try to get attachment ID from URL
				$attachment_id = attachment_url_to_postid( $normalized_file['url'] );
				if ( $attachment_id ) {
					$normalized_file['id'] = $attachment_id;
				}
			}

			// Add filename
			if ( isset( $normalized_file['url'] ) ) {
				if ( isset( $normalized_file['id'] ) ) {
					$attachment_title = get_the_title( $normalized_file['id'] );
					if ( $attachment_title && trim( $attachment_title ) !== '' ) {
						$normalized_file['name'] = $attachment_title;

						// If title doesn't have extension, try to add one from URL
						if ( ! preg_match( '/\.[a-zA-Z0-9]{2,4}$/', $attachment_title ) ) {
							$url_extension = pathinfo( $normalized_file['url'], PATHINFO_EXTENSION );
							if ( $url_extension ) {
								$normalized_file['name'] = $attachment_title . '.' . $url_extension;
							}
						}
					}
				}

				// Final fallback - ensure we always have a valid filename
				if ( empty( $normalized_file['name'] ) || trim( $normalized_file['name'] ) === '' ) {
					$normalized_file['name'] = basename( $normalized_file['url'] );
				}
			}

			if ( ! empty( $normalized_file ) ) {
				$normalized[] = $normalized_file;
			}
		}

		return $normalized;
	}

	/**
	 * Check if array represents a single file object.
	 *
	 * @param array $array Array to check.
	 * @return bool True if it's a single file object.
	 * @since 1.0.0
	 */
	private static function is_single_file_array( array $array ): bool {
		// Check for common file object keys
		$file_keys  = array( 'id', 'url', 'name', 'type', 'size' );
		$array_keys = array_keys( $array );

		// If array has any of these keys and they're not all numeric, it's likely a single file
		$has_file_keys    = ! empty( array_intersect( $file_keys, $array_keys ) );
		$all_keys_numeric = count( array_filter( $array_keys, 'is_numeric' ) ) === count( $array_keys );

		// It's a single file if:
		// 1. Has file-related keys AND not all keys are numeric (not an indexed array)
		return $has_file_keys && ! $all_keys_numeric;
	}

	/**
	 * Check if string is a valid URL with Unicode characters.
	 *
	 * @param string $url URL to check.
	 * @return bool True if it's a valid URL.
	 * @since 1.0.0
	 */
	private static function is_valid_url_with_unicode( string $url ): bool {
		// Basic URL structure check
		if ( ! preg_match( '/^https?:\/\/.+/', $url ) ) {
			return false;
		}

		// Try to encode URL and validate
		$encoded_url = filter_var( $url, FILTER_SANITIZE_URL );
		if ( filter_var( $encoded_url, FILTER_VALIDATE_URL ) ) {
			return true;
		}

		// Additional check for URLs with Unicode in filename
		$parsed = parse_url( $url );
		if ( ! $parsed || ! isset( $parsed['scheme'], $parsed['host'] ) ) {
			return false;
		}

		// Check if scheme and host are valid
		return in_array( $parsed['scheme'], array( 'http', 'https' ) ) && ! empty( $parsed['host'] );
	}
}
