<?php // phpcs:disable ?>
<div
	class="jet-menu-settings-page jet-menu-settings-page__general"
>
	<div class="jet-menu-settings-page__presets-manager cx-vui-component">
		<cx-vui-button
			button-style="accent-border"
			size="mini"
			@click="openPresetManager"
		>
			<span slot="label"><?php _e( 'Preset Manager', 'jet-menu' ); ?></span>
		</cx-vui-button>

		<cx-vui-button
			button-style="accent-border"
			size="mini"
			:url="exportUrl"
			tag-name="a"
		>
			<span slot="label"><?php _e( 'Export Options', 'jet-menu' ); ?></span>
		</cx-vui-button>

		<cx-vui-button
			button-style="accent-border"
			size="mini"
			@click="importVisible=true"
		>
			<span slot="label"><?php _e( 'Import Options', 'jet-menu' ); ?></span>
		</cx-vui-button>

		<cx-vui-button
			button-style="default-border"
			size="mini"
			@click="resetCheckPopup=true"
		>
			<span slot="label"><?php _e( 'Reset Options', 'jet-menu' ); ?></span>
		</cx-vui-button>
	</div>

	<?php include jet_menu()->plugin_path( 'templates/admin/options-page-popups.php' ); ?>

	<cx-vui-switcher
		name="svg-uploads"
		label="<?php _e( 'SVG images upload status', 'jet-menu' ); ?>"
		description="<?php _e( 'Enable or disable SVG images uploading', 'jet-menu' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		return-true="enabled"
		return-false="disabled"
		v-model="pageOptions['svg-uploads']['value']">
	</cx-vui-switcher>

	<cx-vui-switcher
		name="use-template-cache"
		label="<?php _e( 'Template Content Cache', 'jet-menu' ); ?>"
		description="<?php _e( 'Do you want to use the cache for the menu templates ( Elementor & Block Editor )?', 'jet-menu' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		return-true="true"
		return-false="false"
		v-model="pageOptions['use-template-cache']['value']">
	</cx-vui-switcher>

    <cx-vui-select
            v-if="'true' === pageOptions['use-template-cache']['value']"
            name="template-cache-expiration"
            label="<?php _e( 'Cache Expiration', 'jet-menu' ); ?>"
            description="<?php _e( 'Select how long to cache rendered menu templates. Changing this option will clear the existing cache.', 'jet-menu' ); ?>"
            :wrapper-css="[ 'equalwidth' ]"
            size="fullwidth"
            :options-list="cacheTimeoutOptions"
            v-model="pageOptions['template-cache-expiration']['value']">
    </cx-vui-select>

    <div class="cx-vui-component cx-vui-component--equalwidth"
         v-if="'true' === pageOptions['use-template-cache']['value']">
        <div class="cx-vui-component__meta">
            <label class="cx-vui-component__label">
				<?php _e( 'Clear Template Cache', 'jet-menu' ); ?>
            </label>
            <div class="cx-vui-component__desc">
				<?php _e( 'Clear cached menu templates.', 'jet-menu' ); ?>
            </div>
        </div>
        <div class="cx-vui-component__control">
            <cx-vui-button
                    button-style="accent-border"
                    size="mini"
                    :loading="clearStatus.template"
                    @click="clearCache('template')"
            >
                <span slot="label"><?php esc_html_e( 'Clear Template Cache', 'jet-menu' ); ?></span>
            </cx-vui-button>
        </div>
    </div>

    <!--<cx-vui-switcher
        name="plugin-nextgen-edition"
        label="<?php /*_e( 'Revamp Menu', 'jet-menu' ); */?>"
        description="<?php /*_e( 'Once this option is enabled you start building from scratch. To get back to the old menu switch this toggle off', 'jet-menu' ); */?>"
        :wrapper-css="[ 'equalwidth' ]"
        return-true="true"
        return-false="false"
        v-model="pageOptions['plugin-nextgen-edition']['value']"
        v-on:input="nextgenEditionTrigger">
    </cx-vui-switcher>-->

	<?php

	$template = get_template();

	if ( file_exists( jet_menu()->plugin_path( "integration/themes/{$template}" ) ) ) {

		$disable_integration_option = 'jet-menu-disable-integration-' . $template;

		?><cx-vui-switcher
        name="<?php echo esc_attr( $disable_integration_option ); ?>"
        label="<?php _e( 'Use current theme integration?', 'jet-menu' ); ?>"
        :wrapper-css="[ 'equalwidth' ]"
        return-true="true"
        return-false="false"
        v-model="pageOptions['<?php echo esc_js( $disable_integration_option ); ?>']['value']">
        </cx-vui-switcher><?php
	}?>

    <cx-vui-switcher
        name="jet-menu-cache-css"
        label="<?php _e( 'Cache menu CSS', 'jet-menu' ); ?>"
        :wrapper-css="[ 'equalwidth' ]"
        return-true="true"
        return-false="false"
        v-model="pageOptions['jet-menu-cache-css']['value']">
    </cx-vui-switcher>

    <div class="cx-vui-component cx-vui-component--equalwidth"
         v-if="'true' === pageOptions['jet-menu-cache-css']['value']">
        <div class="cx-vui-component__meta">
            <label class="cx-vui-component__label">
				<?php _e( 'Clear CSS Cache', 'jet-menu' ); ?>
            </label>
            <div class="cx-vui-component__desc">
				<?php _e( 'Remove generated menu CSS files.', 'jet-menu' ); ?>
            </div>
        </div>
        <div class="cx-vui-component__control">
            <cx-vui-button
                    button-style="accent-border"
                    size="mini"
                    :loading="clearStatus.css"
                    @click="clearCache('css')"
            >
                <span slot="label"><?php esc_html_e( 'Clear CSS Cache', 'jet-menu' ); ?></span>
            </cx-vui-button>
        </div>
    </div>

</div>
<?php // phpcs:enable ?>