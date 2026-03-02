<?php
/**
 * Jet Menu WPML compatibility
 */

add_filter( 'jet-popup/block-manager/not-supported-blocks', 'add_not_supported_blocks' );

/**
 * @param $links
 *
 * @return bool|mixed|mixed[]|void
 */
function add_not_supported_blocks( $not_supported_blocks ) {
	$blocks = [
		'jet-menu/mega-menu',
		'jet-menu/mobile-menu',
	];

	return array_merge( $not_supported_blocks, $blocks );
}
