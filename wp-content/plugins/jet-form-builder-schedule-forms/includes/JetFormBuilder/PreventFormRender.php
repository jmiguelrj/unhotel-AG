<?php


namespace JFB\ScheduleForms\JetFormBuilder;


use JFB\ScheduleForms\ScheduleForm;
use JFB\ScheduleForms\Vendor\JFBCore\JetFormBuilder\PreventFormRender as BasePreventFormRender;

class PreventFormRender extends BasePreventFormRender {

	private $schedule;

	public function __construct( ScheduleForm $schedule ) {
		$this->schedule = $schedule;

		add_filter(
			'jet-form-builder/prevent-render-form',
			array( $this, 'prevent_render_form' ),
			100,
			2
		);
	}

	public function render_form( $form_id, $attrs, $prev_content = false ) {
		if ( $prev_content ) {
			return $prev_content;
		}
		$this->schedule->get_query()->set_form_id( $form_id );
		$this->schedule->get_query()->fetch();

		return $this->schedule->get_content();
	}

}