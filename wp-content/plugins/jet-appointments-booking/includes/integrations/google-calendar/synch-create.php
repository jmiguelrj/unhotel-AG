<?php
namespace JET_APB\Integrations\Google_Calendar;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use JET_APB\Integrations\Manager as Integrations_Manager;
use JET_APB\Plugin;
use JET_APB\Resources\Appointment_Model;

/**
 * Synch appoinentments with Google Calendar.
 * Push events to Google Calendar on appointment creation.
 */
class Synch_Create {

	/**
	 * Hook event creation on appointment creation.
	 */
	public function __construct() {

		add_action( $this->get_synch_hook(), [ $this, 'on_synch' ] );

		add_filter( 'jet-apb/display-meta-fields', [ $this, 'register_meta' ] );

	}

	public function get_synch_hook() {
		return 'jet-apb/db/create/appointments';
	}

	/**
	 * Register meta fields for appointments details popup.
	 *
	 * @param array $meta
	 * @return array
	 */
	public function register_meta( $meta ) {

		$meta['gcal_event_id'] = [
			'label' => __( 'Google Calendar Event ID', 'jet-apb' ),
			'cb'    => false,
		];

		$meta['gcal_event_link'] = [
			'label' => __( 'Google Calendar Event Link', 'jet-apb' ),
			'cb'    => function( $value, $meta_key ) {
				return make_clickable( $value );
			}
		];

		$meta['gcal_event_meet_link'] = [
			'label' => __( 'Google Meet Link', 'jet-apb' ),
			'cb'    => function( $value, $meta_key ) {
				return make_clickable( $value );
			}
		];

		return $meta;
	}

	/**
	 * Get Google_Calendar\Integration instance from Integrations Manager.
	 *
	 * @return JET_APB\Integrations\Google_Calendar\Integration
	 */
	public function get_integration() {
		return Integrations_Manager::instance()->get_integrations( 'google-calendar' );
	}

	/**
	 * Get appointment context.
	 *
	 * Context defines what GCal connection we need to use.
	 * Contex maybe global, provider or service specific.
	 *
	 * Priority of contexts:
	 *
	 * 1. Provider - if specific GCal connection is established for the provider - it will be used.
	 * 2. Service - if specific GCal connection is established for the service - it will be used.
	 * 3. Global - if provider & service has no connections - will be used a global one.
	 *
	 * @param array $appointment
	 * @return array|boolean
	 */
	public function get_appintment_context( $appointment = [] ) {

		if ( empty( $appointment ) ) {
			return false;
		}

		$provider_id = ! empty( $appointment['provider'] ) ? $appointment['provider'] : false;
		$service_id  = ! empty( $appointment['service'] ) ? $appointment['service'] : false;

		if ( ! $provider_id && ! $service_id ) {
			return false;
		}

		if ( $provider_id && $this->is_post_has_gcal_connection( $provider_id ) ) {
			return [
				'type'   => 'post',
				'object' => $provider_id,
			];
		}

		if ( $service_id && $this->is_post_has_gcal_connection( $service_id ) ) {
			return [
				'type'   => 'post',
				'object' => $service_id,
			];
		}

		$integration = $this->get_integration();

		$is_globally_connected = $integration->google_calendar_module->is_connected( 'global' );

		if ( $is_globally_connected ) {
			return [
				'type'   => 'global',
				'object' => false,
			];
		}

		return false;
	}

	/**
	 * Check if given post has Google Galedar connection by post ID.
	 *
	 * Flow:
	 * 1. Get g-cal realted post meta using Calendar_Meta_Box::get_meta()
	 * 2. Check if post meta has 'use_local_connection' set to true.
	 *
	 * Please note! This function doen't check if specific Google account is really connected,\
	 * this check should be reflected in UI to avoid users from activating use_local_connection,
	 * but not having any connection.
	 *
	 * @param int $post_id Post ID to get meta for.
	 * @return boolean
	 */
	public function is_post_has_gcal_connection( $post_id ) {

		$meta = Calendar_Meta_Box::get_meta( $post_id );

		if ( ! $meta ) {
			return false;
		}

		return ! empty( $meta['use_local_connection'] ) && $meta['use_local_connection'] ? true : false;
	}

	/**
	 * Gets required calenad ID to put event into.
	 *
	 * Flow:
	 * 1. Check if context is not global, if yes - uses calendar settings from the context.
	 * 2. If context is global - checks, maybe in provider or service used specific calendar.
	 *    To do this - gets provider/service meta from Calendar_Meta_Box::get_meta() and watch for
	 *    'use_local_calendar' & 'calendar_id' key.
	 * 3. If no specific calendar is set - uses global calendar ID.
	 *
	 * @param array $appointment
	 * @param array $context
	 * @return void
	 */
	public function get_calendar_id_for_context( $appointment = [], $context = [] ) {

		if ( empty( $context ) ) {
			return false;
		}

		$integration = $this->get_integration();

		if ( ! $integration ) {
			return false;
		}

		if ( 'global' !== $context['type'] ) {

			$meta = Calendar_Meta_Box::get_meta( $context['object'] );

			if ( ! $meta || empty( $meta['calendar_id'] ) ) {
				return false;
			} else {
				return $meta['calendar_id'];
			}
		}

		$provider_id = ! empty( $appointment['provider'] ) ? $appointment['provider'] : false;
		$service_id  = ! empty( $appointment['service'] ) ? $appointment['service'] : false;

		if ( $provider_id  ) {

			$meta = Calendar_Meta_Box::get_meta( $provider_id );

			if ( ! empty( $meta['use_local_calendar'] ) && ! empty( $meta['calendar_id'] ) ) {
				return $meta['calendar_id'];
			}
		}

		if ( $service_id ) {

			$meta = Calendar_Meta_Box::get_meta( $service_id );

			if ( ! empty( $meta['use_local_calendar'] ) && ! empty( $meta['calendar_id'] ) ) {
				return $meta['calendar_id'];
			}
		}

		return $integration->get( 'calendar_id' );
	}

	/**
	 * Add event to google calendar on appointment creation
	 *
	 * @param array $appointment
	 * @return void
	 */
	public function on_synch( $appointment = [] ) {
		$this->create_event( $appointment );
	}

	public function create_event( $appointment = [] ) {

		$integration = $this->get_integration();

		if ( ! $integration ) {
			return;
		}

		if ( in_array( $appointment['status'], Plugin::instance()->statuses->invalid_statuses() ) ) {
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

		$create_meet = $integration->data['create_meet'];

		$event = $api_client->create_event( $calendar_id, $event_data, $create_meet );

		if ( ! empty( $event['id'] ) ) {
			Plugin::instance()->db->appointments_meta->set_meta(
				$appointment['ID'],
				'gcal_event_id',
				$event['id']
			);

			Plugin::instance()->db->appointments_meta->set_meta(
				$appointment['ID'],
				'gcal_calendar_id',
				$calendar_id
			);
		}

		if ( ! empty( $event['htmlLink'] ) ) {
			Plugin::instance()->db->appointments_meta->set_meta(
				$appointment['ID'],
				'gcal_event_link',
				$event['htmlLink']
			);
		}

		if ( ! empty( $event['conferenceData'] ) ) {
			foreach ( $event['conferenceData']['entryPoints'] as $entry ) {
				if ( 'video' === $entry['entryPointType'] ) {
					Plugin::instance()->db->appointments_meta->set_meta(
						$appointment['ID'],
						'gcal_event_meet_link',
						$entry['uri']
					);
				}
			}
		}

		if ( ! empty( $event['error'] ) ) {
			Plugin::instance()->db->appointments_meta->set_meta(
				$appointment['ID'],
				'gcal_event_error_message',
				$event['error']['message']
			);

		}
	}

}
