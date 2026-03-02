import { BaseControl, Button, Icon, Spinner } from '@wordpress/components';
import { useEffect, useRef, useState } from '@wordpress/element';
import { trash } from '@wordpress/icons';

const ImagePicker = ({
	value,
	onChange,
	label = '',
	description = '',
}) => {
	const frameRef = useRef(null);
	const [currentAttachment, setCurrentAttachment] = useState(null);
	const [previewUrl, setPreviewUrl] = useState(null);
	const [loading, setLoading] = useState(false);

	useEffect(() => {
		frameRef.current = wp.media({
			title: 'Select image',
			multiple: false,
			library: {
				type: 'image',
			},
			button: {
				text: 'Set image',
			},
		});

		frameRef.current.on('select', () => {
			const attachment = frameRef.current.state().get('selection').first().toJSON();

			updateAttachment(attachment);
			onChange(attachment);
		});
	}, []);

	useEffect(() => {
		if (!value)
			return;

		if (currentAttachment?.id && currentAttachment.id === value)
			return;

		setLoading(true);

		const mediaAttachment = wp.media.attachment(value);

		new Promise((resolve, reject) => {
			mediaAttachment.fetch({
				success: resolve,
				error: reject,
			});
		})
			.then(() => {
				updateAttachment(mediaAttachment.toJSON());
			})
			.catch(() => {
				updateAttachment(null);
			})
			.finally(() => {
				setLoading(false);
			});
	}, [value]);

	const openMediaFrame = () => {
		frameRef.current.open();

		if (value)
			frameRef.current.state().get('selection').add(wp.media.attachment(value));
	};

	const handleClear = (e) => {
		e.stopPropagation();

		onChange(null);
	};

	const updateAttachment = (newAttachment) => {
		setCurrentAttachment(newAttachment);
		setPreviewUrl(newAttachment?.sizes?.medium?.url || newAttachment?.sizes?.thumbnail?.url || newAttachment?.url || null);

		if (!newAttachment && !value)
			onChange(null);
	};

	return (
		<BaseControl
			__nextHasNoMarginBottom
			className="jsf-control-image-picker"
			label={label}
			help={description}
		>
			{!value ? (
				<Button
					className="jsf-control-image-picker__select-button"
					variant="secondary"
					onClick={openMediaFrame}
				>
					Select image
				</Button>
			) : (
				<div
					className="jsf-control-image-picker__image-preview"
					onClick={openMediaFrame}
				>
					{loading ? (
						<Spinner />
					) : (
						<>
							{previewUrl && (
								<img
									src={previewUrl}
									alt=""
								/>
							)}
							<Button
								className="jsf-control-image-picker__remove-button"
								icon={<Icon icon={trash} />}
								onClick={handleClear}
							/>
						</>
					)}
				</div>
			)}
		</BaseControl>
	);
};

export default ImagePicker;
