<?php
/**
 * Testimonials Block Render Callback
 * 
 * This render callback ensures images are properly displayed on the frontend
 * by converting attachment IDs to URLs if needed.
 * 
 * @package Unhotel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render callback for testimonials block
 * 
 * @param array $attributes Block attributes
 * @param string $content Block content (saved HTML)
 * @return string Rendered HTML
 */
function unhotel_render_testimonials_block( $attributes, $content ) {
	// Ensure attributes is an array
	if ( ! is_array( $attributes ) ) {
		$attributes = array();
	}

	// If no testimonials, return empty
	if ( empty( $attributes['testimonials'] ) || ! is_array( $attributes['testimonials'] ) ) {
		return '';
	}

	// Process testimonials to ensure image URLs are valid
	$processed_testimonials = array();
	foreach ( $attributes['testimonials'] as $testimonial ) {
		$image_url = ! empty( $testimonial['authorImageUrl'] ) ? $testimonial['authorImageUrl'] : '';
		$image_id  = ! empty( $testimonial['authorImageId'] ) ? intval( $testimonial['authorImageId'] ) : 0;

		// If we have an image ID but no URL, or if URL is invalid, try to get URL from ID
		if ( $image_id > 0 && ( empty( $image_url ) || ! filter_var( $image_url, FILTER_VALIDATE_URL ) ) ) {
			$image_url = wp_get_attachment_image_url( $image_id, 'thumbnail' );
			if ( ! $image_url ) {
				// Fallback to full size if thumbnail doesn't exist
				$image_url = wp_get_attachment_image_url( $image_id, 'full' );
			}
		}

		// Update testimonial with valid image URL
		$testimonial['authorImageUrl'] = $image_url;
		$processed_testimonials[] = $testimonial;
	}

	// Update attributes with processed testimonials
	$attributes['testimonials'] = $processed_testimonials;

	// Use the default block rendering but with updated attributes
	// WordPress will use the saved HTML from the save() function, but we can filter it
	// For now, let's use a custom render that ensures images work
	
	$display_mode = ! empty( $attributes['displayMode'] ) ? $attributes['displayMode'] : 'slider';
	
	$container_classes = array( 'unhotel-testimonials-carousel' );
	if ( $display_mode === 'grid' ) {
		$container_classes[] = 'testimonials-grid';
	}
	if ( ! empty( $attributes['fullWidth'] ) ) {
		$container_classes[] = 'fullwidth';
	}
	if ( ! empty( $attributes['hasBackground'] ) ) {
		$container_classes[] = 'has-background';
	}
	$items_per_slide = ! empty( $attributes['itemsPerSlide'] ) ? intval( $attributes['itemsPerSlide'] ) : 1;
	if ( $display_mode === 'slider' ) {
		$container_classes[] = 'items-' . $items_per_slide;
	}

	$container_style = '';
	if ( ! empty( $attributes['hasBackground'] ) && ! empty( $attributes['backgroundColor'] ) ) {
		$container_style = 'background-color: ' . esc_attr( $attributes['backgroundColor'] ) . ';';
	}

	$text_align = ! empty( $attributes['textAlign'] ) ? $attributes['textAlign'] : 'left';
	$show_arrows = ! empty( $attributes['showArrows'] ) ? 'true' : 'false';
	$show_dots = ! empty( $attributes['showDots'] ) ? 'true' : 'false';
	$show_rating = ! empty( $attributes['showRating'] );
	$show_image = ! empty( $attributes['showImage'] );

	$carousel_id = 'testimonials-carousel-' . uniqid();

	$output = '<div class="' . esc_attr( implode( ' ', $container_classes ) ) . '"';
	$output .= ' id="' . esc_attr( $carousel_id ) . '"';
	if ( $display_mode === 'slider' ) {
		$output .= ' data-items="' . esc_attr( $items_per_slide ) . '"';
		$output .= ' data-arrows="' . esc_attr( $show_arrows ) . '"';
		$output .= ' data-dots="' . esc_attr( $show_dots ) . '"';
	}
	$output .= ' data-display-mode="' . esc_attr( $display_mode ) . '"';
	if ( $container_style ) {
		$output .= ' style="' . esc_attr( $container_style ) . '"';
	}
	$output .= '>';

	$track_class = $display_mode === 'grid' ? 'testimonials-grid-container' : 'testimonials-carousel-track';
	$output .= '<div class="' . esc_attr( $track_class ) . '" style="text-align: ' . esc_attr( $text_align ) . ';">';

	// For grid mode, show maximum 4 items (1 row)
	$testimonials_to_show = $processed_testimonials;
	if ( $display_mode === 'grid' ) {
		// Always show maximum 4 items for grid mode (1 row)
		$testimonials_to_show = array_slice( $processed_testimonials, 0, 4 );
	}

	foreach ( $testimonials_to_show as $testimonial ) {
		$testimonial_text = ! empty( $testimonial['testimonialText'] ) ? $testimonial['testimonialText'] : '';
		$author_name = ! empty( $testimonial['authorName'] ) ? $testimonial['authorName'] : '';
		$author_position = ! empty( $testimonial['authorPosition'] ) ? $testimonial['authorPosition'] : '';
		$author_company = ! empty( $testimonial['authorCompany'] ) ? $testimonial['authorCompany'] : '';
		$rating = ! empty( $testimonial['rating'] ) ? intval( $testimonial['rating'] ) : 5;
		$image_url = ! empty( $testimonial['authorImageUrl'] ) ? $testimonial['authorImageUrl'] : '';

		// Skip empty testimonials in grid mode
		if ( $display_mode === 'grid' && empty( $testimonial_text ) ) {
			continue;
		}

		// Add class if no image (text-only testimonial)
		$testimonial_item_class = 'testimonial-item';
		if ( ! $show_image || empty( $image_url ) ) {
			$testimonial_item_class .= ' testimonial-text-only';
		}

		$output .= '<div class="' . esc_attr( $testimonial_item_class ) . '">';

		// Rating stars
		if ( $show_rating ) {
			$output .= '<div class="testimonial-rating" data-rating="' . esc_attr( $rating ) . '">';
			for ( $i = 1; $i <= 5; $i++ ) {
				$output .= '<span class="star' . ( $i <= $rating ? ' filled' : '' ) . '">★</span>';
			}
			$output .= '</div>';
		}

		// Testimonial content
		$output .= '<blockquote class="testimonial-content">';
		$output .= '<div class="testimonial-text-wrapper">';
		$output .= wp_kses_post( wpautop( $testimonial_text ) );
		$output .= '</div>';
		$output .= '</blockquote>';

		// Author section
		$output .= '<div class="testimonial-author">';

		// Author image
		if ( $show_image && $image_url ) {
			$output .= '<img src="' . esc_url( $image_url ) . '"';
			$output .= ' alt="' . esc_attr( $author_name ? $author_name : 'Author' ) . '"';
			$output .= ' class="testimonial-author-image"';
			$output .= ' />';
		}

		// Author info
		$output .= '<div class="testimonial-author-info">';
		if ( $author_name ) {
			$output .= '<cite class="testimonial-author-name">' . esc_html( $author_name ) . '</cite>';
		}
		if ( $author_position || $author_company ) {
			$meta_parts = array();
			if ( $author_position ) {
				$meta_parts[] = esc_html( $author_position );
			}
			if ( $author_company ) {
				$meta_parts[] = esc_html( $author_company );
			}
			$output .= '<span class="testimonial-author-meta">' . implode( ', ', $meta_parts ) . '</span>';
		}
		$output .= '</div>'; // .testimonial-author-info

		$output .= '</div>'; // .testimonial-author
		$output .= '</div>'; // .testimonial-item
	}

	$output .= '</div>'; // .testimonials-carousel-track or .testimonials-grid-container
	$output .= '</div>'; // .unhotel-testimonials-carousel

	return $output;
}

