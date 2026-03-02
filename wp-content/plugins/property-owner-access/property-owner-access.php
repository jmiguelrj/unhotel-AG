<?php
/*
Plugin Name: Unhotel Property Owner Access
Text Domain: property-owner-access
Domain Path: /languages
Description: Add the ability for property owners to access their property details, bookings and payments.
Author: Cos Software Solutions
Author URI: http://www.cossoftware.ro
Version: 1.0.0
*/

// Plugin dependencies
require_once(plugin_dir_path(__FILE__) . 'vendor/autoload.php');
require_once(plugin_dir_path(__FILE__) . 'routes.php');

// Plugin classes and configuration
$directories = [
    "config",
    "models",
    "controllers",
    "helpers",
    "services",
];
foreach ($directories as $directory) {
    $dir = glob(plugin_dir_path(__FILE__) . $directory . "/*.php");
    foreach (glob(plugin_dir_path(__FILE__) . $directory . "/*.php") as $filename) {
        require $filename;
    }
}

// Initialize Corcel database connection
$capsule = Corcel\Database::connect($databaseConfig);
$capsule->setAsGlobal();
$capsule->bootEloquent();

// // QUERY LOG BELOW - uncomment to debug
// // turn on the query log
// $capsule->getConnection()->enableQueryLog();
// // at shutdown dump everything
// add_action('shutdown', function() use ($capsule) {
//     $fullQueries = [];
//     foreach ($capsule->getConnection()->getQueryLog() as $entry) {
//         $sql = $entry['query'];
//         foreach ($entry['bindings'] as $binding) {
//             $val = is_numeric($binding) ? $binding : "'{$binding}'";
//             // replace the first “?” each time
//             $sql = preg_replace('/\?/', $val, $sql, 1);
//         }
//         $fullQueries[] = [
//             'sql'  => $sql,
//             'time' => (isset($entry['time']) ? $entry['time'] . 'ms' : 'n/a'),
//         ];
//     }
//     // dump to screen (for quick copy/paste in dev)
//     var_dump($fullQueries);
//     // or log to error_log if you prefer
//     // error_log(print_r($fullQueries, true));
// });

// Create tables
// create_propertyowneraccess_tables();
