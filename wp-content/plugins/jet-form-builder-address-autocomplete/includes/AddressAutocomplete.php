<?php


namespace Jet_FB_Address_Autocomplete;

class AddressAutocomplete {

	use AddressConfigBase;

	const API_URl = 'https://maps.googleapis.com/maps/api/js?key=%s&callback=%s&libraries=places&v=weekly';

	private $settings;
	private static $instance;
	private $current_prefix;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function clear() {
		self::$instance = null;
	}

	public function key( $tab_key, $option_name = 'api_key' ) {
		return ( (
			isset( $this->settings[ $tab_key ] )
			&& isset( $this->settings[ $tab_key ][ $option_name ] )
			&& $this->settings[ $tab_key ][ $option_name ]
		)
			? $this->settings[ $tab_key ][ $option_name ]
			: false
		);
	}

	public function settings( $tab_slug, $manager ) {
		if ( empty( $this->settings[ $tab_slug ] ) ) {
			$this->settings[ $tab_slug ] = $manager->options( $tab_slug );
		}

		return $this->settings;
	}

	public function enqueue_styles() {
		wp_enqueue_style(
			Plugin::instance()->slug,
			Plugin::instance()->plugin_url( 'assets/css/field.css' ),
			array(),
			JET_FB_ADDRESS_AUTOCOMPLETE_VERSION
		);
	}

	public function enqueue_scripts( $tab_key ) {

		if ( ! $this->key( $tab_key ) ) {
			return;
		}

		wp_enqueue_script(
			Plugin::instance()->slug . '-lib',
			Plugin::instance()->plugin_url( 'assets/lib/jquery-editable-select.js' ),
			array( 'jquery' ),
			JET_FB_ADDRESS_AUTOCOMPLETE_VERSION,
			true
		);

		wp_enqueue_script(
			Plugin::instance()->slug,
			Plugin::instance()->plugin_url( 'assets/dist/frontend.bundle.js' ),
			array(
				'jet-form-builder-frontend-forms',
			),
			JET_FB_ADDRESS_AUTOCOMPLETE_VERSION,
			true
		);

		if ( $this->key( $tab_key, 'disable_js' ) ) {
			return;
		}
		wp_dequeue_script( 'jet-engine-google-maps-api' );
		wp_deregister_script( 'jet-engine-google-maps-api' );

		wp_enqueue_script(
			'jet-engine-google-maps-api',
			sprintf( self::API_URl, $this->key( $tab_key ), 'initJFBAutocomplete' ),
			array(),
			false,
			true
		);
	}

}
