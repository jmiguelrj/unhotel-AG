<div>
	<template v-if="'slot' === settings.booking_type">
		<cx-vui-radio
			label="<?php esc_html_e( 'Calendar layout', 'jet-appointments-booking' ); ?>"
			description="<?php esc_html_e( 'Select layout of calendar for the front-end form', 'jet-appointments-booking' ); ?>"
			:options-list="[
				{
					value: 'default',
					label: 'Default',
				},
				{
					value: 'sidebar_slots',
					label: 'Slots in the sidebar',
				},	
			]"
			:wrapper-css="[ 'equalwidth' ]"
			size="fullwidth"
			:value="settings.calendar_layout"
			@input="updateSetting( $event, 'calendar_layout' )"
		></cx-vui-radio>
		<cx-vui-switcher
			label="<?php esc_html_e( 'Scroll to Appointment details after select slot', 'jet-appointments-booking' ); ?>"
			description="<?php esc_html_e( 'Automatically scrolls page to Appointment details section after selecting slot in the calendar.', 'jet-appointments-booking' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:value="settings.scroll_to_details"
			@input="updateSetting( $event, 'scroll_to_details' )"
		></cx-vui-switcher>
		<cx-vui-switcher
			label="<?php esc_html_e( 'Show timezones picker in calendar', 'jet-appointments-booking' ); ?>"
			description="<?php esc_html_e( 'Show timezones picker in appointment calendar. Allows users to show slots in selected timezone time.', 'jet-appointments-booking' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:value="settings.show_timezones"
			@input="updateSetting( $event, 'show_timezones' )"
		></cx-vui-switcher>
		<cx-vui-switcher
			v-if = "! settings.show_timezones"
			label="<?php esc_html_e( 'Use custom timezones for the calendar', 'jet-appointments-booking' ); ?>"
			description="<?php esc_html_e( 'Allows to select a static timezone for the calendar. All slots will be generated according to this timezone. This only affects how times are displayed on the frontend.', 'jet-appointments-booking' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:value="settings.use_calendar_timezone"
			@input="updateSetting( $event, 'use_calendar_timezone' )"
		></cx-vui-switcher>
		<cx-vui-f-select
			v-if="settings.use_calendar_timezone && ! settings.show_timezones"
			label="<?php esc_html_e( 'Calendar slots timezone', 'jet-appointments-booking' ); ?>"
			description="<?php esc_html_e( 'Set the timezone for the calendar on the frontend. All slots will be generated according to this timezone. This only affects how times are displayed on the frontend — in the admin panel, all times remain stored in UTC+0.', 'jet-appointments-booking' ); ?>"
			:options-list="getTimezonesList()"
			:wrapper-css="[ 'equalwidth', 'calendar-timezone' ]"
			:multiple="false"
			:size="'fullwidth'"
			:value="settings.calendar_timezone"
			@on-change="updateSetting( $event, 'calendar_timezone' )"
		></cx-vui-f-select>
	</template>
	<template v-else>
		<cx-vui-component-wrapper
			label="<?php esc_html_e( 'Calendar layout', 'jet-appointments-booking' ); ?>"
			description="<?php esc_html_e( 'Select layout of calendar for the front-end form', 'jet-appointments-booking' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
		><span style="color:#C92C2C;"><?php 
			printf(
				esc_html__( 'Available only for ', 'jet-appointments-booking' ) . '<b>Slot </b><a href="%s">Schedule Type</a>',
				esc_html__( admin_url( 'admin.php?page=jet-dashboard-settings-page&subpage=jet-apb-working-hours-settings' ) )
			);
		?></span></cx-vui-component-wrapper>
		<cx-vui-component-wrapper
			label="<?php esc_html_e( 'Show timezones picker in calendar', 'jet-appointments-booking' ); ?>"
			description="<?php esc_html_e( 'Show timezones picker in appointment calendar. Allows users to show slots in selected timezone time.', 'jet-appointments-booking' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
		><span style="color:#C92C2C;"><?php 
			printf(
				esc_html__( 'Available only for ', 'jet-appointments-booking' ) . '<b>Slot</b> <a href="%s">Schedule Type</a>',
				esc_html__( admin_url( 'admin.php?page=jet-dashboard-settings-page&subpage=jet-apb-working-hours-settings' ) )
			);
		?></span></cx-vui-component-wrapper>
	</template>
</div>
