<?php
/**
 * Compatibility filters and actions
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define Jet_Smart_Filters_Compatibility_WPML class
 */
class Jet_Smart_Filters_Compatibility_WPML {

	/**
	 * Constructor for the class
	 */
	function __construct() {

		if ( ! defined( 'ICL_SITEPRESS_VERSION' ) && ! defined( 'WPML_ST_VERSION' ) ) {
			return;
		}
		
		add_filter( 'wcml_multi_currency_ajax_actions', array( $this, 'add_action_to_multi_currency_ajax' ) );
		add_filter( 'jet-smart-filters/render_filter_template/filter_id', array( $this, 'modify_filter_id' ) );
		add_filter( 'jet-smart-filters/filters/posts-source/args', array( $this, 'modify_posts_source_args' ) );

		//convert current currency to default
		add_filter( 'jet-smart-filters/query/final-query', array( $this, 'wpml_wc_convert_currency' ) );

		// For Indexer
		add_filter( 'jet-smart-filters/indexer/tax-query-args', array( $this, 'remove_wpml_terms_filters' ) );

		// Translatable nodes
		add_action( 'init', array( $this, 'load_wpml_integration_classes' ) );
		add_filter( 'wpml_elementor_widgets_to_translate', array( $this, 'add_translatable_nodes' ) );

	}

	public function add_action_to_multi_currency_ajax( $ajax_actions = array() ) {

		$ajax_actions[] = 'jet_smart_filters';
		
		return $ajax_actions;
	}

	public function modify_filter_id( $filter_id ) {

		return apply_filters( 'wpml_object_id', $filter_id, jet_smart_filters()->post_type->slug(), true );
	}

	public function modify_posts_source_args( $args ) {

		if ( isset( $args['post_type'] ) ) {
			$is_translated_post_type = apply_filters( 'wpml_is_translated_post_type', null, $args['post_type'] );

			if ( $is_translated_post_type ) {
				$args['suppress_filters'] = false;
			}
		}

		return $args;
	}

	public function remove_wpml_terms_filters( $args ) {

		global $sitepress;

		remove_filter( 'get_term',       array( $sitepress, 'get_term_adjust_id' ), 1 );
		remove_filter( 'get_terms_args', array( $sitepress, 'get_terms_args_filter' ), 10 );
		remove_filter( 'terms_clauses',  array( $sitepress, 'terms_clauses' ), 10 );

		$args['suppress_filters'] = true;

		return $args;
	}

	public function wpml_wc_convert_currency( $args ) {

		global $woocommerce_wpml;

		$providers = strtok( $args['jet_smart_filters'], '/' );

		if (
			$woocommerce_wpml
			&& isset( $woocommerce_wpml->multi_currency )
			&& in_array( $providers, [
				'jet-woo-products-grid',
				'jet-woo-products-list',
				'epro-products',
				'epro-archive-products',
				'woocommerce-shortcode',
				'woocommerce-archive'
			], true )
		) {
			// default currency
			$default_currency = wcml_get_woocommerce_currency_option();
			// current currency
			if ( method_exists( $woocommerce_wpml->multi_currency, 'get_client_currency' ) ) {
				$currency = $woocommerce_wpml->multi_currency->get_client_currency();
			} elseif ( method_exists( $woocommerce_wpml->multi_currency, 'get_current_currency' ) ) {
				$currency = $woocommerce_wpml->multi_currency->get_current_currency();
			} else {
				$currency = wcml_get_woocommerce_currency_option();
			}

			if ( $currency !== $default_currency ) {
				if ( ! empty( $args['meta_query'] ) && is_array( $args['meta_query'] ) ) {
					foreach ( $args['meta_query'] as $i => $meta ) {
						if ( isset( $meta['key'] ) && $meta['key'] === '_price' && ! empty( $meta['value'] ) ) {
							if ( is_array( $meta['value'] ) ) {
								$min_price = $woocommerce_wpml->multi_currency->prices->unconvert_price_amount( $meta['value'][0] );
								$max_price = $woocommerce_wpml->multi_currency->prices->unconvert_price_amount( $meta['value'][1] );

								$args['meta_query'][$i]['value'][0] = $min_price;
								$args['meta_query'][$i]['value'][1] = $max_price;
							}
							break;
						}
					}
				}
			}
		}

		return $args;
	}

	public function load_wpml_integration_classes() {

		if ( ! class_exists( 'WPML_Elementor_Module_With_Items' ) ) {
			return;
		}

		require jet_smart_filters()->plugin_path( 'includes/compatibility/wpml/integration-classes/jet-smart-filters-sorting.php' );
	}

	public function add_translatable_nodes( $nodes_to_translate ) {

		// sorting widget
		$nodes_to_translate[ 'jet-smart-filters-sorting' ] = array(
			'conditions'        => array( 'widgetType' => 'jet-smart-filters-sorting' ),
			'fields'            => array(
					array(
						'field'       => 'label',
						'type'        => esc_html__( 'JetSmartFilters: Sorting Label', 'jet-smart-filters' ),
						'editor_type' => 'LINE',
					),
					array(
						'field'       => 'placeholder',
						'type'        => esc_html__( 'JetSmartFilters: Sorting Placeholder', 'jet-smart-filters' ),
						'editor_type' => 'LINE',
					),
				),
			'integration-class' => 'WPML_Integration_Jet_Smart_Filters_Sorting',
		);

		// search widget
		$nodes_to_translate['jet-smart-filters-search'] = [
			'conditions' => [ 'widgetType' => 'jet-smart-filters-search' ],
			'fields'     => [
				[
					'field'       => 'apply_button_text',
					'type'        => esc_html__( 'JetSmartFilters: Search Apply Button Text', 'jet-smart-filters' ),
					'editor_type' => 'LINE',
				],
			],
		];

		// apply button widget
		$nodes_to_translate['jet-smart-filters-apply-button'] = [
			'conditions' => [ 'widgetType' => 'jet-smart-filters-apply-button' ],
			'fields'     => [
				[
					'field'       => 'apply_button_text',
					'type'        => esc_html__( 'JetSmartFilters: Apply button text', 'jet-smart-filters' ),
					'editor_type' => 'LINE',
				],
			],
		];

		// remove filters widget
		$nodes_to_translate['jet-smart-filters-remove-filters'] = [
			'conditions' => [ 'widgetType' => 'jet-smart-filters-remove-filters' ],
			'fields'     => [
				[
					'field'       => 'remove_filters_text',
					'type'        => esc_html__( 'Remove filters text', 'jet-smart-filters' ),
					'editor_type' => 'LINE',
				],
			],
		];

		// pagination widget
		$nodes_to_translate['jet-smart-filters-pagination'] = [
			'conditions' => [ 'widgetType' => 'jet-smart-filters-pagination' ],
			'fields'     => [
				[
					'field'       => 'prev_text',
					'type'        => esc_html__( 'JetSmartFilters: Pagination Previous Text', 'jet-smart-filters' ),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'next_text',
					'type'        => esc_html__( 'JetSmartFilters: Pagination Next Text', 'jet-smart-filters' ),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'load_more_text',
					'type'        => esc_html__( 'JetSmartFilters: Pagination Load More Text', 'jet-smart-filters' ),
					'editor_type' => 'LINE',
				],
			],
		];

		/*
		 * Additional filter settings
		*/
		$filters_with_additional_settings = [
			'jet-smart-filters-checkboxes',
			'jet-smart-filters-check-range',
			'jet-smart-filters-radio',
			'jet-smart-filters-color-image'
		];

		$additional_settings_fields_to_add = [
			[
				'field'       => 'search_placeholder',
				'type'        => esc_html__( 'JetSmartFilters: Search Placeholder', 'jet-smart-filters' ),
				'editor_type' => 'LINE',
			],
			[
				'field'       => 'more_text',
				'type'        => esc_html__( 'JetSmartFilters: More Text', 'jet-smart-filters' ),
				'editor_type' => 'LINE',
			],
			[
				'field'       => 'less_text',
				'type'        => esc_html__( 'JetSmartFilters: Less Text', 'jet-smart-filters' ),
				'editor_type' => 'LINE',
			],
			[
				'field'       => 'dropdown_placeholder',
				'type'        => esc_html__( 'JetSmartFilters: Placeholder', 'jet-smart-filters' ),
				'editor_type' => 'LINE',
			],
			[
				'field'       => 'dropdown_n_selected_text',
				'type'        => esc_html__( 'JetSmartFilters: Generic text', 'jet-smart-filters' ),
				'editor_type' => 'LINE',
			],
			[
				'field'       => 'dropdown_apply_button_text',
				'type'        => esc_html__( 'JetSmartFilters: Apply button text', 'jet-smart-filters' ),
				'editor_type' => 'LINE',
			]
		];

		foreach ( $filters_with_additional_settings as $widget ) {
			if ( ! isset( $nodes_to_translate[ $widget ] ) ) {
				$nodes_to_translate[ $widget ] = [
					'conditions' => [ 'widgetType' => $widget ],
					'fields'     => [],
				];
			}

			foreach ( $additional_settings_fields_to_add as $new_field ) {
				$already_exists = false;

				if ( isset( $nodes_to_translate[ $widget ]['fields'] ) ) {
					foreach ( $nodes_to_translate[ $widget ]['fields'] as $existing_field ) {
						if ( $existing_field['field'] === $new_field['field'] ) {
							$already_exists = true;
							break;
						}
					}
				}

				if ( ! $already_exists ) {
					$nodes_to_translate[ $widget ]['fields'][] = $new_field;
				}
			}
		}

		return $nodes_to_translate;
	}

}
