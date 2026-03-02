<?php


namespace JFB\LimitResponses\JetEngine;

use JFB\LimitResponses\LimitResponses;
use JFB\LimitResponses\PreventRenderTrait;
use JFB\LimitResponses\Vendor\JFBCore\JetEngine\PreventFormRender;

class PreventRender extends PreventFormRender {

	use PreventRenderTrait;

	public function __construct( LimitResponses $limit_responses ) {
		$this->set_limit_manager( $limit_responses );

		add_filter(
			'jet-engine/forms/pre-render-form',
			array( $this, 'prevent_render_form' ),
			100,
			2
		);
	}

}