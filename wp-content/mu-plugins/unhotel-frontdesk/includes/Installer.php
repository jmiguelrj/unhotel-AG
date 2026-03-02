<?php
namespace UnhotelFD;

if (!defined('ABSPATH')) exit;

class Installer {
    public static function activate() {
        global $wpdb;
        $table = $wpdb->prefix . 'unhotel_frontdesk';
        $ordersrooms = $wpdb->prefix . 'vikbooking_ordersrooms';

        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS {$table} (
            idorderroom INT UNSIGNED NOT NULL PRIMARY KEY,
            arrival_time TIME NULL,
            reg_status VARCHAR(50) NULL,
            receiver VARCHAR(50) NULL,
            obs MEDIUMTEXT NULL,
            updated_by BIGINT UNSIGNED NULL,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_fd_oroom FOREIGN KEY (idorderroom)
              REFERENCES {$ordersrooms}(id) ON DELETE CASCADE
        ) $charset;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        add_option('unhotel_fd_reg_status_options', [
            'Cadastrado e inst','Cadastrado','Não precisa','Pendente','Doc_Solicitado','Check-in Feito'
        ], '', 'no');
        add_option('unhotel_fd_receiver_options', ['SELF','PORTARIA','RECEPÇÃO','MOTOBOY','OUTRO'], '', 'no');
        add_option('unhotel_fd_cache_buster', time(), '', 'no');
    }
}
