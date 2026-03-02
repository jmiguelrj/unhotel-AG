import * as provider from './appointment-provider';
import * as date from './appointment-date';
import { addFilter } from '@wordpress/hooks';

addFilter( 'jet.fb.register.fields', 'jet-form-builder', blocks => {
	blocks.push( provider, date );

	return blocks;
} );
