import { useSelect, useDispatch } from '@wordpress/data';
import { STORE_NAME } from './index';

const DEFAULT_STATE = {
	id: null,
	name: '',
	query: {
		post_types: [],
		post_status: [],
		post_authors: [],
		posts_per_page: 10,
		offset: 0,
		post__in: [],
		post__not_in: [],
		ignore_sticky_posts: false,
		sort: {
			orderby: 'date',
			order: 'DESC'
		},
		taxonomies: [],
		custom_fields: [],
	},
	settings: {
		sizing: [
			{
				width: 9999,
				columns: 3,
				spacing: 30,
			},
			{
				width: 768,
				columns: 2,
				spacing: 20,
			},
			{
				width: 576,
				columns: 1,
				spacing: 10,
			}
		]
	},
	cards: [],
	card: null,
	isListingLoading: false,
	isListingSaving: false,
};

// === Reducer ===
const reducer = (state = DEFAULT_STATE, action) => {
	switch (action.type) {
		case 'SET_LISTING_ID':
			return { ...state, id: action.id };
		case 'SET_LISTING_NAME':
			return { ...state, name: action.name };
		case 'SET_LISTING_QUERY':
			return { ...state, query: action.query };
		case 'SET_LISTING_QUERY_PROP':
			return {
				...state,
				query: {
					...state.query,
					[action.key]: action.value
				}
			};
		case 'SET_LISTING_SETTINGS':
			return { ...state, settings: action.settings };
		case 'SET_LISTING_SETTING':
			return {
				...state,
				settings: {
					...state.settings,
					[action.key]: action.value
				}
			};
		case 'SET_LISTING_CARDS':
			return { ...state, cards: action.cards };
		case 'SET_LISTING_CARD':
			return { ...state, card: action.card };
		case 'SET_IS_LISTING_LOADING':
			return { ...state, isListingLoading: action.isListingLoading };
		case 'SET_IS_LISTING_SAVING':
			return { ...state, isListingSaving: action.isListingSaving };
		case 'CLEAR_LISTING_SETTINGS':
			return DEFAULT_STATE;
		default:
			return state;
	}
};

// === Selectors ===
const selectors = {
	getListingId: (state) => state.listing.id,
	getListingName: (state) => state.listing.name,
	getListingQuery: (state) => state.listing.query,
	getListingQueryProp: (state, key) => state.listing.query[key],
	getListingSettings: (state) => state.listing.settings,
	getListingSetting: (state, key) => state.listing.settings[key],
	getListingCards: (state) => state.listing.cards,
	getListingCard: (state) => state.listing.card,
	getIsListingLoading: (state) => state.listing.isListingLoading,
	getIsListingSaving: (state) => state.listing.isListingSaving
};

// === Actions ===
const actions = {
	setListingId(id) {
		return { type: 'SET_LISTING_ID', id };
	},
	setListingName(name) {
		return { type: 'SET_LISTING_NAME', name };
	},
	setListingQuery(query) {
		return { type: 'SET_LISTING_QUERY', query };
	},
	setListingQueryProp(key, value) {
		return { type: 'SET_LISTING_QUERY_PROP', key, value };
	},
	setListingSettings(settings) {
		return { type: 'SET_LISTING_SETTINGS', settings };
	},
	setListingSetting(key, value) {
		return { type: 'SET_LISTING_SETTING', key, value };
	},
	setListingCards(cards) {
		return { type: 'SET_LISTING_CARDS', cards };
	},
	setListingCard(card) {
		return { type: 'SET_LISTING_CARD', card };
	},
	setIsListingLoading(isListingLoading) {
		return { type: 'SET_IS_LISTING_LOADING', isListingLoading };
	},
	setIsListingSaving(isListingSaving) {
		return { type: 'SET_IS_LISTING_SAVING', isListingSaving };
	},
	clearListingSettings() {
		return { type: 'CLEAR_LISTING_SETTINGS' };
	}
};

// === Helper ===
const helper = {
	useSelect: () => useSelect((select) => ({
		listingId: select(STORE_NAME).getListingId(),
		listingName: select(STORE_NAME).getListingName(),
		listingQuery: select(STORE_NAME).getListingQuery(),
		listingQueryProp: (key) => select(STORE_NAME).getListingQueryProp(key),
		listingSettings: select(STORE_NAME).getListingSettings(),
		listingSetting: (key) => select(STORE_NAME).getListingSetting(key),
		listingCards: select(STORE_NAME).getListingCards(),
		listingCard: select(STORE_NAME).getListingCard(),
		isListingLoading: select(STORE_NAME).getIsListingLoading(),
		isListingSaving: select(STORE_NAME).getIsListingSaving(),
	})),
	useDispatch: () => {
		const dispatch = useDispatch(STORE_NAME);

		return {
			setListingId: dispatch.setListingId,
			setListingName: dispatch.setListingName,
			setListingQuery: dispatch.setListingQuery,
			setListingQueryProp: dispatch.setListingQueryProp,
			setListingSettings: dispatch.setListingSettings,
			setListingSetting: dispatch.setListingSetting,
			clearListingSettings: dispatch.clearListingSettings,
			setListingCards: dispatch.setListingCards,
			setListingCard: dispatch.setListingCard,
			setIsListingLoading: dispatch.setIsListingLoading,
			setIsListingSaving: dispatch.setIsListingSaving,
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