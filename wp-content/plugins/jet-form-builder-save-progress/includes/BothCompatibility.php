<?php


namespace Jet_FB_Save_Progress;


use Jet_Form_Builder\Form_Handler;

trait BothCompatibility {

	abstract protected function scripts_after_end( $form_id = 0 );

	abstract protected function handle_success_submit( $props );

	public function enqueue_after_end() {
		add_filter( 'jet-form-builder/after-end-form', function ( $content, $form ) {
			$this->scripts_after_end( $form->form_id );

			return $content;
		}, 10, 2 );

		add_action( 'jet-engine/forms/booking/after-end-form', function ( $builder ) {
			$this->scripts_after_end( $builder->form_id );
		} );
	}

	public function after_submit_form() {
		add_action( 'jet-form-builder/form-handler/after-send', function ( Form_Handler $handler, $is_success ) {
			call_user_func( array( $this, 'handle_success_submit' ), array(
				$handler,
				$is_success,
				$handler->form_id
			) );
		}, 10, 2 );
		add_action( 'jet-engine/forms/handler/after-send', function ( \Jet_Engine_Booking_Forms_Handler $handler, $is_success ) {
			call_user_func( array( $this, 'handle_success_submit' ), array(
				$handler,
				$is_success,
				$handler->form
			) );
		}, 10, 2 );
	}


}