<div
    class="jet-reviews-admin-form import-form"
>
    <div class="jet-reviews-admin-form__header">
        <div class="jet-reviews-admin-form__header-title"><?php _e( 'Import Reviews', 'jet-reviews' ); ?></div>
        <p class="jet-reviews-admin-form__header-sub-title"><?php _e( 'Here you can import a reviews.', 'jet-reviews' ); ?></p>
    </div>
    <div class="jet-reviews-admin-form__body">
        <form enctype="multipart/form-data" novalidate v-if="!readyToImport">
            <div class="dropbox">
                <input
                    type="file"
                    ref="file"
                    :disabled="parseProgressState"
                    @change="prepareToImport( $event.target.files )"
                    accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
                >
            </div>
        </form>
        <div class="jet-reviews-parsed-data-table" v-if="readyToImport">
            <cx-vui-list-table
                :is-empty="0 === tableHeaderData.length"
                empty-message="<?php _e( 'No Data found', 'jet-reviews' ); ?>"
                class="jet-reviews-admin-page__table"
            >
                <cx-vui-list-table-heading
                    :slots="tableHeaderData"
                    slot="heading"
                >
                    <div
                        v-for="(headerItem, index) in tableHeaderData"
                        :key="index"
                        :slot="headerItem"
                    >{{headerItem}}</div>
                </cx-vui-list-table-heading>
                <cx-vui-list-table-item
                    :slots="tableHeaderData"
                    slot="items"
                    v-for="( rowData, index ) in rawParsedData"
                    :key="index"
                >
                    <div
                        v-for="(headerItem, index) in tableHeaderData"
                        :key="index"
                        :slot="headerItem"
                    >{{ rowData[ headerItem ] }}</div>
                </cx-vui-list-table-item>

            </cx-vui-list-table>
        </div>
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
            @on-click="parseFileHandler"
            :loading="parseProgressState"
            :disabled="!readyToParse"
            v-if="!readyToImport"
        >
            <template v-slot:label>
                <span><?php _e( 'Parse', 'jet-reviews' ); ?></span>
            </template>
        </cx-vui-button>

        <cx-vui-button
            button-style="default"
            class="cx-vui-button--style-accent"
            size="mini"
            @on-click="importHandler"
            :loading="parseImportState"
            v-if="readyToImport"
        >
            <template v-slot:label>
                <span><?php _e( 'Import', 'jet-reviews' ); ?></span>
            </template>
        </cx-vui-button>
    </div>
</div>