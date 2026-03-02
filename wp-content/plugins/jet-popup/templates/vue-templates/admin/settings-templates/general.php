<?php // phpcs:disable ?>
<div
	class="jet-popup-settings-page jet-popup-settings-page__general"
>
    <cx-vui-switcher
        name="use-content-cache"
        label="<?php _e( 'Use Cache for Content', 'jet-popup' ); ?>"
        description="<?php _e( 'Using cache for popup content. Increases page rendering speed', 'jet-popup' ); ?>"
        :wrapper-css="[ 'equalwidth' ]"
        :return-true="true"
        :return-false="false"
        v-model="pageOptions.useContentCache.enable"
    >
    </cx-vui-switcher>

    <cx-vui-switcher
        v-if="pageOptions.useContentCache.enable"
        name="cache-by-url"
        label="<?php _e( 'Cache Content by URL', 'jet-popup' ); ?>"
        description="<?php _e( 'Popup content caching for each page separately', 'jet-popup' ); ?>"
        :wrapper-css="[ 'equalwidth' ]"
        :return-true="true"
        :return-false="false"
        v-model="pageOptions.useContentCache.cacheByUrl"
    >
    </cx-vui-switcher>

    <cx-vui-select
        v-if="pageOptions.useContentCache.enable"
        name="cache-expiration"
        label="<?php _e( 'Сache Expiration', 'jet-popup' ); ?>"
        description="<?php _e( 'Select a timeout for content caching. Select <b>None</b> for permanent cache. Changing this option will clear the content cache of all popups', 'jet-popup' ); ?>"
        :wrapper-css="[ 'equalwidth' ]"
        size="fullwidth"
        :options-list="cacheTimeoutOptions"
        v-model="pageOptions.useContentCache.cacheExpiration">
    </cx-vui-select>

    <div class="cx-vui-component cx-vui-component--equalwidth" v-if="pageOptions.useContentCache.enable">
        <div class="cx-vui-component__meta">
            <label class="cx-vui-component__label" for="cx_use-content-cache"><?php _e( 'Clear Cache', 'jet-popup' ); ?></label>
            <div class="cx-vui-component__desc"><?php _e( 'Clear the content cache for all popups', 'jet-popup' ); ?></div>
        </div>
        <div class="cx-vui-component__control">
            <cx-vui-button
                button-style="accent-border"
                size="mini"
                :loading="clearCacheStatus"
                @click="clearCache"
            >
                <span slot="label"><?php esc_html_e( 'Clear Cache', 'jet-popup' ); ?></span>
            </cx-vui-button>
        </div>
    </div>

</div>

<?php // phpcs:enable ?>
