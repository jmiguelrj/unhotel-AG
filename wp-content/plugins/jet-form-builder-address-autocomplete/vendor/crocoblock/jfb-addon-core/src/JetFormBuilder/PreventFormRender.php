<?php


namespace JetAddressAutocompleteCore\JetFormBuilder;


use JetAddressAutocompleteCore\PreventRenderFormBase;

abstract class PreventFormRender {

	use PreventRenderFormBase;

	public function form_id_key() {
		return 'form_id';
	}

	public function action_name() {
		return 'jet-form-builder/prevent-render-form';
	}

}