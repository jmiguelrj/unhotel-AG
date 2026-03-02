<?php

namespace JFB\LimitResponses\MetaQueries;

use JFB\LimitResponses\Exceptions\LimitException;
use JFB\LimitResponses\Interfaces\SettingsIt;
use JFB\LimitResponses\LimitResponses;
use JFB\LimitResponses\Traits\SettingsTrait;

class UserCounterMetaQuery implements Interfaces\MetaQueryInterface, SettingsIt {

	use SettingsTrait;

	const META_KEY = SettingsMetaQuery::META_KEY;

	/**
	 * @return void
	 * @throws LimitException
	 */
	public function fetch() {
		$all_user_meta = get_user_meta(
			$this->get_user_id_or_throw(),
			LimitResponses::PLUGIN_META_KEY,
			true
		);

		$counters = $all_user_meta
			? json_decode( $all_user_meta, true )
			: array();

		$this->set_settings( $counters );
	}

	/**
	 * @return void
	 * @throws LimitException
	 */
	public function update() {
		update_user_meta(
			$this->get_user_id_or_throw(),
			LimitResponses::PLUGIN_META_KEY,
			wp_json_encode( $this->get_settings() )
		);
	}

	/**
	 * @return int
	 * @throws LimitException
	 */
	private function get_user_id_or_throw(): int {
		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			throw new LimitException( LimitResponses::GUEST_MESSAGE );
		}

		return $user_id;
	}
}