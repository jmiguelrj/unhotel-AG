(function () {

	"use strict";

	Vue
		.use(Vuex)
		.directive('click-outside', {
			bind: function ( el, binding, vnode ) {
				el.clickOutsideEvent = function (event) {
					let classIgnore = '.flatpickr-calendar';

					if ( ! ( el == event.target || el.contains( event.target ) || event.target.closest( classIgnore ) ) ) {
						vnode.context[ binding.expression ]( event );
					}
				};
				document.body.addEventListener( 'click', el.clickOutsideEvent )
			},
			unbind: function ( el ) {
				document.body.removeEventListener( 'click', el.clickOutsideEvent )
			}
		});

	const
		eventHub = new Vue(),
		{ __, sprintf } = wp.i18n,
		buildQuery = function (params) {
			return Object.keys(params).map(function (key) {
				return key + '=' + params[key];
			}).join('&');
		},
		statusMixin = {
			computed: Vuex.mapState(['statuses_schema']),
			methods: {
				isFinished: function (status) {
					return (0 <= this.statuses_schema.finished.indexOf(status));
				},
				isInProgress: function (status) {
					return (0 <= this.statuses_schema.in_progress.indexOf(status));
				},
				isInvalid: function (status) {
					return (0 <= this.statuses_schema.invalid.indexOf(status));
				},
			}
		},
		itemsMethods = {
			computed: Vuex.mapState(
				{
					edit_link: 'edit_link',
					filters: 'filters',
					labels: state => state.config.labels,
				}),
			methods: {
				getOrderLink: function (orderID) {
					return this.edit_link.replace(/\%id\%/, orderID);
				},
				getItemLabel: function (key) {
					let lable = this.labels[key] ? this.labels[key] : key;

					return lable;
				},
				getItemValue: function (item, propertyName) {
					if( ! item[ propertyName ] ){
						return;
					}

					let value = item[ propertyName ];

					switch (propertyName) {
						case 'provider':
							return this.filters[propertyName].value[value];
						case 'service':
							return this.filters[propertyName].value[value];
						case 'status':
							return window.JetAPBConfig.statuses_list[value] || value;
						default:
							return value;
					}
				},
			}
		},
		dateMethods = {
			methods: {
				parseDate: function ( date, format = 'MM DD YYYY' ) {
					return moment( date ).format( format );
				},
				timestampToDate: function ( timestamp, format = 'MM DD YYYY' ) {
					return moment.unix( timestamp ).utc().format( format );
				},
				timeToTimestamp: function ( time, format = 'hh:mm' ) {
					return moment( time, format ).valueOf() / 1000;
				},
				objectDateToTimestamp: function ( date ) {
					return Date.UTC( date.getFullYear(), date.getMonth(), date.getDate(), date.getHours(), date.getMinutes(), date.getSeconds() ) / 1000;
				},
				dateToTimestamp: function ( date ) {
					let timestamp = Date.UTC( date.getFullYear(), date.getMonth(), date.getDate(), 0, 0, 0 ) / 1000;

					return timestamp;
				},
			}
		};

	const store = new Vuex.Store({
		state() {
			const initialState = {
				...window.JetAPBConfig,
				isLoading: true,
				items: {
					itemsList: [],
					totalItems: 0,
					offset: 0,
					perPage: 15,
					onPage: 0,
					pageNumber: 1,
				},
				curentFilters: {},
				curentView: window.localStorage.getItem( 'jet-apb-appointments-view/curentView' ) || 'list',
				views: {
					list: {
						label: __('List', 'jet-appointments-booking'),
						icon: ''
					},
					calendar: {
						label: __('Calendar', 'jet-appointments-booking'),
						icon: ''
					},
					timeline: {
						label: __('Timeline', 'jet-appointments-booking'),
						icon: ''
					},
				},
				action: {
					name: '',
					content: [],
					appointmentsList:[],
				},
				appSettings: {},
				daySlots: '',
				daySlotsIsLoad: false,
				timeline: {
					selectedDate: moment().format("D MMM YYYY"),
					selectedStartTime: '08:00:00',
					selectedEndTime: '17:00:00',
					timeInterval: 30,
				},
				initialSortBy: window.JetAPBConfig.sortBy,
			};

			if ( window.localStorage.getItem( 'breakByDays' ) ) {
				initialState.config.breakByDays = true;
			} else {
				initialState.break_list_by_dates || false;
			}

			if ( window.localStorage.getItem( 'listDateFormat' ) ) {
				initialState.config.listDateFormat = window.localStorage.getItem( 'listDateFormat' );
			}
			
			return initialState;
		},
		mutations: {
			setValue( state, varObject ) {
				if ( 'sortBy' === varObject.key && 'list' === state.curentView ) {
					store.commit('setValue', {
						key: 'initialSortBy',
						value: state.sortBy,
					});
				}
				if ( 'curentView' === varObject.key ) {
					window.localStorage.setItem( 'jet-apb-appointments-view/curentView', varObject.value );

					if ( 'calendar' === varObject.value ) {
						store.commit('setValue', {
							key: 'sortBy',
							value: {
								orderby: 'slot',
								order: 'ASC',
							}
						});
					} else {
						store.commit('setValue', {
							key: 'sortBy',
							value: state.initialSortBy,
						});
					}
				}
				state[ varObject.key ] = varObject.value;
			},
		},
		actions: {
			setConfig: function (store) {
				let updConfig = {
						filters: {},
					},
					filters = store.state.filters;

				//Set filters
				for (const filter in filters) {
					if (filters.hasOwnProperty(filter)) {
						updConfig.filters[filter] = {
							label: filters[filter].label,
							visibility: filters[filter].visibility,
						}
					}
				}

				store.commit('setValue', {
					key: 'config',
					value: Object.assign({}, store.state.config, updConfig)
				});
			},
			getItems: function (store) {
				
				store.commit('setValue', {
					key: 'isLoading',
					value: true
				});

				let mode = window.location.hash;

				if ( ! mode ) {
					mode = window.JetAPBConfig.defaultMode;
				} else {
					mode = mode.replace( '#', '' );
					let allowed = [ 'all', 'upcoming', 'past' ];
					mode = allowed.includes( mode ) ? mode : 'all';
				}

				let state = store.state,
					searchNotIn = [ 'ID', 'user_id', 'date', 'slot', 'slot_end', 'status', 'order_id', 'actions', 'service', 'provider' ],
					searchIn = state.curentFilters.search ? state.columns.filter( x => ! searchNotIn.includes( x ) ) : false ,
					curentView = state.curentView,
					query = buildQuery({
						per_page: 'list' === curentView ? state.items.perPage : 0,
						offset: 'list' === curentView ? state.items.offset : 0,
						filter: JSON.stringify(state.curentFilters),
						sort: JSON.stringify(state.sortBy),
						search_in: searchIn,
						mode: mode,
						dateFormat: state.config.listDateFormat,
					}),
					queryPath = `${state.api.appointments_list}?${query}`;

				wp.apiFetch({
					method: 'get',
					path: queryPath,
				}).then(function (response) {
					if (response.success) {
						let items = {
							itemsList: response.data,
							totalItems: response.total || 0,
							onPage: response.on_page || 0
						}

						store.commit('setValue', {
							key: 'items',
							value: Object.assign({}, state.items, items)
						});
					}

					store.commit('setValue', {
						key: 'isLoading',
						value: false
					});
				}).catch(function (e) {
					eventHub.$CXNotice.add({
						message: e.message,
						type: 'error',
						duration: 7000,
					});

					store.commit('setValue', {
						key: 'isLoading',
						value: false
					});

				});
			},
			addItem: function ( store ) {
				store.commit('setValue', {
					key: 'isLoading',
					value: true,
				});

				let data,
					{
						action,
						api
					} = store.state;

				if ( action.name !== 'new' || ! action.content ) {
					store.commit('setValue', {
						key: 'isLoading',
						value: false,
					});

					return;
				}

				data = action.appointmentsList.map( function( item ){
					for ( const [ key, value ] of Object.entries( action.content ) ) {
						item[ key ] = value && ! item[ key ] ? value : item[ key ];
					}
					return item;
				} );

				wp.apiFetch({
					method: 'POST',
					path: api.add_appointment,
					data: data,
				}).then(function (response) {
					let message = response.data,
						type = !response.success ? 'error' : 'success';

					eventHub.$CXNotice.add({
						message: message,
						type: type,
						duration: 7000,
					});

					store.dispatch( 'getItems' );
				}).catch(function (e) {
					eventHub.$CXNotice.add({
						message: e.message,
						type: 'error',
						duration: 7000,
					});
				});
			},
			updateItem: function (store) {
				let {
					action,
					items,
					api
				} = store.state;

				if ( action.name !== 'update' || ! action.content ) {
					return;
				}
				store.commit('setValue', {
					key: 'isLoading',
					value: true
				});

				wp.apiFetch({
					method: 'POST',
					path: `${api.update_appointment}${action.content.ID}`,
					data: {item: action.content},
				}).then(function (response) {
					let message = !response.success ? response.data : __('Done!', 'jet-appointments-booking'),
						type = !response.success ? 'error' : 'success';

					eventHub.$CXNotice.add({
						message: message,
						type: type,
						duration: 7000,
					});

					for (const index in items.itemsList) {
						if (items.itemsList.hasOwnProperty(index)) {
							if (action.content.ID === items.itemsList[index].ID) {
								items.itemsList.splice(index, 1, action.content);
							}
						}
					}

					store.commit('setValue', {
						key: 'items',
						value: Object.assign({}, store.state.items, items)
					});

					store.commit('setValue', {
						key: 'isLoading',
						value: false
					});

				}).catch(function (e) {
					eventHub.$CXNotice.add({
						message: e.message,
						type: 'error',
						duration: 7000,
					});

					store.commit('setValue', {
						key: 'isLoading',
						value: false
					});
				});
			},
			deleteItem: function ( store ) {
				let {
					action,
					items,
					api
				} = store.state;

				if ( action.name !== 'delete' && action.name !== 'delete-group' || ! action.content ) {
					return;
				}

				store.commit('setValue', {
					key: 'isLoading',
					value: true
				});

				let data = {
					items: [ action.content.ID ]
				};

				if( action.name === 'delete-group' && action.content.isGroupChief ){
					data.group_ID = action.content.group_ID;
				}

				wp.apiFetch({
					path: api.delete_appointment,
					method: 'POST',
					data: data,
				}).then(function (response) {
					if ( ! response.success ) {
						eventHub.$CXNotice.add({
							message: response.data,
							type: 'error',
							duration: 7000,
						});
					}

					let appointmentIDs = response.appointment_IDs;

					items.itemsList = items.itemsList.filter( function( item ) {
						let appID = Number.parseInt( item.ID );

						return ! appointmentIDs.includes( appID );
					})

					store.commit('setValue', {
						key: 'items',
						value: Object.assign({}, store.state.items, items )
					});

					store.commit('setValue', {
						key: 'isLoading',
						value: false
					});

				}).catch(function (response) {
					eventHub.$CXNotice.add({
						message: response.message,
						type: 'error',
						duration: 7000,
					});

					store.commit('setValue', {
						key: 'isLoading',
						value: false
					});
				});
			},
			getDaySlot: function ( store, args) {
				store.commit( 'setValue', {
					key: 'daySlotsIsLoad',
					value: true
				} );

				let {
						api
					} = store.state,
					{
						date,
						date_timestamp,
						provider,
						service,
					} = args,
					dateNow = new Date();

				if( ! date_timestamp || ! service ){
					store
						.commit( 'setValue', {
							key: 'daySlots',
							value: ''
						} )
						.commit( 'setValue', {
							key: 'daySlotsIsLoad',
							value: false
						} );

					return;
				}

				wp.apiFetch({
					method: 'POST',
					path: api.date_slots,
					data: {
						service: service,
						provider: provider,
						date: date_timestamp,
						selected_slots: JSON.stringify( store.state.action.appointmentsList ),
						timestamp: Math.floor( ( dateNow.getTime() - dateNow.getTimezoneOffset() * 60 * 1000 ) / 1000 ),
						admin: true,
					},
				}).then( function (response) {

					if( ! response.data ){
						store.commit( 'setValue', {
							key: 'daySlots',
							value: ''
						} );
					} else {
						store.commit( 'setValue', {
								key: 'daySlots',
								value: response.data
							} );
						store.commit( 'setValue', {
							key: 'appSettings',
							value: {
								...response.data.settings,
								booking_type:response.data.booking_type
							}
						} );
					}

					store.commit( 'setValue', {
						key: 'daySlotsIsLoad',
						value: false
					} );
				}).catch(function (e) {
					eventHub.$CXNotice.add({
						message: e.message,
						type: 'error',
						duration: 7000,
					});

					store.commit( 'setValue', {
						key: 'daySlotsIsLoad',
						value: false
					} );
				});
			}
		}
	})

	Vue.component('jet-apb-appointments-config', {
		template: '#jet-apb-config',
		data: function () {
			return {
				configVisible: false,
			};
		},
		computed:{
			groupView: {
				get: function () {
					return this.$store.state.config.groupView
				},
				set: function ( value ) {
					store.commit('setValue', {
						key: 'config',
						value: Object.assign( {}, this.$store.state.config, { groupView: value } )
					});
				}
			},
			breakByDays: {
				get: function () {
					return this.$store.state.config.breakByDays
				},
				set: function ( value ) {

					if ( value ) {
						window.localStorage.setItem( 'breakByDays', 1 );
					} else {
						window.localStorage.removeItem( 'breakByDays' );
					}

					store.commit( 'setValue', {
						key: 'config',
						value: Object.assign( {}, this.$store.state.config, { breakByDays: value } )
					});
				}
			},
			listDateFormat: {
				get: function () {
					return this.$store.state.config.listDateFormat
				},
				set: function ( value ) {

					if ( value ) {
						window.localStorage.setItem( 'listDateFormat', value );
					} else {
						window.localStorage.setItem( 'listDateFormat', 'd/m/y' );
					}

					store.commit( 'setValue', {
						key: 'config',
						value: Object.assign( {}, this.$store.state.config, { listDateFormat: value } )
					});
				}
			},
			...Vuex.mapState({
				filters: state => state.config.filters,
				labels: state => state.config.labels,
			})
		},
		methods: {
			hidePopUp: function () {
				this.configVisible = false;
			},
			
			dateFormat: function(){
				this.$store.dispatch('getItems');
			},
			maybeRemoveFilter: function(value, filter) {
				if ( ! value &&  this.$store.state.curentFilters[filter] ) {
					delete this.$store.state.curentFilters[filter];
					store.dispatch('getItems');
				}
			},
		   /* updateView: function (value) {
				store.commit('setValue', {
					key: 'curentView',
					value: value
				});
			},*/
		},
	});

	Vue.component('jet-apb-appointments-filter', {
		template: '#jet-apb-appointments-filter',
		mixins: [ dateMethods ],
		components: {
			vuejsDatepicker: window.vuejsDatepicker,
		},
		computed: {
			...Vuex.mapState(['curentFilters', 'filters', 'config', 'items', 'curentView' ]),
			hideFilters: function () {
				for( let key in this.config.filters ) {
					let filter = this.config.filters[ key ];
					if( filter.visibility && filter.label !== 'Search' ){
						return false;
					}
				}

				return true;
			},
		},
		data: function () {
			return {
				dateFormat: 'dd/MM/yyyy',
				expandFilters: false,
				currentMode: 'all',
				showExportPopup: false,
				exportType: 'all',
				exportFormat: 'csv',
				exportDataReturnType: 'id',
				exportDateFormat: 'Y-m-d',
				exportTimeFormat: 'H:i',
				nonce: window.JetAPBConfig.export_nonce,
				exportBase: window.JetAPBConfig.export_url
			};
		},
		created: function() {
			this.currentMode = this.getCurrentMode();
		},
		methods: {
			doExport: function() {

				let urlParts = {
					type: this.exportType,
					format: this.exportFormat,
					return: this.exportDataReturnType,
					date_format: this.exportDateFormat,
					time_format: this.exportTimeFormat,
					_nonce: this.nonce,
				};

				if ( 'filtered' === this.exportType ) {

					let searchNotIn = [ 'ID', 'user_id', 'date', 'slot', 'slot_end', 'status', 'order_id', 'actions', 'service', 'provider' ];
					let searchIn = this.$store.state.curentFilters.search ? this.$store.state.columns.filter( x => ! searchNotIn.includes( x ) ) : false;

					urlParts = { ...urlParts, ...{
						filter: JSON.stringify( this.$store.state.curentFilters ),
						sort: JSON.stringify( this.$store.state.sortBy ),
						per_page: 0,
						search_in: searchIn,
						mode: this.getCurrentMode(),
					} }
				}

				window.location = this.exportBase + '&' + buildQuery( urlParts );

			},
			getCurrentMode: function() {
				let hash = window.location.hash;

				if ( ! hash ) {
					return window.JetAPBConfig.defaultMode;
				} else {
					hash = hash.replace( '#', '' );
					let allowed = [ 'all', 'upcoming', 'past' ];
					return allowed.includes( hash ) ? hash : 'all';
				}

			},
			setMode( mode ) {

				window.location.hash = '#' + mode;
				this.currentMode = mode;

				store.commit( 'setValue', {
					key: 'items',
					value: Object.assign( {}, this.items, {
						offset: 0,
						pageNumber: 1
					})
				});

				store.dispatch( 'getItems' );

			},
			modeButtonStyle( mode ) {
				return this.currentMode === mode ? 'accent' : 'link-accent';
			},
			updateFilters: function (value, name, type) {
				let filterValue = value.target ? value.target.value : value;

				switch (type) {
					case 'date-picker':
						filterValue = value ? this.parseDate( filterValue, 'MMMM DD YYYY' ) : '';
						break;
				}

				let newFilters = Object.assign({}, this.curentFilters, {[name]: filterValue}),
					items = Object.assign({}, this.items, {offset: 0});

				store.commit('setValue', {
					key: 'curentFilters',
					value: newFilters
				});

				store.commit('setValue', {
					key: 'items',
					value: items
				});

				store.dispatch('getItems');
			},

			clearFilter: function () {
				store.commit('setValue', {
					key: 'curentFilters',
					value: {}
				});

				store.dispatch('getItems');
			},

			isVisible: function (id, filter, type) {
				if (!this.config.filters[id].visibility || 'search' === id ) {
					return false;
				}

				if (type !== filter.type) {
					return false;
				}

				switch (true) {
					case 'select' === filter.type && !Object.keys(filter.value).length :
						return false;
						break;

					case 'date-picker' === filter.type && 'list' !== this.curentView :
						return false;
						break;
				}

				return true;
			},

			prepareObjectForOptions: function (input) {
				let result = [{
					'value': '',
					'label': wp.i18n.__('Select...', 'jet-appointments-booking'),
				}];

				for (let value in input) {
					if (input.hasOwnProperty(value)) {
						result.push({
							'value': value,
							'label': input[value],
						});
					}
				}

				return result;
			},
			checkActiveFilters: function() {
				switch ( this.curentView ) {
					case 'timeline' :
					case 'list' :
						if ( Object.keys(this.curentFilters).length === 0 || ( Object.keys(this.curentFilters).length === 1 && this.curentFilters.date === '' ) ) {
							return false;
						}
						break;
					case 'calendar' :
						if ( Object.keys(this.curentFilters).length === 0 || ( Object.keys(this.curentFilters).length === 1 && this.curentFilters.date )  ) {
							return false;
						} 
						break;
				}
				return true;
			},
		},
	});

	Vue.component('jet-apb-pagination', {
		template: '#jet-apb-pagination',
		data: function () {
			return {
				prevClasses: 'jet-apb-pre-page',
				nextClasses: 'jet-apb-pre-page',
			};
		},
		computed: {
			perPage: {
				get: function () {
					return this.$store.state.items.perPage;
				},
				set: function (value) {
					let newValue = Math.abs(value);

					newValue = newValue <= 0 || newValue > 1000 ? 25 : parseInt(newValue);

					this.$store.commit('setValue', {
						key: 'items',
						value: Object.assign({}, this.items, {
							perPage: newValue,
							pageNumber: 1,
							offset: 0
						})
					});

					this.$store.dispatch('getItems');
				}
			},
			...Vuex.mapState({
				items: state => state.items,
				pageNumber: state => state.items.pageNumber,
				totalItems: state => state.items.totalItems,
				perPageInfo: function (state) {
					let total = state.items.totalItems,
						from = total ? state.items.offset + 1 : 0,
						to = total ? state.items.offset + state.items.onPage : 0;

					return __(`Showing ${from} - ${to} of ${total} results`, 'jet-appointments-booking');
				}
			})
		},
		methods: {
			changePage: function (value) {
				let offset = this.perPage * (value - 1);

				store.commit('setValue', {
					key: 'items',
					value: Object.assign({}, this.items, {
						offset: offset,
						pageNumber: value
					})
				});

				store.dispatch('getItems');
			}
		},
	});

	Vue.component('jet-apb-appointments-view', {
		template: '#jet-apb-appointments-view',
		computed: Vuex.mapState([ 'views', 'curentView', 'curentFilters', 'items' ]),
		methods: {
			updateView: function (value) {
				store.commit('setValue', {
					key: 'curentView',
					value: value
				});
				if ( 'calendar' !== store.state.curentView ) {
					let newFilters = Object.assign({}, this.curentFilters, { 'date': '' });

					store.commit('setValue', {
						key: 'curentFilters',
						value: newFilters
					});
	
					store.dispatch('getItems');
				}
			},
		},
	});

	Vue.component('jet-apb-appointments-list', {
		template: '#jet-apb-appointments-list',
		mixins: [statusMixin, itemsMethods],
		data: function () {
			return {
				curentSort: window.JetAPBConfig.sortBy.orderby,
				notSortable: [
					'actions',
					'slot_end'
				],
				groupChiefColumnsIDs: [
					'user_name',
					'user_email',
					'user_id',
					'status',
					'order_id',
					'actions',
				],
				groupItemsCount:[],
				openGroup:[],
			}
		},
		mounted: function() {
			
			let itemID = null;

			if ( window.location.hash && window.location.hash.includes( '#info-' ) ) {
				itemID = window.location.hash.replace( '#info-', '' );
				itemID = parseInt( itemID, 10 );
			}

			if ( itemID ) {

				wp.apiFetch({
					method: 'get',
					path: this.$store.state.api.get_appointment + itemID,
				}).then( ( response ) => {
					if ( response.success && response.item ) {
						this.callPopup( 'info', response.item );
					}

				}).catch(function (e) {
				
					eventHub.$CXNotice.add({
						message: e.message,
						type: 'error',
						duration: 7000,
					});

				});

				
			}
			
		},
		computed: Vuex.mapState({
			sortBy: 'sortBy',
			groupView: state => state.config.groupView,
			breakByDays: state => state.config.breakByDays,
			listDateFormat: state => state.config.listDateFormat,
			itemsList: function ( state ) {
				let itemsList = state.items.itemsList,
					outputItems;

				if( state.config.groupView ){
					let groupID = false;
					this.groupItemsCount = [];

					outputItems = itemsList.sort( function ( item1, item2 ) {
						return item1.group_ID < item2.group_ID;
					} )

					for ( let item of outputItems ){
						let index = outputItems.indexOf( item );

						if( null !== item.group_ID && item.group_ID !== groupID ){
							item.isGroupChief = true;
							groupID = item.group_ID;
						}

						if( null !== item.group_ID ){
							this.groupItemsCount[ item.group_ID ] = this.groupItemsCount[ item.group_ID ] ? parseInt( this.groupItemsCount[ item.group_ID ] ) + 1 : 1 ;
						}
					}

				} else{
					outputItems = itemsList;
				}

				return outputItems;
			},
			groupColumnsIDs: function () {
				return [];
			},
			columnsIDs: function (state) {
				let columnsIDs = [...state.columns, 'actions'],
					columnsVisibility = state.config.columnsVisibility;

				columnsIDs = columnsIDs
					.sort( function ( item1, item2 ) {
						return columnsVisibility.indexOf(item1) - columnsVisibility.indexOf(item2);
					} )
					.filter( item => columnsVisibility.includes(item) );

				return columnsIDs;
			},
		}),
		methods: {
			sortColumn: function (column) {
				if (this.notSortable.includes(column)) {
					return false;
				}

				this.curentSort = column;

				let newSortBy = {
					orderby: column,
					order: "DESC" === this.sortBy.order ? "ASC" : "DESC"
				};

				store.commit('setValue', {
					key: 'sortBy',
					value: newSortBy
				});

				store.dispatch('getItems');
			},
			showGroup( id ){
				if( ! this.openGroup.includes( id ) ){
					this.openGroup.push( id );
				}else{
					let index = this.openGroup.indexOf( id );

					this.openGroup.splice( index, 1 );
				}
			},
			classColumn: function ( column ) {
				return {
					'list-table-heading__cell-content': true,
					'list-table-heading__cell-clickable': !this.notSortable.includes(column),
					'jet-apb-active-column': column === this.curentSort,
					'jet-apb-active-column-asc': column === this.curentSort && "DESC" === this.sortBy.order,
					'jet-apb-active-column-desc': column === this.curentSort && "ASC" === this.sortBy.order,
				};
			},
			classItem: function ( ID ) {
				return {
					'list-table-item': true,
					'list-table-item__in-group': this.groupView && ID,
					'list-table-item__hide': this.groupView && ID && ! this.openGroup.includes( ID ),
				};
			},
			openPopupOnColumnClick( column, item ) {

				const allowedColumns = [
					'ID',
					'service',
					'provider',
					'user_name',
					'user_email',
					'user_id',
					'date',
					'slot',
					'slot_end',
					'status'
				];
				
				if ( allowedColumns.includes( column ) ) {
					this.callPopup( 'info', item );
				}
			},
			classGroupChief: function ( ID ) {
				return {
					'list-table-item': true,
					'list-table-item__group-chief': true,
					'list-table-item__group-chief-open': ID && this.openGroup.includes( ID ),
				};
			},
			callPopup: function ( state = false, item = false ) {

				eventHub.$emit( 'call-popup', {
					state: state,
					item: item,
				})
			},
		},
	});

	Vue.component('jet-apb-appointments-calendar', {
		template: '#jet-apb-appointments-calendar',
		mixins: [ statusMixin, itemsMethods, dateMethods ],
		data() {
			return {
				masks: {
					weekdays: 'WWW',
				},
				maxItemInCell: 7,
			}
		},
		components: {
			vCalendar: window.vCalendar,
		},
		computed: Vuex.mapState({
			columns: state => state.columns,
			curentFilters: state => state.curentFilters,
			itemsList: function (state) {
				let salf = this;
				
				store.commit('setValue', {
					key: 'sortBy',
					value: {
						orderby: 'slot',
						order: 'ASC',
					}
				});

				return state.items.itemsList.map( function (item, index) {
					return {
						key: index,
						dates: salf.timestampToDate( item.date_timestamp, "MMM DD YYYY" ),
						customData: {
							...item,
						},
					}
				} );
			}
		}),
		methods: {
			changeDate: function ( page ) {
				let monthName = moment().month( page.month - 1 ).format('MMMM'),
					filterValue = `1 ${ monthName } ${ page.year }-31 ${ monthName } ${ page.year }`,
					newFilters = Object.assign({}, this.curentFilters, { ['date']: filterValue });

				store.commit('setValue', {
					key: 'curentFilters',
					value: newFilters
				});

				store.dispatch('getItems');
			},
			callPopup: function ( state = false, item = false ) {
				eventHub.$emit('call-popup', {
					item: item,
					state: state,
				})
			},
			containerClass: function ( attributes ) {
				return {
					'jet-apb-calendar-day': true,
					'jet-apb-calendar-day-full': attributes && attributes.length > this.maxItemInCell ? true : false ,
					'jet-apb-calendar-day-not-full': attributes && attributes.length < this.maxItemInCell ? true : false ,
				}
			},
			getRemainingItemCount: function ( attributes ) {
				return attributes && attributes.length > this.maxItemInCell ? attributes.length - this.maxItemInCell : '';
			},
		},
	});

	Vue.component('jet-apb-appointments-timeline', {
		template: '#jet-apb-appointments-timeline',
		mixins: [ itemsMethods, dateMethods ],
		components: {
			vuejsDatepicker: window.vuejsDatepicker,
		},
		data: function () {
			return {
				dateFormat: 'd/MM/yyyy',
				intervalOptions: [
					{
						label: __('15 minutes', 'jet-appointments-booking'),
						value: 15,
					},
					{
						label: __('30 minutes', 'jet-appointments-booking'),
						value: 30,
					},
					{
						label: __('1 hours', 'jet-appointments-booking'),
						value: 60,
					},
				],
				timelines: [{time: '2021-04-22 19:30:00', color: '#ffeb00'}],
				cellHeight: 20,
				cellWidth: 210,
				titleHeight: 44,
				titleWidth: 210,
				itemsTimeDiapazon:{}
			};
		},
		computed: {
			startTime: function () {
				let selectedDate = this.parseDate( this.selectedDate, "YYYY-MM-DD" ),
					startTime = `${ selectedDate } ${this.selectedStartTime}`;

				return startTime;
			},
			endTime: function () {
				let selectedDate = this.parseDate( this.selectedDate, "YYYY-MM-DD" ) /*this.timeInterval > 30 ? moment( this.selectedDate ).add( 1, 'days' ) :*/,
					endTime = `${ selectedDate } ${ this.selectedEndTime }`;

				return endTime;
			},
			selectedDate: {
				get: function () {
					return this.$store.state.timeline.selectedDate
				},
				set: function (value) {
					store.commit('setValue', {
						key: 'timeline',
						value: Object.assign({}, this.$store.state.timeline, {selectedDate: value})
					});
				}
			},
			selectedStartTime: {
				get: function () {
					return this.$store.state.timeline.selectedStartTime;
				},
				set: function (value) {
					let endTime = this.$store.state.timeline.selectedEndTime,
						newStartTime = this.timeToTimestamp(value) >= this.timeToTimestamp(endTime) ? '00:00' : value;

					store.commit('setValue', {
						key: 'timeline',
						value: Object.assign({}, this.$store.state.timeline, {selectedStartTime: newStartTime})
					});
				}
			},
			selectedEndTime: {
				get: function () {
					return this.$store.state.timeline.selectedEndTime;
				},
				set: function (value) {
					let startTime = this.$store.state.timeline.selectedStartTime,
						newEndTime = this.timeToTimestamp(value) <= this.timeToTimestamp(startTime) ? '23:59' : value;

					store.commit('setValue', {
						key: 'timeline',
						value: Object.assign({}, this.$store.state.timeline, {selectedEndTime: newEndTime})
					});
				}
			},
			timeInterval: {
				get: function () {
					return this.$store.state.timeline.timeInterval
				},
				set: function (value) {
					store.commit('setValue', {
						key: 'timeline',
						value: Object.assign({}, this.$store.state.timeline, {timeInterval: value})
					});
				}
			},
			...Vuex.mapState({
				itemsList: function (state) {
					let itemsList = [],
						salf = this;

					state.items.itemsList.forEach( function ( item, index ) {
						let serviceID = parseInt(item.service);

						if ( ! itemsList[ serviceID ] ) {
							itemsList[serviceID] = {
								id: serviceID,
								service: salf.getItemValue(item, 'service'),
								gtArray: [],
							}
						}

						itemsList[serviceID].gtArray.push(
							{
								provider: salf.getItemValue(item, 'provider'),
								id: parseInt( item.ID ),
								start: salf.timestampToDate( item.slot_timestamp, "YYYY-MM-DD HH:mm:ss" ),
								end: salf.timestampToDate( item.slot_end_timestamp, "YYYY-MM-DD HH:mm:ss" ),
								index: index,
								itemData:{
									...item,
								}
							}
						);
					})

					itemsList = itemsList.filter(item => item);

					return itemsList;
				}
			}),
		},
		methods: {
			changeDate: function ( date ) {
				let filterValue = this.parseDate(date, 'DD MMMM YYYY'),
					newFilters = Object.assign({}, this.curentFilters, { ['date']: filterValue });

				store.commit('setValue', {
					key: 'curentFilters',
					value: newFilters
				});

				store.dispatch('getItems');
			},
			leftSidebarStyle: function () {
				return {
					flex: `0 1 ${ this.titleWidth }px`,
				}
			},
			itemVisible: function ( startItemTime = false, endItemTime = false ) {
				let startTimeStamp = this.timeToTimestamp( this.startTime, 'YYYY-MM-DD HH:mm' ),
					endTimeStamp = this.timeToTimestamp( this.endTime, 'YYYY-MM-DD HH:mm' ),
					startItemTimeStamp  = this.timeToTimestamp( startItemTime, 'YYYY-MM-DD HH:mm' );

				if(
					startItemTimeStamp < startTimeStamp ||
					startItemTimeStamp > endTimeStamp
				){
					return false;
				}

				return true;
			},
			itemsWrapperStyle: function () {
				let startTimeStamp = this.timeToTimestamp( this.startTime, 'YYYY-MM-DD HH:mm' ) / 60,
					endTimeStamp = this.timeToTimestamp( this.endTime, 'YYYY-MM-DD HH:mm' ) / 60,
					columnsCount = ( endTimeStamp - startTimeStamp ) / this.timeInterval;

				return {
					'background-size': `${ this.cellWidth }px`,
					'grid-template-columns': `repeat(${ columnsCount }, ${ this.cellWidth }px)`,
				}
			},
			itemStyle: function ( startItemTime = false, endItemTime = false ) {
				if (! startItemTime && ! endItemTime ){
					return;
				}

				let startTimeStamp = this.timeToTimestamp( this.startTime, 'YYYY-MM-DD HH:mm' ) / 60,
					startItemStamp = this.timeToTimestamp( startItemTime, 'YYYY-MM-DD HH:mm:ss' ) / 60,
					endItemStamp = this.timeToTimestamp( endItemTime, 'YYYY-MM-DD HH:mm:ss' ) / 60,
					endTimeStamp = this.timeToTimestamp( this.endTime, 'YYYY-MM-DD HH:mm' ) / 60,
					starPosition = startItemStamp - startTimeStamp,
					endPosition =( endItemStamp - startTimeStamp ) / this.timeInterval,
					columnsCount = ( endTimeStamp - startTimeStamp ) / this.timeInterval;

				if( starPosition < 0 ){
					return;
				}

				starPosition = starPosition === 0 ? starPosition + 1 : starPosition / this.timeInterval +1;
				endPosition = endPosition < columnsCount ? endPosition + 1 : columnsCount ;

				return {
					'grid-column-start': starPosition,
					'grid-column-end': endPosition,
				}
			},
			callPopup: function (state = false, item = false) {
				eventHub.$emit('call-popup', {
					item: item,
					state: state,
				})
			},
		}
	});

	Vue.component('jet-apb-popup', {
		template: '#jet-apb-popup',
		components: {
			vuejsDatepicker: vuejsDatepicker,
			flatPickr: VueFlatpickr,
		},
		mixins: [ statusMixin, itemsMethods, dateMethods ],
		data() {

			let today = new Date();
			today.setDate( today.getDate() - 1 );

			return {
				isShow: false,
				popUpState: '', //delete, new, update, info
				dateFormat: 'dd-MM-yyyy',
				disabledDates: { to: today },
				datePickerVisibility: false,
				metaFields: [],
				providersAPI: this.$store.state.providers_api,
				datesAPI: this.$store.state.api.refresh_dates,
				provider: {},
				refreshingProviders: false,
				recurrencConfig: {
					type: '',
					count: this.$store.state.appSettings.min_recurring_count || 1,
					weekDayChecked: {},
				}
			}
		},
		computed: Vuex.mapState({
			action: 'action',
			edit_link: 'edit_link',
			daySlotsIsLoad: 'daySlotsIsLoad',
			daySlots: state => state.daySlots.slots,
			appSettings: state => state.appSettings,
			status: state => state.filters.status.value,
			service: state => state.filters.service.value,
			minBooking: state => state.multi_booking_settings.min,
			maxBooking: state => state.multi_booking_settings.max,
			content: state => state.action.content,
			rangeSettings:  function ( state ) {
				return {
					enableTime: true,
					noCalendar: true,
					dateFormat: state.appSettings.time_format,
					altFormat: state.appSettings.time_format,
					time_24hr: state.appSettings.time_24hr,
					position: state.appSettings.position,
					hourIncrement: state.appSettings.hour_increment,
					minuteIncrement: state.appSettings.minute_increment,
					defaultDate: new Date( state.appSettings.default_date ),
					endTime: new Date( state.appSettings.end_time ),
					minTime: state.appSettings.min_max_time.min,
					maxTime: state.appSettings.min_max_time.max,
					endMinTime: state.appSettings.min_max_time.end_min,
					endMaxTime: state.appSettings.min_max_time.end_max,

				};
			},
			multiBooking:  function ( state ) {
				return ( state.appSettings.booking_type !== 'slot' ) ? false : state.multi_booking_settings.multi_booking ;
			},
			recurrencTypes: function ( state ) {
				let output = [{
						'value': '',
						'label': __('Select...', 'jet-appointments-booking'),
					}],
					types = state.appSettings.recurrence_types;

				for ( let type of types ) {
					output.push({
						'value': type,
						'label': state.appSettings.recurrence_types_label[ type ],
					});
				}

				return output;
			},
			weekDays: function ( state ) {
				let output = [],
					weekDays = state.appSettings.work_week_days;

				for ( let day of weekDays ) {
					output.push({
						'value': state.appSettings.week_day_order[ day ],
						'label': state.appSettings.week_days_name[ day ],
					});
				}

				return output;
			},
			fields: function (state) {
				let fields = state.columns,
					fieldsSequence = state.items_sequence;

				fields = fields
					.sort( function ( item1, item2 ) {
						return fieldsSequence.indexOf(item1) - fieldsSequence.indexOf(item2);
					} );

				return fields;
			},
		}),
		mounted: function () {
			eventHub.$on('cancel-popup', this.cancelPopup);
			eventHub.$on('call-popup', this.callPopup);
			

		},
		methods: {
			hasMetaFields() {
				let allowMeta = [ 'update', 'info' ]
				return allowMeta.includes( this.popUpState );
			},
			callPopup( { state, item } ) {
				
				let content = ! item.ID && state !== 'new' ? false : Object.assign( {}, item );

				if ( ! content ) {
					this.cancelPopup();
				}

				this.isShow = true;
				this.popUpState = state;

				if ( this.hasMetaFields() ) {
					wp.apiFetch( {
						method: 'get',
						path: this.$store.state.api.appointment_meta + item.ID,
					} ).then( ( response ) => {
					
						if ( response.success ) {
							this.metaFields = response.fields;
						}

					} ).catch( ( e ) => {
						eventHub.$CXNotice.add( {
							message: e.message,
							type: 'error',
							duration: 7000,
						} );
					} );
				}

				store.commit('setValue', {
					key: 'action',
					value: Object.assign( {}, this.action, { name: this.popUpState, content: content } )
				});

				if ( this.action.content.service ) {
					this.refreshProviders( this.action.content.service );
				}
			},
			cancelPopup: function () {
				this.isShow = false ;

				window.location.hash = '';

				store.commit('setValue', {
					key: 'action',
					value: {
						name: false,
						content: false,
						appointmentsList: [],
					}
				});
			},
			deleteItem: function () {
				this.popUpState = this.action.name !== 'info' ? this.action.name : 'delete' ;

				store.commit('setValue', {
					key: 'action',
					value: Object.assign( {}, this.action, { name: this.popUpState } )
				});

				store.dispatch( 'deleteItem' );
				this.cancelPopup();
			},
			editItem: function () {
				this.popUpState = 'update';

				store.commit('setValue', {
					key: 'action',
					value: Object.assign( {}, this.action, { name: this.popUpState } )
				});
			},
			updateItem: function () {
				if( ! this.checkEmptyFields() ){
					return;
				}

				store.dispatch( 'updateItem' );
				this.cancelPopup();
			},
			addNewItem: function () {
				if( ! this.checkEmptyFields() ){
					return;
				}
				if( this.checkMinSlotCount() ){
					return;
				}
				store.dispatch( 'addItem' );

				this.cancelPopup();
			},
			changeValue: function ( value, key, fieldType = '' ) {
				switch (fieldType) {
					case 'date-picker':
						let timestampValue = this.dateToTimestamp( value );

						value = value ? this.parseDate( value, 'DD/MM/YYYY' ) : '';
						this.action.content[ `${key}_timestamp` ] = timestampValue;
						this.action.content[ key ] = value;

						store.dispatch( 'getDaySlot', this.action.content );
					break;
					case 'slot':

						this.action.content[ `${key}_timestamp` ] = value.from;
						this.action.content[ `${key}_end_timestamp` ] = value.to;

						this.action.content[ key ] = this.timestampToDate( value.from, 'HH:mm' );
						this.action.content[ `${key}_end` ] = this.timestampToDate( value.to, 'HH:mm' );

						if( this.multiBooking ) {
							this.addToAppointmentsList();
						} else {

							this.addToAppointmentsList();

							if( ! this.multiBooking || this.popUpState === "update" ){
								this.hideDatepicker();
							}
	
							if( this.appSettings.booking_type === 'recurring' || this.popUpState === "new" ){
								this.repeatApps();
							}
							
						}

					break;

					case 'rangeSlotStart':
					case 'rangeSlotEnd':

						let timePickerStart = document.getElementsByClassName( 'jet-apb__time-picker-start' ),
							timePickerEnd   = document.getElementsByClassName( 'jet-apb__time-picker-end' ),
							start = timePickerStart[0] ? timePickerStart[0]._flatpickr.selectedDates[0] : false ,
							end = timePickerEnd[0] ? timePickerEnd[0]._flatpickr.selectedDates[0] : false ,
							timeRange = this.calcTimeRange( fieldType, start, end );

							if( fieldType === 'rangeSlotEnd' ){
								timePickerStart[0]._flatpickr.setDate( timeRange.slot, false );
							}else{
								if( ! this.appSettings.only_start ){
									timePickerEnd[0]._flatpickr.setDate( timeRange.slotEnd, false );
								}
							}

							this.action.content[ 'slot_timestamp' ]     = this.objectDateToTimestamp( timeRange.slot );
							this.action.content[ 'slot_end_timestamp' ] = this.objectDateToTimestamp( timeRange.slotEnd );

							this.action.content[ 'slot' ]     = this.timestampToDate( this.action.content[ 'slot_timestamp' ], 'HH:mm' );
							this.action.content[ 'slot_end' ] = this.timestampToDate( this.action.content[ 'slot_end_timestamp' ], 'HH:mm' );

							this.addToAppointmentsList();
					break;
					default:
						this.action.content[ key ] = value;
					break;
				}

				switch (key) {
					case 'provider':
					case 'service':
						this.action.content = Object.assign(
							{},
							this.action.content,
							{
								'date': '',
								'date_timestamp': '',
								'slot': '',
								'slot_timestamp': '',
								'slot_end': '',
								'slot_end_timestamp': '',
							}
						);

						if( ! store.state.multi_booking_settings.multi_booking ) {
							this.action.appointmentsList = [];
						}

						this.refreshDates();

					break;
				}

				// refresh providers list on service update
				if ( 'service' === key ) {
					this.action.content.provider = '';
					this.refreshProviders( value );
				}
			},
			refreshDates() {

				let queryString = '?service=' + this.action.content.service + '&provider=' + this.action.content.provider;

				wp.apiFetch({
					method: 'get',
					path: this.datesAPI + queryString,
				}).then( ( response ) => {

					if ( response.success ) {

						const hasWorksDates = response.data.worksDates && response.data.worksDates.length > 0;

						if ( response.data.excludedWeekDaysIndex && ! hasWorksDates ) {
							this.$set( this.disabledDates, 'days', response.data.excludedWeekDaysIndex );
						} else {
							this.$set( this.disabledDates, 'days', [] );
						}

						if ( response.data.excludedDates && response.data.excludedDates.length ) {
							
							let excludedRanges = [];
							let excludedDates  = [];

							for ( var i = 0; i < response.data.excludedDates.length; i++ ) {

								let offset = new Date( response.data.excludedDates[ i ].start * 1000 ).getTimezoneOffset() * 60 * 1000;

								if ( response.data.excludedDates[ i ].start < response.data.excludedDates[ i ].end ) {

									let exludeToDate = new Date( response.data.excludedDates[ i ].end * 1000 + offset );

									exludeToDate.setHours( 23, 59, 59 );

									excludedRanges.push( {
										from: new Date( response.data.excludedDates[ i ].start * 1000 + offset ),
										to: exludeToDate,
									} );
								} else {
									if( ( ! response.data.excludedDates[ i ].service || response.data.excludedDates[ i ].service ==  this.action.content.service ) &&
										( ! response.data.excludedDates[  i].provider || response.data.excludedDates[ i ].provider ==  this.action.content.provider ) ) {
										excludedDates.push( new Date( response.data.excludedDates[ i ].start * 1000 + offset ) );
									}
									
								}
								
							}

							if ( excludedRanges.length ) {
								this.$set( this.disabledDates, 'ranges', excludedRanges );
							}

							if ( excludedDates.length ) {
								this.$set( this.disabledDates, 'dates', excludedDates );
							}

						} else {
							this.$set( this.disabledDates, 'ranges', [] );
							this.$set( this.disabledDates, 'dates', [] );
						}

						if ( response.data.datesRange && response.data.datesRange.end ) {
							this.$set( this.disabledDates, 'from', new Date( response.data.datesRange.end * 1000 ) );
						}

						if ( hasWorksDates ) {

							const predictWorksDatesRange = function( date ) {

								let isDateInRange = false;

								for ( var i = 0; i < this.worksDates.length; i++ ) {

									let offset     = new Date( this.worksDates[ i ].start * 1000 ).getTimezoneOffset() * 60 * 1000;
									let startRange = new Date( this.worksDates[ i ].start * 1000 + offset );
									let endRange   = new Date( this.worksDates[ i ].end * 1000 + offset );

									endRange.setHours( 23, 59, 59 );

									if ( startRange <= date && date <= endRange ) {
										isDateInRange = true;
									}
									
								}

								// always allow dates in given range
								if ( isDateInRange ) {
									return false;
								} else {
									if ( 'override_full' === this.datesMode ) {
										return true;
									} else if ( this.excludedWeekDaysIndex && this.excludedWeekDaysIndex.length ) {
										return this.excludedWeekDaysIndex.includes( date.getDay() );
									}
								}

							}

							this.$set( this.disabledDates, 'customPredictor', predictWorksDatesRange.bind( response.data ) );
						} else {
							this.$set( this.disabledDates, 'customPredictor', null );
						}

					}

				}).catch( ( e ) => {

					eventHub.$CXNotice.add( {
						message: e.message,
						type: 'error',
						duration: 7000,
					} );

				});
			},
			refreshProviders( service ) {

				this.refreshingProviders = true;

				wp.apiFetch({
					method: 'get',
					path: this.providersAPI + '?service=' + service,
				}).then( ( response ) => {

					if ( response.success ) {

						this.provider = {};

						for ( var i = 0; i < response.data.length; i++ ) {
							this.$set( this.provider, response.data[ i ].ID, response.data[ i ].post_title );
						}

					}

					this.refreshingProviders = false;

				}).catch( ( e ) => {
					
					eventHub.$CXNotice.add( {
						message: e.message,
						type: 'error',
						duration: 7000,
					} );

					this.refreshingProviders = false;

				});

			},
			isControlDisabled( key ) {

				if ( 'provider' === key && this.refreshingProviders ) {
					return true;
				}

				return false;

			},
			calcTimeRange ( type = 'start', startDate = false, endDate = false, ){
				let duration    = parseInt( this.appSettings.duration ),
					maxDuration = parseInt( this.appSettings.max_duration ),
					output = {
						slot: startDate.getTime() / 1000 ,
						slotEnd: ! endDate ? duration : endDate.getTime() / 1000
					};

				if( type === 'rangeSlotStart' ) {
					if( ! endDate || output.slotEnd - output.slot < duration ){
						output.slotEnd = output.slot + duration;
					} else if( output.slotEnd - output.slot > maxDuration ) {
						output.slotEnd = output.slot + maxDuration;
					}
				} else {
					if( output.slotEnd - output.slot < duration ){
						output.slot = output.slotEnd - duration;
					} else if( output.slotEnd - output.slot > maxDuration ) {
						output.slot = output.slotEnd - maxDuration;
					}
				}

				output.slotEnd = new Date( output.slotEnd * 1000 );
				output.slot = new Date( output.slot * 1000 );

				return output;
			},
			addToAppointmentsList(){
				let appCount = this.action.appointmentsList.length,
					appObject = Object.assign( {}, this.action.content );

				if( this.multiBooking ){
					if( this.appointmentInList( appObject ) ){
						eventHub.$CXNotice.add({
							message: __('Sorry. This appointment has already been added!', 'jet-appointments-booking'),
							type: 'error',
							duration: 7000,
						})
					} else if ( appCount === this.maxBooking ){
						eventHub.$CXNotice.add({
							message: __('Sorry. You have the max number of appointments!', 'jet-appointments-booking'),
							type: 'error',
							duration: 7000,
						})
					}else{
						this.$set( this.action.appointmentsList, appCount, appObject );
					}
				}else{
					this.$set( this.action.appointmentsList, 0, appObject );
				}
			},
			deleteFromAppointmentsList( index ){

				if( index !== false ){
					this.action.appointmentsList.splice( index, 1 );
				}

				if( ! this.action.appointmentsList.length ){
					this.action.content = Object.assign(
						{},
						this.action.content,
						{
							'date': '',
							'date_timestamp': '',
							'slot': '',
							'slot_timestamp': '',
							'slot_end': '',
							'slot_end_timestamp': '',
						}
					);
				}
			},
			appointmentInList( item ) {
				let _item = JSON.stringify( item );

				for( const index in this.action.appointmentsList ) {
					if( _item === JSON.stringify( this.action.appointmentsList[ index ] ) ){
						return true;
					}
				}

				return false;
			},
			repeatApps() {
				let {
						type,
						weekDayChecked,
						count,
					} = this.recurrencConfig,
					{
						slot_timestamp,
						slot_end_timestamp,
						date_timestamp,
						provider,
						service,
						status,
					} = this.action.content,
					item = {},
					app = 0,
					weekOffset;

				count     = parseInt( count );

				this.$set( this.action, 'appointmentsList', [] );

				switch( type ){
					case 'week':
						let weekDays = [];

						for ( const [ key, value ] of Object.entries( weekDayChecked ) ) {
							if( value ){
								weekDays.push( parseInt(key) );
							}

						}

						if( ! weekDays[0] ){
							return;
						}

						app            = 0;
						weekOffset     = this.getWeekDaysOffset( date_timestamp, weekDays, count );
						type           = 'days';

					break;
					case 'day':
						type = 'days';
					break;

					case 'month':
						type = 'months';
					break;

					case 'year':
						type = 'years';
					break;
				}

				for ( ; app < count; app++ ) {
					let newDateTimestamp, newSlotTimestamp, newSlotEndTimestamp,
						offset = 'week' === this.recurrencConfig.type ? weekOffset[ app ]  : app ,
						appIndex = this.action.appointmentsList.length;

					newDateTimestamp    = this.newTimestamp( date_timestamp, type, offset );
					newSlotTimestamp    = this.newTimestamp( slot_timestamp, type, offset );
					newSlotEndTimestamp = this.newTimestamp( slot_end_timestamp, type, offset );

					item = {
						'provider': provider,
						'service': service,
						'status': status || '',
						'date': this.timestampToDate( newDateTimestamp, 'DD/MM/YYYY' ),
						'date_timestamp': newDateTimestamp,
						'slot': this.timestampToDate( newSlotTimestamp, 'HH:mm' ),
						'slot_timestamp': newSlotTimestamp,
						'slot_end': this.timestampToDate( newSlotEndTimestamp, 'HH:mm' ),
						'slot_end_timestamp': newSlotEndTimestamp,
					};
					
					if ( this.isAvailableDay( item ) ) {
						this.$set( this.action.appointmentsList, appIndex, item );
					}
					
					
				}

			},
			isAvailableDay: function( slot ) {
				
				let result = true, 
				offDates   = this.disabledDates;

				if ( offDates.from && slot.slot_timestamp * 1000 > offDates.from.getTime() ) {
					result = false;
					return result;
				}

				if ( offDates.to && slot.slot_timestamp * 1000 < offDates.to.getTime() ) {
					result = false;
					return result;
				}

				let slotWeekDay = moment( slot.date_timestamp * 1000 ).utc().weekday();

				if ( -1 != offDates.days.indexOf( slotWeekDay ) ) {
					result = false;
					return result;
				}

				if ( offDates.customPredictor?.( new Date( slot.date_timestamp * 1000 ) ) ) {
					result = false;
					return result;
				}

				const slotDate = new Date( slot.date_timestamp * 1000 );

				offDates.dates.forEach(( value, index ) => {

					if( value.toDateString() === slotDate.toDateString() ) {
						result = false;
					}

				});

				offDates.ranges.forEach(( value, index ) => {

					if ( slot.slot_timestamp * 1000 > value.from.getTime() && slot.slot_timestamp * 1000 < value.to.getTime()
					|| slot.slot_end_timestamp * 1000 > value.from.getTime() && slot.slot_end_timestamp * 1000 < value.to.getTime() ) {
						result = false;
					}
		
				});

				return result;
				
			},
			newTimestamp: function( timestamp, type, number ){
				return moment.unix( timestamp ).utc().add( number, type ).valueOf() / 1000 ;
			},
			getWeekDaysOffset: function ( date, weekDays, count = 0 ) {
				let startDate = moment( date * 1000 ).utc().days(),
					output = [],
					offset;

				for ( let weekDay of weekDays ){
					if( weekDay < startDate ){
						offset = 7 - startDate + weekDay ;
					} else if( weekDay > startDate) {
						offset = weekDay - startDate;
					} else {
						offset = 0
					}
					output.push( offset );
				}

				output = output.sort();

				for ( let i = 0; i < count; i++ ) {
					output.push( output[i] + 7 );
				}

				if( output.length > count ){
					output = output.slice( 0, count );
				}

				return output;
			},
			hideDatepicker: function () {
				if( this.multiBooking && this.popUpState === "new" ){
					this.$set(
						this.action,
						'content',
						Object.assign(
							{},
							this.action.content,
							{
								'date': '',
								'date_timestamp': '',
								'slot': '',
								'slot_timestamp': '',
								'slot_end': '',
								'slot_end_timestamp': '',
							}
						)
					);
				}

				this.hideDaySlots();
				this.datePickerVisibility = false;
			},
			showDatepicker: function () {
				if( this.fields.includes('service') && ! this.action.content.service ){
					eventHub.$CXNotice.add({
						message: __('No service assigned!', 'jet-appointments-booking'),
						type: 'error',
						duration: 7000,
					});
					return;
				}

				if( this.fields.includes('provider')  && ! this.action.content.provider ){
					eventHub.$CXNotice.add({
						message: __('No provider assigned!', 'jet-appointments-booking'),
						type: 'error',
						duration: 7000,
					});
					return;
				}

				this.datePickerVisibility = true;
			},
			hideDaySlots: function () {
				store.commit( 'setValue', {
					key: 'daySlots',
					value: ''
				} );
			},
			checkEmptyFields: function () {
				let requiredFields = [ 'status', 'user_email' ],
					emptyFields    = [];

				if( this.provider.length ){
					requiredFields.push( 'provider' );
				}

				if( this.service.length ){
					requiredFields.push( 'service' );
				}

				if( ! this.multiBooking ) {
					requiredFields.push( 'date', 'slot', 'slot_end' );
				} else if ( ! this.action.appointmentsList.length ){
					emptyFields.push( this.labels[ 'appointments_list' ] );
				}

				for ( let field of requiredFields ){
					if (! this.action.content[field] ) {
						emptyFields.push( this.labels[ field ] ? this.labels[ field ] : field );
					}
				}

				if( ! emptyFields[0] ){
					return true;
				}

				emptyFields = emptyFields.join( ', ' ).toLowerCase();

				eventHub.$CXNotice.add({
					message: wp.i18n.sprintf( __('Empty fields: %s', 'jet-appointments-booking'), emptyFields ),
					type: 'error',
					duration: 7000,
				});

				return false;
			},
			checkMinSlotCount: function() {
				if( ! this.multiBooking ) {
					return;
				}

				let appCount = this.action.appointmentsList.length;

				if( appCount < this.minBooking ) {
					eventHub.$CXNotice.add({
						message: sprintf(__('Sorry. You have not selected enough slots, minimum quantity: %s', 'jet-appointments-booking'), this.minBooking),
						type: 'error',
						duration: 7000,
					});

					return true;
				}

			},
			getOptionList: function (key) {
				let output = [{
						'value': '',
						'label': __('Select...', 'jet-appointments-booking'),
					}],
					options = !this[key] ? [] : this[key];

				for ( let index in options ) {
					if ( options.hasOwnProperty( index ) ) {
						output.push({
							'value': index,
							'label': options[ index ],
						});
					}
				}

				return output;
			},
			getOrderLink: function (orderID) {
				return this.edit_link.replace(/\%id\%/, orderID);
			},
			slotsIsEmpty: function ( content ){
				if( typeof( content ) === 'string' ){
					return true;
				}

				return false;
			},
			fieldType: function ( key, type = null ) {
				let needType = '';
				switch ( key ) {
					case 'status':
					case 'service':
					case 'provider':
						needType = 'select';
					break;
					case 'comments':
						needType = 'textarea';
					break;
					case 'date':
						needType = 'date';
					break;
					default:
						needType = type
					break;
				}

				if ( this.beEdited(key) && type === needType ) {
					return true;
				} else {
					return false;
				}
			},
			hideSelectedSlot: function( slot ) {
				if ( ! this.multiBooking ) {
					return;
				}
				for( let app = 0; app < this.action.appointmentsList.length; app++ ) {
					if (
						this.action.appointmentsList[app].service === this.action.content.service &&
						this.action.appointmentsList[app].provider ===  this.action.content.provider &&
						this.action.appointmentsList[app].slot_timestamp === slot.from 
					) {
						return true;
					}
				}
			},
			beEdited: function ( key ) {
				switch ( key ) {
					case 'ID':
					case 'appointment_date':
					case 'order_id':
					case 'slot':
					case 'slot_end':
					case 'user_id':
					case 'group_ID':
						return false;
					default:
						return true;
				}
			},
			beVisible: function ( key ) {
				switch ( key ) {
					case 'slot_end':
						return	window.JetAPBConfig.multi_booking_settings.multi_booking ? false : true;
					case 'slot':
						return	window.JetAPBConfig.multi_booking_settings.multi_booking ? false : true;
					case 'ID':
					case 'appointment_date':
					case 'order_id':
					case 'date_timestamp':
					case 'date_end':
					case 'slot_timestamp':
					case 'slot_end_timestamp':
					case 'type':
						return false;
					default:
						return true;
				}
			},
			popupWidth: function() {
				if ( this.hasMetaFields() ) {
					return '1200px';
				} else {
					return '500px';
				}
			},
			contentClass: function() {
			
				let classes = [ 'jet-apb-details', `jet-apb-details-${ this.popUpState }` ];

				if ( this.hasMetaFields() ) {
					classes.push( 'jet-apb-details--has-meta' );
				}

				return classes;

			}
		}
	});

	Vue.component('jet-apb-add-new-appointment', {
		template: '#jet-apb-add-new-appointment',
		data: function () {
			return {
				item: {
					ID: '',
					user_id: '',
					slot: '',
					slot_end: '',
					date_timestamp: '',
					slot_timestamp: '',
					slot_end_timestamp: '',
				}
			}
		},
		methods: {
			callPopup: function ( state = false, item = false ) {
				eventHub.$emit('call-popup', {
					item: item,
					state: state,
				})
			},
		}
	})

	new Vue({
		el: '#jet-apb-appointments-page',
		template: '#jet-apb-appointments',
		store,
		computed: Vuex.mapState({
			isLoading: 'isLoading',
			isSet: state => state.setup.is_set,
			itemsList: state => state.items.itemsList,
			curentView: state => `jet-apb-appointments-${state.curentView}`,
		}),
		created: function () {
			if ( 'calendar' !== store.state.curentView ) {
				store.dispatch('getItems');
			}
			store.dispatch('setConfig');
		},
	});
})();
