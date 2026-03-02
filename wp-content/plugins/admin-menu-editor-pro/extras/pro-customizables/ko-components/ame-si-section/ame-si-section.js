import { ComponentBindingOptions, createComponentConfig, KoContainerViewModel } from '../control-base.js';
import { AmeCustomizable } from '../../assets/customizable.js';
var Section = AmeCustomizable.Section;
var Control = AmeCustomizable.Control;
class AmeSiSection extends KoContainerViewModel {
    constructor(params, $element) {
        super(params, $element);
        this.createdGroups = new WeakMap();
    }
    getExpectedUiElementType() {
        return Section;
    }
    mapChildToComponentBinding(child) {
        //Put ungrouped child controls into control groups.
        if (child instanceof Control) {
            let group = this.createdGroups.get(child);
            if (!group) {
                group = ComponentBindingOptions.fromElement(child.createControlGroup('ame-si-control-group'));
                this.createdGroups.set(child, group);
            }
            return group;
        }
        return super.mapChildToComponentBinding(child);
    }
    get shouldMapMiscChildrenToPlaceholders() {
        return true;
    }
}
export default createComponentConfig(AmeSiSection, `
	<div class="ame-si-section">
		<h3 data-bind="text: uiElement.title"></h3>
		<!-- ko foreach: childComponents -->
			<!-- ko component: $data --><!-- /ko -->
		<!-- /ko -->
	</div>
`);
//# sourceMappingURL=ame-si-section.js.map