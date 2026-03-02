<?php
add_filter( 'pre_http_request', function( $preempt, $parsed_args, $url ) {
    return new WP_Error( 'http_blocked', 'External HTTP requests blocked locally.' );
}, 10, 3 );
