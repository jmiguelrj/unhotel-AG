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
 * Get event from calendar by cron.
 */
class Synch_Fetch extends Synch_Create {

	/**
	 * Hook scheduled event to fetch appointments from Google Calendar.
	 */
	public function __construct() {

		add_action( 'jet-apb/integrations/after-ajax-save', [ $this, 'cron_actions' ], 0, 1 );

		// On each updating local calendars meta schedula or unschedule fetching events.
		add_action( 'jet-apb/google-calendar/update-meta', [ $this, 'local_calendars_synch' ], 0, 2 );

		add_action( 'jet-apb/google-calendar-fetch-global', [ $this, 'fetch_events' ], 0, 2 );
		add_action( 'jet-apb/google-calendar-fetch-local', [ $this, 'fetch_events' ], 0, 2 );

	}

	/**
	 * Schedule/unschedule fetching events from the local calendar settings.
	 *
	 * Flow:
	 * 1. Check state of 'use_local_connection' and 'use_local_calendar' meta.
	 * 2. If one of them is set to true - schedule fetching events.
	 * 3. If both is set to false - unschedule fetching events.
	 *
	 * @param int $post_id
	 * @param array $meta
	 */
	public function local_calendars_synch( $post_id, $meta, $action = 'create', $settings = [] ) {

		if ( ! empty( $settings['google-calendar']['data']['synch_interval'] ) ) {
			$synch_interval = $settings['google-calendar']['data']['synch_interval'];
		} else {
			$synch_interval = $this->get_integration()->data['synch_interval'];
		}

		$use_local_cn = ! empty( $meta['use_local_connection'] ) ? $meta['use_local_connection'] : false;
		$use_local_cn = filter_var( $use_local_cn, FILTER_VALIDATE_BOOLEAN );

		$use_local_cal = ! empty( $meta['use_local_calendar'] ) ? $meta['use_local_calendar'] : false;
		$use_local_cal = filter_var( $use_local_cal, FILTER_VALIDATE_BOOLEAN );

		if ( $use_local_cn || $use_local_cal ) {

			if ( $use_local_cn ) {
				$context_type = 'post';
				$clear_type   = 'global';
			} else {
				$context_type = 'global';
				$clear_type   = 'post';
			}

			if ( 'remove' === $action || 'update' === $action ) {
				wp_clear_scheduled_hook( 'jet-apb/google-calendar-fetch-local', [ $context_type, $post_id ] );
			}

			if ( 'create' === $action || 'update' === $action ) {
				if ( ! wp_next_scheduled( 'jet-apb/google-calendar-fetch-local', [ $context_type, $post_id ] ) ) {
					$this->set_cron( 'jet-apb/google-calendar-fetch-local', [ $context_type, $post_id ], $synch_interval );
				}
			} 

			/**
			 * In case when we change between use_local_connection and use_local_calendar
			 */
			wp_clear_scheduled_hook( 'jet-apb/google-calendar-fetch-local', [ $clear_type, $post_id ] );
		} else {
			wp_clear_scheduled_hook( 'jet-apb/google-calendar-fetch-local', [ 'post', $post_id ] );
			wp_clear_scheduled_hook( 'jet-apb/google-calendar-fetch-local', [ 'global', $post_id ] );
		}
	}

	public function get_internal_google_events() {

		$time       = time();
		$meta_table = Plugin::instance()->db->appointments_meta->table();
		$app_table  = Plugin::instance()->db->appointments->table();

		$query = "SELECT apm.meta_value FROM {$meta_table} AS apm INNER JOIN {$app_table} AS app ON app.ID = apm.appointment_id WHERE apm.meta_key = 'gcal_event_id' AND app.slot > {$time};";

		global $wpdb;
		$results = $wpdb->get_col( $query );

		return $results;
	}

	/**
	 * Fetch events from Google Calendar.
	 *
	 * @param string $context_type
	 * @param mixed  $object
	 * @return void
	 */
	public function fetch_events( $context_type, $object ) {

		$integration = $this->get_integration();

		if ( ! $integration ) {
			return;
		}

		$object_num = absint( $object );

		$context = [
			'type'   => $context_type,
			'object' => $object_num,
		];

		$query_args    = array();
		$provider      = 0;
		$service       = 0;
		$services_cpt  = Plugin::instance()->settings->get( 'services_cpt' );
		$providers_cpt = Plugin::instance()->settings->get( 'providers_cpt' );

		if ( 0 < $object_num ) {

			$post_type = get_post_type( $object_num );

			if ( $post_type && $services_cpt && $services_cpt === $post_type ) {
				$query_args['service'] = $object_num;
				$service = $object_num;
			} elseif ( $post_type && $providers_cpt && $providers_cpt === $post_type ) {
				$query_args['provider'] = $object_num;
				$provider  = $object_num;
			} else {
				$object_meta = Calendar_Meta_Box::get_meta( $object_num );
				$this->local_calendars_synch( $object_num, $object_meta, 'remove' );
				return;
			}
		}

		$appointment = [
			'service'  => $service,
			'provider' => $provider,
		];

		$api_client  = $integration->google_calendar_module->get_api_client( $context );
		$calendar_id = $this->get_calendar_id_for_context( $appointment, $context );

		if ( empty( $api_client ) || empty( $calendar_id ) ) {
			return;
		}

		$events_data = $api_client->get_events( $calendar_id, 500 );
		$internal_google_events = $this->get_internal_google_events();
		$prev_synched_events = Plugin::instance()->db->appointments_external->get_future_events_ids();
		$events = ! empty( $events_data['items'] ) ? $events_data['items'] : [];
		$ids = [];

		foreach ( $events as $event ) {

			$ids[] = $event['id'];

			$event = (array) $event;

			$action = 'new_appointment';

			if ( in_array( $event['id'], $internal_google_events ) ) {
				continue;
			}

			$start_timestamp = \Crocoblock\Google_Calendar_Synch\Helper::event_date_to_timestamp(
				$event['start']
			);

			$end_timestamp = \Crocoblock\Google_Calendar_Synch\Helper::event_date_to_timestamp(
				$event['end']
			);

			if ( in_array( $event['id'], $prev_synched_events ) ) {

				$current_synched_event = Plugin::instance()->db->appointments_external->external_query( ['external_id' => $event['id']] );

				if ( $start_timestamp != $current_synched_event[$event['id']]->date || $end_timestamp != $current_synched_event[$event['id']]->date_end && ( $current_synched_event[$event['id']]->service != $object_num || $current_synched_event[$event['id']]->provider != $object_num ) ) {

					$where = ['external_id' => $event['id']];

					$action = 'update';
		
				} else {
					continue;
				}
				
			}

			if ( $start_timestamp < time() ) {
				continue;
			}

			if ( $start_timestamp >= $end_timestamp ) {
				$end_timestamp += 3600;
			}

			$start_date_ts = strtotime( date( 'Y-m-d', $start_timestamp ) );
			$end_date_ts   = strtotime( date( 'Y-m-d', $end_timestamp ) );

			$event_data = [
				'service'     => $service,
				'provider'    => $provider,
				'date'        => $start_date_ts,
				'date_end'    => $end_date_ts,
				'slot'        => $start_timestamp,
				'slot_end'    => $end_timestamp,
				'external_id' => $event['id'],
				'calendar_id' => $calendar_id,
			];

			if ( 'update' === $action ) {

				Plugin::instance()->db->appointments_external->update( $event_data, $where );

				$excluded_dates = Plugin::instance()->db->excluded_dates->query( $query_args );

				foreach ( $excluded_dates as $excluded_date ) {
					Plugin::instance()->db->maybe_remove_excluded_app( [
						'provider' => ! empty( $excluded_date['provider'] ) ? $excluded_date['provider'] : 0,
						'service' => $excluded_date['service'],
						'date' => $excluded_date['date'],
					] );
				}

			} else {
				Plugin::instance()->db->appointments_external->new_appointment( $event_data );
			}

		}

		$current_calendar_external = Plugin::instance()->db->appointments_external->external_query( ['calendar_id' => $calendar_id] );

		foreach( $current_calendar_external as $external ) {
			if ( ! in_array( $external->external_id, $ids ) ) {
				Plugin::instance()->db->appointments_external->delete( ['external_id' => $external->external_id] );
				Plugin::instance()->db->maybe_remove_excluded_app( ['service' => $external->service,
						'provider' => ! empty( $external->provider ) ? $external->provider : 0,
						'date' => $external->date,
					] );
			}
		}

	}

	public function cron_actions( $settings ) {

		$is_active_sync = $settings['google-calendar']['data']['sync_events_from_calendar'];
		$sync_switched  = $this->get_integration()->data['sync_events_from_calendar'] !== $settings['google-calendar']['data']['sync_events_from_calendar'] ? true : false;
		$was_update     = $this->get_integration()->data['synch_interval'] !== $settings['google-calendar']['data']['synch_interval'] ? true : false;

		if ( ! $sync_switched && ! $was_update ) {
			return;
		}

		$posts = Integrations_Manager::instance()->get_synch_posts();

		if ( ! empty( $posts ) ) {
			foreach ( $posts as $id => $name ) {
				if ( $sync_switched ) {
					if ( true === $is_active_sync ) {
						$this->local_calendars_synch( $id, $name, 'create', $settings, );
					} else {
						$this->local_calendars_synch( $id, $name, 'remove' );
					}
				} elseif ( $was_update ) {
					$this->local_calendars_synch( $id, $name, 'update', $settings,);
				}
			}
		}

		if ( $sync_switched ) {
			if ( true === $is_active_sync ) {
				$this->set_cron( 'jet-apb/google-calendar-fetch-global', [ 'global', false ], $settings['google-calendar']['data']['synch_interval'] );
			} else {
				wp_clear_scheduled_hook( 'jet-apb/google-calendar-fetch-global', [ 'global', false ] );
			}
		} elseif ( $was_update ) {
			wp_clear_scheduled_hook( 'jet-apb/google-calendar-fetch-global', [ 'global', false ] );
			$this->set_cron( 'jet-apb/google-calendar-fetch-global', [ 'global', false ], $settings['google-calendar']['data']['synch_interval'] );
		}

	}

	public function set_cron( $hook, $args, $interval ) {
		if ( ! wp_next_scheduled( $hook, $args ) ) {
			wp_schedule_event(
				time(),
				$interval,
				$hook,
				$args,
			);
		}
	}
	
}
