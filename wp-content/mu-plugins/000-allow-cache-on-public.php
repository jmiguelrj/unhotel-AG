<?php
/**
 * Backstop: keep public pages cache-friendly even if a plugin re-adds sessions
 * or no-cache headers later in the request.
 */
if (!defined('ABSPATH')) { exit; }

function unhotel_is_cache_candidate_request(): bool {
    // Anonymous GET on front-end, not REST/AJAX/admin, and not obvious dynamic routes
    if (php_sapi_name() === 'cli') return false;
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') return false;

    $cookie = $_SERVER['HTTP_COOKIE'] ?? '';
    if ($cookie && preg_match('/(?:wordpress_logged_in_|wp-postpass_|comment_author_)/i', $cookie)) {
        return false;
    }
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $deny = [
        '/wp-admin/', '/wp-login.php', '/wp-cron.php', '/wp-comments-post.php',
        '/wp-json/', '/?rest_route=',
        '/xmlrpc.php',
        '/checkout', '/cart', '/my-account', '/login', '/logout', '/register',
        '/vikbooking', '/vb', '/owner-portal', '/account',
    ];
    foreach ($deny as $p) {
        if (stripos($uri, $p) !== false) return false;
    }
    return true;
}

// If any plugin calls nocache_headers(), neutralise it on cacheable pages
add_filter('nocache_headers', function($headers) {
    return unhotel_is_cache_candidate_request() ? [] : $headers;
}, 0);

// Late safety net: remove no-cache / cookies and set friendly headers
add_action('send_headers', function () {
    if (!unhotel_is_cache_candidate_request()) return;

    if (function_exists('header_remove')) {
        @header_remove('Pragma');
        @header_remove('Expires');
        @header_remove('Cache-Control');
    }

    // Close any session that might have been (re)opened
    if (function_exists('session_status') && session_status() === PHP_SESSION_ACTIVE) {
        if (function_exists('session_write_close')) { @session_write_close(); }
        elseif (function_exists('session_abort'))   { @session_abort(); }
        @ini_set('session.use_cookies', '0');
    }

    header('Cache-Control: public, s-maxage=3600, max-age=600');
    header('X-Cache-Eligible', 'yes');
}, 9999);

// Also correct headers just before output is finalised
add_filter('wp_headers', function(array $headers) {
    if (!unhotel_is_cache_candidate_request()) return $headers;
    unset($headers['Cache-Control'], $headers['Pragma'], $headers['Expires']);
    $headers['Cache-Control'] = 'public, s-maxage=3600, max-age=600';
    return $headers;
}, 9999);
