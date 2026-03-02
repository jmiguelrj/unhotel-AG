<?php
/**
 * File preview template for Advanced Media field.
 *
 * Used to display uploaded files with remove button and error indicator.
 * Supports placeholders: %file_url%, %file_name%
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
?>
<div class="jet-form-builder-advanced-media-file-upload__file">
    <!-- preview -->

    <button type="button"
            class="jet-form-builder-advanced-media-file-upload__file-remove"
            aria-label="Remove file"
            data-file-name="%file_name%">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
    </button>


    <!-- field -->
</div>