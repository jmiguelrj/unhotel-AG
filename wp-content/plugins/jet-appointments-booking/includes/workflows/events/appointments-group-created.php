<?php
namespace JET_APB\Workflows\Events;

class Appointments_Group_Created extends Base_Event {

	/**
	 * Object ID
	 * @return [type] [description]
	 */
	public function get_id() {
		return 'appointments-group-created';
	}

	/**
	 * Object name
	 *
	 * @return [type] [description]
	 */
	public function get_name() {
		return __( 'Appointments Group Created (Multi Booking)', 'jet-appointments-booking' );
	}

	public function hook() {
		return 'jet-apb/form-action/insert-appointments-group';
	}

}
