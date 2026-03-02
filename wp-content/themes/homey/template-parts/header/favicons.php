<?php
/**
 * This function is used to output the site favicons and apple icons
 * Code is echoed into the wp_head hook
 *
 * @package Homey
 * @author Waqas Riaz
 * @copyright Copyright (c) 2019, Favethemes
 * @link http://www.favethemes.com
 * @since Homey 1.0.0
 */

add_action( 'wp_head', 'homey_favicons_icons' );
if ( ! function_exists( 'homey_favicons_icons' ) ) {
    function homey_favicons_icons() {
        $allowed_html_array = array(
            'link' => array(
                'rel' => array(),
                'sizes' => array(),
                'href' => array()
            )
        );

        // Vars
        $output = '';
        $favicon = homey_option('favicon', '', 'url');
        $iphone_icon = homey_option('iphone_icon', '', 'url');
        $iphone_icon_retina = homey_option('iphone_icon_retina', '', 'url');
        $ipad_icon = homey_option('ipad_icon', '', 'url');
        $ipad_icon_retina = homey_option('ipad_icon_retina', '', 'url');

        // Favicon
        if ( $favicon ) {
            $output .= '<!-- Favicon -->';
            $output .= '<link rel="shortcut icon" href="'. esc_url( $favicon ) .'">';
        }

        // Apple iPhone Icon
        if ( $iphone_icon ) {
            $output .= '<!-- Apple iPhone Icon -->';
            $output .= '<link rel="apple-touch-icon-precomposed" href="'. esc_url( $iphone_icon ) .'">';
        }

        // Apple iPhone Retina Icon
        if ( $iphone_icon_retina ) {
            $output .= '<!-- Apple iPhone Retina Icon -->';
            $output .= '<link rel="apple-touch-icon-precomposed" sizes="114x114" href="'. esc_url( $iphone_icon_retina ) .'">';
        }

        // Apple iPad Icon
        if ( $ipad_icon ) {
            $output .= '<!-- Apple iPhone Icon -->';
            $output .= '<link rel="apple-touch-icon-precomposed" sizes="72x72" href="'. esc_url( $ipad_icon ) .'">';
        }

        // Apple iPad Retina Icon
        if ( $ipad_icon_retina && ! $iphone_icon_retina ) {
            $output .= '<!-- Apple iPhone Icon -->';
            $output .= '<link rel="apple-touch-icon-precomposed" sizes="114x114" href="'. esc_url( $ipad_icon_retina ) .'">';
        }

        echo wp_kses( $output, $allowed_html_array);

    }
}