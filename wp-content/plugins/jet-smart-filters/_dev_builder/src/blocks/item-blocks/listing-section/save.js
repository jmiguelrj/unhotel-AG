import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';

const Save = (props) => {
	const { attributes } = props;
	const { justify, align_items, direction } = attributes;

	const customClass = props.className ? props.className : '';

	// Generate className matching the Edit component logic
	const blockProps = useBlockProps.save({
		className: `jsf-listing-section jsf-listing-section--justify-${justify || 'flex-start'} jsf-listing-section--align-${align_items || 'stretch'} jsf-listing-section--direction-${direction || 'row'} ${customClass}`,
	});

	return (
		<div {...blockProps}>
			<InnerBlocks.Content />
		</div>
	);
};

export default Save;