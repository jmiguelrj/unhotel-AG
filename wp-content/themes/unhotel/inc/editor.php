<?php
/**
 * Gutenberg Editor Customizations
 * 
 * @package Unhotel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register meta fields for page customization
 */
function unhotel_register_meta_fields() {
	// Hide page title
	register_post_meta('page', 'unhotel_hide_title', array(
		'type' => 'boolean',
		'single' => true,
		'show_in_rest' => true,
		'default' => false,
	));
	
	// Custom margin-top value
	register_post_meta('page', 'unhotel_custom_margin_top', array(
		'type' => 'string',
		'single' => true,
		'show_in_rest' => true,
		'default' => '',
	));
	
	// Custom padding value
	register_post_meta('page', 'unhotel_custom_padding', array(
		'type' => 'string',
		'single' => true,
		'show_in_rest' => true,
		'default' => '',
	));
	
	// Custom page class
	register_post_meta('page', 'unhotel_custom_page_class', array(
		'type' => 'string',
		'single' => true,
		'show_in_rest' => true,
		'default' => '',
	));
	
	// Page background color
	register_post_meta('page', 'unhotel_page_bg_color', array(
		'type' => 'string',
		'single' => true,
		'show_in_rest' => true,
		'default' => '#f7f8f9',
	));
}
add_action('init', 'unhotel_register_meta_fields');

/**
 * Enqueue editor customization scripts
 */
function unhotel_enqueue_editor_assets() {
	// Hide title option
	wp_enqueue_script(
		'unhotel-hide-title-meta',
		get_template_directory_uri() . '/inc/editor/hide-title-meta.js',
		array('wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data'),
		filemtime(get_template_directory() . '/inc/editor/hide-title-meta.js')
	);
}
add_action('enqueue_block_editor_assets', 'unhotel_enqueue_editor_assets');

/**
 * Add custom page styling based on meta options
 */
function unhotel_page_custom_styles() {
	if (!is_page()) {
		return;
	}
	
	$page_id = get_the_ID();
	$hide_title = get_post_meta($page_id, 'unhotel_hide_title', true);
	$page_bg_color = get_post_meta($page_id, 'unhotel_page_bg_color', true);
	$custom_margin_top = get_post_meta($page_id, 'unhotel_custom_margin_top', true);
	$custom_padding = get_post_meta($page_id, 'unhotel_custom_padding', true);
	
	// Check if meta exists - if not, it's a new page so set default
	$meta_exists = metadata_exists('post', $page_id, 'unhotel_page_bg_color');
	if (!$meta_exists || ($page_bg_color === false)) {
		$page_bg_color = '#f7f8f9';
	}
	// If meta exists but is empty string, user explicitly cleared it - respect that
	
	// Start style output
	echo '<!-- Page ID: ' . $page_id . ', BG Color: ' . $page_bg_color . ' -->' . "\n";
	echo '<style type="text/css">' . "\n";
	
	// Only apply title styles if title is NOT hidden
	if (!$hide_title) {
		// Set defaults when title is visible
		if ($custom_margin_top === '') {
			$custom_margin_top = '0';
		}
		if ($custom_padding === '') {
			$custom_padding = '40px';
		}
		
		if (!empty($custom_margin_top) || !empty($custom_padding)) {
			echo '.page-id-' . $page_id . ' .post-header.title-page-visible {';
			
			if (!empty($custom_margin_top)) {
				echo 'margin: ' . esc_attr($custom_margin_top) . ' !important;';
			}
			
			if (!empty($custom_padding)) {
				echo 'padding: ' . esc_attr($custom_padding) . ' !important;';
			}
			
			echo '}' . "\n";
		}
	}
	
	// Apply page background color to content area (only if not explicitly cleared)
	if (!empty($page_bg_color)) {
		echo 'body.page-id-' . $page_id . ' .main-content-area {' . "\n";
		echo '  background-color: ' . esc_attr($page_bg_color) . ' !important;' . "\n";
		echo '}' . "\n";
	}
	
	echo '</style>' . "\n";
}
add_action('wp_head', 'unhotel_page_custom_styles');
