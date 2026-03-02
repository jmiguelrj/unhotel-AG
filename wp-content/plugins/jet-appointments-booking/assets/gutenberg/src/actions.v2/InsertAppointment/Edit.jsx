import {
	Flex,
	FlexItem,
	Button,
} from '@wordpress/components';

import {
	WideLine,
	RowControl,
	Label,
	RowControlEnd,
	Help,
} from 'jet-form-builder-components';

import {
	ValidatedSelectControl,
	ValidatedTextControl,
} from 'jet-form-builder-actions';

import { __ } from '@wordpress/i18n';
import { useFields } from 'jet-form-builder-blocks-to-actions';
import { useState } from '@wordpress/element';

import WCFieldsMapRow from './WCFieldsMapRow.jsx';
import DBColumnsMapRow from './DBColumnsMapRow.jsx';
import EditWCDetailsModal from './EditWCDetailsModal';

function EditInsertAppointment( props ) {
	const {
		      settings,
		      onChangeSettingObj,
	      } = props;

	const formFields = useFields( { withInner: false } );

	const [ wcDetailsModal, setWcDetailsModal ] = useState( false );

	return <Flex direction="column">
		<ValidatedSelectControl
			label={ __(
				'Service ID field',
				'jet-appointments-booking',
			) }
			value={ settings.appointment_service_field }
			options={ [
				{ value: '', label: '--' },
				...formFields,
				{
					value: '_manual_input',
					label: __( 'Manual Input', 'jet-appointments-booking' ),
				},
			] }
			onChange={ val => onChangeSettingObj( {
				appointment_service_field: val,
			} ) }
			isErrorSupported={ ( { property } ) => (
				'appointment_service_field' === property
			) }
			required
		/>
		{ '_manual_input' === settings.appointment_service_field && <>
			<WideLine/>
			<ValidatedTextControl
				label={ __(
					'Manual input Service ID',
					'jet-appointments-booking',
				) }
				value={ settings.appointment_service_id }
				onChange={ val => onChangeSettingObj( {
					appointment_service_id: val,
				} ) }
				isErrorSupported={ ( { property } ) => (
					'appointment_service_id' === property
				) }
				required
			/>
		</> }
		{ Boolean( JetAppointmentActionData.has_provider ) && <>
			<WideLine/>
			<ValidatedSelectControl
				label={ __(
					'Provider ID field',
					'jet-appointments-booking',
				) }
				value={ settings.appointment_provider_field }
				options={ [
					{ value: '', label: '--' },
					...formFields,
					{
						value: '_manual_input',
						label: __( 'Manual Input', 'jet-appointments-booking' ),
					},
				] }
				onChange={ val => onChangeSettingObj( {
					appointment_provider_field: val,
				} ) }
				isErrorSupported={ ( { property } ) => (
					'appointment_provider_field' === property
				) }
				required
			/>
		</> }
		{ Boolean( JetAppointmentActionData.has_provider ) && '_manual_input' === settings.appointment_provider_field && <>
			<WideLine/>
			<ValidatedTextControl
				label={ __(
					'Manual input Provider ID',
					'jet-appointments-booking',
				) }
				value={ settings.appointment_provider_id }
				onChange={ val => onChangeSettingObj( {
					appointment_provider_id: val,
				} ) }
				isErrorSupported={ ( { property } ) => (
					'appointment_provider_id' === property
				) }
				required
			/>
		</> }
		<WideLine/>
		<ValidatedSelectControl
			label={ __(
				'Appointment date field',
				'jet-appointments-booking',
			) }
			value={ settings.appointment_date_field }
			options={ [
				{ value: '', label: '--' },
				...formFields,
			] }
			onChange={ val => onChangeSettingObj( {
				appointment_date_field: val,
			} ) }
			isErrorSupported={ ( { property } ) => (
				'appointment_date_field' === property
			) }
			required
		/>
		<WideLine/>
		<ValidatedSelectControl
			label={ __(
				'User e-mail field',
				'jet-appointments-booking',
			) }
			value={ settings.appointment_email_field }
			options={ [
				{ value: '', label: '--' },
				...formFields,
			] }
			onChange={ val => onChangeSettingObj( {
				appointment_email_field: val,
			} ) }
			isErrorSupported={ ( { property } ) => (
				'appointment_email_field' === property
			) }
			required
		/>
		<WideLine/>
		<ValidatedSelectControl
			label={ __(
				'User name field',
				'jet-appointments-booking',
			) }
			value={ settings.appointment_name_field }
			options={ [
				{
					value: '',
					label: '--',
				},
				{
					value: '_use_current_user',
					label: 'Use current user name / "Guest" for not logged-in users',
				},
				...formFields,
			] }
			onChange={ val => onChangeSettingObj( {
				appointment_name_field: val,
			} ) }
		/>
		{ Boolean( JetAppointmentActionData.columns?.length ) && <>
			<WideLine/>
			<DBColumnsMapRow { ...props } />
		</> }
		{ Boolean( JetAppointmentActionData.wc_integration ) && <>
			<WideLine/>
			<ValidatedSelectControl
				label={ __(
					'WooCommerce Price field',
					'jet-appointments-booking',
				) }
				help={ __(
					'Select field to get total price from. If not selectedprice will be get from post meta value.',
					'jet-appointments-booking',
				) }
				value={ settings.appointment_wc_price }
				onChange={ val => onChangeSettingObj( {
					appointment_wc_price: val,
				} ) }
				options={ [
					{ value: '', label: '--' },
					...formFields,
				] }
			/>
			<WideLine/>
			<RowControl createId={ false }>
				<Label>
					{ __(
						'WooCommerce order details',
						'jet-appointments-booking',
					) }
				</Label>
				<RowControlEnd>
					<FlexItem>
						<Button
							variant="secondary"
							onClick={ () => setWcDetailsModal( true ) }
						>
							{ __(
								'Set up',
								'jet-appointments-booking',
							) }
						</Button>
					</FlexItem>
					<Help>
						{ __(
							'Set up booking-related info you want to add to the WooCommerce orders and e-mails.',
							'jet-appointments-booking',
						) }
					</Help>
				</RowControlEnd>
			</RowControl>
			<WideLine/>
			<WCFieldsMapRow { ...props } />
		</> }
		{ wcDetailsModal && <EditWCDetailsModal
			setIsShow={ setWcDetailsModal }
		/> }
	</Flex>;
}

export default EditInsertAppointment;
