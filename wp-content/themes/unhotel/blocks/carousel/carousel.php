<?php
/**
 * Carousel Block Render Callback
 * 
 * @package Unhotel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render Carousel Block
 */
function unhotel_render_carousel_block( $attributes ) {
	// Extract attributes with defaults
	$fullwidth        = ! empty( $attributes['fullwidth'] ) ? 'fullwidth' : '';
	$display_mode     = ! empty( $attributes['displayMode'] ) ? $attributes['displayMode'] : 'carousel';
	$items_to_show    = ! empty( $attributes['itemsToShow'] ) ? intval( $attributes['itemsToShow'] ) : 3;
	$items_to_show_mobile = ! empty( $attributes['itemsToShowMobile'] ) ? intval( $attributes['itemsToShowMobile'] ) : 1;
	$items_to_show_tablet = ! empty( $attributes['itemsToShowTablet'] ) ? intval( $attributes['itemsToShowTablet'] ) : 2;
	$show_arrows      = isset( $attributes['showArrows'] ) ? (bool) $attributes['showArrows'] : true;
	$show_dots        = isset( $attributes['showDots'] ) ? (bool) $attributes['showDots'] : true;
	$source           = ! empty( $attributes['source'] ) ? $attributes['source'] : 'images';
	$has_background   = ! empty( $attributes['hasBackground'] ) ? true : false;
	$background_color = ! empty( $attributes['backgroundColor'] ) ? $attributes['backgroundColor'] : '';
	$posts_per_page   = ! empty( $attributes['postsPerPage'] ) ? intval( $attributes['postsPerPage'] ) : 6;
	$posts_category   = ! empty( $attributes['postsCategory'] ) ? $attributes['postsCategory'] : '';

	// Generate unique ID for this carousel instance
	$carousel_id = 'carousel-' . wp_generate_uuid4();

	// Build container classes
	$container_classes = array( 'unhotel-carousel' );
	if ( $fullwidth ) {
		$container_classes[] = 'fullwidth';
	}
	if ( $display_mode === 'grid' ) {
		$container_classes[] = 'grid-mode';
	} else {
		$container_classes[] = 'carousel-mode';
	}
	if ( $has_background ) {
		$container_classes[] = 'has-background';
	}

	// Build container styles
	$container_styles = array();
	if ( $has_background && $background_color ) {
		$container_styles[] = 'background-color: ' . esc_attr( $background_color );
	}

	// Get items based on source
	$items = array();
	
	if ( $source === 'images' ) {
		$gallery_images = ! empty( $attributes['galleryImages'] ) ? $attributes['galleryImages'] : array();
		if ( ! empty( $gallery_images ) && is_array( $gallery_images ) ) {
			foreach ( $gallery_images as $image_item ) {
				$image_id  = 0;
				$image_url = '';
				$image_alt = '';
				if ( is_array( $image_item ) ) {
					$image_id  = ! empty( $image_item['id'] ) ? intval( $image_item['id'] ) : 0;
					$image_url = ! empty( $image_item['url'] ) ? $image_item['url'] : '';
					$image_alt = ! empty( $image_item['alt'] ) ? $image_item['alt'] : '';
				} else {
					$image_id = intval( $image_item );
				}
				// Always fallback to attachment URL if missing
				if ( empty( $image_url ) && $image_id ) {
					$image_url = wp_get_attachment_image_url( $image_id, 'medium' );
					$image_alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
				}
				// Render image or placeholder
				if ( $image_url ) {
					$items[] = sprintf(
						'<div class="carousel-item carousel-image" style="background-image: url(\'%s\');" title="%s"></div>',
						esc_url( $image_url ),
						esc_attr( $image_alt )
					);
				} else {
					$items[] = sprintf(
						'<div class="carousel-item carousel-image"><div class="carousel-image-placeholder">%s</div></div>',
						esc_html__( 'Image missing', 'unhotel' )
					);
				}
			}
		} else {
			// Check for attached images or gallery in post content
			global $post;
			if ($post) {
				$attachments = get_attached_media( 'image', $post->ID );
				if ( ! empty( $attachments ) ) {
					$count = 0;
					foreach ( $attachments as $attachment ) {

						if ( $count >= $items_to_show ) {
							break;
						}
						$image_url = wp_get_attachment_image_url( $attachment->ID, 'medium' );
						$image_alt = get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true );
						
						$items[] = sprintf(
							'<div class="carousel-item carousel-image">
								<img src="%s" alt="%s" class="carousel-img" />
							</div>',
							esc_url( $image_url ),
							esc_attr( $image_alt )
						);
						$count++;
					}
				}
			}
			
			// If still no images, show placeholder
			if ( empty( $items ) ) {
				for ( $i = 0; $i < $items_to_show; $i++ ) {
					$items[] = sprintf(
						'<div class="carousel-item carousel-image">
							<div class="carousel-image-placeholder">%s</div>
						</div>',
						sprintf( esc_html__( 'Image %d - Add images via Media Library', 'unhotel' ), ( $i + 1 ) )
					);
				}
			}
		}
	} elseif ( $source === 'testimonials' ) {
		$manual_testimonials = ! empty( $attributes['testimonials'] ) ? $attributes['testimonials'] : array();

		if ( ! empty( $manual_testimonials ) && is_array( $manual_testimonials ) ) {
			foreach ( $manual_testimonials as $testimonial ) {
				if ( empty( $testimonial['text'] ) ) {
					continue;
				}

				$author_name    = ! empty( $testimonial['author'] ) ? $testimonial['author'] : '';
				$author_role    = ! empty( $testimonial['role'] ) ? $testimonial['role'] : '';
				$author_company = ! empty( $testimonial['company'] ) ? $testimonial['company'] : '';
				$rating         = ! empty( $testimonial['rating'] ) ? intval( $testimonial['rating'] ) : 5;
				$image_url      = ! empty( $testimonial['imageUrl'] ) ? $testimonial['imageUrl'] : '';
				$image_id       = ! empty( $testimonial['imageId'] ) ? intval( $testimonial['imageId'] ) : 0;

				if ( ! $image_url && $image_id ) {
					$image_url = wp_get_attachment_image_url( $image_id, 'thumbnail' );
				}

				$rating_html = '';
				if ( $rating ) {
					$rating_html = '<div class="testimonial-rating" data-rating="' . esc_attr( $rating ) . '">';
					for ( $i = 1; $i <= 5; $i++ ) {
						$rating_html .= '<span class="star ' . ( $i <= $rating ? 'filled' : '' ) . '">★</span>';
					}
					$rating_html .= '</div>';
				}

				$author_meta = '';
				if ( $author_role || $author_company ) {
					$parts = array();
					if ( $author_role ) {
						$parts[] = esc_html( $author_role );
					}
					if ( $author_company ) {
						$parts[] = esc_html( $author_company );
					}
					$author_meta = '<span class="testimonial-author-meta">' . implode( ', ', $parts ) . '</span>';
				}

				$author_image_html = '';
				if ( $image_url ) {
					$author_image_html = sprintf(
						'<img src="%s" alt="%s" class="testimonial-author-image" />',
						esc_url( $image_url ),
						esc_attr( $author_name ? $author_name : 'Author' )
					);
				}

				// Add class if no image (text-only testimonial)
				$testimonial_class = 'carousel-testimonial';
				if ( empty( $image_url ) ) {
					$testimonial_class .= ' testimonial-text-only';
				}
				
				$items[] = sprintf(
					'<div class="carousel-item %s">
						%s
						<blockquote class="testimonial-content">
							%s
						</blockquote>
						<div class="testimonial-author">
							%s
							<div class="testimonial-author-info">
								%s
								%s
							</div>
						</div>
					</div>',
					esc_attr( $testimonial_class ),
					$rating_html,
					wpautop( wp_kses_post( $testimonial['text'] ) ),
					$author_image_html,
					$author_name ? '<cite class="testimonial-author-name">' . esc_html( $author_name ) . '</cite>' : '',
					$author_meta
				);
			}
		}

		// Fallback: Query testimonials from custom post type or posts if no manual entries found
		if ( empty( $items ) ) {
			$testimonials_query = new WP_Query( array(
				'post_type'      => 'testimonial', // Check if custom post type exists
				'posts_per_page' => $items_to_show,
				'post_status'    => 'publish',
			) );

			// Fallback to regular posts if no testimonial post type
			if ( ! $testimonials_query->have_posts() ) {
				$testimonials_query = new WP_Query( array(
					'post_type'      => 'post',
					'posts_per_page' => $items_to_show,
					'category_name'  => 'testimonials', // If testimonials are in a category
					'post_status'    => 'publish',
				) );
			}

			if ( $testimonials_query->have_posts() ) {
				while ( $testimonials_query->have_posts() ) {
					$testimonials_query->the_post();
					$author = get_post_meta( get_the_ID(), '_testimonial_author', true );
					$company = get_post_meta( get_the_ID(), '_testimonial_company', true );
					$rating = get_post_meta( get_the_ID(), '_testimonial_rating', true );

					$testimonial_content = get_the_content();
					if ( empty( $testimonial_content ) ) {
						$testimonial_content = get_the_excerpt();
					}

					$author_html = '';
					if ( $author ) {
						$author_html = '<cite class="testimonial-author">';
						$author_html .= esc_html( $author );
						if ( $company ) {
							$author_html .= ', <span class="testimonial-company">' . esc_html( $company ) . '</span>';
						}
						$author_html .= '</cite>';
					}

					$rating_html = '';
					if ( $rating && is_numeric( $rating ) ) {
						$rating_html = '<div class="testimonial-rating" data-rating="' . esc_attr( $rating ) . '">';
						for ( $i = 0; $i < 5; $i++ ) {
							$rating_html .= '<span class="star ' . ( $i < $rating ? 'filled' : '' ) . '">★</span>';
						}
						$rating_html .= '</div>';
					}

					// Query-based testimonials are text-only (no images)
					$items[] = sprintf(
						'<div class="carousel-item carousel-testimonial testimonial-text-only">
							<blockquote class="testimonial-content">
								%s
								%s
								%s
							</blockquote>
						</div>',
						$rating_html,
						wpautop( wp_kses_post( $testimonial_content ) ),
						$author_html
					);
				}
				wp_reset_postdata();
			}
		}

		// If still no testimonials, show placeholder
		if ( empty( $items ) ) {
			for ( $i = 0; $i < $items_to_show; $i++ ) {
				$items[] = sprintf(
					'<div class="carousel-item carousel-testimonial testimonial-text-only">
						<blockquote class="testimonial-content">
							<p>%s</p>
							<cite class="testimonial-author">%s</cite>
						</blockquote>
					</div>',
					esc_html__( 'Add testimonials to this block to see them in the carousel.', 'unhotel' ),
					esc_html__( 'Author Name', 'unhotel' )
				);
			}
		}
	} elseif ( $source === 'posts' ) {
		// Query blog posts
		$args = array(
			'post_type'      => 'post',
			'posts_per_page' => $posts_per_page,
			'post_status'    => 'publish',
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		if ( ! empty( $posts_category ) ) {
			$args['category_name'] = $posts_category;
		}

		$posts_query = new WP_Query( $args );

			if ( $posts_query->have_posts() ) {
				while ( $posts_query->have_posts() ) {
					$posts_query->the_post();
					
					$thumbnail = '';
					if ( has_post_thumbnail() ) {
						$thumbnail = get_the_post_thumbnail( get_the_ID(), 'medium', array( 'class' => 'carousel-post-thumbnail' ) );
					}

					$excerpt = get_the_excerpt();
					if ( empty( $excerpt ) ) {
						$excerpt = wp_trim_words( get_the_content(), 20 );
					}
					
					// Get category
					$categories = get_the_category();
					$category_name = ! empty( $categories ) ? esc_html( $categories[0]->name ) : '';
					
					// Get author
					$author_name = get_the_author();
					$author_url = get_author_posts_url( get_the_author_meta( 'ID' ) );
					
					// Format date
					$post_date = get_the_date();
					$human_date = human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) . ' ' . esc_html__( 'ago', 'unhotel' );

					$items[] = sprintf(
						'<div class="carousel-item carousel-post">
							<a href="%s" class="carousel-post-link">
								%s
								<div class="carousel-post-content">
									<h3 class="carousel-post-title">%s</h3>
									%s
									<div class="carousel-post-excerpt">%s</div>
									<div class="carousel-post-meta">
										<span class="carousel-post-date">
											<svg class="carousel-post-date-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
												<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
												<line x1="16" y1="2" x2="16" y2="6"></line>
												<line x1="8" y1="2" x2="8" y2="6"></line>
												<line x1="3" y1="10" x2="21" y2="10"></line>
											</svg>
											%s
										</span>
										<span class="carousel-post-author">
											%s <a href="%s" class="carousel-post-author-link">%s</a>
										</span>
									</div>
								</div>
							</a>
						</div>',
						esc_url( get_permalink() ),
						$thumbnail,
						esc_html( get_the_title() ),
						$category_name ? '<span class="carousel-post-category">' . $category_name . '</span>' : '',
						wp_kses_post( $excerpt ),
						$human_date,
						esc_html__( 'De', 'unhotel' ),
						esc_url( $author_url ),
						esc_html( $author_name )
					);
				}
				wp_reset_postdata();
			}
	}

	// Build output
	$output = sprintf(
		'<div class="%s" id="%s" data-display-mode="%s" data-items="%s" data-arrows="%s" data-dots="%s" %s>',
		esc_attr( implode( ' ', $container_classes ) ),
		esc_attr( $carousel_id ),
		esc_attr( $display_mode ),
		esc_attr( $items_to_show ),
		$show_arrows ? 'true' : 'false',
		$show_dots ? 'true' : 'false',
		! empty( $container_styles ) ? 'style="' . esc_attr( implode( '; ', $container_styles ) ) . '"' : ''
	);

	// Count total items for data attributes
	$total_items = count( $items );
	
	if ( ! empty( $items ) ) {
		$output .= '<div class="carousel-track"';
		// Add data attributes
		if ( $display_mode === 'grid' ) {
			// For grid mode, add data-items to control columns
			$output .= ' data-items="' . esc_attr( $items_to_show ) . '"';
		} elseif ( $display_mode === 'carousel' ) {
			// For carousel mode, add items-to-show attributes for JavaScript
			$output .= ' data-items-to-show="' . esc_attr( $items_to_show ) . '"';
			$output .= ' data-items-to-show-mobile="' . esc_attr( $items_to_show_mobile ) . '"';
			$output .= ' data-items-to-show-tablet="' . esc_attr( $items_to_show_tablet ) . '"';
			$output .= ' data-total-items="' . esc_attr( $total_items ) . '"';
			$output .= ' data-show-arrows="' . ( $show_arrows ? 'true' : 'false' ) . '"';
			$output .= ' data-show-dots="' . ( $show_dots ? 'true' : 'false' ) . '"';
		}
		$output .= '>';
		$output .= implode( '', $items );
		$output .= '</div>';
	} else {
		$output .= '<div class="carousel-track"';
		if ( $display_mode === 'grid' ) {
			$output .= ' data-items="' . esc_attr( $items_to_show ) . '"';
		} elseif ( $display_mode === 'carousel' ) {
			$output .= ' data-items-to-show="' . esc_attr( $items_to_show ) . '"';
			$output .= ' data-items-to-show-mobile="' . esc_attr( $items_to_show_mobile ) . '"';
			$output .= ' data-items-to-show-tablet="' . esc_attr( $items_to_show_tablet ) . '"';
			$output .= ' data-total-items="0"';
			$output .= ' data-show-arrows="' . ( $show_arrows ? 'true' : 'false' ) . '"';
			$output .= ' data-show-dots="' . ( $show_dots ? 'true' : 'false' ) . '"';
		}
		$output .= '>';
		for ( $i = 0; $i < $items_to_show; $i++ ) {
			$output .= '<div class="carousel-item carousel-image"><div class="carousel-image-placeholder">' . esc_html__( 'Add items to this block to see them in the carousel.', 'unhotel' ) . '</div></div>';
		}
		$output .= '</div>';
	}

	$output .= '</div>';

	return $output;
}

