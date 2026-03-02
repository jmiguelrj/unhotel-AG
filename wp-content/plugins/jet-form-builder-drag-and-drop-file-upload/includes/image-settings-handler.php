<?php
namespace JFB_Advanced_Media;

// Prevent direct access to the file.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Handles WordPress image settings based on JetFormBuilder field configuration.
 *
 * This class applies custom image quality and size settings to WordPress
 * image processing filters when processing uploads from JetFormBuilder fields.
 *
 * @since 1.0.0
 */
class Image_Settings_Handler {

	/**
	 * Current image quality setting.
	 *
	 * @var int
	 */
	private static $current_image_quality = 100;

	/**
	 * Current max image width setting.
	 *
	 * @var int
	 */
	private static $current_max_width = 0;

	/**
	 * Current max image height setting.
	 *
	 * @var int
	 */
	private static $current_max_height = 0;

	/**
	 * Apply image quality and size filters for server-side processing.
	 *
	 * This method applies WordPress filters for image processing when files
	 * are being saved to the server (both on_submit and instant modes with save_uploaded_file = true).
	 *
	 * @param array $settings Field settings containing image processing parameters.
	 * @since 1.0.0
	 */
	public static function apply_image_filters( array $settings ): void {
		// Check if files should be saved to server
		$raw_flag  = $settings['save_uploaded_file'] ?? '1';
		$save_flag = filter_var( $raw_flag, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );

		// Only apply WordPress filters when files are being saved to server
		// For client-side processing (save_uploaded_file = false), JavaScript handles image processing
		if ( ! $save_flag ) {
			return;
		}

		// Get image settings from field configuration
		$max_image_width  = intval( $settings['max_image_width'] ?? 0 );
		$max_image_height = intval( $settings['max_image_height'] ?? 0 );
		$image_quality    = intval( $settings['image_quality'] ?? 100 );

		// Store current settings in static variables
		self::$current_image_quality = $image_quality;
		self::$current_max_width     = $max_image_width;
		self::$current_max_height    = $max_image_height;

		// Apply quality filter if set
		if ( $image_quality > 0 && $image_quality <= 100 ) {
			add_filter( 'jpeg_quality', array( __CLASS__, 'get_jpeg_quality' ), 10 );
			add_filter( 'wp_editor_set_quality', array( __CLASS__, 'get_wp_editor_quality' ), 10 );
		}

		// Apply size filter if dimensions are set
		if ( $max_image_width > 0 || $max_image_height > 0 ) {
			add_filter( 'intermediate_image_sizes_advanced', array( __CLASS__, 'filter_image_sizes' ), 10, 2 );
		}
	}



	/**
	 * Remove image quality and size filters after processing.
	 *
	 * This method removes WordPress filters that were applied for image processing
	 * to prevent them from affecting other operations.
	 *
	 * @since 1.0.0
	 */
	public static function remove_image_filters(): void {
		// Remove our specific quality filters by priority
		remove_filter( 'jpeg_quality', array( __CLASS__, 'get_jpeg_quality' ), 10 );
		remove_filter( 'wp_editor_set_quality', array( __CLASS__, 'get_wp_editor_quality' ), 10 );

		// Remove our specific size filter by priority
		remove_filter( 'intermediate_image_sizes_advanced', array( __CLASS__, 'filter_image_sizes' ), 10 );
	}

	/**
	 * Get JPEG quality setting.
	 *
	 * @return int JPEG quality.
	 * @since 1.0.0
	 */
	public static function get_jpeg_quality(): int {
		return self::$current_image_quality ?? 100;
	}

	/**
	 * Get WP Editor quality setting.
	 *
	 * @return int WP Editor quality.
	 * @since 1.0.0
	 */
	public static function get_wp_editor_quality(): int {
		return self::$current_image_quality ?? 100;
	}

	/**
	 * Filter image sizes for resizing.
	 *
	 * @param array $sizes Image sizes.
	 * @param array $metadata Image metadata.
	 * @return array Filtered image sizes.
	 * @since 1.0.0
	 */
	public static function filter_image_sizes( $sizes, $metadata ) {
		$max_width = self::$current_max_width ?? 0;
		$max_height = self::$current_max_height ?? 0;

		if ( ! $max_width && ! $max_height ) {
			return $sizes;
		}

		// Get original dimensions
		$original_width  = $metadata['width'] ?? 0;
		$original_height = $metadata['height'] ?? 0;

		if ( ! $original_width || ! $original_height ) {
			return $sizes;
		}

		// Calculate target dimensions
		$target_width  = $original_width;
		$target_height = $original_height;
		$scale         = 1;

		if ( $max_width && $original_width > $max_width ) {
			$scale = $max_width / $original_width;
		}
		if ( $max_height && $original_height * $scale > $max_height ) {
			$scale = $max_height / $original_height;
		}

		// If no scaling is needed, return original sizes
		if ( $scale >= 1 ) {
			return $sizes;
		}

		$target_width  = round( $original_width * $scale );
		$target_height = round( $original_height * $scale );

		// Filter out sizes larger than our target dimensions
		$filtered_sizes = array();

		foreach ( $sizes as $size_name => $size_data ) {
			$size_width  = $size_data['width'] ?? 0;
			$size_height = $size_data['height'] ?? 0;

			// Keep sizes that are smaller than or equal to our target dimensions
			if ( $size_width <= $target_width && $size_height <= $target_height ) {
				$filtered_sizes[ $size_name ] = $size_data;
			}
		}

		return $filtered_sizes;
	}

	/**
	 * Process original file after upload to apply quality and size settings.
	 *
	 * This method is called for server-side processing when files are being saved
	 * (both on_submit and instant modes with save_uploaded_file = true).
	 *
	 * @param array $metadata Attachment metadata.
	 * @param int $attachment_id Attachment ID.
	 * @param array $settings Field settings containing image processing parameters.
	 * @return array Modified metadata.
	 * @since 1.0.0
	 */
	public static function process_original_file( $metadata, $attachment_id, array $settings ): array {
		// Get image settings from field configuration
		$max_image_width  = intval( $settings['max_image_width'] ?? 0 );
		$max_image_height = intval( $settings['max_image_height'] ?? 0 );
		$image_quality    = intval( $settings['image_quality'] ?? 100 );

		// Get attachment file path
		$file_path = get_attached_file( $attachment_id );
		if ( ! $file_path || ! file_exists( $file_path ) ) {
			self::remove_image_filters();
			return $metadata;
		}

		// Only process images
		$file_type = wp_check_filetype( $file_path );
		if ( ! $file_type['type'] || strpos( $file_type['type'], 'image/' ) !== 0 ) {
			self::remove_image_filters();
			return $metadata;
		}

		// Get original dimensions
		$original_width  = $metadata['width'] ?? 0;
		$original_height = $metadata['height'] ?? 0;

		if ( ! $original_width || ! $original_height ) {
			self::remove_image_filters();
			return $metadata;
		}

		// Calculate target dimensions
		$target_width  = $original_width;
		$target_height = $original_height;
		$scale         = 1;

		if ( $max_image_width && $original_width > $max_image_width ) {
			$scale = $max_image_width / $original_width;
		}
		if ( $max_image_height && $original_height * $scale > $max_image_height ) {
			$scale = $max_image_height / $original_height;
		}

		// If no scaling is needed and quality is default, return original metadata
		if ( $scale >= 1 && $image_quality >= 100 ) {
			self::remove_image_filters();
			return $metadata;
		}

		$target_width  = round( $original_width * $scale );
		$target_height = round( $original_height * $scale );

		// Process the image
		$editor = wp_get_image_editor( $file_path );
		if ( is_wp_error( $editor ) ) {
			self::remove_image_filters();
			return $metadata;
		}

		// Resize image if needed
		if ( $scale < 1 ) {
			$editor->resize( $target_width, $target_height, false );
		}

		// Set quality and convert PNG to JPEG for better compression
		$file_type = wp_check_filetype( $file_path );
		if ( 'image/jpeg' === $file_type['type'] ) {
			$editor->set_quality( $image_quality );
		} elseif ( 'image/png' === $file_type['type'] && 100 > $image_quality ) {

			if ( ! is_wp_error( $editor ) ) {
				$editor->set_quality( $image_quality );
				$editor->save( $file_path );
			}
		}

		$result = $editor->save( $file_path );

		if ( is_wp_error( $result ) ) {
			self::remove_image_filters();
			return $metadata;
		}

		// Update metadata with new dimensions
		$metadata['width']  = $target_width;
		$metadata['height'] = $target_height;

		// Remove filters after processing
		self::remove_image_filters();

		return $metadata;
	}
}
