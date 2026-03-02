<?php


namespace JFB\SelectAutocomplete\JetEngine;


use JFB\SelectAutocomplete\BaseSelectModifier;
use JFB\SelectAutocomplete\Plugin;
use JFB\SelectAutocomplete\Vendor\JFBCore\JetEngine\BaseFieldModifier;

class SelectModifier extends BaseFieldModifier {

	use BaseSelectModifier;

	public function type(): string {
		return 'select';
	}

	public function enqueueFrontendAssets() {
		wp_enqueue_script(
			Plugin::SLUG,
			JET_FB_SELECT_AUTOCOMPLETE_URL . 'assets/js/engine.frontend.js',
			array(),
			JET_FB_SELECT_AUTOCOMPLETE_VERSION,
			true
		);
	}

	public function editorAssets() {
		wp_enqueue_script(
			Plugin::SLUG,
			JET_FB_SELECT_AUTOCOMPLETE_URL . 'assets/js/engine.editor.js',
			array(),
			JET_FB_SELECT_AUTOCOMPLETE_VERSION,
			true
		);
	}

	public function on_base_need_update() {
		$this->add_admin_notice( 'warning', __(
			'<b>Warning</b>: <b>JetFormBuilder Select Autocomplete</b> needs <b>JetEngine</b> update.',
			'jet-form-builder-select-autocomplete'
		) );
	}

	public function on_base_need_install() {
	}


}