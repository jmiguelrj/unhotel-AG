<?php
namespace JFB_Advanced_Media\Blocks;

use Jet_Form_Builder\Blocks\Types\Base;
use Jet_Form_Builder\Classes\Tools;
use JFB_Advanced_Media\Blocks\Advanced_Media_Field_Render;
use JFB_Advanced_Media\Presets\Advanced_Media_Preset;
use JFBAdvancedMediaCore\JetFormBuilder\SmartBaseBlock as Smart_Base_Block;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Advanced Media Field Block.
 *
 * This class defines the advanced media field block for JetFormBuilder
 * including its configuration, rendering, and validation settings.
 *
 * @since 1.0.0
 */
class Advanced_Media_Field_Block extends Base {

	use Smart_Base_Block;

	/**
	 * Get the block name.
	 *
	 * @return string Block name identifier.
	 * @since 1.0.0
	 */
	public function get_name() {
		return 'advanced-media-field';
	}

	/**
	 * Get the field name with array suffix if multiple files allowed.
	 *
	 * @param string $name Field name.
	 * @return string Field name with suffix.
	 * @since 1.0.0
	 */
	public function get_field_name( $name = '' ) {
		$max_files = absint( $this->block_attrs['max_files'] ?? 1 );

		$suffix = '';
		if ( 1 < $max_files ) {
			$suffix = '[]';
		}

		return ( parent::get_field_name() . $suffix );
	}

	/**
	 * Set block data and initialize presets.
	 *
	 * @param array $attributes Block attributes.
	 * @param string|null $content Block content.
	 * @param \WP_Block|null $wp_block WordPress block object.
	 * @since 1.0.0
	 */
	public function set_block_data( $attributes, $content = null, $wp_block = null ) {
		parent::set_block_data( $attributes, $content, $wp_block );

		// Initialize preset system for Advanced Media field
		$this->set_preset();
	}

	/**
	 * Check if this block uses preset system.
	 *
	 * @return bool Always true for Advanced Media field.
	 * @since 1.0.0
	 */
	public function use_preset() {
		return true;
	}

	/**
	 * Get the field template path.
	 *
	 * @param string $path Template path.
	 * @return string Full template path.
	 * @since 1.0.0
	 */
	public function get_field_template( $path ) {
		return JFB_ADVANCED_MEDIA_PATH . 'templates/' . $path;
	}

	/**
	 * Get the path to block metadata.
	 *
	 * @return string Path to block metadata.
	 * @since 1.0.0
	 */
	public function get_path_metadata_block() {
		return JFB_ADVANCED_MEDIA_PATH . 'assets/blocks-json/' . $this->get_name();
	}

	/**
	 * Render the block instance.
	 *
	 * @return Advanced_Media_Field_Render Render instance.
	 * @since 1.0.0
	 */
	public function render_instance() {
		return new Advanced_Media_Field_Render( $this );
	}

	/**
	 * Returns rendered block template
	 *
	 * @return string
	 */
	public function get_template() {
		$render = $this->render_instance()->set_up()->complete_render();

		// Enqueue scripts for editor preview
		if ( \Jet_Form_Builder\Classes\Tools::is_editor() ) {
			$this->enqueue_scripts();
		}

		return $render;
	}

	/**
	 * Get block renderer with validation support
	 *
	 * @param null $wp_block
	 * @return string
	 */
	public function get_block_renderer( $wp_block = null ) {
		$render = $this->get_template();

		if ( \Jet_Form_Builder\Classes\Tools::is_editor() ) {
			return $render;
		}

		// Check if any upload methods are available
		if ( ! $this->has_available_upload_methods() ) {
			return $render;
		}
		$this->enqueue_scripts();

		// Enqueue WordPress media if needed
		if ( $this->should_enqueue_wp_media() ) {
			wp_enqueue_media();
		}

		return $render;
	}

	/**
	 * Check if any upload methods are available.
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	private function has_available_upload_methods() {
		$has_drag_n_drop = $this->block_attrs['drag_n_drop'] ?? true;
		$has_library     = ( $this->block_attrs['wp_media_library'] ?? false ) && is_user_logged_in();

		$has_upload_method = $has_drag_n_drop || $has_library;

		return $has_upload_method;
	}

	/**
	 * Check if WordPress media should be enqueued.
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	private function should_enqueue_wp_media() {
		return ( $this->block_attrs['wp_media_library'] ?? false ) && is_user_logged_in();
	}

	/**
	 * Register block type and scripts.
	 *
	 * @since 1.0.0
	 */
	public function register_block_type() {
		parent::register_block_type();

		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
		add_action( 'jet_plugins/frontend/register_scripts', array( $this, 'register_scripts' ) );
	}

	/**
	 * Register scripts for the block.
	 *
	 * @since 1.0.0
	 */
	public function register_scripts() {
		wp_register_script(
			JFB_ADVANCED_MEDIA_PLUGIN_BASE . '-frontend',
			JFB_ADVANCED_MEDIA_URL . 'assets/dist/frontend.js',
			array( 'jet-form-builder-frontend-forms', 'jet-plugins', 'jquery', 'media-views' ),
			JFB_ADVANCED_MEDIA_VERSION,
			true
		);

		wp_register_style(
			JFB_ADVANCED_MEDIA_PLUGIN_BASE . '-frontend',
			JFB_ADVANCED_MEDIA_URL . 'assets/dist/advanced-media-field.css',
			array(),
			JFB_ADVANCED_MEDIA_VERSION
		);

		if ( ! class_exists( 'Jet_Popup' ) ) {
			return;
		}

		// Check if there are any popups on the current page
		$popups = jet_popup()->generator->get_attached_popups();

		// Also check if we're in a popup context
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing
		$is_popup_context = wp_doing_ajax() ||
							( isset( $_GET['jet_popup'] ) && sanitize_text_field( wp_unslash( $_GET['jet_popup'] ) ) ) ||
							( isset( $_POST['jet_popup'] ) && sanitize_text_field( wp_unslash( $_POST['jet_popup'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( empty( $popups ) && ! $is_popup_context ) {
			return;
		}

		// Ensure WordPress media scripts are loaded in correct order
		wp_enqueue_media();
	}

	/**
	 * Enqueue scripts and styles for the block.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		$script_handle = JFB_ADVANCED_MEDIA_PLUGIN_BASE . '-frontend';

		// Ensure the script is registered
		if ( ! wp_script_is( $script_handle, 'registered' ) ) {
			$this->register_scripts();
		}

		// Enqueue the script
		wp_enqueue_script( $script_handle );

		// Localize script data with success checking
		$this->localize_script_data( $script_handle );

		// Enqueue styles
		wp_enqueue_style( JFB_ADVANCED_MEDIA_PLUGIN_BASE . '-frontend' );
		wp_enqueue_style( 'media-views' );
		wp_enqueue_style( 'dashicons' );
		wp_enqueue_script( 'media-editor' );
	}

	/**
	 * Localize script data with error handling for Windows compatibility.
	 *
	 * @param string $script_handle Script handle
	 * @since 1.0.0
	 */
	private function localize_script_data( $script_handle ) {
		$frontend_data = $this->get_frontend_data();

		// Attempt localization with fallback for Windows
		$primary_success = wp_localize_script( $script_handle, 'jetFormAdvancedMediaFieldFrontendData', $frontend_data );

		// If localization fails, use inline script as fallback
		if ( ! $primary_success ) {
			$this->add_inline_script_fallback( $script_handle, $frontend_data );
		}
	}

	/**
	 * Add inline script as fallback for failed wp_localize_script.
	 *
	 * @param string $script_handle Script handle
	 * @param array $frontend_data Frontend data
	 * @since 1.0.0
	 */
	private function add_inline_script_fallback( $script_handle, $frontend_data ) {
		$inline_script = sprintf(
			'window.jetFormAdvancedMediaFieldFrontendData = %s;',
			wp_json_encode( $frontend_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
		);

		wp_add_inline_script( $script_handle, $inline_script, 'before' );
	}

	/**
	 * Get frontend data for script localization.
	 *
	 * @return array Frontend data
	 * @since 1.0.0
	 */
	private function get_frontend_data() {
		$mime_types = \Jet_Form_Builder\Classes\Tools::get_allowed_mimes_list_for_js();
		$wp_icons   = $this->get_wp_mime_type_icons();

		// Get custom MIME types from WordPress upload_mimes filter
		$custom_mime_types = $this->get_custom_mime_types();

		return array(
			'mime_types'        => $mime_types,
			'custom_mime_types' => $custom_mime_types,
			'wp_icons'          => $wp_icons,
			'userCapabilities'  => array(
				'canUpload'           => current_user_can( 'upload_files' ),
				'canInsertAttachment' => current_user_can( 'upload_files' ) && is_user_logged_in(),
				'isGuest'             => ! is_user_logged_in(),
			),
			'messages'          => $this->get_localized_messages(),
			'ajax'              => array(
				'url'    => admin_url( 'admin-ajax.php' ),
				'nonce'  => wp_create_nonce( 'jfb_advanced_media_upload' ),
				'action' => 'jfb_advanced_media_upload',
			),
		);
	}

	/**
	 * Get custom MIME types from WordPress upload_mimes filter.
	 *
	 * @return array Custom MIME types
	 * @since 1.0.3
	 */
	private function get_custom_mime_types() {
		// Get all MIME types including custom ones from upload_mimes filter
		$all_mime_types = get_allowed_mime_types();

		// Get standard WordPress MIME types for comparison
		$standard_mime_types = wp_get_mime_types();

		// Find custom MIME types (those not in standard WordPress list)
		$custom_mime_types = array();

		foreach ( $all_mime_types as $extension => $mime_type ) {
			if ( ! isset( $standard_mime_types[ $extension ] ) ) {
				$custom_mime_types[ $extension ] = $mime_type;
			}
		}

		return $custom_mime_types;
	}

	/**
	 * Get localized messages for the frontend.
	 *
	 * @return array Localized messages
	 * @since 1.0.0
	 */
	private function get_localized_messages() {
		return array(
			'fileTypeNotAllowed'   => __( 'File type is not allowed', 'jet-form-builder-advanced-media' ),
			'fileAlreadyExist'     => __( 'File already exist', 'jet-form-builder-advanced-media' ),
			// translators: %s is the file size in MB
			'fileSizeExceedsLimit' => __( 'File size exceeds %sMB limit', 'jet-form-builder-advanced-media' ),
			// translators: %s is the maximum number of files allowed
			'maxFilesAllowed'      => __( 'Maximum %s files allowed', 'jet-form-builder-advanced-media' ),
			// translators: %s is the maximum number of files allowed
			'maxFilesAllowedDot'   => __( 'Maximum %s files allowed.', 'jet-form-builder-advanced-media' ),
			// translators: %s is the list of rejected files
			'filesRejected'        => __( 'File(s) was rejected: %s', 'jet-form-builder-advanced-media' ),
			'fileRejected'         => __( "File '%s' was rejected: %s", 'jet-form-builder-advanced-media' ),
			'pleaseWaitUploading'  => __( 'Please wait, files are still uploading...', 'jet-form-builder-advanced-media' ),
			'fileReadingTimeout'   => __( 'File reading timeout', 'jet-form-builder-advanced-media' ),
			'required'             => __( 'This field is required. Please upload or select at least one file.', 'jet-form-builder-advanced-media' ),
		);
	}

	/**
	 * Localize data for the block editor.
	 *
	 * @param mixed $editor Editor instance.
	 * @param string $handle Script handle.
	 * @since 1.0.0
	 */
	public function block_data( $editor, $handle ) {
		$frontend_data = $this->get_frontend_data();

		wp_localize_script(
			$handle,
			'jetFormAdvancedMediaFieldFrontendData',
			$frontend_data
		);
	}

	/**
	 * Get WordPress native MIME type icons URLs.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of icon URLs keyed by MIME type category.
	 */
	private function get_wp_mime_type_icons() {
		$icons = array();

		// Define MIME type categories that WordPress supports
		$mime_categories = array(
			'archive'      => 'application/zip',
			'audio'        => 'audio/mpeg',
			'code'         => 'text/html',
			'default'      => 'application/octet-stream',
			'document'     => 'application/pdf',
			'interactive'  => 'application/x-shockwave-flash',
			'spreadsheet'  => 'application/vnd.ms-excel',
			'text'         => 'text/plain',
			'video'        => 'video/mp4',
		);

		foreach ( $mime_categories as $category => $mime_type ) {
			// Get SVG version first (preferred)
			$svg_icon = wp_mime_type_icon( $mime_type, '.svg' );
			$png_icon = wp_mime_type_icon( $mime_type, '.png' );

			$svg_content = $this->get_svg_by_url( $svg_icon );

			// Ensure SVG content is properly formatted for JSON
			if ( $svg_content ) {
				// Remove any potential problematic characters
				$svg_content = str_replace( array( "\r", "\n", "\t" ), '', $svg_content );
				$svg_content = preg_replace( '/\s+/', ' ', $svg_content ); // Normalize whitespace
				$svg_content = trim( $svg_content );

				// Replace double quotes with HTML entities to avoid JSON escaping issues
				$svg_content = str_replace( '"', '&quot;', $svg_content );
			}

			$icons[ $category ] = array(
				'svg'         => $svg_icon ? $svg_icon : '',
				'svg_content' => $svg_content ? $svg_content : '',
				'png'         => $png_icon ? $png_icon : '',
			);
		}

		return $icons;
	}

	/**
	 * Retrieves SVG content by URL and returns safe SVG code.
	 *
	 * @param string $url URL to the SVG file.
	 * @return string|null SVG code or null if the file is not found.
	 */
	public function get_svg_by_url( $url ) {
		if ( '.svg' !== substr( (string) $url, -4 ) ) {
			return null;
		}

		$site_url = site_url();
		$relative_path = str_replace( $site_url, '', $url );
		$full_path = ABSPATH . ltrim( $relative_path, '/' );

		if ( ! file_exists( $full_path ) ) {
			return null;
		}

		$svg_content = file_get_contents( $full_path );

		if ( ! $svg_content ) {
			return null;
		}

		// Use wp_kses with ENT_NOQUOTES to prevent quote escaping
		$safe_svg = wp_kses(
			$svg_content,
			array(
				'svg' => array(
					'xmlns' => true,
					'width' => true,
					'height' => true,
					'viewBox' => true,
					'fill' => true,
					'stroke' => true,
					'stroke-width' => true,
					'class' => true,
					'aria-hidden' => true,
					'role' => true,
					'focusable' => true,
				),
				'path' => array(
					'd' => true,
					'fill' => true,
					'stroke' => true,
					'stroke-width' => true,
				),
				'g' => array(
					'fill' => true,
					'stroke' => true,
				),
			)
		);

		// Add viewBox if missing
		if ( strpos( $safe_svg, '<svg' ) !== false && strpos( $safe_svg, 'viewBox' ) === false ) {
			$safe_svg = preg_replace(
				'/<svg\b([^>]*)>/',
				'<svg$1 viewBox="0 0 120 160">',
				$safe_svg
			);
		}

		return $safe_svg;
	}

	/**
	 * Get user access options for the editor.
	 *
	 * @return array User access options.
	 * @since 1.0.0
	 */
	private function get_user_access_options(): array {
		$roles = \Jet_Form_Builder\Classes\Tools::get_user_roles_for_js();

		return $roles;
	}

	/**
	 * Get maximum files allowed.
	 *
	 * @return int Maximum files count.
	 * @since 1.0.0
	 */
	public function get_max_files(): int {
		$max_files = $this->block_attrs['max_files'] ?? 1;
		return empty( $max_files ) ? 1 : (int) $max_files;
	}

	/**
	 * Get maximum file size in bytes.
	 *
	 * @return int Maximum file size in bytes.
	 * @since 1.0.0
	 */
	public function get_max_size(): int {
		$size_in_mb = $this->block_attrs['max_size'] ?? false;

		if ( ! is_numeric( $size_in_mb ) ) {
			return wp_max_upload_size();
		}

		return (int) ( MB_IN_BYTES * $size_in_mb );
	}

	/**
	 * Get maximum size message.
	 *
	 * @return string Formatted size message.
	 * @since 1.0.0
	 */
	public function get_max_size_message(): string {
		$on_empty = 'Maximum file size: %max_size%';
		$message  = $this->block_attrs['validation']['messages']['max_size'] ?? $on_empty;

		if ( empty( $message ) ) {
			$message = $on_empty;
		}

		return str_replace( '%max_size%', size_format( $this->get_max_size() ), $message );
	}

	/**
	 * Get format message.
	 *
	 * @return string Formatted message.
	 * @since 1.0.0
	 */
	public function get_format_message(): string {
		$allowed_mimes = $this->block_attrs['allowed_mimes'] ?? array();

		if ( ! empty( $allowed_mimes ) ) {
			$on_empty = 'Allowed file types: %allowed_mimes%';
			$message  = $this->block_attrs['validation']['messages']['allowed_mimes'] ?? $on_empty;
			if ( empty( $message ) ) {
				$message = $on_empty;
			}

			$short_allowed_mimes = array_map(
				function ( $mime ) {
					return substr( strrchr( $mime, '/' ), 1 );
				},
				$allowed_mimes
			);

			$allowed_mimes = implode( ', ', $allowed_mimes );

			$result = str_replace( '%allowed_mimes%', $allowed_mimes, $message );
			$result = str_replace( '%short_allowed_mimes%', implode( ', ', $short_allowed_mimes ), $result );
			return $result;
		}

		return '';
	}

	/**
	 * Parse preset value for the field.
	 *
	 * @param mixed $preset Raw preset value.
	 * @return array Parsed files array.
	 * @since 1.0.0
	 */
	protected function parse_preset( $preset ): array {
		return Advanced_Media_Preset::parse_preset( $preset );
	}

	/**
	 * Get expected preset type.
	 *
	 * @return array Expected preset types.
	 * @since 1.0.0
	 */
	public function expected_preset_type(): array {
		return array( self::PRESET_EXACTLY );
	}

	/**
	 * Get default values from preset.
	 *
	 * @param array $attributes Block attributes.
	 * @return array Default files array.
	 * @since 1.0.0
	 */
	public function get_default_from_preset( $attributes = array() ): array {
		$preset = parent::get_default_from_preset( $attributes );
		$value  = $this->parse_preset( $preset );
		$files  = array();

		foreach ( $value as $item ) {
			if ( isset( $item['url'] ) && isset( $item['id'] ) ) {
				$files[] = array(
					'url' => $item['url'],
					'id'  => $item['id'],
				);
			} elseif ( isset( $item['url'] ) ) {
				$files[] = array(
					'url' => $item['url'],
				);
			}
		}

		return $files;
	}
}
