<?php
define('WP_USE_THEMES', false);
require_once('/var/www/html/wp-load.php');
global $wpdb;

$res1 = $wpdb->get_results("DESCRIBE {$wpdb->prefix}vikbooking_ordersrooms");

$cct_slug = get_option('unhotel_cct_table', 'reception_control_');
$table_name = $wpdb->prefix . 'jet_cct_' . $cct_slug;
$res2 = $wpdb->get_results("DESCRIBE {$table_name}");

$output = "=== wp_vikbooking_ordersrooms ===\n";
foreach($res1 as $row) {
    $output .= str_pad($row->Field, 25) . " | " . $row->Type . "\n";
}

$output .= "\n=== {$table_name} ===\n";
if ($res2) {
    foreach($res2 as $row) {
        $output .= str_pad($row->Field, 25) . " | " . $row->Type . "\n";
    }
} else {
    $output .= "TABLE DOES NOT EXIST\n";
}

file_put_contents('audit_result.txt', $output);
