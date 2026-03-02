<?php
/**
 * Gutenberg Blocks Registration
 * 
 * @package Unhotel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Custom Gutenberg Blocks
 */
function unhotel_register_blocks() {
	// Register Hero block script
	wp_register_script(
		'unhotel-hero-block',
		get_template_directory_uri() . '/blocks/hero/block.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components' ),
		filemtime( get_template_directory() . '/blocks/hero/block.js' )
	);

	// Register Hero block styles (used on both frontend and editor)
	wp_register_style(
		'unhotel-hero-style',
		get_template_directory_uri() . '/blocks/hero/hero.css',
		array(),
		filemtime( get_template_directory() . '/blocks/hero/hero.css' )
	);

	// Register the Hero block
	register_block_type( 'unhotel/hero', array(
		'editor_script' => 'unhotel-hero-block',
		'style'         => 'unhotel-hero-style',
	) );

	// Register Section block script
	wp_register_script(
		'unhotel-section-block',
		get_template_directory_uri() . '/blocks/section/block.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components' ),
		filemtime( get_template_directory() . '/blocks/section/block.js' )
	);

	// Register Section block styles
	wp_register_style(
		'unhotel-section-style',
		get_template_directory_uri() . '/blocks/section/section.css',
		array(),
		filemtime( get_template_directory() . '/blocks/section/section.css' )
	);

	// Register the Section block
	register_block_type( 'unhotel/section', array(
		'editor_script' => 'unhotel-section-block',
		'style'         => 'unhotel-section-style',
	) );

	// Register Carousel block script
	wp_register_script(
		'unhotel-carousel-block',
		get_template_directory_uri() . '/blocks/carousel/block.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components' ),
		filemtime( get_template_directory() . '/blocks/carousel/block.js' )
	);

	// Register Carousel block styles
	wp_register_style(
		'unhotel-carousel-style',
		get_template_directory_uri() . '/blocks/carousel/carousel.css',
		array(),
		filemtime( get_template_directory() . '/blocks/carousel/carousel.css' )
	);

	// Register the Carousel block
	register_block_type( 'unhotel/carousel', array(
		'editor_script' => 'unhotel-carousel-block',
		'style'         => 'unhotel-carousel-style',
		'editor_style'  => 'unhotel-carousel-style',
		'render_callback' => 'unhotel_render_carousel_block',
	) );

	// Register Testimonials block script
	wp_register_script(
		'unhotel-testimonials-block',
		get_template_directory_uri() . '/blocks/testimonials/block.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components' ),
		filemtime( get_template_directory() . '/blocks/testimonials/block.js' )
	);

	// Register Testimonials block styles (only if file exists)
	$testimonial_css = get_template_directory() . '/blocks/testimonials/testimonial.css';
	if ( file_exists( $testimonial_css ) ) {
		wp_register_style(
			'unhotel-testimonials-style',
			get_template_directory_uri() . '/blocks/testimonials/testimonial.css',
			array(),
			filemtime( $testimonial_css )
		);
	}

	// Register the Testimonials block
	// Uses render_callback to ensure images are properly displayed on frontend
	register_block_type( 'unhotel/testimonials', array(
		'editor_script' => 'unhotel-testimonials-block',
		'script'        => null, // No frontend script needed - uses inline script in functions.php
		'style'         => file_exists( $testimonial_css ) ? 'unhotel-testimonials-style' : null,
		'editor_style'  => file_exists( $testimonial_css ) ? 'unhotel-testimonials-style' : null,
		'render_callback' => 'unhotel_render_testimonials_block',
		'attributes' => array(
			'testimonials' => array( 'type' => 'array', 'default' => array() ),
			'showRating' => array( 'type' => 'boolean', 'default' => true ),
			'showImage' => array( 'type' => 'boolean', 'default' => true ),
			'itemsPerSlide' => array( 'type' => 'number', 'default' => 1 ),
			'showArrows' => array( 'type' => 'boolean', 'default' => true ),
			'showDots' => array( 'type' => 'boolean', 'default' => true ),
			'fullWidth' => array( 'type' => 'boolean', 'default' => false ),
			'hasBackground' => array( 'type' => 'boolean', 'default' => false ),
			'backgroundColor' => array( 'type' => 'string', 'default' => '' ),
			'textAlign' => array( 'type' => 'string', 'default' => 'left' ),
			'displayMode' => array( 'type' => 'string', 'default' => 'slider' ),
		),
	) );
}
add_action( 'init', 'unhotel_register_blocks' );

// Include Carousel block render callback
if ( file_exists( get_template_directory() . '/blocks/carousel/carousel.php' ) ) {
	require_once get_template_directory() . '/blocks/carousel/carousel.php';
}

// Include Testimonials block render callback
if ( file_exists( get_template_directory() . '/blocks/testimonials/testimonial.php' ) ) {
	require_once get_template_directory() . '/blocks/testimonials/testimonial.php';
}

// Include Section block render callback
if ( file_exists( get_template_directory() . '/blocks/section/index.php' ) ) {
	require_once get_template_directory() . '/blocks/section/index.php';
}

/**
 * Add custom block category
 */
function unhotel_block_categories( $categories ) {
	return array_merge(
		array(
			array(
				'slug'  => 'unhotel',
				'title' => __( 'Unhotel Blocks', 'unhotel' ),
			),
		),
		$categories
	);
}
add_filter( 'block_categories_all', 'unhotel_block_categories', 10, 2 );
