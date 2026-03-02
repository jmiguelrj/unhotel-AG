<?php
namespace Jet_Theme_Core\Theme_Builder;
/**
 * Class description
 *
 * @package   package_name
 * @author    Cherry Team
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Page_Templates_Manager {

	/**
	 * A reference to an instance of this class.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    Jet_Theme_Core
	 */
	private static $instance = null;

	/**
	 * @var string
	 */
	public $page_template_conditions_option_key = 'jet_page_template_conditions';

	/**
	 * @var array
	 */
	public $page_template_list = [];

	/**
	 * Returns the instance.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return Jet_Theme_Core
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Templates post type slug
	 *
	 * @return string
	 */
	public function get_slug() {
		return jet_theme_core()->theme_builder->post_type;
	}

	/**
	 * @return array
	 */
	public function get_site_page_template_conditions() {
		$page_template_conditions = get_option( $this->page_template_conditions_option_key, [] );

		if ( empty( $page_template_conditions ) ) {
			return [];
		}

		$page_template_conditions = array_filter( $page_template_conditions, function ( $id ) {
			return get_post_status( $id );
		}, ARRAY_FILTER_USE_KEY );

		return array_map( function( $page_template_data ) {

			if ( ! isset( $page_template_data['conditions'] ) ) {
				return $page_template_data = [
					'conditions'    => $page_template_data,
					'relation_type' => 'or',
				];
			}

			return $page_template_data;
		}, $page_template_conditions );

	}

	/**
	 * @param false $template_type
	 * @param string $content_type
	 * @param string $template_name
	 *
	 * @return array
	 */
	public function create_page_template( $template_name = '', $template_conditions = [], $template_layout = [], $template_type = 'unassigned', $relation_type = 'or' ) {

		if ( ! current_user_can( 'edit_posts' ) ) {
			return [
				'type'     => 'error',
				'message'  => __( 'You don\'t have permissions to do this', 'jet-theme-core' ),
			];
		}

		$default_layout = [
			'header' => [
				'id'       => false,
				'enabled'  => true,
				'override' => true,
			],
			'body' => [
				'id'       => false,
				'enabled'  => true,
				'override' => true,
			],
			'footer' => [
				'id'       => false,
				'enabled'  => true,
				'override' => true,
			],
		];

		$template_layout = wp_parse_args( $template_layout, $default_layout );

		$meta_input = [
			'_conditions'    => $template_conditions,
			'_relation_type' => $relation_type,
			'_layout'        => $template_layout,
			'_type'          => $template_type,
		];

		$post_title = $template_name;

		$post_data = array(
			'post_status' => 'publish',
			'post_title'  => $post_title,
			'post_type'   => $this->get_slug(),
			'meta_input'  => $meta_input,
		);

		$template_id = wp_insert_post( $post_data, true );

		if ( empty( $template_name ) ) {
			$post_title = $post_title . 'Page Template #' . $template_id;
		} else {
			$post_title = $post_title . ' #' . $template_id;
		}

		wp_update_post( [
			'ID'         => $template_id,
			'post_title' => $post_title,
		] );

		if ( $template_id ) {

			// Update all site page template conditions.
			$page_template_conditions = $this->get_site_page_template_conditions();

			if ( ! isset( $page_template_conditions[ $template_id ] ) ) {
				$page_template_conditions[ $template_id ] = [
					'conditions'    => [],
					'relation_type' => $relation_type,
				];
				update_option( $this->page_template_conditions_option_key, $page_template_conditions, true );
			}

			$page_template_list = $this->get_page_template_list();

			return [
				'type'     => 'success',
				'message'  => __( 'Page template has been created', 'jet-theme-core' ),
				'data' => [
					'newTemplateId' => $template_id,
					'list'          => $page_template_list,
				],
			];
		} else {
			return [
				'type'     => 'error',
				'message'  => __( 'Server Error. Please try again later.', 'jet-theme-core' ),
				'data' => [],
			];
		}
	}

	/**
	 * @param $template_id
	 *
	 * @return array
	 */
	public function delete_page_template( $template_id ) {

		if ( ! current_user_can( 'edit_posts' ) ) {
			return [
				'type'     => 'error',
				'message'  => __( 'You don\'t have permissions to do this', 'jet-theme-core' ),
			];
		}

		if ( ! $template_id ) {
			return [
				'type'     => 'error',
				'message'  => __( 'Invalid template id', 'jet-theme-core' ),
			];
		}

		$delete = wp_delete_post( $template_id, true );
		$page_template_list = $this->get_page_template_list();


		if ( $delete ) {

			// Update all site page template conditions.
			$page_template_conditions = $this->get_site_page_template_conditions();

			if ( isset( $page_template_conditions[ $template_id ] ) ) {
				unset( $page_template_conditions[ $template_id ] );
				update_option( $this->page_template_conditions_option_key, $page_template_conditions, true );
			}

			return [
				'type'     => 'success',
				'message'  => __( 'Page template has been deleted', 'jet-theme-core' ),
				'data'     => [
					'list' => $page_template_list,
				],
			];
		} else {
			return [
				'type'     => 'error',
				'message'  => __( 'Server Error. Please try again later.', 'jet-theme-core' ),
				'data'     => [
					'list' => $page_template_list,
				],
			];
		}
	}

	/**
	 * @param $template_id
	 *
	 * @return array
	 */
	public function copy_page_template( $template_id ) {

		if ( ! current_user_can( 'edit_posts' ) ) {
			return [
				'type'     => 'error',
				'message'  => __( 'You don\'t have permissions to do this', 'jet-theme-core' ),
			];
		}

		if ( ! $template_id ) {
			return [
				'type'     => 'error',
				'message'  => __( 'Invalid template id', 'jet-theme-core' ),
			];
		}

		$new_template_name   = get_the_title( $template_id ) . ' copy';

		$template_data = get_post( $template_id );

		$new_template    = array(
			'post_title'  => $new_template_name,
			'post_status' => 'publish',
			'post_type'   => $template_data->post_type,
			'post_author' => get_current_user_id(),
		);

		// Create new page template
		$new_template_id = wp_insert_post( $new_template );

		// Copy page template metadata
		$data = get_post_custom( $template_id );

		foreach ( $data as $key => $values ) {
			foreach ( $values as $value) {
				add_post_meta( $new_template_id, $key, maybe_unserialize( $value ) );
			}
		}

		if ( $new_template_id ) {

			// Update all site page template conditions.
			$page_template_conditions = $this->get_site_page_template_conditions();

			if ( isset( $page_template_conditions[ $template_id ] ) ) {
				$page_template_conditions[ $new_template_id ] = [
					'conditions'    => $this->get_page_template_conditions( $template_id ),
					'relation_type' => $this->get_page_template_relation_type( $template_id ),
				];

				update_option( $this->page_template_conditions_option_key, $page_template_conditions, true );
			}

			$page_template_list = $this->get_page_template_list();

			return [
				'type'     => 'success',
				'message'  => __( 'Page template has been deleted', 'jet-theme-core' ),
				'data' => [
					'list' => $page_template_list,
				],
			];
		} else {
			return [
				'type'     => 'error',
				'message'  => __( 'Server Error. Please try again later.', 'jet-theme-core' ),
				'data' => [],
			];
		}
	}

	/**
	 * @return array
	 */
	public function get_page_template_list( $template_name = false, $order_by = false ) {

		if ( ! empty( $this->page_template_list ) ) {
			return $this->page_template_list;
		}

		$params = [
			'post_type'           => $this->get_slug(),
			'ignore_sticky_posts' => true,
			'posts_per_page'      => -1,
			'suppress_filters'     => false,
		];

		if ( $template_name ) {
			$params['s'] = $template_name;
		}

		$structure_types = jet_theme_core()->structures->get_structure_types();

		$page_templates_data = get_posts( $params );

		if ( ! empty( $page_templates_data ) ) {
			foreach ( $page_templates_data as $template ) {
				$template_id = $template->ID;
				$author_id = $template->post_author;
				$author_data = get_userdata( $author_id );
				$type = get_post_meta( $template_id, '_type', true );

				if ( ! in_array( $type, $structure_types ) ) {
					continue;
				}

				$this->page_template_list[] = [
					'id'           => $template_id,
					'templateName' => $template->post_title,
					'date'         => [
						'raw'          => $template->post_date,
						'format'       => get_the_date( '', $template_id ),
						'lastModified' => $template->post_modified,
					],
					'author'       => [
						'id'   => $author_id,
						'name' => $author_data->user_login,
					],
					'type'         => $type,
					'conditions'   => $this->get_page_template_conditions( $template_id ),
					'relationType' => $this->get_page_template_relation_type( $template_id ),
					'layout'       => $this->get_page_template_layout( $template_id ),
					'node'         => $this->get_page_template_node( $template_id ),
					'exportLink'   => \Jet_Theme_Core\Theme_Builder\Page_Templates_Export_Import::get_instance()->get_page_template_export_link( $template_id )
				];
			}
		}

		return $this->page_template_list;
	}

	/**
	 * @param false $page_template_id
	 * @param false $structure
	 * @param array $structure_data
	 *
	 * @return array
	 */
	public function update_page_template_data( $id = false, $data = false ) {

		if ( ! $id || empty( $data ) ) {
			return [
				'type'     => 'error',
				'message'  => __( 'Server Error', 'jet-theme-core' ),
				'data' => [],
			];
		}

		if ( isset( $data['conditions'] ) ) {
			$this->update_page_template_conditions( $id, $data['conditions'] );
		}

		if ( isset( $data['relationType'] ) ) {
			$this->update_page_template_relation_type( $id, $data['relationType'] );
		}

		if ( isset( $data['layout'] ) ) {
			$this->update_page_template_layout( $id, $data['layout'] );
		}

		if ( isset( $data['type'] ) ) {
			$this->update_page_template_type( $id, $data['type'] );
		}

		if ( isset( $data['node'] ) ) {
			$this->update_page_template_node( $id, $data['node'] );
		}

		if ( isset( $data['templateName'] ) ) {
			wp_update_post( [
				'ID'         => $id,
				'post_title' => $data['templateName'],
			] );
		}

		return [
			'type'     => 'success',
			'message'  => __( 'Page template layout updated', 'jet-theme-core' ),
			'data' => [],
		];
	}

	/**
	 * @param false $page_template_id
	 * @param array $conditions
	 */
	public function update_page_template_conditions( $page_template_id = false, $conditions = [] ) {

		update_post_meta( $page_template_id, '_conditions', $conditions );

		// Update all site page template conditions.
		$page_template_conditions = $this->get_site_page_template_conditions();

		if ( isset( $page_template_conditions[ $page_template_id ] ) ) {
			$page_template_conditions[ $page_template_id ]['conditions'] = $conditions;
		}

		update_option( $this->page_template_conditions_option_key, $page_template_conditions, true );
	}

	/**
	 * @param $page_template_id
	 * @param $relation_type
	 *
	 * @return void
	 */
	public function update_page_template_relation_type( $page_template_id = false, $relation_type = 'of' ) {

		update_post_meta( $page_template_id, '_relation_type', $relation_type );

		// Update all site page template conditions.
		$page_template_conditions = $this->get_site_page_template_conditions();

		if ( isset( $page_template_conditions[ $page_template_id ] ) ) {
			$page_template_conditions[ $page_template_id ]['relation_type'] = $relation_type;
		}

		update_option( $this->page_template_conditions_option_key, $page_template_conditions, true );
	}

	/**
	 * @param false $id
	 * @param array $layout
	 */
	public function update_page_template_layout( $id = false, $layout = [] ) {

		if ( ! $id || empty( $layout ) ) {
			return false;
		}

		return update_post_meta( $id, '_layout', $layout );
	}

	/**
	 * @param $template_id
	 *
	 * @return mixed
	 */
	public function get_page_template_layout( $template_id ) {
		$layout = get_post_meta( $template_id, '_layout', true );

		if ( ! empty( $layout ) ) {
			$is_modify = false;

			foreach ( $layout as $structure => $structure_data ) {

				if ( ! empty( $structure_data['id'] ) && 'publish' !== get_post_status( $structure_data['id'] ) ) {
					$layout[ $structure ]['id'] = false;
					$is_modify = true;
				}
			}

			if ( $is_modify ) {
				$this->update_page_template_layout( $template_id, $layout );
			}
		}

		return $layout;
	}

	/**
	 * @param false $id
	 * @param array $layout
	 */
	public function update_page_template_type( $id = false, $type = false ) {

		if ( ! $id || ! $type ) {
			return false;
		}

		return update_post_meta( $id, '_type', $type );
	}

	/**
	 * @param $template_id
	 * @return false|mixed
	 */
	public function get_page_template_node( $template_id ) {
		$node = get_post_meta( $template_id, '_node', true );

		if ( empty( $node ) ) {
			return false;
		}

		return $node;
	}

	/**
	 * @param false $id
	 * @param array $layout
	 */
	public function update_page_template_node( $id = false, $node = false ) {

		if ( ! $id || ! $node ) {
			return false;
		}

		return update_post_meta( $id, '_node', $node );
	}

	/**
	 * @param false $id
	 * @param false $data
	 *
	 * @return array
	 */
	public function update_template_data( $id = false,  $data = false ) {

		if ( ! $id || empty( $data ) ) {
			return [
				'type'     => 'error',
				'message'  => __( 'Server Error', 'jet-theme-core' ),
				'data' => [],
			];
		}

		if ( isset( $data['title'] ) ) {

			wp_update_post( [
				'ID'         => $id,
				'post_title' => $data['title'],
			] );
		}

		return [
			'type'     => 'success',
			'message'  => __( 'Template layout updated', 'jet-theme-core' ),
			'data' => [],
		];
	}

	/**
	 * @param false $page_template_id
	 *
	 * @return array|mixed
	 */
	public function get_page_template_conditions( $page_template_id = false ) {

		if ( ! $page_template_id ) {
			return [];
		}

		$page_template_conditions = get_post_meta( $page_template_id, '_conditions', true );

		if ( ! empty( $page_template_conditions ) ) {
			$page_template_conditions = array_map( function ( $condition ) {

				if ( ! isset( $condition['subGroupArg'] ) ) {
					$condition['subGroupArg'] = '';
				}

				return $condition;
			}, $page_template_conditions );
		}

		return ! empty( $page_template_conditions ) ? $page_template_conditions : [];
	}

	/**
	 * @param false $page_template_id
	 *
	 * @return array|mixed
	 */
	public function get_page_template_relation_type( $page_template_id = false ) {

		if ( ! $page_template_id ) {
			return [];
		}

		$relation_type = get_post_meta( $page_template_id, '_relation_type', true );

		return ! empty( $relation_type ) ? $relation_type : 'or';
	}

	/**
	 * @param $page_template_id
	 *
	 * @return string
	 */
	public function get_page_template_export_link( $page_template_id ) {
		return add_query_arg(
			[
				'action'           => 'jet_theme_core_export_page_template',
				'page_template_id' => $page_template_id,
			],
			admin_url( 'admin-ajax.php' )
		);
	}

	/**
	 * @param false $template_id
	 *
	 * @return array|false
	 */
	public function get_used_page_templates_for_template( $template_id = false ) {

		if ( ! $template_id ) {
			return false;
		}

		$raw_page_template_list = $this->get_page_template_list();
		$page_template_list = [];

		if ( ! empty( $raw_page_template_list ) ) {

			foreach ( $raw_page_template_list as $key => $page_template_data ) {
				$page_template_layout = $page_template_data['layout'];

				foreach ( $page_template_layout as $layout => $layout_data ) {

					if ( $template_id === $layout_data['id'] ) {

						$page_template_url = add_query_arg( [
							'page'          => 'jet-theme-builder',
							'page_template' => $page_template_data['id'],
						], admin_url( 'admin.php' ) );

						$page_template_list[] = [
							'id'                 => $page_template_data['id'],
							'name'               => $page_template_data[ 'templateName' ],
							'date'               => $page_template_data[ 'date' ][ 'format' ],
							'author'             => $page_template_data[ 'author' ][ 'name' ],
							'type'               => $page_template_data[ 'type' ],
							'theme_builder_link' => $page_template_url,
						];
					}
				}
			}
		}

		if ( empty( $page_template_list ) ) {
			return false;
		}

		return $page_template_list;
	}

	/**
	 * @param false $template_id
	 */
	public function get_used_verbose_page_templates( $template_id = false, $structure_id = false ) {

		$warning_icon = \Jet_Theme_Core\Utils::get_admin_ui_icon( 'warning' );

		$exclude_structures = [ 'jet_section' ];

		if ( in_array( $structure_id, $exclude_structures ) ) {
			return sprintf(
				'<div class="jet-template-library__page-templates-alert warning"><div class="jet-template-library__message"><span>%1$s</span></div></div>',
				__( 'Template cannot be used for JetThemeBuilder', 'jet-theme-core' )
			);
		}

		$used_page_templates = $this->get_used_page_templates_for_template( $template_id );

		if ( empty( $used_page_templates ) ) {
			$warning_icon = \Jet_Theme_Core\Utils::get_admin_ui_icon( 'warning' );

			return sprintf(
				'<div class="jet-template-library__page-templates-alert warning"><div class="jet-template-library__message"><span>%1$s</span></div><a class="jet-template-library__action" href="%2$s" target="_blank">%3$s<span>%4$s</span></a></div>',
				__( 'Template isn\'t used yet', 'jet-theme-core' ),
				\Jet_Theme_Core\Utils::get_theme_bilder_link(),
				\Jet_Theme_Core\Utils::get_admin_ui_icon( 'plus' ),
				__( 'Use template', 'jet-theme-core' )
			);
		}

		$verbose = '';

		foreach ( $used_page_templates as $key => $page_template_data ) {
			$verbose .= sprintf( '<div class="jet-template-library__page-templates-item"><a class="page-template-name" href="%4$s" target="_blank">%1$s</a><i class="page-template-date">%2$s</i><i class="page-template-author">%3$s</i></div>',
				$page_template_data['name'],
				$page_template_data['date'],
				$page_template_data['author'],
				$page_template_data['theme_builder_link']
			);
		}

		$verbose = sprintf(
			'<div class="jet-template-library__page-templates-list">%1$s</div>',
			$verbose
		);

		return sprintf(
			'<div class="jet-template-library__page-templates">%1$s</div>',
			$verbose
		);
	}

	/**
	 * @return mixed
	 */
	public function get_nodes_structure() {
		$conditions = jet_theme_core()->template_conditions_manager->get_conditions();
		$nodes = [];

		foreach ( $conditions as $cid => $instance ) {
			$node_data = $instance->get_node_data();

			if ( ! $node_data ) {
				continue;
			}

			$nodes[] = [
				'id'            => $instance->get_id(),
				'label'         => $instance->get_label(),
				'node'          => $node_data['node'],
				'parentNode'    => $node_data['parent'],
				'nodeLabel'     => $node_data['label'],
				'bodyStructure' => $instance->get_body_structure(),
				'priority'      => $instance->get_priority(),
				'pageTemplates' => [],
				'nodes'         => [],
				'nodesVisible'  => true,
				'nodeInfo'      => isset( $node_data['nodeInfo'] ) ? $node_data['nodeInfo'] : false,
				'inherit'       => isset( $node_data['inherit'] ) ? $node_data['inherit'] : false,
				'previewLink'   => isset( $node_data['previewLink'] ) ? $node_data['previewLink'] : false,
				'subNode'       => isset( $node_data['subNode'] ) ? $node_data['subNode'] : false,
			];
		}

		$node_tree = $this->buildNodeHierarchy( $nodes );

		return $nodes;
	}

	/**
	 * @return mixed|null
	 */
	public function get_root_node_options() {
		$post_types = \Jet_Theme_Core\Utils::get_custom_post_types_options();
		$post_type_options = array_map( function ( $type ) {
			return [
				'label' => $type['label'],
				'value' => 'cpt-archive-' . $type['value'],
			];
		}, $post_types );

		$post_type_options = array_merge( [ [
			'label' => __( 'Post', 'jet-theme-core' ),
			'value' => 'archive-all-post',
		] ], $post_type_options );

		return apply_filters( 'jet-theme-core/theme-builder/root-node-options', [
			[
				'label' => __( 'Entire Site', 'jet-theme-core' ),
				'value' => 'entire',
			],
			[
				'label' => __( 'All Archives', 'jet-theme-core' ),
				'value' => 'archive-all',
			],
			[
				'label' => __( 'Post Types', 'jet-theme-core' ),
				'options' => $post_type_options
			]
		] );
	}

	/**
	 * @param array $nodes
	 * @return array
	 */
	public function buildNodeHierarchy( array $nodes ): array {
		$tree = [];
		$references = [];

		foreach ( $nodes as $node ) {
			$node['nodes'] = [];
			$references[ $node['node'] ] = $node;
		}

		foreach ( $nodes as $node ) {

			if ( ! $node['parentNode'] ) {
				$tree[] = &$references[ $node['node'] ];
			} else {
				$references[ $node['parentNode'] ]['nodes'][] = &$references[ $node['node'] ];
			}
		}

		return $tree;
	}

	/**
	 * @return void
	 */
	public function print_admin_notices() {

		if ( ! isset( $_GET['page'] ) || 'jet-theme-builder' !== $_GET['page'] ) {
			return false;
		}

		$page_templates = $this->findLegacyConditionsPageTemplates( [ 'archive-post-type', 'archive-tax', 'singular-post-type' ] );

		if ( empty( $page_templates ) ) {
			return false;
		}

		\Jet_Dashboard\Dashboard::get_instance()->notice_manager->print_admin_notice( [
			'id' => 'convert-legacy-conditions',
			'icon' => '<svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18 8.985L29.295 28.5H6.705L18 8.985ZM18 3L1.5 31.5H34.5L18 3ZM19.5 24H16.5V27H19.5V24ZM19.5 15H16.5V21H19.5V15Z" fill="url(#paint0_linear_101_2)"/><defs><linearGradient id="paint0_linear_101_2" x1="18" y1="3" x2="18" y2="31.5" gradientUnits="userSpaceOnUse"><stop stop-color="#FFA901"/><stop offset="1" stop-color="#FEDB22"/></linearGradient></defs></svg>',
			'title' => esc_html__( 'Found page templates with legacy type of conditions', 'jet-theme-core' ),
			'description' => esc_html__( 'You can convert legacy conditions to new ones. You can then find these page templates in the template hierarchy tree.', 'jet-theme-core' ),
			'type' => 'warning',
			'button' => [
				'text' => esc_html__( 'Convert Now', 'jet-dashboard' ),
				'url' => add_query_arg(
					[
						'action'           => 'convert_legacy_conditions',
						'nonce'            => wp_create_nonce( 'jet-theme-core-builder-nonce' ),
					],
					admin_url( 'admin-ajax.php' )
				),
				'classes' => [ 'cx-vui-button', 'cx-vui-button--style-accent-border', 'cx-vui-button--size-mini' ],
			],
		] );
	}

	/**
	 * @param $array
	 * @param $subGroup
	 * @return bool
	 */
	public function findLegacyConditionsPageTemplates( $subGroups ) {
		$params = [
			'post_type'           => $this->get_slug(),
			'ignore_sticky_posts' => true,
			'posts_per_page'      => -1,
			'suppress_filters'     => false,
		];

		$page_templates_data = get_posts( $params );

		if ( empty( $page_templates_data ) ) {
			return false;
		}

		$page_templates = [];

		foreach ( $page_templates_data as $item ) {
			$conditions = $this->get_page_template_conditions( $item->ID );

			if ( isset( $conditions ) && is_array( $conditions ) ) {
				foreach ( $conditions as $condition ) {

					if ( isset( $condition['subGroup'] ) && in_array( $condition['subGroup'], $subGroups ) ) {
						$page_templates[] = [
							'id'         => $item->ID,
							'conditions' => $conditions,
						];
					}
				}
			}
		}

		if ( empty( $page_templates ) ) {
			return false;
		}

		return $page_templates;
	}

	/**
	 * @return void
	 */
	public function convert_legacy_conditions() {

		if ( ! isset( $_GET['action'] ) ) {
			return;
		}

		if ( 'convert_legacy_conditions' !== $_GET['action']  ) {
			return;
		}

		if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( $_GET['nonce'], 'jet-theme-core-builder-nonce' ) ) {
			wp_send_json_error( __( 'Page has expired. Please reload this page.', 'jet-theme-core' ) );
		}

		$page_templates = $this->findLegacyConditionsPageTemplates( [ 'archive-post-type', 'archive-tax', 'singular-post-type' ] );

		foreach ( $page_templates as $page_template ) {
			$id = $page_template['id'];
			$conditions = $page_template['conditions'];
			$new_conditions = [];

			foreach ( $conditions as $key => &$condition ) {
				$sub_group = $condition['subGroup'];

				switch ( $sub_group ) {
					case 'archive-post-type':

						if ( 'post' === $condition['subGroupValue'] ) {
							$new_sub_group = 'archive-all-post';
							$new_priority = 10;
						} else {
							$new_sub_group = 'cpt-archive-' . $condition['subGroupValue'];
							$new_priority = 45;
						}

						$new_conditions[] = [
							'id'                   => uniqid( '_' ),
							'include'              => 'true',
							'group'                => 'archive',
							'subGroup'             => $new_sub_group,
							'subGroupValue'        => '',
							'subGroupValueVerbose' => '',
							'priority'             => $new_priority,
						];

						unset( $conditions[ $key ] );
						break;
					case 'archive-tax':

						if ( is_array( $condition['subGroupValue'] ) ) {

							foreach ( $condition['subGroupValue'] as $value ) {
								$new_conditions[] = [
									'id'                   => uniqid( '_' ),
									'include'              => 'true',
									'group'                => 'archive',
									'subGroup'             => 'cpt-taxonomy-' . $value,
									'subGroupValue'        => [ 'all' ],
									'subGroupValueVerbose' => [ 'All' ],
									'priority'             => 45
								];
							}
						}

						unset( $conditions[ $key ] );

						break;

					case 'singular-post-type':
						$new_conditions[] = [
							'id'                   => uniqid( '_' ),
							'include'              => 'true',
							'group'                => 'singular',
							'subGroup'             => 'cpt-single-' . $condition['subGroupValue'],
							'subGroupValue'        => [ 'all' ],
							'subGroupValueVerbose' => [ 'All' ],
							'priority'             => 28
						];

						unset( $conditions[ $key ] );
						break;
				}
			}

			$conditions = array_merge( $conditions, $new_conditions );
			$this->update_page_template_conditions( $id, $conditions );
		}

		$referer = wp_get_referer();

		if ( $referer ) {
			wp_safe_redirect( $referer );
		} else {
			wp_safe_redirect(home_url());
		}

		exit;
	}

	/**
	 * Constructor for the class
	 */
	public function __construct() {
		add_action( 'admin_init', [ $this, 'convert_legacy_conditions' ] );
		add_action( 'admin_notices', [ $this, 'print_admin_notices' ] );
	}

}
