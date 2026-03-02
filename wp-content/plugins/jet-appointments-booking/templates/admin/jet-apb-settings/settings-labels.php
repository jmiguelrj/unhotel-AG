<div>
	<cx-vui-switcher
		label="<?php esc_html_e( 'Use custom labels', 'jet-appointments-booking' ); ?>"
		description="<?php esc_html_e( 'Check this change default weekdays and month labels', 'jet-appointments-booking' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		:value="settings.use_custom_labels"
		@input="updateSetting( $event, 'use_custom_labels' )"
	></cx-vui-switcher>
	<template v-if="settings.use_custom_labels">
		<cx-vui-input
			label="<?php esc_html_e( 'Sunday', 'jet-appointments-booking' ); ?>"
			description="<?php esc_html_e( 'By default: Sun', 'jet-appointments-booking' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:size="'fullwidth'"
			:value="settings.custom_labels.Sun"
			@on-input-change="updateLabel( $event.target.value, 'Sun' )"
		></cx-vui-input>
		<cx-vui-input
			label="<?php esc_html_e( 'Monday', 'jet-appointments-booking' ); ?>"
			description="<?php esc_html_e( 'By default: Mon', 'jet-appointments-booking' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:size="'fullwidth'"
			:value="settings.custom_labels.Mon"
			@on-input-change="updateLabel( $event.target.value, 'Mon' )"
		></cx-vui-input>
		<cx-vui-input
			label="<?php esc_html_e( 'Tuesday', 'jet-appointments-booking' ); ?>"
			description="<?php esc_html_e( 'By default: Tue', 'jet-appointments-booking' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:size="'fullwidth'"
			:value="settings.custom_labels.Tue"
			@on-input-change="updateLabel( $event.target.value, 'Tue' )"
		></cx-vui-input>
		<cx-vui-input
			label="<?php esc_html_e( 'Wednesday', 'jet-appointments-booking' ); ?>"
			description="<?php esc_html_e( 'By default: Wed', 'jet-appointments-booking' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:size="'fullwidth'"
			:value="settings.custom_labels.Wed"
			@on-input-change="updateLabel( $event.target.value, 'Wed' )"
		></cx-vui-input>
		<cx-vui-input
			label="<?php esc_html_e( 'Thursday', 'jet-appointments-booking' ); ?>"
			description="<?php esc_html_e( 'By default: Thu', 'jet-appointments-booking' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:size="'fullwidth'"
			:value="settings.custom_labels.Thu"
			@on-input-change="updateLabel( $event.target.value, 'Thu' )"
		></cx-vui-input>
		<cx-vui-input
			label="<?php esc_html_e( 'Friday', 'jet-appointments-booking' ); ?>"
			description="<?php esc_html_e( 'By default: Fri', 'jet-appointments-booking' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:size="'fullwidth'"
			:value="settings.custom_labels.Fri"
			@on-input-change="updateLabel( $event.target.value, 'Fri' )"
		></cx-vui-input>
		<cx-vui-input
			label="<?php esc_html_e( 'Saturday', 'jet-appointments-booking' ); ?>"
			description="<?php esc_html_e( 'By default: Sat', 'jet-appointments-booking' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:size="'fullwidth'"
			:value="settings.custom_labels.Sat"
			@on-input-change="updateLabel( $event.target.value, 'Sat' )"
		></cx-vui-input>
		<cx-vui-input
			label="<?php esc_html_e( 'January', 'jet-appointments-booking' ); ?>"
			description="<?php esc_html_e( 'By default: January', 'jet-appointments-booking' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:size="'fullwidth'"
			:value="settings.custom_labels.January"
			@on-input-change="updateLabel( $event.target.value, 'January' )"
		></cx-vui-input>
		<cx-vui-input
			label="<?php esc_html_e( 'February', 'jet-appointments-booking' ); ?>"
			description="<?php esc_html_e( 'By default: February', 'jet-appointments-booking' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:size="'fullwidth'"
			:value="settings.custom_labels.February"
			@on-input-change="updateLabel( $event.target.value, 'February' )"
		></cx-vui-input>
		<cx-vui-input
			label="<?php esc_html_e( 'March', 'jet-appointments-booking' ); ?>"
			description="<?php esc_html_e( 'By default: March', 'jet-appointments-booking' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:size="'fullwidth'"
			:value="settings.custom_labels.March"
			@on-input-change="updateLabel( $event.target.value, 'March' )"
		></cx-vui-input>
		<cx-vui-input
			label="<?php esc_html_e( 'April', 'jet-appointments-booking' ); ?>"
			description="<?php esc_html_e( 'By default: April', 'jet-appointments-booking' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:size="'fullwidth'"
			:value="settings.custom_labels.April"
			@on-input-change="updateLabel( $event.target.value, 'April' )"
		></cx-vui-input>
		<cx-vui-input
			label="<?php esc_html_e( 'May', 'jet-appointments-booking' ); ?>"
			description="<?php esc_html_e( 'By default: May', 'jet-appointments-booking' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:size="'fullwidth'"
			:value="settings.custom_labels.May"
			@on-input-change="updateLabel( $event.target.value, 'May' )"
		></cx-vui-input>
		<cx-vui-input
			label="<?php esc_html_e( 'June', 'jet-appointments-booking' ); ?>"
			description="<?php esc_html_e( 'By default: June', 'jet-appointments-booking' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:size="'fullwidth'"
			:value="settings.custom_labels.June"
			@on-input-change="updateLabel( $event.target.value, 'June' )"
		></cx-vui-input>
		<cx-vui-input
			label="<?php esc_html_e( 'July', 'jet-appointments-booking' ); ?>"
			description="<?php esc_html_e( 'By default: July', 'jet-appointments-booking' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:size="'fullwidth'"
			:value="settings.custom_labels.July"
			@on-input-change="updateLabel( $event.target.value, 'July' )"
		></cx-vui-input>
		<cx-vui-input
			label="<?php esc_html_e( 'August', 'jet-appointments-booking' ); ?>"
			description="<?php esc_html_e( 'By default: August', 'jet-appointments-booking' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:size="'fullwidth'"
			:value="settings.custom_labels.August"
			@on-input-change="updateLabel( $event.target.value, 'August' )"
		></cx-vui-input>
		<cx-vui-input
			label="<?php esc_html_e( 'September', 'jet-appointments-booking' ); ?>"
			description="<?php esc_html_e( 'By default: September', 'jet-appointments-booking' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:size="'fullwidth'"
			:value="settings.custom_labels.September"
			@on-input-change="updateLabel( $event.target.value, 'September' )"
		></cx-vui-input>
		<cx-vui-input
			label="<?php esc_html_e( 'October', 'jet-appointments-booking' ); ?>"
			description="<?php esc_html_e( 'By default: October', 'jet-appointments-booking' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:size="'fullwidth'"
			:value="settings.custom_labels.October"
			@on-input-change="updateLabel( $event.target.value, 'October' )"
		></cx-vui-input>
		<cx-vui-input
			label="<?php esc_html_e( 'November', 'jet-appointments-booking' ); ?>"
			description="<?php esc_html_e( 'By default: November', 'jet-appointments-booking' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:size="'fullwidth'"
			:value="settings.custom_labels.November"
			@on-input-change="updateLabel( $event.target.value, 'November' )"
		></cx-vui-input>
		<cx-vui-input
			label="<?php esc_html_e( 'December', 'jet-appointments-booking' ); ?>"
			description="<?php esc_html_e( 'By default: December', 'jet-appointments-booking' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:size="'fullwidth'"
			:value="settings.custom_labels.December"
			@on-input-change="updateLabel( $event.target.value, 'December' )"
		></cx-vui-input>
		<cx-vui-input
			label="<?php esc_html_e( 'Form Error: Appointment time already taken', 'jet-appointments-booking' ); ?>"
			description="<?php esc_html_e( 'By default: Appointment time already taken', 'jet-appointments-booking' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:size="'fullwidth'"
			:value="settings.custom_labels.timeAlreadyTaken"
			@on-input-change="updateLabel( $event.target.value, 'timeAlreadyTaken' )"
		></cx-vui-input>
		<cx-vui-input
			label="<?php esc_html_e( 'Form Error: Selected time is not allowed to book', 'jet-appointments-booking' ); ?>"
			description="<?php esc_html_e( 'By default: Selected time is not allowed to book', 'jet-appointments-booking' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:size="'fullwidth'"
			:value="settings.custom_labels.timeNotAllowedToBook"
			@on-input-change="updateLabel( $event.target.value, 'timeNotAllowedToBook' )"
		></cx-vui-input>
		<cx-vui-input
			label="<?php esc_html_e( 'Form Error: You have not selected enough slots', 'jet-appointments-booking' ); ?>"
			description="<?php esc_html_e( 'By default: Sorry. You have not selected enough slots, minimum quantity: %s', 'jet-appointments-booking' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:size="'fullwidth'"
			:value="settings.custom_labels.minSlotCount"
			@on-input-change="updateLabel( $event.target.value, 'minSlotCount' )"
		></cx-vui-input>
		<cx-vui-input
			label="<?php esc_html_e( 'Appointment details label', 'jet-appointments-booking' ); ?>"
			description="<?php esc_html_e( 'By default: Appointment details', 'jet-appointments-booking' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:size="'fullwidth'"
			:value="settings.custom_labels.appointmentDetails"
			@on-input-change="updateLabel( $event.target.value, 'appointmentDetails' )"
		></cx-vui-input>
		<cx-vui-input
			label="<?php esc_html_e( 'Recurring Appointment Count label', 'jet-appointments-booking' ); ?>"
			description="<?php esc_html_e( 'By default: Appointment Count', 'jet-appointments-booking' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:size="'fullwidth'"
			:value="settings.custom_labels.appointmentCount"
			@on-input-change="updateLabel( $event.target.value, 'appointmentCount' )"
		></cx-vui-input>
		<cx-vui-input
			label="<?php esc_html_e( 'Recurring Slot Capacity Count label', 'jet-appointments-booking' ); ?>"
			description="<?php esc_html_e( 'By default: Slot Capacity Count', 'jet-appointments-booking' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:size="'fullwidth'"
			:value="settings.custom_labels.capacityCount"
			@on-input-change="updateLabel( $event.target.value, 'capacityCount' )"
		></cx-vui-input>
		<cx-vui-input
			label="<?php esc_html_e( 'Invalid Confirmation or Cancellation Token label', 'jet-appointments-booking' ); ?>"
			description="<?php esc_html_e( 'By default: Token is invalid or was already used.', 'jet-appointments-booking' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:size="'fullwidth'"
			:value="settings.custom_labels.invalidToken"
			@on-input-change="updateLabel( $event.target.value, 'invalidToken' )"
		></cx-vui-input>
	</template>
</div>
