<?php
namespace JFB_Advanced_Media\Blocks;

use JFB_Advanced_Media\Blocks\Advanced_Media_Field_Block;
use JFB_Modules\Block_Parsers\Module;
// use JFB_Advanced_Media\Blocks\Advanced_Media_Field_Parser;
use Jet_Form_Builder\Classes\Tools;

/**
 * Advanced Media Field Manager.
 *
 * This class manages the registration and initialization of advanced media field blocks,
 * including block registration, asset enqueuing, and parser registration.
 *
 * @since 1.0.0
 */
class Manager {

	/**
	 * Register the manager
	 *
	 * @since 1.0.0
	 */
	public static function register() {
		$instance = new self();
		$instance->init();
	}

	/**
	 * Initialize the manager
	 *
	 * @since 1.0.0
	 */
	public function init() {
		add_action( 'jet-form-builder/blocks/register', array( $this, 'register_blocks' ) );
		add_action( 'jet-form-builder/editor-assets/before', array( $this, 'enqueue_editor_assets' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );

		// Elementor compatibility
		add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'enqueue_elementor_editor_styles' ) );
		add_action( 'elementor/preview/enqueue_styles', array( $this, 'enqueue_elementor_preview_styles' ) );
		add_action( 'elementor/frontend/after_enqueue_styles', array( $this, 'enqueue_elementor_frontend_styles' ) );

		// Register custom request parser early.
		add_action( 'init', array( $this, 'register_parsers' ), 5 );
	}

	/**
	 * Register the custom Advanced Media Field parser so it is available
	 * for request handling / sanitization phase.
	 *
	 * @since 1.0.0
	 */
	public function register_parsers() {
		if ( class_exists( Module::class ) ) {
			Module::instance()->install( new Advanced_Media_Field_Parser() );
		}
	}

	/**
	 * Register blocks
	 *
	 * @param mixed $manager Block manager instance.
	 * @since 1.0.0
	 */
	public function register_blocks( $manager ) {
		// Register the Advanced Media Field block
		$manager->register_block_type( new Advanced_Media_Field_Block() );
	}

	/**
	 * Enqueue editor assets
	 *
	 * @since 1.0.0
	 */
	public function enqueue_editor_assets() {
		$handle = JFB_ADVANCED_MEDIA_PLUGIN_BASE . '-editor';

		wp_enqueue_script(
			$handle,
			JFB_ADVANCED_MEDIA_URL . 'assets/dist/builder.editor.js',
			array(),
			JFB_ADVANCED_MEDIA_VERSION,
			true
		);

		// Enqueue main field styles for preview in admin
		wp_enqueue_style(
			'jet-form-builder-advanced-media-editor-styles',
			JFB_ADVANCED_MEDIA_URL . 'assets/dist/editor.css',
			array(),
			JFB_ADVANCED_MEDIA_VERSION
		);

		wp_enqueue_style(
			'jet-form-builder-advanced-media-field-styles',
			JFB_ADVANCED_MEDIA_URL . 'assets/dist/advanced-media-field.css',
			array(),
			JFB_ADVANCED_MEDIA_VERSION
		);

		// Localize data for editor
		$mime_types = Tools::get_allowed_mimes_list_for_js();

		wp_localize_script(
			$handle,
			'jetFormAdvancedMediaFieldEditorData',
			array(
				'userAccess'   => array(
					array(
						'value' => 'all',
						'label' => __( 'Any registered user', 'jet-form-builder-advanced-media' ),
					),
					array(
						'value' => 'upload_files',
						'label' => __( 'Any user who is allowed to upload files', 'jet-form-builder-advanced-media' ),
					),
					array(
						'value' => 'edit_posts',
						'label' => __( 'Any user who is allowed to edit posts', 'jet-form-builder-advanced-media' ),
					),
					array(
						'value' => 'any_user',
						'label' => __( 'Any user (incl. Guest)', 'jet-form-builder-advanced-media' ),
					),
				),
				'valueFormats' => array(
					array(
						'value' => 'id',
						'label' => __( 'Attachment ID', 'jet-form-builder-advanced-media' ),
					),
					array(
						'value' => 'url',
						'label' => __( 'Attachment URL', 'jet-form-builder-advanced-media' ),
					),
					array(
						'value' => 'both',
						'label' => __( 'Array with attachment ID and URL', 'jet-form-builder-advanced-media' ),
					),
					array(
						'value' => 'ids',
						'label' => __( 'Array of attachment IDs', 'jet-form-builder-advanced-media' ),
					),
				),
				'mime_types'   => $mime_types,
			)
		);
	}

	/**
	 * Enqueue styles for Elementor editor
	 *
	 * @since 1.0.0
	 */
	public function enqueue_elementor_editor_styles() {
		wp_enqueue_style(
			'jet-form-builder-advanced-media-elementor-editor',
			JFB_ADVANCED_MEDIA_URL . 'assets/dist/editor.css',
			array(),
			JFB_ADVANCED_MEDIA_VERSION
		);

		wp_enqueue_style(
			'jet-form-builder-advanced-media-field-elementor',
			JFB_ADVANCED_MEDIA_URL . 'assets/dist/advanced-media-field.css',
			array(),
			JFB_ADVANCED_MEDIA_VERSION
		);
	}

	/**
	 * Enqueue styles for Elementor preview
	 *
	 * @since 1.0.0
	 */
	public function enqueue_elementor_preview_styles() {
		wp_enqueue_style(
			'jet-form-builder-advanced-media-elementor-preview',
			JFB_ADVANCED_MEDIA_URL . 'assets/dist/advanced-media-field.css',
			array(),
			JFB_ADVANCED_MEDIA_VERSION
		);
	}

	/**
	 * Enqueue styles for Elementor frontend
	 *
	 * @since 1.0.0
	 */
	public function enqueue_elementor_frontend_styles() {
		wp_enqueue_style(
			'jet-form-builder-advanced-media-elementor-frontend',
			JFB_ADVANCED_MEDIA_URL . 'assets/dist/advanced-media-field.css',
			array(),
			JFB_ADVANCED_MEDIA_VERSION
		);
	}
}
