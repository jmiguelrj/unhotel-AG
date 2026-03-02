<?php


namespace Jet_FB_HR_Select\JetFormBuilder;

use Jet_FB_HR_Select\BaseAjaxHandler;
use Jet_FB_HR_Select\JetFormBuilder\Blocks\HrSelect;
use Jet_Form_Builder\Plugin as JFBPlugin;

class AjaxHandler extends BaseAjaxHandler {

	private $block_name = '';
	private $blocks     = array();

	public function type(): string {
		return 'jfb';
	}

	public function get_field_settings( $field_name ): array {
		if ( empty( $this->blocks ) ) {
			$this->blocks = JFBPlugin::instance()->form->get_only_form_fields( $this->form_id );
		}
		$field            = JFBPlugin::instance()->form->get_field_by_name( $this->form_id, $field_name, $this->blocks );
		$this->block_name = $field['blockName'] ?? '';

		return $field['attrs'] ?? array();
	}

	/**
	 * @return HrSelect
	 */
	public function create_field_instance() {
		return JFBPlugin::instance()->blocks->get_field_by_name( $this->block_name );
	}

}
