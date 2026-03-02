<?php
/**
 * Plugin Name: Unhotel Frontdesk
 * Description: Arrivals dashboard (Recepção) with REST API, caching, and inline editing.
 * Author: Unhotel
 * Version: 0.2.0
 */

if (!defined('ABSPATH')) exit;

define('UNHOTEL_FD_VERSION', '0.2.0');
define('UNHOTEL_FD_FILE', __FILE__);
define('UNHOTEL_FD_PATH', plugin_dir_path(__FILE__));
define('UNHOTEL_FD_URL',  plugin_dir_url(__FILE__));

require_once UNHOTEL_FD_PATH . 'includes/Installer.php';
require_once UNHOTEL_FD_PATH . 'includes/Admin.php';
require_once UNHOTEL_FD_PATH . 'includes/Rest.php';
require_once UNHOTEL_FD_PATH . 'includes/Shortcode.php';

/** Activation (normal plugins only) */
register_activation_hook(__FILE__, ['UnhotelFD\Installer','activate']);

/** Capabilities */
add_action('init', function () {
    $roles = ['editor','administrator'];
    foreach ($roles as $r) {
        if ($role = get_role($r)) {
            if (!$role->has_cap('manage_unhotel_frontdesk')) {
                $role->add_cap('manage_unhotel_frontdesk');
            }
        }
    }
});

/** Admin UI */
add_action('admin_menu', ['UnhotelFD\Admin','menu']);
add_action('admin_init', ['UnhotelFD\Admin','register_settings']);
add_action('admin_enqueue_scripts', function($hook){
    if ($hook === 'toplevel_page_unhotel-frontdesk') {
        wp_enqueue_style('unhotel-fd-admin', UNHOTEL_FD_URL.'assets/admin.css', [], UNHOTEL_FD_VERSION);
        wp_enqueue_script('unhotel-fd-admin', UNHOTEL_FD_URL.'assets/admin.js', ['jquery'], UNHOTEL_FD_VERSION, true);
        wp_localize_script('unhotel-fd-admin', 'UNHOTEL_FD', [
            'nonce' => wp_create_nonce('unhotel_fd_settings'),
        ]);
    }
});

/** Front-end/shortcode assets */
add_action('wp_enqueue_scripts', function(){
    // Enqueued only when shortcode is present (see Shortcode::render).
});
