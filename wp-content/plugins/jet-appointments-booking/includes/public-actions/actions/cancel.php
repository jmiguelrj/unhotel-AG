<?php
namespace JET_APB\Public_Actions\Actions;

use JET_APB\Plugin;
use JET_APB\Public_Actions\Tokens;

class Cancel extends Confirm {

	public function action_id() {
		return 'cancel';
	}

	public function action_css() {
		echo '.jet-apb-action-result.action-cancel { color: var( --wp--preset--color--vivid-red ); }';
	}

	public function do_action( $appointment = [], $manager = null ) {

		if ( ! $this->is_valid_slot( $appointment, $this->action_id() ) ) {
			return false;
		}

		$message = Plugin::instance()->settings->get( 'cancel_action_message' );

		if ( Plugin::instance()->settings->get( 'same_group_token' ) ) {
			
			foreach ( $this->get_same_token_appointments( $appointment['meta']['_action_token'] ) as $item ) {

				$same_appointment = Plugin::instance()->db->get_appointment_by( 'ID', $item['appointment_id'] );

				if( $this->is_valid_slot( $same_appointment, $this->action_id() ) ) {
					$this->cancel_appointment( $item['appointment_id'], 'cancelled' );
				}

			}

			if ( ! $message ) {
				$message = __( 'Appointments Group cancelled! If you cancelled it by mistake, you need to book this appointment again.', 'jet-appointments-booking' );
			}

		} else {

			$this->cancel_appointment( $appointment['ID'], 'cancelled' );

			if ( ! $message ) {
				$message = __( 'Appointment cancelled! If you cancelled it by mistake, you need to book this appointment again.', 'jet-appointments-booking' );
			}

		}

		$manager->set_message( $message );

		return true;

	}

	public function cancel_appointment( $item, $status ) {

		$this->change_appointment_status( $item, $status );

		Plugin::instance()->db->appointments_meta->delete( [ 
			'appointment_id' => $item, 
			'meta_key'       => Tokens::$token_key,
		] );
		
	}
	
	public function register_macros( $macros, $manager ) {
		
		$macros['cancel_url'] = [
			'label' => __( 'Cancel Appointment URL', 'jet-appointments-booking' ),
			'cb'    => function( $result = null, $args_str = null ) use ( $manager ) {
				$appointment = $manager->get_macros_object();
				return isset( $appointment['meta']['_cancel_url'] ) ? $appointment['meta']['_cancel_url'] : '';
			}
		];

		return $macros;
	}

	public function action_meta() {
		return [
			'_cancel_url' => [
				'label'   => __( 'Cancel URL', 'jet-appointments-booking' ),
				'get_cb'  => [ $this, 'get_url' ],
				'show_cb' => [ $this, 'show_url' ],
			]
		];
	}

}
