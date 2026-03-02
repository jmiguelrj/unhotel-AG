<?php


namespace JFB\LimitResponses\RestrictTypes\Interfaces;

use JFB\LimitResponses\Counters\CycleCounter;
use JFB\LimitResponses\MetaQueries\Interfaces\MetaQueryInterface;
use JFB\LimitResponses\MetaQueries\SettingsMetaQuery;

interface RestrictionIt {

	public function get_id(): string;

	public function increment();

	public function set_counter( CycleCounter $counter );

	public function get_counter(): CycleCounter;

	public function before_run();

	/**
	 * @return MetaQueryInterface|SettingsMetaQuery
	 */
	public function get_meta_settings(): MetaQueryInterface;

	/**
	 * @param MetaQueryInterface|SettingsMetaQuery $query
	 *
	 * @return void
	 */
	public function set_meta_settings( MetaQueryInterface $query );

}
