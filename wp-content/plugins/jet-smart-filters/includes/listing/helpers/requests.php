<?php

namespace Jet_Smart_Filters\Listing\Helpers;

use Jet_Smart_Filters\Listing\Controller as Listing_Controller;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Requests {

	protected $listing_instance;

	public function __construct() {

		$this->listing_instance = Listing_Controller::instance();
	}

	/**
	 * Get listings callback
	 *
	 * @return void
	 */
	public function get_listings() {

		if ( ! $this->is_access_allowed() ) {
			wp_send_json_error( $this->error );
		}

		$untitled_item_name = apply_filters(
			'jet-smart-filters/listing/untitled_listing_name',
			__( 'Untitled Listing', 'jet-smart-filters' )
		);

		$listings_arg_request = jet_smart_filters()->data->get_request_var( 'listings_arg' );

		$listings_arg = $listings_arg_request ? $listings_arg_request : [];
		$listings     = $this->listing_instance->storage->get_listings( $listings_arg );

		if ( ! empty( $listings['items'] ) && is_array( $listings['items'] ) ) {
			foreach ( $listings['items'] as &$item ) {
				if ( empty( $item['name'] ) ) {
					$item['name'] = $untitled_item_name . ' (Id: ' . $item['ID'] . ')';
				}
			}

			unset( $item );
		}

		wp_send_json_success( $listings );
	}

	/**
	 * Get listing callback
	 *
	 * @return void
	 */
	public function get_listing() {

		if ( ! $this->is_access_allowed() ) {
			wp_send_json_error( $this->error );
		}

		$listing_id_request = jet_smart_filters()->data->get_request_var( 'listing_id' );
		$listing_id = $listing_id_request ? absint( $listing_id_request ) : false;

		wp_send_json_success( $this->listing_instance->storage->get_listing( $listing_id ) );
	}

	/**
	 * Save listing callback
	 *
	 * @return void
	 */
	public function save_listing() {

		if ( ! $this->is_access_allowed() ) {
			wp_send_json_error( $this->error );
		}

		$listing_request = jet_smart_filters()->data->get_request_var( 'listing' );
		$data = $listing_request ? $listing_request : [];

		/**
		 * @todo Sanitize data before save (lightly, just ensure only required keys in $data array)
		 */

		$result = $this->listing_instance->storage->update_listing( $data );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		wp_send_json_success( [ 'listing_id' => $result ] );
	}

	/**
	 * Remove listing callback
	 *
	 * @return void
	 */
	public function remove_listing() {

		if ( ! $this->is_access_allowed() ) {
			wp_send_json_error( $this->error );
		}

		$request = jet_smart_filters()->data->get_request();

		$listing_id   = ! empty( $request['listing_id'] ) ? absint( $request['listing_id'] ) : false;
		$listings_arg = ! empty( $request['listings_arg'] ) ? $request['listings_arg'] : [];

		wp_send_json_success( $this->listing_instance->storage->remove_listing( $listing_id, $listings_arg ) );
	}

	/**
	 * Get items list callback
	 *
	 * @return void
	 */
	public function get_items() {

		if ( ! $this->is_access_allowed() ) {
			wp_send_json_error( $this->error );
		}

		$untitled_item_name = apply_filters(
			'jet-smart-filters/listing/untitled_item_name',
			__( 'Untitled Item', 'jet-smart-filters' )
		);

		$items_list_arg_request = jet_smart_filters()->data->get_request_var( 'items_list_arg' );

		$items_list_arg = $items_list_arg_request ? $items_list_arg_request : [];
		$items          = $this->listing_instance->storage->get_items( $items_list_arg );

		if ( ! empty( $items['items'] ) && is_array( $items['items'] ) ) {
			foreach ( $items['items'] as &$item ) {
				if ( empty( $item['name'] ) ) {
					$item['name'] = $untitled_item_name . ' (Id: ' . $item['ID'] . ')';
				}
			}

			unset( $item );
		}

		wp_send_json_success( $items );
	}

	/**
	 * Get item callback
	 *
	 * @return void
	 */
	public function get_listing_item() {

		if ( ! $this->is_access_allowed() ) {
			wp_send_json_error( $this->error );
		}

		$item_id_request_val = jet_smart_filters()->data->get_request_var( 'item_id' );
		$item_id = $item_id_request_val ? absint( $item_id_request_val ) : 0;

		if ( ! $item_id ) {
			wp_send_json_error( 'Item not found in the request' );
		}

		$result = $this->listing_instance->storage->get_listing_item( $item_id );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		wp_send_json_success( $result );
	}

	/**
	 * Save item callback
	 *
	 * @return void
	 */
	public function save_listing_item() {

		if ( ! $this->is_access_allowed() ) {
			wp_send_json_error( $this->error );
		}

		$request = jet_smart_filters()->data->get_request();

		$data = ! empty( $request['listing_item'] ) ? $request['listing_item'] : [];

		/**
		 * @todo Sanitize data before save (lightly, just ensure only required keys in $data array)
		 */

		$result = $this->listing_instance->storage->update_listing_item( $data );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		if ( ! empty( $request['payload'] ) ) {
			if ( ! empty( $request['payload']['related_listing_id'] ) ) {
				$this->listing_instance->storage->update_listing( [
					'ID'      => $request['payload']['related_listing_id'],
					'item_id' => $result
				] );
			}
		}

		wp_send_json_success( [ 'item_id' => $result ] );
	}

	/**
	 * Remove listing item callback
	 *
	 * @return void
	 */
	public function remove_listing_item() {

		if ( ! $this->is_access_allowed() ) {
			wp_send_json_error( $this->error );
		}

		$request = jet_smart_filters()->data->get_request();

		$item_id        = ! empty( $request['item_id'] ) ? absint( $request['item_id'] ) : false;
		$items_list_arg = ! empty( $request['items_list_arg'] ) ? $request['items_list_arg'] : [];

		wp_send_json_success( $this->listing_instance->storage->remove_listing_item( $item_id, $items_list_arg ) );
	}

	/**
	 * Get cards callback
	 *
	 * @return void
	 */
	public function get_cards() {

		if ( ! $this->is_access_allowed() ) {
			wp_send_json_error( $this->error );
		}

		$untitled_item_name = apply_filters(
			'jet-smart-filters/listing/untitled_item_name',
			__( 'Untitled Item', 'jet-smart-filters' )
		);

		$search_request_val = jet_smart_filters()->data->get_request_var( 'jet-smart-filters' );
		$search_query = $search_request_val ? '*' . esc_attr( $search_request_val ) . '*' : '';
		$cards        = $this->listing_instance->storage->get_items_list( array(
			'search' => $search_query
		), false );

		if ( ! empty( $cards ) && is_array( $cards ) ) {
			foreach ( $cards as &$item ) {
				if ( empty( $item['label'] ) ) {
					$item['label'] = $untitled_item_name . ' (Id: ' . $item['value'] . ')';
				}
			}

			unset( $item );
		}

		wp_send_json_success( $cards );
	}

	/**
	 * Get users callback
	 *
	 * @return void
	 */
	public function get_users() {

		if ( ! $this->is_access_allowed() ) {
			wp_send_json_error( $this->error );
		}

		$search_request_val = jet_smart_filters()->data->get_request_var( 'search' );
		$search_query = $search_request_val ? '*' . esc_attr( $search_request_val ) . '*' : '';

		wp_send_json_success( jet_smart_filters()->data->get_user_for_options( array(
			'search' => $search_query
		) ) );
	}

	/**
	 * Get all posts list and grouped
	 *
	 * @return void
	 */
	public function get_posts_list() {

		if ( ! $this->is_access_allowed() ) {
			wp_send_json_error( $this->error );
		}

		$search_request_val = jet_smart_filters()->data->get_request_var( 'search' );
		$search_query = $search_request_val ? esc_attr( $search_request_val ) : '';

		wp_send_json_success( jet_smart_filters()->data->get_posts_for_options( array(
			's_by_title' => $search_query
		) ) );
	}

	/**
	 * Get all terms list and grouped
	 *
	 * @return void
	 */
	public function get_terms_list() {

		if ( ! $this->is_access_allowed() ) {
			wp_send_json_error( $this->error );
		}

		$search_request_val = jet_smart_filters()->data->get_request_var( 'search' );
		$search_query = $search_request_val ? esc_attr( $search_request_val ) : '';

		wp_send_json_success( jet_smart_filters()->data->get_grouped_terms_for_options( array(
			'name__like' => $search_query
		) ) );
	}

	/**
	 * Extract request data,
	 * Check if access allowed to the action handler
	 *
	 * @return boolean
	 */
	public function is_access_allowed( $nonce = null ) {

		$raw_payload = file_get_contents( 'php://input' );
		$data        = json_decode( $raw_payload, true );

		if ( $data && is_array( $data ) ) {
			foreach ( $data as $key => $value ) {
				$_REQUEST[ $key ] = $value;
			}
		}

		if ( ! $nonce ) {
			$nonce = ! empty( $_SERVER['HTTP_X_WP_NONCE'] )
				? sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_WP_NONCE'] ) )
				: null;
		}

		if ( ! $nonce || ! wp_verify_nonce( $nonce, $this->listing_instance->listing_key ) ) {
			$this->error = 'The page is expired. Please reload it and try again.';
			return false;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			$this->error = 'You are not allowed to perform this action.';
			return false;
		}

		return true;
	}
}