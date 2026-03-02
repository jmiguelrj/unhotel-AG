import { useSelect, useDispatch } from '@wordpress/data';
import { STORE_NAME } from './index';
import { useLocalizedData } from "services/data";

const {
	allowedBlock
} = useLocalizedData();

const DEFAULT_STATE = {
	id: null,
	data: {
		name: '',
		content: [],
		settings: [],
		styles: []
	},
	editorSettings: {
		allowedBlockTypes: allowedBlock
	},
	isItemLoading: false,
	isItemSaving: false
};

// === Reducer ===
const reducer = (state = DEFAULT_STATE, action) => {
	switch (action.type) {
		case 'SET_ITEM_ID':
			return { ...state, id: action.id };
		case 'SET_ITEM_DATA':
			return { ...state, data: action.data };
		case 'SET_EDITOR_SETTINGS':
			return { ...state, settings: action.editorSettings };
		case 'SET_IS_ITEM_LOADING':
			return { ...state, isItemLoading: action.isItemLoading };
		case 'SET_IS_ITEM_SAVING':
			return { ...state, isItemSaving: action.isItemSaving };
		case 'CLEAR_ITEM_DATA':
			const { editorSettings } = state;
			
			return {
				...DEFAULT_STATE,
				editorSettings // do not clear editorSettings
			};
		default:
			return state;
	}
};

// === Selectors ===
const selectors = {
	getItemId: (state) => state.item.id,
	getItemData: (state) => state.item.data,
	getEditorSettings: (state) => state.item.editorSettings,
	getIsItemLoading: (state) => state.item.isItemLoading,
	getIsItemSaving: (state) => state.item.isItemSaving
};

// === Actions ===
const actions = {
	setItemId(id) {
		return { type: 'SET_ITEM_ID', id };
	},
	setItemData(data) {
		return { type: 'SET_ITEM_DATA', data };
	},
	setEditorSettings(editorSettings) {
		return { type: 'SET_EDITOR_SETTINGS', editorSettings };
	},
	setIsItemLoading(isItemLoading) {
		return { type: 'SET_IS_ITEM_LOADING', isItemLoading };
	},
	setIsItemSaving(isItemSaving) {
		return { type: 'SET_IS_ITEM_SAVING', isItemSaving };
	},
	clearItemData() {
		return { type: 'CLEAR_ITEM_DATA' };
	}
};

// === Helper ===
const helper = {
	useSelect: () => useSelect((select) => ({
		itemId: select(STORE_NAME).getItemId(),
		itemData: select(STORE_NAME).getItemData(),
		editorSettings: select(STORE_NAME).getEditorSettings(),
		isItemLoading: select(STORE_NAME).getIsItemLoading(),
		isItemSaving: select(STORE_NAME).getIsItemSaving()
	})),
	useDispatch: () => {
		const dispatch = useDispatch(STORE_NAME);

		return {
			setItemId: dispatch.setItemId,
			setItemData: dispatch.setItemData,
			setEditorSettings: dispatch.setEditorSettings,
			setIsItemLoading: dispatch.setIsItemLoading,
			setIsItemSaving: dispatch.setIsItemSaving,
			clearItemData: dispatch.clearItemData,
		};
	}
};

// === Export ===
export default {
	reducer,
	actions,
	selectors,
	helper
};