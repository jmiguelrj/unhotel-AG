import TemplateRender from '@/../../_dev/src/js/blocks/editor/controls/templateRender';

const {
	InspectorControls,
	InspectorAdvancedControls
} = wp.editor;

const {
	Fragment
} = wp.element;

const {
	PanelBody,
	SelectControl,
	TextControl
} = wp.components;

const listingsOptions = JetSmartFilterListingBlockData.listings_options;

listingsOptions.unshift({
	label: 'Select listing...',
	value: '',
});

export default function Edit({ attributes, setAttributes }) {

	return (
		<Fragment>
			<InspectorControls
				key={'inspector'}
			>
				<PanelBody
					title="General"
				>
					<SelectControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label="Listing"
						value={attributes.listing_id}
						options={listingsOptions}
						onChange={newValue => {
							setAttributes({ listing_id: newValue });
						}}
					/>
				</PanelBody>
			</InspectorControls>
			{/* Advanced Section */}
			<InspectorAdvancedControls>
				<TextControl
					label="CSS ID"
					value={attributes._element_id}
					onChange={(value) => setAttributes({ _element_id: value })}
				/>
			</InspectorAdvancedControls>
			<TemplateRender
				block="jsf-grid-block/listing"
				attributes={attributes}
			/>
		</Fragment>
	);
}