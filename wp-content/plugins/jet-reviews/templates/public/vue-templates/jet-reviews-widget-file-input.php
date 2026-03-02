<div :class="classesList">
    <input class="" ref="fileInput" type="file" name="reviewMedia" multiple accept="image/*" @change="handleFiles" hidden>
    <div
        class="jet-reviews-widget-file-input__inner"
        @dragover.prevent="onDragOver"
        @dragleave="onDragLeave"
        @drop.prevent="onFileDrop"
    >
        <span v-html="uploadControlLabel"></span>
        <div
            class="jet-reviews-button jet-reviews-button--primary"
            tabindex="0"
            @click="triggerFileInput"
            @keyup.enter="triggerFileInput"
        >
            <span class="jet-reviews-button__icon" v-if="uploadIcon" v-html="uploadIcon"></span>
            <div class="jet-reviews-button__text">{{ buttonLabel }}</div>
        </div>
        <small v-html="maxSizeLabel"></small>
        <div
            class="jet-new-review-message"
            v-if="isMessageVisible"
        >
            <span>{{ messageText }}</span>
        </div>
        <div class="jet-reviews-widget-file-input__media-list" v-if="reviewMediaPreview.length">
            <div class="jet-reviews-widget-file-input__media-item" v-for="( img, i) in reviewMediaPreview" :key="i">
                <img :src="img" style="max-width: 100px; margin: 5px;" />
            </div>
        </div>
    </div>
</div>
