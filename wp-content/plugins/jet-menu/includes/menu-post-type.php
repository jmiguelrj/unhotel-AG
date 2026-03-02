<?php

namespace Jet_Menu;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class Menu_Post_Type
 * @package Jet_Menu
 */
class Menu_Post_Type {

	/**
	 * A reference to an instance of this class.
	 *
	 * @since 1.0.0
	 * @var   object
	 */
	private static $instance = null;

	/**
	 * @var string
	 */
	protected $post_type = 'jet-menu';

	/**
	 * @var string
	 */
	protected $meta_key  = 'jet-menu-item';

	/**
	 * Jet Menu nonce key
	 *
	 * @var string
	 */
	protected $nonce_key = 'jet-menu-nav-settings';

	/**
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

	/**
	 * Constructor for the class
	 */
	public function __construct() {

		$this->register_post_type();

		$this->edit_mega_template_redirect();

		add_filter( 'option_elementor_cpt_support', array( $this, 'set_option_support' ) );

		add_filter( 'default_option_elementor_cpt_support', array( $this, 'set_option_support' ) );

		add_action( 'template_include', array( $this, 'set_post_type_template' ), 9999 );
		add_action( 'save_post', [ $this, 'save_menu_post_type'], 10, 3 );

		add_filter( 'body_class', array( $this, 'add_body_classes' ), 9 );

		add_action( 'template_redirect', array( $this, 'restrict_template_access' ) );
	}

	/**
	 * Returns post type slug
	 *
	 * @return string
	 */
	public function slug() {
		return $this->post_type;
	}

	/**
	 * Returns Mega Menu meta key
	 *
	 * @return string
	 */
	public function meta_key() {
		return $this->meta_key;
	}

	/**
	 * @param $classes
	 *
	 * @return mixed
	 */
	public function add_body_classes( $classes ) {

		if ( $this->slug() === get_post_type() ) {
			$classes[] = 'jet-menu-post-type';
		}

		return $classes;
	}

	/**
	 * Add elementor support for mega menu items.
	 */
	public function set_option_support( $value ) {

		if ( empty( $value ) ) {
			$value = array();
		}

		return array_merge( $value, array( $this->slug() ) );
	}

	/**
	 * Register post type
	 *
	 * @return void
	 */
	public function register_post_type() {

		$labels = array(
			'name'          => esc_html__( 'Mega Menu Items', 'jet-menu' ),
			'singular_name' => esc_html__( 'Mega Menu Item', 'jet-menu' ),
			'add_new'       => esc_html__( 'Add New Mega Menu Item', 'jet-menu' ),
			'add_new_item'  => esc_html__( 'Add New Mega Menu Item', 'jet-menu' ),
			'edit_item'     => esc_html__( 'Edit Mega Menu Item', 'jet-menu' ),
			'menu_name'     => esc_html__( 'Mega Menu Items', 'jet-menu' ),
		);

		$args = array(
			'labels'              => $labels,
			'hierarchical'        => false,
			'description'         => 'description',
			'taxonomies'          => [],
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'show_in_rest'        => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => null,
			'menu_icon'           => null,
			'show_in_nav_menus'   => false,
			'publicly_queryable'  => true,
			'exclude_from_search' => true,
			'has_archive'         => false,
			'query_var'           => true,
			'can_export'          => true,
			'rewrite'             => true,
			'capability_type'     => 'post',
			'supports'            => array( 'title', 'editor', 'custom-fields' ),
		);

		register_post_type( $this->slug(), $args );

	}

	/**
	 * Returns related mega menu post
	 *
	 * @param  int $menu_id Menu ID
	 * @return [type]          [description]
	 */
	public function get_related_menu_post( $menu_id ) {
		return get_post_meta( $menu_id, $this->meta_key(), true );
	}

	/**
	 * Set blank template for editor
	 */
	public function set_post_type_template( $template ) {

		$found = false;

		if ( is_singular( $this->slug() ) ) {
			$found    = true;
			$template = jet_menu()->plugin_path( 'templates/blank.php' );
		}

		if ( $found ) {
			do_action( 'jet-menu/template-include/found' );
		}

		return $template;

	}

	/**
	 * Edit redirect
	 *
	 * @return void
	 */
	public function edit_mega_template_redirect() {

		// Check if the current user has 'manage_options' capability
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Check if required parameters are present in the request
		// Safe: values are used only for conditional logic, not stored or trusted.
		if ( empty( $_REQUEST['jet-open-editor'] ) || empty( $_REQUEST['item'] ) || empty( $_REQUEST['menu'] ) || empty( $_REQUEST['content'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		// Process content
		$this->process_content();
	}


	/**
	 * Process content based on the content type.
	 *
	 */
	public function process_content() {

		if ( ! isset( $_REQUEST['_nonce'] ) || ! wp_verify_nonce( $_REQUEST['_nonce'], $this->nonce_key ) ) { // phpcs:ignore
			wp_die( esc_html__( 'Security check failed', 'jet-menu' ) );
		}

		// Extract necessary request parameters
		$menu_item_id = isset( $_REQUEST['item'] ) ? intval( $_REQUEST['item'] ) : 0;
		$content_type = isset( $_REQUEST['content'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['content'] ) ) : '';
		$content_name = isset( $_REQUEST['content_name'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['content_name'] ) ) : '';
		$content_id   = isset( $_REQUEST['content_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['content_id'] ) ) : '';

		// Determine the meta key for the content type.
		$meta_key = $this->get_meta_key_for_content_type( $content_type );

		// Get the existing content associated with the menu item.
		$existing_value = get_post_meta( $menu_item_id, $meta_key, true );

		// If no existing content and no provided content ID, create a new content post.
		if ( empty( $content_id ) && ! $existing_value ) {
			$new_content_id = $this->create_new_content( $content_type, $content_name, $menu_item_id );

			update_post_meta( $menu_item_id, $meta_key, $new_content_id );
		}
		// If a content ID is provided and differs from the existing one, update the meta value.
		elseif ( $content_id && $content_id !== $existing_value ) {
			update_post_meta( $menu_item_id, $meta_key, $content_id );
		}

		// Generate the edit link for the content post.
		$edit_link = $this->generate_edit_link( $menu_item_id, $content_type );

		// Perform a redirect to the edit link.
		$this->perform_redirect( $menu_item_id, $content_type, $edit_link );
	}


	/**
	 * Get the meta key for a given content type.
	 *
	 * @param string $content_type The content type.
	 * @return string The meta key for the content type.
	 */
	private function get_meta_key_for_content_type( $content_type ) {
		$meta_keys = [
			'elementor' => 'jet-menu-item',
			'default'   => 'jet-menu-item-block-editor',
			// Add other $content_type and relevant metadata here
		];

		return isset( $meta_keys[ $content_type ] ) ? $meta_keys[ $content_type ] : '';
	}


	/**
	 * Generate the edit link for a content type.
	 *
	 * @param int $menu_item_id The ID of the menu item.
	 * @param string $content_type The content type.
	 * @return string The edit link URL.
	 */
	private function generate_edit_link( $menu_item_id, $content_type ) {
		if ( ! isset( $_REQUEST['_nonce'] ) || ! wp_verify_nonce( $_REQUEST['_nonce'], $this->nonce_key ) ) { // phpcs:ignore
			return false;
		}

		$menu_id = isset( $_REQUEST['menu'] ) ? intval( $_REQUEST['menu'] ) : 0;

		$edit_links = [
			'elementor' => defined('ELEMENTOR_VERSION') ? add_query_arg(
				[
					'post'        => get_post_meta( $menu_item_id, 'jet-menu-item', true ),
					'action'      => 'elementor',
					'context'     => 'jet-menu',
					'parent_menu' => $menu_id,
					'_nonce'      => wp_create_nonce( $this->nonce_key ),
				],
				admin_url( 'post.php' )
			) : false,
			'default' => get_edit_post_link( get_post_meta( $menu_item_id, 'jet-menu-item-block-editor', true ), '' ),
			// Add other $content_type and relevant link generation here
		];

		return isset( $edit_links[ $content_type ] ) ? $edit_links[ $content_type ] : false;
	}

	/**
	 * Create a new content post based on content type and name.
	 *
	 * @param string $content_type The type of content.
	 * @param string $content_name The name of the content.
	 * @param int $menu_item_id The ID of the menu item.
	 * @return int|WP_Error The ID of the newly created content post or a WP_Error object on failure.
	 */
	private function create_new_content( $content_type, $content_name, $menu_item_id ) {
		$post_title = ! empty( $content_name ) ? $content_name : "{$content_type}-mega-item-" . $menu_item_id;

		$new_content_id = wp_insert_post([
			'post_title'  => $post_title,
			'post_status' => 'publish',
			'post_type'   => $this->slug(),
		]);

		if ( ! is_wp_error( $new_content_id ) ) {
			update_post_meta( $new_content_id, '_jet_menu_content_type', $content_type );

			return $new_content_id;
		}

		return $new_content_id; // Return WP_Error in case of failure.
	}

	public function perform_redirect( $menu_item_id, $content_type, $edit_link ) {
		// Update the '_content_type' meta value for $menu_item_id
		update_post_meta( $menu_item_id, '_content_type', $content_type );

		// Redirect the user to the edit link
		wp_safe_redirect( $edit_link );
		exit;
	}


	/**
	 * @param $popup_id
	 *
	 * @return void
	 */
	public function save_menu_post_type( $template_id, $post, $update ) {

		if ( empty( $post ) || 'jet-menu' !== $post->post_type ) {
			return;
		}

		delete_post_meta( $template_id, '_is_deps_ready' );
		delete_post_meta( $template_id, '_is_script_deps' );
		delete_post_meta( $template_id, '_is_style_deps' );
		delete_post_meta( $template_id, '_is_content_elements' );

		if ( class_exists( '\Jet_Cache\DB_Manager' ) ) {
			\Jet_Cache\DB_Manager::get_instance()->delete_cache_by_instance_id( $template_id, 'jet-menu' );
		}
	}


	/**
	 * Restricts access to a jet-menu type's content for users who can't edit posts.
	 */
	public function restrict_template_access() {
		// Check if the current user can edit posts
		if ( current_user_can( 'edit_posts' ) ) {
			return;  // If they can, allow access to the content
		}

		// Check if the current page is a singular jet-menu type
		if ( is_singular( $this->post_type ) ) {
			// If it is, redirect the user to the home page
			wp_safe_redirect( home_url() );
			exit;
		}
	}
}
