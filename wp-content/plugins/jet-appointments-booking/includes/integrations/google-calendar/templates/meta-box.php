<div class="jet-apb-calendar-meta-box">
	<div class="jet-apb-calendar-meta-box__connection">
		<div class="jet-apb-calendar-meta-box__connection-dt">
			<?php esc_html_e( 'Current connection type:', 'jet-engine' ); ?>
		</div>
		<div class="jet-apb-calendar-meta-box__connection-dd">
			<span v-if="! connected_global && ! meta_value.use_local_connection">
				<?php esc_html_e( 'Not Connected', 'jet-engine' ); ?>
			</span>
			<span v-if="connected_global && ! meta_value.use_local_connection">
				<?php esc_html_e( 'Global', 'jet-engine' ); ?>
			</span>
			<span v-if="meta_value.use_local_connection">
				<?php esc_html_e( 'Local', 'jet-engine' ); ?>
			</span>
		</div>
	</div>
	<cx-vui-switcher
		v-if="! meta_value.use_local_calendar"
		label="<?php esc_html_e( 'Use local connection', 'jet-engine' ); ?>"
		description="<?php esc_html_e( 'Enable this toggle to use a local Google Calendar account for this provider/service', 'jet-engine' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		:value="meta_value.use_local_connection"
		@input="updateMeta( 'use_local_connection', ! meta_value.use_local_connection )"
	></cx-vui-switcher>
	<cx-vui-switcher
		v-if="! meta_value.use_local_connection"
		label="<?php esc_html_e( 'Use local calendar', 'jet-engine' ); ?>"
		description="<?php esc_html_e( 'Enable this toggle to use another calendar from the existing global connection', 'jet-engine' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		:value="meta_value.use_local_calendar"
		@input="updateMeta( 'use_local_calendar', ! meta_value.use_local_calendar )"
	></cx-vui-switcher>
	<jet-apb-connect-calendar
		v-if="meta_value.use_local_connection || meta_value.use_local_calendar"
		:value="meta_value"
		:connected="isConnected()"
		:disableCalendar="true"
		:context="getConnectionContext()"
		:can-disconnect="allowDisconnect()"
		@update="updateMetaBulk"
		@disconnected="onDisconnect"
	></jet-apb-connect-calendar>
	<cx-vui-component-wrapper
		v-if="isCalendarSelected( meta_value.calendar_id ) && (meta_value.use_local_connection || meta_value.use_local_calendar)"
	><span style="color:#23282;"><?php 
		esc_html_e( 'Please note: This calendar is already being used in another post. This may cause unexpected behavior', 'jet-appointments-booking' );
	?></span></cx-vui-component-wrapper>
</div>