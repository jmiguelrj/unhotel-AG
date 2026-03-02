<?php


namespace JFB\LimitResponses\JetEngine;


use JFB\LimitResponses\LimitResponses;
use JFB\LimitResponses\MetaQueries\SettingsMetaQuery;
use JFB\LimitResponses\Plugin;
use JFB\LimitResponses\Vendor\JFBCore\JetEngine\RegisterFormMetaBox;

class MetaBox extends RegisterFormMetaBox {

	private $query;

	public function __construct(
		SettingsMetaQuery $query
	) {
		$this->plugin_maybe_init();

		$this->query = $query;
	}

	public function get_id() {
		return LimitResponses::PLUGIN_META_KEY;
	}

	public function get_title() {
		return __( 'Limit Form Responses', 'jet-form-builder' );
	}

	public function get_fields() {
		ob_start();
		include JET_FB_LIMIT_FORM_RESPONSES_PATH . 'templates/meta-box.php';
		$content = ob_get_clean();

		return array(
			$this->get_id() => array(
				'type' => 'html',
				'html' => $content,
			)
		);
	}

	public function register_assets() {
		$this->query->set_form_id( get_the_ID() );
		$this->query->fetch();

		wp_enqueue_script(
			Plugin::SLUG,
			JET_FB_LIMIT_FORM_RESPONSES_URL . 'assets/dist/engine.bundle.js',
			array(),
			JET_FB_LIMIT_FORM_RESPONSES_VERSION,
			true
		);

		wp_localize_script(
			Plugin::SLUG,
			'JetLimitFormResponses',
			array(
				'meta' => $this->query->get_settings()
			)
		);
	}

	public function on_base_need_update() {
		$this->add_admin_notice( 'warning', __(
			'<b>Warning</b>: <b>JetFormBuilder Limit Form Responses</b> needs <b>JetEngine</b> update.',
			'jet-form-builder-limit-form-responses'
		) );
	}

	public function on_base_need_install() {
	}
}