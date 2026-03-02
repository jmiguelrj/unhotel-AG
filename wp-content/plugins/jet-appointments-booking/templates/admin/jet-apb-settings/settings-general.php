<div>
	<cx-vui-select
		label="<?php esc_html_e( 'Services post type', 'jet-appointments-booking' ); ?>"
		description="<?php esc_html_e( 'Select post type to fill services from', 'jet-appointments-booking' ); ?>"
		:options-list="postTypes"
		:wrapper-css="[ 'equalwidth' ]"
		:size="'fullwidth'"
		:value="settings.services_cpt"
		@input="updateSetting( $event, 'services_cpt' )"
	></cx-vui-select>
	<cx-vui-select
		label="<?php esc_html_e( 'Provider post type', 'jet-appointments-booking' ); ?>"
		description="<?php esc_html_e( 'Select post type to fill providers from', 'jet-appointments-booking' ); ?>"
		:options-list="postTypes"
		:wrapper-css="[ 'equalwidth' ]"
		:size="'fullwidth'"
		:value="settings.providers_cpt"
		@input="updateSetting( $event, 'providers_cpt' )"
	></cx-vui-select>
	<cx-vui-select
		v-if="settings.providers_cpt"
		label="<?php esc_html_e( 'Service Slot Duplicating Across Providers', 'jet-appointments-booking' ); ?>"
		:description="dynamicLabel( settings.providers_slot_duplicating )"
		:options-list="[
				{
					value: true,
					label: '<?php esc_html_e( 'Enabled', 'jet-appointments-booking' ); ?>'
				},
				{
					value: false,
					label: '<?php esc_html_e( 'Disabled', 'jet-appointments-booking' ); ?>'
				}
			]"
		:wrapper-css="[ 'equalwidth' ]"
		:size="'fullwidth'"
		:value="settings.providers_slot_duplicating"
		@input="updateSetting( $event, 'providers_slot_duplicating' )"
	></cx-vui-select>
	<?php if ( \JET_APB\Plugin::instance()->settings->is_provider_custom_schedule() ) { ?>
	<cx-vui-component-wrapper
		v-if="false === settings.providers_slot_duplicating && settings.providers_cpt"
	><span style="color:#23282;"><?php 
		esc_html_e( 'Please note:
		You have providers with custom schedules, so slot sharing is always enabled. If you want to disable slot sharing, please disable the custom schedule for all providers.', 'jet-appointments-booking' );
	?></span></cx-vui-component-wrapper>
	<?php } ?>
	
	<?php if ( \JET_APB\Plugin::instance()->wc->has_woocommerce() ) { ?>
	<cx-vui-switcher
		label="<?php esc_html_e( 'WooCommerce Integration', 'jet-appointments-booking' ); ?>"
		description="<?php esc_html_e( 'Check this to connect appointments with WooCommerce checkout', 'jet-appointments-booking' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		:value="settings.wc_integration"
		@input="updateSetting( $event, 'wc_integration' )"
	></cx-vui-switcher>
	<cx-vui-switcher
		label="<?php esc_html_e( 'Two-way WC orders synch', 'jet-appointments-booking' ); ?>"
		description="<?php esc_html_e( 'If you enable this option, WC order status will be updated on appointment status change (by default, if you update an appointment status, related Order will remain the same)', 'jet-appointments-booking' ); ?>"
		v-if="settings.wc_integration"
		:wrapper-css="[ 'equalwidth' ]"
		:value="settings.wc_synch_orders"
		@input="updateSetting( $event, 'wc_synch_orders' )"
	></cx-vui-switcher>
	<?php } else { ?>
	<cx-vui-component-wrapper
		label="<?php esc_html_e( 'WooCommerce Integration', 'jet-appointments-booking' ); ?>"
		description="<?php esc_html_e( 'Check this to connect appointments with WooCommerce checkout', 'jet-appointments-booking' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
	><span style="color:#C92C2C;"><?php 
		wp_kses_post( _e( 'Please install and activate <b>WooCommerce</b> plugin', 'jet-appointments-booking' ) );
	?></span></cx-vui-component-wrapper>
	<?php } ?>
	
	<cx-vui-switcher
		label="<?php esc_html_e( 'Manage Capacity', 'jet-appointments-booking' ); ?>"
		description="<?php esc_html_e( 'Allow to manage services capacity', 'jet-appointments-booking' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		:value="settings.manage_capacity"
		@input="updateSetting( $event, 'manage_capacity' )"
	></cx-vui-switcher>
	<cx-vui-switcher
		label="<?php esc_html_e( 'Show Capacity Counter', 'jet-appointments-booking' ); ?>"
		description="<?php esc_html_e( 'Show service capacity count in slots', 'jet-appointments-booking' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		v-if="settings.manage_capacity"
		:value="settings.show_capacity_counter"
		@input="updateSetting( $event, 'show_capacity_counter' )"
	></cx-vui-switcher>
	<cx-vui-switcher
		label="<?php esc_html_e( 'User Can Manage Booked Capacity', 'jet-appointments-booking' ); ?>"
		description="<?php esc_html_e( 'Allow user to change count of booked items for the selected slot', 'jet-appointments-booking' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		v-if="settings.manage_capacity"
		:value="settings.allow_manage_count"
		@input="updateSetting( $event, 'allow_manage_count' )"
	></cx-vui-switcher>
	<cx-vui-component-wrapper
		v-if="false === settings.providers_slot_duplicating && settings.providers_cpt && settings.manage_capacity"
	><span style="color:#23282;"><?php 
		esc_html_e( 'Please note:
		You have the "Service Slot Duplicating Across Providers" option disabled. This means the service capacity will define the total number of slots that can be booked across all providers, not the number of slots per provider.', 'jet-appointments-booking' );
	?></span></cx-vui-component-wrapper>
</div>