<?php
namespace JFB_Signature_Field\Blocks;

use Jet_FB_Address_Autocomplete\Plugin;
use Jet_Form_Builder\Blocks\Types\Base;
use JFBSignatureFieldCore\JetFormBuilder\SmartBaseBlock as Smart_Base_Block;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define Text field block class
 */
class Signature_Field_Block extends Base {

	use Smart_Base_Block;

	/**
	 * Returns block name
	 *
	 * @return [type] [description]
	 */
	public function get_name() {
		return 'signature-field';
	}

	public function get_field_template( $path ) {
		return JFB_SIGNATURE_FIELD_PATH . 'templates/' . $path;
	}

	public function get_path_metadata_block() {
		return JFB_SIGNATURE_FIELD_PATH . 'assets/blocks-json/' . $this->get_name();
	}

	public function render_instance() {
		$this->enqueue_scripts();
		return new Signature_Field_Render( $this );
	}

	/**
	 * Returns rendered block template
	 *
	 * @return string
	 */
	public function get_template() {
		return $this->render_instance()->set_up()->complete_render();
	}

	private function enqueue_scripts() {
		wp_enqueue_script(
			JFB_SIGNATURE_FIELD_PLUGIN_BASE . '-frontend',
			JFB_SIGNATURE_FIELD_URL . 'assets/dist/frontend.js',
			array( 'jet-form-builder-frontend-forms', 'jet-plugins' ),
			JFB_SIGNATURE_FIELD_VERSION,
			true
		);
	}
}
