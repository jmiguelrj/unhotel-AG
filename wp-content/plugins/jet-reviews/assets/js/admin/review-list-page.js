(function( $, pageConfig ) {

	'use strict';

	Vue.config.devtools = true;

	Vue.component( 'jet-reviews-export-reviews-form', {
		template: '#tmpl-jet-reviews-export-reviews-form',

		data: function() {
			return ( {
				rawSourceType: pageConfig.sourceTypeOptions,
				source: 'post',
				sourceType: 'post',
				exportStatus: false
			} )
		},

		watch: {
			source: function( value ) {
				let sourceTypeOptions = this.rawSourceType[ value ]['types'];

				this.sourceType = sourceTypeOptions[0]['value'];
			}
		},

		computed: {
			sourceOptions: function () {
				let sourceOptions = [];

				for ( const source in this.rawSourceType ) {
					sourceOptions.push( {
						'label': this.rawSourceType[ source ]['label'],
						'value': source,
					} );
				}

				return sourceOptions;
			},

			sourceTypeOptions: function () {
				return this.rawSourceType[ this.source ]['types'];
			},

			exportUrl: function () {
				return `${ pageConfig.rawExportReviewsUrl }&source=${ this.source }&sourceType=${ this.sourceType }`;
			}
		},

		methods: {
			exportHandler: function () {
				window.open( this.exportUrl, '_self' );
				this.closePopupHandler();
			},

			closePopupHandler: function () {
				this.$root.exportPopupVisible = false;
			}
		}

	} );

	Vue.component( 'jet-reviews-import-reviews-form', {
		template: '#tmpl-jet-reviews-import-reviews-form',

		data: function() {
			return ( {
				parseProgressState: false,
				readyToParse: false,
				readyToImport: false,
				parseImportState: false,
				rawParsedData: [],
				tableHeaderData: [],
				file: false,
			} )
		},

		computed: {
			parsedData: function () {
				return this.rawParsedData;
			},
			parsedDataLength: function () {
				return this.parsedData.length;
			},
			columnWidth: function () {
				return 100 / this.tableHeaderData.length;
			}
		},

		methods: {
			prepareToImport( files ) {
				this.file = files[0];
				this.readyToParse = true
			},
			parseFileHandler() {

				if ( ! this.readyToParse ) {
					return false;
				}

				let formData = new FormData(),
				    xhr      = null;

				formData.append( '_file', this.file );
				formData.append( 'action', 'jet_reviews_parse_import_file' );
				this.parseProgressState = true;

				xhr = new XMLHttpRequest();

				xhr.open( 'POST', window.ajaxurl, true );
				xhr.onload = ( event, request ) => {
					this.parseProgressState = false;

					if ( xhr.status == 200 ) {
						let response = event.currentTarget.response;

						response = JSON.parse( response );
						console.log(response)

						if ( response.success ) {
							this.readyToImport = true;
							this.tableHeaderData = response.data.headers;
							this.rawParsedData = response.data.rawParsedData;

						} else {
							console.log( response.data.message );
						}
					} else {
						console.log( xhr.status )
					}
				};

				xhr.send( formData );
			},

			importHandler: function () {
				this.parseImportState = true;

				wp.apiFetch( {
					method: 'post',
					path: pageConfig.importReviewsRoute,
					data: {
						list: this.parsedData,
					},
				} ).then( ( response ) => {
					this.parseImportState = false;

					if ( response.success ) {
						this.$root.itemsList = response.data.page_list;
						this.$root.reviewsCount = +response.data.total_count;
						this.closePopupHandler();
					} else {
						self.$CXNotice.add( {
							message: response.message,
							type: 'error',
							duration: 3000,
						} );
					}
				} );
			},

			closePopupHandler: function () {
				this.$root.importPopupVisible = false;
			}
		}

	} );

	window.JetReviewsListPage = new Vue( {
		el: '#jet-reviews-list-page',

		data: {
			reviewsGetting: false,
			itemsList: pageConfig.reviewsList,
			reviewsCount: +pageConfig.reviewsCount,
			postTypeOptions: pageConfig.postTypeOptions,
			postTypeFilter: '',
			titleSearchText: '',
			page: +pageConfig.currentPage,
			pageSize: +pageConfig.pageSize,
			commentsPageUrl: pageConfig.commentsPageUrl,
			viewState: 'list',
			editReviewId: false,
			reviewSavingState: false,
			actionExecution: false,
			searchingState: false,
			bulkCheck: false,
			bulkAction: '',
			bulkActionStatus: false,
			urlAction: false,
			urlReviewId: false,
			exportPopupVisible: false,
			importPopupVisible: false,
		},

		mounted: function() {
			this.$el.className = this.$el.className + ' is-mounted';

			const urlParams = new URLSearchParams( window.location.href );

			this.urlAction = urlParams.get( 'action' ) || false;
			this.urlReviewId = urlParams.get( 'review' ) || false;

			this.getReviews();
		},

		watch: {
			bulkCheck: function( state ) {
				this.itemsList = this.itemsList.map( ( itemData ) => {
					itemData.check = state;

					return itemData;
				} );

			}
		},

		computed: {
			getReviewsParams: function () {
				return {
					id: this.urlReviewId,
					page: this.page - 1,
					title: this.titleSearchText,
					post_type: this.postTypeFilter,
				}
			},
			checkedItemsList: function () {
				const filteredList = this.itemsList.filter( ( itemData ) => {
					return itemData.check;
				} );

				return filteredList.map( ( itemData ) => {
					return itemData.id;
				} );
			},
			editReviewData: function() {

				if ( 'undefined' !== typeof this.itemsList[ this.editReviewId ] ) {
					return this.itemsList[ this.editReviewId ];
				}

				return {
					approved: 'true',
					author: {
						avatar: '',
						id: '1',
						mail: 'demo@demo.com',
						name: 'admin',
					},
					content: '',
					date: '2000-01-01 00:00:00',
					id: '0',
					post: {},
					post_slug: '',
					rating_data: {},
					title: '',
					type_slug: 'default',
				};
			}
		},

		methods: {
			changePage: function( page ) {
				this.page = page;
				this.getReviews();
			},

			postTypeFilterHandler: function() {
				this.getReviews();
			},

			searchReviewHandle: function() {
				this.searchingState = true;
				this.getReviews();
			},

			getIndexByID: function ( id ) {
				return this.itemsList.findIndex( ( item ) => {
					return item.id === id;
				} );
			},

			getReviews: function() {
				let self = this;

				if ( '' !== this.titleSearchText ) {
					this.page = 1;
				}

				this.reviewsGetting = true;

				wp.apiFetch( {
					method: 'post',
					path: pageConfig.getReviewsRoute,
					data: {
						pageArgs: self.getReviewsParams,
					},
				} ).then( function( response ) {
					self.reviewsGetting = false;
					self.searchingState = false;

					if ( response.success && response.data ) {
						self.itemsList = response.data.page_list;
						self.reviewsCount = +response.data.total_count;

						self.urlActionHandle();
					} else {
						self.$CXNotice.add( {
							message: response.message,
							type: 'error',
							duration: 5000,
						} );
					}
				} );
			},

			approveHandler: function( ids = [], status = 'single' ) {
				const self      = this,
				      itemsList = ids.map( ( id ) => {
						  let index    = this.getIndexByID( id ),
							  approved = true;

						  switch ( status ) {
							  case 'single':
								  approved = -1 !== index ? !this.itemsList[ index ].approved : true;
								  break;
							  case 'unapprove':
								  approved = false;
								  break;
							  case 'approve':
								  approved = true;
								  break;
						  }

					      return {
						      id: id,
						      approved: approved,
							  source: -1 !== index ? this.itemsList[ index ].source : false,
							  sourceType: -1 !== index ? this.itemsList[ index ].source_type : false,
							  sourceId: -1 !== index ? this.itemsList[ index ].post.id : false,
					      };
				      } );

				if ( ! itemsList.length ) {
					return false;
				}

				this.actionExecution = true;

				wp.apiFetch( {
					method: 'post',
					path: pageConfig.toggleReviewApproveRoute,
					data: {
						itemsList: itemsList,
					},
				} ).then( function( response ) {
					self.actionExecution = false;

					if ( response.success ) {
						self.itemsList = response.data.page_list;
						self.reviewsCount = +response.data.total_count;
					} else {
						self.$CXNotice.add( {
							message: response.message,
							type: 'error',
							duration: 3000,
						} );
					}
				} );
			},

			openEditReviewPopup: function( index ) {
				this.editReviewId = index;
				this.viewState = 'edit';
			},

			openExportReviewPopup: function( index ) {
				this.exportPopupVisible = true;
			},

			openImportReviewPopup: function( index ) {
				this.importPopupVisible = true;
			},

			saveReviewHandle: function() {
				let self = this;

				this.reviewSavingState = true;

				wp.apiFetch( {
					method: 'post',
					path: pageConfig.updateReviewRoute,
					data: {
						data: self.editReviewData
					},
				} ).then( function( response ) {

					self.reviewSavingState = false;

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

			deleteReviewHandle: function( ids ) {
				let self = this,
				    itemsList = ids.map( ( id ) => {
						let index    = this.getIndexByID( id );

					    return {
						    id: id,
							source: -1 !== index ? this.itemsList[ index ].source : false,
							sourceType: -1 !== index ? this.itemsList[ index ].source_type : false,
							sourceId: -1 !== index ? this.itemsList[ index ].post.id : false,
					    };
				    } );

				if ( ! itemsList.length ) {
					return false;
				}

				this.actionExecution = true;

				wp.apiFetch( {
					method: 'post',
					path: pageConfig.deleteReviewRoute,
					data: {
						itemsList: itemsList,
						pageArgs: self.getReviewsParams,
					},
				} ).then( function( response ) {
					self.actionExecution = false;

					if ( response.success ) {
						self.itemsList = response.data.page_list;
						self.reviewsCount = +response.data.total_count;
					} else {
						self.$CXNotice.add( {
							message: response.message,
							type: 'error',
							duration: 5000,
						} );
					}
				} );
			},

			deleteReviewMediaHandle: function( ids ) {
				let self = this;

				if ( ! ids.length ) {
					return false;
				}

				this.actionExecution = true;

				wp.apiFetch( {
					method: 'post',
					path: pageConfig.deleteReviewMediaRoute,
					data: {
						itemsList: ids,
						reviewId: this.editReviewData.id,
					},
				} ).then( function( response ) {
					self.actionExecution = false;

					if ( response.success ) {
						console.log( response.data );
						const index = self.getIndexByID( self.editReviewData.id );

						if ( -1 !== index ) {
							self.itemsList[ index ].media = response.data.media;
						}

					} else {
						self.$CXNotice.add( {
							message: response.message,
							type: 'error',
							duration: 5000,
						} );
					}
				} );
			},

			bulkActionHandle: function () {

				switch ( this.bulkAction ) {
					case 'unapprove':
						this.approveHandler( this.checkedItemsList, 'unapprove' );
						break;
					case 'approve':
						this.approveHandler( this.checkedItemsList, 'approve' );
						break;
					case 'delete':
						this.deleteReviewHandle( this.checkedItemsList );
						this.bulkCheck = false;
						break;
				}
			},

			urlActionHandle: function () {

				if ( this.urlAction && this.urlReviewId ) {
					switch ( this.urlAction ) {
						case 'approve':
							this.approveHandler( [ this.urlReviewId ], 'approve' );
							break;
						case 'delete':
							this.deleteReviewHandle( [ this.urlReviewId ] );
							break;
					}

					this.urlAction = false;
					this.urlReviewId = false;
				}
			},

			getRating: function( rating ) {
				let averageRating = +rating,
					ratingColor   = 'very-high';

				if ( averageRating >= 80 && averageRating <= 100 ) {
					ratingColor = 'very-high';
				}

				if ( averageRating >= 60 && averageRating <= 79 ) {
					ratingColor = 'high';
				}

				if ( averageRating >= 40 && averageRating <= 59 ) {
					ratingColor = 'medium';
				}

				if ( averageRating >= 20 && averageRating <= 39 ) {
					ratingColor = 'low';
				}

				if ( averageRating >= 0 && averageRating <= 19 ) {
					ratingColor = 'very-low';
				}

				return `<span class="rating-value ${ratingColor}-rating">${ averageRating }%</span>`;
			},

			getRolesLabel: function( $rolesList ) {
				let label = '';

				if ( 'function' !== typeof $rolesList[ Symbol.iterator ] ) {
					return label;
				}

				for ( let role of $rolesList ) {
					label += `<span class="${ role }-role">${ role }</span>`;
				}

				return label;
			}
		}
	} );

})( jQuery, window.JetReviewsListPageConfig );
