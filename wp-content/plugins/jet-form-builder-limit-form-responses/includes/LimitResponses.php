<?php


namespace JFB\LimitResponses;

use JFB\LimitResponses\Exceptions\LimitException;
use JFB\LimitResponses\MetaQueries\FormCountersMetaQuery;
use JFB\LimitResponses\MetaQueries\SettingsMetaQuery;
use JFB\LimitResponses\RestrictTypes\General;
use JFB\LimitResponses\RestrictTypes\Interfaces\RestrictionIt;

class LimitResponses {

	// legacy constants
	const PLUGIN_META_KEY         = SettingsMetaQuery::META_KEY;
	const PLUGIN_META_COUNTER_KEY = FormCountersMetaQuery::META_KEY;

	// legacy constants
	const CLOSED_MESSAGE   = SettingsMetaQuery::CLOSED_MESSAGE;
	const ERROR_MESSAGE    = SettingsMetaQuery::ERROR_MESSAGE;
	const GUEST_MESSAGE    = SettingsMetaQuery::GUEST_MESSAGE;
	const RESTRICT_MESSAGE = SettingsMetaQuery::RESTRICT_MESSAGE;

	private $plugin;
	private $query;

	/**
	 * @param Plugin $plugin
	 * @param SettingsMetaQuery $query
	 *
	 * @throws Vendor\Auryn\ConfigException
	 */
	public function __construct(
		Plugin $plugin,
		SettingsMetaQuery $query
	) {
		$this->plugin = $plugin;
		$this->query  = $query;

		$this->share_restrictions();
	}

	/**
	 * @return void
	 * @throws Vendor\Auryn\ConfigException
	 */
	private function share_restrictions() {
		foreach ( $this->declare_restrict_types() as $restriction_class ) {
			$this->plugin->get_injector()->share( $restriction_class );
		}

		$this->plugin->get_injector()->share( General::class );
	}

	private function declare_restrict_types(): \Generator {
		yield RestrictTypes\IPAddress::class;
		yield RestrictTypes\LoggedUser::class;
		yield RestrictTypes\Cookie::class;
		yield RestrictTypes\Session::class;
	}

	private function restrict_types(): \Generator {
		yield from $this->declare_restrict_types();

		$restrictions = apply_filters(
			'jet-fb/limit-form-responses/register/restrict-user-types',
			array()
		);

		if ( ! is_array( $restrictions ) ) {
			yield from $restrictions;

			return;
		}

		foreach ( $restrictions as $restriction ) {
			yield $restriction;
		}
	}


	public function try_to_increment() {
		if ( ! $this->get_query()->is_restricted_user() ) {
			return;
		}
		$this->get_restriction()->increment();
	}

	/**
	 * @throws LimitException
	 */
	public function is_reached_limit() {
		if ( ! $this->get_query()->is_restricted_user() ) {
			return;
		}

		if ( $this->get_restriction()->get_counter()->is_reached_limit() ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			throw new LimitException( 'failed' );
		}
	}

	public function try_to_increment_general() {
		if ( ! $this->get_query()->is_restricted_general() ) {
			return;
		}

		$this->get_general_restriction()->increment();
	}

	/**
	 * @throws LimitException
	 */
	public function is_reached_general_limit() {
		if ( ! $this->get_query()->is_restricted_general() ) {
			return;
		}

		$this->get_general_restriction()->before_run();

		if ( $this->get_general_restriction()->get_counter()->is_reached_limit() ) {
			throw new LimitException( 'general' );
		}
	}

	/**
	 * @return RestrictTypes\Interfaces\RestrictionIt|false
	 */
	public function get_restriction() {
		$restrict_by = $this->get_query()->get_restrict_by();

		foreach ( $this->restrict_types() as $restriction_class ) {
			/** @var RestrictionIt $restriction */
			$restriction = is_object( $restriction_class )
				? $restriction_class
				: $this->plugin->get_injector()->make( $restriction_class );

			if ( $restriction->get_id() === $restrict_by ) {
				return $restriction;
			}
		}

		return false;
	}

	/** @noinspection PhpUnhandledExceptionInspection */
	public function get_general_restriction(): General {
		return $this->plugin->get_injector()->make( General::class );
	}

	/**
	 * @return SettingsMetaQuery
	 */
	public function get_query(): SettingsMetaQuery {
		return $this->query;
	}

}
