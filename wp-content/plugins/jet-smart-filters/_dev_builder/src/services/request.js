import { serialize } from '@wordpress/blocks';

let listingsController = new AbortController();
let listingController = new AbortController();
let itemsListController = new AbortController();
let listingItemController = new AbortController();
let cardsController = new AbortController();
let usersController = new AbortController();
let postsController = new AbortController();
let termsController = new AbortController();

export default {
	async fetch(url, options = {}) {
		try {
			let response = await fetch(url, options);

			if (response.status !== 200)
				throw new Error(response.status);

			return response;
		} catch (error) {
			if (error.name === 'AbortError') {
				// Returning a stub so fetch Json() doesn't crash
				return new Response(JSON.stringify({ error: true, message: 'JSF: Request cancelled' }), {
					status: 499,
					headers: { 'Content-Type': 'application/json' }
				});
			} else {
				console.error('JSF: Request error', error);
				throw error;
			}
		}
	},

	async fetchJson(url, params = {}, signal = false) {
		const options = {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': this.nonce
			},
			body: JSON.stringify(params)
		};

		if (signal)
			options.signal = signal;

		const response = await this.fetch(url, options);

		return response.json();
	},

	async getListings(params = {}) {
		// Cancel previous request before new one
		listingsController.abort();
		listingsController = new AbortController();

		return this.fetchJson(
			this.ajaxurl + '?action=' + this.endpoints.get_listings,
			{ listings_arg: params },
			listingsController.signal
		);
	},

	async getListing(listingId) {
		// Cancel previous request before new one
		listingController.abort();
		listingController = new AbortController();

		return this.fetchJson(
			this.ajaxurl + '?action=' + this.endpoints.get_listing,
			{ listing_id: listingId },
			listingController.signal
		);
	},

	async saveListing(data) {
		// Cancel previous request before new one
		listingController.abort();
		listingController = new AbortController();

		return this.fetchJson(
			this.ajaxurl + '?action=' + this.endpoints.save_listing,
			{ listing: data },
			listingController.signal
		);
	},

	async removeListing(id, params = {}) {
		return this.fetchJson(
			this.ajaxurl + '?action=' + this.endpoints.remove_listing,
			{
				listing_id: id,
				listings_arg: params
			}
		);
	},

	async getItems(params = {}) {
		// Cancel previous request before new one
		itemsListController.abort();
		itemsListController = new AbortController();

		return this.fetchJson(
			this.ajaxurl + '?action=' + this.endpoints.get_items,
			{ items_list_arg: params },
			itemsListController.signal
		);
	},

	async getListingItem(itemId) {
		// Cancel previous request before new one
		listingItemController.abort();
		listingItemController = new AbortController();

		return this.fetchJson(
			this.ajaxurl + '?action=' + this.endpoints.get_listing_item,
			{ item_id: itemId },
			listingItemController.signal
		);
	},

	async saveListingItem(itemData, payloadData = null) {

		// Cancel previous request before new one
		listingItemController.abort();
		listingItemController = new AbortController();

		/**
		 * Important!
		 *
		 * Blocks must be serialized before save to correctly render blocks without PHP parser,
		 * like core/group etc.
		 */
		if ( itemData.content ) {
			itemData.content = serialize( itemData.content );
		}

		const data = {
			listing_item: itemData
		};

		if (payloadData)
			data['payload'] = payloadData;

		return this.fetchJson(
			this.ajaxurl + '?action=' + this.endpoints.save_listing_item,
			data,
			listingItemController.signal
		);
	},

	async remove_listing_item(id, params = {}) {
		return this.fetchJson(
			this.ajaxurl + '?action=' + this.endpoints.remove_listing_item,
			{
				item_id: id,
				items_list_arg: params
			}
		);
	},

	async getPostsList(params = {}) {
		// Cancel previous request before new one
		postsController.abort();
		postsController = new AbortController();

		return this.fetchJson(
			this.ajaxurl + '?action=' + this.endpoints.get_posts_list,
			params,
			postsController.signal
		);
	},

	async getTermsList(params = {}) {
		// Cancel previous request before new one
		termsController.abort();
		termsController = new AbortController();

		return this.fetchJson(
			this.ajaxurl + '?action=' + this.endpoints.get_terms_list,
			params,
			termsController.signal
		);
	},

	async getCards(params = {}) {
		// Cancel previous request before new one
		cardsController.abort();
		cardsController = new AbortController();

		return this.fetchJson(
			this.ajaxurl + '?action=' + this.endpoints.get_cards,
			params,
			cardsController.signal
		);
	},

	async getUsers(params = {}) {
		// Cancel previous request before new one
		usersController.abort();
		usersController = new AbortController();

		return this.fetchJson(
			this.ajaxurl + '?action=' + this.endpoints.get_users,
			params,
			usersController.signal
		);
	},

	get ajaxurl() {
		return window.ajaxurl;
	},

	get nonce() {
		return window.JSFBuilderData.nonce;
	},

	get endpoints() {
		return window.JSFBuilderData.endpoints;
	},
};