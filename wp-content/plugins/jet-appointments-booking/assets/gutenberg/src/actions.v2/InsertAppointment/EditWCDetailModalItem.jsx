import { ExternalLink, Flex, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useContext } from '@wordpress/element';
import {
	Label,
	RowControl,
	RowControlEnd,
	WideLine,
	Help,
} from 'jet-form-builder-components';
import {
	ValidatedSelectControl,
	ValidatedTextControl,
} from 'jet-form-builder-actions';

const { RepeaterItemContext } = JetFBComponents;

const detailsTypes = [
	{
		value: 'service',
		label: __( 'Service name', 'jet-appointments-booking' ),
	},
	{
		value: 'provider',
		label: __( 'Provider name', 'jet-appointments-booking' ),
	},
	{
		value: 'date',
		label: __( 'Date', 'jet-appointments-booking' ),
	},
	{
		value: 'slot',
		label: __( 'Time slot start', 'jet-appointments-booking' ),
	},
	{
		value: 'slot_end',
		label: __( 'Time slot end', 'jet-appointments-booking' ),
	},
	{
		value: 'start_end_time',
		label: __( 'Full time slot', 'jet-appointments-booking' ),
	},
	{
		value: 'date_time',
		label: __( 'Full date and time', 'jet-appointments-booking' ),
	},
	{
		value: 'field',
		label: __( 'Form field', 'jet-appointments-booking' ),
	},
	{
		value: 'add_to_calendar',
		label: __( 'Add to Google calendar link', 'jet-appointments-booking' ),
	},
];

const HelpAboutFormatting = () => <Help>
	<ExternalLink
		href="https://codex.wordpress.org/Formatting_Date_and_Time">
		{ __( 'Formatting docs', 'jet-appointments-booking' ) }
	</ExternalLink>
</Help>;

function EditWCDetailModalItem( { formFields } ) {

	const {
		      currentItem,
		      changeCurrentItem,
	      } = useContext( RepeaterItemContext );

	return <Flex direction="column">
		<ValidatedSelectControl
			label={ __( 'Type', 'jet-appointments-booking' ) }
			value={ currentItem.type }
			onChange={ type => changeCurrentItem( { type } ) }
			options={ detailsTypes }
		/>
		<WideLine/>
		<ValidatedTextControl
			label={ __( 'Label', 'jet-appointments-booking' ) }
			value={ currentItem.label }
			onChange={ label => changeCurrentItem( { label } ) }
		/>
		{ [ 'date', 'date_time' ].includes( currentItem.type ) && <>
			<WideLine/>
			<RowControl>
				{ ( { id } ) => <>
					<Label htmlFor={ id }>
						{ __( 'Date format', 'jet-appointments-booking' ) }
					</Label>
					<RowControlEnd>
						<TextControl
							id={ id }
							value={ currentItem.date_format }
							onChange={ val => changeCurrentItem(
								{ date_format: val },
							) }
							__next40pxDefaultSize
							__nextHasNoMarginBottom
						/>
						<HelpAboutFormatting/>
					</RowControlEnd>
				</> }
			</RowControl>
		</> }
		{ [ 'slot', 'slot_end', 'start_end_time', 'date_time' ].includes(
			currentItem.type ) && <>
			<WideLine/>
			<RowControl>
				{ ( { id } ) => <>
					<Label htmlFor={ id }>
						{ __( 'Time format', 'jet-appointments-booking' ) }
					</Label>
					<RowControlEnd>
						<TextControl
							id={ id }
							value={ currentItem.time_format }
							onChange={ val => changeCurrentItem(
								{ time_format: val },
							) }
							__next40pxDefaultSize
							__nextHasNoMarginBottom
						/>
						<HelpAboutFormatting/>
					</RowControlEnd>
				</> }
			</RowControl>
		</> }
		{ 'field' === currentItem.type && <>
			<WideLine/>
			<ValidatedSelectControl
				label={ __( 'Select form field', 'jet-appointments-booking' ) }
				value={ currentItem.field }
				onChange={ field => changeCurrentItem( { field } ) }
				options={ formFields }
			/>
		</> }
		{ 'add_to_calendar' === currentItem.type && <>
			<WideLine/>
			<ValidatedTextControl
				label={ __( 'Link text', 'jet-appointments-booking' ) }
				value={ currentItem.link_label }
				onChange={ val => changeCurrentItem( { link_label: val } ) }
			/>
		</> }
	</Flex>;
}

export default EditWCDetailModalItem;