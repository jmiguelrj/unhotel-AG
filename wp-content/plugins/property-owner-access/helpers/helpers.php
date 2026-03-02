<?php
// Show notifications
function extractSessionNotifications()
{
    if (isset($_SESSION['poa-notifications'])) {
        $notifications = $_SESSION['poa-notifications'];
        unset($_SESSION['poa-notifications']);
        return $notifications;
    }
    return null;
}

// Upload file
function uploadFile($file, $type, $folder, $replaceOldFile = false)
{
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = date('Ymd') . '-' . $type . '-' . uniqid() . '.' . $fileExtension;
    $file['name'] = $fileName;
    $uploadsFolder = wp_upload_dir()['basedir'] . $folder;
    // Check if uploads folder exists, if not, create it
    if (!file_exists($uploadsFolder)) {
        wp_mkdir_p($uploadsFolder);
    }
    // Move the file to uploads folder
    add_filter('upload_dir', 'propertyowneraccess_' . $type . '_upload_dir');
    $uploadFile = wp_handle_upload($file);
    remove_filter('upload_dir', 'propertyowneraccess_' . $type . '_upload_dir');
    // Check if the file was moved
    if ($uploadFile) {
        if (!isset($uploadFile['error'])) {
            // If file was moved and old file should be replaced, delete the old file
            if ($replaceOldFile) {
                removeFile($replaceOldFile, $folder);
            }
            return $fileName;
        } else {
            // Return the error message
            $_SESSION['poa-notifications'][] = ['type' => 'error', 'message' => 'Error uploading the attachment.'];
        }
    }
    return null;
}

// Remove uploaded file
function removeFile($file, $folder)
{
    $file = wp_upload_dir()['basedir'] . $folder . '/' . $file;
    if (file_exists($file)) {
        return unlink($file);
    }
    return false;
}

// Check if user is not logged in or does not have the required role
function checkAuthorized($role = null)
{
    if (!is_user_logged_in() || (!current_user_can('administrator') && !current_user_can('homey_host'))) {
        wp_redirect(wp_login_url());
    } elseif ($role && !current_user_can($role)) {
        wp_redirect(site_url());
    } else {
        return true;
    }
    exit;
}

// Check if user owns the property he is trying to access
function checkOwnership($propertyId)
{
    $current_user = wp_get_current_user();
    $property = PropertyOwner::where('user_id', $current_user->ID)
        ->where('room_id', $propertyId)
        ->first();
    if (!$property) {
        wp_redirect(site_url());
        exit;
    }
}

// Get homey theme user profile picture
function getHomeyUserProfilePicture($userId)
{
    $author_picture_id = get_the_author_meta('homey_author_picture_id', $userId);
    if (!empty($author_picture_id)) {
        $author_picture_id = intval($author_picture_id);
        if ($author_picture_id) {
            $photo = wp_get_attachment_image($author_picture_id, array(240, 240));
            return $photo;
        }
    }
    return false;
}

// Format amount
function formatAmount($number)
{
    if (empty($number)) {
        return 0;
    }
    if (!is_numeric($number)) {
        $number = str_replace(",", "", $number);
        $number = floatval($number);
    }
    return number_format($number, 2, ',', '.');
}

// Sort the properties by the number from the first word of the name
function sortProperties($properties)
{
    return $properties->sortBy(function ($property) {
        return (int)preg_replace('/[^0-9]/', '', explode(' ', $property->name)[0]);
    });
}

// Change language based on url parameter
function changeLanguage($lang = 'pt')
{
    global $sitepress;
    if (isset($sitepress) && $sitepress instanceof SitePress) {
        $sitepress->switch_lang($lang);
    }
}

// Get POA url
function getPoaUrl($url)
{
    global $sitepress;
    $currentLanguage = 'pt';
    $url = trim($url, '/');
    if (isset($sitepress) && $sitepress instanceof SitePress) {
        $currentLanguage = $sitepress->get_current_language();
        $currentLanguage = explode('-', $currentLanguage)[0];
    }
    return site_url() . '/poa/' . $currentLanguage . '/' . $url;
}

// Translate databases with WPML
function translatePoaTables($lang = 'pt-BR')
{
    global $sitepress;
    if (isset($sitepress) && $sitepress instanceof SitePress) {
        // Register the strings for transfer methods
        $transferMethods = TransferMethod::all();
        foreach ($transferMethods as $transferMethod) {
            do_action('wpml_register_single_string', 'propery-owner-access', 'transfer-method-' . $transferMethod->id, $transferMethod->name, false, $lang);
        }
        // Register the strings for expense categories
        $expenseCategories = ExpenseCategory::all();
        foreach ($expenseCategories as $expenseCategory) {
            do_action('wpml_register_single_string', 'propery-owner-access', 'expense-category-' . $expenseCategory->id, $expenseCategory->name, false, $lang);
        }
    }
}

// Retrieve WPML translated strings
function getTranslatedPoaString($string, $id)
{
    global $sitepress;
    if (isset($sitepress) && $sitepress instanceof SitePress) {
        return apply_filters('wpml_translate_single_string', $string, 'propery-owner-access', $id);
    }
    return $string;
}

// Switch to host
// User Switching plugin is required | https://github.com/johnbillion/user-switching
function switchToHost($hostId, $propertyId=null) {
    // Get host wp user
    $hostId = get_user_by( 'id', $hostId );
    if ( !empty($hostId) && method_exists( 'user_switching', 'maybe_switch_url' ) ) {
        $url = user_switching::maybe_switch_url( $hostId );
        if ( $url ) {
            echo '
            <a href="'.$url.( ( !empty($propertyId) ) ? '&redirect_to='.getPoaUrl('properties/'.$propertyId) : '' )  .'" class="action-btn" data-tippy-content="Switch to '.$hostId->display_name.'">
                <i class="las la-user-circle"></i>
            </a>
            ';
        }
    }
}

// Switch back to admin
// User Switching plugin is required | https://github.com/johnbillion/user-switching
function switchBackToAdmin() {
    if ( method_exists( 'user_switching', 'get_old_user' ) ) {
        $old_user = user_switching::get_old_user();
        if ( $old_user ) {
            echo '
            <div class="property-manager-user">
                You are logged in as a property owner.
                <a href="'.esc_url( user_switching::switch_back_url( $old_user ) ).'&redirect_to='.getPoaUrl('admin/properties').'">
                    Switch back to '.esc_html( $old_user->display_name ).'
                </a>
            </div>
            ';
        }
    }
}