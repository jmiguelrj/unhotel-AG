<?php
/**
 * Created by PhpStorm.
 * User: waqasriaz
 * Date: 16/12/15
 * Time: 5:02 PM
 */
global $post;
$sidebar_meta = array('homey_sidebar' => 'no');
if( homey_postid_needed() ) {
    $sidebar_meta = isset($post->ID) ? homey_get_sidebar_meta($post->ID) : '';
}
?>

<?php
if( $sidebar_meta['homey_sidebar'] == 'yes' && !empty($sidebar_meta['selected_sidebar']) ) {
    if( is_active_sidebar( $sidebar_meta['selected_sidebar'] ) ) {
        dynamic_sidebar( $sidebar_meta['selected_sidebar'] );
    }
} else {
    if (is_active_sidebar('blog-sidebar')) {
        dynamic_sidebar('blog-sidebar');
    }
}
?>