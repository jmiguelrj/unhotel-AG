<div id="jet-limit-form-responses">

    <div class="jet-form-editor__row">
        <div class="jet-form-editor__row-label">{{ label( 'enable' ) }}</div>
        <div class="jet-form-editor__row-control">
            <input type="checkbox" :name="fieldName( 'enable' )" v-model="meta.enable" value="1">
            <input
                    v-if="meta._form_submissions && meta._form_submissions.count"
                    type="hidden"
                    :name="fieldName( '_form_submissions', 'count' )"
                    :value="meta._form_submissions.count"
            />
        </div>
    </div>
    <div v-show="meta.enable" class="jet-form-editor__row--wrapper">
        <div class="jet-form-editor__row">
            <div class="jet-form-editor__row-label">{{ label( 'limit' ) }}</div>
            <div class="jet-form-editor__row-control">
                <input type="number" :name="fieldName( 'limit' )" v-model="meta.limit">
            </div>
        </div>
        <div class="jet-form-editor__row">
            <div class="jet-form-editor__row-label">
                {{ label( 'closed_message' ) }}
                <div class="jet-form-editor__row-notice">
                    {{ help( 'closed_message' ) }}
                </div>
            </div>
            <div class="jet-form-editor__row-control">
                <textarea :name="fieldName( 'closed_message' )" v-model="meta.closed_message"></textarea>
            </div>
        </div>
    </div>
    <div class="jet-form-editor__row">
        <div class="jet-form-editor__row-label">{{ label( 'restrict_users' ) }}</div>
        <div class="jet-form-editor__row-control">
            <input type="checkbox" :name="fieldName( 'restrict_users' )" v-model="meta.restrict_users" value="1">
        </div>
    </div>
    <div v-show="meta.restrict_users" class="jet-form-editor__row--wrapper">
        <div class="jet-form-editor__row">
            <div class="jet-form-editor__row-label">{{ label( 'restrict_by' ) }}</div>
            <div class="jet-form-editor__row-control">
                <select :name="fieldName( 'restrict_by' )" v-model="meta.restrict_by">
                    <option v-for="item in options.restrict_by" :value="item.value" :key="item.value">{{ item.label }}
                    </option>
                </select>
            </div>
        </div>
        <div class="jet-form-editor__row">
            <div class="jet-form-editor__row-label">
                {{ label( 'restricted_message' ) }}
                <div class="jet-form-editor__row-notice">
                    {{ help( 'restricted_message' ) }}
                </div>
            </div>
            <div class="jet-form-editor__row-control">
                <textarea :name="fieldName( 'restricted_message' )" v-model="meta.restricted_message"></textarea>
            </div>
        </div>
        <div v-show="'user' === meta.restrict_by" class="jet-form-editor__row--wrapper">
            <div class="jet-form-editor__row">
                <div class="jet-form-editor__row-label">{{ label( 'guest_message' ) }}</div>
                <div class="jet-form-editor__row-control">
                    <textarea :name="fieldName( 'guest_message' )" v-model="meta.guest_message"></textarea>
                </div>
            </div>
        </div>
    </div>
    <div class="jet-form-editor__row" v-show="meta.restrict_users || meta.enable">
        <div class="jet-form-editor__row-label">
            {{ label( 'error_message' ) }}
            <div class="jet-form-editor__row-notice">
                {{ help( 'error_message' ) }}
            </div>
        </div>
        <div class="jet-form-editor__row-control">
            <textarea :name="fieldName( 'error_message' )" v-model="meta.error_message"></textarea>
        </div>
    </div>
</div>