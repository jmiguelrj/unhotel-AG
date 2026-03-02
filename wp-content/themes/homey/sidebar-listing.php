<?php
global $post;
$pageID = isset($post->ID) ? $post->ID : '';
$sidebar_meta = homey_get_sidebar_meta($pageID);
if( is_singular('listing') ) { 

    if( is_active_sidebar( 'single-listing' ) ) {
        dynamic_sidebar( 'single-listing' );
    }
} else {
    if( $sidebar_meta['homey_sidebar'] == 'yes' && !empty($sidebar_meta['selected_sidebar']) ) {
        if( is_active_sidebar( $sidebar_meta['selected_sidebar'] ) ) {
            dynamic_sidebar( $sidebar_meta['selected_sidebar'] );
        }
    } else {
        if( is_active_sidebar( 'listing-sidebar' ) ) {
            dynamic_sidebar( 'listing-sidebar' );
        }
    }
}

?>