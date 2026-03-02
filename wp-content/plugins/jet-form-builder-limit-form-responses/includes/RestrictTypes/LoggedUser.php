<?php


namespace JFB\LimitResponses\RestrictTypes;

use JFB\LimitResponses\Counters\SyncedCycleCounter;
use JFB\LimitResponses\MetaQueries\SettingsMetaQuery;
use JFB\LimitResponses\MetaQueries\UserCounterMetaQuery;
use JFB\LimitResponses\RestrictTypes\Interfaces\RestrictionIt;
use JFB\LimitResponses\RestrictTypes\Traits\RestrictionTrait;
use JFB\LimitResponses\Exceptions\LimitException;
use JFB\LimitResponses\LimitResponses;

class LoggedUser implements RestrictionIt {

	use RestrictionTrait;

	private $query;

	public function __construct(
		SyncedCycleCounter $counter,
		UserCounterMetaQuery $query,
		SettingsMetaQuery $settings
	) {
		$this->set_counter( $counter );
		$this->set_meta_settings( $settings );
		$this->query = $query;
	}

	public function get_id(): string {
		return 'user';
	}

	/**
	 * @return void
	 * @throws LimitException
	 */
	public function before_run() {
		$this->get_query()->fetch();
		$user_meta = $this->get_query()->get_settings();
		$form_id   = $this->get_meta_settings()->get_form_id();

		if ( ! isset( $user_meta[ $form_id ] ) ) {
			$user_meta[ $form_id ] = 0;
		}

		if ( ! is_array( $user_meta[ $form_id ] ) ) {
			$user_meta[ $form_id ] = array(
				'count'    => $user_meta[ $form_id ],
				'reset_at' => - 1,
			);
		}

		$this->get_counter()->set_count( (int) $user_meta[ $form_id ]['count'] );
		$this->get_counter()->set_reset_at( (int) $user_meta[ $form_id ]['reset_at'] );
	}

	/**
	 * @return void
	 * @throws LimitException
	 */
	public function increment() {
		// increase reset_at timestamp
		$this->get_counter()->new_reset_at();

		$user_meta = $this->get_query()->get_settings();

		$user_meta[ $this->get_meta_settings()->get_form_id() ] = array(
			'count'    => 1 + $this->get_counter()->get_count(),
			'reset_at' => $this->get_counter()->get_reset_at(),
		);

		$this->get_query()->set_settings( $user_meta );
		$this->get_query()->update();
	}

	/**
	 * @return UserCounterMetaQuery
	 */
	public function get_query(): UserCounterMetaQuery {
		return $this->query;
	}


}
