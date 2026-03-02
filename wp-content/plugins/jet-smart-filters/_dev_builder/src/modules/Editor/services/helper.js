export const areArraysEqual = (a, b) => {
	if (a.length !== b.length)
		return false;

	return a.every((item, index) => item === b[index]);
};

export const createDebounceChecker = (delay = 500) => {
	let timer = null;

	return () => {
		if (!timer) {

			// There is no timer - set a new one and return true
			timer = setTimeout(() => {
				timer = null;
			}, delay);

			return true;

		} else {

			// There is a timer - we reset it, set a new one and return false
			clearTimeout(timer);
			timer = setTimeout(() => {
				timer = null;
			}, delay);

			return false;

		}
	};
};

export const capitalize = (str) => str.charAt(0).toUpperCase() + str.slice(1);

export default {
	areArraysEqual,
	createDebounceChecker,
	capitalize
};