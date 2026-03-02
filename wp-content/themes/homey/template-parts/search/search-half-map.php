<?php
global $post, $homey_local, $homey_prefix, $booking_type;
$homey_search_type = homey_search_type();

if( !empty($booking_type) ) {
	$homey_search_type = $booking_type;
}

if($homey_search_type == "per_hour") {
    get_template_part('template-parts/search/search-half-map', 'hourly');
} else {
    get_template_part('template-parts/search/search-half-map', 'daily');
}
?>