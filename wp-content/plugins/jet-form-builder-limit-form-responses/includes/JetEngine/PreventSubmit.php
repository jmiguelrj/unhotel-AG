<?php


namespace JFB\LimitResponses\JetEngine;

use JFB\LimitResponses\CachedResponse;
use JFB\LimitResponses\LimitResponses;
use JFB\LimitResponses\PreventSubmitTrait;
use JFB\LimitResponses\Vendor\JFBCore\JetEngine\PreventFormSubmit;

class PreventSubmit extends PreventFormSubmit {

	use PreventSubmitTrait;

	public function __construct( LimitResponses $limit_responses ) {
		$this->set_limit_manager( $limit_responses );

		add_action( 'init', array( $this, 'init_module' ) );
	}

	public function init_module() {
		if ( ! $this->can_init() ) {
			return;
		}
		$this->manage_hooks();
	}

	/**
	 * @inheritDoc
	 */
	public function prevent_process_ajax_form( $handler ) {
		$this->send_response_on_reached_limit( $handler->form, $handler );
	}

	/**
	 * @inheritDoc
	 */
	public function prevent_process_reload_form( $handler ) {
		$this->send_response_on_reached_limit( $handler->form, $handler );
	}


	public function send_response_or_process( CachedResponse $response, $handler ) {
		if ( ! $response->is_reached() ) {
			return;
		}

		$handler->redirect( array(
			'status' => $response->get_message()
		) );
	}
}