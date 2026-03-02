<?php


namespace JFB\LimitResponses;

use JFB\LimitResponses\Exceptions\LimitException;

trait PreventFormTrait {

	/**
	 * @var LimitResponses
	 */
	private $limit_manager;

	/**
	 * @var array<CachedResponse>
	 */
	private $computed_responses = array();

	public function run_increment() {
		return true;
	}

	abstract public function get_message_type_on_general_limit();

	abstract public function get_message_type_on_restrict_limit();

	abstract public function send_response_or_process( CachedResponse $response, $handler );

	protected function send_response_on_reached_limit( $form_id, $handler = null ) {
		$form_id = absint( $form_id );

		if ( isset( $this->computed_responses[ $form_id ] ) ) {
			return $this->send_response_or_process( $this->computed_responses[ $form_id ], $handler );
		}

		$this->get_limit_manager()->get_query()->set_form_id( $form_id );
		$this->get_limit_manager()->get_query()->fetch();

		$response = new CachedResponse();

		try {
			$this->check_general_restriction( $response );
			$this->check_user_restriction( $response );

		} catch ( LimitException $exception ) {
			$response->set_reached( true );
			$response->set_message(
				$this->get_limit_manager()->get_query()->get_message( $response->get_type() )
			);
		}

		/**
		 * Run incrementing counter only if there weren't LimitExceptions
		 */
		if ( ! $response->is_reached() && $this->run_increment() ) {
			$this->get_limit_manager()->try_to_increment_general();
			$this->get_limit_manager()->try_to_increment();
		}

		$result = $this->send_response_or_process( $response, $handler );

		$this->computed_responses[ $form_id ] = $response;

		return $result;
	}

	/**
	 * @throws LimitException
	 */
	protected function check_general_restriction( CachedResponse $response ) {
		try {
			$this->get_limit_manager()->is_reached_general_limit();

		} catch ( LimitException $exception ) {
			$response->set_type( $this->get_message_type_on_general_limit() );

			throw $exception;
		}
	}

	/**
	 * @throws LimitException
	 */
	protected function check_user_restriction( CachedResponse $response ) {
		$restriction = $this->get_limit_manager()->get_restriction();

		if ( ! $restriction ) {
			return;
		}

		try {
			$restriction->before_run();

			$this->get_limit_manager()->is_reached_limit();

		} catch ( LimitException $exception ) {
			$response->set_type( $this->get_message_type_on_restrict_limit() );

			throw $exception;
		}
	}

	/**
	 * @param LimitResponses $limit_manager
	 */
	public function set_limit_manager( LimitResponses $limit_manager ) {
		$this->limit_manager = $limit_manager;
	}

	/**
	 * @return LimitResponses
	 */
	public function get_limit_manager(): LimitResponses {
		return $this->limit_manager;
	}


}
