<?php
namespace Jet_Reviews\Endpoints;

use Jet_Reviews\Reviews\Data as Reviews_Data;
use Jet_Reviews\Reviews\Media as Reviews_Media;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define Posts class
 */
class Delete_Review_Media extends Base {

	/**
	 * [get_method description]
	 * @return [type] [description]
	 */
	public function get_method() {
		return 'POST';
	}

	/**
	 * Returns route name
	 *
	 * @return string
	 */
	public function get_name() {
		return 'delete-review-media';
	}

	/**
	 * Returns arguments config
	 *
	 * @return [type] [description]
	 */
	public function get_args() {
		return array(
			'itemsList' => array(
				'default'    => '',
				'required'   => false,
			),
			'reviewId' => array(
				'default'    => false,
				'required'   => false,
			),
		);
	}

	/**
	 * Check user access to current end-popint
	 *
	 * @return string|bool
	 */
	public function permission_callback( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * [callback description]
	 * @param  [type]   $request [description]
	 * @return function          [description]
	 */
	public function callback( $request ) {
		$args = $request->get_params();
		$items_list = isset( $args['itemsList'] ) ? $args['itemsList'] : [];
		$review_id = isset( $args['reviewId'] ) ? $args['reviewId'] : false;

		if ( empty( $items_list ) || empty( $review_id ) ) {
			return rest_ensure_response( [
				'success' => false,
				'message' => __( 'Error', 'jet-reviews' ),
			] );
		}

		$delete_media = jet_reviews()->reviews_manager->media->delete_media_by_id( $items_list );

		if ( ! $delete_media ) {
			return rest_ensure_response( array(
				'success' => false,
				'message' => __( 'The review media has not been deleted', 'jet-reviews' ),
			) );
		}

		do_action( 'jet-reviews/endpoints/reviews/delete-review-media', $args );

		return rest_ensure_response( [
			'success' => true,
			'message' => __( 'The review media have been deleted', 'jet-reviews' ),
			'data'    => [
				'media' => jet_reviews()->reviews_manager->media->get_media_by_review_id( $review_id ),
			],
		] );
	}

}
