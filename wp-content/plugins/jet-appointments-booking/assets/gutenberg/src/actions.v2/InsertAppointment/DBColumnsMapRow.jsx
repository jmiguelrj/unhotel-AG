/* eslint-disable import/no-extraneous-dependencies */
import { __ } from '@wordpress/i18n';
import {
	Label,
	RowControl,
	RowControlEnd,
} from 'jet-form-builder-components';
import { useFields } from 'jet-form-builder-blocks-to-actions';
import { FieldsMapField } from 'jet-form-builder-actions';
import { useMemo } from '@wordpress/element';

function DBColumnsMapRow( { settings, onChangeSettingObj, source } ) {

	const formFields = useFields( {
		withInner: false,
		placeholder: '--',
	} );

	const dbFields = useMemo( () => {
		return source.columns.map( field => {
			return 'string' === typeof field
			       ? { value: field, label: field }
			       : field;
		} );
	}, [] );

	return <RowControl createId={ false }>
		<Label>
			{ __(
				'DB columns map',
				'jet-appointments-booking',
			) }
		</Label>
		<RowControlEnd gap={ 4 }>
			{ dbFields.map( ( field ) => <FieldsMapField
				key={ field.value }
				tag={ field.value }
				label={ field.label }
				isRequired={ field.required }
				formFields={ formFields }
				value={ settings[ `appointment_custom_field_${ field.value }` ] }
				onChange={ val => onChangeSettingObj( {
					[ `appointment_custom_field_${ field.value }` ]: val,
				} ) }
			/> ) }
		</RowControlEnd>
	</RowControl>;
}

export default DBColumnsMapRow;
