<?php


namespace Jet_FB_HR_Select\JetFormBuilder\Blocks;

use JetHRSelectCore\JetFormBuilder\BlocksParserManager;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


class ParserManager extends BlocksParserManager {

	public function parsers(): array {
		if ( ! function_exists( 'jet_fb_context' ) ) {
			return array(
				new HrSelectParser(),
			);
		}

		return array(
			new HrSelectParserNew(),
		);
	}

	public function on_base_need_update() {
	}

	public function on_base_need_install() {
	}
}
