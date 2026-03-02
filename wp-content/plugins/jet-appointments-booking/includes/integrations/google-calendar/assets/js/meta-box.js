(function () {

	"use strict";

	new Vue({
		el: '#jet_apb_google_calendar_metabox',
		template: '#jet-apb-google-calendar-meta-box',
		data: {
			meta_value: window.JetAPBGCalMeta.meta_value,
			connected_global: window.JetAPBGCalMeta.connected_global,
			connected_local: window.JetAPBGCalMeta.connected_local,
			meta_api: window.JetAPBGCalMeta.meta_api,
			postID: window.JetAPBGCalMeta.post_id,
			nonce: window.JetAPBGCalMeta.nonce,
			calendars: window.JetAPBGCalData.calendars,
		},
		methods: {
			allowDisconnect() {
				if ( true === this.meta_value.use_local_connection ) {
					return true;
				} else {
					return false;
				}
			},
			onDisconnect() {
				this.$set( this.meta_value, 'use_local_connection', false );
				this.$set( this.meta_value, 'calendar_id', '' );
			},
			updateMetaBulk( newMeta ) {

				this.meta_value = {
					...this.meta_value,
					...newMeta,
				};

				this.saveMeta( newMeta );
			},
			updateMeta( metaKey, metaValue ) {

				this.$set( this.meta_value, metaKey, metaValue );
				this.meta_value[ metaKey ] = metaValue;

				this.saveMeta( this.meta_value );
			},
			saveMeta( metaValues ) {

				wp.apiFetch({
					method: 'POST',
					path: this.meta_api,
					data: {
						meta_value: metaValues,
						post_id: this.postID,
						nonce: this.nonce,
					},
				}).then( ( response ) => {

					if ( response.success ) {
						this.$CXNotice.add( {
							message: response.message,
							type: 'success',
							duration: 2000,
						} );
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
			isConnected() {
				if ( this.meta_value.use_local_connection ) {
					return this.connected_local;
				}

				return this.connected_global;
			},
			getConnectionContext() {
				if ( this.meta_value.use_local_connection ) {
					return {
						type: 'post',
						object: this.postID,
					};
				}

				return {
					type: 'global',
					object: '',
				};
			},
			isCalendarSelected( calendarID ) {
				for ( const [key, value] of Object.entries(this.calendars) ) {
					if( value == calendarID && key != this.postID ) {
						return true;
					}
				}
				return false;
			},
		}
	});

})();