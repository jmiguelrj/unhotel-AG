<?php
/**
 * Layout Padding Functions
 * 
 * Handles global padding CSS injection and related functionality
 *
 * @package Unhotel
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

/**
 * Inject global padding CSS variables with responsive values
 */
function unhotel_inject_padding_css() {
	// Get padding values from customizer
	$desktop_left = get_theme_mod('unhotel_padding_desktop_left', 20);
	$desktop_right = get_theme_mod('unhotel_padding_desktop_right', 20);
	$tablet_left = get_theme_mod('unhotel_padding_tablet_left', 15);
	$tablet_right = get_theme_mod('unhotel_padding_tablet_right', 15);
	$mobile_left = get_theme_mod('unhotel_padding_mobile_left', 10);
	$mobile_right = get_theme_mod('unhotel_padding_mobile_right', 10);
	
	// Get container max width
	$container_max_width = get_theme_mod('unhotel_container_max_width', 1200);
	
	// Get padding application settings (default is true = apply padding)
	$apply_padding_poa = get_theme_mod('unhotel_apply_padding_poa', true);
	
	// Check if current page should have padding removed
	$is_poa_page = false;
	// Check for POA page by URL path (works for plugin-generated pages too)
	$current_url = $_SERVER['REQUEST_URI'];
	$is_poa_page = (strpos($current_url, '/poa/') !== false);
	
	// Note: VIK pages are handled via CSS classes, not here
	// Determine if padding should be removed (only for POA when checkbox is UNCHECKED)
	$remove_padding = ($is_poa_page && !$apply_padding_poa);

	// Output CSS
	echo '<style type="text/css" id="unhotel-global-padding">' . "\n";
	echo ':root {' . "\n";
	echo '  --container-max-width: ' . absint($container_max_width) . 'px;' . "\n";
	
	if ($remove_padding) {
		// Set padding to 0
		echo '  --container-padding-left: 0px;' . "\n";
		echo '  --container-padding-right: 0px;' . "\n";
	} else {
		// Mobile first (default)
		echo '  --container-padding-left: ' . absint($mobile_left) . 'px;' . "\n";
		echo '  --container-padding-right: ' . absint($mobile_right) . 'px;' . "\n";
	}
	
	echo '}' . "\n";
	
	if (!$remove_padding) {
		// Tablet
		echo '@media (min-width: 768px) and (max-width: 991px) {' . "\n";
		echo '  :root {' . "\n";
		echo '    --container-padding-left: ' . absint($tablet_left) . 'px;' . "\n";
		echo '    --container-padding-right: ' . absint($tablet_right) . 'px;' . "\n";
		echo '  }' . "\n";
		echo '}' . "\n";
		
		// Desktop
		echo '@media (min-width: 992px) {' . "\n";
		echo '  :root {' . "\n";
		echo '    --container-padding-left: ' . absint($desktop_left) . 'px;' . "\n";
		echo '    --container-padding-right: ' . absint($desktop_right) . 'px;' . "\n";
		echo '  }' . "\n";
		echo '}' . "\n";
	}
	
	echo '</style>' . "\n";
}
add_action('wp_head', 'unhotel_inject_padding_css', 5);

/**
 * Add padding-related body classes
 */
function unhotel_padding_body_classes($classes) {
	// Check URL path for POA (works for plugin-generated pages)
	$current_url = $_SERVER['REQUEST_URI'];
	$is_poa_page = (strpos($current_url, '/poa/') !== false);
	
	if ($is_poa_page) {
		$apply_padding_poa = get_theme_mod('unhotel_apply_padding_poa', true);
		if ($apply_padding_poa) {
			// CHECKED: Apply padding to POA pages
			$classes[] = 'apply-padding-poa';
        }
		// } else {
		// 	// UNCHECKED: Exclude padding from POA pages
		// 	$classes[] = 'exclude-padding-poa';
		// }
	}
	
	// Check if this is a VIK page and global padding should be applied (when CHECKED)
	// Exclude homepage - homepage should always use global padding even if VIK widget is present
	$apply_padding_vik = get_theme_mod('unhotel_apply_padding_vik', true);
	if ($apply_padding_vik && !is_front_page() && !is_home()) {
		// Check if any existing body class starts with custom-vk_ or custom_vk_
		 $classes[] = 'apply-global-padding-vik';
      

	}
	return $classes;
}
add_filter('body_class', 'unhotel_padding_body_classes', 999);
