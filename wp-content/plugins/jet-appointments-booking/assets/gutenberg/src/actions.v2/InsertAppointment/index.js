import EditInsertAppointment from './Edit.jsx';
import { __ } from '@wordpress/i18n';
import { scheduled } from '@wordpress/icons';

export default {
	type: 'insert_appointment',
	label: __( 'Insert appointment', 'jet-appointments-booking' ),
	edit: EditInsertAppointment,
	docHref: 'https://crocoblock.com/knowledge-base/jetappointment/how-to-create-appointment-booking-forms/',
	category: 'content',
	icon: scheduled,
	validators: [
		( { settings } ) => {
			return settings?.appointment_service_field
			       ? false
			       : { property: 'appointment_service_field' };
		},
		( { settings } ) => {
			if ( '_manual_input' !== settings?.appointment_service_field ) {
				return false;
			}
			return settings?.appointment_service_id
			       ? false
			       : { property: 'appointment_service_id' };
		},
		( { settings } ) => {

			if ( ! Boolean( JetAppointmentActionData.has_provider ) ) {
				return false;
			}

			return settings?.appointment_provider_field
			       ? false
			       : { property: 'appointment_provider_field' };
		},
		( { settings } ) => {

			if (
				'_manual_input' !== settings?.appointment_provider_field
				|| ! Boolean( JetAppointmentActionData.has_provider )
			) {
				return false;
			}

			return settings?.appointment_provider_id
			       ? false
			       : { property: 'appointment_provider_id' };
		},
		( { settings } ) => {
			return settings?.appointment_date_field
			       ? false
			       : { property: 'appointment_date_field' };
		},
		( { settings } ) => {
			return settings?.appointment_email_field
			       ? false
			       : { property: 'appointment_email_field' };
		},
	],
};
