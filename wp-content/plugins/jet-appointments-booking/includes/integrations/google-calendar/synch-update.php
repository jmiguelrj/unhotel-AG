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
 * Update event in Google Calendar on appointment update.
 */
class Synch_Update extends Synch_Remove { 
   
    public function get_synch_hook() {
		return 'jet-apb/db/update/appointments';
	}

	/**
	 * Update event in google calendar on appointment update
	 *
	 * @param array $appointment
	 */
	public function on_synch( $appointment = [] ) {

		$event_id = Plugin::instance()->db->appointments_meta->get_meta( $appointment['ID'], 'gcal_event_id' );

		if ( empty( $event_id ) ) {
			return $this->create_event( $appointment );
		}

		$integration = $this->get_integration();

		if ( ! $integration ) {
			return;
		}

		$context = $this->get_appintment_context( $appointment );

		if ( ! $context ) {
			return;
		}

		$api_client  = $integration->google_calendar_module->get_api_client( $context );
		$calendar_id = $this->get_calendar_id_for_context( $appointment, $context );

		if ( empty( $api_client ) || empty( $calendar_id ) ) {
			return;
		}

		if ( in_array( $appointment['status'], Plugin::instance()->statuses->invalid_statuses() ) ) {
			return $api_client->remove_event( $calendar_id, $event_id );
		}

		$appointment_calendar = Plugin::instance()->db->appointments_meta->get_meta( $appointment['ID'], 'gcal_calendar_id' );

		if ( $appointment_calendar !== $calendar_id ) {
			return;
		}

		$service_id  = $appointment['service'];
		$provider_id = $appointment['provider'];

		if ( ! $service_id ) {
			return;
		}

		$summary = get_the_title( $service_id );

		if ( $provider_id ) {
			$summary .= ' - ' . get_the_title( $provider_id );
		}

		$start = \Crocoblock\Google_Calendar_Synch\Helper::timestamp_to_event_date(
			$appointment['slot']
		);

		$end = \Crocoblock\Google_Calendar_Synch\Helper::timestamp_to_event_date(
			$appointment['slot_end']
		);

		$event_data = [
			'summary'     => $summary,
			'description' => '',
			'start'       => $start,
			'end'         => $end,
			'attendees'   => [
				[ 'email' => $appointment['user_email'] ],
			],
		];

		$api_client->update_event( $calendar_id, $event_id, $event_data );
	}
}
