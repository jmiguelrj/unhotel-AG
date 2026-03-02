<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$popup_id = get_the_ID();
$popup_settings = jet_popup()->settings->get_popup_settings( $popup_id );
$popup_settings['jet_popup_use_ajax'] = 'no';


?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<?php if ( ! current_theme_supports( 'title-tag' ) ) : ?>
		<title><?php echo esc_html( wp_get_document_title() ); ?></title>
		<?php endif; ?>
		<?php wp_head(); ?>
	</head>
	<body <?php body_class(); ?>><?php

        jet_popup()->generator->popup_render( $popup_id, $popup_settings, [
            'classes' => [
                'jet-popup--show-state',
                'jet-popup--single-preview',
            ],
        ] );
        wp_print_footer_scripts(); ?>
    </body>
</html>
