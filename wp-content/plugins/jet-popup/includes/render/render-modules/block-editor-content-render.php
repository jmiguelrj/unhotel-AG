<?php
namespace Jet_Popup\Render;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Block_Editor_Content_Render extends Base_Render {

	/**
	 * [$name description]
	 * @var string
	 */
	protected $name = 'block-editor-template-render';

	/**
	 * @var array
	 */
	public $depended_styles = [];

	/**
	 * [$depended_scripts description]
	 * @var array
	 */
	public $depended_scripts = [];

	/**
	 * [init description]
	 * @return [type] [description]
	 */
	public function init() {}

	/**
	 * [get_name description]
	 * @return [type] [description]
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * @return array
	 */
	public function get_depended_styles() {
		return array_unique( $this->depended_styles );
	}

	/**
	 * @return array
	 */
	public function get_depended_scripts() {
		return array_unique( $this->depended_scripts );
	}

	/**
	 * [render description]
	 * @return [type] [description]
	 */
	public function render() {
		$popup_id = $this->get( 'popup_id' );
		$is_content = $this->get( 'is_content', true );

		if ( ! $popup_id || ! $is_content ) {
			return false;
		}

		$popup_obj = get_post( $popup_id );
		$raw_popup_content = $popup_obj->post_content;

		if ( empty( $raw_popup_content ) ) {
			return false;
		}

		$before_styles_queue  = wp_styles()->queue;
		$before_scripts_queue = wp_scripts()->queue;

		$blocks_popup_content = apply_filters( 'jet-popup/render/block-editor/content', do_blocks( $raw_popup_content ), $popup_id );
		$content = do_shortcode( $blocks_popup_content );

		$after_styles_queue = wp_styles()->queue;
		$after_scripts_queue = wp_scripts()->queue;

		$styles_depends = array_diff( $after_styles_queue, $before_styles_queue );
		$script_depends = array_diff( $after_scripts_queue, $before_scripts_queue );

		$this->depended_styles = array_merge( $this->depended_styles, $styles_depends );
		$this->depended_scripts = array_merge( $this->depended_scripts, $script_depends );

		$this->maybe_enqueue_css();

		echo  $content; // phpcs:disable

	}

	/**
	 * @return array
	 */
	public function get_render_data() {
		$popup_id = $this->get( 'popup_id', false );
		$popup_id = apply_filters( 'jet-popup/popup-generator/before-define-popup-assets/popup-id', $popup_id, $this->get_settings() );

		$popup_content = $this->get_content();
		$styles_data = $this->get_content_style_data( $popup_id );
		$scripts_data = $this->get_content_script_data( $popup_id );
		$content_elements = $this->get_content_elements();

		$render_data = [
			'content'         => $popup_content,
			'contentElements' => $content_elements,
			'styles'          => $styles_data['depends'],
			'scripts'         => $scripts_data['depends'],
			'afterScripts'    => [],
		];

		return $render_data;
	}

	/**
	 * [get_elementor_template_scripts_url description]
	 * @param  [type] $template_id [description]
	 * @return [type]              [description]
	 */
	public function get_content_style_data( $popup_id = false ) {

		if ( ! $popup_id ) {
			return [
				'depends' => [],
			];
		}

		$is_deps = $this->get( 'is_style_deps', true );

		if ( ! $is_deps ) {
			return [
				'depends' => [],
			];
		}

		$data_cache = get_post_meta( $popup_id, '_is_style_deps', true );
		$use_cache  = apply_filters( 'jet-popup/render/render-data/use-cache', true );

		if ( ! empty( $data_cache ) && $use_cache ) {
			return $data_cache;
		}

		$depended_styles = $this->get_depended_styles();

		$styles_data = [
			'depends' => array_map( function ( $handle ) {
				$style_obj = $this->get_registered_style_obj_by_handler( $handle );

				return [
					'handle' => $handle,
					'src'    => $style_obj->src,
					'obj'    => $style_obj
				];
			}, $depended_styles ),
		];

		update_post_meta( $popup_id, '_is_style_deps', $styles_data );

		return $styles_data;
	}

	/**
	 * [get_elementor_template_scripts_url description]
	 * @param  [type] $template_id [description]
	 * @return [type]              [description]
	 */
	public function get_content_script_data( $popup_id = false ) {

		if ( ! $popup_id ) {
			return [
				'depends' => [],
			];
		}

		$is_deps = $this->get( 'is_script_deps', true );

		if ( ! $is_deps ) {
			return [
				'depends' => [],
			];
		}

		$data_cache = get_post_meta( $popup_id, '_is_script_deps', true );
		$use_cache = apply_filters( 'jet-popup/render/render-data/use-cache', true );

		if ( ! empty( $data_cache ) && $use_cache ) {
			return $this->fix_encoded_special_chars( $data_cache );
		}

		$depended_scripts = $this->get_depended_scripts();

		$scripts_data = [
			'depends' => array_map( function ( $handle ) {
				$script_obj = $this->get_registered_script_obj_by_handler( $handle );

				return [
					'handle' => $handle,
					'src'    => $script_obj->src,
					'obj'    => $script_obj
				];
			}, $depended_scripts ),
		];

		update_post_meta( $popup_id, '_is_script_deps', $scripts_data );

		return $scripts_data;
	}

	/**
	 * @param $raw_content
	 * @return void
	 */
	public function get_content_elements() {
		$popup_id = $this->get( 'popup_id' );

		if ( ! $popup_id ) {
			return [];
		}

		$is_content_elements = $this->get( 'is_content_elements', true );

		if ( ! $is_content_elements ) {
			return [];
		}

		$data_cache = get_post_meta( $popup_id, '_is_content_elements', true );
		$use_cache  = apply_filters( 'jet-popup/render/render-data/use-cache', true );

		if ( ! empty( $data_cache ) && $use_cache ) {
			return $data_cache;
		}

		$popup_obj = get_post( $popup_id );
		$raw_content = $popup_obj->post_content;

		if ( empty( $raw_content ) ) {
			return [];
		}

		$blocks_list = parse_blocks( $raw_content );
		$rendered_blocks = [];

		if ( ! empty( $blocks_list ) ) {
			foreach ( $blocks_list as $block_data ) {
				if ( ! empty( $block_data['blockName'] ) ) {
					$rendered_blocks[] = $block_data['blockName'];
				}
			}
		}

		return $rendered_blocks;
	}

	/**
	 * @return void
	 */
	public function get_css() {
		$popup_id = $this->get( 'popup_id' );
		$css_string = $this->render_css( $popup_id );

		return sprintf( '<style id="jet-popup-styles-%1$s" type="text/css">%2$s</style>', $popup_id, $css_string );
	}

	/**
	 * @param $popup_id
	 *
	 * @return string
	 */
	public function render_css( $popup_id = false ) {

		if ( ! $popup_id ) {
			return '';
		}

		$css_vars_string = '';
		$styles = jet_popup()->settings->get_popup_styles( $popup_id );
		$styles_settings = jet_popup()->settings->get_popup_default_styles_settings();

		foreach ( $styles as $setting => $value ) {

			if ( empty( $value ) || ! isset( $styles_settings[ $setting ] ) ) {
				continue;
			}

			switch ( $styles_settings[ $setting ]['type'] ) {
				case 'background':
					$css_vars_string .= $this->get_background_type_vars( $setting, $value );
					break;
				default:
					$css_vars_string .= sprintf( '--jp-%1$s: %2$s;', str_replace('_', '-', $setting ), $value );
					break;
			}
		}

		return sprintf( '#jet-popup-%1$s { %2$s }', $popup_id, $css_vars_string );

	}

	/**
	 * @param $setting
	 * @param $options
	 * @return string
	 */
	public function get_background_type_vars( $setting, $options ) {
		$css_vars_string = '';

		if ( empty( $setting ) || empty( $options ) || ! isset( $options['type'] ) ) {
			return $css_vars_string;
		}

		switch ( $options['type'] ) {
			case 'classic':
				$css_vars_string .= sprintf( '--jp-%1$s-%2$s: %3$s;', str_replace('_', '-', $setting ), 'color', $options['color'] );

				if ( ! empty( $options['bg_image_url'] ) ) {
					$css_vars_string .= sprintf( '--jp-%1$s-%2$s: url(%3$s);', str_replace('_', '-', $setting ), 'image', $options['bg_image_url'] );
				}

				$css_vars_string .= sprintf( '--jp-%1$s-%2$s: %3$s;', str_replace('_', '-', $setting ), 'position', $options['bg_position'] );
				$css_vars_string .= sprintf( '--jp-%1$s-%2$s: %3$s;', str_replace('_', '-', $setting ), 'repeat', $options['bg_repeat'] );
				$css_vars_string .= sprintf( '--jp-%1$s-%2$s: %3$s;', str_replace('_', '-', $setting ), 'size', $options['bg_size'] );

				break;
			case 'gradient':
				$css_vars_string .= sprintf( '--jp-%1$s-%2$s: %3$s;', str_replace('_', '-', $setting ), 'image', $options['gradient'] );

				break;
		}

		return $css_vars_string;
	}

	/**
	 * [render description]
	 * @return [type] [description]
	 */
	public function maybe_enqueue_css() {
		
		if ( ! class_exists( '\JET_SM\Gutenberg\Style_Manager' ) ) {
			return;
		}

		$popup_id = $this->get( 'popup_id' );
		
		\JET_SM\Gutenberg\Style_Manager::get_instance()->render_blocks_style( $popup_id );
		\JET_SM\Gutenberg\Style_Manager::get_instance()->render_blocks_fonts( $popup_id );

	}
}
