<?php
/*
Plugin Name: Unhotel Contacts Export (MU)
Description: Exports one-row-per-phone Google Contacts CSV using VikBooking data with WhatsApp-friendly label.
Author: Unhotel
*/

if (!defined('ABSPATH')) exit;

add_action('init', function() {
    // Manual trigger: https://yourdomain/?unhotel_export_contacts=1
    if (current_user_can('manage_options') && isset($_GET['unhotel_export_contacts'])) {
        $ok = unhotel_export_contacts_csv();
        $upload = wp_upload_dir();
        $url = trailingslashit($upload['baseurl']) . 'unhotel-contacts/contacts.csv';
        header('Content-Type: text/plain; charset=utf-8');
        echo $ok ? "Contacts CSV exported.\nURL: {$url}\n" : "Export failed.\n";
        exit;
    }

    // Daily schedule ~03:10 BRT
    if (!wp_next_scheduled('unhotel_contacts_export_daily')) {
        $tz   = new DateTimeZone('America/Sao_Paulo');
        $now  = new DateTime('now', $tz);
        $run  = new DateTime('today 03:10', $tz);
        if ($now >= $run) $run->modify('+1 day');
        $run->setTimezone(new DateTimeZone('UTC'));
        wp_schedule_event($run->getTimestamp(), 'daily', 'unhotel_contacts_export_daily');
    }
});
add_action('unhotel_contacts_export_daily', 'unhotel_export_contacts_csv');

function unhotel_export_contacts_csv() {
    global $wpdb;
    $p = $wpdb->prefix;

    $upload = wp_upload_dir();
    $dir    = trailingslashit($upload['basedir']) . 'unhotel-contacts';
    if (!wp_mkdir_p($dir)) return false;
    $file   = trailingslashit($dir) . 'contacts.csv';

    // SQL:
    $sql = "
    SELECT
      picked.contact_label                   AS `Name`,
      picked.first_name                      AS `Given Name`,
      ''                                     AS `Family Name`,
      'Mobile'                               AS `Phone 1 - Type`,
      picked.phone_e164                      AS `Phone 1 - Value`,
      COALESCE(hist.all_reservations, '')    AS `Notes`
    FROM (
      SELECT *
      FROM (
        SELECT
          REGEXP_REPLACE(o.phone, '[^0-9]+', '') AS phone_digits,
          CASE
            WHEN REGEXP_REPLACE(o.phone,'[^0-9]+','') REGEXP '^55'
              THEN CONCAT('+', REGEXP_REPLACE(o.phone,'[^0-9]+',''))
            WHEN LENGTH(REGEXP_REPLACE(o.phone,'[^0-9]+','')) IN (10,11)
              THEN REPLACE(o.phone, ' ', '')
            ELSE CONCAT('+', REGEXP_REPLACE(o.phone,'[^0-9]+',''))
          END AS phone_e164,

          c.first_name,
          o.id                           AS reservation_no,
          a.apts                         AS apartments_for_this_res,

          CASE
            WHEN COALESCE(o.channel,'') = ''   THEN 'Unhotel'
            WHEN o.channel LIKE '%booking%'    THEN 'Booking'
            WHEN o.channel LIKE '%airbnb%'     THEN 'Airbnb'
            WHEN o.channel LIKE '%expedia%'    THEN 'Expedia'
            ELSE o.channel
          END AS OTA,

          CONCAT(
            c.first_name, ' ',
            a.apts, ' ',
            DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(o.checkin),'SYSTEM','-03:00'), '%d/%m'),
            ' - ',
            DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(o.checkout),'SYSTEM','-03:00'), '%d/%m/%Y'),
            ' ',
            CASE
              WHEN COALESCE(o.channel,'') = ''   THEN 'Unhotel'
              WHEN o.channel LIKE '%booking%'    THEN 'Booking'
              WHEN o.channel LIKE '%airbnb%'     THEN 'Airbnb'
              WHEN o.channel LIKE '%expedia%'    THEN 'Expedia'
              ELSE o.channel
            END,
            ' ',
            o.id
          ) AS contact_label,

          ROW_NUMBER() OVER (
            PARTITION BY REGEXP_REPLACE(o.phone,'[^0-9]+','')
            ORDER BY
              CASE WHEN o.checkin >= UNIX_TIMESTAMP(CONVERT_TZ(CURDATE(),'SYSTEM','-03:00'))
                   THEN 1 ELSE 0 END DESC,
              CASE WHEN o.checkin >= UNIX_TIMESTAMP(CONVERT_TZ(CURDATE(),'SYSTEM','-03:00'))
                   THEN o.checkin END ASC,
              CASE WHEN o.checkin <  UNIX_TIMESTAMP(CONVERT_TZ(CURDATE(),'SYSTEM','-03:00'))
                   THEN o.checkin END DESC
          ) AS rn
        FROM {$p}vikbooking_orders o
        JOIN {$p}vikbooking_customers_orders co ON co.idorder = o.id
        JOIN {$p}vikbooking_customers c         ON c.id      = co.idcustomer
        JOIN (
          SELECT orr.idorder AS idorder,
                 GROUP_CONCAT(DISTINCT LEFT(r.name,5) ORDER BY LEFT(r.name,5) SEPARATOR '+') AS apts
          FROM {$p}vikbooking_ordersrooms orr
          JOIN {$p}vikbooking_rooms r ON r.id = orr.idroom
          GROUP BY orr.idorder
        ) a ON a.idorder = o.id
        WHERE o.status = 'confirmed'
          AND o.phone IS NOT NULL AND o.phone <> ''
      ) x
      WHERE x.rn = 1
    ) picked
    LEFT JOIN (
      SELECT
        REGEXP_REPLACE(o.phone, '[^0-9]+', '') AS phone_digits,
        GROUP_CONCAT(
          CONCAT(
            a.apts, ' ',
            DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(o.checkin),'SYSTEM','-03:00'), '%d/%m'),
            '–',
            DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(o.checkout),'SYSTEM','-03:00'), '%d/%m/%Y'),
            ' #', o.id
          )
          ORDER BY o.checkin DESC SEPARATOR ' | '
        ) AS all_reservations
      FROM {$p}vikbooking_orders o
      JOIN (
        SELECT orr.idorder AS idorder,
               GROUP_CONCAT(DISTINCT LEFT(r.name,5) ORDER BY LEFT(r.name,5) SEPARATOR '+') AS apts
        FROM {$p}vikbooking_ordersrooms orr
        JOIN {$p}vikbooking_rooms r ON r.id = orr.idroom
        GROUP BY orr.idorder
      ) a ON a.idorder = o.id
      WHERE o.status = 'confirmed'
        AND o.phone IS NOT NULL AND o.phone <> ''
      GROUP BY REGEXP_REPLACE(o.phone,'[^0-9]+','')
    ) hist
      ON hist.phone_digits = picked.phone_digits
    WHERE picked.contact_label NOT LIKE '*%'
    ORDER BY `Name` ASC
    ";

    $rows = $wpdb->get_results($sql, ARRAY_A);
    if (!is_array($rows)) return false;

    $fh = fopen($file, 'w');
    if (!$fh) return false;
    fwrite($fh, "\xEF\xBB\xBF"); // Excel-friendly UTF‑8 BOM

    $headers = ['Name','Given Name','Family Name','Phone 1 - Type','Phone 1 - Value','Notes'];
    fputcsv($fh, $headers);
    foreach ($rows as $r) {
        $line = [];
        foreach ($headers as $h) $line[] = isset($r[$h]) ? $r[$h] : '';
        fputcsv($fh, $line);
    }
    fclose($fh);
    return true;
}
