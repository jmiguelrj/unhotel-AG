<?php

namespace JFB_Formless\Modules\Routes\Pages;

use Jet_Form_Builder\Admin\Pages\Base_Page;
use JFB_Formless\Modules\AdminComponents;
use JFB_Formless\Modules\Routes\Module;

class Routes extends Base_Page {

	private $module;
	private $components;

	public function __construct(
		Module $module,
		AdminComponents\Module $components
	) {
		$this->module     = $module;
		$this->components = $components;
	}


	/**
	 * @inheritDoc
	 */
	public function title(): string {
		return __( 'Endpoints', 'jet-form-builder-formless-actions-endpoints' );
	}

	/**
	 * @inheritDoc
	 */
	public function slug(): string {
		return 'action-endpoints';
	}

	public function assets() {
		$script_url   = $this->module->get_url( 'assets/build/all.js' );
		$script_asset = require_once $this->module->get_path( 'assets/build/all.asset.php' );

		array_push(
			$script_asset['dependencies'],
			AdminComponents\Module::HANDLE
		);

		$this->components->register_assets();

		wp_enqueue_script(
			$this->slug(),
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
		wp_localize_script(
			$this->slug(),
			'JetFBPageConfig',
			$this->page_config()
		);

		// dataviews module related
		wp_enqueue_style( 'wp-components' );
		wp_enqueue_style( 'wp-edit-site' );
		wp_add_inline_style(
			'wp-edit-site',
			'body.js #wpadminbar { display:block; }'
		);
	}

	public function page_config(): array {
		return array(
			'addNewHref' => jet_fb_current_page()->get_url(
				array(
					'item_id' => - 1,
				)
			),
		);
	}
}
