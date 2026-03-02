<?php
namespace JET_APB\Public_Actions\Actions;

use JET_APB\Plugin;

abstract class Base {
	
	public function __construct() {
		add_filter( 'jet-apb/public-actions/process/' . $this->action_id(), [ $this, 'set_action_template' ], 0, 2 );
		add_filter( 'jet-apb/public-actions/process/' . $this->action_id(), [ $this, 'do_action' ], 10, 2 );
		add_action( 'jet-apb/public-actions/print-styles/' . $this->action_id(), [ $this, 'action_css' ] );
		add_action( 'jet-apb/form-action/insert-appointment', [ $this, 'save_action_meta' ], 10, 2 );
		add_action( 'jet-apb/display-meta-fields', [ $this, 'show_action_meta' ], 10, 2 );
		add_filter( 'jet-apb/macros-list', [ $this, 'register_macros' ], 10, 2 );


	}

	abstract public function action_id();

	abstract public function do_action( $appointment = [], $manager = null );

	/**
	 * Check if appropriate actionc has a template and return ID of this template
	 * 
	 * @return [type] [description]
	 */
	public function get_action_template() {
		return false;
	}

	public function action_css() {
	}

	public function set_action_template( $appointment = [], $manager = null ) {
		$action_template = $this->get_action_template();

		if ( $action_template ) {
			if ( $this->is_valid_slot( $appointment, $this->action_id() ) ) {
				$manager->set_template( $action_template );
			} else {
				$manager->render_error_page();
			}
		}

		return $appointment;
	}

	public function register_macros( $macros, $manager ) {
		return $macros;
	}

	public function save_action_meta( $appointment, $action ) {
		foreach ( $this->action_meta() as $key => $data ) {
			$appointment->set_meta( [
				$key => call_user_func( $data['get_cb'], $appointment, $action ),
			] );
		}
	}

	public function show_action_meta( $fields = [] ) {

		foreach ( $this->action_meta() as $key => $data ) {
		
			$fields[ $key ] = [
				'label' => $data['label'],
				'cb'    => $data['show_cb'],
			];
			
		}

		return $fields;

	}

	public function action_meta() {
		return [];
	}

	public function is_valid_slot( $appointment, $type ) {

		if ( 'confirm' === $type ) {
			$check_status = ( ! empty( $appointment['status'] ) && 'completed' === $appointment['status'] ) ? true : false; 
		} elseif ( 'cancel' === $type ) {
			$check_status =  ( ! empty( $appointment['status'] ) && in_array( $appointment['status'], Plugin::instance()->statuses->invalid_statuses() ) ) ? true : false;
		}

		if ( $check_status ) {
			return false;
		}

		$limit = Plugin::instance()->settings->get( $type . '_deadline_limit' );
		$unit  = Plugin::instance()->settings->get( $type . '_deadline_unit' );
		$now   = date( 'U' );

		$compare_date = strtotime( "+{$limit}{$unit}", $now );

		$timezone_string = get_option( 'timezone_string' );

		if ( $timezone_string ) {
			// Timezone is set via name like 'Europe/Kyiv'
			$datetime = new \DateTime( 'now', new \DateTimeZone( $timezone_string ) );
			$offset = $datetime->getOffset(); // in seconds
		} else {
			// Fallback to manual offset (e.g., UTC+2)
			$offset = get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;
		}

		if ( $offset ) {
			$compare_date += $offset;
		}

		if (  $appointment['slot'] <= $compare_date ) {
			return false;
		} 

		return true;

	}

}
