<?php


namespace JFB\LimitResponses;


trait CssSelector {

	public function selector( $selector = '' ) {
		return "{{WRAPPER}} .jet-form-limit-message$selector";
	}

	public function uniq_id( $suffix ) {
		return "jet_fb_limit_form_responses__$suffix";
	}

}