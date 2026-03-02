<?php
/**
 * Template Name: Search Results
 */
get_header();
global $post, $wp_query, $paged, $listing_founds, $number_of_listings;

$search_result_page = homey_option('search_result_page');

if($search_result_page == 'half_map') {
    get_template_part('template-parts/half_map'); 
} else {
    get_template_part('template-parts/normal_page'); 
}

get_footer(); 
?>