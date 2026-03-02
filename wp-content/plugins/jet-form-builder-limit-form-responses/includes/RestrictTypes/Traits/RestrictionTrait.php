<?php


namespace JFB\LimitResponses\RestrictTypes\Traits;

use JFB\LimitResponses\Counters\CycleCounter;
use JFB\LimitResponses\MetaQueries\Interfaces\MetaQueryInterface;
use JFB\LimitResponses\MetaQueries\SettingsMetaQuery;

trait RestrictionTrait {

	private $counter;

	private $meta_settings;

	public function set_counter( CycleCounter $counter ) {
		$this->counter = $counter;
	}

	public function get_counter(): CycleCounter {
		return $this->counter;
	}

	/**
	 * @return MetaQueryInterface|SettingsMetaQuery
	 */
	public function get_meta_settings(): MetaQueryInterface {
		return $this->meta_settings;
	}

	/**
	 * @param MetaQueryInterface|SettingsMetaQuery $query
	 *
	 * @return void
	 */
	public function set_meta_settings( MetaQueryInterface $query ) {
		$this->meta_settings = $query;
	}

}
