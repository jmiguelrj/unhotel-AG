<?php


namespace JFB\LimitResponses;


use JFB\LimitResponses\Vendor\JFBCore\AttributesTrait;

trait PreventRenderTrait {

	use AttributesTrait;
	use PreventFormTrait;

	public function run_increment() {
		return false;
	}

	public function render_form( $form_id, $attrs, $prev_content ) {
		return $prev_content ? $prev_content : $this->send_response_on_reached_limit( $form_id );
	}

	public function get_message_type_on_general_limit() {
		return LimitResponses::CLOSED_MESSAGE;
	}

	public function get_message_type_on_restrict_limit() {
		return LimitResponses::RESTRICT_MESSAGE;
	}

	public function send_response_or_process( CachedResponse $response, $handler ) {
		return $response->is_reached() ? $this->render_limit_message( $response ) : false;
	}

	public function render_limit_message( CachedResponse $response ) {
		$this->add_attribute( 'class', 'jet-form-limit-message' );
		$this->add_attribute( 'class', $response->get_type() );
		$content = do_shortcode( $response->get_message() );

		ob_start();
		include JET_FB_LIMIT_FORM_RESPONSES_PATH . 'templates/prevent-message.php';
		return ob_get_clean();
	}

}