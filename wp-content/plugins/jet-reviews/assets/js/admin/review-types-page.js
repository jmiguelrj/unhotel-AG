(function( $, pageConfig ) {

	'use strict';

	Vue.config.devtools = true;

	window.JetReviewsTypesPage = new Vue( {
		el: '#jet-reviews-types-page',

		data: {
			progressStatus: false,
			deletePopupVisible: false,
			deleteTypeSlug: false,
			rawItemsList: Object.values( pageConfig.typesList ) || [],
			sourceTypeRawOptions: pageConfig.sourceTypeRawOptions || false,
			tempTypeData: {
				name: '',
				fields: []
			}
		},

		mounted: function() {
			this.$el.className = this.$el.className + ' is-mounted';
		},

		computed: {
			itemsList: function() {
				return this.rawItemsList;
			},
			preparedTempTypeData: function() {
				return {
					name: this.tempTypeData.name,
					slug: this.preSetSlug( this.tempTypeData.name ),
					fields: this.tempTypeData.fields
				}
			},
		},

		methods: {
			addNewTypeHandle: function() {
				window.open( pageConfig.addReviewTypeUrl, '_self' );
			},

			addNewField: function( event ) {
				var field = {
					label: '',
					step: 1,
					max: 100,
				};

				this.preparedTempTypeData.fields.push( field );
			},

			cloneField: function( index ) {
				var field    = this.tempTypeData.fields[ index ],
					newField = {
						label: field.label + '_copy',
						step: field.step,
						max: field.max,
					};

				this.tempTypeData.fields.splice( index + 1, 0, newField );
			},

			deleteField: function( index ) {

				if ( 1 === this.tempTypeData.fields.length ) {
					this.$CXNotice.add( {
						message: pageConfig.messages['emptyFields'],
						type: 'error',
						duration: 5000,
					} );

					return;
				}

				this.tempTypeData.fields.splice( index, 1 );
			},

			copyTypeHandle: function( slug ) {
				let self = this;

				this.progressStatus = true;

				wp.apiFetch( {
					method: 'post',
					path: pageConfig.copyReviewType,
					data: {
						slug: slug
					},
				} ).then( function( response ) {

					if ( response.success ) {
						self.progressStatus = false;
						self.rawItemsList.unshift( response.data );

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

			openDeleteTypePopup: function( typeSlug ) {
				this.deletePopupVisible = true;
				this.deleteTypeSlug = typeSlug;
			},

			deleteTypeHandle: function() {
				let self = this;

				this.progressStatus = true;

				wp.apiFetch( {
					method: 'post',
					path: pageConfig.deleteReviewType,
					data: {
						slug: self.deleteTypeSlug
					},
				} ).then( function( response ) {

					if ( response.success ) {
						self.progressStatus = false;

						let index = self.rawItemsList.findIndex( ( item, index ) => {
							return item.slug === self.deleteTypeSlug;
						} );

						Vue.delete( self.rawItemsList, index );

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

			generateFieldsList: function( fields ) {

				if ( ! fields || 0 === fields.length ) {
					return 'Default';
				}

				let labelsArray = fields.map( function( field ) {
					return `${ field.label }`;
				} );

				return labelsArray.join( ', ' );
			},

			generateSourceLabel: function ( source ) {
				if ( ! this.sourceTypeRawOptions.hasOwnProperty( source ) ) {
					return '';
				}

				return this.sourceTypeRawOptions[ source ]['label'];
			},

			generateSourceTypeLabel: function ( source, sourceType ) {

				if ( ! this.sourceTypeRawOptions.hasOwnProperty( source ) ) {
					return '';
				}

				let types = this.sourceTypeRawOptions[ source ]['types'],
					type = types.filter( ( type ) => {
						return sourceType === type.value;
					} );

				if ( 0 === type.length ) {
					return '';
				}

				return type[0].label;
			},

			preSetSlug: function( string ) {

				if ( 0 === string.length ) {
					return '';
				}

				var regex = /\s+/g,
					slug  = string.toLowerCase().replace( regex, '-' );

				// Replace accents
				slug = slug.normalize( 'NFD' ).replace( /[\u0300-\u036f]/g, "" );

				if ( 20 < slug.length ) {
					slug = slug.substr( 0, 20 );

					if ( '-' === slug.slice( -1 ) ) {
						slug = slug.slice( 0, -1 );
					}
				}

				return slug;

			},
		}
	} );

})( jQuery, window.JetReviewsTypesPageConfig );
