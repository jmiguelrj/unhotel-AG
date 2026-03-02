<?php
namespace Jet_Engine_Layout_Switcher;

class Render extends \Jet_Engine_Render_Base {

	private static $enqueue_assets = false;

	public function get_name() {
		return 'jet-engine-layout-switcher';
	}

	public function default_settings() {
		return array(
			'widget_id' => '',
			'view'      => '',
			'layouts'   => array(),
		);
	}

	public function get_view() {
		return jet_engine_layout_switcher()->get_view( $this->get_settings( 'view' ) );
	}

	public function render() {
		$settings = $this->get_settings();

		if ( empty( $settings['widget_id'] ) ) {
			printf(
				'<div class="jet-listing-notice">%1$s</div>',
				esc_html__( 'Please select the Listing Grid to show the Layout Switcher.', 'jet-engine' )
			);

			return;
		}

		if ( empty( $settings['view'] ) || ! $this->get_view() ) {
			printf(
				'<div class="jet-listing-notice">%1$s</div>',
				esc_html__( 'The view instance is not defined.', 'jet-engine' )
			);
			return;
		}

		if ( empty( $settings['layouts'] ) ) {
			return;
		}

		$listing_settings = $this->get_default_listing_settings();
		$is_edit_mode     = $this->is_edit_mode();

		if ( empty( $listing_settings ) && ! $is_edit_mode ) {
			return;
		}

		$this->enqueue_assets();

		$cookie_key     = 'jet_engine_layout_' . esc_attr( $settings['widget_id'] );
		$cookie_value   = isset( $_COOKIE[ $cookie_key ] ) ? json_decode( wp_unslash( $_COOKIE[ $cookie_key ] ), true ) : false;
		$current_layout = ( ! empty( $cookie_value ) && ! empty( $cookie_value['layout'] ) ) ? $cookie_value['layout'] : false;

		$buttons_html           = '';
		$default_layout         = null;
		$active_layout_settings = null;
		$default_listing_id     = $listing_settings['lisitng_id'];

		$show_label = isset( $settings['show_label'] ) ? filter_var( $settings['show_label'], FILTER_VALIDATE_BOOLEAN ) : true;

		foreach ( $settings['layouts'] as $layout ) {

			$label = ! empty( $layout['label'] ) ? $layout['label'] : '';
			$slug  = self::get_prepared_slug( $layout );
			$icon  = ! empty( $layout['icon'] ) ? \Jet_Engine_Tools::render_icon( $layout['icon'], 'je-layout-switcher__btn-icon' ) : '';

			$is_default_layout = ! empty( $layout['is_default_layout'] ) ? filter_var( $layout['is_default_layout'], FILTER_VALIDATE_BOOLEAN ) : false;

			if ( $is_default_layout ) {
				$default_layout  = $slug;
				$layout_settings = $listing_settings;
			} else {
				$layout_settings = $this->get_layout_settings( $layout );

				if ( ! empty( $layout_settings['lisitng_id'] ) ) {
					$listing_id = absint( $layout_settings['lisitng_id'] );
					$view_type  = jet_engine()->listings->data->get_listing_type( $listing_id );

					jet_engine()->admin_bar->register_item( 'edit_post_' . $listing_id, array(
						'title'     => get_the_title( $listing_id ),
						'sub_title' => jet_engine()->admin_bar->get_post_type_label( jet_engine()->post_type->slug() ),
						'href'      => jet_engine()->post_type->admin_screen->get_edit_url( $view_type, $listing_id ),
					) );
				}

				$layout_settings = array_merge( $listing_settings, $layout_settings );
			}

			$button_attr = array(
				'class'         => array( 'je-layout-switcher__btn' ),
				'data-slug'     => esc_attr( $slug ),
				'data-settings' => htmlspecialchars( json_encode( $layout_settings ) ),
			);

			if ( $is_default_layout ) {
				$button_attr['data-is-default'] = '1';
			}

			if ( $current_layout === $slug || ( ! $current_layout && $is_default_layout ) ) {
				$button_attr['class'][] = 'je-layout-switcher__btn--active';
				$active_layout_settings = $layout_settings;
			}

			if ( ! $show_label && ! empty( $label ) ) {
				$button_attr['aria-label'] = esc_attr( $label );
				$label = '';
			}

			$button_html = sprintf(
				'<button %3$s>%1$s%2$s</button>',
				$icon,
				$label,
				\Jet_Engine_Tools::get_attr_string( $button_attr )
			);

			$buttons_html .= $button_html;
		}

		$layout_css = '';

		if ( $active_layout_settings && $current_layout && $current_layout !== $default_layout && ! $is_edit_mode ) {

			$uniq_wrap_selector = $this->get_uniq_wrap_selector( $settings['widget_id'] );

			$selector = $uniq_wrap_selector . ' > .jet-listing-grid > .jet-listing-grid__items';
			$slide_selector = $uniq_wrap_selector . ' > .jet-listing-grid > .jet-listing-grid__slider > .jet-listing-grid__items.slick-slider .slick-slide';

			if ( ! empty( $active_layout_settings['columns'] ) ) {
				$auto_col_rules = '';
				$slide_css = '';

				if ( 'auto' === $active_layout_settings['columns'] && ! empty( $active_layout_settings['column_min_width'] ) ) {
					$auto_col_rules .= ' grid-template-columns: repeat( auto-fill, minmax( ' . $active_layout_settings['column_min_width'] . 'px, 1fr ) ) !important;';

					$slide_css = sprintf(
						' %1$s { width: %2$spx !important; }',
						$slide_selector,
						$active_layout_settings['column_min_width']
					);
				}

				$layout_css .= sprintf(
					'%1$s { display: %2$s !important; --columns: %3$s !important;%4$s }%5$s',
					$selector,
					( 'auto' === $active_layout_settings['columns'] ) ? 'grid' : 'flex',
					$active_layout_settings['columns'],
					$auto_col_rules,
					$slide_css
				);
			}

			$active_breakpoints = $this->get_active_breakpoints();

			foreach ( $active_breakpoints as $name => $breakpoint ) {

				$columns_name = sprintf( 'columns%s%s', $this->get_breakpoint_divider(), $name );

				if ( empty( $active_layout_settings[ $columns_name ] ) ) {
					continue;
				}

				$dir   = $breakpoint['direction'];
				$value = $breakpoint['value'] . 'px';

				$bp_auto_col_rules = '';
				$bp_slide_css = '';
				$columns_min_width_name = sprintf( 'column_min_width%s%s', $this->get_breakpoint_divider(), $name );

				if ( 'auto' === $active_layout_settings[ $columns_name ] && ! empty( $active_layout_settings[ $columns_min_width_name ] ) ) {
					$bp_auto_col_rules .= ' grid-template-columns: repeat( auto-fill, minmax( ' . $active_layout_settings[ $columns_min_width_name ] . 'px, 1fr ) ) !important;';

					$bp_slide_css = sprintf(
						' %1$s { width: %2$spx !important; }',
						$slide_selector,
						$active_layout_settings[ $columns_min_width_name ]
					);
				}

				$layout_css .= sprintf(
					' @media(%1$s-width: %2$s) { %3$s { display: %4$s !important; --columns: %5$s !important;%6$s }%7$s }',
					$dir,
					$value,
					$selector,
					( 'auto' === $active_layout_settings[ $columns_name ] ) ? 'grid' : 'flex',
					$active_layout_settings[ $columns_name ],
					$bp_auto_col_rules,
					$bp_slide_css
				);
			}

		}

		if ( ! empty( $layout_css ) ) {
			$layout_css = sprintf(
				'<style type="text/css" id="jet-engine-layout-switcher-custom-css-%1$s">%2$s</style>',
				esc_attr( $settings['widget_id'] ),
				$layout_css
			);
		}

		printf(
			'<div class="je-layout-switcher" data-widget-id="%1$s" data-view="%2$s" data-default-listing="%3$s"><div class="je-layout-switcher__group" role="group">%4$s</div>%5$s</div>',
			esc_attr( $settings['widget_id'] ),
			esc_attr( $settings['view'] ),
			esc_attr( $default_listing_id ),
			$buttons_html,
			$layout_css
		);
	}

	public static function get_prepared_slug( $layout ) {

		if ( ! empty( $layout['slug'] ) ) {
			$slug = $layout['slug'];
		} elseif ( ! empty( $layout['label'] ) ) {
			$slug = $layout['label'];
		} elseif ( ! empty( $layout['_id'] ) ) {
			$slug = 'layout' . '-' . $layout['_id'];
		} elseif ( ! empty( $layout['id'] ) ) {
			$slug = 'layout' . '-' . $layout['id'];
		} else {
			$slug = 'layout' . '-' . md5( json_encode( $layout ) );
		}

		// Sanitize slug.
		$slug = strtolower( $slug );
		$slug = remove_accents( $slug );
		$slug = preg_replace( '/[^a-z0-9\s\-\_]/', '', $slug );
		$slug = str_replace( ' ', '-', $slug );

		return $slug;
	}

	public function get_default_listing_settings() {
		$widget_id        = $this->get_settings( 'widget_id' );
		$listing_settings = $this->get_view()->get_listing_settings_by_id( $widget_id );

		return $this->get_layout_settings( $listing_settings );
	}

	public function get_layout_settings_keys() {
		return array(
			'lisitng_id',
			'columns',
			'column_min_width',
		);
	}

	public function get_layout_settings( $settings = array() ) {

		if ( empty( $settings ) ) {
			return array();
		}

		$allowed = $this->get_layout_settings_keys();
		$allowed_regex = '/^(?:' . join( '|', $allowed ) . ')/';

		return array_filter( $settings, function ( $value, $setting ) use ( $allowed_regex ) {

			if ( preg_match( $allowed_regex, $setting ) && ! empty( $value ) ) {
				return true;
			}

			return false;
		}, ARRAY_FILTER_USE_BOTH );
	}

	public function enqueue_assets() {
		$is_edit_mode    = $this->is_edit_mode();
		$is_preview_mode = $this->is_preview_mode();

		if ( $is_edit_mode || $is_preview_mode || self::$enqueue_assets ) {
			return;
		}

		$css      = '';
		$css_path = JET_ENGINE_LAYOUT_SWITCHER_PATH . 'assets/css/layout-switcher.css';

		if ( is_file( $css_path ) && is_readable( $css_path ) ) {
			$css = file_get_contents( $css_path );
		}

		// Print inline css
		if ( ! empty( $css ) ) {
			printf( '<style>%s</style>', $css );
		}

		// Enqueue script
		wp_enqueue_script( 'jet-engine-layout-switcher' );

		self::$enqueue_assets = true;
	}

	public function is_edit_mode() {
		return $this->get_view()->is_edit_mode();
	}

	public function is_preview_mode() {
		return $this->get_view()->is_preview_mode();
	}

	public function get_active_breakpoints() {
		return $this->get_view()->get_active_breakpoints();
	}

	public function get_breakpoint_divider() {
		return $this->get_view()->get_breakpoint_divider();
	}

	public function get_uniq_wrap_selector( $widget_id ) {
		return $this->get_view()->get_uniq_wrap_selector( $widget_id );
	}
}
