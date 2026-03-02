<?php


namespace JFB\ScheduleForms\JetEngine;


use JFB\ScheduleForms\ScheduleForm;
use JFB\ScheduleForms\Vendor\JFBCore\JetEngine\PreventFormRender as BasePreventFormRender;

class PreventFormRender extends BasePreventFormRender {

	private $schedule;

	public function __construct( ScheduleForm $schedule ) {
		$this->schedule = $schedule;

		add_filter(
			'jet-engine/forms/pre-render-form',
			array( $this, 'prevent_render_form' ),
			100,
			2
		);
	}

	public function render_form( $form_id, $attrs, $prev_content ) {
		if ( $prev_content ) {
			return $prev_content;
		}
		$this->schedule->get_query()->set_form_id( $form_id );
		$this->schedule->get_query()->fetch();

		return $this->schedule->get_content();
	}

}