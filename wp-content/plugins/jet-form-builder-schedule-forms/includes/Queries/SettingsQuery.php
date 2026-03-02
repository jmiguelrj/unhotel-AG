<?php

namespace JFB\ScheduleForms\Queries;

class SettingsQuery {

	const META_KEY = '_jf_schedule_form';

	private $settings = array();
	private $form_id  = 0;

	public function fetch() {
		$form_id = $this->get_form_id();
		$meta    = get_post_meta( $form_id, self::META_KEY, true );

		$this->set_settings(
			$meta ? json_decode( $meta, true ) : array()
		);
	}

	/**
	 * @param array $settings
	 */
	public function set_settings( array $settings ) {
		$this->settings = $settings;
	}

	/**
	 * @param int $form_id
	 */
	public function set_form_id( int $form_id ) {
		$this->form_id = $form_id;
	}

	/**
	 * @return int
	 */
	public function get_form_id(): int {
		return $this->form_id;
	}

	/**
	 * @return array
	 */
	public function get_settings(): array {
		return $this->settings;
	}

	public function get_setting( string $name ) {
		return $this->settings[ $name ] ?? false;
	}

	public function set_setting( string $name, $value ) {
		$this->settings[ $name ] = $value;
	}

}