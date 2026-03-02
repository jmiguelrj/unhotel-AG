<?php

namespace JFB\LimitResponses\MetaQueries;

use JFB\LimitResponses\Interfaces\FormIt;
use JFB\LimitResponses\Interfaces\SettingsIt;
use JFB\LimitResponses\Traits\FormTrait;
use JFB\LimitResponses\Traits\SettingsTrait;
use JFB\LimitResponses\Vendor\JFBCore\Common\MetaQuery;

class SettingsMetaQuery implements Interfaces\MetaQueryInterface, FormIt, SettingsIt {

	use FormTrait;
	use SettingsTrait;

	const META_KEY = '_jf_limit_responses';

	const CLOSED_MESSAGE   = 'closed_message';
	const ERROR_MESSAGE    = 'error_message';
	const GUEST_MESSAGE    = 'guest_message';
	const RESTRICT_MESSAGE = 'restricted_message';

	const DEFAULT_MESSAGES = array(
		self::GUEST_MESSAGE    => 'Please log in to submit the form',
		self::ERROR_MESSAGE    => 'This form can no longer accept your request, sorry',
		self::RESTRICT_MESSAGE => 'You have already submitted a request from this form',
		self::CLOSED_MESSAGE   => 'This form is no longer available',
	);

	public function fetch() {
		$this->set_settings(
			MetaQuery::get_json_meta(
				array(
					'id'  => $this->get_form_id(),
					'key' => self::META_KEY,
				)
			)
		);
	}

	public function update() {
		MetaQuery::set_json_meta(
			array(
				'id'    => $this->get_form_id(),
				'value' => $this->get_settings(),
				'key'   => self::META_KEY,
			)
		);
	}

	public function get_restrict_by(): string {
		$restrict_by = $this->get_setting( 'restrict_by' );

		return $restrict_by ?: 'id_address';
	}

	public function is_restricted_general(): bool {
		return (bool) $this->get_setting( 'enable' );
	}

	public function is_restricted_user(): bool {
		return (bool) $this->get_setting( 'restrict_users' );
	}


	public function get_message( $type ) {
		return $this->get_setting( $type ) ?: self::DEFAULT_MESSAGES[ $type ];
	}
}