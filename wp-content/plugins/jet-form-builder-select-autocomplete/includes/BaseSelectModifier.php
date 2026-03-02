<?php


namespace JFB\SelectAutocomplete;

use Jet_Form_Builder\Blocks\Render\Base as BaseRender;

/**
 * @method BaseRender|\Jet_Engine_Booking_Forms_Builder getClass()
 *
 * Trait BaseSelectModifier
 * @package JFB\SelectAutocomplete
 */
trait BaseSelectModifier {

	public function enqueueFrontendAssets() {
	}

	public function onRender(): array {
		$args = $this->getArgs();

		if ( ! isset( $args['autocomplete_enable'] ) || ! $args['autocomplete_enable'] ) {
			return $args;
		}
		if ( isset( $args['autocomplete_via_ajax'] ) && $args['autocomplete_via_ajax'] ) {
			$this->getClass()->add_attribute( 'data-ajax--url', esc_url( admin_url( 'admin-ajax.php' ) ) );
		}
		if ( isset( $args['autocomplete_minimumInputLength'] ) ) {
			$this->getClass()->add_attribute( 'data-minimum-input-length', $args['autocomplete_minimumInputLength'] );
		}
		if ( isset( $args['placeholder'] ) && $args['placeholder'] ) {
			$this->getClass()->add_attribute( 'data-placeholder', $args['placeholder'] );
		}

		$this->getClass()->add_attribute( 'class', 'jet-select-autocomplete' );

		wp_enqueue_script(
			Plugin::SLUG . '-select2-lib',
			JET_FB_SELECT_AUTOCOMPLETE_URL . 'assets/lib/js/select2.min.js',
			array( 'jquery', 'wp-hooks' ),
			'4.0.13',
			true
		);
		wp_enqueue_style(
			Plugin::SLUG,
			JET_FB_SELECT_AUTOCOMPLETE_URL . 'assets/lib/css/select2.min.css',
			array(),
			'4.0.13'
		);

		$this->enqueueFrontendAssets();

		return $args;
	}


}