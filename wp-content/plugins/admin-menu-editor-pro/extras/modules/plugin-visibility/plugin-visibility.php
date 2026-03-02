<?php

use YahnisElsts\AdminMenuEditor\ImportExport\ameBasicExportableModule;
use YahnisElsts\AdminMenuEditor\ImportExport\ameExportableComponent;

require_once AME_ROOT_DIR . '/modules/plugin-visibility/plugin-visibility.php';

class amePluginVisibilityPro extends amePluginVisibility implements ameBasicExportableModule {
	public function __construct($menuEditor) {
		parent::__construct($menuEditor);

		if ( class_exists('ReflectionClass', false) ) {
			//This should never throw an exception since the parent class must exist for this constructor to be run.
			$reflector = new ReflectionClass(get_parent_class($this));
			$this->moduleDir = dirname($reflector->getFileName());
			$this->moduleId = basename($this->moduleDir);
		}
	}

	public function getExportableComponents(): array {
		return [
			ameExportableComponent::builder('Plugin visibility')
				->exportCallback(function () {
					$settings = $this->loadSettings();
					if ( empty($settings['plugins']) && empty($settings['grantAccessByDefault']) ) {
						return null;
					}
					return $settings;
				})
				->importCallback(function ($newSettings) {
					if ( !is_array($newSettings) || empty($newSettings) ) {
						return false;
					}

					$this->loadSettings();
					$this->settings = array_merge($this->settings, $newSettings);
					$this->saveSettings();
					return true;
				})
				->build(),
		];
	}
}