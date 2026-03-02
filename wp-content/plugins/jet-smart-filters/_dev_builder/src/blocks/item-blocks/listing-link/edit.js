import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { Fragment } from '@wordpress/element';
import { PanelBody, SelectControl, TextControl, ToggleControl, TextareaControl } from '@wordpress/components';
import { useLocalizedData } from "services/data";
import { GroupedSelect } from 'modules/UI';
import helper from "services/helper";

export default function Edit({ attributes, setAttributes }) {
	const blockProps = useBlockProps({
		className: [
			'jsf-listing-link-block',
			attributes.className || ''
		].join(' ').trim(),
		draggable: false,
		onDragStart: (e) => e.preventDefault(),
	});

	const {
		linkSourcesOptions,
		labelTypesOptions,
		fieldSourcesOptions,
		objectFieldsOptions,
		labelAriaTypesOptions,
		relAttributeTypesOptions
	} = useLocalizedData();

	const getListingLinkPreview = () => {
		if (attributes.link_fallback)
			return attributes.link_fallback;

		switch (attributes.label_type) {
			case 'static':
				if (attributes.label_text)
					return attributes.label_text;

				break;

			case 'dynamic':
				switch (attributes.label_source) {
					case 'object':
						const values = helper.findLabelsInGroupedOptionsByValue(objectFieldsOptions, attributes.label_object);

						if (values) {
							return 'Object: ' + `${values.groupLabel} -> ${values.optionLabel}`;
						}

						break;

					case 'meta':
						return 'Meta: ' + `${attributes.label_meta_key ? attributes.label_meta_key : 'key not set'}`;

					case 'option':
						return 'Option: ' + `${attributes.label_option_name ? attributes.label_option_name : 'name not set'}`;
				}

				break;
		}

		return 'Listing Link';
	};


	return (
		<Fragment>
			<InspectorControls
				key={'inspector'}
			>
				<PanelBody
					title="General"
				>
					<GroupedSelect
						label='Source'
						value={attributes.source}
						options={linkSourcesOptions}
						onChange={newValue => {
							setAttributes({ source: newValue });
						}}
					/>
					{'attachment' === attributes.source &&
						<TextControl
							__next40pxDefaultSize
							__nextHasNoMarginBottom
							type="text"
							label="Attachment Key"
							value={attributes.attachment_key}
							onChange={newValue => {
								setAttributes({ attachment_key: newValue });
							}}
						/>
					}
					{'meta' === attributes.source &&
						<TextControl
							__next40pxDefaultSize
							__nextHasNoMarginBottom
							type="text"
							label="Meta Key"
							value={attributes.meta_key}
							onChange={newValue => {
								setAttributes({ meta_key: newValue });
							}}
						/>
					}
					{'options' === attributes.source &&
						<TextControl
							__next40pxDefaultSize
							__nextHasNoMarginBottom
							type="text"
							label="Option Name"
							value={attributes.option_name}
							onChange={newValue => {
								setAttributes({ option_name: newValue });
							}}
						/>
					}
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						type="text"
						label="Fallback"
						value={attributes.link_fallback}
						onChange={newValue => {
							setAttributes({ link_fallback: newValue });
						}}
					/>
				</PanelBody>
				<PanelBody
					title="Label"
				>
					<SelectControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label='Label Type'
						value={attributes.label_type}
						options={labelTypesOptions}
						onChange={newValue => {
							setAttributes({ label_type: newValue });
						}}
					/>
					{'static' === attributes.label_type &&
						<TextControl
							__next40pxDefaultSize
							__nextHasNoMarginBottom
							type="text"
							label="Label Text"
							value={attributes.label_text}
							onChange={newValue => {
								setAttributes({ label_text: newValue });
							}}
						/>
					}
					{'dynamic' === attributes.label_type &&
						<>
							<SelectControl
								__next40pxDefaultSize
								__nextHasNoMarginBottom
								label="Label Source"
								value={attributes.label_source}
								options={fieldSourcesOptions}
								onChange={newValue => {
									setAttributes({ label_source: newValue });
								}}
							/>
							{'object' === attributes.label_source &&
								<GroupedSelect
									label="Label Object"
									value={attributes.label_object}
									options={objectFieldsOptions}
									onChange={newValue => {
										setAttributes({ label_object: newValue });
									}}
								/>
							}
							{'meta' === attributes.label_source &&
								<TextControl
									__next40pxDefaultSize
									__nextHasNoMarginBottom
									type="text"
									label="Label Meta Key"
									value={attributes.label_meta_key}
									onChange={newValue => {
										setAttributes({ label_meta_key: newValue });
									}}
								/>
							}
							{'option' === attributes.label_source &&
								<TextControl
									__next40pxDefaultSize
									__nextHasNoMarginBottom
									type="text"
									label="Label Option Name"
									value={attributes.label_option_name}
									onChange={newValue => {
										setAttributes({ label_option_name: newValue });
									}}
								/>
							}
						</>
					}
					<SelectControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label="Aria Label Type"
						value={attributes.label_aria_type}
						options={labelAriaTypesOptions}
						onChange={newValue => {
							setAttributes({ label_aria_type: newValue });
						}}
					/>
					{'custom' === attributes.label_aria_type &&
						<TextControl
							__next40pxDefaultSize
							__nextHasNoMarginBottom
							type="text"
							label="Aria Label Text"
							value={attributes.label_aria_text}
							onChange={newValue => {
								setAttributes({ label_aria_text: newValue });
							}}
						/>
					}
				</PanelBody>
				<PanelBody
					title="Settings"
				>
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						type="text"
						label="URL Prefix"
						value={attributes.url_prefix}
						onChange={newValue => {
							setAttributes({ url_prefix: newValue });
						}}
					/>
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						type="text"
						label="URL Anchor"
						help="Add anchor to the URL. Without #."
						value={attributes.url_anchor}
						onChange={newValue => {
							setAttributes({ url_anchor: newValue });
						}}
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label="Open in a New Window"
						checked={attributes.is_new_window}
						onChange={newValue => {
							setAttributes({ is_new_window: newValue });
						}}
					/>
					<SelectControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label="Add &quot;rel&quot; Attribute"
						value={attributes.rel_attribute_type}
						options={relAttributeTypesOptions}
						onChange={newValue => {
							setAttributes({ rel_attribute_type: newValue });
						}}
					/>
				</PanelBody>
			</InspectorControls>
			<div {...blockProps}>
				<a className='jsf-listing-link'>{getListingLinkPreview()}</a>
			</div>
		</Fragment>
	);
}