import { useEffect, useState, useRef } from '@wordpress/element';

export function useMountStatus(onUnmount) {
	const [isMounted, setIsMounted] = useState(false);
	const unmountCallbackRef = useRef(onUnmount);

	// Update the callback if it changes
	useEffect(() => {
		unmountCallbackRef.current = onUnmount;
	}, [onUnmount]);

	useEffect(() => {
		setIsMounted(true); // On mount

		return () => {
			setIsMounted(false); // On unmount
			if (unmountCallbackRef.current) {
				unmountCallbackRef.current();
			}
		};
	}, []);

	return isMounted;
}

export function getUniqueId() {
	return Math.random().toString(16).substr(2, 7);
}

export function isObject(obj) {
	return typeof obj === 'object' && !Array.isArray(obj) && obj !== null;
};

export function convertToOptions(data) {
	if (!isObject(data))
		return [];

	return Object.entries(data).map(([value, label]) => ({ value, label }));
}

export function convertNestedOptions(data) {
	if (!Array.isArray(data))
		if (typeof data === 'object' && data !== null) {
			data = Object.entries(data).map(([type, { label, options }]) => ({
				label,
				options
			}));
		} else {
			return [];
		}

	return data.map(group => {
		const { label, options } = group;

		return {
			label,
			options: convertToOptions(options)
		};
	});
}

export function findLabelsInGroupedOptionsByValue(groupedOptions, targetValue) {
	for (const group of groupedOptions) {
		const option = group.options.find(opt => opt.value === targetValue);

		if (option) {
			return {
				groupLabel: group.label,
				optionLabel: option.label
			};
		}
	}

	return null;
}

export default {
	useMountStatus,
	getUniqueId,
	isObject,
	convertToOptions,
	convertNestedOptions,
	findLabelsInGroupedOptionsByValue
};