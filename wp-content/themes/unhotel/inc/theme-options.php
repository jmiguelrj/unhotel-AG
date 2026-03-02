<?php
/**
 * Theme Options and Customizer Settings
 *
 * @package Unhotel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Add Customizer Support
 */
function unhotel_customize_register( $wp_customize ) {
	// Add section for theme options
	$wp_customize->add_section( 'unhotel_options', array(
		'title'    => esc_html__( 'Theme Options', 'unhotel' ),
		'priority' => 130,
	) );

	// Add Main Image setting
	$wp_customize->add_setting( 'unhotel_main_image', array(
		'default'           => '',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'unhotel_main_image', array(
		'label'       => esc_html__( 'Hero Background Image', 'unhotel' ),
		'description' => esc_html__( 'Upload a background image for the hero section', 'unhotel' ),
		'section'     => 'unhotel_options',
		'mime_type'   => 'image',
	) ) );

	// Hero Heading
	$wp_customize->add_setting( 'unhotel_hero_heading', array(
		'default'           => 'Rent your vacation apartment in Rio de Janeiro',
		'sanitize_callback' => 'sanitize_text_field',
	) );

	$wp_customize->add_control( 'unhotel_hero_heading', array(
		'label'       => esc_html__( 'Hero Heading', 'unhotel' ),
		'description' => esc_html__( 'Main heading for the hero section', 'unhotel' ),
		'section'     => 'unhotel_options',
		'type'        => 'text',
	) );

	// Hero Subtitle
	$wp_customize->add_setting( 'unhotel_hero_subtitle', array(
		'default'           => 'Experience the comfort of a Boutique Hotel',
		'sanitize_callback' => 'sanitize_text_field',
	) );

	$wp_customize->add_control( 'unhotel_hero_subtitle', array(
		'label'       => esc_html__( 'Hero Subtitle', 'unhotel' ),
		'description' => esc_html__( 'Subtitle text for the hero section', 'unhotel' ),
		'section'     => 'unhotel_options',
		'type'        => 'text',
	) );

	// Footer Logo
	$wp_customize->add_setting( 'unhotel_footer_logo', array(
		'default'           => '',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'unhotel_footer_logo', array(
		'label'       => esc_html__( 'Footer Logo', 'unhotel' ),
		'description' => esc_html__( 'Upload a logo for the footer area', 'unhotel' ),
		'section'     => 'unhotel_options',
		'mime_type'   => 'image',
	) ) );

	// Footer Description
	$wp_customize->add_setting( 'unhotel_footer_description', array(
		'default'           => '',
		'sanitize_callback' => 'wp_kses_post',
	) );

	$wp_customize->add_control( 'unhotel_footer_description', array(
		'label'       => esc_html__( 'Footer Description', 'unhotel' ),
		'description' => esc_html__( 'Add text or HTML for the footer description', 'unhotel' ),
		'section'     => 'unhotel_options',
		'type'        => 'textarea',
	) );

	// Add Layout section for padding controls
	$wp_customize->add_section( 'unhotel_layout', array(
		'title'    => esc_html__( 'Layout Settings', 'unhotel' ),
		'priority' => 131,
	) );

	// Desktop Padding Left
	$wp_customize->add_setting( 'unhotel_padding_desktop_left', array(
		'default'           => '20',
		'sanitize_callback' => 'absint',
		'transport'         => 'postMessage',
	) );

	$wp_customize->add_control( 'unhotel_padding_desktop_left', array(
		'label'       => esc_html__( 'Desktop Left Padding (px)', 'unhotel' ),
		'description' => esc_html__( 'Set the left padding for desktop screens (min-width: 992px)', 'unhotel' ),
		'section'     => 'unhotel_layout',
		'type'        => 'number',
		'input_attrs' => array(
			'min'  => 0,
			'max'  => 200,
			'step' => 1,
		),
	) );

	// Desktop Padding Right
	$wp_customize->add_setting( 'unhotel_padding_desktop_right', array(
		'default'           => '20',
		'sanitize_callback' => 'absint',
		'transport'         => 'postMessage',
	) );

	$wp_customize->add_control( 'unhotel_padding_desktop_right', array(
		'label'       => esc_html__( 'Desktop Right Padding (px)', 'unhotel' ),
		'description' => esc_html__( 'Set the right padding for desktop screens (min-width: 992px)', 'unhotel' ),
		'section'     => 'unhotel_layout',
		'type'        => 'number',
		'input_attrs' => array(
			'min'  => 0,
			'max'  => 200,
			'step' => 1,
		),
	) );

	// Tablet Padding Left
	$wp_customize->add_setting( 'unhotel_padding_tablet_left', array(
		'default'           => '15',
		'sanitize_callback' => 'absint',
		'transport'         => 'postMessage',
	) );

	$wp_customize->add_control( 'unhotel_padding_tablet_left', array(
		'label'       => esc_html__( 'Tablet Left Padding (px)', 'unhotel' ),
		'description' => esc_html__( 'Set the left padding for tablet screens (768px - 991px)', 'unhotel' ),
		'section'     => 'unhotel_layout',
		'type'        => 'number',
		'input_attrs' => array(
			'min'  => 0,
			'max'  => 200,
			'step' => 1,
		),
	) );

	// Tablet Padding Right
	$wp_customize->add_setting( 'unhotel_padding_tablet_right', array(
		'default'           => '15',
		'sanitize_callback' => 'absint',
		'transport'         => 'postMessage',
	) );

	$wp_customize->add_control( 'unhotel_padding_tablet_right', array(
		'label'       => esc_html__( 'Tablet Right Padding (px)', 'unhotel' ),
		'description' => esc_html__( 'Set the right padding for tablet screens (768px - 991px)', 'unhotel' ),
		'section'     => 'unhotel_layout',
		'type'        => 'number',
		'input_attrs' => array(
			'min'  => 0,
			'max'  => 200,
			'step' => 1,
		),
	) );

	// Mobile Padding Left
	$wp_customize->add_setting( 'unhotel_padding_mobile_left', array(
		'default'           => '10',
		'sanitize_callback' => 'absint',
		'transport'         => 'postMessage',
	) );

	$wp_customize->add_control( 'unhotel_padding_mobile_left', array(
		'label'       => esc_html__( 'Mobile Left Padding (px)', 'unhotel' ),
		'description' => esc_html__( 'Set the left padding for mobile screens (max-width: 767px)', 'unhotel' ),
		'section'     => 'unhotel_layout',
		'type'        => 'number',
		'input_attrs' => array(
			'min'  => 0,
			'max'  => 100,
			'step' => 1,
		),
	) );

	// Mobile Padding Right
	$wp_customize->add_setting( 'unhotel_padding_mobile_right', array(
		'default'           => '10',
		'sanitize_callback' => 'absint',
		'transport'         => 'postMessage',
	) );

	$wp_customize->add_control( 'unhotel_padding_mobile_right', array(
		'label'       => esc_html__( 'Mobile Right Padding (px)', 'unhotel' ),
		'description' => esc_html__( 'Set the right padding for mobile screens (max-width: 767px)', 'unhotel' ),
		'section'     => 'unhotel_layout',
		'type'        => 'number',
		'input_attrs' => array(
			'min'  => 0,
			'max'  => 100,
			'step' => 1,
		),
	) );

	// Apply padding to POA pages
	$wp_customize->add_setting( 'unhotel_apply_padding_poa', array(
		'default'           => true,
		'sanitize_callback' => 'rest_sanitize_boolean',
	) );

	$wp_customize->add_control( 'unhotel_apply_padding_poa', array(
		'label'       => esc_html__( 'Apply Global Padding to POA Pages', 'unhotel' ),
		'description' => esc_html__( 'Uncheck this to remove padding from POA (Property Owner Access) pages', 'unhotel' ),
		'section'     => 'unhotel_layout',
		'type'        => 'checkbox',
	) );

	// Apply padding to VIK pages
	$wp_customize->add_setting( 'unhotel_apply_padding_vik', array(
		'default'           => true,
		'sanitize_callback' => 'rest_sanitize_boolean',
	) );

	$wp_customize->add_control( 'unhotel_apply_padding_vik', array(
		'label'       => esc_html__( 'Apply Global Padding to VIK Pages', 'unhotel' ),
		'description' => esc_html__( 'Uncheck this to remove padding from VikBooking plugin pages', 'unhotel' ),
		'section'     => 'unhotel_layout',
		'type'        => 'checkbox',
	) );

	// Container Max Width
	$wp_customize->add_setting( 'unhotel_container_max_width', array(
		'default'           => '1200',
		'sanitize_callback' => 'absint',
		'transport'         => 'postMessage',
	) );

	$wp_customize->add_control( 'unhotel_container_max_width', array(
		'label'       => esc_html__( 'Container Max Width (px)', 'unhotel' ),
		'description' => esc_html__( 'Set the maximum width for .section-inner-container', 'unhotel' ),
		'section'     => 'unhotel_layout',
		'type'        => 'number',
		'input_attrs' => array(
			'min'  => 600,
			'max'  => 2000,
			'step' => 10,
		),
	) );
}
add_action( 'customize_register', 'unhotel_customize_register' );

/**
 * Get Main Image URL
 */
function unhotel_get_main_image() {
	$image_id = get_theme_mod( 'unhotel_main_image' );
	if ( $image_id ) {
		return wp_get_attachment_image_url( $image_id, 'full' );
	}
	return '';
}

/**
 * Display Main Image
 */
function unhotel_display_main_image( $class = '' ) {
	$image_url = unhotel_get_main_image();
	if ( $image_url ) {
		echo '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( get_bloginfo( 'name' ) ) . '" class="' . esc_attr( $class ) . '">';
	}
}

/**
 * Get Hero Heading
 */
// function unhotel_get_hero_heading() {
// 	return get_theme_mod( 'unhotel_hero_heading', 'Rent your vacation apartment in Rio de Janeiro' );
// }

// /**
//  * Get Hero Subtitle
//  */
// function unhotel_get_hero_subtitle() {
// 	return get_theme_mod( 'unhotel_hero_subtitle', 'Experience the comfort of a Boutique Hotel' );
// }

/**
 * Get Footer Logo URL
 */
function unhotel_get_footer_logo() {
	$image_id = get_theme_mod( 'unhotel_footer_logo' );
	if ( $image_id ) {
		return wp_get_attachment_image_url( $image_id, 'full' );
	}
	return '';
}

/**
 * Display Footer Logo
 */
function unhotel_display_footer_logo( $class = 'footer-logo' ) {
	$logo_url = unhotel_get_footer_logo();
	if ( $logo_url ) {
		echo '<img src="' . esc_url( $logo_url ) . '" alt="' . esc_attr( get_bloginfo( 'name' ) ) . '" class="' . esc_attr( $class ) . '">';
	}
}

/**
 * Get Footer Description
 */
// function unhotel_get_footer_description() {
// 	return get_theme_mod( 'unhotel_footer_description', '' );
// }

/**
 * Display Footer Description
 */

/**
 * Get Footer Description
 */
function unhotel_get_footer_description() {
	return get_theme_mod( 'unhotel_footer_description', '' );
}


/**
 * Get Hero Heading
 */
function unhotel_get_hero_heading() {
    $value = get_theme_mod( 'unhotel_hero_heading', 'Rent your vacation apartment in Rio de Janeiro' );
    return apply_filters( 'wpml_translate_single_string', $value, 'unhotel', 'Hero Heading' );
}

/**
 * Get Hero Subtitle
 */
function unhotel_get_hero_subtitle() {
    $value = get_theme_mod( 'unhotel_hero_subtitle', 'Experience the comfort of a Boutique Hotel' );
    return apply_filters( 'wpml_translate_single_string', $value, 'unhotel', 'Hero Subtitle' );
}


/**
 * Get Footer Description
 */
function unhotel_display_footer_description() {
    $value = unhotel_get_footer_description();
    if ( ! empty( $value ) ) {
        echo '<div class="footer-description">' . wp_kses_post( $value ) . '</div>';
    }
}



