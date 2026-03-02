<?php
namespace Jet_Popup;

// If this file is called directly, abort.
use function wsd\afb\has_attributes;

if ( ! defined( 'WPINC' ) ) {
	die;
}

class Block_Editor_Manager {

	/**
	 * A reference to an instance of this class.
	 *
	 * @since 1.0.0
	 * @var   object
	 */
	private static $instance = null;

	/**
	 * @var array
	 */
	private $registered_blocks = [];

	/**
	 * Data_Attributes instance holder
	 * @var [type]
	 */
	public $data_attributes;

	/**
	 * Constructor for the class
	 */
	public function __construct() {
		
		$this->load_files();

		$this->data_attributes = new Data_Attributes();

		add_action( 'init', array( $this, 'register_data_attrs' ), 0 );
		add_action( 'init', array( $this, 'register_block_types' ) );
		add_action( 'init', [ $this, 'register_post_meta' ], 999 );
		add_action( 'enqueue_block_editor_assets', [ $this, 'script_enqueue' ], 9 );
		add_filter( 'block_type_metadata', [ $this, 'add_block_attrs' ], 9 );
		add_filter( 'render_block_data', [ $this, 'render_block_data' ], 10, 4 );
		add_filter( 'register_block_type_args', [ $this, 'register_block_type_arg' ], 10, 2);
	}

	/**
	 * @return void
	 */
	public function register_data_attrs() {

		$this->data_attributes->register_attributes( [
			[
				'name'        => 'jetPopupInstance',
				'type'        => 'select',
				'dataType'    => 'string',
				'dataAttr'    => 'data-popup-instance',
				'default'     => 'none',
				'label'       => __( 'Attached Popup' ),
				'description' => __( 'Select Jet Popup for this Block' ),
				'options'     => function() {
					return array_merge(
						[
							[
								'label' => __( 'None', 'jet-popup' ),
								'value' => 'none',
							]
						],
						\Jet_Popup_Utils::get_posts_by_type( 'jet-popup', '', [], false )
					);
				},
			],
			[
				'name'        => 'jetPopupTriggerType',
				'type'        => 'select',
				'dataType'    => 'string',
				'dataAttr'    => 'data-popup-trigger-type',
				'default'     => 'none',
				'label'       => __( 'Trigger Type' ),
				'description' => __( 'Select trigger type' ),
				'options'     => function() {
					return \Jet_Popup_Utils::get_popup_attached_trigger_list( true );
				},
				'condition'   => [
					'jetPopupInstance!' => 'none',
				],
			],
			[
				'name'        => 'jetPopupCustomSelector',
				'type'        => 'text',
				'dataType'    => 'string',
				'dataAttr'    => 'data-popup-custom-selector',
				'default'     => '',
				'label'       => __( 'Custom Selector' ),
				'description' => __( 'Enter the css selector used in the block HTML' ),
				'condition'   => [
					'jetPopupInstance!'   => 'none',
					'jetPopupTriggerType' => 'click-selector',
				],
			],
		] );

	}

	/**
	 * Load files
	 */
	public function load_files() {
		require jet_popup()->plugin_path( 'includes/block-editor/data-attributes.php' );
	}

	/**
	 * @return array
	 */
	public function get_registered_blocks() {
		return $this->registered_blocks;
	}

	/**
	 * @return array
	 */
	public function get_registered_block_attrs() {
		$registered_blocks = $this->get_registered_blocks();

		$block_attrs = [];

		foreach ( $registered_blocks as $block_slug => $block_instance ) {
			$block_attrs[ $block_slug ] = $block_instance->get_attributes();
		}

		return $block_attrs;
	}

	/**
	 * @return void
	 */
	public function register_block_types() {
		$base_path = jet_popup()->plugin_path( 'includes/block-editor/blocks/' );

		require $base_path . 'base.php';

		$default_blocks = apply_filters( 'jet-popup/block-manager/blocks-list', [
			'\Jet_Popup\Blocks\Action_Button'   => $base_path . 'action-button.php',
		] );

		foreach ( $default_blocks as $class => $file ) {
			require $file;

			$instance = new $class;
			$id = $instance->get_name();

			$this->registered_blocks[ $id ] = $instance;
		}

	}

	/**
	 * @return void
	 */
	function register_post_meta() {

		register_meta(
			'post',
			'_styles',
			[
				'object_subtype' => jet_popup()->post_type->slug(),
				'type'              => 'object',
				'default'           => '',
				'single'            => true,
				'show_in_rest'      => [
					'schema' => [
						'type'       => 'object',
						'properties' => [],
						'additionalProperties' => true,
					]
				],
				'auth_callback'     => [ $this, 'auth_callback' ],
				'sanitize_callback' => [ $this, 'sanitize_callback' ],
			]
		);

		register_meta(
			'post',
			'_settings',
			[
				'object_subtype' => jet_popup()->post_type->slug(),
				'type'              => 'object',
				'default'           => '',
				'single'            => true,
				'show_in_rest'      => [
					'schema' => [
						'type'       => 'object',
						'properties' => [],
						'additionalProperties' => true,
					]
				],
				'auth_callback'     => [ $this, 'auth_callback' ],
				'sanitize_callback' => [ $this, 'sanitize_callback' ],
			]
		);
	}

	/**
	 * @param $res
	 * @param $key
	 * @param $post_id
	 * @param $user_id
	 * @param $cap
	 *
	 * @return bool
	 */
	public function auth_callback( $res, $key, $post_id, $user_id, $cap ) {
		return true;
	}

	/**
	 * @param $meta_value
	 * @param $meta_key
	 * @param $object_type
	 *
	 * @return mixed
	 */
	public function sanitize_callback( $meta_value, $meta_key, $object_type ){
		return $meta_value;
	}

	/**
	 * @return void
	 */
	function script_enqueue() {
		$screen = get_current_screen();

		jet_popup()->assets->enqueue_styles();

		wp_enqueue_script(
			'jet-popup-block-editor',
			jet_popup()->plugin_url( 'assets/js/jet-popup-block-editor.js' ),
			[ 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-block-editor', 'lodash' ],
			jet_popup()->get_version()
		);

		$available_popups = array_merge(
			[
				[
					'label' => __( 'None', 'jet-popup' ),
					'value' => 'none',
				]
			],
			\Jet_Popup_Utils::get_posts_by_type( 'jet-popup', '', [], false )
		);

		wp_localize_script( 'jet-popup-block-editor', 'JetPopupBlockEditorConfig', [
			'availablePopups' => $available_popups,
			'dataAttributes' => $this->data_attributes->to_localized_data(),
			'attachedTriggerOptions' => \Jet_Popup_Utils::get_popup_attached_trigger_list( true ),
			'actionsOptions' => \Jet_Popup_Utils::get_popup_action_list( true ),
			'notSupportedBlocks' => $this->get_not_supported_blocks(),
			'registeredBlockAttrs' => $this->get_registered_block_attrs(),
			'postType' => get_post_type(),
		] );

		if ( $screen->id === jet_popup()->post_type->slug() ) {

			wp_enqueue_script(
				'jet-popup-block-editor-plugin',
				jet_popup()->plugin_url( 'assets/js/jet-popup-block-editor-plugin.js' ),
				[ 'wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-block-editor' ],
				jet_popup()->get_version()
			);

			wp_localize_script( 'jet-popup-block-editor-plugin', 'JetPopupBlockEditorPluginConfig', [
				'popupAnimationTypeOptions' => \Jet_Popup_Utils::get_popup_animation_list( true ),
				'popupOpenTriggerOptions' => \Jet_Popup_Utils::get_popup_open_trigger_list( true ),
				'popupTimeDelayOptions' => \Jet_Popup_Utils::get_popup_time_delay_list( true ),
				'notSupportedBlocks' => $this->get_not_supported_blocks(),
				'labels' => [
					'importButtonLabel' => __( 'Import Popup', 'jet-popup' ),
				],
				'defaultStyleSettings' => jet_popup()->settings->get_popup_default_styles(),
				'defaultSettings' => jet_popup()->settings->get_popup_default_settings(),
				'conditionManagerUrl'  => admin_url( 'edit.php?post_type=jet-popup&library_action=edit_conditions' ),
			] );
		}

	}

	/**
	 * @param $metadata
	 * @return mixed
	 */
	public function add_block_attrs( $metadata ) {

		if ( in_array( $metadata['name'], $this->get_not_supported_blocks() ) ) {
			return $metadata;
		}

		$metadata['attributes'] = ! empty( $metadata['attributes'] ) ? $metadata['attributes'] : [];
		$metadata['attributes'] = array_merge( $metadata['attributes'], $this->data_attributes->to_block_attrs() );

		return $metadata;
	}

	/**
	 * @param $parsed_block
	 * @param $source_block
	 * @param $parent_block
	 * @return mixed
	 */
	public function render_block_data( $parsed_block, $source_block, $parent_block ) {
		$block_attrs = $parsed_block['attrs'];

		if ( isset( $block_attrs['jetPopupInstance'] ) && 'none' !== $block_attrs['jetPopupInstance'] ) {
			jet_popup()->generator->add_attached_popup( $block_attrs['jetPopupInstance'] );
		}

		return $parsed_block;
	}

	/**
	 * @param $args
	 * @param $name
	 * @return mixed
	 */
	function register_block_type_arg( $args, $name ) {
		$not_supported = $this->get_not_supported_blocks();

		if ( in_array( $name, $not_supported ) || ! $this->data_attributes ) {
			return $args;
		}

		if ( ! isset( $args['attributes'] ) ) {
			$args['attributes'] = [];
		}

		$args['attributes'] = array_merge( $args['attributes'], $this->data_attributes->to_block_attrs() );

		if ( isset( $args['render_callback'] ) && is_callable( $args['render_callback'] ) ) {
			$cb = $args['render_callback'];
			$args['render_callback'] = function ( $attributes, $content, $block = null ) use ( $cb, $args ) {
				$rendered = call_user_func( $cb, $attributes, $content, $block );

				if ( ! isset( $attributes['jetPopupInstance'] ) || 'none' === $attributes['jetPopupInstance'] ) {
					return $rendered;
				}

				return $this->add_attributes( $attributes, $rendered );
			};
		}

		return $args;
	}

	/**
	 * Add attributes to root element.
	 *
	 * @param array $args AFB settings.
	 * @param string $html Block HTML.
	 * @return string Block HTML with additional attributes.
	 */
	public function add_attributes( $args, $html ) {
		$dom = $this->get_dom( $html );
		$body = $dom->getElementsByTagName('body')->item(0);

		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		foreach( $body->childNodes as $root ) {

			if ( method_exists( $root, 'setAttribute') ) {
				try {
					$this->data_attributes->process_attributes( $args, function( $attr, $value ) use ( $root ) {
						$root->setAttribute( $attr, $value );
					} );
				} catch(Exception $e) {
					//do_action('afb_set_attribute_exception', $e, $key, $value, $dom);
				}
			}
		}

		return $this->get_body( $dom->saveHTML() );
	}

	/**
	 * Get DOM Document.
	 *
	 * @param string $html
	 * @return DOMDocument
	 */
	public function get_dom( $html ) {
		$dom = new \DOMDocument();
		$libxml_previous_state = libxml_use_internal_errors( true );
		$dom->loadHTML( '<html><body>' . mb_convert_encoding( trim( $html ), 'HTML-ENTITIES', 'UTF-8' ) . '</body></html>' );

		libxml_clear_errors();
		libxml_use_internal_errors( $libxml_previous_state );

		return $dom;
	}

	/**
	 * Parse `<body>` content out of a rendered HTML document.
	 *
	 * @param string $html
	 * @return string
	 */
	public function get_body( $html ) {
		return trim(
			preg_replace(
				'/^<!DOCTYPE.+?>/', '',
				str_replace(
					[ '<html>', '</html>', '<body>', '</body>' ],
					'',
					$html
				)
			)
		);
	}

	/**
	 * @return mixed|null
	 */
	public function get_not_supported_blocks() {
		return apply_filters( 'jet-popup/block-manager/not-supported-blocks', [
			'core/freeform',
			'core/html',
			'core/shortcode',
			'core/legacy-widget',
			'jet-popup/action-button',
		] );
	}

	/*
	 * Returns the instance.
	 *
	 * @since  1.0.0
	 * @return object
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}
}
