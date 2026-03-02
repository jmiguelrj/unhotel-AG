import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps, InnerBlocks } from '@wordpress/block-editor';
import {
	PanelBody,
	__experimentalToggleGroupControl as ToggleGroupControl,
	__experimentalToggleGroupControlOptionIcon as ToggleGroupControlOptionIcon
} from '@wordpress/components';
import { Fragment } from '@wordpress/element';

import DirectionRowIcon from 'modules/Icons/DirectionRow';
import DirectionColumnIcon from 'modules/Icons/DirectionColumn';

import JustifyStartIcon from 'modules/Icons/JustifyStart';
import JustifyCenterIcon from 'modules/Icons/JustifyCenter';
import JustifyEndIcon from 'modules/Icons/JustifyEnd';
import JustifySpaceBetweenIcon from 'modules/Icons/JustifySpaceBetween';

import AlignTopIcon from 'modules/Icons/AlignTop';
import AlignMiddleIcon from 'modules/Icons/AlignMiddle';
import AlignBottomIcon from 'modules/Icons/AlignBottom';
import AlignStretchIcon from 'modules/Icons/AlignStretch';

const directionOptions = [
	{ icon: <DirectionRowIcon />, value: 'row' },
	{ icon: <DirectionColumnIcon />, value: 'column' },
];

const justifyOptions = [
	{ icon: <JustifyStartIcon />, value: 'flex-start', label: 'Start' },
	{ icon: <JustifyCenterIcon />, value: 'center', label: 'Center' },
	{ icon: <JustifyEndIcon />, value: 'flex-end', label: 'End' },
	{ icon: <JustifySpaceBetweenIcon />, value: 'space-between', label: 'Space Between' },
];

const alignOptions = [
	{ icon: <AlignStretchIcon />, value: 'stretch', label: 'Stretch' },
	{ icon: <AlignMiddleIcon />, value: 'center', label: 'Center' },
	{ icon: <AlignTopIcon />, value: 'flex-start', label: 'Start' },
	{ icon: <AlignBottomIcon />, value: 'flex-end', label: 'End' },
];

export default function Edit({ attributes, setAttributes }) {
	const { justify, align_items, direction } = attributes;

	const customClass = attributes.className ? attributes.className : '';

	// Updated className with prefix and container class
	const blockProps = useBlockProps({
		draggable: false,
		onDragStart: (e) => e.preventDefault(),
		className: `jsf-listing-section jsf-listing-section--justify-${justify || 'flex-start'} jsf-listing-section--align-${align_items || 'stretch'} jsf-listing-section--direction-${direction || 'row'} ${customClass}`,
	});

	return (
		<Fragment>
			<InspectorControls>
				<PanelBody title={__('Section Settings', 'jet-smart-filters')}>
					<ToggleGroupControl
						label={__('Content Direction', 'jet-smart-filters')}
						value={direction || 'row'}
						onChange={(val) => setAttributes({ direction: val })}
						isBlock
					>
						{directionOptions.map((option) => (
							<ToggleGroupControlOptionIcon
								value={option.value}
								label={option.label}
								icon={option.icon}
							/>
						))}
					</ToggleGroupControl>
					<ToggleGroupControl
						label={__('Justify Content', 'jet-smart-filters')}
						value={justify}
						onChange={(val) => setAttributes({ justify: val })}
						isBlock
					>
						{justifyOptions.map((option) => (
							<ToggleGroupControlOptionIcon
								value={option.value}
								label={option.label}
								icon={option.icon}
							/>
						))}
					</ToggleGroupControl>
					<ToggleGroupControl
						label={__('Align Items Inside Section', 'jet-smart-filters')}
						value={align_items}
						onChange={(val) => setAttributes({ align_items: val })}
						isBlock
					>
						{alignOptions.map((option) => (
							<ToggleGroupControlOptionIcon
								value={option.value}
								label={option.label}
								icon={option.icon}
							/>
						))}
					</ToggleGroupControl>
				</PanelBody>
			</InspectorControls>
			<div {...blockProps}>
				<InnerBlocks />
			</div>
		</Fragment>
	);
}