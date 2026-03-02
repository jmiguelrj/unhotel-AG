<?php


namespace JFB\ScheduleForms\JetEngine;


use JFB\ScheduleForms\ScheduleForm;
use JFB\ScheduleForms\Vendor\JFBCore\JetEngine\PreventFormSubmit as BasePreventFormSubmit;

class PreventFormSubmit extends BasePreventFormSubmit {

	private $schedule;

	public function __construct( ScheduleForm $schedule ) {
		$this->schedule = $schedule;

		add_action( 'init', array( $this, 'init_module' ) );
	}

	public function init_module() {
		if ( ! $this->can_init() ) {
			return;
		}
		$this->manage_hooks();
	}

	public function prevent_process_ajax_form( $handler ) {
		$this->schedule->get_query()->set_form_id( $handler->form );
		$this->schedule->get_query()->fetch();

		if ( ! $this->schedule->get_schedule_type() ) {
			return;
		}

		$handler->redirect( array(
			'status' => 'failed',
		) );
	}

	public function prevent_process_reload_form( $handler ) {
		$this->schedule->get_query()->set_form_id( $handler->form );
		$this->schedule->get_query()->fetch();

		if ( ! $this->schedule->get_schedule_type() ) {
			return;
		}

		$handler->redirect( array(
			'status' => 'failed',
		) );
	}

}