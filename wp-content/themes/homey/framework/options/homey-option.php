<?php
/**
 * Retuns theme options data
 *
 * @package	Homey
 * @author Waqas Riaz
 * @copyright Copyright (c) 2016, Favethemes
 * @link http://favethemes.com
 * @since Homey 1.0
 */

if ( ! function_exists( 'homey_option' ) ) {
	function homey_option( $id, $fallback = false, $param = false ) {
		if ( isset( $_GET['homey_'.$id] ) ) {
			if ( '-1' == $_GET['homey_'.$id] ) {
				return false;
			} else {
				return $_GET['homey_'.$id];
			}
		} else {
			global $homey_options;

            if(!is_object($homey_options) || empty($homey_options)){
                $homey_options = get_option('homey_options');
            }

			if ( $fallback == false ) $fallback = '';
			$output = ( isset($homey_options[$id]) && $homey_options[$id] !== '' ) ? $homey_options[$id] : $fallback;
			if ( !empty($homey_options[$id]) && $param ) {
				$output = $homey_options[$id][$param];
			}
		}
		return $output;
	}
}