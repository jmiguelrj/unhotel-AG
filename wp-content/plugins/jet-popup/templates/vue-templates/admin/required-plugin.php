<div :class="classList">
    <div class="required-plugin-info">
        <div class="required-plugin-icon">
            <img :src="pluginData.badge" alt="">
        </div>
        <div class="required-plugin-name">
            <span>{{ pluginData.name }}</span>
        </div>
    </div>
    <div class="required-plugin-action">
        <cx-vui-button
            button-style="link-accent"
            size="link"
            v-if="installVisible"
            @click="pluginAction( 'install' )"
            :loading="pluginActionStatus"
        >
            <span slot="label"><?php esc_html_e( 'Install Plugin', 'jet-popup' ); ?></span>
        </cx-vui-button>
        <cx-vui-button
            button-style="link-accent"
            size="link"
            v-if="activateVisible"
            @click="pluginAction( 'activate' )"
            :loading="pluginActionStatus"
        >
            <span slot="label"><?php esc_html_e( 'Activate Plugin', 'jet-popup' ); ?></span>
        </cx-vui-button>
        <cx-vui-button
            button-style="link-accent"
            size="link"
            v-if="activateLicenseVisible"
            @click="pluginAction( 'activateLicense' )"
            :loading="pluginActionStatus"
        >
            <span slot="label"><?php esc_html_e( 'Activate License', 'jet-popup' ); ?></span>
        </cx-vui-button>
        <span class="required-plugin-activated-label" v-if="activatedLabelVisible"><?php esc_html_e( 'Plugin Active', 'jet-popup' ); ?></span>
    </div>
</div>
