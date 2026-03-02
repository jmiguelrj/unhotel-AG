<?php
namespace JFB_Signature_Field\Blocks;

use JFBSignatureFieldCore\JetFormBuilder\BlocksManager as Blocks_Manager;
use JFB_Signature_Field\Plugin;

class Manager extends Blocks_Manager {

	/**
	 * Get registered blocks
	 *
	 * @return array
	 */
	public function fields() {
		return array(
			new Signature_Field_Block()
		);
	}

	/**
	 * Enqueue block editor script
	 *
	 * @return void
	 */
	public function before_init_editor_assets() {

		wp_enqueue_script(
			JFB_SIGNATURE_FIELD_PLUGIN_BASE . '-editor',
			JFB_SIGNATURE_FIELD_URL . 'assets/dist/builder.editor.js',
			array(),
			JFB_SIGNATURE_FIELD_VERSION,
			true
		);

		wp_localize_script( JFB_SIGNATURE_FIELD_PLUGIN_BASE . '-editor', 'JFBSignatureFieldData', array(
			'isSVGAllowed' => Plugin::instance()->is_svg_upload_allowed()
		) );
	}
}
