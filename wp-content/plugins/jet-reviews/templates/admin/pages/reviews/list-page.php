<?php
/**
 * Review List template
 */
?><div id="jet-reviews-list-page" class="jet-reviews-admin-page jet-reviews-admin-page--reviews-list">
    <hr class="wp-header-end">
    <div class="jet-reviews-admin-page__list-view" v-if="'list'===viewState">
        <div class="jet-reviews-admin-page__header">
            <h1 class="wp-heading-inline"><?php _e( 'Review List', 'jet-reviews' ); ?></h1>
            <div class="jet-reviews-admin-page__actions">
                <cx-vui-button
                        button-style="accent-border"
                        size="mini"
                        @click="openExportReviewPopup"
                >
                    <span slot="label"><?php _e( 'Export', 'jet-reviews' ); ?></span>
                </cx-vui-button>
                <cx-vui-button
                        button-style="accent-border"
                        size="mini"
                        @click="openImportReviewPopup"
                >
                    <span slot="label"><?php _e( 'Import', 'jet-reviews' ); ?></span>
                </cx-vui-button>
            </div>
        </div>
        <div class="jet-reviews-admin-page__filters">
            <div class="jet-reviews-admin-page__filter">
                <label for="cx_search-review-input"><?php _e( 'Bulk Actions', 'jet-reviews' ); ?></label>
                <div class="jet-reviews-admin-page__filter-form">
                    <cx-vui-select
                            name="bulk-action"
                            :wrapper-css="[ 'equalwidth' ]"
                            size="fullwidth"
                            :prevent-wrap="true"
                            :options-list="[
                        {
                            value: '',
                            label: '<?php _e( 'Select action', 'jet-reviews' ); ?>'
                        },
                        {
                            value: 'unapprove',
                            label: '<?php _e( 'Unapprove', 'jet-reviews' ); ?>'
                        },
                        {
                            value: 'approve',
                            label: '<?php _e( 'Approve', 'jet-reviews' ); ?>'
                        },
                        {
                            value: 'delete',
                            label: '<?php _e( 'Delete', 'jet-reviews' ); ?>'
                        }
                    ]"
                            v-model="bulkAction">
                    </cx-vui-select>
                    <cx-vui-button
                            button-style="accent-border"
                            size="mini"
                            @click="bulkActionHandle"
                            :loading="bulkActionStatus"
                    >
                        <span slot="label"><?php _e( 'Apply', 'jet-reviews' ); ?></span>
                    </cx-vui-button>
                </div>
            </div>
            <div class="jet-reviews-admin-page__filter">
                <label for="cx_search-review-input"><?php _e( 'Search reviews', 'jet-reviews' ); ?></label>
                <div class="jet-reviews-admin-page__filter-form">
                    <cx-vui-input
                            name="search-review-input"
                            :wrapper-css="[ 'equalwidth' ]"
                            size="fullwidth"
                            :prevent-wrap="true"
                            type="text"
                            v-model="titleSearchText"
                    >
                    </cx-vui-input>
                    <cx-vui-button
                            button-style="accent-border"
                            size="mini"
                            @click="searchReviewHandle"
                            :loading="searchingState"
                    >
                        <span slot="label"><?php _e( 'Search', 'jet-reviews' ); ?></span>
                    </cx-vui-button>
                </div>
            </div>
        </div>
        <div class="jet-reviews-admin-page__content">
            <cx-vui-list-table
                    :is-empty="0 === itemsList.length"
                    empty-message="<?php _e( 'No reviews found', 'jet-reviews' ); ?>"
                    class="jet-reviews-admin-page__table jet-reviews-admin-page__table--reviews"
                    :class="{ 'loading-status': reviewsGetting || actionExecution }"
            >
                <cx-vui-list-table-heading
                        :slots="[ 'check', 'author', 'content', 'rating', 'post', 'date', 'actions' ]"
                        slot="heading"
                >
                    <div slot="check">
                        <cx-vui-switcher
                                name="bulk-check"
                                :prevent-wrap="true"
                                :return-true="true"
                                :return-false="false"
                                v-model="bulkCheck"
                        >
                        </cx-vui-switcher>
                    </div>
                    <div slot="author"><?php _e( 'Author', 'jet-reviews' ); ?></div>
                    <div slot="content"><?php _e( 'Content', 'jet-reviews' ); ?></div>
                    <div slot="rating"><?php _e( 'Rating', 'jet-reviews' ); ?></div>
                    <div slot="post"><?php _e( 'Source', 'jet-reviews' ); ?></div>
                    <div slot="date"><?php _e( 'Date', 'jet-reviews' ); ?></div>
                    <div slot="actions"><?php _e( 'Actions', 'jet-reviews' ); ?></div>
                </cx-vui-list-table-heading>
                <cx-vui-list-table-item
                    :class="{ 'not-approved': ! item.approved }"
                    :slots="[ 'check', 'author', 'content', 'rating', 'post', 'date', 'actions' ]"
                    slot="items"
                    v-for="( item, index ) in itemsList"
                    :key="index"
                >
                    <div slot="check">
                        <cx-vui-switcher
                            name="`bulk-check-${index}`"
                            :prevent-wrap="true"
                            :return-true="true"
                            :return-false="false"
                            v-model="item.check"
                        >
                        </cx-vui-switcher>
                    </div>
                    <div slot="author">
                        <div class="author-data">
                            <a class="author-data__avatar" :href="item.author.url" v-html="item.author.avatar"></a>
                            <div class="author-data__info">
                                <b>{{ item.author.name }}</b>
                                <i>{{ item.author.mail }}</i>
                                <div class="author-data__roles" v-html="getRolesLabel( item.author.roles )"></div>
                            </div>
                        </div>
                    </div>
                    <div slot="content">
                        <div><b v-html="item.title"></b></div>
                        <div><i v-html="item.content"></i></div>
                        <div v-if="0 !== item.comments_count">
                            <a :href="`${ commentsPageUrl }&review-id=${ item.id }`">
                                <span><?php _e( 'Comments: ', 'jet-reviews' ); ?></span>
                                <span>{{ item.comments_count }}</span>
                            </a>
                        </div>
                        <div class="count-badges">
                            <div class="count-badge">
                                <span class="dashicons dashicons-format-image"></span>
                                <span>{{ item.media.length }}</span>
                            </div>
                            <div class="count-badge">
                                <span class="dashicons dashicons-thumbs-up"></span>
                                <span>{{ item.likes }}</span>
                            </div>
                            <div class="count-badge">
                                <span class="dashicons dashicons-thumbs-down"></span>
                                <span>{{ item.dislikes }}</span>
                            </div>
                        </div>
                    </div>
                    <div slot="rating" v-html="getRating( item.rating )"></div>
                    <div slot="post">
                        <div class="source-data">
                            <div class="source-data__item">
                                <i><?php _e( 'Source: ', 'jet-reviews' ); ?></i><span>{{ item.source }}</span>
                            </div>
                            <div class="source-data__item">
                                <i><?php _e( 'Source type: ', 'jet-reviews' ); ?></i><span>{{ item.source_type }}</span>
                            </div>
                        </div>
                    </div>
                    <div slot="date">{{ item.date }}</div>
                    <div slot="actions">
					<span
                            class="approve-action"
                            @click='approveHandler( [ item.id ] )'
                    >
						<span v-if="item.approved" :style="{ color: '#d98500'}"><?php _e( 'Unapprove', 'jet-reviews' ); ?></span>
						<span v-if="!item.approved" :style="{ color: '#46B450'}"><?php _e( 'Approve', 'jet-reviews' ); ?></span>
					</span>
                        <span>|</span>
                        <span
                                class="edit-action"
                                @click='openEditReviewPopup( index )'
                        ><?php
						    _e( 'Edit', 'jet-reviews' );
						    ?></span>
                        <span>|</span>
                        <span
                                class="delete-action"
                                @click='deleteReviewHandle( [ item.id ] )'
                        ><?php
						    _e( 'Delete', 'jet-reviews' );
						    ?></span>
                    </div>
                </cx-vui-list-table-item>
            </cx-vui-list-table>
            <div
                class="jet-reviews-admin-page__pagination"
                v-if="0 !== itemsList.length"
            >
                <cx-vui-pagination
                    :total="reviewsCount"
                    :page-size="pageSize"
                    @on-change="changePage"
                ></cx-vui-pagination>
            </div>
        </div>
    </div>
    <div class="jet-reviews-admin-page__edit-view" v-if="'edit'===viewState">
        <div class="jet-reviews-admin-page__header">
            <h1 class="wp-heading-inline"><?php _e( 'Edit Review', 'jet-reviews' ); ?></h1>
            <div class="jet-reviews-admin-page__actions">
                <cx-vui-button
                    button-style="accent-border"
                    size="mini"
                    @click="viewState='list'"
                >
                    <span slot="label"><?php _e( 'Back to list', 'jet-reviews' ); ?></span>
                </cx-vui-button>
            </div>
        </div>
        <div class="jet-reviews-admin-page__content">
            <div class="jet-reviews-admin-page__content-inner">
                <div class="cx-vui-subtitle"><?php _e( 'Review Content', 'jet-reviews' ); ?></div>
                <div class="cx-vui-panel">
                    <cx-vui-input
                        name="review-title"
                        label="<?php _e( 'Title', 'jet-reviews' ); ?>"
                        description="<?php _e( 'Edit review title', 'jet-reviews' ); ?>"
                        :wrapper-css="[ 'equalwidth' ]"
                        size="fullwidth"
                        type="text"
                        v-model="editReviewData.title"
                    >
                    </cx-vui-input>

                    <cx-vui-textarea
                        name="review-content"
                        label="<?php _e( 'Content', 'jet-reviews' ); ?>"
                        description="<?php _e( 'Edit review content', 'jet-reviews' ); ?>"
                        :wrapper-css="[ 'equalwidth' ]"
                        size="fullwidth"
                        v-model="editReviewData['content']"
                        :rows="9"
                    >
                    </cx-vui-textarea>

                    <div class="cx-vui-component cx-vui-component--equalwidth">
                        <div class="cx-vui-component__meta">
                            <label class="cx-vui-component__label"><?php _e( 'Rating Fields', 'jet-reviews' ); ?></label>
                        </div>
                        <div class="cx-vui-component__control">
                            <div class="edit-review__fields">
                                <div
                                        class="edit-review__field"
                                        v-for="(fieldData, index) in editReviewData.rating_data"
                                >
                                    <b><?php _e( 'Name:', 'jet-reviews' ); ?></b><span>{{ fieldData.field_label }}</span>
                                    <b><?php _e( 'Step:', 'jet-reviews' ); ?></b><span>{{ fieldData.field_step }}</span>
                                    <b><?php _e( 'Max:', 'jet-reviews' ); ?></b><span>{{ fieldData.field_max }}</span>
                                    <b><?php _e( 'Value:', 'jet-reviews' ); ?></b><span>{{ fieldData.field_value }}</span>
                                    <b><?php _e( 'Rating:', 'jet-reviews' ); ?></b><span>{{ Math.round( +fieldData.field_value * 100 / +fieldData.field_max ) }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="cx-vui-component cx-vui-component--equalwidth" v-if="0 !== editReviewData.media.length">
                        <div class="cx-vui-component__meta">
                            <label class="cx-vui-component__label"><?php _e( 'Media', 'jet-reviews' ); ?></label>
                            <div class="cx-vui-component__desc"><?php _e( 'Attached media files for review', 'jet-reviews' ); ?></div>
                        </div>
                        <div class="cx-vui-component__control">
                            <div class="media-list" :class="{ 'loading-status': actionExecution }">
                                <div
                                    class="media-item"
                                    v-for="(mediaData, index) in editReviewData.media"
                                >
                                    <img :src="mediaData.media_url" alt="">
                                    <div class="media-item-delete" @click='deleteReviewMediaHandle( [ mediaData.id ] )'>
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5.99982 19C5.99982 20.1 6.89982 21 7.99982 21H15.9998C17.0998 21 17.9998 20.1 17.9998 19V7H5.99982V19ZM18.9998 4H15.4998L14.4998 3H9.49982L8.49982 4H4.99982V6H18.9998V4Z" fill="#D6336C"></path></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="jet-reviews-admin-page__sidebar">
                <div class="jet-reviews-admin-page__sidebar-inner">
                    <div class="cx-vui-subtitle"><?php _e( 'Review Details', 'jet-reviews' ); ?></div>
                    <div class="jet-reviews-info-items">
                        <div class="jet-reviews-info-item">
                            <div class="review-author">
                                <div class="review-author-avatar" v-html="editReviewData.author.avatar"></div>
                                <div class="review-author-info">
                                    <span><b><?php _e( 'Login: ', 'jet-reviews' ); ?></b>{{ editReviewData.author.name }}</span>
                                    <span><b><?php _e( 'Mail: ', 'jet-reviews' ); ?></b>{{ editReviewData.author.mail }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="jet-reviews-info-item">
                            <b><?php _e( 'Date: ', 'jet-reviews' ); ?></b><span>{{ editReviewData.date }}</span>
                        </div>
                        <div class="jet-reviews-info-item">
                            <b><?php _e( 'Source: ', 'jet-reviews' ); ?></b><span>{{ editReviewData.source }}</span>
                        </div>
                        <div class="jet-reviews-info-item">
                            <b><?php _e( 'Source Type: ', 'jet-reviews' ); ?></b><span>{{ editReviewData.source_type }}</span>
                        </div>
                        <div class="jet-reviews-info-item">
                            <b><?php _e( 'Assigned to: ', 'jet-reviews' ); ?></b><a class="link" target="_blank" :href="editReviewData.post.link">{{ editReviewData.post.title }}</a>
                        </div>
                        <div class="jet-reviews-info-item">
                            <b><?php _e( 'Review Type: ', 'jet-reviews' ); ?></b><span>{{ editReviewData.type_slug }}</span>
                        </div>
                    </div>
                    <div class="cx-vui-subtitle"><?php _e( 'Actions', 'jet-reviews' ); ?></div>
                    <div class="jet-reviews-admin-page__actions-buttons">
                        <cx-vui-button
                            button-style="accent"
                            :loading="reviewSavingState"
                            @click="saveReviewHandle"
                        >
                            <svg slot="label" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.6667 5.33333V1.79167H1.79167V5.33333H10.6667ZM6.125 13.4167C6.65278 13.9444 7.27778 14.2083 8 14.2083C8.72222 14.2083 9.34722 13.9444 9.875 13.4167C10.4028 12.8889 10.6667 12.2639 10.6667 11.5417C10.6667 10.8194 10.4028 10.1944 9.875 9.66667C9.34722 9.13889 8.72222 8.875 8 8.875C7.27778 8.875 6.65278 9.13889 6.125 9.66667C5.59722 10.1944 5.33333 10.8194 5.33333 11.5417C5.33333 12.2639 5.59722 12.8889 6.125 13.4167ZM12.4583 0L16 3.54167V14.2083C16 14.6806 15.8194 15.0972 15.4583 15.4583C15.0972 15.8194 14.6806 16 14.2083 16H1.79167C1.29167 16 0.861111 15.8194 0.5 15.4583C0.166667 15.0972 0 14.6806 0 14.2083V1.79167C0 1.31944 0.166667 0.902778 0.5 0.541667C0.861111 0.180556 1.29167 0 1.79167 0H12.4583Z" fill="white"/></svg>
                            <span slot="label"><?php _e( 'Save Review Data', 'jet-reviews' ) ?></span>
                        </cx-vui-button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <transition name="popup">
        <cx-vui-popup
            class="jet-reviews-admin-page__popup export-popup"
            v-model="exportPopupVisible"
            :header="false"
            :footer="false"
            body-width="480px"
        >
            <div slot="content">
                <jet-reviews-export-reviews-form></jet-reviews-export-reviews-form>
            </div>
        </cx-vui-popup>
    </transition>

    <transition name="popup">
        <cx-vui-popup
            class="jet-reviews-admin-page__popup import-popup"
            v-model="importPopupVisible"
            :header="false"
            :footer="false"
        >
            <div slot="content">
                <jet-reviews-import-reviews-form></jet-reviews-import-reviews-form>
            </div>
        </cx-vui-popup>
    </transition>
</div>
