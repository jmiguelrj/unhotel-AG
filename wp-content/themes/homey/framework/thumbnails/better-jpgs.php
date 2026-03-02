<?php
/**
 * Filters the image quality for thumbnails to be at the highest ratio possible.
 *
 * Supports the new 'wp_editor_set_quality' filter added in WP 3.5.
 *
 *
 * @package WordPress
 * @subpackage Homey
 * @since Homey 1.0.0
 */


if ( ! function_exists('homey_image_full_quality') ) {
	function homey_image_full_quality( $quality ) {
		if ( !homey_option( 'jpeg_100' ) ) {
			$quality = '90';
		} else {
			$quality = 100;
		}
		return apply_filters( 'homey_jpeg_quality', $quality );
	}
}
add_filter( 'jpeg_quality', 'homey_image_full_quality' );
add_filter( 'wp_editor_set_quality', 'homey_image_full_quality' );