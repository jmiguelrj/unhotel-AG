<?php
namespace JET_APB\DB;

use JET_APB\Plugin;
use JET_APB\Tools;
use JET_APB\Resources\Appointment_Model;
use JET_APB\Calendar;

/**
 * Database manager class
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define Base DB class
 */
class Manager {

	public $appointments;
	public $appointments_meta;
	public $excluded_dates;
	public $dates_to_exclude = array();
	public $appointments_external;

	/**
	 * Constructor for the class
	 */
	public function __construct() {

		$this->appointments      = new Appointments();
		$this->appointments_meta = new Appointments_Meta();
		$this->excluded_dates    = new Excluded_Dates();
		$this->appointments_external = new Appointments_External();

		// Adjust excluded dates state on appointments status switch from valid to invalid and vice versa
		add_action( 'jet-apb/db/update/appointments', [ $this, 'adjust_excluded_dates' ], 10, 3 );

	}

	/**
	 * Adjust excluded dates state on appointments status switch from valid to invalid and vice versa
	 *
	 * @param  [type] $new_appointment [description]
	 * @param  [type] $update_by       [description]
	 * @param  [type] $old_appointment [description]
	 * @return [type]                  [description]
	 */
	public function adjust_excluded_dates( $new_appointment, $update_by, $old_appointment ) {

		$new_status = ! empty( $new_appointment['status'] ) ? $new_appointment['status'] : false;
		$old_status = ! empty( $old_appointment['status'] ) ? $old_appointment['status'] : false;

		if ( in_array( $new_status, Plugin::instance()->statuses->invalid_statuses() )
			&& in_array( $old_status, Plugin::instance()->statuses->exclude_statuses() )
		) {
			Plugin::instance()->db->remove_appointment_date_from_excluded( $new_appointment );
		}

		if ( in_array( $old_status, Plugin::instance()->statuses->invalid_statuses() )
			&& in_array( $new_status, Plugin::instance()->statuses->exclude_statuses() )
		) {
			Plugin::instance()->db->maybe_exclude_appointment_date( $new_appointment );
		}

	}

	/**
	 * Remove date of passed appoinemtnt from excluded dates
	 *
	 * @param  [type] $appointment [description]
	 * @return [type]              [description]
	 */
	public function remove_appointment_date_from_excluded( $appointment ) {

		if ( is_integer( $appointment ) ) {
			$appointment = $this->get_appointment_by( 'ID', $appointment );
		}
		if ( is_array( $appointment ) ) {
			$appointment = new Appointment_Model( $appointment );
		}

		if ( ! $appointment ) {
			return;
		}

		$excluded_where = array();

		if ( ! empty( $appointment->get( 'date' ) ) ) {
			$excluded_where['date'] = $appointment->get( 'date' );
		}

		if ( ! empty( $appointment->get( 'service' ) ) ) {
			$excluded_where['service'] = $appointment->get( 'service' );
		}

		if ( ! empty( $appointment->get( 'provider' ) ) ) {
			$excluded_where['provider'] = $appointment->get( 'provider' );
		}

		$this->excluded_dates->delete( $excluded_where );

	}

	/**
	 * Check if date of given appointments is in schedule
	 *
	 * @param  object  $appointment [description]
	 * @return boolean              [description]
	 */
	public function is_appointment_date_allowed( $appointment = null ) {

		if ( ! $appointment ) {
			return false;
		}

		$date     = $appointment->get( 'date' );
		$service  = $appointment->get( 'service' );
		$provider = $appointment->get( 'provider' );

		if ( ! $date ) {
			return false;
		}

		$excluded_dates      = Plugin::instance()->calendar->get_off_dates( $service, $provider );
		$works_dates         = Plugin::instance()->calendar->get_works_dates( $service, $provider );
		$allowed_dates_range = Plugin::instance()->calendar->get_dates_range( $service, $provider );
		$dates_mode          = Plugin::instance()->calendar->get_working_days_mode( $service, $provider );
		$available_week_days = Plugin::instance()->calendar->get_available_week_days( $service, $provider );

		// If date not in allowed range - decline it in any case
		if ( ! empty( $allowed_dates_range ) ) {

			if ( ! empty( $allowed_dates_range['start'] ) && $date < $allowed_dates_range['start'] ) {
				return false;
			}

			if ( ! empty( $allowed_dates_range['end'] ) && $date > $allowed_dates_range['end'] ) {
				return false;
			}
		}

		// Check if this date is set separetely as work date
		$works_dates  = ( ! empty( $works_dates ) && is_array( $works_dates ) ) ? $works_dates : [];
		$is_work_date = false;

		if ( empty( $works_dates ) || empty( $works_dates[0]['start'] ) ) {
			$is_work_date = true;
		}

		foreach ( $works_dates as $works_date ) {
			if ( $works_date['start'] <= $date && $date <= $works_date['end'] ) {
				$is_work_date = true;
			}
		}

		// if date is in $excluded_dates - decline full dates, check other - if they are not in $work_dates - also declie
		$excluded_dates  = ( ! empty( $excluded_dates ) && is_array( $excluded_dates ) ) ? $excluded_dates : [];

		foreach ( $excluded_dates as $excluded_date ) {
			if ( $excluded_date['start'] <= $date
				&& $date <= $excluded_date['end']
				&& ( empty( $excluded_date['service'] )
					|| absint( $excluded_date['service'] ) === absint( $service ) )
				) {

				// Decline full date imideately
				if ( ! empty( $excluded_date['is_full'] ) ) {
					return false;
				}

				// Also ecline if this not is separetely set working date
				if ( ! $is_work_date ) {
					return false;
				}

			}
		}

		// If this date is set separetely as work date - allow it
		if ( $is_work_date ) {
			return true;
		} elseif ( 'override_full' === $dates_mode ) {
			return false;
		}

		// Decline not allowed week days
		if ( ! empty( $available_week_days ) ) {

			if ( ! in_array( strtolower( date( 'l', $date ) ), $available_week_days ) ) {
				return false;
			}
		}

		// Allow other any other date
		return true;

	}

	/**
	 * Check if this appointmetn is available
	 *
	 * @param  [type] $appointment_data [description]
	 * @return [type]                   [description]
	 */
	public function appointment_available( $appointment ) {

		if ( is_array( $appointment ) ) {
			$appointment = new Appointment_Model( $appointment );
		}

		$query_args   = [];
		$is_available = false;
		$service_id   = $appointment->get( 'service' );
		$provider_id  = $appointment->get( 'provider' );
		$buffer_before = Tools::get_time_settings( $service_id, $provider_id, 'buffer_before', 0 );
		$buffer_after = Tools::get_time_settings( $service_id, $provider_id, 'buffer_after', 0 );

		if ( ! $this->is_appointment_date_allowed( $appointment ) ) {
			return false;
		}

		if ( ! empty( $service_id ) && 'service' === Plugin::instance()->settings->get( 'check_by' ) ) {
			$query_args['service'] = $service_id;
		}

		if ( ! empty( $provider_id ) ) {
			$query_args['provider'] = $provider_id;
		}

		$query_args['date']     = $appointment->get( 'date' );
		$query_args['status']   = array_merge( Plugin::instance()->statuses->exclude_statuses() );

		$manage_capacity = Plugin::instance()->settings->get( 'manage_capacity' );

		if ( $manage_capacity ) {
			$booked_appointments = Plugin::instance()->db->appointments->query_with_capacity( $query_args );
			$service_count       = Plugin::instance()->tools->get_service_count( $service_id );
		} else {
			$booked_appointments = Plugin::instance()->db->appointments->query( $query_args );
		}

		$external            = Calendar::get_external( $service_id, $provider_id, $appointment->get( 'date' ) );
		$booked_appointments = Calendar::merge_excluded( ( ( empty( $booked_appointments ) || ! is_array( $booked_appointments ) ) ? [] : $booked_appointments ), $external, $service_id, $provider_id );

		if ( ! empty( $booked_appointments ) ) {

			$appointment_slot     = $appointment->get( 'slot' ) - intval( $buffer_before );
			$appointment_slot_end = $appointment->get( 'slot_end' ) + intval( $buffer_after );
			$slot_count           = $appointment->get( 'count' );

			foreach ( $booked_appointments as $booked_appointment ){
				$booked_appointment = new Appointment_Model( $booked_appointment );

				if ( $booked_appointment->conditions()->is_in_range(
						[ 'start' => $appointment_slot, 'end' => $appointment_slot_end ],
						[ 'before' => $buffer_before, 'after' => $buffer_after ]
					)
				) {

					if ( $appointment->conditions()->is_with_capacity() ) {
						if ( $booked_appointment->conditions()->is_in_range(
							[ 'start' => $appointment->get( 'slot' ), 'end' => $appointment->get( 'slot_end' ) ]
							)
						) {
							$slot_count += $booked_appointment->get( 'count' );
						}
						if ( $slot_count > $service_count ) {
							$is_available = true;
							break;
						}
					} else {
						$is_available = true;
						break;
					}
				}
			}
		}

		if ( ! $is_available ) {
			return true;
		} else {
			return false;
		}

	}

	/**
	 * Delete appointment from DB
	 *
	 * @param  [type] $appointment_id [description]
	 * @return [type]                 [description]
	 */
	public function delete_appointment( $appointment_id ) {

		$appointment = $this->get_appointment_by( 'ID', $appointment_id );

		if ( ! $appointment ) {
			return;
		}

		$appointment_where = array(
			'ID' => $appointment_id,
		);

		$this->appointments->delete( $appointment_where );

		$this->appointments_meta->create_table( false );
		$this->appointments_meta->delete( [
			'appointment_id' => $appointment_id,
		] );

		$this->remove_appointment_date_from_excluded( $appointment );
		$this->maybe_remove_excluded_app( $appointment );

		/**
		 * Trigger hook after appoinetment deletion
		 */
		do_action( 'jet-apb/db/delete/appointments', $appointment );
	}

	/**
	 * Insert new appointment and maybe add excluded date
	 *
	 * @param  array  $appointment [description]
	 * @return [type]              [description]
	 */
	public function add_appointment( $appointment = array() ) {
		$app_model = new Appointment_Model( $appointment );
		return $app_model->save();
	}

	/**
	 * Maybe add appointment date to excluded
	 *
	 * @param  [type] $appointment [description]
	 * @return [type]              [description]
	 */
	public function maybe_exclude_appointment_date( $appointment, $exclude_other = true ) {
		$this->excluded_dates->maybe_exclude_appointment_date( $appointment, $exclude_other = true );
	}

	public function maybe_exclude_other_app( $appointment ) {
		$this->excluded_dates->maybe_exclude_other_app( $appointment );
	}

	public function maybe_remove_excluded_app( $appointment ) {
		$this->excluded_dates->maybe_remove_excluded_app( $appointment );
	}

	/**
	 * Returns appointments detail by order id
	 *
	 * @return [type] [description]
	 */
	public function get_appointments_by( $field = 'ID', $value = null ) {

		$appointments = $this->appointments->query( [ $field => $value ] );

		if ( empty( $appointments ) ) {
			return false;
		}

		return $appointments;

	}

	/**
	 * Returns appointment detail by order id
	 *
	 * @return [type] [description]
	 */
	public function get_appointment_by( $field = 'ID', $value = null ) {

		$appointment = $this->get_appointments_by( $field, $value );
		$appointment = $this->get_appointments_meta( $appointment );

		return ! empty( $appointment ) ? $appointment[0] : false;
	}

	/**
	 * Get meta data of given appointments
	 *
	 * @param  array  $appointments [description]
	 * @return [type]               [description]
	 */
	public function get_appointments_meta( $appointments = [] ) {

		if ( empty( $appointments ) ) {
			return [];
		}

		$ids = [];
		$appointments_by_ids = [];

		foreach ( $appointments as $app ) {
			if ( is_object( $app ) ) {
				$app = $app->to_array();
			}

			$appointments_by_ids[ $app['ID'] ] = $app;
			$ids[] = $app['ID'];
		}

		$this->appointments_meta->create_table( false );

		$meta = $this->appointments_meta->query( [ 'appointment_id' => $ids ] );

		if ( ! empty( $meta ) ) {
			foreach ( $meta as $_row ) {
				if ( empty( $appointments_by_ids[ $_row['appointment_id'] ] ) ) {
					$appointments_by_ids[ $_row['appointment_id'] ]['meta'] = [];
				}

				$appointments_by_ids[ $_row['appointment_id'] ]['meta'][ $_row['meta_key'] ] = maybe_unserialize( $_row['meta_value'] );
			}

			return array_values( $appointments_by_ids );
		} else {
			return $appointments;
		}

	}

}
