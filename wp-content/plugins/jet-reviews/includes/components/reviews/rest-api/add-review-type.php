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
class Add_Review_Type extends Base {

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
		return 'add-review-type';
	}

	/**
	 * Returns arguments config
	 *
	 * @return [type] [description]
	 */
	public function get_args() {

		return array(
			'name' => array(
				'default'    => '',
				'required'   => false,
			),
			'slug' => array(
				'default'    => '',
				'required'   => false,
			),
			'settings' => array(
				'default'    => '',
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
		$name  = trim( $args['name'] );
		$slug  = $args['slug'];
		$settings = $args['settings'];

		$prepared_data = array(
			'name' => $name,
			'slug' => $slug,
			'source' => $settings['source'],
			'source_type' => $settings['source_type'],
			'fields' => ! empty( $settings['fields'] ) ? maybe_serialize( $settings['fields'] ) : '',
			'settings' => maybe_serialize( $settings ),
		);

		$is_exist = Reviews_Data::get_instance()->is_review_type_exist( $slug );

		if ( $is_exist ) {
			return rest_ensure_response( array(
				'success' => false,
				'message' => __( 'Type with this slug is already exist', 'jet-reviews' ),
			) );
		}

		$review_type_slug = jet_reviews()->reviews_manager->types->get_review_type_slug_by_source_type( $settings['source'], $settings['source_type'] );

		if ( $review_type_slug ) {
			return rest_ensure_response( array(
				'success' => false,
				'message' => __( 'Type with this source type is already exist', 'jet-reviews' ),
			) );
		}

		$insert_id = Reviews_Data::get_instance()->add_new_review_type( $prepared_data );

		do_action( 'jet-reviews/endpoints/reviews/add-review-type', $args, $insert_id );

		if ( ! $insert_id ) {
			return rest_ensure_response( array(
				'success' => false,
				'message' => __( 'Error', 'jet-reviews' ),
			) );
		}

		return rest_ensure_response( array(
			'success' => true,
			'message' => __( 'New review type has been created', 'jet-reviews' ),
			'data'    => array(
				'insert_id' => $insert_id,
			),
		) );
	}

}
