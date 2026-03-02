<?php
$databaseConfig = [
    'driver'    => 'mysql',
    'host'      => DB_HOST,
    'database'  => DB_NAME,
    'username'  => DB_USER,
    'password'  => DB_PASSWORD,
    'charset'   => defined( 'DB_CHARSET' )   ? DB_CHARSET   : 'utf8',
    'prefix'    => $wpdb->prefix,
];