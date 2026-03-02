<?php


namespace JFB\LimitResponses\JetFormBuilder;

use JFB\LimitResponses\LimitResponses;
use JFB\LimitResponses\Plugin;
use JFB\LimitResponses\Vendor\JFBCore\JetFormBuilder\PluginManager;

class LimitPluginManager extends PluginManager {

	public function plugin_version_compare(): string {
		return '1.2.0';
	}

	public function before_init_editor_assets() {
		wp_enqueue_script(
			Plugin::SLUG,
			JET_FB_LIMIT_FORM_RESPONSES_URL . 'assets/dist/builder.bundle.js',
			array(),
			JET_FB_LIMIT_FORM_RESPONSES_VERSION,
			true
		);
	}

	public function meta_data() {
		return array(
			LimitResponses::PLUGIN_META_KEY => array()
		);
	}

	public function on_base_need_update() {
		$this->add_admin_notice( 'warning', __(
			'<b>Warning</b>: <b>JetFormBuilder Limit Form Responses</b> needs <b>JetFormBuilder</b> update.',
			'jet-form-builder-limit-form-responses'
		) );
	}

	public function on_base_need_install() {
	}
}