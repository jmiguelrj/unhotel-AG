<?php

namespace JFB\LimitResponses\MetaQueries;

use JFB\LimitResponses\Interfaces\SettingsIt;
use JFB\LimitResponses\Traits\SettingsTrait;
use JFB\LimitResponses\Vendor\JFBCore\Common\MetaQuery;

class FormCountersMetaQuery implements Interfaces\MetaQueryInterface, SettingsIt {

	use SettingsTrait;

	const META_KEY = '_jf_limit_responses_counters';

	private $settings_query;

	public function __construct(
		SettingsMetaQuery $settings_query
	) {
		$this->settings_query = $settings_query;
	}

	public function fetch() {
		$this->set_settings(
			MetaQuery::get_json_meta(
				array(
					'id'  => $this->settings_query->get_form_id(),
					'key' => self::META_KEY,
				)
			)
		);
	}

	public function update() {
		MetaQuery::set_json_meta(
			array(
				'id'    => $this->settings_query->get_form_id(),
				'value' => $this->get_settings(),
				'key'   => self::META_KEY,
			)
		);
	}
}