<?php
/**
 * JetPopup post type template
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Export_Import' ) ) {

	/**
	 * Define Jet_Export_Import class
	 */
	class Jet_Export_Import {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Constructor for the class
		 */
		public function __construct() {
			add_filter( 'post_row_actions', [ $this, 'add_export_import_in_dashboard' ], 10, 2 );
			add_action( 'admin_action_jet_popup_import_preset', array( $this, 'import_popup_preset' ) );
			add_action( 'admin_action_jet_popup_create_from_library_preset', array( $this, 'create_from_library_preset' ) );
			add_action( 'admin_init', [ $this, 'popup_export_preset' ] );
		}

		/**
		 * [add_export_import_in_dashboard description]
		 * @param [type]   $actions [description]
		 * @param \WP_Post $post    [description]
		 */
		public function add_export_import_in_dashboard( $actions, \WP_Post $post ) {

			if ( current_user_can( 'edit_post', $post->ID ) && 'jet-popup' === get_post_type( $post->ID ) ) {
				$actions['jet_popup_export'] = sprintf(
					'<a id="jet-popup-export-link" href="%1$s">%2$s</a>',
					$this->get_export_link( $post->ID ),
					__( 'Export Popup', 'jet-popup' )
				);
			}

			return $actions;
		}

		/**
		 * [popup_export_preset description]
		 * @return [type] [description]
		 */
		public function popup_export_preset() {

			if ( ! isset( $_GET['action'] ) ) {
				return;
			}

			if ( 'jet_popup_export_preset' !== $_GET['action'] && ! isset( $_GET['popup_id'] ) ) {
				return;
			}

			$popup_id = isset( $_GET['popup_id'] ) ? absint( wp_unslash( $_GET['popup_id'] ) ) : 0; // phpcs:ignore

			if ( ! current_user_can( 'edit_posts', $popup_id ) ) {
				wp_die( esc_html__( 'Acess denied', 'jet-popup') );
			}

			// Nonce check
			$nonce = ! empty( $_GET['popup_nonce'] ) ? $_GET['popup_nonce'] : false; // phpcs:ignore

			if ( ! $nonce || ! wp_verify_nonce( $nonce, 'jet_popup_export_nonce' ) ) {
				wp_die( esc_html__( 'Link is expired. Please reload page and try again.', 'jet-popup') );
			}

			$this->export_template( $popup_id );
		}

		/**
		 * [get_export_link description]
		 * @param  [type] $popup_id [description]
		 * @return [type]           [description]
		 */
		public function get_export_link( $popup_id ) {
			return add_query_arg(
				[
					'action'      => 'jet_popup_export_preset',
					'popup_id'    => $popup_id,
					'popup_nonce' => wp_create_nonce( 'jet_popup_export_nonce' ),
				],
				admin_url( 'admin-ajax.php' )
			);
		}

		/**
		 * [export_template description]
		 * @param  [type] $popup_id [description]
		 * @return [type]           [description]
		 */
		public function export_template( $popup_id ) {
			$file_data = $this->prepare_popup_export( $popup_id );

			header( 'Pragma: public' );
			header( 'Expires: 0' );
			header( 'Cache-Control: public' );
			header( 'Content-Description: File Transfer' );
			header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
			header( 'Content-Type: application/octet-stream' );
			header( 'Content-Disposition: attachment; filename="'. $file_data['name'] . '"' );
			header( 'Content-Transfer-Encoding: binary' );

			session_write_close();

			// Output file contents.
			echo $file_data['content']; // phpcs:ignore

			die();
		}

		/**
		 * [prepare_popup_export description]
		 * @param  [type] $popup_id [description]
		 * @return [type]           [description]
		 */
		public function prepare_popup_export( $popup_id ) {
			$popup_data = [];

			$popup_conditions_data = jet_popup()->conditions_manager->get_popup_conditions( $popup_id );

			$popup_data['_conditions'] = $popup_conditions_data['conditions'];
			$popup_data['_relation_type'] = $popup_conditions_data['relationType'];
			$popup_data['_content_type'] = jet_popup()->post_type->get_popup_content_type( $popup_id );
			$popup_data['_settings'] = jet_popup()->settings->get_popup_settings( $popup_id );
			$popup_data['_styles'] = jet_popup()->settings->get_popup_styles( $popup_id );

			$post_data = get_post( $popup_id );
			$content = $post_data->post_content;
			$popup_data['content'] = $content;

			$export_data = [
				'version' => jet_popup()->get_version(),
				'title'   => get_the_title( $popup_id ),
			];

			$export_data += $popup_data;

			$export_data = apply_filters( 'jet-popup/export-import/export-data', $export_data, $popup_id );

			return [
				'name'    => 'jet-popup-' . $export_data['_content_type'] . '-' . $popup_id . '(' . date( 'Y-m-d-h-i-s' ) . ')' . '.json',
				'content' => wp_json_encode( $export_data ),
			];
		}

		/**
		 * [import_popup_preset description]
		 * @return [type] [description]
		 */
		public function import_popup_preset() {
			// phpcs:disable WordPress.Security.NonceVerification
			if ( ! current_user_can( 'import' ) ) {
				wp_die( esc_html__( 'You don\'t have permissions to do this', 'jet-popup' ) );
			}
			if ( empty( $_FILES ) ) {
				wp_die( esc_html__( 'File not passed', 'jet-popup' ) );
			}
			if ( ! empty( $_FILES['file'] ) ) {
				$file = $_FILES['file']; // phpcs:ignore
			} else {
				wp_die( esc_html__( 'File not passed', 'jet-popup' ) );
			}
			// phpcs:enable WordPress.Security.NonceVerification

			if ( 'application/json' !== $file['type'] ) {
				wp_die( esc_html__( 'Format not allowed', 'jet-popup' ) );
			}

			$file_content = file_get_contents( $file['tmp_name'] );
			$import_data = json_decode( $file_content, true );

			if ( ! $import_data ) {
				wp_die( esc_html__( 'No data found in file', 'jet-popup' ) );
			}

			$new_popup_data = $this->get_new_popup_args( $import_data );

			$new_popup_id = wp_insert_post( $new_popup_data, true );

			if ( ! $new_popup_id ) {
				wp_die(
					esc_html__( 'Can\'t create popup. Please try again', 'jet-popup' ),
					esc_html__( 'Error', 'jet-popup' )
				);
			}

			$content_type = isset( $import_data[ '_content_type' ] ) ? $import_data[ '_content_type' ] : 'default';
			$conditions = isset( $import_data[ '_conditions' ] ) ? $import_data[ '_conditions' ] : [];
			$relation_type = isset( $import_data[ '_relation_type' ] ) ? $import_data[ '_relation_type' ] : [];

			jet_popup()->conditions_manager->update_site_popup_conditions( $new_popup_id, $conditions, $relation_type );

			switch ( $content_type ) {
				case 'default':
					$redirect = get_edit_post_link( $new_popup_id, '' );
					break;
				case 'elementor':
					$redirect = \Elementor\Plugin::$instance->documents->get( $new_popup_id )->get_edit_url();
					break;
			}

			wp_redirect( $redirect ); // phpcs:ignore

			die();
		}

		/**
		 * @param $import_data
		 * @return array
		 */
		public function get_new_popup_args( $import_data = false ) {
			$name = $import_data[ 'title' ];
			$content = $import_data[ 'content' ];
			$content_type = $import_data[ '_content_type' ];
			$conditions = isset( $import_data[ '_conditions' ] ) ? $import_data[ '_conditions' ] : [];
			$relation_type = isset( $import_data[ '_relation_type' ] ) ? $import_data[ '_relation_type' ] : [];

			$meta_input = [
				'_content_type'  => $content_type,
				'_conditions'    => $conditions,
				'_relation_type' => $relation_type,
				'_settings'      => wp_parse_args( $import_data[ '_settings' ], jet_popup()->settings->get_popup_default_settings() ),
				'_styles'        => wp_parse_args( $import_data[ '_styles' ], jet_popup()->settings->get_popup_default_styles() ),
			];

			$popup_args = [];

			switch ( $content_type ) {
				case 'default':

					if ( ! empty( $content ) ) {
						$popup_args = [
							'post_content' => $content,
						];
					}

					$meta_fields = [
						'_elementor_template_type' => jet_popup()->post_type->slug(), // Elementor Compatibility / add doc type meta field
					];

					$meta_input = wp_parse_args( $meta_input, $meta_fields );

					break;
				case 'elementor':

					if ( !\Jet_Popup_Utils::has_elementor() ) {
						return [
							'type'     => 'error',
							'message'  => __( 'Elementor plugin not active.', 'jet-popup' ),
							'popup_id' => false,
						];
					}

					$documents = \Elementor\Plugin::instance()->documents;
					$doc_type = $documents->get_document_type( jet_popup()->post_type->slug() );

					if ( ! $doc_type ) {
						return [
							'type'     => 'error',
							'message'  => __( 'Incorrect doc type.', 'jet-popup' ),
							'popup_id' => false,
						];
					}

					$elementor_content = $this->process_export_import_content( $content, 'on_import' );

					$meta_fields = [
						'_elementor_edit_mode'     => 'builder',
						$doc_type::TYPE_META_KEY   => jet_popup()->post_type->slug(),
						'_elementor_data'          => wp_slash( json_encode( $elementor_content ) ),
						'_elementor_page_settings' => $import_data['page_settings'],
					];

					$meta_input = wp_parse_args( $meta_input, $meta_fields );

					break;
			}

			$new_popup_args = wp_parse_args( [
				'post_status' => 'publish',
				'post_title'  => $name,
				'post_type'   => jet_popup()->post_type->slug(),
				'meta_input'  => $meta_input,
			], $popup_args );

			return apply_filters( 'jet-popup/export-import/new-popup-args', $new_popup_args, $import_data );
		}

		/**
		 * [create_from_library_preset description]
		 * @return [type] [description]
		 */
		public function create_from_library_preset() {

			if ( ! current_user_can( 'edit_posts' ) ) {
				wp_die(
					esc_html__( 'You don\'t have permissions to do this', 'jet-popup' ),
					esc_html__( 'Error', 'jet-popup' )
				);
			}

			$preset = isset( $_REQUEST['preset'] ) ? esc_attr( $_REQUEST['preset'] ) : false; // phpcs:ignore

			if ( ! $preset ) {
				wp_die(
					esc_html__( 'Stop Cheating!', 'jet-popup' ),
					esc_html__( 'Error', 'jet-popup' )
				);
			}

			$response_data = $this->get_preset_remote_request( $preset );

			if ( ! $response_data['success']) {
				wp_die(
					esc_html__( 'Api Server Error', 'jet-popup' ),
					esc_html__( 'Error', 'jet-popup' )
				);
			}

			$import_data = json_decode( $response_data['data'], true );

			if ( ! $import_data ) {
				wp_die( esc_html__( 'No data found in file', 'jet-popup' ) );
			}

			$new_popup_data = $this->get_new_popup_args( $import_data );

			$new_popup_id = wp_insert_post( $new_popup_data, true );

			if ( ! $new_popup_id ) {
				wp_die(
					esc_html__( 'Can\'t create popup. Please try again', 'jet-popup' ),
					esc_html__( 'Error', 'jet-popup' )
				);
			}

			$content_type = isset( $import_data[ '_content_type' ] ) ? $import_data[ '_content_type' ] : 'default';
			$conditions = isset( $import_data[ '_conditions' ] ) ? $import_data[ '_conditions' ] : [];
			$relation_type = isset( $import_data[ '_relation_type' ] ) ? $import_data[ '_relation_type' ] : [];

			jet_popup()->conditions_manager->update_site_popup_conditions( $new_popup_id, $conditions, $relation_type );

			switch ( $content_type ) {
				case 'default':
					$redirect = get_edit_post_link( $new_popup_id, '' );
					break;
				case 'elementor':
					$redirect = \Elementor\Plugin::$instance->documents->get( $new_popup_id )->get_edit_url();
					break;
			}

			wp_redirect( $redirect ); // phpcs:ignore

			die();
		}

		/**
		 * [create_new_popup_data description]
		 * @param  [type] $preset_data [description]
		 * @return [type]              [description]
		 */
		public function create_new_popup_data( $preset_data ) {
			$documents      = Elementor\Plugin::instance()->documents;
			$doc_type       = $documents->get_document_type( jet_popup()->post_type->slug() );
			$popup_content  = $preset_data[ 'content' ];
			$popup_settings = $preset_data[ 'page_settings' ];
			$popup_content  = $this->process_export_import_content( $popup_content, 'on_import' );

			$post_data = [
				'post_title' => ! empty( $preset_data['title'] ) ? $preset_data['title'] : __( 'New Popup', 'jet-popup' ),
				'post_type'  => jet_popup()->post_type->slug(),
				'meta_input' => [
					'_elementor_edit_mode'     => 'builder',
					$doc_type::TYPE_META_KEY   => jet_popup()->post_type->slug(),
					'_elementor_data'          => wp_slash( json_encode( $popup_content ) ),
					'_elementor_page_settings' => $popup_settings,
					'_settings'                => jet_popup()->settings->merge_with_defaults_settings( $popup_settings ),
					'_content_type'            => isset( $preset_data[ 'content_type' ] ) ? $preset_data[ 'content_type' ] : 'elementor',
					'_conditions'              => isset( $preset_data['_conditions'] ) ? $preset_data['_conditions'] : [],
					'_relation_type'           => isset( $preset_data['_relation_type'] ) ? $preset_data['_relation_type'] : [],
				],
			];

			$popup_id = wp_insert_post( $post_data );

			jet_popup()->conditions_manager->update_site_popup_conditions( $popup_id, [], 'or' );

			if ( ! $popup_id ) {
				wp_die(
					esc_html__( 'Can\'t create preset. Please try again', 'jet-popup' ),
					esc_html__( 'Error', 'jet-popup' )
				);
			}

			wp_redirect( jet_popup()->elementor()->documents->get( $popup_id )->get_edit_url() ); // phpcs:ignore

			exit();
		}

		/**
		 * Process content for export/import.
		 *
		 * Process the content and all the inner elements, and prepare all the
		 * elements data for export/import.
		 *
		 * @since 1.5.0
		 * @access protected
		 *
		 * @param array  $content A set of elements.
		 * @param string $method  Accepts either `on_export` to export data or
		 *                        `on_import` to import data.
		 *
		 * @return mixed Processed content data.
		 */
		protected function process_export_import_content( $content, $method ) {
			return ELementor\Plugin::$instance->db->iterate_data(
				$content, function( $element_data ) use ( $method ) {
					$element = ELementor\Plugin::$instance->elements_manager->create_element_instance( $element_data );

					// If the widget/element isn't exist, like a plugin that creates a widget but deactivated
					if ( ! $element ) {
						return null;
					}

					return $this->process_element_export_import_content( $element, $method );
				}
			);
		}

		/**
		 * Process single element content for export/import.
		 *
		 * Process any given element and prepare the element data for export/import.
		 *
		 * @since 1.5.0
		 * @access protected
		 *
		 * @param Controls_Stack $element
		 * @param string         $method
		 *
		 * @return array Processed element data.
		 */
		protected function process_element_export_import_content( $element, $method ) {

			$element_data = $element->get_data();

			if ( method_exists( $element, $method ) ) {
				// TODO: Use the internal element data without parameters.
				$element_data = $element->{$method}( $element_data );
			}

			foreach ( $element->get_controls() as $control ) {
				$control_class = ELementor\Plugin::$instance->controls_manager->get_control( $control['type'] );

				// If the control isn't exist, like a plugin that creates the control but deactivated.
				if ( ! $control_class ) {
					return $element_data;
				}

				if ( method_exists( $control_class, $method ) ) {
					$element_data['settings'][ $control['name'] ] = $control_class->{$method}( $element->get_settings( $control['name'] ), $control );
				}
			}

			return $element_data;
		}

		/**
		 * [reset_popup_conditions description]
		 * @param  [type] $popup_settings [description]
		 * @return [type]                 [description]
		 */
		public function reset_popup_conditions( $popup_settings ) {

			foreach ( $popup_settings as $condition => $value ) {

				if ( false !== strpos( $condition, 'conditions_' ) ) {
					unset( $popup_settings[ $condition ] );
				}
			}

			return $popup_settings;
		}

		/**
		 * Retrieve the raw response from the HTTP request using the GET method.
		 *
		 * @since  1.0.0
		 * @return array|WP_Error
		 */
		public function get_preset_remote_request( $preset_id ) {
			$preset_end_point = apply_filters( 'jet-popup/preset-endpoint', 'https://crocoblock.com/interactive-popups/wp-json/croco/v1/install-preset/' );
			//$preset_end_point = apply_filters( 'jet-popup/preset-endpoint', 'https://crocoblock-dev:8890//wp-json/croco/v1/install-preset/' );

			$url = $preset_end_point . $preset_id;

			$response = wp_remote_get( $url, array(
				'timeout'   => 60,
				'sslverify' => false
			) );

			$response_code = wp_remote_retrieve_response_code( $response );

			if ( '' === $response_code ) {
				return new \WP_Error;
			}

			$result = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( ! is_array( $result ) ) {
				return new \WP_Error;
			}

			return $result;
		}

		/**
		 * [predesigned_popups description]
		 * @return [type] [description]
		 */
		public function predesigned_popups() {

			$base_url = jet_popup()->plugin_url( 'templates/dummy-popups/' );
			$base_dir = jet_popup()->plugin_path( 'templates/dummy-popups/' );

			return apply_filters( 'jet-popup/predesigned-popups', [
				'popup-1' => [
					'title'    => __( 'Classic', 'jet-popup' ),
					'content'  => $base_dir . 'popup-1/preset.json',
					'thumb'    => $base_url . 'popup-1/thumbnail.png',
				],
				'popup-2' => [
					'title'    => __( 'Slide In', 'jet-popup' ),
					'content'  => $base_dir . 'popup-2/preset.json',
					'thumb'    => $base_url . 'popup-2/thumbnail.png',
				],
				'popup-3' => [
					'title'   => __( 'Bar', 'jet-popup' ),
					'content' => $base_dir . 'popup-3/preset.json',
					'thumb'   => $base_url . 'popup-3/thumbnail.png',
				],
				'popup-4' => [
					'title'   => __( 'Bordering', 'jet-popup' ),
					'content' => $base_dir . 'popup-4/preset.json',
					'thumb'   => $base_url . 'popup-4/thumbnail.png',
				],
				'popup-5' => [
					'title'   => __( 'Full View', 'jet-popup' ),
					'content' => $base_dir . 'popup-5/preset.json',
					'thumb'   => $base_url . 'popup-5/thumbnail.png',
				],
				'popup-6' => [
					'title'   => __( 'Full Width', 'jet-popup' ),
					'content' => $base_dir . 'popup-6/preset.json',
					'thumb'   => $base_url . 'popup-6/thumbnail.png',
				],
			] );
		}

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
	}

}
