<?php


namespace JFB\LimitResponses\JetFormBuilder;

use Jet_Form_Builder\Actions\Events\Base_Event;
use Jet_Form_Builder\Actions\Events\Default_Process\Default_Process_Event;
use Jet_Form_Builder\Exceptions\Action_Exception;
use JFB\LimitResponses\Exceptions\LimitException;
use JFB\LimitResponses\LimitResponses;

class PreventSubmit {

	private $limit;

	public function __construct( LimitResponses $limit ) {
		if ( ! function_exists( 'jet_form_builder' ) ) {
			return;
		}

		$this->limit = $limit;

		add_action(
			'jet-form-builder/request',
			array( $this, 'prevent_submit_form' )
		);
		add_action(
			'jet-form-builder/after-trigger-event',
			array( $this, 'increment_counter' ),
			20
		);
	}

	/**
	 * @return void
	 * @throws Action_Exception
	 */
	public function prevent_submit_form() {
		$form_id = absint( jet_fb_handler()->form_id );

		$this->get_limit()->get_query()->set_form_id( $form_id );
		$this->get_limit()->get_query()->fetch();

		$restriction = $this->get_limit()->get_restriction();

		try {
			// general restriction
			$this->get_limit()->is_reached_general_limit();

			// user restriction
			$restriction->before_run();
			$this->get_limit()->is_reached_limit();

		} catch ( LimitException $exception ) {
			throw new Action_Exception(
				$this->get_limit()->get_query()->get_message( LimitResponses::ERROR_MESSAGE )
			);
		}
	}


	public function increment_counter( Base_Event $event ) {
		if ( ! ( $event instanceof Default_Process_Event ) ) {
			return;
		}
		$this->get_limit()->try_to_increment_general();
		$this->get_limit()->try_to_increment();
	}

	/**
	 * @return LimitResponses
	 */
	public function get_limit(): LimitResponses {
		return $this->limit;
	}


}