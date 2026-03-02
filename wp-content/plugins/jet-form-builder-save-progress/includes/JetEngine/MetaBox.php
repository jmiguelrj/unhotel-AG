<?php


namespace Jet_FB_Save_Progress\JetEngine;


use Jet_FB_Save_Progress\Plugin;
use Jet_FB_Save_Progress\SaveProgress;
use JetSaveProgressCore\JetEngine\RegisterFormMetaBox;

class MetaBox extends RegisterFormMetaBox {

	public function get_id() {
		return SaveProgress::instance()->slug;
	}

	public function component_slug() {
		return 'jf-save-progress';
	}

	public function get_title() {
		return __( 'Form Progress', 'jet-form-builder' );
	}

	public function register_assets() {
		wp_enqueue_script(
			Plugin::instance()->slug . '-editor',
			Plugin::instance()->plugin_url( 'assets/dist/engine.bundle.js' ),
			array(),
			Plugin::instance()->get_version(),
			true
		);

		wp_localize_script( Plugin::instance()->slug . '-editor', 'JetSaveProgressSettings',
			array(
				'meta' => SaveProgress::instance()->settings()
			)
		);
	}

	public function on_base_need_update() {
		$this->add_admin_notice( 'warning', __(
			'<b>Warning</b>: <b>JetFormBuilder Save From Progress</b> needs <b>JetEngine</b> update.',
			'jet-form-builder-save-progress'
		) );
	}

	public function on_base_need_install() {
	}
}