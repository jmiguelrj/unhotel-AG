import { Button } from '@wordpress/components';
import { CloneIcon, RemoveIcon } from 'modules/Icons';
import useRepeater from "../services/repeater";

const Repeater = ({
	items,
	labelMask,
	buttonAddLabel = "Add Item",
	defaultItemData = {},
	separator = false,
	onChange,
	children
}) => {
	const repeater = useRepeater(items, onChange, { newItemIsOpen: true });

	return (
		<div className="jsf-repeater">
			<div className="jsf-repeater__items">
				{items.map((item, index) => (
					<>
						{(separator && index !== 0) && (
							<div className="jsf-repeater__separator">{separator}</div>
						)}
						<div className="jsf-repeater__item">
							<div className="jsf-repeater__item__header">
								<button
									className="jsf-repeater__item__header__label"
									onClick={() => repeater.setOpenItem(index)}
								>
									{item.label || repeater.getLabel(labelMask, item)}
								</button>
								<div className="jsf-repeater__item__header__controls">
									<button
										className="jsf-repeater__item__clone"
										onClick={() => repeater.cloneItem(index)}
									>
										<CloneIcon />
									</button>
									<button
										className="jsf-repeater__item__remove"
										onClick={() => repeater.removeItem(index)}
									>
										<RemoveIcon />
									</button>
								</div>
							</div>
							{item.isOpen && (
								<div className="jsf-repeater__item__content">
									{children(item, index)}
								</div>
							)}
						</div>
					</>
				))}
			</div>
			<Button
				className="jsf-repeater__button-add"
				onClick={() => repeater.addNewItem(defaultItemData)}
			>{buttonAddLabel}</Button>
		</div>
	);
};

export default Repeater;