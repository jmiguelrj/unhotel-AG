<?php

namespace JFB_Formless\Services;

use Jet_Form_Builder\Db_Queries\Exceptions\Sql_Exception;
use Jet_Form_Builder\Db_Queries\Query_Conditions_Builder;
use Jet_Form_Builder\Exceptions\Query_Builder_Exception;
use JFB_Formless\DB\Models;
use JFB_Formless\DB\Views;

class Route {

	const RESTRICTION_USER = 0;
	const RESTRICTION_ROLE = 1;
	const RESTRICTION_CAP  = 2;

	const ACTION_AJAX = 0;
	const ACTION_REST = 1;
	const ACTION_URL  = 2;

	/**
	 * Field
	 *
	 * @var int
	 */
	private $id = 0;
	/**
	 * Dependency
	 *
	 * @var int
	 */
	private $form_id = 0;
	/**
	 * Field
	 * Possible values: 0, 1, 2
	 *  0 - by WP AJAX
	 *  1 - by REST API
	 *  2 - by URL
	 *
	 * @var int
	 */
	private $action_type;
	/**
	 * @var bool
	 */
	private $restricted;
	/**
	 * Field
	 * Possible values: 0, 1, 2
	 * 0 - any registered user
	 * 1 - restricted by role
	 * 2 - restricted by capability
	 *
	 * @var int
	 */
	private $restriction_type;
	/**
	 * Field
	 *
	 * @var array
	 */
	private $restriction_roles;
	/**
	 * Field
	 *
	 * @var string
	 */
	private $restriction_cap;
	/**
	 * Field
	 *
	 * @var bool
	 */
	private $log;

	/**
	 * @return void
	 * @throws ValidateException
	 */
	public function validate_required() {
		// to trigger validation
		$this->set_form_id( $this->get_form_id() );
	}

	public function has_permission(): bool {
		if ( ! $this->is_restricted() ) {
			return true;
		}

		switch ( $this->get_restriction_type() ) {
			case self::RESTRICTION_USER:
				return is_user_logged_in();
			case self::RESTRICTION_ROLE:
				return $this->has_user_role();
			case self::RESTRICTION_CAP:
				return current_user_can( $this->get_restriction_cap() );
			default:
				return true;
		}
	}

	private function has_user_role(): bool {
		$user          = wp_get_current_user();
		$flipped_roles = array_flip( $user->roles );
		$roles         = $this->get_restriction_roles();

		foreach ( $roles as $role ) {
			if ( isset( $flipped_roles[ $role ] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param int $id
	 *
	 * @return void
	 * @throws ValidateException
	 */
	public function set_id( int $id ) {
		if ( 0 >= $id ) {
			throw new ValidateException( 'invalid_id' );
		}
		$this->id = $id;
	}

	/**
	 * @param int $form_id
	 *
	 * @return void
	 * @throws ValidateException
	 */
	public function set_form_id( int $form_id ) {
		if ( 0 >= $form_id ) {
			throw new ValidateException( 'invalid_form_id' );
		}
		$this->form_id = $form_id;
	}

	/**
	 * @param int $action_type
	 *
	 * @return void
	 */
	public function set_action_type( int $action_type ) {
		$this->action_type = $action_type;
	}

	/**
	 * @param bool $log
	 */
	public function set_log( bool $log ) {
		$this->log = $log;
	}

	/**
	 * @param int $restriction_type
	 */
	public function set_restriction_type( int $restriction_type ) {
		$this->restriction_type = $restriction_type;
	}

	/**
	 * @param string $restriction_cap
	 */
	public function set_restriction_cap( string $restriction_cap ) {
		$this->restriction_cap = $restriction_cap;
	}

	/**
	 * @param array $restriction_roles
	 */
	public function set_restriction_roles( array $restriction_roles ) {
		$this->restriction_roles = $restriction_roles;
	}

	/**
	 * @param bool $restricted
	 */
	public function set_restricted( bool $restricted ) {
		$this->restricted = $restricted;
	}

	/**
	 * @return int
	 */
	public function get_form_id(): int {
		return $this->form_id;
	}

	/**
	 * @return int|null
	 */
	public function get_action_type() {
		return $this->action_type;
	}

	/**
	 * @return int|null
	 */
	public function get_restriction_type() {
		return $this->restriction_type;
	}

	/**
	 * @return string|null
	 */
	public function get_restriction_cap() {
		return $this->restriction_cap;
	}

	/**
	 * @return array|null
	 */
	public function get_restriction_roles() {
		return $this->restriction_roles;
	}

	/**
	 * @return bool|null
	 */
	public function is_log() {
		return $this->log;
	}

	/**
	 * @return int
	 */
	public function get_id(): int {
		return $this->id;
	}

	/**
	 * @return bool|null
	 */
	public function is_restricted() {
		return $this->restricted;
	}

	public function reset() {
		/** @noinspection PhpUnhandledExceptionInspection */
		$this->set_form_id( 1 );
		$this->set_action_type( 0 );
		$this->set_restricted( false );
		$this->set_log( false );
		$this->set_restriction_type( 0 );
		$this->set_restriction_roles( array() );
		$this->set_restriction_cap( '' );
	}

}
