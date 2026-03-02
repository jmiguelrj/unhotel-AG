<?php


namespace Jet_FB_Save_Progress\JetFormBuilder;


use Jet_FB_Save_Progress\Plugin;
use Jet_FB_Save_Progress\SaveProgress;
use JetSaveProgressCore\JetFormBuilder\PluginManager;

class SavePluginManager extends PluginManager {

	public function plugin_version_compare(): string {
		return '1.2.0';
	}

	public function before_init_editor_assets() {
		wp_enqueue_script(
			Plugin::instance()->slug,
			Plugin::instance()->plugin_url( 'assets/dist/builder.bundle.js' ),
			array( 'wp-i18n' ),
			Plugin::instance()->get_version(),
			true
		);
	}

	public function meta_data() {
		return array(
			SaveProgress::instance()->slug => array()
		);
	}

	public function on_base_need_update() {
		$this->add_admin_notice( 'warning', __(
			'<b>Warning</b>: <b>JetFormBuilder Save From Progress</b> needs <b>JetFormBuilder</b> update.',
			'jet-form-builder-save-progress'
		) );
	}

	public function on_base_need_install() {
	}
}