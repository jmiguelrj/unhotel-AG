<?php
global $post, $homey_prefix;
$header_title = esc_html__(get_post_meta( $post->ID, $homey_prefix. 'header_title', true ), 'homey');
$header_subtitle = esc_html__(get_post_meta( $post->ID, $homey_prefix. 'header_subtitle', true ), 'homey');
$banner_search = esc_html__(get_post_meta( $post->ID, 'homey_head_search_style', true), 'homey');

if(homey_is_splash()) {
	$header_title = esc_html__(homey_option( 'splash_welcome_text' ), 'homey');
	$header_subtitle = esc_html__(homey_option( 'splash_welcome_sub' ), 'homey');
}

if(!empty($header_title)) {
	echo '<h2 class="banner-title">'.esc_attr($header_title).'</h2>';
}

if(!empty($header_subtitle)) {
	echo '<p class="banner-subtitle">'.esc_attr($header_subtitle).'</p>';
} 
?>