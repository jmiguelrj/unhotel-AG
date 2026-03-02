/// <reference path="../../../js/knockout.d.ts" />
/// <reference path="../../../js/jquery.d.ts" />
/// <reference types="@types/lodash" />
/// <reference path="../../../modules/actor-selector/actor-selector.ts" />
/// <reference path="../../../js/jquery.biscuit.d.ts" />
/// <reference path="../../ko-extensions.ts" />
import { registerBaseComponents } from '../../pro-customizables/ko-components/ame-components.js';
import { AmeCustomizable } from '../../pro-customizables/assets/customizable.js';
var ServiceRegistry = AmeCustomizable.ServiceRegistry;
var unserializeSetting = AmeCustomizable.unserializeSetting;
var unserializeUiElement = AmeCustomizable.unserializeUiElement;
var lift = AmeMiniFunc.lift;
var SaveSettingsForm = AmeKoFreeExtensions.SaveSettingsForm;
let ameTweakManager;
class AmeTweakManagerModule {
    constructor(scriptData) {
        this.lastUserTweakSuffix = 0;
        const _ = AmeTweakManagerModule._;
        this.actorSelector = new AmeActorSelector(AmeActors, scriptData.isProVersion);
        //Reselect the previously selected actor.
        this.actorSelector.setSelectedActorFromUrl();
        //Set syntax highlighting options.
        this.cssHighlightingOptions = _.merge({}, scriptData.defaultCodeEditorSettings, {
            'codemirror': {
                'mode': 'css',
                'lint': true,
                'autoCloseBrackets': true,
                'matchBrackets': true
            }
        });
        this.settings = AmeCustomizable.unserializeSettingMap(scriptData.settings);
        const collapsibleSectionStateStore = new AmeCollapsibleStateStore(AmeTweakManagerModule.openSectionCookieName, scriptData.preferenceCookiePath, AmeTweakManagerModule.openSectionCookieName);
        const registry = ServiceRegistry.init()
            .register('actorSelector', this.actorSelector)
            .register('collapsibleStateStore', collapsibleSectionStateStore);
        const featureStrategy = new AmeActorFeatureStrategy({
            roleCombinationMode: AmeRoleCombinationMode.Some,
            getSelectedActor: this.actorSelector.getActorObservable(ko),
            getAllActors: () => this.actorSelector.getVisibleActors()
        });
        // noinspection JSMismatchedCollectionQueryUpdate -- Used in commented-out sample code below.
        const sectionIds = [];
        this.interfaceStructure = AmeCustomizable.unserializeUiElement(scriptData.interfaceStructure, this.settings.get.bind(this.settings), registry, 
        //Assign the correct components to container elements.
        (data) => {
            switch (data.t) {
                case 'section':
                    data.component = 'ame-postbox-section';
                    if (data.id) {
                        sectionIds.push(data.id);
                    }
                    break;
                case 'control':
                    if (data.component === 'ame-actor-feature-checkbox') {
                        //Ensure actor feature checkboxes use the shared strategy.
                        data.params = data.params || {};
                        data.params['strategy'] = featureStrategy;
                    }
            }
        });
        //By default, open the first section and close the rest.
        if (!collapsibleSectionStateStore.hasAnyStoredStates()) {
            const sectionStates = {};
            sectionIds.forEach((sectionId, index) => {
                sectionStates[sectionId] = index === 0;
            });
            collapsibleSectionStateStore.setAll(sectionStates);
        }
        if (scriptData.lastUserTweakSuffix) {
            this.lastUserTweakSuffix = scriptData.lastUserTweakSuffix;
        }
        this.adminCssEditorDialog = new AmeEditAdminCssDialog(this);
        this.saveSettingsForm = new SaveSettingsForm({
            ...scriptData.saveFormConfig,
            settingsGetter: () => {
                return {
                    'tweaks': this.serializeTweakSettings(),
                    'lastUserTweakSuffix': this.lastUserTweakSuffix
                };
            },
            selectedActor: this.actorSelector.getActorObservable(ko),
            formClasses: ['ame-twm-save-form']
        });
    }
    serializeTweakSettings() {
        const tweakSettingsPrefix = 'ws_ame_tweak_settings--tweaks.';
        const _ = AmeTweakManagerModule._;
        const relevantSettings = _.pickBy(this.settings.getAllSettingValues(), (value, key) => {
            if (!key.startsWith(tweakSettingsPrefix)) {
                return false;
            }
            if (key.endsWith('.enabledForActor')) {
                return !_.isEmpty(value);
            }
            return true;
        });
        //Drop the key prefix, sort by the remaining key, and build nested object structure.
        const tweaks = {};
        _(relevantSettings)
            .mapKeys((_value, key) => key.substring(tweakSettingsPrefix.length))
            .toPairs()
            .sortBy(([key, _value]) => key)
            .forEach((value) => {
            _.set(tweaks, value[0], value[1]);
        });
        return tweaks;
    }
    onAddCssSnippet() {
        this.adminCssEditorDialog.open();
    }
    onEditTweak(_unused, event) {
        if (!(event.originalEvent instanceof CustomEvent)) {
            return;
        }
        const objectId = event.originalEvent.detail.objectId;
        const ids = this.getIdsForTweak(objectId);
        const adminCssSection = this.getAdminCssSection();
        const mainControl = adminCssSection.findChildById(ids.controls.actorFeature);
        if (!(mainControl instanceof AmeCustomizable.Control)) {
            throw new Error('Tweak control not found: ' + ids.controls.actorFeature);
        }
        lift([this.settings.get(ids.settings.label), this.settings.get(ids.settings.css)], (labelSetting, cssSetting) => {
            this.adminCssEditorDialog.selectedTweak = {
                label: labelSetting.value,
                cssCode: cssSetting.value,
                checkboxLabel: mainControl.label
            };
            this.adminCssEditorDialog.open();
        });
    }
    onDeleteTweak(_unused, event) {
        if (!(event.originalEvent instanceof CustomEvent)) {
            return;
        }
        if (!confirm('Delete this tweak?')) {
            return;
        }
        const objectId = event.originalEvent.detail.objectId;
        const ids = this.getIdsForTweak(objectId);
        const adminCssSection = this.getAdminCssSection();
        const mainControl = adminCssSection.findChildById(ids.controls.actorFeature);
        if (mainControl === null) {
            throw new Error('Tweak control not found: ' + ids.controls.actorFeature);
        }
        this.whilePreservingScrollPosition(() => {
            const removedChildren = adminCssSection.children.remove(mainControl);
            if (removedChildren.length === 0) {
                throw new Error('Failed to remove tweak control from UI structure: ' + ids.controls.actorFeature);
            }
        });
        //Also remove the associated settings.
        for (const settingId of Object.values(ids.settings)) {
            this.settings.remove(settingId);
        }
    }
    addAdminCssTweak(label, css) {
        const adminCssSection = this.getAdminCssSection();
        const defaultSnippetControl = adminCssSection.findChildById('ame-tweak-default-admin-css');
        if (!(defaultSnippetControl instanceof AmeCustomizable.Control)) {
            throw new Error('Default admin CSS snippet control not found');
        }
        const defaultSnippetEditor = defaultSnippetControl.findChildById('ws_ame_tweak_settings--tweaks_default-admin-css_css');
        if (!(defaultSnippetEditor instanceof AmeCustomizable.Control)) {
            throw new Error('Default admin CSS snippet editor not found');
        }
        this.lastUserTweakSuffix++;
        let slug = AmeTweakManagerModule.slugify(label);
        if (slug !== '') {
            slug = '-' + slug;
        }
        const newTweakId = 'utw-' + this.lastUserTweakSuffix + slug;
        const ids = this.getIdsForTweak(newTweakId);
        //Add a new CSS snippet control.
        //First, it needs new settings for the CSS code, actor access map, and metadata.
        const settingData = {
            [ids.settings.userDefinedFlag]: { value: true },
            [ids.settings.typeId]: { value: 'admin-css' },
            [ids.settings.label]: { value: label },
            [ids.settings.css]: { value: css },
            [ids.settings.actorFeature]: { value: {}, defaultValue: {} },
        };
        for (const [settingId, definition] of Object.entries(settingData)) {
            this.settings.add(unserializeSetting(settingId, definition));
        }
        //Create the control: the actor feature checkbox with a nested code editor.
        const newControl = unserializeUiElement({
            t: 'control',
            id: ids.controls.actorFeature,
            label: label,
            component: 'ame-actor-feature-checkbox',
            settings: { value: ids.settings.actorFeature },
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
                    settings: { value: ids.settings.css },
                    //Copy other params from the default editor.
                    params: Object.assign({}, defaultSnippetEditor.componentParams)
                }
            ]
        }, this.settings.get.bind(this.settings), this.interfaceStructure.getServiceRegistry());
        //Hack: Directly manipulating children() causes the page to jump, most likely because
        //childComponents is a computed observable that gets re-evaluated and makes KO re-render
        //the whole list. (I suspect KO doesn't intelligently diff arrays when the array comes
        //from a computed observable.)
        //To avoid this, we temporarily store the scroll position and restore it afterwards.
        this.whilePreservingScrollPosition(() => {
            //Usually, the last child is the "Add CSS Snippet" button, so let's insert before it.
            if (adminCssSection.children().length > 1) {
                adminCssSection.children.splice(adminCssSection.children().length - 1, 0, newControl);
            }
            else {
                //Fallback: Just append to the end.
                adminCssSection.children.push(newControl);
            }
        });
    }
    whilePreservingScrollPosition(callback) {
        const $window = jQuery(window);
        const oldScrollPosition = $window.scrollTop() || 0;
        callback();
        //Restore scroll position to avoid a jump due to DOM changes.
        window.setTimeout(() => {
            $window.scrollTop(oldScrollPosition);
        }, 1);
    }
    getIdsForTweak(tweakId) {
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
                actorFeature: 'ame-tweak-' + tweakId,
                css: 'ws_ame_tweak_settings--tweaks_' + tweakId + '_css',
            }
        };
    }
    getAdminCssSection() {
        const adminCssSection = this.interfaceStructure.findChildById('twm-section_admin-css');
        if (!(adminCssSection instanceof AmeCustomizable.Section)) {
            //This should never happen; the section exists by default.
            throw new Error('Admin CSS section not found');
        }
        return adminCssSection;
    }
    static slugify(input) {
        const _ = AmeTweakManagerModule._;
        let output = _.deburr(input);
        output = output.replace(/[^a-zA-Z0-9 _\-]/g, '');
        return _.kebabCase(output);
    }
}
AmeTweakManagerModule._ = wsAmeLodash;
AmeTweakManagerModule.openSectionCookieName = 'ame_tmce_open_sections';
class AmeEditAdminCssDialog extends AmeBaseKnockoutDialog {
    constructor(manager) {
        const _ = wsAmeLodash;
        super();
        this.manager = manager;
        this.jQueryWidget = null;
        this.autoCancelButton = true;
        this.selectedTweak = null;
        this.options.minWidth = 400;
        this.tweakLabel = ko.observable('');
        this.cssCode = ko.observable('');
        this.confirmButtonLabel = ko.observable('Add Snippet');
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
        }
        else {
            this.tweakLabel('');
            this.cssCode('');
            this.title('Add admin CSS snippet');
            this.confirmButtonLabel('Add Snippet');
        }
    }
    onConfirm() {
        if (this.selectedTweak) {
            //Update the existing tweak.
            this.selectedTweak.label(this.tweakLabel());
            this.selectedTweak.cssCode(this.cssCode());
            this.selectedTweak.checkboxLabel(this.tweakLabel());
        }
        else {
            //Create a new tweak.
            this.manager.addAdminCssTweak(this.tweakLabel(), this.cssCode());
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
{
    registerBaseComponents();
    let isTwmInitialized = false;
    function wsAmeInitTweakManager() {
        if (isTwmInitialized) {
            return;
        }
        const rootNode = document.getElementById('ame-tweak-manager');
        if (!rootNode) {
            return;
        }
        ameTweakManager = new AmeTweakManagerModule(wsTweakManagerData);
        ko.applyBindings(ameTweakManager, rootNode);
        isTwmInitialized = true;
    }
    //Try to initialize the tweak manager as soon as possible so that tweak sections
    //can be targeted by #hash links.
    wsAmeInitTweakManager();
    jQuery(function () {
        //Alternatively, we can wait until the document is ready.
        wsAmeInitTweakManager();
    });
}
//# sourceMappingURL=tweak-manager.js.map