<?php

namespace JFB_Formless\Modules\AdminComponents;

class Module {

	const HANDLE = 'crocoblock-admin-components';

	public function register_assets() {
		$script_url   = $this->get_url( 'assets/build/index.js' );
		$script_asset = require_once $this->get_path( 'assets/build/index.asset.php' );

		wp_register_script(
			self::HANDLE,
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
	}

	public function get_url( string $url = '' ): string {
		return JFB_FORMLESS_URL . 'modules/AdminComponents/' . $url;
	}

	public function get_path( string $path = '' ): string {
		return JFB_FORMLESS_PATH . 'modules/AdminComponents/' . $path;
	}

}
