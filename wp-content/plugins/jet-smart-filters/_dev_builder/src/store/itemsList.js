import { useSelect, useDispatch } from '@wordpress/data';
import { STORE_NAME } from './index';

const DEFAULT_STATE = {
	data: [],
	itemsCount: 0,
	columns: [
		{ id: 'name', label: 'Item Name', enableHiding: false, defaultVisibility: true },
		{ id: 'created', label: 'Created' },
	],
	view: {
		type: 'table',
		perPage: 10,
		titleField: 'name',
		fields: [
			'created'
		],
		filters: [],
		sort: {
			field: 'created',
			direction: 'desc'
		},
	},
	defaultLayouts: {
		table: {}
	}
};

// === Reducer ===
const reducer = (state = DEFAULT_STATE, action) => {
	switch (action.type) {
		case 'SET_ITEMSLIST_DATA':
			return { ...state, data: action.data };
		case 'SET_ITEMSLIST_COUNT':
			return { ...state, itemsCount: action.itemsCount };
		case 'SET_ITEMSLIST_COLUMNS':
			return { ...state, columns: action.columns };
		case 'SET_ITEMSLIST_VIEW':
			return { ...state, view: action.view };
		case 'SET_ITEMSLIST_DEFAULT_LAYOUTS':
			return { ...state, defaultLayouts: action.defaultLayouts };
		case 'CLEAR_ITEMSLIST_DATA':
			return DEFAULT_STATE;
		default:
			return state;
	}
};

// === Selectors ===
const selectors = {
	getItemsListData: (state) => state.itemsList.data,
	getItemsListCount: (state) => state.itemsList.itemsCount,
	getItemsListColumns: (state) => state.itemsList.columns,
	getItemsListView: (state) => state.itemsList.view,
	getItemsListDefaultLayouts: (state) => state.itemsList.defaultLayouts,
};

// === Actions ===
const actions = {
	setItemsListData(data) {
		return { type: 'SET_ITEMSLIST_DATA', data };
	},
	setItemsListCount(itemsCount) {
		return { type: 'SET_ITEMSLIST_COUNT', itemsCount };
	},
	setItemsListColumns(columns) {
		return { type: 'SET_ITEMSLIST_COLUMNS', columns };
	},
	setItemsListView(view) {
		return { type: 'SET_ITEMSLIST_VIEW', view };
	},
	setItemsListDefaultLayouts(defaultLayouts) {
		return { type: 'SET_ITEMSLIST_DEFAULT_LAYOUTS', defaultLayouts };
	},
	clearItemsListData() {
		return { type: 'CLEAR_ITEMSLIST_DATA' };
	}
};

// === Helper ===
const helper = {
	useSelect: () => useSelect((select) => ({
		itemsListData: select(STORE_NAME).getItemsListData(),
		itemsListCount: select(STORE_NAME).getItemsListCount(),
		itemsListColumns: select(STORE_NAME).getItemsListColumns(),
		itemsListView: select(STORE_NAME).getItemsListView(),
		itemsListDefaultLayouts: select(STORE_NAME).getItemsListDefaultLayouts(),
	})),
	useDispatch: () => {
		const dispatch = useDispatch(STORE_NAME);

		return {
			setItemsListData: dispatch.setItemsListData,
			setItemsListCount: dispatch.setItemsListCount,
			setItemsListColumns: dispatch.setItemsListColumns,
			setItemsListView: dispatch.setItemsListView,
			setItemsListDefaultLayouts: dispatch.setItemsListDefaultLayouts,
			clearItemsListData: dispatch.clearItemsListData
		};
	}
};

// === Export ===
export default {
	reducer,
	selectors,
	actions,
	helper
};