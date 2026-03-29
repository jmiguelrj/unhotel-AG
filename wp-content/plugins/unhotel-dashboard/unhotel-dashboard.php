<?php

/**
 * Plugin Name: Unhotel Dashboard
 * Description: React-based Reception Dashboard for Unhotel.
 * Version: 1.2.0
 * Author: João Miguel
 * 
 * Changelog:
 * 1.2.0 - 2025-12-24
 * - Feature: Added "arrival_time" field to CCT table for inline time editing.
 * - Feature: Updated 'Activity Timeline' to show actual WordPress username (Author).
 * - Feature: Added 'Status Audit Log' to track who changed guest status and when.
 * - UX: Moved Search & Sort controls to Sidebar.
 * - UX: Implemented Sticky Headers for long guest lists.
 * - UX: Added Client-side caching for "Smart Data Fetching" by date.
 * - Fix: Dynamic URLs using get_site_url() for environment compatibility.
 * - Fix: Guest Count now reflects only the selected date.
 */

if (! defined('ABSPATH')) {
    exit;
}

// 1. Activation Hook: Schema Check
register_activation_hook(__FILE__, 'unhotel_dashboard_activate');

function unhotel_dashboard_activate()
{
    global $wpdb;

    // Use configured table name or default
    $cct_slug = get_option('unhotel_cct_table', 'reception_control_');
    // Ensure slug doesn't have prefix if user added it, or do we assume user gives just the slug?
    // Let's assume user gives the suffix after `jet_cct_`. Or full table name?
    // To be safe and consistent with previous code which used 'jet_cct_reception_control_',
    // let's assume the option stores the FULL suffix after `jet_cct_`. 
    // Default was 'reception_control_' (based on previous code using $wpdb->prefix . 'jet_cct_reception_control_')
    // Wait, previous code: $table_name = $wpdb->prefix . 'jet_cct_reception_control_';
    // So default suffix is 'reception_control_'

    $full_table_name = $wpdb->prefix . 'jet_cct_' . $cct_slug;

    // Check if table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$full_table_name'") != $full_table_name) {
        // Table missing. Create it.
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $full_table_name (
            _ID bigint(20) NOT NULL AUTO_INCREMENT,
            room_order_id bigint(20) NOT NULL,
            arrival_time varchar(20) DEFAULT '',
            status_reception varchar(50) DEFAULT 'registration',
            status_cleaning varchar(50) DEFAULT 'dirty',
            notes_log longtext,
            
            transportation_type varchar(20) DEFAULT '',
            flight_number varchar(20) DEFAULT '',
            airport varchar(10) DEFAULT '',
            landing_time varchar(10) DEFAULT '',
            next_day tinyint(1) DEFAULT 0,
            arriving_from varchar(100) DEFAULT '',

            cct_status varchar(255) DEFAULT 'publish',
            cct_created datetime DEFAULT CURRENT_TIMESTAMP,
            cct_modified datetime DEFAULT CURRENT_TIMESTAMP,
            cct_author_id bigint(20) DEFAULT 0,
            booking_room_key varchar(50) DEFAULT '',
            PRIMARY KEY  (_ID)
        ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    } else {
        // Table exists, check columns
        $columns = array(
            'room_order_id' => 'bigint(20) NOT NULL',
            'arrival_time'  => "varchar(20) DEFAULT ''",
            'status_reception' => "varchar(50) DEFAULT 'registration'",
            'status_cleaning' => "varchar(50) DEFAULT 'dirty'",
            'notes_log' => 'longtext',

            'transportation_type' => "varchar(20) DEFAULT ''",
            'flight_number' => "varchar(20) DEFAULT ''",
            'airport' => "varchar(10) DEFAULT ''",
            'landing_time' => "varchar(10) DEFAULT ''",
            'next_day' => "tinyint(1) DEFAULT 0",
            'arriving_from' => "varchar(100) DEFAULT ''",
            'booking_room_key' => "varchar(50) DEFAULT ''"
        );

        foreach ($columns as $col => $definition) {
            $exists = $wpdb->get_results("SHOW COLUMNS FROM $full_table_name LIKE '$col'");
            if (empty($exists)) {
                $wpdb->query("ALTER TABLE $full_table_name ADD COLUMN $col $definition");
            }
        }
    }

    // 2. Lookups Table (User Request: wp_jet_cct_reception_lookups)
    // Note: User referred to it as "existing", but we ensure schema matches request.
    $lookups_table = $wpdb->prefix . 'jet_cct_reception_lookups';
    $use_db_delta = false;

    // Check if table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$lookups_table'") != $lookups_table) {
        $charset_collate = $wpdb->get_charset_collate();
        // Schema: type, value, label, priority
        $sql = "CREATE TABLE $lookups_table (
            _ID bigint(20) NOT NULL AUTO_INCREMENT,
            type varchar(50) NOT NULL,
            value varchar(100) NOT NULL,
            label varchar(255) NOT NULL,
            priority int(11) DEFAULT 0,
            cct_status varchar(255) DEFAULT 'publish',
            cct_created datetime DEFAULT CURRENT_TIMESTAMP,
            cct_modified datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (_ID)
        ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        $use_db_delta = true;
    } else {
        // Table exists, check if priority column exists
        $col_exists = $wpdb->get_results("SHOW COLUMNS FROM $lookups_table LIKE 'priority'");
        if (empty($col_exists)) {
            $wpdb->query("ALTER TABLE $lookups_table ADD COLUMN priority int(11) DEFAULT 0");
        }
    }

    // Seed Data (Idempotent)
    // Clear old seeds if needed? No, user might have customized them.
    // However, for this task, we want to ensure these specific seeds exist with correct priorities.
    // We will use ON DUPLICATE UPDATE logic or just check existence.
    // Since we added priority, we should update existing priorities if they match our defaults.

    $seeds = array(
        // Filter Config
        array('type' => 'filter_config', 'value' => '^', 'label' => 'Reception', 'priority' => 1),
        array('type' => 'filter_config', 'value' => '#', 'label' => 'Area',      'priority' => 2),
        array('type' => 'filter_config', 'value' => '@', 'label' => 'Rooms',     'priority' => 3),
        array('type' => 'filter_config', 'value' => '!', 'label' => 'Bathrooms', 'priority' => 4),

        // Registration Status
        array('type' => 'registration_status', 'value' => '#CCCCCC', 'label' => 'Pendente',     'priority' => 0),
        array('type' => 'registration_status', 'value' => '#FFC107', 'label' => 'Contato',      'priority' => 1),
        array('type' => 'registration_status', 'value' => '#E0E7FF', 'label' => 'Doc Recebido', 'priority' => 2),
        array('type' => 'registration_status', 'value' => '#F3E8FF', 'label' => 'Cadastrado',   'priority' => 3),
        array('type' => 'registration_status', 'value' => '#C2E7FF', 'label' => 'Instruído',    'priority' => 4),
        array('type' => 'registration_status', 'value' => '#D1FAE5', 'label' => 'Check-in',     'priority' => 5),

        // Airports List
        array('type' => 'airports_list', 'value' => 'Rio de Janeiro', 'label' => 'GIG', 'priority' => 1),
        array('type' => 'airports_list', 'value' => 'Rio de Janeiro', 'label' => 'SDU', 'priority' => 2),
    );

    foreach ($seeds as $seed) {
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT _ID FROM $lookups_table WHERE type = %s AND label = %s", // Match by label for strictness or value? Value is unique usually.
            // Using Value + Type is safer for filters.
            // But for statuses, value is color? Label is safer for logic if color changes?
            // Let's use Type + Label for status/airports, Type + Value for filters.
            // Wait, standardizing: Type + Value is best for Lookups.
            // But for status, value is mutable CSS potentially.
            // Let's stick to Type + Label for now as unique key? 
            // Actually, for Filters, value is the char code. For Status, Label is the key concept.
            // Let's try matching Type + Label first.
            $seed['type'],
            $seed['label']
        ));

        if (! $exists) {
            $wpdb->insert($lookups_table, $seed);
        } else {
            // Update priority if exists (to enforce new sorting)
            $wpdb->update(
                $lookups_table,
                array('priority' => $seed['priority'], 'value' => $seed['value']),
                array('_ID' => $exists)
            );
        }
    }

    // Migration logic for stable composite key
    $wpdb->query("UPDATE $full_table_name cct JOIN {$wpdb->prefix}vikbooking_ordersrooms orr ON cct.room_order_id = orr.id SET cct.booking_room_key = CONCAT(orr.idorder, '_', orr.idroom) WHERE cct.booking_room_key IS NULL OR cct.booking_room_key = ''");
}

// 2. Menu Page & Settings
add_action('admin_menu', 'unhotel_dashboard_menu');

function unhotel_dashboard_menu()
{
    // Main Menu
    add_menu_page(
        'Reception Dashboard',
        'Reception',
        'manage_options',
        'unhotel-dashboard',
        'unhotel_dashboard_render_page',
        'dashicons-groups',
        6
    );

    // Submenu: Dashboard (Default)
    add_submenu_page(
        'unhotel-dashboard',
        'Dashboard',
        'Dashboard',
        'manage_options',
        'unhotel-dashboard',
        'unhotel_dashboard_render_page'
    );

    // Submenu: Configuration
    add_submenu_page(
        'unhotel-dashboard',
        'Configuration',
        'Configuration',
        'manage_options',
        'unhotel-dashboard-config',
        'unhotel_dashboard_render_config'
    );
}

function unhotel_dashboard_render_page()
{
    echo '<div id="unhotel-dashboard-root"></div>';
}

function unhotel_get_schema()
{
    global $wpdb;

    // 0. Pre-fetch Default Status (Priority 0)
    $lookups_table = $wpdb->prefix . 'jet_cct_reception_lookups';
    $default_status = $wpdb->get_var("SELECT label FROM $lookups_table WHERE type = 'registration_status' AND priority = 0 LIMIT 1");
    if (!$default_status) $default_status = 'Pendente'; // Hard fallback

    $cct_slug = get_option('unhotel_cct_table', 'reception_control_');

    $tables = array(
        'o' => array('name' => $wpdb->prefix . 'vikbooking_orders', 'label' => 'Orders (o)'),
        'c' => array('name' => $wpdb->prefix . 'vikbooking_customers', 'label' => 'Customers (c)'),
        'r' => array('name' => $wpdb->prefix . 'vikbooking_rooms', 'label' => 'Rooms (r)'),
        'cct' => array('name' => $wpdb->prefix . 'jet_cct_' . $cct_slug, 'label' => 'CCT (cct)'),
        'ota' => array('name' => $wpdb->prefix . 'unhotel_jm_ota', 'label' => 'OTA Lookup (ota)'),
        'orr' => array('name' => $wpdb->prefix . 'vikbooking_ordersrooms', 'label' => 'OrdersRooms (orr)'), // Added for completeness
        'co' => array('name' => $wpdb->prefix . 'vikbooking_customers_orders', 'label' => 'CustOrder (co)'),
    );

    $schema = array();

    foreach ($tables as $alias => $info) {
        $table_name = $info['name'];
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
            $cols = $wpdb->get_results("SHOW COLUMNS FROM $table_name");
            $columns = array();
            foreach ($cols as $c) {
                // We store value as "alias.column"
                $columns[] = array(
                    'value' => $alias . '.' . $c->Field,
                    'label' => $c->Field . ' (' . $c->Type . ')'
                );
            }
            $schema[] = array(
                'label' => $info['label'],
                'options' => $columns
            );
        }
    }
    return $schema;
}

function unhotel_dashboard_render_config()
{
    if (isset($_POST['unhotel_save_settings']) && check_admin_referer('unhotel_config_action', 'unhotel_config_nonce')) {
        if (isset($_POST['unhotel_logo_url'])) {
            update_option('unhotel_logo_url', sanitize_text_field($_POST['unhotel_logo_url']));
        }
        if (isset($_POST['unhotel_system_language'])) {
            update_option('unhotel_system_language', sanitize_text_field($_POST['unhotel_system_language']));
        }

        echo '<div class="notice notice-success is-dismissible"><p>Settings saved.</p></div>';
    }

    $logo_url = get_option('unhotel_logo_url', '');
?>
    <div class="wrap">
        <h1>Reception Dashboard Configuration</h1>
        <form method="post">
            <?php wp_nonce_field('unhotel_config_action', 'unhotel_config_nonce'); ?>

            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Logo URL (SVG)</th>
                    <td>
                        <input type="text" name="unhotel_logo_url" value="<?php echo esc_attr($logo_url); ?>" class="regular-text" />
                        <p class="description">Full URL to the brand logo SVG. If provided, replaces "FrontDesk" text.</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">System Language</th>
                    <td>
                        <select name="unhotel_system_language">
                            <option value="en_US" <?php selected(get_option('unhotel_system_language', 'en_US'), 'en_US'); ?>>English (Default)</option>
                            <option value="pt_BR" <?php selected(get_option('unhotel_system_language'), 'pt_BR'); ?>>Português (Brasil)</option>
                        </select>
                        <p class="description">Select the language for the dashboard interface.</p>
                    </td>
                </tr>
            </table>

            <?php submit_button('Save Settings', 'primary', 'unhotel_save_settings'); ?>
        </form>
    </div>
<?php
}

// 3. Shortcode
add_shortcode('unhotel_dashboard', 'unhotel_dashboard_shortcode');

function unhotel_dashboard_shortcode()
{
    // Enqueue scripts here if shortcode is used on frontend
    unhotel_dashboard_enqueue('frontend');
    return '<div id="unhotel-dashboard-root"></div>';
}

// 4. Enqueue Scripts
add_action('admin_enqueue_scripts', 'unhotel_dashboard_admin_enqueue');
add_action('wp_enqueue_scripts', 'unhotel_dashboard_frontend_enqueue'); // Optional: Separate hook if needed, or reuse

function unhotel_dashboard_admin_enqueue($hook)
{
    if ('toplevel_page_unhotel-dashboard' === $hook || 'reception_page_unhotel-dashboard-config' === $hook) {
        unhotel_dashboard_enqueue($hook);
    }
}

// Wrapper to share logic
function unhotel_dashboard_enqueue($hook)
{
    // If not the dashboard page (backend) and not frontend shortcode usage, skip
    // Simplify: Just enqueue if called. 

    $asset_file = include(plugin_dir_path(__FILE__) . 'build/index.asset.php');

    // Ensure wp-element is loaded for wp.element.createRoot / render
    $deps = array_merge($asset_file['dependencies'], array('wp-element', 'wp-components', 'wp-i18n'));

    $js_path = plugin_dir_path(__FILE__) . 'build/index.js';
    $js_ver = file_exists($js_path) ? filemtime($js_path) : '1.0.0';

    wp_enqueue_script(
        'unhotel-dashboard-script',
        plugins_url('build/index.js', __FILE__),
        $deps,
        $js_ver,
        true
    );

    $css_path = plugin_dir_path(__FILE__) . 'build/index.css';
    $css_ver = file_exists($css_path) ? filemtime($css_path) : '1.0.0';

    wp_enqueue_style(
        'unhotel-dashboard-style',
        plugins_url('build/index.css', __FILE__),
        array(),
        $css_ver
    );

    $extra_fields = get_option('unhotel_extra_fields', array());

    // Pass nonce and API url
    $extra_fields = get_option('unhotel_extra_fields', array());
    $status_settings = get_option('unhotel_status_settings', array(
        array('slug' => 'registration', 'label' => 'Registration', 'color' => 'amber'),
        array('slug' => 'cleaning', 'label' => 'Cleaning', 'color' => 'blue'),
        array('slug' => 'checkin', 'label' => 'Check-in', 'color' => 'emerald'),
        array('slug' => 'instructed', 'label' => 'Instructed', 'color' => 'purple'),
        array('slug' => 'contacted', 'label' => 'Contacted', 'color' => 'sky')
    ));

    // Fetch dynamic status colors from reception_lookups
    global $wpdb;
    $lookup_table = $wpdb->prefix . 'jet_cct_reception_lookups';
    $status_rows = $wpdb->get_results("SELECT label, value FROM {$lookup_table} WHERE type = 'registration_status'");
    $dynamic_status_colors = array();
    if ($status_rows) {
        foreach ($status_rows as $row) {
            $dynamic_status_colors[$row->label] = $row->value;
        }
    }

    wp_localize_script('unhotel-dashboard-script', 'unhotelData', array(
        'root' => esc_url_raw(rest_url()),
        'nonce' => wp_create_nonce('wp_rest'),
        'extraFields' => $extra_fields,
        'statusSettings' => $status_settings,
        'statusColors' => $dynamic_status_colors,
        'userName' => wp_get_current_user()->display_name,
        'logoUrl' => get_option('unhotel_logo_url', ''),
        'language' => get_option('unhotel_system_language', 'en_US')
    ));
}

function unhotel_dashboard_frontend_enqueue()
{
    // Only enqueue if shortcode is present? Or if we want to support it globally.
    // Ideally we check has_shortcode, but to be safe and simple, we can rely on the shortcode callback calling it.
    // However, wp_enqueue_script should be called early.
    // Better: Helper function called by shortcode might be too late for header, but fine for footer.
    // Since we output in footer (true arg), calling from shortcode is usually okay.
}


// 5. REST API
add_action('rest_api_init', function () {
    register_rest_route('unhotel/v1', '/guests', array(
        'methods' => 'GET',
        'callback' => 'unhotel_get_guests',
        'permission_callback' => '__return_true',
    ));

    register_rest_route('unhotel/v1', '/update', array(
        'methods' => 'POST',
        'callback' => 'unhotel_update_guest',
        'permission_callback' => '__return_true',
    ));

    register_rest_route('unhotel/v1', '/lookups', array(
        'methods' => 'GET',
        'callback' => 'unhotel_get_lookups',
        'permission_callback' => '__return_true',
    ));

    register_rest_route('unhotel/v1', '/lookups', array(
        'methods' => 'POST',
        'callback' => 'unhotel_add_lookup',
        'permission_callback' => '__return_true',
    ));

    register_rest_route('unhotel/v1', '/suggestions', array(
        'methods' => 'GET',
        'callback' => 'unhotel_get_suggestions',
        'permission_callback' => '__return_true',
    ));

    register_rest_route('unhotel/v1', '/diagnose', array(
        'methods' => 'GET',
        'callback' => 'unhotel_diagnose',
        'permission_callback' => '__return_true', // Should be restrictive in prod, but open for debug per request
    ));

    register_rest_route('unhotel/v1', '/debug_fetch', array(
        'methods' => 'GET',
        'callback' => 'unhotel_debug_fetch',
        'permission_callback' => '__return_true',
    ));

    register_rest_route('unhotel/v1', '/last-minute-count', array(
        'methods' => 'GET',
        'callback' => 'unhotel_get_last_minute_count',
        'permission_callback' => '__return_true',
    ));

    register_rest_route('unhotel/v1', '/last-minute-ack', array(
        'methods' => 'POST',
        'callback' => 'unhotel_last_minute_ack',
        'permission_callback' => '__return_true',
    ));

    register_rest_route('unhotel/v1', '/filters', array(
        'methods' => 'GET',
        'callback' => 'unhotel_get_filters',
        'permission_callback' => '__return_true',
    ));
});

function unhotel_debug_fetch($request)
{
    global $wpdb;
    $id = $request->get_param('id');

    if (empty($id)) {
        return new WP_Error('missing_id', 'Please provide an ID (vikbooking_ordersrooms.id)', array('status' => 400));
    }

    $debug_log = array();
    $t_orders_rooms = $wpdb->prefix . 'vikbooking_ordersrooms';
    $t_orders = $wpdb->prefix . 'vikbooking_ordersrooms'; // Wait, typo in standard var name? No, DB table name.
    $t_orders_main = $wpdb->prefix . 'vikbooking_orders';
    $t_cust_ord = $wpdb->prefix . 'vikbooking_customers_orders';
    $t_customers = $wpdb->prefix . 'vikbooking_customers';

    $cct_slug = get_option('unhotel_cct_table', 'reception_control_');
    $t_cct = $wpdb->prefix . 'jet_cct_' . $cct_slug;

    // Step 1: Orders Rooms
    $row_orr = $wpdb->get_row($wpdb->prepare("SELECT * FROM $t_orders_rooms WHERE id = %d", $id));
    $debug_log['step1_orders_rooms'] = $row_orr ? $row_orr : 'Not Found';

    if (!$row_orr) {
        return rest_ensure_response($debug_log);
    }

    // Step 2: Order
    $order_id = $row_orr->idorder;
    $row_order = $wpdb->get_row($wpdb->prepare("SELECT * FROM $t_orders_main WHERE id = %d", $order_id));
    $debug_log['step2_order'] = $row_order ? $row_order : 'Not Found';

    // Step 3: Customer
    $row_cust_pivot = $wpdb->get_row($wpdb->prepare("SELECT * FROM $t_cust_ord WHERE idorder = %d", $order_id));
    if ($row_cust_pivot) {
        $cust_id = $row_cust_pivot->idcustomer;
        $row_cust = $wpdb->get_row($wpdb->prepare("SELECT * FROM $t_customers WHERE id = %d", $cust_id));
        $debug_log['step3_customer'] = $row_cust ? $row_cust : 'Not Found (Pivot exists)';
    } else {
        $debug_log['step3_customer'] = 'Pivot (customers_orders) Not Found';
    }

    // Step 4: CCT
    $row_cct = $wpdb->get_row($wpdb->prepare("SELECT * FROM $t_cct WHERE room_order_id = %d", $id));
    $debug_log['step4_cct'] = $row_cct ? $row_cct : 'Not Found';

    // Step 5: Simulation (Why is it missing?)
    // We replicate the exact conditions of `unhotel_get_guests` for this SINGLE row.
    // Date Range logic is usually the culprit.

    $today_ts = current_time('timestamp');
    // Using the same widened window as implemented earlier
    $start = $today_ts - (30 * DAY_IN_SECONDS);
    $end = $today_ts + (30 * DAY_IN_SECONDS);

    if ($row_order) {
        $checkin = $row_order->checkin;
        $debug_log['step5_logic_check'] = array(
            'order_checkin_ts' => $checkin,
            'order_checkin_date' => date('Y-m-d', $checkin),
            'window_start' => date('Y-m-d', $start) . " ($start)",
            'window_end' => date('Y-m-d', $end) . " ($end)",
            'in_window' => ($checkin >= $start && $checkin <= $end) ? 'YES' : 'NO',
            'status' => $row_order->status,
            'status_pass' => ($row_order->status == 'confirmed') ? 'YES' : 'NO'
        );
    }

    return rest_ensure_response($debug_log);
}

function unhotel_diagnose()
{
    global $wpdb;
    $report = array(
        'stage1_db_connection' => 'Pending',
        'stage2_tables' => array(),
        'stage3_relations' => array(),
        'stage4_sample_data' => 'Pending'
    );

    // Stage 1: Connection
    if ($wpdb->check_connection()) {
        $report['stage1_db_connection'] = 'Pass';
    } else {
        $report['stage1_db_connection'] = 'Fail: ' . $wpdb->last_error;
        return rest_ensure_response($report);
    }

    // Stage 2: Tables
    $cct_slug = get_option('unhotel_cct_table', 'reception_control_');
    $required_tables = array(
        'orders' => $wpdb->prefix . 'vikbooking_orders',
        'orders_rooms' => $wpdb->prefix . 'vikbooking_ordersrooms',
        'cust_orders' => $wpdb->prefix . 'vikbooking_customers_orders',
        'customers' => $wpdb->prefix . 'vikbooking_customers',
        'rooms' => $wpdb->prefix . 'vikbooking_rooms',
        'cct' => $wpdb->prefix . 'jet_cct_' . $cct_slug,
        'filters' => $wpdb->prefix . 'jet_cct_reception_lookups'
    );

    $tables_ok = true;
    foreach ($required_tables as $key => $table) {
        $exists = $wpdb->get_var("SHOW TABLES LIKE '$table'") == $table;
        $report['stage2_tables'][$key] = $exists ? 'Pass' : 'Fail';
        if (!$exists) $tables_ok = false;
    }

    if (!$tables_ok) return rest_ensure_response($report);

    // Stage 3: Relations
    $t = $required_tables;

    // Count Confirmed
    $count_confirmed = $wpdb->get_var("SELECT COUNT(*) FROM {$t['orders']} WHERE status='confirmed'");
    $report['stage3_relations']['confirmed_orders'] = $count_confirmed;

    // Count Joined with Rooms
    $count_rooms_join = $wpdb->get_var("
        SELECT COUNT(*) FROM {$t['orders']} o 
        JOIN {$t['orders_rooms']} orr ON o.id = orr.idorder 
        WHERE o.status='confirmed'
    ");
    $report['stage3_relations']['orders_with_rooms'] = $count_rooms_join;

    // Count Joined with Customer
    // Note: using idcustomer as verified earlier
    $count_cust_join = $wpdb->get_var("
        SELECT COUNT(*) FROM {$t['orders']} o 
        JOIN {$t['cust_orders']} co ON o.id = co.idorder
        JOIN {$t['customers']} c ON co.idcustomer = c.id
        WHERE o.status='confirmed'
    ");
    $report['stage3_relations']['orders_with_customers'] = $count_cust_join;

    // Count Joined with CCT
    $count_cct_join = $wpdb->get_var("
        SELECT COUNT(*) FROM {$t['orders_rooms']} orr 
        JOIN {$t['cct']} cct ON orr.id = cct.room_order_id
    ");
    $report['stage3_relations']['rooms_with_cct_data'] = $count_cct_join;


    // Stage 4: Sample Data
    // Fetch one full guest object using the main logic
    $sample = unhotel_get_guests(new WP_REST_Request());
    // unhotel_get_guests returns rest_ensure_response($data).
    // We need just the data.

    // Let's manually fetch one row to test serialization
    $today_ts = current_time('timestamp');
    $start = $today_ts - (30 * DAY_IN_SECONDS);
    $end = $today_ts + (30 * DAY_IN_SECONDS);

    $query = "SELECT id, checkin FROM {$t['orders']} WHERE status='confirmed' LIMIT 1";
    $row = $wpdb->get_row($query);

    if ($row) {
        $encoded = json_encode($row);
        if ($encoded === false) {
            $report['stage4_sample_data'] = 'Fail: JSON Error ' . json_last_error_msg();
        } else {
            $report['stage4_sample_data'] = 'Pass: ' . $encoded;
        }
    } else {
        $report['stage4_sample_data'] = 'No confirmed orders found to sample.';
    }

    return rest_ensure_response($report);
}

function unhotel_get_guests($request)
{
    global $wpdb;

    // 1. Date Range or Search
    $search_term = $request->get_param('search');
    $start_param = $request->get_param('start_date'); // Format: YYYY-MM-DD
    $end_param   = $request->get_param('end_date');

    // WP Timezone Aware Date Parsing
    $tz = wp_timezone();

    if ($start_param && $end_param) {
        $dt_start = new DateTime($start_param . ' 00:00:00', $tz);
        $start_ts = $dt_start->getTimestamp();

        $dt_end = new DateTime($end_param . ' 23:59:59', $tz);
        $end_ts = $dt_end->getTimestamp();
    } else {
        // Fallback: +/- 30 days if not specified
        $dt_start = new DateTime('today', $tz);
        $dt_start->modify('-30 days');
        $start_ts = $dt_start->getTimestamp();

        $dt_end = new DateTime('today 23:59:59', $tz);
        $dt_end->modify('+30 days');
        $end_ts = $dt_end->getTimestamp();
    }

    // 2. Table Names
    $t_orders = $wpdb->prefix . 'vikbooking_orders';
    $t_orders_rooms = $wpdb->prefix . 'vikbooking_ordersrooms';
    $t_cust_ord = $wpdb->prefix . 'vikbooking_customers_orders';
    $t_cust = $wpdb->prefix . 'vikbooking_customers';
    $t_rooms = $wpdb->prefix . 'vikbooking_rooms';
    $t_ota_lookup = $wpdb->prefix . 'unhotel_jm_ota';
    $t_users = $wpdb->prefix . 'users';

    // Dynamic CCT Table
    $cct_slug = get_option('unhotel_cct_table', 'reception_control_');
    $t_cct = $wpdb->prefix . 'jet_cct_' . $cct_slug;
    $lookups_table = $wpdb->prefix . 'jet_cct_reception_lookups';

    // Fetch Default Registration Status (Priority 0)
    $default_status = $wpdb->get_var("SELECT label FROM $lookups_table WHERE type = 'registration_status' AND priority = 0 LIMIT 1");
    if (!$default_status) $default_status = 'Pendente'; // Fallback

    // 3. Dynamic Extra Selects
    $extra_fields = get_option('unhotel_extra_fields', array());
    $extra_selects = '';
    foreach ($extra_fields as $field) {
        $col = esc_sql($field['column']);
        // If column has a dot, assume it's alias.column. Otherwise prepend cct.
        if (strpos($col, '.') !== false) {
            $extra_selects .= ", $col";
        } else {
            $extra_selects .= ", cct.$col";
        }
    }

    // Pre-calculate site url for SQL
    $site_url = get_site_url();

    // 4. Filter Logic (Search vs Date Range)
    if (!empty($search_term)) {
        // Search
        $where_clause = $wpdb->prepare("AND (o.id = %d OR o.idorderota = %s)", intval($search_term), $search_term);
    } else {
        $where_clause = $wpdb->prepare("AND (o.checkin BETWEEN %d AND %d)", $start_ts, $end_ts);
    }

    // 5. SQL Construction
    $query = "
    SELECT 
        cct._ID as cct_id,
        orr.id as ref_id,
        orr.idroom,
        o2.id as booking_id,
        o2.idorderota as ext_id, -- N11
        
        -- N3 (Name): First word of Name
        SUBSTRING_INDEX(CONCAT(c.first_name, ' ', c.last_name), ' ', 1) as name,
        CONCAT(c.first_name, ' ', c.last_name) as full_name,
        
        -- A5 (Profile Pic)
        c.pic as profile_pic,

        -- N2 (Room): First 5 chars
        LEFT(r.name, 5) as room,

        -- N1 (Arrival) / CCT Mutable Data
        cct.arrival_time as time,
        cct.status_reception as status, -- N8
        cct.status_cleaning as cleaning,
        cct.notes_log, -- N15 (Raw JSON string)

        -- N4 (Pax)
        (orr.adults + orr.children) as pax,

        -- N5 (Nights)
        DATEDIFF(FROM_UNIXTIME(o2.checkout), FROM_UNIXTIME(o2.checkin)) as nights,

        -- N6 (Source)
        CASE 
            WHEN o2.OTA LIKE '%booking%' THEN 'booking'
            WHEN o2.OTA LIKE '%airbnb%'  THEN 'airbnb'
            WHEN o2.OTA LIKE '%expedia%' THEN 'expedia'
            ELSE 'Unhotel'
        END as source,

        -- N14 (Pending $)
        CASE 
            WHEN (o2.total - o2.totpaid) <= 0.01 THEN ''
            WHEN o2.OTA LIKE '%airbnb%' THEN ''
            WHEN o2.OTA LIKE '%expedia%' THEN ''
            ELSE CAST((o2.total - o2.totpaid) AS CHAR)
        END as amount_pending,
        (o2.total - o2.totpaid) as amount_raw, -- Keep for logic if needed

        -- N9 (Email)
        o2.custmail as email,

        -- N10 (Phone)
        o2.phone as phone,
        
        -- N12 (In) / N13 (Out)
        o2.checkin as checkin_ts,
        o2.checkout as checkout_ts,
        DATE_FORMAT(FROM_UNIXTIME(o2.checkin), '%Y-%m-%d') as checkin_iso,

        -- N16 (Author)
        cct.cct_author_id as author_id,
        u.display_name as author_name, -- A6
        
        -- N17 (Timestamp)
        cct.cct_modified as modified_date,

        -- Logistics
        cct.transportation_type,
        cct.flight_number,
        cct.airport,
        cct.landing_time,
        cct.next_day,
        cct.arriving_from,

        -- N18 (Link) components (sid, ts, lang)
        o2.sid,
        o2.ts, o2.ts as booking_ts,
        o2.lang,
        c.country, -- Need this for flag

        -- N19 (Flag)
        CONCAT('$site_url', '/wp-content/plugins/vikbooking/admin/resources/countries/', c.country, '.png') as flag,
        
        -- N?? (Has Documents?)
        CASE WHEN co.pax_data LIKE '%\"documents\":%' THEN 1 ELSE 0 END as has_documents,

        -- OTA Logo (Existing)
        ota.ota_logo_file as otaIcon,

        -- WhatsApp Link (Existing Helper)
        CONCAT('https://wa.me/', REPLACE(o2.phone, '+', '')) as whatsapp,

        -- Contact Label
        CONCAT(
            SUBSTRING_INDEX(CONCAT(c.first_name, ' ', c.last_name), ' ', 1), ' ',
            LEFT(r.name, 5), ' ',
            DATE_FORMAT(FROM_UNIXTIME(o2.checkin), '%d/%m'),
            ' - ',
            DATE_FORMAT(FROM_UNIXTIME(o2.checkout), '%d/%m/%y'),
            ' ', o2.OTA, ' ', o2.id
        ) as contactLabel,

        -- UI Enhancement: Room Image
        r.img as room_image,
        r.idcat as room_cats,
        
        -- ISO Date for Frontend Filtering/Navigation
        FROM_UNIXTIME(o2.checkin, '%Y-%m-%d') as checkin_iso

        $extra_selects

    FROM (
        SELECT
            o.*,
            CASE 
                WHEN COALESCE(o.channel, '') = ''  THEN 'Unhotel'
                WHEN o.channel LIKE '%booking%'    THEN 'Booking'
                WHEN o.channel LIKE '%airbnb%'     THEN 'Airbnb'
                WHEN o.channel LIKE '%expedia%'    THEN 'Expedia'
                ELSE o.channel
            END AS OTA
        FROM $t_orders o
        WHERE o.status = 'confirmed'
        $where_clause
    ) AS o2
    LEFT JOIN $t_orders_rooms orr ON o2.id = orr.idorder
    INNER JOIN $t_cust_ord co ON o2.id = co.idorder
    INNER JOIN $t_cust c ON co.idcustomer = c.id
    LEFT JOIN $t_rooms r ON orr.idroom = r.id
    LEFT JOIN $t_ota_lookup ota ON ota.OTA_name = o2.OTA
    LEFT JOIN $t_cct cct ON cct.booking_room_key = CONCAT(o2.id, '_', orr.idroom)
    LEFT JOIN $t_users u ON cct.cct_author_id = u.ID
    ORDER BY o2.checkin ASC
    ";

    $results = $wpdb->get_results($query);

    $data = array();
    foreach ($results as $row) {
        $notes_array = $row->notes_log ? json_decode($row->notes_log, true) : array();
        if (is_string($notes_array)) { $notes_array = json_decode($notes_array, true); }
        if (! is_array($notes_array)) {
            $notes_array = array();
        }

        // Collect extra data
        $extras = array();
        foreach ($extra_fields as $field) {
            $col = $field['column'];
            if (property_exists($row, $col)) {
                $extras[$field['label']] = $row->$col;
            } else {
                $extras[$field['label']] = null;
            }
        }

        // N18 Link Gen
        // URL: https://unhotel.com.br/[lang_slug]/?sid=[id]&ts=[ts]
        // Logic: if idorderota exists use it (Wait, request said: "if idorderota exists use it, else use sid" as the ID param?)
        // Actually: `/?sid=[id]` is usually the query var for the booking.
        // Let's follow request: "Logic: if idorderota exists use it, else use sid"
        $sid_val = !empty($row->ext_id) ? $row->ext_id : $row->sid;
        $lang_slug = ($row->lang == 'pt-BR') ? 'reserva-pt' : 'en/booking-details-en';
        $site_url = get_site_url(); // A8
        $resLink = "{$site_url}/{$lang_slug}/?sid={$sid_val}&ts={$row->ts}";

        // A5 Picture Logic
        $picUrl = null;
        if (!empty($row->profile_pic)) {
            if (filter_var($row->profile_pic, FILTER_VALIDATE_URL)) {
                $picUrl = $row->profile_pic;
            } else {
                $picUrl = $site_url . '/wp-content/plugins/vikbooking/site/resources/uploads/' . $row->profile_pic;
            }
        }

        // Room Image Dual-Check Logic
        $roomImageUrl = null;
        if (!empty($row->room_image)) {
            $upload_dir = wp_upload_dir();
            $base_path = $upload_dir['basedir'];
            $base_url = $upload_dir['baseurl'];

            // 1. Check Standard Uploads (Production/MU Plugin)
            if (file_exists(wp_normalize_path($base_path . '/' . $row->room_image))) {
                $roomImageUrl = $base_url . '/' . $row->room_image;
            }
            // 2. Check VikBooking Plugin Resource Dir (Localhost Default)
            elseif (file_exists(wp_normalize_path(WP_PLUGIN_DIR . '/vikbooking/site/resources/uploads/' . $row->room_image))) {
                $roomImageUrl = $site_url . '/wp-content/plugins/vikbooking/site/resources/uploads/' . $row->room_image;
            }
            // 3. Fallback Construction (Just in case)
            else {
                $roomImageUrl = $site_url . '/wp-content/plugins/vikbooking/site/resources/uploads/' . $row->room_image;
            }
        }

        $has_starred_note = false;
        if (is_array($notes_array)) {
            foreach ($notes_array as $n) {
                if (!empty($n['starred']) || !empty($n['is_starred']) || !empty($n['isStarred']) || (isset($n['star']) && $n['star'] == 1)) {
                    $has_starred_note = true;
                    break;
                }
            }
        }

        // Output JSON structure
        $data[] = array(
            'id' => $row->cct_id ? $row->cct_id : null,
            'has_starred_note' => $has_starred_note,
            'refId' => $row->booking_id . '_' . $row->idroom,   // N7
            'bookingId' => $row->booking_id, // N7
            'extId' => $row->ext_id, // N11
            'otaRef' => $row->ext_id, // Explicit Alias for Frontend Clarity

            'name' => $row->name, // N3
            'fullName' => $row->full_name,
            'pic' => $picUrl,
            'room' => $row->room, // N2

            'roomImageUrl' => $roomImageUrl, // New URL (Dual-Check)
            'roomImageRaw' => $row->room_image, // Keep raw if needed
            'categoryIds' => $row->room_cats,   // Raw string "1;3;8;"

            'time' => $row->time, // N1
            'status' => $row->status ? $row->status : $default_status, // N8 (Use fetched default)
            'cleaning' => $row->cleaning ? $row->cleaning : 'dirty',

            'notes' => $notes_array, // N15 (Timeline)
            'pax' => intval($row->pax), // N4
            'nights' => intval($row->nights), // N5
            'source' => $row->source, // N6

            'amountPending' => $row->amount_pending, // N14 (String)
            'amountRaw' => floatval($row->amount_raw),

            'email' => $row->email, // N9
            'phone' => $row->phone, // N10

            'checkin' => $row->checkin_ts, // N12
            'checkout' => $row->checkout_ts, // N13

            'authorId' => $row->author_id, // N16
            'authorName' => $row->author_name, // A6
            'modifiedDate' => $row->modified_date, // N17
            'booking_ts' => $row->booking_ts, // Exposed for Last Minute
            'resLink' => $resLink, // N18

            'flag' => $row->flag, // N19

            'hasDocuments' => (bool)$row->has_documents,

            'otaIcon' => $row->otaIcon,
            'whatsapp' => $row->whatsapp,
            'contactLabel' => $row->contactLabel,
            'checkinIso' => $row->checkin_iso,
            'checkinIso' => $row->checkin_iso,
            'extras' => $extras,

            // Logistics Module
            'logistics' => array(
                'transportType' => $row->transportation_type,
                'flightNumber' => $row->flight_number,
                'airport' => $row->airport,
                'landingTime' => $row->landing_time,
                'nextDay' => (bool)$row->next_day,
                'arrivingFrom' => $row->arriving_from
            )
        );
    }

    return rest_ensure_response($data);
}

function unhotel_update_guest($request)
{
    global $wpdb;
    $params = $request->get_json_params();

    // Optional updates
    $status = isset($params['status']) ? sanitize_text_field($params['status']) : null;
    $notes = isset($params['notes']) ? $params['notes'] : null;
    $arrival_time = isset($params['time']) ? sanitize_text_field($params['time']) : null;
    $ref_id = isset($params['refId']) ? sanitize_text_field($params['refId']) : '';

    if (! $ref_id) {
        return new WP_Error('invalid_data', 'Missing Ref ID', array('status' => 400));
    }

    list($res_id, $room_id) = explode('_', $ref_id);

    $cct_slug = get_option('unhotel_cct_table', 'reception_control_');
    $table_name = $wpdb->prefix . 'jet_cct_' . $cct_slug;

    // Check existing
    $existing = $wpdb->get_row($wpdb->prepare("SELECT _ID, status_reception, notes_log FROM $table_name WHERE booking_room_key = %s", $ref_id));

    // Current user info
    $current_user_id = get_current_user_id();
    $current_user = wp_get_current_user();
    $author_name = $current_user->exists() ? $current_user->display_name : 'System';

    // Prepare update data
    $data_to_update = array();
    $formats = array();

    // 1. Status with Audit Log
    if ($status !== null) {
        $old_status = $existing ? $existing->status_reception : 'registration';

        if ($old_status !== $status) {
            $data_to_update['status_reception'] = $status;
            $formats[] = '%s';

            // Auto-log
            $log_entry = array(
                'text' => "Changed status from $old_status to $status",
                'time' => current_time('d/m/Y H:i'),
                'timestamp' => time(),
                'type' => 'system',
                'id' => time() . '_sys',
                'author' => $author_name // Save author name in note for display without join if needed
            );

            // Fetch existing notes if not passed in request
            if ($notes === null) {
                $existing_notes = ($existing && $existing->notes_log) ? json_decode($existing->notes_log, true) : array();
                if (is_string($existing_notes)) { $existing_notes = json_decode($existing_notes, true); }
                if (!is_array($existing_notes)) $existing_notes = array();
                $existing_notes[] = $log_entry;
                $notes = $existing_notes; // Set to be updated below
            } else {
                // If notes were passed, append to them? 
                // Usually frontend sends FULL new array. 
                // If we are appending system note, we should append to the incoming array.
                $notes[] = $log_entry;
            }
        }
    }

    // 2. Notes
    // Ensure author name is added to NEW notes if not present?
    // Frontend should ideally send author, but backend can enforce it.
    if ($notes !== null) {
        // Encode
        $data_to_update['notes_log'] = json_encode($notes);
        $formats[] = '%s';
    }

    // 3. Arrival Time (Legacy)
    if ($arrival_time !== null) {
        $data_to_update['arrival_time'] = $arrival_time;
        $formats[] = '%s';
    }

    // 4. Logistics Module
    $logistics = isset($params['logistics']) ? $params['logistics'] : null;
    if ($logistics && is_array($logistics)) {
        $fields = array(
            'transportType' => 'transportation_type',
            'flightNumber' => 'flight_number',
            'airport' => 'airport',
            'landingTime' => 'landing_time',
            'nextDay' => 'next_day',
            'arrivingFrom' => 'arriving_from'
        );

        foreach ($fields as $key => $col) {
            if (isset($logistics[$key])) {
                $val = $logistics[$key];
                if ($col === 'next_day') {
                    $val = $val ? 1 : 0; // Boolean to tinyint
                    $formats[] = '%d';
                } else {
                    $val = sanitize_text_field($val);
                    $formats[] = '%s';
                }
                $data_to_update[$col] = $val;
            }
        }
    }

    // Always update modified
    $data_to_update['cct_modified'] = current_time('mysql');
    $formats[] = '%s';

    // Author of LAST change
    $data_to_update['cct_author_id'] = $current_user_id;
    $formats[] = '%d';

    // Set stable Reservation ID directly from composite key
    $data_to_update['reservation_id'] = intval($res_id);
    $formats[] = '%d';

    if (empty($data_to_update)) {
        return rest_ensure_response(array('success' => true, 'no_changes' => true));
    }

    if ($existing) {
        $wpdb->update(
            $table_name,
            $data_to_update,
            array('booking_room_key' => $ref_id),
            $formats,
            array('%s')
        );
        $record_id = $existing->_ID;
    } else {
        // Insert defaults if missing
        if (!isset($data_to_update['status_reception'])) {
            $data_to_update['status_reception'] = $status ? $status : 'registration';
            $formats[] = '%s';
        }
        if (!isset($data_to_update['notes_log'])) {
            $data_to_update['notes_log'] = isset($data_to_update['notes_log']) ? $data_to_update['notes_log'] : '[]';
            // formats handled above if set, else need to set.
            // Simplified: Just merge defaults.
        }

        $defaults = array(
            'booking_room_key' => $ref_id,
            'status_cleaning' => 'dirty',
            'cct_status' => 'publish',
            'cct_created' => current_time('mysql'),
        );
        $default_formats = array('%s', '%s', '%s', '%s');

        $final_data = array_merge($defaults, $data_to_update);
        $final_formats = array_merge($default_formats, $formats);

        $wpdb->insert($table_name, $final_data, $final_formats);
        $record_id = $wpdb->insert_id;
    }

    return rest_ensure_response(array('id' => $record_id));
}

function unhotel_last_minute_ack($request)
{
    update_option('unhotel_last_minute_ack_time', time());
    return rest_ensure_response(array('success' => true));
}

function unhotel_get_last_minute_count($request)
{
    global $wpdb;
    $t_orders = $wpdb->prefix . 'vikbooking_orders';
    $t_cust_ord = $wpdb->prefix . 'vikbooking_customers_orders';
    $t_cust = $wpdb->prefix . 'vikbooking_customers';

    // Logic: Count Same Day Bookings (Booked Today AND Arriving Today)
    $today_str = current_time('Y-m-d');
    $start_ts = strtotime($today_str . ' 00:00:00');
    $end_ts   = strtotime($today_str . ' 23:59:59');

    // Count orders where:
    // 1. Booking creation (ts) is Today
    // 2. Check-in (checkin) is Today
    // 3. Status is confirmed (optional, but good practice)
    $query = $wpdb->prepare("
        SELECT COUNT(DISTINCT o.id) as count, MAX(o.ts) as latest_ts
        FROM $t_orders o
        INNER JOIN $t_cust_ord co ON o.id = co.idorder
        INNER JOIN $t_cust c ON co.idcustomer = c.id
        WHERE o.status = 'confirmed'
        AND o.ts >= %d
        AND o.checkin >= %d 
        AND o.checkin <= %d
    ", $start_ts, $start_ts, $end_ts);

    $row = $wpdb->get_row($query);

    $count = $row ? (int) $row->count : 0;
    $latest_ts = $row ? (int) $row->latest_ts : 0;
    
    // Check against global ack time
    $ack_time = (int) get_option('unhotel_last_minute_ack_time', 0);
    $alert = ($count > 0 && $latest_ts > $ack_time);

    return rest_ensure_response(array('count' => $count, 'alert' => $alert));
}

function unhotel_get_lookups($request)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'jet_cct_reception_lookups';

    $type = $request->get_param('type');

    if ($type) {
        $query = $wpdb->prepare("SELECT * FROM $table_name WHERE type = %s ORDER BY priority ASC, label ASC", $type);
    } else {
        $query = "SELECT * FROM $table_name ORDER BY type ASC, priority ASC";
    }

    $results = $wpdb->get_results($query);
    return rest_ensure_response($results);
}

function unhotel_add_lookup($request)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'jet_cct_reception_lookups';
    $params = $request->get_json_params();

    $type = isset($params['type']) ? sanitize_text_field($params['type']) : '';
    $value = isset($params['value']) ? sanitize_text_field($params['value']) : '';
    $label = isset($params['label']) ? sanitize_text_field($params['label']) : $value;

    if (!$type || !$value) {
        return new WP_Error('missing_params', 'Type and Value are required', array('status' => 400));
    }

    // Check duplicate
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT _ID FROM $table_name WHERE type = %s AND value = %s",
        $type,
        $value
    ));

    if ($exists) {
        return rest_ensure_response(array('success' => true, 'id' => $exists, 'message' => 'Already exists'));
    }

    $wpdb->insert(
        $table_name,
        array(
            'type' => $type,
            'value' => $value,
            'label' => $label
        ),
        array('%s', '%s', '%s')
    );

    return rest_ensure_response(array('success' => true, 'id' => $wpdb->insert_id));
}

function unhotel_get_suggestions($request)
{
    global $wpdb;
    $field = $request->get_param('field');

    // Allowed fields to query
    $allowed = array('flight_number', 'arriving_from');
    if (!in_array($field, $allowed)) {
        return new WP_Error('invalid_field', 'Field not allowed', array('status' => 400));
    }

    // Check Cache
    $cache_key = 'unhotel_suggestions_' . $field;
    $cached = get_transient($cache_key);
    if ($cached !== false) {
        return rest_ensure_response($cached);
    }

    $cct_slug = get_option('unhotel_cct_table', 'reception_control_');
    $table_name = $wpdb->prefix . 'jet_cct_' . $cct_slug;

    // Fetch distinct values, non-empty, order by recent ID (proxy for recency)
    // Actually simple distinct is fine, maybe limit 200
    $query = $wpdb->prepare("
        SELECT DISTINCT $field 
        FROM $table_name 
        WHERE $field != '' AND $field IS NOT NULL
        ORDER BY _ID DESC 
        LIMIT 200
    ");

    $results = $wpdb->get_col($query);

    // Cache for 1 hour
    set_transient($cache_key, $results, 3600);

    return rest_ensure_response($results);
}

function unhotel_get_filters($request)
{
    global $wpdb;

    // 1. Fetch Configs
    $desc_table = $wpdb->prefix . 'jet_cct_reception_lookups';
    // Fallback if table doesn't exist yet (before re-activation)?
    // Assume it exists or query safe.
    $group_configs = $wpdb->get_results("SELECT value, label FROM $desc_table WHERE type = 'filter_config' ORDER BY priority ASC");

    if (empty($group_configs)) {
        return rest_ensure_response(array());
    }

    // 2. Fetch All Categories
    // Table: wp_vikbooking_categories
    $cat_table = $wpdb->prefix . 'vikbooking_categories';
    $categories = $wpdb->get_results("SELECT id, name FROM $cat_table");

    // 3. Process
    $filter_tree = array();

    // Init Groups
    foreach ($group_configs as $g) {
        $filter_tree[$g->value] = array(
            'id' => $g->value, // Use prefix as ID for now, or generated
            'label' => $g->label,
            'prefix' => $g->value,
            'items' => array()
        );
    }

    // Assign Categories
    foreach ($categories as $cat) {
        $name = $cat->name; // Column is 'name'
        if (empty($name)) continue;

        $first_char = mb_substr($name, 0, 1);

        if (isset($filter_tree[$first_char])) {
            $clean_name = trim(mb_substr($name, 1));
            $filter_tree[$first_char]['items'][] = array(
                'id' => $cat->id,
                'name' => $clean_name,
                'original' => $name
            );
        }
    }

    // Re-index to array
    $response = array_values($filter_tree);

    // DEBUG: Append debug info if requested or always for now (User issue)
    // We can't easily append to array_values if we want clean JSON array of objects.
    // Let's inspect via error_log or temporary hidden item?
    // Better: Add a dummy item with debug info if needed, OR just log to a specific endpoint.
    // Let's try adding a "debug_stats" property to the FIRST group if it exists, or just log to response headers?
    // Actually, I'll log to error_log to be safe? No, I can't read error_log easily.
    // I will return a special structure if ?debug=1

    return rest_ensure_response($response);
}
