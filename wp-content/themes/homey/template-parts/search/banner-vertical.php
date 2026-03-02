<?php
global $post, $homey_local, $homey_prefix;
$homey_search_type = homey_search_type();

$search_type = get_post_meta( $post->ID, 'homey_banner_search_hourly', true );
if( $search_type ) {
	$homey_search_type = 'per_hour';
}

if($homey_search_type == "per_hour") {
    get_template_part('template-parts/search/banner-vertical', 'hourly');
} else {
    get_template_part('template-parts/search/banner-vertical', 'daily');
}

