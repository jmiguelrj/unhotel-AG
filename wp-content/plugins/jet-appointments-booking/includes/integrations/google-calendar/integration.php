<?php
namespace JET_APB\Integrations\Google_Calendar;

use JET_APB\Integrations\Base_Integration;
use JET_APB\Plugin;
use JET_APB\Integrations\Manager as Integrations_Manager;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define integration manager class
 */
class Integration extends Base_Integration {

	public $google_calendar_module = null;

	/**
	 * Setup integration
	 * This method runs only when integration is enabled.
	 */
	public function on_setup() {

		add_action( 'jet-engine/rest-api/init-endpoints', array( $this, 'init_rest' ), 99 );

		$this->google_calendar_module = new \Crocoblock\Google_Calendar_Synch\Controller( [
			'app_slug' => 'jet-apb-google-calendar',
		] );

		$this->google_calendar_module->register_auth_callback( [ $this, 'auth_callback' ] );

		$data = $this->get_data();
		$client_id = isset( $data['client_id'] ) ? $data['client_id'] : '';
		$client_secret = isset( $data['client_secret'] ) ? $data['client_secret'] : '';
		$sync_from_calendar = isset( $data['sync_events_from_calendar'] ) ? $data['sync_events_from_calendar'] : false;

		if ( $sync_from_calendar ) {
			add_filter( 'jet-apb/calendar/check-external-slots', '__return_true' );
		}

		if ( empty( $client_id ) || empty( $client_secret ) ) {
			return;
		}

		$this->google_calendar_module->set( 'client_id', $client_id );
		$this->google_calendar_module->set( 'client_secret', $client_secret );

		new Synch_Create();
		new Synch_Remove();
		new Synch_Update();
		new Synch_Before_Update();
		new Synch_Fetch();

		new Calendar_Meta_Box();
	}

	/**
	 * Callback function for Google Calendar auth client.
	 *
	 * 1. Gets code from the request.
	 * 2. If code is not empty - calls save_token_callback function with code and
	 *    redirections URLs set as arguments.
	 */
	public function auth_callback( $request, $save_token_callback = false ) {

		$code = ! empty( $_GET['code'] ) ? sanitize_text_field( wp_unslash( $_GET['code'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

		if ( empty( $code ) ) {
			return;
		}

		if ( is_callable( $save_token_callback ) ) {
			$save_token_callback( $code, [
				'global' => admin_url( 'admin.php?page=jet-dashboard-settings-page&subpage=jet-apb-integrations' ),
				'post'   => '%edit_post_link%',
			] );
		}
	}

	public function get_id() {
		return 'google-calendar';
	}

	public function get_name() {
		return __( 'Google Calendar', 'jet-appointments-booking' );
	}

	public function get_description() {
		return  sprintf(
			__( 'Allow to set up 2-way sync with Google Calendar globally, per service or per provider. <a href="%s">Full guide</a>', 'jet-appointments-booking' ),
			'https://crocoblock.com/knowledge-base/jetappointment/how-to-set-up-two-way-appointment-sync-with-google-calendar/'
		);
	}

	public function assets() {

		wp_enqueue_script(
			'jet-apb-google-calendar-integration-component',
			JET_APB_URL . 'includes/integrations/google-calendar/assets/js/data-component.js',
			array( 'wp-api-fetch', 'jquery' ),
			JET_APB_VERSION,
			true
		);

		$redirect_url = $this->google_calendar_module ? $this->google_calendar_module->get_redirect_url() : '';
		$is_connected = $this->google_calendar_module ? $this->google_calendar_module->is_connected( 'global' ) : false;


		wp_localize_script( 'jet-apb-google-calendar-integration-component', 'JetAPBGCalData', [
			'apiPath' => Plugin::instance()->rest_api->get_url( 'appointment-google-auth', false ),
			'calendarsPath' => Plugin::instance()->rest_api->get_url( 'appointment-google-calendars-list', false ),
			'redirectURL' => $redirect_url,
			'connected' => $is_connected,
			'cron_schedules' => $this->get_cron_schedules(),
			'calendars' => $this->get_calendars(),
		] );
	}

	/**
	 * Initialize Rest API endpoints
	 *
	 * @param object $api_manager JetEngine API manager.
	 * @return void
	 */
	public function init_rest( $api_manager ) {

		$google_auth = new Rest_API\Google_Auth();
		$google_auth->set_google_calendar_module( $this->google_calendar_module );

		$google_calendars = new Rest_API\Google_Calendars_List();
		$google_calendars->set_google_calendar_module( $this->google_calendar_module );

		$google_meta = new Rest_API\Google_Calendars_Meta();
		$google_meta->set_google_calendar_module( $this->google_calendar_module );

		$api_manager->register_endpoint( $google_auth );
		$api_manager->register_endpoint( $google_calendars );
		$api_manager->register_endpoint( $google_meta );
	}

	public function get_data_component() {
		return 'jet-apb-google-calendar-integration';
	}

	public function parse_data( $data = [] ) {

		$global_connection = isset( $data['use_global_connection'] ) ? $data['use_global_connection'] : false;
		$global_connection = filter_var( $global_connection, FILTER_VALIDATE_BOOLEAN );

		$sync_from_calendar = isset( $data['sync_events_from_calendar'] ) ? $data['sync_events_from_calendar'] : false;
		$sync_from_calendar = filter_var( $sync_from_calendar, FILTER_VALIDATE_BOOLEAN );

		$create_meet = isset( $data['create_meet'] ) ? $data['create_meet'] : false;
		$create_meet = filter_var( $create_meet, FILTER_VALIDATE_BOOLEAN );

		return [
			'sync_events_from_calendar' => $sync_from_calendar,
			'use_global_connection' => $global_connection,
			'create_meet' => $create_meet,
			'client_id' => isset( $data['client_id'] ) ? $data['client_id'] : '',
			'client_secret' => isset( $data['client_secret'] ) ? $data['client_secret'] : '',
			'calendar_id' => isset( $data['calendar_id'] ) ? $data['calendar_id'] : '',
			'synch_interval' => isset( $data['synch_interval'] ) ? $data['synch_interval'] : 'twicedaily',
		];
	}

	public function get_templates() {

		ob_start();
		include JET_APB_PATH . 'includes/integrations/google-calendar/templates/connect-calendar.php';
		$connect_template = ob_get_clean();

		printf(
			'<script type="text/x-template" id="jet-apb-google-calendar-connect">%s</script>',
			$connect_template // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);

		return [
			'jet-apb-google-calendar-integration' => JET_APB_PATH . 'includes/integrations/google-calendar/templates/data-component.php',
		];
	}

	public static function get_cron_schedules() {

		$schedules = wp_get_schedules();

		uasort( $schedules, function ( $a, $b ) {
			if ( $a['interval'] == $b['interval'] ) {
				return 0;
			}

			return ( $a['interval'] < $b['interval'] ) ? -1 : 1;
		} );

		$result          = [];
		$found_intervals = [];

		foreach ( $schedules as $name => $int ) {
			if ( ! in_array( $int['interval'], $found_intervals ) ) {
				$diff = human_time_diff( 0, $int['interval'] );

				$result[] = [
					'value' => $name,
					'label' => $int['display'] . ' (' . $diff . ')',
				];

				$found_intervals[] = $int['interval'];
			}
		}

		return $result;

	}

	public function get_calendars() {

		$synch_post_meta = Integrations_Manager::instance()->get_synch_posts();
		$global_settings = $this->get_data();
		$calendars       = [];

		foreach( $synch_post_meta as $id => $post_meta ) {
			if ( ! empty( $post_meta['calendar_id'] ) && ( false !== $post_meta['use_local_calendar'] || false !== $post_meta['use_local_connection'] ) ) {
				$calendars[$id] = $post_meta['calendar_id'];
			}
		}
		$calendars['global'] = $global_settings['calendar_id'];

		return $calendars;

	}
}
