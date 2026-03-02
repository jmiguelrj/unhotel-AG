import { Button } from '@wordpress/components';
import { Repeater } from 'modules/UI';
import useRepeater from "../services/repeater";

const NestedRepeater = ({
	items,
	defaultItemData = {},
	externalSeparator = false,
	externalButtonAddLabel = 'Add Item',
	internalLabelMask = '',
	internalSeparator = false,
	internalButtonAddLabel = 'Add Item',
	onChange,
	children
}) => {
	const externalRepeater = useRepeater(items, onChange);

	const internalOnChange = (newInternalValue, externalIndex) => {
		const newItems = [...items];

		if (newInternalValue.length) {
			newItems[externalIndex] = newInternalValue;
		} else {
			newItems.splice(externalIndex, 1);
		}

		onChange(newItems);
	};

	return (
		<div className="jsf-repeater-nested">
			<div className="jsf-repeater-nested__external">
				<div className="jsf-repeater-nested__external__items">
					{items.map((internalItems, externalIndex) => (
						<>
							{(externalSeparator && externalIndex !== 0) && (
								<div className="jsf-repeater-nested__external__separator">{externalSeparator}</div>
							)}
							<div className="jsf-repeater-nested__internal">
								<Repeater
									items={internalItems}
									defaultItemData={defaultItemData}
									labelMask={internalLabelMask}
									separator={internalSeparator}
									buttonAddLabel={internalButtonAddLabel}
									onChange={(newInternalValue) => internalOnChange(newInternalValue, externalIndex)}
								>
									{(item, index) => {
										const internalIndex = index;
										const internalItem = item;

										return children(internalItem, externalIndex, internalIndex);
									}}
								</Repeater>
							</div>
						</>
					))}
				</div>
				<Button
					className="jsf-repeater-nested__external__button-add"
					variant="primary"
					onClick={() => externalRepeater.addNewItem([defaultItemData])}
				>{externalButtonAddLabel}</Button>
			</div>
		</div>
	);
};

export default NestedRepeater;