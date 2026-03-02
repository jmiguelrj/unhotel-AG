<?php

namespace Jet_FB_Save_Progress;


use JetSaveProgressCore\Common\MetaQuery;

class SaveProgress {

	use BothCompatibility;

	public         $slug = '_jf_save_progress';
	private        $settings;
	private static $instance;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function clear() {
		self::$instance = null;
	}

	public function settings( $form_id = 0 ) {
		// Don't cache settings as they can be different for each form
		$settings = MetaQuery::get_json_meta( array(
			'id'  => $form_id,
			'key' => $this->slug,
		) );

		return $settings;
	}

	protected function scripts_after_end( $form_id = 0 ) {
		$settings = json_encode( $this->settings( $form_id ) );

		if ( ! $settings ) {
			return;
		}

		$bundle = Plugin::instance()->plugin_url( 'assets/dist/deprecated.frontend.bundle.js' );

		if ( class_exists( '\Jet_Form_Builder\Blocks\Validation' ) ) {
			$bundle = Plugin::instance()->plugin_url( 'assets/dist/jet.fb.frontend.bundle.js' );
		}

		wp_enqueue_script(
			Plugin::instance()->slug,
			$bundle,
			array(
				'jet-form-builder-frontend-forms',
			),
			Plugin::instance()->get_version(),
			true
		);

		wp_add_inline_script(
			Plugin::instance()->slug, "
			window.JetFormSaveProgress = window.JetFormSaveProgress || {};
			window.JetFormSaveProgress[ $form_id ] = JSON.parse( `$settings` );
			",
			'before'
		);
	}

	protected function handle_success_submit( $props ) {
		list( $handler, $is_success, $form_id ) = $props;

		if ( ! $is_success ) {
			return;
		}
		$settings = $this->settings( $form_id );

		if ( ! isset( $settings['clear_storage'] ) || ! $settings['clear_storage'] ) {
			return;
		}

		$handler->add_response_data( array(
			'jfb_clear_storage' => $form_id,
		) );
	}

}