import { useEffect } from '@wordpress/element';
import helper from "services/helper";

export const useRepeater = (items, setItems, props = {}) => {
	// On init
	useEffect(() => {
		// Set IDs
		if (props.setIDs) {
			const newItems = [...items];

			for (let index = 0; index < newItems.length; index++) {
				if (!newItems[index].id) {
					newItems[index] = {
						...newItems[index],
						id: helper.getUniqueId()
					};
				}
			}

			if (JSON.stringify(newItems) !== JSON.stringify(items))
				setItems(newItems);
		}
	}, []);

	const setOpenItem = (index, state = null) => {
		const item = items[index];

		if (!item)
			return;

		if (state === null) {
			if ('isOpen' in item) {
				delete item.isOpen;
			} else {
				item.isOpen = true;
			}
		} else if (state === true) {
			item.isOpen = true;
		} else if (state === false) {
			delete item.isOpen;
		}

		setItems(items);
	};

	const addNewItem = (itemData) => {
		const newItems = [...items];

		if (props.setIDs && !itemData.id)
			itemData.id = helper.getUniqueId();

		if (props.newItemIsOpen)
			itemData.isOpen = true;

		newItems.push(itemData);

		setItems(newItems);
	};

	const cloneItem = (index) => {
		const newItems = [...items];
		const clonedItem = { ...newItems[index] };

		if (props.setIDs)
			clonedItem.id = helper.getUniqueId();

		newItems.splice(index + 1, 0, clonedItem);

		setItems(newItems);
	};

	const removeItem = (index) => {
		const newItems = [...items].filter((_, i) => i !== index);

		setItems(newItems);
	};

	const getLabel = (mask, item, defaultLabel = '') => {
		if (!mask)
			return defaultLabel;

		return mask
			.replace(/\{(\w+)\|\|(.*?)\|(.*?)\|\|\}/g, (_, key, singular, plural) => {
				const value = parseInt(item[key], 10) || 0;
				return `${value}${value === 1 ? singular : plural}`;
			})
			.replace(/\{(\w+)::(.*?)\}/g, (_, key, fallback) => {
				const value = item[key];
				return value !== undefined && value !== "" ? String(value) : fallback;
			})
			.replace(/\{(\w+)\}/g, (_, key) => item[key] || "");
	};

	return {
		setOpenItem,
		addNewItem,
		cloneItem,
		removeItem,
		getLabel
	};
};

export default useRepeater;