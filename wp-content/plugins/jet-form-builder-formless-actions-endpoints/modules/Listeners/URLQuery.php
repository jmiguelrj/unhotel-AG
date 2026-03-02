<?php

namespace JFB_Formless\Modules\Listeners;

use Jet_Form_Builder\Exceptions\Query_Builder_Exception;
use JFB_Formless\Services;
use JFB_Formless\Modules\Listeners\Adapters\RESTToSendJSON;

class URLQuery {

	const QUERY_ARG         = 'jfb_submit';
	const META_ENDPOINT_KEY = 'url_action';

	private $proxy;
	private $send_json;

	/**
	 * @var string
	 */
	private $action;

	public function __construct(
		ListenerProxy $proxy,
		RESTToSendJSON $send_json
	) {
		$this->proxy     = $proxy;
		$this->send_json = $send_json;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$this->action = sanitize_key( $_GET[ self::QUERY_ARG ] ?? '' );

		if ( ! $this->action ) {
			return;
		}

		add_action( 'parse_request', array( $this, 'run_submit' ) );
	}


	public function run_submit() {
		try {
			$route_id = $this->proxy->query_route_id( self::META_ENDPOINT_KEY, $this->action );
		} catch ( Query_Builder_Exception $exception ) {
			// we use specific GET parameter to resolve action name, so that we need abort request immediately
			wp_send_json_error(
				array(
					'status' => 'not_found',
				),
				404
			);
			die;
		}

		$this->proxy->set_action_type( Services\Route::ACTION_URL );
		$this->proxy->set_request( $this->get_request() );

		$this->send_json->send(
			$this->proxy->get_response( $route_id )
		);
	}

	private function get_request(): array {
		// phpcs:ignore WordPress.Security
		$field_values = $_GET['data'] ?? array();

		return is_array( $field_values ) ? $field_values : array();
	}


}
