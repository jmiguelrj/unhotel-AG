<?php
global $homey_local, $homey_prefix, $template;
$homey_local = homey_get_localization();
$homey_prefix = 'homey_';
/**
 * @package Homey
 * @since Homey 1.0
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
    <?php if(basename($template) == "dashboard-reservations.php"){ ?>
        <meta name="robots" content="noindex,nofollow">
    <?php } ?>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

    <?php if( homey_get_map_system() == 'mapbox' ) { ?>
        <script src='https://api.tiles.mapbox.com/mapbox-gl-js/v1.12.0/mapbox-gl.js'></script>
        <link href='https://api.tiles.mapbox.com/mapbox-gl-js/v1.12.0/mapbox-gl.css' rel='stylesheet' />
    <?php } ?>
	<?php wp_head(); ?>
</head>


<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php 
$header_type = homey_option('header_type');
if( empty($header_type)) {
    $header_type = '1';
}
if(homey_is_dashboard()) {
    $header_type = '1';
}

if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'header' ) ) {
    get_template_part( 'template-parts/header/header', $header_type ); 
}
?>


<div id="section-body">
<?php 
if(homey_banner_needed()) {
    get_template_part('template-parts/banner/main'); 
}
?>	

<?php
$enable_search = homey_option('enable_search');
$search_position = homey_option('search_position');
$search_pages = homey_option('search_pages');
$search_selected_pages = homey_option('search_selected_pages');

if(isset($_GET['search_position'])) {
    $search_position = $_GET['search_position'];
}

if( !homey_is_dashboard() ) {

    if(homey_search_needed()) {

        if (!is_home() && !is_singular('post')) {
            if ($enable_search != 0 && $search_position == 'under_banner') {
                if ($search_pages == 'only_home') {
                    if (is_front_page()) {
                        get_template_part ('template-parts/search/main-search');
                    }
                } elseif ($search_pages == 'all_pages') {
                        get_template_part ('template-parts/search/main-search');

                } elseif ($search_pages == 'only_innerpages') {
                    if (!is_front_page()) {
                        get_template_part ('template-parts/search/main-search');
                    }
                } else if( $search_pages == 'specific_pages' ) { 
                    if( is_page( $search_selected_pages ) ) { 
                        get_template_part ('template-parts/search/main-search');
                    }
                } else if( $search_pages == 'only_taxonomy_pages' ) { 
                    if( is_tax() ) {
                        get_template_part ('template-parts/search/main-search');
                    }
                }
            }
        }
    } //homey_search_needed
} //homey_is_dashboard

$already_signed_up = isset($_GET["already_signed_up"]) ? $_GET["already_signed_up"] : '';
?>
<div id="has_social_account" style="display: none" data-has-social-account="<?php echo esc_attr($already_signed_up);?>"></div>

