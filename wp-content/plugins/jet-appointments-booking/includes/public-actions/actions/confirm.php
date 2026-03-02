<?php
namespace JET_APB\Public_Actions\Actions;

use JET_APB\Plugin;
use JET_APB\Public_Actions\Tokens;

class Confirm extends Base {

	public function action_id() {
		return 'confirm';
	}

	public function action_css() {
		echo '.jet-apb-action-result.action-confirm { color: var( --wp--preset--color--vivid-green-cyan ); }';
	}

	public function get_action_template() {
		
		$result_type = Plugin::instance()->settings->get( $this->action_id() . '_action_template_type' );

		if ( 'custom_template' !== $result_type ) {
			return false;
		}

		$template = Plugin::instance()->settings->get( $this->action_id() . '_action_template' );

		if ( $template ) {
			return $template;
		} else {
			return false;
		}

	}

	public function do_action( $appointment = [], $manager = null ) {

		if (  ! $this->is_valid_slot( $appointment, $this->action_id() ) ) {
			return false;
		}

		$message = Plugin::instance()->settings->get( 'confirm_action_message' );

		if ( Plugin::instance()->settings->get( 'same_group_token' )  ) {

			foreach ( $this->get_same_token_appointments( $appointment['meta']['_action_token'] ) as $item ) {
				
				$same_appointment = Plugin::instance()->db->get_appointment_by( 'ID', $item['appointment_id'] );

				if( $this->is_valid_slot( $same_appointment, $this->action_id() ) ) {
					$this->change_appointment_status( $item['appointment_id'], 'completed' );
				}

			}

			if ( ! $message ) {
				$message = __( 'Appointments Group confirmed!', 'jet-appointments-booking' );
			}

		} else {

			$this->change_appointment_status( $appointment['ID'], 'completed' );

			if ( ! $message ) {
				$message = __( 'Appointment confirmed!', 'jet-appointments-booking' );
			}

		}

		$manager->set_message( $message );

		return true;

	}

	public function register_macros( $macros, $manager ) {
		
		$macros['confirm_url'] = [
			'label' => __( 'Confirm Appointment URL', 'jet-appointments-booking' ),
			'cb'    => function( $result = null, $args_str = null ) use ( $manager ) {
				$appointment = $manager->get_macros_object();
				return isset( $appointment['meta']['_confirm_url'] ) ? $appointment['meta']['_confirm_url'] : '';
			}
		];

		return $macros;
	}

	public function get_same_token_appointments( $token ) {

		return Plugin::instance()->db->appointments_meta->query( [
			'meta_key'   => Tokens::$token_key,
			'meta_value' => $token,
		] );

	}

	public function change_appointment_status ( $item_id, $status ) {
		Plugin::instance()->db->appointments->update( 
			array( 'status' => $status ), 
			array( 'ID' => $item_id ) 
		);
	}

	public function get_url( $appointment, $action ) {
		
		$tokens = new Tokens();

		return $tokens->token_url( add_query_arg( [
			'_jet_apb_action' => $this->action_id(),
		], home_url( '/' ) ), $appointment );

	}

	public function show_url( $value, $key ) {
		return make_clickable( $value );
	}

	public function action_meta() {
		return [
			'_confirm_url' => [
				'label'   => __( 'Confirm URL', 'jet-appointments-booking' ),
				'get_cb'  => [ $this, 'get_url' ],
				'show_cb' => [ $this, 'show_url' ],
			]
		];
	}

}
