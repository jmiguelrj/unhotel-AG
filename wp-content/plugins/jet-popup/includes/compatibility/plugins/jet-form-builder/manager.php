<?php
namespace Jet_Popup\Compatibility;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Compatibility Manager
 */
class Jet_Form_Builder {

	/**
	 * Include files
	 */
	public function load_files() {}

	/**
	 * @param $render_data
	 * @param $popup_id
	 * @param $widgets
	 * @param $content_type
	 * @return mixed
	 */
	public function modify_render_data( $render_data, $popup_id, $content_type ) {
		$abort = apply_filters( 'jet-popup/compatibility/jfb/prevent-enqueue-wp-editor', false, $render_data, $popup_id, $content_type );

		if ( $abort ) {
			return $render_data;
		}
		$popup_settings = jet_popup()->settings->get_popup_settings( $popup_id );

		if ( ! filter_var( $popup_settings['jet_popup_use_ajax'], FILTER_VALIDATE_BOOLEAN ) ) {
			return $render_data;
		}

		$content_elements = $render_data['contentElements'];

		if ( 'elementor' === $content_type && in_array( 'jet-form-builder-form', $content_elements ) ) {
			wp_enqueue_editor();
		}

		if ( 'default' === $content_type && in_array( 'jet-forms/form-block', $content_elements ) ) {
			wp_enqueue_editor();
		}

		return $render_data;

	}

	/**
	 * @param $blocks
	 * @return mixed
	 */
	public function modify_not_supported_blocks( $blocks ) {
		$not_supported_blocks = [
			'jet-forms/calculated-field',
			'jet-forms/checkbox-field',
			'jet-forms/color-picker-field',
			'jet-forms/conditional-block',
			'jet-forms/date-field',
			'jet-forms/datetime-field',
			'jet-forms/form-break-field',
			'jet-forms/form-break-start',
			'jet-forms/group-break-field',
			'jet-forms/heading-field',
			'jet-forms/hidden-field',
			'jet-forms/map-field',
			'jet-forms/media-field',
			'jet-forms/number-field',
			'jet-forms/progress-bar',
			'jet-forms/radio-field',
			'jet-forms/range-field',
			'jet-forms/repeater-field',
			'jet-forms/select-field',
			'jet-forms/submit-field',
			'jet-forms/text-field',
			'jet-forms/textarea-field',
			'jet-forms/time-field',
			'jet-forms/wysiwyg-field',
		];

		return array_merge( $blocks, $not_supported_blocks );
	}

	public $option_key;

	/**
	 * [__construct description]
	 */
	public function __construct() {

		if ( ! defined( 'JET_FORM_BUILDER_VERSION' ) ) {
			return false;
		}

		$this->load_files();

		add_filter( 'jet-plugins/render/render-data', [ $this, 'modify_render_data' ], 10, 4 );
		add_filter( 'jet-popup/block-manager/not-supported-blocks', [ $this, 'modify_not_supported_blocks' ] );

		$this->option_key = 'popup_to_forms_map';

		add_action( 'post_updated', [ $this, 'on_form_update' ], 10, 3 );
		add_action( 'save_post', [ $this, 'on_popup_save' ], 10, 3 );

	}

	public function on_form_update( $post_id, $post_after, $post_before ) {

		if ( get_post_type( $post_id ) !== 'jet-form-builder' ) {
			return;
		}
	
		$map = get_option( $this->option_key, [] );
	
		foreach ( $map as $popup_id => $form_ids ) {
			if ( is_array( $form_ids ) && in_array( $post_id, $form_ids, true ) ) {
				$this->clear_popup_cache( $popup_id );
			}
		}
	}
	
	public function on_popup_save( $post_id, $post, $update ) {
	
		if ( $post->post_type !== 'jet-popup' ) {
			return;
		}
	
		$form_ids = $this->extract_form_ids_from_content( $post->post_content );
	
		$map = get_option( $this->option_key, [] );
	
		if ( ! isset( $map[ $post_id ] ) || $map[ $post_id ] !== $form_ids ) {
			$map[ $post_id ] = $form_ids;
			update_option( $this->option_key, $map );
		}
	}
	
	private function extract_form_ids_from_content( $content ) {
		if ( preg_match_all( '/data-form-id=["\']?(\d+)/i', $content, $matches ) ) {
			$form_ids = array_map( 'intval', $matches[1] );
			return $form_ids;
		}

		if ( preg_match_all( '/"(?:formId|form_id)":\s*(\d+)/i', $content, $matches ) ) {
			$form_ids = array_map( 'intval', $matches[1] );
			return $form_ids;
		}

		return [];
	}

	private function clear_popup_cache( $popup_id ) {
		if ( class_exists( '\Jet_Cache\Manager' ) ) {
			delete_post_meta( $popup_id, '_is_deps_ready' );
			delete_post_meta( $popup_id, '_is_script_deps' );
			delete_post_meta( $popup_id, '_is_style_deps' );
			delete_post_meta( $popup_id, '_is_content_elements' );
			delete_transient( md5( sprintf( 'jet_popup_render_content_data_styles_%s', $popup_id ) ) );
			delete_transient( md5( sprintf( 'jet_popup_render_content_data_scripts_%s', $popup_id ) ) );

			\Jet_Cache\Manager::get_instance()->db_manager->delete_cache_by_instance_id( $popup_id, 'jet-popup' );
		}
	}

}
