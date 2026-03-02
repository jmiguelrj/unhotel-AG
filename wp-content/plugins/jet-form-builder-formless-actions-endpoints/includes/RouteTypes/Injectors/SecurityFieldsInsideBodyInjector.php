<?php

namespace JFB_Formless\RouteTypes\Injectors;

use JFB_Formless\RouteTypes\Injectors\Interfaces\RouteTypeInjectorInterface;
use JFB_Formless\RouteTypes\Interfaces\RouteInterface;
use JFB_Modules\Security\Csrf\Csrf_Tools;
use JFB_Modules\Post_Type;
use JFB_Modules\Security\Wp_Nonce;

class SecurityFieldsInsideBodyInjector implements RouteTypeInjectorInterface {

	/** @noinspection PhpUnhandledExceptionInspection */
	public function inject( RouteInterface $route ) {
		$body    = $route->get_body() ?: array();
		$form_id = $route->get_route_meta()->get_route()->get_form_id();

		// store previous form id, because button could be inside form
		$prev_form_id = jet_fb_live()->form_id;

		jet_fb_live()->form_id = $form_id;

		/** @var Post_Type\Module $post_type */
		$post_type = jet_form_builder()->module( 'post-type' );
		/** @var Wp_Nonce\Module $wp_nonce */
		$wp_nonce = jet_form_builder()->module( 'wp-nonce' );
		$args     = $post_type->get_args();

		if ( ! empty( $args['use_csrf'] ) ) {
			$body[ Csrf_Tools::FIELD ] = $this->get_csrf_token();
		}

		if ( 'render' === ( $args['load_nonce'] ?? '' ) ) {
			$body[ $wp_nonce::KEY ] = wp_create_nonce( $wp_nonce->get_nonce_id() );
		}

		$route->set_body( $body );
		jet_fb_live()->form_id = $prev_form_id;
	}

	private function get_csrf_token(): string {
		$input = Csrf_Tools::get_field();

		preg_match( '/value=\"(\w+)"/', $input, $matches );

		return empty( $matches[1] ) ? '' : $matches[1];
	}

}
