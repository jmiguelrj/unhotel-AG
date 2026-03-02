<?php

namespace JFB_Formless\Modules\Routes\AJAX;

class CheckIsUniqueAjaxEndpoint {

	const ACTION = 'jfb-formless-check-is-unique-action';

	public function __construct() {
		$action = self::ACTION;

		add_action( "wp_ajax_nopriv_{$action}", array( $this, 'check_is_unique_action' ) );
	}


	public function check_is_unique_action() {
		$action      = sanitize_key( $_GET['jfb_action'] ?? '' );
		$hook        = sprintf( 'wp_ajax_%s', $action );
		$hook_nopriv = sprintf( 'wp_ajax_nopriv_%s', $action );

		if ( ! has_action( $hook ) && ! has_action( $hook_nopriv ) ) {
			wp_send_json_success();
		}

		wp_send_json_error();
	}


	public function get_url( string $action ): string {
		return add_query_arg(
			array(
				'action'     => self::ACTION,
				'jfb_action' => $action,
			),
			admin_url(
				'admin-ajax.php'
			)
		);
	}

}
