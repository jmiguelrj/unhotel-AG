import { useSelect, useDispatch } from '@wordpress/data';
import { STORE_NAME } from './index';
import { Listings, Listing, ItemsList, Card } from '@/views/pages';
import ListingItemsIcon from '../modules/Icons/ListingItemsIcon';
import ListingsIcon from '../modules/Icons/ListingsIcon';

const DEFAULT_STATE = {
	pagesMap: {
		'': { // home page
			component: Listings,
			title: 'All Listings',
			args: [],
			is_nav_item: true,
			icon: ListingsIcon,
		},
		'new-listing': {
			component: Listing,
			title: 'New Listing',
		},
		'edit-listing': {
			component: Listing,
			title: 'Edit Listing',
			args: ['listing_id'],
		},
		'items': {
			component: ItemsList,
			title: 'All Items',
			is_nav_item: true,
			icon: ListingItemsIcon,
		},
		'new-item': {
			component: Card,
			title: 'New Item',
		},
		'edit-item': {
			component: Card,
			title: 'Edit Item',
			args: ['item_id'],
		},
	},
	pageData: {},
	pageTitle: '',
};

// === Reducer ===
const reducer = (state = DEFAULT_STATE, action) => {
	switch (action.type) {
		case 'SET_ROUTER_PAGES_MAP':
			return { ...state, pagesMap: action.pagesMap };
		case 'SET_ROUTER_PAGE_DATA':
			return { ...state, pageData: action.pageData };
		case 'SET_ROUTER_PAGE_TITLE':
			return { ...state, pageTitle: action.pageTitle };
		default:
			return state;
	}
};

// === Selectors ===
const selectors = {
	getRouterPagesMap: (state) => state.router.pagesMap,
	getRouterPageData: (state) => state.router.pageData,
	getRouterPageTitle: (state) => state.router.pageTitle,
};

// === Actions ===
const actions = {
	setRouterPagesMap(pagesMap) {
		return { type: 'SET_ROUTER_PAGES_MAP', pagesMap };
	},
	setRouterPageData(pageData) {
		return { type: 'SET_ROUTER_PAGE_DATA', pageData };
	},
	setRouterPageTitle(pageTitle) {
		return { type: 'SET_ROUTER_PAGE_TITLE', pageTitle };
	},
};

// === Helper ===
const helper = {
	useSelect: () => useSelect((select) => ({
		pagesMap: select(STORE_NAME).getRouterPagesMap(),
		pageData: select(STORE_NAME).getRouterPageData(),
		pageTitle: select(STORE_NAME).getRouterPageTitle(),
	})),
	useDispatch: () => {
		const dispatch = useDispatch(STORE_NAME);

		return {
			setPagesMap: dispatch.setRouterPagesMap,
			setPageData: dispatch.setRouterPageData,
			setPageTitle: dispatch.setRouterPageTitle,
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
