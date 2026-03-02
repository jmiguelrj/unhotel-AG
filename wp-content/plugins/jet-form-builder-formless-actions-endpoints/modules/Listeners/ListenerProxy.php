<?php

namespace JFB_Formless\Modules\Listeners;

use Jet_Form_Builder\Actions\Events\Default_Required\Default_Required_Event;
use Jet_Form_Builder\Classes\Http\Http_Tools;
use Jet_Form_Builder\Db_Queries\Query_Conditions_Builder;
use Jet_Form_Builder\Exceptions\Action_Exception;
use Jet_Form_Builder\Exceptions\Handler_Exception;
use Jet_Form_Builder\Exceptions\Query_Builder_Exception;
use Jet_Form_Builder\Exceptions\Repository_Exception;
use Jet_Form_Builder\Exceptions\Request_Exception;
use Jet_Form_Builder\Form_Messages\Status_Info;
use JFB_Modules\Security\Exceptions\Spam_Exception;
use JFB_Formless\DB\Views;
use JFB_Formless\Adapters;
use JFB_Formless\Services;
use JFB_Formless\DB\Models;

class ListenerProxy {

	private $meta;
	private $route_to_db;
	private $log_model;
	private $request     = array();
	private $action_type = 0;

	public function __construct(
		Views\RoutesMeta $meta,
		Adapters\RouteToDatabase $route_to_db,
		Services\Route $route,
		Models\RequestLogs $log_model
	) {
		$this->meta        = $meta;
		$this->route_to_db = $route_to_db;
		$this->log_model   = $log_model;
		$this->route_to_db->set_route( $route );
	}

	public function get_response( $route_id ): \WP_REST_Response {
		try {
			$this->route_to_db->get_route()->set_id( (int) $route_id );
			$this->route_to_db->find();
		} catch ( Services\ValidateException $exception ) {
			return new \WP_REST_Response(
				array(
					'code'    => 'invalid_route_id',
					'message' => __( 'Invalid route ID.', 'jet-form-builder-formless-actions-endpoints' ),
				),
				404
			);
		} catch ( Query_Builder_Exception $exception ) {
			return new \WP_REST_Response(
				array(
					'code'    => 'not_found',
					'message' => __( 'Route not founded.', 'jet-form-builder-formless-actions-endpoints' ),
				),
				404
			);
		}

		if ( $this->get_action_type() !== $this->route_to_db->get_route()->get_action_type() ) {
			return new \WP_REST_Response(
				array(
					'code'    => 'invalid_endpoint',
					'message' => __( 'Invalid endpoint.', 'jet-form-builder-formless-actions-endpoints' ),
				),
				400
			);
		}

		$this->log_request();

		if ( ! $this->route_to_db->get_route()->has_permission() ) {
			return new \WP_REST_Response(
				array(
					'code'    => 'no_permission',
					'message' => __( 'You have not access to this endpoint', 'jet-form-builder-formless-actions-endpoints' ),
				),
				403
			);
		}

		jet_fb_handler()->set_form_id( $this->route_to_db->get_route()->get_form_id() );
		jet_fb_handler()->set_referrer( wp_get_referer() );

		try {
			/** @var \JFB_Modules\Captcha\Module $captcha */
			$captcha = jet_form_builder()->module( 'captcha' );
			$captcha->rep_clear();
		} catch ( Repository_Exception $exception ) {
			// continue
		}

		$this->try_run_process();
		$this->after_process();

		return $this->get_response_object();
	}

	public function try_run_process() {
		add_filter(
			'jet-form-builder/request-handler/request',
			array( $this, 'get_request_once' ),
			- 1
		);

		try {
			jet_fb_handler()->send_form();
		} catch ( Request_Exception $exception ) {
			jet_fb_handler()->set_response_args(
				array(
					'code' => $exception->get_form_status(),
					'data' => array(
						'errors' => $exception->get_fields_errors(),
					),
				)
			);

			return;
		} catch ( Action_Exception $exception ) {
			jet_fb_handler()->is_success = $exception->is_success();

			jet_fb_handler()->set_response_args(
				array(
					'code' => $exception->get_form_status(),
				)
			);

			return;
		} catch ( Spam_Exception $exception ) {
			jet_fb_handler()->set_response_args(
				array(
					'code' => $exception->get_form_status(),
				)
			);

			return;
		}

		jet_fb_handler()->set_response_args(
			array(
				'code' => 'success',
			)
		);
	}

	private function after_process() {
		global $post;

		/**
		 * For form-record compatibility
		 *
		 * @see \JFB_Modules\Form_Record\Controller::save_record
		 */
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$post = (object) array(
			'ID'        => 0,
			'post_type' => '',
		);

		/**
		 * Also for form-record compatibility
		 *
		 * @see \JFB_Modules\Form_Record\Controller::save_record
		 */
		jet_fb_handler()->set_response_args(
			array(
				'status' => jet_fb_handler()->response_args['code'] ?? 'failed',
			)
		);

		try {
			jet_fb_events()->execute( Default_Required_Event::class );

			do_action(
				'jet-form-builder/form-handler/after-send',
				jet_fb_handler(),
				jet_fb_handler()->is_success
			);
		} catch ( Handler_Exception $exception ) {
			jet_fb_handler()->is_success    = false;
			jet_fb_handler()->response_args = array(
				'code' => $exception->get_form_status(),
			);
			jet_fb_handler()->response_data = array();
		}

		unset( jet_fb_handler()->response_args['status'] );
	}

	private function log_request() {
		if ( ! $this->route_to_db->get_route()->is_log() ) {
			return;
		}

		$log = array(
			'route_id'   => $this->route_to_db->get_route()->get_id(),
			'body'       => wp_json_encode( $this->get_request() ),
			'user_agent' => Http_Tools::get_user_agent(),
			'referrer'   => wp_get_referer(),
			'ip_address' => Http_Tools::get_ip_address(),
		);

		$this->log_model->insert_soft( $log );
	}


	/**
	 * @param string $endpoint_meta_key
	 * @param string $action
	 *
	 * @return int
	 * @throws Query_Builder_Exception
	 */
	public function query_route_id( string $endpoint_meta_key, string $action ): int {
		$this->meta->set_select( array( 'route_id' ) );
		$this->meta->set_conditions(
			array(
				array(
					'type'   => Query_Conditions_Builder::TYPE_EQUAL,
					'values' => array( 'route_key', $endpoint_meta_key ),
				),
				array(
					'type'   => Query_Conditions_Builder::TYPE_EQUAL,
					'values' => array( 'route_value', $action ),
				),
			)
		);

		$query    = $this->meta->query();
		$route_id = (int) $query->db()->get_var( $query->sql() );

		if ( ! $route_id ) {
			throw new Query_Builder_Exception( 'not_found' );
		}

		return $route_id;
	}

	private function get_response_object(): \WP_REST_Response {
		$args = jet_fb_handler()->get_response_args();

		if ( jet_fb_handler()->response_data ) {
			$args['data'] = jet_fb_handler()->response_data;
		}

		if ( ! empty( $args['message'] ) ) {
			return new \WP_REST_Response(
				$args,
				jet_fb_handler()->is_success ? 200 : 400
			);
		}

		$status          = new Status_Info( $args['code'] ?? 'failed' );
		$args['message'] = jet_fb_msg_router_manager()->get_message_by_info( $status );

		return new \WP_REST_Response(
			$args,
			$status->is_success() ? 200 : 400
		);
	}

	public function get_request_once(): array {
		$request = $this->get_request();
		$this->set_request( array() );

		return $request;
	}

	/**
	 * @param array $request
	 */
	public function set_request( array $request ) {
		$this->request = apply_filters( 'jet-form-builder-formless/proxy/request', $request, $this );
	}

	/**
	 * @return array
	 */
	public function get_request(): array {
		return $this->request;
	}

	/**
	 * @return int
	 */
	public function get_action_type(): int {
		return $this->action_type;
	}

	/**
	 * @param int $action_type
	 */
	public function set_action_type( int $action_type ) {
		$this->action_type = $action_type;
	}

}
