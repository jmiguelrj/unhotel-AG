<?php


namespace JFBAdvancedMediaCore\JetFormBuilder;

use JFBAdvancedMediaCore\SmartBaseFormField;

trait SmartBaseBlock {

	use SmartBaseFormField;

	/**
	 * Returns current block render instance
	 *
	 * @param null $wp_block
	 *
	 * @return string
	 */
	public function get_block_renderer( $wp_block = null ) {
		return $this->get_template();
	}
}