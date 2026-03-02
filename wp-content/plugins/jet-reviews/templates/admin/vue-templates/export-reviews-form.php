<div
    class="jet-reviews-admin-form export-form"
>
    <div class="jet-reviews-admin-form__header">
        <div class="jet-reviews-admin-form__header-title"><?php _e( 'Export Reviews', 'jet-reviews' ); ?></div>
        <p class="jet-reviews-admin-form__header-sub-title"><?php _e( 'Here you can export a reviews.', 'jet-reviews' ); ?></p>
    </div>
    <div class="jet-reviews-admin-form__body">
        <cx-vui-select
            name="source-select"
            label="<?php _e( 'Source', 'jet-reviews' ); ?>"
            :wrapper-css="[ 'vertical-fullwidth' ]"
            size="fullwidth"
            :options-list="sourceOptions"
            v-model="source"
        >
        </cx-vui-select>
        <cx-vui-select
            name="source-type-select"
            label="<?php _e( 'Source Type', 'jet-reviews' ); ?>"
            :wrapper-css="[ 'vertical-fullwidth' ]"
            size="fullwidth"
            :options-list="sourceTypeOptions"
            v-model="sourceType"
        >
        </cx-vui-select>
    </div>
    <div class="jet-reviews-admin-form__footer">
        <cx-vui-button
            button-style="default"
            class="cx-vui-button--style-accent-border"
            size="mini"
            @on-click="closePopupHandler"
        >
            <template v-slot:label>
                <span><?php _e( 'Cancel', 'jet-reviews' ); ?></span>
            </template>
        </cx-vui-button>
        <cx-vui-button
            button-style="default"
            class="cx-vui-button--style-accent"
            size="mini"
            @click="exportHandler"
            :loading="exportStatus"
        >
            <span slot="label"><?php _e( 'Export', 'jet-reviews' ); ?></span>
        </cx-vui-button>
    </div>
</div>