<?php


namespace JFB\ScheduleForms\JetFormBuilder;


use Jet_Form_Builder\Exceptions\Action_Exception;
use JFB\ScheduleForms\ScheduleForm;
use JFB\ScheduleForms\Vendor\JFBCore\JetFormBuilder\PreventFormSubmit as BasePreventFormSubmit;

class PreventFormSubmit extends BasePreventFormSubmit {

	private $schedule;

	public function __construct( ScheduleForm $schedule ) {
		if ( ! function_exists( 'jet_form_builder' ) ) {
			return;
		}

		$this->schedule = $schedule;

		add_action(
			'jet-form-builder/request',
			array( $this, 'prevent_submit_form' ),
			- 1
		);
	}

	/**
	 * @return void
	 * @throws Action_Exception
	 */
	public function prevent_submit_form() {
		$this->schedule->get_query()->set_form_id( jet_fb_handler()->form_id );
		$this->schedule->get_query()->fetch();

		if ( ! $this->schedule->get_schedule_type() ) {
			return;
		}

		throw new Action_Exception( 'failed' );
	}

	public function prevent_process_ajax_form( $handler ) {
	}

	public function prevent_process_reload_form( $handler ) {
	}
}