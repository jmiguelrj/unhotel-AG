<?php

namespace JFB_Formless\Modules\Routes\Pages;

use Jet_Form_Builder\Admin\Single_Pages\Base_Single_Page;
use Jet_Form_Builder\Classes\Tools;
use JFB_Formless\Modules\AdminComponents;
use JFB_Formless\Modules\Routes\Module;

class RouteSingle extends Base_Single_Page {

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
		return __( 'Endpoint', 'jet-form-builder-formless-actions-endpoints' );
	}

	public function parent_slug(): string {
		return 'action-endpoints';
	}

	public function assets() {
		$script_url   = $this->module->get_url( 'assets/build/single.js' );
		$script_asset = require_once $this->module->get_path( 'assets/build/single.asset.php' );

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

	/**
	 * We should accept -1 for "add new" page
	 *
	 * @return int
	 */
	public function query_id(): int {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return intval( $_GET['item_id'] ?? 0 );
	}

	public function query_config(): array {
		return array();
	}

	/**
	 * We aren't using native containers here
	 *
	 * @return array
	 */
	public function page_config(): array {
		$roles = Tools::get_user_roles_for_js( array() );
		array_shift( $roles );

		return array(
			'roles'   => $roles,
			'restUrl' => rest_url(),
			'rootUrl' => user_trailingslashit( site_url() ),
		);
	}

}
