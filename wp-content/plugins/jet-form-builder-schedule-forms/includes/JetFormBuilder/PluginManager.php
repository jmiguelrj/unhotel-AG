<?php


namespace JFB\ScheduleForms\JetFormBuilder;


use JFB\ScheduleForms\Plugin;
use JFB\ScheduleForms\ScheduleForm;
use JFB\ScheduleForms\Vendor\JFBCore\JetFormBuilder\PluginManager as BasePluginManager;

class PluginManager extends BasePluginManager {

	public function plugin_version_compare(): string {
		return '1.2.0';
	}

	/**
	 * @return void
	 */
	public function before_init_editor_assets() {
		wp_enqueue_script(
			Plugin::SLUG,
			JET_FB_SCHEDULE_FORMS_URL . 'assets/dist/builder.bundle.js',
			array( 'wp-i18n' ),
			JET_FB_SCHEDULE_FORMS_VERSION,
			true
		);

		wp_enqueue_style(
			Plugin::SLUG,
			JET_FB_SCHEDULE_FORMS_URL . 'assets/css/editor.css',
			array(),
			JET_FB_SCHEDULE_FORMS_VERSION
		);
	}

	public function meta_data() {
		return array(
			ScheduleForm::PLUGIN_META_KEY => array()
		);
	}

	public function on_base_need_update() {
		$this->add_admin_notice( 'warning', __(
			'<b>Warning</b>: <b>JetFormBuilder Schedule Forms</b> needs <b>JetFormBuilder</b> update.',
			'jet-form-builder-schedule-forms'
		) );
	}

	public function on_base_need_install() {
	}
}