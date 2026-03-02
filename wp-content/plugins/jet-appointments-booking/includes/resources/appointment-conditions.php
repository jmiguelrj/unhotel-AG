<?php
namespace JET_APB\Resources;

use JET_APB\Plugin;

class Appointment_Conditions {

	protected $app;

	public function __construct( $app ) {
		$this->app = $app;
	}

	/**
	 * Check if appointment (or given) status is invalid
	 *
	 * @param string $status
	 *
	 * @return bool
	 */
	public function is_status_invalid( $status = '' ) {

		if ( empty( $status ) ) {
			$status = $this->app->get( 'status' );
		}

		return in_array( $status, Plugin::instance()->statuses->invalid_statuses() );
	}

	/**
	 * Check if appointment (or given) status is excluded
	 *
	 * @param string $status
	 *
	 * @return bool
	 */
	public function is_status_excluded( $status = '' ) {

		if ( empty( $status ) ) {
			$status = $this->app->get( 'status' );
		}

		return in_array( $status, Plugin::instance()->statuses->exclude_statuses() );
	}

	/**
	 * Check if appointment slot and slot_end timestamps are in given range
	 *
	 * @param array $range
	 * @param array $buffers
	 * @return boolean
	 */
	public function is_in_range( $range = [], $buffers = [] ) {
		$slot = $this->app->get( 'slot' );
		$slot_end = $this->app->get( 'slot_end' );

		if ( ! empty( $buffers ) ) {
			$slot = $slot - $buffers['before'];
			$slot_end = $slot_end + $buffers['after'];
		}

		$range_start = $range['start'];
		$range_end   = $range['end'];

		if ( ( $slot < $range_start && $range_start < $slot_end )
			|| ( $slot < $range_end && $range_end < $slot_end )
			|| ( $range_start < $slot && $slot < $range_end )
			|| ( $range_start < $slot_end && $slot_end < $range_end )
			|| ( $slot == $range_start && $range_end == $slot_end )
		) {
			return true;
		}

		return false;
	}

	/**
	 * Check if current appointment supports capacity management
	 *
	 * @return boolean
	 */
	public function is_with_capacity() {

		$type = $this->app->get( 'type' );
		$manage_capacity = Plugin::instance()->settings->get( 'manage_capacity' );

		if ( ( 'slot' === $type || 'recurring' === $type ) && $manage_capacity ) {
			return true;
		} else {
			return false;
		}
	}
}