<?php


namespace Jet_FB_HR_Select\JetFormBuilder\Blocks;


use Jet_FB_HR_Select\Plugin;
use JetHRSelectCore\JetFormBuilder\BlocksManager;

class ManagerBlocks extends BlocksManager {

	public function fields() {
		return array(
			new HrSelect()
		);
	}

	/**
	 * @return void
	 */
	public function before_init_editor_assets() {
		wp_enqueue_script(
			Plugin::instance()->slug . '-editor',
			Plugin::instance()->plugin_url( 'assets/dist/builder.editor.js' ),
			array(),
			Plugin::instance()->get_version(),
			true
		);
	}

	public function on_base_need_update() {
		$this->add_admin_notice( 'warning', __(
			'<b>Warning</b>: <b>JetFormBuilder Hierarchical Select</b> needs <b>JetFormBuilder</b> update.',
			'jet-form-builder-hr-select'
		) );
	}

	public function on_base_need_install() {
	}

}