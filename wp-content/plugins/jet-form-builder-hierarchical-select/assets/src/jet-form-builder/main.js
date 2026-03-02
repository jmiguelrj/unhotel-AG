import * as block from './hr-select';
import getBlockSource from './functions';

const {
	      addFilter,
      } = wp.hooks;

const { getBlocksByName = () => [] } = JetFBActions;

const hrBlockName = 'jet-forms/hr-select';

/**
 * @deprecated 1.0.3
 */
!Boolean( JetFBHooks.useFields ) &&
addFilter( 'jet.fb.calculated.field.available.fields', 'jet-form-builder',
	fields => {
		const hrSelects     = getBlocksByName( [ hrBlockName ] );
		const hrSelectNames = [];

		for ( const hrSelect of hrSelects ) {
			hrSelectNames.push( `%FIELD::${ hrSelect.attributes.name }%` );

			if ( !hrSelect.attributes || !hrSelect.attributes.levels ) {
				continue;
			}
			for ( const level of hrSelect.attributes.levels ) {
				if ( !level.name ) {
					continue;
				}
				fields.push( `%FIELD::${ level.name }%` );
			}
		}

		fields = fields.filter( field => (
			!hrSelectNames.includes( field )
		) );

		return fields;
	} );

/**
 * @deprecated 1.0.3
 */
!Boolean( JetFBHooks.useFields ) &&
addFilter( 'jet.fb.getFormFieldsBlocks', 'jet-form-builder-hr-select',
	( fields, context = 'default' ) => {
		const hrSelects = getBlocksByName( [ hrBlockName ] );

		fields = fields.filter( field => (
			hrBlockName !== field.blockName
		) );

		for ( const hrSelect of hrSelects ) {

			if ( !hrSelect.attributes || !hrSelect.attributes.levels ) {
				continue;
			}
			for ( const level of hrSelect.attributes.levels ) {
				if ( !level.name ) {
					continue;
				}
				fields.push( {
					name: level.name,
					label: `(${ hrSelect.attributes.name }) ${ level.label ||
					level.name }`,
					value: level.name,
				} );

				if ( 'preset' === context ) {
					return fields;
				}
			}
		}

		return fields;
	} );

addFilter( 'jet.fb.register.fields', 'jet-form-builder-hr-select', blocks => {
	blocks.push( block );

	return blocks;
} );

addFilter( 'jet.fb.register.editProps', 'jet-from-builder-hr-select',
	editProps => {
		for ( const editProp of editProps ) {
			if ( 'source' === editProp.name ) {
				return editProps;
			}
		}
		editProps.push( {
			name: 'source',
			callable: getBlockSource,
		} );

		return editProps;
	} );

