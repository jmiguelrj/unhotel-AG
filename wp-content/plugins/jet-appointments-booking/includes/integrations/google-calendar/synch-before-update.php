<?php
namespace JET_APB\Integrations\Google_Calendar;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use JET_APB\Integrations\Manager as Integrations_Manager;
use JET_APB\Plugin;

/**
 * Synch appoinentments with Google Calendar.
 * Check for calendar change in apppointment, for reset.
 */
class Synch_Before_Update extends Synch_Remove { 
   
    public function get_synch_hook() {
		return 'jet-apb/db/before-update/appointments';
	}

	/**
	 * Remove and insert the event if the calendar after the update differs from the current one.
	 *
	 * @param array $appointment
	 */
	public function on_synch( $appointment = [] ) {

		$initial_appointment = ! empty( $appointment['initial'] ) ? $appointment['initial'] : false;
		$updated_appointment = ! empty( $appointment['updated'] ) ? $appointment['updated'] : false;

		if ( $initial_appointment ) {
			$initial_appointment_array = $initial_appointment->to_array();
		}
		if ( $updated_appointment ) {
			$updated_appointment_array = $updated_appointment->to_array();
		}

		$integration = $this->get_integration();

		if ( ! $integration ) {
			return;
		}

		$context_initial     = $this->get_appintment_context( $initial_appointment_array );
		$initial_calendar_id = $this->get_calendar_id_for_context( $initial_appointment_array, $context_initial );
		$api_client_initial  = $integration->google_calendar_module->get_api_client( $context_initial );

		$context_updated     = $this->get_appintment_context( $updated_appointment_array );
		$updated_calendar_id = $this->get_calendar_id_for_context( $updated_appointment_array, $context_updated );
		$api_client_updated  = $integration->google_calendar_module->get_api_client( $context_updated );

		if ( $initial_calendar_id !== $updated_calendar_id ) {

			if ( ! empty( $api_client_initial ) && ! empty( $initial_calendar_id ) ) {
				$initial_event_id = $initial_appointment_array['meta']['gcal_event_id'];
				$api_client_initial->remove_event( $initial_calendar_id, $initial_event_id );
			}

			if ( ! in_array( $updated_appointment_array['status'], Plugin::instance()->statuses->invalid_statuses() ) && ! empty( $api_client_updated ) && ! empty( $updated_calendar_id ) ) {

				$service_id  = $updated_appointment_array['service'];
				$provider_id = $updated_appointment_array['provider'];
		
				if ( ! $service_id ) {
					return;
				}
	
				$summary = get_the_title( $service_id );
		
				if ( $provider_id ) {
					$summary .= ' - ' . get_the_title( $provider_id );
				}
		
				$start = \Crocoblock\Google_Calendar_Synch\Helper::timestamp_to_event_date(
					$updated_appointment_array['slot']
				);
		
				$end = \Crocoblock\Google_Calendar_Synch\Helper::timestamp_to_event_date(
					$updated_appointment_array['slot_end']
				);
		
				$event_data = [
					'summary'     => $summary,
					'description' => '',
					'start'       => $start,
					'end'         => $end,
					'attendees'   => [
						[ 'email' => $updated_appointment_array['user_email'] ],
					],
				];
		
				$create_meet = $integration->data['create_meet'];
		
				$event = $api_client_updated->create_event( $updated_calendar_id, $event_data, $create_meet );

				if ( ! empty( $event['id'] ) ) {
					$updated_appointment->set_meta( 'gcal_event_id', $event['id'] );
					$updated_appointment->set_meta( 'gcal_calendar_id', $updated_calendar_id );
					
				}
		
				if ( ! empty( $event['htmlLink'] ) ) {
					$updated_appointment->set_meta( 'gcal_event_link', $event['htmlLink'] );
				}
		
				if ( ! empty( $event['conferenceData'] ) ) {
					foreach ( $event['conferenceData']['entryPoints'] as $entry ) {
						if ( 'video' === $entry['entryPointType'] ) {
							$updated_appointment->set_meta( 'gcal_event_meet_link', $entry['uri'] );
						}
					}
				}

				if ( ! empty( $event['error'] ) ) {
					$updated_appointment->set_meta( 'gcal_event_error_message', $event['error']['message'] );
				}

				$updated_appointment->save();

			}

		}

	}
}
