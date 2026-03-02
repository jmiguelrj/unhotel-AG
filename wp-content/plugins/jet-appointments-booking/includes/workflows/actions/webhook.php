<?php
namespace JET_APB\Workflows\Actions;

use JET_APB\Plugin;
use JET_APB\Workflows\Base_Object;

class Webhook extends Base_Action {

	public function get_id() {
		return 'webhook';
	}

	public function get_name() {
		return __( 'Call a Webhook', 'jet-appointments-bookin' );
	}

	public function register_action_controls() {
		echo '<cx-vui-input
			label="' . esc_html__( 'Webhook URL', 'jet-appointments-booking' ) . '"
			description="' . esc_html__( 'Name of the action to visually identify it in the list', 'jet-appointments-booking' ) . '"
			:wrapper-css="[ \'equalwidth\' ]"
			size="fullwidth"
			v-if="\'webhook\' === item.actions[ actionIndex ].action_id"
			:value="item.actions[ actionIndex ].webhook_url"
			@on-input-change="setActionProp( actionIndex, \'webhook_url\', $event.target.value )"
		/>';
	}
	
	public function do_action() {
		
		$this->fetch_appointments_meta();
		
		$webhook_url = $this->parse_macros( $this->get_settings( 'webhook_url' ) );

		$args = array(
			'body' => $this->prepare_webhook_args( $this->get_appointment() ),
		);

		/**
		 * Filter webhook arguments
		 */
		$args = apply_filters( 'jet-apb/workflows/webhook/request-args', $args, $this );

		if ( $webhook_url ) {
			$response = wp_remote_post( $webhook_url, $args );
		}

		do_action( 'jet-apb/workflows/webhook/after-response', $response, $args, $this );

	}

	public function prepare_webhook_args( $appointment ) {

		$slot_time_format = Plugin::instance()->settings->get( 'slot_time_format' );

		if ( empty( $appointment->get_meta('user_local_time') ) ) {
			$local_time = date( $slot_time_format, $appointment->get( 'slot' ) );

			if ( ! empty( $appointment->get( 'slot_end' ) ) ) {
				$local_time .= '-' . date( $slot_time_format, $appointment->get( 'slot_end' ) );
			}

			$appointment->set_meta( 'user_local_time', $local_time );

		}

		if ( empty( $appointment->get_meta( 'user_local_date' ) ) ) {
			$appointment->set_meta( 'user_local_date', date_i18n( get_option( 'date_format', 'F d, Y' ), $appointment->get( 'slot' ) ) );
		}

		if ( empty( $appointment->get_meta( 'user_timezone' ) ) ) {
			$appointment->set_meta( 'user_timezone', wp_timezone()->getName() );
		}

		if ( empty( $appointment->get_meta( '_service_title' ) ) && ! empty( $appointment->get( 'service' ) ) ) {
			$appointment->set_meta( '_service_title', $appointment->get_service_title() );
		}

		if ( empty( $appointment->get_meta( '_provider_title' ) ) && ! empty( $appointment->get( 'provider' ) ) ) {
			$appointment->set_meta( '_provider_title', $appointment->get_provider_title() );
		}

		if ( empty( $appointment->get_meta( '_service_url' ) ) && ! empty( $appointment->get( 'service' ) ) ) {
			$appointment->set_meta( '_service_url', get_permalink( $appointment->get( 'service' ) ) );
		}

		if ( empty( $appointment->get_meta( '_provider_url' ) ) && ! empty( $appointment->get( 'provider' ) ) ) {
			$appointment->set_meta( '_provider_url', get_permalink ( $appointment->get( 'provider' ) ) );
		}

		return $appointment->to_array();

	}

}
