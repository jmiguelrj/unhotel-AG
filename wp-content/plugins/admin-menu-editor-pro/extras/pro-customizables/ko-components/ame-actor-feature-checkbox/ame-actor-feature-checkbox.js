import { ComponentBindingOptions, createControlComponentConfig, KoStandaloneControl } from '../control-base.js';
import { AmeCustomizable } from '../../assets/customizable.js';
var ServiceRegistry = AmeCustomizable.ServiceRegistry;
//Note: Requires Lodash, but does not explicitly import it because this plugin
//already uses Lodash as a global variable (wsAmeLodash) in many places.
class AmeActorFeatureCheckbox extends KoStandaloneControl {
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
            return ComponentBindingOptions.fromElement(child, null, {
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
export default createControlComponentConfig(AmeActorFeatureCheckbox, `
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
`);
//# sourceMappingURL=ame-actor-feature-checkbox.js.map