import {AmeCustomizable} from '../../pro-customizables/assets/customizable.js';
import {registerBaseComponents} from '../../pro-customizables/ko-components/ame-components.js';
import ameSiStructure from '../../pro-customizables/ko-components/ame-si-structure/ame-si-structure.js';
import ameSiSection from '../../pro-customizables/ko-components/ame-si-section/ame-si-section.js';
import ameSiControlGroup from '../../pro-customizables/ko-components/ame-si-control-group/ame-si-control-group.js';

declare const wsAmeKoPrototypeData: AmeKoPrototyping.ScriptData;
// noinspection TypeScriptUMDGlobal
declare const wsAmeLodash: _.LoDashStatic;

namespace AmeKoPrototyping {
	import lift = AmeMiniFunc.lift;
	const _ = wsAmeLodash;

	import SettingDefinitionMap = AmeCustomizable.SettingDefinitionMap;
	import InterfaceStructureData = AmeCustomizable.InterfaceStructureData;
	import SettingCollection = AmeCustomizable.SettingCollection;
	import unserializeSettings = AmeCustomizable.unserializeSettingMap;
	import InterfaceStructure = AmeCustomizable.InterfaceStructure;
	import unserializeUiElement = AmeCustomizable.unserializeUiElement;

	import AnySpecificElementData = AmeCustomizable.AnySpecificElementData;
	import ServiceRegistry = AmeCustomizable.ServiceRegistry;
	import unserializeSetting = AmeCustomizable.unserializeSetting;

	export interface ScriptData {
		settings: SettingDefinitionMap;
		interfaceStructure: InterfaceStructureData;
		preferenceCookiePath: string;
	}

	registerBaseComponents();
	ko.components.register('ame-si-structure', ameSiStructure);
	ko.components.register('ame-si-section', ameSiSection);
	ko.components.register('ame-si-control-group', ameSiControlGroup);

	class SampleViewModel {
		public settings: SettingCollection;
		public interfaceStructure: InterfaceStructure;

		readonly actorSelector: AmeActorSelector;
		private readonly adminCssEditorDialog: AmeEditAdminCssDialogV2;

		public readonly cssHighlightingOptions: Record<string, any>;
		private lastUserTweakSuffix: number = 0;

		constructor(data: ScriptData) {
			this.actorSelector = new AmeActorSelector(AmeActors, true);

			const collapsibleSectionStateStore = new AmeCollapsibleStateStore(
				'ame-ko-prototype-section-states',
				data.preferenceCookiePath
			);

			const registry = ServiceRegistry.init()
				.register('actorSelector', this.actorSelector)
				.register('collapsibleStateStore', collapsibleSectionStateStore);

			const featureStrategy = new AmeActorFeatureStrategy({
				roleCombinationMode: AmeRoleCombinationMode.Some,
				getSelectedActor: this.actorSelector.getActorObservable(ko),
				getAllActors: () => this.actorSelector.getVisibleActors()
			});

			// noinspection JSMismatchedCollectionQueryUpdate -- Used in commented-out sample code below.
			const sectionIds: string[] = [];

			this.settings = unserializeSettings(data.settings);
			this.interfaceStructure = unserializeUiElement(
				data.interfaceStructure,
				this.settings.get.bind(this.settings),
				registry,
				//Assign the correct components to container elements.
				(data: AnySpecificElementData) => {
					switch (data.t) {
						case 'section':
							data.component = 'ame-postbox-section';//'ame-si-section';
							if (data.id) {
								sectionIds.push(data.id);
							}
							break;
						case 'control-group':
							if (!data.component) {
								data.component = 'ame-general-control-group';
							}
							break;
						case 'control':
							if (data.component === 'ame-actor-feature-checkbox') {
								//Ensure actor feature checkboxes use the shared strategy.
								data.params = data.params || {};
								data.params['strategy'] = featureStrategy;
							}
					}
				}
			);

			console.log('UI Structure:', this.interfaceStructure);

			//By default, open the first section and close the rest.
			if (!collapsibleSectionStateStore.hasAnyStoredStates()) {
				const sectionStates: { [sectionId: string]: boolean } = {};
				sectionIds.forEach((sectionId, index) => {
					sectionStates[sectionId] = index === 0;
				});
				collapsibleSectionStateStore.setAll(sectionStates);
			}

			//Read the last used user tweak suffix from settings.
			this.lastUserTweakSuffix =
				this.settings.get('ws_ame_tweak_settings--lastUserTweakSuffix')
					.map(setting => setting.value() || 0)
					.getOrElse(() => 0);
			console.log('Last user tweak suffix:', this.lastUserTweakSuffix);

			this.adminCssEditorDialog = new AmeEditAdminCssDialogV2(this);

			//This is more complicated in the real tweak manager. Just initializing for demo purposes.
			this.cssHighlightingOptions = {};

			console.log(this.settings.getAllSettingValues());
		}

		onAddCssSnippet() {
			console.log('Add CSS Snippet button clicked');
			this.adminCssEditorDialog.open();
		}

		onEditTweak(_unused: unknown, event: BaseJQueryEventObject) {
			if (!(event.originalEvent instanceof CustomEvent)) {
				return;
			}
			const objectId = event.originalEvent.detail.objectId;
			console.info('Edit tweak requested: ' + objectId);

			const ids = this.getIdsForTweak(objectId);
			const adminCssSection = this.getAdminCssSection();
			const mainControl = adminCssSection.findChildById(ids.controls.actorFeature);
			if (!(mainControl instanceof AmeCustomizable.Control)) {
				throw new Error('Tweak control not found: ' + ids.controls.actorFeature);
			}

			lift(
				[this.settings.get(ids.settings.label), this.settings.get(ids.settings.css)],
				(labelSetting, cssSetting) => {
					this.adminCssEditorDialog.selectedTweak = {
						label: labelSetting.value,
						cssCode: cssSetting.value,
						checkboxLabel: mainControl.label
					};
					this.adminCssEditorDialog.open();
				}
			);
		}

		onDeleteTweak(_unused: unknown, event: BaseJQueryEventObject) {
			if (!(event.originalEvent instanceof CustomEvent)) {
				return;
			}
			const objectId = event.originalEvent.detail.objectId;
			console.info('Delete tweak requested: ' + objectId);

			const adminCssSection = this.getAdminCssSection();

			const ids = this.getIdsForTweak(objectId);
			const mainControl = adminCssSection.findChildById(ids.controls.actorFeature);
			if (mainControl === null) {
				throw new Error('Tweak control not found: ' + ids.controls.actorFeature);
			}

			const removedChildren = adminCssSection.children.remove(mainControl);
			if (removedChildren.length === 0) {
				throw new Error('Failed to remove tweak control from UI structure: ' + ids.controls.actorFeature);
			}

			//Also remove the associated settings.
			for (const settingId of Object.values(ids.settings)) {
				console.log('Removing setting:', settingId);
				this.settings.remove(settingId);
			}
		}

		private getIdsForTweak(tweakId: string) {
			const baseSettingId = 'ws_ame_tweak_settings--tweaks.' + tweakId;
			return {
				settings: {
					actorFeature: baseSettingId + '.enabledForActor',
					label: baseSettingId + '.label',
					css: baseSettingId + '.css',
					userDefinedFlag: baseSettingId + '.isUserDefined',
					typeId: baseSettingId + '.typeId',
				},
				controls: {
					actorFeature: 'ws_ame_tweak_settings--tweaks_' + tweakId + '_enabledForActor',
					css: 'ws_ame_tweak_settings--tweaks_' + tweakId + '_css',
				}
			};
		}

		addAdminCssTweak(label: string, cssCode: string) {
			console.log('Adding new admin CSS tweak:', label, cssCode);

			const adminCssSection = this.getAdminCssSection();
			const defaultSnippetControl = adminCssSection.findChildById(
				'ws_ame_tweak_settings--tweaks_default-admin-css_enabledForActor'
			);
			if (!(defaultSnippetControl instanceof AmeCustomizable.Control)) {
				throw new Error('Default admin CSS snippet control not found');
			}
			const defaultSnippetEditor = defaultSnippetControl.findChildById(
				'ws_ame_tweak_settings--tweaks_default-admin-css_css'
			);
			if (!(defaultSnippetEditor instanceof AmeCustomizable.Control)) {
				throw new Error('Default admin CSS snippet editor not found');
			}

			this.lastUserTweakSuffix++;

			let slug = _.kebabCase(_.deburr(label)); //AmeTweakManagerModule.slugify(label);
			if (slug !== '') {
				slug = '-' + slug;
			}

			const newTweakId = 'utw-' + this.lastUserTweakSuffix + slug;
			const ids = this.getIdsForTweak(newTweakId);

			//Add a new CSS snippet control.
			//First, it needs new settings for the CSS code, actor access map, and metadata.
			const settingData: Record<string, AmeCustomizable.SettingDefinition> = {
				[ids.settings.userDefinedFlag]: {value: true},
				[ids.settings.typeId]: {value: 'admin-css'},
				[ids.settings.label]: {value: label},
				[ids.settings.css]: {value: cssCode},
				[ids.settings.actorFeature]: {value: {}, defaultValue: {}},
			};
			for (const [settingId, definition] of Object.entries(settingData)) {
				this.settings.add(unserializeSetting(settingId, definition));
			}

			//Create the control: the actor feature checkbox with a nested code editor.
			const newControl = unserializeUiElement(
				{
					t: 'control',
					id: ids.controls.actorFeature,
					label: label,
					component: 'ame-actor-feature-checkbox',
					settings: {value: ids.settings.actorFeature},
					params: {
						hasTweakActions: true,
						objectId: newTweakId,
					},
					children: [
						{
							t: 'control',
							id: ids.controls.css,
							label: 'CSS Code',
							component: 'ame-code-editor',
							settings: {value: ids.settings.css},
							//Copy other params from the default editor.
							params: Object.assign({}, defaultSnippetEditor.componentParams)
						}
					]
				},
				this.settings.get.bind(this.settings),
				this.interfaceStructure.getServiceRegistry()
			);

			if (!(newControl instanceof AmeCustomizable.Control)) {
				throw new Error('Failed to create new CSS snippet control');
			}

			//Usually, the last child is the "Add CSS Snippet" button, so let's insert before it.
			if (adminCssSection.children().length > 1) {
				adminCssSection.children.splice(
					adminCssSection.children().length - 1,
					0,
					newControl
				);
				return;
			} else {
				//Fallback: Just append to the end.
				adminCssSection.children.push(newControl);
			}
		}

		private getAdminCssSection(): AmeCustomizable.Section {
			const adminCssSection = this.interfaceStructure.findChildById('admin-css');
			if (!(adminCssSection instanceof AmeCustomizable.Section)) {
				//This should never happen; the section exists by default.
				throw new Error('Admin CSS section not found');
			}
			return adminCssSection;
		}

		serializeTweakSettings(): Record<string, unknown> {
			const tweakSettingsPrefix = 'ws_ame_tweak_settings--tweaks.';

			const relevantSettings = _.pickBy(
				this.settings.getAllSettingValues(),
				(value, key) => {
					if (!key.startsWith(tweakSettingsPrefix)) {
						return false;
					}
					if (key.endsWith('.enabledForActor')) {
						return !_.isEmpty(value);
					}
					return true;
				}
			);

			//Drop the key prefix, sort by the remaining key, and build nested object structure.
			const tweaks = {};
			_(relevantSettings)
				.mapKeys((_value, key) => key.substring(tweakSettingsPrefix.length))
				.toPairs()
				.sortBy(([key, _value]) => key)
				.forEach((value) => {
					_.set(tweaks, value[0], value[1]);
				});

			return {
				tweaks: tweaks,
				lastUserTweakSuffix: this.lastUserTweakSuffix
			};
		}
	}

	class AmeEditAdminCssDialogV2 extends AmeBaseKnockoutDialog {
		jQueryWidget: JQuery | null = null;
		isOpen: KnockoutObservable<boolean>;
		autoCancelButton: boolean = true;

		isConfirmButtonEnabled: KnockoutComputed<boolean>;
		tweakLabel: KnockoutObservable<string>;
		cssCode: KnockoutObservable<string>;
		confirmButtonLabel: KnockoutObservable<string | null>;

		selectedTweak: {
			label: KnockoutObservable<string>,
			cssCode: KnockoutObservable<string>,
			checkboxLabel: KnockoutObservable<string>
		} | null = null;

		constructor(private readonly manager: SampleViewModel) {
			const _ = wsAmeLodash;
			super();
			this.options.minWidth = 400;

			this.tweakLabel = ko.observable('');
			this.cssCode = ko.observable('');
			this.confirmButtonLabel = ko.observable<string | null>('Add Snippet');
			this.title = ko.observable(null);

			this.isConfirmButtonEnabled = ko.computed(() => {
				return !((_.trim(this.tweakLabel()) === '') || (_.trim(this.cssCode()) === ''));
			});
			this.isOpen = ko.observable(false);
		}

		onOpen() {
			if (this.selectedTweak) {
				this.tweakLabel(this.selectedTweak.label());
				this.title('Edit admin CSS snippet');
				this.confirmButtonLabel('Save Changes');

				this.cssCode(this.selectedTweak.cssCode());
			} else {
				this.tweakLabel('');
				this.cssCode('');
				this.title('Add admin CSS snippet');
				this.confirmButtonLabel('Add Snippet');
			}
		}

		onConfirm() {
			if (this.selectedTweak) {
				//Update the existing tweak.
				console.log('Updating existing tweak');
				this.selectedTweak.label(this.tweakLabel());
				this.selectedTweak.cssCode(this.cssCode());
				this.selectedTweak.checkboxLabel(this.tweakLabel());
			} else {
				//Create a new tweak.
				console.log('Creating new tweak');
				this.manager.addAdminCssTweak(
					this.tweakLabel(),
					this.cssCode()
				);
			}
			this.close();
		}

		onClose() {
			this.selectedTweak = null;
		}

		close() {
			this.isOpen(false);
		}

		open() {
			this.isOpen(true);
		}
	}

	const vm = new SampleViewModel(wsAmeKoPrototypeData);
	(window as any)['wsAmeKoPrototypeVm'] = vm;

	const rootContainer = document.getElementById('ws-ame-ko-prototype-container');
	if (rootContainer === null) {
		throw new Error('Root container element not found');
	}

	ko.applyBindings(vm, rootContainer);
}