<div class="jet-apb-integration-component">
	<cx-vui-component-wrapper
		:wrapper-css="[ 'error' ]"
		v-if="! timezoneIsSet"
		label="<?php esc_html_e( 'Note!', 'jet-appointments-booking' ); ?>"
		description="<?php esc_html_e( 'You didn`t set your website timezone settings. To create Zoom Meetings with correct date/time, please set your timezone', 'jet-appointments-booking' ); ?> - <a href='<?php echo esc_html( admin_url( 'options-general.php#timezone_string' ) ); ?>' target='_blank'><?php esc_html_e( 'here', 'jet-appointments-booking' ); ?></a>"
	/>
	<cx-vui-input
		label="<?php esc_html_e( 'Account ID', 'jet-appoinmtents-booking' ) ?>"
		description="<?php esc_html_e( 'Account ID field of your Zoom App', 'jet-appoinmtents-booking' ) ?>"
		:wrapper-css="[ 'equalwidth' ]"
		size="fullwidth"
		:value="settings.account_id"
		@input="setData( 'account_id', $event )"
	/>
	<cx-vui-input
		label="<?php esc_html_e( 'Client ID', 'jet-appoinmtents-booking' ) ?>"
		description="<?php esc_html_e( 'Client ID field of your Zoom App', 'jet-appoinmtents-booking' ) ?>"
		:wrapper-css="[ 'equalwidth' ]"
		size="fullwidth"
		:value="settings.client_id"
		@input="setData( 'client_id', $event )"
	/>
	<cx-vui-input
		label="<?php esc_html_e( 'Client secret', 'jet-appoinmtents-booking' ) ?>"
		description="<?php esc_html_e( 'Client secret field of your Zoom App', 'jet-appoinmtents-booking' ) ?>"
		:wrapper-css="[ 'equalwidth' ]"
		size="fullwidth"
		:value="settings.client_secret"
		@input="setData( 'client_secret', $event )"
	/>
	<cx-vui-component-wrapper
		label="<?php esc_html_e( 'Authenticate', 'jet-appointments-booking' ); ?>"
		description="<?php esc_html_e( 'Get an access token by given credentials', 'jet-appointments-booking' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
	>
		<cx-vui-button
			button-style="accent"
			size="mini"
			:disabled="authDisabled()"
			@click="getToken()"
		>
			<template slot="label"><?php esc_html_e( 'Auth', 'jet-appointments-booking' ); ?></template>
		</cx-vui-button>
		<div
			v-if="tokenMessage"
			:class="{
				'validatation-result': true,
				'validatation-result--success': token,
				'validatation-result--error': ! token,
			}"
		>{{ tokenMessage }}</div>
	</cx-vui-component-wrapper>
	<cx-vui-component-wrapper
		label="<?php esc_html_e( 'Where to get these credentials?', 'jet-appointments-booking' ); ?>"
		description="<?php esc_html_e( 'To get Zoom API credentials you need to create App at Zoom Developer portal. Here you can find detailed instructions', 'jet-appointments-booking' ); ?> - <a href='https://marketplace.zoom.us/docs/guides/build/server-to-server-oauth-app/#create-a-server-to-server-oauth-app' target='_blank'><?php esc_html_e( 'Create a Server-to-Server OAuth app', 'jet-appointments-booking' ); ?></a><br><br><?php esc_html_e( 'Also please make sure you enabled <b>meeting:master</b> and <b>meeting:write:admin</b> scopes for your App', 'jet-appoinmtents-booking' ); ?>"
	/>
	<cx-vui-switcher
		v-if="manageCapacity"
		label="<?php esc_html_e( 'Share the same meeting for grouped appointments', 'jet-appointments-booking' ); ?>"
		description="<?php esc_html_e( 'Create same zoom link for the same slot', 'jet-appointments-booking' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		:value="settings.same_zoom_link"
		@input="setData( 'same_zoom_link', $event )"
	></cx-vui-switcher>
	<cx-vui-switcher
		v-if="! settings.same_zoom_link"
		label="<?php esc_html_e( 'Delete meeting on appointment  cancel', 'jet-appointments-booking' ); ?>"
		description="<?php esc_html_e( 'Delete previously created meeting if appropriate appointment status was changed to Cancelled, Refunded or Failed', 'jet-appointments-booking' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		:value="settings.delete_on_appointment_cancel"
		@input="setData( 'delete_on_appointment_cancel', $event )"
	></cx-vui-switcher>
</div>