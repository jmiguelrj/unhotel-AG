<?php


namespace JFB\LimitResponses\Interfaces;

interface SettingsIt {

	public function set_settings( array $settings );

	public function get_settings(): array;

	public function set_setting( string $name, $value );

	public function get_setting( string $name );

}
