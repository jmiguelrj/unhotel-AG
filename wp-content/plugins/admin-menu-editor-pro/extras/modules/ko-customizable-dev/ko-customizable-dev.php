<?php

namespace YahnisElsts\AdminMenuEditor\KoCustomizableDev;

use YahnisElsts\AdminMenuEditor\Tweaks\ameTweakManager;
use YahnisElsts\AdminMenuEditor\Customizable\Builders\ControlBuilder;
use YahnisElsts\AdminMenuEditor\Customizable\Controls\AlignmentSelector;
use YahnisElsts\AdminMenuEditor\Customizable\Controls\ControlFlow\ForEachBlock;
use YahnisElsts\AdminMenuEditor\Customizable\Controls\EventButton;
use YahnisElsts\AdminMenuEditor\Customizable\Controls\InterfaceStructure;
use YahnisElsts\AdminMenuEditor\Customizable\Rendering\Context;
use YahnisElsts\AdminMenuEditor\Customizable\Rendering\FormTableRenderer;
use YahnisElsts\AdminMenuEditor\Customizable\SettingCondition;
use YahnisElsts\AdminMenuEditor\Customizable\Settings\AbstractSetting;
use YahnisElsts\AdminMenuEditor\Customizable\Storage\AbstractSettingsDictionary;
use YahnisElsts\AdminMenuEditor\Customizable\Storage\LazyArrayStorage;
use YahnisElsts\AdminMenuEditor\ProCustomizable\Controls\BorderStyleSelector;
use YahnisElsts\WpDependencyWrapper\v1\ScriptDependency;
use YahnisElsts\AdminMenuEditor\Customizable\Schemas\SchemaFactory;

class AmeKoCustomizableDevModule extends \ameModule {
	protected $tabSlug = 'customizable-dev';
	protected $tabTitle = 'KO Prototype';

	private $settings;

	public function __construct($menuEditor) {
		parent::__construct($menuEditor);

		$this->settings = new CustomizableTestSettings();
	}

	public function enqueueTabScripts() {
		parent::enqueueTabScripts();

		$structure = $this->getInterfaceStructure();
		$structure->enqueueKoComponentDependencies();

		$serializationContext = new Context();

		//Collect all settings used in the UI structure.
		$settingsToSerialize = iterator_to_array($structure->getAllReferencedSettings($serializationContext));

		//Include the settings used to store the user-defined tweak suffix.
		$tweakModule = $this->getTweakModule();
		$tweakSettings = $tweakModule->loadSettings();
		$lastSuffixSetting = $tweakSettings->getSetting('lastUserTweakSuffix');
		$settingsToSerialize[$lastSuffixSetting->getId()] = $lastSuffixSetting;

		//For user-defined tweaks, we also need their "typeId" setting and other metadata.
		//These are not directly referenced in the UI structure.
		$userDefinedTweaks = $tweakSettings->get('userDefinedTweaks', []);
		foreach(array_keys($userDefinedTweaks) as $tweakId) {
			foreach(['typeId', 'isUserDefined', 'label'] as $field) {
				$settingId = "tweaks.$tweakId.$field";
				$setting = $tweakSettings->getSetting($settingId);
				$settingsToSerialize[$setting->getId()] = $setting;
			}
		}

		$baseDeps = $this->menuEditor->get_base_dependencies();

		ScriptDependency::create(plugins_url('ko-customizable-dev.js', __FILE__))
			->addDependencies(
				'jquery',
				'ame-customizable-settings',
				'ame-lodash',
				'ame-knockout',
				'jquery-qtip',
				$baseDeps['ame-actor-manager'],
				$baseDeps['ame-actor-selector']
			)
			->setType('module')
			->addJsVariable('wsAmeKoPrototypeData', [
				'settings'             => AbstractSetting::serializeSettingsForJs($settingsToSerialize),
				'interfaceStructure'   => $structure->serializeForJs($serializationContext),
				'preferenceCookiePath' => ADMIN_COOKIE_PATH,
			])
			->enqueue();
	}

	protected function outputMainTemplate() {
		require AME_ROOT_DIR . '/modules/actor-selector/actor-selector-template.php';
		?>
		<h2>Knockout Version</h2>
		<div id="ws-ame-ko-prototype-container"
		     data-bind="event: {
		        'adminMenuEditor:deleteObject':  onDeleteTweak,
		        'adminMenuEditor:editObject':    onEditTweak,
		        'adminMenuEditor:addCssSnippet': onAddCssSnippet
		     }">
			<!--suppress HtmlUnknownTag -->
			<ame-si-structure params="structure: interfaceStructure">
				Knockout will replace contents of this custom element with the interface structure.
			</ame-si-structure>

			<div id="ame-twm-add-admin-css-dialog-v2"
			     data-bind="ameDialog: adminCssEditorDialog, ameEnableDialogButton: adminCssEditorDialog.isConfirmButtonEnabled"
			     title="Add admin CSS snippet"
			     style="display: none;" class="ame-twm-dialog">
				<div class="ws_dialog_subpanel">
					<label for="ame-twm-new-css-tweak-label"><strong>Name</strong></label><br>
					<input type="text" id="ame-twm-new-css-tweak-label" class="large-text"
					       data-bind="textInput: adminCssEditorDialog.tweakLabel">
				</div>

				<div class="ws_dialog_subpanel">
					<label for="ame-twm-new-css-tweak-code"><strong>CSS code</strong></label><br>
					<textarea id="ame-twm-new-css-tweak-code" cols="40" rows="6"
					          data-bind="value: adminCssEditorDialog.cssCode,
			          ameCodeMirror: {options: $root.cssHighlightingOptions, refreshTrigger: adminCssEditorDialog.isOpen}"></textarea>
				</div>
			</div>
		</div>
		<style>
			.ame-twm-dialog .CodeMirror {
				height: auto;
			}

			.ame-twm-dialog .CodeMirror-scroll {
				min-height: 100px;
				max-height: 500px;
			}
		</style>
		<hr>
		<?php

		echo '<h2>FormTableRenderer Version</h2>';
		$structure = $this->getInterfaceStructure();
		$renderer = new FormTableRenderer();
		$renderer->renderStructure($structure);
		echo '<hr>';

		return true;
	}

	private function getInterfaceStructure(): InterfaceStructure {
		$s = $this->settings;
		$b = $s->elementBuilder();
		$enumSetting = $s->getSetting('exampleEnum');

		$structure = $b->structure(
			$b->section(
				'Tweak Refactoring Prototypes',
				new ForEachBlock(
					$s->ref('tweakRefactor.snippets'),
					[
						$b->group(
							'title here',
							$b->textBox($s->ref('$item.label')),
							$b->codeEditor($s->ref('$item.code'))/*,
						$b->textBox($s->ref('$item.enabledForActor'))*/
						),
					]
				),
				$b->auto('tweakRefactor.tweaks')
			)->id('ame-kcd-tweak-refactoring'),
			$b->section(
				'Actor Features',
				$b->html('<p class="description">Features or permissions that can be enabled or disabled for the currently selected actor.</p>'),
				$b->group(
					'Flat List',
					$b->actorFeatureCheckbox('tweakRefactor.actorFeatureExampleA'),
					$b->actorFeatureCheckbox('tweakRefactor.actorFeatureExampleB'),
					$b->actorFeatureCheckbox('tweakRefactor.actorFeatureExampleC')
				)->stacked(),
				$b->group(
					'Nested Features',
					$b->actorFeatureCheckbox('tweakRefactor.parentFeature')
						->add(
							$b->actorFeatureCheckbox('tweakRefactor.childFeatureA'),
							$b->actorFeatureCheckbox('tweakRefactor.childFeatureB')
						)
				)
			)->id('ame-kcd-actor-features'),
			$b->section(
				'Nested Collections',
				new ForEachBlock(
					$s->ref('nestedCollections'),
					[
						$b->group(
							'Array Item',
							$b->textBox($s->ref('$item.name')),
							$b->number($s->ref('$item.value')),
							$b->html('<h4>Inner Items</h4>'),
							new ForEachBlock(
								$s->ref('$item.innerItems'),
								[
									$b->group(
										'Inner Item',
										$b->textArea($s->ref('$item.description')),
										$b->number($s->ref('$item.amount'))
									),
								]
							),
							$b->html('<h4>Tags</h4>'),
							$b->textBox($s->ref('$item.tags'))
						),
					]
				)
			)->id('ame-kcd-nested-collections'),
			$b->section(
				'Schema-Based Settings',
				$b->auto('schemaString'),
				$b->auto('schemaBool'),
				$b->auto('schemaInt'),
				$b->auto('schemaEnum'),
				$b->auto('schemaEnumMixed'),
				$b->auto('schemaColor'),

				$b->auto('schemaFont')
			)->id('ame-kcd-schema-settings'),
			$b->section(
				'Sample Settings',
				$b->auto('fooInt'),
				$b->auto('barString'),
				$b->auto('bazBool'),
				$b->radioGroup('exampleEnum')
					->choiceChild(
						'one',
						$b->auto('nestedOne')->enabled(
							new SettingCondition($enumSetting, '==', 'one')
						)
					)
					->choiceChild(
						'3.05',
						$b->auto('nestedThree')->enabled(
							new SettingCondition($enumSetting, '==', '3.05')
						)
					)
					->classes('ame-rg-with-color-pickers')
			)->id('ame-kcd-sample-settings'),
			$b->section(
				'More Settings',
				$b->auto('quxColor'),
				$b->editor('longString'),
				$b->auto('someFont'),
				$b->auto('testImage'),
				$b->control(AlignmentSelector::class, $s->findSetting('alignment'))
			)->id('ame-kcd-more-settings'),
			$b->autoSection('exampleSpacing')->id('ame-kcd-spacing'),
			$b->autoSection('exampleBoxShadow')->id('ame-kcd-box-shadow'),
			$b->section(
				'Border styles',
				$b->control(BorderStyleSelector::class, 'exampleBorderStyle')
			)->id('ame-kcd-border-styles')
		);

		$tweakStructure = $this->getTweakInterfaceStructure();
		foreach ($tweakStructure->getAsSections() as $section) {
			$structure->addBefore($section, 'ame-kcd-tweak-refactoring');
		}

		return $structure->build();
	}

	private function getTweakModule(): ameTweakManager {
		$modules = $this->menuEditor->get_loaded_modules();
		if ( !isset($modules['tweaks']) ) {
			throw new \LogicException(
				'Tweak module is not loaded. The KO Customizable Dev module requires the Tweak module to be active to display tweak UI prototypes.'
			);
		}

		if ( !($modules['tweaks'] instanceof ameTweakManager) ) {
			throw new \LogicException(
				'Tweak module is not an instance of ameTweakManager. This should never happen.'
			);
		}

		return $modules['tweaks'];
	}

	private function getTweakInterfaceStructure(): InterfaceStructure {
		$tweakModule = $this->getTweakModule();
		$tweakData = ['sections' => [], 'aliases' => []];
		$tweaks = [];

		//Sort sections by priority (ascending numbers).
		usort($tweakData['sections'], function ($a, $b) {
			$priorityA = $a['priority'] ?? 10;
			$priorityB = $b['priority'] ?? 10;
			return $priorityA <=> $priorityB;
		});

		$settings = $tweakModule->loadSettings();
		$b = $settings->elementBuilder();
		$s = $settings->settingFactory();
		$schemas = new SchemaFactory();

		/*echo '<pre>';
		var_dump($settings->toArray());
		exit;*/

		//Sections
		$sectionBuilders = [];
		foreach ($tweakData['sections'] as $sectionData) {
			$plainId = $sectionData['id'];

			$section = $b->section($sectionData['label'])
				->id($plainId)
				->params(['childrenContainerClasses' => ['ame-check-or-radio-collection']]);

			if ( !empty($sectionData['description']) ) {
				$section->description($sectionData['description']);
			}

			$sectionBuilders[$plainId] = $section;
		}

		$tweakControlsById = [];
		$tweakSectionsByTweakId = [];

		//Tweaks
		foreach ($tweaks as $tweak) {
			$plainTweakId = $tweak->getId();

			$tweakControl = $tweak->createUiElement($b, $settings, 'tweaks.' . $plainTweakId);
			$tweakControlsById[$plainTweakId] = $tweakControl;

			if ( !empty($tweak->getParentId()) ) {
				$parentTweakId = $tweak->getParentId();
				if ( isset($tweakControlsById[$parentTweakId]) ) {
					$parentControl = $tweakControlsById[$parentTweakId];
					$parentControl->add($tweakControl);
					continue;
				} else {
					//Parent not found; put it in a section instead (fallthrough).
				}
			}

			$plainSectionId = $tweak->getSectionId() ?? 'general';
			if ( !isset($sectionBuilders[$plainSectionId]) ) {
				continue;
			}

			$sectionBuilder = $sectionBuilders[$plainSectionId];
			$sectionBuilder->add($tweakControl);
			$tweakSectionsByTweakId[$plainTweakId] = $sectionBuilder;
		}

		//Aliases
		foreach ($tweakData['aliases'] as $aliasInfo) {
			$targetTweakId = $aliasInfo['tweakId'];
			if ( !isset($tweakControlsById[$targetTweakId]) ) {
				continue;
			}

			//An alias is just another control that points to the same setting.
			$targetControl = $tweakControlsById[$targetTweakId];
			if ( !($targetControl instanceof ControlBuilder) ) {
				throw new \LogicException(sprintf(
					'Invalid alias: "%s" is not a ControlBuilder instance.',
					$targetTweakId
				));
			}

			$targetSettings = $targetControl->getSettings();
			$firstSetting = reset($targetSettings);
			if ( $firstSetting === false ) {
				throw new \LogicException(sprintf(
					'Invalid alias: target control for "%s" has no settings.',
					$targetTweakId
				));
			}
			$aliasControl = $b->actorFeatureCheckbox($firstSetting)
				->label($aliasInfo['label'] ?? $targetTweakId)
				->id('alias-' . $targetTweakId);

			$tooltip = 'This is an alias for: "' . $targetControl->getParam('label', $targetTweakId) . '"';
			if ( isset($tweakSectionsByTweakId[$targetTweakId]) ) {
				$section = $tweakSectionsByTweakId[$targetTweakId];
				$tooltip .= ' in the section "' . $section->getTitle() . '".';
			}
			$aliasControl->tooltip($tooltip);

			if ( !empty($aliasInfo['parentId']) ) {
				$parentTweakId = $aliasInfo['parentId'];
				if ( isset($tweakControlsById[$parentTweakId]) ) {
					$parentControl = $tweakControlsById[$parentTweakId];
					$parentControl->add($aliasControl);
					continue;
				}
			}

			$plainSectionId = $aliasInfo['sectionId'] ?? 'general';
			if ( !isset($sectionBuilders[$plainSectionId]) ) {
				continue;
			}
			$sectionBuilder = $sectionBuilders[$plainSectionId];
			$sectionBuilder->add($aliasControl);
		}

		if ( isset($sectionBuilders['admin-css']) ) {
			$adminCssSection = $sectionBuilders['admin-css'];
			$adminCssSection->add(
				new EventButton(
					[],
					[
						'label'     => 'Add CSS snippet',
						'eventName' => 'adminMenuEditor:addCssSnippet',
						'wrap'      => true,
					]
				)
			);
		}

		$structure = $b->structure();
		foreach ($sectionBuilders as $b) {
			$structure->add($b);
		}

		/*echo '<pre>';
		var_dump($tweakData);
		exit;*/

		return $structure->build();
	}
}

class CustomizableTestSettings extends AbstractSettingsDictionary {
	public function __construct() {
		parent::__construct(
			new LazyArrayStorage(),
			'ame_customizable_test_settings--'
		);

		$this->set(
			'testImage',
			['externalUrl' => 'https://picsum.photos/seed/picsum/300/150',]
		);

		//Add some sample snippets to the settings for testing rendering.
		$this->set('tweakRefactor.snippets', [
			[
				'label'           => 'Make body text red',
				'enabledForActor' => [
					'role:administrator' => true,
					'role:subscriber'    => false,
				],
				'code'            => 'body { color: red; }',
			],
			[
				'label'           => 'Increase font size',
				'enabledForActor' => [
					'role:administrator' => true,
					'role:subscriber'    => true,
				],
				'code'            => 'body { font-size: 18px; }',
			],
		]);

		$this->set('nestedCollections', [
			[
				'name'       => 'First Item',
				'value'      => 100,
				'innerItems' => [
					[
						'description' => 'Inner item 1',
						'amount'      => 10,
					],
					[
						'description' => 'Inner item 2',
						'amount'      => 20,
					],
				],
				'tags'       => ['alpha', 'beta'],
			],
			[
				'name'       => 'Second Item',
				'value'      => 200,
				'innerItems' => [
					[
						'description' => 'Inner item A',
						'amount'      => 30,
					],
				],
				'tags'       => ['gamma', 'delta', 'epsilon'],
			],
		]);
	}

	protected function createDefaults() {
		return [];
	}

	protected function createSettings() {
		$f = $this->settingFactory();
		$settings = [
			//Create some sample settings.
			$f->integer('fooInt', 'Foo Integer', ['default' => 123]),
			$f->string(
				'barString',
				'Bar String',
				[
					'default'     => 'Hello, world!',
					'description' => 'This is a sample string setting.',
				]),
			$f->boolean('bazBool', 'Baz Boolean', ['description' => 'This is a sample boolean setting.']),
			$f->cssColor('quxColor', 'color', 'Qux Color', ['default' => '#ff0000']),
			$f->cssFont('someFont', 'Font'),
			$f->string('longString', 'Long String', ['default' => str_repeat('Lorem ipsum ', 50)]),
			$f->image('testImage', 'An Image'),
			$f->enum(
				'exampleEnum',
				['one', 2, '3.05'],
				'Enum (mixed types)'
			)
				->describeChoice('one', 'Option 1')
				->describeChoice(2, 'Option 2')
				->describeChoice('3.05', 'Option 3'),
			$f->cssColor('nestedOne', 'Nested One', null, ['default' => '#00ff00']),
			$f->integer('nestedThree', 'Nested Three', ['default' => 42, 'min' => 10, 'max' => 99]),

			$f->cssSpacing('exampleSpacing', 'Spacing'),
			$f->stringEnum(
				'alignment',
				['none', 'left', 'center', 'right'],
				'Alignment',
				['default' => 'none']
			),
			$f->cssBoxShadow('exampleBoxShadow', 'Box Shadow'),
			$f->cssEnum(
				'exampleBorderStyle',
				'border-style',
				['solid', 'dashed', 'double', 'dotted',],
				'Border style',
				['default' => 'solid']
			),
		];

		//Also make some schema-based settings for testing.
		$s = new SchemaFactory();
		$schemaSettings = $f->buildSettings([
			'schemaString'    => $s->string('Schema String')->min(5)->max(20)->trim()->regex('/^[a-z0-9]+$/i'),
			'schemaBool'      => $s->boolean('This is a sample setting with a boolean schema')
				->settingParams(['groupTitle' => 'Schema Bool']),
			'schemaInt'       => $s->int('Schema Int')->defaultValue(42)->min(10)->max(99),
			'schemaEnum'      => $s->enum(['one', 'two', 'three'], 'Schema Enum'),
			'schemaEnumMixed' => $s->enum(['one', 2, '3.05'], 'Schema Enum (mixed types)')
				->describeValue('one', 'Option 1')
				->describeValue(2, 'Option 2')
				->describeValue('3.05', 'Option 3'),
			'schemaColor'     => $s->cssColor('Schema Color')->defaultValue('#00ff00'),

			'schemaFont' => $s->cssFont('Schema Font'),

			'tweakRefactor' => $s->struct([
				'snippets'             => $s->arr(
					$s->struct([
						'label'           => $s->string('Label')->min(3)->max(200),
						'enabledForActor' => $s->record(
							$s->string()->min(1)->max(250),
							$s->boolean()
						),
						'code'            => $s->string('CSS Snippet')->min(10),
					]),
					'CSS Snippets'
				),
				'tweaks'               => $s->record(
					$s->string('Tweak ID')->min(3)->max(100),
					$s->struct([
						'enabledForActor' => $s->record(
							$s->string()->min(1)->max(250),
							$s->boolean()
						),
					]),
					'Generic Tweaks'
				),
				'actorFeatureExampleA' => $s->actorFeatureMap('Feature A enabled'),
				'actorFeatureExampleB' => $s->actorFeatureMap('Feature B enabled'),
				'actorFeatureExampleC' => $s->actorAccess('Feature C (with alias)'),

				'parentFeature' => $s->actorAccess('Parent feature'),
				'childFeatureA' => $s->actorAccess('Child feature A'),
				'childFeatureB' => $s->actorAccess('Child feature B'),
			]),

			'nestedCollections' => $s->arr(
				$s->struct([
					'name'       => $s->string('Name')->min(1)->max(100),
					'value'      => $s->int('Value')->min(0)->max(1000),
					'innerItems' => $s->arr(
						$s->struct([
							'description' => $s->string('Description')->min(1)->max(200),
							'amount'      => $s->int('Amount')->min(0)->max(500),
						])
					),
					'tags'       => $s->arr($s->string('Tag')->min(1)->max(50)),
				], 'Item With Inner Collection'),
				'Outer Collection'
			),
		]);

		return array_merge($settings, $schemaSettings);
	}
}