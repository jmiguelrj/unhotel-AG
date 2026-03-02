<?php

namespace JFB\LimitResponses\Counters;

use JFB\LimitResponses\MetaQueries\SettingsMetaQuery;

class SyncedCycleCounter extends CycleCounter {

	/**
	 * @var SettingsMetaQuery
	 */
	private $query;

	public function __construct(
		SettingsMetaQuery $query
	) {
		$this->query = $query;
	}

	public function is_reached_limit(): bool {
		$this->set_limit( (int) $this->get_query()->get_setting( 'cycle_limit' ) );
		$this->set_cycle( (string) $this->get_query()->get_setting( 'cycle' ) );

		return parent::is_reached_limit();
	}

	/**
	 * @return SettingsMetaQuery
	 */
	public function get_query(): SettingsMetaQuery {
		return $this->query;
	}


}