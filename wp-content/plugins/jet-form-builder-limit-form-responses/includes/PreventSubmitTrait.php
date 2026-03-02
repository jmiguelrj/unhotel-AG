<?php


namespace JFB\LimitResponses;


trait PreventSubmitTrait {

	use PreventFormTrait;

	public function get_message_type_on_general_limit() {
		return LimitResponses::ERROR_MESSAGE;
	}

	public function get_message_type_on_restrict_limit() {
		return LimitResponses::ERROR_MESSAGE;
	}

}