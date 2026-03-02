<?php
namespace JET_APB\Rest_API;

use JET_APB\Plugin;
use JET_APB\Time_Slots;
use JET_APB\Resources\Appointment_Model;

class Endpoint_Update_Appointment extends \Jet_Engine_Base_API_Endpoint {

	/**
	 * Returns route name
	 *
	 * @return string
	 */
	public function get_name() {
		return 'update-appointment';
	}

	/**
	 * API callback
	 *
	 * @return void
	 */
	public function callback( $request ) {

		$params      = $request->get_params();
		$item_id     = ! empty( $params['id'] ) ? absint( $params['id'] ) : 0;
		$item        = ! empty( $params['item'] ) ? $params['item'] : array();
		$not_allowed = array(
			'order_id',
			'user_id',
			'ID',
			'group_ID',
			'date_timestamp',
			'slot_timestamp',
			'slot_end_timestamp',
			'isGroupChief',
		);

		foreach ( $item as $key => $value ) {
			$value = in_array( $key, [ 'date', 'slot', 'slot_end' ] ) ? $item[ $key . '_timestamp' ] : $value ;
			$item[ $key ] = ! empty( $value ) ? esc_attr( $value ) : '';
		}

		foreach ( $not_allowed as $key  ) {
			if ( isset( $item[ $key ] ) ) {
				unset( $item[ $key ] );
			}
		}

		if ( empty( $item ) ) {
			return rest_ensure_response( array(
				'success' => false,
				'data'    => __( 'No data to update', 'jet-appointments-booking' ),
			) );
		}

		$initial_model = new Appointment_Model([], $params['id']);
		$app_model     = new Appointment_Model( $item, $item_id );

		do_action( 
			'jet-apb/db/before-update/appointments', 
			[ 
				'initial' => $initial_model, 
				'updated' => $app_model 
			],  
			$this 
		);

		if (
			Plugin::instance()->settings->get( 'show_timezones' )
			&& ! empty( $app_model->get_meta('user_timezone') )
		) {

			$app_local_zone = $app_model->get_meta('user_timezone');

			$args = [
				'user_local_time' => $this->get_local_date( $app_model->get('slot'), $app_local_zone, Plugin::instance()->settings->get( 'slot_time_format' ) ) . "-" . $this->get_local_date( $app_model->get('slot_end'), $app_local_zone, Plugin::instance()->settings->get( 'slot_time_format' ) ),
				'user_local_date' => $this->get_local_date( $app_model->get('slot'), $app_local_zone, 'F d, Y' ),
			];

			foreach ( $args as $meta_key => $meta_value ) {
				$app_model->set_meta( $meta_key, $meta_value );
			}

		}
		$app_model->save();

		return rest_ensure_response( array(
			'success' => true,
		) );

	}

	/**
	 * Check user access to current end-popint
	 *
	 * @return bool
	 */
	public function permission_callback( $request ) {
		return Plugin::instance()->current_user_can( $this->get_name() );
	}

	/**
	 * Returns endpoint request method - GET/POST/PUT/DELTE
	 *
	 * @return string
	 */
	public function get_method() {
		return 'POST';
	}

	/**
	 * Get query param. Regex with query parameters
	 *
	 * @return string
	 */
	public function get_query_params() {
		return '(?P<id>[\d]+)';
	}

	public function get_local_date( $timestamp, $timezone, $format ) {

		$timezone               = str_replace( 'UTC', '', $timezone );
		$strdate                = date( 'Y-m-d H:i:s', $timestamp );
		$date_with_current_zone = date_create( $strdate, wp_timezone() );

		return wp_date( $format, $date_with_current_zone->format('U'), timezone_open( $timezone ) );

	}

}