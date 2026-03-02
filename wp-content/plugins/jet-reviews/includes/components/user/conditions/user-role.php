<?php
namespace Jet_Reviews\User\Conditions;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class User_Role extends Base_Condition {

	/**
	 * [$slug description]
	 * @var string
	 */
	private $slug = 'user-role';

	/**
	 * [$invalid_message description]
	 * @var boolean
	 */
	private $invalid_message = false;

	/**
	 * [__construct description]
	 */
	public function __construct() {
		$this->invalid_message = __( '*User with this role cannot publish a new review', 'jet-reviews' );
	}

	/**
	 * @return string
	 */
	public function get_type() {
		return 'can-review';
	}

	/**
	 * [get_slug description]
	 * @return [type] [description]
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * [get_valid_message description]
	 * @return [type] [description]
	 */
	public function get_invalid_message() {
		return apply_filters( 'jet-reviews/user/conditions/invalid-message/{$this->slug}', $this->invalid_message, $this );
	}

	/**
	 * [check description]
	 * @return [type] [description]
	 */
	public function check( $user_data = [], $review_type_settings = false ) {

		if ( empty( $user_data ) ) {
			return true;
		}

		$roles = $user_data['roles'];

		foreach ( $roles as $key => $role ) {

			if ( in_array( $role, $review_type_settings['allowed_roles'] ) ) {
				return true;
			}
		}

		return false;
	}

}
