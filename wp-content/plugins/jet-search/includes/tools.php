<?php
/**
 * Jet_Search_Tools class
 *
 * @package   jet-search
 * @author    Zemez
 * @license   GPL-2.0+
 */
use Jet_Engine\Bricks_Views\Elements\Listing_Grid;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Search_Tools' ) ) {

	/**
	 * Define Jet_Search_Tools class
	 */
	class Jet_Search_Tools {

		/**
		 * Get public post types options list
		 *
		 * @return array
		 */
		public static function get_post_types() {
			$post_types = get_post_types( array( 'show_in_nav_menus' => true ), 'objects' );

			$result = array();

			if ( empty( $post_types ) ) {
				return $result;
			}

			foreach ( $post_types as $slug => $post_type ) {
				$result[ $slug ] = $post_type->label;
			}

			return apply_filters( 'jet-search/tools/get-post-types', $result );
		}

		/**
		 * Get public taxonomies options list
		 *
		 * @return array
		 */
		public static function get_taxonomies( $with_hidden = false, $include_custom_attributes = false ) {
			
			$args   = array();
			$result = array();

			if ( ! $with_hidden ) {
				$args['show_in_nav_menus'] = true;
			}

			$taxonomies = get_taxonomies( $args, 'objects' );

			if ( empty( $taxonomies ) ) {
				return $result;
			}

			foreach ( $taxonomies as $slug => $post_type ) {
				$result[ $slug ] = $post_type->label;
			}

			if ( $include_custom_attributes ) {
				/**
				 * Filters the list of taxonomies to allow adding custom attribute-like entries.
				 *
				 * @param array $result Taxonomies list before custom attributes.
				 *
				 * @return array Modified taxonomies list.
				 */
				$result = apply_filters( 'jet_search/extend_taxonomies_with_custom_attributes', $result );
			}

			return $result;
		}

		/**
		 * Returns image size array in slug => name format
		 *
		 * @return  array
		 */
		public static function get_image_sizes() {

			global $_wp_additional_image_sizes;

			$sizes  = get_intermediate_image_sizes();
			$result = array();

			foreach ( $sizes as $size ) {
				if ( in_array( $size, array( 'thumbnail', 'medium', 'medium_large', 'large' ) ) ) {
					$result[ $size ] = ucwords( trim( str_replace( array( '-', '_' ), array( ' ', ' ' ), $size ) ) );
				} else {
					$result[ $size ] = sprintf(
						'%1$s (%2$sx%3$s)',
						ucwords( trim( str_replace( array( '-', '_' ), array( ' ', ' ' ), $size ) ) ),
						$_wp_additional_image_sizes[ $size ]['width'],
						$_wp_additional_image_sizes[ $size ]['height']
					);
				}
			}

			return array_merge( array( 'full' => esc_html__( 'Full', 'jet-search' ), ), $result );
		}

		/**
		 * Return available prev arrows list
		 *
		 * @return array
		 */
		public static function get_available_prev_arrows_list() {
			return apply_filters(
				'jet-search/available-nav-arrows/prev',
				array(
					'angle'          => esc_html__( 'Angle', 'jet-search' ),
					'chevron'        => esc_html__( 'Chevron', 'jet-search' ),
					'angle-double'   => esc_html__( 'Angle Double', 'jet-search' ),
					'arrow'          => esc_html__( 'Arrow', 'jet-search' ),
					'caret'          => esc_html__( 'Caret', 'jet-search' ),
					'long-arrow'     => esc_html__( 'Long Arrow', 'jet-search' ),
					'arrow-circle'   => esc_html__( 'Arrow Circle', 'jet-search' ),
					'chevron-circle' => esc_html__( 'Chevron Circle', 'jet-search' ),
					'caret-square'   => esc_html__( 'Caret Square', 'jet-search' ),
				)
			);
		}

		public static function get_svg_arrows( $arrow_type ) {

			$arrow = [];

			switch( $arrow_type ){
				case 'angle':
					$arrow['left']  = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14.625 5.3999L16.3 7.0749L11.35 12.0249L16.3 16.9749L14.625 18.6499L7.99999 12.0249L14.625 5.3999Z" fill="#0F172A"/></svg>';
					$arrow['right'] = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9.37501 18.6001L7.70001 16.9251L12.65 11.9751L7.70001 7.0251L9.37501 5.3501L16 11.9751L9.37501 18.6001Z" fill="#0F172A"/></svg>';
					break;
				case 'chevron':
					$arrow['left']  = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16.4 1.4499L18.4 3.4749L9.82502 12.0499L18.4 20.6249L16.4 22.6499L5.80002 12.0499L16.4 1.4499Z" fill="#0F172A"/></svg>';
					$arrow['right'] = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.59998 22.5501L5.59998 20.5251L14.175 11.9501L5.59998 3.3751L7.59998 1.3501L18.2 11.9501L7.59998 22.5501Z" fill="#0F172A"/></svg>';
					break;
				case 'angle-double':
					$arrow['left']  = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11.925 4.5748L6.39999 11.9998L11.925 19.4248L9.02499 19.4248L3.47499 11.9998L9.02499 4.5748L11.925 4.5748ZM19.3 4.57481L13.775 11.9998L19.3 19.4248L16.375 19.4248L10.85 11.9998L16.375 4.57481L19.3 4.57481Z" fill="#0F172A"/></svg>';
					$arrow['right'] = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12.075 19.4252L17.6 12.0002L12.075 4.5752H14.975L20.525 12.0002L14.975 19.4252H12.075ZM4.70001 19.4252L10.225 12.0002L4.70001 4.5752H7.62501L13.15 12.0002L7.62501 19.4252H4.70001Z" fill="#0F172A"/></svg>';
					break;
				case 'arrow':
					$arrow['left']  = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 3.7249L13.675 5.4249L8.25 10.8249L20.275 10.8249L20.275 13.1749L8.24999 13.1749L13.675 18.5999L12 20.2749L3.725 11.9999L12 3.7249Z" fill="#0F172A"/></svg>';
					$arrow['right'] = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 20.2751L10.325 18.5751L15.75 13.1751H3.72501V10.8251H15.75L10.325 5.4001L12 3.7251L20.275 12.0001L12 20.2751Z" fill="#0F172A"/></svg>';
					break;
				case 'caret':
					$arrow['left']  = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14.425 5.9749L14.425 18.0249L8.39999 11.9999L14.425 5.9749Z" fill="#0F172A"/></svg>';
					$arrow['right'] = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9.57501 18.0251V5.9751L15.6 12.0001L9.57501 18.0251Z" fill="#0F172A"/></svg>';
					break;
				case 'long-arrow':
					$arrow['left']  = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.72499 6.62519L8.42499 8.30019L5.92499 10.8002L21.425 10.8002L21.425 13.1502L5.89999 13.1502L8.42499 15.6502L6.74999 17.3252L1.39999 11.9752L6.72499 6.62519Z" fill="#0F172A"/></svg>';
					$arrow['right'] = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M17.275 17.3748L15.575 15.6998L18.075 13.1998H2.57501V10.8498H18.1L15.575 8.3498L17.25 6.6748L22.6 12.0248L17.275 17.3748Z" fill="#0F172A"/></svg>';
					break;
				case 'arrow-circle':
					$arrow['left']  = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 8.35L8.35 12L12 15.65L13.05 14.6L11.2 12.75L15.75 12.75L15.75 11.25L11.2 11.25L13.05 9.4L12 8.35ZM12 2C13.4167 2 14.7333 2.25417 15.95 2.7625C17.1667 3.27083 18.225 3.975 19.125 4.875C20.025 5.775 20.7292 6.83333 21.2375 8.05C21.7458 9.26667 22 10.5833 22 12C22 13.4 21.7458 14.7083 21.2375 15.925C20.7292 17.1417 20.025 18.2 19.125 19.1C18.225 20 17.1667 20.7083 15.95 21.225C14.7333 21.7417 13.4167 22 12 22C10.6 22 9.29167 21.7417 8.075 21.225C6.85833 20.7083 5.8 20 4.9 19.1C4 18.2 3.29167 17.1417 2.775 15.925C2.25833 14.7083 2 13.4 2 12C2 10.5833 2.25833 9.26666 2.775 8.05C3.29167 6.83333 4 5.775 4.9 4.875C5.8 3.975 6.85834 3.27083 8.075 2.7625C9.29167 2.25417 10.6 2 12 2Z" fill="#0F172A"/></svg>';
					$arrow['right'] = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 15.65L15.65 12L12 8.35L10.95 9.4L12.8 11.25H8.25V12.75H12.8L10.95 14.6L12 15.65ZM12 22C10.5833 22 9.26667 21.7458 8.05 21.2375C6.83333 20.7292 5.775 20.025 4.875 19.125C3.975 18.225 3.27083 17.1667 2.7625 15.95C2.25417 14.7333 2 13.4167 2 12C2 10.6 2.25417 9.29167 2.7625 8.075C3.27083 6.85833 3.975 5.8 4.875 4.9C5.775 4 6.83333 3.29167 8.05 2.775C9.26667 2.25833 10.5833 2 12 2C13.4 2 14.7083 2.25833 15.925 2.775C17.1417 3.29167 18.2 4 19.1 4.9C20 5.8 20.7083 6.85833 21.225 8.075C21.7417 9.29167 22 10.6 22 12C22 13.4167 21.7417 14.7333 21.225 15.95C20.7083 17.1667 20 18.225 19.1 19.125C18.2 20.025 17.1417 20.7292 15.925 21.2375C14.7083 21.7458 13.4 22 12 22Z" fill="#0F172A"/></svg>';
					break;
				case 'chevron-circle':
					$arrow['left']  = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8.25 11.9751L13.075 16.8251L14.625 15.2251L11.375 11.9751L14.625 8.7251L13.075 7.1251L8.25 11.9751ZM1.35 12.0001C1.35 10.4834 1.62083 9.0751 2.1625 7.7751C2.70417 6.4751 3.45 5.3501 4.4 4.4001C5.35 3.4501 6.475 2.70426 7.775 2.1626C9.075 1.62093 10.4833 1.3501 12 1.3501C13.4833 1.3501 14.8708 1.62093 16.1625 2.1626C17.4542 2.70426 18.5792 3.4501 19.5375 4.4001C20.4958 5.3501 21.25 6.4751 21.8 7.7751C22.35 9.0751 22.625 10.4834 22.625 12.0001C22.625 13.4834 22.35 14.8709 21.8 16.1626C21.25 17.4543 20.4958 18.5793 19.5375 19.5376C18.5792 20.4959 17.4542 21.2501 16.1625 21.8001C14.8708 22.3501 13.475 22.6251 11.975 22.6251C10.475 22.6251 9.075 22.3501 7.775 21.8001C6.475 21.2501 5.35 20.4959 4.4 19.5376C3.45 18.5793 2.70417 17.4543 2.1625 16.1626C1.62083 14.8709 1.35 13.4834 1.35 12.0001Z" fill="#0F172A"/></svg>';
					$arrow['right'] = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15.725 12L10.9 7.15L9.35001 8.75L12.6 12L9.35001 15.25L10.9 16.85L15.725 12ZM22.625 11.975C22.625 13.4917 22.3542 14.9 21.8125 16.2C21.2708 17.5 20.525 18.625 19.575 19.575C18.625 20.525 17.5 21.2708 16.2 21.8125C14.9 22.3542 13.4917 22.625 11.975 22.625C10.4917 22.625 9.10417 22.3542 7.81251 21.8125C6.52084 21.2708 5.39584 20.525 4.43751 19.575C3.47917 18.625 2.72501 17.5 2.17501 16.2C1.62501 14.9 1.35001 13.4917 1.35001 11.975C1.35001 10.4917 1.62501 9.10417 2.17501 7.8125C2.72501 6.52083 3.47917 5.39583 4.43751 4.4375C5.39584 3.47917 6.52084 2.725 7.81251 2.175C9.10417 1.625 10.5 1.35 12 1.35C13.5 1.35 14.9 1.625 16.2 2.175C17.5 2.725 18.625 3.47917 19.575 4.4375C20.525 5.39583 21.2708 6.52083 21.8125 7.8125C22.3542 9.10417 22.625 10.4917 22.625 11.975Z" fill="#0F172A"/></svg>';
					break;
				case 'caret-square':
					$arrow['left']  = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M20 22C21.1046 22 22 21.1046 22 20L22 4C22 2.89543 21.1046 2 20 2L4 2C2.89543 2 2 2.89543 2 4L2 20C2 21.1046 2.89543 22 4 22L20 22ZM8.4 11.975L14.425 18L14.425 5.95L8.4 11.975Z" fill="#0F172A"/></svg>';
					$arrow['right'] = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M4 2C2.89543 2 2 2.89543 2 4V20C2 21.1046 2.89543 22 4 22H20C21.1046 22 22 21.1046 22 20V4C22 2.89543 21.1046 2 20 2H4ZM15.6 12.025L9.575 6V18.05L15.6 12.025Z" fill="#0F172A"/></svg>';
					break;
			}

			$arrow = apply_filters( 'jet-search/nav-arrow', $arrow, $arrow_type );

			return $arrow;
		}

		/**
		 * Check if is valid timestamp
		 *
		 * @param  int|string $timestamp
		 * @return boolean
		 */
		public static function is_valid_timestamp( $timestamp ) {
			return ( ( string ) ( int ) $timestamp === $timestamp ) && ( $timestamp <= PHP_INT_MAX ) && ( $timestamp >= ~PHP_INT_MAX );
		}

		public static function allowed_meta_callbacks() {
			$callbacks = apply_filters( 'jet-search/ajax-search/meta_callbacks', array(
				''                        => esc_html__( 'Clean', 'jet-search' ),
				'date'                    => esc_html__( 'Format date', 'jet-search' ),
				'date_i18n'               => esc_html__( 'Format date (localized)', 'jet-search' ),
				'get_the_title'           => 'get_the_title',
				'wp_get_attachment_url'   => 'wp_get_attachment_url',
				'wp_get_attachment_image' => 'wp_get_attachment_image',
			) );

			return $callbacks;
		}

		public static function get_allowed_html_tags() {
			$allowed_tags = array(
				'a'      => array(
					'href'  => array(),
					'title' => array()
				),
				'p'      => array(
					'style' => array()
				),
				'br'     => array(),
				'em'     => array(),
				'strong' => array(),
			);
			return apply_filters( 'jet-search/ajax-search/notifications/allowed_html_tags', $allowed_tags );
		}

		public static function get_placeholder_image_src() {
			$placeholder = trailingslashit( plugin_dir_url( 'jet-search' ) ) . 'jet-search/assets/images/placeholder.png';
			return $placeholder;
		}

		public static function prepared_default_search_query_settings( $settings, $widget_current_query = '' ) {

			$settings = self::prepared_settings($settings);

			if ( '' === $settings['show_search_category_list'] ) {
				unset( $settings['search_taxonomy'] );
				unset( $settings['show_search_category_list'] );
			}

			unset( $settings['show_search_category_list'] );

			if ( true === $settings['current_query'] || 'yes' === $widget_current_query || true === $widget_current_query ) {
				unset( $settings['search_source'] );
				unset( $settings['include_terms_ids'] );
				unset( $settings['exclude_terms_ids'] );
				unset( $settings['exclude_posts_ids'] );
			}

			if ( empty( $settings['search_source'] ) ) {
				$settings['search_source'] = 'any';
			}

			return $settings;
		}

		public static function prepared_settings( $settings ) {

			if ( isset( $settings['show_search_category_list'] ) ) {
				$settings['show_search_category_list'] = 'false' === $settings['show_search_category_list'] || '' === $settings['show_search_category_list'] || false === $settings['show_search_category_list'] ? false : true;
			}

			if ( isset( $settings['current_query'] ) ) {
				$settings['current_query'] = 'false' === $settings['current_query'] || '' === $settings['current_query'] || false === $settings['current_query'] ? false : true;
			}

			if ( isset( $settings['sentence'] ) ) {
				$settings['sentence'] = 'false' === $settings['sentence'] || '' === $settings['sentence'] || false === $settings['sentence'] ? false : true;
			}

			if ( isset( $settings['search_in_taxonomy'] ) ) {
				$settings['search_in_taxonomy'] = 'false' === $settings['search_in_taxonomy'] || '' === $settings['search_in_taxonomy'] || false === $settings['search_in_taxonomy'] ? false : true;

			}

			if ( isset( $settings['catalog_visibility'] ) ) {
				$settings['catalog_visibility'] = 'false' === $settings['catalog_visibility'] || '' === $settings['catalog_visibility'] || false === $settings['catalog_visibility'] ? false : true;
			}

			return $settings;
		}

		public static function prepared_settings_for_elementor( $settings ) {

			$settings['show_search_category_list'] = 'false' === $settings['show_search_category_list'] ? '' : 'yes';
			$settings['current_query']             = 'false' === $settings['current_query'] ? '' : 'yes';
			$settings['sentence']                  = 'false' === $settings['sentence'] ? '' : 'yes';
			$settings['search_in_taxonomy']        = 'false' === $settings['search_in_taxonomy'] ? '' : 'yes';
			$settings['catalog_visibility']        = 'false' === $settings['catalog_visibility'] ? '' : 'yes';

			return $settings;
		}

		public static function prepared_settings_for_bricks( $settings ) {
			$settings['show_search_category_list'] = 'false' === $settings['show_search_category_list'] ? false : true;
			$settings['current_query']             = 'false' === $settings['current_query'] ? false : true;
			$settings['sentence']                  = 'false' === $settings['sentence'] ? false : true;
			$settings['search_in_taxonomy']        = 'false' === $settings['search_in_taxonomy'] ? false : true;
			$settings['catalog_visibility']        = 'false' === $settings['catalog_visibility'] ? false : true;

			return $settings;
		}

		public static function prepared_settings_for_blocks( $settings ) {
			$settings['show_search_category_list'] = 'false' === $settings['show_search_category_list']
				? array(
					'type'    => 'boolean',
					'default' => false,
				)
				: array(
					'type'    => 'boolean',
					'default' => true,
				);

			$settings['search_taxonomy'] = isset( $settings['search_taxonomy'] )
				? array(
					'type'    => 'string',
					'default' => $settings['search_taxonomy'],
				)
				: array(
					'type'    => 'string',
					'default' => 'category',
				);

			$settings['current_query'] = 'false' === $settings['current_query']
				? array(
					'type'    => 'boolean',
					'default' => false,
				)
				: array(
					'type'    => 'boolean',
					'default' => true,
				);

			$settings['search_results_url'] = isset( $settings['search_results_url'] )
				? array(
					'type'    => 'string',
					'default' => $settings['search_results_url'],
				)
				: array(
					'type'    => 'string',
					'default' => '',
				);

			$settings['search_results_target_widget_id'] = isset( $settings['search_results_target_widget_id'] )
				? array(
					'type'    => 'string',
					'default' => $settings['search_results_target_widget_id'],
				)
				: array(
					'type'    => 'string',
					'default' => '',
				);

			if ( isset( $settings['search_source'] ) ) {
				self::set_default_settings_list( 'search_source', 'search_source_list', 'post_types', $settings );
			}

			if ( isset( $settings['include_terms_ids'] ) ) {
				self::set_default_settings_list( 'include_terms_ids', 'include_terms_ids_list', 'terms', $settings );
			}

			if ( isset( $settings['exclude_terms_ids'] ) ) {
				self::set_default_settings_list( 'exclude_terms_ids', 'exclude_terms_ids_list', 'terms', $settings );
			}

			if ( isset( $settings['exclude_posts_ids'] ) ) {
				self::set_default_settings_list( 'exclude_posts_ids', 'exclude_posts_ids_list', 'posts', $settings );
			}

			$settings['custom_fields_source'] = isset( $settings['custom_fields_source'] )
				? array(
					'type'    => 'string',
					'default' => $settings['custom_fields_source'],
				)
				: array(
					'type'    => 'string',
					'default' => '',
				);

			$settings['sentence'] = 'false' === $settings['sentence']
				? array(
					'type'    => 'boolean',
					'default' => false,
				)
				: array(
					'type'    => 'boolean',
					'default' => true,
				);

			$settings['search_in_taxonomy'] = 'false' === $settings['search_in_taxonomy']
				? array(
					'type'    => 'boolean',
					'default' => false,
				)
				: array(
					'type'    => 'boolean',
					'default' => true,
				);

			if ( isset( $settings['search_in_taxonomy_source'] ) ) {
				self::set_default_settings_list( 'search_in_taxonomy_source', 'search_in_taxonomy_source_list', 'taxonomies', $settings );
			}

			$settings['results_order_by'] = isset( $settings['results_order_by'] )
				? array(
					'type'    => 'string',
					'default' => $settings['results_order_by'],
				)
				: array(
					'type'    => 'string',
					'default' => '',
				);

			$settings['results_order'] = isset( $settings['results_order'] )
				? array(
					'type'    => 'string',
					'default' => $settings['results_order'],
				)
				: array(
					'type'    => 'string',
					'default' => 'asc',
				);

			$settings['catalog_visibility'] = 'false' === $settings['catalog_visibility']
				? array(
					'type'    => 'boolean',
					'default' => false,
				)
				: array(
					'type'    => 'boolean',
					'default' => true,
				);

			return $settings;
		}

		public static function set_default_settings_list( $setting, $setting_list, $list_type, &$settings ) {

			$list = array();

			switch ( $list_type ) {
				case 'post_types':
					$list = jet_search_blocks_integration()->get_post_types_list();
					break;
				case 'taxonomies':
					$list = jet_search_blocks_integration()->get_taxonomies_list( true );
					break;
				case 'terms':
					$ids = $settings[$setting];

					if ( ! empty( $ids ) ) {
						$terms_args = array(
							'hide_empty' => false,
						);
						$terms_args['include'] = $ids;

						$terms = get_terms( $terms_args );

						global $wp_taxonomies;

						foreach ( $terms as $term ) {
							if ( isset( $wp_taxonomies[ $term->taxonomy ] ) ) {
								$list[] = array(
									'value' => $term->term_id,
									'label' => sprintf( '%1$s: %2$s', $wp_taxonomies[ $term->taxonomy ]->label, $term->name ),
								);
							}
						}
					}
					break;
				case 'posts':
					$ids = $settings[$setting];

					if ( ! empty( $ids ) ) {
						$args = array(
							'post_type'           => 'any',
							'posts_per_page'      => - 1,
							'suppress_filters'    => false,
							'ignore_sticky_posts' => true,
							'post__in'            => $ids
						);

						$posts = get_posts( $args );

						foreach ( $posts as $post ) {
							$list[] = array(
								'value' => $post->ID,
								'label' => sprintf( '%1$s: %2$s', ucfirst( $post->post_type ), $post->post_title ),
							);
						}
					}

					break;
			}

			if ( empty( $list ) ) {
				$settings[$setting] = array(
					'type'    => 'array',
					'default' => array(),
				);

				return $settings;
			}

			$result = array();

			if ( ! empty( $settings[$setting] ) ) {
				foreach ( $list as $list_item ) {
					if ( in_array( $list_item["value"], $settings[$setting] ) ) {
						$result[] = array( "value" => $list_item["value"], "label" => $list_item["label"] );
					}
				}
			}

			$settings[$setting] = ! empty( $settings[$setting] )
				? array(
					'type'    => 'array',
					'default' => $settings[$setting],
				)
				: array(
					'type'    => 'array',
					'default' => array(),
				);

			$settings[$setting_list] = ! empty( $result )
				? array(
					'type'    => 'array',
					'default' => $result,
				)
				: array(
					'type'    => 'array',
					'default' => array(),
				);

			return $settings;
		}

		public static function get_listing_grid( $listing_id ) {
			$listing_html = '';

			if ( empty( $listing_id  ) ) {
				return $listing_html;
			}

			$is_bricks_listing    = false;
			$is_blocks_listing    = false;
			$is_elementor_listing = false;

			if ( '' != $listing_id && class_exists( 'Jet_Engine' ) ) {
				if ( defined( 'BRICKS_VERSION' ) ) {
					wp_enqueue_style( 'bricks-photoswipe' );
					wp_enqueue_script( 'bricks-photoswipe' );
					wp_enqueue_script( 'bricks-photoswipe-lightbox' );
				}

				wp_enqueue_script(
					'jquery-slick',
					jet_engine()->plugin_url( 'assets/lib/slick/slick.min.js' ),
					array( 'jquery' ),
					'1.8.1',
					true
				);

				$is_bricks_listing    = jet_engine()->listings->data->get_listing_type( $listing_id ) === 'bricks';
				$is_blocks_listing    = jet_engine()->listings->data->get_listing_type( $listing_id ) === 'blocks';
				$is_elementor_listing = jet_engine()->listings->data->get_listing_type( $listing_id ) === 'elementor';

				if (  $is_bricks_listing ) {
					$listing_grid = new Listing_Grid;

					$listing_grid->settings['lisitng_id'] = $listing_id;
					$listing_grid->settings['posts_num']  = 1;

					$listing_grid->render();
				}

				if ( $is_blocks_listing ) {
					$blocks_listing_grid_instance = jet_engine()->blocks_views->block_types->get_block_type_instance( 'listing-grid' );
					$listing_html                 = $blocks_listing_grid_instance->render_callback( [ 'lisitng_id' => $listing_id, '_block_id' => '', 'posts_num' => 1 ] );
				}

				if (  $is_elementor_listing ) {
					$initial_object = jet_engine()->listings->data->get_current_object();

					$listing_html = jet_engine()->frontend->get_listing_item_content( $listing_id );

					jet_engine()->frontend->reset_data();
					jet_engine()->listings->data->set_current_object( $initial_object );
				}
			}

			return $listing_html;
		}

		/**
		 * Retrieves and displays the source listing grid.
		 *
		 * This method generates HTML for the source listing grid based on the provided listing ID and source.
		 *
		 * @param int $listing_id The ID of the listing.
		 * @param object $source The source object that provides methods to set arguments, search string, limit, and render.
		 * @return string The generated HTML for the source listing grid.
		 */
		public static function get_source_listing_grid( $listing_id, $source ) {
			$listing_html = '';

			if ( empty( $listing_id  )  ) {
				return $listing_html;
			}

			if ( filter_var( $settings['search_source_' . $key], FILTER_VALIDATE_BOOLEAN ) ) {
				$source->set_args( $settings );
				$source->set_search_string( '' );
				$source->set_limit( 1 );

				$render = $source->render();

				if ( ! empty( $render ) ) {
					echo $render[0]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			}
		}

		/**
		 * Render new elementor icons
		 *
		 * @return [type] [description]
		 */
		public static function render_icon( $icon = null, $icon_class = '', $custom_atts = array() ) {

			$custom_atts_string = '';

			if ( ! empty( $custom_atts ) ) {
				foreach ( $custom_atts as $key => $value ) {
					$custom_atts_string .= sprintf( ' %1$s="%2$s"', esc_attr( $key ), esc_attr( $value ) );
				}
			}

			static $total = 0;

			if ( ! is_array( $icon ) && is_numeric( $icon ) ) {

				ob_start();

				echo '<div class="' . esc_attr( $icon_class ) . ' is-svg-icon"' . $custom_atts_string . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

				$mime = get_post_mime_type( $icon );

				if ( 'image/svg+xml' === $mime ) {
					$file = get_attached_file( $icon );

					if ( file_exists( $file ) ) {
						include $file;
					}

				} else {
					echo wp_get_attachment_image( $icon, 'full' );
				}

				echo '</div>';

				return ob_get_clean();

			}
			// Render Bricks svg icon
			elseif ( ! is_array( $icon ) && false !== str_contains( $icon, '<svg' ) ) {

				ob_start();

				echo '<div class="' . esc_attr( $icon_class ) . ' is-svg-icon"' . $custom_atts_string . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '</div>';

				return ob_get_clean();

			}
			// Render Bricks font icon
			elseif ( ! is_array( $icon ) && false !== str_contains( $icon, '<i' ) ) {

				ob_start();

				echo '<div class="' . esc_attr( $icon_class ) . '">';
				echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '</div>';

				return ob_get_clean();
			}
			// Bricks font icon with array value
			elseif ( is_array( $icon ) && isset( $icon['library'] ) && isset( $icon['icon'] ) ) {
				return sprintf( '<div class="%1$s"><i class="%2$s"></i></div>', esc_attr( $icon_class ), $icon['icon'] );
			}

			// Pre-process Gutenberg icon
			if ( ! empty( $icon ) ) {

				$icon_src = null;

				if ( is_array( $icon ) && ! empty( $icon['src'] ) ) {
					$icon_src = $icon['src'];

					return sprintf( '<div class="%1$s is-svg-icon"%2$s>%3$s</div>', esc_attr( $icon_class ), $custom_atts_string, $icon_src );
				}
			}

			$is_new = class_exists( 'Elementor\Icons_Manager' ) && Elementor\Icons_Manager::is_migration_allowed();

			if ( $is_new ) {
				ob_start();

				$custom_atts['class']       = $icon_class;
				$custom_atts['aria-hidden'] = 'true';

				Elementor\Icons_Manager::render_icon( $icon, $custom_atts );

				$html = ob_get_clean();

				$is_svg_library = 'svg' === $icon['library'];
				$is_svg_inline  = false !== strpos( $html, 'e-font-icon-svg' );

				if ( $is_svg_library || $is_svg_inline ) {

					if ( $is_svg_inline ) {
						$html = str_replace( $icon_class . ' ', '', $html );
					}

					$html = sprintf( '<div class="%1$s is-svg-icon"%2$s>%3$s</div>', esc_attr( $icon_class ), $custom_atts_string, $html );
				}

				return $html;

			} else {
				return false;
			}

		}

		public static function custom_fields_post_type_update( $custom_fields_source, $query_post_type ) {

			$cf_keys = ! is_array( $custom_fields_source ) ? explode( ',', str_replace( ' ', '', $custom_fields_source ) ) : $custom_fields_source;

			if ( ( $key = array_search( '_sku', $cf_keys ) ) !== false ) {
				$show_product_variation = apply_filters( 'jet-search/ajax-search/show-product-variations', true );

				if ( $show_product_variation ) {
					if ( function_exists( 'WC' ) ) {
						if ( 'any' === $query_post_type ) {
							$post_types = get_post_types( array( 'exclude_from_search' => false ), 'names' );
							$post_types = array_values( $post_types );

							$is_product_source = array_search( 'product', $post_types );

							if ( false !== $is_product_source ) {
								$query_post_type = array_merge( $post_types, array( 'product_variation' ) );
							}
						} else {
							$query_post_type = is_array( $query_post_type ) ? $query_post_type : (array) $query_post_type;
							$is_product_source = array_search( 'product', $query_post_type );

							if ( false !== $is_product_source ) {
								$query_post_type = array_merge( $query_post_type, array( 'product_variation' ) );
							}
						}
					}
				}
			} else {
				if ( 'any' === $query_post_type ) {
					$post_types      = get_post_types( array( 'exclude_from_search' => false ), 'names' );
					$query_post_type = array_values( $post_types );
				}
			}

			if ( ! is_array( $query_post_type ) ) {
				$query_post_type = explode( ',', str_replace( ' ', '', $query_post_type ) );
			}

			return $query_post_type;
		}

		public static function maybe_add_enqueue_assets_data( &$response ) {
			if ( isset( $_REQUEST['isEditMode'] ) && filter_var( $_REQUEST['isEditMode'], FILTER_VALIDATE_BOOLEAN ) ) { // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
				return;
			}

			// Ensure registered `jet-plugins` script.
			if ( ! wp_script_is( 'jet-plugins', 'registered' )  ) {
				jet_search()->register_jet_plugins_js();
			}

			wp_scripts()->done[] = 'jquery';
			wp_scripts()->done[] = 'jet-plugins';
			wp_scripts()->done[] = 'jet-engine-frontend';

			$scripts = wp_scripts()->queue;
			$styles  = wp_styles()->queue;

			$styles[] = 'wp-block-library';

			if ( ! empty( $scripts ) ) {
				$response['scripts'] = array();

				foreach ( (array) $scripts as $script ) {

					ob_start();
					wp_scripts()->do_items( $script );
					$script_html = ob_get_clean();

					$response['scripts'][ $script ] = $script_html;
				}
			}

			if ( ! empty( $styles ) ) {
				$response['styles'] = array();

				foreach ( (array) $styles as $style ) {

					ob_start();
					wp_styles()->do_items( $style );
					$style_html = ob_get_clean();

					$response['styles'][ $style ] = $style_html;
				}
			}

		}

		public static function merge_arrays_unique_by_id( array $array1, array $array2 ) {
			if ( empty( $array1 ) ) {
				return $array2;
			}

			if ( empty( $array2 ) ) {
				return $array1;
			}

			$result = [];

			foreach ( $array1 as $item ) {
				$result[$item['id']] = $item;
			}

			foreach ( $array2 as $item) {
				if ( ! isset( $result[$item['id']] ) ) {
					$result[$item['id']] = $item;
				}
			}

			return array_values($result);
		}

		public static function get_product_variation_ids( $product_id ) {

			if ( ! class_exists( 'WooCommerce' ) ) {
				return array();
			}

			$product = wc_get_product( $product_id );

			if ( $product && $product->is_type( 'variable' ) ) {
				return $product->get_children();
			}

			return array();
		}
	}
}
