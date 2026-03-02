<?php
namespace JET_APB\Admin;

use JET_APB\Plugin;
use JET_APB\Time_Slots;
use JET_APB\Calendar;

/**
 * Settings manager
 */
class Settings {

	/**
	 * Default settings array
	 *
	 * @var array
	 */
	public $defaults = [];

	/**
	 * Main settings array
	 *
	 * @var array
	 */
	public $main_settings = [
		'is_set'                       => false,
		'services_cpt'                 => '',
		'providers_cpt'                => '',
		'providers_slot_duplicating'   => true,
		'db_columns'                   => [],
		'wc_integration'               => false,
		'wc_product_id'                => false,
		'wc_synch_orders'              => false,
		'hide_setup'                   => false,
		'check_by'                     => 'global',
		'manage_capacity'              => false,
		'show_capacity_counter'        => false,
		'allow_manage_count'           => false,
		'use_custom_labels'            => false,
		'process_on_hold'              => 'invalid',
		'switch_status'                => false,
		'show_timezones'               => false,
		'allow_action_links'           => false,
		'same_group_token'             => false,
		'break_by_days'                => false,
		'confirm_action_template_type' => 'text_message',
		'confirm_action_message'       => '',
		'confirm_action_template'      => '',
		'cancel_action_template_type'  => 'text_message',
		'cancel_action_message'        => '',
		'cancel_action_template'       => '',
		'switch_status_period'         => 'hourly',
		'switch_status_from'           => [ 'on-hold' ],
		'switch_status_to'             => 'failed',
		'calendar_layout'              => 'default',
		'use_calendar_timezone'        => false,
		'calendar_timezone'            => 'UTC',
		'scroll_to_details'            => false,
		'capability_type'              => 'manage_options',
		'cancel_deadline_limit'        => 1,
		'confirm_deadline_limit'       => 1,
		'cancel_deadline_unit'         => 'hour',
		'confirm_deadline_unit'        => 'hour',
		'custom_labels'                => [
			'Sun'                  => 'Sun',
			'Mon'                  => 'Mon',
			'Tue'                  => 'Tue',
			'Wed'                  => 'Wed',
			'Thu'                  => 'Thu',
			'Fri'                  => 'Fri',
			'Sat'                  => 'Sat',
			'January'              => 'January',
			'February'             => 'February',
			'March'                => 'March',
			'April'                => 'April',
			'May'                  => 'May',
			'June'                 => 'June',
			'July'                 => 'July',
			'August'               => 'August',
			'September'            => 'September',
			'October'              => 'October',
			'November'             => 'November',
			'December'             => 'December',
			'timeAlreadyTaken'     => 'Appointment time already taken',
			'timeNotAllowedToBook' => 'Selected time is not allowed to book',
			'appointmentDetails'   => 'Appointment details:',
			'capacityCount'        => 'Slot Capacity Count:',
			'appointmentCount'     => 'Appointment Count:',
			'minSlotCount'         => 'Sorry. You have not selected enough slots, minimum quantity: %s',
			'invalidToken'         => 'Token is invalid or was already used.',
		],
	];

	
	/**
	 * Working Hours Config array
	 *
	 * @var array
	 */
	public $working_hours = [
		'slot_time_format' => 'H:i',
		'buffer_before'  => 0,
		'buffer_after'   => 0,
		'default_slot'   => 1800,
		'locked_time'    => 0,
		'booking_type'   => 'slot',
		'max_duration'   => 3600,
		'step_duration'  => 300,
		'several_days'   => false,
		'only_start'     => false,
		'multi_booking'  => false,
		'min_slot_count' => 1,
		'max_slot_count' => 1,
		'min_recurring_count' => 1,
		'max_recurring_count' => 5,
		'days_off'       => [],
		'working_days'   => [],
		'working_days_mode' => 'override_full',
		'days_off_allow_rewrite' => 'allow',
		're_booking'     => [],
		'appointments_range' => [
			'type'       => 'all',
			'range_num'  => 60,
			'range_unit' => 'days',
		],
		'working_hours'  =>[
			'monday'    => [
				[ 'from' => '08:00', 'to' => '17:00' ]
			],
			'tuesday'   => [
				[ 'from' => '08:00', 'to' => '17:00' ]
			],
			'wednesday' => [
				[ 'from' => '08:00', 'to' => '17:00' ]
			],
			'thursday'  => [
				[ 'from' => '08:00', 'to' => '17:00' ]
			],
			'friday'    => [
				[ 'from' => '08:00', 'to' => '17:00' ]
			],
			'saturday'  => [],
			'sunday'    => [],
		],
	];

	/**
	 * Assets Config array
	 *
	 * @var array
	 */
	public $assets = [];

	/**
	 * Settings DB key
	 *
	 * @var string
	 */
	private $key = 'jet-apb-settings';

	/**
	 * Stored settings cache
	 *
	 * @var null
	 */
	public $settings = null;

	/**
	 * [__construct description]
	 * @param array $pages [description]
	 */
	public function __construct() {
		$this->set_assets();
		$this->set_default_config();

		add_action( 'wp_ajax_jet_apb_save_settings', array( $this, 'ajax_save_settings' ) );
		add_action( 'wp_ajax_jet_apb_clear_excluded', array( $this, 'reset_excluded_dates' ) );
	}

	/**
	 * Reset excluded dates data
	 *
	 * @return [type] [description]
	 */
	private function set_default_config() {
		$this->defaults = wp_parse_args( $this->main_settings, $this->working_hours );
	}

	/**
	 * Reset excluded dates data
	 *
	 * @return [type] [description]
	 */
	private function set_assets() {
		$this->assets = [
			'weekdays'   => [
				'monday'    => esc_html__( 'Monday', 'jet-appointments-booking' ),
				'tuesday'   => esc_html__( 'Tuesday', 'jet-appointments-booking' ),
				'wednesday' => esc_html__( 'Wednesday', 'jet-appointments-booking' ),
				'thursday'  => esc_html__( 'Thursday', 'jet-appointments-booking' ),
				'friday'    => esc_html__( 'Friday', 'jet-appointments-booking' ),
				'saturday'  => esc_html__( 'Saturday', 'jet-appointments-booking' ),
				'sunday'    => esc_html__( 'Sunday', 'jet-appointments-booking' ),
			],
			'booking_types' => [
				[
					'value' => 'slot',
					'label' => esc_html__( 'Slot', 'jet-appointments-booking' ),
				],
				[
					'value' => 'range',
					'label' => esc_html__( 'Time Picker', 'jet-appointments-booking' ),
				],
				[
					'value' => 'recurring',
					'label' => esc_html__( 'Recurring', 'jet-appointments-booking' ),
				],
			],
			'rebooking_options' => [
				[
					'value' => 'day',
					'label' => esc_html__( 'Day', 'jet-appointments-booking' ),
				],
				[
					'value' => 'week',
					'label' => esc_html__( 'Week', 'jet-appointments-booking' ),
				],
				[
					'value' => 'month',
					'label' => esc_html__( 'Month', 'jet-appointments-booking' ),
				],
				[
					'value' => 'year',
					'label' => esc_html__( 'Year', 'jet-appointments-booking' ),
				],
			],
			'slot_time_format' => [
				[
					'value' => '',
					'label' => esc_html__( 'Select...', 'jet--appointments-booking' ),
				],
				[
					'value' => 'H:i',
					'label' => '13:00 - 14:00',
				],
				[
					'value' => 'g:i a',
					'label' => '1:00 pm - 2:00 pm',
				],
				[
					'value' => 'g:i A',
					'label' => '1:00 PM - 2:00 PM',
				],
			],
		];
	}

	/**
	 * Returns name of the nonce action to validate settings-related requests validity
	 * @return [type] [description]
	 */
	public function nonce_key() {
		return $this->key;
	}

	/**
	 * Reset excluded dates data
	 *
	 * @return [type] [description]
	 */
	public function reset_excluded_dates() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array(
				'message' => 'Access denied',
			) );
		}

		if ( empty( $_REQUEST['_nonce'] ) 
			|| ! wp_verify_nonce( $_REQUEST['_nonce'], $this->nonce_key() ) // phpcs:ignore
		) {
			wp_send_json_error( array(
				'message' => 'Page is expired. Please reload it and try again'
			) );
		}

		Plugin::instance()->db->excluded_dates->clear();

	}

	/**
	 * Save settings by ajax request
	 *
	 * @return [type] [description]
	 */
	public function ajax_save_settings() {
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array(
				'message' => 'Access denied',
			) );
		}

		if ( empty( $_REQUEST['_nonce'] ) 
			|| ! wp_verify_nonce( $_REQUEST['_nonce'], $this->nonce_key() ) // phpcs:ignore
		) {
			wp_send_json_error( array(
				'message' => 'Page is expired. Please reload it and try again'
			) );
		}

		$data                   = ! empty( $_REQUEST['settings'] ) ? json_decode( stripcslashes( $_REQUEST['settings'] ), true ) : []; // phpcs:ignore
		$settings               = wp_parse_args( $data, $this->defaults );
		$update_db_columns      = ! empty( $_REQUEST['update_db_columns'] ) ? $_REQUEST['update_db_columns'] : false; // phpcs:ignore
		$update_db_columns      = filter_var( $update_db_columns, FILTER_VALIDATE_BOOLEAN );
		$settings_before_update = $this->get_all();

		if ( empty( $settings ) ) {
			wp_send_json_error( array(
				'message' => 'Empty data',
			) );
		}

		foreach ( $settings as $setting => $value ) {
			if ( $this->setting_registered( $setting ) ) {

				switch ( $setting ) {

					case 'multi_booking':
						if ( 'slot' !== $settings['booking_type'] ) {
							$value = false;
						}

						break;

					case 'working_days':
					case 'days_off':
					case 're_booking':
						if ( ! is_array( $value ) ) {
							$value = false;
						}

						break;

					case 'working_days_mode':
						$value = ! empty( $value ) ? esc_attr( $value ) : 'override_full';
						break;

					case 'calendar_layout':
						$value = ! empty( $value ) ? esc_attr( $value ) : 'default';
						break;

					case 'confirm_action_message':
					case 'cancel_action_message':
						$value = wp_kses_post( $value );
						break;

					case 'buffer_before':
					case 'buffer_after':
					case 'locked_time':
					case 'default_slot':
					case 'min__duration':
					case 'max__duration':
						$value = intval( abs( ceil( $value ) ) );
						break;
					case 'min_slot_count':
					case 'max_slot_count':
						if( empty( $value )){
							$value = 1;
						}else{
							$value = intval( ceil( $value ) );
							$value = $value <= 0 ? 1 : $value ;
						}
						break;
					case 'wc_integration':
					case 'wc_synch_orders':
					case 'hide_setup':
					case 'manage_capacity':
					case 'allow_manage_count':
					case 'show_capacity_counter':
					case 'use_custom_labels':
					case 'scroll_to_details':
					case 'several_days':
					case 'only_start':	
					case 'switch_status':
					case 'show_timezones':
					case 'allow_confirm_links':
						$value = filter_var( $value, FILTER_VALIDATE_BOOLEAN );
						break;

					case 'db_columns':

						$old_columns = $this->get( 'db_columns' );

						if ( $update_db_columns ) {
							$value = $this->process_columns_diff( $value, $old_columns );
						} else {
							$value = $old_columns;
						}

						break;

					case 'cancel_deadline_limit':
					case 'confirm_deadline_limit':
						if ( 0 >= $value ) {
							$value = 1;
						}
		
						break;
					
					case 'capability_type':
						$value = ! empty( $value ) ? esc_attr( $value ) : 'manage_options';
						break;
					case 'use_calendar_timezone':
					case 'calendar_timezone':
						if ( $settings['show_timezones'] ) {
							$value = false;
						}
						break;

				}

				$this->update( $setting, $value, false );

			}
		}

		do_action( 'jet-apb/settings/after-ajax-save', $settings_before_update, $this );

		$this->write();

		wp_send_json_success( array(
			'message' => __( 'Settings saved!', 'jet-appointments-booking' ),
		) );

	}

	public function show_timezones() {
		
		$show_timezones = $this->get( 'show_timezones' );

		if ( ! $show_timezones ) {
			return false;
		}

		$booking_type = $this->get( 'booking_type' );

		if ( ! $booking_type || 'slot' === $booking_type ) {
			return true;
		} else {
			return false;
		}

	}

	/**
	 * The function processes data before localization. Added in version 1.2.0. Remove in version 1.5.0.
	 * @param  [type] $settings [description]
	 * @return [type]           [description]
	 */
	public function passe_settings( $settings ) {

		if ( empty( $settings ) ) {
			return $settings;
		}

		if ( isset( $settings['days_off'] ) && ! empty( $settings['days_off'] ) ) {
			$new_days_off = [];

			foreach ( $settings['days_off'] as $value ) {

				if( ! isset( $value["date"] ) ){
					$new_days_off[] = $value;
					continue;
				}

				$start          = $value["date"];
				$startTimeStamp = strtotime( $start );
				$new_days_off[] = [
					'start'          => $start,
					'startTimeStamp' => $startTimeStamp,
					'end'            => $start,
					'endTimeStamp'   => $startTimeStamp,
					'name'           => $value["name"],
					'type'           => 'days_off',
				];

			}

			$settings['days_off'] = $new_days_off;
		}

		return $settings;
	}

	/**
	 * Process columns difference and returns santizzed new columns list
	 * @param  [type] $new_columns [description]
	 * @param  [type] $old_columns [description]
	 * @return [type]              [description]
	 */
	public function process_columns_diff( $new_columns, $old_columns ) {

		$new_columns = $this->sanitize_columns( $new_columns );

		$to_delete = array_diff( $old_columns, $new_columns );
		$to_add    = array_diff( $new_columns, $old_columns );

		if ( ! empty( $to_delete ) ) {
			Plugin::instance()->db->appointments->delete_table_columns( $to_delete );
		}

		if ( ! empty( $to_add ) ) {
			Plugin::instance()->db->appointments->insert_table_columns( $to_add );
		}

		return $new_columns;

	}

	/**
	 * Sanitize SQL table columns list names
	 *
	 * @param  array  $columns [description]
	 * @return [type]          [description]
	 */
	public function sanitize_columns( $columns = [] ) {

		if ( empty( $columns ) ) {
			return [];
		}

		$sanitized = [];

		foreach ( array_filter( $columns ) as $column ) {
			$sanitized[] = $this->sanitize_column( $column );
		}

		return $sanitized;

	}

	/**
	 * Sanitize single DB column
	 * @param  [type] $column [description]
	 * @return [type]         [description]
	 */
	public function sanitize_column( $column ) {
		return sanitize_key( str_replace( ' ', '_', $column ) );
	}

	/**
	 * Return all settings and setup settings cache
	 *
	 * @return [type] [description]
	 */
	public function get_all() {
		if ( null === $this->settings ) {
			$this->settings = get_option( $this->key, [] );

			if( $this->settings && ! isset( $this->settings['buffer_before'] ) ){
				$this->settings['buffer_before'] = isset( $this->settings['default_buffer_before'] ) ? $this->settings['default_buffer_before'] : 0 ;
			}

			if( $this->settings && ! isset( $this->settings['buffer_after'] ) ){
				$this->settings['buffer_after'] = isset( $this->settings['default_buffer_after'] ) ? $this->settings['default_buffer_after'] : 0 ;
			}

			$this->settings = wp_parse_args( $this->settings, $this->defaults );
			$this->settings = $this->passe_settings( $this->settings );

			if ( empty( $this->settings['custom_labels'] ) ) {
				$this->settings['custom_labels'] = $this->defaults['custom_labels'];
			}

		}

		return $this->settings;
	}

	/**
	 * Get setting by name
	 *
	 * @param  [type] $setting [description]
	 * @return [type]          [description]
	 */
	public function get( $setting ) {
		$settings = $this->get_all();
		
		if ( isset( $settings[ $setting ] ) ) {

			if (
				isset( $this->defaults[ $setting ] ) 
				&& is_array( $this->defaults[ $setting ] ) 
				&& ! is_array( $settings[ $setting ] ) 
			) {
				return $this->defaults[ $setting ];
			} else {
				return $settings[ $setting ];
			}

		} else {
			return isset( $this->defaults[ $setting ] ) ? $this->defaults[ $setting ] : null;
		}

	}

	/**
	 * Update setting in cahce and database
	 *
	 * @param  [type]  $setting [description]
	 * @param  boolean $write   [description]
	 * @return [type]           [description]
	 */
	public function update( $setting = null, $value = null, $write = true ) {

		$this->get_all();

		/**
		 * Modify options before write into DB
		 */
		do_action( 'jet-apb/settings/before-update', $this->settings, $setting, $value );

		$this->settings[ $setting ] = $value;

		if ( $write ) {
			$this->write();
		}

	}

	/**
	 * Write settings cache
	 * @return [type] [description]
	 */
	public function write() {

		/**
		 * Modify options before write into DB
		 */
		do_action( 'jet-apb/settings/before-write', $this );

		update_option( $this->key, $this->settings, false );
	}

	/**
	 * Check if passed settings is registered in defaults
	 *
	 * @return [type] [description]
	 */
	public function setting_registered( $setting = null ) {
		return ( $setting && isset( $this->defaults[ $setting ] ) );
	}

	public function get_current_timezone() {
		
		$current_offset = get_option( 'gmt_offset' );
		$tzstring       = get_option( 'timezone_string' );

		if ( $tzstring ) {
			return $tzstring;
		} else {
			return $current_offset;
		}

	}

	public function get_custom_label( $setting, $default = '' ) {

		$use_custom_labels = $this->get( 'use_custom_labels' );
	
		if ( ! $use_custom_labels ) {
			return $default;
		}
	
		$custom_labels = $this->get( 'custom_labels' );
	
		return ! empty( $custom_labels[ $setting ] ) ? esc_html( $custom_labels[ $setting ] ) : $default;
	}

	public function providers_slot_duplicating() {
		
		$providers_slot_duplicating = $this->get( 'providers_slot_duplicating' ); 

		if ( $providers_slot_duplicating || $this->is_provider_custom_schedule() ) {
			return true;
		} else {
			return false;
		}

	}

	public function is_provider_custom_schedule() {

		$providers_cpt = Plugin::instance()->settings->get( 'providers_cpt' );

		if ( ! empty( $providers_cpt ) ) {

			global $wpdb;

			$posts    = $wpdb->posts;
			$postmeta = $wpdb->postmeta;

			$query = $wpdb->prepare( "
				SELECT posts.id
				FROM   $posts AS posts
					   INNER JOIN $postmeta AS postmeta
							   ON posts.id = postmeta.post_id
				WHERE  posts.post_type = %s
					   AND posts.post_status = 'publish'
					   AND postmeta.meta_key = 'jet_apb_post_meta'
					   AND postmeta.meta_value LIKE '%\"use_custom_schedule\";b:1;%';",
				$providers_cpt	
			 );

			if ( 0 < $wpdb->query( $query ) ) {
				return true;
			} else {
				return false;
			}

		} else {
			$this->update( 'providers_slot_duplicating', true, false );
		}
		
	}

	public function check_date_availability( $service, $provider, $date ) {

		$slots         = [];
		$time          = time() + current_datetime()->getOffset();
		$time         += Plugin::instance()->calendar->get_schedule_settings( $provider, $service, 0, 'locked_time' );
		$buffer_before = Plugin::instance()->calendar->get_schedule_settings( $provider, $service, 0, 'buffer_before' );
		$buffer_after  = Plugin::instance()->calendar->get_schedule_settings( $provider, $service, 0, 'buffer_after' );
		$duration      = Plugin::instance()->calendar->get_schedule_settings( $provider, $service, 0, 'default_slot' );
		$working_hours = Plugin::instance()->calendar->get_schedule_settings( $provider, $service, [], 'working_hours' );
		$working_days  = Plugin::instance()->calendar->get_schedule_settings( $provider, $service, [], 'working_days' );

		$working_hours = ( ! empty( $working_hours ) && is_array( $working_hours ) ) ? $working_hours : [];
		$working_days  = ( ! empty( $working_days ) && is_array( $working_days ) ) ? $working_days : [];

		$day_schedule  = Plugin::instance()->calendar->get_day_schedule( $date, $working_hours, $working_days );

		Time_Slots::set_starting_point( $date );

		if ( 0 < $time ) {
			Time_Slots::set_timenow( $time );
		}

		if ( 1 < count( $day_schedule ) ) {

			usort( $day_schedule, function( $a, $b ) {

				$a_from = strtotime( $a['from'] );
				$b_from = strtotime( $b['from'] );

				if ( $a_from === $b_from ) {
					return 0;
				}

				return ( $a_from < $b_from ) ? -1 : 1;

			} );
		}

		foreach ( $day_schedule as $day_part ) {
			$slots = $slots + Time_Slots::generate_intervals( array(
				'from'          => $day_part['from'],
				'to'            => $day_part['to'],
				'duration'      => $duration,
				'buffer_before' => $buffer_before,
				'buffer_after'  => $buffer_after,
				'from_now'      => true,
				'service'       => $service,
				'provider'      => $provider,
			) );
		}

		$args = array(
			'provider' => $provider,
			'date'     => $date,
		);

		$related_providers          = [];
		$use_providers_slot_duplicating = Plugin::instance()->settings->providers_slot_duplicating();

		// If the providers_slot_duplicating option is enabled, forming an array of all service providers
		if ( false === $use_providers_slot_duplicating ) {
			foreach ( Plugin::instance()->tools->get_providers_for_service( $service ) as $provider_obj ) {
				array_push( $related_providers, $provider_obj->ID );
			}
		}

		if ( $provider ) {
			if ( false === $use_providers_slot_duplicating ) {
				$query_args['provider'] = $related_providers;
			} else {
				$query_args['provider'] = $provider;
			}
		}
		
		if ( 'service' === Plugin::instance()->settings->get( 'check_by' ) ) {
			$args['service'] = $service;
		}

		$manage_capacity = Plugin::instance()->settings->get( 'manage_capacity' );

		if( $manage_capacity ) {
			$booked_slots  = Plugin::instance()->db->appointments->query_with_capacity( $args );
			$service_count = Plugin::instance()->tools->get_service_count( $service );
		} else {
			$booked_slots =  Plugin::instance()->db->appointments->query( $args );
		}

		$external = Calendar::get_external( $service, $provider, $date );
		$booked_slots = Calendar::merge_excluded( ( ( empty( $booked_slots ) || ! is_array( $booked_slots ) ) ? [] : $booked_slots ), $external, $service, $provider );

		foreach( $slots as $slot => $slot_data ) {

			$booked_slot_count = 0;

			foreach( $booked_slots as $booked_slot ) {
				if ( 
					( $booked_slot['slot'] < $slot_data['from'] && $slot_data['from'] < $booked_slot['slot_end'] )
					|| ( $booked_slot['slot'] < $slot_data['to'] && $slot_data['to'] < $booked_slot['slot_end'] )
					|| ( $slot_data['from'] < $booked_slot['slot'] && $booked_slot['slot'] < $slot_data['to'] )
					|| ( $slot_data['from'] < $booked_slot['slot_end'] && $booked_slot['slot_end'] < $slot_data['to'] )
					|| ( $booked_slot['slot'] == $slot_data['from'] && $slot_data['to'] == $booked_slot['slot_end'] )
				) {
						if( $manage_capacity ) {

							$booked_slot_count += $booked_slot['count'];

							if( $booked_slot_count >= $service_count ) {
								unset( $slots[$slot] );
							}

						} else {
							unset( $slots[$slot] );
						}
					}
			}
		}

		if( count( $slots ) > 0 ) {
			return true;
		} else {
			return false;
		}

	}

}
