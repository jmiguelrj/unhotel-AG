<?php
namespace Jet_Engine_Layout_Switcher\Blocks;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Layout_Switcher_Block extends \Jet_Engine_Blocks_Views_Type_Base {

	/**
	 * Returns block name
	 *
	 * @return string
	 */
	public function get_name() {
		return 'layout-switcher';
	}

	/**
	 * Return attributes array
	 *
	 * @return array
	 */
	public function get_attributes() {
		return array(
			'widget_id' => array(
				'type'    => 'string',
				'default' => '',
			),
			'layouts' => array(
				'type'    => 'array',
				'default' => array(),
			),
			'show_label' => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'view' => array(
				'type'    => 'string',
				'default' => 'blocks',
			),
		);
	}
}
