<?php
namespace JET_APB\DB;

/**
 * Database manager class
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Appointments_External DB class
 */
class Appointments_External extends Base {

	/**
	 * Return table slug
	 *
	 * @return [type] [description]
	 */
	public function table_slug() {
		return 'appointments_external';
	}

	/**
	 * Returns columns schema
	 * @return [type] [description]
	 */
	public function schema() {
		return array(
			'ID'          => 'bigint(20) NOT NULL AUTO_INCREMENT',
			'service'     => 'bigint(20)',
			'provider'    => 'bigint(20)',
			'date'        => "bigint(20) NOT NULL",
			'date_end'    => "bigint(20) NOT NULL",
			'slot'        => "bigint(20) NOT NULL",
			'slot_end'    => "bigint(20) NOT NULL",
			'external_id' => "text NOT NULL",
			'calendar_id' => "text NOT NULL",
		);
	}

	/**
	 * Create DB table for apartment units
	 *
	 * @return [type] [description]
	 */
	public function get_table_schema() {

		$charset_collate = $this->wpdb()->get_charset_collate();
		$table           = $this->table();
		$default_columns = $this->schema();
		$columns_schema  = '';

		foreach ( $default_columns as $column => $desc ) {
			$columns_schema .= $column . ' ' . $desc . ',';
		}

		return "CREATE TABLE $table (
			$columns_schema
			PRIMARY KEY (ID)
		) $charset_collate;";
	}

	/**
	 * Create new external appoinemtment
	 *
	 * Format:
	 * [
	 * 	'service'     => 'Service ID or 0',
	 * 	'provider'    => 'Provider ID or 0',
	 * 	'date'        => "Timestamp of appointment start",
	 * 	'date_end'    => "Timestamp of appointment end",
	 * 	'slot'        => "Timestamp of appointment slot start",
	 * 	'slot_end'    => "Timestamp of appointment slot end",
	 * 	'external_id' => "External ID of appointment in external system to avoid duplicates",
	 * ]
	 *
	 * @param array $data
	 * @return void
	 */
	public function new_appointment( $data = [] ) {
		$this->create_table();
		$this->insert( $data );
	}

	/**
	 * Get future events
	 *
	 * @return array
	 */
	public function get_future_events_ids() {

		$this->create_table();

		$events = $this->wpdb()->get_col(
			$this->wpdb()->prepare(
				"SELECT external_id FROM {$this->table()} WHERE slot > %d",
				time()
			)
		);

		return $events;
	}

	/**
	 * Query external events
	 *
	 * @param array $args
	 * @return array
	 */
	public function external_query( $args = array() ) {

		$table = $this->table();

		$query = "SELECT DISTINCT external_id, service, provider, date, date_end, slot, slot_end FROM $table";
		$rel   = 'AND';


		$query .= $this->add_where_args( $args, $rel );

		$raw = $this->wpdb()->get_results( $query, OBJECT_K );

		return $raw;

	}

}
