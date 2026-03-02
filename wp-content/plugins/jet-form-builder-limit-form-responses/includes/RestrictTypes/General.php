<?php


namespace JFB\LimitResponses\RestrictTypes;

use JFB\LimitResponses\Counters\CycleCounter;
use JFB\LimitResponses\MetaQueries\FormCountersMetaQuery;
use JFB\LimitResponses\MetaQueries\SettingsMetaQuery;
use JFB\LimitResponses\RestrictTypes\Interfaces\RestrictionIt;
use JFB\LimitResponses\RestrictTypes\Traits\RestrictionTrait;

class General implements RestrictionIt {

	const SUBMISSIONS_KEY = '_form_submissions';

	use RestrictionTrait;

	private $query;

	public function __construct(
		CycleCounter $counter,
		FormCountersMetaQuery $query,
		SettingsMetaQuery $settings
	) {
		$this->set_counter( $counter );
		$this->set_meta_settings( $settings );
		$this->query = $query;
	}

	public function get_id(): string {
		return 'general';
	}

	public function before_run() {
		$this->get_query()->fetch();
		$counters = $this->get_query()->get_settings();

		$this->get_counter()->set_limit( (int) $this->get_meta_settings()->get_setting( 'limit' ) );
		$this->get_counter()->set_count( (int) ( $counters[ self::SUBMISSIONS_KEY ]['count'] ?? 0 ) );
	}

	public function increment() {
		$counters = $this->get_query()->get_settings();

		$counters[ self::SUBMISSIONS_KEY ] = $counters[ self::SUBMISSIONS_KEY ] ?? array( 'count' => 0 );
		++ $counters[ self::SUBMISSIONS_KEY ]['count'];

		$this->get_query()->set_settings( $counters );
		$this->get_query()->update();
	}

	/**
	 * @return FormCountersMetaQuery
	 */
	public function get_query(): FormCountersMetaQuery {
		return $this->query;
	}

}
