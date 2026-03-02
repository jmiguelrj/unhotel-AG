import { registerStore } from '@wordpress/data';

import routerStore from './router';
import listingsStore from './listings';
import listingStore from './listing';
import itemsListStore from './itemsList';
import itemStore from './item';

// Register the store
export const STORE_NAME = 'jsf-builder';

const DEFAULT_STATE = {
	router: routerStore.reducer(undefined, {}),
	listings: listingsStore.reducer(undefined, {}),
	listing: listingStore.reducer(undefined, {}),
	itemsList: itemsListStore.reducer(undefined, {}),
	item: itemStore.reducer(undefined, {}),
};

const storeInstance = registerStore(STORE_NAME, {
	reducer(state = DEFAULT_STATE, action) {
		return {
			router: routerStore.reducer(state.router, action),
			listings: listingsStore.reducer(state.listings, action),
			listing: listingStore.reducer(state.listing, action),
			itemsList: itemsListStore.reducer(state.itemsList, action),
			item: itemStore.reducer(state.item, action),
		};
	},
	actions: {
		...routerStore.actions,
		...listingsStore.actions,
		...listingStore.actions,
		...itemsListStore.actions,
		...itemStore.actions,
	},
	selectors: {
		...routerStore.selectors,
		...listingsStore.selectors,
		...listingStore.selectors,
		...itemsListStore.selectors,
		...itemStore.selectors,
	},
});

export default {
	STORE_NAME,
	instance: storeInstance,
	router: routerStore.helper,
	listings: listingsStore.helper,
	listing: listingStore.helper,
	itemsList: itemsListStore.helper,
	item: itemStore.helper,
};