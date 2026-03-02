<?php
/**
 * Uses JetEngine meta component to process meta
 */
namespace JET_APB\Admin\Cpt_Meta_Box;

use JET_APB\Plugin;
use JET_APB\Time_Slots;

class Services_Meta extends Base_Vue_Meta_Box {
	
	/**
	 * Price Settingss Config array
	 *
	 * @var array
	 */
	protected $meta_settings = [
		'_app_capacity'     => 1,
		'price_type'        => '_app_price',
		'_app_price'        => 10,
		'_app_price_hour'   => 5,
		'_app_price_minute' => 1,
	];
	
	/**
	 * Default settings array
	 *
	 * @var array
	 */
	protected $defaults = [
		'default_slot'  => 1800,
		'buffer_before' => 0,
		'buffer_after'  => 0,
	];
	
	/**
	 * Class constructor
	 */
	public function __construct() {
		parent::__construct( Plugin::instance()->settings->get( 'services_cpt' ) );
		
		$manage_capacity = Plugin::instance()->settings->get( 'manage_capacity' );
		$this->assets['manage_capacity'] = "$manage_capacity";
		
		// Needed for backward compatibility.
		// phpcs:disable
		if( isset( $_GET['post'] ) ) {
			$post                                 = wp_unslash( $_GET['post'] );
			$this->meta_settings['_app_capacity'] = $manage_capacity ? get_post_meta( $post, '_app_capacity', true ) : false;
			$this->meta_settings['_app_price']    = get_post_meta( $post, '_app_price', true );
			$this->defaults['default_slot']       = get_post_meta( $post, '_service_duration', true );
			$this->defaults['buffer_before']      = get_post_meta( $post, '_buffer_before', true );
			$this->defaults['buffer_after']       = get_post_meta( $post, '_buffer_after', true );
		}
		// phpcs:enable
		
	}

	/**
	 * Add a meta box to post.
	 */
	public function add_meta_box(){

		if ( ! $this->is_cpt_page() ) {
			return;
		}
		
		add_meta_box(
			'settings_meta_box',
			esc_html__( 'Appointment Settings', 'jet-appointments-booking' ),
			[ $this, 'settings_meta_box_callback' ],
			[ $this->current_screen_slug ],
			'normal',
			'high'
		);
		
		add_meta_box(
			'schedule_meta_box',
			esc_html__( 'Custom Schedule', 'jet-appointments-booking' ),
			[ $this, 'custom_schedule_meta_box_callback' ],
			[ $this->current_screen_slug ],
			'normal',
			'high'
		);
	}

	/**
	 * Require metabox html.
	 */
	public function settings_meta_box_callback(){
		require_once( JET_APB_PATH .'templates/admin/settings-meta-box.php' );
	}
	
	/**
	 * Require metabox html.
	 */
	public function custom_schedule_meta_box_callback(){
		require_once( JET_APB_PATH .'templates/admin/custom-schedule-meta-box.php' );
	}

}