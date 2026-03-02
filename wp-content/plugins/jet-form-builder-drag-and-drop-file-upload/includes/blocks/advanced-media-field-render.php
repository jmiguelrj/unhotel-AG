<?php
namespace JFB_Advanced_Media\Blocks;

use Jet_Form_Builder\Blocks\Dynamic_Value;
use Jet_Form_Builder\Blocks\Render\Base;
use JFBAdvancedMediaCore\JetFormBuilder\RenderBlock;
use JFB_Advanced_Media\Plugin;
use JFB_Advanced_Media\Presets\Advanced_Media_Preset;
use Jet_Form_Builder\Classes\Attributes_Trait;
use Jet_Form_Builder\Classes\Tools;

/**
 * Advanced Media Field Render.
 *
 * This class handles rendering of advanced media fields in forms
 * including file previews, drag & drop zones, and media library integration.
 *
 * @property \JFB_Advanced_Media\Blocks\Advanced_Media_Field_Block $block_type
 * @since 1.0.0
 */
class Advanced_Media_Field_Render extends Base {

	use RenderBlock;

	/**
	 * Whether styles have been rendered.
	 *
	 * @var bool
	 * @since 1.0.0
	 */
	public static $styles_rendered = false;

	/**
	 * Attribute storage for preview files.
	 *
	 * @var Attributes_Trait
	 * @since 1.0.0
	 */
	protected $files;

	/**
	 * Get the field name.
	 *
	 * @return string Field name identifier.
	 * @since 1.0.0
	 */
	public function get_name() {
		return 'advanced-media-field';
	}

	/**
	 * Render previews for default uploaded files.
	 *
	 * This method generates HTML for file previews based on
	 * the default files configured in the block attributes,
	 * including preset files that were merged by set_preset().
	 *
	 * @return string HTML for file previews.
	 * @since 1.0.0
	 */
	protected function render_previews(): string {
		// Get files from block attributes (includes both default and preset files)
		$files = $this->block_type->block_attrs['default'] ?? array();

		if ( empty( $files ) ) {
			return '';
		}

		$preview_tpl = $this->get_preview_html();
		$html        = '';

		foreach ( $files as $file ) {
			if ( empty( $file['url'] ) && isset( $file['id'] ) && is_array( $file['id'] ) ) {
				$file = $file['id'];
			}

			$file_url = isset( $file['url'] ) ? $file['url'] : wp_get_attachment_url( $file );

			if ( empty( $file_url ) ) {
				continue;
			}

			$updated = str_replace( '%file_url%', $file_url, $preview_tpl );
			$updated = str_replace(
				'%file_name%',
				$this->get_name_from_file( $file['url'] ?? $file_url ),
				$updated
			);

			// preset field
			$updated = str_replace( '<!-- field -->', $this->get_field_preset( $file ), $updated );

			$image_ext    = array( 'jpg', 'jpeg', 'jpe', 'gif', 'png', 'svg', 'webp' );
			$img_ext_preg = '!\.(' . join( '|', $image_ext ) . ')$!i';

			if ( preg_match( $img_ext_preg, $file_url ) ) {
				$replace = sprintf( '<img src="%s" alt="" width="100px" height="100px">', esc_url( $file_url ) );
				$updated = str_replace( '<!-- preview -->', $replace, $updated );
			}

			$html .= $updated;
		}

		return $html;
	}

	/**
	 * Get preview HTML template.
	 *
	 * @return string Preview HTML template.
	 * @since 1.0.0
	 */
	protected function get_preview_html(): string {
		$template = apply_filters( 'jfb_advanced_media/preview_template_path', JFB_ADVANCED_MEDIA_PATH . 'templates/file-preview.php' );

		if ( ! file_exists( $template ) ) {
			// fallback to core template to avoid fatal error
			$template = Tools::get_global_template( 'fields/image-preview.php' );
		}

		ob_start();
		require $template;
		return ob_get_clean();
	}

	/**
	 * Get field preset HTML.
	 *
	 * @param array $file File data.
	 * @return string Field preset HTML.
	 * @since 1.0.0
	 */
	protected function get_field_preset( array $file ): string {
		ob_start();

		require JFB_ADVANCED_MEDIA_PATH . 'templates/fields/preset-advanced-media-field.php';

		return ob_get_clean();
	}

	/**
	 * Get filename from file URL.
	 *
	 * @param string $file_url File URL.
	 * @return string Filename.
	 * @since 1.0.0
	 */
	protected function get_name_from_file( $file_url ): string {
		return basename( $file_url );
	}

	/**
	 * Get files attribute handler.
	 *
	 * @return Attributes_Trait Files attribute handler.
	 * @since 1.0.0
	 */
	public function files() {
		if ( ! $this->files ) {
			$this->files = new class() {
				use Attributes_Trait;
			};
		}

		return $this->files;
	}

	/**
	 * Render the field.
	 *
	 * @param string $attrs_string Attributes string.
	 * @return string Rendered field HTML.
	 * @since 1.0.0
	 */
	public function render_field( $attrs_string ) {
		// Use standard template rendering (advanced-media-field.php)
		return $this->render_without_layout( null, $this->get_default_args_with_filter() );
	}

	/**
	 * Render the complete field with template.
	 *
	 * @param \WP_Block $wp_block WordPress block object.
	 * @param string $template Template path.
	 * @return string Rendered field HTML.
	 * @since 1.0.0
	 */
	public function render( $wp_block = null, $template = null ) {
		$max_files = $this->block_type->get_max_files();
		$max_size  = $this->block_type->get_max_size();

		// JavaScript configuration for file upload and validation
		// Include image-specific settings for image processing
		$js_config = array(
			'max_files'        => $max_files,
			'max_size'         => $max_size,
			'max_image_width'  => $this->block_type->block_attrs['max_image_width'] ?? '',
			'max_image_height' => $this->block_type->block_attrs['max_image_height'] ?? '',
			'image_quality'    => $this->block_type->block_attrs['image_quality'] ?? 100,
		);

		$data_args = htmlspecialchars( \Jet_Form_Builder\Classes\Tools::encode_json( $js_config ) );

		$this->files()->add_attribute( 'data-args', $data_args );

		// Add validation support if validation module is available
		try {
			/** @var \JFB_Modules\Validation\Module $validation_module */
			$validation_module = jet_form_builder()->module( 'validation' );
			$validation_module->add_validation_block( $this->block_type );
		} catch ( \Exception $e ) {
			// Validation module not available
		}

		$field = parent::render( $wp_block, $template );

		$template_tag = sprintf( '<template class="jet-form-builder-advanced-media__preview-template">%s</template>', $this->get_preview_html() );

		return $template_tag . $field;
	}

	/**
	 * Get media CSS.
	 *
	 * @return string Media CSS.
	 * @since 1.0.0
	 */
	public function get_media_css() {
		return '';
	}
}
