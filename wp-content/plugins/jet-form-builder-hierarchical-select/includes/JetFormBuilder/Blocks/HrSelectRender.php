<?php


namespace Jet_FB_HR_Select\JetFormBuilder\Blocks;

use Jet_FB_HR_Select\BaseRenderHrSelect;
use Jet_Form_Builder\Blocks\Render\Base;
use JetHRSelectCore\JetFormBuilder\RenderBlock;

/**
 * @property HrSelect $block_type
 *
 * Class HrSelectRender
 * @package Jet_FB_HR_Select\JetFormBuilder\Blocks
 */
class HrSelectRender extends Base {

	use RenderBlock;
	use BaseRenderHrSelect {
		BaseRenderHrSelect::_preset_attributes_map insteadof RenderBlock;
	}

	public function get_name() {
		return 'hr-select';
	}

}