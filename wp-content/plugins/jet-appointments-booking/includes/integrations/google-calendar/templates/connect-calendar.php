<div class="jet-apb-integration-component">
	<template v-if="! connected">
		<cx-vui-component-wrapper
			:wrapper-css="[ 'equalwidth' ]"
			label="<?php esc_html_e( 'Connect Google Account', 'jet-appointments-booking' ); ?>"
			description="<?php esc_html_e( 'Log in with your Google Account to get the list of available calendars', 'jet-appoinmtents-booking' ); ?>"
		>
			<cx-vui-button
				button-style="accent"
				size="mini"
				@click="authorizeGoogle()"
			>
				<template slot="label">
					<?php esc_html_e( 'Connect', 'jet-appointments-booking' ); ?>
				</template>
			</cx-vui-button>
		</cx-vui-component-wrapper>
	</template>
	<template v-if="connected">
		<cx-vui-select
			label="<?php esc_html_e( 'Select Calendar to sync events with', 'jet-appointments-booking' ); ?>"
			description="<?php esc_html_e( 'The Calendar to put events into and get busy slots from (if this option is enabled)', 'jet-appointments-booking' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			size="fullwidth"
			:options-list="calendarsList"
			:value="settings.calendar_id"
			@input="setData( 'calendar_id', $event )"
		>
			<a
				href="#"
				v-if="allowDisconnect()"
				@click.prevent="disconnectGoogle()"
			><?php esc_html_e( 'Disconnect', 'jet-appointments-booking' ); ?></a>
		</cx-vui-select>
		<cx-vui-switcher
		v-if="value.client_id && value.client_secret && value.use_global_connection"
		label="<?php esc_html_e( 'Create Meet', 'jet-appointments-booking' ) ?>"
		description="<?php esc_html_e( 'Add Google Meet link for created calendar event', 'jet-appointments-booking' ) ?>"
		:wrapper-css="[ 'equalwidth' ]"
		:value="settings.create_meet"
		@input="setData( 'create_meet', $event )"
		/>
		<cx-vui-switcher
		v-if="value.client_id && value.client_secret && value.use_global_connection"
		label="<?php esc_html_e( 'Sync events from Google Calendar', 'jet-appointments-booking' ) ?>"
		description="<?php esc_html_e( 'Sync your Google Calendar events to exclude reserved slots', 'jet-appointments-booking' ) ?>"
		:wrapper-css="[ 'equalwidth' ]"
		:value="settings.sync_events_from_calendar"
		@input="setData( 'sync_events_from_calendar', $event )"
		/>
		<cx-vui-select
		v-if="value.sync_events_from_calendar"
		label="<?php esc_html_e( 'Calendar synchronization interval', 'jet-appointments-booking' ); ?>"
		description="<?php esc_html_e( 'Choose the interval for calendar synchronization', 'jet-appointments-booking' ); ?>"
		:options-list="cronSchedules"
		:wrapper-css="[ 'equalwidth' ]"
		size="fullwidth"
		:value="settings.synch_interval"
		@input="setData( 'synch_interval', $event )"
	></cx-vui-select>
	</template>
</div>