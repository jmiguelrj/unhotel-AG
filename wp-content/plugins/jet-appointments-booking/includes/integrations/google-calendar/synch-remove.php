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
 * Remove event from Google Calendar on appointment deleting.
 */
class Synch_Remove extends Synch_Create { 
    
    public function get_synch_hook() {
		return 'jet-apb/db/delete/appointments';
	}

	/**
	 * Remove event from google calendar on appointment deleting
	 *
	 * @param array $appointment
	 */
    public function on_synch( $appointment = [] ) {
		$this->remove_event( $appointment );
	}

	public function remove_event( $appointment = [] ) {

		$event_id = $appointment['meta']['gcal_event_id'];

		if ( empty( $event_id ) ) {
			return;
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

		$api_client->remove_event( $calendar_id, $event_id );

	}
}
