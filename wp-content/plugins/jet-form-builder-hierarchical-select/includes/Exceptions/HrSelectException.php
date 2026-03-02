<?php


namespace Jet_FB_HR_Select\Exceptions;


use Throwable;

class HrSelectException extends \Exception {

	protected $value;

	public function __construct( $value ) {
		$this->value = $value;

		parent::__construct();
	}

	public function get_value() {
		return $this->value;
	}

}