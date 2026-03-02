(function () {

	"use strict";

	Vue.component( 'jet-apb-integrations', {
		template: '#jet-dashboard-jet-apb-integrations',
		data() {
			return {
				integrations: window.JetAPBIntegrationsData.integrations,
				apiPath: window.JetAPBIntegrationsData.api,
			}
		},
		watch: {
			integrations: {
				handler( integrationsList ) {

					window.jetAPBSavingIntegrations = true;

					wp.apiFetch({
						method: 'POST',
						path: this.apiPath,
						data: {
							integrations: integrationsList,
						},
					}).then( ( response ) => {

						window.jetAPBSavingIntegrations = false;

						this.$CXNotice.add({
							message: 'Integrations Settings Saved!',
							type: 'success',
							duration: 7000,
						});

						// trigger a custom JS event to notify other components
						window.dispatchEvent( new CustomEvent( 'jet-apb-integrations-updated', {
							detail: {
								integrations: integrationsList,
							}
						} ) );
					}).catch( ( e ) => {

						window.jetAPBSavingIntegrations = false;

						this.$CXNotice.add({
							message: e.message,
							type: 'error',
							duration: 7000,
						});
					} );
				},
				deep: true,
			}
		},
		methods: {
			updateIntegrations( id, key, value ) {
				this.$set( this.integrations[ id ], key, value );
			}
		}
	} );

})();