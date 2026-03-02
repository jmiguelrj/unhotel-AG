<?php
namespace UnhotelFD;

if (!defined('ABSPATH')) exit;

class Rest {
    public static function init() {
        add_action('rest_api_init', [__CLASS__, 'routes']);
    }

    public static function routes() {
        register_rest_route('unhotel/v1', '/arrivals', [
            'methods'  => 'GET',
            'callback' => [__CLASS__, 'list'],
            'permission_callback' => function(){ return current_user_can('manage_unhotel_frontdesk'); },
            'args' => [
                'page'   => ['default'=>1],
                'limit'  => ['default'=>50],
                'window' => ['default'=>'today'],
                'search' => ['default'=>''],
            ]
        ]);

        register_rest_route('unhotel/v1', '/arrivals/(?P<idor>\d+)', [
            'methods'  => 'PATCH',
            'callback' => [__CLASS__, 'update'],
            'permission_callback' => function(){ return current_user_can('manage_unhotel_frontdesk'); },
        ]);
    }

    public static function list(\WP_REST_Request $req) {
        global $wpdb; $p = $wpdb->prefix;

        $page  = max(1, (int)$req->get_param('page'));
        $limit = min(100, max(10, (int)$req->get_param('limit')));
        $offset= ($page-1)*$limit;
        $window= sanitize_text_field($req->get_param('window'));
        $search= trim((string)$req->get_param('search'));

        $where = [];
        $where[] = "o.status = 'confirmed'";
        $where[] = "(o.custdata IS NULL OR o.custdata <> 'Room Closed')";

        $rio_today_start = "UNIX_TIMESTAMP(CONVERT_TZ(CURDATE(),'SYSTEM','-03:00'))";
        $rio_today_end   = "UNIX_TIMESTAMP(CONVERT_TZ(DATE_ADD(CURDATE(), INTERVAL 1 DAY),'SYSTEM','-03:00'))";

        if ($window === 'today') {
            $where[] = "o.checkin >= $rio_today_start AND o.checkin < $rio_today_end";
        } elseif ($window === 'tomorrow') {
            $where[] = "o.checkin >= $rio_today_end AND o.checkin < UNIX_TIMESTAMP(CONVERT_TZ(DATE_ADD(CURDATE(), INTERVAL 2 DAY),'SYSTEM','-03:00'))";
        } elseif ($window === 'upcoming') {
            $where[] = "o.checkin >= $rio_today_end";
        } elseif ($window === 'lastminute') {
            $where[] = "o.checkin BETWEEN UNIX_TIMESTAMP(CONVERT_TZ(NOW(),'SYSTEM','-03:00'))
                                 AND UNIX_TIMESTAMP(CONVERT_TZ(DATE_ADD(NOW(), INTERVAL 3 HOUR),'SYSTEM','-03:00'))";
        } else {
            $where[] = "o.checkin BETWEEN $rio_today_start AND UNIX_TIMESTAMP(CONVERT_TZ(DATE_ADD(CURDATE(), INTERVAL 31 DAY),'SYSTEM','-03:00'))";
        }

        if ($search !== '') {
            $like = '%'.$wpdb->esc_like($search).'%';
            $where[] = $wpdb->prepare("(c.first_name LIKE %s OR r.name LIKE %s OR o.id LIKE %s OR o.phone LIKE %s)", $like, $like, $like, $like);
        }
        $where_sql = $where ? 'WHERE '.implode(' AND ', $where) : '';

        // Cache only when no search
        $cache_key = '';
        if ($search === '') {
            $cache_key = 'unfd__'.md5(json_encode([$window,$page,$limit, get_option('unhotel_fd_cache_buster')]));
            $cached = get_transient($cache_key);
            if ($cached) return rest_ensure_response($cached);
        }

        $sql = "
        SELECT
          orr.id AS idorderroom,
          o.id   AS reservation_no,
          LEFT(r.name,5) AS apartment,
          c.first_name AS guest_first,
          o.checkin AS checkin_ts,
          COALESCE(fd.arrival_time, orr.Arrival_Time) AS arrival_time,
          fd.reg_status, fd.receiver, COALESCE(fd.obs, orr.Observation) AS obs
        FROM {$p}vikbooking_orders o
        JOIN {$p}vikbooking_ordersrooms orr ON orr.idorder = o.id
        JOIN {$p}vikbooking_rooms r ON r.id = orr.idroom
        LEFT JOIN {$p}vikbooking_customers_orders co ON co.idorder = o.id
        LEFT JOIN {$p}vikbooking_customers c ON c.id = co.idcustomer
        LEFT JOIN {$p}unhotel_frontdesk fd ON fd.idorderroom = orr.id
        $where_sql
        ORDER BY o.checkin ASC, r.name ASC
        LIMIT %d OFFSET %d";

        $rows = $wpdb->get_results($wpdb->prepare($sql, $limit, $offset), ARRAY_A);

        $count_sql = "
        SELECT COUNT(*)
        FROM {$p}vikbooking_orders o
        JOIN {$p}vikbooking_ordersrooms orr ON orr.idorder = o.id
        JOIN {$p}vikbooking_rooms r ON r.id = orr.idroom
        LEFT JOIN {$p}vikbooking_customers_orders co ON co.idorder = o.id
        LEFT JOIN {$p}vikbooking_customers c ON c.id = co.idcustomer
        $where_sql";
        $total = (int)$wpdb->get_var($count_sql);

        $resp = [
            'total' => $total,
            'rows'  => $rows,
            'lists' => [
                'reg_status' => get_option('unhotel_fd_reg_status_options', []),
                'receiver'   => get_option('unhotel_fd_receiver_options', []),
            ]
        ];

        if ($cache_key) {
            set_transient($cache_key, $resp, 60); // 60s cache
        }

        return rest_ensure_response($resp);
    }

    public static function update(\WP_REST_Request $req) {
        global $wpdb; $p = $wpdb->prefix;
        $idor = (int)$req['idor'];

        $arrival = $req->get_param('arrival_time');
        $reg     = $req->get_param('reg_status');
        $recv    = $req->get_param('receiver');
        $obs     = $req->get_param('obs');

        $data = ['updated_by'=>get_current_user_id()];
        if ($arrival !== null) $data['arrival_time'] = preg_match('/^\d{2}:\d{2}$/', (string)$arrival) ? $arrival : null;
        if ($reg !== null)     $data['reg_status']   = sanitize_text_field($reg);
        if ($recv !== null)    $data['receiver']     = sanitize_text_field($recv);
        if ($obs !== null)     $data['obs']          = wp_kses_post($obs);

        $table = "{$p}unhotel_frontdesk";
        $exists = (int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE idorderroom=%d", $idor));
        if ($exists) $wpdb->update($table, $data, ['idorderroom'=>$idor]);
        else { $data['idorderroom'] = $idor; $wpdb->insert($table, $data); }

        // Bust cache
        update_option('unhotel_fd_cache_buster', time());

        return rest_ensure_response(['ok'=>true]);
    }
}
Rest::init();
