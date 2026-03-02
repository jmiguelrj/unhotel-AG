<?php
namespace JET_APB\Workflows\Actions;

use JET_APB\Workflows\Base_Object;
use JET_APB\Plugin;
use JET_APB\Resources\Appointment_Model;

abstract class Base_Action extends Base_Object {

	public $settings    = [];
	public $appointment = [];

	public function __construct() {
		add_action( 'jet-apb/workflows/action-controls', [ $this, 'register_action_controls' ] );
	}

	public function register_action_controls() {
	}

	public function setup( $settings = [], $appointment = null ) {
		$this->settings    = $settings;
		$this->appointment = $this->update_model( $appointment );
	}

	public function update_model( $appointments ) {
		$appointments = Plugin::instance()->tools->get_appointments( $appointments );
		if( ! empty( $appointments ) ) {
			$result = array();
			foreach ( $appointments as $appointment ) {
				$ID = $appointment->get( 'ID' );
				$result[] = new Appointment_Model( [], $ID );
			}
			return $result;
		}
	}

	public function get_settings( $setting = null ) {

		if ( ! $setting ) {
			return $this->settings;
		}

		return isset( $this->settings[ $setting ] ) ? $this->settings[ $setting ] : false;

	}

	public function update_settings( $setting, $value = null ) {
		$this->settings[ $setting ] = $value;
	}

	public function fetch_appointments_meta() {
		$appointments = Plugin::instance()->tools->get_appointments( $this->appointment );
		$this->appointment = Plugin::instance()->db->get_appointments_meta( $appointments );
	}

	public function parse_single_macros( $message_content = '', $appointment = [] ) {

		Plugin::instance()->macros->set_macros_object( $appointment );
		return Plugin::instance()->macros->do_macros( $message_content );

	}

	public function parse_macros( $string = '' ) {

		$appointments = Plugin::instance()->tools->get_appointments( $this->appointment );

		preg_match(
			'/\%(appointmens_list|appointments_list)\%([\s\S]*)\%(appointmens_list_end|appointments_list_end)\%/',
			$string,
			$appointments_list_matches
		);

		foreach ( $appointments as $app ) {
			$app->set( 'group_count', count( $appointments ) );
		}

		$appointments_list_content = isset( $appointments_list_matches[2] ) ? $appointments_list_matches[2] : '';

		if ( ! empty( $appointments_list_content ) ) {

			$appointments_list_output_content = '';

			foreach ( $appointments as $appointment ) {
				$appointments_list_output_content .= $this->parse_single_macros( $appointments_list_content, $appointment );
			}

			if ( ! empty( $appointments_list_output_content ) ) {
				$string = str_replace( $appointments_list_content, $appointments_list_output_content, $string );
			}

		}

		return $this->parse_single_macros( $string, $appointments[0] );

	}

	abstract public function do_action();

	/**
	 * Get first appointment from method get_appointments
	 * 
	 * @return object
	 */
	public function get_appointment() {
		return Plugin::instance()->tools->get_appointments( $this->appointment )[0];
	}

}
