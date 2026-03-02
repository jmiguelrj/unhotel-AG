<div>
	<cx-vui-select
		label="<?php esc_html_e( 'Availability check by', 'jet-appointments-booking' ); ?>"
		description="<?php esc_html_e( 'Select type of slots availability check - through all services or independent by each service', 'jet-appointments-booking' ); ?>"
		:options-list="[
			{
				value: 'global',
				label: '<?php esc_html_e( 'Through all services', 'jet-appointments-boooking' ); ?>',
			},
			{
				value: 'service',
				label: '<?php esc_html_e( 'By each service', 'jet-appointments-boooking' ); ?>',
			}
		]"
		:wrapper-css="[ 'equalwidth' ]"
		:size="'fullwidth'"
		:value="settings.check_by"
		@input="updateSetting( $event, 'check_by' )"
	></cx-vui-select>
	<cx-vui-select
		label="<?php esc_html_e( 'How to process \'on-hold\' appointments', 'jet-appointments-booking' ); ?>"
		description="<?php esc_html_e( 'Select the way how \'on-hold\' appointments slots will be handled in the calendar. \'on-hold\' appointments used when you integrate appointments with some payment system from JetFormBuilder or WooCommerce', 'jet-appointments-booking' ); ?>"
		:options-list="[
			{
				value: 'invalid',
				label: '<?php esc_html_e( 'Keep `on-hold` slots available', 'jet-appointments-boooking' ); ?>',
			},
			{
				value: 'in_progress',
				label: '<?php esc_html_e( 'Exclude `on-hold` slots from calendar', 'jet-appointments-boooking' ); ?>',
			}
		]"
		:wrapper-css="[ 'equalwidth' ]"
		:size="'fullwidth'"
		:value="settings.process_on_hold"
		@input="updateSetting( $event, 'process_on_hold' )"
	></cx-vui-select>
	<cx-vui-switcher
		label="<?php esc_html_e( 'Automatically switch appointments status', 'jet-appointments-booking' ); ?>"
		description="<?php esc_html_e( 'Check this to automatically change status for \'pending\' or \'on hold\' appointments to \'failed\' after selected period of time. This is may be useful if you want automatically make available not confirmed slots.', 'jet-appointments-booking' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		:value="settings.switch_status"
		@input="updateSetting( $event, 'switch_status' )"
	></cx-vui-switcher>
	<cx-vui-select
		label="<?php esc_html_e( 'Switch interval', 'jet-appointments-booking' ); ?>"
		description="<?php esc_html_e( 'Select switching appointments time interval', 'jet-appointments-booking' ); ?>"
		v-if="settings.switch_status"
		:options-list="getGlobalConfig( 'switch_intervals', [] )"
		:wrapper-css="[ 'equalwidth' ]"
		:size="'fullwidth'"
		:value="settings.switch_status_period"
		@input="updateSetting( $event, 'switch_status_period' )"
	></cx-vui-select>
	<cx-vui-f-select
		label="<?php esc_html_e( 'Switch from', 'jet-appointments-booking' ); ?>"
		description="<?php esc_html_e( 'Find appointments with this status', 'jet-appointments-booking' ); ?>"
		v-if="settings.switch_status"
		:options-list="[
			{
				value: 'on-hold',
				label: '<?php echo esc_html( \Jet_APB\Plugin::instance()->statuses->get_status_label( 'on-hold' ) ); ?>',
			},
			{
				value: 'pending',
				label: '<?php echo esc_html( \Jet_APB\Plugin::instance()->statuses->get_status_label( 'pending' ) ); ?>',
			},
			{
				value: 'processing',
				label: '<?php echo esc_html( \Jet_APB\Plugin::instance()->statuses->get_status_label( 'processing' ) ); ?>',
			},	
		]"
		:wrapper-css="[ 'equalwidth' ]"
		:size="'fullwidth'"
		:multiple="true"
		:value="settings.switch_status_from"
		@input="updateSetting( $event, 'switch_status_from' )"
	></cx-vui-f-select>
	<cx-vui-select
		label="<?php esc_html_e( 'Switch to', 'jet-appointments-booking' ); ?>"
		description="<?php esc_html_e( 'Switch status to this', 'jet-appointments-booking' ); ?>"
		v-if="settings.switch_status"
		:options-list="[
			{
				value: 'failed',
				label: '<?php echo esc_html( \Jet_APB\Plugin::instance()->statuses->get_status_label( 'failed' ) ); ?>',
			},
			{
				value: 'cancelled',
				label: '<?php echo esc_html( \Jet_APB\Plugin::instance()->statuses->get_status_label( 'cancelled' ) ); ?>',
			},	
		]"
		:wrapper-css="[ 'equalwidth' ]"
		:size="'fullwidth'"
		:value="settings.switch_status_to"
		@input="updateSetting( $event, 'switch_status_to' )"
	></cx-vui-select>
	
	<cx-vui-switcher
		label="<?php esc_html_e( 'Generate Confirmation URLs', 'jet-appointments-booking' ); ?>"
		description="<?php esc_html_e( 'Generate for each appointments unique URLs to confirm or decline appointment. URLs are stored in the Appointment meta data and can be used inside emails or webhooks.', 'jet-appointments-booking' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		:value="settings.allow_action_links"
		@input="updateSetting( $event, 'allow_action_links' )"
	></cx-vui-switcher>
	
	<cx-vui-select
		label="<?php esc_html_e( 'Generate Same Confirmation URLs for appointments in group', 'jet-appointments-booking' ); ?>"
		description="<?php esc_html_e( 'For all appointments in the group, the confirmation and cancellation links will be the same.', 'jet-appointments-booking' ); ?>"
		v-if="( settings.manage_capacity || settings.multi_booking ) && settings.allow_action_links"
		:options-list="[
			{
				value: false,
				label: '<?php esc_html_e( 'Unique link for each appointment', 'jet-appointments-booking' ) ?>',
			},
			{
				value: true,
				label: '<?php esc_html_e( 'One link for group', 'jet-appointments-booking' ) ?>',
			},	
		]"
		:wrapper-css="[ 'equalwidth' ]"
		:size="'fullwidth'"
		:value="settings.same_group_token"
		@input="updateSetting( $event, 'same_group_token' )"
	></cx-vui-select>

	<cx-vui-select
		label="<?php esc_html_e( 'Confirm Page Shows', 'jet-appointments-booking' ); ?>"
		description="<?php esc_html_e( 'How to present information on the Confirmation page - with plain text message or custom template', 'jet-appointments-booking' ); ?>"
		v-if="settings.allow_action_links"
		:options-list="[
			{
				value: 'text_message',
				label: '<?php esc_html_e( 'Text Message', 'jet-appointments-booking' ) ?>',
			},
			{
				value: 'custom_template',
				label: '<?php esc_html_e( 'Custom Template', 'jet-appointments-booking' ) ?>',
			},
		]"
		:wrapper-css="[ 'equalwidth' ]"
		:size="'fullwidth'"
		:value="settings.confirm_action_template_type"
		@input="updateSetting( $event, 'confirm_action_template_type' )"
	></cx-vui-select>
	<cx-vui-component-wrapper
		v-if="settings.allow_action_links"
		label="<?php esc_html_e( 'Appointment confirmation deadline', 'jet-appointments-booking' ); ?>"
		description="<?php esc_html_e( 'Specify a confirmation time limit before the start of appointment. Once this deadline passes, confirmation URL will no longer be accepted.', 'jet-appointments-booking' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
	>
		<div style="display: flex; gap: 5px;">
			<cx-vui-input
				type="number"
				min="1"
				:value="settings.confirm_deadline_limit"
				@input="updateSetting( $event, 'confirm_deadline_limit' )"
				:prevent-wrap="true"
				style="width: 55px;"
			></cx-vui-input>

			<cx-vui-select
				:options-list="[
					{
						value: 'minutes',
						label: '<?php esc_html_e( 'Minute(s)', 'jet-appointments-booking' ); ?>'
					},
					{
						value: 'hour',
						label: '<?php esc_html_e( 'Hour(s)', 'jet-appointments-booking' ); ?>'
					},
					{
						value: 'day',
						label: '<?php esc_html_e( 'Day(s)', 'jet-appointments-booking' ); ?>'
					},
					{
						value: 'week',
						label: '<?php esc_html_e( 'Week(s)', 'jet-appointments-booking' ); ?>'
					}
				]"
				:value="settings.confirm_deadline_unit"
				@input="updateSetting( $event, 'confirm_deadline_unit' )"
				:prevent-wrap="true"
				:size="'fullwidth'"
				style="width: 100px;"
			></cx-vui-select>
		</div>
	</cx-vui-component-wrapper>
	<cx-vui-textarea
		label="<?php esc_html_e( 'Confirmed Message', 'jet-appointments-booking' ); ?>"
		description="<?php esc_html_e( 'Message to show on appointment confirmation', 'jet-appointments-booking' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		:size="'fullwidth'"
		:value="settings.confirm_action_message"
		v-if="true === settings.allow_action_links && 'text_message' === settings.confirm_action_template_type"
		@on-input-change="updateSetting( $event.target.value, 'confirm_action_message' )"
	></cx-vui-textarea>
	<cx-vui-select
		label="<?php esc_html_e( 'Confirm Page Template', 'jet-appointments-booking' ); ?>"
		description="<?php esc_html_e( 'Select template to use as confiramtion page. This template will replace whole page content.', 'jet-appointments-booking' ); ?>"
		v-if="true === settings.allow_action_links && 'custom_template' === settings.confirm_action_template_type"
		:options-list="allowedTemplates"
		:wrapper-css="[ 'equalwidth' ]"
		size="fullwidth"
		:value="settings.confirm_action_template"
		@input="updateSetting( $event, 'confirm_action_template' )"
	></cx-vui-select>

	<cx-vui-select
		label="<?php esc_html_e( 'Cancellation Page Shows', 'jet-appointments-booking' ); ?>"
		description="<?php esc_html_e( 'How to present information on the Cancellation page - with plain text message or custom template', 'jet-appointments-booking' ); ?>"
		v-if="settings.allow_action_links"
		:options-list="[
			{
				value: 'text_message',
				label: '<?php esc_html_e( 'Text Message', 'jet-appointments-booking' ) ?>',
			},
			{
				value: 'custom_template',
				label: '<?php esc_html_e( 'Custom Template', 'jet-appointments-booking' ) ?>',
			},
		]"
		:wrapper-css="[ 'equalwidth' ]"
		size="fullwidth"
		:value="settings.cancel_action_template_type"
		@input="updateSetting( $event, 'cancel_action_template_type' )"
	></cx-vui-select>
	<cx-vui-component-wrapper
		v-if="settings.allow_action_links"
		label="<?php esc_html_e( 'Appointment cancellation deadline', 'jet-appointments-booking' ); ?>"
		description="<?php esc_html_e( 'Specify a cancellation time limit before the start of appointment. Once this deadline passes, cancellation URL will no longer be accepted.', 'jet-appointments-booking' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
	>
		<div style="display: flex; gap: 5px;">
			<cx-vui-input
				type="number"
				min="1"
				:value="settings.cancel_deadline_limit"
				@input="updateSetting( $event, 'cancel_deadline_limit' )"
				:prevent-wrap="true"
				style="width: 55px;"
			></cx-vui-input>

			<cx-vui-select
				:options-list="[
					{
						value: 'minutes',
						label: '<?php esc_html_e( 'Minute(s)', 'jet-appointments-booking' ); ?>'
					},
					{
						value: 'hour',
						label: '<?php esc_html_e( 'Hour(s)', 'jet-appointments-booking' ); ?>'
					},
					{
						value: 'day',
						label: '<?php esc_html_e( 'Day(s)', 'jet-appointments-booking' ); ?>'
					},
					{
						value: 'week',
						label: '<?php esc_html_e( 'Week(s)', 'jet-appointments-booking' ); ?>'
					}
				]"
				:value="settings.cancel_deadline_unit"
				@input="updateSetting( $event, 'cancel_deadline_unit' )"
				:prevent-wrap="true"
				:size="'fullwidth'"
				style="width: 100px;"
			></cx-vui-select>
		</div>
	</cx-vui-component-wrapper>
	<cx-vui-textarea
		label="<?php esc_html_e( 'Cancelled Message', 'jet-appointments-booking' ); ?>"
		description="<?php esc_html_e( 'Message to show on appointment cancel', 'jet-appointments-booking' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		size="fullwidth"
		:value="settings.cancel_action_message"
		v-if="true === settings.allow_action_links && 'text_message' === settings.cancel_action_template_type"
		@on-input-change="updateSetting( $event.target.value, 'cancel_action_message' )"
	></cx-vui-textarea>
	<cx-vui-select
		label="<?php esc_html_e( 'Cancellation Page Template', 'jet-appointments-booking' ); ?>"
		description="<?php esc_html_e( 'Select template to use as cancellation page. This template will replace whole page content.', 'jet-appointments-booking' ); ?>"
		v-if="true === settings.allow_action_links && 'custom_template' === settings.cancel_action_template_type"
		:options-list="allowedTemplates"
		:wrapper-css="[ 'equalwidth' ]"
		size="fullwidth"
		:value="settings.cancel_action_template"
		@input="updateSetting( $event, 'cancel_action_template' )"
	></cx-vui-select>
	<cx-vui-switcher
		label="<?php esc_html_e( 'Hide Set Up Wizard', 'jet-appointments-booking' ); ?>"
		description="<?php esc_html_e( 'Check this to hide Set Up page to avoid unnecessary plugin resets', 'jet-appointments-booking' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		:value="settings.hide_setup"
		@input="updateSetting( $event, 'hide_setup' )"
	></cx-vui-switcher>
	<cx-vui-input
		label="<?php esc_html_e( 'Access Capability', 'jet-appointments-booking' ); ?>"
		description="<?php esc_html_e( 'The string to change access capability to appointments admin page and appointments actions.', 'jet-appointments-booking' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		:size="'fullwidth'"
		:value="settings.capability_type"
		@on-input-change="updateSetting( $event.target.value, 'capability_type' )"
	></cx-vui-input>
</div>
