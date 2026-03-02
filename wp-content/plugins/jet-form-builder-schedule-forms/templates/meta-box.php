<div id="jet-schedule-forms">
    <div class="jet-form-editor__row">
        <div class="jet-form-editor__row-label">{{ label( 'enable' ) }}</div>
        <div class="jet-form-editor__row-control">
            <input type="checkbox" :name="fieldName( 'enable' )" v-model="schedule.enable" value="1">
        </div>
    </div>
    <div v-show="schedule.enable">
        <div class="jet-form-editor__row">
            <div class="jet-form-editor__row-label">{{ label( 'from_date' ) }}</div>
            <div class="jet-form-editor__row-control">
                <input type="datetime-local" :name="fieldName( 'from_date' )" v-model="schedule.from_date">
            </div>
        </div>
        <div class="jet-form-editor__row">
            <div class="jet-form-editor__row-label">{{ label( 'to_date' ) }}</div>
            <div class="jet-form-editor__row-control">
                <input type="datetime-local" :name="fieldName( 'to_date' )" v-model="schedule.to_date">
            </div>
        </div>
        <div class="jet-form-editor__row">
            <div class="jet-form-editor__row-label">
                {{ label( 'pending_message' ) }}
                <div class="jet-form-editor__row-notice">
                    {{ help( 'pending_message' ) }}
                </div>
            </div>
            <div class="jet-form-editor__row-control">
                <textarea :name="fieldName( 'pending_message' )" v-model="schedule.pending_message"></textarea>
            </div>
        </div>
        <div class="jet-form-editor__row">
            <div class="jet-form-editor__row-label">
                {{ label( 'expired_message' ) }}
                <div class="jet-form-editor__row-notice">
                    {{ help( 'expired_message' ) }}
                </div>
            </div>
            <div class="jet-form-editor__row-control">
                <textarea :name="fieldName( 'expired_message' )" v-model="schedule.expired_message"></textarea>
            </div>
        </div>
    </div>
</div>