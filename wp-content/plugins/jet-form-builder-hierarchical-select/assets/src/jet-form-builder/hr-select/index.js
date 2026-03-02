import HrSelectEdit from './edit';
import metadata from './block.json';

const { __ } = wp.i18n;

const { name, icon } = metadata;

/**
 * Available items for `useEditProps`:
 *  - uniqKey
 *  - formFields
 *  - blockName
 *  - attrHelp
 */
const settings = {
	title: __( 'Hierarchical Select', 'jet-form-builder' ),
	description: __(
		`Display a Select field group in the form, where child terms are 
taken from a parent hierarchical taxonomy and shown as cascading dropdown lists.`,
		'jet-form-builder',
	),
	icon: <span dangerouslySetInnerHTML={ { __html: icon } }></span>,
	edit: HrSelectEdit,
	jfbGetFields: function ( context ) {
		const levels = this.attributes?.levels ?? [];
		const fields = [];

		for ( const level of levels ) {
			if ( !level.name ) {
				continue;
			}
			fields.push( {
				name: level.name,
				label: `(${ this.attributes.name }) ${ level.label ||
				level.name }`,
				value: level.name,
			} );

			if ( 'preset' === context ) {
				return fields;
			}
		}

		return fields;
	},
	useEditProps: [ 'uniqKey', 'blockName', 'attrHelp', 'source' ],
	example: {
		attributes: {
			isPreview: true,
		},
	},
};

export {
	metadata,
	name,
	settings,
};