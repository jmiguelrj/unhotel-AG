import { label as globalLabel, options } from '../source';

const { __ } = wp.i18n;

const label = {
	appointment_provider_field: __( 'Get Provider ID From:', 'jet-appointments-booking' ),
	appointment_provider_form_field: __( 'Select Provider Field:', 'jet-appointments-booking' ),
	appointment_provider_id: __( 'Set Provider ID:', 'jet-appointments-booking' ),
	slot_auto_check: __( 'Auto-Select Single Slot', 'jet-appointments-booking' ),
	...globalLabel
};

const help = {
	slot_auto_check_help: __( 'Skip the slot selection step when only one time slot is available.', 'jet-appointments-booking' ),
};


export {
	label,
	options,
	help,
};

