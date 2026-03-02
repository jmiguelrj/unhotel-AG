import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { useEffect, useState, Fragment } from '@wordpress/element';
import { PanelBody, SelectControl, TextControl, ToggleControl, TextareaControl } from '@wordpress/components';
import { useLocalizedData } from "services/data";
import { GroupedSelect } from 'modules/UI';

export default function Edit({ attributes, setAttributes }) {
	const blockProps = useBlockProps({
		className: [
			'jsf-listing-terms-block',
			attributes.className || ''
		].join(' ').trim(),
		draggable: false,
		onDragStart: (e) => e.preventDefault(),
	});

	const {
		groupedTaxonomiesOptions,
		termsOrderByOptions,
		termsOrderOptions
	} = useLocalizedData();

	const ItemTagName = attributes.terms_linked
		? 'a'
		: 'div';

	const [items, setItems] = useState([]);

	useEffect(() => {
		const itemExamples = ['Item 1', 'Item 2', 'Item 3'];
		const termsNum = attributes.show_all_terms
			? false
			: attributes.terms_num;

		const items = termsNum
			? itemExamples.slice(0, termsNum)
			: itemExamples;

		setItems(items);
	}, [attributes.show_all_terms, attributes.terms_num]);

	return (
		<Fragment>
			<InspectorControls
				key={'inspector'}
			>
				<PanelBody
					title="Terms List"
				>
					<GroupedSelect
						label='From Taxonomy'
						value={attributes.from_tax}
						options={groupedTaxonomiesOptions}
						onChange={newValue => {
							setAttributes({ from_tax: newValue });
						}}
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label="Show All Terms"
						checked={attributes.show_all_terms}
						onChange={newValue => {
							setAttributes({ show_all_terms: newValue });
						}}
					/>
					{!attributes.show_all_terms &&
						<TextControl
							__next40pxDefaultSize
							__nextHasNoMarginBottom
							type="number"
							min="1"
							max="20"
							label="Terms number to show"
							value={attributes.terms_num}
							onChange={newValue => {
								setAttributes({ terms_num: Number(newValue) });
							}}
						/>
					}
					<SelectControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label="Order By"
						options={termsOrderByOptions}
						value={attributes.order_by}
						onChange={newValue => {
							setAttributes({ order_by: newValue });
						}}
					/>
					<SelectControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label="Order"
						options={termsOrderOptions}
						value={attributes.order}
						onChange={newValue => {
							setAttributes({ order: newValue });
						}}
					/>
				</PanelBody>
				<PanelBody
					title="Display Settings"
				>
					<ToggleControl
						__nextHasNoMarginBottom
						label="Linked Terms"
						checked={attributes.terms_linked}
						onChange={newValue => {
							setAttributes({ terms_linked: newValue });
						}}
					/>
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						type="text"
						label="Delimiter"
						value={attributes.terms_delimiter}
						onChange={newValue => {
							setAttributes({ terms_delimiter: newValue });
						}}
					/>
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						type="text"
						label="Text Before Terms List"
						value={attributes.terms_prefix}
						onChange={newValue => {
							setAttributes({ terms_prefix: newValue });
						}}
					/>
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						type="text"
						label="Text After Terms List"
						value={attributes.terms_suffix}
						onChange={newValue => {
							setAttributes({ terms_suffix: newValue });
						}}
					/>
				</PanelBody>
			</InspectorControls>
			<div {...blockProps}>
				<div class="jsf-listing-terms">
					{attributes.terms_prefix &&
						<div class="jsf-listing-terms-prefix">{attributes.terms_prefix}</div>
					}
					{items.map((item, index) => (
						<React.Fragment key={index}>
							<ItemTagName className="jsf-listing-terms-item">{item}</ItemTagName>
							{attributes.terms_delimiter && index < items.length - 1 && (
								<div className="jsf-listing-terms-delimiter">{attributes.terms_delimiter}</div>
							)}
						</React.Fragment>
					))}
					{attributes.terms_suffix &&
						<div class="jsf-listing-terms-suffix">{attributes.terms_suffix}</div>
					}
				</div>
			</div>
		</Fragment>
	);
}