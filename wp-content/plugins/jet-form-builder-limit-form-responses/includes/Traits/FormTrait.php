<?php


namespace JFB\LimitResponses\Traits;

trait FormTrait {

	private $form_id = 0;

	public function set_form_id( int $form_id ) {
		$this->form_id = $form_id;
	}

	public function get_form_id(): int {
		return $this->form_id;
	}

}
