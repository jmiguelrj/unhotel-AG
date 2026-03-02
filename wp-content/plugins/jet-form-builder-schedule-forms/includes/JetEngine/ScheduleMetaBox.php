<?php


namespace JFB\ScheduleForms\JetEngine;


use JFB\ScheduleForms\Plugin;
use JFB\ScheduleForms\Queries\SettingsQuery;
use JFB\ScheduleForms\ScheduleForm;
use JFB\ScheduleForms\Vendor\JFBCore\JetEngine\RegisterFormMetaBox;

class ScheduleMetaBox extends RegisterFormMetaBox {

	private $query;

	public function __construct(
		SettingsQuery $query
	) {
		$this->plugin_maybe_init();

		$this->query = $query;
	}

	public function get_id() {
		return ScheduleForm::PLUGIN_META_KEY;
	}

	public function get_title() {
		return __( 'Form Schedule', 'jet-form-builder-schedule-forms' );
	}

	public function get_fields() {
		ob_start();
		include JET_FB_SCHEDULE_FORMS_PATH . 'templates/meta-box.php';
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
			JET_FB_SCHEDULE_FORMS_URL . 'assets/dist/engine.bundle.js',
			array(),
			JET_FB_SCHEDULE_FORMS_VERSION,
			true
		);

		wp_localize_script(
			Plugin::SLUG,
			'JetScheduleForms',
			array(
				'schedule' => $this->query->get_settings(),
			)
		);
	}

	public function on_base_need_update() {
		$this->add_admin_notice( 'warning', __(
			'<b>Warning</b>: <b>JetFormBuilder Schedule Forms</b> needs <b>JetEngine</b> update.',
			'jet-form-builder-schedule-forms'
		) );
	}

	public function on_base_need_install() {
	}
}