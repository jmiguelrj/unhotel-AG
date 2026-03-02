<?php

namespace Jet_Reviews\Endpoints;

use Jet_Reviews\Reviews\Data as Reviews_Data;
use Jet_Reviews\Reviews\Media as Reviews_Media;
use Jet_Reviews\User\Manager as User_Manager;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define Posts class
 */
class Submit_Review extends Base {

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
		return 'submit-review';
	}

	/**
	 * Returns arguments config
	 *
	 * @return [type] [description]
	 */
	public function get_args() {
		return [
			'source' => array(
				'default' => 'post',
				'required' => false,
			),
			'source_type' => array(
				'default' => 'post',
				'required' => false,
			),
			'source_id' => array(
				'default' => '',
				'required' => false,
			),
			'title' => array(
				'default' => '',
				'required' => false,
			),
			'content' => array(
				'default' => '',
				'required' => false,
			),
			'author_id' => array(
				'default' => '',
				'required' => false,
			),
			'author_name' => array(
				'default' => '',
				'required' => false,
			),
			'author_mail' => array(
				'default' => '',
				'required' => false,
			),
			'rating_data' => array(
				'default' => [],
				'required' => false,
			),
			'captcha_token' => array(
				'default' => '',
				'required' => false,
			),
		];
	}

	/**
	 * Check user access to current end-point
	 *
	 * @return string|bool
	 */
	public function permission_callback( $request ) {
		$args            = $request->get_params();
		$user_data       = jet_reviews()->user_manager->get_raw_user_data();
		$source          = isset( $args[ 'source' ] ) ? $args[ 'source' ] : 'post';
		$source_type     = isset( $args[ 'source_type' ] ) ? $args[ 'source_type' ] : 'post';
		$review_type_slug = jet_reviews()->reviews_manager->types->get_review_type_slug_by_source_type( $source, $source_type );
		$source_settings  = jet_reviews()->reviews_manager->types->get_review_type_data( $review_type_slug, true );

		if ( ! empty( array_intersect( $user_data['roles'], $source_settings['allowed_roles'] ) ) ) {
			return true;
		}

		return false;
	}

	/**
	 * [callback description]
	 *
	 * @param  [type]   $request [description]
	 *
	 * @return function          [description]
	 */
	public function callback( $request ) {
		$args = $request->get_params();
		$allowed_html = jet_reviews_tools()->get_content_allowed_html();
		$source        = isset( $args[ 'source' ] ) ? $args[ 'source' ] : 'post';
		$source_type   = isset( $args[ 'source_type' ] ) ? $args[ 'source_type' ] : 'post';
		$source_id     = isset( $args[ 'source_id' ] ) ? $args[ 'source_id' ] : false;
		$title         = isset( $args[ 'title' ] ) ? wp_kses( $args[ 'title' ], 'strip' ) : '';
		$content       = isset( $args[ 'content' ] ) ? wp_kses( $args[ 'content' ], $allowed_html ) : '';
		$author_id     = isset( $args[ 'author_id' ] ) ? $args[ 'author_id' ] : '0';
		$author_name   = isset( $args[ 'author_name' ] ) ? wp_kses( $args[ 'author_name' ], 'strip' ) : '';
		$author_mail   = isset( $args[ 'author_mail' ] ) ? sanitize_email( $args[ 'author_mail' ] ) : '';
		$rating_data   = isset( $args[ 'rating_data' ] ) ? json_decode( $args[ 'rating_data' ], true ) : [];
		$captcha_token = isset( $args[ 'captcha_token' ] ) ? $args[ 'captcha_token' ] : '';

		if ( jet_reviews_tools()->is_demo_mode() ) {
			return rest_ensure_response( [
				'success' => false,
				'code'    => 'demo-mode',
				'message' => __( 'You can\'t leave a review. Demo mode is active', 'jet-reviews' ),
				'data'    => [],
			] );
		}

		$recaptcha_instance = jet_reviews()->integration_manager->get_integration_module_instance( 'recaptcha' );
		$captcha_verify     = $recaptcha_instance->maybe_verify( $captcha_token );

		if ( ! $captcha_verify ) {
			return rest_ensure_response( [
				'success' => false,
				'code'    => 'captcha-failed',
				'message' => __( 'Captcha validation failed', 'jet-reviews' ),
				'data'    => [],
			] );
		}

		$review_type_slug = jet_reviews()->reviews_manager->types->get_review_type_slug_by_source_type( $source, $source_type );
		$review_type_data = jet_reviews()->reviews_manager->types->get_review_type_data( $review_type_slug );
		$source_settings = $review_type_data['settings'];
		$need_approve = $source_settings[ 'need_approve' ];
		$is_guest = false === strpos( $author_id, 'guest' ) ? false : true;

		if ( $is_guest ) {
			$prepared_guest_data = [
				'guest_id' => $author_id,
				'name'     => $author_name,
				'mail'     => $author_mail,
			];

			$author_obj = get_user_by('email', $author_mail );
			$guest_obj = jet_reviews()->user_manager->get_guest_by_email( $author_mail );

			if ( $author_obj ) {
				return rest_ensure_response( [
					'success' => false,
					'code'    => 'mail-exist',
					'message' => __( 'User with this mail is already exist', 'jet-reviews' ),
					'data'    => [],
				] );
			}

			$insert_guest_id = jet_reviews()->user_manager->add_new_guest( $prepared_guest_data );
		}

		/*
		 * Check forbidden content
		 */
		$is_forbidden_content = jet_reviews_tools()->forbidden_text_validation( [ $title, $content ] );

		if ( $is_forbidden_content ) {
			$need_approve = true;
		}

		$rating = $this->calculate_rating( $rating_data );

		$prepared_data = [
			'source'      => $source,
			'post_id'     => $source_id,
			'post_type'   => $source_type,
			'author'      => $author_id,
			'date'        => current_time( 'mysql' ),
			'title'       => $title,
			'content'     => $content,
			'type_slug'   => $review_type_slug,
			'rating_data' => maybe_serialize( $rating_data ),
			'rating'      => $rating,
			'approved'    => filter_var( $need_approve, FILTER_VALIDATE_BOOLEAN ) ? 0 : 1,
			'pinned'      => 0,
		];

		$insert_data = Reviews_Data::get_instance()->add_new_review( $prepared_data, true );

		do_action( 'jet-reviews/endpoints/reviews/submit-review', $args, $insert_data );

		if ( ! $insert_data ) {
			return rest_ensure_response( [
				'success' => false,
				'code'    => 'db-error',
				'message' => __( 'DataBase Error', 'jet-reviews' ),
				'data'    => [],
			] );
		}

		$insert_id = $insert_data[ 'insert_id' ];
		$attached_media = $_FILES['attached_media'] ?? [];

		/**
		 * Maybe attach media for review
		 */
		if ( ! empty( $attached_media ) ) {
			Reviews_Media::get_instance()->add_media_for_review( $insert_id, $attached_media, $prepared_data );
		}

		/**
		 * Maybe update average rating post meta field
		 */
		if ( filter_var( $source_settings[ 'metadata' ], FILTER_VALIDATE_BOOLEAN ) ) {
			$this->maybe_update_rating_metadata( $source_id, $source_settings[ 'metadata_rating_key' ], $insert_data[ 'rating' ], $source_settings[ 'metadata_ratio_bound' ] );
		}

		/**
		 * Check if nessesary moderator approving
		 */
		if ( filter_var( $need_approve, FILTER_VALIDATE_BOOLEAN ) ) {
			return rest_ensure_response( [
				'success' => true,
				'code'    => 'need-approve',
				'message' => __( '*Your review must be approved by the moderator', 'jet-reviews' ),
				'data'    => [],
			] );
		}

		$author_data = jet_reviews()->user_manager->get_raw_user_data( $author_id );

		$review_verification_data = jet_reviews()->user_manager->get_verification_data( $source_settings[ 'verifications' ], [
			'user_id' => $author_data[ 'id' ],
			'post_id' => $source_id,
		] );

		$reviews_media_list = Reviews_Data::get_instance()->get_media_by_review_ids( [ $insert_id ] );

		$return_data = [
			'id'            => $insert_id,
			'source'        => $source,
			'source_type'   => $source_type,
			'author'        => [
				'id'     => $author_data[ 'id' ],
				'name'   => $author_data[ 'name' ],
				'mail'   => $author_data[ 'mail' ],
				'avatar' => $author_data[ 'avatar' ],
				'roles'  => $author_data[ 'roles' ],
			],
			'date'          => [
				'raw'        => $prepared_data[ 'date' ],
				'human_diff' => jet_reviews_tools()->human_time_diff_by_date( $prepared_data[ 'date' ] ),
			],
			'title'         => $title,
			'content'       => $content,
			'type_slug'     => $prepared_data[ 'type_slug' ],
			'rating_data'   => $rating_data,
			'rating'        => $rating,
			'comments'      => [],
			'approved'      => $need_approve,
			'like'          => 0,
			'dislike'       => 0,
			'approval'      => jet_reviews()->user_manager->get_review_approval_data( $insert_id ),
			'pinned'        => false,
			'verifications'  => $review_verification_data,
			'media' => array_values( $reviews_media_list ),
		];

		return rest_ensure_response( [
			'success' => true,
			'message' => __( '*Already reviewed', 'jet-reviews' ),
			'code'    => 'review-created',
			'data'    => array (
				'item'   => $return_data,
				'rating' => $insert_data[ 'rating' ],
			),
		] );
	}

	/**
	 * [calculate_rating description]
	 *
	 * @param  [type] $rating_data [description]
	 *
	 * @return [type]              [description]
	 */
	public function calculate_rating( $rating_data ) {
		$fields_rating = [];

		foreach ( $rating_data as $key => $field_data ) {
			$value = (int) $field_data[ 'field_value' ];
			$max   = (int) $field_data[ 'field_max' ];
			$fields_rating[] = round( ( 100 * $value ) / $max, 2 );
		}

		return round( array_sum( $fields_rating ) / count( $fields_rating ) );
	}

	/**
	 * [maybe_update_rating_metadata description]
	 *
	 * @param boolean $post_id [description]
	 * @param boolean $meta_key [description]
	 * @param integer $rating [description]
	 * @param integer $ratio_bound [description]
	 *
	 * @return [type]               [description]
	 */
	public function maybe_update_rating_metadata( $post_id = false, $meta_key = false, $rating = 100, $ratio_bound = 5 ) {

		if ( ! $post_id || empty( $meta_key ) ) {
			return false;
		}

		update_post_meta( $post_id, $meta_key, ( $rating / 100 ) * $ratio_bound );
	}

}