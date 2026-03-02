<?php
namespace JET_APB\Integrations\Google_Calendar;

use JET_APB\Plugin;
use JET_APB\Integrations\Manager as Integrations_Manager;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Register, render and process Google-calendar related metabox
 * for appointment services and providers
 */
class Calendar_Meta_Box {

	protected $services_cpt = null;
	protected $providers_cpt = null;

	public static $meta_key = '_jet_apb_google_calendar';

	public function __construct() {

		$this->services_cpt  = Plugin::instance()->settings->get( 'services_cpt' );
		$this->providers_cpt = Plugin::instance()->settings->get( 'providers_cpt' );

		add_action( 'add_meta_boxes', [ $this, 'add_meta_box' ], 20 );
	}

	/**
	 * Check if is appointment-related CPT.
	 *
	 * @return boolean
	 */
	public function is_related_cpt( $current_cpt = null ) {

		$app_cpts = [
			$this->services_cpt
		];

		if ( $this->providers_cpt ) {
			$app_cpts[] = $this->providers_cpt;
		}

		if ( $current_cpt && in_array( $current_cpt, $app_cpts ) ) {
			return true;
		}

		// phpcs:disable WordPress.Security.NonceVerification
		if ( ! empty( $_GET['post_type'] ) && in_array( $_GET['post_type'], $app_cpts ) ) {
			return true;
		}

		if ( ! empty( $_GET['post'] ) && in_array( get_post_type( $_GET['post'] ), $app_cpts ) ) {
			return true;
		}
		// phpcs:enable WordPress.Security.NonceVerification

		global $current_screen;

		if (
			$current_screen
			&& ! empty( $current_screen->post_type )
			&& in_array( $current_screen->post_type, $app_cpts )
		) {
			return true;
		}

		return false;
	}

	/**
	 * Register meta box to render
	 *
	 * @param [type] $post_type
	 * @return void
	 */
	public function add_meta_box( $post_type ) {

		if ( ! $this->is_related_cpt( $post_type ) ) {
			return;
		}

		add_meta_box(
			'jet-apb-google-calendar',
			__( 'Google Calendar Sync', 'jet-apb' ),
			[ $this, 'render_meta_box' ],
			$post_type,
			'normal',
			'high'
		);
	}

	/**
	 * Render meta box
	 *
	 * @param object $post Post object.
	 */
	public function render_meta_box( $post ) {

		$integration = Integrations_Manager::instance()->get_integrations( 'google-calendar' );

		$integration->assets();

		wp_enqueue_script(
			'jet-apb-google-calendar-meta-box',
			JET_APB_URL . 'includes/integrations/google-calendar/assets/js/meta-box.js',
			array( 'cx-vue-ui' ),
			JET_APB_VERSION,
			true
		);

		$post_id = $post->ID;

		wp_localize_script( 'jet-apb-google-calendar-meta-box', 'JetAPBGCalMeta', [
			'meta_value'       => self::get_meta(),
			'connected_global' => $integration->google_calendar_module->is_connected( 'global' ),
			'connected_local'  => $integration->google_calendar_module->is_connected( 'post', $post_id ),
			'post_id'          => $post_id,
			'nonce'            => wp_create_nonce( self::$meta_key ),
			'meta_api'         => Plugin::instance()->rest_api->get_url( 'appointment-google-calendars-meta', false ),
		] );

		echo '<div id="jet_apb_google_calendar_metabox"></div>';

		$this->print_templates();
	}

	/**
	 * Get meta value
	 *
	 * @return array
	 */
	public static function get_meta( $post_id = false ) {

		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		$meta = get_post_meta( $post_id, self::$meta_key, true );

		if ( ! $meta ) {
			$meta = [];
		}

		$meta = array_merge( self::get_default_meta(), $meta );

		return $meta;
	}

	/**
	 * Get default meta values
	 *
	 * @return array
	 */
	public static function get_default_meta() {
		return [
			'use_local_connection' => false,
			'use_local_calendar'   => false,
			'calendar_id'          => '',
		];
	}

	/**
	 * Update post meta by post ID
	 *
	 * @param int   $post_id
	 * @param array $meta
	 * @return void
	 */
	public static function update_meta( $post_id = null, $meta = [] ) {

		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		if ( ! $post_id ) {
			return;
		}

		if ( ! is_array( $meta ) ) {
			return;
		}

		$meta = array_merge( self::get_default_meta(), $meta );

		update_post_meta( $post_id, self::$meta_key, $meta );

		do_action( 'jet-apb/google-calendar/update-meta', $post_id, $meta );
	}

	/**
	 * Delete post meta by post ID
	 *
	 * @param int $post_id
	 * @return void
	 */
	public static function delete_meta( $post_id = null ) {

		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		if ( ! $post_id ) {
			return;
		}

		delete_post_meta( $post_id, self::$meta_key );
	}

	/**
	 * Print Vue templates.
	 *
	 * @return void
	 */
	public function print_templates() {

		ob_start();
		include JET_APB_PATH . 'includes/integrations/google-calendar/templates/meta-box.php';
		$template = ob_get_clean();

		printf(
			'<script type="text/x-template" id="jet-apb-google-calendar-meta-box">%s</script>',
			$template // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);

		ob_start();
		include JET_APB_PATH . 'includes/integrations/google-calendar/templates/connect-calendar.php';
		$connect_template = ob_get_clean();

		printf(
			'<script type="text/x-template" id="jet-apb-google-calendar-connect">%s</script>',
			$connect_template // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}
}
