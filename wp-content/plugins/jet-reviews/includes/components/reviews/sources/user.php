<?php
namespace Jet_Reviews\Reviews\Source;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class User extends Base {

	/**
	 * [get_slug description]
	 * @return [type] [description]
	 */
	public function get_slug() {
		return 'user';
	}

	/**
	 * [get_slug description]
	 * @return [type] [description]
	 */
	public function get_name() {
		return __( 'User', 'jet-reviews' );
	}

	/**
	 * [get_source_id description]
	 * @return [type] [description]
	 */
	public function get_current_id() {
		$current_id = $this->get_current_author_name();
		$slug = $this->get_slug();

		return apply_filters( "jet-reviews/source/source-{$slug}/current-id", $current_id, $this );
	}

	/**
	 * @return false|mixed|string
	 */
	public function get_type( $args = [] ) {
		return 'wp-user';
	}

	/**
	 * @param array $args
	 *
	 * @return mixed|string
	 */
	public function get_item_label( $args = [] ) {
		$current_id = $this->get_current_id();

		if ( ! empty( $args['source_id'] ) ) {
			$current_id = $args['source_id'];
		}

		$user_data = jet_reviews()->user_manager->get_raw_user_data( $current_id );

		return $user_data['name'];
	}

	/**
	 * @param array $args
	 *
	 * @return mixed|string
	 */
	public function get_item_decsription( $args = [] ) {
		$post_id = $this->get_current_id();

		if ( ! empty( $args['source_id'] ) ) {
			$post_id = $args['source_id'];
		}

		return '';
	}

	/**
	 * @param array $args
	 *
	 * @return false|string
	 */
	public function get_item_thumb_url( $args = [] ) {
		$current_id = $this->get_current_id();

		if ( ! empty( $args['source_id'] ) ) {
			$current_id = $args['source_id'];
		}

		$user_data = jet_reviews()->user_manager->get_raw_user_data( $current_id );

		return get_avatar_url( $user_data['mail'], array(
			'size' => 256,
		) );
	}

	/**
	 * [get_source_settings description]
	 * @return [type] [description]
	 */
	public function get_settings() {
		return [];
	}

	/**
	 * @return array
	 */
	public function get_types_options() {
		return [
			[
				'label' => __( 'WP User', 'jet-reviews' ),
				'value' => 'wp-user',
			],
		];
	}

	/**
	 * @return array|mixed|string|null
	 */
	public function get_current_author_name() {

		if ( is_author() ) {
			$author = get_queried_object();

			return (int) $author->ID;
		} elseif ( is_singular() ) {
			return (int) get_post_field( 'post_author', get_the_ID() );
		}

		return get_current_user_id();
	}

}
