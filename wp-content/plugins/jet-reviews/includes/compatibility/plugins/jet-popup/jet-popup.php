<?php
namespace Jet_Reviews\Compatibility;

use Jet_Reviews\Endpoints as Endpoints;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Compatibility Manager
 */
class Jet_Popup {

	/**
	 * [__construct description]
	 */
	public function __construct() {

		if ( ! class_exists( 'Jet_Popup' ) ) {
			return false;
		}

		add_filter( 'jet-popup/block-manager/not-supported-blocks', function ( $not_supported_blocks ) {

			if ( ! in_array( 'jet-reviews/jet-reviews-advanced', $not_supported_blocks ) ) {
				$not_supported_blocks[] = 'jet-reviews/jet-reviews-advanced';
			}

			return $not_supported_blocks;
		}, 10, 2 );

	}

}
