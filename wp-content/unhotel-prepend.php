<?php
/**
 * Unhotel early header/cookie guard.
 * Runs before WordPress, MU-plugins, and normal plugins (via auto_prepend_file).
 * Goal: keep anonymous, cacheable pages free of PHPSESSID and no-cache headers
 * so SiteGround Dynamic Cache/CDN can return x-proxy-cache: HIT.
 */

if (PHP_SAPI === 'cli') { return; }

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri    = $_SERVER['REQUEST_URI'] ?? '/';
$cookie = $_SERVER['HTTP_COOKIE'] ?? '';

/** Decide if this request should be cacheable */
$cacheable =
    ($method === 'GET')
    // if the visitor is logged in or has protected-post/comment cookies, don't cache
    && !preg_match('/(?:wordpress_logged_in_|wp-postpass_|comment_author_)/i', $cookie);

// Exclude admin/REST/AJAX and obviously dynamic routes (adjust to your site)
if ($cacheable) {
    foreach ([
        '/wp-admin/', '/wp-login.php', '/wp-cron.php', '/wp-comments-post.php',
        '/wp-json/', '/?rest_route=',
        '/xmlrpc.php',
        '/checkout', '/cart', '/my-account', '/login', '/logout', '/register',
        '/vikbooking', '/vb',              // VikBooking front routes (adjust if different)
        '/owner-portal', '/account',       // POA routes (adjust if different)
    ] as $deny) {
        if (stripos($uri, $deny) !== false) { $cacheable = false; break; }
    }
}

if ($cacheable) {
    // 1) Prevent PHP from issuing a PHPSESSID cookie and default "nocache" limiter
    @ini_set('session.use_cookies', '0');    // no Set-Cookie from session module
    @ini_set('session.use_only_cookies', '1');
    @ini_set('session.cache_limiter', '');   // don't add no-store/no-cache automatically

    // 2) Register a header callback that fires *right before* headers are sent.
    //    This lets us strip any Set-Cookie / no-cache headers that plugins add later.
    if (function_exists('header_register_callback')) {
        header_register_callback(function () {
            // remove cookies and no-cache headers, then set cache-friendly ones
            if (function_exists('header_remove')) {
                @header_remove('Set-Cookie');   // drop ALL response cookies on cacheable pages
                @header_remove('Pragma');
                @header_remove('Expires');
                @header_remove('Cache-Control');
            }
            // Friendly cache headers: 10m browser, 60m proxy/CDN
            header('Cache-Control: public, s-maxage=3600, max-age=600', true);
            header('X-Cache-Eligible: yes', true);
        });
    }
} else {
    // Helpful hint for debugging
    header('X-Cache-Eligible: no', true);
}
