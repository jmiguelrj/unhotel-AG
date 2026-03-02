<div class="jet-apb-integration-component">
	<div v-if="! redirectURL" style="padding: 20px;"><?php
		esc_html_e( 'Google integration requires a page reload to get the latest data from your website. Please wait a moment...', 'jet-appointments-booking' );
	?></div>
	<template v-else>
		<cx-vui-input
			label="<?php esc_html_e( 'Client ID', 'jet-appoinmtents-booking' ) ?>"
			description="<?php esc_html_e( 'Client ID field of your Google Calendar', 'jet-appoinmtents-booking' ) ?>"
			:wrapper-css="[ 'equalwidth' ]"
			size="fullwidth"
			:value="settings.client_id"
			@input="setData( 'client_id', $event )"
		/>
		<cx-vui-input
			label="<?php esc_html_e( 'Client secret', 'jet-appoinmtents-booking' ) ?>"
			description="<?php esc_html_e( 'Client secret field of your Google Calendar', 'jet-appoinmtents-booking' ) ?>"
			:wrapper-css="[ 'equalwidth' ]"
			size="fullwidth"
			:value="settings.client_secret"
			@input="setData( 'client_secret', $event )"
		/>
		<cx-vui-component-wrapper
			:wrapper-css="[ 'raw' ]"
			label="<?php esc_html_e( 'Redirect URL', 'jet-appointments-booking' ); ?>"
			description="<?php esc_html_e( 'Use this URL in Google console settings', 'jet-appoinmtents-booking' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
		>
			<code>{{ redirectURL }}</code>
		</cx-vui-component-wrapper>
		<template v-if="value.client_id && value.client_secret">
			<cx-vui-switcher
				label="<?php esc_html_e( 'Use global Calendar connection', 'jet-appointments-booking' ) ?>"
				description="<?php esc_html_e( 'Enable this toggle to use a single Google Calendar account for all providers/services', 'jet-appointments-booking' ) ?>"
				:wrapper-css="[ 'equalwidth' ]"
				:value="settings.use_global_connection"
				@input="setData( 'use_global_connection', ! settings.use_global_connection )"
			/>
			<jet-apb-connect-calendar
				v-if="settings.use_global_connection"
				:value="settings"
				:connected="connected"
				:context="{
					type: 'global',
					object: false,
				}"
				@update="updateSettings"
			></jet-apb-connect-calendar>
			<cx-vui-component-wrapper
			v-if="isCalendarSelected( settings.calendar_id )"
			><span style="color:#23282;"><?php
			esc_html_e( 'Please note: This calendar is already being used in another post. This may cause unexpected behavior', 'jet-appointments-booking' );
			?></span></cx-vui-component-wrapper>
		</template>
	</template>
</div>