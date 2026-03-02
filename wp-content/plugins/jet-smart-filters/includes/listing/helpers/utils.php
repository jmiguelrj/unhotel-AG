<?php

namespace Jet_Smart_Filters\Listing\Helpers;

use Jet_Smart_Filters\Listing\Controller as Listing_Controller;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Utils {

	protected $listing_instance;

	public function __construct() {

		$this->listing_instance = Listing_Controller::instance();
	}

	public function prepare_query_args( $raw_query ) {

		$args = $raw_query;

		// Authors
		if ( !empty( $raw_query['post_authors'] ) ) {
			$args['author__in'] = array_map( function( $author ) {
				return intval( $author['value'] );
			}, $raw_query['post_authors']);
		}

		// Posts per page
		if (isset($raw_query['posts_per_page'])) {
			$args['posts_per_page'] = intval($raw_query['posts_per_page']);
		}

		// Offset
		if ( isset($raw_query['offset'] ) ) {
			$args['offset'] = intval( $raw_query['offset'] );
		}

		// Include only these posts
		if ( !empty( $raw_query['post__in'] ) ) {
			$args['post__in'] = array_map( function( $post ) {
				return intval( $post['value'] );
			}, $raw_query['post__in']);
		}

		// Exclude these posts
		if ( !empty( $raw_query['post__not_in'] ) ) {
			$args['post__not_in'] = array_map( function( $post ) {
				return intval( $post['value'] );
			}, $raw_query['post__not_in']);
		}

		// Ignore sticky posts
		if ( isset( $raw_query['ignore_sticky_posts'] ) ) {
			$args['ignore_sticky_posts'] = (bool) $raw_query['ignore_sticky_posts'];
		}

		// Sorting
		if ( !empty( $raw_query['sort'] ) ) {
			if ( !empty( $raw_query['sort']['orderby'] ) ) {
				$args['orderby'] = $raw_query['sort']['orderby'];
			}
			if ( !empty($raw_query['sort']['order'] ) ) {
				$args['order'] = strtoupper( $raw_query['sort']['order'] );
			}
		}

		return $args;
	}

	public function get_object_field_value( $field_key, $object ) {

		if ( ! $object || ! is_object( $object ) ) {
			return '';
		}

		$object_fields = $this->listing_instance->helpers->blocks_options->get_object_fields();

		switch ( get_class( $object ) ) {
			case 'WP_Post':
				if ( isset( $object_fields['post']['options'][$field_key] ) ) {
					switch ( $field_key ) {
						case 'post_id':
							return $object->ID;
						default:
							return isset( $object->$field_key ) ? $object->$field_key : '';
					}
				}
				break;

			case 'WP_Term':
				if ( isset( $object_fields['term']['options'][$field_key] ) ) {
					return isset( $object->$field_key ) ? $object->$field_key : '';
				}
				break;

			case 'WP_User':
				if ( isset( $object_fields['user']['options'][$field_key] ) ) {
					return isset( $object->$field_key ) ? $object->$field_key : '';
				}
				break;

			case 'WP_Comment':
				if ( isset( $object_fields['comment']['options'][$field_key] ) ) {
					return isset( $object->$field_key ) ? $object->$field_key : '';
				}

			default:
				return '';
		}
	}

	function get_meta_value( $meta_key, $object, $single = true ) {

		if ( empty( $meta_key ) || ! is_object( $object ) ) {
			return null;
		}

		switch ( get_class( $object ) ) {
			case 'WP_Post':
				return get_post_meta( $object->ID, $meta_key, $single );

			case 'WP_Term':
				return get_term_meta( $object->term_id, $meta_key, $single );

			case 'WP_User':
				return get_user_meta( $object->ID, $meta_key, $single );

			case 'WP_Comment':
				return get_comment_meta( $object->comment_ID, $meta_key, $single );

			default:
				return '';
		}
	}

	public function apply_filter_callback( $callback_key, $value, $settings = [] ) {

		switch ( $callback_key ) {
			case 'date':
				$date_format = ! empty( $settings['date_format'] ) ? $settings['date_format'] : 'F j, Y';

				return date( $date_format, strtotime( $value ) );

			case 'date_i18n':
				return date_i18n( get_option( 'date_format' ), strtotime( $value ) );

			case 'number_format':
				$decimal_count       = ! empty( $settings['decimal_count'] ) ? $settings['decimal_count'] : 0;
				$decimal_point       = ! empty( $settings['decimal_point'] ) ? $settings['decimal_point'] : '.';
				$thousands_separator = ! empty( $settings['thousands_separator'] ) ? $settings['thousands_separator'] : '';

				return is_numeric( $value ) ? number_format( $value, $decimal_count, $decimal_point, $thousands_separator ) : $value;

			case 'get_the_title':
				$post = get_post( $value );

				return $post ? get_the_title( $post ) : $value;

			case 'get_permalink':
				$post = get_post( $value );

				return $post ? get_permalink( $post ) : $value;

			case 'get_term_link':
				$term_link = get_term_link( (int) $value );

				return is_wp_error( $term_link ) ? $value : $term_link;

			case 'wp_oembed_get':
				$embed = wp_oembed_get( $value );

				return $embed ? $embed : $value;

			case 'make_clickable':
				return make_clickable( $value );

			case 'wp_get_attachment_image':
				return wp_get_attachment_image( $value, 'full' );

			case 'do_shortcode':
				return do_shortcode( $value );

			case 'human_time_diff':
				$time = strtotime( $value );

				if ( ! $time ) {
					return $value;
				}

				return human_time_diff( $time, current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'jet-smart-filters' );

			case 'wpautop':
				return wpautop( $value );

			case 'zeroise':
				return zeroise( $value, 2 ); // например, добавим ведущий 0 до 2 цифр: "04"

			default:
				return $value;
		}
	}

	public function get_object_image( $object, $settings = [] ) {

		$image_url = '';

		// default settings
		$settings = array_merge( [
			'class' => '',
			'size'  => '',
			'alt'   => ''
		], $settings );

		switch ( get_class( $object ) ) {
			case 'WP_Post':
				$attr = [];

				if ( ! empty( $settings['class'] ) ) {
					$attr['class'] = $settings['class'];
				}

				if ( ! empty( $settings['alt'] ) ) {
					$attr['alt'] = $settings['alt'];
				}

				$image_url = get_the_post_thumbnail(
					$object->ID,
					$settings['size'],
					$attr
				);

				break;

			case 'WP_User':

				break;
		}

		return $image_url;
	}

	function get_post_terms_list( $object, $taxonomy, $args = [] ) {

		 if ( ! isset( $object ) || ! is_object( $object ) ) {
			return [];
		}

		$defaults_args = [
			'orderby' => 'name',
			'order'   => 'ASC',
		];

		$terms = wp_get_post_terms( $object->ID, $taxonomy, wp_parse_args( $args, $defaults_args ) );

		if ( is_wp_error( $terms ) ) {
			return [];
		}

		if ( ! empty( $args['limit'] ) ) {
			$terms = array_slice( $terms, 0, $args['limit'] );
		}

		return $terms;
	}
}