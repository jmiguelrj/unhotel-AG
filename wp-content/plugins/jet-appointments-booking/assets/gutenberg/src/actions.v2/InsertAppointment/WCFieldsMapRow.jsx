/* eslint-disable import/no-extraneous-dependencies */
import { __ } from '@wordpress/i18n';
import {
	Label,
	RowControl,
	RowControlEnd,
	Help,
} from 'jet-form-builder-components';
import { useFields } from 'jet-form-builder-blocks-to-actions';
import { FieldsMapField } from 'jet-form-builder-actions';
import { useMemo } from '@wordpress/element';

function WCFieldsMapRow( { settings, onChangeSettingObj, source } ) {

	const formFields = useFields( {
		withInner: false,
		placeholder: '--',
	} );

	const wcFields = useMemo( () => {
		return source.wc_fields.map( field => {
			return 'string' === typeof field
			       ? { value: field, label: field }
			       : field;
		} );
	}, [] );

	return <RowControl createId={ false }>
		<Label>
			{ __(
				'WooCommerce checkout fields map',
				'jet-appointments-booking',
			) }
		</Label>
		<RowControlEnd gap={ 4 }>
			{ wcFields.map( ( field ) => <FieldsMapField
				key={ field.value }
				tag={ field.value }
				label={ field.label }
				isRequired={ field.required }
				formFields={ formFields }
				value={ settings[ `wc_fields_map__${ field.value }` ] }
				onChange={ val => onChangeSettingObj( {
					[ `wc_fields_map__${ field.value }` ]: val,
				} ) }
			/> ) }
		</RowControlEnd>
	</RowControl>;
}

export default WCFieldsMapRow;
