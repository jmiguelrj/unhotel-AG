import { useSelect, useDispatch } from '@wordpress/data';
import { STORE_NAME } from './index';

const DEFAULT_STATE = {
	data: [],
	itemsCount: 0,
	columns: [
		{ id: 'name', label: 'Listing Name', enableHiding: false, defaultVisibility: true },
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
		case 'SET_LISTINGS_DATA':
			return { ...state, data: action.data };
		case 'SET_LISTINGS_ITEMS_COUNT':
			return { ...state, itemsCount: action.itemsCount };
		case 'SET_LISTINGS_COLUMNS':
			return { ...state, columns: action.columns };
		case 'SET_LISTINGS_VIEW':
			return { ...state, view: action.view };
		case 'SET_LISTINGS_DEFAULT_LAYOUTS':
			return { ...state, defaultLayouts: action.defaultLayouts };
		case 'CLEAR_LISTINGS_DATA':
			return DEFAULT_STATE;
		default:
			return state;
	}
};

// === Selectors ===
const selectors = {
	getListingsData: (state) => state.listings.data,
	getListingsItemsCount: (state) => state.listings.itemsCount,
	getListingsColumns: (state) => state.listings.columns,
	getListingsView: (state) => state.listings.view,
	getListingsDefaultLayouts: (state) => state.listings.defaultLayouts,
};

// === Actions ===
const actions = {
	setListingsData(data) {
		return { type: 'SET_LISTINGS_DATA', data };
	},
	setListingsItemsCount(itemsCount) {
		return { type: 'SET_LISTINGS_ITEMS_COUNT', itemsCount };
	},
	setListingsColumns(columns) {
		return { type: 'SET_LISTINGS_COLUMNS', columns };
	},
	setListingsView(view) {
		return { type: 'SET_LISTINGS_VIEW', view };
	},
	setListingsDefaultLayouts(defaultLayouts) {
		return { type: 'SET_LISTINGS_DEFAULT_LAYOUTS', defaultLayouts };
	},
	clearListingsData() {
		return { type: 'CLEAR_LISTINGS_DATA' };
	}
};

// === Helper ===
const helper = {
	useSelect: () => useSelect((select) => ({
		listingsData: select(STORE_NAME).getListingsData(),
		listingsItemsCount: select(STORE_NAME).getListingsItemsCount(),
		listingsColumns: select(STORE_NAME).getListingsColumns(),
		listingsView: select(STORE_NAME).getListingsView(),
		listingsDefaultLayouts: select(STORE_NAME).getListingsDefaultLayouts(),
	})),
	useDispatch: () => {
		const dispatch = useDispatch(STORE_NAME);

		return {
			setListingsData: dispatch.setListingsData,
			setListingsItemsCount: dispatch.setListingsItemsCount,
			setListingsColumns: dispatch.setListingsColumns,
			setListingsView: dispatch.setListingsView,
			setListingsDefaultLayouts: dispatch.setListingsDefaultLayouts,
			clearListingsData: dispatch.clearListingsData
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