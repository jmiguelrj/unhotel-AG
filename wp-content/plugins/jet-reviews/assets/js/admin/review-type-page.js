(function( $, pageConfig ) {

	'use strict';

	Vue.config.devtools = true;

	window.JetReviewsTypePage = new Vue( {
		el: '#jet-reviews-type-page',

		data: {
			actionProccessing: false,
			deleteActionProccessing: false,
			syncSourceMetaStatus: false,
			name: pageConfig.typeTypeData.name,
			slug: pageConfig.typeTypeData.slug,
			sourceTypeRawOptions: pageConfig.sourceTypeRawOptions || {},
			rawTypesList: pageConfig.typesList || {},
			typeSettings: pageConfig.typeTypeData.settings || {},
			allRolesOptions: pageConfig.allRolesOptions,
			verificationOptions: pageConfig.verificationOptions,
			structureDataTypesOptions: pageConfig.structureDataTypesOptions,
			allowedMediaOptions: pageConfig.allowedMediaOptions,
			defaultReviewFields: pageConfig.defaultReviewFields,
			syncPostmetaStatus: false,
			pageAction: pageConfig.action,
			originalData: {},
		},

		mounted: function() {
			this.$el.className = this.$el.className + ' is-mounted';
			this.updateOriginalData();
		},

		watch: {
			name: function( newValue ) {
				if ( 'add' === this.pageAction ) {
					this.slug = this.preSetSlug( newValue );
				}
			},
			'typeSettings.source': function( curr, old ) {
				this.typeSettings.source_type = this.sourceTypeRawOptions[curr].types[0].value;
			}
		},

		computed: {
			isSettingsChanged: function() {
				const fetched = {
					name: this.name,
					slug: this.slug,
					typeSettings: this.typeSettings
				}

				return this.originalData !== JSON.stringify( fetched );
			},

			sourceOptions: function () {
				return Object.values( this.sourceTypeRawOptions ).map( ( item ) => {
					return {
						label: item.label,
						value: item.value,
					}
				} );
			},

			sourceTypeOptions: function () {
				this.usedSourceTypes;

				if ( '' === this.typeSettings.source || ! this.typeSettings.source in this.sourceTypeRawOptions ) {
					return [];
				}

				return this.sourceTypeRawOptions[ this.typeSettings.source ]['types'];
			},

			isFieldsEmpty: function () {

				if ( 0 !== this.typeSettings.fields.length ) {
					return false;
				}

				return true;
			}
		},

		methods: {
			addNewRatingField: function( event ) {
				var field = {
					label: '',
					step: 1,
					max: 100,
				};

				this.typeSettings.fields.push( field );
			},

			cloneRatingField: function( index ) {
				var field    = this.typeSettings.fields[ index ],
					newField = {
						label: field.label + '_copy',
						step: field.step,
						max: field.max,
					};

				this.typeSettings.fields.splice( index + 1, 0, newField );
			},

			deleteRatingField: function( index ) {

				if ( -1 === this.typeSettings.fields.length ) {
					this.$CXNotice.add( {
						message: 'Empty fields',
						type: 'error',
						duration: 5000,
					} );

					return;
				}

				this.typeSettings.fields.splice( index, 1 );
			},

			addTypeHandle: function() {
				let self = this;

				if ( '' === this.name || '' === this.slug ) {
					self.$CXNotice.add( {
						message: 'Name is empty',
						type: 'error',
						duration: 5000,
					} );

					return false;
				}

				if ( '' === this.slug ) {
					self.$CXNotice.add( {
						message: 'Slug is empty',
						type: 'error',
						duration: 5000,
					} );

					return false;
				}

				if ( '' === this.source ) {
					self.$CXNotice.add( {
						message: 'Source is empty',
						type: 'error',
						duration: 5000,
					} );

					return false;
				}

				this.actionProccessing = true;

				wp.apiFetch( {
					method: 'post',
					path: pageConfig.addReviewType,
					data: {
						name: self.name,
						slug: self.slug,
						settings: self.typeSettings,
					},
				} ).then( function( response ) {
					self.actionProccessing = false;

					if ( response.success ) {
						self.$CXNotice.add( {
							message: response.message,
							type: 'success',
							duration: 5000,
						} );

						self.pageAction = 'edit';
						self.slug = self.slug;
						self.updateOriginalData();
					} else {
						self.$CXNotice.add( {
							message: response.message,
							type: 'error',
							duration: 5000,
						} );
					}
				} );
			},

			saveTypeHandle: function() {
				let self = this;

				this.actionProccessing = true;

				wp.apiFetch( {
					method: 'post',
					path: pageConfig.updateReviewType,
					data: {
						name: this.name,
						slug: this.slug,
						settings: this.typeSettings,
					},
				} ).then( function( response ) {
					self.actionProccessing = false;

					if ( response.success ) {
						self.$CXNotice.add( {
							message: response.message,
							type: 'success',
							duration: 5000,
						} );

						self.updateOriginalData();
					} else {
						self.$CXNotice.add( {
							message: response.message,
							type: 'error',
							duration: 5000,
						} );
					}
				} );
			},

			deleteTypeHandle: function() {
				let self = this;

				self.deleteActionProccessing = true;

				wp.apiFetch( {
					method: 'post',
					path: pageConfig.deleteReviewType,
					data: {
						slug: self.slug,
					},
				} ).then( function( response ) {
					self.deleteActionProccessing = false;

					if ( response.success ) {
						self.$CXNotice.add( {
							message: response.message,
							type: 'success',
							duration: 5000,
						} );

						window.open( pageConfig.reviewTypesPageUrl, '_self' );
					} else {
						self.$CXNotice.add( {
							message: response.message,
							type: 'error',
							duration: 5000,
						} );
					}
				} );
			},

			syncRatingData: function() {
				let self = this;

				this.syncSourceMetaStatus = true;

				wp.apiFetch( {
					method: 'post',
					path: pageConfig.syncReviewType,
					data: {
						postType: self.typeSettings.source_type,
					},
				} ).then( function( response ) {
					self.syncSourceMetaStatus = false;

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

			updateOriginalData: function () {
				const fetched = {
					name: this.name,
					slug: this.slug,
					typeSettings: this.typeSettings
				}

				this.originalData = JSON.stringify( fetched );
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

})( jQuery, window.JetReviewsTypePageConfig );
