<?php

namespace JFB\LimitResponses;

class CachedResponse {

	private $reached = false;
	/**
	 * @var string
	 */
	private $message;
	/**
	 * @var string
	 */
	private $type;

	/**
	 * @return bool
	 */
	public function is_reached(): bool {
		return $this->reached;
	}

	/**
	 * @return string
	 */
	public function get_message(): string {
		return $this->message;
	}

	/**
	 * @return string
	 */
	public function get_type(): string {
		return $this->type;
	}

	/**
	 * @param bool $reached
	 */
	public function set_reached( bool $reached ) {
		$this->reached = $reached;
	}

	/**
	 * @param string $message
	 */
	public function set_message( string $message ) {
		$this->message = $message;
	}

	/**
	 * @param string $type
	 */
	public function set_type( string $type ) {
		$this->type = $type;
	}


}