<?php
namespace JFB_Advanced_Media\Styles;

// Prevent direct access to the file.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Manager {

	protected $framework;

	public function __construct() {

		$this->framework = new Loader(
			array(
				JFB_ADVANCED_MEDIA_PATH . 'includes/styles/blocks-style-manager/style-manager.php',
			)
		);

		add_action( 'init', array( $this, 'register_blocks' ), 99 );
	}

	/**
	 * Register blocks with style manager.
	 */
	public function register_blocks() {

		$module_data = $this->framework->get_included_module_data( 'style-manager.php' );

		$manager = new \Crocoblock\Blocks_Style\Manager(
			array(
				'path' => $module_data['path'],
				'url'  => $module_data['url'],
			)
		);

		$block_name = 'jet-forms/drag-and-drop-file-upload';

		$manager->register_block_support( $block_name );
		$block = $manager->get_block( $block_name );

		if ( ! $block ) {
			return;
		}

		$block->start_section(
			array(
				'id'           => 'section_upload_area',
				'initial_open' => true,
				'title'        => 'Upload Area',
			)
		);

		$block->add_control(
			array(
				'id'           => 'area_bg_color',
				'label'        => 'Upload Area Background',
				'type'         => 'color-picker',
				'css_var'      => array(
					'full_name' => '--jfam-upload-area-bg-color',
				),
			)
		);

		$block->add_control(
			array(
				'id'           => 'area_border_color',
				'label'        => 'Upload Area Border Color',
				'type'         => 'color-picker',
				'css_var'      => array(
					'full_name' => '--jfam-upload-area-border-color',
				),
			)
		);

		$block->add_control(
			array(
				'id'           => 'upload_area_text_color',
				'label'        => 'Upload Area Text Color',
				'type'         => 'color-picker',
				'css_var'      => array(
					'full_name' => '--jfam-upload-area-text-color',
				),
			)
		);

		$block->add_control(
			array(
				'id'           => 'browse_files_text_color',
				'label'        => 'Browse Files Button Text Color',
				'type'         => 'color-picker',
				'css_var'      => array(
					'full_name' => '--jfam-browse-files-text-color',
				),
			)
		);

		$block->add_control(
			array(
				'id'           => 'browse_files_hover_text_color',
				'label'        => 'Browse Files Button Hover Text Color',
				'type'         => 'color-picker',
				'css_var'      => array(
					'full_name' => '--jfam-browse-files-hover-text-color',
				),
			)
		);

		$block->add_control(
			array(
				'id'           => 'browse_files_background_color',
				'label'        => 'Browse Files Button Background Color',
				'type'         => 'color-picker',
				'css_var'      => array(
					'full_name' => '--jfam-browse-files-background-color',
				),
			)
		);

		$block->add_control(
			array(
				'id'           => 'browse_files_hover_background_color',
				'label'        => 'Browse Files Button Hover Background Color',
				'type'         => 'color-picker',
				'css_var'      => array(
					'full_name' => '--jfam-browse-files-hover-background-color',
				),
			)
		);

		$block->add_control(
			array(
				'id'           => 'browse_files_border_color',
				'label'        => 'Browse Files Button Border Color',
				'type'         => 'color-picker',
				'css_var'      => array(
					'full_name' => '--jfam-browse-files-border-color',
				),
			)
		);

		$block->add_control(
			array(
				'id'           => 'browse_files_hover_border_color',
				'label'        => 'Browse Files Button Hover Border Color',
				'type'         => 'color-picker',
				'css_var'      => array(
					'full_name' => '--jfam-browse-files-hover-border-color',
				),
			)
		);

		$block->add_control(
			array(
				'id'           => 'upload_file_background_color',
				'label'        => 'Upload File Background Color',
				'type'         => 'color-picker',
				'css_var'      => array(
					'full_name' => '--jfam-upload-file-background-color',
				),
			)
		);

		$block->add_control(
			array(
				'id'           => 'upload_file_icon_color',
				'label'        => 'Upload File Icon Color',
				'type'         => 'color-picker',
				'css_var'      => array(
					'full_name' => '--jfam-upload-file-icon-color',
				),
			)
		);

		$block->add_control(
			array(
				'id'           => 'library_button_bg_color',
				'label'        => 'Library Button Background Color',
				'type'         => 'color-picker',
				'css_var'      => array(
					'full_name' => '--jfam-library-button-bg-color',
				),
			)
		);

		$block->add_control(
			array(
				'id'           => 'library_button_hover_bg_color',
				'label'        => 'Library Button Hover Background Color',
				'type'         => 'color-picker',
				'css_var'      => array(
					'full_name' => '--jfam-library-button-hover-bg-color',
				),
			)
		);

		$block->add_control(
			array(
				'id'           => 'library_button_text_color',
				'label'        => 'Library Button Text Color',
				'type'         => 'color-picker',
				'css_var'      => array(
					'full_name' => '--jfam-library-button-text-color',
				),
			)
		);

		$block->add_control(
			array(
				'id'           => 'library_button_hover_text_color',
				'label'        => 'Library Button Hover Text Color',
				'type'         => 'color-picker',
				'css_var'      => array(
					'full_name' => '--jfam-library-button-hover-text-color',
				),
			)
		);

		$block->add_control(
			array(
				'id'           => 'library_button_border_color',
				'label'        => 'Library Button Border Color',
				'type'         => 'color-picker',
				'css_var'      => array(
					'full_name' => '--jfam-library-button-border-color',
				),
			)
		);

		$block->add_control(
			array(
				'id'           => 'library_button_hover_border_color',
				'label'        => 'Library Button Hover Border Color',
				'type'         => 'color-picker',
				'css_var'      => array(
					'full_name' => '--jfam-library-button-hover-border-color',
				),
			)
		);

		$block->add_control(
			array(
				'id'           => 'error_message_color',
				'label'        => 'Error Message Text Color',
				'type'         => 'color-picker',
				'css_var'      => array(
					'full_name' => '--jfam-error-message-color',
				),
			)
		);

		$block->add_control(
			array(
				'id'           => 'error_message_background_color',
				'label'        => 'Error Message Background Color',
				'type'         => 'color-picker',
				'css_var'      => array(
					'full_name' => '--jfam-error-message-background-color',
				),
			)
		);

		$block->end_section();
	}
}
