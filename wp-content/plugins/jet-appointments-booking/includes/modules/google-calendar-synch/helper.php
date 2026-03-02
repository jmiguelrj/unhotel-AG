<?php
namespace Crocoblock\Google_Calendar_Synch;

/**
 * Class contains static methods to help adjust the data
 * before sending it to the Google Calendar API to expected format.
 */
class Helper {

	/**
	 * Date string to Google calendar date prop
	 *
	 * @param string $datetime_string
	 * @return array
	 */
	public static function datetime_to_event_date( $datetime_string = '' ) {
		return self::timestamp_to_event_date( strtotime( $datetime_string ) );
	}

	/**
	 * Timestamp to Google calendar date prop
	 *
	 * @param string $datetime_string
	 * @return array
	 */
	public static function timestamp_to_event_date( $timestamp = '' ) {
		return [
			'dateTime' => date_i18n( 'Y-m-d\TH:i:sP', $timestamp ),
			'timeZone' => wp_timezone_string(),
		];
	}

	/**
	 * Google calendar date prop to timestamp
	 *
	 * @param array $event_date
	 * @return int|bool
	 */
	public static function event_date_to_timestamp( $event_date = [] ) {

		$timezone_string = get_option( 'timezone_string' );

		if ( $timezone_string ) {
			// Timezone is set via name like 'Europe/Kyiv'
			$datetime = new \DateTime( 'now', new \DateTimeZone( $timezone_string ) );
			$offset = $datetime->getOffset(); // in seconds
		} else {
			// Fallback to manual offset (e.g., UTC+2)
			$offset = get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;
		}

		if ( ! empty( $event_date['dateTime'] ) ) {
			$time = strtotime( $event_date['dateTime'] );
			$time = $time + $offset;

			return $time;
		}

		if ( ! empty( $event_date['date'] ) ) {
			$time = strtotime( $event_date['date'] );
			$time = $time + $offset;

			return $time;
		}

		return false;
	}
}
