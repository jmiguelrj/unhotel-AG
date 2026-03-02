<?php
namespace JET_APB;

use JET_APB\Tools;

/**
 * Appointment_Price class
 */
class Appointment_Price {

	private $args = null;

	private $service_meta = null;

	private $provider_meta = null;

	public function __construct( $args = [] ) {

		if( ! $this->args ){
			$this->args = wp_parse_args( $args, [
				'service' => false,
				'provider' => false,
			] );
		}

		if( ! $this->service_meta ){
			$this->service_meta = $this->get_post_meta( $this->args['service'], true );
		}

		if( ! $this->provider_meta ){
			$this->provider_meta = $this->get_post_meta( $this->args['provider'], false );
		}
	}

	public function get_post_meta( $post_id, $has_defaults = false ) {
		
		$defaults      = false;
		$default_price = 10;
		
		if ( $has_defaults ) {
			$defaults = [
				'price_type' => '_app_price',
				'_app_price' => 0,
			];
		}

		if ( ! $post_id ) {
			return $defaults;
		}
	
		$post_type = get_post_type( $post_id );
		$providers = Plugin::instance()->settings->get( 'providers_cpt' );

		if ( $post_type === $providers ) {
			$defaults['price_type'] = '_app_price_service';
		}
	
		if ( $has_defaults ) {
			/**
			 * Set defaults to 10, to ensure consistency with default data 
			 * if user doesn't change any setting in service/provider post
			 */
			$defaults['_app_price'] = $default_price;
		}

		$post_meta = get_post_meta( $post_id, 'jet_apb_post_meta', true );

		if ( empty( $post_meta ) ) {

			$price = get_post_meta( $post_id, '_app_price', true );

			if ( '' == $price ) {
				$price = isset( $defaults['_app_price'] ) ? $defaults['_app_price'] : $default_price;
			}
			$price_type =  isset( $defaults['price_type'] ) ? $defaults['price_type'] : '_app_price';

			$post_meta = [
				'price_type' => $price_type,
				'_app_price' => $price,
			];
		} else {
			$post_meta = isset( $post_meta['meta_settings'] ) ? $post_meta['meta_settings'] : [
				'price_type' => '_app_price',
				'_app_price' => $default_price,
			];
		}

		return $post_meta;
	}

	public function get_price( $fixed_price = true ){
		
		$type = $this->get_type();

		if ( $type !== '_app_price_service'
			&& isset( $this->provider_meta[ $type ] ) 
			&& null !== isset( $this->provider_meta[ $type ] ) 
		) {
			$price = $this->provider_meta[ $type ];
		} else {
			$type  = ! empty( $this->service_meta['price_type'] ) ?  $this->service_meta['price_type'] : '_app_price' ;
			$price = $this->service_meta[ $type ];
		}

		switch ( $type ) {
			case '_app_price_hour':
				$slot_duration = Tools::get_time_settings( $this->args['service'], $this->args['provider'], 'default_slot', 0 );
				$hours = ceil( $slot_duration / 60 / 60 );

				$price = $fixed_price ? $price * $hours : $price ;
			break;

			case '_app_price_minute':
				$slot_duration = Tools::get_time_settings( $this->args['service'], $this->args['provider'], 'default_slot', 0 );
				$minutes = ceil( $slot_duration / 60 );

				$price = $fixed_price ? $price * $minutes : $price ;
			break;
		}

		return [ 'price' => $price, 'type' => $type ];
	}

	public function get_type() {
		
		$type = '_app_price';

		if( ! empty( $this->service_meta['price_type'] ) ){
			$type = $this->service_meta['price_type'];
		}

		if( ! empty( $this->provider_meta['price_type'] ) ){
			$type = $this->provider_meta['price_type'];
		}

		return $type;
	}
}
