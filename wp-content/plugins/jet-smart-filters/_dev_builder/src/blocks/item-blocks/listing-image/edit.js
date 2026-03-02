import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { useEffect, useState, Fragment } from '@wordpress/element';
import { PanelBody, SelectControl, TextControl, RangeControl, ToggleControl, TextareaControl } from '@wordpress/components';
import { useLocalizedData } from "services/data";
import { GroupedSelect, ImagePicker } from 'modules/UI';

export default function Edit({ attributes, setAttributes }) {
	const blockProps = useBlockProps({
		className: [
			'jsf-listing-image-block',
			attributes.className || ''
		].join(' ').trim(),
		draggable: false,
		onDragStart: (e) => e.preventDefault(),
	});

	const {
		mediaSourcesOptions,
		imageSizesOptions,
		imagePlaceholderUrl,
		linkSourcesOptions
	} = useLocalizedData();

	const [currentAttachmentData, setCurrentAttachmentData] = useState(null);
	const [previewImageUrl, setPreviewImageUrl] = useState(null);

	useEffect(() => {
		let imageUrl = imagePlaceholderUrl;

		if (currentAttachmentData) {
			if (currentAttachmentData?.sizes?.[attributes.image_size]?.url) {
				imageUrl = currentAttachmentData.sizes[attributes.image_size].url;
			} else if (currentAttachmentData.url) {
				imageUrl = currentAttachmentData.url;
			}
		} else if (attributes.image_fallback) {
			const attachment = wp.media.attachment(attributes.image_fallback);

			attachment.fetch().then(function () {
				setCurrentAttachmentData(attachment.attributes);
			});
		}

		setPreviewImageUrl(imageUrl);
	}, [currentAttachmentData, attributes.image_size]);

	return (
		<Fragment>
			<InspectorControls
				key={'inspector'}
			>
				<PanelBody
					title="General"
				>
					<GroupedSelect
						label="Source"
						value={attributes.image_source}
						options={mediaSourcesOptions}
						onChange={newValue => {
							setAttributes({ image_source: newValue });
						}}
					/>
					{'meta' === attributes.image_source &&
						<TextControl
							__next40pxDefaultSize
							__nextHasNoMarginBottom
							type="text"
							label="Meta Key"
							value={attributes.image_meta_key}
							onChange={newValue => {
								setAttributes({ image_meta_key: newValue });
							}}
						/>
					}
					{'options' === attributes.image_source &&
						<TextControl
							__next40pxDefaultSize
							__nextHasNoMarginBottom
							type="text"
							label="Option Name"
							value={attributes.image_option_name}
							onChange={newValue => {
								setAttributes({ image_option_name: newValue });
							}}
						/>
					}
					{['meta', 'options'].includes(attributes.image_source) &&
						<TextControl
							__next40pxDefaultSize
							__nextHasNoMarginBottom
							type="text"
							label="Image URL Prefix"
							help="Add prefix to the image URL. For example for the cases when source contains only part of the URL"
							value={attributes.image_url_prefix}
							onChange={newValue => {
								setAttributes({ image_url_prefix: newValue });
							}}
						/>
					}
					<ImagePicker
						label="Fallback image"
						value={attributes.image_fallback}
						onChange={newAttachmentData => {
							setCurrentAttachmentData(newAttachmentData);
							setAttributes({
								image_fallback: newAttachmentData?.id
									? newAttachmentData.id
									: newAttachmentData
							});
						}}
					/>
				</PanelBody>
				<PanelBody
					title="Settings"
				>
					{'user_avatar' === attributes.image_source ? (
						<RangeControl
							__next40pxDefaultSize
							__nextHasNoMarginBottom
							label="Image Size"
							value={attributes.avatar_size}
							min={10}
							max={500}
							onChange={newValue => {
								setAttributes({ avatar_size: newValue });
							}}
						/>
					) : (
						<SelectControl
							__next40pxDefaultSize
							__nextHasNoMarginBottom
							label="Image Size"
							value={attributes.image_size}
							options={imageSizesOptions}
							onChange={newValue => {
								setAttributes({ image_size: newValue });
							}}
						/>
					)}
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						type="text"
						label="Alt Text"
						value={attributes.custom_image_alt}
						onChange={newValue => {
							setAttributes({ custom_image_alt: newValue });
						}}
					/>
				</PanelBody>
				<PanelBody
					title="Image Link"
				>
					<ToggleControl
						__nextHasNoMarginBottom
						label="Is Linked"
						checked={attributes.image_is_linked}
						onChange={newValue => {
							setAttributes({ image_is_linked: newValue });
						}}
					/>
					{attributes.image_is_linked &&
						<>
							<GroupedSelect
								label='Link Source'
								value={attributes.link_source}
								options={linkSourcesOptions}
								onChange={newValue => {
									setAttributes({ link_source: newValue });
								}}
							/>
							{'attachment' === attributes.link_source &&
								<TextControl
									__next40pxDefaultSize
									__nextHasNoMarginBottom
									type="text"
									label="Link Attachment Key"
									value={attributes.link_attachment_key}
									onChange={newValue => {
										setAttributes({ link_attachment_key: newValue });
									}}
								/>
							}
							{'meta' === attributes.link_source &&
								<TextControl
									__next40pxDefaultSize
									__nextHasNoMarginBottom
									type="text"
									label="Link Meta Key"
									value={attributes.link_meta_key}
									onChange={newValue => {
										setAttributes({ link_meta_key: newValue });
									}}
								/>
							}
							{'options' === attributes.link_source &&
								<TextControl
									__next40pxDefaultSize
									__nextHasNoMarginBottom
									type="text"
									label="Link Option Name"
									value={attributes.link_option_name}
									onChange={newValue => {
										setAttributes({ link_option_name: newValue });
									}}
								/>
							}
						</>
					}
				</PanelBody>
			</InspectorControls>
			<div {...blockProps}>
				<div class="jsf-listing-image">
					<img
						className='jsf-listing-image__img wp-post-image'
						src={previewImageUrl}
						alt=""
					/>
				</div>
			</div>
		</Fragment>
	);
}