import { useNavigate as useReactNavigate } from "react-router-dom";
import { useLocation } from 'react-router-dom';

export const useNavigate = () => {
	const navigate = useReactNavigate();

	const goToPage = (pageName = null, state = null) => {
		if (!pageName)
			return;

		navigate(`/${pageName}`, state ? { state } : undefined);
	};

	const goToNewListing = (state = null) => {
		navigate('/new-listing', state ? { state } : undefined);
	};

	const goToEditListing = (listId = null, state = null) => {
		if (!listId)
			return;

		navigate(`/edit-listing/${listId}`, state ? { state } : undefined);
	};

	const goToNewItem = (state = null) => {
		navigate('/new-item', state ? { state } : undefined);
	};

	const goToEditItem = (itemId = null, state = null) => {
		if (!itemId)
			return;

		navigate(`/edit-item/${itemId}`, state ? { state } : undefined);
	};

	return {
		goToPage,
		goToNewListing,
		goToEditListing,
		goToNewItem,
		goToEditItem
	};
};

export const useLocationState = () => {
	const location = useLocation();

	const getLocationState = () => {
		return location.state;
	};

	const getLocationStateProp = (prop) => {
		return location.state?.[prop];
	};

	return {
		getLocationState,
		getLocationStateProp
	};
};

export default {
	useNavigate,
	useLocationState
};