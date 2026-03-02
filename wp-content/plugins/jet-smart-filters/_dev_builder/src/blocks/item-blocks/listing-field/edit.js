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
			'jsf-listing-field-block',
			attributes.className || ''
		].join(' ').trim(),
		draggable: false,
		onDragStart: (e) => e.preventDefault(),
	});

	const {
		fieldSourcesOptions,
		objectFieldsOptions,
		filterCallbacksOptions
	} = useLocalizedData();

	const TagName = attributes.tag || 'div';

	const getListingFieldPreview = () => {
		if (attributes.fallback)
			return attributes.fallback;

		switch (attributes.source) {
			case 'object':
				const values = helper.findLabelsInGroupedOptionsByValue(objectFieldsOptions, attributes.object);

				if (values) {
					return 'Object: ' + `${values.groupLabel} -> ${values.optionLabel}`;
				}

				break;

			case 'meta':
				return 'Meta: ' + `${attributes.meta_key ? attributes.meta_key : 'key not set'}`;

			case 'option':
				return 'Option: ' + `${attributes.option_name ? attributes.option_name : 'name not set'}`;

		}

		return 'Listing Field';
	};

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
						label="Source"
						value={attributes.source}
						options={fieldSourcesOptions}
						onChange={newValue => {
							setAttributes({ source: newValue });
						}}
					/>
					{'object' === attributes.source &&
						<GroupedSelect
							label="Post Object"
							value={attributes.object}
							options={objectFieldsOptions}
							onChange={newValue => {
								setAttributes({ object: newValue });
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
					{'option' === attributes.source &&
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
						value={attributes.fallback}
						onChange={newValue => {
							setAttributes({ fallback: newValue });
						}}
					/>
				</PanelBody>
				<PanelBody
					title="Settings"
				>
					<SelectControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label="Field tag"
						value={attributes.tag}
						options={[
							{
								value: 'div',
								label: 'DIV',
							},
							{
								value: 'h1',
								label: 'H1',
							},
							{
								value: 'h2',
								label: 'H2',
							},
							{
								value: 'h3',
								label: 'H3',
							},
							{
								value: 'h4',
								label: 'H4',
							},
							{
								value: 'h5',
								label: 'H5',
							},
							{
								value: 'h6',
								label: 'H6',
							},
							{
								value: 'p',
								label: 'P',
							},
							{
								value: 'span',
								label: 'SPAN',
							},
						]}
						onChange={newValue => {
							setAttributes({ tag: newValue });
						}}
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label="Filter Value Before Output"
						checked={attributes.use_filter}
						onChange={newValue => {
							setAttributes({ use_filter: newValue });
						}}
					/>
					{attributes.use_filter &&
						<>
							<SelectControl
								__next40pxDefaultSize
								__nextHasNoMarginBottom
								label="Filter Callback"
								value={attributes.filter_callback}
								options={filterCallbacksOptions}
								onChange={newValue => {
									setAttributes({ filter_callback: newValue });
								}}
							/>
							{'date' === attributes.filter_callback &&
								<TextControl
									__next40pxDefaultSize
									__nextHasNoMarginBottom
									type="text"
									label="Date Format"
									value={attributes.date_format}
									onChange={newValue => {
										setAttributes({ date_format: newValue });
									}}
								/>
							}
							{'number_format' === attributes.filter_callback &&
								<>
									<TextControl
										__next40pxDefaultSize
										__nextHasNoMarginBottom
										type="text"
										label="Thousands Separator"
										value={attributes.thousands_separator}
										onChange={newValue => {
											setAttributes({ thousands_separator: newValue });
										}}
									/>
									<TextControl
										__next40pxDefaultSize
										__nextHasNoMarginBottom
										type="number"
										min="0"
										max="5"
										label="Decimals"
										value={attributes.decimal_count}
										onChange={newValue => {
											setAttributes({ decimal_count: Number(newValue) });
										}}
									/>
									<TextControl
										__next40pxDefaultSize
										__nextHasNoMarginBottom
										type="text"
										label="Decimal Separator"
										value={attributes.decimal_point}
										onChange={newValue => {
											setAttributes({ decimal_point: newValue });
										}}
									/>
								</>
							}
						</>
					}
					<ToggleControl
						__nextHasNoMarginBottom
						label="Customize Output"
						checked={attributes.use_custom_format}
						onChange={newValue => {
							setAttributes({ use_custom_format: newValue });
						}}
					/>
					{attributes.use_custom_format &&
						<TextareaControl
							__nextHasNoMarginBottom
							type="text"
							label="Customize Output Format"
							help="%s will be replaced with field value. If you need use plain % sign, replace it with %%"
							value={attributes.custom_format}
							onChange={newValue => {
								setAttributes({ custom_format: newValue });
							}}
						/>
					}
				</PanelBody>
			</InspectorControls>
			<TagName {...blockProps}>
				<div className='jsf-listing-field'>{getListingFieldPreview()}</div>
			</TagName>
		</Fragment>
	);
}