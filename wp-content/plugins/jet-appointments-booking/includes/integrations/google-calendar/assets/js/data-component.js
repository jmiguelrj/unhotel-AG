(function () {

	"use strict";

	Vue.component( 'jet-apb-connect-calendar', {
		template: '#jet-apb-google-calendar-connect',
		props: [ 'value', 'connected', 'context', 'can-disconnect' ],
		data() {
			return {
				settings: {},
				apiPath: window.JetAPBGCalData.apiPath,
				calendarsPath: window.JetAPBGCalData.calendarsPath,
				doingAuth: false,
				cronSchedules : window.JetAPBGCalData.cron_schedules,
				calendarsList: [
					{
						value: '',
						label: 'Select calendar...',
					}
				],
			}
		},
		created() {
			this.settings = { ...this.value };
		},
		mounted() {
			if ( this.connected ) {
				if ( ! window.jetAPBSavingIntegrations ) {
					this.fetchCalendars();
				} else {
					window.addEventListener( 'jet-apb-integrations-updated', ( e ) => {
						if ( e.detail.integrations["google-calendar"].enabled ) {
							this.fetchCalendars();
						}
					} );
				}
			}
		},
		methods: {
			allowDisconnect() {
				if ( undefined === this.canDisconnect ) {
					return true;
				} else {
					return this.canDisconnect;
				}
			},
			getContextType() {
				if ( this.context && this.context.type ) {
					return this.context.type;
				} else {
					return 'global';
				}
			},
			setData( key, value ) {
				this.$set( this.settings, key, value );
				this.settings[ key ] = value;
				this.$emit( 'update', this.settings );
			},
			getContextObject() {
				if ( this.context && this.context.object ) {
					return this.context.object;
				} else {
					return '';
				}
			},
			fetchCalendars() {

				let apiQuery = this.calendarsPath;

				// add query paramter 'context_type' to apiQuery
				const queryArgs = {
					context_type: this.getContextType(),
					context_object: this.getContextObject(),
				};

				for ( const key in queryArgs ) {
					if ( queryArgs[ key ] ) {
						if ( apiQuery.indexOf( '?' ) === -1 ) {
							apiQuery += `?${ key }=${ queryArgs[ key ] }`;
						} else {
							apiQuery += `&${ key }=${ queryArgs[ key ] }`;
						}
					}
				}

				wp.apiFetch({
					method: 'GET',
					path: apiQuery,
				}).then( ( response ) => {

					if ( response.success ) {
						for ( let i = 0; i < response.calendars.length; i++ ) {
							this.calendarsList.push( {
								value: response.calendars[ i ].id,
								label: response.calendars[ i ].name,
							} );
						}
					} else {
						this.$CXNotice.add( {
							message: response.message,
							type: 'error',
							duration: 7000,
						} );
					}
				}).catch( ( e ) => {
					this.$CXNotice.add( {
						message: e.message,
						type: 'error',
						duration: 7000,
					} );
				} );
			},
			authDisabled() {
				return this.doingAuth || ! this.settings.account_id || ! this.settings.client_id || ! this.settings.client_secret;
			},
			disconnectGoogle() {
				if ( confirm( 'Are you sure?' ) ) {
					this.authorizeGoogle( 'disconnect' );
				}
			},
			authorizeGoogle( action ) {

				this.doingAuth = true;

				action = action || 'authorize';

				wp.apiFetch({
					method: 'POST',
					path: this.apiPath,
					data: {
						settings: this.settings,
						context_type: this.getContextType(),
						context_object: this.getContextObject(),
						action: action,
					},
				}).then( ( response ) => {

					this.doingAuth = false;

					if ( 'disconnect' === action ) {
						if ( response.success ) {
							this.$emit( 'disconnected' );
							location.reload();
						}
					} else if ( response.success ) {
						window.open( response.redirect, '_blank' );
					}
				}).catch( ( e ) => {
					this.doingAuth = false;
				} );
			},
		},
	} );

	Vue.component( 'jet-apb-google-calendar-integration', {
		template: '#jet-dashboard-jet-apb-google-calendar-integration',
		props: [ 'value' ],
		data() {
			return {
				settings: {},
				redirectURL: window.JetAPBGCalData.redirectURL,
				connected: window.JetAPBGCalData.connected,
				calendars: window.JetAPBGCalData.calendars,
			}
		},
		created() {
			if ( this.value.client_id || this.value.client_secret ) {
				this.settings = { ...this.value };
			}
		},
		mounted() {
			if ( ! this.redirectURL ) {
				if ( ! window.jetAPBSavingIntegrations ) {
					window.location.reload();
				} else {
					window.addEventListener( 'jet-apb-integrations-updated', ( e ) => {
						window.location.reload();
					} );
				}
			}
		},
		methods: {
			setData( key, value ) {
				this.$set( this.settings, key, value );
				this.settings[ key ] = value;
				this.$emit( 'input', this.settings );
			},
			updateSettings( newSettings ) {
				this.settings = { ...this.settings, ...newSettings };
				this.$emit( 'input', this.settings );
			},
			isCalendarSelected( calendarID ) {
				for ( const [key, value] of Object.entries(this.calendars) ) {
					if( value == calendarID && key !== 'global' ) {
						return true;
					}
				}
				return false;
			},
		}
	} );

})();