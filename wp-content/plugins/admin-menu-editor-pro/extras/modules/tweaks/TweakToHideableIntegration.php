<?php

namespace YahnisElsts\AdminMenuEditor\Tweaks;

use ameUtils;
use YahnisElsts\AdminMenuEditor\EasyHide\HideableItemStore;

class TweakToHideableIntegration {
	/**
	 * @var ameTweakManager
	 */
	private $manager;

	public function __construct(ameTweakManager $manager) {
		$this->manager = $manager;
	}

	public function registerHideableItems(HideableItemStore $store) {
		$settings = ameUtils::get($this->manager->loadSettings(), 'tweaks');

		$enabledSections = [
			ameGutenbergBlockManager::SECTION_ID => 'Gutenberg Blocks',
			ameTinyMceButtonManager::SECTION_ID  => 'TinyMCE Buttons',
			'profile'                            => null,
			'sidebar-widgets'                    => null,
			'sidebars'                           => null,
			'gutenberg-general'                  => 'Gutenberg Block Editor',
		];
		$enabledSections = apply_filters('admin_menu_editor-hideable_tweak_sections', $enabledSections);

		$postEditorCategory = $store->getOrCreateCategory('post-editor', 'Editor', null, false, 0, 0);
		$parentCategories = [
			ameGutenbergBlockManager::SECTION_ID => $postEditorCategory,
			ameTinyMceButtonManager::SECTION_ID  => $postEditorCategory,
			'gutenberg-general'                  => $postEditorCategory,
		];

		$categoriesBySection = [];
		foreach ($enabledSections as $sectionId => $customLabel) {
			$section = $this->manager->getSection($sectionId);
			if ( !$section ) {
				continue;
			}

			$parent = null;
			if ( isset($parentCategories[$sectionId]) ) {
				$parent = $parentCategories[$sectionId];
			}

			$category = $store->getOrCreateCategory(
				'tw/' . $sectionId,
				!empty($customLabel) ? $customLabel : str_replace('Hide ', '', $section->getLabel()),
				$parent,
				false,
				0,
				0
			);

			$description = $section->getDescription();
			if ( !empty($description) ) {
				$category->setTooltip($description);
			}

			$categoriesBySection[$sectionId] = $category;
		}

		$generalCat = $store->getOrCreateCategory('admin-ui', 'General', null, true);
		$generalCat->setSortPriority(1);

		foreach ($this->manager->getRegisteredTweaks() as $tweak) {
			$sectionCategoryExists = isset($categoriesBySection[$tweak->getSectionId()]);

			$isHideable = ($sectionCategoryExists || $tweak->isIndependentlyHideable());
			if ( !$isHideable ) {
				continue;
			}

			$tweakParent = $tweak->getParentId();
			if ( !empty($tweakParent) ) {
				$parent = $store->getItemById(self::getHideableIdForTweak($tweakParent));
			} else {
				$parent = null;
			}

			$enabled = ameUtils::get($settings, [$tweak->getId(), 'enabledForActor'], []);
			$inverted = null;

			$categories = [];
			if ( $sectionCategoryExists ) {
				$categories[] = $categoriesBySection[$tweak->getSectionId()];
			}
			$customCategoryId = $tweak->getHideableCategoryId();
			if ( $customCategoryId ) {
				$customCategory = $store->getCategory($customCategoryId);
				if ( $customCategory ) {
					$categories[] = $customCategory;
					//Tweak state should not be inverted, so if the category does that,
					//we'll need to override that setting.
					if ( $customCategory->isInvertingItemState() ) {
						$inverted = false;
					}
				}
			}

			$store->addItem(
				self::getHideableIdForTweak($tweak->getId()),
				$tweak->getHideableLabel(),
				$categories,
				$parent,
				$enabled,
				ameTweakManager::HIDEABLE_ITEM_COMPONENT,
				null,
				$inverted
			);
		}
	}

	public function saveHideableItems($errors, $items) {
		$tweakSettings = ameUtils::get($this->manager->loadSettings(), 'tweaks', []);
		$prefixLength = strlen(ameTweakManager::HIDEABLE_ITEM_PREFIX);
		$anyTweaksModified = false;

		foreach ($items as $id => $item) {
			$tweakId = substr($id, $prefixLength);

			$enabled = $item['enabled'] ?? [];
			$oldEnabled = ameUtils::get($tweakSettings, [$tweakId, 'enabledForActor'], []);

			if ( !ameUtils::areAssocArraysEqual($enabled, $oldEnabled) ) {
				if ( !empty($enabled) ) {
					if ( !isset($tweakSettings[$tweakId]) ) {
						$tweakSettings[$tweakId] = [];
					}
					$tweakSettings[$tweakId]['enabledForActor'] = $enabled;
				} else {
					//To save space, we can simply remove the array if it's empty.
					if ( isset($tweakSettings[$tweakId]['enabledForActor']) ) {
						unset($tweakSettings[$tweakId]['enabledForActor']);
					}
				}
				$anyTweaksModified = true;
			}
		}

		if ( $anyTweaksModified ) {
			$this->manager->setTweakSettings($tweakSettings);
		}

		return $errors;
	}

	private static function getHideableIdForTweak($tweakId): string {
		return ameTweakManager::HIDEABLE_ITEM_PREFIX . $tweakId;
	}
}