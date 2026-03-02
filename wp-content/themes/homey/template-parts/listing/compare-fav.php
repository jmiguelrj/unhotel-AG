<?php
global $post, $current_user, $homey_local;
wp_get_current_user();

$key = '';
$userID      =   $current_user->ID;
$fav_option = 'homey_favorites-'.$userID;
$fav_option = get_option( $fav_option );
if( !empty($fav_option) ) {
    $key = array_search($post->ID, $fav_option);
}

if( $key != false || $key != '' ) {
    $favorite = $homey_local['remove_favorite'];
} else {
    $favorite = $homey_local['add_favorite'];
}
?>
<button class="btn-item-tools dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-ellipsis-v" aria-hidden="true"></i></button>
<ul class="dropdown-menu">
    <li>
    	<a class="homey_compare compare-<?php echo intval($post->ID); ?>" href="" data-listing_id="<?php echo intval($post->ID); ?>"><?php echo esc_attr($homey_local['compare_label']); ?></a>
    </li>
    <li>
    	<a href="" class="add_fav" data-listid="<?php echo intval($post->ID); ?>">
    		<?php echo esc_html($favorite); ?>
    	</a>
    </li>
</ul>