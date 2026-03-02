'use strict';

let jetReviewsSettinsMixin = {
	data: function() {
		return {
			pageOptions: window.JetReviewsSettingsConfig.settingsData || [],
			savingStatus: false,
			ajaxSaveHandler: null,
			debonceSavingInterval: null,
		};
	},

	watch: {
		pageOptions: {
			handler( options ) {
				clearInterval( this.debonceSavingInterval );
				this.debonceSavingInterval = setTimeout( this.saveOptions, 500 );
			},
			deep: true
		}
	},

	computed: {
		preparedOptions: function() {
			return this.pageOptions;
		}
	},

	methods: {
		saveOptions: function() {

			let self = this;

			this.savingStatus = true;

			wp.apiFetch( {
				method: 'post',
				path: window.JetReviewsSettingsConfig.saveSettingsRoute,
				data: {
					settings: self.preparedOptions
				},
			} ).then( function( response ) {

				if ( response.success ) {
					self.$CXNotice.add( {
						message: response.message,
						type: 'success',
						duration: 5000,
					} );
				} else {
					self.$CXNotice.add( {
						message: response.message,
						type: 'error',
						duration: 5000,
					} );
				}
			} );
		},
	}
}

Vue.component( 'jet-reviews-integrations', {

	template: '#jet-dashboard-jet-reviews-integrations',

	mixins: [ jetReviewsSettinsMixin ],

	data: function() {
		return {};
	},

	computed: {},

	methods: {}
} );

Vue.component( 'jet-reviews-advanced', {

	template: '#jet-dashboard-jet-reviews-advanced',

	mixins: [ jetReviewsSettinsMixin ],

	data: function() {
		return {};
	},

	computed: {},

	methods: {}
} );

Vue.component( 'jet-reviews-woocommerce', {

	template: '#jet-dashboard-jet-reviews-woocommerce',

	mixins: [ jetReviewsSettinsMixin ],

	data: function() {
		return {
			productList: window.JetReviewsSettingsConfig.productsList || [],
			toPullProductList: [],
			convertWooReviewsStatus: false,
		};
	},

	methods: {
		convertWooReviews: function() {
			let self = this;

			this.convertWooReviewsStatus = true;

			wp.apiFetch( {
				method: 'post',
				path: window.JetReviewsSettingsConfig.pullProductReviewsRoute,
				data: {
					products: self.toPullProductList,
				},
			} ).then( function( response ) {
				self.convertWooReviewsStatus = false;

				if ( response.success ) {
					self.$CXNotice.add( {
						message: response.message,
						type: 'success',
						duration: 5000,
					} );
				} else {
					self.$CXNotice.add( {
						message: response.message,
						type: 'error',
						duration: 5000,
					} );
				}
			} );
		}
	}
} );

Vue.component( 'jet-reviews-post-types', {

	template: '#jet-dashboard-jet-reviews-post-types',

	mixins: [ jetReviewsSettinsMixin ],

	data: function() {
		return {
			sourceTypeListUrl: window.JetReviewsSettingsConfig.sourceTypeListUrl,
		};
	},

	computed: {},

	methods: {}
} );
