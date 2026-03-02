<?php


namespace JFB\LimitResponses\Traits;

trait SettingsTrait {

	private $settings = array();

	public function set_settings( array $settings ) {
		$this->settings = $settings;
	}

	public function get_settings(): array {
		return $this->settings;
	}

	public function set_setting( string $name, $value ) {
		$this->settings[ $name ] = $value;
	}

	public function get_setting( string $name ) {
		return $this->settings[ $name ] ?? false;
	}

}
