<?php
// Plugin activation
function create_propertyowneraccess_tables() {
    global $wpdb;
    $dbPrefix = $wpdb->prefix;

    // Create expense categories table
    $table_name = $dbPrefix.'poa_expense_categories';
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
            id int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            parent_id int(10) UNSIGNED NULL,
            name text NOT NULL,
            PRIMARY KEY  (id)
        ) ENGINE=InnoDB $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    // Create expenses table
    $table_name = $dbPrefix.'poa_expenses';
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
            id int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            room_id int(10) UNSIGNED NOT NULL,
            date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            expenses_category_id int(10) UNSIGNED NOT NULL,
            note text,
            amount decimal(10,2) NOT NULL,
            attachment text,
            owner bool DEFAULT 0,
            PRIMARY KEY (id),
            FOREIGN KEY (room_id) REFERENCES {$dbPrefix}vikbooking_rooms(id),
            FOREIGN KEY (expenses_category_id) REFERENCES {$dbPrefix}poa_expense_categories(id)
        ) ENGINE=InnoDB $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    // Create transfer methods table
    $table_name = $dbPrefix.'poa_transfer_methods';
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
            id int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            name text NOT NULL,
            PRIMARY KEY  (id)
        ) ENGINE=InnoDB $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    // Create transfers table
    $table_name = $dbPrefix.'poa_transfers';
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
            id int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            room_id int(10) UNSIGNED NOT NULL,
            transfer_method_id int(10) UNSIGNED NOT NULL,
            note text,
            amount decimal(10,2) NOT NULL,
            attachment text,
            PRIMARY KEY (id),
            FOREIGN KEY (room_id) REFERENCES {$dbPrefix}vikbooking_rooms(id),
            FOREIGN KEY (transfer_method_id) REFERENCES {$dbPrefix}poa_transfer_methods(id)
        ) ENGINE=InnoDB $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    // Create property owners table
    $table_name = $dbPrefix.'poa_property_owners';
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
            id int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            room_id int(10) UNSIGNED NOT NULL,
            contract text,
            documents text,
            note text,
            PRIMARY KEY (id),
            FOREIGN KEY (room_id) REFERENCES {$dbPrefix}vikbooking_rooms(id)
        ) ENGINE=InnoDB $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    // Create property owners commissions table
    $table_name = $dbPrefix.'poa_property_owners_commissions';
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
            id int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            property_owner_id int(10) UNSIGNED NOT NULL,
            percentage decimal(10,2) NOT NULL,
            date_from datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            date_to datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY (id),
            FOREIGN KEY (property_owner_id) REFERENCES {$dbPrefix}poa_property_owners(id)
        ) ENGINE=InnoDB $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}