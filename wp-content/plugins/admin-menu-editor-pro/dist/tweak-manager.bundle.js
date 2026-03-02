"use strict";
(self["wsAmeWebpackChunk"] = self["wsAmeWebpackChunk"] || []).push([["tweak-manager"],{

/***/ "./extras/modules/tweaks/tweak-manager.ts":
/*!************************************************!*\
  !*** ./extras/modules/tweaks/tweak-manager.ts ***!
  \************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _pro_customizables_ko_components_ame_components_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../pro-customizables/ko-components/ame-components.js */ "./extras/pro-customizables/ko-components/ame-components.js");
/* harmony import */ var _pro_customizables_assets_customizable_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../pro-customizables/assets/customizable.js */ "./extras/pro-customizables/assets/customizable.js");
/// <reference path="../../../js/knockout.d.ts" />
/// <reference path="../../../js/jquery.d.ts" />
/// <reference types="@types/lodash" />
/// <reference path="../../../modules/actor-selector/actor-selector.ts" />
/// <reference path="../../../js/jquery.biscuit.d.ts" />
/// <reference path="../../ko-extensions.ts" />


var ServiceRegistry = _pro_customizables_assets_customizable_js__WEBPACK_IMPORTED_MODULE_1__.AmeCustomizable.ServiceRegistry;
var unserializeSetting = _pro_customizables_assets_customizable_js__WEBPACK_IMPORTED_MODULE_1__.AmeCustomizable.unserializeSetting;
var unserializeUiElement = _pro_customizables_assets_customizable_js__WEBPACK_IMPORTED_MODULE_1__.AmeCustomizable.unserializeUiElement;
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
        this.settings = _pro_customizables_assets_customizable_js__WEBPACK_IMPORTED_MODULE_1__.AmeCustomizable.unserializeSettingMap(scriptData.settings);
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
        this.interfaceStructure = _pro_customizables_assets_customizable_js__WEBPACK_IMPORTED_MODULE_1__.AmeCustomizable.unserializeUiElement(scriptData.interfaceStructure, this.settings.get.bind(this.settings), registry, 
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
        if (!(mainControl instanceof _pro_customizables_assets_customizable_js__WEBPACK_IMPORTED_MODULE_1__.AmeCustomizable.Control)) {
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
        if (!(defaultSnippetControl instanceof _pro_customizables_assets_customizable_js__WEBPACK_IMPORTED_MODULE_1__.AmeCustomizable.Control)) {
            throw new Error('Default admin CSS snippet control not found');
        }
        const defaultSnippetEditor = defaultSnippetControl.findChildById('ws_ame_tweak_settings--tweaks_default-admin-css_css');
        if (!(defaultSnippetEditor instanceof _pro_customizables_assets_customizable_js__WEBPACK_IMPORTED_MODULE_1__.AmeCustomizable.Control)) {
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
        if (!(adminCssSection instanceof _pro_customizables_assets_customizable_js__WEBPACK_IMPORTED_MODULE_1__.AmeCustomizable.Section)) {
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
    (0,_pro_customizables_ko_components_ame_components_js__WEBPACK_IMPORTED_MODULE_0__.registerBaseComponents)();
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


/***/ }),

/***/ "./extras/pro-customizables/ko-components/ame-actor-feature-checkbox/ame-actor-feature-checkbox.js":
/*!*********************************************************************************************************!*\
  !*** ./extras/pro-customizables/ko-components/ame-actor-feature-checkbox/ame-actor-feature-checkbox.js ***!
  \*********************************************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _control_base_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../control-base.js */ "./extras/pro-customizables/ko-components/control-base.js");
/* harmony import */ var _assets_customizable_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../assets/customizable.js */ "./extras/pro-customizables/assets/customizable.js");


var ServiceRegistry = _assets_customizable_js__WEBPACK_IMPORTED_MODULE_1__.AmeCustomizable.ServiceRegistry;
//Note: Requires Lodash, but does not explicitly import it because this plugin
//already uses Lodash as a global variable (wsAmeLodash) in many places.
class AmeActorFeatureCheckbox extends _control_base_js__WEBPACK_IMPORTED_MODULE_0__.KoStandaloneControl {
    constructor(params, $element) {
        super(params, $element);
        this.htmlId = '';
        this.hasTweakActions = false;
        this.objectId = null;
        this.childCheckedObservables = ko.observableArray([]);
        if (this.id) {
            this.htmlId = this.id;
        }
        if (typeof this.settings['value'] === 'undefined') {
            throw new Error('AmeActorFeatureCheckbox requires a "value" setting to be defined.');
        }
        this.registerChildObservable = (childObservable) => {
            this.childCheckedObservables.push(childObservable);
        };
        const valueObservable = this.settings.value.value;
        let isUpdating = false;
        const observableMap = new AmeObservableActorFeatureMap(valueObservable());
        //Apply changes from the observable map back to the setting.
        ko.computed(() => observableMap.getAll())
            .extend({ deferred: true })
            .subscribe((newValue) => {
            if (isUpdating) {
                return;
            }
            //Avoid updating the setting if the value hasn't actually changed.
            //This isn't strictly necessary to avoid infinite loops, but it helps prevent some
            //unnecessary updates that isUpdating alone doesn't prevent (likely because of
            //{deferred: true} above).
            const currentExternalValue = valueObservable();
            if (wsAmeLodash.isEqual(currentExternalValue, newValue)) {
                return;
            }
            isUpdating = true;
            valueObservable(newValue);
            isUpdating = false;
        });
        //Apply changes from the setting to the observable map.
        valueObservable.subscribe((externalValue) => {
            if (isUpdating) {
                return;
            }
            isUpdating = true;
            if (externalValue === null) {
                observableMap.resetAll();
            }
            else {
                observableMap.setAll(externalValue);
            }
            isUpdating = false;
        });
        this.featureState = new AmeActorFeatureState(observableMap, this.acquireFeatureStrategy(params));
        this.isChecked = ko.computed({
            read: this.featureState.isEnabled,
            write: (newValue) => {
                this.featureState.isEnabled(newValue);
                //When the user checks or unchecks this checkbox, update all child checkboxes.
                //Note that this only propagates changes from parent to children, not the other way around.
                //The setting represented by this checkbox can be independent of its children, like
                //a parent tweak that hides an entire section + child tweaks that hide individual fields.
                this.childCheckedObservables().forEach((childObservable) => {
                    childObservable(newValue);
                });
            }
        });
        this.isIndeterminate = this.featureState.isIndeterminate;
        //Register our observable with the parent checkbox, if there is one.
        if (typeof params['cbRegisterCheckedObservable'] === 'function') {
            params['cbRegisterCheckedObservable'](this.isChecked);
        }
        if (typeof params['hasTweakActions'] === 'boolean') {
            this.hasTweakActions = params['hasTweakActions'];
        }
        if (typeof params['objectId'] === 'string') {
            this.objectId = params['objectId'];
        }
    }
    acquireFeatureStrategy(params) {
        //The strategy can either be passed directly or constructed using an actor selector from
        //the service registry and optional strategy settings.
        if (typeof params['strategy'] !== 'undefined') {
            const strategy = params['strategy'];
            if (!(strategy instanceof AmeActorFeatureStrategy)) {
                throw new Error('AmeActorFeatureCheckbox parameter "strategy" is not a valid AmeActorFeatureStrategy instance.');
            }
            return strategy;
        }
        if (typeof params['registry'] === 'undefined') {
            throw new Error('AmeActorFeatureCheckbox requires either the "strategy" or the "registry" parameter.');
        }
        const registry = params['registry'];
        if (!(registry instanceof ServiceRegistry)) {
            throw new Error('AmeActorFeatureCheckbox parameter "registry" is not a valid ServiceRegistry instance.');
        }
        const actorSelector = registry.get('actorSelector');
        if (!(actorSelector instanceof AmeActorSelector)) {
            throw new Error('AmeActorFeatureCheckbox requires a valid AmeActorSelector registered as "actorSelector" in the ServiceRegistry.');
        }
        return new AmeActorFeatureStrategy({
            ...ameUnserializeFeatureStrategySettings(params.strategySettings ?? {}),
            getSelectedActor: actorSelector.getActorObservable(ko),
            getAllActors: () => actorSelector.getVisibleActors()
        });
    }
    mapChildToComponentBinding(child) {
        if (child.component === 'ame-actor-feature-checkbox') {
            //Pass the registration function to child checkboxes so they can register their observables.
            return _control_base_js__WEBPACK_IMPORTED_MODULE_0__.ComponentBindingOptions.fromElement(child, null, {
                cbRegisterCheckedObservable: this.registerChildObservable,
            });
        }
        return super.mapChildToComponentBinding(child);
    }
    get inputClasses() {
        return ['ame-actor-feature-checkbox', ...super.inputClasses];
    }
    get classes() {
        return ['ame-actor-feature-checkbox-control', ...super.classes];
    }
    triggerEditEvent() {
        const target = this.findChild('div');
        target[0].dispatchEvent(new CustomEvent('adminMenuEditor:editObject', {
            detail: { objectId: this.objectId },
            bubbles: true,
        }));
        return false;
    }
    triggerDeleteEvent() {
        const target = this.findChild('div');
        target[0].dispatchEvent(new CustomEvent('adminMenuEditor:deleteObject', {
            detail: { objectId: this.objectId },
            bubbles: true,
        }));
        return false;
    }
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ((0,_control_base_js__WEBPACK_IMPORTED_MODULE_0__.createControlComponentConfig)(AmeActorFeatureCheckbox, `
	<div data-bind="class: classString, attr: { id: htmlId }">
		<label>
			<input type="checkbox" data-bind="checked: isChecked, indeterminate: isIndeterminate, attr: inputAttributes, 
				class: inputClassString, enable: isEnabled">
			<span data-bind="text: label"></span>
			<!-- ko if: tooltip -->
				<!-- ko component: {name: 'ame-tooltip-trigger', params: {tooltip: tooltip}} --><!-- /ko -->
			<!-- /ko -->
		</label>
		<!-- ko if: hasTweakActions -->
		<span class="ame-afc-tweak-actions">
			<a href="#" class="ame-afc-action ame-afc-edit" title="Edit"
			   data-bind="click: triggerEditEvent"
			><span class="dashicons dashicons-edit"></span></a><a href="#"
			    class="ame-afc-action ame-afc-delete"
			    title="Delete"
			    data-bind="click: triggerDeleteEvent"
			><span class="dashicons dashicons-trash"></span></a>
		</span>
		<!-- /ko -->
		<!-- ko if: (description) -->
			<!-- ko component: {name: 'ame-nested-description', params: {description: description, includeLineBreak: false}} --><!-- /ko -->
		<!-- /ko -->
		<!-- ko if: childComponents().length > 0 -->
			<div class="ame-general-control-children">
			<!-- ko foreach: childComponents -->
				<div class="ame-control-child">
				<!-- ko component: $data --><!-- /ko -->
				</div>
			<!-- /ko -->
			</div>
		<!-- /ko -->
	</div>
`));
//# sourceMappingURL=ame-actor-feature-checkbox.js.map

/***/ }),

/***/ "./extras/pro-customizables/ko-components/ame-box-dimensions/ame-box-dimensions.js":
/*!*****************************************************************************************!*\
  !*** ./extras/pro-customizables/ko-components/ame-box-dimensions/ame-box-dimensions.js ***!
  \*****************************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _control_base_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../control-base.js */ "./extras/pro-customizables/ko-components/control-base.js");
/* harmony import */ var _lazy_popup_slider_adapter_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../lazy-popup-slider-adapter.js */ "./extras/pro-customizables/ko-components/lazy-popup-slider-adapter.js");



const allDimensionKeys = [
    'top', 'bottom', 'left', 'right',
    'topLeft', 'topRight', 'bottomLeft', 'bottomRight'
];
function isDimensionKey(key) {
    return allDimensionKeys.includes(key);
}
const DefaultDimensionsInOrder = [
    ['top', 'Top'],
    ['bottom', 'Bottom'],
    ['left', 'Left'],
    ['right', 'Right'],
];
const SideDimensions = ['top', 'bottom', 'left', 'right'];
const SymmetricDimensionMap = {
    'vertical': ['top', 'bottom'],
    'horizontal': ['left', 'right'],
};
function isSymmetricDimensionKey(key) {
    return SymmetricDimensionMap.hasOwnProperty(key);
}
let nextId = 0;
class AmeBoxDimensions extends _control_base_js__WEBPACK_IMPORTED_MODULE_0__.KoStandaloneControl {
    constructor(params, $element) {
        super(params, $element);
        this.inputIdPrefix = '_ame-box-dimensions-c-input-' + (nextId++);
        this.unitElementId = '_ame-box-dimensions-c-unit-' + (nextId++);
        this.wrapperAttributes = {};
        if ((typeof params.id === 'string') && (params.id !== '')) {
            this.wrapperAttributes['id'] = '_ame-box-dimensions-w-' + params.id;
        }
        if ((typeof params['dimensionNames'] !== 'undefined') && Array.isArray(params['dimensionNames'])) {
            this.dimensionsInOrder = params['dimensionNames'];
        }
        else {
            this.dimensionsInOrder = DefaultDimensionsInOrder;
        }
        //Make observable proxies for the individual dimension settings.
        const temp = {};
        for (const [dimensionKey, dimensionName] of this.dimensionsInOrder) {
            const setting = this.settings['value.' + dimensionKey];
            if (!setting || (typeof setting !== 'object')) {
                throw new Error(`Missing setting for the "${dimensionName}" side.`);
            }
            temp[dimensionKey] = ko.computed({
                read: () => {
                    return setting.value();
                },
                write: (newValue) => {
                    if (newValue === '') {
                        newValue = null;
                    }
                    setting.value(newValue);
                },
                deferEvaluation: true,
            }).extend({ 'ameNumericInput': true });
        }
        this.dimensions = temp;
        //Similarly, make an observable for the unit setting.
        const unitSetting = this.settings['value.unit'];
        if (!unitSetting || (typeof unitSetting !== 'object')) {
            throw new Error('Missing setting for the unit.');
        }
        this.unitSetting = unitSetting;
        const defaultDropdownOptions = {
            options: [],
            optionsText: 'text',
            optionsValue: 'value'
        };
        if (params.unitDropdownOptions && (typeof params.unitDropdownOptions === 'object')) {
            const unitDropdownOptions = params.unitDropdownOptions;
            this.unitDropdownOptions = {
                options: unitDropdownOptions['options'] || defaultDropdownOptions.options,
                optionsText: unitDropdownOptions['optionsText'] || defaultDropdownOptions.optionsText,
                optionsValue: unitDropdownOptions['optionsValue'] || defaultDropdownOptions.optionsValue,
            };
        }
        else {
            this.unitDropdownOptions = defaultDropdownOptions;
        }
        this.isLinkActive = ko.observable(false);
        //Enable the link button by default if all dimensions are equal. Exception: null values.
        //Dimensions can have different defaults, so null doesn't necessarily mean that they
        //are actually equal.
        const firstKey = Object.keys(this.dimensions)[0];
        const firstValue = this.dimensions[firstKey]();
        if ((firstValue !== null) && (firstValue !== '')) {
            let areAllDimensionsEqual = true;
            for (const [dimensionKey] of this.dimensionsInOrder) {
                if (this.dimensions[dimensionKey]() !== firstValue) {
                    areAllDimensionsEqual = false;
                    break;
                }
            }
            this.isLinkActive(areAllDimensionsEqual);
        }
        //When "link" mode is enabled, keep all dimensions in sync.
        let isUpdatingAllDimensions = false; //Prevent infinite loops.
        const updateAllDimensions = (newValue) => {
            if (!isUpdatingAllDimensions && this.isLinkActive()) {
                isUpdatingAllDimensions = true;
                newValue = this.normalizeValue(newValue);
                for (const observable of Object.values(this.dimensions)) {
                    observable(newValue);
                }
                isUpdatingAllDimensions = false;
            }
        };
        for (const dimensionKey of Object.keys(this.dimensions)) {
            this.dimensions[dimensionKey].subscribe(updateAllDimensions);
        }
        //In "symmetric" mode, the top/bottom and left/right dimensions are always equal.
        //The control will only show "vertical" and "horizontal" inputs.
        this.symmetricModeEnabled = ko.observable(this.decideSymmetricMode(params));
        //Create computed observables for the "vertical" and "horizontal" dimensions.
        this.symmetricValues = {};
        for (const name in SymmetricDimensionMap) {
            if (!isSymmetricDimensionKey(name) || !SymmetricDimensionMap.hasOwnProperty(name)) {
                continue;
            }
            const sides = SymmetricDimensionMap[name];
            this.symmetricValues[name] = ko.computed({
                read: () => {
                    if (this.symmetricModeEnabled()) {
                        return this.dimensions[sides[0]]();
                    }
                    else {
                        return null;
                    }
                },
                write: (newValue) => {
                    if (this.symmetricModeEnabled()) {
                        newValue = this.normalizeValue(newValue);
                        for (const side of sides) {
                            this.dimensions[side](newValue);
                        }
                    }
                },
                deferEvaluation: true
            }).extend({ 'ameNumericInput': true });
        }
        //The control displays a different set of inputs depending on the current mode.
        this.inputsInOrder = ko.pureComputed(() => {
            let result;
            if (this.symmetricModeEnabled()) {
                result = [
                    ['vertical', 'Vertical'],
                    ['horizontal', 'Horizontal'],
                ];
            }
            else {
                result = this.dimensionsInOrder;
            }
            return result;
        });
        let sliderOptions = {
            'positionParentSelector': '.ame-single-box-dimension',
            'verticalOffset': -2,
        };
        if (typeof params.popupSliderWithin === 'string') {
            sliderOptions.positionWithinClosest = params.popupSliderWithin;
        }
        this.sliderAdapter = new _lazy_popup_slider_adapter_js__WEBPACK_IMPORTED_MODULE_1__.LazyPopupSliderAdapter(params.sliderRanges ? params.sliderRanges : null, '.ame-box-dimensions-control', 'input.ame-box-dimensions-input', sliderOptions);
    }
    get classes() {
        return ['ame-box-dimensions-control', ...super.classes];
    }
    //noinspection JSUnusedGlobalSymbols -- Used in the template.
    /**
     * Get an observable for a specific dimension or a pair of dimensions.
     *
     * Unfortunately, Knockout doesn't seem to support nested indexed accessors
     * like "dimensions[$data[0]]", so we have to use a method instead.
     */
    getInputObservable(key) {
        if (this.symmetricModeEnabled() && isSymmetricDimensionKey(key)) {
            return this.symmetricValues[key];
        }
        if (isDimensionKey(key)) {
            return this.dimensions[key];
        }
        throw new Error('Invalid input key for the current mode: ' + key);
    }
    getInputIdFor(key) {
        return this.inputIdPrefix + '-' + key;
    }
    // noinspection JSUnusedGlobalSymbols -- Used in the template.
    getInputAttributes(key) {
        return {
            id: this.getInputIdFor(key),
            'data-unit-element-id': this.unitElementId,
            'data-ame-box-dimension': key,
        };
    }
    // noinspection JSUnusedGlobalSymbols -- Used in the template.
    getSettingFor(key) {
        const settingName = 'value.' + key;
        if (settingName in this.settings) {
            return this.settings[settingName];
        }
        if (this.symmetricModeEnabled() && isSymmetricDimensionKey(key)) {
            for (const dimension of SymmetricDimensionMap[key]) {
                //Since both symmetric dimensions are always equal, we can use
                //either of the two settings.
                const settingName = 'value.' + dimension;
                if (settingName in this.settings) {
                    return this.settings[dimension];
                }
            }
        }
        return null;
    }
    // noinspection JSUnusedGlobalSymbols -- Actually used in the template.
    toggleLink() {
        this.isLinkActive(!this.isLinkActive());
        //When enabling "link" mode, fill all inputs with the same value.
        //Use the first non-empty value.
        if (this.isLinkActive()) {
            let firstValue = null;
            for (const dimensionObservable of Object.values(this.dimensions)) {
                const value = dimensionObservable();
                if ((value !== null) && (value !== '')) {
                    firstValue = value;
                    break;
                }
            }
            if (firstValue !== null) {
                firstValue = this.normalizeValue(firstValue);
                for (const dimensionObservable of Object.values(this.dimensions)) {
                    dimensionObservable(firstValue);
                }
            }
        }
    }
    normalizeValue(value) {
        if (value === null) {
            return null;
        }
        //Convert strings to numbers, and invalid strings to null.
        if (typeof value === 'string') {
            value = parseFloat(value);
            if (isNaN(value)) {
                return null;
            }
        }
        return value;
    }
    /**
     * Determine whether the control should be in "symmetric" mode.
     */
    decideSymmetricMode(componentParams) {
        //This mode is off by default and can be enabled by setting the "symmetricMode" parameter.
        let enableMode = (typeof componentParams['symmetricMode'] !== 'undefined')
            ? (!!componentParams['symmetricMode'])
            : false;
        if (!enableMode) {
            return false;
        }
        //Symmetric mode can't be enabled if the control doesn't have all side dimensions.
        const hasAllSideDimensions = SideDimensions.every((key) => {
            return (key in this.dimensions);
        });
        if (!hasAllSideDimensions) {
            return false;
        }
        //It also can only be enabled if top/bottom and left/right dimensions are equal.
        return ((this.dimensions['top']() === this.dimensions['bottom']())
            && (this.dimensions['left']() === this.dimensions['right']()));
    }
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ((0,_control_base_js__WEBPACK_IMPORTED_MODULE_0__.createControlComponentConfig)(AmeBoxDimensions, `
	<fieldset data-bind="class: classString, enable: isEnabled, style: styles, attr: wrapperAttributes"
	          data-ame-is-component="1">
		<!-- ko foreach: inputsInOrder -->
			<div data-bind="class: ('ame-single-box-dimension ame-box-dimension-' + $data[0])">
				<input type="text" inputmode="numeric" maxlength="20" pattern="\\s*-?[0-9]+(?:[.,]\\d*)?\\s*" 
					data-bind="value: $parent.getInputObservable($data[0]), valueUpdate: 'input',
					attr: $component.getInputAttributes($data[0]),
					class: ('ame-small-number-input ame-box-dimensions-input ame-box-dimensions-input-' + $data[0]),
					enable: $component.isEnabled,
					click: $component.sliderAdapter.handleKoClickEvent,
					ameValidationErrorClass: $component.getSettingFor($data[0])" />				
				<label data-bind="attr: {'for': $component.getInputIdFor($data[0])}" 
					class="ame-box-dimension-label"><span
					data-bind="text: $data[1]" class="ame-box-dimension-label-text"></span></label>
			</div>
		<!-- /ko -->
		<ame-unit-dropdown params="optionData: unitDropdownOptions, settings: {value: unitSetting},
			classes: ['ame-box-dimensions-unit-selector'],
			id: unitElementId"></ame-unit-dropdown>
		<button class="button button-secondary ame-box-dimensions-link-button hide-if-no-js"
			title="Link values" data-bind="enable: isEnabled, css: {'active': isLinkActive}, 
				click: $component.toggleLink.bind($component)"><span class="dashicons dashicons-admin-links"></span></button>
	</fieldset>
`));
//# sourceMappingURL=ame-box-dimensions.js.map

/***/ }),

/***/ "./extras/pro-customizables/ko-components/ame-choice-control/ame-choice-control.js":
/*!*****************************************************************************************!*\
  !*** ./extras/pro-customizables/ko-components/ame-choice-control/ame-choice-control.js ***!
  \*****************************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   AmeChoiceControl: () => (/* binding */ AmeChoiceControl),
/* harmony export */   ChoiceControlOption: () => (/* binding */ ChoiceControlOption)
/* harmony export */ });
/* harmony import */ var _control_base_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../control-base.js */ "./extras/pro-customizables/ko-components/control-base.js");

class ChoiceControlOption {
    constructor(data) {
        this.value = data.value;
        this.label = data.label;
        this.description = data.description || '';
        this.enabled = (typeof data.enabled === 'undefined') || data.enabled;
        this.icon = data.icon || '';
    }
}
class AmeChoiceControl extends _control_base_js__WEBPACK_IMPORTED_MODULE_0__.KoStandaloneControl {
    constructor(params, $element) {
        super(params, $element);
        this.options = ko.observableArray([]);
        if ((typeof params['options'] !== 'undefined') && Array.isArray(params.options)) {
            this.options(params.options.map((optionData) => new ChoiceControlOption(optionData)));
        }
    }
}
//# sourceMappingURL=ame-choice-control.js.map

/***/ }),

/***/ "./extras/pro-customizables/ko-components/ame-code-editor/ame-code-editor.js":
/*!***********************************************************************************!*\
  !*** ./extras/pro-customizables/ko-components/ame-code-editor/ame-code-editor.js ***!
  \***********************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _control_base_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../control-base.js */ "./extras/pro-customizables/ko-components/control-base.js");


/**
 * Code editor control with syntax highlighting.
 *
 * This control uses the custom Knockout binding "ameCodeMirror" to do the heavy
 * lifting. The binding is defined in ko-extensions.ts.
 *
 * Note: The user can disable syntax highlighting. In that case, this control
 * should behave like a normal textarea.
 */
class AmeCodeEditor extends _control_base_js__WEBPACK_IMPORTED_MODULE_0__.KoStandaloneControl {
    constructor(params, $element) {
        super(params, $element);
        if ((typeof params.editorSettings === 'object') && (params.editorSettings !== null)) {
            this.editorSettings = params.editorSettings;
        }
        else {
            this.editorSettings = false;
        }
    }
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ((0,_control_base_js__WEBPACK_IMPORTED_MODULE_0__.createControlComponentConfig)(AmeCodeEditor, `
	<div class="ame-code-editor-control-wrap">  
		<textarea data-bind="attr: inputAttributes, value: valueProxy, 
			class: inputClassString, ameCodeMirror: editorSettings" 
			class="large-text" cols="50" rows="10"></textarea>
	</div>
	<!-- ko if: (description) -->
		<!-- ko component: {name: 'ame-sibling-description', params: {description: description}} --><!-- /ko -->
	<!-- /ko -->
`));
//# sourceMappingURL=ame-code-editor.js.map

/***/ }),

/***/ "./extras/pro-customizables/ko-components/ame-color-picker/ame-color-picker.js":
/*!*************************************************************************************!*\
  !*** ./extras/pro-customizables/ko-components/ame-color-picker/ame-color-picker.js ***!
  \*************************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _control_base_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../control-base.js */ "./extras/pro-customizables/ko-components/control-base.js");

/**
 * A wrapper for the WordPress color picker.
 *
 * Note that the custom 'ameColorPicker' binding must be available when this component
 * is used. You must enqueue the 'ame-ko-extensions' script for this to work.
 */
class AmeColorPicker extends _control_base_js__WEBPACK_IMPORTED_MODULE_0__.KoStandaloneControl {
    constructor(params, $element) {
        super(params, $element);
    }
    koDescendantsComplete(node) {
        //Make the color picker input visible. Its visibility is set to hidden by default.
        if (node.nodeType === Node.COMMENT_NODE) {
            //The component was bound to a comment node. The real element
            //should be the next non-comment sibling.
            let nextElement;
            do {
                nextElement = node.nextElementSibling;
            } while (nextElement && (nextElement.nodeType === Node.COMMENT_NODE));
            if (!nextElement) {
                return; //This should never happen.
            }
            node = nextElement;
        }
        if (!node || (node.nodeType !== Node.ELEMENT_NODE)) {
            return; //This should never happen.
        }
        const $picker = jQuery(node);
        //This should be a .wp-picker-container element that contains an input.
        const $input = $picker.find('input.ame-color-picker');
        if ($input.length > 0) {
            $input.css('visibility', 'visible');
        }
    }
    get classes() {
        return ['ame-color-picker', 'ame-color-picker-component', ...super.classes];
    }
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ((0,_control_base_js__WEBPACK_IMPORTED_MODULE_0__.createControlComponentConfig)(AmeColorPicker, `
	<input type="text" style="visibility: hidden" data-bind="ameColorPicker: valueProxy, 
		class: classString, attr: inputAttributes">
`));
//# sourceMappingURL=ame-color-picker.js.map

/***/ }),

/***/ "./extras/pro-customizables/ko-components/ame-components.js":
/*!******************************************************************!*\
  !*** ./extras/pro-customizables/ko-components/ame-components.js ***!
  \******************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   registerBaseComponents: () => (/* binding */ registerBaseComponents)
/* harmony export */ });
/* harmony import */ var _ame_box_dimensions_ame_box_dimensions_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ame-box-dimensions/ame-box-dimensions.js */ "./extras/pro-customizables/ko-components/ame-box-dimensions/ame-box-dimensions.js");
/* harmony import */ var _ame_color_picker_ame_color_picker_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ame-color-picker/ame-color-picker.js */ "./extras/pro-customizables/ko-components/ame-color-picker/ame-color-picker.js");
/* harmony import */ var _ame_font_style_picker_ame_font_style_picker_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./ame-font-style-picker/ame-font-style-picker.js */ "./extras/pro-customizables/ko-components/ame-font-style-picker/ame-font-style-picker.js");
/* harmony import */ var _ame_image_selector_ame_image_selector_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./ame-image-selector/ame-image-selector.js */ "./extras/pro-customizables/ko-components/ame-image-selector/ame-image-selector.js");
/* harmony import */ var _ame_number_input_ame_number_input_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./ame-number-input/ame-number-input.js */ "./extras/pro-customizables/ko-components/ame-number-input/ame-number-input.js");
/* harmony import */ var _ame_nested_description_ame_nested_description_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./ame-nested-description/ame-nested-description.js */ "./extras/pro-customizables/ko-components/ame-nested-description/ame-nested-description.js");
/* harmony import */ var _ame_radio_button_bar_ame_radio_button_bar_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./ame-radio-button-bar/ame-radio-button-bar.js */ "./extras/pro-customizables/ko-components/ame-radio-button-bar/ame-radio-button-bar.js");
/* harmony import */ var _ame_radio_group_ame_radio_group_js__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./ame-radio-group/ame-radio-group.js */ "./extras/pro-customizables/ko-components/ame-radio-group/ame-radio-group.js");
/* harmony import */ var _ame_select_box_ame_select_box_js__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./ame-select-box/ame-select-box.js */ "./extras/pro-customizables/ko-components/ame-select-box/ame-select-box.js");
/* harmony import */ var _ame_sibling_description_ame_sibling_description_js__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./ame-sibling-description/ame-sibling-description.js */ "./extras/pro-customizables/ko-components/ame-sibling-description/ame-sibling-description.js");
/* harmony import */ var _ame_static_html_ame_static_html_js__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! ./ame-static-html/ame-static-html.js */ "./extras/pro-customizables/ko-components/ame-static-html/ame-static-html.js");
/* harmony import */ var _ame_text_input_ame_text_input_js__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! ./ame-text-input/ame-text-input.js */ "./extras/pro-customizables/ko-components/ame-text-input/ame-text-input.js");
/* harmony import */ var _ame_toggle_checkbox_ame_toggle_checkbox_js__WEBPACK_IMPORTED_MODULE_12__ = __webpack_require__(/*! ./ame-toggle-checkbox/ame-toggle-checkbox.js */ "./extras/pro-customizables/ko-components/ame-toggle-checkbox/ame-toggle-checkbox.js");
/* harmony import */ var _ame_unit_dropdown_ame_unit_dropdown_js__WEBPACK_IMPORTED_MODULE_13__ = __webpack_require__(/*! ./ame-unit-dropdown/ame-unit-dropdown.js */ "./extras/pro-customizables/ko-components/ame-unit-dropdown/ame-unit-dropdown.js");
/* harmony import */ var _ame_wp_editor_ame_wp_editor_js__WEBPACK_IMPORTED_MODULE_14__ = __webpack_require__(/*! ./ame-wp-editor/ame-wp-editor.js */ "./extras/pro-customizables/ko-components/ame-wp-editor/ame-wp-editor.js");
/* harmony import */ var _ame_horizontal_separator_ame_horizontal_separator_js__WEBPACK_IMPORTED_MODULE_15__ = __webpack_require__(/*! ./ame-horizontal-separator/ame-horizontal-separator.js */ "./extras/pro-customizables/ko-components/ame-horizontal-separator/ame-horizontal-separator.js");
/* harmony import */ var _ame_code_editor_ame_code_editor_js__WEBPACK_IMPORTED_MODULE_16__ = __webpack_require__(/*! ./ame-code-editor/ame-code-editor.js */ "./extras/pro-customizables/ko-components/ame-code-editor/ame-code-editor.js");
/* harmony import */ var _ame_tooltip_trigger_ame_tooltip_trigger_js__WEBPACK_IMPORTED_MODULE_17__ = __webpack_require__(/*! ./ame-tooltip-trigger/ame-tooltip-trigger.js */ "./extras/pro-customizables/ko-components/ame-tooltip-trigger/ame-tooltip-trigger.js");
/* harmony import */ var _ame_actor_feature_checkbox_ame_actor_feature_checkbox_js__WEBPACK_IMPORTED_MODULE_18__ = __webpack_require__(/*! ./ame-actor-feature-checkbox/ame-actor-feature-checkbox.js */ "./extras/pro-customizables/ko-components/ame-actor-feature-checkbox/ame-actor-feature-checkbox.js");
/* harmony import */ var _ame_event_button_ame_event_button_js__WEBPACK_IMPORTED_MODULE_19__ = __webpack_require__(/*! ./ame-event-button/ame-event-button.js */ "./extras/pro-customizables/ko-components/ame-event-button/ame-event-button.js");
/* harmony import */ var _ame_placeholder_ame_placeholder_js__WEBPACK_IMPORTED_MODULE_20__ = __webpack_require__(/*! ./ame-placeholder/ame-placeholder.js */ "./extras/pro-customizables/ko-components/ame-placeholder/ame-placeholder.js");
/* harmony import */ var _ame_general_control_group_ame_general_control_group_js__WEBPACK_IMPORTED_MODULE_21__ = __webpack_require__(/*! ./ame-general-control-group/ame-general-control-group.js */ "./extras/pro-customizables/ko-components/ame-general-control-group/ame-general-control-group.js");
/* harmony import */ var _ame_general_structure_ame_general_structure_js__WEBPACK_IMPORTED_MODULE_22__ = __webpack_require__(/*! ./ame-general-structure/ame-general-structure.js */ "./extras/pro-customizables/ko-components/ame-general-structure/ame-general-structure.js");
/* harmony import */ var _ame_postbox_section_ame_postbox_section_js__WEBPACK_IMPORTED_MODULE_23__ = __webpack_require__(/*! ./ame-postbox-section/ame-postbox-section.js */ "./extras/pro-customizables/ko-components/ame-postbox-section/ame-postbox-section.js");
/*
 * This utility module imports all the base Knockout components and exports
 * a function that can be used to register the components with Knockout.
 */
























let componentsRegistered = false;
/**
 * Register the base Knockout components that are part of AME.
 *
 * It's safe to call this function multiple times. It will only register the components once.
 */
function registerBaseComponents() {
    if (componentsRegistered) {
        return;
    }
    ko.components.register('ame-placeholder', _ame_placeholder_ame_placeholder_js__WEBPACK_IMPORTED_MODULE_20__["default"]);
    ko.components.register('ame-general-control-group', _ame_general_control_group_ame_general_control_group_js__WEBPACK_IMPORTED_MODULE_21__["default"]);
    ko.components.register('ame-general-structure', _ame_general_structure_ame_general_structure_js__WEBPACK_IMPORTED_MODULE_22__["default"]);
    ko.components.register('ame-box-dimensions', _ame_box_dimensions_ame_box_dimensions_js__WEBPACK_IMPORTED_MODULE_0__["default"]);
    ko.components.register('ame-color-picker', _ame_color_picker_ame_color_picker_js__WEBPACK_IMPORTED_MODULE_1__["default"]);
    ko.components.register('ame-font-style-picker', _ame_font_style_picker_ame_font_style_picker_js__WEBPACK_IMPORTED_MODULE_2__["default"]);
    ko.components.register('ame-image-selector', _ame_image_selector_ame_image_selector_js__WEBPACK_IMPORTED_MODULE_3__["default"]);
    ko.components.register('ame-number-input', _ame_number_input_ame_number_input_js__WEBPACK_IMPORTED_MODULE_4__["default"]);
    ko.components.register('ame-nested-description', _ame_nested_description_ame_nested_description_js__WEBPACK_IMPORTED_MODULE_5__["default"]);
    ko.components.register('ame-radio-button-bar', _ame_radio_button_bar_ame_radio_button_bar_js__WEBPACK_IMPORTED_MODULE_6__["default"]);
    ko.components.register('ame-radio-group', _ame_radio_group_ame_radio_group_js__WEBPACK_IMPORTED_MODULE_7__["default"]);
    ko.components.register('ame-select-box', _ame_select_box_ame_select_box_js__WEBPACK_IMPORTED_MODULE_8__["default"]);
    ko.components.register('ame-sibling-description', _ame_sibling_description_ame_sibling_description_js__WEBPACK_IMPORTED_MODULE_9__["default"]);
    ko.components.register('ame-static-html', _ame_static_html_ame_static_html_js__WEBPACK_IMPORTED_MODULE_10__["default"]);
    ko.components.register('ame-text-input', _ame_text_input_ame_text_input_js__WEBPACK_IMPORTED_MODULE_11__["default"]);
    ko.components.register('ame-toggle-checkbox', _ame_toggle_checkbox_ame_toggle_checkbox_js__WEBPACK_IMPORTED_MODULE_12__["default"]);
    ko.components.register('ame-unit-dropdown', _ame_unit_dropdown_ame_unit_dropdown_js__WEBPACK_IMPORTED_MODULE_13__["default"]);
    ko.components.register('ame-wp-editor', _ame_wp_editor_ame_wp_editor_js__WEBPACK_IMPORTED_MODULE_14__["default"]);
    ko.components.register('ame-horizontal-separator', _ame_horizontal_separator_ame_horizontal_separator_js__WEBPACK_IMPORTED_MODULE_15__["default"]);
    ko.components.register('ame-code-editor', _ame_code_editor_ame_code_editor_js__WEBPACK_IMPORTED_MODULE_16__["default"]);
    ko.components.register('ame-actor-feature-checkbox', _ame_actor_feature_checkbox_ame_actor_feature_checkbox_js__WEBPACK_IMPORTED_MODULE_18__["default"]);
    ko.components.register('ame-event-button', _ame_event_button_ame_event_button_js__WEBPACK_IMPORTED_MODULE_19__["default"]);
    ko.components.register('ame-postbox-section', _ame_postbox_section_ame_postbox_section_js__WEBPACK_IMPORTED_MODULE_23__["default"]);
    ko.components.register('ame-tooltip-trigger', _ame_tooltip_trigger_ame_tooltip_trigger_js__WEBPACK_IMPORTED_MODULE_17__["default"]);
    componentsRegistered = true;
}
//# sourceMappingURL=ame-components.js.map

/***/ }),

/***/ "./extras/pro-customizables/ko-components/ame-description/ame-description.js":
/*!***********************************************************************************!*\
  !*** ./extras/pro-customizables/ko-components/ame-description/ame-description.js ***!
  \***********************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   AmeDescriptionComponent: () => (/* binding */ AmeDescriptionComponent)
/* harmony export */ });
/* harmony import */ var _control_base_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../control-base.js */ "./extras/pro-customizables/ko-components/control-base.js");

/**
 * Base class for description components.
 */
class AmeDescriptionComponent extends _control_base_js__WEBPACK_IMPORTED_MODULE_0__.KoStandaloneControl {
    constructor(params, $element) {
        super(params, $element);
        this.description = params.description || '';
    }
}
//# sourceMappingURL=ame-description.js.map

/***/ }),

/***/ "./extras/pro-customizables/ko-components/ame-event-button/ame-event-button.js":
/*!*************************************************************************************!*\
  !*** ./extras/pro-customizables/ko-components/ame-event-button/ame-event-button.js ***!
  \*************************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _control_base_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../control-base.js */ "./extras/pro-customizables/ko-components/control-base.js");

class AmeEvetButton extends _control_base_js__WEBPACK_IMPORTED_MODULE_0__.KoStandaloneControl {
    constructor(params, $element) {
        super(params, $element);
        this.eventData = null;
        this.wrap = false;
        this.cachedEventTarget = null;
        this.triedToFindEventTarget = false;
        if (typeof params['eventName'] === 'undefined') {
            throw new Error('AmeEventButton requires an "eventName" parameter to be defined.');
        }
        this.eventName = String(params['eventName']);
        if (typeof params['eventData'] !== 'undefined') {
            this.eventData = params['eventData'];
        }
        if (typeof params['wrap'] !== 'undefined') {
            this.wrap = Boolean(params['wrap']);
        }
    }
    triggerEvent() {
        this.findEventTarget()?.dispatchEvent(new CustomEvent(this.eventName, {
            detail: this.eventData,
            bubbles: true,
        }));
    }
    findEventTarget() {
        if (this.triedToFindEventTarget) {
            return this.cachedEventTarget;
        }
        this.triedToFindEventTarget = true;
        const $child = this.findChild('input, p');
        if ($child.length === 0) {
            throw new Error('AmeEventButton could not find its child element to dispatch the event on.');
        }
        this.cachedEventTarget = $child[0];
        return this.cachedEventTarget;
    }
    get classes() {
        return ['button', 'ame-event-button-control', ...super.classes];
    }
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ((0,_control_base_js__WEBPACK_IMPORTED_MODULE_0__.createControlComponentConfig)(AmeEvetButton, `
	<!-- ko if: wrap -->
	<p><input data-bind="class: classString, enable: isEnabled, click: triggerEvent, value: label" type="button"></p>
	<!-- /ko -->
	<!-- ko ifnot: wrap -->
	<input data-bind="class: classString, enable: isEnabled, click: triggerEvent, value: label" type="button">
	<!-- /ko -->
`));
//# sourceMappingURL=ame-event-button.js.map

/***/ }),

/***/ "./extras/pro-customizables/ko-components/ame-font-style-picker/ame-font-style-picker.js":
/*!***********************************************************************************************!*\
  !*** ./extras/pro-customizables/ko-components/ame-font-style-picker/ame-font-style-picker.js ***!
  \***********************************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _control_base_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../control-base.js */ "./extras/pro-customizables/ko-components/control-base.js");

//Note: Font style picker CSS is already included in the main 'controls.scss' file
//and won't be duplicated or included here. Instead, load that stylesheet when
//using any control components.
/**
 * Font style options that can be selected in the picker component.
 *
 * Regrettably, these are duplicated from the PHP version of the control.
 * Once browsers support importing JSON files, we can move this to a separate
 * file and use that file in both places.
 */
const fontStyleOptions = {
    "font-style": [
        {
            "value": null,
            "text": "Default font style",
            "label": "&mdash;"
        },
        {
            "value": "italic",
            "text": "Italic",
            "label": "<span class=\"dashicons dashicons-editor-italic\"></span>"
        }
    ],
    "text-transform": [
        {
            "value": null,
            "text": "Default letter case",
            "label": "&mdash;"
        },
        {
            "value": "uppercase",
            "text": "Uppercase",
            "label": {
                'text-transform': 'uppercase'
            }
        },
        {
            "value": "lowercase",
            "text": "Lowercase",
            "label": {
                'text-transform': 'lowercase'
            }
        },
        {
            "value": "capitalize",
            "text": "Capitalize each word",
            "label": {
                'text-transform': 'capitalize'
            }
        }
    ],
    "font-variant": [
        {
            "value": null,
            "text": "Default font variant",
            "label": "&mdash;"
        },
        {
            "value": "small-caps",
            "text": "Small caps",
            "label": {
                'font-variant': 'small-caps'
            }
        }
    ],
    "text-decoration": [
        {
            "value": null,
            "text": "Default text decoration",
            "label": "&mdash;"
        },
        {
            "value": "underline",
            "text": "Underline",
            "label": "<span class=\"dashicons dashicons-editor-underline\"></span>"
        },
        {
            "value": "line-through",
            "text": "Strikethrough",
            "label": "<span class=\"dashicons dashicons-editor-strikethrough\"></span>"
        }
    ]
};
//Generate label HTML for options that don't have it yet.
function makeFontSample(styles) {
    let styleString = '';
    for (const [property, value] of Object.entries(styles)) {
        styleString += `${property}: ${value};`;
    }
    return `<span class="ame-font-sample" style="${styleString}">ab</span>`;
}
let flattenedOptions = [];
for (const [property, options] of Object.entries(fontStyleOptions)) {
    options.forEach((option) => {
        //Skip null values. They're used to indicate the default option,
        //and we don't need those in the Knockout version of this control.
        if (option.value === null) {
            return;
        }
        let labelString;
        if (typeof option.label === 'object') {
            labelString = makeFontSample(option.label);
        }
        else {
            labelString = option.label;
        }
        flattenedOptions.push({
            'value': option.value,
            'text': option.text || '',
            'property': property,
            'label': labelString
        });
    });
}
class AmeFontStylePicker extends _control_base_js__WEBPACK_IMPORTED_MODULE_0__.KoStandaloneControl {
    constructor(params, $element) {
        super(params, $element);
        this.options = flattenedOptions;
    }
    get classes() {
        return ['ame-font-style-control', ...super.classes];
    }
    // noinspection JSUnusedGlobalSymbols -- Used in the template, below.
    isOptionSelected(property, value) {
        if (this.settings.hasOwnProperty(property)) {
            return (this.settings[property].value() === value);
        }
        return false;
    }
    // noinspection JSUnusedGlobalSymbols -- Used in the template.
    toggleOption(property, value) {
        if (!this.settings.hasOwnProperty(property)) {
            return;
        }
        const targetSetting = this.settings[property];
        if (targetSetting.value() === value) {
            //When the user clicks on the currently selected option, reset it to the default.
            targetSetting.tryUpdate(null);
        }
        else {
            //Otherwise, set the new value.
            targetSetting.tryUpdate(value);
        }
    }
}
//Note: This weird spacing in the template string is intentional. It's used to
//remove whitespace nodes from the DOM, which would otherwise slightly change
//the layout of the control.
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ((0,_control_base_js__WEBPACK_IMPORTED_MODULE_0__.createControlComponentConfig)(AmeFontStylePicker, `
	<fieldset data-ame-is-component="1" data-bind="class: classString, style: styles">
		<!-- 
		ko foreach: options 
		--><label class="ame-font-style-control-choice" data-bind="attr: {title: (text || '')}"><!-- 
			ko if: text 
			--><span class="screen-reader-text" data-bind="text: text"></span><!-- 
			/ko 
		--><span class="button button-secondary ame-font-style-control-choice-label" 
				data-bind="html: label, css: { 'active': $component.isOptionSelected(property, value) },
				click: $component.toggleOption.bind($component, property, value)"></span></label><!-- 
		/ko -->
	</fieldset>
`));
//# sourceMappingURL=ame-font-style-picker.js.map

/***/ }),

/***/ "./extras/pro-customizables/ko-components/ame-general-control-group/ame-general-control-group.js":
/*!*******************************************************************************************************!*\
  !*** ./extras/pro-customizables/ko-components/ame-general-control-group/ame-general-control-group.js ***!
  \*******************************************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _control_base_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../control-base.js */ "./extras/pro-customizables/ko-components/control-base.js");
/* harmony import */ var _assets_customizable_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../assets/customizable.js */ "./extras/pro-customizables/assets/customizable.js");


class AmeGeneralControlGroup extends _control_base_js__WEBPACK_IMPORTED_MODULE_0__.KoContainerViewModel {
    constructor(params, $element) {
        super(params, $element);
        this.labelFor = params.labelFor || null;
    }
    getExpectedUiElementType() {
        return _assets_customizable_js__WEBPACK_IMPORTED_MODULE_1__.AmeCustomizable.ControlGroup;
    }
    get classes() {
        return ['ame-general-control-group', ...super.classes];
    }
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ((0,_control_base_js__WEBPACK_IMPORTED_MODULE_0__.createComponentConfig)(AmeGeneralControlGroup, `
	<div data-bind="class: classString">
		<h4 class="ame-gcg-title">
			<!-- ko if: title -->
				<!-- ko if: labelFor -->
					<label data-bind="attr: {for: labelFor}, text: title"></label>
				<!-- /ko -->
				<!-- ko ifnot: labelFor -->
					<span data-bind="text: title"></span>
				<!-- /ko -->
			<!-- /ko -->
		</h4>
		<div class="ame-gcg-children">
		<!-- ko foreach: childComponents -->
			<!-- ko component: $data --><!-- /ko -->
		<!-- /ko -->
		</div>
	</div>	
`));
//# sourceMappingURL=ame-general-control-group.js.map

/***/ }),

/***/ "./extras/pro-customizables/ko-components/ame-general-structure/ame-general-structure.js":
/*!***********************************************************************************************!*\
  !*** ./extras/pro-customizables/ko-components/ame-general-structure/ame-general-structure.js ***!
  \***********************************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _control_base_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../control-base.js */ "./extras/pro-customizables/ko-components/control-base.js");

class AmeGeneralStructure extends _control_base_js__WEBPACK_IMPORTED_MODULE_0__.KoRendererViewModel {
    constructor(params, $element) {
        super(params, $element);
    }
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ((0,_control_base_js__WEBPACK_IMPORTED_MODULE_0__.createRendererComponentConfig)(AmeGeneralStructure, `
	<!-- ko foreach: structure.children -->
		<!-- ko if: $data.component -->
			<!-- ko component: { name: $data.component, params: $data.getComponentParams() } --><!-- /ko -->					
		<!-- /ko -->
		<!-- ko ifnot: $data.component -->
			<!-- ko component: { name: 'ame-placeholder', params: $data.getComponentParams() } --><!-- /ko -->
		<!-- /ko -->
	<!-- /ko -->
`));
//# sourceMappingURL=ame-general-structure.js.map

/***/ }),

/***/ "./extras/pro-customizables/ko-components/ame-horizontal-separator/ame-horizontal-separator.js":
/*!*****************************************************************************************************!*\
  !*** ./extras/pro-customizables/ko-components/ame-horizontal-separator/ame-horizontal-separator.js ***!
  \*****************************************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _control_base_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../control-base.js */ "./extras/pro-customizables/ko-components/control-base.js");

class AmeHorizontalSeparator extends _control_base_js__WEBPACK_IMPORTED_MODULE_0__.KoStandaloneControl {
    constructor(params, $element) {
        super(params, $element);
    }
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ((0,_control_base_js__WEBPACK_IMPORTED_MODULE_0__.createControlComponentConfig)(AmeHorizontalSeparator, `
	<div class="ame-horizontal-separator"></div>
`));
//# sourceMappingURL=ame-horizontal-separator.js.map

/***/ }),

/***/ "./extras/pro-customizables/ko-components/ame-image-selector/ame-image-selector.js":
/*!*****************************************************************************************!*\
  !*** ./extras/pro-customizables/ko-components/ame-image-selector/ame-image-selector.js ***!
  \*****************************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _control_base_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../control-base.js */ "./extras/pro-customizables/ko-components/control-base.js");


/**
 * Image selector control.
 *
 * This implementation hands off the work to the existing AmeImageSelectorApi.ImageSelector
 * class to avoid duplicating the effort. That class is not a module because it is also
 * used for the more progressive-enhancement-y PHP-rendered controls, so we can't import it.
 */
class AmeImageSelector extends _control_base_js__WEBPACK_IMPORTED_MODULE_0__.KoStandaloneControl {
    constructor(params, $element) {
        super(params, $element);
        this.selectorInstance = null;
        //Verify that our dependencies are available.
        if (typeof AmeImageSelectorApi === 'undefined') {
            throw new Error('AmeImageSelectorApi is not available. Remember to enqueue "ame-image-selector-control-v2".');
        }
        if (typeof AmeImageSelectorApi.ImageSelector === 'undefined') {
            throw new Error('AmeImageSelectorApi.ImageSelector is not available. This is probably a bug.');
        }
        this.externalUrlsAllowed = !!params.externalUrlsAllowed;
        this.canSelectMedia = !!params.canSelectMedia;
        this.imageProxy = this.settings.value.value;
    }
    get classes() {
        return [
            'ame-image-selector-v2',
            ...super.classes,
        ];
    }
    koDescendantsComplete() {
        const $container = this.findChild('.ame-image-selector-v2');
        if ($container.length === 0) {
            return;
        }
        this.selectorInstance = new AmeImageSelectorApi.ImageSelector($container, {
            externalUrlsAllowed: this.externalUrlsAllowed,
            canSelectMedia: this.canSelectMedia,
        }, this.imageProxy());
    }
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ((0,_control_base_js__WEBPACK_IMPORTED_MODULE_0__.createControlComponentConfig)(AmeImageSelector, `
	<div class="ame-image-selector-v2" data-ame-is-component="1" 
		data-bind="class: classString, ameObservableChangeEvents: { observable: imageProxy }">
		<!-- The contents should be generated by the image selector API. -->
	</div>
`));
//# sourceMappingURL=ame-image-selector.js.map

/***/ }),

/***/ "./extras/pro-customizables/ko-components/ame-nested-description/ame-nested-description.js":
/*!*************************************************************************************************!*\
  !*** ./extras/pro-customizables/ko-components/ame-nested-description/ame-nested-description.js ***!
  \*************************************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _control_base_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../control-base.js */ "./extras/pro-customizables/ko-components/control-base.js");
/* harmony import */ var _ame_description_ame_description_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../ame-description/ame-description.js */ "./extras/pro-customizables/ko-components/ame-description/ame-description.js");


/**
 * A simple component that displays the description of a UI element.
 *
 * Like AmeSiblingDescription, but intended to be rendered inside
 * the parent control or container, not as a sibling.
 */
class AmeNestedDescription extends _ame_description_ame_description_js__WEBPACK_IMPORTED_MODULE_1__.AmeDescriptionComponent {
    constructor(params, $element) {
        super(params, $element);
        this.includeLineBreak = true;
        if (typeof params.includeLineBreak !== 'undefined') {
            this.includeLineBreak = params.includeLineBreak;
        }
    }
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ((0,_control_base_js__WEBPACK_IMPORTED_MODULE_0__.createComponentConfig)(AmeNestedDescription, `
	<!-- ko if: includeLineBreak --><br><!-- /ko --><span class="description" data-bind="html: description"></span>	
`));
//# sourceMappingURL=ame-nested-description.js.map

/***/ }),

/***/ "./extras/pro-customizables/ko-components/ame-number-input/ame-number-input.js":
/*!*************************************************************************************!*\
  !*** ./extras/pro-customizables/ko-components/ame-number-input/ame-number-input.js ***!
  \*************************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   AmeNumberInput: () => (/* binding */ AmeNumberInput),
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _control_base_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../control-base.js */ "./extras/pro-customizables/ko-components/control-base.js");
/// <reference path="../../../../customizables/assets/popup-slider.d.ts" />

class AmeNumberInput extends _control_base_js__WEBPACK_IMPORTED_MODULE_0__.KoStandaloneControl {
    constructor(params, $element) {
        super(params, $element);
        this.sliderRanges = null;
        this.slider = null;
        this.numericValue = this.valueProxy.extend({ 'ameNumericInput': true });
        this.unitText = params.unitText || '';
        this.hasUnitDropdown = params.hasUnitDropdown || false;
        this.unitElementId = params.unitElementId || '';
        if (this.hasUnitDropdown && params.unitDropdownOptions) {
            this.unitDropdownOptions = {
                options: params.unitDropdownOptions.options || [],
                optionsText: params.unitDropdownOptions.optionsText || 'text',
                optionsValue: params.unitDropdownOptions.optionsValue || 'value'
            };
        }
        else {
            this.unitDropdownOptions = null;
        }
        this.min = params.min || null;
        this.max = params.max || null;
        this.step = params.step || null;
        if (params.sliderRanges) {
            this.sliderRanges = params.sliderRanges;
        }
        this.popupSliderWithin = (typeof params.popupSliderWithin === 'string') ? params.popupSliderWithin : null;
        this.inputClasses.unshift('ame-input-with-popup-slider', 'ame-number-input');
    }
    get classes() {
        const classes = ['ame-number-input-control'];
        if (this.sliderRanges !== null) {
            classes.push('ame-container-with-popup-slider');
        }
        classes.push(...super.classes);
        return classes;
    }
    get inputClasses() {
        const classes = ['ame-input-with-popup-slider', 'ame-number-input'];
        classes.push(...super.inputClasses);
        return classes;
    }
    getAdditionalInputAttributes() {
        let attributes = super.getAdditionalInputAttributes();
        if (this.min !== null) {
            attributes['min'] = this.min.toString();
        }
        if (this.max !== null) {
            attributes['max'] = this.max.toString();
        }
        if (this.step !== null) {
            attributes['step'] = this.step.toString();
        }
        if (this.unitElementId) {
            attributes['data-unit-element-id'] = this.unitElementId;
        }
        return attributes;
    }
    // noinspection JSUnusedGlobalSymbols -- Used in the Knockout template in this same file.
    showPopupSlider($data, event) {
        if ((this.sliderRanges === null) || (typeof AmePopupSlider === 'undefined')) {
            return;
        }
        //Some sanity checks.
        if (!event.target) {
            return;
        }
        const $input = jQuery(event.target);
        if ($input.is(':disabled') || !$input.is('input')) {
            return;
        }
        const $container = $input.closest('.ame-container-with-popup-slider');
        if ($container.length < 1) {
            return;
        }
        //Initialize the slider if it's not already initialized.
        if (!this.slider) {
            let sliderOptions = {};
            if (this.popupSliderWithin) {
                sliderOptions.positionWithinClosest = this.popupSliderWithin;
            }
            //In HTML, we would pass the range data as a "data-slider-ranges" attribute,
            //but here we can just set the data directly.
            $input.data('slider-ranges', this.sliderRanges);
            this.slider = AmePopupSlider.createSlider($container, sliderOptions);
        }
        this.slider.showForInput($input);
    }
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ((0,_control_base_js__WEBPACK_IMPORTED_MODULE_0__.createControlComponentConfig)(AmeNumberInput, `
	<fieldset data-bind="class: classString, enable: isEnabled">
		<div data-bind="class: (hasUnitDropdown ? 'ame-input-group' : '')">
			<input type="text" inputmode="numeric" maxlength="20" pattern="\\s*-?[0-9]+(?:[.,]\\d*)?\\s*"
				   data-bind="attr: inputAttributes, value: numericValue, valueUpdate: 'input', 
				   class: inputClassString, enable: isEnabled, click: showPopupSlider.bind($component),
				   ameValidationErrorClass: settings.value">
			
			<!-- ko if: hasUnitDropdown -->
				<ame-unit-dropdown params="optionData: unitDropdownOptions, settings: {value: settings.unit},
					classes: ['ame-input-group-secondary', 'ame-number-input-unit'],
					id: unitElementId"></ame-unit-dropdown>
			<!-- /ko -->
			<!-- ko if: (!hasUnitDropdown && unitText) -->
				<span class="ame-number-input-unit" 
					  data-bind="text: unitText, attr: {id: unitElementId, 'data-number-unit': unitText}"></span>
			<!-- /ko -->
		</div>
	</fieldset>	
`));
//# sourceMappingURL=ame-number-input.js.map

/***/ }),

/***/ "./extras/pro-customizables/ko-components/ame-placeholder/ame-placeholder.js":
/*!***********************************************************************************!*\
  !*** ./extras/pro-customizables/ko-components/ame-placeholder/ame-placeholder.js ***!
  \***********************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _control_base_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../control-base.js */ "./extras/pro-customizables/ko-components/control-base.js");
/* harmony import */ var _assets_customizable_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../assets/customizable.js */ "./extras/pro-customizables/assets/customizable.js");


var UiElement = _assets_customizable_js__WEBPACK_IMPORTED_MODULE_1__.AmeCustomizable.UiElement;
class AmePlaceholder extends _control_base_js__WEBPACK_IMPORTED_MODULE_0__.KoComponentViewModel {
    constructor(params, $element) {
        super(params, $element);
    }
    getExpectedUiElementType() {
        return UiElement;
    }
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ((0,_control_base_js__WEBPACK_IMPORTED_MODULE_0__.createComponentConfig)(AmePlaceholder, `
	<div class="ame-placeholder-component">
		<span class="ame-placeholder-component-text">
			UI element without a component. <br>
			
			<!-- ko if: id --> 
			ID: <strong data-bind="text: id"></strong>
			<!-- /ko -->
			<!-- ko if: params.label --> 
			Label: <strong data-bind="text: params.label"></strong>
			<!-- /ko -->
			<!-- ko if: params.title -->
			Title: <strong data-bind="text: params.title"></strong>
			<!-- /ko -->
		</span>
	</div>
`));
//# sourceMappingURL=ame-placeholder.js.map

/***/ }),

/***/ "./extras/pro-customizables/ko-components/ame-postbox-section/ame-postbox-section.js":
/*!*******************************************************************************************!*\
  !*** ./extras/pro-customizables/ko-components/ame-postbox-section/ame-postbox-section.js ***!
  \*******************************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _control_base_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../control-base.js */ "./extras/pro-customizables/ko-components/control-base.js");
/* harmony import */ var _assets_customizable_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../assets/customizable.js */ "./extras/pro-customizables/assets/customizable.js");


var Section = _assets_customizable_js__WEBPACK_IMPORTED_MODULE_1__.AmeCustomizable.Section;
class AmePostboxSection extends _control_base_js__WEBPACK_IMPORTED_MODULE_0__.KoContainerViewModel {
    constructor(params, $element) {
        super(params, $element);
        this.htmlId = '';
        this.descriptionAsTooltip = null;
        this.htmlId = this.id;
        //Optionally, remember the open/closed state of the section.
        if (this.id && this.registry && this.registry.has('collapsibleStateStore')) {
            const collapsibleStateStore = this.registry.get('collapsibleStateStore');
            this.isOpen = collapsibleStateStore.getOrCreateObservable(this.id, true);
        }
        else {
            this.isOpen = ko.observable(true);
        }
        if (this.description) {
            this.descriptionAsTooltip = {
                htmlContent: this.description,
                type: 'info',
                extraClasses: []
            };
        }
    }
    getExpectedUiElementType() {
        return Section;
    }
    get shouldMapMiscChildrenToPlaceholders() {
        return true;
    }
    toggle() {
        this.isOpen(!this.isOpen());
    }
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ((0,_control_base_js__WEBPACK_IMPORTED_MODULE_0__.createComponentConfig)(AmePostboxSection, `
	<div class="ws-ame-postbox ame-postbox-section" 
		data-bind="css: { 'ws-ame-closed-postbox': !isOpen() }, attr: { id: htmlId }">
		<div class="ws-ame-postbox-header">
			<h3>
				<span data-bind="text: title"></span>
				<!-- ko if: descriptionAsTooltip -->
					<!-- ko component: {name: 'ame-tooltip-trigger', params: {tooltip: descriptionAsTooltip}} --><!-- /ko -->
				<!-- /ko -->
			</h3>
			<button class="ws-ame-postbox-toggle" data-bind="click: toggle"></button>
		</div>
		<div class="ws-ame-postbox-content" data-bind="class: childrenContainerClass">
			<!-- ko foreach: childComponents -->
				<div class="ame-postbox-section-child">
				<!-- ko component: $data --><!-- /ko -->
				</div>
			<!-- /ko -->			
		</div>
	</div>
`));
//# sourceMappingURL=ame-postbox-section.js.map

/***/ }),

/***/ "./extras/pro-customizables/ko-components/ame-radio-button-bar/ame-radio-button-bar.js":
/*!*********************************************************************************************!*\
  !*** ./extras/pro-customizables/ko-components/ame-radio-button-bar/ame-radio-button-bar.js ***!
  \*********************************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _control_base_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../control-base.js */ "./extras/pro-customizables/ko-components/control-base.js");
/* harmony import */ var _ame_choice_control_ame_choice_control_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../ame-choice-control/ame-choice-control.js */ "./extras/pro-customizables/ko-components/ame-choice-control/ame-choice-control.js");



class AmeRadioButtonBar extends _ame_choice_control_ame_choice_control_js__WEBPACK_IMPORTED_MODULE_1__.AmeChoiceControl {
    constructor(params, $element) {
        super(params, $element);
    }
    get classes() {
        return ['ame-radio-button-bar-control', ...super.classes];
    }
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ((0,_control_base_js__WEBPACK_IMPORTED_MODULE_0__.createControlComponentConfig)(AmeRadioButtonBar, `
	<fieldset data-bind="class: classString, enable: isEnabled, style: styles" data-ame-is-component="1">
		<!-- ko foreach: options -->
		<label data-bind="attr: {title: description}" class="ame-radio-bar-item">
			<input type="radio" data-bind="class: $component.inputClassString,
				checked: $component.valueProxy, checkedValue: value, enable: $component.isEnabled,
				ameObservableChangeEvents: true">
			<span class="button ame-radio-bar-button" data-bind="css: {'ame-rb-has-label' : label}">
				<!-- ko if: (icon && (icon.indexOf('dashicons-') >= 0)) -->
					<span data-bind="class: 'dashicons ' + icon"></span>
				<!-- /ko -->
				<!-- ko if: label -->
					<span class="ame-rb-label" data-bind="text: label"></span>
				<!-- /ko -->
			</span>
		</label>
		<!-- /ko -->
	</fieldset>
`));
//# sourceMappingURL=ame-radio-button-bar.js.map

/***/ }),

/***/ "./extras/pro-customizables/ko-components/ame-radio-group/ame-radio-group.js":
/*!***********************************************************************************!*\
  !*** ./extras/pro-customizables/ko-components/ame-radio-group/ame-radio-group.js ***!
  \***********************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _control_base_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../control-base.js */ "./extras/pro-customizables/ko-components/control-base.js");
/* harmony import */ var _ame_choice_control_ame_choice_control_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../ame-choice-control/ame-choice-control.js */ "./extras/pro-customizables/ko-components/ame-choice-control/ame-choice-control.js");



// noinspection JSUnusedGlobalSymbols -- Enum keys like "Paragraph" are used when serializing wrapStyle in PHP.
var WrapStyle;
(function (WrapStyle) {
    WrapStyle["LineBreak"] = "br";
    WrapStyle["Paragraph"] = "p";
    WrapStyle["None"] = "";
})(WrapStyle || (WrapStyle = {}));
function isWrapStyle(value) {
    if (typeof value !== 'string') {
        return false;
    }
    return (typeof WrapStyle[value] === 'string');
}
let nextRadioGroupId = 1;
class AmeRadioGroup extends _ame_choice_control_ame_choice_control_js__WEBPACK_IMPORTED_MODULE_1__.AmeChoiceControl {
    constructor(params, $element) {
        super(params, $element);
        this.wrapStyle = WrapStyle.None;
        this.childByValue = new Map();
        if ((typeof params['valueChildIndexes'] === 'object') && Array.isArray(params.valueChildIndexes)) {
            const children = ko.unwrap(this.inputChildren);
            for (const [value, index] of params.valueChildIndexes) {
                if (!children || !children[index]) {
                    throw new Error('The "' + this.label() + '" radio group has no children, but its valueChildIndexes'
                        + ' requires child #' + index + ' to be associated with value "' + value + '".');
                }
                this.childByValue.set(value, children[index]);
            }
        }
        this.wrapStyle = isWrapStyle(params.wrapStyle) ? WrapStyle[params.wrapStyle] : WrapStyle.None;
        if (this.childByValue.size > 0) {
            this.wrapStyle = WrapStyle.None;
        }
        this.radioInputPrefix = (typeof params.radioInputPrefix === 'string')
            ? params.radioInputPrefix
            : ('ame-rg-input-' + nextRadioGroupId++ + '-');
    }
    get classes() {
        const result = ['ame-radio-group-component', ...super.classes];
        if (this.childByValue.size > 0) {
            result.push('ame-rg-has-nested-controls');
        }
        return result;
    }
    // noinspection JSUnusedGlobalSymbols -- Used in the template below.
    getChoiceChild(value) {
        return this.childByValue.get(value) || null;
    }
    // noinspection JSUnusedGlobalSymbols -- Used in the template.
    /**
     * Get the ID attribute for a radio input.
     *
     * Note: This must match the algorithm used by the PHP version of this control
     * to work correctly with the BorderStyleSelector control that adds style samples
     * to each choice and uses the ID to link them to the inputs (so that clicking
     * the sample selects the option).
     */
    getRadioInputId(choice) {
        let sanitizedValue = (choice.value !== null) ? choice.value.toString() : '';
        //Emulate the sanitize_key() function from WordPress core.
        sanitizedValue = sanitizedValue.toLowerCase().replace(/[^a-z0-9_\-]/gi, '');
        return this.radioInputPrefix + sanitizedValue;
    }
}
const choiceTemplate = `
	<label data-bind="class: 'ame-rg-option-label',
		css: {'ame-rg-has-choice-child' : ($component.getChoiceChild(value) !== null)}">
		<input type="radio" data-bind="class: $component.inputClassString, 
			checked: $component.valueProxy, checkedValue: value, enable: $component.isEnabled,
			attr: {id: $component.getRadioInputId($data)}">
		<span data-bind="html: label"></span>
		<!-- ko if: description -->
			<!-- ko component: {name: 'ame-nested-description', params: {description: description}} --><!-- /ko -->
		<!-- /ko -->
	</label>
`;
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ((0,_control_base_js__WEBPACK_IMPORTED_MODULE_0__.createControlComponentConfig)(AmeRadioGroup, `
	<fieldset data-bind="class: classString, enable: isEnabled, style: styles">
		<!-- ko foreach: options -->
			<!-- ko if: $component.wrapStyle === 'br' -->
				${choiceTemplate} <br>
			<!-- /ko -->
			<!-- ko if: $component.wrapStyle === 'p' -->
				<p>${choiceTemplate}</p>
			<!-- /ko -->
			<!-- ko if: $component.wrapStyle === '' -->
				${choiceTemplate}
			<!-- /ko -->
			<!-- ko with: $component.getChoiceChild(value) -->
			<span class="ame-rg-nested-control" 
				data-bind="component: {name: component, params: getComponentParams()}"></span>
			<!-- /ko -->
		<!-- /ko -->
	</fieldset>
`));
//# sourceMappingURL=ame-radio-group.js.map

/***/ }),

/***/ "./extras/pro-customizables/ko-components/ame-select-box/ame-select-box.js":
/*!*********************************************************************************!*\
  !*** ./extras/pro-customizables/ko-components/ame-select-box/ame-select-box.js ***!
  \*********************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _ame_choice_control_ame_choice_control_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../ame-choice-control/ame-choice-control.js */ "./extras/pro-customizables/ko-components/ame-choice-control/ame-choice-control.js");
/* harmony import */ var _control_base_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../control-base.js */ "./extras/pro-customizables/ko-components/control-base.js");


class AmeSelectBox extends _ame_choice_control_ame_choice_control_js__WEBPACK_IMPORTED_MODULE_0__.AmeChoiceControl {
    constructor(params, $element) {
        super(params, $element);
    }
    get classes() {
        return ['ame-select-box-control', ...super.classes];
    }
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ((0,_control_base_js__WEBPACK_IMPORTED_MODULE_1__.createControlComponentConfig)(AmeSelectBox, `
	<select data-bind="class: classString, value: valueProxy, options: options,
		optionsValue: 'value', optionsText: 'label', enable: isEnabled, attr: inputAttributes"></select>
	<!-- ko if: (description) -->
		<!-- ko component: {name: 'ame-sibling-description', params: {description: description}} --><!-- /ko -->
	<!-- /ko -->	
`));
//# sourceMappingURL=ame-select-box.js.map

/***/ }),

/***/ "./extras/pro-customizables/ko-components/ame-sibling-description/ame-sibling-description.js":
/*!***************************************************************************************************!*\
  !*** ./extras/pro-customizables/ko-components/ame-sibling-description/ame-sibling-description.js ***!
  \***************************************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _control_base_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../control-base.js */ "./extras/pro-customizables/ko-components/control-base.js");
/* harmony import */ var _ame_description_ame_description_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../ame-description/ame-description.js */ "./extras/pro-customizables/ko-components/ame-description/ame-description.js");


/**
 * A simple component that displays the description of a UI element.
 *
 * This should be rendered as a sibling of the UI element's component,
 * typically immediately after it.
 *
 * Caution: HTML is allowed in the description.
 */
class AmeSiblingDescription extends _ame_description_ame_description_js__WEBPACK_IMPORTED_MODULE_1__.AmeDescriptionComponent {
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ((0,_control_base_js__WEBPACK_IMPORTED_MODULE_0__.createComponentConfig)(AmeSiblingDescription, `
	<p class="description" data-bind="html: description"></p>	
`));
//# sourceMappingURL=ame-sibling-description.js.map

/***/ }),

/***/ "./extras/pro-customizables/ko-components/ame-static-html/ame-static-html.js":
/*!***********************************************************************************!*\
  !*** ./extras/pro-customizables/ko-components/ame-static-html/ame-static-html.js ***!
  \***********************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _control_base_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../control-base.js */ "./extras/pro-customizables/ko-components/control-base.js");

class AmeStaticHtml extends _control_base_js__WEBPACK_IMPORTED_MODULE_0__.KoStandaloneControl {
    constructor(params, $element) {
        super(params, $element);
        this.containerType = 'span';
        this.htmlContent = (typeof params.html === 'string') ? params.html : '';
        if (typeof params.container === 'string') {
            this.containerType = params.container;
        }
    }
}
//Note: The HTML content has to be in a container element because Knockout doesn't allow
//using the "html" binding with virtual elements.
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ((0,_control_base_js__WEBPACK_IMPORTED_MODULE_0__.createControlComponentConfig)(AmeStaticHtml, `
	<!-- ko if: containerType === 'div' -->
		<div data-bind="html: htmlContent"></div>
	<!-- /ko -->
	<!-- ko if: containerType === 'span' -->
		<span data-bind="html: htmlContent"></span>
	<!-- /ko -->
`));
//# sourceMappingURL=ame-static-html.js.map

/***/ }),

/***/ "./extras/pro-customizables/ko-components/ame-text-input/ame-text-input.js":
/*!*********************************************************************************!*\
  !*** ./extras/pro-customizables/ko-components/ame-text-input/ame-text-input.js ***!
  \*********************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   AmeTextInput: () => (/* binding */ AmeTextInput),
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _control_base_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../control-base.js */ "./extras/pro-customizables/ko-components/control-base.js");

class AmeTextInput extends _control_base_js__WEBPACK_IMPORTED_MODULE_0__.KoStandaloneControl {
    constructor(params, $element) {
        super(params, $element);
        this.inputType = 'text';
        this.isCode = params.isCode || false;
        this.inputType = params.inputType || 'text';
    }
    get inputClasses() {
        const classes = ['regular-text'];
        if (this.isCode) {
            classes.push('code');
        }
        classes.push('ame-text-input-control', ...super.inputClasses);
        return classes;
    }
    getAdditionalInputAttributes() {
        return {
            'type': this.inputType,
            ...super.getAdditionalInputAttributes()
        };
    }
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ((0,_control_base_js__WEBPACK_IMPORTED_MODULE_0__.createControlComponentConfig)(AmeTextInput, `
	<input data-bind="value: valueProxy, attr: inputAttributes, class: inputClassString">
	<!-- ko if: (description) -->
		<!-- ko component: {name: 'ame-sibling-description', params: {description: description}} --><!-- /ko -->
	<!-- /ko -->	
`));
//# sourceMappingURL=ame-text-input.js.map

/***/ }),

/***/ "./extras/pro-customizables/ko-components/ame-toggle-checkbox/ame-toggle-checkbox.js":
/*!*******************************************************************************************!*\
  !*** ./extras/pro-customizables/ko-components/ame-toggle-checkbox/ame-toggle-checkbox.js ***!
  \*******************************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _control_base_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../control-base.js */ "./extras/pro-customizables/ko-components/control-base.js");

class AmeToggleCheckbox extends _control_base_js__WEBPACK_IMPORTED_MODULE_0__.KoStandaloneControl {
    constructor(params, $element) {
        super(params, $element);
        this.onValue = (typeof params.onValue !== 'undefined') ? params.onValue : true;
        this.offValue = (typeof params.offValue !== 'undefined') ? params.offValue : false;
        if (typeof this.settings['value'] === 'undefined') {
            this.isChecked = ko.pureComputed(() => false);
        }
        else {
            this.isChecked = ko.computed({
                read: () => {
                    return this.settings.value.value() === ko.unwrap(this.onValue);
                },
                write: (newValue) => {
                    this.settings.value.value(ko.unwrap(newValue ? this.onValue : this.offValue));
                },
                deferEvaluation: true
            });
        }
    }
    get classes() {
        return ['ame-toggle-checkbox-control', ...super.classes];
    }
}
//Unlike the HTML version of this control, the Knockout version doesn't have
//a second, hidden checkbox. This is because the component is entirely JS-based
//and doesn't need to be submitted as part of a form.
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ((0,_control_base_js__WEBPACK_IMPORTED_MODULE_0__.createControlComponentConfig)(AmeToggleCheckbox, `
	<label data-bind="class: classString">
		<input type="checkbox" data-bind="checked: isChecked, attr: inputAttributes, 
			class: inputClassString, enable: isEnabled">
		<span data-bind="text: label"></span>
		<!-- ko if: (description) -->
			<!-- ko component: {name: 'ame-nested-description', params: {description: description}} --><!-- /ko -->
		<!-- /ko -->
	</label>	
`));
//# sourceMappingURL=ame-toggle-checkbox.js.map

/***/ }),

/***/ "./extras/pro-customizables/ko-components/ame-tooltip-trigger/ame-tooltip-trigger.js":
/*!*******************************************************************************************!*\
  !*** ./extras/pro-customizables/ko-components/ame-tooltip-trigger/ame-tooltip-trigger.js ***!
  \*******************************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _control_base_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../control-base.js */ "./extras/pro-customizables/ko-components/control-base.js");

class AmeTooltipTrigger extends _control_base_js__WEBPACK_IMPORTED_MODULE_0__.KoStandaloneControl {
    constructor(params, $element) {
        super(params, $element);
        if ((typeof params.tooltip === 'undefined') || (params.tooltip === null)) {
            throw new Error('The AmeTooltipTrigger component requires the "tooltip" parameter.');
        }
        this.tooltip = params.tooltip;
        this.text = this.tooltip.htmlContent || '';
        //Convert newlines to <br> for better formatting in tooltips.
        //Some other parts of the plugin rely on the implicit conversion of newlines to <br>
        //that qTip2 apparently does when reading the title attribute, but this component
        //doesn't use the title attribute.
        this.text = this.text.replace(/\n/g, '<br>');
    }
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ((0,_control_base_js__WEBPACK_IMPORTED_MODULE_0__.createComponentConfig)(AmeTooltipTrigger, `
	<a class="ws_tooltip_trigger" 
		data-bind="ameTooltip: {text: text}"><span class="dashicons dashicons-info"></span></a>
`));
//# sourceMappingURL=ame-tooltip-trigger.js.map

/***/ }),

/***/ "./extras/pro-customizables/ko-components/ame-unit-dropdown/ame-unit-dropdown.js":
/*!***************************************************************************************!*\
  !*** ./extras/pro-customizables/ko-components/ame-unit-dropdown/ame-unit-dropdown.js ***!
  \***************************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   AmeUnitDropdown: () => (/* binding */ AmeUnitDropdown),
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _control_base_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../control-base.js */ "./extras/pro-customizables/ko-components/control-base.js");

class AmeUnitDropdown extends _control_base_js__WEBPACK_IMPORTED_MODULE_0__.KoStandaloneControl {
    constructor(params, $element) {
        super(params, $element);
        this.dropdownData = params.optionData || {
            options: [],
            optionsText: 'text',
            optionsValue: 'value'
        };
        this.selectId = params.id || '';
    }
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ((0,_control_base_js__WEBPACK_IMPORTED_MODULE_0__.createControlComponentConfig)(AmeUnitDropdown, `
	<select data-bind="options: dropdownData.options, optionsText: dropdownData.optionsText, 
		optionsValue: dropdownData.optionsValue, value: valueProxy, class: classString,
		attr: {id: selectId}"></select>
`));
//# sourceMappingURL=ame-unit-dropdown.js.map

/***/ }),

/***/ "./extras/pro-customizables/ko-components/ame-wp-editor/ame-wp-editor.js":
/*!*******************************************************************************!*\
  !*** ./extras/pro-customizables/ko-components/ame-wp-editor/ame-wp-editor.js ***!
  \*******************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _control_base_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../control-base.js */ "./extras/pro-customizables/ko-components/control-base.js");


//Note: Requires Lodash, but does not explicitly import it because this plugin
//already uses Lodash as a global variable (wsAmeLodash) in many places. Code
//that uses this component should make sure that Lodash is loaded.
let autoAssignedIdCounter = 0;
/**
 * List of visual editor buttons that are visible in the "teeny" mode.
 *
 * Found in /wp-includes/class-wp-editor.php, the editor_settings() method.
 * The relevant code is around line #601 (as of WP 6.1.1).
 */
const TeenyButtons = [
    'bold',
    'italic',
    'underline',
    'blockquote',
    'strikethrough',
    'bullist',
    'numlist',
    'alignleft',
    'aligncenter',
    'alignright',
    'undo',
    'redo',
    'link',
    'fullscreen'
];
/**
 * List of Quicktags editor buttons that are visible by default.
 *
 * The default list of text editor buttons used by wp.editor.initialize()
 * doesn't match the defaults used by wp_editor() in PHP. Let's copy the list
 * from /includes/class-wp-editor.php.
 */
const DefaultQuicktagsButtons = [
    'strong', 'em', 'link', 'block', 'del', 'ins', 'img', 'ul', 'ol', 'li', 'code', 'more', 'close'
];
class AmeWpEditor extends _control_base_js__WEBPACK_IMPORTED_MODULE_0__.KoStandaloneControl {
    constructor(params, $element) {
        super(params, $element);
        this.editorId = null;
        this.isWpEditorInitialized = false;
        const textSetting = this.settings.value;
        if (typeof textSetting === 'undefined') {
            throw new Error('Visual Editor control is missing the required setting');
        }
        this.rows = params.rows || 6;
        this.isTeeny = !!params.teeny;
    }
    getAdditionalInputAttributes() {
        return {
            rows: this.rows.toString(),
            ...super.getAdditionalInputAttributes()
        };
    }
    koDescendantsComplete() {
        const $textArea = this.findChild('textarea.ame-wp-editor-textarea');
        if ($textArea.length === 0) {
            return;
        }
        const currentValue = this.valueProxy();
        $textArea.val((currentValue === null) ? '' : currentValue.toString());
        //The textarea must have an ID for wp.editor.initialize() to work.
        {
            let editorId = $textArea.attr('id');
            if (!editorId) {
                editorId = 'ws-ame-wp-editor-aid-' + (autoAssignedIdCounter++);
                $textArea.attr('id', editorId);
            }
            this.editorId = editorId;
        }
        //Update the setting when the contents of the underlying textarea change.
        //This happens when the user selects the "Text" tab in the editor, or when
        //TinyMCE is unavailable (e.g. if the "Disable the visual editor when writing"
        //option is checked in the user's profile).
        $textArea.on('change input', this.throttleUpdates(() => $textArea.val()));
        let editorSettings = {
            tinymce: {
                wpautop: true
            },
            quicktags: {
                //The default list of text editor buttons used by wp.editor.initialize()
                //doesn't match the defaults used by wp_editor() in PHP. Let's copy the list
                //from /includes/class-wp-editor.php.
                buttons: DefaultQuicktagsButtons.join(','),
            },
            //Include the "Add Media" button.
            mediaButtons: true,
        };
        if (typeof window['tinymce'] === 'undefined') {
            //TinyMCE is disabled or not available.
            editorSettings.tinymce = false;
        }
        if (this.isTeeny && (typeof editorSettings.tinymce === 'object')) {
            editorSettings.tinymce.toolbar1 = TeenyButtons.join(',');
            editorSettings.tinymce.toolbar2 = '';
        }
        const $document = jQuery(document);
        const self = this;
        //After the editor finishes initializing, add an event listener to update
        //the setting when the contents of the visual editor change.
        $document.on('tinymce-editor-init', function addMceChangeListener(event, editor) {
            if (editor.id !== self.editorId) {
                return; //Not our editor.
            }
            //According to the TinyMCE documentation, the "Change" event is fired
            //when "changes [...] cause an undo level to be added". This could be
            //too frequent for our purposes, so we'll throttle the callback.
            editor.on('Change', self.throttleUpdates(() => editor.getContent()));
            $document.off('tinymce-editor-init', addMceChangeListener);
        });
        //Unfortunately, as of WP 6.2-beta, wp.editor.initialize() doesn't add
        //the "wp-editor-container" wrapper when only the Quicktags editor is used.
        //This means the editor won't be styled correctly. Let's fix that.
        $document.on('quicktags-init', function maybeAddEditorWrapper(event, editor) {
            if (!editor || (editor.id !== self.editorId)) {
                return;
            }
            if (editor.canvas) {
                const $textarea = jQuery(editor.canvas);
                const $wrapper = $textarea.closest('.wp-editor-container');
                if ($wrapper.length === 0) {
                    //Also include the toolbar in the wrapper.
                    const $toolbar = $textarea.prevAll('.quicktags-toolbar').first();
                    $textarea.add($toolbar).wrapAll('<div class="wp-editor-container"></div>');
                }
            }
            $document.off('quicktags-init', maybeAddEditorWrapper);
        });
        //Finally, initialize the editor.
        wp.editor.initialize($textArea.attr('id'), editorSettings);
        this.isWpEditorInitialized = true;
    }
    /**
     * Create a throttled function that updates the setting.
     *
     * There are multiple ways to get the contents of the editor (e.g. TinyMCE mode
     * vs a plain textarea), so using a utility function helps avoid code duplication.
     *
     * @param valueGetter
     * @protected
     */
    throttleUpdates(valueGetter) {
        const textSetting = this.settings.value;
        return wsAmeLodash.throttle(function () {
            textSetting.value(valueGetter());
            return void 0;
        }, 1000, { leading: true, trailing: true });
    }
    dispose() {
        //Destroy the editor. It's not clear whether this is necessary, but it's
        //probably a good idea to give WP a chance to clean up.
        if (this.isWpEditorInitialized && (this.editorId !== null)) {
            wp.editor.remove(this.editorId);
            this.isWpEditorInitialized = false;
        }
        super.dispose();
    }
}
//Note: The class of the textarea element is set directly instead of using a binding
//because it must always have the "wp-editor-area" class for it to render correctly
//(apparently, wp.editor.initialize() does not automatically add that class).
//Knockout should not be able to remove the class.
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ((0,_control_base_js__WEBPACK_IMPORTED_MODULE_0__.createControlComponentConfig)(AmeWpEditor, `
	<textarea data-bind="attr: inputAttributes" class="wp-editor-area ame-wp-editor-textarea" cols="40"></textarea>	
`));
//# sourceMappingURL=ame-wp-editor.js.map

/***/ }),

/***/ "./extras/pro-customizables/ko-components/control-base.js":
/*!****************************************************************!*\
  !*** ./extras/pro-customizables/ko-components/control-base.js ***!
  \****************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   ComponentBindingOptions: () => (/* binding */ ComponentBindingOptions),
/* harmony export */   KoComponentViewModel: () => (/* binding */ KoComponentViewModel),
/* harmony export */   KoContainerViewModel: () => (/* binding */ KoContainerViewModel),
/* harmony export */   KoControlViewModel: () => (/* binding */ KoControlViewModel),
/* harmony export */   KoRendererViewModel: () => (/* binding */ KoRendererViewModel),
/* harmony export */   KoStandaloneControl: () => (/* binding */ KoStandaloneControl),
/* harmony export */   createComponentConfig: () => (/* binding */ createComponentConfig),
/* harmony export */   createControlComponentConfig: () => (/* binding */ createControlComponentConfig),
/* harmony export */   createRendererComponentConfig: () => (/* binding */ createRendererComponentConfig)
/* harmony export */ });
/* harmony import */ var _assets_customizable_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../assets/customizable.js */ "./extras/pro-customizables/assets/customizable.js");

var Setting = _assets_customizable_js__WEBPACK_IMPORTED_MODULE_0__.AmeCustomizable.Setting;
var InterfaceStructure = _assets_customizable_js__WEBPACK_IMPORTED_MODULE_0__.AmeCustomizable.InterfaceStructure;
var ServiceRegistry = _assets_customizable_js__WEBPACK_IMPORTED_MODULE_0__.AmeCustomizable.ServiceRegistry;
/**
 * Base view model for Customizable KO components.
 *
 * Architecture note:
 *
 * In retrospect, it may have been possible to use UiElement subclasses as view models for components
 * by setting viewModel.createViewModel to a function that returns the UiElement instance. That would
 * let us avoid an additional level of abstraction and the need to split control properties between
 * the UiElement class hierarchy and the view model classes.
 *
 * However, that would require a major refactoring, so we'll stick with the current approach for now.
 */
class KoComponentViewModel {
    constructor(params, $element) {
        this.params = params;
        this.$element = $element;
        /**
         * The ID of the KO component, which is normally identical to the ID of the underlying UiElement.
         *
         * Note that this is not necessarily the same as the ID attribute of the DOM element(s) generated
         * by the component. The DOM element (if any) might have a different ID or no ID at all. Still,
         * this ID is expected to be HTML-safe, and can be used in an ID attribute.
         */
        this.id = '';
        this.isBoundToComment = ($element[0]) && ($element[0].nodeType === Node.COMMENT_NODE);
        if ((typeof params.id === 'string') && (params.id !== '')) {
            this.id = params.id;
        }
        this.uiElement = null;
        const expectedType = this.getExpectedUiElementType();
        if (expectedType !== null) {
            if ((typeof params.uiElement !== 'undefined')
                && (params.uiElement instanceof expectedType)) {
                this.uiElement = params.uiElement;
            }
            else {
                throw new Error('uiElement is not a ' + expectedType.name + ' instance.');
            }
        }
        else if ((typeof params.uiElement !== 'undefined') && !(this instanceof KoStandaloneControl)) {
            console.warn('Unexpected "uiElement" parameter for ' + this.constructor.name
                + ' that did not expect an UI element. Did you forget to override getExpectedUiElementType() ?', params.uiElement);
        }
        if (typeof params.registry !== 'undefined') {
            if (params.registry instanceof ServiceRegistry) {
                this.registry = params.registry;
            }
            else {
                throw new Error('Component parameter "registry" is not a valid ServiceRegistry instance.');
            }
        }
        else if (this.uiElement !== null) {
            this.registry = this.uiElement.getServiceRegistry();
        }
        else {
            this.registry = null;
        }
        if (typeof params.children !== 'undefined') {
            if (this.isObservableArray(params.children)) {
                this.inputChildren = params.children;
            }
            else if (Array.isArray(params.children)) {
                this.inputChildren = ko.observableArray(params.children);
            }
            else {
                throw new Error('Invalid "children" parameter: expected an array or an observable array.');
            }
        }
        else {
            this.inputChildren = ko.observableArray();
        }
        this.childComponents = ko.pureComputed(() => {
            const result = ko.unwrap(this.inputChildren)
                .map(child => this.mapChildToComponentBinding(child))
                .filter(binding => binding !== null);
            //TypeScript does not recognize that the filter() call above removes
            //all null values, so we need an explicit cast.
            return result;
        });
        this.customClasses = ((typeof params.classes === 'object') && Array.isArray(params.classes)) ? params.classes : [];
        this.customStyles = ((typeof params.styles === 'object') && (params.styles !== null)) ? params.styles : {};
        if (typeof params.enabled !== 'undefined') {
            if (ko.isObservable(params.enabled)) {
                this.isEnabled = params.enabled;
            }
            else {
                this.isEnabled = ko.pureComputed(() => !!params.enabled);
            }
        }
        else {
            this.isEnabled = ko.pureComputed(() => true);
        }
        //Get the description from the "description" parameter.
        this.description = params.description
            ? ko.unwrap(params.description.toString()) : '';
        //Tooltip.
        if (typeof params.tooltip === 'object' && (params.tooltip !== null)) {
            const tooltipParam = params.tooltip;
            this.tooltip = {
                htmlContent: (typeof tooltipParam.htmlContent === 'string')
                    ? tooltipParam.htmlContent
                    : '',
                type: ((typeof tooltipParam.type === 'string') && (tooltipParam.type === 'experimental'))
                    ? 'experimental'
                    : 'info',
                extraClasses: ((typeof tooltipParam.extraClasses !== 'undefined') && Array.isArray(tooltipParam.extraClasses))
                    ? tooltipParam.extraClasses.filter(c => (typeof c === 'string'))
                    : [],
            };
        }
        else {
            this.tooltip = null;
        }
    }
    dispose() {
        this.childComponents.dispose();
    }
    getExpectedUiElementType() {
        return null;
    }
    get classes() {
        return [].concat(this.customClasses);
    }
    // noinspection JSUnusedGlobalSymbols -- Used in Knockout templates.
    get classString() {
        return this.classes.join(' ');
    }
    // noinspection JSUnusedGlobalSymbols -- Used in Knockout templates.
    get styles() {
        return Object.assign({}, this.customStyles);
    }
    findChild(selector, allowSiblingSearch = null) {
        if (allowSiblingSearch === null) {
            //Enable only if the component is bound to a comment (i.e. "<!-- ko component: ... -->").
            allowSiblingSearch = this.isBoundToComment;
        }
        if (this.isBoundToComment) {
            if (allowSiblingSearch) {
                return this.$element.nextAll(selector).first();
            }
            else {
                //We would never find anything because a comment node has no children.
                return jQuery();
            }
        }
        return this.$element.find(selector);
    }
    isObservableArray(value) {
        return (typeof value === 'function')
            && (typeof value.slice === 'function')
            && (typeof value.indexOf === 'function')
            && (ko.isObservable(value));
    }
    mapChildToComponentBinding(child) {
        if (child.component) {
            return ComponentBindingOptions.fromElement(child);
        }
        else if (this.shouldMapMiscChildrenToPlaceholders) {
            return ComponentBindingOptions.fromElement(child, 'ame-placeholder');
        }
        return null;
    }
    /**
     * Whether child UI elements without a specified component should be mapped
     * to the "ame-placeholder" component.
     */
    get shouldMapMiscChildrenToPlaceholders() {
        return false;
    }
}
function makeCreateVmFunctionForComponent(ctor) {
    return function (params, componentInfo) {
        const $element = jQuery(componentInfo.element);
        return new ctor(params, $element);
    };
}
function createComponentConfig(ctor, templateString) {
    return {
        viewModel: {
            createViewModel: makeCreateVmFunctionForComponent(ctor),
        },
        template: templateString,
    };
}
//endregion
//region Container
class ComponentBindingOptions {
    // noinspection JSUnusedGlobalSymbols -- the uiElement property is used in the KO template of AC control groups.
    constructor(name, params, uiElement) {
        this.name = name;
        this.params = params;
        this.uiElement = uiElement;
        if (name === '') {
            throw new Error('Component name cannot be empty.');
        }
    }
    static fromElement(element, overrideComponentName = null, overrideSomeComponentParams = null) {
        if (!element.component && (overrideComponentName === null)) {
            throw new Error(`Cannot create component binding options for UI element "${element.id}" without a component name.`);
        }
        const params = element.getComponentParams();
        if (overrideSomeComponentParams !== null) {
            Object.assign(params, overrideSomeComponentParams);
        }
        return new ComponentBindingOptions(overrideComponentName || element.component, params, element);
    }
}
class KoContainerViewModel extends KoComponentViewModel {
    constructor(params, $element) {
        if (typeof params.children === 'undefined') {
            throw new Error('Missing "children" parameter.');
        }
        super(params, $element);
        this.title = ko.pureComputed(() => {
            if (typeof params.title !== 'undefined') {
                let title = ko.unwrap(params.title);
                if ((title !== null) && (typeof title !== 'undefined')) {
                    return title.toString();
                }
            }
            return '';
        });
        if ((typeof params.childrenContainerClasses !== 'undefined') && Array.isArray(params.childrenContainerClasses)) {
            this.childrenContainerClass = params.childrenContainerClasses.join(' ');
        }
        else {
            this.childrenContainerClass = '';
        }
    }
}
//endregion
//region Control
class KoControlViewModel extends KoComponentViewModel {
    constructor(params, $element) {
        super(params, $element);
        this.settings =
            ((typeof params.settings === 'object') && isSettingMap(params.settings))
                ? params.settings
                : {};
        if (typeof this.settings.value !== 'undefined') {
            this.valueProxy = this.settings.value.value;
        }
        else {
            this.valueProxy = ko.pureComputed(() => {
                console.error('Missing "value" setting for a control component.', this.settings, params);
                return '';
            });
        }
        //Input ID will be provided by the server if applicable.
        this.primaryInputId = (typeof params.primaryInputId === 'string') ? params.primaryInputId : null;
        this.customInputClasses = ((typeof params.inputClasses !== 'undefined') && Array.isArray(params.inputClasses))
            ? params.inputClasses
            : [];
        this.inputAttributes = ko.pureComputed(() => {
            const attributes = ((typeof params.inputAttributes === 'object') && (params.inputAttributes !== null))
                ? params.inputAttributes
                : {};
            const inputId = this.getPrimaryInputId();
            if ((inputId !== null) && (inputId !== '')) {
                attributes.id = inputId;
            }
            //Note: The "name" field is not used because these controls are entirely JS-driven.
            const additionalAttributes = this.getAdditionalInputAttributes();
            for (const key in additionalAttributes) {
                if (!additionalAttributes.hasOwnProperty(key)) {
                    continue;
                }
                attributes[key] = additionalAttributes[key];
            }
            return attributes;
        });
        if ((typeof params.label !== 'undefined') && (params.label !== null)) {
            if (ko.isObservable(params.label)) {
                this.label = params.label;
            }
            else {
                this.label = ko.observable(
                //Seemingly unnecessary check, but the TS compiler complains label is possibly undefined.
                (typeof params.label !== 'undefined') ? params.label.toString() : '');
            }
        }
        else {
            this.label = ko.observable('');
        }
    }
    get inputClasses() {
        return this.customInputClasses;
    }
    // noinspection JSUnusedGlobalSymbols -- Used in Knockout templates.
    get inputClassString() {
        return this.inputClasses.join(' ');
    }
    getAdditionalInputAttributes() {
        return {};
    }
    getPrimaryInputId() {
        return this.primaryInputId;
    }
}
function isSettingMap(value) {
    if (value === null) {
        return false;
    }
    if (typeof value !== 'object') {
        return false;
    }
    const valueAsRecord = value;
    for (const key in valueAsRecord) {
        if (!valueAsRecord.hasOwnProperty(key)) {
            continue;
        }
        if (!(valueAsRecord[key] instanceof Setting)) {
            return false;
        }
    }
    return true;
}
/**
 * A control that doesn't use or need a UI element instance, but can still have
 * settings and other parameters typically associated with controls.
 */
class KoStandaloneControl extends KoControlViewModel {
}
function createControlComponentConfig(ctor, templateString) {
    return {
        viewModel: {
            createViewModel: makeCreateVmFunctionForComponent(ctor),
        },
        template: templateString,
    };
}
//endregion
//region Renderer
class KoRendererViewModel extends KoComponentViewModel {
    constructor(params, $element) {
        super(params, $element);
        if ((typeof params.structure !== 'object') || !(params.structure instanceof InterfaceStructure)) {
            throw new Error('Invalid interface structure for a renderer component.');
        }
        this.structure = params.structure;
    }
}
function createRendererComponentConfig(ctor, templateString) {
    return {
        viewModel: {
            createViewModel: makeCreateVmFunctionForComponent(ctor),
        },
        template: templateString,
    };
}
//endregion
//# sourceMappingURL=control-base.js.map

/***/ }),

/***/ "./extras/pro-customizables/ko-components/lazy-popup-slider-adapter.js":
/*!*****************************************************************************!*\
  !*** ./extras/pro-customizables/ko-components/lazy-popup-slider-adapter.js ***!
  \*****************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   LazyPopupSliderAdapter: () => (/* binding */ LazyPopupSliderAdapter)
/* harmony export */ });
/// <reference path="../../../customizables/assets/popup-slider.d.ts" />
/**
 * This is a wrapper for the popup slider that initializes the slider on first use.
 * It's useful for Knockout components.
 */
class LazyPopupSliderAdapter {
    constructor(sliderRanges, containerSelector = '.ame-container-with-popup-slider', inputSelector = 'input', sliderOptions = {}) {
        this.sliderRanges = sliderRanges;
        this.containerSelector = containerSelector;
        this.inputSelector = inputSelector;
        this.sliderOptions = sliderOptions;
        this.slider = null;
        if (!sliderOptions.hasOwnProperty('ranges')) {
            sliderOptions.ranges = sliderRanges;
        }
        this.handleKoClickEvent = ($data, event) => {
            //Verify that this is one of the inputs we're interested in.
            //Also, disabled inputs should not trigger the slider.
            if (event.target === null) {
                return;
            }
            const $input = jQuery(event.target);
            if ($input.is(':disabled') || !$input.is(this.inputSelector)) {
                return;
            }
            //Short-circuit if the slider is already initialized.
            if (this.slider) {
                this.slider.showForInput($input);
                return;
            }
            //Some sanity checks.
            if (typeof AmePopupSlider === 'undefined') {
                return;
            }
            const $container = $input.closest(this.containerSelector);
            if ($container.length < 1) {
                return;
            }
            this.initSlider($container);
            if (this.slider !== null) {
                //TS doesn't realize that this.initSlider() will initialize the slider.
                this.slider.showForInput($input);
            }
        };
    }
    /**
     * Initialize the slider if it's not already initialized.
     */
    initSlider($container) {
        if (this.slider) {
            return;
        }
        //In HTML, we would pass the range data as a "data-slider-ranges" attribute,
        //but here they are passed via the "ranges" option (see the constructor).
        this.slider = AmePopupSlider.createSlider($container, this.sliderOptions);
    }
}
//# sourceMappingURL=lazy-popup-slider-adapter.js.map

/***/ })

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ __webpack_require__.O(0, ["customizable"], () => (__webpack_exec__("./extras/modules/tweaks/tweak-manager.ts")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ }
]);
//# sourceMappingURL=tweak-manager.bundle.js.map