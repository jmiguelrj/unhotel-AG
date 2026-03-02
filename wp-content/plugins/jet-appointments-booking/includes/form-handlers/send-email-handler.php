<?php
namespace JET_APB\Form_Handlers;

use JET_APB\Plugin;

trait Send_Email_Handler {


	/**
	 * The function processes macros before sending an email.
	 *
	 * @param  [type] $message_content [description]
	 * @param  [type] $class_instant   [description]
	 * @return [type]                  [description]
	 */
	public function parse_message_content( $message_content ) {

		$appointments = Plugin::instance()->tools->get_appointments( $this->getAppointments() );

		if ( ! isset( $appointments[0] ) ) {
			return $message_content;
		}

		preg_match(
			'/\%(appointmens_list|appointments_list)\%([\s\S]*)\%(appointmens_list_end|appointments_list_end)\%/',
			$message_content,
			$appointmens_list_matches
		);

		$appointmens_list_content  = isset( $appointmens_list_matches[2] ) ? $appointmens_list_matches[2] : '';

		if ( ! empty( $appointmens_list_content ) ) {

			$appointmens_list_output_content = '';
			$appointments = Plugin::instance()->db->get_appointments_meta( $appointments );

			foreach ( $appointments as $appointment ) {
				$appointmens_list_output_content .= $this->parse_appointments_macros( $appointmens_list_content, $appointment );
			}

			if ( ! empty( $appointmens_list_output_content ) ) {
				$message_content = str_replace( $appointmens_list_content, $appointmens_list_output_content, $message_content );
			}

		}

		$result = $this->parse_appointments_macros( $message_content, $appointments[0] );

		return $result;
	}

	public function parse_appointments_macros( $message_content, $appointment ) {
		Plugin::instance()->macros->set_macros_object( $appointment );
		return Plugin::instance()->macros->do_macros( $message_content );
	}

}