<?php
namespace JET_APB\Public_Actions;

use JET_APB\Plugin;

/**
 * Public actions manager
 */
class Tokens {
	
	public static $token_key = '_action_token';
	public static $timestamp = null;
	public static $index     = 0;

	public function get_appointment_by_token( $token = '' ) {
		
		$raw_meta = Plugin::instance()->db->appointments_meta->query( [
			'meta_key'   => self::$token_key,
			'meta_value' => $token,
		] );

		if ( empty( $raw_meta ) ) {
			return false;
		}

		$appointment_id = $raw_meta[0]['appointment_id'];

		return Plugin::instance()->db->get_appointment_by( 'ID', $appointment_id );

	}

	public function token_url( $url, $appointment = [] ) {
		return add_query_arg( [ self::$token_key => $this->get_token( $appointment ) ], $url );
	}

	public function get_token( $appointment = [] ) {

		if ( is_object( $appointment ) ) {
			$appointment = $appointment->to_array();
		}

		if ( ! empty( $appointment['meta'] ) ) {
			foreach ( $appointment['meta'] as $meta ) {
				parse_str( $meta, $meta_token );
				if ( ! empty( $meta_token[self::$token_key] ) ) {
					return $meta_token[self::$token_key];
				}
			}
		}
		
		$same_group_token = Plugin::instance()->settings->get( 'same_group_token' );

		if ( ! $same_group_token ) {
			self::$index ++;
		}

		if ( ! self::$timestamp ) {
			self::$timestamp = time();
		}

		if ( $same_group_token && $appointment['group_ID'] ) {
			$str = self::$timestamp . $appointment['user_email'] . $appointment['group_ID'];
		} else {
			$str = self::$timestamp . $appointment['provider'] . $appointment['service'] . $appointment['user_email'] . self::$index;
		}

		return md5( $str );

	}

}
