<?php
namespace JET_APB\Resources;

use JET_APB\Plugin;

class Appointment_Model {

	protected $data = [];
	protected $initial_data = [];
	protected $meta = [];
	protected $conditions = null;

	public function __construct( $data = [], $ID = false ) {

		if ( $ID ) {
			$this->data = Plugin::instance()->db->get_appointment_by( 'ID', $ID );
			$this->set( 'ID', $ID );
			$this->initial_data = $this->data;
		}

		if ( ! empty( $this->data['meta'] ) ) {
			$this->set_meta( $this->data['meta'] );
			unset( $this->data['meta'] );
		}

		if ( ! empty( $data ) ) {
			foreach ( $data as $key => $value ) {
				if ( 'meta' === $key ) {
					$this->set_meta( $value );
				} else {
					$this->data[ $key ] = $value;
				}
			}
		}

		$this->sanitize_data();
	}

	/**
	 * Get appointment conditions checker instance
	 *
	 * @return Appointment_Conditions
	 */
	public function conditions() {
		if ( null === $this->conditions ) {
			$this->conditions = new Appointment_Conditions( $this );
		}

		return $this->conditions;
	}

	/**
	 * Sanitize appointment data to ensure all props is correctly set.
	 */
	public function sanitize_data() {

		if ( ! $this->get( 'user_id' ) && is_user_logged_in() ) {
			$this->set( 'user_id', get_current_user_id() );
		}

		if ( ! $this->get( 'provider' ) ) {
			$this->set( 'provider', 0 );
		}

		$slot_end = $this->get( 'slotEnd' );

		if ( $slot_end ) {
			$this->set( 'slot_end', absint( $slot_end ) );
			$this->delete_key( 'slotEnd' );
		}

		$type = $this->get( 'type' );

		if ( ! $type ) {
			$this->set( 'type', Plugin::instance()->settings->get( 'booking_type' ) );
		}

		if ( ! $this->get( 'appointment_date' ) ) {
			$this->set( 'appointment_date', wp_date( 'Y-m-d H:i:s' ) );
		}

		$timezone      = $this->get( 'timezone' );
		$friendly_time = $this->get( 'friendlyTime' );
		$friendly_date = $this->get( 'friendlyDate' );

		$use_calendar_timezone = Plugin::instance()->settings->get( 'use_calendar_timezone' );

		if ( ! $timezone && $use_calendar_timezone ) {
			$calendar_timezone = Plugin::instance()->settings->get( 'calendar_timezone' );
			$timezone          = $calendar_timezone[0];
		}

		if ( $timezone && $friendly_time && $friendly_date ) {
			$this->set_meta( 'user_local_time', $friendly_time );
			$this->set_meta( 'user_local_date', $friendly_date );
			$this->set_meta( 'user_timezone', $timezone );
		}

		if ( ! $this->get( 'status' ) ) {
			if ( $this->get_initial( 'status' ) ) {
				$this->set( 'status', $this->get_initial( 'status' ) );
			} else {
				$this->set( 'status', 'pending' );
			}
		}

		$capacity = $this->get( 'capacity' );

		if ( ! $capacity ) {
			$this->set( 'capacity', 1 );
		} else {
			$this->set( 'capacity', absint( $capacity ) );
		}

		$count = $this->get( 'count' );

		if ( ! $count ) {
			$this->set( 'count', 1 );
		} else {
			$this->set( 'count', absint( $count ) );
		}
	}

	/**
	 * Delete key from appointment data
	 *
	 * @param string $key
	 */
	public function delete_key( $key ) {

		if ( empty( $key ) ) {
			return false;
		}

		if ( isset( $this->data[ $key ] ) ) {
			unset( $this->data[ $key ] );
		}

		return true;
	}

	/**
	 * Get appointment data
	 *
	 * @param bool $with_meta
	 * @return array
	 */
	public function get_data( $with_meta = false ) {

		$data = $this->data;

		if ( $with_meta && ! empty( $this->meta ) ) {
			$data['meta'] = $this->meta;
		}

		return $data;
	}

	/**
	 * Get appointment meta data
	 *
	 * @param string $key
	 * @return array
	 */
	public function get_meta( $key = '' ) {

		if ( $key && isset( $this->meta[ $key ] ) ) {
			return $this->meta[ $key ];
		} elseif ( $key ) {
			return false;
		}

		return $this->meta;
	}

	/**
	 * Set appointment meta data
	 *
	 * @param string|array $key Single key or array of key=>value pairs
	 * @param mixed  $value
	 */
	public function set_meta( $key, $value = null ) {

		if ( empty( $key ) ) {
			return false;
		}

		if ( is_array( $key ) ) {
			foreach ( $key as $k => $v ) {
				$this->meta[ $k ] = $v;
			}
		} else {
			$this->meta[ $key ] = $value;
		}

		return true;
	}

	/**
	 * Get appointment data by key
	 *
	 * @param string $key
	 *
	 * @return array|bool
	 */
	public function get( $key = '' ) {

		if ( 'meta' === $key ) {
			return $this->get_meta();
		}

		if ( empty( $key ) ) {
			return $this->get_data();
		}

		if ( ! isset( $this->data[ $key ] ) ) {

			// check if key is in meta
			if ( isset( $this->meta[ $key ] ) ) {
				return $this->meta[ $key ];
			}

			return false;
		}

		return $this->data[ $key ];
	}

	/**
	 * Get initial appointment data by key
	 *
	 * @param string $key
	 *
	 * @return array|bool
	 */
	public function get_initial( $key = '' ) {

		if ( empty( $key ) ) {
			return $this->initial_data;
		}

		if ( ! isset( $this->initial_data[ $key ] ) ) {
			return false;
		}

		return $this->initial_data[ $key ];
	}

	/**
	 * Get appointment data by key
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return bool
	 */
	public function set( $key, $value = null ) {

		if ( empty( $key ) ) {
			return false;
		}

		$this->data[ $key ] = $value;

		return true;
	}

	/**
	 * Chek if appointment prop is changed whild updating
	 *
	 * @param  string $key
	 * @return boolean
	 */
	public function is_prop_changed( $key ) {

		$current = $this->get( $key );
		$initial = $this->get_initial( $key );

		return ( $current !== $initial );
	}


	/**
	 * Get appointment DB instance
	 *
	 * @return object
	 */
	public function get_db() {
		return Plugin::instance()->db->appointments;
	}

	/**
	 * Get appointment meta DB instance
	 *
	 * @return object
	 */
	public function get_db_meta() {
		return Plugin::instance()->db->appointments_meta;
	}

	/**
	 * Save appointment
	 *
	 * @return int|bool
	 */
	public function save() {

		$ID = $this->get( 'ID' );

		if ( ! empty( $ID ) ) {
			$result = $this->update_appointment();
		} else {
			$result = $this->create_appointment();
		}

		/**
		 * Trigger update hook after appoinetment created
		 */
		do_action( 'jet-apb/db/update/appointments', $this->get_data( true ), true, [], $this );

		return $result;
	}

	/**
	 * Update appointment in DB.
	 *
	 * @return int|bool
	 */
	public function update_appointment() {

		if ( $this->is_prop_changed( 'status' ) ) {

			if ( $this->conditions()->is_status_invalid() ) {
				Plugin::instance()->db->remove_appointment_date_from_excluded( $this->get_initial() );
			}

			if ( $this->conditions()->is_status_invalid( $this->get_initial( 'status' ) )
				&& $this->conditions()->is_status_excluded()
			) {
				Plugin::instance()->db->maybe_exclude_appointment_date( $this->get_initial() );
			}
		}

		foreach ( $this->get_meta() as $meta_key => $meta_value ) {
			$this->get_db_meta()->update( [
				'meta_value' => maybe_unserialize( $meta_value )
			], [
				'appointment_id' => $this->get( 'ID' ),
				'meta_key'       => $meta_key,
			] );
		}

		$this->get_db()->update( $this->get_data_for_db(), array( 'ID' => $this->get( 'ID' ) ) );

		return $this->get( 'ID' );
	}


	/**
	 * Create appointment in DB.
	 *
	 * @return int|bool
	 */
	public function create_appointment() {

		$appointment_id = $this->get_db()->insert( $this->get_data_for_db() );

		$this->get_db_meta()->create_table( false );

		foreach ( $this->get_meta() as $meta_key => $meta_value ) {
			$this->get_db_meta()->insert( [
				'appointment_id' => $appointment_id,
				'meta_key'       => $meta_key,
				'meta_value'     => maybe_serialize( $meta_value ),
			] );
		}

		Plugin::instance()->db->maybe_exclude_appointment_date( $this->get_data() );

		$this->set( 'ID', $appointment_id );

		/**
		 * Trigger hook after appoinetment created
		 */
		do_action( 'jet-apb/db/create/appointments', $this->get_data( true ), $this );

		return $appointment_id;
	}

	/**
	 * Get appointment data for DB
	 *
	 * @return array
	 */
	public function get_data_for_db() {
		$data = $this->get_data();

		if ( isset( $data['meta'] ) ) {
			unset( $data['meta'] );
		}

		$allowed_columns    = Plugin::instance()->db->appointments->get_columns_list();
		$additional_columns = Plugin::instance()->settings->get( 'db_columns' );

		if ( ! empty( $additional_columns ) ) {
			foreach ( $additional_columns as $additional_column ) {
				if ( ! in_array( $additional_column, $allowed_columns ) ) {
					$allowed_columns[] = $additional_column;
				}
			}
		}

		$result = [];

		foreach ( $data as $key => $value ) {
			if ( in_array( $key, $allowed_columns ) ) {
				$result[ $key ] = $value;
			}
		}

		return $result;
	}

	/**
	 * Get appointment service title
	 *
	 * @return string|bool
	 */
	public function get_service_title() {

		$service_title = $this->get( 'service_title' );

		// Return immediately if we already have the title
		if ( $service_title ) {
			return $service_title;
		}

		$service = $this->get( 'service' );

		if ( ! $service ) {
			return false;
		}

		$title = get_the_title( $service );

		$this->set( 'service_title', $title );

		return $title;
	}

	/**
	 * Get appointment provider title
	 *
	 * @return string|bool
	 */
	public function get_provider_title() {

		$provider_title = $this->get( 'provider_title' );

		// Return immediately if we already have the title
		if ( $provider_title ) {
			return $provider_title;
		}

		$provider = $this->get( 'provider' );

		if ( ! $provider ) {
			return false;
		}

		$title = get_the_title( $provider );

		$this->set( 'provider_title', $title );

		return $title;
	}

	/**
	 * Get appointment human readable date
	 *
	 * @return string
	 */
	public function get_human_readable_date() {

		$date = $this->get( 'date' );

		return Plugin::instance()->tools->get_verbosed_date( $date );
	}

	/**
	 * Get appointment human readable date
	 *
	 * @param string $key Slot start or slot end.
	 * @return string
	 */
	public function get_human_readable_slot( $key = 'slot' ) {

		$slot = $this->get( $key );

		if ( ! $slot ) {
			return '';
		}

		return Plugin::instance()->tools->get_verbosed_slot( $slot );
	}

	/**
	 * Create human readable date prop
	 *
	 * @param string $date
	 *
	 * @return $this
	 */
	public function compute_human_date( $format = '' ) {

		if ( ! $format ) {
			$format = get_option( 'time_format' );
		}

		$date = sprintf(
			'%1$s, %2$s - %3$s',
			$this->get_human_readable_date(),
			$this->get_human_readable_slot(),
			$this->get_human_readable_slot( 'slot_end' )
		  );

		$this->set( 'human_read_date', $date );

		return $this;
	}

	/**
	 * Return current appoiment data as array
	 *
	 * @return array
	 */
	public function to_array() {

		$data = $this->get_data();
		$data['meta'] = $this->get_meta();

		return $data;
	}
}