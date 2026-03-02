<?php
/**
 * Plugin Name: VBO Custom Check-in Data Driver (Simplified)
 * Description: Custom data collector for Vik Booking: includes children, no required fields, date/file validations, placeholders, grouping. Simplified (no i18n, no ID-type validation).
 * Version: 1.1.1
 * Author: JM / Unhotel
 */

if (!defined('ABSPATH')) { exit; }

define('VBO_CID_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('VBO_CID_PLUGIN_URL', plugin_dir_url(__FILE__));

add_action('plugins_loaded', function () {
    require_once VBO_CID_PLUGIN_PATH . 'drivers/custom.php';
    require_once VBO_CID_PLUGIN_PATH . 'drivers/field-text.php';
    require_once VBO_CID_PLUGIN_PATH . 'drivers/field-textarea.php';
    require_once VBO_CID_PLUGIN_PATH . 'drivers/field-date.php';
    require_once VBO_CID_PLUGIN_PATH . 'drivers/field-select.php';
    require_once VBO_CID_PLUGIN_PATH . 'drivers/field-time.php';
    require_once VBO_CID_PLUGIN_PATH . 'drivers/field-file.php';
    require_once VBO_CID_PLUGIN_PATH . 'drivers/field-heading.php';
});

add_filter('vikbooking_load_paxdatafields_drivers', function ($drivers) {
    if (!is_array($drivers)) { $drivers = []; }
    $drivers['custom'] = VBO_CID_PLUGIN_PATH . 'drivers/custom.php';
    return $drivers;
});
