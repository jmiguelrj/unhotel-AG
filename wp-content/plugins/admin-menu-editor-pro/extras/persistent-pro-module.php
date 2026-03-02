<?php

use YahnisElsts\AdminMenuEditor\Customizable\Storage\AbstractSettingsDictionary;
use YahnisElsts\AdminMenuEditor\ImportExport\ameExportableComponent;
use YahnisElsts\AdminMenuEditor\ImportExport\ameExportableModule;

class amePersistentProModule extends amePersistentModule implements ameExportableModule {

	/**
	 * @param array $importedData
	 * @internal
	 */
	public function handleDataImport($importedData) {
		//Action: admin_menu_editor-import_data
		if ( !empty($this->moduleId) && isset($importedData, $importedData[$this->moduleId]) ) {
			$this->importSettings($importedData[$this->moduleId]);
		}
	}

	public function exportSettings() {
		if ( isset($this->moduleId) ) {
			if ( $this->settingsWrapperEnabled ) {
				$settings = $this->loadSettings();
				if ( $settings instanceof AbstractSettingsDictionary ) {
					$exported = $settings->toArray();
					if ( !empty($exported) ) {
						return $exported;
					}
				} else {
					return null;
				}
			} else {
				return $this->loadSettings();
			}
		}
		return null;
	}

	public function importSettings($newSettings) {
		if ( !is_array($newSettings) || empty($newSettings) ) {
			return false;
		}

		$this->mergeSettingsWith($newSettings);
		$this->saveSettings();
		return true;
	}

	/**
	 * @return string
	 */
	public function getExportOptionLabel() {
		return $this->getTabTitle();
	}

	public function getExportOptionDescription() {
		return '';
	}

	public function getExportableComponents(): array {
		return [
			ameExportableComponent::builder($this->getExportOptionLabel())
				->description($this->getExportOptionDescription())
				->exportCallback([$this, 'exportSettings'])
				->importCallback([$this, 'importSettings'])
				->build(),
		];
	}
}