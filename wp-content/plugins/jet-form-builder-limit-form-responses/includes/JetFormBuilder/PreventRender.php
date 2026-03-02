<?php


namespace JFB\LimitResponses\JetFormBuilder;

use JFB\LimitResponses\LimitResponses;
use JFB\LimitResponses\PreventRenderTrait;
use JFB\LimitResponses\Vendor\JFBCore\JetFormBuilder\PreventFormRender;

class PreventRender extends PreventFormRender {

	use PreventRenderTrait;

	public function __construct( LimitResponses $limit_responses ) {
		$this->set_limit_manager( $limit_responses );

		add_filter(
			'jet-form-builder/prevent-render-form',
			array( $this, 'prevent_render_form' ),
			100,
			2
		);
	}

}