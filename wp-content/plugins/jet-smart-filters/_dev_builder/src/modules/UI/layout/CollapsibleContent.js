import { useState } from '@wordpress/element';
import { AngleRightIcon } from 'modules/Icons';

const CollapsibleContent = ({
	closeLabel = 'Show',
	openLabel = 'Hide',
	children
}) => {
	const [isOpen, setIsOpen] = useState(false);

	return (
		<div className={`jsf-collapsible-content ${isOpen ? 'jsf-opend' : ''}`}>
			<button
				className='jsf-collapsible-content__button'
				onClick={() => setIsOpen(!isOpen)}
			>
				<div className='jsf-collapsible-content__button__icon'>
					<AngleRightIcon />
				</div>
				<div className='jsf-collapsible-content__button__label'>
					{isOpen ? openLabel : closeLabel}
				</div>
			</button>
			{isOpen && <div className='jsf-collapsible-content__container'>{children}</div>}
		</div>
	);
};

export default CollapsibleContent;