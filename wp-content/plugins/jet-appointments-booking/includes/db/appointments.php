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
 * Define DB class
 */
class Appointments extends Base {

	public $defaults = array(
		'status'   => 'pending',
		'provider' => 0,
	);

	/**
	 * Additinal DB columns list
	 *
	 * @var array
	 */
	public $additional_db_columns = array();

	public function get_defaults() {
		$this->defaults['appointment_date'] = wp_date( 'Y-m-d H:i:s' );
		return $this->defaults;
	}

	/**
	 * Return table slug
	 *
	 * @return [type] [description]
	 */
	public function table_slug() {
		return 'appointments';
	}

	/**
	 * Returns additional DB columns
	 * @return [type] [description]
	 */
	public function get_additional_db_columns() {
		return $this->additional_db_columns;
	}

	/**
	 * Returns currently queried appointment ID
	 *
	 * @return [type] [description]
	 */
	public function get_queried_item_id() {

		$object = jet_engine()->listings->data->get_current_object();

		if ( is_object( $object ) ) {

			if ( isset( $object->post_type ) && 'jet_apb_list' === $object->post_type ) {
				return $object->ID;
			} else {
				return false;
			}

		} elseif ( is_array( $object ) ) {
			return isset( $object['ID'] ) ? $object['ID'] : false;
		} else {
			return false;
		}
	}

	/**
	 * Add new DB column
	 *
	 * @param [type] $column [description]
	 */
	public function add_column( $column ) {
		$this->additional_db_columns[] = $column;
	}

	/**
	 * Ensure provider is passed correctly
	 *
	 * @param  array  $data [description]
	 * @return [type]       [description]
	 */
	public function sanitize_data_before_db( $data = array() ) {

		$data['provider'] = ! empty( $data['provider'] ) ? absint( $data['provider'] ) : 0;

		return $data;
	}

	/**
	 * Returns columns schema
	 * @return [type] [description]
	 */
	public function schema() {
		return array(
			'ID'               => "bigint(20) NOT NULL AUTO_INCREMENT",
			'group_ID'         => "bigint(20)",
			'status'           => "text",
			'service'          => "text",
			'provider'         => "text",
			'order_id'         => "bigint(20)",
			'user_id'          => "bigint(20)",
			'user_name'        => "text NOT NULL",
			'user_email'       => "text",
			'date'             => "bigint(20) NOT NULL",
			'date_end'         => "bigint(20) NOT NULL",
			'slot'             => "bigint(20) NOT NULL",
			'slot_end'         => "bigint(20) NOT NULL",
			'type'             => "text",
			'appointment_date' => "datetime NOT NULL default '0000-00-00 00:00:00'",
		);
	}

	/**
	 * Returns columns list
	 * @return array
	 */
	public function get_columns_list() {

		$default_columns    = $this->schema();
		$additional_columns = $this->get_additional_db_columns();
		$columns = array_keys( $default_columns );

		if ( ! empty( $additional_columns ) ) {
			$columns = array_merge( $columns, $additional_columns );
		}

		return $columns;
	}

	/**
	 * Create DB table for apartment units
	 *
	 * @return [type] [description]
	 */
	public function get_table_schema() {

		$charset_collate = $this->wpdb()->get_charset_collate();
		$table           = $this->table();

		$default_columns    = $this->schema();
		$additional_columns = $this->get_additional_db_columns();
		$columns_schema     = '';

		foreach ( $default_columns as $column => $desc ) {
			$columns_schema .= $column . ' ' . $desc . ',';
		}

		if ( is_array( $additional_columns ) && ! empty( $additional_columns ) ) {
			foreach ( $additional_columns as $column ) {
				$columns_schema .= $column . ' text,';
			}
		}

		return "CREATE TABLE $table (
			$columns_schema
			PRIMARY KEY (ID)
		) $charset_collate;";

	}

	public function prepare_params( $params ) {

		$offset      = ! empty( $params['offset'] ) ? absint( $params['offset'] ) : 0;
		$per_page    = isset( $params['per_page'] ) ? intval( $params['per_page'] ) : 50;
		$sort        = ! empty( $params['sort'] ) ? $params['sort'] : array();
		$filter      = ! empty( $params['filter'] ) ? $params['filter'] : array();
		$search_in   = ! empty( $params['search_in'] ) ? str_replace( ',', ', ', $params['search_in'] )  : false ;
		$mode        = ! empty( $params['mode'] ) ? $params['mode'] : 'all';

		if ( ! is_array( $sort ) && ! empty( $sort ) ) {
			$sort = json_decode( $sort, true );
		}

		if ( ! is_array( $filter ) && ! empty( $filter ) ) {
			$filter = json_decode( $filter, true );
		}

		$filter = ( ! empty( $filter ) && is_array( $filter ) ) ? array_filter( $filter ) : array();
		$sort   = ( ! empty( $sort ) && is_array( $sort ) ) ? array_filter( $sort ) : array( 'orderby' => 'ID', 'order' => 'DESC', );

		$search = ! empty( $filter['search'] ) ? $filter['search'] : false ;

		if ( $search ) {
			unset( $filter['search'] );
		}

		if ( ! empty( $filter['date'] ) && ! is_int( $filter['date'] ) ) {
			$filter_date = $filter['date'];
			unset( $filter['date'] );

			$filter = array_merge( $filter, $this->parse_date( $filter_date ) );
		}

		switch ( $mode ) {

			case 'upcoming':
				$filter['slot>>'] = time();
				break;

			case 'past':
				$filter['slot<<'] = time();
				break;

		}

		return [
			'filter' => $filter,
			'per_page' => $per_page,
			'offset' => $offset,
			'sort' => $sort,
			'search' => $search,
			'search_in' => $search_in,
		];
	}

	public function get_appointments( $params = array() ) {

		$prepared_params = $this->prepare_params( $params );

		$appointments = $this->query(
			$prepared_params['filter'],
			$prepared_params['per_page'],
			$prepared_params['offset'],
			$prepared_params['sort'],
			$prepared_params['search'],
			$prepared_params['search_in']
		);

		if ( empty( $appointments ) ) {
			$appointments = array();
		}

		return $appointments;

	}

	/**
	 * Return count of queried items
	 *
	 * @return [type] [description]
	 */
	public function count( $args = array(), $rel = 'AND' ) {

		$prepared_params = $this->prepare_params( $args );

		$query = $this->get_query_string(
			'count',
			$prepared_params['filter'],
			0,
			0,
			$prepared_params['sort'],
			$prepared_params['search'],
			$prepared_params['search_in']
		);

		return $this->wpdb()->get_var( $query );

	}

	public function parse_date( $date ) {

		$output = [];
		$dates_array = explode( '-', $date );

		if ( count( $dates_array ) > 1 ) {
			$output['date>='] = strtotime( $dates_array[0] );
			$output['date<='] = strtotime( $dates_array[1] );
		} else {
			$output['date'] = strtotime( $dates_array[0] );
		}

		return $output;

	}

	/**
	 * Query appointments with capacity counted
	 *
	 * @return [type] [description]
	 */
	public function query_with_capacity( $args = array(), $exclude_duplicated = false ) {

		$table = $this->table();

		$service = 'service';

		$query = "SELECT DISTINCT $service, provider, date, slot, slot_end FROM $table";
		$capacity_query = "SELECT slot, COUNT( slot ) AS count FROM $table";
		$rel   = 'AND';

		if ( isset( $args['after'] ) ) {
			$after = $args['after'];
			unset( $args['after'] );
			$args['ID>'] = $after;
		}

		if ( isset( $args['before'] ) ) {
			$before = $args['before'];
			unset( $args['before'] );
			$args['ID<'] = $before;
		}

		$query .= $this->add_where_args( $args, $rel );
		$capacity_query .= $this->add_where_args( $args, $rel );
		$capacity_query .= " GROUP BY slot";

		$raw = $this->wpdb()->get_results( $query, ARRAY_A );
		$capacity = $this->wpdb()->get_results( $capacity_query, OBJECT_K );

		foreach ( $raw as &$item ) {
			$item['count'] = isset( $capacity[ $item['slot'] ] ) ? $capacity[ $item['slot'] ]->count : 1;
			$item['booked_count'] = isset( $capacity[ $item['slot'] ] ) ? $capacity[ $item['slot'] ]->count : 1;
		}

		if ( $exclude_duplicated ) {
			$raw = $this->remove_date_duplicates( $raw );
		}

		return $raw;

	}

	/**
	 * Remove elements with the same dates from an array.
	 *
	 * @param array $excluded Input array.
	 * @return array
	 */
	public function remove_date_duplicates( $excluded = array() ) {

		if ( empty( $excluded ) ) {
            return [];
        }

		$result  = [];
		$checked = [];

		foreach ( $excluded as $item ) {

			if ( ! is_array( $item ) || ! isset( $item['slot'], $item['slot_end'] ) ) {
                continue;
            }

			$key = $item['slot'] . '-' . $item['slot_end'];

			if ( ! isset( $checked[$key] ) ) {

				$checked[$key] = true;
				$result[]      = $item;

			}

		}
		
		return $result;
	}

}