import { Button } from '@wordpress/components';
import SortableList from '../SortableList';
import { CloneIcon, RemoveIcon } from 'modules/Icons';
import useRepeater from "../services/repeater";

const SortableRepeater = ({
	items,
	labelMask,
	buttonAddLabel = "Add Item",
	defaultItemData = {},
	onChange,
	children
}) => {
	const repeater = useRepeater(items, onChange, { setIDs: true, newItemIsOpen: true });

	const onDragStart = (active) => {
		const index = active?.data?.current?.sortable?.index;

		if (index === undefined)
			return;

		repeater.setOpenItem(index, false);
	};

	return (
		<div className="jsf-sortable-repeater">
			<SortableList
				items={items}
				onChange={onChange}
				onDragStart={onDragStart}
			>
				{(item, index) => (
					<SortableList.Item id={item.id}>
						<div className="jsf-sortable-repeater__item">
							<div className="jsf-sortable-repeater__item__header">
								<SortableList.DragHandle />
								<button
									className="jsf-sortable-repeater__item__header__label"
									onClick={() => repeater.setOpenItem(index)}
								>
									{item.label || repeater.getLabel(labelMask, item)}
								</button>
								<div className="jsf-sortable-repeater__item__header__controls">
									<button
										className="jsf-sortable-repeater__item__clone"
										onClick={() => repeater.cloneItem(index)}
									>
										<CloneIcon />
									</button>
									<button
										className="jsf-sortable-repeater__item__remove"
										onClick={() => repeater.removeItem(index)}
									>
										<RemoveIcon />
									</button>
								</div>
							</div>
							{item.isOpen && (
								<div className="jsf-sortable-repeater__item__content">
									{children(item, index)}
								</div>
							)}
						</div>
					</SortableList.Item>
				)}
			</SortableList>
			<Button
				className="jsf-sortable-repeater__button-add"
				variant="primary"
				onClick={() => repeater.addNewItem(defaultItemData)}
			>{buttonAddLabel}</Button>
		</div>
	);
};

export default SortableRepeater;