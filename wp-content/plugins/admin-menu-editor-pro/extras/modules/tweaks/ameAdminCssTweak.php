<?php

namespace YahnisElsts\AdminMenuEditor\Tweaks;

use YahnisElsts\AdminMenuEditor\Customizable\Builders\BaseElementBuilder;
use YahnisElsts\AdminMenuEditor\Customizable\Builders\ElementBuilderFactory;
use YahnisElsts\AdminMenuEditor\Customizable\Schemas\SchemaFactory;
use YahnisElsts\AdminMenuEditor\Customizable\Storage\AbstractSettingsDictionary;

class ameAdminCssTweak extends ameDelegatedTweak {
	public function getSettingsSchemaFields(SchemaFactory $s): array {
		return array_merge(parent::getSettingsSchemaFields($s), [
			'label' => $s->string()->defaultValue(''),
			'css'   => $s->string(),
		]);
	}

	public function createUiElement(
		ElementBuilderFactory      $b,
		AbstractSettingsDictionary $settings,
		                           $settingPrefix,
		array                      $extraElementParams = []
	): BaseElementBuilder {
		$extraElementParams['objectId'] = $this->getId();
		//Only user-created tweaks can be edited/deleted, and their IDs always start with 'utw-'.
		if ( strpos($this->getId(), 'utw-') === 0 ) {
			$extraElementParams['hasTweakActions'] = true;
		}

		$defaultControl = parent::createUiElement($b, $settings, $settingPrefix, $extraElementParams);

		$defaultControl->add(
			$b->codeEditor($settingPrefix . '.css')->cssMode()
		);

		return $defaultControl;
	}

	public function hasAnySettings() {
		//This is necessary for the extra settings to be passed to the apply() method.
		return true;
	}
}