<?php
/**
 * Review Type template
 */
?>
<hr class="wp-header-end">
<div id="jet-reviews-type-page" class="jet-reviews-admin-page jet-reviews-admin-page--review-type">
    <div class="jet-reviews-admin-page__header">
        <h1 class="wp-heading-inline"><?php $this->get_page_title(); ?></h1>
    </div>
    <div class="jet-reviews-admin-page__content">
        <div class="jet-reviews-admin-page__content-inner">
            <cx-vui-collapse
                :collapsed="false"
            >
                <h3 class="cx-vui-subtitle" slot="title"><?php _e( 'General Settings', 'jet-engine' ); ?></h3>
                <div class="cx-vui-panel" slot="content">
                    <cx-vui-input
                        name="name"
                        label="<?php _e( 'Name', 'jet-reviews' ); ?>"
                        description="<?php _e( 'Set unique name for your review type. Eg. `Projects`', 'jet-reviews' ); ?>"
                        :wrapper-css="[ 'equalwidth' ]"
                        size="fullwidth"
                        v-model="name"
                    >
                    </cx-vui-input>

                    <cx-vui-input
                        name="slug"
                        label="<?php _e( 'Slug', 'jet-reviews' ); ?>"
                        description="<?php _e( 'Set slug for your review type. Slug should contain only letters, numbers and `-` or `_` chars', 'jet-reviews' ); ?>"
                        :wrapper-css="[ 'equalwidth' ]"
                        size="fullwidth"
                        v-model="slug"
                    >
                    </cx-vui-input>

                    <cx-vui-select
                        name="source"
                        label="<?php _e( 'Source', 'jet-reviews' ); ?>"
                        description="<?php _e( 'Choose review source', 'jet-reviews' ); ?>"
                        :wrapper-css="[ 'equalwidth' ]"
                        size="fullwidth"
                        :options-list="sourceOptions"
                        v-model="typeSettings['source']">
                    </cx-vui-select>

                    <cx-vui-select
                        name="source-type"
                        label="<?php _e( 'Source Type', 'jet-reviews' ); ?>"
                        description="<?php _e( 'Choose review source type', 'jet-reviews' ); ?>"
                        :wrapper-css="[ 'equalwidth' ]"
                        size="fullwidth"
                        :options-list="sourceTypeOptions"
                        v-model="typeSettings['source_type']">
                    </cx-vui-select>
                </div>
            </cx-vui-collapse>
            <cx-vui-collapse
                :collapsed="false"
            >
                <h3 class="cx-vui-subtitle" slot="title"><?php _e( 'Advanced Settings', 'jet-engine' ); ?></h3>
                <div class="cx-vui-panel" slot="content">

                    <div class="cx-vui-component cx-vui-component--equalwidth">
                        <div class="cx-vui-component__meta">
                            <label class="cx-vui-component__label" for="cx_allowed-roles"><?php _e( 'Review Fields', 'jet-reviews' ); ?></label>
                            <div class="cx-vui-component__desc"><?php _e( 'Leave blank if you want to use the default rating fields or create your own set of fields to be used for this type of review', 'jet-reviews' ); ?></div>
                        </div>
                        <div class="cx-vui-component__control">
                            <label class="cx-vui-component__label" for="cx_allowed-roles"><?php _e( 'Default fields:', 'jet-reviews' ); ?></label>
                            <div class="review-default-fields">
                                <div class="review-default-field" v-for="( field, index ) in defaultReviewFields" :index="index">
                                    <div class="review-default-field-attr">
                                        <span><?php _e( 'Label:', 'jet-reviews' ); ?></span><i>{{ field.label }}</i>
                                    </div>
                                    <div class="review-default-field-attr">
                                        <span><?php _e( 'Step:', 'jet-reviews' ); ?></span><i>{{ field.step }}</i>
                                    </div>
                                    <div class="review-default-field-attr">
                                        <span><?php _e( 'Max value:', 'jet-reviews' ); ?></span><i>{{ field.max }}</i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="cx-vui-component cx-vui-component--fullwidth-control">
                        <div class="cx-vui-component__control">
                            <div class="cx-vui-inner-panel">
                                <cx-vui-repeater
                                        class="cx-vui-repeater__half-col"
                                        button-label="Add New Field"
                                        button-style="accent"
                                        button-size="mini"
                                        v-model="typeSettings['fields']"
                                        @add-new-item="addNewRatingField"
                                >
                                    <cx-vui-repeater-item
                                            v-for="( field, index ) in typeSettings['fields']"
                                            :title="typeSettings['fields'][ index ].label"
                                            :index="index"
                                            @clone-item="cloneRatingField( index )"
                                            @delete-item="deleteRatingField( index )"
                                            :key="index"
                                    >

                                        <cx-vui-input
                                                label="<?php _e( 'Label', 'jet-reviews' ); ?>"
                                                :wrapper-css="[ 'equalwidth' ]"
                                                type="text"
                                                :size="'fullwidth'"
                                                v-model="typeSettings['fields'][ index ].label"
                                        ></cx-vui-input>

                                        <cx-vui-input
                                                label="<?php _e( 'Step', 'jet-reviews' ); ?>"
                                                :wrapper-css="[ 'equalwidth' ]"
                                                type="number"
                                                :size="'fullwidth'"
                                                :min="0"
                                                v-model="typeSettings['fields'][ index ].step"
                                        ></cx-vui-input>

                                        <cx-vui-input
                                                label="<?php _e( 'Max Value', 'jet-reviews' ); ?>"
                                                :wrapper-css="[ 'equalwidth' ]"
                                                type="number"
                                                :size="'fullwidth'"
                                                :min="0"
                                                :step="1"
                                                v-model="typeSettings['fields'][ index ].max"
                                        ></cx-vui-input>

                                    </cx-vui-repeater-item>

                                </cx-vui-repeater>
                            </div>
                        </div>
                    </div>

                    <cx-vui-f-select
                        name="allowed-roles"
                        label="<?php _e( 'Allowed roles', 'jet-reviews' ); ?>"
                        description="<?php _e( 'User roles that can leave reviews', 'jet-reviews' ); ?>"
                        :wrapper-css="[ 'equalwidth' ]"
                        :placeholder="'Select...'"
                        :multiple="true"
                        :options-list="allRolesOptions"
                        autocomplete="<?php echo jet_reviews_tools()->generate_rand_string(); ?>"
                        v-model="typeSettings['allowed_roles']"
                    ></cx-vui-f-select>

                    <cx-vui-f-select
                        name="review-verifications"
                        label="<?php _e( 'Review author verification', 'jet-reviews' ); ?>"
                        description="<?php _e( 'Choose review author verification types', 'jet-reviews' ); ?>"
                        :wrapper-css="[ 'equalwidth' ]"
                        :placeholder="'Select...'"
                        :multiple="true"
                        :options-list="verificationOptions"
                        autocomplete="<?php echo jet_reviews_tools()->generate_rand_string(); ?>"
                        v-model="typeSettings['verifications']">
                    </cx-vui-f-select>

                    <cx-vui-f-select
                        name="review-comment-verifications"
                        label="<?php _e( 'Comment author verification', 'jet-reviews' ); ?>"
                        description="<?php _e( 'Choose сomment author verification types', 'jet-reviews' ); ?>"
                        :wrapper-css="[ 'equalwidth' ]"
                        :placeholder="'Select...'"
                        :multiple="true"
                        :options-list="verificationOptions"
                        autocomplete="<?php echo jet_reviews_tools()->generate_rand_string(); ?>"
                        v-model="typeSettings['comment_verifications']">
                    </cx-vui-f-select>

                    <cx-vui-switcher
                        name="need-approve"
                        label="<?php _e( 'New review approval', 'jet-reviews' ); ?>"
                        description="<?php _e( 'Need admin approval for a new review', 'jet-reviews' ); ?>"
                        :wrapper-css="[ 'equalwidth' ]"
                        :return-true="true"
                        :return-false="false"
                        v-model="typeSettings['need_approve']"
                    >
                    </cx-vui-switcher>

                    <cx-vui-switcher
                        name="comments-allowed"
                        label="<?php _e( 'Allow comments', 'jet-reviews' ); ?>"
                        description="<?php _e( 'Allow review comments for this type of post', 'jet-reviews' ); ?>"
                        :wrapper-css="[ 'equalwidth' ]"
                        :return-true="true"
                        :return-false="false"
                        v-model="typeSettings['comments_allowed']"
                    >
                    </cx-vui-switcher>

                    <cx-vui-switcher
                        name="comments-need-approve"
                        label="<?php _e( 'New review comments need approval', 'jet-reviews' ); ?>"
                        description="<?php _e( 'Need admin approval for a new review comment', 'jet-reviews' ); ?>"
                        :wrapper-css="[ 'equalwidth' ]"
                        :return-true="true"
                        :return-false="false"
                        v-model="typeSettings['comments_need_approve']"
                    >
                    </cx-vui-switcher>

                    <cx-vui-switcher
                        name="approval-allowed"
                        label="<?php _e( 'Allow review rate actions', 'jet-reviews' ); ?>"
                        description="<?php _e( 'Allow likes/dislikes for review items for this type of post', 'jet-reviews' ); ?>"
                        :wrapper-css="[ 'equalwidth' ]"
                        :return-true="true"
                        :return-false="false"
                        v-model="typeSettings['approval_allowed']"
                    >
                    </cx-vui-switcher>

                    <cx-vui-switcher
                        name="upload-media"
                        label="<?php _e( 'Upload media', 'jet-reviews' ); ?>"
                        description="<?php _e( 'Allow uploading media files for review', 'jet-reviews' ); ?>"
                        :wrapper-css="[ 'equalwidth' ]"
                        :return-true="true"
                        :return-false="false"
                        v-model="typeSettings['upload_media']"
                    >
                    </cx-vui-switcher>

                    <cx-vui-f-select
                        v-if="typeSettings['upload_media']"
                        name="allowed-media"
                        label="<?php _e( 'Allowed media', 'jet-reviews' ); ?>"
                        description="<?php _e( 'Choose сomment author verification types', 'jet-reviews' ); ?>"
                        :wrapper-css="[ 'equalwidth' ]"
                        :placeholder="'Select...'"
                        :multiple="true"
                        :options-list="allowedMediaOptions"
                        autocomplete="<?php echo jet_reviews_tools()->generate_rand_string(); ?>"
                        v-model="typeSettings['allowed_media']">
                    </cx-vui-f-select>

                    <cx-vui-input
                        v-if="typeSettings['upload_media']"
                        name="maxsize-media"
                        type="number"
                        label="<?php _e( 'Max upload file size(MB)', 'jet-reviews' ); ?>"
                        description="<?php _e( 'The maximum file size that can be uploaded for review', 'jet-reviews' ); ?>"
                        :wrapper-css="[ 'equalwidth' ]"
                        size="fullwidth"
                        :min="1"
                        :max="36"
                        v-model="typeSettings['maxsize_media']"
                    >
                    </cx-vui-input>
                </div>
            </cx-vui-collapse>

            <cx-vui-collapse
                :collapsed="false"
            >
                <h3 class="cx-vui-subtitle" slot="title"><?php _e( 'Source Metadata', 'jet-engine' ); ?></h3>
                <div class="cx-vui-panel" slot="content">
                    <cx-vui-switcher
                        name="metadata"
                        label="<?php _e( 'Use source metadata', 'jet-reviews' ); ?>"
                        description="<?php _e( 'Use source metadata for jet reviews data', 'jet-reviews' ); ?>"
                        :wrapper-css="[ 'equalwidth' ]"
                        :return-true="true"
                        :return-false="false"
                        v-model="typeSettings['metadata']"
                    >
                    </cx-vui-switcher>

                    <cx-vui-input
                        v-if="typeSettings['metadata']"
                        name="metadata-rating-key"
                        label="<?php _e( 'Average rating metadata key', 'jet-reviews' ); ?>"
                        description="<?php _e( 'Define meta key, which will store the average source rating.', 'jet-reviews' ); ?>"
                        :wrapper-css="[ 'equalwidth' ]"
                        size="fullwidth"
                        v-model="typeSettings['metadata_rating_key']"
                    >
                    </cx-vui-input>

                    <cx-vui-input
                        v-if="typeSettings['metadata']"
                        name="metadata_ratio_bound"
                        type="number"
                        label="<?php _e( 'Post meta ratio bound', 'jet-reviews' ); ?>"
                        description="<?php _e( 'Specify ratio conversion limit for source metadata.', 'jet-reviews' ); ?>"
                        :wrapper-css="[ 'equalwidth' ]"
                        size="fullwidth"
                        :min="1"
                        :max="100"
                        v-model="typeSettings['metadata_ratio_bound']"
                    >
                    </cx-vui-input>

                    <div class="cx-vui-component cx-vui-component--equalwidth" v-if="typeSettings['metadata']">
                        <div class="cx-vui-component__meta">
                            <label class="cx-vui-component__label"><?php _e( 'Sync average rating', 'jet-reviews' ); ?></label>
                            <div class="cx-vui-component__desc"><?php _e( 'Add/Update jet-rating data to source metadata', 'jet-reviews' ); ?></div>
                        </div>
                        <div class="cx-vui-component__control">
                            <cx-vui-button
                                button-style="accent-border"
                                size="mini"
                                :loading="syncSourceMetaStatus"
                                @click="syncRatingData()"
                            >
                                <span slot="label"><?php _e( 'Sync rating data', 'jet-reviews' ); ?></span>
                            </cx-vui-button>
                        </div>
                    </div>
                </div>
            </cx-vui-collapse>
            <cx-vui-collapse
                :collapsed="false"
            >
                <h3 class="cx-vui-subtitle" slot="title"><?php _e( 'Structure Data', 'jet-engine' ); ?></h3>
                <div class="cx-vui-panel" slot="content">
                    <cx-vui-switcher
                        name="structure-data"
                        label="<?php _e( 'Use Structure Data', 'jet-reviews' ); ?>"
                        description="<?php _e( 'Rendering structure data in JSON-LD format', 'jet-reviews' ); ?>"
                        :wrapper-css="[ 'equalwidth' ]"
                        :return-true="true"
                        :return-false="false"
                        v-model="typeSettings['structuredata']"
                    >
                    </cx-vui-switcher>

                    <cx-vui-select
                        v-if="typeSettings['structuredata']"
                        name="structure-data-type"
                        label="<?php _e( 'Type', 'jet-reviews' ); ?>"
                        description="<?php _e( 'Choose structure data type', 'jet-reviews' ); ?>"
                        :wrapper-css="[ 'equalwidth' ]"
                        size="fullwidth"
                        :options-list="structureDataTypesOptions"
                        v-model="typeSettings['structuredata_type']">
                    </cx-vui-select>

                </div>
            </cx-vui-collapse>
        </div>
        <div class="jet-reviews-admin-page__sidebar">
            <div class="jet-reviews-admin-page__sidebar-inner">
                <div class="cx-vui-subtitle"><?php _e( 'Actions', 'jet-reviews' ); ?></div>
                <div class="cx-vui-text"><?php
		            _e( 'If you are not sure where to start, please check tutorials list below this block', 'jet-reviews' );
		            ?></div>
                <div class="jet-reviews-admin-page__actions-buttons">
                    <cx-vui-button
                        v-if="'add' === pageAction"
                        button-style="accent"
                        :loading="actionProccessing"
                        @click="addTypeHandle"
                    >
                        <svg slot="label" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.6667 5.33333V1.79167H1.79167V5.33333H10.6667ZM6.125 13.4167C6.65278 13.9444 7.27778 14.2083 8 14.2083C8.72222 14.2083 9.34722 13.9444 9.875 13.4167C10.4028 12.8889 10.6667 12.2639 10.6667 11.5417C10.6667 10.8194 10.4028 10.1944 9.875 9.66667C9.34722 9.13889 8.72222 8.875 8 8.875C7.27778 8.875 6.65278 9.13889 6.125 9.66667C5.59722 10.1944 5.33333 10.8194 5.33333 11.5417C5.33333 12.2639 5.59722 12.8889 6.125 13.4167ZM12.4583 0L16 3.54167V14.2083C16 14.6806 15.8194 15.0972 15.4583 15.4583C15.0972 15.8194 14.6806 16 14.2083 16H1.79167C1.29167 16 0.861111 15.8194 0.5 15.4583C0.166667 15.0972 0 14.6806 0 14.2083V1.79167C0 1.31944 0.166667 0.902778 0.5 0.541667C0.861111 0.180556 1.29167 0 1.79167 0H12.4583Z" fill="white"/></svg>
                        <span slot="label"><?php _e( 'Create Review Type', 'jet-reviews' ) ?></span>
                    </cx-vui-button>
                    <cx-vui-button
                        v-if="'edit' === pageAction"
                        button-style="accent"
                        :disabled="!isSettingsChanged"
                        :loading="actionProccessing"
                        @click="saveTypeHandle"
                    >
                        <svg slot="label" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.6667 5.33333V1.79167H1.79167V5.33333H10.6667ZM6.125 13.4167C6.65278 13.9444 7.27778 14.2083 8 14.2083C8.72222 14.2083 9.34722 13.9444 9.875 13.4167C10.4028 12.8889 10.6667 12.2639 10.6667 11.5417C10.6667 10.8194 10.4028 10.1944 9.875 9.66667C9.34722 9.13889 8.72222 8.875 8 8.875C7.27778 8.875 6.65278 9.13889 6.125 9.66667C5.59722 10.1944 5.33333 10.8194 5.33333 11.5417C5.33333 12.2639 5.59722 12.8889 6.125 13.4167ZM12.4583 0L16 3.54167V14.2083C16 14.6806 15.8194 15.0972 15.4583 15.4583C15.0972 15.8194 14.6806 16 14.2083 16H1.79167C1.29167 16 0.861111 15.8194 0.5 15.4583C0.166667 15.0972 0 14.6806 0 14.2083V1.79167C0 1.31944 0.166667 0.902778 0.5 0.541667C0.861111 0.180556 1.29167 0 1.79167 0H12.4583Z" fill="white"/></svg>
                        <span slot="label"><?php _e( 'Update Review Type', 'jet-reviews' ) ?></span>
                    </cx-vui-button>
                    <cx-vui-button
                        v-if="'edit' === pageAction"
                        button-style="link-error"
                        :size="'link'"
                        :loading="deleteActionProccessing"
                        @click="deleteTypeHandle"
                    >
                        <svg slot="label" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2.28564 14.1921V3.42857H13.7142V14.1921C13.7142 14.6686 13.5208 15.089 13.1339 15.4534C12.747 15.8178 12.3005 16 11.7946 16H4.20529C3.69934 16 3.25291 15.8178 2.866 15.4534C2.4791 15.089 2.28564 14.6686 2.28564 14.1921Z"/><path d="M14.8571 1.14286V2.28571H1.14282V1.14286H4.57139L5.56085 0H10.4391L11.4285 1.14286H14.8571Z"/></svg>
                        <span slot="label"><?php _e( 'Delete', 'jet-reviews' ); ?></span>
                    </cx-vui-button>
                </div>
            </div>
        </div>
    </div>
</div>
