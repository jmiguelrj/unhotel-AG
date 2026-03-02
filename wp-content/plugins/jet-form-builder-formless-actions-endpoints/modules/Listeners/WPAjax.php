<?php

namespace JFB_Formless\Modules\Listeners;

use Jet_Form_Builder\Exceptions\Query_Builder_Exception;
use JFB_Formless\Services;
use JFB_Formless\Modules\Listeners\Adapters\RESTToSendJSON;

class WPAjax {

	const META_ENDPOINT_KEY = 'ajax_action';

	private $proxy;
	private $send_json;
	private $route_id;

	public function __construct(
		ListenerProxy $proxy,
		RESTToSendJSON $send_json
	) {
		$this->proxy     = $proxy;
		$this->send_json = $send_json;

		if ( ! wp_doing_ajax() ) {
			return;
		}

		add_action( 'admin_init', array( $this, 'add_ajax_listener' ) );
	}


	public function add_ajax_listener() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$action = sanitize_key( $_GET['action'] ?? '' );

		try {
			$this->route_id = $this->proxy->query_route_id( self::META_ENDPOINT_KEY, $action );
		} catch ( Query_Builder_Exception $exception ) {
			// it isn't our action
			return;
		}

		add_action(
			"wp_ajax_{$action}",
			array( $this, 'run_submit' )
		);
		add_action(
			"wp_ajax_nopriv_{$action}",
			array( $this, 'run_submit' )
		);
	}

	public function run_submit() {
		$this->proxy->set_request( $this->get_request() );
		$this->proxy->set_action_type( Services\Route::ACTION_AJAX );

		$this->send_json->send(
			$this->proxy->get_response( $this->route_id )
		);
	}

	private function get_request(): array {
		return json_decode( file_get_contents( 'php://input' ), true );
	}


}
