<?php
namespace Jet_Reviews\Endpoints;

use Jet_Reviews\Reviews\Data as Reviews_Data;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define Posts class
 */
class Import_Reviews extends Base {

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
		return 'import-reviews';
	}

	/**
	 * Returns arguments config
	 *
	 * @return [type] [description]
	 */
	public function get_args() {
		return array(
			'list' => array(
				'default'    => [],
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

		if ( empty( $args['list'] ) ) {
			return rest_ensure_response( array(
				'success' => false,
				'message' => __( 'Error', 'jet-reviews' ),
			) );
		}

		$review_list = $args['list'];
		$imported_review_data = [];

		if ( ! empty( $review_list ) ) {

			foreach ( $review_list as $key => $row_data ) {
				$insert_data = Reviews_Data::get_instance()->add_new_review( $row_data, true );
				$imported_review_data[] = $insert_data;
			}
		}

		$reviews_query = Reviews_Data::get_instance()->get_admin_reviews_list_by_page( false, 0, 20 );

		return rest_ensure_response( [
			'success' => true,
			'message' => __( 'The reviews have been imported', 'jet-reviews' ),
			'data'    => [
				'imported_data' => $imported_review_data,
				'page_list'     => $reviews_query['page_list'],
				'total_count'   => $reviews_query['total_count'],
			],
		] );
	}

}
