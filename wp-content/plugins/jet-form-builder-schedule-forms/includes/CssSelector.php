<?php


namespace JFB\ScheduleForms;


trait CssSelector {

	public function selector( $selector = '' ) {
		return "{{WRAPPER}} .jet-form-schedule-message$selector";
	}

	public function uniq_id( $suffix ) {
		return "jet_fb_schedule_forms__$suffix";
	}

}