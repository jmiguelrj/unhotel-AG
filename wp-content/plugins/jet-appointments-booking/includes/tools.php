<?php

namespace JET_APB;

use JET_APB\Time_Slots;
use JET_APB\Resources\Appointment_Model;

/**
 * Tools class
 */
class Tools {

	/**
	 * Get services for provider ID
	 *
	 * @param integer $provider_id [description]
	 * @return array|bool
	 */
	public function get_services_for_provider ( $provider_id = 0 ) {

		$services_cpt = Plugin::instance()->settings->get( 'services_cpt' );
		$providers_cpt = Plugin::instance()->settings->get( 'providers_cpt' );

		if ( !$provider_id || !$services_cpt || !$providers_cpt ) {
			return false;
		}

		$services = jet_engine()->relations->get_related_posts( array( 'post_type_1' => $services_cpt, 'post_type_2' => $providers_cpt, 'post_id' => $provider_id, 'from' => $services_cpt, ) );

		return $services;
	}

	/**
	 * Get service capacity count
	 *
	 * @param  [type] $service [description]
	 * @return [type]          [description]
	 */
	public function get_service_count ( $service ) {
		$post_meta = get_post_meta( $service, 'jet_apb_post_meta', true );
		$capacity  = isset( $post_meta['meta_settings'] ) && ! empty( $post_meta['meta_settings']['_app_capacity'] ) ? $post_meta['meta_settings']['_app_capacity'] : get_post_meta( $service, '_app_capacity', true ) ;
		$capacity  = absint( $capacity );

		if ( !$capacity ) {
			$capacity = 1;
		}

		return $capacity;

	}

	/**
	 * Get providers list for passed services ID
	 *
	 * @param integer $service_id [description]
	 * @return [type]              [description]
	 */
	public function get_providers_for_service ( $service_id = 0 ) {

		$services_cpt = Plugin::instance()->settings->get( 'services_cpt' );
		$providers_cpt = Plugin::instance()->settings->get( 'providers_cpt' );

		if ( !$service_id || !$services_cpt || !$providers_cpt ) {
			return array();
		}

		$providers = jet_engine()->relations->get_related_posts( array( 'post_type_1' => $services_cpt, 'post_type_2' => $providers_cpt, 'post_id' => $service_id, 'from' => $providers_cpt, ) );

		$result = array();

		if ( !empty( $providers ) ) {

			global $wpdb;

			$query = implode( ', ', $providers );
			$table = $wpdb->posts;
			$result = $wpdb->get_results( "SELECT ID, post_title FROM $table WHERE `ID` IN ( $query );" );

		}

		if ( !is_array( $result ) ) {
			return array();
		} else {
			return $result;
		}

	}

	/**
	 * Returns services label
	 * @return [type] [description]
	 */
	public function get_services_label () {

		$services_cpt = Plugin::instance()->settings->get( 'services_cpt' );

		if ( !$services_cpt ) {
			return null;
		}

		$services = get_post_type_object( $services_cpt );

		if ( !$services ) {
			return null;
		}

		return $services->labels->singular_name;

	}

	/**
	 * Returns providers label
	 * @return [type] [description]
	 */
	public function get_providers_label () {

		$providers_cpt = Plugin::instance()->settings->get( 'providers_cpt' );

		if ( !$providers_cpt ) {
			return null;
		}

		$providers = get_post_type_object( $providers_cpt );

		if ( !$providers ) {
			return null;
		}

		return $providers->labels->singular_name;

	}
	/**
	 * Returns verbosed slot by timestatmp
	 * @return [type] [description]
	 */
	public function get_verbosed_date ( $date ) {
		$date_format = get_option( 'date_format', 'F j, Y' );

		return date_i18n( $date_format, $date );
	}

	/**
	 * Returns verbosed slot by timestatmp
	 * @return [type] [description]
	 */
	public function get_verbosed_slot ( $slot ) {
		$time_format = Plugin::instance()->settings->get( 'slot_time_format' );

		if ( !$time_format ) {
			$time_format = get_option( 'time_format', 'H:i' );
		}

		return date_i18n( $time_format, $slot );
	}

	public function get_posts ( $from = 'services', $args = [ 'posts_per_page' => -1 ] ) {

		$post_type = false;

		switch ( $from ) {
			case 'services':
				$post_type = Plugin::instance()->settings->get( 'services_cpt' );
				break;

			case 'providers':
				$post_type = Plugin::instance()->settings->get( 'providers_cpt' );
				break;
		}

		if ( !$post_type ) {
			return array();
		}

		$args = wp_parse_args( [ 'post_type' => $post_type ], $args );
		$posts = get_posts( $args );

		if ( empty( $posts ) ) {
			return array();
		} else {
			return wp_list_pluck( $posts, 'post_title', 'ID' );
		}

	}

	/**
	 * Returns path to template file.
	 *
	 * @return string|bool
	 */
	public function get_template ( $name = null ) {

		$template_path = apply_filters( 'jet-apb/tools/template-path', 'jet-appointments-booking' );
		$template = locate_template( $template_path . $name );

		if ( !$template ) {
			$template = JET_APB_PATH . 'templates/public/' . $name;
		}

		if ( file_exists( $template ) ) {
			return $template;
		} else {
			return false;
		}
	}

	public function secondsToTime ( $second = 0, $format = 'H:i:s' ) {
		$result = date( $format, $second );

		return $result;
	}

	public static function get_time_settings( $service = null, $provider = null, $key = 'booking_type', $default_value  = '' ){
		$value         = null;
		$post_meta     = get_post_meta( $provider, 'jet_apb_post_meta', true );
		$general_value = Plugin::instance()->settings->get( $key );
		$general_value = $general_value ? $general_value : $default_value;

		if( ! isset( $post_meta[ 'custom_schedule' ] ) || ! $post_meta[ 'custom_schedule' ][ 'use_custom_schedule' ] ){
			$post_meta = get_post_meta( $service, 'jet_apb_post_meta', true );
		}

		if( ! isset( $post_meta[ 'custom_schedule' ] ) || ! $post_meta[ 'custom_schedule' ][ 'use_custom_schedule' ] ){
			$value =  $general_value;
		}else{
			if ( isset( $post_meta[ 'custom_schedule' ][ $key ] ) ){
				$value = $post_meta[ 'custom_schedule' ][ $key ];
				$value = NULL !== $value ? $value : $general_value ;
			}
		}

		if( NULL === $value && $default_value ){
			$value = $default_value;
		}

		return $value;
	}

	public static function date_format_php_to_momentjs( $format = null, $mask = [] ){

		if( ! $format ){
			return '';
		}

		$parsed_format = $format;
		$mask = ! empty( $mask ) ? $mask : [
			'/H{1}/' => 'HH',
			'/h{1}/' => 'hh',
			'/Y{1}/' => 'YYYY',
			'/y{1}/' => 'YY',
			'/M{1}/' => 'MMM',
			'/n{1}/' => 'M',
			'/m{1}/' => 'MM',
			'/F{1}/' => 'MMMM',
			'/d{1}/' => 'DD',
			'/D{1}/' => 'ddd',
			'/j{1}/' => 'D',
			'/l{1}/' => 'dddd',
			'/i{1}/' => 'mm',
			'/g{1}/' => 'hh',
		];

		foreach ( $mask as $key => $value ) {
			$parsed_format = preg_replace( $key, $value, $parsed_format );
		}

		return $parsed_format;
	}

	/**
	 * Compare two arrays recursively and return the differences
	 *
	 * @param array $array1 The first array to compare.
	 * @param array $array2 The second array to compare.
	 * @return array An array containing the differences between the two arrays.
	 */
	public static function array_diff_assoc_recursive( $array1, $array2 ) {
		$diff = [];

		foreach ( $array1 as $key => $value ) {
			if ( ! array_key_exists( $key, $array2 ) ) {
				$diff[$key] = $value;
			} elseif ( is_array( $value ) && is_array( $array2[$key] ) ) {
				$nested_diff = self::array_diff_assoc_recursive( $value, $array2[$key] );
				if ( ! empty( $nested_diff ) ) {
					$diff[$key] = $nested_diff;
				}
			} elseif ( $value !== $array2[$key] ) {
				$diff[$key] = $value;
			}
		}

		return $diff;
	}

	/**
	 * Returns time slots
	 *
	 * @return [type] [description]
	 */
	public function get_time_slots( $plain = true ) {

		return Time_Slots::prepare_slots_for_js(
			Time_Slots::generate_slots(
				array(
					'from'     => 30 * MINUTE_IN_SECONDS,
					'to'       => DAY_IN_SECONDS / 2,
					'interval' => 15 * MINUTE_IN_SECONDS,
					'format'   => 'U',
				)
			),
			'G\h i\m\i\n',
			$plain,
			true
		);

	}

	/**
	 * Returns time slots
	 *
	 * @return [type] [description]
	 */
	public function get_interval_time_slots( $plain = true ) {

		return Time_Slots::prepare_slots_for_js(
			Time_Slots::generate_slots(
				array(
					'to'       => DAY_IN_SECONDS / 2,
					'interval' => 15 * MINUTE_IN_SECONDS,
					'format'   => 'U',
				)
			),
			'G\h i\m\i\n',
			$plain,
			true
		);
	}

	/**
	 * Get Appointment_Model object from the input data
	 *
	 * @param mixed  $data
	 * 
	 * @return array
	 */
	public function get_appointments( $data ) {
		
		$appointments = array();

		if ( is_array( $data ) ) {
			if( ! empty( $data['ID'] ) ) {
				$appointments[] = new Appointment_Model( $data );
			} else {
				foreach( $data as $appointment ) {
					if( ( ! is_object( $appointment ) ) ) {
						$appointments[] = new Appointment_Model( $appointment );
					} else {
						$appointments[] = $appointment;
					}	
				}
			}
		} elseif ( is_integer( $data ) ) {
			$appointments[] = new Appointment_Model( [], $data );
		} elseif ( is_object( $data ) ) {
			$appointments[] = $data;
		}

		return $appointments;
	}

	/**
	 * Recursively sanitizes an array or a string input.
	 *
	 * @param mixed    $input    The input to be sanitized. Can be an array or a string.
	 * @param callable $callback Optional. A user-defined callback function for custom sanitization of string values.
	 *
	 * @return mixed The sanitized array or string.
	 */
	public function sanitize_array_recursively( $input, $callback = null ) {

		if ( is_array( $input ) ) {
			foreach ( $input as $key => $value ) {
				$input[ $key ] = self::sanitize_array_recursively( $value );
			}
		} elseif ( is_string( $input ) ) {
			if ( is_callable( $callback ) ) {
				$input = call_user_func( $callback, $input );
			} elseif ( function_exists( 'sanitize_text_field' ) ) {
				// Fallback to sanitize_text_field if no callback provided
				// This is useful for sanitizing strings in WordPress context
				$input = sanitize_text_field( $input );
			}
		}

		return $input;
	}

}
